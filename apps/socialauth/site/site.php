<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * live search fronend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */




class socialauth_site extends socialauth_admin {
	function frontend () {
		$REQUESTURIPATH=$this->getClearRequestURI();
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/lib/common_logger.php';
		
		if($REQUESTURIPATH=='socialauth'){
			return false;
		}elseif($REQUESTURIPATH=='socialauth/register'){
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/register_using_model.php');
			$Register = new Register_Using_Model();
			$Register->register_social=true;
			/*if(strtolower($_SERVER['REQUEST_METHOD'])=='get'){
				$this->setRequestValue('login', $_SESSION['ssAuthData']['_login']);
				$this->setRequestValue('email', $_SESSION['ssAuthData']['email']);
				$this->setRequestValue('fio', $_SESSION['ssAuthData']['name']);
				$this->setRequestValue('group_id', $this->getConfigValue('apps.socialauth.default_group_id'));
				$this->setRequestValue('newpass', $_SESSION['ssAuthData']['_pass']);
				$this->setRequestValue('newpass_retype', $_SESSION['ssAuthData']['_pass']);
			}*/
			
			$rs1 = $Register->main();
			$this->template->assert('register_block', $rs1);
			$this->set_apps_template('socialauth', $this->getConfigValue('theme'), 'main_file_tpl', 'register.tpl');
			return true;
		}elseif($REQUESTURIPATH=='socialauth/login/google'){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/lib/gl/gl_logger.php';
			$AUTH=Gl_Logger::getInstance();
			$r=$AUTH->prelogin();
			return true;
		}elseif($REQUESTURIPATH=='socialauth/login/facebook'){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/lib/fb/fb_logger.php';
			$AUTH=Fb_Logger::getInstance();
			$r=$AUTH->prelogin();
			return true;
		}elseif($REQUESTURIPATH=='socialauth/login/odnoklassniki'){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/lib/ok/ok_logger.php';
			$AUTH=Ok_Logger::getInstance();
			$r=$AUTH->prelogin();
			return true;
		}elseif($REQUESTURIPATH=='socialauth/login/vkontakte'){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/lib/vk/vk_logger.php';
			$AUTH=Vk_Logger::getInstance();
			$r=$AUTH->prelogin();
			return true;
		}elseif($REQUESTURIPATH=='socialauth/login/twitter'){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/lib/tw/tw_logger.php';
			$AUTH=Tw_Logger::getInstance();
			$r=$AUTH->prelogin();
			return true;
		}elseif($REQUESTURIPATH=='socialauth/login'){
			
			$do=$_GET['do'];
			switch($do){
				case 'login_ok' : {
					if($this->getConfigValue('apps.socialauth.ok.enable')){
						require_once SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/lib/ok/ok_logger.php';
						$OL=Ok_Logger::getInstance();
						$r=$OL->login();
						if($r){
							if($id=$this->checkExistingUser($_SESSION['ssAuthData']['ssType'], $_SESSION['ssAuthData']['id'])){
								require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
								$login_object = new Login();
								$login_object->setLoggedUser($id);
								unset($_SESSION['ssAuthData']);
							}else{
								header('location: '.SITEBILL_MAIN_URL.'/socialauth/register/?do=new_done');
								exit();
							}
						}
					}
					break;
				}
				case 'login_vk' : {
					if($this->getConfigValue('apps.socialauth.vk.enable')){
						require_once SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/lib/vk/vk_logger.php';
						$VK=Vk_Logger::getInstance();
						$r=$VK->login();
						if($r){
							if($id=$this->checkExistingUser($_SESSION['ssAuthData']['ssType'], $_SESSION['ssAuthData']['id'])){
								require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
								$login_object = new Login();
								$login_object->setLoggedUser($id);
						
							}/*elseif($id=$this->addSocialUser($_SESSION['ssAuthData'])){
								require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
								$login_object = new Login();
								$login_object->setLoggedUser($id);
							}*/else{
							
								header('location: '.SITEBILL_MAIN_URL.'/socialauth/register/?do=new_done');
								exit();
							}/*else{
							$this->direct_add_user($_SESSION['ssAuthData']);
							}*/
							//print_r($_SESSION['ssAuthData']);
						}
					}
					break;
				}
				case 'login_fb' : {
					if($this->getConfigValue('apps.socialauth.fb.enable')){
						require_once SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/lib/fb/fb_logger.php';
						$FB=Fb_Logger::getInstance();
						$r=$FB->login();
						if($r){
							if($id=$this->checkExistingUser($_SESSION['ssAuthData']['ssType'], $_SESSION['ssAuthData']['id'])){
								require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
								$login_object = new Login();
								$login_object->setLoggedUser($id);
							}else{
								header('location: '.SITEBILL_MAIN_URL.'/socialauth/register/?do=new_done');
								exit();
							}
						}
					}
					break;
				}
				case 'login_tw' : {
					if($this->getConfigValue('apps.socialauth.tw.enable')){
						require_once SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/lib/tw/tw_logger.php';
						$TW=Tw_Logger::getInstance();
						$r=$TW->login();
						if($r){
							if($id=$this->checkExistingUser($_SESSION['ssAuthData']['ssType'], $_SESSION['ssAuthData']['id'])){
								require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
								$login_object = new Login();
								$login_object->setLoggedUser($id);
							}else{
								header('location: '.SITEBILL_MAIN_URL.'/socialauth/register/?do=new_done');
								exit();
							}
						}
					}
					break;
				}
				case 'login_gl' : {
					if($this->getConfigValue('apps.socialauth.gl.enable')){
						require_once SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/lib/gl/gl_logger.php';
						$GL=Gl_Logger::getInstance();
						$r=$GL->login();
						if($r){
							if($id=$this->checkExistingUser($_SESSION['ssAuthData']['ssType'], $_SESSION['ssAuthData']['id'])){
								require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
								$login_object = new Login();
								$login_object->setLoggedUser($id);
							}else{
								header('location: '.SITEBILL_MAIN_URL.'/socialauth/register/?do=new_done');
								exit();
							}/*else{
								$this->direct_add_user($_SESSION['ssAuthData']);
							}*/
							//print_r($_SESSION['ssAuthData']);
						}
					}
					break;
				}
			}
			if($r){
				
				if(isset($_COOKIE['back_url']) && $_COOKIE['back_url']!=''){
					$backUrl=$_COOKIE['back_url'];
				}else{
					$backUrl='http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/';
				}
				header('location: '.$backUrl);
				exit();
			}
			return true;
		}
		return false;
    }
    
    /*protected function addSocialUser($data){
    	$pass=substr(md5(rand(1000,9999).time()), 0, 8);
    	$md5pass=md5($pass);
    	$query = 'INSERT INTO '.DB_PREFIX.'_user (login, password, fio, email, reg_date, `'.$data['ssType'].'_id`) VALUES (?, ?, ?, ?, ?, ?)';
    	$DBC=DBC::getInstance();
    	if(1==intval($this->getConfigValue('email_as_login'))){
    		$login=$email;
    	}
    	$stmt=$DBC->query($query, array($data['_login'], $md5pass, $data['name'], $data['email'], date('Y-m-d H:i:s'), $data['id']));
    	if ( $stmt ) {
    		return $DBC->lastInsertId();
    	}
    	return false;
    }*/
    
    protected function checkExistingUser($ssType, $ssId){
    	$DBC=DBC::getInstance();
    	$query='SELECT * FROM '.DB_PREFIX.'_user WHERE `'.$ssType.'_id`=?';
    	$stmt=$DBC->query($query, array($ssId));
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		return $ar['user_id'];
    	}/*else{
    		$data=$_SESSION['ssAuthData'];
    		return $this->direct_add_user($data);
    	}*/
    	return false;
    }
    
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
    
   /* protected function direct_add_user ($data) {
    	
    }*/
}
// APPID 241890879350734
// App Secret 54cf4ee94e63739a858fffc7273b8958

