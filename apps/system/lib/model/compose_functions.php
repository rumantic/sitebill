<?php
namespace system\lib\model;
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

class compose_functions {
    function def_link($model, $key){
        if(preg_match('/^http(s?):/', $model[$key]['value_string'])){
            $url = $model[$key]['value_string'];
            $maxlen = 25;
            if (strlen($url) > $maxlen) {
                $url = substr($url, 0, $maxlen).'...';
            }
            return '<a href="'.$model[$key]['value_string'].'" target="_blank">'.$url.'</a>';
        }
        return $model[$key]['value_string'];
    }

    function def_datetimeru($model, $key){
        if($model[$key]['type'] == 'dtdatetime'){
            return date('d.m.Y H:i', strtotime($model[$key]['value']));
        }
        return $model[$key]['value_string'];
    }
}