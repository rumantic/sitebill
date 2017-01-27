<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
require_once (SITEBILL_DOCUMENT_ROOT . '/apps/seo/admin/admin.php');

class seo_update extends seo_admin {

    function main($secret_key = '') {
	$rs = 'Обновление конфигурации';
	return $rs;
    }

}
