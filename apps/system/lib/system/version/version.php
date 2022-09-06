<?php
/**
 * This class check version of the API and update table DB_PREFIX_version
 * In table DB_PREFIX_version store all information about changes in tables and code
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
class Version extends Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
    }
    
    /**
     * Get version value by tag
     * @param string $tag
     * @return mixed 
     */
    function get_version_value ( $tag ) {
        return true;
    }
    
    /**
     * Set version value tag
     * @param string $tag
     * @param int $verion
     * @return value 
     */
    function set_version_value ( $tag, $version ) {
        $query = "insert into ".DB_PREFIX."_version (name, version) values (?, ?)";
        $DBC=DBC::getInstance();
        $stmt=$DBC->query($query, array($tag, $version));
        return true;
    }
}
?>
