<?php
date_default_timezone_set('Europe/Moscow');
//error_reporting(E_WARNING | E_ERROR);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
//error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

ini_set('gd.jpeg_ignore_warning', true);
//error_reporting(E_ALL);
ini_set('display_errors','On');
session_start();


if(!isset($_REQUEST['action']) || $_REQUEST['action']==''){
	$_REQUEST['action']='data';
}
if($_REQUEST['action']!=@$_SESSION['rem_action']){
	if(isset($_REQUEST['page'])){
		$_SESSION['rem_page']=$_REQUEST['page'];
	}else{
		$_SESSION['rem_page']=1;
	}
	$_SESSION['rem_action']=$_REQUEST['action'];
}else{
	if(isset($_REQUEST['page'])){
		$_SESSION['rem_page']=$_REQUEST['page'];
	}else{
		//$_SESSION['rem_page']=1;
	}
	//$_SESSION['rem_action']=$_REQUEST['action'];
}
$_POST['page']=$_SESSION['rem_page'];

//print_r($_REQUEST);


$settings=parse_ini_file('../settings.ini.php',true);
//echo $settings['Settings']['estate_folder'];
if(isset($settings['Settings']['estate_folder'])AND($settings['Settings']['estate_folder']!='')){
$folder='/'.$settings['Settings']['estate_folder'];
}else{
$folder='';
}

$sitebill_document_root = $_SERVER['DOCUMENT_ROOT'].$folder;
define('SITEBILL_DOCUMENT_ROOT', $sitebill_document_root);
define('SITEBILL_DOCUMENT_ROOT_ADMIN', $sitebill_document_root.'/admin');
define('SITEBILL_MAIN_URL', $folder);
define('SITEBILL_ADMIN_BASE', $folder.'/admin');
global $home_url;
$home_url = '';
include_once (SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/backend.php');
?>
