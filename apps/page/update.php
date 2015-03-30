<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
class page_update extends SiteBill {
    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
    }
    
    function main () {
        $query_data[] = "ALTER TABLE ".DB_PREFIX."_page ADD COLUMN is_service INT(11)";
        
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
