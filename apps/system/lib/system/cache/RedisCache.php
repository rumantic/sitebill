<?php
namespace system\lib\system\cache;
/**
 * Redis cache
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class RedisCache
{

    /**
     * @var \Redis
     */
    public static $redis;


    private static function connect () {
        if ( !isset(self::$redis) ) {
            if ( !defined('REDIS_SALT') ) {
                echo 'REDIS_SALT not defined';
                exit;
            }
            self::$redis = new \Redis();
            self::$redis->connect('127.0.0.1', 6379);
            if ( defined('REDIS_PASSWORD') and REDIS_PASSWORD != '' ) {
                self::$redis->auth(REDIS_PASSWORD);
            }
        }
    }

    public static function enabled () {
        if ( defined('REDIS_ENABLED') and REDIS_ENABLED ) {
            return true;
        }
        return false;
    }

    private static function key_wrapper ($key) {
        return REDIS_SALT.'_'.$key;
    }

    /**
     * Return cache value
     * @param string $key key
     * @return mixed|NULL
     */
    public static function get($key){
        if ( self::enabled() ) {
            self::connect();
            $value = self::$redis->get(self::key_wrapper($key));
            if ( $value ) {
                return $value;
            }
        }
        return NULL;
    }

    public static function getArray($key){
        if ( self::enabled() ) {
            self::connect();
            $value = self::$redis->hGetAll(self::key_wrapper($key));
            if ( is_array($value) ) {
                return $value;
            }
        }
        return NULL;
    }

    public static function set($key, $value){
        if ( self::enabled() ) {
            self::connect();
            self::$redis->set(self::key_wrapper($key), $value);
            return true;
        }
        return NULL;
    }

    public static function setArray($key, $value){
        if ( self::enabled() ) {
            self::connect();
            self::$redis->hMSet(self::key_wrapper($key), $value);
            return true;
        }
        return NULL;
    }

}
