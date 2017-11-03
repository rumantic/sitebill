<?php
class Ok_Logger extends Common_Logger {
	
	private static $instance=NULL;
	private $config=array();
	
	public static function getInstance(){
		if(self::$instance==NULL){
			self::$instance=new Ok_Logger();
		}
		return self::$instance;
	}
	
	private function configure(){
		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
		$Config = new config_admin();
		//$Config->getServerFullUrl()
		$this->config=array(
			'CLIENT_ID'		=>	$Config->getConfigValue('apps.socialauth.ok.client_id'),
			'PUBLIC_KEY'	=>	$Config->getConfigValue('apps.socialauth.ok.public_key'),
			'RESPONSE_TYPE' =>	'code',
			'REDIRECT_URI'	=>	(1===(int)$Config->getConfigValue('work_on_https') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/socialauth/login?do=login_ok',
			'CLIENT_SECRET'	=>	$Config->getConfigValue('apps.socialauth.ok.client_secret'),
			'GRANT_TYPE'	=>	'authorization_code',
			'TOKEN_URL'		=>	'http://api.odnoklassniki.ru/oauth/token.do',
			'AUTH_URL'		=>	'http://www.odnoklassniki.ru/oauth/authorize',
			'DATA_URL'		=>	'http://api.odnoklassniki.ru/fb.do',
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
			
			$params=$this->config;
			$params['code']=$_GET['code'];
			$params['grant_type']=$this->config['GRANT_TYPE'];
			$params['client_secret']=$this->config['CLIENT_SECRET'];
			$params['client_id']=$this->config['CLIENT_ID'];
			$params['redirect_uri']=$this->config['REDIRECT_URI'];
			
			
			
			$url = $this->config['TOKEN_URL'];
			
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url); // url, куда будет отправлен запрос
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params))); // передаём параметры
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			$result = curl_exec($curl);
			curl_close($curl);
			
			$result = json_decode($result, true);
			
			//print_r($result);
			
			if (isset($result['access_token']) && isset($this->config['PUBLIC_KEY'])) {
				$sign = md5('application_key='.$this->config['PUBLIC_KEY'].'fields=uid,name,email,pic_4format=jsonmethod=users.getCurrentUser' . md5($result['access_token'].$this->config['CLIENT_SECRET']));
			
				$params = array(
						'method'          => 'users.getCurrentUser',
						'access_token'    => $result['access_token'],
						'application_key' => $this->config['PUBLIC_KEY'],
						'format'          => 'json',
						'sig'             => $sign
				);
				$params['fields']='uid,name,email,pic_4';
				
				$url = $this->config['DATA_URL'];
				$oResponce = json_decode(file_get_contents($url.'?'. urldecode(http_build_query($params))));
				if($oResponce->uid!==null){
					
					$result=$oResponce;
					$_login='ok'.$oResponce->uid;
					$_pass=$_pass=Sitebill::genPassword();
					$email = $_login.'@ok.com';
					/*if(!isset($oResponce->email) || $oResponce->email==''){
						$email = $_login.'@ok.com';
					}else{
						$email = $userInfo->email;
					}*/
					//$_pass_md5=md5($_pass);
					$ssInfo['ssType']='ok';
					$ssInfo['id']=$oResponce->uid;
					$ssInfo['email']='';
					if(isset($oResponce->email)){
						$ssInfo['email']=$oResponce->email;
					}
					
					$ssInfo['name']=$oResponce->name;
					//$ssInfo['link']=$userInfo->link;
					$ssInfo['picture']=$userInfo->pic_4;
					$ssInfo['_email']='ok'.$oResponce->uid.'@ok.com';
					$ssInfo['_login']='ok'.$oResponce->uid;
					$ssInfo['_pass']=$_pass;
					$_SESSION['ssAuthData']=$ssInfo;
					//$this->authUser($_login, $_pass, SiteBill::iconv('utf-8', SITE_ENCODING, $result->name), $email);
					return true;
				}else{
					return false;
					$answer='Ошибка при попытке авторизации.';
				}
			}else{
				return false;
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
		$href=$url.'?'.urldecode(http_build_query($params));
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