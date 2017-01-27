<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Left menu
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class left_menu extends Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
    }
    
    /**
     * Main
     */
    function main () {
        global $smarty;
        $DBC=DBC::getInstance();
        $query = "select ms.* from ".DB_PREFIX."_menu m, ".DB_PREFIX."_menu_structure ms where m.tag='left' and m.menu_id=ms.menu_id order by sort_order";
        $stmt=$DBC->query($query);
        $ra = array();
        if($stmt){
        	$i = 0;
        	while ( $ar=$DBC->fetch($stmt) ) {
        		$ra[$i] = $ar;
        		$i++;
        	}
        }
        
        $smarty->assign('left_menu', $ra);
        return true;
    }
}