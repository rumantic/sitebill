<?php
/**
 * Register using model
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class Register_Using_Model extends User_Object_Manager {
	
	function main () {
		$do=$this->getRequestValue('do');
		$action='_'.$do.'Action';
		if(!method_exists($this, $action)){
			$action='_defaultAction';
		}
		$rs .= $this->$action();
		return $rs;
	}
	
	protected function _activateAction(){
		$rs='';
		$activation_code=$this->getRequestValue('activation_code');
		$email=$this->getRequestValue('email');
		//$q="SELECT COUNT(*) AS cnt FROM ".DB_PREFIX."_user WHERE email=? AND pass=? AND active=0";
		$q="SELECT active AS cnt FROM ".DB_PREFIX."_user WHERE email=? AND pass=? LIMIT 1";
		
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($q, array($email, $activation_code));
		
		if(!$stmt){
			$rs=Multilanguage::_('ACTIVATION_ERROR','system');
		}else{
			$ar=$DBC->fetch($stmt);
			if((int)$ar['cnt']==0){
				$q="UPDATE ".DB_PREFIX."_user SET active=1 WHERE email=? AND pass=?";
				$stmt=$DBC->query($q, array($email, $activation_code));
					
					
				$rs=Multilanguage::_('ACCOUNT_ACTIVATED','system');
					
					
			
				if(1==$this->getConfigValue('registration_notice')){
					/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
					 $mailer = new Mailer();*/
			
					if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/mails/'.Multilanguage::get_current_language().'/register_email_notify_complete.tpl')){
						$tpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/mails/'.Multilanguage::get_current_language().'/register_email_notify_complete.tpl';
					}elseif(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/template/register_email_notify_complete.tpl.html')){
						$tpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/template/register_email_notify_complete.tpl.html';
					}elseif(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/system/template/mails/'.Multilanguage::get_current_language().'/register_email_notify_complete.tpl')){
						$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/system/template/mails/'.Multilanguage::get_current_language().'/register_email_notify_complete.tpl';
					}else{
						$tpl='';
					}
			
					if($tpl==''){
						$message=Multilanguage::_('NEW_REGISTER_BODY_TRIMMED','system');
					}else{
						global $smarty;
			
						$q="SELECT * from ".DB_PREFIX."_user WHERE email=? LIMIT 1";
						$stmt=$DBC->query($q, array($email));
						$ar=$DBC->fetch($stmt);
							
						$user_info = $ar;
						$query = "select * from ".DB_PREFIX."_cache where parameter=?";
						$stmt=$DBC->query($query, array($activation_code));
						$ar=$DBC->fetch($stmt);
						$password = $ar['value'];
						$query = "delete from ".DB_PREFIX."_cache where parameter=?";
						$stmt=$DBC->query($query, array($activation_code));
							
						$smarty->assign('user_name', $user_info['fio']);
						$smarty->assign('site_url', 'http://'.$_SERVER['HTTP_HOST']);
						$smarty->assign('login', $user_info['login']);
						$smarty->assign('password', $password);
						$smarty->assign('email_signature', $this->getConfigValue('email_signature'));
						$message=$smarty->fetch($tpl);
					}
			
			
			
			
					$subject = sprintf(Multilanguage::_('NEW_REGISTER_TITLE','system'), $_SERVER['HTTP_HOST']);
						
					$to = $this->getRequestValue('email');
					$from = $this->getConfigValue('order_email_acceptor');
					/*if ( $this->getConfigValue('use_smtp') ) {
					 $mailer->send_smtp($to, $from, $subject, $message, 1);
					} else {
					$mailer->send_simple($to, $from, $subject, $message, 1);
					}*/
					$this->sendFirmMail($to, $from, $subject, $message);
				}
					
					
			
			}else{
				header('location: '.SITEBILL_MAIN_URL.'/');
				exit();
				$rs=Multilanguage::_('ACTIVATION_ERROR','system');
			}
		}
		
		return $rs;
	}
	
	protected function postPreparedOperations($form_data){
		return $form_data;
	}
	
	protected function _new_doneAction(){
		$rs='';
	
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$form_data = $this->data_model;
	
		$form_data[$this->table_name]['newpass']['required'] = 'on';
		$form_data[$this->table_name]['newpass_retype']['required'] = 'on';
		unset($form_data[$this->table_name]['active']);
		
		$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
		
		if(!isset($form_data[$this->table_name]['group_id'])){
			if(0!=(int)$this->getConfigValue('newuser_registration_groupid')){
				$form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
			}else{
				$form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
			}
		}else{
			if(''!=$this->getConfigValue('newuser_registration_shared_groupid')){
				$groups=array();
				$shared_groups=$this->getConfigValue('newuser_registration_shared_groupid');
				$shared_groups=preg_replace('/[^\d,]/', '', $shared_groups);
				$groups=explode(',', $shared_groups);
				
				if(!in_array($form_data[$this->table_name]['group_id']['value'], $groups)){
					if(0!=(int)$this->getConfigValue('newuser_registration_groupid')){
						$form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
					}else{
						$form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
					}
				}
			}else{
				if(0!=(int)$this->getConfigValue('newuser_registration_groupid')){
					$form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
				}else{
					$form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
				}
			}
		}
		
		
		
		if ( isset($form_data[$this->table_name]['reg_date']) ) {
			$form_data[$this->table_name]['reg_date']['value'] = date('Y-m-d H:i:s');
		}
		
		$form_data[$this->table_name]=$this->postPreparedOperations($form_data[$this->table_name]);
		 
		if ( !$this->check_data( $form_data[$this->table_name] ) ) {
			$form_data[$this->table_name]['imgfile']['value'] = '';
			$rs = $this->get_form($form_data[$this->table_name], 'new', 0, '', SITEBILL_MAIN_URL.'/register/');
			 
		} else {
			$new_user_id = $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
			
			if ( $this->getError() ) {
				$form_data[$this->table_name]['imgfile']['value'] = '';
				$rs = $this->get_form($form_data[$this->table_name], 'new', 0, '', SITEBILL_MAIN_URL.'/register/');
			} else {
				$email = $form_data[$this->table_name]['email']['value'];
				$login = $form_data[$this->table_name]['login']['value'];
				$password = $form_data[$this->table_name]['newpass']['value'];
				
				if(1==$this->getConfigValue('use_registration_email_confirm')){
					$DBC=DBC::getInstance();
					$activation_code=md5(time().'_'.rand(100,999));
					$query='UPDATE '.DB_PREFIX.'_user SET pass=? WHERE user_id=?';
					$stmt=$DBC->query($query, array($activation_code, $new_user_id));
					//$this->db->exec("UPDATE ".DB_PREFIX."_user SET pass='".$activation_code."' WHERE user_id=".$new_user_id);
					$activation_link='<a href="http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/register?do=activate&activation_code='.$activation_code.'&email='.$email.'">http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/register?do=activate&activation_code='.$activation_code.'&email='.$email.'</a>';
					/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
					$mailer = new Mailer();*/
					$message = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_BODY','system'), $activation_link);
					$subject = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_TITLE','system'), $_SERVER['HTTP_HOST']);
					 
					$to = $email;
					$from = $this->getConfigValue('order_email_acceptor');
					
					$this->sendFirmMail($to, $from, $subject, $message);
					/*
					if ( $this->getConfigValue('use_smtp') ) {
						$mailer->send_smtp($to, $from, $subject, $message, 1);
					} else {
						$mailer->send_simple($to, $from, $subject, $message, 1);
					}*/
					
					//save tmp password
					$query = "delete from ".DB_PREFIX."_cache where parameter=?";
					$stmt=$DBC->query($query, array($activation_code));
					//$this->db->exec($query);
					$query = "insert into ".DB_PREFIX."_cache (`parameter`, `value`) values (?, ?)";
					$stmt=$DBC->query($query, array($activation_code, $password));
					//$this->db->exec($query);
					
					$rs = '<h3>'.Multilanguage::_('REGISTER_SUCCESS','system').'</h3><br>';
					if($form_data[$this->table_name]['active']['value']!=1){
						$rs.=Multilanguage::_('ACTIVATION_CODE_SENT','system');
					}
					return $rs;
				}
				
				if(1==$this->getConfigValue('registration_notice')){
					/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
					$mailer = new Mailer();*/
					$message = sprintf(Multilanguage::_('NEW_REGISTER_BODY','system'), $login, $password);
					$subject = sprintf(Multilanguage::_('NEW_REGISTER_TITLE','system'), $_SERVER['HTTP_HOST']);
					 
					$to = $email;
					$from = $this->getConfigValue('order_email_acceptor');
					$this->sendFirmMail($to, $from, $subject, $message);
					/*if ( $this->getConfigValue('use_smtp') ) {
						$mailer->send_smtp($to, $from, $subject, $message, 1);
					} else {
						$mailer->send_simple($to, $from, $subject, $message, 1);
					}*/
				}
				
				if(1==$this->getConfigValue('notify_admin_about_register')){
					$DBC=DBC::getInstance();
					/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
					$mailer = new Mailer();*/
					$q="SELECT * from ".DB_PREFIX."_user WHERE user_id=? LIMIT 1";
					$stmt=$DBC->query($q, array($new_user_id));
					$user_info=$DBC->fetch($stmt);
						
					$message = sprintf(Multilanguage::_('NEW_REGISTER_NEW_USER','system'), $user_info['login']);
					$subject = sprintf(Multilanguage::_('NEW_REGISTER_TITLE','system'), $_SERVER['HTTP_HOST']);
						
					$to = $this->getConfigValue('order_email_acceptor');
					$from = $this->getConfigValue('order_email_acceptor');
					$this->sendFirmMail($to, $from, $subject, $message);
					/*if ( $this->getConfigValue('use_smtp') ) {
						$mailer->send_smtp($to, $from, $subject, $message, 1);
					} else {
						$mailer->send_simple($to, $from, $subject, $message, 1);
					}*/
				}
				
				$rs=$this->postRegisterAction($form_data);
				
			}
		}
		return $rs;
	}
	
	protected function postRegisterAction($form_data){
		return $this->welcome();
	}
	
	protected function _defaultAction(){
		$rs='';
		
		
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$form_data = $this->data_model;
		$form_data[$this->table_name]['newpass']['required'] = 'on';
		$form_data[$this->table_name]['newpass_retype']['required'] = 'on';
		unset($form_data[$this->table_name]['active']);
		
		if(isset($form_data[$this->table_name]['group_id'])){
			$shared_groups=$this->getConfigValue('newuser_registration_shared_groupid');
			$shared_groups=preg_replace('/[^\d,]/', '', $shared_groups);
			if($shared_groups!=''){
				$form_data[$this->table_name]['group_id']['query']='SELECT group_id, name FROM '.DB_PREFIX.'_group WHERE group_id IN ('.$shared_groups.')';
			}else{
				$form_data[$this->table_name]['group_id']['query']='SELECT group_id, name FROM '.DB_PREFIX.'_group WHERE group_id=0';
			}
			
		}
		
		$rs = $this->get_form($form_data[$this->table_name], 'new', 0, '', SITEBILL_MAIN_URL.'/register/');
		return $rs;
	}
	
	
	
	public function ajaxRegister(){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$form_data = $this->data_model;
		$form_data[$this->table_name]['newpass']['required'] = 'on';
		$form_data[$this->table_name]['newpass_retype']['required'] = 'on';
		unset($form_data[$this->table_name]['active']);
		
		$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
		
		if(!isset($form_data[$this->table_name]['group_id'])){
			if(0!=(int)$this->getConfigValue('newuser_registration_groupid')){
				$form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
			}else{
				$form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
			}
		}else{
			if(''!=$this->getConfigValue('newuser_registration_shared_groupid')){
				$groups=array();
				$shared_groups=$this->getConfigValue('newuser_registration_shared_groupid');
				$shared_groups=preg_replace('/[^\d,]/', '', $shared_groups);
				$groups=explode(',', $shared_groups);
				
				if(!in_array($form_data[$this->table_name]['group_id']['value'], $groups)){
					if(0!=(int)$this->getConfigValue('newuser_registration_groupid')){
						$form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
					}else{
						$form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
					}
				}
			}else{
				if(0!=(int)$this->getConfigValue('newuser_registration_groupid')){
					$form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
				}else{
					$form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
				}
			}
		}
		
		
/*
		if(0!=(int)$this->getConfigValue('newuser_registration_groupid')){
			$form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
		}else{
			$form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
		}
*/
		
		if ( isset($form_data[$this->table_name]['reg_date']) ) {
			$form_data[$this->table_name]['reg_date']['value'] = date('Y-m-d H:i:s');
		}
		
		foreach ($form_data[$this->table_name] as $it=>$va){
			$form_data[$this->table_name][$it]['value']=SiteBill::iconv('utf-8', SITE_ENCODING, $va['value']);
		}
		
		$form_data[$this->table_name]=$this->postPreparedOperations($form_data[$this->table_name]);
			
		if ( !$this->check_data( $form_data[$this->table_name] ) ) {
			$form_data[$this->table_name]['imgfile']['value'] = '';
			//$rs = $this->get_form($form_data[$this->table_name], 'new');
			return $this->getError();
		
		} else {
			$new_user_id = $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
			if ( $this->getError() ) {
				$form_data[$this->table_name]['imgfile']['value'] = '';
				return $this->getError();
				$rs = $this->get_form($form_data[$this->table_name], 'new');
			} else {
				$email = $form_data[$this->table_name]['email']['value'];
				$login = $form_data[$this->table_name]['login']['value'];
				$password = $form_data[$this->table_name]['newpass']['value'];
		
				if(1==$this->getConfigValue('use_registration_email_confirm')){
					$activation_code=md5(time().'_'.rand(100,999));
					$this->db->exec("UPDATE ".DB_PREFIX."_user SET pass='".$activation_code."' WHERE user_id=".$new_user_id);
					$activation_link='<a href="http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/register?do=activate&activation_code='.$activation_code.'&email='.$email.'">http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/register?do=activate&activation_code='.$activation_code.'&email='.$email.'</a>';
					/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
					$mailer = new Mailer();*/
					$message = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_BODY','system'), $activation_link);
					$subject = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_TITLE','system'), $_SERVER['HTTP_HOST']);
		
					$to = $email;
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
						
					$rs = '<h3>'.Multilanguage::_('REGISTER_SUCCESS','system').'</h3><br>';
					$rs.=Multilanguage::_('ACTIVATION_CODE_SENT','system');
					return $rs;
				}
		
				if(1==$this->getConfigValue('registration_notice')){
					/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
					$mailer = new Mailer();*/
					$message = sprintf(Multilanguage::_('NEW_REGISTER_BODY','system'), $login, $password);
					$subject = sprintf(Multilanguage::_('NEW_REGISTER_TITLE','system'), $_SERVER['HTTP_HOST']);
		
					$to = $email;
					$from = $this->getConfigValue('order_email_acceptor');
					/*if ( $this->getConfigValue('use_smtp') ) {
						$mailer->send_smtp($to, $from, $subject, $message, 1);
					} else {
						$mailer->send_simple($to, $from, $subject, $message, 1);
					}*/
					$this->sendFirmMail($to, $from, $subject, $message);
				}
				
				if(1==$this->getConfigValue('notify_admin_about_register')){
					$DBC=DBC::getInstance();
					/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
					$mailer = new Mailer();*/
					$q="SELECT * from ".DB_PREFIX."_user WHERE user_id=? LIMIT 1";
					$stmt=$DBC->query($q, array($new_user_id));
					$user_info=$DBC->fetch($stmt);
				
					$message = sprintf(Multilanguage::_('NEW_REGISTER_NEW_USER','system'), $user_info['login']);
					$subject = sprintf(Multilanguage::_('NEW_REGISTER_TITLE','system'), $_SERVER['HTTP_HOST']);
				
					$to = $this->getConfigValue('order_email_acceptor');
					$from = $this->getConfigValue('order_email_acceptor');
					/*if ( $this->getConfigValue('use_smtp') ) {
						$mailer->send_smtp($to, $from, $subject, $message, 1);
					} else {
						$mailer->send_simple($to, $from, $subject, $message, 1);
					}*/
					$this->sendFirmMail($to, $from, $subject, $message);
				}
				return 'ok';
				$rs = $this->welcome();
			}
		}
	}
	
	public function getRegisterFormElements(){
		$form_data = $this->data_model;
		$form_data[$this->table_name]['newpass']['required'] = 'on';
		$form_data[$this->table_name]['newpass_retype']['required'] = 'on';
		unset($form_data[$this->table_name]['active']);
		//unset($form_data[$this->table_name]['group_id']);
		
		$reg_form_elements=array();
		foreach($form_data[$this->table_name] as $fden=>$fdev){
			if($fdev['required']=='on'){
				$reg_form_elements[$fden]=$fdev;
			}
		}
		if(isset($reg_form_elements['group_id'])){
			if($this->getConfigValue('newuser_registration_shared_groupid')!=""){
				$shared_groups=$this->getConfigValue('newuser_registration_shared_groupid');
				$shared_groups=preg_replace('/[^\d,]/', '', $shared_groups);
				//var_dump($shared_groups);
				if($shared_groups!=''){
					$reg_form_elements['group_id']['query']='SELECT * FROM '.DB_PREFIX.'_group WHERE group_id IN ('.$shared_groups.')';
				}else{
					$reg_form_elements['group_id']['query']='SELECT * FROM '.DB_PREFIX.'_group WHERE group_id=0';
				}
			}
			else
			{
				unset($reg_form_elements['group_id']);
			}
		}
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
		$el = $form_generator->compile_form_elements($reg_form_elements,true);
		return $el['public'][$this->getConfigValue('default_tab_name')];
	}
	
	/**
	 * Check data
	 * @param array $form_data
	 * @return boolean
	 */
	function check_data ( $form_data ) {
		
		
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		 
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
		
		if(!preg_match('/^([a-zA-Z0-9-_@\.]*)$/', $form_data['login']['value'])){
			$this->riseError('Логин может содержать только латинские буквы, цифры, подчеркивание, тире');
			return false;
		}
		
		if(preg_match('/^(vk|tw|gl|fb|ok)([0-9]*)$/', $form_data['login']['value'])){
			$this->riseError('Логин уже занят');
			return false;
		}
		
		if ( !$this->checkLogin($form_data['login']['value']) ) {
			$this->riseError('Логин уже занят');
			return false;
		}
		 
		if ( !$data_model->check_data($form_data) ) {
			$this->riseError($data_model->GetErrorMessage());
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
	
	function welcome() {
		$rs = '<h3>'.Multilanguage::_('REGISTER_SUCCESS','system').'</h3><br>';
		$rs .= '<a href="'.SITEBILL_MAIN_URL.'/account/">'.Multilanguage::_('PRIVATE_ACCOUNT','system').'</a>';
		return $rs;
	}

	public function getUniqLogin($login){
		if ( !$this->checkLogin($login) ) {
			$DBC=DBC::getInstance();
			$query='SELECT login FROM '.DB_PREFIX.'_user WHERE login LIKE \''.$login.'%\'';
			
			$stmt=$DBC->query($query);
			$used_logins=array();
			$used_numbers=array();
			if($stmt){
				while($ar=$DBC->fetch($stmt)){
					$used_logins[]=$ar['login'];
				}
			}
			
			foreach($used_logins as $used_login){
				if(preg_match('/^'.$login.'(\d+)$/', $used_login, $matches)){
					$used_numbers[]=(int)$matches[1];
				}
			}
			if(empty($used_numbers)){
				$login=$login.'1';
			}else{
				
				rsort($used_numbers);
				$login=$login.($used_numbers[0]+1);
			}
			
			
		}
		return $login;
	}
	
}