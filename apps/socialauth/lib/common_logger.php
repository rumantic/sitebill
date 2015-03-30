<?php
class Common_Logger {
	protected function authUser($_login, $_pass, $name, $email){
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
		$login_object = new Login();
		$Config = new config_admin();
		if(!$login_object->checkLogin($_login, $_pass)){
			$login_object->direct_add_user($_login, md5($_pass), $name, $email);
			//activate account
			$DBC=DBC::getInstance();
			$query='UPDATE '.DB_PREFIX.'_user SET active=1, group_id=? WHERE login=?';
			$stmt=$DBC->query($query, array($Config->getConfigValue('apps.socialauth.default_group_id'), $_login));
			
				
			if(1==$login_object->getConfigValue('notify_admin_about_register')){
				/*
				require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
				$mailer = new Mailer();
				*/
				
				$message = 'На сайте зарегистрирован новый пользователь '.$_login;
				$subject = 'Новый пользователь '.$_login.' на сайте '.$_SERVER['HTTP_HOST'];
					
				$to = $login_object->getConfigValue('order_email_acceptor');
				$from = $login_object->getConfigValue('order_email_acceptor');
				/*
				if ( $login_object->getConfigValue('use_smtp') ) {
					$mailer->send_smtp($to, $from, $subject, $message, 1);
				} else {
					$mailer->send_simple($to, $from, $subject, $message, 1);
				}
				*/
				$login_object->sendFirmMail($to, $from, $subject, $message);
			}
				
			$login_object->checkLogin($_login, $_pass);
			return true;
		}
	}
}