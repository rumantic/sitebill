<?php
error_reporting(E_ERROR | E_WARNING);
ini_set('display_errors','On');

session_start();
require_once("../../system/starter.php");
if(!defined('SITE_ENCODING')){
	define('SITE_ENCODING', 'windows-1251');
}
header('Content-Type: text/html; charset='.SITE_ENCODING);

require_once(SITEBILL_DOCUMENT_ROOT.'/third/smarty/Smarty.class.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/db/MySQL.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/uploadify/uploadify.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/multilanguage/multilanguage.class.php');

$smarty = new Smarty;
$smarty->cache_dir    = SITEBILL_DOCUMENT_ROOT.'/cache/smarty';
$smarty->compile_dir  = SITEBILL_DOCUMENT_ROOT.'/cache/compile';
Sitebill::setLangSession();
Multilanguage::start('backend',$_SESSION['_lang']);

require_once(SITEBILL_DOCUMENT_ROOT.'/apps/articles/admin/admin.php');
$articles_admin = new articles_admin();
echo $articles_admin->ajax();


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

