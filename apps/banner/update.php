<?php
class banner_update extends SiteBill {
    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
    }
    
    function main () {
        $query_data[] = "
CREATE TABLE `".DB_PREFIX."_banner` (
  `banner_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `body` text,
  `description` text,
  `catalog_id` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `url` text NOT NULL,
  PRIMARY KEY (`banner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=".DB_ENCODING."";
        $query_data[] = "alter table ".DB_PREFIX."_banner add column description text";
        
        $query_data[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_banner_informer` (`biid` int(11) NOT NULL AUTO_INCREMENT, `access_code` varchar(10) NOT NULL, `informer_parameters` text NOT NULL, `is_active` tinyint(1) NOT NULL, PRIMARY KEY (`biid`)) ENGINE=MyISAM  DEFAULT CHARSET=".DB_ENCODING."";
        
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