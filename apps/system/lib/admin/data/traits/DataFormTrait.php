<?php
/**
 * DataFormTrait — Form generation methods for Data_Manager.
 *
 * Extracted from data_manager.php during refactoring.
 * Methods: get_form(), get_batch_update_form(), get_element(), getNonUniqIds(), checkUniquety()
 */
trait DataFormTrait
{
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
        $rs .= '<form method="post" class="form-horizontal" action="' . ($this->get_default_form_action()?$this->get_default_form_action():$action) . '" enctype="multipart/form-data">';
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

    function get_batch_update_form($form_data = array(), $ids = array(), $selected_fields = array(), $action = 'index.php') {
        $_SESSION['allow_disable_root_structure_select'] = true;
        if ($button_title == '') {
            $button_title = Multilanguage::_('L_TEXT_SAVE');
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();
        $rs = $this->get_ajax_functions();
        if (1 == $this->getConfigValue('apps.geodata.enable')) {
            $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/geodata/js/geodata.js"></script>';
        }
        $rs .= '<form method="post" class="form-horizontal" action="' . $action . '" enctype="multipart/form-data">';
        if ($this->getError()) {
            $this->template->assign('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
        }
        $el = $form_generator->compile_form_elements($form_data);
        $el['private'][] = array('html' => '<input type="hidden" name="do" value="batch_update" />');
        $el['private'][] = array('html' => '<input type="hidden" name="action" value="' . $this->action . '">');
        $el['private'][] = array('html' => '<input type="hidden" name="language_id" value="">');

        foreach ($ids as $id) {
            $el['private'][] = array('html' => '<input type="hidden" name="batch_ids[]" value="' . $id . '">');
        }
        $el['form_header'] = $rs;
        $el['form_footer'] = '</form>';
        $el['controls']['submit'] = array('html' => '<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">' . $button_title . '</button>');

        $this->template->assign('selected_fields', $selected_fields);
        $this->template->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data_batch_update.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data_batch_update.tpl';
        } else {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/apps/admin/admin/template/data_form_batch_update.tpl';
        }
        return $this->template->fetch($tpl_name);
    }
}
