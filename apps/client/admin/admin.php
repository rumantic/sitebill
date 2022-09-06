<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Client admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class client_admin extends Object_Manager {
    use \system\traits\blade\BladeTrait;

    public $client_topic_id = null;

    /**
     * @var Permission
     */
    protected $permission;

    /**
     * Constructor
     */
    function __construct() {
        $this->enable_angular();
        parent::__construct();
        Multilanguage::appendAppDictionary('client');

        $this->table_name = 'client';
        $this->action = 'client';
        $this->primary_key = 'client_id';
        $this->app_title = Multilanguage::_('APP_TITLE', 'client');

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $this->permission = new Permission();


        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_admin = new config_admin();

        $config_admin->addParamToConfig('apps.client.enable', '1', 'Включить приложение', 1);
        $config_admin->addParamToConfig('apps.client.namespace', 'client', 'Пространство имен приложения');
        $config_admin->addParamToConfig('apps.client.folder_title', 'Заявки', 'Заголовок приложения в хлебных крошках');
        $config_admin->addParamToConfig('apps.client.allow-redirect_url_for_orders', '', 'Разрешить редирект на другую страницу при удачном завершении подачи заявки', 1);
        $config_admin->addParamToConfig('apps.client.orders_email', '', 'Email для уведомления о заявках (если несколько, то указать через запятую)');
        $config_admin->addParamToConfig('apps.client.notify_admin', '0', 'Уведомлять администраторов о заявках', 1);
        $config_admin->addParamToConfig('apps.client.order_mode', '0', 'Режим заявок', 1);
        $config_admin->addParamToConfig('apps.client.frontend_enable', '0', 'Открыть доступ к выбору клиентов в ЛК', 1);
        $config_admin->addParamToConfig('apps.client.create_client_on_user_register', '0', 'Создавать запись в таблице client с данными пользователя при регистрации', 1);
        $config_admin->addParamToConfig('apps.client.antispam_disable', '0', 'Отключить проверку на спам-сообщения в заявках (не рекомендуется)', 1);
        $config_admin->addParamToConfig('apps.client.hide_user_id_on_frontend', '0', 'apps.client.hide_user_id_on_frontend (dev param)', 1);

        $config_admin->addParamToConfig(
            'apps.client.front_manager_alias',
            'clientmanager',
            'Алиас для менеджера клиентов на фронте',
            0);
        $config_admin->addParamToConfig(
            'apps.client.front_manager.event.enable',
            '0',
            'Уведомлять админа о событии VISIT',
            1);
        $config_admin->addParamToConfig(
            'apps.client.thankyou_url',
            '',
            'Адрес страницы, куда отправлять пользователя после успешной заявки (например, thankyou). Если пусто, то редиректа не будет.');

        $config_admin->addParamToConfig(
            'apps.client.default_grid_item',
            'client_id,fio,phone,user_id',
            'Список колонок в таблице клиентов по-умолчанию (для личного кабинета)');



        //$this->install();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/client/admin/client_model.php');
        $Object = new Client_Model();
        $this->model = $Object;

        $form_data = array();

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($this->table_name, false);
            if (empty($form_data)) {
                $form_data = array();
                $form_data = $Object->get_model($ajax);
                //$form_data = $this->_get_big_city_kvartira_model2($ajax);
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, $this->table_name);
                $form_data = array();
                $form_data = $ATH->load_model($this->table_name, false);
            }
        } else {
            $form_data = $Object->get_model($ajax);
        }
        $this->redefine_primary_key($form_data);


        $this->data_model = $form_data;
        if (
            isset($this->data_model[$this->table_name]['user_id']) and
            $this->getConfigValue('apps.client.hide_user_id_on_frontend') and
            !$this->permission->is_admin($this->getSessionUserId())
        ) {
            $this->data_model[$this->table_name]['user_id']['type'] = 'hidden';
            $this->data_model[$this->table_name]['user_id']['name'] = 'user_id';
            $this->data_model[$this->table_name]['user_id']['value'] = $this->getSessionUserId();
        }

        $this->add_apps_local_and_root_resource_paths('client');


    }

    /**
     * Метод для использования встраивания форм через хелпер
     * @param $params
     * @return string
     */
    function imbuildform($params){
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/client/site/site.php';
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/client/admin/client_order.php';
        $Client_Order = new Client_Order();
        $model = $params['form'];
        $view = $params['view'];


        $form_data = $Client_Order->loadOrderModel($model);

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();

        $el = $form_generator->compile_form_elements($form_data);
        if ($this->getConfigValue('post_form_agreement_enable') == 1) {
            $el['agreement_block'] = $form_generator->getAgreementFormBlock();
        }

        return $this->view($view, ['form_data' => $el]);

    }

    function redefine_primary_key ( $form_data ) {
        foreach ($form_data[$this->table_name] as $item => $item_array) {
            if ( $item_array['type'] == 'primary_key' ) {
                $this->primary_key = $item_array['name'];
                return true;
            }
        }
        return false;
    }

    function set_client_topic_id($topic_id) {
        $this->client_topic_id = $topic_id;
        $this->template->assign('client_topic_id', $this->client_topic_id);
    }

    function install() {
        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_client` (
		  `client_id` int(11) NOT NULL AUTO_INCREMENT,
		  `fio` varchar(255) NOT NULL,
		  `phone` varchar(255) NOT NULL,
		  `email` varchar(150) NOT NULL,
		  `address` text NOT NULL,
    	  `date` INT(10) NOT NULL, 
		  PRIMARY KEY (`client_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=" . DB_ENCODING . " AUTO_INCREMENT=1 ;";
        $DBC = DBC::getInstance();
        $success = false;
        $stmt = $DBC->query($query, array(), $rows, $success);
        if (!$success) {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED_ERROR');
        } else {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED');
            ;
        }
        return $rs;
    }

    protected function _edit_doneAction() {
        $rs = '';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
        $new_values = $this->getRequestValue('_new_value');
        if (1 == $this->getConfigValue('use_combobox') && count($new_values) > 0) {
            $remove_this_names = array();
            foreach ($form_data[$this->table_name] as $fd) {
                if (isset($new_values[$fd['name']]) && $new_values[$fd['name']] != '' && $fd['combo'] == 1) {
                    $id = md5(time() . '_' . rand(100, 999));
                    $remove_this_names[] = $id;
                    $form_data[$this->table_name][$id]['value'] = $new_values[$fd['name']];
                    $form_data[$this->table_name][$id]['type'] = 'auto_add_value';
                    $form_data[$this->table_name][$id]['dbtype'] = 'notable';
                    $form_data[$this->table_name][$id]['value_table'] = $form_data[$this->table_name][$fd['name']]['primary_key_table'];
                    $form_data[$this->table_name][$id]['value_primary_key'] = $form_data[$this->table_name][$fd['name']]['primary_key_name'];
                    $form_data[$this->table_name][$id]['value_field'] = $form_data[$this->table_name][$fd['name']]['value_name'];
                    $form_data[$this->table_name][$id]['assign_to'] = $fd['name'];
                    $form_data[$this->table_name][$id]['required'] = 'off';
                    $form_data[$this->table_name][$id]['unique'] = 'off';
                }
            }
        }
        $data_model->forse_auto_add_values($form_data[$this->table_name]);
        if (!$this->check_data($form_data[$this->table_name])) {
            $form_data['data'] = $this->removeTemporaryFields($form_data['data'], $remove_this_names);
            $rs = $this->get_form($form_data[$this->table_name], 'edit');
        } else {
            $this->edit_data($form_data[$this->table_name]);
            if ($this->getError()) {
                $form_data['data'] = $this->removeTemporaryFields($form_data['data'], $remove_this_names);
                $rs = $this->get_form($form_data[$this->table_name], 'edit');
            } else {
                $rs .= $this->grid();
            }
        }
        return $rs;
    }

    protected function _deleteAction() {
        $rs = '';

        $this->delete_data($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
        if ($this->getError()) {
            $rs .= '<div align="center">' . Multilanguage::_('L_ERROR_ON_DELETE') . ': ' . $this->GetErrorMessage() . '<br>';
            $rs .= '<a href="?action=' . $this->action . '">ОК</a>';
            $rs .= '</div>';
        } else {
            $rs .= $this->grid();
        }
        return $rs;
    }


    protected function _newAction() {
        $rs = '';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;

        if ($form_data[$this->table_name]['date']['type'] == 'date') {
            $form_data[$this->table_name]['date']['value'] = time();
        }

        $rs = $this->get_form($form_data[$this->table_name]);
        return $rs;
    }


    protected function _new_doneAction() {
        $rs = '';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
        $new_values = $this->getRequestValue('_new_value');
        if (1 == $this->getConfigValue('use_combobox') && count($new_values) > 0) {
            $remove_this_names = array();
            foreach ($form_data[$this->table_name] as $fd) {
                if (isset($new_values[$fd['name']]) && $new_values[$fd['name']] != '' && $fd['combo'] == 1) {
                    $id = md5(time() . '_' . rand(100, 999));
                    $remove_this_names[] = $id;
                    $form_data[$this->table_name][$id]['value'] = $new_values[$fd['name']];
                    $form_data[$this->table_name][$id]['type'] = 'auto_add_value';
                    $form_data[$this->table_name][$id]['dbtype'] = 'notable';
                    $form_data[$this->table_name][$id]['value_table'] = $form_data[$this->table_name][$fd['name']]['primary_key_table'];
                    $form_data[$this->table_name][$id]['value_primary_key'] = $form_data[$this->table_name][$fd['name']]['primary_key_name'];
                    $form_data[$this->table_name][$id]['value_field'] = $form_data[$this->table_name][$fd['name']]['value_name'];
                    $form_data[$this->table_name][$id]['assign_to'] = $fd['name'];
                    $form_data[$this->table_name][$id]['required'] = 'off';
                    $form_data[$this->table_name][$id]['unique'] = 'off';
                }
            }
        }
        //echo '<pre>';
        //print_r($form_data[$this->table_name]);
        //exit();
        $data_model->forse_auto_add_values($form_data[$this->table_name]);
        if (!$this->check_data($form_data[$this->table_name]) || (1 == $this->getConfigValue('filter_double_data') && !$this->checkUniquety($form_data[$this->table_name]))) {
            $form_data['data'] = $this->removeTemporaryFields($form_data['data'], $remove_this_names);
            $rs = $this->get_form($form_data[$this->table_name], 'new');
        } else {
            $new_record_id = $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
            $this->set_new_record_id($new_record_id);
            if ($this->getError()) {
                $form_data['data'] = $this->removeTemporaryFields($form_data['data'], $remove_this_names);
                $rs = $this->get_form($form_data[$this->table_name], 'new');
            } else {
                $rs .= $this->grid();
            }
        }
        return $rs;
    }

    function create_client_on_user_register ( $user_data ) {
        //$this->writeLog(__METHOD__.', user_data '. var_export($user_data, true));
        $this->data_model[$this->table_name]['status_id']['value'] = 'Новая';
        $this->data_model[$this->table_name]['fio']['value'] = $user_data['fio']['value'];
        $this->data_model[$this->table_name]['email']['value'] = $user_data['email']['value'];
        $this->data_model[$this->table_name]['phone']['value'] = $user_data['phone']['value'];
        $new_record_id = $this->add_data($this->data_model[$this->table_name]);
        //$this->writeLog(__METHOD__.', $new_record_id '.$new_record_id);

        if ($this->getError()) {
            $this->writeLog(__METHOD__.', error '.$this->GetErrorMessage());
        }
    }

    protected function _change_paramAction() {
        $form_data = $this->data_model;
        $id_array = array();
        $ids = trim($this->getRequestValue('ids'));
        $param_name = trim($this->getRequestValue('param_name'));
        $param_value = trim($this->getRequestValue('new_param_value'));

        if (isset($form_data[$this->table_name][$param_name]) && $ids != '') {
            $id_array = explode(',', $ids);
            $rs .= $this->mass_change_param($this->table_name, $this->primary_key, $id_array, $param_name, $param_value);
        } else {
            $rs .= $this->grid();
        }
        return $rs;
    }

    protected function _viewAction() {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;

        $default_request_items['client_id'] = $this->getRequestValue($this->primary_key);

        if ( $this->getConfigValue('apps.comment.enable') ) {
            $this->template->assert('apps_comment_on', 1);

            require_once SITEBILL_DOCUMENT_ROOT . '/apps/comment/admin/admin.php';
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/comment/site/site.php';
            $comment_site = new comment_site();
            $comment_site->generateCommentPanel($this->getSessionUserId(), 'client', $this->getRequestValue($this->primary_key));
        }


        $default_request_string = array();
        foreach ( $default_request_items as $key => $value ) {
            $default_request_string[] = "default_request[$key]=$value";
        }

        $this->template->assert('default_request', implode('&', $default_request_string));

        //$this->conctact_subaction();

        if ($this->getRequestValue('language_id') > 0) {
            $form_data[$this->table_name] = $data_model->init_model_data_from_db_language($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name], false, $this->getRequestValue('language_id'));
        } else {
            $form_data[$this->table_name] = $data_model->init_model_data_from_db($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name]);
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/view.php');
        $table_view = new Table_View();
        $client_view = '';
        $client_view .= '<table class="table">';
        $client_view .= $table_view->compile_view($form_data[$this->table_name]);
        $client_view .= '</table>';
        $this->template->assert('client_view', $client_view);

        $rs = $this->show($form_data[$this->table_name]);
        return $rs;
    }

    function add_data($form_data, $language_id = 0) {
        $id = parent::add_data($form_data, $language_id);
        if ($id && strlen($form_data['imgfile']['value']) > 0) {
            $this->update_photo($id);
        }
        return $id;
    }

    function edit_data($form_data, $language_id = 0, $primary_key_value = false) {
        $answer = parent::edit_data($form_data, $language_id, $primary_key_value);
        if ($answer !== false && strlen($form_data['imgfile']['value']) > 0) {
            $this->update_photo($this->getRequestValue($this->primary_key));
        }
        return $answer;
    }


    function show($data) {

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/client/template/edit.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/client/template/edit.tpl';
        } else {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/apps/client/admin/template/edit.tpl.html';
        }
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/comment/admin/admin.php';
        $comments = comment_admin::getCommentsWithUser('client', $data[$this->primary_key]['value']);

        $now = time();
        //echo
        foreach ($comments as &$c) {
            //$d=$now-strtotime($c['comment_date']);
            $cd = strtotime($c['comment_date']);
            $c['comment_date'] = date('d-m-Y H:i', $cd);
            $c['ago'] = $this->convertDeltaTime($now - $cd);
        }
        $this->template->assert('attachments_block', SiteBill::getAttachmentsBlock());
        $this->template->assert('attachments', SiteBill::getAttachments('client', $data[$this->primary_key]['value']));
        $this->template->assert('current_user_id', $this->getAdminUserId());
        $this->template->assert('client_comments', $comments);
        $this->template->assert('client_data', $data);
        $this->template->assert('client_primary_key', $this->primary_key);
        $this->template->assert('client_primary_value', $data[$this->primary_key]['value']);
        return $this->template->fetch($tpl_name);
    }

    private function convertDeltaTime($seconds) {
        if ($seconds < 61) {
            return '1 minute ago';
        } elseif ($seconds < 3540) {
            return (string) ceil($seconds / 60) . ' minutes ago';
        } elseif ($seconds < 86400) {
            return (string) ceil($seconds / 3600) . ' hours ago';
            //return date('h',$seconds)/*(string)ceil($seconds/86400)*/.' hours ago';
        } else {
            return (string) ceil($seconds / 86400) . ' days ago';
        }
        //return (string)ceil($seconds/24*86400).' days ago';
    }

    function grid($params = array(), $default_params = array()) {
        if ( self::$replace_grid_with_angular ) {
            return $this->angular_grid();
        }

        if (1 == intval($this->getConfigValue('apps.client.order_mode'))) {
            return $this->grid_order_mode();
        }




        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);
        $common_grid->set_action($this->action);
        $common_grid->set_grid_table($this->table_name);



        $DBC = DBC::getInstance();
        $used_fields = array();
        $query = 'SELECT `grid_fields` FROM ' . DB_PREFIX . '_table_grids WHERE `action_code`=?';
        $stmt = $DBC->query($query, array($this->action));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $used_fields = json_decode($ar['grid_fields']);
        }

        if (!empty($used_fields)) {
            foreach ($used_fields as $uf) {
                $common_grid->add_grid_item($uf);
            }
            $common_grid->add_grid_control('view');
            $common_grid->add_grid_control('edit');
            $common_grid->add_grid_control('delete');
        } else {
            $common_grid->add_grid_item($this->primary_key);
            $common_grid->add_grid_item('type_id');
            $common_grid->add_grid_item('date');
            $common_grid->add_grid_item('fio');
            $common_grid->add_grid_item('phone');
            $common_grid->add_grid_item('email');
            $common_grid->add_grid_item('status_id');
            $common_grid->add_grid_control('view');
            $common_grid->add_grid_control('edit');
            $common_grid->add_grid_control('delete');
        }


        $common_grid->setPagerParams(array('action' => $this->action, 'page' => $this->getRequestValue('page'), 'per_page' => $this->getConfigValue('common_per_page')));

        //$common_grid->set_grid_query("select * from ".DB_PREFIX."_".$this->table_name." order by date desc");
        $rs = $common_grid->extended_items();
        $rs .= $common_grid->construct_grid();
        return $rs;
    }

    function grid_order_mode() {

        $request_params = array();
        $request_params['action'] = 'client';



        $per_page = 10;
        $page = intval($_GET['page']);
        if ($page == 0) {
            $page = 1;
        }
        $where_p = array();
        $where = array();
        $what = array();


        $DBC = DBC::getInstance();
        $status = $_GET['status_id'];
        if (!is_array($status)) {
            $status = array();
        }
        if (!empty($status)) {
            $request_params['status_id'] = $status;
            $where[] = 'status_id IN (' . implode(',', array_fill(0, count($status), '?')) . ')';
            $where_p = array_merge($where_p, $status);
        }
        $types = $_GET['type_id'];
        if (!is_array($types)) {
            $types = array();
        }
        if (!empty($types)) {
            $request_params['type_id'] = $types;
            $where[] = 'type_id IN (' . implode(',', array_fill(0, count($types), '?')) . ')';
            $where_p = array_merge($where_p, $types);
        }




        $order_statuses = array();
        foreach ($this->data_model[$this->table_name]['status_id']['select_data'] as $k => $v) {
            $s = 0;
            if (in_array($k, $status)) {
                $s = 1;
            }
            $order_statuses[$k] = array('n' => $v, 's' => $s);
        }
        $order_types = array();
        foreach ($this->data_model[$this->table_name]['type_id']['select_data'] as $k => $v) {
            $s = 0;
            if (in_array($k, $types)) {
                $s = 1;
            }
            $order_types[$k] = array('n' => $v, 's' => $s);
        }

        $limit = ' LIMIT ' . (($page - 1) * $per_page) . ', ' . $per_page;
        $order = ' ORDER BY `date` DESC';

        $query = 'SELECT SQL_CALC_FOUND_ROWS * FROM ' . DB_PREFIX . '_client ' . (!empty($where) ? ' WHERE ' . implode(' AND ', $where) : '') . $order . $limit;

        $stmt = $DBC->query($query, $where_p);

        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret[] = $ar;
            }
        }

        $query = 'SELECT FOUND_ROWS() AS _cnt';
        $stmt = $DBC->query($query);
        $ar = $DBC->fetch($stmt);
        $total = $ar['_cnt'];


        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/page_navigator.php';
        $nurl = 'admin';


        //$_params=$pager_params;
        //unset($_params['page_url']);
        $paging = Page_Navigator::getPagingArray($total, $page, $per_page, $request_params, $nurl);

        global $smarty;
        $smarty->assign('pager_array', $paging);
        $smarty->assign('order_types', $order_types);
        $smarty->assign('order_statuses', $order_statuses);
        $smarty->assign('orders_m', $this->data_model[$this->table_name]);
        $smarty->assign('orders', $ret);
        return $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/client/admin/template/grid.tpl');
    }

    /**
     * Get form for edit or new record
     * @param array $form_data
     * @param string $do
     * @param int $language_id
     * @param string $button_title
     * @return string
     */
    function get_form($form_data = array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php') {
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
        $rs .= '<form method="post" class="form-horizontal" action="'.$action.'" enctype="multipart/form-data" id="client_form">';

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
        $el['private'][] = array('html' => '<input type="hidden" name="topic_id" id="topic_id" value="' . $this->client_topic_id . '">');

        $el['form_header'] = $rs;
        $el['form_footer'] = '</form>';

        /* if ( $do != 'new' ) {
          $el['controls']['apply']=array('html'=>'<button id="apply_changes" class="btn btn-info">'.Multilanguage::_('L_TEXT_APPLY').'</button>');
          } */
        $el['controls']['submit'] = array('html' => '<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">' . $button_title . '</button>');

        $smarty->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl';
        } else {
            $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
        }
        return $smarty->fetch($tpl_name);
    }

    private function update_photo($client_id) {
        if (SITEBILL_MAIN_URL != '') {
            $add_folder = SITEBILL_MAIN_URL . '/';
        }

        $this->user_image_dir = '/img/data/user/';
        $imgfile_directory = $this->user_image_dir;

        $document_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $add_folder;


        $avial_ext = array('jpg', 'jpeg', 'gif', 'png');
        if (isset($_FILES['imgfile'])) {

            if (($_FILES['imgfile']['error'] != 0)OR ( $_FILES['imgfile']['size'] == 0)) {
                //echo 'Не указан или указан не верно файл для загрузки<br>';
            } else {
                $fprts = explode('.', $_FILES['imgfile']['name']);
                if (count($fprts) > 1) {
                    $ext = $fprts[count($fprts) - 1];
                    if (in_array($ext, $avial_ext)) {
                        $usrfilename = time() . '.' . $ext;
                        //echo $imgfile_directory.$usrfilename;
                        $i = rand(0, 999);
                        $preview_name = "img" . uniqid() . '_' . time() . "_" . $i . "." . $ext;
                        $preview_name_tmp = "_tmp" . uniqid() . '_' . time() . "_" . $i . "." . $ext;

                        if (!move_uploaded_file($_FILES['imgfile']['tmp_name'], $document_root . '/' . $imgfile_directory . $preview_name_tmp)) {

                        } else {
                            list($width, $height) = $this->makePreview($document_root . '/' . $imgfile_directory . $preview_name_tmp, $document_root . '/' . $imgfile_directory . $preview_name, 160, 160, $ext, 1);
                            unlink($document_root . '/' . $imgfile_directory . $preview_name_tmp);
                            $DBC = DBC::getInstance();
                            $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET `imgfile`=? WHERE `client_id`=?';
                            $stmt = $DBC->query($query, array($preview_name, $client_id));
                        }
                    }
                }
            }
        }
    }

    function ajax() {
        if ($this->getRequestValue('action') == 'get_order_form') {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/client/site/site.php';
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/client/admin/client_order.php';

            $Client_Order = new Client_Order();
            $model = $this->getRequestValue('model');
            $options = $this->getRequestValue('options');
            $custom_template = $this->getRequestValue('custom_template');

            //$this->writeLog(array('apps_name'=>'apps.client', 'method' => __METHOD__, 'message' => 'get_order_form', 'type' => NOTICE));

            return $Client_Order->get_order_form($model, $options, $custom_template);
        } elseif ($this->getRequestValue('action') == 'send_by_email' && 1 == intval($this->getConfigValue('apps.client.order_mode'))) {
            if ($_SESSION['current_user_group_name'] != 'admin') {
                return json_encode(array('status' => 0, 'txt' => 'Access denied'));
            }
            $emails = $this->getRequestValue('emails');
            $id = intval($_POST['id']);
            if (!is_array($emails) || $id == 0) {
                return json_encode(array('status' => 0, 'txt' => 'Unable'));
            }
            array_filter($emails, function($var) {
                if (trim($var) != '' && filter_var(trim($var), FILTER_VALIDATE_EMAIL)) {
                    return true;
                } else {
                    return false;
                }
            });
            if (empty($emails)) {
                return json_encode(array('status' => 0, 'txt' => 'No recievers'));
            }

            $theme = mb_substr(trim($this->getRequestValue('theme')), 0, 100, 'utf-8');
            if ($theme == '') {
                $theme = 'Информация о заявке';
            }
            $message = mb_substr(trim($this->getRequestValue('message')), 0, 500, 'utf-8');

            $DBC = DBC::getInstance();
            $query = 'SELECT `order_text` FROM ' . DB_PREFIX . '_client WHERE client_id=?';
            $stmt = $DBC->query($query, array($id));
            if (!$stmt) {
                return json_encode(array('status' => 0, 'txt' => 'No recievers'));
            }
            $ar = $DBC->fetch($stmt);

            $this->sendFirmMail($emails, '', $theme, '<div>' . $message . '</div>' . $ar['order_text']);

            return json_encode(array('status' => 1, 'txt' => 'Sended'));
        } elseif ($this->getRequestValue('action') == 'delete_order' && 1 == intval($this->getConfigValue('apps.client.order_mode'))) {
            if ($_SESSION['current_user_group_name'] != 'admin') {
                return json_encode(array('status' => 0, 'txt' => 'Access denied'));
            }
            $id = intval($_POST['id']);
            $this->delete_data($this->table_name, $this->primary_key, $id);
            if ($this->getError()) {
                return json_encode(array('status' => 0, 'txt' => 'Unable'));
            } else {
                return json_encode(array('status' => 1, 'txt' => 'Deleted'));
            }
        } elseif ($this->getRequestValue('action') == 'set_status' && 1 == intval($this->getConfigValue('apps.client.order_mode'))) {
            if ($_SESSION['current_user_group_name'] != 'admin') {
                return json_encode(array('status' => 0, 'txt' => 'Access denied'));
            }

            $DBC = DBC::getInstance();
            $status_id = trim($_POST['status_id']);
            if (isset($this->data_model[$this->table_name]['status_id']['select_data'][$status_id]) && intval($_POST['id']) > 0) {
                $query = 'UPDATE ' . DB_PREFIX . '_client SET status_id=? WHERE client_id=?';
                $stmt = $DBC->query($query, array($status_id, intval($_POST['id'])));
                return json_encode(array('status' => 1, 'txt' => $this->data_model[$this->table_name]['status_id']['select_data'][$status_id]));
            }
            return json_encode(array('status' => 0, 'txt' => 'Access denied'));
        } elseif ($this->getRequestValue('action') == 'save_order_form' && 'post' == strtolower($_SERVER['REQUEST_METHOD'])) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/client/site/site.php';
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/client/admin/client_order.php';
            if(file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/'.$this->getConfigValue('theme').'/apps/client/admin/local_client_order.php')){
                require_once SITEBILL_DOCUMENT_ROOT . '/template/frontend/'.$this->getConfigValue('theme').'/apps/client/admin/local_client_order.php';
                $Client_Order = new Local_Client_Order();
            }else{
                $Client_Order = new Client_Order();
            }


            $model = $this->getRequestValue('model');
            $this->writeLog(array('apps_name' => 'apps.client', 'method' => __METHOD__, 'message' => 'save_order_form', 'type' => NOTICE));

            return $Client_Order->save_order_form($model);
        } elseif ($this->getRequestValue('action') == 'get_client') {

            $user_id = $this->getSessionUserID();
            $access_allow = false;


            if ($user_id == 0) {

            } elseif ($_SESSION['current_user_group_name'] == 'admin') {
                $access_allow = true;
            } elseif (preg_match('/^' . preg_quote($this->getServerFullUrl() . '/account', '/') . '/', $_SERVER['HTTP_REFERER']) && $this->getConfigValue('apps.client.frontend_enable')) {
                $access_allow = true;
            } elseif ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin')) {
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php');
                $permission = new Permission();
                if (!$permission->get_access($user_id, 'client', 'access')) {
                    $access_allow = false;
                } else {
                    $access_allow = true;
                }
            }

            $ret = array();
            if (!$access_allow) {
                return json_encode(array_values($ret));
            }
            $client_ids = array();
            //$phone=preg_replace('/[^\d]/', '', $this->getRequestValue('phone'));
            $phone = trim($this->getRequestValue('phone'));
            if ($phone == '') {
                return json_encode(array_values($ret));
            }
            $DBC = DBC::getInstance();
            $query = 'SELECT `client_id`, `fio`, `phone` FROM ' . DB_PREFIX . '_client WHERE `phone` LIKE ? OR `fio` LIKE ?';
            $stmt = $DBC->query($query, array('%' . $phone . '%', '%' . $phone . '%'));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $ret[$ar['client_id']] = array('n' => $ar['fio'], 'p' => $ar['phone'], 'i' => $ar['client_id']);
                    $ret[$ar['client_id']]['ob'] = 0;
                    $client_ids[] = $ar['client_id'];
                }
            }
            //print_r($ret);
            if (!empty($client_ids)) {
                $query = 'SELECT COUNT(id) AS _cnt, client_id FROM ' . DB_PREFIX . '_data WHERE client_id IN (' . implode(',', $client_ids) . ') GROUP BY client_id';
                $stmt = $DBC->query($query);
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $ret[$ar['client_id']]['ob'] = $ar['_cnt'];
                    }
                }
            }
            return json_encode(array_values($ret));
        } elseif ($this->getRequestValue('action') == 'add_client') {
            $user_id = $this->getSessionUserID();
            $access_allow = false;

            if ($user_id == 0) {

            } elseif ($_SESSION['current_user_group_name'] == 'admin') {
                $access_allow = true;
            } elseif ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin')) {
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php');
                $permission = new Permission();
                if (!$permission->get_access($user_id, 'client', 'access')) {
                    $access_allow = false;
                } else {
                    $access_allow = true;
                }
            }

            if (!$access_allow) {
                $ret['status'] = 0;
            } else {
                $fio = trim($this->getRequestValue('fio'));
                $phone = preg_replace('/[^\d]/', '', $this->getRequestValue('phone'));
                if ($fio != '' && $phone != '') {
                    $newcid = $this->createClient($fio, $phone);
                    if ($newcid !== 0) {
                        $ret['status'] = 1;
                        $ret['id'] = $newcid;
                        $ret['fio'] = $fio;
                        $ret['phone'] = $phone;
                    } else {
                        $ret['status'] = 0;
                    }
                } else {
                    $ret['status'] = 0;
                }
            }



            return json_encode($ret);
        } else {

        }
        return false;
    }

    protected function createClient($fio, $phone) {
        $DBC = DBC::getInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . '_client (fio, phone) VALUES (?, ?)';
        $stmt = $DBC->query($query, array($fio, $phone));
        if ($stmt) {
            return $DBC->lastInsertId();
        }
        return 0;
    }

}
