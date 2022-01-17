<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Config REST class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_config extends API_Common {
    /**
     * @var config_admin
     */
    private $config_admin;

    function __construct()
    {
        parent::__construct();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $this->config_admin = new config_admin();

    }

    function _get () {
        $user_id = $this->get_my_user_id();

        if ($this->permission->get_access($user_id, 'config', 'view')) {
            $response = new API_Response('success', 'config loaded', $this->getAllConfigArray());
        } else {
            $response = new API_Response('error', 'load config failed: access denied');
        }

        return $this->json_string($response->get());
    }

    function _system_config () {
        $user_id = $this->get_my_user_id();

        if ($this->permission->get_access($user_id, 'config', 'view')) {
            $response = new API_Response('success', 'config loaded', $this->config_admin->api_array());
        } else {
            $response = new API_Response('error', 'load config failed: access denied');
        }

        return $this->json_string($response->get());
    }

    function _update () {
        $user_id = $this->get_my_user_id();
        $ql_items = $this->request->get('ql_items');

        if ($this->permission->get_access($user_id, 'config', 'view')) {
            try {
                foreach ( $ql_items as $key => $value ) {
                    if ( !$this->config_admin->updateParamByKey($key, $value) ) {
                        throw new \Exception($this->GetErrorMessage());
                    }
                }
                $response = new API_Response('success', 'config updated', $this->config_admin->api_array());
            } catch (\Exception $e) {
                $response = new API_Response('error', 'update config failed: '.$e->getMessage());
            }
        } else {
            $response = new API_Response('error', 'update config failed: access denied');
        }

        return $this->json_string($response->get());
    }

    function _getHiddenConfigValue () {
        $user_id = $this->get_my_user_id();
        $key = $this->request->get('key');

        if ($this->permission->get_access($user_id, 'config', 'view')) {
            $Config=SConfig::getInstance();
            $response = new API_Response('success', 'config loaded', $Config::getHiddenConfigValue($key));
        } else {
            $response = new API_Response('error', 'load config failed: access denied');
        }

        return $this->json_string($response->get());
    }

    function _updateHiddenConfigValue () {
        $user_id = $this->get_my_user_id();
        $key = $this->request->get('key');
        $value = $this->request->get('value');

        if ($this->permission->get_access($user_id, 'config', 'view')) {
            $Config=SConfig::getInstance();
            $response = new API_Response('success', 'config updated', $Config::updateHiddenConfigValue($key, $value));
        } else {
            $response = new API_Response('error', 'load config failed: access denied');
        }

        return $this->json_string($response->get());

    }

    function _store_user_settings(){
        $user_id = $this->get_my_user_id();
        $params = $this->request->get('params');
        $confkey = 'user.settings.'.$user_id;
        if ($this->permission->get_access($user_id, 'config', 'view')) {
            $Config=SConfig::getInstance();
            $response = new API_Response('success', 'config updated', $Config::storeHiddenConfigValueParams($confkey, $params));
        } else {
            $response = new API_Response('error', 'load config failed: access denied');
        }
        return $this->json_string($response->get());
    }

}
