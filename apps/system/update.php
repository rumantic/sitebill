<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

class system_update extends SiteBill {

    /**
     * Construct
     */
    function __construct() {
	$this->sitebill();
	if (file_exists(SITEBILL_DOCUMENT_ROOT . '/inc/db.inc.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/install')) {
	    $msgs = array();

	    self::removeDirectory(SITEBILL_DOCUMENT_ROOT . '/install', $msgs);

	    if (count($msg) > 0) {
		foreach ($msgs as $msg) {
		    echo $msg . '<br/>';
		}
	    }
	}
    }

    public static function removeDirectory($dir, &$msg = array()) {
	$files = scandir($dir);

	if (count($files) > 2) {
	    foreach ($files as $file) {
		if ($file != '.' && $file != '..') {
		    if (is_dir($dir . '/' . $file)) {
			self::removeDirectory($dir . '/' . $file, $msg);
		    } elseif (is_writable($dir . '/' . $file)) {
			@unlink($dir . '/' . $file);
		    } else {
			$msg[] = 'Файл/директория ' . $file . ' не удален. Удалите его самостоятельно.';
		    }
		}
	    }
	}

	if (is_writable($dir)) {
	    rmdir($dir);
	} else {
	    $msg[] = 'Файл/директория ' . $dir . ' не удален. Удалите его самостоятельно.';
	}
    }

    function main($secret_key = '') {

	$DBC = DBC::getInstance();


	$column_info = array();
	$query = 'SELECT * FROM ' . DB_PREFIX . '_columns WHERE name=? AND table_id=(SELECT table_id FROM ' . DB_PREFIX . '_table WHERE name=? LIMIT 1)';
	$stmt = $DBC->query($query, array('user_id', 'data'));
	if ($stmt) {
	    $column_info = $DBC->fetch($stmt);
	}

	if (!empty($column_info) && $column_info['type'] != 'select_by_query') {
	    $query = 'UPDATE ' . DB_PREFIX . '_columns SET `type`=?, `primary_key_name`=?, `primary_key_table`=?, `value_name`=?, `value`=?, `query`=? WHERE columns_id=?';
	    $params = array();
	    $params[] = 'select_by_query';
	    $params[] = 'user_id';
	    $params[] = 'user';
	    $params[] = 'fio';
	    $params[] = '0';
	    $params[] = 'SELECT * FROM ' . DB_PREFIX . '_user';
	    $params[] = $column_info['columns_id'];

	    $stmt = $DBC->query($query, $params);
	}


	$columns = array();
	$query = 'SELECT * FROM ' . DB_PREFIX . '_columns WHERE name IN (\'country_id\', \'region_id\', \'city_id\', \'street_id\', \'district_id\', \'street_id\', \'metro_id\') AND type=?';
	$stmt = $DBC->query($query, array('select_by_query'));
	if ($stmt) {
	    while ($ar = $DBC->fetch($stmt)) {
		$columns[] = $ar;
	    }
	}

	if (!empty($columns)) {
	    $query = 'UPDATE ' . DB_PREFIX . '_columns SET `primary_key_table`=? WHERE columns_id=?';
	    foreach ($columns as $column) {
		if ($column['name'] == 'country_id' && $column['primary_key_table'] == '') {
		    $stmt = $DBC->query($query, array('country', $column['columns_id']));
		} elseif ($column['name'] == 'region_id' && $column['primary_key_table'] == '') {
		    $stmt = $DBC->query($query, array('region', $column['columns_id']));
		} elseif ($column['name'] == 'city_id' && $column['primary_key_table'] == '') {
		    $stmt = $DBC->query($query, array('city', $column['columns_id']));
		} elseif ($column['name'] == 'street_id' && $column['primary_key_table'] == '') {
		    $stmt = $DBC->query($query, array('street', $column['columns_id']));
		} elseif ($column['name'] == 'district_id' && $column['primary_key_table'] == '') {
		    $stmt = $DBC->query($query, array('district', $column['columns_id']));
		} elseif ($column['name'] == 'metro_id' && $column['primary_key_table'] == '') {
		    $stmt = $DBC->query($query, array('metro', $column['columns_id']));
		}
	    }
	}

	/* Установка ссылки на имя содержащей модели для элемента uploads */
	$columns = array();
	$query = 'SELECT c.columns_id, c.table_name, t.name AS parent_tablename FROM ' . DB_PREFIX . '_columns c LEFT JOIN ' . DB_PREFIX . '_table t USING(table_id) WHERE c.`type`=? AND c.`table_name`=\'\'';
	$stmt = $DBC->query($query, array('uploads'));
	if ($stmt) {
	    while ($ar = $DBC->fetch($stmt)) {
		$columns[] = $ar;
	    }
	}

	if (!empty($columns)) {
	    $query = 'UPDATE ' . DB_PREFIX . '_columns SET `table_name`=? WHERE columns_id=?';
	    foreach ($columns as $column) {
		$stmt = $DBC->query($query, array($column['parent_tablename'], $column['columns_id']));
	    }
	}

	$columns = array();

	//удаление неиспользуемой колонки street
	$query = 'SELECT * FROM ' . DB_PREFIX . '_columns WHERE name=? AND table_id=(SELECT table_id FROM ' . DB_PREFIX . '_table WHERE name=? LIMIT 1)';
	$stmt = $DBC->query($query, array('street', 'data'));
	if (!$stmt) {
	    $query = 'ALTER TABLE `re_data` DROP `street`';
	    $stmt = $DBC->query($query);
	}




	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_topic ADD column name_en varchar(255)";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_data ADD column meta_title text";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_data ADD column meta_keywords text";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_data ADD column meta_description text";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_data ADD column geo_lat decimal(9,6) DEFAULT NULL";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_data ADD column geo_lng decimal(9,6) DEFAULT NULL";

	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_topic ADD column meta_title text";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_topic ADD column meta_keywords text";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_topic ADD column meta_description text";


	$query_data[] = "DROP INDEX dna_key_idx ON " . DB_PREFIX . "_dna";
	$query_data[] = "CREATE UNIQUE INDEX dna_key_idx ON " . DB_PREFIX . "_dna (group_id, component_id, function_id)";

	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_data ADD column premium_status_end int(11) not null default 0";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_data ADD column bold_status_end int(11) not null default 0";
	$query_data[] = "ALTER table " . DB_PREFIX . "_data ADD column vip_status_end int(11) not null default 0";

	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_country ADD column url varchar(255)";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_country ADD column description text";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_country ADD column meta_title text";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_country ADD column meta_description text";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_country ADD column meta_keywords text";

	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_uploadify ADD column element varchar(255) not null DEFAULT ''";
	$query_data[] = "ALTER TABLE  `" . DB_PREFIX . "_uploadify` CHANGE  `element`  `element` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";

	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_bill ADD column payment_sum varchar(255) NOT NULL";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_bill ADD column bdirect TINYINT NOT NULL";

	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_bill ADD column payment_sum_robokassa decimal(10,2) NOT NULL";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_bill ADD column payment_type varchar(100) NOT NULL";

	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_bill ADD column payment_params TEXT NOT NULL";

	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_component ADD column title varchar(255)";

	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_topic ADD column public_title varchar(255)";

	$query_data[] = "create unique index component_name_idx on " . DB_PREFIX . "_component (name)";
	$query_data[] = "create unique index function_name_idx on " . DB_PREFIX . "_function (name)";
	$query_data[] = "create unique index cf_idx on " . DB_PREFIX . "_component_function (component_id, function_id)";

	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_street ADD column city_id int(11) not null default 0";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_street ADD column district_id int(11) not null default 0";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_user ADD column notify int(11) not null default 0";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_topic ADD column `published` int(10) not null default 1";

	$query_data[] = "DELETE FROM " . DB_PREFIX . "_config WHERE `config_key`='apps.cache.enable'";

	$query_data[] = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_table_grids` (`action_code` varchar(255) NOT NULL, `grid_fields` text NOT NULL, UNIQUE KEY `action_code` (`action_code`) ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

	$query_data[] = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_userlists` (`user_id` int(10) unsigned NOT NULL, `id` int(10) unsigned NOT NULL, `lcode` varchar(5) NOT NULL, UNIQUE KEY `user_id` (`lcode`,`user_id`,`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	$query_data[] = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_user_blocked_logins` (`login` varchar(255) NOT NULL, `blocked_to` datetime NOT NULL, `try_count` tinyint(4) NOT NULL DEFAULT '0', UNIQUE KEY `login` (`login`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_user ADD column `auth_hash` varchar(32)";
	$query_data[] = "ALTER TABLE " . DB_PREFIX . "_user ADD column `auth_salt` varchar(32)";

	$query_data[] = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_cache` (`parameter` varchar(200) NOT NULL, `value` mediumtext NOT NULL, `created_at` int(15) NOT NULL,  `valid_for` int(15) NOT NULL,  PRIMARY KEY (`parameter`)) ENGINE=MyISAM DEFAULT CHARSET=utf8";

	$DBC = DBC::getInstance();

	$query_upd_col = 'SELECT group_id FROM ' . DB_PREFIX . '_group WHERE system_name=?';
	$stmt = $DBC->query($query_upd_col, array('admin'));
	if ($stmt) {
	    $ar = $DBC->fetch($stmt);
	    $gid = (int) $ar['group_id'];
	} else {
	    $gid = 1;
	}

	$query_upd_col = 'SELECT columns_id FROM ' . DB_PREFIX . '_columns WHERE name=? AND table_id=(SELECT table_id FROM ' . DB_PREFIX . '_table WHERE name=? LIMIT 1)';
	$stmt = $DBC->query($query_upd_col, array('date_added', 'data'));
	if ($stmt) {
	    $ar = $DBC->fetch($stmt);
	    $cid = $ar['columns_id'];
	    $query_upd_col = 'UPDATE ' . DB_PREFIX . '_columns SET value=?, type=?, group_id=? WHERE columns_id=?';
	    $stmt = $DBC->query($query_upd_col, array('now', 'dtdatetime', $gid, $cid));
	}

	$media_docs_folder = SITEBILL_DOCUMENT_ROOT . '/img/mediadocs/';
	if (!file_exists($media_docs_folder)) {
	    mkdir($media_docs_folder);
	}
	if (!file_exists($media_docs_folder)) {
	    $rs .= 'Потытка создать директорию для присоединенных файлов не прошла. Создайте директорию /img/mediadocs/ самостоятельно.<br>';
	}


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
	$rs .= 'Обновление зависимых приложений<br>';
	$system_version = $this->get_app_version('system');
	$rs .= $this->get_dependency($system_version, $secret_key);
	$rs .= $this->update_language_structure();



	return $rs;
    }

    function update_language_structure() {
	$languages = Multilanguage::foreignLanguages();
	$DBC = DBC::getInstance();
	foreach ($languages as $language_id => $language_title) {
	    $query = "alter table " . DB_PREFIX . "_menu_structure add column name_" . $language_id . " varchar(255)";
	    $stmt = $DBC->query($query);

	    $query = "alter table " . DB_PREFIX . "_topic add column name_" . $language_id . " varchar(255)";
	    $stmt = $DBC->query($query);
	}
    }

    function get_dependency($version, $secret_key = '') {
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/sitebill/admin/admin.php');
    	$sitebill_admin = new sitebill_admin();
    	$rs .= $sitebill_admin->update_app('api', $secret_key);

	$rs .= 'Зависимые приложения обновлены<br>';
	return $rs;
    }

    function get_app_version($app_name) {
	if (!function_exists('file_get_html')) {
	    if (file_exists(SITEBILL_APPS_DIR . '/third/simple_html_dom/simple_html_dom.php')) {
		require_once SITEBILL_APPS_DIR . '/third/simple_html_dom/simple_html_dom.php';
	    } else {
		require_once SITEBILL_DOCUMENT_ROOT . '/third/simple_html_dom/simple_html_dom.php';
	    }
	}
	$apps_dir = SITEBILL_DOCUMENT_ROOT . '/apps';

	$version = false;

	if (is_file($apps_dir . '/' . $app_name . '/' . $app_name . '.xml')) {
	    //Parsing by simple_xml_dom
	    $xml = @file_get_html($apps_dir . '/' . $app_name . '/' . $app_name . '.xml');
	    if ($xml && is_object($xml)) {
		//$title=SiteBill::iconv('UTF-8', 'UTF-8', $xml->find('administration',0)->find('menu',0)->innertext());
		$action = (string) $xml->find('name', 0)->innertext();
		$version = (string) $xml->find('version', 0)->innertext();
	    }
	}
	return $version;
    }

}
