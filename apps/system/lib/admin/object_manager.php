<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

use Illuminate\Database\Capsule\Manager as Capsule;
use system\lib\system\cache\RedisCache;
use system\lib\model\ColumnItem;
use table\Http\ViewComposers\TableComposer;
use sharder\lib\sharder;

require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/traits/ObjectExportTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/traits/ObjectAliasTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/traits/ObjectMenuTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/traits/ObjectGridConfigTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/traits/ObjectAvatarTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/traits/ObjectFormTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/traits/ObjectModelInitTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/traits/ObjectShardTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/traits/ObjectNotificationTrait.php';

/**
 * Object manager
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Object_Manager extends SiteBill {

    use ObjectExportTrait;
    use ObjectAliasTrait;
    use ObjectMenuTrait;
    use ObjectGridConfigTrait;
    use ObjectAvatarTrait;
    use ObjectFormTrait;
    use ObjectModelInitTrait;
    use ObjectShardTrait;
    use ObjectNotificationTrait;

    /**
     * Table name
     * @public string
     */
    public $table_name;

    /**
     * Primary key
     * @public string
     */
    public $primary_key;

    /**
     * Action name
     * @public string
     */
    public $action;
    public $mod_name = '';

    /**
     * Data model
     * @public array
     */
    public $data_model;
    protected $imgs = false;
    public $app_title;
    private $new_record_id = false;
    private $total_count = 0;
    protected $redirect_disabled = false;
    public $notwatermarked_folder = SITEBILL_DOCUMENT_ROOT . '/img/nwtm/';
    protected $nowatermark_folder_with_id = false;
    private $grid_params = null;

    /**
     * @var Data_Model
     */
    private $data_model_object;

    /**
     * @var ColumnItem[]
     */
    private $columnItems;

    protected $enable_angular = false;

    private static $tables_list = false;

    function _preload() {
        return false;
    }

    function check_table_exist($table_name) {
        if ( !self::$tables_list ) {
            $this->load_table_list();
        }
        if (is_array(self::$tables_list) && isset(self::$tables_list[$table_name]) && self::$tables_list[$table_name] ) {
            return true;
        }
        if (is_array(self::$tables_list) && self::$tables_list[DB_PREFIX.'_'.$table_name] ) {
            return true;
        }
        return false;
    }

    function load_table_list () {
        $table_list = RedisCache::getArray('table_list');
        if ( $table_list ) {
            self::$tables_list = $table_list;
            return;
        }

        $tables = Capsule::select('SHOW TABLES');
        $var = 'Tables_in_'.DB_BASE;
        foreach ( $tables as $item ) {
            self::$tables_list[$item->$var] = true;
        }
        RedisCache::setArray('table_list', self::$tables_list);
    }

    protected function disable_redirect() {
        $this->redirect_disabled = true;
    }

    protected function isRedirectDisabled() {
        return $this->redirect_disabled;
    }

    public function set_mod($mod_name) {
        $this->mod_name = $mod_name;
    }

    public function set_total_count($total_count) {
        $this->total_count = $total_count;
    }

    public function get_total_count() {
        return $this->total_count;
    }

    protected function _helpAction() {
        return $this->_help();
    }

    protected function _help() {
        return '';
    }

    protected function _installAction() {
        return $this->install();
    }

    function install() {
        return '';
    }

    public function _before_edit_done_action($form_data) {
        return $form_data;
    }

    protected function _before_add_done_action($form_data) {
        return $form_data;
    }

    public function _before_check_action($form_data, $type = 'new') {
        return $form_data;
    }

    protected function _after_edit_done_action($form_data) {
        return $form_data;
    }

    protected function _after_add_done_action($form_data) {
        return $form_data;
    }

    public function rest_new_done() {
        $this->disable_redirect();
        $this->_new_doneAction();
    }

    public function rest_edit_done() {
        $this->disable_redirect();
        $this->_edit_doneAction();
    }

    protected function _edit_doneAction() {
        //init
        //before check
        //checking
        //before edit (if checked)
        //edit (if checked)
        //after edit (if checked)
        $rs = '';
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;

        // Собираем модель и ее данные для свойств не передаваемых запросом
        $partialmodel = null;
        $attacheddata = array($this->primary_key);
        foreach ($form_data[$this->table_name] as $k => $v){
            if(in_array($v['type'], array('uploads', 'docuploads'))){
                $attacheddata[] = $k;
            }
        }

        if(!empty($attacheddata)){
            $partialmodel = $form_data[$this->table_name];
            foreach ($partialmodel as $k => $v){
                if(!in_array($k, $attacheddata)){
                    unset($partialmodel[$k]);
                }
            }
            $partialmodel = $data_model->init_model_data_from_db($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $partialmodel);
        }

        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);



        if(!is_null($partialmodel)){
            foreach ($partialmodel as $k => $v){
                if(isset($form_data[$this->table_name][$k])){
                    $form_data[$this->table_name][$k]['value'] = $partialmodel[$k]['value'];
                }
            }
        }


        $new_values = $this->getRequestValue('_new_value');
        if (1 == $this->getConfigValue('use_combobox') && is_array($new_values) && @count($new_values) > 0) {
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
        $data_model->forse_injected_values($form_data[$this->table_name]);
        $data_model->forse_autocalc_values($form_data[$this->table_name]);
        //$data_model->clear_auto_add_values($form_data[$this->table_name]);
        $form_data[$this->table_name] = $this->_before_check_action($form_data[$this->table_name], 'edit');

        if (!$this->check_data($form_data[$this->table_name]/* , $error_fields */)) {
            $form_data[$this->table_name] = $this->removeTemporaryFields($form_data[$this->table_name], $remove_this_names);
            $rs = $this->get_form($form_data[$this->table_name], 'edit');
        } else {
            //$delete_avatar=$_POST['delete_avatar'];

            $form_data[$this->table_name] = $this->_before_edit_done_action($form_data[$this->table_name]);
            $this->edit_data($form_data[$this->table_name]);
            if ($this->getError()) {
                $form_data[$this->table_name] = $this->removeTemporaryFields($form_data[$this->table_name], $remove_this_names);
                $rs = $this->get_form($form_data[$this->table_name], 'edit');
            } else {
                /* $this->attachAvatars($this->data_model, $this->table_name, $this->primary_key, $form_data[$this->table_name][$this->primary_key]['value']);


                  if(is_array($delete_avatar)){

                  foreach($delete_avatar as $k=>$v){
                  if(isset($this->data_model[$this->table_name][$k]) && $this->data_model[$this->table_name][$k]['type']=='avatar'){
                  $this->clearAvatarElement($this->table_name, $k, $this->primary_key, $form_data[$this->table_name][$this->primary_key]['value']);
                  }
                  }

                  } */

                if ($this->getConfigValue('apps.shoplog.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/shoplog/admin/admin.php';
                    $Logger = new shoplog_admin();
                    $Logger->addLog($form_data[$this->table_name][$this->primary_key]['value'], $_SESSION['user_id_value'], 'edit', $this->table_name);
                }
                if ($this->getConfigValue('apps.realtylogv2.enable') && $this->table_name == 'data') {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                    $Logger = new realtylogv2_admin();
                    $Logger->addLog($form_data[$this->table_name]['id']['value'], $_SESSION['user_id_value'], 'edit', $this->table_name, $this->primary_key);
                }
                //header('location: ?action='.$this->action);
                //exit();
                $rs .= $this->grid();
            }
        }
        $form_data = $this->_after_edit_done_action($form_data);
        return $rs;
    }

    protected function _editAction() {
        $rs = '';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;

        if ($this->getRequestValue('subdo') == 'delete_image') {
            $this->deleteImage($this->table_name, $this->getRequestValue('image_id'));
        }

        if ($this->getRequestValue('subdo') == 'up_image') {
            $this->reorderImage($this->table_name, $this->getRequestValue('image_id'), $this->primary_key, $this->getRequestValue($this->primary_key), 'up');
        }

        if ($this->getRequestValue('subdo') == 'down_image') {
            $this->reorderImage($this->table_name, $this->getRequestValue('image_id'), $this->primary_key, $this->getRequestValue($this->primary_key), 'down');
        }

        if ($this->getRequestValue('language_id') > 0 and ! $this->language->get_version($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $this->getRequestValue('language_id'))) {
            $rs = $this->get_form($form_data[$this->table_name], 'new', $this->getRequestValue('language_id'));
        } else {
            $model_itited = $data_model->init_model_data_from_db($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name]);
            if ($model_itited) {
                if (1 == $this->getConfigValue('apps.language.autotrans_enable')) {
                    $model_itited = $data_model->init_model_data_auto_translate($model_itited);
                }
                $rs = $this->get_form($model_itited, 'edit');
            } else {
                $rs = '';
            }
        }
        return $rs;
    }

    protected function _deleteAction() {
        $rs = '';
        if ($this->getConfigValue('apps.shoplog.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/shoplog/admin/admin.php';
            $Logger = new shoplog_admin();
            $Logger->addLog($this->getRequestValue($this->primary_key), $_SESSION['user_id_value'], 'delete', $this->table_name);
        }
        if ($this->getConfigValue('apps.realtylogv2.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
            $Logger = new realtylogv2_admin();
            $Logger->addLog($this->getRequestValue($this->primary_key), $_SESSION['user_id_value'], 'delete', $this->table_name, $this->primary_key);
        }
        $this->delete_data($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));

        if ($this->getError()) {
            $rs .= '<div align="center">' . Multilanguage::_('L_ERROR_ON_DELETE') . ': ' . $this->GetErrorMessage() . '<br>';
            $rs .= '<a href="?action=' . $this->action . '">ОК</a>';
            $rs .= '</div>';
        } else {
            if ($this->isRedirectDisabled()) {
                return true;
            }

            header('location: ?action=' . $this->action);
            exit();
            $rs .= $this->grid();
        }
        return $rs;
    }

    public function addObject($var_data, $attachments = array()) {
        $rs = '';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        $form_data[$this->table_name] = $data_model->init_model_data_from_var($var_data, 0, $form_data[$this->table_name]);
        $new_values = $var_data['_new_value'];
        if (1 == $this->getConfigValue('use_combobox') && @count($new_values) > 0) {
            $remove_this_names = array();
            foreach ($form_data[$this->table_name] as $fd) {
                if (isset($new_values[$fd['name']]) && $new_values[$fd['name']] != '' && $fd['combo'] == 1) {
                    $id = md5(time() . '_' . random_int(100, 999));
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
        $data_model->forse_injected_values($form_data[$this->table_name]);

        $form_data[$this->table_name] = $this->_before_check_action($form_data[$this->table_name]);
        //var_dump($form_data[$this->table_name]);
        if (!$this->check_data($form_data[$this->table_name]) || (1 == $this->getConfigValue('filter_double_data') && !$this->checkUniquety($form_data[$this->table_name]))) {

            return false;
        } else {
            $form_data[$this->table_name] = $this->_before_add_done_action($form_data[$this->table_name]);
            $new_record_id = $this->add_data($form_data[$this->table_name], 0);
            if ($this->getError()) {
                return false;
            } else {
                $this->new_record_id = $new_record_id;
                if ($this->getConfigValue('apps.shoplog.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/shoplog/admin/admin.php';
                    $Logger = new shoplog_admin();
                    $Logger->addLog($new_record_id, $_SESSION['user_id_value'], 'new', $this->table_name);
                }
                if ($this->getConfigValue('apps.realtylogv2.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                    $Logger = new realtylogv2_admin();
                    $Logger->addLog($new_record_id, $_SESSION['user_id_value'], 'new', $this->table_name, $this->primary_key);
                }
                //header('location: ?action='.$this->action);
                //exit();
                return $new_record_id;
            }
        }
    }

    protected function _new_doneAction() {
        $rs = '';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
        $new_values = $this->getRequestValue('_new_value');
        if (1 == $this->getConfigValue('use_combobox') && is_array($new_values) && @count($new_values) > 0) {
            $remove_this_names = array();
            foreach ($form_data[$this->table_name] as $fd) {
                if (isset($new_values[$fd['name']]) && $new_values[$fd['name']] != '' && $fd['combo'] == 1) {
                    $id = md5(time() . '_' . random_int(100, 999));
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
        $data_model->forse_injected_values($form_data[$this->table_name]);

        $form_data[$this->table_name] = $this->_before_check_action($form_data[$this->table_name]);
        if (!$this->check_data($form_data[$this->table_name]) || (1 == $this->getConfigValue('filter_double_data') && !$this->checkUniquety($form_data[$this->table_name]))) {
            $form_data[$this->table_name] = $this->removeTemporaryFields($form_data[$this->table_name], $remove_this_names);
            $rs = $this->get_form($form_data[$this->table_name], 'new');
        } else {
            $form_data[$this->table_name] = $this->_before_add_done_action($form_data[$this->table_name]);
            $new_record_id = $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
            if ($this->getError()) {
                $form_data[$this->table_name] = $this->removeTemporaryFields($form_data[$this->table_name], $remove_this_names);

                $rs = $this->get_form($form_data[$this->table_name], 'new');
            } else {
                $this->new_record_id = $new_record_id;
                if ($this->getConfigValue('apps.shoplog.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/shoplog/admin/admin.php';
                    $Logger = new shoplog_admin();
                    $Logger->addLog($new_record_id, $_SESSION['user_id_value'], 'new', $this->table_name);
                }
                if ($this->getConfigValue('apps.realtylogv2.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                    $Logger = new realtylogv2_admin();
                    $Logger->addLog($new_record_id, $_SESSION['user_id_value'], 'new', $this->table_name, $this->primary_key);
                }
                //header('location: ?action='.$this->action);
                //exit();
                $rs .= $this->grid();
                $form_data[$this->table_name][$this->primary_key]['value'] = $new_record_id;
                $this->_after_add_done_action($form_data);
            }
        }
        return $rs;
    }

    public function get_new_record_id() {
        return $this->new_record_id;
    }

    public function set_new_record_id($record_id) {
        $this->new_record_id = $record_id;
    }

    protected function _newAction() {
        $rs = '';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        if ( defined('IFRAME_MODE') ) {
            if ( is_array($this->getRequestValue('default_request'))  ) {
                foreach ($this->getRequestValue('default_request') as $key => $value) {
                    $form_data[$this->table_name][$key]['value'] = $value;
                }
            }
        }


        $rs = $this->get_form($form_data[$this->table_name]);
        return $rs;
    }

    protected function _mass_deleteAction() {
        $rs = '';

        $id_array = array();
        $ids = trim($this->getRequestValue('ids'));
        if ($ids != '') {
            $id_array = explode(',', $ids);
        }
        $rs .= $this->mass_delete_data($this->table_name, $this->primary_key, $id_array);
        return $rs;
    }

    protected function _gridAction() {
        $rs = '';
        $rs .= $this->grid();
        return $rs;
    }

    protected function _batch_updateAction() {
        $rs = '';
        $rs .= $this->batch_update($this->table_name, $this->primary_key);
        return $rs;
    }

    protected function _change_paramAction() {
        $rs = '';
        $id_array = array();
        $ids = trim($this->getRequestValue('ids'));
        $param_name = trim($this->getRequestValue('param_name'));
        $param_value = trim($this->getRequestValue('new_param_value'));
        if (isset($form_data[$this->table_name][$param_name]) && $ids != '') {
            $id_array = explode(',', $ids);
            $rs .= $this->mass_change_param($this->table_name, $this->primary_key, $id_array, $param_name, $param_value);
        } else {
            $rs .= $this->_gridAction();
        }
        return $rs;
    }

    protected function _defaultAction() {
        return $this->grid(/*array('url' => 'account/data')*/);
    }

    protected function _structureAction() {
        $rs = '';
        $rs .= $this->structure_processor();
        return $rs;
    }

    /**
     * Main
     * @param void
     * @return string
     */
    function main() {
        //$rs .= '<hr>';
        $do = $this->getRequestValue('do');
        $action = '_' . $do . 'Action';

        if (!method_exists($this, $action)) {
            $action = '_defaultAction';
        }

        $rs_action = $this->$action();

        $rs_top = $this->get_app_title_bar();
        if ( !self::admin3_compatible() ) {
            $rs_top .= '<div class="page-header">'.$this->getTopMenu().'</div>';
        }
        $rs = '<div class="row-fluid">';
        $rs .= '<div class="col-xs-12">';
        $rs .= $rs_top;
        $rs .= $rs_action;
        $rs .= '</div>';
        $rs .= '</div>';

        return $rs;
    }

    function checkUniquety($form_data) {
        return TRUE;
    }

    function mass_delete_data($table_name, $primary_key, $ids) {
        $errors = '';
        if (@count($ids) > 0) {
            foreach ($ids as $id) {
                $this->delete_data($this->table_name, $this->primary_key, $id);
                if ($this->getError()) {
                    $errors .= '<div align="center">' . Multilanguage::_('L_ERROR_ON_DELETE') . ' ID=' . $id . ': ' . $this->GetErrorMessage() . '<br>';
                    $errors .= '</div>';
                    $this->error_message = false;
                }
            }
        }
        $rs = '';
        if ($errors != '') {
            $rs .= $errors . '<div align="center"><a href="?action=' . $this->action . '">ОК</a></div>';
        } else {
            $rs .= $this->grid();
        }
        return $rs;
    }

    function mass_change_param($table_name, $primary_key, $ids, $param_name, $param_value) {
        $errors = '';
        $rs = '';
        if (@count($ids) > 0) {
            $data_model = new Data_Model();
            $form_data = $this->data_model;
            $partial_form_data = array();
            $partial_form_data[$this->table_name][$this->primary_key] = $form_data[$this->table_name][$this->primary_key];
            $partial_form_data[$this->table_name][$param_name] = $form_data[$this->table_name][$param_name];

            /* foreach($form_data[$this->table_name] as $fk=>$fv){
              if($fk!==$this->primary_key || $fk!==$param_name){
              unset($form_data[$this->table_name][$fk]);
              }
              } */
            //$FD=$form_data
            foreach ($ids as $id) {
                $partial_form_data[$this->table_name][$this->primary_key]['value'] = $id;
                $partial_form_data[$this->table_name][$param_name]['value'] = $param_value;
                //print_r($partial_form_data[$this->table_name]);
                if ($this->check_data($partial_form_data[$this->table_name])) {
                    $this->edit_data($partial_form_data[$this->table_name]);
                }
            }
        }
        $rs .= $this->grid();

        return $rs;
    }

    /**
     * Применение декорирования данных к массиву модели
     * прокладочная функция вызова метода Data_Model
     * @param $row_datas массив модели
     * @return mixed декорированный массив модели
     */
    function applyGCompose($row_datas){
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model_object = new Data_Model();
        foreach ($row_datas as $k => $item) {
            $row_datas[$k] = $data_model_object->applyGCompose($item);
        }
        return $row_datas;
    }

    /**
     * Load record by id
     * @param int $record_id
     * @return array
     */
    function load_by_id($record_id) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        if (!isset($this->data_model_object) || !is_object($this->data_model_object)) {
            $this->data_model_object = new Data_Model();
        }

        $form_data = $this->data_model;

        if (is_array($record_id) && !empty($record_id)) {
            $form_data[$this->table_name] = $this->data_model_object->init_model_data_from_db_multi($this->table_name, $this->primary_key, $record_id, $form_data[$this->table_name], TRUE);
            /*foreach ($form_data[$this->table_name] as $k => $v){
                $form_data[$this->table_name][$k] = $this->data_model_object->init_language_values($v);
            }*/
        } elseif ($record_id > 0) {
            $form_data[$this->table_name] = $this->data_model_object->init_model_data_from_db($this->table_name, $this->primary_key, $record_id, $form_data[$this->table_name], TRUE);
        }

        return $form_data[$this->table_name];
        //print_r($form_data[$this->table_name]);
    }

    function get_id_by_filter($field, $value, $filters = array()) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        if (!isset($this->data_model_object) || !is_object($this->data_model_object)) {
            $this->data_model_object = new Data_Model();
        }
        $primary_key_value = $this->data_model_object->get_value_id_by_name($this->table_name, $field, $this->primary_key, $value, $filters);
        return $primary_key_value;
    }

    /**
     * Delete data
     * @param string $table_name
     * @param string $primary_key
     * @param int $primary_key_value
     */
    function delete_data($table_name, $primary_key, $primary_key_value) {
        $model = $this->data_model;
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $model = $data_model->init_model_data_from_db($table_name, $primary_key, $primary_key_value, $model[$table_name]);
        if (!$model) {
            return;
        }
        $uploads = array();
        $docuploads = array();
        $avtars = array();
        $multiitems = array();
        foreach ($model as $model_field) {
            if ($model_field['type'] == 'uploads' && !empty($model_field['value'])) {
                foreach ($model_field['value'] as $upload) {
                    $uploads[] = $upload['preview'];
                    $uploads[] = $upload['normal'];
                    if ( $upload['remote'] === 'true') {
                        $remote_shards[] = $upload['preview'];
                        $remote_shards[] = $upload['normal'];
                    }
                }
            } elseif ($model_field['type'] == 'docuploads' && !empty($model_field['value'])) {
                foreach ($model_field['value'] as $upload) {
                    $docuploads[] = $upload['normal'];
                }
            } elseif ($model_field['type'] == 'avatar' && $model_field['value'] != '') {
                $avtars[] = $model_field['value'];
            } elseif ($model_field['type'] == 'select_by_query_multi') {
                $multiitems[] = $model_field['name'];
            }
        }




        $DBC = DBC::getInstance();
        $query = 'DELETE FROM ' . DB_PREFIX . '_' . $table_name . ' WHERE `' . $primary_key . '` = ?';
        $stmt = $DBC->query($query, array($primary_key_value));
        if (!$stmt) {
            return false;
        }
        if (!empty($uploads)) {
            foreach ($uploads as $upload) {
                @unlink(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $upload);
                @unlink(SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $upload);
                if ($table_name == 'data') {
                    @unlink($this->notwatermarked_folder . $upload);
                }
            }
            if ( $this->getConfigValue('apps.sharder.api_key') or $this->getConfigValue('apps.sharder.s3.enable')  ) {
                if ( !is_object($this->sharder) ) {
                    $this->sharder = new sharder();
                }
                $this->sharder->remove_remote_files($remote_shards, $this->getServerFullUrl(true), $this->is_shard_queue_enable());
            }
        }
        if (!empty($docuploads)) {
            foreach ($docuploads as $upload) {
                @unlink(SITEBILL_DOCUMENT_ROOT . '/img/mediadocs/' . $upload);
            }
        }
        if (!empty($avtars)) {
            foreach ($avtars as $avtar) {
                @unlink(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $avtar);
            }
        }
        if (!empty($multiitems)) {

            $params = array();
            $params[] = $table_name;
            $params = array_merge($params, $multiitems);
            $params[] = $primary_key_value;
            $query = 'DELETE FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name` IN (' . implode(', ', array_fill(0, @count($multiitems), '?')) . ') AND `primary_id`=?';
            $stmt = $DBC->query($query, $params);
        }
        return true;
    }

    protected function angular_grid ( $action = null ) {
        if ( !$action ) {
            $action = $this->action;
        }
        $table_composer = new TableComposer();
        self::$grid_replaced_with_angular = true;
        return $table_composer->render(array(
            'component' => $action,
            'table_name' => $this->table_name,
            'primary_key' => $this->primary_key,
        ));
    }


    /**
     * Grid
     * @param $params - здесь задаем параметры для того чтобы полностью переопределить структуру грида
     * @param $default_params - здесь указываем параметры для вывода колонок по-умолчанию, если нет пользовательских и нет $params тогда рисуем колонки из $default_params
     * @return string
     */
    function grid($params = array(), $default_params = array()) {
        if (!isset($this->table_name)) {
            return '';
        }

        if ( self::$replace_grid_with_angular ) {
            return $this->angular_grid();
        }

        if ( @count($params) == 0 and $this->get_grid_params() != null) {
            $params = $this->get_grid_params();
        }


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);
        $common_grid->set_action($this->action);
        $common_grid->set_grid_table($this->table_name);
        if (@$params['url'] != '') {
            $common_grid->set_grid_url($params['url']);
        }
        if (isset($default_params['render_user_id'])) {
            $common_grid->set_render_user_id($default_params['render_user_id']);
        }

        if (isset($params['grid_item']) && @count($params['grid_item']) > 0) {
            foreach ($params['grid_item'] as $grid_item) {
                $common_grid->add_grid_item($grid_item);
            }
        } else {
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
            } else {
                if (isset($default_params['grid_item']) && @count($default_params['grid_item']) > 0) {
                    foreach ($default_params['grid_item'] as $grid_item) {
                        $common_grid->add_grid_item($grid_item);
                    }
                } else {
                    foreach ( $this->get_default_grid_items() as $g_item ) {
                        $common_grid->add_grid_item($g_item);
                    }
                }
            }
        }

        if (isset($params['grid_controls']) && @count($params['grid_controls']) > 0) {
            foreach ($params['grid_controls'] as $grid_item) {
                $common_grid->add_grid_control($grid_item);
            }
        } else {
            $common_grid->add_grid_control('edit');
            $common_grid->add_grid_control('delete');
        }

        if (isset($params['grid_conditions']) && @count($params['grid_conditions']) > 0) {
            $common_grid->set_conditions($params['grid_conditions']);
        }
        if (isset($params['grid_conditions_sql']) && @count($params['grid_conditions_sql']) > 0) {
            $common_grid->set_conditions_sql($params['grid_conditions_sql']);
        }

        if (@$default_params['batch_update']) {
            $common_grid->enableBatchUpdate();
            $common_grid->setBatchUpdateUrl($default_params['batch_update_url']);
        }


        if (@$default_params['mass_delete'] && @$default_params['mass_delete_url']) {
            //$common_grid->enableBatchUpdate();
            $common_grid->setMAssDeleteUrl($default_params['mass_delete_url']);
        }

        if (@$default_params['batch_activate']) {
            $common_grid->enableBatchActivate();
        }
        //$common_grid->set_grid_query('SELECT * FROM '.DB_PREFIX.'_'.$this->table_name.' ORDER BY name ASC');

        if (isset($default_params['pager_params'])) {
            $pager_params = $default_params['pager_params'];
        } else {
            $pager_params = array();
        }

        /*if(isset($params['page_url'])){
            $pager_params['page_url'] = $params['page_url'];
        }*/
        $pager_params['action'] = $this->action;
        $pager_params['page'] = $this->getRequestValue('page');
        $pager_params['per_page'] = $this->getConfigValue('common_per_page');

        $common_grid->setPagerParams($pager_params);

        $grid_string = $common_grid->construct_grid();
        $rs = $common_grid->extended_items();
        $rs .= $grid_string;
        return $rs;
    }

    function get_default_grid_items () {
        return array($this->primary_key, 'name');
    }

    /**
     * Generate grid array (array version of the grid method)
     * @param $params - здесь задаем параметры для того чтобы полностью переопределить структуру грида
     * @param $default_params - здесь указываем параметры для вывода колонок по-умолчанию, если нет пользовательских и нет $params тогда рисуем колонки из $default_params
     * @return string
     */
    function grid_array($params = array(), $default_params = array()) {
        if (!isset($this->table_name)) {
            return '';
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);
        $common_grid->set_action($this->action);
        $common_grid->set_grid_table($this->table_name);
        if (@$params['url'] != '') {
            $common_grid->set_grid_url($params['url']);
        }
        if (isset($default_params['render_user_id'])) {
            $common_grid->set_render_user_id($default_params['render_user_id']);
        }

        if (isset($params['grid_item']) && @count($params['grid_item']) > 0) {
            foreach ($params['grid_item'] as $grid_item) {
                $common_grid->add_grid_item($grid_item);
            }
        } else {
            $DBC = DBC::getInstance();
            $used_fields = array();
            $query = 'SELECT `grid_fields` FROM ' . DB_PREFIX . '_table_grids WHERE `action_code`=?';
            $stmt = $DBC->query($query, array($this->action));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $used_fields = json_decode($ar['grid_fields']);
                //$this->writeLog(__METHOD__ . ', rows = <pre>' . var_export($used_fields, true) . '</pre>');
            }

            if (!empty($used_fields)) {
                foreach ($used_fields as $uf) {
                    $common_grid->add_grid_item($uf);
                }
            } else {
                if (isset($default_params['grid_item']) && @count($default_params['grid_item']) > 0) {
                    foreach ($default_params['grid_item'] as $grid_item) {
                        $common_grid->add_grid_item($grid_item);
                    }
                } else {
                    $common_grid->add_grid_item($this->primary_key);
                    $common_grid->add_grid_item('name');
                }
            }
        }
        $common_grid->add_grid_item('city_id');
        $common_grid->add_grid_item('street_id');
        $common_grid->add_grid_item('image');

        if (isset($params['grid_controls']) && @count($params['grid_controls']) > 0) {
            foreach ($params['grid_controls'] as $grid_item) {
                $common_grid->add_grid_control($grid_item);
            }
        } else {
            $common_grid->add_grid_control('edit');
            $common_grid->add_grid_control('delete');
        }

        if (isset($params['grid_conditions']) && @count($params['grid_conditions']) > 0) {
            $common_grid->set_conditions($params['grid_conditions']);
        }

        if (isset($params['grid_conditions_sql']) && @count($params['grid_conditions_sql']) > 0) {
            $common_grid->set_conditions_sql($params['grid_conditions_sql']);
        }
        if (isset($params['grid_conditions_left_join']) && @count($params['grid_conditions_left_join']) > 0) {
            $common_grid->set_conditions_left_join($params['grid_conditions_left_join']);
        }


        //$common_grid->set_grid_query('SELECT * FROM '.DB_PREFIX.'_'.$this->table_name.' ORDER BY name ASC');

        if ($params['page'] != '' and $params['per_page'] != '') {
            $common_grid->setPagerParams(array('action' => $this->action, 'page' => $params['page'], 'per_page' => $params['per_page']));
        } else {
            $common_grid->setPagerParams(array('action' => $this->action, 'page' => $this->getRequestValue('page'), 'per_page' => $this->getConfigValue('common_per_page')));
        }

        //$this->writeLog(__METHOD__ . ', yes table = <pre>' . var_export($rows, true) . '</pre>');

        $common_grid->construct_grid();
        $this->set_total_count($common_grid->get_total_count());

        return $common_grid->construct_grid_array();
    }

    /**
     * Add data
     * @param array $form_data form data
     * @param int $language_id
     * @return boolean
     */
    function add_data($form_data, $language_id = 0) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $query_params = $data_model->get_prepared_insert_query(DB_PREFIX . '_' . $this->table_name, $form_data, $language_id);
        $query_params_vals = $query_params['p'];
        $this->writeLog(__METHOD__);
        //$this->writeArrayLog($query_params);

        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query_params['q'], $query_params_vals, $rows, $success);

        if (!$success) {
            $this->riseError($DBC->getLastError());
            return false;
        }
        $new_record_id = $DBC->lastInsertId();

        if ($new_record_id > 0) {
            foreach ($form_data as $form_item) {
                if (@$form_item['type'] == 'uploads') {
                    $imgs_uploads = $this->appendUploads($this->table_name, $form_item, $this->primary_key, $new_record_id);
                    $this->set_imgs($imgs_uploads);
                } elseif (@$form_item['type'] == 'docuploads') {
                    $imgs_uploads = $this->appendDocUploads($this->table_name, $form_item, $this->primary_key, $new_record_id);
                }
            }
            $imgs = $this->editImageMulti($this->action, $this->table_name, $this->primary_key, $new_record_id);

            $this->set_imgs($imgs);

            $this->update_multi_items($new_record_id, $form_data);
        }

        return $new_record_id;
    }

    function update_multi_items ($record_id, $form_data) {
        $DBC = DBC::getInstance();

        $mutiitems = array();
        foreach ($form_data as $k => $form_item) {
            if (@$form_item['type'] == 'select_by_query_multi') {
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
            $params[] = $this->table_name;
            $params = array_merge($params, $keys);
            $params[] = $record_id;
            $query = 'DELETE FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name` IN (' . implode(', ', array_fill(0, @count($keys), '?')) . ') AND `primary_id`=?';
            $stmt = $DBC->query($query, $params);

            $query = 'INSERT INTO ' . DB_PREFIX . '_multiple_field (`table_name`, `field_name`, `primary_id`, `field_value`) VALUES (?,?,?,?)';
            foreach ($mutiitems as $key => $vals) {
                if (!empty($vals)) {
                    foreach ($vals as $val) {
                        $stmt = $DBC->query($query, array($this->table_name, $key, $record_id, $val));
                        //echo $DBC->getLastError();
                    }
                }
            }
            return true;
        }
        return false;
    }

    function set_imgs($imgs = false) {
        if (!empty($imgs) and @count($imgs) > 0) {
            $this->imgs = $imgs;
        }
    }

    function get_imgs() {
        return $this->imgs;
    }

    /**
     * Edit data
     * @param array $form_data form data
     * @param int $language_id language id
     * @return boolean
     */
    function edit_data($form_data, $language_id = 0, $primary_key_value = false) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        if ($primary_key_value) {
            $id = $primary_key_value;
            $query_params = $data_model->get_prepared_edit_query(DB_PREFIX . '_' . $this->table_name, $this->primary_key, $primary_key_value, $form_data, $language_id);
        } else {
            $id = intval($this->getRequestValue($this->primary_key));
            $query_params = $data_model->get_prepared_edit_query(DB_PREFIX . '_' . $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data, $language_id);
        }
        if ($this->need_check_access($this->table_name)) {
            if (!$this->check_access($this->table_name, $this->get_check_access_user_id($this->table_name), 'edit', $this->primary_key, $id)) {
                $this->riseError('ID = ' . $id . ', ' . Multilanguage::_('L_ACCESS_DENIED'));
                return false;
            }
        }


        $query_params_vals = $query_params['p'];



        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query_params['q'], $query_params_vals, $rows, $success);

        if (!$success) {
            $this->riseError($DBC->getLastError());
        }

        /* if(!$stmt){
          return false;
          } */
        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploads') {
                $imgs_uploads = $this->appendUploads($this->table_name, $form_item, $this->primary_key, (int) $this->getRequestValue($this->primary_key));
                $this->set_imgs($imgs_uploads);
            }
        }
        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'docuploads') {
                $imgs_uploads = $this->appendDocUploads($this->table_name, $form_item, $this->primary_key, (int) $this->getRequestValue($this->primary_key));
                //$this->set_imgs($imgs);
            }
        }
        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploadify_image') {
                $imgs = $this->editImageMulti($this->action, $this->table_name, $this->primary_key, (int) $this->getRequestValue($this->primary_key));
                $this->set_imgs($imgs);
            }
        }
        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploadify_file') {
                $imgs = $this->editFileMulti($this->action, $this->table_name, $this->primary_key, (int) $this->getRequestValue($this->primary_key));
                $this->set_imgs($imgs);
            }
        }

        $this->update_multi_items($id, $form_data);

        return $id;
    }

    /**
     * Check data
     * @param array $form_data
     * @return boolean
     */
    function check_data($form_data/* , &$error_fields=array() */) {
        $this->clearError();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        if (!$data_model->check_data($form_data/* , $error_fields */)) {
            $this->riseError($data_model->GetErrorMessage());
            return false;
        }
        return true;
    }

    protected function removeTemporaryFields(&$model, $remove_this_names = array()) {
        if (isset($remove_this_names) && @count($remove_this_names) > 0) {
            foreach ($remove_this_names as $r) {
                unset($model[$r]);
            }
        }
        return $model;
    }

    protected function batch_update($table_name, $primary_key) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);


        foreach ($form_data[$this->table_name] as $key => $value) {
            if ($value['type'] == 'attachment' || $value['type'] == 'photo' || $value['type'] == 'uploadify_image' || $value['type'] == 'uploads' || $value['type'] == 'avatar' || $value['type'] == 'docuploads') {
                unset($form_data[$this->table_name][$key]);
            }
        }
        if (isset($_REQUEST['submit'])) {
            $need_to_update = $this->getRequestValue('batch_update');
            $ids = $this->getRequestValue('batch_ids');
            if ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
                $cuser_id = (int) $_SESSION['user_id_value'];
                if (count($ids) > 0) {
                    foreach ($ids as $k => $id) {
                        if (!$this->checkOwning($id, $cuser_id)) {
                            unset($ids[$k]);
                        }
                    }
                }
            }

            if (count($ids) < 1) {
                return $this->grid();
            }

            if (count($need_to_update) < 1) {
                return $this->grid();
            }

            $sub_form = array();
            foreach ($need_to_update as $key => $value) {
                if (isset($form_data[$this->table_name][$key])) {
                    $sub_form[$this->table_name][$key] = $form_data[$this->table_name][$key];
                }
            }

            if (empty($sub_form)) {
                return $this->grid();
            }

            $sub_form[$this->table_name] = $data_model->init_model_data_from_request($sub_form[$this->table_name]);
            $new_values = $this->getRequestValue('_new_value');
            if (1 == $this->getConfigValue('use_combobox') && count($new_values) > 0) {
                $remove_this_names = array();
                foreach ($sub_form[$this->table_name] as $fd) {
                    if (isset($new_values[$fd['name']]) && $new_values[$fd['name']] != '' && $fd['combo'] == 1) {
                        $id = md5(time() . '_' . rand(100, 999));
                        $remove_this_names[] = $id;
                        $sub_form[$this->table_name][$id]['value'] = $new_values[$fd['name']];
                        $sub_form[$this->table_name][$id]['type'] = 'auto_add_value';
                        $sub_form[$this->table_name][$id]['dbtype'] = 'notable';
                        $sub_form[$this->table_name][$id]['value_table'] = $form_data[$this->table_name][$fd['name']]['primary_key_table'];
                        $sub_form[$this->table_name][$id]['value_primary_key'] = $sub_form[$this->table_name][$fd['name']]['primary_key_name'];
                        $sub_form[$this->table_name][$id]['value_field'] = $sub_form[$this->table_name][$fd['name']]['value_name'];
                        $sub_form[$this->table_name][$id]['assign_to'] = $fd['name'];
                        $sub_form[$this->table_name][$id]['required'] = 'off';
                        $sub_form[$this->table_name][$id]['unique'] = 'off';
                    }
                }
            }
            $data_model->forse_auto_add_values($sub_form[$this->table_name]);
            if (!$this->check_data($sub_form[$this->table_name])) {
                $sub_form['data'] = $this->removeTemporaryFields($sub_form['data'], $remove_this_names);
                $rs = $this->get_batch_update_form($form_data[$this->table_name], $ids, $need_to_update);
            } else {
                foreach ($ids as $id) {
                    $concrete_form = $sub_form;
                    $concrete_form[$this->table_name][$this->primary_key]['value'] = $id;
                    $concrete_form[$this->table_name][$this->primary_key]['type'] = 'primary_key';



                    $this->edit_data($concrete_form[$this->table_name], 0, $id);

                    if ($this->getError()) {
                        //$form_data['data']=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
                        //$rs = $this->get_form($form_data[$this->table_name], 'edit');
                    } else {
                        if ($this->getConfigValue('apps.realtylogv2.enable')) {
                            require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                            $Logger = new realtylogv2_admin();
                            $Logger->addLog($concrete_form[$this->table_name][$this->primary_key]['value'], $_SESSION['user_id_value'], 'edit', $this->table_name, $this->primary_key);
                        }
                    }
                }
                $rs = $this->grid();
            }
        } else {
            $ids = $this->getRequestValue('batch_ids');
            $rs = $this->get_batch_update_form($form_data[$this->table_name], explode(',', $ids));
        }
        return $rs;
    }

    /**
     * Выполняем загрузку дополнительных параметров для формирования запроса SQL
     * из хуков /template/frontend/'.$this->getConfigValue('theme').'/hooks'.'/hooks.php
     * @param type $context контекст объекта
     * @param type $params параметры
     * @return type
     */
    public function onGridConditionsPrepare($context, $params) {
        if (function_exists('onGridConditionsPrepare_hook')) {
            $params = onGridConditionsPrepare_hook($context, $params);
        }
        return $params;
    }

    function prepare_model_for_dadata($form_data) {
        $dadata_enable = false;
        foreach ($form_data as $key => $item_array) {
            if ($item_array['parameters']['dadata'] == 1) {
                $dadata_enable = true;
                break;
            }
        }
        if ($dadata_enable) {
            foreach ($form_data as $key => $item_array) {
                if (in_array($key, array('city_id', 'district_id', 'street_id'))) {
                    $form_data[$key]['parameters']['autocomplete'] = 1;
                }
            }
        }
        return $form_data;
    }

    function set_grid_params ( $params ) {
        $this->grid_params = $params;
    }

    function get_grid_params () {
        if ( $this->grid_params != null ) {
            return $this->grid_params;
        } elseif ( is_array($this->getRequestValue('grid_params')) ) {
            $this->grid_params = $this->getRequestValue('grid_params');
            return $this->grid_params;
        }
        return null;
    }

    function tryHandlers ($model, $do, $form_data, $id) {
        $handlers_register = Sitebill_Registry::get_handlers();
        if ( is_array($handlers_register) and count($handlers_register) > 0 ) {
            foreach ($handlers_register as $handler) {
                if ( class_exists($handler) ) {
                    $new_handler = new $handler();
                    if ( method_exists($new_handler, 'set_context') ) {
                        $new_handler->set_context($this);
                    }
                    $method_name = $model.'__'.$do;
                    if ( method_exists($new_handler, $model.'__'.$do) ) {
                        $new_handler->$method_name($model, $do, $form_data, $id);
                    }
                }
            }
        }
    }

    public function get_data_model_object ():Data_Model {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        if (!isset($this->data_model_object) || !is_object($this->data_model_object)) {
            $this->data_model_object = new Data_Model();
        }
        return $this->data_model_object;
    }

    public function getColumnItem ( $name ): ColumnItem {
        if ( !isset($this->columnItems[$name]) ) {
            if ( !isset($this->data_model[$this->table_name][$name]) ) {
                throw new \Exception("Unknown column $name in table ".$this->table_name);
            }
            $this->columnItems[$name] = new ColumnItem($this->data_model[$this->table_name][$name]);
        }
        return $this->columnItems[$name];
    }

    public function change_data_id($id) {
        try {
            $AUTO_INCREMENT = $this->get_next_autoincrement_value();
            $DBC = DBC::getInstance();
            $query = 'UPDATE '.DB_PREFIX.'_'.$this->table_name.' SET id=? WHERE id=?';
            $stmt = $DBC->query($query, array($AUTO_INCREMENT, $id));
            if ( $stmt ) {
                return $AUTO_INCREMENT;
            }

            throw new \Exception("SQL error: ".$DBC->getLastError());
        } catch (Exception $e) {
            $this->riseError($e->getMessage());
            return false;
        }
    }
    private function get_next_autoincrement_value () {
        $DBC = DBC::getInstance();
        $query = "SELECT AUTO_INCREMENT
                    FROM information_schema.tables
                    WHERE table_name = '".DB_PREFIX."_".$this->table_name."'
                    AND table_schema = DATABASE()";
        $stmt = $DBC->query($query, array());
        if ( $stmt ) {
            $ar = $DBC->fetch($stmt);
            return $ar['AUTO_INCREMENT'];
        }
        throw new \Exception("Cant get AUTO_INCREMENT table ".$this->table_name);
    }

    public function enable_angular () {
        $this->enable_angular = true;
    }

    public function is_angular_enabled () {
        return $this->enable_angular;
    }
}
