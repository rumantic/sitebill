<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
require_once (SITEBILL_DOCUMENT_ROOT . '/apps/getrent/admin/admin.php');

class getrent_update extends getrent_admin {

    function main($secret_key = '') {
	$rs = 'Обновление конфигурации';
	return $rs;
    }

}
