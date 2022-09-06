<?php

class search_forms_admin extends table_admin {

    public function __construct() {
        parent::__construct();
        if (!$this->helper->check_table_exist('table_searchform')) {
            $this->install();
        }
        $this->table_name = 'table_searchform';
        $this->primary_key = 'searchform_id';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/searchform_model.php');
        $Object = new Searchform_Model();
        $this->data_model = $Object->get_model();
    }

    function install() {
        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_table_searchform` (
			  `searchform_id` int(11) NOT NULL AUTO_INCREMENT,
			  `topic_id` text NOT NULL,
			  `columns` text NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `title_en` varchar(255) NOT NULL,
			  PRIMARY KEY (`searchform_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=" . DB_ENCODING . ";";
        $DBC = DBC::getInstance();
        $success = false;
        $stmt = $DBC->query($query, array(), $rows, $success);
        if (!$success) {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED_ERROR');
        } else {
            $rs = Multilanguage::_('L_APPLICATION_INSTALLED');
        }
        return $rs;
    }

    function getTopMenu() {
        $rs = parent::getTopMenu();
        $rs .= '<a href="?action=table&section=search_forms&do=new" class="btn btn-primary">Новая форма поиска</a> ';
        return $rs;
    }

    function main() {
        $rs = $this->getTopMenu();
        switch ($this->getRequestValue('do')) {
            case 'edit' : {
                    $rs .= $this->editSearchForm($this->getRequestValue($this->primary_key));
                    break;
                }
            case 'new' : {
                    $rs .= $this->editSearchForm(0);
                    break;
                }
            case 'mass_delete' : {
                    $id_array = array();
                    $ids = trim($this->getRequestValue('ids'));
                    if ($ids != '') {
                        $id_array = explode(',', $ids);
                    }
                    $rs .= $this->mass_delete_data($this->table_name, $this->primary_key, $id_array);
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
            default : {
                    $rs .= $this->grid();
                }
        }

        $rs_new = $this->get_app_title_bar();
        $rs_new .= $rs;
        return $rs_new;
    }

    private function editSearchForm($id) {
        //return 'Правим форму '.$id;
        $form_data = array();
        $avial_cols = array();
        $selected_cols = array();

        $model_data = $this->helper->load_model('data');
        $all_form_fields = $this->get_test_form($model_data['data']);

        if ($id != 0) {
            $DBC = DBC::getInstance();
            $stmt = $DBC->query('SELECT * FROM ' . DB_PREFIX . '_table_searchform WHERE ' . $this->primary_key . '=? LIMIT 1', array($id));
            if ($stmt) {
                $form_data = $DBC->fetch($stmt);
                if ($form_data['topic_id'] != '') {
                    $form_data['topic_id'] = explode(',', $form_data['topic_id']);
                } else {
                    $form_data['topic_id'] = array();
                }
                $selected_columns_ids = unserialize($form_data['columns']);


                $columns_ids = $this->_getColumnsNameIds();

                foreach ($selected_columns_ids as $k => $sc) {
                    if (isset($all_form_fields[$columns_ids[$sc]])) {
                        $selected_cols[] = $all_form_fields[$columns_ids[$sc]];
                        unset($all_form_fields[$columns_ids[$sc]]);
                    }
                }
            }
        } else {
            $form_data['searchform_id'] = 0;
            $form_data['title'] = '';
            $form_data['title_en'] = '';
            $form_data['topic_id'] = array();
        }
        $avial_cols = $all_form_fields;
        include_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php';
        $SM = new Structure_Manager();
        $this->template->assert('topic_select_box', $SM->getCategorySelectBoxWithName('search_form_topic', (array) $form_data['topic_id']));
        $this->template->assert('form_id', $form_data['searchform_id']);
        $this->template->assert('form_title', $form_data['title']);
        $this->template->assert('form_title_en', $form_data['title_en']);
        $this->template->assert('available_columns', $avial_cols);
        $this->template->assert('selected_columns', $selected_cols);
        global $smarty;
        return $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/template/search_form_main.tpl');
        //global $smarty;
        //$this->template->assert('forms_list', $forms);
        //return $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/template/search_form_list.tpl');
    }

    function grid($params = array(), $default_params = array()) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);


        $common_grid->add_grid_item($this->primary_key);
        $common_grid->add_grid_item('title');

        $common_grid->add_grid_control('edit');
        $common_grid->add_grid_control('delete');

        $common_grid->setPagerParams(array('action' => $this->action, 'page' => $this->getRequestValue('page'), 'per_page' => $this->getConfigValue('common_per_page')));

        $common_grid->set_grid_query("select * from " . DB_PREFIX . "_" . $this->table_name . " order by " . $this->primary_key . " asc");
        $rs = $common_grid->construct_grid();
        return $rs;
    }

    public function save_search_form() {
        $DBC = DBC::getInstance();
        $columns_ids = array();
        $form_id = (int) $this->getRequestValue('form_id');
        $topic_id = $this->getRequestValue('topic_id');
        if (is_array($topic_id) && !empty($topic_id)) {
            $topic_id = implode(',', $topic_id);
        } else {
            $topic_id = '';
        }

        //$form_title=preg_replace('/[^A-Za-zА-Яа-я0-9єії\-_ ]/u', '', $this->getRequestValue('form_title'));
        $form_title = $this->getRequestValue('form_title');
        $form_title = SiteBill::iconv('utf-8', SITE_ENCODING, $form_title);

        $form_title_en = $this->getRequestValue('form_title_en');
        $form_title_en = SiteBill::iconv('utf-8', SITE_ENCODING, $form_title_en);

        $fields = $this->getRequestValue('fields');
        if (count($fields) == 0) {
            $q = "DELETE FROM " . DB_PREFIX . "_table_searchform WHERE `searchform_id`=" . $form_id;
            $stmt = $DBC->query($q);
            return;
        }
        $q = 'SELECT columns_id, name FROM ' . DB_PREFIX . '_columns WHERE table_id=(SELECT table_id FROM ' . DB_PREFIX . '_table WHERE `name`=\'data\' LIMIT 1)';
        $stmt = $DBC->query($q);
        while ($ar = $DBC->fetch($stmt)) {
            $columns_ids[$ar['name']] = $ar['columns_id'];
        }
        $q = "DELETE FROM " . DB_PREFIX . "_table_searchform WHERE `searchform_id`=" . $form_id;
        $stmt = $DBC->query($q);

        $input_fields = array();
        foreach ($fields as $f) {
            $input_fields[] = $columns_ids[$f];
        }

        if ($form_id == 0) {
            $DBC->query("INSERT INTO " . DB_PREFIX . "_table_searchform (`topic_id`, `columns`, `title`, `title_en`) VALUES (?,?,?,?)", array($topic_id, serialize($input_fields), $form_title, $form_title_en));
        } else {
            $DBC->query("INSERT INTO " . DB_PREFIX . "_table_searchform (`searchform_id`, `topic_id`, `columns`, `title`, `title_en`) VALUES (?,?,?,?,?)", array($form_id, $topic_id, serialize($input_fields), $form_title, $form_title_en));
        }
    }

    /*
      function get_local_search_form(){
      return '';
      include_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php';
      $topic_id=0;
      $topic_id=(int)$this->getRequestValue('topic_id');
      $SK=new SiteBill_Krascap();
      $result=$SK->_detectUrlParams($_SERVER['REQUEST_URI']);
      if($result['topic_id']){
      $topic_id=$result['topic_id'];
      }

      $columns_ids=$this->_getColumnsNameIds();


      $parents=array(0);

      $SM=new Structure_Manager();
      $chains=$SM->createCatalogChains();

      foreach($chains['num'] as $k=>$v){
      $chains['num'][$k]=explode('|',$v);
      }
      if(isset($chains['num'][$topic_id])){
      $parents=array_merge($parents, $chains['num'][$topic_id]);
      }

      $parents=array_reverse($parents);

      foreach($parents as $p){
      $topic_columns=$this->getTopicColumns($p);
      if($topic_columns){
      break;
      }else{
      $topic_columns=array();
      return false;
      }
      }

      $model_data = $this->helper->load_model('data');
      $model_data = $this->helper->add_ajax($model_data);

      require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
      $data_model = new Data_Model();
      $model_data['data']=$data_model->init_model_data_from_request($model_data['data']);

      $all_columns=$this->get_test_form($model_data['data']);

      foreach($topic_columns as $k=>$sc){
      if(isset($all_columns[$columns_ids[$sc]])){
      $selected_cols[$columns_ids[$sc]]=$all_columns[$columns_ids[$sc]];
      //unset($all_columns[$columns_ids[$sc]]);
      }
      }


      $this->template->assert('selected_columns', $selected_cols);
      global $smarty;
      $form_body=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/template/search_form_local.tpl');
      return array('form_body'=>$form_body,
      'form_title'=>$this->getTopicFormTitle($topic_id)
      );
      }
     */

    function get_local_search_forms() {

        $forms = array();
        if (NULL !== $this->getRequestValue('topic_id')) {
            $topic_id = (array) $this->getRequestValue('topic_id');
        } else {
            $topic_id = array();
        }

        include_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php';
        $SK = new SiteBill_Krascap();
        $result = $SK->_detectUrlParams($_SERVER['REQUEST_URI']);

        if ($result['topic_id']) {
            $topic_id = (array) $result['topic_id'];
        }

        $DBC = DBC::getInstance();
        $stmt = $DBC->query('SELECT * FROM ' . DB_PREFIX . '_table_searchform order by searchform_id');
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $_forms[] = $ar;
            }
        }
        if ( !is_array($_forms) ) {
            return $forms;
        }

        if (count($_forms) > 0) {
            global $smarty;
            $model_data = $this->helper->load_model('data');
            $model_data = $this->helper->add_ajax($model_data);
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            $data_model = new Data_Model();
            $model_data['data'] = $data_model->init_model_data_from_request($model_data['data'], true);
            $all_columns = $this->get_test_form($model_data['data']);
            $columns_ids = $this->_getColumnsNameIds();


            foreach ($_forms as $_f) {

                $lang_key = 'title_' . $_SESSION['_lang'];
                if ($_f[$lang_key] != '') {
                    $_f['title'] = $_f[$lang_key];
                }

                $partial_data = $all_columns;
                $selected_cols = array();
                $this_form_columns = unserialize($_f['columns']);
                foreach ($this_form_columns as $k => $sc) {
                    if (isset($partial_data[$columns_ids[$sc]])) {

                        if ($partial_data[$columns_ids[$sc]]['title'] == 'Город' and $_SESSION['_lang'] != 'ru') {
                            $partial_data[$columns_ids[$sc]]['title'] = 'City';
                        }
                        if ($partial_data[$columns_ids[$sc]]['title'] == 'Цена' and $_SESSION['_lang'] != 'ru') {
                            $partial_data[$columns_ids[$sc]]['title'] = 'Price';
                        }
                        if ($partial_data[$columns_ids[$sc]]['title'] == 'Кол.во комнат' and $_SESSION['_lang'] != 'ru') {
                            $partial_data[$columns_ids[$sc]]['title'] = 'Rooms';
                        }


                        $selected_cols[$columns_ids[$sc]] = $partial_data[$columns_ids[$sc]];
                    }
                }
                if ($_f['topic_id'] != '') {
                    $_topic_id = explode(',', $_f['topic_id']);
                } else {
                    $_topic_id = array();
                }

                if (!empty($_topic_id)) {
                    foreach ($_topic_id as $k => $t) {
                        $selected_cols['topic_id' . $k] = array(
                            'title' => '',
                            'title_en' => '',
                            'type' => 'hidden',
                            'required' => 'on',
                            'html' => '<input type="hidden" name="topic_id[]" value="' . $t . '" />',
                            'tab' => ''
                        );
                    }
                }
                $this->template->assert('selected_columns', $selected_cols);
                if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/table/admin/template/search_form_local.tpl')) {
                    $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/table/admin/template/search_form_local.tpl';
                } else {
                    $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/template/search_form_local.tpl';
                }
                $form_body = $smarty->fetch($tpl);
                $forms[$_f['title']]['body'] = $form_body;
                $forms[$_f['title']]['id'] = 'lsf'.md5($_f['title']);

                if (count(array_intersect($_topic_id, $topic_id)) > 0) {
                    $forms[$_f['title']]['active'] = 1;
                } else {
                    $forms[$_f['title']]['active'] = 0;
                }
            }
        }
        return $forms;





        /*
          $columns_ids=$this->_getColumnsNameIds();


          foreach($parents as $p){
          $topic_columns=$this->getTopicColumns($p);
          if($topic_columns){
          break;
          }else{
          $topic_columns=array();
          return false;
          }
          }

          $model_data = $this->helper->load_model('data');
          $model_data = $this->helper->add_ajax($model_data);

          require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
          $data_model = new Data_Model();
          $model_data['data']=$data_model->init_model_data_from_request($model_data['data']);

          $all_columns=$this->get_test_form($model_data['data']);

          foreach($topic_columns as $k=>$sc){
          if(isset($all_columns[$columns_ids[$sc]])){
          $selected_cols[$columns_ids[$sc]]=$all_columns[$columns_ids[$sc]];
          //unset($all_columns[$columns_ids[$sc]]);
          }
          }


          $this->template->assert('selected_columns', $selected_cols);
          global $smarty;
          $form_body=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/template/search_form_local.tpl');
          return array('form_body'=>$form_body,
          'form_title'=>$this->getTopicFormTitle($topic_id)
          ); */
    }

    function get_test_form($form_data = array(), $do = 'new', $language_id = 0, $button_title = '') {

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/search_form_generator.php')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/search_form_generator.php');
            $form_generator = new Search_Form_Generator();
        } else {
            $form_generator = new Form_Generator();
        }

        $avtypes = array(
            'safe_string',
            'select_box',
            'structure',
            'price',
            'email',
            'mobilephone',
            'select_by_query',
            'select_by_query_multiple',
            'select_box_structure',
            'select_box_structure_simple_multiple',
            'checkbox'
        );

        foreach ($form_data as $k => $v) {
            if (!in_array($v['type'], $avtypes)) {
                unset($form_data[$k]);
            }
        }


        $els = $form_generator->compile_form_elements($form_data, true);

        foreach ($els['public'][$this->getConfigValue('default_tab_name')] as $e) {
            $_els[$e['name']] = $e;
            if ($_els[$e['name']]['type'] == 'checkbox') {
                $_els[$e['name']]['html'] = str_replace('value="0"', 'value="1"', $_els[$e['name']]['html']);
            }
        }

        return $_els;
    }

    private function _getColumnsNameIds() {
        $DBC = DBC::getInstance();

        $columns_ids = array();
        $q = 'SELECT columns_id, name FROM ' . DB_PREFIX . '_columns WHERE table_id=(SELECT table_id FROM ' . DB_PREFIX . '_table WHERE `name`=\'data\' LIMIT 1)';
        $stmt = $DBC->query($q);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $columns_ids[$ar['columns_id']] = $ar['name'];
            }
        }

        return $columns_ids;
    }

}
