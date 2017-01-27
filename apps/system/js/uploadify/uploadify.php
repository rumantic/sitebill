<?php
error_reporting(E_ERROR | E_WARNING);
ini_set('display_errors','On');
session_start();
require_once("../../starter.php");

define('UPLOADIFY', true);

ini_set("include_path", $include_path );
require_once(SITEBILL_DOCUMENT_ROOT.'/third/smarty/Smarty.class.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/db/MySQL.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/uploadify/uploadify.php');
$smarty = new Smarty;
$sitebill = new SiteBill();
$sitebill->writeLog('uploadify module');
$uploadify = new Sitebill_Uploadify();
echo $uploadify->main( $_REQUEST['file'] );