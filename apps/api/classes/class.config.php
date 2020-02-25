<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Config REST class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_config extends API_Common {
    function _get () {
        $user_id = $this->get_my_user_id();
        
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $permission = new Permission();
        if ($permission->get_access($user_id, 'config', 'view')) {
            $response = new API_Response('success', 'config loaded', $this->getAllConfigArray());
        } else {
            $response = new API_Response('error', 'load config failed: access denied');
        }
        
        return $this->json_string($response->get());
    }
    
    function _getHiddenConfigValue () {
        $user_id = $this->get_my_user_id();
        $key = $this->request->get('key');
        
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $permission = new Permission();
        if ($permission->get_access($user_id, 'config', 'view')) {
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
        
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $permission = new Permission();
        if ($permission->get_access($user_id, 'config', 'view')) {
            $Config=SConfig::getInstance();
            //$response = new API_Response('success', 'config updated', $Config::updateHiddenConfigValue($key, $value));
            $response = new API_Response('success', 'config updated', $Config::updateHiddenConfigValue($key, $value));
        } else {
            $response = new API_Response('error', 'load config failed: access denied');
        }
        
        return $this->json_string($response->get());
        
    }
    
}
