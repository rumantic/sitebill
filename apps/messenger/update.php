<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
require_once (SITEBILL_DOCUMENT_ROOT . '/apps/messenger/admin/admin.php');
class messenger_update extends messenger_admin {
    function main ( $secret_key = '' ) {
	return 'Обновление конфигурации<br>';
    }
}
