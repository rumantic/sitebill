<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * menu
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class menu extends Object_Manager {
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
        
        $query = "select ms.*, m.tag from ".DB_PREFIX."_menu m, ".DB_PREFIX."_menu_structure ms where m.menu_id=ms.menu_id order by ms.sort_order";
        //echo $query;
        
        $this->db->exec($query);
        $ra = array();
        $i = 0;
        while ( $this->db->fetch_assoc() ) {
            $ra[$this->db->row['tag']][] = $this->db->row;
            //$i++;
        }
        foreach ( $ra as $tag => $menu_structure ) {
            //echo '<pre>';
            //print_r($menu_structure);
            //echo '</pre>';
            $smarty->assign($tag, $menu_structure);
        }
        return true;
    }
}
?>
