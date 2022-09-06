<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once (SITEBILL_DOCUMENT_ROOT . '/apps/downloader/admin/admin.php');
class downloader_update extends downloader_admin {
    /**
     * Construct
     */
    function __construct() {
        parent::__construct();
    }

    function main ($secret_key = '') {
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_admin = new config_admin();

        $config_admin->addParamToConfig(
            'apps.downloader.enable',
            '0',
            'Разрешить скачивание фотографий объявлений одним файлом',
            1
        );
        $config_admin->addParamToConfig(
            'apps.downloader.alias',
            'imgzip',
            'Алиас приложения для скачивания картинок'
        );

        $config_admin->addParamToConfig(
            'apps.downloader.src_enable',
            '0',
            'Разрешить скачивание фотографий объявлений по URL источника',
            1
        );

        $config_admin->addParamToConfig(
            'apps.downloader.src_alias',
            'download_src',
            'Алиас приложения для скачивания одной картинки'
        );

        $rs = '<br>Обновление конфигурации<br>';
        return $rs;
    }
}
