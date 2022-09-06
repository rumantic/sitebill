<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
use system\lib\system\cache\RedisCache;

/**
 * Vendors admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class config_admin extends Object_Manager
{

    private $dev_status = 0;
    protected static $check_config_array = array();



    ///private static $check_config_array_static = array();

    /**
     * Constructor
     */
    function __construct($realty_type = false)
    {
        parent::__construct();

        $this->table_name = 'config';
        $this->action = 'config';
        $this->app_title = Multilanguage::_('L_SETTINGS');
        $this->primary_key = 'id';
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/config_model.php');
        $model = new Config_Model();
        $this->data_model = $model->get_model();
        $this->check_config_structure();
    }

    function ajax()
    {
        return false;
    }

    function main()
    {
        $this->template->assign('disable_vue', 1);

        if ($this->is_demo()) {
            return 'Конфигурация в демо-версии отключена';
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        global $__user, $__db;
        //$this->clear_apps_cache();

        $rs = $this->getTopMenu();
        switch ($this->getRequestValue('do')) {
            case 'new' :
            {
                $rs .= $this->get_form($this->data_model[$this->table_name], 'new');
                break;
            }

            case 'new_done' :
            {
                $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
                $form_data[$this->table_name]['title']['value'] = $this->validateParamTitle($form_data[$this->table_name]['title']['value']);

                if (!$this->check_data($form_data[$this->table_name])) {
                    $rs .= $this->get_form($form_data[$this->table_name], 'new');
                } else {
                    $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
                    if ($this->getError()) {
                        $rs .= $this->get_form($form_data[$this->table_name], 'new');
                    } else {

                        $rs .= $this->grid();
                    }
                }
                break;
            }

            case 'edit' :
            {
                $form_data[$this->table_name] = $data_model->init_model_data_from_db($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name]);
                $rs .= $this->get_form($form_data[$this->table_name], 'edit');
                break;
            }

            case 'edit_done' :
            {

                $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
                $form_data[$this->table_name]['title']['value'] = $this->validateParamTitle($form_data[$this->table_name]['title']['value']);
                if (!$this->check_data($form_data[$this->table_name])) {
                    $rs .= $this->get_form($form_data[$this->table_name], 'edit');
                } else {
                    $this->edit_data($form_data[$this->table_name]);
                    if ($this->getError()) {
                        $rs .= $this->get_form($form_data[$this->table_name], 'edit');
                    } else {
                        $rs .= $this->grid();
                    }
                }
                break;
            }

            case 'save' :
            {
                $back_url = FALSE;
                if (isset($_SERVER['HTTP_REFERER'])) {
                    $back_url = $_SERVER['HTTP_REFERER'];
                }
                //echo '<pre>';
                //print_r($_POST);
                if ($this->isDemo()) {
                    $rs .= Multilanguage::_('L_MESSAGE_THIS_IS_TRIAL');
                    return $rs;
                }

                $post = $this->getRequestValue('conf_param_value');
                if ( !isset($post) ) {
                    $post = $this->request()->get('conf_param_value');
                }
                // TODO временно отключаем получение параметров через стандартную функцию для избежания очистки пустых значений
                $post = $this->request()->get('conf_param_value');
                //print_r($_REQUEST['conf_param_value']);

                //exit();
                if (count($post) > 0) {
                    foreach ($post as $k => $v) {
                        $this->updateParamToConfig($k, $v);
                    }
                }

                if ($back_url) {
                    $data_url = parse_url($back_url);
                    if (preg_match('/action=([^&]*)/', $data_url['query'], $matches)) {
                        if ($matches[1] !== 'config') {
                            $url = $data_url['scheme'] . '://' . $data_url['host'] . $data_url['path'] . '?' . $matches[0];
                            header('location:' . $url);
                            exit();
                        }
                    }
                }

                $rs .= $this->grid();
                break;
            }

            case 'delete' :
            {
                $this->delete_data($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
                if ($this->getError()) {
                    $rs .= '<div align="center">' . Multilanguage::_('L_ERROR_ON_DELETE') . ': ' . $this->GetErrorMessage() . '<br>';
                    $rs .= '<a href="?action=' . $this->action . '">ОК</a>';
                    $rs .= '</div>';
                } else {
                    $rs .= $this->grid();
                }
                break;
            }
            case 'extended' :
            {
                $rs .= $this->grid_extended();
                break;
            }

            case 'text' :
            {
                $rs .= $this->grid_text();
                break;
            }

            default :
            {
                //$this->loadAllConfigParams();
                $rs .= $this->grid();
                //$rs.=$this->getAddForm();
            }
        }
        $rs_new = $this->get_app_title_bar();
        $rs_new .= $rs;
        //$this->clear_apps_cache();

        return $rs_new;
    }

    function install_hidden_config()
    {
        $DBC = DBC::getInstance();
        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_hidden_config` (
		  `config_key` varchar(255) NOT NULL,
		  `config_value` text NOT NULL,
		  UNIQUE KEY `conf_param` (`config_key`)
		) ENGINE=MyISAM DEFAULT CHARSET=" . DB_ENCODING . ";";
        $stmt = $DBC->query($query);
    }

    function install()
    {
        $DBC = DBC::getInstance();
        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_" . $this->table_name . "` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `config_key` varchar(255) NOT NULL DEFAULT '',
		  `value` text,
		  `title` text,
        	`vtype` INT(11) DEFAULT 0, 
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=" . DB_ENCODING . " ;";
        $stmt = $DBC->query($query);
    }

    function getConfigSection($section = NULL)
    {
        $ret = array();
        $data = $this->createConfigStructure();
        if ($section !== NULL) {
            $ret = $data[$section];
        }
        return $ret;
    }

    function grid_text()
    {

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/apps/apps_processor.php');
        $APP = new Apps_Processor();
        $apps = $APP->load_apps_menu(true);
        foreach ($apps as $ak => $av) {
            $apps_array['apps.' . $ak] = $av;
        }
        $apps_array['apps.realty'] = array('title' => 'Дополнительно');
        $apps_array['apps.contact'] = array('title' => 'Контакты');
        $data = $this->createConfigStructure();
        $keys = array_keys($data);
        $keys_fl = array_flip($keys);
        $codenames_to_names = array();

        foreach ($keys_fl as $k => $v) {
            if (strpos($k, 'apps.') !== FALSE) {
                if (isset($apps_array[$k])) {
                    $codenames_to_names[$k] = $apps_array[$k]['title'];
                }
            } else {
                $codenames_to_names[$k] = $k;
            }
        }

        $primary_tab = $codenames_to_names[Multilanguage::_('L_COMMON')];
        unset($codenames_to_names[Multilanguage::_('L_COMMON')]);

        asort($codenames_to_names);

        $str = '';
        foreach ($data[$primary_tab] as $d) {
            $str .= strip_tags($d['title']) . ' (' . $d['config_key'] . ')<br>';
        }
        foreach ($codenames_to_names as $k => $v) {
            $str .= '<h3>' . $k . '</h3>';
            foreach ($data[$k] as $d) {
                $str .= strip_tags($d['title']) . ' (' . $d['config_key'] . ')<br>';
            }
        }
        return $str;
    }

    function api_array () {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/apps/apps_processor.php');
        $APP = new Apps_Processor();
        $apps = $APP->load_apps_menu(true);
        foreach ($apps as $ak => $av) {
            $apps_array['apps.' . $ak] = $av;
        }
        $apps_array['apps.realty'] = array('title' => 'Дополнительно');
        $apps_array['apps.contact'] = array('title' => 'Контакты');
        $data = $this->createConfigStructure();
        $keys = array_keys($data);
        $keys_fl = array_flip($keys);
        $codenames_to_names = array();

        foreach ($keys_fl as $k => $v) {
            if (strpos($k, 'apps.') !== FALSE) {
                if (isset($apps_array[$k])) {
                    $codenames_to_names[$k] = $apps_array[$k]['title'];
                }
            } else {
                $codenames_to_names[$k] = $k;
            }
        }

        $primary_tab = $codenames_to_names[Multilanguage::_('L_COMMON')];

        asort($codenames_to_names);

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/config_mask.php');
        $CM = new Config_Mask();
        $config_mask = $CM->get_model();

        $i = 0;
        $primary_tab_index = 0;
        foreach ($codenames_to_names as $k => $v) {
            if ( $k == $primary_tab ) {
                $primary_tab_index = $i;
            }
            $result[$i] = array(
                'title' => $v,
                'key' => $k,
            );


            foreach ($data[$k] as $d) {
                $ret = array();
                if (!isset($config_mask[$d['config_key']])) {
                    $ret['name'] = $d['config_key'];
                    $ret['hint'] = $d['config_key'];
                    $ret['title'] = $d['title'];


                    $ret['value'] = $d['value'];
                    if ( $d['vtype'] == SConfig::$fieldtypeString ) {
                        $ret['type'] = 'safe_string';
                    } elseif ( $d['vtype'] == SConfig::$fieldtypeCheckbox ) {
                        $ret['type'] = 'checkbox';
                    } elseif ( $d['vtype'] == SConfig::$fieldtypeSelectbox ) {
                        $ret['type'] = 'select_box';
                    } elseif ( $d['vtype'] == SConfig::$fieldtypeTextarea ) {
                        $ret['type'] = 'textarea';
                    } elseif ( $d['vtype'] == SConfig::$fieldtypeUploads ) {
                        $ret['type'] = 'uploads';
                        $ret['value'] = unserialize($ret['value']);
                    } else {
                        $ret['type'] = 'safe_string';
                    }

                    if(isset($d['params']['select_data'])){
                        $ret['select_data'] = $d['params']['select_data'];
                        $select_data_indexed = array();
                        foreach ( $ret['select_data'] as $key_s => $value_s ) {
                            array_push($select_data_indexed, array('id'=>$key_s, 'value' => $value_s));
                        }
                        $ret['select_data_indexed'] = $select_data_indexed;
                    }

                    $ret['sort_order'] = $d['sort_order'];
                } else {

                    $ret = $config_mask[$d['config_key']];
                    if ( $ret['type'] == 'select_box' ) {
                        $select_data_indexed = array();
                        foreach ( $ret['select_data'] as $key_s => $value_s ) {
                            array_push($select_data_indexed, array('id'=>$key_s, 'value' => $value_s));
                        }
                        $ret['select_data_indexed'] = $select_data_indexed;
                    }


                    $ret['name'] = $d['config_key'];
                    $ret['hint'] = $d['config_key'];
                    $ret['title'] = $d['title'];
                    $ret['value'] = $d['value'];
                    $ret['sort_order'] = $d['sort_order'];
                }

                if ( $ret['type'] != 'checkbox' and $d['title'] != strip_tags($d['title']) ) {
                    $ret['hint'] = $d['config_key'].' '.$d['title'];
                }

                $result[$i]['data'][$d['config_key']] = $ret;
            }
            $i++;

        }

        $primary_tab_array = $result[$primary_tab_index];
        unset($result[$primary_tab_index]);
        array_unshift($result, $primary_tab_array);

        return $result;
    }

    function grid($params = array(), $default_params = array())
    {
        if ( self::$replace_grid_with_angular ) {
            return $this->angular_grid();
        }

        $rs = '';
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/apps/apps_processor.php');
        $APP = new Apps_Processor();
        $apps = $APP->load_apps_menu(true, 'admin', false);
        foreach ($apps as $ak => $av) {
            $apps_array['apps.' . $ak] = $av;
        }
        $apps_array['apps.realty'] = array('title' => 'Дополнительно');
        $apps_array['apps.contact'] = array('title' => 'Контакты');


        $data = $this->createConfigStructure();

        $current_tab_nr = (int)$this->getRequestValue('tab_nr');

        $keys = array_keys($data);
        $keys_fl = array_flip($keys);


        $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/config/js/utils.js"></script>';
        $rs .= '<style>.form-horizontal .control-label {width: 460px;} .form-horizontal .controls {margin-left: 480px;}</style>';


        $codenames_to_names = array();

        foreach ($keys_fl as $k => $v) {
            if (strpos($k, 'apps.') !== FALSE) {
                if (isset($apps_array[$k])) {
                    $codenames_to_names[$k] = $apps_array[$k]['title'];
                }
            } else {
                $codenames_to_names[$k] = $k;
            }
        }

        $primary_tab = $codenames_to_names[Multilanguage::_('L_COMMON')];
        unset($codenames_to_names[Multilanguage::_('L_COMMON')]);

        asort($codenames_to_names);

        $rs .= '<div class="tabbable tabs-left">';
        $rs .= '<ul class="nav nav-tabs">';

        $rs .= '<li' . (0 == $current_tab_nr ? ' class="active"' : '') . '><a href="#config-tabs-left-0" data-toggle="tab">' . $primary_tab . '</a></li>';

        $ti = 1;
        foreach ($codenames_to_names as $k => $v) {
            $rs .= '<li' . ($ti == $current_tab_nr ? ' class="active"' : '') . '><a href="#config-tabs-left-' . $ti . '" data-toggle="tab">' . $v . '</a></li>';
            $ti++;
        }


        $tf = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/settings.php';
        if (file_exists($tf)) {
            $rs .= '<li><a href="#config-tabs-left-template-sets" data-toggle="tab">Настройки Шаблона</a></li>';
        }

        $rs .= '</ul>';
        $rs .= '<div class="tab-content">';

        $rs .= '<div id="config-tabs-left-0" class="tab-pane fade in' . (0 == $current_tab_nr ? ' active' : '') . '">';
        $rs .= '<h3>' . $primary_tab . '</h3>';
        $rs .= $this->getTabForm($data[$primary_tab], $ti);
        $rs .= '</div>';

        $ti = 1;
        foreach ($codenames_to_names as $k => $v) {
            $rs .= '<div id="config-tabs-left-' . $ti . '" class="tab-pane fade in' . ($ti == $current_tab_nr ? ' active' : '') . '">';
            if (isset($apps_array[$k])) {
                $rs .= '<h3>' . $apps_array[$k]['title'] . '</h3>';
            } else {
                $rs .= '<h3>' . $k . '</h3>';
            }
            $rs .= $this->getTabForm($data[$k], $ti);
            $rs .= '</div>';
            $ti++;
        }
        if (file_exists($tf)) {
            $rs .= '<div id="config-tabs-left-template-sets" class="tab-pane fade in">';
            $rs .= '<h3>Настройки шаблона</h3>';
            require_once($tf);
            $st = new template_setting();
            $rs .= '<div>' . $st->getform(ltrim($_SERVER['HTTP_HOST'], 'www.')) . '</div>';
            $rs .= '</div>';
        }
        $rs .= '</div>';
        $rs .= '</div>';


        return $rs;
    }

    private function customSort($a, $b)
    {
        if ($a['name'] < $b['name']) {
            return -1;
        }
        return 1;
    }

    function createConfigStructure()
    {
        $DBC = DBC::getInstance();

        $data = array();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_config ORDER BY sort_order ASC, config_key ASC';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                if($ar['vtype'] == SConfig::$fieldtypeLangSelect){
                    $ar['value'] = ($ar['value'] != '' ? json_decode($ar['value'], true) : array());
                }
                if(isset($ar['params']) && '' != $ar['params']){
                    $ar['params'] = json_decode($ar['params'], true);
                }else{
                    $ar['params'] = array();
                }
                $ret[] = $ar;
                $list = array();
                $list = explode('.', $ar['config_key']);
                if (count($list) == 1) {
                    $data[Multilanguage::_('L_COMMON')][] = $ar;
                } elseif (count($list) > 2) {
                    $data[$list[0] . '.' . $list[1]][] = $ar;
                }
            }
        }

        $ob[Multilanguage::_('L_COMMON')] = $data[Multilanguage::_('L_COMMON')];
        unset($data[Multilanguage::_('L_COMMON')]);
        //echo '<pre>';

        ksort($data);
        foreach ($data as $k => $d) {
            $ob[$k] = $d;
        }
        //array_unshift($data, $ob);
        //print_r($data);
        return $ob;
    }

    function getTabForm($data, $tab_nr = 0)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/config_mask.php');
        $CM = new Config_Mask();
        $config_mask = $CM->get_model();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/config_form_generator.php');
        $FG = new Config_Form_Generator();


        $rs = '<form class="config_form form-horizontal applied" method="post" action="' . SITEBILL_MAIN_URL . '/admin/?action=' . $this->action . '">';

        foreach ($data as $d) {
            if ($this->is_demo() and $d['config_key'] == 'license_key') {

            } else {
                $ret = array();
                if (!isset($config_mask[$d['config_key']])) {
                    $ret['name'] = $d['id'];
                    $ret['hint'] = $d['config_key'];

                    $ret['title'] = $d['title'];
                    $ret['value'] = $d['value'];
                    if ($d['vtype'] == SConfig::$fieldtypeString) {
                        $ret['type'] = 'safe_string';
                    } elseif ($d['vtype'] == SConfig::$fieldtypeCheckbox) {
                        $ret['type'] = 'checkbox';
                    } elseif ($d['vtype'] == SConfig::$fieldtypeSelectbox) {
                        $ret['type'] = 'select_box';
                        $ret['select_data'] = array();
                        if(isset($d['params']['select_data'])){
                            $ret['select_data'] = $d['params']['select_data'];
                        }
                    } elseif ($d['vtype'] == SConfig::$fieldtypeTextarea) {
                        $ret['type'] = 'textarea';
                    } elseif ($d['vtype'] == SConfig::$fieldtypeLangSelect) {
                        $ret['type'] = 'langselect';

                    } else {
                        $ret['type'] = 'safe_string';
                    }

                    $ret['sort_order'] = $d['sort_order'];
                } else {

                    $ret = $config_mask[$d['config_key']];

                    $ret['name'] = $d['id'];
                    $ret['hint'] = $d['config_key'];
                    $ret['title'] = $d['title'];
                    $ret['value'] = $d['value'];
                    $ret['sort_order'] = $d['sort_order'];
                }
                //$elements=$FG->compile_form_elements(array($ret));
                $rs .= $FG->compile_form(array($ret));
            }
        }
        //$rs .= '</tbody>';
        //$rs .= '<tr>';

        $rs .= '<div class="control-group">';

        $rs .= '<label class="control-label">';
        //$rs .= '<button type="button" name="cnf_resort" class="btn btn-info cnf_resort"><i class="icon-refresh icon-white"></i> '.Multilanguage::_('L_SORT').'</button> ';
        $rs .= '<button type="submit" name="cnf_submit" class="btn btn-primary">' . Multilanguage::_('L_TEXT_SAVE') . '</button>';
        $rs .= '</label>';
        $rs .= '<div class="controls">';
        $rs .= '</div>';
        $rs .= '</div>';

        //$rs .= '<td><input type="button" name="cnf_resort" class="cnf_resort" value="'.Multilanguage::_('L_SORT').'"></td><td>
        //		<input type="submit" name="cnf_submit" value="'.Multilanguage::_('L_TEXT_SAVE').'"></td><td></td>';
        //$rs .= '</tr>';
        $rs .= '<input type="hidden" name="do" value="save">';
        $rs .= '<input type="hidden" name="tab_nr" value="' . $tab_nr . '">';
        //$rs .= '</table>';
        $rs .= '</form>';

        return $rs;
    }

    function updateParamToConfig($conf_param_id, $conf_param_value)
    {
        if ( SConfig::getConfigTypeById($conf_param_id) == SConfig::$fieldtypeUploads ) {
            return false;
        }
        $DBC = DBC::getInstance();
        $query = "UPDATE `" . DB_PREFIX . "_" . $this->table_name . "` SET `value`=? WHERE `" . $this->primary_key . "`=?";
        if(is_array($conf_param_value)){
            $conf_param_value = json_encode($conf_param_value);
        }else{
            $conf_param_value = $this->validateParam($conf_param_value);
        }
        //echo 'update '.$conf_param_id.' as value "'.$conf_param_value.'"'.'<br>';
        //$conf_param_value = $this->validateParam($conf_param_value);
        $DBC->query($query, array($conf_param_value, $conf_param_id), $rows, $success);
        if ( !$success ) {
            $this->riseError($DBC->getLastError());
            return false;
        }
        return TRUE;
    }

    function updateParamByKey($conf_param_key, $conf_param_value)
    {
        $DBC = DBC::getInstance();
        $query = "UPDATE `" . DB_PREFIX . "_" . $this->table_name . "` SET `value`=? WHERE `config_key`=?";
        $DBC->query($query, array($this->validateParam($conf_param_value), $conf_param_key), $rows, $success);
        if ( !$success ) {
            $this->riseError($DBC->getLastError());
            return false;
        }
        return TRUE;
    }

    function validateParam($param)
    {
        $rs = $param;
        $rs = str_replace(array('\'', '"', '`'), '', $rs);
        if ( is_scalar($rs) ) {
            $rs = trim($rs);
        }
        return $rs;
    }

    function validateParamTitle($param)
    {
        $rs = $param;
        $rs = str_replace(array('`'), '', $rs);
        $rs = trim($rs);
        return $rs;
    }

    function is_demo()
    {
        if (preg_match('/estate\.sitebill\.ru/', $_SERVER['HTTP_HOST'])) {
            return true;
        }
        return false;
    }

    /**
     * Check config item
     * @param string $key
     * @return boolean
     */
    function check_config_item($key)
    {
        if (@self::$check_config_array[$key] == 1) {
            return true;
        }
        return false;
    }

    /**
     * Create new config entry
     * @param $conf_new_param_name - parameter codename
     * @param $conf_new_param_value - parameter default value
     * @param $conf_new_param_title - parameter label
     * @param int $vtype - parameter type (1 - checkbox)
     * @param array $params - parameters
     * @return bool
     */
    function addParamToConfig($conf_new_param_name, $conf_new_param_value, $conf_new_param_title, $vtype = 0, $params = array())
    {
        if ($this->check_config_item($conf_new_param_name)) {
            return true;
        }
        $DBC = DBC::getInstance();

        if($vtype == SConfig::$fieldtypeSelectbox){
            $selectdata = array();
            if(!empty($params)){
                $selectdata = $params;
            }
            $query = "INSERT INTO " . DB_PREFIX . "_" . $this->table_name . " (`config_key`, `value`, `title`, `vtype`, `params`) VALUES (?,?,?,?,?)";
            $stmt = $DBC->query($query, array($this->validateParam($conf_new_param_name), $this->validateParam($conf_new_param_value), $this->validateParamTitle($conf_new_param_title), $vtype, json_encode($selectdata)), $row, $success);
        }else{
            $query = "INSERT INTO " . DB_PREFIX . "_" . $this->table_name . " (`config_key`, `value`, `title`, `vtype`) VALUES (?,?,?,?)";
            $stmt = $DBC->query($query, array($this->validateParam($conf_new_param_name), $this->validateParam($conf_new_param_value), $this->validateParamTitle($conf_new_param_title), $vtype), $row, $success);
        }
        if (!$success) {
            //$this->riseError($DBC->getLastError());
            //echo 'ERROR ON INSERT<br>';
        }
        $config_id = $DBC->lastInsertId();
        $query = "UPDATE `" . DB_PREFIX . "_" . $this->table_name . "` SET `sort_order`=? WHERE `id`=?";
        $stmt = $DBC->query($query, array($config_id, $config_id));
        if (@$params['public'] == true) {
            $this->set_public_access($conf_new_param_name);
        }
        $this->reloadCheckConfigStructure();
        return TRUE;
    }

    function set_public_access($conf_new_param_name)
    {
        $DBC = DBC::getInstance();
        $query = "UPDATE `" . DB_PREFIX . "_" . $this->table_name . "` SET `public`=? WHERE `config_key`=?";
        $stmt = $DBC->query($query, array(1, $conf_new_param_name));
    }

    function getTopMenu()
    {
        $rs = '';
        //$rs.='<a href="'.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=new">Добавить параметр</a>';
        return $rs;
    }

    /**
     * Check config structure
     * @param void
     * @return string
     */
    function check_config_structure()
    {
        $this->reloadCheckConfigStructure();
        if (empty(self::$check_config_array)) {
            self::$check_config_array = SConfig::$check_config_array;
        }
        //ВАЖНЫЕ КОНФИГИ
        if (!$this->check_config_item('system_email')) {
            if ($_SERVER['SERVER_NAME'] != '') {
                $system_email = 'info@' . $_SERVER['SERVER_NAME'];
            } else {
                $system_email = '';
            }
            $this->addParamToConfig('system_email', $system_email, 'От чьего email будут отправляться письма с сайта. Подробнее о настройке <a href="http://wiki.sitebill.ru/index.php?title=Mail" target="_blank">тут</a>');
        }
        $this->addParamToConfig('system_email_robot', '', 'Имя робота отправщика для писем');

        //Общие
        $this->addParamToConfig('site_title', 'Агентство недвижимости', 'Заголовок сайта');
        $this->addParamToConfig('theme', 'agency', 'Тема оформления');
        $this->addParamToConfig('order_email_acceptor', 'kondin@etown.ru', 'Email на который будут приходить заявки с сайта');
        $this->addParamToConfig('per_page', '20', 'Количество объявлений на одну страницу на сайте');
        $this->addParamToConfig('common_per_page', '10', 'Количество позиций на страницу (для списков справочников в админке)');

        $this->addParamToConfig('per_page_account', '10', 'Количество позиций на страницу (для списков в ЛК)');

        $this->addParamToConfig('core.listing.pager_draw_all', '0', 'Отрисовывать все страницы в постраничной навигации', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('core.listing.pager_page_offset', '7', 'Количество страниц показываемых в обе стороны от активной');

        $this->addParamToConfig('core.listing.pager_draw_all_max', '0', 'Максимальное число страниц, до которого в пейджере будут отображаться все страницы');

        $this->addParamToConfig('core.listing.pager_end_buttons', '1', 'Отображать кнопки Первая-Последняя', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('core.listing.pager_prev_buttons', '1', 'Отображать кнопки Предыдущая-Следующая', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('core.listing.pager_show_prefixes', '1', 'Отображать префиксы для пропущенных страниц', SConfig::$fieldtypeCheckbox);


        $this->addParamToConfig('currency_enable', '0', 'Включить поддержку выбора валют в объявлении', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('allow_login_account', '1', 'Разрешить вход в личный кабинет', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('allow_register_account', '1', 'Разрешить регистрацию на сайте', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('allow_remind_password', '1', 'Разрешить напоминание пароля', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('bootstrap_version', '', 'Версия Bootstrap');

        $this->addParamToConfig(
            'use_google_map',
            '0',
            'Использовать карту Google',
            SConfig::$fieldtypeSelectbox,
            array(
            'select_data' => array('0'=>'Yаndex','1'=>'Google','2'=>'Leaflet OSM'
            )
        ));

        $this->addParamToConfig('google_api_key', '', 'Ключ API Google');

        $this->addParamToConfig('google_api_key_server', '', 'Ключ API Google для серверных запросов');

        $this->addParamToConfig('google_recaptcha_key', '', 'Ключ Google ReCaptcha');

        $this->addParamToConfig('use_captcha_admin_entry', '0', 'Использовать капчу на входе в админку', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('work_on_https', '0', 'Работать через https', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('moderate_first', '0', 'Не публиковать объявления из ЛК без премодерации', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('hide_contact_input_user_data', '0', 'Убрать поля ввода контактов из формы добавления объявления в личном кабинете', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('use_realty_view_counter', '1', 'Использовать встроенный счетчик просмотров', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('date_format', 'standart', 'Формат даты', SConfig::$fieldtypeSelectbox, array(
            'select_data' => array('standart'=>'standart','eu'=>'EU','us'=>'US')
        ));

        $this->addParamToConfig('ue_name', 'руб.', 'Название валюты в личном кабинете');

        $this->addParamToConfig('enable_special_in_account', '0', 'В личном кабинете доступна галочка спец.размещений', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('enable_curator_mode', '0', 'Активировать режим куратора', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig(
            'enable_coworker_mode',
            '0',
            'Активировать режим совместной работы над записями',
            SConfig::$fieldtypeCheckbox,
            array('public' => true)
        );

        $this->addParamToConfig('curator_mode_fullaccess', '0', 'Полный доступ куратора к объектам стажера', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('curator_mode_chainsallow', '0', 'Разрешить цепочки кураторства', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('use_new_realty_grid', '1', 'Использовать настраиваемую сетку в выводе в админке', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('notify_admin_about_register', '0', 'Уведомлять администратора о новой регистрации пользователя', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('notify_about_added_realty', '0', 'Уведомлять пользователя о добавленных объявлениях', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('filter_double_data', '0', 'Не допускать добавления дубликатов данных', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('check_permissions', '1', 'Разделение прав доступа для групп. Группа администраторов (admin) имеет доступ ко всем функциям без учета прав доступа.', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('allow_user_email_change', '0', 'Разрешить пользователям изменять email', SConfig::$fieldtypeCheckbox);

        /*
          if ( !$this->check_config_item('divide_step_form') ) {
          $this->addParamToConfig('divide_step_form','0','Делить формы на шаги');
          }
         */

        $this->addParamToConfig('use_registration_email_confirm', '0', 'Использовать активацию аккаунта по email при регистрации', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('use_registration_sms_confirm', '0', 'Использовать активацию аккаунта с помщью SMS при регистрации. <a href="http://wiki.sitebill.ru/index.php?title=%D0%A0%D0%B5%D0%B3%D0%B8%D1%81%D1%82%D1%80%D0%B0%D1%86%D0%B8%D1%8F_%D1%81_SMS_%D0%BF%D0%BE%D0%B4%D1%82%D0%B2%D0%B5%D1%80%D0%B6%D0%B4%D0%B5%D0%BD%D0%B8%D0%B5%D0%BC" target="_blank">Подробнее</a>', 1);

        $this->addParamToConfig('email_signature', 'С уважением, команда ' . $_SERVER['SERVER_NAME'], 'Подпись в письмах');

        $this->addParamToConfig('registration_notice', '0', 'Уведомлять пользователя о регистрации', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('meta_title_main', '', 'Заголовок главной');

        $this->addParamToConfig('meta_keywords_main', '', 'Ключевые слова главной');

        $this->addParamToConfig('meta_description_main', '', 'Мета-описание главной');

        $this->addParamToConfig('default_tab_name', 'Основное', 'Название закладки формы по-умолчанию');

        $this->addParamToConfig('csrf_salt', '', 'Соль для создания CSRF-токена');

        //view
        $this->addParamToConfig('photo_per_data', '0', 'Количество изображений для одного объекта (0 или ничего - без ограничений)');


        //notify
        $this->addParamToConfig('add_notification_email', '', 'E-mail для получения уведомлений о новых объявлениях (при отсутствии изпользуется order_email_acceptor)');

        $this->addParamToConfig('notify_about_publishing', '0', 'Уведомлять пользователя о публикации его объявления после модерации.', SConfig::$fieldtypeCheckbox);


        $this->addParamToConfig('post_form_agreement_enable', '0', 'Активировать выдачу соглашения после формы', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('post_form_agreement_text_add', 'Я,  ознакомлен(а) с Пользовательским соглашением', 'Текст соглашения после формы добавления объявления');

        $this->addParamToConfig('post_form_agreement_enable_note', '0', 'Выводить соглашение с формой в виде текстового уведомления', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('register_form_agreement_enable', '0', 'Добавлять элемент согласия с Правилами к форме регистрации', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('register_form_agreement_enable_ch', '0', 'Элемент согласия с Правилами к форме регистрации изначально выбран', SConfig::$fieldtypeCheckbox);


        $this->addParamToConfig('is_watermark', '0', 'Использовать watermark на фотографиях<br> (по-умолчанию картинка лежит тут /img/watermark/watermark.gif)', SConfig::$fieldtypeCheckbox);

        /*if (!$this->check_config_item('watermark_user_control')) {
            $this->addParamToConfig('watermark_user_control', '0', 'Установка применения watermark пользователем на уровне объектов', SConfig::$fieldtypeCheckbox);
        }*/
        //admin
        $this->addParamToConfig('hide_empty_catalog', '1', 'Прятать каталоги без содержимого', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('user_account_enable', '0', 'Редактировать лицевой счет пользователя в админке', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('seo_photo_name_enable', '0', 'Включить SEO-оптимизацию названий изображений', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('advert_cost', '0', 'Стоимость размещения одного простого объявления. <a href="http://www.sitebill.ru/stoimost-obyavleniya.html" target="_blank">Что это такое?</a>');

        $this->addParamToConfig('special_advert_cost', '0', 'Стоимость размещения одного специального предложения');

        $this->addParamToConfig('editor', 'cleditor', 'Тип WYSIWYG-редактора', SConfig::$fieldtypeSelectbox, array(
            'select_data' => array('cleditor'=>'cleditor','ckeditor'=>'ckeditor','codemirror'=>'codemirror')
        ));

        $this->addParamToConfig('autocomplete_distinct', '0', 'Фильтровать данные autocomplete-выдачи на уникальность названий',SConfig::$fieldtypeCheckbox);


        //Второстепенные КОНФИГИ
        $this->addParamToConfig('login_user_data_ad', '', 'Список дополнительных полей данных авторизированного пользователя', 3);

        $this->addParamToConfig('set_cookie_subdomenal', 0, 'Устанавливать COOKIE для всех субдоменов', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.realty.allow_notactive_direct', 0, 'Открыть доступ к неактивным объектам по прямой ссылке', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('user_pic_smart', 0, 'Выдерживать точный размер для аватар пользователей', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('default_timezone', '', 'Временная зона');

        $this->addParamToConfig('classic_local_grid', '0', 'Использовать классический локальный конструктор списков', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('classic_local_view', '0', 'Использовать классический локальный конструктор карточки', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('user_anonimouse_group_id', '0', 'ID группы гостей');

        $this->addParamToConfig('login_block_time', '5', 'Время блокироваки авторизаций аккаунта в минутах');

        $this->addParamToConfig('max_login_try_count', '5', 'Количество ошибочных попыток авторизации');

        $this->addParamToConfig('core_domain', '', 'Базовый домен (без протокола)');

        $this->addParamToConfig('robokassa_pay_enable', '1', 'Включить модуль ROBOKASSA', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('robokassa_by_frekassa', '0', 'Работа модуля ROBOKASSA через интерфейс FREEKASSA', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('core_level_symbol', '#.#', 'Символ отбивки для корневых разделов в элементе structure');

        $this->addParamToConfig('level_symbol', '#.#', 'Символ отбивки для вложенных разделов в элементе structure');

        $this->addParamToConfig('dontclean_uploadify_table', '0', 'Не очищать таблицу загрузок автоматически', SConfig::$fieldtypeCheckbox);

        /* if ( !$this->check_config_item('use_heaps') ) {
          $this->addParamToConfig('use_heaps','0','Use Heaps');
          } */

        $this->addParamToConfig('apps.realty.update_date_added', 0, 'Обновлять дату добавления на текущую при редактировании объявления в ЛК', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.realty.uniq_params', '', 'Параметры сравнения дублирующихся объявлений', 0);

        $this->addParamToConfig('disable_guest_add', 0, 'Запретить гостевое добавление', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.realty.similar_preg', '', 'Параметры похожих');

        $this->addParamToConfig('apps.realty.similar_grid', 0, 'Формировать массив "Похожие" как стандартный список', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('core.listing.add_user_info', '0', 'Выбирать данные пользователя', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('core.listing.add_user_info_fields', '', 'Список выбираемых данных пользователя');

        $this->addParamToConfig('core.listing.select_query_fields', '', 'Список select_by_query-полей необходимых для подбора', 3);

        /* if ( !$this->check_config_item('apps.realty.updated_at_field_type') ) {
          $this->addParamToConfig('apps.realty.updated_at_field_type','','Тип поля даты обновления (по умолчанию 0=datetime, либо укажите 1=timestamp)');
          }

          if ( !$this->check_config_item('apps.realty.updated_at_field') ) {
          $this->addParamToConfig('apps.realty.updated_at_field','','Системное имя поля даты обновления');
          } */

        $this->addParamToConfig('apps.realty.admin_fast_view', '', 'Набор полей быстрого просмотра');

        $this->addParamToConfig('admin_grid_leftbuttons', 1, 'Размещать кнопки управления слева', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.realty.title_preg', '', 'Формат строки заголовка');

        $this->addParamToConfig('data_adv_share_access', '1', 'Разделять доступ к объявлениям в админке', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('data_adv_share_access_user_list_strict', '0', 'Пользователь при добавлении объявления в админке при включенной опции data_adv_share_access может видеть только себя в списке пользователей', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('data_adv_share_access_can_view_all', '1', 'Разрешить просмотр всех записей (без редактирования и удаления) при включенной опции data_adv_share_access', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('data_adv_share_access_extended', '', 'Список идентификаторов пользователей через запятую, которые видны в списке при редактировании объявления в режиме share_access');

        $this->addParamToConfig('register_passstregth', '0', 'Регистрация: сила пароля (0|1|2|3)');

        $this->addParamToConfig('register_maxpasslength', '32', 'Регистрация: максимальная длина пароля');

        $this->addParamToConfig('register_minpasslength', '5', 'Регистрация: минимальная длина пароля');

        $this->addParamToConfig('add_pagenumber_title_place', '0', 'Куда добавлять количество страниц в заголовке', SConfig::$fieldtypeSelectbox, array(
            'select_data' => array('0'=>'заголовок на странице','1'=>'МЕТА-заголовок','2'=>'во все заголовки')
        ));

        $this->addParamToConfig('apps.realty.use_predeleting', '0', 'Использовать архивирование при удалении для объявлений', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.realty.archived_notactive', '0', 'Архивированные объявления полностью не доступны', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('jpeg_quality', '80', 'Коэффициент качества для JPEG/JPG (от 0 до 100)');

        $this->addParamToConfig('png_quality', '5', 'Степень сжатия для PNG: от 0 (нет сжатия) до 9');

        $this->addParamToConfig('robokassa_koef', '1', 'Коэффициент перевода валюты сайта в RUR');

        $this->addParamToConfig('newuser_registration_shared_groupid', '', 'ID групп, допустимых к выбору пользователем');

        $this->addParamToConfig('newuser_autoregistration_groupid', '', 'ID группы присваиваемой новым автозарегистрированным пользователям');

        $this->addParamToConfig('newuser_registration_groupid', '5', 'ID группы присваиваемой новым зарегистрировавшимся пользователям');

        $this->addParamToConfig('apps.realty.sorts', '', 'Сортировка в сетке объявлений по умолчанию');

        $this->addParamToConfig('add_pagenumber_title', '0', 'Добавлять к заголовку страницы номер текущей страницы', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('is_underconstruction', '0', 'Закрыть сайт', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('is_underconstruction_allowed_ip', '127.0.0.1', 'IP разрешенный для доступа в закрытом режиме');

        $this->addParamToConfig('notify_about_payment', '0', 'Уведомлять администратора о платежах по email', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.watermark.opacity', '50', 'Процент прозрачности наложения водяногознака (от 0 до 100)');

        $this->addParamToConfig('apps.realty.off_system_ajax', '0', 'Off system Ajax', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('disable_mail_additionals', '', 'Mailer: Отключить передачу дополнительных флагов в заголовках письма', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('save_without_watermark', '', 'Сохранять копию изображений без водяного знака', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.realty.preview_smart_resizing', '0', 'Использовать умную подгонку превьюшек', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.realty.data_image_big_height', '600', 'Высота изображения объявления');

        $this->addParamToConfig('apps.realty.data_image_big_width', '800', 'Ширина изображения объявления');

        $this->addParamToConfig('apps.realty.data_image_preview_height', '200', 'Высота превью изображения объявления');

        $this->addParamToConfig('apps.realty.data_image_preview_width', '200', 'Ширина превью изображения объявления');

        $this->addParamToConfig('similar_items_count', '', 'Количество похожих объявлений в просмотре объявления');

        $this->addParamToConfig('block_user_search_forms', '0', 'Блокировать формы поиска пользователя', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('block_user_front_grids', '0', 'Блокировать фронтальные сетки пользователя', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('show_up_icon', '0', 'Админ может поднимать объявления', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('captcha_type', '0', 'Тип капчи', SConfig::$fieldtypeSelectbox, array(
            'select_data' => array('0'=>'стандартная', '2'=>'игнорировать капчу', '3'=>'KCaptcha', '4'=>'reCaptcha')
        ));

        $this->addParamToConfig('show_cattree_left', '1', 'Выводить дерево каталогов слева в списке объявлений', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('ignore_free_from_parameter', '1', 'Игнорировать свободно с', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('disable_root_structure_select', '0', 'Блокировать корневые элементы в селектбоксах структуры', SConfig::$fieldtypeSelectbox, array(
            'select_data' => array('0'=>'не блокировать','1'=>'только верхний уровень','2'=>'все не крайние разделы')
        ));

        $this->addParamToConfig('use_combobox', '0', 'Использовать combobox в элементах select', SConfig::$fieldtypeCheckbox);


        /* vk */
        $this->addParamToConfig('apps.socialauth.vk.enable', '0', 'Включить авторизацию через Вконтакте', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.socialauth.vk.api_key', 'vk api_key', 'VK API_KEY');

        $this->addParamToConfig('apps.socialauth.vk.secret', 'vk secret', 'VK SECRET');

        $this->addParamToConfig('apps.socialauth.vk.redirect_url', 'vk redirect_url', 'vk redirect_url');

        /* fb */
        $this->addParamToConfig('apps.socialauth.fb.enable', '0', 'Включить авторизацию через Facebook', SConfig::$fieldtypeCheckbox);


        $this->addParamToConfig('apps.accountsms.enable', '0', 'Включить кабинет accountsms', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('template.agency.logo', 'logo.gif', 'Шаблон Agency. Файл логотипа.');

        $this->addParamToConfig('apps.registersms.enable', '0', 'Включить регистрацию через SMS', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.newsparser_rbc.portion', '10', 'Количество новостей обрабатываемых за один проход');

        $this->addParamToConfig('apps.yml.delivery', 'true', 'Возможность доставки товара на условиях, которые указываются в партнерском интерфейсе http://partner.market.yandex.ru на странице "редактирование" (true/false).', SConfig::$fieldtypeSelectbox, array(
            'select_data' => array('true'=>'true','false'=>'false')
        ));

        $this->addParamToConfig('apps.yml.pickup', 'false', 'Возможность предварительно заказать товар и забрать его в точке продаж (true/false).', SConfig::$fieldtypeSelectbox, array(
            'select_data' => array('true'=>'true','false'=>'false')
        ));

        $this->addParamToConfig('apps.yml.store', 'false', 'Возможность приобрести товар в точке продаж без предварительного заказа по интернету (true/false).', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.freeorder.notification_email', '', 'E-mail для получения уведомлений о новых заявках через Apps.Freeorder (при отсутствии изпользуется order_email_acceptor)');

        $this->addParamToConfig('apps.yandexrealty_parser.default_user_id', '0', 'ID пользователя по умолчанию. Если 0, то ID пользователя будет браться из таблицы доменов. Если не 0, то в качестве user_id для позиции будет использоваться это значение.');

        $this->addParamToConfig('apps.yandexrealty_parser.default_activity_status', '1', 'Статус активности для добавляемых записей', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.yandexrealty_parser.allow_create_new_category', '1', 'Разрешить создание цепочек категорий в случае отсутствия подходящей', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.yandexrealty_parser.category_for_all', '1000', 'ID категории, которая будет сопоставлена добавляемой записи в случае apps.yandexrealty_parser.allow_create_new_category=0');

        $this->addParamToConfig('apps.sms.max_uses', '0', 'Количество использований SMS-напоминания (0 или ничего - без ограничений)');

        $this->addParamToConfig('apps.realtypro.show_contact.enable', '0', 'Включить показ контактов объявления', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.watermark.enable', '1', 'Включить приложение Apps.WatermarkPrinter', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.watermark.position', 'center', 'Расположение принта', SConfig::$fieldtypeSelectbox, array(
            'select_data' => array('center'=>'центр','top-left'=>'верх-лево','top-right'=>'верх-право','bottom-left'=>'низ-лево','bottom-right'=>'низ-право')
        ));

        $this->addParamToConfig('apps.watermark.offset_top', '5', 'Отступ принта сверху, px');

        $this->addParamToConfig('apps.watermark.offset_bottom', '5', 'Отступ принта снизу, px');

        $this->addParamToConfig('apps.watermark.offset_left', '5', 'Отступ принта слева, px');

        $this->addParamToConfig('apps.watermark.offset_right', '5', 'Отступ принта справа, px');

        $this->addParamToConfig('apps.watermark.preview_enable', '0', 'Добавлять водяной знак на превью-изображении', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.watermark.image_preview', '', 'Название файла для водяного знака для превью-изображений');

        $this->addParamToConfig('apps.watermark.printanywhere', '', 'Наносить водяной знак на всю графику', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.shoplog.enable', '0', 'Включитьп приложение Apps.Shoplog', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.rabota.enable', '0', 'Включить приложение Apps.Rabota', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.shop.current_city_id', '', 'ID текущего города');

        $this->addParamToConfig('apps.shop.mail_title', 'Интернет-магазин', 'Название магазина (будет указано в заголовке писем о заказах)');

        $this->addParamToConfig('apps.yml.local_delivery_cost', '', 'Cтоимость доставки для своего региона');

        $this->addParamToConfig('apps.fasteditor.email_send_password_text', 'Пароль для доступа к редактированию {password}', 'Текст сообщения на почту с паролем для редактирования ( {password} указывает место размещения пароля в тексте сообщения)');

        $this->addParamToConfig('apps.fasteditor.sms_send_password_text_long', 'Ваше объявление бесплатно размещено. Помощь в оформлении недвижимости тел 37-86-86, 89289678686 Пароль для редакции объявления {password}', '(Длинное) Текст sms сообщения с паролем для редактирования ( {password} указывает место размещения пароля в тексте сообщения)');

        $this->addParamToConfig('apps.fasteditor.sms_send_password_text', 'Ваше объявление бесплатно размещено. Помощь в оформлении недвижимости тел 37-86-86, 89289678686 Пароль для редакции объявления {password}', 'Текст sms сообщения с паролем для редактирования ( {password} указывает место размещения пароля в тексте сообщения)');

        $this->addParamToConfig('apps.freeorder.enable', '0', 'Включить Apps.Freeorder', SConfig::$fieldtypeCheckbox);


        $this->addParamToConfig('apps.shopstat.enable', '0', 'Включить Apps.Shopstat', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.orderhistory.enable', '0', 'Включить Apps.Orderhistory', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.sms.apikey', 'XXXXXXXXXXXXYYYYYYYYYYYYZZZZZZZZXXXXXXXXXXXXYYYYYYYYYYYYZZZZZZZZ', 'SMSPilot API ключ. Можно получить по адресу <a target=_blank href=http://www.smspilot.ru/apikey.php>http://www.smspilot.ru/apikey.php</a>');

        $this->addParamToConfig('apps.sms.sender', 'estate.cms', 'Имя отправителя в SMS отправленных через SMSPilot');

        $this->addParamToConfig('apps.fasteditor.enable', '0', 'Включить Apps.FastEditor', SConfig::$fieldtypeCheckbox);


        $this->addParamToConfig('apps.realtybuyorder.enable', '0', 'Включить Realtybuyorder', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.realtybuyorder.text_after_send', 'Ваш заказ принят', 'Текст после заказа через Realtybuyorder');



        $this->addParamToConfig('apps.realtylog.enable', '0', 'Включить Apps.Realtylog', SConfig::$fieldtypeCheckbox);



        $this->addParamToConfig('apps.realtypro.youtube', '1', 'Разрешить youtube-ролики в объявлении', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.yml.shop_name', 'Some Shop', 'Короткое название магазина');

        $this->addParamToConfig('apps.yml.company_name', 'Some Company', 'Полное наименование компании');

        $this->addParamToConfig('apps.yml.shop_platform_name', 'Some CMS', 'Система управления контентом');

        $this->addParamToConfig('apps.yml.shop_platform_version', '1.0', 'Версия CMS');

        $this->addParamToConfig('apps.yml.shop_development_team', 'Some Dev Team', 'Наименование агентства, которое оказывает техническую поддержку интернет-магазину');

        $this->addParamToConfig('apps.yml.shop_development_team_email', 'Some Email', 'Контактный адрес разработчиков CMS');





        $this->addParamToConfig('apps.plan.enable', '0', 'Включить Plan.Apps', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.balcony.enable', '0', 'Включить Balcony.Apps', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.sanuzel.enable', '0', 'Включить Sanuzel.Apps', SConfig::$fieldtypeCheckbox);


        $this->addParamToConfig('apps.watermark.image', 'watermark.gif', 'Название файла изображения для водяного знака, путь до картинок /img/watermark/');

        $this->addParamToConfig('apps.billing.enable', '0', 'Включить Billing.Apps', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.realtyspecial.enable', '0', 'Включить RealtySpecial.Apps', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.realtypro.enable', '0', 'Включить RealtyPro.Apps', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.realty.ajax_region_refresh', '1', 'Ajax - обновление региона', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.realty.ajax_city_refresh', '1', 'Ajax - обновление города', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.realty.ajax_district_refresh', '1', 'Ajax - обновление района', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.realty.ajax_metro_refresh', '1', 'Ajax - обновление метро', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.realty.ajax_street_refresh', '1', 'Ajax - обновление улицы', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.shop.recipients_list', '', 'Магазин. Список уведомляемых получателей при добавлении объявления пользователем');

        $this->addParamToConfig('apps.realtypro.admin.items_per_page', '10', 'Недвижимость. Админка. Количество позиций на странице');

        $this->addParamToConfig('apps.shop.admin.products_per_page', '10', 'Магазин. Количество продуктов на странице в админке');

        $this->addParamToConfig('apps.shop.front.products_per_page', '10', 'Магазин. Количество продуктов на странице в ЛК пользователя');

        //form
        $this->addParamToConfig('country_in_form', '0', 'Выбор страны в форме объявления', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('region_in_form', '0', 'Выбор региона в форме объявления', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('city_in_form', '1', 'Выбор города в форме объявления', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('metro_in_form', '1', 'Выбор метро в форме объявления', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('district_in_form', '1', 'Выбор района в форме объявления', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('street_in_form', '1', 'Выбор улицы в форме объявления', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('optype_in_form', '0', 'Выбор контракта в форме поиска', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('uploader_type', '', 'Тип апплоадера для загрузки картинок. При неуказанном значении по умолчанию используется Uploadify. <a href="http://www.sitebill.ru/uploader-type.html" target="_blank">Что это?</a>', SConfig::$fieldtypeSelectbox, array(
            'select_data' => array('uploadify'=>'uploadify','pluploader'=>'pluploader')
        ));

        $this->addParamToConfig('link_street_to_city', '0', 'Включить привязку улиц к городу', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('user_add_street_enable', '0', 'Пользователи могут добавлять улицы', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('allow_callme_timelimits', '0', 'Добавить возможность указания допустимого для звонка времени', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('allow_additional_stationary_number', '0', 'Добавить дополнительный номер городского телефона', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('allow_additional_mobile_number', '0', 'Добавить дополнительный номер мобильного телефона', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('post_form_agreement_text', 'Я, ознакомлен(а), что данная заявка будет доставлена по всем Агентствам недвижимости которые зарегистрированы на сайте.', 'Текст соглашения после формы');

        $this->addParamToConfig('ajax_form_in_admin', '1', 'Режим ajax в формах администратора', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('ajax_form_in_user', '1', 'Режим ajax в формах личного кабинета', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('menu_type', 'purecss', 'Тип верхнего меню (purecss/slidemenu)', SConfig::$fieldtypeSelectbox, array(
            'select_data' => array('purecss'=>'purecss','slidemenu'=>'slidemenu','megamenu'=>'megamenu')
        ));

        $this->addParamToConfig('allow_tags_search_frontend', '0', 'Разрешить поиск по тэгам во фронтенде', SConfig::$fieldtypeCheckbox);


        ////////////
        $this->addParamToConfig('news_image_big_width', '800', 'Новости - ширина большой картинки');

        $this->addParamToConfig('news_image_big_height', '600', 'Новости - высота большой картинки');

        $this->addParamToConfig('news_image_preview_width', '300', 'Новости - ширина превью картинки');

        $this->addParamToConfig('news_image_preview_height', '300', 'Новости - высота превью картинки');

        ////////////
        $this->addParamToConfig('gallery_image_big_width', '800', 'Галерея - ширина большой картинки');

        $this->addParamToConfig('gallery_image_big_height', '600', 'Галерея - высота большой картинки');

        $this->addParamToConfig('gallery_image_preview_width', '300', 'Галерея - ширина превью картинки');

        $this->addParamToConfig('gallery_image_preview_height', '300', 'Галерея - высота превью картинки');

        $this->addParamToConfig('robokassa_server', 'https://auth.robokassa.ru/Merchant/Index.aspx', 'Адрес службы приема платежей robokassa.ru');

        $this->addParamToConfig('robokassa_login', 'robokassa_login', 'Логин для robokassa.ru');

        $this->addParamToConfig('robokassa_password1', 'robokassa_password1', 'Пароль 1 для robokassa.ru');

        $this->addParamToConfig('robokassa_password2', 'robokassa_password2', 'Пароль 2 для robokassa.ru');

        $this->addParamToConfig('robokassa_testmode', '0', 'Тестовый режим модуля робокассы', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('robokassa_testpassword1', 'robokassa_testpassword1', 'Тестовый пароль 1 для robokassa.ru');

        $this->addParamToConfig('robokassa_testpassword2', 'robokassa_testpassword2', 'Тестовый пароль 2 для robokassa.ru');

        $this->addParamToConfig('use_smtp', '0', 'Отправка почты через smtp. <a href="http://www.sitebill.ru/smtp.html" target="_blank">Что это такое?</a>', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('use_smtp_ssl', '1', 'Использовать SSL при подключении к SMTP', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('smtp1_server', 'smtp.yandex.ru', 'SMTP-сервер для отправки заявок');

        $this->addParamToConfig('smtp1_login', 'rumantic.coder', 'SMTP-login');

        $this->addParamToConfig('smtp1_password', '123456', 'SMTP-password');

        $this->addParamToConfig('smtp1_port', '587', 'SMTP-port');

        $this->addParamToConfig('smtp1_from', 'rumantic.coder@yandex.ru', 'SMTP-от кого <br>(это поле должно соответствовать имени и адресу домена)');

        $this->addParamToConfig('editor1', 'bbeditor', 'WYSIWYG-редактор1');

        $this->addParamToConfig('show_demo_banners', '0', 'Показывать рекламные баннеры sitebill.ru', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('use_topic_publish_status', '1', 'Использовать переключатель активности для категорий', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('use_topic_linker', '0', 'Включить переадресацию категорий <a href="http://wiki.sitebill.ru/index.php?title=Use_topic_linker" target="_blank">?</a>');

        $this->addParamToConfig('email_as_login', '0', 'Использовать email в качестве логина <a href="http://wiki.sitebill.ru/index.php?title=email_as_login" target="_blank">?</a>');

        $this->addParamToConfig('min_payment_sum', '0', 'Минимальная сумма для пополнения счета');

        $this->addParamToConfig('query_cache_enable', '0', 'Включить кэширование SQL-запросов', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('query_cache_time', '60', 'Длительность хранения кэша SQL-запросов в секундах');

        $this->addParamToConfig('apps.contact.phone', '', 'Телефон на сайте');
        $this->addParamToConfig('apps.contact.phone2', '', 'Телефон на сайте (2)');
        $this->addParamToConfig('apps.contact.phone3', '', 'Телефон на сайте (3)');
        $this->addParamToConfig('apps.contact.whatsapp', '', 'Whatsapp на сайте');
        $this->addParamToConfig('apps.contact.whatsapp.text', '', 'Текст в сообщении WhatsApp по-умолчанию');

        $this->addParamToConfig('apps.contact.email', '', 'Email на сайте');

        $this->addParamToConfig('apps.contact.address0', '', 'Адрес на сайте (верх, не обязательно)');
        $this->addParamToConfig('apps.contact.address', '', 'Адрес на сайте');

        $this->addParamToConfig('apps.contact.skype', '', 'Skype на сайте');

        $this->addParamToConfig('apps.contact.ampm', 'пн-пт: 10:00-19:00', 'Режим работы');

        $this->addParamToConfig('use_native_file_name_on_uploadify', '0', 'Сохранять физические названия загруженных файлов', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('ups_price', '0', 'Цена одного поднятия');

        $this->addParamToConfig('vip_cost', '0', 'Цена VIP-объявления за 1 день');

        $this->addParamToConfig('premium_cost', '0', 'Цена Премиум-объявления за 1 день');

        $this->addParamToConfig('bold_cost', '0', 'Цена выделения объявления за 1 день');

        $this->addParamToConfig('vip_rotator_number', '5', 'Количество VIP-объявлений в колонке');

        $this->addParamToConfig('apps_cache_disable', '0', 'Выключить кэш приложений', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('sql_paranoid_mode', '1', 'Режим максимальной безопасности для входных параметров', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('dadata_autocomplete_force', '0', 'Принудительно добавить параметр autocomplete для гео-параметров, если в форме есть опция dadata=1. <a href=http://wiki.sitebill.ru/index.php?title=%D0%A0%D0%B0%D1%81%D0%BF%D0%BE%D0%B7%D0%BD%D0%B0%D0%B2%D0%B0%D0%BD%D0%B8%D0%B5_%D0%B0%D0%B4%D1%80%D0%B5%D1%81%D0%BE%D0%B2_%D1%87%D0%B5%D1%80%D0%B5%D0%B7_dadata.ru>?</a>', SConfig::$fieldtypeCheckbox);

        $this->addParamToConfig('apps.realty.default_frontend_route', '/grid/data', 'Маршрут по-умолчанию для angular-фронтенда', 0, array('public' => true));
        $this->addParamToConfig('apps.realty.enable_guest_mode', '1', 'Включить guest-mode для angular-фронтенда', SConfig::$fieldtypeCheckbox, array('public' => true));
        $this->addParamToConfig('apps.realty.enable_toolbar', '0', 'Включить toolbar для angular-фронтенда', SConfig::$fieldtypeCheckbox, array('public' => true));
        $this->addParamToConfig('apps.realty.enable_navbar', '0', 'Включить navbar для angular-фронтенда', SConfig::$fieldtypeCheckbox, array('public' => true));
        $this->addParamToConfig('apps.realty.show_home_icon', '0', 'Выводить иконку Home для angular-фронтенда', SConfig::$fieldtypeCheckbox, array('public' => true));
        $this->addParamToConfig('apps.realty.search_string_parser.enable', '0', 'Включить разбор поисковой строки', SConfig::$fieldtypeCheckbox, array('public' => true));
        $this->addParamToConfig('apps.realty.min_filter_reset_count', '0', 'Ограничить минимальное значение фильтров для сброса', 0, array('public' => true));
        $this->addParamToConfig(
            'apps.realty.grid.enable_grouping',
            '0',
            'Включить группировку в таблицах',
            SConfig::$fieldtypeCheckbox,
            array('public' => true)
        );
        $this->addParamToConfig(
            'apps.realty.data.disable_edit',
            '0',
            'Запретить обычным пользователям (не админам) редактировать свои объявления',
            SConfig::$fieldtypeCheckbox,
            array('public' => true)
        );

        $this->addParamToConfig(
            'apps.realty.data.global_freeze_default_columns_list',
            '0',
            'Запретить на фронте менять настройки всех таблиц',
            SConfig::$fieldtypeCheckbox,
            array('public' => true)
        );

        $this->addParamToConfig(
            'apps.realty.data.global_disable_refresh_button',
            '0',
            'Запретить на фронте кнопку Обновить таблицу',
            SConfig::$fieldtypeCheckbox,
            array('public' => true)
        );
        $this->addParamToConfig(
            'use_vue',
            '0',
            'Использовать VUE',
            SConfig::$fieldtypeCheckbox,
            array('public' => false)
        );
        $this->addParamToConfig(
            'apps.realty.logo',
            '',
            'Логотип (основной)',
            SConfig::$fieldtypeUploads,
            array('public' => true)
        );
        $this->addParamToConfig(
            'apps.realty.logo-white',
            '',
            'Логотип (светлая версия)',
            SConfig::$fieldtypeUploads,
            array('public' => true)
        );
        $this->addParamToConfig(
            'apps.realty.additional_dropzone_button',
            '0',
            'Дополнительная кнопка загрузки dropzone',
            SConfig::$fieldtypeCheckbox
        );
        $this->addParamToConfig(
            'apps.realty.mobilephone_old_mask',
            '0',
            'Использовать старую маску ввода для mobilephone',
            SConfig::$fieldtypeCheckbox
        );




        //if (!$this->check_config_item('use_metaphone')) {
        //$this->addParamToConfig('use_metaphone', '0', 'Использовать metaphone', 1);
        //}

    }

    private function reloadCheckConfigStructure()
    {
        $redis_cache = RedisCache::getArray('reloadCheckConfigStructure');
        if ( $redis_cache ) {
            self::$check_config_array = $redis_cache;
            return;
        }
        $DBC = DBC::getInstance();
        $query = "select * from " . DB_PREFIX . "_config";
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                self::$check_config_array[$ar['config_key']] = '1';
            }
        }
        RedisCache::setArray('reloadCheckConfigStructure', self::$check_config_array);
    }

}
