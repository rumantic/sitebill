<?php
/**
 * Slide menu class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Mega_Menu extends Structure_Manager {
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
        $category_structure = $this->loadCategoryStructure();
        $level = 1;
        $rs = '
<div id="menu_mega" class="jqueryslidemenu">
<ul id="menusys_mega">
        ';
        foreach ( $category_structure['childs'][0] as $item_id => $categoryID ) {
            if($category_structure['catalog'][$categoryID]['url']!=''){
            	if ( preg_match('/^http/', $category_structure['catalog'][$categoryID]['url']) ) {
            		$rs .= '<li class="item hasChild"><a class="'.($category_structure['catalog'][$categoryID]['current']==1 ? ' active' : ' item').'" href="'.$category_structure['catalog'][$categoryID]['url'].'"><span class="no-image"><span class="menu-title">'.$category_structure['catalog'][$categoryID]['name'].'</span></span></a>';
            	} else {
            		$rs .= '<li class="item hasChild"><a class="'.($category_structure['catalog'][$categoryID]['current']==1 ? ' active' : ' item').'" href="'.SITEBILL_MAIN_URL.'/'.$category_structure['catalog'][$categoryID]['url'].'"><span class="no-image"><span class="menu-title">'.$category_structure['catalog'][$categoryID]['name'].'</span></span></a>';
            	}
            		 
            }else{
            	$rs .= '<li class="item hasChild"><a class="'.($category_structure['catalog'][$categoryID]['current']==1 ? ' active' : ' item').'" href="'.SITEBILL_MAIN_URL.'/topic'.$categoryID.'.html"><span class="no-image"><span class="menu-title">'.$category_structure['catalog'][$categoryID]['name'].'</span></span></a>';
            }
        	
            $rs .= $this->getChildNodes($categoryID, $category_structure, $level + 1, $current_category_id);
            $rs .= '</li>';
        }
        
        $content_drop_menu = $this->get_content_drop_menu();
        if ( count($content_drop_menu) > 0 ) {
        	foreach ( $content_drop_menu as $tag => $menu_structure ) {
        		$rs .= '<li class="item hasChild"><a class="item" href="#"><span class="no-image"><span class="menu-title">'.$menu_structure[0]['menu_title'].'</span></span></a>';

        		$rs .= '<ul class="mega-ul ul">';
        		foreach ( $menu_structure as $item_id => $item ) {
        			if ( !preg_match('/http:/', $item['url']) ) {
        				$url = SITEBILL_MAIN_URL.$item['url'];
        			} else {
        				$url = $item['url'];
        			}
        			$rs .= '<li class="item hasChild"><a class="item" href="'.$url.'"><span class="no-image"><span class="menu-title">'.$item['name'].'</span></span></a>';
        		}
        		$rs .= '</ul>';
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
    	$rs .= '<ul class="mega-ul ul">';
        foreach ( $category_structure['childs'][$categoryID] as $child_id ) {
           // $rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/?topic_id='.$child_id.'">'.$category_structure['catalog'][$child_id]['name'].'</a>';
       		if($category_structure['catalog'][$child_id]['url']!=''){
            	$rs .= '<li><a class="item" href="'.SITEBILL_MAIN_URL.'/'.$category_structure['catalog'][$child_id]['url'].'"><span class="no-image"><span class="menu-title">'.$category_structure['catalog'][$child_id]['name'].'</span></span></a>';
            }else{
            	$rs .= '<li><a class="item" href="'.SITEBILL_MAIN_URL.'/topic'.$child_id.'.html"><span class="no-image"><span class="menu-title">'.$category_structure['catalog'][$child_id]['name'].'</span></span></a>';
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