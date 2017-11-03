<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Login class
 * @author Kondin Dmitry <kondin@etown.ru>
 */
class Login extends SiteBill {
    var $user_id = 0;
    private $hard_mode;
    /**
     * Constructor
     */
    function Login () {
    	
        $this->SiteBill();
        $this->hardmode=false;
        if(!isset($_SESSION['key'])){
        	$this->setSessionKey($this->GenerateSessionKey(0));
        }
        /*$Sitebill_User=Sitebill_User::getInstance();
        if(!$Sitebill_User->isLogged && !preg_match('/\/logout/', $_SERVER['REQUEST_URI'])){
        	$this->restoreUser();
        }*/
        
        if ( empty($_SESSION['user_id']) && !preg_match('/\/logout/', $_SERVER['REQUEST_URI'])) {
        	$this->restoreUser();
        }
        if(isset($_SESSION['user_id']) && (int)$_SESSION['user_id']!=0){
        	if(isset($_COOKIE['user_favorites']) && $_COOKIE['user_favorites']!=''){
        		$cc=unserialize($_COOKIE['user_favorites']);
        		$_SESSION['favorites']=$cc[(int)$_SESSION['user_id']];
        	}
        }
    }
    
    function restoreUser(){
    	
    	if(isset($_COOKIE["logged_user_id"]) && (int)$_COOKIE["logged_user_id"]>0 && isset($_COOKIE["logged_user_token"])/* &&  $_COOKIE["logged_user_token"]!='' && md5((int)$_COOKIE["logged_user_id"].' '.$_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_USER_AGENT'])==$_COOKIE["logged_user_token"]*/){
    		
    		$DBC=DBC::getInstance();
    		if($this->hard_mode){
    			
    			$what=array();
    			$where=array();
    			$where_val=array();
    			$add_fieds=array();
    			
    			if(''!=trim($this->getConfigValue('login_user_data_ad'))){
    				$fields_str=explode(',', $this->getConfigValue('login_user_data_ad'));
    				foreach($fields_str as $k){
    					$add_fieds[]=trim($k);
    				}
    			}
    			
    			if(!empty($add_fieds)){
    				foreach($add_fieds as $k){
    					$what[]='u.`'.$k.'`';
    				}
    			}
    			
    			if(1==intval($this->getConfigValue('email_as_login'))){
    				$what[]='u.`email` AS login';
    				$what[]='u.`email`';
    			}else{
    				$what[]='u.`login`';
    				$what[]='u.`email`';
    			}
    			 
    			$what[]='u.`user_id`';
    			$what[]='u.`fio`';
    			$what[]='u.`group_id`';
    			$what[]='g.`system_name`';
    			$what[]='g.`name` AS gname';
    			 
    			$where[]='u.`user_id`=?';
    			$where_val[]=$_COOKIE["logged_user_id"];
    			$where[]='u.`auth_hash`=?';
    			$where_val[]=$_COOKIE["logged_user_token"];
    			
    			$query='SELECT '.implode(',', $what).' FROM '.DB_PREFIX.'_user u LEFT JOIN '.DB_PREFIX.'_group g USING(group_id) WHERE '.implode(',', $where).' LIMIT 1';
    			$stmt=$DBC->query($query, $where_val);
    		}else{
    			$query='SELECT `auth_salt` FROM '.DB_PREFIX.'_user WHERE user_id=?';
    			$stmt=$DBC->query($query, array($_COOKIE["logged_user_id"]));
    			if(!$stmt){
    				
    				setcookie('logged_user_id', '', time()-60*60*24*5, '/', self::$_cookiedomain);
    				setcookie('logged_user_token', '', time()-60*60*24*5, '/', self::$_cookiedomain);
    				return false;
    			}
    			$ar=$DBC->fetch($stmt);
    			if($ar['auth_salt']==''){
    				
    				$auth_salt=md5(rand(10000, 99999).time());
    				$sql = 'UPDATE '.DB_PREFIX.'_user SET `auth_salt`=? WHERE `user_id`=? ';
    				$stmt=$DBC->query($sql, array($auth_salt, $_COOKIE["logged_user_id"]));
    				setcookie('logged_user_id', '', time()-60*60*24*5, '/', self::$_cookiedomain);
    				setcookie('logged_user_token', '', time()-60*60*24*5, '/', self::$_cookiedomain);
    				return false;
    			}else{
    				$auth_salt=$ar['auth_salt'];
    			}
    			$test_hash=md5((int)$_COOKIE["logged_user_id"].' '.$_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_USER_AGENT'].' '.$auth_salt);
    			//echo $_COOKIE["logged_user_token"].'@@@@';
    			//echo $test_hash;
    			if($test_hash!=$_COOKIE["logged_user_token"]){
    				//echo 'No hash_equals';
    				setcookie('logged_user_id', '', time()-60*60*24*5, '/', self::$_cookiedomain);
    				setcookie('logged_user_token', '', time()-60*60*24*5, '/', self::$_cookiedomain);
    				return false;
    			}
    			
    			
    			$what=array();
    			$where=array();
    			$where_val=array();
    			$add_fieds=array();
    			 
    			if(''!=trim($this->getConfigValue('login_user_data_ad'))){
    				$fields_str=explode(',', $this->getConfigValue('login_user_data_ad'));
    				foreach($fields_str as $k){
    					$add_fieds[]=trim($k);
    				}
    			}
    			 
    			if(!empty($add_fieds)){
    				foreach($add_fieds as $k){
    					$what[]='u.`'.$k.'`';
    				}
    			}
    			 
    			if(1==intval($this->getConfigValue('email_as_login'))){
    				$what[]='u.`email` AS login';
    				$what[]='u.`email`';
    			}else{
    				$what[]='u.`login`';
    				$what[]='u.`email`';
    			}
    			
    			$what[]='u.`user_id`';
    			$what[]='u.`fio`';
    			$what[]='u.`group_id`';
    			$what[]='g.`system_name`';
    			$what[]='g.`name` AS gname';
    			
    			$where[]='u.user_id=?';
    			$where_val[]=$_COOKIE["logged_user_id"];
    			 
    			
    			
    			
    			$query='SELECT '.implode(',', $what).' FROM '.DB_PREFIX.'_user u LEFT JOIN '.DB_PREFIX.'_group g USING(group_id) WHERE '.implode(',', $where).' LIMIT 1';
    			$stmt=$DBC->query($query, $where_val);
    		}
    		
    		
    		//$DBC=DBC::getInstance();
    		//$query='SELECT u.login, u.user_id, u.fio, u.group_id, g.system_name, g.name FROM '.DB_PREFIX.'_user u LEFT JOIN '.DB_PREFIX.'_group g USING(group_id) WHERE u.user_id='.(int)$_COOKIE["logged_user_id"].' LIMIT 1';
    		
    		//$stmt=$DBC->query($query);
    		if($stmt){
    			$ar=$DBC->fetch($stmt);
    			$user_id=intval($ar['user_id']);
    			if($user_id>0){
    				$session_key = $this->GenerateSessionKey($ar['user_id']);
    				$this->setSessionKey( $session_key );
    				$this->setUserId($ar['user_id']);
    				$_SESSION['user_id']=$ar['user_id'];
    				$_SESSION['current_user_name']=$ar['fio'];
    				$_SESSION['current_user_group_name']=$ar['system_name'];
    				$_SESSION['current_user_group_id']=$ar['group_id'];
    				
    				
    				if($_SESSION['current_user_group_name']=='admin'){
    					$_SESSION['user_id_value']=$ar['user_id'];
    				}
    				
    				$_SESSION['current_user_login']=$ar['login'];
    				$_SESSION['current_user_email']=$ar['email'];
    				$_SESSION['current_user_group_title']=$ar['gname'];
    				
    				$add_user_data=array();
    				if(!empty($add_fieds)){
    					foreach($add_fieds as $k){
    						$add_user_data[$k]=$ar[$k];
    					}
    				}
    				$_SESSION['current_user_info']=$add_user_data;
    				
    				$this->restoreFavorites($user_id);
    				
    				
    				
    				
    				/*
    				$_SESSION['Sitebill_User']=array();
    				$_SESSION['Sitebill_User']['name']=$ar['fio'];
    				$_SESSION['Sitebill_User']['group_id']=$ar['group_id'];
    				$_SESSION['Sitebill_User']['group_name']=$ar['name'];
    				$_SESSION['Sitebill_User']['login']=$ar['login'];
    				$_SESSION['Sitebill_User']['user_id']=(int)$ar['user_id'];
    				$_SESSION['Sitebill_User']['group_system_name']=$ar['system_name'];
    				*/
    			}
    			
    			return true;
    		}else{
    			setcookie('logged_user_id', '', time()-60*60*24*5, '/', self::$_cookiedomain);
    			setcookie('logged_user_token', '', time()-60*60*24*5, '/', self::$_cookiedomain);
    		}
    	}
    	
    	unset($_SESSION["user_id"]);
    }
    /**
     * Main
     * @param void
     * @return string
     */
    function main () {
    	
    	if(isset($_SESSION['go_after_login']) && $_SESSION['go_after_login']!=''){
    		$back_url=$_SESSION['go_after_login'];
    		
    	}else{
    		$back_url=$_SERVER['HTTP_REFERER'];
    		if(parse_url($back_url, PHP_URL_HOST)!=$_SERVER['HTTP_HOST']){
    			$back_url=$this->getServerFullUrl();
    		}
    		$_SESSION['go_after_login']=$back_url;
    	}
    	
        
        
        
        /*
        $Sitebill_User=Sitebill_User::getInstance();
        if($Sitebill_User->isLogged()){
        	$rs = $this->wellcomePage();
        	return $rs;
        }*/
        
        
        
        
        
       
        if ( $this->getSessionUserId() > 0 ) {
            $rs = $this->wellcomePage();
            return $rs;
        }
        $this->template->assign('title', Multilanguage::_('L_AUTH_TITLE'));
        $do=$this->getRequestValue('do');
        switch ( $do ) {
        	/*case 'login_vk':
        		require_once (SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/vk/vk_logger.php');
        		$VK = Vk_Logger::getInstance();
        		$VK->login();
        	break;*/
            case 'login':
            	$login=$this->getRequestValue('login');
            	$password=$this->getRequestValue('password');
            	$this->checkLogin(  $login, $password, $this->getRequestValue('rememberme')  );
                //echo "error_message = ".$this->error_message."<br>";
                if ( $this->GetError() ){
                    $rs = $this->loginForm();
                } else {
                    $rs = $this->wellcomePage();
                    $this->restoreFavorites($user_id);
                }
            break;
            default:
                if ( $this->getSessionUserId() > 0 ) {
                    $rs = $this->wellcomePage();
                } else {
                    $rs = $this->loginForm();
                }
        }
        return $rs;    
    }
    
    function alreadyLogin () {
        $rs = Multilanguage::_('YOU_AUTHORIZED','system');
        return $rs;
    }
    
    
    public function getAuthData(){
    	global $smarty;
    	$user_id = $this->getSessionUserId();
    	
    	if ( $user_id > 0 ) {
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
    		$Account = new Account;
    		return array('is_logged'=>1,
    				'fio'=>$this->getFio($user_id),
    				'ballance'=>$Account->getAccountValue($user_id),
    				'total_data_count'=>$Account->get_user_data_count($user_id),
    				);
    		/*$smarty->assign('auth_is_logged', 1);
    		$smarty->assign('fio', $this->getFio($user_id));
    		$smarty->assign('ballance', $Account->getAccountValue($user_id));
    		$smarty->assign('total_data_count', $Account->get_user_data_count($user_id));*/
    		//$rs = $smarty->fetch('user_menu.tpl');
    	} else {
    		return array('is_logged'=>0);
    	}
    }
    
    function getUserMenu(){
    	
    	/*$Sitebill_User=Sitebill_User::getInstance();
    	if($Sitebill_User->isLogged()){
    		$user_id = $Sitebill_User->getUserId();
    		global $smarty;
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
    		$Account = new Account;
    		$smarty->assign('fio', $this->getFio($user_id));
    		$smarty->assign('ballance', $Account->getAccountValue($user_id));
    		$smarty->assign('total_data_count', $Account->get_user_data_count($user_id));
    		$rs = $smarty->fetch('user_menu.tpl');
    	}else{
    		$rs='';
    	}*/
    	$user_id = $this->getSessionUserId();
    	if ( $user_id > 0/* || $this->USER_isUserAuthorized()*/) {
    		global $smarty;
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
    		
    		$Account = new Account;
    		//$smarty->assign('fio', $this->getFio($user_id));
    		$smarty->assign('fio', $_SESSION['current_user_name']);
    		$smarty->assign('ballance', $Account->getAccountValue($user_id));
    		$smarty->assign('total_data_count', $Account->get_user_data_count($user_id));
    		$rs = $smarty->fetch('user_menu.tpl');
    	} else {
    		$rs='';
    	}
    	return $rs;
    }
    
    /**
     * Get auth menu
     * @param void
     * @return string
     */
    function getAuthMenu () {
        global $estate_folder;
        global $smarty;
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
        
        $Account = new Account;
        
        $user_id = $this->getSessionUserId();
        if ( $user_id > 0 ) {
            $smarty->assign('fio', $this->getFio($user_id));
            $smarty->assign('ballance', $Account->getAccountValue($user_id));
            $smarty->assign('total_data_count', $Account->get_user_data_count($user_id));
            $rs = $smarty->fetch('user_menu.tpl');
        } else {
        	if($this->getConfigValue('theme')=='albostar'){
        		$rs = '<h1>'.Multilanguage::_('L_AUTH_TITLE').'</h1>';
		        if ( $this->getConfigValue('ajax_auth_form') ) {
		            $rs .= $this->get_ajax_auth_form();
		        } else {
		            $rs .= $this->get_simple_auth_form(SITEBILL_MAIN_URL.'/login/',$this->getConfigValue('allow_register_account'), $this->getConfigValue('allow_remind_password'));
		        }
		        $rs .= '';
		       
        	}else{
	        	$rs = '<table border="0" cellpadding="0" cellspacing="0" align="center">
		                                        <tr>
		                                            <td class="special">
		                                            	<div id="admin_area">
		        ';
	        	$social_link = false;
	        	if ( $this->getConfigValue('apps.socialauth.fb.enable') ) {
	        		//require_once (SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/facebook/fb_logger.php');
	        		//$FB = FB_Logger::getInstance();
	        		//$rs .= $FB->getLoginURL();
	        	}
	        	
	        	if ( $this->getConfigValue('apps.socialauth.vk.enable') ) {
	        		require_once (SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/vk/vk_logger.php');
	        		$VK = Vk_Logger::getInstance();
	        		$social_link .= $VK->getLoginLink();
	        	}
	        	if ( $social_link ) {
	        		$rs .= '<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/apps/socialauth/css/style.css" />';
	        		$rs .= '<div class="login_label">'.Multilanguage::_('LOGIN_BY','system').':</div> '.$social_link.'<br><div class="clr"></div>';
	        	}
	        	
	        	if ( $this->isDemo() ) {
	        	    $rs .= '<div class="clr"></div>login: admin, password: admin';
	        	}
		        if ( $this->getConfigValue('ajax_auth_form') ) {
		            $rs .= $this->get_ajax_auth_form();
		        } else {
		            $rs .= $this->get_simple_auth_form(SITEBILL_MAIN_URL.'/login/',$this->getConfigValue('allow_register_account'), $this->getConfigValue('allow_remind_password'));
		        }
		        $rs .= '
		                                        </div>    
		        								</td>
		                                        </tr>
		                                    </table>';
		        }
        	}
        
        
        
        
        return $rs;
    }
    
   

    /**
     * Method to generate 32 - digit session key
     * @param void
     * @return string $session_key - session key
     */
    function GenerateSessionKey ( $user_id ) {
    	$this->clear_session_table();
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $session_key = md5(rand().time().$user_ip);

        $query = 'INSERT INTO '.DB_PREFIX.'_session (`user_id`, `ip`, `session_key`, `start_date`) values (?, ?, ?, now())';
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query, array($user_id, $user_ip, $session_key));
        return $session_key;
    }
    
    /**
     * Get wellcome page
     * @param void
     * @return string
     */
    function wellcomePage () {
    	$back_url=$_SESSION['go_after_login'];
    	unset($_SESSION['go_after_login']);
    	$rs = '<h1>Добро пожаловать!</h1>';
        $rs .= 'Перейти в <a href="'.SITEBILL_MAIN_URL.'/account/data/">личный кабинет</a>';
        if ( !preg_match('/login/', $back_url) && !preg_match('/logout/', $back_url) ) {
        	$rs .= '<script type="text/javascript">location.href="'.$back_url.'"</script>';
        }
        return $rs;
    }

    /**
     * Login form
     * @param void
     * @return string
     */
    function loginForm () {
        global $ETOWN_LANG;
        if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/local_login_form.tpl') ) {
        	$this->template->assign('allow_register_account', $this->getConfigValue('allow_register_account'));
        	$this->template->assign('allow_remind_password', $this->getConfigValue('allow_remind_password'));
        	if ( $this->getError() and $this->GetErrorMessage() != 'not login' ) {
        		$this->template->assign('error_message', $this->GetErrorMessage());
        	}
        	 
        	return $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/local_login_form.tpl');
        }
        
        $rs = '<table border="0" cellpadding="0" cellspacing="0" align="center" class="front_login_form_table"><tr><td class="special"><div id="admin_area" class="front_login_form"><h1>'.Multilanguage::_('L_AUTH_TITLE').'</h1><br><div class="row-fluid">';
        if ( $this->getConfigValue('ajax_auth_form') ) {
            $rs .= $this->get_ajax_auth_form();
        } else {
            $rs .= $this->get_simple_auth_form(SITEBILL_MAIN_URL.'/login/',$this->getConfigValue('allow_register_account'), $this->getConfigValue('allow_remind_password'));
        }
        $rs .= '</div></div>';
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/admin/admin.php')){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/socialauth/admin/admin.php';
			$SA=new socialauth_admin();
			$panel=$SA->getSocialAuthPanel();
			if($panel!=''){
				$rs.='<h2>'.Multilanguage::_('L_AUTH_BYSOCIAL').'</h2>';
				$rs .= $SA->getSocialAuthPanel();
			}
		}
		
        $rs .= '</td></tr></table>';
        return $rs;
    }
    
    function get_data($url) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    function direct_add_user ($login, $pass, $fio, $email ) {
    	$query = 'INSERT INTO '.DB_PREFIX.'_user (login, password, fio, email, reg_date) VALUES (?, ?, ?, ?, ?)';
    	$DBC=DBC::getInstance();
    	if(1==intval($this->getConfigValue('email_as_login'))){
			$login=$email;
		}
		$stmt=$DBC->query($query, array($login, $pass, $fio, $email, date('Y-m-d H:i:s')));
    	if ( $stmt ) {
    		return $DBC->lastInsertId();
    	}
    	return false;
    }
    
    /*function checkSocialLogin($social_id, $type){
    	$DBC=DBC::getInstance();
    	
    	$ss_id=$type.'_socialid';
    	 
    	$query='SELECT user_id FROM '.DB_PREFIX.'_user WHERE `'.$ss_id.'`=? LIMIT 1';
    	$stmt=$DBC->query($query, array($social_id));
    }*/
    
    function setLoggedUser($id){
    	$DBC=DBC::getInstance();
    	
    	$what=array();
    	
    	$add_fieds=array();
    	 
    	if(''!=trim($this->getConfigValue('login_user_data_ad'))){
    		$fields_str=explode(',', $this->getConfigValue('login_user_data_ad'));
    		foreach($fields_str as $k){
    			$add_fieds[]=trim($k);
    		}
    	}
    	 
    	if(!empty($add_fieds)){
    		foreach($add_fieds as $k){
    			$what[]='u.`'.$k.'`';
    		}
    	}
    	 
    	 
    	$what[]='u.`login`';
    	$what[]='u.`user_id`';
    	$what[]='u.`fio`';
    	$what[]='u.`group_id`';
    	$what[]='g.`system_name`';
    	$what[]='g.`name` AS gname';
    	$what[]='u.`email`';
    	
    	
    	$query = 'SELECT '.implode(',', $what).' FROM '.DB_PREFIX.'_user u LEFT JOIN '.DB_PREFIX.'_group g USING(group_id) WHERE user_id=?';
    	$stmt=$DBC->query($query, array($id));
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		if ( $ar['user_id'] != '' ) {
    			$session_key = $this->GenerateSessionKey($ar['user_id']);
    			$this->setSessionKey( $session_key );
    			$this->setUserId($ar['user_id']);
    			$_SESSION['user_id']=$ar['user_id'];
    	
    			$_SESSION['current_user_name']=$ar['fio'];
    			$_SESSION['current_user_group_name']=$ar['system_name'];
    			if($_SESSION['current_user_group_name']=='admin'){
    				$_SESSION['user_id_value']=$ar['user_id'];
    			}
    			$_SESSION['current_user_login']=$ar['login'];
    			$_SESSION['current_user_email']=$ar['email'];
    			$_SESSION['current_user_group_id']=$ar['group_id'];
    			$_SESSION['current_user_group_title']=$ar['gname'];
    			
    			$add_user_data=array();
    			if(!empty($add_fieds)){
    				foreach($add_fieds as $k){
    					$add_user_data[$k]=$ar[$k];
    				}
    			}
    			$_SESSION['current_user_info']=$add_user_data;
    	
    			$this->restoreFavorites($ar['user_id']);
    			/*if($rememberme==1){
    				$auth_salt='';
    				$sql = 'SELECT `auth_salt` FROM '.DB_PREFIX.'_user WHERE user_id=?';
    				$stmt=$DBC->query($sql, array($_SESSION['user_id']));
    				if($stmt){
    					$ar=$DBC->fetch($stmt);
    					$auth_salt=$ar['auth_salt'];
    				}
    				 
    				if($this->hard_mode){
    					$str=$auth_hash;
    				}else{
    					if($auth_salt==''){
    						$auth_salt=md5(rand(10000, 99999).time());
    						$sql = 'UPDATE '.DB_PREFIX.'_user SET `auth_salt`=? WHERE `user_id`=? ';
    						$stmt=$DBC->query($sql, array($auth_salt, $_SESSION['user_id']));
    					}
    					$str=md5($_SESSION['user_id'].' '.$_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_USER_AGENT'].' '.$auth_salt);
    				}
    				//$str=md5($_SESSION['user_id'].' '.$_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_USER_AGENT']);
    				 
    				setcookie('logged_user_id', $_SESSION['user_id'], time()+60*60*24*5, '/', self::$_cookiedomain);
    				setcookie('logged_user_token', $str, time()+60*60*24*5, '/', self::$_cookiedomain);
    			}*/
    	
    			//$query='DELETE FROM '.DB_PREFIX.'_user_blocked_logins WHERE login=?';
    			//$stmt=$DBC->query($query, array($login));
    	
    			return true;
    		}
    	}
    }
    
    /**
     * Check login
     * @param string $login login
     * @param string $password password
     * @return boolean
     */
    function checkLogin ( $login, $password, $rememberme=0 ) {
    	/*$Sitebill_User=Sitebill_User::getInstance();
    	if($Sitebill_User->isLogged()){
    		return true;
    	}*/
    	
    	
    	
        if ( $_SESSION['user_id'] > 0/* || $_SESSION['Sitebill_User']['user_id']>0*/) {
            return true; 
        }
        if ( $this->getConfigValue('ajax_auth_form') ) {
            $this->riseError('not login');
            unset($_SESSION['user_id']);
            /*unset($_SESSION['Sitebill_User']);
            $this->USER_logoutUser();*/
            return false;
        } else {
        	
            if ( $login != '' and $password != '' ) {
            	
            	$DBC=DBC::getInstance();
            	
            	$max_try_count=intval($this->getConfigValue('max_login_try_count'));
            	if($max_try_count==0){
            		$max_try_count=5;
            	}
            	$block_time=intval($this->getConfigValue('login_block_time'));
            	
            	$query='SELECT try_count, blocked_to FROM '.DB_PREFIX.'_user_blocked_logins WHERE login=? LIMIT 1';
            	$stmt=$DBC->query($query, array($login));
            	if($stmt){
            		$ar=$DBC->fetch($stmt);
            		$try_count=intval($ar['try_count']);
            		$blocked_to=strtotime($ar['blocked_to']);
            	}else{
            		$try_count=0;
            		//$blocked_to=date('Y-m-d H:');
            	}
            	
            	if($blocked_to>time()){
            		$this->riseError(Multilanguage::_('L_ACCOUNT_LOGIN_FROZEN'));
            		return false;
            	}
            	
            	$what=array();
            	$where=array();
            	$where_val=array();
            	$add_fieds=array();
            	
            	if(''!=trim($this->getConfigValue('login_user_data_ad'))){
            		$fields_str=explode(',', $this->getConfigValue('login_user_data_ad'));
            		foreach($fields_str as $k){
            			$add_fieds[]=trim($k);
            		}
            	}
            	
            	if(!empty($add_fieds)){
            		foreach($add_fieds as $k){
            			$what[]='u.`'.$k.'`';
            		}
            	}
            	
            	
            	$what[]='u.`login`';
            	$what[]='u.`user_id`';
            	$what[]='u.`fio`';
            	$what[]='u.`group_id`';
            	$what[]='g.`system_name`';
            	$what[]='g.`name` AS gname';
            	$what[]='u.`email`';
            	
            	$where[]='u.password=?';
            	$where_val[]=md5($password);
            	if(1==$this->getConfigValue('use_registration_email_confirm')){
            		$where[]='u.active=1';
            		
            	}
            	if(1==intval($this->getConfigValue('email_as_login'))){
            		$where[]='u.email=?';
            		$where_val[]=$login;
            	}else{
            		$where[]='(u.login=? OR u.email=?)';	
            		$where_val[]=$login;
            		$where_val[]=$login;
            	}
            	
            	
                //$query = 'SELECT u.login, u.user_id, u.fio, u.group_id, g.system_name, g.name, u.auth_salt FROM '.DB_PREFIX.'_user u LEFT JOIN '.DB_PREFIX.'_group g USING(group_id) WHERE u.login=? AND u.password=?'.(1==$this->getConfigValue('use_registration_email_confirm') ? ' AND u.active=1' : '');
                $query = 'SELECT '.implode(',', $what).' FROM '.DB_PREFIX.'_user u LEFT JOIN '.DB_PREFIX.'_group g USING(group_id) WHERE '.implode(' AND ', $where);
              
                $stmt=$DBC->query($query, $where_val);
                
                if($stmt){
                	$ar=$DBC->fetch($stmt);
                	if ( $ar['user_id'] != '' ) {
                		
                		 
                		$session_key = $this->GenerateSessionKey($ar['user_id']);
                		$this->setSessionKey( $session_key );
                		$this->setUserId($ar['user_id']);
                		$_SESSION['user_id']=$ar['user_id'];
                		
                		$_SESSION['current_user_name']=$ar['fio'];
                		$_SESSION['current_user_group_name']=$ar['system_name'];
                		if($_SESSION['current_user_group_name']=='admin'){
                			$_SESSION['user_id_value']=$ar['user_id'];
                		}
                		$_SESSION['current_user_login']=$ar['login'];
                		$_SESSION['current_user_email']=$ar['email'];
                		$_SESSION['current_user_group_id']=$ar['group_id'];
                		$_SESSION['current_user_group_title']=$ar['gname'];
                		
                		$add_user_data=array();
                		if(!empty($add_fieds)){
                			foreach($add_fieds as $k){
                				$add_user_data[$k]=$ar[$k];
                			}
                		}
                		$_SESSION['current_user_info']=$add_user_data;
                		
                		$this->restoreFavorites($ar['user_id']);
                		
                		$this->writeLog(array('apps_name'=>'auth', 'method' => 'login', 'message' => 'Авторизация пользователя ID: '.$ar['user_id'], 'type' => NOTICE));
                		
                		/*$query='INSERT INTO '.DB_PREFIX.'_user_logins (user_id, login_date) VALUES (?,?)';
                		$stmt=$DBC->query($query, array($ar['user_id'], date('Y-m-d H:i:s')));*/
                		/*	
                		$_SESSION['Sitebill_User']=array();
                		$_SESSION['Sitebill_User']['name']=$ar['fio'];
                		$_SESSION['Sitebill_User']['group_id']=$ar['group_id'];
                		$_SESSION['Sitebill_User']['group_name']=$ar['name'];
                		$_SESSION['Sitebill_User']['login']=$ar['login'];
                		$_SESSION['Sitebill_User']['user_id']=(int)$ar['user_id'];
                		$_SESSION['Sitebill_User']['group_system_name']=$ar['system_name'];
                		$_SESSION['Sitebill_User']['auth_time']=date('Y-m-d H:i:s', time());
                		array('name'=>$ar['fio'],
                		'group_id'=>$ar['group_id'],
                		'group_name'=>$ar['name'],
                		'login'=>$ar['login'],
                		'user_id'=>(int)$ar['user_id'],
                		'group_system_name'=>$ar['system_name'],
                		'auth_time'=>date('Y-m-d H:i:s', time()));
                		*/	
                	
                			
                		if($rememberme==1){
                			$auth_salt='';
                			//$auth_salt=$ar['auth_salt'];
                			
                			$sql = 'SELECT `auth_salt` FROM '.DB_PREFIX.'_user WHERE user_id=?';
                			$stmt=$DBC->query($sql, array($_SESSION['user_id']));
                			if($stmt){
                				$ar=$DBC->fetch($stmt);
                				$auth_salt=$ar['auth_salt'];
                			}
                			
                			if($this->hard_mode){
                				$str=$auth_hash;
                			}else{
                				if($auth_salt==''){
                					$auth_salt=md5(rand(10000, 99999).time());
                					$sql = 'UPDATE '.DB_PREFIX.'_user SET `auth_salt`=? WHERE `user_id`=? ';
                					$stmt=$DBC->query($sql, array($auth_salt, $_SESSION['user_id']));
                				}
                				$str=md5($_SESSION['user_id'].' '.$_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_USER_AGENT'].' '.$auth_salt);
                			}
                			//$str=md5($_SESSION['user_id'].' '.$_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_USER_AGENT']);
                			
                			setcookie('logged_user_id', $_SESSION['user_id'], time()+60*60*24*5, '/', self::$_cookiedomain);
                			setcookie('logged_user_token', $str, time()+60*60*24*5, '/', self::$_cookiedomain);
                		}
                		
                		$query='DELETE FROM '.DB_PREFIX.'_user_blocked_logins WHERE login=?';
                		$stmt=$DBC->query($query, array($login));
                		
                		return true;
                	}
                }
                
                $try_count++;
                if($try_count>=$max_try_count){
                	if($max_try_count>1){
                		$query='UPDATE '.DB_PREFIX.'_user_blocked_logins SET try_count=?, blocked_to=? WHERE login=?';
                		$stmt=$DBC->query($query, array($try_count, date('Y-m-d H:i:s', time()+$block_time*60), $login));
                	}else{
                		$query='INSERT INTO '.DB_PREFIX.'_user_blocked_logins (login, try_count, blocked_to) VALUES (?,?,?)';
                		//$query='UPDATE '.DB_PREFIX.'_user_blocked_logins SET try_count=?, blocked_to=? WHERE login=?';
                		$stmt=$DBC->query($query, array($login, $try_count, date('Y-m-d H:i:s', time()+$block_time*60)));
                	}
                }elseif($try_count==1){
                	$query='INSERT INTO '.DB_PREFIX.'_user_blocked_logins (login, try_count) VALUES (?,?)';
                	$stmt=$DBC->query($query, array($login, $try_count));
                }else{
                	$query='UPDATE '.DB_PREFIX.'_user_blocked_logins SET try_count=? WHERE login=?';
                	$stmt=$DBC->query($query, array($try_count, $login));
                }
                
                $this->riseError(Multilanguage::_('L_ERROR_LOGIN_PASS'));
                return false;
            }
            $this->riseError('not login');
            unset($_SESSION['user_id']);
           return false;
        }
    }
    /**
     * Set session
     * @param string $key session key
     * @return void
     */
    function setSessionKey ( $key ) {
        $_SESSION['key'] = $key;
    }
    
    /**
     * Get user ID
     * @param void
     * @return int
     */
    function getUserId () {
        return $this->user_id;
    }
    
    /**
     * Set user ID
     * @param int $user_id user ID
     * @return void
     */
    function setUserId ( $user_id ) {
        $this->user_id = $user_id;
    }
    
    /**
     * Get user info string
     * @param int $user_id user id
     * @return string
     */
    function getUserInfo ( $user_id ) {
        $query = "select * from ".DB_PREFIX."_user where user_id=$user_id";
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		$rs='';
		if($stmt){
			$ar=$DBC->fetch($stmt);
			$rs .= 'ФИО: '.$ar['fio'].'<br>';
			$rs .= 'email: '.$ar['email'].'<br>';
		}
        return $rs;
    }
    
    /**
     * Get session user ID
     * @param void
     * @return int
     */
    function getSessionUserId ( ) {
        $key = (isset($_SESSION['key']) ? $_SESSION['key'] : '');
	if ( self::$Heaps['session']['user_id_none'] == 1 ) {
	    return false;
	}
	
	if ( self::$Heaps['session']['user_id'] != '' ) {
	    return self::$Heaps['session']['user_id'];
	}
        if ( $key != '' ) {
        	$query = 'SELECT `user_id` FROM '.DB_PREFIX.'_session WHERE `session_key` =?';
        	$DBC=DBC::getInstance();
			$stmt=$DBC->query($query, array($key));
			if($stmt){
				$ar=$DBC->fetch($stmt);
				$user_id = $ar['user_id'];
				if ( $user_id == '' ) {
				    self::$Heaps['session']['user_id_none'] = 1;
				} else {
				    self::$Heaps['session']['user_id'] = $user_id;
				}
				//echo 'set user_id = '.$user_id;
			}
        	if ( $user_id != '' and $user_id != 0 ) {
        		$this->user_id = $user_id;
        		//$init->setUserId($user_id);
        		return $user_id;
        	} else {
        		$this->user_id = 0;
        		return 0;
        	}
        }
        $this->user_id = 0;
        return 0;
    }

    /**
     * Get fio
     * @param int $user_id user id
     * @return string
     */
    function getFio ( $user_id ) {
        $query = "select fio from ".DB_PREFIX."_user where user_id=$user_id";
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			$ar=$DBC->fetch($stmt);
			return $ar['fio'];
		}
    }
    
    /**
     * Get login
     * @param int $user_id user id
     * @return string
     */
    function getLogin ( $user_id ) {
        $query = "select login from ".DB_PREFIX."_user where user_id=$user_id";
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
    	if($stmt){
			$ar=$DBC->fetch($stmt);
			return $ar['login'];
		}
    }
}