<?php
require_once(__DIR__."/../../inc/db.inc.php");
$settings=parse_ini_file(__DIR__.'/../../settings.ini.php', true);
if(isset($settings['Settings']['estate_folder']) && ($settings['Settings']['estate_folder']!='')){
	$folder='/'.$settings['Settings']['estate_folder'];
}else{
	$folder='';
}
if ( !empty($settings['Settings']['HTTP_HOST']) ) {
    $_SERVER['HTTP_HOST'] = $settings['Settings']['HTTP_HOST'];
}
$sitebill_document_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/').$folder;
if ( !defined('SITEBILL_DOCUMENT_ROOT') ) {
    define('SITEBILL_DOCUMENT_ROOT', $sitebill_document_root);
}
define('SITEBILL_MAIN_URL', $folder);
if ( isset($__db_prefix) and !defined('DB_PREFIX') ) {
    define('DB_PREFIX', $__db_prefix);
}
