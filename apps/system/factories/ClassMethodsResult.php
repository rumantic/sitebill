<?php
namespace system\factories;

abstract class ClassMethodsResult
{
    function getMethodsResult ( $key ) {
        $result = array();
        $methods = get_class_methods($this);
        foreach ($methods as $item) {
            if ( str_contains($item, $key) ) {
                $result[] = $this->$item();
            }
        }
        return $result;
    }
}
