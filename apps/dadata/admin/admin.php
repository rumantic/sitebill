<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Dadata cleaner backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');

class dadata_admin extends Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();

        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/config/admin/admin.php');
        $config_admin = new config_admin();

        $config_admin->addParamToConfig('apps.dadata.enable', '0', 'Включить распознавание адресов через dadata.ru', 1);
        $config_admin->addParamToConfig('apps.dadata.cron_key', md5(time().rand(1, 999999)), 'Ключ запуска (передается через /apps/dadata/cron.php?sec=XXXX)');
        $config_admin->addParamToConfig('apps.dadata.apiKey', '', 'dadata.ru apiKey');
        $config_admin->addParamToConfig('apps.dadata.secretKey', '', 'dadata.ru secretKey');
        $config_admin->addParamToConfig('apps.dadata.limit', '5', 'Количество записей обрабатываемых за 1 запуск');
        $config_admin->addParamToConfig('apps.dadata.default_city', '', 'Город по-умолчанию');
        $config_admin->addParamToConfig('apps.dadata.address_column', 'parser_address', 'Название колонки в таблице data, в которой хранится сырой адрес');
        $config_admin->addParamToConfig(
            'apps.dadata.check_street_id',
            '0',
            'Не парсить объявление, если уже указана улица',
            1
        );
        $config_admin->addParamToConfig(
            'apps.dadata.parsed_flag',
            'adsapi_loaded',
            'Название поля checkbox для признака записей, которые нужно распознавать',
            0
        );
    }
}
