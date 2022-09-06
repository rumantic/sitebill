<?php
date_default_timezone_set('Europe/Moscow');
//error_reporting(E_ERROR);
error_reporting(E_ERROR | E_WARNING);
//error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once ('starter.php');
if(isset($include_path)){
    ini_set("include_path", $include_path);
}
require_once(SITEBILL_DOCUMENT_ROOT.'/third/smarty/Smarty.class.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/init.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/db/MySQL.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/uploadify/uploadify.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/multilanguage/multilanguage.class.php');

$smarty = new Smarty;
$sitebill = new SiteBill();

if ( defined('BOOTSTRAP_LARAVEL') and BOOTSTRAP_LARAVEL ) {
    $smarty->assign('estate_folder', SITEBILL_MAIN_URL);
    $smarty->assign('estate_folder_control', SITEBILL_MAIN_URL.'/admin/');
    $smarty->assign('assets_folder', SITEBILL_MAIN_URL.'/apps/admin/admin/template1');
    $smarty->assign('SITEBILL_DOCUMENT_ROOT', SITEBILL_DOCUMENT_ROOT);
    $smarty->template_dir = SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template1';
    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/api/classes/class.common.php');
    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/api/classes/class.controller.php');
} else {
    $smarty->template_dir = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $sitebill->getConfigValue('theme');
}

$smarty->cache_dir    = SITEBILL_DOCUMENT_ROOT.'/cache/smarty';
$smarty->compile_dir  = SITEBILL_DOCUMENT_ROOT.'/cache/compile';
Sitebill::setLangSession();
Multilanguage::start('backend',$_SESSION['_lang']);
