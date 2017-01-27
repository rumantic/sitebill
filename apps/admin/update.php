<?php
class admin_update extends SiteBill {
    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
    }
    
    function main () {
    	$rs .= 'update access rights<br>';
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/permission/permission.php');
    	$permssion = new Permission();
    	$permssion->init_components();
    	 
        return $rs;
    }
}