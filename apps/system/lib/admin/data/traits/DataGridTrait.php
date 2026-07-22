<?php
/**
 * DataGridTrait — Grid display methods for Data_Manager.
 *
 * Extracted from data_manager.php during refactoring.
 * Methods: grid(), get_data_grid(), create_admin_grid(), get_sitebill_adv_ext_by_model(),
 *          compile_memory_control(), get_memory_header()
 */
trait DataGridTrait
{
    /**
     * Return grid
     */
    function grid($params = array(), $default_params = array()) {
        if ( self::$replace_grid_with_angular ) {
            return $this->angular_grid();
        }

        global $smarty;
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $params[] = 'action=data';

        if ('' != trim($this->getRequestValue('active'))) {
            $params[] = 'active=' . trim($this->getRequestValue('active'));
        }
        if ('' != trim($this->getRequestValue('hot'))) {
            $params[] = 'hot=' . trim($this->getRequestValue('hot'));
        }
        if (0 != intval($this->getRequestValue('status_id'))) {
            $params[] = 'status_id=' . intval($this->getRequestValue('status_id'));
        }
        $current_category_id = $this->getRequestValue('topic_id');
        $smarty->assign('data_category_tree', $Structure_Manager->get_category_tree_control($current_category_id, 0, false, $params));

        $rs = '';
        if(1 == $this->getConfigValue('apps.memorylist.admingridenable')){
            $rs .= $this->get_memory_header();
        }

        $rs .= '<table border="0" width="100%">';
        $rs .= '<tr>';


        if (function_exists('custom_admin_search_fields')) {
            $this->template->assign('custom_admin_search_fields', custom_admin_search_fields($smarty));
        }
        $rs .= '<td style="vertical-align: top;">';
        $rs .= $this->get_data_grid(0, $current_category_id);
        if ($this->getConfigValue('apps.realtylogv2.enable') and $this->getRequestValue($this->getConfigValue('apps.realtylogv2.search_key')) != '' and file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php')) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php');
            $realtylogv2_admin = new realtylogv2_admin();
            $rs .= $realtylogv2_admin->_searchAction();
        }
        $rs .= '</td>';
        $rs .= '<tr>';
        $rs .= '</table>';
        return $rs;
    }

    /**
     * Get data grid
     * @param int $user_id
     * @param int $topic_id
     * @return string
     */
    function get_data_grid($user_id, $topic_id) {
        global $smarty;


        if ($this->getConfigValue('apps.geodata.enable')) {
            $smarty->assign('app_geodata_mode', 1);
        } else {
            $smarty->assign('app_geodata_mode', 0);
        }


        if ( $this->getRequestValue('do') != 'edit_done' and $this->getRequestValue('do') != 'new_done' ) {
            $params = $this->gatherRequestParams();
        }
        if (isset($this->data_model[$this->table_name]['uniq_id'])) {
            $smarty->assign('show_uniq_id', 'true');
        }
        $params['admin'] = true;
        $params['action'] = 'data';
        $params['_collect_user_info'] = 1;

        $share_and_permission = false;
        if ((1 === (int) $this->getConfigValue('check_permissions')) && (@$_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
            $params['user_id'] = (int) $_SESSION['user_id_value'];
            $share_and_permission = true;
        }
        /*
         * А теперь проверим что у нас включено условие data_adv_share_access и включено data_adv_share_access_can_view_all
         * Чтобы пользователю можно было смотреть чужие записи без права редактирования или удаления чужих
         */
        if ($share_and_permission and $this->getConfigValue('data_adv_share_access_can_view_all')) {
            $this->template->assign('data_adv_share_access_can_view_all', 1);
            $this->template->assign('data_adv_share_access_user_id', $params['user_id']);
            unset($params['user_id']);
        }

        if(0 != intval($this->getRequestValue('memorylist_id'))){
            $params['memorylist_id'] = intval($this->getRequestValue('memorylist_id'));
        }

        /*
          /* @TODO	 Удалить этот блок после удовлетворительного тестирования и перенести все в грид конструктор
         */

        $params = $this->gatherParamsFromSconfig($params);

        if (1 == $this->getConfigValue('use_new_realty_grid')) {
            $this->create_admin_grid($params);
        } else {
            $grid_constructor = $this->_getGridConstructor();
            $grid_constructor->main($params);
        }

        $smarty->assign('admin', 1);
        if (isset($params['topic_id'])) {
            $smarty->assign('topic_id', $params['topic_id']);
        } else {
            $smarty->assign('topic_id', 0);
        }

        if ($this->getConfigValue('apps.fasteditor.enable')) {
            $smarty->assign('sms_enable', 'true');
        }
        if ($this->getConfigValue('apps.realtypro.show_contact.enable')) {
            $smarty->assign('show_contacts_enable', 'true');
        }
        if ($this->getConfigValue('show_up_icon') == 1) {
            $smarty->assign('show_up_icon', 'true');
        }
        if (intval($this->getConfigValue('admin_grid_leftbuttons')) === 1) {
            $smarty->assign('admin_grid_leftbuttons', 1);
        } else {
            $smarty->assign('admin_grid_leftbuttons', 0);
        }

        if ( 1 == $this->getConfigValue('apps.billing.enable_in_admin') ) {
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/billing/admin/admin.php';
            $billing = new billing_admin();
            $billing_plugin = $billing->billing_plugin();
            $this->template->assign('billing_controls_tpl', $billing->billing_controls_tpl());
        }

        if (1 == $this->getConfigValue('use_new_realty_grid')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php';
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/gridmanager_admin.php';
            $GMA = new gridmanager_admin();
            $smarty->assign('grid_data_columns', $GMA->getGridColumns());
            if (file_exists(SITEBILL_DOCUMENT_ROOT . "/template/frontend/" . $this->getConfigValue('theme') . "/apps/admin/template/realty_grid_wdg.tpl")) {
                $html = @$smarty->fetch(SITEBILL_DOCUMENT_ROOT . "/template/frontend/" . $this->getConfigValue('theme') . "/apps/admin/template/realty_grid_wdg.tpl");
            } else {
                $html = @$smarty->fetch($this->get_smarty_template_dir() . "/realty_grid_wdg.tpl");
            }
        } else {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . "/template/frontend/" . $this->getConfigValue('theme') . "/apps/admin/template/realty_grid.tpl")) {
                $html = @$smarty->fetch(SITEBILL_DOCUMENT_ROOT . "/template/frontend/" . $this->getConfigValue('theme') . "/apps/admin/template/realty_grid.tpl");
            } else {
                $html = @$smarty->fetch($this->get_smarty_template_dir() . "/realty_grid.tpl");
            }
        }
        if ( isset($billing_plugin) and $billing_plugin != '' ) {
            $html .= $billing_plugin;
        }
        return $html;
    }

    function create_admin_grid($params) {
        $grid_constructor = $this->_getGridConstructor();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();

        $res = $this->get_sitebill_adv_ext_by_model($params);
        $this->template->assign('category_tree', $grid_constructor->get_category_tree($params, $category_structure));
        $this->template->assign('breadcrumbs', $grid_constructor->get_category_breadcrumbs($params, $category_structure, SITEBILL_MAIN_URL));
        $this->template->assign('search_params', json_encode($params));
        $this->template->assign('search_url', $_SERVER['REQUEST_URI']);

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');

        $data_model = new Data_Model();
        $_model = $data_model->get_kvartira_model(false, true);
        $_model = $_model['data'];
        foreach ($_model as $k => $item_array) {
            $rules = array();
            if (isset($item_array['parameters']['rules']) && $item_array['parameters']['rules'] != '') {
                $rules_string = $item_array['parameters']['rules'];

                $rules_parts = explode(',', $rules_string);
                foreach ($rules_parts as $r => $rp) {
                    $rules_parts[$r] = trim($rp);
                }


                foreach ($rules_parts as $rp) {
                    $x = explode(':', $rp);
                    $rules[trim($x[0])] = (isset($x[1]) ? trim($x[1]) : '');
                }

                if (!isset($rules['Type'])) {
                    $rules['Type'] = 'string';
                }
            }
            $_model[$k]['_rules'] = $rules;
        }


        foreach ($res as $k => $v) {
            $res[$k] = $data_model->applyGCompose($res[$k]);
            $res[$k] = SiteBill::modelSimplification($res[$k]);
            $res[$k]['_href'] = $this->getRealtyHREF($res[$k]['id']['value']);
        }

        if(isset($_model['topic_id'])){
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
            $Structure_Manager = new Structure_Manager();
            $ch = $Structure_Manager->createCatalogChains();
            $category_structure = $Structure_Manager->loadCategoryStructure();

            foreach ($res as $k => $v) {
                $tid = $v['topic_id']['value'];
                if(isset($ch['ar'][$tid]) && count($ch['ar'][$tid]) > 1){
                    $vars = $ch['ar'][$tid];
                    array_pop($vars);
                    $nms = array();
                    foreach($vars as $idt){
                        $nms[] = $category_structure['catalog'][$idt]['name'];
                    }
                    $res[$k]['topic_id']['_hint'] = implode(', ', $nms);
                }

            }
        }


        //print_r($category_structure['catalog']);

        $this->template->assign('core_model', $_model);

        if (1 == intval($this->getConfigValue('use_topic_actual_days'))) {
            $topic_actuals = array();
            $DBC = DBC::getInstance();
            $query = 'SELECT id, actual_days FROM ' . DB_PREFIX . '_topic';
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $topic_actuals[$ar['id']] = $ar['actual_days'];
                }
            }
            foreach ($res as $k => $v) {
                $actual_adv_days = floor((time() - strtotime($v['date_added']['value'])) / (24 * 3600));
                if (isset($topic_actuals[$v['topic_id']['value']]) && intval($topic_actuals[$v['topic_id']['value']]) > 0 && $actual_adv_days > $topic_actuals[$v['topic_id']['value']]) {
                    $res[$k]['_classes'] = 'actuality_expired';
                }
            }
        }
        if(1 == $this->getConfigValue('apps.memorylist.admingridenable')){
            foreach ($res as $k => $v) {
                $res[$k]['_memo'] = $this->compile_memory_control($v['id']['value']);
            }
        }



        $grid_constructor->get_sales_grid($res);
    }



    private function compile_memory_control($id) {
        $this->template->assign('id', $id);
        return $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/admin/template/memorylist_item_control.tpl');
    }

    private function get_memory_header() {
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/admin/memory_list.php';
        $ML = new Memory_List();

        $memory_lists = $ML->getUserMemoryLists($_SESSION['user_id']);
        foreach ($memory_lists as $ml) {
            if (isset($ml['items']) && count($ml['items']) > 0) {
                foreach ($ml['items'] as $item) {
                    $items_in_memory[$item['id']][] = $ml;
                }
            }
        }

        $this->template->assign('items_in_memory', $items_in_memory);
        return $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/admin/template/memorylist_header.tpl');
    }

    /**
     * MUST be moved to Grid_Constructor
     */
    function get_sitebill_adv_ext_by_model($params, $random = false) {
        $params['_sortmodel'] = 1;
        $grid_constructor = $this->_getGridConstructor();
        $data = $grid_constructor->get_sitebill_adv_core($params);
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');

        $data_model = new Data_Model();
        $this->template->assert('pager_array', $data['paging']);
        $this->template->assert('pager', $data['pager']);
        $this->template->assert('pagerurl', $data['pagerurl']);
        $this->template->assert('url', $data['url']);
        $this->template->assert('_total_records', $data['_total_records']);
        $this->template->assert('_max_page', $data['_max_page']);
        $this->template->assert('_params', $data['_params']);
        $_model = $data_model->get_kvartira_model();

        $ret = array();

        $ids = array();
        foreach ($data['data'] as $r) {
            $ids[] = $r['id'];
        }

        if (!empty($ids)) {
            $rets = $data_model->init_model_data_from_db_multi('data', 'id', $ids, $_model['data'], true);

            $i = 0;
            foreach ($rets as $k => $r) {
                //$ret[$i] = SiteBill::modelSimplification($r);
                $ret[$i] = $r;
                //$ret[$i]['_href'] = $this->getRealtyHREF($r['id']['value']);
                $i++;
            }
        }



        return $ret;
    }
}
