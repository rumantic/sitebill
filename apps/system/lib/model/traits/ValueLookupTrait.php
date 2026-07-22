<?php
/**
 * ValueLookupTrait — extracted from Data_Model class (model.php)
 * Auto-generated, do not edit manually.
 */
use system\lib\system\cache\RedisCache;

trait ValueLookupTrait
{
    /**
     * Get string value by ID
     * @param string $primary_key_table
     * @param string $primary_key_name
     * @param string $value_name
     * @param string $primary_key_value
     * @param boolean $cache use cache
     * @return string
     */
    function get_string_value_by_id($primary_key_table, $primary_key_name, $value_name, $value, $cache = false)
    {
        if ($value == '' || $value == '0') {
            return '';
        }
        $DBC = DBC::getInstance();
        if ($cache) {
            $redis_key = 'dict_'.$primary_key_table.'.'.$value_name;
            $redis_cache = unserialize(RedisCache::get($redis_key));
            if ( is_array($redis_cache) and count($redis_cache) > 0 ) {
                return $redis_cache[$value];
            } elseif (!isset(self::$cache[$primary_key_table][$value][$value_name])) {
                //exit;
                $value_name = str_replace(' ', '', $value_name);
                $value_name = str_replace('`', '', $value_name);

                $primary_key_name = str_replace('`', '', $primary_key_name);
                $primary_key_name = str_replace(' ', '', $primary_key_name);

                $query = 'SELECT `' . $primary_key_name . '`, `' . $value_name . '` FROM ' . DB_PREFIX . '_' . $primary_key_table . '';

                $stmt = $DBC->query($query);
                if ($stmt) {
                    while ($ar = $DBC->fetch($stmt)) {
                        self::$cache[$primary_key_table][$value_name][$ar[$primary_key_name]] = $ar[$value_name];
                    }
                }
            }
            if ( is_array(self::$cache[$primary_key_table][$value_name]) and count(self::$cache[$primary_key_table][$value_name]) > 0 ) {
                RedisCache::set($redis_key, serialize(@self::$cache[$primary_key_table][$value_name]));
            }
            return @self::$cache[$primary_key_table][$value_name][$value];
        } else {
            $query = 'SELECT `'.$value_name.'` FROM ' . DB_PREFIX . '_' . $primary_key_table . ' WHERE `' . $primary_key_name . '` = ?';
            $stmt = $DBC->query($query, array($value));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                return $ar[$value_name];
            } else {
                return '';
            }
        }
    }

    /**
     * Get string values from outer table by ID
     * @param string $primary_key_table
     * @param string $primary_key_name
     * @param string $value_name
     * @param string $primary_key_value
     * @return string
     */
    function get_values_list($what, $primary_table_name, $primary_key_name, $secondary_table_name, $secondary_key_name, $value)
    {
        $ret = array();
        $query = 'SELECT ' . $what . ' FROM ' . $primary_table_name . ' WHERE ' . $primary_key_name . ' IN (SELECT ' . $primary_key_name . ' FROM ' . $secondary_table_name . ' WHERE ' . $secondary_key_name . '=?)';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($value));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ret[] = $ar[$what];
            }
        }
        if (count($ret) > 0) {
            return implode(', ', $ret);
        } else {
            return '';
        }
    }

    /**
     * Получить ID записи по значению одного из столбцов
     * @params $table - название таблицы
     * @params $filed - название столбца из таблицы, по которому ведем поиск
     * @params $primary_key - название ключа таблицы (ID)
     * @params $value - значение для поиска
     * @params $filters - необязательный параметр устанавливает фильтры для условия выборки
     * @return возвращаем ID записи или FALSE если запись не найдена
     */
    function get_value_id_by_name($table, $field, $primary_key, $value, $filters = array())
    {
        $query_params = array();
        $query_values = array();

        $query_params[] = '`' . $field . '`=?';
        $query_values[] = $value;

        if (!empty($filters)) {
            foreach ($filters as $k => $op) {
                $query_params[] = '`' . $k . '`=?';
                $query_values[] = $op;
            }
        }

        $DBC = DBC::getInstance();
        if ($this->getConfigValue('use_metaphone')) {
            $metaphone = $this->mtphn($value);
            $query = 'SELECT ' . $primary_key . ' FROM ' . DB_PREFIX . '_' . $table . ' WHERE ' . 'damlevlim(?,metaphone,20)<2 LIMIT 1';
            $query_values = array($metaphone);
            $this->writeLog(__METHOD__ . ' use_metaphone ' . "table = $table, field = $field, primary_key = $primary_key, value = $value, metaphone = $metaphone");
        } else {
            $query = 'SELECT ' . $primary_key . ' FROM ' . DB_PREFIX . '_' . $table . ' WHERE ' . implode(' AND ', $query_params);
        }

        $stmt = $DBC->query($query, $query_values);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar[$primary_key] != 0) {
                if ($this->getConfigValue('use_metaphone')) {
                    $this->writeLog(__METHOD__ . ' metaphone result = ' . $ar[$primary_key]);
                }
                return $ar[$primary_key];
            } else {
                return FALSE;
            }
        } else {
            //echo $DBC->getLastError();
            return FALSE;
        }
    }

    /**
     * Получить ID записи по значению одного из столбцов
     * @params $table - название таблицы
     * @params $primary_key - название ключа таблицы (ID)
     * @params $filters - необязательный параметр устанавливает фильтры для условия выборки
     * @return возвращаем ID записи или FALSE если запись не найдена
     */
    function get_key_value_by_filters($table, $primary_key, $filters = array())
    {
        $query_params = array();
        $query_values = array();


        if (!empty($filters)) {
            foreach ($filters as $k => $op) {
                $query_params[] = '`' . $k . '`=?';
                $query_values[] = $op;
            }
        }

        $DBC = DBC::getInstance();
        $query = 'SELECT ' . $primary_key . ' FROM ' . DB_PREFIX . '_' . $table . ' WHERE ' . implode(' AND ', $query_params);

        $stmt = $DBC->query($query, $query_values);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar[$primary_key] != 0) {
                return $ar[$primary_key];
            } else {
                return FALSE;
            }
        } else {
            //echo $DBC->getLastError();
            return FALSE;
        }
    }

}
