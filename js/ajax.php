<?php
/**
 * Ajax server module
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
error_reporting(0);

//error_reporting(E_ALL);
//ini_set('display_errors','On');

session_start();
$settings=parse_ini_file('../settings.ini.php',true);
if(isset($settings['Settings']['estate_folder'])AND($settings['Settings']['estate_folder']!='')){
$folder='/'.$settings['Settings']['estate_folder'];
}else{
$folder='';
}
$estate_folder = $folder;
global $home_url;
$home_url = '';
require_once("../inc/db.inc.php");
if(!defined('SITE_ENCODING')){
	define('SITE_ENCODING', 'windows-1251');
}
header('Content-Type: text/html; charset='.SITE_ENCODING);
$sitebill_document_root = $_SERVER['DOCUMENT_ROOT'].$folder;
define('SITEBILL_DOCUMENT_ROOT', $sitebill_document_root);
define('SITEBILL_MAIN_URL', $folder);
define('DB_PREFIX', $__db_prefix);

ini_set("include_path", $include_path );

require_once(SITEBILL_DOCUMENT_ROOT.'/third/smarty/Smarty.class.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/init.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/db/MySQL.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill_krascap.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/language/russian.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/ajax/ajax_server.php');

$smarty = new Smarty;

$init = new Init();
$init->initGlobals();
$ETOWN_LANG = new Etown_Lang;

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
$sitebill->writeLog('ajax');
$smarty->template_dir = SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme');
$smarty->cache_dir    = SITEBILL_DOCUMENT_ROOT.'/cache/smarty';
$smarty->compile_dir  = SITEBILL_DOCUMENT_ROOT.'/cache/compile';
$ajax_server = new Ajax_Server();
$rs = $ajax_server->main();
//$sitebill->writeLog($rs);
echo $rs;
?>