<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Table admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class table_admin extends Object_Manager
{
    /**
     * @var Admin_Table_Helper
     */
    public $helper;

    /**
     * Constructor
     */
    function __construct($realty_type = false)
    {
        parent::__construct();
        $this->table_name = 'table';
        $this->app_title = 'Редактор форм';
        $this->action = 'table';
        $this->primary_key = 'table_id';
        $this->section = '';
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/table_model.php');
        $Object = new Table_Model();
        $this->data_model = $Object->get_model();
        /*
          require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
          $config_admin = new config_admin();

          if ( !$config_admin->check_config_item('apps.table.additional_filtering') ) {
          $config_admin->addParamToConfig('apps.table.additional_filtering','0','Включить фильтрацию видимости полей по до дополнительнуму параметру');
          }

          if ( !$config_admin->check_config_item('apps.table.additional_filtering_field') ) {
          $config_admin->addParamToConfig('apps.table.additional_filtering_field','','Системное имя дополнительного поля в модели data');
          }
         */
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php');
        $this->helper = new Admin_Table_Helper();
        if (!$this->helper->check_table_exist('table') or !$this->helper->check_table_exist('columns')) {
            $this->install();
        }


        $section = $this->getRequestValue('section');
        if ($section === NULL) {
            if (isset($_SESSION['_table_section']) && $_SESSION['_table_section'] != '') {
                $this->section = $_SESSION['_table_section'];
            } else {
                $this->section = 'table';
                $_SESSION['_table_section'] = 'table';
            }
        } else {
            $this->section = $this->getRequestValue('section');
            $_SESSION['_table_section'] = $this->section;
        }


        //$this->install();
    }

    public function _preload()
    {
        if (1 == $this->getConfigValue('block_user_search_forms')) {

        } else {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/search_forms_admin.php';
            $SFA = new search_forms_admin();
            $this->template->assert('local_search_forms', $SFA->get_local_search_forms());
        }
    }

    function main()
    {
        $rs = '';
        if ($this->section == 'search_forms') {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/search_forms_admin.php';
            $SFA = new search_forms_admin();
            $rs .= $SFA->main();
            return $rs;
        } elseif ($this->section == 'gridmanager') {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/gridmanager_admin.php';
            $SFA = new gridmanager_admin();
            $rs .= $SFA->main();
            return $rs;
        } elseif ($this->section == 'front_gridmanager') {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/front_gridmanager_admin.php';
            $SFA = new front_gridmanager_admin();
            $rs .= $SFA->main();
            return $rs;
        } elseif ($this->section == 'modelmanager') {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/modelmanager_admin.php';
            $SFA = new modelmanager_admin();
            $rs .= $SFA->main();
            return $rs;
        }/* elseif($this->section=='formcompounder'){
          require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/formcompounder_admin.php';
          $SFA=new formcompounder_admin();
          $rs.=$SFA->main();
          return $rs;
          } */
        $rs .= parent::main();

        if (!$this->helper->check_table_exist('table') or !$this->helper->check_table_exist('columns')) {
            $rs .= '<h1>Приложение не установлено. <a href="?action=table&do=structure&subdo=install">Установить</a></h1>';
        }

        return $rs;
    }

    protected function _fieldrulesbytopicAction()
    {
        if (!defined('DEVMODE')) {
            return $this->_defaultAction();
        }
        $table_id = (int)$this->getRequestValue('table_id');
        $columns = array();
        if (1 === intval($this->getConfigValue('apps.table.additional_filtering'))) {
            $use_add_filtering = true;
            $optype_column_info = array();
            $optype_column_variants = array();
        } else {
            $use_add_filtering = false;
        }


        $DBC = DBC::getInstance();
        $query = 'SELECT columns_id, active, table_id, name, title, type, group_id, active_in_topic, active_in_optype FROM ' . DB_PREFIX . '_columns WHERE table_id=? ORDER BY active DESC, sort_order ASC';
        $stmt = $DBC->query($query, array($table_id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                if ($ar['active_in_topic'] != '' && $ar['active_in_topic'] != '0') {
                    preg_match_all('/(\d+)/', $ar['active_in_topic'], $matches_all);
                    $ar['active_in_topic'] = $matches_all[1];
                } else {
                    $ar['active_in_topic'] = array();
                }
                if ($use_add_filtering) {

                    if ($ar['active_in_optype'] != '' && $ar['active_in_optype'] != '0') {
                        preg_match_all('/(\d+)/', $ar['active_in_optype'], $matches_all);

                        $ar['active_in_optype'] = $matches_all[1];
                    } else {
                        $ar['active_in_optype'] = array();
                    }

                    if ($ar['name'] == 'optype') {
                        $optype_column_info = $ar;
                    }
                }
                $columns[$ar['columns_id']] = $ar;
            }
        }


        if ($use_add_filtering && !empty($optype_column_info)) {
            if ($optype_column_info['type'] == 'select_box') {
                $query = 'SELECT select_data FROM ' . DB_PREFIX . '_columns WHERE table_id=? AND columns_id=?';
                $stmt = $DBC->query($query, array($table_id, $optype_column_info['columns_id']));
                $ar = $DBC->fetch($stmt);
                $optype_column_info['select_data'] = $this->helper->unserializeSelectData($ar['select_data']);
                unset($optype_column_info['select_data'][0]);
                $optype_column_variants = $optype_column_info['select_data'];
            }
        }

        //print_r($optype_column_info);

        if (isset($_POST['submit'])) {
            //print_r($_POST);
            $post = $_POST;
            if (!empty($columns)) {
                if ($use_add_filtering) {
                    $query = 'UPDATE ' . DB_PREFIX . '_columns SET `active_in_topic`=?, `active_in_optype`=? WHERE columns_id=?';
                } else {
                    $query = 'UPDATE ' . DB_PREFIX . '_columns SET `active_in_topic`=? WHERE columns_id=?';
                }

                foreach ($columns as $ar) {

                    if (isset($post['active_in_topic'][$ar['columns_id']])) {
                        $aint = implode(',', $post['active_in_topic'][$ar['columns_id']]);
                    } else {
                        $aint = '';
                    }

                    if ($use_add_filtering) {
                        if (isset($post['active_in_optype'][$ar['columns_id']])) {
                            $aino = implode(',', $post['active_in_optype'][$ar['columns_id']]);
                        } else {
                            $aino = '';
                        }
                    }

                    if ($use_add_filtering) {
                        $stmt = $DBC->query($query, array($aint, $aino, $ar['columns_id']));
                        //var_dump($stmt);
                        //print_r(array($aint, $aino, $ar['columns_id']));
                    } else {
                        $stmt = $DBC->query($query, array($aint, $ar['columns_id']));
                    }
                }
            }
            header('location: ' . SITEBILL_MAIN_URL . '/admin/?action=table&do=fieldrulesbytopic&table_id=' . $table_id);
            exit();
        } else {
            $ret = array();

            if (!empty($columns)) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_implements.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                $SM = new Structure_Manager();
                $structure = $SM->loadCategoryStructure();
                foreach ($columns as $ar) {
                    $ch = $SM->getCategoryCheckboxes('active_in_topic[' . $ar['columns_id'] . ']', $ar['active_in_topic']);
                    $ar['ch'] = $ch;
                    $och = $this->getOptypeCheckboxes('active_in_optype[' . $ar['columns_id'] . ']', $ar['active_in_optype'], $optype_column_variants);
                    $ar['och'] = $och;
                    $ret[$ar['columns_id']] = $ar;
                }
            }


            global $smarty;
            $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/template/columns_rules_grid_topic.tpl';
            $smarty->assign('columns', $ret);
            $smarty->assign('table_id', $table_id);
            return $smarty->fetch($tpl);
        }


        global $smarty;
        $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/template/columns_rules_grid_topic.tpl';
        $smarty->assign('columns', $ret);
        $smarty->assign('table_id', $table_id);
        //$smarty->assign('CategoryCheckboxes', $ch);
        //$smarty->assign('linned_structure', $linned_structure);
        //$smarty->assign('rules', $rules);
        return $smarty->fetch($tpl);
    }

    protected function getOptypeCheckboxes($name, $vals = array(), $variants = array())
    {
        $rs = '';
        $rs .= '<div class="checkbox_collection">';
        $rs .= '<a href="#" class="checkbox_collection_decheck">Очистить все</a>';
        foreach ($variants as $k => $v) {

            $rs .= '<div class="ait_bc">';
            $rs .= '<div class="ait_bc_h"><input name="' . $name . '[]" value="' . $k . '" type="checkbox"' . (in_array($k, $vals) ? ' checked="checked"' : '') . ' /> ' . $v . '</div>';
            $rs .= '</div>';
        }
        $rs .= '</div>';
        return $rs;
    }

    protected function _fieldrulesbygroupAction()
    {
        if (!defined('DEVMODE')) {
            return $this->_defaultAction();
        }
        $table_id = (int)$this->getRequestValue('table_id');
        $ret = array();
        $groups = array();
        $rules = array();

        $DBC = DBC::getInstance();
        $query = 'SELECT columns_id, active, table_id, name, title, type, group_id FROM ' . DB_PREFIX . '_columns WHERE table_id=? ORDER BY active DESC, sort_order ASC';
        $stmt = $DBC->query($query, array($table_id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret[$ar['columns_id']] = $ar;
            }
        }

        $columns_ids = array_keys($ret);
        $query = 'SELECT * FROM ' . DB_PREFIX . '_group';
        $stmt = $DBC->query($query, array($table_id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $groups[] = $ar;
            }
        }

        foreach ($ret as $cid => $v) {
            $g = $v['group_id'];
            if ($g !== '' && $g !== '0') {
                $_g = explode(',', $g);
                foreach ($groups as $gk => $gv) {
                    if (in_array($gv['group_id'], $_g)) {
                        $rules[$cid][$gv['group_id']] = 1;
                    } else {
                        $rules[$cid][$gv['group_id']] = 0;
                    }
                }
            } else {
                foreach ($groups as $gk => $gv) {
                    $rules[$cid][$gv['group_id']] = 1;
                }
            }
        }

        //$groups[]=array('name'=>'Гость', 'group_id'=>0);
        global $smarty;
        $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/template/columns_rules_grid_group.tpl';
        $smarty->assign('columns', $ret);
        $smarty->assign('groups', $groups);
        $smarty->assign('rules', $rules);
        return $smarty->fetch($tpl);
    }

    protected function _fieldrulesAction()
    {
        if (!defined('DEVMODE')) {
            return $this->_defaultAction();
        }
        $table_id = (int)$this->getRequestValue('table_id');
        $ret = array();
        $groups = array();

        $DBC = DBC::getInstance();
        $query = 'SELECT columns_id, active, table_id, name, title, type FROM ' . DB_PREFIX . '_columns WHERE table_id=? ORDER BY active DESC, sort_order ASC';
        $stmt = $DBC->query($query, array($table_id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret[$ar['columns_id']] = $ar;
            }
        }

        $columns_ids = array_keys($ret);
        $query = 'SELECT * FROM ' . DB_PREFIX . '_group';
        $stmt = $DBC->query($query, array($table_id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $groups[] = $ar;
            }
        }
        $groups[] = array('name' => 'Гость', 'group_id' => 0);
        global $smarty;
        $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/template/columns_rules_grid.tpl';
        $smarty->assign('columns', $ret);
        $smarty->assign('groups', $groups);
        $smarty->assign('rules', $this->loadAllRules($columns_ids));
        return $smarty->fetch($tpl);
        print_r($ret);

        return '_fieldrulesAction';
    }

    protected function _importtableAction()
    {

        $table_id = (int)$this->getRequestValue('table_id');

        if (isset($_POST['submit'])) {

            if (!empty($_FILES)) {
                $tempFile = $_FILES['importfile']['tmp_name'];
                $targetPath = SITEBILL_DOCUMENT_ROOT . '/cache/upl' . '/';
                $arr = explode('.', $_FILES['importfile']['name']);
                $ext = strtolower(end($arr));
                if ($ext != 'json') {
                    return 'Импорт не удался: расширение файла не *.json';
                }
                $content = file_get_contents($tempFile);
                $columns = json_decode($content, true);

                if (isset($columns['elements']) && count($columns['elements']) > 0) {
                    $errors = '';
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php';
                    $CM = new columns_admin();
                    $model = $CM->data_model;
                    $tablec = $CM->table_name;

                    $DBC = DBC::getInstance();
                    //$query = 'SELECT columns_id, name FROM ' . DB_PREFIX . '_' . $tablec . ' WHERE table_id=?';
                    //$stmt = $DBC->query($query, array($table_id));


                    foreach ($columns['elements'] as $k => $v) {
                        $v['table_id'] = $table_id;

                        foreach ($model[$tablec] as $it => $iv) {
                            $model[$tablec][$it]['value'] = $v[$it];
                        }
                        $CM->clearError();
                        $CM->add_data($model[$tablec]);
                        if ($CM->getError()) {
                            $errors .= $CM->getError() . '<br>';
                        } else {
                            $errors .= 'Свойство ' . $v['name'] . ' импортировано<br>';
                        }
                    }
                    return 'Импорт завершен<br>' . $errors;
                } else {
                    return 'Импорт не удался, columns = ' . '<pre>' . var_export($columns, true) . '</pre>';
                }
                //var_dump();
                //echo '<pre>';
                //print_r($columns);
            } else {
                return 'Нет файла';
            }
        } else {
            $this->template->assign('table_id', $table_id);
            global $smarty;
            //$smarty->assign('table_id', $table_id);
            return $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/template/import_table.tpl');
        }

        exit();
    }

    protected function _exporttableAction()
    {
        /*if (!defined('DEVMODE')) {
            return $this->_defaultAction();
        }*/
        $table_id = (int)$this->getRequestValue('table_id');

        $columns = array();

        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_columns WHERE `table_id`=?';
        $stmt = $DBC->query($query, array($table_id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ar['table_id'] = 0;
                $ar['columns_id'] = 0;
                $columns[] = $ar;
            }
        }

        if (count($columns) > 0) {
            $query = 'SELECT name FROM ' . DB_PREFIX . '_table WHERE `table_id`=?';
            $stmt = $DBC->query($query, array($table_id));
            $ar = $DBC->fetch($stmt);
            $file_name = $ar['name'] . '.model.json';
            header('Content-Type: text');
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
            echo json_encode(array('elements' => $columns), JSON_PRETTY_PRINT);
        }
        exit();
    }


    /**
     * Управление активностью полей в разделах в одной общей таблице
     * @return type
     * @global type $smarty
     */
    protected function _activitymatrixAction()
    {
        if (!defined('DEVMODE')) {
            return $this->_defaultAction();
        }
        $table_id = (int)$this->getRequestValue('table_id');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $cs = $Structure_Manager->loadCategoryStructure();

        $active_topic_id = intval($this->getRequestValue('active_topic_id'));
        if ($active_topic_id > 0) {
            //print_r($cs);
            $ch = $Structure_Manager->get_all_childs($active_topic_id, $cs);
            $ch[] = $active_topic_id;
            foreach ($cs['catalog'] as $k => $v) {
                if (!in_array($k, $ch)) {
                    unset($cs['catalog'][$k]);
                }
            }
            //$cs['childs'][0] = array($topic_id);
        }

        $DBC = DBC::getInstance();

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $rules = $_POST['rule'];

            if ($active_topic_id > 0 && empty($rules)) {
                $query = 'SELECT `columns_id`, `active_in_topic` FROM ' . DB_PREFIX . '_columns WHERE `table_id`=? AND `active`=1 AND `type` != ?';
                $stmt = $DBC->query($query, array($table_id, 'select_box_structure'));
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        if ($ar['active_in_topic'] != '' && $ar['active_in_topic'] != '0') {
                            $acts = explode(',', $ar['active_in_topic']);
                        } else {
                            $acts = array();
                        }
                        $columns[$ar['columns_id']] = array_diff($acts, $ch);
                    }
                }
                $query = 'UPDATE ' . DB_PREFIX . '_columns SET `active_in_topic`=? WHERE `columns_id`=?';
                foreach ($columns as $cid => $v) {
                    if (!empty($v)) {
                        $s = implode(',', $v);
                    } else {
                        $s = '';
                    }
                    $stmt = $DBC->query($query, array($s, $cid));
                }
            } elseif (empty($rules)) {
                $query = 'UPDATE ' . DB_PREFIX . '_columns SET `active_in_topic`=? WHERE `table_id`=? AND `active`=1 AND `type` != ?';
                $stmt = $DBC->query($query, array('', $table_id, 'select_box_structure'));
            } else {
                $query = 'SELECT `columns_id`, `active_in_topic` FROM ' . DB_PREFIX . '_columns WHERE `table_id`=? AND `active`=1 AND `type` != ?';
                $stmt = $DBC->query($query, array($table_id, 'select_box_structure'));
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        if ($ar['active_in_topic'] != '' && $ar['active_in_topic'] != '0') {
                            $acts = explode(',', $ar['active_in_topic']);
                        } else {
                            $acts = array();
                        }
                        if ($active_topic_id > 0) {
                            $columns[$ar['columns_id']] = array_diff($acts, $ch);
                        } else {
                            $columns[$ar['columns_id']] = $acts;
                        }
                    }
                }


                $query = 'UPDATE ' . DB_PREFIX . '_columns SET `active_in_topic`=? WHERE `columns_id`=?';
                foreach ($columns as $cid => $v) {
                    if ($active_topic_id > 0) {


                        if (!isset($rules[$cid]) || (isset($rules[$cid]) && count($rules[$cid]) == count($ch))) {
                            if (empty($columns[$cid])) {

                            } else {
                                $columns[$cid] = array_merge($columns[$cid], array_keys($rules[$cid]));
                            }
                        } else {
                            $columns[$cid] = array_merge($columns[$cid], array_keys($rules[$cid]));
                        }

                    } else {
                        if (!isset($rules[$cid])) {
                            $columns[$cid] = array();
                        } elseif (isset($rules[$cid]) && count($rules[$cid]) == count($cs['catalog'])) {
                            $columns[$cid] = array();
                        } else {
                            $columns[$cid] = array_keys($rules[$cid]);
                        }
                        //$stmt = $DBC->query($query, array($columns[$cid], $cid));
                    }
                    //print_r($rules[$cid]);
                    //print_r($columns[$cid]);
                    if (!empty($columns[$cid])) {
                        $s = implode(',', $columns[$cid]);
                    } else {
                        $s = '';
                    }
                    $stmt = $DBC->query($query, array($s, $cid));

                }
            }
        }


        $columns = array();

        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_columns WHERE `table_id`=? AND `active`=1';
        $stmt = $DBC->query($query, array($table_id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $act = explode(',', $ar['active_in_topic']);
                if (count($act) == 1 && $act[0] == 0) {
                    $act = array();
                }
                $columns[$ar['columns_id']] = array('name' => $ar['name'], 'title' => $ar['title'], 'active_in_topic' => $act);
            }
        }

        $this->template->assign('columns', $columns);

        /* require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
          $Structure_Manager = new Structure_Manager();
          $cs=$Structure_Manager->loadCategoryStructure(); */
        $names = array();
        foreach ($cs['catalog'] as $k => $v) {
            $names[$v['id']] = $this->get_category_breadcrumbs_string(array('topic_id' => $v['id']), $cs);
        }
        $this->template->assign('active_topic_id', $active_topic_id);
        $this->template->assign('topics', $names);
        $this->template->assign('table_id', $table_id);
        //print_r($names);

        global $smarty;
        return $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/template/activity_matrix.tpl');
    }

    /*
      protected function _check_ml_fieldsAction(){
      $table_id=intval($this->getRequestValue('table_id'));

      $langs=array_values(Multilanguage::availableLanguages());


      $exising_columns=array();
      $need_ml_columns=array();
      $columns_to_add=array();

      $DBC=DBC::getInstance();
      $query='SELECT columns_id, name, is_ml FROM '.DB_PREFIX.'_columns WHERE table_id=?';
      $stmt=$DBC->query($query, array($table_id));
      if($stmt){
      while($ar=$DBC->fetch($stmt)){
      if($ar['is_ml']==1){
      $need_ml_columns[]=$ar;
      }
      $exising_columns[]=$ar['name'];
      }
      }

      if(!empty($need_ml_columns)){
      foreach($need_ml_columns as $nmc){
      foreach($langs as $lng){
      $new_name=$nmc['name'].'_'.$lng;
      if(!isset($exising_columns[$new_name])){
      $columns_to_add[$nmc['columns_id']][]=$new_name;
      }
      }
      }
      }

      if(!empty($columns_to_add)){
      require_once SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php';
      $CM=new columns_admin();
      $opts=array(
      'required'=>0,
      'is_ml'=>0
      );
      foreach ($columns_to_add as $columns_id=>$new_names){
      foreach ($new_names as $new_name){
      $CM->copyColumn($columns_id, $new_name, $opts);
      }
      }

      $query = 'SELECT name FROM '.DB_PREFIX.'_table WHERE table_id=? LIMIT 1';
      $stmt=$DBC->query($query, array($table_id));
      if($stmt){
      $ar=$DBC->fetch($stmt);
      $this->helper->update_table($ar['name']);
      $_POST['table_name']=$ar['name'];
      }


      }

      return $this->_defaultAction();
      }
     */

    protected function _add_langsAction()
    {
        if (1 !== intval($this->getConfigValue('apps.language.use_langs'))) {
            return $this->_defaultAction();
        }
        $langs = array_values(Multilanguage::availableLanguages());

        $default_lng = '';
        if (1 == $this->getConfigValue('apps.language.use_default_as_ru')) {
            $default_lng = 'ru';
        } elseif ('' != trim($this->getConfigValue('apps.language.use_as_default'))) {
            $default_lng = trim($this->getConfigValue('apps.language.use_as_default'));
        }

        if ($default_lng != '') {
            foreach ($langs as $k => $lng) {
                if ($lng == $default_lng) {
                    unset($langs[$k]);
                    break;
                }
            }
        }

        if (!empty($langs)) {
            $needle_filds = array('title', 'hint', 'value', 'title_default', 'select_data', 'tab');

            $queries = array();
            foreach ($needle_filds as $nf) {
                foreach ($langs as $lng) {
                    if ($nf != 'select_data') {
                        $queries[] = 'ALTER TABLE  `' . DB_PREFIX . '_columns` ADD  `' . $nf . '_' . $lng . '` VARCHAR( 255 );';
                    } else {
                        $queries[] = 'ALTER TABLE  `' . DB_PREFIX . '_columns` ADD  `' . $nf . '_' . $lng . '` TEXT;';
                    }
                }
            }
            $DBC = DBC::getInstance();

            foreach ($queries as $query) {
                $stmt = $DBC->query($query);
            }
        }


        return $this->_defaultAction();
    }

    protected function _edit_langsAction()
    {
        if (1 !== intval($this->getConfigValue('apps.language.use_langs'))) {
            return $this->_defaultAction();
        }
        $langs = array_values(Multilanguage::availableLanguages());
        $needle_filds = array('title', 'hint', 'value', 'title_default', 'select_data', 'tab');
        $table_id = (int)$this->getRequestValue('table_id');
        $DBC = DBC::getInstance();

        $ret = '';

        if ('post' == strtolower($_SERVER['REQUEST_METHOD']) && !isset($_POST['submit'])) {
            $ret .= '<div class="alert">Возможно что-то пошло не так. Запрос передан не полностью. Может быть некоторые настройки сервера (php:max_input_vars) не позволяют выполнить его.</div>';
        } elseif (isset($_POST['submit'])) {

            $selected_fields = array();

            foreach ($needle_filds as $nf) {
                $selected_fields[] = $nf;
                foreach ($langs as $lng) {
                    $selected_fields[] = $nf . '_' . $lng;
                }
            }
            $postdata = $_POST['c'];

            foreach ($postdata as $cid => $vals) {
                $keys = array_keys($vals);
                $values = array_values($vals);
                $values[] = $cid;
                $query = 'UPDATE `' . DB_PREFIX . '_columns` SET `' . implode('`=?, `', $keys) . '`=? WHERE columns_id=?';
                $stmt = $DBC->query($query, $values);
                //echo $DBC->getLastError();
            }
        }


        $selected_fields = array();

        foreach ($needle_filds as $nf) {
            $selected_fields[] = $nf;
            foreach ($langs as $lng) {
                $selected_fields[] = $nf . '_' . $lng;
            }
        }

        $query = 'SELECT `name`, `columns_id`, `' . implode('`,`', $selected_fields) . '` FROM `' . DB_PREFIX . '_columns` WHERE table_id=? ORDER BY `sort_order`';


        $stmt = $DBC->query($query, array($table_id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $data[$ar['columns_id']] = $ar;
            }
        }

        if (!empty($data)) {
            $ret .= '<form method="post" action="' . SITEBILL_MAIN_URL . '/admin/">';
            $ret .= '<input type="hidden" value="table" name="action" />';
            $ret .= '<input type="hidden" value="edit_langs" name="do" />';
            $ret .= '<input type="hidden" value="' . $table_id . '" name="table_id" />';
            $ret .= '<table>';

            foreach ($data as $row) {
                $ret .= '<thead><tr>';
                $ret .= '<th>' . $row['name'] . '</th>';
                $ret .= '<th>def</th>';
                foreach ($langs as $lng) {
                    $ret .= '<th>' . $lng . '</th>';
                }
                $ret .= '</tr></thead>';
                foreach ($needle_filds as $nk => $nf) {
                    $ret .= '<tr>';
                    $ret .= '<td>' . $nf . '</td>';
                    if ($nf == 'select_data') {
                        $ret .= '<td><textarea name="c[' . $row['columns_id'] . '][' . $nf . ']">' . $row[$nf] . '</textarea></td>';
                    } else {
                        $ret .= '<td><input type="text" value="' . $row[$nf] . '" name="c[' . $row['columns_id'] . '][' . $nf . ']" /></td>';
                    }

                    foreach ($langs as $lng) {
                        if ($nf == 'select_data') {
                            $ret .= '<td><textarea name="c[' . $row['columns_id'] . '][' . ($nf . '_' . $lng) . ']">' . $row[$nf . '_' . $lng] . '</textarea></td>';
                        } else {
                            if ($row[$nf . '_' . $lng] == '') {
                                $tr_value = $this->google_translate($row[$nf], $lng);
                            } else {
                                $tr_value = $row[$nf . '_' . $lng];
                            }

                            $ret .= '<td><input type="text" value="' . $tr_value . '" name="c[' . $row['columns_id'] . '][' . ($nf . '_' . $lng) . ']" /></td>';
                        }
                    }
                    $ret .= '</tr>';
                }
                $ret .= '<tr><td><hr /></td></tr>';
            }
            $ret .= '</table>';
            $ret .= '<input type="submit" value="Сохранить" name="submit" />';
            $ret .= '</form>';
        }
        return $ret;
    }

    protected function _copytableAction()
    {
        /*if (!defined('DEVMODE')) {
            return $this->_defaultAction();
        }*/
        $table_id = (int)$this->getRequestValue('table_id');
        if (isset($_POST['submit'])) {
            $new_name = trim($this->getRequestValue('name'));
            if ($new_name == '') {
                return 'Укажите имя модели';
            }
            $DBC = DBC::getInstance();
            $query = 'SELECT COUNT(table_id) AS _cnt FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE name=?';
            $stmt = $DBC->query($query, array($new_name));
            $ar = $DBC->fetch($stmt);
            $id = 0;
            if ((int)$ar['_cnt'] == 0) {
                $query = 'INSERT INTO ' . DB_PREFIX . '_table (name) VALUES (?)';
                $stmt = $DBC->query($query, array($new_name));
                if ($stmt) {
                    $id = $DBC->lastInsertId();
                }
            } else {
                return 'Модель с таким именем уже существует';
            }

            if ($id != 0) {
                $columns = array();
                $query = 'SELECT * FROM ' . DB_PREFIX . '_columns WHERE `table_id`=?';
                $stmt = $DBC->query($query, array($table_id));
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $columns[] = $ar;
                    }
                }
            }
            if (!empty($columns)) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/columns_model.php');
                $Object = new columns_Model();
                $data_model = $Object->get_model();
                foreach ($columns as $column) {
                    $column['table_id'] = $id;
                    unset($column['columns_id']);
                    $ckeys = array_keys($column);
                    $cvals = array_values($column);
                    $query = 'INSERT INTO ' . DB_PREFIX . '_columns (`' . implode('`,`', $ckeys) . '`) VALUES (' . implode(',', array_fill(0, count($cvals), '?')) . ')';
                    $stmt = $DBC->query($query, $cvals);
                }
            }

            $ret = $this->_defaultAction();
        } else {
            $ret = 'Укажите название новой модели в которую будет произведено копирование. Название следует указывать без префикса базы данных<br /><form action="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=copytable&table_id=' . $table_id . '" method="post"><input type="text" name="name" /><br /><input type="submit" name="submit" value="Скопировать" /></form>';
        }
        return $ret;
        $ret = array();
        $groups = array();

        $DBC = DBC::getInstance();
        $query = 'SELECT columns_id, active, table_id, name, title, type FROM ' . DB_PREFIX . '_columns WHERE table_id=? ORDER BY active DESC, sort_order ASC';
        $stmt = $DBC->query($query, array($table_id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret[$ar['columns_id']] = $ar;
            }
        }

        $columns_ids = array_keys($ret);
        $query = 'SELECT * FROM ' . DB_PREFIX . '_group';
        $stmt = $DBC->query($query, array($table_id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $groups[] = $ar;
            }
        }
        $groups[] = array('name' => 'Гость', 'group_id' => 0);
        global $smarty;
        $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/template/columns_rules_grid.tpl';
        $smarty->assign('columns', $ret);
        $smarty->assign('groups', $groups);
        $smarty->assign('rules', $this->loadAllRules($columns_ids));
        return $smarty->fetch($tpl);
    }

    protected function _showAction()
    {
        $table_id = intval($this->getRequestValue('table_id'));
        //$langs=array_values(Multilanguage::availableLanguages());
        //$control_langs=(count($langs)>0 ? true : false);
        $rs = '';
        $control_langs = false;
        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
            $control_langs = true;
        }

        $DBC = DBC::getInstance();

        $query = "SELECT * FROM " . DB_PREFIX . "_table WHERE table_id=?";
        $stmt = $DBC->query($query, array($table_id));

        if (!$stmt) {
            echo 'ERROR';
            return false;
        }
        $table_info = $DBC->fetch($stmt);


        $rs .= '<style>#model_table td {background-color: White;}</style><div class="row-fluid">
		<div class="span12">
		<table id="model_table" class="table table-bordered">
		<thead>
		<tr>
		<th class="center">
		<label>
		<input type="checkbox" class="ace">
		<span class="lbl"></span>
		</label>
		</th>
		<th>Domain</th>
		<th>Price</th>
		<th class="hidden-480">Clicks</th>
		
		<th class="hidden-phone">
		<i class="icon-time bigger-110 hidden-phone"></i>
		Update
		</th>
		<th class="hidden-480">Status</th>
		
		<th></th>
		</tr>
		</thead>
		
		<tbody class="applied">';
        $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/table/js/interface.js"></script>';
        $cols = $this->get_columns_list_m($table_info['table_id']);
        if (false !== $cols) {
            $rs .= $cols;
        }

        $rs .= '</tbody>
		</table>
		</div><!-- /span -->
		</div>';

        $ra = array();


        if (count($ra) > 0) {

            //global $smarty;
            //$smarty->assign('tables_list', $ra);
            //$rs.=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/template/tables_list.tpl');


            $rs .= '<div class="accordion" id="accordion2">';
            foreach ($ra as $primary_key_value => $item_array) {
                $rs .= '<div class="accordion-group">';
                $rs .= '<div class="accordion-heading">';
                $rs .= '<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#formeditoracc_' . $item_array['name'] . '">';
                $rs .= $item_array['name'] . ($item_array['description'] != '' ? ' (' . $item_array['description'] . ')' : '');
                $rs .= '</a>';
                if (defined('DEVMODE')) {
                    $rs .= '<a class="btn btn-success" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=fieldrules&table_id=' . $primary_key_value . '" title="Редактировать"><i class="icon-white icon-user"></i> <sup>beta</sup></a> ';
                }

                $rs .= '<a class="btn btn-info" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=edit&table_id=' . $primary_key_value . '" title="Редактировать"><i class="icon-white icon-pencil"></i></a> ';
                $rs .= '<a class="btn btn-info" href="' . SITEBILL_MAIN_URL . '/admin/?action=columns&do=new&table_id=' . $primary_key_value . '" title="Добавить колонку"><i class="icon-white icon-plus"></i></a> ';
                $rs .= '<a class="btn btn-danger" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=delete&table_id=' . $primary_key_value . '" title="Удалить" onclick="if ( confirm(\'Действительно хотите удалить запись?\') ) {return true;} else {return false;}"><i class="icon-white icon-remove"></i></a> ';

                if (isset($custom_admin_entity_menu[$item_array['name']])) {
                    $rs .= '<a class="btn btn-success" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=handler&table=' . $item_array['name'] . '" title="Зарегистрированный обработчик"><i class="icon-white icon-asterisk"></i></a> ';
                } else {
                    $rs .= '<a class="btn" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=handler&table=' . $item_array['name'] . '" title="Обработчик по-умолчанию"><i class="icon-white icon-asterisk"></i></a> ';
                }

                if ($this->helper->check_table_exist($item_array['name'])) {
                    //$rs .= '<a class="btn btn-info update_table" href="#" title="Сохранить сортировку"><i class="icon-white icon-refresh"></i></a> ';
                    $rs .= '<a class="btn btn-info" href="?action=table&do=structure&subdo=update_table&table_name=' . $item_array['name'] . '" title="Обновить таблицу" ><i class="icon-white icon-list-alt"></i></a>';
                } else {
                    $rs .= '<a href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=structure&subdo=create_table&table_name=' . $item_array['name'] . '" title="Создать таблицу"><img src="' . SITEBILL_MAIN_URL . '/apps/table/img/create_table.png" border="0" class="admin_control"></a>';
                }
                if (defined('DEVMODE')) {
                    $rs .= '<a class="btn btn-warning" href="?action=table&do=structure&subdo=clear_table&table_name=' . $item_array['name'] . '" title="Очистить" onclick="if ( confirm(\'Действительно хотите очистить таблицу? Будут удалены все поля таблицы не входящие в текущую модель и данные, содержащиеся в этих полях.\') ) {return true;} else {return false;}"><i class="icon-white icon-cog"></i><sup>beta</sup></a> ';
                    $rs .= '<a class="btn" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=copytable&table_id=' . $primary_key_value . '" title="Копировать модель (BETA)"><i class="icon-white icon-book"></i> <sup>beta</sup></a> ';
                    $rs .= '<a class="btn" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=exporttable&table_id=' . $primary_key_value . '" title="Экспортировать модель (BETA)">Экспорт <sup>beta</sup></a> ';
                    $rs .= '<a class="btn" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=importtable&table_id=' . $primary_key_value . '" title="Импортировать модель (BETA)">Импорт <sup>beta</sup></a> ';
                }


                $rs .= '<div class="btn-group"><a class="btn dropdown-toggle" data-toggle="dropdown" href="#">Быстрое добавление<span class="caret"></span></a>
  <ul class="dropdown-menu">
    <li><a href="' . SITEBILL_MAIN_URL . '/admin/?action=columns&do=add_meta&table_id=' . $primary_key_value . '">Добавить мета поля (3 поля)</a></li>
    		
  </ul>
</div> ';
                if ($control_langs) {
                    $rs .= '<a class="btn" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=edit_langs&table_id=' . $primary_key_value . '" title="Быстрые переводы">Быстрые переводы</a> ';
                }

                $rs .= '</div>';
                $rs .= '<div id="formeditoracc_' . $item_array['name'] . '" class="accordion-body' . ($table_info['name'] == $item_array['name'] ? ' in' : '') . ' collapse">';
                $rs .= '<div class="accordion-inner">';
                $cols = $this->get_columns_list($item_array);
                if (false !== $cols) {
                    $rs .= $cols;
                }

                $rs .= '</div>';
                $rs .= '</div>';
                $rs .= '</div>';
            }

            $rs .= '</div>';
        } else {
            $rs .= '<br><br>Записей не найдено';
        }
        return $rs;
    }

    protected function _deleteAction()
    {
        $this->delete_data($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
        $DBC = DBC::getInstance();
        $query = 'DELETE FROM ' . DB_PREFIX . '_columns WHERE table_id=?';
        $stmt = $DBC->query($query, array(intval($this->getRequestValue($this->primary_key))));
        return $this->grid();
    }

    public function force_delete_table($table_id)
    {
        $table_record = $this->load_by_id($table_id);
        $this->delete_data($this->table_name, $this->primary_key, $table_id);
        $DBC = DBC::getInstance();
        $query = 'DELETE FROM ' . DB_PREFIX . '_columns WHERE table_id=?';
        $stmt = $DBC->query($query, array(intval($table_id)), $rows, $success);
        if (!$success) {
            throw new Exception($DBC->getLastError());
        }

        $table_name = $table_record['name']['value'];

        $query = "DROP TABLE IF EXISTS `" . DB_PREFIX . "_" . $table_name . "`";
        $stmt = $DBC->query($query, array(), $rows, $success);
        if (!$success) {
            throw new Exception($DBC->getLastError());
        }
    }

    private function loadAllRules($columns_ids)
    {
        $ret = array();
        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_group_rule WHERE columns_id IN (' . implode(',', $columns_ids) . ')';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret['edit'][$ar['columns_id']][$ar['group_id']] = $ar['edit_status'];
                $ret['view'][$ar['columns_id']][$ar['group_id']] = $ar['view_status'];
            }
        }

        return $ret;
    }

    function create_customentity_record(
        $table,
        $entity_title,
        $is_public = 0,
        $alias = '',
        $list_tpl = '',
        $view_tpl = '',
        $sortby = '',
        $sortorder = '',
        $per_page = 0,
        $list_title = '',
        $view_title = ''
    )
    {
        $DBC = DBC::getInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . '_customentity (`entity_name`, `entity_title`, `is_public`, `alias`, `list_tpl`, `view_tpl`, `sortby`, `sortorder`, `per_page`, `list_title`, `view_title`) VALUES (?,?,?,?,?,?,?,?,?,?,?)';

        $params = array();
        $params[] = $table;
        $params[] = trim($entity_title);
        $params[] = intval($is_public);
        $params[] = trim($alias);
        $params[] = trim($list_tpl);
        $params[] = trim($view_tpl);
        $params[] = trim($sortby);
        $params[] = trim($sortorder);
        $params[] = intval($per_page);
        $params[] = trim($list_title);
        $params[] = trim($view_title);
        $stmt = $DBC->query($query, $params);
        if ($DBC->getLastError()) {
            $this->writeLog($DBC->getLastError());
            return false;
        }
        return true;
    }

    function check_entity_exist($entity_name)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/customentity/admin/admin.php');
        $custom_admin_entity_menu = customentity_admin::getEntityList();
        return isset($custom_admin_entity_menu[$entity_name]);
    }

    public function handlerAction_public()
    {
        $this->disable_redirect();
        $this->_handlerAction();
    }

    protected function _handlerAction()
    {
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/customentity/admin/admin.php')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/customentity/admin/admin.php');
            $custom_admin_entity_menu = customentity_admin::getEntityList();
        } else {
            header('location:' . SITEBILL_MAIN_URL . '/admin/?action=table');
        }
        $subdo = $this->getRequestValue('subdo');
        $table = $this->getRequestValue('table');

        if ($subdo == 'add_handler' && '' != $table && '' != $this->getRequestValue('entity_title') && !isset($custom_admin_entity_menu[$table])) {
            $DBC = DBC::getInstance();
            $query = 'INSERT INTO ' . DB_PREFIX . '_customentity (`entity_name`, `entity_title`, `is_public`, `alias`, `list_tpl`, `view_tpl`, `sortby`, `sortorder`, `per_page`, `list_title`, `view_title`) VALUES (?,?,?,?,?,?,?,?,?,?,?)';

            $params = array();
            $params[] = $table;
            $params[] = trim($this->getRequestValue('entity_title'));
            $params[] = intval($this->getRequestValue('is_public'));
            $params[] = trim($this->getRequestValue('alias'));
            $params[] = trim($this->getRequestValue('list_tpl'));
            $params[] = trim($this->getRequestValue('view_tpl'));
            $params[] = trim($this->getRequestValue('sortby'));
            $params[] = trim($this->getRequestValue('sortorder'));
            $params[] = intval($this->getRequestValue('per_page'));
            $params[] = trim($this->getRequestValue('list_title'));
            $params[] = trim($this->getRequestValue('view_title'));
            $stmt = $DBC->query($query, $params);
            //echo $DBC->getLastError();
            if ($this->isRedirectDisabled()) {
                return true;
            }
            header('location:' . SITEBILL_MAIN_URL . '/admin/?action=table');
            exit();
        } elseif ($subdo == 'update_handler' && '' != $table && '' != $this->getRequestValue('entity_title') && isset($custom_admin_entity_menu[$table])) {
            $DBC = DBC::getInstance();
            $query = 'UPDATE ' . DB_PREFIX . '_customentity SET `entity_title`=?, `is_public`=?, `alias`=?, `list_tpl`=?, `view_tpl`=?, `sortby`=?, `sortorder`=?, `per_page`=?, `list_title`=?, `view_title`=? WHERE `entity_name`=?';
            $stmt = $DBC->query($query, array($this->getRequestValue('entity_title'), intval($this->getRequestValue('is_public')), trim($this->getRequestValue('alias')), trim($this->getRequestValue('list_tpl')), trim($this->getRequestValue('view_tpl')), trim($this->getRequestValue('sortby')), trim($this->getRequestValue('sortorder')), intval($this->getRequestValue('per_page')), trim($this->getRequestValue('list_title')), trim($this->getRequestValue('view_title')), $table));
            if ($this->isRedirectDisabled()) {
                return true;
            }
            header('location:' . SITEBILL_MAIN_URL . '/admin/?action=table');
            exit();
        } elseif ($subdo == 'delete_handler' && '' != $table && isset($custom_admin_entity_menu[$table])) {
            $DBC = DBC::getInstance();
            $query = 'DELETE FROM ' . DB_PREFIX . '_customentity WHERE entity_name=?';
            $stmt = $DBC->query($query, array($table));
            if ($this->isRedirectDisabled()) {
                return true;
            }
            header('location:' . SITEBILL_MAIN_URL . '/admin/?action=table');
            exit();
        }

        if (isset($custom_admin_entity_menu[$table])) {
            $rs = '<form action="' . SITEBILL_MAIN_URL . '/admin/" method="post">
			<fieldset>
		    <legend>Пользовательский обработчик для <b>' . $table . '</b> существует. Хотите удалить?</legend>
		    <label>Имя модели</label>
		    <input type="text" disabled="disabled" name="entity_name" value="' . $table . '">
		    		<label>Название</label>
		    <input type="text" name="entity_title" value="' . $custom_admin_entity_menu[$table]['entity_title'] . '">
			<span class="help-block">Название приложения, которое будет выводиться в меню.</span>
		    		<label>Публичный</label>
		    <input type="checkbox" name="is_public" value="1"' . ($custom_admin_entity_menu[$table]['is_public'] == 1 ? ' checked="checked"' : '') . '>
		    <span class="help-block">Отметка этой галочки даст возможность к базовому выводу этого приложения в публичную часть сайта по указанному алиасу.</span>
		    				<label>Alias</label>
		    <input type="text" name="alias" value="' . $custom_admin_entity_menu[$table]['alias'] . '">
		    		<span class="help-block">Алиас по которому будет доступно приложение.</span>
		    		<label>Шаблон списка</label>
		    <input type="text" name="list_tpl" value="' . $custom_admin_entity_menu[$table]['list_tpl'] . '">
		    		<span class="help-block">Имя файла шаблона списка объектов приложения.</span>
		    		<label>Шаблон объекта</label>
		    <input type="text" name="view_tpl" value="' . $custom_admin_entity_menu[$table]['view_tpl'] . '">
		    		<span class="help-block">Имя файла шаблона карточки объекта приложения.</span>
		    <label>Поле сортировки</label>
		    <input type="text" name="sortby" value="' . $custom_admin_entity_menu[$table]['sortby'] . '">
		    		<span class="help-block">Системное имя поля из модели объекта по которому будет проводиться сортировка. По-умолчанию это поле-первичный ключ.</span>
		    		 <label>Направление сортировки</label>
		    <input type="text" name="sortorder" value="' . $custom_admin_entity_menu[$table]['sortorder'] . '">
		    		<span class="help-block">Направление сортировки. Используйте asc для сортировки по возрастанию и desc для сортировки по спаданию значений. По-умолчанию это "desc".</span>
		    		<label>Количество на страницу</label>
		    <input type="text" name="per_page" value="' . $custom_admin_entity_menu[$table]['per_page'] . '">
		    		<span class="help-block">Количество объектов на одной странице в списочном выводе.</span>
		    		<label>Заголовок списка</label>
		    <input type="text" name="list_title" value="' . $custom_admin_entity_menu[$table]['list_title'] . '">
		    		<label>Заголовок страницы объекта</label>
		    <input type="text" name="view_title" value="' . $custom_admin_entity_menu[$table]['view_title'] . '">
		    		<input type="hidden" name="action" value="table">
		    <input type="hidden" name="do" value="handler">
		    <input type="hidden" name="subdo" value="update_handler">
		    <input type="hidden" name="table" value="' . $table . '">
		    <br />
		    <button type="submit" class="btn btn-danger">Изменить</button> 		
		    
		    		
		  </fieldset>
    		</form>
		    	<form action="' . SITEBILL_MAIN_URL . '/admin/" method="post">
			<input type="hidden" name="action" value="table">
		    <input type="hidden" name="do" value="handler">
		    <input type="hidden" name="subdo" value="delete_handler">
		    <input type="hidden" name="table" value="' . $table . '">
		    <br />
		    <button type="submit" class="btn btn-danger">Удалить</button>
		   	</form>
		    		<a class="btn btn-warning" href="' . SITEBILL_MAIN_URL . '/admin/?action=table">Вернуться к списку</a> ';
            return $rs;
            return 'Handler setted. <a href="">Delete</a>?';
        } else {
            $rs = '<form action="' . SITEBILL_MAIN_URL . '/admin/" method="post">
			<fieldset>
		    <legend>Пользовательский обработчик для <b>' . $table . '</b> не создан. Хотите создать?</legend>
		    <label>Имя модели</label>
		    <input type="text" disabled="disabled" name="entity_name" value="' . $table . '">
		    		<label>Название</label>
		    <input type="text" name="entity_title" value="">
		    		<span class="help-block">Название приложения, которое будет выводиться в меню.</span>
		    		<label>Публичный</label>
		    <input type="checkbox" name="is_public" value="1">
		    <span class="help-block">Отметка этой галочки даст возможность к базовому выводу этого приложения в публичную часть сайта по указанному алиасу.</span>
		    				<label>Alias</label>
		    <input type="text" name="alias" value="">
		    		<span class="help-block">Алиас по которому будет доступно приложение.</span>
		    		<label>Шаблон списка</label>
		    <input type="text" name="list_tpl" value="">
		    		<span class="help-block">Имя файла шаблона списка объектов приложения.</span>
		    		<label>Шаблон объекта</label>
		    <input type="text" name="view_tpl" value="">
		    		<span class="help-block">Имя файла шаблона карточки объекта приложения.</span>
		    <label>Поле сортировки</label>
		    <input type="text" name="sortby" value="">
		    		<span class="help-block">Системное имя поля из модели объекта по которому будет проводиться сортировка. По-умолчанию это поле-первичный ключ.</span>
		    		 <label>Направление сортировки</label>
		    <input type="text" name="sortorder" value="">
		    		<span class="help-block">Направление сортировки. Используйте asc для сортировки по возрастанию и desc для сортировки по спаданию значений. По-умолчанию это "desc".</span>
		    		<label>Количество на страницу</label>
		    <input type="text" name="per_page" value="">
		    		<span class="help-block">Количество объектов на одной странице в списочном выводе.</span>
		    <label>Заголовок списка</label>
		    <input type="text" name="list_title" value="">
		    		<label>Заголовок страницы объекта</label>
		    <input type="text" name="view_title" value="">
		    <input type="hidden" name="action" value="table">
		    <input type="hidden" name="do" value="handler">
		    <input type="hidden" name="subdo" value="add_handler">
		  	<input type="hidden" name="table" value="' . $table . '">
		    <br />
		    <button type="submit" class="btn btn-primary">Создать</button> 
		    <a class="btn btn-warning" href="' . SITEBILL_MAIN_URL . '/admin/?action=table">Нет, не нужно</a>
		  </fieldset>
    		</form>';
            return $rs;
        }
        return '_handlerAction';
    }

    function init_from_php($table_name)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $kvartira_model = $data_model->get_kvartira_model();
        $street_model = $data_model->get_street_model();
        $city_model = $data_model->get_city_model();
        //print_r($street_model);
        $this->create_table_and_columns($kvartira_model, 'data');
        $this->create_table_and_columns($street_model, 'street');
        $this->create_table_and_columns($city_model, 'city');
        //print_r($kvartira_model);
    }

    protected function _init_dummyAction()
    {
        if ($this->request()->get('table_name') != '') {
            $rs = $this->init_dummy_model($this->request()->get('table_name'));
            if ($this->getError()) {
                return $this->getError();
            }
            $rs .= '<br>Модель создана успешно';
        } else {
            $tables_list = $this->getOnlyTablesList();

            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/modelmanager_admin.php';
            $SFA = new modelmanager_admin();

            $model_list = $SFA->loadAllModels();
            $rs = _e('Для создания (обновления) модели кликните по названию таблицы.
                    <br>Если модель уже есть, то при обновлении будут добавлены новые поля с типом safe_string и статусом active = 0, если поля есть в таблице, но нет в модели. 
                    <br><span class="error">Красные</span> - модели не существует, а таблица есть');
            $rs .= '<br>';
            $rs .= '<br>';
            foreach ($tables_list as $table_name) {
                $class = '';
                if (!$model_list[$table_name]) {
                    $class = "error";
                }
                $rs .= '<a href="?action=table&do=init_dummy&table_name=' . $table_name . '" class="' . $class . '">' . $table_name . '</a><br>';
            }
        }
        return $rs;
    }


    function init_dummy_model($table_name)
    {
        $columns = $this->getOnlyTableFields($table_name);

        $dummy_model[$table_name] = [];
        foreach ($columns as $column) {
            $dummy_model[$table_name] = $dummy_model[$table_name] +
                \system\factories\model\Item::base(
                    \system\types\model\Dictionary::SAFE_STRING,
                    $column,
                    $column
                );
        }
        return $this->create_table_and_columns($dummy_model, $table_name, 0);
    }

    function create_table_and_columns($model, $table_name, $default_active_value = 1)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php');
        $columns_admin = new columns_admin();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        $table_id = $data_model->get_value_id_by_name('table', 'name', 'table_id', $table_name);

        if (!$table_id) {
            $table_id = $this->add_table_record($table_name);
        }

        $columns_admin->data_model['columns']['table_id']['value'] = $table_id;

        $rs = '';

        foreach ($model[$table_name] as $item_id => $item) {

            $columns_admin->data_model['columns']['name']['value'] = $item['name'];
            $columns_admin->data_model['columns']['active']['value'] = $default_active_value;
            $columns_admin->data_model['columns']['title']['value'] = $item['title'];
            $columns_admin->data_model['columns']['type']['value'] = $item['type'];
            $columns_admin->data_model['columns']['value']['value'] = $item['value'];
            $columns_admin->data_model['columns']['primary_key_name']['value'] = $item['primary_key_name'];

            $columns_admin->data_model['columns']['primary_key_table']['value'] = $item['primary_key_table'];
            $columns_admin->data_model['columns']['value_string']['value'] = $item['value_string'];
            $columns_admin->data_model['columns']['query']['value'] = $item['query'];
            $columns_admin->data_model['columns']['value_name']['value'] = $item['value_name'];
            $columns_admin->data_model['columns']['title_default']['value'] = $item['title_default'];
            $columns_admin->data_model['columns']['value_default']['value'] = $item['value_default'];

            $columns_admin->data_model['columns']['value_table']['value'] = $item['value_table'];
            $columns_admin->data_model['columns']['value_primary_key']['value'] = $item['value_primary_key'];
            $columns_admin->data_model['columns']['value_field']['value'] = $item['value_field'];
            $columns_admin->data_model['columns']['assign_to']['value'] = $item['assign_to'];
            $columns_admin->data_model['columns']['dbtype']['value'] = $item['dbtype'];
            if (isset($item['select_data']) && is_array($item['select_data']) && !empty($item['select_data'])) {
                $str = '';
                foreach ($item['select_data'] as $k => $v) {
                    $str .= '{' . $k . '~~' . $v . '}';
                }
                $columns_admin->data_model['columns']['select_data']['value'] = serialize($item['select_data']);
                $columns_admin->data_model['columns']['select_data']['value'] = $str;
            } else {
                $columns_admin->data_model['columns']['select_data']['value'] = '';
            }
            //$columns_admin->data_model['columns']['select_values']['value'] = ((is_array($item['select_values']) && !empty($item['select_values'])) ? implode('|',$item['select_values']) : '');
            $columns_admin->data_model['columns']['table_name']['value'] = $item['table_name'];
            $columns_admin->data_model['columns']['primary_key']['value'] = $item['primary_key'];
            $columns_admin->data_model['columns']['primary_key_value']['value'] = $item['primary_key_value'];
            $columns_admin->data_model['columns']['action']['value'] = $item['action'];
            $columns_admin->data_model['columns']['hint']['value'] = $item['hint'];
            $columns_admin->data_model['columns']['tab']['value'] = $item['tab'];
            if ($item['active_in_topic'] != '') {
                $columns_admin->data_model['columns']['active_in_topic']['value'] = $item['active_in_topic'];
            } else {
                unset($columns_admin->data_model['columns']['active_in_topic']);
            }
            if ($item['group_id'] != '') {
                $columns_admin->data_model['columns']['group_id']['value'] = $item['group_id'];
            } else {
                unset($columns_admin->data_model['columns']['group_id']);
            }

            $columns_admin->data_model['columns']['sort_order']['value'] = 0;
            if ($item['required'] == 'on') {
                $item['required'] = 1;
            } else {
                $item['required'] = 0;
            }
            $columns_admin->data_model['columns']['required']['value'] = $item['required'];

            if ($item['unique'] == 'on') {
                $item['unique'] = 1;
            } else {
                $item['unique'] = 0;
            }
            $columns_admin->data_model['columns']['unique']['value'] = $item['unique'];

            if (isset($item['parameters']) && $item['parameters'] != '' && is_array($item['parameters']) && !empty($item['parameters'])) {
                $columns_admin->data_model['columns']['parameters']['value'] = serialize($item['parameters']);
            } else {
                unset($columns_admin->data_model['columns']['parameters']);
            }

            //echo '<hr>devider'.$item['table_name'].'<br>';
            //echo '<pre>';
            //print_r($columns_admin->data_model['columns']);
            //echo '</pre>';


            if ($columns_admin->check_data($columns_admin->data_model['columns'])) {

                $column_id = $columns_admin->add_data($columns_admin->data_model['columns']);
                if ($columns_admin->getError()) {
                    //echo $columns_admin->GetErrorMessage().'<br>';
                    $rs .= $columns_admin->GetErrorMessage() . '<br>';
                } else {
                    $columns_admin->data_model['columns']['columns_id']['value'] = $column_id;
                    $columns_admin->data_model['columns']['sort_order']['value'] = $column_id;
                    $columns_admin->edit_data($columns_admin->data_model['columns'], 0, $column_id);
                }
            } else {
                //echo $columns_admin->GetErrorMessage().'<br>';
                $rs .= $columns_admin->GetErrorMessage() . '<br>';
            }
            //unset($item);
        }
        return $rs;
    }

    function add_table_record($table_name)
    {
        $this->data_model['table']['name']['value'] = $table_name;

        $new_record_id = $this->add_data($this->data_model['table']);
        if ($this->getError()) {
            return $this->GetErrorMessage();
        }
        return $new_record_id;
    }

    function structure_processor()
    {
        switch ($this->getRequestValue('subdo')) {
            case 'create_table':
                $rs = $this->helper->create_table($this->getRequestValue('table_name'));
                break;

            case 'update_table':
                $rs = $this->helper->update_table($this->getRequestValue('table_name'));
                break;

            case 'clear_table':
                $rs = $this->helper->clear_table($this->getRequestValue('table_name'));
                break;

            case 'install':
                $rs = $this->install();
                $rs .= $this->init_from_php('data');
                break;

            case 'init_from_php':
                $rs = $this->init_from_php('data');
                break;
        }
        return $rs;
    }

    function getTopMenu()
    {
        $rs = '<div> ';
        $rs .= '<a href="?action=table&section=table" class="btn btn-primary">Список таблиц</a> ';
        $rs .= '<a href="?action=table&section=table&do=new" class="btn btn-primary">Добавить таблицу</a> ';
        $rs .= '<a href="?action=columns&do=new" class="btn btn-primary">Добавить колонку в таблицу</a>	';
        $rs .= '<a href="?action=table&section=gridmanager" class="btn btn-primary">Grid Manager</a> ';
        $rs .= '<a href="?action=table&section=search_forms" class="btn btn-primary">Формы поиска</a> ';
        //$rs .= '<a href="?action=table&section=modelmanager" class="btn btn-primary">Model Manager</a> ';
        $rs .= '<a href="?action=table&section=front_gridmanager" class="btn btn-primary">Сетки</a> ';
        $rs .= '<a href="?action=table&do=init_dummy" class="btn btn-primary">Таблицы без моделей</a> ';


        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
            $rs .= '<a href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=add_langs" class="btn btn-primary">Добавить языковые поля в модель редактора форм</a> ';
        }


        $rs .= '<div> ';
        return $rs;
    }

    function grid($params = array(), $default_params = array())
    {
        if (self::$replace_grid_with_angular) {
            return $this->angular_grid('models-editor');
        }

        $rs = $this->get_table_list();
        return $rs;
    }

    function loadTablesInfo()
    {
        $DBC = DBC::getInstance();

        // groups list
        $groups = array();
        $query = 'SELECT `name`, `group_id` FROM ' . DB_PREFIX . '_group';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $groups[$ar['group_id']] = $ar['name'];
            }
        }

        // custom entities list
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/customentity/admin/admin.php')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/customentity/admin/admin.php');
            $custom_admin_entity_menu = customentity_admin::getEntityList();
        }

        $tables = array();
        $query = "SELECT * FROM " . DB_PREFIX . "_table ORDER BY name ASC, table_id DESC";

        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ar['_firstletter'] = mb_strtolower(mb_substr($ar['name'], 0, 1, 'utf-8'), 'utf-8');

                if (isset($custom_admin_entity_menu[$ar['name']])) {
                    $ar['_customentityhandler'] = 1;
                } else {
                    $ar['_customentityhandler'] = 0;
                }

                $tables[$ar['table_id']] = $ar;
            }
        }


        if (!empty($tables)) {
            foreach ($tables as $tableid => $table) {
                $tables[$tableid]['_tableexists'] = ($this->helper->check_table_exist($table['name']) ? 1 : 0);
                $tables[$tableid]['_groups'] = $groups;
            }
        }

        return $tables;

    }

    function get_table_list()
    {
        //$langs=array_values(Multilanguage::availableLanguages());
        //$control_langs=(count($langs)>0 ? true : false);
        $rs = '';

        $tableid = intval($this->getRequestValue('table_id'));

        $control_langs = false;
        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
            $control_langs = true;
        }

        $tables = $this->loadTablesInfo();

        $groups = array();
        if(!empty($tables)){
            $firsttable = reset($tables);
            $groups = $firsttable['_groups'];
            unset($firsttable);
        }

        $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/table/js/interface.js?v=3"></script>';
        $rs .= '<link href="' . SITEBILL_MAIN_URL . '/apps/table/css/style.css" rel="stylesheet">';
        $current_table = $this->getRequestValue('table_name');

        $rs .= '<div class="app-table-tablelist">';

        if (count($tables) > 0) {

            $firstletters = [];
            foreach ($tables as $table) {
                $firstletters[$table['_firstletter']] = $table['_firstletter'];
            }

            $rs .= '<div class="firstletters">';
            foreach ($firstletters as $firstletter) {
                $rs .= '<div class="firstletter" data-letter="' . $firstletter . '">' . $firstletter . '</div>';
            }
            $rs .= '</div>';

            $rs .= '<div class="accordion" id="columnslist_accordion">';
            foreach ($tables as $primary_key_value => $item_array) {
                $rs .= '<div class="accordion-group columnslist_accordion_group" data-letter="' . $item_array['_firstletter'] . '">';
                $rs .= '<div class="accordion-heading">';
                $rs .= '<a class="accordion-toggle accordion-toggle-' . $primary_key_value . '" data-toggle="collapse" data-parent="#columnslist_accordion" href="#formeditoracc_' . $item_array['name'] . '">';
                $rs .= $item_array['name'] . ($item_array['description'] != '' ? ' (' . $item_array['description'] . ')' : '');
                $rs .= '</a>';
                $rs .= '<div class="model-accordion-controls">';
                if (defined('DEVMODE')) {
                    $rs .= '<a class="btn btn-mini btn-success" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=fieldrules&table_id=' . $primary_key_value . '" title="Редактировать"><i class="icon-white icon-user"></i> <sup>beta</sup></a> ';
                }

                $rs .= '<a class="btn btn-mini btn-info" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=edit&table_id=' . $primary_key_value . '" title="Редактировать модель"><i class="icon-white icon-pencil"></i></a> ';
                $rs .= '<a class="btn btn-mini btn-info" href="' . SITEBILL_MAIN_URL . '/admin/?action=columns&do=new&table_id=' . $primary_key_value . '" title="Добавить колонку в модель"><i class="icon-white icon-plus"></i></a> ';
                if (defined('DEVMODE')) {
                    $rs .= '<a class="btn btn-mini btn-info" href="' . SITEBILL_MAIN_URL . '/admin/?action=columns&do=many&table_id=' . $primary_key_value . '" title="Добавить несколько колонок в модель"><i class="icon-white icon-plus"></i><i class="icon-white icon-plus"></i></a> ';
                }
                $rs .= '<a class="btn btn-mini btn-danger" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=delete&table_id=' . $primary_key_value . '" title="Удалить модель" onclick="if ( confirm(\'Действительно хотите удалить запись?\') ) {return true;} else {return false;}"><i class="icon-white icon-remove"></i></a> ';

                if ($item_array['_customentityhandler'] == 1) {
                    $rs .= '<a class="btn btn-mini btn-success" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=handler&table=' . $item_array['name'] . '" title="Зарегистрированный обработчик"><i class="icon-white icon-asterisk"></i></a> ';
                } else {
                    $rs .= '<a class="btn btn-mini" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=handler&table=' . $item_array['name'] . '" title="Обработчик по-умолчанию"><i class="icon-white icon-asterisk"></i></a> ';
                }

                if ($item_array['_tableexists'] == 1) {
                    //$rs .= '<a class="btn btn-info update_table" href="#" title="Сохранить сортировку"><i class="icon-white icon-refresh"></i></a> ';
                    $rs .= '<a class="btn btn-mini btn-info" href="?action=table&do=structure&subdo=update_table&table_name=' . $item_array['name'] . '" title="Обновить таблицу" ><i class="icon-white icon-repeat"></i></a>';
                } else {
                    $rs .= '<a class="btn btn-mini" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=structure&subdo=create_table&table_name=' . $item_array['name'] . '" title="Создать таблицу"><i class="icon-white icon-list-alt"></i></a>';
                }
                $rs .= ' <div class="btn-group"><a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#">Управление моделью <sup>beta</sup><span class="caret"></span></a>';
                $rs .= '<ul class="dropdown-menu">';
                $rs .= '<li><a href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=copytable&table_id=' . $primary_key_value . '">Копировать модель</a></li>';
                $rs .= '<li><a href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=exporttable&table_id=' . $primary_key_value . '">Экспортировать модель</a></li>';
                $rs .= '<li><a href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=importtable&table_id=' . $primary_key_value . '">Импортировать модель</a></li>';

                //$rs .= '<li><a href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=createmany&table_id=' . $primary_key_value . '">Создать несколько</a></li>';

                $rs .= '</ul>';
                $rs .= '</div> ';
                if (defined('DEVMODE')) {
                    $rs .= '<a class="btn btn-mini btn-warning" href="?action=table&do=structure&subdo=clear_table&table_name=' . $item_array['name'] . '" title="Очистить" onclick="if ( confirm(\'Действительно хотите очистить таблицу? Будут удалены все поля таблицы не входящие в текущую модель и данные, содержащиеся в этих полях.\') ) {return true;} else {return false;}"><i class="icon-white icon-cog"></i><sup>beta</sup></a> ';
                    $rs .= '<a class="btn btn-mini" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=activitymatrix&table_id=' . $primary_key_value . '" title="Матрица разделов">Матрица разделов <sup>beta</sup></a> ';
                }


                $rs .= '<div class="btn-group"><a class="btn btn-mini dropdown-toggle" data-toggle="dropdown" href="#">Быстрое добавление<span class="caret"></span></a>';
                $rs .= '<ul class="dropdown-menu">';
                $rs .= '<li><a href="' . SITEBILL_MAIN_URL . '/admin/?action=columns&do=add_meta&table_id=' . $primary_key_value . '">Добавить мета поля (3 поля) и поле описания</a></li>';
                $rs .= '</ul>';
                $rs .= '</div> ';
                if ($control_langs) {
                    $rs .= '<a class="btn btn-mini" href="' . SITEBILL_MAIN_URL . '/admin/?action=table&do=edit_langs&table_id=' . $primary_key_value . '" title="Быстрые переводы">Быстрые переводы</a> ';
                }
                $rs .= '</div>';

                $rs .= '</div>';
                $rs .= '<div id="formeditoracc_' . $item_array['name'] . '" class="accordion-body' . ($current_table == $item_array['name'] || $tableid == $item_array['table_id'] ? ' in' : '') . ' collapse">';
                $rs .= '<div class="accordion-inner">';
                $cols = $this->get_columns_list($item_array);
                if (false !== $cols) {
                    $rs .= $cols;
                }

                $rs .= '</div>';
                $rs .= '</div>';
                $rs .= '</div>';
            }

            $rs .= '</div>';



            if(!empty($groups)){
                $rs .= '<div class="modal hide fade" id="group-restriction-edit"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h3>Group management</h3></div>';
                $rs .= '<div class="modal-body">';
                $rs .= '<div class="alert alert-info">Check the required groups and click "Update"</div>';
                $rs .= '<input type="hidden" value="" name="cid">';
                foreach($groups as $uid => $uin){
                    $rs .= '<div class="group_ed_ch">';
                    $rs .= '<input id="g_check_'.$uid.'" type="checkbox" class="checker ace" name="group_id[]" value="'.$uid.'"> <label for="g_check_'.$uid.'" class="lbl">'.$uin.' (ID'.$uid.')</label>';
                    $rs .= '</div>';
                }
                $rs .= '</div><div class="modal-footer"><a href="#" class="btn btn-primary clear">Clear all</a><a href="#" class="btn btn-primary ok">Update</a></div></div>';
            }

        } else {
            $rs .= '<br><br>Записей не найдено';
        }

        $rs .= '</div>';

        return $rs;
    }

    function get_columns_list_m($table_id)
    {

        $rs = '';

        $control_langs = false;
        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
            $control_langs = true;
        }

        $DBC = DBC::getInstance();
        $ra = array();

        $query = 'SELECT `name` FROM ' . DB_PREFIX . '_table WHERE table_id=? LIMIT 1';
        $stmt = $DBC->query($query, array($table_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $table_name = $ar['name'];
        }

        $query = "SELECT * FROM " . DB_PREFIX . "_columns WHERE table_id=? ORDER BY sort_order ASC";

        $stmt = $DBC->query($query, array($table_id));
        if (!$stmt) {
            //echo 'ERROR';
            return false;
        }
        while ($ar = $DBC->fetch($stmt)) {
            $ra[$ar['columns_id']] = $ar;
        }
        if (count($ra) > 0) {

            foreach ($ra as $primary_key_value => $item_array) {


                if ($item_array['active'] == 0) {
                    $class = "column";
                } else {
                    $class = "column";
                }
                if (isset($this->grid_controls) && count($this->grid_controls) > 0) {
                    $rs .= '<tr alt="' . $primary_key_value . '" class="' . $class . '">';
                } else {
                    $rs .= '<tr alt="' . $primary_key_value . '" class="' . $class . '">';
                }
                $rs .= '<td class="center"><input type="checkbox"  class="ace" value="' . $item_array['columns_id'] . '" /><span class="lbl"></span></td>';

                $icon_class = '';
                switch ($item_array['type']) {
                    case 'checkbox' :
                    {
                        $icon_class = 'icon-check';
                        break;
                    }
                    case 'geodata' :
                    case 'gadres' :
                    {
                        $icon_class = 'icon-map-marker';
                        break;
                    }
                    case 'safe_string' :
                    case 'mobilephone' :
                    {
                        $icon_class = 'icon-font';
                        break;
                    }
                    case 'uploads' :
                    case 'uploadify_image' :
                    {
                        $icon_class = 'icon-picture';
                        break;
                    }
                    case 'date' :
                    case 'dtdate' :
                    case 'dtdatetime' :
                    case 'dttime' :
                    {
                        $icon_class = 'icon-calendar';
                        break;
                    }
                    case 'select_box' :
                    case 'select_box_structure' :
                    case 'select_by_query' :
                    case 'structure' :
                    {
                        $icon_class = 'icon-tasks';
                        break;
                    }
                    case 'primary_key' :
                    {
                        $icon_class = 'icon-filter';
                        break;
                    }
                    case 'textarea' :
                    case 'textarea_editor' :
                    {
                        $icon_class = 'icon-comment';
                        break;
                    }
                }


                $rs .= '<td>' . $item_array['name'] . '</td>';
                $rs .= '<td><i class="icon ' . $icon_class . '"></i> ' . $item_array['type'] . '</td>';

                if ($item_array['required'] == 1) {
                    $rs .= '<td><a class="state_change btn btn-mini btn-warning" href="derequired" title="Обязательное" alt="' . $primary_key_value . '"><i class="icon-white icon-ok-circle"></i></a></td>';
                } else {
                    $rs .= '<td><a class="state_change btn btn-mini" href="required" title="Обязательное" alt="' . $primary_key_value . '"><i class="icon-white icon-ok-circle"></i></a></td>';
                }
                $rs .= '<td><a href="#" id="' . $item_array['name'] . '" data-type="text" data-pk="' . $item_array['columns_id'] . '" class="editable editable-click addeditable" style="display: inline;">' . $item_array['title'] . '</a></td>';
                $rs .= '<td class="field_tab" alt="' . $primary_key_value . '">' . ($item_array['tab'] != '' ? '<span class="defined">' . $item_array['tab'] . '</span>' : '<span class="undefined">Не указано</span>') . '</td>';

                $rs .= '<td width="15%" nowrap>';
                $rs .= '<a href="?action=columns&do=edit&table_name=' . $table_name . '&columns_id=' . $primary_key_value . '" class="btn btn-info btn-mini" title="Редактировать"><i class="icon-white icon-pencil"></i></a>';
                $rs .= ' <a href="?action=columns&do=delete&table_name=' . $table_name . '&columns_id=' . $primary_key_value . '" title="Удалить" class="btn btn-danger btn-mini" onclick="if ( confirm(\'Действительно хотите удалить запись?\') ) {return true;} else {return false;}"><i class="icon-white icon-remove"></i></a>';
                if ($item_array['active'] == 0) {
                    $rs .= ' <a class="state_change btn btn-mini" href="activate" title="Активация" alt="' . $primary_key_value . '"><i class="icon-white icon-off"></i></a>';
                } else {
                    $rs .= ' <a class="state_change btn btn-mini btn-warning" href="deactivate" title="Активация" alt="' . $primary_key_value . '"><i class="icon-white icon-off"></i></a>';
                }
                if ($control_langs) {
                    $rs .= ' <a class="btn btn-mini" href="' . SITEBILL_MAIN_URL . '/admin/?action=columns&do=add_lang_fields&columns_id=' . $primary_key_value . '" title="Добавить\проверить мультиязычные поля для этого поля"><i class="icon-white icon-plus"></i> ML</a> ';
                }
                $rs .= '</td>';

                $rs .= '</tr>';
            }
        }
        return $rs;
    }

    function get_columns_list($table_info)
    {
        $control_langs = false;
        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
            $control_langs = true;
        }

        $DBC = DBC::getInstance();
        $ra = array();
        /*
          require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php';
          $SM=new Structure_Manager();
          $structure=$SM->loadCategoryStructure();
          $structure=$structure['catalog'];
          $structure[0]['name']='Без ограничений';
         */
        /* $query = 'SELECT `name` FROM ' . DB_PREFIX . '_table WHERE table_id=? LIMIT 1';
         $stmt = $DBC->query($query, array($table_id));
         if ($stmt) {
             $ar = $DBC->fetch($stmt);
             $table_name = $ar['name'];
         }*/

        $table_name = $table_info['name'];
        $table_id = $table_info['table_id'];
        $params['groups'] = $table_info['_groups'];

        /* $query='SHOW COLUMNS FROM `'.DB_PREFIX.'_'.$table_name.'`';
          $stmt=$DBC->query($query);
          if($stmt){
          while($ar=$DBC->fetch($stmt)){
          //print_r($ar);
          $rcolumns[$ar['Field']]=$ar['Type'];
          }
          } */


        $query = "SELECT * FROM " . DB_PREFIX . "_columns WHERE table_id=? ORDER BY sort_order ASC";

        $stmt = $DBC->query($query, array($table_id));
        if (!$stmt) {
            //echo 'ERROR';
            return false;
        }
        while ($ar = $DBC->fetch($stmt)) {
            $ra[$ar['columns_id']] = $ar;
        }
        if (count($ra) > 0) {
            $rs = '<table border="0" width="100%" class="columns_list">';
            $rs .= '<tbody class="applied table table-hover">';

            foreach ($ra as $primary_key_value => $item_array) {


                if ($item_array['active'] == 0) {
                    $class = "row3notactive column";
                } else {
                    $class = "-row3 column";
                }
                if (isset($this->grid_controls) && count($this->grid_controls) > 0) {
                    $rs .= '<tr alt="' . $primary_key_value . '" class="' . $class . '">';
                } else {
                    $rs .= '<tr alt="' . $primary_key_value . '" class="' . $class . '">';
                }
                $rs .= '<td><div class="dd-move"><i class="icon-move bigger-130"></i></div></td>';
                $rs .= '<td><input id="col_check_' . $item_array['columns_id'] . '" type="checkbox" class="checker ace" value="' . $item_array['columns_id'] . '"><label for="col_check_' . $item_array['columns_id'] . '" class="lbl"></label></td>';

                $icon_class = '';
                switch ($item_array['type']) {
                    case 'checkbox' :
                    {
                        $icon_class = 'icon-check';
                        break;
                    }
                    case 'geodata' :
                    case 'gadres' :
                    {
                        $icon_class = 'icon-map-marker';
                        break;
                    }
                    case 'safe_string' :
                    case 'mobilephone' :
                    {
                        $icon_class = 'icon-font';
                        break;
                    }
                    case 'uploads' :
                    case 'uploadify_image' :
                    {
                        $icon_class = 'icon-picture';
                        break;
                    }
                    case 'date' :
                    case 'dtdate' :
                    case 'dtdatetime' :
                    case 'dttime' :
                    {
                        $icon_class = 'icon-calendar';
                        break;
                    }
                    case 'select_box' :
                    case 'select_box_structure' :
                    case 'select_by_query' :
                    case 'structure' :
                    {
                        $icon_class = 'icon-tasks';
                        break;
                    }
                    case 'primary_key' :
                    {
                        $icon_class = 'icon-filter';
                        break;
                    }
                    case 'textarea' :
                    case 'textarea_editor' :
                    {
                        $icon_class = 'icon-comment';
                        break;
                    }
                }


                //$rs .= '<td>'.$item_array['name'].' ('.$rcolumns[$item_array['name']].')</td>';
                $rs .= '<td>' . $item_array['name'] . '</td>';

                //$rs .= '<td>' . $item_array['active_in_topic'] . '</td>';


                //$rs .= '<td>' . (!empty($gnames) ? implode(', ', $gnames) : '') . '</td>';

                $rs .= '<td><i class="icon ' . $icon_class . '"></i> ' . $item_array['type'];
                /* if(($item_array['type']=='select_box' || $item_array['type']=='checkbox') && !preg_match('/^int\((\d+)\)/', $rcolumns[$item_array['name']])){
                  $rs .= '<div style="font-size: 11px;color: #9c2626;"><i class="icon icon-exclamation-sign"></i> Возможно данное поле имеет неправильный тип</div>';
                  } */
                $rs .= '</td>';
                //$rs .= '<td>'.$item_array['name'].'</td>';
                /* $rs .= '<td>';
                  if($item_array['active_in_topic']!=''){
                  $active_in_topic_array=array();
                  $active_in_topic=explode(',', $item_array['active_in_topic']);
                  foreach($active_in_topic as $ait){
                  if(isset($structure[$ait])){
                  $active_in_topic_array[]=$structure[$ait]['name'];
                  }
                  }
                  $rs .= implode(', ', $active_in_topic_array);
                  }else{
                  $rs .= 'Без ограничений';
                  }
                  $rs .= '</td>'; */
                if ($item_array['required'] == 1) {
                    $rs .= '<td><a class="state_change btn btn-mini btn-warning" href="derequired" title="Обязательное" alt="' . $primary_key_value . '"><i class="icon-white icon-ok-circle"></i></a></td>';
                } else {
                    $rs .= '<td><a class="state_change btn btn-mini" href="required" title="Обязательное" alt="' . $primary_key_value . '"><i class="icon-white icon-ok-circle"></i></a></td>';
                }
                //$rs .= '<td>'.$item_array['unique'].'</td>';
                if ($item_array['unique'] == 1) {
                    $rs .= '<td>Уникальное</td>';
                } else {
                    $rs .= '<td></td>';
                }
                $rs .= '<td><a href="#" id="' . $item_array['name'] . '" data-type="text" data-pk="' . $item_array['columns_id'] . '" class="editable editable-click addeditable" style="display: inline;">' . $item_array['title'] . '</a></td>';

                $active_in_topic_array = array();
                if ( $item_array['active_in_topic'] != '' and $item_array['active_in_topic'] != 0 ) {
                    $gr = explode(',', $item_array['active_in_topic']);
                    foreach ($gr as $gid) {
                        if ($gid != 0) {
                            $active_in_topic_array[] = $gid;
                        }
                    }
                }

                $gnames = array();
                if ($item_array['group_id'] != '') {
                    $gr = explode(',', $item_array['group_id']);
                    foreach ($gr as $gid) {
                        if (isset($params['groups'][$gid])) {
                            $gnames[] = $params['groups'][$gid];
                        } else {
                            $gnames[] = '???';
                        }
                    }
                }
                $rs .= '<td>';

                $rs .= 'Ограничений по группам: <a href="javascript:void(0);" rel="popover" class="tooltipe_block show_groups show_groups_'.$primary_key_value.' label label-info" data-content="'.(count($gnames) == 0 ? 'Нет' : implode(', ', $gnames)).'" data-cid="'.$primary_key_value.'">'.count($gnames).'</a>';


                $rs .= (count($active_in_topic_array) == 0?'':'Ограничений по категориям: <a href="javascript:void(0);" rel="popover" class="tooltipe_block label label-info" data-content="' . implode(', ', $active_in_topic_array) . '">' . count($active_in_topic_array) . '</a>');
                $rs .= '</td>';

                $rs .= '<td class="field_tab" alt="' . $primary_key_value . '">' . ($item_array['tab'] != '' ? '<span class="defined">' . $item_array['tab'] . '</span>' : '<span class="undefined">Не указано</span>') . '</td>';

                $rs .= '<td width="15%" nowrap>';
                $rs .= '<a href="?action=columns&do=edit&table_name=' . $table_name . '&columns_id=' . $primary_key_value . '" class="btn btn-mini btn-info" title="Редактировать"><i class="icon-white icon-pencil"></i></a>';
                $rs .= ' <a href="?action=columns&do=delete&table_name=' . $table_name . '&columns_id=' . $primary_key_value . '" title="Удалить" class="btn btn-mini btn-danger" onclick="if ( confirm(\'Действительно хотите удалить запись?\') ) {return true;} else {return false;}"><i class="icon-white icon-remove"></i></a>';
                if ($item_array['active'] == 0) {
                    $rs .= ' <a class="state_change btn btn-mini" href="activate" title="Активация" alt="' . $primary_key_value . '"><i class="icon-white icon-off"></i></a>';
                } else {
                    $rs .= ' <a class="state_change btn btn-mini btn-warning" href="deactivate" title="Активация" alt="' . $primary_key_value . '"><i class="icon-white icon-off"></i></a>';
                }
                if ($control_langs) {
                    $rs .= ' <a class="btn btn-mini" href="' . SITEBILL_MAIN_URL . '/admin/?action=columns&do=add_lang_fields&columns_id=' . $primary_key_value . '" title="Добавить\проверить мультиязычные поля для этого поля"><i class="icon-white icon-plus"></i> ML</a> ';
                }
                $rs .= '</td>';

                $rs .= '</tr>';
            }
            $rs .= '<tfoot>'
                . '<tr>'
                . '<td colspan="8">'
                . '<button class="delete_checked_columns btn btn-danger"><i class="icon-white icon-remove"></i>' . Multilanguage::_('L_DELETE_CHECKED') . '</button> 
					<button class="activity_set_columns btn btn-inverse"><i class="icon-white icon-th"></i> Установить активность в категориях <sup>(beta)</sup></button> 
                    <button class="addml_multi btn btn-inverse"><i class="icon-white icon-th"></i> ML</button> 
					</td></tr></tfoot>';
            $rs .= '</tbody>';
            $rs .= '</table>';
        }
        return $rs;
    }

    function test_case()
    {
        $model_data = $this->helper->load_model('data');
        $rs = $this->get_test_form($model_data['data']);
        return $rs;
    }

    function get_test_form($form_data = array(), $do = 'new', $language_id = 0, $button_title = '')
    {
        if (!is_array($form_data)) {
            return false;
        }
        $rs = '';
        if ($button_title == '') {
            $button_title = Multilanguage::_('L_TEXT_SAVE');
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();

        $rs .= $this->get_ajax_functions();

        $rs .= '<form method="post" action="index.php" enctype="multipart/form-data">';
        $rs .= '<table>';
        if ($this->getError()) {
            $rs .= $form_generator->get_error_message_row($this->GetErrorMessage());
        }
        $rs .= $form_generator->compile_form($form_data);

        if ($do == 'new') {
            $rs .= '<input type="hidden" name="do" value="new_done">';
            $rs .= '<input type="hidden" name="' . $this->primary_key . '" value="' . $this->getRequestValue($this->primary_key) . '">';
        } else {
            $rs .= '<input type="hidden" name="do" value="edit_done">';
            $rs .= '<input type="hidden" name="' . $this->primary_key . '" value="' . $form_data[$this->primary_key]['value'] . '">';
        }
        //$rs .= '<input type="hidden" name="page" value="'.$_SESSION['rem_page'].'">';
        $rs .= '<input type="hidden" name="action" value="' . $this->action . '">';
        $rs .= '<input type="hidden" name="language_id" value="' . $language_id . '">';

        $rs .= '<tr>';
        $rs .= '<td></td>';
        $rs .= '<td><input type="submit" name="submit" id="formsubmit" onClick="return SitebillCore.formsubmit(this);" value="' . $button_title . '"></td>';
        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '</form>';

        return $rs;
    }

    function install()
    {
        $DBC = DBC::getInstance();
        $success_result = true;
        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_table` (
		  `table_id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) NOT NULL,
		  `description` text,
		  PRIMARY KEY (`table_id`),
		  UNIQUE KEY `table_name` (`name`)
		) ENGINE=MyISAM  DEFAULT CHARSET=" . DB_ENCODING . " AUTO_INCREMENT=1 ;";
        $success = false;
        $stmt = $DBC->query($query, array(), $rows, $success);
        $success_result = $success_result && $success;


        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_columns` (
			  `columns_id` int(11) NOT NULL AUTO_INCREMENT,
			  `active` tinyint(1) NOT NULL DEFAULT '1',
			  `table_id` int(11) NOT NULL DEFAULT '0',
			  `group_id` varchar(255) NOT NULL,
			  `name` varchar(255) NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `primary_key_name` varchar(255) DEFAULT NULL,
			  `primary_key_table` varchar(255) DEFAULT NULL,
			  `value_string` varchar(255) DEFAULT NULL,
			  `query` text,
			  `value_name` varchar(255) DEFAULT NULL,
			  `title_default` varchar(255) DEFAULT NULL,
			  `value_default` varchar(255) DEFAULT NULL,
			  `value` varchar(255) NOT NULL,
			  `type` varchar(255) NOT NULL,
			  `required` tinyint(1) NOT NULL DEFAULT '0',
			  `unique` tinyint(1) NOT NULL DEFAULT '0',
			  `sort_order` int(11) NOT NULL DEFAULT '0',
			  `value_table` varchar(255) NOT NULL,
			  `value_primary_key` varchar(255) NOT NULL,
			  `value_field` varchar(255) NOT NULL,
			  `assign_to` varchar(255) NOT NULL,
			  `dbtype` varchar(255) NOT NULL,
			  `table_name` varchar(255) NOT NULL,
			  `primary_key` varchar(255) NOT NULL,
			  `primary_key_value` varchar(255) NOT NULL,
			  `action` varchar(255) NOT NULL,
			  `select_data` text NOT NULL,
              `active_in_topic` varchar(255) DEFAULT NULL,
              `tab` varchar(255) DEFAULT NULL,
              `hint` varchar(255) DEFAULT NULL,
				`entity` varchar(255) DEFAULT NULL,
				`combo` tinyint(1) NOT NULL DEFAULT '0',
				`parameters` text,
			  PRIMARY KEY (`columns_id`),
			  UNIQUE KEY `column_table` (`table_id`,`name`)
			) ENGINE=MyISAM  DEFAULT CHARSET=" . DB_ENCODING . " AUTO_INCREMENT=1 ;";
        $success = false;
        $stmt = $DBC->query($query, array(), $rows, $success);
        $success_result = $success_result && $success;

        //create unique index
        $query = "CREATE UNIQUE INDEX column_table ON " . DB_PREFIX . "_columns ( table_id,  name) ";
        $success = false;
        $stmt = $DBC->query($query, array(), $rows, $success);
        $success_result = $success_result && $success;

        if (!$success_result) {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED_ERROR');
        } else {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED');
        }
        return $rs;
    }

    function ajax()
    {
        $ajax_controller_user_id = $this->getSessionUserId();
        if ($ajax_controller_user_id != 0) {
            $DBC = DBC::getInstance();
            $query = 'SELECT system_name FROM ' . DB_PREFIX . '_group WHERE group_id=(SELECT group_id FROM ' . DB_PREFIX . '_user WHERE user_id=? LIMIT 1)';
            $stmt = $DBC->query($query, array($ajax_controller_user_id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                if ($ar['system_name'] != 'admin') {
                    return false;
                }
            }
        } else {
            return false;
        }
        if ($this->getRequestValue('action') == 'show_groups') {
            $cid = intval($this->getRequestValue('columns_id'));
            $DBC = DBC::getInstance();
            $query = 'SELECT group_id FROM ' . DB_PREFIX . '_columns WHERE columns_id=?';
            $stmt = $DBC->query($query, array($cid));
            $gs = array();
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                if($ar['group_id'] != ''){
                    $gs = explode(',', $ar['group_id']);
                }
            }
            if (!empty($gs)) {
                foreach ($gs as $k => $v) {
                    $gs[$k] = intval($v);
                }
            }
            return json_encode($gs);
        }
        if ($this->getRequestValue('action') == 'change_groups') {
            $responce = array(
                'status' => 0,
                'data' => array()
            );
            $cid = intval($this->getRequestValue('columns_id'));
            $ids = $this->getRequestValue('ids');
            if(is_null($ids)){
                $ids = array(0);
            }else{
                foreach ($ids as $i => $v) {
                    $ids[$i] = intval($v);
                }
            }

            $groups = array();
            $query = 'SELECT group_id, name FROM ' . DB_PREFIX . '_group WHERE group_id IN ('.implode(',', array_fill(0, count($ids), '?')).')';
            $stmt = $DBC->query($query, array_values($ids));
            if($stmt){
                while($ar = $DBC->fetch($stmt)){
                    $groups[$ar['group_id']] = $ar['name'];
                }
            }

            if (count($groups) > 0) {
                $cols = implode(',', array_keys($groups));
            } else {
                $cols = '';
            }
            $DBC = DBC::getInstance();
            $query = 'UPDATE ' . DB_PREFIX . '_columns SET group_id = ? WHERE columns_id = ?';
            $stmt = $DBC->query($query, array($cols, $cid));

            $responce['status'] = 1;
            $responce['data']['groups'] = $groups;
            $responce['data']['joined'] = (count($groups) > 0 ? implode(', ', $groups) : 'Нет');
            $responce['data']['count'] = count($groups);

            return json_encode($responce);
        }
        if ($this->getRequestValue('action') == 'reorder_columns') {
            $this->reorder_columns($this->getRequestValue('ids'));
        }
        if ($this->getRequestValue('action') == 'get_table_fields_select') {
            return $this->getTableFieldsSelectbox($this->getRequestValue('table_name'));
        }
        if ($this->getRequestValue('action') == 'get_preview') {
            return $this->getFormElementPreview($this->getRequestValue('data'));
        }
        if ($this->getRequestValue('action') == 'change_column_state') {
            return $this->changeColumnState($this->getRequestValue('operation'), (int)$this->getRequestValue('id'));
        }
        if ($this->getRequestValue('action') == 'change_field_tab') {
            return $this->changeFieldTab($this->getRequestValue('tab_name'), (int)$this->getRequestValue('id'));
        }
        if ($this->getRequestValue('action') == 'change_column_field') {
            return $this->changeColumnField();
        }
        if ($this->getRequestValue('action') == 'save_search_form') {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/search_forms_admin.php';
            $SFA = new search_forms_admin();
            $SFA->save_search_form();
        }
        if ($this->getRequestValue('action') == 'save_front_grid') {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/front_gridmanager_admin.php';
            $SFA = new front_gridmanager_admin();
            $SFA->save_front_grid();
        }
        if ($this->getRequestValue('action') == 'save_group_rules') {
            if (!defined('DEVMODE')) {
                return '';
            }
            $DBC = DBC::getInstance();
            $rules = $this->getRequestValue('rule');
            $query = 'INSERT INTO ' . DB_PREFIX . '_group_rule (group_id, columns_id, view_status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE view_status=?';
            foreach ($rules['view'] as $column_id => $rule) {
                foreach ($rule as $group_id => $status) {
                    $DBC->query($query, array($group_id, $column_id, $status, $status));
                }
            }
            $query = 'INSERT INTO ' . DB_PREFIX . '_group_rule (group_id, columns_id, edit_status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE edit_status=?';
            foreach ($rules['edit'] as $column_id => $rule) {
                foreach ($rule as $group_id => $status) {
                    $DBC->query($query, array($group_id, $column_id, $status, $status));
                }
            }
            print_r($rules);
        }
        if ($this->getRequestValue('action') == 'save_viewrules_group') {
            if (!defined('DEVMODE')) {
                return '';
            }
            $DBC = DBC::getInstance();
            $rules = $this->getRequestValue('rule');
            //print_r($rules);
            //exit();
            if (!empty($rules)) {
                $query = 'UPDATE ' . DB_PREFIX . '_columns SET group_id=? WHERE columns_id=?';

                foreach ($rules as $cid => $r) {
                    $DBC->query($query, array($r, $cid));
                }
            }
        }
        return false;
    }

    function changeColumnField()
    {
        $DBC = DBC::getInstance();
        $user_id = (int)$_SESSION['user_id_value'];
        if ($user_id !== (int)$this->getAdminUserId()) {
            return json_encode(array('success' => false, 'msg' => 'error'));
        }
        $id = (int)$this->getRequestValue('pk');
        $field_name = $this->getRequestValue('field_name');
        $value = SiteBill::iconv('utf-8', SITE_ENCODING, $this->escape($this->getRequestValue('value')));
        if ($id != 0 && $field_name != '') {
            $query = 'UPDATE ' . DB_PREFIX . '_columns SET `' . $field_name . '`=? WHERE columns_id=?';
            $stmt = $DBC->query($query, array($value, $id));
            return json_encode(array('success' => true));
        }
        return json_encode(array('success' => false, 'msg' => 'error'));
    }

    function reorder_columns($ids_string)
    {
        $DBC = DBC::getInstance();
        $ids = array();
        $ids_string = trim($ids_string);
        if ($ids_string != '') {
            $ids = explode(',', $ids_string);
        }
        if (!empty($ids)) {
            foreach ($ids as $k => $id) {
                $query = 'UPDATE ' . DB_PREFIX . '_columns SET sort_order=' . ($k + 1) . ' WHERE columns_id=' . $id;
                $stmt = $DBC->query($query);
            }
        }
        //return $a;
    }

    function changeFieldTab($tab_name, $id)
    {
        $DBC = DBC::getInstance();
        $tab_name = trim($tab_name);
        $tab_name = SiteBill::iconv('utf-8', SITE_ENCODING, $tab_name);
        if ($id != 0) {
            $query = 'UPDATE ' . DB_PREFIX . '_columns SET tab=? WHERE columns_id=?';
            $stmt = $DBC->query($query, array($tab_name, $id));
            if ($tab_name != '') {
                return $tab_name;
            } else {
                return 'Не указано';
            }
        }
        return '';
    }

    function changeColumnState($operation, $id)
    {
        if ($id != 0) {
            $DBC = DBC::getInstance();
            if ($operation == 'activate') {
                $q = 'UPDATE ' . DB_PREFIX . '_columns SET active=1 WHERE columns_id=?';
                $ret = 'activated';
            } elseif ($operation == 'deactivate') {
                $q = 'UPDATE ' . DB_PREFIX . '_columns SET active=0 WHERE columns_id=?';
                $ret = 'deactivated';
            } elseif ($operation == 'required') {
                $q = 'UPDATE ' . DB_PREFIX . '_columns SET required=1 WHERE columns_id=?';
                $ret = 'required';
            } elseif ($operation == 'derequired') {
                $q = 'UPDATE ' . DB_PREFIX . '_columns SET required=0 WHERE columns_id=?';
                $ret = 'derequired';
            } else {
                $ret = '';
            }
            $stmt = $DBC->query($q, array($id), $rows, $success);
            if (!$success) {
                $this->riseError($DBC->getLastError());
            }
        } else {
            $ret = '';
        }
        return $ret;
    }

    function getTablesNames()
    {
        $tables = array('' => 'выбрать');
        return array_merge($tables, $this->getOnlyTablesList());
    }

    function getOnlyTablesList()
    {
        $DBC = DBC::getInstance();
        $query = 'SHOW FULL TABLES';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $x = array_values($ar);
                if ($x[1] == 'BASE TABLE') {
                    $t_name = str_replace(DB_PREFIX . '_', '', $x[0]);
                    $tables[$t_name] = $t_name;
                }
            }
        }
        return $tables;
    }

    function getTableFieldsSelectbox($table)
    {
        $columns = $this->getTableFields($table);
        foreach ($columns as $k => $v) {
            $ret .= '<option value="' . $k . '">' . $v . '</option>';
        }
        return $ret;
    }

    function getTableFields($table)
    {
        $tables = array('' => 'выбрать');
        return array_merge($tables, $this->getOnlyTableFields($table));
    }

    function getOnlyTableFields($table)
    {
        if ($table != '') {
            $DBC = DBC::getInstance();
            $query = 'SHOW COLUMNS FROM `' . DB_PREFIX . '_' . $table . '`';
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $columns[$ar['Field']] = $ar['Field'];
                }
            }
        }
        return $columns;
    }


    function getFormElementPreview($data)
    {
        $element_data = array();
        $_d = explode('|', $data);
        if (count($_d) > 0) {
            foreach ($_d as $d) {
                list($k, $v) = explode(':', $d);

                $element_data[$k] = SiteBill::iconv('utf-8', SITE_ENCODING, $v);
                if ($k == 'select_data') {
                    $element_data[$k] = $this->helper->unserializeSelectData($element_data[$k]);
                }
            }
        }
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php';
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php';
        $FG = new Form_Generator();
        echo $FG->compile_form(array('preview' => $element_data), true);
    }

}
