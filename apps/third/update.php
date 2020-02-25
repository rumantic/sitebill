<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
class third_update extends SiteBill {
    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
    }
    
    function main () {
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/third/phpexcel/PHPExcel/Shared/JAMA/docs/download.php')) {
            $rs .= 'Удаление устаревшей библиотеки PHPExcel JAMA docs<br>';
            Sitebill::removeDirectory(SITEBILL_DOCUMENT_ROOT . '/apps/third/phpexcel/PHPExcel/Shared/JAMA/docs', $msgs);
            if (count($msg) > 0) {
                foreach ($msgs as $msg) {
                    $rs .= $msg . '<br/>';
                }
            }
        }
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/third/phpexcel/PHPExcel/Shared/JAMA/examples/tile.php')) {
            $rs .= 'Удаление устаревшей библиотеки PHPExcel JAMA examplex <br>';
            Sitebill::removeDirectory(SITEBILL_DOCUMENT_ROOT . '/apps/third/phpexcel/PHPExcel/Shared/JAMA/examples', $msgs);
            if (count($msg) > 0) {
                foreach ($msgs as $msg) {
                    $rs .= $msg . '<br/>';
                }
            }
        }
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/third/phpexcel/PHPExcel/Shared/JAMA/tests/TestMatrix.php')) {
            $rs .= 'Удаление устаревшей библиотеки PHPExcel JAMA tests <br>';
            Sitebill::removeDirectory(SITEBILL_DOCUMENT_ROOT . '/apps/third/phpexcel/PHPExcel/Shared/JAMA/tests', $msgs);
            if (count($msg) > 0) {
                foreach ($msgs as $msg) {
                    $rs .= $msg . '<br/>';
                }
            }
        }
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/third/phpexcel/PHPExcel.php')) {
            $rs .= 'Удаление устаревшей библиотеки PHPExcel<br>';
            Sitebill::removeDirectory(SITEBILL_DOCUMENT_ROOT . '/apps/third/phpexcel', $msgs);
            if ( is_array($msg) ) {
                if (count($msg) > 0) {
                    foreach ($msgs as $msg) {
                        $rs .= $msg . '<br/>';
                    }
                }
            }
        }
        
        
        return $rs;
    }
}