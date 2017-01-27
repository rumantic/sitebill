<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
class customentity_update extends SiteBill {
	function main () {
        $query_data[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_customentity` (
		  `entity_name` varchar(255) NOT NULL,
		  `entity_title` varchar(255) NOT NULL,
		  PRIMARY KEY (`entity_name`)
		) ENGINE=MyISAM DEFAULT CHARSET=".DB_ENCODING."";
        
       	$rs = '<h3>'.Multilanguage::_('SQL_NOW','system').'</h3>';
        $DBC=DBC::getInstance();
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