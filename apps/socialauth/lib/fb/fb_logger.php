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
			'REDIRECT_URI'	=>	(1===(int)$Config->getConfigValue('work_on_https') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/socialauth/login?do=login_fb',
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
			
			$params = array();
			$params['client_id']=$this->config['CLIENT_ID'];
			$params['redirect_uri']=$this->config['REDIRECT_URI'];
			$params['client_secret']=$this->config['CLIENT_SECRET'];
			$params['code']=$_GET['code'];
			
			$tokenInfo = null;
			$d=file_get_contents($url . '?' . http_build_query($params));
			$tokenInfo=json_decode($d, true);
		
		
			if (count($tokenInfo) > 0 && isset($tokenInfo['access_token'])) {
					
				
				$params = array('access_token' => $tokenInfo['access_token']);
				$params['fields']='id,last_name,first_name,name,email,picture';
				$x=file_get_contents('https://graph.facebook.com/v2.8/me' . '?' . urldecode(http_build_query($params)));
				
				$userInfo = json_decode($x);
				
				if (isset($userInfo->id)) {
					$_login='fb'.$userInfo->id;
					$_pass=Sitebill::genPassword();
					$email = $userInfo->email;
					/*if(!isset($userInfo->email) || $userInfo->email==''){
						$email = $_login.'@fb.com';
					}else{
						$email = $userInfo->email;
					}*/
					
					$_pass_md5=md5($_pass);
					
					$ssInfo['ssType']='fb';
					$ssInfo['id']=$userInfo->id;
					$ssInfo['email']='';
					if(isset($userInfo->email)){
						$ssInfo['email']=$userInfo->email;
					}
					
					$ssInfo['name']=$userInfo->name;
					//$ssInfo['link']=$userInfo->link;
					$ssInfo['picture']=$userInfo->picture;
					$ssInfo['_email']='fb'.$userInfo->id.'@fb.com';
					$ssInfo['_login']='fb'.$userInfo->id;
					$ssInfo['_pass']=$_pass;
					$_SESSION['ssAuthData']=$ssInfo;
					
					//$this->authUser($_login, $_pass, SiteBill::iconv('utf-8', SITE_ENCODING, $userInfo->name), $email);
					
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
		$params['scope']='email';
		
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