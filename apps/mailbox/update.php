<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
require_once (SITEBILL_DOCUMENT_ROOT . '/apps/mailbox/admin/admin.php');

class mailbox_update extends mailbox_admin {

    function main($secret_key = '') {
	$rs = 'Обновление конфигурации';
	return $rs;
    }

}
