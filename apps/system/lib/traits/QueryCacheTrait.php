<?php
/**
 * QueryCacheTrait — extracted from SiteBill class (sitebill.php)
 * Auto-generated, do not edit manually.
 */
trait QueryCacheTrait
{
    function get_cache_hash($query, $params)
    {
        return md5($query . implode('', $params));
    }

    function get_query_cache_value($query, $params)
    {
        $result['result'] = false;
        if (!$this->getConfigValue('query_cache_enable')) {
            return $result;
        }
        $this->delete_query_cache();

        $DBC = DBC::getInstance();
        $md5_query_sum = $this->get_cache_hash($query, $params);

        $cache_query = "select `value` from " . DB_PREFIX . "_cache where parameter = ? and valid_for > ?";
        $stmt = $DBC->query($cache_query, array($md5_query_sum, time()));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $result['result'] = true;
            $result['value'] = $ar['value'];
        }
        return $result;
    }

    function insert_query_cache_value($query, $params, $value)
    {
        $DBC = DBC::getInstance();
        if ($this->getConfigValue('query_cache_enable')) {
            $md5_query_sum = $this->get_cache_hash($query, $params);
            $query_insert_cache = "insert into " . DB_PREFIX . "_cache (`parameter`, `value`, `created_at`, `valid_for`) values (?, ?, ?, ?)";
            $stmt = $DBC->query($query_insert_cache, array($md5_query_sum, $value, time(), time() + $this->getConfigValue('query_cache_time')));
        }

    }

    function delete_query_cache()
    {
        $DBC = DBC::getInstance();
        if ($this->getConfigValue('query_cache_enable')) {
            //Очищаем старые записи кэша
            $query_delete_cache = "delete from " . DB_PREFIX . "_cache where `created_at`<?";
            $stmt = $DBC->query($query_delete_cache, array(time() - $this->getConfigValue('query_cache_time')));
        }
    }

}
