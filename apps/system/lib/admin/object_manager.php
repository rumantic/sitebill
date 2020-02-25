<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Object manager
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Object_Manager extends SiteBill {

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
    private $nowatermark_folder_with_id = false;
    private $grid_params = null;

    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
    }

    function _preload() {
        return false;
    }

    function check_table_exist($table_name) {
        $query = 'SHOW TABLES LIKE ?';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array(DB_PREFIX . '_' . $table_name));
        if (!$stmt) {
            return false;
        }
        return true;
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

                if ($this->getConfigValue('apps.realtylog.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/admin/admin.php';
                    $Logger = new realtylog_admin();
                    $Logger->addLog($form_data[$this->table_name]['id']['value'], $_SESSION['user_id_value'], 'edit', $this->table_name);
                }
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
        return $rs;
    }

    protected function attachAvatars($model, $table, $key_name, $key_val) {
        foreach ($model[$table] as $k => $v) {
            if ($v['type'] == 'avatar' && isset($_FILES[$k]) && $_FILES[$k]['error'] == 0) {

                $parameters = $v['parameters'];

                if (isset($parameters['width']) && (int) $parameters['width'] != 0) {
                    $width = (int) $parameters['width'];
                } else {
                    $width = 250;
                }

                if (isset($parameters['height']) && (int) $parameters['height'] != 0) {
                    $height = (int) $parameters['height'];
                } else {
                    $height = 150;
                }

                /* if(isset($parameters['mode']) && (int)$parameters['height']!=0){
                  $height=(int)$parameters['height'];
                  }else{
                  $height=150;
                  } */

                if (!in_array($_FILES[$k]['type'], array('image/jpeg', 'image/pjpeg', 'image/gif', 'image/png'))) {
                    
                } else {
                    $fprts = explode('.', $_FILES[$k]['name']);
                    $ext = strtolower(end($fprts));
                    $name = md5(time() . rand(10, 99)) . '.' . $ext;

                    if (!move_uploaded_file($_FILES[$k]['tmp_name'], SITEBILL_DOCUMENT_ROOT . '/img/data/' . $name)) {
                        
                    } else {
                        $res = $this->makePreview(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $name, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $name, $width, $height, $ext, 'f');
                        if ($res !== false) {
                            $DBC = DBC::getInstance();
                            $query = 'UPDATE ' . DB_PREFIX . '_' . $table . ' SET `' . $k . '`=? WHERE `' . $key_name . '`=?';
                            $stmt = $DBC->query($query, array($name, $key_val));
                        }
                    }
                }
            }
        }
    }

    protected function clearAvatarElement($table, $el, $key_name, $key_val) {
        $DBC = DBC::getInstance();
        $query = 'SELECT `' . $el . '` FROM ' . DB_PREFIX . '_' . $table . ' WHERE `' . $key_name . '`=?';
        $stmt = $DBC->query($query, array($key_val));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $fname = $ar[$el];
            @unlink(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $fname);
            $query = 'UPDATE ' . DB_PREFIX . '_' . $table . ' SET `' . $el . '`=? WHERE `' . $key_name . '`=?';
            $stmt = $DBC->query($query, array('', $key_val));
        }
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
            if ($this->getRequestValue('language_id') > 0) {
                $model_itited = $data_model->init_model_data_from_db_language($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name], false, $this->getRequestValue('language_id'));
                if ($model_itited) {
                    $rs = $this->get_form($model_itited, 'edit');
                } else {
                    $rs = '';
                }
                //$form_data[$this->table_name] = $data_model->init_model_data_from_db_language ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name], false, $this->getRequestValue('language_id') );
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
            //$rs = $this->get_form($form_data[$this->table_name], 'edit');
        }
        return $rs;
    }

    protected function _deleteAction() {
        $rs = '';
        if ($this->getConfigValue('apps.realtylog.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/admin/admin.php';
            $Logger = new realtylog_admin();
            $Logger->addLog($this->getRequestValue($this->primary_key), $_SESSION['user_id_value'], 'delete', $this->table_name);
        }
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
        $data_model->forse_injected_values($form_data[$this->table_name]);

        $form_data[$this->table_name] = $this->_before_check_action($form_data[$this->table_name]);
        //var_dump($form_data[$this->table_name]);
        if (!$this->check_data($form_data[$this->table_name]) || (1 == $this->getConfigValue('filter_double_data') && !$this->checkUniquety($form_data[$this->table_name]))) {

            return false;
        } else {
            $form_data[$this->table_name] = $this->_before_add_done_action($form_data[$this->table_name]);
            $new_record_id = $this->add_data($form_data[$this->table_name], 0);
            print_r($this->getError());
            if ($this->getError()) {

                return false;
            } else {
                $this->new_record_id = $new_record_id;
                if ($this->getConfigValue('apps.realtylog.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/admin/admin.php';
                    $Logger = new realtylog_admin();
                    $Logger->addLog($new_record_id, $_SESSION['user_id_value'], 'new', $this->table_name);
                }
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

                //$this->attachAvatars($this->data_model, $this->table_name, $this->primary_key, $new_record_id);
                if ($this->getConfigValue('apps.realtylog.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/admin/admin.php';
                    $Logger = new realtylog_admin();
                    $Logger->addLog($new_record_id, $_SESSION['user_id_value'], 'new', $this->table_name);
                }
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
            }
        }
        return $rs;
    }

    public function get_new_record_id() {
        return $this->new_record_id;
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
        //$rs = $this->getTopMenu();
        $rs = $this->grid();
        return $rs;
    }

    protected function _formatgridAction() {

        global $smarty;
        $DBC = DBC::getInstance();
        $action = $this->action;
        if (post === strtolower($_SERVER['REQUEST_METHOD'])) {
            $fields = $_POST['field'];
            if (count($fields) > 0) {
                $query = 'INSERT INTO ' . DB_PREFIX . '_table_grids (`action_code`, `grid_fields`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `grid_fields`=?';
                $stmt = $DBC->query($query, array($action, json_encode($fields), json_encode($fields)));
            } else {
                $query = 'DELETE FROM ' . DB_PREFIX . '_table_grids WHERE `action_code`=?';
                $stmt = $DBC->query($query, array($action));
            }
        } else {
            
        }

        $used_fields = array();
        $query = 'SELECT `grid_fields` FROM ' . DB_PREFIX . '_table_grids WHERE `action_code`=?';
        $stmt = $DBC->query($query, array($action));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $used_fields = json_decode($ar['grid_fields']);
        }

        $model_fields = $this->data_model[$this->table_name];
        $model_fields_resorted = array();

        if (!empty($used_fields)) {
            foreach ($used_fields as $uf) {
                $model_fields_resorted[$uf] = $model_fields[$uf];
                unset($model_fields[$uf]);
            }
            foreach ($model_fields as $k => $uf) {
                $model_fields_resorted[$k] = $model_fields[$k];
            }

            $model_fields = $model_fields_resorted;
        }

        $smarty->assign('used_fields', $used_fields);

        if ($this->save_url == 'empty') {
            $smarty->assign('save_url', '');
        } else {
            $smarty->assign('save_url', SITEBILL_MAIN_URL . '/admin/index.php?action=' . $this->action . '&do=formatgrid');
        }
        $smarty->assign('model_fields', $model_fields);
        $ret = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/system/template/grid/grid_fields_managing.tpl');
        return $ret;
    }

    protected function _structureAction() {
        $rs = '';
        $rs .= $this->structure_processor();
        return $rs;
    }

    protected function _importAction() {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/components/dropzone_xls/dropzone.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $dropzone = new DropZone();
        $dropzone->set_context($this);
        //$form_generator = new Form_Generator();
        //$uploads_item = $form_generator->compile_uploads_element($item_array);
        $this->template->assign('uploads_item', $dropzone->compile_uploads_element($item_array));
        //$this->template->assign('dropzone', $dropzone->getDropzonePlugin($this->get_session_key()));

        return $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/system/template/object/import_form.tpl');
    }

    protected function _exportAction($input_params = array()) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);
        $common_grid->set_action($this->action);
        $common_grid->set_grid_table($this->table_name);

        $_model = $this->data_model[$this->table_name];
        $params['grid_item'] = array_keys($_model);

        if (isset($params['grid_item']) && count($params['grid_item']) > 0) {
            foreach ($params['grid_item'] as $grid_item) {
                $common_grid->add_grid_item($grid_item);
            }
        } else {
            $common_grid->add_grid_item($this->primary_key);
            $common_grid->add_grid_item('name');
        }

        if (isset($params['grid_controls']) && count($params['grid_controls']) > 0) {
            foreach ($params['grid_controls'] as $grid_item) {
                $common_grid->add_grid_control($grid_item);
            }
        } else {
            $common_grid->add_grid_control('edit');
            $common_grid->add_grid_control('delete');
        }

        if (isset($input_params['grid_conditions']) && count($input_params['grid_conditions']) > 0) {
            $common_grid->set_conditions($input_params['grid_conditions']);
        }
        if (isset($params['grid_conditions_sql']) && count($params['grid_conditions_sql']) > 0) {
            $common_grid->set_conditions_sql($params['grid_conditions_sql']);
        }

        if ($input_params['per_page'] != '') {
            $per_page = $input_params['per_page'];
        } else {
            $per_page = 99999;
        }
        $common_grid->setPagerParams(array('action' => $this->action, 'page' => 1, 'per_page' => $per_page));

        $common_grid->construct_query();


        $exported_template_fields = $this->getRequestValue('template_fields');
        //$exported_fields = array(0=>'country_id', 1=>'name');
        if (is_array($exported_template_fields) && count($exported_template_fields) > 0) {
            $exported_fields = array_keys($exported_template_fields);
        } else {
            $exported_fields = array_keys($_model);
        }
        if (in_array('tlocation', $exported_fields)) {
            foreach ($exported_fields as $k => $ef) {
                if ($ef == 'tlocation') {
                    unset($exported_fields[$k]);
                    $exported_fields[] = 'country_id';
                    $exported_fields[] = 'region_id';
                    $exported_fields[] = 'city_id';
                    $exported_fields[] = 'district_id';
                    $exported_fields[] = 'street_id';
                    $_model = $this->data_manager_export->get_model(true);
                    break;
                }
            }
        }

        $cycle_per_page = intval($this->getRequestValue('per_page'));
        $current_page = 0;

        $query_count = $query;

        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query_count);
        //echo $query_count;
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
        }
        $cycle_total = $ar['total'];
        $cycle_total = 1;


        for ($i = 0; $i <= $cycle_total; $i += $cycle_per_page) {
            $current_page++;


            $data_a = $common_grid->construct_grid_array();
            /*
              echo '<pre>';
              print_r($data_a);
              echo '</pre>';
              exit;
             */

            $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $styleArray = array(
                'font' => array(
                    'bold' => true,
                ),
                'alignment' => array(
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                ),
                'borders' => array(
                    'bottom' => array(
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => array(
                            'rgb' => '808080'
                        )
                    ),
                ),
                'fill' => array(
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'rotation' => 90,
                    'color' => array(
                        'rgb' => 'c5c5c5',
                    )
                ),
            );

            $last_letter = $this->num2alpha(count($exported_fields) - 1);

            $objPHPExcel->getActiveSheet()->getStyle('A1:' . $last_letter . '1')->applyFromArray($styleArray);

            $column = 1;

            foreach ($exported_fields as $ef) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, 1, SiteBill::iconv(SITE_ENCODING, 'utf-8', $_model[$ef]['title']));

                $objPHPExcel->getActiveSheet()->getColumnDimension($this->num2alpha($column))->setAutoSize(true);

                $column++;
            }
            $column = 1;

            foreach ($data_a as $item_id => $data_item_a) {
                $row = $item_id + 2;
                $column = 1;
                foreach ($data_item_a as $key => $value) {
                    if (is_array($value)) {
                        $value = $value['value_string'];
                    }
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, SiteBill::iconv(SITE_ENCODING, 'utf-8', $value));
                    $column++;
                }
            }

            $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objPHPExcel);
            $xlsx_file_name = $this->action . "_page" . $current_page . ".xlsx";
            $xlsx_output_file = SITEBILL_DOCUMENT_ROOT . "/cache/upl/" . $xlsx_file_name;
            $objWriter->save($xlsx_output_file);

            $handle = fopen($xlsx_output_file, "r");
            $contents = fread($handle, filesize($xlsx_output_file));
            fclose($handle);
            if ($cycle_per_page == 0) {
                header("Content-type: application/octet-stream");
                header("Content-disposition: attachment; filename=" . $xlsx_file_name . "");
                echo $contents;
                exit;
            } else {
                $rs .= '<a href="' . SITEBILL_MAIN_URL . '/cache/upl/' . $xlsx_file_name . '" download="' . $xlsx_file_name . '">' . $xlsx_file_name . '</a><br>';
            }
        }


        //$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
        $rsr = '<h3>Скачать готовые файлы</h3><br/>' . $rs . '';

        return $rsr;

        exit;
    }

    function num2alpha($n) {
        for ($r = ""; $n >= 0; $n = intval($n / 26) - 1)
            $r = chr($n % 26 + 0x41) . $r;
        return $r;
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

        $rs = $this->$action();

        $rs_new = $this->get_app_title_bar();
        $rs_new .= $this->getTopMenu();
        $rs_new .= $rs;

        return $rs_new;
    }

    function checkUniquety($form_data) {
        return TRUE;
    }

    function get_app_title_bar() {
        $breadcrumbs = array();
        $breadcrumbs[] = array('href' => '#', 'title' => Multilanguage::_('L_ADMIN_MENU_APPLICATIONS'));

        if (!empty($this->app_title)) {
            $breadcrumbs[] = array('href' => '?action=' . $this->action . '', 'title' => $this->app_title);
        } else {
            $breadcrumbs[] = array('href' => '?action=' . $this->action . '', 'title' => $this->action);
        }
        $help_link = '<a href="' . SITEBILL_MAIN_URL . '?action=' . $this->action . '&do=help">Help</a>';
        $this->template->assign('help_link', $help_link);
        $this->template->assign('breadcrumbs_array', $breadcrumbs);
        return '';

        /*
          $rs = '<div class="breadcrumbs" id="breadcrumbs">';
          $rs .= '<ul class="breadcrumb">';
          $rs .= '<li>'.Multilanguage::_('L_ADMIN_MENU_APPLICATIONS').' <span class="divider">/</span> ';
          if ( !empty($this->app_title) ) {
          $rs .= '<a href="?action='.$this->action.'">'.$this->app_title.'</a>';
          } else {
          $rs .= '<a href="?action='.$this->action.'">'.$this->action.'</a>';
          }
          $rs .= '</li>';
          $rs .= '</ul>';
          $rs .= '<div class="clear"></div>';
          $rs .= '</div>';

          return $rs;
         */
    }

    function mass_delete_data($table_name, $primary_key, $ids) {
        $errors = '';
        if (count($ids) > 0) {
            foreach ($ids as $id) {
                $this->delete_data($this->table_name, $this->primary_key, $id);
                if ($this->getError()) {
                    $errors .= '<div align="center">' . Multilanguage::_('L_ERROR_ON_DELETE') . ' ID=' . $id . ': ' . $this->GetErrorMessage() . '<br>';
                    $errors .= '</div>';
                    $this->error_message = false;
                }
            }
        }
        if ($errors != '') {
            $rs .= $errors . '<div align="center"><a href="?action=' . $this->action . '">ОК</a></div>';
        } else {
            $rs .= $this->grid($user_id);
        }
        return $rs;
    }

    function mass_change_param($table_name, $primary_key, $ids, $param_name, $param_value) {
        $errors = '';
        if (count($ids) > 0) {
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
                    if ( $upload['remote'] == 'true') {
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
            if ( $this->getConfigValue('apps.sharder.enable') ) {
                if ( !is_object($this->sharder) ) {
                    $this->sharder = new \sharder\lib\sharder();
                }
                $this->sharder->remove_remote_files($remote_shards, $this->getServerFullUrl(true));
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
            $query = 'DELETE FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name` IN (' . implode(', ', array_fill(0, count($multiitems), '?')) . ') AND `primary_id`=?';
            $stmt = $DBC->query($query, $params);
        }
        return true;
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
        if ( count($params) == 0 and $this->get_grid_params() != null) {
            $params = $this->get_grid_params();
        }


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);
        $common_grid->set_action($this->action);
        $common_grid->set_grid_table($this->table_name);
        if ($params['url'] != '') {
            $common_grid->set_grid_url($params['url']);
        }
        if (isset($default_params['render_user_id'])) {
            $common_grid->set_render_user_id($default_params['render_user_id']);
        }

        if (isset($params['grid_item']) && count($params['grid_item']) > 0) {
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
                if (isset($default_params['grid_item']) && count($default_params['grid_item']) > 0) {
                    foreach ($default_params['grid_item'] as $grid_item) {
                        $common_grid->add_grid_item($grid_item);
                    }
                } else {
                    $common_grid->add_grid_item($this->primary_key);
                    $common_grid->add_grid_item('name');
                }
            }
        }

        if (isset($params['grid_controls']) && count($params['grid_controls']) > 0) {
            foreach ($params['grid_controls'] as $grid_item) {
                $common_grid->add_grid_control($grid_item);
            }
        } else {
            $common_grid->add_grid_control('edit');
            $common_grid->add_grid_control('delete');
        }

        if (isset($params['grid_conditions']) && count($params['grid_conditions']) > 0) {
            $common_grid->set_conditions($params['grid_conditions']);
        }
        if (isset($params['grid_conditions_sql']) && count($params['grid_conditions_sql']) > 0) {
            $common_grid->set_conditions_sql($params['grid_conditions_sql']);
        }

        if ($default_params['batch_update']) {
            $common_grid->enableBatchUpdate();
            $common_grid->setBatchUpdateUrl($default_params['batch_update_url']);
        }
        
        
        if ($default_params['mass_delete'] && $default_params['mass_delete_url']) {
            //$common_grid->enableBatchUpdate();
            $common_grid->setMAssDeleteUrl($default_params['mass_delete_url']);
        }
        
        if ($default_params['batch_activate']) {
            $common_grid->enableBatchActivate();
        }
        //$common_grid->set_grid_query('SELECT * FROM '.DB_PREFIX.'_'.$this->table_name.' ORDER BY name ASC');

        if (isset($default_params['pager_params'])) {
            $pager_params = $default_params['pager_params'];
        } else {
            $pager_params = array();
        }

        $pager_params['action'] = $this->action;
        $pager_params['page'] = $this->getRequestValue('page');
        $pager_params['per_page'] = $this->getConfigValue('common_per_page');

        $common_grid->setPagerParams($pager_params);

        $grid_string = $common_grid->construct_grid();
        $rs = $common_grid->extended_items();
        $rs .= $grid_string;
        return $rs;
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
        if ($params['url'] != '') {
            $common_grid->set_grid_url($params['url']);
        }
        if (isset($default_params['render_user_id'])) {
            $common_grid->set_render_user_id($default_params['render_user_id']);
        }

        if (isset($params['grid_item']) && count($params['grid_item']) > 0) {
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
                if (isset($default_params['grid_item']) && count($default_params['grid_item']) > 0) {
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

        if (isset($params['grid_controls']) && count($params['grid_controls']) > 0) {
            foreach ($params['grid_controls'] as $grid_item) {
                $common_grid->add_grid_control($grid_item);
            }
        } else {
            $common_grid->add_grid_control('edit');
            $common_grid->add_grid_control('delete');
        }

        if (isset($params['grid_conditions']) && count($params['grid_conditions']) > 0) {
            $common_grid->set_conditions($params['grid_conditions']);
        }

        if (isset($params['grid_conditions_sql']) && count($params['grid_conditions_sql']) > 0) {
            $common_grid->set_conditions_sql($params['grid_conditions_sql']);
        }
        if (isset($params['grid_conditions_left_join']) && count($params['grid_conditions_left_join']) > 0) {
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
        $this->writeArrayLog($query_params);

        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query_params['q'], $query_params_vals, $rows, $success);

        if (!$success) {
            $this->riseError($DBC->getLastError());
            return false;
        }
        $new_record_id = $DBC->lastInsertId();

        if ($new_record_id > 0) {
            foreach ($form_data as $form_item) {
                if ($form_item['type'] == 'uploads') {
                    $imgs_uploads = $this->appendUploads($this->table_name, $form_item, $this->primary_key, $new_record_id);
                    $this->set_imgs($imgs_uploads);
                } elseif ($form_item['type'] == 'docuploads') {
                    $imgs_uploads = $this->appendDocUploads($this->table_name, $form_item, $this->primary_key, $new_record_id);
                }
            }
            $imgs = $this->editImageMulti($this->action, $this->table_name, $this->primary_key, $new_record_id);

            $this->set_imgs($imgs);

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
                $params[] = $this->table_name;
                $params = array_merge($params, $keys);
                $params[] = $new_record_id;
                $query = 'DELETE FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name` IN (' . implode(', ', array_fill(0, count($keys), '?')) . ') AND `primary_id`=?';
                $stmt = $DBC->query($query, $params);

                $query = 'INSERT INTO ' . DB_PREFIX . '_multiple_field (`table_name`, `field_name`, `primary_id`, `field_value`) VALUES (?,?,?,?)';
                foreach ($mutiitems as $key => $vals) {
                    if (!empty($vals)) {
                        foreach ($vals as $val) {
                            $stmt = $DBC->query($query, array($this->table_name, $key, $new_record_id, $val));
                            //echo $DBC->getLastError();
                        }
                    }
                }
            }
        }

        return $new_record_id;
    }

    function set_imgs($imgs = false) {
        if (!empty($imgs) and count($imgs) > 0) {
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
            $params[] = $this->table_name;
            $params = array_merge($params, $keys);
            $params[] = $id;
            $query = 'DELETE FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name` IN (' . implode(', ', array_fill(0, count($keys), '?')) . ') AND `primary_id`=?';
            $stmt = $DBC->query($query, $params);

            $query = 'INSERT INTO ' . DB_PREFIX . '_multiple_field (`table_name`, `field_name`, `primary_id`, `field_value`) VALUES (?,?,?,?)';
            foreach ($mutiitems as $key => $vals) {
                if (!empty($vals)) {
                    foreach ($vals as $val) {
                        $stmt = $DBC->query($query, array($this->table_name, $key, $id, $val));
                        //echo $DBC->getLastError();
                    }
                }
            }
        }
        /*
          if(!$success){
          return false;
          } */
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

    /**
     * Get top menu
     * @param void 
     * @return string
     */
    function getTopMenu() {
        $rs = '';
        $rs .= '<a href="?action=' . $this->action . '&do=new" class="btn btn-primary">' . Multilanguage::_('L_ADD_RECORD_BUTTON') . '</a> ';
        //$rs .= '</div>';
        //$rs .= '<form method="post"><input type="hidden" name="action" value="add" /><input type="submit" name="submit" value="Добавить объявление" /></form>';
        return $rs;
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
        if ( defined('IFRAME_MODE') ) {
            $action = '?';
        }


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
        $rs .= '<form method="post" class="form-horizontal" action="' . $action . '" enctype="multipart/form-data">';

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

        $el['form_header'] = $rs;
        $el['form_header_action'] = $action;
        $el['form_header_class'] = 'form-horizontal';
        $el['form_header_enctype'] = 'multipart/form-data';
        $el['form_footer'] = '</form>';

        /* if ( $do != 'new' ) {
          $el['controls']['apply']=array('html'=>'<button id="apply_changes" class="btn btn-info">'.Multilanguage::_('L_TEXT_APPLY').'</button>');
          } */
        $el['controls']['submit'] = array('html' => '<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">' . $button_title . '</button>');

        if ($this->getConfigValue('post_form_agreement_enable') == 1 && ADMIN_MODE != 1) {
            $el['agreement_block'] = $form_generator->getAgreementFormBlock();
        }

        $smarty->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl';
        } else {
            $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
        }
        return $smarty->fetch($tpl_name);
    }

    /**
     * Set apps template
     * @param string $apps_name
     * @param string $theme
     * @param string $template_key
     * @param string $template_value
     * @return boolean
     */
    function set_apps_template($apps_name, $theme, $template_key, $template_value) {
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/apps/' . $apps_name . '/site/template/' . $template_value)) {
            $this->template->assert($template_key, SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/apps/' . $apps_name . '/site/template/' . $template_value);
        } elseif (!file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/' . $apps_name . '/' . $template_value)) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/' . $apps_name . '/site/template/' . $template_value)) {
                $this->template->assert($template_key, SITEBILL_DOCUMENT_ROOT . '/apps/' . $apps_name . '/site/template/' . $template_value);
            } else {
                echo sprintf(Multilanguage::_('L_FILE_NOT_FOUND'), SITEBILL_DOCUMENT_ROOT . '/apps/' . $apps_name . '/site/template/' . $template_value);
                exit;
            }
        } else {
            $this->template->assert($template_key, SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/' . $apps_name . '/' . $template_value);
        }
    }

    function get_apps_template($apps_name, $theme, $template_key, $template_value) {
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/apps/' . $apps_name . '/site/template/' . $template_value)) {
            return SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/apps/' . $apps_name . '/site/template/' . $template_value;
        } elseif (!file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/' . $apps_name . '/' . $template_value)) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/' . $apps_name . '/site/template/' . $template_value)) {
                return SITEBILL_DOCUMENT_ROOT . '/apps/' . $apps_name . '/site/template/' . $template_value;
            } else {
                return '';
            }
        } else {
            return SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/' . $apps_name . '/' . $template_value;
        }
    }

    function getSteps($form_data, $step) {

        $default_tab_name = $this->getConfigValue('default_tab_name');
        $tabs = array($default_tab_name);

        foreach ($form_data as $item_id => $item_array) {
            if (isset($item_array['tab']) && $item_array['tab'] != '') {
                $tabs[$item_array['tab']] = $item_array['tab'];
            }
        }
        $tabs_array = array();
        $i = 1;
        foreach ($tabs as $t) {
            if ($i < $step) {
                $tabs_array[$i] = array('name' => $t, 'step' => $i, 'status' => 'done');
            } elseif ($i == $step) {
                $tabs_array[$i] = array('name' => $t, 'step' => $i, 'status' => 'current');
            } else {
                $tabs_array[$i] = array('name' => $t, 'step' => $i, 'status' => 'further');
            }
            $i++;
        }
        return $tabs_array;
    }

    protected function createTranslitAliasByFields($id, $fields_for_alias) {
        $alias = '';
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data_shared = $data_model->get_kvartira_model(false, true);

        $form_data_shared = $data_model->init_model_data_from_db('data', 'id', $id, $form_data_shared['data'], true);
        $values = array();
        foreach ($fields_for_alias as $v) {
            $key = trim($v);
            if (isset($form_data_shared[$key])) {
                if (($form_data_shared[$key]['type'] == 'select_box_structure' || $form_data_shared[$key]['type'] == 'select_by_query' || $form_data_shared[$key]['type'] == 'select_box') && $form_data_shared[trim($v)]['value_string'] != '') {
                    $values[] = $form_data_shared[trim($v)]['value_string'];
                } elseif ($form_data_shared[trim($v)]['value'] != '') {
                    $values[] = $form_data_shared[trim($v)]['value'];
                }
            }
        }
        if (!empty($values)) {
            foreach ($values as $k => $v) {
                $values[$k] = $this->transliteMe($v);
            }
            $alias = implode('-', $values);
        }

        return $alias;
    }

    protected function makeUniqueAlias($alias, $id) {
        $is_similar_alias_exists = false;
        $DBC = DBC::getInstance();
        $query = "SELECT COUNT(*) AS cnt FROM " . DB_PREFIX . "_data WHERE translit_alias=? AND id<>? ORDER BY translit_alias DESC LIMIT 1";
        $stmt = $DBC->query($query, array($alias, $id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ((int) $ar['cnt'] > 0) {
                $is_similar_alias_exists = true;
            }
        }

        if ($is_similar_alias_exists) {
            $is_alias_cathed = false;
            $query = "SELECT translit_alias FROM " . DB_PREFIX . "_data WHERE translit_alias LIKE '" . $alias . "%' AND id<>? ORDER BY LENGTH(translit_alias) DESC, translit_alias DESC";
            $stmt = $DBC->query($query, array($id));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    if (preg_match('/' . $alias . '-(\d+)$/', $ar['translit_alias'], $matches)) {
                        $alias .= '-' . ((int) $matches[1] + 1);
                        $is_alias_cathed = true;
                        break;
                    }
                }
            }
            if (!$is_alias_cathed) {
                $alias .= '-1';
            }
        }
        return $alias;
    }

    protected function saveTranslitAlias($id) {
        $new_alias = '';
        if (1 == $this->getConfigValue('apps.seo.allow_custom_realty_aliases')) {
            $DBC = DBC::getInstance();
            $query = 'SELECT translit_alias FROM re_data WHERE re_data.id=? LIMIT 1';
            $stmt = $DBC->query($query, array($id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $old_alias = $ar['translit_alias'];
            }

            if ($old_alias == '') {
                if ('' != $this->getConfigValue('apps.seo.allow_custom_realty_aliase_fields')) {
                    $fields = explode(',', $this->getConfigValue('apps.seo.allow_custom_realty_aliase_fields'));
                    foreach ($fields as $k => $v) {
                        $fields[$k] = trim($v);
                    }
                    $new_alias = $this->createTranslitAliasByFields($id, $fields);
                }

                if ('' != $new_alias) {
                    $new_alias = $this->makeUniqueAlias($new_alias, $id);
                }
            } else {
                return;
            }
        }

        if ($new_alias == '') {
            $DBC = DBC::getInstance();
            $new_alias = $this->createTranslitAliasByFields($id, array('city_id', 'street_id', 'number'));
            if ('' != $new_alias) {
                $new_alias = $this->makeUniqueAlias($new_alias, $id);
            }
        }

        $query = 'UPDATE re_data SET translit_alias=? WHERE id=?';
        $stmt = $DBC->query($query, array($new_alias, $id));
    }

    protected function removeTemporaryFields(&$model, $remove_this_names = array()) {
        if (count($remove_this_names) > 0) {
            foreach ($remove_this_names as $r) {
                unset($model[$r]);
            }
        }
        return $model;
    }

    protected function batch_update($table_name, $primary_key) {
        $rs .= $this->grid($user_id);
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

    function init_db_model ($table_name, $default_object_model, $params = false) {
        $form_data = array();

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model($table_name, false);
            if (empty($form_data)) {
                $form_data = array();
                $form_data = $default_object_model->get_model($params);
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
                $TA = new table_admin();
                $TA->create_table_and_columns($form_data, $table_name);
                $form_data = array();
                $form_data = $ATH->load_model($table_name, false);
            }
        } else {
            $form_data = $default_object_model->get_model($params);
        }

        $this->model = $default_object_model;
        $this->data_model = $form_data;
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
}
