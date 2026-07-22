<?php

use system\lib\model\compose_functions;
use system\lib\system\cache\RedisCache;


/**
 * Data model
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
// Model trait files
require_once __DIR__ . '/traits/ModelDefinitionTrait.php';
require_once __DIR__ . '/traits/DataInitTrait.php';
require_once __DIR__ . '/traits/CrudQueryTrait.php';
require_once __DIR__ . '/traits/DataValidationTrait.php';
require_once __DIR__ . '/traits/ValueLookupTrait.php';

class Data_Model extends SiteBill
{
    use \system\lib\model\traits\lifecycle\AfterRequestInitTrait;

    static $cache;

    /**
     * @var Permission
     */
    private $permission;
    /**
     * @var compose_functions
     */
    private $compose_functions;
    private $table_name;
    // Traits extracted from Data_Model
    use ModelDefinitionTrait,
        DataInitTrait,
        CrudQueryTrait,
        DataValidationTrait,
        ValueLookupTrait;


    /**
     * Construct
     */
    function __construct()
    {
        parent::__construct();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php');
        $this->permission = new Permission();
        $this->compose_functions = new compose_functions();
    }

    public function convertDistMeashures($val, $from, $to = 'm')
    {
        $def_m = 'm';
        $convars = array(
            'm' => array('km' => 0.001, 'm' => 1, 'mil' => 0.000621371, 'yar' => 1.09361, 'ft' => 3.28084, 'smil' => 0.000539957)
        );
        if ($from == $to) {
            return $val;
        } elseif ($from == $def_m) {
            if (isset($convars[$def_m][$to])) {
                return $convars[$def_m][$to] * $val;
            }
        } else {
            $from_k = 0;
            $to_k = 0;
            foreach ($convars[$def_m] as $k => $v) {
                if ($k == $from) {
                    $from_k = $v;
                }
                if ($k == $to) {
                    $to_k = $v;
                }
            }
            if ($from_k != 0 && $to_k != 0) {
                return $to_k * $val / $from_k;
            }
        }
        return $val;
    }

    public function convertAreaMeashures($val, $from, $to = 'sqm')
    {
        $def_m = 'sqm';
        $convars = array(
            'sqm' => array('ha' => 0.0001, 'sqm' => 1, 'ar' => 0.01, 'sqf' => 10.7639, 'sqy' => 1.19599, 'acr' => 0.000247105)
        );
        if ($from == $to) {
            return $val;
        } elseif ($from == $def_m) {
            if (isset($convars[$def_m][$to])) {
                return $convars[$def_m][$to] * $val;
            }
        } else {
            $from_k = 0;
            $to_k = 0;
            foreach ($convars[$def_m] as $k => $v) {
                if ($k == $from) {
                    $from_k = $v;
                }
                if ($k == $to) {
                    $to_k = $v;
                }
            }
            if ($from_k != 0 && $to_k != 0) {
                return $to_k * $val / $from_k;
            }
        }
        return $val;
    }

    /*
    function getObject($model, $conditions){
        require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
        $ATH = new Admin_Table_Helper();
        $model_array = $ATH->load_model($model, true);
        if(is_null($model_array)){
            return null;
        }

        $select_conditions = array();
        $select_values = array();

        foreach ($model_array as $modelitem_name => $model_item) {
            if(isset($conditions[$modelitem_name])){
                if(is_array($conditions[$modelitem_name])){
                    $select_conditions[] = '`'.$modelitem_name.'` IN ('.implode(',', array_fill(0, count($conditions[$modelitem_name]), '?')).')';
                    $select_values = array_merge($select_values, $conditions[$modelitem_name]);
                }else{
                    $select_conditions[] = '`'.$modelitem_name.'` IN (?)';
                    $select_values[] = $conditions[$modelitem_name];
                }
            }
        }

        return 1;
    }
    */

    function can_edit($model, $key)
    {
        if ($this->get_table_name() == 'data' && $this->getConfigValue('apps.realty.data.disable_edit')) {
            return false;
        }
        return true;
    }

    function get_crud_permissions($model, $key)
    {
        $current_user_id = $this->getSessionUserId();
        $component_name = (isset($model[$key]['table_name']) ? $model[$key]['table_name'] : '');
        $crud = array(
            'C' => $this->permission->get_access($current_user_id, $component_name, 'create'),
            'R' => true, // пока всем можно читать
            'U' => $this->permission->get_access($current_user_id, $component_name, 'update'),
            'D' => $this->permission->get_access($current_user_id, $component_name, 'delete'),
        );

        if (isset($model['user_id']) && $current_user_id == $model['user_id']['value']) {
            $crud = array(
                'C' => true,
                'R' => true,
                'U' => $this->can_edit($model, $key),
                'D' => true,
            );
        }

        if ($this->permission->is_admin($current_user_id)) {
            $crud = array(
                'C' => true,
                'R' => true,
                'U' => true,
                'D' => true,
            );
        }

        return $crud;
    }


    function filter_danger_names($function_name)
    {
        $danger_functions = array('exec', 'system', 'passthru', 'pcntl_exec', 'popen proc_open', 'shell_exec');
        if (in_array($function_name, $danger_functions)) {
            return true;
        }
        return false;
    }

    function exec_function($function_name, $model, $key)
    {
        if ($this->filter_danger_names($function_name)) {
            return 'function exec failed - banned name ' . $function_name;
        }
        return $function_name($model, $key);
    }


    function set_table_name($table_name)
    {
        $this->table_name = $table_name;
    }

    function get_table_name()
    {
        return $this->table_name;
    }


    function try_get_model_from_db($table_name, $exist_model)
    {
        $form_data = array();
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($table_name);


            if (empty($form_data[$table_name])) {
                $form_data = array();
                $form_data = $exist_model;
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, $table_name);
                $form_data = array();
                $form_data = $ATH->load_model($table_name);
            }

            $form_data = $ATH->add_ajax($form_data);
        } else {
            $form_data = $exist_model;
        }
        return $form_data;
    }

    /*function _get_big_city_kvartira_model($ajax = false)
    {
        $form_data = array();

        $form_data['data']['id']['name'] = 'id';
        $form_data['data']['id']['title'] = 'Идентификатор';
        $form_data['data']['id']['value'] = 0;
        $form_data['data']['id']['length'] = 40;
        $form_data['data']['id']['type'] = 'primary_key';
        $form_data['data']['id']['required'] = 'off';
        $form_data['data']['id']['unique'] = 'off';

        $form_data['data']['user_id']['name'] = 'user_id';
        $form_data['data']['user_id']['title'] = 'Идентификатор пользователя';
        $form_data['data']['user_id']['value'] = 0;
        $form_data['data']['user_id']['length'] = 40;
        $form_data['data']['user_id']['type'] = 'select_by_query';
        $form_data['data']['user_id']['query'] = 'select * from ' . DB_PREFIX . '_user order by login';
        $form_data['data']['user_id']['value_name'] = 'login';
        $form_data['data']['user_id']['primary_key_name'] = 'user_id';
        $form_data['data']['user_id']['primary_key_table'] = 'user';
        $form_data['data']['user_id']['title_default'] = 'выбрать пользователя';
        $form_data['data']['user_id']['value_default'] = 0;
        $form_data['data']['user_id']['required'] = 'off';
        $form_data['data']['user_id']['unique'] = 'off';

        $form_data['data']['date_added']['name'] = 'date_added';
        $form_data['data']['date_added']['title'] = 'Дата подачи';
        $form_data['data']['date_added']['value'] = '';
        $form_data['data']['date_added']['length'] = 40;
        $form_data['data']['date_added']['type'] = 'hidden';
        $form_data['data']['date_added']['required'] = 'off';
        $form_data['data']['date_added']['unique'] = 'off';

        $form_data['data']['active']['name'] = 'active';
        $form_data['data']['active']['title'] = 'Публиковать на сайте';
        $form_data['data']['active']['value'] = 0;
        $form_data['data']['active']['length'] = 40;
        $form_data['data']['active']['type'] = 'checkbox';
        $form_data['data']['active']['required'] = 'off';
        $form_data['data']['active']['unique'] = 'off';

        $form_data['data']['hot']['name'] = 'hot';
        $form_data['data']['hot']['title'] = 'Спецразмещение';
        $form_data['data']['hot']['value'] = 0;
        $form_data['data']['hot']['length'] = 40;
        $form_data['data']['hot']['type'] = 'checkbox';
        $form_data['data']['hot']['required'] = 'off';
        $form_data['data']['hot']['unique'] = 'off';

        $form_data['data']['optype']['name'] = 'optype';
        $form_data['data']['optype']['title'] = 'Тип операции';
        $form_data['data']['optype']['value'] = 0;
        $form_data['data']['optype']['length'] = 40;
        $form_data['data']['optype']['type'] = 'select_box';
        $form_data['data']['optype']['select_data'] = array('0' => 'сдам', '1' => 'продам');
        $form_data['data']['optype']['required'] = 'off';
        $form_data['data']['optype']['unique'] = 'off';

        $form_data['data']['topic_id']['name'] = 'topic_id';
        $form_data['data']['topic_id']['title'] = 'Категория';
        $form_data['data']['topic_id']['value_string'] = '';
        $form_data['data']['topic_id']['value'] = 0;
        $form_data['data']['topic_id']['length'] = 40;
        $form_data['data']['topic_id']['type'] = 'select_box_structure';
        $form_data['data']['topic_id']['required'] = 'on';
        $form_data['data']['topic_id']['unique'] = 'off';

        $form_data['data']['metro_id']['name'] = 'metro_id';
        $form_data['data']['metro_id']['primary_key_name'] = 'metro_id';
        $form_data['data']['metro_id']['primary_key_table'] = 'metro';
        $form_data['data']['metro_id']['title'] = 'Метро';
        $form_data['data']['metro_id']['value_string'] = '';
        $form_data['data']['metro_id']['value'] = 0;
        $form_data['data']['metro_id']['length'] = 40;
        $form_data['data']['metro_id']['type'] = 'select_by_query';
        $form_data['data']['metro_id']['query'] = 'select * from ' . DB_PREFIX . '_metro order by name';
        $form_data['data']['metro_id']['value_name'] = 'name';
        $form_data['data']['metro_id']['title_default'] = 'выбрать метро';
        $form_data['data']['metro_id']['value_default'] = 0;
        $form_data['data']['metro_id']['required'] = 'off';
        $form_data['data']['metro_id']['unique'] = 'off';

        $form_data['data']['metro_time_onfoot']['name'] = 'metro_time_onfoot';
        $form_data['data']['metro_time_onfoot']['title'] = 'Минут до метро пешком';
        $form_data['data']['metro_time_onfoot']['value'] = '';
        $form_data['data']['metro_time_onfoot']['length'] = 40;
        $form_data['data']['metro_time_onfoot']['type'] = 'safe_string';
        $form_data['data']['metro_time_onfoot']['required'] = 'off';
        $form_data['data']['metro_time_onfoot']['unique'] = 'off';

        $form_data['data']['metro_time_oncar']['name'] = 'metro_time_oncar';
        $form_data['data']['metro_time_oncar']['title'] = 'Минут до метро транспортом';
        $form_data['data']['metro_time_oncar']['value'] = '';
        $form_data['data']['metro_time_oncar']['length'] = 40;
        $form_data['data']['metro_time_oncar']['type'] = 'safe_string';
        $form_data['data']['metro_time_oncar']['required'] = 'off';
        $form_data['data']['metro_time_oncar']['unique'] = 'off';

        $form_data['data']['city_id']['name'] = 'city_id';
        $form_data['data']['city_id']['primary_key_name'] = 'city_id';
        $form_data['data']['city_id']['primary_key_table'] = 'city';
        $form_data['data']['city_id']['title'] = Multilanguage::_('L_CITY');
        $form_data['data']['city_id']['value_string'] = '';
        $form_data['data']['city_id']['value'] = 0;
        $form_data['data']['city_id']['length'] = 40;
        $form_data['data']['city_id']['type'] = 'select_by_query';
        $form_data['data']['city_id']['query'] = 'select * from ' . DB_PREFIX . '_city order by name';
        $form_data['data']['city_id']['value_name'] = 'name';
        $form_data['data']['city_id']['title_default'] = Multilanguage::_('L_CHOOSE_CITY');
        $form_data['data']['city_id']['value_default'] = 0;
        $form_data['data']['city_id']['required'] = 'off';
        $form_data['data']['city_id']['unique'] = 'off';

        $form_data['data']['street_id']['name'] = 'street_id';
        $form_data['data']['street_id']['primary_key_name'] = 'street_id';
        $form_data['data']['street_id']['primary_key_table'] = 'street';
        $form_data['data']['street_id']['title'] = Multilanguage::_('L_STREET');
        $form_data['data']['street_id']['value_string'] = '';
        $form_data['data']['street_id']['value'] = 0;
        $form_data['data']['street_id']['length'] = 40;
        $form_data['data']['street_id']['type'] = 'select_by_query';
        $form_data['data']['street_id']['query'] = 'select * from ' . DB_PREFIX . '_street order by name';
        $form_data['data']['street_id']['value_name'] = 'name';
        $form_data['data']['street_id']['title_default'] = Multilanguage::_('L_CHOOSE_STREET');
        $form_data['data']['street_id']['value_default'] = 0;
        $form_data['data']['street_id']['required'] = 'off';
        $form_data['data']['street_id']['unique'] = 'off';

        if ($this->getConfigValue('user_add_street_enable')) {
            $form_data['data']['new_street']['name'] = 'new_street';
            $form_data['data']['new_street']['title'] = 'Новая улица';
            $form_data['data']['new_street']['value'] = '';
            $form_data['data']['new_street']['length'] = 40;
            $form_data['data']['new_street']['type'] = 'auto_add_value';
            $form_data['data']['new_street']['dbtype'] = 'notable';
            $form_data['data']['new_street']['value_table'] = 'street';
            $form_data['data']['new_street']['value_primary_key'] = 'street_id';
            $form_data['data']['new_street']['value_field'] = 'name';
            $form_data['data']['new_street']['assign_to'] = 'street_id';
            $form_data['data']['new_street']['required'] = 'off';
            $form_data['data']['new_street']['unique'] = 'off';
        }

        $form_data['data']['number']['name'] = 'number';
        $form_data['data']['number']['title'] = 'Номер дома';
        $form_data['data']['number']['value'] = '';
        $form_data['data']['number']['length'] = 40;
        $form_data['data']['number']['type'] = 'safe_string';
        $form_data['data']['number']['required'] = 'off';
        $form_data['data']['number']['unique'] = 'off';

        $form_data['data']['housing_number']['name'] = 'housing_number';
        $form_data['data']['housing_number']['title'] = 'Номер корпуса';
        $form_data['data']['housing_number']['value'] = '';
        $form_data['data']['housing_number']['length'] = 40;
        $form_data['data']['housing_number']['type'] = 'safe_string';
        $form_data['data']['housing_number']['required'] = 'off';
        $form_data['data']['housing_number']['unique'] = 'off';

        $form_data['data']['price']['name'] = 'price';
        $form_data['data']['price']['title'] = 'Цена';
        $form_data['data']['price']['value'] = '';
        $form_data['data']['price']['length'] = 40;
        $form_data['data']['price']['type'] = 'price';
        $form_data['data']['price']['required'] = 'off';
        $form_data['data']['price']['unique'] = 'off';

        if ($this->getConfigValue('currency_enable')) {
            $form_data['data']['currency_id']['name'] = 'currency_id';
            $form_data['data']['currency_id']['primary_key_name'] = 'currency_id';
            $form_data['data']['currency_id']['primary_key_table'] = 'currency';
            $form_data['data']['currency_id']['title'] = 'Валюта';
            $form_data['data']['currency_id']['value_string'] = '';
            $form_data['data']['currency_id']['value'] = 0;
            $form_data['data']['currency_id']['length'] = 40;
            $form_data['data']['currency_id']['type'] = 'select_by_query';
            $form_data['data']['currency_id']['query'] = 'select * from ' . DB_PREFIX . '_currency WHERE is_active=1 ORDER BY sort_order ASC, code ASC, currency_id ASC';
            $form_data['data']['currency_id']['value_name'] = 'name';
            $form_data['data']['currency_id']['title_default'] = '';
            $form_data['data']['currency_id']['value_default'] = 0;
            $form_data['data']['currency_id']['required'] = 'off';
            $form_data['data']['currency_id']['unique'] = 'off';
        }

        $form_data['data']['rent_term']['name'] = 'rent_term';
        $form_data['data']['rent_term']['title'] = 'Срок аренды';
        $form_data['data']['rent_term']['value'] = 0;
        $form_data['data']['rent_term']['length'] = 40;
        $form_data['data']['rent_term']['type'] = 'select_box';
        $form_data['data']['rent_term']['select_data'] = array('0' => 'длинный', '1' => 'короткий');
        $form_data['data']['rent_term']['required'] = 'off';
        $form_data['data']['rent_term']['unique'] = 'off';

        $form_data['data']['room_count']['name'] = 'room_count';
        $form_data['data']['room_count']['title'] = 'Кол.во комнат';
        $form_data['data']['room_count']['value'] = '';
        $form_data['data']['room_count']['length'] = 40;
        $form_data['data']['room_count']['type'] = 'safe_string';
        $form_data['data']['room_count']['required'] = 'off';
        $form_data['data']['room_count']['unique'] = 'off';

        $form_data['data']['floor']['name'] = 'floor';
        $form_data['data']['floor']['title'] = 'Этаж';
        $form_data['data']['floor']['value'] = '';
        $form_data['data']['floor']['length'] = 40;
        $form_data['data']['floor']['type'] = 'safe_string';
        $form_data['data']['floor']['required'] = 'off';
        $form_data['data']['floor']['unique'] = 'off';

        $form_data['data']['floor_count']['name'] = 'floor_count';
        $form_data['data']['floor_count']['title'] = 'Этажность';
        $form_data['data']['floor_count']['value'] = '';
        $form_data['data']['floor_count']['length'] = 40;
        $form_data['data']['floor_count']['type'] = 'safe_string';
        $form_data['data']['floor_count']['required'] = 'off';
        $form_data['data']['floor_count']['unique'] = 'off';

        $form_data['data']['refrigerator']['name'] = 'refrigerator';
        $form_data['data']['refrigerator']['title'] = 'Холодильник';
        $form_data['data']['refrigerator']['value'] = 0;
        $form_data['data']['refrigerator']['length'] = 40;
        $form_data['data']['refrigerator']['type'] = 'select_box';
        $form_data['data']['refrigerator']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['refrigerator']['required'] = 'off';
        $form_data['data']['refrigerator']['unique'] = 'off';

        $form_data['data']['tvset']['name'] = 'tvset';
        $form_data['data']['tvset']['title'] = 'Телевизор';
        $form_data['data']['tvset']['value'] = 0;
        $form_data['data']['tvset']['length'] = 40;
        $form_data['data']['tvset']['type'] = 'select_box';
        $form_data['data']['tvset']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['tvset']['required'] = 'off';
        $form_data['data']['tvset']['unique'] = 'off';

        $form_data['data']['washer']['name'] = 'washer';
        $form_data['data']['washer']['title'] = 'Cтиральная машина';
        $form_data['data']['washer']['value'] = 0;
        $form_data['data']['washer']['length'] = 40;
        $form_data['data']['washer']['type'] = 'select_box';
        $form_data['data']['washer']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['washer']['required'] = 'off';
        $form_data['data']['washer']['unique'] = 'off';

        $form_data['data']['furniture_kitchen']['name'] = 'furniture_kitchen';
        $form_data['data']['furniture_kitchen']['title'] = 'Мебель на кухне';
        $form_data['data']['furniture_kitchen']['value'] = 0;
        $form_data['data']['furniture_kitchen']['length'] = 40;
        $form_data['data']['furniture_kitchen']['type'] = 'select_box';
        $form_data['data']['furniture_kitchen']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['furniture_kitchen']['required'] = 'off';
        $form_data['data']['furniture_kitchen']['unique'] = 'off';

        $form_data['data']['furniture_room']['name'] = 'furniture_room';
        $form_data['data']['furniture_room']['title'] = 'Мебель в комнате';
        $form_data['data']['furniture_room']['value'] = 0;
        $form_data['data']['furniture_room']['length'] = 40;
        $form_data['data']['furniture_room']['type'] = 'select_box';
        $form_data['data']['furniture_room']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['furniture_room']['required'] = 'off';
        $form_data['data']['furniture_room']['unique'] = 'off';

        $form_data['data']['balcony']['name'] = 'balcony';
        $form_data['data']['balcony']['title'] = 'Балкон';
        $form_data['data']['balcony']['value'] = 0;
        $form_data['data']['balcony']['length'] = 40;
        $form_data['data']['balcony']['type'] = 'select_box';
        $form_data['data']['balcony']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['balcony']['required'] = 'off';
        $form_data['data']['balcony']['unique'] = 'off';

        $form_data['data']['is_telephone']['name'] = 'is_telephone';
        $form_data['data']['is_telephone']['title'] = 'Телефон';
        $form_data['data']['is_telephone']['value'] = 0;
        $form_data['data']['is_telephone']['length'] = 40;
        $form_data['data']['is_telephone']['type'] = 'select_box';
        $form_data['data']['is_telephone']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['is_telephone']['required'] = 'off';
        $form_data['data']['is_telephone']['unique'] = 'off';

        $form_data['data']['plate']['name'] = 'plate';
        $form_data['data']['plate']['title'] = 'Плита';
        $form_data['data']['plate']['value'] = 0;
        $form_data['data']['plate']['length'] = 40;
        $form_data['data']['plate']['type'] = 'select_box';
        $form_data['data']['plate']['select_data'] = array('0' => 'не указано', '1' => 'газ', '2' => 'электро');
        $form_data['data']['plate']['required'] = 'off';
        $form_data['data']['plate']['unique'] = 'off';

        $form_data['data']['square_all']['name'] = 'square_all';
        $form_data['data']['square_all']['title'] = 'Площадь общая';
        $form_data['data']['square_all']['value'] = '';
        $form_data['data']['square_all']['length'] = 40;
        $form_data['data']['square_all']['type'] = 'safe_string';
        $form_data['data']['square_all']['required'] = 'off';
        $form_data['data']['square_all']['unique'] = 'off';

        $form_data['data']['square_live']['name'] = 'square_live';
        $form_data['data']['square_live']['title'] = 'Площадь жилая';
        $form_data['data']['square_live']['value'] = '';
        $form_data['data']['square_live']['length'] = 40;
        $form_data['data']['square_live']['type'] = 'safe_string';
        $form_data['data']['square_live']['required'] = 'off';
        $form_data['data']['square_live']['unique'] = 'off';

        $form_data['data']['square_kitchen']['name'] = 'square_kitchen';
        $form_data['data']['square_kitchen']['title'] = 'Площадь кухни';
        $form_data['data']['square_kitchen']['value'] = '';
        $form_data['data']['square_kitchen']['length'] = 40;
        $form_data['data']['square_kitchen']['type'] = 'safe_string';
        $form_data['data']['square_kitchen']['required'] = 'off';
        $form_data['data']['square_kitchen']['unique'] = 'off';


        $form_data['data']['contact_phone_1']['name'] = 'contact_phone_1';
        $form_data['data']['contact_phone_1']['title'] = 'Телефон1';
        $form_data['data']['contact_phone_1']['value'] = '';
        $form_data['data']['contact_phone_1']['length'] = 40;
        $form_data['data']['contact_phone_1']['type'] = 'safe_string';
        $form_data['data']['contact_phone_1']['required'] = 'off';
        $form_data['data']['contact_phone_1']['unique'] = 'off';

        $form_data['data']['contact_phone_2']['name'] = 'contact_phone_2';
        $form_data['data']['contact_phone_2']['title'] = 'Телефон2';
        $form_data['data']['contact_phone_2']['value'] = '';
        $form_data['data']['contact_phone_2']['length'] = 40;
        $form_data['data']['contact_phone_2']['type'] = 'safe_string';
        $form_data['data']['contact_phone_2']['required'] = 'off';
        $form_data['data']['contact_phone_2']['unique'] = 'off';

        $form_data['data']['text']['name'] = 'text';
        $form_data['data']['text']['title'] = 'Описание';
        $form_data['data']['text']['value'] = '';
        $form_data['data']['text']['length'] = 40;
        $form_data['data']['text']['type'] = 'textarea';
        $form_data['data']['text']['required'] = 'off';
        $form_data['data']['text']['unique'] = 'off';
        $form_data['data']['text']['rows'] = '10';
        $form_data['data']['text']['cols'] = '40';

        $form_data['data']['renter_slavic']['name'] = 'renter_slavic';
        $form_data['data']['renter_slavic']['title'] = 'Cлавян';
        $form_data['data']['renter_slavic']['value'] = 0;
        $form_data['data']['renter_slavic']['type'] = 'checkbox';
        $form_data['data']['renter_slavic']['required'] = 'off';
        $form_data['data']['renter_slavic']['unique'] = 'off';
        $form_data['data']['renter_slavic']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_decent']['name'] = 'renter_decent';
        $form_data['data']['renter_decent']['title'] = 'Всех приличных';
        $form_data['data']['renter_decent']['value'] = 0;
        $form_data['data']['renter_decent']['type'] = 'checkbox';
        $form_data['data']['renter_decent']['required'] = 'off';
        $form_data['data']['renter_decent']['unique'] = 'off';
        $form_data['data']['renter_decent']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_rfcitisen']['name'] = 'renter_rfcitisen';
        $form_data['data']['renter_rfcitisen']['title'] = 'Граждан  РФ';
        $form_data['data']['renter_rfcitisen']['value'] = 0;
        $form_data['data']['renter_rfcitisen']['type'] = 'checkbox';
        $form_data['data']['renter_rfcitisen']['required'] = 'off';
        $form_data['data']['renter_rfcitisen']['unique'] = 'off';
        $form_data['data']['renter_rfcitisen']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_family']['name'] = 'renter_family';
        $form_data['data']['renter_family']['title'] = 'Сем. пару';
        $form_data['data']['renter_family']['value'] = 0;
        $form_data['data']['renter_family']['type'] = 'checkbox';
        $form_data['data']['renter_family']['required'] = 'off';
        $form_data['data']['renter_family']['unique'] = 'off';
        $form_data['data']['renter_family']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_onegirl']['name'] = 'renter_onegirl';
        $form_data['data']['renter_onegirl']['title'] = 'Одну девушку';
        $form_data['data']['renter_onegirl']['value'] = 0;
        $form_data['data']['renter_onegirl']['type'] = 'checkbox';
        $form_data['data']['renter_onegirl']['required'] = 'off';
        $form_data['data']['renter_onegirl']['unique'] = 'off';
        $form_data['data']['renter_onegirl']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_twogirl']['name'] = 'renter_twogirl';
        $form_data['data']['renter_twogirl']['title'] = 'Двух девушек';
        $form_data['data']['renter_twogirl']['value'] = 0;
        $form_data['data']['renter_twogirl']['type'] = 'checkbox';
        $form_data['data']['renter_twogirl']['required'] = 'off';
        $form_data['data']['renter_twogirl']['unique'] = 'off';
        $form_data['data']['renter_twogirl']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_oneman']['name'] = 'renter_oneman';
        $form_data['data']['renter_oneman']['title'] = 'Одного мужчину';
        $form_data['data']['renter_oneman']['value'] = 0;
        $form_data['data']['renter_oneman']['type'] = 'checkbox';
        $form_data['data']['renter_oneman']['required'] = 'off';
        $form_data['data']['renter_oneman']['unique'] = 'off';
        $form_data['data']['renter_oneman']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_twomen']['name'] = 'renter_twomen';
        $form_data['data']['renter_twomen']['title'] = 'Двух мужчин';
        $form_data['data']['renter_twomen']['value'] = 0;
        $form_data['data']['renter_twomen']['type'] = 'checkbox';
        $form_data['data']['renter_twomen']['required'] = 'off';
        $form_data['data']['renter_twomen']['unique'] = 'off';
        $form_data['data']['renter_twomen']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_with_children']['name'] = 'renter_with_children';
        $form_data['data']['renter_with_children']['title'] = 'Можно с детьми';
        $form_data['data']['renter_with_children']['value'] = 0;
        $form_data['data']['renter_with_children']['type'] = 'checkbox';
        $form_data['data']['renter_with_children']['required'] = 'off';
        $form_data['data']['renter_with_children']['unique'] = 'off';
        $form_data['data']['renter_with_children']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_with_animals']['name'] = 'renter_with_animals';
        $form_data['data']['renter_with_animals']['title'] = 'Можно с животными';
        $form_data['data']['renter_with_animals']['value'] = 0;
        $form_data['data']['renter_with_animals']['type'] = 'checkbox';
        $form_data['data']['renter_with_animals']['required'] = 'off';
        $form_data['data']['renter_with_animals']['unique'] = 'off';
        $form_data['data']['renter_with_animals']['tab'] = 'Требования к соискателю';

        $form_data['data']['renter_another']['name'] = 'renter_another';
        $form_data['data']['renter_another']['title'] = 'Другие требования';
        $form_data['data']['renter_another']['value'] = 0;
        $form_data['data']['renter_another']['type'] = 'checkbox';
        $form_data['data']['renter_another']['required'] = 'off';
        $form_data['data']['renter_another']['unique'] = 'off';
        $form_data['data']['renter_another']['tab'] = 'Требования к соискателю';


        $form_data['data']['image']['name'] = 'image';
        $form_data['data']['image']['table_name'] = 'data';
        $form_data['data']['image']['primary_key'] = 'id';
        $form_data['data']['image']['primary_key_value'] = 0;
        $form_data['data']['image']['action'] = 'data';
        $form_data['data']['image']['title'] = 'Фотографии ';
        $form_data['data']['image']['value'] = '';
        $form_data['data']['image']['length'] = 40;
        $form_data['data']['image']['type'] = 'uploadify_image';
        $form_data['data']['image']['required'] = 'off';
        $form_data['data']['image']['unique'] = 'off';

        if ($this->getConfigValue('apps.realtypro.youtube')) {
            $form_data['data']['youtube']['name'] = 'youtube';
            $form_data['data']['youtube']['title'] = 'Видео';
            $form_data['data']['youtube']['value'] = '';
            $form_data['data']['youtube']['length'] = 40;
            $form_data['data']['youtube']['type'] = 'youtube';
            $form_data['data']['youtube']['required'] = 'off';
            $form_data['data']['youtube']['unique'] = 'off';
        }

        $form_data['data']['view_count']['name'] = 'view_count';
        $form_data['data']['view_count']['title'] = 'Количество просмотров';
        $form_data['data']['view_count']['value'] = '';
        $form_data['data']['view_count']['length'] = 40;
        $form_data['data']['view_count']['type'] = 'hidden';
        $form_data['data']['view_count']['required'] = 'off';
        $form_data['data']['view_count']['unique'] = 'off';

        $form_data['data']['whoyuaare']['name'] = 'whoyuaare';
        $form_data['data']['whoyuaare']['title'] = 'Кто вы';
        $form_data['data']['whoyuaare']['value'] = 0;
        $form_data['data']['whoyuaare']['length'] = 40;
        $form_data['data']['whoyuaare']['type'] = 'select_box';
        $form_data['data']['whoyuaare']['select_data'] = array('0' => 'не указано', '1' => 'собственник', '2' => 'агентство', '3' => 'частный риелтор');
        $form_data['data']['whoyuaare']['required'] = 'off';
        $form_data['data']['whoyuaare']['unique'] = 'off';

        $form_data['data']['fio']['name'] = 'fio';
        $form_data['data']['fio']['title'] = 'Ваше имя';
        $form_data['data']['fio']['value'] = '';
        $form_data['data']['fio']['length'] = 40;
        $form_data['data']['fio']['type'] = 'safe_string';
        $form_data['data']['fio']['required'] = 'on';
        $form_data['data']['fio']['unique'] = 'off';

        $form_data['data']['email']['name'] = 'email';
        $form_data['data']['email']['title'] = 'E-mail';
        $form_data['data']['email']['value'] = '';
        $form_data['data']['email']['length'] = 40;
        $form_data['data']['email']['type'] = 'email';
        $form_data['data']['email']['required'] = 'off';
        $form_data['data']['email']['unique'] = 'off';

        $form_data['data']['phone']['name'] = 'phone';
        $form_data['data']['phone']['title'] = 'Ваш телефон (мобильный)<br />Формат ввода <b>8**********</b>';
        $form_data['data']['phone']['value'] = '';
        $form_data['data']['phone']['length'] = 40;
        $form_data['data']['phone']['type'] = 'mobilephone';
        $form_data['data']['phone']['required'] = 'on';
        $form_data['data']['phone']['unique'] = 'off';

        $form_data['data']['agency_cooperation']['name'] = 'agency_cooperation';
        $form_data['data']['agency_cooperation']['title'] = 'Готовы ли вы сотрудничать с агентствами';
        $form_data['data']['agency_cooperation']['value'] = 1;
        $form_data['data']['agency_cooperation']['type'] = 'checkbox';
        $form_data['data']['agency_cooperation']['required'] = 'off';
        $form_data['data']['agency_cooperation']['unique'] = 'off';

        return $form_data;
    }*/

    /*function _get_big_city_kvartira_model2($ajax = false)
    {
        $form_data = array();

        $form_data['data']['id']['name'] = 'id';
        $form_data['data']['id']['title'] = 'Идентификатор';
        $form_data['data']['id']['value'] = 0;
        $form_data['data']['id']['length'] = 40;
        $form_data['data']['id']['type'] = 'primary_key';
        $form_data['data']['id']['required'] = 'off';
        $form_data['data']['id']['unique'] = 'off';

        $form_data['data']['user_id']['name'] = 'user_id';
        $form_data['data']['user_id']['title'] = 'Идентификатор пользователя';
        $form_data['data']['user_id']['value'] = 0;
        $form_data['data']['user_id']['length'] = 40;
        $form_data['data']['user_id']['type'] = 'select_by_query';
        $form_data['data']['user_id']['query'] = 'select * from ' . DB_PREFIX . '_user order by login';
        $form_data['data']['user_id']['value_name'] = 'login';
        $form_data['data']['user_id']['primary_key_name'] = 'user_id';
        $form_data['data']['user_id']['primary_key_table'] = 'user';
        $form_data['data']['user_id']['title_default'] = 'выбрать пользователя';
        $form_data['data']['user_id']['value_default'] = 0;
        $form_data['data']['user_id']['required'] = 'off';
        $form_data['data']['user_id']['unique'] = 'off';

        $form_data['data']['date_added']['name'] = 'date_added';
        $form_data['data']['date_added']['title'] = 'Дата подачи';
        $form_data['data']['date_added']['value'] = '';
        $form_data['data']['date_added']['length'] = 40;
        $form_data['data']['date_added']['type'] = 'hidden';
        $form_data['data']['date_added']['required'] = 'off';
        $form_data['data']['date_added']['unique'] = 'off';

        $form_data['data']['active']['name'] = 'active';
        $form_data['data']['active']['title'] = 'Публиковать на сайте';
        $form_data['data']['active']['value'] = 0;
        $form_data['data']['active']['length'] = 40;
        $form_data['data']['active']['type'] = 'checkbox';
        $form_data['data']['active']['required'] = 'off';
        $form_data['data']['active']['unique'] = 'off';

        $form_data['data']['hot']['name'] = 'hot';
        $form_data['data']['hot']['title'] = 'Спецразмещение';
        $form_data['data']['hot']['value'] = 0;
        $form_data['data']['hot']['length'] = 40;
        $form_data['data']['hot']['type'] = 'checkbox';
        $form_data['data']['hot']['required'] = 'off';
        $form_data['data']['hot']['unique'] = 'off';

        $form_data['data']['optype']['name'] = 'optype';
        $form_data['data']['optype']['title'] = 'Тип операции';
        $form_data['data']['optype']['value'] = 0;
        $form_data['data']['optype']['length'] = 40;
        $form_data['data']['optype']['type'] = 'select_box';
        $form_data['data']['optype']['select_data'] = array('0' => 'сдам', '1' => 'продам');
        $form_data['data']['optype']['required'] = 'off';
        $form_data['data']['optype']['unique'] = 'off';

        $form_data['data']['topic_id']['name'] = 'topic_id';
        $form_data['data']['topic_id']['title'] = 'Категория';
        $form_data['data']['topic_id']['value_string'] = '';
        $form_data['data']['topic_id']['value'] = 0;
        $form_data['data']['topic_id']['length'] = 40;
        $form_data['data']['topic_id']['type'] = 'select_box_structure';
        $form_data['data']['topic_id']['required'] = 'on';
        $form_data['data']['topic_id']['unique'] = 'off';

        $form_data['data']['metro_id']['name'] = 'metro_id';
        $form_data['data']['metro_id']['primary_key_name'] = 'metro_id';
        $form_data['data']['metro_id']['primary_key_table'] = 'metro';
        $form_data['data']['metro_id']['title'] = 'Метро';
        $form_data['data']['metro_id']['value_string'] = '';
        $form_data['data']['metro_id']['value'] = 0;
        $form_data['data']['metro_id']['length'] = 40;
        $form_data['data']['metro_id']['type'] = 'select_by_query';
        $form_data['data']['metro_id']['query'] = 'select * from ' . DB_PREFIX . '_metro order by name';
        $form_data['data']['metro_id']['value_name'] = 'name';
        $form_data['data']['metro_id']['title_default'] = 'выбрать метро';
        $form_data['data']['metro_id']['value_default'] = 0;
        $form_data['data']['metro_id']['required'] = 'off';
        $form_data['data']['metro_id']['unique'] = 'off';

        $form_data['data']['city_id']['name'] = 'city_id';
        $form_data['data']['city_id']['primary_key_name'] = 'city_id';
        $form_data['data']['city_id']['primary_key_table'] = 'city';
        $form_data['data']['city_id']['title'] = Multilanguage::_('L_CITY');
        $form_data['data']['city_id']['value_string'] = '';
        $form_data['data']['city_id']['value'] = 0;
        $form_data['data']['city_id']['length'] = 40;
        $form_data['data']['city_id']['type'] = 'select_by_query';
        $form_data['data']['city_id']['query'] = 'select * from ' . DB_PREFIX . '_city order by name';
        $form_data['data']['city_id']['value_name'] = 'name';
        $form_data['data']['city_id']['title_default'] = Multilanguage::_('L_CHOOSE_CITY');
        $form_data['data']['city_id']['value_default'] = 0;
        $form_data['data']['city_id']['required'] = 'off';
        $form_data['data']['city_id']['unique'] = 'off';

        $form_data['data']['street_id']['name'] = 'street_id';
        $form_data['data']['street_id']['primary_key_name'] = 'street_id';
        $form_data['data']['street_id']['primary_key_table'] = 'street';
        $form_data['data']['street_id']['title'] = Multilanguage::_('L_STREET');
        $form_data['data']['street_id']['value_string'] = '';
        $form_data['data']['street_id']['value'] = 0;
        $form_data['data']['street_id']['length'] = 40;
        $form_data['data']['street_id']['type'] = 'select_by_query';
        $form_data['data']['street_id']['query'] = 'select * from ' . DB_PREFIX . '_street order by name';
        $form_data['data']['street_id']['value_name'] = 'name';
        $form_data['data']['street_id']['title_default'] = Multilanguage::_('L_CHOOSE_STREET');
        $form_data['data']['street_id']['value_default'] = 0;
        $form_data['data']['street_id']['required'] = 'off';
        $form_data['data']['street_id']['unique'] = 'off';

        if ($this->getConfigValue('user_add_street_enable')) {
            $form_data['data']['new_street']['name'] = 'new_street';
            $form_data['data']['new_street']['title'] = 'Новая улица';
            $form_data['data']['new_street']['value'] = '';
            $form_data['data']['new_street']['length'] = 40;
            $form_data['data']['new_street']['type'] = 'auto_add_value';
            $form_data['data']['new_street']['dbtype'] = 'notable';
            $form_data['data']['new_street']['value_table'] = 'street';
            $form_data['data']['new_street']['value_primary_key'] = 'street_id';
            $form_data['data']['new_street']['value_field'] = 'name';
            $form_data['data']['new_street']['assign_to'] = 'street_id';
            $form_data['data']['new_street']['required'] = 'off';
            $form_data['data']['new_street']['unique'] = 'off';
        }

        $form_data['data']['number']['name'] = 'number';
        $form_data['data']['number']['title'] = 'Номер дома';
        $form_data['data']['number']['value'] = '';
        $form_data['data']['number']['length'] = 40;
        $form_data['data']['number']['type'] = 'safe_string';
        $form_data['data']['number']['required'] = 'off';
        $form_data['data']['number']['unique'] = 'off';

        $form_data['data']['price']['name'] = 'price';
        $form_data['data']['price']['title'] = 'Цена';
        $form_data['data']['price']['value'] = '';
        $form_data['data']['price']['length'] = 40;
        $form_data['data']['price']['type'] = 'price';
        $form_data['data']['price']['required'] = 'off';
        $form_data['data']['price']['unique'] = 'off';

        if ($this->getConfigValue('currency_enable')) {
            $form_data['data']['currency_id']['name'] = 'currency_id';
            $form_data['data']['currency_id']['primary_key_name'] = 'currency_id';
            $form_data['data']['currency_id']['primary_key_table'] = 'currency';
            $form_data['data']['currency_id']['title'] = 'Валюта';
            $form_data['data']['currency_id']['value_string'] = '';
            $form_data['data']['currency_id']['value'] = 0;
            $form_data['data']['currency_id']['length'] = 40;
            $form_data['data']['currency_id']['type'] = 'select_by_query';
            $form_data['data']['currency_id']['query'] = 'select * from ' . DB_PREFIX . '_currency WHERE is_active=1 ORDER BY sort_order ASC, code ASC, currency_id ASC';
            $form_data['data']['currency_id']['value_name'] = 'name';
            $form_data['data']['currency_id']['title_default'] = '';
            $form_data['data']['currency_id']['value_default'] = 0;
            $form_data['data']['currency_id']['required'] = 'off';
            $form_data['data']['currency_id']['unique'] = 'off';
        }

        $form_data['data']['aim']['name'] = 'aim';
        $form_data['data']['aim']['title'] = 'Цель';
        $form_data['data']['aim']['value'] = 0;
        $form_data['data']['aim']['length'] = 40;
        $form_data['data']['aim']['type'] = 'select_box';
        $form_data['data']['aim']['select_data'] = array('0' => 'не указанно', '1' => 'Инвестиция', '2' => 'Для отдыха', '3' => 'Для ПМЖ');
        $form_data['data']['aim']['required'] = 'off';
        $form_data['data']['aim']['unique'] = 'off';

        $form_data['data']['room_count']['name'] = 'room_count';
        $form_data['data']['room_count']['title'] = 'Кол.во комнат';
        $form_data['data']['room_count']['value'] = '';
        $form_data['data']['room_count']['length'] = 40;
        $form_data['data']['room_count']['type'] = 'safe_string';
        $form_data['data']['room_count']['required'] = 'off';
        $form_data['data']['room_count']['unique'] = 'off';

        $form_data['data']['floor']['name'] = 'floor';
        $form_data['data']['floor']['title'] = 'Этаж';
        $form_data['data']['floor']['value'] = '';
        $form_data['data']['floor']['length'] = 40;
        $form_data['data']['floor']['type'] = 'safe_string';
        $form_data['data']['floor']['required'] = 'off';
        $form_data['data']['floor']['unique'] = 'off';

        $form_data['data']['floor_count']['name'] = 'floor_count';
        $form_data['data']['floor_count']['title'] = 'Этажность';
        $form_data['data']['floor_count']['value'] = '';
        $form_data['data']['floor_count']['length'] = 40;
        $form_data['data']['floor_count']['type'] = 'safe_string';
        $form_data['data']['floor_count']['required'] = 'off';
        $form_data['data']['floor_count']['unique'] = 'off';

        $form_data['data']['refrigerator']['name'] = 'refrigerator';
        $form_data['data']['refrigerator']['title'] = 'Холодильник';
        $form_data['data']['refrigerator']['value'] = 0;
        $form_data['data']['refrigerator']['length'] = 40;
        $form_data['data']['refrigerator']['type'] = 'select_box';
        $form_data['data']['refrigerator']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['refrigerator']['required'] = 'off';
        $form_data['data']['refrigerator']['unique'] = 'off';

        $form_data['data']['tvset']['name'] = 'tvset';
        $form_data['data']['tvset']['title'] = 'Телевизор';
        $form_data['data']['tvset']['value'] = 0;
        $form_data['data']['tvset']['length'] = 40;
        $form_data['data']['tvset']['type'] = 'select_box';
        $form_data['data']['tvset']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['tvset']['required'] = 'off';
        $form_data['data']['tvset']['unique'] = 'off';

        $form_data['data']['washer']['name'] = 'washer';
        $form_data['data']['washer']['title'] = 'Cтиральная машина';
        $form_data['data']['washer']['value'] = 0;
        $form_data['data']['washer']['length'] = 40;
        $form_data['data']['washer']['type'] = 'select_box';
        $form_data['data']['washer']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['washer']['required'] = 'off';
        $form_data['data']['washer']['unique'] = 'off';

        $form_data['data']['furniture_kitchen']['name'] = 'furniture_kitchen';
        $form_data['data']['furniture_kitchen']['title'] = 'Мебель на кухне';
        $form_data['data']['furniture_kitchen']['value'] = 0;
        $form_data['data']['furniture_kitchen']['length'] = 40;
        $form_data['data']['furniture_kitchen']['type'] = 'select_box';
        $form_data['data']['furniture_kitchen']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['furniture_kitchen']['required'] = 'off';
        $form_data['data']['furniture_kitchen']['unique'] = 'off';

        $form_data['data']['furniture_room']['name'] = 'furniture_room';
        $form_data['data']['furniture_room']['title'] = 'Мебель в комнате';
        $form_data['data']['furniture_room']['value'] = 0;
        $form_data['data']['furniture_room']['length'] = 40;
        $form_data['data']['furniture_room']['type'] = 'select_box';
        $form_data['data']['furniture_room']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['furniture_room']['required'] = 'off';
        $form_data['data']['furniture_room']['unique'] = 'off';

        $form_data['data']['balcony']['name'] = 'balcony';
        $form_data['data']['balcony']['title'] = 'Балкон';
        $form_data['data']['balcony']['value'] = 0;
        $form_data['data']['balcony']['length'] = 40;
        $form_data['data']['balcony']['type'] = 'select_box';
        $form_data['data']['balcony']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['balcony']['required'] = 'off';
        $form_data['data']['balcony']['unique'] = 'off';

        $form_data['data']['is_telephone']['name'] = 'is_telephone';
        $form_data['data']['is_telephone']['title'] = 'Телефон';
        $form_data['data']['is_telephone']['value'] = 0;
        $form_data['data']['is_telephone']['length'] = 40;
        $form_data['data']['is_telephone']['type'] = 'select_box';
        $form_data['data']['is_telephone']['select_data'] = array('0' => 'не указано', '1' => 'есть', '2' => 'нет');
        $form_data['data']['is_telephone']['required'] = 'off';
        $form_data['data']['is_telephone']['unique'] = 'off';

        $form_data['data']['plate']['name'] = 'plate';
        $form_data['data']['plate']['title'] = 'Плита';
        $form_data['data']['plate']['value'] = 0;
        $form_data['data']['plate']['length'] = 40;
        $form_data['data']['plate']['type'] = 'select_box';
        $form_data['data']['plate']['select_data'] = array('0' => 'не указано', '1' => 'газ', '2' => 'электро');
        $form_data['data']['plate']['required'] = 'off';
        $form_data['data']['plate']['unique'] = 'off';

        $form_data['data']['square_all']['name'] = 'square_all';
        $form_data['data']['square_all']['title'] = 'Площадь общая';
        $form_data['data']['square_all']['value'] = '';
        $form_data['data']['square_all']['length'] = 40;
        $form_data['data']['square_all']['type'] = 'safe_string';
        $form_data['data']['square_all']['required'] = 'off';
        $form_data['data']['square_all']['unique'] = 'off';

        $form_data['data']['square_live']['name'] = 'square_live';
        $form_data['data']['square_live']['title'] = 'Площадь жилая';
        $form_data['data']['square_live']['value'] = '';
        $form_data['data']['square_live']['length'] = 40;
        $form_data['data']['square_live']['type'] = 'safe_string';
        $form_data['data']['square_live']['required'] = 'off';
        $form_data['data']['square_live']['unique'] = 'off';

        $form_data['data']['square_kitchen']['name'] = 'square_kitchen';
        $form_data['data']['square_kitchen']['title'] = 'Площадь кухни';
        $form_data['data']['square_kitchen']['value'] = '';
        $form_data['data']['square_kitchen']['length'] = 40;
        $form_data['data']['square_kitchen']['type'] = 'safe_string';
        $form_data['data']['square_kitchen']['required'] = 'off';
        $form_data['data']['square_kitchen']['unique'] = 'off';

        $form_data['data']['object_type']['name'] = 'object_type';
        $form_data['data']['object_type']['title'] = 'Тип объекта';
        $form_data['data']['object_type']['value'] = 0;
        $form_data['data']['object_type']['length'] = 40;
        $form_data['data']['object_type']['type'] = 'select_box';
        $form_data['data']['object_type']['select_data'] = array('0' => 'не указано', '1' => 'Апартаменты', '2' => 'Дом', '3' => 'Вилла');
        $form_data['data']['object_type']['required'] = 'off';
        $form_data['data']['object_type']['unique'] = 'off';

        $form_data['data']['object_state']['name'] = 'object_state';
        $form_data['data']['object_state']['title'] = 'Состояние';
        $form_data['data']['object_state']['value'] = 0;
        $form_data['data']['object_state']['length'] = 40;
        $form_data['data']['object_state']['type'] = 'select_box';
        $form_data['data']['object_state']['select_data'] = array('0' => 'не указано', '1' => 'Готовый объект', '2' => 'Строится', '3' => 'Вторичный рынок');
        $form_data['data']['object_state']['required'] = 'off';
        $form_data['data']['object_state']['unique'] = 'off';

        $form_data['data']['object_destination']['name'] = 'object_destination';
        $form_data['data']['object_destination']['title'] = 'Раcположение';
        $form_data['data']['object_destination']['value'] = 0;
        $form_data['data']['object_destination']['length'] = 40;
        $form_data['data']['object_destination']['type'] = 'select_box';
        $form_data['data']['object_destination']['select_data'] = array('0' => 'не указано', '1' => 'Центральная часть', '2' => 'У моря', '3' => 'В горах');
        $form_data['data']['object_destination']['required'] = 'off';
        $form_data['data']['object_destination']['unique'] = 'off';

        $form_data['data']['infra_greenzone']['name'] = 'infra_greenzone';
        $form_data['data']['infra_greenzone']['title'] = 'Зеленая зона';
        $form_data['data']['infra_greenzone']['value'] = 0;
        $form_data['data']['infra_greenzone']['type'] = 'checkbox';
        $form_data['data']['infra_greenzone']['required'] = 'off';
        $form_data['data']['infra_greenzone']['unique'] = 'off';
        $form_data['data']['infra_greenzone']['tab'] = 'Инфраструктура поблизости';

        $form_data['data']['infra_sea']['name'] = 'infra_sea';
        $form_data['data']['infra_sea']['title'] = 'Море';
        $form_data['data']['infra_sea']['value'] = 0;
        $form_data['data']['infra_sea']['type'] = 'checkbox';
        $form_data['data']['infra_sea']['required'] = 'off';
        $form_data['data']['infra_sea']['unique'] = 'off';
        $form_data['data']['infra_sea']['tab'] = 'Инфраструктура поблизости';

        $form_data['data']['infra_sport']['name'] = 'infra_sport';
        $form_data['data']['infra_sport']['title'] = 'Спорт';
        $form_data['data']['infra_sport']['value'] = 0;
        $form_data['data']['infra_sport']['type'] = 'checkbox';
        $form_data['data']['infra_sport']['required'] = 'off';
        $form_data['data']['infra_sport']['unique'] = 'off';
        $form_data['data']['infra_sport']['tab'] = 'Инфраструктура поблизости';

        $form_data['data']['infra_clinic']['name'] = 'infra_clinic';
        $form_data['data']['infra_clinic']['title'] = 'Больница';
        $form_data['data']['infra_clinic']['value'] = 0;
        $form_data['data']['infra_clinic']['type'] = 'checkbox';
        $form_data['data']['infra_clinic']['required'] = 'off';
        $form_data['data']['infra_clinic']['unique'] = 'off';
        $form_data['data']['infra_clinic']['tab'] = 'Инфраструктура поблизости';

        $form_data['data']['infra_terminal']['name'] = 'infra_terminal';
        $form_data['data']['infra_terminal']['title'] = 'Вокзал';
        $form_data['data']['infra_terminal']['value'] = 0;
        $form_data['data']['infra_terminal']['type'] = 'checkbox';
        $form_data['data']['infra_terminal']['required'] = 'off';
        $form_data['data']['infra_terminal']['unique'] = 'off';
        $form_data['data']['infra_terminal']['tab'] = 'Инфраструктура поблизости';

        $form_data['data']['infra_airport']['name'] = 'infra_airport';
        $form_data['data']['infra_airport']['title'] = 'Аэропорт';
        $form_data['data']['infra_airport']['value'] = 0;
        $form_data['data']['infra_airport']['type'] = 'checkbox';
        $form_data['data']['infra_airport']['required'] = 'off';
        $form_data['data']['infra_airport']['unique'] = 'off';
        $form_data['data']['infra_airport']['tab'] = 'Инфраструктура поблизости';

        $form_data['data']['infra_bank']['name'] = 'infra_bank';
        $form_data['data']['infra_bank']['title'] = 'Банки';
        $form_data['data']['infra_bank']['value'] = 0;
        $form_data['data']['infra_bank']['type'] = 'checkbox';
        $form_data['data']['infra_bank']['required'] = 'off';
        $form_data['data']['infra_bank']['unique'] = 'off';
        $form_data['data']['infra_bank']['tab'] = 'Инфраструктура поблизости';

        $form_data['data']['infra_restaurant']['name'] = 'infra_restaurant';
        $form_data['data']['infra_restaurant']['title'] = 'Рестораны';
        $form_data['data']['infra_restaurant']['value'] = 0;
        $form_data['data']['infra_restaurant']['type'] = 'checkbox';
        $form_data['data']['infra_restaurant']['required'] = 'off';
        $form_data['data']['infra_restaurant']['unique'] = 'off';
        $form_data['data']['infra_restaurant']['tab'] = 'Инфраструктура поблизости';


        $form_data['data']['contact_phone_1']['name'] = 'contact_phone_1';
        $form_data['data']['contact_phone_1']['title'] = 'Телефон1';
        $form_data['data']['contact_phone_1']['value'] = '';
        $form_data['data']['contact_phone_1']['length'] = 40;
        $form_data['data']['contact_phone_1']['type'] = 'safe_string';
        $form_data['data']['contact_phone_1']['required'] = 'off';
        $form_data['data']['contact_phone_1']['unique'] = 'off';

        $form_data['data']['contact_phone_2']['name'] = 'contact_phone_2';
        $form_data['data']['contact_phone_2']['title'] = 'Телефон2';
        $form_data['data']['contact_phone_2']['value'] = '';
        $form_data['data']['contact_phone_2']['length'] = 40;
        $form_data['data']['contact_phone_2']['type'] = 'safe_string';
        $form_data['data']['contact_phone_2']['required'] = 'off';
        $form_data['data']['contact_phone_2']['unique'] = 'off';

        $form_data['data']['text']['name'] = 'text';
        $form_data['data']['text']['title'] = 'Описание';
        $form_data['data']['text']['value'] = '';
        $form_data['data']['text']['length'] = 40;
        $form_data['data']['text']['type'] = 'textarea';
        $form_data['data']['text']['required'] = 'off';
        $form_data['data']['text']['unique'] = 'off';
        $form_data['data']['text']['rows'] = '10';
        $form_data['data']['text']['cols'] = '40';


        $form_data['data']['image']['name'] = 'image';
        $form_data['data']['image']['table_name'] = 'data';
        $form_data['data']['image']['primary_key'] = 'id';
        $form_data['data']['image']['primary_key_value'] = 0;
        $form_data['data']['image']['action'] = 'data';
        $form_data['data']['image']['title'] = 'Фотографии ';
        $form_data['data']['image']['value'] = '';
        $form_data['data']['image']['length'] = 40;
        $form_data['data']['image']['type'] = 'uploadify_image';
        $form_data['data']['image']['required'] = 'off';
        $form_data['data']['image']['unique'] = 'off';

        if ($this->getConfigValue('apps.realtypro.youtube')) {
            $form_data['data']['youtube']['name'] = 'youtube';
            $form_data['data']['youtube']['title'] = 'Видео';
            $form_data['data']['youtube']['value'] = '';
            $form_data['data']['youtube']['length'] = 40;
            $form_data['data']['youtube']['type'] = 'safe_string';
            $form_data['data']['youtube']['required'] = 'off';
            $form_data['data']['youtube']['unique'] = 'off';
        }

        $form_data['data']['view_count']['name'] = 'view_count';
        $form_data['data']['view_count']['title'] = 'Количество просмотров';
        $form_data['data']['view_count']['value'] = '';
        $form_data['data']['view_count']['length'] = 40;
        $form_data['data']['view_count']['type'] = 'hidden';
        $form_data['data']['view_count']['required'] = 'off';
        $form_data['data']['view_count']['unique'] = 'off';

        $form_data['data']['whoyuaare']['name'] = 'whoyuaare';
        $form_data['data']['whoyuaare']['title'] = 'Кто вы';
        $form_data['data']['whoyuaare']['value'] = 0;
        $form_data['data']['whoyuaare']['length'] = 40;
        $form_data['data']['whoyuaare']['type'] = 'select_box';
        $form_data['data']['whoyuaare']['select_data'] = array('0' => 'не указано', '1' => 'собственник', '2' => 'агентство', '3' => 'частный риелтор');
        $form_data['data']['whoyuaare']['required'] = 'off';
        $form_data['data']['whoyuaare']['unique'] = 'off';

        $form_data['data']['fio']['name'] = 'fio';
        $form_data['data']['fio']['title'] = 'Ваше имя';
        $form_data['data']['fio']['value'] = '';
        $form_data['data']['fio']['length'] = 40;
        $form_data['data']['fio']['type'] = 'safe_string';
        $form_data['data']['fio']['required'] = 'off';
        $form_data['data']['fio']['unique'] = 'off';

        $form_data['data']['email']['name'] = 'email';
        $form_data['data']['email']['title'] = 'E-mail';
        $form_data['data']['email']['value'] = '';
        $form_data['data']['email']['length'] = 40;
        $form_data['data']['email']['type'] = 'email';
        $form_data['data']['email']['required'] = 'off';
        $form_data['data']['email']['unique'] = 'off';

        $form_data['data']['phone']['name'] = 'phone';
        $form_data['data']['phone']['title'] = 'Ваш телефон (мобильный)<br />Формат ввода <b>8**********</b>';
        $form_data['data']['phone']['value'] = '';
        $form_data['data']['phone']['length'] = 40;
        $form_data['data']['phone']['type'] = 'mobilephone';
        $form_data['data']['phone']['required'] = 'off';
        $form_data['data']['phone']['unique'] = 'off';


        return $form_data;
    }*/

}