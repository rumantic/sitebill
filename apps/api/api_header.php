<?php
/**
 * REST API
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
error_reporting(E_ERROR);
ini_set('display_errors', 'On');

//header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
//header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
//cors();
//header('Access-Control-Allow-Origin: *');

session_start();
require_once("../../inc/db.inc.php");

$settings = parse_ini_file('../../settings.ini.php', true);
if (isset($settings['Settings']['estate_folder'])AND ( $settings['Settings']['estate_folder'] != '')) {
    $folder = '/' . $settings['Settings']['estate_folder'];
} else {
    $folder = '';
}
$sitebill_document_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $folder;

define('SITEBILL_DOCUMENT_ROOT', $sitebill_document_root);
define('SITEBILL_MAIN_URL', $folder);
define('API_MODE', true);
define('DB_PREFIX', $__db_prefix);
if ( isset($include_path) ) {
    ini_set("include_path", $include_path);
}
require_once(SITEBILL_DOCUMENT_ROOT . '/third/smarty/Smarty.class.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/init.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/db/MySQL.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/uploadify/uploadify.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/multilanguage/multilanguage.class.php');
$smarty = new Smarty;

$sitebill = new SiteBill();
//$sitebill->writeLog(__METHOD__.', '. var_export($_REQUEST, true));

$smarty->template_dir = SITEBILL_DOCUMENT_ROOT . '/apps/admin/admin/template1';
$smarty->cache_dir = SITEBILL_DOCUMENT_ROOT . '/cache/smarty';
$smarty->compile_dir = SITEBILL_DOCUMENT_ROOT . '/cache/compile';
$smarty->assign('SITEBILL_DOCUMENT_ROOT', SITEBILL_DOCUMENT_ROOT);

Sitebill::setLangSession();
Multilanguage::start('backend', $_SESSION['_lang']);
