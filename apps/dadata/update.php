<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
class dadata_update extends SiteBill {
    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
    }

    function main () {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/dadata/admin/admin.php');
        $dadata = new dadata_admin();
        $rs = 'Конфигурация обновлена<br>';
        return $rs;
    }
}
