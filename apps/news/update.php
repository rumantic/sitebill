<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
class news_update extends SiteBill {
    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
    }
    
    function main () {
        $query_data[] = "ALTER TABLE ".DB_PREFIX."_news ADD COLUMN news_topic_id INT(11)";
        
        $query_data[] = "ALTER TABLE ".DB_PREFIX."_news ADD COLUMN meta_h1 text";
        $query_data[] = "ALTER TABLE ".DB_PREFIX."_news ADD COLUMN meta_title text";
        $query_data[] = "ALTER TABLE ".DB_PREFIX."_news ADD COLUMN meta_description text";
        $query_data[] = "ALTER TABLE ".DB_PREFIX."_news ADD COLUMN meta_keywords text";
        $query_data[] = "ALTER TABLE ".DB_PREFIX."_news ADD COLUMN newsalias text";
        
        $query_data[] = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_news_topic` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `name` varchar(255) NOT NULL,
				  `url` varchar(255) NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=".DB_ENCODING." ;";
        
        
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
