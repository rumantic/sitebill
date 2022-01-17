<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Profile editor using model
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class User_Profile_Model extends User_Profile {

    function main() {
        $user_id = $this->getSessionUserId();
        if ($user_id != 0) {
            $this->setRequestValue($this->primary_key, $user_id);
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            $data_model = new Data_Model();
            $form_data = $this->data_model;
            if ($this->getConfigValue('email_as_login')) {
                unset($form_data[$this->table_name]['email']);
            }
            $rs .= '<h1>' . Multilanguage::_('PROFILE', 'system') . '</h1>';
            $rs .= $this->getTopMenu();

            $rs .= '<div class="profile_page">';

            switch ($this->getRequestValue('do')) {

                case 'email_edit' : {
                        if (1 != intval($this->getConfigValue('allow_user_email_change'))) {
                            break;
                        }
                        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/register_using_model.php');
                        $RUM = new Register_Using_Model();

                        $ret = '';
                        $error = '';
                        $success = '';
                        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
                            $email = trim($this->getRequestValue('email'));
                            $pass_old = trim($this->getRequestValue('pass_old'));
                            if ($email == '' || $pass_old == '') {
                                $error = 'Необходимо заполнить все поля';
                            } elseif (strlen($email) < 5 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $error = 'Указан неправильный email';
                            } elseif (!$RUM->checkEmail($email)) {
                                $error = Multilanguage::_('REG_EMAIL_YET_REG', 'system');
                            } else {

                                $DBC = DBC::getInstance();
                                $query = 'UPDATE ' . DB_PREFIX . '_user SET `email`=? WHERE `password`=? AND `user_id`=?';
                                $stmt = $DBC->query($query, array($email, md5($pass_old), $user_id));
                                if (!$stmt) {
                                    $error = 'Вы указали неправильный старый пароль';
                                } else {
                                    $success = 'E-mail изменен';

                                    $message = 'Вы успешно заменили email-адрес на сайте ' . $_SERVER['HTTP_HOST'];
                                    if (1 == intval($this->getConfigValue('email_as_login'))) {
                                        $message .= '<br />С этого момента для авторизации на сайте используйте свой новый email ' . $email . '.  Авторизация по прежнему email-адресу теперь невозможна.';
                                    }
                                    $from = $this->getConfigValue('system_email');
                                    $subject = 'Смена email';
                                    $this->sendFirmMail($email, $from, $subject, $message);
                                }
                            }
                        }

                        if ($error != '') {
                            $ret .= '<div class="alert alert-error">' . $error . '</div>';
                        }
                        if ($success != '') {
                            $ret .= '<div class="alert alert-success">' . $success . '</div>';
                        }

                        $ret .= '<div class="profile_change_password">';
                        $ret .= '<div class="tabbed_form_block"><form class="form-horizontal" action="' . SITEBILL_MAIN_URL . '/account/profile/" method="post">
					  <fieldset>
							<div class="form-group"><label>' . Multilanguage::_('L_PASSWORD') . '</label>
						<input type="password" name="pass_old"></div>
						<div class="form-group"><label>' . Multilanguage::_('L_NEW_EMAIL') . '</label>
						<input type="text" name="email"></div>
						
						
						<input type="hidden" name="do" value="email_edit">
						<div class="form_element_control"><input type="submit" class="btn btn-primary" value="' . Multilanguage::_('L_CHANGE') . '"></div>
						
					  </fieldset>
					</form></div>';
                        $ret .= '</div>';
                        $rs .= $ret;
                        break;
                    }

                case 'pass_edit' : {
                    $ret = '';
                    $error = '';
                    $success = '';
                    if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
                        $pass = trim($this->getRequestValue('pass'));
                        $pass2 = trim($this->getRequestValue('pass2'));
                        $pass_old = trim($this->getRequestValue('pass_old'));
                        if ($pass == '' || $pass2 == '' || $pass_old == '') {
                            $error = 'Необходимо заполнить все поля';
                        } elseif ($pass != $pass2) {
                            $error = 'Пароли не совпадают';
                        } else {
                            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/register_using_model.php');
                            $RUM = new Register_Using_Model();
                            if (!$RUM->checkPasswordQuality($pass, $msg)) {
                                $error = $msg;
                            } else {
                                $DBC = DBC::getInstance();
                                $query = 'UPDATE ' . DB_PREFIX . '_user SET `password`=? WHERE `password`=? AND `user_id`=?';
                                $stmt = $DBC->query($query, array(md5($pass), md5($pass_old), $user_id));
                                if (!$stmt) {
                                    $error = 'Вы указали неправильный старый пароль';
                                } else {
                                    $success = 'Пароль изменен';
                                }
                            }
                        }
                    }

                    if ($error != '') {
                        $ret .= '<div class="alert alert-error">' . $error . '</div>';
                    }
                    if ($success != '') {
                        $ret .= '<div class="alert alert-success">' . $success . '</div>';
                    }

                    $ret .= '<div class="profile_change_password">';
                    $ret .= '<div class="tabbed_form_block"><form class="form-horizontal" action="' . SITEBILL_MAIN_URL . '/account/profile/" method="post">
                        <fieldset>
                        <div class="form-group"><label>' . Multilanguage::_('L_REGISTER_OLD_PASSWORD') . '</label>
                        <input type="password" name="pass_old"></div>
                        <div class="form-group"><label>' . Multilanguage::_('L_REGISTER_NEW_PASSWORD') . '</label>
                        <input type="password" name="pass"></div>
                        <div class="form-group"><label>' . Multilanguage::_('L_REGISTER_RETYPE_PASSWORD') . '</label>
                        <input type="password" name="pass2"></div>

                        <input type="hidden" name="do" value="pass_edit">
                        <div class="form_element_control"><input type="submit" class="btn btn-primary" value="' . Multilanguage::_('L_CHANGE') . '"></div>

                        </fieldset>
                        </form></div>';
                    $ret .= '</div>';
                    $rs .= $ret;
                    break;
                }

                case 'edit_done' : {

                        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
                        if (isset($_POST['delpic'])) {
                            $this->deleteUserpic($user_id);
                        }
                        unset($form_data[$this->table_name]['company_id']);
                        unset($form_data[$this->table_name]['group_id']);
                        unset($form_data[$this->table_name]['login']);
                        unset($form_data[$this->table_name]['publication_limit']);
                        unset($form_data[$this->table_name]['captcha']);
                        unset($form_data[$this->table_name]['active']);
                        unset($form_data[$this->table_name]['notify']);
                        unset($form_data[$this->table_name]['email']);

                        if (!$this->check_data($form_data[$this->table_name])) {

                            $rs .= $this->get_form($form_data[$this->table_name], 'edit', 0, '', SITEBILL_MAIN_URL . '/account/profile/');
                        } else {
                            $this->edit_data($form_data[$this->table_name]);
                            if ($this->getError()) {
                                $rs .= $this->get_form($form_data[$this->table_name], 'edit', 0, '', SITEBILL_MAIN_URL . '/account/profile/');
                            } else {
                                $this->updateUserPicture($user_id);
                                $rs .= $this->showProfile($user_id);
                            }
                        }
                        break;
                    }

                case 'edit' : {


                        if ($this->getRequestValue('subdo') == 'delete_image') {
                            $this->deleteImage($this->table_name, $this->getRequestValue('image_id'));
                        }

                        if ($this->getRequestValue('subdo') == 'up_image') {
                            $this->reorderImage($this->table_name, $this->getRequestValue('image_id'), $this->primary_key, $this->getRequestValue($this->primary_key), 'up');
                        }

                        if ($this->getRequestValue('subdo') == 'down_image') {
                            $this->reorderImage($this->table_name, $this->getRequestValue('image_id'), $this->primary_key, $this->getRequestValue($this->primary_key), 'down');
                        }

                        if ($this->getRequestValue('language_id') > 0 and ! $this->language->get_version($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $this->getRequestValue('language_id'))) {
                            $rs .= $this->get_form($form_data[$this->table_name], 'new', $this->getRequestValue('language_id'), '', SITEBILL_MAIN_URL . '/account/profile/');
                        } else {
                            if ($this->getRequestValue('language_id') > 0) {
                                $form_data[$this->table_name] = $data_model->init_model_data_from_db_language($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name], false, $this->getRequestValue('language_id'));
                            } else {
                                $form_data[$this->table_name] = $data_model->init_model_data_from_db($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name]);
                            }
                            unset($form_data[$this->table_name]['company_id']);
                            unset($form_data[$this->table_name]['group_id']);
                            unset($form_data[$this->table_name]['login']);
                            unset($form_data[$this->table_name]['publication_limit']);
                            unset($form_data[$this->table_name]['captcha']);
                            unset($form_data[$this->table_name]['active']);
                            unset($form_data[$this->table_name]['notify']);
                            unset($form_data[$this->table_name]['email']);

                            $rs .= $this->get_form($form_data[$this->table_name], 'edit', 0, '', SITEBILL_MAIN_URL . '/account/profile/');
                        }

                        break;
                    }

                default : {
                        $rs .= $this->showProfile($user_id);
                    }
            }
            $rs .= '</div>';
        } else {
            $rs = '';
        }

        return $rs;
    }

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
        $form_generator->set_context($this);


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

        $el['controls']['submit'] = array('html' => '<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">' . $button_title . '</button>');

        $el['settings']['show_agreement'] = 1;

        $smarty->assign('form_elements', $el);
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl')) {
            $tpl_name = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/admin/template/form_data.tpl';
        } else {
            $tpl_name = $this->getAdminTplFolder() . '/data_form.tpl';
        }
        return $smarty->fetch($tpl_name);
    }

    /*
      protected function deleteUserImage($user_id){
      $DBC=DBC::getInstance();
      $query='SELECT imgfile FROM '.DB_PREFIX.'_user WHERE user_id=?';
      $stmt=$DBC->query($query, array($user_id));
      if($stmt){
      $ar=$DBC->fetch($stmt);
      $imgfile_directory=SITEBILL_DOCUMENT_ROOT.'/img/data/user/';
      @unlink($imgfile_directory.$ar['imgfile']);
      $query='UPDATE '.DB_PREFIX.'_user SET imgfile=\'\' WHERE user_id=?';
      $stmt=$DBC->query($query, array($user_id));
      }
      }
     */

    private function showProfile($user_id) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $form_data = $data_model->init_model_data_from_db($this->table_name, $this->primary_key, $user_id, $this->data_model[$this->table_name]);
        unset($form_data['captcha']);
        unset($form_data['active']);

        unset($form_data['publication_limit']);
        unset($form_data['captcha']);
        unset($form_data['notify']);

        $form_data1 = array();
        $form_data1['group_id']['type'] = 'safe_string';
        $form_data1['group_id']['title'] = 'Группа';
        $form_data1['group_id']['value'] = $_SESSION['current_user_group_title'];
        foreach ($form_data as $k => $v) {
            $form_data1[$k] = $v;
        }

        $form_data = $form_data1;
        unset($form_data1);

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_view.php');
        $form_view = new Form_View_Generator();
        $rs = '<div class="profile_view_table">';
        $rs .= '<div class="form_element_control">';
        $rs .= '<form method="post" class="profile_edit">';
        $rs .= '<input type="hidden" name="do" value="edit" />';
        $rs .= '<input type="submit" name="submit" class="btn btn-primary" value="' . Multilanguage::_('EDIT_PROFILE', 'system') . '" />';
        $rs .= '</form>';
        $rs .= '<form method="get" class="password_edit" action="' . SITEBILL_MAIN_URL . '/account/profile/">';
        $rs .= '<input type="hidden" name="do" value="pass_edit" />';
        $rs .= '<input type="submit" name="submit" class="btn btn-primary" value="' . Multilanguage::_('EDIT_PROFILE_PASSWORD', 'system') . '" />';
        $rs .= '</form>';
        if (1 == intval($this->getConfigValue('allow_user_email_change'))) {
            $rs .= '<form method="get" class="password_edit" action="' . SITEBILL_MAIN_URL . '/account/profile/">';
            $rs .= '<input type="hidden" name="do" value="email_edit" />';
            $rs .= '<input type="submit" name="submit" class="btn btn-primary" value="' . Multilanguage::_('EDIT_PROFILE_EMAIL', 'system') . '" />';
            $rs .= '</form>';
        }

        $rs .= '</div>';
        $rs .= '<table class="table table-hover">';
        $rs .= $form_view->compile_form($form_data);
        $rs .= '</table>';
        //return $rs;



        $rs = '<div class="view_table">' . $rs . '</div>';

        $rs .= '</div>';
        return $rs;
    }

}