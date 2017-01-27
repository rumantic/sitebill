<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
class comment_update extends SiteBill {
    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
    }
    
    function main () {
        $query_data[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_comment` (
		  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
		  `comment_text` text NOT NULL,
		  `comment_date` datetime NOT NULL,
		  `parent_comment_id` int(10) unsigned NOT NULL DEFAULT '0',
		  `object_type` varchar(255) NOT NULL,
		  `object_id` int(10) unsigned NOT NULL DEFAULT '0',
		  `is_published` tinyint(1) unsigned NOT NULL DEFAULT '1',
		  PRIMARY KEY (`comment_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=".DB_ENCODING." AUTO_INCREMENT=1 ;";
        
        
        $rs = '<h3>'.Multilanguage::_('SQL_NOW','system').'</h3>';
        $DBC=DBC::getInstance();
        
        foreach ( $query_data as $query ) {
        	$stmt=$DBC->query($query);
        	if ( !$stmt ) {
        		$rs .= Multilanguage::_('ERROR_ON_SQL_RUN','system').': '.$query.'<br>';
        	} else {
        		$rs .= Multilanguage::_('QUERY_SUCCESS','system').': '.$query.'<br>';
        	}
        }
        return $rs;
    }
}