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
  `catalog_id` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `url` text NOT NULL,
  PRIMARY KEY (`banner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251        
        ";
        
        $rs = '<h3>'.Multilanguage::_('SQL_NOW','system').'</h3>';
        foreach ( $query_data as $query ) {
        	$this->db->exec($query);
        	if ( !$this->db->success ) {
        		$rs .= Multilanguage::_('ERROR_ON_SQL_RUN','system').': '.$query.', <b>'.$this->db->error.'</b><br>';
        	} else {
        		$rs .= Multilanguage::_('QUERY_SUCCESS','system').': '.$query.'<br>';
        	}
        }
        return $rs;
    }
}
