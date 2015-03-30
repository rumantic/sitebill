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
					}
					break;
				}
				case 'login_vk' : {
					if($this->getConfigValue('apps.socialauth.vk.enable')){
						require_once SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/lib/vk/vk_logger.php';
						$VK=Vk_Logger::getInstance();
						$r=$VK->login();
					}
					break;
				}
				case 'login_fb' : {
					if($this->getConfigValue('apps.socialauth.fb.enable')){
						require_once SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/lib/fb/fb_logger.php';
						$FB=Fb_Logger::getInstance();
						$r=$FB->login();
					}
					break;
				}
				case 'login_tw' : {
					if($this->getConfigValue('apps.socialauth.tw.enable')){
						require_once SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/lib/tw/tw_logger.php';
						$TW=Tw_Logger::getInstance();
						$r=$TW->login();
					}
					break;
				}
				case 'login_gl' : {
					if($this->getConfigValue('apps.socialauth.gl.enable')){
						require_once SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/lib/gl/gl_logger.php';
						$GL=Gl_Logger::getInstance();
						$r=$GL->login();
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
}
// APPID 241890879350734
// App Secret 54cf4ee94e63739a858fffc7273b8958

