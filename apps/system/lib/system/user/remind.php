<?php
/**
 * @File: remind.php
 * @Author: Kondin Dmitry
 * @Date: 15.05.06 10:03
 * @Description: Class library for remind password
 */
class Remind extends User_Object {
      /**
       * Constructor of the class
       * @param void
       * @return void
       */
	function Remind () {
      	$this->SiteBill();
	}
      
      /**
       * Main
       */
	function main () {
		$this->template->assign('title', Multilanguage::_('PASSWORD_RECOVERY'));
		$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/system/template/user.remind.tpl';
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/template/user.remind.tpl')){
			$tpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/template/user.remind.tpl';
		}
		$this->template->assign('main_file_tpl', $tpl);
		if(isset($_POST['submit']) && !isset($_REQUEST['recovery_code'])){
      		$login=trim($this->getRequestValue('login'));
      		$email=trim($this->getRequestValue('email'));
      		
      		if($login=='' && $email==''){
      			$this->template->assign('error_msg', Multilanguage::_('NO_SUCH_USER','system'));
      			$this->getForm();
      		}else{
      			$user_array=$this->getUserId($login, $email);
      			
      			
      			if($user_array){
      				$code=$this->addPasswordRecovery($user_array['user_id']);
      				$message=sprintf(Multilanguage::_('REMIND_PASSWORD_BODY','system'),$_SERVER['HTTP_HOST'],'<a href="http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/remind/?recovery_code='.$code.'">http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL.'/remind/?recovery_code='.$code.'</a>');
      			
      				$subject = sprintf(Multilanguage::_('REMIND_PASSWORD_TITLE','system'),$_SERVER['HTTP_HOST']);
      				$to = trim($user_array['email']);
      				$from = $this->getConfigValue('order_email_acceptor');
      				$this->sendFirmMail($to, $from, $subject, $message);
      				$fto=array();
      				$fto=explode('@', $to);
      				if(isset($fto[0])){
      					$str11=substr($fto[0], 0, 2);
      					$str12=substr($fto[0], -1);
      					$fto[0]=$str11.'***'.$str12;
      				}
      				if(isset($fto[1])){
      					$str11=substr($fto[1], 0, 2);
      					$str12=substr($fto[1], -1);
      					$fto[1]=$str11.'***'.$str12;
      				}
      				$this->template->assign('success_msg', sprintf(Multilanguage::_('REMIND_INSTRUCTION','system'), implode('@', $fto)));
      				$this->getRecoveryForm();
      				$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/system/template/user.remind.tpl';
      				
      			}else{
      				$this->template->assign('error_msg', Multilanguage::_('NO_SUCH_USER','system'));
      				$this->getForm();
      			}
      		}
      		
      	}elseif(isset($_REQUEST['recovery_code'])){
      		//echo 'recovery<br>';
      		$user_id=$this->checkRecoveryCode($this->getRequestValue('recovery_code'));
      		//echo 'user_id = '.$user_id.'<br>';
      		if($user_id!=0){
      			$login = $this->getLoginByUserID($user_id);    
      			$email = $this->getEmail($user_id);
      			if ( $login == '' ) {
      				$login = $email;
      			}
      			    
      			$new_password=Sitebill::genPassword(6);
      			$this->updatePassword($user_id, $new_password);
      			$message = sprintf(Multilanguage::_('NEW_PASSWORD_ASC_BODY','system'), $login, $new_password, 'http://'.$_SERVER['HTTP_HOST'].SITEBILL_MAIN_URL);
				$subject = sprintf(Multilanguage::_('NEW_PASSWORD_ASC_TITLE','system'), $_SERVER['HTTP_HOST']);
				
				$to = $this->getEmail($user_id);
				$from = $this->getConfigValue('order_email_acceptor');
				$this->sendFirmMail($to, $from, $subject, $message);
				$this->removePasswordRecovery($user_id, $this->getRequestValue('recovery_code'));
				$fto=array();
				$fto=explode('@', $to);
				if(isset($fto[0])){
					$str11=substr($fto[0], 0, 2);
					$str12=substr($fto[0], -1);
					$fto[0]=$str11.'***'.$str12;
				}
				if(isset($fto[1])){
					$str11=substr($fto[1], 0, 2);
					$str12=substr($fto[1], -1);
					$fto[1]=$str11.'***'.$str12;
				}
				$this->template->assign('success_msg', sprintf(Multilanguage::_('NEW_PASS_ON_POST','system'), implode('@', $fto)));
				
      		}else{
      			$rs = $this->getForm();
      		}
      	}else{
      		$this->getForm();
      	}
      	
      }
      

      /**
       * Update password
       * @param int $user_id user id
       * @param string $password password
       * @return mixed
	   */
      function updatePassword ( $user_id, $password ) {
          $query = 'UPDATE '.DB_PREFIX.'_user SET `password`=? WHERE `user_id`=?';
          $DBC=DBC::getInstance();
          $stmt=$DBC->query($query, array(md5($password), $user_id));
          return true;
      }
      
   
      /**
       * Get form
       * @param void
       * @return string
       */
		function getForm () {
	      	
	      	if ( 1==$this->getConfigValue('email_as_login') ) {
	      		$this->template->assign('email_as_login', 1);
	      	}
	      	$this->template->assign('remind_href', SITEBILL_MAIN_URL.'/remind'.Sitebill::$_trslashes);
	      	$ftpl=SITEBILL_DOCUMENT_ROOT.'/apps/system/template/user.remind.form.tpl';
	      	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/template/user.remind.form.tpl')){
	      		$ftpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/template/user.remind.form.tpl';
	      	}
	      	$this->template->assign('remind_form', $ftpl);
	      	//$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/system/template/user.remind.tpl';
	      	//$this->template->assign('main_file_tpl', $tpl);
		}
		
		function getRecoveryForm () {
			$this->template->assign('recovery_href', SITEBILL_MAIN_URL.'/remind'.Sitebill::$_trslashes);
			$ftpl=SITEBILL_DOCUMENT_ROOT.'/apps/system/template/user.remind.recoveryform.tpl';
			if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/template/user.remind.recoveryform.tpl')){
				$ftpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/template/user.remind.recoveryform.tpl';
			}
			$this->template->assign('recovery_form', $ftpl);
		}
      
    
      function getUserId($login,$email){
      	$id=0;
      	if($login==''){
      		return false;
      	}
      	$query="SELECT user_id, email FROM ".DB_PREFIX."_user WHERE login=? OR email=?";
      	$DBC=DBC::getInstance();
      	$stmt=$DBC->query($query, array($login, $login));
      	if($stmt){
      		$ar=$DBC->fetch($stmt);
      		if($ar['user_id'] > 0){
      			$id=(int)$ar['user_id'];
      			$ra['user_id'] = $id;
      			$ra['email'] = $ar['email'];
      			return $ra;
      		}
      	}
      	return false;
      }
      
      function addPasswordRecovery($user_id){
      	$code=md5(time());
      	$query='INSERT INTO '.DB_PREFIX.'_password_recovery (`user_id`, `recovery_code`) VALUES (?, ?)';
      	$DBC=DBC::getInstance();
      	$stmt=$DBC->query($query, array($user_id, $code));
      	return $code;
      }
      
      function removePasswordRecovery($user_id, $code){
      	$query='DELETE FROM '.DB_PREFIX.'_password_recovery WHERE `user_id`=? AND `recovery_code`=?';
      	$DBC=DBC::getInstance();
      	$stmt=$DBC->query($query, array($user_id, $code));
      	return;
      }
      
	function checkRecoveryCode($code){
		$id=0;
		$query='SELECT `user_id` FROM '.DB_PREFIX.'_password_recovery WHERE `recovery_code`=?';
      	$DBC=DBC::getInstance();
      	$stmt=$DBC->query($query, array($code));
      	
      	if($stmt){
      		$ar=$DBC->fetch($stmt);
      		$id=(int)$ar['user_id'];
      	}
      	return $id;
	}
}