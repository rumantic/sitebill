<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * data admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class data_admin extends Object_Manager {

    public $save_url = 'empty';
    /**
     * @var Permission
     */
    protected $permission;

    /**
     * Constructor
     */
    function __construct($realty_type = false) {
        $this->SiteBill();
        Multilanguage::appendAppDictionary('data');
        $this->table_name = 'data';
        $this->action = 'data';
        $this->primary_key = 'id';
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $this->permission = new Permission();


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $this->data_model_object = $data_model;
        $this->data_model = $data_model->get_kvartira_model($this->getConfigValue('ajax_form_in_admin'));
        if ( !$this->allowChangeUserId() ) {
            $this->data_model[$this->table_name]['user_id']['type'] = 'hidden';
            $this->data_model[$this->table_name]['user_id']['name'] = 'user_id';
            $this->data_model[$this->table_name]['user_id']['value'] = $this->getSessionUserId();
        }

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_admin = new config_admin();

        if (!$config_admin->check_config_item('apps.data.notify_admin_added')) {
            $config_admin->addParamToConfig('apps.data.notify_admin_added', '0', 'Уведомлять администратора о новых объявлениях из ЛК', 1);
        }

        if (!$config_admin->check_config_item('apps.data.disable_excel_import')) {
            $config_admin->addParamToConfig('apps.data.disable_excel_import', '0', 'Выключить функцию импорта из excel в ЛК', 1);
        }
        if (!$config_admin->check_config_item('apps.data.disable_excel_export')) {
            $config_admin->addParamToConfig('apps.data.disable_excel_export', '0', 'Выключить функцию экспорта в excel в ЛК', 1);
        }
        if (!$config_admin->check_config_item('apps.data.disable_format_grid')) {
            $config_admin->addParamToConfig('apps.data.disable_format_grid', '0', 'Выключить функцию выбора колонок в таблице в ЛК', 1);
        }
        if (!$config_admin->check_config_item('apps.data.disable_pdf')) {
            $config_admin->addParamToConfig('apps.data.disable_pdf', '0', 'Выключить функцию экспорта в PDF в ЛК', 1);
        }
        if (!$config_admin->check_config_item('apps.data.disable_add_button')) {
            $config_admin->addParamToConfig('apps.data.disable_add_button', '0', 'Выключить кнопку добавления объявлений в ЛК', 1);
        }

        if (!$config_admin->check_config_item('apps.data.allow_add_button_group_list')) {
            $config_admin->addParamToConfig(
                'apps.data.allow_add_button_group_list',
                '',
                'Список групп, которым разрешено добавлять объявления. Пример <strong>1,2,3,4</strong>',
                0);
        }

        if (!$config_admin->check_config_item('apps.data.allow_user_id_change_group_list')) {
            $config_admin->addParamToConfig(
                'apps.data.allow_user_id_change_group_list',
                '',
                'Список групп, которым разрешено редактировать user_id. Пример <strong>1,2,3,4</strong>',
                0);
        }


        if (!$config_admin->check_config_item('apps.data.enable_city_coworker')) {
            $config_admin->addParamToConfig(
                'apps.data.enable_city_coworker',
                '0',
                'Включить режим коворкеров по городам (назначение доступа к объявлениям по целому городу)',
                1);
        }




        if (!$config_admin->check_config_item('apps.data.disable_all_button')) {
            $config_admin->addParamToConfig(
                'apps.data.disable_all_button',
                '0',
                'Выключить кнопку ВСЕ в ЛК',
                1);
        }

        if (!$config_admin->check_config_item('apps.data.disable_memory_button')) {
            $config_admin->addParamToConfig('apps.data.disable_memory_button', '0', 'Выключить кнопку Сохраненные списки в ЛК', 1);
        }
        if (!$config_admin->check_config_item('apps.data.disable_delete_button')) {
            $config_admin->addParamToConfig('apps.data.disable_delete_button', '0', 'Выключить кнопку Удаления в ЛК', 1);
        }
        if (!$config_admin->check_config_item('apps.data.disable_edit_button')) {
            $config_admin->addParamToConfig('apps.data.disable_edit_button', '0', 'Выключить кнопку Редактирования в ЛК', 1);
        }

        $config_admin->addParamToConfig('apps.data.excel_limit', '500', 'Максимальное количество записей выгружаемых через Excel');

        if (!$config_admin->check_config_item('apps.data.check_unique_enable')) {
            $config_admin->addParamToConfig('apps.data.check_unique_enable', '0', 'Включить проверку уникальности текста', 1);
        }
        if (!$config_admin->check_config_item('apps.data.check_unique_percent')) {
            $config_admin->addParamToConfig('apps.data.check_unique_percent', '10', 'Минимальное значение уникальности текста %');
        }
        if (!$config_admin->check_config_item('apps.data.unique_text_required')) {
            $config_admin->addParamToConfig('apps.data.unique_text_required', 'Текст объявления не уникален', 'Сообщение об ошибке при проверке уникальности');
        }
        if ( $this->getConfigValue('dadata_autocomplete_force') ) {
            $this->data_model['data'] = $this->prepare_model_for_dadata($this->data_model['data']);
        }

        if (!$config_admin->check_config_item('apps.data.default_sort')) {
            $config_admin->addParamToConfig('apps.data.default_sort', '', 'Сортировка по-умолчанию. Указывается в виде системное имя поля|направление сортировки');
        }
    }

    public function _preload() {

    }

    function set_tags_from_request () {
        if ( $this->getRequestValue('subdo') == 'set_tags' ) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/components/model_tags/model_tags.php');
            $model_tags = new model_tags();
            $tag_name = $this->getRequestValue('tag_name');
            $tag_value = $this->getRequestValue('tag_value');
            $tags_array = [
                $tag_name => [$tag_value]
            ];
            $model_tags->set_model_tags('data', $tags_array);
            header('Location: '.$this->request()->url());
        }
        return false;
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
        //$default_params['render_user_id'] = $this->getSessionUserId();
        if (!preg_match('/all[\/]?$/', $REQUESTURIPATH)) {
            //$params['grid_conditions']['user_id'] = $this->getSessionUserId();
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
        if ( $this->getConfigValue('apps.billing.enable') ) {
            $rs .= $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/billing/site/template/billing_panel.tpl');
        }

        return $rs;
    }

    /**
     * Delete data
     * @param string $table_name
     * @param string $primary_key
     * @param int $primary_key_value
     */
    function delete_data($table_name, $primary_key, $primary_key_value) {
        if ( $this->getConfigValue('apps.data.disable_delete_button') ) {
            $this->riseError(_e('Функция удаления отключена'));
            return false;
        }

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
        $params['per_page'] = $this->getConfigValue('apps.data.excel_limit');
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
        if ( $this->getConfigValue('apps.data.disable_edit_button') ) {
            $this->riseError(_e('Функция редактирования отключена'));
            return false;
        }

        $id = intval($this->getRequestValue('id'));
        if($id==0){
            $id = intval($form_data[$this->primary_key]['value']);
        }

        if ($id == 0) {
            return false;
        }
        $status_changed = false;

        $moderate_first = false;
        if (1 == $this->getConfigValue('moderate_first')) {
            $moderate_first = true;
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
        $account = new Account();
        $user_balance = $account->getAccountValue($this->getSessionUserId());

        if(isset($form_data['price'])){
            $form_data['price']['value'] = str_replace(' ', '', $form_data['price']['value']);
        }


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

        if(!$moderate_first){
            //проверяем возможность установки активного статуса в зависимости от наличия услуги списания за размещения
            if(isset($form_data['active']) && ($form_data_tmp['active']['value'] == 0 and $form_data['active']['value'] == 1)){
                if ($this->getConfigValue('apps.billing.enable')) {
                    if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/tariff/tariff.xml') and $this->getConfigValue('apps.tariff.enable') and file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/billing.xml')) {
                        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php');
                        $billing = new Billing();

                        if(!$billing->checkAdvAbonent($_SESSION['user_id'], $id)){
                            $this->riseError('Вы не можете изменить статус активности');
                            return false;
                        }else{
                            $billing->setAdvAbonentState($_SESSION['user_id'], $id);
                        }
                    }
                }


            }
        }

        if ($moderate_first) {
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

        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'docuploads') {
                $imgs_uploads = $this->appendDocUploads('data', $form_item, 'id', $id);
            }
        }

        $mutiitems = array();
        foreach ($form_data as $k => $form_item) {
            if ($form_item['type'] == 'select_by_query_multi') {
                $vals = $form_item['value'];
                if (!is_array($vals)) {
                    $vals = (array) $mutiitems[$k];
                }
                if (!empty($vals)) {
                    $mutiitems[$k] = $vals;
                } else {
                    $mutiitems[$k] = array();
                }
            }
        }

        if (!empty($mutiitems)) {
            $keys = array_keys($mutiitems);

            $params = array();
            $params[] = 'data';
            $params = array_merge($params, $keys);
            $params[] = $id;
            $query = 'DELETE FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name` IN (' . implode(', ', array_fill(0, count($keys), '?')) . ') AND `primary_id`=?';
            $stmt = $DBC->query($query, $params);

            $query = 'INSERT INTO ' . DB_PREFIX . '_multiple_field (`table_name`, `field_name`, `primary_id`, `field_value`) VALUES (?,?,?,?)';
            foreach ($mutiitems as $key => $vals) {
                if (!empty($vals)) {
                    foreach ($vals as $val) {
                        $stmt = $DBC->query($query, array('data', $key, $id, $val));
                    }
                }
            }
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
        if ( $this->getConfigValue('apps.data.disable_edit_button') ) {
            $this->riseError(_e('Функция редактирования отключена'));
            return false;
        }

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
        if (1 == $this->getConfigValue('apps.language.autotrans_enable')) {
            $form_data['data'] = $data_model->init_model_data_auto_translate($form_data['data']);
        }

        if (1 == $this->getConfigValue('divide_step_form') && isset($_POST['submit'])) {
            $_form_data['data'] = $data_model->init_model_data_from_request($_form_data['data']);
            foreach ($_form_data['data'] as $fdk => $fdv) {
                if ($fdv['type'] == 'uploadify_image') {
                    unset($_form_data['data'][$fdk]);
                }
            }
            $form_data['data'] = array_merge($form_data['data'], $_form_data['data']);
        }

        if ( !$this->allowChangeUserId() ) {
            $form_data['data']['user_id']['type'] = 'hidden';
        }
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
        if ( $this->getConfigValue('apps.data.disable_edit_button') ) {
            $this->riseError(_e('Функция редактирования отключена'));
            return false;
        }

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

        if(isset($form_data['data']['user_id'])){
            if(1==$this->getConfigValue('enable_curator_mode')){
                unset($form_data['data']['user_id']);
            }else{
                if ( !$this->allowChangeUserId() ) {
                    $form_data['data']['user_id']['value'] = $user_id;
                }
            }
        }


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

    function checkUniquety($form_data) {
        $unque_fields = trim($this->getConfigValue('apps.realty.uniq_params'));
        //$unque_fields='city_id,topic_id,price';

        $fields = array();
        if ('' !== $unque_fields) {
            $matches = array();
            preg_match_all('/([^,\s]+)/i', $unque_fields, $matches);
            if (!empty($matches[1])) {
                $fields = $matches[1];
            }
        }

        if (!empty($fields)) {
            $where = array();
            foreach ($fields as $f) {
                if (isset($form_data[$f])) {
                    if ($form_data[$f]['dbtype'] == 1 || ($form_data[$f]['dbtype'] != 'notable' && $form_data[$f]['dbtype'] != '0')) {
                        $where[] = '`' . $f . '`=?';
                        $where_val[] = $form_data[$f]['value'];
                    }
                }
            }
        } elseif (isset($form_data['city_id']) && isset($form_data['street_id']) && isset($form_data['number'])) {
            $where[] = '`city_id`=?';
            $where_val[] = (int) $form_data['city_id']['value'];
            $where[] = '`street_id`=?';
            $where_val[] = (int) $form_data['street_id']['value'];
            $where[] = '`number`=?';
            $where_val[] = $form_data['number']['value'];
        } else {
            return TRUE;
        }

        $DBC = DBC::getInstance();

        $uns = array();
        $query = 'SELECT id FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE ' . implode(' AND ', $where);

        $stmt = $DBC->query($query, $where_val);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $uns[] = $ar['id'];
            }
        }
        if (count($uns) > 0) {
            $this->riseError(Multilanguage::_('ADVUNIQUETY_ERROR', 'system').' ('.implode(',', $uns).')');
            return FALSE;
        }
        return TRUE;
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


        if ( !$this->allowChangeUserId() ) {
            $form_data['data']['user_id']['value'] = $user_id;
            $form_data['data']['user_id']['type'] = 'hidden';
        }
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

        if ( $this->getConfigValue('apps.data.disable_delete_button') ) {
            return _e('Функция удаления отключена');
        }

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

        $user_id = $this->getSessionUserId();
        $rs = '';

        if ($this->getConfigValue('advert_cost') > 0 ) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
            $account = new Account();
            $account_value = $account->getAccountValue($this->getSessionUserId());

            $rs .= '<p><b>Стоимость размещения одного объявления ' . $this->getConfigValue('advert_cost') . ' ' . $this->getConfigValue('ue_name') . '</b></p>';

            if ($account_value < $this->getConfigValue('advert_cost')) {
                $rs .= '<p>Ваш баланс ' . $account_value . ' ' . $this->getConfigValue('ue_name') . '</p>';
                $rs .= '<b>На вашем счету не хватает средств для размещения объявления, <a href="' . SITEBILL_MAIN_URL . '/account/balance/?do=add_bill">пополнить</a></b></td>';
                return $rs;
            }
        }



        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        // var_dump($form_data['data']['square_rooms']);

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
                $new_tariff_message = 'Вы можете <a href="'.SITEBILL_MAIN_URL.'/billing/tarifflist/" class="btn btn-success">подключить другой тариф</a> с большими лимитами.';

                if ($user_limits && $user_limits['total'] >= $user_limits['limits']) {
                    $rs = '<h1>Превышен лимит объявлений. Вы разместили все <b>' . $user_limits['total'] . '</b> из доступных <b>' . $user_limits['limits'] . '</b> объявлений за <b>' . $user_limits['period_key'] . '</b> '.$new_tariff_message.'</h1>' ;
                    return $rs;
                }
                if (method_exists($billing, 'getUserAdvLimits')) {

                    $user_limits = $billing->getUserAdvLimits($user_id, 'advlimit_data');

                    if ($user_limits && $user_limits['total'] >= $user_limits['limits']) {
                        $rs = '<h1>Превышен лимит объявлений. Вы разместили все <b>' . $user_limits['total'] . '</b> из доступных <b>' . $user_limits['limits'] . '</b> объявлений на вашем тарифе. '.$new_tariff_message.'</h1>';
                        return $rs;
                    }
                }
                //Проверяем достаточно ли денег на счету для размещения нового объекта
                if(!$billing->checkAdvAbonent($_SESSION['user_id'])){
                    $rs = '<h1>Недостаточно средств на счету для размещения объекта</h1>';
                    return $rs;
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
        if ( !$this->allowChangeUserId() ) {
            $form_data['data']['user_id']['value'] = $user_id;
            $form_data['data']['user_id']['type'] = 'hidden';
        }
        $form_data['data']['active']['value'] = 1;
        if ($this->getConfigValue('enable_special_in_account') != 1) {
            unset($form_data['data']['hot']);
        }

        $rs = $this->get_form($form_data['data']);
        return $rs;
    }

    function mass_delete_data($table_name, $primary_key, $ids) {

        if ( $this->getConfigValue('apps.data.disable_delete_button') ) {
            return _e('Функция удаления отключена');
        }


        $cuser_id = (int) $_SESSION['user_id'];

        if($cuser_id==0){
            return '';
        }
        $errors = '';

        if (count($ids) > 0) {
            foreach ($ids as $k => $id) {
                if (!$this->check_access_to_data($cuser_id, $id)) {
                    unset($ids[$k]);
                }
            }
        }

        if(count($ids)==0){
            header('location: '.SITEBILL_MAIN_URL.'/account/data/');
            exit();
        }

        if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting')) {
            $DBC = DBC::getInstance();
            $query = 'UPDATE ' . DB_PREFIX . '_data SET archived=1 WHERE `id` IN (' . implode(',', $ids) . ')';
            $stmt = $DBC->query($query);
            header('location: '.SITEBILL_MAIN_URL.'/account/data/');
            exit();
        } else {
            foreach ($ids as $id) {
                $log_id = false;
                if ($this->getConfigValue('apps.realtylog.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/admin/admin.php';
                    $Logger = new realtylog_admin();
                    $log_id = $Logger->addLog($id, $cuser_id, 'delete', $table_name);
                }
                if ($this->getConfigValue('apps.realtylogv2.enable')) {

                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';

                    $Logger = new realtylogv2_admin();

                    $log_id = $Logger->addLog($id, $cuser_id, 'delete', $table_name, $primary_key);
                }
                $this->delete_data($table_name, $primary_key, $id);
                if ($this->getError()) {
                    if ($log_id !== false) {
                        $Logger->deleteLog($log_id);
                    }
                    $errors .= '<div align="center">' . Multilanguage::_('L_ERROR_ON_DELETE') . ' ID=' . $id . ': ' . $this->GetErrorMessage() . '<br>';
                    $errors .= '</div>';
                    $this->error_message = false;
                }
            }
            if ($errors != '') {
                $rs .= $errors . '<div align="center"><a href="'.SITEBILL_MAIN_URL.'/accoutn/data/">ОК</a></div>';
            } else {
                header('location: '.SITEBILL_MAIN_URL.'/account/data/');
                exit();
            }
            return $rs;
        }
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
        $enable_curator_mode=false;
        if (
            1 == $this->getConfigValue('enable_curator_mode')
            or
            1 == $this->getConfigValue('enable_coworker_mode')
        ) {
            $enable_curator_mode = true;
            $has_access = 0;

            if( 1 === intval($this->getConfigValue('curator_mode_fullaccess'))){

                $query = 'SELECT COUNT(d.id) AS _cnt FROM ' . DB_PREFIX . '_data d LEFT JOIN ' . DB_PREFIX . '_user u USING(user_id) WHERE d.id=? AND u.parent_user_id=?';
                $stmt = $DBC->query($query, array($data_id, $user_id));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ($ar['_cnt'] > 0) {
                        $has_access = 1;
                    }
                }
            } elseif ( $this->getConfigValue('apps.data.enable_city_coworker') ) {
                $has_access = $this->check_coworker_access_by_foreign_key(
                    'data',
                    $user_id,
                    'update',
                    'id',
                    $data_id,
                    'city');
            } else{
                $query = 'SELECT COUNT(id) AS _cnt FROM ' . DB_PREFIX . '_cowork WHERE coworker_id=? AND object_type=? AND id=?';
                $stmt = $DBC->query($query, array($user_id, 'data', $data_id));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ($ar['_cnt'] > 0) {
                        $has_access = 1;
                    }
                }
            }


        }

        $where=array();
        $where_val=array();

        $where[]='`id`=?';
        $where_val[]=$data_id;
        if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting')) {
            $where[]='`archived`=0';
        }

        if($enable_curator_mode){
            $where[]='(`user_id`=? OR (`user_id`!=? AND 1='.$has_access.'))';
            $where_val[]=$user_id;
            $where_val[]=$user_id;
        }else{
            $where[]='`user_id`=?';
            $where_val[]=$user_id;
        }

        $query = 'SELECT id FROM ' . DB_PREFIX . '_data WHERE '.implode(' AND ', $where);
        $stmt = $DBC->query($query, $where_val);

        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['id'] > 0) {
                return true;
            }
        }
        return false;

        /*$DBC = DBC::getInstance();
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
        return false;*/
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

        $moderate_first = false;

        if (1 == $this->getConfigValue('moderate_first')) {
            $moderate_first = true;
        }

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


        if ($this->getConfigValue('apps.billing.enable')) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/tariff/tariff.xml') and $this->getConfigValue('apps.tariff.enable') and file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/billing.xml')) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php');
                $billing = new Billing();

                $need_money += $billing->getAdvAbonentPayment($_SESSION['user_id']);

                /*if(!$billing->getAdvAbonent($_SESSION['user_id'])){
                    $rs = 'Недостаточно средств на счету для размещения объекта';
                    return $rs;
                }*/
            }
        }

        if ($user_balance < $need_money) {
            $this->riseError('Недостаточно средств на счете для операции. <a href="' . SITEBILL_MAIN_URL . '/account/balance/?do=add_bill_done&bill=' . $need_money . '">Пополнить баланс на ' . $need_money . ' ' . $this->getConfigValue('ue_name') . '</a>');
            return false;
        }


        if (1 == $this->getConfigValue('moderate_first')) {
            if(isset($form_data['active'])){
                $form_data['active']['value'] = 0;
            }
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

        if ($moderate_first) {
            $this->notifyAboutModerationNeed($new_record_id, 'new');
        }

        if ($new_record_id > 0) {
            $this->setUpdatedAtDate($new_record_id);
        }

        //если отключено модерирование и у пользователя есть доступ к установке активности
        //и объект подается как активный, тогда производим списание за первый период размещения
        if(!$moderate_first && isset($form_data['active']) && $form_data['active']['value'] == 1){
            if ($this->getConfigValue('apps.billing.enable')) {
                $billing->setAdvAbonentState($_SESSION['user_id'], $new_record_id);
            }
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

        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'docuploads') {
                $imgs_uploads = $this->appendDocUploads('data', $form_item, 'id', $new_record_id);
            }
        }

        $mutiitems = array();
        foreach ($form_data as $k => $form_item) {
            if ($form_item['type'] == 'select_by_query_multi') {
                $vals = $form_item['value'];
                if (!is_array($vals)) {
                    $vals = (array) $mutiitems[$k];
                }
                if (!empty($vals)) {
                    $mutiitems[$k] = $vals;
                } else {
                    $mutiitems[$k] = array();
                }
            }
        }



        if (!empty($mutiitems)) {
            $keys = array_keys($mutiitems);

            $params = array();
            $params[] = 'data';
            $params = array_merge($params, $keys);
            $params[] = $new_record_id;
            $query = 'DELETE FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name` IN (' . implode(', ', array_fill(0, count($keys), '?')) . ') AND `primary_id`=?';
            $stmt = $DBC->query($query, $params);

            $query = 'INSERT INTO ' . DB_PREFIX . '_multiple_field (`table_name`, `field_name`, `primary_id`, `field_value`) VALUES (?,?,?,?)';
            foreach ($mutiitems as $key => $vals) {
                if (!empty($vals)) {
                    foreach ($vals as $val) {
                        $stmt = $DBC->query($query, array('data', $key, $new_record_id, $val));
                        //echo $DBC->getLastError();
                    }
                }
            }
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
        $from = $this->getConfigValue('system_email');
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

    function allowChangeUserId() {
        if ( $this->getConfigValue('apps.data.allow_user_id_change_group_list') != '' ) {
            $user_group_id = $this->permission->get_user_group_id($this->getSessionUserId());
            $allow_groups = explode(',', $this->getConfigValue('apps.data.allow_user_id_change_group_list'));
            if ( in_array($user_group_id, $allow_groups) ) {
                return true;
            }
        }
        return false;
    }

}
