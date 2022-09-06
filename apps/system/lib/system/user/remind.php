<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * @File: remind.php
 * @Author: Kondin Dmitry
 * @Date: 15.05.06 10:03
 * @Description: Class library for remind password
 */
class Remind extends User_Object
{

    /**
     * Constructor of the class
     * @param void
     * @return void
     */
    function Remind()
    {
        parent::__construct();
    }


    function ajax()
    {
        if (!$this->getConfigValue('allow_remind_password')) {
            $resp = array(
                'status' => 0,
                'msg' => 'Функция восстановления пароля отключена администратором'
            );
        } else {
            $resp = array(
                'status' => 0,
                'msg' => ''
            );
            if ('post' === strtolower($_SERVER['REQUEST_METHOD'])) {
                $login = trim($this->getRequestValue('login'));
                $email = trim($this->getRequestValue('email'));
                if ($login == '' && $email == '') {
                    $resp['msg'] = Multilanguage::_('NO_SUCH_USER', 'system');
                } else {
                    if ($login == '') {
                        $login = $email;
                    }
                    if ($this->getConfigValue('email_as_login')) {
                        $user_array = $this->getUserIdByEmail($login);
                    } else {
                        $user_array = $this->getUserId($login, $email);

                    }
                    if ($user_array) {
                        $code = $this->addPasswordRecovery($user_array['user_id']);
                        $message = sprintf(Multilanguage::_('REMIND_PASSWORD_BODY', 'system'), $_SERVER['HTTP_HOST'], '<a href="' . $this->getServerFullUrl() . '/remind/?recovery_code=' . $code . '">' . $this->getServerFullUrl() . '/remind/?recovery_code=' . $code . '</a>');
                        $message .= '<br>' . _e('Код доступа к смене пароля:') . ' <strong>' . $code . '</strong>';

                        $subject = sprintf(Multilanguage::_('REMIND_PASSWORD_TITLE', 'system'), $_SERVER['HTTP_HOST']);
                        $to = trim($user_array['email']);
                        $from = $this->getConfigValue('order_email_acceptor');
                        $this->sendFirmMail($to, $from, $subject, $message);
                        $fto = array();
                        $fto = explode('@', $to);
                        if (isset($fto[0])) {
                            $str11 = substr($fto[0], 0, 2);
                            $str12 = substr($fto[0], -1);
                            $fto[0] = $str11 . '***' . $str12;
                        }
                        if (isset($fto[1])) {
                            $str11 = substr($fto[1], 0, 2);
                            $str12 = substr($fto[1], -1);
                            $fto[1] = $str11 . '***' . $str12;
                        }

                        $resp['msg'] = sprintf(Multilanguage::_('REMIND_INSTRUCTION', 'system'), implode('@', $fto));
                        $resp['status'] = 1;

                    } else {
                        $resp['msg'] = Multilanguage::_('NO_SUCH_USER', 'system');
                    }
                }
            }
        }

        return json_encode($resp);
    }

    /**
     * Main
     */
    function main()
    {
        $this->template->assign('title', _e('Восстановление пароля'));
        $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/system/template/user.remind.tpl';
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/template/user.remind.tpl')) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/template/user.remind.tpl';
        }
        $this->template->assign('main_file_tpl', $tpl);
        if (isset($_POST['submit']) && !isset($_REQUEST['recovery_code'])) {
            $login = trim($this->getRequestValue('login'));
            $email = trim($this->getRequestValue('email'));
            $mobile = trim($this->getRequestValue('mobile'));
            $this->get_user_and_remind($login, $email, $mobile);
        } elseif (isset($_REQUEST['recovery_code'])) {
            $this->process_remind_code($this->getRequestValue('recovery_code'));
        } else {
            $this->getForm();
        }
    }

    function getMobile ( $user_id ) {
        $query = "select mobile from ".DB_PREFIX."_user where user_id=?";
        $DBC=DBC::getInstance();
        $stmt=$DBC->query($query, array($user_id));
        if($stmt){
            $ar=$DBC->fetch($stmt);
            return $ar['mobile'];
        }
        return false;
    }

    function process_remind_code_by_mobile($user_id) {
        if ($user_id != 0) {
            $mobile = $this->getMobile($user_id);

            $new_password = rand(10000, 99999);
            $this->updatePassword($user_id, $new_password);

            if ( file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/sms/admin/admin.php') ) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/sms/admin/admin.php';
                $sms_admin = new sms_admin();
                $message = 'New password: '.$new_password;
                $sms_result = $sms_admin->send($mobile, $message);
                if ( $sms_result ) {
                    $this->activateUser($user_id);
                    $success_msg = _e('New password send to mobile phone: ').$mobile;
                    $this->template->assign('success_msg', $success_msg);
                    return $success_msg;
                } else {
                    $this->template->assign('error_msg', _e('Error on sending sms message'));
                    $this->getForm();
                    $this->riseError(_e('Error on sending sms message'));
                    return false;
                }
            }
            return true;

        } else {
            $this->template->assign('error_msg', _e('Неверный код'));
            $this->getRecoveryForm();
            $this->riseError(_e('Код указан неверно'));
        }
        return false;
    }

    function process_remind_code($recovery_code)
    {
        //echo 'recovery<br>';
        $user_id = $this->checkRecoveryCode($recovery_code);

        if ( strlen($recovery_code) < 5 and $this->getConfigValue('apps.sms.allow_sms_register') ) {
            return $this->process_remind_code_by_mobile($user_id);
        }

        //echo 'user_id = '.$user_id.'<br>';
        if ($user_id != 0) {
            if ($this->getConfigValue('email_as_login')) {
                $email = $this->getEmail($user_id);
                $login = $email;
            } else {
                $login = $this->getLoginByUserID($user_id);
                $email = $this->getEmail($user_id);
                if ($login == '') {
                    $login = $email;
                }
            }

            $new_password = Sitebill::genPassword(6);
            $this->updatePassword($user_id, $new_password);

            $message = sprintf(Multilanguage::_('NEW_PASSWORD_ASC_BODY', 'system'), $login, $new_password, $this->getServerFullUrl());
            $subject = sprintf(Multilanguage::_('NEW_PASSWORD_ASC_TITLE', 'system'), $_SERVER['HTTP_HOST']);

            $to = $email;
            $from = $this->getConfigValue('system_email');
            $this->sendFirmMail($to, $from, $subject, $message);
            $this->removePasswordRecovery($user_id, $this->getRequestValue('recovery_code'));
            $fto = array();
            $fto = explode('@', $to);
            if (isset($fto[0])) {
                $str11 = substr($fto[0], 0, 2);
                $str12 = substr($fto[0], -1);
                $fto[0] = $str11 . '***' . $str12;
            }
            if (isset($fto[1])) {
                $str11 = substr($fto[1], 0, 2);
                $str12 = substr($fto[1], -1);
                $fto[1] = $str11 . '***' . $str12;
            }
            $success_msg = sprintf(Multilanguage::_('NEW_PASS_ON_POST', 'system'), implode('@', $fto));
            $this->template->assign('success_msg', $success_msg);
            return $success_msg;
        } else {
            $rs = $this->getForm();
            $this->riseError(_e('Код указан неверно'));
        }
        return false;
    }

    function recoveryByMobile($mobile) {
        $user_array = $this->getUserIdByMobile($mobile);
        if ($user_array) {
            $code = $this->addPasswordRecovery($user_array['user_id'], true);
            $message = 'Your recovery code: '.$code;
            $to = trim($user_array['mobile']);

            if ( file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/sms/admin/admin.php') ) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/sms/admin/admin.php';
                $sms_admin = new sms_admin();
                $sms_result = $sms_admin->send($to, $message);
                if ( $sms_result ) {
                    $this->template->assign('success_msg', _e('Recovery code send to: ').$mobile);
                    $this->getRecoveryForm();
                    return true;
                } else {
                    $this->template->assign('error_msg', _e('Error on sending sms message'));
                    $this->getForm();
                    $this->riseError(_e('Error on sending sms message'));
                    return false;
                }
            }
            return true;
        } else {
            $this->template->assign('error_msg', Multilanguage::_('NO_SUCH_USER', 'system'));
            $this->getForm();
            $this->riseError(Multilanguage::_('NO_SUCH_USER', 'system'));
            return false;

        }
        return true;
    }

    function get_user_and_remind($login, $email, $mobile = '')
    {
        if ( $mobile != '' ) {
            return $this->recoveryByMobile($mobile);
        }

        if ($login == '' && $email == '') {
            $this->template->assign('error_msg', Multilanguage::_('NO_SUCH_USER', 'system'));
            $this->riseError(Multilanguage::_('NO_SUCH_USER', 'system'));
            $this->getForm();
            return false;
        } else {

            if ($this->getConfigValue('email_as_login')) {
                $user_array = $this->getUserIdByEmail($login);
            } else {
                $user_array = $this->getUserId($login, $email);
            }


            if ($user_array) {
                $code = $this->addPasswordRecovery($user_array['user_id']);
                $message = sprintf(Multilanguage::_('REMIND_PASSWORD_BODY', 'system'), $_SERVER['HTTP_HOST'], '<a href="' . $this->getServerFullUrl() . '/remind/?recovery_code=' . $code . '">' . $this->getServerFullUrl() . '/remind/?recovery_code=' . $code . '</a>');
                $message .= '<br>' . _e('Код доступа к смене пароля:') . ' <strong>' . $code . '</strong>';

                $subject = sprintf(Multilanguage::_('REMIND_PASSWORD_TITLE', 'system'), $_SERVER['HTTP_HOST']);
                $to = trim($user_array['email']);
                $from = $this->getConfigValue('system_email');
                $this->sendFirmMail($to, $from, $subject, $message);
                $fto = array();
                $fto = explode('@', $to);
                if (isset($fto[0])) {
                    $str11 = substr($fto[0], 0, 2);
                    $str12 = substr($fto[0], -1);
                    $fto[0] = $str11 . '***' . $str12;
                }
                if (isset($fto[1])) {
                    $str11 = substr($fto[1], 0, 2);
                    $str12 = substr($fto[1], -1);
                    $fto[1] = $str11 . '***' . $str12;
                }
                $this->template->assign('success_msg', sprintf(Multilanguage::_('REMIND_INSTRUCTION', 'system'), implode('@', $fto)));
                $this->getRecoveryForm();
                $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/system/template/user.remind.tpl';
                return sprintf(Multilanguage::_('REMIND_INSTRUCTION', 'system'), implode('@', $fto));
            } else {
                $this->template->assign('error_msg', Multilanguage::_('NO_SUCH_USER', 'system'));
                $this->getForm();
                $this->riseError(Multilanguage::_('NO_SUCH_USER', 'system'));
                return false;

            }
        }
        return true;
    }

    /**
     * Update password
     * @param int $user_id user id
     * @param string $password password
     * @return mixed
     */
    function updatePassword($user_id, $password)
    {
        $query = 'UPDATE ' . DB_PREFIX . '_user SET `password`=? WHERE `user_id`=?';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array(md5($password), $user_id));
        return true;
    }

    function activateUser($user_id) {
        $query = 'UPDATE ' . DB_PREFIX . '_user SET `active`=? WHERE `user_id`=?';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array(1, $user_id));
        return true;
    }

    /**
     * Get form
     * @param void
     * @return string
     */
    function getForm()
    {

        if (1 == $this->getConfigValue('email_as_login')) {
            $this->template->assign('email_as_login', 1);
        }
        //$this->template->assign('remind_href', SITEBILL_MAIN_URL . '/remind' . Sitebill::$_trslashes);
        $this->template->assign('remind_href', $this->createUrlTpl('remind'));
        $ftpl = SITEBILL_DOCUMENT_ROOT . '/apps/system/template/user.remind.form.tpl';
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/template/user.remind.form.tpl')) {
            $ftpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/template/user.remind.form.tpl';
        }
        $this->template->assign('remind_form', $ftpl);
        //$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/system/template/user.remind.tpl';
        //$this->template->assign('main_file_tpl', $tpl);
    }

    function getRecoveryForm()
    {
        //$this->template->assign('recovery_href', SITEBILL_MAIN_URL . '/remind' . Sitebill::$_trslashes);
        $this->template->assign('recovery_href', $this->createUrlTpl('remind'));
        $ftpl = SITEBILL_DOCUMENT_ROOT . '/apps/system/template/user.remind.recoveryform.tpl';
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/template/user.remind.recoveryform.tpl')) {
            $ftpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/template/user.remind.recoveryform.tpl';
        }
        $this->template->assign('recovery_form', $ftpl);
    }

    function getUserId($login, $email)
    {
        $id = 0;
        if ($login == '') {
            return false;
        }
        $query = "SELECT user_id, email FROM " . DB_PREFIX . "_user WHERE login=? OR email=?";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($login, $login));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['user_id'] > 0) {
                $id = (int)$ar['user_id'];
                $ra['user_id'] = $id;
                $ra['email'] = $ar['email'];
                return $ra;
            }
        }
        return false;
    }

    function getUserIdByEmail($email)
    {
        $id = 0;
        if ($email == '') {
            return false;
        }
        $query = "SELECT user_id, email FROM " . DB_PREFIX . "_user WHERE email=?";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($email));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['user_id'] > 0) {
                $id = (int)$ar['user_id'];
                $ra['user_id'] = $id;
                $ra['email'] = $ar['email'];
                return $ra;
            }
        }
        return false;
    }

    function getUserIdByMobile($mobile)
    {
        $id = 0;
        if ($mobile == '') {
            return false;
        }
        $query = "SELECT user_id, mobile FROM " . DB_PREFIX . "_user WHERE mobile=?";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($mobile));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['user_id'] > 0) {
                $id = (int)$ar['user_id'];
                $ra['user_id'] = $id;
                $ra['mobile'] = $ar['mobile'];
                return $ra;
            }
        }
        return false;
    }

    function addPasswordRecovery($user_id, $by_sms = false)
    {
        if ( $by_sms ) {
            $code = rand(1000, 9999);
        } else {
            $code = md5(time());
        }
        $query = 'INSERT INTO ' . DB_PREFIX . '_password_recovery (`user_id`, `recovery_code`) VALUES (?, ?)';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($user_id, $code));
        return $code;
    }

    function removePasswordRecovery($user_id, $code)
    {
        $query = 'DELETE FROM ' . DB_PREFIX . '_password_recovery WHERE `user_id`=? AND `recovery_code`=?';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($user_id, $code));
        return;
    }

    function checkRecoveryCode($code)
    {
        $id = 0;
        $query = 'SELECT `user_id` FROM ' . DB_PREFIX . '_password_recovery WHERE `recovery_code`=?';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($code));

        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $id = (int)$ar['user_id'];
        }
        return $id;
    }

}
