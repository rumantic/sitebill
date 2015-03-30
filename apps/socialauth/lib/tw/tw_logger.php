<?php
class Tw_Logger extends Common_Logger {
	
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
			'API_KEY'			=>	$Config->getConfigValue('apps.socialauth.tw.api_key'),
			'CLIENT_SECRET'		=>	$Config->getConfigValue('apps.socialauth.tw.client_secret'),
			'REDIRECT_URI'      =>	'http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/socialauth/login?do=login_tw',
			'TOKEN_URL'			=>	'https://api.twitter.com/oauth/request_token',
			'AUTH_URL'			=>	'https://api.twitter.com/oauth/authorize',
			'ACCESS_TOKEN_URL'	=>	'https://api.twitter.com/oauth/access_token',
			'ACCOUNT_DATA_URL'	=>	'https://api.twitter.com/1.1/users/show.json',
		);
	}
	
	public function prelogin(){
		$url=$this->getLoginURL();
		if(!$url){
			return 'ERROR';
		}
		header('location: '.$url);
		exit();
	}
	
	public function login(){
		
		$Config = new config_admin();
		
		$answer='';
		if(isset($_GET['oauth_token'])){
			
			$oauth_nonce = md5(uniqid(rand(), true));
			$oauth_timestamp = time();
			$oauth_token = $_GET['oauth_token'];
			$oauth_verifier = $_GET['oauth_verifier'];
			$oauth_token_secret = $_SESSION['oauth_token_secret'];
			
			
			$url=$this->config['ACCESS_TOKEN_URL'];
			
			$oauth_base_text = "GET&";
			$oauth_base_text .= urlencode($url)."&";
			$oauth_base_text .= urlencode("oauth_token=".$oauth_token."&");
			$oauth_base_text .= urlencode("oauth_consumer_key=".$this->config['API_KEY']."&");
			$oauth_base_text .= urlencode("oauth_nonce=".$oauth_nonce."&");
			$oauth_base_text .= urlencode("oauth_signature_method=HMAC-SHA1&");
			$oauth_base_text .= urlencode("oauth_timestamp=".$oauth_timestamp."&");
			$oauth_base_text .= urlencode("oauth_verifier=".$oauth_verifier."&");
			$oauth_base_text .= urlencode("oauth_version=1.0");
			
			$key = $this->config['CLIENT_SECRET']."&".$oauth_token_secret;
				
			$oauth_signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));
			
			
			
			$params=array();
			$params['oauth_consumer_key']=$this->config['API_KEY'];
			$params['oauth_nonce']=$oauth_nonce;
			$params['oauth_signature_method']='HMAC-SHA1';
			$params['oauth_signature']=$oauth_signature;
			$params['oauth_version']='1.0';
			$params['oauth_timestamp']=$oauth_timestamp;
			$params['oauth_token']=urlencode($oauth_token);
			$params['oauth_verifier']=urlencode($oauth_verifier);
			
			$href = $url . '?' . urldecode(http_build_query($params));
		
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $href);
			curl_setopt($curl, CURLOPT_POST, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			$result = curl_exec($curl);
			
			curl_close($curl);
			
			parse_str($result, $userInfo);
			
			if (isset($userInfo['user_id'])) {
				
				$url=$this->config['ACCOUNT_DATA_URL'];
				$oauth_nonce = md5(uniqid(rand(), true));
				
				// время когда будет выполняться запрос (в секундых)
				$oauth_timestamp = time();
				
				$oauth_token = $userInfo['oauth_token'];
				$oauth_token_secret = $userInfo['oauth_token_secret'];
				$screen_name = $userInfo['screen_name'];
				
				$oauth_base_text = "GET&";
				$oauth_base_text .= urlencode($url).'&';
				$oauth_base_text .= urlencode('oauth_consumer_key='.$this->config['API_KEY'].'&');
				$oauth_base_text .= urlencode('oauth_nonce='.$oauth_nonce.'&');
				$oauth_base_text .= urlencode('oauth_signature_method=HMAC-SHA1&');
				$oauth_base_text .= urlencode('oauth_timestamp='.$oauth_timestamp."&");
				$oauth_base_text .= urlencode('oauth_token='.$oauth_token."&");
				$oauth_base_text .= urlencode('oauth_version=1.0&');
				$oauth_base_text .= urlencode('screen_name=' . $screen_name);
				
				$key = $this->config['CLIENT_SECRET'].'&'.$oauth_token_secret;
				$signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));
				
				// Формируем GET-запрос
				$url = $url;
				$url .= '?oauth_consumer_key=' . $this->config['API_KEY'];
				$url .= '&oauth_nonce=' . $oauth_nonce;
				$url .= '&oauth_signature=' . urlencode($signature);
				$url .= '&oauth_signature_method=HMAC-SHA1';
				$url .= '&oauth_timestamp=' . $oauth_timestamp;
				$url .= '&oauth_token=' . urlencode($oauth_token);
				$url .= '&oauth_version=1.0';
				$url .= '&screen_name=' . $screen_name;
				
				
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_POST, 0);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				$result = curl_exec($curl);
				curl_close($curl);
				
				$user_data = json_decode($result);
				
				$_login='tw'.$user_data->id;
				$_pass='tw'.$user_data->id.$Config->getConfigValue('apps.socialauth.salt');
				$email = $_login.'@tw.com';
				$_pass_md5=md5($_pass);
					
				$this->authUser($_login, $_pass, SiteBill::iconv('utf-8', SITE_ENCODING, $user_data->screen_name), $email);
				
				return true;
			}
			
			
			
			
			
		}
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
	
	
	
	public function getLoginURL(){
		if($tokenInfo=$this->getToken()){
			$_SESSION['oauth_token'] = $tokenInfo['oauth_token'];
			$_SESSION['oauth_token_secret'] = $tokenInfo['oauth_token_secret'];
			$href = $this->config['AUTH_URL'];
			$href .= '?oauth_token='.$tokenInfo['oauth_token'];
			return $href;
		}else{
			return false;
		}
	}
	
	private function getToken(){
		$oauth_nonce = md5(uniqid(rand(), true));
		$oauth_timestamp = time();
		
		
		
		$url=$this->config['TOKEN_URL'];
		
		$oauth_base_text = "GET&";
		$oauth_base_text .= urlencode($url)."&";
		$oauth_base_text .= urlencode("oauth_callback=".urlencode($this->config['REDIRECT_URI'])."&");
		$oauth_base_text .= urlencode("oauth_consumer_key=".$this->config['API_KEY']."&");
		$oauth_base_text .= urlencode("oauth_nonce=".$oauth_nonce."&");
		$oauth_base_text .= urlencode("oauth_signature_method=HMAC-SHA1&");
		$oauth_base_text .= urlencode("oauth_timestamp=".$oauth_timestamp."&");
		$oauth_base_text .= urlencode("oauth_version=1.0");
		
		$key = $this->config['CLIENT_SECRET']."&";
			
		$oauth_signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));
		
		
		
		$params=array();
		$params['oauth_consumer_key']=$this->config['API_KEY'];
		$params['oauth_nonce']=$oauth_nonce;
		$params['oauth_signature_method']='HMAC-SHA1';
		$params['oauth_signature']=$oauth_signature;
		$params['oauth_version']='1.0';
		$params['oauth_timestamp']=$oauth_timestamp;
		$params['oauth_callback']=urlencode($this->config['REDIRECT_URI']);
		
		
		$href = $url . '?' . urldecode(http_build_query($params));
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $href);
		curl_setopt($curl, CURLOPT_POST, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($curl);
		curl_close($curl);
		//echo $result;
		parse_str($result, $tokenInfo);
		
		if(isset($tokenInfo['oauth_token'])){
			return $tokenInfo;
		}else{
			return false;
		}
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