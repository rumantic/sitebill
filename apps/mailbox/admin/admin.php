<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * mailbox admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class mailbox_admin extends Object_Manager {

    /**
     * Constructor
     */
    function __construct($realty_type = false) {
        $this->SiteBill();

        $this->table_name = 'mailbox';
        $this->action = 'mailbox';
        Multilanguage::appendAppDictionary($this->action, $this->getConfigValue('theme'));


        $this->primary_key = 'mailbox_id';

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_admin = new config_admin();

        if (!$config_admin->check_config_item('apps.mailbox.enable')) {
            $config_admin->addParamToConfig('apps.mailbox.enable', '0', 'Включить приложение Mailbox');
        }
        if (!$config_admin->check_config_item('apps.mailbox.claim_address')) {
            $config_admin->addParamToConfig('apps.mailbox.claim_address', '', 'Адрес электронной почты для отправки жалоб');
        }
        if (!$config_admin->check_config_item('apps.mailbox.show_claim_button')) {
            $config_admin->addParamToConfig('apps.mailbox.show_claim_button', '0', 'Показывать кнопку добавления жалобы');
        }
        if (!$config_admin->check_config_item('apps.mailbox.use_complaint_mode')) {
            $config_admin->addParamToConfig('apps.mailbox.use_complaint_mode', '0', 'Включить режим Жалоба', 1);
        }

        if (!$config_admin->check_config_item('apps.mailbox.complaint_mode_variants')) {
            $config_admin->addParamToConfig('apps.mailbox.complaint_mode_variants', '{1~~Агент/Мошенник}{2~~Продано/Сдано}{3~~Неверная цена}{4~~Неверный адрес}{5~~Недозвониться}', 'Перечень жалоб', 3);
        }

        if (!$config_admin->check_config_item('apps.mailbox.complaint_black_auto')) {
            $config_admin->addParamToConfig('apps.mailbox.complaint_black_auto', 0, 'Автоматически добавлять в черный список', 1);
        }
        
        $config_admin->addParamToConfig('apps.mailbox.complaint_black_auto_phone_field_name', 'phone', 'Название поля в таблице data с телефоном, который нужно блокировать');

        //$this->install();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/mailbox/admin/mailbox_model.php');
        $Object = new Mailbox_Model();
        $this->data_model = $Object->get_model();
    }

    public function _preload() {
        if ($this->getConfigValue('apps.mailbox.enable')) {
            $this->template->assert('mailbox_panel', $this->getMailboxPanel(intval($_SESSION['user_id'])));
            $this->template->assert('mailbox_on', 1);
            $this->template->assert('post_form_agreement_enable', $this->getConfigValue('post_form_agreement_enable'));
            $this->template->assert('post_form_agreement_text_add', $this->getConfigValue('post_form_agreement_text_add'));
            $this->template->assert('estate_folder', SITEBILL_MAIN_URL);
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/mailbox/site/template/form.tpl')) {
                $this->template->assert('apps_mailbox_block', SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/mailbox/site/template/form.tpl');
            } else {
                $this->template->assert('apps_mailbox_block', SITEBILL_DOCUMENT_ROOT . '/apps/mailbox/site/template/form.tpl');
            }
            if (1 == $this->getConfigValue('apps.mailbox.show_claim_button')) {
                $this->template->assert('apps_mailbox_show_claim_button', 1);
            }
            if (1 == $this->getConfigValue('apps.mailbox.use_complaint_mode')) {
                $this->template->assert('apps_mailbox_use_complaint_mode', 1);
                $ret = array();
                if ('' != trim($this->getConfigValue('apps.mailbox.complaint_mode_variants'))) {

                    $matches = array();
                    preg_match_all('/\{[^\}]+\}/', trim($this->getConfigValue('apps.mailbox.complaint_mode_variants')), $matches);
                    if (count($matches) > 0) {
                        foreach ($matches[0] as $v) {
                            $v = str_replace(array('{', '}'), '', $v);
                            $d = explode('~~', $v);
                            $ret[$d[0]] = $d[1];
                        }
                    }
                }
                $c['captcha']['name'] = 'captcha';
                $c['captcha']['title'] = Multilanguage::_('CAPTCHA_TITLE', 'system');
                $c['captcha']['value'] = '';
                $c['captcha']['length'] = 40;
                $c['captcha']['type'] = 'captcha';
                $c['captcha']['required'] = 'on';
                $c['captcha']['unique'] = 'off';
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
                $form_generator = new Form_Generator();
                $el = $form_generator->compile_form_elements($c);
                $this->template->assign('apps_mailbox_complaint_mode_captcha', $el['hash']['captcha']['html']);
                $this->template->assert('apps_mailbox_complaint_mode_variants', $ret);
            }
        } else {
            $this->template->assert('mailbox_panel', '');
            $this->template->assert('mailbox_on', 0);
        }
    }

    function install() {
        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_mailbox` (
			  `mailbox_id` int(11) NOT NULL AUTO_INCREMENT,
			  `sender_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `reciever_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `theme` varchar(255) NOT NULL,
			  `message` text NOT NULL,
			  `name` varchar(255) NOT NULL,
			  `phone` varchar(30) NOT NULL,
			  `email` varchar(100) NOT NULL,
			  `realty_id` int(10) unsigned NOT NULL DEFAULT '0',
			  `status` tinyint(4) NOT NULL DEFAULT '0',
			  `creation_date` datetime NOT NULL,
			  PRIMARY KEY (`mailbox_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=" . DB_ENCODING . " AUTO_INCREMENT=1 ;";
        $DBC = DBC::getInstance();
        $success = false;
        $stmt = $DBC->query($query, array(), $rows, $success);
        if (!$success) {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED_ERROR');
        } else {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED');
        }
        return $rs;
    }

    function grid($params = array(), $default_params = array()) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);


        $common_grid->add_grid_item($this->primary_key);
        $common_grid->add_grid_item('theme');
        $common_grid->add_grid_item('message');

        $common_grid->add_grid_control('edit');
        $common_grid->add_grid_control('delete');

        $common_grid->setPagerParams(array('action' => $this->action, 'page' => $this->getRequestValue('page'), 'per_page' => $this->getConfigValue('common_per_page')));

        //$common_grid->set_grid_query("select * from ".DB_PREFIX."_".$this->table_name." order by ".$this->primary_key." asc");
        $rs = $common_grid->construct_grid();
        return $rs;
    }

    function getTopMenu() {
        $rs = '';
        $rs .= '<a href="?action=' . $this->action . '&do=new" class="btn btn-primary">' . Multilanguage::_('L_ADD_RECORD_BUTTON') . '</a> ';
        $rs .= '<a href="?action=' . $this->action . '&do=massnew" class="btn btn-primary">Отправить всем</a> ';
        return $rs;
    }

    function ajax() {
        if ($this->getRequestValue('action') == 'get_logged_user_data') {
            return $this->get_logged_user_data();
        } elseif ($this->getRequestValue('action') == 'send_message' && 'post'==strtolower($_SERVER['REQUEST_METHOD'])) {

            return $this->save_message();
        } elseif ($this->getRequestValue('action') == 'send_admin_message') {
            $captcha = $this->getRequestValue('captcha');
            $captcha_key = $this->getRequestValue('captcha_key');
            $DBC = DBC::getInstance();
            $query = 'SELECT COUNT(*) AS cnt FROM ' . DB_PREFIX . '_captcha_session WHERE captcha_session_key=? AND captcha_string=?';
            $stmt = $DBC->query($query, array($captcha_key, $captcha));
            $ar = $DBC->fetch($stmt);
            if ($ar['cnt'] == 1) {
                $this->setRequestValue('reciever_id', $this->getAdminUserId());
                return $this->save_message();
            } else {
                return json_encode(array('answer' => 'invalid_captcha'));
            }

        } elseif ($this->getRequestValue('action') == 'send_friend_message' && 'post'==strtolower($_SERVER['REQUEST_METHOD'])) {
            return $this->send_friend_message();
        } elseif ($this->getRequestValue('action') == 'read_message' && 'post'==strtolower($_SERVER['REQUEST_METHOD'])) {
            return $this->read_message();
        } elseif ($this->getRequestValue('action') == 'get_complaint_form' ) {
            return $this->get_complaint_form();
        } elseif ($this->getRequestValue('action') == 'save_complaint' && 'post'==strtolower($_SERVER['REQUEST_METHOD'])) {
            return $this->save_complaint();
        } elseif ($this->getRequestValue('action') == 'send_complaint' && 'post'==strtolower($_SERVER['REQUEST_METHOD'])) {
            return $this->send_complaint();
        } elseif ($this->getRequestValue('action') == 'get_connect_form' ) {
            return $this->get_connect_form();
        }/*elseif ( $this->getRequestValue('action') == 'test' ) {
            return 'mailbox_test';
        }*/else {

        }
        return false;
    }

    private function get_connect_form() {
        $ret = '<div class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>Modal header</h3>
  </div>
  <div class="modal-body">
    <p>One fine body…</p>
  </div>
  <div class="modal-footer">
    <a href="#" class="btn">Close</a>
    <a href="#" class="btn btn-primary">Save changes</a>
  </div>
</div>';
        return $ret;
    }

    private function send_complaint() {

        $responce = array('status' => 0, 'msg' => '');

        if (1 != $this->getConfigValue('apps.mailbox.use_complaint_mode')) {
            return json_encode($responce);
        }

        $id = intval($this->getRequestValue('id'));
        $complaint_id = intval($this->getRequestValue('complaint_id'));
        $captcha = trim($_POST['captcha']);
        $captcha_session_key = trim($_POST['captcha_session_key']);

        $ret = array();
        if ('' != trim($this->getConfigValue('apps.mailbox.complaint_mode_variants'))) {

            $matches = array();
            preg_match_all('/\{[^\}]+\}/', trim($this->getConfigValue('apps.mailbox.complaint_mode_variants')), $matches);
            if (count($matches) > 0) {
                foreach ($matches[0] as $v) {
                    $v = str_replace(array('{', '}'), '', $v);
                    $d = explode('~~', $v);
                    $ret[$d[0]] = $d[1];
                }
            }
        }

        if (!isset($ret[$complaint_id])) {
            $responce['msg'] = 'Не указана причина жалобы';
            return json_encode($responce);
        }

        $DBC = DBC::getInstance();
        if (2 != $this->getConfigValue('captcha_type')) {
            $query = 'SELECT captcha_session_id FROM ' . DB_PREFIX . '_captcha_session WHERE captcha_session_key=? AND captcha_string=?';
            $stmt = $DBC->query($query, array($captcha_session_key, $captcha));
            if (!$stmt) {
                $query = 'DELETE FROM ' . DB_PREFIX . '_captcha_session WHERE captcha_session_key=?';
                $stmt = $DBC->query($query, array($captcha_session_key));
                $responce['msg'] = 'Не правильно указан защитный код';
                return json_encode($responce);
                return;
            }
            $query = 'DELETE FROM ' . DB_PREFIX . '_captcha_session WHERE captcha_session_key=?';
            $stmt = $DBC->query($query, array($captcha_session_key));
        }


        $body = 'Жалоба на объект ID: ' . $id . '<br />';
        $body .= 'Причина жалобы: ' . $ret[$complaint_id] . '<br />';
        $subject = $_SERVER['SERVER_NAME'] . ': Жалоба на объявление ID: ' . $id;
        $from = $this->getConfigValue('system_email');
        if ('' == $this->getConfigValue('apps.mailbox.claim_address')) {
            $n_email = $this->getConfigValue('order_email_acceptor');
        } else {
            $n_email = $this->getConfigValue('apps.mailbox.claim_address');
        }

        $this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
        $this->template->assign('complaint_description', $ret[$complaint_id]);
        $this->template->assign('id', $id);
        $this->template->assign('edit_url', $this->getServerFullUrl() . '/admin/?action=data&do=edit&id=' . $id);
        $email_template_fetched = $this->fetch_email_template('complaint_object');

        if ($email_template_fetched) {
            $subject = $email_template_fetched['subject'];
            $body = $email_template_fetched['message'];

            $message_array['apps_name'] = 'need_moderate';
            $message_array['method'] = __METHOD__;
            $message_array['message'] = "subject = $subject, message = $body";
            $message_array['type'] = '';
            //$this->writeLog($message_array);
        }


        $this->sendFirmMail($n_email, $from, $subject, $body);

        if (1 == intval($this->getConfigValue('apps.mailbox.complaint_black_auto')) && 1 == intval($this->getConfigValue('apps.blacklist.enable'))) {

            $DBC = DBC::getInstance();
            $phone_field_name = $this->getConfigValue('apps.mailbox.complaint_black_auto_phone_field_name');
            $query = 'SELECT `'.$phone_field_name.'` FROM ' . DB_PREFIX . '_data WHERE id=?';
            $stmt = $DBC->query($query, array($id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                if ($ar[$phone_field_name] != '') {
                    $nr = preg_replace('/[^0-9]/', '', $ar[$phone_field_name]);
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/blacklist/admin/admin.php';
                    $BA = new blacklist_admin();
                    $BA->addNumberToBlacklist($nr, $ret[$complaint_id]);
                }
            }
        }

        $responce['status'] = 1;
        return json_encode($responce);
    }

    private function save_complaint() {
        if (1 != $this->getConfigValue('apps.mailbox.show_claim_button')) {
            return 1;
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        //$form_data = $this->data_model;
        $form_data = array();

        $form_data['email']['name'] = 'email';
        $form_data['email']['title'] = 'E-mail';
        $form_data['email']['value'] = '';
        $form_data['email']['length'] = 40;
        $form_data['email']['type'] = 'safe_string';
        $form_data['email']['required'] = 'off';
        $form_data['email']['unique'] = 'off';

        $form_data['realty_id']['name'] = 'realty_id';
        $form_data['realty_id']['title'] = 'realty_id';
        $form_data['realty_id']['value'] = '';
        $form_data['realty_id']['length'] = 40;
        $form_data['realty_id']['type'] = 'hidden';
        $form_data['realty_id']['required'] = 'off';
        $form_data['realty_id']['unique'] = 'off';

        $form_data['message']['name'] = 'message';
        $form_data['message']['title'] = 'Сообщение';
        $form_data['message']['value'] = '';
        $form_data['message']['length'] = 40;
        $form_data['message']['type'] = 'textarea';
        $form_data['message']['required'] = 'off';
        $form_data['message']['unique'] = 'off';
        $form_data['message']['rows'] = '10';
        $form_data['message']['cols'] = '40';

        $form_data['captcha']['name'] = 'captcha';
        $form_data['captcha']['title'] = Multilanguage::_('CAPTCHA_TITLE', 'system');
        $form_data['captcha']['value'] = '';
        $form_data['captcha']['length'] = 40;
        $form_data['captcha']['type'] = 'captcha';
        $form_data['captcha']['required'] = 'on';
        $form_data['captcha']['unique'] = 'off';

        $form_data = $data_model->init_model_data_from_request($form_data);

        if (!$this->check_data($form_data)) {
            return 0;
        } else {
            $body = nl2br($form_data['message']['value']) . '<br />';
            $body .= 'Email отправителя ' . $form_data['email']['value'] . '<br /><br />';
            /* require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
              $mailer = new Mailer(); */
            $subject = $_SERVER['SERVER_NAME'] . ': Жалоба на объявление ID' . $form_data['realty_id']['value'];
            $from = $this->getConfigValue('system_email');
            if ('' == $this->getConfigValue('apps.mailbox.claim_address')) {
                $n_email = $this->getConfigValue('order_email_acceptor');
            } else {
                $n_email = $this->getConfigValue('apps.mailbox.claim_address');
            }

            /* if ( $this->getConfigValue('use_smtp') ) {
              $mailer->send_smtp($n_email, $from, $subject, $body, 1);
              } else {
              $mailer->send_simple($n_email, $from, $subject, $body, 1);
              } */

            $this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
            $this->template->assign('complaint_description', nl2br($form_data['message']['value']));
            $this->template->assign('id', $form_data['realty_id']['value']);
            $this->template->assign('edit_url', $this->getServerFullUrl() . '/admin/?action=data&do=edit&id=' . $form_data['realty_id']['value']);
            $email_template_fetched = $this->fetch_email_template('complaint_object');

            if ($email_template_fetched) {
                $subject = $email_template_fetched['subject'];
                $body = $email_template_fetched['message'];

                $message_array['apps_name'] = 'need_moderate';
                $message_array['method'] = __METHOD__;
                $message_array['message'] = "subject = $subject, message = $body";
                $message_array['type'] = '';
                //$this->writeLog($message_array);
            }

            $this->sendFirmMail($n_email, $from, $subject, $body);
            return 1;
        }
    }

    private function get_complaint_form() {
        if (1 != $this->getConfigValue('apps.mailbox.show_claim_button')) {
            return '';
        }
        $id = (int) $this->getRequestValue('realty_id');
        global $smarty;
        $form_data = array();

        $form_data['email']['name'] = 'email';
        $form_data['email']['title'] = 'E-mail';
        $form_data['email']['value'] = '';
        $form_data['email']['length'] = 40;
        $form_data['email']['type'] = 'safe_string';
        $form_data['email']['required'] = 'off';
        $form_data['email']['unique'] = 'off';

        $form_data['realty_id']['name'] = 'realty_id';
        $form_data['realty_id']['title'] = 'realty_id';
        $form_data['realty_id']['value'] = $id;
        $form_data['realty_id']['length'] = 40;
        $form_data['realty_id']['type'] = 'hidden';
        $form_data['realty_id']['required'] = 'off';
        $form_data['realty_id']['unique'] = 'off';

        $form_data['message']['name'] = 'message';
        $form_data['message']['title'] = 'Сообщение';
        $form_data['message']['value'] = '';
        $form_data['message']['length'] = 40;
        $form_data['message']['type'] = 'textarea';
        $form_data['message']['required'] = 'off';
        $form_data['message']['unique'] = 'off';
        $form_data['message']['rows'] = '10';
        $form_data['message']['cols'] = '40';

        $form_data['captcha']['name'] = 'captcha';
        $form_data['captcha']['title'] = Multilanguage::_('CAPTCHA_TITLE', 'system');
        $form_data['captcha']['value'] = '';
        $form_data['captcha']['length'] = 40;
        $form_data['captcha']['type'] = 'captcha';
        $form_data['captcha']['required'] = 'on';
        $form_data['captcha']['unique'] = 'off';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();


        $rs .= '<form method="post" class="form" action="' . $action . '" enctype="multipart/form-data">';


        $el = $form_generator->compile_form_elements($form_data);
        $el['form_header'] = $rs;
        $el['form_footer'] = '</form>';







        $smarty->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl';
        } else {
            $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
        }
        return $smarty->fetch($tpl_name);
    }

    function read_message($user_id) {
        $id = $this->getRequestValue('id');
        $q = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET status=1 WHERE mailbox_id=?';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($q, array($id));
    }

    
    
    function getMailboxPanel($user_id) {
        if ($user_id == 0) {
            return '';
        } else {
            $total_msgs = $this->getUserIncomingMessages($user_id);
            $unreaded_msgs = $this->getUserIncomingMessagesUnreaded($user_id);
            if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/mailbox/site/template/panel.tpl')){
                global $smarty;
                $smarty->assign('mailbox_allmsg', $total_msgs['count']);
                $smarty->assign('mailbox_unreaded', $unreaded_msgs['count']);
                return $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/mailbox/site/template/panel.tpl');
            }else{
                return Multilanguage::_('MESSAGES', 'mailbox') . ': <span class="mailbox_allmsg">' . $total_msgs['count'] . '</span>'.($unreaded_msgs['count']>0 ? ' (<span class="mailbox_unrmsg">' . $unreaded_msgs['count'].'</span>)' : '');
            }
            /*if($total_msgs['count']>0){
                return '<a href="'.SITEBILL_MAIN_URL.'/mailbox/">Сообщения: '.$total_msgs['count'].' ('.$unreaded_msgs['count'].')</a>';
            }else{
                return 'Сообщения: '.$total_msgs['count'].' ('.$unreaded_msgs['count'].')';
            }*/
            //return Multilanguage::_('MESSAGES','mailbox').': <span class="mailbox_allmsg">'.$total_msgs['count'].'</span>'.($unreaded_msgs['count']>0 ? ' (<span class="mailbox_unrmsg">'.$unreaded_msgs['count'].'</span>)' : '');
        }
    }

    function getUserIncomingMessages($user_id) {
        $ret = array();
        $senders=array();
        $DBC=DBC::getInstance();

        $where=array();
        $where_val=array();

        $where[]='`reciever_id`=?';
        $where_val[]=$user_id;
        if(isset($params['realty_id']) && 0<intval($params['realty_id'])){
            $where[]='`realty_id`=?';
            $where_val[]=intval($params['realty_id']);
        }

        $query='SELECT * FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE '.implode(' AND ', $where).' ORDER BY `creation_date` DESC';
        $stmt=$DBC->query($query, $where_val);

        if($stmt){
            while($ar=$DBC->fetch($stmt)){
                $ret[]=$ar;
                $senders[$ar['sender_id']]=$ar['sender_id'];
            }
        }

        if(!empty($senders)){
            $query='SELECT * FROM '.DB_PREFIX.'_user WHERE `user_id` IN ('.implode(',', array_keys($senders)).')';
            $stmt=$DBC->query($query);
            if($stmt){
                while($ar=$DBC->fetch($stmt)){
                    $senders[$ar['user_id']]=$ar;
                }
            }
        }

        if (count($ret) > 0) {
            foreach ($ret as &$r) {
                $r['href'] = $this->getRealtyHREF($r['realty_id']);
                $r['_delhref'] = SITEBILL_MAIN_URL.'/mailbox/delete/'.$r['mailbox_id'];
                $r['_gohref'] = SITEBILL_MAIN_URL.'/mailbox/?realty_id='.$r['realty_id'];
                if($r['sender_id'] != 0){
                    $r['_sender'] = $senders[$r['sender_id']];
                }
            }
        }

        return array('count' => count($ret), 'messages' => $ret);
    }

    function getUserIncomingMessagesUnreaded($user_id) {
        $ret = array();
        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE status=0 AND reciever_id=' . $user_id . ' ORDER BY creation_date DESC';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret[] = $ar;
            }
        }
        if (count($ret) > 0) {
            foreach ($ret as &$r) {
                $r['href'] = $this->getRealtyHREF($r['id']);
            }
        }
        return array('count' => count($ret), 'messages' => $ret);
    }

    function get_logged_user_data() {
        $DBC = DBC::getInstance();

        $uid = (int) $_SESSION['user_id'];
        if ($uid > 0) {
            $q = 'SELECT * FROM ' . DB_PREFIX . '_user WHERE user_id=' . $uid;
            $stmt = $DBC->query($q);
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                return json_encode(array_map(array('mailbox_admin', 'conv'), $ar));
            } else {
                return json_encode(array('res' => 'no_user'));
            }
        } else {
            return json_encode(array('res' => 'no_user'));
        }
    }

    protected function _massnewAction() {
        $rs = '';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        foreach ($form_data[$this->table_name] as $k => $v) {
            if (!in_array($k, array('theme', 'message'))) {
                unset($form_data[$this->table_name][$k]);
            }
        }

        $rs = $this->get_simple_form($form_data[$this->table_name], $do = 'massnew_done');
        return $rs;
    }

    protected function _massnew_doneAction() {
        $rs = '';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        $current_user = (int) $this->getAdminUserId();
        $except_user[] = $current_user;
        if (0 != (int) $this->getUnregisteredUserId()) {
            $except_user[] = (int) $this->getUnregisteredUserId();
        }

        $form_data = $data_model->init_model_data_from_request($form_data[$this->table_name]);

        $DBC = DBC::getInstance();
        $query = 'SELECT user_id, email FROM ' . DB_PREFIX . '_user WHERE user_id NOT IN (' . implode(',', $except_user) . ')';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {

                $m = $form_data;
                $m['sender_id']['value'] = $current_user;
                $m['reciever_id']['value'] = $ar['user_id'];
                $m['creation_date']['value'] = date('Y-m-d H:i:s', time());
                $new_record_id = $this->add_data($m);
            }
        }
        $rs = 'Сообщение разослано';

        return $rs;
    }

    function get_simple_form($form_data = array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php') {


        global $smarty;
        if ($button_title == '') {
            $button_title = Multilanguage::_('L_TEXT_SAVE');
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();


        $rs .= $this->get_ajax_functions();
        if (1 == $this->getConfigValue('apps.geodata.enable')) {
            $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/geodata/js/geodata.js"></script>';
        }
        $rs .= '<form method="post" class="form-horizontal" action="' . $action . '" enctype="multipart/form-data">';

        if ($this->getError()) {
            $smarty->assign('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
        }

        $el = $form_generator->compile_form_elements($form_data);

        $el['private'][] = array('html' => '<input type="hidden" name="do" value="massnew_done" />');
        $el['private'][] = array('html' => '<input type="hidden" name="action" value="' . $this->action . '">');
        $el['private'][] = array('html' => '<input type="hidden" name="language_id" value="' . $language_id . '">');

        $el['form_header'] = $rs;
        $el['form_footer'] = '</form>';

        $el['controls']['submit'] = array('html' => '<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">' . $button_title . '</button>');

        $smarty->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl';
        } else {
            $tpl_name = $smarty->template_dir . '/data_form.tpl';
        }
        return $smarty->fetch($tpl_name);
    }

    //function save_admin_message

    function save_message() {
        global $smarty;
        $uid = (int) $_SESSION['user_id'];
        $this->setRequestValue('sender_id', $uid);
        $DBC = DBC::getInstance();

        $payed_mode = false;

        $to = (int) $this->getRequestValue('reciever_id');

        if ($to == 0) {
            return json_encode(array('answer' => 'no_reciever'));
        }

        $query = 'SELECT user_id, email, fio FROM ' . DB_PREFIX . '_user WHERE user_id=' . $to;
        $stmt = $DBC->query($query);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ((int) $ar['user_id'] == 0 || $ar['email'] == '') {
                return json_encode(array('answer' => 'no_reciever'));
            } else {
                $n_email = $ar['email'];
                $n_fio = $ar['fio'];
            }
        } else {
            return json_encode(array('answer' => 'no_reciever'));
        }

        $theme=trim(SiteBill::iconv('utf-8', SITE_ENCODING, $this->getRequestValue('theme')));
        if($theme==''){
            $theme=Multilanguage::_('NO_THEME', 'mailbox');
        }

        $this->setRequestValue('theme', $theme);
        $this->setRequestValue('message', SiteBill::iconv('utf-8', SITE_ENCODING, $this->getRequestValue('message')));
        $this->setRequestValue('name', SiteBill::iconv('utf-8', SITE_ENCODING, $this->getRequestValue('name')));

        $message=$this->getRequestValue('message');
        $name=$this->getRequestValue('name');
        $email=$this->getRequestValue('email');
        $realty_id=intval($this->getRequestValue('realty_id'));
        $phone=$this->getRequestValue('phone');

        if($theme=='' || $message=='' || $name=='' || $email==''){
            return json_encode(array('answer'=>'fields_not_specified'));
        }

        if ($payed_mode) {
            $subject = 'Заявка на сайте ' . $_SERVER['SERVER_NAME'];
            $from = $this->getConfigValue('system_email');
            $body = 'На сайте оставлена заявка к Вашему объекту ID: ' . $realty_id;


            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            $data_model = new Data_Model();
            $form_data = $this->data_model;
            $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);

            require_once ((SITEBILL_DOCUMENT_ROOT . '/apps/client/admin/admin.php'));
            $client_admin = new client_admin();

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/view.php');
            $table_view = new Table_View();
            $order_table = '';
            $order_table .= '<table border="1" cellpadding="2" cellspacing="2" style="border: 1px solid gray;">';
            $order_table .= $table_view->compile_view($form_data[$this->table_name]);
            $order_table .= '</table>';

            $this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
            $this->template->assign('order_description', $order_table);
            $this->template->assign('id', $realty_id);
            $this->template->assign('edit_url', $this->getServerFullUrl() . '/account/data/?do=edit&id=' . $realty_id);
            $email_template_fetched = $this->fetch_email_template('mailbox_object_order');



            if ($email_template_fetched) {
                $subject = $email_template_fetched['subject'];
                $body = $email_template_fetched['message'];

                $message_array['apps_name'] = 'need_moderate';
                $message_array['method'] = __METHOD__;
                $message_array['message'] = "subject = $subject, message = $body";
                $message_array['type'] = '';
                //$this->writeLog($message_array);
            }

            $this->sendFirmMail($n_email, $from, $subject, $body);


            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/client/client.xml')) {
                require_once ((SITEBILL_DOCUMENT_ROOT . '/apps/client/admin/admin.php'));
                $client_admin = new client_admin();

                $client_admin->data_model['client']['type_id']['value'] = 'Заявка владельцу';
                $client_admin->data_model['client']['status_id']['value'] = 'new';
                $client_admin->data_model['client']['date']['value'] = time();
                $client_admin->data_model['client']['fio']['value'] = $form_data[$this->table_name]['name']['value'];
                $client_admin->data_model['client']['email']['value'] = $form_data[$this->table_name]['email']['value'];
                $client_admin->data_model['client']['phone']['value'] = $form_data[$this->table_name]['phone']['value'];

                $client_admin->data_model['client']['order_text']['value'] = $order_table;

                $order_id = $client_admin->add_data($client_admin->data_model['client']);
                if ($client_admin->getError()) {
                    $rs = $client_admin->GetErrorMessage();
                }
            }


            $form_data[$this->table_name]['theme']['value'] = 'Заявка к объекту ' . $realty_id;
            $form_data[$this->table_name]['message']['value'] = 'На сайте оставлена заявка к Вашему объекту ID: ' . $realty_id . '. ID заявки ' . $order_id;
            $form_data[$this->table_name]['email']['value'] = '';
            $form_data[$this->table_name]['name']['value'] = 'Администрация';
            $form_data[$this->table_name]['phone']['value'] = '';

            $form_data[$this->table_name]['creation_date']['value'] = date('Y-m-d H:i:s', time());
            $form_data[$this->table_name]['status']['value'] = 0;
            $this->add_data($form_data[$this->table_name]);
        } else {

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            $data_model = new Data_Model();
            $form_data = $this->data_model;
            $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
            $form_data[$this->table_name]['creation_date']['value'] = date('Y-m-d H:i:s', time());
            $form_data[$this->table_name]['status']['value'] = 0;
            $this->add_data($form_data[$this->table_name]);

            $smarty->assign('message', $message);
            $smarty->assign('theme', $theme);
            $smarty->assign('n_fio', $n_fio);
            $smarty->assign('realty_id', $realty_id);
            $smarty->assign('realty_href', $this->getRealtyHref($realty_id));
            $smarty->assign('server_name', $_SERVER['SERVER_NAME']);
            $smarty->assign('email', $email);
            $smarty->assign('email_signature', $this->getConfigValue('email_signature'));

            $smarty->assign('name', $name);
            $smarty->assign('phone', $phone);
            $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/mailbox/admin/template/email.tpl.html';
            if (!file_exists($tpl)) {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/mailbox/admin/template/email.tpl.html';
            }
            $body = $smarty->fetch($tpl);

            $subject = 'Заявка на сайте ' . $_SERVER['SERVER_NAME'] . ': ' . $theme;
            $from = $this->getConfigValue('system_email');

            $this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
            $this->template->assign('order_description', $order_table);
            $this->template->assign('id', $realty_id);
            $this->template->assign('edit_url', $this->getServerFullUrl() . '/account/data/?do=edit&id=' . $realty_id);
            $email_template_fetched = $this->fetch_email_template('mailbox_object_order_detailed');

            if ($email_template_fetched) {
                $subject = $email_template_fetched['subject'];
                $body = $email_template_fetched['message'];

                $message_array['apps_name'] = 'need_moderate';
                $message_array['method'] = __METHOD__;
                $message_array['message'] = "subject = $subject, message = $body";
                $message_array['type'] = '';
                //$this->writeLog($message_array);
            }

            $this->sendFirmMail($n_email, $from, $subject, $body);
        }

        return json_encode(array('answer' => 'sended'));
    }
    
    public function sendPM($from, $to, $params){
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model[$this->table_name];
    	foreach($form_data as $k=>$v){
    		if(isset($params[$k])){
    			$form_data[$k]['value']=$params[$k];
    			$form_data[$k]['required']=0;
    		}
    	}
    	$form_data['creation_date']['value']=date('Y-m-d H:i:s',time());
    	$form_data['status']['value']=0;
    	$form_data['sender_id']['value']=$from;
    	$form_data['reciever_id']['value']=$to;
    	return $this->add_data($form_data);
	}
	
    function send_friend_message() {
        return;
        return json_encode(array('answer' => 'deprecated'));
    }

    private function conv($n) {
        return SiteBill::iconv(SITE_ENCODING, "utf-8", $n);
    }

}