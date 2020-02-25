<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * Created by PhpStorm.
 * User: unclead.nsk
 * Date: 06.04.14
 * Time: 15:58
 */

class Helper {

    public static $timer = null;

    public static function isUnicode($string) {
        return preg_match('//u', $string);
    }

    public static function toLowerCase($string)
    {
        return mb_strtolower($string, 'UTF-8');
    }

    public static function resetTimer($time = null) {
        self::$timer = empty($time) ? microtime(true) : $time;
    }

    public static function timer($returnValue = false) {
        $time_start = self::$timer;
        $time_end = microtime(true);
        self::resetTimer($time_end);

        $time = round(($time_end-$time_start), 3);
        if($returnValue) {
            return $time;
        } else {
            echo 'Время выполнения' . round(($time_end-$time_start), 3) .' сек.<br/>' ;
        }
    }

    public static function dump($value) {
        echo '<pre>';
        print_r($value);
        echo '</pre><hr/>';
    }
} 