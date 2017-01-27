<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
require_once (SITEBILL_DOCUMENT_ROOT . '/apps/logger/admin/admin.php');

class logger_update extends logger_admin {

    function main($secret_key = '') {
	$DBC = DBC::getInstance();
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_logger ADD column user_id INT(11) NOT NULL default 0";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_logger ADD column ipaddr varchar(255) NOT NULL default ''";

	$rs = 'Обновление базы данных<br/>';

	foreach ($query_data as $query) {
	    $success = false;
	    $rows = 0;
	    $stmt = $DBC->query($query, array(), $rows, $success);
	    if (!$success) {
		//$rs .= Multilanguage::_('ERROR_ON_SQL_RUN','system').': '.$query.'<br>';
	    } else {
		$rs .= Multilanguage::_('QUERY_SUCCESS', 'system') . ': ' . $query . '<br>';
	    }
	}
	$rs .= 'Обновление базы данных завершено<br/>';

	return $rs;
    }

}
