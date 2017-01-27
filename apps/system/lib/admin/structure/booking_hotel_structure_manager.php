<?php
class Booking_Hotel_Structure_Manager extends Structure_Implements {
	
	function __construct() {
		$this->SiteBill();
		$this->table='booking_hotel_topic';
		$this->entity='booking_hotel';
		$this->action='structure_'.$this->entity;
		$this->operation_type_array = $this->load_operation_type_list();
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/version/version.php';
		$version = new Version();
		if ( !$version->get_version_value('topic.url') ) {
			$this->add_topic_url();
			$version->set_version_value('topic.url', 1);
		}
		//echo 534535;
	}
	
	function load_data_structure ( $user_id, $params = array(), $search_params=array() ) {
		$where='';
		if($user_id!=0 && $user_id!=''){
			$where=' WHERE user_id='.$user_id;
		}
		$query = "SELECT COUNT(hotel_id) as total, booking_hotel_topic_id FROM ".DB_PREFIX."_booking_hotel ".$where." GROUP BY booking_hotel_topic_id";
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			while ( $ar=$DBC->fetch($stmt) ) {
				$ret['data'][$user_id][$ar['booking_hotel_topic_id']]=$ar['total'];
			}
		}
		return $ret;
	}
	
	function loadCategoryStructure($user_id=''){
		$category_structure = parent::loadCategoryStructure();
		$data_structure = $this->load_data_structure( $user_id, $params, $search_params );
		if(count($category_structure['catalog'])>0){
			foreach($category_structure['catalog'] as $cat_point){
				$ch=0;
				$this->getChildsItemsCount($cat_point['id'], $category_structure['childs'], $data_structure['data'][$user_id], $ch);
				$data_structure['data'][$user_id][$cat_point['id']]+=$ch;
			}
			foreach($category_structure['catalog'] as $k=>$cat_point){
				if(isset($data_structure['data'][$user_id][$k])){
					$category_structure['catalog'][$k]['data_count']=$data_structure['data'][$user_id][$k];
				}else{
					$category_structure['catalog'][$k]['data_count']=0;
				}
			}
			$cc=$this->createCatalogChains();
			//print_r($cc);
			foreach($category_structure['catalog'] as $k=>$cat_point){
				$chain=array();
				if(isset($cc['num'][$k])){
					foreach(explode('|',$cc['num'][$k]) as $n){
						$chain[]=array('href'=>$category_structure['catalog'][$n]['url'],'name'=>$category_structure['catalog'][$n]['name']);
					}
				}
				$category_structure['catalog'][$k]['chain']=$chain;
			}
		}
		return $category_structure;
	}
	
	function getCategoriesArray($user_id='',$my=false){
		$category_structure = $this->loadCategoryStructure($user_id);
		
		$cats=array();
		
		if(count($category_structure['childs'][0])>0){
			foreach ( $category_structure['childs'][0] as $item_id => $catalog_id ) {
				$cats[$catalog_id]['name']=$category_structure['catalog'][$catalog_id]['name'];
				if($my){
					$cats[$catalog_id]['href']='my/?topic='.$catalog_id;
					/*if($category_structure['catalog'][$catalog_id]['url'] == ''){
						$cats[$catalog_id]['href']='topic'.$catalog_id;
					}else{
						$cats[$catalog_id]['href']=$category_structure['catalog'][$catalog_id]['url'];
					}*/
				}else{
					if($category_structure['catalog'][$catalog_id]['url'] == ''){
						$cats[$catalog_id]['href']='topic'.$catalog_id;
					}else{
						$cats[$catalog_id]['href']=$category_structure['catalog'][$catalog_id]['url'];
					}
				}
				
				
				$cats[$catalog_id]['count']=$category_structure['catalog'][$catalog_id]['data_count'];
				//$cats[$catalog_id]['count']=$data_structure['data'][''][$catalog_id];
				if(isset($category_structure['childs'][$catalog_id])){
					$this->appendChilds($cats[$catalog_id],$catalog_id,$category_structure,$my);
				}
			}
		}
		return $cats;
	}
	
	function appendChilds(&$el,$id,$category_structure,$my){
		foreach ( $category_structure['childs'][$id] as $item_id => $catalog_id ) {
			unset($x);
			$x['name']=$category_structure['catalog'][$catalog_id]['name'];
			if($my){
				$x['href']='my/?topic='.$catalog_id;
			}else{
				if($category_structure['catalog'][$catalog_id]['url'] == ''){
					$x['href']='topic'.$catalog_id;
				}else{
					$x['href']=$category_structure['catalog'][$catalog_id]['url'];
				}
			}
			
			//$x['href']='topic'.$catalog_id;
			//$x['count']=$data_structure['data'][''][$catalog_id];
			$x['count']=$category_structure['catalog'][$catalog_id]['data_count'];
			if(isset($category_structure['childs'][$catalog_id])){
				$this->appendChilds($x,$catalog_id,$category_structure,$my);
			}
			$el['childs'][$catalog_id]=$x;
		}
	}
	
	function reorderTopics($orderArray){
		//print_r($orderArray);
		if(count($orderArray)>0){
			$DBC=DBC::getInstance();
			foreach($orderArray as $k=>$v){
				$query='UPDATE '.DB_PREFIX.'_'.$this->table.' SET `order`='.((int)$v).' WHERE id='.((int)$k);
				$stmt=$DBC->query($query);
			}
		}
	}
	
}