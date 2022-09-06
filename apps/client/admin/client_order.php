<?php

class Client_Order extends client_site {

    public function makeClientOrder($order_model) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->loadOrderModel($order_model);

        if (empty($form_data)) {
            return false;
        }

        switch ($this->getRequestValue('do')) {
            case 'new_done' : {
                    $pk = 0;
                    $form_data = $data_model->init_model_data_from_request($form_data);
                    try {
                        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/customentity/admin/admin.php')) {
                            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/customentity/admin/admin.php');

                            $form_data_entity = $this->loadOnlyEntityModel($order_model);
                            if ($form_data_entity) {
                                $form_data_entity = $data_model->init_model_data_from_request($form_data_entity);

                                $customentity = new customentity_admin();
                                $customentity->custom_construct($order_model);
                                if ($this->check_data($form_data_entity)) {
                                    $pk = $customentity->add_data($form_data_entity);
                                }

                                if ($customentity->getError()) {
                                    $this->writeLog(array('apps_name' => 'apps.client', 'method' => __METHOD__, 'message' => $customentity->GetErrorMessage(), 'type' => 'ERROR'));
                                }
                            }
                        }
                    } catch (Exception $e) {
                        $this->writeLog(array('apps_name' => 'apps.client', 'method' => __METHOD__, 'message' => $e->getMessage(), 'type' => 'ERROR'));
                    }

                    $new_values = $this->getRequestValue('_new_value');
                    if (1 == $this->getConfigValue('use_combobox') && count($new_values) > 0) {
                        $remove_this_names = array();
                        foreach ($form_data as $fd) {
                            if (isset($new_values[$fd['name']]) && $new_values[$fd['name']] != '' && $fd['combo'] == 1) {
                                $id = md5(time() . '_' . rand(100, 999));
                                $remove_this_names[] = $id;
                                $form_data[$id]['value'] = $new_values[$fd['name']];
                                $form_data[$id]['type'] = 'auto_add_value';
                                $form_data[$id]['dbtype'] = 'notable';
                                $form_data[$id]['value_table'] = $form_data[$fd['name']]['primary_key_table'];
                                $form_data[$id]['value_primary_key'] = $form_data[$fd['name']]['primary_key_name'];
                                $form_data[$id]['value_field'] = $form_data[$fd['name']]['value_name'];
                                $form_data[$id]['assign_to'] = $fd['name'];
                                $form_data[$id]['required'] = 'off';
                                $form_data[$id]['unique'] = 'off';
                            }
                        }
                    }
                    $data_model->forse_auto_add_values($form_data);
                    if (!$this->check_data($form_data) || (1 == $this->getConfigValue('filter_double_data') && !$this->checkUniquety($form_data))) {
                        $form_data = $this->removeTemporaryFields($form_data, $remove_this_names);
                        $rs = $this->get_form($form_data, 'new', 0, Multilanguage::_('L_TEXT_SEND'));
                    } else {
                        $primary_key_name = '';
                        foreach ($form_data as $k => $v) {
                            if ($v['type'] == 'hidden') {
                                $form_data[$k]['type'] = 'safe_string';
                            }
                            if ($v['type'] == 'primary_key' && $pk != 0) {
                                $form_data[$k]['value'] = $pk;
                                $primary_key_name = $form_data[$k]['name'];
                            }
                        }

                        require_once ((SITEBILL_DOCUMENT_ROOT . '/apps/client/admin/admin.php'));
                        $client_admin = new client_admin();

                        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/view.php');
                        $table_view = new Table_View();


                        if ($form_data_entity && $pk != 0) {
                            $form_data = $data_model->init_model_data_from_db($order_model, $primary_key_name, $pk, $form_data);
                        }

                        $order_table = '';
                        $order_table .= '<table border="1" cellpadding="2" cellspacing="2" style="border: 1px solid gray;">';
                        $table_view->setAbsoluteUrls();
                        $order_table .= $table_view->compile_view($form_data);
                        $order_table .= '</table>';
                        $subject = $_SERVER['SERVER_NAME'] . ': '._e('Новая заявка от клиента').' / ' . $client_admin->data_model['client']['type_id']['select_data'][$order_model];
                        $to = $this->get_email_list();
                        $from = $this->getConfigValue('system_email');
                        $this->sendFirmMail($to, $from, $subject, $order_table);

                        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/client/client.xml')) {
                            require_once ((SITEBILL_DOCUMENT_ROOT . '/apps/client/admin/admin.php'));
                            $client_admin = new client_admin();

                            $client_admin->data_model['client']['type_id']['value'] = $order_model;
                            $client_admin->data_model['client']['status_id']['value'] = 'new';
                            $client_admin->data_model['client']['date']['value'] = time();
                            $client_admin->data_model['client']['fio']['value'] = $form_data['fio']['value'];
                            $client_admin->data_model['client']['email']['value'] = $form_data['email']['value'];
                            $client_admin->data_model['client']['phone']['value'] = $form_data['phone']['value'];
                            unset($form_data['fio']);
                            unset($form_data['email']);
                            unset($form_data['phone']);
                            $client_admin->data_model['client']['order_text']['value'] = $order_table;

                            $client_admin->add_data($client_admin->data_model['client']);
                            if ($client_admin->getError()) {
                                $rs = $client_admin->GetErrorMessage();
                            } else {
                                if (1 == $this->getConfigValue('apps.client.allow-redirect_url_for_orders')) {
                                    header('location: ' . SITEBILL_MAIN_URL . '/client/order/' . $order_model . '/online-' . $order_model . '/');
                                    exit();
                                } else {
                                    $rs = $this->getSaveSuccessMessage();
                                }
                            }
                        }
                    }
                    break;
                }
            default : {
                    $rs = $this->get_form($form_data, 'new', 0, Multilanguage::_('L_TEXT_SEND'));
                    break;
                }
        }
        return $rs;
    }

    /**
     * Check data
     * @param array $form_data
     * @return boolean
     */
    function check_data($form_data/* , &$error_fields=array() */) {
        //echo '<h1>check</h1>';
        $check_status = parent::check_data($form_data);
        if (!$check_status) {
            return $check_status;
        }
        if ( $this->getConfigValue('apps.client.antispam_disable') ) {
            return true;
        }
        if ($this->getConfigValue('apps.akismet.enable')) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/akismet/admin/admin.php');
            $akismet_admin = new akismet_admin();
            $check_string = false;
            foreach ($form_data as $item => $item_array) {
                if (in_array($form_data[$item]['type'], array('safe_string', 'textarea', 'textarea_editor'))) {
                    //echo $form_data[$item]['value'].'<br>';
                    $check_string .= ' ' . $form_data[$item]['value'];
                }
            }
            //echo $check_string;
            if ($check_string) {
                if ($akismet_admin->akismet_check($check_string)) {
                    $this->riseError($akismet_admin->GetErrorMessage());
                    return false;
                }
            }
        }
        return true;
    }

    function save_order_form($order_model) {
        if (in_array($order_model, array('data', 'city', 'country', 'region', 'user'))) {
            return '';
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        $form_data = $this->loadOrderModel($order_model);

        if (!$form_data) {
            return;
        }
        $form_data = $data_model->init_model_data_from_request($form_data);

        $form_data_entity = false;
        $pk = 0;
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/customentity/admin/admin.php')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/customentity/admin/admin.php');

            $form_data_entity = $this->loadOnlyEntityModel($order_model);
            if ($form_data_entity) {
                $form_data_entity = $data_model->init_model_data_from_request($form_data_entity);

                $customentity = new customentity_admin();
                $customentity->custom_construct($order_model);
                if ($this->check_data($form_data_entity)) {
                    $pk = $customentity->add_data($form_data_entity);
                    if (isset($form_data['captcha'])) {
                        unset($form_data['captcha']);
                    }
                }/* else{
                  var_dump($this->GetErrorMessage());
                  } */
            }
        }

        $new_values = $this->getRequestValue('_new_value');
        if (1 == $this->getConfigValue('use_combobox') && count($new_values) > 0) {
            $remove_this_names = array();
            foreach ($form_data as $fd) {
                if (isset($new_values[$fd['name']]) && $new_values[$fd['name']] != '' && $fd['combo'] == 1) {
                    $id = md5(time() . '_' . rand(100, 999));
                    $remove_this_names[] = $id;
                    $form_data[$id]['value'] = $new_values[$fd['name']];
                    $form_data[$id]['type'] = 'auto_add_value';
                    $form_data[$id]['dbtype'] = 'notable';
                    $form_data[$id]['value_table'] = $form_data[$fd['name']]['primary_key_table'];
                    $form_data[$id]['value_primary_key'] = $form_data[$fd['name']]['primary_key_name'];
                    $form_data[$id]['value_field'] = $form_data[$fd['name']]['value_name'];
                    $form_data[$id]['assign_to'] = $fd['name'];
                    $form_data[$id]['required'] = 'off';
                    $form_data[$id]['unique'] = 'off';
                }
            }
        }
        $data_model->forse_auto_add_values($form_data);
        if (!$this->check_data($form_data)) {
            $this->writeLog(array('apps_name' => 'apps.client', 'method' => __METHOD__, 'message' => 'check_error: ' . $this->GetErrorMessage(), 'type' => ERROR));

            return json_encode(array('status' => 'error', 'message' => $this->GetErrorMessage()));
            $form_data = $this->removeTemporaryFields($form_data, $remove_this_names);
            $rs = $this->get_form($form_data, 'new', 0, Multilanguage::_('L_TEXT_SEND'));
        } else {
            $primary_key_name = '';
            foreach ($form_data as $k => $v) {
                if ($v['type'] == 'hidden') {
                    $form_data[$k]['type'] = 'safe_string';
                }
                if ($v['type'] == 'primary_key' && $pk != 0) {
                    $form_data[$k]['value'] = $pk;
                    $primary_key_name = $form_data[$k]['name'];
                }
            }

            if ($form_data_entity && $pk != 0) {
                $form_data = $data_model->init_model_data_from_db($order_model, $primary_key_name, $pk, $form_data_entity, true);
            }

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/view.php');
            $table_view = new Table_View();
            $table_view->setAbsoluteUrls();
            $order_table = '';
            $order_table .= '<table border="1" cellpadding="2" cellspacing="2" style="border: 1px solid gray;">';
            $order_table .= $table_view->compile_view($form_data);
            $order_table .= '</table>';

            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/client/client.xml')) {
                require_once ((SITEBILL_DOCUMENT_ROOT . '/apps/client/admin/admin.php'));
                $client_admin = new client_admin();

                $client_admin->data_model['client']['type_id']['value'] = $order_model;
                $client_admin->data_model['client']['status_id']['value'] = 'new';
                $client_admin->data_model['client']['date']['value'] = time();
                $client_admin->data_model['client']['fio']['value'] = $form_data['fio']['value'];
                $client_admin->data_model['client']['email']['value'] = $form_data['email']['value'];
                $client_admin->data_model['client']['phone']['value'] = $form_data['phone']['value'];
                if (isset($client_admin->data_model['client']['ip'])) {
                    if ('' != $_SERVER['HTTP_X_FORWARDED_FOR']) {
                        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
                    } else {
                        $ip = $_SERVER['REMOTE_ADDR'];
                    }
                    $client_admin->data_model['client']['ip']['value'] = $ip;
                }
                if (isset($client_admin->data_model['client']['src_page'])) {
                    $client_admin->data_model['client']['src_page']['value'] = $_SERVER['HTTP_REFERER'];
                }
                unset($form_data['fio']);
                unset($form_data['email']);
                unset($form_data['phone']);
                $client_admin->data_model['client']['order_text']['value'] = $order_table;

                $client_order_id = $this->add_record_to_client_table($client_admin);
                $this->writeLog(array('apps_name' => 'apps.client', 'method' => __METHOD__, 'message' => 'add client record', 'type' => NOTICE));

                if ($client_admin->getError()) {
                    $rs = $client_admin->GetErrorMessage();
                    $this->writeLog(array('apps_name' => 'apps.client', 'method' => __METHOD__, 'message' => 'client_add_error: ' . $client_admin->GetErrorMessage(), 'type' => ERROR));
                    return json_encode(array('status' => 'error', 'message' => '<div class="alert alert-success">' . $client_admin->GetErrorMessage() . '</div>'));
                } else {
                    $subject = $_SERVER['SERVER_NAME'] . ': '._e('Новая заявка от клиента').' / ' . $client_admin->data_model['client']['type_id']['select_data'][$order_model];
                    $to = $this->get_email_list();
                    $_owner_user_id = intval($this->request()->get('_owner_user_id'));
                    if ( $_owner_user_id > 0 ) {
                        $owner_user_record = \system\lib\model\eloquent\User::where('user_id', '=', $_owner_user_id)->first();
                        if ( $owner_user_record->email ) {
                            $to[$owner_user_record->email] = $owner_user_record->email;
                        }
                    }

                    $from = $this->getConfigValue('system_email');
                    $order_mail_body = $order_table;
                    if (isset($client_admin->data_model['client']['src_page'])) {
                        $order_mail_body.='<p>Заявка отправлена со страницы: '.$client_admin->data_model['client']['src_page']['value'].'</p>';
                    }
                    $this->writeLog(array('apps_name' => 'apps.client', 'method' => __METHOD__, 'message' => 'send_email to' . $to, 'type' => NOTICE));
                    $this->sendFirmMail($to, $from, $subject, $order_mail_body);

                    $this->afterClientOrderSave($order_model, $client_order_id, $form_data, $client_admin->data_model['client']);

                    return json_encode(
                        array(
                            'status' => 'ok',
                            'message' => $this->getSaveSuccessMessage()
                        )
                    );
                }
            } else {

            }
        }
    }

    function add_record_to_client_table ($client_admin) {
        return $client_admin->add_data($client_admin->data_model['client']);
    }

    function getSaveSuccessMessage() {
        if ( $this->getConfigValue('apps.client.thankyou_url') != '' ) {
            return $this->get_ThankYou_Url_redirect();
        } else {
            return '<div class="alert alert-success">' . Multilanguage::_('L_MESSAGE_ORDER_ACCEPTED_EXT') . '</div>';
        }
    }

    function get_ThankYou_Url_redirect () {
        $rs = '<script>
            window.location.href = "'.$this->getConfigValue('apps.client.thankyou_url').'";
        </script>';
        return $rs;
    }

    function afterClientOrderSave($order_model_name, $client_order_id, $form_data, $client_form_data){

    }

    /* function get_client_form($form, $options=array()){

      $DBC=DBC::getInstance();
      $query='SELECT * FROM '.DB_PREFIX.'_client_form WHERE client_form_id=?';
      $stmt=$DBC->query($query, array($form));
      if(!$stmt){
      return '';
      }
      $ar=$DBC->fetch($stmt);
      if($ar['active']==0){
      return '';
      }

      $form_data=$this->loadOrderModel($ar['form_model']);
      require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
      $form_generator = new Form_Generator();

      $el = $form_generator->compile_form_elements($form_data);
      global $smarty;
      $smarty->assign('form_elements',$el);
      if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl')){
      $tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl';
      }else{
      $tpl_name=$this->getAdminTplFolder().'/data_form.tpl';
      }
      return $smarty->fetch($tpl_name);


      if(in_array($model_name, array('data', 'city', 'country', 'region', 'user'))){
      return '';
      }
      $form_data=$this->loadOrderModel($model_name);
      if(!$form_data){
      return '';
      }
      if(!empty($options)){
      foreach ($options as $k=>$opt){
      if(isset($form_data[$k])){
      $form_data[$k]['value']=htmlspecialchars($opt);
      }
      }
      }
      //return $this->get_form($form_data, 'new');


      $_SESSION['allow_disable_root_structure_select']=true;
      global $smarty;
      if($button_title==''){
      $button_title = (Multilanguage::is_set('L_TEXT_SEND', 'system') ? Multilanguage::_('L_TEXT_SEND', 'system') : Multilanguage::_('L_TEXT_SEND'));
      }
      require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
      $data_model = new Data_Model();

      require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
      $form_generator = new Form_Generator();


      $rs .= $this->get_ajax_functions();
      if(1==$this->getConfigValue('apps.geodata.enable')){
      $rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/geodata/js/geodata.js"></script>';
      }
      $rs .= '<form method="post" class="form-horizontal" action="" enctype="multipart/form-data" id="client_form">';

      if ( $this->getError() ) {
      $smarty->assign('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
      }

      $el = $form_generator->compile_form_elements($form_data);


      $el['form_header']=$rs;
      $el['form_footer']='</form>';


      $el['controls']['submit']=array('html'=>'<input type="submit" class="btn btn-primary" value="'.$button_title.'">');





      $smarty->assign('form_elements',$el);
      if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl')){
      $tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl';
      }else{
      $tpl_name=$this->getAdminTplFolder().'/data_form.tpl';
      }
      return $smarty->fetch($tpl_name);


      //return $this->get_form($form_data, 'new');
      } */

    function get_order_form($model_name, $options = array(), $custom_template = false) {
        $rs = '';
        if (in_array($model_name, array('data', 'city', 'country', 'region', 'user'))) {
            return '';
        }
        $form_data = $this->loadOrderModel($model_name);
        if (!$form_data) {
            return '';
        }

        $options_form_class='form-horizontal';

        if (!empty($options)) {

            if(isset($options['form_class'])){
                $options_form_class=htmlspecialchars($options['form_class']);
            }

            foreach ($options as $k => $opt) {
                if (isset($form_data[$k])) {
                    $form_data[$k]['value'] = htmlspecialchars($opt);
                }
            }
        }
        $_SESSION['allow_disable_root_structure_select'] = true;
        global $smarty;
        if(isset($options['button_title'])){
            $button_title = htmlspecialchars($options['button_title']);
        }else{
            $button_title = (Multilanguage::is_set('L_TEXT_SEND', 'system') ? Multilanguage::_('L_TEXT_SEND', 'system') : Multilanguage::_('L_TEXT_SEND'));
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();


        $rs .= $this->get_ajax_functions();
        if (1 == $this->getConfigValue('apps.geodata.enable')) {
            $hasgeofield = false;
            foreach ($form_data as $item){
                if($item['type'] == 'geodata'){
                    $hasgeofield = true;
                    break;
                }
            }
            if(!$hasgeofield){
                $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/geodata/js/geodata.js"></script>';
            }
        }
        $rs .= '<form method="post" class="client_form '.$options_form_class.'" action="" enctype="multipart/form-data" id="client_form">';

        if ($this->getError()) {
            $this->template->assert('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
        }

        $el = $form_generator->compile_form_elements($form_data);


        $el['form_header'] = $rs;

        if ($this->getConfigValue('post_form_agreement_enable') == 1) {
            $el['agreement_block'] = $form_generator->getAgreementFormBlock();
        }
        $el['form_footer'] = '</form>';

        $el['controls']['submit'] = array('html' => '<input type="submit" class="btn btn-primary" value="' . $button_title . '">');
        $this->template->assert('form_elements', $el);

        $custom_template_name = '';
        if($custom_template){
            $custom_template_name = basename($custom_template);
            $custom_template_name = trim($custom_template_name, '.');
        }

        $tpl_name = '';

        //var_dump($custom_template_name);

        //Проверяем наличие шаблона по его имени
        if($custom_template_name != ''){
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/' . $custom_template_name . '.tpl')) {
                $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/' . $custom_template_name . '.tpl';
            }elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/resources/smartypages/' . $custom_template_name . '.tpl')) {
                $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/resources/smartypages/' . $custom_template_name . '.tpl';
            }
        }

        //Проверяем наличие шаблона по предопределенным именам модели формы
        if($tpl_name == ''){
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/' . $model_name . '_form.tpl')) {
                $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/' . $model_name . '_form.tpl';
            }elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/resources/smartypages/' . $model_name . '_form.tpl')) {
                $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/resources/smartypages/' . $model_name . '_form.tpl';
            }
        }

        //Проверяем наличие переопределенной общей формы
        if($tpl_name == ''){
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl')) {
                $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl';
            }
        }

        //Устанавливаем в качестве шаблона дефолтную системную форму
        if($tpl_name == ''){
            $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
        }

        //var_dump($tpl_name);



        /*if ($custom_template == '') {
            $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/' . $custom_template_name . '.tpl') and $custom_template) {
            //$tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/' . $custom_template_name . '.tpl';
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/' . $model_name . '_form.tpl')) {
            //$tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/' . $model_name . '_form.tpl';
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl')) {
            //$tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl';
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/resources/smartypages/' . $model_name . '_form.tpl')) {
            //$tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/resources/smartypages/' . $model_name . '_form.tpl';
        } else {
            $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
        }*/
        if ( $options['user_id'] ) {
            $_owner_user_id = '<input type="hidden" name="_owner_user_id" id="_owner_user_id" value="'.$options['user_id'].'">';
        } else {
            $_owner_user_id = '';
        }

        return $this->template->fetch($tpl_name).$_owner_user_id;
    }

    function get_form($form_data = array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php') {
        $rs = '';
        $_SESSION['allow_disable_root_structure_select'] = true;
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
        $rs .= '<form method="post" class="form-horizontal" action="" enctype="multipart/form-data" id="client_form">';

        if ($this->getError()) {
            $smarty->assign('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
        }

        $el = $form_generator->compile_form_elements($form_data);

        if ($do == 'new') {
            $el['private'][] = array('html' => '<input type="hidden" name="do" value="new_done" />');
            $el['private'][] = array('html' => '<input type="hidden" name="' . $this->primary_key . '" value="' . $this->getRequestValue($this->primary_key) . '" />');
        } else {
            $el['private'][] = array('html' => '<input type="hidden" name="do" value="edit_done" />');
            $el['private'][] = array('html' => '<input type="hidden" name="' . $this->primary_key . '" value="' . $form_data[$this->primary_key]['value'] . '" />');
        }
        $el['private'][] = array('html' => '<input type="hidden" name="action" value="' . $this->action . '">');
        $el['private'][] = array('html' => '<input type="hidden" name="language_id" value="' . $language_id . '">');
        if (!is_null($this->client_topic_id)) {
            $el['private'][] = array('html' => '<input type="hidden" name="topic_id" id="topic_id" value="' . $this->client_topic_id . '">');
        }


        $el['form_header'] = $rs;
        $el['form_footer'] = '</form>';
        if ($this->getConfigValue('post_form_agreement_enable') == 1) {
            $el['agreement_block'] = $form_generator->getAgreementFormBlock();
        }
        $el['controls']['submit'] = array('html' => '<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">' . $button_title . '</button>');
        $smarty->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl';
        } else {
            $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
        }
        return $smarty->fetch($tpl_name);
    }

    private function loadOnlyEntityModel($model_name, $ignore_user_group = false) {
        $DBC = DBC::getInstance();
        $query = 'SELECT COUNT(table_id) AS cnt FROM ' . DB_PREFIX . '_table WHERE name=?';
        $stmt = $DBC->query($query, array($model_name));
        if (!$stmt) {
            return false;
        }

        $ar = $DBC->fetch($stmt);
        if ($ar['cnt'] == 0) {
            return false;
        }

        $query = 'SELECT COUNT(entity_name) AS cnt FROM ' . DB_PREFIX . '_customentity WHERE entity_name=?';
        $stmt = $DBC->query($query, array($model_name));
        if (!$stmt) {
            return false;
        }

        $ar = $DBC->fetch($stmt);
        if ($ar['cnt'] == 0) {
            return false;
        }

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($model_name, $ignore_user_group);
            if ($form_data) {
                $form_data = $ATH->add_ajax($form_data);
            }
        }

        if (!$form_data) {
            return false;
        }
        return $form_data[$model_name];
    }

    protected function loadOrderModel($model_name) {

        $DBC = DBC::getInstance();
        $query = 'SELECT COUNT(table_id) AS cnt FROM ' . DB_PREFIX . '_table WHERE name=?';
        $stmt = $DBC->query($query, array($model_name));
        if (!$stmt) {
            return false;
        }

        $ar = $DBC->fetch($stmt);
        if ($ar['cnt'] == 0) {
            return false;
        }

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($model_name, false);
            if ($form_data) {
                $form_data = $ATH->add_ajax($form_data);
            }
        }

        if (!$form_data) {
            return false;
        }
        return $form_data[$model_name];
    }

}
