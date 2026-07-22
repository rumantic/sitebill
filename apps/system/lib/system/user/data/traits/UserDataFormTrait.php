<?php
/**
 * UserDataFormTrait — form rendering methods extracted from User_Data_Manager.
 *
 * Methods: get_form, getSteps, _get_form_step_divided, _get_form_standart
 */
trait UserDataFormTrait
{
    function get_form($form_data = array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php')
    {
        $_SESSION['allow_disable_root_structure_select'] = true;
        if (1 == $this->getConfigValue('divide_step_form')) {
            return $this->_get_form_step_divided($form_data, $do);
        } else {
            return $this->_get_form_standart($form_data, $do);
        }
    }

    function getSteps($form_data, $step)
    {

        $default_tab_name = $this->getConfigValue('default_tab_name');
        $tabs = array($default_tab_name);

        foreach ($form_data as $item_id => $item_array) {
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

    function _get_form_step_divided($form_data = array(), $do = 'new', $language_id = 0, $button_title = '')
    {


        //$step=(int)$this->getRequestValue('step')
        $requesturi = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        if (SITEBILL_MAIN_URL != '') {
            preg_replace('/^' . trim(SITEBILL_MAIN_URL, '/') . '/', '', $requesturi);
        }
        if (preg_match('/step(\d+)$/', $requesturi, $matches)) {
            $step = (int)$matches[1];
        } else {
            $step = 1;
        }
        //echo $step;

        $steps_names = $this->getSteps($form_data, $step);
        $last_step = $steps_names[count($steps_names)]['name'];

        if (isset($form_data['captcha'])) {
            $form_data['captcha']['tab'] = $last_step;
        }

        foreach ($form_data as $k => $v) {
            if ($v[type] === 'uploadify_image') {
                $form_data[$k]['tab'] = $last_step;
            }
        }
        $steps_names = $this->getSteps($form_data, $step);

        $steps_total = count($steps_names);


        $Sitebill_Registry = Sitebill_Registry::getInstance();
        $Sitebill_Registry->addFeedback('divide_step_form', true);
        $Sitebill_Registry->addFeedback('step', $step);

        $user_id = (int)$_SESSION['user_id'];


        global $smarty;
        $el = array();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
        $account = new Account();
        $account_value = $account->getAccountValue($user_id);
        $rs = '<div class="clear"></div>';
        $rs .= $this->get_ajax_functions();
        $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/geodata/js/geodata.js"></script>';
        //$rs .= '<form method="post" action="'.SITEBILL_MAIN_URL.'/account/data/">';
        if (1 == $this->getConfigValue('use_combobox')) {
            $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/mycombobox.js"></script>';
            $rs .= '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/system/css/mycombobox.css" />';
        }

        $el = $form_generator->compile_form_elements($form_data);

        $topic_id = (int)$form_data['topic_id']['value'];
        $current_id = (int)$form_data['id']['value'];

        if ($topic_id != 0 && $current_id != 0) {

            $href = $this->getRealtyHREF($current_id, true, array('topic_id' => $topic_id, 'alias' => $form_data['translit_alias']['value']));
            $rs .= '<a class="btn btn-success pull-right" href="' . $href . '" target="_blank">' . Multilanguage::_('L_SEE_AT_SITE') . '</a>';
        }

        if ($step < $steps_total) {
            $rs .= '<form id="step_form" method="post" action="' . SITEBILL_MAIN_URL . '/account/data/step' . (1 + $step) . '" enctype="multipart/form-data" class="user_add_form">';
        } else {
            $rs .= '<form id="step_form" method="post" action="' . SITEBILL_MAIN_URL . '/account/data/step' . $steps_total . '" enctype="multipart/form-data" class="user_add_form">';
        }

        if ($this->getConfigValue('advert_cost') > 0 and ($do == 'new' or $do == 'new_done')) {

            $rs .= '<p><b>Стоимость размещения одного объявления ' . $this->getConfigValue('advert_cost') . ' ' . $this->getConfigValue('ue_name') . '</b></p>';

            if ($account_value < $this->getConfigValue('advert_cost')) {
                $rs .= '<p>Ваш баланс ' . $account_value . ' ' . $this->getConfigValue('ue_name') . '</p>';
                $rs .= '<b>На вашем счету не хватает средств для размещения объявления, <a href="' . SITEBILL_MAIN_URL . '/account/balance/?do=add_bill">пополнить</a></b></td>';
                return $rs;
            }
        }


        if ($this->getError()) {
            $smarty->assign('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
        }


        $el['form_header'] = $rs;
        $el['form_footer'] = '</form>';


        if ($step < $steps_total) {
            if ($do === 'new') {
                $el['private'][] = array('html' => '<input type="hidden" name="do" value="new" />');
            } else {
                $el['private'][] = array('html' => '<input type="hidden" name="do" value="edit" />');
            }
        } else {
            if ($do === 'new') {
                $el['private'][] = array('html' => '<input type="hidden" name="do" value="new_done" />');
            } else {
                $el['private'][] = array('html' => '<input type="hidden" name="do" value="edit_done" />');
            }
        }


        if ($step > 1) {
            $el['controls']['back'] = array('html' => '<input type="submit" name="submit" alt="' . ($step - 1) . '" id="formsubmit_back" value="Назад" />');
        }

        if ($step < $steps_total) {
            $button_title = 'Следующий шаг';
        } else {
            $button_title = 'Сохранить';
        }

        $el['controls']['submit'] = array('html' => '<input type="submit" name="submit" id="formsubmit" onClick="return SitebillCore.formsubmit(this);" value="' . $button_title . '" />');


        $smarty->assign('current_step', $step);
        $smarty->assign('divide_by_step', 1);
        $smarty->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl';
        } else {
            $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
        }

        return $smarty->fetch($tpl_name);
    }

    function _get_form_standart($form_data = array(), $do = 'new', $language_id = 0, $button_title = '')
    {
        if ($button_title == '') {
            $button_title = Multilanguage::_('L_TEXT_SAVE');
        }
        $user_id = (int)$_SESSION['user_id'];
        $el = array();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        $form_generator = new Form_Generator();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
        $account = new Account();
        $account_value = $account->getAccountValue($user_id);
        $rs = '';
        $rs .= '<div class="clear"></div>';
        $rs .= $this->get_ajax_functions();
        $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/geodata/js/geodata.js"></script>';
        if (1 == $this->getConfigValue('use_combobox')) {
            $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/mycombobox.js"></script>';
            $rs .= '<link rel="stylesheet" href="' . SITEBILL_MAIN_URL . '/apps/system/css/mycombobox.css" />';
        }

        $topic_id = (int)$form_data['topic_id']['value'];
        $current_id = (int)$form_data['id']['value'];

        if ($topic_id != 0 && $current_id != 0) {
            $href = $this->getRealtyHREF($current_id, true, array('topic_id' => $topic_id, 'alias' => $form_data['translit_alias']['value']));
            $rs .= '<a class="btn btn-success form-cntrl form-cntrl-siteview" href="' . $href . '" target="_blank">' . Multilanguage::_('L_SEE_AT_SITE') . '</a>';
        }

        $rs .= '<form method="post" class="form-horizontal" action="' . SITEBILL_MAIN_URL . '/account/data' . SiteBill::$_trslashes . '" enctype="multipart/form-data">';

        if ($this->getConfigValue('advert_cost') > 0 and ($do == 'new' or $do == 'new_done')) {

            $rs .= '<p><b>Стоимость размещения одного объявления ' . $this->getConfigValue('advert_cost') . ' ' . $this->getConfigValue('ue_name') . '</b></p>';

            if ($account_value < $this->getConfigValue('advert_cost')) {
                $rs .= '<p>Ваш баланс ' . $account_value . ' ' . $this->getConfigValue('ue_name') . '</p>';
                $rs .= '<b>На вашем счету не хватает средств для размещения объявления, <a href="' . SITEBILL_MAIN_URL . '/account/balance/?do=add_bill">пополнить</a></b></td>';
                return $rs;
            }
        }

        if ($this->getError()) {
            $this->template->assign('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
        }

        $el = $form_generator->compile_form_elements($form_data);
        $el['form_header'] = $rs;
        $el['form_header_action'] = SITEBILL_MAIN_URL . '/account/data/';
        $el['form_header_class'] = 'form-horizontal';
        $el['form_header_enctype'] = 'multipart/form-data';
        $el['form_footer'] = '</form>';
        if ($do == 'new') {
            $el['private'][] = array('html' => '<input type="hidden" name="do" value="new_done" />');
        } else {
            $el['private'][] = array('html' => '<input type="hidden" name="do" value="edit_done" />');
        }

        /* $token = md5(uniqid(mt_rand() . microtime()));
          $hash = md5($token.$_SESSION['csrfsecret']);
          $el['private'][] = array('html' => '<input type="hidden" name="csrftoken" value="'.$token.'" />');
          $el['private'][] = array('html' => '<input type="hidden" name="csrfhash" value="'.$hash.'" />');
         */
        $el['controls']['submit'] = array('html' => '<input class="btn btn-primary" type="submit" name="submit" id="formsubmit" onClick="return SitebillCore.formsubmit(this);" value="' . $button_title . '" />');

        $this->template->assign('do', $do);
        $this->template->assign('id', $form_data['id']['value']);
        $this->template->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data_front.tpl')) {

            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data_front.tpl';
        } else {
            /* if (defined('RUN_WITH3BOOTSTRAP') && RUN_WITH3BOOTSTRAP == 1) {
              $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
              } else {
              $tpl_name = $this->getAdminTplFolder() . '/data_form_front.tpl';
              } */

            $tpl_name = $this->getAdminTplFolder() . '/data_form_front.tpl';
        }

        return $this->template->fetch($tpl_name);
    }
}
