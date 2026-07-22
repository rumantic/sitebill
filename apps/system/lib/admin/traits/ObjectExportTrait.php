<?php
/**
 * ObjectExportTrait — Excel/PDF export and import methods extracted from Object_Manager.
 *
 * Methods: _exportAction, public_export, num2alpha, _pdfreportAction, _importAction
 *
 * @see Object_Manager
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

trait ObjectExportTrait
{
    public function public_export($input_params = array())
    {
        return $this->_exportAction($input_params);
    }

    protected function _exportAction($input_params = array())
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);
        $common_grid->set_action($this->action);
        $common_grid->set_grid_table($this->table_name);

        $_model = $this->data_model[$this->table_name];
        if (is_array($_model)) {
            $params['grid_item'] = array_keys($_model);
        }

        if (isset($params['grid_item']) && @count($params['grid_item']) > 0) {
            foreach ($params['grid_item'] as $grid_item) {
                $common_grid->add_grid_item($grid_item);
            }
        } else {
            $common_grid->add_grid_item($this->primary_key);
            $common_grid->add_grid_item('name');
        }

        if (isset($params['grid_controls']) && @count($params['grid_controls']) > 0) {
            foreach ($params['grid_controls'] as $grid_item) {
                $common_grid->add_grid_control($grid_item);
            }
        } else {
            $common_grid->add_grid_control('edit');
            $common_grid->add_grid_control('delete');
        }

        if (isset($input_params['grid_conditions']) && @count($input_params['grid_conditions']) > 0) {
            $common_grid->set_conditions($input_params['grid_conditions']);
        }
        if (isset($params['grid_conditions_sql']) && @count($params['grid_conditions_sql']) > 0) {
            $common_grid->set_conditions_sql($params['grid_conditions_sql']);
        }

        if ($input_params['per_page'] != '') {
            $per_page = $input_params['per_page'];
        } else {
            $per_page = 99999;
        }
        $common_grid->setPagerParams(array('action' => $this->action, 'page' => 1, 'per_page' => $per_page));

        $common_grid->construct_grid();

        $exported_template_fields = $this->getRequestValue('template_fields');
        if (is_array($exported_template_fields) && @count($exported_template_fields) > 0) {
            $exported_fields = array_keys($exported_template_fields);
        } else {
            if (is_array($_model)) {
                $exported_fields = array_keys($_model);
            }
        }
        if (is_array($exported_fields) and in_array('tlocation', $exported_fields)) {
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
        $cycle_total = 1;

        for ($i = 0; $i <= $cycle_total; $i += $cycle_per_page) {
            $current_page++;

            $data_a = $common_grid->construct_grid_array();

            $objPHPExcel = new Spreadsheet();
            $styleArray = array(
                'font' => array('bold' => true),
                'alignment' => array('horizontal' => Alignment::HORIZONTAL_LEFT),
                'borders' => array(
                    'bottom' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '808080')
                    ),
                ),
                'fill' => array(
                    'fillType' => Fill::FILL_SOLID,
                    'rotation' => 90,
                    'color' => array('rgb' => 'c5c5c5'),
                ),
            );

            $last_letter = $this->num2alpha(@count($exported_fields) - 1);

            try {
                $objPHPExcel->getActiveSheet()->getStyle('A1:' . $last_letter . '1')->applyFromArray($styleArray);
            } catch (Exception $e) {
                echo $e->getMessage();
                exit;
            }

            $column = 1;
            foreach ($exported_fields as $ef) {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, 1, SiteBill::iconv(SITE_ENCODING, 'utf-8', $_model[$ef]['title']));
                $objPHPExcel->getActiveSheet()->getColumnDimension($this->num2alpha($column))->setAutoSize(true);
                $column++;
            }

            foreach ($data_a as $item_id => $data_item_a) {
                $row = $item_id + 2;
                $column = 1;
                foreach ($data_item_a as $key => $value) {
                    if (is_array($value)) {
                        if ($data_item_a[$key]['type'] == 'select_by_query_multi' && is_array($value['value_string'])) {
                            $value = implode(',', $value['value_string']);
                        } else {
                            $value = $value['value_string'];
                        }
                    }
                    if (!empty($value) and !is_scalar($value)) {
                        $value = 'array!';
                    }
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($column, $row, SiteBill::iconv(SITE_ENCODING, 'utf-8', $value));
                    $column++;
                }
            }

            $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($objPHPExcel);
            $xlsx_file_name = str_replace(':', '_', $this->action) . "_page" . $current_page . ".xlsx";
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
                $rs = '<a href="' . SITEBILL_MAIN_URL . '/cache/upl/' . $xlsx_file_name . '" download="' . $xlsx_file_name . '">' . $xlsx_file_name . '</a><br>';
            }
        }

        return '<h3>Скачать готовые файлы</h3><br/>' . $rs . '';
        exit;
    }

    function _pdfreportAction()
    {
        $tplfile = 'pdf_item.tpl';

        $local_template = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $this->action . '/admin/template/' . $tplfile;
        $apps_template = SITEBILL_DOCUMENT_ROOT . '/apps/' . $this->action . '/admin/template/' . $tplfile;
        $global_template = SITEBILL_DOCUMENT_ROOT . '/apps/system/template/grid/' . $tplfile;

        if (file_exists($local_template)) {
            $tpl = $local_template;
        } elseif (file_exists($apps_template)) {
            $tpl = $apps_template;
        } else {
            $tpl = $global_template;
        }

        $items = $this->load_by_id($this->getRequestValue($this->primary_key));
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/view.php');
        $table_view = new Table_View();
        $this->template->assign('tr_rendered', $table_view->compile_view($items));
        $this->template->assign('_core_folder', SITEBILL_DOCUMENT_ROOT);

        $html = $this->template->fetch($tpl);

        $dompdfoptions = new Options();
        $dompdfoptions->set('isRemoteEnabled', TRUE);
        $dompdf = new \Dompdf\Dompdf($dompdfoptions);
        $dompdf->loadHtml($html);
        $dompdf->render();
        $output = $dompdf->output();
        header("Content-type: application/pdf");
        echo $output;
        exit();
    }

    protected function _importAction()
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/components/dropzone_xls/dropzone.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $dropzone = new DropZone();
        $dropzone->set_context($this);
        $this->template->assign('uploads_item', $dropzone->compile_uploads_element([]));

        return $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/system/template/object/import_form.tpl');
    }

    function num2alpha($n)
    {
        for ($r = ""; $n >= 0; $n = intval($n / 26) - 1)
            $r = chr($n % 26 + 0x41) . $r;
        return $r;
    }
}
