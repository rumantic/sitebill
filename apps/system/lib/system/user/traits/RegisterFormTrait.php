<?php
/**
 * RegisterFormTrait — form rendering and preparation for Register_Using_Model
 *
 * Extracted methods:
 *   - addAgreementElement(&$form_data)
 *   - get_form($form_data, $do, $language_id, $button_title, $action)
 *   - getRequiredRegisterFormElements()
 *   - getRegisterFormElements($params)
 */
trait RegisterFormTrait
{
    protected function addAgreementElement(&$form_data)
    {
        if ($this->getConfigValue('register_form_agreement_enable') == 1) {
            $form_data['_post_agreement_check']['name'] = '_post_agreement_check';
            $form_data['_post_agreement_check']['title'] = Multilanguage::_('REGISTER_AGREEMENT_TEXT', 'system');
            $form_data['_post_agreement_check']['value'] = '';
            if ($this->getConfigValue('register_form_agreement_enable_ch') == 1) {
                $form_data['_post_agreement_check']['value'] = '1';
            }
            $form_data['_post_agreement_check']['length'] = 40;
            $form_data['_post_agreement_check']['dbtype'] = 0;
            $form_data['_post_agreement_check']['type'] = 'checkbox';
            $form_data['_post_agreement_check']['required'] = 'on';
            $form_data['_post_agreement_check']['unique'] = 'off';
        }
    }

    function get_form($form_data = array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php')
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


        //$el['private'][] = array('html' => '<input type="hidden" name="_csrf_token" value="' . self::$_csrf_token . '">');

        $el['form_header'] = $rs;
        $el['form_header_action'] = $action;
        $el['form_header_class'] = 'form-horizontal';
        $el['form_header_enctype'] = 'multipart/form-data';
        $el['form_footer'] = '</form>';

        $el['controls']['submit'] = array('html' => '<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">' . $button_title . '</button>');

        $smarty->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl';
        } else {
            $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
        }
        return $smarty->fetch($tpl_name);
    }

    public function getRequiredRegisterFormElements()
    {
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model('user_register', false);
        }

        if (!$form_data || empty($form_data['user_register'])) {
            $form_data = $this->data_model;
        } else {
            $form_data[$this->table_name] = $form_data['user_register'];
        }


        $form_data[$this->table_name]['newpass']['required'] = 'on';
        $form_data[$this->table_name]['newpass_retype']['required'] = 'on';
        unset($form_data[$this->table_name]['active']);

        $this->addAgreementElement($form_data[$this->table_name]);


        $reg_form_elements = array();
        foreach ($form_data[$this->table_name] as $fden => $fdev) {
            if ($fdev['required'] == 'on') {
                $reg_form_elements[$fden] = $fden;
            }
        }

        return $reg_form_elements;
    }

    public function getRegisterFormElements($params = array())
    {

        $dynamictab = 'regels';

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data = $ATH->load_model('user_register', false);
        }
        //var_dump($form_data);
        if (!$form_data || empty($form_data['user_register'])) {
            $form_data = $this->data_model;
        } else {
            $form_data[$this->table_name] = $form_data['user_register'];
        }


        $form_data[$this->table_name]['newpass']['required'] = 'on';
        $form_data[$this->table_name]['newpass_retype']['required'] = 'on';
        unset($form_data[$this->table_name]['active']);

        $this->addAgreementElement($form_data[$this->table_name]);


        $reg_form_elements = array();
        foreach ($form_data[$this->table_name] as $fden => $fdev) {
            if ($fdev['required'] == 'on' || in_array($fden, $params)) {
                $reg_form_elements[$fden] = $fdev;
            }
        }
        if (isset($reg_form_elements['group_id'])) {
            if ($this->getConfigValue('newuser_registration_shared_groupid') != "") {
                $shared_groups = $this->getConfigValue('newuser_registration_shared_groupid');
                $shared_groups = preg_replace('/[^\d,]/', '', $shared_groups);
                //var_dump($shared_groups);
                if ($shared_groups != '') {
                    $reg_form_elements['group_id']['query'] = 'SELECT * FROM ' . DB_PREFIX . '_group WHERE group_id IN (' . $shared_groups . ')';
                } else {
                    $reg_form_elements['group_id']['query'] = 'SELECT * FROM ' . DB_PREFIX . '_group WHERE group_id=0';
                }
            } else {
                unset($reg_form_elements['group_id']);
            }
        }

        //$el['private'][] = array('html' => '<input type="hidden" name="_csrf_token" value="' . $this->_csrf_token . '">');

        foreach ($reg_form_elements as $k => $v) {
            $reg_form_elements[$k]['tab'] = $dynamictab;
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();
        $el = $form_generator->compile_form_elements($reg_form_elements, false);

        //$el['public'][$this->getConfigValue('default_tab_name')]['_csrf_token']['type'] = 'hidden';
        //$el['public'][$this->getConfigValue('default_tab_name')]['_csrf_token']['html'] = '<input type="hidden" value="'.self::$_csrf_token.'" name="_csrf_token">';

        return $el['public'][$dynamictab];
    }
}
