<?php
require_once(__DIR__."/../../inc/db.inc.php");
$settings=parse_ini_file(__DIR__.'/../../settings.ini.php', true);
if(isset($settings['Settings']['estate_folder']) && ($settings['Settings']['estate_folder']!='')){
	$folder='/'.$settings['Settings']['estate_folder'];
}else{
	$folder='';
}
$sitebill_document_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/').$folder;
define('SITEBILL_DOCUMENT_ROOT', $sitebill_document_root);
define('SITEBILL_MAIN_URL', $folder);
define('DB_PREFIX', $__db_prefix);