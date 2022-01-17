<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
require_once (SITEBILL_DOCUMENT_ROOT.'/apps/menu/admin/admin.php');

class menu_update extends menu_admin {
    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
    }

    function main () {
        $query_data = $this->migrations();

        $rs = '<h3>'.Multilanguage::_('SQL_NOW','system').'</h3>';
        $DBC = DBC::getInstance();

        foreach ( $query_data as $query ) {
            $success=false;
            $stmt=$DBC->query($query, array(), $rows, $success);
            if ( !$success ) {
                $rs .= Multilanguage::_('ERROR_ON_SQL_RUN','system').': '.$query.'<br>';
            } else {
                $rs .= Multilanguage::_('QUERY_SUCCESS','system').': '.$query.'<br>';
            }
        }
        return $rs;
    }
}
