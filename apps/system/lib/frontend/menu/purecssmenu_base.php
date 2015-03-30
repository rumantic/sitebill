<?php
class Purecssmenu_Base {
	
	public static function get_menu ($category_structure) {
			
			$rs = '<ul class="pureCssMenu pureCssMenum" style="border: 0px;">';
			foreach ( $category_structure['childs'][0] as $item_id => $categoryID ) {
	
				if ( count($category_structure['childs'][$categoryID]) > 0 ) {
					$name = '<span>'.$category_structure['catalog'][$categoryID]['name'].'</span>';
				} else {
					$name = '<span>'.$category_structure['catalog'][$categoryID]['name'].'</span>';
				}
	
				if($category_structure['catalog'][$categoryID]['url']!=''){
					if ( preg_match('/^http/', $category_structure['catalog'][$categoryID]['url']) ) {
						$rs .= '<li class="pureCssMenui"><a class="pureCssMenui'.($category_structure['catalog'][$categoryID]['current']==1 ? ' current' : '').'" href="'.$category_structure['catalog'][$categoryID]['url'].'">'.$name.'</a>';
					} else {
						$rs .= '<li class="pureCssMenui"><a class="pureCssMenui'.($category_structure['catalog'][$categoryID]['current']==1 ? ' current' : '').'" href="'.SITEBILL_MAIN_URL.'/'.$category_structure['catalog'][$categoryID]['url'].'">'.$name.'</a>';
					}
				}else{
					$rs .= '<li class="pureCssMenui"><a class="pureCssMenui'.($category_structure['catalog'][$categoryID]['current']==1 ? ' current' : '').'" href="'.SITEBILL_MAIN_URL.'/topic'.$categoryID.'.html">'.$name.'</a>';
				}
				 
				$rs .= self::getChildNodes($categoryID, $category_structure, $current_category_id=0);
				$rs .= '</li>';
			}
			$rs .= '</ul>';
			return $rs;
		}

		
		public static function getChildNodes($categoryID, $category_structure, $current_category_id) {
			$rs='';
			if ( !is_array($category_structure['childs'][$categoryID]) ) {
				return '';
			}
			$rs .= '<ul  class="pureCssMenum">';
			foreach ( $category_structure['childs'][$categoryID] as $child_id ) {
				if ( !empty($category_structure['childs'][$child_id]) AND count($category_structure['childs'][$child_id]) > 0 ) {
					$name = '<span>'.$category_structure['catalog'][$child_id]['name'].'</span>';
				} else {
					$name = $category_structure['catalog'][$child_id]['name'];
				}
	
				if($category_structure['catalog'][$child_id]['url']!=''){
					$rs .= '<li class="pureCssMenui"><a class="pureCssMenui'.($category_structure['catalog'][$child_id]['current']==1 ? ' current' : '').'" href="'.SITEBILL_MAIN_URL.'/'.$category_structure['catalog'][$child_id]['url'].'">'.$name.'</a>';
				}else{
					$rs .= '<li class="pureCssMenui"><a class="pureCssMenui'.($category_structure['catalog'][$child_id]['current']==1 ? ' current' : '').'" href="'.SITEBILL_MAIN_URL.'/topic'.$child_id.'.html">'.$name.'</a>';
				}
				if ( !empty($category_structure['childs'][$child_id]) AND count($category_structure['childs'][$child_id]) > 0 ) {
					$rs .= self::getChildNodes($child_id, $category_structure, $current_category_id);
				}
				$rs .= '</li>';
			}
			$rs .= '</ul>';
			return $rs;
		}
}