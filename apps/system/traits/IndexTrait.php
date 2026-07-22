<?php
namespace system\traits;

trait IndexTrait {
    function indexByKey( $array, $key ) {
        $result = [];
        foreach ($array as $array_key => $item) {
            $result[$item[$key]['value']] = $item;
        }
        return $result;
    }
}
