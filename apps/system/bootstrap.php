<?php
date_default_timezone_set('Europe/Moscow');
//error_reporting(E_ERROR);
error_reporting(E_ERROR | E_WARNING);
//error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once ('starter.php');
ini_set("include_path", $include_path );
require_once(SITEBILL_DOCUMENT_ROOT.'/third/smarty/Smarty.class.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/init.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/db/MySQL.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/uploadify/uploadify.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/multilanguage/multilanguage.class.php');

$smarty = new Smarty;
$sitebill = new SiteBill();

$smarty->template_dir = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $sitebill->getConfigValue('theme');
$smarty->cache_dir    = SITEBILL_DOCUMENT_ROOT.'/cache/smarty';
$smarty->compile_dir  = SITEBILL_DOCUMENT_ROOT.'/cache/compile';
Sitebill::setLangSession();
Multilanguage::start('backend',$_SESSION['_lang']);
