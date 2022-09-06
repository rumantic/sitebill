<?php

/**
 * Structure manager
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */

//require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/cache/cache.php';
class Structure_Manager extends SiteBill_Krascap
{

    private static $_category_structure = NULL;
    private static $_category_structure_published = NULL;
    private static $_category_urls = NULL;
    var $operation_type_array = array();
    private $context_object = NULL;
    private $j = 0;

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();
        //$this->operation_type_array = $this->load_operation_type_list();
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/version/version.php';
        $version = new Version();
        if (!$version->get_version_value('topic.url')) {
            $this->add_topic_url();
            $version->set_version_value('topic.url', 1);
        }
        $this->action = 'structure';
        $this->table_name = 'topic';
        $this->primary_key = 'id';
        $this->app_title = Multilanguage::_('L_ADMIN_MENU_STRUCTURE');
    }

    function add_topic_url()
    {
        $query = "alter table " . DB_PREFIX . "_topic add column url text";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
    }

    function get_content_drop_menu()
    {
        $ra = array();
        $DBC = DBC::getInstance();

        $query = "SELECT ms.*, m.tag, m.name as menu_title FROM " . DB_PREFIX . "_menu m, " . DB_PREFIX . "_menu_structure ms WHERE m.menu_id=ms.menu_id and m.tag like '%drop_menu%' ORDER BY ms.sort_order";
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ra[$ar['tag']][] = $ar;
            }
        }
        return $ra;
    }

    function upgrade()
    {
        $DBC = DBC::getInstance();

        $query = "alter table " . DB_PREFIX . "_topic add column meta_title text";
        $stmt = $DBC->query($query);

        $query = "alter table " . DB_PREFIX . "_topic add column meta_keywords text";
        $stmt = $DBC->query($query);

        $query = "alter table " . DB_PREFIX . "_topic add column meta_description text";
        $stmt = $DBC->query($query);
    }

    function list2Tree($list)
    {
        $r = array();
        $childs = array();
        $items = array();
        $currentDepth = 0;

        foreach ($list as $line) {
            $name = rtrim($line);
            $lDepth = strlen($line) - strlen(ltrim($name, " "));
            $name = trim($name);
            if ($lDepth == 0) {
                $items[0][] = $name;
            }
            echo $name . ' ' . $lDepth . '==';

        }
        print_r($items);
    }

    /**
     * Main
     * @param void
     * @return string
     */
    function main()
    {
        //return 'В разработке';
        $do = $this->getRequestValue('do');
        switch ($do) {
            case 'loadlist':
            {
                if ('post' == strtolower($_SERVER['REQUEST_METHOD'])) {
                    $catlist = explode(PHP_EOL, trim($_POST['catlist']));
                    print_r($catlist);
                    $this->list2Tree($catlist);
                    $rs .= '<textarea name="catlist" rows="30">222</textarea>';
                } else {

                }
                $rs .= '<form method="post" action="' . SITEBILL_MAIN_URL . '/admin' . self::$_trslashes . '">'
                    . '<textarea name="catlist" rows="30">'
                    . '</textarea>'
                    . '<input type="hidden" name="action" value="structure">'
                    . '<input type="hidden" name="do" value="loadlist">'
                    . '<input type="submit">'
                    . '</form>';
                break;
            }
            case 'delete':
                if ($this->isDemo()) {
                    return $this->demo_function_disabled();
                }
                $category_structure = $this->loadCategoryStructure();
                if (count($category_structure['childs'][$this->getRequestValue('id')]) > 0) {
                    $rs = Multilanguage::_('CATEGORY_HAS_CHILDS', 'system') . '<br>';
                    $rs .= '<a href="?action=structure">' . Multilanguage::_('BACK_TO_LIST', 'system') . '</a>';
                    return $rs;
                }

                $c = 0;
                $query = 'SELECT COUNT(*) AS rs FROM ' . DB_PREFIX . '_data WHERE topic_id=' . (int)$this->getRequestValue('id');
                $DBC = DBC::getInstance();
                $stmt = $DBC->query($query);
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $c = $ar['rs'];
                }
                if ($c != 0) {
                    $rs = Multilanguage::_('NOT_EMPTY_CATEGORY', 'system') . '<br>';
                    $rs .= '<a href="?action=structure">' . Multilanguage::_('BACK_TO_LIST', 'system') . '</a>';
                    return $rs;
                }
                $this->deleteRecord($this->getRequestValue('id'));
                /* $Cache=Cache::getInstance();
                  $Cache->clearValue('catalog_structure'); */
                $rs = $this->getTopMenu();
                $rs .= $this->grid();
                break;

            case 'edit':

                $rs = '';

                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
                $data_model = new Data_Model();
                $form_data = $this->getStrModel();

                if ($this->getRequestValue('subdo') == 'delete_image') {
                    $this->deleteImage($this->table_name, $this->getRequestValue('image_id'));
                }

                if ($this->getRequestValue('subdo') == 'up_image') {
                    $this->reorderImage($this->table_name, $this->getRequestValue('image_id'), $this->primary_key, $this->getRequestValue($this->primary_key), 'up');
                }

                if ($this->getRequestValue('subdo') == 'down_image') {
                    $this->reorderImage($this->table_name, $this->getRequestValue('image_id'), $this->primary_key, $this->getRequestValue($this->primary_key), 'down');
                }

                if ($this->getRequestValue('language_id') > 0 and !$this->language->get_version($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $this->getRequestValue('language_id'))) {
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
                //return $rs;

                /* if ( $this->getRequestValue('subdo') == 'delete_image' ) {
                  $this->deleteImage('topic', $this->getRequestValue('image_id'));
                  }

                  if ( $this->getRequestValue('subdo') == 'up_image' ) {
                  $this->reorderImage('topic', $this->getRequestValue('image_id'), 'id', $this->getRequestValue('id'),'up');
                  }

                  if ( $this->getRequestValue('subdo') == 'down_image' ) {
                  $this->reorderImage('topic', $this->getRequestValue('image_id'), 'id', $this->getRequestValue('id'), 'down');
                  }
                  //echo 1;
                  $hash = $this->load($this->getRequestValue('id'));
                  $rs = $this->getForm('edit'); */
                break;

            case 'new':
                $rs = '';

                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
                $data_model = new Data_Model();
                $form_data = $this->getStrModel();
                $form_data[$this->table_name]['parent_id']['value'] = (int)$this->getRequestValue('parent_id');
                $rs = $this->get_form($form_data[$this->table_name]);
                //return $rs;
                // $rs = $this->getForm();
                break;

            case 'linker':
                //Структура таблицы для хранения линков
                /**
                 * CREATE TABLE IF NOT EXISTS `re_topic_links` (
                 * `id` int(11) NOT NULL AUTO_INCREMENT,
                 * `topic_id` int(11) NOT NULL,
                 * `link_topic_id` int(11) NOT NULL,
                 * `params` text,
                 * PRIMARY KEY (`id`)
                 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
                 * */
                //$rs = $this->getTopMenu();
                if (isset($_POST['submit'])) {

                    //echo '<pre>';
                    //print_r($_POST);
                    $this->saveLinkerAssociations($_POST['data']);
                    $rs .= $this->getCategoryTreeLinker(0);
                    //print_r($_POST);
                } else {
                    $rs .= $this->getCategoryTreeLinker(0);
                }

                break;

            case 'associations':
                $rs = $this->getTopMenu();
                if (isset($_POST['submit'])) {

                    //echo '<pre>';
                    //print_r($_POST);
                    $this->saveAssociations($_POST['data']);
                    $rs .= $this->getCategoryTreeAssoc(0);
                    //print_r($_POST);
                } else {
                    $rs .= $this->getCategoryTreeAssoc(0);
                }

                break;

            /* case 'chains':
              $this->loadCategoriesUrls();

              break; */

            case 'new_done':
            {
                if ($this->isDemo()) {
                    return $this->demo_function_disabled();
                }
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
                $data_model = new Data_Model();
                $form_data = $this->getStrModel();

                $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);

                if (isset($form_data[$this->table_name]['url']) && $form_data[$this->table_name]['url']['value'] == '') {
                    $form_data[$this->table_name]['url']['value'] = $this->transliteMe($form_data[$this->table_name]['name']['value']);
                    $this->setRequestValue('url', $this->transliteMe($form_data[$this->table_name]['name']['value']));
                }


                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                $OM = new Object_Manager();

                if (!$OM->check_data($form_data[$this->table_name]) || !$this->checkData()) {
                    if ($OM->GetErrorMessage() != '') {
                        $this->riseError($this->GetErrorMessage() . ' ' . $OM->GetErrorMessage());
                    }
                    $rs = $this->get_form($form_data[$this->table_name], 'new');
                } else {
                    $new_record_id = $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
                    if ($this->getError()) {
                        $rs = $this->get_form($form_data[$this->table_name], 'new');
                    } else {
                        $rs .= $this->grid();
                    }
                }
                /* return $rs;
                  if ( !$this->checkData() ) {
                  $rs = $this->getForm();
                  } else {
                  $this->addRecord();
                  $Cache=Cache::getInstance();
                  $Cache->clearValue('catalog_structure');
                  $rs = $this->getTopMenu();
                  $rs .= $this->grid();
                  } */
                break;
            }
            case 'edit_done':
                if ($this->isDemo()) {
                    return $this->demo_function_disabled();
                }
                $rs = '';
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
                $data_model = new Data_Model();
                $form_data = $this->getStrModel();

                $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);

                if (isset($form_data[$this->table_name]['url']) && $form_data[$this->table_name]['url']['value'] == '') {
                    $form_data[$this->table_name]['url']['value'] = $this->transliteMe($form_data[$this->table_name]['name']['value']);
                    $this->setRequestValue('url', $this->transliteMe($form_data[$this->table_name]['name']['value']));
                }


                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
                $OM = new Object_Manager();

                if (!$OM->check_data($form_data[$this->table_name]) || !$this->checkData()) {
                    if ($OM->GetErrorMessage() != '') {
                        $this->riseError($this->GetErrorMessage() . ' ' . $OM->GetErrorMessage());
                    }
                    $rs = $this->get_form($form_data[$this->table_name], 'edit');
                } else {
                    $this->edit_data($form_data[$this->table_name]);
                    if ($this->getError()) {
                        $rs = $this->get_form($form_data[$this->table_name], 'edit');
                    } else {
                        $rs .= $this->grid();
                    }
                }
                //return $rs;

                /* if ( !$this->checkData() ) {
                  $rs = $this->getForm('edit');
                  } else {
                  $this->editRecord($this->getRequestValue('id'));
                  $Cache=Cache::getInstance();
                  $Cache->clearValue('catalog_structure');
                  $rs = $this->getTopMenu();
                  $rs .= $this->grid();
                  } */
                break;

            case 'reorder_topics':
                $orderArray = $this->getRequestValue('order');
                $this->reorderTopics($orderArray);

                /* $Cache=Cache::getInstance();
                  $Cache->clearValue('catalog_structure'); */
                //$Cache->update();
                $rs = $this->getTopMenu();
                $rs .= $this->grid();

                break;

            default:
            {
                $rs .= $this->grid();
            }
        }

        $rs = $this->get_app_title_bar() . $this->getTopMenu() . $rs;
        return $rs;
    }

    function edit_data($form_data, $language_id = 0, $primary_key_value = false)
    {

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        if ($primary_key_value) {
            $query_params = $data_model->get_prepared_edit_query(DB_PREFIX . '_' . $this->table_name, $this->primary_key, $primary_key_value, $form_data, $language_id);
        } else {
            $query_params = $data_model->get_prepared_edit_query(DB_PREFIX . '_' . $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data, $language_id);
            $primary_key_value = $this->getRequestValue($this->primary_key);
        }

        $query_params_vals = $query_params['p'];


        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query_params['q'], $query_params_vals, $rows, $success);


        if (!$success) {
            $this->riseError($DBC->getLastError());
            return false;
        }

        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploads') {
                $imgs_uploads = $this->appendUploads($this->table_name, $form_item, $this->primary_key, (int)$this->getRequestValue($this->primary_key));
                //$this->set_imgs($imgs_uploads);
            } elseif ($form_item['type'] == 'select_by_query_multi') {
                //echo 1;
                $vals = $form_item['value'];
                if (!is_array($vals)) {
                    $vals = (array)$vals;
                }
                $query = 'DELETE FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name`=? AND `primary_id`=?';
                $stmt = $DBC->query($query, array($this->table_name, $form_item['name'], $primary_key_value));
                //echo $DBC->getLastError();
                if (!empty($vals)) {
                    //refresh
                    $query = 'INSERT INTO ' . DB_PREFIX . '_multiple_field (`table_name`, `field_name`, `primary_id`, `field_value`) VALUES (?,?,?,?)';
                    foreach ($vals as $val) {
                        $stmt = $DBC->query($query, array($this->table_name, $form_item['name'], $primary_key_value, $val));
                    }
                }
            }
        }
        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploadify_image') {
                $imgs = $this->editImageMulti($this->action, $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
                //$this->set_imgs($imgs);
            }
        }
        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploadify_file') {
                $imgs = $this->editFileMulti($this->action, $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
                //$this->set_imgs($imgs);
            }
        }
    }

    function add_data($form_data, $language_id = 0)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        //$query = $data_model->get_insert_query(DB_PREFIX.'_'.$this->table_name, $form_data, $language_id);
        $query_params = $data_model->get_prepared_insert_query(DB_PREFIX . '_' . $this->table_name, $form_data, $language_id);
        $query_params_vals = $query_params['p'];
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query_params['q'], $query_params_vals, $rows, $success);
        //$stmt=$DBC->query($query, array(), $row, $success);
        if (!$success) {
            $this->riseError($DBC->getLastError());
            return false;
        }
        $new_record_id = $DBC->lastInsertId();
        if ($new_record_id > 0) {
            foreach ($form_data as $form_item) {
                if ($form_item['type'] == 'uploads') {
                    $imgs_uploads = $this->appendUploads($this->table_name, $form_item, $this->primary_key, $new_record_id);

                    //$this->set_imgs($imgs_uploads);
                } elseif ($form_item['type'] == 'select_by_query_multi') {
                    //echo 1;
                    $vals = $form_item['value'];
                    if (!is_array($vals)) {
                        $vals = (array)$vals;
                    }
                    $query = 'DELETE FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name`=? AND `primary_id`=?';
                    $stmt = $DBC->query($query, array($this->table_name, $form_item['name'], $new_record_id));
                    //echo $DBC->getLastError();
                    if (!empty($vals)) {
                        //refresh
                        $query = 'INSERT INTO ' . DB_PREFIX . '_multiple_field (`table_name`, `field_name`, `primary_id`, `field_value`) VALUES (?,?,?,?)';
                        foreach ($vals as $val) {
                            $stmt = $DBC->query($query, array($this->table_name, $form_item['name'], $new_record_id, $val));
                        }
                    }
                }
            }
            $imgs = $this->editImageMulti($this->action, $this->table_name, $this->primary_key, $new_record_id);

            //$this->set_imgs($imgs);
        }

        return $new_record_id;
    }

    function get_app_title_bar()
    {
        $breadcrumbs = array();
        $breadcrumbs[] = array('href' => '#', 'title' => Multilanguage::_('L_ADMIN_MENU_APPLICATIONS'));

        if (!empty($this->app_title)) {
            $breadcrumbs[] = array('href' => '?action=' . $this->action . '', 'title' => $this->app_title);
        } else {
            $breadcrumbs[] = array('href' => '?action=' . $this->action . '', 'title' => $this->action);
        }
        $this->template->assign('breadcrumbs_array', $breadcrumbs);
        return '';
    }

    function saveAssociations($rules)
    {
        //print_r($rules);
        $category_structure = $this->loadCategoryStructure();
        foreach ($category_structure['childs'] as $k => $v) {
            $ret[$k] = $this->get_all_childs($k, $category_structure);
        }
        foreach ($ret[0] as $kk => $vv) {
            if (isset($rules[$vv])) {

                if ($rules[$vv]['legacy'] == 'on') {
                    $this->updateAssociations(array($vv), $rules[$vv]);
                    //echo '<br />START CHILDS UPDATING FOR ID '.$vv.':<br />';
                    $this->updateAssociations($ret[$vv], $rules[$vv]);
                    //echo ':END CHILDS UPDATING<br /><br />';
                    foreach ($ret[$vv] as $vvv) {
                        unset($rules[$vvv]);
                    }
                } else {
                    $this->updateAssociations(array($vv), $rules[$vv]);
                }
                //unset($rules[$vv]);
            } else {
                //echo 'NO RULES FOR ID:'.$vv.'<br />';
            }
            unset($rules[$vv]);
        }
        //print_r($ret);
    }

    private function updateAssociations($items = array(), $rules = array())
    {
        if (!empty($items)) {
            $DBC = DBC::getInstance();
            foreach ($items as $v) {
                $query = 'UPDATE ' . DB_PREFIX . '_topic SET obj_type_id=' . (int)$rules['obj_type_id'] . ', operation_type_id=' . (int)$rules['operation_type'] . ' WHERE id=' . (int)$v;
                $stmt = $DBC->query($query);
                //echo 'SET rules FOR ID:'.$v.' optype='.$rules['operation_type'].' realtytype='.$rules['obj_type_id'].'<br />';
            }
        }
    }

    /**
     * Get operation type name by ID
     * @param int $operation_type_id operation type id
     * @return string
     */
    function get_operation_type_name_by_id($operation_type_id)
    {
        return $this->operation_type_array[$operation_type_id]['name'];
    }

    /**
     * Get operation type select box
     * @param int $operation_type_id operation type id
     * @return string
     */
    function get_operation_type_select_box($operation_type_id)
    {
        $query = "SELECT * FROM " . DB_PREFIX . "_operation_type order by `operation_type_id` ";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        $rs = '<select name="operation_type_id">';
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                if ($operation_type_id == $ar['operation_type_id']) {
                    $selected = 'selected';
                } else {
                    $selected = '';
                }
                $rs .= '<option value="' . $ar['operation_type_id'] . '" ' . $selected . '>' . $ar['name'] . '</option>';
            }
        }
        $rs .= '</select>';
        return $rs;
    }

    /**
     * Edit record
     * @param int $id topic ID
     * @return boolean
     */
    /* function editRecord ( $id ) {
      $languages = Multilanguage::foreignLanguages();
      foreach ( $languages as $language_id => $language_title ) {
      $lang_string .= "name_{$language_id}='".$this->escape($this->getRequestValue('name_'.$language_id))."',";
      }

      $query = "update ".DB_PREFIX."_topic set
      name='".$this->escape($this->getRequestValue('name'))."',
      {$lang_string}
      parent_id='".(int)$this->getRequestValue('parent_id')."',
      url='".$this->escape(trim($this->getRequestValue('url')))."',
      meta_title='".$this->escape($this->getRequestValue('meta_title'))."',
      meta_keywords='".$this->escape($this->getRequestValue('meta_keywords'))."',
      meta_description='".$this->escape($this->getRequestValue('meta_description'))."',
      description='".$this->getRequestValue('description')."'
      where id=".$id."";

      $DBC=DBC::getInstance();
      $stmt=$DBC->query($query, array(), $row, $success_mark);
      if ( !$success_mark ) {
      echo $DBC->getLastError();
      }else{
      $imgs=$this->editImageMulti('topic', 'topic', 'id', $id);
      }
      return true;
      } */

    /**
     * Delete record
     * @param int $id topic ID
     * @return boolean
     */
    function deleteRecord($id)
    {
        $imgs_ids = array();
        $DBC = DBC::getInstance();
        if (1 == $this->getConfigValue('allow_topic_images')) {
            $query = 'SELECT image_id FROM ' . DB_PREFIX . '_topic_image WHERE id=' . $id;


            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $imgs_ids[] = $ar['image_id'];
                }
            }
        }

        $query = "delete from " . DB_PREFIX . "_topic where id=" . $id . "";
        $stmt = $DBC->query($query);
        if (!$stmt) {
            echo 'ERROR ON DELETE';
        }
        if (count($imgs_ids) > 0) {
            foreach ($imgs_ids as $im) {
                $this->deleteImage('topic', $im);
            }
        }
        return TRUE;
    }

    function deleteTopicItem($id, $delete_option, $childs_delete_option)
    {
        $DBC = DBC::getInstance();
        $childs_delete_option = 'move_up';
        if ($delete_option == 'delete_current') {
            if ($childs_delete_option == 'move_up') {
                $query = 'SELECT parent_id FROM ' . DB_PREFIX . '_topic WHERE id=? LIMIT 1';
                $stmt = $DBC->query($query, array($id));
                $ar = $DBC->fetch($stmt);
                $parent_id = (int)$ar['parent_id'];


                $query = 'UPDATE ' . DB_PREFIX . '_topic SET parent_id=? WHERE parent_id=?';
                $stmt = $DBC->query($query, array($parent_id, $id));

                $query = 'UPDATE ' . DB_PREFIX . '_data SET topic_id=? WHERE topic_id=?';
                $stmt = $DBC->query($query, array($parent_id, $id));

                $this->deleteRecord($id);
            }
        } elseif ($delete_option == 'delete_incoming') {
            if ($childs_delete_option == 'move_up') {
                $parent_id = $id;


                $category_structure = $this->loadCategoryStructure();
                $childs_topics = array();
                $childs_topics = $this->get_all_childs($id, $category_structure);
                if (count($childs_topics) > 0) {
                    $str_a = array();
                    foreach ($childs_topics as $ct) {
                        $str_a[] = '?';
                    }

                    $params = $childs_topics;
                    array_unshift($params, $id);

                    $query = 'UPDATE ' . DB_PREFIX . '_data SET topic_id=? WHERE topic_id IN (' . implode(',', $str_a) . ')';
                    $stmt = $DBC->query($query, $params);

                    foreach ($childs_topics as $did) {
                        $this->deleteRecord($did);
                    }
                }
            }
        } elseif ($delete_option == 'delete_branch') {
            if ($childs_delete_option == 'move_up') {
                $query = 'SELECT parent_id FROM ' . DB_PREFIX . '_topic WHERE id=? LIMIT 1';
                $stmt = $DBC->query($query, array($id));
                $ar = $DBC->fetch($stmt);
                $parent_id = (int)$ar['parent_id'];


                $category_structure = $this->loadCategoryStructure();
                $childs_topics = array();
                $childs_topics = $this->get_all_childs($id, $category_structure);

                if (count($childs_topics) > 0) {
                    $str_a = array();
                    foreach ($childs_topics as $ct) {
                        $str_a[] = '?';
                    }

                    $childs_topics[] = $id;
                    $str_a[] = '?';

                    $params = $childs_topics;
                    array_unshift($params, $parent_id);

                    $query = 'UPDATE ' . DB_PREFIX . '_data SET topic_id=? WHERE topic_id IN (' . implode(',', $str_a) . ')';
                    $stmt = $DBC->query($query, $params);

                    foreach ($childs_topics as $did) {
                        $this->deleteRecord($did);
                    }
                }
            }
        }
    }

    /**
     * Add record
     * @param void
     * @return boolean
     */
    /* function addRecord ( ) {
      $languages = Multilanguage::foreignLanguages();
      foreach ( $languages as $language_id => $language_title ) {
      $lang_string .= "name_{$language_id}='".$this->escape($this->getRequestValue('name_'.$language_id))."',";
      }
      $DBC=DBC::getInstance();
      $query = "insert into ".DB_PREFIX."_topic set
      name='".$this->escape($this->getRequestValue('name'))."',
      {$lang_string}
      parent_id='".(int)$this->getRequestValue('parent_id')."', url='".$this->getRequestValue('url')."',
      meta_title='".$this->escape($this->getRequestValue('meta_title'))."',
      meta_keywords='".$this->escape($this->getRequestValue('meta_keywords'))."',
      meta_description='".$this->escape($this->getRequestValue('meta_description'))."',
      description='".$this->getRequestValue('description')."'";
      //echo $query;
      $stmt=$DBC->query($query, $params);
      if ( !$stmt ) {
      //echo 'ERROR ON INSERT';
      }else{
      $new_record_id = $DBC->lastInsertId();
      $imgs=$this->editImageMulti('topic', 'topic', 'id', $new_record_id);
      }

      return true;
      } */

    function get_form($form_data = array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php')
    {

        $_SESSION['allow_disable_root_structure_select'] = false;
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
            $el['private'][] = array('html' => '<input type="hidden" name="id" value="' . $this->getRequestValue('id') . '" />');
        } else {
            $el['private'][] = array('html' => '<input type="hidden" name="do" value="edit_done" />');
            $el['private'][] = array('html' => '<input type="hidden" name="id" value="' . $form_data['id']['value'] . '" />');
        }
        $el['private'][] = array('html' => '<input type="hidden" name="action" value="structure">');
        $el['private'][] = array('html' => '<input type="hidden" name="language_id" value="' . $language_id . '">');

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

    /**
     * Get form
     * @param string $action action
     * @return string
     */
    function getForm($action = 'new')
    {
        $form_data = $this->getStrModel();
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
        $OM = new Object_Manager();
        $rs = $OM->get_form($form_data['structure'], $action, 0, '', SITEBILL_MAIN_URL . '/admin/index.php?action=structure');
        return $rs;
        global $debug_mode;
        $editor_code = $this->getConfigValue('editor');

        $languages = Multilanguage::foreignLanguages();


        $id = 'descr';

        if ($editor_code == 'ckeditor') {
            $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/ckeditor/ckeditor.js"></script>';
            $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/ckeditor/adapters/jquery.js"></script>';
            $rs .= '<script type="text/javascript">
        	$(document).ready(function() {
        	$("textarea#' . $id . '").ckeditor({
        	filebrowserBrowseUrl : \'/ckfinder/ckfinder.html\',
        	filebrowserImageBrowseUrl : \'/ckfinder/ckfinder.html?Type=Images\',
        	filebrowserFlashBrowseUrl : \'/ckfinder/ckfinder.html?Type=Flash\',
        	filebrowserUploadUrl : \'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files\',
        	filebrowserImageUploadUrl : \'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images\',
        	filebrowserFlashUploadUrl : \'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash\'
        });
        });
        </script>';
        } else {
            $rs .= '<link rel="stylesheet" type="text/css" href="' . SITEBILL_MAIN_URL . '/js/cleditor/jquery.cleditor.css" />
        	<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/js/cleditor/jquery.cleditor.min.js"></script>
        	<script type="text/javascript">
        	$(document).ready(function() {
        	$("textarea#' . $id . '").cleditor();
        });
        </script>
        ';
        }


        $rs .= '<form method="post" action="index.php" name="rentform" enctype="multipart/form-data">';
        $rs .= '<table border="0">';

        $rs .= '<tr>';
        $rs .= '<td colspan="2" style="text-align: center;"><b>' . sprintf(Multilanguage::_('L_NEED_REQUIERD_FIELDS'), '<span class="error">*</span>') . '</b></td>';
        $rs .= '</tr>';

        if ($this->GetError()) {
            $rs .= '<tr>';
            $rs .= '<td></td>';
            $rs .= '<td><span class="error">' . $this->GetError() . '</span></td>';
            $rs .= '</tr>';
        }

        /*
          $rs .= '<tr>';
          $rs .= '<td class="left_column">Тип операции <span class="error">*</span>:</td>';
          $rs .= '<td>'.$this->get_operation_type_select_box( $this->getRequestValue('operation_type_id') ).'</td>';
          $rs .= '</tr>';
         */

        $rs .= '<tr>';
        $rs .= '<td class="left_column">' . Multilanguage::_('PARENT_TOPIC', 'system') . ':</td>';
        $rs .= '<td>' . $this->getCategorySelectBox($this->getRequestValue('parent_id')) . '</td>';
        $rs .= '</tr>';

        $rs .= '<tr>';
        $rs .= '<td class="left_column">' . Multilanguage::_('TOPIC_NAME', 'system') . '<span class="error">*</span>:</td>';
        $rs .= '<td><input type="text" name="name" value="' . $this->getRequestValue('name') . '"></td>';
        $rs .= '</tr>';

        foreach ($languages as $language_id => $language_title) {
            $rs .= '<tr>';
            $rs .= '<td class="left_column">' . Multilanguage::_('TOPIC_NAME', 'system') . ' <b>' . $language_id . '</b>:</td>';
            $rs .= '<td><input type="text" name="name_' . $language_id . '" value="' . $this->getRequestValue('name_' . $language_id) . '"></td>';
            $rs .= '</tr>';
        }

        $rs .= '<tr>';
        $rs .= '<td class="left_column">' . Multilanguage::_('META_TITLE', 'system') . ':</td>';
        $rs .= '<td><input type="text" name="meta_title" value="' . $this->getRequestValue('meta_title') . '"></td>';
        $rs .= '</tr>';

        $rs .= '<tr>';
        $rs .= '<td class="left_column">' . Multilanguage::_('META_KEYWORDS', 'system') . ':</td>';
        $rs .= '<td><textarea name="meta_keywords" cols="50" rows="5">' . $this->getRequestValue('meta_keywords') . '</textarea></td>';
        $rs .= '</tr>';

        $rs .= '<tr>';
        $rs .= '<td class="left_column">' . Multilanguage::_('META_DESCRIPTION', 'system') . ':</td>';
        $rs .= '<td><textarea name="meta_description" cols="50" rows="5">' . $this->getRequestValue('meta_description') . '</textarea></td>';
        $rs .= '</tr>';


        $rs .= '<tr>';
        $rs .= '<td class="left_column">' . Multilanguage::_('DESCRIPTION', 'system') . ':</td>';
        $rs .= '<td><textarea id="' . $id . '" name="description" rows="10" cols="30">' . $this->getRequestValue('description') . '</textarea></td>';
        $rs .= '</tr>';

        $rs .= '<tr>';
        $rs .= '<td class="left_column">' . Multilanguage::_('FINE_URL_TEXT', 'system') . ' <span class="error">*</span>:</td>';
        $rs .= '<td><input type="text" name="url" value="' . $this->getRequestValue('url') . '"></td>';
        $rs .= '</tr>';

        if (1 == $this->getConfigValue('allow_topic_images')) {

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
            $form_generator = new Form_Generator();

            $form_data['name'] = 'image';
            $form_data['table_name'] = 'topic';
            $form_data['primary_key'] = 'id';
            $form_data['primary_key_value'] = $this->getRequestValue('id');
            $form_data['action'] = 'structure';
            $form_data['title'] = Multilanguage::_('L_UPLOADER_TITLE');
            $form_data['value'] = '';
            $form_data['length'] = 40;
            $form_data['type'] = 'uploadify_image';
            $form_data['required'] = 'off';
            $form_data['unique'] = 'off';

            $rs .= $form_generator->get_uploadify_row($form_data);
        }


        $rs .= '<tr>';
        $rs .= '<td></td>';
        if ($action == 'edit') {
            $rs .= '<input type="hidden" name="do" value="edit_done">';
            $rs .= '<input type="hidden" name="id" value="' . $this->getRequestValue('id') . '">';
        } else {
            $rs .= '<input type="hidden" name="do" value="done">';
        }
        $rs .= '<input type="hidden" name="action" value="structure">';

        $rs .= '<td><input type="submit" value="' . Multilanguage::_('L_TEXT_SAVE') . '"></td>';
        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '</form>';

        return $rs;
    }

    /**
     * Load
     * @param int $record_id record ID
     * @return boolean
     */
    function load($record_id)
    {
        $DBC = DBC::getInstance();

        $query = "select * from " . DB_PREFIX . "_topic where id=$record_id";
        $stmt = $DBC->query($query);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $this->setRequestValue('name', $ar['name']);

            $languages = Multilanguage::foreignLanguages();
            foreach ($languages as $language_id => $language_title) {
                $this->setRequestValue('name_' . $language_id, $ar['name_' . $language_id]);
            }


            $this->setRequestValue('id', $ar['id']);
            $this->setRequestValue('url', $ar['url']);
            $this->setRequestValue('description', $ar['description']);
            $this->setRequestValue('parent_id', $ar['parent_id']);
            $this->setRequestValue('meta_title', $ar['meta_title']);
            $this->setRequestValue('meta_keywords', $ar['meta_keywords']);
            $this->setRequestValue('meta_description', $ar['meta_description']);
        }
    }

    /**
     * Check data
     * @param void
     * @return boolean
     */
    function checkData()
    {
        if ($this->getRequestValue('name') == '') {
            $this->riseError(Multilanguage::_('NOT_SET_TOPIC_NAME', 'system'));
            return false;
        }
        if ($this->getRequestValue('parent_id') == $this->getRequestValue('id')) {
            $this->riseError(Multilanguage::_('CANT_BE_PARENT_YOURSELF', 'system'));
            return false;
        }

        if (0 != (int)$this->getRequestValue('id') && 0 != (int)$this->getRequestValue('parent_id')) {
            $id = (int)$this->getRequestValue('id');
            $parent_id = (int)$this->getRequestValue('parent_id');
            $category_structure = $this->loadCategoryStructure();
            $childs = $this->get_all_childs($id, $category_structure);
            if (in_array($parent_id, $childs)) {
                $this->riseError(Multilanguage::_('CANT_BE_PARENT_YOURSELF', 'system'));
                return false;
            }
        }

        if ($this->getRequestValue('url') == '') {
            $this->riseError('Не указан ЧПУ каталога');
            return false;
        }

        return true;
    }

    /**
     * Get top menu
     * @param void
     * @return string
     */
    function getTopMenu()
    {
        $rs = '<a href="?action=structure&do=new" class="btn btn-primary">' . Multilanguage::_('L_ADD_RECORD_BUTTON') . '</a> ';
        if ($this->getConfigValue('use_topic_linker')) {
            $rs .= '<a href="?action=structure&do=linker" class="btn btn-primary">' . Multilanguage::_('L_TOPIC_LINKER') . '</a> ';
        }
        //$rs .= '<a href="?action=structure&do=loadlist" class="btn btn-primary">Загрузить списком</a> ';
        //$rs = '<a href="?action=structure" class="btn btn-primary">'.Multilanguage::_('TOPIC_LIST','system').'</a>';
        //$rs .= '<a href="?action=structure&do=chains" class="btn btn-primary">Структурные цепочки</a>';
        /* $rs .= '
          <div class="navbar">
          <div class="navbar-inner">
          <div class="container">

          <a href="?action=structure&do=new" class="btn btn-primary">'.Multilanguage::_('ADD_TOPIC','system').'</a>
          </div>
          </div>
          </div>
          <p>Вид менеджера: '.((isset($_SESSION['structure_manager_grid_type']) && $_SESSION['structure_manager_grid_type']=='new') ? 'Новый <a href="'.SITEBILL_MAIN_URL.'/admin/index.php?action=structure&structure_manager_grid_type=old">Старый</a>' : '<a href="'.SITEBILL_MAIN_URL.'/admin/index.php?action=structure&structure_manager_grid_type=new">Новый</a> Старый').'</p>
          '; */

        //$rs .= '<a href="?action=structure&do=associations" class="btn btn-primary">'.Multilanguage::_('COMPARISONS','system').'</a>';
        return $rs;
    }

    /**
     * Возвращает ассоциативный массив соответствий id категорий и составным иерархическим урлам
     * @return array
     *
     */
    function loadCategoriesUrls()
    {

        /* if($this->getConfigValue('apps.cache.enable')==1){
          $Cache=Cache::getInstance();
          if($Cache->isValid('categories_urls','expired')){
          $ret=$Cache->getValue('categories_urls');
          }else{
          $ret=$this->createCategoriesUrls();
          $Cache->addValue('categories_urls', $ret, (time()+86400));
          }
          }else{
          $ret=$this->createCategoriesUrls();
          } */
        $ret = $this->createCategoriesUrls();
        return $ret;
    }

    /**
     * Создает ассоциативный массив соответствий id категорий и составным иерархическим урлам
     * @return array
     */
    private function createCategoriesUrls()
    {

        if (self::$_category_urls === NULL) {
            $ret = array();
            $_ret = array();
            $DBC = DBC::getInstance();

            $query = 'SELECT id, parent_id, url AS name FROM ' . DB_PREFIX . '_topic';
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $categories[$ar['id']] = $ar['name'];
                    $items[$ar['id']] = $ar['parent_id'];
                    $points[] = $ar['id'];
                }
            }


            if (is_array($points) && count($points) > 0) {
                if (1 == $this->getConfigValue('apps.seo.level_enable')) {
                    foreach ($points as $p) {
                        $chain = array();
                        $chain[] = $categories[$p];
                        $this->appendParent($p, $items, $chain, $categories);
                        $_ret[$p]['chain_parts'] = $chain;
                    }

                    foreach ($_ret as $k => $r) {
                        $ret[$k] = implode('/', $r['chain_parts']);
                    }
                } else {
                    foreach ($points as $p) {
                        $ret[$p] = $categories[$p];
                    }
                }
            }
            self::$_category_urls = $ret;
        }

        return self::$_category_urls;
    }

    /**
     * Ищет транслитерированный урл предка для конкретного элемента
     */
    private function appendParent($child_id, &$items, &$chain, $categories)
    {
        if ((int)$items[$child_id] !== 0) {
            array_unshift($chain, $categories[$items[$child_id]]);
            $this->appendParent($items[$child_id], $items, $chain, $categories);
        }
    }

    function createCatalogChains()
    {
        $ret = array();
        $points = array();

        $fname = 'name';
        if (1 === intval($this->getConfigValue('apps.language.use_langs'))) {
            $curlang = $this->getCurrentLang();
            $default_lng = '';
            if (1 == $this->getConfigValue('apps.language.use_default_as_ru')) {
                $default_lng = 'ru';
            } elseif ('' != trim($this->getConfigValue('apps.language.use_as_default'))) {
                $default_lng = trim($this->getConfigValue('apps.language.use_as_default'));
            }
            if ($default_lng != '' && $default_lng == $curlang) {

            } else {
                $fname .= '_' . $curlang;
            }
        }

        $query = 'SELECT id, parent_id, LOWER(`' . $fname . '`) AS name FROM ' . DB_PREFIX . '_topic';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $categories[$ar['id']] = $ar['name'];
                $items[$ar['id']] = $ar['parent_id'];
                $points[] = $ar['id'];
            }
        }
        if (!empty($points)) {
            foreach ($points as $p) {
                $chain = $categories[$p];
                $chain_num = $p;
                $this->findParent($p, $items, $chain, $chain_num, $categories);
                $ret[$p] = $chain;
                $ret_num[$p] = $chain_num;
            }
        }
        $ret_arr = array();
        if (!empty($ret_num)) {
            foreach ($ret_num as $k => $v) {
                $ret_arr[$k] = explode('|', $v);
            }
        }
        return $rs = array('txt' => $ret, 'num' => $ret_num, 'ar' => $ret_arr);
    }

    function findParent($child_id, &$items, &$chain, &$chain_num, $categories)
    {
        if ((int)$items[$child_id] !== 0) {
            //echo $child_id.' has parent '.$items[$child_id].'<br>';;
            $chain = $categories[$items[$child_id]] . '|' . $chain;
            $chain_num = $items[$child_id] . '|' . $chain_num;
            $this->findParent($items[$child_id], $items, $chain, $chain_num, $categories);
        }
    }

    function convertToNestedArray($structure, $parent_id = 0, $level = 0)
    {
        $nested = array();
        $level++;
        if ($level > 999) {
            echo 'to many levels in structure';
            exit;
        }
        foreach ($structure['childs'][$parent_id] as $item_id => $topic_id) {
            $structure['catalog'][$topic_id]['level'] = $level;
            $params['topic_id'] = $topic_id;
            $structure['catalog'][$topic_id]['breadcrumbs'] = $this->get_category_breadcrumbs_string($params, $structure, SITEBILL_MAIN_URL . '/');
            $structure['catalog'][$topic_id]['value'] = $structure['catalog'][$topic_id]['breadcrumbs'];

            array_push($nested, $structure['catalog'][$topic_id]);
            if (is_array($structure['childs'][$topic_id]) && count($structure['childs'][$topic_id]) > 0) {
                $tmp = $this->convertToNestedArray($structure, $topic_id, $level);
                foreach ($tmp as $tmp_item_id => $tmp_array) {
                    array_push($nested, $tmp_array);
                }
            }
        }
        return $nested;
    }


    /*function getCategoryTreeArray($first_run = true, $load_published = false, $id = 0, &$el = null, $structure = null){

        if($first_run){

            echo 'first run';
            $ret = array();

            $structure = $this->loadCategoryStructure($load_published);

            if(!empty($structure['childs'][0])){
                foreach($structure['childs'][0] as $cid){
                    $x = array(
                        'name' => $structure['catalog'][$cid]['name'],
                        'url' => $structure['catalog'][$cid]['url'],
                        'href' => SITEBILL_MAIN_URL.'/'.$structure['catalog'][$cid]['url'].self::$_trslashes
                    );

                    if(isset($structure['childs'][$cid]) && is_array($structure['childs'][$cid]) && !empty($structure['childs'][$cid])){
                        $this->getCategoryTreeArray(false, $load_published, $cid, $x, $structure);
                    }
                    $ret[] = $x;
                }


            }
            print_r($ret);
        }else{

            echo 'inside '.$id;

            foreach($structure['childs'][$id] as $cid){
                //print_r($x);
                $x = array(
                    'name' => $structure['catalog'][$cid]['name'],
                    'url' => $structure['catalog'][$cid]['url'],
                    'href' => SITEBILL_MAIN_URL.'/'.$structure['catalog'][$cid]['url'].self::$_trslashes
                );
                //print_r($x);
                if(isset($structure['childs'][$cid]) && is_array($structure['childs'][$cid]) && !empty($structure['childs'][$cid])){
                    $this->getCategoryTreeArray(false, $load_published, $cid, $x, $structure);
                }

                $el['childs'][] = $x;
            }
        }








    }*/

    /**
     * Load category structure
     * @param $load_published true/false - параметр определяет загружать ли категории по статусу активности. Если true - то будут загружены только активные. Если false - то будут загружены все категории
     * @return array
     */
    function loadCategoryStructure($load_published = false)
    {
        $where_active_condition = '';
        if ($load_published) {
            if (self::$_category_structure_published !== NULL) {
                return self::$_category_structure_published;
            }
        } else {
            if (self::$_category_structure !== NULL) {
                return self::$_category_structure;
            }
        }
        $DBC = DBC::getInstance();

        if ($load_published) {
            $where_active_condition = ' WHERE t.`published`=1 ';
        }

        //$query = "SELECT t.* FROM " . DB_PREFIX . "_topic t " . $where_active_condition . " ORDER BY parent_id ASC, `order` ASC, name ASC  ";
        $query = "SELECT t.* FROM " . DB_PREFIX . "_topic t " . $where_active_condition . " ORDER BY `order` ";

        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                if (Multilanguage::get_current_language() != 'ru' && $ar['name_' . Multilanguage::get_current_language()] != '') {
                    $ar['name'] = $ar['name_' . Multilanguage::get_current_language()];
                }

                $ret['catalog'][$ar['id']] = $ar;
                $ret['childs'][$ar['parent_id']][] = $ar['id'];
            }
        }

        if (1 == $this->getConfigValue('apps.seo.level_enable')) {
            $urls = $this->loadCategoriesUrls();
            if (is_array($ret['catalog']) && count($ret['catalog']) > 0) {
                foreach ($ret['catalog'] as $k => $v) {
                    $ret['catalog'][$k]['url'] = $urls[$v['id']];
                }
            }
        }

        if (1 == $this->getConfigValue('allow_topic_images')) {
            if (is_array($ret['catalog']) && count($ret['catalog']) > 0) {
                foreach ($ret['catalog'] as $k => $v) {
                    $query = "select i.* from " . DB_PREFIX . "_topic_image as li, " . DB_PREFIX . "_image as i where li.id=$k and li.image_id=i.image_id order by li.sort_order";
                    $stmt = $DBC->query($query);
                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            $ret['catalog'][$k]['images'][] = $ar;
                        }
                    }
                }
            }
        }

        $current = $this->urlAnalizer();
        if ($current !== FALSE) {
            $this->findCurrent($ret, $current);
        }
        if ($load_published) {
            self::$_category_structure_published = $ret;
        } else {
            self::$_category_structure = $ret;
        }
        return $ret;
    }

    /**
     * Возвращает массив всех вложенных категорий для искомой
     * @param int $category_id category ID
     * @param array $category_structure structure data array
     * @return array
     */
    function get_all_childs($category_id, $category_structure)
    {
        $ra = array();
        //echo 'category_id = '.$category_id.'<br>';
        if (isset($category_structure['childs'][$category_id]) && count($category_structure['childs'][$category_id]) > 0) {
            $ra = $category_structure['childs'][$category_id];

            foreach ($category_structure['childs'][$category_id] as $item_id => $child_id) {
                if (isset($category_structure['childs'][$child_id]) && count($category_structure['childs'][$child_id]) > 0) {
                    $ra = array_merge($ra, $this->get_all_childs($child_id, $category_structure));
                }
            }
        }
        return $ra;
    }

    /**
     * Определяем объект в контексте которого запускается класс
     * Нужно для передачи объекта при генерации дерева категорий с подсчетом количества объявлений
     * из тегов
     * @param object $context_object
     */
    function set_context($context_object)
    {
        $this->context_object = $context_object;
    }

    /**
     * Возвращает контекст
     * @return object
     */
    function get_context()
    {
        return $this->context_object;
    }

    /**
     * Load data structure
     * @param int $user_id
     * @return array
     */
    function load_data_structure($user_id, $params = array(), $search_params = array())
    {
        $where_array = array();

        if ($this->get_context() != NULL) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/grid.php');
            $common_grid = new Common_Grid($this->get_context());
            $common_grid->set_action('data');
            $common_grid->set_grid_table('data');

            $tagged_params = $common_grid->add_tags_params();
            $where_array = $common_grid->add_tagged_parms_to_where($where_array, $tagged_params, 'data');
        }

        if ($user_id == 0) {
            if (isset($params['active']) && $params['active'] == 1) {
                $where_array[] = DB_PREFIX . '_data.`active`=1';
            } elseif (isset($params['active']) && $params['active'] == 'notactive') {
                $where_array[] = DB_PREFIX . '_data.`active`=0';
            }

            if (isset($params['hot']) && $params['hot'] == 1) {
                $where_array[] = DB_PREFIX . '_data.`hot`=1';
            }

            if (count($search_params) > 0) {
                foreach ($search_params as $v) {
                    $where_array[] = DB_PREFIX . '_data.`' . $v . '`';
                }
            }
            /*
              if(isset($params['realty_type_id'])){
              $where_array[] = 're_data.realty_type_id='.(int)$params['realty_type_id'];
              }
             */
            $where = '';
            if (count($where_array) > 0) {
                $where = ' WHERE ' . implode(' AND ', $where_array);
            }
            //$query = "SELECT id, topic_id FROM ".DB_PREFIX."_data ".$where;
            $query = "SELECT COUNT(id) as total, topic_id FROM " . DB_PREFIX . "_data " . $where . " GROUP BY topic_id";
        } else {
            $where = ' 1 = 1 ';
            if (count($where_array) > 0) {
                $where = implode(' AND ', $where_array);
            }

            // $query = "SELECT id, topic_id FROM ".DB_PREFIX."_data  where user_id = $user_id";
            $query = "SELECT COUNT(id) as total, topic_id FROM " . DB_PREFIX . "_data  where " . $where . " AND user_id = $user_id GROUP BY topic_id";
        }
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {

                $ret['data'][$user_id][$ar['topic_id']] = $ar['total'];
            }
        }

        return $ret;
    }

    /**
     * Load data structure for shop
     * @param int $user_id
     * @return array
     */
    function load_data_structure_shop($user_id, $params = array())
    {
        $where_array = array();
        //echo '<pre>';
        //print_r($params);
        //echo '</pre>';
        $language_id = ((int)$this->getRequestValue('language_id') == 0 ? 0 : (int)$this->getRequestValue('language_id'));


        if ($user_id == 0) {

            //$enable_publication_limit=$this->getConfigValue('apps.shop.user_limit_enable');

            if ($params['enable_publication_limit'] == 1) {
                $where_array[] = '((' . DB_PREFIX . '_shop_product.product_add_date+' . DB_PREFIX . '_user.publication_limit*24*3600)>' . time() . ')';
            }

            if ($params['active'] == 1) {
                $where_array[] = DB_PREFIX . '_shop_product.active=1';
            } elseif ($params['active'] == 'notactive') {
                $where_array[] = DB_PREFIX . '_shop_product.active=0';
            }

            if (isset($params['city_id'])) {
                $where_array[] = '(' . DB_PREFIX . '_shop_product.city_id=' . $params['city_id'] . ')';
            }

            $where_array[] = DB_PREFIX . '_shop_product.language_id=' . $language_id;

            if (count($where_array) > 0) {
                $where = ' WHERE ' . implode(' AND ', $where_array);
            }
            //$query = "SELECT product_id, category_id FROM ".DB_PREFIX."_shop_product ".$where;
            $query = "SELECT COUNT(" . DB_PREFIX . "_shop_product.product_id) as total, " . DB_PREFIX . "_shop_product.category_id FROM " . DB_PREFIX . "_shop_product LEFT JOIN " . DB_PREFIX . "_user ON " . DB_PREFIX . "_shop_product.user_id=" . DB_PREFIX . "_user.user_id " . $where . " GROUP BY " . DB_PREFIX . "_shop_product.category_id";
        } else {

            if ($params['active'] == 1) {
                $where_array[] = DB_PREFIX . '_shop_product.active=1';
            } elseif ($params['active'] == 'notactive') {
                $where_array[] = DB_PREFIX . '_shop_product.active=0';
            } elseif ($params['archived'] == 1) {
                $where_array[] = '((' . DB_PREFIX . '_shop_product.product_add_date+' . DB_PREFIX . '_user.publication_limit*24*3600)<' . time() . ')';
            } elseif ($params['archived'] == 'notarchived') {
                $where_array[] = '((' . DB_PREFIX . '_shop_product.product_add_date+' . DB_PREFIX . '_user.publication_limit*24*3600)>' . time() . ')';
            }

            $where_array[] = DB_PREFIX . '_shop_product.user_id = ' . $user_id;

            $where = ' WHERE ' . implode(' AND ', $where_array);
            //$query = "SELECT product_id, category_id FROM ".DB_PREFIX."_shop_product  where user_id = $user_id";
            $query = "SELECT COUNT(product_id) as total, category_id FROM " . DB_PREFIX . "_shop_product LEFT JOIN " . DB_PREFIX . "_user ON " . DB_PREFIX . "_shop_product.user_id=" . DB_PREFIX . "_user.user_id   " . $where . " GROUP BY category_id";
        }
        //echo $query.'<br>';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {

                $ret['data'][$user_id][$ar['category_id']] = $ar['total'];
            }
        }
        return $ret;
    }

    /**
     * Load data structure for price
     * @param int $user_id
     * @return array
     */
    function load_data_structure_price($user_id, $params = array())
    {
        $where_array = array();
        $language_id = ((int)$this->getRequestValue('language_id') == 0 ? 0 : (int)$this->getRequestValue('language_id'));

        if (count($where_array) > 0) {
            $where = ' WHERE ' . implode(' AND ', $where_array);
        }
        $query = "SELECT COUNT(" . DB_PREFIX . "_price.price_id) as total, " . DB_PREFIX . "_price.category_id FROM " . DB_PREFIX . "_price GROUP BY " . DB_PREFIX . "_price.category_id";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {

                $ret['data'][$user_id][$ar['category_id']] = $ar['total'];
            }
        }
        return $ret;
    }

    /**
     * Get category select box
     * @param int $current_category_id category ID
     * @param mixed $ajax_function
     * @return string
     */
    function getCategorySelectBox($current_category_id, $ajax_function = false)
    {
        $category_structure = $this->loadCategoryStructure();
        $level = 1;
        $rs = '';
        if ($ajax_function) {
            $rs .= '<select name="parent_id" id="parent_id" onchange="' . $ajax_function . '">';
        } else {
            $rs .= '<select name="parent_id">';
        }
        $rs .= '<option value="0">' . Multilanguage::_('L_CHOOSE_TOPIC') . '</option>';
        if (isset($category_structure['childs'][0]) && count($category_structure['childs'][0]) > 0) {
            foreach ($category_structure['childs'][0] as $item_id => $categoryID) {
                //echo $categoryID.'<br>';
                //echo 'items = '.$items.'<br>';
                if ($current_category_id == $categoryID) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }

                /* if($this->getConfigValue('disable_root_structure_select')==1){
                  $disabled=' disabled="disabled"';
                  }else{
                  $disabled='';
                  } */

                $rs .= '<option value="' . $categoryID . '" ' . $selected . $disabled . '>' . str_repeat(' . ', $level) . $category_structure['catalog'][$categoryID]['name'] . '</option>';
                $rs .= $this->getChildNodes($categoryID, $category_structure, $level + 1, $current_category_id);
            }
        }

        $rs .= '</select>';
        return $rs;
    }

    function getLevel($category_structure, $pid, $level, &$leveled, $selected_id, &$now_selected, &$find_more)
    {
        $fm = $find_more;
        if (isset($category_structure['childs'][$pid]) && count($category_structure['childs'][$pid]) > 0) {
            $i = 0;
            foreach ($category_structure['childs'][$pid] as $item_id => $categoryID) {
                $leveled[$level][$pid][$i] = array($category_structure['catalog'][$categoryID]['id'], $category_structure['catalog'][$categoryID]['name'], 0);
                //$ob=
                if ($categoryID == $selected_id) {
                    //$ob[2]=1;
                    $leveled[$level][$pid][$i][2] = 1;
                    $fm = false;
                    $now_selected = true;
                }

                $this->getLevel($category_structure, $categoryID, $level + 1, $leveled, $selected_id, $now_selected, $fm);
                if ($fm && $now_selected) {
                    //$ob[2]=1;
                    $leveled[$level][$pid][$i][2] = 1;
                    $fm = false;
                }

                $i++;
            }
        }
    }

    function getCategorySelectBoxLeveled($name, $selected, $options = array(), $model_item = array())
    {
        $category_structure = $this->loadCategoryStructure($this->getConfigValue('use_topic_publish_status'));
        //$selected=intval($this->getRequestValue('topic_id'));
        $now_selected = false;
        $find_more = true;
        $leveled = array();
        $pid = 0;
        $level = 1;

        if (!defined('ADMIN_MODE')) {
            $bootstrap_version = trim($this->getConfigValue('bootstrap_version'));
            if ($bootstrap_version == '3') {
                $classes = 'form-control';
            } elseif ($bootstrap_version == '4') {
                $classes = 'mdb-select';
            } elseif ($bootstrap_version == '4md') {
                $classes = 'mdb-select';
            } else {
                $classes = '';
            }
        } else {
            $classes = '';
        }

        $this->getLevel($category_structure, $pid, $level, $leveled, $selected, $now_selected, $find_more);

        $rt .= '<div class="leveled">';
        $rt .= '<input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $selected . '">';
        foreach ($leveled as $lev => $it) {
            $active = 0;
            $rt .= '<div class="level" data-level="' . $lev . '">';
            foreach ($it as $pid => $pvals) {
                $rt .= '<div data-id="' . $pid . '" class="levelitem levelitem_' . $pid . '" style="display: none;">';
                if ($lev > 1) {
                    $rt .= _e('Подтип недвижимости') . ' ';
                    if (isset($model_item) and $model_item['required'] == 'on') {
                        $rt .= '<span style="color: red;">*</span> ';
                    }
                }
                $rt .= '<select' . ($classes != '' ? ' class="' . $classes . '"' : '') . '>';

                $tname = '';
                if ($lev == 1) {
                    $tname = (isset($options['zerotitle']) && $options['zerotitle'] != '' ? $options['zerotitle'] : '--');
                } else {
                    $tname = (isset($options['nonzerotitle']) && $options['nonzerotitle'] != '' ? $options['nonzerotitle'] : '--');
                }

                $rt .= '<option value="0">' . $tname . '</option>';
                foreach ($pvals as $pval) {
                    $rt .= '<option value="' . $pval[0] . '"' . ($pval[2] == 1 ? ' selected="selected"' : '') . '>' . $pval[1] . '</option>';
                }
                $rt .= '</select>';
                $rt .= '</div>';
            }

            $rt .= '</div>';
        }
        $rt .= '</div>';
        return $rt;
    }

    /**
     * Get category select box
     * @param string $name name
     * @param int $current_category_id category ID
     * @param mixed $ajax_function
     * @return string
     */
    function getCategorySelectBoxWithName($name, $current_category_id, $ajax_function = false, $parameters = array(), $zero_title = '')
    {
        //echo '$current_category_id = '.$current_category_id;
        $core_level_symbol = $this->getConfigValue('core_level_symbol');
        $core_level_symbol = str_replace('#', ' ', $core_level_symbol);

        if (!defined('ADMIN_MODE')) {

            if (isset($parameters['classes'])) {
                $classes = $parameters['classes'];
            } else {
                $bootstrap_version = trim($this->getConfigValue('bootstrap_version'));
                if ($bootstrap_version == '3') {
                    $classes = 'form-control';
                } elseif ($bootstrap_version == '4') {
                    $classes = 'mdb-select';
                } elseif ($bootstrap_version == '4md') {
                    $classes = 'mdb-select';
                } else {
                    $classes = '';
                }
            }


        } else {
            $classes = '';
        }

        //$start=68;
        $start = 0;

        if (isset($parameters['ignore_published_status']) && $parameters['ignore_published_status']) {
            $category_structure = $this->loadCategoryStructure();
        } else {
            $category_structure = $this->loadCategoryStructure($this->getConfigValue('use_topic_publish_status'));
        }
        if (isset($parameters['only_top_level']) && $parameters['only_top_level']) {
            $tmp = $category_structure['childs'][0];
            unset($category_structure['childs']);
            $category_structure['childs'][0] = $tmp;
        }

        if (isset($parameters['enabled_ids']) && '' != trim($parameters['enabled_ids'])) {
            preg_match_all('/(\d+)/', $parameters['enabled_ids'], $matches);
            if (isset($matches[1]) && is_array($matches[1]) && !empty($matches[1])) {
                foreach ($category_structure['childs'][0] as $k => $v) {
                    if (!in_array($v, $matches[1])) {
                        unset($category_structure['childs'][0][$k]);
                    }
                }
            }
        }
        //echo '<pre>';
        //print_r($category_structure);

        $level = 1;
        $rs = '';
        $multiple = false;
        if (is_array($current_category_id)) {
            $multiple = true;
        }
        if ($ajax_function) {
            $rs .= '<select name="' . $name . ($multiple ? '[]' : '') . '" id="' . $name . '"' . ($classes != '' ? ' class="' . $classes . '"' : '') . ' onchange="' . $ajax_function . '"' . ($multiple ? ' multiple="multiple"' : '') . '>';
        } else {
            $rs .= '<select name="' . $name . ($multiple ? '[]' : '') . '"' . ($multiple ? ' multiple="multiple"' : '') . ' id="' . $name . '"' . ($classes != '' ? ' class="' . $classes . '"' : '') . '>';
        }

        if ($zero_title == '') {
            $title_default = Multilanguage::_('L_CHOOSE_TOPIC');
        } else {
            $title_default = $zero_title;
        }

        if (!$multiple) {
            $rs .= '<option class="rootlevel rootlevel_0" value="' . ($start != 0 ? $start : '0') . '">' . $title_default . '</option>';
        }
        if (isset($category_structure['childs'][$start]) && count($category_structure['childs'][$start]) > 0) {
            foreach ($category_structure['childs'][$start] as $item_id => $categoryID) {
                $superparent = $categoryID;
                if ($multiple) {
                    if (in_array($categoryID, $current_category_id)) {
                        $selected = " selected ";
                    } else {
                        $selected = "";
                    }
                } else {
                    if ($current_category_id == $categoryID) {
                        $selected = " selected ";
                    } else {
                        $selected = "";
                    }
                }

                if (($this->getConfigValue('disable_root_structure_select') == 1 || $this->getConfigValue('disable_root_structure_select') == 3) && isset($_SESSION['allow_disable_root_structure_select']) && $_SESSION['allow_disable_root_structure_select'] === true) {
                    $disabled = ' disabled="disabled" style="background-color:#eee;"';
                } elseif ($this->getConfigValue('disable_root_structure_select') == 2 && isset($category_structure['childs'][$categoryID]) && is_array($category_structure['childs'][$categoryID]) && isset($_SESSION['allow_disable_root_structure_select']) && $_SESSION['allow_disable_root_structure_select'] === true) {
                    $disabled = ' disabled="disabled" style="background-color:#eee;"';
                } else {
                    $disabled = '';
                }


                if (function_exists('BeforPrintOptionName_getCategorySelectBoxWithName')) {
                    $option_title = BeforPrintOptionName_getCategorySelectBoxWithName($category_structure['catalog'][$categoryID]['name']);
                } else {
                    $option_title = $category_structure['catalog'][$categoryID]['name'];
                }
                $rs .= '<option class="rootlevel rootlevel_' . $level . '" data-superparent="' . $superparent . '" value="' . $categoryID . '" ' . $selected . $disabled . '>' . str_repeat($core_level_symbol, $level) . $option_title . '</option>';
                $rs .= $this->getChildNodes($categoryID, $category_structure, $level + 1, $current_category_id, $superparent);
            }
        }

        $_SESSION['allow_disable_root_structure_select'] = false;
        $rs .= '</select>';
        return $rs;
    }

    function getCategoryCheckboxes($name, $current_category_id, $ajax_function = false)
    {
        $category_structure = $this->loadCategoryStructure();
        $rs = '';
        if (isset($category_structure['childs'][0]) && count($category_structure['childs'][0]) > 0) {
            $rs .= '<style></style>';
            $rs .= '<div class="checkbox_collection">';
            $rs .= '<a href="#" class="checkbox_collection_decheck">Очистить все</a>';
            foreach ($category_structure['childs'][0] as $item_id => $categoryID) {

                $rs .= '<div class="ait_bc">';
                $rs .= '<div class="ait_bc_h"><input name="' . $name . '[]" value="' . $category_structure['catalog'][$categoryID]['id'] . '" type="checkbox"' . (in_array($categoryID, $current_category_id) ? ' checked="checked"' : '') . ' /> ' . $category_structure['catalog'][$categoryID]['name'] . '</div>';
                $rs .= $this->getChildNodesCheckboxes($name, $categoryID, $category_structure, $current_category_id);
                $rs .= '</div>';
            }
            $rs .= '</div>';
        }
        return $rs;
    }

    function getChildNodesCheckboxes($name, $categoryID, $category_structure, $current_category_id)
    {
        $rs = '';
        if (isset($category_structure['childs'][$categoryID]) && count($category_structure['childs'][$categoryID]) > 0) {
            foreach ($category_structure['childs'][$categoryID] as $child_id) {
                $rs .= '<div class="ait_bc">';
                $rs .= '<div class="ait_bc_h"><input name="' . $name . '[]" value="' . $category_structure['catalog'][$child_id]['id'] . '" type="checkbox"' . (in_array($child_id, $current_category_id) ? ' checked="checked"' : '') . ' /> ' . $category_structure['catalog'][$child_id]['name'] . '</div>';

                if (isset($category_structure['childs'][$child_id])) {
                    if (count($category_structure['childs'][$child_id]) > 0) {
                        $rs .= $this->getChildNodesCheckboxes($name, $child_id, $category_structure, $current_category_id);
                    }
                }
                $rs .= '</div>';
            }
        }
        return $rs;
    }

    function getShopCategorySelectBoxWithName($name, $current_category_id, $ajax_function = false)
    {
        //echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadShopCategoryStructure();
        //echo '<pre>';
        //print_r($category_structure);
        $level = 1;
        $rs = '';
        if ($ajax_function) {
            $rs .= '<select name="' . $name . '" id="' . $name . '" onchange="' . $ajax_function . '">';
        } else {
            $rs .= '<select name="' . $name . '">';
        }
        $rs .= '<option value="0">' . Multilanguage::_('L_CHOOSE_TOPIC') . '</option>';
        foreach ($category_structure['childs'][0] as $item_id => $categoryID) {
            //echo $categoryID.'<br>';
            //echo 'items = '.$items.'<br>';
            if ($current_category_id == $categoryID) {
                $selected = " selected ";
            } else {
                $selected = "";
            }

            $rs .= '<option value="' . $categoryID . '" ' . $selected . '>' . str_repeat(' . ', $level) . $category_structure['catalog'][$categoryID]['category_name'] . '</option>';
            $rs .= $this->getShopChildNodes($categoryID, $category_structure, $level + 1, $current_category_id);
        }
        $rs .= '</select>';
        return $rs;
    }

    /**
     * Load mark structure
     * @param void
     * @return array
     */
    function load_mark_structure()
    {
        $query = "SELECT * FROM " . DB_PREFIX . "_mark order by `name` ";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret['mark'][$ar['mark_id']] = $ar;
                $ret['childs'][$ar['parent_id']][] = $ar['mark_id'];
            }
        }
        return $ret;
    }

    /**
     * Load coachwork structure
     * @param void
     * @return array
     */
    function load_coachwork_structure()
    {
        $ret = array();
        $query = "SELECT * FROM " . DB_PREFIX . "_coachwork order by `name` ";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret['coachwork'][$ar['coachwork_id']] = $ar;
                $ret['childs'][$ar['parent_id']][] = $ar['coachwork_id'];
            }
        }
        return $ret;
    }

    /**
     * Load model structure
     * @param void
     * @return array
     */
    function load_model_structure()
    {
        $query = "SELECT * FROM " . DB_PREFIX . "_model order by `name` ";
        //echo $query;
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret['model'][$ar['model_id']] = $ar;
                $ret['childs'][$ar['mark_id']][] = $ar['model_id'];
            }
        }
        return $ret;
    }

    /**
     * Load modification structure
     * @param void
     * @return array
     */
    function load_modification_structure()
    {
        $query = "SELECT * FROM " . DB_PREFIX . "_modification order by `name` ";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret['modification'][$ar['modification_id']] = $ar;
                $ret['childs'][$ar['model_id']][] = $ar['modification_id'];
            }
        }
        return $ret;
    }

    /**
     * Get mark select box
     * @param int $current_mark_id mark ID
     * @param mixed $ajax_function ajax function
     * @return string
     */
    function getMarkSelectBox($current_mark_id, $ajax_function = false)
    {
        //echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        $mark_structure = $this->load_mark_structure();
        //echo '<pre>';
        //print_r($category_structure);
        $level = 1;
        $rs = '';
        $rs .= '<div id="mark_id_div">';
        if ($ajax_function) {
            $rs .= '<select name="mark_id" id="mark_id" onchange="' . $ajax_function . '">';
        } else {
            $rs .= '<select name="mark_id" id="mark_id">';
        }
        $rs .= '<option value="0">..</option>';
        foreach ($category_structure['childs'][0] as $item_id => $categoryID) {
            //echo $categoryID.'<br>';
            //echo 'items = '.$items.'<br>';
            if ($current_category_id == $categoryID) {
                $selected = " selected ";
            } else {
                $selected = "";
            }

            $rs .= '<option disabled>' . str_repeat(' . ', $level) . $category_structure['catalog'][$categoryID]['name'] . '</option>';
            $rs .= $this->get_mark_option_items($categoryID, $mark_structure, $level, $current_mark_id);
            $rs .= $this->getChildNodes($categoryID, $category_structure, $level + 1, $current_category_id);
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }

    /**
     * Get flat mark select box
     * @param int $categoryID category ID
     * @param int $current_mark_id selected mark_id
     * @return string
     */
    function get_flat_mark_select_box($categoryID, $current_mark_id)
    {
        $mark_structure = $this->load_mark_structure();
        $rs = '';
        $rs .= '<div id="mark_id_div">';
        $rs .= '<select name="mark_id" id="mark_id" onchange="update_model_list()">';
        $rs .= '<option value="0">' . Multilanguage::_('L_CHOOSE_MARK') . '</option>';
        if (is_array($mark_structure['childs'][$categoryID])) {
            foreach ($mark_structure['childs'][$categoryID] as $mark_id) {
                if ($current_mark_id == $mark_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
                $rs .= '<option value="' . $mark_id . '" ' . $selected . '>' . $mark_structure['mark'][$mark_id]['name'] . '</option>';
            }
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }

    /**
     * Get flat coachwork select box
     * @param int $categoryID category ID
     * @param int $current_coachwork_id selected coachwork_id
     * @return string
     */
    function get_flat_coachwork_select_box($categoryID, $current_coachwork_id)
    {
        $coachwork_structure = $this->load_coachwork_structure();
        $rs = '';
        $rs .= '<div id="coachwork_id_div">';
        $rs .= '<select name="coachwork_id" id="coachwork_id">';
        $rs .= '<option value="0">' . Multilanguage::_('L_CHOOSE_BODYTYPE') . '</option>';
        if (is_array($coachwork_structure['childs'][$categoryID])) {
            foreach ($coachwork_structure['childs'][$categoryID] as $coachwork_id) {
                if ($current_coachwork_id == $coachwork_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
                $rs .= '<option value="' . $coachwork_id . '" ' . $selected . '>' . $coachwork_structure['coachwork'][$coachwork_id]['name'] . '</option>';
            }
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }

    /**
     * Get flat model select box
     * @param int $mark_id mark ID
     * @param int $current_model_id selected model_id
     * @return string
     */
    function get_flat_model_select_box($mark_id, $current_model_id)
    {
        $model_structure = $this->load_model_structure();
        $rs = '';
        $rs .= '<div id="model_id_div">';
        $rs .= '<select name="model_id" id="model_id" onchange="update_modification_list()">';
        $rs .= '<option value="0">' . Multilanguage::_('L_CHOOSE_MODEL') . '</option>';
        if (is_array($model_structure['childs'][$mark_id])) {
            foreach ($model_structure['childs'][$mark_id] as $model_id) {
                if ($current_model_id == $model_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
                $rs .= '<option value="' . $model_id . '" ' . $selected . '>' . $model_structure['model'][$model_id]['name'] . '</option>';
                //$rs .= '<option value="'.$model_id.'" '.$selected.'>'.str_repeat(' _ ', $level+1).$model_id.'</option>';
                //$rs .= $this->get_model_option_items( $model_structure, $current_model_id );
            }
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }

    /**
     * Get flat modification select box
     * @param int $model_id model ID
     * @param int $current_modification_id selected modification_id
     * @return string
     */
    function get_flat_modification_select_box($model_id, $current_modification_id)
    {
        $modification_structure = $this->load_modification_structure();
        $rs = '';
        $rs .= '<div id="modification_id_div">';
        $rs .= '<select name="modification_id" id="modification_id">';
        $rs .= '<option value="0">' . Multilanguage::_('L_CHOOSE_MODIFICATION') . '</option>';
        if (is_array($modification_structure['childs'][$model_id])) {
            foreach ($modification_structure['childs'][$model_id] as $modification_id) {
                if ($current_modification_id == $modification_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
                $rs .= '<option value="' . $modification_id . '" ' . $selected . '>' . $modification_structure['modification'][$modification_id]['name'] . '</option>';
                //$rs .= '<option value="'.$model_id.'" '.$selected.'>'.str_repeat(' _ ', $level+1).$model_id.'</option>';
                //$rs .= $this->get_model_option_items( $model_structure, $current_model_id );
            }
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }

    /**
     * Get mark select box
     * @param int $current_mark_id mark ID
     * @return string
     */
    function getModelSelectBox($current_mark_id)
    {
        //echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        $mark_structure = $this->load_mark_structure();
        $model_structure = $this->load_model_structure();
        //echo '<pre>';
        //print_r($model_structure);
        $level = 1;
        $rs = '';
        $rs .= '<div id="model_id_div">';
        $rs .= '<select name="model_id">';
        $rs .= '<option value="0">..</option>';
        foreach ($category_structure['childs'][0] as $item_id => $categoryID) {
            //echo $categoryID.'<br>';
            //echo 'items = '.$items.'<br>';
            if ($current_category_id == $categoryID) {
                $selected = " selected ";
            } else {
                $selected = "";
            }

            $rs .= '<option disabled>' . str_repeat(' . ', $level) . $category_structure['catalog'][$categoryID]['name'] . '</option>';
            $rs .= $this->get_mark_and_model_option_items($categoryID, $mark_structure, $level, $current_mark_id, $model_structure);
            $rs .= $this->getChildNodes($categoryID, $category_structure, $level + 1, $current_category_id);
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }

    /**
     * Get mark option items
     * @param int $categoryID category ID
     * @param array $mark_structure mark structure
     * @param int $level
     * @param int $current_mark_id selected mark_id
     * @return string
     */
    function get_mark_option_items($categoryID, $mark_structure, $level, $current_mark_id)
    {
        if (is_array($mark_structure['childs'][$categoryID])) {
            foreach ($mark_structure['childs'][$categoryID] as $mark_id) {
                if ($current_mark_id == $mark_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
                $rs .= '<option value="' . $mark_id . '" ' . $selected . '>' . str_repeat(' _ ', $level + 1) . $mark_structure['mark'][$mark_id]['name'] . '</option>';
            }
        }
        return $rs;
    }

    /**
     * Get mark and model option items
     * @param int $categoryID category ID
     * @param array $mark_structure mark structure
     * @param int $level
     * @param int $current_model_id selected model_id
     * @param array $model_structure
     * @return string
     */
    function get_mark_and_model_option_items($categoryID, $mark_structure, $level, $current_model_id, $model_structure)
    {
        if (is_array($mark_structure['childs'][$categoryID])) {
            foreach ($mark_structure['childs'][$categoryID] as $mark_id) {
                if ($current_mark_id == $mark_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
                $rs .= '<option disabled>' . str_repeat(' _ ', $level + 1) . $mark_structure['mark'][$mark_id]['name'] . '</option>';
                $rs .= $this->get_model_option_items($model_structure, $current_model_id, $mark_id, $level);
            }
        }
        return $rs;
    }

    /**
     * Get model option items
     * @param array $model_structure model structure
     * @param int $current_model_id current model id
     * @param int $mark_id
     * @param int $level level
     * @return string
     */
    function get_model_option_items($model_structure, $current_model_id, $mark_id, $level)
    {
        if (is_array($model_structure['childs'][$mark_id])) {
            foreach ($model_structure['childs'][$mark_id] as $model_id) {
                if ($current_model_id == $model_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
                $rs .= '<option value="' . $model_id . '" ' . $selected . '>' . str_repeat(' * ', $level + 2) . $model_structure['model'][$model_id]['name'] . '</option>';
                //$rs .= '<option value="'.$model_id.'" '.$selected.'>'.str_repeat(' _ ', $level+1).$model_id.'</option>';
                //$rs .= $this->get_model_option_items( $model_structure, $current_model_id );
            }
        }
        return $rs;
    }

    function getCategoryTreeModern($current_category_id)
    {
        global $smarty;
        $smarty->assign('structure_grid_allow_drag', 1);
        $smarty->assign('use_topic_publish_status', intval($this->getConfigValue('use_topic_publish_status')));
        $ret = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/system/template/structure_grid.tpl');
        return $ret;
    }

    /**
     * Get category tree
     * @param int $current_category_id category ID
     * @return string
     */
    function getCategoryTree($current_category_id)
    {
        //echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        $level = 0;
        $rs = '';
        $rs .= '<form method="post">';
        $rs .= '<table border="0"  class="table table-hover">';
        $rs .= '<tr>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '<td class="row_title"><input type="submit" value="' . Multilanguage::_('RESORT_ITEMS', 'system') . '" name="submit" class="btn btn-info"/></td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td class="row_title">' . Multilanguage::_('L_TEXT_TITLE') . '</td>';
        $rs .= '<td class="row_title">' . Multilanguage::_('URL_NAME', 'system') . '</td>';
        $rs .= '<td class="row_title">' . Multilanguage::_('SORT_ORDER', 'system') . '</td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '</tr>';
        if (count($category_structure) > 0) {
            foreach ($category_structure['childs'][0] as $item_id => $catalog_id) {
                //echo $catalog_id.'<br>';
                $rs .= $this->get_row($catalog_id, $category_structure, $level, 'row1');
                $rs .= $this->getChildNodesRow($catalog_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        $rs .= '<tr>';
        $rs .= '<input type="hidden" name="action" value="structure" />';
        $rs .= '<input type="hidden" name="do" value="reorder_topics" />';
        $rs .= '<td class="row_title"></td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '<td class="row_title"><input type="submit" value="' . Multilanguage::_('RESORT_ITEMS', 'system') . '" name="submit" class="btn btn-info"/></td>';
        $rs .= '<td class="row_title"></td>';

        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '</form>';
        return $rs;
    }

    /**
     * Get category tree control
     * @param int $current_category_id category ID
     * @param int $user_id
     * @param boolean $control
     * @param array $params
     * @return string
     */
    function get_category_tree_control($current_category_id, $user_id, $control = false, $params = array(), $search_params = array())
    {
        // @todo: $user_id нужно добавить проверку на массив в этом значении и генерировать контрол в соответствии с массивом
        // user_id

        $category_structure = $this->loadCategoryStructure();
        $data_structure = $this->load_data_structure($user_id, $params, $search_params);
        //print_r($data_structure);
        if (is_array($category_structure['catalog']) && count($category_structure['catalog']) > 0) {
            foreach ($category_structure['catalog'] as $cat_point) {
                $ch = 0;
                $this->getChildsItemsCount($cat_point['id'], $category_structure['childs'], $data_structure['data'][$user_id], $ch);
                if (!isset($data_structure['data'][$user_id][$cat_point['id']])) {
                    $data_structure['data'][$user_id][$cat_point['id']] = 0;
                }
                $data_structure['data'][$user_id][$cat_point['id']] += $ch;
            }
        }
        unset($params['active']);
        unset($params['hot']);

        $level = 0;
        $rs = '';
        $rs .= '<table border="0" width="100%" class="table table-hover">';
        if (is_array($category_structure['childs'][0]) && count($category_structure['childs'][0]) > 0) {
            foreach ($category_structure['childs'][0] as $item_id => $catalog_id) {
                //echo $catalog_id.'<br>';
                $rs .= $this->get_row_control($catalog_id, $category_structure, $level, 'row1', $user_id, $control, $data_structure, $current_category_id, $params);
                $rs .= $this->get_child_nodes_row_control($catalog_id, $category_structure, $level + 1, $current_category_id, $user_id, $control, $data_structure, $params);
            }
        }
        $rs .= '</table>';
        return $rs;
    }

    /**
     * Get category tree control for shop
     * @param int $current_category_id category ID
     * @param int $user_id
     * @param boolean $control
     * @param array $params
     * @return string
     */
    function get_category_tree_control_shop($current_category_id, $user_id, $control = false, $params = array())
    {
        //print_r($params);
        //echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        $data_structure = $this->load_data_structure_shop($user_id, $params);
        //echo '<pre>';
        //print_r($data_structure);
        //print_r($category_structure);

        foreach ($category_structure['catalog'] as $cat_point) {
            $ch = 0;
            $this->getChildsItemsCount($cat_point['id'], $category_structure['childs'], $data_structure['data'][$user_id], $ch);

            $data_structure['data'][$user_id][$cat_point['id']] += $ch;
        }


        $level = 0;
        $rs = '';
        $rs .= '<table border="0">';
        $rs .= '<tr>';
        $rs .= '<td class="row_title">' . Multilanguage::_('L_TEXT_TITLE') . '</td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '</tr>';
        foreach ($category_structure['childs'][0] as $item_id => $catalog_id) {
            //echo $catalog_id.'<br>';
            $rs .= $this->get_row_control($catalog_id, $category_structure, $level, 'row1', $user_id, $control, $data_structure, $current_category_id, $params);
            $rs .= $this->get_child_nodes_row_control($catalog_id, $category_structure, $level + 1, $current_category_id, $user_id, $control, $data_structure, $params);
        }
        $rs .= '</table>';
        return $rs;
    }

    /**
     * Get category tree control for price
     * @param int $current_category_id category ID
     * @param int $user_id
     * @param boolean $control
     * @param array $params
     * @return string
     */
    function get_category_tree_control_price($current_category_id, $user_id, $control = false, $params = array())
    {
        //print_r($params);
        //echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        $data_structure = $this->load_data_structure_price($user_id, $params);
        //echo '<pre>';
        //print_r($data_structure);
        //print_r($category_structure);

        foreach ($category_structure['catalog'] as $cat_point) {
            $ch = 0;
            $this->getChildsItemsCount($cat_point['id'], $category_structure['childs'], $data_structure['data'][$user_id], $ch);

            $data_structure['data'][$user_id][$cat_point['id']] += $ch;
        }


        $level = 0;
        $rs = '';
        $rs .= '<table border="0">';
        $rs .= '<tr>';
        $rs .= '<td class="row_title">' . Multilanguage::_('L_TEXT_TITLE') . '</td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '</tr>';
        foreach ($category_structure['childs'][0] as $item_id => $catalog_id) {
            //echo $catalog_id.'<br>';
            $rs .= $this->get_row_control($catalog_id, $category_structure, $level, 'row1', $user_id, $control, $data_structure, $current_category_id, $params);
            $rs .= $this->get_child_nodes_row_control($catalog_id, $category_structure, $level + 1, $current_category_id, $user_id, $control, $data_structure, $params);
        }
        $rs .= '</table>';
        return $rs;
    }

    //function getOwnItemsCount($id,$data_structure){
    //	return $data_structure['data'][$user_id][$id];
    //}

    function getChildsItemsCount($id, $category_structure_childs, $data_structure, &$ret)
    {
        //echo '1Call with id='.$id.' <br />';
        if (isset($category_structure_childs[$id]) && count($category_structure_childs[$id]) > 0) {
            foreach ($category_structure_childs[$id] as $v) {
                //echo '2$v='.$v.' <br />';
                //echo '2a$count='.$data_structure[$v].' <br />';
                if (isset($data_structure[$v])) {
                    $ret += $data_structure[$v];
                }

                $this->getChildsItemsCount($v, $category_structure_childs, $data_structure, $ret);
            }
        }
        //echo '3$ret='.$ret.' <br />';
        //return $data_structure['data'][$user_id][$id];
    }

    /**
     * Get row
     * @param int $categoryID
     * @param array $category_structure
     * @param int $level
     * @param string $row_class
     */
    function get_row($categoryID, $category_structure, $level, $row_class)
    {
        $rs .= '<tr>';
        $rs .= '<td class="' . $row_class . '">' . str_repeat('&nbsp;.&nbsp;', $level) . $category_structure['catalog'][$categoryID]['name'] . '</td>';
        if ($category_structure['catalog'][$categoryID]['url'] == '') {
            $rs .= '<td class="' . $row_class . '">' . 'topic' . $categoryID . '.html</td>';
        } else {
            $rs .= '<td class="' . $row_class . '">' . $category_structure['catalog'][$categoryID]['url'] . '</td>';
        }
        $edit_icon = ' <a href="?action=structure&do=edit&id=' . $categoryID . '" class="btn btn-info"><i class="icon-white icon-pencil"></i></a> ';
        $delete_icon = ' <a href="?action=structure&do=delete&id=' . $categoryID . '" onclick="if ( confirm(\'' . Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE') . '\') ) {return true;} else {return false;}" class="btn btn-danger"><i class="icon-white icon-remove"></i></a> ';

        //$rs .= '<td class="'.$row_class.'">'.$this->get_operation_type_name_by_id($category_structure['catalog'][$categoryID]['operation_type_id']).'</td>';
        $rs .= '<td class="' . $row_class . '"><input type="text" size="4" name="order[' . $categoryID . ']" value="' . $category_structure['catalog'][$categoryID]['order'] . '"/></td>';
        $rs .= '<td class="' . $row_class . '">' . $edit_icon . $delete_icon . '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    /**
     * Get row control
     * @param int $categoryID
     * @param array $category_structure
     * @param int $level
     * @param string $row_class
     * @param int $user_id
     * @param boolean $control
     * @param array $data_structure
     * @param int $current_category_id
     * @param array $params
     * @return string
     */
    function get_row_control($categoryID, $category_structure, $level, $row_class, $user_id, $control = false, $data_structure, $current_category_id, $params = array())
    {
        //echo '<pre>';
        //print_r($params);
        //echo '</pre>';
        $rs = '';

        if (((int)$this->getConfigValue('hide_empty_catalog') != 0) and ((int)$data_structure['data'][$user_id][$categoryID] == 0)) {
            return '';
        }


        if (count($params) > 0) {
            $add_url = '&' . implode('&', $params);
        }

        //echo "add_url = ".$add_url;
        $rs .= '<tr>';
        if ($categoryID == $current_category_id) {
            $row_class = 'active';
        }
        $subclass = '';
        if ($category_structure['catalog'][$categoryID]['parent_id'] == 0) {
            $subclass = 'maincat';
        }
        $rs .= '<td class="' . $row_class . ' ' . $subclass . '"><a href="?topic_id=' . $categoryID . '' . $add_url . '">' . str_repeat('&nbsp;.&nbsp;', $level) . $category_structure['catalog'][$categoryID]['name'] . '</a> (' . (int)$data_structure['data'][$user_id][$categoryID] . ') <small>id:' . $categoryID . '</small></td>';

        if ($control) {
            $edit_icon = '<a href="?action=structure&do=edit&id=' . $categoryID . '"><img src="' . SITEBILL_MAIN_URL . '/img/edit.gif" border="0" width="16" height="16" alt="редактировать" title="редактировать"></a>';
            $delete_icon = '<a href="?action=structure&do=delete&id=' . $categoryID . '" onclick="if ( confirm(\'' . Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE') . '\') ) {return true;} else {return false;}"><img src="' . SITEBILL_MAIN_URL . '/img/delete.gif" border="0" width="16" height="16" alt="удалить" title="удалить"></a>';
        }

        //$rs .= '<td class="'.$row_class.'">'.$this->get_operation_type_name_by_id($category_structure['catalog'][$categoryID]['operation_type_id']).'</td>';
        if ($control) {
            $rs .= '<td class="' . $row_class . '">' . $edit_icon . $delete_icon . '</td>';
        }
        $rs .= '</tr>';


        return $rs;
    }

    /**
     * Get child nodes
     * @param $categoryID
     * @param $category_structure
     * @param $level
     * @param $current_category_id
     */
    function getChildNodesRow($categoryID, $category_structure, $level, $current_category_id)
    {
        if (!is_array($category_structure['childs'][$categoryID])) {
            return '';
        }
        foreach ($category_structure['childs'][$categoryID] as $child_id) {
            if ($current_category_id == $child_id) {
                $selected = " selected ";
            } else {
                $selected = "";
            }
            $this->j++;
            if (ceil($this->j / 2) > floor($this->j / 2)) {
                $row_class = "row1";
            } else {
                $this->j = 0;
                $row_class = "row2";
            }

            //print_r($category_structure['catalog'][$child_id]);
            $rs .= '<tr>';
            $rs .= '<td class="' . $row_class . '">' . str_repeat('&nbsp;.&nbsp;', $level) . $category_structure['catalog'][$child_id]['name'] . '</td>';

            if ($category_structure['catalog'][$child_id]['url'] == '') {
                $rs .= '<td class="' . $row_class . '">' . 'topic' . $category_structure['catalog'][$child_id]['id'] . '.html</td>';
            } else {
                $rs .= '<td class="' . $row_class . '">' . $category_structure['catalog'][$child_id]['url'] . '</td>';
            }


            $edit_icon = ' <a href="?action=structure&do=edit&id=' . $child_id . '" class="btn btn-info"><i class="icon-white icon-pencil"></i></a> ';
            $delete_icon = ' <a href="?action=structure&do=delete&id=' . $child_id . '" onclick="if ( confirm(\'' . Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE') . '\') ) {return true;} else {return false;}"  class="btn btn-danger"><i class="icon-white icon-remove"></i></a> ';

            //$rs .= '<td class="'.$row_class.'">'.$this->get_operation_type_name_by_id($category_structure['catalog'][$child_id]['operation_type_id']).'</td>';
            $rs .= '<td class="' . $row_class . '"><input type="text" size="5" name="order[' . $child_id . ']" value="' . $category_structure['catalog'][$child_id]['order'] . '"/></td>';
            $rs .= '<td class="' . $row_class . '">' . $edit_icon . $delete_icon . '</td>';
            $rs .= '</tr>';
            //$rs .= '<option value="'.$child_id.'" '.$selected.'>'.str_repeat(' . ', $level).$category_structure['catalog'][$child_id]['name'].'</option>';
            //print_r($category_structure['childs'][$child_id]);
            if (count($category_structure['childs'][$child_id]) > 0) {
                $rs .= $this->getChildNodesRow($child_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        return $rs;
    }

    /**
     * Get child nodes control
     * @param $categoryID
     * @param $category_structure
     * @param $level
     * @param $current_category_id
     */
    function get_child_nodes_row_control($categoryID, $category_structure, $level, $current_category_id, $user_id, $control = false, $data_structure, $params = array())
    {
        $rs = '';
        if (!isset($category_structure['childs'][$categoryID]) || !is_array($category_structure['childs'][$categoryID])) {
            return '';
        }


        if (count($params) > 0) {
            $add_url = '&' . implode('&', $params);
        }

        foreach ($category_structure['childs'][$categoryID] as $child_id) {


            if ((0 != $this->getConfigValue('hide_empty_catalog')) and (0 == $data_structure['data'][$user_id][$child_id])) {
                $rs .= '';
            } else {

                if ($current_category_id == $child_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
                $this->j++;
                if (ceil($this->j / 2) > floor($this->j / 2)) {
                    $row_class = "row1";
                } else {
                    $this->j = 0;
                    $row_class = "row2";
                }

                //print_r($category_structure['catalog'][$child_id]);
                //print_r($data_structure['data'][$user_id]);
                //echo "category_id = $child_id, count = ".$data_structure['data'][$user_id][$child_id].'<br>';
                $rs .= '<tr>';

                if ($child_id == $current_category_id) {
                    $row_class = 'active';
                }
                $rs .= '<td class="' . $row_class . '"><a href="?topic_id=' . $child_id . '' . $add_url . '">' . str_repeat('&nbsp;.&nbsp;', $level) . $category_structure['catalog'][$child_id]['name'] . '</a> (' . (int)$data_structure['data'][$user_id][$child_id] . ')' . ' <small>id:' . $child_id . '</small></td>';
                if ($control) {
                    $edit_icon = '<a href="?action=structure&do=edit&id=' . $child_id . '"><img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/edit.png" border="0"  alt="редактировать" title="редактировать"></a>';
                    $delete_icon = '<a href="?action=structure&do=delete&id=' . $child_id . '" onclick="if ( confirm(\'' . Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE') . '\') ) {return true;} else {return false;}"><img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/delete.png" border="0" width="16" height="16" alt="удалить" title="удалить"></a>';
                }

                //$rs .= '<td class="'.$row_class.'">'.$this->get_operation_type_name_by_id($category_structure['catalog'][$child_id]['operation_type_id']).'</td>';
                if ($control) {
                    $rs .= '<td class="' . $row_class . '">' . $edit_icon . $delete_icon . '</td>';
                }

                $rs .= '</tr>';
                //$rs .= '<option value="'.$child_id.'" '.$selected.'>'.str_repeat(' . ', $level).$category_structure['catalog'][$child_id]['name'].'</option>';
                //print_r($category_structure['childs'][$child_id]);
                if (isset($category_structure['childs'][$child_id]) && count($category_structure['childs'][$child_id]) > 0) {
                    $rs .= $this->get_child_nodes_row_control($child_id, $category_structure, $level + 1, $current_category_id, $user_id, $control, $data_structure, $params);
                }
            }
        }
        return $rs;
    }

    /**
     * Load operation type list
     * @param void
     * @return array
     */
    /* function load_operation_type_list () {
      $query = "SELECT * FROM ".DB_PREFIX."_operation_type";
      $DBC=DBC::getInstance();
      $stmt=$DBC->query($query);
      $ret=array();
      if($stmt){
      while ( $ar=$DBC->fetch($stmt) ) {
      $ret[$ar['operation_type_id']]= $ar;
      }
      }
      return $ret;
      } */

    /**
     * Get child nodes
     * @param $categoryID
     * @param $category_structure
     * @param $level
     * @param $current_category_id
     */
    function getChildNodes($categoryID, $category_structure, $level, $current_category_id, $superparent = 0)
    {
        $level_symbol = $this->getConfigValue('level_symbol');
        $level_symbol = str_replace('#', ' ', $level_symbol);

        $core_level_symbol = $this->getConfigValue('core_level_symbol');
        $core_level_symbol = str_replace('#', ' ', $core_level_symbol);

        $rs = '';
        if (!isset($category_structure['childs'][$categoryID]) || !is_array($category_structure['childs'][$categoryID])) {
            return '';
        }
        $multiple = false;
        if (is_array($current_category_id)) {
            $multiple = true;
        }
        foreach ($category_structure['childs'][$categoryID] as $child_id) {
            if ($multiple) {
                if (in_array($child_id, $current_category_id)) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
            } else {
                if ($current_category_id == $child_id) {
                    $selected = " selected ";
                } else {
                    $selected = "";
                }
            }

            if (($this->getConfigValue('disable_root_structure_select') == 2 || $this->getConfigValue('disable_root_structure_select') == 3) && isset($category_structure['childs'][$child_id]) && is_array($category_structure['childs'][$child_id]) && $_SESSION['allow_disable_root_structure_select'] === true) {
                $disabled = ' disabled="disabled" style="background-color:#eee;"';
            } else {
                $disabled = '';
            }

            if ($core_level_symbol == '') {
                $offset_level = $level - 1;
            } else {
                $offset_level = $level;
            }
            $rs .= '<option class="rootlevel rootlevel_' . $level . '" data-superparent="' . $superparent . '" data-parent="' . $categoryID . '" data-level="' . $level . '" value="' . $child_id . '" data-value="' . $category_structure['catalog'][$child_id]['name'] . '" ' . $selected . $disabled . '>' . str_repeat($level_symbol, $offset_level) . $category_structure['catalog'][$child_id]['name'] . '</option>';
            if (isset($category_structure['childs'][$child_id])) {
                if (count($category_structure['childs'][$child_id]) > 0) {
                    $rs .= $this->getChildNodes($child_id, $category_structure, $level + 1, $current_category_id, $superparent);
                }
            }
        }
        return $rs;


        ////////////////////////////////////////////
        if (!is_array($category_structure['childs'][$categoryID])) {
            return '';
        }
        foreach ($category_structure['childs'][$categoryID] as $child_id) {
            if ($current_category_id == $child_id) {
                $selected = " selected ";
            } else {
                $selected = "";
            }
            //print_r($category_structure['catalog'][$child_id]);
            $rs .= '<option value="' . $child_id . '" ' . $selected . '>' . str_repeat(' . ', $level) . $category_structure['catalog'][$child_id]['name'] . '</option>';
            //print_r($category_structure['childs'][$child_id]);
            if (count($category_structure['childs'][$child_id]) > 0) {
                $rs .= $this->getChildNodes($child_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        return $rs;
    }

    function getShopChildNodes($categoryID, $category_structure, $level, $current_category_id)
    {
        if (!is_array($category_structure['childs'][$categoryID])) {
            return '';
        }
        foreach ($category_structure['childs'][$categoryID] as $child_id) {
            if ($current_category_id == $child_id) {
                $selected = " selected ";
            } else {
                $selected = "";
            }
            $rs .= '<option value="' . $child_id . '" ' . $selected . '>' . str_repeat(' . ', $level) . $category_structure['catalog'][$child_id]['category_name'] . '</option>';

            if (count($category_structure['childs'][$child_id]) > 0) {
                $rs .= $this->getShopChildNodes($child_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        return $rs;


        ////////////////////////////////////////////
        if (!is_array($category_structure['childs'][$categoryID])) {
            return '';
        }
        foreach ($category_structure['childs'][$categoryID] as $child_id) {
            if ($current_category_id == $child_id) {
                $selected = " selected ";
            } else {
                $selected = "";
            }
            //print_r($category_structure['catalog'][$child_id]);
            $rs .= '<option value="' . $child_id . '" ' . $selected . '>' . str_repeat(' . ', $level) . $category_structure['catalog'][$child_id]['category_name'] . '</option>';
            //print_r($category_structure['childs'][$child_id]);
            if (count($category_structure['childs'][$child_id]) > 0) {
                $rs .= $this->getShopChildNodes($child_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        return $rs;
    }

    /**
     * Get service type select box
     * @param int $current_servicetype_id service type ID
     * @return string
     * @author Kris
     */
    function getServiceTypesTree_selectBox($select_name, $current_servicetype_id)
    {
        //echo '$current_category_id = '.$current_category_id;
        $service_type_array = $this->getServiceTypeTree_array(0, 0);
        $level = 1;
        $rs = '';
        $rs .= '<div id="parent_id_div">';
        $rs .= '<select name="' . $select_name . '">';
        $rs .= '<option value="0">..</option>';

        $rs .= $this->getServiceTypesTree_optionItems($service_type_array, $current_servicetype_id);
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }

    /**
     * Get service type 'option' items for select box
     * @param int $current_servicetype_id - current service type ID
     * @param array $array - items
     * @return string
     * @author Kris
     */
    function getServiceTypesTree_optionItems($array, $current_servicetype_id)
    {
        if (count($array) == 0)
            return '';
        foreach ($array as $value) {

            $level_lines = '';
            for ($i = 0; $i < $value['level']; $i++)
                $level_lines .= ' - ';
            if ($current_servicetype_id == $value['id']) {
                $selected = " selected ";
            } else {
                $selected = "";
            }
            $rs .= '<option value="' . $value['id'] . '" ' . $selected . '>' . str_repeat(' . ', $value['level']) . $value['name'] . '</option>';
            $rs .= $this->getServiceTypesTree_optionItems($value['child'], $current_servicetype_id);
        }
        return $rs;
    }

    /**
     * Get service type tree in array with field
     * 'level', which specify the nesting level, and
     * 'child' containing child array with the same structure as parent
     * @param int $current_servicetype_id service type ID
     * @return array
     * @author Kris
     */
    function getServiceTypeTree_array($level, $parent_id)
    {
        global $_SESSION;
        $query = "select st1.* from " . DB_PREFIX . "_service_type as st1 where st1.parent_id = " . $parent_id . "";
        $arr = array();

        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if ($stmt) {
            $j = 0;
            while ($ar = $DBC->fetch($stmt)) {
                $arr[$j]['name'] = $ar['name'];
                $arr[$j]['id'] = $ar['service_type_id'];
                $arr[$j]['level'] = $level;
                $j++;
            }
        }

        if (!empty($arr)) {
            foreach ($arr as $key => $value) {
                $arr[$key]['child'] = $this->getServiceTypeTree_array($arr[$key]['level'] + 1, $arr[$key]['id']);
            }
        }
        return $arr;
    }

    /**
     * Grid
     * @param void
     * @return string
     */
    function grid()
    {
        return $this->getCategoryTreeModern(0);
    }

    function findCurrent(&$structure, $active)
    {
        foreach ($structure['childs'] as $k => $v) {
            foreach ($v as $vv) {
                if ($vv == $active) {
                    $structure['catalog'][$vv]['current'] = 1;
                    if ($k != 0) {
                        $structure['catalog'][$k]['current'] = 1;
                        $this->findCurrent($structure, $k);
                        return;
                    }
                }
            }
        }
    }

    private function getRealtyTypeSelectbox($realty_type, $topic_id)
    {
        $re_types = array('0' => 'Игнорировать', '1' => 'Жилая', '4' => 'Коммерческая', '6' => 'Нежилая');
        $ret = '';
        $ret .= '<select name="data[' . $topic_id . '][obj_type_id]">';
        foreach ($re_types as $k => $v) {
            if ($realty_type == $k) {
                $ret .= '<option value="' . $k . '" selected="selected">' . $v . '</option>';
            } else {
                $ret .= '<option value="' . $k . '">' . $v . '</option>';
            }
        }
        $ret .= '</select>';
        return $ret;
    }

    private function getOperationTypeSelectbox($operation_type, $topic_id)
    {
        $op_types = array('0' => 'Игнорировать', '1' => 'Продажа', '2' => 'Аренда');
        $ret = '';
        $ret .= '<select name="data[' . $topic_id . '][operation_type]">';
        foreach ($op_types as $k => $v) {
            if ($operation_type == $k) {
                $ret .= '<option value="' . $k . '" selected="selected">' . $v . '</option>';
            } else {
                $ret .= '<option value="' . $k . '">' . $v . '</option>';
            }
        }
        $ret .= '</select>';
        return $ret;
    }

    function getCategoryTreeAssoc()
    {
        //echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        //echo '<pre>';
        //print_r($category_structure);
        $level = 0;
        $rs = '';
        $rs .= '<form method="post">';
        $rs .= '<table border="0">';
        $rs .= '<tr>';
        $rs .= '<td class="row_title" colspan="4"><input type="submit" value="' . Multilanguage::_('L_TEXT_SAVE') . '" name="submit" /></td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td class="row_title">' . Multilanguage::_('L_TEXT_TITLE') . '</td>';
        $rs .= '<td class="row_title">' . Multilanguage::_('OPERATION_TYPE', 'system') . '</td>';
        $rs .= '<td class="row_title">' . Multilanguage::_('ESTATE_TYPE', 'system') . '</td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '</tr>';
        if (count($category_structure) > 0) {
            foreach ($category_structure['childs'][0] as $item_id => $catalog_id) {
                //echo $catalog_id.'<br>';
                $rs .= $this->getRowAssoc($catalog_id, $category_structure, $level, 'row1');
                $rs .= $this->getChildNodesRowAssoc($catalog_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        $rs .= '<tr>';
        $rs .= '<input type="hidden" name="action" value="structure" />';
        $rs .= '<input type="hidden" name="do" value="associations" />';
        $rs .= '<td class="row_title" colspan="4"><input type="submit" value="' . Multilanguage::_('L_TEXT_SAVE') . '" name="submit" /></td>';

        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '</form>';
        return $rs;
    }

    function saveLinkerAssociations($rules)
    {
        foreach ($_POST['topic'] as $topic_id => $link_topic_id) {
            $this->update_topic_links($topic_id, $link_topic_id, $_POST['params_topic'][$topic_id]);
        }
    }

    function update_topic_links($topic_id, $link_topic_id, $params)
    {
        $DBC = DBC::getInstance();
        //echo "topic_id = $topic_id, link_topic_id = $link_topic_id, params = $params<br>";
        $query = 'SELECT id FROM ' . DB_PREFIX . '_topic_links WHERE topic_id=?';
        $stmt = $DBC->query($query, array($topic_id), $row, $success);
        //echo $DBC->getLastError();

        if ($stmt) {
            //echo 'exist<br>';
            $ar = $DBC->fetch($stmt);
            if ($ar['id'] > 0) {
                $query = 'update ' . DB_PREFIX . '_topic_links SET topic_id=?, link_topic_id=?, params=? where topic_id=?';
                $stmt = $DBC->query($query, array($topic_id, $link_topic_id, $params, $topic_id), $row, $success);
                if (!$success) {
                    echo $DBC->getLastError();
                }
            }
        } else {
            //echo $DBC->getLastError().'<br>';
            //echo 'not exist<br>';

            $query = 'insert into ' . DB_PREFIX . '_topic_links (topic_id, link_topic_id, params) values (?, ?, ?)';
            $stmt = $DBC->query($query, array($topic_id, $link_topic_id, $params));
        }
        return true;
    }

    function load_topic_links()
    {
        $DBC = DBC::getInstance();
        $ra = array();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_topic_links';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ra[$ar['topic_id']]['link_topic_id'] = $ar['link_topic_id'];
                $ra[$ar['topic_id']]['params'] = $ar['params'];
            }
        }
        return $ra;
    }

    function getCategoryTreeLinker()
    {
        $topic_links_hash = $this->load_topic_links();
        //echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        $data_structure = $this->load_data_structure(0);

        //echo '<pre>';
        //print_r($category_structure);
        $level = 0;
        $rs = '';
        $rs .= '<form method="post">';
        $rs .= '<table class="table table-striped table-bordered table-hover">';
        $rs .= '<tr>';
        $rs .= '<td class="row_title" colspan="4"><input type="submit" value="' . Multilanguage::_('L_TEXT_SAVE') . '" name="submit" /></td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td class="row_title" width="20%">' . Multilanguage::_('L_TEXT_TITLE') . '</td>';
        $rs .= '<td class="row_title" width="10%">' . Multilanguage::_('OPERATION_TYPE', 'system') . '</td>';
        $rs .= '<td class="row_title" width="80%">' . Multilanguage::_('PARAMS', 'system') . '</td>';
        $rs .= '</tr>';
        if (count($category_structure) > 0) {
            foreach ($category_structure['childs'][0] as $item_id => $catalog_id) {
                //echo $catalog_id.'<br>';
                //$rs .= $this->getRowAssoc($catalog_id, $category_structure, $level, 'row1');
                $rs .= $this->getRowLinker($catalog_id, $category_structure, $level, 'row1', $topic_links_hash, $data_structure);
                $rs .= $this->getChildNodesRowLinker($catalog_id, $category_structure, $level + 1, $current_category_id, $topic_links_hash, $data_structure);
            }
        }
        $rs .= '<tr>';
        $rs .= '<input type="hidden" name="action" value="structure" />';
        $rs .= '<input type="hidden" name="do" value="linker" />';
        $rs .= '<td class="row_title" colspan="4"><input type="submit" value="' . Multilanguage::_('L_TEXT_SAVE') . '" name="submit" /></td>';

        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '</form>';
        return $rs;
    }

    function getRowAssoc($categoryID, $category_structure, $level, $row_class)
    {
        $rs .= '<tr>';
        $rs .= '<td class="' . $row_class . '">' . str_repeat('&nbsp;.&nbsp;', $level) . $category_structure['catalog'][$categoryID]['name'] . '</td>';

        $rs .= '<td class="' . $row_class . '">' . $this->getOperationTypeSelectbox($category_structure['catalog'][$categoryID]['operation_type_id'], $categoryID) . '</td>';
        $rs .= '<td class="' . $row_class . '">' . $this->getRealtyTypeSelectbox($category_structure['catalog'][$categoryID]['obj_type_id'], $categoryID) . '</td>';

        $rs .= '<td><input type="checkbox" name="data[' . $categoryID . '][legacy]" /> ' . Multilanguage::_('INHERIT', 'system') . '</td>';
        $rs .= '</tr>';

        return $rs;
    }

    function getRowLinker($categoryID, $category_structure, $level, $row_class, $topic_links_hash, $data_structure = false)
    {
        if (isset($data_structure['data'][0][$categoryID])) {
            $has_data = ' warning ';
        } else {
            $has_data = '';
        }

        $rs .= '<tr class="' . $has_data . '">';
        $rs .= '<td class="' . $row_class . '">' . str_repeat('&nbsp;.&nbsp;', $level) . $category_structure['catalog'][$categoryID]['name'] . ' <strong>[' . $categoryID . ']</strong>' . ' d = ' . $data_structure['data'][0][$categoryID] . '</td>';

        $params['ignore_published_status'] = 1;
        $true_categoryID = $categoryID;
        if ($topic_links_hash[$categoryID]['link_topic_id'] != '') {
            $categoryID = $topic_links_hash[$categoryID]['link_topic_id'];
        }

        if ($categoryID != $true_categoryID) {
            $changed_style = 'style="background-color: green;"';
        } else {
            $changed_style = '';
        }

        $rs .= '<td class="' . $row_class . '" ' . $changed_style . '>' . $this->getCategorySelectBoxWithName('topic[' . $true_categoryID . ']', $categoryID, false, $params) . '</td>';
        $rs .= '<td class="' . $row_class . '"><textarea name="params_topic[' . $true_categoryID . ']">' . $topic_links_hash[$true_categoryID]['params'] . '</textarea></td>';

        $rs .= '</tr>';

        return $rs;
    }

    function getChildNodesRowLinker($categoryID, $category_structure, $level, $current_category_id, $topic_links_hash, $data_structure = false)
    {
        if (!is_array($category_structure['childs'][$categoryID])) {
            return '';
        }
        $params['ignore_published_status'] = 1;
        foreach ($category_structure['childs'][$categoryID] as $child_id) {
            if ($category_structure['catalog'][$child_id]['published'] == 1) {
                // Чтобы проще видеть, в линкере отображаем только не опубликованные ветки
                // В рабочей версии закоменчено, пока включать через код, потом выключать
                //continue;
            }
            if ($topic_links_hash[$child_id]['link_topic_id'] != '') {
                $tmp_child_id = $topic_links_hash[$child_id]['link_topic_id'];
            } else {
                $tmp_child_id = $child_id;
            }

            if ($child_id != $tmp_child_id) {
                $changed_style = 'style="background-color: green;"';
            } else {
                $changed_style = '';
            }


            if ($current_category_id == $child_id) {
                $selected = " selected ";
            } else {
                $selected = "";
            }
            $this->j++;
            if (ceil($this->j / 2) > floor($this->j / 2)) {
                $row_class = "row1";
            } else {
                $this->j = 0;
                $row_class = "row2";
            }
            if ($category_structure['catalog'][$child_id]['published'] == 0 and isset($data_structure['data'][0][$child_id]) and $data_structure['data'][0][$child_id] > 100) {
                $has_data = ' error ';
            } elseif ($category_structure['catalog'][$child_id]['published'] == 0 and isset($data_structure['data'][0][$child_id])) {
                $has_data = ' warning ';
            } else {
                $has_data = '';
            }

            $rs .= '<tr class="' . $has_data . '">';
            $rs .= '<td class="' . $row_class . '">' .
                str_repeat('&nbsp;.&nbsp;', $level) . $category_structure['catalog'][$child_id]['name'] .
                ' <strong>[' . $child_id . ']</strong>' .
                ' d = ' . $data_structure['data'][0][$child_id] .
                ' p = ' . $category_structure['catalog'][$child_id]['published'] . '</td>';


            $rs .= '<td class="' . $row_class . '" ' . $changed_style . '>' . $this->getCategorySelectBoxWithName('topic[' . $child_id . ']', $tmp_child_id, false, $params) . '</td>';
            $rs .= '<td class="' . $row_class . '"><textarea type="text" name="params_topic[' . $child_id . ']">' . $topic_links_hash[$child_id]['params'] . '</textarea></td>';


            $rs .= '</tr>';
            if (is_array($category_structure['childs'][$child_id]) and count($category_structure['childs'][$child_id]) > 0) {
                $rs .= $this->getChildNodesRowLinker($child_id, $category_structure, $level + 1, $current_category_id, $topic_links_hash, $data_structure);
            }
        }
        return $rs;
    }

    function getChildNodesRowAssoc($categoryID, $category_structure, $level, $current_category_id)
    {
        if (!is_array($category_structure['childs'][$categoryID])) {
            return '';
        }
        foreach ($category_structure['childs'][$categoryID] as $child_id) {
            if ($current_category_id == $child_id) {
                $selected = " selected ";
            } else {
                $selected = "";
            }
            $this->j++;
            if (ceil($this->j / 2) > floor($this->j / 2)) {
                $row_class = "row1";
            } else {
                $this->j = 0;
                $row_class = "row2";
            }

            $rs .= '<tr>';
            $rs .= '<td class="' . $row_class . '">' . str_repeat('&nbsp;.&nbsp;', $level) . $category_structure['catalog'][$child_id]['name'] . '</td>';

            $rs .= '<td class="' . $row_class . '">' . $this->getOperationTypeSelectbox($category_structure['catalog'][$child_id]['operation_type_id'], $child_id) . '</td>';
            $rs .= '<td class="' . $row_class . '">' . $this->getRealtyTypeSelectbox($category_structure['catalog'][$child_id]['obj_type_id'], $child_id) . '</td>';
            $rs .= '<td><input type="checkbox" name="data[' . $child_id . '][legacy]" /> ' . Multilanguage::_('INHERIT', 'system') . '</td>';

            $rs .= '</tr>';
            if (count($category_structure['childs'][$child_id]) > 0) {
                $rs .= $this->getChildNodesRowAssoc($child_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        return $rs;
    }

    public static function has_child($id)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT COUNT(id) AS cnt FROM ' . DB_PREFIX . '_topic WHERE parent_id=?';
        $stmt = $DBC->query($query, array($id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            return $ar['cnt'] > 0 ? true : false;
        }
        return false;
    }

    protected function getStrModelFromDB()
    {
        $form_data = array();
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
        $ATH = new Admin_Table_Helper();
        $form_data = $ATH->load_model('topic', false);
        if (empty($form_data)) {
            $form_data = array();
        }
        return $form_data;
    }

    public function get_model()
    {
        return $this->getStrModel();
    }

    protected function getStrModel()
    {

        $form_data = $this->getStrModelFromDB();

        if (!empty($form_data)) {
            return $form_data;
        }


        $use_langs = false;
        if (1 == intval($this->getConfigValue('apps.language.use_langs'))) {
            $use_langs = true;
            $languages = Multilanguage::foreignLanguages();
        }


        $form_data['topic']['id']['name'] = 'id';
        $form_data['topic']['id']['title'] = Multilanguage::_('L_ID');
        $form_data['topic']['id']['value'] = 0;
        $form_data['topic']['id']['length'] = 40;
        $form_data['topic']['id']['type'] = 'primary_key';
        $form_data['topic']['id']['required'] = 'off';
        $form_data['topic']['id']['unique'] = 'off';

        $form_data['topic']['parent_id']['name'] = 'parent_id';
        $form_data['topic']['parent_id']['title'] = 'Родительская категория';
        $form_data['topic']['parent_id']['value_string'] = '';
        $form_data['topic']['parent_id']['value'] = 0;
        $form_data['topic']['parent_id']['length'] = 40;
        $form_data['topic']['parent_id']['type'] = 'select_box_structure';
        $form_data['topic']['parent_id']['required'] = 'off';
        $form_data['topic']['parent_id']['unique'] = 'off';

        $form_data['topic']['order']['name'] = 'order';
        $form_data['topic']['order']['title'] = 'Порядок сортировки';
        $form_data['topic']['order']['value'] = '';
        $form_data['topic']['order']['length'] = 40;
        $form_data['topic']['order']['type'] = 'safe_string';
        $form_data['topic']['order']['required'] = 'off';
        $form_data['topic']['order']['unique'] = 'off';

        $form_data['topic']['published']['name'] = 'published';
        $form_data['topic']['published']['title'] = 'Раздел активен';
        $form_data['topic']['published']['value'] = 1;
        $form_data['topic']['published']['length'] = 40;
        $form_data['topic']['published']['type'] = 'checkbox';
        $form_data['topic']['published']['required'] = 'off';
        $form_data['topic']['published']['unique'] = 'off';

        $form_data['topic']['name']['name'] = 'name';
        $form_data['topic']['name']['title'] = 'Название';
        $form_data['topic']['name']['value'] = '';
        $form_data['topic']['name']['length'] = 40;
        $form_data['topic']['name']['type'] = 'safe_string';
        $form_data['topic']['name']['required'] = 'on';
        $form_data['topic']['name']['unique'] = 'off';


        if ($use_langs) {
            foreach ($languages as $language_id => $language_title) {

                $form_data['topic']['name_' . $language_id]['name'] = 'name_' . $language_id;
                $form_data['topic']['name_' . $language_id]['title'] = Multilanguage::_('TOPIC_NAME', 'system') . ' <b>' . $language_id . '</b>';
                $form_data['topic']['name_' . $language_id]['value'] = '';
                $form_data['topic']['name_' . $language_id]['length'] = 40;
                $form_data['topic']['name_' . $language_id]['type'] = 'safe_string';
                $form_data['topic']['name_' . $language_id]['required'] = 'off';
                $form_data['topic']['name_' . $language_id]['unique'] = 'off';
            }
        }


        $form_data['topic']['meta_title']['name'] = 'meta_title';
        $form_data['topic']['meta_title']['title'] = 'meta_title';
        $form_data['topic']['meta_title']['value'] = '';
        $form_data['topic']['meta_title']['length'] = 40;
        $form_data['topic']['meta_title']['type'] = 'safe_string';
        $form_data['topic']['meta_title']['required'] = 'off';
        $form_data['topic']['meta_title']['unique'] = 'off';

        if ($use_langs) {
            foreach ($languages as $language_id => $language_title) {
                $form_data['topic']['meta_title_' . $language_id]['name'] = 'meta_title_' . $language_id;
                $form_data['topic']['meta_title_' . $language_id]['title'] = 'meta_title' . ' <b>' . $language_id . '</b>';
                $form_data['topic']['meta_title_' . $language_id]['value'] = '';
                $form_data['topic']['meta_title_' . $language_id]['length'] = 40;
                $form_data['topic']['meta_title_' . $language_id]['type'] = 'safe_string';
                $form_data['topic']['meta_title_' . $language_id]['required'] = 'off';
                $form_data['topic']['meta_title_' . $language_id]['unique'] = 'off';
            }
        }

        $form_data['topic']['public_title']['name'] = 'public_title';
        $form_data['topic']['public_title']['title'] = 'public_title';
        $form_data['topic']['public_title']['value'] = '';
        $form_data['topic']['public_title']['length'] = 40;
        $form_data['topic']['public_title']['type'] = 'safe_string';
        $form_data['topic']['public_title']['required'] = 'off';
        $form_data['topic']['public_title']['unique'] = 'off';

        if ($use_langs) {
            foreach ($languages as $language_id => $language_title) {
                $form_data['topic']['public_title_' . $language_id]['name'] = 'public_title_' . $language_id;
                $form_data['topic']['public_title_' . $language_id]['title'] = 'public_title' . ' <b>' . $language_id . '</b>';
                $form_data['topic']['public_title_' . $language_id]['value'] = '';
                $form_data['topic']['public_title_' . $language_id]['length'] = 40;
                $form_data['topic']['public_title_' . $language_id]['type'] = 'safe_string';
                $form_data['topic']['public_title_' . $language_id]['required'] = 'off';
                $form_data['topic']['public_title_' . $language_id]['unique'] = 'off';
            }
        }

        $form_data['topic']['meta_keywords']['name'] = 'meta_keywords';
        $form_data['topic']['meta_keywords']['title'] = 'meta_keywords';
        $form_data['topic']['meta_keywords']['value'] = '';
        $form_data['topic']['meta_keywords']['length'] = 40;
        $form_data['topic']['meta_keywords']['type'] = 'safe_string';
        $form_data['topic']['meta_keywords']['required'] = 'off';
        $form_data['topic']['meta_keywords']['unique'] = 'off';

        if ($use_langs) {
            foreach ($languages as $language_id => $language_title) {
                $form_data['topic']['meta_keywords_' . $language_id]['name'] = 'meta_keywords_' . $language_id;
                $form_data['topic']['meta_keywords_' . $language_id]['title'] = 'meta_keywords' . ' <b>' . $language_id . '</b>';
                $form_data['topic']['meta_keywords_' . $language_id]['value'] = '';
                $form_data['topic']['meta_keywords_' . $language_id]['length'] = 40;
                $form_data['topic']['meta_keywords_' . $language_id]['type'] = 'safe_string';
                $form_data['topic']['meta_keywords_' . $language_id]['required'] = 'off';
                $form_data['topic']['meta_keywords_' . $language_id]['unique'] = 'off';
            }
        }

        $form_data['topic']['meta_description']['name'] = 'meta_description';
        $form_data['topic']['meta_description']['title'] = 'meta_description';
        $form_data['topic']['meta_description']['value'] = '';
        $form_data['topic']['meta_description']['length'] = 40;
        $form_data['topic']['meta_description']['type'] = 'safe_string';
        $form_data['topic']['meta_description']['required'] = 'off';
        $form_data['topic']['meta_description']['unique'] = 'off';

        if ($use_langs) {
            foreach ($languages as $language_id => $language_title) {
                $form_data['topic']['meta_description_' . $language_id]['name'] = 'meta_description_' . $language_id;
                $form_data['topic']['meta_description_' . $language_id]['title'] = 'meta_description' . ' <b>' . $language_id . '</b>';
                $form_data['topic']['meta_description_' . $language_id]['value'] = '';
                $form_data['topic']['meta_description_' . $language_id]['length'] = 40;
                $form_data['topic']['meta_description_' . $language_id]['type'] = 'safe_string';
                $form_data['topic']['meta_description_' . $language_id]['required'] = 'off';
                $form_data['topic']['meta_description_' . $language_id]['unique'] = 'off';
            }
        }

        $form_data['topic']['description']['name'] = 'description';
        $form_data['topic']['description']['title'] = 'Описание';
        $form_data['topic']['description']['value'] = '';
        $form_data['topic']['description']['length'] = 40;
        $form_data['topic']['description']['type'] = 'textarea_editor';
        $form_data['topic']['description']['required'] = 'off';
        $form_data['topic']['description']['unique'] = 'off';

        if ($use_langs) {
            foreach ($languages as $language_id => $language_title) {
                $form_data['topic']['description_' . $language_id]['name'] = 'description_' . $language_id;
                $form_data['topic']['description_' . $language_id]['title'] = 'Описание' . ' <b>' . $language_id . '</b>';
                $form_data['topic']['description_' . $language_id]['value'] = '';
                $form_data['topic']['description_' . $language_id]['length'] = 40;
                $form_data['topic']['description_' . $language_id]['type'] = 'textarea_editor';
                $form_data['topic']['description_' . $language_id]['required'] = 'off';
                $form_data['topic']['description_' . $language_id]['unique'] = 'off';
            }
        }


        $form_data['topic']['url']['name'] = 'url';
        $form_data['topic']['url']['title'] = 'ЧПУ, название раздела латинскими буквами, например, arenda. Без точек и без /';
        $form_data['topic']['url']['value'] = '';
        $form_data['topic']['url']['length'] = 40;
        $form_data['topic']['url']['type'] = 'safe_string';
        $form_data['topic']['url']['required'] = 'on';
        $form_data['topic']['url']['unique'] = 'off';

        if ($this->getConfigValue('use_topic_actual_days')) {
            $form_data['topic']['actual_days']['name'] = 'actual_days';
            $form_data['topic']['actual_days']['title'] = 'Актуальность (кол.во дней до подсветки)';
            $form_data['topic']['actual_days']['value'] = '';
            $form_data['topic']['actual_days']['length'] = 40;
            $form_data['topic']['actual_days']['type'] = 'safe_string';
            $form_data['topic']['actual_days']['required'] = 'off';
            $form_data['topic']['actual_days']['unique'] = 'off';
        }


        require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
        $ATH = new Admin_Table_Helper();

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
        $TA = new table_admin();
        $TA->create_table_and_columns($form_data, 'topic');

        $ATH->create_table('topic');
        $ATH->update_table('topic');


        return $this->getStrModelFromDB();
    }

}
