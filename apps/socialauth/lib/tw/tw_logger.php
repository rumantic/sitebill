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
			'REDIRECT_URI'      =>	(1===(int)$Config->getConfigValue('work_on_https') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/socialauth/login?do=login_tw',
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
				$screen_name = $userInfo['user_id'];
				
				$oauth_base_text = "GET&";
				$oauth_base_text .= urlencode($url).'&';
				//$oauth_base_text .= urlencode('include_entities=false&');
				$oauth_base_text .= urlencode('oauth_consumer_key='.$this->config['API_KEY'].'&');
				$oauth_base_text .= urlencode('oauth_nonce='.$oauth_nonce.'&');
				$oauth_base_text .= urlencode('oauth_signature_method=HMAC-SHA1&');
				$oauth_base_text .= urlencode('oauth_timestamp='.$oauth_timestamp."&");
				$oauth_base_text .= urlencode('oauth_token='.$oauth_token."&");
				$oauth_base_text .= urlencode('oauth_version=1.0&');
				$oauth_base_text .= urlencode('user_id=' . $screen_name);
				
				
				$key = $this->config['CLIENT_SECRET'].'&'.$oauth_token_secret;
				$signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));
				
				// Формируем GET-запрос
				$url = $url;
				$url .= '?';
				//$url .= 'include_entities=false';
				$url .= 'oauth_consumer_key=' . $this->config['API_KEY'];
				$url .= '&oauth_nonce=' . $oauth_nonce;
				$url .= '&oauth_signature=' . urlencode($signature);
				$url .= '&oauth_signature_method=HMAC-SHA1';
				$url .= '&oauth_timestamp=' . $oauth_timestamp;
				$url .= '&oauth_token=' . urlencode($oauth_token);
				$url .= '&oauth_version=1.0';
				$url .= '&user_id=' . $screen_name;
				
				
				
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_POST, 0);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				$result = curl_exec($curl);
				curl_close($curl);
				
				$userInfo = json_decode($result);
				
				if(!isset($userInfo->errors) && isset($userInfo->id)){
					$_login='tw'.$userInfo->id;
					$_pass=$_pass=Sitebill::genPassword();
					$email = $_login.'@tw.com';
					//$_pass_md5=md5($_pass);

					$ssInfo['ssType']='tw';
					$ssInfo['id']=$userInfo->id;
					$ssInfo['email']='';
					if(isset($userInfo->email)){
						$ssInfo['email']=$userInfo->email;
					}
						
					$ssInfo['name']=$userInfo->screen_name;
					//$ssInfo['link']=$userInfo->link;
					$ssInfo['picture']=$userInfo->picture;
					$ssInfo['_email']='tw'.$userInfo->id;
					$ssInfo['_login']='tw'.$userInfo->id.'@tw.com';
					$ssInfo['_pass']=$_pass;
					$_SESSION['ssAuthData']=$ssInfo;
					
					//$this->authUser($_login, $_pass, SiteBill::iconv('utf-8', SITE_ENCODING, $userInfo->screen_name), $email);
					
				}else{
					return false;
					
				}
				
				
				return true;
			}
			
			
			
			
			
		}
		/*if(isset($_REQUEST['code'])){
			
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
		}*/
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
		/*require_once SITEBILL_DOCUMENT_ROOT.'/third/oauth/tmhOAuth.php';
		 require_once SITEBILL_DOCUMENT_ROOT.'/third/oauth/tmhUtilities.php';
		$connection = new tmhOAuth(array(
				'consumer_key' => $this->config['API_KEY'],
				'consumer_secret' => $this->config['CLIENT_SECRET'],
				'timestamp' => time()
		));
		$connection->request('POST', $connection->url('oauth/request_token?oauth_callback='.$this->config['REDIRECT_URI']));
		$response = $connection->extract_params($connection->response["response"]);
		//print_r($response);
		return $response;*/
		
		
		
		
		
		//return array('oauth_token'=>'2316991279-qIRJpXaqHaA7CZ1PWSXQus00ux3tyDbFyHDu1Vm', 'oauth_token_secret'=>'d7wzdM4UhAAf2ejTT9CWeJdW94nkLPZZXilqJ5mQQYbEy');
		
		$oauth_nonce = md5(uniqid(rand(), true));
		$oauth_timestamp = time();
		$url=$this->config['TOKEN_URL'];
		
		$signature_parts=array();
		
		
		$signature_parts[]='oauth_consumer_key='.$this->config['API_KEY'];
		$signature_parts[]='oauth_nonce='.$oauth_nonce;
		$signature_parts[]='oauth_signature_method=HMAC-SHA1';
		$signature_parts[]='oauth_timestamp='.$oauth_timestamp;
		$signature_parts[]='oauth_version=1.0';
		//$signature_parts[]='oauth_callback='.$this->config['REDIRECT_URI'];
		
		$signature_base='POST&'.rawurlencode($url).'&'.rawurlencode(implode('&', $signature_parts));
		//$signature_base='GET&'.rawurlencode($url).'&'.rawurlencode(implode('&', $signature_parts));
		//$signature_base=' GET&https%3A%2F%2Fapi.twitter.com%2Foauth%2Frequest_token&oauth_consumer_key%3DzBD3u6X66IUFhZLzWUaxy91Yn%26oauth_nonce%3D7ebc8554ffd31c93c2a281efb89847ab%26oauth_signature_method%3DHMAC-SHA1%26oauth_timestamp%3D1467567144%26oauth_version%3D1.0';
		
		//echo $signature_base;
		$key = $this->config['CLIENT_SECRET']."&";
		$oauth_signature = base64_encode(hash_hmac("sha1", $signature_base, $key, true));
		
		//var_dump($oauth_signature);
		
		
		/*$oauth_base_text = "POST&";
		 $oauth_base_text .= urlencode('https://api.twitter.com/oauth/request_token')."&";
		$oauth_base_text .= urlencode("oauth_consumer_key=".$this->config['API_KEY']."&");
		$oauth_base_text .= urlencode("oauth_nonce=".$oauth_nonce."&");
		$oauth_base_text .= urlencode("oauth_signature_method=HMAC-SHA1&");
		$oauth_base_text .= urlencode("oauth_timestamp=".$oauth_timestamp."&");
		$oauth_base_text .= urlencode("oauth_version=1.0");
		$oauth_base_text .= urlencode("oauth_callback=".urlencode($this->config['REDIRECT_URI'])."&");
		
		$key = $this->config['CLIENT_SECRET']."&";
			
		$oauth_signature = base64_encode(hash_hmac("sha1", $oauth_base_text, $key, true));
		*/
		
		
		$params=array();
		/*$params[]='oauth_consumer_key='.$this->config['API_KEY'];
		 $params[]='oauth_nonce='.$oauth_nonce;
		$params[]='oauth_signature='.rawurlencode($oauth_signature);
		$params[]='oauth_signature_method=HMAC-SHA1';
		$params[]='oauth_timestamp='.$oauth_timestamp;
		$params[]='oauth_version=1.0';
		$params[]='oauth_callback='.rawurlencode($this->config['REDIRECT_URI']);*/
		
		
		$params['oauth_consumer_key']=$this->config['API_KEY'];
		$params['oauth_nonce']=$oauth_nonce;
		$params['oauth_signature']=urlencode($oauth_signature);
		$params['oauth_signature_method']='HMAC-SHA1';
		$params['oauth_timestamp']=$oauth_timestamp;
		$params['oauth_version']='1.0';
		
		$href = $url/*.'?'.implode('&', $params)*/;
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $href);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, array());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
		curl_setopt($curl, CURLOPT_HEADER, false);
		//curl_setopt($curl, CURLOPT_HEADERFUNCTION, array($this, 'curlHeader'));
		if(!empty($params)) {
			uksort($params, 'strcmp');
			$encoded_quoted_pairs = array();
			foreach ($params as $k => $v) {
				$encoded_quoted_pairs[] = "{$k}=\"{$v}\"";
			}
			$header = 'Authorization: OAuth ' . implode(', ', $encoded_quoted_pairs);
				
				
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, array($header));
		
		$result = curl_exec($curl);
		curl_close($curl);
		parse_str($result, $tokenInfo);
		
		if(isset($tokenInfo['oauth_token'])){
			return $tokenInfo;
		}else{
			return false;
		}
		
		
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