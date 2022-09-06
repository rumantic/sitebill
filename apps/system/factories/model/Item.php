<?php

namespace system\factories\model;

use system\types\model\Base;
use system\types\model\SelectByQuery;

class Item {
    public static function base( $type, $name, $title, $value = '', $required = 'off',  $unique = 'off', $length = '', $parameters = array()) {
        return [
            $name => [
                \system\types\model\Base::$type => $type,
                \system\types\model\Base::$name => $name,
                \system\types\model\Base::$title => $title,
                \system\types\model\Base::$value => $value,
                \system\types\model\Base::$required => $required,
                \system\types\model\Base::$unique => $unique,
                \system\types\model\Base::$length => $length,
                \system\types\model\Base::$parameters => $parameters,
            ]
        ];
    }
    public static function select_by_query(
        $name,
        $title,
        $primary_key_name,
        $primary_key_table,
        $query,
        $value_name,

        //optional
        $title_default = '',
        $value_default = '',
        $value = '',
        $required = 'off',
        $unique = 'off',
        $length = '',
        $parameters = array()
    ) {
        $type = \system\types\model\Dictionary::SELECT_BY_QUERY;
        $base = self::base($type, $name, $title, $value, $required,  $unique, $length, $parameters);

        $base[$name][\system\types\model\SelectByQuery::$primary_key_name] = $primary_key_name;
        $base[$name][\system\types\model\SelectByQuery::$primary_key_table] = $primary_key_table;
        $base[$name][\system\types\model\SelectByQuery::$query] = $query;
        $base[$name][\system\types\model\SelectByQuery::$value_name] = $value_name;
        $base[$name][\system\types\model\SelectByQuery::$title_default] = $title_default;
        $base[$name][\system\types\model\SelectByQuery::$value_default] = $value_default;
        return $base;
    }

}
