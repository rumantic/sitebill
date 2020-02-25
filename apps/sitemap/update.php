<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

class sitemap_update extends SiteBill {

    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
    }

    function main() {
        $rs = '';
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/sitemap.xml')) {
            $rs .= 'Обнаружен устревший файл sitemap.xml расположенный в корне сайта. Для корректной работы приложения удалите его. Наличие физического файла не является необходимым, а его имя служит точкой входа в приложение, которое генерирует содержимое файла динамически.';
        }
        return $rs;
    }

}
