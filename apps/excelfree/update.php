<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

class excelfree_update extends SiteBill {

    /**
     * Construct
     */
    function __construct() {
        parent::__construct();
    }

    function main() {
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/excelfree/lib/phpexcel/PHPExcel.php')) {
            $rs .= 'Удаление устаревшей библиотеки PHPExcel<br>';
            Sitebill::removeDirectory(SITEBILL_DOCUMENT_ROOT . '/apps/excelfree/lib', $msgs);
            if (count($msg) > 0) {
                foreach ($msgs as $msg) {
                    $rs .= $msg . '<br/>';
                }
            }
        }
        return $rs;
    }

}
