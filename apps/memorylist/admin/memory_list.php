<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

class Memory_List extends Object_Manager {

    private $memorylist_table;
    private $memorylist_item_table;
    private $this_user;

    function __construct() {
        $this->SiteBill();
        $this->memorylist_table = 'memorylist';
        $this->memorylist_item_table = 'memorylist_item';
        $this->this_user = (int) $_SESSION['user_id'];
    }

    function main() {
        $do = $this->getRequestValue('do');

        $action = '_' . $do . 'Action';
        if (!method_exists($this, $action)) {
            $action = '_defaultAction';
        }
        $rs .= $this->$action();
        return $rs;
    }

    protected function _defaultAction() {
        return $this->grid();
    }

    protected function _getexcelAction() {
        $USER_ID = $this->this_user;
        $ids = (array) $this->getRequestValue('exported_ids');
        $filter_id = (int) $this->getRequestValue('filter_id');

        if ($filter_id != '' && $this->checkMemoryListOwner($filter_id, $USER_ID)) {
            $ids = array();
            $memory_lists = $this->getUserMemoryLists($this->this_user);
            foreach ($memory_lists[$filter_id]['items'] as $item) {
                $ids[] = $item['id'];
            }
        } elseif (count($ids) > 0) {
            $memory_lists = $this->getUserMemoryLists($this->this_user);
            $items_in_memory = array();
            $memory_lists = $this->getUserMemoryLists($this->this_user);
            foreach ($memory_lists as $ml) {
                if (isset($ml['items']) && count($ml['items']) > 0) {
                    foreach ($ml['items'] as $item) {
                        $items_in_memory[] = $item['id'];
                    }
                }
            }
            foreach ($ids as $k => $id) {
                if ((int) $id == 0 || !in_array($id, $items_in_memory)) {
                    unset($ids[$k]);
                } else {
                    $ids[$k] = (int) $id;
                }
            }
        }

        if (empty($ids)) {
            return;
        }


        $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $ext = (int) $this->getRequestValue('ext');
        if ($ext == 1) {
            $tplfile = 'xls_memorylist_ext.xlsx';
        } else {
            $tplfile = 'xls_memorylist.xlsx';
        }

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/admin/template/' . $tplfile)) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/admin/template/' . $tplfile;
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/site/template/' . $tplfile)) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/site/template/' . $tplfile;
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/userdata/admin/template/' . $tplfile)) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/userdata/admin/template/' . $tplfile;
        } else {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/userdata/admin/template/xls_memorylist.xlsx';
        }

        try {
            $objTPLExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load($tpl);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            die('Error loading file: ' . $e->getMessage());
        }

        $objTPLWorksheet = $objTPLExcel->getActiveSheet();

        $sheetData = $objTPLWorksheet->toArray(null, true, true, true);

        $exported_fields_names = array_values($sheetData[2]);
        $exported_fields_titles = array_values($sheetData[1]);
        $exported_fields_names_letters = array_keys($sheetData[2]);


        $firstletter = $exported_fields_names_letters[0];
        $last_letter = end($exported_fields_names_letters);
        reset($exported_fields_names_letters);

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php';
        $Model = new Data_Model();
        $advmodel = $Model->get_kvartira_model(false, true);

        $data_array = array();
        foreach ($ids as $id) {
            $_advmodel = $advmodel;
            $_advmodel['data'] = $Model->init_model_data_from_db('data', 'id', $id, $_advmodel['data'], true);
            if ($_advmodel['data']) {
                $data_array[] = SiteBill::modelSimplification($_advmodel['data']);
            }
        }
        //print_r($data_array);
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();

        $count = count($data_array);

        $ActiveSheet = $objTPLWorksheet;

        $row = 1;
        $column = 0;

        foreach ($exported_fields_titles as $title) {
            $ActiveSheet->setCellValueByColumnAndRow($column, $row, $title);
            $column++;
        }
        $row++;
        foreach ($data_array as $grid_item) {
            $column = 0;
            foreach ($exported_fields_names as $k => $field) {

                if ($field != '' && isset($grid_item[$field])) {
                    if ($grid_item[$field]['type'] == 'select_by_query' || $grid_item[$field]['type'] == 'select_box' || $grid_item[$field]['type'] == 'select_box_structure') {
                        $value = $grid_item[$field]['value_string'];
                    } elseif ($grid_item[$field]['type'] == 'textarea_editor') {
                        $value = strip_tags($grid_item[$field]['value']);
                    } elseif ($grid_item[$field]['type'] == 'uploadify_image') {
                        if (count($grid_item[$field]['image_array'] > 0)) {
                            $objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                            $objDrawing->setPath(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $grid_item[$field]['image_array'][0]['preview']);
                            $objDrawing->setHeight(100);
                            $colletter = $exported_fields_names_letters[$k];
                            $objDrawing->setCoordinates($colletter . $row);
                            $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
                            $value = '';
                        } else {
                            $value = '';
                        }
                    } else {
                        $value = $grid_item[$field]['value'];
                    }
                } else {
                    $value = '';
                }
                $ActiveSheet->setCellValueByColumnAndRow($column, $row, SiteBill::iconv(SITE_ENCODING, 'utf-8', $value));
                $column++;
            }

            if (1 == $this->getConfigValue('apps.userdata.xlsexportlink')) {
                if (1 == $this->getConfigValue('apps.seo.level_enable')) {
                    if ($category_structure['catalog'][$grid_item['topic_id']['value']]['url'] != '') {
                        $parent_category_url = $category_structure['catalog'][$grid_item['topic_id']['value']]['url'] . '/';
                    } else {
                        $parent_category_url = '';
                    }
                } else {
                    $parent_category_url = '';
                }

                if (1 == $this->getConfigValue('apps.seo.data_alias_enable') && $grid_item['translit_alias']['value'] != '') {
                    $href = SITEBILL_MAIN_URL . '/' . $parent_category_url . $grid_item['translit_alias']['value'];
                } elseif (1 == $this->getConfigValue('apps.seo.html_prefix_enable')) {
                    $href = SITEBILL_MAIN_URL . '/' . $parent_category_url . 'realty' . $grid_item['id']['value'] . '.html';
                } else {
                    $href = SITEBILL_MAIN_URL . '/' . $parent_category_url . 'realty' . $grid_item['id']['value'];
                }

                $href = 'http://' . $_SERVER['HTTP_HOST'] . $href;

                $cell = $ActiveSheet->getCellByColumnAndRow($column, $row);
                $cell->setValue('Перейти');
                $cell->getHyperlink()->setUrl($href);
            }


            $row++;
        }

        foreach ($exported_fields_names_letters as $k => $l) {
            $ActiveSheet->duplicateStyle($objTPLWorksheet->getStyle($l . '2'), $l . '2:' . $l . ($count + 1));
        }
        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objTPLExcel);
        $xlsx_file_name = "data" . date('Y-m-d_H_i') . ".xlsx";
        $xlsx_output_file = SITEBILL_DOCUMENT_ROOT . "/cache/upl/" . $xlsx_file_name;
        $objWriter->save($xlsx_output_file);

        $handle = fopen($xlsx_output_file, "r");
        $contents = fread($handle, filesize($xlsx_output_file));
        fclose($handle);

        header("Content-type: application/octet-stream");
        header("Content-disposition: attachment; filename=" . $xlsx_file_name . "");


        echo $contents;
        exit();
    }

    public function _getpdfAction() {
        $USER_ID = $this->this_user;
        $ids = (array) $this->getRequestValue('exported_ids');
        $filter_id = (int) $this->getRequestValue('filter_id');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/data/site/site.php');
        $data_site = new data_site();


        if ($filter_id != '' && $this->checkMemoryListOwner($filter_id, $USER_ID)) {
            $ids = array();
            $memory_lists = $this->getUserMemoryLists($this->this_user);
            foreach ($memory_lists[$filter_id]['items'] as $item) {
                $ids[] = $item['id'];
            }
        } elseif (count($ids) > 0) {
            $memory_lists = $this->getUserMemoryLists($this->this_user);
            $items_in_memory = array();
            $memory_lists = $this->getUserMemoryLists($this->this_user);
            foreach ($memory_lists as $ml) {
                if (isset($ml['items']) && count($ml['items']) > 0) {
                    foreach ($ml['items'] as $item) {
                        $items_in_memory[] = $item['id'];
                    }
                }
            }
            foreach ($ids as $k => $id) {
                if ((int) $id == 0 || !in_array($id, $items_in_memory)) {
                    unset($ids[$k]);
                } else {
                    $ids[$k] = (int) $id;
                }
            }
        }


        if (empty($ids)) {
            return;
        }
        echo $this->compile_pdf($ids, $data_site, $USER_ID);
        exit();
    }
    
    /**
     * Получение массива моделей объектов
     * @param array $ids
     * @return array
     */
    public function init_exported_data($ids){
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data_shared = $data_model->get_kvartira_model(false, true);
        $form_data_shared = $form_data_shared['data'];

        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $data = $data_model->init_model_data_from_db_multi('data', 'id', $ids, $form_data_shared, true);
        if(!empty($data)){
            foreach($data as $k => $v){
                $data[$k]['_href'] = $this->getRealtyHREF($v['id']['value'], true, array('topic_id' => $v['topic_id']['value']));
            }
        }
        return $data;
    }
    
    /**
     * Дополняет модель данных объявления дополнительными данными, например связанными данными из других объектов
     * @param array $data
     */
    public function adopt_model_to_pdfexport($data){
        return $data;
    }
    
    /**
     * Формирует pdf-страницу для списка объектов заданных массивом id
     * @global object $smarty
     * @param array $ids
     * @param boolean $stuff
     */
    public function compile_rich_pdf ( $ids, $stuff = false ) {
        global $smarty;
        
        $data = $this->init_exported_data($ids);

        if(!empty($data)){
            $data = $this->adopt_model_to_pdfexport($data);
        }
        
        $tpl = '';
        $tplfile = 'pdf_memory_list_grid_client.tpl';
        if($stuff){
            $tplfile = 'pdf_memory_list_grid_stuff.tpl';
        }
        
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/memorylist/site/template/' . $tplfile)) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/memorylist/site/template/' . $tplfile;
        } else {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/site/template/' . $tplfile;
        }


        $this->template->assign('_core_folder', SITEBILL_DOCUMENT_ROOT);
        $this->template->assign('grid_items', $data);

        $html = $smarty->fetch($tpl);
        
        $dompdfoptions = new \Dompdf\Options();
        $dompdfoptions->set('isRemoteEnabled', TRUE);
        $dompdf = new \Dompdf\Dompdf($dompdfoptions);
        $dompdf->loadHtml($html);
        $dompdf->render();
        $output = $dompdf->output();
        header("Content-type: application/pdf");
        echo $output;
        exit();
    }

    public function compile_pdf ( $ids, data_site $data_site, $USER_ID ) {
        if ( $this->getConfigValue('apps.pdfreport.grid_item') != '' ) {
            $default_params['grid_item'] = array_map('trim', explode(',', $this->getConfigValue('apps.pdfreport.grid_item')));
            if ($this->getConfigValue('apps.pdfreport.custom_grid_item_disable')) {
                $params['grid_item'] = $default_params['grid_item'];
            }
        } else {
            $default_params['grid_item'] = array('id', 'topic_id', 'city_id', 'district_id', 'street_id', 'price', 'image');
        }

        $REQUESTURIPATH = Sitebill::getClearRequestURI();
        if (!preg_match('/all[\/]?$/', $REQUESTURIPATH)) {
            $params['grid_conditions']['user_id'] = $this->getSessionUserId();
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($data_site);
        $common_grid->set_action('memorylist');
        $common_grid->set_grid_table('data');
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
            $stmt = $DBC->query($query, array('data_user_' . $USER_ID));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $used_fields = json_decode($ar['grid_fields']);
            }
            if (empty($used_fields)) {
                $query = 'SELECT `grid_fields` FROM ' . DB_PREFIX . '_table_grids WHERE `action_code`=?';
                $stmt = $DBC->query($query, array('data'));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $used_fields = json_decode($ar['grid_fields']);
                }
            }

            if (!empty($used_fields)) {
                $default_params['grid_item'] = $used_fields;
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

        //if (isset($params['grid_conditions']) && count($params['grid_conditions']) > 0) {
        $params_ids = array();
        $params_ids['id'] = $ids;
        //print_r($ids);
        $common_grid->set_conditions($params_ids);
        //}
        //$common_grid->set_grid_query('SELECT * FROM '.DB_PREFIX.'_'.$this->table_name.' ORDER BY name ASC');
        $common_grid->setPagerParams(array('action' => $data_site->action, 'page' => 1, 'per_page' => count($ids)));

        $rs = $common_grid->extended_items();
        //$common_grid->construct_query();
        $common_grid->construct_grid();
        $grid_array = $common_grid->construct_grid_array();
        $grid_array = $common_grid->degradate_grid($grid_array);

        //echo '<pre>';
        //print_r($this->data_model);
        //echo '</pre>';
        //exit;
        //echo '<pre>';
        //print_r($default_params['grid_item']);
        //echo '</pre>';

        $this->template->assign('header_items', $default_params['grid_item']);
        $this->template->assign('data_model', $data_site->data_model);

        $grid_constructor = $this->_getGridConstructor();
        $grid_array_transformed = @$grid_constructor->transformGridData($grid_array);
        //echo '<pre>';
        //print_r($grid_array);
        //echo '</pre>';
        //exit;

        return $data_site->createPDF($grid_array, $grid_array_transformed);
    }

    public function deleteMemorylist($id) {
        $memorylist_id = $id;
        if ($this->checkMemoryListOwner($memorylist_id, $this->this_user)) {
            $DBC = DBC::getInstance();
            $query = 'DELETE FROM ' . DB_PREFIX . '_' . $this->memorylist_table . ' WHERE memorylist_id=?';
            $stmt = $DBC->query($query, array($memorylist_id));
            $query = 'DELETE FROM ' . DB_PREFIX . '_' . $this->memorylist_item_table . ' WHERE memorylist_id=?';
            $stmt = $DBC->query($query, array($memorylist_id));
        }
    }

    protected function _deleteAction() {
        $memorylist_id = (int) $this->getRequestValue('filter_id');
        if ($this->checkMemoryListOwner($memorylist_id, $this->this_user)) {
            $this->deleteMemoryList($memorylist_id);
        }
        return $this->_defaultAction();
    }

    public function checkMemoryListOwner($memorylist_id, $user_id) {
        if ( $this->getConfigValue('apps.memorylist.public_access_enable') ) {
            return true;
        }
        $DBC = DBC::getInstance();
        $query = 'SELECT COUNT(memorylist_id) AS cnt FROM ' . DB_PREFIX . '_' . $this->memorylist_table . ' WHERE memorylist_id=? AND user_id=?';

        $stmt = $DBC->query($query, array($memorylist_id, $user_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['cnt'] > 0) {
                return true;
            }
        }
        return false;
    }

    public function showfilter() {
        $USER_ID = $this->this_user;
        $ids = (array) $this->getRequestValue('exported_ids');
        $filter_id = (int) $this->getRequestValue('filter_id');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/data/site/site.php');
        $data_site = new data_site();
        return $data_site->grid();

        $memorylist_id = (int) $this->getRequestValue('filter_id');
        $mls = $this->getUserMemoryLists($this->this_user);

        if (!isset($mls[$memorylist_id])) {
            return $this->grid();
        }

        $items = array();

        foreach ($mls[$memorylist_id]['items'] as $item) {
            $items_in_memory[$item['id']][] = $mls[$memorylist_id];
            $items[] = $item['id'];
        }



        //require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
        //$this->_grid_constructor = new Grid_Constructor();
        //$grid_constructor=$this->_grid_constructor;
        $grid_constructor = $this->_getGridConstructor();
        $USER_ID = $this->this_user;

        $params = array();

        $params['id'] = $items;
        $params['no_portions'] = 1;
        $params['admin'] = 1;

        $res = $grid_constructor->get_sitebill_adv_ext_base_ajax($params);
        global $smarty;

        $smarty->assign('items_in_memory', $items_in_memory);

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/site/template/memorylist_items_grid.tpl')) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/userdata/site/template/memorylist_items_grid.tpl';
        } else {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/userdata/site/template/memorylist_items_grid.tpl';
        }
        $smarty->assign('grid_items', $res['data']);
        $grid = $smarty->fetch($tpl);
        return $grid;
    }

    function grid($params = array(), $default_params = array()) {
        
        $admin_zone_url = false;
        if(isset($params['admin_zone_url'])){
            $admin_zone_url = true;
        }

        global $smarty;

        $user_filters = $this->getUserMemoryLists($this->this_user);
        
        if(!empty($user_filters)){
            foreach($user_filters as $k => $v){
                if($admin_zone_url){
                    $user_filters[$k]['_show_link'] = SITEBILL_MAIN_URL.'/admin/?action=data&memorylist_id='.$v['memorylist_id'];
                    $user_filters[$k]['_pdf_link'] = SITEBILL_MAIN_URL.'/admin/?action=data&do=memorylist&subdo=getpdf&filter_id='.$v['memorylist_id'];
                    $user_filters[$k]['_excel_link'] = SITEBILL_MAIN_URL.'/admin/?action=data&do=memorylist&subdo=getexcel&filter_id='.$v['memorylist_id'];
                    $user_filters[$k]['_delete_link'] = SITEBILL_MAIN_URL.'/admin/?action=data&do=memorylist&subdo=delete&filter_id='.$v['memorylist_id'];
                }else{
                    $user_filters[$k]['_show_link'] = SITEBILL_MAIN_URL.'/memorylist/?do=showfilter&filter_id='.$v['memorylist_id'];
                    $user_filters[$k]['_pdf_link'] = SITEBILL_MAIN_URL.'/memorylist/?do=getpdf&filter_id='.$v['memorylist_id'];
                    $user_filters[$k]['_excel_link'] = SITEBILL_MAIN_URL.'/memorylist/?do=getexcel&filter_id='.$v['memorylist_id'];
                    $user_filters[$k]['_delete_link'] = SITEBILL_MAIN_URL.'/memorylist/?do=delete&filter_id='.$v['memorylist_id'];
                }
                
            }
        }
        
        $smarty->assign('user_filters', $user_filters);
        if (1 == intval($this->getConfigValue('apps.pdfreport.enabled'))) {
            $smarty->assign('memorylist_pdf', 1);
        } else {
            $smarty->assign('memorylist_pdf', 0);
        }

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/excel/admin/admin.php') || file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/excelfree/admin/admin.php')) {
            $smarty->assign('memorylist_excel', 1);
        } else {
            $smarty->assign('memorylist_excel', 0);
        }

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/memorylist/site/template/user_filters_grid.tpl')) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/memorylist/site/template/user_filters_grid.tpl';
        } else {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/site/template/user_filters_grid.tpl';
        }

        return $smarty->fetch($tpl);
    }

    public function createMemoryList($title) {
        $DBC = DBC::getInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . '_' . $this->memorylist_table . ' (user_id, title, created_at) VALUES (?, ?, ?)';
        $stmt = $DBC->query($query, array($this->this_user, $title, date('Y-m-d H:i:s', time())));
        if ($stmt) {
            return $DBC->lastInsertId();
        }
        return false;
    }
    
    /**
     * Получаем memory_list_id по входным параметрам
     * Если такого листа еще нет, тогда метод попробует создать его и присвоит ему ИД
     * 
     * @param type $domain
     * @param type $user_id
     * @param type $deal_id
     * @param type $title
     */
    public function get_domain_memory_list_id ( $domain, $user_id, $deal_id, $title ) {
        $DBC = DBC::getInstance();
        if ( $this->getConfigValue('apps.memorylist.public_access_enable') ) {
            $query = 'SELECT memorylist_id FROM ' . DB_PREFIX . '_' . $this->memorylist_table . ' WHERE domain=? and deal_id=? limit 1';
            $stmt = $DBC->query($query, array($domain, $deal_id), $success);
        } else {
            $query = 'SELECT memorylist_id FROM ' . DB_PREFIX . '_' . $this->memorylist_table . ' WHERE domain=? and user_id=? and deal_id=? limit 1';
            $stmt = $DBC->query($query, array($domain, $user_id, $deal_id), $success);
        }

        if ( !$success ) {
            $this->writeLog($DBC->getLastError());
        }
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ( $ar['memorylist_id'] > 0 ) {
                return $ar['memorylist_id'];
            }
        }
        $memorylist_id = $this->create_domain_memory_list($domain, $user_id, $deal_id, $title);
        return $memorylist_id;
    }
    
    public function create_domain_memory_list ( $domain, $user_id, $deal_id, $title ) {
        $DBC = DBC::getInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . '_' . $this->memorylist_table . ' (user_id, title, created_at, domain, deal_id) VALUES (?, ?, ?, ?, ?)';
        $stmt = $DBC->query($query, array($user_id, $title, date('Y-m-d H:i:s', time()), $domain, $deal_id));
        if ($stmt) {
            return $DBC->lastInsertId();
        }
        return false;
    }
    
    public function toggle_item (  $memorylist_id, $data_id ) {
        $DBC = DBC::getInstance();
        $query = 'SELECT memorylist_id, id FROM ' . DB_PREFIX . '_' . $this->memorylist_item_table . ' WHERE memorylist_id=? and id=?';
        $stmt = $DBC->query($query, array($memorylist_id, $data_id), $success);
        if ( !$success ) {
            $this->writeLog($DBC->getLastError());
        }
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ( $ar['memorylist_id'] > 0 ) {
                $this->delete_item($memorylist_id, $data_id);
                return 'remove';
            }
        }
        $this->add_item($memorylist_id, $data_id);
        return 'add';
    }
    
    public function parse_memory_list ( $domain, $deal_id, $user_id, $primary_key, $rows ) {
        $user_memory_list = $this->getUserMemoryLists_indexed_by_data_id($user_id, $domain, $deal_id);
        foreach ($rows as $idx => $item) {
            if ( isset($user_memory_list[$rows[$idx][$primary_key]['value']]) ) {
                $rows[$idx][$primary_key]['collections'] = $deal_id;
            }
            //$ra['index'][$item[$primary_key]['value']] = $idx;
        }
        return $rows;
    }
    
    public function delete_item($memorylist_id, $data_id) {
        $DBC = DBC::getInstance();
        $query = 'DELETE FROM ' . DB_PREFIX . '_' . $this->memorylist_item_table . ' WHERE memorylist_id=? and id=?';
        $stmt = $DBC->query($query, array($memorylist_id, $data_id), $success);
        if ( !$success ) {
            $this->writeLog($DBC->getLastError());
        }
        return true;
    }
    public function add_item($memorylist_id, $data_id) {
        $DBC = DBC::getInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . '_' . $this->memorylist_item_table . ' (memorylist_id, id) VALUES (?, ?)';
        $stmt = $DBC->query($query, array($memorylist_id, $data_id), $success);
        if ( !$success ) {
            $this->writeLog($DBC->getLastError());
        }
        return true;
    }
    

    public function appendItems($memorylist_id, $items) {
        $DBC = DBC::getInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . '_' . $this->memorylist_item_table . ' (memorylist_id, id) VALUES (?, ?)';
        foreach ($items as $item) {
            $stmt = $DBC->query($query, array($memorylist_id, $item));
        }
    }

    /* private function deleteMemoryList($memorylist_id) {
      $DBC = DBC::getInstance();
      $query = 'DELETE FROM ' . DB_PREFIX . '_' . $this->memorylist_table . ' WHERE memorylist_id=?';
      $stmt = $DBC->query($query, array($memorylist_id));
      $query = 'DELETE FROM ' . DB_PREFIX . '_' . $this->memorylist_item_table . ' WHERE memorylist_id=?';
      $stmt = $DBC->query($query, array($memorylist_id));
      } */

    public function deleteItems($memorylist_id, $items) {
        $DBC = DBC::getInstance();
        $query = 'DELETE FROM ' . DB_PREFIX . '_' . $this->memorylist_item_table . ' WHERE memorylist_id=? AND id=?';
        foreach ($items as $item) {
            $stmt = $DBC->query($query, array($memorylist_id, $item));
        }
        return true;
    }
    
    function select_data_ids_by_memorylist_id ($user_id, $memorylist_id) {
        $DBC = DBC::getInstance();
        $ids = array();

        if ( $this->getConfigValue('apps.memorylist.public_access_enable') ) {
            $query = "SELECT ml.* FROM ".DB_PREFIX."_memorylist_item ml, ".DB_PREFIX."_memorylist m WHERE m.memorylist_id=ml.memorylist_id AND m.memorylist_id=?";
            $stmt = $DBC->query($query, array($memorylist_id));
        } else {
            $query = "SELECT ml.* FROM ".DB_PREFIX."_memorylist_item ml, ".DB_PREFIX."_memorylist m WHERE m.memorylist_id=ml.memorylist_id AND m.user_id=? AND m.memorylist_id=?";
            $stmt = $DBC->query($query, array($user_id, $memorylist_id));
        }


        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ids[] = $ar['id'];
            }
        }
        return $ids;
    }
    
    public function getUserMemoryLists_indexed_by_data_id($user_id, $domain = false, $deal_id = false) {
        $DBC = DBC::getInstance();
        $mlids = array();
        $mls = array();
        $mls_tmp = array();
        if ( $this->getConfigValue('apps.memorylist.public_access_enable') ) {
            $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->memorylist_table . ' WHERE user_id IS NOT NULL';
            if ( $domain ) {
                $query .= ' AND domain=? AND deal_id=?';
                $dbc_array = array($domain, $deal_id);
            } else {
                $dbc_array = array();
            }
        } else {
            $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->memorylist_table . ' WHERE user_id=?';
            if ( $domain ) {
                $query .= ' AND domain=? AND deal_id=?';
                $dbc_array = array($user_id, $domain, $deal_id);
            } else {
                $dbc_array = array($user_id);
            }
        }


        $stmt = $DBC->query($query, $dbc_array);
        
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $mls_tmp[$ar['memorylist_id']] = $ar;
            }
        }

        if (!empty($mls_tmp)) {
            $mlids = array_keys($mls_tmp);
        }

        if (!empty($mlids)) {
            $qar = array();
            foreach ($mlids as $m) {
                $qar[] = '?';
            }
            $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->memorylist_item_table . ' WHERE memorylist_id IN (' . implode(',', array_fill(0, count($qar), '?')) . ')';

            $stmt = $DBC->query($query, $mlids);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $mls[$ar['id']]['items'][] = $ar;
                }
            }
        }
        return $mls;
    }
    

    public function getUserMemoryLists($user_id, $domain = false, $deal_id = false) {
        $DBC = DBC::getInstance();
        $mlids = array();
        $mls = array();
        if ( $this->getConfigValue('apps.memorylist.public_access_enable') ) {
            $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->memorylist_table . ' WHERE user_id IS NOT NULL';
            if ( $domain ) {
                $query .= ' AND domain=? AND deal_id=?';
                $dbc_array = array($domain, $deal_id);
            } else {
                $dbc_array = array();
            }
        } else {
            $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->memorylist_table . ' WHERE user_id=?';
            if ( $domain ) {
                $query .= ' AND domain=? AND deal_id=?';
                $dbc_array = array($user_id, $domain, $deal_id);
            } else {
                $dbc_array = array($user_id);
            }
        }


        $stmt = $DBC->query($query, $dbc_array);
        
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $mls[$ar['memorylist_id']] = $ar;
            }
        }

        if (!empty($mls)) {
            $mlids = array_keys($mls);
        }

        if (!empty($mlids)) {
            $qar = array();
            foreach ($mlids as $m) {
                $qar[] = '?';
            }
            $query = 'SELECT * FROM ' . DB_PREFIX . '_' . $this->memorylist_item_table . ' WHERE memorylist_id IN (' . implode(',', array_fill(0, count($qar), '?')) . ')';

            $stmt = $DBC->query($query, $mlids);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $mls[$ar['memorylist_id']]['items'][] = $ar;
                }
            }
        }
        return $mls;
    }

    function select_data_ids_by_deal_id ($user_id, $domain, $deal_id) {
        $DBC = DBC::getInstance();
        $ids = array();

        if ( $this->getConfigValue('apps.memorylist.public_access_enable') ) {
            $query = "SELECT ml.* FROM ".DB_PREFIX."_memorylist_item ml, ".DB_PREFIX."_memorylist m WHERE m.memorylist_id=ml.memorylist_id AND m.domain=? AND m.deal_id=?";
            $stmt = $DBC->query($query, array($domain, $deal_id));
        } else {
            $query = "SELECT ml.* FROM ".DB_PREFIX."_memorylist_item ml, ".DB_PREFIX."_memorylist m WHERE m.memorylist_id=ml.memorylist_id AND m.user_id=? AND m.domain=? AND m.deal_id=?";
            $stmt = $DBC->query($query, array($user_id, $domain, $deal_id));
        }


        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ids[] = $ar['id'];
            }
        }
        return $ids;
    }
}
