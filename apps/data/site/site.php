<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * data fronend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class data_site extends data_admin {

    function __construct() {
        parent::__construct();
    }

    function frontend() {
        return false;
    }

    function main() {
        $uid = $this->getSessionUserId();
        if ($uid == 0 or ! isset($uid)) {
            $rs = Multilanguage::_('L_ACCESS_DENIED');
            return $rs;
        }
        return parent::main();
    }

    protected function _formatgridAction() {

        global $smarty;
        $DBC = DBC::getInstance();
        $action = $this->action . '_user_' . $this->getSessionUserId();
        if ('post' === strtolower($_SERVER['REQUEST_METHOD'])) {
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

    protected function _getpdfAction() {

        $default_params['grid_item'] = array('id', 'topic_id', 'city_id', 'district_id', 'street_id', 'price', 'image');
        $REQUESTURIPATH = Sitebill::getClearRequestURI();
        if (!preg_match('/all[\/]?$/', $REQUESTURIPATH)) {
            $params['grid_conditions']['user_id'] = $this->getSessionUserId();
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);
        $common_grid->set_action($this->action);
        $common_grid->set_grid_table($this->table_name);
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

        if (isset($params['grid_conditions']) && count($params['grid_conditions']) > 0) {
            $common_grid->set_conditions($params['grid_conditions']);
        }

        //$common_grid->set_grid_query('SELECT * FROM '.DB_PREFIX.'_'.$this->table_name.' ORDER BY name ASC');


        $common_grid->setPagerParams(array('action' => $this->action, 'page' => $this->getRequestValue('page'), 'per_page' => $this->getConfigValue('common_per_page')));

        $rs = $common_grid->extended_items();
        //$common_grid->construct_query();
        $common_grid->construct_grid();
        $grid_array = $common_grid->construct_grid_array();

        //echo '<pre>';
        //print_r($this->data_model);
        //echo '</pre>';
        //exit;
        //echo '<pre>';
        //print_r($default_params['grid_item']);
        //echo '</pre>';

        $this->template->assign('header_items', $default_params['grid_item']);
        $this->template->assign('data_model', $this->data_model);

        $grid_constructor = $this->_getGridConstructor();
        $grid_array_transformed = @$grid_constructor->transformGridData($grid_array);
        //echo '<pre>';
        //print_r($grid_array);
        //echo '</pre>';
        //exit;

        $this->createPDF($grid_array, $grid_array_transformed, intval($this->getRequestValue('ext')));

        exit();
    }

    /**
     * Get top menu
     * @param void 
     * @return string
     */
    function getTopMenu() {
        $rs = '';
        $rs .= '<a href="?action=' . $this->action . '&do=new" class="btn btn-primary">' . Multilanguage::_('L_ADD_RECORD_BUTTON') . '</a> ';
        $rs .= '<a href="' . SITEBILL_MAIN_URL . '/account/data/all" class="btn btn-primary">' . Multilanguage::_('L_ALL') . '</a> ';
        $rs .= '<a href="' . SITEBILL_MAIN_URL . '/memorylist/" class="btn btn-primary">Сохраненные списки</a> ';
        //$rs .= '</div>';
        //$rs .= '<form method="post"><input type="hidden" name="action" value="add" /><input type="submit" name="submit" value="Добавить объявление" /></form>';
        return $rs;
    }

    public function createPDF($data, $grid_array_transformed) {
        global $smarty;

        $smarty->assign('grid_items', $data);
        $smarty->assign('grid_array_transformed', $grid_array_transformed);

        $this->template->assign('_core_folder', SITEBILL_DOCUMENT_ROOT);
        $pdf_file_storage = SITEBILL_DOCUMENT_ROOT . '/cache/';

        require_once(SITEBILL_DOCUMENT_ROOT . "/apps/pdfreport/lib/dompdf/dompdf_config.inc.php");

        if ($this->getConfigValue('apps.pdfreport.custom_templates')) {
            require_once(SITEBILL_DOCUMENT_ROOT . "/apps/pdfreport/admin/admin.php");
            $pdfreport_admin = new pdfreport_admin();

            $header_template = $pdfreport_admin->load_template('header');
            if ($header_template) {
                $header_string = $smarty->fetch('string:' . $header_template);
                $smarty->assign('header', $header_string);
            } else {
                $smarty->assign('header', '');
            }

            $greatings_template = $pdfreport_admin->load_template('greatings');
            if ($greatings_template) {
                $greatings_string = $smarty->fetch('string:' . $greatings_template);
                $smarty->assign('greatings', $greatings_string);
            } else {
                $smarty->assign('greatings', '');
            }

            $garanties_template = $pdfreport_admin->load_template('garanties');
            if ($garanties_template) {
                $garanties_string = $smarty->fetch('string:' . $garanties_template);
                $smarty->assign('garanties', $garanties_string);
            } else {
                $smarty->assign('garanties', '');
            }

            $mortgage_template = $pdfreport_admin->load_template('mortgage');
            if ($mortgage_template) {
                $mortgage_string = $smarty->fetch('string:' . $mortgage_template);
                $smarty->assign('mortgage', $mortgage_string);
            } else {
                $smarty->assign('mortgage', '');
            }

            $gifts_template = $pdfreport_admin->load_template('gifts');
            if ($gifts_template) {
                $gifts_string = $smarty->fetch('string:' . $gifts_template);
                $smarty->assign('gifts', $gifts_string);
            } else {
                $smarty->assign('gifts', '');
            }

            $footer_template = $pdfreport_admin->load_template('footer');
            if ($footer_template) {
                $footer_string = $smarty->fetch('string:' . $footer_template);
                $smarty->assign('footer', $footer_string);
            } else {
                $smarty->assign('footer', '');
            }

            $objects_template = $pdfreport_admin->load_template('objects');
            if ($objects_template) {
                $objects_string = $smarty->fetch('string:' . $objects_template);
                $smarty->assign('objects', $objects_string);
            } else {
                $smarty->assign('objects', '');
            }


            //Собираем весь документ
            $main_template = $pdfreport_admin->load_template('main');
            $html = $smarty->fetch('string:' . $main_template);
        } else {

            $tplfile = 'data_grid.tpl';

            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/pdfreport/' . $tplfile)) {
                $html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/pdfreport/' . $tplfile);
            } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/pdfreport/admin/template/' . $tplfile)) {
                $html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/pdfreport/admin/template/' . $tplfile);
            } else {
                $html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/pdfreport/admin/template/data_grid.tpl');
            }
        }

        $dompdf = new DOMPDF();
        $dompdf->set_paper('A4', 'landscape');
        $dompdf->load_html($html);
        $dompdf->render();

        $output = $dompdf->output();
        header("Content-type: application/pdf");
        echo $output;
        exit();
    }

}
