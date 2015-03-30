<?php
/**
 * User registration class
 * @author Kondin Dmitry <kondin@etown.ru>
 */
class Register extends Login {
    /**
     * Constructor
     */
    function Register () {
        $this->SiteBill();
    }

    /**
     * Main
     * @param void
     * @return string
     */
    function main () {
        global $init;
        global $config;
        global $smarty;
        
        if ( $this->getSessionUserId() > 0 ) {
            $rs = $this->alreadyLogin();
            return $rs;
        }
        
        switch ( $init->getValue('do', 'default') ) {
        	case 'activate' : {
        		$activation_code=$this->getRequestValue('activation_code');
        		$email=$this->getRequestValue('email');
        		$q="SELECT COUNT(*) AS cnt FROM ".DB_PREFIX."_user WHERE email='".$email."' AND pass='".$activation_code."'";
        		$this->db->exec($q);
        		$this->db->fetch_assoc();
        		if($this->db->row['cnt']!=0){
        			$q="UPDATE ".DB_PREFIX."_user SET active=1 WHERE email='".$email."' AND pass='".$activation_code."'";
        			$this->db->exec($q);
        			$rs=Multilanguage::_('ACCOUNT_ACTIVATED','system');
        			
        			if(1==$this->getConfigValue('registration_notice')){
        				/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
        				$mailer = new Mailer();*/
        				if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/template/register_email_notify_complete.tpl.html') ) {
        					
        					$q="SELECT * ".DB_PREFIX."_user WHERE email='".$email."'";
        					$this->db->exec($q);
        					$this->db->fetch_assoc();
        					 
        					$smarty->assign('user_name', $this->db->row['fio']);
        					$smarty->assign('login', $this->db->row['login']);
        					$smarty->assign('password', 'тот что указывали при регистрации, из соображений безопасности не отображается');
        					$smarty->assign('email_signature', $this->getConfigValue('email_signature'));
        					 
        					$message = $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/template/register_email_notify_complete.tpl.html');
        				} else {
        					$message = Multilanguage::_('NEW_REGISTER_BODY_TRIMMED','system');
        				}
        				$subject = sprintf(Multilanguage::_('NEW_REGISTER_TITLE','system'), $_SERVER['HTTP_HOST']);
        				 
        				$to = $init->getValue('email');
        				$from = $this->getConfigValue('order_email_acceptor');
        				/*if ( $this->getConfigValue('use_smtp') ) {
        					$mailer->send_smtp($to, $from, $subject, $message, 1);
        				} else {
        					$mailer->send_simple($to, $from, $subject, $message, 1);
        				}*/
        				$this->sendFirmMail($to, $from, $subject, $message);
        			}
        			
        		}else{
        			$rs=Multilanguage::_('ACTIVATION_ERROR','system');
        		}
        		return $rs;
        	}
            case 'register':
            	if(!preg_match('/^([a-zA-Z0-9-_]*)$/', $init->getValue('login'))){
            		$rs = 'Логин может содержать только латинские буквы, цифры, подчеркивание, тире';
            		$rs .= $this->getRegisterForm();
            		return $rs;
            		//$this->riseError('Логин может содержать только латинские буквы, цифры, подчеркивание, тире');
            		//return false;
            	}
            		$new_user_id=$this->addUser($init->getValue('login'),
            				$init->getValue('password'),
            				$init->getValue('retype_password'),
            				$init->getValue('fio'),
            				$init->getValue('email'),
            				$init->getValue('captcha_string'),
            				$init->getValue('captcha_session_key')
            		);
            		if ( !$new_user_id ) {
            			$rs = $this->getRegisterForm();
            			return $rs;
            		} else {
            			
            			
            			
            			if(1==$this->getConfigValue('use_registration_email_confirm')){
            				$activation_code=md5(time().'_'.rand(100,999));
            				$this->db->exec("UPDATE ".DB_PREFIX."_user SET pass='".$activation_code."' WHERE user_id=".$new_user_id);
            				$activation_link='<a href="http://'.$_SERVER['HTTP_HOST'].'/register?do=activate&activation_code='.$activation_code.'&email='.$init->getValue('email').'">http://'.$_SERVER['HTTP_HOST'].'/register?do=activate&activation_code='.$activation_code.'&email='.$init->getValue('email').'</a>';
            				/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
            				$mailer = new Mailer();*/
            				$message = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_BODY','system'), $activation_link);
            				$subject = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_TITLE','system'), $_SERVER['HTTP_HOST']);
            				 
            				$to = $init->getValue('email');
            				$from = $this->getConfigValue('order_email_acceptor');
            				/*if ( $this->getConfigValue('use_smtp') ) {
            					$mailer->send_smtp($to, $from, $subject, $message, 1);
            				} else {
            					$mailer->send_simple($to, $from, $subject, $message, 1);
            				}*/
            				$this->sendFirmMail($to, $from, $subject, $message);
            				$rs = '<h3>'.Multilanguage::_('REGISTER_SUCCESS','system').'</h3><br>';
            				$rs.=Multilanguage::_('ACTIVATION_CODE_SENT','system');
            				return $rs;
            			}
            			 
            			if(1==$this->getConfigValue('registration_notice')){
            				/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
            				$mailer = new Mailer();*/
            				$message = sprintf(Multilanguage::_('NEW_REGISTER_BODY','system'), $init->getValue('login'), $init->getValue('password'));
            				$subject = sprintf(Multilanguage::_('NEW_REGISTER_TITLE','system'), $_SERVER['HTTP_HOST']);
            				 
            				$to = $init->getValue('email');
            				$from = $this->getConfigValue('order_email_acceptor');
            				/*if ( $this->getConfigValue('use_smtp') ) {
            					$mailer->send_smtp($to, $from, $subject, $message, 1);
            				} else {
            					$mailer->send_simple($to, $from, $subject, $message, 1);
            				}*/
            				$this->sendFirmMail($to, $from, $subject, $message);
            			}

            			if(1==$this->getConfigValue('notify_admin_about_register')){
            				/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
            				$mailer = new Mailer();*/
            				$message = 'На сайте зарегистрирован новый пользователь '.$init->getValue('login');
            				$subject = 'Новый пользователь '.$init->getValue('login').' на сайте '.$_SERVER['HTTP_HOST'];
            				 
            				$to = $this->getConfigValue('order_email_acceptor');
            				$from = $this->getConfigValue('order_email_acceptor');
            				/*if ( $this->getConfigValue('use_smtp') ) {
            					$mailer->send_smtp($to, $from, $subject, $message, 1);
            				} else {
            					$mailer->send_simple($to, $from, $subject, $message, 1);
            				}*/
            				$this->sendFirmMail($to, $from, $subject, $message);
            			}
            			 
            			 
            			 
            			
            			 
            			require_once( SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
            			$Login = new Login;
            			$Login->checkLogin($init->getValue('login'), $init->getValue('password'));
            		
            			$user_info_string = $Login->getUserInfo($Login->getUserId());
            		
            			$rs = '<h3>'.Multilanguage::_('REGISTER_SUCCESS','system').'</h3><br>';
            			$rs .= '<a href="'.SITEBILL_MAIN_URL.'/account/">'.Multilanguage::_('PRIVATE_ACCOUNT','system').'</a>';
            			return $rs;
            		}
            	
            	
            break;
            default:
                $rs = $this->getRegisterForm();
                return $rs;
        }
    }
    
    /**
     * Check captcha
     * @param string $captcha_string captcha string
     * @param string $captcha_session_key captcha session key
     * @return boolean
     */
    function checkCaptcha ( $captcha_string, $captcha_session_key ) {
        $query = "select captcha_string from ".DB_PREFIX."_captcha_session where captcha_session_key='$captcha_session_key'";
        //echo $query;
        $this->db->exec($query);
        $this->db->fetch_assoc();
        //echo $this->db->row['captcha_string'];
        if ( $this->db->row['captcha_string'] == $captcha_string ) {
            return true;
        }   
        return false;
    }
    
    /**
     * Add user
     * @param string $login login
     * @param string $password password
     * @param string $retype_password retype password
     * @param string $captcha_string captcha string
     * @param string $captcha_session_key captcha session key
     * @return boolean
     */
    function addUser ( $login, $password, $retype_password, $fio, $email, $captcha_string, $captcha_session_key ) {
        if ( !$this->checkCaptcha( $captcha_string, $captcha_session_key ) ) {
            $this->riseError(Multilanguage::_('L_ERROR_CAPTCHA_INVALID'));
            return false;
        }
        
        if ( $fio == '' ) {
            $this->riseError(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED').' '.Multilanguage::_('L_REGISTER_FIO'));
            return false;
        }
        if ( $email == '' ) {
            $this->riseError(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED').' '.Multilanguage::_('L_REGISTER_EMAIL'));
            return false;
        }
        if ( $login == '' ) {
            $this->riseError(Multilanguage::_('L_ERROR_FIELD_NOT_FILLED').' '.Multilanguage::_('L_REGISTER_LOGIN'));
            return false;
        }
        if ( strlen($password) < 5 ) {
            $this->riseError(sprintf(Multilanguage::_('MIN_PASSWORD_LENGTH','system'),'5'));
            return false;
        }
        if ( $password != $retype_password ) {
            $this->riseError(Multilanguage::_('PASSWORDS_NOT_EQUAL','system'));
            return false;
        }
        $password = md5($password);
        if ( !$this->checkLogin($login) ) {
            $group_id = $this->getGroupIdByName('realtor');
            $query = "insert into ".DB_PREFIX."_user (login, password, fio, email, account, group_id, reg_date) values ('$login', '$password', '$fio', '$email', 0, $group_id, NOW())";
            //echo $query;
            $this->db->exec($query);
            $new_user_id = $this->db->last_insert_id();
            //Add user to quality assurance project
            //$this->addUserToQualityProject($new_user_id);
            
            return $new_user_id;
        } else {
            $this->riseError(Multilanguage::_('USERNAME_EXISTS','system'));
            return false;
        }
        
    }
    
    /**
     * Get group ID by group name
     * @param string $group_name group name
     * @return integer
     */
    function getGroupIdByName ( $group_name ) {
        $query = "select group_id from ".DB_PREFIX."_group where system_name='$group_name'";
        $this->db->exec($query);
        $this->db->fetch_assoc();
        return $this->db->row['group_id'];
    }
    
    /**
     * Check login
     * @param string $login login
     * @return boolean
     */
    function checkLogin ( $login ) {
        $query = "select user_id from ".DB_PREFIX."_user where login='$login'";
        $this->db->exec($query);
        $this->db->fetch_assoc();
        if ( $this->db->row['user_id'] > 0 ) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Generate captcha session key
     * @param void
     * @return string
     */
    function generateCaptchaSessionKey () {
        return md5(time().rand(9999, 4).'random key captcha string core sitebill');
    }
    
    
    /**
     * Get register form
     * @param void
     * @return string
     */
    function getRegisterForm () {
    	global $init;
    	$social_link = false;
    	if ( $this->getConfigValue('apps.socialauth.fb.enable') ) {

    	}
    	
    	if ( $this->getConfigValue('apps.socialauth.vk.enable') ) {
    		require_once (SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/vk/vk_logger.php');
    		$VK = Vk_Logger::getInstance();
    		$social_link .= $VK->getLoginLink();
    	}
    	
        $this->clear_captcha_session_table();
        $captcha_session_key = $this->generateCaptchaSessionKey();
        $rs = '<div class="big_form">';
        
        $rs .= '<table border="0">';
        if ( $this->getError() ) {
            $rs .= '<tr><td colspan="2"><span class="error">'.$this->getErrorMessage().'</span></td></tr>';
        }
        $rs .= '<form action="'.SITEBILL_MAIN_URL.'/register/?do=register" method="post">';
        if ( $social_link ) {
        	$rs .= '<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/apps/socialauth/css/style.css" />';
        	$rs .= '<tr><td align="right">'.Multilanguage::_('LOGIN_BY','system').':</td><td> '.$social_link.'</td></tr>';
        }
        
        $rs .= '<tr><td align="right">'.Multilanguage::_('L_REGISTER_FIO').' <span class="error">*</span></td><td> <input class="register_input" type="text" name="fio" value="'.$init->getValue('fio', '').'"></td></tr>';
        $rs .= '<tr><td align="right">'.Multilanguage::_('L_REGISTER_EMAIL').' <span class="error">*</span></td><td> <input class="register_input" type="text" name="email" value="'.$init->getValue('email', '').'"></td></tr>';
        $rs .= '<tr><td align="right">'.Multilanguage::_('L_REGISTER_LOGIN').' <span class="error">*</span></td><td> <input class="register_input" type="text" name="login" value="'.$init->getValue('login', '').'"></td></tr>';
        $rs .= '<tr><td align="right">'.Multilanguage::_('L_REGISTER_PASSWORD').' <span class="error">*</span></td><td><input class="register_input" type="password" name="password"></td></tr>';
        $rs .= '<tr><td align="right">'.Multilanguage::_('L_REGISTER_RETYPE_PASSWORD').' <span class="error">*</span></td><td><input class="register_input" type="password" name="retype_password"></td></tr>';
        $rs .= '<tr><td></td><td><img src="'.SITEBILL_MAIN_URL.'/captcha.php?captcha_session_key='.$captcha_session_key.'" width="180" height="80"></td></tr>';
        $rs .= '<tr><td align="right">'.Multilanguage::_('L_CAPTCHA').' <span class="error">*</span></td><td><input type="text" class="register_input" name="captcha_string"></td></tr>';
        $rs .= '<input type="hidden" name="captcha_session_key" value="'.$captcha_session_key.'">';
        $rs .= '<tr><td></td><td><input type="submit" value="Регистрация"></td></tr>';
        $rs .= '</table>';
        $rs .= '</form>';
        $rs .= '</div>';
        return $rs;
    }
}
?>