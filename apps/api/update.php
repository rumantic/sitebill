<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

class api_update extends SiteBill {

    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
    }

    function main() {
        $query_data[] = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_oauth` (
				`oauth_id` int(11) NOT NULL AUTO_INCREMENT,
				`user_id` int(11) NOT NULL,
                                `session_key` varchar(255) NOT NULL,
				`ip` varchar(255) NOT NULL,
                                `date_login` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY (`oauth_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=" . DB_ENCODING . " ;";


        //$rs = '<h3>'.Multilanguage::_('SQL_NOW','system').'</h3>';

        $DBC = DBC::getInstance();


        foreach ($query_data as $query) {
            $success = false;
            $stmt = $DBC->query($query, array(), $rows, $success);
            if (!$success) {
                //$rs .= Multilanguage::_('ERROR_ON_SQL_RUN','system').': '.$query.'<br>';
            } else {
                //$rs .= Multilanguage::_('QUERY_SUCCESS','system').': '.$query.'<br>';
            }
        }
        return $rs;
    }

}
