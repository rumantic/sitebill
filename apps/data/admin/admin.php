<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * data admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class data_admin extends Object_Manager {

    public $save_url = 'empty';

    /**
     * Constructor
     */
    function __construct($realty_type = false) {
        $this->SiteBill();
        Multilanguage::appendAppDictionary('data');
        $this->table_name = 'data';
        $this->action = 'data';
        $this->primary_key = 'id';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $this->data_model_object = $data_model;
        $this->data_model = $data_model->get_kvartira_model($this->getConfigValue('ajax_form_in_admin'));
        $this->data_model[$this->table_name]['user_id']['type'] = 'hidden';
        $this->data_model[$this->table_name]['user_id']['value'] = $this->getSessionUserId();
    }

    public function _preload() {
        
    }

    function grid($params = array(), $default_params = array()) {
        $REQUESTURIPATH = Sitebill::getClearRequestURI();
        if ($this->getConfigValue('apps.pdfreport.enabled')) {
            $this->template->assign('pdf_enable', 1);
        }
        //Устанавливаем параметр USER_ID для функции импорта XLS файла. 
        //Чтобы при загрузке из XLS пользоатель не смог получить доступ к чужим записям
        $_SESSION['politics']['data']['check_access'] = true;
        $_SESSION['politics']['data']['user_id'] = $this->getSessionUserId();

        $default_params['grid_item'] = array('id', 'topic_id', 'city_id', 'district_id', 'street_id', 'price', 'image');
        $default_params['render_user_id'] = $this->getSessionUserId();
        if (!preg_match('/all[\/]?$/', $REQUESTURIPATH)) {
            $params['grid_conditions']['user_id'] = $this->getSessionUserId();
        }

        $params['grid_controls'] = array('edit', 'delete', 'memorylist');
        $params['url'] = '/' . $REQUESTURIPATH;
        //$params['pager_url']='account/data';

        $rs = '
	
	    <link rel="stylesheet" href="/apps/admin/admin/template1/assets/css/font-awesome.min.css" />
			<link rel="stylesheet" href="/apps/data/css/style.css" />
	    <script src="/apps/system/js/bootstrap/js/bootstrap.min.js"></script>
	    
		
		<script src="/apps/admin/admin/template1/assets/js/bootstrap-tag.min.js"></script>
		
		<!-- ace scripts -->
		';
        if (isset($this->data_model[$this->table_name]['user_id'])) {
            $this->data_model[$this->table_name]['user_id']['type'] = 'select_by_query';
        }

        $DBC = DBC::getInstance();
        $used_fields = array();
        $query = 'SELECT `grid_fields` FROM ' . DB_PREFIX . '_table_grids WHERE `action_code`=?';
        $stmt = $DBC->query($query, array('data_user_' . $this->getSessionUserId()));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $used_fields = json_decode($ar['grid_fields']);
            $params['grid_item'] = $used_fields;
        }

        $rs .= parent::grid($params, $default_params);

        return $rs;
    }

    /**
     * Delete data
     * @param string $table_name
     * @param string $primary_key
     * @param int $primary_key_value
     */
    function delete_data($table_name, $primary_key, $primary_key_value) {
        if ($this->need_check_access($table_name)) {
            if (!$this->check_access($table_name, $this->get_check_access_user_id($table_name), 'delete', $primary_key, $primary_key_value)) {
                $this->riseError('ID = ' . $primary_key_value . ', ' . Multilanguage::_('L_ACCESS_DENIED'));
                return false;
            }
        }
        return parent::delete_data($table_name, $primary_key, $primary_key_value);
    }

    function _exportAction($input_params = array()) {
        $REQUESTURIPATH = Sitebill::getClearRequestURI();
        if (!preg_match('/all[\/]?$/', $REQUESTURIPATH)) {
            $params['grid_conditions']['user_id'] = $this->getSessionUserId();
        }
        parent::_exportAction($params);
    }

    function ajax() {

        return false;
    }
    
    /**
     * Edit data
     * @param array $form_data form data
     * @return boolean
     */
    function edit_data($form_data, $language_id = 0, $primary_key_value = false) {
        $id = intval($this->getRequestValue('id'));
        if ($id == 0) {
            return false;
        }
        $status_changed = false;

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
        $account = new Account();
        $user_balance = $account->getAccountValue($this->getSessionUserId());

        $form_data['price']['value'] = str_replace(' ', '', $form_data['price']['value']);

        $form_data_tmp = $form_data;

        //get prev state
        $form_data_tmp = $data_model->init_model_data_from_db('data', 'id', $id, $form_data_tmp);
        //if prev hot is 0 and new is 1, calculate money
        if ($form_data_tmp['hot']['value'] == 0 and $form_data['hot']['value'] == 1) {
            if ($user_balance < $this->getConfigValue('special_advert_cost')) {
                $this->riseError('Недостаточно средств на счете для операции. <a href="' . SITEBILL_MAIN_URL . '/account/balance/?do=add_bill_done&bill=' . $this->getConfigValue('special_advert_cost') . '">Пополнить баланс на ' . $this->getConfigValue('special_advert_cost') . ' ' . $this->getConfigValue('ue_name') . '</a>');
                return false;
            } else {
                $account->minusMoney($this->getSessionUserId(), $this->getConfigValue('special_advert_cost'));
            }
        }

        if (1 == $this->getConfigValue('moderate_first')) {
            $form_data['active']['value'] = 0;
        }

        if (1 == $this->getConfigValue('apps.geodata.try_encode') && 1 == $this->getConfigValue('apps.geodata.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/geodata/admin/admin.php';
            $GA = new geodata_admin();
            $form_data = $GA->try_geocode($form_data);
        }

        if (isset($form_data['status_id'])) {
            $current_status_id = 0;
            $DBC = DBC::getInstance();
            $query = 'SELECT status_id FROM ' . DB_PREFIX . '_data WHERE `id`=?';
            $stmt = $DBC->query($query, array($id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $current_status_id = intval($ar['status_id']);
            }

            if ($current_status_id !== intval($form_data['status_id']['value'])) {
                $status_changed = true;
            }
        }

        $queryp = $data_model->get_prepared_edit_query(DB_PREFIX . '_data', 'id', $id, $form_data);
        $DBC = DBC::getInstance();

        $row = 0;
        $success_mark = false;
        $stmt = $DBC->query($queryp['q'], $queryp['p'], $rows, $success_mark);
        if (!$success_mark) {
            $this->riseError($DBC->getLastError());
            return;
        }



        if (1 == $this->getConfigValue('moderate_first')) {
            $this->notifyAboutModerationNeed($id, 'edit');
        }

        if ($success_mark && $status_changed) {
            $this->setStatusDate($id);
        }

        if ($success_mark && 0 === intval($this->getConfigValue('apps.billing.enable'))) {
            $this->setUpdatedAtDate($id);
        }

        $imgs = array();

        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploads') {
                $ims = $this->appendUploads('data', $form_item, 'id', $id);
                if (is_array($ims) && count($ims) > 0) {
                    $imgs = array_merge($imgs, $ims);
                }
            }
        }

        $ims = $this->editImageMulti('data', 'data', 'id', $id);
        if (is_array($ims) && count($ims) > 0) {
            $imgs = array_merge($imgs, $ims);
        }



        if (1 == $this->getConfigValue('apps.seo.data_alias_enable') && ((isset($form_data['translit_alias']) && $form_data['translit_alias']['value'] == '') || !isset($form_data['translit_alias']))) {
            $this->saveTranslitAlias($id);
        }

        if ($this->getConfigValue('is_watermark')) {
            $filespath = SITEBILL_DOCUMENT_ROOT . '/img/data/';
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/watermark/watermark.php';
            $Watermark = new Watermark();
            $Watermark->setPosition($this->getConfigValue('apps.watermark.position'));
            $Watermark->setOffsets(array(
                $this->getConfigValue('apps.watermark.offset_left'),
                $this->getConfigValue('apps.watermark.offset_top'),
                $this->getConfigValue('apps.watermark.offset_right'),
                $this->getConfigValue('apps.watermark.offset_bottom')
            ));
            if (defined('STR_MEDIA') && STR_MEDIA == Sitebill::MEDIA_SAVE_FOLDER) {
                $copy_folder = MEDIA_FOLDER . '/nowatermark/';
                if (defined('STR_MEDIA_FOLDERFDAYS') && STR_MEDIA_FOLDERFDAYS === 1) {
                    $foldeformat = 'Ymd';
                } else {
                    $foldeformat = 'Ym';
                }
                $folder_name = date($foldeformat, time());
                $locs = $copy_folder . '/' . $folder_name;
                if (!is_dir($locs)) {
                    mkdir($locs);
                }
                if (1 == $this->getConfigValue('save_without_watermark') && !empty($imgs)) {
                    $copy_folder = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark';
                    foreach ($imgs as $v) {
                        copy($filespath . $v['normal'], $copy_folder . '/' . $v['normal']);
                    }
                }
                if (!empty($imgs)) {
                    foreach ($imgs as $v) {
                        $Watermark->printWatermark(MEDIA_FOLDER . '/' . $v['normal']);
                    }
                }
            } else {
                if (1 == $this->getConfigValue('save_without_watermark') && !empty($imgs)) {
                    $copy_folder = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/';
                    foreach ($imgs as $v) {
                        copy($filespath . $v['normal'], $copy_folder . $v['normal']);
                    }
                }
                if (!empty($imgs)) {
                    foreach ($imgs as $v) {
                        $Watermark->printWatermark($filespath . $v['normal']);
                    }
                }
            }
        }
    }
    
    public function setStatusDate($id, $date = '') {
        $DBC = DBC::getInstance();
        if ($date == '') {
            $date = date('Y-m-d H:i:s', time());
        }
        $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET status_change=? WHERE `' . $this->primary_key . '`=?';
        $stmt = $DBC->query($query, array($date, $id));
    }

    public function setUpdatedAtDate($id) {
        $field = trim($this->getConfigValue('apps.realty.updated_at_field'));
        $type = intval($this->getConfigValue('apps.realty.updated_at_field_type'));
        $update_date_added = intval($this->getConfigValue('apps.realty.update_date_added'));

        if ($field == '' && 1 === $update_date_added) {
            $field = 'date_added';
            $type = 0;
        }


        if ($field == '' || $type > 1) {
            return false;
        }

        $DBC = DBC::getInstance();
        if ($type == 1) {
            $date = time();
        } else {
            $date = date('Y-m-d H:i:s', time());
        }
        $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET `' . $field . '`=? WHERE `' . $this->primary_key . '`=?';
        $stmt = $DBC->query($query, array($date, $id));
        if ($stmt) {
            return true;
        }
        return false;
    }
    
    protected function _editAction() {
        $id = intval($this->getRequestValue('id'));
        $user_id = $this->getSessionUserId();

        $aggregroup = -1;
        $cgroup_id = intval($_SESSION['current_user_group_id']);

        $rs = '';
        if ($cgroup_id == $aggregroup) {
            if (!$this->check_access_to_aggregated_data($user_id, $id)) {
                return Multilanguage::_('L_ACCESS_DENIED');
            }
        } elseif (!$this->check_access_to_data($user_id, $id)) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }

        /* if ( !$this->check_access_to_data($user_id, $this->getRequestValue('id')) ) {
          return Multilanguage::_('L_ACCESS_DENIED');
          } */

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;


        if ($this->getConfigValue('more_fields_in_lk')) {
            $form_data = $this->init_more_fields($form_data);
        }
        $form_data['data']['fio']['required'] = 'off';


        if ($this->getConfigValue('special_advert_cost') > 0) {
            $form_data['data']['hot']['title'] = 'Спецразмещение<br> (стоимость размещения в блоке <b>' . $this->getConfigValue('special_advert_cost') . ' ' . $this->getConfigValue('ue_name') . '</b>)';
        }

        if ($this->getConfigValue('user_add_street_enable') != 1) {
            if (isset($form_data['data']['new_street'])) {
                unset($form_data['data']['new_street']);
            }
        }

        $_form_data = $form_data;
        $form_data['data'] = $data_model->init_model_data_from_db('data', 'id', $this->getRequestValue('id'), $form_data['data']);

        if (1 == $this->getConfigValue('divide_step_form') && isset($_POST['submit'])) {
            $_form_data['data'] = $data_model->init_model_data_from_request($_form_data['data']);
            foreach ($_form_data['data'] as $fdk => $fdv) {
                if ($fdv['type'] == 'uploadify_image') {
                    unset($_form_data['data'][$fdk]);
                }
            }
            $form_data['data'] = array_merge($form_data['data'], $_form_data['data']);
        }

        $form_data['data']['user_id']['type'] = 'hidden';
        unset($form_data['data']['view_count']);
        if ($this->getConfigValue('enable_special_in_account') != 1) {
            unset($form_data['data']['hot']);
        }

        $rs .= $this->get_form($form_data['data'], 'edit');
        if ($this->getConfigValue('apps.realtylog.enable')) {
            $rs .= '<h2>Лог изменений</h2>';
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/site/site.php';
            $Logger = new realtylog_site();
            $rs .= $Logger->getLogs($this->getRequestValue('id'), $user_id);
        }
        return $rs;
    }
    
    protected function _edit_doneAction() {
        $user_id = $this->getSessionUserId();
        $id = intval($this->getRequestValue('id'));

        $aggregroup = -1;
        $cgroup_id = intval($_SESSION['current_user_group_id']);

        $rs = '';
        if ($cgroup_id == $aggregroup) {
            if (!$this->check_access_to_aggregated_data($user_id, $id)) {
                return Multilanguage::_('L_ACCESS_DENIED');
            }
        } elseif (!$this->check_access_to_data($user_id, $id)) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }


        /* $rs='';
          if ( !$this->check_access_to_data($user_id, $this->getRequestValue('id')) ) {
          return Multilanguage::_('L_ACCESS_DENIED');
          } */

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;


        if ($this->getConfigValue('more_fields_in_lk')) {
            $form_data = $this->init_more_fields($form_data);
        }
        $form_data['data']['fio']['required'] = 'off';


        if ($this->getConfigValue('special_advert_cost') > 0) {
            $form_data['data']['hot']['title'] = 'Спецразмещение<br> (стоимость размещения в блоке <b>' . $this->getConfigValue('special_advert_cost') . ' ' . $this->getConfigValue('ue_name') . '</b>)';
        }

        if ($this->getConfigValue('user_add_street_enable') != 1) {
            if (isset($form_data['data']['new_street'])) {
                unset($form_data['data']['new_street']);
            }
        }
        if ($this->getConfigValue('enable_special_in_account') != 1) {
            unset($form_data['data']['hot']);
        }
        $form_data['data'] = $data_model->init_model_data_from_request($form_data['data']);




        $new_values = $this->getRequestValue('_new_value');
        if (1 == $this->getConfigValue('use_combobox') && count($new_values) > 0) {
            $remove_this_names = array();
            foreach ($form_data['data'] as $fd) {
                if (isset($new_values[$fd['name']]) && $new_values[$fd['name']] != '' && $fd['combo'] == 1) {
                    $id = md5(time() . '_' . rand(100, 999));
                    $remove_this_names[] = $id;

                    $form_data['data'][$id]['value'] = $new_values[$fd['name']];
                    $form_data['data'][$id]['type'] = 'auto_add_value';
                    $form_data['data'][$id]['dbtype'] = 'notable';
                    $form_data['data'][$id]['value_table'] = $form_data['data'][$fd['name']]['primary_key_table'];
                    $form_data['data'][$id]['value_primary_key'] = $form_data['data'][$fd['name']]['primary_key_name'];
                    $form_data['data'][$id]['value_field'] = $form_data['data'][$fd['name']]['value_name'];
                    $form_data['data'][$id]['assign_to'] = $fd['name'];
                    $form_data['data'][$id]['required'] = 'off';
                    $form_data['data'][$id]['unique'] = 'off';
                }
            }
        }


        $form_data['data']['user_id']['value'] = $user_id;

        $y_id = '';
        if (strpos($form_data['data']['youtube']['value'], 'youtube.com') !== FALSE) {
            $d = parse_url($form_data['data']['youtube']['value']);
            if (isset($d['query'])) {
                parse_str($d['query'], $a);
                $y_id = $a['v'];
            }
        } elseif (strpos($form_data['data']['youtube']['value'], 'youtu.be') !== FALSE) {
            $d = parse_url($form_data['data']['youtube']['value']);
            if (isset($d['path']) && trim($d['path'], '/') != '' && strpos(trim($d['path'], '/'), '/') === false) {
                $y_id = trim($d['path'], '/');
            }
        } else {

            if (preg_match('/.*([-_A-Za-z0-9]+).*/', $form_data['data']['youtube']['value'], $matches)) {
                $y_id = $matches[0];
            }
        }
        $form_data['data']['youtube']['value'] = $y_id;
        unset($form_data['data']['view_count']);
        if ($this->getConfigValue('enable_special_in_account') != 1) {
            unset($form_data['data']['hot']);
        }
        $data_model->forse_auto_add_values($form_data['data']);
        $data_model->forse_injected_values($form_data['data']);
        //$data_model->clear_auto_add_values($form_data['data']);
        $form_data['data'] = $this->_before_check_action($form_data['data'], 'edit');
        if (!$this->check_data($form_data['data'])) {
            $form_data['data'] = $this->removeTemporaryFields($form_data['data'], $remove_this_names);
            $rs = $this->get_form($form_data['data'], 'edit');
        } else {
            $form_data['data'] = $this->_before_edit_done_action($form_data['data']);
            $this->edit_data($form_data['data']);
            if ($this->getError()) {
                $form_data['data'] = $this->removeTemporaryFields($form_data['data'], $remove_this_names);
                $rs = $this->get_form($form_data['data'], 'edit');
            } else {

                if ($this->getConfigValue('apps.realtylog.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/admin/admin.php';
                    $Logger = new realtylog_admin();
                    $Logger->addLog($form_data['data']['id']['value'], $user_id, 'edit', 'data');
                }
                if ($this->getConfigValue('apps.realtylogv2.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                    $Logger = new realtylogv2_admin();
                    $Logger->addLog($form_data['data']['id']['value'], $user_id, 'edit', 'data', 'id');
                }
                header('Location: ' . SITEBILL_MAIN_URL . '/account/data/');
                die();
            }
        }
        return $rs;
    }
    

    protected function _new_doneAction() {
        /* $rtoken=$_POST['csrftoken'];
          $rhash=$_POST['csrfhash'];
          var_dump($rtoken);
          var_dump($rhash);

          if($rtoken==''){
          exit();
          }

          if(md5($rtoken.$_SESSION['csrfsecret'])!=$rhash){
          exit();
          } */



        $user_id = $this->getSessionUserId();
        $user_id = intval($_SESSION['user_id']);
        $rs = '';


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;


        if ($this->getConfigValue('more_fields_in_lk')) {
            $form_data = $this->init_more_fields($form_data);
        }

        if (isset($form_data['data']['fio'])) {
            $form_data['data']['fio']['required'] = 'off';
        }



        if ($this->getConfigValue('special_advert_cost') > 0 && isset($form_data['data']['hot'])) {
            $form_data['data']['hot']['title'] = 'Спецразмещение<br> (стоимость размещения в блоке <b>' . $this->getConfigValue('special_advert_cost') . ' ' . $this->getConfigValue('ue_name') . '</b>)';
        }

        if ($this->getConfigValue('user_add_street_enable') != 1) {
            if (isset($form_data['data']['new_street'])) {
                unset($form_data['data']['new_street']);
            }
        }
        if ($this->getConfigValue('enable_special_in_account') != 1) {
            unset($form_data['data']['hot']);
        }
        $form_data['data'] = $data_model->init_model_data_from_request($form_data['data']);

        $new_values = $this->getRequestValue('_new_value');
        if (1 == $this->getConfigValue('use_combobox') && count($new_values) > 0) {
            $remove_this_names = array();
            foreach ($form_data['data'] as $fd) {
                if (isset($new_values[$fd['name']]) && $new_values[$fd['name']] != '' && $fd['combo'] == 1) {
                    $id = md5(time() . '_' . rand(100, 999));
                    $remove_this_names[] = $id;
                    $form_data['data'][$id]['value'] = $new_values[$fd['name']];
                    $form_data['data'][$id]['type'] = 'auto_add_value';
                    $form_data['data'][$id]['dbtype'] = 'notable';
                    $form_data['data'][$id]['value_table'] = $form_data['data'][$fd['name']]['primary_key_table'];
                    $form_data['data'][$id]['value_primary_key'] = $form_data['data'][$fd['name']]['primary_key_name'];
                    $form_data['data'][$id]['value_field'] = $form_data['data'][$fd['name']]['value_name'];
                    $form_data['data'][$id]['assign_to'] = $fd['name'];
                    $form_data['data'][$id]['required'] = 'off';
                    $form_data['data'][$id]['unique'] = 'off';
                }
            }
        }


        $form_data['data']['user_id']['value'] = $user_id;
        $form_data['data']['user_id']['type'] = 'hidden';
        $form_data['data']['date_added']['value'] = date('Y-m-d H:i:s', time());


        $y_id = '';
        if (strpos($form_data['data']['youtube']['value'], 'youtube.com') !== FALSE) {
            $d = parse_url($form_data['data']['youtube']['value']);
            if (isset($d['query'])) {
                parse_str($d['query'], $a);
                $y_id = $a['v'];
            }
        } elseif (strpos($form_data['data']['youtube']['value'], 'youtu.be') !== FALSE) {
            $d = parse_url($form_data['data']['youtube']['value']);
            if (isset($d['path']) && trim($d['path'], '/') != '' && strpos(trim($d['path'], '/'), '/') === false) {
                $y_id = trim($d['path'], '/');
            }
        } else {

            if (preg_match('/.*([-_A-Za-z0-9]+).*/', $form_data['data']['youtube']['value'], $matches)) {
                $y_id = $matches[0];
            }
        }
        $form_data['data']['youtube']['value'] = $y_id;

        $data_model->forse_auto_add_values($form_data['data']);
        $data_model->forse_injected_values($form_data['data']);
        $form_data[$this->table_name] = $this->_before_check_action($form_data[$this->table_name]);
        if (!$this->check_data($form_data['data']) || (1 == $this->getConfigValue('filter_double_data') && !$this->checkUniquety($form_data['data']))) {

            $form_data['data'] = $this->removeTemporaryFields($form_data['data'], $remove_this_names);
            $rs = $this->get_form($form_data['data'], 'new');
        } else {
            $form_data[$this->table_name] = $this->_before_add_done_action($form_data['data']);
            $new_record_id = $this->add_data($form_data['data']);
            if ($this->getError()) {
                $form_data['data'] = $this->removeTemporaryFields($form_data['data'], $remove_this_names);
                $rs = $this->get_form($form_data['data']);
            } else {
                if ($this->getConfigValue('apps.realtylog.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/admin/admin.php';
                    $Logger = new realtylog_admin();
                    $Logger->addLog($new_record_id, $user_id, 'new', 'data');
                }
                if (1 == $this->getConfigValue('notify_about_added_realty')) {
                    $this->notifyUserAboutAdding($form_data['data']['user_id']['value'], $new_record_id, $form_data['data']['topic_id']['value']);
                }

                if ($this->getConfigValue('apps.realtylogv2.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                    $Logger = new realtylogv2_admin();
                    $Logger->addLog($new_record_id, $user_id, 'new', 'data', 'id');
                }
                header('Location: ' . SITEBILL_MAIN_URL . '/account/data/');
                die();
            }
        }
        return $rs;
    }

    protected function _deleteAction() {
        $user_id = $this->getSessionUserId();
        $id = intval($this->getRequestValue('id'));
        $rs = '';
        if (!$this->check_access_to_data($user_id, $id)) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }

        $data_model = new Data_Model();
        $model = $data_model->get_kvartira_model(false, true);

        if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting') && isset($model['data']['archived'])) {
            $DBC = DBC::getInstance();
            $query = 'UPDATE ' . DB_PREFIX . '_data SET `archived`=1 WHERE `id`=?';
            $stmt = $DBC->query($query, array($id));
            $this->setUpdatedAtDate($id);
        } else {
            if ($this->getConfigValue('apps.realtylog.enable')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/admin/admin.php';
                $Logger = new realtylog_admin();
                $Logger->addLog($id, $user_id, 'delete', 'data');
            }
            if ($this->getConfigValue('apps.realtylogv2.enable')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                $Logger = new realtylogv2_admin();
                $Logger->addLog($id, $user_id, 'delete', 'data', 'id');
            }
            $this->delete_data('data', 'id', $id);
        }
        header('location: ' . SITEBILL_MAIN_URL . '/account/data/');
        exit();
        $rs .= $this->grid($user_id, $this->getRequestValue('topic_id'));
        return $rs;
    }

    protected function _newAction() {

        /* $breadcrumbs=array();
          if(Multilanguage::is_set('LT_HOME', '_template')){
          $breadcrumbs[]='<a href="'.$folder.'/">'.Multilanguage::_('LT_HOME', '_template').'</a>';
          }else{
          $breadcrumbs[]='<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>';
          }

          if(Multilanguage::is_set('LT_PRIVATE_ACCOUNT', '_template')){
          $breadcrumbs[]='<a href="'.$folder.'/account/">'.Multilanguage::_('LT_PRIVATE_ACCOUNT', '_template').'</a>';
          }else{
          $breadcrumbs[]='<a href="'.$folder.'/account/">'.Multilanguage::_('PRIVATE_ACCOUNT', 'system').'</a>';
          }

          if(Multilanguage::is_set('LT_MY_ADS', '_template')){
          $breadcrumbs[]='<a href="'.$folder.'/account/data/">'.Multilanguage::_('LT_MY_ADS', '_template').'</a>';
          }else{
          $breadcrumbs[]='<a href="'.$folder.'/account/data/">'.Multilanguage::_('MY_ADS', 'system').'</a>';
          }

          if(Multilanguage::is_set('LT_MY_ADV_ADD', '_template')){
          $breadcrumbs[]=Multilanguage::_('LT_MY_ADV_ADD', '_template');
          }else{
          $breadcrumbs[]=Multilanguage::_('MY_ADV_ADD', 'system');
          }

          $this->template->assert('breadcrumbs', $this->get_breadcrumbs($breadcrumbs)); */

        $user_id = $this->getSessionUserId();
        $rs = '';




        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        // var_dump($form_data['data']['square_rooms']);

        if ($this->getConfigValue('more_fields_in_lk')) {
            $form_data = $this->init_more_fields($form_data);
        }
        $form_data['data']['fio']['required'] = 'off';


        if ($this->getConfigValue('special_advert_cost') > 0) {
            $form_data['data']['hot']['title'] = 'Спецразмещение<br> (стоимость размещения в блоке <b>' . $this->getConfigValue('special_advert_cost') . ' ' . $this->getConfigValue('ue_name') . '</b>)';
        }

        if ($this->getConfigValue('user_add_street_enable') != 1) {
            if (isset($form_data['data']['new_street'])) {
                unset($form_data['data']['new_street']);
            }
        }

        //$form_data['data'] = $data_model->init_model_data_from_request($form_data['data']);

        if ($this->getConfigValue('apps.billing.enable')) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/tariff/tariff.xml') and $this->getConfigValue('apps.tariff.enable') and file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/billing.xml')) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php');
                $billing = new Billing();

                $user_limits = $billing->getUserLimits($user_id, 'limit_data');

                if ($user_limits && $user_limits['total'] >= $user_limits['limits']) {
                    $rs = 'Превышен лимит объявлений. Вы разместили все <b>' . $user_limits['total'] . '</b> из доступных <b>' . $user_limits['limits'] . '</b> объявлений за <b>' . $user_limits['period_key'] . '</b>';
                    return $rs;
                }
                if (method_exists($billing, 'getUserAdvLimits')) {

                    $user_limits = $billing->getUserAdvLimits($user_id, 'advlimit_data');

                    if ($user_limits && $user_limits['total'] >= $user_limits['limits']) {
                        $rs = 'Превышен лимит объявлений. Вы разместили все <b>' . $user_limits['total'] . '</b> из доступных <b>' . $user_limits['limits'] . '</b> объявлений';
                        return $rs;
                    }
                }
            } else {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
                $Account = new Account;
                $company_profile = $Account->get_company_profile($user_id);
                if ($company_profile['limit_data_left']['value'] < 1) {
                    $rs = 'Превышен лимит объявлений. Вы разместили все ' . $company_profile['limit_data']['value'] . ' объявлений. Для увеличения лимита обратитесь к администратору.';
                    return $rs;
                }
            }
        }
        //$form_data['data'] = $data_model->init_model_data_from_request($form_data['data']);
        $form_data['data']['user_id']['value'] = $user_id;
        $form_data['data']['user_id']['type'] = 'hidden';
        $form_data['data']['active']['value'] = 1;
        if ($this->getConfigValue('enable_special_in_account') != 1) {
            unset($form_data['data']['hot']);
        }

        $rs = $this->get_form($form_data['data']);
        return $rs;
    }

    /**
     * Check access to data
     * @param int $user_id
     * @param int $data_id
     * @return boolean
     */
    function check_access_to_data($user_id, $data_id) {
        $DBC = DBC::getInstance();
        if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting')) {
            $query = "SELECT id FROM " . DB_PREFIX . "_data WHERE user_id=? AND id=? AND archived=0";
        } else {
            $query = "SELECT id FROM " . DB_PREFIX . "_data WHERE user_id=? AND id=?";
        }

        $stmt = $DBC->query($query, array($user_id, $data_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['id'] > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check access to data
     * @param int $user_id
     * @param int $data_id
     * @return boolean
     */
    function check_access_to_aggregated_data($user_id, $data_id) {
        $DBC = DBC::getInstance();

        $query = 'SELECT user_id FROM ' . DB_PREFIX . '_user WHERE puser_id=?';
        if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting')) {
            $query = "SELECT id FROM " . DB_PREFIX . "_data WHERE user_id IN (SELECT user_id FROM " . DB_PREFIX . "_user WHERE puser_id=? OR user_id=?) AND id=? AND archived=0";
        } else {
            $query = "SELECT id FROM " . DB_PREFIX . "_data WHERE user_id IN (SELECT user_id FROM " . DB_PREFIX . "_user WHERE puser_id=? OR user_id=?) AND id=?";
        }

        $stmt = $DBC->query($query, array($user_id, $user_id, $data_id));


        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['id'] > 0) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Add data
     * @param array $form_data form data
     * @return boolean
     */
    function add_data($form_data, $language_id = 0) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
        $account = new Account();
        $user_balance = $account->getAccountValue($this->getSessionUserId());

        $form_data['price']['value'] = str_replace(' ', '', $form_data['price']['value']);

        //check balance and cost of service
        $need_money = 0;
        if ($this->getConfigValue('advert_cost') > 0) {
            $need_money += $this->getConfigValue('advert_cost');
        }
        if ($this->getConfigValue('special_advert_cost') > 0 and $form_data['hot']['value'] == 1) {
            $need_money += $this->getConfigValue('special_advert_cost');
        }
        if ($user_balance < $need_money) {
            $this->riseError('Недостаточно средств на счете для операции. <a href="' . SITEBILL_MAIN_URL . '/account/balance/?do=add_bill_done&bill=' . $need_money . '">Пополнить баланс на ' . $need_money . ' ' . $this->getConfigValue('ue_name') . '</a>');
            return false;
        }


        if (1 == $this->getConfigValue('moderate_first')) {
            $form_data['active']['value'] = 0;
        }

        if (1 == $this->getConfigValue('apps.geodata.try_encode') && 1 == $this->getConfigValue('apps.geodata.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/geodata/admin/admin.php';
            $GA = new geodata_admin();
            $form_data = $GA->try_geocode($form_data);
        }

        //$query = $data_model->get_insert_query(DB_PREFIX.'_data', $form_data);
        $queryp = $data_model->get_prepared_insert_query(DB_PREFIX . '_' . $this->table_name, $form_data);

        $DBC = DBC::getInstance();

        $stmt = $DBC->query($queryp['q'], $queryp['p'], $row, $success_mark);
        if (!$success_mark) {
            $this->riseError($DBC->getLastError());
            return false;
        }

        $new_record_id = $DBC->lastInsertId();

        if (1 == $this->getConfigValue('moderate_first')) {
            $this->notifyAboutModerationNeed($new_record_id, 'new');
        }

        if ($new_record_id > 0) {
            $this->setUpdatedAtDate($new_record_id);
        }

        $imgs = array();

        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploads') {
                $ims = $this->appendUploads('data', $form_item, 'id', $new_record_id);
                if (is_array($ims) && count($ims) > 0) {
                    $imgs = array_merge($imgs, $ims);
                }
            }
        }

        $ims = $this->editImageMulti('data', 'data', 'id', $new_record_id);
        if (is_array($ims) && count($ims) > 0) {
            $imgs = array_merge($imgs, $ims);
        }

        if (1 == $this->getConfigValue('apps.seo.data_alias_enable') && ((isset($form_data['translit_alias']) && $form_data['translit_alias']['value'] == '') || !isset($form_data['translit_alias']))) {
            $this->saveTranslitAlias($new_record_id);
        }

        if ($this->getConfigValue('is_watermark')) {
            $filespath = SITEBILL_DOCUMENT_ROOT . '/img/data/';
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/watermark/watermark.php';
            $Watermark = new Watermark();
            $Watermark->setPosition($this->getConfigValue('apps.watermark.position'));
            $Watermark->setOffsets(array(
                $this->getConfigValue('apps.watermark.offset_left'),
                $this->getConfigValue('apps.watermark.offset_top'),
                $this->getConfigValue('apps.watermark.offset_right'),
                $this->getConfigValue('apps.watermark.offset_bottom')
            ));

            if (defined('STR_MEDIA') && STR_MEDIA == Sitebill::MEDIA_SAVE_FOLDER) {
                $copy_folder = MEDIA_FOLDER . '/nowatermark/';
                if (defined('STR_MEDIA_FOLDERFDAYS') && STR_MEDIA_FOLDERFDAYS === 1) {
                    $foldeformat = 'Ymd';
                } else {
                    $foldeformat = 'Ym';
                }
                $folder_name = date($foldeformat, time());
                $locs = $copy_folder . '/' . $folder_name;
                if (!is_dir($locs)) {
                    mkdir($locs);
                }
                if (1 == $this->getConfigValue('save_without_watermark') && !empty($imgs)) {
                    $copy_folder = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark';
                    foreach ($imgs as $v) {
                        copy($filespath . $v['normal'], $copy_folder . '/' . $v['normal']);
                    }
                }
                if (!empty($imgs)) {
                    foreach ($imgs as $v) {
                        $Watermark->printWatermark(MEDIA_FOLDER . '/' . $v['normal']);
                    }
                }
            } else {
                if (1 == $this->getConfigValue('save_without_watermark') && !empty($imgs)) {
                    $copy_folder = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/';
                    foreach ($imgs as $v) {
                        copy($filespath . $v['normal'], $copy_folder . $v['normal']);
                    }
                }
                if (!empty($imgs)) {
                    foreach ($imgs as $v) {
                        $Watermark->printWatermark($filespath . $v['normal']);
                    }
                }
            }
        }

        if ($new_record_id > 0) {
            if ($this->getConfigValue('advert_cost') > 0) {
                $account->minusMoney($this->getSessionUserId(), $this->getConfigValue('advert_cost'));
            }
            if ($this->getConfigValue('special_advert_cost') > 0 and $form_data['hot']['value'] == 1) {
                $account->minusMoney($this->getSessionUserId(), $this->getConfigValue('special_advert_cost'));
            }

            if ($this->getConfigValue('apps.twitter.enable') && 1 == (int) $this->getConfigValue('apps.twitter.allow_posting_from_account')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/twitter/admin/admin.php';
                $Twitter = new twitter_admin();
                $Twitter->sendTwit($new_record_id);
            }
        }
        return $new_record_id;

        //echo "new_record_id = $new_record_id<br>";
        //echo $query;
    }
    
    
    private function notifyAboutModerationNeed($id, $action = 'new') {

        /* require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
          $mailer = new Mailer(); */
        $subject = $_SERVER['SERVER_NAME'] . ': объявление требует модерации';
        $from = $this->getConfigValue('order_email_acceptor');
        $useremail = $this->getConfigValue('order_email_acceptor');
        $body = '';
        if ($action == 'edit') {
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
        if ($action == 'edit') {
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

    private function notifyUserAboutAdding($user_id, $id, $topic_id) {
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

        /* require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
          $Structure_Manager = new Structure_Manager();
          $category_structure = $Structure_Manager->loadCategoryStructure();

          if(1==$this->getConfigValue('apps.seo.level_enable')){

          if($category_structure['catalog'][$topic_id]['url']!=''){
          $parent_category_url=$category_structure['catalog'][$topic_id]['url'].'/';
          }else{
          $parent_category_url='';
          }
          }else{
          $parent_category_url='';
          }
          if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $translit_alias!=''){
          $href=$this->getServerFullUrl().'/'.$parent_category_url.$translit_alias;
          }elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
          $href=$this->getServerFullUrl().'/'.$parent_category_url.'realty'.$id.'.html';
          }else{
          $href=$this->getServerFullUrl().'/'.$parent_category_url.'realty'.$id;
          } */

        //$href='http://'.$_SERVER['HTTP_HOST'].$href;
        /* require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
          $mailer = new Mailer(); */

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
