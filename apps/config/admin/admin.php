<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Vendors admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class config_admin extends Object_Manager {

    private $dev_status = 0;
    protected static $check_config_array = array();

    ///private static $check_config_array_static = array();

    /**
     * Constructor
     */
    function __construct($realty_type = false) {
        $this->SiteBill();

        $this->table_name = 'config';
        $this->action = 'config';
        $this->app_title = Multilanguage::_('L_SETTINGS');
        $this->primary_key = 'id';
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/config_model.php');
        $this->data_model_object = new Config_Model();
        $this->data_model = $this->data_model_object->get_model();
        //$this->install();
        //$this->install_hidden_config();
        $this->check_config_structure();
    }

    function ajax() {
        /*if ($this->getRequestValue('action') == 'resort') {
            return $this->resort();
        }*/
        return false;
    }

    /*function resort() {
        $ids = trim($this->getRequestValue('ids'));
        if ($ids != '') {
            $DBC = DBC::getInstance();
            $ids_array = explode(',', $ids);
            $i = 1;
            foreach ($ids_array as $id) {
                $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET sort_order=' . $i . ' WHERE ' . $this->primary_key . '=' . $id;
                $stmt = $DBC->query($query);
                $i++;
            }
        }
    }*/

    function main() {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        global $__user, $__db;
        $this->clear_apps_cache();

        $rs = $this->getTopMenu();
        switch ($this->getRequestValue('do')) {
            case 'new' : {
                    $rs .= $this->get_form($this->data_model[$this->table_name], 'new');
                    break;
                }

            case 'new_done' : {
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

            case 'edit' : {
                    $form_data[$this->table_name] = $data_model->init_model_data_from_db($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name]);
                    $rs .= $this->get_form($form_data[$this->table_name], 'edit');
                    break;
                }

            case 'edit_done' : {

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

            case 'save' : {
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
                    $post = $_POST['conf_param_value'];
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

            case 'delete' : {
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
            case 'extended' : {
                    $rs .= $this->grid_extended();
                    break;
                }

            case 'text' : {
                    $rs .= $this->grid_text();
                    break;
                }

            default : {
                    //$this->loadAllConfigParams();
                    $rs .= $this->grid();
                    //$rs.=$this->getAddForm();
                }
        }
        $rs_new = $this->get_app_title_bar();
        $rs_new .= $rs;
        $this->clear_apps_cache();

        return $rs_new;
    }

    function install_hidden_config() {
        $DBC = DBC::getInstance();
        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_hidden_config` (
		  `config_key` varchar(255) NOT NULL,
		  `config_value` text NOT NULL,
		  UNIQUE KEY `conf_param` (`config_key`)
		) ENGINE=MyISAM DEFAULT CHARSET=" . DB_ENCODING . ";";
        $stmt = $DBC->query($query);
    }

    function install() {
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

    function getConfigSection($section = NULL) {
        $ret = array();
        $data = $this->createConfigStructure();
        if ($section !== NULL) {
            $ret = $data[$section];
        }
        return $ret;
    }

    function grid_text() {

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
        $rs .= '<h3>' . $primary_tab . '</h3>';
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

    function grid($params = array(), $default_params = array()) {
        $rs = '';
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/apps/apps_processor.php');
        $APP = new Apps_Processor();
        $apps = $APP->load_apps_menu(true);
        foreach ($apps as $ak => $av) {
            $apps_array['apps.' . $ak] = $av;
            //$apps_array_names['apps.'.$ak]=$av['title'];
        }
        $apps_array['apps.realty'] = array('title' => 'Дополнительно');
        $apps_array['apps.contact'] = array('title' => 'Контакты');
        //$apps_array_names['apps.realty']='Дополнительно';
        //asort($apps_array_names);
        //print_r($apps_array_names);


        $data = $this->createConfigStructure();

        $current_tab_nr = (int) $this->getRequestValue('tab_nr');

        $keys = array_keys($data);
        //$keys=array_keys($apps_array_names);
        //print_r($keys);
        $keys_fl = array_flip($keys);


        $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/config/js/utils.js"></script>';
        $rs .= '<style>.form-horizontal .control-label {width: 460px;} .form-horizontal .controls {margin-left: 480px;}</style>';


        //TABS
        //print_r($keys_fl);



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
        /* $_codenames_to_names=$codenames_to_names;
          $codenames_to_names=array();
          $codenames_to_names[$primary_tab]=$primary_tab;
          $codenames_to_names=array_merge($codenames_to_names, $_codenames_to_names);
          unset($_codenames_to_names); */

        $rs .= '<div class="tabbable tabs-left">';
        $rs .= '<ul class="nav nav-tabs">';

        $rs .= '<li' . (0 == $current_tab_nr ? ' class="active"' : '') . '><a href="#config-tabs-left-0" data-toggle="tab">' . $primary_tab . '</a></li>';

        $ti = 1;
        foreach ($codenames_to_names as $k => $v) {
            $rs .= '<li' . ($ti == $current_tab_nr ? ' class="active"' : '') . '><a href="#config-tabs-left-' . $ti . '" data-toggle="tab">' . $v . '</a></li>';
            $ti++;
        }


        /* foreach($keys_fl as $k=>$v){
          if(strpos($k, 'apps.')!==FALSE){
          if(isset($apps_array[$k])){
          $rs.='<li'.($v==$current_tab_nr ? ' class="active"' : '').'><a href="#config-tabs-left-'.$v.'" data-toggle="tab">'.$apps_array[$k]['title'].'</a></li>';
          $codenames_to_names[$k]=$apps_array[$k]['title'];
          }else{
          unset($data[$k]);
          }

          }else{
          $rs.='<li'.($keys_fl[$k]==$current_tab_nr ? ' class="active"' : '').'><a href="#config-tabs-left-'.$keys_fl[$k].'" data-toggle="tab">'.$k.'</a></li>';
          $codenames_to_names[$k]=$k;
          }
          } */


        //print_r($data);
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
        //$i=0;
        /* foreach($data as $k=>$v){
          $rs.='<div id="config-tabs-left-'.$keys_fl[$k].'" class="tab-pane fade in'.($keys_fl[$k]==$current_tab_nr ? ' active' : '').'">';
          if(isset($apps_array[$k])){
          $rs.='<h3>'.$apps_array[$k]['title'].'</h3>';
          }else{
          $rs.='<h3>'.$k.'</h3>';
          }
          $rs.=$this->getTabForm($v, $keys_fl[$k]);
          $rs.='</div>';
          //$i++;
          } */
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



        //ACCORDION

        /*
          $rs.='<div class="accordion" id="accordion2">';
          foreach($keys_fl as $k=>$v){
          //var_dump($current_tab_nr==$v);
          $rs.='<div class="accordion-group">';
          $rs.='<div class="accordion-heading">';
          if(strpos($k, 'apps.')!==FALSE){
          if(isset($apps_array[$k])){
          $rs.='<a href="#config-tabs-left-'.$v.'" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#config-tabs-left-'.$v.'">'.$apps_array[$k]['title'].'</a>';
          }else{
          unset($data[$k]);
          }

          }else{
          $rs.='<a href="#config-tabs-left-'.$keys_fl[$k].'" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#config-tabs-left-'.$keys_fl[$k].'">'.$k.'</a>';
          }
          $rs.='</div>';
          $rs.='<div id="config-tabs-left-'.$keys_fl[$k].'" class="accordion-body'.($current_tab_nr==$v ? ' in' : '').' collapse">';
          $rs.='<div class="accordion-inner">';
          $rs.=$this->getTabForm($data[$k], $keys_fl[$k]);
          $rs.='</div>';
          $rs.='</div>';
          $rs.='</div>';
          }
          $rs.='</div>';
         */




        return $rs;
    }

    private function customSort($a, $b) {
        /* if($a['nr']==0){
          return -1;
          }elseif($b['nr']==0){
          return -1;
          }elseif($a['name']<$b['name']){
          return -1;
          } */
        if ($a['name'] < $b['name']) {
            return -1;
        }
        return 1;
    }

    function createConfigStructure() {
        $DBC = DBC::getInstance();

        $data = array();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_config ORDER BY sort_order ASC, config_key ASC';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
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

    function getTabForm($data, $tab_nr = 0) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/config_mask.php');
        $CM = new Config_Mask();
        $config_mask = $CM->get_model();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/config_form_generator.php');
        $FG = new Config_Form_Generator();




        $rs = '<form class="config_form form-horizontal applied" method="post" action="' . SITEBILL_MAIN_URL . '/admin/index.php?action=' . $this->action . '">';

        foreach ($data as $d) {
            if ($this->is_demo() and $d['config_key'] == 'license_key') {
                
            } else {
                $ret = array();
                if (!isset($config_mask[$d['config_key']])) {
                    $ret['name'] = $d['id'];
                    $ret['hint'] = $d['config_key'];

                    $ret['title'] = $d['title'];
                    $ret['value'] = $d['value'];
                    if ($d['vtype'] == 0) {
                        $ret['type'] = 'safe_string';
                    } elseif ($d['vtype'] == 1) {
                        $ret['type'] = 'checkbox';
                    } elseif ($d['vtype'] == 2) {
                        $ret['type'] = 'select_box';
                    } elseif ($d['vtype'] == 3) {
                        $ret['type'] = 'textarea';
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

    /*
      function getTabForm_old($data){
      $rs .= '<form method="post" action="'.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'">';
      $rs .= '<table border="0" width="600">';
      $rs .= '<thead><th>Описание</th><th>Значение</th><th>Имя параметра</th><th></th><th></th></thead>';
      foreach($data as $d){
      if ( $this->is_demo() and  $d['config_key'] == 'license_key' ) {

      } else {
      $rs .= '<tr>';
      $rs .= '<td>'.$d['title'].'</td>';
      $rs .= '<td><input type="text" size="20" name="conf_param_value['.$d['id'].']" value="'.htmlspecialchars($d['value']).'"></td>';
      $rs .= '<td>'.$d['config_key'].'</td>';
      $rs .= '<td>'.($this->dev_status?'<a href="?action='.$this->action.'&do=delete&'.$this->primary_key.'='.$d['id'].'">Удалить</a>':'').'</td>';
      $rs .= '<td>'.($this->dev_status?'<a href="?action='.$this->action.'&do=edit&'.$this->primary_key.'='.$d['id'].'">Править</a>':'').'</td>';
      $rs .= '</tr>';
      }
      }
      $rs .= '<tr>';
      $rs .= '<td></td><td></td><td><input type="submit" name="cnf_submit" value="Сохранить"></td>';
      $rs .= '</tr>';
      //$rs .= '<input type="hidden" name="action" value="'.$this->action.'">';
      $rs .= '<input type="hidden" name="do" value="save">';
      $rs .= '</table>';
      $rs .= '</form>';
      return $rs;
      }
     */

    function updateParamToConfig($conf_param_id, $conf_param_value) {
        $DBC = DBC::getInstance();
        $query = "UPDATE `" . DB_PREFIX . "_" . $this->table_name . "` SET `value`=? WHERE `" . $this->primary_key . "`=?";
        $stmt = $DBC->query($query, array($this->validateParam($conf_param_value), $conf_param_id));
        return TRUE;
    }

    function validateParam($param) {
        /* echo '<pre>';
          var_dump($param);
          echo '</pre>'; */
        $rs = $param;
        if (get_magic_quotes_gpc()) {
            $rs = stripslashes($rs);
        }
        $rs = str_replace(array('\'', '"', '`'), '', $rs);
        $rs = trim($rs);
        //$rs=addslashes($rs);
        //$rs=mysql_real_escape_string($rs);
        /* if($quotes==1){
          $rs=mysql_real_escape_string(stripcslashes($rs));
          }else{
          $rs=mysql_real_escape_string($rs);
          } */
        return $rs;
    }

    function validateParamTitle($param) {
        $rs = $param;
        $rs = str_replace(array('`'), '', $rs);
        $rs = trim($rs);
        if (get_magic_quotes_gpc()) {
            $rs = stripslashes($rs);
            //$rs=mysql_real_escape_string(stripcslashes($rs));
        } else {
            //$rs=mysql_real_escape_string($rs);
        }
        //$rs=addslashes($rs);
        return $rs;
    }

    function is_demo() {
        global $__user;
        if (preg_match('/etown/', $__user)) {
            return true;
        }
        return false;
    }

    /**
     * Check config item
     * @param string $key
     * @return boolean
     */
    function check_config_item($key) {
        if (self::$check_config_array[$key] == 1) {
            return true;
        }
        return false;
    }

    function addParamToConfig($conf_new_param_name, $conf_new_param_value, $conf_new_param_title, $vtype = 0, $params = array()) {
        if ($this->check_config_item($conf_new_param_name)) {
            return true;
        }
        $DBC = DBC::getInstance();
        $query = "INSERT INTO " . DB_PREFIX . "_" . $this->table_name . " (config_key, value, title, vtype) VALUES (?,?,?,?)";
        $stmt = $DBC->query($query, array($this->validateParam($conf_new_param_name), $this->validateParam($conf_new_param_value), $this->validateParamTitle($conf_new_param_title), $vtype), $row, $success);
        if (!$success) {
            //$this->riseError($DBC->getLastError());
            //echo 'ERROR ON INSERT<br>';
        }
        $config_id = $DBC->lastInsertId();
        $query = "UPDATE `" . DB_PREFIX . "_" . $this->table_name . "` SET `sort_order`=? WHERE `id`=?";
        $stmt = $DBC->query($query, array($config_id, $config_id));
        if ( $params['public'] == true ) {
            $this->set_public_access($conf_new_param_name);
        }
        $this->reloadCheckConfigStructure();
        return TRUE;
    }

    function set_public_access ($conf_new_param_name) {
        $DBC = DBC::getInstance();
        $query = "UPDATE `" . DB_PREFIX . "_" . $this->table_name . "` SET `public`=? WHERE `config_key`=?";
        $stmt = $DBC->query($query, array(1, $conf_new_param_name));
    }

    function getTopMenu() {
        $rs = '';
        //$rs.='<a href="'.SITEBILL_MAIN_URL.'/admin/index.php?action='.$this->action.'&do=new">Добавить параметр</a>';
        return $rs;
    }

    /**
     * Check config structure
     * @param void
     * @return string
     */
    function check_config_structure() {
        if (empty(self::$check_config_array)) {
            $DBC = DBC::getInstance();
            $query = "select * from " . DB_PREFIX . "_config";
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    self::$check_config_array[$ar['config_key']] = '1';
                }
            }
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
        if (!$this->check_config_item('system_email_robot')) {
            $this->addParamToConfig('system_email_robot', '', 'Имя робота отправщика для писем');
        }

        //Общие
        if (!$this->check_config_item('site_title')) {
            $this->addParamToConfig('site_title', 'Агентство недвижимости', 'Заголовок сайта');
        }
        if (!$this->check_config_item('theme')) {
            $this->addParamToConfig('theme', 'agency', 'Тема оформления');
        }
        if (!$this->check_config_item('order_email_acceptor')) {
            $this->addParamToConfig('order_email_acceptor', 'kondin@etown.ru', 'Email на который будут приходить заявки с сайта');
        }
        if (!$this->check_config_item('per_page')) {
            $this->addParamToConfig('per_page', '20', 'Количество объявлений на одну страницу на сайте');
        }
        if (!$this->check_config_item('common_per_page')) {
            $this->addParamToConfig('common_per_page', '10', 'Количество позиций на страницу (для списков справочников в админке)');
        }
        
        if (!$this->check_config_item('per_page_account')) {
            $this->addParamToConfig('per_page_account', '10', 'Количество позиций на страницу (для списков в ЛК)');
        }

        if (!$this->check_config_item('core.listing.pager_draw_all')) {
            $this->addParamToConfig('core.listing.pager_draw_all', '0', 'Отрисовывать все страницы в постраничной навигации', 1);
        }
        if (!$this->check_config_item('core.listing.pager_page_offset')) {
            $this->addParamToConfig('core.listing.pager_page_offset', '7', 'Количество страниц показываемых в обе стороны от активной');
        }
        if (!$this->check_config_item('core.listing.pager_draw_all_max')) {
            $this->addParamToConfig('core.listing.pager_draw_all_max', '0', 'Максимальное число страниц, до которого в пейджере будут отображаться все страницы');
        }
        if (!$this->check_config_item('core.listing.pager_end_buttons')) {
            $this->addParamToConfig('core.listing.pager_end_buttons', '1', 'Отображать кнопки Первая-Последняя', 1);
        }
        if (!$this->check_config_item('core.listing.pager_prev_buttons')) {
            $this->addParamToConfig('core.listing.pager_prev_buttons', '1', 'Отображать кнопки Предыдущая-Следующая', 1);
        }
        if (!$this->check_config_item('core.listing.pager_show_prefixes')) {
            $this->addParamToConfig('core.listing.pager_show_prefixes', '1', 'Отображать префиксы для пропущенных страниц', 1);
        }

        if (!$this->check_config_item('currency_enable')) {
            $this->addParamToConfig('currency_enable', '0', 'Включить поддержку выбора валют в объявлении', 1);
        }
        if (!$this->check_config_item('allow_register_account')) {
            $this->addParamToConfig('allow_register_account', '1', 'Разрешить регистрацию на сайте', 1);
        }
        if (!$this->check_config_item('allow_remind_password')) {
            $this->addParamToConfig('allow_remind_password', '1', 'Разрешить напоминание пароля', 1);
        }



        if (!$this->check_config_item('bootstrap_version')) {
            $this->addParamToConfig('bootstrap_version', '', 'Версия Bootstrap');
        }
        
        if (!$this->check_config_item('use_google_map')) {
            $this->addParamToConfig('use_google_map', '0', 'Использовать карту Google', 1);
        }

        if (!$this->check_config_item('google_api_key')) {
            $this->addParamToConfig('google_api_key', '', 'Ключ API Google');
        }
        
        if (!$this->check_config_item('google_api_key_server')) {
            $this->addParamToConfig('google_api_key_server', '', 'Ключ API Google для серверных запросов');
        }

        if (!$this->check_config_item('google_recaptcha_key')) {
            $this->addParamToConfig('google_recaptcha_key', '', 'Ключ Google ReCaptcha');
        }

        if (!$this->check_config_item('use_captcha_admin_entry')) {
            $this->addParamToConfig('use_captcha_admin_entry', '0', 'Использовать капчу на входе в админку', 1);
        }
        if (!$this->check_config_item('work_on_https')) {
            $this->addParamToConfig('work_on_https', '0', 'Работать через https', 1);
        }
        if (!$this->check_config_item('moderate_first')) {
            $this->addParamToConfig('moderate_first', '0', 'Не публиковать объявления из ЛК без премодерации', 1);
        }

        if (!$this->check_config_item('hide_contact_input_user_data')) {
            $this->addParamToConfig('hide_contact_input_user_data', '0', 'Убрать поля ввода контактов из формы добавления объявления в личном кабинете', 1);
        }
        if (!$this->check_config_item('use_realty_view_counter')) {
            $this->addParamToConfig('use_realty_view_counter', '0', 'Использовать встроенный счетчик просмотров', 1);
        }
        if (!$this->check_config_item('date_format')) {
            $this->addParamToConfig('date_format', 'standart', 'Формат даты');
        }

        if (!$this->check_config_item('ue_name')) {
            $this->addParamToConfig('ue_name', 'руб.', 'Название валюты в личном кабинете');
        }


        if (!$this->check_config_item('enable_special_in_account')) {
            $this->addParamToConfig('enable_special_in_account', '0', 'В личном кабинете доступна галочка спец.размещений', 1);
        }

        if (!$this->check_config_item('enable_curator_mode')) {
            $this->addParamToConfig('enable_curator_mode', '0', 'Активировать режим куратора', 1);
        }
        
        if (!$this->check_config_item('curator_mode_fullaccess')) {
            $this->addParamToConfig('curator_mode_fullaccess', '0', 'Полный доступ куратора к объектам стажера', 1);
        }
        
        if (!$this->check_config_item('curator_mode_chainsallow')) {
            $this->addParamToConfig('curator_mode_chainsallow', '0', 'Разрешить цепочки кураторства', 1);
        }


        if (!$this->check_config_item('use_new_realty_grid')) {
            $this->addParamToConfig('use_new_realty_grid', '0', 'Использовать настраиваемую сетку в выводе в админке', 1);
        }

        if (!$this->check_config_item('notify_admin_about_register')) {
            $this->addParamToConfig('notify_admin_about_register', '0', 'Уведомлять администратора о новой регистрации пользователя', 1);
        }

        if (!$this->check_config_item('notify_about_added_realty')) {
            $this->addParamToConfig('notify_about_added_realty', '0', 'Уведомлять пользователя о добавленных объявлениях', 1);
        }

        if (!$this->check_config_item('filter_double_data')) {
            $this->addParamToConfig('filter_double_data', '0', 'Не допускать добавления дубликатов данных', 1);
        }
        if (!$this->check_config_item('check_permissions')) {
            $this->addParamToConfig('check_permissions', '0', 'Разделение прав доступа для групп. Группа администраторов (admin) имеет доступ ко всем функциям без учета прав доступа.', 1);
        }
        if (!$this->check_config_item('allow_user_email_change')) {
            $this->addParamToConfig('allow_user_email_change', '0', 'Разрешить пользователям изменять email', 1);
        }

        /*
          if ( !$this->check_config_item('divide_step_form') ) {
          $this->addParamToConfig('divide_step_form','0','Делить формы на шаги');
          }
         */

        if (!$this->check_config_item('use_registration_email_confirm')) {
            $this->addParamToConfig('use_registration_email_confirm', '0', 'Использовать активацию аккаунта по email при регистрации', 1);
        }

        if (!$this->check_config_item('use_registration_sms_confirm')) {
            $this->addParamToConfig('use_registration_sms_confirm', '0', 'Использовать активацию аккаунта с помщью SMS при регистрации. <a href="http://wiki.sitebill.ru/index.php?title=%D0%A0%D0%B5%D0%B3%D0%B8%D1%81%D1%82%D1%80%D0%B0%D1%86%D0%B8%D1%8F_%D1%81_SMS_%D0%BF%D0%BE%D0%B4%D1%82%D0%B2%D0%B5%D1%80%D0%B6%D0%B4%D0%B5%D0%BD%D0%B8%D0%B5%D0%BC" target="_blank">Подробнее</a>', 1);
        }

        if (!$this->check_config_item('email_signature')) {
            $this->addParamToConfig('email_signature', 'С уважением, команда ' . $_SERVER['SERVER_NAME'], 'Подпись в письмах');
        }

        if (!$this->check_config_item('registration_notice')) {
            $this->addParamToConfig('registration_notice', '0', 'Уведомлять пользователя о регистрации', 1);
        }

        if (!$this->check_config_item('meta_title_main')) {
            $this->addParamToConfig('meta_title_main', '', 'Заголовок главной');
        }

        if (!$this->check_config_item('meta_keywords_main')) {
            $this->addParamToConfig('meta_keywords_main', '', 'Ключевые слова главной');
        }

        if (!$this->check_config_item('meta_description_main')) {
            $this->addParamToConfig('meta_description_main', '', 'Мета-описание главной');
        }

        if (!$this->check_config_item('default_tab_name')) {
            $this->addParamToConfig('default_tab_name', 'Основное', 'Название закладки формы по-умолчанию');
        }
        
        if (!$this->check_config_item('csrf_salt')) {
            $this->addParamToConfig('csrf_salt', '', 'Соль для создания CSRF-токена');
        }
        //view
        if (!$this->check_config_item('photo_per_data')) {
            $this->addParamToConfig('photo_per_data', '0', 'Количество изображений для одного объекта (0 или ничего - без ограничений)');
        }



        //notify	    
        if (!$this->check_config_item('add_notification_email')) {
            $this->addParamToConfig('add_notification_email', '', 'E-mail для получения уведомлений о новых объявлениях (при отсутствии изпользуется order_email_acceptor)');
        }
        if (!$this->check_config_item('notify_about_publishing')) {
            $this->addParamToConfig('notify_about_publishing', '0', 'Уведомлять пользователя о публикации его объявления после модерации.', 1);
        }

        if (!$this->check_config_item('post_form_agreement_enable')) {
            $this->addParamToConfig('post_form_agreement_enable', '0', 'Активировать выдачу соглашения после формы', 1);
        }
        if (!$this->check_config_item('post_form_agreement_text_add')) {
            $this->addParamToConfig('post_form_agreement_text_add', 'Я,  ознакомлен(а) с Пользовательским соглашением', 'Текст соглашения после формы добавления объявления');
        }
        if (!$this->check_config_item('post_form_agreement_enable_note')) {
            $this->addParamToConfig('post_form_agreement_enable_note', '0', 'Выводить соглашение с формой в виде текстового уведомления', 1);
        }
        if (!$this->check_config_item('register_form_agreement_enable')) {
            $this->addParamToConfig('register_form_agreement_enable', '0', 'Добавлять элемент согласия с Правилами к форме регистрации', 1);
        }
        if (!$this->check_config_item('register_form_agreement_enable_ch')) {
            $this->addParamToConfig('register_form_agreement_enable_ch', '0', 'Элемент согласия с Правилами к форме регистрации изначально выбран', 1);
        }

        if (!$this->check_config_item('is_watermark')) {
            $this->addParamToConfig('is_watermark', '0', 'Использовать watermark на фотографиях<br> (по-умолчанию картинка лежит тут /img/watermark/watermark.gif)');
        }
        /*if (!$this->check_config_item('watermark_user_control')) {
            $this->addParamToConfig('watermark_user_control', '0', 'Установка применения watermark пользователем на уровне объектов',1);
        }*/
        //admin
        if (!$this->check_config_item('hide_empty_catalog')) {
            $this->addParamToConfig('hide_empty_catalog', '1', 'Прятать каталоги без содержимого', 1);
        }
        if (!$this->check_config_item('user_account_enable')) {
            $this->addParamToConfig('user_account_enable', '0', 'Редактировать лицевой счет пользователя в админке', 1);
        }
        if (!$this->check_config_item('seo_photo_name_enable')) {
            $this->addParamToConfig('seo_photo_name_enable', '0', 'Включить SEO-оптимизацию названий изображений', 1);
        }
        if (!$this->check_config_item('advert_cost')) {
            $this->addParamToConfig('advert_cost', '0', 'Стоимость размещения одного простого объявления. <a href="http://www.sitebill.ru/stoimost-obyavleniya.html" target="_blank">Что это такое?</a>');
        }
        if (!$this->check_config_item('special_advert_cost')) {
            $this->addParamToConfig('special_advert_cost', '0', 'Стоимость размещения одного специального предложения');
        }
        if (!$this->check_config_item('editor')) {
            $this->addParamToConfig('editor', 'cleditor', 'Тип WYSIWYG-редактора');
        }
        
        if (!$this->check_config_item('autocomplete_distinct')) {
            $this->addParamToConfig('autocomplete_distinct', '0', 'Фильтровать данные autocomplete-выдачи на уникальность названий',1);
        }
        



        //Второстепенные КОНФИГИ
        if (!$this->check_config_item('login_user_data_ad')) {
            $this->addParamToConfig('login_user_data_ad', '', 'Список дополнительных полей данных авторизированного пользователя', 3);
        }

        if (!$this->check_config_item('set_cookie_subdomenal')) {
            $this->addParamToConfig('set_cookie_subdomenal', 0, 'Устанавливать COOKIE для всех субдоменов', 1);
        }

        if (!$this->check_config_item('apps.realty.allow_notactive_direct')) {
            $this->addParamToConfig('apps.realty.allow_notactive_direct', 0, 'Открыть доступ к неактивным объектам по прямой ссылке', 1);
        }

        if (!$this->check_config_item('user_pic_smart')) {
            $this->addParamToConfig('user_pic_smart', 0, 'Выдерживать точный размер для аватар пользователей', 1);
        }

        if (!$this->check_config_item('default_timezone')) {
            $this->addParamToConfig('default_timezone', '', 'Временная зона');
        }

        if (!$this->check_config_item('classic_local_grid')) {
            $this->addParamToConfig('classic_local_grid', '0', 'Использовать классический локальный конструктор списков', 1);
        }

        if (!$this->check_config_item('classic_local_view')) {
            $this->addParamToConfig('classic_local_view', '0', 'Использовать классический локальный конструктор карточки', 1);
        }

        if (!$this->check_config_item('user_anonimouse_group_id')) {
            $this->addParamToConfig('user_anonimouse_group_id', '0', 'ID группы гостей');
        }

        if (!$this->check_config_item('login_block_time')) {
            $this->addParamToConfig('login_block_time', '5', 'Время блокироваки авторизаций аккаунта в минутах');
        }

        if (!$this->check_config_item('max_login_try_count')) {
            $this->addParamToConfig('max_login_try_count', '5', 'Количество ошибочных попыток авторизации');
        }

        if (!$this->check_config_item('core_domain')) {
            $this->addParamToConfig('core_domain', '', 'Базовый домен (без протокола)');
        }


        if (!$this->check_config_item('robokassa_pay_enable')) {
            $this->addParamToConfig('robokassa_pay_enable', '1', 'Включить модуль ROBOKASSA', 1);
        }

        if (!$this->check_config_item('robokassa_by_frekassa')) {
            $this->addParamToConfig('robokassa_by_frekassa', '0', 'Работа модуля ROBOKASSA через интерфейс FREEKASSA', 1);
        }

        if (!$this->check_config_item('core_level_symbol')) {
            $this->addParamToConfig('core_level_symbol', '#.#', 'Символ отбивки для корневых разделов в элементе structure');
        }

        if (!$this->check_config_item('level_symbol')) {
            $this->addParamToConfig('level_symbol', '#.#', 'Символ отбивки для вложенных разделов в элементе structure');
        }

        if (!$this->check_config_item('dontclean_uploadify_table')) {
            $this->addParamToConfig('dontclean_uploadify_table', '0', 'Не очищать таблицу загрузок автоматически', 1);
        }



        /* if ( !$this->check_config_item('use_heaps') ) {
          $this->addParamToConfig('use_heaps','0','Use Heaps');
          } */

        if (!$this->check_config_item('apps.realty.update_date_added')) {
            $this->addParamToConfig('apps.realty.update_date_added', 0, 'Обновлять дату добавления на текущую при редактировании объявления в ЛК', 1);
        }

        if (!$this->check_config_item('apps.realty.uniq_params')) {
            $this->addParamToConfig('apps.realty.uniq_params', '', 'Параметры сравнения дублирующихся объявлений', 0);
        }

        if (!$this->check_config_item('disable_guest_add')) {
            $this->addParamToConfig('disable_guest_add', 0, 'Запретить гостевое добавление', 1);
        }

        if (!$this->check_config_item('apps.realty.similar_preg')) {
            $this->addParamToConfig('apps.realty.similar_preg', '', 'Параметры похожих');
        }
        
        if (!$this->check_config_item('apps.realty.similar_grid')) {
            $this->addParamToConfig('apps.realty.similar_grid', 0, 'Формировать массив "Похожие" как стандартный список', 1);
        }

        if (!$this->check_config_item('core.listing.add_user_info')) {
            $this->addParamToConfig('core.listing.add_user_info', '0', 'Выбирать данные пользователя', 1);
        }

        if (!$this->check_config_item('core.listing.add_user_info_fields')) {
            $this->addParamToConfig('core.listing.add_user_info_fields', '', 'Список выбираемых данных пользователя');
        }

        if (!$this->check_config_item('core.listing.select_query_fields')) {
            $this->addParamToConfig('core.listing.select_query_fields', '', 'Список select_by_query-полей необходимых для подбора', 3);
        }


        /* if ( !$this->check_config_item('apps.realty.updated_at_field_type') ) {
          $this->addParamToConfig('apps.realty.updated_at_field_type','','Тип поля даты обновления (по умолчанию 0=datetime, либо укажите 1=timestamp)');
          }

          if ( !$this->check_config_item('apps.realty.updated_at_field') ) {
          $this->addParamToConfig('apps.realty.updated_at_field','','Системное имя поля даты обновления');
          } */

        if (!$this->check_config_item('apps.realty.admin_fast_view')) {
            $this->addParamToConfig('apps.realty.admin_fast_view', '', 'Набор полей быстрого просмотра');
        }

        if (!$this->check_config_item('admin_grid_leftbuttons')) {
            $this->addParamToConfig('admin_grid_leftbuttons', 0, 'Размещать кнопки управления слева', 1);
        }

        if (!$this->check_config_item('apps.realty.title_preg')) {
            $this->addParamToConfig('apps.realty.title_preg', '', 'Формат строки заголовка');
        }


        if (!$this->check_config_item('data_adv_share_access')) {
            $this->addParamToConfig('data_adv_share_access', '0', 'Разделять доступ к объявлениям в админке', 1);
        }

        if (!$this->check_config_item('data_adv_share_access_user_list_strict')) {
            $this->addParamToConfig('data_adv_share_access_user_list_strict', '0', 'Пользователь при добавлении объявления в админке при включенной опции data_adv_share_access может видеть только себя в списке пользователей', 1);
        }

        if (!$this->check_config_item('data_adv_share_access_can_view_all')) {
            $this->addParamToConfig('data_adv_share_access_can_view_all', '0', 'Разрешить просмотр всех записей (без редактирования и удаления) при включенной опции data_adv_share_access', 1);
        }

        if (!$this->check_config_item('data_adv_share_access_extended')) {
            $this->addParamToConfig('data_adv_share_access_extended', '', 'Список идентификаторов пользователей через запятую, которые видны в списке при редактировании объявления в режиме share_access');
        }


        if (!$this->check_config_item('register_passstregth')) {
            $this->addParamToConfig('register_passstregth', '0', 'Регистрация: сила пароля (0|1|2|3)');
        }

        if (!$this->check_config_item('register_maxpasslength')) {
            $this->addParamToConfig('register_maxpasslength', '32', 'Регистрация: максимальная длина пароля');
        }

        if (!$this->check_config_item('register_minpasslength')) {
            $this->addParamToConfig('register_minpasslength', '5', 'Регистрация: минимальная длина пароля');
        }

        if (!$this->check_config_item('add_pagenumber_title_place')) {
            $this->addParamToConfig('add_pagenumber_title_place', '0', 'Куда добавлять количество страниц в заголовке');
        }

        if (!$this->check_config_item('apps.realty.use_predeleting')) {
            $this->addParamToConfig('apps.realty.use_predeleting', '0', 'Использовать архивирование при удалении для объявлений', 1);
        }

        if (!$this->check_config_item('apps.realty.archived_notactive')) {
            $this->addParamToConfig('apps.realty.archived_notactive', '0', 'Архивированные объявления полностью не доступны', 1);
        }

        if (!$this->check_config_item('jpeg_quality')) {
            $this->addParamToConfig('jpeg_quality', '80', 'Коэффициент качества для JPEG/JPG (от 0 до 100)');
        }

        if (!$this->check_config_item('png_quality')) {
            $this->addParamToConfig('png_quality', '0', 'Степень сжатия для PNG: от 0 (нет сжатия) до 9');
        }

        if (!$this->check_config_item('robokassa_koef')) {
            $this->addParamToConfig('robokassa_koef', '1', 'Коэффициент перевода валюты сайта в RUR');
        }

        if (!$this->check_config_item('newuser_registration_shared_groupid')) {
            $this->addParamToConfig('newuser_registration_shared_groupid', '', 'ID групп, допустимых к выбору пользователем');
        }

        if (!$this->check_config_item('newuser_autoregistration_groupid')) {
            $this->addParamToConfig('newuser_autoregistration_groupid', '', 'ID группы присваиваемой новым автозарегистрированным пользователям');
        }

        if (!$this->check_config_item('newuser_registration_groupid')) {
            $this->addParamToConfig('newuser_registration_groupid', '', 'ID группы присваиваемой новым зарегистрировавшимся пользователям');
        }

        if (!$this->check_config_item('apps.realty.sorts')) {
            $this->addParamToConfig('apps.realty.sorts', '', 'Сортировка в сетке объявлений по умолчанию');
        }

        if (!$this->check_config_item('add_pagenumber_title')) {
            $this->addParamToConfig('add_pagenumber_title', '0', 'Добавлять к заголовку страницы номер текущей страницы', 1);
        }

        if (!$this->check_config_item('is_underconstruction')) {
            $this->addParamToConfig('is_underconstruction', '0', 'Закрыть сайт', 1);
        }

        if (!$this->check_config_item('is_underconstruction_allowed_ip')) {
            $this->addParamToConfig('is_underconstruction_allowed_ip', '127.0.0.1', 'IP разрешенный для доступа в закрытом режиме');
        }

        if (!$this->check_config_item('notify_about_payment')) {
            $this->addParamToConfig('notify_about_payment', '0', 'Уведомлять администратора о платежах по email', 1);
        }

        if (!$this->check_config_item('apps.watermark.opacity')) {
            $this->addParamToConfig('apps.watermark.opacity', '50', 'Процент прозрачности наложения водяногознака (от 0 до 100)');
        }



        if (!$this->check_config_item('apps.realty.off_system_ajax')) {
            $this->addParamToConfig('apps.realty.off_system_ajax', '0', 'Off system Ajax', 1);
        }

        if (!$this->check_config_item('disable_mail_additionals')) {
            $this->addParamToConfig('disable_mail_additionals', '', 'Mailer: Отключить передачу дополнительных флагов в заголовках письма', 1);
        }

        if (!$this->check_config_item('save_without_watermark')) {
            $this->addParamToConfig('save_without_watermark', '', 'Сохранять копию изображений без водяного знака', 1);
        }

        if (!$this->check_config_item('apps.realty.preview_smart_resizing')) {
            $this->addParamToConfig('apps.realty.preview_smart_resizing', '0', 'Использовать умную подгонку превьюшек', 1);
        }

        if (!$this->check_config_item('apps.realty.data_image_big_height')) {
            $this->addParamToConfig('apps.realty.data_image_big_height', '600', 'Высота изображения объявления');
        }

        if (!$this->check_config_item('apps.realty.data_image_big_width')) {
            $this->addParamToConfig('apps.realty.data_image_big_width', '800', 'Ширина изображения объявления');
        }

        if (!$this->check_config_item('apps.realty.data_image_preview_height')) {
            $this->addParamToConfig('apps.realty.data_image_preview_height', '200', 'Высота превью изображения объявления');
        }

        if (!$this->check_config_item('apps.realty.data_image_preview_width')) {
            $this->addParamToConfig('apps.realty.data_image_preview_width', '200', 'Ширина превью изображения объявления');
        }

        if (!$this->check_config_item('similar_items_count')) {
            $this->addParamToConfig('similar_items_count', '', 'Количество похожих объявлений в просмотре объявления');
        }

        if (!$this->check_config_item('block_user_search_forms')) {
            $this->addParamToConfig('block_user_search_forms', '0', 'Блокировать формы поиска пользователя', 1);
        }

        if (!$this->check_config_item('block_user_front_grids')) {
            $this->addParamToConfig('block_user_front_grids', '0', 'Блокировать фронтальные сетки пользователя', 1);
        }

        if (!$this->check_config_item('show_up_icon')) {
            $this->addParamToConfig('show_up_icon', '0', 'Админ может поднимать объявления', 1);
        }

        if (!$this->check_config_item('captcha_type')) {
            $this->addParamToConfig('captcha_type', '0', 'Тип капчи');
        }


        if (!$this->check_config_item('show_cattree_left')) {
            $this->addParamToConfig('show_cattree_left', '1', 'Выводить дерево каталогов слева в списке объявлений', 1);
        }

        if (!$this->check_config_item('ignore_free_from_parameter')) {
            $this->addParamToConfig('ignore_free_from_parameter', '1', 'Игнорировать свободно с', 1);
        }

        if (!$this->check_config_item('disable_root_structure_select')) {
            $this->addParamToConfig('disable_root_structure_select', '0', 'Блокировать корневые элементы в селектбоксах структуры', 1);
        }

        if (!$this->check_config_item('use_combobox')) {
            $this->addParamToConfig('use_combobox', '0', 'Использовать combobox в элементах select', 1);
        }


        /* vk */
        if (!$this->check_config_item('apps.socialauth.vk.enable')) {
            $this->addParamToConfig('apps.socialauth.vk.enable', '0', 'Включить авторизацию через Вконтакте', 1);
        }
        if (!$this->check_config_item('apps.socialauth.vk.api_key')) {
            $this->addParamToConfig('apps.socialauth.vk.api_key', 'vk api_key', 'VK API_KEY');
        }
        if (!$this->check_config_item('apps.socialauth.vk.secret')) {
            $this->addParamToConfig('apps.socialauth.vk.secret', 'vk secret', 'VK SECRET');
        }
        if (!$this->check_config_item('apps.socialauth.vk.redirect_url')) {
            $this->addParamToConfig('apps.socialauth.vk.redirect_url', 'vk redirect_url', 'vk redirect_url');
        }


        /* fb */
        if (!$this->check_config_item('apps.socialauth.fb.enable')) {
            $this->addParamToConfig('apps.socialauth.fb.enable', '0', 'Включить авторизацию через Facebook', 1);
        }





        if (!$this->check_config_item('apps.accountsms.enable')) {
            $this->addParamToConfig('apps.accountsms.enable', '0', 'Включить кабинет accountsms', 1);
        }

        if (!$this->check_config_item('template.agency.logo')) {
            $this->addParamToConfig('template.agency.logo', 'logo.gif', 'Шаблон Agency. Файл логотипа.');
        }


        if (!$this->check_config_item('apps.registersms.enable')) {
            $this->addParamToConfig('apps.registersms.enable', '0', 'Включить регистрацию через SMS', 1);
        }

        if (!$this->check_config_item('apps.newsparser_rbc.portion')) {
            $this->addParamToConfig('apps.newsparser_rbc.portion', '10', 'Количество новостей обрабатываемых за один проход');
        }

        if (!$this->check_config_item('apps.yml.delivery')) {
            $this->addParamToConfig('apps.yml.delivery', 'true', 'Возможность доставки товара на условиях, которые указываются в партнерском интерфейсе http://partner.market.yandex.ru на странице "редактирование" (true/false).');
        }

        if (!$this->check_config_item('apps.yml.pickup')) {
            $this->addParamToConfig('apps.yml.pickup', 'false', 'Возможность предварительно заказать товар и забрать его в точке продаж (true/false).');
        }

        if (!$this->check_config_item('apps.yml.store')) {
            $this->addParamToConfig('apps.yml.store', 'false', 'Возможность приобрести товар в точке продаж без предварительного заказа по интернету (true/false).');
        }

        if (!$this->check_config_item('apps.freeorder.notification_email')) {
            $this->addParamToConfig('apps.freeorder.notification_email', '', 'E-mail для получения уведомлений о новых заявках через Apps.Freeorder (при отсутствии изпользуется order_email_acceptor)');
        }

        if (!$this->check_config_item('apps.yandexrealty_parser.default_user_id')) {
            $this->addParamToConfig('apps.yandexrealty_parser.default_user_id', '0', 'ID пользователя по умолчанию. Если 0, то ID пользователя будет браться из таблицы доменов. Если не 0, то в качестве user_id для позиции будет использоваться это значение.');
        }

        if (!$this->check_config_item('apps.yandexrealty_parser.default_activity_status')) {
            $this->addParamToConfig('apps.yandexrealty_parser.default_activity_status', '1', 'Статус активности для добавляемых записей', 1);
        }



        if (!$this->check_config_item('apps.yandexrealty_parser.allow_create_new_category')) {
            $this->addParamToConfig('apps.yandexrealty_parser.allow_create_new_category', '1', 'Разрешить создание цепочек категорий в случае отсутствия подходящей', 1);
        }

        if (!$this->check_config_item('apps.yandexrealty_parser.category_for_all')) {
            $this->addParamToConfig('apps.yandexrealty_parser.category_for_all', '1000', 'ID категории, которая будет сопоставлена добавляемой записи в случае apps.yandexrealty_parser.allow_create_new_category=0');
        }

        if (!$this->check_config_item('apps.twitter.enable')) {
            $this->addParamToConfig('apps.twitter.enable', '0', 'Включить приложение Apps.Twitter', 1);
        }

        if (!$this->check_config_item('apps.twitter.user_secret')) {
            $this->addParamToConfig('apps.twitter.user_secret', '', 'Access token secret');
        }

        if (!$this->check_config_item('apps.twitter.user_token')) {
            $this->addParamToConfig('apps.twitter.user_token', '', 'Access token');
        }

        if (!$this->check_config_item('apps.twitter.consumer_secret')) {
            $this->addParamToConfig('apps.twitter.consumer_secret', '', 'Consumer_secret');
        }

        if (!$this->check_config_item('apps.twitter.consumer_key')) {
            $this->addParamToConfig('apps.twitter.consumer_key', '', 'Consumer_key');
        }

        if (!$this->check_config_item('apps.sms.max_uses')) {
            $this->addParamToConfig('apps.sms.max_uses', '0', 'Количество использований SMS-напоминания (0 или ничего - без ограничений)');
        }

        if (!$this->check_config_item('apps.realtypro.show_contact.enable')) {
            $this->addParamToConfig('apps.realtypro.show_contact.enable', '0', 'Включить показ контактов объявления', 1);
        }


        if (!$this->check_config_item('apps.watermark.enable')) {
            $this->addParamToConfig('apps.watermark.enable', '1', 'Включить приложение Apps.WatermarkPrinter', 1);
        }

        if (!$this->check_config_item('apps.watermark.position')) {
            $this->addParamToConfig('apps.watermark.position', 'center', 'Расположение принта (center|top-left|top-right|bottom-left|bottom-right)');
        }

        if (!$this->check_config_item('apps.watermark.offset_top')) {
            $this->addParamToConfig('apps.watermark.offset_top', '5', 'Отступ принта сверху, px');
        }

        if (!$this->check_config_item('apps.watermark.offset_bottom')) {
            $this->addParamToConfig('apps.watermark.offset_bottom', '5', 'Отступ принта снизу, px');
        }

        if (!$this->check_config_item('apps.watermark.offset_left')) {
            $this->addParamToConfig('apps.watermark.offset_left', '5', 'Отступ принта слева, px');
        }

        if (!$this->check_config_item('apps.watermark.offset_right')) {
            $this->addParamToConfig('apps.watermark.offset_right', '5', 'Отступ принта справа, px');
        }

        if (!$this->check_config_item('apps.watermark.preview_enable')) {
            $this->addParamToConfig('apps.watermark.preview_enable', '0', 'Добавлять водяной знак на превью-изображении', 1);
        }

        if (!$this->check_config_item('apps.watermark.image_preview')) {
            $this->addParamToConfig('apps.watermark.image_preview', '', 'Название файла для водяного знака для превью-изображений');
        }
        
        if (!$this->check_config_item('apps.watermark.printanywhere')) {
            $this->addParamToConfig('apps.watermark.printanywhere', '', 'Наносить водяной знак на всю графику', 1);
        }



        if (!$this->check_config_item('apps.shoplog.enable')) {
            $this->addParamToConfig('apps.shoplog.enable', '0', 'Включитьп приложение Apps.Shoplog', 1);
        }

        if (!$this->check_config_item('apps.rabota.enable')) {
            $this->addParamToConfig('apps.rabota.enable', '0', 'Включить приложение Apps.Rabota', 1);
        }

        if (!$this->check_config_item('apps.shop.current_city_id')) {
            $this->addParamToConfig('apps.shop.current_city_id', '', 'ID текущего города');
        }

        if (!$this->check_config_item('apps.shop.mail_title')) {
            $this->addParamToConfig('apps.shop.mail_title', 'Интернет-магазин', 'Название магазина (будет указано в заголовке писем о заказах)');
        }


        if (!$this->check_config_item('apps.yml.local_delivery_cost')) {
            $this->addParamToConfig('apps.yml.local_delivery_cost', '', 'Cтоимость доставки для своего региона');
        }


        if (!$this->check_config_item('apps.fasteditor.email_send_password_text')) {
            $this->addParamToConfig('apps.fasteditor.email_send_password_text', 'Пароль для доступа к редактированию {password}', 'Текст сообщения на почту с паролем для редактирования ( {password} указывает место размещения пароля в тексте сообщения)');
        }

        if (!$this->check_config_item('apps.fasteditor.sms_send_password_text_long')) {
            $this->addParamToConfig('apps.fasteditor.sms_send_password_text_long', 'Ваше объявление бесплатно размещено. Помощь в оформлении недвижимости тел 37-86-86, 89289678686 Пароль для редакции объявления {password}', '(Длинное) Текст sms сообщения с паролем для редактирования ( {password} указывает место размещения пароля в тексте сообщения)');
        }

        if (!$this->check_config_item('apps.fasteditor.sms_send_password_text')) {
            $this->addParamToConfig('apps.fasteditor.sms_send_password_text', 'Ваше объявление бесплатно размещено. Помощь в оформлении недвижимости тел 37-86-86, 89289678686 Пароль для редакции объявления {password}', 'Текст sms сообщения с паролем для редактирования ( {password} указывает место размещения пароля в тексте сообщения)');
        }


        if (!$this->check_config_item('apps.freeorder.enable')) {
            $this->addParamToConfig('apps.freeorder.enable', '0', 'Включить Apps.Freeorder', 1);
        }

        if (!$this->check_config_item('apps.news.news_line.per_page')) {
            $this->addParamToConfig('apps.news.news_line.per_page', '3', 'Количество новостей в новостном блоке на главной странице');
        }

        if (!$this->check_config_item('apps.shopstat.enable')) {
            $this->addParamToConfig('apps.shopstat.enable', '0', 'Включить Apps.Shopstat', 1);
        }

        if (!$this->check_config_item('apps.orderhistory.enable')) {
            $this->addParamToConfig('apps.orderhistory.enable', '0', 'Включить Apps.Orderhistory');
        }

        if (!$this->check_config_item('apps.sms.apikey')) {
            $this->addParamToConfig('apps.sms.apikey', 'XXXXXXXXXXXXYYYYYYYYYYYYZZZZZZZZXXXXXXXXXXXXYYYYYYYYYYYYZZZZZZZZ', 'SMSPilot API ключ. Можно получить по адресу <a target=_blank href=http://www.smspilot.ru/apikey.php>http://www.smspilot.ru/apikey.php</a>');
        }

        if (!$this->check_config_item('apps.sms.sender')) {
            $this->addParamToConfig('apps.sms.sender', 'estate.cms', 'Имя отправителя в SMS отправленных через SMSPilot');
        }

        if (!$this->check_config_item('apps.fasteditor.enable')) {
            $this->addParamToConfig('apps.fasteditor.enable', '0', 'Включить Apps.FastEditor', 1);
        }

        if (!$this->check_config_item('apps.shop.city_enable')) {
            $this->addParamToConfig('apps.shop.city_enable', '0', 'Указание города в свойствах товара', 1);
        }


        if (!$this->check_config_item('apps.realtybuyorder.enable')) {
            $this->addParamToConfig('apps.realtybuyorder.enable', '0', 'Включить Realtybuyorder', 1);
        }

        if (!$this->check_config_item('apps.realtybuyorder.text_after_send')) {
            $this->addParamToConfig('apps.realtybuyorder.text_after_send', 'Ваш заказ принят', 'Текст после заказа через Realtybuyorder');
        }

        if (!$this->check_config_item('apps.realtycsv.enable')) {
            $this->addParamToConfig('apps.realtycsv.enable', '0', 'Включить Apps.RealtyCSV', 1);
        }

        if (!$this->check_config_item('apps.realtylog.enable')) {
            $this->addParamToConfig('apps.realtylog.enable', '0', 'Включить Apps.Realtylog', 1);
        }

        if (!$this->check_config_item('apps.shop.enable')) {
            $this->addParamToConfig('apps.shop.enable', '0', 'Включить Apps.Shop', 1);
        }

        if (!$this->check_config_item('apps.page.enable')) {
            $this->addParamToConfig('apps.page.enable', '1', 'Включить Apps.Page', 1);
        }

        if (!$this->check_config_item('apps.realtypro.youtube')) {
            $this->addParamToConfig('apps.realtypro.youtube', '1', 'Разрешить youtube-ролики в объявлении', 1);
        }

        if (!$this->check_config_item('apps.yml.shop_name')) {
            $this->addParamToConfig('apps.yml.shop_name', 'Some Shop', 'Короткое название магазина');
        }
        if (!$this->check_config_item('apps.yml.company_name')) {
            $this->addParamToConfig('apps.yml.company_name', 'Some Company', 'Полное наименование компании');
        }
        if (!$this->check_config_item('apps.yml.shop_platform_name')) {
            $this->addParamToConfig('apps.yml.shop_platform_name', 'Some CMS', 'Система управления контентом');
        }
        if (!$this->check_config_item('apps.yml.shop_platform_version')) {
            $this->addParamToConfig('apps.yml.shop_platform_version', '1.0', 'Версия CMS');
        }
        if (!$this->check_config_item('apps.yml.shop_development_team')) {
            $this->addParamToConfig('apps.yml.shop_development_team', 'Some Dev Team', 'Наименование агентства, которое оказывает техническую поддержку интернет-магазину');
        }
        if (!$this->check_config_item('apps.yml.shop_development_team_email')) {
            $this->addParamToConfig('apps.yml.shop_development_team_email', 'Some Email', 'Контактный адрес разработчиков CMS');
        }








        if (!$this->check_config_item('apps.news.enable')) {
            $this->addParamToConfig('apps.news.enable', '1', 'Включить News.Apps', 1);
        }
        if (!$this->check_config_item('apps.news.front.per_page')) {
            $this->addParamToConfig('apps.news.front.per_page', '5', 'Количество новостей на страницу');
        }
        if (!$this->check_config_item('apps.plan.enable')) {
            $this->addParamToConfig('apps.plan.enable', '0', 'Включить Plan.Apps', 1);
        }
        if (!$this->check_config_item('apps.balcony.enable')) {
            $this->addParamToConfig('apps.balcony.enable', '0', 'Включить Balcony.Apps', 1);
        }
        if (!$this->check_config_item('apps.sanuzel.enable')) {
            $this->addParamToConfig('apps.sanuzel.enable', '0', 'Включить Sanuzel.Apps', 1);
        }


        if (!$this->check_config_item('apps.company.timelimit')) {
            $this->addParamToConfig('apps.company.timelimit', '0', 'Скрывать объявления компаний у которых закончился доступ в ЛК', 1);
        }

        if (!$this->check_config_item('apps.watermark.image')) {
            $this->addParamToConfig('apps.watermark.image', 'watermark.gif', 'Название файла изображения для водяного знака, путь до картинок /img/watermark/');
        }


        if (!$this->check_config_item('apps.billing.enable')) {
            $this->addParamToConfig('apps.billing.enable', '0', 'Включить Billing.Apps', 1);
        }

        if (!$this->check_config_item('apps.realtyspecial.enable')) {
            $this->addParamToConfig('apps.realtyspecial.enable', '0', 'Включить RealtySpecial.Apps', 1);
        }

        if (!$this->check_config_item('apps.realtypro.enable')) {
            $this->addParamToConfig('apps.realtypro.enable', '0', 'Включить RealtyPro.Apps', 1);
        }

        if (!$this->check_config_item('apps.company.enable')) {
            $this->addParamToConfig('apps.company.enable', '0', 'Включить Company.Apps', 1);
        }

        if (!$this->check_config_item('apps.company.best')) {
            $this->addParamToConfig('apps.company.best', '0', 'Использовать лучшие предложения Company.Apps', 1);
        }




        if (!$this->check_config_item('apps.realty.ajax_region_refresh')) {
            $this->addParamToConfig('apps.realty.ajax_region_refresh', '1', 'Ajax - обновление региона', 1);
        }

        if (!$this->check_config_item('apps.realty.ajax_city_refresh')) {
            $this->addParamToConfig('apps.realty.ajax_city_refresh', '1', 'Ajax - обновление города', 1);
        }

        if (!$this->check_config_item('apps.realty.ajax_district_refresh')) {
            $this->addParamToConfig('apps.realty.ajax_district_refresh', '1', 'Ajax - обновление района', 1);
        }

        if (!$this->check_config_item('apps.realty.ajax_metro_refresh')) {
            $this->addParamToConfig('apps.realty.ajax_metro_refresh', '1', 'Ajax - обновление метро', 1);
        }

        if (!$this->check_config_item('apps.realty.ajax_street_refresh')) {
            $this->addParamToConfig('apps.realty.ajax_street_refresh', '1', 'Ajax - обновление улицы', 1);
        }

        if (!$this->check_config_item('apps.shop.recipients_list')) {
            $this->addParamToConfig('apps.shop.recipients_list', '', 'Магазин. Список уведомляемых получателей при добавлении объявления пользователем');
        }

        if (!$this->check_config_item('apps.realtypro.admin.items_per_page')) {
            $this->addParamToConfig('apps.realtypro.admin.items_per_page', '10', 'Недвижимость. Админка. Количество позиций на странице');
        }
        if (!$this->check_config_item('apps.shop.admin.products_per_page')) {
            $this->addParamToConfig('apps.shop.admin.products_per_page', '10', 'Магазин. Количество продуктов на странице в админке');
        }
        if (!$this->check_config_item('apps.shop.front.products_per_page')) {
            $this->addParamToConfig('apps.shop.front.products_per_page', '10', 'Магазин. Количество продуктов на странице в ЛК пользователя');
        }





        //form
        if (!$this->check_config_item('country_in_form')) {
            $this->addParamToConfig('country_in_form', '0', 'Выбор страны в форме объявления', 1);
        }
        if (!$this->check_config_item('region_in_form')) {
            $this->addParamToConfig('region_in_form', '0', 'Выбор региона в форме объявления', 1);
        }
        if (!$this->check_config_item('city_in_form')) {
            $this->addParamToConfig('city_in_form', '1', 'Выбор города в форме объявления', 1);
        }
        if (!$this->check_config_item('metro_in_form')) {
            $this->addParamToConfig('metro_in_form', '1', 'Выбор метро в форме объявления', 1);
        }

        if (!$this->check_config_item('district_in_form')) {
            $this->addParamToConfig('district_in_form', '1', 'Выбор района в форме объявления', 1);
        }
        if (!$this->check_config_item('street_in_form')) {
            $this->addParamToConfig('street_in_form', '1', 'Выбор улицы в форме объявления', 1);
        }
        if (!$this->check_config_item('optype_in_form')) {
            $this->addParamToConfig('optype_in_form', '0', 'Выбор контракта в форме поиска', 1);
        }

        if (!$this->check_config_item('uploader_type')) {
            $this->addParamToConfig('uploader_type', '', 'Тип апплоадера для загрузки картинок. При неуказанном значении по умолчанию используется Uploadify. <a href="http://www.sitebill.ru/uploader-type.html" target="_blank">Что это?</a>');
        }
        if (!$this->check_config_item('link_street_to_city')) {
            $this->addParamToConfig('link_street_to_city', '0', 'Включить привязку улиц к городу', 1);
        }
        if (!$this->check_config_item('user_add_street_enable')) {
            $this->addParamToConfig('user_add_street_enable', '0', 'Пользователи могут добавлять улицы', 1);
        }
        if (!$this->check_config_item('allow_callme_timelimits')) {
            $this->addParamToConfig('allow_callme_timelimits', '0', 'Добавить возможность указания допустимого для звонка времени', 1);
        }
        if (!$this->check_config_item('allow_additional_stationary_number')) {
            $this->addParamToConfig('allow_additional_stationary_number', '0', 'Добавить дополнительный номер городского телефона', 1);
        }
        if (!$this->check_config_item('allow_additional_mobile_number')) {
            $this->addParamToConfig('allow_additional_mobile_number', '0', 'Добавить дополнительный номер мобильного телефона', 1);
        }
        if (!$this->check_config_item('post_form_agreement_text')) {
            $this->addParamToConfig('post_form_agreement_text', 'Я, ознакомлен(а), что данная заявка будет доставлена по всем Агентствам недвижимости которые зарегистрированы на сайте.', 'Текст соглашения после формы');
        }
        if (!$this->check_config_item('ajax_form_in_admin')) {
            $this->addParamToConfig('ajax_form_in_admin', '1', 'Режим ajax в формах администратора', 1);
        }
        if (!$this->check_config_item('ajax_form_in_user')) {
            $this->addParamToConfig('ajax_form_in_user', '1', 'Режим ajax в формах личного кабинета', 1);
        }
        if (!$this->check_config_item('menu_type')) {
            $this->addParamToConfig('menu_type', 'purecss', 'Тип верхнего меню (purecss/slidemenu)');
        }
        if (!$this->check_config_item('allow_tags_search_frontend')) {
            $this->addParamToConfig('allow_tags_search_frontend', '0', 'Разрешить поиск по тэгам во фронтенде', 1);
        }

        ////////////
        if (!$this->check_config_item('news_image_big_width')) {
            $this->addParamToConfig('news_image_big_width', '800', 'Новости - ширина большой картинки');
        }
        if (!$this->check_config_item('news_image_big_height')) {
            $this->addParamToConfig('news_image_big_height', '600', 'Новости - высота большой картинки');
        }
        if (!$this->check_config_item('news_image_preview_width')) {
            $this->addParamToConfig('news_image_preview_width', '300', 'Новости - ширина превью картинки');
        }
        if (!$this->check_config_item('news_image_preview_height')) {
            $this->addParamToConfig('news_image_preview_height', '300', 'Новости - высота превью картинки');
        }
        ////////////
        if (!$this->check_config_item('gallery_image_big_width')) {
            $this->addParamToConfig('gallery_image_big_width', '800', 'Галерея - ширина большой картинки');
        }
        if (!$this->check_config_item('gallery_image_big_height')) {
            $this->addParamToConfig('gallery_image_big_height', '600', 'Галерея - высота большой картинки');
        }
        if (!$this->check_config_item('gallery_image_preview_width')) {
            $this->addParamToConfig('gallery_image_preview_width', '300', 'Галерея - ширина превью картинки');
        }
        if (!$this->check_config_item('gallery_image_preview_height')) {
            $this->addParamToConfig('gallery_image_preview_height', '300', 'Галерея - высота превью картинки');
        }

        //$this->addParamToConfig('use_topic_actual_days', '0', 'Использовать настраиваемую актуальность объявлений по категориям', 1);





        /*
          if ( !$this->check_config_item('autoreg_enable') ) {
          $this->addParamToConfig('autoreg_enable','0','Включить авторегистрацию <a href="http://www.sitebill.ru/autoreg.html" target="_blank">что это такое?</a>');
          }
         */



        /* images */
        /*
          if ( !$this->check_config_item('shop_product_image_big_width') ) {
          $this->addParamToConfig('shop_product_image_big_width','800','Ширина большой картинки товара');
          }
          if ( !$this->check_config_item('shop_product_image_big_height') ) {
          $this->addParamToConfig('shop_product_image_big_height','600','Высота большой картинки товара');
          }
          if ( !$this->check_config_item('shop_product_image_preview_width') ) {
          $this->addParamToConfig('shop_product_image_preview_width','180','Ширина маленькой картинки товара');
          }
          if ( !$this->check_config_item('shop_product_image_preview_height') ) {
          $this->addParamToConfig('shop_product_image_preview_height','180','Высота маленькой картинки товара');
          }
         */
        /* images */

        /*
          if ( !$this->check_config_item('city_in_form') ) {
          $this->addParamToConfig('city_in_form','1','Выбор города в форме объявления');
          }
         */

        if (!$this->check_config_item('robokassa_server')) {
            $this->addParamToConfig('robokassa_server', 'https://auth.robokassa.ru/Merchant/Index.aspx', 'Адрес службы приема платежей robokassa.ru');
        }

        if (!$this->check_config_item('robokassa_login')) {
            $this->addParamToConfig('robokassa_login', 'robokassa_login', 'Логин для robokassa.ru');
        }

        if (!$this->check_config_item('robokassa_password1')) {
            $this->addParamToConfig('robokassa_password1', 'robokassa_password1', 'Пароль 1 для robokassa.ru');
        }
        if (!$this->check_config_item('robokassa_password2')) {
            $this->addParamToConfig('robokassa_password2', 'robokassa_password2', 'Пароль 2 для robokassa.ru');
        }

        if (!$this->check_config_item('robokassa_testmode')) {
            $this->addParamToConfig('robokassa_testmode', '0', 'Тестовый режим модуля робокассы', 1);
        }
        if (!$this->check_config_item('robokassa_testpassword1')) {
            $this->addParamToConfig('robokassa_testpassword1', 'robokassa_testpassword1', 'Тестовый пароль 1 для robokassa.ru');
        }
        if (!$this->check_config_item('robokassa_testpassword2')) {
            $this->addParamToConfig('robokassa_testpassword2', 'robokassa_testpassword2', 'Тестовый пароль 2 для robokassa.ru');
        }

        if (!$this->check_config_item('use_smtp')) {
            $this->addParamToConfig('use_smtp', '0', 'Отправка почты через smtp. <a href="http://www.sitebill.ru/smtp.html" target="_blank">Что это такое?</a>');
        }

        if (!$this->check_config_item('use_smtp_ssl')) {
            $this->addParamToConfig('use_smtp_ssl', '1', 'Использовать SSL при подключении к SMTP', 1);
        }

        if (!$this->check_config_item('smtp1_server')) {
            $this->addParamToConfig('smtp1_server', 'smtp.yandex.ru', 'SMTP-сервер для отправки заявок');
        }

        if (!$this->check_config_item('smtp1_login')) {
            $this->addParamToConfig('smtp1_login', 'rumantic.coder', 'SMTP-login');
        }

        if (!$this->check_config_item('smtp1_password')) {
            $this->addParamToConfig('smtp1_password', '123456', 'SMTP-password');
        }

        if (!$this->check_config_item('smtp1_port')) {
            $this->addParamToConfig('smtp1_port', '587', 'SMTP-port');
        }
        if (!$this->check_config_item('smtp1_from')) {
            $this->addParamToConfig('smtp1_from', 'rumantic.coder@yandex.ru', 'SMTP-от кого <br>(это поле должно соответствовать имени и адресу домена)');
        }



        if (!$this->check_config_item('editor1')) {
            $this->addParamToConfig('editor1', 'bbeditor', 'WYSIWYG-редактор1');
        }

        if (!$this->check_config_item('show_demo_banners')) {
            $this->addParamToConfig('show_demo_banners', '0', 'Показывать рекламные баннеры sitebill.ru', 1);
        }
        if (!$this->check_config_item('use_topic_publish_status')) {
            $this->addParamToConfig('use_topic_publish_status', '0', 'Использовать переключатель активности для категорий', 1);
        }

        if (!$this->check_config_item('use_topic_linker')) {
            $this->addParamToConfig('use_topic_linker', '0', 'Включить переадресацию категорий <a href="http://wiki.sitebill.ru/index.php?title=Use_topic_linker" target="_blank">?</a>');
        }

        if (!$this->check_config_item('email_as_login')) {
            $this->addParamToConfig('email_as_login', '0', 'Использовать email в качестве логина <a href="http://wiki.sitebill.ru/index.php?title=email_as_login" target="_blank">?</a>');
        }

        if (!$this->check_config_item('min_payment_sum')) {
            $this->addParamToConfig('min_payment_sum', '0', 'Минимальная сумма для пополнения счета');
        }


        if (!$this->check_config_item('query_cache_enable')) {
            $this->addParamToConfig('query_cache_enable', '0', 'Включить кэширование SQL-запросов');
        }
        if (!$this->check_config_item('query_cache_time')) {
            $this->addParamToConfig('query_cache_time', '60', 'Длительность хранения кэша SQL-запросов в секундах');
        }

        if (!$this->check_config_item('apps.contact.phone')) {
            $this->addParamToConfig('apps.contact.phone', '', 'Телефон на сайте');
        }
        if (!$this->check_config_item('apps.contact.email')) {
            $this->addParamToConfig('apps.contact.email', '', 'Email на сайте');
        }
        if (!$this->check_config_item('apps.contact.address')) {
            $this->addParamToConfig('apps.contact.address', '', 'Адрес на сайте');
        }
        if (!$this->check_config_item('apps.contact.skype')) {
            $this->addParamToConfig('apps.contact.skype', '', 'Skype на сайте');
        }
        if (!$this->check_config_item('use_native_file_name_on_uploadify')) {
            $this->addParamToConfig('use_native_file_name_on_uploadify', '0', 'Сохранять физические названия загруженных файлов', 1);
        }

        if (!$this->check_config_item('ups_price')) {
            $this->addParamToConfig('ups_price', '0', 'Цена одного поднятия');
        }
        if (!$this->check_config_item('vip_cost')) {
            $this->addParamToConfig('vip_cost', '0', 'Цена VIP-объявления за 1 день');
        }
        if (!$this->check_config_item('premium_cost')) {
            $this->addParamToConfig('premium_cost', '0', 'Цена Премиум-объявления за 1 день');
        }
        if (!$this->check_config_item('bold_cost')) {
            $this->addParamToConfig('bold_cost', '0', 'Цена выделения объявления за 1 день');
        }
        if (!$this->check_config_item('vip_rotator_number')) {
            $this->addParamToConfig('vip_rotator_number', '5', 'Количество VIP-объявлений в колонке');
        }
        if (!$this->check_config_item('apps_cache_disable')) {
            $this->addParamToConfig('apps_cache_disable', '0', 'Выключить кэш приложений', 1);
        }
        if (!$this->check_config_item('sql_paranoid_mode')) {
            $this->addParamToConfig('sql_paranoid_mode', '1', 'Режим максимальной безопасности для входных параметров', 1);
        }
        if (!$this->check_config_item('dadata_autocomplete_force')) {
            $this->addParamToConfig('dadata_autocomplete_force', '0', 'Принудительно добавить параметр autocomplete для гео-параметров, если в форме есть опция dadata=1. <a href=http://wiki.sitebill.ru/index.php?title=%D0%A0%D0%B0%D1%81%D0%BF%D0%BE%D0%B7%D0%BD%D0%B0%D0%B2%D0%B0%D0%BD%D0%B8%D0%B5_%D0%B0%D0%B4%D1%80%D0%B5%D1%81%D0%BE%D0%B2_%D1%87%D0%B5%D1%80%D0%B5%D0%B7_dadata.ru>?</a>', 1);
        }
        $this->addParamToConfig('apps.realty.default_frontend_route', '/grid/data', 'Маршрут по-умолчанию для angular-фронтенда', 0, array('public' => true));
        $this->addParamToConfig('apps.realty.enable_guest_mode', '0', 'Включить guest-mode для angular-фронтенда', 1, array('public' => true));

        //if (!$this->check_config_item('use_metaphone')) {
            //$this->addParamToConfig('use_metaphone', '0', 'Использовать metaphone', 1);
        //}
        
    }

    private function reloadCheckConfigStructure() {
        $DBC = DBC::getInstance();
        $query = "select * from " . DB_PREFIX . "_config";
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                self::$check_config_array[$ar['config_key']] = '1';
            }
        }
    }

}
