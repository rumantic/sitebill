<?php
error_reporting(E_WARNING);
//error_reporting(E_ALL);
ini_set('display_errors','Off');

session_start();


if (!defined('__DIR__')) {
	define(__DIR__, dirname(__FILE__));
}
$minimal_php_version_text='5.3';  //Minimal reqiured PHP version
$minimal_gd_version_text='2';  //Minimal reqiured GD version
$db_inc_file=__DIR__.'/../inc/db.inc.php';  //db connections
$settings_file=__DIR__.'/../settings.ini.php';  //settings file
$wrong_license_key_message = "Введен неверный лицензионный ключ. Либо срок действия лицензии истек.";
function decode ( $key ) {
	$array = split("-", $key);
	$first = hexdec($array[0]);
	$second = hexdec($array[1]);
	$index = ($first+$second)/10000;
	if ( !in_array($index, array(1,2,3,4)) ) {
		return 0;
	}
	for ( $i = 2; $i < 5; $i++ ) {
		$sum += hexdec($array[$i]);
	}
	if ( $sum != hexdec($array[5]) ) {
		return 0;
	}
	return hexdec($array[$index]);
}
function check_license ( $license_key ) {
	$ins = decode($license_key);
	$d = time() - $ins;
	if ( $d > 86400*30 ) {
		return "wrong_license_key_message";
	}
	return "";
}

if(isset($_POST['step'])){
	$step=(int)$_POST['step'];
}else{
    if ( file_exists($db_inc_file) ) {
    	echo 'CMS Sitebill уже установлена, если вы хотите переустановить скрипт, то удалите файл ./inc/db.inc.php, очистите базу данных от старых таблиц и запустите установку еще раз';
    	exit;
    }
	$step=0;
}

ini_set("include_path", $include_path );

if(isset($_SESSION['steps'])){
	$steps=$_SESSION['steps'];
}else{
	$steps=array(
		'1'=>array('title'=>'Лицензионное соглашение','result'=>0,'id'=>'1'),
		'2'=>array('title'=>'Проверка системных требований','result'=>0,'id'=>'1'),
		'3'=>array('title'=>'Установка параметров соединения с БД','result'=>0,'id'=>'2'),
		'4'=>array('title'=>'Создание администратора БД','result'=>0,'id'=>'3'),
		'5'=>array('title'=>'Установка основных конфигурационных параметров','result'=>0,'id'=>'4')
	);
}
/*
$Installer=new Installer();
$Installer->run();
*/
if($step==0){
	$progressbar_value=0;
	rollbackSteps($steps, 0);
	$text.='<h1>Начало установки</h1>';
	$text.='<p><b>Благодарим за выбор CMS &laquo;Sitebill&raquo;</b></p>';
	$text .= '<p>Сейчас будет выполнена установка CMS &laquo;Sitebill&raquo; для управления сайтом. Конфигурация - Агентство недвижимости.</p>';
	$text.='<form method="post">';
	//$text .= '<table border="0">';
	//$text .= '<tr>';	
	//$text .= '<td>';	
	$text.='<input type="hidden" name="step" value="1" />';
	
	//$text .= '</td>';	
	//$text .= '</tr>';	
	//$text .= '</table>';
	$text.='<div class="controls"><input type="submit" name="submit" value="Далее" /></div>';
	$text.='</form>';
	
}elseif($step==1){
	if(isset($_POST['forward'])){
		if ( $_POST['iamagree']!='y' ) {
			$error_message = 'Для продолжения поставьте галочку "Я согласен с условиями"';
		} elseif ($error_message = check_license($_POST['license_key'])) {
		
		}
		
		if ( $error_message == 'wrong_license_key_message' ) {
			$error_message = $wrong_license_key_message;
		}
		
		if($error_message != '' ){
			$progressbar_value=0;
			$text.='<h1>Лицензионное соглашение</h1>';
			$text .= get_license_text();
			$text.=getBackButton(0);
			$text.='<form method="post">';
			
			$text .= get_input_license($error_message);
			$text.='Я согласен с условиями <input type="checkbox" name="iamagree" value="y" />';
			$text.='<input type="hidden" name="step" value="1" />';
			$text.='<div class="controls"><input type="submit" name="forward" value="Принять" /></div>';
			$text.='</form>';
			$text .= '<p align="center">Если возникли вопросы по установке, то посмотрите это видео</p><p align="center">
			<iframe width="560" height="315" src="http://www.youtube.com/embed/uYRALUk_luk" frameborder="0" allowfullscreen></iframe>
			</p>
			';
				
				
		}else{
			$_SESSION['license_key'] = $_REQUEST['license_key'];
			$progressbar_value=20;
			$steps[$step]['result']=1;
			$text.='<h1>Проверка системных требований</h1>';
			$text.='<p>Для работы системы необходимы:</p>';
			$text.='<p>РНР версии не младше '.$minimal_php_version_text.'</p>';
			$text.='<p>GD версии не младше '.$minimal_gd_version_text.'</p>';
			$text.='<p>Подключенные модули PDO, mbstring, iconv</p>';
			$text.='';
			
			$text.='<form method="post">';
			$text.='<input type="hidden" name="step" value="'.($step+1).'" />';
			$text.='<div class="controls"><input type="button" name="" value="Назад" onclick="javascript:SIF();"><input type="submit" name="forward" value="Далее" /></div>';
			$text.='</form>';
			$text.=getBackButton(1);
		}
	}else{
		$progressbar_value=0;
		rollbackSteps($steps, 0);
		$text.='<h1>Лицензионное соглашение</h1>';
		$text .= get_license_text();
		$text.=getBackButton(0);
		$text.='<form method="post">';

		$text .= get_input_license();
		$text.='Я согласен с условиями <input type="checkbox" name="iamagree" value="y" />';
		$text.='<input type="hidden" name="step" value="1" />';
		$text.='<div class="controls"><input type="submit" name="forward" value="Принять" /></div>';
		
		$text.='</form>';
		$text .= '<p align="center">Если возникли вопросы по установке, то посмотрите это видео</p><p align="center">
		<iframe width="560" height="315" src="http://www.youtube.com/embed/uYRALUk_luk" frameborder="0" allowfullscreen></iframe>
		</p>
		';
		
	}
	
	
}elseif($step==2){
	if(isset($_POST['forward'])){
		$php_check=checkPHPVersion($minimal_php_version_text);
		$gd_check=checkGDVersion($minimal_gd_version_text);
		$libxml_check=getLibXmlStatus();
		$catalog_errors=array();
		$libraries_errors=array();
		$catalog_check=check_catalogs_and_permissions($catalog_errors);
		$libraries_check=checkNeededLibraries($libraries_errors);
		$answer=array();
		$error='';
		$modules=get_loaded_extensions();
		if($php_check===FALSE){
			$answer[]='Версия PHP не ниже '.$minimal_php_version_text.' <span class="error">Ошибка</span>';
			//$error.='Ваша версия PHP '.PHP_VERSION.' устарела<br />';
			$error=1;
		}else{
			$answer[]='Версия PHP не ниже '.$minimal_php_version_text.' <span class="ok">OK</span>';
		}
		if($gd_check===FALSE){
			$answer[]='Версия библиотеки GD не ниже '.$minimal_gd_version_text.' <span class="error">Ошибка</span>';
			//$error.='Ваша версия PHP '.PHP_VERSION.' устарела<br />';
			$error=1;
			//$error.='Библиотека GD устарела либо отсутствует<br />';
		}else{
			$answer[]='Версия библиотеки GD не ниже '.$minimal_gd_version_text.' <span class="ok">OK</span>';
		}
		
		if(!in_array('PDO', $modules) || !in_array('pdo_mysql', $modules)){
			$answer[]='Необходимо наличие модуля PDO <span class="error">Ошибка</span>';
			$error=1;
		}else{
			$answer[]='Необходимо наличие модуля PDO <span class="ok">OK</span>';
		}
		
		if(!in_array('mbstring', $modules)){
			$answer[]='Необходимо наличие модуля mbstring <span class="error">Ошибка</span>';
			$error=1;
		}else{
			$answer[]='Необходимо наличие модуля mbstring <span class="ok">OK</span>';
		}
		
		if(!in_array('iconv', $modules)){
			$answer[]='Необходимо наличие модуля iconv <span class="error">Ошибка</span>';
			$error=1;
		}else{
			$answer[]='Необходимо наличие модуля iconv <span class="ok">OK</span>';
		}
		
		if($libxml_check===FALSE){
			$answer[]='Отключена поддержка библиотеки libxml <span class="error">Не критичная ошибка</span>';
			//$error=1;
		}else{
			$answer[]='Поддерживается библиотека libxml <span class="ok">OK</span>';
		}
		if($catalog_check===FALSE){
			$error=1;
		}
		if($libraries_check===FALSE){
			$error=1;
		}
		
		if(!empty($catalog_errors)){
			foreach($catalog_errors as $ce){
				$answer[]=$ce;
			}
		}
		if(!empty($libraries_errors)){
			foreach($libraries_errors as $ce){
				$answer[]=$ce;
			}
		}
		
		if($error==''){
			$progressbar_value=40;
			$steps[$step]['result']=1;
			$text.=getDBParametersForm(3);
			$text.=getBackButton(1);
		}else{
			$progressbar_value=20;
			$text.='<h1>Проверка системных требований</h1>';
			$text.=getResultMessage($answer);
/*
			$text.='<div class="controls">После исправления настроек нажмите <input type="button" name="" value="Повторить" onclick="javascript:SIF_Retry();"></div>';
			$text.=getRetryButton(2);
*/

			$text.='<form method="post">';
			$text.='<input type="hidden" name="step" value="2" />';
			$text.='<div class="controls">После исправления настроек нажмите <input type="submit" name="forward" value="Повторить" /></div>';
			$text.= '</form>';

		}
	}else{
		rollbackSteps($steps, 2);
		$php_check=checkPHPVersion($minimal_php_version_text);
		$gd_check=checkGDVersion($minimal_gd_version_text);
		$libxml_check=getLibXmlStatus();
		$catalog_errors=array();
		$libraries_errors=array();
		$catalog_check=check_catalogs_and_permissions($catalog_errors);
		$libraries_check=checkNeededLibraries($libraries_errors);
		$answer=array();
		$error='';
		if($php_check===FALSE){
			$answer[]='Версия PHP не ниже '.$minimal_php_version_text.' <span class="error">Ошибка</span>';
			//$error.='Ваша версия PHP '.PHP_VERSION.' устарела<br />';
			$error=1;
		}else{
			$answer[]='Версия PHP не ниже '.$minimal_php_version_text.' <span class="ok">OK</span>';
		}
		if($gd_check===FALSE){
			$answer[]='Версия библиотеки GD не ниже '.$minimal_gd_version_text.' <span class="error">Ошибка</span>';
			//$error.='Ваша версия PHP '.PHP_VERSION.' устарела<br />';
			$error=1;
			//$error.='Библиотека GD устарела либо отсутствует<br />';
		}else{
			$answer[]='Версия библиотеки GD не ниже '.$minimal_gd_version_text.' <span class="ok">OK</span>';
		}
		if($libxml_check===FALSE){
			$answer[]='Отключена поддержка библиотеки libxml <span class="error">Не критичная ошибка</span>';
			//$error=1;
		}else{
			$answer[]='Поддерживается библиотека libxml <span class="ok">OK</span>';
		}
		
		if($catalog_check===FALSE){
			$error=1;
		}
		if($libraries_check===FALSE){
			$error=1;
		}
		
		if(!empty($catalog_errors)){
			foreach($catalog_errors as $ce){
				$answer[]=$ce;
			}
		}
		if(!empty($libraries_errors)){
			foreach($libraries_errors as $ce){
				$answer[]=$ce;
			}
		}
		
		if($error==''){
			$progressbar_value=40;
			$steps[$step]['result']=1;
			$text.=getDBParametersForm(3,$_SESSION['db']);
			$text.=getBackButton(1);
		}else{
			$progressbar_value=20;
			$text.='<h1>Проверка системных требований</h1>';
			$text.=getResultMessage($answer);
			$text.='<div class="controls"><input type="button" name="" value="Повторить" onclick="javascript:SIF_Retry();"></div>';
			$text.=getRetryButton(2);
		}
	}
	
}elseif($step==3){
	if(isset($_POST['forward'])){
		$connection_status=FALSE;
		if(isset($_POST['db_user']) AND isset($_POST['db_pass']) AND isset($_POST['db_name']) AND isset($_POST['db_host'])){
			$db_user=checkParameter($_POST['db_user']);
			$db_pass=checkParameter($_POST['db_pass']);
			$db_name=checkParameter($_POST['db_name']);
			$db_host=checkParameter($_POST['db_host']);
				
			if($db_host==''){
				$db_host='localhost';
			}
			if(!empty($db_host) AND !empty($db_name) AND !empty($db_user)){
				$connection_status=checkConnection($db_host, $db_name, $db_user, $db_pass);
			}
		}
		if($connection_status){
			$progressbar_value=60;
			$steps[$step]['result']=1;
			$_SESSION['db']=array(
			'db_user'=>$db_user,
			'db_name'=>$db_name,
			'db_pass'=>$db_pass,
			'db_host'=>$db_host,
			);
			fillIncFile($db_inc_file);
			installTables();
			//$text.='Параметры валидны';
			$text.=getAdminCreateForm(4);
			$text.=getBackButton(2);
		}else{
			$text.=getErrorMessage('Ошибка в параметрах подключения к БД');
			$progressbar_value=40;
			$text.=getDBParametersForm(3);
			$text.=getBackButton(1);
		}
	}else{
		rollbackSteps($steps, 2);
		$progressbar_value=40;
		$text.=getDBParametersForm(3,$_SESSION['db']);
		$text.=getBackButton(2);
	}
	
}elseif($step==4){
	if(isset($_POST['forward'])){
		$admin_login=$_POST['admin_login'];
		$admin_pass=$_POST['admin_pass'];
		if($admin_login=='' OR $admin_pass==''){
			$progressbar_value=60;
			$text.=getAdminCreateForm(4);
			$text.=getBackButton(3);
		}else{
			
			checkConnection($_SESSION['db']['db_host'],$_SESSION['db']['db_name'],$_SESSION['db']['db_user'],$_SESSION['db']['db_pass']);
			$query="INSERT INTO re_user (login, password, pass, active, group_id, fio, reg_date) VALUES ('".$admin_login."','".md5($admin_pass)."','admin',1,1, 'Administrator', '".date('Y-m-d H:i:s').".')";
			$answer=mysql_query($query);
			if($answer){
				$progressbar_value=80;
				$steps[$step]['result']=1;
				$_SESSION['created_user']=$admin_login;
				$text.='<p>Администратор создан</p>';
				$text.='<p>Логин: '.$admin_login.'</p>';
				$text.='<p>Пароль: '.$admin_pass.'</p>';
				$text.=getSettingsForm(5);
				$text.=getBackButton(4);
			}else{
				$text.=getErrorMessage('Ошибка при создании пользователя - возможно пользователь уже создан, или вы пытаетесь установить скрипт на уже установленной базе. Для установки скрипта необходима чистая база без таблиц');
				$progressbar_value=60;
				$text.=getAdminCreateForm(4);
				$text.=getBackButton(2);
			}
		}
	}else{
		checkConnection($_SESSION['db']['db_host'],$_SESSION['db']['db_name'],$_SESSION['db']['db_user'],$_SESSION['db']['db_pass']);
		$query="DELETE FROM re_user WHERE login='".$_SESSION['created_user']."'";
		unset($_SESSION['created_user']);
		$answer=mysql_query($query);
		$progressbar_value=60;
		rollbackSteps($steps, 3);
		$text.='<p>Администратор удален из БД. Вам придется создать нового Администратора.</p>';
		$text.=getAdminCreateForm(4);
		$text.=getBackButton(3);
	}
		
}elseif($step==5){
	if ( isset($_POST['distrib_folder']) ) {
		$error_message = false;
		if ( $_POST['site_title'] == '' ) {
			$error_message = 'Не заполнено поле Заголовок сайта';
		}
		
		if ($_POST['order_email_acceptor'] == '' ) {
			$error_message = 'Неверно указан email';
		}
	}
	if(isset($_POST['distrib_folder']) and !$error_message){
		$progressbar_value=100;
		$steps[$step]['result']=1;
		$folder=trim(str_replace(array('/','\\'), '', $_POST['distrib_folder']));
		fillSettingsFile($settings_file, array('estate_folder'=>$folder));
		/////////////////////////////////////
		$settings=parse_ini_file(__DIR__.'/../settings.ini.php',true);
		if(isset($settings['Settings']['estate_folder'])AND($settings['Settings']['estate_folder']!='')){
			$folder='/'.$settings['Settings']['estate_folder'];
		}else{
			$folder='';
		}
	
		$sitebill_document_uri='';
		$sitebill_document_root = $_SERVER['DOCUMENT_ROOT'].$folder;
		define('SITEBILL_DOCUMENT_ROOT', $sitebill_document_root);
		//define('SITEBILL_MAIN_URL', $folder);
		require_once(__DIR__.'/../third/smarty/Smarty.class.php');
		require_once(__DIR__."/../inc/db.inc.php");
		define('DB_PREFIX', $__db_prefix);
		
		checkConnection($_SESSION['db']['db_host'],$_SESSION['db']['db_name'],$_SESSION['db']['db_user'],$_SESSION['db']['db_pass']);
		
		$query="INSERT INTO ".DB_PREFIX."_config (config_key, value, title) VALUES ('license_key','".$_SESSION['license_key']."','Лицензионный ключ')";
		$answer=mysql_query($query);
		if ( !$answer ) {
			echo 'Ошибка подключения к базе, повторите процесс установки снова '.mysql_error();
			exit;
		}
		
		
		
		require_once(__DIR__.'/../apps/system/lib/db/MySQL.php');
		require_once(__DIR__.'/../apps/system/lib/sitebill.php');
		require_once(__DIR__.'/../apps/system/lib/admin/object_manager.php');
		require_once(__DIR__.'/../apps/config/admin/admin.php');
		require_once(__DIR__.'/../apps/system/lib/system/install/install.php');
		
		$smarty = new Smarty;
		$sitebill = new SiteBill();
		
		$smarty->template_dir = SITEBILL_DOCUMENT_ROOT.'/template/frontend/agency';
		$smarty->cache_dir    = SITEBILL_DOCUMENT_ROOT.'/cache/smarty';
		$smarty->compile_dir  = SITEBILL_DOCUMENT_ROOT.'/cache/compile';
		$config_admin = new config_admin();
		$config_admin->check_config_structure();
		$config_admin->db->exec('update '.DB_PREFIX."_config set value='0' where config_key='show_cattree_left'");
		
		$install_manager = new Install_Manager();
		//insert license key
		$install_manager->install_default_data($folder);
		
		$query="update ".DB_PREFIX."_config set value = '".$_REQUEST['order_email_acceptor']."' where config_key='order_email_acceptor'";
		$install_manager->db->exec($query);
		if ( $install_manager->db->error ) {
			echo $install_manager->db->error;
		}
		
		$query="update ".DB_PREFIX."_user set email = '".$_REQUEST['order_email_acceptor']."' where login='admin'";
		$install_manager->db->exec($query);
		if ( $install_manager->db->error ) {
			echo $install_manager->db->error;
		}
		
		
		$query="update ".DB_PREFIX."_config set value = '".$_REQUEST['site_title']."' where config_key='site_title'";
		$install_manager->db->exec($query);
		if ( $install_manager->db->error ) {
			echo $install_manager->db->error;
		}
		
		//install apps.seo
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/seo/admin/admin.php');
		$seo_admin = new seo_admin();
		$query="update ".DB_PREFIX."_config set value = '1' where config_key='apps.seo.level_enable'";
		$install_manager->db->exec($query);
		if ( $install_manager->db->error ) {
			echo $install_manager->db->error;
		}
		$query="update ".DB_PREFIX."_config set value = '0' where config_key='apps.seo.html_prefix_enable'";
		$install_manager->db->exec($query);
		if ( $install_manager->db->error ) {
			echo $install_manager->db->error;
		}
		$seo_admin->update_structure();
		
		//install apps.geodata
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/geodata/admin/admin.php');
		$geodata_admin = new geodata_admin();
		$query="update ".DB_PREFIX."_config set value = '1' where config_key='apps.geodata.enable'";
		$install_manager->db->exec($query);
		if ( $install_manager->db->error ) {
			echo $install_manager->db->error;
		}
		
		//installl apps.mailbox
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/mailbox/admin/admin.php');
		$mailbox_admin = new mailbox_admin();
		$query="update ".DB_PREFIX."_config set value = '1' where config_key='apps.mailbox.enable'";
		$install_manager->db->exec($query);
		if ( $install_manager->db->error ) {
			echo $install_manager->db->error;
		}
		
		//install apps.getrent
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/getrent/admin/admin.php');
		$getrent_admin = new getrent_admin();
		$query="update ".DB_PREFIX."_config set value = '1' where config_key='apps.getrent.enable'";
		$install_manager->db->exec($query);
		if ( $install_manager->db->error ) {
			echo $install_manager->db->error;
		}
		
		/*
		 * Ups
		 * 
		 */
		
		///////////////////////////////////////
		$text.='<p>Поздравляем! Установка завершена. Не забудьте удалить папку install из корня сайта.</p>';
		$text.='<p><a href="'.$folder.'/">Перейти на сайт</a></p>';
		$text.='<p><a href="'.$folder.'/admin/">Перейти в панель управления</a></p>';
		$text.='<p>Чтобы быстрее освоиться с функциями движка зайдите на <a href="http://www.youtube.com/user/DMn1c" target="_blank">наш канал в youtube</a></p>';
		$_SESSION=array();
		unset($_SESSION);
	}else{
		$text.=getSettingsForm(5, $error_message);
	}
}else{
	$text.='Установка завершена. Не забудьте удалить папку install из корня сайта.';
}

$_SESSION['steps']=$steps;

function rollbackSteps(&$steps,$to){
	foreach($steps as $k=>&$s){
		if($k>$to){
			$s['result']=0;
		}
	}
	
}

function getBackButton($step){
	$text='';

	$text.='<form method="post" name="InternalForm">';

	$text.='<input type="hidden" name="step" value="'.$step.'" />';
	//$text.='<input type="submit" name="back" value="Назад" />';
	$text.='</form>';
	
	return $text;
}

function getRetryButton($step){
	$text='';
	$text.='<form method="post" name="RetryForm">';
	$text.='<input type="hidden" name="step" value="'.$step.'" />';
	$text.='</form>';
	return $text;
}



function get_input_license ($error_message = '') {
	$rs .= '<hr>';
	$rs .= '<table border="0" cellpadding="0" cellspacing="0" class="license">';
	if ( $error_message != '' ) {
		$rs .= '<tr>';
		$rs .= '<td>';
		$rs .= '<div class="error">'.$error_message.'</div>';
		$rs .= '</td>';
		$rs .= '</tr>';
	}
	$rs .= '<tr>';
	$rs .= '<td>';
	$rs .= '<div class="license_field">Лицензионный ключ: <input type="text" size="40" name="license_key" value="'.$_REQUEST['license_key'].'"> <a href="http://www.sitebill.ru/client/cart.php?a=add&pid=6" target="_blank">получить демо-ключ</a></div>';
	$rs .= '</td>';
	$rs .= '</tr>';
	
	$rs .= '<tr>';
	$rs .= '<td>';
	$rs .= '<p class="demo">Купить лицензионный ключ можно по этой <a href="http://www.sitebill.ru/price-cms-sitebill/" target="_blank">ссылке</a></p>';
	$rs .= '</td>';
	$rs .= '</tr>';
	
	$rs .= '</table>';
	$rs .= '<div class="clear"></div>';
	return $rs;
}

function get_license_text () {
	$rs .= '
<p>КЛЮЧ дает право использовать копию сайта на неограниченный период времени на любом количестве доменов и поддоменов. Также КЛЮЧ дает возможность использовать функции загрузки обновлений и обеспечивает доступ к интернет-магазину приложений на сайте <a href="http://www.sitebill.ru" target="_blank">http://www.sitebill.ru</a></p>
<p>Приобретенный КЛЮЧ не подлежит возврату, предполагается что вы ознакомились со всеми функциями сайта и делаете осознанную покупку.</p>
	';
	return $rs;	
	
}

function getErrorMessage($message){
	return '<p class="error">'.$message.'</p>';
}

function getResultMessage($message){
	$ret='';
	if(!empty($message)){
		foreach($message as $m){
			$ret.='<p>'.$m.'</p>';
		}
	}
	return $ret;
}

function getSettingsForm($step, $error_message = ''){
	$text='';
	$text.='<h1>Другие настройки</h1>';
	$text.='<form method="post">';
	$text.='<table>';
	$text.='<input type="hidden" name="step" value="'.$step.'" />';
	if ( $_SERVER['REQUEST_URI'] != '/install/' and  $_SERVER['REQUEST_URI'] != '/install/index.php' ) {
		$distr_folder = str_replace('index.php', '', $_SERVER['REQUEST_URI']);
		$distr_folder = str_replace('install', '', $distr_folder);
		$distr_folder = str_replace('/', '', $distr_folder);
	}
	if ( $_REQUEST['site_title'] == '' ) {
		$_REQUEST['site_title'] = 'Агентство недвижимости';
	}
	if ( $error_message != '' ) {
		$text.='<tr><td colspan="2"><span class="error">'.$error_message.'</span></td></tr>';
	}
	
	
	$text.='<tr><td>Заголовок сайта <span class="error">*</span></td><td><input type="text" size="40" name="site_title" value="'.$_REQUEST['site_title'].'" /></td></tr>';
	$text.='<tr><td>Email администратора (на этот адрес будут приходить заявки с сайта) <span class="error">*</span></td><td><input type="text"  size="40" name="order_email_acceptor" value="'.$_REQUEST['order_email_acceptor'].'" /></td></tr>';
	
	$text.='<tr><td>Имя папки установки (при установке не в корень сайта), если вы установили скрипт в корневой каталог, то оставьте это поле пустым. Если система сама поставила значение, то сверьте его со своим каталогом</td><td><input type="text"  size="40" name="distrib_folder" value="'.$distr_folder.'" /></td></tr>';
	$text.='</table>';
	$text.='<div class="controls"><input type="button" name="" value="Назад" onclick="javascript:SIF();"><input type="submit" name="submit" value="Далее" /></div>';
	
	$text.='</form>';
	return $text;
}

function getDBParametersForm($step,$params=array()){
	$text='';
	$text.='<h1>Параметры подключения к БД</h1>';
	$text.='<p>Укажите параметры подключения к БД для CMS &laquo;Sitebill&raquo;. База данных должна быть создана</p>';
	//$text.='<p>Если хостинг БД вашего провайдера отличается от localhost введите его в поле "Хост".</p>';
	$text.='<form method="post">';
	$text.='<table>';
	
	$text.='<input type="hidden" name="step" value="'.$step.'" />';
	$text.='<tr><td>Хост</td><td><input type="text" name="db_host" value="'.$params['db_host'].'" /></td></tr>';
	$text.='<tr><td>База</td><td><input type="text" name="db_name" value="'.$params['db_name'].'" /></td></tr>';
	$text.='<tr><td>Пользователь</td><td><input type="text" name="db_user" value="'.$params['db_user'].'" /></td></tr>';
	$text.='<tr><td>Пароль</td><td><input type="text" name="db_pass" value="'.$params['db_pass'].'" /></td></tr>';
	$text.='<tr><td></td><td></td></tr>';
	
	$text.='</table>';
	$text.='<div class="controls"><input type="button" name="" value="Назад" onclick="javascript:SIF();"><input type="submit" name="forward" value="Далее" /></div>';
	$text.='</form>';
	return $text;
}

function getAdminCreateForm($step){
	$text='';
	$text.='<h1>Создание Администратора</h1>';
	$text.='<p>Для администрирования системы вам необходимо создать первого пользователя - Администратора. Остальные пользователи будут созданы Администратором из панели управления или при регистрации на сайте</p>';
	$text.='<form method="post">';
	$text.='<table>';
	
	$text.='<input type="hidden" name="step" value="'.$step.'" />';
	$text.='<tr><td>Логин</td><td><input type="text" name="admin_login" value="admin" /></td></tr>';
	$text.='<tr><td>Пароль</td><td><input type="text" name="admin_pass" value="admin" /></td></tr>';
	//$text.='<tr><td><input type="button" name="" value="Назад" onclick="javascript:SIF();"></td><td><input type="submit" name="forward" value="Далее" /></td></tr>';
	
	$text.='</table>';
	$text.='<div class="controls"><input type="button" name="" value="Назад" onclick="javascript:SIF();"><input type="submit" name="forward" value="Далее" /></div>';
	$text.='</form>';
	return $text;
}

function check_catalogs_and_permissions (&$errors) {
        $error_folder_stack = array();
        $no_error = TRUE;
        $check_folders = array('/cache/compile', '/cache/upl', '/img/data', '/img/data/user', '/inc', '/settings.ini.php');
        
        $dir_name = dirname(__FILE__);
        
        foreach ( $check_folders as $folder ) {
            if ( !is_writable($dir_name.'/..'.$folder) ) {
                $error_folder_stack[] =  $folder.' <span class="error">нет прав на запись! (проверьте права доступа)</span>';
                $no_error = FALSE;
            }else{
            	$error_folder_stack[] =  $folder.' <span class="ok">доступен на запись</span>';
            }
        }
        $errors=$error_folder_stack;
        return $no_error;
    }

function checkParameter($p){
	return $p;
	//return preg_replace('/[^-A-Za-z_0-9\.]/', '', $p);
}

function checkConnection($db_host,$db_name,$db_user,$db_pass){
	$result=FALSE;
	$conn_id=FALSE;
	$db_sel=FALSE;
	
	$conn_id = @mysql_connect($db_host, $db_user, $db_pass);
	if($conn_id!==FALSE){
		$db_sel=@mysql_select_db($db_name, $conn_id);
		if($db_sel){
			$result=TRUE;
		}
	}
	return $result;
}

function getConnection($db_host,$db_name,$db_user,$db_pass){
	$result=FALSE;
	$conn_id=FALSE;
	$db_sel=FALSE;
	
	$conn_id = @mysql_connect($db_host, $db_user, $db_pass);
	if($conn_id!==FALSE){
		$db_sel=@mysql_select_db($db_name, $conn_id);
		if($db_sel){
			$result=TRUE;
		}
	}
	return $result;
}

function installTables(){
	$new_queries=array();
	$queries=file('estate.sql');
	$query='';
	foreach($queries as $q){
		if(0!==strpos($q, '#')){
			$query=$query.' '.$q;
		}
	}
	$new_queries=explode(';',$query);
	mysql_query('SET NAMES utf8');
	if(!empty($new_queries)){
		foreach($new_queries as $nq){
			mysql_query($nq);
		}
	}
}

function fillIncFile($inc_file){
	$text.='<?php'."\r\n";
	$text.='$__server = \''.$_SESSION['db']['db_host'].'\';'."\r\n";
	$text.='$__user = \''.$_SESSION['db']['db_user'].'\';'."\r\n";
	$text.='$__password = \''.$_SESSION['db']['db_pass'].'\';'."\r\n";
	$text.='$__db = \''.$_SESSION['db']['db_name'].'\';'."\r\n";
	$text.='$__db_prefix = \'re\'; // не менять'."\r\n";
	$text.='$__document_root = $_SERVER[\'DOCUMENT_ROOT\'];'."\r\n";
	$text.='define(\'SITE_ENCODING\', \'UTF-8\');'."\r\n";
	$text.='define(\'DB_ENCODING\', \'utf8\');'."\r\n";
	
	$text.='if (!isset($__connection))'."\r\n";
	$text.='{'."\r\n";
	$text.='	$__connection = mysql_connect($__server, $__user, $__password) '."\r\n";
	$text.='		or die(\'Не удалось поключиться к серверу БД (\' . mysql_error() . \')\');'."\r\n";
	$text.='	mysql_select_db($__db)'."\r\n";
	$text.='		or die (\'Не удалось подключиться к БД(\' . mysql_error() . \')\');'."\r\n";
	
	$text.='mysql_query("SET NAMES utf8");'."\r\n";
	
	$text.='}'."\r\n";
	$text.='?>';
	$f=fopen($inc_file,'w');
	fwrite($f,$text);
	fclose($f);
}

function fillSettingsFile($file,$settings=array()){
	$text='';
	$text.='[Settings]'."\r\n";
	if(!empty($settings)){
		foreach($settings as $k=>$s){
			$text.=$k.'='.$s."\r\n";
		}
	}
	$f=fopen($file,'w');
	fwrite($f,$text);
	fclose($f);
}


function checkNeededVersions($params=array()){
	if(empty($params)){
		return TRUE;
	}else{
		if(isset($params['php_need'])){
			
		}
	}
}

function checkPHPVersion($minimal_php_version_text){
	$a=explode('.',$minimal_php_version_text);
	$b=explode('.',PHP_VERSION);
	$compare_result=compareVersions($a, $b);
	if($compare_result<2){
		return TRUE;
	}
	return FALSE;
}

function checkGDVersion($minimal_gd_version_text){
	$current_gd_version=getGDVersion();
	if ( $current_gd_version < 2 ) {
		return false;
	}
	return true;
}

function checkNeededLibraries(&$errors){
	$error_folder_stack = array();
	$no_error = TRUE;
	$check_folders = array('/third/simple_html_dom/simple_html_dom.php');
        
	$dir_name = dirname(__FILE__);
        
	foreach ( $check_folders as $folder ) {
		if(!file_exists($dir_name.'/..'.$folder)){
			$error_folder_stack[] =  $folder.' <span class="error">Отсутствует!</span>';
			$no_error = FALSE;
		}else{
			$error_folder_stack[] =  $folder.' <span class="ok">Присутствует</span>';
		}
	}
	$errors=$error_folder_stack;
	return $no_error;
}


//return 1 - first argument younger
//return 2 - second argument younger
//return 0 - equals

function compareVersions($a,$b,$l=0){
	if(!isset($a[$l]) AND !isset($b[$l]) ){
		return 0;
	}else{
		if((int)$a[$l]==(int)$b[$l]){
			return compareVersions($a, $b, $l+1);
		}elseif((int)$a[$l]<(int)$b[$l]){
			return 1;
		}else{
			return 2;
		}
	}
}

function gdVersion($user_ver = 0)
{
	if (! extension_loaded('gd')) {
		return;
	}
	static $gd_ver = 0;
	// Just accept the specified setting if it's 1.
	if ($user_ver == 1) {
		$gd_ver = 1; return 1;
	}
	// Use the static variable if function was called previously.
	if ($user_ver !=2 && $gd_ver > 0 ) {
		return $gd_ver;
	}
	// Use the gd_info() function if possible.
	if (function_exists('gd_info')) {
		$ver_info = gd_info();
		preg_match('/\d/', $ver_info['GD Version'], $match);
		$gd_ver = $match[0];
		return $match[0];
	}
	// If phpinfo() is disabled use a specified / fail-safe choice...
	if (preg_match('/phpinfo/', ini_get('disable_functions'))) {
		if ($user_ver == 2) {
			$gd_ver = 2;
			return 2;
		} else {
			$gd_ver = 1;
			return 1;
		}
	}
	// ...otherwise use phpinfo().
	ob_start();
	phpinfo(8);
	$info = ob_get_contents();
	ob_end_clean();
	$info = stristr($info, 'gd version');
	preg_match('/\d/', $info, $match);
	$gd_ver = $match[0];
	return $match[0];
} // End gdVersion()

function getGDVersion() {
	return gdVersion();
}

function callCheckConfig(){
	
}

function getLibXmlStatus(){
	ob_start();
    phpinfo(8);
    $phpinfo=ob_get_contents();
    ob_end_clean();
    $phpinfo=stristr($phpinfo,"libXML support ");
    $phpinfo=stristr($phpinfo,"support");
    $end=strpos($phpinfo,"</tr>");
    if($end){
    	$phpinfo=substr($phpinfo,0,$end);
    }
    $phpinfo=strip_tags($phpinfo);
	if (preg_match("/.*(active).*/", $phpinfo, $r)) {
        $support=TRUE;
    }else{
    	$support=FALSE;
    }
    return $support;
}






?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>CMS &laquo;Sitebill&raquo; - Мастер установки</title>
    <script src="../js/jquery.js"></script>
	<link href="http://www.sitebill.ru/css/nano.css" rel="stylesheet" type="text/css" />
	<script src="http://www.sitebill.ru/js/nanoapi.js"></script>
	<script src="http://www.sitebill.ru/js/nanoapi_beta.js"></script>
    <link href="css/style.css" rel="stylesheet" type="text/css"/>
	<link href="../css/jquery-ui-1.8.custom.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="../js/jquery.ui.core.js"></script>   
    <script type="text/javascript" src="../js/jquery-ui-1.8.19.custom.min.js"></script>   

	<script type="text/javascript">
	$(document).ready(function() 
		    {
			$(function(){
		    	  $("#progressbar").progressbar({
		    		  value: <?php echo $progressbar_value; ?>
			    	  });
		    });
	});
	</script> 
	
	<script language="JavaScript">
	  function SIF () {
	    document.InternalForm.submit();
	  }

	  function SIF_Retry () {
	    document.RetryForm.submit();
	  }
	</script>
</head>
<body onload="runDialog('homescript_etown_ru');">
<div id="content">
<p>&nbsp;</p>
<p>&nbsp;</p>
<div id="esh1">
	<div class="corners">
		<div id="tl">
			<div id="tr">
				<div id="br">
					<div id="bl">
						<div class="header_corner">


<div id="page">
	<div id="title"><h1>Мастер установки CMS &laquo;Sitebill&raquo;</h1></div>
	<div id="progress_table">
		<?php 
			echo '<table>';
			foreach($steps as $s){
				echo '<tr>';
				echo '<td>'.$s['title'].'</td><td>'.($s['result']==1 ? '<img src="img/accepted.png" />' : '<img src="img/cancel.png" />').'</td>';
				echo '</tr>';
			}
			echo '</table>';
		?>
		<div id="progressbar"></div>
	</div>
	<div id="install_content">
	<?php
		echo $text;
	?>
	</div>
</div>

				        </div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>

</div>
</body>
</html>