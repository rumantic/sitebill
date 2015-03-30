<?php
class Menu_Decorator {
	
	public static function getMenu($view_type='slidemenu', $category_structure=array()){
		//echo $view_type;
		switch($view_type){
			case 'slidemenu' : {
				$function_name='getMenu_slidemenu';
				break;
			}
			case 'purecss' : {
				$function_name='getMenu_purecssmenu';
				break;
			}
			case 'megamenu' : {
				$function_name='getMenu_megamenu';
				break;
			}
			case 'sfmenu' : {
				$function_name='getMenu_sfmenu';
				break;
			}
			default : {
				$function_name='getMenu_slidemenu';
			}
		}
		
		return self::$function_name($category_structure);
	}
	
	private static function getMenu_slidemenu($category_structure){
		$rs = '<div id="myslidemenu" class="jqueryslidemenu">';
		$rs .= '<ul>';
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
			 
			$rs .= self::getChildNodes_slidemenu($categoryID, $category_structure, $current_category_id);
			$rs .= '</li>';
		}
		$rs .= '</ul></div>';
		return $rs;
	}
	
	private static function getMenu_purecssmenu($category_structure){
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
		
			$rs .= self::getChildNodes_purecssmenu($categoryID, $category_structure, $current_category_id=0);
			$rs .= '</li>';
		}
		$rs .= '</ul>';
		return $rs;
	}
	
	private static function getMenu_megamenu($category_structure){
		$rs = '<div id="menu_mega" class="jqueryslidemenu"><ul id="menusys_mega">';
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
			 
			$rs .= self::getChildNodes_megamenu($categoryID, $category_structure, $current_category_id);
			$rs .= '</li>';
		}
		$rs .= '</ul></div>';
		return $rs;
	}
	
	private static function getChildNodes_slidemenu($categoryID, $category_structure, $current_category_id) {
		if ( !is_array($category_structure['childs'][$categoryID]) ) {
			return '';
		}
		$rs .= '<ul>';
		foreach ( $category_structure['childs'][$categoryID] as $child_id ) {
			if($category_structure['catalog'][$child_id]['url']!=''){
				if ( preg_match('/^http/', $category_structure['catalog'][$categoryID]['url']) ) {
					$rs .= '<li><a href="'.$category_structure['catalog'][$child_id]['url'].'">'.$category_structure['catalog'][$child_id]['name'].'</a>';
				} else {
					$rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/'.$category_structure['catalog'][$child_id]['url'].'">'.$category_structure['catalog'][$child_id]['name'].'</a>';
				}
			}else{
				$rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/topic'.$child_id.'.html">'.$category_structure['catalog'][$child_id]['name'].'</a>';
			}
			if ( count($category_structure['childs'][$child_id]) > 0 ) {
				$rs .= self::getChildNodes_slidemenu($child_id, $category_structure, $current_category_id);
			}
			$rs .= '</li>';
		}
		$rs .= '</ul>';
		return $rs;
	}
	
	private static function getChildNodes_purecssmenu($categoryID, $category_structure, $current_category_id) {
		$rs='';
		if ( !is_array($category_structure['childs'][$categoryID]) ) {
			return '';
		}
		$rs .= '<ul class="pureCssMenum">';
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
				$rs .= self::getChildNodes_purecssmenu($child_id, $category_structure, $current_category_id);
			}
			$rs .= '</li>';
		}
		$rs .= '</ul>';
		return $rs;
	}
	
	private static function getChildNodes_megamenu($categoryID, $category_structure, $current_category_id) {
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
				$rs .= self::getChildNodes_megamenu($child_id, $category_structure, $current_category_id);
			}
			$rs .= '</li>';
		}
		$rs .= '</ul>';
		return $rs;
	}
	
	private static function getMenu_sfmenu($category_structure){
		$rs .= '<ul class="sf-menu">';
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
	
			$rs .= self::getChildNodes_slidemenu($categoryID, $category_structure, $current_category_id);
			$rs .= '</li>';
		}
		$rs .= '</ul>';
		return $rs;
	}
	
	private static function getChildNodes_sfmenu($categoryID, $category_structure, $current_category_id) {
		if ( !is_array($category_structure['childs'][$categoryID]) ) {
			return '';
		}
		$rs .= '<ul>';
		foreach ( $category_structure['childs'][$categoryID] as $child_id ) {
			// $rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/?topic_id='.$child_id.'">'.$category_structure['catalog'][$child_id]['name'].'</a>';
			if($category_structure['catalog'][$child_id]['url']!=''){
				$rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/'.$category_structure['catalog'][$child_id]['url'].'">'.$category_structure['catalog'][$child_id]['name'].'</a>';
			}else{
				$rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/topic'.$child_id.'.html"><span class="no-image">'.$category_structure['catalog'][$child_id]['name'].'</a>';
			}
			if ( count($category_structure['childs'][$child_id]) > 0 ) {
				$rs .= self::getChildNodes_sfmenu($child_id, $category_structure, $current_category_id);
			}
			$rs .= '</li>';
		}
		$rs .= '</ul>';
		return $rs;
	}
}