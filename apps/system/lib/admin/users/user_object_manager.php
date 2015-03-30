<?php
/**
 * User object manager
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class User_Object_Manager extends Object_Manager {
    private $user_image_dir; 
    
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        $this->table_name = 'user';
        $this->action = 'user';
        $this->app_title = Multilanguage::_('USER_APP_NAME','system');
        $this->primary_key = 'user_id';
	    
        $this->data_model = $this->get_user_model();
        
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
        $config_manager = new config_admin();
        if ( !$config_manager->check_config_item('apps.shop.user_limit_enable') ) {
	        $config_manager->addParamToConfig('apps.shop.user_limit_enable','0','Активировать режим временных ограничений пользовательских публикаций');
        }
        
        if ( !$config_manager->check_config_item('user_pic_width') ) {
        	$config_manager->addParamToConfig('user_pic_width','160','Ширина картинки пользователя');
        }
        
        if ( !$config_manager->check_config_item('user_pic_height') ) {
        	$config_manager->addParamToConfig('user_pic_height','160','Высота картинки пользователя');
        }
        
        
    }
    
    protected function _edit_doneAction(){
    	
    	$rs='';
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	 
    	$form_data[$this->table_name]['newpass']['required'] = 'off';
			$form_data[$this->table_name]['newpass_retype']['required'] = 'off';
            $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
		    if ( !$this->check_data( $form_data[$this->table_name] ) ) {
		        $rs = $this->get_form($form_data[$this->table_name], 'edit');
		    } else {
		        $this->edit_data($form_data[$this->table_name]);
		        if ( $this->getError() ) {
		            $rs = $this->get_form($form_data[$this->table_name], 'edit');
		        } else {
		            $rs .= $this->grid();
		        }
		    }
    	return $rs;
    }
    
    function delete_data($table_name, $primary_key, $primary_key_value ) {
    	
    	$search_queries=array(
    			Multilanguage::_('TABLE_ADS','system')=>'SELECT COUNT(*) AS rs FROM '.DB_PREFIX.'_data WHERE user_id=?'
    	);
    	$ans=array();
    	$DBC=DBC::getInstance();
    	foreach($search_queries as $k=>$v){
    		$stmt=$DBC->query($v, array($primary_key_value));
    		if($stmt){
    			$ar=$DBC->fetch($stmt);
    			if($ar['rs']!=0){
    				$ans[]=sprintf(Multilanguage::_('MESSAGE_CANT_DELETE','system'), $k);
    			}
    		}
    	}
    	if(empty($ans)){
    		return parent::delete_data($table_name, $primary_key, $primary_key_value);
    	}else{
    		return $this->riseError(implode('<br />',$ans));
    	}
    }
    
    
    protected function _editAction(){
    	$rs='';
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	
    	$form_data[$this->table_name]['newpass']['required'] = 'off';
		$form_data[$this->table_name]['newpass_retype']['required'] = 'off';
        if ( $this->getRequestValue('subdo') == 'delete_image' ) {
        	$this->deleteImage($this->table_name, $this->getRequestValue('image_id'));
		}
            	
		if ( $this->getRequestValue('subdo') == 'up_image' ) {
			$this->reorderImage($this->table_name, $this->getRequestValue('image_id'), $this->primary_key, $this->getRequestValue($this->primary_key),'up');
		}
            	
		if ( $this->getRequestValue('subdo') == 'down_image' ) {
			$this->reorderImage($this->table_name, $this->getRequestValue('image_id'), $this->primary_key, $this->getRequestValue($this->primary_key), 'down');
		}
			    
		if ( $this->getRequestValue('language_id') > 0 and !$this->language->get_version($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $this->getRequestValue('language_id')) ) {
			$rs = $this->get_form($form_data[$this->table_name], 'new', $this->getRequestValue('language_id'));
		} else {
			if ( $this->getRequestValue('language_id') > 0 ) {
				$form_data[$this->table_name] = $data_model->init_model_data_from_db_language ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name], false, $this->getRequestValue('language_id') );
			} else {
				$form_data[$this->table_name] = $data_model->init_model_data_from_db ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name] );
			}
			$rs = $this->get_form($form_data[$this->table_name], 'edit');
		}
    	return $rs;
    }
    
    protected function _new_doneAction(){
    	$rs='';
    
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    
    	$form_data[$this->table_name]['newpass']['required'] = 'on';
		    $form_data[$this->table_name]['newpass_retype']['required'] = 'on';
		    
            $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
            if ( isset($form_data[$this->table_name]['reg_date']) ) {
            	$form_data[$this->table_name]['reg_date']['value'] = date('Y-m-d H:i:s');
            }
            
		    if ( !$this->check_data( $form_data[$this->table_name] ) ) {
		        $form_data[$this->table_name]['imgfile']['value'] = '';
		        $rs = $this->get_form($form_data[$this->table_name], 'new');
		        
		    } else {
		    	$new_user_id=$this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
		        if ( $this->getError() ) {
		            $form_data[$this->table_name]['imgfile']['value'] = '';
		            $rs = $this->get_form($form_data[$this->table_name], 'new');
		        } else {
		        	
		        	if(1==$this->getConfigValue('use_registration_email_confirm')){
		        		$activation_code=md5(time().'_'.rand(100,999));
		        		$this->db->exec("UPDATE ".DB_PREFIX."_user SET pass='".$activation_code."' WHERE user_id=".$new_user_id);
		        		$activation_link='<a href="http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/register?do=activate&activation_code='.$activation_code.'&email='.$form_data[$this->table_name]['email']['value'].'">http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/register?do=activate&activation_code='.$activation_code.'&email='.$form_data[$this->table_name]['email']['value'].'</a>';
		        		/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
		        		$mailer = new Mailer();*/
		        		$message = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_BODY','system'), $activation_link);
		        		$subject = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_TITLE','system'), $_SERVER['HTTP_HOST']);
		        	
		        		$to = $form_data[$this->table_name]['email']['value'];
		        		$from = $this->getConfigValue('order_email_acceptor');
		        		/*if ( $this->getConfigValue('use_smtp') ) {
		        			$mailer->send_smtp($to, $from, $subject, $message, 1);
		        		} else {
		        			$mailer->send_simple($to, $from, $subject, $message, 1);
		        		}*/
		        		$this->sendFirmMail($to, $from, $subject, $message);
		        		//save tmp password
		        		$query = "delete from ".DB_PREFIX."_cache where parameter='{$activation_code}'";
		        		$this->db->exec($query);
		        		$query = "insert into ".DB_PREFIX."_cache (parameter, `value`) values ('$activation_code', '$password')";
		        		$this->db->exec($query);
		        			
		        	}
		        	$rs .= $this->grid();
		        }
		    }
    	return $rs;
    }
    
    protected function _newAction(){
    	$rs='';
    
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	$form_data[$this->table_name]['newpass']['required'] = 'on';
	    $form_data[$this->table_name]['newpass_retype']['required'] = 'on';
	    $rs = $this->get_form($form_data[$this->table_name]);
    	return $rs;
    }
    
    
    
    
	
    
	/**
	 * Edit data
	 * @param array $form_data form data
	 * @param int $language_id language id
	 * @return boolean
	 */
	function edit_data ( $form_data, $language_id = 0 ) {
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    $query = $data_model->get_edit_query(DB_PREFIX.'_'.$this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data, $language_id);
	    
	    $this->db->exec($query);
	    if ( !$this->db->success ) {
	        $this->riseError($this->db->error);
	        return false;
	    }
	    
	    if(isset($_POST['delpic'])){
	    	$user_id=(int)$this->getRequestValue($this->primary_key);
	    	$this->deleteUserpic($user_id);
	    }
	    
	    foreach ($form_data as $form_item){
	    	if($form_item['type']=='uploads'){
	    		$imgs_uploads = $this->appendUploads($this->table_name, $form_item, $this->primary_key, (int)$this->getRequestValue($this->primary_key));
	    		$this->set_imgs($imgs_uploads);
	    
	    	}
	    }
	    
	    if ( strlen($form_data['imgfile']['value']) > 0 ) {
	        //$this->user_image_dir = $form_data['imgfile']['path']; 
	        $this->update_photo($this->getRequestValue($this->primary_key));
	    }
        
	    if ( $form_data['newpass']['value'] != '' ) {
            $this->editPassword($this->getRequestValue($this->primary_key), $form_data['newpass']['value']);
        }
	    
	}
	
	protected function deleteUserpic($user_id){
		$DBC=DBC::getInstance();
		$query='SELECT imgfile FROM '.DB_PREFIX.'_user WHERE user_id=?';
		$stmt=$DBC->query($query, array($user_id));
		if($stmt){
			$ar=$DBC->fetch($stmt);
			
			$imgfile_directory=SITEBILL_DOCUMENT_ROOT.'/img/data/user/';
			if($ar['imgfile']!='' && file_exists($imgfile_directory.$ar['imgfile'])){
				@unlink($imgfile_directory.$ar['imgfile']);
			}
			
			$query='UPDATE '.DB_PREFIX.'_user SET imgfile=\'\' WHERE user_id=?';
			$stmt=$DBC->query($query, array($user_id));
		}
	}
	
	/**
	 * Get group ID by group name
	 * @param string $group_name group name
	 * @return integer
	 */
	function getGroupIdByName ( $group_name ) {
		$query = "select group_id from ".DB_PREFIX."_group where system_name='$group_name'";
		$this->db->exec($query);
		$this->db->fetch_assoc();
		return $this->db->row['group_id'];
	}
	
	
	/**
	 * Add data
	 * @param array $form_data form data
	 * @param int $language_id
	 * @return boolean
	 */
	function add_data ( $form_data, $language_id = 0 ) {
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
	    $query = $data_model->get_insert_query(DB_PREFIX.'_'.$this->table_name, $form_data, $language_id);
	    //echo $query.'<br>';
	    $this->db->exec($query);
	    if ( !$this->db->success ) {
	        $this->riseError($this->db->error);
	        return false;
	    }
	    
	    $new_record_id = $this->db->last_insert_id();
	    
	    if ( strlen($form_data['imgfile']['value']) > 0 ) {
	        //$this->user_image_dir = $form_data['imgfile']['path']; 
	        //$this->user_image_dir='/img/data/user/';
	        $this->update_photo($new_record_id);
	    }
        
	    if ( $form_data['newpass']['value'] != '' ) {
            $this->editPassword($new_record_id, $form_data['newpass']['value']);
        }
        
        foreach ($form_data as $form_item){
        	if($form_item['type']=='uploads'){
        		$imgs_uploads = $this->appendUploads($this->table_name, $form_item, $this->primary_key, $new_record_id);
        		$this->set_imgs($imgs_uploads);
        	  
        	}
        }
	    
	    //echo "new_record_id = $new_record_id<br>";
	    //echo $query;
	    return $new_record_id;
	}
	
	/**
	 * Check data
	 * @param array $form_data
	 * @return boolean
	 */
	function check_data ( $form_data ) {
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
	    if ( !empty($form_data['login']) ) {
	    	if ( $this->getRequestValue('do') != 'edit_done' ) {
	    		if ( !$this->checkLogin($form_data['login']['value']) ) {
	    			$this->riseError('Такой login уже зарегистрирован');
	    			return false;
	    		}
	    	} else {
	    		if ( !$this->checkDiffLogin($form_data['login']['value'], $form_data['user_id']['value']) ) {
	    			$this->riseError('Такой login уже зарегистрирован');
	    			return false;
	    		}
	    	}
	    } else {
	    	if ( $this->getRequestValue('do') != 'edit_done' ) {
	    		if ( !$this->checkEmail($form_data['email']['value']) ) {
	    			$this->riseError('Такой email уже зарегистрирован');
	    			return false;
	    		}
	    	} else {
	    		if ( !$this->checkDiffEmail($form_data['email']['value'], $form_data['user_id']['value']) ) {
	    			$this->riseError('Такой email уже зарегистрирован');
	    			return false;
	    		}
	    	}
	    }
	    
	    if ( !$data_model->check_data($form_data) ) {
	        $this->riseError($data_model->GetErrorMessage());
	        return false;
	    }
	    
	    
	    if(!preg_match('/^([a-zA-Z0-9-_\.@]*)$/', $form_data['login']['value'])){
	    	$this->riseError('Логин может содержать только латинские буквы, цифры, подчеркивание, тире, амперсанд и точку');
	    	return false;
	    }
	    
	    if ( $form_data['newpass']['value'] != '' ) {
            if ( strlen($form_data['newpass']['value']) < 5 ) {
                $this->riseError(sprintf(Multilanguage::_('MIN_PASSWORD_LENGTH','system'),'5'));
                return false;
            }
            if ( $form_data['newpass']['value'] != $form_data['newpass_retype']['value'] ) {
                $this->riseError(Multilanguage::_('PASSWORDS_NOT_EQUAL','system'));
                return false;
            }
        }
	    
	    return true;
	}

	/**
     * Check login
     * @param string $login login
     * @return boolean
     */
    function checkLogin ( $login ) {
		$query = 'select count(*) as cid from '.DB_PREFIX.'_user where login=\''.$login.'\'';
		//echo $query;
		$this->db->exec($query);
		$this->db->fetch_assoc();
		if ( $this->db->row['cid'] > 0 ) {
		    return false;
		}
		return true;
    }
    
	/**
     * Check diff login not for this record id
     * @param string $login login
     * @param int $user_id
     * @return boolean
     */
    function checkDiffLogin ( $login, $user_id ) {
		$query = 'select count(*) as cid from '.DB_PREFIX.'_user where login=\''.$login.'\' and user_id<>'.$user_id;
		//echo $query;
		$this->db->exec($query);
		$this->db->fetch_assoc();
		if ( $this->db->row['cid'] > 0 ) {
		    return false;
		}
		return true;
    }
    
    
    /**
     * Check email
     * @param string $email email
     * @return boolean
     */
    function checkEmail ( $email ) {
    	$DBC=DBC::getInstance();
    	$query = 'SELECT COUNT(*) AS cid FROM '.DB_PREFIX.'_user WHERE LOWER(email)=?';
    	$stmt=$DBC->query($query, array(strtolower($email)));
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		if($ar['cid'] > 0) {
	    		return false;
	    	}
	    	return true;
    	}
    	return false;
    }
    
    /**
     * Check diff email not for this record id
     * @param string $email email
     * @param int $user_id
     * @return boolean
     */
    function checkDiffEmail ( $email, $user_id ) {
    	$DBC=DBC::getInstance();
    	$query = 'SELECT COUNT(*) AS cid FROM '.DB_PREFIX.'_user WHERE LOWER(email)=? AND user_id<>?';
    	$stmt=$DBC->query($query, array(strtolower($email), $user_id));
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		if($ar['cid'] > 0) {
    			return false;
    		}
    		return true;
    	}
    	return false;
    }
    
	
	
    /**
     * Edit password
     * @param int $user_id user id
     * @param string $password password
     * @return boolean
     */
    function editPassword ( $user_id, $password ) {
        $query = "update ".DB_PREFIX."_user set password='".md5($password)."' where user_id=$user_id";
        $this->db->exec($query);
        return true;
    }
	
	
	function update_photo ( $user_id ) {
        if ( SITEBILL_MAIN_URL != '' ) {
            $add_folder = SITEBILL_MAIN_URL.'/';
        }
        
	    
	    //global $sitebill_document_root;
	    //echo '$sitebill_document_root = '.$sitebill_document_root.'<br>';
	    //echo '$add_folder = '.$add_folder.'<br>';
        $this->user_image_dir='/img/data/user/';
	    $imgfile_directory=$this->user_image_dir;
	    
	    $document_root = $_SERVER['DOCUMENT_ROOT'].$add_folder; 
		
		$avial_ext=array('jpg', 'jpeg', 'gif', 'png');
		if(isset($_FILES['imgfile'])){
			
			if(($_FILES['imgfile']['error']!=0)OR($_FILES['imgfile']['size']==0)){
				//echo 'Не указан или указан не верно файл для загрузки<br>';
			}else{
				//$ret='No errors';
				$fprts=explode('.',$_FILES['imgfile']['name']);
				//print_r($fprts,true);
				
				if(count($fprts)>1){
					$ext=strtolower($fprts[count($fprts)-1]);
					if(in_array($ext,$avial_ext)){
						$usrfilename=time().'.'.$ext;
						//echo $imgfile_directory.$usrfilename;
                        $i = rand(0, 999);
						$preview_name="img".uniqid().'_'.time()."_".$i.".".$ext;
                        $preview_name_tmp="_tmp".uniqid().'_'.time()."_".$i.".".$ext;
						
						if(! move_uploaded_file($_FILES['imgfile']['tmp_name'], $document_root.'/'.$imgfile_directory.$preview_name_tmp) ){
							
						}else{
						    $this->deleteUserpic($user_id);
                            list($width,$height)=$this->makePreview($document_root.'/'.$imgfile_directory.$preview_name_tmp, $document_root.'/'.$imgfile_directory.$preview_name, $this->getConfigValue('user_pic_width'),$this->getConfigValue('user_pic_height'), $ext,1);
                            unlink($document_root.'/'.$imgfile_directory.$preview_name_tmp);
                            
							$query='UPDATE '.DB_PREFIX.'_user SET imgfile="'.$preview_name.'" WHERE user_id='.$user_id;
							//$ret=$query;
							$this->db->exec($query);
						}
					}
					
				}
			}
		}
		//return $ret;
	}
	
	function get_user_model ($ignore_user_group=false) {
	    $form_data = array();
	    $table_name='user';
	    if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php') ){
	    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
	    	$ATH=new Admin_Table_Helper();
	    	$form_data=$ATH->load_model($table_name, $ignore_user_group);
	    	if(empty($form_data)){
	    		$form_data = array();
	    		$form_data = $this->_get_user_model();
	    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
	    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php';
	    		$TA=new table_admin();
	    		$TA->create_table_and_columns($form_data, $table_name);
	    		$form_data = array();
	    		$form_data=$ATH->load_model($table_name, $ignore_user_group);
	    	}
	    }else{
	    	$form_data = $this->_get_user_model($ajax);
	    }
	    
	    if ( $this->getConfigValue('use_registration_email_confirm')  ) {
	    	$form_data['user']['active']['name'] = 'active';
	    	$form_data['user']['active']['title'] = 'Активен';
	    	$form_data['user']['active']['value'] = '';
	    	$form_data['user']['active']['type'] = 'checkbox';
	    }
	     
	    return $form_data;
	}
	
    
    
    /**
     * Get user model
     * @param void
     * @return array
     */
    function _get_user_model () {
        $form_user = array();
        
		$form_user['user']['user_id']['name'] = 'user_id';
		$form_user['user']['user_id']['title'] = Multilanguage::_('L_ID');
		$form_user['user']['user_id']['value'] = '';
		$form_user['user']['user_id']['length'] = 40;
		$form_user['user']['user_id']['type'] = 'primary_key';
		$form_user['user']['user_id']['required'] = 'off';
		$form_user['user']['user_id']['unique'] = 'off';

		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/group/group_manager.php') ) {
		    $form_user['user']['group_id']['name'] = 'group_id';
		    $form_user['user']['group_id']['primary_key_name'] = 'group_id';
		    $form_user['user']['group_id']['primary_key_table'] = 'group';
		    $form_user['user']['group_id']['title'] = Multilanguage::_('GROUP','system');
		    $form_user['user']['group_id']['value'] = 0;
		    $form_user['user']['group_id']['length'] = 40;
		    $form_user['user']['group_id']['type'] = 'select_by_query';
		    $form_user['user']['group_id']['query'] = 'select * from '.DB_PREFIX.'_group order by name';
		    $form_user['user']['group_id']['value_name'] = 'name';
		    $form_user['user']['group_id']['title_default'] = Multilanguage::_('L_CHOOSE_GROUP');
		    $form_user['user']['group_id']['value_default'] = 0;
		    $form_user['user']['group_id']['required'] = 'on';
		    $form_user['user']['group_id']['unique'] = 'off';
		}
		
		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/company/admin/admin.php') ) {
			if ( $this->getConfigValue('apps.company.enable') ) {
				$form_user['user']['company_id']['name'] = 'company_id';
				$form_user['user']['company_id']['primary_key_name'] = 'company_id';
				$form_user['user']['company_id']['primary_key_table'] = 'company';
				$form_user['user']['company_id']['title'] = Multilanguage::_('COMPANY','system');
				$form_user['user']['company_id']['value'] = 0;
				$form_user['user']['company_id']['length'] = 40;
				$form_user['user']['company_id']['type'] = 'select_by_query';
				$form_user['user']['company_id']['query'] = 'select * from '.DB_PREFIX.'_company order by name';
				$form_user['user']['company_id']['value_name'] = 'name';
				$form_user['user']['company_id']['title_default'] = Multilanguage::_('L_CHOOSE_COMPANY');
				$form_user['user']['company_id']['value_default'] = 0;
				$form_user['user']['company_id']['required'] = 'on';
				$form_user['user']['company_id']['unique'] = 'off';
			}
		}
		
		$form_user['user']['login']['name'] = 'login';
		$form_user['user']['login']['title'] = 'Login';
		$form_user['user']['login']['value'] = '';
		$form_user['user']['login']['length'] = 40;
		$form_user['user']['login']['type'] = 'safe_string';
		$form_user['user']['login']['required'] = 'on';
		$form_user['user']['login']['unique'] = 'off';
		
		$form_user['user']['reg_date']['name'] = 'reg_date';
		$form_user['user']['reg_date']['title'] = Multilanguage::_('REG_DATE','system');
		$form_user['user']['reg_date']['value'] = date('Y-m-d H:i:s',time());
		$form_user['user']['reg_date']['length'] = 40;
		$form_user['user']['reg_date']['type'] = 'hidden';
		$form_user['user']['reg_date']['required'] = 'off';
		$form_user['user']['reg_date']['unique'] = 'off';

		$form_user['user']['newpass']['name'] = 'newpass';
		$form_user['user']['newpass']['title'] = Multilanguage::_('NEW_PASS','system');
		$form_user['user']['newpass']['value'] = '';
		$form_user['user']['newpass']['length'] = 40;
		$form_user['user']['newpass']['type'] = 'password';
		$form_user['user']['newpass']['dbtype'] = 'notable';
		$form_user['user']['newpass']['required'] = 'off';
		$form_user['user']['newpass']['unique'] = 'off';

		$form_user['user']['newpass_retype']['name'] = 'newpass_retype';
		$form_user['user']['newpass_retype']['title'] = Multilanguage::_('RETYPE_NEW_PASS','system');
		$form_user['user']['newpass_retype']['value'] = '';
		$form_user['user']['newpass_retype']['length'] = 40;
		$form_user['user']['newpass_retype']['type'] = 'password';
		$form_user['user']['newpass_retype']['dbtype'] = 'notable';
		$form_user['user']['newpass_retype']['required'] = 'off';
		$form_user['user']['newpass_retype']['unique'] = 'off';
		
		$form_user['user']['fio']['name'] = 'fio';
		$form_user['user']['fio']['title'] = Multilanguage::_('L_FIO');
		$form_user['user']['fio']['value'] = '';
		$form_user['user']['fio']['length'] = 40;
		$form_user['user']['fio']['type'] = 'safe_string';
		$form_user['user']['fio']['required'] = 'on';
		$form_user['user']['fio']['unique'] = 'off';
		
		$form_user['user']['email']['name'] = 'email';
		$form_user['user']['email']['title'] = 'Email';
		$form_user['user']['email']['value'] = '';
		$form_user['user']['email']['length'] = 40;
		$form_user['user']['email']['type'] = 'safe_string';
		$form_user['user']['email']['required'] = 'on';
		$form_user['user']['email']['unique'] = 'on';
		
		$form_user['user']['phone']['name'] = 'phone';
		$form_user['user']['phone']['title'] = Multilanguage::_('L_PHONE');
		$form_user['user']['phone']['value'] = '';
		$form_user['user']['phone']['length'] = 40;
		$form_user['user']['phone']['type'] = 'safe_string';
		$form_user['user']['phone']['required'] = 'off';
		$form_user['user']['phone']['unique'] = 'off';
		
		$form_user['user']['mobile']['name'] = 'mobile';
		$form_user['user']['mobile']['title'] = Multilanguage::_('L_CELLPHONE');
		$form_user['user']['mobile']['value'] = '';
		$form_user['user']['mobile']['length'] = 40;
		$form_user['user']['mobile']['type'] = 'safe_string';
		$form_user['user']['mobile']['required'] = 'off';
		$form_user['user']['mobile']['unique'] = 'off';

		$form_user['user']['icq']['name'] = 'icq';
		$form_user['user']['icq']['title'] = Multilanguage::_('L_ICQNR');
		$form_user['user']['icq']['value'] = '';
		$form_user['user']['icq']['length'] = 40;
		$form_user['user']['icq']['type'] = 'safe_string';
		$form_user['user']['icq']['required'] = 'off';
		$form_user['user']['icq']['unique'] = 'off';
		
		$form_user['user']['site']['name'] = 'site';
		$form_user['user']['site']['title'] = Multilanguage::_('L_SITE');
		$form_user['user']['site']['value'] = '';
		$form_user['user']['site']['length'] = 40;
		$form_user['user']['site']['type'] = 'safe_string';
		$form_user['user']['site']['required'] = 'off';
		$form_user['user']['site']['unique'] = 'off';
		
		$form_user['user']['imgfile']['name'] = 'imgfile';
		$form_user['user']['imgfile']['title'] = Multilanguage::_('L_PHOTO');
		$form_user['user']['imgfile']['value'] = '';
		$form_user['user']['imgfile']['length'] = 40;
		$form_user['user']['imgfile']['type'] = 'photo';
		$form_user['user']['imgfile']['path'] = '/img/data/user/';
		$form_user['user']['imgfile']['required'] = 'off';
		$form_user['user']['imgfile']['unique'] = 'off';

		if ( $this->getConfigValue('user_account_enable') ) {
			$form_user['user']['account']['name'] = 'account';
			$form_user['user']['account']['title'] = Multilanguage::_('L_ACCOUNT');
			$form_user['user']['account']['value'] = 0;
			$form_user['user']['account']['length'] = 40;
			$form_user['user']['account']['type'] = 'safe_string';
			$form_user['user']['account']['required'] = 'off';
			$form_user['user']['account']['unique'] = 'off';
		}
		
		if ( $this->getConfigValue('apps.shop.user_limit_enable') ) {
		    $form_user['user']['publication_limit']['name'] = 'publication_limit';
		    $form_user['user']['publication_limit']['title'] = Multilanguage::_('PUBLICATION_TIMELIMIT','system');
		    $form_user['user']['publication_limit']['value'] = $this->getConfigValue('user_publication_limit');
		    $form_user['user']['publication_limit']['length'] = 40;
		    $form_user['user']['publication_limit']['type'] = 'safe_string';
		    $form_user['user']['publication_limit']['required'] = 'off';
		    $form_user['user']['publication_limit']['unique'] = 'off';
		}
		
		return $form_user;
    }
    /**
     * Grid
     * @param void
     * @return string
     */
    function grid () {
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);
        $common_grid->set_grid_table($this->table_name);
        
        $common_grid->add_grid_item('user_id');
        $common_grid->add_grid_item('login');
        $common_grid->add_grid_item('fio');
        $common_grid->add_grid_item('active');
        $common_grid->add_grid_item('reg_date');
        $common_grid->add_grid_item('group_id');
        $common_grid->add_grid_item('email');
        
        $common_grid->add_grid_control('edit');
        $common_grid->add_grid_control('delete');
        
        $where_parts=array();
        $group_id=(int)$this->getRequestValue('group_id');
        $company_id=(int)$this->getRequestValue('company_id');
        
        $conditions=array();
        
        if($group_id>0){
        	$where_parts[]='group_id='.$group_id;
        	$conditions['group_id']=$group_id;
        }
        
    	if($company_id>0){
        	$where_parts[]='company_id='.$company_id;
        	$conditions['company_id']=$company_id;
        }
        
        $common_grid->setPagerParams(array('action'=>$this->action,'page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('common_per_page')));
        
        if(!empty($conditions)){
        	$common_grid->set_conditions($conditions);
        }
        /*if(count($where_parts)>0){
        	$common_grid->set_grid_query("select * from ".DB_PREFIX."_".$this->table_name." where ".implode(' AND ',$where_parts)." order by user_id asc");
        	$common_grid->setPagerParams(array('page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('common_per_page'),'action'=>$this->action, 'group_id'=>$group_id, 'company_id'=>$company_id));
        }else{
        	$common_grid->set_grid_query("select * from ".DB_PREFIX."_".$this->table_name." order by user_id asc");
        	$common_grid->setPagerParams(array('page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('common_per_page'),'action'=>$this->action, 'group_id'=>'', 'company_id'=>''));
        }*/
        
        /*
        if($this->getRequestValue('district_id')!=0){
        	$common_grid->set_conditions(array('district_id'=>(int)$this->getRequestValue('district_id')));
        	$common_grid->setPagerParams(array('action'=>$this->action,'page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('common_per_page')));
        	//$common_grid->set_grid_query("SELECT * FROM ".DB_PREFIX."_".$this->table_name." WHERE district_id=".(int)$this->getRequestValue('district_id')." ORDER BY ".$this->primary_key." ASC");
        }elseif($this->getRequestValue('city_id')!=0){
        	$common_grid->set_conditions(array('city_id'=>(int)$this->getRequestValue('city_id')));
        	$common_grid->setPagerParams(array('action'=>$this->action,'page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('common_per_page')));
        	//$common_grid->set_grid_query("SELECT * FROM ".DB_PREFIX."_".$this->table_name." WHERE city_id=".(int)$this->getRequestValue('city_id')." ORDER BY ".$this->primary_key." ASC");
        }else{
        	$common_grid->setPagerParams(array('action'=>$this->action,'page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('common_per_page')));
        	//$common_grid->set_grid_query("SELECT * FROM ".DB_PREFIX."_".$this->table_name." ORDER BY ".$this->primary_key." ASC");
        }
        
        */
        
        //$common_grid->set_grid_query("select u.*, g.name AS group_id from ".DB_PREFIX."_".$this->table_name." u LEFT JOIN ".DB_PREFIX."_group g ON u.group_id=g.group_id order by user_id asc");
        $rs = $common_grid->construct_grid();
        return $rs;
    }
    
	/**
	 * Get top menu
	 * @param void 
	 * @return string
	 */
	function getTopMenu () {
	    $rs = '';
	    $rs .= '<a href="?action='.$this->action.'&do=new" class="btn btn-primary">'.Multilanguage::_('ADD_USER','system').'</a>';
	    if ( preg_match('/admin/', $_SERVER['REQUEST_URI']) ) {
	    	$rs.=$this->getAdditionalSearchForm();
	    }
	    
	    //select * from re_company order by name
	    
	    
	    //$rs .= '<a href="?action='.$this->action.'&do=new" class="btn btn-primary">Добавить пользователя</a>';
		return $rs;
	}
	
	function getAdditionalSearchForm(){
		$query='select * from re_group order by name';
		$this->db->exec($query);
		$ret.='<form method="post" action="'.SITEBILL_MAIN_URL.'/admin/index.php?action=user">';
		$ret.='<select name="group_id">';
		$ret.='<option value="">'.Multilanguage::_('ANY_GROUP','system').'</option>';
		while($this->db->fetch_assoc()){
			if($this->getRequestValue('group_id')==$this->db->row['group_id']){
				$ret.='<option value="'.$this->db->row['group_id'].'" selected="selected">'.$this->db->row['name'].'</option>';
			}else{
				$ret.='<option value="'.$this->db->row['group_id'].'">'.$this->db->row['name'].'</option>';
			}
			
		}
		$ret.='</select>';
		if($this->getConfigValue('apps.company.enable')==1){
			$query='select * from re_company order by name';
			$this->db->exec($query);
			$ret.='<select name="company_id">';
			$ret.='<option value="">'.Multilanguage::_('ANY_COMPANY','system').'</option>';
			while($this->db->fetch_assoc()){
				if($this->getRequestValue('company_id')==$this->db->row['company_id']){
					$ret.='<option value="'.$this->db->row['company_id'].'" selected="selected">'.$this->db->row['name'].'</option>';
				}else{
					$ret.='<option value="'.$this->db->row['company_id'].'">'.$this->db->row['name'].'</option>';
				}
				
			}
			$ret.='</select>';
		}
		$ret.='<input type="hidden" name="action" value="'.$this->action.'">';
		$ret .= '<input type="submit" name="submit" value="'.Multilanguage::_('L_TEXT_SELECT').'">';
		$ret.='</form>';
		return $ret;
	}
	
	
}
?>
