<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Login REST class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_oauth extends API_Common {
	public function _login () {
		$login = $this->getRequestValue('login');
		$password = $this->getRequestValue('password');
		
		$this->writeLog(array('apps_name'=>'apps.api', 'method' => __METHOD__, 'message' => 'login = '.$login.', md5(password) = '.md5($password), 'type' => NOTICE));
		
		$DBC=DBC::getInstance();
		//$query = "SELECT user_id, fio, group_id FROM ".DB_PREFIX."_user WHERE login='?' and password='?'".(1==$this->getConfigValue('use_registration_email_confirm') ? ' AND active=1' : '');
		$query = 'SELECT user_id FROM '.DB_PREFIX.'_user WHERE login=? and password=?';
		
		$stmt=$DBC->query($query, array($login, md5($password)));
		
		if ( $stmt ) {
			$ar = $DBC->fetch($stmt);
			if ( $ar['user_id'] > 0 ) {
				$this->writeLog(array('apps_name'=>'apps.api', 'method' => __METHOD__, 'message' => 'login success '.var_export($ar, true), 'type' => NOTICE));
				$ar['session_key'] = $this->init_session_key($ar['user_id']);
				require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/permission/permission.php' );
				$permission = new Permission();
				if ( $permission->get_access($ar['user_id'], 'admin_panel', 'login') ) {
					$ar['admin_panel_login'] = 1;
				} else {
					$ar['admin_panel_login'] = 0;
				}
				$ar['success'] = 1;
				
				return $this->json_string($ar);
			}
		}
		$this->writeLog(array('apps_name'=>'apps.api', 'method' => __METHOD__, 'message' => 'login failed', 'type' => ERROR));
		return $this->request_failed('login failed');
	}
	
	public function _check_session_key () {
		$session_key = $this->getRequestValue('session_key');
		$DBC=DBC::getInstance();
		$query = 'SELECT user_id FROM '.DB_PREFIX.'_oauth WHERE session_key=?';
		$this->writeLog(array('apps_name'=>'apps.api', 'method' => __METHOD__, 'message' => 'check session_key, session_key = '.$session_key, 'type' => NOTICE));
		
		$stmt=$DBC->query($query, array($session_key));
		
		if ( $stmt ) {
			$ar = $DBC->fetch($stmt);
			if ( $ar['user_id'] > 0 ) {
				$this->writeLog(array('apps_name'=>'apps.api', 'method' => __METHOD__, 'message' => 'check session_key success '.var_export($ar, true), 'type' => NOTICE));
				$ar['config']['per_page'] = $this->getConfigValue('per_page');
				
				require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/permission/permission.php' );
				$permission = new Permission();
				if ( $permission->get_access($ar['user_id'], 'admin_panel', 'login') ) {
					$ar['admin_panel_login'] = 1;
				} else {
					$ar['admin_panel_login'] = 0;
				}
				
				require_once (SITEBILL_DOCUMENT_ROOT.'/apps/api/classes/class.stat.php' );
				$api_stat = new API_stat();
				$ar['city_list'] = $api_stat->_get_city_list(true);
				$ar['success'] = 1;
				
				return $this->json_string($ar);
			}
		}
		$this->riseError('check_session_key_failed');
		return $this->request_failed('check_session_key_failed');
	}
	
	
	
	private function init_session_key ( $user_id ) {
		$user_ip = $_SERVER['REMOTE_ADDR'];
		$session_key = md5(rand().time().$user_ip);
		
		$query = 'insert into '.DB_PREFIX.'_oauth (user_id, ip, session_key) values (?, ?, ?)';
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query, array($user_id, $user_ip, $session_key));
		return $session_key;
	}
}