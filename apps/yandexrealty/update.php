<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
class yandexrealty_update extends SiteBill {
    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
    }
    
    function main () {
        $query_data[] = "alter table ".DB_PREFIX."_yandexrealty_assoc add column realty_category tinyint(4) NOT NULL DEFAULT '0'";
        
        $rs = 'start update database<br>';
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
