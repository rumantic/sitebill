<?php

/**
 * Data manager
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class Data_Manager extends Object_Manager {

    protected $billing_mode_on = false;
    protected $data_model_object;
    public $notwatermarked_folder = SITEBILL_DOCUMENT_ROOT.'/img/nwtm/';
    private $nowatermark_folder_with_id = false;
    /**
     * Constructor
     */
    function Data_Manager() {
        $this->SiteBill();
        $this->table_name = 'data';
        $this->action = 'data';
        $this->app_title = Multilanguage::_('DATA_APP_NAME', 'system');
        $this->primary_key = 'id';
        $this->update_table();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $this->data_model_object = $data_model;
        $this->data_model = $data_model->get_kvartira_model($this->getConfigValue('ajax_form_in_admin'));


        if ($this->getConfigValue('theme') == 'albostar') {
            $this->data_model['data']['date_added']['type'] = 'safe_string';
        }

        $this->data_model['data']['user_id']['name'] = 'user_id';
        $this->data_model['data']['user_id']['primary_key_name'] = 'user_id';
        $this->data_model['data']['user_id']['primary_key_table'] = 'user';
        $this->data_model['data']['user_id']['title'] = Multilanguage::_('USER');
        $this->data_model['data']['user_id']['value_string'] = '';
        $this->data_model['data']['user_id']['value'] = 0;
        $this->data_model['data']['user_id']['length'] = 40;
        $this->data_model['data']['user_id']['type'] = 'select_by_query';
        if ($this->getConfigValue('theme') == 'ipn') {
            $this->data_model['data']['user_id']['query'] = 'select * from ' . DB_PREFIX . '_user  where group_id <> 3 order by fio';
        } elseif ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access')) and $this->getConfigValue('data_adv_share_access_extended') != '') {
            $extended_user_list = array();
            $extended_user_list = explode(',', $this->getConfigValue('data_adv_share_access_extended'));
            array_push($extended_user_list, (int) $_SESSION['user_id_value']);
            $this->data_model['data']['user_id']['query'] = 'select * from ' . DB_PREFIX . '_user  where user_id in (' . implode(',', $extended_user_list) . ') order by fio';
        } elseif ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access')) and (int) $this->getConfigValue('data_adv_share_access_user_list_strict') == 1) {
            $this->data_model['data']['user_id']['query'] = 'select * from ' . DB_PREFIX . '_user  where user_id = ' . (int) $_SESSION['user_id_value'] . ' order by fio';
        } else {
            $this->data_model['data']['user_id']['query'] = 'select * from ' . DB_PREFIX . '_user order by fio';
        }
        /* $this->data_model['data']['user_id']['value_name'] = 'fio';
          $this->data_model['data']['user_id']['title_default'] = Multilanguage::_('L_CHOOSE_USER'); */
        $this->data_model['data']['user_id']['value_default'] = 0;
        $this->data_model['data']['user_id']['required'] = 'on';
        $this->data_model['data']['user_id']['unique'] = 'off';

        /* var_dump($_SESSION['user_id_value']);
          if((1===(int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name']!=='admin') && (1===(int)$this->getConfigValue('data_adv_share_access'))){
          $user_id=(int)$_SESSION['user_id_value'];

          //$this->setRequestValue('user_id', $user_id);
          $this->data_model['data']['user_id']['value'] = $user_id;
          }else{
          $this->data_model['data']['user_id']['value'] = $this->getAdminUserId();
          } */
        $user_id = 0;
        if (isset($_SESSION['user_id_value'])) {
            $user_id = (int) $_SESSION['user_id_value'];
        }

        $this->data_model['data']['user_id']['value'] = $user_id;

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable') == 1) {
            $this->billing_mode_on = true;
        }
        if ($this->getConfigValue('dadata_autocomplete_force')) {
            $this->data_model['data'] = $this->prepare_model_for_dadata($this->data_model['data']);
        }
    }

    function get_model() {
        return $this->data_model;
    }

    function structure_processor() {
        if ($this->getRequestValue('subdo') == 'sms') {
            $form_data = $this->load_by_id($this->getRequestValue('id'));
            if ($form_data['tmp_password']['value'] == '') {
                $form_data['tmp_password']['value'] = substr(md5(time()), 1, 6);

                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
                $data_model = new Data_Model();
                $DBC = DBC::getInstance();
                $queryp = $data_model->get_prepared_edit_query(DB_PREFIX . '_' . $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data);
                $DBC->query($queryp['q'], $queryp['p']);
            }
            $body = $this->getConfigValue('apps.fasteditor.sms_send_password_text');
            $body = str_replace('{password}', $form_data['tmp_password']['value'], $body);
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/sms/admin/admin.php');
            $SMSSender = new sms_admin();
            if ($SMSSender->send($form_data['phone']['value'], $body)) {
                $rs = Multilanguage::_('MESSAGE_SUCCESS_NOTIFICATION', 'system') . ' ' . $body;
            } else {
                $rs = Multilanguage::_('MESSAGE_ERROR_NOTIFICATION', 'system');
            }

            return $rs;
        }
    }

    function update_table() {
        return;
    }

    /**
     * Get count
     */
    function get_count($active) {
        $DBC = DBC::getInstance();
        if ($active == 'vip') {
            $query = 'SELECT COUNT(id) AS total FROM ' . DB_PREFIX . '_data WHERE vip_status_end<>0 AND ' . DB_PREFIX . '_data.vip_status_end >= \'' . time() . '\'';
        } elseif ($active == 'premium') {
            $query = 'SELECT COUNT(id) AS total FROM ' . DB_PREFIX . '_data WHERE premium_status_end<>0 AND ' . DB_PREFIX . '_data.premium_status_end >= \'' . time() . '\'';
        } elseif ($active == 'bold') {
            $query = 'SELECT COUNT(id) AS total FROM ' . DB_PREFIX . '_data WHERE bold_status_end<>0 AND ' . DB_PREFIX . '_data.bold_status_end >= \'' . time() . '\'';
        } elseif ($active == 'all') {
            $query = "select count(id) as total from " . DB_PREFIX . "_data";
        } elseif ($active == 'notactive') {
            if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting')) {
                $query = "select count(id) as total from " . DB_PREFIX . "_data where active=0 AND archived=0";
            }else{
                $query = "select count(id) as total from " . DB_PREFIX . "_data where active=0";
            }
        } elseif ($active == 'hot') {
            $query = "select count(id) as total from " . DB_PREFIX . "_data where hot=1";
        } elseif ($active == 'free') {
            $query = "select count(id) as total from " . DB_PREFIX . "_data where status_id='free'";
        } elseif ($active == 'no_answer') {
            $query = "select count(id) as total from " . DB_PREFIX . "_data where status_id='no_answer'";
        } elseif ($active == 'call') {
            $query = "select count(id) as total from " . DB_PREFIX . "_data where status_id='call'";
        } elseif ($active == 'actual') {
            $query = "select count(id) as total from " . DB_PREFIX . "_data where status_id='actual'";
        } elseif ($active == 'archived') {
            $query = "select count(id) as total from " . DB_PREFIX . "_data where archived=1";
        } else {
            if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting')) {
                $query = "select count(id) as total from " . DB_PREFIX . "_data where active=1 AND archived=0";
            }else{
                $query = "select count(id) as total from " . DB_PREFIX . "_data where active=1";
            }

        }

        $result = $this->get_query_cache_value($query, array());
        if ( $result['result'] === true ) {
            return $result['value'];
        }

        $stmt = $DBC->query($query);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $this->insert_query_cache_value($query, array(), $ar['total']);
            return $ar['total'];
        }
        return 0;
    }

    /**
     * Get top menu
     * @param void
     * @return string
     */
    function getTopMenu() {
        global $smarty;
        if ($this->billing_mode_on) {
            $smarty->assign('billing_mode_on', 1);
        }
        if (isset($this->data_model['data']['status_id'])) {
            $smarty->assign('free_count', $this->get_count('free'));
            $smarty->assign('no_answer_count', $this->get_count('no_answer'));
            $smarty->assign('call_count', $this->get_count('call'));
            $smarty->assign('actual_count', $this->get_count('actual'));
        }

        if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting') && isset($this->data_model['data']['archived'])) {
            $smarty->assign('archived_count', $this->get_count('archived'));
        }

        if (file_exists(SITEBILL_DOCUMENT_ROOT . "/template/frontend/" . $this->getConfigValue('theme') . "/apps/admin/template/data_top_menu.tpl.html")) {
            $tpl = SITEBILL_DOCUMENT_ROOT . "/template/frontend/" . $this->getConfigValue('theme') . "/apps/admin/template/data_top_menu.tpl.html";
        } elseif (file_exists($this->getAdminTplFolder() . '/data_top_menu.tpl.html')) {
            $tpl = $this->getAdminTplFolder() . '/data_top_menu.tpl.html';
        } else {
            $tpl = '';
        }


        if ($tpl != '') {
            $smarty->assign('user_select_box', $this->getUserSelectBox());
            $smarty->assign('active_items_count', $this->get_count(1));
            $smarty->assign('notactive_items_count', $this->get_count('notactive'));
            if ($this->billing_mode_on) {
                $billing_mode_on_counts = array();
                $billing_mode_on_counts['vip'] = $this->get_count('vip');
                $billing_mode_on_counts['premium'] = $this->get_count('premium');
                $billing_mode_on_counts['bold'] = $this->get_count('bold');
                $smarty->assign('billing_mode_on_counts', $billing_mode_on_counts);
                $billing_mode_on_statuses['vip'] = (int) $this->getRequestValue('vip_status');

                $billing_mode_on_statuses['premium'] = (int) $this->getRequestValue('premium_status');

                $billing_mode_on_statuses['bold'] = (int) $this->getRequestValue('bold_status');
                $smarty->assign('billing_mode_on_statuses', $billing_mode_on_statuses);
            } else {
                $smarty->assign('hot_items_count', $this->get_count('hot'));
            }

            $smarty->assign('all_items_count', $this->get_count('all'));


            $smarty->assign('active', $this->getRequestValue('active'));
            $smarty->assign('hot', $this->getRequestValue('hot'));

            $rs = $smarty->fetch($tpl);
        } else {
            $rs = '';
            $rs .= '<table border="0">';
            $rs .= '<tr>';
            $rs .= '<td>';
            $rs .= '<a href="?action=' . $this->action . '&do=new" class="btn btn-success">' . Multilanguage::_('L_ADD_RECORD_BUTTON') . '</a>';
            $rs .= '</td>';
            $rs .= '<td>';
            $rs .= '&nbsp;&nbsp;&nbsp;&nbsp;';
            if ($this->getRequestValue('active') == 1) {
                $rs .= '<b>' . Multilanguage::_('ACTIVE_ITEMS', 'system') . ' (' . $this->get_count(1) . ')</b> | ';
            } else {
                $rs .= '<a href="?action=' . $this->action . '&active=1">' . Multilanguage::_('ACTIVE_ITEMS', 'system') . ' (' . $this->get_count(1) . ')</a> | ';
            }
            if ($this->getRequestValue('active') == 'notactive') {
                $rs .= '<b>' . Multilanguage::_('NOTACTIVE_ITEMS', 'system') . ' (' . $this->get_count('notactive') . ')</b> | ';
            } else {
                $rs .= '<a href="?action=' . $this->action . '&active=notactive">' . Multilanguage::_('NOTACTIVE_ITEMS', 'system') . ' (' . $this->get_count('notactive') . ')</a> | ';
            }
            if ($this->getRequestValue('hot') == 1) {
                $rs .= '<b>' . ($this->getConfigValue('theme') == 'albostar' ? Multilanguage::_('EDITED_ITEMS', 'system') : Multilanguage::_('HOT_ITEMS', 'system')) . ' (' . $this->get_count('hot') . ')</b> | ';
            } else {
                $rs .= '<a href="?action=' . $this->action . '&hot=1">' . ($this->getConfigValue('theme') == 'albostar' ? Multilanguage::_('EDITED_ITEMS', 'system') : Multilanguage::_('HOT_ITEMS', 'system')) . ' (' . $this->get_count('hot') . ')</a> | ';
            }

            if ($this->getRequestValue('active') == '' AND $this->getRequestValue('hot') != 1) {
                $rs .= '<b>Все (' . $this->get_count('all') . ')</b>  ';
            } else {
                $rs .= '<a href="?action=' . $this->action . '">Все (' . $this->get_count('all') . ')</a>  ';
            }

            $rs .= '</td>';
            $rs .= '<td>';
            $rs .= '' . $this->getAdditionalSearchForm();
            $rs .= '</td>';
            $rs .= '</tr>';
            $rs .= '</table>';
        }
        return $rs;
    }

    protected function checkOwning($id, $user_id) {
        $DBC = DBC::getInstance();
        $query = 'SELECT COUNT(`' . $this->primary_key . '`) AS _cnt FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '`=? AND `user_id`=?';
        $stmt = $DBC->query($query, array($id, $user_id));
        $res = false;
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ((int) $ar['_cnt'] === 1) {
                $res = true;
            }
        }
        return $res;
    }

    protected function _batch_field_editAction() {
        $field = $this->getRequestValue('field');
        if (!isset($this->data_model[$this->table_name][$field]) || $this->data_model[$this->table_name][$field]['type'] != 'price') {
            return '';
        }
        $ids = $this->getRequestValue('id');
        if (!is_array($ids) || empty($ids)) {
            return '';
        }
        if ($_SESSION['current_user_group_name'] != 'admin') {
            foreach ($ids as $k => $id) {
                if (!$this->checkOwning($id, $_SESSION['user_id'])) {
                    unset($ids[$k]);
                }
            }
        }

        if (empty($ids)) {
            return '';
        }

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {

            if (!isset($_POST['step']) || empty($_POST['step'])) {
                return '---';
            }

            $vals = array();
            $DBC = DBC::getInstance();

            $query = 'SELECT `id`, `' . $field . '` FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE id IN (' . implode(',', $ids) . ')';

            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $vals[$ar['id']] = $ar[$field];
                }
            }

            if (empty($vals)) {
                return '---';
            }



            foreach ($vals as $id => $price) {

                foreach ($_POST['step'] as $step) {

                    if (isset($step['perc_diff'])) {
                        $val = $step['perc_diff'];
                        $dir = $step['perc_diff_dir'];
                        if ($dir != 'minus') {
                            $dir = 'plus';
                        }
                        if ($val > 0) {
                            if ($dir == 'minus') {
                                $price = $price - ($price * $val / 100);
                            } else {
                                $price = $price + ($price * $val / 100);
                            }
                        }
                    } elseif (isset($step['round'])) {
                        $val = $step['round'];
                        $dir = $step['round_dir'];
                        if ($dir != 'min' && $dir != 'max') {
                            $dir = 'near';
                        }
                        if ($val > 0) {
                            $k = 1;
                            switch ($dir) {
                                case 'max' : {
                                        $k = ceil($price / $val);
                                        break;
                                    }
                                case 'min' : {
                                        $k = floor($price / $val);
                                        break;
                                    }
                                case 'near' : {
                                        if (($price % $val) >= $val / 2) {
                                            $k = floor($price / $val) + 1;
                                        } else {
                                            $k = floor($price / $val);
                                        }
                                        break;
                                    }
                            }
                            $price = $k * $val;
                        }
                    } elseif (isset($step['summ_diff'])) {
                        $val = $step['summ_diff'];
                        $dir = $step['summ_diff_dir'];
                        if ($dir != 'minus') {
                            $dir = 'plus';
                        }
                        if ($val > 0) {
                            if ($dir == 'minus') {
                                $price = $price - $val;
                            } else {
                                $price = $price + $val;
                            }
                        }
                    }
                    $vals[$id] = $price;
                }
            }
            $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET `' . $field . '`=? WHERE `id`=?';
            foreach ($vals as $id => $price) {
                $stmt = $DBC->query($query, array($price, $id));
            }

            return '<div class="alert">Изменения применены. Изменено объектов: ' . count($vals) . '</div>';
        } else {
            global $smarty;
            $vals = array();
            $DBC = DBC::getInstance();
            $query = 'SELECT `' . $field . '` FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE id IN (' . implode(',', $ids) . ') LIMIT 100';


            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $vals[] = $ar[$field];
                }
            }
            if (empty($vals)) {
                return '';
            }
            $smarty->assign('field_name', $field);
            $smarty->assign('ids', $ids);
            $smarty->assign('field_vals', json_encode($vals));
            return $smarty->fetch($this->getAdminTplFolder() . '/batch_field_edit.tpl');
        }
    }

    protected function _weditAction() {
        //print_r($this->data_model);
        $field = $this->getRequestValue('field');
        if (!isset($this->data_model[$this->table_name][$field]) || $this->data_model[$this->table_name][$field]['type'] != 'price') {
            return '';
        }

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {

            $topic_id = intval($this->getRequestValue('topic_id'));
            if (!isset($_POST['step']) || empty($_POST['step'])) {
                return '---';
            }

            $vals = array();
            $DBC = DBC::getInstance();

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $Structure_Manager = new Structure_Manager();
            if ($topic_id > 0) {
                $category_structure = $Structure_Manager->loadCategoryStructure();
                $c = $Structure_Manager->get_all_childs($topic_id, $category_structure);
                $c[] = $topic_id;
                $query = 'SELECT `id`, `' . $field . '` FROM ' . DB_PREFIX . '_' . $this->table_name . (count($c) > 0 ? ' WHERE topic_id IN (' . implode(',', $c) . ')' : '');
            } else {
                $query = 'SELECT `id`, `' . $field . '` FROM ' . DB_PREFIX . '_' . $this->table_name;
            }


            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $vals[$ar['id']] = $ar[$field];
                }
            }

            if (empty($vals)) {
                return '---';
            }



            foreach ($vals as $id => $price) {

                foreach ($_POST['step'] as $step) {

                    if (isset($step['perc_diff'])) {
                        $val = $step['perc_diff'];
                        $dir = $step['perc_diff_dir'];
                        if ($dir != 'minus') {
                            $dir = 'plus';
                        }
                        if ($val > 0) {
                            if ($dir == 'minus') {
                                $price = $price - ($price * $val / 100);
                            } else {
                                $price = $price + ($price * $val / 100);
                            }
                        }
                    } elseif (isset($step['round'])) {
                        $val = $step['round'];
                        $dir = $step['round_dir'];
                        if ($dir != 'min' && $dir != 'max') {
                            $dir = 'near';
                        }
                        if ($val > 0) {
                            $k = 1;
                            switch ($dir) {
                                case 'max' : {
                                        $k = ceil($price / $val);
                                        break;
                                    }
                                case 'min' : {
                                        $k = floor($price / $val);
                                        break;
                                    }
                                case 'near' : {
                                        if (($price % $val) >= $val / 2) {
                                            $k = floor($price / $val) + 1;
                                        } else {
                                            $k = floor($price / $val);
                                        }
                                        break;
                                    }
                            }
                            $price = $k * $val;
                        }
                    } elseif (isset($step['summ_diff'])) {
                        $val = $step['summ_diff'];
                        $dir = $step['summ_diff_dir'];
                        if ($dir != 'minus') {
                            $dir = 'plus';
                        }
                        if ($val > 0) {
                            if ($dir == 'minus') {
                                $price = $price - $val;
                            } else {
                                $price = $price + $val;
                            }
                        }
                    }
                    $vals[$id] = $price;
                }
            }
            $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET `' . $field . '`=? WHERE `id`=?';
            foreach ($vals as $id => $price) {
                $stmt = $DBC->query($query, array($price, $id));
            }

            return 'Изменения применены. Изменено объектов: ' . count($vals);
        } else {
            global $smarty;
            $topic_id = intval($this->getRequestValue('topic_id'));

            $vals = array();
            $DBC = DBC::getInstance();

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $Structure_Manager = new Structure_Manager();
            if ($topic_id > 0) {
                $category_structure = $Structure_Manager->loadCategoryStructure();
                $c = $Structure_Manager->get_all_childs($topic_id, $category_structure);
                $c[] = $topic_id;
                $query = 'SELECT `' . $field . '` FROM ' . DB_PREFIX . '_' . $this->table_name . (count($c) > 0 ? ' WHERE topic_id IN (' . implode(',', $c) . ')' : '') . ' LIMIT 100';
            } else {
                $query = 'SELECT `' . $field . '` FROM ' . DB_PREFIX . '_' . $this->table_name . ' LIMIT 100';
            }


            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $vals[] = $ar[$field];
                }
            }
            if (empty($vals)) {
                $smarty->assign('no_val_avial', 1);
            }
            $smarty->assign('topic_id', $topic_id);
            $smarty->assign('field_name', $field);
            $smarty->assign('structure_box', $Structure_Manager->getCategorySelectBoxWithName('topic_id', $topic_id));
            $smarty->assign('field_vals', json_encode($vals));
            return $smarty->fetch($this->getAdminTplFolder() . '/wedit.tpl');
        }
    }

    protected function _editAction() {
        if ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
            $user_id = (int) $_SESSION['user_id_value'];
            if ($this->checkOwning($this->getRequestValue($this->primary_key), $user_id)) {
                return parent::_editAction();
            }
        } else {
            return parent::_editAction();
        }
        return '';
    }

    protected function _edit_doneAction() {
        if ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
            $skip_check_owning = false;
            if ($this->getConfigValue('data_adv_share_access_extended') != '') {
                $extended_user_list = explode(',', $this->getConfigValue('data_adv_share_access_extended'));
                if (!in_array($this->getRequestValue('user_id'), $extended_user_list)) {
                    $user_id = (int) $_SESSION['user_id_value'];
                } else {
                    $user_id = $this->getRequestValue('user_id');
                    $skip_check_owning = true;
                }
            } else {
                $user_id = (int) $_SESSION['user_id_value'];
            }
            $this->setRequestValue('user_id', $user_id);

            $_POST['user_id'] = $user_id;
            if ($skip_check_owning) {
                return parent::_edit_doneAction();
            } elseif ($this->checkOwning($this->getRequestValue($this->primary_key), $user_id)) {
                return parent::_edit_doneAction();
            }
        } else {
            return parent::_edit_doneAction();
        }
        return '';
    }

    protected function _new_doneAction() {
        if ( isset($_SESSION['user_id_value']) ) {
            $user_id = (int) $_SESSION['user_id_value'];
        } else {
            $user_id = (int) $_SESSION['user_id'];
        }
        if ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
            $this->setRequestValue('user_id', $user_id);
            $_POST['user_id'] = $user_id;
        }
        if ( $this->getConfigValue('apps.products.limit_add_data')  ) {
            require_once (SITEBILL_DOCUMENT_ROOT.'/apps/api/classes/class.common.php');
            require_once (SITEBILL_DOCUMENT_ROOT.'/apps/cart/api/class.cart.php');
            $api_cart = new API_cart();
            $user_limit = (int)$api_cart->get_user_limit($user_id, 'exclusive');
            if ( $user_limit < 1 ) {
                $this->riseError('Превышен лимит эксклюзивов');
                return false;
            }
        }
        $status = parent::_new_doneAction();
        if ( $status and $this->get_new_record_id() and $this->getConfigValue('apps.products.limit_add_data') ) {
            $increment = new \userproducts\modules\increment();
            $increment->decrement_limit('user', $user_id, 'exclusive', 1);
        }
        return $status;
    }

    protected function _delete_finalAction() {
        if (intval($this->getConfigValue('apps.realty.use_predeleting')) !== 1) {
            return '';
        }

        if ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
            $user_id = (int) $_SESSION['user_id_value'];
            if ($this->checkOwning($id, $user_id)) {
                return parent::_deleteAction();
            }
        } else {
            return parent::_deleteAction();
        }
        return '';
    }

    /*
     * Restore adv from archive to actual base
     */

    protected function _restoreAction() {
        if (intval($this->getConfigValue('apps.realty.use_predeleting')) !== 1) {
            return '';
        }
        $id = intval($this->getRequestValue($this->primary_key));
        if ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
            $user_id = (int) $_SESSION['user_id_value'];
            if ($this->checkOwning($id, $user_id)) {
                $DBC = DBC::getInstance();
                $query = 'UPDATE ' . DB_PREFIX . '_data SET `archived`=0 WHERE `id`=?';
                $stmt = $DBC->query($query, array($id));
            }
        } else {
            $DBC = DBC::getInstance();
            $query = 'UPDATE ' . DB_PREFIX . '_data SET `archived`=0 WHERE `id`=?';
            $stmt = $DBC->query($query, array($id));
        }
        if ($this->isRedirectDisabled()) {
            return true;
        }
        header('location: ' . SITEBILL_MAIN_URL . '/admin/?archived=1');
        exit();
        return '';
    }

    protected function _deleteAction() {
        $id = intval($this->getRequestValue($this->primary_key));
        if ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
            $user_id = (int) $_SESSION['user_id_value'];
            if ($this->checkOwning($id, $user_id)) {
                if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting') && isset($this->data_model['data']['archived'])) {
                    $DBC = DBC::getInstance();
                    $query = 'UPDATE ' . DB_PREFIX . '_data SET `archived`=1 WHERE `id`=?';
                    $stmt = $DBC->query($query, array($id));
                    if ($this->isRedirectDisabled()) {
                        return true;
                    }

                    header('location: ' . SITEBILL_MAIN_URL . '/admin/?action=' . $this->action);
                    exit();
                } else {
                    return parent::_deleteAction();
                }
            }
        } else {
            if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting') && isset($this->data_model['data']['archived'])) {
                $DBC = DBC::getInstance();
                $query = 'UPDATE ' . DB_PREFIX . '_data SET `archived`=1 WHERE `id`=?';
                $stmt = $DBC->query($query, array($id));
                if ($this->isRedirectDisabled()) {
                    return true;
                }

                header('location: ' . SITEBILL_MAIN_URL . '/admin/?action=' . $this->action);
                exit();
                return $this->grid();
            } else {
                return parent::_deleteAction();
            }
        }
        return '';
    }

    /** TODO
    * Субобъекты
    * заметки
    */
    protected function _subobjAction() {
        //echo 'complexobj';
        //$this->initCOModel();


        $ret = '';

        $user_id = intval($_SESSION['user_id']);
        $id = intval($this->getRequestValue('id'));

        if (!$this->checkOwning($id, $user_id)) {
            return 'Access denied';
        }

        return '_subobjAction';

        $complex_list = '';
        $DBC = DBC::getInstance();
        $query = 'SELECT complex_id, name FROM ' . DB_PREFIX . '_complex ORDER BY name ASC';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $complex_list .= '<a class="' . ($complex_id == $ar['complex_id'] ? 'selected' : '') . '" href="' . SITEBILL_MAIN_URL . '/admin/index.php?action=complex&do=complexobj&complex_id=' . $ar['complex_id'] . '">' . $ar['name'] . '</a>';
            }
        }
        global $smarty;
        $smarty->assign('complex_list', $complex_list);
        if ($id === 0) {

        } else {

            $subobjs = array();
            $DBC = DBC::getInstance();
            $query = 'SELECT complexobj_id FROM ' . DB_PREFIX . '_complexobj WHERE complex_id=? ORDER BY complexobj_id ASC';
            $stmt = $DBC->query($query, array($complex_id));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $complexobjs[] = $ar['complexobj_id'];
                }
            }

            $form_data = $this->data_model;

            /* require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
              $ATH=new Admin_Table_Helper();
              $form_data=$ATH->load_model('complexobj', false); */

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            $data_model = new Data_Model();

            if (!empty($complexobjs)) {
                foreach ($complexobjs as $k => $complexobj) {
                    $m[$k] = $data_model->init_model_data_from_db('complexobj', 'complexobj_id', $complexobj, $form_data['complexobj'], true);
                }
            }

            $form_data['complexobj']['complex_id']['value'] = $complex_id;

            $smarty->assign('add_form', $this->get_goform($form_data['complexobj']));

            $smarty->assign('complex_id', $complex_id);

            $cols = array();
            foreach ($form_data['complexobj'] as $v) {
                $cols[] = array('n' => $v['name'], 't' => $v['title']);
            }

            $smarty->assign('complexobjs_comlumns', $cols);
            $smarty->assign('complexobjs', $m);
        }
        $ret = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/complex/admin/template/complexobjs_list.tpl');
        return $ret;
    }

    function gatherRequestParams() {
        $params = array();
        $var = $this->getRequestValue('user_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['user_id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['user_id'] = $var;
            }
        }

        $var = $this->getRequestValue('topic_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['topic_id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['topic_id'] = $var;
            }
        }

        $var = $this->getRequestValue('country_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['country_id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['country_id'] = $var;
            }
        }

        $var = $this->getRequestValue('region_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['region_id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['region_id'] = $var;
            }
        }

        $var = $this->getRequestValue('city_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['city_id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['city_id'] = $var;
            }
        }

        $var = $this->getRequestValue('district_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['district_id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['district_id'] = $var;
            }
        }

        $var = $this->getRequestValue('metro_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['metro_id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['metro_id'] = $var;
            }
        }

        $var = $this->getRequestValue('street_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['street_id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['street_id'] = $var;
            }
        }

        $var = intval($this->getRequestValue('page'));
        if ($var > 0) {
            $params['page'] = $var;
        }

        $var = trim($this->getRequestValue('order'));
        if ($var != '') {
            $params['order'] = $var;
        }

        $var = trim($this->getRequestValue('asc'));
        if ($var != '') {
            $params['asc'] = $var;
        }

        $var = trim($this->getRequestValue('active'));
        if ($var != '') {
            $params['active'] = $var;
        }

        $var = intval($this->getRequestValue('hot'));
        if ($var > 0) {
            $params['hot'] = $var;
        }

        $var = $this->getRequestValue('id');
        if (!is_array($var) && intval($var) > 0) {
            $params['id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['id'] = $var;
            }
        }

        $var = intval($this->getRequestValue('status_id'));
        if ($var > 0) {
            $params['status_id'] = $var;
        }

        $var = intval($this->getRequestValue('client_id'));
        if ($var > 0) {
            $params['client_id'] = $var;
        }

        $var = intval($this->getRequestValue('archived'));
        if ($var > 0) {
            $params['archived'] = $var;
        }

        $params['price'] = $this->getRequestValue('price');

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable') == 1) {
            $var = intval($this->getRequestValue('vip_status'));
            if ($var > 0) {
                $params['vip_status'] = $var;
            }
            $var = intval($this->getRequestValue('premium_status'));
            if ($var > 0) {
                $params['premium_status'] = $var;
            }
            $var = intval($this->getRequestValue('bold_status'));
            if ($var > 0) {
                $params['bold_status'] = $var;
            }
        }



        if (isset($this->data_model[$this->table_name]['uniq_id'])) {
            $var = intval($this->getRequestValue('uniq_id'));
            if ($var > 0) {
                $params['uniq_id'] = $var;
            }
            //$params['uniq_id'] = (int)$this->getRequestValue('uniq_id');
            //$smarty->assign('show_uniq_id', 'true');
        }
        if ($this->getRequestValue('srch_export_cian') == 'on' || $this->getRequestValue('srch_export_cian') == '1') {
            $var = intval($this->getRequestValue('srch_export_cian'));
            if ($var > 0) {
                $params['srch_export_cian'] = 1;
            }
            //$params['srch_export_cian'] = 1;
        }

        $var = $this->getRequestValue('srch_id');
        if (!is_array($var) && intval($var) > 0) {
            $params['id'] = intval($var);
        } elseif (is_array($var)) {
            $var = array_map(function($a) {
                return intval($a);
            }, $var);
            $var = array_filter($var, function($a) {
                if ($a != 0) {
                    return $a;
                }
            });
            if (count($var) > 0) {
                $params['id'] = $var;
            }
        }

        $var = trim($this->getRequestValue('srch_word'));
        if ($var != '') {
            $params['srch_word'] = $var;
        }
        $var = trim($this->getRequestValue('srch_phone'));
        if ($var != '') {
            $params['srch_phone'] = $var;
        }
        $var = trim($this->getRequestValue('srch_date_from'));
        if ($var != '') {
            $params['srch_date_from'] = $var;
        } else {
            $params['srch_date_from'] = 0;
        }
        $var = trim($this->getRequestValue('srch_date_to'));
        if ($var != '') {
            $params['srch_date_to'] = $var;
        } else {
            $params['srch_date_to'] = 0;
        }

        return $params;
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
        if ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
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
                $html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . "/template/frontend/" . $this->getConfigValue('theme') . "/apps/admin/template/realty_grid_wdg.tpl");
            } else {
                $html = $smarty->fetch($smarty->template_dir . "/realty_grid_wdg.tpl");
            }
        } else {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . "/template/frontend/" . $this->getConfigValue('theme') . "/apps/admin/template/realty_grid.tpl")) {
                $html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . "/template/frontend/" . $this->getConfigValue('theme') . "/apps/admin/template/realty_grid.tpl");
            } else {
                $html = $smarty->fetch($smarty->template_dir . "/realty_grid.tpl");
            }
        }
        if ( $billing_plugin != '' ) {
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
                $res[$k]['_memo'] = '<div>'.$this->compile_memory_control($v['id']['value']).'</div>';
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

    function add_tags_params($params) {
        if (isset($_SESSION['tags_array']) && is_array($_SESSION['tags_array'])) {
            foreach ($_SESSION['tags_array'] as $column_name => $column_values) {
                $model = $this->get_model();
                $column_values = $this->parse_id_values_from_model($column_name, $column_values, $this->get_model());
                if($model[$this->table_name][$column_name]['type'] == 'select_by_query_multi'){
                    $pkname = '';
                    foreach ($model[$this->table_name] as $k => $v){
                        if($v['type'] == 'primary_key'){
                            $pkname = $k;
                            break;
                        }
                    }
                    unset($params[$column_name]);
                    $params[$pkname] = $column_values;
                    //$params['id'] = $column_values;
                }elseif (isset($params[$column_name]) and ! is_array($params[$column_name])) {
                    if ($params[$column_name] != 0) {
                        array_push($column_values, $params[$column_name]);
                    }
                    $params[$column_name] = $column_values;
                } elseif (isset($params[$column_name]) and is_array($params[$column_name])) {
                    $params[$column_name] = array_merge($params[$column_name], $column_values);
                } elseif (is_array($column_values)) {
                    $params[$column_name] = $column_values;
                }
            }
        }
        return $params;
    }

    function parse_id_values_from_model($column_name, $column_values, $data_model) {
        if ($data_model[$this->table_name][$column_name]['type'] == 'select_by_query') {
            foreach ($column_values as $idx => $value) {

                $namefield = $data_model[$this->table_name][$column_name]['value_name'];
                $langpostfix = $this->getLangPostfix($this->getCurrentLang());

                if(isset($data_model[$this->table_name][$column_name]['parameters']['no_ml']) && $data_model[$this->table_name][$column_name]['parameters']['no_ml'] == 1){

                }else{
                    $namefield = $namefield.$langpostfix;
                }

                $val = $this->data_model_object->get_value_id_by_name($data_model[$this->table_name][$column_name]['primary_key_table'], $namefield, $data_model[$this->table_name][$column_name]['primary_key_name'], $value);

                if (0 != (int) $val) {
                    $column_values[$idx] = $val;
                } else {
                    unset($column_values[$idx]);
                }
            }
        } elseif($data_model[$this->table_name][$column_name]['type'] == 'select_by_query_multi') {
            foreach ($column_values as $idx => $value) {
                $val = $this->data_model_object->get_value_id_by_name($data_model[$this->table_name][$column_name]['primary_key_table'], $data_model[$this->table_name][$column_name]['value_name'], $data_model[$this->table_name][$column_name]['primary_key_name'], $value);

                if (0 != (int) $val) {
                    $column_values[$idx] = $val;
                } else {
                    unset($column_values[$idx]);
                }
            }
            if(!empty($column_values)){
                //$model_array[$key]['value'] = array();
                //$model_array[$key]['value_string'] = '';
                $DBC = DBC::getInstance();
                $query = 'SELECT DISTINCT `primary_id` FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name`=? AND `field_value` IN ('.implode(',', array_fill(0, count($column_values), '?')).')';
                $stmt = $DBC->query($query, array_merge(array($this->table_name, $column_name), $column_values));

                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $ids[] = $ar['primary_id'];
                    }
                    $column_values = array();
                    $column_values = $ids;
                }else{
                    $column_values = array(-1);
                }

                //print_r($ids);
            }
        } elseif ($data_model[$this->table_name][$column_name]['type'] == 'select_box' and count($column_values) > 0) {
            $select_data = array_flip($data_model[$this->table_name][$column_name]['select_data']);
            $ra = array();
            foreach ($column_values as $idx => $value) {
                if ($select_data[$value]) {
                    $ra[] = $select_data[$value];
                }
            }
            return $ra;
        } elseif ($data_model[$this->table_name][$column_name]['type'] == 'select_box_structure' and count($column_values) > 0) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $Structure = new Structure_Manager();
            $x = $Structure->createCatalogChains();
            $categoryChain = $x['txt'];
            $categoryChainRev = array_flip($categoryChain);
            foreach ($column_values as $idx => $value) {
                $value_array = explode(' / ', $value);
                $var = implode("|", $value_array);
                $var = mb_strtolower($var);
                if (isset($categoryChainRev[$var])) {
                    $column_values[$idx] = $categoryChainRev[$var];
                } else {
                    unset($column_values[$idx]);
                }
            }
        }
        return $column_values;
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

    function add_tagged_parms_to_where($where_array, $tagged_params) {

        foreach ($tagged_params as $column_name => $column_values) {
            if (is_array($column_values) && count($column_values) > 0) {
                //$column_values=array_filter($column_values, function($a){if($a!=''){return $a;}});
                if (!empty($column_values)) {
                    $type = $this->data_model['data'][$column_name]['type'];
                    if ($type == 'dtdatetime') {
                        if(isset($column_values['min']) || isset($column_values['max'])){
                            if (isset($column_values['min'])) {
                                $where_array[] = "(re_data.`" . $column_name . "` >= '" . $column_values['min'] . " 00:00:00')";
                            }
                            if (isset($column_values['max'])) {
                                $where_array[] = "(re_data.`" . $column_name . "` <= '" . $column_values['max'] . " 23:59:59')";
                            }
                        } /*elseif($column_values == 'today') {
                            $where_array[] = "(re_data.`" . $column_name . "` >= '" . date('Y-m-d 00:00:00') . "')";
                            $where_array[] = "(re_data.`" . $column_name . "` <= '" . date('Y-m-d 23:59:59') . " ')";
                        } elseif($column_values == 'yesterday') {
                            $where_array[] = "(re_data.`" . $column_name . "` >= '" . date('Y-m-d 00:00:00', (time() - 24*3600)) . "')";
                            $where_array[] = "(re_data.`" . $column_name . "` <= '" . date('Y-m-d 23:59:59', (time() - 24*3600)) . "')";
                        } elseif($column_values == 'thismonth') {
                            $where_array[] = "(re_data.`" . $column_name . "` >= '" . date('Y-m-d 00:00:00', (time() - 24*3600)) . "')";
                            $where_array[] = "(re_data.`" . $column_name . "` <= '" . date('Y-m-d 23:59:59', (time() - 24*3600)) . "')";
                        } */else {
                            $where_array[] = "(re_data." . $column_name . " IN ('" . implode('\',\'', $column_values) . "'))";
                        }
                    } elseif (isset($column_values['min']) || isset($column_values['max'])) {
                        if (isset($column_values['min'])) {
                            $where_array[] = "(re_data.`" . $column_name . "`*1 >= '" . $column_values['min'] . "')";
                        }
                        if (isset($column_values['max'])) {
                            $where_array[] = "(re_data.`" . $column_name . "`*1 <= '" . $column_values['max'] . "')";
                        }
                    } elseif ($type == 'client_id') {
                        $where_fio_phone_array = array();
                        foreach ($column_values as $fio_phone) {
                            list($fio, $phone) = explode(',', $fio_phone);
                            $fio = trim($fio);
                            $phone = trim($phone);
                            $where_fio_phone_array[] = ' client_id IN (SELECT client_id FROM ' . DB_PREFIX . '_client WHERE fio=\'' . $fio . '\' AND phone=\'' . $phone . '\') ';
                        }

                        $where_array[] = implode(' OR ', $where_fio_phone_array);
                    } else {
                        $where_array[] = "(re_data." . $column_name . " IN ('" . implode('\',\'', $column_values) . "'))";
                    }
                }
            }
        }
        return $where_array;
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

        $topic_id = (int) $form_data['topic_id']['value'];
        $current_id = (int) $form_data[$this->primary_key]['value'];

        if ($topic_id != 0 && $current_id != 0) {

            $href = $this->getRealtyHREF($current_id, false, array('topic_id' => $topic_id, 'alias' => $form_data['translit_alias']['value']));
            $rs .= '<div class="row"><a class="btn btn-success pull-right" href="' . $href . '" target="_blank">' . Multilanguage::_('L_SEE_AT_SITE') . '</a></div>';
        }

        if (1 == $this->getConfigValue('apps.geodata.enable')) {
            $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/geodata/js/geodata.js"></script>';
        }
        $rs .= '<form method="post" class="form-horizontal" action="index.php" enctype="multipart/form-data">';
        /* $id=md5('data_form_'.time());
          $rs .= '<form method="post" id="'.$id.'" class="form-horizontal" action="index.php" enctype="multipart/form-data">';
          $rs .= '<script>var control_visibility="'.$id.'";</script>'; */
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
        $el['form_footer'] = '</form>';

        if ($do != 'new') {
            $el['controls']['apply'] = array('html' => '<button id="apply_changes" class="btn btn-info">' . Multilanguage::_('L_TEXT_APPLY') . '</button>');
        }
        $el['controls']['submit'] = array('html' => '<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">' . $button_title . '</button>');

        $smarty->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data_admin.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data_admin.tpl';
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl';
        } else {
            $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
        }
        $html = $smarty->fetch($tpl_name);
        /* if(file_exists(SITEBILL_MAIN_URL.'/template/frontend/'.$this->getConfigValue('theme').'/js/custom_data_admin.js')){

          } */

        return $html;

        if ($REQUESTURIPATH == 'show_ms') {
            ini_set('memory_limit', '1024M');
            $DBC = DBC::getInstance();
            $query = 'SELECT * FROM re_mysearch';
            $stmt = $DBC->query($query);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $ar['params'] = json_decode($ar['params']);
                    $mysearch[] = $ar;
                }
            }
            foreach ($mysearch as $k => $v) {
                if (isset($v['params']['city_id'])) {
                    $query = 'SELECT r.country_id FROM re_city ci LEFT JOIN re_region r USING(region_id) WHERE ci.city_id=?';
                    $stmt = $DBC->query($query, array($v['params']['city_id']));
                    if ($stmt) {
                        $ar = $DBC->fetch($stmt);
                        $mysearch[$k]['params']['country_id'] = $ar['country_id'];
                    }
                }
            }
            print_r($mysearch);
            exit();
        }
    }

    function getNonUniqIds($form_data){
        $ids = array();
        $unque_fields = trim($this->getConfigValue('apps.realty.uniq_params'));

        $id = 0;
        if(intval($form_data['id']['value']) != 0){
            $id = intval($form_data['id']['value']);
        }

        $fields = array();
        if ('' !== $unque_fields) {
            $matches = array();
            preg_match_all('/([^,\s]+)/i', $unque_fields, $matches);
            if (!empty($matches[1])) {
                $fields = $matches[1];
            }
        }

        $where = array();
        $where_val = array();

        if (!empty($fields)) {
            foreach ($fields as $f) {
                if (isset($form_data[$f])) {
                    if ($form_data[$f]['dbtype'] == 1 || ($form_data[$f]['dbtype'] != 'notable' && $form_data[$f]['dbtype'] != '0')) {
                        $where[] = '`' . $f . '`=?';
                        $where_val[] = $form_data[$f]['value'];
                    }
                }
            }
            if($id > 0){
                $where[] = '`id`<>?';
                $where_val[] = $id;
            }
        } elseif (isset($form_data['city_id']) && isset($form_data['street_id']) && isset($form_data['number'])) {
            $where[] = '`city_id`=?';
            $where_val[] = (int) $form_data['city_id']['value'];
            $where[] = '`street_id`=?';
            $where_val[] = (int) $form_data['street_id']['value'];
            $where[] = '`number`=?';
            $where_val[] = $form_data['number']['value'];
            if($id > 0){
                $where[] = '`id`<>?';
                $where_val[] = $id;
            }
        } else {
            return $ids;
        }

        $DBC = DBC::getInstance();

        $query = 'SELECT id FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE ' . implode(' AND ', $where);

        $stmt = $DBC->query($query, $where_val);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ids[] = $ar['id'];
            }
        }

        return $ids;
    }

    function checkUniquety($form_data) {
        $uns = $this->getNonUniqIds($form_data);
        if (count($uns) > 0) {
            $this->riseError(Multilanguage::_('ADVUNIQUETY_ERROR', 'system').' ('.implode(',', $uns).')');
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Edit data
     * @param array $form_data form data
     * @return boolean
     */
    function edit_data($form_data, $language_id = 0, $primary_key_value = false) {

        $id = intval($this->getRequestValue('id'));

        $need_send_message = 0;
        $status_changed = false;

        if (isset($form_data['tmp_password']) && $form_data['tmp_password']['value'] == '') {
            $form_data['tmp_password']['value'] = substr(md5(time()), 1, 6);
        }

        if (isset($form_data['price'])) {
            $form_data['price']['value'] = str_replace(' ', '', $form_data['price']['value']);
        }

        $DBC = DBC::getInstance();

        if ($this->getConfigValue('apps.billing.enable') == 1) {
            if (isset($form_data['vip_status_end']) && isset($form_data['premium_status_end']) && isset($form_data['bold_status_end'])) {
                $current_vip_status_end = 0;
                $current_premium_status_end = 0;
                $current_bold_status_end = 0;
                $q = 'SELECT vip_status_end, premium_status_end, bold_status_end FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '`=?';
                $stmt = $DBC->query($q, array($id));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $current_vip_status_end = (int) $ar['vip_status_end'];
                    $current_premium_status_end = (int) $ar['premium_status_end'];
                    $current_bold_status_end = (int) $ar['bold_status_end'];
                }
                $new_vip_date = $this->prepareVipStatsDateValue($current_vip_status_end, $form_data['vip_status_end']['value']);
                if ($new_vip_date === FALSE) {
                    unset($form_data['vip_status_end']);
                } else {
                    $form_data['vip_status_end']['value'] = $new_vip_date;
                }
                $new_premium_date = $this->prepareVipStatsDateValue($current_premium_status_end, $form_data['premium_status_end']['value']);
                if ($new_premium_date === FALSE) {
                    unset($form_data['premium_status_end']);
                } else {
                    $form_data['premium_status_end']['value'] = $new_premium_date;
                }

                $new_bold_date = $this->prepareVipStatsDateValue($current_bold_status_end, $form_data['bold_status_end']['value']);
                if ($new_bold_date === FALSE) {
                    unset($form_data['bold_status_end']);
                } else {
                    $form_data['bold_status_end']['value'] = $new_bold_date;
                }
            } elseif (isset($form_data['vip_status_end'])) {
                $current_vip_status_end = 0;
                $q = 'SELECT vip_status_end FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '`=?';

                $stmt = $DBC->query($q, array((int) $this->getRequestValue('id')));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $current_vip_status_end = (int) $ar['vip_status_end'];
                }

                $new_vip_date = $this->prepareVipStatsDateValue($current_vip_status_end, $form_data['vip_status_end']['value']);
                if ($new_vip_date === FALSE) {
                    unset($form_data['vip_status_end']);
                } else {
                    $form_data['vip_status_end']['value'] = $new_vip_date;
                }
            } elseif (isset($form_data['bold_status_end'])) {
                $current_bold_status_end = 0;
                $q = 'SELECT bold_status_end FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '`=?';
                $stmt = $DBC->query($q, array((int) $this->getRequestValue('id')));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $current_bold_status_end = (int) $ar['bold_status_end'];
                }

                $new_bold_date = $this->prepareVipStatsDateValue($current_bold_status_end, $form_data['bold_status_end']['value']);
                if ($new_bold_date === FALSE) {
                    unset($form_data['bold_status_end']);
                } else {
                    $form_data['bold_status_end']['value'] = $new_bold_date;
                }
            } elseif (isset($form_data['premium_status_end'])) {
                $current_premium_status_end = 0;
                $q = 'SELECT premium_status_end FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '`=?';
                $stmt = $DBC->query($q, array((int) $this->getRequestValue('id')));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $current_premium_status_end = (int) $ar['premium_status_end'];
                }

                $new_premium_date = $this->prepareVipStatsDateValue($current_premium_status_end, $form_data['premium_status_end']['value']);
                if ($new_premium_date === FALSE) {
                    unset($form_data['premium_status_end']);
                } else {
                    $form_data['premium_status_end']['value'] = $new_premium_date;
                }
            }
        } else {
            unset($form_data['premium_status_end']);
            unset($form_data['bold_status_end']);
            unset($form_data['vip_status_end']);
        }

        if (1 === (int) $this->getConfigValue('notify_about_publishing') || 1 === (int) $this->getConfigValue('apps.twitter.enable')) {
            $query = 'SELECT active, hot FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '`=?';

            $stmt = $DBC->query($query, array((int) $this->getRequestValue('id')));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $current_active_status = $ar['active'];
                $current_hot_status = $ar['hot'];
            }
        }

        if (isset($form_data['status_id'])) {
            $current_status_id = 0;
            $query = 'SELECT status_id FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '`=?';
            $stmt = $DBC->query($query, array($id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $current_status_id = intval($ar['status_id']);
            }

            if ($current_status_id !== intval($form_data['status_id']['value'])) {
                $status_changed = true;
            }
        }

        if ($this->getConfigValue('notify_about_publishing')) {

            if ($current_active_status == 0 AND $form_data['active']['value'] == 1) {
                $need_send_message = 1;
            }
            if ($current_hot_status == 1 AND $form_data['hot']['value'] == 0) {
                $need_send_message = 1;
            }

            if ($need_send_message == 1) {
                $n_id = $id;
                $n_pass = $form_data['tmp_password']['value'];
                $n_email = $form_data['email']['value'];
                $n_phone = $form_data['phone']['value'];
                $n_fio = $form_data['fio']['value'];

                $user_id = $form_data['user_id']['value'];
                if ($user_id > 0) {
                    $DBC = DBC::getInstance();
                    $query = 'SELECT email, phone, user_id, fio, group_id, login FROM ' . DB_PREFIX . '_user WHERE user_id=?';
                    $stmt = $DBC->query($query, array($user_id));
                    if ($stmt) {
                        $ar = $DBC->fetch($stmt);
                        if ($ar['login'] != '_unregistered') {
                            $n_pass = $form_data['tmp_password']['value'];
                            $n_email = $ar['email'];
                            $n_phone = $ar['phone'];
                            $n_fio = $ar['fio'];
                        }
                    }
                }
            }
        }

        $y_id = '';
        if (isset($form_data['youtube'])) {
            if (strpos($form_data['youtube']['value'], 'youtube.com') !== FALSE) {
                $d = parse_url($form_data['youtube']['value']);
                if (isset($d['query'])) {
                    parse_str($d['query'], $a);
                    $y_id = $a['v'];
                }
            } elseif (strpos($form_data['youtube']['value'], 'youtu.be') !== FALSE) {
                $d = parse_url($form_data['youtube']['value']);
                if (isset($d['path']) && trim($d['path'], '/') != '' && strpos(trim($d['path'], '/'), '/') === false) {
                    $y_id = trim($d['path'], '/');
                }
            } else {

                if (preg_match('/.*([-_A-Za-z0-9]+).*/', $form_data['youtube']['value'], $matches)) {
                    $y_id = $matches[0];
                }
            }
            $form_data['youtube']['value'] = $y_id;
        }

        if (1 == $this->getConfigValue('apps.geodata.try_encode') && 1 == $this->getConfigValue('apps.geodata.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/geodata/admin/admin.php';
            $GA = new geodata_admin();
            $form_data = $GA->try_geocode($form_data);
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        $query_params = $data_model->get_prepared_edit_query(DB_PREFIX . '_' . $this->table_name, $this->primary_key, $form_data[$this->primary_key]['value'], $form_data);
        $query_params_vals = $query_params['p'];
        $query = $query_params['q'];
        //$this->writeArrayLog($query_params);


        $stmt = $DBC->query($query, $query_params_vals, $rows, $success_mark);
        if (!$success_mark) {
            $this->riseError($DBC->getLastError());
        }

        $imgs = array();

        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploads') {
                $ims = $this->appendUploads($this->table_name, $form_item, $this->primary_key, $id);
                if (is_array($ims) && count($ims) > 0 && 0 == intval($form_item['parameters']['no_watermark'])) {
                    $imgs = array_merge($imgs, $ims);
                }
            } elseif ($form_item['type'] == 'docuploads') {
                $imgs_uploads = $this->appendDocUploads($this->table_name, $form_item, $this->primary_key, $id);
            } elseif ($form_item['type'] == 'select_by_query_multi') {
                //echo 1;
                $vals = $form_item['value'];
                if (!is_array($vals)) {
                    $vals = (array) $vals;
                }
                $query = 'DELETE FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name`=? AND `primary_id`=?';
                $stmt = $DBC->query($query, array($this->table_name, $form_item['name'], $id));
                //echo $DBC->getLastError();
                if (!empty($vals)) {
                    //refresh
                    $query = 'INSERT INTO ' . DB_PREFIX . '_multiple_field (`table_name`, `field_name`, `primary_id`, `field_value`) VALUES (?,?,?,?)';
                    foreach ($vals as $val) {
                        $stmt = $DBC->query($query, array($this->table_name, $form_item['name'], $id, $val));
                    }
                }
            }
        }

        $ims = $this->editImageMulti('data', 'data', $this->primary_key, $id);
        if (is_array($ims) && count($ims) > 0) {
            $imgs = array_merge($imgs, $ims);
        }

        if (1 == $this->getConfigValue('apps.seo.data_alias_enable') && ((isset($form_data['translit_alias']) && $form_data['translit_alias']['value'] == '') || !isset($form_data['translit_alias']))) {
            $this->saveTranslitAlias($id);
        }

        if ($status_changed) {
            $this->setStatusDate($id);
        }

        /* Send notify messages */
        if ($need_send_message) {
            if ($n_email != '') {
                $this->notifyEmailAboutActivation($n_id, $n_email, array('fio' => $n_fio));
            } elseif ($n_phone != '' and file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/sms/admin/admin.php')) {
                $body = $this->getConfigValue('apps.fasteditor.sms_send_password_text_long');
                $body = str_replace('{password}', $n_pass, $body);
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/sms/admin/admin.php');
                $SMSSender = new sms_admin();
                if ($SMSSender->send($n_phone, $body)) {

                } else {

                }
            }
        }

        /* Add twit */

        if ($this->getConfigValue('apps.twitter.enable')) {
            if ($current_active_status == 0 AND $form_data['active']['value'] == 1) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/twitter/admin/admin.php';
                $Twitter = new twitter_admin();
                $Twitter->sendTwit($this->getRequestValue('id'));
            }
        }

        if ($this->getConfigValue('apps.telegram.enable')) {
            if ($current_active_status == 0 AND $form_data['active']['value'] == 1) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/telegram/admin/admin.php';
                $Telegram = new telegram_admin();
                $Telegram->sendPost($this->getRequestValue('id'));
            }
        }

        if ($this->getConfigValue('apps.facebook.enable')) {
            if ($current_active_status == 0 AND $form_data['active']['value'] == 1) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/facebook/admin/admin.php';
                $Facebook = new facebook_admin();
                $Facebook->sendPost($this->getRequestValue('id'));
            }
        }



        if ($this->getConfigValue('is_watermark')) {
            $this->do_watermark($imgs);
        }

        // Обработка handler
        $this->tryHandlers('data', 'edit_data', $form_data, $id);

        $page = $this->getRequestValue('page');
        $_POST = array();
        $_POST['page'] = $page;
        return $id;
    }

    function do_watermark($imgs, $position = '', $offset_left = '', $offset_top = '', $offset_right = '', $offset_bottom = '') {
        $filespath = SITEBILL_DOCUMENT_ROOT . '/img/data/';
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/watermark/watermark.php';
        $Watermark = new Watermark();
        if ($position == '') {
            $position = $this->getConfigValue('apps.watermark.position');
        }
        if ($offset_left == '') {
            $offset_left = $this->getConfigValue('apps.watermark.offset_left');
        }
        if ($offset_top == '') {
            $offset_top = $this->getConfigValue('apps.watermark.offset_top');
        }
        if ($offset_right == '') {
            $offset_right = $this->getConfigValue('apps.watermark.offset_right');
        }
        if ($offset_bottom == '') {
            $offset_bottom = $this->getConfigValue('apps.watermark.offset_bottom');
        }


        $Watermark->setPosition($position);
        $Watermark->setOffsets(array(
            $offset_left,
            $offset_top,
            $offset_right,
            $offset_bottom
        ));
        $preview_width = $this->getConfigValue('data_image_preview_width');
        if ($preview_width == '') {
            $preview_width = $this->getConfigValue('news_image_preview_width');
        }
        $preview_height = $this->getConfigValue('data_image_preview_height');
        if ($preview_height == '') {
            $preview_height = $this->getConfigValue('news_image_preview_height');
        }


        if (defined('STR_MEDIA') && STR_MEDIA == Sitebill::MEDIA_SAVE_FOLDER) {
            /*
             * TODO
             * перенести создание папки под сохранение копий безвотермарка внутрь условия требования такого сохранения
             */
            $copy_folder = MEDIA_FOLDER . '/nowatermark/';
            if (defined('STR_MEDIA_FOLDERFDAYS') && STR_MEDIA_FOLDERFDAYS === 1) {
                $foldeformat = 'Ymd';
            } else {
                $foldeformat = 'Ym';
            }
            $folder_name = date($foldeformat, time());
            $locs = $copy_folder . '/' . $folder_name;
            if (!is_dir($locs)) {
                mkdir($locs);
            }
            if (1 == $this->getConfigValue('save_without_watermark') && !empty($imgs)) {
                $copy_folder = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark';
                foreach ($imgs as $v) {
                    copy($filespath . $v['normal'], $copy_folder . '/' . $v['normal']);
                }
            }
            if (!empty($imgs)) {
                foreach ($imgs as $v) {
                    $Watermark->printWatermark(MEDIA_FOLDER . '/' . $v['normal']);
                    if ($this->getConfigValue('apps.watermark.preview_enable')) {
                        $Watermark->printWatermark($filespath . $v['preview'], true);
                        //$this->makePreview(MEDIA_FOLDER . '/' . $v['preview'], MEDIA_FOLDER . '/' . $v['preview'], $preview_width, $preview_height);
                    }
                }
            }
        } else {
            if (1 == $this->getConfigValue('save_without_watermark') && !empty($imgs)) {
                $copy_folder = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/';
                foreach ($imgs as $v) {
                    /*
                      Обработчик создания папок для варианта с размещением изображений в подпапках
                      $path_parts=explode('/', $v['normal']);
                      if(count($path_parts)==3){
                      $locs=$copy_folder.$path_parts[0];
                      if (!is_dir($locs)) {
                      mkdir($locs);
                      }
                      $locs = $copy_folder.$path_parts[0].'/'.$path_parts[1];
                      if (!is_dir($locs)) {
                      mkdir($locs);
                      }
                      }
                     */
                    copy($filespath . $v['normal'], $copy_folder . $v['normal']);
                }
            }
            if (!empty($imgs)) {
                foreach ($imgs as $v) {
                    $Watermark->printWatermark($filespath . $v['normal']);
                    if ($this->getConfigValue('apps.watermark.preview_enable')) {
                        $Watermark->printWatermark($filespath . $v['preview'], true);
                        //$this->makePreview(MEDIA_FOLDER . '/' . $v['preview'], MEDIA_FOLDER . '/' . $v['preview'], $preview_width, $preview_height);
                    }
                }
            }
        }
    }

    public function notifyEmailAboutActivation($n_id, $n_email, $data = array()) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $DBC = DBC::getInstance();
        $SM = new Structure_Manager();

        $category_structure = $SM->loadCategoryStructure();
        if (1 == $this->getConfigValue('apps.seo.data_alias_enable')) {
            $query = 'SELECT translit_alias, topic_id FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE ' . $this->primary_key . '=? LIMIT 1';
        } else {
            $query = 'SELECT topic_id FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE ' . $this->primary_key . '=? LIMIT 1';
        }

        $stmt = $DBC->query($query, array($n_id));
        if ($stmt) {
            $seo_data = $DBC->fetch($stmt);
        } else {
            $seo_data = array();
        }

        $href = $this->getRealtyHREF($n_id, true, array('topic_id' => $seo_data['topic_id'], 'alias' => $seo_data['translit_alias']));
        $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/template/mails/reguser_pub_notify.tpl';
        global $smarty;
        if (isset($data['fio']) && $data['fio'] != '') {
            $smarty->assign('mail_fio', $data['fio']);
            $smarty->assign('fio', $data['fio']);
        } else {
            $smarty->assign('mail_fio', '');
            $smarty->assign('fio', '');
        }
        $smarty->assign('href', $href);
        $smarty->assign('edit_url', $this->getServerFullUrl() . '/account/data/?do=edit&id=' . $n_id);
        $smarty->assign('mail_adv_id', $n_id);

        $smarty->assign('mail_signature', $this->getConfigValue('email_signature'));
        if (file_exists($tpl)) {
            $body = $smarty->fetch($tpl);
        } else {
            $body = Multilanguage::_('YOUR_AD_PUBLISHED', 'system') . '<br />';
            $body .= Multilanguage::_('AD_LINK', 'system') . ' <a href="' . $href . '">' . $href . '</a><br />';
        }
        $subject = $_SERVER['SERVER_NAME'] . ': ' . Multilanguage::_('YOUR_AD_PUBLISHED_SUBJ', 'system');
        $from = $this->getConfigValue('system_email');

        $this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
        $email_template_fetched = $this->fetch_email_template('data_moderate_success');

        if ($email_template_fetched) {
            $subject = $email_template_fetched['subject'];
            $body = $email_template_fetched['message'];

            $message_array['apps_name'] = 'need_moderate';
            $message_array['method'] = __METHOD__;
            $message_array['message'] = "subject = $subject, message = $body";
            $message_array['type'] = '';
            //$this->writeLog($message_array);
        }

        $this->sendFirmMail($n_email, $from, $subject, $body);
    }

    protected function _set_statusAction() {
        $set_status_id = (int) $this->getRequestValue('set_status_id');
        $data_id = (int) $this->getRequestValue('id');
        $this->setStatusState($data_id, $set_status_id);
        if ($this->getError()) {
            echo $this->GetErrorMessage();
        }
        //echo 'set status action';
        return $this->grid();
    }

    public function setStatusState($data_id, $status_id) {
        $DBC = DBC::getInstance();
        $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET status_id=? WHERE `' . $this->primary_key . '`=?';

        $stmt = $DBC->query($query, array($status_id, $data_id), $row, $success);
        if (!$success) {
            $this->riseError($DBC->getLastError());
            return false;
        }

        $this->setStatusDate($data_id);
    }

    public function setStatusDate($id, $date = '') {
        $DBC = DBC::getInstance();
        if ($date == '') {
            $date = date('Y-m-d H:i:s', time());
        }
        $query = 'UPDATE ' . DB_PREFIX . '_' . $this->table_name . ' SET status_change=? WHERE `' . $this->primary_key . '`=?';
        $stmt = $DBC->query($query, array($date, $id), $row, $success);
        if (!$success) {
            $this->riseError($DBC->getLastError());
            return false;
        }
    }

    public function getDataStatInfo($params = array()) {
        //@todo: Очень жесткие запросы при большом количестве записей
        $statuses = array();
        $activities = array();

        $DBC = DBC::getInstance();

        $query = 'SELECT active, COUNT(id) AS _cnt FROM ' . DB_PREFIX . '_data GROUP BY active';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $activities[$ar['active']] = $ar['_cnt'];
            }
        }

        if (!empty($params)) {
            foreach ($params as $f) {
                $query = 'SELECT `' . $f . '`, COUNT(id) AS _cnt FROM ' . DB_PREFIX . '_data GROUP BY `' . $f . '`';
                $stmt = $DBC->query($query);
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $statuses[$f][$ar[$f]] = $ar['_cnt'];
                    }
                }
            }
        }
        return array('status' => $statuses, 'active' => $activities, 'total' => array_sum($activities));
    }

    /**
     * Add data
     * @param array $form_data form data
     * @return boolean
     */
    function add_data($form_data, $language_id = 0) {

        $y_id = '';
        if (strpos($form_data['youtube']['value'], 'youtube.com') !== FALSE) {
            $d = parse_url($form_data['youtube']['value']);
            if (isset($d['query'])) {
                parse_str($d['query'], $a);
                $y_id = $a['v'];
            }
        } elseif (strpos($form_data['youtube']['value'], 'youtu.be') !== FALSE) {
            $d = parse_url($form_data['youtube']['value']);
            if (isset($d['path']) && trim($d['path'], '/') != '' && strpos(trim($d['path'], '/'), '/') === false) {
                $y_id = trim($d['path'], '/');
            }
        } else {
            if (preg_match('/.*([-_A-Za-z0-9]+).*/', $form_data['youtube']['value'], $matches)) {
                $y_id = $matches[0];
            }
        }
        $form_data['youtube']['value'] = $y_id;
        $form_data['price']['value'] = str_replace(' ', '', $form_data['price']['value']);
        //$form_data['date_added']['value'] = date('Y-m-d H:i:s', time());

        if (1 == $this->getConfigValue('apps.geodata.try_encode') && 1 == $this->getConfigValue('apps.geodata.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/geodata/admin/admin.php';
            $GA = new geodata_admin();
            $form_data = $GA->try_geocode($form_data);
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $queryp = $data_model->get_prepared_insert_query(DB_PREFIX . '_' . $this->table_name, $form_data, $language_id);

        $DBC = DBC::getInstance();

        $stmt = $DBC->query($queryp['q'], $queryp['p'], $row, $success_mark);
        if (!$success_mark) {
            $this->riseError($DBC->getLastError());
            return false;
        }

        $new_record_id = $DBC->lastInsertId();

        if ($new_record_id > 0 && isset($form_data['status_id'])) {
            $this->setStatusDate($new_record_id);
        }

        $imgs = array();

        foreach ($form_data as $form_item) {
            if ($form_item['type'] == 'uploads') {
                $ims = $this->appendUploads($this->table_name, $form_item, $this->primary_key, $new_record_id);
                if (is_array($ims) && count($ims) > 0 && 0 == intval($form_item['parameters']['no_watermark'])) {
                    $imgs = array_merge($imgs, $ims);
                }
            } elseif ($form_item['type'] == 'docuploads') {
                $imgs_uploads = $this->appendDocUploads($this->table_name, $form_item, $this->primary_key, $new_record_id);
            } elseif ($form_item['type'] == 'select_by_query_multi') {
                $vals = $form_item['value'];
                if (!is_array($vals)) {
                    $vals = (array) $vals;
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

        $ims = $this->editImageMulti('data', 'data', 'id', $new_record_id);
        if (is_array($ims) && count($ims) > 0) {
            $imgs = array_merge($imgs, $ims);
        }
        if (1 == $this->getConfigValue('apps.seo.data_alias_enable') && ((isset($form_data['translit_alias']) && $form_data['translit_alias']['value'] == '') || !isset($form_data['translit_alias']))) {
            $this->saveTranslitAlias($new_record_id);
        }

        if ($this->getConfigValue('is_watermark')) {
            $this->do_watermark($imgs);
        }

        $messenger_post = false;
        $DBC = DBC::getInstance();
        $query = 'SELECT `active` FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '`=?';
        $stmt = $DBC->query($query, array($new_record_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['active'] == 1) {
                $messenger_post = true;
            }
        }


        if ($this->getConfigValue('apps.twitter.enable')) {
            if ($messenger_post) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/twitter/admin/admin.php';
                $Twitter = new twitter_admin();
                $Twitter->sendTwit($new_record_id);
            }
        }
        if ($this->getConfigValue('apps.telegram.enable')) {
            if ($messenger_post) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/telegram/admin/admin.php';
                $Telegram = new telegram_admin();
                $Telegram->sendPost($new_record_id);
            }
        }
        if ($this->getConfigValue('apps.facebook.enable')) {
            if ($messenger_post) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/facebook/admin/admin.php';
                $Facebook = new facebook_admin();
                $Facebook->sendPost($new_record_id);
            }
        }




        $page = $this->getRequestValue('page');
        $_POST = array();
        $_POST['page'] = $page;
        return $new_record_id;
    }

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
        $params = array();
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

    function getAdditionalSearchForm() {
        $query = 'select * from re_user order by fio';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        $ret = '';
        if ($stmt) {
            $ret .= '<form method="post">';
            $ret .= '<select name="user_id" style="width: 200px;" onchange="this.form.submit()">';
            $ret .= '<option value="">' . Multilanguage::_('L_CHOOSE_USER') . '</option>';
            while ($ar = $DBC->fetch($stmt)) {
                if ($this->getRequestValue('user_id') == $ar['user_id']) {
                    $ret .= '<option value="' . $ar['user_id'] . '" selected="selected">' . $ar['login'] . ' (' . $ar['fio'] . ')</option>';
                } else {
                    $ret .= '<option value="' . $ar['user_id'] . '">' . $ar['login'] . ' (' . $ar['fio'] . ')</option>';
                }
            }
            $ret .= '</select>';
            $ret .= '<input type="hidden" name="action" value="' . $this->action . '">';
            $ret .= '<input type="submit" name="submit" value="' . Multilanguage::_('L_TEXT_SELECT') . '">';
            $ret .= '</form>';
        }
        return $ret;
    }

    function getUserSelectBox() {
        $query = 'select * from re_user order by fio';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        $ret = '';
        if ($stmt) {
            $ret .= '<select name="user_id" onchange="this.form.submit()">';
            $ret .= '<option value="">' . Multilanguage::_('L_CHOOSE_USER') . '</option>';
            while ($ar = $DBC->fetch($stmt)) {
                if ($this->getRequestValue('user_id') == $ar['user_id']) {
                    $ret .= '<option value="' . $ar['user_id'] . '" selected="selected">' . $ar['login'] . ' (' . $ar['fio'] . ')</option>';
                } else {
                    $ret .= '<option value="' . $ar['user_id'] . '">' . $ar['login'] . ' (' . $ar['fio'] . ')</option>';
                }
            }
            $ret .= '</select>';
        }

        /*$query = 'select * from re_user order by fio';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        $ret = '';
        if ($stmt) {
            $ret .= '<div class="nav-search" id="nav-search">';
			$ret .= '<form class="form-search">';
			$ret .= '<span class="input-icon">';
			$ret .= '<input type="text" placeholder="Search ..." class="input-small nav-search-input-users" id="nav-search-input" autocomplete="off" />';
			$ret .= '<i class="icon-search nav-search-icon"></i>';
			$ret .= '</span>';
			$ret .= '</form>';
			$ret .= '</div>';

        }*/

        return $ret;
    }

    function mass_delete_data($table_name, $primary_key, $ids) {
        $errors = '';
        if ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
            $cuser_id = (int) $_SESSION['user_id_value'];
            if (count($ids) > 0) {
                foreach ($ids as $k => $id) {
                    if (!$this->checkOwning($id, $cuser_id)) {
                        unset($ids[$k]);
                    }
                }
            }
        }

        if (count($ids) > 0) {
            if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting')) {

                $archived_yet = array();
                $DBC = DBC::getInstance();
                //Получаем список ID объектов, которые уже пребывают в архивном состоянии
                $query = 'SELECT `id` FROM ' . DB_PREFIX . '_data WHERE `id` IN (' . implode(',', $ids) . ') AND `archived` = 1';
                $stmt = $DBC->query($query);
                if($stmt){
                    while($ar = $DBC->fetch($stmt)){
                        $archived_yet[] = $ar['id'];
                    }
                }
                //Отбираем объекты, которые нужно перенести в архив
                $to_archive = array_diff($ids, $archived_yet);

                if(!empty($to_archive)){
                    $query = 'UPDATE ' . DB_PREFIX . '_data SET archived=1 WHERE `id` IN (' . implode(',', $to_archive) . ')';
                    $stmt = $DBC->query($query);
                }
                //Отправляем на удаление объекты, которые и так находились в архиве
                $ids = $archived_yet;
            }
        }

        if (count($ids) > 0) {
            foreach ($ids as $id) {
                $log_id = false;
                if ($this->getConfigValue('apps.realtylog.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/admin/admin.php';
                    $Logger = new realtylog_admin();
                    $log_id = $Logger->addLog($id, $_SESSION['user_id_value'], 'delete', $this->table_name);
                }
                if ($this->getConfigValue('apps.realtylogv2.enable')) {
                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                    $Logger = new realtylogv2_admin();
                    $log_id = $Logger->addLog($id, $_SESSION['user_id_value'], 'delete', $this->table_name, $this->primary_key);
                }
                $this->enable_shard_queue();
                $this->delete_data($this->table_name, $this->primary_key, $id);
                if ($this->getError()) {
                    if ($log_id !== false) {
                        $Logger->deleteLog($log_id);
                    }
                    $errors .= '<div align="center">' . Multilanguage::_('L_ERROR_ON_DELETE') . ' ID=' . $id . ': ' . $this->GetErrorMessage() . '<br>';
                    $errors .= '</div>';
                    $this->error_message = false;
                }
            }
            $this->run_shard_task();
            if ($errors != '') {
                $rs .= $errors . '<div align="center"><a href="?action=' . $this->action . '">ОК</a></div>';
            } else {
                header('location: ?action=' . $this->action);
                exit();
                $rs .= $this->grid($user_id);
            }
            return $rs;
        }

    }

    /**
     * Delete data
     * @param string $table_name
     * @param string $primary_key
     * @param int $primary_key_value
     */
    function delete_data($table_name, $primary_key, $primary_key_value) {
        $DBC = DBC::getInstance();
        $imgs_ids = array();
        $query = 'SELECT image_id FROM ' . DB_PREFIX . '_' . $table_name . '_image WHERE ' . $primary_key . '=?';

        $stmt = $DBC->query($query, array($primary_key_value));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $imgs_ids[] = $ar['image_id'];
            }
        }

        $delete_result = parent::delete_data($table_name, $primary_key, $primary_key_value);
        if ($delete_result) {
            if (count($imgs_ids) > 0) {
                foreach ($imgs_ids as $im) {
                    $this->deleteImage($table_name, $im);
                }
            }
            $query = 'DELETE FROM ' . DB_PREFIX . '_memorylist_item WHERE `id` = ?';
            $stmt = $DBC->query($query, array($primary_key_value));

            $query = 'DELETE FROM ' . DB_PREFIX . '_userlists WHERE `id` = ? AND `lcode` = ?';
            $stmt = $DBC->query($query, array($primary_key_value, 'fav'));
        }
        return $delete_result;
    }

    public function get_element($element_name) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        if (isset($form_data[$this->table_name][$element_name])) {
            $fd[$this->table_name][$element_name] = $form_data[$this->table_name][$element_name];
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
            $form_generator = new Form_Generator();
            $element_data = $form_generator->compile_form_elements($fd[$this->table_name], false);
            return $element_data['hash'][$element_name]['html'];
        }
        return '';
    }

    private function prepareVipStatsDateValue($current_vip_timestamp, $new_vip_timestamp) {
        $ret = 0;
        if ($current_vip_timestamp < time()) {
            $current_vip_timestamp = 0;
        }

        if ($current_vip_timestamp != 0) {
            $olddate = date('d.m.Y', $current_vip_timestamp);
            $oldtime = date('H:i:s', $current_vip_timestamp);
            $newdate = date('d.m.Y', $new_vip_timestamp);
            if ($newdate != $olddate) {
                $ret = strtotime($newdate . ' ' . $oldtime);
            } else {
                $ret = FALSE;
            }
        } else {
            if ($new_vip_timestamp == '' || $new_vip_timestamp == 0) {
                $ret = 0;
            } else {
                $newdate = date('d.m.Y', $new_vip_timestamp);
                $ret = strtotime($newdate . ' ' . date('H:i:s', time()));
            }
        }
        return $ret;
    }

    protected function _mass_deletebypropAction() {
        $rs = '';

        $prop = $this->getRequestValue('prop');
        $prop_value = $this->getRequestValue('prop_value');
        $DBC = DBC::getInstance();
        $query = 'SELECT id FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $prop . '`=?';
        $stmt = $DBC->query($query, array($prop_value));
        $id_array = array();
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $id_array[] = $ar['id'];
            }
        }

        if (!empty($id_array)) {
            $this->setRequestValue('ids', implode(',', $id_array));
        }
        $rs = $this->_mass_deleteAction();
        return $rs;
    }

    protected function _memorylistAction() {
        $rs = '';
        require_once SITEBILL_DOCUMENT_ROOT.'/apps/memorylist/admin/memory_list.php';
        $ML=new Memory_List();

        if ( $this->getRequestValue('subdo') == 'getpdf' ) {
            $memorylist_id = intval($this->getRequestValue('filter_id'));
            $domain = false;
            $stuff = false;
            if($this->getRequestValue('report_type') == 'staff'){
                $stuff = true;
            }

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/data/admin/admin.php');

            $ids = $ML->select_data_ids_by_memorylist_id($_SESSION['user_id'], $memorylist_id);
            $ML->compile_rich_pdf($ids, $stuff);

            $rs .= $ML->grid(array('admin_zone_url' => 1));
        } elseif ($this->getRequestValue('subdo') == 'showfilter') {
            $rs .= $ML->showfilter();
        } elseif ($this->getRequestValue('subdo') == 'delete') {
            $id = intval($this->getRequestValue('filter_id'));
            $ML->deleteMemorylist($id);
            $rs .= $ML->grid(array('admin_zone_url' => 1));
        }else {
            $rs .= $ML->grid(array('admin_zone_url' => 1));
        }



        return $rs;
    }

    protected function _duplicateAction() {
        $rs = '';
        $id_array = array();
        $ids = trim($this->getRequestValue('ids'));
        if ($ids != '') {
            $id_array = explode(',', $ids);
        }
        $rs .= $this->duplicate($this->table_name, $this->primary_key, $id_array);
        return $rs;
    }

    protected function _statAction() {
        global $smarty;


        $id = intval($this->getRequestValue('id'));

        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_stat_views_d WHERE id=? AND `object`=? ORDER BY `date` ASC';
        $stmt = $DBC->query($query, array($id, 'data'));

        $views = array();

        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $views[] = $ar;
            }
        }
        $max = 0;
        $counts = array();
        foreach ($views as $v) {
            $counts[] = $v['vcount'];
        }
        $max = max($counts);
        foreach ($views as $k => $v) {
            $views[$k]['prc'] = $v['vcount'] * 100 / $max;
        }

        $smarty->assign('views', $views);
        //$smarty->assign('view_data', $order_table);
        $html = $smarty->fetch($smarty->template_dir . "/realty_view_stat.tpl");
        return $html;
    }

    protected function _viewAction() {
        global $smarty;
        $id = intval($this->getRequestValue('id'));
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data_shared = $data_model->get_kvartira_model(false, true);
        $form_data_shared = $data_model->init_model_data_from_db('data', 'id', $id, $form_data_shared['data'], true);
        $form_data_shared = $data_model->applyGCompose($form_data_shared);

        if (!$form_data_shared) {
            return '';
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/view.php');
        $table_view = new Table_View();
        $order_table = '';
        $order_table .= '<table class="table">';
        $order_table .= $table_view->compile_view($form_data_shared);
        $order_table .= '</table>';

        $notes = array();
        $DBC = DBC::getInstance();
        $query = 'SELECT dn.*, u.fio FROM ' . DB_PREFIX . '_data_note dn LEFT JOIN ' . DB_PREFIX . '_user u USING(user_id) WHERE dn.id=? ORDER BY dn.added_at ASC';
        $stmt = $DBC->query($query, array($id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $notes[] = $ar;
            }
        }
        $smarty->assign('view_data_notes', $notes);
        $smarty->assign('view_data', $order_table);
        $html = $smarty->fetch($smarty->template_dir . "/realty_view.tpl");
        return $html;
    }

    protected function duplicate($table_name, $primary_key, $ids) {
        if (count($ids) == 0) {
            return;
        }
        $with_images = false;
        if (1 == (int) $this->getRequestValue('duplicate_images')) {
            $with_images = true;
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;

        foreach ($ids as $id) {
            $form_data[$this->table_name] = $data_model->init_model_data_from_db($this->table_name, $this->primary_key, $id, $form_data[$this->table_name]);
            if ($with_images) {
                $hasUploadify = false;
                $uploads = array();

                foreach ($form_data[$this->table_name] as $key => $item) {
                    if ($item['type'] == 'uploadify_image') {
                        $hasUploadify = true;
                        $images = array();
                        if (count($item['image_array']) > 0) {
                            $i = 1;
                            foreach ($item['image_array'] as $img) {
                                $preview = $img['preview'];
                                $normal = $img['normal'];

                                $parts = explode('.', $normal);
                                $normal_name = "img" . uniqid() . '_' . time() . "_" . $i . "." . end($parts);
                                reset($parts);
                                $parts = explode('.', $preview);
                                $preview_name = "prv" . uniqid() . '_' . time() . "_" . $i . "." . end($parts);
                                reset($parts);
                                copy(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $normal, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $normal_name);
                                copy(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $preview, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $preview_name);

                                $images[] = array('normal' => $normal_name, 'preview' => $preview_name);
                                $i++;
                            }
                        }
                    } elseif ($item['type'] == 'uploads') {
                        if (is_array($item['value']) && count($item['value']) > 0) {
                            $i = 1;
                            foreach ($item['value'] as $k => $img) {
                                $preview = $img['preview'];
                                $normal = $img['normal'];

                                $parts = explode('.', $normal);
                                $normal_name = "img" . uniqid() . '_' . time() . "_" . $i . "." . end($parts);
                                reset($parts);
                                $parts = explode('.', $preview);
                                $preview_name = "prv" . uniqid() . '_' . time() . "_" . $i . "." . end($parts);
                                reset($parts);
                                copy(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $normal, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $normal_name);
                                copy(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $preview, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $preview_name);
                                $form_data[$this->table_name][$key]['value'][$k]['normal'] = $normal_name;
                                $form_data[$this->table_name][$key]['value'][$k]['preview'] = $preview_name;
                                $i++;
                            }
                            $uploads[$key] = serialize($form_data[$this->table_name][$key]['value']);
                        }
                    }

                    if ($item['name'] == 'translit_alias') {
                        $form_data[$this->table_name][$key]['value'] .= '-' . time();
                    }
                }
            } else {
                foreach ($form_data[$this->table_name] as $k => $item) {
                    if ($item['type'] == 'uploads') {
                        $form_data[$this->table_name][$k]['value'] = '';
                    }
                    if ($item['name'] == 'translit_alias') {
                        $form_data[$this->table_name][$k]['value'] .= '-' . time();
                    }
                }
            }

            if (function_exists('BeforeDuplicate')) {
                $form_data[$this->table_name] = BeforeDuplicate($form_data[$this->table_name]);
            }


            $form_data[$this->table_name][$primary_key]['value'] == '';
            $new_record_id = $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
            if ($this->getError()) {
                echo $this->getErrorMessage() . '<br>';
            }
            if ($with_images && $hasUploadify && count($images) > 0) {
                $this->add_image_records($images, $this->table_name, $this->primary_key, $new_record_id);
            }
            if ($with_images && !empty($uploads)) {
                $DBC = DBC::getInstance();
                $query = 'UPDATE ' . DB_PREFIX . '_data SET';
                foreach ($uploads as $ku => $kv) {
                    $query .= ' `' . $ku . '`=?';
                }
                $query .= ' WHERE ' . $this->primary_key . '=' . $new_record_id;
                $stmt = $DBC->query($query, array_values($uploads));
            }
        }
        return $this->_defaultAction();
    }

    protected function batch_update($table_name, $primary_key) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
        foreach ($form_data[$this->table_name] as $key => $value) {
            if ($value['type'] == 'attachment' || $value['type'] == 'photo' || $value['type'] == 'uploadify_image' || $value['type'] == 'uploads' || $value['type'] == 'avatar' || $value['type'] == 'docuploads') {
                unset($form_data[$this->table_name][$key]);
            }
        }
        if (isset($_REQUEST['submit'])) {
            $need_to_update = $this->getRequestValue('batch_update');
            $ids = $this->getRequestValue('batch_ids');
            if ((1 === (int) $this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name'] !== 'admin') && (1 === (int) $this->getConfigValue('data_adv_share_access'))) {
                $cuser_id = (int) $_SESSION['user_id_value'];
                if (count($ids) > 0) {
                    foreach ($ids as $k => $id) {
                        if (!$this->checkOwning($id, $cuser_id)) {
                            unset($ids[$k]);
                        }
                    }
                }
            }

            if (count($ids) < 1) {
                return $this->grid();
            }

            if (count($need_to_update) < 1) {
                return $this->grid();
            }

            $sub_form = array();
            foreach ($need_to_update as $key => $value) {
                if (isset($form_data[$this->table_name][$key])) {
                    $sub_form[$this->table_name][$key] = $form_data[$this->table_name][$key];
                }
            }

            if (empty($sub_form)) {
                return $this->grid();
            }

            $sub_form[$this->table_name] = $data_model->init_model_data_from_request($sub_form[$this->table_name]);
            $new_values = $this->getRequestValue('_new_value');
            if (1 == $this->getConfigValue('use_combobox') && count($new_values) > 0) {
                $remove_this_names = array();
                foreach ($sub_form[$this->table_name] as $fd) {
                    if (isset($new_values[$fd['name']]) && $new_values[$fd['name']] != '' && $fd['combo'] == 1) {
                        $id = md5(time() . '_' . rand(100, 999));
                        $remove_this_names[] = $id;
                        $sub_form[$this->table_name][$id]['value'] = $new_values[$fd['name']];
                        $sub_form[$this->table_name][$id]['type'] = 'auto_add_value';
                        $sub_form[$this->table_name][$id]['dbtype'] = 'notable';
                        $sub_form[$this->table_name][$id]['value_table'] = $form_data[$this->table_name][$fd['name']]['primary_key_table'];
                        $sub_form[$this->table_name][$id]['value_primary_key'] = $sub_form[$this->table_name][$fd['name']]['primary_key_name'];
                        $sub_form[$this->table_name][$id]['value_field'] = $sub_form[$this->table_name][$fd['name']]['value_name'];
                        $sub_form[$this->table_name][$id]['assign_to'] = $fd['name'];
                        $sub_form[$this->table_name][$id]['required'] = 'off';
                        $sub_form[$this->table_name][$id]['unique'] = 'off';
                    }
                }
            }
            $data_model->forse_auto_add_values($sub_form[$this->table_name]);
            if (!$this->check_data($sub_form[$this->table_name])) {
                $sub_form['data'] = $this->removeTemporaryFields($sub_form['data'], $remove_this_names);
                $rs = $this->get_batch_update_form($form_data[$this->table_name], $ids, $need_to_update);
            } else {
                foreach ($ids as $id) {
                    $concrete_form = $sub_form;
                    $concrete_form[$this->table_name][$this->primary_key]['value'] = $id;
                    $concrete_form[$this->table_name][$this->primary_key]['type'] = 'primary_key';
                    $this->edit_data($concrete_form[$this->table_name]);
                    if ($this->getError()) {
                        //$form_data['data']=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
                        //$rs = $this->get_form($form_data[$this->table_name], 'edit');
                    } else {
                        if ($this->getConfigValue('apps.realtylog.enable')) {
                            require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylog/admin/admin.php';
                            $Logger = new realtylog_admin();
                            $Logger->addLog($concrete_form[$this->table_name][$this->primary_key]['value'], $_SESSION['user_id_value'], 'edit', $this->table_name);
                        }
                        if ($this->getConfigValue('apps.realtylogv2.enable')) {
                            require_once SITEBILL_DOCUMENT_ROOT . '/apps/realtylogv2/admin/admin.php';
                            $Logger = new realtylogv2_admin();
                            $Logger->addLog($concrete_form[$this->table_name][$this->primary_key]['value'], $_SESSION['user_id_value'], 'edit', $this->table_name, $this->primary_key);
                        }
                    }
                }
                $rs .= $this->grid();
            }
        } else {
            $ids = $this->getRequestValue('batch_ids');
            $rs .= $this->get_batch_update_form($form_data[$this->table_name], explode(',', $ids));
        }
        return $rs;
    }

    function get_batch_update_form($form_data = array(), $ids = array(), $selected_fields = array(), $action = 'index.php') {
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
        $el['private'][] = array('html' => '<input type="hidden" name="do" value="batch_update" />');
        $el['private'][] = array('html' => '<input type="hidden" name="action" value="' . $this->action . '">');
        $el['private'][] = array('html' => '<input type="hidden" name="language_id" value="' . $language_id . '">');

        foreach ($ids as $id) {
            $el['private'][] = array('html' => '<input type="hidden" name="batch_ids[]" value="' . $id . '">');
        }
        $el['form_header'] = $rs;
        $el['form_footer'] = '</form>';
        $el['controls']['submit'] = array('html' => '<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">' . $button_title . '</button>');

        $smarty->assign('selected_fields', $selected_fields);
        $smarty->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data_batch_update.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data_batch_update.tpl';
        } else {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/apps/admin/admin/template/data_form_batch_update.tpl';
        }
        return $smarty->fetch($tpl_name);
    }

}
