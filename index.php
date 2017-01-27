<?php
date_default_timezone_set('Europe/Moscow');
error_reporting(E_ERROR | E_WARNING);
//error_reporting(E_ALL);
ini_set('display_errors','On');
//ini_set('date.timezone', 'Europe/Moscow');

if(!file_exists('./inc/db.inc.php')){
	if ( file_exists('./install/index.php') ) {
		header('location:./install/');
	} else {
		echo 'CMS Sitebill не установлена, для установки необходим установщик в /install/. <a href="https://www.sitebill.ru/demo/">Скачать дистрибутив</a>';
	}
	exit();
}
session_start();

$settings=parse_ini_file('settings.ini.php',true);
if(isset($settings['Settings']['estate_folder'])AND($settings['Settings']['estate_folder']!='')){
$folder='/'.$settings['Settings']['estate_folder'];
}else{
$folder='';
}
$estate_folder = $folder;
global $home_url;
$home_url = '';
require_once("inc/db.inc.php");

$sitebill_document_root = $_SERVER['DOCUMENT_ROOT'].$folder;
define('SITEBILL_DOCUMENT_ROOT', $sitebill_document_root);
define('SITEBILL_MAIN_URL', $folder);
define('DB_PREFIX', $__db_prefix);
// текущая валюта. функция переопределения текущей валюты должна переопределить эту константу и записать новое значение в сессию.
if(!defined('CURRENT_CURRENCY')){
	if(isset($_SESSION['current_currency'])){
		define('CURRENT_CURRENCY', $_SESSION['current_currency']);
	}else{
		define('CURRENT_CURRENCY', 1);
	}
}

ini_set("include_path", $include_path );

require_once(SITEBILL_DOCUMENT_ROOT.'/third/smarty/Smarty.class.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/init.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/db/MySQL.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill_krascap.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/language/russian.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/install/install.php');

if(file_exists(SITEBILL_DOCUMENT_ROOT.'/inc/db.inc.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/install')){
	$msgs=array();
	Sitebill::removeDirectory(SITEBILL_DOCUMENT_ROOT.'/install', $msgs);
	if(count($msg)>0){
		foreach($msgs as $msg){
			echo $msg.'<br/>';
		}
	}
}

$smarty = new Smarty;

$init = new Init();
$init->initGlobals();
$ETOWN_LANG = new Etown_Lang;
$install_manager = new Install_Manager();
if ( !$install_manager->main() ) {
    echo $install_manager->GetErrorMessage();
    exit;
}

if(isset($_REQUEST['_lang'])){
	$_SESSION['_lang']=$_REQUEST['_lang'];
}else{
	if(!isset($_SESSION['_lang'])){
		$_SESSION['_lang']='ru';
	}
}



require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/multilanguage/multilanguage.class.php';
Multilanguage::start('frontend',$_SESSION['_lang']);



$sitebill = new SiteBill();
//$sitebill->writeLog('test');
$smarty->template_dir = SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme');
$smarty->cache_dir    = SITEBILL_DOCUMENT_ROOT.'/cache/smarty';
$smarty->compile_dir  = SITEBILL_DOCUMENT_ROOT.'/cache/compile';

$sitebill_krascap = new SiteBill_Krascap();
$sitebill_krascap->main();

$smarty->display("main.tpl");

exit;    
?>