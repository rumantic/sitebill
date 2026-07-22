<?php
/**
 * RegisterNotifyTrait — email notifications for Register_Using_Model
 *
 * Extracted methods:
 *   - send_registration_notice($form_data)
 *   - notify_admin_about_register($new_user_id)
 */
trait RegisterNotifyTrait
{
    protected function send_registration_notice($form_data)
    {
        $to = $form_data['email']['value'];
        if (1 == intval($this->getConfigValue('email_as_login'))) {
            $login = $form_data['email']['value'];
        } else {
            $login = $form_data['login']['value'];
        }

        $password = $form_data['newpass']['value'];
        $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/template/mails/user_registration.tpl';
        global $smarty;
        $smarty->assign('mail_login', $login);
        $smarty->assign('login', $login);
        $smarty->assign('mail_password', $form_data['newpass']['value']);
        $smarty->assign('password', $form_data['newpass']['value']);
        $smarty->assign('mail_server', $this->getServerFullUrl());

        if (file_exists($tpl)) {
            //$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/system/template/mails/user_registration.tpl';

            $message = $smarty->fetch($tpl);
        } else {
            $message = sprintf(Multilanguage::_('NEW_REGISTER_BODY', 'system'), $login, '***');
        }

        if (Multilanguage::is_set('LT_NEW_REGISTER_TITLE', '_template')) {
            $subject = sprintf(Multilanguage::_('LT_NEW_REGISTER_TITLE', '_template'), $_SERVER['HTTP_HOST']);
        } else {
            $subject = sprintf(Multilanguage::_('NEW_REGISTER_TITLE', 'system'), $_SERVER['HTTP_HOST']);
        }

        $from = $this->getConfigValue('system_email');

        $this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
        $this->template->assign('target_url', $this->getServerFullUrl());

        $email_template_fetched = $this->fetch_email_template('user_registration_complete');

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

    public function notify_admin_about_register($new_user_id)
    {
        $DBC = DBC::getInstance();
        $q = "SELECT * FROM " . DB_PREFIX . "_user WHERE user_id=? LIMIT 1";
        $stmt = $DBC->query($q, array($new_user_id));
        $user_info = $DBC->fetch($stmt);

        if (1 == intval($this->getConfigValue('email_as_login'))) {
            $login = $user_info['email'];
        } else {
            $login = $user_info['login'];
        }

        $message = sprintf(Multilanguage::_('NEW_REGISTER_NEW_USER', 'system'), $login);
        $subject = sprintf(Multilanguage::_('NEW_REGISTER_TITLE', 'system'), $_SERVER['HTTP_HOST']);

        $to = $this->getConfigValue('order_email_acceptor');
        $from = $this->getConfigValue('system_email');

        $this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
        $this->template->assign('target_url', $this->getServerFullUrl() . '/admin/?action=user');
        $this->template->assign('user_info', $user_info);

        $email_template_fetched = $this->fetch_email_template('notify_admin_about_register');

        if ($email_template_fetched) {
            $subject = $email_template_fetched['subject'];
            $message = $email_template_fetched['message'];

            $message_array['apps_name'] = 'notify_admin_about_register';
            $message_array['method'] = __METHOD__;
            $message_array['message'] = "subject = $subject, message = $message";
            $message_array['type'] = '';
            //$this->writeLog($message_array);
        }

        $this->sendFirmMail($to, $from, $subject, $message);
    }
}
