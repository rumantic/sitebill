<?php
/**
 * SiteBill sitebill.ru interface class
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class SiteBill_Krascap extends SiteBill {
    var $image_number = 5;
    public $lock_title = false;
    protected $_grid_constructor;
    //protected $currentCommand='';
    
    /**
     * Constructor
     * @param void
     * @return void
     */
    function SiteBill_Krascap() {
    	if (version_compare(phpversion(), "5.3.0", "<=")) { 
    		echo 'Для работы CMS Sitebill необходим <b>PHP 5.3</b> и выше. Сейчас у вас работает PHP версии '.phpversion().'<br>  Включите, пожалуйста, новую версию PHP через панель управления хостингом или обратитесь в тех.поддержку вашего хостинга.<br>Также можете задать вопрос на <a href="http://goo.gl/f78nzw">нашем форуме</a>';
    		exit;
    	}
        $this->SiteBill();
        //require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
        //$this->_grid_constructor = new Grid_Constructor();
    }
    
    /*function __construct() {
    	if (version_compare(phpversion(), "5.3.0", "<=")) {
    		echo 'Для работы CMS Sitebill необходим <b>PHP 5.3</b> и выше. Сейчас у вас работает PHP версии '.phpversion().'<br>  Включите, пожалуйста, новую версию PHP через панель управления хостингом или обратитесь в тех.поддержку вашего хостинга.<br>Также можете задать вопрос на <a href="http://goo.gl/f78nzw">нашем форуме</a>';
    		exit;
    	}
    	parent::__construct();
    }*/
    
    
    
    
    
    /*public function setCurrentCommand($command){
    	$this->currentCommand=$command;
    }*/
    
    
    /**
     * Method for final operations
     */
    final function finalizer(){
    	$Sitebill_Includer=Sitebill_Includer::getInstance();
    	$Sitebill_Includer->fetch();
    }
    
    function load_user_stat ( $user_id ) {
	$user_stat['advs_counter'] = 777;
	return $user_stat;
    }
    
    
    
    
    /**
     * Get preview image
     * @param int $record_id record ID
     * @param int $index image index
     * @return string
     */
    function getPreviewImage ( $record_id, $index ) {
    	$DBC=DBC::getInstance();
    	$query = 'SELECT img'.$index.'_preview FROM re_data WHERE id=?';
    	$stmt=$DBC->query($query, array($record_id));        //echo $query;
    	$ar=$DBC->fetch($stmt);
        if ( $ar['img'.$index.'_preview'] != '' ) {
            return '<img src="'.SITEBILL_MAIN_URL.'/img/data/'.$ar['img'.$index.'_preview'].'" border="0">'; 
        }
        return false;
    }
    
    /**
     * Process get form
     * @param 
     * @return string
     */
    function processGetRentForm () {
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/components/com_data_get_rent/sitebill_data_get_rent.php');
        $sitebill_data_get_rent = new Sitebill_Data_Get_Rent();
        $rs = $sitebill_data_get_rent->main();
        return $rs;
    }
    
    /**
     * Get page by URI
     * @param string $uri uri
     * @return array
     */
   /* function getPageByURI ( $uri ) {
        global $__db_prefix;
        //$uri = mysql_real_escape_string($uri);
        $uri = str_replace('/', '', $uri);
    	$DBC=DBC::getInstance();
    	$query = 'SELECT * FROM '.DB_PREFIX.'_page WHERE uri=? LIMIT 1';
    	$stmt=$DBC->query($query, array((string)$uri));
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		if((int)$ar['page_id']>0){
    			return $ar;
    		}
    	}
    	return false;
    }*/
    
    function getExtendedSearchFormParams(){
    	$DBC=DBC::getInstance();
    	$ar=array();
       	$query='SELECT MAX(floor_count) AS max_floor_count, MAX(price) AS max_price FROM '.DB_PREFIX.'_data WHERE active=1';
       	$stmt=$DBC->query($query);
       	if($stmt){
       		$ar=$DBC->fetch($stmt);
       	}
       	return $ar;
    }
    
    protected function FrontAction_isunderconstruct(){
    	
    }
    
    protected function FrontAction_yandexrealty_export(){
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/yandexrealty/admin/local_admin.php')){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/yandexrealty/admin/admin.php';
    		require_once SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/yandexrealty/admin/local_admin.php';
    		$YRE=new local_yandexrealty_admin();
    	}else{
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/yandexrealty/admin/admin.php';
    		$YRE=new yandexrealty_admin();
    	}
    		
    	header("Content-Type: text/xml");
    	echo $YRE->export();
    	exit();
    }
    
    /*public function runRouter($REQUESTURIPATH){
    	
    	$working_route='';
    	
    	if($REQUESTURIPATH==''){
    		$working_route='home';
    	}
    	$route_map=array(
    		'logout'=>array(
    			'tpl'=>'/^logout$/',
    			'params'=>array()
    		),
    			'myfavorites'=>array(
    					'tpl'=>'/^myfavorites$/',
    					'params'=>array()
    			),
    			'find'=>array(
    					'tpl'=>'/^find$/',
    					'params'=>array()
    			),
    			'robox'=>array(
    					'tpl'=>'/^robox\//',
    					'params'=>array()
    			),
    			'map'=>array(
    					'tpl'=>'/^map(\/(\?.*)?)?$/',
    					'params'=>array()
    			),
    			'goroda'=>array(
    					'tpl'=>'/^goroda\//',
    					'params'=>array()
    			),
    			'register'=>array(
    					'tpl'=>'/^register\//',
    					'params'=>array()
    			),
    			'remind'=>array(
    					'tpl'=>'/^remind\//',
    					'params'=>array()
    			),
    			'login'=>array(
    					'tpl'=>'/^login\//',
    					'params'=>array()
    			),
    			'add'=>array(
    					'tpl'=>'/^add(\/index.php)?$/',
    					'params'=>array()
    			),
    			'ipotekaorder'=>array(
    					'tpl'=>'/^ipotekaorder(\/index.php)?$/',
    					'params'=>array()
    			),
    			'contactus'=>array(
    					'tpl'=>'/^contactus$/',
    					'params'=>array()
    			),
    			'land'=>array(
    					'tpl'=>'/^land$/',
    					'params'=>array()
    			),
    			'account'=>array(
    					'tpl'=>'/^account(\/.*)?$/',
    					'params'=>array()
    			),
    			'user'=>array(
    					'tpl'=>'/^user(\d+).html/',
    					'params'=>array(
    						'user_id'	
    					)
    			)
    	);
    	$yandex_alias=trim($this->getConfigValue('apps.yandexrealty.alias'));
    	$stantdart_yandex_alias=trim($this->getConfigValue('apps.yandexrealty.standart_entry_alias'));
    	if($stantdart_yandex_alias===''){
    		$stantdart_yandex_alias='yandexrealty';
    	}
    	if($yandex_alias!==''){
    	
    	}elseif(0===intval($this->getConfigValue('apps.yandexrealty.disable_standart_entrypoint')) && $REQUESTURIPATH==$stantdart_yandex_alias){
    		$this->FrontAction_yandexrealty_export();
    	}
    	
    	
    	foreach($route_map as $route_command=>$route_data){
    		if (preg_match($route_data['tpl'], $REQUESTURIPATH, $matches)) {
    			$working_route=$route_command;
    			if(is_array($route_data['params']) && count($route_data['params'])>0){
    				foreach ($route_data['params'] as $pi=>$pn){
    					if(isset($matches[($pi+1)])){
    						$router_params[$pn]=$matches[($pi+1)];
    						break;
    					}
    				}
    			}
    			
    		}
    	}
    	
    	$DBC=DBC::getInstance();
    	
    	if($working_route==''){
    		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/predefinedlinks/admin/admin.php')){
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/predefinedlinks/admin/admin.php';
    			$PDLA=new predefinedlinks_admin();
    			if($predefined_info=$PDLA->checkAlias($REQUESTURIPATH)){
    				$working_route='list';
    			}
    		}
    	}
    	
    	if($working_route=='' && intval($this->getConfigValue('apps.seo.no_country_url'))===0){
    		$query='SELECT * FROM '.DB_PREFIX.'_country WHERE url=? LIMIT 1';
    		$stmt=$DBC->query($query, array($REQUESTURIPATH));
    		if($stmt){
    			$ar=$DBC->fetch($stmt);
    			 
    			if(intval($ar['country_id'])!==0){
    				$working_route='list';
    			}
    		}
    	}
    	
    	if($working_route=='' && intval($this->getConfigValue('apps.seo.no_region_url'))===0){
    		$query='SELECT * FROM '.DB_PREFIX.'_region WHERE alias=? LIMIT 1';
    		$stmt=$DBC->query($query, array($REQUESTURIPATH));
    		if($stmt){
    			$ar=$DBC->fetch($stmt);
    			if(intval($ar['region_id'])!==0){
    				$working_route='list';
    			}
    		}
    	}
    	
    	if($working_route=='' && intval($this->getConfigValue('apps.seo.no_city_url'))===0){
    		$query='SELECT * FROM '.DB_PREFIX.'_city WHERE url=? LIMIT 1';
    		$stmt=$DBC->query($query, array($REQUESTURIPATH));
    		if($stmt){
    			$ar=$DBC->fetch($stmt);
    			if(intval($ar['city_id'])!==0){
    				$working_route='list';
    			}
    		}
    	}
    	
    	if($working_route==''){
    		if($this->getConfigValue('apps.complex.enable')){
    			$DBC=DBC::getInstance();
    			$query='SELECT * FROM '.DB_PREFIX.'_complex WHERE url=? LIMIT 1';
    			$stmt=$DBC->query($query, array($REQUESTURIPATH));
    			if($stmt){
    				$ar=$DBC->fetch($stmt);
    				if(intval($ar['complex_id'])!==0){
    					$working_route='list';
    				}
    			}
    		}
    	}
    	
    	
    	
    	
    }*/
    
    /**
     * Main
     * @param void
     * @return string
     */
    function main () {
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
    	$REQUESTURIPATH=Sitebill::getClearRequestURI();
    	global $folder;
    	$this->template->assert('REQUESTURIPATH', $REQUESTURIPATH);
    	$this->template->assert('estate_folder', $folder);
    	Multilanguage::appendTemplateDictionary($this->getConfigValue('theme'));
    	
    	
    	//if(preg_match('/^im\/small\/(r|c|f)(\d+)x(\d+)\/(\d+)\.(jpg|jpeg|gif|png)$/', $REQUESTURIPATH, $matches)){
    	/*if(preg_match('/^im\/small\/(r|c|f)(\d+)x(\d+)\/(.*)\.(jpg|jpeg|gif|png)$/', $REQUESTURIPATH, $matches)){	
    		
    		$ref=$_SERVER['HTTP_REFERER'];
    		if($ref===NULL || 'estatecms.ru'!=parse_url($ref, PHP_URL_HOST)){
    			$sapi_name = php_sapi_name();
    			if ($sapi_name == 'cgi' || $sapi_name == 'cgi-fcgi') {
    				header('Status: 404 Not Found');
    			} else {
    				header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    			}
    			exit();
    		}
    		
    		
    		$max_width='600';
    		$max_height='600';
    		$min_width='10';
    		$min_height='10';
    		
    		$new_width=(int)$matches[2];
    		$new_height=(int)$matches[3]; 		
    	
    		$mod=$matches[1];
    		
    		$name=$matches[4];
    		$ext=$matches[5];
    		
    		$folder_exists=false;
    		$file_exists=false;
    		
    		if($new_width<$min_width || $new_width>$max_width || $new_height<$min_height || $new_height>$max_height){
    			
    			$sapi_name = php_sapi_name();
    			if ($sapi_name == 'cgi' || $sapi_name == 'cgi-fcgi') {
    				header('Status: 404 Not Found');
    			} else {
    				header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    			}
    			exit();
    		}
    		
    		$dir=SITEBILL_DOCUMENT_ROOT.'/im/small/';
    		
    		if(!file_exists($dir)){
    			mkdir($dir);
    			chmod($dir, 0755);
    		}
    		
    		
    		$dir=$dir.$mod.$new_width.'x'.$new_height.'/';
    		if(!file_exists($dir)){
    			mkdir($dir);
    			chmod($dir, 0755);
    		}
    		
    		
    		
    		//$normal_file=SITEBILL_DOCUMENT_ROOT.'/im/normal/'.$name.'.'.$ext;
    		$normal_file=SITEBILL_DOCUMENT_ROOT.'/img/data/'.$name.'.'.$ext;
    		$small_file=$dir.$name.'.'.$ext;
    		
    		//echo $normal_file;
    		if(file_exists($normal_file)){
    			$this->makePreview ( $normal_file, $small_file, $new_width, $new_height, $ext, $mod );
    		}else{
    			$sapi_name = php_sapi_name();
    			if ($sapi_name == 'cgi' || $sapi_name == 'cgi-fcgi') {
    				header('Status: 404 Not Found');
    			} else {
    				header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    			}
    			exit();
    		}
    		
    		if(file_exists($small_file)){
    			header('Content-Type: image/'.$ext);
    			$f=file_get_contents($small_file);
    			echo $f;
    			exit();
    		}else{
    			$sapi_name = php_sapi_name();
    			if ($sapi_name == 'cgi' || $sapi_name == 'cgi-fcgi') {
    				header('Status: 404 Not Found');
    			} else {
    				header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    			}
    			exit();
    		}
    		
    		exit();
    	}*/
    	
    	/*$ip='192.170.21.13';
    	$ip=$_SERVER['REMOTE_ADDR'];
    	
    	$access_allowed=false;
    	$ip=$_SERVER['REMOTE_ADDR'];
    	$allowed_ips=array();
    	if(''!==trim($this->getConfigValue('is_underconstruction_allowed_ip'))){
    		$allowed_ips=explode(',', trim($this->getConfigValue('is_underconstruction_allowed_ip')));
    	}

    	if(count($allowed_ips)>0){
    		foreach ($allowed_ips as $allowed_ip){
    			$testing_ip=str_replace(array('*', '.'), array('(\d+)', '\.'), $allowed_ip);
    			if(preg_match('/^'.$testing_ip.'$/', $ip)){
    				$access_allowed=true;
    				break;
    			}
    		}
    	}
    	
    	if($access_allowed){
    		echo 'OPENED';
    	}else{
    		echo 'CLOSED';
    	}*/
    	
    
    	if(1==$this->getConfigValue('is_underconstruction')){
    		$access_allowed=false;
    		$ip=$_SERVER['REMOTE_ADDR'];
    		
    		if($ip!=''){
    			$allowed_ips=array();
    			
    			if(''!==trim($this->getConfigValue('is_underconstruction_allowed_ip'))){
    				$allowed_ips=explode(',', trim($this->getConfigValue('is_underconstruction_allowed_ip')));
    			}
    			
    			if(count($allowed_ips)>0){
    				foreach ($allowed_ips as $allowed_ip){
    					$testing_ip=str_replace(array('*', '.'), array('(\d+)', '\.'), $allowed_ip);
    					if(preg_match('/^'.$testing_ip.'$/', $ip)){
    						$access_allowed=true;
    						break;
    					}
    				}
    			}
    		}
    		
    		
    		
    		if(!$access_allowed){
    			header('HTTP/1.0 503 Service Unavailable');
    			header('Retry-After: 3600');
    			$this->template->assert('is_underconstruction_mode', '1');
    			return;
    		}
    	
    	}
    	
    	/*if(1==$this->getConfigValue('is_underconstruction')){
    		$ip=$_SERVER['REMOTE_ADDR'];
    		if($ip=='' || $ip!=$this->getConfigValue('is_underconstruction_allowed_ip')){
    			header('HTTP/1.0 503 Service Unavailable');
    			header('Retry-After: 3600');
    			$this->template->assert('is_underconstruction_mode', '1');
    			return;
    		}
    		
    	}*/
    	
    	if ( !isset($_SESSION['favorites']) ) {
    		$_SESSION['favorites'] = array();
    	}
    	
    	//$this->runRouter($REQUESTURIPATH);
    	
    	$yandex_alias=trim($this->getConfigValue('apps.yandexrealty.alias'));
    	$stantdart_yandex_alias=trim($this->getConfigValue('apps.yandexrealty.standart_entry_alias'));
    	if($stantdart_yandex_alias===''){
    		$stantdart_yandex_alias='yandexrealty';
    	}
    	if($yandex_alias!==''){
    		
    	}elseif(0===intval($this->getConfigValue('apps.yandexrealty.disable_standart_entrypoint')) && $REQUESTURIPATH==$stantdart_yandex_alias){
			$this->FrontAction_yandexrealty_export();
		}
		
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
		$apps_processor = new Apps_Processor();
		$apps_processor->run_preload();
		
		//echo '<br><br><br>******************************************************************<br><br><br>';
	
		if ( isset($_SESSION['theme']) && $_SESSION['theme'] != '' and $this->getConfigValue('show_demo_banners')) {
			$theme = $_SESSION['theme'];
		} else {
			$theme = $this->getConfigValue('theme');
		}
		
    	if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$theme.'/main/main.php') ) {
    		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor_local.php');
    		require_once (SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$theme.'/main/main.php');
    		$frontend_main = new frontend_main();
    		return $frontend_main->main();
    	} else {
        global $__site_title, $folder, $smarty;
        //echo '<br><br><br>******************************************************************<br><br><br>';
        
        
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        
        
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/search/kvartira_search.php');
        $kvartira_search_form = new Kvartira_Search_Form();
        $kvartira_search_form->main();
        $this->template->assert('search_form_template', 'search_form.tpl');

        if ( $this->getConfigValue('menu_type') == 'purecss' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/menu/purecssmenu.php');
            $purecssmenu = new PureCSS_Menu();
            $this->template->assert('slide_menu', $purecssmenu->get_menu());
        } elseif ( $this->getConfigValue('menu_type') == 'onelevel' ) {
        	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/onelevelmenu/lib/onelevelmenu.php');
        	$onelevel = new Onelevel_Menu();
        	$this->template->assert('slide_menu', $onelevel->get_menu());
        } elseif ( $this->getConfigValue('menu_type') == 'megamenu' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/menu/megamenu.php');
        	$megamenu = new Mega_Menu();
        	$this->template->assert('slide_menu', $megamenu->get_menu());
        } else {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/menu/slidemenu.php');
            $slidemenu = new Slide_Menu();
            $this->template->assert('slide_menu', $slidemenu->get_menu());
        }
        
        $extendedSearchFormParams=$this->getExtendedSearchFormParams();
        
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/plugin/plugin_processor.php');
        $Plugin_Processor = new Plugin_Processor();
        $Plugin_Processor->main();
        
        $extendedSearchFormParams=$this->getExtendedSearchFormParams();
        $this->template->assert('max_floor_count', $extendedSearchFormParams['max_floor_count']);
        $this->template->assert('max_price', $extendedSearchFormParams['max_price']);
        
        
        //set default value
		$this->template->assert('base', SITEBILL_MAIN_URL);
		$this->template->assert('show_demo_banners', $this->getConfigValue('show_demo_banners'));
		$this->template->assert('REQUEST_URI', $_SERVER['REQUEST_URI']);
		$this->template->assert('type_list2', '');
        $this->template->assert('type_list3', '');
        $this->template->assert('title', $this->getConfigValue('site_title'));
        
        $this->template->assert('right_column', 1);
        
        $this->template->assert('structure_box', $Structure_Manager->getCategorySelectBoxWithName('topic_id', $this->getRequestValue('topic_id') ));
        //print_r($_SESSION);
        
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
        $Login = new Login();
        
        if ( preg_match('/\/logout/', $_SERVER['REQUEST_URI']) ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/logout.php');
            $Logout = new Logout;
            $Logout->main();
        }
        
        $this->template->assert('user_id', $Login->getSessionUserId());
        
        $this->template->assert('auth_menu', $Login->getAuthMenu());
        
        $this->template->assert('current_theme_name', $this->getConfigValue('theme'));
        
        if ( $_SERVER['REQUEST_URI'] == '/' ) {
        	if ( $this->getConfigValue('theme') != 'etown' ) {
        		$this->grid_special();
        	}
        }
        if ( $this->getConfigValue('theme') != 'etown' ) {
        	$this->grid_special_right();
        }
        if($this->getConfigValue('theme')=='albostar'){
        	$this->template->assert('rot_banners', $this->getLast(10));
        }
        
    	/*if($this->getConfigValue('apps.freeorder.enable')==1){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/freeorder/admin/admin.php';
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/freeorder/site/site.php';
    		$FreeOrder=new freeorder_site();
    		$this->template->assert('freeorder_form', $FreeOrder->getForm());
        	$this->template->assert('freeorder_on', 'yes');
        }else{
        	$this->template->assert('freeorder_on', 'no');
        }*/
        
        $this->template->assert('meta_keywords', '');
        $this->template->assert('meta_description', '');

        
        if ( preg_match('/\/robox/', $_SERVER['REQUEST_URI']) ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/robokassa/robokassa.php');
            $robokassa = new Robox();
            $rs = $robokassa->main();
            if ( preg_match('/result/', $_SERVER['REQUEST_URI']) ) {
                echo $rs;
                exit;
            }
            $this->template->assert('main', $rs);
            $this->template->render();
            $rs = $this->template->toHTML();
            return $rs;
        }
        
        
        if ( preg_match('/\/register/', $_SERVER['REQUEST_URI']) ) {
        	if ( !$this->getConfigValue('allow_register_account') ) {
        		$this->template->assert('main', 'Функция регистрации отключена администратором');
        	} else {
        		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/register.php');
        		$Register = new Register;
        		//$smarty->assign->assert('main', $Register->main());
        		$rs1 = $Register->main();
        		$this->template->assert('main', $rs1);
        	}
        	$this->template->assert('hide_advelements', '1');
            $this->template->render();
            $rs = $this->template->toHTML();
            return $rs;
        }
        
        if ( preg_match('/\/remind/', $_SERVER['REQUEST_URI']) ) {
        	if ( !$this->getConfigValue('allow_remind_password') ) {
    	        $this->template->assert('main', Multilanguage::_('REMIND_PASS_OFF','system'));
        	} else {
            	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/user.php');
            	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/remind.php');
            	$remind = new Remind;
	            //$smarty->assign('main', $remind->main());
    	        $this->template->assert('main', $remind->main());
        	}
            $this->template->render();
            $rs = $this->template->toHTML();
            return $rs;
        }
        
        if ( preg_match('/\/login/', $_SERVER['REQUEST_URI']) ) {
            $this->template->assert('main', $Login->main());
            if ( $Login->getSessionUserId() > 0 ) {
                $this->template->assert('auth_menu', $Login->getAuthMenu());
            }
            $this->template->render();
            $rs = $this->template->toHTML();
            return $rs;
            //$resultString .= $this->getHomePageString();
            //return $resultString;
        }

        
        
        if ( $this->getConfigValue('theme') != 'kgs' ) {
        	if ( preg_match('/^\/add(\/)*/', $_SERVER['REQUEST_URI']) ) {
        		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
        		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/form/add.php');
        		$user_add = new User_Add();
        	
        		$this->template->assert('main', $user_add->main());
        		$this->template->render();
        		$rs = $this->template->toHTML();
        		return $rs;
        	}
        }
        
        if ( preg_match('/\/ipotekaorder\//', $_SERVER['REQUEST_URI']) ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/form/ipoteka.php');
            $ipoteka_order = new Ipoteka_Order_Form();
            
            $this->template->assert('main', $ipoteka_order->main());
            $this->template->render();
            $rs = $this->template->toHTML();
            return $rs;
        }
        
    	if ( preg_match('/\/goroda\//', $_SERVER['REQUEST_URI']) ) {
			$city=$this->getCityListTr();
			$topic=$this->getTopicListTr();
			if(count($city)>0 && count($topic)>0){
				foreach($city as $c){
					foreach($topic as $t){
						$rs.='<a href="/'.$c['translit_name'].'-'.$t['translit_name'].'.html">'.$c['name'].' ('.$t['name'].')</a><br />';
					}
				}
			}
			$this->template->assert('main', $rs);
			/*$this->template->assert('search_form', $land_front->getSearchForm());
			*/
			$this->template->render();
			$rs = $this->template->toHTML();
			return $rs;
		}
        
        if ( preg_match('/\/contactus\//', $_SERVER['REQUEST_URI']) ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/form/contactus.php');
            $contactus_form = new contactus_Form();
            
            $this->template->assert('main', $contactus_form->main());
            $this->template->render();
            $rs = $this->template->toHTML();
            return $rs;
        }
        
        
        if ( preg_match('/\/land\//', $_SERVER['REQUEST_URI']) ) {
        	require_once('lib/admin/land/land_manager.php');
        	require_once('lib/frontend/land/land_front.php');
        	$land_front = new Land_Front();
        	
            $this->template->assert('main', $land_front->main());
        	$this->template->assert('search_form', $land_front->getSearchForm());
            
            $this->template->render();
            $rs = $this->template->toHTML();
            return $rs;
        }
		
		
        
        if ( preg_match('/\/getrent\//', $_SERVER['REQUEST_URI']) ) {
            $this->template->assert('main', $this->processGetRentForm('buy'));
            $this->template->render();
            $rs = $this->template->toHTML();
            return $rs;
        }
        //require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
        //$apps_processor = new Apps_Processor();
        $apps_processor->run_frontend();
        if ( count($apps_processor->get_executed_apps()) > 0 ) {
        	$this->template->render();
        	$rs = $this->template->toHTML();
        	return $rs;
        }

        
        if ( preg_match('/\/account/', $_SERVER['REQUEST_URI']) ) {
        	$this->template->assert('right_column', '');
        	$this->template->assert('search_form_template', '');
        	$this->template->assert('is_account', '1');
        
        	//return;
        	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
        	$Account = new Account;
        
        	if ( $Account->get_user_id() > 0 ) {
        		$company_profile = $Account->get_company_profile($Account->get_user_id());
        		$this->template->assert('company', $company_profile);
        	}
        
        
        	$this->template->assert('breadcrumbs',
        			$this->get_breadcrumbs(
        					array(
        							'<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>',
        							'<a href="'.$folder.'/account/">'.Multilanguage::_('PRIVATE_ACCOUNT','system').'</a>'
        					)));
        
        	if ( preg_match('/profile/', $_SERVER['REQUEST_URI']) ) {
        		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/profile.php');
        		$profile = new User_Profile();
        		$this->template->assert('breadcrumbs',
        				$this->get_breadcrumbs(
        						array(
        								'<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>',
        								'<a href="'.$folder.'/account/">'.Multilanguage::_('PRIVATE_ACCOUNT','system').'</a>',
        								'<a href="'.$folder.'/account/profile/">'.Multilanguage::_('PROFILE','system').'</a>'
        						)));
        
        		$this->template->assert('main', $profile->main());
        	} elseif ( preg_match('/balance/', $_SERVER['REQUEST_URI']) ) {
        
        		$this->template->assert('breadcrumbs',
        				$this->get_breadcrumbs(
        						array(
        								'<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>',
        								'<a href="'.$folder.'/account/">'.Multilanguage::_('PRIVATE_ACCOUNT','system').'</a>',
        								'<a href="'.$folder.'/account/balance/">'.Multilanguage::_('BALANCE','system').'</a>'
        						)));
        
        		$this->template->assert('main', $Account->main());
        	} elseif ( preg_match('/\/user/', $_SERVER['REQUEST_URI'] ) ) {
        		if ( $this->getConfigValue('apps.company.enable') ) {
        			$this->template->assert('breadcrumbs',
        					$this->get_breadcrumbs(
        							array(
        									'<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>',
        									'<a href="'.$folder.'/account/">'.Multilanguage::_('PRIVATE_ACCOUNT','system').'</a>',
        									'<a href="'.$folder.'/account/user/">'.Multilanguage::_('REALTERS','system').'</a>'
        							)));
        
        			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
        			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/user/user_company_manager.php');
        			$user_company_manager = new User_Company_Manager();
        			$this->template->assert('main', $user_company_manager->frontend_main());
        		}
        
        	} elseif ( preg_match('/data/', $_SERVER['REQUEST_URI']) ) {
        
        		$this->template->assert('breadcrumbs',
        				$this->get_breadcrumbs(
        						array(
        								'<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>',
        								'<a href="'.$folder.'/account/">'.Multilanguage::_('PRIVATE_ACCOUNT','system').'</a>',
        								'<a href="'.$folder.'/account/data/">'.Multilanguage::_('MY_ADS','system').'</a>'
        						)));
        
        		if(preg_match('/add/', $_SERVER['REQUEST_URI'])){
        			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_admin.php');
        			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_editor.php');
        			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/data/user_data.php');
        			$user_data_manager = new User_Data_Manager();
        			$this->template->assert('main', $user_data_manager->add());
        		}else{
        			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_admin.php');
        			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_editor.php');
        			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/data/user_data.php');
        			$user_data_manager = new User_Data_Manager();
        			$this->template->assert('main', $user_data_manager->main());
        		}
        
        	} else {
        		$this->template->assert('breadcrumbs',
        				$this->get_breadcrumbs(
        						array(
        								'<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>',
        								'<a href="'.$folder.'/account/">'.Multilanguage::_('PRIVATE_ACCOUNT','system').'</a>'
        						)));
        
        		$this->template->assert('main', $Account->getHome());
        	}
        	$this->template->render();
        	$rs = $this->template->toHTML();
        	return $rs;
    		}
        }
        
        $this->map();
       // $this->template->assert('total_map', $this->map2());
        if(1==$this->getConfigValue('apps.seo.data_alias_enable')){
        	$requesturi=trim(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH),'/');
        	$requesturi=str_replace('\\', '/', $requesturi);
        	if(SITEBILL_MAIN_URL!=''){
        		preg_replace('/^'.trim(SITEBILL_MAIN_URL,'/').'/','',$requesturi);
        	}
        
        	$url_string_parts=explode('/',$requesturi);
        	if(count($url_string_parts)>0){
        		$possible_alias=$url_string_parts[count($url_string_parts)-1];
        
        		$possible_alias=preg_replace('/[^A-Za-z0-9_-]/','',urldecode($possible_alias));
        		if($possible_alias!=''){
        			$DBC=DBC::getInstance();
        			$q='SELECT id FROM '.DB_PREFIX.'_data WHERE translit_alias=? LIMIT 1';
        			$stmt=$DBC->query($q, array((string)$possible_alias));
        			if($stmt){
        				$ar=$DBC->fetch($stmt);
        				if((int)$ar['id']>0){
        					$realty_id=(int)$ar['id'];
        					$this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
        					//require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
        					//$kvartira_view = new Kvartira_View();
        					$kvartira_view=$this->_getRealtyViewer();
        					$this->template->assert('main', $kvartira_view->main($realty_id));
        					return;
        				}
        			}
        		}
        	}
        }
       
        if ( preg_match('/realty/', $_SERVER['REQUEST_URI']) ) {
        	if ( SITEBILL_MAIN_URL != '' ) {
        		$realty_view_regexp = '/^'.'\\'.SITEBILL_MAIN_URL.'\/realty/';
        	} else {
        		$realty_view_regexp = '/^\/realty/';
        	}
        	if(1==$this->getConfigValue('apps.seo.level_enable') && preg_match($realty_view_regexp, $_SERVER['REQUEST_URI'])){
        		$realty_id = $this->getIDfromURI($_SERVER['REQUEST_URI']);
        		//echo 'realty_id = '.$realty_id;
        		if($realty_id){
        			$DBC=DBC::getInstance();
        			$query='SELECT topic_id FROM '.DB_PREFIX.'_data WHERE id=?';
        			$stmt=$DBC->query($query, array($realty_id));
        			if($stmt){
        				$ar=$DBC->fetch($stmt);
        				$topic_id=$ar['topic_id'];
        				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        				$Structure_Manager = new Structure_Manager();
        				$category_structure = $Structure_Manager->loadCategoryStructure();
        				
        				if($category_structure['catalog'][$topic_id]['url']!=''){
        					$parent_category_url=$category_structure['catalog'][$topic_id]['url'].'/';
        				}else{
        					$parent_category_url='';
        				}
        				
        				if(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
        					$new_location=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$realty_id.'.html';
        				}else{
        					$new_location=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$realty_id;
        				}
        				header('HTTP/1.1 301 Moved Permanently');
        				header('Location: '.$new_location);
        				exit();
        			}
        			
        			
        		}else {
        			header("Status: 404 Not Found");
        			$this->template->assign('title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
        			$this->template->assign('meta_title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
        			$this->template->assign('error_message', '<h1>'.Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND').'</h1>');
        			$this->template->assign('main_file_tpl', 'error_message.tpl');
        		}
        	
        	}elseif(1==$this->getConfigValue('apps.seo.level_enable') && !preg_match($realty_view_regexp, $_SERVER['REQUEST_URI'])){
        		$realty_id = $this->getIDfromURI($_SERVER['REQUEST_URI']);
        		$this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
        		//require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
        		//$kvartira_view = new Kvartira_View();
        		$kvartira_view=$this->_getRealtyViewer();
        		$this->template->assert('main', $kvartira_view->main($realty_id));
        	} elseif(0==$this->getConfigValue('apps.seo.level_enable') && preg_match($realty_view_regexp, $_SERVER['REQUEST_URI'])){
        		$realty_id = $this->getIDfromURI($_SERVER['REQUEST_URI']);
        		$this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
        		//require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
        		//$kvartira_view = new Kvartira_View();
        		$kvartira_view=$this->_getRealtyViewer();
        		$this->template->assert('main', $kvartira_view->main($realty_id));
        	}else {
        		header("Status: 404 Not Found");
        		$this->template->assign('title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
        		$this->template->assign('meta_title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
        		$this->template->assign('error_message', '<h1>'.Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND').'</h1>');
        		$this->template->assign('main_file_tpl', 'error_message.tpl');
        	}
            /*$realty_id = $this->getIDfromURI($_SERVER['REQUEST_URI']);
        	$this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
            $kvartira_view = new Kvartira_View();
            
            $this->template->assert('main', $kvartira_view->main($realty_id));*/
            
        } elseif( $this->getRequestValue('do') == 'buy' ) {
            $this->template->assert('main', $this->processAdvancedForm('buy'));
        } elseif( $this->getRequestValue('do') == 'rent' ) {
            $this->template->assert('main', $this->processAdvancedForm('rent'));
        } elseif ( $this->getRequestValue('view') != '' ) {
            $this->template->assert('main', $this->getPage($this->getRequestValue('view')));
        } else {
        	if($this->getConfigValue('apps.realtypro.enable')!=1){
            	$this->template->assert('main', '<p><br></p>'.$this->grid_adv());
        	}
            
        }
        $this->template->render();
        $rs = $this->template->toHTML();
        return $rs;
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
    
    /**
     * Get ID from URI
     * @param string $uri uri
     * @return int
     */
    function getIDfromURI ( $uri ) {
    	if(trim($this->getConfigValue('apps.seo.realty_alias'))!=''){
    		$realty_alias=trim($this->getConfigValue('apps.seo.realty_alias'));
    	}else{
    		$realty_alias='realty';
    	}
    	preg_match('/'.$realty_alias.'(\d+)(.html)?/s', $uri, $matches);
    	if ( $matches[1] > 0 ) {
    		return $matches[1];
    	}
    	return false;
    	
    }
    
    /**
     * Get image form block
     * @param int $record_id record ID
     * @param int $image_number image number
     * @return string
     */
    /*function getImageBlock ( $record_id, $image_number ) {
        for ( $i = 1; $i <= $image_number; $i++ ) {
            $rs .= '<tr>';
            $rs .= '<td class="left_column">Фото '.$i.':<br>'.$this->getPreviewImage($record_id, $i).'</td>';
            $rs .= '<td nowrap><input type="file" name="img'.$i.'"></td>';
            $rs .= '</tr>';
            
            $rs .= '<tr>';
            $rs .= '<td colspan="2"><hr></td>';
            $rs .= '</tr>';
        }
        return $rs;
    }*/
    
    
    
    
    /**
     * Process advanced form 
     * @param string $key key
     * @return string
     */
    function processAdvancedForm () {
        if ( $_REQUEST['do'] == 'add_done' ) {
            $data = $this->initDataFromRequest();
            if ( $this->checkAdvData($data) ) {
                $data['active'] = 0;
                $data['street'] = $this->getStreetNameById($data['street_id']);
                
                $this->newAdvRecord($data);
                $rs = Multilanguage::_('L_MESSAGE_ON_MODERATION');
                return $rs;
            }
        }
        $rs = $this->getAdvForm( $data, 'add_done' );
        return $rs;
    }
    
    /**
     * Check adv data
     * @param array $data data
     * @return boolean
     */
    function checkAdvData ( $data ) {
        if ( $this->getRequestValue('district_id') == '' and  $this->getRequestValue('new_district') == '' ) {
            $this->riseError(Multilanguage::_('L_ERROR_DISTRICT_NOT_SPECIFIED'));
            return false;
        }
        if ( $this->getRequestValue('price') == '' ) {
            $this->riseError(Multilanguage::_('L_ERROR_PRICE_NOT_SPECIFIED'));
            return false;
        }
        return true;
    }
    
    /**
     * Edit image
     * @param int $record_id record id
     * @return boolean
     */
    /*function editImage ( $record_id ) {
        global $sitebill_document_root;
        for ( $i=1; $i <= $this->image_number; $i++ ) {
            $need_prv=0;
            $preview_name='';   
            if (!empty($_FILES['img'.$i]['name'])) { 
                $arr=split('\.',$_FILES['img'.$i]['name']);
                $ext=strtolower($arr[count($arr)-1]);
                $preview_name="img".uniqid().'_'.time()."_".$i.".".$ext;
                $prv="prv".uniqid().'_'.time()."_".$i.".".$ext;
                $preview_name_tmp="_tmp".uniqid().'_'.time()."_".$i.".".$ext;
                move_uploaded_file($_FILES['img'.$i]['tmp_name'], $sitebill_document_root.'/img/data/'.$preview_name_tmp);  
                list($width,$height)=$this->makePreview($sitebill_document_root.'/img/data/'.$preview_name_tmp, $sitebill_document_root.'/img/data/'.$preview_name, 600,400, $ext,1);
                list($w,$h)=$this->makePreview($sitebill_document_root.'/img/data/'.$preview_name_tmp, $sitebill_document_root.'/img/data/'.$prv, 130,130, $ext,'width');
                unlink($sitebill_document_root.'/img/data/'.$preview_name_tmp);
                
                $DBC=DBC::getInstance();
                $query='UPDATE re_data SET img'.$i.'=? WHERE id=?';
                $stmt=$DBC->query($query, array(mysql_real_escape_string($preview_name), $record_id));
                $query='UPDATE re_data set SET'.$i.'_preview=? WHERE id=?';
                $stmt=$DBC->query($query, array(mysql_real_escape_string($prv), $record_id));
            } 
        }
    }*/
    
    /**
     * Init data from request
     * @param void
     * @return array
     */
    function initDataFromRequest () {
        $data_array['type_id'] = $this->getRequestValue('type_id');
        $data_array['topic_id'] = $this->getRequestValue('topic_id');
        
        $data_array['tid'] = $this->getRequestValue('tid');
        $data_array['tid1'] = $this->getRequestValue('tid1');
        $data_array['tid2'] = $this->getRequestValue('tid2');
        
        $data_array['country_id'] = $this->getRequestValue('country_id');
        $data_array['new_country'] = $this->getRequestValue('new_country');
        
        $data_array['city_id'] = $this->getRequestValue('city_id');
        $data_array['new_city'] = $this->getRequestValue('new_city');
        
        $data_array['metro_id'] = $this->getRequestValue('metro_id');
        $data_array['new_metro'] = $this->getRequestValue('new_metro');
        
        $data_array['district_id'] = $this->getRequestValue('district_id');
        $data_array['new_district'] = $this->getRequestValue('new_district');
        
        $data_array['street'] = $this->getRequestValue('street');
        $data_array['street_id'] = $this->getRequestValue('street_id');
        $data_array['new_street'] = $this->getRequestValue('new_street');
        
        $data_array['price'] = $this->getRequestValue('price');
        $data_array['contact'] = $this->getRequestValue('contact');
        $data_array['agent_tel'] = $this->getRequestValue('agent_tel');
        $data_array['agent_email'] = $this->getRequestValue('agent_email');
        
        if ( $this->getRequestValue('room_count') != '' ) {
            $data_array['room_count'] = $this->getRequestValue('room_count');
        } else {
            $data_array['room_count'] = 0;
        }

        //elite
        if ( $this->getRequestValue('elite') == 1 ) {
            $data_array['elite'] = 1;
        } else {
            $data_array['elite'] = 0;
        }
        
        //active
        if ( $this->getRequestValue('active') == 1 ) {
            $data_array['active'] = 1;
        } else {
            $data_array['active'] = 0;
        }

        //hot
        if ( $this->getRequestValue('hot') == 1 ) {
            $data_array['hot'] = 1;
        } else {
            $data_array['hot'] = 0;
        }
        
        if ( $this->getRequestValue('floor') != '' ) {
            $data_array['floor'] = $this->getRequestValue('floor');
        } else {
            $data_array['floor'] = 0;
        }
        
        if ( $this->getRequestValue('floor_count') != '' ) {
            $data_array['floor_count'] = $this->getRequestValue('floor_count');
        } else {
            $data_array['floor_count'] = 0;
        }
        
        $data_array['walls'] = $this->getRequestValue('walls');
        $data_array['balcony'] = $this->getRequestValue('balcony');
        $data_array['square_all'] = $this->getRequestValue('square_all');
        $data_array['square_live'] = $this->getRequestValue('square_live');
        $data_array['square_kitchen'] = $this->getRequestValue('square_kitchen');
        $data_array['bathroom'] = $this->getRequestValue('bathroom');
        
        $data_array['text'] = $this->getRequestValue('text');
        $data_array['id'] = $this->getRequestValue('id');
        $data_array['is_telephone'] = $this->getRequestValue('is_telephone');
        $data_array['furniture'] = $this->getRequestValue('furniture');
        $data_array['plate'] = $this->getRequestValue('plate');
        $data_array['number'] = $this->getRequestValue('number');
        return $data_array;
    }
    
    
    /**
     * Process buy
     * @param string $key key
     * @return string
     */
    /*function processForm ( $key ) {
        if ( $this->getRequestValue('send') == 'ok' ) {
            if ( $this->checkData() ) {
                return $this->addRequestRecord();
            }
        }
        $rs = $this->getOrderForm( $key );
        return $rs;
    }*/
    
    /**
     * Check data
     * @param void
     * @return boolean
     */
    /*function checkData () {
        if ( $this->getRequestValue('name') == '' ) {
            $this->riseError(Multilanguage::_('L_ERROR_NAME_NOT_SPECIFIED'));
            return false;
        }
        if ( $this->getRequestValue('request') == '' ) {
            $this->riseError(Multilanguage::_('L_ERROR_TEXT_NOT_SPECIFIED'));
            return false;
        }
        if ( $this->getRequestValue('contact') == '' ) {
            $this->riseError(Multilanguage::_('L_ERROR_PHONE_NOT_SPECIFIED'));
            return false;
        }
        return true;
    }*/
    
    /**
     * Get order form
     * @param string $action key
     * @return string
     */
    /*function getOrderForm ( $action ) {
        $rs .= '<form method="post" action="index.php" name="rentform" enctype="multipart/form-data">';
        $rs .= '<table border="0">';
        
        $rs .= '<tr>';
        $rs .= '<td colspan="2"><b>'.sprintf(Multilanguage::_('L_NEED_REQUIERD_FIELDS'),'<span class="error">*</span>').'</b></td>';
        $rs .= '</tr>';
        
        if ( $this->GetError() ) {
            $rs .= '<tr>';
            $rs .= '<td colspan="2"><span class="error">'.$this->GetError().'</span></td>';
            $rs .= '</tr>';
        }
        
        $rs .= '<tr>';
        $rs .= '<td class="left_column">'.Multilanguage::_('L_FIO').' <span class="error">*</span>:</td>';
        $rs .= '<td><input type="text" name="name" size="40" value="'.$this->getRequestValue('name').'"></td>';
        $rs .= '</tr>';

        $rs .= '<tr>';
        $rs .= '<td class="left_column">'.Multilanguage::_('L_TEXT').' <span class="error">*</span>:</td>';
        $rs .= '<td><textarea cols="40" rows="5" name="request">'.$this->getRequestValue('request').'</textarea></td>';
        $rs .= '</tr>';
        
        $rs .= '<tr>';
        $rs .= '<td class="left_column">'.Multilanguage::_('L_PHONE').' <span class="error">*</span>:</td>';
        $rs .= '<td><input type="text" size="40" name="contact" value="'.$this->getRequestValue('contact').'"></td>';
        $rs .= '</tr>';

        $rs .= '<tr>';
        $rs .= '<td></td>';

        $rs .= '<input type="hidden" name="do" value="'.$action.'">';
        $rs .= '<input type="hidden" name="send" value="ok">';
        
        $rs .= '<td><input type="submit" value="'.Multilanguage::_('L_TEXT_SEND').'"></td>';
        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '</form>';
        
        return $rs;
    }*/

    
    
	function topicUrlFind($request_uri){
		
		$url_parts=parse_url(urldecode($request_uri));
		
		$path=$url_parts['path'];
		if(substr($path, 0, 1)==='/'){
			$path=substr($path, 1);
		}
		if(substr($path, -1, 1)==='/'){
			$path=substr($path, 0, strlen($path)-1);
		}
		
		
		$topic_name = str_replace('/', '', $url_parts['path']);
		
		
		$topic_name=$path;
		
		
		$topic_name=SiteBill::getClearRequestURI();
		if($topic_name==''){
			return false;
		}
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
		$Structure=new Structure_Manager();
		$urls=$Structure->loadCategoriesUrls();
		
		if($this->getConfigValue('apps.seo.level_enable')==1){
		
		}else{
			foreach($urls as $k=>$u){
				$up=explode('/', $u);
				$urls[$k]=end($up);
			}
		}
		
		$urls_to_ids=array_flip($urls);
		if(isset($urls_to_ids[$topic_name])){
			return $urls_to_ids[$topic_name];
		}else{
			return FALSE;
		}
		if(strlen($topic_name) > 0){
	    	$DBC=DBC::getInstance();
			$query='SELECT id FROM '.DB_PREFIX.'_topic WHERE url=? LIMIT 1';
			$stmt=$DBC->query($query, array($topic_name));
			
			if($stmt){
				$ar=$DBC->fetch($stmt);
				return $ar['id'];
			}else{
				return FALSE;
			}

		}else{
			return FALSE;
		}
	}
	
	function cityTopicUrlFind($request_uri){
		$request_uri=urldecode($request_uri);
		
		$cid=NULL;
		$tid=NULL;
		$request_uri=trim($request_uri,'/');
		if(strpos($request_uri, '-')!=false){
			$request_uri=str_replace('.html', '', $request_uri);
			$parts=array();
			$parts=explode('-',$request_uri);
			/*print_r($parts);*/
			$parts_count=count($parts);
			for($i=1;$i<$parts_count;$i++){
				$cid=NULL;
				$tid=NULL;
				$city_name='';
				
				$left_part=array();
				$right_part=array();
				$left_part=array_slice($parts, 0, $i);
				$right_part=array_slice($parts, $i);
				
				$DBC=DBC::getInstance();
				$query='SELECT city_id, name FROM '.DB_PREFIX.'_city WHERE translit_name=? LIMIT 1';
				
				$stmt=$DBC->query($query, array(implode('-',$left_part)));
				
				
				if($stmt){
					$ar=$DBC->fetch($stmt);
					$cid=$ar['city_id'];
					$city_name=$ar['name'];
				}
					
				$query='SELECT id FROM '.DB_PREFIX.'_topic WHERE translit_name=?';
				$stmt=$DBC->query($query, array(implode('-',$right_part)));
				if($stmt){
					$ar=$DBC->fetch($stmt);
					$tid=$ar['id'];
				}
				
				if($cid!==NULL && $tid!=NULL){
					return array($cid, $tid, $city_name);
				}
			}
			return FALSE;
		}
		return FALSE;
		
	}
	
	function isTopicExists($topic_id){
		$DBC=DBC::getInstance();
		$query='SELECT COUNT(id) AS cnt FROM '.DB_PREFIX.'_topic WHERE id=?';
		$stmt=$DBC->query($query, array((int)$topic_id));
		
		if($stmt){
			$ar=$DBC->fetch($stmt);
			if($ar['cnt']>0){
				return TRUE;
			}
		}
		return FALSE;
	}
	
	function grid_adv_favorites(){
		
		//$grid_constructor = $this->_grid_constructor;
		$grid_constructor = $this->_getGridConstructor();
		
		/*$params['id'] = $this->getRequestValue('id');
		$params['topic_id'] = '';
		$params['order'] = $this->getRequestValue('order');
		$params['region_id'] = $this->getRequestValue('region_id');
		$params['city_id'] = $this->getRequestValue('city_id');
		$params['district_id'] = $this->getRequestValue('district_id');
		$params['metro_id'] = $this->getRequestValue('metro_id');
		$params['street_id'] = $this->getRequestValue('street_id');
		$params['page'] = $this->getRequestValue('page');*/
		$params['page'] = $this->getRequestValue('page');
		$params['asc'] = $this->getRequestValue('asc');
		$params['order'] = $this->getRequestValue('order');
		
		if ( count($_SESSION['favorites']) != 0 ) {
			$params['favorites'] = $_SESSION['favorites'];
		
		}else{
			$params['favorites'] = array(-1);
		}
		
		
		
		
		/*$params['price'] = $this->getRequestValue('price');
		$this->template->assign('price', $params['price']);
		
		$params['price_min'] = $this->getRequestValue('price_min');
		$this->template->assign('price_min', $params['price_min']);
		
		$params['house_number'] = $this->getRequestValue('house_number');
		$this->template->assign('house_number', $params['house_number']);*/
		
		$params['onlyspecial'] = $this->getRequestValue('onlyspecial');
		$this->template->assign('onlyspecial', $params['onlyspecial']);
		
		$grid_constructor->main($params);
		$this->template->assert('breadcrumbs', $this->get_breadcrumbs( array( '<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>', 'Избранное' )));

		return $rs;
	}
	
	function _detectUrlParams($server_request_uri){
		
		$server_request_uri=urldecode($server_request_uri);
		$server_request_uri=parse_url($server_request_uri, PHP_URL_PATH);
		$topic_id=FALSE;
		$city_id=FALSE;
		$gorod_name=FALSE;
		
		$server_request_uri=SiteBill::getClearRequestURI();
		
		if(preg_match('/topic(\d*).html/', $server_request_uri, $matches) && $this->isTopicExists($matches[1])){
			//$this->setRequestValue('topic_id', $matches[1]);
			$topic_id=(int)$matches[1];
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
			$Structure=new Structure_Manager();
			$urls=$Structure->loadCategoriesUrls();
			//print_r($urls);
			if(isset($urls[$topic_id]) && $urls[$topic_id]!=''){
				header('location:'.SITEBILL_MAIN_URL.'/'.$urls[$topic_id]);
				exit();
			}
		}else{
			if($x=$this->cityTopicUrlFind($server_request_uri)){
				$topic_id=$x[1];
				$city_id=$x[0];
				$gorod_name = $x[2];
			}elseif($x=$this->topicUrlFind($server_request_uri)){
				$topic_id=$x;
			}else{
				if($this->getConfigValue('apps.seo.level_enable')==1){
					$ru=$server_request_uri;
					if(substr($ru, 0, 1)==='/'){
						$ru=substr($ru, 1);
					}
					if(substr($ru, -1, 1)==='/'){
						$ru=substr($ru, 0, strlen($ru)-1);
					}
					//$ru=trim($server_request_uri,'/');
					if(SITEBILL_MAIN_URL!=''){
						$ru=str_replace(trim(SITEBILL_MAIN_URL,'/').'/', '', $ru);
					}
					require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
					$Structure=new Structure_Manager();
					$urls=$Structure->loadCategoriesUrls();
	
					$urls_to_ids=array_flip($urls);
	
					$parts=explode('?',$ru);
					
					if ( strlen($parts[0]) > 0 ) {
						if(isset($urls_to_ids[$parts[0]])){
	
							//$this->setRequestValue('topic_id', $urls_to_ids[$parts[0]]);
							$topic_id=$urls_to_ids[$parts[0]];
						}
					}
				}
			}
		}
		return array(
				'topic_id'=>$topic_id,
				'city_id'=>$city_id,
				'gorod_name'=>$gorod_name,
		);
	}
	
	protected function grid_data(){
		$REQUESTURIPATH=Sitebill::getClearRequestURI();
	}
	
	
	/*protected function get_grid_data_params(){
		if(NULL!==$this->getRequestValue('id')){
			$params['id'] = (int)$this->getRequestValue('id');
		}
		if(NULL!==$this->getRequestValue('topic_id')){
			$params['topic_id'] = $this->safeRequestParams($this->getRequestValue('topic_id'));
		}
		if(NULL!==$this->getRequestValue('order')){
			$params['order'] = $this->getRequestValue('order');
		}
		if(NULL!==$this->getRequestValue('region_id')){
			$params['region_id'] = $this->safeRequestParams($this->getRequestValue('region_id'));
		}
		if(NULL!==$this->getRequestValue('city_id')){
			$params['city_id'] = $this->safeRequestParams($this->getRequestValue('city_id'));
		}
		if(NULL!==$this->getRequestValue('district_id')){
			$params['district_id'] = $this->safeRequestParams($this->getRequestValue('district_id'));
		}
		if(NULL!==$this->getRequestValue('country_id')){
			$params['country_id'] = $this->safeRequestParams($this->getRequestValue('country_id'));
		}
		if(NULL!==$this->getRequestValue('metro_id')){
			$params['metro_id'] = $this->safeRequestParams($this->getRequestValue('metro_id'));
		}
		
		if(NULL!==$this->getRequestValue('street_id')){
			$params['street_id'] = $this->safeRequestParams($this->getRequestValue('street_id'));
		}
		
		
		if ( $this->getConfigValue('apps.complex.enable') && NULL!==$this->getRequestValue('complex_id') ) {
			$params['complex_id'] = $this->safeRequestParams($this->getRequestValue('complex_id'));
		}
		if(NULL!==$this->getRequestValue('page')){
			$params['page'] = (int)$this->getRequestValue('page');
		}
		if(NULL!==$this->getRequestValue('spec')){
			$params['spec'] = $this->getRequestValue('spec');
		}
		if(NULL!==$this->getRequestValue('owner')){
			$params['owner'] = (int)$this->getRequestValue('owner');
		}
		if(NULL!==$this->getRequestValue('asc')){
			$params['asc'] = $this->getRequestValue('asc');
		}
		
		
		if(NULL!==$this->getRequestValue('user_id')){
			$params['user_id'] = $this->getRequestValue('user_id');
		}
		
		if(NULL!==$this->getRequestValue('currency_id')){
			$params['currency_id'] = (int)$this->getRequestValue('currency_id');
		}
		if(NULL!==$this->getRequestValue('price')){
			$params['price'] = (int)str_replace(' ', '', $this->getRequestValue('price'));
			$this->template->assign('price', $params['price']);
		}
		
		if(NULL!==$this->getRequestValue('price_min')){
			$params['price_min'] = (int)str_replace(' ', '', $this->getRequestValue('price_min'));
			$this->template->assign('price_min', $params['price_min']);
		}
		
		if(NULL!==$this->getRequestValue('price_pm')){
			$params['price_pm'] = (int)str_replace(' ', '', $this->getRequestValue('price_pm'));
			$this->template->assign('price_pm', $params['price_pm']);
		}
		
		if(NULL!==$this->getRequestValue('price_pm_min')){
			$params['price_pm_min'] = (int)str_replace(' ', '', $this->getRequestValue('price_pm_min'));
			$this->template->assign('price_pm_min', $params['price_pm_min']);
		}
		
		if(NULL!==$this->getRequestValue('house_number')){
			$params['house_number'] = $this->getRequestValue('house_number');
			$this->template->assign('house_number', $params['house_number']);
		}
		
		if(NULL!==$this->getRequestValue('onlyspecial')){
			$params['onlyspecial'] = $this->getRequestValue('onlyspecial');
			$this->template->assign('onlyspecial', $params['onlyspecial']);
		}
		
		if(NULL!==$this->getRequestValue('floor_min')){
			$params['floor_min'] = (int)$this->getRequestValue('floor_min');
		}
		
		if(NULL!==$this->getRequestValue('floor_max')){
			$params['floor_max'] = (int)$this->getRequestValue('floor_max');
		}
		
		if(NULL!==$this->getRequestValue('floor_count_min')){
			$params['floor_count_min'] = (int)$this->getRequestValue('floor_count_min');
		}
		
		if(NULL!==$this->getRequestValue('floor_count_max')){
			$params['floor_count_max'] = (int)$this->getRequestValue('floor_count_max');
		}
		
		if(NULL!==$this->getRequestValue('not_first_floor')){
			$params['not_first_floor'] = (int)$this->getRequestValue('not_first_floor');
		}
		
		if(NULL!==$this->getRequestValue('not_last_floor')){
			$params['not_last_floor'] = (int)$this->getRequestValue('not_last_floor');
		}
		
			
		if(NULL!==$this->getRequestValue('square_min')){
			$params['square_min'] = (int)$this->getRequestValue('square_min');
		}
		
		if(NULL!==$this->getRequestValue('square_max')){
			$params['square_max'] = (int)$this->getRequestValue('square_max');
		}
		
		if(NULL!==$this->getRequestValue('live_square_min')){
			$params['live_square_min'] = (int)$this->getRequestValue('live_square_min');
		}
		
		if(NULL!==$this->getRequestValue('kitchen_square_min')){
			$params['kitchen_square_min'] = (int)$this->getRequestValue('kitchen_square_min');
		}
		
		if(NULL!==$this->getRequestValue('kitchen_square_max')){
			$params['kitchen_square_max'] = (int)$this->getRequestValue('kitchen_square_max');
		}
		
		if(NULL!==$this->getRequestValue('live_square_max')){
			$params['live_square_max'] = (int)$this->getRequestValue('live_square_max');
		}
		
		if(NULL!==$this->getRequestValue('is_phone')){
			$params['is_phone'] = (int)$this->getRequestValue('is_phone');
		}
		
		if(NULL!==$this->getRequestValue('is_balkony')){
			$params['is_balkony'] = (int)$this->getRequestValue('is_balkony');
		}
		
		if(NULL!==$this->getRequestValue('is_sanitary')){
			$params['is_sanitary'] = (int)$this->getRequestValue('is_sanitary');
		}
		
			
		if(NULL!==$this->getRequestValue('status')){
			$params['status'] = (int)$this->getRequestValue('status');
		}
			
		
		if(NULL!==$this->getRequestValue('nout_from_sale')){
			$params['nout_from_sale'] = (int)$this->getRequestValue('nout_from_sale');
		}
		
		if(NULL!==$this->getRequestValue('nwith_null_params')){
			$params['nwith_null_params'] = (int)$this->getRequestValue('nwith_null_params');
		}
			
		if(NULL!==$this->getRequestValue('by_ipoteka')){
			$params['by_ipoteka'] = (int)$this->getRequestValue('by_ipoteka');
		}
			
		if(NULL!==$this->getRequestValue('new_only')){
			$params['new_only'] = (int)$this->getRequestValue('new_only');
		}
			
		if(NULL!==$this->getRequestValue('is_furniture')){
			$params['is_furniture'] = (int)$this->getRequestValue('is_furniture');
		}
		
		if(NULL!==$this->getRequestValue('has_photo')){
			$params['has_photo'] = (int)$this->getRequestValue('has_photo');
		}
		
		if(NULL!==$this->getRequestValue('is_internet')){
			$params['is_internet'] = (int)$this->getRequestValue('is_internet');
		}
		
		if(NULL!==$this->getRequestValue('room_count')){
			$params['room_count'] = $this->getRequestValue('room_count');
		}
		
		if(NULL!==$this->getRequestValue('optype') && $this->getRequestValue('optype')!=''){
			$params['optype'] = (int)$this->getRequestValue('optype');
		}
		
		if(NULL!==$this->getRequestValue('minbeds')){
			$params['minbeds'] = (int)$this->getRequestValue('minbeds');
		}
		
		if(NULL!==$this->getRequestValue('minbaths')){
			$params['minbaths'] = (int)$this->getRequestValue('minbaths');
		}
		
		if(NULL!==$this->getRequestValue('uniq_id')){
			$params['uniq_id'] = (int)$this->getRequestValue('uniq_id');
		}
			
		
		
		if(1==(int)$this->getRequestValue('export_afy')){
			$params['export_afy'] = 1;
		}
		if(1==(int)$this->getRequestValue('export_cian')){
			$params['export_cian'] = 1;
		}
			
		if(NULL!==$this->getRequestValue('extended_search')){
			$params['extended_search'] = $this->getRequestValue('extended_search');
		}
		if(NULL!==$this->getRequestValue('search')){
			$params['search'] = $this->getRequestValue('search');
		}
		
			
		if(0!=(int)$this->getRequestValue('page_limit')){
			$params['page_limit'] = (int)$this->getRequestValue('page_limit');
		}
		
		if(NULL!==$this->getRequestValue('geocoords')){
			$params['geocoords'] = preg_replace('/[^0-9.+-:]/', '', $this->getRequestValue('geocoords'));
			if($params['geocoords']==''){
				unset($params['geocoords']);
			}
		}
		
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable')==1){
			if(NULL!==$this->getRequestValue('vip_status')){
				$params['vip_status'] = (int)$this->getRequestValue('vip_status');
			}
			if(NULL!==$this->getRequestValue('premium_status')){
				$params['premium_status'] = (int)$this->getRequestValue('premium_status');
			}
			if(NULL!==$this->getRequestValue('bold_status')){
				$params['bold_status'] = (int)$this->getRequestValue('bold_status');
			}
		}
	}*/
	
	protected function FrontAction_grid_find($REQUESTURIPATH){
		$grid_constructor=$this->_getGridConstructor();
		if(Multilanguage::is_set('LT_FIND_URL_TITLE', '_template')){
			$title = Multilanguage::_('LT_FIND_URL_TITLE', '_template');
		}else{
			$title = Multilanguage::_('FIND_URL_TITLE', 'system');
		}
			
		$this->template->assign('title', $title);
		$this->template->assign('meta_title', $title);
		$this->setRequestValue('find_url_catched', 1);
		
		$params_r=$this->gatherRequestParams();
		if(!empty($params)){
			$params=array_merge($params, $params_r);
		}else{
			$params=$params_r;
		}
		
		$grid_constructor->main($params);
	}
	
	protected function FrontAction_grid_country($REQUESTURIPATH, $country_info){
		
		if(1===intval($this->getConfigValue('apps.language.use_langs'))){
			$curlang=$this->getCurrentLang();
			$lang_postfix='_'.$curlang;
			if(1===intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang=='ru'){
				$lang_postfix='';
			}
		}else{
			$lang_postfix='';
		}
		$meta_title='';
		if(isset($country_info['meta_title'.$lang_postfix]) && $country_info['meta_title'.$lang_postfix]!=''){
			$meta_title = $country_info['meta_title'.$lang_postfix];
		}elseif($country_info['meta_title'] != ''){
			$meta_title = $country_info['meta_title'];
		}
			
		if(isset($country_info['name'.$lang_postfix]) && $country_info['name'.$lang_postfix]!=''){
			$title = $country_info['name'.$lang_postfix];
		}else{
			$title = $country_info['name'];
		}
			
		if($meta_title==''){
			$meta_title=$title;
		}
			
		if(intval($this->getRequestValue('page')) > 1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
			if(0==(int)$this->getConfigValue('add_pagenumber_title_place') && $title!=''){
				$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
			}elseif(1==(int)$this->getConfigValue('add_pagenumber_title_place') && $meta_title!=''){
				$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
			}elseif(2==(int)$this->getConfigValue('add_pagenumber_title_place')){
				if($title!=''){
					$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}
				if($meta_title!=''){
					$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}
			}
		}
			
		$this->template->assign('title', $title);
		$this->template->assign('meta_title', $meta_title);
			
		if(isset($country_info['description'.$lang_postfix]) && $country_info['description'.$lang_postfix]!=''){
			$this->template->assign('description', $country_info['description'.$lang_postfix]);
		}elseif ( $country_info['description'] != '' ) {
			$this->template->assign('description', $country_info['description']);
		}
		if(isset($country_info['meta_description'.$lang_postfix]) && $country_info['meta_description'.$lang_postfix]!=''){
			$this->template->assign('meta_description', $country_info['meta_description'.$lang_postfix]);
		}elseif ( $country_info['meta_description'] != '' ) {
			$this->template->assign('meta_description', $country_info['meta_description']);
		}else{
			$this->template->assign('meta_description', $this->getConfigValue('meta_description_main'));
		}
		if(isset($country_info['meta_keywords'.$lang_postfix]) && $country_info['meta_keywords'.$lang_postfix]!=''){
			$this->template->assign('meta_keywords', $country_info['meta_keywords'.$lang_postfix]);
		}elseif ( $country_info['meta_keywords'] != '' ) {
			$this->template->assign('meta_keywords', $country_info['meta_keywords']);
		}else{
			$this->template->assign('meta_keywords', $this->getConfigValue('meta_keywords_main'));
		}
			
		$grid_constructor=$this->_getGridConstructor();
		
		$params=$this->gatherRequestParams();
		$this->setRequestValue('country_id', intval($country_info['country_id']));
		$params['country_id']=intval($country_info['country_id']);
		$grid_constructor->main($params);
	}
	
	protected function FrontAction_grid_region($REQUESTURIPATH, $region_info){
		if(1===intval($this->getConfigValue('apps.language.use_langs'))){
			$curlang=$this->getCurrentLang();
			$lang_postfix='_'.$curlang;
			if(1===intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang=='ru'){
				$lang_postfix='';
			}
		}else{
			$lang_postfix='';
		}
			
		if(isset($region_info['public_title']) && $region_info['public_title']!=''){
			$title = $region_info['public_title'];
		}else{
			$title = $region_info['name'];
		}
		if ( $region_info['meta_title'] != '' ) {
			$meta_title = $region_info['meta_title'];
		} else {
			$meta_title = $region_info['name'];
		}
			
		if(intval($this->getRequestValue('page')) > 1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
			if(0==(int)$this->getConfigValue('add_pagenumber_title_place') && $title!=''){
				$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
			}elseif(1==(int)$this->getConfigValue('add_pagenumber_title_place') && $meta_title!=''){
				$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
			}elseif(2==(int)$this->getConfigValue('add_pagenumber_title_place')){
				if($title!=''){
					$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}
				if($meta_title!=''){
					$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}
			}
		}
		
		$this->template->assign('title', $title);
		$this->template->assign('meta_title', $meta_title);
		
		if ( $region_info['description'] != '' ) {
			$this->template->assign('description', $region_info['description']);
		}
		if ( $region_info['meta_description'] != '' ) {
			$this->template->assign('meta_description', $region_info['meta_description']);
		}else{
			$this->template->assign('meta_description', $this->getConfigValue('meta_description_main'));
		}
		if ( $region_info['meta_keywords'] != '' ) {
			$this->template->assign('meta_keywords', $region_info['meta_keywords']);
		}else{
			$this->template->assign('meta_keywords', $this->getConfigValue('meta_keywords_main'));
		}
		
		$grid_constructor=$this->_getGridConstructor();
		
		$params=$this->gatherRequestParams();
		$this->setRequestValue('region_id', intval($region_info['region_id']));
		$params['region_id']=intval($region_info['region_id']);
		$grid_constructor->main($params);
		
	}
	
	protected function FrontAction_grid_complex($REQUESTURIPATH, $complex_info){
		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/complex/admin/admin.php');
		$complex_admin = new complex_admin();
		$data_model = new Data_Model();
		$complex_data = $complex_admin->data_model;
		$complex_data = $data_model->init_model_data_from_db ( 'complex', 'complex_id', (int)$complex_info['complex_id'], $complex_data['complex'], true );
		$complex_data['image']['image_array'] = $this->get_image_array('complex', 'complex', 'complex_id', (int)$ar['complex_id']);

		$this->template->assign('complex_data', $complex_data);
			
		if ( $complex_info['meta_title'] != '' ) {
			$title = $complex_info['name'];
			$meta_title = $complex_info['meta_title'];
		} else {
			$title = $meta_title = $complex_info['name'];
		}
		
		if(intval($this->getRequestValue('page')) > 1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
			if(0==(int)$this->getConfigValue('add_pagenumber_title_place') && $title!=''){
				$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
			}elseif(1==(int)$this->getConfigValue('add_pagenumber_title_place') && $meta_title!=''){
				$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
			}elseif(2==(int)$this->getConfigValue('add_pagenumber_title_place')){
				if($title!=''){
					$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}
				if($meta_title!=''){
					$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}
			}
		}
		
		$this->template->assign('title', $title);
		$this->template->assign('meta_title', $meta_title);
		
		if ( $complex_info['description'] != '' ) {
			$this->template->assign('description', $complex_info['description']);
		}
		if ( $complex_info['meta_description'] != '' ) {
			$this->template->assign('meta_description', $complex_info['meta_description']);
		}else{
			$this->template->assign('meta_description', $this->getConfigValue('meta_description_main'));
		}
		if ( $complex_info['meta_keywords'] != '' ) {
			$this->template->assign('meta_keywords', $complex_info['meta_keywords']);
		}else{
			$this->template->assign('meta_keywords', $this->getConfigValue('meta_keywords_main'));
		}
		
		
		
		//$this->setRequestValue('complex_view', $REQUESTURIPATH);
	
		$grid_constructor=$this->_getGridConstructor();
	
		$params=$this->gatherRequestParams();
		$this->setRequestValue('complex_id', (int)$complex_info['complex_id']);
		$params['complex_id']=intval($complex_info['complex_id']);
		$grid_constructor->main($params);
	
		//$this->setRequestValue('city_id', (int)$city_info['city_id']);
		//$this->setRequestValue('city_view', $REQUESTURIPATH);
	}
	
	protected function FrontAction_grid_favorites($REQUESTURIPATH){
		$this->template->assign('title', 'Избранное');
		$grid_constructor=$this->_getGridConstructor();
		$params['page'] = $this->getRequestValue('page');
		$params['asc'] = $this->getRequestValue('asc');
		$params['order'] = $this->getRequestValue('order');
		if(count($_SESSION['favorites'])!=0){
			$params['favorites'] = $_SESSION['favorites'];
		}else{
			$params['favorites'] = array(-1);
		}
		$grid_constructor->main($params);
	}
	
	protected function FrontAction_grid_city($REQUESTURIPATH, $city_info){
		if(1===intval($this->getConfigValue('apps.language.use_langs'))){
			$curlang=$this->getCurrentLang();
			$lang_postfix='_'.$curlang;
			if(1===intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang=='ru'){
				$lang_postfix='';
			}
		}else{
			$lang_postfix='';
		}
			
		if(isset($city_info['public_title'.$lang_postfix]) && $city_info['public_title'.$lang_postfix]!=''){
			$title = $city_info['public_title'.$lang_postfix];
		}elseif(isset($city_info['public_title']) && $city_info['public_title']!=''){
			$title = $city_info['public_title'];
		}else{
			$title = $city_info['name'];
		}
		if(isset($city_info['meta_title'.$lang_postfix]) && $city_info['meta_title'.$lang_postfix]!=''){
			$meta_title = $city_info['meta_title'.$lang_postfix];
		}elseif ( $city_info['meta_title'] != '' ) {
			$meta_title = $city_info['meta_title'];
		} else {
			$meta_title = $title;
		}
		
		if(intval($this->getRequestValue('page')) > 1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
			if(0==(int)$this->getConfigValue('add_pagenumber_title_place') && $title!=''){
				$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
			}elseif(1==(int)$this->getConfigValue('add_pagenumber_title_place') && $meta_title!=''){
				$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
			}elseif(2==(int)$this->getConfigValue('add_pagenumber_title_place')){
				if($title!=''){
					$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}
				if($meta_title!=''){
					$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}
			}
		}
		
		$this->template->assign('title', $title);
		$this->template->assign('meta_title', $meta_title);
		
		if(isset($city_info['description'.$lang_postfix]) && $city_info['description'.$lang_postfix]!=''){
			$this->template->assign('description', $city_info['description'.$lang_postfix]);
		}elseif ( $city_info['description'] != '' ) {
			$this->template->assign('description', $city_info['description']);
		}
		if(isset($city_info['meta_description'.$lang_postfix]) && $city_info['meta_description'.$lang_postfix]!=''){
			$this->template->assign('meta_description', $city_info['meta_description'.$lang_postfix]);
		}elseif ( $city_info['meta_description'] != '' ) {
			$this->template->assign('meta_description', $city_info['meta_description']);
		}else{
			$this->template->assign('meta_description', $this->getConfigValue('meta_description_main'));
		}
		if(isset($city_info['meta_keywords'.$lang_postfix]) && $city_info['meta_keywords'.$lang_postfix]!=''){
			$this->template->assign('meta_keywords', $city_info['meta_keywords'.$lang_postfix]);
		}elseif ( $city_info['meta_keywords'] != '' ) {
			$this->template->assign('meta_keywords', $city_info['meta_keywords']);
		}else{
			$this->template->assign('meta_keywords', $this->getConfigValue('meta_keywords_main'));
		}
		
		$grid_constructor=$this->_getGridConstructor();
		
		$params=$this->gatherRequestParams();
		$this->setRequestValue('city_id', intval($city_info['city_id']));
		$params['city_id']=intval($city_info['city_id']);
		$grid_constructor->main($params);
		
		$this->setRequestValue('city_id', (int)$city_info['city_id']);
		$this->setRequestValue('city_view', $REQUESTURIPATH);
	}
	
	/*protected function setPageMetaNames($title='', $meta_title='', $meta_description='', $meta_keywords=''){
		
		if(intval($this->getRequestValue('page')) > 1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
			if(0==(int)$this->getConfigValue('add_pagenumber_title_place') && $title!=''){
				$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
			}elseif(1==(int)$this->getConfigValue('add_pagenumber_title_place') && $meta_title!=''){
				$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
			}elseif(2==(int)$this->getConfigValue('add_pagenumber_title_place')){
				if($title!=''){
					$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}
				if($meta_title!=''){
					$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}
			}
		}
		
		if ( !$this->lock_title && $title!='') {
			$this->template->assign('title', $title);
		}
		
		if($meta_title!=''){
			$this->template->assign('meta_title', $meta_title);
		}
		if($meta_description!=''){
			$this->template->assign('meta_description', $meta_description);
		}
		if($meta_keywords!=''){
			$this->template->assign('meta_keywords', $meta_keywords);
		}
		
	}*/
	
	protected function isHomePage($REQUESTURIPATH){
		if($REQUESTURIPATH=='' && strtoupper($_SERVER['REQUEST_METHOD'])=='GET' && empty($_GET)){
			return true;
		}
		return false;
	}
	
	protected function FrontAction_index(){
		/*$grid_constructor=$this->_getGridConstructor();
		$params=$this->gatherRequestParams();
		$params['city_id']=1;
		$grid_constructor->main($params);*/
	}
	
	protected function FrontAction_add($REQUESTURIPATH){
		if($_SESSION['user_id']>0){
			header('location: '.SITEBILL_MAIN_URL.'/account/data/?do=new');
			exit();
		}
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/form/add.php');
		$user_add = new User_Add();
		$this->template->assert('main', $user_add->main());
	}
	
	protected function FrontAction_account($REQUESTURIPATH){
		
		$this->template->assert('right_column', '');
		$this->template->assert('is_account', '1');
		$this->template->assert('search_form_template', '');
	
		//return;
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
		$Account = new Account;
	
		if(1==$this->getConfigValue('apps.upper.enable')){
			$user_id = $Account->get_user_id();
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/upper/admin/admin.php');
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/upper/site/site.php');
	
			$upper_site = new upper_site();
			$upps_left = $upper_site->checkUserLimits($user_id);
			$packs_left = $upper_site->checkUserPacks($user_id);
				
			$this->template->assert('apps_upper_enable', 1);
			$this->template->assert('upps_left', $upps_left);
			$this->template->assert('packs_left', $packs_left);
		}
	
	
		if ( $Account->get_user_id() > 0 ) {
			$company_profile = $Account->get_company_profile($Account->get_user_id());
			$this->template->assert('company', $company_profile);
		}
	
	
		$this->template->assert('breadcrumbs',
				$this->get_breadcrumbs(
						array(
								'<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>',
								'<a href="'.$folder.'/account/">Личный кабинет</a>'
						)));
	
		if ( preg_match('/^account\/profile/', $REQUESTURIPATH) ) {
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/profile.php');
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/profile_using_model.php');
			$profile = new User_Profile_Model();
			$this->template->assert('breadcrumbs',
					$this->get_breadcrumbs(
							array(
									'<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>',
									'<a href="'.$folder.'/account/">Личный кабинет</a>',
									'<a href="'.$folder.'/account/profile/">Профиль</a>'
							)));
				
			$this->template->assert('main', $profile->main());
		} elseif ( preg_match('/^account\/balance/', $REQUESTURIPATH) ) {
	
			$this->template->assert('breadcrumbs',
					$this->get_breadcrumbs(
							array(
									'<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>',
									'<a href="'.$folder.'/account/">Личный кабинет</a>',
									'<a href="'.$folder.'/account/balance/">Баланс</a>'
							)));
	
			$this->template->assert('main', $Account->main());
		} elseif ( preg_match('/^account\/user/', $REQUESTURIPATH) ) {
			if ( $this->getConfigValue('apps.company.enable') ) {
				$this->template->assert('breadcrumbs',
						$this->get_breadcrumbs(
								array(
										'<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>',
										'<a href="'.$folder.'/account/">Личный кабинет</a>',
										'<a href="'.$folder.'/account/user/">Риелторы</a>'
								)));
	
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/user/user_company_manager.php');
				$user_company_manager = new User_Company_Manager();
				$this->template->assert('main', $user_company_manager->frontend_main());
			}
	
		} else {
	
			$this->template->assert('breadcrumbs',
					$this->get_breadcrumbs(
							array(
									'<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>',
									'<a href="'.$folder.'/account/">Личный кабинет</a>',
									'<a href="'.$folder.'/account/data/">Мои объявления</a>'
							)));
	
			if(preg_match('/add/', $REQUESTURIPATH)){
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_admin.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_editor.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/data/user_data.php');
				$user_data_manager = new User_Data_Manager();
				$this->template->assert('main', $user_data_manager->add());
			}else{
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_admin.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_editor.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/data/user_data.php');
				$user_data_manager = new User_Data_Manager();
				$this->template->assert('main', $user_data_manager->main());
			}
	
		}
		$work_subcontroller='account';
		$has_result=true;
	}
	
	protected function FrontAction_login($REQUESTURIPATH){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
		$Login = new Login();
		$this->template->assert('main', $Login->main());
		if ( $Login->getSessionUserId() > 0 ) {
			$this->template->assert('auth_menu', $Login->getAuthMenu());
		}
	}
	
	protected function FrontAction_register($REQUESTURIPATH){
		if ( !$this->getConfigValue('allow_register_account') ) {
			$this->template->assert('main', 'Функция регистрации отключена администратором');
		} else {
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/register_using_model.php');
			$Register = new Register_Using_Model();
			$rs1 = $Register->main();
			$this->template->assert('main', $rs1);
		}
	}
	
	protected function FrontAction_remind($REQUESTURIPATH){
		if ( !$this->getConfigValue('allow_remind_password') ) {
			$this->template->assert('main', 'Функция напоминания пароля отключена администратором');
		} else {
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/user.php');
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/remind.php');
			$remind = new Remind;
			$this->template->assert('main', $remind->main());
		}
	}
	
	protected function FrontAction_ipotekaorder($REQUESTURIPATH){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/form/ipoteka.php');
		//require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/form/local_ipoteka.php');
		//$ipoteka_order = new Local_Ipoteka_Order_Form();
		$ipoteka_order = new Ipoteka_Order_Form();
		$this->template->assert('main', $ipoteka_order->main());
	}
	
	protected function FrontAction_contactus($REQUESTURIPATH){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/form/contactus.php');
		$contactus_form = new contactus_Form();
		$this->template->assert('main', $contactus_form->main());
	}
	
	protected function FrontAction_logout($REQUESTURIPATH){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/logout.php');
		$Logout = new Logout;
		$Logout->main();
	}
	
	protected function FrontAction_grid_common(){
		$grid_constructor=$this->_getGridConstructor();
		$params=$this->gatherRequestParams();
		$grid_constructor->main($params);
	}
	
	protected function FrontAction_grid_user($REQUESTURIPATH, $info=array()){
		
		$user_id=intval($info['user_id']);
		if($user_id==0){
			return $this->FrontAction_404($REQUESTURIPATH);
		}else{
			$fio='';
			$DBC=DBC::getInstance();
			$query='SELECT fio FROM '.DB_PREFIX.'_user WHERE user_id=? LIMIT 1';
			$stmt=$DBC->query($query, array((int)$user_id));
			if($stmt){
				$ar=$DBC->fetch($stmt);
				$fio=$ar['fio'];
			}else{
				return $this->FrontAction_404($REQUESTURIPATH);
			}
			$title = Multilanguage::_('AGENT_ADS','system').' '.$fio;
			$meta_title=$title;
			
			if(intval($this->getRequestValue('page')) > 1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
				if(0==(int)$this->getConfigValue('add_pagenumber_title_place') && $title!=''){
					$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}elseif(1==(int)$this->getConfigValue('add_pagenumber_title_place') && $meta_title!=''){
					$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}elseif(2==(int)$this->getConfigValue('add_pagenumber_title_place')){
					if($title!=''){
						$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
					}
					if($meta_title!=''){
						$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
					}
				}
			}
			
			$this->template->assign('title', $title);
			$this->template->assign('meta_title', $meta_title);
			
			$grid_constructor=$this->_getGridConstructor();
			$params=$this->gatherRequestParams();
			$params['user_id']=$matches[1];
			$grid_constructor->main($params);
		}
	}
	
	protected function FrontAction_myfavorites($REQUESTURIPATH, $info=array()){
		//$favorites=$_SESSION['favorites'];
		if(count($_SESSION['favorites'])!=0){
			$grid_constructor=$this->_getGridConstructor();
			$params=$this->gatherRequestParams();
			$params['favorites'] = $_SESSION['favorites'];
			$grid_constructor->main($params);
		}
	}
	
	
	protected function FrontAction_404($REQUESTURIPATH){
		$sapi_name = php_sapi_name();
		if ($sapi_name == 'cgi' || $sapi_name == 'cgi-fcgi') {
			header('Status: 404 Not Found');
		} else {
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
		}
		$this->template->assign('title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
		$this->template->assign('meta_title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
		$this->template->assign('error_message', '<h1>'.Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND').'</h1>');
		$this->template->assign('main_file_tpl', 'error_message.tpl');
	}
	
	protected function FrontAction_grid_topic($REQUESTURIPATH, $topic_info){
		if(1===intval($this->getConfigValue('apps.language.use_langs'))){
			$curlang=$this->getCurrentLang();
			$lang_postfix='_'.$curlang;
			if(1===intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang=='ru'){
				$lang_postfix='';
			}
		}
				
				
		if(isset($topic_info['meta_title'.$lang_postfix]) && $topic_info['meta_title'.$lang_postfix]!=''){
			$meta_title = $topic_info['meta_title'.$lang_postfix];
		}elseif($topic['meta_title'] != ''){
			$meta_title = $topic_info['meta_title'];
		}else{
			$meta_title='';
		}
		
		if(isset($topic_info['name'.$lang_postfix]) && $topic_info['name'.$lang_postfix]!=''){
			$title = $topic_info['name'.$lang_postfix];
		}else{
			$title = $topic_info['name'];
		}
		
		if(isset($topic_info['public_title'.$lang_postfix]) && $topic_info['public_title'.$lang_postfix]!=''){
			$title=$topic_info['public_title'.$lang_postfix];
			
		}elseif(isset($topic_info['public_title']) && $topic_info['public_title']!=''){
			$title=$topic_info['public_title'];
		}
		
		if($meta_title==''){
			$meta_title=$title;
		}
		
		if(isset($topic_info['description'.$lang_postfix]) && $topic_info['description'.$lang_postfix]!=''){
			$this->template->assign('description', $topic_info['description'.$lang_postfix]);
		}elseif ( $topic_info['description'] != '' ) {
			$this->template->assign('description', $topic_info['description']);
		}
		if(isset($topic_info['meta_description'.$lang_postfix]) && $topic_info['meta_description'.$lang_postfix]!=''){
			$this->template->assign('meta_description', $topic_info['meta_description'.$lang_postfix]);
		}elseif ( $topic_info['meta_description'] != '' ) {
			$this->template->assign('meta_description', $topic_info['meta_description']);
		}
		if(isset($topic_info['meta_keywords'.$lang_postfix]) && $topic_info['meta_keywords'.$lang_postfix]!=''){
			$this->template->assign('meta_keywords', $topic_info['meta_keywords'.$lang_postfix]);
		}elseif ( $topic_info['meta_keywords'] != '' ) {
			$this->template->assign('meta_keywords', $topic_info['meta_keywords']);
		}
		
		if(intval($this->getRequestValue('page')) > 1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
			if(0==(int)$this->getConfigValue('add_pagenumber_title_place') && $title!=''){
				$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
			}elseif(1==(int)$this->getConfigValue('add_pagenumber_title_place') && $meta_title!=''){
				$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
			}elseif(2==(int)$this->getConfigValue('add_pagenumber_title_place')){
				if($title!=''){
					$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}
				if($meta_title!=''){
					$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}
			}
		}
		
		$this->template->assign('title', $title);
		$this->template->assign('meta_title', $meta_title);
	
		$grid_constructor=$this->_getGridConstructor();
	
		$params=$this->gatherRequestParams();
		$this->setRequestValue('topic_id', intval($topic_info['id']));
		$params['topic_id']=intval($topic_info['id']);
		$grid_constructor->main($params);
	
		//$this->setRequestValue('city_id', (int)$city_info['city_id']);
		//$this->setRequestValue('city_view', $REQUESTURIPATH);
	}
	
	protected function FrontAction_grid_citytopic($REQUESTURIPATH, $info){
		
		$topic_info = $this->getTopicFullInfo($info[1]);
		$gorod_name = $info[2];
		
		if(1===intval($this->getConfigValue('apps.language.use_langs'))){
			$curlang=$this->getCurrentLang();
			$lang_postfix='_'.$curlang;
			if(1===intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang=='ru'){
				$lang_postfix='';
			}
		}
	
	
		if(isset($topic_info['meta_title'.$lang_postfix]) && $topic_info['meta_title'.$lang_postfix]!=''){
			$meta_title = $topic_info['meta_title'.$lang_postfix];
		}elseif($topic['meta_title'] != ''){
			$meta_title = $topic_info['meta_title'];
		}else{
			$meta_title='';
		}
	
		if(isset($topic_info['name'.$lang_postfix]) && $topic_info['name'.$lang_postfix]!=''){
			$title = $topic_info['name'.$lang_postfix];
		}else{
			$title = $topic_info['name'];
		}
	
		if(isset($topic_info['public_title'.$lang_postfix]) && $topic_info['public_title'.$lang_postfix]!=''){
			$title=$topic_info['public_title'.$lang_postfix];
				
		}elseif(isset($topic_info['public_title']) && $topic_info['public_title']!=''){
			$title=$topic_info['public_title'];
		}
	
		if($meta_title==''){
			$meta_title=$title;
		}
		
		
	
		if(isset($topic_info['description'.$lang_postfix]) && $topic_info['description'.$lang_postfix]!=''){
			$this->template->assign('description', $topic_info['description'.$lang_postfix]);
		}elseif ( $topic_info['description'] != '' ) {
			$this->template->assign('description', $topic_info['description']);
		}
		if(isset($topic_info['meta_description'.$lang_postfix]) && $topic_info['meta_description'.$lang_postfix]!=''){
			$this->template->assign('meta_description', $topic_info['meta_description'.$lang_postfix]);
		}elseif ( $topic_info['meta_description'] != '' ) {
			$this->template->assign('meta_description', $topic_info['meta_description']);
		}
		if(isset($topic_info['meta_keywords'.$lang_postfix]) && $topic_info['meta_keywords'.$lang_postfix]!=''){
			$this->template->assign('meta_keywords', $topic_info['meta_keywords'.$lang_postfix]);
		}elseif ( $topic_info['meta_keywords'] != '' ) {
			$this->template->assign('meta_keywords', $topic_info['meta_keywords']);
		}
	
		if ( $gorod_name ) {
			$title .= ' - '.$gorod_name;
		}
			
		if(intval($this->getRequestValue('page')) > 1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
			if(0==(int)$this->getConfigValue('add_pagenumber_title_place') && $title!=''){
				$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
			}elseif(1==(int)$this->getConfigValue('add_pagenumber_title_place') && $meta_title!=''){
				$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
			}elseif(2==(int)$this->getConfigValue('add_pagenumber_title_place')){
				if($title!=''){
					$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}
				if($meta_title!=''){
					$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}
			}
		}
		
		$this->template->assign('title', $title);
		$this->template->assign('meta_title', $meta_title);
	
		$grid_constructor=$this->_getGridConstructor();
	
		$params=$this->gatherRequestParams();
		$this->setRequestValue('topic_id', intval($topic_info['id']));
		$params['topic_id']=intval($topic_info['id']);
		$grid_constructor->main($params);
	
		//$this->setRequestValue('city_id', (int)$city_info['city_id']);
		//$this->setRequestValue('city_view', $REQUESTURIPATH);
	}
	
	protected function FrontAction_grid_predefined($REQUESTURIPATH, $predefined_info){
		if(1===intval($this->getConfigValue('apps.language.use_langs'))){
			$curlang=$this->getCurrentLang();
			$lang_postfix='_'.$curlang;
			if(1===intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang=='ru'){
				$lang_postfix='';
			}
		}else{
			$lang_postfix='';
		}
		
		if($lang_postfix!=''){
			foreach($predefined_info as $k=>$v){
				if(isset($predefined_info[$k.$lang_postfix]) && $predefined_info[$k.$lang_postfix]!=''){
					$predefined_info[$k]=$predefined_info[$k.$lang_postfix];
				}
			}
		}
		
		if(isset($predefined_info['meta_title'.$lang_postfix]) && $predefined_info['meta_title'.$lang_postfix] != '') {
			$meta_title = $predefined_info['meta_title'.$lang_postfix];
		}else{
			$meta_title = $predefined_info['meta_title'];
		}
		
		if(isset($predefined_info['title'.$lang_postfix]) && $predefined_info['title'.$lang_postfix]!=''){
			$title = $predefined_info['title'.$lang_postfix];
		}else{
			$title = $predefined_info['title'];
		}
		
		if($meta_title==''){
			$meta_title=$title;
		}
			
		if(intval($this->getRequestValue('page')) > 1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
			if(0==(int)$this->getConfigValue('add_pagenumber_title_place') && $title!=''){
				$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
			}elseif(1==(int)$this->getConfigValue('add_pagenumber_title_place') && $meta_title!=''){
				$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
			}elseif(2==(int)$this->getConfigValue('add_pagenumber_title_place')){
				if($title!=''){
					$title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}
				if($meta_title!=''){
					$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.intval($this->getRequestValue('page')).']';
				}
			}
		}
			
		$this->template->assign('title', $title);
		$this->template->assign('meta_title', $meta_title);
			
		if(isset($predefined_info['description'.$lang_postfix]) && $predefined_info['description'.$lang_postfix]!=''){
			$this->template->assign('description', $predefined_info['description'.$lang_postfix]);
		}elseif($predefined_info['description'] != ''){
			$this->template->assign('description', $predefined_info['description']);
		}
		if(isset($predefined_info['meta_description'.$lang_postfix]) && $predefined_info['meta_description'.$lang_postfix]!=''){
			$this->template->assign('meta_description', $predefined_info['meta_description'.$lang_postfix]);
		}elseif($predefined_info['meta_description'] != '') {
			$this->template->assign('meta_description', $predefined_info['meta_description']);
		}else{
			$this->template->assign('meta_description', $this->getConfigValue('meta_description_main'));
		}
		if(isset($predefined_info['meta_keywords'.$lang_postfix]) && $predefined_info['meta_keywords'.$lang_postfix]!=''){
			$this->template->assign('meta_keywords', $predefined_info['meta_keywords'.$lang_postfix]);
		}elseif ( $predefined_info['meta_keywords'] != '' ) {
			$this->template->assign('meta_keywords', $predefined_info['meta_keywords']);
		}else{
			$this->template->assign('meta_keywords', $this->getConfigValue('meta_keywords_main'));
		}
		
		
		$this->setRequestValue('predefined_info', $predefined_info);
	
		$grid_constructor=$this->_getGridConstructor();
	
		$params=$this->gatherRequestParams();
		if(count($predefined_info['params'])>0){
			foreach($predefined_info['params'] as $k=>$v){
				$this->setRequestValue($k, $v);
				$params[$k]=$v;
			}
		}
		//$this->setRequestValue('city_id', intval($city_info['city_id']));
		
		$grid_constructor->main($params);
	
		//$this->setRequestValue('city_id', (int)$city_info['city_id']);
		//$this->setRequestValue('city_view', $REQUESTURIPATH);
	}
	
	public function FrontAction_grid_custom($REQUESTURIPATH){
		return false;
	}
    
    /**
     * Grid adv
     * @param void
     * @return string
     */
	
	function grid_adv ($params=array()) {
		$country_url_catched=false;
		$find_url_catched=false;
		$city_url_catched=false;
		$metro_url_catched=false;
		$region_url_catched=false;
		$predefined_url_catched=false;
		$route_catched=false;
		$complex_url_catched=false;
		$REQUESTURIPATH=Sitebill::getClearRequestURI();
	
		$grid_constructor = $this->_getGridConstructor();
	
		//$SF=Sitebill_Registry::getInstance();
	
		//$SF->clearFeedback('catched_route');
		//$SF->clearFeedback('catched_route_params');
	
	
		/*$Sitebill_Registry=Sitebill_Registry::getInstance();
			if(1==(int)$Sitebill_Registry->getFeedback('route_catched')){
		$route_catched=true;
		}else{
		$route_catched=false;
		}*/
	
		if($REQUESTURIPATH=='find'){
			//$grid_constructor->setCatchedRoute('system:find');
			//$SF->addFeedback('catched_route', 'system:find');
			$find_url_catched=true;
			//$params['pager_url']='find';
		}elseif($REQUESTURIPATH!=''){
			$DBC=DBC::getInstance();
			if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/router/router.php')){
				require_once SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/router/router.php';
				$Router=new Router();
				if($Router->checkUrl($REQUESTURIPATH)){
					$route_catched=true;
					//$grid_constructor->setCatchedRoute('system:route');
					//$grid_constructor->setCatchedRouteParams($work_params['params']);
					$work_params=$Router->getWorkParams();
					foreach($work_params['params'] as $k=>$v){
						$this->setRequestValue($k, $v);
					}
				}
			}
				
				
			if(!$route_catched){
				if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/predefinedlinks/admin/admin.php')){
					require_once SITEBILL_DOCUMENT_ROOT.'/apps/predefinedlinks/admin/admin.php';
					$PDLA=new predefinedlinks_admin();
					if($predefined_info=$PDLA->checkAlias($REQUESTURIPATH)){
						$predefined_url_catched=true;
						//$grid_constructor->setCatchedRoute('system:predefinedlinks');
						//$grid_constructor->setCatchedRouteParams($predefined_info);
					}
				}
			}
				
			if(!$route_catched && !$predefined_url_catched){
				if(intval($this->getConfigValue('apps.seo.no_country_url'))===0){
					$query='SELECT * FROM '.DB_PREFIX.'_country WHERE url=? LIMIT 1';
					$stmt=$DBC->query($query, array($REQUESTURIPATH));
					if($stmt){
						$ar=$DBC->fetch($stmt);
	
						if((int)$ar['country_id']!=0){
							$country_url_catched=true;
							$country_info=$ar;
							//$grid_constructor->setCatchedRoute('system:country');
							//$grid_constructor->setCatchedRouteParams($ar);
						}
					}
				}
			}
				
			if(!$route_catched && !$predefined_url_catched && !$country_url_catched){
				if(intval($this->getConfigValue('apps.seo.no_region_url'))===0){
					$query='SELECT * FROM '.DB_PREFIX.'_region WHERE alias=? LIMIT 1';
					$stmt=$DBC->query($query, array($REQUESTURIPATH));
					if($stmt){
						$ar=$DBC->fetch($stmt);
						if((int)$ar['region_id']!=0){
							$region_url_catched=true;
							$region_info=$ar;
							//$grid_constructor->setCatchedRoute('system:region');
							//$grid_constructor->setCatchedRouteParams($ar);
						}
					}
				}
			}
				
			if(!$route_catched && !$predefined_url_catched && !$country_url_catched && !$region_url_catched){
				if(intval($this->getConfigValue('apps.seo.no_city_url'))===0){
					$query='SELECT * FROM '.DB_PREFIX.'_city WHERE url=? LIMIT 1';
					$stmt=$DBC->query($query, array($REQUESTURIPATH));
					if($stmt){
						$ar=$DBC->fetch($stmt);
							
						if((int)$ar['city_id']!=0){
							$city_url_catched=true;
							$city_info=$ar;
							//$grid_constructor->setCatchedRoute('system:city');
							//$grid_constructor->setCatchedRouteParams($ar);
						}
					}
				}
			}
			
			if(!$route_catched && !$predefined_url_catched && !$country_url_catched && !$region_url_catched && !$city_url_catched){
				if(intval($this->getConfigValue('apps.seo.no_metro_url'))===0){
					$query='SELECT * FROM '.DB_PREFIX.'_metro WHERE `alias`=? LIMIT 1';
					$stmt=$DBC->query($query, array($REQUESTURIPATH));
					if($stmt){
						$ar=$DBC->fetch($stmt);
							
						if((int)$ar['metro_id']!=0){
							$metro_url_catched=true;
							$metro_info=$ar;
						}
					}
				}
			}
				
			if(!$metro_url_catched && !$route_catched && !$predefined_url_catched && !$country_url_catched && !$region_url_catched && !$city_info){
				if($this->getConfigValue('apps.complex.enable')){
					$DBC=DBC::getInstance();
					$query='SELECT * FROM '.DB_PREFIX.'_complex WHERE url=? LIMIT 1';
					$stmt=$DBC->query($query, array($REQUESTURIPATH));
					if($stmt){
						$ar=$DBC->fetch($stmt);
						if(intval($ar['complex_id'])!==0){
							$complex_url_catched=true;
							$complex_info=$ar;
							//$grid_constructor->setCatchedRoute('system:complex');
							//$grid_constructor->setCatchedRouteParams($ar);
						}
					}
				}
			}
			/*
				if(!$route_catched){
			if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/predefinedlinks/admin/admin.php')){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/predefinedlinks/admin/admin.php';
			$PDLA=new predefinedlinks_admin();
			if($predefined_info=$PDLA->checkAlias($REQUESTURIPATH)){
			$predefined_url_catched=true;
			}
			}
				
				
	
			if(!$predefined_url_catched){
			if(intval($this->getConfigValue('apps.seo.no_country_url'))===0){
			$query='SELECT * FROM '.DB_PREFIX.'_country WHERE url=? LIMIT 1';
			$stmt=$DBC->query($query, array($REQUESTURIPATH));
			if($stmt){
			$ar=$DBC->fetch($stmt);
	
			if((int)$ar['country_id']!=0){
			$country_url_catched=true;
			$country_info=$ar;
			}
			}
			}
				
				
			if(!$country_url_catched){
			if(intval($this->getConfigValue('apps.seo.no_city_url'))===0){
			$query='SELECT * FROM '.DB_PREFIX.'_city WHERE url=? LIMIT 1';
			$stmt=$DBC->query($query, array($REQUESTURIPATH));
			if($stmt){
			$ar=$DBC->fetch($stmt);
				
			if((int)$ar['city_id']!=0){
			$city_url_catched=true;
			$city_info=$ar;
			}
			}
			}
	
			}
			}
	
	
				
			if(!$predefined_url_catched && !$country_url_catched && !$city_url_catched){
			$region_templates=array('region/[:alias]', 'region/[:id]', '[:alias]');
			$query='SELECT * FROM '.DB_PREFIX.'_region WHERE alias=? LIMIT 1';
			$stmt=$DBC->query($query, array($REQUESTURIPATH));
			if($stmt){
			$ar=$DBC->fetch($stmt);
				
			if((int)$ar['region_id']!=0){
			$region_url_catched=true;
			$region_info=$ar;
			}
			}
			}
				
			if ( $this->getConfigValue('apps.complex.enable') ) {
			$DBC=DBC::getInstance();
			$query='SELECT * FROM '.DB_PREFIX.'_complex WHERE url=? LIMIT 1';
			$stmt=$DBC->query($query, array($REQUESTURIPATH));
			if($stmt){
			$ar=$DBC->fetch($stmt);
				
			if((int)$ar['complex_id']!=0){
			$complex_url_catched=true;
			$complex_info=$ar;
			}
			}
			}
			}
			*/
				
				
				
		}
	
	
		$gorod_name = false;
		//$grid_constructor = $this->_grid_constructor;
	
		if($find_url_catched){
			if(Multilanguage::is_set('LT_FIND_URL_TITLE', '_template')){
				$title = Multilanguage::_('LT_FIND_URL_TITLE', '_template');
			}else{
				$title = Multilanguage::_('FIND_URL_TITLE', 'system');
			}
				
			$this->template->assign('title', $title);
			$this->template->assign('meta_title', $title);
			$this->setRequestValue('find_url_catched', 1);
		}elseif($route_catched){
			//$work_params=$Router->getWorkParams();
			//$this->setRequestValue('router_info', $work_params);
		}elseif($predefined_url_catched){
			if(1===intval($this->getConfigValue('apps.language.use_langs'))){
				$curlang=$this->getCurrentLang();
				$lang_postfix='_'.$curlang;
				if(1===intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang=='ru'){
					$lang_postfix='';
				}
			}else{
				$lang_postfix='';
			}
				
			if(isset($predefined_info['meta_title'.$lang_postfix]) && $predefined_info['meta_title'.$lang_postfix] != '') {
				$meta_title = $predefined_info['meta_title'.$lang_postfix];
			}else{
				$meta_title = $predefined_info['meta_title'];
			}
				
			if(isset($predefined_info['title'.$lang_postfix]) && $predefined_info['title'.$lang_postfix]!=''){
				$title = $predefined_info['title'.$lang_postfix];
			}else{
				$title = $predefined_info['title'];
			}
				
			if($meta_title==''){
				$meta_title=$title;
			}
				
				
				
			if ( (int)$this->getRequestValue('page') > 0 && (int)$this->getRequestValue('page')!=1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
				if(0==(int)$this->getConfigValue('add_pagenumber_title_place')){
					$title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
				}elseif(1==(int)$this->getConfigValue('add_pagenumber_title_place')){
					$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
				}elseif(2==(int)$this->getConfigValue('add_pagenumber_title_place')){
					$title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
					$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
				}
			}
	
			$this->template->assign('title', $title);
			$this->template->assign('meta_title', $meta_title);
	
			if(isset($predefined_info['description'.$lang_postfix]) && $predefined_info['description'.$lang_postfix]!=''){
				$this->template->assign('description', $predefined_info['description'.$lang_postfix]);
			}elseif($predefined_info['description'] != ''){
				$this->template->assign('description', $predefined_info['description']);
			}
			if(isset($predefined_info['meta_description'.$lang_postfix]) && $predefined_info['meta_description'.$lang_postfix]!=''){
				$this->template->assign('meta_description', $predefined_info['meta_description'.$lang_postfix]);
			}elseif($predefined_info['meta_description'] != '') {
				$this->template->assign('meta_description', $predefined_info['meta_description']);
			}else{
				$this->template->assign('meta_description', $this->getConfigValue('meta_description_main'));
			}
			if(isset($predefined_info['meta_keywords'.$lang_postfix]) && $predefined_info['meta_keywords'.$lang_postfix]!=''){
				$this->template->assign('meta_keywords', $predefined_info['meta_keywords'.$lang_postfix]);
			}elseif ( $predefined_info['meta_keywords'] != '' ) {
				$this->template->assign('meta_keywords', $predefined_info['meta_keywords']);
			}else{
				$this->template->assign('meta_keywords', $this->getConfigValue('meta_keywords_main'));
			}
			if(count($predefined_info['params'])>0){
				foreach($predefined_info['params'] as $k=>$v){
					$this->setRequestValue($k, $v);
				}
			}
			
			$this->setRequestValue('predefined_info', $predefined_info);
				
		}elseif($country_url_catched){
				
			if(1===intval($this->getConfigValue('apps.language.use_langs'))){
				$curlang=$this->getCurrentLang();
				$lang_postfix='_'.$curlang;
				if(1===intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang=='ru'){
					$lang_postfix='';
				}
			}else{
				$lang_postfix='';
			}
			$meta_title='';
			if(isset($country_info['meta_title'.$lang_postfix]) && $country_info['meta_title'.$lang_postfix]!=''){
				$meta_title = $country_info['meta_title'.$lang_postfix];
			}elseif($country_info['meta_title'] != ''){
				$meta_title = $country_info['meta_title'];
			}
				
			if(isset($country_info['name'.$lang_postfix]) && $country_info['name'.$lang_postfix]!=''){
				$title = $country_info['name'.$lang_postfix];
			}else{
				$title = $country_info['name'];
			}
				
			if($meta_title==''){
				$meta_title=$title;
			}
				
			if ( (int)$this->getRequestValue('page') > 0 && (int)$this->getRequestValue('page')!=1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
				if(0==(int)$this->getConfigValue('add_pagenumber_title_place')){
					$title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
				}elseif(1==(int)$this->getConfigValue('add_pagenumber_title_place')){
					$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
				}elseif(2==(int)$this->getConfigValue('add_pagenumber_title_place')){
					$title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
					$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
				}
			}
				
			$this->template->assign('title', $title);
			$this->template->assign('meta_title', $meta_title);
				
			if(isset($country_info['description'.$lang_postfix]) && $country_info['description'.$lang_postfix]!=''){
				$this->template->assign('description', $country_info['description'.$lang_postfix]);
			}elseif ( $country_info['description'] != '' ) {
				$this->template->assign('description', $country_info['description']);
			}
			if(isset($country_info['meta_description'.$lang_postfix]) && $country_info['meta_description'.$lang_postfix]!=''){
				$this->template->assign('meta_description', $country_info['meta_description'.$lang_postfix]);
			}elseif ( $country_info['meta_description'] != '' ) {
				$this->template->assign('meta_description', $country_info['meta_description']);
			}else{
				$this->template->assign('meta_description', $this->getConfigValue('meta_description_main'));
			}
			if(isset($country_info['meta_keywords'.$lang_postfix]) && $country_info['meta_keywords'.$lang_postfix]!=''){
				$this->template->assign('meta_keywords', $country_info['meta_keywords'.$lang_postfix]);
			}elseif ( $country_info['meta_keywords'] != '' ) {
				$this->template->assign('meta_keywords', $country_info['meta_keywords']);
			}else{
				$this->template->assign('meta_keywords', $this->getConfigValue('meta_keywords_main'));
			}
				
				
			$this->setRequestValue('country_id', (int)$country_info['country_id']);
			$this->setRequestValue('country_view', $REQUESTURIPATH);
		} elseif($city_url_catched) {
				
			if(1===intval($this->getConfigValue('apps.language.use_langs'))){
				$curlang=$this->getCurrentLang();
				$lang_postfix='_'.$curlang;
				if(1===intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang=='ru'){
					$lang_postfix='';
				}
			}else{
				$lang_postfix='';
			}
				
			if(isset($city_info['public_title'.$lang_postfix]) && $city_info['public_title'.$lang_postfix]!=''){
				$title = $city_info['public_title'.$lang_postfix];
			}elseif(isset($city_info['public_title']) && $city_info['public_title']!=''){
				$title = $city_info['public_title'];
			}else{
				$title = $city_info['name'];
			}
			if(isset($city_info['meta_title'.$lang_postfix]) && $city_info['meta_title'.$lang_postfix]!=''){
				$meta_title = $city_info['meta_title'.$lang_postfix];
			}elseif ( $city_info['meta_title'] != '' ) {
				$meta_title = $city_info['meta_title'];
			} else {
				$meta_title = $title;
			}
				
			if ( (int)$this->getRequestValue('page') > 0 && (int)$this->getRequestValue('page')!=1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
				$title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
			}
	
			$this->template->assign('title', $title);
			$this->template->assign('meta_title', $meta_title);
	
			if(isset($city_info['description'.$lang_postfix]) && $city_info['description'.$lang_postfix]!=''){
				$this->template->assign('description', $city_info['description'.$lang_postfix]);
			}elseif ( $city_info['description'] != '' ) {
				$this->template->assign('description', $city_info['description']);
			}
			if(isset($city_info['meta_description'.$lang_postfix]) && $city_info['meta_description'.$lang_postfix]!=''){
				$this->template->assign('meta_description', $city_info['meta_description'.$lang_postfix]);
			}elseif ( $city_info['meta_description'] != '' ) {
				$this->template->assign('meta_description', $city_info['meta_description']);
			}else{
				$this->template->assign('meta_description', $this->getConfigValue('meta_description_main'));
			}
			if(isset($city_info['meta_keywords'.$lang_postfix]) && $city_info['meta_keywords'.$lang_postfix]!=''){
				$this->template->assign('meta_keywords', $city_info['meta_keywords'.$lang_postfix]);
			}elseif ( $city_info['meta_keywords'] != '' ) {
				$this->template->assign('meta_keywords', $city_info['meta_keywords']);
			}else{
				$this->template->assign('meta_keywords', $this->getConfigValue('meta_keywords_main'));
			}
	
	
			$this->setRequestValue('city_id', (int)$city_info['city_id']);
			$this->setRequestValue('city_view', $REQUESTURIPATH);
				
		}  elseif($metro_url_catched) {
				
			if(1===intval($this->getConfigValue('apps.language.use_langs'))){
				$curlang=$this->getCurrentLang();
				$lang_postfix='_'.$curlang;
				if(1===intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang=='ru'){
					$lang_postfix='';
				}
			}else{
				$lang_postfix='';
			}
				
			if(isset($metro_info['public_title'.$lang_postfix]) && $metro_info['public_title'.$lang_postfix]!=''){
				$title = $metro_info['public_title'.$lang_postfix];
			}elseif(isset($metro_info['public_title']) && $metro_info['public_title']!=''){
				$title = $metro_info['public_title'];
			}else{
				$title = $metro_info['name'];
			}
			if(isset($metro_info['meta_title'.$lang_postfix]) && $metro_info['meta_title'.$lang_postfix]!=''){
				$meta_title = $metro_info['meta_title'.$lang_postfix];
			}elseif ( $metro_info['meta_title'] != '' ) {
				$meta_title = $metro_info['meta_title'];
			} else {
				$meta_title = $title;
			}
				
			if ( (int)$this->getRequestValue('page') > 0 && (int)$this->getRequestValue('page')!=1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
				$title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
			}
	
			$this->template->assign('title', $title);
			$this->template->assign('meta_title', $meta_title);
	
			if(isset($metro_info['description'.$lang_postfix]) && $metro_info['description'.$lang_postfix]!=''){
				$this->template->assign('description', $metro_info['description'.$lang_postfix]);
			}elseif ( $metro_info['description'] != '' ) {
				$this->template->assign('description', $metro_info['description']);
			}
			if(isset($metro_info['meta_description'.$lang_postfix]) && $metro_info['meta_description'.$lang_postfix]!=''){
				$this->template->assign('meta_description', $metro_info['meta_description'.$lang_postfix]);
			}elseif ( $metro_info['meta_description'] != '' ) {
				$this->template->assign('meta_description', $metro_info['meta_description']);
			}else{
				$this->template->assign('meta_description', $this->getConfigValue('meta_description_main'));
			}
			if(isset($metro_info['meta_keywords'.$lang_postfix]) && $metro_info['meta_keywords'.$lang_postfix]!=''){
				$this->template->assign('meta_keywords', $metro_info['meta_keywords'.$lang_postfix]);
			}elseif ( $metro_info['meta_keywords'] != '' ) {
				$this->template->assign('meta_keywords', $metro_info['meta_keywords']);
			}else{
				$this->template->assign('meta_keywords', $this->getConfigValue('meta_keywords_main'));
			}
	
	
			$this->setRequestValue('metro_id', (int)$metro_info['metro_id']);
			$this->setRequestValue('metro_view', $REQUESTURIPATH);
				
		} elseif($region_url_catched) {
				
			if(1===intval($this->getConfigValue('apps.language.use_langs'))){
				$curlang=$this->getCurrentLang();
				$lang_postfix='_'.$curlang;
				if(1===intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang=='ru'){
					$lang_postfix='';
				}
			}else{
				$lang_postfix='';
			}
				
			if(isset($region_info['public_title']) && $region_info['public_title']!=''){
				$title = $region_info['public_title'];
			}else{
				$title = $region_info['name'];
			}
			if ( $region_info['meta_title'] != '' ) {
				$meta_title = $region_info['meta_title'];
			} else {
				$meta_title = $region_info['name'];
			}
				
			if ( (int)$this->getRequestValue('page') > 0 && (int)$this->getRequestValue('page')!=1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
				if(0==(int)$this->getConfigValue('add_pagenumber_title_place')){
					$title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
				}elseif(1==(int)$this->getConfigValue('add_pagenumber_title_place')){
					$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
				}elseif(2==(int)$this->getConfigValue('add_pagenumber_title_place')){
					$title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
					$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
				}
			}
	
			$this->template->assign('title', $title);
			$this->template->assign('meta_title', $meta_title);
	
			if ( $region_info['description'] != '' ) {
				$this->template->assign('description', $region_info['description']);
			}
			if ( $region_info['meta_description'] != '' ) {
				$this->template->assign('meta_description', $region_info['meta_description']);
			}else{
				$this->template->assign('meta_description', $this->getConfigValue('meta_description_main'));
			}
			if ( $region_info['meta_keywords'] != '' ) {
				$this->template->assign('meta_keywords', $region_info['meta_keywords']);
			}else{
				$this->template->assign('meta_keywords', $this->getConfigValue('meta_keywords_main'));
			}
	
	
			$this->setRequestValue('region_id', (int)$region_info['region_id']);
			$this->setRequestValue('region_view', $REQUESTURIPATH);
		} elseif ($complex_url_catched){
			require_once (SITEBILL_DOCUMENT_ROOT.'/apps/complex/admin/admin.php');
			$complex_admin = new complex_admin();
			$data_model = new Data_Model();
			$complex_data = $complex_admin->data_model;
			$complex_data = $data_model->init_model_data_from_db ( 'complex', 'complex_id', (int)$ar['complex_id'], $complex_data['complex'], true );
			$complex_data['image']['image_array'] = $this->get_image_array('complex', 'complex', 'complex_id', (int)$ar['complex_id']);
			/*
				echo '<pre>';
			print_r($complex_data);
			echo '</pre>';
			*/
			$this->template->assign('complex_data', $complex_data);
	
				
			if ( $complex_info['meta_title'] != '' ) {
				$title = $complex_info['name'];
				$meta_title = $complex_info['meta_title'];
			} else {
				$title = $meta_title = $complex_info['name'];
			}
				
			if ( (int)$this->getRequestValue('page') > 0 && (int)$this->getRequestValue('page')!=1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
				if(0==(int)$this->getConfigValue('add_pagenumber_title_place')){
					$title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
				}elseif(1==(int)$this->getConfigValue('add_pagenumber_title_place')){
					$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
				}elseif(2==(int)$this->getConfigValue('add_pagenumber_title_place')){
					$title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
					$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
				}
			}
				
			$this->template->assign('title', $title);
			$this->template->assign('meta_title', $meta_title);
				
			if ( $complex_info['description'] != '' ) {
				$this->template->assign('description', $complex_info['description']);
			}
			if ( $complex_info['meta_description'] != '' ) {
				$this->template->assign('meta_description', $complex_info['meta_description']);
			}else{
				$this->template->assign('meta_description', $this->getConfigValue('meta_description_main'));
			}
			if ( $complex_info['meta_keywords'] != '' ) {
				$this->template->assign('meta_keywords', $complex_info['meta_keywords']);
			}else{
				$this->template->assign('meta_keywords', $this->getConfigValue('meta_keywords_main'));
			}
				
				
			$this->setRequestValue('complex_id', (int)$ar['complex_id']);
			$this->setRequestValue('complex_view', $REQUESTURIPATH);
		}else{
			$result=$this->_detectUrlParams(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
				
				
			if($result['topic_id']/* && !is_array($result['topic_id'])*/){
				$this->setRequestValue('topic_id', $result['topic_id']);
			}
			if($result['city_id']){
				$this->setRequestValue('city_id', $result['city_id']);
			}
			$gorod_name=$result['gorod_name'];
				
				
				
				
				
				
			$url_info = parse_url($_SERVER['REQUEST_URI']);
			if ( SITEBILL_MAIN_URL != '' ) {
				$cmp_url = SITEBILL_MAIN_URL.'/';
			} else {
				$cmp_url = '/';
			}
			if ( $this->getRequestValue('country_id') == '' && $this->getRequestValue('city_id') == '' && $this->getRequestValue('topic_id') == '' and ($url_info['path'] != $cmp_url and $url_info['path'] != $cmp_url.'index.php' and $url_info['path'] != $cmp_url.'search/') and $this->getRequestValue('user_id') === NULL) {
				$sapi_name = php_sapi_name();
	
				if ($sapi_name == 'cgi' || $sapi_name == 'cgi-fcgi') {
					header('Status: 404 Not Found');
				} else {
					header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
				}
				$this->template->assign('title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
				$this->template->assign('meta_title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
				$this->template->assign('error_message', '<h1>'.Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND').'</h1>');
				$this->template->assign('main_file_tpl', 'error_message.tpl');
				//exit();
				//echo 1;
				return false;
			} elseif ( ( !is_array($result['topic_id']) && $this->getRequestValue('topic_id') > 0 ) or ($gorod_name != '' and is_array($this->getRequestValue('topic_id'))) ) {
				if ( is_array($this->getRequestValue('topic_id')) ) {
					$tmp_tppc = $this->getRequestValue('topic_id');
					$topic = $this->getTopicFullInfo($tmp_tppc[0]);
				} else {
					$topic = $this->getTopicFullInfo($this->getRequestValue('topic_id'));
				}
	
				if(1===intval($this->getConfigValue('apps.language.use_langs'))){
					$curlang=$this->getCurrentLang();
					$lang_postfix='_'.$curlang;
					if(1===intval($this->getConfigValue('apps.language.use_default_as_ru')) && $curlang=='ru'){
						$lang_postfix='';
					}
				}
	
	
				if(isset($topic['meta_title'.$lang_postfix]) && $topic['meta_title'.$lang_postfix]!=''){
					$meta_title = $topic['meta_title'.$lang_postfix];
				}elseif($topic['meta_title'] != ''){
					$meta_title = $topic['meta_title'];
				}else{
					$meta_title='';
				}
	
				if(isset($topic['name'.$lang_postfix]) && $topic['name'.$lang_postfix]!=''){
					$title = $topic['name'.$lang_postfix];
				}else{
					$title = $topic['name'];
				}
	
				if(isset($topic['public_title'.$lang_postfix]) && $topic['public_title'.$lang_postfix]!=''){
					$title=$topic['public_title'.$lang_postfix];
						
				}elseif(isset($topic['public_title']) && $topic['public_title']!=''){
					$title=$topic['public_title'];
					/*if($meta_title==''){
					 $meta_title=$title;
					}*/
				}
	
				if($meta_title==''){
					$meta_title=$title;
				}
	
				/*if($meta_title==''){
				 $meta_title=$topic['name'];
				}*/
	
				/*if ( $topic['meta_title'] != '' ) {
				 $title = $topic['name'];
				$meta_title = $topic['meta_title'];
				} else {
				$title = $meta_title = $topic['name'];
				}
	
				if(isset($topic['public_title']) && $topic['public_title']!=''){
				$title=$topic['public_title'];
				$meta_title=$topic['public_title'];
				}*/
	
				if(isset($topic['description'.$lang_postfix]) && $topic['description'.$lang_postfix]!=''){
					$this->template->assign('description', $topic['description'.$lang_postfix]);
				}elseif ( $topic['description'] != '' ) {
					$this->template->assign('description', $topic['description']);
				}
				if(isset($topic['meta_description'.$lang_postfix]) && $topic['meta_description'.$lang_postfix]!=''){
					$this->template->assign('meta_description', $topic['meta_description'.$lang_postfix]);
				}elseif ( $topic['meta_description'] != '' ) {
					$this->template->assign('meta_description', $topic['meta_description']);
				}
				if(isset($topic['meta_keywords'.$lang_postfix]) && $topic['meta_keywords'.$lang_postfix]!=''){
					$this->template->assign('meta_keywords', $topic['meta_keywords'.$lang_postfix]);
				}elseif ( $topic['meta_keywords'] != '' ) {
					$this->template->assign('meta_keywords', $topic['meta_keywords']);
				}
				if ( $gorod_name ) {
					$title .= ' - '.$gorod_name;
				}
					
				if ( (int)$this->getRequestValue('page') > 0 && (int)$this->getRequestValue('page')!=1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
					if(0==(int)$this->getConfigValue('add_pagenumber_title_place')){
						$title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
					}elseif(1==(int)$this->getConfigValue('add_pagenumber_title_place')){
						$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
					}elseif(2==(int)$this->getConfigValue('add_pagenumber_title_place')){
						$title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
						$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
					}
				}
				$this->template->assign('title', $title);
				$this->template->assign('meta_title', $meta_title);
			} else {
				if ( $this->getConfigValue('meta_title_main')!='' ) {
					$title = $this->getConfigValue('site_title');
					$meta_title = $this->getConfigValue('meta_title_main');
				} else {
					$title = $meta_title = $this->getConfigValue('site_title');
				}
				//$title = ($this->getConfigValue('meta_title_main')!='' ? $this->getConfigValue('meta_title_main') : $this->getConfigValue('site_title'));
				if ( (int)$this->getRequestValue('page') > 0 && (int)$this->getRequestValue('page')!=1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
					if(0==(int)$this->getConfigValue('add_pagenumber_title_place')){
						$title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
					}elseif(1==(int)$this->getConfigValue('add_pagenumber_title_place')){
						$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
					}elseif(2==(int)$this->getConfigValue('add_pagenumber_title_place')){
						$title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
						$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
					}
				}
				if(preg_match('/user(\d+).html/', $_SERVER['REQUEST_URI'], $matches)){
					$user_id=$matches[1];
					$fio='';
					if(0!==(int)$user_id){
						$DBC=DBC::getInstance();
						$query='SELECT fio FROM '.DB_PREFIX.'_user WHERE user_id=? LIMIT 1';
						$stmt=$DBC->query($query, array((int)$user_id));
						if($stmt){
							$ar=$DBC->fetch($stmt);
							$fio=$ar['fio'];
						}
					}
					$title = Multilanguage::_('AGENT_ADS','system').' '.$fio;
					$meta_title=$title;
				}elseif((int)$this->getRequestValue('user_id')!=0){
					$user_id=(int)$this->getRequestValue('user_id');
					$fio='';
					if(0!==(int)$user_id){
						$DBC=DBC::getInstance();
						$query='SELECT fio FROM '.DB_PREFIX.'_user WHERE user_id=? LIMIT 1';
						$stmt=$DBC->query($query, array((int)$user_id));
						if($stmt){
							$ar=$DBC->fetch($stmt);
							$fio=$ar['fio'];
						}
					}
						
					$title = Multilanguage::_('AGENT_ADS','system').' '.$fio;
					$meta_title=$title;
				}
				//$meta_title=$title;
				if ( !$this->lock_title ) {
					$this->template->assign('title', $title);
				}
				$this->template->assign('meta_title', $meta_title);
				$this->template->assign('meta_description', $this->getConfigValue('meta_description_main'));
				$this->template->assign('meta_keywords', $this->getConfigValue('meta_keywords_main'));
			}
		}
	
		
		
	
	
	
	
		$this->setGridViewType();
	
	
		if($route_catched){
				
		}elseif($predefined_url_catched){
				
	
		}elseif($country_url_catched){
	
				
		} elseif($city_url_catched) {
			if(method_exists($this, 'cityFrontPage')){
				return $this->cityFrontPage($city_info);
			}
		} elseif($region_url_catched) {
				
		} elseif ($complex_url_catched){
				
		}else{
				
		}
	
		$params_r=$this->gatherRequestParams();
		if(!empty($params)){
			$params=array_merge($params, $params_r);
		}else{
			$params=$params_r;
		}
	
		$grid_constructor->main($params);
	
	
		return '';
	}
	
	function grid_adv2 ($params=array()) {
		$country_url_catched=false;
		$find_url_catched=false;
		$city_url_catched=false;
		$region_url_catched=false;
		$predefined_url_catched=false;
		$route_catched=false;
		$REQUESTURIPATH=Sitebill::getClearRequestURI();
		
		$grid_constructor = $this->_getGridConstructor();
	//	echo 1;
		$this->setGridViewType();
		
		if($REQUESTURIPATH!=''){
			
			$trailing_slashe='/';
			if(1==(int)$this->getConfigValue('apps.seo.no_trailing_slashes')){
				$trailing_slashe='';
			}
			
			if($REQUESTURIPATH=='find'){
				return $this->FrontAction_grid_find($REQUESTURIPATH);
			}
			
			if($REQUESTURIPATH=='myfavorites'){
				return $this->FrontAction_grid_favorites($REQUESTURIPATH);
			}
			
			
			
			if(preg_match('/^user(\d).html$/', $REQUESTURIPATH, $matches)){
				return $this->FrontAction_grid_user($REQUESTURIPATH, array('user_id'=>$matches[1]));
			}
			
			
			
			if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/predefinedlinks/admin/admin.php')){
				require_once SITEBILL_DOCUMENT_ROOT.'/apps/predefinedlinks/admin/admin.php';
				$PDLA=new predefinedlinks_admin();
				if($predefined_info=$PDLA->checkAlias($REQUESTURIPATH)){
					return $this->FrontAction_grid_predefined($REQUESTURIPATH, $predefined_info);
				}
			}
			
			
			$DBC=DBC::getInstance();
			
			if(intval($this->getConfigValue('apps.seo.no_country_url'))===0){
				$query='SELECT * FROM '.DB_PREFIX.'_country WHERE url=? LIMIT 1';
				$stmt=$DBC->query($query, array($REQUESTURIPATH));
				if($stmt){
					$ar=$DBC->fetch($stmt);
			
					if((int)$ar['country_id']!=0){
						if($ar['url']!=$REQUESTURIPATH){
							$new_location=SITEBILL_MAIN_URL.'/'.$ar['url'].$trailing_slashe;
							$this->go301($new_location);
						}
						return $this->FrontAction_grid_country($REQUESTURIPATH, $ar);
					}
				}
			}
			
			if(intval($this->getConfigValue('apps.seo.no_region_url'))===0){
				$query='SELECT * FROM '.DB_PREFIX.'_region WHERE alias=? LIMIT 1';
				$stmt=$DBC->query($query, array($REQUESTURIPATH));
				if($stmt){
					$ar=$DBC->fetch($stmt);
					if((int)$ar['region_id']!=0){
						if($ar['alias']!=$REQUESTURIPATH){
							$new_location=SITEBILL_MAIN_URL.'/'.$ar['alias'].$trailing_slashe;
							$this->go301($new_location);
						}
						return $this->FrontAction_grid_region($REQUESTURIPATH, $ar);
					}
				}
			}
			
			if(intval($this->getConfigValue('apps.seo.no_city_url'))===0){
				$query='SELECT * FROM '.DB_PREFIX.'_city WHERE url=? LIMIT 1';
				$stmt=$DBC->query($query, array($REQUESTURIPATH));
				if($stmt){
					$ar=$DBC->fetch($stmt);
						
					if((int)$ar['city_id']!=0){
						if($ar['url']!=$REQUESTURIPATH){
							$new_location=SITEBILL_MAIN_URL.'/'.$ar['url'].$trailing_slashe;
							$this->go301($new_location);
						}
						return $this->FrontAction_grid_city($REQUESTURIPATH, $ar);
					}
				}
			}
			
			if($this->getConfigValue('apps.complex.enable') && intval($this->getConfigValue('apps.complex.no_grid_catch'))===0){
				$DBC=DBC::getInstance();
				$query='SELECT * FROM '.DB_PREFIX.'_complex WHERE url=? LIMIT 1';
				$stmt=$DBC->query($query, array($REQUESTURIPATH));
				if($stmt){
					$ar=$DBC->fetch($stmt);
					if(intval($ar['complex_id'])!==0){
						if($ar['url']!=$REQUESTURIPATH){
							$new_location=SITEBILL_MAIN_URL.'/'.$ar['url'].$trailing_slashe;
							$this->go301($new_location);
						}
						return $this->FrontAction_grid_complex($REQUESTURIPATH, $ar);
					}
				}
			}
			
			if(intval($this->getConfigValue('apps.seo.no_topic_url'))===0){
				if(preg_match('/topic(\d*).html/', $REQUESTURIPATH, $matches)){
					$topic_id=(int)$matches[1];
					require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
					$Structure=new Structure_Manager();
					$urls=$Structure->loadCategoriesUrls();
					
					if(isset($urls[$topic_id]) && $urls[$topic_id]!=''){
						$new_location=SITEBILL_MAIN_URL.'/'.$urls[$topic_id].$trailing_slashe;
						$this->go301($new_location);
						exit();
					}elseif(isset($urls[$topic_id])){
						$topic_info=$this->getTopicFullInfo($topic_id);
						return $this->FrontAction_grid_topic($REQUESTURIPATH, $topic_info);
					}
				}else{
					$topic_id=0;
					require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
					$Structure=new Structure_Manager();
					$urls=$Structure->loadCategoriesUrls();
					foreach($urls as $k=>$v){
						if($v==''){
							unset($urls[$k]);
						}
					}	
					$urls_to_ids=array_flip($urls);
					if(isset($urls_to_ids[$REQUESTURIPATH])){
						$topic_id=$urls_to_ids[$REQUESTURIPATH];
					}
					
					if($topic_id>0){
						$topic_info=$this->getTopicFullInfo($topic_id);
						return $this->FrontAction_grid_topic($REQUESTURIPATH, $topic_info);
					}
				}
			}
			
			if(intval($this->getConfigValue('apps.seo.no_city_topic_url'))===0){
				$x=$this->cityTopicUrlFind($REQUESTURIPATH);
				if(false!=$x){
					return $this->FrontAction_grid_citytopic($REQUESTURIPATH, $x);
				}
			}
			
			/*if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/trouter/trouter.php')){
				require_once SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/trouter/trouter.php';
				$Router=new TRouter();
				$Router->setAlias($REQUESTURIPATH);
				if($Router->detectAlias()){
					return $Router->run();
				}
			}*/
			
			if(false!==($r=$this->FrontAction_grid_custom($REQUESTURIPATH))){
				$c=$r[0];
				if(method_exists($this, $c)){
					return $this->$c($REQUESTURIPATH, $r[1]);
				}
			}
			
			if(intval($this->getConfigValue('apps.seo.no_index_search'))===0 && $REQUESTURIPATH=='index.php'){
				return $this->FrontAction_grid_common();
			}
			
			return $this->FrontAction_404($REQUESTURIPATH);
		}else{
			if(intval($this->getConfigValue('apps.seo.no_index_search'))===0){
				return $this->FrontAction_grid_common();
			}else{
				return $this->FrontAction_index();
			}
			
			
			
			
			
			
			/*$url_info = parse_url($_SERVER['REQUEST_URI']);
			if ( SITEBILL_MAIN_URL != '' ) {
				$cmp_url = SITEBILL_MAIN_URL.'/';
			} else {
				$cmp_url = '/';
			}
			if ( $this->getRequestValue('country_id') == '' && $this->getRequestValue('city_id') == '' && $this->getRequestValue('topic_id') == '' and ($url_info['path'] != $cmp_url and $url_info['path'] != $cmp_url.'index.php' and $url_info['path'] != $cmp_url.'search/') and $this->getRequestValue('user_id') === NULL) {
				$sapi_name = php_sapi_name();
				
				if ($sapi_name == 'cgi' || $sapi_name == 'cgi-fcgi') {
					header('Status: 404 Not Found');
				} else {
					header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
				}
				$this->template->assign('title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
				$this->template->assign('meta_title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
				$this->template->assign('error_message', '<h1>'.Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND').'</h1>');
				$this->template->assign('main_file_tpl', 'error_message.tpl');
				//exit();
				//echo 1;
				return false;
			} elseif ( ( !is_array($result['topic_id']) && $this->getRequestValue('topic_id') > 0 ) or ($gorod_name != '' and is_array($this->getRequestValue('topic_id'))) ) {
				
			} else {
				if ( $this->getConfigValue('meta_title_main')!='' ) {
					$title = $this->getConfigValue('site_title');
					$meta_title = $this->getConfigValue('meta_title_main');
				} else {
					$title = $meta_title = $this->getConfigValue('site_title');
				}
				//$title = ($this->getConfigValue('meta_title_main')!='' ? $this->getConfigValue('meta_title_main') : $this->getConfigValue('site_title'));
				if ( (int)$this->getRequestValue('page') > 0 && (int)$this->getRequestValue('page')!=1 && 1==$this->getConfigValue('add_pagenumber_title') ) {
					if(0==(int)$this->getConfigValue('add_pagenumber_title_place')){
						$title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
					}elseif(1==(int)$this->getConfigValue('add_pagenumber_title_place')){
						$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
					}elseif(2==(int)$this->getConfigValue('add_pagenumber_title_place')){
						$title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
						$meta_title .= ' ['.Multilanguage::_('L_PAGE').' '.$this->getRequestValue('page').']';
					}
				}
				if(preg_match('/user(\d+).html/', $_SERVER['REQUEST_URI'], $matches)){
					$user_id=$matches[1];
					$fio='';
					if(0!==(int)$user_id){
						$DBC=DBC::getInstance();
						$query='SELECT fio FROM '.DB_PREFIX.'_user WHERE user_id=? LIMIT 1';
						$stmt=$DBC->query($query, array((int)$user_id));
						if($stmt){
							$ar=$DBC->fetch($stmt);
							$fio=$ar['fio'];
						}
					}
					$title = Multilanguage::_('AGENT_ADS','system').' '.$fio;
					$meta_title=$title;
				}elseif((int)$this->getRequestValue('user_id')!=0){
					$user_id=(int)$this->getRequestValue('user_id');
					$fio='';
					if(0!==(int)$user_id){
						$DBC=DBC::getInstance();
						$query='SELECT fio FROM '.DB_PREFIX.'_user WHERE user_id=? LIMIT 1';
						$stmt=$DBC->query($query, array((int)$user_id));
						if($stmt){
							$ar=$DBC->fetch($stmt);
							$fio=$ar['fio'];
						}
					}
			
					$title = Multilanguage::_('AGENT_ADS','system').' '.$fio;
					$meta_title=$title;
				}
				//$meta_title=$title;
				if ( !$this->lock_title ) {
					$this->template->assign('title', $title);
				}
				$this->template->assign('meta_title', $meta_title);
				$this->template->assign('meta_description', $this->getConfigValue('meta_description_main'));
				$this->template->assign('meta_keywords', $this->getConfigValue('meta_keywords_main'));
			
			}*/
		}
		
		
		
		
		
		
		
		
		/*if($route_catched){
			
		}elseif($predefined_url_catched){
			
				
		}elseif($country_url_catched){
				
			
		} elseif($city_url_catched) {
			if(method_exists($this, 'cityFrontPage')){
				return $this->cityFrontPage($city_info);
			}
		} elseif($region_url_catched) {
			
		} elseif ($complex_url_catched){
			
		}else{
			
		}
		
		$params_r=$this->gatherRequestParams();
		if(!empty($params)){
			$params=array_merge($params, $params_r);
		}else{
			$params=$params_r;
		}
				
		$grid_constructor->main($params);
		

		return $rs;*/
	}
	
	public function gatherRequestParams(){
		$REQUESTURIPATH=SiteBill::getClearRequestURI();
		$params=array();
		
		/*if(NULL!==$this->getRequestValue('places')){
			$params['places'] = $this->getRequestValue('places');
		}*/
		
		if(NULL!==$this->getRequestValue('id')){
			if(is_array($this->getRequestValue('id'))){
				$params['id'] = $this->getRequestValue('id');
			}else{
				$params['id'] = (int)$this->getRequestValue('id');
			}
			
		}
		if(NULL!==$this->getRequestValue('topic_id')){
			$params['topic_id'] = $this->safeRequestParams($this->getRequestValue('topic_id'));
		}
		if(NULL!==$this->getRequestValue('order')){
			$params['order'] = $this->getRequestValue('order');
		}
		if(NULL!==$this->getRequestValue('region_id')){
			$params['region_id'] = $this->safeRequestParams($this->getRequestValue('region_id'));
		}
		if(NULL!==$this->getRequestValue('city_id')){
			$params['city_id'] = $this->safeRequestParams($this->getRequestValue('city_id'));
		}
		if(NULL!==$this->getRequestValue('district_id')){
			$params['district_id'] = $this->safeRequestParams($this->getRequestValue('district_id'));
		}
		if(NULL!==$this->getRequestValue('country_id')){
			$params['country_id'] = $this->safeRequestParams($this->getRequestValue('country_id'));
		}
		if(NULL!==$this->getRequestValue('metro_id')){
			$params['metro_id'] = $this->safeRequestParams($this->getRequestValue('metro_id'));
		}
		
		if(NULL!==$this->getRequestValue('street_id')){
			$params['street_id'] = $this->safeRequestParams($this->getRequestValue('street_id'));
		}
		
		
		if ( $this->getConfigValue('apps.complex.enable') && NULL!==$this->getRequestValue('complex_id') ) {
			$params['complex_id'] = $this->safeRequestParams($this->getRequestValue('complex_id'));
		}
		if(NULL!==$this->getRequestValue('page')){
			$params['page'] = (int)$this->getRequestValue('page');
		}
		if(NULL!==$this->getRequestValue('spec')){
			$params['spec'] = $this->getRequestValue('spec');
		}
		if(NULL!==$this->getRequestValue('owner')){
			$params['owner'] = (int)$this->getRequestValue('owner');
		}
		if(NULL!==$this->getRequestValue('asc')){
			$params['asc'] = $this->getRequestValue('asc');
		}
		
		
		if(NULL!==$this->getRequestValue('user_id')){
			$params['user_id'] = $this->getRequestValue('user_id');
		}
		
		if(NULL!==$this->getRequestValue('currency_id')){
			$params['currency_id'] = (int)$this->getRequestValue('currency_id');
		}
		if(NULL!==$this->getRequestValue('price')){
			$params['price'] = (int)str_replace(' ', '', $this->getRequestValue('price'));
			$this->template->assign('price', $params['price']);
		}
		
		if(NULL!==$this->getRequestValue('price_min')){
			$params['price_min'] = (int)str_replace(' ', '', $this->getRequestValue('price_min'));
			$this->template->assign('price_min', $params['price_min']);
		}
		
		if(NULL!==$this->getRequestValue('price_pm')){
			$params['price_pm'] = (int)str_replace(' ', '', $this->getRequestValue('price_pm'));
			$this->template->assign('price_pm', $params['price_pm']);
		}
		
		if(NULL!==$this->getRequestValue('price_pm_min')){
			$params['price_pm_min'] = (int)str_replace(' ', '', $this->getRequestValue('price_pm_min'));
			$this->template->assign('price_pm_min', $params['price_pm_min']);
		}
		
		if(NULL!==$this->getRequestValue('house_number')){
			$params['house_number'] = $this->getRequestValue('house_number');
			$this->template->assign('house_number', $params['house_number']);
		}
		
		if(NULL!==$this->getRequestValue('onlyspecial')){
			$params['onlyspecial'] = $this->getRequestValue('onlyspecial');
			$this->template->assign('onlyspecial', $params['onlyspecial']);
		}
		
		if(NULL!==$this->getRequestValue('floor_min')){
			$params['floor_min'] = (int)$this->getRequestValue('floor_min');
		}
		
		if(NULL!==$this->getRequestValue('floor_max')){
			$params['floor_max'] = (int)$this->getRequestValue('floor_max');
		}
		
		if(NULL!==$this->getRequestValue('floor_count_min')){
			$params['floor_count_min'] = (int)$this->getRequestValue('floor_count_min');
		}
		
		if(NULL!==$this->getRequestValue('floor_count_max')){
			$params['floor_count_max'] = (int)$this->getRequestValue('floor_count_max');
		}
		
		if(NULL!==$this->getRequestValue('not_first_floor')){
			$params['not_first_floor'] = (int)$this->getRequestValue('not_first_floor');
		}
		
		if(NULL!==$this->getRequestValue('not_last_floor')){
			$params['not_last_floor'] = (int)$this->getRequestValue('not_last_floor');
		}
		
			
		if(NULL!==$this->getRequestValue('square_min')){
			$params['square_min'] = (int)$this->getRequestValue('square_min');
		}
		
		if(NULL!==$this->getRequestValue('square_max')){
			$params['square_max'] = (int)$this->getRequestValue('square_max');
		}
		
		if(NULL!==$this->getRequestValue('live_square_min')){
			$params['live_square_min'] = (int)$this->getRequestValue('live_square_min');
		}
		
		if(NULL!==$this->getRequestValue('kitchen_square_min')){
			$params['kitchen_square_min'] = (int)$this->getRequestValue('kitchen_square_min');
		}
		
		if(NULL!==$this->getRequestValue('kitchen_square_max')){
			$params['kitchen_square_max'] = (int)$this->getRequestValue('kitchen_square_max');
		}
		
		if(NULL!==$this->getRequestValue('live_square_max')){
			$params['live_square_max'] = (int)$this->getRequestValue('live_square_max');
		}
		
		if(NULL!==$this->getRequestValue('is_phone')){
			$params['is_phone'] = (int)$this->getRequestValue('is_phone');
		}
		
		if(NULL!==$this->getRequestValue('is_balkony')){
			$params['is_balkony'] = (int)$this->getRequestValue('is_balkony');
		}
		
		if(NULL!==$this->getRequestValue('is_sanitary')){
			$params['is_sanitary'] = (int)$this->getRequestValue('is_sanitary');
		}
		
			
		if(NULL!==$this->getRequestValue('status')){
			$params['status'] = (int)$this->getRequestValue('status');
		}
			
		
		if(NULL!==$this->getRequestValue('nout_from_sale')){
			$params['nout_from_sale'] = (int)$this->getRequestValue('nout_from_sale');
		}
		
		if(NULL!==$this->getRequestValue('nwith_null_params')){
			$params['nwith_null_params'] = (int)$this->getRequestValue('nwith_null_params');
		}
			
		if(NULL!==$this->getRequestValue('by_ipoteka')){
			$params['by_ipoteka'] = (int)$this->getRequestValue('by_ipoteka');
		}
			
		if(NULL!==$this->getRequestValue('new_only')){
			$params['new_only'] = (int)$this->getRequestValue('new_only');
		}
			
		if(NULL!==$this->getRequestValue('is_furniture')){
			$params['is_furniture'] = (int)$this->getRequestValue('is_furniture');
		}
		
		if(NULL!==$this->getRequestValue('has_photo')){
			$params['has_photo'] = (int)$this->getRequestValue('has_photo');
		}
		
		if(NULL!==$this->getRequestValue('is_internet')){
			$params['is_internet'] = (int)$this->getRequestValue('is_internet');
		}
		
		if(NULL!==$this->getRequestValue('room_count')){
			$params['room_count'] = $this->getRequestValue('room_count');
		}
		
		if(NULL!==$this->getRequestValue('optype') && $this->getRequestValue('optype')!=''){
			$params['optype'] = (int)$this->getRequestValue('optype');
		}
		
		if(NULL!==$this->getRequestValue('minbeds')){
			$params['minbeds'] = (int)$this->getRequestValue('minbeds');
		}
		
		if(NULL!==$this->getRequestValue('minbaths')){
			$params['minbaths'] = (int)$this->getRequestValue('minbaths');
		}
		
		if(NULL!==$this->getRequestValue('uniq_id')){
			$params['uniq_id'] = (int)$this->getRequestValue('uniq_id');
		}
			
		
		
		if(1==(int)$this->getRequestValue('export_afy')){
			$params['export_afy'] = 1;
		}
		if(1==(int)$this->getRequestValue('export_cian')){
			$params['export_cian'] = 1;
		}
			
		if(NULL!==$this->getRequestValue('extended_search')){
			$params['extended_search'] = $this->getRequestValue('extended_search');
		}
		if(NULL!==$this->getRequestValue('search')){
			$params['search'] = $this->getRequestValue('search');
		}
		
		
			
		if(0!=(int)$this->getRequestValue('page_limit')){
			$params['page_limit'] = (int)$this->getRequestValue('page_limit');
		}
		
		if(NULL!==$this->getRequestValue('geocoords')){
			$params['geocoords'] = preg_replace('/[^0-9.+-:]/', '', $this->getRequestValue('geocoords'));
			if($params['geocoords']==''){
				unset($params['geocoords']);
			}
		}
		
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable')==1){
			if(NULL!==$this->getRequestValue('vip_status')){
				$params['vip_status'] = (int)$this->getRequestValue('vip_status');
			}
			if(NULL!==$this->getRequestValue('premium_status')){
				$params['premium_status'] = (int)$this->getRequestValue('premium_status');
			}
			if(NULL!==$this->getRequestValue('bold_status')){
				$params['bold_status'] = (int)$this->getRequestValue('bold_status');
			}
		}
		
		/*if($REQUESTURIPATH=='find'){
			$params['pager_url']=$REQUESTURIPATH;
		}*/
		
		return $params;
	}
	
	protected function setGridViewType(){
		
		if(in_array($this->getRequestValue('grid_type'),array('thumbs', 'list'))){
			$_SESSION['grid_type']=$this->getRequestValue('grid_type');
		}else{
			if(!isset($_SESSION['grid_type'])){
				if ( $this->getConfigValue('grid_type') != '' ) {
					$_SESSION['grid_type']=$this->getConfigValue('grid_type');
				} else {
					$_SESSION['grid_type']='list';
				}
			}
		}
	}
	
	function map ( $only_data = false ) {
		$data=array();
		if($this->getConfigValue('apps.geodata.enable')!=1){
			$this->template->assign('_geo_data_hide', 1);
			return json_encode($data);
		}
		
	
		$params['id'] = $this->getRequestValue('id');
		$params['topic_id'] = $this->getRequestValue('topic_id');
		$params['order'] = $this->getRequestValue('order');
		$params['region_id'] = $this->getRequestValue('region_id');
		$params['city_id'] = $this->getRequestValue('city_id');
		$params['district_id'] = $this->getRequestValue('district_id');
		$params['metro_id'] = $this->getRequestValue('metro_id');
		$params['street_id'] = $this->getRequestValue('street_id');
		$params['page'] = $this->getRequestValue('page');
		$params['spec'] = $this->getRequestValue('spec');
		$params['owner'] = (int)$this->getRequestValue('owner');
		$params['asc'] = $this->getRequestValue('asc');
		if(NULL!=$this->getRequestValue('user_id')){
			$params['user_id'] = $this->getRequestValue('user_id');
		}
	
		$params['price'] = (int)str_replace(' ', '', $this->getRequestValue('price'));
		$this->template->assign('price', $params['price']);
	
		$params['price_min'] = (int)str_replace(' ', '', $this->getRequestValue('price_min'));
		$this->template->assign('price_min', $params['price_min']);
	
		$params['house_number'] = $this->getRequestValue('house_number');
		$this->template->assign('house_number', $params['house_number']);
	
		$params['onlyspecial'] = $this->getRequestValue('onlyspecial');
		$this->template->assign('onlyspecial', $params['onlyspecial']);
	
		$params['floor_min'] = (int)$this->getRequestValue('floor_min');
		$params['floor_max'] = (int)$this->getRequestValue('floor_max');
	
		$params['floor_count_min'] = (int)$this->getRequestValue('floor_count_min');
		$params['floor_count_max'] = (int)$this->getRequestValue('floor_count_max');
	
		$params['square_min'] = (int)$this->getRequestValue('square_min');
		$params['square_max'] = (int)$this->getRequestValue('square_max');
	
		$params['is_phone'] = (int)$this->getRequestValue('is_phone');
		$params['is_furniture'] = (int)$this->getRequestValue('is_furniture');
		$params['has_photo'] = (int)$this->getRequestValue('has_photo');
		$params['is_internet'] = (int)$this->getRequestValue('is_internet');
	
		$params['room_count'] = $this->getRequestValue('room_count');
		$params['optype'] = $this->getRequestValue('optype');
		$params['extended_search'] = $this->getRequestValue('extended_search');
		$params['search'] = $this->getRequestValue('search');
		$params['no_portions'] = 1;
		$params['no_premium_filtering'] = 1;
		$params['has_geo'] = 1;
		
	
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
		$grid_constructor = new Grid_Constructor();
		$odata=array();
		$odata=$grid_constructor->get_sitebill_adv_ext($params);
		
		global $smarty;
		
		foreach($odata as $k=>$d){
			$data[$k]['currency_name']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['currency_name']);
			$data[$k]['city']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['city']);
			$data[$k]['street']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['street']);
			if((int)$d['price']!=0){
				$gdata[$k]['price']=number_format($d['price'],0,'.',' ');
			}else{
				$gdata[$k]['price']=$d['price'];
			}
			$data[$k]['type_sh']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['type_sh']);
			$data[$k]['title']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['city'].' '.$d['street'].(($d['number']!='' && $d['number']!=0) ? ', '.$d['number'] : '').' ('.$gdata[$k]['price'].')');
			$smarty->assign('realty',$d);
			$html=$smarty->fetch('realty_on_map.tpl');
			$html = str_replace("\r\n", ' ', $html);
			$html = str_replace("\n", ' ', $html);
			$html = str_replace("\t", ' ', $html);
			$html = addslashes($html);
			$data[$k]['html']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $html);
			$data[$k]['geo_lat']=$d['geo_lat'];
			$data[$k]['geo_lng']=$d['geo_lng'];
			$data[$k]['href']=$d['href'];
			$data[$k]['parent_category_url']=$d['parent_category_url'];
			$data[$k]['id']=$d['id'];
			
		}
		if ( $only_data ) {
		    return json_encode($data);
		}
		
		$this->template->assign('_geo_data', json_encode($data));
		
		$this->template->assign('main_file_tpl', 'map.tpl');
		return true;
	}
	
	function map2 ( $only_data = false ) {
	
		global $smarty;
	
	
		$params['id'] = $this->getRequestValue('id');
		$params['topic_id'] = $this->getRequestValue('topic_id');
		$params['order'] = $this->getRequestValue('order');
		$params['region_id'] = $this->getRequestValue('region_id');
		$params['city_id'] = $this->getRequestValue('city_id');
		$params['district_id'] = $this->getRequestValue('district_id');
		$params['metro_id'] = $this->getRequestValue('metro_id');
		$params['street_id'] = $this->getRequestValue('street_id');
		$params['page'] = $this->getRequestValue('page');
		$params['spec'] = $this->getRequestValue('spec');
		$params['owner'] = (int)$this->getRequestValue('owner');
		$params['asc'] = $this->getRequestValue('asc');
		if(NULL!=$this->getRequestValue('user_id')){
			$params['user_id'] = $this->getRequestValue('user_id');
		}
	
		$params['price'] = (int)str_replace(' ', '', $this->getRequestValue('price'));
		$this->template->assign('price', $params['price']);
	
		$params['price_min'] = (int)str_replace(' ', '', $this->getRequestValue('price_min'));
		$this->template->assign('price_min', $params['price_min']);
	
		$params['house_number'] = $this->getRequestValue('house_number');
		$this->template->assign('house_number', $params['house_number']);
	
		$params['onlyspecial'] = $this->getRequestValue('onlyspecial');
		$this->template->assign('onlyspecial', $params['onlyspecial']);
	
		$params['floor_min'] = (int)$this->getRequestValue('floor_min');
		$params['floor_max'] = (int)$this->getRequestValue('floor_max');
	
		$params['floor_count_min'] = (int)$this->getRequestValue('floor_count_min');
		$params['floor_count_max'] = (int)$this->getRequestValue('floor_count_max');
	
		$params['square_min'] = (int)$this->getRequestValue('square_min');
		$params['square_max'] = (int)$this->getRequestValue('square_max');
	
		$params['is_phone'] = (int)$this->getRequestValue('is_phone');
		$params['is_furniture'] = (int)$this->getRequestValue('is_furniture');
		$params['has_photo'] = (int)$this->getRequestValue('has_photo');
		$params['is_internet'] = (int)$this->getRequestValue('is_internet');
	
		$params['room_count'] = $this->getRequestValue('room_count');
		$params['optype'] = $this->getRequestValue('optype');
		$params['extended_search'] = $this->getRequestValue('extended_search');
		$params['search'] = $this->getRequestValue('search');
		$params['no_portions'] = 1;
		$params['no_premium_filtering'] = 1;
		$params['has_geo'] = 1;
	
	
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
		$grid_constructor = new Grid_Constructor();
		$data=$grid_constructor->get_sitebill_adv_ext($params);
	
		global $smarty;
	
		foreach($data as $k=>$d){
			$data[$k]['currency_name']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['currency_name']);
			$data[$k]['city']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['city']);
			$data[$k]['street']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['street']);
			$data[$k]['price']=number_format($d['price'],0,'.',' ');
			$data[$k]['type_sh']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['type_sh']);
			$data[$k]['title']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['city'].' '.$d['street'].(($d['number']!='' && $d['number']!=0) ? ', '.$d['number'] : '').' ('.$data[$k]['price'].')');
			$smarty->assign('realty',$d);
			$html=$smarty->fetch('realty_on_map.tpl');
			$html = str_replace("\r\n", ' ', $html);
			$html = str_replace("\n", ' ', $html);
			$html = str_replace("\t", ' ', $html);
			$html = addslashes($html);
			$data[$k]['html']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $html);
				
				
		}
		if ( $only_data ) {
			return json_encode($data);
		}
	
		$this->template->assign('data', json_encode($data));
	
		return $smarty->fetch('map.tpl');
		return true;
	}
	

    /**
     * Get special grid
     * @param
     * @return
     */
    function grid_special () {
        $params['spec'] = 'spec';
        $grid_constructor=$this->_getGridConstructor();
        $grid_constructor->special($params);
    }
    
    /**
     * Get special right grid
     * @param
     * @return
     */
    function grid_special_right () {
        $params['spec'] = 'spec';
        $grid_constructor=$this->_getGridConstructor();
        $grid_constructor->special_right($params);
    }
    
    /**
     * Get sitebill adv ext
     * @param
     * @return
     */
    /*function get_sitebill_adv_ext($tid, $tid1, $tid2, $district_id, $street_id, $p_price ) {
        $where_array = false;
        $where_array[] = 're_district.id=re_data.district_id';
        $where_array[] = 're_topic.id=re_data.type_id';
        if ( $tid != '' ) {
            $where_array[] = 're_data.topic_id='.$_REQUEST['tid'];
        }
        
        if ( $_REQUEST['istreet'] != '' ) {
            $where_array[] = 're_data.street=\''.mysql_real_escape_string($_REQUEST['istreet']).'\'';
        } elseif ( $street_id != '' ) {
            $where_array[] = 're_data.street=\''.$this->getStreetNameById($street_id).'\'';
        }
        
        
        if ( $district_id != '' ) {
            $where_array[] = 're_data.district_id='.$district_id.'';
        }
        
        if ( $p_price != '' ) {
            $where_array[] = 're_data.price <= '.$p_price.'';
        }
        
        if ( $tid1 != '' ) {
            $where_array[] = 're_data.type_id='.$_REQUEST['tid1'];
        }
        $where_array[] = 're_data.active=1';
        
        if ( $where_array ) {
            $where_statement = " where ".implode(' and ', $where_array);
        }
        return $ra;
    }*/
    
    /**
     * Get update date
     * @param void
     * @return string
     */
    function getUpdateDate () {
        $rs = '<b>'.Multilanguage::_('L_MESSAGE_DB_UPDATED').': '.date('d.m.Y').'</b>';
        return $rs;
    }
    
    /**
     * Get topic title
     * @param int $topic_id topic ID
     * @return string
     */
    function getTopicTitle ( $topic_id ) {
    	$DBC=DBC::getInstance();
        $query = 'SELECT name FROM '.DB_PREFIX.'_topic WHERE id=? LIMIT 1';
        $stmt=$DBC->query($query, array($topic_id));
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	return $ar['name'];
        }
        return '';
    }
    
    /**
     * Get topic full info
     * @param int $topic_id topic ID
     * @return array
     */
    function getTopicFullInfo ( $topic_id ) {
    	$DBC=DBC::getInstance();
    	$query = 'SELECT * FROM '.DB_PREFIX.'_topic WHERE id=? LIMIT 1';
    	$stmt=$DBC->query($query, array($topic_id));
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		return $ar;
    	}
    	return array();
    }
    
    
    /**
     * Valid page
     * @param int $array_count array count
     * @param int $counter counter 
     * @param int $page page
     * @return boolean
     */
    function validPage ( $array_count, $counter, $page = 1 ) {
        //global $per_page;
        $per_page = $this->getConfigValue('per_page');
        //echo "page = $page, counter = $counter, per_page = $per_page";
        if ( $page == '' ) {
            $page = 1;
        }
        if ( ($counter >  $per_page*( $page - 1) ) and ( $counter <= $per_page*$page ) ) {
            return true;
        }
        return false;
    }
    
    /**
     * If record has photo then return true else false
     * @param int $record_id record ID
     * @return boolean
     */
    function recordHasPhoto ( $record_id ) {
        for ( $index = 0; $index <= $this->image_number; $index++ ) {
            if ($this->getPreviewImage($record_id, $index) ) {
                return true;
            }
        }
    }
    
    /*
    function get_adv_ext($p_topic_id, $p_user_id, $p_type_id, $p_district_id, $p_street, $where_add, $p_price = 0, $order = '') {
        global $topic, $topic1, $topic2, $topic_id1, $topic_id2;
        //print_r($topic2);

        if (count($topic2) > 0) {
            $where_add = $topic2[$topic_id2]['sql_where'];
        } elseif (count($topic1) > 0) {
            $where_add = $topic1[$topic_id1]['sql_where'];
        }

        if (trim($where_add) > '' ) {
            $where_add .= " and active > 0 ";
        } else {
            $where_add .= " active > 0 ";
        }
        $res = $this->get_data($p_topic_id, $p_user_id, $p_type_id, $p_district_id, $p_street, $where_add, $p_price, $order);
        return ($res);
    }
    */
    /*
    function get_array_from_query($sqlQuery) {
        $result = array();
        if ($sqlResult = mysql_query($sqlQuery) or die(mysql_error())) {
            while ($row = mysql_fetch_assoc($sqlResult)) {
                $result[$row['id']] = $row;
            }
        }
        return($result);
    }
    */
    
    function getLast($count=10){
    	if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/banner/banner.php') ) {
    		include_once (SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/banner/banner.php');
    	} else {
    		$banners=array(
    				array('src'=>'/template/frontend/albostar/img/banners3.png','href'=>'/baner3.html'),
    				array('src'=>'/template/frontend/albostar/img/banners2.png','href'=>'/baner2.html'),
    				array('src'=>'/template/frontend/albostar/img/banners1.png','href'=>'/baner1.html')
    		);
    	}
    	$DBC=DBC::getInstance();
    	$ret=array();
    	$query='SELECT MAX( i.image_id ) AS image_id, i.id FROM '.DB_PREFIX.'_data_image i, '.DB_PREFIX.'_data d WHERE (d.id = i.id AND d.active =1) GROUP BY i.id ORDER BY i.id DESC LIMIT 0 , ?';
    	$stmt=$DBC->query($query, array($count));
    	if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$data[$ar['id']]=$ar['image_id'];
			}
		}
    	
    	if(count($data)>0){
    		$dids=array_keys($data);
    		//$iids=
    		$query='SELECT d.id, d.price, t.name AS topic_name, c.name AS city_name, ds.name AS district_name FROM '.DB_PREFIX.'_data d LEFT JOIN '.DB_PREFIX.'_topic t ON d.topic_id=t.id LEFT JOIN '.DB_PREFIX.'_city c ON d.city_id=c.city_id LEFT JOIN '.DB_PREFIX.'_district ds ON d.district_id=ds.id WHERE d.id IN ('.implode(',', $dids).')';
    		$stmt=$DBC->query($query);
    		if($stmt){
    			while($ar=$DBC->fetch($stmt)){
    				$ret[$ar['id']]=$ar;
    			}
    		}
    		
    		$flip_data=array_flip($data);
    		//echo $query;
    		foreach ( $flip_data as $img_not_need => $data_id ) {
    			$query='SELECT i.image_id, i.preview FROM '.DB_PREFIX.'_image i, '.DB_PREFIX.'_data_image d WHERE d.id='.$data_id.' and d.image_id=i.image_id order by d.sort_order limit 1';
    			$stmt=$DBC->query($query);
    			$ar=$DBC->fetch($stmt);
    			
    			$ret[$data_id]['prev']=SITEBILL_MAIN_URL.'/img/data/'.$ar['preview'];
    		}
    	}
    	$countd=count($ret);
    	foreach($ret as $r){
    		$temp[]=$r;
    	}
    	unset($ret);
    	$ret=$temp;
    	unset($temp);
    	if($countd==0){
    		foreach($banners as $b){
    			$ret_b[]=$this->getBannerWrap($b);
    		}
    	}elseif($countd<3){
    		$mid=floor($countd/2);
    		$ret_b[]=$this->getBannerWrap($banners[0]);
    		
    		for($i=0;$i<$mid;$i++){
    			$ret_b[]=$this->getRealtyWrap($ret[$i]);
    		}
    		$ret_b[]=$this->getBannerWrap($banners[1]);
    		for($i=$mid;$i<$countd;$i++){
    			$ret_b[]=$this->getRealtyWrap($ret[$i]);
    		}
    		$ret_b[]=$this->getBannerWrap($banners[2]);
    	}else{
    		$mid=floor($countd/3);
    		for($i=0;$i<$mid;$i++){
    			$ret_b[]=$this->getRealtyWrap($ret[$i]);
    		}
    		$ret_b[]=$this->getBannerWrap($banners[0]);
    		for($i=$mid;$i<$mid*2;$i++){
    			$ret_b[]=$this->getRealtyWrap($ret[$i]);
    		}
    		$ret_b[]=$this->getBannerWrap($banners[1]);
    		for($i=$mid*2;$i<$countd;$i++){
    			$ret_b[]=$this->getRealtyWrap($ret[$i]);
    		}
    		$ret_b[]=$this->getBannerWrap($banners[2]);
    	}
    	
    	
    	//echo '<pre>';
    	//print_r($ret);
    	//print_r($ret_b);
    	return $ret_b;
    }
    
    function getRealtyWrap($data){
    	$ret='<div class="itm">';
    	$ret.='<a href="'.SITEBILL_MAIN_URL.'/realty'.$data['id'].'.html">';
    	$ret.='<div class="itm_img"><img src="'.$data['prev'].'" /></div>';
    	$ret.=($data['topic_name']!='' ? $data['topic_name'].'</br>' : '');
    	$ret.=($data['city_name']!='' ? Multilanguage::_('L_TEXT_CITY_1').' <b>'.$data['city_name'].'</b></br>' : '');
    	$ret.=($data['district_name']!='' ? Multilanguage::_('L_TEXT_DISTRICT').' <b>'.$data['district_name'].'</b></br>' : '');
    	$ret.='<span class="price">'.number_format($data['price'],0,',',' ').' руб.</span>';
    	$ret.='</a>';
    	$ret.='</div>';
    	return $ret;
    }
    
	function getBannerWrap($data){
    	$ret='<div class="itm"><a href="'.SITEBILL_MAIN_URL.$data['href'].'"><div class="itm_img"><img src="'.SITEBILL_MAIN_URL.$data['src'].'" /></div></a></div>';
    	return $ret;
	}
	
	function getCityListTr(){
		$city=array();
		$translite_names=array();
		if(1==$this->getConfigValue('apps.geodata.enable')){
			$query='SELECT city_id, name, translit_name, geo_lat, geo_lng FROM '.DB_PREFIX.'_city';
		}else{
			$query='SELECT city_id, name, translit_name FROM '.DB_PREFIX.'_city';
		}
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$city[]=$ar;
				$translite_names[$ar['city_id']]=$ar['translit_name'];
			}
		}
		
		if(count($city)>0){
			$query='UPDATE '.DB_PREFIX.'_city SET translit_name=? WHERE city_id=?';
			foreach($city as &$c){
				if($c['translit_name']==''){
					$_tn=$this->transliteMe($c['name']);
					if(in_array($_tn,$translite_names)){
						$_tn=$_tn.'_'.rand(10,99);
					}
					
					$stmt=$DBC->query($query, array($_tn, $c['city_id']));
					if($stmt){
						//$ar=$DBC->fetch($stmt);
						$translite_names[]=$_tn;
						$c['translit_name']=$_tn;
					}
				}
			}
		}
		return $city;
	}
	
	function getTopicListTr(){
		$DBC=DBC::getInstance();
		$topic=array();
		$translite_names=array();
		$query='SELECT id, name, translit_name FROM '.DB_PREFIX.'_topic';
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$topic[]=$ar;
				$translite_names[$ar['id']]=$ar['translit_name'];
			}
		}
		
		if(count($topic)>0){
			$query='UPDATE '.DB_PREFIX.'_topic SET translit_name=? WHERE id=?';
			foreach($topic as &$c){
				if($c['translit_name']==''){
					$_tn=$this->transliteMe($c['name']);
					if(in_array($_tn,$translite_names)){
						$_tn=$_tn.'_'.$c['id'];
					}
					$stmt=$DBC->query($query, array($_tn, $c['id']));
					if($stmt){
						$ar=$DBC->fetch($stmt);
						$translite_names[]=$_tn;
						$c['translit_name']=$_tn;
					}
				}
			}
		}
		return $topic;
	}
	
	
	function urlAnalizer(){
		$topic_id=FALSE;
		if(preg_match('/topic(\d*).html/',$_SERVER['REQUEST_URI'],$matches)){
			$topic_id=$matches[1];
		}elseif($x=$this->topicUrlFind($_SERVER['REQUEST_URI'])){
			$topic_id=$x;
		}else{
			$topic_id=FALSE;
		}
		return $topic_id;
	}
	
	function safeRequestParams($params){
		/*if(is_array($params)){
			$params=array_map(function($a){return (int)$a;},$params);
			$params=array_filter($params,function($a){return $a!=0;});
			if(count($params)==0){
				return NULL;
			}
		}else{
			$params=(int)$params;
			if($params==0){
				return NULL;
			}
		}*/
		return $params;
	}
	
	function isRealtyDetected($requesturi){
		$hard_mode=false; //decline all aliased url if they have no determined alias
		$result=false;
		$unknown_address=false;
		$realty_id=false;
	
		if(!$result && 1==$this->getConfigValue('apps.seo.data_alias_enable')){
			$url_string_parts=explode('/',$requesturi);
			if(count($url_string_parts)>0){
				$possible_alias=$url_string_parts[count($url_string_parts)-1];
		
				$possible_alias=preg_replace('/[^A-Za-z0-9_-]/','',urldecode($possible_alias));
				if($possible_alias!=''){
					$DBC=DBC::getInstance();
					$q='SELECT id, topic_id, translit_alias FROM '.DB_PREFIX.'_data WHERE translit_alias=?';
					$stmt=$DBC->query($q, array($possible_alias));
					if($stmt){
						$ar=$DBC->fetch($stmt);
						//$this->db->fetch_assoc();
						if((int)$ar['id']>0){
							if(1==$this->getConfigValue('apps.seo.level_enable') && count($url_string_parts)==1){
								require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
								$Structure_Manager = new Structure_Manager();
								$urls=$Structure_Manager->loadCategoriesUrls();
								if(isset($urls[$ar['topic_id']]) && $urls[$ar['topic_id']]!=''){
									$new_location=SITEBILL_MAIN_URL.'/'.$urls[$ar['topic_id']].'/'.$ar['translit_alias'];
									$this->go301($new_location);
									return false;
								}else{
									return false;
								}
							}elseif(1==$this->getConfigValue('apps.seo.level_enable') && count($url_string_parts)>1){
								require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
								$Structure_Manager = new Structure_Manager();
								$urls=$Structure_Manager->loadCategoriesUrls();
								array_pop($url_string_parts);
								$facturl=implode('/', $url_string_parts);
								if(!isset($urls[$ar['topic_id']]) || $urls[$ar['topic_id']]=='' || $urls[$ar['topic_id']]!=$facturl){
									return false;
								}
							}elseif(0==$this->getConfigValue('apps.seo.level_enable') && count($url_string_parts)>1){
								
								require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
								$Structure_Manager = new Structure_Manager();
								$urls=$Structure_Manager->loadCategoriesUrls();
								array_pop($url_string_parts);
								
								$facturl=implode('/', $url_string_parts);
								
								if(isset($urls[$ar['topic_id']]) && $urls[$ar['topic_id']]!='' && $urls[$ar['topic_id']]==$facturl){
									$new_location=SITEBILL_MAIN_URL.'/'.$ar['translit_alias'];
									$this->go301($new_location);
									return false;
								}else{
									return false;
								}
							}
							$realty_id=(int)$ar['id'];
							$this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
							/*require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
							require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/view/local_kvartira_view.php');
							$kvartira_view = new Local_Kvartira_View();
							*/
							//require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
							//$kvartira_view = new Kvartira_View();
							$kvartira_view=$this->_getRealtyViewer();
							if($html=$kvartira_view->main($realty_id)){
								$this->template->assert('main', $html);
								$result=true;
							}
						}
					}
				}
			}
		}
		if(trim($this->getConfigValue('apps.seo.realty_alias'))!=''){
    		$realty_alias=trim($this->getConfigValue('apps.seo.realty_alias'));
    	}else{
    		$realty_alias='realty';
    	}
	
		if ( !$result && preg_match('/'.$realty_alias.'/', $requesturi) ) {
			
			//if(preg_match('/^realty/', $requesturi) && )
			
			
			$realty_id = $this->getIDfromURI($requesturi);
			if(!$realty_id){
				return false;
			}
			
			if(1==$this->getConfigValue('apps.seo.data_alias_enable')){
				$DBC=DBC::getInstance();
				$query='SELECT topic_id, translit_alias FROM '.DB_PREFIX.'_data WHERE id=?';
				$stmt=$DBC->query($query, array($realty_id));
				if($stmt){
					$ar=$DBC->fetch($stmt);
					if($ar['translit_alias']!=''){
						if(1==$this->getConfigValue('apps.seo.level_enable')){
							require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
							$Structure=new Structure_Manager();
							$urls=$Structure->loadCategoriesUrls();
							if(isset($urls[$ar['topic_id']]) && $urls[$ar['topic_id']]!=''){
								$new_location=SITEBILL_MAIN_URL.'/'.$urls[$ar['topic_id']].'/'.$ar['translit_alias'];
								$this->go301($new_location);
								return false;
							}else{
								$new_location=SITEBILL_MAIN_URL.'/'.$ar['translit_alias'];
								$this->go301($new_location);
								return false;
							}
						}else{
							$new_location=SITEBILL_MAIN_URL.'/'.$ar['translit_alias'];
							$this->go301($new_location);
							return false;
						}
					}elseif($hard_mode && $ar['translit_alias']==''){
						return false;
					}
					
				}else{
					return false;
				}
				
			}
			
			if(1==$this->getConfigValue('apps.seo.level_enable') && preg_match('/^'.$realty_alias.'/', $requesturi)){
				
				$realty_id = $this->getIDfromURI($requesturi);
				if(!$realty_id){
					return false;
				}
				$DBC=DBC::getInstance();
				$query='SELECT topic_id FROM '.DB_PREFIX.'_data WHERE id=?';
				$stmt=$DBC->query($query, array($realty_id));
				if($stmt){
					$ar=$DBC->fetch($stmt);
					
					$topic_id=intval($ar['topic_id']);
					//echo $topic_id;
					require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
					$Structure_Manager = new Structure_Manager();
					$category_structure = $Structure_Manager->loadCategoryStructure();
						
					if($category_structure['catalog'][$topic_id]['url']!=''){
						$parent_category_url=$category_structure['catalog'][$topic_id]['url'].'/';
					}else{
						$parent_category_url='';
					}
						
					if(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
						$new_location=SITEBILL_MAIN_URL.'/'.$parent_category_url.$realty_alias.$realty_id.'.html';
					}else{
						$new_location=SITEBILL_MAIN_URL.'/'.$parent_category_url.$realty_alias.$realty_id;
					}
					$this->go301($new_location);
					return false;
				}else{
					return false;
				}
				
		
			}elseif(1==$this->getConfigValue('apps.seo.level_enable') && !preg_match('/^'.$realty_alias.'/', $requesturi)){
				
				$realty_id = $this->getIDfromURI($requesturi);
				
				if(!$realty_id){
					return false;
				}
				$DBC=DBC::getInstance();
				$query='SELECT topic_id FROM '.DB_PREFIX.'_data WHERE id=?';
				$stmt=$DBC->query($query, array($realty_id));
				if($stmt){
					$ti=$DBC->fetch($stmt);
					require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
					$Structure_Manager = new Structure_Manager();
					$urls=$Structure_Manager->loadCategoriesUrls();
					$real_turl=$urls[$ti['topic_id']];
					$comparative_url=$real_turl.'/'.$realty_alias.$realty_id;
					if(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
						$comparative_url.='.html';
					}
					//echo preg_quote($real_turl, '/');
					if(!preg_match('/^'.preg_quote($comparative_url, '/').'$/', ltrim($requesturi, '/'))){
						$new_location=SITEBILL_MAIN_URL.'/'.$real_turl.'/'.$realty_alias.$realty_id;
						if(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
							$new_location.='.html';
						}
						$this->go301($new_location);
						return false;
					}
				}
				
				/*require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/view/local_kvartira_view.php');
				$kvartira_view = new Local_Kvartira_View();
				*/
				//require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
				//$kvartira_view = new Kvartira_View();
				$kvartira_view=$this->_getRealtyViewer();
				$html=$kvartira_view->main($realty_id);
				if($html){
					$this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
					$this->template->assert('main', $html);
					$result=true;
				}
			} elseif(0==$this->getConfigValue('apps.seo.level_enable') && preg_match('/^'.$realty_alias.'/', $requesturi)){
				$realty_id = $this->getIDfromURI($_SERVER['REQUEST_URI']);
				if(!$realty_id){
					return false;
				}
				$comparative_url=$realty_alias.$realty_id;
				if(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
					$comparative_url.='.html';
				}
				if(!preg_match('/^'.preg_quote($comparative_url, '/').'$/', ltrim($requesturi, '/'))){
					$new_location=SITEBILL_MAIN_URL.'/'.$realty_alias.$realty_id;
					if(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
						$new_location.='.html';
					}
					$this->go301($new_location);
					return false;
				}
				
				/*require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/view/local_kvartira_view.php');
				$kvartira_view = new Local_Kvartira_View();
				*/
				//require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
				//$kvartira_view = new Kvartira_View();
				$kvartira_view=$this->_getRealtyViewer();
				$html=$kvartira_view->main($realty_id);
				if($html){
					$this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
					$this->template->assert('main', $html);
					$result=true;
				}
			}elseif(0==$this->getConfigValue('apps.seo.level_enable') && !preg_match('/^'.$realty_alias.'/', $requesturi)){
				$realty_id = $this->getIDfromURI($requesturi);
				
				if(!$realty_id){
					return false;
				}
				$DBC=DBC::getInstance();
				$query='SELECT topic_id FROM '.DB_PREFIX.'_data WHERE id=?';
				$stmt=$DBC->query($query, array($realty_id));
				//echo $DBC->getLastError();
				if($stmt){
					$ti=$DBC->fetch($stmt);
					//print_r($ti);
					require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
					$Structure_Manager = new Structure_Manager();
					$urls=$Structure_Manager->loadCategoriesUrls();
					$real_turl=$urls[$ti['topic_id']];
					$comparative_url=$real_turl.'/'.$realty_alias.$realty_id;
					
					/*if(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
						$comparative_url.='.html';
					}*/
					//echo preg_quote($real_turl, '/');
					if(preg_match('/^'.preg_quote($comparative_url, '/').'/', ltrim($requesturi, '/'))){
						$new_location=SITEBILL_MAIN_URL.'/'.$realty_alias.$realty_id;
						if(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
							$new_location.='.html';
						}
						$this->go301($new_location);
						return false;
					}else{
						return false;
					}
				}
				
				/*require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/view/local_kvartira_view.php');
				$kvartira_view = new Local_Kvartira_View();
				*/
				//require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
				//$kvartira_view = new Kvartira_View();
				$kvartira_view=$this->_getRealtyViewer();
				$html=$kvartira_view->main($realty_id);
				if($html){
					$this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
					$this->template->assert('main', $html);
					$result=true;
				}
			}/*else {
				$unknown_address=true;
				header("Status: 404 Not Found");
				$this->template->assign('title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
				$this->template->assign('meta_title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
				$this->template->assign('error_message', '<h1>'.Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND').'</h1>');
				$this->template->assign('main_file_tpl', 'error_message.tpl');
			}*/
		}
		
		if(!$result && 0==$this->getConfigValue('apps.seo.data_alias_enable')){
			$url_string_parts=explode('/',$requesturi);
			if(count($url_string_parts)>0){
				$possible_alias=end($url_string_parts);
			
				$possible_alias=preg_replace('/[^A-Za-z0-9_-]/','',urldecode($possible_alias));
				if($possible_alias!=''){
					$DBC=DBC::getInstance();
					$q='SELECT id, topic_id, translit_alias FROM '.DB_PREFIX.'_data WHERE translit_alias=?';
					$stmt=$DBC->query($q, array($possible_alias));
					if($stmt){
						$ar=$DBC->fetch($stmt);
						
						if((int)$ar['id']>0){
							if(1==$this->getConfigValue('apps.seo.level_enable')){
								require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
								$Structure_Manager = new Structure_Manager();
								$urls=$Structure_Manager->loadCategoriesUrls();
								if(isset($urls[$ar['topic_id']]) && $urls[$ar['topic_id']]!=''){
									$new_location=SITEBILL_MAIN_URL.'/'.$urls[$ar['topic_id']].'/realty'.$ar['id'];
								}else{
									$new_location=SITEBILL_MAIN_URL.'/'.$realty_alias.$ar['id'];
								}
								if(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
									$new_location=$new_location.'.html';
								}
								$this->go301($new_location);
								return false;
							}elseif(0==$this->getConfigValue('apps.seo.level_enable')){
								$new_location=SITEBILL_MAIN_URL.'/'.$realty_alias.$ar['id'];
								if(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
									$new_location=$new_location.'.html';
								}
								$this->go301($new_location);
								return false;
							}
						}
					}
					
				}
			}
		}
		
		return $result;
	}
	
	
}