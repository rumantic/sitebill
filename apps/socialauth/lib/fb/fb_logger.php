<?php
class Fb_Logger extends Common_Logger {
	
	private static $instance=NULL;
	private $config=array();
	
	public static function getInstance(){
		if(self::$instance==NULL){
			self::$instance=new self();
		}
		return self::$instance;
	}
	
	private function configure(){
		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
		$Config = new config_admin();
		
		$this->config=array(
			'CLIENT_ID'		=>	$Config->getConfigValue('apps.socialauth.fb.client_id'),
			'CLIENT_SECRET'	=>	$Config->getConfigValue('apps.socialauth.fb.client_secret'),
			'REDIRECT_URI'	=>	'http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/socialauth/login?do=login_fb',
			'TOKEN_URL'		=>	'https://graph.facebook.com/oauth/access_token',
			'AUTH_URL'		=>	'https://www.facebook.com/dialog/oauth',
		);
	}
	
	public function prelogin(){
		$url=$this->getLoginURL();
		header('location: '.$url);
		exit();
	}
	
	public function login(){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
		$login_object = new Login();
		$Config = new config_admin();
		
		$answer='';
		if(isset($_REQUEST['code'])){
			
			$result = false;
			
			
			$url = $this->config['TOKEN_URL'];
			
			$params=$this->config;
			$params['client_id']=$this->config['CLIENT_ID'];
			$params['redirect_uri']=$this->config['REDIRECT_URI'];
			$params['client_secret']=$this->config['CLIENT_SECRET'];
			$params['code']=$_GET['code'];
			
			$tokenInfo = null;
			parse_str(file_get_contents($url . '?' . http_build_query($params)), $tokenInfo);
			
			if (count($tokenInfo) > 0 && isset($tokenInfo['access_token'])) {
				$params = array('access_token' => $tokenInfo['access_token']);
			
				$userInfo = json_decode(file_get_contents('https://graph.facebook.com/me' . '?' . urldecode(http_build_query($params))));
				if (isset($userInfo->id)) {
					$_login='fb'.$userInfo->id;
					$_pass=$_login.$Config->getConfigValue('apps.socialauth.salt');
					$email = $_login.'@fb.com';
					$_pass_md5=md5($_pass);
					$this->authUser($_login, $_pass, SiteBill::iconv('utf-8', SITE_ENCODING, $userInfo->name), $email);
					
					return true;
				}
			}
			
			
			
		         
			
		        
		}else{
			$answer=$this->getLoginLink();
		}
		return $answer;
	}
	
	public function getLoginLink(){
		$url=$this->config['AUTH_URL'];
		$params=array();
		$params['client_id']=$this->config['CLIENT_ID'];
		$params['redirect_uri']=$this->config['REDIRECT_URI'];
		$params['response_type']='code';
		
		
		
		$href=$url.'?'.urldecode(http_build_query($params));
		return '<a href="'.$href.'" class="ok_button">OK</a>';
	}
	
	public function getLoginURL(){
		$url=$this->config['AUTH_URL'];
		$params=array();
		$params['client_id']=$this->config['CLIENT_ID'];
		$params['redirect_uri']=$this->config['REDIRECT_URI'];
		$params['response_type']='code';
		$params['scope']='email, user_birthday';
		
		$href = $url . '?' . urldecode(http_build_query($params));
		return $href;
	}
	
	
	private function __construct(){
		$this->configure();
		if(isset($_SESSION['current_user']) && $_SESSION['current_user']['user_id']>0){
			$this->user_id=$_SESSION['current_user']['user_id'];
			$this->user_name=$_SESSION['current_user']['name'];
		}
	}
	
	
	
	private function __clone(){
		
	}
}