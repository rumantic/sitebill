<?php
/**
 * ObjectFormTrait — Form generation and template resolution methods extracted from Object_Manager.
 *
 * Methods: get_form, get_batch_update_form, getSteps, set_apps_template, get_apps_template,
 *          bootstrap_and_css_header
 *
 * @see Object_Manager
 */

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

trait ObjectFormTrait
{
    private $default_form_action = false;

    function set_default_form_action($action)
    {
        $this->default_form_action = $action;
    }

    function get_default_form_action()
    {
        return $this->default_form_action;
    }

    function get_form($form_data = array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php')
    {
        if (defined('IFRAME_MODE')) {
            $action = '?';
        }

        $_SESSION['allow_disable_root_structure_select'] = true;

        if ($button_title == '') {
            $button_title = Multilanguage::_('L_TEXT_SAVE');
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();
        $form_generator->set_context($this);

        $rs = $this->get_ajax_functions();
        if (1 == $this->getConfigValue('apps.geodata.enable')) {
            $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/geodata/js/geodata.js"></script>';
        }
        $rs .= '<form method="post" class="form-horizontal" action="' . ($this->get_default_form_action() ? $this->get_default_form_action() : $action) . '" enctype="multipart/form-data">';

        if ($this->getError()) {
            $this->template->assign('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
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

        $el['controls']['submit'] = array('html' => '<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">' . $button_title . '</button>');

        $admin_mode = false;
        if (defined('ADMIN_MODE') and ADMIN_MODE == 1) {
            $admin_mode = true;
        }

        if ($this->getConfigValue('post_form_agreement_enable') == 1 && !$admin_mode) {
            $el['agreement_block'] = $form_generator->getAgreementFormBlock();
        }

        $this->template->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl';
        } else {
            $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
        }
        return $this->template->fetch($tpl_name);
    }

    function get_batch_update_form($form_data = array(), $ids = array(), $selected_fields = array(), $action = 'index.php')
    {
        $_SESSION['allow_disable_root_structure_select'] = true;
        $button_title = Multilanguage::_('L_TEXT_SAVE');
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

    function getSteps($form_data, $step)
    {
        $default_tab_name = $this->getConfigValue('default_tab_name');
        $tabs = array($default_tab_name);

        foreach ($form_data as $item_array) {
            if (isset($item_array['tab']) && $item_array['tab'] != '') {
                $tabs[$item_array['tab']] = $item_array['tab'];
            }
        }
        $tabs_array = array();
        $i = 1;
        foreach ($tabs as $t) {
            if ($i < $step) {
                $tabs_array[$i] = array('name' => $t, 'step' => $i, 'status' => 'done');
            } elseif ($i == $step) {
                $tabs_array[$i] = array('name' => $t, 'step' => $i, 'status' => 'current');
            } else {
                $tabs_array[$i] = array('name' => $t, 'step' => $i, 'status' => 'further');
            }
            $i++;
        }
        return $tabs_array;
    }

    function set_apps_template($apps_name, $theme, $template_key, $template_value)
    {
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/apps/' . $apps_name . '/site/template/' . $template_value)) {
            $this->template->assert($template_key, SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/apps/' . $apps_name . '/site/template/' . $template_value);
        } elseif (!file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/' . $apps_name . '/' . $template_value)) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/' . $apps_name . '/site/template/' . $template_value)) {
                $this->template->assert($template_key, SITEBILL_DOCUMENT_ROOT . '/apps/' . $apps_name . '/site/template/' . $template_value);
            } else {
                echo sprintf(Multilanguage::_('L_FILE_NOT_FOUND'), SITEBILL_DOCUMENT_ROOT . '/apps/' . $apps_name . '/site/template/' . $template_value);
                exit;
            }
        } else {
            $this->template->assert($template_key, SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/' . $apps_name . '/' . $template_value);
        }
    }

    function get_apps_template($apps_name, $theme, $template_key, $template_value)
    {
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/apps/' . $apps_name . '/site/template/' . $template_value)) {
            return SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/apps/' . $apps_name . '/site/template/' . $template_value;
        } elseif (!file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/' . $apps_name . '/' . $template_value)) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/' . $apps_name . '/site/template/' . $template_value)) {
                return SITEBILL_DOCUMENT_ROOT . '/apps/' . $apps_name . '/site/template/' . $template_value;
            } else {
                return '';
            }
        } else {
            return SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/' . $apps_name . '/' . $template_value;
        }
    }

    protected function bootstrap_and_css_header()
    {
        $rs = '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template1/assets/css/font-awesome.min.css" />';
        $rs .= '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/data/css/style.css" />';
        $bootstrap_version = trim($this->getConfigValue('bootstrap_version'));
        if ($bootstrap_version == '3') {
            $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/system/js/bootstrap3-typeahead.min.js"></script>';
        }
        $rs .= '<script src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template1/assets/js/bootstrap-tag.min.js"></script>';

        return $rs;
    }
}
