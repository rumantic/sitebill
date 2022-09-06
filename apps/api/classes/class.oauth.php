<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Login REST class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_oauth extends API_Common {

    public function _login() {
        $login = $this->request->get('login');
        $password = $this->request->get('password');
        $rememberme = (int) $this->request->get('rememberme');

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/login.php');
        $Login = new Login();

        //$this->writeLog(array('apps_name' => 'apps.api', 'method' => __METHOD__, 'message' => 'login = ' . $login . ', md5(password) = ' . md5($password), 'type' => NOTICE));


        if (TRUE === $Login->checkLogin($login, $password, $rememberme)) {
            //$this->writeLog(array('apps_name' => 'apps.api', 'method' => __METHOD__, 'message' => 'session = <pre>' . var_export($_SESSION, true) . '</pre>' . 'login = ' . $login . ', md5(password) = ' . md5($password), 'type' => NOTICE));

            $DBC = DBC::getInstance();
            //$query = "SELECT user_id, fio, group_id FROM ".DB_PREFIX."_user WHERE login='?' and password='?'".(1==$this->getConfigValue('use_registration_email_confirm') ? ' AND active=1' : '');
            $query = 'SELECT user_id, group_id FROM ' . DB_PREFIX . '_user WHERE (login=? or email=?) and password=?';

            $stmt = $DBC->query($query, array($login, $login, md5($password)));

            $ar = $DBC->fetch($stmt);
            if ($ar['user_id'] > 0) {

                //$this->writeLog(array('apps_name' => 'apps.api', 'method' => __METHOD__, 'message' => 'login success ' . var_export($ar, true), 'type' => NOTICE));
                $ar['session_key'] = $this->init_session_key($ar['user_id'], ' login');
                $this->setSessionUserId($ar['user_id']);
                $Login->makeUserLogged($ar['user_id'], 0, $ar['session_key']);
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
                $permission = new Permission();
                if ($permission->get_access($ar['user_id'], 'admin_panel', 'login')) {
                    $ar['admin_panel_login'] = 1;
                } else {
                    $ar['admin_panel_login'] = 0;
                }
                $ar['success'] = 1;
                $ar['api_url'] = $this->get_domain_https();
                $structure = $permission->get_structure();
                $ar['structure'] = $structure[$ar['group_id']];
                if ( !$this->getConfigValue('check_permissions') and $structure[$ar['group_id']]['group_name'] != 'admin' ) {
                    $ar['admin_panel_login'] = 0;
                }

                return $this->json_string($ar);
            }
        }
        //$this->writeLog(array('apps_name' => 'apps.api', 'method' => __METHOD__, 'message' => 'login failed', 'type' => ERROR));
        return $this->request_failed('login failed');
    }

    public function _register () {
        if ( !$this->getConfigValue('allow_register_account') ) {
            return $this->request_failed('register disabled');
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/register_using_model.php');
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/register/local_register_using_model.php')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/register/local_register_using_model.php');
            $Register = new Local_Register_Using_Model();
        } else {
            $Register = new Register_Using_Model();
        }

        $this->setRequestValue('do', 'new_done');
        $Register->setRequestValue('json', true);
        $Register->setRequestValue('login', $this->request->get('login'));
        $Register->setRequestValue('email', $this->request->get('login'));
        $Register->setRequestValue('newpass', $this->request->get('password'));
        $Register->setRequestValue('newpass_retype', $this->request->get('password_retype'));

        return $Register->ajaxRegister();
    }

    public function _remind() {
        if (!$this->getConfigValue('allow_remind_password')) {
            return $this->request_failed(_e('Функция напоминания пароля отключена администратором'));
        } else {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/user.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/remind.php');
            $remind = new Remind;
            $success_message = $remind->get_user_and_remind($this->request->get('login'), $this->request->get('login'));
            if ( !$success_message ) {
                return $this->request_failed($remind->getError());
            } else {
                return $this->request_success($success_message);
            }
        }
    }

    public function _remind_validate_code() {
        if (!$this->getConfigValue('allow_remind_password')) {
            return $this->request_failed(_e('Функция напоминания пароля отключена администратором'));
        } else {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/user.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/remind.php');
            $remind = new Remind;
            $success_message = $remind->process_remind_code($this->request->get('code'));
            if ( !$success_message ) {
                return $this->request_failed($remind->getError());
            } else {
                return $this->request_success($success_message);
            }
        }
    }


    public function _logout() {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/logout.php');
        $Logout = new Logout;
        $Logout->clear_session_and_cookies();
        return $this->request_success('logout_complete');
    }

    public function _check_session_key() {
        $session_key = $this->request->get('session_key');
        $start_session_key = $this->request->get('session_key');
        //В последнюю очередь попробуем получить ключ сессии из SESSION
        //В случае если к API обращается локальный сайт (сам к себе)
        if ($session_key == '') {
            $session_key_local = $this->get_session_key();
            $session_key = $session_key_local;
        } else if ( $session_key == 'nobody' ) {
            return $this->json_string($this->init_nobody_session());
        }

        //echo $session_key;
        $DBC = DBC::getInstance();
        $need_init_oauth = false;
        if (isset($session_key_local)) {
            $query = 'SELECT user_id FROM ' . DB_PREFIX . '_session WHERE session_key=?';
            $need_init_oauth = true;
        } else {
            $query = 'SELECT user_id FROM ' . DB_PREFIX . '_oauth WHERE session_key=?';
        }
        //$this->writeLog(array('apps_name' => 'apps.api', 'method' => __METHOD__, 'message' => 'check session_key, session_key = ' . $session_key, 'type' => NOTICE));

        $stmt = $DBC->query($query, array($session_key));

        if ($stmt) {
            $ar = $DBC->fetch($stmt);

            if ($ar['user_id'] > 0) {
                $ar = $this->init_success_response($ar['user_id'], $session_key, $need_init_oauth);
                $ar['step'] = 'first';
                $ar['session_key_local'] = $this->get_session_key();
                $ar['start_session_key'] = $start_session_key;
                $ar['need_init_oauth'] = $need_init_oauth;
                return $this->json_string($ar);
            }
        }
        // Теперь попробуем восстановить oauth
        $session_key_local = $this->get_session_key();
        if ( $session_key_local != '' and $session_key != '' ) {
            $query = 'SELECT user_id FROM ' . DB_PREFIX . '_session WHERE session_key=?';
            $stmt = $DBC->query($query, array($session_key_local));

            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                if ($ar['user_id'] > 0) {
                    $query = 'insert into ' . DB_PREFIX . '_oauth (user_id, ip, session_key) values (?, ?, ?)';
                    $user_ip = $_SERVER['REMOTE_ADDR'];
                    $stmt = $DBC->query($query, array($ar['user_id'], $user_ip, $session_key));
                    $ar = $this->init_success_response($ar['user_id'], $session_key, false);
                    $ar['step'] = 'second';
                    $ar['session_key_local'] = $session_key_local;
                    $ar['start_session_key'] = $start_session_key;
                    $ar['need_init_oauth'] = false;
                    return $this->json_string($ar);
                }
            }

        }


        $this->riseError('check_session_key_failed');
        return $this->request_failed('check_session_key_failed');
    }

    private function init_success_response ($user_id, $session_key, $need_init_oauth) {
        $ar = array();
        $ar['user_id'] = $user_id;
        $ar['config']['per_page'] = $this->getConfigValue('per_page');

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $permission = new Permission();
        if ($permission->get_access($ar['user_id'], 'admin_panel', 'login')) {
            $ar['admin_panel_login'] = 1;
        } else {
            $ar['admin_panel_login'] = 0;
        }
        if ( !isset($_SESSION['current_user_group_id']) ) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/login.php');
            $Login = new Login();
            $Login->loadUserInfo($ar['user_id']);
        }

        $structure = $permission->get_structure();
        $ar['structure'] = $structure[$_SESSION['current_user_group_id']];

        $ar['success'] = 1;
        $ar['api_url'] = $this->get_domain_https();

        if ($need_init_oauth) {
            $ar['session_key'] = $this->init_session_key($ar['user_id'], ' response');
            $ar['new_session_key'] = true;
        } else {
            $ar['session_key'] = $session_key;
        }
        return $ar;
    }

    public function _get_access () {
        $user_id = $this->get_my_user_id();
        $model_name = $this->request->get('model_name');
        $function_name = $this->request->get('function_name');

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $permission = new Permission();
        if (!$permission->get_access($user_id, $model_name, $function_name)) {
            $response = new API_Response('error', _e('Доступ запрещен'));
        } else {
            $response = new API_Response('success', _e('Доступ открыт'));
        }
        return $this->json_string($response->get());
    }

    public function _load_my_profile () {
        $user_id = $this->get_my_user_id();
        if ( $user_id > 0 ) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');
            $user_object_manager = new User_Object_Manager();
            $user_object_manager->data_model = $user_object_manager->get_user_model(true);

            $ar['state'] = 'success';
            $ar['data'] = $user_object_manager->load_profile($user_id);

            return $this->json_string($ar);
        }
        $this->riseError('load_profile_failed');
        return $this->request_failed('load_profile_failed');
    }

    private function init_session_key($user_id, $place = ' ?') {
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $session_key = md5(rand() . time() . $user_ip);

        $query = 'insert into ' . DB_PREFIX . '_oauth (user_id, ip, session_key) values (?, ?, ?)';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($user_id, $user_ip, $session_key));
        return $session_key;
    }

    private function init_nobody_session () {
        $user_id = 0;
        //$ar['session_key'] = $this->init_session_key($user_id);
        $ar['session_key'] = 'nobody';

        $ar['user_id'] = $user_id;
        $ar['config']['per_page'] = $this->getConfigValue('per_page');
        $ar['admin_panel_login'] = 0;
        $ar['success'] = 1;
        $ar['api_url'] = $this->get_domain_https();

        $ar['structure']['group_name'] = 'nobody';
        $ar['structure']['data']['access'] = 1;
        $ar['structure']['sale']['access'] = 1;
        return $ar;
    }

    private function get_domain_https () {
        return 'https://'.$_SERVER['HTTP_HOST'];
    }

}
