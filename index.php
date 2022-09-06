<?php

date_default_timezone_set('Europe/Moscow');
//error_reporting(E_ERROR);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
//error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
//error_reporting(E_ALL);
ini_set('display_errors', 'On');

if (!file_exists('./inc/db.inc.php')) {
    if (file_exists('./install/index.php')) {
        header('location:./install/');
    } else {
        echo 'CMS Sitebill не установлена, для установки необходим установщик в /install/. <a href="https://www.sitebill.ru/demo/">Скачать дистрибутив</a>';
    }
    exit();
}
session_start();

$settings = parse_ini_file('settings.ini.php', true);
if (isset($settings['Settings']['estate_folder'])AND ( $settings['Settings']['estate_folder'] != '')) {
    $folder = '/' . $settings['Settings']['estate_folder'];
} else {
    $folder = '';
}
$estate_folder = $folder;
global $home_url;
$home_url = '';
require_once("inc/db.inc.php");

$sitebill_document_root = $_SERVER['DOCUMENT_ROOT'] . $folder;
define('SITEBILL_DOCUMENT_ROOT', $sitebill_document_root);
define('SITEBILL_MAIN_URL', $folder);
define('DB_PREFIX', $__db_prefix);
// текущая валюта. функция переопределения текущей валюты должна переопределить эту константу и записать новое значение в сессию.
if (!defined('CURRENT_CURRENCY')) {
    if (isset($_SESSION['current_currency'])) {
        define('CURRENT_CURRENCY', $_SESSION['current_currency']);
    } else {
        define('CURRENT_CURRENCY', 1);
    }
}
if ( isset($include_path) ) {
    ini_set("include_path", $include_path);
}

require_once(SITEBILL_DOCUMENT_ROOT . '/third/smarty/Smarty.class.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/init.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/db/MySQL.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/language/russian.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/install/install.php');

if (file_exists(SITEBILL_DOCUMENT_ROOT . '/inc/db.inc.php') && file_exists(SITEBILL_DOCUMENT_ROOT . '/install')) {
    $msgs = array();
    Sitebill::removeDirectory(SITEBILL_DOCUMENT_ROOT . '/install', $msgs);
    if (count($msgs) > 0) {
        foreach ($msgs as $msg) {
            echo $msg . '<br/>';
        }
    }
    if ( file_exists(SITEBILL_DOCUMENT_ROOT . '/install') ) {
        echo 'Для продолжения работы удалите каталог install в корне сайта';
        exit;
    }
}
if ( file_exists(SITEBILL_DOCUMENT_ROOT . '/sitebill_setup.php') ) {
    unlink(SITEBILL_DOCUMENT_ROOT . '/sitebill_setup.php');
    if ( file_exists(SITEBILL_DOCUMENT_ROOT . '/sitebill_setup.php') ) {
        echo 'Для продолжения работы удалите файл sitebill_setup.php в корне сайта';
        exit;
    }
}

$smarty = new Smarty;

$init = new Init();
$init->initGlobals();
$ETOWN_LANG = new Etown_Lang;
$install_manager = new Install_Manager();
if (!$install_manager->main()) {
    echo $install_manager->GetErrorMessage();
    exit;
}

if (isset($_REQUEST['_lang'])) {
    $_SESSION['_lang'] = $_REQUEST['_lang'];
} else {
    if (!isset($_SESSION['_lang'])) {
        $_SESSION['_lang'] = 'ru';
    }
}


if(isset($_GET['dlang'])){

    $sitebill = new SiteBill();
    $RURI = $sitebill::getClearRequestURI();

    $prefix_list = array();
    $prefixlistconf = trim($sitebill->getConfigValue('apps.language.language_prefix_list'));
    if ($prefixlistconf !== '') {
        $prefix_pairs = explode('|', $prefixlistconf);
        if (count($prefix_pairs) > 0) {
            foreach ($prefix_pairs as $lp) {
                list($pr, $lo) = explode('=', $lp);
                $prefix_list[$pr] = $lo;
            }
        }
    }

    $locale = '';
    if(isset($prefix_list[$_SESSION['_lang']])){
        $locale = $prefix_list[$_SESSION['_lang']];
    }

    $sitebill->go301($sitebill->createUrlTpl($RURI, false, false, $locale));
    exit();
}

require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/multilanguage/multilanguage.class.php';

Sitebill::initRequest();
Multilanguage::start('frontend', $_SESSION['_lang']);

$sitebill = new SiteBill();
/*
if ($_GET['session_region_id'] != '') {
    $_SESSION['session_region_id'] = $_GET['session_region_id'];
    setcookie('session_region_id', $_GET['session_region_id'], time()+$sitebill->get_cookie_duration_in_sec(), '/', SiteBill::$_cookiedomain);
    $sitebill->setRequestValue('region_id', $_GET['session_region_id']);
} elseif ($_COOKIE['session_region_id'] != '') {
    $_SESSION['session_region_id'] = $_COOKIE['session_region_id'];
    $sitebill->setRequestValue('region_id', $_COOKIE['session_region_id']);
}
*/


//$sitebill->writeLog('test');
$smarty->template_dir = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $sitebill->getConfigValue('theme');
$smarty->cache_dir = SITEBILL_DOCUMENT_ROOT . '/cache/smarty';
$smarty->compile_dir = SITEBILL_DOCUMENT_ROOT . '/cache/compile';

$sitebill_krascap = new SiteBill_Krascap();

$sitebill_krascap->main();

$smarty->display("main.tpl");

exit;
?>
