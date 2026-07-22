<?php
/**
 * DataCountTrait — Count and statistics methods for Data_Manager.
 *
 * Extracted from data_manager.php during refactoring.
 * Methods: get_count(), getDataStatInfo()
 */
trait DataCountTrait
{
    /**
     * Get count
     */
    function get_count($active) {
        return "#count_$active#";
        $DBC = DBC::getInstance();
        if ($active === 'vip') {
            $query = 'SELECT COUNT(id) AS total FROM ' . DB_PREFIX . '_data WHERE vip_status_end<>0 AND ' . DB_PREFIX . '_data.vip_status_end >= \'' . time() . '\'';
        } elseif ($active === 'premium') {
            $query = 'SELECT COUNT(id) AS total FROM ' . DB_PREFIX . '_data WHERE premium_status_end<>0 AND ' . DB_PREFIX . '_data.premium_status_end >= \'' . time() . '\'';
        } elseif ($active === 'bold') {
            $query = 'SELECT COUNT(id) AS total FROM ' . DB_PREFIX . '_data WHERE bold_status_end<>0 AND ' . DB_PREFIX . '_data.bold_status_end >= \'' . time() . '\'';
        } elseif ($active === 'all') {
            $query = "select count(id) as total from " . DB_PREFIX . "_data";
        } elseif ($active === 'notactive') {
            if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting')) {
                $query = "select count(id) as total from " . DB_PREFIX . "_data where active=0 AND archived=0";
            }else{
                $query = "select count(id) as total from " . DB_PREFIX . "_data where active=0";
            }
        } elseif ($active === 'hot') {
            $query = "select count(id) as total from " . DB_PREFIX . "_data where hot=1";
        } elseif ($active === 'free') {
            $query = "select count(id) as total from " . DB_PREFIX . "_data where status_id='free'";
        } elseif ($active === 'no_answer') {
            $query = "select count(id) as total from " . DB_PREFIX . "_data where status_id='no_answer'";
        } elseif ($active === 'call') {
            $query = "select count(id) as total from " . DB_PREFIX . "_data where status_id='call'";
        } elseif ($active === 'actual') {
            $query = "select count(id) as total from " . DB_PREFIX . "_data where status_id='actual'";
        } elseif ($active === 'archived') {
            $query = "select count(id) as total from " . DB_PREFIX . "_data where archived=1";
        } else {
            if (1 === (int) $this->getConfigValue('apps.realty.use_predeleting')) {
                $query = "select count(id) as total from " . DB_PREFIX . "_data where active=1 AND archived=0";
            }else{
                $query = "select count(id) as total from " . DB_PREFIX . "_data where active=1";
            }

        }

        $result = $this->get_query_cache_value($query, array());
        if ( $result['result'] === true ) {
            return $result['value'];
        }

        $stmt = $DBC->query($query);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $this->insert_query_cache_value($query, array(), $ar['total']);
            return $ar['total'];
        }
        return 0;
    }

    public function getDataStatInfo($params = array()) {
        return array('status' => [], 'active' => [], 'total' => 0);

        //@todo: Очень жесткие запросы при большом количестве записей
        $statuses = array();
        $activities = array();


        $DBC = DBC::getInstance();

        $query = 'SELECT active, COUNT(id) AS _cnt FROM ' . DB_PREFIX . '_data GROUP BY active';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $activities[$ar['active']] = $ar['_cnt'];
            }
        }

        if (!empty($params)) {
            foreach ($params as $f) {
                $query = 'SELECT `' . $f . '`, COUNT(id) AS _cnt FROM ' . DB_PREFIX . '_data GROUP BY `' . $f . '`';
                $stmt = $DBC->query($query);
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        $statuses[$f][$ar[$f]] = $ar['_cnt'];
                    }
                }
            }
        }
        return array('status' => $statuses, 'active' => $activities, 'total' => array_sum($activities));
    }
}
