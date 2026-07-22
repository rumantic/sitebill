<?php
spl_autoload_register(function ($className) {
    //@todo: Нужно четко определить как не использовать strtolower
    $className = str_replace("\\", DIRECTORY_SEPARATOR, $className);
    $document_root = __DIR__.'/../../../../';
    if ( defined('SITEBILL_MAIN_URL') && !is_null(SITEBILL_MAIN_URL) ) {
        if('' !== SITEBILL_MAIN_URL){
            $document_root .= '../';
        }
        $document_root .= SITEBILL_MAIN_URL;
    }

    $file_name = $document_root . '/apps/' . $className . '.php';

    if ( $className == 'system\lib\model\data_model' ) {
        // Автолоад Data_Model класса
        include_once $document_root . '/apps/system/lib/model/model.php';
        return;
    }

    if ( @file_exists($file_name) ) {
        // Автолоад любого класса внутри папки приложений
        include_once $file_name;
    } elseif ( @file_exists(strtolower($file_name)) ) {
        // Автолоад любого класса внутри папки приложений
        include_once strtolower($file_name);
    }/* elseif ( preg_match("/^sitebill\/component/", $className) ) {
        $className = str_replace('sitebill\\component\\', '', $className);
        $file_name = $document_root . '/apps/system/lib/components/' . $className . '/' . $className . '.php';
        include_once $file_name;
        return;
    }*/ elseif ( preg_match("/^sitebill/", $className) ) {
        // Автолоад любого класса внутри папки приложений
        $className = str_replace('sitebill\\apps\\', '', $className);
        $file_name = $document_root . '/apps/' . $className . '.php';
        include_once $file_name;
        return;
        /*} elseif (preg_match("/^Theme/", $className)){
            // Автолоад из папки шаблона
            // Оценить стоит ли или ограничиться автолоадом из папки local, что бы не возить локализованные файлы по папкам шаблонов
            $className = str_replace('Theme\\', 'template/frontend/'.SConfig::getConfigValueStatic('theme').'/main/', $className);
            $file_name = $document_root . $className . '.php';
            include_once $file_name;
            return;*/
    } else {
        // Автолоад любого класса с постфиксом .class внутри папки приложений
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
if ( defined('BOOTSTRAP_LARAVEL') and BOOTSTRAP_LARAVEL ) {
    $l_root = str_replace('/packages/sitebill', '', SITEBILL_DOCUMENT_ROOT);
    require_once $l_root.'/vendor/autoload.php';
} elseif ( version_compare(PHP_VERSION, '8.1.0') >= 0 and file_exists('/usr/local/share/apps/third81/vendor/autoload.php')) {
    require_once '/usr/local/share/apps/third81/vendor/autoload.php';
} elseif ( version_compare(PHP_VERSION, '8.1.0') >= 0 and file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/third81/vendor/autoload.php')) {
    require_once SITEBILL_DOCUMENT_ROOT . '/apps/third81/vendor/autoload.php';
} else {
    require_once SITEBILL_DOCUMENT_ROOT . '/apps/third/vendor/autoload.php';
}

