<?php

error_reporting (E_ALL);

include('kcaptcha.php');


$settings=parse_ini_file('../../settings.ini.php',true);
if(isset($settings['Settings']['estate_folder'])AND($settings['Settings']['estate_folder']!='')){
	$folder='/'.$settings['Settings']['estate_folder'];
}else{
	$folder='';
}
$sitebill_document_root = $_SERVER['DOCUMENT_ROOT'].$folder;

define('SITEBILL_DOCUMENT_ROOT', $sitebill_document_root);

require_once SITEBILL_DOCUMENT_ROOT."/inc/db.inc.php";
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/debugger.class.php';
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/logger.class.php';
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/dbc.php';
session_start();

$captcha = new KCAPTCHA();
if(!defined('DB_PREFIX')){
	define('DB_PREFIX', $__db_prefix);
}
if(!defined('DB_DSN')){
	define('DB_DSN','mysql:host='.$__server.';dbname='.$__db);
}
if(!defined('DB_ENCODING')){
	define('DB_ENCODING','cp1251');
}
if(!defined('SITE_ENCODING')){
	define('SITE_ENCODING','windows-1251');
}
if(!defined('DB_USER')){
	define('DB_USER',$__user);
}
if(!defined('DB_PASS')){
	define('DB_PASS',$__password);
}

$captcha_session_key = $_REQUEST['captcha_session_key'];
$_SESSION[$captcha_session_key] = $captcha->getKeyString();
/*
if($_REQUEST[session_name()]){
	$_SESSION['captcha_keystring'] = $captcha->getKeyString();
}*/

$DBC=DBC::getInstance();
$query = 'INSERT INTO '.DB_PREFIX.'_captcha_session (captcha_session_key, captcha_string) VALUES (?, ?)';

$stmt=$DBC->query($query, array($captcha_session_key, $captcha->getKeyString()));