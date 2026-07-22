<?php

/**
 * Register using model
 * @author Kondin Dmitriy <kondin@etown.ru>
 *
 * Refactored: methods extracted into 5 traits.
 */
require_once __DIR__ . '/traits/RegisterFormTrait.php';
require_once __DIR__ . '/traits/RegisterVerifyTrait.php';
require_once __DIR__ . '/traits/RegisterNotifyTrait.php';
require_once __DIR__ . '/traits/RegisterValidationTrait.php';
require_once __DIR__ . '/traits/RegisterSubmitTrait.php';

class Register_Using_Model extends User_Object_Manager
{
    use RegisterFormTrait;
    use RegisterVerifyTrait;
    use RegisterNotifyTrait;
    use RegisterValidationTrait;
    use RegisterSubmitTrait;

    public $register_social = false;

    function __construct()
    {
        parent::__construct();
        if ($this->getConfigValue('email_as_login')) {
            $this->data_model['user']['login']['type'] = 'hidden';
            $this->data_model['user']['login']['required'] = 'off';
        }
    }

    function main()
    {
        if ($_SESSION['user_id'] > 0) {
            header('location: /');
            exit();
        }

        $do = $this->getRequestValue('do');
        $action = '_' . $do . 'Action';
        if (!method_exists($this, $action)) {
            $action = '_defaultAction';
        }
        return $this->$action();
    }

    protected function _defaultAction()
    {
        $rs = '';


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

        //print_r($form_data);

        if (isset($form_data[$this->table_name]['group_id'])) {
            $shared_groups = $this->getConfigValue('newuser_registration_shared_groupid');
            $shared_groups = preg_replace('/[^\d,]/', '', $shared_groups);
            if ($shared_groups != '') {
                $form_data[$this->table_name]['group_id']['query'] = 'SELECT group_id, name FROM ' . DB_PREFIX . '_group WHERE group_id IN (' . $shared_groups . ')';
            } else {
                $form_data[$this->table_name]['group_id']['query'] = 'SELECT group_id, name FROM ' . DB_PREFIX . '_group WHERE group_id=0';
            }
        }

        $this->addAgreementElement($form_data[$this->table_name]);

        $register_url = SITEBILL_MAIN_URL . '/register/';
        if ($this->register_social) {
            if (isset($form_data[$this->table_name]['email'])) {
                $form_data[$this->table_name]['email']['value'] = $_SESSION['ssAuthData']['email'];
            }
            if (isset($form_data[$this->table_name]['fio'])) {
                $form_data[$this->table_name]['fio']['value'] = $_SESSION['ssAuthData']['name'];
            }
            if (isset($form_data[$this->table_name]['login'])) {
                $form_data[$this->table_name]['login']['value'] = $_SESSION['ssAuthData']['_login'];
            }
            if (isset($form_data[$this->table_name]['newpass'])) {
                $form_data[$this->table_name]['newpass']['value'] = $_SESSION['ssAuthData']['_pass'];
            }
            if (isset($form_data[$this->table_name]['newpass_retype'])) {
                $form_data[$this->table_name]['newpass_retype']['value'] = $_SESSION['ssAuthData']['_pass'];
            }
            $register_url = SITEBILL_MAIN_URL . '/socialauth/register/';
        }

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/register_user.tpl')) {
            global $smarty;
            $smarty->assign('register_form', $this->get_form($form_data[$this->table_name], 'new', 0, Multilanguage::_('L_GOREGISTER_BUTTON'), SITEBILL_MAIN_URL . '/register/'));
            return $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/register_user.tpl');
        } else {
            return $this->get_form($form_data[$this->table_name], 'new', 0, Multilanguage::_('L_GOREGISTER_BUTTON'), $register_url);
        }

        return $rs;
    }

    function welcome()
    {
        $rs = '<h3>' . Multilanguage::_('REGISTER_SUCCESS', 'system') . '</h3><br>';
        $rs .= '<a href="' . SITEBILL_MAIN_URL . '/account/">' . Multilanguage::_('PRIVATE_ACCOUNT', 'system') . '</a>';
        return $rs;
    }

    public function getUniqLogin($login)
    {
        if (!$this->checkLogin($login)) {
            $DBC = DBC::getInstance();
            $query = 'SELECT login FROM ' . DB_PREFIX . '_user WHERE login LIKE \'' . $login . '%\'';

            $stmt = $DBC->query($query);
            $used_logins = array();
            $used_numbers = array();
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $used_logins[] = $ar['login'];
                }
            }

            foreach ($used_logins as $used_login) {
                if (preg_match('/^' . $login . '(\d+)$/', $used_login, $matches)) {
                    $used_numbers[] = (int)$matches[1];
                }
            }
            if (empty($used_numbers)) {
                $login = $login . '1';
            } else {

                rsort($used_numbers);
                $login = $login . ($used_numbers[0] + 1);
            }
        }
        return $login;
    }

}
