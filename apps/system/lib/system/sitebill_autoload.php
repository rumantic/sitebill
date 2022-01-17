<?php
spl_autoload_register(function ($className) {
    //$className = strtolower($className);
    //@todo: Нужно четко определить как не использовать strtolower
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    $document_root = $_SERVER['DOCUMENT_ROOT'];

    $file_name = $document_root . '/apps/' . $className . '.php';

    if ( $className == 'system\lib\model\data_model' ) {
        include_once $document_root . '/apps/system/lib/model/model.php';
        return;
    }
    if ( @file_exists($file_name) ) {
        include_once $file_name;
    } elseif ( @file_exists(strtolower($file_name)) ) {
        include_once strtolower($file_name);
    } else {
        $file_name = $document_root . '/apps/' . $className . '.class.php';
        if ( @file_exists($file_name) ) {
            include_once $file_name;
        } else {
            if ( preg_match('/api_/', $className) ) {
                $className = strtolower($className);
                $className = str_replace('api_', 'class.', $className);
                $file_name = $document_root . '/apps/' . $className . '.php';
                if ( @file_exists($file_name) ) {
                    include_once $file_name;
                }
            }
        }
    }

});

require_once SITEBILL_DOCUMENT_ROOT . '/apps/third/vendor/autoload.php';
