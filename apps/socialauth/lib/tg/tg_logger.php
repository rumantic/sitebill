<?php
/*https://telegram.org/blog/login*/
class Tg_Logger extends Common_Logger {
	
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
			'CLIENT_ID'		=>	$Config->getConfigValue('apps.socialauth.gl.client_id'),
			'CLIENT_SECRET'	=>	$Config->getConfigValue('apps.socialauth.gl.client_secret'),
			'REDIRECT_URI'	=>	(1===(int)$Config->getConfigValue('work_on_https') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/socialauth/login?do=login_gl',
			'TOKEN_URL'		=>	'https://accounts.google.com/o/oauth2/token',
			'AUTH_URL'		=>	'https://accounts.google.com/o/oauth2/auth',
		);
	}
	
	public function prelogin(){
		$url=$this->getLoginURL();
		header('location: '.$url);
		exit();
	}
	
	public function login(){
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
		$Config = new config_admin();
        //var_dump($_REQUEST);
        //exit();
        
        $auth_data=$_GET;
        //$BOT_TOKEN='468363704:AAE34BZvvUqSw2H6fXl1kCJSLCdvPCYP1pQ';
        $BOT_TOKEN=$Config->getConfigValue('apps.socialauth.tg.bot_token');
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
		$login_object = new Login();
		$Config = new config_admin();
        
        $check_hash = $auth_data['hash'];
        unset($auth_data['hash']);
        unset($auth_data['do']);
        $data_check_arr = array();
        foreach ($auth_data as $key => $value) {
          $data_check_arr[] = $key . '=' . $value;
        }
        sort($data_check_arr);
        
        $data_check_string = implode("\n", $data_check_arr);
        $secret_key = hash('sha256', $BOT_TOKEN, true);
        $hash = hash_hmac('sha256', $data_check_string, $secret_key);
        
        if (strcmp($hash, $check_hash) !== 0) {
            //throw new Exception('Data is NOT from Telegram');
            return false;
			$answer='Data is NOT from Telegram';
        }
        if ((time() - $_GET['auth_date']) > 86400) {
            //throw new Exception('Data is outdated');
            return false;
			$answer='Data is outdated';
        }
        
        $_login='tg'.$auth_data['id'];
        $_pass=Sitebill::genPassword();
        $email = $_login.'@tg.com';
     
        $_pass_md5=md5($_pass);
        $ssInfo['ssType']='tg';
        $ssInfo['id']=$auth_data['id'];
        $ssInfo['email']='';
        $ssInfo['name']='';
        if(isset($auth_data['first_name']) && isset($auth_data['last_name']) && $auth_data['first_name']!='' && $auth_data['last_name']!=''){
            $ssInfo['name']=$auth_data['first_name'].' '.$auth_data['last_name'];
        }elseif(isset($auth_data['first_name']) && $auth_data['first_name']!=''){
            $ssInfo['name']=$auth_data['first_name'];
        }elseif(isset($auth_data['last_name']) && $auth_data['last_name']!=''){
            $ssInfo['name']=$auth_data['last_name'];
        }
        
        
        $ssInfo['_email']='tg'.$auth_data['id'].'@tg.com';
        $ssInfo['_login']='tg'.$auth_data['id'];
        $ssInfo['_pass']=$_pass;
        //$ssInfo['group_id']=$this->getConfigValue('apps.socialauth.default_group_id');
        $_SESSION['ssAuthData']=$ssInfo;
        return true;
        
        echo $hash;
        
        var_dump($_REQUEST);
        exit();
		
		$answer='';
		if(isset($_REQUEST['code'])){
			
			$result = false;
			
			$url = $this->config['TOKEN_URL'];
			
			$params=$this->config;
			$params['client_id']=$this->config['CLIENT_ID'];
			$params['redirect_uri']=$this->config['REDIRECT_URI'];
			$params['client_secret']=$this->config['CLIENT_SECRET'];
			$params['code']=$_GET['code'];
			$params['grant_type']='authorization_code';
			
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			$result = curl_exec($curl);
			curl_close($curl);
			
			$tokenInfo = json_decode($result, true);
			
			if (isset($tokenInfo['access_token'])) {
				$params['access_token'] = $tokenInfo['access_token'];
			
				$userInfo = json_decode(file_get_contents('https://www.googleapis.com/oauth2/v1/userinfo' . '?' . urldecode(http_build_query($params))));
				if (isset($userInfo->id)) {
					$_login='gl'.$userInfo->id;
					$_pass=Sitebill::genPassword();
					$email = $_login.'@gl.com';
					/*if(!isset($userInfo->email) || $userInfo->email==''){
						$email = $_login.'@gl.com';
					}else{
						$email = $userInfo->email;
					}*/
					$_pass_md5=md5($_pass);
					$ssInfo['ssType']='gl';
					$ssInfo['id']=$userInfo->id;
					$ssInfo['email']=$userInfo->email;
					$ssInfo['name']=$userInfo->name;
					$ssInfo['link']=$userInfo->link;
					$ssInfo['picture']=$userInfo->picture;
					$ssInfo['_email']='gl'.$userInfo->id.'@gl.com';
					$ssInfo['_login']='gl'.$userInfo->id;
					$ssInfo['_pass']=$_pass;
					//$ssInfo['group_id']=$this->getConfigValue('apps.socialauth.default_group_id');
					$_SESSION['ssAuthData']=$ssInfo;
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
		$params['scope']='https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile';
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