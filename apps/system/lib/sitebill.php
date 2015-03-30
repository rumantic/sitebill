<?php
/**
 * SiteBill parent class
 * @author Kondin Dmitriy <kondin@etown.ru>
 */

if(!defined('DB_PREFIX')){
	define('DB_PREFIX', $__db_prefix);
}
if (  !defined('UPLOADIFY_TABLE')  ) 
{
    define('UPLOADIFY_TABLE', DB_PREFIX.'_uploadify');
}
if (  !defined('IMAGE_TABLE')  ) 
{
    define('IMAGE_TABLE', DB_PREFIX.'_image');
}


//require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/sitebill_application.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/sitebill_registry.php');
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/multilanguage/multilanguage.class.php';
Multilanguage::start('frontend',$_SESSION['_lang']);
Multilanguage::appendAppDictionary('system');

require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/debugger.class.php';
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/logger.class.php';
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/dbc.php';
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/sitebill_datetime.php';
//require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/sitebill_router.php';
//require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/sitebill_user.php';
//Sitebill_User::getInstance();


if(!defined('DB_DSN')){
	define('DB_DSN','mysql:host='.$__server.';dbname='.$__db);
}
if(!defined('DB_ENCODING')){
	define('DB_ENCODING','cp1251');
}
if(!defined('SITE_ENCODING')){
	define('SITE_ENCODING','windows-1251');
}
if(!defined('DB_USER')){
	define('DB_USER',$__user);
}
if(!defined('DB_PASS')){
	define('DB_PASS',$__password);
}
if(!defined('LOG_ENABLED')){
	define('LOG_ENABLED',false);
}
if(!defined('DEBUG_ENABLED')){
	define('DEBUG_ENABLED',false);
}
if(!defined('LOGGER_FILE')){
	define('LOGGER_FILE',SITEBILL_DOCUMENT_ROOT.'/log_000.txt');
}
if(isset($_REQUEST['search'])){
	$_SESSION['rem_page']=1;
}
if(isset($_REQUEST['page'])){
	$_SESSION['rem_page']=$_REQUEST['page'];
}elseif(!isset($_SESSION['rem_page'])){
	$_SESSION['rem_page']=1;
}
$_POST['page']=$_SESSION['rem_page'];

/*
if(!isset($_SESSION['Sitebill_User']) || !is_array($_SESSION['Sitebill_User'])){
	$_SESSION['Sitebill_User']=array();
	$_SESSION['Sitebill_User']['name']='';
	$_SESSION['Sitebill_User']['group_id']=0;
	$_SESSION['Sitebill_User']['group_name']='Гость';
	$_SESSION['Sitebill_User']['login']='';
	$_SESSION['Sitebill_User']['user_id']=0;
	$_SESSION['Sitebill_User']['group_system_name']='guest';
}
*/
class SiteBill {
    /**
     * Error message
     */
    var $error_message = false;
    var $uploadify_dir = '/cache/upl/';
    var $storage_dir = '/img/data/';
    protected static $config_loaded = false;
    protected static $config_array = array();
    protected static $local_config = false;
    private $external_uploadify_image_array = false;
    protected static $storage = array();
   
    /**
     * Constructor
     */
    function SiteBill() {
        global $__server, $__db, $__user, $__password, $sitebill_document_root;
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/template/template.php';
		$this->template = new Template();
		if ( $this->isDemo() ) {
		    $this->template->assign('show_demo_banners', '1');
		}
		$this->db = new Db( $__server, $__db, $__user, $__password );
		Sitebill_Datetime::setDateFormat($this->getConfigValue('date_format'));
		//$this->Request=new stdClass();
		//$this->Request->method=$_SERVER['REQUEST_METHOD'];
		//$this->Request->path=Sitebill::getClearRequestURI();
		//$this->Request->getParams=$_GET;
		//$DB=DBC::getInstance();
		if(defined('ADMIN_NO_MAP')){
			$this->template->assign('ADMIN_NO_MAP_PROVIDERS', '1');
		}else{
			$this->template->assign('ADMIN_NO_MAP_PROVIDERS', '0');
		}
		if(defined(ADMIN_NO_NANOAPI)){
			$this->template->assign('ADMIN_NO_NANOAPI', '1');
		}else{
			$this->template->assign('ADMIN_NO_NANOAPI', '0');
		}
		if(false===self::$local_config){
			if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/configuration/config.yml')){
				require_once SITEBILL_DOCUMENT_ROOT."/third/spyc/spyc.php";
				self::$local_config = spyc_load_file(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/configuration/config.yml');
			}else{
				self::$local_config = array();
			}
		}
		
		self::setLangSession();
	}
	
	
	/*
	public function USER_isUserAuthorized(){
		if(isset($_SESSION['Sitebill_User']['user_id']) && (int)$_SESSION['Sitebill_User']['user_id']>0){
			return true;
		}
		return false;
	}
	
	public function USER_getUserId(){
		if(isset($_SESSION['Sitebill_User']['user_id']) && (int)$_SESSION['Sitebill_User']['user_id']>0){
			return $_SESSION['Sitebill_User']['user_id'];
		}
		return 0;
	}
	
	public function USER_logoutUser(){
		if(isset($_SESSION['Sitebill_User'])){
			unset($_SESSION['Sitebill_User']);
		}
	}
    */
 	static function getAttachments($object_type, $object_id){
    	$attachments=array();
    	if((int)$object_id==0 || $object_type==''){
    		return $attachments;
    	}
    	$DBC=DBC::getInstance();
    	$stmt=$DBC->query('SELECT * FROM '.DB_PREFIX.'_attachment WHERE object_type=? AND object_id=?', array($object_type, $object_id));
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$attachments[]=$ar;
    		}
    	}
    	return $attachments;
    }
    
    static function appendAttachments($object_type, $object_id, $attachments){
    	if(count($attachments)>0){
    		$DBC=DBC::getInstance();
    		$q='INSERT INTO '.DB_PREFIX.'_attachment (file_name, object_id, object_type) VALUES (?,?,?)';
    		foreach($attachments as $attachment){
    			if(file_exists(SITEBILL_DOCUMENT_ROOT.'/cache/upl/'.$attachment)){
    				copy(SITEBILL_DOCUMENT_ROOT.'/cache/upl/'.$attachment, SITEBILL_DOCUMENT_ROOT.'/attachments/'.$attachment);
    				unlink(SITEBILL_DOCUMENT_ROOT.'/cache/upl/'.$attachment);
    				if(file_exists(SITEBILL_DOCUMENT_ROOT.'/cache/upl/thumbnail/'.$attachment)){
    					unlink(SITEBILL_DOCUMENT_ROOT.'/cache/upl/thumbnail/'.$attachment);
    				}
    				$DBC->query($q, array($attachment,$object_id,$object_type));
    			}
    		}
    	}
    }
    
    function escape($text){
    	if(get_magic_quotes_gpc()){
    		$text=mysql_real_escape_string(stripcslashes($text));
    	}else{
    		$text=mysql_real_escape_string($text);
    	}
    	return $text;
    }
    
	/**
     * Get breadcrumbs
     * @param array $items
     * @return string
     */
    function get_breadcrumbs ( $items ) {
        if ( count($items) > 0 ) {
            return implode(' / ', $items);
        }
        return '';
    }
    
    function get_ajax_functions () {
    	$rs='<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/refresher.functions.js"></script>';
        return $rs;
    }
    
    /**
     * Get apps template full path
     * @param string $apps_name
     * @param string $theme
     * @param string $template_value
     * @return boolean
     */
    function get_apps_template_full_path ( $apps_name, $theme, $template_value ) {
    	if ( !file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$theme.'/'.$apps_name.'/'.$template_value) ) {
    		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/'.$apps_name.'/site/template/'.$template_value) ) {
    			return SITEBILL_DOCUMENT_ROOT.'/apps/'.$apps_name.'/site/template/'.$template_value;
    		} else {
    			echo Multilanguage::_('L_FILE')." ".SITEBILL_DOCUMENT_ROOT.'/apps/'.$apps_name.'/site/template/'.$template_value.' не найден';
    			exit;
    		}
    	} else {
    		return SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$theme.'/'.$apps_name.'/'.$template_value;
    	}
    }
    
    /**
     * Get page by URI
     * @param string $uri uri
     * @return array
     */
    function getPageByURI ( $uri ) {
    	$DBC=DBC::getInstance();
    	$query='SELECT * FROM '.DB_PREFIX.'_page WHERE uri=? LIMIT 1';
    	
        $uri = mysql_real_escape_string($uri);
        $uri = str_replace('/', '', $uri);
        $stmt=$DBC->query($query, array($uri));
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	if($ar['page_id']>0){
        		return $ar;
        	}
        }
    	return false;
    }
    
    /**
     * Get session key
     * @param void
     * @return string
     */
    function get_session_key () {
    	return $_SESSION['key'];
    }
    
    /**
     * Delete session by key
     * @param string $session_key
     * @return void
     */
    function delete_session_key ( $session_key ) {
    	$DBC=DBC::getInstance();
        $query = "DELETE FROM ".DB_PREFIX."_session WHERE session_key=?";
        $stmt=$DBC->query($query, array((string)$session_key));
        return $_SESSION['key'];
    }
    
    
    /**
     * Get session user ID
     * @param void
     * @return int
     */
    function getSessionUserId ( ) {
        global $init;
        
        $key = (isset($_SESSION['key']) ? $_SESSION['key'] : '');
        if ( $key != '' ) {
        	$DBC=DBC::getInstance();
        	$query = "SELECT user_id FROM ".DB_PREFIX."_session WHERE session_key=? LIMIT 1";
        	$stmt=$DBC->query($query, array((string)$key));
        	if($stmt){
        		$ar=$DBC->fetch($stmt);
        		$user_id = $ar['user_id'];
        		if ( $user_id != '' and $user_id != 0 ) {
        			$this->user_id = $user_id;
        			$init->setUserId($user_id);
        			return $user_id;
        		} else {
        			$this->user_id = 0;
        			return 0;
        		}
        	}
       	}
        $this->user_id = 0;
        return 0;
    }
    
    /**
     * Load uploadify images
     * @param string $session_code session code
     * @return array
     */
    function load_uploadify_images ( $session_code='', $element_name='' ) {
        $ra = array();
        
        $DBC=DBC::getInstance();
        if($element_name==''){
        	$query='SELECT * FROM '.UPLOADIFY_TABLE.' WHERE `session_code`=? ORDER BY `uploadify_id`';
        	$stmt=$DBC->query($query, array((string)$session_code));
        }else{
        	$query='SELECT * FROM '.UPLOADIFY_TABLE.' WHERE `session_code`=? AND `element`=? ORDER BY `uploadify_id`';
        	$stmt=$DBC->query($query, array((string)$session_code, $element_name));
        }
        if($stmt){
        	while($ar=$DBC->fetch($stmt)){
        		$ra[] = $ar['file_name'];
        	}
        }
        if(empty($ra)){
        	return false;
        }else{
        	return $ra;
        }
    }
    
    /**
     * Edit image
     * @param string $action action
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record ID
     * @return boolean
     */
    function editImageMulti( $action, $table_name, $key, $record_id, $name_template='' ) {
    	
    	if ( !isset($record_id) or $record_id == 0 ) {
    		return false;
    	}
    	$path = SITEBILL_DOCUMENT_ROOT.'/img/data/';
    	$uploadify_path = SITEBILL_DOCUMENT_ROOT.$this->uploadify_dir;
    	$session_key=(string)$this->get_session_key();
    	$ra = array();
    	//update image
    	$images = $this->load_uploadify_images($session_key);
    	if (!$images) {
    		//Попробуем получить фото из внешнего запроса
    		$images = $this->getExternalUploadifyImageArray();
    		if (!$images) {
    			return false;
    		}
    	}
    	
    	if($action=='data'){
    		$DBC=DBC::getInstance();
    		
    		$avial_count=(int)$this->getConfigValue('photo_per_data');
    		if($avial_count==0){
    			$avial_count=1000;
    		}else{
    			$loaded=0;
    			$query='SELECT COUNT(data_image_id) AS cnt FROM '.DB_PREFIX.'_'.$table_name.'_image WHERE '.$key.'='.$record_id;
    			$stmt=$DBC->query($query);
    			if($stmt){
    				$ar=$DBC->fetch($stmt);
    				$loaded=(int)$ar['cnt'];
    			}
    			$avial_count=$avial_count-$loaded;
    			if($avial_count<1){
    				$this->delete_uploadify_images($session_key);
    				return false;
    			}
    		}
    		
    		if(count($images)>$avial_count){
    			$images=array_slice($images, 0, $avial_count);
    		}
    	}
    	
    	
    	
    	foreach ( $images as $image_name ) {
            $i++;
            $need_prv=0;
            $preview_name='';   
            if ( !empty($image_name) ) {
                $arr=explode('.',$image_name);
                $ext=strtolower($arr[count($arr)-1]);
                if((1==$this->getConfigValue('seo_photo_name_enable')) AND ($name_template!='')){
                	$name_template=substr($name_template, 0, 150);
                	if($i==0){
                		$preview_name_no_ext=$name_template;
                		$prv_no_ext=$name_template."_prev";
                	}else{
                		$preview_name_no_ext=$name_template."_".$i;
                		$prv_no_ext=$name_template."_prev".$i;
                	}
                	
                	if(file_exists($path.$preview_name_no_ext.".".$ext)){
                		$rand=rand(0, 1000);
	                	while(file_exists($path.$preview_name_no_ext."_".$rand.".".$ext)){
	                		$rand=rand(0,1000);
	                	}
	                	$preview_name=$preview_name_no_ext."_".$rand.".".$ext;
                		$prv=$prv_no_ext."_".$rand.".".$ext;
                	}else{
                		$preview_name=$preview_name_no_ext.".".$ext;
                		$prv=$prv_no_ext.".".$ext;
                	}
                }else{
                	$preview_name="img".uniqid().'_'.time()."_".$i.".".$ext;
	                $prv="prv".uniqid().'_'.time()."_".$i.".".$ext;
	                $preview_name_tmp="_tmp".uniqid().'_'.time()."_".$i.".".$ext;
                }
                
                if(in_array($ext,array('jpg','jpeg','gif','png'))){
                
	               //print_r($this->config_array);	
	               //echo $action.'_image_big_width';
	                
	                $big_width = $this->getConfigValue($action.'_image_big_width');
	                if ($big_width == '') {
	                	$big_width = $this->getConfigValue('news_image_big_width');
	                }
	                $big_height = $this->getConfigValue($action.'_image_big_height');
	                if ( $big_height == '' ) {
	                	$big_height = $this->getConfigValue('news_image_big_height');
	                }
	                
	                $preview_width = $this->getConfigValue($action.'_image_preview_width');
	                if ( $preview_width == '' ) {
	                	$preview_width = $this->getConfigValue('news_image_preview_width');
	                }
	                $preview_height = $this->getConfigValue($action.'_image_preview_height');
	                if ( $preview_height == '' ) {
	                	$preview_height = $this->getConfigValue('news_image_preview_height');
	                }
	                
	                
	                
	                
	                
	                
	                
	                $rn=$this->makePreview($uploadify_path.$image_name, $path.$preview_name, $big_width,$big_height, $ext,1);
	                if(1==$this->getConfigValue('apps.realty.preview_smart_resizing') && $action=='data'){
	                	$rp=$this->makePreview($uploadify_path.$image_name, $path.$prv, $preview_width, $preview_height, $ext,'smart');
	                }else{
	                	$rp=$this->makePreview($uploadify_path.$image_name, $path.$prv, $preview_width, $preview_height, $ext,'width');
	                }
	                
	                if($rp && $rn){
	                	/* На случай, если сервер выставляет на загруженные файлы права 0600*/
	                	chmod($path.$preview_name, 0644);
	                	chmod($path.$prv, 0644);
	                	/**/
	                	 
	                	$ra[$i]['preview'] = $prv;
	                	$ra[$i]['normal'] = $preview_name;
	                }
	                
                
                }
                
                
            } 
        }
        $this->add_image_records($ra, $table_name, $key, $record_id);
        $this->delete_uploadify_images($this->get_session_key());
        return $ra;
    }
    
    /**
     * Эта функция устанавливает массив с картинками для эмитации загрузки картинок в UPLOADIFY
     * Используется в APPS.API для загрузки картинок из мобильного приложения
     * @param $_image_array - массив с картинками
     * @return void
     */
	function setExternalUploadifyImageArray ( $_image_array ) {
		$this->external_uploadify_image_array = $_image_array;
	}
	
	function getExternalUploadifyImageArray () {
		return $this->external_uploadify_image_array;
	}
    
    function appendUploads( $table, $field, $pk_field, $record_id, $name_template='' ) {
    	$field_name=$field['name'];
    	$parameters=$field['parameters'];
    	$session_key=(string)$this->get_session_key();

    	 
    	$action=$table;
    	if ( !isset($record_id) || $record_id == 0 ) {
    		return false;
    	}
    	
    	$DBC=DBC::getInstance();
    	
    	$path = SITEBILL_DOCUMENT_ROOT.'/img/data/';
    	$uploadify_path = SITEBILL_DOCUMENT_ROOT.$this->uploadify_dir;
    	
    	//$this->writeLog(array('apps_name'=>'apps.system', 'method' => __METHOD__, 'message' => 'before load uploadify'.var_export($field, true), 'type' => NOTICE));
    	 
    	 
    	$ra = array();
    	//update image
    	$uploads = $this->load_uploadify_images($session_key, $field_name);
    	if (!$uploads) {
    		//Попробуем получить фото из внешнего запроса
    		$uploads = $this->getExternalUploadifyImageArray();
    		//$this->writeLog(array('apps_name'=>'apps.system', 'method' => __METHOD__, 'message' => 'after get external uploads = '.var_export($uploads, true), 'type' => NOTICE));
    		if (!$uploads) {
    			return false;
    		}
    	}
    	//$this->writeLog(array('apps_name'=>'apps.system', 'method' => __METHOD__, 'message' => 'uploads = '.var_export($uploads, true), 'type' => NOTICE));
    	 
    	
    	
    	$query='SELECT `'.$field_name.'` FROM '.DB_PREFIX.'_'.$table.' WHERE `'.$pk_field.'`=? LIMIT 1';
    	
    	$stmt=$DBC->query($query, array($record_id));
    	if(!$stmt){
    		return false;
    	}
    	$ar=$DBC->fetch($stmt);
    	
    	if($ar[$field_name]===''){
    		$attached_yet=array();
    	}else{
    		$attached_yet=unserialize($ar[$field_name]);
    	}
    	$i=0;
    	$max_filesize=(int)str_replace('M', '', ini_get('upload_max_filesize'));
    	if(isset($parameters['max_file_size']) && (int)$parameters['max_file_size']!=0){
    		$max_filesize=(int)$parameters['max_file_size'];
    	}
    	foreach ( $uploads as $image_name ) {
    		$i++;
    		$need_prv=0;
    		$preview_name='';
    		$filesize=filesize($uploadify_path.$image_name)/(1024*1024);
    		if($filesize>$max_filesize){
    			continue;
    		}
    		if ( !empty($image_name) ) {
    			$arr=explode('.',$image_name);
    			$ext=strtolower(end($arr));
    			//$ext=strtolower($arr[count($arr)-1]);
    			if((1==$this->getConfigValue('seo_photo_name_enable')) AND ($name_template!='')){
    				$name_template=substr($name_template,0,150);
    				if($i==0){
    					$preview_name_no_ext=$name_template;
    					$prv_no_ext=$name_template."_prev";
    				}else{
    					$preview_name_no_ext=$name_template."_".$i;
    					$prv_no_ext=$name_template."_prev".$i;
    				}
    					
    				if(file_exists($path.$preview_name_no_ext.".".$ext)){
    					$rand=rand(0,1000);
    					while(file_exists($path.$preview_name_no_ext."_".$rand.".".$ext)){
    						$rand=rand(0,1000);
    					}
    					$preview_name=$preview_name_no_ext."_".$rand.".".$ext;
    					$prv=$prv_no_ext."_".$rand.".".$ext;
    				}else{
    					$preview_name=$preview_name_no_ext.".".$ext;
    					$prv=$prv_no_ext.".".$ext;
    				}
    			}else{
    				$preview_name="img".uniqid().'_'.time()."_".$i.".".$ext;
    				$prv="prv".uniqid().'_'.time()."_".$i.".".$ext;
    				$preview_name_tmp="_tmp".uniqid().'_'.time()."_".$i.".".$ext;
    			}
    	
    			if(in_array($ext, array('jpg','jpeg','gif','png'))){
    				$big_width = $this->getConfigValue($action.'_image_big_width');
    				if ($big_width == '') {
    					$big_width = $this->getConfigValue('news_image_big_width');
    				}
    				$big_height = $this->getConfigValue($action.'_image_big_height');
    				if ( $big_height == '' ) {
    					$big_height = $this->getConfigValue('news_image_big_height');
    				}
    	
    				$preview_width = $this->getConfigValue($action.'_image_preview_width');
    				if ( $preview_width == '' ) {
    					$preview_width = $this->getConfigValue('news_image_preview_width');
    				}
    				$preview_height = $this->getConfigValue($action.'_image_preview_height');
    				if ( $preview_height == '' ) {
    					$preview_height = $this->getConfigValue('news_image_preview_height');
    				}
    				
    				if(isset($parameters['norm_width']) && (int)$parameters['norm_width']!=0){
    					$big_width=(int)$parameters['norm_width'];
    				}
    				
    				if(isset($parameters['norm_height']) && (int)$parameters['norm_height']!=0){
    					$big_height=(int)$parameters['norm_height'];
    				}
    				
    				if(isset($parameters['prev_width']) && (int)$parameters['prev_width']!=0){
    					$preview_width=(int)$parameters['prev_width'];
    				}
    				
    				if(isset($parameters['prev_height']) && (int)$parameters['prev_height']!=0){
    					$preview_height=(int)$parameters['prev_height'];
    				}
    	
    				list($width,$height)=$this->makePreview($uploadify_path.$image_name, $path.$preview_name, $big_width,$big_height, $ext,1);
    				if(1==$this->getConfigValue('apps.realty.preview_smart_resizing') && $action=='data'){
    					list($w,$h)=$this->makePreview($uploadify_path.$image_name, $path.$prv, $preview_width, $preview_height, $ext,'smart');
    				}elseif(isset($parameters['preview_smart_resizing']) && (int)$parameters['preview_smart_resizing']!=0){
    					list($w,$h)=$this->makePreview($uploadify_path.$image_name, $path.$prv, $preview_width, $preview_height, $ext,'smart');
    				}else{
    					list($w,$h)=$this->makePreview($uploadify_path.$image_name, $path.$prv, $preview_width, $preview_height, $ext,'width');
    				}
    	
    				/* На случай, если сервер выставляет на загруженные файлы права 0600*/
    				chmod($path.$preview_name, 0644);
    				chmod($path.$prv, 0644);
    				/**/
    				$ra[$i]['preview'] = $prv;
    				$ra[$i]['normal'] = $preview_name;
    			}
    			$attached_yet[]=array('preview'=>$prv, 'normal'=>$preview_name, 'type'=>'graphic', 'mime'=>$ext);
    			
    	
    		}
    	}
    	 
    	$query='UPDATE '.DB_PREFIX.'_'.$table.' SET `'.$field_name.'`=? WHERE `'.$pk_field.'`=?';
    	if(count($attached_yet)>0){
    		$stmt=$DBC->query($query, array(serialize($attached_yet), $record_id));
    	}else{
    		$stmt=$DBC->query($query, array('', $record_id));
    	}
    	//$this->add_image_records($ra, $table_name, $key, $record_id);
    	$this->delete_uploadify_images($session_key, $field_name);
    	return $ra;
    }
    
    
    
    /**
     * Edit file
     * @param string $action action
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record ID
     * @return boolean
     */
    function editFileMulti( $action, $table_name, $key, $record_id ) {
    	$path = SITEBILL_DOCUMENT_ROOT.'/img/data/';
    	$uploadify_path = SITEBILL_DOCUMENT_ROOT.$this->uploadify_dir;
    	
    	$ra = array();
    	
    	//update image
    	$images = $this->load_uploadify_images($this->get_session_key());
    	if(!$images){
    		return;
    	}
    	
        foreach ( $images as $image_name ) {
            $i++;
            $need_prv=0;
            $preview_name='';   
            if ( !empty($image_name) ) { 
                $arr=explode('.', $image_name);
                $ext=strtolower(end($arr));
                $preview_name="file".uniqid().'_'.time()."_".$i.".".$ext;
                $prv="ffile".uniqid().'_'.time()."_".$i.".".$ext;
                $preview_name_tmp="_tmp".uniqid().'_'.time()."_".$i.".".$ext;
                
                
                list($width,$height)=$this->makeMove($uploadify_path.$image_name, $path.$preview_name);
                $ra[$i]['preview'] = $preview_name;
                $ra[$i]['normal'] = $preview_name;
            } 
        }
        $this->add_image_records($ra, $table_name, $key, $record_id);
        $this->delete_uploadify_images($this->get_session_key());
        return $ra;
    }
    
    function clear_uploadify_table($session_code=''){
    	$uploadify_path = SITEBILL_DOCUMENT_ROOT.$this->uploadify_dir;
        $DBC=DBC::getInstance();
        $ra = array();
        if($session_code==''){
        	$query = "SELECT file_name FROM ".UPLOADIFY_TABLE;
        	$stmt=$DBC->query($query);
        }else{
        	$query = "SELECT file_name FROM ".UPLOADIFY_TABLE.' WHERE session_code=?';
        	$stmt=$DBC->query($query, array($session_code));
        }
        
        
        if($stmt){
        	while ($ar=$DBC->fetch($stmt)){
        		$ra[] = $ar['file_name'];
        	}
        }
        
        if ( count($ra) > 0 ) {
        	foreach ( $ra as $image_name ) {
        		if ( is_file($uploadify_path.$image_name) ) {
        			unlink($uploadify_path.$image_name);
        		}
        	}
        }
        
        if($session_code==''){
        	$query = "TRUNCATE TABLE ".UPLOADIFY_TABLE;
        	$stmt=$DBC->query($query);
        }else{
        	$query = "DELETE FROM ".UPLOADIFY_TABLE.' WHERE session_code=?';
        	$stmt=$DBC->query($query, array($session_code));
        }
        
        return true;
	}
    
	function clear_captcha_session_table(){
		$limit_date=date('Y-m-d H:i:s', (time()-24*3600));
		$DBC=DBC::getInstance();
		$q='DELETE FROM '.DB_PREFIX.'_captcha_session WHERE start_date<?';
		$DBC->query($q, array($limit_date));
    	return true;
    }
    
	function clear_session_table(){
		$limit_date=date('Y-m-d H:i:s', (time()-24*3600));
		$DBC=DBC::getInstance();
		$q='DELETE FROM '.DB_PREFIX.'_session WHERE start_date<?';
		$DBC->query($q, array($limit_date));
    	return true;
    }
    
    /**
     * Delete uploadify images
     * @param string $session_code session code
     * @return array
     */
    function delete_uploadify_images ( $session_code, $element='' ) {
    	$uploadify_path = SITEBILL_DOCUMENT_ROOT.$this->uploadify_dir;
    	$DBC=DBC::getInstance();
    	
        $ra = array();
        if($element!=''){
        	$query = 'SELECT file_name FROM '.UPLOADIFY_TABLE.' WHERE `session_code`=? AND `element`=?';
        	$stmt=$DBC->query($query, array((string)$session_code, $element));
        }else{
        	$query = 'SELECT file_name FROM '.UPLOADIFY_TABLE.' WHERE `session_code`=?';
        	$stmt=$DBC->query($query, array((string)$session_code));
        }
        
        
        
        if($stmt){
        	while($ar=$DBC->fetch($stmt)){
        		$ra[] = $ar['file_name'];
        	}
        }
        if ( count($ra) > 0 ) {
        	foreach ( $ra as $image_name ) {
        		if ( is_file($uploadify_path.$image_name) ) {
        			unlink($uploadify_path.$image_name);
        		}
        	}
        }
        if($element!=''){
        	$query = 'DELETE FROM '.UPLOADIFY_TABLE.' WHERE `session_code`=? AND `element`=?';
        	$stmt=$DBC->query($query, array((string)$session_code, $element));
        }else{
        	$query = 'DELETE FROM '.UPLOADIFY_TABLE.' WHERE session_code=?';
        	$stmt=$DBC->query($query, array((string)$session_code));
        }
        
        return true;
	}
    
	/**
     * Delete uploadify image
     * @param string $image_name image_name
     * @return array
     */
    function delete_uploadify_image ( $image_name ) {
    	$DBC=DBC::getInstance();
    	$file_name=$image_name;
    	$uploadify_path = SITEBILL_DOCUMENT_ROOT.$this->uploadify_dir;
        $query = 'DELETE FROM '.UPLOADIFY_TABLE.' WHERE file_name=?';
        $DBC->query($query, array($file_name));
        unlink($uploadify_path.$file_name);
        return true;
    }

    function get_ajax_auth_form () {
        if ( SITEBILL_MAIN_URL != '' ) {
            $add_folder = SITEBILL_MAIN_URL.'/';
        }
        $rs .= '<form method="post" onsubmit="run_login(\'login\', \'cp1251\', \''.$_SERVER['SERVER_NAME'].$add_folder.'\'); return false;">';
        $rs .= '';
        $rs .= '<table border="0">';
        if ( $this->getError() and $this->GetErrorMessage() != 'not login' ) {
            $rs .= '<tr>';
            $rs .= '<td colspan="2"><span class="error">'.$this->GetErrorMessage().'</span></td>';
            $rs .= '</tr>';
        }
        $rs .= '<tr>';
        $rs .= '<td class="special" colspan="2"><div id="error_message"></div></td>';
        $rs .= '</tr>';
        
        $rs .= '<tr>';
        $rs .= '<td class="special">'.Multilanguage::_('L_LOGIN').' </td>';
        $rs .= '<td class="special"><input type="text" name="login" id="login"></td>';
        $rs .= '</tr>';
        
        $rs .= '<tr>';
        $rs .= '<td class="special">'.Multilanguage::_('L_PASSWORD').' </td>';
        $rs .= '<td class="special"><input type="password" name="password" id="password"></td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td class="special">';
        if ( $this->getConfigValue('allow_register_admin') ) {
            $rs .= '<a href="#" onclick="run_command(\'register\', \'cp1251\', \''.$_SERVER['SERVER_NAME'].$add_folder.'\'); return false;">'.Multilanguage::_('L_AUTH_REGISTRATION').'</a>';
        }
        $rs .= '</td>';
        $rs .= '<td class="special"><input type="submit" value="'.Multilanguage::_('L_LOGIN_BUTTON').'" onclick="run_login(\'login\', \'cp1251\', \''.$_SERVER['SERVER_NAME'].$add_folder.'\'); return false;"></td>';
        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '';
        $rs .= '</form>';
        return $rs;
    }
    
    /**
     * Get simple auth form
     * @param string $action
     * @param boolean $register
     * @param boolean $remind
     * @return string
     */
    function get_simple_auth_form ( $action = '/login/', $register = true, $remind = true ) {
        if ( SITEBILL_MAIN_URL != '' ) {
            $add_folder = '/'.SITEBILL_MAIN_URL;
        }
        
        
        
        
        if($this->getConfigValue('theme')=='albostar'){
        	$rs .= '<form method="post" action="'.SITEBILL_MAIN_URL.$action.'">';
	        $rs .= '';
	        
	        if ( $this->getError() and $this->GetErrorMessage() != 'not login' ) {
	            $rs .= '<div>';
	            $rs .= '<span class="error">'.$this->GetErrorMessage().'</span>';
	           	$rs .= '</div>';
	        }
	        
	        
	        $rs .= '<label>'.Multilanguage::_('L_AUTH_LOGIN').'</label>';
	        $rs .= '<input type="text" name="login" id="login">';
	        $rs .= '<br />';
	        
	        $rs .= '<label>'.Multilanguage::_('L_AUTH_PASSWORD').'</label>';
	        $rs .= '<input type="password" name="password" id="password">';
	        $rs .= '<input type="submit" value="Вход">';
        	if ( $register ) {
	        	$rs .= '<br />';
	            $rs .= '<a href="'.SITEBILL_MAIN_URL.'/register/">'.Multilanguage::_('L_AUTH_REGISTRATION').'</a>';
	        }
	        if ( $remind ) {
	        	$rs .= '<br />';
	        	$rs .= '<a href="'.SITEBILL_MAIN_URL.'/remind/">'.Multilanguage::_('L_AUTH_FORGOT_PASS').'</a>';
	        }
	        
	        $rs .= '<input type="hidden" name="do" value="login">';
	        $rs .= '</form>';
        }else{
        	
        	$rs .= '<form method="post" action="'.SITEBILL_MAIN_URL.$action.'">';
        	if ( $this->getError() and $this->GetErrorMessage() != 'not login' ) {
        		$rs .= '<div class="alert alert-error" style="display:block;">';
        		$rs .= '<a class="close" data-dismiss="alert" href="#">x</a>'.$this->GetErrorMessage().'';
        		$rs .= '</div>';
        	}
        	
        	$rs .= '<input class="span12" placeholder="'.Multilanguage::_('L_AUTH_LOGIN').'" type="text" name="login" id="login" />';
        	$rs .= '<input class="span12" placeholder="'.Multilanguage::_('L_AUTH_PASSWORD').'" type="password" name="password" id="password" />';
        	$rs .= '<label class="checkbox">';
        	$rs .= '<input type="checkbox" name="rememberme" value="1"> Запомнить меня';
        	$rs .= '</label>';
        	$rs .= '<button class="btn-info btn" type="submit">'.Multilanguage::_('L_AUTH_ENTER').'</button>';
        	$rs .= '<input type="hidden" name="do" value="login">';
        	$rs .= '</form>';
        	
        	
        	if ( $register ) {
	            $rs .= '<a href="'.SITEBILL_MAIN_URL.'/register/">'.Multilanguage::_('L_AUTH_REGISTRATION').'</a>';
	        }
	        if ( $remind ) {
	        	$rs .= '<br><a href="'.SITEBILL_MAIN_URL.'/remind/">'.Multilanguage::_('L_AUTH_FORGOT_PASS').'</a>';
	        }
	        
        }
        
        return $rs;
    }
    
    
    /**
     * Add image data records
     * @param array $images images
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record id
     * @return boolean
     */
    function add_image_records ( $images, $table_name, $key, $record_id ) {
    	
    	$DBC=DBC::getInstance();
    	foreach ( $images as $item_id => $item_array ) {
    		$query = 'INSERT INTO '.IMAGE_TABLE.' (normal, preview) VALUES (?, ?)';
    		$stmt=$DBC->query($query, array($item_array['normal'], $item_array['preview']));
    		if($stmt){
    			$image_id=$DBC->lastInsertId();
    			$this->add_table_image_record($table_name, $key, $record_id, $image_id);
    		}
    	}
    }
    
    /**
     * Add table_image record
     * @param int $record_id record id
     * @param int $image_id image id
     * @return boolean
     */
    function add_table_image_record($table_name, $key, $record_id, $image_id) {
    	$DBC=DBC::getInstance();
       	$query = 'INSERT INTO '.DB_PREFIX.'_'.$table_name.'_image ('.$key.', image_id, sort_order) values (?, ?, ?)';
    	$DBC->query($query, array($record_id, $image_id, $image_id));
    	return true;
    }
    
   /**
     * Get Plupload plugin (http://www.plupload.com/)
     * Only html4 version available (not attached files for others)
     * @param string $session_code session code
     * @return string
     */
    function getPluploaderPlugin($session_code){
    	$this->clear_uploadify_table($session_code);
    	global $folder;
    	$rs .= '
    		
    		<style type="text/css">@import url('.$folder.'/apps/system/js/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css);</style>
			<script type="text/javascript" src="'.$folder.'/apps/system/js/plupload/plupload.full.js"></script>
			<script type="text/javascript" src="'.$folder.'/apps/system/js/plupload/jquery.plupload.queue/jquery.plupload.queue.js">
			<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>
			<script type="text/javascript" src="'.$folder.'/apps/system/js/plupload/i18n/ru.js"></script>
			<script>        
		       $(function() {
		       		function log(msg){
		       			 $("#log").append(msg + "\n");
		       		
		       		};
		       		
		       		var del=[];
		       
					$("#html4_uploader").pluploadQueue({
						runtimes : \'html4\',
						multiple_queues: true,
						url : "'.$folder.'/apps/system/js/uploadify/uploadify.php?session='.$session_code.'",
						init : {
							FileUploaded: function(up, file, info) {
								if (info.response.indexOf("wrong_ext") != -1){
									file.status = plupload.FAILED;
									up.trigger("UploadProgress", file);
								}else if(info.response.indexOf("max_file_size") != -1){
									file.status = plupload.FAILED;
									up.trigger("UploadProgress", file);
								}
							},
							
						}
					});
				});  
		    </script>  
			<div id="log"></div>
			<div id="html4_uploader">You browser doesnt support simple upload forms. Are you using Lynx?</div>';
    	return $rs;
    }
    
   /**
     * Get uploadify plugin
     * @param string $session_code session code
     * @return string
     */
    function getUploadifyPlugin ( $session_code, $params=array() ) {
    	//echo $session_code;
    	$this->clear_uploadify_table($session_code);
        global $folder;
        $rs = '';
        $rs .= '
<link href="'.$folder.'/apps/system/js/uploadify/uploadify.css" type="text/css" rel="stylesheet" />
<style>
		#filecollector { overflow: hidden; }
		#filecollector div { width: 100px; display: block; float: left; padding: 5px; margin: 3px; }
		#filecollector div img { width: 100px; border: 1px solid #CFCFCF; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15); border-radius: 5px; margin-bottom: 5px; }
</style>
		
<script type="text/javascript" src="'.$folder.'/apps/system/js/uploadify/jquery.uploadify.js"></script>
<script type="text/javascript">
var uploadedfiles = 0;
var maxQueueSize = 100;
var queueSize = 0;
$(document).ready(function() {
	var max_item_count='.((int)$this->getConfigValue('photo_per_data')>0 ? (int)$this->getConfigValue('photo_per_data') : 1000).';
	
	
	
  $(\'#file_upload\').uploadify({
    \'swf\'  : \''.$folder.'/apps/system/js/uploadify/uploadify.swf\',
    \'uploader\'    : \''.$folder.'/apps/system/js/uploadify/uploadify.php?session='.$session_code.'\',
    \'cancelImg\' : \''.$folder.'/apps/system/js/uploadify/uploadify-cancel.png\',
    \'folder\'    : \''.$folder.'/cache/upl\',
    \'auto\'      : true,
	\'fileTypeExts\': \'*.jpg;*.jpeg;*.png;*.gif\',
	\'multi\': true,	
	\'queueSizeLimit\': 100,
		\'buttonText\': \''.((isset($params['button_name']) && $params['button_name']!='') ? $params['button_name'] : Multilanguage::_('L_PHOTO')).'\',	
	\'buttonImg\': \''.$folder.'/img/button_img_upl.png\',	
    \'onUploadSuccess\': function(fileObj, response, data) {
    					queueSize++;
    					if ( response == \'max_file_size\' ) {
    						alert(\''.Multilanguage::_('L_MESSAGE_MAX_UPL_SIZE').' '.ini_get('upload_max_filesize').' \');
    						return false;
    					}
    					if ( response == \'wrong_ext\' ) {
    						alert(\''.Multilanguage::_('L_MESSAGE_AVIALABLE_EXTS').' *.jpg,*.jpeg,*.png,*.gif\');
    						return false;
    					}
    					if ( queueSize > maxQueueSize ) {
    						alert(\''.Multilanguage::_('L_MESSAGE_MAX_FILES_COUNT').'\');
    						return false;
    					}
    					var imgs_count=$("div.preview_admin").length+$("#filecollector img").length;
    					imgs_count++;
    					if(imgs_count==max_item_count){
    						$(\'#file_uploadUploader\').hide();
						}
    					addFileNotify(queueSize);
    					addFileInCollector(response);
    					
    				}
                    
    });
    
});
function addFileNotify ( queueSize ) {
	$(\'#filenotify\').html( \'Вы успешно загрузили: \' + queueSize + \' файл(ов)\' );
}
function addFileInCollector ( filePath ) {
	var temp=new Array();
	temp=filePath.split(\'/\');
	var f=temp[temp.length-1];
	var cont=$(\'#filecollector\').html();
	cont=cont+\'<div><img src="\'+filePath+\'" /><a class="kill_upl btn btn-mini btn-danger" href="javascript:void(0)" alt="\'+f+\'">X</a></div>\';
	$(\'#filecollector\').html(cont);
	
}

$(document).ready(function() {
	$(document).on(\'click\', \'a.kill_upl\',function(){
	//$(\'a.kill_upl\').live(\'click\',function(){
		var imgs_count=$("div.preview_admin").length+$("#filecollector img").length;
		var max_item_count='.((int)$this->getConfigValue('photo_per_data')>0 ? (int)$this->getConfigValue('photo_per_data') : 1000).';
		var url=\'/js/ajax.php?action=delete_uploadify_image&img_name=\'+$(this).attr(\'alt\');
		$.getJSON(url,{},function(data){
		
		});
		var parent=$(this).parent(\'div\');
		parent.html(\'\');
		parent.remove();
		imgs_count--;
		if(imgs_count<max_item_count){
    		$(\'#file_uploadUploader\').show();
		}
	});
	//$.getJSON(\'/js/ajax.php?action=delete_uploadify_image&img_name=\'+file,{},function(data){
		
});






</script>
<input id="file_upload" name="file_upload" type="file" />
<div id="filenotify"></div>
<div id="filecollector"></div>
        ';
        return $rs;
    }
    
    function getDropzonePlugin ( $session_code, $params=array() ) {
    	$element=$params['element'];
    	    	
    	$this->clear_uploadify_table($session_code);
    	$id='dz_'.md5(time().rand(100, 999));
    	$Dropzone_name='Dropzone_'.md5(time().rand(100, 999));
    	
    	if((int)$params['min_img_count']!=0){
    		$src='var formsubmit=$("#'.$id.'").parents("form").eq(0).find("[name=submit]");
					var vm=formsubmit.data("valid_me");
					if(vm === undefined){
						vm=[];
					}
					vm.push({id:"'.$id.'", count:'.(int)$params['min_img_count'].'});
					formsubmit.data("valid_me", vm);';
    	}else{
    		$src='';
    	}
    	
    	
    	$rs.='<script>
    			
    			$(document).ready(function(){
    				var '.$Dropzone_name.' = new Dropzone("div#'.$id.'", 
    				{ 
    					maxFilesize: '.$params['max_file_size'].',
						url: "'.SITEBILL_MAIN_URL.'/apps/system/js/uploadify/uploadify.php?uploader_type=dropzone&session='.$session_code.'&element='.$element.'",
	    				addRemoveLinks: true
					}); 
					'.$src.' 
					'.$Dropzone_name.'.on("complete", function(){
    						if(this.getQueuedFiles().length==0 && this.getUploadingFiles().length==0){
    							var form=$(this.element).parents("form");
    							form.find("[name=submit]").prop("disabled", false);	
										
    						}
    
    				}).on("success", function(file, responce) {
							if(responce.status=="error"){
								$(file.previewElement).remove();
								'.$Dropzone_name.'_quenue--;
							}else{
								var rem=$(file.previewElement).find(".dz-remove");
								var temp=new Array();
								temp=responce.msg.split(\'/\');
								var file_name=temp[temp.length-1];
								rem.attr("alt", file_name);
								rem.on("click", function(){
    								var url="'.SITEBILL_MAIN_URL.'/js/ajax.php?action=delete_uploadify_image&img_name="+$(this).attr("alt");
									$.getJSON(url,{},function(data){});
    							});
							}
    						
    				}).on("addedfile", function(file){
    					var form=$(this.element).parents("form");
    					form.find("[name=submit]").prop("disabled", true);	
    											
    				});
				});
				</script>';
    	$rs.='<div class="dropzone_outer"><div id="'.$id.'" class="dropzone_inner"><div class="dz-default dz-message"><span><span class="bigger-50 bolder"><i class="icon-caret-right red"></i> Переместите сюда файлы</span> для загрузки 				<span class="smaller-80 grey">(или кликните)</span> <br> 				<i class="upload-icon icon-cloud-upload blue icon-3x"></i></span></div></div></div>';
    	
    	return $rs;
    }
    
    /**
     * Get uploadify plugin
     * @param string $session_code session code
     * @return string
     */
    function getUploadifyFilePlugin ( $session_code, $params=array() ) {
    	$this->clear_uploadify_table($session_code);
    	$id=md5(time().rand(1000,9999));
        global $folder;
        
        $rs = '';
        $rs .= '
<link href="'.$folder.'/apps/system/js/uploadify/uploadify.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="'.$folder.'/apps/system/js/uploadify/jquery.uploadify.js"></script>
<script type="text/javascript">
var uploadedfiles = 0;
var maxQueueSize = 100;
var queueSize = 0;
$(document).ready(function() {
  $(\'#'.$id.'\').uploadify({
    \'swf\'  : \''.$folder.'/apps/system/js/uploadify/uploadify.swf\',
    \'uploader\'    : \''.$folder.'/apps/system/js/uploadify/uploadify.php?file=1&session='.$session_code.'\',
    \'cancelImg\' : \''.$folder.'/apps/system/js/uploadify/uploadify-cancel.png\',
    \'folder\'    : \''.$folder.'/cache/upl\',
    \'auto\'      : true,
	\'fileTypeExts\': \'*.doc;*.pdf;*.zip\',
	\'multi\': true,	
	\'queueSizeLimit\': 100,
	\'buttonText\': \''.((isset($params['button_name']) && $params['button_name']!='') ? $params['button_name'] : Multilanguage::_('L_FILE')).'\',	
	\'buttonImg\': \''.$folder.'/img/button_img_upl.png\',	
    \'onUploadSuccess\': function(fileObj, response, data) {
    					queueSize++;
    					if ( response == \'max_file_size\' ) {
    						alert(\''.Multilanguage::_('L_MESSAGE_MAX_UPL_SIZE').' '.ini_get('upload_max_filesize').' \');
    						return false;
    					}
    					if ( response == \'wrong_ext\' ) {
    						alert(\''.Multilanguage::_('L_MESSAGE_AVIALABLE_EXTS').' png, jpg, tif, jpeg, doc,docx, xls, xlsx, pdf, txt, zip, rar\');
    						return false;
    					}
    					if ( queueSize > maxQueueSize ) {
    						alert(\''.Multilanguage::_('L_MESSAGE_MAX_FILES_COUNT').'\');
    						return false;
    					}
    					addFileNotify(queueSize);
    				}
                    
    });
});
function addFileNotify ( queueSize ) {
	$(\'#filenotify\').html( \'Вы успешно загрузили: \' + queueSize + \' файл(ов)\' );
}
</script>
<input id="'.$id.'" name="file_upload" type="file" />
<div id="filenotify"></div>
        ';
        return $rs;
    }
    
    
    /**
     * Is demo
     * @param void
     * @return boolean
     */
    function isDemo () {
        global $__user, $__db;
        if ( preg_match('/rumantic_estate/', $__db) ) {
            return true;
        }
        return false;
    }
    
    /**
     * Demo function disabled
     * @param void
     * @return string
     */
    function demo_function_disabled () {
        return Multilanguage::_('L_MESSAGE_THIS_IS_TRIAL_COMMON');
    }
    
    
    /**
     * Load config
     * @param
     * @return
     */
    function loadConfig () {
    	self::$config_array['per_page'] = 25;
    	self::$config_array['site_title'] = 'Агентство недвижимости';
    	 
    	self::$config_array['news_image_big_width'] = 350;
    	self::$config_array['news_image_big_height'] = 350;
    	 
    	self::$config_array['news_image_preview_width'] = 200;
    	self::$config_array['news_image_preview_height'] = 200;
    
    	self::$config_array['gallery_image_big_width'] = 800;
    	self::$config_array['gallery_image_big_height'] = 600;
    	 
    	self::$config_array['gallery_image_preview_width'] = 200;
    	self::$config_array['gallery_image_preview_height'] = 200;
    	 
    	self::$config_array['auto_image_big_width'] = 800;
    	self::$config_array['auto_image_big_height'] = 600;
    	 
    	self::$config_array['auto_image_preview_width'] = 200;
    	self::$config_array['auto_image_preview_height'] = 200;
    	 
    	self::$config_array['data_image_big_width'] = 1000;
    	self::$config_array['data_image_big_height'] = 800;
    	 
    	self::$config_array['data_image_preview_width'] = 300;
    	self::$config_array['data_image_preview_height'] = 300;
    	 
    	self::$config_array['shop_product_image_big_width'] = 800;
    	self::$config_array['shop_product_image_big_height'] = 600;
    	 
    	self::$config_array['shop_product_image_preview_width'] = 200;
    	self::$config_array['shop_product_image_preview_height'] = 200;
    	 
    	self::$config_array['vendor_image_big_width'] = 800;
    	self::$config_array['vendor_image_big_height'] = 600;
    	 
    	self::$config_array['vendor_image_preview_width'] = 50;
    	self::$config_array['vendor_image_preview_height'] = 50;
    	 
    	
    	
    	self::$config_array['topic_image_big_width'] = 800;
    	self::$config_array['topic_image_big_height'] = 600;
    	
    	self::$config_array['topic_image_preview_width'] = 200;
    	self::$config_array['topic_image_preview_height'] = 200;
    
    	//if ( !$this->config_loaded ) {
    	if ( !self::$config_loaded ) {
    		$DBC=DBC::getInstance();
    		$query='SELECT * FROM '.DB_PREFIX.'_config ORDER BY config_key';
    		$stmt=$DBC->query($query);
    		if ( $stmt ) {
    			while($ar=$DBC->fetch($stmt)){
    				self::$config_loaded = true;
    				self::$config_array[$ar['config_key']] = $ar['value'];
    			}
    		}
    		if(isset(self::$config_array['apps.realty.data_image_preview_width'])){
    			self::$config_array['data_image_preview_width'] = self::$config_array['apps.realty.data_image_preview_width'];
    		}
    		 
    		if(isset(self::$config_array['apps.realty.data_image_preview_height'])){
    			self::$config_array['data_image_preview_height'] = self::$config_array['apps.realty.data_image_preview_height'];
    		}
    		 
    		if(isset(self::$config_array['apps.realty.data_image_big_width'])){
    			self::$config_array['data_image_big_width'] = self::$config_array['apps.realty.data_image_big_width'];
    		}
    		 
    		if(isset(self::$config_array['apps.realty.data_image_big_height'])){
    			self::$config_array['data_image_big_height'] = self::$config_array['apps.realty.data_image_big_height'];
    		}
    		
    		if(isset($_SESSION['user_domain_owner']) && isset($_SESSION['user_domain_owner']['theme']) && $_SESSION['user_domain_owner']['theme']!=''){
    			self::$config_array['theme'] = $_SESSION['user_domain_owner']['theme'];
    		}
    		
    	}
    }
    
    
    /**
     * Delete image
     * @param string $table_name table name
     * @param int $image_id image id
     * @return boolean
     */
    function deleteImage ( $table_name, $image_id ) {
    	$DBC=DBC::getInstance();
    	//delete records from land_image
    	$query = 'DELETE FROM '.DB_PREFIX.'_'.$table_name.'_image WHERE image_id=?';
    	$DBC->query($query, array($image_id));
    	
    	//delete image files
    	$this->deleteImageFiles( $image_id );
    	
    	//delete image records
    	$query = 'DELETE FROM '.IMAGE_TABLE.' WHERE image_id=?';
    	$DBC->query($query, array($image_id));
    	return true;    	
    }
    
    function makeImageMain($action, $image_id, $key, $key_value){
    	$DBC=DBC::getInstance();
    	$query = 'SELECT image_id FROM '.DB_PREFIX.'_'.$action.'_image WHERE `'.$key.'`=? ORDER BY sort_order';
    	$stmt=$DBC->query($query, array($key_value));
    	$imgs=array();
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$imgs[]=$ar['image_id'];
    		}
    	}
    	
    	if(!empty($imgs)){
    		$imgids=array_flip($imgs);
    		if(isset($imgids[$image_id])){
    			unset($imgs[$imgids[$image_id]]);
    			array_unshift($imgs, $image_id);
    		}
    		$query='UPDATE '.DB_PREFIX.'_'.$action.'_image SET sort_order=? WHERE image_id=?';
    		foreach($imgs as $k=>$v){
    			$DBC->query($query, array($k+1, $v));
    		}
    	}
    }
    
    /**
     * Reorder image
     * @param $action
     * @param $image_id
     * @param $key 
     * @param $key_value
     * @param $direction
     * @return mixed
     */
    /*
    function reorderImage($action, $image_id, $key, $key_value, $direction) {
    	//echo $action.' '.$image_id.' '.$key.' '.$key_value.' '.$direction;
    	$DBC=DBC::getInstance();
    	//get current image info
    	$query = 'SELECT '.$action.'_image_id, sort_order FROM '.DB_PREFIX.'_'.$action.'_image WHERE image_id=?';
    	//echo $image_id;
    	$stmt=$DBC->query($query, array($image_id));
    	$rr=$DBC->fetch($stmt);
    	
    	$record_image_id=$rr[$action.'_image_id'];
    	
    	$sort_order = $rr['sort_order'];
    	
    	if ( $direction == 'down' ) {
	    	//get next image id
    		$query = 'SELECT '.$action.'_image_id, sort_order FROM '.DB_PREFIX.'_'.$action.'_image WHERE sort_order > ? AND `'.$key.'` = ? ORDER BY sort_order ASC LIMIT 1';
    		//echo $query;
    		$stmt=$DBC->query($query, array($sort_order, $key_value));
    		if($stmt){
    			$ar=$DBC->fetch($stmt);
    			$next_record_image_id = $ar[$action.'_image_id'];
    			$next_sort_order = $ar['sort_order'];
    			//echo $next_record_image_id.' '.$next_sort_order;
    			
    			$query = 'UPDATE '.DB_PREFIX.'_'.$action.'_image SET sort_order=? WHERE '.$action.'_image_id=?';
    			$stmt=$DBC->query($query, array($next_sort_order, $record_image_id));
    			$stmt=$DBC->query($query, array($sort_order, $next_record_image_id));
    		}
    		
    		
    	}
    	
    	if ( $direction == 'up' ) {
    		//print_r($rr);
	    	//get next image id
    		$query = 'SELECT '.$action.'_image_id, sort_order FROM '.DB_PREFIX.'_'.$action.'_image WHERE sort_order < ? AND `'.$key.'` = ? ORDER BY sort_order ASC LIMIT 1';
    		//echo $query;
    		$stmt=$DBC->query($query, array($sort_order, $key_value));
    		if($stmt){
    			$ar=$DBC->fetch($stmt);
    			print_r($rr);
    			print_r($ar);
    			$next_record_image_id = $ar[$action.'_image_id'];
    			$next_sort_order = $ar['sort_order'];
    			 
    			$query = 'UPDATE '.DB_PREFIX.'_'.$action.'_image SET sort_order=? WHERE '.$action.'_image_id=?';
    			$stmt=$DBC->query($query, array($next_sort_order, $record_image_id));
    			 
    			//$query = 'UPDATE '.DB_PREFIX.'_'.$action.'_image SET sort_order=? WHERE '.$action.'_image_id=?';
    			$stmt=$DBC->query($query, array($sort_order, $next_record_image_id));
    		}
    		
    	}
    	
    	//get next image
    	
    }
    */
    
    function reorderImage($action, $image_id, $key, $key_value, $direction) {
    	$DBC=DBC::getInstance();
    	$query = 'SELECT '.$action.'_image_id, sort_order FROM '.DB_PREFIX.'_'.$action.'_image WHERE image_id=?';
    	$stmt=$DBC->query($query, array($image_id));
    	$rr=array();
    	if(!$stmt){
    		return;
    	}
    	$rr=$DBC->fetch($stmt);
   		$record_image_id = $rr[$action.'_image_id'];
    	$sort_order = $rr['sort_order'];
    	
    	if ( $direction == 'down' ) {
    		$query = 'SELECT '.$action.'_image_id, sort_order FROM '.DB_PREFIX.'_'.$action.'_image WHERE sort_order > ? AND `'.$key.'` = ? ORDER BY sort_order ASC';
    		$stmt=$DBC->query($query, array($sort_order, $key_value));
    		if(!$stmt){
    			return;
    		}
    		$rr = $DBC->fetch($stmt);
    		$next_record_image_id = (int)$rr[$action.'_image_id'];
    		if ( $next_record_image_id==0 ) {
    			return;
    		}
    		$next_sort_order = $rr['sort_order'];
    		
    		$query = 'UPDATE '.DB_PREFIX.'_'.$action.'_image SET sort_order=? WHERE '.$action.'_image_id=?';
    		$stmt=$DBC->query($query, array($next_sort_order, $record_image_id));
    		 
    		$query = 'UPDATE '.DB_PREFIX.'_'.$action.'_image SET sort_order=? WHERE '.$action.'_image_id=?';
    		$stmt=$DBC->query($query, array($sort_order, $next_record_image_id));
    	}
    	 
    	if ( $direction == 'up' ) {
    		$query = 'SELECT '.$action.'_image_id, sort_order FROM '.DB_PREFIX.'_'.$action.'_image WHERE sort_order < ? AND `'.$key.'` = ? ORDER BY sort_order DESC';
    		$stmt=$DBC->query($query, array($sort_order, $key_value));
    		if(!$stmt){
    			return;
    		}
    		$rr = $DBC->fetch($stmt);
    		$next_record_image_id = (int)$rr[$action.'_image_id'];
    		if ( $next_record_image_id==0 ) {
    			return;
    		}
    		$next_sort_order = $rr['sort_order'];
    		$query = 'UPDATE '.DB_PREFIX.'_'.$action.'_image SET sort_order=? WHERE '.$action.'_image_id=?';
    		$stmt=$DBC->query($query, array($next_sort_order, $record_image_id));
    		 
    		$query = 'UPDATE '.DB_PREFIX.'_'.$action.'_image SET sort_order=? WHERE '.$action.'_image_id=?';
    		$stmt=$DBC->query($query, array($sort_order, $next_record_image_id));
    	}
    }
    
    function reorderTopics($orderArray){
    	if(count($orderArray)>0){
    		$DBC=DBC::getInstance();
    		$query='UPDATE '.DB_PREFIX.'_topic SET `order`=? WHERE id=?';
    		foreach($orderArray as $k=>$v){
    			$DBC->query($query, array((int)$v, (int)$k));
    		}
    	}
    }
    
    /**
     * Delete image files
     * @param $image_id image id
     * @return boolean
     */
    function deleteImageFiles( $image_id ) {
    	$path = SITEBILL_DOCUMENT_ROOT.$this->storage_dir;
    	$DBC=DBC::getInstance();
    	$query = 'SELECT * FROM '.IMAGE_TABLE.' WHERE image_id=?';
    	$stmt=$DBC->query($query, array((int)$image_id));
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$preview = $ar['preview'];
    			$normal = $ar['normal'];
    			@unlink($path.$preview);
    			@unlink($path.$normal);
    		}
    	}
    	return true;
    }

    /**
     * Get config value
     * @param string $key key
     * @return string
     */
    function getConfigValue ( $key ) {
        //echo '<b>need config key = '.$key.'</b><br>';
    	if ( !self::$config_loaded ) {
    	    //echo '<b>load config</b><br>';
    		$this->loadConfig();
    	}
    	if ( isset(self::$config_array[$key]) ) {
    	    //echo "cfg: $key = ".$this->config_array[$key]."<br>";
    		return self::$config_array[$key];
    	}
    	return false;
    }
    
    function getAllConfigArray () {
    	return self::$config_array;
    }
    
    /*function setConfigValue ( $key, $value ) {
    	if ( !$this->config_loaded ) {
    		$this->loadConfig();
    	}
    	$this->config_array[$key]=$value;
    }*/
    
    /**
     * Get debug mode
     * @param void
     * @return boolean
     */
    function getDebugMode() {
        return DEBUG_MODE;
    }
    
    /**
     * Set debug mode 
     * @param boolean
     * @return void
     */
    function setDebugMode ( $debug_mode ) {
        return;
    }
    
    function htmlspecialchars($value, $flags=''){
    	if($flags==''){
    		$flags=ENT_COMPAT | ENT_HTML401;
    	}
    	if(is_array($value)){
    		if(count($value)>0){
    			foreach ($value as $ak=>$av){
    				if(is_array($av)){
    					$value[$ak]=$this->htmlspecialchars($av);
    				}else{
    					$value[$ak]=htmlspecialchars($av, $flags, SITE_ENCODING);
    				}
    			}
    		}
    	}else{
    		$value=htmlspecialchars($value, $flags, SITE_ENCODING);
    	}
    	return $value;
    }
    
    function htmlspecialchars_decode($value, $flags=''){
    	if($flags==''){
    		$flags=ENT_COMPAT | ENT_HTML401;
    	}
    	if(is_array($value)){
    		if(count($value)>0){
    			foreach ($value as $ak=>$av){
    				if(is_array($av)){
    					$value[$ak]=$this->htmlspecialchars_decode($av);
    				}else{
    					$value[$ak]=htmlspecialchars_decode($av, $flags);
    				}
    			}
    		}
    	}else{
    		$value=htmlspecialchars_decode($value, $flags);
    	}
    	return $value;
    }
    
    /**
     * Get value
     * @param string $key key
     * @return string
     */
    function getRequestValue( $key, $type='', $from='' ) {
    	$value=NULL;
    	switch($from){
    		case 'get' : {
    			if(isset($_GET[$key])){
    				$value=$_GET[$key];
    				$value=htmlspecialchars($_GET[$key]);
    			}
    			break;
    		}
    		case 'post' : {
    			if(isset($_POST[$key])){
    				$value=$_POST[$key];
    			}
    			break;
    		}
    		default : {
    			if(isset($_GET[$key])){
    				$value=$_GET[$key];
    				//$value=$this->xssProtect($_GET[$key]);
    				//$value=strip_tags($_GET[$key]);
    				if(is_array($value)){
    					$value=$this->htmlspecialchars($value);
    					/*foreach ($value as $k=>$v){
    						$value[$k]=htmlspecialchars($v);
    					}*/
    				}else{
    					$value=htmlspecialchars($value);
    				}
    				
    			}elseif(isset($_POST[$key])){
    				$value=$_POST[$key];
    				//echo '<pre>';
    				//echo $key;
    				//print_r($value);
    				if(is_array($value)){
    					/*foreach ($value as $k=>$v){
    						$value[$k]=htmlspecialchars($v);
    					}*/
    					$value=$this->htmlspecialchars($value);
    				}else{
    					$value=htmlspecialchars($value);
    				}
    				//echo '</pre>';
    			}
    		}
    	}
    	
    	if($value===NULL){
    		return $value;
    	}
    	
    	//$value=preg_replace('/<script>/', '', $value);
    	
    	
    	if ( !is_array($value) ) {
    		$value=trim($value);
    			$value=$this->getSafeValue($value);
    			if ( preg_match('/union/i', $value) ) {
    				return NULL;
    			}
    			if ( preg_match('/left\sjoin/i', $value) ) {
    				return NULL;
    			}
    			
    			if ( preg_match('/sleep[\s]*\(/i', $value) ) {
    				return NULL;
    			}
    			 
    			if ( preg_match_all('/select/i', $value, $matches)) {
    				if(count($matches[0])>1){
    					return NULL;
    				}
    			}
    			return $value;
    			//return $this->getSafeValue($value);
    			//return str_replace('\'', '', $value);
    		}elseif(is_array($value)){
    			$values=$value;
    			foreach($values as $k=>$v){
    				if(!is_array($v)){
    					$v=trim($v);
    					$v=$this->getSafeValue($v);
    					if($v==='' || preg_match('/union/i', $v) || preg_match('/select/i', $v) || preg_match('/left\sjoin/i', $v) || preg_match('/sleep[\s]*\(/i', $v)){
    						unset($values[$k]);
    					}else{
    						$values[$k]=$v;
    					}
    				}
    			}
    			if(count($values)==0){
    				return array();
    			}else{
    				return $values;
    			}
    		}
    		
    	switch($type){
    		case 'int' : {
    			if(!is_array($value)){
    				$value=(int)$value;
    			}else{
    				$value=0;
    			}
    			
    			break;
    		}
    		case 'bool' : {
    			$value=(bool)$value;
    			break;
    		}
    		case 'float' : {
    			$value=preg_replace('/[^\d\.,]/', '', $value);
    			break;
    		}
    	}
    		
    	return $value;
    }
    
    private function xssProtect($value){
    	if(is_array($value)){
    		foreach ($value as $k=>$v){
    			$value[$k]=htmlspecialchars($v);
    		}
    	}else{
    		$value=htmlspecialchars($value);
    	}
    	return $value;
    }
    
    private function getSafeValue($value){
    	return preg_replace('/(\/\*[^\/]*\*\/)/', '', $value);
    }
    
    /**
     * Set request value
     * @param string $key key
     * @param string $value value
     * @return void
     */
    function setRequestValue ( $key, $value ) {
        $_POST[$key] = $value;
        return;
    }

    /**
     * Rise error 
     * @param string $error error message
     * @return void
     */
    function riseError ( $error_message ) {
        $this->error_message = $error_message;
        $this->error_state = true;
    }
    
    /**
     * Get error 
     * @param void
     * @return boolean
     */
    function getError ( ) {
        return $this->error_message;
    }
    
    /**
     * Get error message
     * @param void
     * @return string
     */
    function GetErrorMessage () {
        return $this->error_message;
    }
    
    /**
     * Write log message
     * @param string $message message
     * @return void
     */
    function writeLog ( $message ) {
    	if ( $this->getConfigValue('apps.logger.enable') ) {
    		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
    		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/logger/admin/admin.php');
    		if ( is_array($message) ) {
    			logger_admin::write_log($message);
    		} else {
    			$message_array = array('apps_name' => '', 'method' => '', 'message' => $message, 'type' => '' );
    			logger_admin::write_log($message_array);
    		}
    		return;
    	}
        global $debug_log;
        if ( !preg_match('/Z:\/home/',SITEBILL_DOCUMENT_ROOT) ) {
            return;
        }
        if (!$handle = @fopen($debug_log, 'a')) {
            //echo "Cannot open error log file ($debug_log)";
            return;
        }
        $error_message = date("Y-m-d H:i:s ").$message."\n";
        if (fwrite($handle, $error_message) === FALSE) {
            //echo "Cannot write to erro log file ($debug_log)";
            return;
        }
        fclose($handle);
        return;
    }
    
    /**
     * Get image list admin
     * @param string $action action
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record id
     * @return string
     */
	function getImageListAdmin ( $action, $table_name, $key, $record_id, &$callback_count=NULL ) {
		global $__db_prefix;
    	if ( SITEBILL_MAIN_URL != '' ) {
    	    $url = SITEBILL_MAIN_URL.'/'.$this->storage_dir;
    	} else {
    	    $url = $this->storage_dir;
    	}
    	
    	$record_id=(int)$record_id;
    	
    	
    	//$query = "SELECT i.* FROM ".DB_PREFIX."_".$table_name."_image AS li, ".IMAGE_TABLE." AS i WHERE li.".$key."=$record_id AND li.image_id=i.image_id ORDER BY li.sort_order";
    	$query = 'SELECT i.* FROM '.DB_PREFIX.'_'.$table_name.'_image AS li, '.IMAGE_TABLE.' AS i WHERE li.'.$key.'=? AND li.image_id=i.image_id ORDER BY li.sort_order';
    	$DBC=DBC::getInstance();
    	$stmt=$DBC->query($query, array($record_id));
    	if($stmt){
    		$i=0;
    		$rs .= '<style>
    			.preview_admin { float: left; min-height: 250px; padding: 5px; margin: 5px; }
    			.preview_admin td > img { width: 100px; border: 1px solid #CFCFCF;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
	border-radius: 5px;
	margin-bottom: 5px;}
    
    			</style>';
    		$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/dataimagelist.js"></script>';
    		$rs .= '<script type="text/javascript">DataImagelist.attachDblclick();</script>';
    		while($ar=$DBC->fetch($stmt)){
    			
    			$rs .= '<div class="preview_admin">
    		<table border="0" id="data_gallery">';
    			if(isset($ar['title'])){
    				$rs .= '<tr><td class="field_tab" style="height:20px; border: 1px solid gray;" alt="'.$ar['image_id'].'">'.$ar['title'].'<td></tr>';
    			}
    			if(isset($ar['description'])){
    				$rs .= '<tr><td class="field_tab_description" style="height:20px; border: 1px solid gray;" alt="'.$ar['image_id'].'">'.$ar['description'].'<td></tr>';
    			}
    		
    			$rs .= '<tr>
    		<td>
    		<br />
    		<img src="'.$url.''.$ar['preview'].'" border="0" align="left"/><br>
    		</td>';
    			$rs.='</tr>';
    			$rs.='<tr>';
    			$rs.='<td>';
    			$rs .= '<a href="javascript:void(0);" onClick="DataImagelist.deleteImage(this,'.$ar['image_id'].','.$record_id.',\''.$table_name.'\',\''.$key.'\')"><img src="'.SITEBILL_MAIN_URL.'/apps/admin/admin/template/img/delete.png" width="16" border="0" alt="удалить" title="удалить"></a>
    		<a href="javascript:void(0);" onClick="DataImagelist.upImage(this,'.$ar['image_id'].','.$record_id.',\''.$table_name.'\',\''.$key.'\')"><img src="'.SITEBILL_MAIN_URL.'/img/up.gif" border="0" alt="наверх" title="наверх"></a>
    		<a href="javascript:void(0);" onClick="DataImagelist.downImage(this,'.$ar['image_id'].','.$record_id.',\''.$table_name.'\',\''.$key.'\')"><img src="'.SITEBILL_MAIN_URL.'/img/down1.gif" border="0" alt="вниз" title="вниз"></a>
    		<a href="javascript:void(0);" onClick="DataImagelist.makeMain(this,'.$ar['image_id'].','.$record_id.',\''.$table_name.'\',\''.$key.'\')">Сделать главной</a>
    		</td>
    		</tr>
    		</table>
    		</div>';
    			//$rs .= '<div style="clear: both;"></div>';
    			$i++;
    		}
    		if($callback_count!==NULL){
    			$callback_count=$i;
    		}
    	}
    	return $rs;
    }
    
    /**
     * Get file list admin
     * @param string $action action
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record id
     * @return string
     */
    function getFileListAdmin ( $action, $table_name, $key, $record_id ) {
    	if ( SITEBILL_MAIN_URL != '' ) {
    	    $url = SITEBILL_MAIN_URL.'/'.$this->storage_dir;
    	} else {
    	    $url = $this->storage_dir;
    	}
    	$record_id=(int)$record_id;
    	$DBC=DBC::getInstance();
    	$query = 'SELECT i.* FROM '.DB_PREFIX.'_'.$table_name.'_image AS li, '.IMAGE_TABLE.' AS i WHERE li.'.$key.'=? AND li.image_id=i.image_id ORDER BY li.sort_order';
    	$stmt=$DBC->query($query, array($record_id));
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			/*$up_link = '?action='.$action.'&do=edit&'.$key.'='.$record_id.'&subdo=up_image&image_id='.$ar['image_id'];
    			$down_link = '?action='.$action.'&do=edit&'.$key.'='.$record_id.'&subdo=down_image&image_id='.$ar['image_id'];
    			 
    			 
    			$up_link_img = '<a href="'.$up_link.'"><img src="'.SITEBILL_MAIN_URL.'/img/up.gif" border="0" alt="наверх" title="наверх"></a>';
    			$down_link_img = '<a href="'.$down_link.'"><img src="'.SITEBILL_MAIN_URL.'/img/down1.gif" border="0" alt="вниз" title="вниз"></a>';
    				*/
    			$delete_link = '?action='.$action.'&do=edit&'.$key.'='.$record_id.'&subdo=delete_image&image_id='.$ar['image_id'];
    			$rs .= '<div class="preview_admin" style="padding: 2px; border: 1px solid gray;">
    		<table border="0">
    		<tr>
    		<td>
    		<a href="'.$url.$ar['preview'].'" target="_blank"><img src="/img/file.png" border="0" align="left"/> '.$ar['preview'].'</a><br>
    		</td>
    		<td>
    		<a href="'.$delete_link.'" onclick="return confirm(\''.Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE').'\');">'.Multilanguage::_('L_DELETE_LC').'</a>
    		
    		</td>
    		</tr>
    		</table>
    		</div>';
    			$rs .= '<div style="clear: both;"></div>';
    		}
    	}
    	return $rs;
    }
    
    
	/**
	 * Get page links list
	 * @param int $cur_page current page number
	 * @param int $total 
	 * @param int $per_page
	 * @param array $params
	 * @return array
	 */
	function get_page_links_list ($page, $total, $per_page, $params ) {
	    if ( $total <= $per_page ) {
	        return '';
	    }
	    //echo $params['page_url'];
	    if(isset($params['page_url']) && $params['page_url']!=''){
	    	$url=SITEBILL_MAIN_URL.'/'.$params['page_url'];
	    	unset($params['page_url']);
	    }else{
	    	$url='';
	    }
	    
	    //print_r($params);
	    $pairs = array();
	    unset($params['page']);
	    if(count($params)>0){
		    foreach ( $params as $key => $value ) {
		        if(is_array($value)){
	        		if(count($value)>0){
	        			foreach($value as $v){
	        				if($v!=''){
	        					$pairs[] = $key.'[]='.$v;
	        				}
	        			}
	        		}
	        	}elseif ( $value != '' ) {
		            $pairs[] = "$key=$value";
		        }
		    }
	    }
	    //print_r($pairs);
	    //$url='';
	    
		if(count($pairs)>0){
	    	$url= $url.'?'.implode('&', $pairs);
	    }else{
	    	$url= $url;
	    }
	    
		
		//echo $total;
		$current_page =  $page;
		if($current_page==''){
			$current_page=1;
		}else{
			$current_page=(int)$current_page;
		}
		
	    $limit = $per_page;
		
		$total_pages=ceil($total/$limit);
		$page_navigation='';
		$first_page_navigation='';
		$last_page_navigation='';
		$start_page_navigation='';
		$end_page_navigation='';
		$p_prew=$current_page-1;
		$p_next=$current_page+1;
		
	
		
		$last_number_page='<li><a rel="nofollow" href="'.$url.(false!==strpos($url, '?') ? '&page='.$total_pages : '?page='.$total_pages).'" class="pagenav"><strong>'.$total_pages.'</strong></a></li>';
		
		if($current_page==1){
			$first_page_navigation.='<li><span class="pagenav">&laquo;&laquo; </span></li>';
		}else{
			$first_page_navigation.='<li><a rel="nofollow" href="'.$url.(false!==strpos($url, '?') ? '&page=1' : '?page=1').'" class="pagenav" title="в начало">&laquo;&laquo; </a></li>';
		}
		
		if($current_page==$total_pages){
			$last_page_navigation.='<li><span class="pagenav"> &raquo;&raquo;</span></li>';
			$last_number_page='';
		}else{
			$last_page_navigation.='<li><a rel="nofollow" href="'.$url.(false!==strpos($url, '?') ? '&page='.$total_pages : '?page='.$total_pages).'" class="pagenav" title="в конец"> &raquo;&raquo;</a></li>';
		}
		
		if($p_prew<1){
			$start_page_navigation.='<li><span class="pagenav">&laquo; </span></li>';
		}else{
			$start_page_navigation.='<li><a rel="nofollow" href="'.$url.(false!==strpos($url, '?') ? '&page='.$p_prew : '?page='.$p_prew).'" class="pagenav" title="предыдущая">&laquo; </a></li>';
		}
		
		if($p_next>$total_pages){
			$end_page_navigation.='<li><span class="pagenav"> &raquo;</span></li>';
		}else{
			$end_page_navigation.='<li><a rel="nofollow" href="'.$url.(false!==strpos($url, '?') ? '&page='.$p_next : '?page='.$p_next).'" class="pagenav" title="следующая"> &raquo;</a></li>';
		}
		
		
		$linestart=$current_page-7;
		$lineend=$current_page+7;
		
		if($linestart<=1){
			$linestart=1;
			$lineprefix='';
		}else{
			$lineprefix='<li>...</li>';
		}
		
		if($lineend>=$total_pages){
			$lineend=$total_pages;
			$last_number_page='';
			$linepostfix='';
		}
		else{
			$linepostfix='<li>...</li>';
		}
		
		
		
		//for($i=1;$i<=$total_pages;$i++){
		for($i=$linestart;$i<=$lineend;$i++){
			if($current_page==$i){
				$page_navigation.='<li><span class="pagenav"> '.$i.' </span></li>';
			}else{
				$page_navigation.='<li><a rel="nofollow" href="'.$url.(false!==strpos($url, '?') ? '&page='.$i : '?page='.$i).'" class="pagenav"><strong>'.$i.'</strong></a></li>';
			}
		}
		$page_navigation='<ul class="pagination">'.$first_page_navigation.$start_page_navigation.$lineprefix.$page_navigation.$linepostfix.$end_page_navigation.$last_number_page.$last_page_navigation.'</ul>';
	    return $page_navigation;
	}
    
    /**
     * Get image list admin
     * @param string $action action
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record id
     * @param int $limit limit value
     * @return string
     */
    function get_image_array ( $action, $table_name, $key, $record_id, $limit = 0 ) {
    	$DBC=DBC::getInstance();
    	$url = $this->storage_dir;
    	$ra=array();
    	$record_id=(int)$record_id;
    	$query = 'SELECT i.* FROM '.DB_PREFIX.'_'.$table_name.'_image AS li, '.IMAGE_TABLE.' AS i WHERE li.'.$key.'=? AND li.image_id=i.image_id ORDER BY li.sort_order';
    	
    	if ( $limit > 0 ) {
    		$query .= ' LIMIT ?';
    	}
    	
    	
    	if ( $limit > 0 ) {
    		$stmt=$DBC->query($query, array($record_id, $limit));
    	}else{
    		$stmt=$DBC->query($query, array($record_id));
    	}
    	
    	if($stmt){
    		$i = 0;
    		while($ar=$DBC->fetch($stmt)){
    			$ra[$i]['preview'] =  $ar['preview'];
    			$ra[$i]['normal'] =  $ar['normal'];
    				
    			$ra[$i]['title'] =  $ar['title'];
    			$ra[$i]['description'] =  $ar['description'];
    		
    			$ra[$i]['img_preview'] =  $url.''.$ar['preview'];
    			$ra[$i]['img_normal'] =  $url.''.$ar['normal'];
    			$i++;
    		}
    	}
    	
    	return $ra;
    }
    
    
    /**
     * Get category breadcrumbs
     * @param array $params
     * @param array $category_structure
     * @param string $url
     * @return string
     */
    function get_category_breadcrumbs( $params, $category_structure, $url = '' ) {
        $rs = '';
        
        if ( !isset($params['topic_id']) || is_array($params['topic_id']) ) {
            return $rs;
        }
        //foreach ( $category_structure['childs'][0] as $item_id => $catalog_id ) {
        if($category_structure['catalog'][$params['topic_id']]['url']!=''){
        	$ra[] = '<a rel="nofollow" href="'.rtrim($url,'/').'/'.$category_structure['catalog'][$params['topic_id']]['url'].'">'.$category_structure['catalog'][$params['topic_id']]['name'].'</a>';
        }else{
        	$ra[] = '<a rel="nofollow" href="'.rtrim($url,'/').'/topic'.$params['topic_id'].'.html">'.$category_structure['catalog'][$params['topic_id']]['name'].'</a>';
        }
        
        $parent_category_id = $category_structure['catalog'][$params['topic_id']]['parent_id'];
        while ( $category_structure['catalog'][$parent_category_id]['parent_id'] != 0 ) {
            if ( $j++ > 100 ) {
                return;
            }
	        if($category_structure['catalog'][$parent_category_id]['url']!=''){
	        	$ra[] = '<a rel="nofollow" href="'.rtrim($url,'/').'/'.$category_structure['catalog'][$parent_category_id]['url'].'">'.$category_structure['catalog'][$parent_category_id]['name'].'</a>';
	        }else{
	        	$ra[] = '<a rel="nofollow" href="'.rtrim($url,'/').'/topic'.$parent_category_id.'.html">'.$category_structure['catalog'][$parent_category_id]['name'].'</a>';
	        }
            $parent_category_id = $category_structure['catalog'][$parent_category_id]['parent_id'];
        }
        if ( $category_structure['catalog'][$parent_category_id]['name'] != '' ) {
       	 	if($category_structure['catalog'][$parent_category_id]['url']!=''){
	        	$ra[] = '<a rel="nofollow" href="'.rtrim($url,'/').'/'.$category_structure['catalog'][$parent_category_id]['url'].'">'.$category_structure['catalog'][$parent_category_id]['name'].'</a>';
	        }else{
	        	$ra[] = '<a rel="nofollow" href="'.rtrim($url,'/').'/topic'.$parent_category_id.'.html">'.$category_structure['catalog'][$parent_category_id]['name'].'</a>';
	        }
        }
      	$ra[]='<a rel="nofollow" href="'.SITEBILL_MAIN_URL.'/">'.Multilanguage::_('L_HOME').'</a>';
        $rs = implode(' / ', array_reverse($ra));
        return $rs;
    }
    
    /**
     * Get category breadcrumbs
     * @param array $params
     * @param array $category_structure
     * @param string $url
     * @return string
     */
    function get_category_breadcrumbs_string( $params, $category_structure, $url = '' ) {
        $rs = '';
        $ra[] = ''.$category_structure['catalog'][$params['topic_id']]['name'].'';
        $parent_category_id = $category_structure['catalog'][$params['topic_id']]['parent_id'];
        while ( isset($category_structure['catalog'][$parent_category_id]['parent_id']) && $category_structure['catalog'][$parent_category_id]['parent_id'] != 0 ) {
            if ( $j++ > 100 ) {
                return;
            }
            $ra[] = ''.$category_structure['catalog'][$parent_category_id]['name'].'';
            $parent_category_id = $category_structure['catalog'][$parent_category_id]['parent_id'];
        }
        if ( isset($category_structure['catalog'][$parent_category_id]['name']) && $category_structure['catalog'][$parent_category_id]['name'] != '' ) {
            $ra[] = ''.$category_structure['catalog'][$parent_category_id]['name'].'';
        }
        $rs = implode(' / ', array_reverse($ra));
        return $rs;
    }
    
    /**
     * Make preview
     * @param
     * @return
     */
    function makePreview ( $src, $dst, $width, $height, $ext='jpg', $md=0 ) {
    	
        if ($ext=='jpg' || $ext=='jpeg'){
        	$source_img=@ImageCreateFromJPEG($src);
        } elseif ($ext=='png') {
        	$source_img=@ImageCreateFromPNG($src);
        } elseif ($ext=='gif') {
        	$source_img=@ImageCreateFromGIF($src);
        }
        
		if($source_img===false){
			return false;
		}
          
        $w_src=imagesx($source_img);
        $h_src=imagesy($source_img);
        if ($w_src>$h_src) {$mode='width';}else{$mode='height';}
        if ($md=='height') {$mode='height';}
        if ($md=='width') {$mode='width';}
        if ($md=='smart') {$mode='smart';}
        if ($md=='c' || $md=='f') {$mode=$md;}
        
        if($mode=='smart' || $mode=='c'){
        	$source_width=$w_src;
        	$source_height=$h_src;
        	
        	$dest_width=$width;
        	$dest_height=$height;
        	
        	$width_proportion=$source_width/$dest_width;
        	$height_proportion=$source_height/$dest_height;
        	
        	if($width_proportion<$height_proportion){
        		$common_proportion=$width_proportion;
        	}else{
        		$common_proportion=$height_proportion;
        	}
        	
        	$equal_width=$dest_width*$common_proportion;
        	$equal_height=$dest_height*$common_proportion;
        	
        	
        	$width_offset=intval(($source_width-$equal_width)/2);
        	$height_offset=intval(($source_height-$equal_height)/2);
        	
        	$tmp_img=imageCreateTrueColor($dest_width, $dest_height);
        	imageAlphaBlending($tmp_img, false);
        	imageSaveAlpha($tmp_img, true);
        	imageCopyResampled($tmp_img, $source_img, 0, 0, $width_offset, $height_offset, $dest_width, $dest_height, ($equal_width), ($equal_height));
        	
        }elseif($mode=='f'){
        	$source_width=$w_src;
        	$source_height=$h_src;
        	 
        	$dest_width=$width;
        	$dest_height=$height;
        	
        	
        	 
        	$width_proportion=$source_width/$dest_width;
        	$height_proportion=$source_height/$dest_height;
        	 
        	if($width_proportion>$height_proportion){
        		$common_proportion=$width_proportion;
        	}else{
        		$common_proportion=$height_proportion;
        	}
        	 
        	$equal_width=$source_width/$common_proportion;
        	$equal_height=$source_height/$common_proportion;
        	 
        	/*echo $source_width, '=', $source_height, '<br>';
        	echo $dest_width, '=', $dest_height, '<br>';
        	echo $width_proportion, '=', $height_proportion, '<br>';
        	echo $equal_width, '=', $equal_height, '<br>';*/
        	 
        	$width_offset=intval(($dest_width-$equal_width)/2);
        	$height_offset=intval(($dest_height-$equal_height)/2);
        	 
        	$tmp_img=imageCreateTrueColor($dest_width, $dest_height);
        	imageAlphaBlending($tmp_img, false);
        	imageSaveAlpha($tmp_img, true);
        	$trans_colour = imagecolorallocate($tmp_img, 255, 255, 255);
        	imagefill($tmp_img, 0, 0, $trans_colour);
        	imageCopyResampled($tmp_img, $source_img, $width_offset, $height_offset, 0, 0, $equal_width, $equal_height, $source_width, $source_height);
        	 
        }else{
        	$ratio=1;
        	if ($mode=='width') {
        		if ($w_src>$width){$ratio=$w_src/$width;}
        	} else {
        		$tmp=$width;$width=$height;$height=$tmp;
        		if ($h_src>$height){$ratio=$h_src/$height;}
        	}
        	$width_tmp=intval($w_src/$ratio);
        	$height_tmp=intval($h_src/$ratio);
        	$tmp_img=imageCreateTrueColor($width_tmp,$height_tmp);
        	imageAlphaBlending($tmp_img, false);
        	imageSaveAlpha($tmp_img, true);
        	imageCopyResampled($tmp_img, $source_img, 0, 0, 0, 0, $width_tmp, $height_tmp, $w_src, $h_src);
        }
        
        
        if ($ext=='jpg' || $ext=='jpeg'){
        	imagejpeg($tmp_img, $dst, (int)$this->getConfigValue('jpeg_quality'));
        }elseif($ext=='png'){
        	imagepng($tmp_img, $dst, (int)$this->getConfigValue('png_quality'));
        }elseif($ext=='gif'){
        	imagegif($tmp_img,$dst);
        } 
        ImageDestroy($source_img);
        ImageDestroy($tmp_img);
        // ImageDestroy($preview_img);
        return array($width,$height);
    }
    
   
    
    /**
     * Make move 
     * @param
     * @return
     */
    function makeMove ( $src, $dst ) {
    	@rename($src, $dst);
    }
    
    /**
     * return id of Admininstrator 
     * @param
     * @return int
     */   
	function getAdminUserId(){
		if(isset(self::$storage['AdminUserId'])){
			return self::$storage['AdminUserId'];
		}
		$admin_id=0;
		$DBC=DBC::getInstance();
    	$query = 'SELECT u.user_id FROM '.DB_PREFIX.'_user u LEFT JOIN '.DB_PREFIX.'_group g USING(group_id) WHERE g.system_name=? LIMIT 1';
        $stmt=$DBC->query($query, array('admin'));
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	$admin_id=$ar['user_id'];
        	self::$storage['AdminUserId']=$admin_id;
        }
    	return $admin_id;
    }
    
/**
     * return Vendor info 
     * @param id integer
     * @return string
     */   
	function getVendorInfoById($id){
		$vendor_info=array();
		$DBC=DBC::getInstance();
    	$query = 'SELECT * FROM '.DB_PREFIX.'_vendor WHERE vendor_id=? LIMIT 1';
    	$stmt=$DBC->query($query, array($id));
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		$vendor_info=$ar['user_id'];
    	}
        return $vendor_info;
    }
    
    function getUnregisteredUserId(){
    	$user_id=0;
    	/*if(0!=(int)$this->getConfigValue('free_advs_user_id')){
    		return (int)$this->getConfigValue('free_advs_user_id');
    	}*/
    	$DBC=DBC::getInstance();
    	$query = 'SELECT user_id FROM '.DB_PREFIX.'_user WHERE login=? LIMIT 1';
    	$stmt=$DBC->query($query, array('_unregistered'));
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		$user_id=$ar['user_id'];
    	}
    	return $user_id;
    }
    
    function growCounter($table_name,$primary_key_name,$primary_key_value,$user_id=0){
    	if(1==$this->getConfigValue('use_realty_view_counter')){
    		if(!isset($_SESSION['realty_views'][$primary_key_value])){
    			$DBC=DBC::getInstance();
    			$query='UPDATE '.DB_PREFIX.'_'.$table_name.' SET view_count=view_count+1 WHERE '.$primary_key_name.'=?';
    			$stmt=$DBC->query($query, array($primary_key_value));
    		}
			$_SESSION['realty_views'][$primary_key_value]=time();
    	}
    }
    
	
	function validateEmailFormat($email){
		if(preg_match('/^[0-9a-z]+[-\._0-9a-z]*@[0-9a-z]+[-\._^0-9a-z]*[0-9a-z]+[\.]{1}[a-z]{2,6}$/',strtolower($email))){
			return true;
		}else{
			return false;
		}
	}
	
	function validateMobilePhoneNumberFormat($phone_number, $mask=''){
		if($mask!=''){
			$clear_number=preg_replace('/[^\d]/', '', $phone_number);
			
			if(preg_match('/^'.$mask.'$/',$clear_number)){
				return $clear_number;
			}else{
				return FALSE;
			}
		}else{
			if ( $this->getConfigValue('apps.fasteditor.enable') ) {
				$clear_number=preg_replace('/[^\d]/', '', $phone_number);
				if(preg_match('/^8(\d){10}$/',$clear_number)){
					return $clear_number;
				}else{
					return FALSE;
				}
			}else{
				return TRUE;
			}
		}
		
		
	}
	
	public static function getAttachmentsBlock(){
		global $smarty;
		return $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/attachments_block.tpl');
	}
	
	public static function modelSimplification($model){
		foreach($model as $mkey=>$melement){
			foreach($melement as $k=>$v){
				if($k=='type' && ($v!='select_by_query' && $v!='select_box' && $v!='select_box_structure' && $v!='structure' && $v!='date')){
					$model[$mkey]['value_string']=$model[$mkey]['value'];
				}
				if(!in_array($k, array('name','title','value','value_string','type','image_array'))){
					unset($model[$mkey][$k]);
				}
				
			}
		}
		return $model;
	}
	
	public static function iconv($in_charset, $out_charset, $string){
		if(strtolower($in_charset)==strtolower($out_charset)){
			return $string;
		}else{
			return iconv($in_charset, $out_charset.'//IGNORE', $string);
		}
	}
	
	public static function removeDirectory($dir, &$msg=array()) {
		$files = scandir($dir);
		
		if(count($files)>2){
			foreach($files as $file){
				if($file!='.' && $file!='..'){
					if(is_dir($dir.'/'.$file)){
						self::removeDirectory($dir.'/'.$file, $msg);
					}elseif(is_writable($dir.'/'.$file)){
						@unlink($dir.'/'.$file);
					}else{
						$msg[]='Файл/директория '.$file.' не удален. Удалите его самостоятельно.';
					}
				}
			}
		}
	    
	    if(is_writable($dir)){
	    	rmdir($dir);
	    }else{
	    	$msg[]='Файл/директория '.$dir.' не удален. Удалите его самостоятельно.';
	    }
	}
	
	function transliteMe($str){
		$str=str_replace(array(',','.','/','\\','"','\'', '~', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '_', '+', '|', ';', '?', '<', '>', '`', '[', ']', '{', '}', '№' ), '', $str);
		$str = mb_strtolower($str, SITE_ENCODING);
		$tr = array(
				"а"=>"a","б"=>"b",
				"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ё"=>"e","ж"=>"j",
				"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
				"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
				"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
				"ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
				"ы"=>"i","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
	
				"А"=>"a","Б"=>"b",
				"В"=>"v","Г"=>"g","Д"=>"d","Е"=>"e","Ё"=>"e","Ж"=>"j",
				"З"=>"z","И"=>"i","Й"=>"y","К"=>"k","Л"=>"l",
				"М"=>"m","Н"=>"n","О"=>"o","П"=>"p","Р"=>"r",
				"С"=>"s","Т"=>"t","У"=>"u","Ф"=>"f","Х"=>"h",
				"Ц"=>"ts","Ч"=>"ch","Ш"=>"sh","Щ"=>"sch","Ъ"=>"y",
				"Ы"=>"i","Ь"=>"","Э"=>"e","Ю"=>"yu","Я"=>"ya",
	
				" "=> "-"
		);
		//preg_replace
		return strtr(mb_strtolower($str, SITE_ENCODING),$tr);
	}
	
	public static function setLangSession(){
		if(isset($_GET['_lang'])){
			$lang=trim(preg_replace('/[^a-z]/i', '', $_GET['_lang']));
			if($lang!=''){
				$_SESSION['_lang']=$lang;
			}
		}
		if(!isset($_SESSION['_lang']) || $_SESSION['_lang']==''){
			$_SESSION['_lang']='ru';
		}
	}
	
	public static function getClearRequestURI(){
		
		$REQUESTURIPATH=parse_url(urldecode($_SERVER['REQUEST_URI']), PHP_URL_PATH);
		
		if(false===$REQUESTURIPATH){
			return '';
		}
		if('/'===$REQUESTURIPATH){
			return '';
		}
		$REQUESTURIPATH=str_replace('\\', '/', $REQUESTURIPATH);
		if(substr($REQUESTURIPATH, 0, 1)==='/'){
			$REQUESTURIPATH=substr($REQUESTURIPATH, 1);
		}
		if(substr($REQUESTURIPATH, -1, 1)==='/'){
			$REQUESTURIPATH=substr($REQUESTURIPATH, 0, strlen($REQUESTURIPATH)-1);
		}
		//var_dump($REQUESTURIPATH);
		//$REQUESTURIPATH=trim(str_replace('\\', '/', parse_url(urldecode($_SERVER['REQUEST_URI']), PHP_URL_PATH)),'/');
		if(SITEBILL_MAIN_URL!=''){
			$REQUESTURIPATH=trim(preg_replace('/^'.trim(SITEBILL_MAIN_URL,'/').'/','',$REQUESTURIPATH),'/');
		}
		return $REQUESTURIPATH;
	}
	
	public function sendFirmMail($to, $from, $subject, $body){
		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
		$mailer = new Mailer();
		
		global $smarty;
		$smarty->assign('letter_content', $body);
		$smarty->assign('estate_core_url', 'http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL);
		$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/system/template/firm_mail_wrapper.tpl';
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/firm_mail_wrapper.tpl')){
			$tpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/firm_mail_wrapper.tpl';
		}
		$body=$smarty->fetch($tpl);
		
		if ( $this->getConfigValue('use_smtp') ) {
			$mailer->send_smtp($to, $from, $subject, $body, 1);
		} else {
			$mailer->send_simple($to, $from, $subject, $body, 1);
		}
	}
	
	
	/********************************************************/
	/**
	 * Get category breadcrumbs
	 * @param array $params
	 * @param array $category_structure
	 * @param string $url
	 * @return string
	 */
	
	/*
	function get_category_breadcrumbs( $params, $category_structure, $url = '' ) {
		$rs = '';
		$url1=$url;
		if ( !isset($params['topic_id']) || is_array($params['topic_id']) ) {
			return $rs;
		}
		//foreach ( $category_structure['childs'][0] as $item_id => $catalog_id ) {
		$town='';
	
		if(isset($params['city_id'])){
			$DBC=DBC::getInstance();
			$stmt=$DBC->query('SELECT name,url FROM '.DB_PREFIX.'_city WHERE city_id=?',array((int)$params['city_id']));
			if($stmt){
				$ar=$DBC->fetch($stmt);
				$town='<a href="'.SITEBILL_MAIN_URL.'/'.$ar['url'].'/">'.$ar['name'].'</a>';
				if($this->getConfigValue('apps.seo.city_url_enable')==1)$url=SITEBILL_MAIN_URL.'/'.$url1.$ar['url'].'/';
			}
			 
		}
		if($category_structure['catalog'][$params['topic_id']]['url']!=''){
			$ra[] = '<a href="'.rtrim($url,'/').'/'.$category_structure['catalog'][$params['topic_id']]['url'].'">'.$category_structure['catalog'][$params['topic_id']]['name'].'</a>';
		}else{
			$ra[] = '<a href="'.rtrim($url,'/').'/topic'.$params['topic_id'].'.html">'.$category_structure['catalog'][$params['topic_id']]['name'].'</a>';
		}
	
		$parent_category_id = $category_structure['catalog'][$params['topic_id']]['parent_id'];
		while ( $category_structure['catalog'][$parent_category_id]['parent_id'] != 0 ) {
			if ( $j++ > 100 ) {
				return;
			}
			if($category_structure['catalog'][$parent_category_id]['url']!=''){
				$ra[] = '<a href="'.rtrim($url,'/').'/'.$category_structure['catalog'][$parent_category_id]['url'].'">'.$category_structure['catalog'][$parent_category_id]['name'].'</a>';
			}else{
				$ra[] = '<a href="'.rtrim($url,'/').'/topic'.$parent_category_id.'.html">'.$category_structure['catalog'][$parent_category_id]['name'].'</a>';
			}
			$parent_category_id = $category_structure['catalog'][$parent_category_id]['parent_id'];
		}
		if ( $category_structure['catalog'][$parent_category_id]['name'] != '' ) {
			if($category_structure['catalog'][$parent_category_id]['url']!=''){
				$ra[] = '<a href="'.rtrim($url,'/').'/'.$category_structure['catalog'][$parent_category_id]['url'].'">'.$category_structure['catalog'][$parent_category_id]['name'].'</a>';
			}else{
				$ra[] = '<a href="'.rtrim($url,'/').'/topic'.$parent_category_id.'.html">'.$category_structure['catalog'][$parent_category_id]['name'].'</a>';
			}
		}
	
		if(!empty($town))$ra[]=$town;
		$ra[]='<a href="'.SITEBILL_MAIN_URL.'/">'.Multilanguage::_('L_HOME').'</a>';
		$rs = implode(' / ', array_reverse($ra));
		return $rs;
	}
	
	
	function get_current_town(){
		static $currtown=null;
		if($currtown)return $currtown;
		$DBC=DBC::getInstance();
	
		$REQUESTURIPATH=trim(Sitebill::getClearRequestURI(),'/');
		if ( $REQUESTURIPATH != '' ) {
			//echo $REQUESTURIPATH.'<br>';
			$url=explode('/',$REQUESTURIPATH);
			$query='SELECT * FROM '.DB_PREFIX.'_city WHERE url=? LIMIT 1';
			$stmt=$DBC->query($query, array($url[0]));
			if($stmt){
				$ar=$DBC->fetch($stmt);
				if((int)$ar['city_id']!=0){
					$city_url_catched=true;
					$city_info=$ar;
				}
			}
	
			if ( $city_info['city_id'] > 0 ) {
				$currtown = $city_info['city_id'];
				$this->template->assign('select_city_id', $currtown);
				$this->template->assign('select_city_name', $city_info['name']);
				$this->template->assign('select_city_url', $city_info['url']);
	
			}
		}
		return $currtown;
	
	}
	
	function GetCityUrls(){
		static $city_urls =array();
		if(count($city_urls)>0) return $city_urls;
		$DBC=DBC::getInstance();
		$stmt=$DBC->query('SELECT city_id,url FROM '.DB_PREFIX.'_city');
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$city_urls[$ar['city_id']]=$ar['url'];
			}
		}
		return $city_urls;
	
	}
	
	public function getrealval($key,$v,$new=false){
		static $res=array();
		$val=mb_strtoupper(trim($v));
		if(empty($val))return false;
	
		$key=mb_strtoupper(trim($key));
		if(empty($key))return false;
		if(isset($res[$key][$val]))return $res[$key][$val];
		$query="SELECT * FROM ".DB_PREFIX."_data_aliases WHERE `key`=? AND `alias`=? LIMIT 1";
		$this->db->exec($query, array($key, $val));
		$this->db->fetch_assoc();
		if(($this->db->row)){
			$res[$key][$val]=$this->db->row['value'];
		}else
			if($new){
			$query="INSERT INTO ".DB_PREFIX."_data_aliases (`key`,`alias`,`value`)values(?,?,?)";
			$this->db->exec($query, array($key, $val, $v));
			$res[$key][$val]=$v;
		}
		
	
		return $res[$key][$val];
	}
	*/
	
}


?>
