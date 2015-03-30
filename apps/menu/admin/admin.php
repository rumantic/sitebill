<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
* Service menu backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class menu_admin extends Object_Manager {
	
	private $pages=array();
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
    }
    
    public function _preload(){
    	$this->loadMenus();
    }
    
    private function loadMenus(){
    	global $smarty;
    	$ra = array();
    	$DBC=DBC::getInstance();
    	
    	$query = "SELECT ms.*, m.tag, m.name as menu_title FROM ".DB_PREFIX."_menu m, ".DB_PREFIX."_menu_structure ms WHERE m.menu_id=ms.menu_id ORDER BY ms.sort_order";
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while ( $ar=$DBC->fetch($stmt) ) {
    			if ( isset($ar['name_'.Multilanguage::get_current_language()]) && $ar['name_'.Multilanguage::get_current_language()] != '' ) {
    				 $ar['name'] = $ar['name_'.Multilanguage::get_current_language()];
    			}
    			$ar['url']=trim($ar['url']);
    			if($ar['url']!='' && 0!==strpos($ar['url'], 'http:')){
    				$ar['url'] = SITEBILL_MAIN_URL.'/'.trim($ar['url'], '/').'/';
    			}
    			$ra[$ar['tag']][] = $ar;
    			
    		}
    	}
    	if(!empty($ra)){
    		foreach ( $ra as $tag => $menu_structure ) {
    			$smarty->assign($tag, $menu_structure);
    			$tag_title = $tag.'_title';
    			$smarty->assign($tag_title, $menu_structure[0]['menu_title']);
    		}
    	}
    }
}