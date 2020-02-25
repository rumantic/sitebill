<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
require_once (SITEBILL_DOCUMENT_ROOT . '/apps/geodata/admin/admin.php');

class geodata_update extends geodata_admin {

    function main($secret_key = '') {
        $DBC = DBC::getInstance();
        $query = "delete from " . DB_PREFIX . "_config where config_key='apps.geodata.on_home'";
        $stmt = $DBC->query($query, array(), $rows, $success);       
        
        $query = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_geodata_geocodercache` ( `id` int(11) NOT NULL AUTO_INCREMENT, `address_str` varchar(255) NOT NULL, `geocode_result` text NOT NULL, PRIMARY KEY (`id`), KEY `address` (`address_str`) ) ENGINE=MyISAM DEFAULT CHARSET=" . DB_ENCODING."";  
        $stmt = $DBC->query($query);
        
        $rs = 'Обновление конфигурации';
        return $rs;
    }

}