<?php
/**
 * RegisterVerifyTrait — activation and verification for Register_Using_Model
 *
 * Extracted methods:
 *   - ajax_activate_sms()
 *   - formatActivateSuccessMessage($msg)
 *   - formatActivateErrorMessage($msg)
 *   - _activateAction()
 */
trait RegisterVerifyTrait
{
    public function ajax_activate_sms()
    {
        $activation_code = $this->getRequestValue('activation_code');
        if ($activation_code == '') {
            return 'wrong_sms_code';
        }

        $q = "SELECT active AS cnt FROM " . DB_PREFIX . "_user WHERE pass=? LIMIT 1";

        $DBC = DBC::getInstance();
        $stmt = $DBC->query($q, array($activation_code));

        if (!$stmt) {
            return 'wrong_sms_code';
        } else {
            $ar = $DBC->fetch($stmt);
            if ((int)$ar['cnt'] == 0) {
                $q = "UPDATE " . DB_PREFIX . "_user SET active=1, pass='' WHERE pass=?";
                $stmt = $DBC->query($q, array($activation_code));
                return 'activate_success';
            }
        }
        return 'wrong_sms_code';
    }

    public function formatActivateSuccessMessage($msg)
    {
        return $msg;
    }

    public function formatActivateErrorMessage($msg)
    {
        return $msg;
    }

    protected function _activateAction()
    {
        $rs = '';
        $activation_code = $this->getRequestValue('activation_code');
        $email = $this->getRequestValue('email');
        $q = "SELECT active AS cnt, user_id FROM " . DB_PREFIX . "_user WHERE email=? AND pass=? LIMIT 1";

        $DBC = DBC::getInstance();
        $stmt = $DBC->query($q, array($email, $activation_code));

        if (!$stmt) {
            $rs = $this->formatActivateErrorMessage(Multilanguage::_('ACTIVATION_ERROR', 'system'));
        } else {
            $ar = $DBC->fetch($stmt);
            $new_user_id = $ar['user_id'];
            if ((int)$ar['cnt'] == 0) {
                $q = "UPDATE " . DB_PREFIX . "_user SET active=1 WHERE email=? AND pass=?";
                $stmt = $DBC->query($q, array($email, $activation_code));

                if (Multilanguage::is_set('LT_ACCOUNT_ACTIVATED', '_template')) {
                    $rs = $this->formatActivateSuccessMessage(Multilanguage::_('LT_ACCOUNT_ACTIVATED', '_template'));
                } else {
                    $rs = $this->formatActivateSuccessMessage(Multilanguage::_('ACCOUNT_ACTIVATED', 'system'));
                }

                if (1 == $this->getConfigValue('notify_admin_about_register')) {
                    $this->notify_admin_about_register($new_user_id);
                }

                if (1 == $this->getConfigValue('registration_notice')) {
                    $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/template/mails/register_email_notify_complete.tpl';

                    global $smarty;

                    $q = "SELECT * FROM " . DB_PREFIX . "_user WHERE email=? LIMIT 1";
                    $stmt = $DBC->query($q, array($email));
                    $ar = $DBC->fetch($stmt);

                    $user_info = $ar;
                    $query = "SELECT * FROM " . DB_PREFIX . "_cache WHERE parameter=?";
                    $stmt = $DBC->query($query, array($activation_code));
                    $password = '';
                    if ($stmt) {
                        $ar = $DBC->fetch($stmt);
                        $password = $ar['value'];
                    }
                    $query = "DELETE FROM " . DB_PREFIX . "_cache WHERE parameter=?";
                    $stmt = $DBC->query($query, array($activation_code));

                    $smarty->assign('user_name', $user_info['fio']);
                    $smarty->assign('site_url', $this->getServerFullUrl());
                    if (1 == intval($this->getConfigValue('email_as_login'))) {
                        $smarty->assign('login', $user_info['email']);
                    } else {
                        $smarty->assign('login', $user_info['login']);
                    }

                    $smarty->assign('password', $password);
                    $smarty->assign('current_language', Multilanguage::get_current_language());

                    $smarty->assign('email_signature', $this->getConfigValue('email_signature'));
                    if (file_exists($tpl)) {
                        $message = $smarty->fetch($tpl);
                    } else {
                        $message = Multilanguage::_('NEW_REGISTER_BODY_TRIMMED', 'system');
                    }
                    if (Multilanguage::is_set('LT_NEW_REGISTER_TITLE', '_template')) {
                        $subject = sprintf(Multilanguage::_('LT_NEW_REGISTER_TITLE', '_template'), $_SERVER['HTTP_HOST']);
                    } else {
                        $subject = sprintf(Multilanguage::_('NEW_REGISTER_TITLE', 'system'), $_SERVER['HTTP_HOST']);
                    }
                    $to = $this->getRequestValue('email');
                    $from = $this->getConfigValue('system_email');

                    $this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
                    $this->template->assign('target_url', $this->getServerFullUrl());

                    $email_template_fetched = $this->fetch_email_template('user_activate_complete');

                    if ($email_template_fetched) {
                        $subject = $email_template_fetched['subject'];
                        $message = $email_template_fetched['message'];

                        $message_array['apps_name'] = 'register_using_model';
                        $message_array['method'] = __METHOD__;
                        $message_array['message'] = "subject = $subject, message = $message";
                        $message_array['type'] = '';
                        //$this->writeLog($message_array);
                    }

                    $this->sendFirmMail($to, $from, $subject, $message);
                }
            } else {
                header('location: ' . SITEBILL_MAIN_URL . '/');
                exit();
                $rs = Multilanguage::_('ACTIVATION_ERROR', 'system');
            }
        }

        return $rs;
    }
}
