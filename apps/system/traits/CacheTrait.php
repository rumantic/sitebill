<?php
namespace system\traits;

trait CacheTrait
{
    function setCache ($key, $value, $lifetime) {
        if ( $this->getCacheValue($key) ) {
            return true;
        }
        $cache = new \system\lib\model\eloquent\Cache;
        $cache->parameter = $key;
        $cache->value =  $value;
        $cache->created_at = time();
        $cache->valid_for = time() + $lifetime;
        $cache->save();
        return true;
    }

    function getCacheValue ($key) {
        \system\lib\model\eloquent\Cache::where([
            ['parameter', '=', $key],
            ['valid_for', '<', time()]
        ])->delete();

        $exists_code = \system\lib\model\eloquent\Cache::where([
            ['parameter', '=', $key],
        ])->first();
        if ($exists_code) {
            return $exists_code->value;
        }
        return false;
    }
}

