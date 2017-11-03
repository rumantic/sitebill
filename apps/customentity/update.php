<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
class customentity_update extends SiteBill {
	function main () {
		$query_data=array();
        $query_data[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_customentity` (
		  `entity_name` varchar(255) NOT NULL,
		  `entity_title` varchar(255) NOT NULL,
		  PRIMARY KEY (`entity_name`)
		) ENGINE=MyISAM DEFAULT CHARSET=".DB_ENCODING."";
        
		$query_data[] = "ALTER TABLE  ".DB_PREFIX."_customentity ADD COLUMN `is_public` TINYINT(1) NOT NULL DEFAULT 1";
		$query_data[] = "ALTER TABLE  ".DB_PREFIX."_customentity ADD COLUMN `alias`	VARCHAR(255) NOT NULL DEFAULT ''";
		$query_data[] = "ALTER TABLE  ".DB_PREFIX."_customentity ADD COLUMN `list_tpl` VARCHAR(255) NOT NULL DEFAULT ''";
		$query_data[] = "ALTER TABLE  ".DB_PREFIX."_customentity ADD COLUMN `view_tpl` VARCHAR(255) NOT NULL DEFAULT ''";
		$query_data[] = "ALTER TABLE  ".DB_PREFIX."_customentity ADD COLUMN `per_page` INT(11) NOT NULL DEFAULT '10'";
		$query_data[] = "ALTER TABLE  ".DB_PREFIX."_customentity ADD COLUMN `sortby` VARCHAR(100) NOT NULL DEFAULT ''";
		$query_data[] = "ALTER TABLE  ".DB_PREFIX."_customentity ADD COLUMN `sortorder` VARCHAR(4) NOT NULL DEFAULT ''";
		$query_data[] = "ALTER TABLE  ".DB_PREFIX."_customentity ADD COLUMN `list_title` VARCHAR(255) NOT NULL DEFAULT ''";
		$query_data[] = "ALTER TABLE  ".DB_PREFIX."_customentity ADD COLUMN `view_title` VARCHAR(255) NOT NULL DEFAULT ''";
        
      
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