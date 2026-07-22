<?php
/**
 * RegisterSubmitTrait — registration submit logic for Register_Using_Model
 *
 * Extracted methods:
 *   - _new_doneAction()
 *   - ajaxRegister()
 *   - postPreparedOperations($form_data)
 *   - postRegisterAction($form_data)
 *   - newuser_registration_shared_groupid_array()
 */
trait RegisterSubmitTrait
{
    protected function postPreparedOperations($form_data)
    {
        return $form_data;
    }

    protected function _new_doneAction()
    {
        $rs = '';


        /*$token = $_POST['_csrf_token'];
        if(false === $this->checkCSRFToken($token)){
            echo 'Possible CSRF attack';
            exit();
        }*/


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        $used_local_model = false;
        $form_data = array();

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data_local = $ATH->load_model('user_register', false);
            if ($form_data_local && !empty($form_data_local['user_register'])) {
                $form_data[$this->table_name] = $form_data_local['user_register'];
                $used_local_model = true;
            }
        }

        if (empty($form_data)) {
            $form_data = $this->data_model;
        }

        $form_data[$this->table_name]['newpass']['required'] = 'on';
        $form_data[$this->table_name]['newpass_retype']['required'] = 'on';
        unset($form_data[$this->table_name]['active']);

        /* if(isset($form_data[$this->table_name]['group_id'])){
          $shared_groups=$this->getConfigValue('newuser_registration_shared_groupid');
          $shared_groups=preg_replace('/[^\d,]/', '', $shared_groups);
          if($shared_groups!=''){
          $form_data[$this->table_name]['group_id']['query']='SELECT group_id, name FROM '.DB_PREFIX.'_group WHERE group_id IN ('.$shared_groups.')';
          }else{
          $form_data[$this->table_name]['group_id']['query']='SELECT group_id, name FROM '.DB_PREFIX.'_group WHERE group_id=0';
          }
          } */

        if (isset($form_data[$this->table_name]['group_id'])) {
            $groups = array();
            $shared_groups = $this->getConfigValue('newuser_registration_shared_groupid');
            $shared_groups = preg_replace('/[^\d,]/', '', $shared_groups);
            $groups = explode(',', $shared_groups);
            if ($shared_groups != '') {
                $form_data[$this->table_name]['group_id']['query'] = 'SELECT group_id, name FROM ' . DB_PREFIX . '_group WHERE group_id IN (' . $shared_groups . ')';
            } else {
                $form_data[$this->table_name]['group_id']['query'] = 'SELECT group_id, name FROM ' . DB_PREFIX . '_group WHERE group_id=0';
            }
        }

        $this->addAgreementElement($form_data[$this->table_name]);

        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);

        if ($this->register_social) {
            if (isset($form_data[$this->table_name]['login']) && $form_data[$this->table_name]['login']['value'] == '') {
                $form_data[$this->table_name]['login']['value'] = $_SESSION['ssAuthData']['_login'];
            }
            if (isset($form_data[$this->table_name]['email']) && $form_data[$this->table_name]['email']['value'] == '') {
                $form_data[$this->table_name]['email']['value'] = $_SESSION['ssAuthData']['email'];
            }
            if (isset($form_data[$this->table_name]['fio']) && $form_data[$this->table_name]['fio']['value'] == '') {
                $form_data[$this->table_name]['fio']['value'] = $_SESSION['ssAuthData']['name'];
            }
            if (isset($form_data[$this->table_name]['group_id']) && intval($form_data[$this->table_name]['group_id']['value']) == 0) {
                $form_data[$this->table_name]['group_id']['value'] = $this->getConfigValue('apps.socialauth.default_group_id');
            }
            if (isset($form_data[$this->table_name]['newpass']) && intval($form_data[$this->table_name]['newpass']['value']) == 0) {
                $form_data[$this->table_name]['newpass']['value'] = $_SESSION['ssAuthData']['_pass'];
            }
            if (isset($form_data[$this->table_name]['newpass_retype']) && intval($form_data[$this->table_name]['newpass_retype']['value']) == 0) {
                $form_data[$this->table_name]['newpass_retype']['value'] = $_SESSION['ssAuthData']['_pass'];
            }
            if ($this->getConfigValue('register_form_agreement_enable') == 1 && strtolower($_SERVER['REQUEST_METHOD']) == 'get') {
                if ($this->getConfigValue('register_form_agreement_enable_ch') == 1) {
                    $form_data[$this->table_name]['_post_agreement_check']['value'] = '1';
                }
            }
        }


        $default_group_id = 0;
        if (0 != intval($this->getConfigValue('newuser_registration_groupid'))) {
            $default_group_id = intval($this->getConfigValue('newuser_registration_groupid'));
        } else {
            $default_group_id = $this->getGroupIdByName('realtor');
        }

        if (!isset($form_data[$this->table_name]['group_id'])) {
            $form_data[$this->table_name]['group_id']['value'] = $default_group_id;
        } else {
            if ('' != $this->getConfigValue('newuser_registration_shared_groupid')) {

                /* $groups=array();
                  $shared_groups=$this->getConfigValue('newuser_registration_shared_groupid');
                  $shared_groups=preg_replace('/[^\d,]/', '', $shared_groups);
                  $groups=explode(',', $shared_groups); */

                if (!in_array($form_data[$this->table_name]['group_id']['value'], $groups) && !$this->register_social) {
                    $form_data[$this->table_name]['group_id']['value'] = $default_group_id;
                } elseif (!in_array($form_data[$this->table_name]['group_id']['value'], $groups) && $this->register_social) {
                    $form_data[$this->table_name]['group_id']['value'] = 0;
                }
            } else {
                if (!$this->register_social) {
                    $form_data[$this->table_name]['group_id']['value'] = $default_group_id;
                } else {
                    $form_data[$this->table_name]['group_id']['value'] = 0;
                }
            }
        }
        if (0 != (int)$this->getConfigValue('apps.billing.default_tariff_id')) {
            $form_data[$this->table_name]['tariff_id']['value'] = $this->getConfigValue('apps.billing.default_tariff_id');
        }

        if (1 == intval($this->getConfigValue('email_as_login')) && isset($form_data[$this->table_name]['login']) && $form_data[$this->table_name]['login']['value'] == '') {
            $form_data[$this->table_name]['login']['value'] = $form_data[$this->table_name]['email']['value'];
        }


        if (isset($form_data[$this->table_name]['reg_date'])) {
            $form_data[$this->table_name]['reg_date']['value'] = date('Y-m-d H:i:s');
        } elseif ($used_local_model && isset($this->data_model[$this->table_name]['reg_date'])) {
            $form_data[$this->table_name]['reg_date'] = $this->data_model[$this->table_name]['reg_date'];
            $form_data[$this->table_name]['reg_date']['value'] = date('Y-m-d H:i:s');
        }

        $form_data[$this->table_name] = $this->postPreparedOperations($form_data[$this->table_name]);

        $register_url = SITEBILL_MAIN_URL . '/register/';
        if ($this->register_social) {
            $register_url = SITEBILL_MAIN_URL . '/socialauth/register/';
        }

        if (!$this->check_data($form_data[$this->table_name])) {
            $form_data[$this->table_name]['imgfile']['value'] = '';
            $rs = $this->get_form($form_data[$this->table_name], 'new', 0, Multilanguage::_('L_GOREGISTER_BUTTON'), $register_url);
        } else {
            $new_user_id = $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));

            $need_activation = false;

            if ($this->register_social) {
                $register_social_data = $_SESSION['ssAuthData'];
                unset($_SESSION['ssAuthData']);
            }

            $email = $form_data[$this->table_name]['email']['value'];

            if (1 == $this->getConfigValue('use_registration_email_confirm') && (($this->register_social && $email != $register_social_data['email']) || (!$this->register_social))) {
                $need_activation = true;
            }


            if ($this->getError()) {
                $form_data[$this->table_name]['imgfile']['value'] = '';
                $rs = $this->get_form($form_data[$this->table_name], 'new', 0, Multilanguage::_('L_GOREGISTER_BUTTON'), $register_url);
            } else {
                $email = $form_data[$this->table_name]['email']['value'];
                $login = $form_data[$this->table_name]['login']['value'];
                $password = $form_data[$this->table_name]['newpass']['value'];

                if (1 == $this->getConfigValue('apps.client.create_client_on_user_register')) {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/client/admin/admin.php');
                    $client_admin = new client_admin();
                    $client_admin->create_client_on_user_register($form_data[$this->table_name]);
                }


                if ($this->register_social) {
                    $DBC = DBC::getInstance();
                    $query = 'UPDATE ' . DB_PREFIX . '_user SET `' . $register_social_data['ssType'] . '_id`=? WHERE user_id=?';
                    $stmt = $DBC->query($query, array($register_social_data['id'], $new_user_id));
                }

                if ($need_activation) {
                    $DBC = DBC::getInstance();
                    $activation_code = md5(time() . '_' . rand(100, 999));
                    $query = 'UPDATE ' . DB_PREFIX . '_user SET pass=? WHERE user_id=?';
                    $stmt = $DBC->query($query, array($activation_code, $new_user_id));
                    $activation_link = '<a href="http://' . $_SERVER['HTTP_HOST'] . SITEBILL_MAIN_URL . '/register?do=activate&activation_code=' . $activation_code . '&email=' . $email . '">http://' . $_SERVER['HTTP_HOST'] . SITEBILL_MAIN_URL . '/register?do=activate&activation_code=' . $activation_code . '&email=' . $email . '</a>';
                    if (1 == intval($this->getConfigValue('email_as_login'))) {
                        $mail_login = $email;
                    } else {
                        $mail_login = $login;
                    }
                    $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/template/mails/user_registration_conf.tpl';
                    global $smarty;
                    $smarty->assign('mail_activation_link', $this->getServerFullUrl() . '/register?do=activate&activation_code=' . $activation_code . '&email=' . $email);
                    $smarty->assign('mail_server', $this->getServerFullUrl());
                    $smarty->assign('mail_current_language', Multilanguage::get_current_language());

                    if (file_exists($tpl)) {
                        //$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/system/template/mails/user_registration_conf.tpl';
                        global $smarty;

                        $smarty->assign('mail_login', $mail_login);
                        $smarty->assign('mail_password', $password);
                        $smarty->assign('mail_activation_link', $this->getServerFullUrl() . '/register?do=activate&activation_code=' . $activation_code . '&email=' . $email);
                        $smarty->assign('mail_server', $this->getServerFullUrl());
                        $smarty->assign('mail_current_language', Multilanguage::get_current_language());
                        $message = $smarty->fetch($tpl);
                    } else {
                        $message = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_BODY', 'system'), $activation_link);
                    }

                    if (Multilanguage::is_set('LT_NEW_REG_EMAILACCEPT_TITLE', '_template')) {
                        $subject = sprintf(Multilanguage::_('LT_NEW_REG_EMAILACCEPT_TITLE', '_template'), $_SERVER['HTTP_HOST']);
                    } else {
                        $subject = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_TITLE', 'system'), $_SERVER['HTTP_HOST']);
                    }


                    $to = $email;
                    $from = $this->getConfigValue('system_email');

                    $this->template->assign('login', $login);
                    $this->template->assign('password', $password);
                    $this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
                    $email_template_fetched = $this->fetch_email_template('registration_email_confirm');

                    if ($email_template_fetched) {
                        $subject = $email_template_fetched['subject'];
                        $message = $email_template_fetched['message'];

                        $message_array['apps_name'] = 'register_using_model';
                        $message_array['method'] = __METHOD__;
                        $message_array['message'] = "subject = $subject, message = $message";
                        $message_array['type'] = '';
                        //$this->writeLog($message_array);
                    }


                    $this->sendFirmMail($to, $from, $subject, $message);
                    $query = 'DELETE FROM ' . DB_PREFIX . '_cache WHERE parameter=?';
                    $stmt = $DBC->query($query, array($activation_code));
                    $query = "insert into " . DB_PREFIX . "_cache (`parameter`, `value`) values (?, ?)";
                    $stmt = $DBC->query($query, array($activation_code, $password));

                    if (Multilanguage::is_set('LT_REGISTER_SUCCESS', '_template')) {
                        $rs = '<h3>' . Multilanguage::_('LT_REGISTER_SUCCESS', '_template') . '</h3><br>';
                    } else {
                        $rs = '<h3>' . Multilanguage::_('REGISTER_SUCCESS', 'system') . '</h3><br>';
                    }
                    if ($form_data[$this->table_name]['active']['value'] != 1) {
                        if (Multilanguage::is_set('LT_ACTIVATION_CODE_SENT', '_template')) {
                            $rs .= Multilanguage::_('LT_ACTIVATION_CODE_SENT', '_template');
                        } else {
                            $rs .= Multilanguage::_('ACTIVATION_CODE_SENT', 'system');
                        }
                    }
                    return $rs;
                } else {
                    $DBC = DBC::getInstance();
                    $query = 'UPDATE ' . DB_PREFIX . '_user SET `active`=? WHERE user_id=?';
                    $stmt = $DBC->query($query, array(1, $new_user_id));
                }


                if (1 == $this->getConfigValue('registration_notice')) {
                    $this->send_registration_notice($form_data[$this->table_name]);
                }

                if (1 == $this->getConfigValue('notify_admin_about_register')) {
                    $this->notify_admin_about_register($new_user_id);
                }
                if (!$need_activation && $this->register_social) {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/login.php');
                    $login_object = new Login();
                    $login_object->setLoggedUser($new_user_id);
                    header('location: ' . SITEBILL_MAIN_URL . '/');
                    exit();
                }

                $rs = $this->postRegisterAction($form_data);
                //return $rs;
            }
        }

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/register_user.tpl')) {
            global $smarty;
            $smarty->assign('register_form', $rs);
            return $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/register_user.tpl');
        } else {
            return $rs;
        }

        return $rs;
    }

    protected function postRegisterAction($form_data)
    {
        $rs = '';
        if (Multilanguage::is_set('LT_REGISTER_SUCCESS', '_template')) {
            $rs .= '<h3>' . Multilanguage::_('LT_REGISTER_SUCCESS', '_template') . '</h3><br>';
        } else {
            if (1 == $this->getConfigValue('use_registration_email_confirm')) {
                $rs .= '<h3>' . Multilanguage::_('ACTIVATION_CODE_SENT', 'system') . '</h3><br>';
            } else {
                $rs .= Multilanguage::_('REGISTER_SUCCESS', 'system');
            }
        }
        $rs .= '<a href="' . SITEBILL_MAIN_URL . '/login/">Войти</a>';
        return $rs;
    }

    public function newuser_registration_shared_groupid_array()
    {
        $shared_groups = $this->getConfigValue('newuser_registration_shared_groupid');
        $shared_groups = preg_replace('/[^\d,]/', '', $shared_groups);
        if ($shared_groups != '') {
            $groups = explode(',', $shared_groups);
            if (is_array($groups) and count($groups) > 0) {
                return $groups;
            }
        }
        return false;
    }

    public function ajaxRegister()
    {

        /*$token = $_POST['_csrf_token'];
        if(false === $this->checkCSRFToken($token)){
            echo 'Possible CSRF attack';
            exit();
        }*/

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        $used_local_model = false;
        $form_data = array();

        $json_mode = false;
        if (1 == intval($this->getRequestValue('json'))) {
            $json_mode = true;
        }


        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/helper.php';
            $ATH = new Admin_Table_Helper();
            $form_data_local = $ATH->load_model('user_register', false);
            if ($form_data_local && !empty($form_data_local['user_register'])) {
                $form_data[$this->table_name] = $form_data_local['user_register'];
                $used_local_model = true;
            }
        }

        if (empty($form_data)) {
            $form_data = $this->data_model;
        }

        $this->addAgreementElement($form_data[$this->table_name]);


        $form_data[$this->table_name]['newpass']['required'] = 'on';
        $form_data[$this->table_name]['newpass_retype']['required'] = 'on';
        unset($form_data[$this->table_name]['active']);

        $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
        $form_data[$this->table_name]['login']['value'] = trim($form_data[$this->table_name]['login']['value']);


        if (1 == intval($this->getConfigValue('email_as_login')) && isset($form_data[$this->table_name]['login']) && $form_data[$this->table_name]['login']['value'] == '') {
            $form_data[$this->table_name]['login']['value'] = $form_data[$this->table_name]['email']['value'];
        }

        if (!isset($form_data[$this->table_name]['group_id'])) {
            if (0 != (int)$this->getConfigValue('newuser_registration_groupid')) {
                $form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
            } else {
                $form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
            }
        } else {
            if ('' != $this->getConfigValue('newuser_registration_shared_groupid')) {
                $groups = array();
                $shared_groups = $this->getConfigValue('newuser_registration_shared_groupid');
                $shared_groups = preg_replace('/[^\d,]/', '', $shared_groups);
                $groups = explode(',', $shared_groups);

                if (!in_array($form_data[$this->table_name]['group_id']['value'], $groups)) {
                    if (0 != (int)$this->getConfigValue('newuser_registration_groupid')) {
                        $form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
                    } else {
                        $form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
                    }
                }
            } else {
                if (0 != (int)$this->getConfigValue('newuser_registration_groupid')) {
                    $form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
                } else {
                    $form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
                }
            }
        }


        /*
          if(0!=(int)$this->getConfigValue('newuser_registration_groupid')){
          $form_data[$this->table_name]['group_id']['value'] = (int)$this->getConfigValue('newuser_registration_groupid');
          }else{
          $form_data[$this->table_name]['group_id']['value'] = $this->getGroupIdByName('realtor');
          }
         */

        if (isset($form_data[$this->table_name]['reg_date'])) {
            $form_data[$this->table_name]['reg_date']['value'] = date('Y-m-d H:i:s');
        } elseif ($used_local_model && isset($this->data_model[$this->table_name]['reg_date'])) {
            $form_data[$this->table_name]['reg_date'] = $this->data_model[$this->table_name]['reg_date'];
            $form_data[$this->table_name]['reg_date']['value'] = date('Y-m-d H:i:s');
        }

        foreach ($form_data[$this->table_name] as $it => $va) {
            $form_data[$this->table_name][$it]['value'] = SiteBill::iconv('utf-8', SITE_ENCODING, $va['value']);
        }

        $form_data[$this->table_name] = $this->postPreparedOperations($form_data[$this->table_name]);

        if (!$this->check_data($form_data[$this->table_name])) {
            $form_data[$this->table_name]['imgfile']['value'] = '';
            if ($json_mode) {
                return json_encode(array('result' => 0, 'msg' => $this->getError()));
            }
            return $this->getError();
        } else {
            $new_user_id = $this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
            if ($this->getError()) {
                $form_data[$this->table_name]['imgfile']['value'] = '';
                if ($json_mode) {
                    return json_encode(array('result' => 0, 'msg' => $this->getError()));
                }
                return $this->getError();
                $rs = $this->get_form($form_data[$this->table_name], 'new');
            } else {
                $email = $form_data[$this->table_name]['email']['value'];
                if ($this->getConfigValue('apps.sms.phone_source_column')) {
                    $login = $form_data[$this->table_name][$this->getConfigValue('apps.sms.phone_source_column')]['value'];
                } else {
                    if (1 == intval($this->getConfigValue('email_as_login'))) {
                        $login = $form_data[$this->table_name]['email']['value'];
                    } else {
                        $login = $form_data[$this->table_name]['login']['value'];
                    }
                }
                $password = $form_data[$this->table_name]['newpass']['value'];

                $this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
                $this->template->assign('login', $login);
                $this->template->assign('password', $password);

                if (1 == $this->getConfigValue('apps.client.create_client_on_user_register')) {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/client/admin/admin.php');
                    $client_admin = new client_admin();
                    $client_admin->create_client_on_user_register($form_data[$this->table_name]);
                }

                if (1 == $this->getConfigValue('use_registration_sms_confirm') && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/sms/admin/admin.php')) {
                    $activation_code = substr(md5(time() . '_' . rand(100, 999)), 0, 5);
                    $DBC = DBC::getInstance();
                    $query = 'UPDATE ' . DB_PREFIX . '_user SET pass=? WHERE user_id=?';
                    $stmt = $DBC->query($query, array($activation_code, $new_user_id));

                    $query = 'DELETE FROM ' . DB_PREFIX . '_cache WHERE parameter=?';
                    $stmt = $DBC->query($query, array($activation_code));
                    $query = 'INSERT INTO ' . DB_PREFIX . '_cache (`parameter`, `value`) VALUES (?, ?)';
                    $stmt = $DBC->query($query, array($activation_code, $password));

                    require_once SITEBILL_DOCUMENT_ROOT . '/apps/sms/admin/admin.php';
                    $SMS = new sms_admin();
                    if ($this->getConfigValue('apps.sms.sender') != '') {
                        $sms_sender = $this->getConfigValue('apps.sms.sender');
                    } else {
                        $sms_sender = 'sms_sender';
                    }
                    $r = $SMS->send($login, 'Vash kod: ' . $activation_code, $sms_sender);
                    if ($json_mode) {
                        return json_encode(array('result' => 1, 'msg' => 'confirm_sms_code'));
                    }
                    return 'confirm_sms_code';
                }


                if (1 == $this->getConfigValue('use_registration_email_confirm')) {
                    $DBC = DBC::getInstance();
                    $activation_code = md5(time() . '_' . rand(100, 999));
                    $query = 'UPDATE ' . DB_PREFIX . '_user SET pass=? WHERE user_id=?';
                    $stmt = $DBC->query($query, array($activation_code, $new_user_id));
                    $activation_link = '<a href="http://' . $_SERVER['HTTP_HOST'] . SITEBILL_MAIN_URL . '/register?do=activate&activation_code=' . $activation_code . '&email=' . $email . '">http://' . $_SERVER['HTTP_HOST'] . SITEBILL_MAIN_URL . '/register?do=activate&activation_code=' . $activation_code . '&email=' . $email . '</a>';

                    $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/system/template/mails/user_registration_conf.tpl';
                    global $smarty;
                    $smarty->assign('mail_activation_link', $this->getServerFullUrl() . '/register?do=activate&activation_code=' . $activation_code . '&email=' . $email);
                    $smarty->assign('mail_server', $this->getServerFullUrl());
                    $smarty->assign('mail_current_language', Multilanguage::get_current_language());

                    if (file_exists($tpl)) {
                        global $smarty;
                        $smarty->assign('mail_login', $login);
                        $smarty->assign('mail_password', $password);
                        $smarty->assign('mail_activation_link', $this->getServerFullUrl() . '/register?do=activate&activation_code=' . $activation_code . '&email=' . $email);
                        $smarty->assign('mail_server', $this->getServerFullUrl());
                        $smarty->assign('mail_current_language', Multilanguage::get_current_language());
                        $message = $smarty->fetch($tpl);
                    } else {
                        $message = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_BODY', 'system'), $activation_link);
                    }

                    if (Multilanguage::is_set('LT_NEW_REG_EMAILACCEPT_TITLE', '_template')) {
                        $subject = sprintf(Multilanguage::_('LT_NEW_REG_EMAILACCEPT_TITLE', '_template'), $_SERVER['HTTP_HOST']);
                    } else {
                        $subject = sprintf(Multilanguage::_('NEW_REG_EMAILACCEPT_TITLE', 'system'), $_SERVER['HTTP_HOST']);
                    }


                    $to = $email;
                    $from = $this->getConfigValue('system_email');

                    $email_template_fetched = $this->fetch_email_template('registration_email_confirm');

                    if ($email_template_fetched) {
                        $subject = $email_template_fetched['subject'];
                        $message = $email_template_fetched['message'];

                        $message_array['apps_name'] = 'register_using_model';
                        $message_array['method'] = __METHOD__;
                        $message_array['message'] = "subject = $subject, message = $message";
                        $message_array['type'] = '';
                        //$this->writeLog($message_array);
                    }

                    $this->sendFirmMail($to, $from, $subject, $message);
                    $query = 'DELETE FROM ' . DB_PREFIX . '_cache WHERE parameter=?';
                    $stmt = $DBC->query($query, array($activation_code));
                    $query = "insert into " . DB_PREFIX . "_cache (`parameter`, `value`) values (?, ?)";
                    $stmt = $DBC->query($query, array($activation_code, $password));

                    if (Multilanguage::is_set('LT_REGISTER_SUCCESS', '_template')) {
                        $rs = '<h3>' . Multilanguage::_('LT_REGISTER_SUCCESS', '_template') . '</h3><br>';
                    } else {
                        $rs = '<h3>' . Multilanguage::_('REGISTER_SUCCESS', 'system') . '</h3><br>';
                    }
                    if ($form_data[$this->table_name]['active']['value'] != 1) {
                        if (Multilanguage::is_set('LT_ACTIVATION_CODE_SENT', '_template')) {
                            $rs .= Multilanguage::_('LT_ACTIVATION_CODE_SENT', '_template');
                        } else {
                            $rs .= Multilanguage::_('ACTIVATION_CODE_SENT', 'system');
                        }
                    }
                    if ($json_mode) {
                        return json_encode(array('result' => 1, 'subres' => 'email_confirm', 'msg' => $rs));
                    }
                    return $rs;
                }

                if (1 == $this->getConfigValue('notify_admin_about_register')) {
                    $this->notify_admin_about_register($new_user_id);
                }


                if (1 == $this->getConfigValue('registration_notice')) {
                    $message = sprintf(Multilanguage::_('NEW_REGISTER_BODY', 'system'), $login, '***');
                    $subject = sprintf(Multilanguage::_('NEW_REGISTER_TITLE', 'system'), $_SERVER['HTTP_HOST']);

                    $to = $email;
                    $from = $this->getConfigValue('system_email');

                    $this->template->assign('target_url', $this->getServerFullUrl());

                    $email_template_fetched = $this->fetch_email_template('user_registration_complete');

                    if ($email_template_fetched) {
                        $subject = $email_template_fetched['subject'];
                        $message = $email_template_fetched['message'];

                        $message_array['apps_name'] = 'register_using_model';
                        $message_array['method'] = __METHOD__;
                        $message_array['message'] = "subject = $subject, message = $message";
                        $message_array['type'] = '';
                        //$this->writeLog($message_array);
                    }

                    $this->sendFirmMail($to, $from, $subject, $message);
                }


                $DBC = DBC::getInstance();
                $query = 'UPDATE ' . DB_PREFIX . '_user SET `active`=? WHERE user_id=?';
                $stmt = $DBC->query($query, array(1, $new_user_id));

                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/login.php');
                $login_object = new Login();
                $login_object->setLoggedUser($new_user_id);
                if ($json_mode) {
                    return json_encode(array('result' => 1, 'subres' => 'reload', 'msg' => ''));
                }
                return 'ok';
                $rs = $this->welcome();
            }
        }
    }
}
