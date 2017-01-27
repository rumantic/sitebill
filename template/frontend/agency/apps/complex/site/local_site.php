<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
class local_complex_site extends complex_site {
	
	function grid () {
		$ra=parent::grid();
		if(count($ra['grid_array'])>0){
			foreach($ra['grid_array'] as $k=>$v){
				if($ra['grid_array'][$k]['geo']!=''){
					list($ra['grid_array'][$k]['geo_lat'], $ra['grid_array'][$k]['geo_lng'])=explode(',', $ra['grid_array'][$k]['geo']);
				}
			}
		}
		return $ra;
	}
	
}