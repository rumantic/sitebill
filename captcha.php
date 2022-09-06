<?php
/**
 * Captcha generator
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
error_reporting(E_WARNING);
ini_set('display_errors', 'Off');
session_start();

$settings = parse_ini_file('settings.ini.php', true);
if (isset($settings['Settings']['estate_folder']) and ($settings['Settings']['estate_folder'] != '')) {
    $folder = '/' . $settings['Settings']['estate_folder'];
} else {
    $folder = '';
}
$sitebill_document_root = $_SERVER['DOCUMENT_ROOT'] . $folder;
require("inc/db.inc.php");
if (!defined('DB_HOST')) {
    define('DB_HOST', $__server);
}
if (!defined('DB_PORT')) {
    define('DB_PORT', $__db_port);
}
if (!defined('DB_BASE')) {
    define('DB_BASE', $__db);
}
if (!defined('DB_DSN')) {
    if (defined(DB_PORT) && DB_PORT != '') {
        define('DB_DSN', 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_BASE);
    } else {
        define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_BASE);
    }
}
if (!defined('DB_ENCODING')) {
    define('DB_ENCODING', 'utf8');
}
if (!defined('DB_USER')) {
    define('DB_USER', $__user);
}
if (!defined('DB_PASS')) {
    define('DB_PASS', $__password);
}
define('SITEBILL_DOCUMENT_ROOT', $sitebill_document_root);
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/debugger.class.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/logger.class.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/dbc.php';

$start_arr = random_captha();
$fon = random_captha();

$captha = imagecreate(180, 80);//создаем картинку
$font = SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/font/1.ttf';

imagecolorallocate($captha, 255, 255, 255);
$a = 0;
for ($i = 0; $i < 3; $i++) {
    $color = imagecolorallocate($captha, 200, 200, 200);
    imagettftext($captha, 30, rand(-20, 20), rand(0, 180), rand(0, 80), $color, $font, $fon[rand(0, (sizeof($fon) - 1))]);
}

$a = 0;
$captcha_string = '';
for ($i = 0; $i < 6; $i++) {
    $color = imagecolorallocate($captha, 0, 0, 0);
    $captcha_string .= $start_arr[$i];
    imagettftext($captha, 30, rand(-10, 10), $a += 23, rand(40, 60), $color, $font, $start_arr[$i]);
}
$captcha_session_key = $_REQUEST['captcha_session_key'];
$_SESSION[$captcha_session_key] = $captcha_string;
$DBC = DBC::getInstance();
$query = "INSERT INTO " . DB_PREFIX . "_captcha_session (`captcha_session_key`, `captcha_string`) VALUES (?, ?)";
$DBC->query($query, array((string)$captcha_session_key, (string)$captcha_string));

for ($i = 0; $i < 100; $i++) {
    $x = rand(0, 180);
    $y = rand(0, 80);
    imagesetpixel($captha, $x, $y, 0);
}

header("Content-type: image/png");
imagepng($captha);

function random_captha()
{
    $alphafit = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
    for ($i = 0; $i < 6; $i++)
        $random_captha[$i] = $alphafit[rand(0, sizeof($alphafit) - 1)];
    return $random_captha;
}
