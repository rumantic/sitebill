<?php
/**
 * Login class
 * @author Kondin Dmitry <kondin@etown.ru>
 */
class Login extends SiteBill {
    var $user_id = 0;
    
    /**
     * Constructor
     */
    function Login () {
        $this->SiteBill();
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
        		//print_r($cc);
        		$_SESSION['favorites']=$cc[(int)$_SESSION['user_id']];
        	}
        }
    }
    
  
    
    function restoreUser(){
    	
    	if(isset($_COOKIE["logged_user_id"]) && (int)$_COOKIE["logged_user_id"]>0 && isset($_COOKIE["logged_user_token"]) &&  $_COOKIE["logged_user_token"]!='' && md5((int)$_COOKIE["logged_user_id"].' '.$_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_USER_AGENT'])==$_COOKIE["logged_user_token"]){
    		
    		$DBC=DBC::getInstance();
    		$query='SELECT u.login, u.user_id, u.fio, u.group_id, g.system_name, g.name FROM '.DB_PREFIX.'_user u LEFT JOIN '.DB_PREFIX.'_group g USING(group_id) WHERE u.user_id='.(int)$_COOKIE["logged_user_id"].' LIMIT 1';
    		
    		$stmt=$DBC->query($query);
    		if($stmt){
    			$ar=$DBC->fetch($stmt);
    			if((int)$ar['user_id']>0){
    				$session_key = $this->GenerateSessionKey($ar['user_id']);
    				$this->setSessionKey( $session_key );
    				$this->setUserId($ar['user_id']);
    				$_SESSION['user_id']=$ar['user_id'];
    				$_SESSION['current_user_name']=$ar['fio'];
    				
    				
    				//$this->db->exec('SELECT system_name FROM '.DB_PREFIX.'_group WHERE group_id='.$ar['group_id']);
    				//$this->db->fetch_assoc();
    				$_SESSION['current_user_group_name']=$ar['system_name'];
    				$_SESSION['current_user_group_id']=$ar['group_id'];
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
    		}
    	}
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
    		$_SESSION['go_after_login']=$back_url;
    	}
    	
        global $init;
        
        
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
        
        switch ( $init->getValue('do', 'default') ) {
        	case 'login_vk':
        		require_once (SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/vk/vk_logger.php');
        		$VK = Vk_Logger::getInstance();
        		$VK->login();
        	break;
            case 'login':
            	$this->checkLogin(  $init->getValue('login'), $init->getValue('password'), $this->getRequestValue('rememberme')  );
                //echo "error_message = ".$this->error_message."<br>";
                if ( $this->GetError() ){
                    $rs = $this->loginForm();
                } else {
                    $rs = $this->wellcomePage();
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
    		$smarty->assign('fio', $this->getFio($user_id));
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
        //global $config;
        
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
        global $config;
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $session_key = md5(rand().time().$user_ip);

        $query = "insert into ".DB_PREFIX."_session (user_id, ip, session_key, start_date) values ('".$user_id."', '$user_ip', '$session_key', now())";
        $this->db->exec( $query );
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
        
        $rs = '            
                                    <table border="0" cellpadding="0" cellspacing="0" align="center">
                                        <tr>
                                            <td class="special">
                                            	<div id="admin_area">
                                                <h1>'.Multilanguage::_('L_AUTH_TITLE').'</h1><br><div class="row-fluid">
        ';
        if ( $this->getConfigValue('ajax_auth_form') ) {
            $rs .= $this->get_ajax_auth_form();
        } else {
            $rs .= $this->get_simple_auth_form(SITEBILL_MAIN_URL.'/login/',$this->getConfigValue('allow_register_account'), $this->getConfigValue('allow_remind_password'));
        }
        $rs .= '
                                        </div></div>    
        								</td>
                                        </tr>
                                    </table>';
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
    	$query = "insert into ".DB_PREFIX."_user (login, password, fio, email, reg_date) values ('$login', '$pass', '$fio', '$email', '".date('Y-m-d H:i:s')."')";
    	$this->db->exec($query);
    	if ( !$this->db->success ) {
    		$this->riseError($this->db->error);
    		return false;
    	}
    	return true;
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
                $query = 'SELECT u.login, u.user_id, u.fio, u.group_id, g.system_name, g.name FROM '.DB_PREFIX.'_user u LEFT JOIN '.DB_PREFIX.'_group g USING(group_id) WHERE u.login=? AND u.password=?'.(1==$this->getConfigValue('use_registration_email_confirm') ? ' AND u.active=1' : '');
                
                $stmt=$DBC->query($query, array($login, md5($password)));
                
                if($stmt){
                	$ar=$DBC->fetch($stmt);
                	if ( $ar['user_id'] != '' ) {
                		
                		 
                		$session_key = $this->GenerateSessionKey($ar['user_id']);
                		$this->setSessionKey( $session_key );
                		$this->setUserId($ar['user_id']);
                		$_SESSION['user_id']=$ar['user_id'];
                		$_SESSION['current_user_name']=$ar['fio'];
                		//$this->db->exec('SELECT system_name FROM '.DB_PREFIX.'_group WHERE group_id=(SELECT group_id FROM '.DB_PREFIX.'_user WHERE login=\''.$login.'\')');
                		//$this->db->fetch_assoc();
                		$_SESSION['current_user_group_name']=$ar['system_name'];
                		$_SESSION['current_user_group_id']=$ar['group_id'];
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
                			$str=md5($_SESSION['user_id'].' '.$_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_USER_AGENT']);
                			setcookie('logged_user_id', $_SESSION['user_id'], time()+60*60*24*5, '/');
                			setcookie('logged_user_token', $str, time()+60*60*24*5, '/');
                		}
                		return true;
                	}
                }
                /*
                $this->db->exec($query);
                $this->db->fetch_assoc();
                if ( $this->db->row['user_id'] != '' ) {
                	//$Sitebill_User->loginUser($this->db->row['user_id']);
                	
                    $session_key = $this->GenerateSessionKey($this->db->row['user_id']);
                    //$Sitebill_User=Sitebill_User::getInstance();
                    //$Sitebill_User->initUser((int)$this->db->row['user_id']);
                    //$Sitebill_User->setSessionKey($session_key);
                    
                    $this->setSessionKey( $session_key );
                    $this->setUserId($this->db->row['user_id']);
                    $_SESSION['user_id']=$this->db->row['user_id'];
                    $_SESSION['current_user_name']=$this->db->row['fio'];
					$this->db->exec('SELECT system_name FROM '.DB_PREFIX.'_group WHERE group_id=(SELECT group_id FROM '.DB_PREFIX.'_user WHERE login=\''.$login.'\')');
					$this->db->fetch_assoc();
					$_SESSION['current_user_group_name']=$this->db->row['system_name'];
					
					
					$_SESSION['Sitebill_User']=array();
					$_SESSION['Sitebill_User']['name']=$ar['fio'];
					$_SESSION['Sitebill_User']['group_id']=$ar['group_id'];
					$_SESSION['Sitebill_User']['group_name']=$ar['name'];
					$_SESSION['Sitebill_User']['login']=$ar['login'];
					$_SESSION['Sitebill_User']['user_id']=(int)$ar['user_id'];
					$_SESSION['Sitebill_User']['group_system_name']=$ar['system_name'];
					
					
					if($rememberme==1){
						$str=md5($_SESSION['user_id'].' '.$_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_USER_AGENT']);
						setcookie('logged_user_id', $_SESSION['user_id'], time()+60*60*24*5, '/');
						setcookie('logged_user_token', $str, time()+60*60*24*5, '/');
					}
                    return true;
                }*/
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
        $this->db->exec($query);
        $this->db->fetch_assoc();
        $rs .= 'ФИО: '.$this->db->row['fio'].'<br>';
        $rs .= 'email: '.$this->db->row['email'].'<br>';
        return $rs;
    }
    
    /**
     * Get session user ID
     * @param void
     * @return int
     */
    function getSessionUserId ( ) {
        global $init;
        
        $key = (isset($_SESSION['key']) ? $_SESSION['key'] : '');
        if ( $key != '' ) {
        	$query = "select user_id from ".DB_PREFIX."_session where session_key ='$key'";
        	//echo $query;
        	$this->db->exec($query);
        	$this->db->fetch_assoc();
        	$user_id = $this->db->row['user_id'];
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
        $this->db->exec($query);
        $this->db->fetch_assoc();
        return $this->db->row['fio'];
    }
    
    /**
     * Get login
     * @param int $user_id user id
     * @return string
     */
    function getLogin ( $user_id ) {
        $query = "select login from ".DB_PREFIX."_user where user_id=$user_id";
        $this->db->exec($query);
        $this->db->fetch_assoc();
        return $this->db->row['login'];
    }
}
?>