<?php
/**
 * UserDataNotificationTrait — notification methods extracted from User_Data_Manager.
 *
 * Methods: notifyAboutNewAdvert, notifyAboutModerationNeed, notifyUserAboutAdding
 */
trait UserDataNotificationTrait
{
    private function notifyAboutNewAdvert($id)
    {

        /* require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
          $mailer = new Mailer(); */
        $subject = $_SERVER['SERVER_NAME'] . ': добавлено новое объявление';

        $from = $this->getConfigValue('system_email');
        $useremail = $this->getConfigValue('order_email_acceptor');
        $body = '';
        $body .= 'Было добавлено объявление с ID ' . $id . '<br />';

        $data_model = new Data_Model();
        $model = $data_model->get_kvartira_model(false, true);
        $model = $data_model->init_model_data_from_db($this->table_name, $this->primary_key, $id, $model[$this->table_name]);

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/view.php');
        $table_view = new Table_View();
        $table_view->setAbsoluteUrls();
        $body .= '<table border="1" cellpadding="2" cellspacing="2" class="table table-striped table-hover">';
        $body .= $table_view->compile_view($model);
        $body .= '</table>';

        $body .= $this->getConfigValue('email_signature');


        $this->template->assign('target_url', $this->getServerFullUrl() . '/admin/?action=data&do=edit&id=' . $id);
        $this->template->assign('id', $id);
        $this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
        $email_template_fetched = $this->fetch_email_template('new_adv_nomoderate');

        if ($email_template_fetched) {
            $subject = $email_template_fetched['subject'];
            $message = $email_template_fetched['message'];

            $message_array['apps_name'] = 'new_adv_nomoderate';
            $message_array['method'] = __METHOD__;
            $message_array['message'] = "subject = $subject, message = $message";
            $message_array['type'] = '';
            ////$this->writeLog($message_array);
        }

        $this->sendFirmMail($useremail, $from, $subject, $body);
        return;
    }

    private function notifyAboutModerationNeed($id, $action = 'new')
    {

        /* require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
          $mailer = new Mailer(); */
        $subject = $_SERVER['SERVER_NAME'] . ': объявление требует модерации';
        $from = $this->getConfigValue('system_email');
        $useremail = $this->getConfigValue('order_email_acceptor');
        $body = '';
        if ($action === 'edit') {
            $body .= 'Было изменено объявление с ID ' . $id . '<br />';
            $body .= 'Объявление снято с публикации и ожидает модерации.<br />';
        } else {
            $body .= 'Было добавлено объявление с ID ' . $id . '<br />';
            $body .= 'Объявление ожидает модерации.<br />';
        }


        $body .= $this->getConfigValue('email_signature');
        /* if ( $this->getConfigValue('use_smtp') ) {
          $mailer->send_smtp($useremail, $from, $subject, $body, 1);
          } else {
          $mailer->send_simple($useremail, $from, $subject, $body, 1);
          } */

        $this->template->assign('target_url', $this->getServerFullUrl() . '/admin/?action=data&do=edit&id=' . $id);
        if ($action === 'edit') {
            $this->template->assign('edit_action', 1);
        }
        $this->template->assign('id', $id);
        $this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
        $email_template_fetched = $this->fetch_email_template('need_moderate');

        if ($email_template_fetched) {
            $subject = $email_template_fetched['subject'];
            $message = $email_template_fetched['message'];

            $message_array['apps_name'] = 'need_moderate';
            $message_array['method'] = __METHOD__;
            $message_array['message'] = "subject = $subject, message = $message";
            $message_array['type'] = '';
            ////$this->writeLog($message_array);
        }

        $this->sendFirmMail($useremail, $from, $subject, $body);
        return;
    }

    private function notifyUserAboutAdding($user_id, $id, $topic_id)
    {
        $DBC = DBC::getInstance();

        $useremail = '';
        $fio = '';
        $query = 'SELECT fio, email FROM ' . DB_PREFIX . '_user WHERE user_id=? LIMIT 1';
        $stmt = $DBC->query($query, array($user_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $fio = $ar['fio'];
            $useremail = $ar['email'];
        }

        $translit_alias = '';
        $query = 'SELECT translit_alias FROM ' . DB_PREFIX . '_data WHERE id=? LIMIT 1';
        $stmt = $DBC->query($query, array($id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $translit_alias = $ar['translit_alias'];
        }

        $href = $this->getRealtyHREF($id, true, array('topic_id' => $topic_id, 'alias' => $translit_alias));


        $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/template/mails/reguser_add_notify.tpl';
        if (file_exists($tpl)) {
            //$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/system/template/mails/user_registration_conf.tpl';
            global $smarty;
            $smarty->assign('mail_adv_link', $href);
            $smarty->assign('mail_user_fio', $fio);
            $smarty->assign('mail_adv_id', $id);
            if (1 == $this->getConfigValue('moderate_first')) {
                $smarty->assign('mail_moderate_first', 1);
            }
            $smarty->assign('mail_signature', $this->getConfigValue('email_signature'));
            $body = $smarty->fetch($tpl);
        } else {
            $body = '';
            $body .= sprintf(Multilanguage::_('DEAR_FIO', 'system'), $fio) . '<br />';
            $body .= Multilanguage::_('YOUR_ADV_ADD', 'system') . '<br />';
            $body .= Multilanguage::_('YOUR_ADV_LINK', 'system') . ' <a href="' . $href . '">' . $href . '</a><br />';
            if (1 == $this->getConfigValue('moderate_first')) {
                $body .= Multilanguage::_('ADV_NEED_MODERATING_FIRST', 'system') . '<br />';
            }
            $body .= $this->getConfigValue('email_signature');
        }


        $subject = $_SERVER['SERVER_NAME'] . ': ' . Multilanguage::_('REGU_ADDNOTE_SUBJ', 'system');
        $from = $this->getConfigValue('system_email');
        /* $body='';
          $body.='Уважаемый, '.$fio.'!<br />';
          $body.='Ваше объявление размещено.<br />';
          $body.='Адрес объявления <a href="'.$href.'">'.$href.'</a><br />';
          $body.=$this->getConfigValue('email_signature'); */
        /* if ( $this->getConfigValue('use_smtp') ) {
          $mailer->send_smtp($useremail, $from, $subject, $body, 1);
          } else {
          $mailer->send_simple($useremail, $from, $subject, $body, 1);
          } */

        $this->template->assign('target_url', $href);
        $this->template->assign('edit_url', $this->getServerFullUrl() . '/account/data/?do=edit&id=' . $id);
        $this->template->assign('moderate_first', $this->getConfigValue('moderate_first'));
        $this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
        $email_template_fetched = $this->fetch_email_template('user_notify_about_adding');

        if ($email_template_fetched) {
            $subject = $email_template_fetched['subject'];
            $message = $email_template_fetched['message'];

            $message_array['apps_name'] = 'user_notify_about_adding';
            $message_array['method'] = __METHOD__;
            $message_array['message'] = "subject = $subject, message = $message";
            $message_array['type'] = '';
            //$this->writeLog($message_array);
        }

        $this->sendFirmMail($useremail, $from, $subject, $body);
        return;
    }
}
