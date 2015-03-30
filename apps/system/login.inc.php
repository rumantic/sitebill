<?php
class Sitebill_Auth extends SiteBill {
    /**
     * Constructor
     */
    function Sitebill_Auth() {
        $this->SiteBill();
    }
    
    function restoreUser(){
    	if((int)$_COOKIE["logged_user_id"]>0 && $_COOKIE["logged_user_token"]!='' && md5((int)$_COOKIE["logged_user_id"].' '.$_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_USER_AGENT'])==$_COOKIE["logged_user_token"]){
    		$DBC=DBC::getInstance();
    		$query='SELECT login, password FROM '.DB_PREFIX.'_user WHERE user_id='.(int)$_COOKIE["logged_user_id"].' LIMIT 1';
    		$stmt=$DBC->query($query);
    		if($stmt){
    			$ar=$DBC->fetch($stmt);
    			require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/permission/permission.php');
    			$permission = new Permission();
    			 
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
    					$session_key = md5(rand().time().$ar1['user_id']);
    					$_SESSION['key'] = $session_key;
    					$id=$ar1['user_id'];
    					$_SESSION['user_id_value']=$id;
    					$query='SELECT system_name FROM '.DB_PREFIX.'_group WHERE group_id=(SELECT group_id FROM '.DB_PREFIX.'_user WHERE login=?)';
    					$stmt=$DBC->query($query, array($login));
    					$ar=$DBC->fetch($stmt);
    					$_SESSION['current_user_group_name']=$ar['system_name'];
    					return true;
    				}
    			}
    			
    			return false;
    			
    		}
    		return true;
    	}else{
    		return false;
    	}
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
    				if ( $this->checkAuth($this->getRequestValue('login'), $this->getRequestValue('password'), $this->getRequestValue('rememberme')) ) {
    					$_SESSION['user_id'] = 'true';
    					return true;
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
        $sql = 'SELECT u.login, u.user_id, u.fio, u.group_id, g.system_name, g.name FROM '.DB_PREFIX.'_user u LEFT JOIN '.DB_PREFIX.'_group g USING(group_id) WHERE u.login=? AND u.password=?';
        
        $stmt=$DBC->query($sql, array($login, $password));
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	
        	
        	//$this->db->exec($sql);
        	//$this->db->fetch_assoc();
        	if ( $ar['user_id'] > 0 ) {
        		if ( !$permission->get_access($ar['user_id'], 'admin_panel', 'login') ) {
        			return false;
        		}
        		//$Sitebill_User->loginUser($this->db->row['user_id']);
        		$session_key = md5(rand().time().$ar['user_id']);
        		//$Sitebill_User->setSessionKey($session_key);
        		$_SESSION['key'] = $session_key;
        		$id=$ar['user_id'];
        			
        		$_SESSION['user_id_value']=$id;
        		//$sql = 'SELECT system_name FROM '.DB_PREFIX.'_group WHERE group_id=(SELECT group_id FROM '.DB_PREFIX.'_user WHERE login=?)';
        		//$stmt=$DBC->query($sql, array($login));
        		//$ar=$DBC->fetch($stmt);
        			
        		$_SESSION['current_user_group_name']=$ar['system_name'];
        		$_SESSION['current_user_name']=$ar['fio'];
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
        			$str=md5($_SESSION['user_id_value'].' '.$_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_USER_AGENT']);
        			setcookie('logged_user_id', $id, time()+60*60*24*5);
        			setcookie('logged_user_token', $str, time()+60*60*24*5);
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
