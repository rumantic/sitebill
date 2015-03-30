<?php
class Menu_Builder extends Structure_Manager {
	function __construct() {
		$this->SiteBill();
	}
	
	function get_menu () {
		$category_structure = $this->loadCategoryStructure();
		/*$level = 1;*/
		$menu=array();
		/*$rs = '
<div id="myslidemenu" class="jqueryslidemenu">
<ul>
        ';*/
		foreach ( $category_structure['childs'][0] as $item_id => $categoryID ) {
			if($category_structure['catalog'][$categoryID]['url']!=''){
				if ( preg_match('/^http/', $category_structure['catalog'][$categoryID]['url']) ) {
					$menu[$categoryID]['href']=$category_structure['catalog'][$categoryID]['url'];
					$menu[$categoryID]['title']=$category_structure['catalog'][$categoryID]['name'];
					//$rs .= '<li><a href="'.$category_structure['catalog'][$categoryID]['url'].'">'.$category_structure['catalog'][$categoryID]['name'].'</a>';
				} else {
					$menu[$categoryID]['href']=SITEBILL_MAIN_URL.'/'.$category_structure['catalog'][$categoryID]['url'];
					$menu[$categoryID]['title']=$category_structure['catalog'][$categoryID]['name'];
					//$rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/'.$category_structure['catalog'][$categoryID]['url'].'">'.$category_structure['catalog'][$categoryID]['name'].'</a>';
				}
				 
			}else{
				$menu[$categoryID]['href']=SITEBILL_MAIN_URL.'/topic'.$categoryID.'.html';
				$menu[$categoryID]['title']=$category_structure['catalog'][$categoryID]['name'];
				//$rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/topic'.$categoryID.'.html">'.$category_structure['catalog'][$categoryID]['name'].'</a>';
			}
			$menu[$categoryID]['childs']=$this->getChildNodes($categoryID, $category_structure, $current_category_id);
			//$rs .= $this->getChildNodes($categoryID, $category_structure, $level + 1, $current_category_id);
			/*$rs .= '</li>';*/
		}
		/*$rs .= '
</ul>
</div>
        ';*/
		return $menu;
		return $rs;
	}
	
	function getChildNodes($categoryID, $category_structure, $current_category_id) {
		if ( !is_array($category_structure['childs'][$categoryID]) || count($category_structure['childs'][$categoryID])<1 ) {
			return array();
		}
		$menu=array();
		//$rs .= '<ul>';
		foreach ( $category_structure['childs'][$categoryID] as $child_id ) {
			// $rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/?topic_id='.$child_id.'">'.$category_structure['catalog'][$child_id]['name'].'</a>';
			if($category_structure['catalog'][$child_id]['url']!=''){
				$menu[$child_id]['href']=SITEBILL_MAIN_URL.'/'.$category_structure['catalog'][$child_id]['url'];
				$menu[$child_id]['title']=$category_structure['catalog'][$child_id]['name'];
				//$menu[$child_id]['href']=SITEBILL_MAIN_URL.'/'.$category_structure['catalog'][$child_id]['url'];
				//$menu[$child_id]['title']=$category_structure['catalog'][$child_id]['name'];
				//$rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/'.$category_structure['catalog'][$child_id]['url'].'">'.$category_structure['catalog'][$child_id]['name'].'</a>';
			}else{
				$menu[$child_id]['href']=SITEBILL_MAIN_URL.'/topic'.$child_id.'.html';
				$menu[$child_id]['title']=$category_structure['catalog'][$child_id]['name'];
				//$menu[$child_id]['href']=SITEBILL_MAIN_URL.'/'.$category_structure['catalog'][$child_id]['url'];
				//$menu[$child_id]['title']=$category_structure['catalog'][$child_id]['name'];
				//$rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/topic'.$child_id.'.html">'.$category_structure['catalog'][$child_id]['name'].'</a>';
			}
			if ( count($category_structure['childs'][$child_id]) > 0 ) {
				$menu[$child_id]['childs']=$this->getChildNodes($child_id, $category_structure, $current_category_id);
				//$rs .= $this->getChildNodes($child_id, $category_structure, $current_category_id);
			}
			//$rs .= '</li>';
		}
		//$rs .= '</ul>';
		return $menu;
		return $rs;
	}
}