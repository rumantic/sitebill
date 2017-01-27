<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
require_once (SITEBILL_DOCUMENT_ROOT . '/apps/geodata/admin/admin.php');

class geodata_update extends geodata_admin {

    function main($secret_key = '') {
	$rs = 'Обновление конфигурации';
	return $rs;
    }

}
