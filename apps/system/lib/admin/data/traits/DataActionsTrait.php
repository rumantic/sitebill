<?php
/**
 * DataActionsTrait — Miscellaneous action methods for Data_Manager.
 *
 * Extracted from data_manager.php during refactoring.
 * Methods: _defaultAction(), structure_processor(), update_table(), _subobjAction(),
 *          _set_statusAction(), setStatusState(), setStatusDate(), _statAction(),
 *          _viewAction(), _duplicateAction(), duplicate(), batch_update(),
 *          _memorylistAction(), _mass_deletebypropAction()
 */
trait DataActionsTrait
{
    protected function _defaultAction() {
        if ( $this->getConfigValue('apps.realty.use_predeleting') ) {
            $data_model = new Data_Model();
            $model = $data_model->get_kvartira_model(false, true);
            if ( !isset($model['data']['archived']) ) {
                return _e('Вы включили опцию').' apps.realty.use_predeleting<br> '.
                    _e('Но для корректной работы нужно добавить поле archived в таблицу data с типом hidden');
            }


        }
        return parent::_defaultAction();
    }


    function structure_processor() {
        if ($this->getRequestValue('subdo') === 'sms') {
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
        $html = $smarty->fetch($this->get_smarty_template_dir() . "/realty_view_stat.tpl");
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
        $html = $smarty->fetch($this->get_smarty_template_dir() . "/realty_view.tpl");
        return $html;
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
                                if ( $img['remote'] === 'true' ) {
                                    if ( $this->getConfigValue('apps.sharder.api_key') or $this->getConfigValue('apps.sharder.s3.enable')  ) {
                                        if (!is_object($this->sharder)) {
                                            $this->sharder = new \sharder\lib\sharder();
                                        }
                                        $normal_name = $this->sharder->getCloudCopy($normal);
                                        $preview_name = $this->sharder->getCloudCopy($preview);
                                        $form_data[$this->table_name][$key]['value'][$k]['remote'] = 'true';

                                    } else {
                                        echo 'Копирование облачных файлов недоступно<br>';
                                        exit();
                                    }
                                } else {
                                    $parts = explode('.', $normal);
                                    $normal_name = "img" . uniqid() . '_' . time() . "_" . $i . "." . end($parts);
                                    reset($parts);
                                    $parts = explode('.', $preview);
                                    $preview_name = "prv" . uniqid() . '_' . time() . "_" . $i . "." . end($parts);
                                    reset($parts);
                                    copy(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $normal, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $normal_name);
                                    copy(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $preview, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $preview_name);
                                }

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
            if (1 == $this->getConfigValue('use_combobox') && is_array($new_values) && count($new_values) > 0) {
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
}
