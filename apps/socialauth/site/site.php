<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * live search fronend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class socialauth_site extends socialauth_admin {

    function getLogger($loggertype){
        $AUTH = null;
        switch($loggertype){
            case 'login_gl' :
            case 'google' : {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/gl/gl_logger.php';
                $AUTH = Gl_Logger::getInstance();
                break;
            }
            case 'login_fb' :
            case 'facebook' : {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/fb/fb_logger.php';
                $AUTH = Fb_Logger::getInstance();
                break;
            }
            case 'login_ok' :
            case 'odnoklassniki' : {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/ok/ok_logger.php';
                $AUTH = Ok_Logger::getInstance();
                break;
            }
            case 'login_vk' :
            case 'vkontakte' : {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/vk/vk_logger.php';
                $AUTH = Vk_Logger::getInstance();
                break;
            }
            case 'login_tw' :
            case 'twitter' : {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/tw/tw_logger.php';
                $AUTH = Tw_Logger::getInstance();
                break;
            }
            case 'login_tg' :
            case 'twitter' : {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/tg/tg_logger.php';
                $AUTH = Tg_Logger::getInstance();
                break;
            }
        }
        return $AUTH;
    }

    function frontend()
    {
        $REQUESTURIPATH = $this->getClearRequestURI();
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/common_logger.php';

        if ($REQUESTURIPATH == 'socialauth') {
            return false;
        } elseif ($REQUESTURIPATH == 'socialauth/register') {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/register_using_model.php');
            $Register = new Register_Using_Model();
            $Register->register_social = true;
            $rs1 = $Register->main();
            $this->template->assert('register_block', $rs1);
            $this->set_apps_template('socialauth', $this->getConfigValue('theme'), 'main_file_tpl', 'register.tpl');
            return true;
        } elseif (preg_match('/^socialauth\/login\/(google|facebook|odnoklassniki|vkontakte|twitter)/', $REQUESTURIPATH, $matches)) {
            $AUTH = $this->getLogger($matches[1]);
            if (!is_null($AUTH)) {
                $r = $AUTH->prelogin();
                return true;
            }
            /*} elseif ($REQUESTURIPATH == 'socialauth/connect/google') {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/socialauth/lib/gl/gl_logger.php';
                $AUTH = Gl_Logger::getInstance();
                $r = $AUTH->preconnect();
                return true;
            }*/
        } elseif ($REQUESTURIPATH == 'socialauth/login') {

            $r = false;
            $do = $_GET['do'];

            $AUTH = $this->getLogger($do);
            if(!is_null($AUTH)){
                $r = $AUTH->login();
                if ($r) {
                    if ($id = $this->checkExistingUser($_SESSION['ssAuthData']['ssType'], $_SESSION['ssAuthData']['id'])) {
                        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/login.php');
                        $login_object = new Login();
                        $login_object->setLoggedUser($id);
                        unset($_SESSION['ssAuthData']);
                    } else {
                        header('location: ' . SITEBILL_MAIN_URL . '/socialauth/register/?do=new_done');
                        exit();
                    }
                }
                return true;
            }

            if($r){

                if (isset($_COOKIE['back_url']) && $_COOKIE['back_url'] != '') {
                    $backUrl = $_COOKIE['back_url'];
                } else {
                    $backUrl = 'http://' . $_SERVER['HTTP_HOST'] . SITEBILL_MAIN_URL . '/';
                }
                header('location: ' . $backUrl);
                exit();
            }
            return true;
        }
        return false;
    }

    protected function checkExistingUser($ssType, $ssId) {
        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_user WHERE `' . $ssType . '_id`=?';
        $stmt = $DBC->query($query, array($ssId));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            return $ar['user_id'];
        }/* else{
          $data=$_SESSION['ssAuthData'];
          return $this->direct_add_user($data);
          } */
        return false;
    }

    /* protected function authUser($_login, $_pass, $name, $email){

      require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
      $login_object = new Login();
      $Config = new config_admin();
      if(1==intval($Config->getConfigValue('email_as_login'))){
      $l=$email;
      }else{
      $l=$_login;
      }

      if(!$login_object->checkLogin($l, $_pass)){
      $id=$login_object->direct_add_user($_login, md5($_pass), $name, $email);
      //activate account
      $DBC=DBC::getInstance();
      $query='UPDATE '.DB_PREFIX.'_user SET active=1, group_id=? WHERE user_id=?';
      $stmt=$DBC->query($query, array($Config->getConfigValue('apps.socialauth.default_group_id'), $id));


      if(1==$login_object->getConfigValue('notify_admin_about_register')){


      $message = 'На сайте зарегистрирован новый пользователь '.$_login;
      $subject = 'Новый пользователь '.$_login.' на сайте '.$_SERVER['HTTP_HOST'];

      $to = $login_object->getConfigValue('order_email_acceptor');
      $from = $login_object->getConfigValue('order_email_acceptor');

      $login_object->sendFirmMail($to, $from, $subject, $message);
      }

      $login_object->checkLogin($l, $_pass);
      return true;
      }
      } */

    /* protected function direct_add_user ($data) {

      } */
    

    
    protected function hasUserConnectedSocialId($ssType, $user_id){
        $DBC=DBC::getInstance();
        $query='SELECT `'.$ssType.'_id` AS _ssid FROM '.DB_PREFIX.'_user WHERE user_id=?';
        $stmt=$DBC->query($query, array($user_id));
        if(!$stmt){
            return false;

        }
        $ar=$DBC->fetch($stmt);
        return $ar['_ssid'];
    }
}