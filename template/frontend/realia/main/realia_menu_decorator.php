<?php
class Realia_Menu_Decorator {
	
	public static function getMenu($category_structure=array()){
		return self::buildMenu($category_structure);
	}
	
	private static function buildMenu($category_structure){
		$rs = '<ul class="nav">';
		foreach ( $category_structure['childs'][0] as $item_id => $categoryID ) {
			$hasChilds=false;
			if ( count($category_structure['childs'][$categoryID]) > 0 ) {
				$hasChilds=true;
				$name = $category_structure['catalog'][$categoryID]['name'];
			} else {
				$name = $category_structure['catalog'][$categoryID]['name'];
			}
			
			if(isset($category_structure['catalog'][$categoryID]['_cnt'])){
				$name=$name.' ('.$category_structure['catalog'][$categoryID]['_cnt'].')';
			}
				
			if($hasChilds){
				$li_open='<li class="menuparent">';
			}else{
				$li_open='<li>';
			}
			
			$title='';
				
			if($category_structure['catalog'][$categoryID]['url']!=''){
				if ( preg_match('/^http/', $category_structure['catalog'][$categoryID]['url']) ) {
					$rs .= $li_open.'<a href="'.$category_structure['catalog'][$categoryID]['url'].'"'.($hasChilds ? ' class="menuparent"' : '').'>'.$name.'</a>';
				} else {
					$rs .= $li_open.'<a href="'.SITEBILL_MAIN_URL.'/'.$category_structure['catalog'][$categoryID]['url'].'"'.($hasChilds ? ' class="menuparent"' : '').'>'.$name.'</a>';
				}
			}else{
				$rs .= $li_open.'<a href="'.SITEBILL_MAIN_URL.'/topic'.$categoryID.'.html"'.($hasChilds ? ' class="menuparent"' : '').'>'.$name.'</a>';
			}
	
				
			if($hasChilds){
				$rs .= self::buildChildNodes($categoryID, $category_structure, 0);
			}
			$rs .= '</li>';
		}
		$rs .= '</ul>';
		return $rs;
	}
	
	private static function buildChildNodes($categoryID, $category_structure, $current_category_id) {
		if ( !is_array($category_structure['childs'][$categoryID]) ) {
			return '';
		}
	
		$rs = '<ul>';
		foreach ( $category_structure['childs'][$categoryID] as $child_id ) {
			$hasChilds=false;
			if ( count($category_structure['childs'][$child_id]) > 0 ) {
				$hasChilds=true;
				$name = $category_structure['catalog'][$child_id]['name'];
			} else {
				$name = $category_structure['catalog'][$child_id]['name'];
			}
			
			if(isset($category_structure['catalog'][$child_id]['_cnt'])){
				$name=$name.' ('.$category_structure['catalog'][$child_id]['_cnt'].')';
			}
	
			if($hasChilds){
				$li_open='<li class="menuparent">';
			}else{
				$li_open='<li>';
			}
	
			if($category_structure['catalog'][$child_id]['url']!=''){
				if ( preg_match('/^http/', $category_structure['catalog'][$child_id]['url']) ) {
					$rs .= $li_open.'<a href="'.$category_structure['catalog'][$child_id]['url'].'"'.($hasChilds ? ' class="menuparent"' : '').'>'.$name.'</a>';
				} else {
					$rs .= $li_open.'<a href="'.SITEBILL_MAIN_URL.'/'.$category_structure['catalog'][$child_id]['url'].'"'.($hasChilds ? ' class="menuparent"' : '').'>'.$name.'</a>';
				}
			}else{
				$rs .= $li_open.'<a href="'.SITEBILL_MAIN_URL.'/topic'.$child_id.'.html"'.($hasChilds ? ' class="menuparent"' : '').'>'.$name.'</a>';
			}
	
	
	
			$rs .= self::buildChildNodes($child_id, $category_structure, 0);
			$rs .= '</li>';
		}
		$rs .= '</ul>';
		return $rs;
	
	}
	
}