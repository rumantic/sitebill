<?php

/**
 * Region manager
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class Region_Manager extends Object_Manager {

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
        $this->table_name = 'region';
        $this->action = 'region';
        $this->app_title = Multilanguage::_('REGION_APP_NAME', 'system');
        $this->primary_key = 'region_id';

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/model/region_model.php')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/model/region_model.php');
            $data_model = new Region_Data_Model();
        } else {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            $data_model = new Data_Model();
        }
        $this->data_model = $data_model->get_region_model();
    }

    function grid($params = array(), $default_params = array()) {
        $default_params['grid_item'] = array('region_id', 'name', 'country_id');
        return parent::grid(array(), $default_params);
    }

    /**
     * Delete data
     * @param string $table_name
     * @param string $primary_key
     * @param int $primary_key_value
     */
    function delete_data($table_name, $primary_key, $primary_key_value) {
        $DBC = DBC::getInstance();

        $search_queries = array(
            Multilanguage::_('TABLE_CITY', 'system') => 'SELECT COUNT(*) AS rs FROM ' . DB_PREFIX . '_city WHERE region_id=?',
            Multilanguage::_('TABLE_ADS', 'system') => 'SELECT COUNT(*) AS rs FROM ' . DB_PREFIX . '_data WHERE region_id=?'
        );
        $ans = array();
        $DBC = DBC::getInstance();


        foreach ($search_queries as $k => $v) {
            $query = str_replace('?', $primary_key_value, $v);
            $stmt = $DBC->query($query);
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $rs = $ar['rs'];
                if ($rs != 0) {
                    $ans[] = sprintf(Multilanguage::_('MESSAGE_CANT_DELETE', 'system'), $k);
                }
            }
        }
        if (empty($ans)) {
            return parent::delete_data($table_name, $primary_key, $primary_key_value);
        } else {
            $this->riseError(implode('<br />', $ans));
        }
    }

}
