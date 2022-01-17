<?php

/**
 * contactus form
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class contactus_Form extends Object_Manager {

    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        $this->table_name = 'contactus';
        $this->action = 'contactus';
        $this->primary_key = 'contactus_id';
        $this->data_model = $this->get_contactus_model();
        if ( isset($this->data_model[$this->table_name]['captcha']) ) {
            $this->data_model[$this->table_name]['captcha']['dbtype'] = 'notable';
        }
    }

    function main() {
        $this->template->assert('title', _e('Напишите нам'));
        $this->template->assert('meta_title', _e('Напишите нам'));
        $breadcrumbs = array();
        $breadcrumbs[] = array('title' => Multilanguage::_('L_HOME'), 'href' => $this->createUrlTpl(''));
        $breadcrumbs[] = array('title' => _e('Напишите нам'), 'href' => '');
        if (!empty($breadcrumbs)) {
            $bc_ar = array();
            foreach ($breadcrumbs as $bc) {
                if ($bc['href'] != '') {
                    $bc_ar[] = '<a href="' . $bc['href'] . '">' . $bc['title'] . '</a>';
                } else {
                    $bc_ar[] = $bc['title'];
                }
            }
        }
        $this->template->assign('breadcrumbs', implode(' / ', $bc_ar));
        $this->template->assign('complex_breadcrumbs', $breadcrumbs);
        return parent::main();
    }

    /*
     * Формирует код формы для вставки в вспомогательные формы не на родной странице
     * параметры формы
     */

    function get_order_form($params = array()) {
        $rs = '';
        $form_data = $this->data_model;
        $form_data = $form_data[$this->table_name];
        //$rs .= $this->get_form($form_data[$this->table_name], 'new', 0, Multilanguage::_('L_TEXT_SEND'), SITEBILL_MAIN_URL . '/contactus/');


        global $smarty;
        if ($button_title == '') {
            $button_title = Multilanguage::_('L_TEXT_SEND');
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();
        if (isset($params['form_id'])) {
            $form_id = $params['form_id'];
        } else {
            $form_id = 'id' . md5(time());
        }
        if (isset($params['onsuccess'])) {
            $onsuccess = $params['onsuccess'] . '("' . $form_id . '");';
        } else {
            $onsuccess = '';
        }

        $rs .= $this->get_ajax_functions();
        $rs .= '<script>$(document).ready(function(){
            var form=$("form#' . $form_id . '");
                var errorb=form.find(".error");
        
		
		errorb.hide();
		
		form.submit(function(e){
			var data=SitebillCore.serializeFormJSON(form);
			data.action="save_contactus";
			$.ajax({
				url: estate_folder+"/js/ajax.php",
				data: data,
				dataType: "json",
				type: "post",
				success: function(json){
					if(json.status==1){
                    
						form.html(json.msg);
                        ' . $onsuccess . '
					}else{
						errorb.html(json.msg).show();
					}
				}
			});
			e.preventDefault();
		});})</script>';
        $rs .= '<form id="' . $form_id . '" method="post" class="' . (isset($params['form_class']) ? $params['form_class'] : 'form-horizontal') . '" action="' . $action . '" enctype="multipart/form-data">';
        $rs .= '<div class="error"></div>';
        $el = $form_generator->compile_form_elements($form_data);

        //$el['private'][] = array('html' => '<input type="hidden" name="do" value="new_done" />');
        //$el['private'][] = array('html' => '<input type="hidden" name="' . $this->primary_key . '" value="' . $this->getRequestValue($this->primary_key) . '" />');


        $el['form_header'] = $rs;
        $el['form_header_action'] = $action;
        $el['form_header_class'] = 'form-horizontal';
        $el['form_header_enctype'] = 'multipart/form-data';
        $el['form_footer'] = '</form>';

        /* if ( $do != 'new' ) {
          $el['controls']['apply']=array('html'=>'<button id="apply_changes" class="btn btn-info">'.Multilanguage::_('L_TEXT_APPLY').'</button>');
          } */
        $el['controls']['submit'] = array('html' => '<button id="formsubmit" name="submit" class="btn btn-primary">' . $button_title . '</button>');

        if ($this->getConfigValue('post_form_agreement_enable') == 1) {
            $el['agreement_block'] = $form_generator->getAgreementFormBlock();
        }

        $smarty->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/contactus_form.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/contactus_form.tpl';
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl';
        } else {
            $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
        }
        return $smarty->fetch($tpl_name);

        return $rs;
    }

    /**
     * Main
     * @param void
     * @return string
     */
    /* function main () {
      require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
      $data_model = new Data_Model();
      $form_data = $this->data_model;

      $rs = $this->getTopMenu();

      switch( $this->getRequestValue('do') ){
      case 'new_done' : {

      $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
      //echo '<pre>';
      //print_r($form_data['data']);

      if ( !$this->check_data( $form_data[$this->table_name] ) ) {
      $rs .= $this->get_form($form_data[$this->table_name], 'new', 0, Multilanguage::_('L_TEXT_SEND'), SITEBILL_MAIN_URL.'/contactus/');

      } else {
      $order_table = $this->add_data($form_data[$this->table_name]);
      $subject = $_SERVER['SERVER_NAME'].': '.Multilanguage::_('NEW_MESSAGE_FROM_SITE','system');
      $to = $this->getConfigValue('order_email_acceptor');
      $from = $this->getConfigValue('system_email');
      $this->sendFirmMail($to, $from, $subject, $order_table);
      $rs = '<h1>'.Multilanguage::_('MESSAGE_SENT','system').'</h1>';
      $rs .= $order_table;

      }
      break;
      }

      default : {
      $rs .= $this->get_form($form_data[$this->table_name], 'new', 0, Multilanguage::_('L_TEXT_SEND'), SITEBILL_MAIN_URL.'/contactus/');
      }
      }
      return $rs;
      } */

    protected function _defaultAction() {
        $rs = '';
        $form_data = $this->data_model;
        $rs .= $this->get_form($form_data[$this->table_name], 'new', 0, Multilanguage::_('L_TEXT_SEND'), $this->createUrlTpl('contactus'));
        return $rs;
    }

    protected function _new_doneAction() {
        $rs = '';
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
        if (!$this->check_data($form_data[$this->table_name])) {
            $rs .= $this->get_form($form_data[$this->table_name], 'new', 0, Multilanguage::_('L_TEXT_SEND'), $this->createUrlTpl('contactus'));
        } else {

            $order_table = $this->add_data($form_data[$this->table_name]);
            if ($order_table !== false) {
                if (isset($form_data[$this->table_name]['theme']) && $form_data[$this->table_name]['theme']['type'] == 'select_box' && $form_data[$this->table_name]['theme']['value'] != 0) {
                    $subject = $_SERVER['SERVER_NAME'] . ': ' . Multilanguage::_('NEW_MESSAGE_FROM_SITE', 'system') . ' - ' . $form_data[$this->table_name]['theme']['value_string'];
                    //$subject = $this->getConfigValue('site_title').': '.Multilanguage::_('NEW_MESSAGE_FROM_SITE', 'system').' - '.$form_data[$this->table_name]['theme']['value_string'];
                } else {
                    $subject = $_SERVER['SERVER_NAME'] . ': ' . Multilanguage::_('NEW_MESSAGE_FROM_SITE', 'system');
                }

                /* $attachments=array();
                  if(isset(isset($form_data[$this->table_name]['documents']))){
                  foreach($form_data[$this->table_name]['documents'] as $doc)
                  } */

                $to = $this->getConfigValue('order_email_acceptor');
                $from = $this->getConfigValue('system_email');
                $this->sendFirmMail($to, $from, $subject, $order_table);
                if (Multilanguage::is_set('CONTACTUS_MESSAGE_SENT')) {
                    $msg = Multilanguage::_('CONTACTUS_MESSAGE_SENT');
                } else {
                    $rs = $this->getSaveSuccessMessage();
                }
            } else {
                $rs = _e('Отправить сообщение в данный момент невозможно. Повторите попытку позже.');
            }
        }
        return $rs;
    }

    function getSaveSuccessMessage() {
        if ( $this->getConfigValue('apps.client.thankyou_url') != '' ) {
            return $this->get_ThankYou_Url_redirect();
        } else {
            return Multilanguage::_('MESSAGE_SENT', 'system');
        }
    }

    function get_ThankYou_Url_redirect () {
        $rs = '<script>
            window.location.href = "'.$this->getConfigValue('apps.client.thankyou_url').'";
        </script>';
        return $rs;
    }


    /**
     * Get top menu
     * @param void
     * @return string
     */
    function getTopMenu() {
        /* $rs = '';
          $rs .= '<h1>Напишите нам</h1>';
          return $rs; */
    }

    /*
     * Обработка формы отправленной через ajax
     */

    function save_message() {
        $rs = '';
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
        if (!$this->check_data($form_data[$this->table_name])) {
            return json_encode(array('status' => 0, 'msg' => $this->GetErrorMessage()));
        } else {

            $order_table = $this->add_data($form_data[$this->table_name]);
            if ($order_table !== false) {
                if (isset($form_data[$this->table_name]['theme']) && $form_data[$this->table_name]['theme']['type'] == 'select_box' && $form_data[$this->table_name]['theme']['value'] != 0) {
                    $subject = $_SERVER['SERVER_NAME'] . ': ' . Multilanguage::_('NEW_MESSAGE_FROM_SITE', 'system') . ' - ' . $form_data[$this->table_name]['theme']['value_string'];
                } else {
                    $subject = $_SERVER['SERVER_NAME'] . ': ' . Multilanguage::_('NEW_MESSAGE_FROM_SITE', 'system');
                }
                $to = $this->getConfigValue('order_email_acceptor');
                $from = $this->getConfigValue('system_email');
                $this->sendFirmMail($to, $from, $subject, $order_table);
                if (Multilanguage::is_set('CONTACTUS_MESSAGE_SENT')) {
                    $msg = Multilanguage::_('CONTACTUS_MESSAGE_SENT');
                } else {
                    $msg = Multilanguage::_('MESSAGE_SENT', 'system');
                }
                return json_encode(array('status' => 1, 'msg' => $msg));
            } else {
                //$rs='Отправить сообщение в данный момент невозможно. Повторите попытку позже.';
                return json_encode(array('status' => 0, 'msg' => _e('Отправить сообщение в данный момент невозможно. Повторите попытку позже.')));
            }


            //$rs .= $order_table;
        }
        return $rs;
    }

    /* function getFrontalForm(){
      $rs = '';
      $form_data = $this->data_model;
      $rs .= $this->get_form($form_data[$this->table_name], 'new', 0, Multilanguage::_('L_TEXT_SEND'), SITEBILL_MAIN_URL . '/contactus/');
      return $rs;
      } */

    /*
     * Проверка является ли приложение записывающим в свою таблицу в БД или
     * только собирающим данные для отправки письма
     */

    function isWritableTable() {
        $writable = false;
        $DBC = DBC::getInstance();
        $query = 'SHOW TABLES LIKE ?';
        $stmt = $DBC->query($query, array(DB_PREFIX . '_' . $this->table_name));
        if ($stmt) {
            $writable = true;
        }
        return $writable;
    }

    /**
     * Add data
     * @param array $form_data form data
     * @return boolean
     */
    function add_data($form_data, $language_id = 0) {

        //var_dump($this->isWritableTable());
        /* require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
          $data_model = new Data_Model(); */
        if ($this->isWritableTable()) {
            //echo 1;
            $id = parent::add_data($form_data);
            if (!$id) {
                return false;
            }
            $DBC = DBC::getInstance();
            $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET `created_at`=? WHERE `' . $this->primary_key . '`=?';
            $stmt = $DBC->query($query, array(date('Y-m-d H:i:s'), $id));
        }

        if ($this->isWritableTable()) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            $data_model = new Data_Model();
            $primary_key_name = '';
            foreach ($form_data as $model_element) {
                if ($model_element['type'] == 'primary_key') {
                    $primary_key_name = $model_element['name'];
                }
            }
            $form_data = $data_model->init_model_data_from_db($this->table_name, $primary_key_name, $id, $form_data);
            unset($form_data[$primary_key_name]);
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/view.php');
        $table_view = new Table_View();
        $table_view->setAbsoluteUrls();
        $rs .= '<table border="1" cellpadding="2" cellspacing="2" style="border: 1px solid gray;">';
        $rs .= $table_view->compile_view($form_data);
        $rs .= '</table>';



        return $rs;
    }

    /*
     * Возвращает модель объекта обратной связи из БД или из кода
     */

    function get_contactus_model($ajax = false) {
        $form_data = array();
        $table_name = 'contactus';
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($table_name, false);


            if (empty($form_data)) {
                $form_data = array();
                $form_data = $this->_get_contactus_model($ajax);
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, $table_name);
                $form_data = array();
                $form_data = $ATH->load_model($table_name, $ignore_user_group);
            }

            $form_data = $ATH->add_ajax($form_data);
        } else {
            $form_data = $this->_get_contactus_model($ajax);
        }
        return $form_data;
    }

    /**
     * Check data
     * @param array $form_data
     * @return boolean
     */
    function check_data($form_data/* , &$error_fields=array() */) {
        $check_status = parent::check_data($form_data);
        if (!$check_status) {
            return $check_status;
        }
        if ($this->getConfigValue('apps.akismet.enable')) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/akismet/admin/admin.php');
            $akismet_admin = new akismet_admin();
            if ($akismet_admin->akismet_check($form_data['text']['value'] . ' ' . $form_data['email']['value'])) {
                $this->riseError($akismet_admin->GetErrorMessage());
                return false;
            }
        }
        return true;
    }

    /**
     * Get contactus model
     * @param boolean $ajax mode
     * @return array
     */
    function _get_contactus_model($ajax = false) {
        $form_data = array();

        $form_data['contactus']['id']['name'] = 'id';
        $form_data['contactus']['id']['title'] = Multilanguage::_('L_ID');
        $form_data['contactus']['id']['value'] = 0;
        $form_data['contactus']['id']['length'] = 40;
        $form_data['contactus']['id']['type'] = 'primary_key';
        $form_data['contactus']['id']['required'] = 'off';
        $form_data['contactus']['id']['unique'] = 'off';

        $form_data['contactus']['fio']['name'] = 'fio';
        $form_data['contactus']['fio']['title'] = Multilanguage::_('NAME_OR_COMPANY_NAME', 'system');
        $form_data['contactus']['fio']['value'] = '';
        $form_data['contactus']['fio']['length'] = 40;
        $form_data['contactus']['fio']['type'] = 'safe_string';
        $form_data['contactus']['fio']['required'] = 'on';
        $form_data['contactus']['fio']['unique'] = 'off';

        $form_data['contactus']['phone']['name'] = 'phone';
        $form_data['contactus']['phone']['title'] = Multilanguage::_('L_PHONE');
        $form_data['contactus']['phone']['value'] = '';
        $form_data['contactus']['phone']['length'] = 40;
        $form_data['contactus']['phone']['type'] = 'safe_string';
        $form_data['contactus']['phone']['required'] = 'on';
        $form_data['contactus']['phone']['unique'] = 'off';

        $form_data['contactus']['email']['name'] = 'email';
        $form_data['contactus']['email']['title'] = Multilanguage::_('L_EMAIL');
        $form_data['contactus']['email']['value'] = '';
        $form_data['contactus']['email']['length'] = 40;
        $form_data['contactus']['email']['type'] = 'safe_string';
        $form_data['contactus']['email']['required'] = 'on';
        $form_data['contactus']['email']['unique'] = 'off';

        $form_data['contactus']['text']['name'] = 'text';
        $form_data['contactus']['text']['title'] = Multilanguage::_('L_TEXT');
        $form_data['contactus']['text']['value'] = '';
        $form_data['contactus']['text']['length'] = 40;
        $form_data['contactus']['text']['type'] = 'textarea';
        $form_data['contactus']['text']['required'] = 'on';
        $form_data['contactus']['text']['unique'] = 'off';
        $form_data['contactus']['text']['rows'] = '10';
        $form_data['contactus']['text']['cols'] = '40';

        $form_data['contactus']['captcha']['name'] = 'captcha';
        $form_data['contactus']['captcha']['title'] = Multilanguage::_('L_CAPTCHA');
        $form_data['contactus']['captcha']['value'] = '';
        $form_data['contactus']['captcha']['length'] = 40;
        $form_data['contactus']['captcha']['type'] = 'captcha';
        $form_data['contactus']['captcha']['required'] = 'on';
        $form_data['contactus']['captcha']['unique'] = 'off';


        //$item_array['action'], $item_array['table_name'], $item_array['primary_key'], $item_array['primary_key_value'];

        return $form_data;
    }

}
