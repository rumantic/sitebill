<?php
error_reporting(E_ERROR | E_WARNING);
ini_set('display_errors','On');

session_start();
require_once("../../../inc/db.inc.php");
if(!defined('SITE_ENCODING')){
	define('SITE_ENCODING', 'windows-1251');
}
header('Content-Type: text/html; charset='.SITE_ENCODING);
$settings=parse_ini_file('../../../settings.ini.php',true);
if(isset($settings['Settings']['estate_folder'])AND($settings['Settings']['estate_folder']!='')){
$folder='/'.$settings['Settings']['estate_folder'];
}else{
$folder='';
}
$sitebill_document_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/').$folder;

define('SITEBILL_DOCUMENT_ROOT', $sitebill_document_root);
define('SITEBILL_MAIN_URL', $folder);
define('DB_PREFIX', $__db_prefix);

ini_set("include_path", $include_path );
require_once(SITEBILL_DOCUMENT_ROOT.'/third/smarty/Smarty.class.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/init.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/db/MySQL.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/uploadify/uploadify.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/multilanguage/multilanguage.class.php');

$smarty = new Smarty;


Sitebill::setLangSession();
Multilanguage::start('backend',$_SESSION['_lang']);

$sitebill = new SiteBill();
$smarty->template_dir = SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme');
$smarty->cache_dir    = SITEBILL_DOCUMENT_ROOT.'/cache/smarty';
$smarty->compile_dir  = SITEBILL_DOCUMENT_ROOT.'/cache/compile';

require_once(SITEBILL_DOCUMENT_ROOT.'/apps/dashboard/admin/admin.php');
$local_dashboard_admin = SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme').'/apps/dashboard/admin/admin.php';
if (file_exists($local_dashboard_admin) ) {
    require_once($local_dashboard_admin);
    $dashboard_admin = new local_dashboard_admin();
} else {
    $dashboard_admin = new dashboard_admin();
}
$dashboard_admin->ajax();
?>