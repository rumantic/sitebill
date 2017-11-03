<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Excel admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
//set_include_path(SITEBILL_DOCUMENT_ROOT.'/apps/excelfree/lib/phpexcel/');
class excelfree_admin extends Object_Manager {

    protected $data_manager_export;

    /**
     * Constructor
     */
    function __construct($realty_type = false) {
        $this->SiteBill();
        Multilanguage::appendAppDictionary('excelfree');

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_admin = new config_admin();

        if (!$config_admin->check_config_item('apps.excelfree.geodata_strategy')) {
            $config_admin->addParamToConfig('apps.excelfree.geodata_strategy', '', 'Стратегия обработки географических данных');
        }

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/excelfree/admin/data_manager_export.php';
        $this->data_manager_export = new Data_Manager_Export();
        $this->action = 'excelfree';
        $this->app_title = Multilanguage::_('APPLICATION_NAME', 'excelfree');
    }

    function install() {
        $rs = Multilanguage::_('L_APPLICATION_INSTALLED');
        return $rs;
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

    function main() {


        if ($this->getRequestValue('do') == 'install') {
            $rs = $this->install();
            return $rs;
        }

        /* if ( !$this->check_table_exist('excel_template') or !$this->check_table_exist('excel_template_columns') or !$this->check_table_exist('excel_template_params')) {
          $rs = '<h1>Приложение не установлено. <a href="?action=excel&do=install">Установить</a></h1>';
          return $rs;
          } */

        if ($this->getRequestValue('do') == 'export_filter') {
            $filter .= $this->data_manager_export->get_search_form($this->data_manager_export->get_search_model($template_params), 'edit', 0, '');
            $rs .= $this->get_form($filter);
            //echo $rs;
        } elseif ($this->getRequestValue('do') == 'install') {
            $rs = $this->install();
        } elseif ($this->getRequestValue('do') == 'export') {
            $rs = $this->get_export_form();
        } else {
            $rs = $this->get_form();
        }
        //$this->load_xls();

        $rs_new = $this->get_app_title_bar();
        //$rs_new = '<div class="apps_path">'.Multilanguage::_('L_ADMIN_MENU_APPLICATIONS').' / ';
        //$rs_new .= '<a href="?action=excelfree">Excel</a>';
        //$rs_new .= '</div>';
        //$rs_new .= '<div class="clear"></div>';
        $rs_new .= $rs;

        return $rs_new;
    }

    function get_export_form() {
        require_once SITEBILL_APPS_DIR . '/third/phpexcel/PHPExcel/IOFactory.php';
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/grid_constructor.php';
        $grid_constructor = new Grid_Constructor();

        $params['user_id'] = $this->getRequestValue('user_id');
        $params['topic_id'] = $this->getRequestValue('topic_id');
        $params['order'] = $this->getRequestValue('order');
        $params['region_id'] = $this->getRequestValue('region_id');
        $params['city_id'] = $this->getRequestValue('city_id');
        $params['district_id'] = $this->getRequestValue('district_id');
        $params['metro_id'] = $this->getRequestValue('metro_id');
        $params['street_id'] = $this->getRequestValue('street_id');
        $params['page'] = $this->getRequestValue('page');
        $params['asc'] = $this->getRequestValue('asc');
        $params['price'] = $this->getRequestValue('price');
        $params['active'] = $this->getRequestValue('active');
        $params['hot'] = $this->getRequestValue('hot');
        $params['id'] = $this->getRequestValue('srch_id');
        $params['srch_word'] = $this->getRequestValue('srch_word');
        $params['srch_phone'] = $this->getRequestValue('srch_phone');
        $params['srch_date_from'] = $this->getRequestValue('srch_date_from') ? $this->getRequestValue('srch_date_from') : 0;
        $params['srch_date_to'] = $this->getRequestValue('srch_date_to') ? $this->getRequestValue('srch_date_to') : 0;
        $params['admin'] = true;

        //спец.параметр для for_press
        $params['for_press'] = $this->getRequestValue('for_press');

        $exported_template_fields = $this->getRequestValue('template_fields');
        if (is_array($exported_template_fields) && count($exported_template_fields) > 0) {
            $exported_fields = array_keys($exported_template_fields);
        } else {
            $exported_fields = array();
        }
        $_model = $this->data_manager_export->get_model();
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

        $query_count = $this->data_manager_export->get_search_query($params, true);

        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query_count);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
        }
        $cycle_total = $ar['total'];


        for ($i = 0; $i <= $cycle_total; $i += $cycle_per_page) {
            $current_page++;

            $data_a = $this->data_manager_export->grid_array($params, $exported_fields, $this->getRequestValue('per_page'), $current_page);
            $objPHPExcel = new PHPExcel();
            $styleArray = array(
                'font' => array(
                    'bold' => true,
                ),
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                ),
                'borders' => array(
                    'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                    ),
                    'left' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                    ),
                    'right' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                    ),
                    'bottom' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                    ),
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
                    'rotation' => 90,
                    'startcolor' => array(
                        'argb' => 'FFA0A0A0',
                    ),
                    'endcolor' => array(
                        'argb' => 'FFFFFFFF',
                    ),
                ),
            );

            $objPHPExcel->getActiveSheet()->getStyle('A1:AN1')->applyFromArray($styleArray);


            $column = 0;

            foreach ($exported_fields as $ef) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, 1, SiteBill::iconv(SITE_ENCODING, 'utf-8', $_model[$ef]['title']));
                $column++;
            }
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setAutoSize(true);

            $objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AI')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AJ')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AK')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AL')->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension('AM')->setAutoSize(true);

            foreach ($data_a as $item_id => $data_item_a) {
                $row = $item_id + 2;
                $column = 0;
                foreach ($data_item_a as $key => $value) {

                    if (is_array($value)) {
                        if (is_array($value['value_string'])) {
                            $value = implode(',', $value['value_string']);
                        } else {
                            $value = $value['value_string'];
                        }
                    }
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, SiteBill::iconv(SITE_ENCODING, 'utf-8', $value));
                    $column++;
                }
            }

            $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
            $xlsx_file_name = "data" . date('Y-m-d_H_i') . "_page" . $current_page . ".xlsx";
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
    }

    function get_form($form_data = array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php') {
        $rs .= '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/excelfree/css/style.css">';
        $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/excelfree/js/jquery-ui-1.8.16.custom.min.js"></script> ';
        $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/excelfree/js/utils.js"></script> ';
        $rs .= '<div class="file">';
        $rs .= '<div class="import">';
        $rs .= '<div class="input_field" style="height: 106px;">';
        $rs .= '<a href="?action=data&do=import" class="btn btn-success">Импортировать файл Excel</a>';
        //$rs .= Multilanguage::_('L_SELECT_XLS_FILE','excelfree');
        //$rs .= $this->getUploadifyFilePlugin($this->get_session_key());
        $rs .= '</div>';
        $rs .= '</div>';
        $rs .= '<div class="export">';
        $rs .= '<a href="?action=excelfree&do=export_filter">' . Multilanguage::_('L_LOAD_TO_EXCEL', 'excelfree') . '</a><br/><br/>';
        $rs .= '</div>';
        $rs .= '</div>';

        $rs .= '<div class="file">';
        $rs .= '<div id="excel">' . $form_data . '</div>';
        $rs .= '</div>';



        return $rs;
    }

    function remapping($data) {
        $header = $data[1];
        //print_r($data);
        //print_r($header);
        foreach ($data as $item_id => $item_a) {
            if ($item_id > 1) {
                foreach ($item_a as $letter => $value) {

                    $title = SiteBill::iconv('utf-8', SITE_ENCODING, $header[$letter]);

                    $data[$item_id][$title] = SiteBill::iconv('utf-8', SITE_ENCODING, $value);
                    unset($data[$item_id][$letter]);
                    //$key = $this->get_key_by_title($title, $header);
                    /* $key = $this->get_key_by_title_only_model($title);
                      if($key){
                      $data[$item_id][$key] = SiteBill::iconv('utf-8', SITE_ENCODING, $value);
                      }else{
                      $data[$item_id][$title] = SiteBill::iconv('utf-8', SITE_ENCODING, $value);
                      }
                      unset($data[$item_id][$letter]); */
                }
            }
        }
        //print_r($data);
        return $data;
    }

    function template_select($excel_template_id = false) {
        return $rs;
    }

    function xls_parser($excel_template_id = false) {


        $files = $this->load_uploadify_images($this->get_session_key());

        if ($files) {
            $mapper = $this->mapper();
            $data = $this->load_xls($files[0]);

            $assoc_array = ((isset($_POST['assoc_array']) && count($_POST['assoc_array']) > 0) ? $_POST['assoc_array'] : NULL);
            //print_r($assoc_array);
            if ($excel_template_id > 0) {
                $assoc_array = $this->load_template($excel_template_id);
            } elseif ($assoc_array === NULL) {
                $assoc_array = $mapper['data']['fields'];
            } else {
                foreach ($assoc_array as $key => $value) {
                    $assoc_array[$key] = SiteBill::iconv('utf-8', SITE_ENCODING, $value);
                }
            }

            $rs .= $this->template_select($this->getRequestValue('excel_template_id'));



            $rs .= '<table class="table table-hover table-bordered">';
            $rs .= '<tr class="row_head">';
            //print row from model
            foreach ($mapper['data']['fields'] as $item_id => $item) {
                $rs .= '<td class="row_title">' . $item . '</td>';
            }
            $rs .= '</tr>';
            $data = $this->remapping($data);


            $rs .= '<tr class="xls_row_title">';

            //$columns = array();
            //$this->writeLog(__METHOD__.", assoc_array = ".var_export($assoc_array, true));
            //print_r($assoc_array);
            //print_r($data);
            foreach ($assoc_array as $item_id => $item) {
                //foreach ( $data[1] as $letter => $value ) {
                $value = $item;
                //var_dump($value);
                if ($value != '') {

                    //$columns[] = '\''.$value.'\'';
                } elseif ($value = $this->get_key_by_title($item, $data[1])) {
                    //$assoc_array[$item_id] = $value;
                    // $columns[] = '\''.$value.'\'';
                }
                $rs .= '<td class="title">' . $this->select_box($item_id, $data[1], $value) . '</td>';
            }
            $rs .= '</tr>';
            //print_r($assoc_array);
            //$this->writeLog(__METHOD__.", columns = ".var_export($columns, true));


            /*
              $rs .= '<pre>';
              $rs .= var_export($columns, true);
              $rs .= '</pre>';
             */

            unset($data[1]);
            //print_r($data);
            //print_r($assoc_array);
            foreach ($data as $row_id => $data_item) {
                $rs .= '<tr>';
                foreach ($mapper['data']['fields'] as $item_id => $item) {
                    $assoc_key = $assoc_array[$item_id];
                    $data[$row_id][$assoc_key] = mb_substr($data[$row_id][$assoc_key], 0, 20, SITE_ENCODING);
                    $rs .= '<td>' . strip_tags($data[$row_id][$assoc_key]) . '</td>';
                }
                $rs .= '</tr>';
                if ($j++ > 10) {
                    break;
                }
            }

            $rs .= '</tr>';
            $rs .= '</table>';

            /* $rs .= '<script>';
              $rs .= 'var ca = new Array('.implode(',', $columns).')';
              $rs .= '</script>'; */

            return $rs;
            return var_export($data, true);
        }
    }

    function get_key_by_title($title, $header) {
        foreach ($this->data_manager_export->get_model() as $key => $item) {
            //echo 'title = '.$title.', $item[\'title\'] = '.$item['title'].'<br>';
            if ($title == $item['name'] and $item['title'] == $header[$item['title']]) {
                //echo 'return = '.$header[$item['title']].'<br>';
                return $header[$item['title']];
            }
        }
        return false;
    }

    function get_key_by_title_only_model($title) {
        foreach ($this->data_manager_export->get_model(true) as $key => $item) {
            //echo 'title = '.$title.', mtitle = '.$item['title'].'<br>';
            if ($title == $item['title']) {
                return $item['name'];
            }
        }
        return false;
    }

    function run_import() {
        $files = $this->load_uploadify_images($this->get_session_key());
        if ($files) {
            $assoc_array = ((isset($_POST['assoc_array']) && count($_POST['assoc_array']) > 0) ? $_POST['assoc_array'] : NULL);
            if ($assoc_array === NULL) {
                $assoc_array = ((isset($_GET['assoc_array']) && count($_GET['assoc_array']) > 0) ? $_GET['assoc_array'] : NULL);
            }


            //$this->writeLog(__METHOD__.', assoc post = <pre>'.var_export($assoc_array, true).'</pre>');

            $mapper = $this->mapper();

            if ($assoc_array === NULL) {
                $assoc_array = $mapper['data']['fields'];
            } else {
                foreach ($assoc_array as $key => $value) {
                    $assoc_array[$key] = SiteBill::iconv('utf-8', SITE_ENCODING, $value);
                }
            }
            //$this->writeLog(__METHOD__.', assoc a post = <pre>'.var_export($assoc_array, true).'</pre>');

            $data = $this->load_xls($files[0]);
            $data = $this->remapping($data);

            $rs .= $this->sql_exec('data', $data, $mapper, $assoc_array);
            $this->delete_uploadify_image($files[0]);
            return $rs;
        }
    }

    /**
     * Метод выполняет генерацию SQL-запросов и их выполнение
     * В зависимости от того, есть ли значение в таблице с таким ключом, генерируется INSERT или UPDATE запрос
     * Возвращаем результаты выполнения каждого SQL-запроса
     * @param string $table_name - название таблицы
     * @param array $data - данные для загрузки
     * @param array $mapper - ассоциативный массив с маппингом полей
     * @param array $assoc_array - перемаппированый массив, с учетом перестановок
     * @return string
     */
    function sql_exec($table_name, $data, $mapper, $assoc_array) {

        $keys = array_keys($mapper[$table_name]['fields']);
        $primary_key = $mapper[$table_name]['primary_key'];
        unset($data[1]);
        foreach ($data as $data_id => $data_array) {
            $this->data_manager_export->init_request_from_xls($assoc_array, $data_array);
            if ($this->data_manager_export->is_record_exist($data_array, $assoc_array)) {
                $rs .= $this->data_manager_export->edit();
            } else {
                $rs .= $this->data_manager_export->insert();
            }
        }
        return $rs;
    }

    function ajax() {
        if ($this->getRequestValue('action') == 'sql_exec') {
            return $this->run_import();
        } elseif ($this->getRequestValue('do') == 'uploadify') {
            return $this->uploadify();
        } else {
            return $this->xls_parser();
        }
        return false;
    }

    function uploadify() {

        $file_mode = 'excel';
        if (!empty($_FILES)) {

            switch ($this->getConfigValue('uploader_type')) {
                case 'pluploader' : {
                        $file_container_name = 'file';
                        break;
                    }
                default : {
                        $file_container_name = 'Filedata';
                    }
            }
            $tempFile = $_FILES[$file_container_name]['tmp_name'];
            $targetPath = SITEBILL_DOCUMENT_ROOT . '/cache/upl/';

            $path_parts = pathinfo($_FILES[$file_container_name]['name']);

            $ext = $path_parts['extension'];

            if (($_FILES[$file_container_name]['size'] / 1000000) > ( (int) str_replace('M', '', ini_get('upload_max_filesize')) )) {
                //if ( 1 ) {
                echo 'max_file_size';
                return;
            }
            if ($file_mode == 'excel') {
                $avail_ext = array('xls', 'xlsx');
                if (!in_array(strtolower($ext), $avail_ext)) {
                    echo 'wrong_ext';
                    return;
                }
            }
            $i = 1;
            $preview_name_tmp = "jpg_" . uniqid() . '_' . time() . "_" . $i . "." . $ext;
            $targetFile = str_replace('//', '/', $targetPath) . $preview_name_tmp;

            while (file_exists($targetFile)) {
                $i++;
                $preview_name_tmp = "jpg_" . uniqid() . '_' . time() . "_" . $i . "." . $ext;
                $targetFile = str_replace('//', '/', $targetPath) . $preview_name_tmp;
            }
            echo str_replace(SITEBILL_DOCUMENT_ROOT, '', $targetFile);

            move_uploaded_file($tempFile, $targetFile);
        }
        $this->clear_uploadify_table($this->get_session_key(), true);
        $this->addFile($_REQUEST['session'], $preview_name_tmp);
    }

    /**
     * Add file
     * @param string $session_code session code
     * @param string $targetFile target file
     * @return boolean
     */
    function addFile($session_code, $targetFile) {
        $query = "insert into " . UPLOADIFY_TABLE . " (session_code, file_name) values ('$session_code', '$targetFile')";
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        return true;
    }

    function select_box($field_key, $data, $current_value = '') {
        $max_length = 30;
        //$this->writeLog("field_key = $field_key, data = ".var_export($data, true).", current_value = ".$current_value);
        //$this->writeLog("field_key = $field_key, current_value = ".$current_value.", request_value = ".$this->getRequestValue($field_key));
        $rs .= '<select name="' . $field_key . '" id="' . $field_key . '" class="field">';
        $rs .= '<option value="_not_defined">нет соответствия</option>';
        foreach ($data as $key => $value) {
            if (!empty($value)) {
                $selected = '';
                //$this->writeLog("  letter = $letter, key = ".$key);
                if ($current_value == $value) {
                    //$this->writeLog("  case1 ");
                    $selected = 'selected';
                } elseif ($value == $this->getRequestValue($field_key) and ! empty($value)) {
                    //$this->writeLog("  case2 ");
                    $selected = 'selected';
                } else {
                    //$this->writeLog("  case3 ");
                    $selected = '';
                }
                if (strlen($value) > $max_length) {
                    $print_value = substr($value, 0, $max_length) . '...';
                } else {
                    $print_value = $value;
                }
                $rs .= '<option value="' . $value . '" ' . $selected . '>' . $value . '</option>';
            }
        }
        $rs .= '</select>';

        return $rs;
    }

    function mapper() {
        $data_model = $this->data_manager_export->get_model(true);

        $mapper['data']['primary_key'] = 'id';
        foreach ($data_model as $key => $item_a) {
            //$mapper['data']['fields'][$key] = $key;
            $mapper['data']['fields'][$key] = $item_a['title'];
        }

        return $mapper;
    }

    /**
     * Get uploadify plugin
     * @param string $session_code session code
     * @return string
     */
    function getUploadifyFilePlugin( $session_code, $params=array() ) {
        $this->clear_uploadify_table();
        global $folder;

        if ($this->getConfigValue('uploader_type') == 'pluploader') {
            $rs .= '
    	
    		<style type="text/css">@import url(' . $folder . '/apps/system/js/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css);</style>
			<script type="text/javascript" src="' . $folder . '/apps/system/js/plupload/plupload.full.js"></script>
			<script type="text/javascript" src="' . $folder . '/apps/system/js/plupload/jquery.plupload.queue/jquery.plupload.queue.js">
			<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>
			<script>        
		       $(function() {
		       		function log(msg){
		       			 $("#log").append(msg + "\n");
		       		
		       		};
		       		
		       		var del=[];
		       
					$("#html4_uploader").pluploadQueue({
						runtimes : \'html4\',
						multiple_queues: true,
						multi_selection: false,
						url : "' . $folder . '/apps/excelfree/js/ajax.php?do=uploadify&file=excel&session=' . $session_code . '",
						init : {
							FileUploaded: function(up, file, info) {
								if (info.response.indexOf("wrong_ext") != -1){
									file.status = plupload.FAILED;
									up.trigger("UploadProgress", file);
								}else if(info.response.indexOf("max_file_size") != -1){
									file.status = plupload.FAILED;
									up.trigger("UploadProgress", file);
								}else{
									complete_load(\'' . $session_code . '\');
								}
							},
							
						}
					});
				});  
		    </script>  
			<div id="log"></div>
			<div id="html4_uploader">You browser doesnt support simple upload forms. Are you using Lynx?</div>';
        } else {
            $rs = '';
            $rs .= '
			<link href="' . $folder . '/apps/system/js/uploadify/uploadify.css" type="text/css" rel="stylesheet" />
			<script type="text/javascript" src="' . $folder . '/apps/system/js/uploadify/jquery.uploadify.js"></script>
		<script type="text/javascript">
			var uploadedfiles = 0;
			var maxQueueSize = 1;
			var queueSize = 0;
			$(document).ready(function() {
			$(\'#file_upload\').uploadify({
			\'swf\'  : \'' . $folder . '/apps/system/js/uploadify/uploadify.swf\',
			\'uploader\'    : \'' . $folder . '/apps/excelfree/js/ajax.php?do=uploadify&file=excel&session=' . $session_code . '\',
			\'cancelImg\' : \'' . $folder . '/apps/system/js/uploadify/cancel.png\',
			\'folder\'    : \'' . $folder . '/cache/upl\',
			\'auto\'      : true,
			\'fileTypeExts\': \'*.xlsx;*.xls\',
			\'multi\': true,
			\'queueSizeLimit\': 100,
			\'sizeLimit\': 2000000,
			\'buttonText\': \'Файл\',
			\'onUploadSuccess\': function(fileObj, response, data) {
			if ( response == \'max_file_size\' ) {
			alert(\'' . Multilanguage::_('L_MESSAGE_MAX_UPL_SIZE') . ' ' . ini_get('upload_max_filesize') . ' \');
			return false;
		}
		if ( response == \'wrong_ext\' ) {
		alert(\'' . Multilanguage::_('L_MESSAGE_AVIALABLE_EXTS') . ' xls, xlsx\');
		return false;
		}
		addFileNotify(queueSize);
		complete_load(\'' . $session_code . '\');
		
		}
		
		});
		});
		function addFileNotify ( queueSize ) {
		$(\'#filenotify\').html( \'' . Multilanguage::_('L_MESSAGE_FILE_LOADED') . '\' );
		}';

            $rs .= <<<EOF
		
EOF;
            $rs .= '
		</script>
		<input id="file_upload" name="file_upload" type="file" />
		<div id="filenotify"></div>';
        }


        return $rs;
    }

    private static function clearUndecodedXmlEntities($row) {
        foreach ($row as $k => $v) {
            $row[$k] = str_replace('_x000D_', ' ', $v);
        }
        return $row;
    }

    private static function is_empty_xls_row($row) {
        $count = count($row);
        $empty = 0;
        foreach ($row as $k => $v) {
            if ($v == '') {
                $empty++;
            }
        }
        if ($empty == $count) {
            return true;
        }
        return false;
    }

    function load_xls($xls_file) {
        $ret_data = array();
        require_once SITEBILL_APPS_DIR . '/third/phpexcel/PHPExcel/IOFactory.php';
        //require_once SITEBILL_APPS_DIR.'/excelfree/lib/phpexcel/PHPExcel/IOFactory.php';
        $inputFileName = SITEBILL_DOCUMENT_ROOT . '/cache/upl/' . $xls_file;

        $objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
        $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);

        foreach ($sheetData as $k => $v) {
            $v = self::clearUndecodedXmlEntities($v);
            if (!self::is_empty_xls_row($v)) {
                $ret_data[$k] = $v;
            }
        }
        return $ret_data;
    }

}
