<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * downloader admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class downloader_admin extends Object_Manager {

    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        $this->data_model[$this->table_name]['user_id']['value'] = $this->getSessionUserId();
    }

    public function _preload() {
        return false;
    }


    function ajax() {
        return false;
    }
}
