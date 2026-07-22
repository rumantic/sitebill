<?php
/**
 * REST API
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
error_reporting(E_ERROR);
ini_set('display_errors', 'On');

$pre_request = json_decode(file_get_contents('php://input'), true);

if ( $pre_request['action'] == 'model' or $pre_request['action'] == 'oauth' ) {
    header('Content-Type: application/json; charset=utf-8');
}

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

// Admin template switching
$_admin_template_allowed = array('template1', 'tailwind');
$_admin_template = $sitebill->getConfigValue('apps.admin.template');
if ( !empty($_SESSION['admin_template']) && in_array($_SESSION['admin_template'], $_admin_template_allowed, true) ) {
    $_admin_template = $_SESSION['admin_template'];
}
if ( !in_array($_admin_template, $_admin_template_allowed, true) ) {
    $_admin_template = 'template1';
}
$smarty->template_dir = SITEBILL_DOCUMENT_ROOT . '/apps/admin/admin/' . $_admin_template;
// Keep server-side form/grid generators in sync with the selected admin template
if ( $_admin_template === 'tailwind' ) {
    $sitebill->setConfigValue('bootstrap_version', 'tailwind');
}
$smarty->cache_dir = SITEBILL_DOCUMENT_ROOT . '/cache/smarty';
$smarty->compile_dir = SITEBILL_DOCUMENT_ROOT . '/cache/compile';
$smarty->assign('SITEBILL_DOCUMENT_ROOT', SITEBILL_DOCUMENT_ROOT);

Sitebill::setLangSession();
Multilanguage::start('backend', $_SESSION['_lang']);
