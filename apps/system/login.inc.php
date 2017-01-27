<?php
class Sitebill_Auth extends SiteBill {
	private $hard_mode;
    /**
     * Constructor
     */
    function Sitebill_Auth() {
        $this->SiteBill();
        $this->hardmode=false;
    }
    
    function restoreUser(){
    	
    	if((int)$_COOKIE["logged_user_id"]>0 && $_COOKIE["logged_user_token"]!=''/* && md5((int)$_COOKIE["logged_user_id"].' '.$_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_USER_AGENT'])==$_COOKIE["logged_user_token"]*/){
    		$DBC=DBC::getInstance();
    		if($this->hard_mode){
    			$query='SELECT u.login, u.password, u.fio, u.group_id, u.user_id, g.system_name FROM '.DB_PREFIX.'_user u LEFT JOIN '.DB_PREFIX.'_group g USING(group_id) WHERE u.user_id=? AND u.`auth_hash`=? LIMIT 1';
    			$stmt=$DBC->query($query, array($_COOKIE["logged_user_id"], $_COOKIE["logged_user_token"]));
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
    			if($test_hash!=$_COOKIE["logged_user_token"]){
    				setcookie('logged_user_id', '', time()-60*60*24*5, '/', self::$_cookiedomain);
    				setcookie('logged_user_token', '', time()-60*60*24*5, '/', self::$_cookiedomain);
    				return false;
    			}
    			$query='SELECT u.login, u.password, u.fio, u.group_id, u.user_id, g.system_name FROM '.DB_PREFIX.'_user u LEFT JOIN '.DB_PREFIX.'_group g USING(group_id) WHERE u.user_id=? LIMIT 1';
    			$stmt=$DBC->query($query, array($_COOKIE["logged_user_id"]));
    		}
    		
    		
    		//$query='SELECT login, password, fio, group_id, user_id FROM '.DB_PREFIX.'_user WHERE user_id='.(int)$_COOKIE["logged_user_id"].' LIMIT 1';
    		//$query='SELECT login, password, fio, group_id, user_id FROM '.DB_PREFIX.'_user WHERE user_id=? AND `auth_hash`=? LIMIT 1';
    		
    		
    		if($stmt){
    			$ar=$DBC->fetch($stmt);
    			require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/permission/permission.php');
    			$permission = new Permission();
    			if ( !$permission->get_access($ar['user_id'], 'admin_panel', 'login') ) {
    				return false;
    			}
    			
    			$session_key = $this->GenerateSessionKey($ar['user_id']);
    			$_SESSION['key'] = $session_key;
    			$id=$ar['user_id'];
    				
    			$_SESSION['user_id_value']=$id;
    			$_SESSION['user_id']=$id;
    			$_SESSION['current_user_group_id']=$ar['group_id'];
    			$_SESSION['current_user_name']=$ar['fio'];
    			$_SESSION['current_user_group_name']=$ar['system_name'];
    				
    			/*$query='SELECT system_name FROM '.DB_PREFIX.'_group WHERE group_id=?';
    			$stmt=$DBC->query($query, array($ar['group_id']));
    			$ar=$DBC->fetch($stmt);*/
    			
    			return true;
    			
    			/*
    			 
    			$password = $ar['password'];
    			$login = $ar['login'];
    			
    			$sql = "SELECT user_id, group_id FROM re_user WHERE login=? AND password=? LIMIT 1";
    			$stmt=$DBC->query($sql, array($login, $password));
    			if($stmt){
    				$ar1=$DBC->fetch($stmt);
    				if ( $ar1['user_id'] > 0 ) {
    					if ( !$permission->get_access($ar1['user_id'], 'admin_panel', 'login') ) {
    						return false;
    					}
    					$session_key = $this->GenerateSessionKey($ar['user_id']);
    					$_SESSION['key'] = $session_key;
    					$id=$ar1['user_id'];
    					
    					$_SESSION['user_id_value']=$id;
    					$_SESSION['user_id']=$id;
    					$_SESSION['current_user_group_id']=$ar['group_id'];
    					
    					$_SESSION['current_user_name']=$ar['fio'];
    					
    					
    					$query='SELECT system_name FROM '.DB_PREFIX.'_group WHERE group_id=(SELECT group_id FROM '.DB_PREFIX.'_user WHERE login=?)';
    					$stmt=$DBC->query($query, array($login));
    					$ar=$DBC->fetch($stmt);
    					$_SESSION['current_user_group_name']=$ar['system_name'];
    					return true;
    				}
    			}
    			
    			return false;*/
    			
    		}
    		
    	}
    	setcookie('logged_user_id', '', time()-60*60*24*5, '/', self::$_cookiedomain);
    	setcookie('logged_user_token', '', time()-60*60*24*5, '/', self::$_cookiedomain);
    	return false;
    }
    
    /**
     * Main
     */
    function main () {
    	
    	if ( $this->getConfigValue('ajax_auth_form') ) {
    		if ( $_SESSION['user_id'] == '' or  $_SESSION['group'] != 'nanoadmin' ) {
    			$this->riseError('not login');
    			unset($_SESSION['user_id']);
    	
    			return $this->getAuthForm();
    		}
    		return true;
    	} else {
    		
    		if ( empty($_SESSION['user_id'])) {
    			
    			if($this->restoreUser()){
    				
    				return true;
    			}else{
    				
    				if(strtolower($_SERVER['REQUEST_METHOD'])=='post'){
    					if(1===intval($this->getConfigValue('use_captcha_admin_entry'))){
    						$c['captcha']['name'] = 'captcha';
    						$c['captcha']['title'] = Multilanguage::_('CAPTCHA_TITLE', 'system');
    						$c['captcha']['value'] = '';
    						$c['captcha']['length'] = 40;
    						$c['captcha']['type'] = 'captcha';
    						$c['captcha']['required'] = 'on';
    						$c['captcha']['unique'] = 'off';
    							
    						require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    						$data_model = new Data_Model();
    						$form_data = $this->data_model;
    						$c = $data_model->init_model_data_from_request($c);
    							
    						$captcha_check=$data_model->check_data($c);
    					}else{
    						$captcha_check=true;
    					}
    					
    					if(!$captcha_check){
    						$this->riseError($data_model->GetErrorMessage());
    						return $this->getAuthForm();
    					}elseif ( $this->checkAuth($this->getRequestValue('login'), $this->getRequestValue('password'), $this->getRequestValue('rememberme')) ) {
    						//$_SESSION['user_id'] = 'true';
    						return true;
    					}
    						
    					
    					
    				}
    				$this->riseError('not login');
    				return $this->getAuthForm();
    			}
    	
    		}else {
    			
    			require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/permission/permission.php');
    			$permission = new Permission();
    			if ( !$permission->get_access($_SESSION['user_id_value'], 'admin_panel', 'login') ) {
    				unset($_SESSION['user_id']);
    				$this->riseError('not login');
    				return $this->getAuthForm();
    			}
    		}
    	}
    	return true;
    	
    	
    	/*$Sitebill_User=Sitebill_User::getInstance();
    	if($Sitebill_User->isLogged()){
    		return true;
    	}
    	
    	
        if ( $this->getConfigValue('ajax_auth_form') ) {
        	if(!$Sitebill_User->isLogged() || $_SESSION['group'] != 'nanoadmin'){
        		$this->riseError('not login');
        		$Sitebill_User->logoutUser();
        		return $this->getAuthForm();
        	}
        	return true;
        } else {
        	if(!$Sitebill_User->isLogged() || !$Sitebill_User->isAdmin()){
        		if($this->restoreUser()){
        			return true;
        		}else{
        			if ( $this->checkAuth($this->getRequestValue('login'), $this->getRequestValue('password'), $this->getRequestValue('rememberme')) ) {
        				return true;
        			}
        			$this->riseError('not login');
        			return $this->getAuthForm();
        		}
        	}else{
        		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/permission/permission.php');
        		$permission = new Permission();
        		if ( !$permission->get_access($Sitebill_User->getId(), 'admin_panel', 'login') ) {
        			$Sitebill_User->logoutUser();
        			unset($_SESSION['user_id']);
        			$this->riseError('not login');
        			return $this->getAuthForm();
        		}
        	}
        }
        return true;*/
    }
    
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
     * Check auth
     * @param string $login login
     * @param string $password password
     * @return boolean
     */
    function checkAuth ( $login, $password, $rememberme=0 ) {
    	//$Sitebill_User=Sitebill_User::getInstance();
    	$rememberme=(int)$rememberme;
    	
	    require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/permission/permission.php');
	    $permission = new Permission();
	    
        $password = md5($password);
        $DBC=DBC::getInstance();
        //$sql = "SELECT user_id, group_id FROM re_user WHERE login=? AND password=?";
        $sql = 'SELECT u.login, u.user_id, u.fio, u.group_id, g.system_name, g.name FROM '.DB_PREFIX.'_user u LEFT JOIN '.DB_PREFIX.'_group g USING(group_id) WHERE (u.login=? OR u.email=?) AND u.password=?';
        //$sql = 'SELECT u.login, u.user_id, u.fio, u.group_id, g.system_name, g.name, u.`auth_salt` FROM '.DB_PREFIX.'_user u LEFT JOIN '.DB_PREFIX.'_group g USING(group_id) WHERE (u.login=? OR u.email=?) AND u.password=?';
        
        $stmt=$DBC->query($sql, array($login, $login, $password));
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	if ( $ar['user_id'] > 0 ) {
        		if ( !$permission->get_access($ar['user_id'], 'admin_panel', 'login') ) {
        			return false;
        		}
        		
        		$session_key = $this->GenerateSessionKey($ar['user_id']);
        		//$session_key = md5(rand().time().$ar['user_id']);
        		//$Sitebill_User->setSessionKey($session_key);
        		$_SESSION['key'] = $session_key;
        		$id=$ar['user_id'];
        		
        		$_SESSION['user_id_value']=$id;
        		//$sql = 'SELECT system_name FROM '.DB_PREFIX.'_group WHERE group_id=(SELECT group_id FROM '.DB_PREFIX.'_user WHERE login=?)';
        		//$stmt=$DBC->query($sql, array($login));
        		//$ar=$DBC->fetch($stmt);
        		$_SESSION['user_id']=$ar['user_id'];
        		$_SESSION['current_user_group_id']=$ar['group_id'];
        		$_SESSION['current_user_group_name']=$ar['system_name'];
        		$_SESSION['current_user_name']=$ar['fio'];
        		
        		if($this->hard_mode){
        			$auth_hash=md5(rand(10000, 99999));
        			$sql = 'UPDATE '.DB_PREFIX.'_user SET `auth_hash`=? WHERE `user_id`=? ';
        			$stmt=$DBC->query($sql, array($auth_hash, $id));
        		}
        		/**/
        		/*
        		$_SESSION['Sitebill_User']=array();
        		$_SESSION['Sitebill_User']['name']=$ar['fio'];
        		$_SESSION['Sitebill_User']['group_id']=$ar['group_id'];
        		$_SESSION['Sitebill_User']['group_name']=$ar['name'];
        		$_SESSION['Sitebill_User']['login']=$ar['login'];
        		$_SESSION['Sitebill_User']['user_id']=(int)$ar['user_id'];
        		$_SESSION['Sitebill_User']['group_system_name']=$ar['system_name'];
        		$_SESSION['Sitebill_User']['auth_time']=date('Y-m-d H:i:s', time());
        		*/
        		if($rememberme==1){
        			
        			
        			$sql = 'SELECT `auth_salt` FROM '.DB_PREFIX.'_user WHERE user_id=?';
        			$stmt=$DBC->query($sql, array($id));
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
        					$stmt=$DBC->query($sql, array($auth_salt, $id));
        				}
        				
        				$str=md5($id.' '.$_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_USER_AGENT'].' '.$auth_salt);
        			}
        			setcookie('logged_user_id', $id, time()+60*60*24*5, '/', self::$_cookiedomain);
        			setcookie('logged_user_token', $str, time()+60*60*24*5, '/', self::$_cookiedomain);
        		}
        		return true;
        	}
        }
        
        return false;
    }
    
    /**
     * Check auth
     * @param string $login login
     * @param string $password password
     * @return boolean
     */
    function checkRemoteAuth ( $login, $password ) {
        $password = md5($password);
        if ( $login != '' and $password != '' ) {
            $response = file_get_contents('http://www.sitebill.ru/auth.php?login='.$login.'&password='.$password);
            $auth_status = explode(':', $response);
            //echo '<pre>';
            //print_r($auth_status);
            //echo '</pre>';
            if ( $auth_status[0] == 'error' ) {
                $this->riseError($auth_status[1]);
                return false;
            }
            if ( $auth_status[5] != 'nanoadmin' ) {
                $this->riseError('Доступ в панель управления запрещен');
                return false;
            }
            if ( $auth_status[0] == 'success' ) {
                $_SESSION['user_id'] = $auth_status[1];
                $_SESSION['key'] = $auth_status[3];
                $_SESSION['group'] = $auth_status[5];
                //print_r($_SESSION);
                return true; 
            }
        }
        $this->riseError('not login');
        unset($_SESSION['user_id']);
        return false;
    }
    
    /**
     * Get auth form
     * @param
     * @return
     */
    function getAuthForm () {
    	
    	global $smarty;
    	if ( $this->isDemo() ) {
    		$smarty->assign('ntext', 'login: admin, password: admin');
    	}
    	if ( $this->getConfigValue('ajax_auth_form') ) {
    		$rs .= $this->get_ajax_auth_form();
    	} else {
    		$smarty->assign('formbody', $this->get_simple_auth_form('/admin/', false, false));
    		//$rs .= $this->get_simple_auth_form('/admin/', false, false);
    	}
    	return $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/system/template/auth_page.tpl');
    	/*
        $rs .= '
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset='.SITE_ENCODING.'">
	<link href="http://www.sitebill.ru/css/nano.css" rel="stylesheet" type="text/css" />
    <script src="'.SITEBILL_MAIN_URL.'/js/jquery.js"></script>
	<script src="http://www.sitebill.ru/js/nanoapi.js"></script>
	<script src="http://www.sitebill.ru/js/nanoapi_beta.js"></script>
    <link rel=stylesheet type="text/css" href="'.SITEBILL_MAIN_URL.'/css/style.css">
</head>
<body>
        <p>&nbsp;</p>        
                                    <table border="0" cellpadding="0" cellspacing="0" align="center">
                                        <tr>
                                            <td class="special">
                                            	<div id="admin_area">
                                                <h1>Авторизация</h1><br>
        ';
        if ( $this->isDemo() ) {
        	$rs .= 'login: admin, password: admin';
        }
        
        if ( $this->getConfigValue('ajax_auth_form') ) {
            $rs .= $this->get_ajax_auth_form();
        } else {
            $rs .= $this->get_simple_auth_form('/admin/', false, false);
        }
        $rs .= '
                                        </div>    
        								</td>
                                        </tr>
                                    </table>
</body>
</html>
        ';
        return $rs;
        */
    }
    
    
}

$sitebill_auth = new Sitebill_Auth();
$sitebill_auth->main();
if ( $sitebill_auth->getError() ) {
    echo $sitebill_auth->getAuthForm();
    exit;
}

?>
