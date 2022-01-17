<?php

/**
 * Данный скрипт необходимо поместить в крон и запускать с определенным периодом.
 * В зависимости от мощности вашего сервера, можно поставить период запуска от 3 минут, до часа
 * Подробнее о настройках скрипта парсинга картинок тут http://wiki.sitebill.ru/index.php?title=Excel
 */
error_reporting(E_ERROR | E_WARNING);
ini_set('display_errors', 'On');
session_start();
require_once($_SERVER['DOCUMENT_ROOT']."/apps/system/starter.php");
if (!defined('SITE_ENCODING')) {
    define('SITE_ENCODING', 'windows-1251');
}
header('Content-Type: text/html; charset=' . SITE_ENCODING);
require_once(SITEBILL_DOCUMENT_ROOT . '/third/smarty/Smarty.class.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/db/MySQL.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/uploadify/uploadify.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/multilanguage/multilanguage.class.php');

$smarty = new Smarty;
$smarty->cache_dir = SITEBILL_DOCUMENT_ROOT . '/cache/smarty';
$smarty->compile_dir = SITEBILL_DOCUMENT_ROOT . '/cache/compile';

Sitebill::setLangSession();
$sitebill = new SiteBill();

Multilanguage::start('backend', $_SESSION['_lang']);

require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/data/data_manager.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/dadata/lib/dadata.php');
require_once(SITEBILL_DOCUMENT_ROOT . '/apps/dadata/lib/cleaner.php');
$dadata_cleaner = new Dadata_Cleaner();
//$dadata_cleaner->clean_one('Тюмень, Мира, 10');
$dadata_cleaner->clean();

echo 'Завершено<br>';

