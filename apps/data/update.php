<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
require_once (SITEBILL_DOCUMENT_ROOT . '/apps/data/admin/admin.php');

class data_update extends data_admin {

    function main($secret_key = '') {

	$DBC = DBC::getInstance();
    $query_data[] = "ALTER TABLE " . DB_PREFIX . "_data ADD column `land_area` int(10) not null default 0";
    
	$query_data[] = "CREATE TABLE `" . DB_PREFIX . "_data_note` (
  `data_note_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `user_id` int(11) not null default 0,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `message` text,
  PRIMARY KEY (`data_note_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";

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
