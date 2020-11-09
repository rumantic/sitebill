<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Model REST class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_model extends API_Common {

    private $record_id;

    public function _get_models() {
        $DBC = DBC::getInstance();
        $query = "SELECT * FROM " . DB_PREFIX . "_table ORDER BY name ASC, table_id DESC";
        $stmt = $DBC->query($query);
        if (!$stmt) {
            return $this->request_failed('models list not defined');
        }
        while ($ar = $DBC->fetch($stmt)) {
            $models[] = array('id' => $ar['table_id'], 'name' => $ar['name']);
        }
        return $this->json_string($models);
    }

    public function _load_config() {
        $SConfig = SConfig::getInstance();
        $data = $SConfig->getPublicConfig();
        $data = $this->extract_config_items($data);
        $local_data = $this->_load_local_config_from_file();
        if ( $local_data ) {
            $data = array_merge($data, $local_data);
        }

        $ret = array(
            'state' => 'success',
            'data' => $data,
        );
        return $this->json_string($ret);
    }

    private function extract_config_items ( $config ) {
        $matches = array();
        preg_match_all('/\{[^\}]+\}/', trim($config['apps.mailbox.complaint_mode_variants']), $matches);
        if (count($matches) > 0) {
            foreach ($matches[0] as $v) {
                $v = str_replace(array('{', '}'), '', $v);
                $d = explode('~~', $v);
                $ret[$d[0]] = $d[1];
            }
        }
        $config['apps.mailbox.complaint_mode_variants'] =  $ret;
        return $config;
    }

    public function _load_local_config_from_file () {
        if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/local/frontend.php') ) {
            return include (SITEBILL_DOCUMENT_ROOT.'/template/frontend/local/frontend.php');
        }
        return false;
    }

    public function _load_only_model( $model_name = null, $custom_model_object = null ) {
        if ( $model_name == null ) {
            $model_name = $this->request->get('model_name');
        }
        if ( $custom_model_object != null ) {
            $model_object = $custom_model_object;
        } else {
            $model_object = $this->init_custom_model_object($model_name);
        }
        if ($model_object) {
            $data_array = $model_object->data_model;
            foreach ($model_object->data_model[$model_name] as $model_item_array) {
                $columns[] = $model_item_array;
            }
            $columns_index = $this->indexing_columns($columns);

            $tabs = $this->extract_tabs($data_array);

            $ret = array(
                'state' => 'success',
                'columns' => $columns,
                'columns_index' => $columns_index['index'],
                'data' => $data_array,
                'tabs' => $tabs
            );
            return $this->json_string($ret);
        }
        return $this->request_failed('model not defined');

    }


    public function _load_data( $custom_model_object = null ) {
        $model_name = $this->request->get('model_name');
        $primary_key = $this->request->get('primary_key');
        $key_value = $this->request->get('key_value');
        if ( $custom_model_object != null ) {
            $model_object = $custom_model_object;
        } else {
            $model_object = $this->init_custom_model_object($model_name);
        }
        $user_id = $this->get_my_user_id();

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $permission = new Permission();
        if (!$permission->get_access($user_id, $model_name, 'access') and !$permission->get_access($user_id, $model_name, 'view')) {
            $response = new API_Response('error', _e('Доступ запрещен'));
            return $this->json_string($response->get());
        }


        if ($model_object) {
            $data_array = $model_object->load_by_id($key_value);
            if ( $key_value == null and $model_name == 'data' and isset($data_array['user_id']) ) {
                $data_array['user_id']['value'] = $user_id;
            }
            $tabs = $this->extract_tabs($data_array);

            $ret = array(
                'state' => 'success',
                $primary_key => $key_value,
                'data' => $data_array,
                'tabs' => $tabs
            );
            return $this->json_string($ret);
        }
        return $this->request_failed('model not defined');
    }

    public function cleanup_model ($model, $allowed_fields) {
        foreach ( $model as $key => $item_array ) {
            if ( !in_array($key, $allowed_fields) ) {
                unset($model[$key]);
            }
        }
        return $model;
    }

    public function _load_any_profile () {
        $user_id = $this->request->get('user_id');
        if ( $user_id > 0 ) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');
            $user_object_manager = new User_Object_Manager();
            $public_fields = array('fio', 'email', 'phone', 'imgfile', 'mobile');

            $user_object_manager->data_model = $user_object_manager->get_user_model();
            $user_object_manager->data_model['user'] = $this->cleanup_model($user_object_manager->data_model['user'], $public_fields);

            if ($user_object_manager) {
                $data_array = $user_object_manager->load_by_id($user_id);
                $tabs = $this->extract_tabs($data_array);

                $ret = array(
                    'state' => 'success',
                    'user_id' => $user_id,
                    'data' => $data_array,
                    'tabs' => $tabs
                );
                return $this->json_string($ret);
            }
        }
        $this->riseError('load_profile_failed');
        return $this->request_failed('load_profile_failed');
    }


    public function extract_tabs($data_array) {
        $tabs = array();
        if (is_array($data_array)) {
            foreach ($data_array as $key => $item_array) {
                if ($item_array['tab'] != '') {
                    $tabs[$item_array['tab']][] = $key;
                } else {
                    $tabs[$this->getConfigValue('default_tab_name')][] = $key;
                }
            }
        }
        if (count($tabs) > 0) {
            return $tabs;
        }
        return false;
    }

    public function _load_grid_columns() {
        $model_name = $this->request->get('model_name');
        $user_id = $this->get_my_user_id();
        $columns = $this->get_columns($model_name, $user_id);
        if ($columns) {
            $response = new API_Response('success', 'load complete', $columns);
        } else {
            $response = new API_Response('error', 'columns list empty');
        }
        return $this->json_string($response->get());
    }

    public function _load_page() {
        $slug = $this->request->get('slug');
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/page/admin/admin.php');
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/page/site/site.php');
        $page_site = new page_site();
        $page = $page_site->load_by_id($page_site->getPageIDByURI($slug));
        if ($page) {
            $response = new API_Response('success', 'load complete', $page);
        } else {
            $response = new API_Response('error', 'page not found');
        }
        return $this->json_string($response->get());
    }


    public function get_columns($model_name, $user_id) {
        $DBC = DBC::getInstance();
        $used_fields = array();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_table_grids WHERE `action_code`=?';
        $action_code = $this->get_grid_action_code($model_name, $user_id);
        $stmt = $DBC->query($query, array($action_code));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $used_fields = json_decode($ar['grid_fields']);
            $meta_fields = (array) json_decode($ar['meta']);
            if (count($used_fields) > 0) {
                $ra['grid_fields'] = $used_fields;
                $ra['meta'] = $meta_fields;
                return $ra;
            }
        }
        return false;
    }

    public function _uppend_uploads() {
        $model_object = $this->init_custom_model_object($this->request->get('model_name'));
        //$this->writeLog($this->request->get('image_field'));
        //$this->writeArrayLog($model_object->data_model);
        //$table, $field, $pk_field, $record_id, $name_template = ''
        if ($this->request->get('model_name') == 'user') {
            $images = $model_object->appendUploadsUser($this->request->get('model_name'), $this->request->get('primary_key'), $this->request->get('key_value'));
        } else {
            $images = $model_object->appendUploads($this->request->get('model_name'), $model_object->data_model[$this->request->get('model_name')][$this->request->get('image_field')], $this->request->get('primary_key'), $this->request->get('key_value'));
        }

        if ($images) {
            $ret = array(
                'state' => 'success',
                'data' => $images
            );
            return $this->json_string($ret);
        }
        return $this->request_failed('uppend_uploads failed '.$model_object->getError());
    }

    public function _delete() {
        $model_name = $this->request->get('model_name');
        $primary_key = $this->request->get('primary_key');
        $key_value = $this->request->get('key_value');
        $user_id = $this->get_my_user_id();

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $permission = new Permission();
        //внутри get_access еще надо реализовать проверку доступа к записям из data
        //сейчас проверка опционально проверяет только группу и разрешает админам удалять

        if ($permission->get_access($user_id, $model_name, 'access')) {


            $model_object = $this->init_custom_model_object($model_name);
            if ($model_object->delete_data($model_name, $primary_key, $key_value)) {
                $response = new API_Response('success', 'delete complete');
            } else {
                $response = new API_Response('error', $model_object->GetErrorMessage());
            }
        } else {
            $response = new API_Response('error', _e('Доступ запрещен'));
        }
        return $this->json_string($response->get());
    }

    public function _report() {
        $model_name = $this->request->get('model_name');
        $primary_key = $this->request->get('primary_key');
        $key_value = $this->request->get('key_value');
        $complaint_id = $this->request->get('complaint_id');
        $user_id = $this->get_my_user_id();

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $permission = new Permission();
        //внутри get_access еще надо реализовать проверку доступа к записям из data
        //сейчас проверка опционально проверяет только группу и разрешает админам удалять

        if ($permission->get_access($user_id, $model_name, 'access')) {


            require_once (SITEBILL_DOCUMENT_ROOT.'/apps/mailbox/admin/admin.php');
            $mailbox_admin = new mailbox_admin();
            $this->setRequestValue('action', 'send_complaint');
            $this->setRequestValue('id', $key_value);
            $this->setRequestValue('complaint_id', $complaint_id);
            $complaint_response = json_decode($mailbox_admin->ajax(), true);

            if ($complaint_response['status'] == 1) {
                $response = new API_Response('success', 'report complete');
            } else {
                $response = new API_Response('error', $complaint_response['msg']);
            }
        } else {
            $response = new API_Response('error', _e('Доступ запрещен'));
        }
        return $this->json_string($response->get());
    }


    public function _delete_data() {
        $user_id = $this->get_my_user_id();

        $data_id = $this->request->get('id');

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $permission = new Permission();
        //внутри get_access еще надо реализовать проверку доступа к записям из data
        //сейчас проверка опционально проверяет только группу и разрешает админам удалять
        if ($permission->get_access($user_id, 'data', 'access')) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/data/data_manager.php');
            $data_manager = new Data_Manager();
            if ($data_manager->delete_data('data', 'id', $data_id)) {
                $response = new API_Response('success', 'delete complete');
            } else {
                $response = new API_Response('error', $data_manager->GetErrorMessage());
            }
        } else {
            $response = new API_Response('error', 'error on delete');
        }
        return $this->json_string($response->get());
    }

    public function _unpublish_data() {
        $user_id = $this->get_my_user_id();

        $data_id = $this->request->get('id');

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $permission = new Permission();
        //внутри get_access еще надо реализовать проверку доступа к записям из data
        //сейчас проверка опционально проверяет только группу и разрешает админам удалять
        if ($permission->get_access($user_id, 'data', 'access')) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/data/data_manager.php');
            $data_manager = new Data_Manager();
            $data_array = $data_manager->load_by_id($data_id);
            $data_array['active']['value'] = 0;
            $data_manager->edit_data($data_array, 0, $data_id);
            if (!$data_manager->getError()) {
                $response = new API_Response('success', 'unpublish complete');
            } else {
                $response = new API_Response('error', $data_manager->GetErrorMessage());
            }
        } else {
            $response = new API_Response('error', 'error on unpublish');
        }
        return $this->json_string($response->get());
    }

    public function _get_model() {
        $model_id = $this->getRequestValue('id');
        //$this->writeLog(__METHOD__.', id = '.$model_id);

        $DBC = DBC::getInstance();
        $query = "SELECT name FROM " . DB_PREFIX . "_table WHERE table_id=?";
        $stmt = $DBC->query($query, array($model_id));
        if (!$stmt) {
            return $this->request_failed('model not defined');
        }
        $ar = $DBC->fetch($stmt);
        $model_name = $ar['name'];
        if ($model_name != '') {

            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/customentity/admin/admin.php');
            $customentity_admin = new customentity_admin();
            $customentity_admin->custom_construct($model_name);
            $ret = array(
                'id' => $model_id,
                'name' => $model_name,
                'columns' => array_values($customentity_admin->data_model[$model_name])
            );
            //$this->writeLog(__METHOD__ . ', name = ' . $model_name);
            //$this->writeLog(__METHOD__ . ', model = <pre>' . var_export($customentity_admin->data_model, true) . '</pre>');
            return $this->json_string($ret);
        }
        return $this->request_failed('model not defined');
    }

    public function _select() {
        //$data_id = $this->getRequestValue('id');
        $data = json_decode(file_get_contents('php://input'), true);

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/apiproxy/admin/crm_object.php');
        $crm_object = new crm_object();
        $crm_object->save_selected($this->get_placement(), $this->get_crm_item_id(), $this->get_site(), $this->get_site_user_id(), $data['selected_items']);

        $ret = array('data_id' => $data['selected_items']);
        return $this->json_string($ret);
    }

    public function _load_selected() {
        //$data_id = $this->getRequestValue('id');
        $data = json_decode(file_get_contents('php://input'), true);

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/apiproxy/admin/crm_object.php');
        $crm_object = new crm_object();
        $selected = $crm_object->load_selected($this->get_placement(), $this->get_crm_item_id(), $this->get_site(), $this->get_site_user_id());

        $ret = array('selected' => $selected);
        return $this->json_string($ret);
    }

    public function _load_dictionary() {
        $columnName = $this->request->get('columnName');
        $model_name = $this->request->get('model_name');
        $term = $this->request->get('term');
        $switch_off_ai_mode = $this->request->get('switch_off_ai_mode');

        if ($model_name == '') {
            $model_name = 'data';
        }


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/components/model_tags/model_tags.php');
        $model_tags = new model_tags();
        //$this->writeLog($switch_off_ai_mode);
        //$this->writeLog(boolval($switch_off_ai_mode));
        if (boolval($switch_off_ai_mode)) {
            //$this->writeLog('switch off ai mode');
            $model_tags->set_ai_mode(false);
        } else {
            //$this->writeLog('switch on ai mode');
            $model_tags->set_ai_mode(true);
        }

        $model_tags->enable_primary_key_mode();
        if ( !empty($term) ) {
            $model_tags->set_term($term);
        }

        $model_object = $this->init_custom_model_object($model_name);

        $dictionary_array = $model_tags->get_array($model_name, $columnName, 'array', $model_object->data_model[$model_name]);
        //$this->writeArrayLog($dictionary_array);

        if ($model_tags->getError()) {
            $response = new API_Response('error', $model_tags->GetErrorMessage());
            return $this->json_string($response->get());
        } else {
            $ret = array('data' => $dictionary_array);
        }
        return $this->json_string($ret);
    }

    public function _load_dictionary_with_params() {
        $columnName = $this->request->get('columnName');
        $model_name = $this->request->get('model_name');
        $params = $this->request->get('params');
        $switch_off_ai_mode = $this->request->get('switch_off_ai_mode');

        if ($model_name == '') {
            $model_name = 'data';
        }


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/components/model_tags/model_tags.php');
        $model_tags = new model_tags();
        //Будем сохранять всю информацию о массиве
        $model_tags->set_store_all_mode(true);
        //$this->writeLog($switch_off_ai_mode);
        //$this->writeLog(boolval($switch_off_ai_mode));
        if (boolval($switch_off_ai_mode)) {
            //$this->writeLog('switch off ai mode');
            $model_tags->set_ai_mode(false);
        } else {
            //$this->writeLog('switch on ai mode');
            $model_tags->set_ai_mode(true);
        }

        $model_tags->enable_primary_key_mode();

        $model_object = $this->init_custom_model_object($model_name);

        $dictionary_array = $model_tags->get_array($model_name, $columnName, 'array', $model_object->data_model[$model_name]);
        if ( $this->getConfigValue('system_email') == 'info@sklyuchami.com' and (isset($params['region_id']) or isset($params['topic_id'])) ) {
            $dictionary_array = $this->cleanup_array($model_object, $columnName, $dictionary_array, $params, $model_tags);
        }
        //$this->writeArrayLog($dictionary_array);

        if ($model_tags->getError()) {
            $response = new API_Response('error', $model_tags->GetErrorMessage());
            return $this->json_string($response->get());
        } else {
            $ret = array('data' => $dictionary_array);
        }
        return $this->json_string($ret);
    }

    function cleanup_array($model_object, $columnName, $dictionary_array, $params, model_tags $model_tags) {
        $DBC = DBC::getInstance();
        $store = $model_tags->get_store();
        $region_id = $params['region_id'];

        if ( $columnName == 'object_type' and $params['topic_id'] > 0 ) {
            // $this->writeLog('$columnName');
            // $this->writeLog('topic_id = '.$params['topic_id']);
            // $this->writeArrayLog($store);
            if ( is_array($store) ) {
                foreach ($store as $object_type_id => $object_type_items) {
                    if ( $object_type_items['topic_id'] ==  $params['topic_id']) {
                        $result_dictionary[] = array('id' => $object_type_id, 'value' => $object_type_items['name']);
                    }
                }
                // $this->writeArrayLog($result_dictionary);
                return $result_dictionary;
            }
        }

        if ( !isset($region_id) ) {
            return $dictionary_array;
        }

        //$this->writeArrayLog($model_tags->get_store());
        //$this->writeLog('store');
        //$this->writeArrayLog($columnName);
        if ( $columnName == 'city_id' ) {
            // Получаем список ID городов для этого региона
            $query = "select city_id from ".DB_PREFIX."_city where region_id=?";
            $stmt = $DBC->query($query, array($region_id));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    if ( isset($store[$ar['city_id']]) ) {
                        $result_dictionary[] = array('id' => $ar['city_id'], 'value' => $store[$ar['city_id']]['name']);
                    }

                }
            }
            return $result_dictionary;

        }

        if ( $columnName == 'district_id' ) {
            //$this->writeLog('$columnName');
            //$this->writeLog('region_id = '.$region_id);
            //$this->writeArrayLog($store);

            $query = "select d.id from ".DB_PREFIX."_district d, ".DB_PREFIX."_city c where c.region_id=? AND d.city_id=c.city_id";
            //$this->writeLog($query);
            $stmt = $DBC->query($query, array($region_id));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    if ( isset($store[$ar['id']]) ) {
                        $result_dictionary[] = array('id' => $ar['id'], 'value' => $store[$ar['id']]['name']);
                    }
                }
            }
            return $result_dictionary;

            //$this->writeArrayLog($result_dictionary);
        }

        if ( $columnName == 'street_id' ) {
            $query = "select s.street_id from ".DB_PREFIX."_street s, ".DB_PREFIX."_city c where c.region_id=? AND s.city_id=c.city_id";
            $stmt = $DBC->query($query, array($region_id));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    if ( isset($store[$ar['street_id']]) ) {
                        $result_dictionary[] = array('id' => $ar['street_id'], 'value' => $store[$ar['street_id']]['name']);
                    }
                }
            }
            return $result_dictionary;
        }

        if ( $columnName == 'object_type' ) {
            /*
            $query = "select s.street_id from ".DB_PREFIX."_street s, ".DB_PREFIX."_city c where c.region_id=? AND s.city_id=c.city_id";
            $stmt = $DBC->query($query, array($region_id));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    if ( isset($store[$ar['street_id']]) ) {
                        $result_dictionary[] = array('id' => $ar['street_id'], 'value' => $store[$ar['street_id']]['name']);
                    }
                }
            }
            return $result_dictionary;
            */
        }


        if ( $columnName == 'Zonag_id' ) {
            //$this->writeLog('$columnName');
            //$this->writeLog('region_id = '.$region_id);
            //$this->writeArrayLog($store);

            $query = "select z.Zonag_id from ".DB_PREFIX."_Zonag z, ".DB_PREFIX."_city c where c.region_id=? AND z.city_id=c.city_id";
            //$this->writeLog($query);
            $stmt = $DBC->query($query, array($region_id));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    if ( isset($store[$ar['Zonag_id']]) ) {
                        $result_dictionary[] = array('id' => $ar['Zonag_id'], 'value' => $store[$ar['Zonag_id']]['name']);
                    }
                }
            }
            return $result_dictionary;

            //$this->writeArrayLog($result_dictionary);
        }


        return $dictionary_array;
    }


    public function _load_ads_by_term() {
        $term = $this->request->get('term');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/search/admin/admin.php');
        $search_admin = new search_admin();
        $search_result = $search_admin->get_terms($term, true);
        if ($search_admin->getError()) {
            $response = new API_Response('error', $search_admin->GetErrorMessage());
            return $this->json_string($response->get());
        } else {
            $ret = $search_result;
            //$ret = array('data' => $this->fake_adv());
        }
        return $this->json_string($ret);
    }

    function fake_adv() {
        $ra = array();
        for ($i = 0; $i < 5; $i++) {
            $ra[] = array('adv' => 'login' . $i);
        }
        return $ra;
    }

    public function update_native_request_params($ql_items, $model_object = false) {
        foreach ($ql_items as $key => $value) {
            if ( $model_object ) {
                if ( $model_object->data_model[$model_object->table_name][$key]['type'] == 'checkbox' ) {
                    if ( $value == 0 ) {
                        $value = NULL;
                    }
                }
            }
            $_REQUEST[$key] = $value;
            $_POST[$key] = $value;
            $this->setRequestValue($key, $value);
        }
    }

    public function _native_update() {
        $model_name = $this->request->get('model_name');
        $key_value = $this->request->get('key_value');
        $ql_items = $this->request->get('ql_items');
        $user_id = $this->get_my_user_id();
        //$this->writeArrayLog($ql_items);

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $permission = new Permission();
        // @todo: Экспериментальная проверка на edit
        if ($permission->get_access($user_id, $model_name, 'edit')) {
            if ( $ql_items['user_id'] != $user_id ) {
                $response = new API_Response('error', _e('Доступ запрещен'));
                return $this->json_string($response->get());
            }
        } elseif (!$permission->get_access($user_id, $model_name, 'access')) {
            $response = new API_Response('error', _e('Доступ запрещен'));
            return $this->json_string($response->get());
        }

        $model_object = $this->init_custom_model_object($model_name);
        if (count($ql_items) > 0) {
            $this->update_native_request_params($ql_items, $model_object);
        }

        $primary_key = $model_object->primary_key;
        $this->setRequestValue($primary_key, $key_value);
        $this->setRequestValue('do', 'edit_done');
        $model_object->rest_edit_done();
        if ($model_object->getError()) {
            $response = new API_Response('error', $model_object->GetErrorMessage());
        } else {
            $response = new API_Response('success', 'edit native complete');
        }
        return $this->json_string($response->get());
    }

    public function _native_insert() {
        $model_name = $this->request->get('model_name');
        $key_value = $this->request->get('key_value');
        $ql_items = $this->request->get('ql_items');
        $user_id = $this->get_my_user_id();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $permission = new Permission();
        if (!$permission->get_access($user_id, $model_name, 'access')) {
            $response = new API_Response('error', _e('Доступ запрещен'));
            return $this->json_string($response->get());
        }

        $model_object = $this->init_custom_model_object($model_name);
        if (count($ql_items) > 0) {
            $this->update_native_request_params($ql_items, $model_object);
        }

        $primary_key = $model_object->primary_key;
        $this->setRequestValue('do', 'new_done');
        $model_object->rest_new_done();
        if ($model_object->getError()) {
            $response = new API_Response('error', $model_object->GetErrorMessage());
        } else {
            $new_record_id = $model_object->get_new_record_id();
            $data = $model_object->load_by_id($new_record_id);
            $response = new API_Response('success', 'new native complete', array('new_record_id' => $new_record_id, 'items' => $data));
        }
        return $this->json_string($response->get());
    }

    /**
     * Универсальный метод для редактирования любой сущности. В аргументах передаем массив редактируемых значений
     */
    public function _graphql_update() {
        $model_name = $this->request->get('model_name');
        $key_value = $this->request->get('key_value');
        $ql_items = $this->request->get('ql_items');
        $only_ql = $this->request->get('only_ql');
        $user_id = $this->get_my_user_id();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $permission = new Permission();
        if (!$permission->get_access($user_id, $model_name, 'access')) {
            $response = new API_Response('error', _e('Доступ запрещен'));
            return $this->json_string($response->get());
        }



        //$this->writeLog('key_value = '.$key_value);
        //$this->writeArrayLog($ql_items);



        $model_object = $this->init_custom_model_object($model_name);

        if (count($ql_items) > 0) {
            $this->update_native_request_params($ql_items, $model_object);
        }

        $primary_key = $model_object->primary_key;
        $this->setRequestValue($primary_key, $key_value);

        if ((int) $key_value == 0) {
            //Предварительно проверим данные, чтобы не создавать заведомо неправильную запись
            $model_data = $model_object->data_model[$model_name];
            $model_data = $data_model->init_model_data_from_request($model_data);
            if (!$model_object->check_data($model_data)) {
                $response = new API_Response('error', $model_object->GetErrorMessage());
                return $this->json_string($response->get());
            }

            //$this->writeArrayLog($model_data['price']);
            //$this->writeLog('key value 0 = '.$key_value);


            $this->_new_empty_record();
            if ($this->getError()) {
                $response = new API_Response('error', $this->GetErrorMessage());
                return $this->json_string($response->get());
            }
            $key_value = $this->get_record_id();
            $model_data[$primary_key]['value'] = $key_value;
            $this->setRequestValue($primary_key, $key_value);
        } else {
            //$this->writeLog('key value not 0 = '.$key_value);

            $model_data = $model_object->load_by_id($key_value);
            $model_data = $data_model->init_model_data_from_request($model_data);
            $model_data[$primary_key]['value'] = $key_value;
        }
        //$this->writeLog('key_value after = '.$key_value);

        $this->setRequestValue('do', 'edit_done');

        //$this->writeLog(var_export($model_data, true));
        if ($model_object->getError()) {
            $response = new API_Response('error', $model_object->GetErrorMessage());
            return $this->json_string($response->get());
        }
        if (!$model_data) {
            $response = new API_Response('error', 'record not found');
            return $this->json_string($response->get());
        }

        if (count($ql_items) > 0) {
            //$this->writeArrayLog($ql_items);
            //$this->writeArrayLog($_POST);
            //$this->writeArrayLog($model_data['price']);
            //$this->writeLog('after first init');
            //$this->writeArrayLog($model_data['price']);
            //$this->writeLog('second init');

            foreach ($ql_items as $key => $value) {
                //$model_data[$key]['value'] = $value;
                if ($only_ql and $key != $primary_key) {
                    $new_model[$model_name][$key] = $model_object->data_model[$model_name][$key];
                }
            }
            if ($only_ql) {
                $new_model[$model_name][$primary_key] = $model_object->data_model[$model_name][$primary_key];
                $model_object->data_model = $new_model;
                foreach ($model_data as $key => $item) {
                    if (!isset($new_model[$model_name][$key]) and $key != $primary_key) {
                        unset($model_data[$key]);
                    }
                }
            }
        }

        $model_data = $model_object->_before_check_action($model_data, 'edit');
        //$this->writeArrayLog($ql_items);
        //$this->writeArrayLog($model_data);
        //$this->writeArrayLog($this->request->dump(), true);
        //$this->writeArrayLog($_REQUEST, true);

        if (!$model_object->check_data($model_data)) {
            $response = new API_Response('error', $model_object->GetErrorMessage());
        } else {
            $model_data = $model_object->_before_edit_done_action($model_data);
            $model_object->edit_data($model_data, 0, $key_value);

            if ($model_object->getError()) {
                $response = new API_Response('error', $model_object->GetErrorMessage());
            } else {
                if ($this->getConfigValue('apps.realtylog.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/admin/admin.php';
                    $Logger = new realtylog_admin();
                    $Logger->addLog($model_data, $user_id, 'edit', 'data');
                }
                if ($this->getConfigValue('apps.realtylogv2.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                    $Logger = new realtylogv2_admin();
                    $Logger->addLog($model_data, $user_id, 'edit', 'data', 'id');
                }


                $response = new API_Response('success', 'edit ql complete');
            }
        }

        return $this->json_string($response->get());
    }

    function set_record_id($record_id) {
        $this->record_id = $record_id;
    }

    function get_record_id() {
        return $this->record_id;
    }

    /**
     * Создание пустой записи и возвращаем ИД новой записи
     */
    public function _new_empty_record() {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        $model_name = $this->request->get('model_name');
        $user_id = $this->get_my_user_id();
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $permission = new Permission();
        if (!$permission->get_access($user_id, $model_name, 'access')) {
            $response = new API_Response('error', _e('Доступ запрещен'));
            return $this->json_string($response->get());
        }

        //$this->writeLog(var_export($ql_items, true));

        $model_object = $this->init_custom_model_object($model_name);
        $model_data = $model_object->data_model[$model_name];
        $model_data = $data_model->init_model_data_from_request($model_data);

        //$this->writeLog(var_export($model_data, true));
        if ($model_object->getError()) {
            $this->riseError($model_object->GetErrorMessage());
            $response = new API_Response('error', $model_object->GetErrorMessage());
            return $this->json_string($response->get());
        }
        if (!$model_data) {
            $response = new API_Response('error', 'model not found');
            return $this->json_string($response->get());
        }

        if (count($model_data) > 0) {
            foreach ($model_data as $key => $value) {
                $model_data[$key]['required'] = 'off';
            }
        }
        $new_record_id = $model_object->add_data($model_data, 0);
        if ($model_object->getError()) {
            $this->riseError($model_object->GetErrorMessage());
            $response = new API_Response('error', $model_object->GetErrorMessage());
        } else {
            //Если запись создалась, то вернем массив того что создалось
            $model_data = $model_object->load_by_id($new_record_id);
            $this->set_record_id($new_record_id);

            $response = new API_Response('success', $model_data);
        }
        return $this->json_string($response->get());
    }

    public function _delete_selection() {
        //$data_id = $this->getRequestValue('id');
        $data = json_decode(file_get_contents('php://input'), true);
        //$this->writeLog(__METHOD__ . var_export($data, true));

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $permission = new Permission();
        if (!$permission->get_access($user_id, 'data', 'access')) {
            $response = new API_Response('error', _e('Доступ запрещен'));
            return $this->json_string($response->get());
        }

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/apiproxy/admin/crm_object.php');
        $crm_object = new crm_object();
        $crm_object->delete_selection($this->get_placement(), $this->get_crm_item_id(), $this->get_site(), $this->get_site_user_id(), $data['item_id']);

        $selected = $crm_object->load_selected($this->get_placement(), $this->get_crm_item_id(), $this->get_site(), $this->get_site_user_id());
        $ret = array('selected' => $selected);
        return $this->json_string($ret);
    }

    function get_site_user_id() {
        return 1;
    }

    function get_site() {
        return 'test.ru';
    }

    function get_placement() {
        if ($_SESSION['PLACEMENT'] != '') {
            return $_SESSION['PLACEMENT'];
        } else {
            return 'SITEBILL_DEV_PLACEMENT';
        }
    }

    function get_crm_item_id() {
        if ($_SESSION['PLACEMENT_OPTIONS'] != '') {
            $ar = json_decode($_SESSION['PLACEMENT_OPTIONS']);
            return $ar->ID;
        } else {
            return 111;
        }
    }

    public function _set_user_id_for_client() {
        $client_id = $this->request->get('client_id');
        $user_id = $this->get_my_user_id();

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $permission = new Permission();
        if (!$permission->get_access($user_id, 'client', 'access')) {
            $response = new API_Response('error', _e('Доступ запрещен'));
            return $this->json_string($response->get());
        }


        $model_object = $this->init_custom_model_object('client');
        $client_data = $model_object->load_by_id($client_id);
        if ($client_data['user_id']['value'] != 0 and $client_data['user_id']['value'] != $user_id) {
            return $this->request_failed('client_already_has_owner');
        }
        if ($client_data['user_id']['value'] != 0 and $client_data['user_id']['value'] == $user_id) {
            $client_data['user_id']['value'] = 0;
        } else {
            $client_data['user_id']['value'] = $user_id;
        }
        $model_object->edit_data($client_data, 0, $client_id);
        if ($model_object->getError()) {
            return $this->request_failed($model_object->GetErrorMessage());
        }
    }

    public function load_meta($model_name, $user_id) {
        $action = $this->get_grid_action_code($model_name, $user_id);
        $DBC = DBC::getInstance();
        $query = 'SELECT meta FROM ' . DB_PREFIX . '_table_grids WHERE action_code=?';
        $stmt = $DBC->query($query, array($action));
        if (!$stmt) {
            return false;
        }
        $ar = $DBC->fetch($stmt);

        $result = array();
        $meta_array = (array) json_decode($ar['meta']);
        if (count($meta_array) > 0) {
            foreach ($meta_array as $key => $item) {
                if (is_object($item)) {
                    $result[$key] = (array) $item;
                } else {
                    $result[$key] = $item;
                }
            }
        }

        return $result;
    }

    public function _update_column_meta() {
        $model_name = $this->request->get('model_name');
        $column_name = $this->request->get('column_name');
        $key = $this->request->get('key');
        $params = $this->request->get('params');
        $user_id = $this->get_my_user_id();

        $action = $this->get_grid_action_code($model_name, $user_id);
        $current_meta = $this->load_meta($model_name, $user_id);
        //$this->writeLog('$current_meta');
        //$this->writeArrayLog($current_meta);
        //$response = new API_Response('success', 'true', $current_meta);
        //return $this->json_string($response->get());



        $DBC = DBC::getInstance();
        if (count($params) > 0) {
            if ($column_name != '') {
                $current_meta[$key][$column_name] = $params;
            } else {
                $current_meta[$key] = $params;
            }
            $query = 'INSERT INTO ' . DB_PREFIX . '_table_grids (`action_code`, `meta`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `meta`=?';
            $stmt = $DBC->query($query, array($action, json_encode($current_meta), json_encode($current_meta)));
        }
        if (!$stmt) {
            return $this->request_failed('update format_grid meta failed: ' . $DBC->getLastError());
        }
        $response = new API_Response('success', 'true');
        return $this->json_string($response->get());
    }

    private function update_meta_value_by_key($model_name, $user_id, $key, $value) {
        $current_meta = $this->load_meta($model_name, $user_id);
        $action = $this->get_grid_action_code($model_name, $user_id);

        $DBC = DBC::getInstance();
        $current_meta[$key] = $value;
        $query = 'INSERT INTO ' . DB_PREFIX . '_table_grids (`action_code`, `meta`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `meta`=?';
        $stmt = $DBC->query($query, array($action, json_encode($current_meta), json_encode($current_meta)));

        if (!$stmt) {
            return false;
        }
        return true;
    }

    public function _get_data() {
        $model_name = $this->request->get('model_name');
        $owner = $this->request->get('owner');
        $input_params = $this->request->get('params');
        $page = $this->request->get('page');
        $per_page = $this->request->get('per_page');
        $user_id = $this->get_my_user_id();
        //$this->writeArrayLog($input_params);
        $load_collections = false;
        $only_collections = false;
        if ( isset($input_params['load_collections']) ) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/admin/memory_list.php';
            $ML = new Memory_List();

            $load_collections = true;
            $collections_domain = $input_params['collections_domain'];
            $collections_deal_id = $input_params['collections_deal_id'];
            if ( isset($input_params['only_collections']) ) {
                $only_collections = true;
                //unset($input_params['only_collections']);
            }
            $this->writeLog($collections_deal_id);
            $this->writeLog($only_collections);
            unset($input_params['load_collections']);
            unset($input_params['collections_domain']);
            unset($input_params['collections_deal_id']);
        }
        $this->writeArrayLog($input_params);


        if ($model_name == '') {
            $model_name = 'data';
        }
        if ($model_name != '') {

            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
            $permission = new Permission();

            //@todo надо решить как быть с anonymous доступом, чтобы могли получать доступ гости к таблице объявлений, например.
            if (!$permission->get_access($user_id, $model_name, 'access') and !$permission->get_access($user_id, $model_name, 'view')) {
                $response = new API_Response('error', _e('Доступ запрещен'));
                return $this->json_string($response->get());
            }

            $customentity_admin = $this->init_custom_model_object($model_name);

            foreach ($customentity_admin->data_model[$model_name] as $model_item_array) {
                $columns[] = $model_item_array;
            }
            //$this->writeLog(json_encode($columns));
            //$this->writeLog('<pre>'. var_export($ee, true).'</pre>');
            //$this->writeArrayLog($columns);
            $columns_index = $this->indexing_columns($columns);
            $grid_columns = $this->get_columns($model_name, $user_id);
            if (!$grid_columns) {
                //$grid_columns = array_keys($columns_index['index']);
            }
            //$this->writeLog('$grid_columns');
            //$this->writeArrayLog($grid_columns);
            if ($per_page == 0 and isset($grid_columns['meta']['per_page'])) {
                $per_page = $grid_columns['meta']['per_page'];
            } elseif ($per_page == 0) {
                $per_page = $this->getConfigValue('per_page');
            }


            if ($this->request->get('grid_item')) {
                $params['grid_item'] = $this->request->get('grid_item');
                //Принудительно добавляем в список колонок primary key
                array_push($params['grid_item'], $customentity_admin->primary_key);
                if (isset($customentity_admin->data_model[$model_name]['active'])) {
                    array_push($params['grid_item'], 'active');
                }
                if (isset($customentity_admin->data_model[$model_name]['hot'])) {
                    array_push($params['grid_item'], 'hot');
                }
            } else {
                if ( $model_name == 'data' ) {
                    $params['grid_item'] = array(
                        'id',
                        'date_added',
                        'topic_id',
                        'city_id',
                        'district_id',
                        'street_id',
                        'number',
                        'price',
                        'image'
                    );
                } else {
                    $params['grid_item'] = array_slice($columns_index['default_columns_list'], 0, 7);
                }
                if (!$grid_columns) {
                    $grid_columns['grid_fields'] = $params['grid_item'];
                }
            }

            if ($model_name == 'client') {
                if ($owner) {
                    $user_id = $this->get_my_user_id();
                    $default_params['render_user_id'] = $user_id;
                    $params['grid_conditions']['user_id'] = $user_id;
                } else {
                    //$params['grid_conditions']['user_id'] = 0;
                }
            }

            if (
                (1 === (int) $this->getConfigValue('check_permissions')) &&
                ($_SESSION['current_user_group_name'] !== 'admin') &&
                (1 === (int) $this->getConfigValue('data_adv_share_access')) &&
                (0 === (int) $this->getConfigValue('data_adv_share_access_can_view_all')) &&
                $model_name == 'data'
            ) {
                $params['grid_conditions']['user_id'] = $user_id;
            }
            //unset($params['grid_conditions']['user_id']);
            //unset($default_params['render_user_id']);
            $params['page'] = $page;
            $params['per_page'] = $per_page;
            if ( $user_id == 0 and isset($customentity_admin->data_model[$model_name]['active'])) {
                $input_params['active'] = 1;
            }

            //Переопределяем параметры если они пришли к нам из запроса
            if (count($input_params) > 0) {
                $params = $this->convert_to_grid_conditions(
                    $params,
                    $input_params,
                    $customentity_admin,
                    $model_name,
                    $ML,
                    $user_id,
                    $collections_domain,
                    $collections_deal_id
                );
            }
            if ( function_exists('api_model_get_data_grid_conditions_sql') ) {
                $hook_conditions = api_model_get_data_grid_conditions_sql($model_name);
                if ( $hook_conditions and count($hook_conditions) > 0 ) {
                    foreach ( $hook_conditions as $hook_key => $hook_value ) {
                        $params['grid_conditions_sql'][$hook_key] = $hook_value;
                    }
                }
            }

            //$this->writeLog('<h1>params</h1>');
            //$this->writeArrayLog($params);
            $rows = $customentity_admin->grid_array($params, $default_params);
            if ($customentity_admin->getError()) {
                $response = new API_Response('error', $customentity_admin->GetErrorMessage());
                return $this->json_string($response->get());
            }
            //$this->writeLog('<b>current</b>');
            $rows_index = $this->indexing_rows($rows, $customentity_admin->primary_key);
            if ( $load_collections ) {
                $rows = $ML->parse_memory_list($collections_domain, $collections_deal_id, $user_id, $customentity_admin->primary_key, $rows);
            }


            //$this->writeArrayLog($customentity_admin->data_model[$model_name]);
            //$columns = array_values($customentity_admin->data_model[$model_name]);

            $ret = array(
                'id' => $model_id,
                'name' => $model_name,
                'per_page' => $per_page,
                'total_count' => $customentity_admin->get_total_count(),
                'columns' => $columns,
                'columns_index' => $columns_index['index'],
                'rows_index' => $rows_index['index'],
                'default_columns_list' => $columns_index['default_columns_list'],
                'grid_columns' => $grid_columns,
                'rows' => $rows,
            );
            //$this->writeLog(__METHOD__ . ', name = ' . $model_name);
            //$this->writeLog(__METHOD__ . ', rows = <pre>' . var_export($ret, true) . '</pre>');
            $result = $this->json_string($ret);
            //$this->writeLog($result);
            return $result;
        }
        return $this->request_failed('model not defined');
    }

    public function convert_to_grid_conditions (
        $params,
        $input_params,
        $customentity_admin,
        $model_name,
        $ML = null,
        $user_id = null,
        $collections_domain = null,
        $collections_deal_id = null
    ) {

        if ( isset($input_params['load_collections']) ) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/admin/memory_list.php';
            $ML = new Memory_List();

            $load_collections = true;
            $collections_domain = $input_params['collections_domain'];
            $collections_deal_id = $input_params['collections_deal_id'];
            if ( isset($input_params['only_collections']) ) {
                $only_collections = true;
                //unset($input_params['only_collections']);
            }
            //$this->writeLog($collections_deal_id);
            //$this->writeLog($only_collections);
            unset($input_params['load_collections']);
            unset($input_params['collections_domain']);
            unset($input_params['collections_deal_id']);
        }

        foreach ($input_params as $key => $value) {
            if ($key == 'price_min' or $key == 'price_max') {
                if ($key == 'price_min') {
                    $params['grid_conditions_sql'][$key] = '`'.DB_PREFIX.'_'.$model_name.'`.`price` >= ' . (int) $value;
                }
                if ($key == 'price_max') {
                    $params['grid_conditions_sql'][$key] = '`'.DB_PREFIX.'_'.$model_name.'`.`price` <= ' . (int) $value;
                }
            } elseif ($key == 'concatenate_search') {
                $concatenate_condition = $this->compile_concatenate_condition($model_name, $customentity_admin->data_model[$model_name], $value);
                $params['grid_conditions_left_join'] = $this->compile_concatenate_condition_left_join($model_name, $customentity_admin->data_model[$model_name], $value);
                if ($concatenate_condition) {
                    $params['grid_conditions_sql']['concatenate_search'] = $concatenate_condition;
                    if (is_array($params['grid_conditions_left_join']['where'])) {
                        $params['grid_conditions_sql']['concatenate_search'] = ' ('.$params['grid_conditions_sql']['concatenate_search'].' OR '.' ( '.implode(' OR ', $params['grid_conditions_left_join']['where']).' ) '.') ';
                    }
                }

            } elseif ($customentity_admin->data_model[$model_name][$key]['type'] == 'uploads') {
                $ignore_uploads_condition = false;
                if ( is_array($value) ) {
                    if ( count($value) > 1 ) {
                        $ignore_uploads_condition = true;
                    } elseif (in_array(1, $value)) {
                        $only_image_condition = true;
                    } else {
                        $without_image_condition = true;
                    }
                } else {
                    if ($value == 1) {
                        $only_image_condition = true;
                    } else {
                        $without_image_condition = true;
                    }
                }
                if ($only_image_condition == 1) {
                    $condition_uploads = " not in ('', 'a:0:{}') ";
                    $uploads_null_condidtion .= " AND `".DB_PREFIX."_{$model_name}`.`{$customentity_admin->data_model[$model_name][$key]['name']}` IS NOT NULL ";
                } else {
                    $condition_uploads = " in ('', 'a:0:{}' ) ";
                    $uploads_null_condidtion .= " OR  `".DB_PREFIX."_{$model_name}`.`{$customentity_admin->data_model[$model_name][$key]['name']}` IS NULL ";
                }

                if ( !$ignore_uploads_condition ) {
                    $params['grid_conditions_sql'][$key] = "( `".DB_PREFIX."_{$model_name}`.`{$customentity_admin->data_model[$model_name][$key]['name']}` " . $condition_uploads . $uploads_null_condidtion . " ) ";
                }
            } elseif ($customentity_admin->data_model[$model_name][$key]['type'] == 'compose') {
                $composed_query = $this->compile_composed_query($model_name, $customentity_admin->data_model[$model_name], $key, $input_params[$key]);
                if ( $composed_query ) {
                    $params['grid_conditions_sql'][$key] = $composed_query;
                }
            } elseif ($customentity_admin->data_model[$model_name][$key]['type'] == 'dtdatetime') {
                if ($value['startDate'] != NULL and $value['endDate'] != NULL) {
                    $params['grid_conditions_sql'][$key] = "( `".DB_PREFIX."_{$model_name}`.`$key` >= '" . date('Y-m-d', strtotime($value['startDate'])) . "' and `".DB_PREFIX."_{$model_name}`.`$key` <= '" . date('Y-m-d', strtotime($value['endDate'])) . " 23:59:59') ";
                }
            } elseif ($customentity_admin->data_model[$model_name][$key]['type'] == 'date') {
                if ($value['startDate'] != NULL and $value['endDate'] != NULL) {
                    $params['grid_conditions_sql'][$key] = "( `".DB_PREFIX."_{$model_name}`.`$key` >= " . strtotime($value['startDate']) . " and `".DB_PREFIX."_{$model_name}`.`$key` <= " . strtotime($value['endDate']) . ") ";
                }
            } elseif ($key == 'only_collections') {
                $collections_ids = $ML->getUserMemoryLists_indexed_by_data_id($user_id, $collections_domain, $collections_deal_id);
                $this->writeArrayLog($collections_ids);
                if (is_array($collections_ids) and count($collections_ids) > 0 ) {
                    $params['grid_conditions_sql']['collections_ids'] = "`".DB_PREFIX."_{$model_name}`.`".$customentity_admin->primary_key."` in (". implode(',', array_keys($collections_ids)).") ";
                } else {
                    $params['grid_conditions_sql']['collections_ids'] = "`".DB_PREFIX."_{$model_name}`.`".$customentity_admin->primary_key."` in (null) ";
                }
                unset($input_params['only_collections']);
            } else {
                $params['grid_conditions'][$key] = $value;
            }
        }
        return $params;
    }

    private function compile_concatenate_condition($model_name, $data_model, $value) {
        $concatenate_columns = array();
        foreach ($data_model as $key => $item) {
            if (in_array($item['type'], array('primary_key', 'mobilephone', '', 'safe_string', 'textarea', 'textarea_editor')) and $item['dbtype'] != 'notable') {
                $concatenate_columns[] = '`' .DB_PREFIX.'_'. $model_name . '`.'.'`' . $key . '`';
            }
        }
        if (count($concatenate_columns) > 0) {
            $value = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
            $value = str_replace(array('(', ')', '*', ',', '`'), '', $value);

            $columns = implode(',', $concatenate_columns);
            return "( concat({$columns}) like '%{$value}%' ) ";
        }
        return false;
    }

    private function compile_concatenate_condition_left_join($model_name, $data_model, $value) {
        $left_joins = array();
        $value = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
        $value = str_replace(array('(', ')', '*', ',', '`'), '', $value);

        foreach ($data_model as $key => $item) {
            if ( $item['dbtype'] != 'notable' ) {
                if (in_array($item['type'], array('select_by_query'))) {
                    $left_joins['tables'][] = ' LEFT JOIN `'.DB_PREFIX.'_'.$item['primary_key_table'].'` on (`'.DB_PREFIX.'_'.$item['primary_key_table'].'`.`'.$item['primary_key_name'].'`=`' .DB_PREFIX.'_'. $model_name . '`.`'.$item['name'].'`) ';
                    $left_joins['where'][] = ' ( `'.DB_PREFIX.'_'.$item['primary_key_table'].'`.`'.$item['value_name'].'` like \'%'.$value.'%\' ) ';
                } elseif ($item['type'] == 'select_box_structure') {
                    $left_joins['tables'][] = ' LEFT JOIN `'.DB_PREFIX.'_topic` on (`'.DB_PREFIX.'_topic`.`id`=`' .DB_PREFIX.'_'. $model_name . '`.`topic_id`) ';
                    $left_joins['where'][] = ' ( `'.DB_PREFIX.'_topic`.`name` like \'%'.$value.'%\' ) ';
                }
            }
        }
        return $left_joins;
    }


    private function get_grid_action_code($model_name, $user_id) {
        $action = $model_name . '_user_' . $user_id;

        return $action;
    }

    public function _format_grid() {
        $model_name = $this->request->get('model_name');
        $grid_items = $this->request->get('grid_items');
        $per_page = $this->request->get('per_page');
        $user_id = $this->get_my_user_id();

        $action = $this->get_grid_action_code($model_name, $user_id);

        $DBC = DBC::getInstance();
        if (count($grid_items) > 0) {
            $query = 'INSERT INTO ' . DB_PREFIX . '_table_grids (`action_code`, `grid_fields`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `grid_fields`=?';
            $stmt = $DBC->query($query, array($action, json_encode($grid_items), json_encode($grid_items)));
        } else {
            $query = 'DELETE FROM ' . DB_PREFIX . '_table_grids WHERE `action_code`=?';
            $stmt = $DBC->query($query, array($action));
        }
        if (!$stmt) {
            return $this->request_failed('update format_grid failed');
        }
        $response = new API_Response('success', 'true');
        return $this->json_string($response->get());
    }

    private function indexing_columns($columns) {
        foreach ($columns as $idx => $item) {
            $ra['index'][$item['name']] = $idx;
            $ra['default_columns_list'][] = $item['name'];
        }
        return $ra;
    }

    private function indexing_rows($rows, $primary_key) {
        foreach ($rows as $idx => $item) {
            $ra['index'][$item[$primary_key]['value']] = $idx;
        }
        return $ra;
    }

    public function _get_max() {
        $model_name = $this->request->get('model_name');
        $columnName = $this->request->get('columnName');
        $DBC = DBC::getInstance();
        $query = "select max($columnName) as maximum from " . DB_PREFIX . "_$model_name";
        $stmt = $DBC->query($query, array());
        if (!$stmt) {
            return $this->request_failed('model not defined');
        }
        $ar = $DBC->fetch($stmt);

        $response = new API_Response('success', $ar['maximum']);
        return $this->json_string($response->get());
    }

    private function compile_composed_query($table_name, $model, $column_name, $input_params) {
        $compose_columns = explode(',', $model[$column_name]['parameters']['columns']);
        $query_part = array();
        $query = false;
        //$this->writeArrayLog($compose_columns);
        //$this->writeArrayLog($input_params);
        $condition_glue = ' OR ';
        if ( is_array($compose_columns) and count($compose_columns) > 0 ) {
            foreach ( $compose_columns as $item ) {
                //$this->writeLog($item);
                if (isset($model[$item]['parameters']['slider'])) {
                    $query_part[] = '`'.DB_PREFIX.'_'.$table_name.'`.`'.$item.'` >= ' . (int) $input_params[$item]['min'];
                    $query_part[] = '`'.DB_PREFIX.'_'.$table_name.'`.`'.$item.'` <= ' . (int) $input_params[$item]['max'];
                    $condition_glue = ' AND ';

                } elseif ( isset($input_params[$item]) and is_array($input_params[$item]) and count($input_params[$item]) > 0 ) {
                    $query_part[] = "`".DB_PREFIX."_{$table_name}`.`$item` in (".implode(',', $input_params[$item]).")";
                }
            }

        }
        //$this->writeArrayLog($query_part);

        if ( count($query_part) > 0 ) {
            $query = "(".implode($condition_glue, $query_part).")";
        }
        //$this->writeLog($query);

        return $query;
    }

    public function _get_contact() {
        $model_name = 'contact';
        $model_object = $this->init_custom_model_object($model_name);
        $model_object->data_model[$model_object->table_name]['client_id']['type'] = 'select_by_query';

        return $this->_load_data($model_object);
    }

    public function _get_today_count () {
        $model_name = $this->request->get('model_name');
        $total = 0;
        $query = "select count(id) as _cnt from re_data where date_added > '".date('Y-m-d')."'";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $total = $ar['_cnt'];
        } else {
            $total = 0;
        }
        $response = new API_Response('success', $total);
        return $this->json_string($response->get());
    }

}
