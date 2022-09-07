<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
require_once (SITEBILL_DOCUMENT_ROOT . '/apps/getrent/admin/admin.php');

class getrent_update extends getrent_admin {

    function main($secret_key = '') {
		$rs = 'Обновление конфигурации';
		/* Добавление колонки created_at */
		$DBC=DBC::getInstance();
		$query = 'SELECT `table_id` FROM '.DB_PREFIX.'_table WHERE `name`=?';
		$stmt = $DBC->query($query, array('data_get_rent'));
		if($stmt){
			$ar=$DBC->fetch($stmt);
			$table_id=$ar['table_id'];
			
			$query = 'SELECT * FROM ' . DB_PREFIX . '_columns WHERE `name`=? AND `table_id`=?';
			$stmt = $DBC->query($query, array('created_at', $table_id));
			if (!$stmt) {
				$query = 'ALTER TABLE '.DB_PREFIX.'_data_get_rent ADD COLUMN created_at datetime NOT NULL';
				$stmt = $DBC->query($query);
				
				$query="INSERT INTO `re_columns` (`active`, `table_id`, `group_id`, `name`, `title`, `type`, `required`, `unique`, `hint`, `parameters`) VALUES (1, ".$table_id.", '', 'created_at', 'Дата создания', 'dtdatetime', 0, 0, '', 'a:0:{}');";
				$stmt = $DBC->query($query);
				
				$rs = 'Поле created_at добавлено в модель data_get_rent';
			}
		}
		return $rs;
    }

}
