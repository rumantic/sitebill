<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
require_once (SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/admin/admin.php');

class memorylist_update extends memorylist_admin {

    function main($secret_key = '') {
	$DBC = DBC::getInstance();
	$query_data[] = "CREATE TABLE `" . DB_PREFIX . "_memorylist` (
  `memorylist_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `deal_id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`memorylist_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
	$query_data[] = "CREATE TABLE `" . DB_PREFIX . "_memorylist_item` (
  `memorylist_id` int(10) UNSIGNED NOT NULL,
  `id` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
	$query_data[] = "ALTER TABLE `" . DB_PREFIX . "_memorylist`  ADD COLUMN `domain` varchar(255);";
	$query_data[] = "ALTER TABLE `" . DB_PREFIX . "_memorylist`  ADD COLUMN `deal_id` int(11) not null default 0;";
	$query_data[] = "ALTER TABLE `" . DB_PREFIX . "_memorylist_item`  ADD UNIQUE KEY `memorylist_id` (`memorylist_id`,`id`);";

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
