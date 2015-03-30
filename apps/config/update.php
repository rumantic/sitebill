<?php
class config_update extends SiteBill {
    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
    }
    
    function main () {
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
        $config_admin = new config_admin();
        $config_admin->check_config_structure();
   		$rs = Multilanguage::_('UPDATE_CONFIG_SUCCESS','system').'<br>';
        return $rs;
    }
}
