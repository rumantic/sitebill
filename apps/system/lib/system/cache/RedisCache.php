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
            if ( !defined('REDIS_SALT') && !defined('REDIS_PREFIX') ) {
                echo 'REDIS_SALT\REDIS_PREFIX not defined';
                exit;
            }
            self::$redis = new \Redis();
            self::$redis->connect((defined('REDIS_HOST') ? REDIS_HOST : '127.0.0.1'), (defined('REDIS_PORT') ? REDIS_PORT : 6379));
            if ( defined('REDIS_PASSWORD') and REDIS_PASSWORD != '' ) {
                self::$redis->auth(REDIS_PASSWORD);
            }
            if ( defined('REDIS_DBNR') && REDIS_DBNR != '' ) {
                self::$redis->select(REDIS_DBNR);
            }
            if ( defined('REDIS_PREFIX') && REDIS_PREFIX != '' ) {
                self::$redis->setOption(\Redis::OPT_PREFIX, REDIS_PREFIX.':');
            }
            
        }
    }

    public static function enabled () {
        if ( defined('REDIS_ENABLED') and REDIS_ENABLED ) {
            return true;
        }
        return false;
    }

    /**
     * Add salt to key name
     * @param string $key
     * @return string
     */
    private static function key_wrapper ($key) {
        return (REDIS_SALT != '' ? REDIS_SALT.'_' : '').$key;
    }

    /**
     * Remove prefix from key name
     * @param string $key
     * @return string
     */
    private static function remove_prefix($key){
        if(defined('REDIS_PREFIX') && REDIS_PREFIX != ''){
            return preg_replace('/^('.preg_quote(REDIS_PREFIX.':').')/', '', $key);
        }
        return $key;
    }

    /**
     * Remove salt from key name
     * @param string $key
     * @return string
     */
    private static function remove_key_wrapper ($key) {
        if(defined('REDIS_SALT') && REDIS_SALT != ''){
            return preg_replace('/^('.preg_quote(REDIS_SALT).'_)/', '', $key);
        }
        return $key;
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

    public static function set($key, $value, $timeout = null){
        if ( self::enabled() ) {
            self::connect();
            self::$redis->set(self::key_wrapper($key), $value, $timeout);
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

    /**
     * Remove key
     * @param $key
     * @param $ttl
     * @return bool|null
     */
    public static function del($key){
        if ( self::enabled() ) {
            self::connect();
            return self::$redis->del(self::key_wrapper($key));
        }
        return NULL;
    }

    /**
     * Return existing keys' names by pattern w\o salt and w\o prefix
     * @param string $pattern
     * @return array|null
     */
    public static function keys($pattern){
        if ( self::enabled() ) {
            self::connect();
            $keys = self::$redis->keys(self::key_wrapper($pattern));
            if(!empty($keys)){
                $keys = array_map(['self', 'remove_key_wrapper'], $keys);
                $keys = array_map(['self', 'remove_prefix'], $keys);
            }
            return $keys;
        }
        return NULL;
    }

}
