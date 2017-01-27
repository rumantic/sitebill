<?php
/**
 * Slide menu class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Slide_Menu extends Structure_Manager {
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
    }
    
    /**
     * Get menu
     * @param
     * @return
     */
    function get_menu () {
        $category_structure = $this->loadCategoryStructure($this->getConfigValue('use_topic_publish_status'));
        $level = 1;
        $rs = '
<div id="myslidemenu" class="jqueryslidemenu">
<ul>
        ';
        if(isset($category_structure['childs'][0]) && count($category_structure['childs'][0])>0){
        	foreach ( $category_structure['childs'][0] as $item_id => $categoryID ) {
        		if($category_structure['catalog'][$categoryID]['url']!=''){
        			if ( preg_match('/^http/', $category_structure['catalog'][$categoryID]['url']) ) {
        				$rs .= '<li><a href="'.$category_structure['catalog'][$categoryID]['url'].'">'.$category_structure['catalog'][$categoryID]['name'].'</a>';
        			} else {
        				$rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/'.$category_structure['catalog'][$categoryID]['url'].'">'.$category_structure['catalog'][$categoryID]['name'].'</a>';
        			}
        			 
        		}else{
        			$rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/topic'.$categoryID.'.html">'.$category_structure['catalog'][$categoryID]['name'].'</a>';
        		}
        		 
        		$rs .= $this->getChildNodes($categoryID, $category_structure, $level + 1, $current_category_id);
        		$rs .= '</li>';
        	}
        }
        
        $rs .= '
</ul>
</div>
        ';
        return $rs;
    }
    
    /**
     * Get child nodes
     * @param $categoryID
     * @param $category_structure
     * @param $level
     * @param $current_category_id
     */
    function getChildNodes($categoryID, $category_structure, $level, $current_category_id) {
    	if ( !is_array($category_structure['childs'][$categoryID]) ) {
    		return '';
    	}
    	$rs .= '<ul>';
        foreach ( $category_structure['childs'][$categoryID] as $child_id ) {
           // $rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/?topic_id='.$child_id.'">'.$category_structure['catalog'][$child_id]['name'].'</a>';
       		if($category_structure['catalog'][$child_id]['url']!=''){
            	$rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/'.$category_structure['catalog'][$child_id]['url'].'">'.$category_structure['catalog'][$child_id]['name'].'</a>';
            }else{
            	$rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/topic'.$child_id.'.html">'.$category_structure['catalog'][$child_id]['name'].'</a>';
            }
            if ( count($category_structure['childs'][$child_id]) > 0 ) {
                $rs .= $this->getChildNodes($child_id, $category_structure, $level + 1, $current_category_id);
            }
            $rs .= '</li>';
        }
        $rs .= '</ul>';
        return $rs;
    }
    
}
?>