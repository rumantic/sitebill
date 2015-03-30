<?php
/**
 * Login using email-address
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class Login_Email extends Login {
	
	/**
	 * Main
	 * @param void
	 * @return string
	 */
	function main () {
		global $init;
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
				$this->checkLogin(  $init->getValue('email'), $init->getValue('password')  );
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
	
	/**
	 * Check login
	 * @param string $email email-address
	 * @param string $password password
	 * @return boolean
	 */
	function checkLogin ( $email, $password ) {
		if ( $_SESSION['user_id'] > 0 ) {
			return true;
		}
		if ( $this->getConfigValue('ajax_auth_form') ) {
			$this->riseError('not login');
			unset($_SESSION['user_id']);
			return false;
		} else {
			if ( $email != '' and $password != '' ) {
				$query = "SELECT user_id, fio FROM ".DB_PREFIX."_user WHERE email='".$email."' and password='".md5($password)."'".(1==$this->getConfigValue('use_registration_email_confirm') ? ' AND active=1' : '');
				//echo $query;
				$this->db->exec($query);
				$this->db->fetch_assoc();
				if ( $this->db->row['user_id'] != '' ) {
					$session_key = $this->GenerateSessionKey($this->db->row['user_id']);
					$this->setSessionKey( $session_key );
					$this->setUserId($this->db->row['user_id']);
					$_SESSION['user_id']=$this->db->row['user_id'];
					$_SESSION['current_user_name']=$this->db->row['fio'];
					$this->db->exec('SELECT system_name FROM '.DB_PREFIX.'_group WHERE group_id=(SELECT group_id FROM '.DB_PREFIX.'_user WHERE login=\''.$login.'\')');
					$this->db->fetch_assoc();
					$_SESSION['current_user_group_name']=$this->db->row['system_name'];
						
					return true;
				}
				$this->riseError(Multilanguage::_('L_ERROR_EMAIL_PASS'));
				return false;
			}
			$this->riseError('not login');
			unset($_SESSION['user_id']);
			return false;
		}
	}
	
	/**
	 * Get simple auth form
	 * @param string $action
	 * @param boolean $register
	 * @param boolean $remind
	 * @return string
	 */
	function get_simple_auth_form ( $action = '/login/', $register = true, $remind = true ) {
		if ( SITEBILL_MAIN_URL != '' ) {
			$add_folder = '/'.SITEBILL_MAIN_URL;
		}
			$rs .= '<form method="post" action="'.SITEBILL_MAIN_URL.$action.'">';
			$rs .= '';
			$rs .= '<table border="0">';
			if ( $this->getError() and $this->GetErrorMessage() != 'not login' ) {
				$rs .= '<tr>';
				$rs .= '<td colspan="2"><span class="error">'.$this->GetErrorMessage().'</span></td>';
				$rs .= '</tr>';
			}
			 
			 
			$rs .= '<tr>';
			$rs .= '<td class="special">'.Multilanguage::_('L_AUTH_EMAIL').' </td>';
			$rs .= '<td class="special"><input type="text" name="email" id="email"></td>';
			$rs .= '</tr>';
			 
			$rs .= '<tr>';
			$rs .= '<td class="special">'.Multilanguage::_('L_AUTH_PASSWORD').' </td>';
			$rs .= '<td class="special"><input type="password" name="password" id="password"></td>';
			$rs .= '</tr>';
			$rs .= '<tr>';
			$rs .= '<td class="special">';
			 
			if ( $register ) {
				$rs .= '<a href="'.SITEBILL_MAIN_URL.'/register/">'.Multilanguage::_('L_AUTH_REGISTRATION').'</a>';
			}
			if ( $remind ) {
				$rs .= '<br><a href="'.SITEBILL_MAIN_URL.'/remind/">'.Multilanguage::_('L_AUTH_FORGOT_PASS').'</a>';
			}
			 
			$rs .= '</td>';
			$rs .= '<td class="special"><input type="submit" value="'.Multilanguage::_('L_AUTH_ENTER').'"></td>';
			$rs .= '</tr>';
			$rs .= '</table>';
			$rs .= '<input type="hidden" name="do" value="login">';
			$rs .= '</form>';
	
		return $rs;
	}
}