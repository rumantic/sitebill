<?php
/**
 * MailTrait — extracted from SiteBill class (sitebill.php)
 * Auto-generated, do not edit manually.
 */
trait MailTrait
{
    public function sendFirmMail($to, $from, $subject, $body, $customtpl = '', $to_user_id = 0)
    {
        if ( config('apps.telegram.enable') and config('apps.telegram.email_duplicate') ) {
            include_once (SITEBILL_DOCUMENT_ROOT.'/apps/telegram/admin/admin.php');
            $telegram_admin = new telegram_admin();
            $html = new \Html2Text\Html2Text($subject.'<br>'.$body);
            $post = [
                'text' => $html->getText(),
                'parse_mode' => 'html'
            ];
            $telegram_admin->_send(config('apps.telegram.token'), config('apps.telegram.chat_id'), $post);

        }
        Logger::emaillog($to, $from, $subject, $body, $to_user_id);
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/mailer/mailer.php');
        $mailer = new Mailer();
        //Если указано несколько почтовых ящиков для получения в $to через запятую, то делаем из него массив
        if (is_string($to)) {
            if (preg_match('/,/', $to)) {
                $to_array = explode(',', $to);
                $to = array();
                foreach ($to_array as $k => $to_email_string) {
                    array_push($to, $to_email_string);
                }
            }
        }
        $this->writeLog(__METHOD__ . ', ' . "to = " . var_export($to, true));


        global $smarty;
        $smarty->assign('to', $to);
        $smarty->assign('subject', $subject);

        $smarty->assign('letter_content', $body);
        $smarty->assign('estate_core_url', $this->getServerFullUrl());
        $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/system/template/firm_mail_wrapper.tpl';
        if ($customtpl != '' && file_exists($customtpl)) {
            $tpl = $customtpl;
        }
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/firm_mail_wrapper.tpl')) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/firm_mail_wrapper.tpl';
        } elseif (!$this->getConfigValue('apps.emailtemplates.enable', false)) {
            // Если не установлен emailtemplates, то используем новую обертку для Email-сообщения, в которой есть полноценные шапка и подвал
            // Локальный firm_mail_wrapper.tpl используем как обычно, если он есть
            $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/system/template/firm_mail_wrapper_with_header_and_footer.tpl';
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/firm_mail_wrapper_with_header_and_footer.tpl')) {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/firm_mail_wrapper_with_header_and_footer.tpl';
            }
        }
        $body = $smarty->fetch($tpl);

        if ($this->getConfigValue('use_smtp')) {
            $mailer->send_smtp($to, $from, $subject, $body, 1);
        } else {
            $mailer->send_simple($to, $from, $subject, $body, 1);
        }
        /* TODO
         * Этот блок предназначен на замену для возможности отправки приложений в письмах
         */
        /*
        if ($this->getConfigValue('use_smtp')) {
            $mailer->send_smtp($to, $from, $subject, $body);
        } else {
            $mailer->send_simple($to, $from, $subject, $body, $attachments);
        }
        */
    }

    /**
     * Ищем в таблице emailtemplates шаблон с именем $name
     * Если находим, то делаем smarty fetch для subject и message
     * Предварительно все переменные должны быть assign-нуты в smarty
     * @param type $name - системное название шаблона
     * @return mixed (массив с готовый с subject и message, если шаблон найдет. false - если шаблон не найден)
     */
    function fetch_email_template($name)
    {
        global $smarty;
        $ra = array();
        if ($this->getConfigValue('apps.emailtemplates.enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/emailtemplates/admin/admin.php');
            $emailtemplates_admin = new emailtemplates_admin();
            return $emailtemplates_admin->compile_template($name);
        }
        return false;
    }

    function clear_apps_cache()
    {
        //Очищаем кэш apps
        $DBC = DBC::getInstance();
        $query = "TRUNCATE TABLE " . DB_PREFIX . "_apps";
        $stmt = $DBC->query($query, array(), $rows, $success);
    }

}
