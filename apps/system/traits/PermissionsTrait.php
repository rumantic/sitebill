<?php
namespace system\traits;

trait PermissionsTrait
{
    /**
     * @var \Permission
     */
    private $permission_instance;

    private function init_instance () {
        if ( !$this->permission_instance ) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
            $this->permission_instance = new \Permission();
        }
    }

    function get_access ($user_id, $model_name, $function_name = 'access') {
        $this->init_instance();
        return $this->permission_instance->get_access($user_id, $model_name, $function_name);
    }

    function get_permission_instance () {
        $this->init_instance();
        return $this->permission_instance;
    }

    function enableNobodyAccess ( $component = false ) {
        if ( $component ) {
            $component_name = $component;
        } else {
            $component_name = $this->action;
        }
        $this->init_instance();
        $this->permission_instance->add_group_permission(
            $this->permission_instance->get_nobody_group_id(),
            $component_name,
            $this->app_title,
            'access'
        );
    }

}
