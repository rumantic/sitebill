<?php
namespace system\traits\statistics;

trait StatisticsTrait
{
    public static $new_stat_items = 'new_stat_items';
    public static $updated_stat_items = 'updated_stat_items';
    public static $rejected_stat_items = 'rejected_stat_items';
    public static $error_stat_items = 'error_stat_items';
    private $statistics_storage;

    public function increment_stat ( $type ) {
        $this->statistics_storage[$type]++;
    }

    public function get_stat ( $type ) {
        return $this->statistics_storage[$type];
    }

    public function get_all_stat () {
        return $this->statistics_storage;
    }

    public function add_stat_error ( $error ) {
        $this->statistics_storage[self::$error_stat_items][] = $error;
    }
}
