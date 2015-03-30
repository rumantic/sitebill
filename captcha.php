<?php
/**
 * Captcha generator
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
//error_reporting(E_WARNING);
//echo 'test';
ini_set('display_errors','Off');
session_start();

$settings=parse_ini_file('settings.ini.php',true);
if(isset($settings['Settings']['estate_folder'])AND($settings['Settings']['estate_folder']!='')){
	$folder='/'.$settings['Settings']['estate_folder'];
}else{
	$folder='';
}
$sitebill_document_root = $_SERVER['DOCUMENT_ROOT'].$folder;
require("inc/db.inc.php");
define('SITEBILL_DOCUMENT_ROOT', $sitebill_document_root);
define('DB_PREFIX', $__db_prefix);

require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/db/MySQL.php');
global $__server, $__db, $__user, $__password, $sitebill_document_root;

$db = new DB( $__server, $__db, $__user, $__password );

$start_arr = random_captha();
$fon = random_captha();

$captha = imagecreate(180,80);//создаем картинку
$font = SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/font/1.ttf';
//echo $font;

// деламе шумы
imagecolorallocate($captha,255,255,255);
$a=0;// начальная позиция
for($i=0; $i < 3; $i++)//наносим код на картинку
{
    $color=imagecolorallocate($captha, 200, 200, 200);
    imagettftext($captha, 30, rand(-20, 20), rand(0,180),rand(0,80), $color, $font, $fon[rand(0,sizeof($fon))]);
}
// ~шумы сделаны
//imagecolorallocate($captha,255,255,255);
$a=0;// начальная позиция
for($i=0; $i < 6; $i++)//наносим код на картинку
{
  $color = imagecolorallocate($captha,0,0,0);
  $captcha_string .= $start_arr[$i];
  imagettftext($captha, 30, rand(-10,10), $a+=23,rand(40,60), $color, $font, $start_arr[$i]);
}
$captcha_session_key = $_REQUEST['captcha_session_key'];
$_SESSION[$captcha_session_key] = $captcha_string;
$quotes=get_magic_quotes_gpc();
if($quotes==1){
	$captcha_session_key=mysql_real_escape_string(stripcslashes($captcha_session_key));
}else{
	$captcha_session_key=mysql_real_escape_string($captcha_session_key);
}
$query = "insert into ".DB_PREFIX."_captcha_session (captcha_session_key, captcha_string) values ('".$captcha_session_key."', '".$captcha_string."')";
$db->exec($query);
// и добавляем ещё шума
for ($i = 0; $i < 100; $i++) 
{
    $x = rand(0,180);
    $y = rand(0,80);
    imagesetpixel($captha, $x, $y, 0);
}
// ~шумы добавлены!
header("Content-type: image/png");
imagepng($captha);//выводим капчу


// функция рандоминизирует капчу
function random_captha()
{
    $alphafit = array('1','2','3','4','5','6','7','8','9','0');
    for($i = 0; $i<6; $i++)
        $random_captha[$i] = $alphafit[rand(0,sizeof($alphafit)-1)];
    return $random_captha;
}
?>