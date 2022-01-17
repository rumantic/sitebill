<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Admin3 admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');

class admin3_admin extends Object_Manager
{
    use \system\traits\blade\BladeTrait;

    function __construct()
    {
        parent::__construct();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_manager = new config_admin();
        $config_manager->addParamToConfig(
            'apps.admin3.enable',
            '0',
            'Включить приложение Admin3',
            1
        );

        $config_manager->addParamToConfig(
            'apps.admin3.alias',
            'admin3',
            'Алиас приложения Admin3'
        );

        $config_manager->addParamToConfig(
            'apps.admin3.redirect_from_old_admin',
            '0',
            'Включить принудительную переадресацию со старой админки в новую',
            1
        );

        $config_manager->addParamToConfig(
            'apps.admin3.default_app',
            'data',
            'Запуск приложения по-умолчанию'
        );

        $this->add_apps_local_and_root_resource_paths('admin3');
    }
}
