<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once(SITEBILL_DOCUMENT_ROOT . '/apps/akismet/admin/admin.php');

class akismet_update extends akismet_admin
{
    /**
     * Construct
     */
    function __construct()
    {
        parent::__construct();
    }

    function main($secret_key = '')
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_admin = new config_admin();
        if (!$config_admin->check_config_item('apps.akismet.enable')) {
            $config_admin->addParamToConfig('apps.akismet.enable', '1', 'Включить антиспам Akismet', 1);
        }

        if (!$config_admin->check_config_item('apps.akismet.key')) {
            $config_admin->addParamToConfig('apps.akismet.key', '227a31a0ec75', 'Ключ akismet. <a href="https://akismet.com/" target="_blank">Получить ключ</a>');
        }
        $config_admin->addParamToConfig('apps.akismet.auth_disable', '1', 'Не проверять антиспамом для авторизованных', 1);

        $rs = 'Обновление конфигурации';
        return $rs;
    }
}
