<?php
class Common_Logger {
	/*protected function authUser($_login, $_pass, $name, $email){
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
		$login_object = new Login();
		$Config = new config_admin();
		if(1==intval($Config->getConfigValue('email_as_login'))){
			$l=$email;
		}else{
			$l=$_login;
		}
		
		if(!$login_object->checkLogin($l, $_pass)){
			$id=$login_object->direct_add_user($_login, md5($_pass), $name, $email);
			//activate account
			$DBC=DBC::getInstance();
			$query='UPDATE '.DB_PREFIX.'_user SET active=1, group_id=? WHERE user_id=?';
			$stmt=$DBC->query($query, array($Config->getConfigValue('apps.socialauth.default_group_id'), $id));
			
				
			if(1==$login_object->getConfigValue('notify_admin_about_register')){
				$message = 'На сайте зарегистрирован новый пользователь '.$_login;
				$subject = 'Новый пользователь '.$_login.' на сайте '.$_SERVER['HTTP_HOST'];
					
				$to = $login_object->getConfigValue('order_email_acceptor');
				$from = $login_object->getConfigValue('order_email_acceptor');
				$login_object->sendFirmMail($to, $from, $subject, $message);
			}
				
			$login_object->checkLogin($l, $_pass);
			return true;
		}
	}*/
	
}