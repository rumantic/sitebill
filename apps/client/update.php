<?php
class client_update extends SiteBill {
    /**
     * Construct
     */
    function __construct() {
        parent::__construct();
    }

    function main () {
        $rs='';
        $DBC=DBC::getInstance();
        $query_data[] = "ALTER TABLE ".DB_PREFIX."_client CHANGE  `status_id`  `status_id` VARCHAR( 255 ) NOT NULL";
        $query_data[] = "ALTER TABLE ".DB_PREFIX."_client CHANGE  `type_id`  `type_id` VARCHAR( 100 ) NOT NULL";
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
