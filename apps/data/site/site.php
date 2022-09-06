<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * data fronend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class data_site extends data_admin
{


    function __construct()
    {
        parent::__construct();
        if ($this->getConfigValue('hide_contact_input_user_data')) {
            unset($this->data_model['data']['fio']);
            unset($this->data_model['data']['phone']);
            unset($this->data_model['data']['email']);
        }
    }

    function frontend()
    {
        return false;
    }

    function main()
    {
        $uid = $this->getSessionUserId();
        if ($uid == 0 or !isset($uid)) {
            $rs = Multilanguage::_('L_ACCESS_DENIED');
            return $rs;
        }
        if (!$this->allowAddButton() and ($this->getRequestValue('do') == 'new' or $this->getRequestValue('do') == 'new_done')) {
            return '';
        }
        $this->template->assert('search_form', $this->get_search_form());
        return parent::main();
    }

    protected function _exportPhotoAction()
    {

        $id = intval($this->getRequestValue('id'));
        $user_id = intval($this->getSessionUserId());

        $aggregroup = -1;
        $cgroup_id = intval($_SESSION['current_user_group_id']);

        $rs = '';
        if ($cgroup_id == $aggregroup) {
            if (!$this->check_access_to_aggregated_data($user_id, $id)) {
                return Multilanguage::_('L_ACCESS_DENIED');
            }
        } elseif (!$this->check_access_to_data($user_id, $id)) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }

        $this->get_photos($id);
    }

    protected function _exportPhotoClearAction()
    {
        $id = intval($this->getRequestValue('id'));
        $user_id = intval($this->getSessionUserId());

        $aggregroup = -1;
        $cgroup_id = intval($_SESSION['current_user_group_id']);

        $rs = '';
        if ($cgroup_id == $aggregroup) {
            if (!$this->check_access_to_aggregated_data($user_id, $id)) {
                return Multilanguage::_('L_ACCESS_DENIED');
            }
        } elseif (!$this->check_access_to_data($user_id, $id)) {
            return Multilanguage::_('L_ACCESS_DENIED');
        }

        $this->get_photos($id, true);
    }

    function get_photos($id, $clearprotect = false)
    {

        $DBC = DBC::getInstance();
        //$isprotected = false;

        $query = 'SELECT image FROM ' . DB_PREFIX . '_data WHERE id = ? AND image <> ?';
        $stmt = $DBC->query($query, array($id, ''));
        if (!$stmt) {
            exit();
        }
        $ar = $DBC->fetch($stmt);
        $images = unserialize($ar['image']);

        if (empty($images)) {
            return false;
        }


        $zip = new ZipArchive();
        $zip_name = "photos_" . $id . '_' . time() . ".zip";
        $zip->open($zip_name, ZIPARCHIVE::CREATE);

        $exported = array();

        if ($clearprotect && 1 == intval($this->getConfigValue('watermark_user_control'))) {
            $fold = $this->notwatermarked_folder;
            if ( $this->nowatermark_folder_with_id) {
                $fold = $fold . $id . '/';
            }
            foreach ($images as $photo) {
                if (file_exists($fold . $photo['normal'])) {
                    $exported[] = array($fold . $photo['normal'], $photo['normal']);
                } else {
                    $exported[] = array(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $photo['normal'], $photo['normal']);
                }
            }
        } elseif ($clearprotect && 0 == intval($this->getConfigValue('watermark_user_control'))) {
            $fold = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/';

            foreach ($images as $photo) {
                if (file_exists($fold . $photo['normal'])) {
                    $exported[] = array($fold . $photo['normal'], $photo['normal']);
                } else {
                    $exported[] = array(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $photo['normal'], $photo['normal']);
                }
            }
        } else {
            $j = 0;
            foreach ($images as $photo) {
                $j++;
                if ($photo['remote'] === 'true') {
                    $pathinfo = pathinfo($photo['normal']);
                    $file_name = $j . '.' . $pathinfo['extension'];
                    $exported[] = array($fold . $photo['normal'], $photo['normal'], 1);
                } else {
                    $exported[] = array(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $photo['normal'], $photo['normal']);
                }
            }
        }

        foreach ($exported as $exp) {
            if (isset($exp[2]) && $exp[2] == 1) {
                $zip->addFromString($exp[0], file_get_contents($exp[1]));
            } else {
                $zip->addFile($exp[0], $exp[1]);
            }

        }

        $zip->close();
        if (file_exists($zip_name)) {
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);
            header('Content-type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zip_name . '"');
            readfile($zip_name);
            unlink($zip_name);
        }
        exit();
    }

    protected function checkOwning($id, $user_id)
    {
        if (!is_array($id)) {
            $id = (array)$id;
        }
        $DBC = DBC::getInstance();
        $query = 'SELECT `' . $this->primary_key . '` FROM ' . DB_PREFIX . '_' . $this->table_name . ' WHERE `' . $this->primary_key . '` IN (' . implode(',', $id) . ') AND `user_id`=?';

        $stmt = $DBC->query($query, array($user_id));
        $owned = array();
        $res = false;
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $owned[] = $ar[$this->primary_key];
            }
        }
        return $owned;
    }

    function get_app_title_bar()
    {
        $breadcrumbs = array();
        $breadcrumbs[] = array('href' => $this->createUrlTpl(''), 'title' => Multilanguage::_('L_HOME'));
        $breadcrumbs[] = array('href' => $this->createUrlTpl('account'), 'title' => _e('Личный кабинет'));

        $this->template->assign('breadcrumbs_array_structured', $breadcrumbs);
        return '';
    }


    function _mass_actionAction()
    {
        $action = trim($this->getRequestValue('action_name'));


        switch ($action) {
            case 'activate' :
            {
                if (!isset($this->data_model[$this->table_name]['active'])) {

                    return $this->grid();
                }


                $ids = $this->getRequestValue('ids');

                $cuser_id = (int)$_SESSION['user_id'];
                if (count($ids) > 0) {
                    $ids = $this->checkOwning($ids, $cuser_id);
                }

                if (count($ids) < 1) {
                    return $this->grid();
                }

                if (1 == $this->getConfigValue('moderate_first')) {
                    return $this->grid();
                }

                $DBC = DBC::getInstance();
                $query = 'UPDATE ' . DB_PREFIX . '_data SET active=1 WHERE id IN (' . implode(',', $ids) . ')';
                $stmt = $DBC->query($query, array(), $rows, $success_mark);

                if ($success_mark && 0 === intval($this->getConfigValue('apps.billing.enable'))) {
                    foreach ($ids as $id) {
                        $this->setUpdatedAtDate($id);
                    }
                }
                header('location:' . SITEBILL_MAIN_URL . '/'.$this->get_app_root() . self::$_trslashes);
                exit();
                return $this->grid();
                break;
            }
            case 'deactivate' :
            {
                if (!isset($this->data_model[$this->table_name]['active'])) {
                    return $this->grid();
                }


                $ids = $this->getRequestValue('ids');
                //print_r($ids);
                $cuser_id = (int)$_SESSION['user_id'];
                if (count($ids) > 0) {
                    $ids = $this->checkOwning($ids, $cuser_id);
                }

                if (count($ids) < 1) {
                    return $this->grid();
                }

                $DBC = DBC::getInstance();
                $query = 'UPDATE ' . DB_PREFIX . '_data SET active=0 WHERE id IN (' . implode(',', $ids) . ')';
                $stmt = $DBC->query($query, array(), $rows, $success_mark);

                if ($success_mark && 0 === intval($this->getConfigValue('apps.billing.enable'))) {
                    foreach ($ids as $id) {
                        $this->setUpdatedAtDate($id);
                    }
                }

                header('location:' . SITEBILL_MAIN_URL . '/'.$this->get_app_root() . self::$_trslashes);
                exit();
                break;
            }
        }
        echo 1;
    }

    function _batch_updateAction()
    {
        if ($this->getConfigValue('apps.data.disable_edit_button')) {
            $this->riseError(_e('Функция редактирования отключена'));
            return false;
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $this->data_model;
        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
        foreach ($form_data[$this->table_name] as $key => $value) {
            if ($value['type'] == 'attachment' || $value['type'] == 'photo' || $value['type'] == 'uploadify_image' || $value['type'] == 'uploads' || $value['type'] == 'avatar' || $value['type'] == 'docuploads' || $value['type'] == 'captcha') {
                unset($form_data[$this->table_name][$key]);
            }
        }

        if (isset($_POST['submit'])) {
            $need_to_update = $this->getRequestValue('batch_update');
            $ids = $this->getRequestValue('batch_ids');
            $cuser_id = (int)$_SESSION['user_id'];
            if (count($ids) > 0) {
                $ids = $this->checkOwning($ids, $cuser_id);
                /* foreach ($ids as $k => $id) {
                  if (!$this->checkOwning($id, $cuser_id)) {
                  unset($ids[$k]);
                  }
                  } */
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

            //print_r($sub_form);

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

                    $r = $this->edit_data($concrete_form[$this->table_name]);
                    //var_dump($this->getError());
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
                header('location:' . SITEBILL_MAIN_URL . '/'.$this->get_app_root() . self::$_trslashes);
                exit();
                $rs .= $this->grid();
            }
        } else {
            $ids = $this->getRequestValue('batch_ids');
            $rs = $this->get_batch_update_form($form_data[$this->table_name], explode(',', $ids));
        }
        return $rs;
    }

    function get_batch_update_form($form_data = array(), $ids = array(), $selected_fields = array(), $action = 'index.php')
    {
        $rs = '';
        $_SESSION['allow_disable_root_structure_select'] = true;
        global $smarty;
        $button_title = Multilanguage::_('L_TEXT_SAVE');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();
        $rs .= $this->get_ajax_functions();
        if (1 == $this->getConfigValue('apps.geodata.enable')) {
            $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/geodata/js/geodata.js"></script>';
        }
        $rs .= '<form method="post" class="form-horizontal" action="' . SITEBILL_MAIN_URL . '/'.$this->get_app_root() . self::$_trslashes . '" enctype="multipart/form-data">';
        if ($this->getError()) {
            $smarty->assign('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
        }
        $el = $form_generator->compile_form_elements($form_data);
        $el['private'][] = array('html' => '<input type="hidden" name="do" value="batch_update" />');
        $el['private'][] = array('html' => '<input type="hidden" name="action" value="' . $this->action . '">');
        //$el['private'][] = array('html' => '<input type="hidden" name="language_id" value="' . $language_id . '">');

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
            $tpl_name = $this->getAdminTplFolder() . '/data_form_batch_update.tpl';
        }
        return $smarty->fetch($tpl_name);
    }

    function _eexportAction()
    {
        $params['user_id'] = $_SESSION['user_id'];


        $grid_constructor = $this->_getGridConstructor();


        $params['admin'] = true;
        $res = $grid_constructor->get_sitebill_adv_ext($params);
        global $smarty;

        $tplfile = 'data_grid.tpl';

        $smarty->assign('grid_items', $res['data']);
        $html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/pdfreport/admin/template/data_grid.tpl');
        require_once(SITEBILL_DOCUMENT_ROOT . "/apps/pdfreport/lib/dompdf/dompdf_config.inc.php");
        $dompdf = new DOMPDF();
        $dompdf->set_paper('A4', 'landscape');
        $dompdf->load_html($html);
        $dompdf->render();

        $output = $dompdf->output();
        header("Content-type: application/pdf");
        echo $output;
        exit();
    }

    function get_form($form_data = array(), $do = 'new', $language_id = 0, $button_title = '', $action = '?')
    {

        $rs = '';
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
        $el['form_header_action'] = $action;
        $el['form_header_class'] = 'form-horizontal';
        $el['form_header_enctype'] = 'multipart/form-data';
        $el['form_footer'] = '</form>';

        /* if ( $do != 'new' ) {
          $el['controls']['apply']=array('html'=>'<button id="apply_changes" class="btn btn-info">'.Multilanguage::_('L_TEXT_APPLY').'</button>');
          } */
        $el['controls']['submit'] = array('html' => '<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">' . $button_title . '</button>');


        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data_front.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data_front.tpl';
        } else {
            $tpl_name = $this->getAdminTplFolder() . '/data_form_front.tpl';
        }

        $smarty->assign('form_elements', $el);
        /* if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl')){
          $tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl';
          }else{
          $tpl_name=$this->getAdminTplFolder().'/data_form.tpl';
          } */
        return $smarty->fetch($tpl_name);
    }

    function add_data($form_data, $language_id = 0)
    {
        $curator_id = 0;
        $user_id = intval($_SESSION['user_id']);
        if (1 == $this->getConfigValue('enable_curator_mode') && 0 === intval($this->getConfigValue('curator_mode_fullaccess'))) {
            $DBC = DBC::getInstance();
            $query = 'SELECT parent_user_id FROM ' . DB_PREFIX . '_user WHERE user_id=?';
            $stmt = $DBC->query($query, array($user_id));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                if (intval($ar['parent_user_id']) > 0) {
                    $curator_id = intval($ar['parent_user_id']);
                }
            }
        }

        $new_record_id = parent::add_data($form_data, $language_id);

        if ($new_record_id && $curator_id > 0) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/components/cowork/cowork.php';
            $CW = new Cowork();
            $CW->setCoworkerToObject($this->table_name, $new_record_id, $curator_id);
        }
        if ($this->getConfigValue('apps.data.notify_admin_added')) {
            $this->notifyAdmin($new_record_id);
        }
        return $new_record_id;
    }

    function notifyAdmin($data_id)
    {
        //@todo: Сделать табличку с подробной инфой об объекте
        $body = $this->getRealtyHREF($data_id);
        $to = $this->getConfigValue('order_email_acceptor');
        $subject = $_SERVER['HTTP_HOST'] . _e(': новое объявление в ЛК');
        $this->sendFirmMail($to, $this->getConfigValue('system_email'), $subject, $body);
    }

    protected function _formatgridAction()
    {

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
        $smarty->assign('bootstrap_version', intval($this->getConfigValue('bootstrap_version')));
        $smarty->assign('model_fields', $model_fields);
        $ret = $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/system/template/grid/grid_fields_managing.tpl');
        return $ret;
    }

    protected function _getpdfAction()
    {

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
        $grid_array = $common_grid->degradate_grid($grid_array);
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
    function getTopMenu()
    {


        $state = '';
        if (isset($_GET['active']) && $_GET['active'] == 1) {
            $state = 'active';
        } elseif (isset($_GET['active']) && $_GET['active'] == 0) {
            $state = 'notactive';
        }

        $REQUESTURIPATH = $this->getClearRequestURI();
        if ($REQUESTURIPATH == 'account/data/all') {
            $state = 'all';
        }

        if ( $this->request()->get('state') == 'all' ) {
            $state = 'all';
        }


        $rs = '';
        if ($this->allowAddButton()) {
            $rs .= '<a href="' . $this->createUrlTpl($REQUESTURIPATH . '?action=' . $this->action . '&do=new') . '" class="btn">' . Multilanguage::_('L_ADD_RECORD_BUTTON') . '</a> ';
        }

        if (!$this->getConfigValue('apps.data.disable_memory_button')) {
            $rs .= '<a href="' . $this->createUrlTpl('memorylist') . '" class="btn">' . _e('Подборки') . '</a> ';
        }


        $rs .= '<div class="btn-group" role="group" aria-label="...">';
        if (!$this->getConfigValue('apps.data.disable_all_button') && !$this->is_default_app_root() ) {
            if ( !$this->getConfigValue('apps.data.remove_only_all_button') ) {
                $rs .= '<a href="' . $this->createUrlTpl($this->get_app_root().'/?state=all') . '" class="btn' . ($state == 'all' ? ' btn-primary btn-current' : '') . '">' . Multilanguage::_('L_ALL') . '</a> ';
            }
        }
        if (!$this->getConfigValue('apps.data.disable_all_button') && $this->is_default_app_root() ) {
            if ( !$this->getConfigValue('apps.data.remove_only_all_button') ) {
                $rs .= '<a href="' . $this->createUrlTpl($this->get_app_root().'/all/') . '" class="btn' . ($state == 'all' ? ' btn-primary btn-current' : '') . '">' . Multilanguage::_('L_ALL') . '</a> ';
            }
        }

        if ($this->getConfigValue('apps.data.allow_postponded') ) {
            $rs .= '<a href="' . $this->createUrlTpl($this->get_app_root().'/?postponded=1') . '" class="btn' .
                ($this->request()->get('postponded') ? ' btn-primary btn-current' : '') . '">' . _e('Отложенные') . '</a> ';
            if ( $this->request()->get('postponded') ) {
                $state = 'postponded';
            }

        }


        $rs .= '<a href="' . $this->createUrlTpl($this->get_app_root()) . '" class="btn' . ($state == '' ? ' btn-primary btn-current' : '') . '">' . _e('Все мои') . '</a>
            <a href="' . $this->createUrlTpl($this->get_app_root().'/?active=1') . '" class="btn' . ($state == 'active' ? ' btn-primary btn-current' : '') . '">' . _e('Активные') . '</a>
            <a href="' . $this->createUrlTpl($this->get_app_root().'/?active=0') . '" class="btn' . ($state == 'notactive' ? ' btn-primary btn-current' : '') . '">' . _e('В архиве') . '</a>';
        $rs .= '</div>';
        if (1 == $this->getConfigValue('apps.yandexrealty.allow_personal_feeds')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/yandexrealty/admin/admin.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/yandexrealty/site/site.php');
            $yandexrealty_site = new yandexrealty_site();
            $yandexrealty_user_feed_url = $yandexrealty_site->get_user_id_feed_url($this->getSessionUserId());
            if ($yandexrealty_user_feed_url) {
                $rs .= '<a href="' . $yandexrealty_user_feed_url . '" target="_blank" class="btn">' . _e('Ваш XML-фид') . '</a> ';
            }

        }


        //$rs .= '</div>';
        //$rs .= '<form method="post"><input type="hidden" name="action" value="add" /><input type="submit" name="submit" value="Добавить объявление" /></form>';
        if ($this->getRequestValue('do') == '') {
            return $rs;
        }
    }

    function allowAddButton()
    {
        if ($this->getConfigValue('apps.data.disable_add_button')) {
            return false;
        }
        if ($this->getConfigValue('apps.data.allow_add_button_group_list') != '') {
            $user_group_id = $this->permission->get_user_group_id($this->getSessionUserId());
            $allow_groups = explode(',', $this->getConfigValue('apps.data.allow_add_button_group_list'));
            if (in_array($user_group_id, $allow_groups)) {
                return true;
            }
            return false;
        }
        return true;
    }

    private function set_toolbar_buttons () {
        if ($this->getConfigValue('apps.pdfreport.enabled')) {
            $this->template->assign('pdf_enable', 1);
        }
        if ($this->getConfigValue('apps.data.disable_excel_import')) {
            $this->template->assign('disable_excel_import', 1);
        }
        if ($this->getConfigValue('apps.data.disable_excel_export')) {
            $this->template->assign('disable_excel_export', 1);
        }
        if ($this->getConfigValue('apps.data.disable_format_grid')) {
            $this->template->assign('disable_format_grid', 1);
        }
        if ($this->getConfigValue('apps.data.disable_pdf')) {
            $this->template->assign('disable_pdf', 1);
        }
    }

    private function set_session_politics () {
        //Устанавливаем параметр USER_ID для функции импорта XLS файла.
        //Чтобы при загрузке из XLS пользоатель не смог получить доступ к чужим записям
        $_SESSION['politics']['data']['check_access'] = true;
        $_SESSION['politics']['data']['user_id'] = $this->getSessionUserId();

    }

    private function get_cowork_panel ($params, $default_params) {
        $coworked = array();
        $coworked_users = array();

        $enable_curator_mode = intval($this->getConfigValue('enable_curator_mode'));

        if ($enable_curator_mode) {

            $cowork_mode = trim($this->getRequestValue('cowork_mode'));
            if (!is_numeric($cowork_mode)) {
                $cowork_mode = '';
            }

            $cowork_panel = '';

            $DBC = DBC::getInstance();

            if (1 === intval($this->getConfigValue('curator_mode_fullaccess'))) {
                $query = 'SELECT user_id FROM ' . DB_PREFIX . '_user WHERE parent_user_id=?';
                $stmt = $DBC->query($query, array($this->getSessionUserId()));
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $coworked_users[$ar['user_id']] = array();
                    }
                }

                if (!empty($coworked_users)) {
                    $query = 'SELECT id FROM ' . DB_PREFIX . '_data WHERE user_id IN (' . implode(',', array_keys($coworked_users)) . ')';
                    $stmt = $DBC->query($query);
                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            $coworked[] = $ar['id'];
                        }
                    }
                }
            } else {
                $query = 'SELECT id FROM ' . DB_PREFIX . '_cowork WHERE coworker_id=? AND object_type=?';
                $stmt = $DBC->query($query, array($this->getSessionUserId(), 'data'));
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $coworked[] = $ar['id'];
                    }
                }

                if (!empty($coworked)) {
                    $query = 'SELECT DISTINCT user_id FROM ' . DB_PREFIX . '_data WHERE id IN(' . implode(',', array_values($coworked)) . ')';
                    $stmt = $DBC->query($query);
                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            if ($ar['user_id'] > 0) {
                                $coworked_users[$ar['user_id']] = array();
                            }

                        }
                    }
                }
            }

            if (!empty($coworked_users)) {

                $users = array();
                /*
                $cowork_panel .= '<div class="btn-group" role="group">';
                $cowork_panel .= '<a class="btn btn-primary" '.($cowork_mode == '' ? 'disabled="disabled"' : '').' href="'.SITEBILL_MAIN_URL.'/account/data'.self::$_trslashes.'">Все</a>';
                $cowork_panel .= '<a class="btn btn-primary" '.($cowork_mode == '0' ? 'disabled="disabled"' : '').' href="'.SITEBILL_MAIN_URL.'/account/data'.self::$_trslashes.'?cowork_mode=0">Только мои</a>';
                */
                $query = 'SELECT fio, user_id FROM ' . DB_PREFIX . '_user WHERE user_id IN(' . implode(',', array_keys($coworked_users)) . ')';
                $stmt = $DBC->query($query);
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $users[] = $ar;
                    }
                }
                /*
                foreach($users as $ar){
					$cowork_panel .= '<a class="btn btn-primary" '.($cowork_mode == $ar['user_id'] ? 'disabled="disabled"' : '').' href="'.SITEBILL_MAIN_URL.'/account/data'.self::$_trslashes.'?cowork_mode='.$ar['user_id'].'">'.$ar['fio'].'</a>';
                }
				*/

                $rowclass = 'row-fluid';
                $colclass = 'span4';
                if ('3' == $this->getConfigValue('bootstrap_version')) {
                    $rowclass = 'row';
                    $colclass = 'col-md-4';
                }
                $cowork_panel .= '<div>';
                $cowork_panel .= '<form id="cowork_filter" action="' . $this->createUrlTpl($this->get_app_root()) . '" method="get">';
                $cowork_panel .= '<div class="' . $rowclass . '">';
                $cowork_panel .= '<div class="' . $colclass . '">';
                $cowork_panel .= '<select name="cowork_mode">';
                $cowork_panel .= '<option value="">'._e('Все').'</option>';
                $cowork_panel .= '<option ' . ($cowork_mode == '0' ? 'selected="selected"' : '') . ' value="0">'._e('Только мои').'</option>';
                foreach ($users as $ar) {
                    $cowork_panel .= '<option ' . ($cowork_mode == $ar['user_id'] ? 'selected="selected"' : '') . ' value="' . $ar['user_id'] . '">' . $ar['fio'] . '</option>';
                }
                $cowork_panel .= '</select>';
                $cowork_panel .= '</div>';
                $active = trim($this->getRequestValue('active'));
                $cowork_panel .= '<div class="' . $colclass . '">';
                $cowork_panel .= '<select name="active">';
                $cowork_panel .= '<option ' . ($active == '' ? 'selected="selected"' : '') . ' value="">'._e('Любое состояние').'</option>';
                $cowork_panel .= '<option ' . ($active == '1' ? 'selected="selected"' : '') . ' value="1">'._e('Активные').'</option>';
                $cowork_panel .= '<option ' . ($active == '0' ? 'selected="selected"' : '') . ' value="0">'._e('В архиве').'</option>';
                $cowork_panel .= '</select>';
                $cowork_panel .= '</div>';
                $cowork_panel .= '<div class="' . $colclass . '">';
                $cowork_panel .= '<input type="submit" value="'._e('Показать').'" class="btn btn-primary">';
                $cowork_panel .= '</div>';
                $cowork_panel .= '</div>';
                $cowork_panel .= '</form>';

                $cowork_panel .= '</div>';
            }
        }


        if ($enable_curator_mode && 1 === intval($this->getConfigValue('curator_mode_fullaccess'))) {
            if ($cowork_mode === '0') {
                $params['grid_conditions']['user_id'] = $this->getSessionUserId();
                $default_params['pager_params']['cowork_mode'] = 0;
            } elseif ($cowork_mode !== '') {
                $default_params['pager_params']['cowork_mode'] = $cowork_mode;
                $params['grid_conditions']['user_id'] = $cowork_mode;
            } else {
                $users = array($this->getSessionUserId());
                $users = array_merge($users, array_keys($coworked_users));
                $params['grid_conditions']['user_id'] = $users;
                //@todo: эта опция вызывает полное зависание при пейджинге
                // /admin?action=data&_sortby=id&_sortdir=DESC&0[]=Array&page=2
            }
        } elseif ($enable_curator_mode && !empty($coworked)) {

            if ($cowork_mode === '0') {

                $params['grid_conditions']['user_id'] = $this->getSessionUserId();
                $default_params['pager_params']['cowork_mode'] = 0;
            } elseif ($cowork_mode !== '') {
                $cleared_coworked = array();
                $query = 'SELECT id FROM ' . DB_PREFIX . '_data WHERE user_id = ? AND id IN (' . implode(',', array_values($coworked)) . ')';
                $stmt = $DBC->query($query, array($cowork_mode));
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $cleared_coworked[] = $ar['id'];
                    }
                }
                $default_params['pager_params']['cowork_mode'] = $cowork_mode;

                if (!empty($cleared_coworked)) {
                    $params['grid_conditions']['id'] = $cleared_coworked;
                } else {
                    //@todo: эта опция вызывает полное зависание при пейджинге
                    // /admin?action=data&_sortby=id&_sortdir=DESC&0[]=Array&page=2
                    $params['grid_conditions']['id'] = array('-1');
                }
            } else {
                //@todo: эта опция вызывает полное зависание при пейджинге
                // /admin?action=data&_sortby=id&_sortdir=DESC&0[]=Array&page=2
                $params['grid_conditions']['id'] = $coworked;
                $params['grid_conditions']['user_id'] = $this->getSessionUserId();
            }
        }

        return [$cowork_panel, $params, $default_params];

    }

    function get_search_form () {
        $params = array();
        $this->enable_vue();
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/grid.php');
        $grid = new Common_Grid($this);
        $params['user_select_box'] = $grid->vue_tags_input('data', 'user_id');
        return $this->view('apps.data.resources.views.search_form', $params);
    }


    function grid($params = array(), $default_params = array())
    {
        if (isset($this->data_model[$this->table_name]['user_id'])) {
            $this->data_model[$this->table_name]['user_id']['type'] = 'select_by_query';
        }

        $REQUESTURIPATH = Sitebill::getClearRequestURI();
        $this->set_tags_from_request();
        $this->set_toolbar_buttons();
        $this->set_session_politics();


        $default_params['grid_item'] = $this->get_grid_items();

        $default_params['render_user_id'] = $this->getSessionUserId();

        $default_params['pager_params']['per_page'] = $this->getConfigValue('per_page_account');
        $default_params['pager_params']['page_url'] = $this->get_app_root();

        list($cowork_panel, $params, $default_params) = $this->get_cowork_panel($params, $default_params);


        if (intval($this->getConfigValue('enable_curator_mode')) == 0) {

            if (!preg_match('/all[\/]?$/', $REQUESTURIPATH)) {
                $params['grid_conditions']['user_id'] = $this->getSessionUserId();
            }
            if ( $this->request()->get('state') != 'all' && $this->get_app_root() != 'account/data' ) {
                $params['grid_conditions']['user_id'] = $this->getSessionUserId();
            } elseif ( $this->get_app_root() == 'account/data' and !preg_match('/all[\/]?$/', $REQUESTURIPATH)) {
                $params['grid_conditions']['user_id'] = $this->getSessionUserId();
            } else {
                if ( $this->getConfigValue('apps.data.disable_all_button') != 1 ) {
                    unset($params['grid_conditions']['user_id']);
                    $default_params['pager_params']['state'] = 'all';
                }
            }
        }
        if (preg_match('/\/all$/', $REQUESTURIPATH)) {
            $default_params['pager_params']['page_url'] = 'account/data/all';
            $default_params['pager_params']['state'] = 'all';
        }

        if (1 == (int)$this->getConfigValue('apps.realty.use_predeleting')) {
            $params['grid_conditions']['archived'] = 0;
        }
        if ($this->getRequestValue('active') != null) {
            $params['grid_conditions']['active'] = $this->getRequestValue('active');
        }
        if ($this->getRequestValue('adsapi_loaded') == 1) {
            $params['grid_conditions']['adsapi_loaded'] = 1;
        }



        $this->set_sort_by_request();
        $params = $this->onGridConditionsPrepare($this, $params);
        $params = $this->add_grid_controls_params($params);

        $params['url'] = '/' . $REQUESTURIPATH;

        $params = $this->addCategoryTree($params);
        $rs = $this->bootstrap_and_css_header();
        $params = $this->add_grid_item_params($params);
        $default_params = $this->batch_and_mass_default_params($default_params);

        if ($cowork_panel != '') {
            $rs .= '<div>' . $cowork_panel . '</div>';
        }
        if ( $this->is_all_state() ) {
            $params = $this->get_agency_params($params, $this->getSessionUserId());
        }

        $params = $this->get_postponded_params($params);

        if ( $this->get_full_access_mode() ) {
            unset($params['grid_conditions']['user_id']);
            unset($default_params['render_user_id']);
            $_SESSION['politics']['data']['check_access'] = false;
            unset($_SESSION['politics']['data']['user_id']);
        }

        if ( $this->getConfigValue('apps.data.disable_all_button') == 1 ) {
            $params['grid_conditions']['user_id'] = $this->getSessionUserId();
        }


        $rs .= Object_Manager::grid($params, $default_params);
        if ($this->getConfigValue('apps.billing.enable')) {
            $rs .= $this->billing_plugin();
        }

        return $rs;
    }

    function get_postponded_params ($params) {
        if ($this->getConfigValue('apps.data.allow_postponded') ) {
            if ( $this->request()->get('postponded') ) {
                $params['grid_conditions_sql']['postponded'] = '`'.DB_PREFIX.'_'.$this->table_name.'`.`postponded_to` > ' . '\''.date('Y-m-d H:i:s').'\'';
            }
        }

        return $params;
    }

    function is_all_state () {
        if ( $this->request()->get('state') == 'all' ) {
            return true;
        }
        if (preg_match('/all[\/]?$/', Sitebill::getClearRequestURI())) {
            return true;
        }
        return false;
    }

    function get_agency_params ( $params, $user_id ) {
        if ( $this->agency_admin ) {
            $params['grid_conditions']['user_id'] = $this->agency_admin->get_agency_user_id_array($user_id);
        }
        return $params;
    }

    private function set_sort_by_request () {
        if (null === $this->getRequestValue('_sortby')) {
            $default_sort = trim($this->getConfigValue('apps.data.default_sort'));
            if ($default_sort != '') {
                list($_sortby, $_sortdir) = explode('|', $default_sort);
                if (trim($_sortby) != '') {
                    $this->setRequestValue('_sortby', $_sortby);
                    if (trim($_sortdir) != '') {
                        $this->setRequestValue('_sortdir', $_sortdir);
                    }
                }
            }

        }
    }

    /**
     * Добавление кастомных контролов
     * Тип func используется для функциональніх контролов, доступность которых основывается на текущих данных объекта
     * параметр func определяет имя функцииЮ возвращающей
     * array_push($params['grid_controls'], array(
            'name' => 'actionname (имя экшена)',
            'btnclass' => 'btnclass (класс кнопки)',
            'btnicon' => 'btnclass (иконка кнопки)',
            'btntext' => 'btnclass (текст кнопки)',
            'type' => 'func',
            'func' => 'checkBtnEnabled (функция возвоащающая true|false как признак доступности кнопки. аргумент - данные объекта в рамках прав пользователя)',
            'object' => $this (указатель на объект)
        ));
     * @param array $params
     * @return array
     */
    protected function add_custom_grid_controls_params ($params){
        return $params;
    }

    private function add_grid_controls_params ($params) {
        $params['grid_controls'] = array('fast_preview');
        if (!$this->getConfigValue('apps.data.disable_delete_button')) {
            array_push($params['grid_controls'], 'delete');
        }
        if (!$this->getConfigValue('apps.data.disable_edit_button')) {
            array_push($params['grid_controls'], 'edit');
        }
        if ($this->getConfigValue('apps.reservation.enable')) {
            array_push($params['grid_controls'], 'reservation');
        }
        if (!$this->getConfigValue('apps.data.disable_memory_button')) {
            array_push($params['grid_controls'], 'memorylist');
        }
        return $this->add_custom_grid_controls_params($params);
    }

    private function batch_and_mass_default_params ( $default_params ) {
        $default_params['batch_update'] = true;
        $default_params['batch_update_url'] = SITEBILL_MAIN_URL .'/'.$this->get_app_root() . self::$_trslashes;

        $default_params['mass_delete'] = true;
        $default_params['mass_delete_url'] = SITEBILL_MAIN_URL . '/'.$this->get_app_root() . self::$_trslashes;

        if (isset($this->data_model[$this->table_name]['active'])) {
            $default_params['batch_activate'] = true;
        }
        return $default_params;
    }

    private function add_grid_item_params ($params) {
        $DBC = DBC::getInstance();
        $used_fields = array();
        $query = 'SELECT `grid_fields` FROM ' . DB_PREFIX . '_table_grids WHERE `action_code`=?';
        $stmt = $DBC->query($query, array('data_user_' . $this->getSessionUserId()));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $used_fields = json_decode($ar['grid_fields']);
            $params['grid_item'] = $used_fields;
        }
        return $params;
    }

    private function addCategoryTree ($params) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $Structure_Manager->set_context($this);
        if ( !is_array($params['grid_conditions']['user_id']) ) {
            $category_tree = $Structure_Manager->get_category_tree_control($this->getConfigValue('topic_id'), $params['grid_conditions']['user_id']);
            $this->template->assert('category_tree_account', $category_tree);
        }

        if ($this->getRequestValue('topic_id') != '') {
            $all_cats = $Structure_Manager->get_all_childs($this->getRequestValue('topic_id'), $Structure_Manager->loadCategoryStructure());
            //$all_cats = array_push($all_cats, $this->getRequestValue('topic_id'));
            array_push($all_cats, $this->getRequestValue('topic_id'));
            //print_r($all_cats);
            $params['grid_conditions']['topic_id'] = $all_cats;
        }
        return $params;
    }

    public function billing_plugin()
    {
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/billing/admin/admin.php';
        $billing = new billing_admin();
        return $billing->billing_plugin();
    }

    public function createPDF($data, $grid_array_transformed)
    {
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

    /**
     * Check data
     * @param array $form_data
     * @return boolean
     */
    function check_data($form_data/* , &$error_fields=array() */)
    {
        $check_status = parent::check_data($form_data);
        if (!$check_status) {
            return $check_status;
        }
        if ($this->getConfigValue('apps.akismet.enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/akismet/admin/admin.php');
            $akismet_admin = new akismet_admin();

            if ($akismet_admin->akismet_check($form_data['text']['value'] . ' ' . $form_data['fio']['value'] . ' ' . $form_data['email']['value'] . ' ' . $form_data['phone']['value'])) {
                $this->riseError($akismet_admin->GetErrorMessage());
                return false;
            }
        }
        if ($this->getConfigValue('apps.data.check_unique_enable')) {
            $unique_percent = $this->check_unique_text('text', $form_data['text']['value'], $form_data['id']['value']);
            if ($unique_percent) {
                $this->riseError($this->getConfigValue('apps.data.unique_text_required') . ', сейчас совпадение ' . $unique_percent . '%');
                return false;
            }
        }

        return true;
    }

    function check_unique_text($column_name, $search_text, $id)
    {
        set_include_path(SITEBILL_DOCUMENT_ROOT . '/apps/data/lib/');
        require_once 'SearchEngine.php';
        require_once 'ApiDataSource.php';
        // подключение списка стоп-слов
        $stop_words = require_once 'stop_words.php';
        // список стоп-символов. В случае, если не указан система удалить, все кроме букв/цифр/пробела
        $stop_symbols = '';

        if (empty($search_text)) {
            return false;
        }
        $limit = 100;
        // защита от "дурака"
        if ($limit > 100000) {
            $limit = 100000;
        }
        $dataSource = new ApiDataSource();
        $dataSource->setCount($limit);
        //$compare_text = 'Что-то свое светлую и уютную 3х комнатную квартиру 90 серии в хорошем состоянии с ремонтом в пос. общественного транспорта. Развитая инфраструктура с ';

        $dataSource->setText($search_text);
        $options = array(
            'stop_words' => $stop_words,
            'stop_symbols' => $stop_symbols,
            'shingle_length' => 10
        );
        $engine = new SearchEngine($dataSource, $options);

        $DBC = DBC::getInstance();
        $query = 'SELECT id, text FROM ' . DB_PREFIX . '_data WHERE id <> ?';
        $stmt = $DBC->query($query, array($id), $success);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                if ($ar['text'] != '') {
                    $ar['text'] = str_replace("\n", " ", $ar['text']);
                    //echo $ar['text'].'<br>'."\n\n";

                    $engine->setSearchText($ar['text']);
                    //$engine->setSearchText($compare_text);
                    $result = $engine->run();

                    if ($result['percent'] > $this->getConfigValue('apps.data.check_unique_percent')) {
                        //echo '<h1>duplicate! '.$ar['id'].' '.$result['percent'].'</h1>';
                        return $result['percent'];
                        echo $ar['text'] . '<br>' . "\n\n";
                        echo 'Количество дублей (совпадение 100%): ' . $result['duplicates'] . '<br/>';
                        echo 'Процент схожести: ' . $result['percent'] . '<br/>';
                        echo 'Затраченное время: ' . $result['time'] . '<br/>';
                    }
                }
            }
        }
    }

}
