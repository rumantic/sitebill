<?php
class Front_Grid_Constructor extends SiteBill_Krascap {
	
	public function grid_exists($topics){
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/front_gridmanager_admin.php')){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php';
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/front_gridmanager_admin.php';
			$FGMA=new front_gridmanager_admin();
			return $FGMA->getCustomGrid($topics);
		}
		return false;
	}
	
	public function fullGenerate($model, $fields_array, $params){
		
		$adv=array();
		$primary_key='';
		$grid_head_names=array();
		$grid_head_ids=array();
		
		foreach($fields_array as $f=>$ff){
			$grid_head_ids[$f]=$f;
		}
		
		foreach($fields_array as $f=>$ff){
			if(isset($model[$f])){
				if($ff['title']==''){
					$fields_array[$f]['title']=$model[$f]['title'];
					$fields_array[$f]['type']=$model[$f]['type'];
				}
			}
		}
		
		$ks=array_keys($model);
		$main_table=$ks[0];
		
		$model=$model[$main_table];
		
		$main_table=DB_PREFIX.'_'.$main_table;
		
		$select_query=$this->buildSelectQuery($model, $main_table);
		
		if(isset($params['asc']) && in_array($params['asc'], array('asc', 'desc'))){
			$order_direction=$params['asc'];
		}else{
			$order_direction='DESC';
		}
		
		if(isset($params['order']) && $params['order']!='' && in_array($params['order'], $grid_head_ids)){
			if(isset($fields_array[$params['order']]['attached']) && !empty($fields_array[$params['order']]['attached'])){
				$order='ORDER BY '.$params['order'].' '.$order_direction;
				foreach($fields_array[$params['order']]['attached'] as $attached){
					if(isset($model[$attached['name']])){
						$order.=', '.$attached['name'].' '.$order_direction;
					}
				}
			}else{
				$order='ORDER BY '.$params['order'].' '.$order_direction;
			}
			$this->template->assert('current_sort', $params['order']);
		}else{
			$order='ORDER BY price '.$order_direction;
			$this->template->assert('current_sort', 'price');
		}
		
		
		
		if(isset($model['topic_id'])){
			if ( !is_array($params['topic_id']) && $params['topic_id'] != '' &&  $params['topic_id'] != 0) {
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
				$Structure_Manager = new Structure_Manager();
				$category_structure = $Structure_Manager->loadCategoryStructure();
				global $smarty;
				//echo $category_structure['catalog'][$params['topic_id']]['description'];
				$smarty->assign('topic_description', $category_structure['catalog'][$params['topic_id']]['description']);
					
				$childs = $Structure_Manager->get_all_childs($params['topic_id'], $category_structure);
				if ( count($childs) > 0 ) {
					array_push($childs, $params['topic_id']);
					$query_parts['where'][] = 're_data.topic_id in ('.implode(' , ',$childs).') ';
				} else {
					$query_parts['where'][] = 're_data.topic_id='.$params['topic_id'];
				}
			}elseif(is_array($params['topic_id'])){
				$query_parts['where'][] = 're_data.topic_id IN ('.implode(',', $params['topic_id']).')';
			}
		}
			
			
		if( isset($model['export_cian']) && isset($params['srch_export_cian']) && $params['srch_export_cian']==1){
			$query_parts['where'][] = '`'.$main_table.'`.`export_cian`=1';
		}
			
		if( isset($params['favorites']) && !empty($params['favorites'])){
			$query_parts['where'][] = '`'.$main_table.'`.`id` IN ('.implode(',',$params['favorites']).')';
		}
			
		if( isset($model['uniq_id']) && isset($params['uniq_id']) && $params['uniq_id']!=0){
			$query_parts['where'][] = '`'.$main_table.'`.`uniq_id`='.$params['uniq_id'];
		}
			
		if( isset($model['optype']) && isset($params['optype'])){
			$query_parts['where'][] = '`'.$main_table.'`.`optype`='.(int)$params['optype'];
		}
			
		if ( isset($model['country_id']) && isset($params['country_id']) and $params['country_id'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`country_id` = '.$params['country_id'];
		}else{
			unset($params['country_id']);
		}
			
		/*if ( isset($model['city_id']) && isset($params['city_id']) and $params['city_id'] != 0  ) {
		 $query_parts['where'][] = '`'.$main_table.'`.`city_id` = '.$params['city_id'];
		}else{
		unset($params['city_id']);
		}*/
		
		if ( isset($model['city_id']) && isset($params['city_id']) ) {
			if(is_array($params['city_id']) && !empty($params['city_id'])){
				$query_parts['where'][] = '`'.$main_table.'`.`city_id` IN ('.implode(',', $params['city_id']).')';
			}elseif((int)$params['city_id'] != 0){
				$query_parts['where'][] = '`'.$main_table.'`.`city_id` = '.$params['city_id'];
			}else{
				unset($params['city_id']);
			}
		}else{
			unset($params['city_id']);
		}
		
		if ( isset($model['district_id']) && isset($params['district_id']) ) {
			if(is_array($params['district_id']) && !empty($params['district_id'])){
				$query_parts['where'][] = '`'.$main_table.'`.`district_id` IN ('.implode(',', $params['district_id']).')';
			}elseif((int)$params['district_id'] != 0){
				$query_parts['where'][] = '`'.$main_table.'`.`district_id` = '.$params['district_id'];
			}else{
				unset($params['district_id']);
			}
		}else{
			unset($params['district_id']);
		}
		
		/*if ( isset($model['district_id']) && isset($params['district_id']) and $params['district_id'] != 0  ) {
		 $query_parts['where'][] = '`'.$main_table.'`.`district_id` = '.$params['district_id'];
		}else{
		unset($params['district_id']);
		}*/
		
		if ( isset($model['id']) && isset($params['id']) && $params['id'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`id` = '.$params['id'];
		}
		
		if ( isset($model['user_id']) && isset($params['user_id']) && $params['user_id'] > 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`user_id` = '.$params['user_id'];
		}
		
		if ( isset($model['hot']) && isset($params['onlyspecial']) && $params['onlyspecial'] > 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`hot` = 1';
		}
		
		
		if ( isset($model['price']) && isset($params['price']) && $params['price'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`price`  <= '.$params['price'];
		}
		
		if ( isset($model['price']) && isset($params['price_min']) && $params['price_min'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`price`  >= '.$params['price_min'];
		}
		////
		if ( isset($model['price_pm']) && isset($params['price_pm']) && $params['price_pm'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`price_pm`  <= '.$params['price_pm'];
		}
		if ( isset($model['price_pm']) && isset($params['price_pm_min']) && $params['price_pm_min'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`price_pm`  >= '.$params['price_pm_min'];
		}
		//////
		if ( isset($model['number']) && isset($params['house_number']) && $params['house_number'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`number`  = \''.$params['house_number'].'\'';
		}
		
		
		/*if ( isset($model['region_id']) && isset($params['region_id']) && $params['region_id'] != 0 ) {
		 $query_parts['where'][] = '`'.$main_table.'`.`region_id` = '.$params['region_id'];
		}else{
		unset($params['region_id']);
		}*/
		
		if ( isset($model['region_id']) && isset($params['region_id']) ) {
			if(is_array($params['region_id']) && !empty($params['region_id'])){
				$query_parts['where'][] = '`'.$main_table.'`.`region_id` IN ('.implode(',', $params['region_id']).')';
			}elseif((int)$params['region_id'] != 0){
				$query_parts['where'][] = '`'.$main_table.'`.`region_id` = '.$params['region_id'];
			}else{
				unset($params['region_id']);
			}
		}else{
			unset($params['region_id']);
		}
		
		if ( isset($model['hot']) && isset($params['spec']) ) {
			$query_parts['where'][] = '`'.$main_table.'`.`hot` = 1 ';
		}
		if ( isset($model['hot']) && isset($params['hot']) ) {
			$query_parts['where'][] = '`'.$main_table.'`.`hot` = 1 ';
		}
		
		if ( isset($model['metro_id']) && isset($params['metro_id']) ) {
			if(is_array($params['metro_id']) && !empty($params['metro_id'])){
				$query_parts['where'][] = '`'.$main_table.'`.`metro_id` IN ('.implode(',', $params['metro_id']).')';
			}elseif((int)$params['metro_id'] != 0){
				$query_parts['where'][] = '`'.$main_table.'`.`metro_id` = '.(int)$params['metro_id'];
			}else{
				unset($params['metro_id']);
			}
		}else{
			unset($params['metro_id']);
		}
		
		if ( isset($model['street_id']) && isset($params['street_id']) && $params['street_id'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`street_id` = '.$params['street_id'];
		}else{
			unset($params['street_id']);
		}
		
		if(isset($params['srch_phone']) && $params['srch_phone'] !== NULL){
			$phone = preg_replace('/[^\d]/', '', $params['srch_phone']);
			$sub_where=array();
			if($phone!=''){
				if($this->getConfigValue('allow_additional_mobile_number') && isset($model['ad_mobile_phone'])){
					$sub_where[] = '(`'.$main_table.'`.`ad_mobile_phone` LIKE \'%'.$phone.'%\')';
				}
				if($this->getConfigValue('allow_additional_stationary_number') && isset($model['ad_stacionary_phone'])){
					$sub_where[] = '(`'.$main_table.'`.`ad_stacionary_phone` LIKE \'%'.$phone.'%\')';
				}
				if(isset($model['phone'])){
					$sub_where[] = '(`'.$main_table.'`.`phone` LIKE \'%'.$phone.'%\')';
				}
				if(!empty($sub_where)){
					$query_parts['where'][]='('.implode(' OR ',$sub_where).')';
				}
		
			}
				
		}
		
		if(isset($params['srch_word']) && $params['srch_word'] !== NULL){
			$sub_where=array();
			$word=htmlspecialchars($params['srch_word'], ENT_QUOTES, SITE_ENCODING);
			if(isset($model['text'])){
				$sub_where[] = '(`'.$main_table.'`.`text` LIKE \'%'.$word.'%\')';
			}
			if(isset($model['more1'])){
				$sub_where[] = '(`'.$main_table.'`.`more1` LIKE \'%'.$word.'%\')';
			}
			if(isset($model['more2'])){
				$sub_where[] = '(`'.$main_table.'`.`more2` LIKE \'%'.$word.'%\')';
			}
			if(isset($model['more3'])){
				$sub_where[] = '(`'.$main_table.'`.`more3` LIKE \'%'.$word.'%\')';
			}
			if(!empty($sub_where)){
				$query_parts['where'][]='('.implode(' OR ',$sub_where).')';
			}
				
				
				
				
		}
		
		if(isset($model['room_count']) && isset($params['room_count'])){
			if(is_array($params['room_count']) && count($params['room_count'])>0){
				$sub_where=array();
				foreach($params['room_count'] as $rq){
					if($rq==4){
						$sub_where[]='`'.$main_table.'`.`room_count`>3';
					}elseif(0!=(int)$rq){
						$sub_where[]='`'.$main_table.'`.`room_count`='.(int)$rq;
					}
				}
				if(count($sub_where)>0){
					$query_parts['where'][]='('.implode(' OR ', $sub_where).')';
				}
			}else{
				//unset($params['room_count']);
			}
		}
		
		if(isset($model['date_added'])){
			if($params['srch_date_from']!=0 && $params['srch_date_to']!=0){
				$query_parts['where'][]="((`".$main_table."`.`date_added`>='".$params['srch_date_from']."') AND (`".$main_table."`.`date_added`<='".$params['srch_date_to']."'))";
			}elseif($params['srch_date_from']!=0){
				$query_parts['where'][]="(`".$main_table."`.`date_added`>='".$params['srch_date_from']."')";
			}elseif($params['srch_date_to']!=0){
				$query_parts['where'][]="(`".$main_table."`.`date_added`<='".$params['srch_date_to']."')";
			}
		}
		
		if(isset($model['floor'])){
			if($params['floor_min']!=0 && $params['floor_max']!=0){
				$query_parts['where'][]="(re_data.floor BETWEEN ".$params['floor_min']." AND ".$params['floor_max'].")";
			}elseif($params['floor_min']!=0){
				$query_parts['where'][]="(re_data.floor>=".$params['floor_min'].")";
			}elseif($params['floor_max']!=0){
				$query_parts['where'][]="(re_data.floor<=".$params['floor_max'].")";
			}
				
			if(isset($params['not_first_floor']) && $params['not_first_floor']==1){
				$query_parts['where'][]="(re_data.floor <> 1)";
			}
				
			if(isset($params['not_last_floor']) && $params['not_last_floor']==1){
				$query_parts['where'][]="(re_data.floor <> re_data.floor_count)";
			}
		}
		
		if(isset($model['floor_count'])){
			if($params['floor_count_min']!=0 && $params['floor_count_max']!=0){
				$query_parts['where'][]="(re_data.floor_count BETWEEN ".$params['floor_count_min']." AND ".$params['floor_count_max'].")";
			}elseif($params['floor_count_min']!=0){
				$query_parts['where'][]="(re_data.floor_count>=".$params['floor_count_min'].")";
			}elseif($params['floor_count_max']!=0){
				$query_parts['where'][]="(re_data.floor_count<=".$params['floor_count_max'].")";
			}
		}
		
		if(isset($model['square_all'])){
			if($params['square_min']!=0 && $params['square_max']!=0){
				$query_parts['where'][]="(`".$main_table."`.`square_all` BETWEEN ".$params['square_min']." AND ".$params['square_max'].")";
			}elseif($params['square_min']!=0){
				$query_parts['where'][]="(`".$main_table."`.`square_all`>=".$params['square_min'].")";
			}elseif($params['square_max']!=0){
				$query_parts['where'][]="(`".$main_table."`.`square_all`<=".$params['square_max'].")";
			}
		}
		
		if(isset($model['square_live'])){
			if(isset($params['live_square_min']) && $params['live_square_min']!=0){
				$query_parts['where'][]="(`".$main_table."`.`square_live` >= ".$params['live_square_min'].")";
			}
			if(isset($params['live_square_max']) && $params['live_square_max']!=0){
				$query_parts['where'][]="(`".$main_table."`.`square_live` <= ".$params['live_square_max'].")";
			}
		}
		if(isset($model['square_kitchen'])){
			if(isset($params['kitchen_square_min']) && $params['kitchen_square_min']!=0){
				$query_parts['where'][]="(`".$main_table."`.`square_kitchen` >= ".$params['kitchen_square_min'].")";
			}
			if(isset($params['kitchen_square_max']) && $params['kitchen_square_max']!=0){
				$query_parts['where'][]="(`".$main_table."`.`square_kitchen` <= ".$params['kitchen_square_max'].")";
			}
		}
		
		if(isset($model['is_telephone']) && isset($params['is_phone']) && $params['is_phone']==1){
			$query_parts['where'][]="(`".$main_table."`.`is_telephone`=1)";
		}else{
			unset($params['is_phone']);
		}
		
		if(isset($model['is_internet']) && isset($params['is_internet']) && $params['is_internet']==1){
			$query_parts['where'][]="(`".$main_table."`.`is_internet`=1)";
		}else{
			unset($params['is_internet']);
		}
		
		if(isset($model['furniture']) && isset($params['is_furniture']) && $params['is_furniture']==1){
			$query_parts['where'][]="(`".$main_table."`.`furniture`=1)";
		}else{
			unset($params['is_furniture']);
		}
		
		if(isset($model['whoyuaare']) && isset($params['owner']) && $params['owner']==1){
			$query_parts['where'][]="(`".$main_table."`.`whoyuaare`=1)";
		}else{
			unset($params['owner']);
		}
		
		////////////////////
		if(isset($model['image']) && $params['has_photo']==1){
			$query_parts['where'][]='((SELECT COUNT(*) FROM '.DB_PREFIX.'_data_image WHERE `id`='.DB_PREFIX.'_data.id)>0)';
		}else{
			unset($params['has_photo']);
		}
		///////////////////
		
		if(isset($model['infra_greenzone']) && $params['infra_greenzone']==1){
			$query_parts['where'][]="(`".$main_table."`.`infra_greenzone`=1)";
		}else{
			unset($params['infra_greenzone']);
		}
		
		if(isset($model['infra_sea']) && $params['infra_sea']==1){
			$query_parts['where'][]="(`".$main_table."`.`infra_sea`=1)";
		}else{
			unset($params['infra_sea']);
		}
		
		if(isset($model['infra_sport']) && $params['infra_sport']==1){
			$query_parts['where'][]="(`".$main_table."`.`infra_sport`=1)";
		}else{
			unset($params['infra_sport']);
		}
		
		if(isset($model['infra_clinic']) && $params['infra_clinic']==1){
			$query_parts['where'][]="(`".$main_table."`.`infra_clinic`=1)";
		}else{
			unset($params['infra_clinic']);
		}
		
		if(isset($model['infra_terminal']) && $params['infra_terminal']==1){
			$query_parts['where'][]="(`".$main_table."`.`infra_terminal`=1)";
		}else{
			unset($params['infra_terminal']);
		}
		
		if(isset($model['infra_airport']) && $params['infra_airport']==1){
			$query_parts['where'][]="(`".$main_table."`.`infra_airport`=1)";
		}else{
			unset($params['infra_airport']);
		}
		
		if(isset($model['infra_bank']) && $params['infra_bank']==1){
			$query_parts['where'][]="(`".$main_table."`.`infra_bank`=1)";
		}else{
			unset($params['infra_bank']);
		}
		
		if(isset($model['infra_restaurant']) && $params['infra_restaurant']==1){
			$query_parts['where'][]="(`".$main_table."`.`infra_restaurant`=1)";
		}else{
			unset($params['infra_restaurant']);
		}
		
		
		if(isset($model['object_state']) && isset($params['object_state']) && is_array($params['object_state']) && count($params['object_state'])>0){
			$query_parts['where'][]="(`".$main_table."`.`object_state` IN (".implode(',', $params['object_state'])."))";
		}else{
			unset($params['object_state']);
		}
		
		if(isset($model['object_type']) && isset($params['object_type']) && is_array($params['object_type']) && count($params['object_type'])>0){
			$query_parts['where'][]="(`".$main_table."`.`object_type` IN (".implode(',', $params['object_type'])."))";
		}else{
			unset($params['object_type']);
		}
		
		if(isset($model['object_destination']) && isset($params['object_destination']) && is_array($params['object_destination']) && count($params['object_destination'])>0){
			$query_parts['where'][]="(`".$main_table."`.`object_destination` IN (".implode(',', $params['object_destination'])."))";
		}else{
			unset($params['object_destination']);
		}
		
		if(isset($model['aim']) && isset($params['aim']) && is_array($params['aim']) && count($params['aim'])>0){
			$query_parts['where'][]="(`".$main_table."`.`aim` IN (".implode(',', $params['aim'])."))";
		}else{
			unset($params['aim']);
		}
		
		if(isset($model['geo']) && $params['has_geo']==1){
			$query_parts['where'][]='(`'.$main_table.'`.`geo_lat` IS NOT NULL AND `'.$main_table.'`.`geo_lng` IS NOT NULL)';
		}
		
		if ( $params['admin'] != 1 ) {
			$query_parts['where'][] = '`'.$main_table.'`.`active`=1';
		} elseif ( $params['active'] == 1 ) {
			$query_parts['where'][] = '`'.$main_table.'`.`active`=1';
		} elseif ( $params['active'] == 'notactive' ) {
			$query_parts['where'][] = '`'.$main_table.'`.`active`=0';
		}
		
		if(isset($model['bedrooms_count']) && isset($params['minbeds']) && (int)$params['minbeds']!=0){
			$query_parts['where'][]="(`".$main_table."`.`bedrooms_count` >= ".(int)$params['minbeds'].")";
		}
		if(isset($model['bathrooms_count']) && isset($params['minbaths']) && (int)$params['minbaths']!=0){
			$query_parts['where'][]="(`".$main_table."`.`bathrooms_count` >=".(int)$params['minbaths'].")";
		}
		
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/template_search.php')){
			require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/template_search.php');
			$Template_Search=new Template_Search();
			$results=$Template_Search->run();
			
			if(isset($results['where'])){
				$query_parts['where']=array_merge($query_parts['where'], $results['where']);
			}
			if(isset($results['params'])){
				$params=array_merge($params, $results['params']);
			}
		}
		
		
		
		
		
		if ( !isset($params['page']) || (int)$params['page'] == 0 ) {
			$page = 1;
		} else {
			$page = (int)$params['page'];
		}
		
		$limit = $this->getConfigValue('per_page');
		
		//$smarty->assign('_max_page',$max_page);
		
		/*if($page>$max_page){
		 $page=1;
		$params['page']=1;
		}*/
		
		$start = ($page-1)*$limit;
		
		$query='SELECT SQL_CALC_FOUND_ROWS '.$select_query['what'].'
    			FROM `'.$main_table.'` '.$select_query['left_join'].'
    			'.(!empty($query_parts['where']) ? 'WHERE '.implode(' AND ', $query_parts['where']) : '').' '.$order.' LIMIT '.$start.', '.$limit;
		//echo $query;
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$adv[]=$ar;
			}
		}
		
		$query='SELECT FOUND_ROWS() as fr';
		$stmt=$DBC->query($query);
		if($stmt){
			$ar=$DBC->fetch($stmt);
			$total_count=$ar['fr'];
		}else{
			$total_count=0;
		}
		
		$max_page=ceil($total_count/$limit);
		
		
		if($page>$max_page){
			$page=1;
			$params['page']=1;
			$start = ($page-1)*$limit;
				
			$query='SELECT SQL_CALC_FOUND_ROWS '.$select_query['what'].'
    			FROM `'.$main_table.'` '.$select_query['left_join'].'
    			'.(!empty($query_parts['where']) ? 'WHERE '.implode(' AND ', $query_parts['where']) : '').' '.$order.' LIMIT '.$start.', '.$limit;
				
			$DBC=DBC::getInstance();
			$stmt=$DBC->query($query);
			if($stmt){
				while($ar=$DBC->fetch($stmt)){
					$adv[]=$ar;
				}
			}
		}
		
		
		$pager_params=$params;
			
			
		unset($params['order']);
		unset($params['asc']);
		unset($params['favorites']);
		
		if(isset($model['topic_id'])){
			if(!is_array($params['topic_id']) && $params['topic_id']!=''){
				if(!$params['admin']){
					if($this->cityTopicUrlFind($_SERVER['REQUEST_URI'])){
						$p=parse_url($_SERVER['REQUEST_URI']);
						unset($params['city_id']);
						unset($params['topic_id']);
						unset($pager_params['city_id']);
						unset($pager_params['topic_id']);
						$pageurl=trim($p['path'],'/');
					}elseif($category_structure['catalog'][$params['topic_id']]['url']!='' && 1==$this->getConfigValue('apps.seo.level_enable')){
						$pageurl=$category_structure['catalog'][$params['topic_id']]['url'];
						//unset($pager_params['topic_id']);
						unset($params['topic_id']);
						unset($pager_params['topic_id']);
					}elseif($category_structure['catalog'][$params['topic_id']]['url']!=''){
						//echo 1;
						$pageurl=$category_structure['catalog'][$params['topic_id']]['url'];
						unset($pager_params['topic_id']);
						unset($params['topic_id']);
					}else{
						if(preg_match('/topic(\d*).html/',$_SERVER['REQUEST_URI'])){
							unset($pager_params['topic_id']);
						}
						if($params['topic_id']!=0){
							$pageurl='topic'.$params['topic_id'].'.html';
							unset($params['topic_id']);
						}else{
							$pageurl='';
							unset($params['topic_id']);
							unset($pager_params['topic_id']);
						}
							
					}
				}else{
					$pageurl='';
				}
			}else{
				$pageurl='';
			}
		}
		
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
		$Structure_Manager = new Structure_Manager();
		$category_structure = $Structure_Manager->loadCategoryStructure();
		
		$grid_geodata=array();
		//print_r($model);
		
		//print_r($fields_array);
		
		
		foreach($adv as $k=>$ad){
			
			//print_r($ad);
			
			global $smarty;
			
			
			
			
			
			if(1==$this->getConfigValue('apps.seo.level_enable')){
					
				if($category_structure['catalog'][$ad['topic_id_orig']]['url']!=''){
					$adv[$k]['parent_category_url']=$category_structure['catalog'][$ad['topic_id_orig']]['url'].'/';
				}else{
					$adv[$k]['parent_category_url']='';
				}
			}else{
				$adv[$k]['parent_category_url']='';
			}
				
			if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $ad['translit_alias']!=''){
				$adv[$k]['href']=SITEBILL_MAIN_URL.'/'.$adv[$k]['parent_category_url'].$ad['translit_alias'];
			}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
				$adv[$k]['href']=SITEBILL_MAIN_URL.'/'.$adv[$k]['parent_category_url'].'realty'.$ad['id'].'.html';
			}else{
				$adv[$k]['href']=SITEBILL_MAIN_URL.'/'.$adv[$k]['parent_category_url'].'realty'.$ad['id'];
			}
			
			
			
			if( isset($ad['geo_lat']) && isset($ad['geo_lng']) && $ad['geo_lat']!='' && $ad['geo_lng']!='' ){
				
				$smarty->assign('realty',$adv[$k]);
				$html=$smarty->fetch('realty_on_map.tpl');
				$html = str_replace("\r\n", ' ', $html);
				$html = str_replace("\n", ' ', $html);
				$html = str_replace("\t", ' ', $html);
				$html = addslashes($html);
				
				$grid_geodata[]=array(
						'lat'=>$ad['geo_lat'],
						'lng'=>$ad['geo_lng'],
						'id'=>$ad['id'],
						'html'=>SiteBill::iconv(SITE_ENCODING, 'utf-8', $html)
				);
				
				
			}
		}
		
		$adv_=array();
		$adv_=$this->prepareData($adv, $fields_array, $model);
		/*
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/grid/front_grid_local.php')){
			require_once SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/grid/front_grid_local.php';
			$adv_=Front_Grid_Local::prepareData($adv, $fields_array, $model);
		}else{
			foreach($adv as $k=>$ad){
				$p=array();
				foreach($fields_array as $f=>$ff){
					if(isset($ff['attached']) && !empty($ff['attached'])){
						$attached_parts=array();
						if($model[$f]['type']=='select_box'){
							$attached_parts[]=$model[$f]['select_data'][$ad[$f]];
						}else{
							$attached_parts[]=$ad[$f];
						}
							
						$separator=$ff['separator'];
						foreach($ff['attached'] as $attached){
			
							if($model[$attached['name']]['type']=='select_box'){
								//print_r($model[$f]);
								$attached_parts[]=$model[$f]['select_data'][$ad[$attached['name']]];
							}else{
								if($ad[$attached['name']]!=''){
									$attached_parts[]=$ad[$attached['name']];
								}
							}
			
			
			
			
						}
						$p[$f]=implode($separator, $attached_parts);
					}else{
						if($model[$f]['type']=='select_box'){
							//print_r($model[$f]);
							$p[$f]=$model[$f]['select_data'][$ad[$f]];
						}else{
							$p[$f]=$ad[$f];
						}
							
					}
				}
				$p['href']=$ad['href'];
				$adv_[]=$p;
			}
		}
		*/
		
		
		
		
		
		//print_r($adv_);
		
		$pager_params['page_url']=$pageurl;
		
		foreach ( $params as $key => $value ) {
			if(is_array($value)){
				if(count($value)>0){
					foreach($value as $v){
						if($v!=''){
							$pairs[] = $key.'[]='.$v;
						}
					}
				}
			}elseif ( $value != '') {
				if($key!='topic_id'){
					$pairs[] = "$key=$value";
				}elseif($params['admin']){
					$pairs[] = "$key=$value";
				}
		
			}
		}
		
		if ( is_array($pairs) ) {
			$url = $pageurl.'?'.implode('&', $pairs);
		}else{
			$url = $pageurl.'?key=value';
		}
		$this->template->assert('url', $url);
		
		$this->template->assert('grid_geodata', json_encode($grid_geodata));
		
		$this->template->assert('pager', $this->get_page_links_list ($page, $total_count, $limit, $pager_params ));
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_front_grid.tpl')){
			$this->template->assign('main_file_tpl', 'realty_front_grid.tpl');
		}else{
			$inc_file=SITEBILL_DOCUMENT_ROOT.'/apps/system/template/realty_front_grid.tpl';
			$this->template->assign('main_file_tpl', $inc_file);
		}
		
		
		$this->template->assign('grid_items', $adv_);
		$this->template->assign('grid_header', $fields_array);
		return $ret;
	}
	
	public function generate($model, $fields_array, $params){
		
		$adv=array();
		$primary_key='';
		$grid_head_names=array();
		$grid_head_ids=array();
		
		foreach($fields_array as $f){
			$fields[]=$f['name'];
			$grid_head_names[$f['name']]=$f['title'];
		}
		
		
		$ks=array_keys($model);
		$main_table=$ks[0];
		$model=$model[$main_table];
		
		$main_table=DB_PREFIX.'_'.$main_table;
		
		$this->buildSelectQuery($model, $main_table);
		
		$query_parts=array();
		$ret=array();
		
		
		foreach($model as $m){
			if($m['type']=='primary_key'){
				$query_parts['select_what'][]='`'.$main_table.'`.`'.$m['name'].'`';
				break;
			}
		}
		
		if(isset($model['translit_alias'])){
			$query_parts['select_what'][]='`'.$main_table.'`.`translit_alias`';
		}
		
		
		
		
		
		
		
		 
		foreach($fields_array as $f=>$ff){
			
			//echo $model[$f]['title'];
			if(isset($model[$f])){
				if($ff['title']==''){
					$fields_array[$f]['title']=$model[$f]['title'];
					$fields_array[$f]['type']=$model[$f]['type'];
				}
				
				$grid_head_ids[$f]=$f;
				//print_r($model[$f]);
				//$grid_head_ids[$f]=$f;
				
				if(isset($ff['attached']) && !empty($ff['attached'])){
					$attached_query_parts=array();
					
					
					
					//print_r($p);
					foreach($ff['attached'] as $attached){
						//print_r($attached);
						if(isset($model[$attached['name']])){
							$ap=$this->compileModelElement($model[$attached['name']], $main_table, true);
							
							foreach($ap['select_what'] as $p1){
								$attached_query_parts['select_what'][]=$p1;
							}
							foreach($ap['left_join'] as $p1){
								$attached_query_parts['left_join'][]=$p1;
							}
							foreach($ap['select_from'] as $p1){
								$attached_query_parts['select_from'][]=$p1;
							}
							foreach($ap['select_what_attached'] as $p1){
								$attached_query_parts['select_what_attached'][]=$p1;
							}
						}
					}
					
					
					$p=$this->compileModelElement($model[$f], $main_table, true);
					/*foreach($p['select_what'] as $p1){
					 $attached_query_parts['select_what'][]=$p1;
					}*/
					foreach($p['left_join'] as $p1){
						$attached_query_parts['left_join'][]=$p1;
					}
					foreach($p['select_from'] as $p1){
						$attached_query_parts['select_from'][]=$p1;
					}
					foreach($p['select_what_attached'] as $p1){
						//$attached_query_parts['select_what_attached'][]=$p1;
						array_unshift($attached_query_parts['select_what_attached'], $p1);
					}
					
					if(!empty($attached_query_parts['select_what_attached'])){
						
						$attached_query_parts['select_what'][]='CONCAT_WS(\''.$ff['separator'].'\','.implode(',',$attached_query_parts['select_what_attached']).') AS '.$ff['name'];
					}
					if(isset($attached_query_parts['select_what']) && !empty($attached_query_parts['select_what'])){
						foreach($attached_query_parts['select_what'] as $p1){
							$query_parts['select_what'][]=$p1;
						}
					}
					if(isset($attached_query_parts['left_join']) && !empty($attached_query_parts['left_join'])){
						foreach($attached_query_parts['left_join'] as $p1){
							$query_parts['left_join'][]=$p1;
						}
					}
					if(isset($attached_query_parts['select_from']) && !empty($attached_query_parts['select_from'])){
						foreach($attached_query_parts['select_from'] as $p1){
							$query_parts['select_from'][]=$p1;
						}
					}
					
					
					
					
					
					
					//$total_parts=array();
					//print_r($attached_query_parts);
				}else{
					$p=$this->compileModelElement($model[$f], $main_table);
					foreach($p['select_what'] as $p1){
						$query_parts['select_what'][]=$p1;
					}
					foreach($p['left_join'] as $p1){
						$query_parts['left_join'][]=$p1;
					}
					foreach($p['select_from'] as $p1){
						$query_parts['select_from'][]=$p1;
					}
				}
				
				
				
				 
			}else{
				
			}
		}
		
		
		
		if(isset($params['order']) && $params['order']!='' && in_array($params['order'], $grid_head_ids)){
			$order='ORDER BY '.$params['order'];
		}else{
			$order='ORDER BY price';
		}
		
		if(isset($params['asc']) && in_array($params['asc'], array('asc', 'desc'))){
			$order.=' '.$params['asc'];
		}else{
			$order.=' DESC';
		}
		
		if(isset($model['topic_id'])){
			if ( !is_array($params['topic_id']) && $params['topic_id'] != '' &&  $params['topic_id'] != 0) {
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
				$Structure_Manager = new Structure_Manager();
				$category_structure = $Structure_Manager->loadCategoryStructure();
				global $smarty;
				//echo $category_structure['catalog'][$params['topic_id']]['description'];
				$smarty->assign('topic_description', $category_structure['catalog'][$params['topic_id']]['description']);
			
				$childs = $Structure_Manager->get_all_childs($params['topic_id'], $category_structure);
				if ( count($childs) > 0 ) {
					array_push($childs, $params['topic_id']);
					$query_parts['where'][] = 're_data.topic_id in ('.implode(' , ',$childs).') ';
				} else {
					$query_parts['where'][] = 're_data.topic_id='.$params['topic_id'];
				}
			}elseif(is_array($params['topic_id'])){
				$query_parts['where'][] = 're_data.topic_id IN ('.implode(',', $params['topic_id']).')';
			}
		}
		 
		 
		if( isset($model['export_cian']) && isset($params['srch_export_cian']) && $params['srch_export_cian']==1){
			$query_parts['where'][] = '`'.$main_table.'`.`export_cian`=1';
		}
		 
		if( isset($params['favorites']) && !empty($params['favorites'])){
			$query_parts['where'][] = '`'.$main_table.'`.`id` IN ('.implode(',',$params['favorites']).')';
		}
		 
		if( isset($model['uniq_id']) && isset($params['uniq_id']) && $params['uniq_id']!=0){
			$query_parts['where'][] = '`'.$main_table.'`.`uniq_id`='.$params['uniq_id'];
		}
		 
		if( isset($model['optype']) && isset($params['optype'])){
			$query_parts['where'][] = '`'.$main_table.'`.`optype`='.(int)$params['optype'];
		}
		 
		if ( isset($model['country_id']) && isset($params['country_id']) and $params['country_id'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`country_id` = '.$params['country_id'];
		}else{
			unset($params['country_id']);
		}
		 
		/*if ( isset($model['city_id']) && isset($params['city_id']) and $params['city_id'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`city_id` = '.$params['city_id'];
		}else{
			unset($params['city_id']);
		}*/
		
		if ( isset($model['city_id']) && isset($params['city_id']) ) {
			if(is_array($params['city_id']) && !empty($params['city_id'])){
				$query_parts['where'][] = '`'.$main_table.'`.`city_id` IN ('.implode(',', $params['city_id']).')';
			}elseif((int)$params['city_id'] != 0){
				$query_parts['where'][] = '`'.$main_table.'`.`city_id` = '.$params['city_id'];
			}else{
				unset($params['city_id']);
			}
		}else{
			unset($params['city_id']);
		}
		
		if ( isset($model['district_id']) && isset($params['district_id']) ) {
			if(is_array($params['district_id']) && !empty($params['district_id'])){
				$query_parts['where'][] = '`'.$main_table.'`.`district_id` IN ('.implode(',', $params['district_id']).')';
			}elseif((int)$params['district_id'] != 0){
				$query_parts['where'][] = '`'.$main_table.'`.`district_id` = '.$params['district_id'];
			}else{
				unset($params['district_id']);
			}
		}else{
			unset($params['district_id']);
		}
		
		/*if ( isset($model['district_id']) && isset($params['district_id']) and $params['district_id'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`district_id` = '.$params['district_id'];
		}else{
			unset($params['district_id']);
		}*/
		
		if ( isset($model['id']) && isset($params['id']) && $params['id'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`id` = '.$params['id'];
		}
		
		if ( isset($model['user_id']) && isset($params['user_id']) && $params['user_id'] > 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`user_id` = '.$params['user_id'];
		}
		
		if ( isset($model['hot']) && isset($params['onlyspecial']) && $params['onlyspecial'] > 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`hot` = 1';
		}
		
		
		if ( isset($model['price']) && isset($params['price']) && $params['price'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`price`  <= '.$params['price'];
		}
		
		if ( isset($model['price']) && isset($params['price_min']) && $params['price_min'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`price`  >= '.$params['price_min'];
		}
		////
		if ( isset($model['price_pm']) && isset($params['price_pm']) && $params['price_pm'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`price_pm`  <= '.$params['price_pm'];
		}
		if ( isset($model['price_pm']) && isset($params['price_pm_min']) && $params['price_pm_min'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`price_pm`  >= '.$params['price_pm_min'];
		}
		//////
		if ( isset($model['number']) && isset($params['house_number']) && $params['house_number'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`number`  = \''.$params['house_number'].'\'';
		}
		
		
		/*if ( isset($model['region_id']) && isset($params['region_id']) && $params['region_id'] != 0 ) {
			$query_parts['where'][] = '`'.$main_table.'`.`region_id` = '.$params['region_id'];
		}else{
			unset($params['region_id']);
		}*/
		
		if ( isset($model['region_id']) && isset($params['region_id']) ) {
			if(is_array($params['region_id']) && !empty($params['region_id'])){
				$query_parts['where'][] = '`'.$main_table.'`.`region_id` IN ('.implode(',', $params['region_id']).')';
			}elseif((int)$params['region_id'] != 0){
				$query_parts['where'][] = '`'.$main_table.'`.`region_id` = '.$params['region_id'];
			}else{
				unset($params['region_id']);
			}
		}else{
			unset($params['region_id']);
		}
		
		if ( isset($model['hot']) && isset($params['spec']) ) {
			$query_parts['where'][] = '`'.$main_table.'`.`hot` = 1 ';
		}
		if ( isset($model['hot']) && isset($params['hot']) ) {
			$query_parts['where'][] = '`'.$main_table.'`.`hot` = 1 ';
		}
		
		if ( isset($model['metro_id']) && isset($params['metro_id']) ) {
			if(is_array($params['metro_id']) && !empty($params['metro_id'])){
				$query_parts['where'][] = '`'.$main_table.'`.`metro_id` IN ('.implode(',', $params['metro_id']).')';
			}elseif((int)$params['metro_id'] != 0){
				$query_parts['where'][] = '`'.$main_table.'`.`metro_id` = '.(int)$params['metro_id'];
			}else{
				unset($params['metro_id']);
			}
		}else{
			unset($params['metro_id']);
		}
		
		if ( isset($model['street_id']) && isset($params['street_id']) && $params['street_id'] != 0  ) {
			$query_parts['where'][] = '`'.$main_table.'`.`street_id` = '.$params['street_id'];
		}else{
			unset($params['street_id']);
		}
		
		if(isset($params['srch_phone']) && $params['srch_phone'] !== NULL){
			$phone = preg_replace('/[^\d]/', '', $params['srch_phone']);
			$sub_where=array();
			if($phone!=''){
				if($this->getConfigValue('allow_additional_mobile_number') && isset($model['ad_mobile_phone'])){
					$sub_where[] = '(`'.$main_table.'`.`ad_mobile_phone` LIKE \'%'.$phone.'%\')';
				}
				if($this->getConfigValue('allow_additional_stationary_number') && isset($model['ad_stacionary_phone'])){
					$sub_where[] = '(`'.$main_table.'`.`ad_stacionary_phone` LIKE \'%'.$phone.'%\')';
				}
				if(isset($model['phone'])){
					$sub_where[] = '(`'.$main_table.'`.`phone` LIKE \'%'.$phone.'%\')';
				}
				if(!empty($sub_where)){
					$query_parts['where'][]='('.implode(' OR ',$sub_where).')';
				}
				
			}
			
		}
		
		if(isset($params['srch_word']) && $params['srch_word'] !== NULL){
			$sub_where=array();
			$word=htmlspecialchars($params['srch_word'], ENT_QUOTES, SITE_ENCODING);
			if(isset($model['text'])){
				$sub_where[] = '(`'.$main_table.'`.`text` LIKE \'%'.$word.'%\')';
			}
			if(isset($model['more1'])){
				$sub_where[] = '(`'.$main_table.'`.`more1` LIKE \'%'.$word.'%\')';
			}
			if(isset($model['more2'])){
				$sub_where[] = '(`'.$main_table.'`.`more2` LIKE \'%'.$word.'%\')';
			}
			if(isset($model['more3'])){
				$sub_where[] = '(`'.$main_table.'`.`more3` LIKE \'%'.$word.'%\')';
			}
			if(!empty($sub_where)){
				$query_parts['where'][]='('.implode(' OR ',$sub_where).')';
			}
			
			
			
			
		}
		
		if(isset($model['room_count']) && isset($params['room_count'])){
			if(is_array($params['room_count']) && count($params['room_count'])>0){
				$sub_where=array();
				foreach($params['room_count'] as $rq){
					if($rq==4){
						$sub_where[]='`'.$main_table.'`.`room_count`>3';
					}elseif(0!=(int)$rq){
						$sub_where[]='`'.$main_table.'`.`room_count`='.(int)$rq;
					}
				}
				if(count($sub_where)>0){
					$query_parts['where'][]='('.implode(' OR ', $sub_where).')';
				}
			}else{
				//unset($params['room_count']);
			}
		}
		
		if(isset($model['date_added'])){
			if($params['srch_date_from']!=0 && $params['srch_date_to']!=0){
				$query_parts['where'][]="((`".$main_table."`.`date_added`>='".$params['srch_date_from']."') AND (`".$main_table."`.`date_added`<='".$params['srch_date_to']."'))";
			}elseif($params['srch_date_from']!=0){
				$query_parts['where'][]="(`".$main_table."`.`date_added`>='".$params['srch_date_from']."')";
			}elseif($params['srch_date_to']!=0){
				$query_parts['where'][]="(`".$main_table."`.`date_added`<='".$params['srch_date_to']."')";
			}
		}
		
		if(isset($model['floor'])){
			if($params['floor_min']!=0 && $params['floor_max']!=0){
				$query_parts['where'][]="(re_data.floor BETWEEN ".$params['floor_min']." AND ".$params['floor_max'].")";
			}elseif($params['floor_min']!=0){
				$query_parts['where'][]="(re_data.floor>=".$params['floor_min'].")";
			}elseif($params['floor_max']!=0){
				$query_parts['where'][]="(re_data.floor<=".$params['floor_max'].")";
			}
			
			if(isset($params['not_first_floor']) && $params['not_first_floor']==1){
				$query_parts['where'][]="(re_data.floor <> 1)";
			}
			
			if(isset($params['not_last_floor']) && $params['not_last_floor']==1){
				$query_parts['where'][]="(re_data.floor <> re_data.floor_count)";
			}
		}
		
		if(isset($model['floor_count'])){
			if($params['floor_count_min']!=0 && $params['floor_count_max']!=0){
				$query_parts['where'][]="(re_data.floor_count BETWEEN ".$params['floor_count_min']." AND ".$params['floor_count_max'].")";
			}elseif($params['floor_count_min']!=0){
				$query_parts['where'][]="(re_data.floor_count>=".$params['floor_count_min'].")";
			}elseif($params['floor_count_max']!=0){
				$query_parts['where'][]="(re_data.floor_count<=".$params['floor_count_max'].")";
			}
		}
		
		if(isset($model['square_all'])){
			if($params['square_min']!=0 && $params['square_max']!=0){
				$query_parts['where'][]="(`".$main_table."`.`square_all` BETWEEN ".$params['square_min']." AND ".$params['square_max'].")";
			}elseif($params['square_min']!=0){
				$query_parts['where'][]="(`".$main_table."`.`square_all`>=".$params['square_min'].")";
			}elseif($params['square_max']!=0){
				$query_parts['where'][]="(`".$main_table."`.`square_all`<=".$params['square_max'].")";
			}
		}
		
		if(isset($model['square_live'])){
			if(isset($params['live_square_min']) && $params['live_square_min']!=0){
				$query_parts['where'][]="(`".$main_table."`.`square_live` >= ".$params['live_square_min'].")";
			}
			if(isset($params['live_square_max']) && $params['live_square_max']!=0){
				$query_parts['where'][]="(`".$main_table."`.`square_live` <= ".$params['live_square_max'].")";
			}
		}
		if(isset($model['square_kitchen'])){
			if(isset($params['kitchen_square_min']) && $params['kitchen_square_min']!=0){
				$query_parts['where'][]="(`".$main_table."`.`square_kitchen` >= ".$params['kitchen_square_min'].")";
			}
			if(isset($params['kitchen_square_max']) && $params['kitchen_square_max']!=0){
				$query_parts['where'][]="(`".$main_table."`.`square_kitchen` <= ".$params['kitchen_square_max'].")";
			}
		}
		
		if(isset($model['is_telephone']) && isset($params['is_phone']) && $params['is_phone']==1){
			$query_parts['where'][]="(`".$main_table."`.`is_telephone`=1)";
		}else{
			unset($params['is_phone']);
		}
		
		if(isset($model['is_internet']) && isset($params['is_internet']) && $params['is_internet']==1){
			$query_parts['where'][]="(`".$main_table."`.`is_internet`=1)";
		}else{
			unset($params['is_internet']);
		}
		
		if(isset($model['furniture']) && isset($params['is_furniture']) && $params['is_furniture']==1){
			$query_parts['where'][]="(`".$main_table."`.`furniture`=1)";
		}else{
			unset($params['is_furniture']);
		}
		
		if(isset($model['whoyuaare']) && isset($params['owner']) && $params['owner']==1){
			$query_parts['where'][]="(`".$main_table."`.`whoyuaare`=1)";
		}else{
			unset($params['owner']);
		}
		
		////////////////////
		if(isset($model['image']) && $params['has_photo']==1){
			$query_parts['where'][]='((SELECT COUNT(*) FROM '.DB_PREFIX.'_data_image WHERE `id`='.DB_PREFIX.'_data.id)>0)';
		}else{
			unset($params['has_photo']);
		}
		///////////////////
		
		if(isset($model['infra_greenzone']) && $params['infra_greenzone']==1){
			$query_parts['where'][]="(`".$main_table."`.`infra_greenzone`=1)";
		}else{
			unset($params['infra_greenzone']);
		}
		
		if(isset($model['infra_sea']) && $params['infra_sea']==1){
			$query_parts['where'][]="(`".$main_table."`.`infra_sea`=1)";
		}else{
			unset($params['infra_sea']);
		}
		
		if(isset($model['infra_sport']) && $params['infra_sport']==1){
			$query_parts['where'][]="(`".$main_table."`.`infra_sport`=1)";
		}else{
			unset($params['infra_sport']);
		}
		
		if(isset($model['infra_clinic']) && $params['infra_clinic']==1){
			$query_parts['where'][]="(`".$main_table."`.`infra_clinic`=1)";
		}else{
			unset($params['infra_clinic']);
		}
		
		if(isset($model['infra_terminal']) && $params['infra_terminal']==1){
			$query_parts['where'][]="(`".$main_table."`.`infra_terminal`=1)";
		}else{
			unset($params['infra_terminal']);
		}
		
		if(isset($model['infra_airport']) && $params['infra_airport']==1){
			$query_parts['where'][]="(`".$main_table."`.`infra_airport`=1)";
		}else{
			unset($params['infra_airport']);
		}
		
		if(isset($model['infra_bank']) && $params['infra_bank']==1){
			$query_parts['where'][]="(`".$main_table."`.`infra_bank`=1)";
		}else{
			unset($params['infra_bank']);
		}
		
		if(isset($model['infra_restaurant']) && $params['infra_restaurant']==1){
			$query_parts['where'][]="(`".$main_table."`.`infra_restaurant`=1)";
		}else{
			unset($params['infra_restaurant']);
		}
		
		
		if(isset($model['object_state']) && isset($params['object_state']) && is_array($params['object_state']) && count($params['object_state'])>0){
			$query_parts['where'][]="(`".$main_table."`.`object_state` IN (".implode(',', $params['object_state'])."))";
		}else{
			unset($params['object_state']);
		}
		
		if(isset($model['object_type']) && isset($params['object_type']) && is_array($params['object_type']) && count($params['object_type'])>0){
			$query_parts['where'][]="(`".$main_table."`.`object_type` IN (".implode(',', $params['object_type'])."))";
		}else{
			unset($params['object_type']);
		}
		
		if(isset($model['object_destination']) && isset($params['object_destination']) && is_array($params['object_destination']) && count($params['object_destination'])>0){
			$query_parts['where'][]="(`".$main_table."`.`object_destination` IN (".implode(',', $params['object_destination'])."))";
		}else{
			unset($params['object_destination']);
		}
		
		if(isset($model['aim']) && isset($params['aim']) && is_array($params['aim']) && count($params['aim'])>0){
			$query_parts['where'][]="(`".$main_table."`.`aim` IN (".implode(',', $params['aim'])."))";
		}else{
			unset($params['aim']);
		}
		
		if(isset($model['geo']) && $params['has_geo']==1){
			$query_parts['where'][]='(`'.$main_table.'`.`geo_lat` IS NOT NULL AND `'.$main_table.'`.`geo_lng` IS NOT NULL)';
		}
		
		if ( $params['admin'] != 1 ) {
			$query_parts['where'][] = '`'.$main_table.'`.`active`=1';
		} elseif ( $params['active'] == 1 ) {
			$query_parts['where'][] = '`'.$main_table.'`.`active`=1';
		} elseif ( $params['active'] == 'notactive' ) {
			$query_parts['where'][] = '`'.$main_table.'`.`active`=0';
		}
		
		if(isset($model['bedrooms_count']) && isset($params['minbeds']) && (int)$params['minbeds']!=0){
			$query_parts['where'][]="(`".$main_table."`.`bedrooms_count` >= ".(int)$params['minbeds'].")";
		}
		if(isset($model['bathrooms_count']) && isset($params['minbaths']) && (int)$params['minbaths']!=0){
			$query_parts['where'][]="(`".$main_table."`.`bathrooms_count` >=".(int)$params['minbaths'].")";
		}
		
		
		
		
		
		
		
		
		if ( !isset($params['page']) || (int)$params['page'] == 0 ) {
			$page = 1;
		} else {
			$page = (int)$params['page'];
		}
		
		$limit = $this->getConfigValue('per_page');
		
		//$smarty->assign('_max_page',$max_page);
		
		/*if($page>$max_page){
			$page=1;
			$params['page']=1;
		}*/
		
		$start = ($page-1)*$limit;
		
		
		 
		 
		$query='SELECT SQL_CALC_FOUND_ROWS '.implode(', ', $query_parts['select_what']).'
    			FROM `'.$main_table.'` '.(count($query_parts['left_join'])>0 ? implode(' ', $query_parts['left_join']) : '').'
    			'.(!empty($query_parts['where']) ? 'WHERE '.implode(' AND ', $query_parts['where']) : '').' '.$order.' LIMIT '.$start.', '.$limit;
		//echo $query;
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$adv[]=$ar;
			}
		}
		
		$query='SELECT FOUND_ROWS() as fr';
		$stmt=$DBC->query($query);
		if($stmt){
			$ar=$DBC->fetch($stmt);
			$total_count=$ar['fr'];
		}else{
			$total_count=0;
		}
		
		$max_page=ceil($total_count/$limit);
		
		
		if($page>$max_page){
			$page=1;
			$params['page']=1;
			$start = ($page-1)*$limit;
			
			$query='SELECT SQL_CALC_FOUND_ROWS '.implode(', ', $query_parts['select_what']).'
    			FROM `'.$main_table.'` '.implode(' ', $query_parts['left_join']).'
    			'.(!empty($query_parts['where']) ? 'WHERE '.implode(' AND ', $query_parts['where']) : '').' '.$order.' LIMIT '.$start.', '.$limit;
			
			$DBC=DBC::getInstance();
			$stmt=$DBC->query($query);
			if($stmt){
				while($ar=$DBC->fetch($stmt)){
					$adv[]=$ar;
				}
			}
		}
		
		$pager_params=$params;
		 
		 
		unset($params['order']);
		unset($params['asc']);
		unset($params['favorites']);
		
		if(isset($model['topic_id'])){
			if(!is_array($params['topic_id']) && $params['topic_id']!=''){
				if(!$params['admin']){
					if($this->cityTopicUrlFind($_SERVER['REQUEST_URI'])){
						$p=parse_url($_SERVER['REQUEST_URI']);
						unset($params['city_id']);
						unset($params['topic_id']);
						unset($pager_params['city_id']);
						unset($pager_params['topic_id']);
						$pageurl=trim($p['path'],'/');
					}elseif($category_structure['catalog'][$params['topic_id']]['url']!='' && 1==$this->getConfigValue('apps.seo.level_enable')){
						$pageurl=$category_structure['catalog'][$params['topic_id']]['url'];
						//unset($pager_params['topic_id']);
						unset($params['topic_id']);
						unset($pager_params['topic_id']);
					}elseif($category_structure['catalog'][$params['topic_id']]['url']!=''){
						//echo 1;
						$pageurl=$category_structure['catalog'][$params['topic_id']]['url'];
						unset($pager_params['topic_id']);
						unset($params['topic_id']);
					}else{
						if(preg_match('/topic(\d*).html/',$_SERVER['REQUEST_URI'])){
							unset($pager_params['topic_id']);
						}
						if($params['topic_id']!=0){
							$pageurl='topic'.$params['topic_id'].'.html';
							unset($params['topic_id']);
						}else{
							$pageurl='';
							unset($params['topic_id']);
							unset($pager_params['topic_id']);
						}
							
					}
				}else{
					$pageurl='';
				}
			}else{
				$pageurl='';
			}
		}
		
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
		$Structure_Manager = new Structure_Manager();
		$category_structure = $Structure_Manager->loadCategoryStructure();
		
		
		foreach($adv as $k=>$ad){
			if(1==$this->getConfigValue('apps.seo.level_enable')){
			
				if($category_structure['catalog'][$ad['topic_id_orig']]['url']!=''){
					$adv[$k]['parent_category_url']=$category_structure['catalog'][$ad['topic_id_orig']]['url'].'/';
				}else{
					$adv[$k]['parent_category_url']='';
				}
			}else{
				$adv[$k]['parent_category_url']='';
			}
			
			if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $ad['translit_alias']!=''){
				$adv[$k]['href']=SITEBILL_MAIN_URL.'/'.$adv[$k]['parent_category_url'].$ad['translit_alias'];
			}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
				$adv[$k]['href']=SITEBILL_MAIN_URL.'/'.$adv[$k]['parent_category_url'].'realty'.$ad['id'].'.html';
			}else{
				$adv[$k]['href']=SITEBILL_MAIN_URL.'/'.$adv[$k]['parent_category_url'].'realty'.$ad['id'];
			}
		}
		
		
		
		
		
		$pager_params['page_url']=$pageurl;
		
		foreach ( $params as $key => $value ) {
			if(is_array($value)){
				if(count($value)>0){
					foreach($value as $v){
						if($v!=''){
							$pairs[] = $key.'[]='.$v;
						}
					}
				}
			}elseif ( $value != '') {
				if($key!='topic_id'){
					$pairs[] = "$key=$value";
				}elseif($params['admin']){
					$pairs[] = "$key=$value";
				}
		
			}
		}
		
		if ( is_array($pairs) ) {
			$url = $pageurl.'?'.implode('&', $pairs);
		}else{
			$url = $pageurl.'?key=value';
		}
		$this->template->assert('url', $url);
		
		
		
		$this->template->assert('pager', $this->get_page_links_list ($page, $total_count, $limit, $pager_params ));
		
		$this->template->assign('main_file_tpl', 'realty_front_grid.tpl');
		
		$this->template->assign('grid_items', $adv);
		$this->template->assign('grid_header', $fields_array);
		$this->template->assign('grid_head_ids', $grid_head_ids);
		$this->template->assign('grid_head_names', $grid_head_names);
		
		
		//echo $query;
		//print_r($query_parts);
		//echo '</pre>';
		return $ret;
	}
	
	protected function compileModelElement($element, $main_table, $is_attached=false){
		//print_r($element);
		$ret=array(
				'select_what'=>array(),
				'select_from'=>array(),
				'left_join'=>array()
		);
		 
		if($element['type']=='safe_string'){
			$ret['select_what'][]='`'.$main_table.'`.`'.$element['name'].'`';
			if($is_attached){
				$ret['select_what_attached'][]='`'.$main_table.'`.`'.$element['name'].'`';
			}
		}elseif($element['type']=='primary_key'){
			//$ret['select_what'][]='`'.$main_table.'`.`'.$element['name'].'`';
		}elseif($element['type']=='price'){
			$ret['select_what'][]='`'.$main_table.'`.`'.$element['name'].'`';
			if($is_attached){
				$ret['select_what_attached'][]='`'.$main_table.'`.`'.$element['name'].'`';
			}
		}elseif($element['type']=='select_by_query'){
			//$ret['select_what'][]='`'.$element['name'].'`';
	
			$ret['left_join'][]='LEFT JOIN `'.DB_PREFIX.'_'.$element['primary_key_table'].'` ON `'.DB_PREFIX.'_data`.`'.$element['name'].'`=`'.DB_PREFIX.'_'.$element['primary_key_table'].'`.`'.$element['primary_key_name'].'`';
			if($is_attached){
				$ret['select_what_attached'][]='`'.DB_PREFIX.'_'.$element['primary_key_table'].'`.`'.$element['value_name'].'`';
			}
			$ret['select_what'][]='`'.DB_PREFIX.'_'.$element['primary_key_table'].'`.`'.$element['value_name'].'` AS `'.$element['name'].'`';
			$ret['select_what'][]='`'.$main_table.'`.`'.$element['name'].'` AS `'.$element['name'].'_orig`';
			//print_r($element);
		}elseif($element['type']=='select_box_structure'){
			$ret['left_join'][]='LEFT JOIN `'.DB_PREFIX.'_topic` ON `'.DB_PREFIX.'_data`.`'.$element['name'].'`=`'.DB_PREFIX.'_topic`.`id`';
			$ret['select_what'][]='`'.DB_PREFIX.'_topic`.`name` AS `'.$element['name'].'`';
			$ret['select_what'][]='`'.$main_table.'`.`'.$element['name'].'` AS `'.$element['name'].'_orig`';
			if($is_attached){
				$ret['select_what_attached'][]='`'.DB_PREFIX.'_topic`.`name`';
			}
		}elseif($element['type']=='textarea' || $element['type']=='textarea_editor'){
			$ret['select_what'][]='`'.$main_table.'`.`'.$element['name'].'`';
			if($is_attached){
				$ret['select_what_attached'][]='`'.$main_table.'`.`'.$element['name'].'`';
			}
		}else{
			$ret['select_what'][]='`'.$main_table.'`.`'.$element['name'].'`';
			if($is_attached){
				$ret['select_what_attached'][]='`'.$main_table.'`.`'.$element['name'].'`';
			}
		}
		return $ret;
	}
	
	public function buildSelectQueryExternal($model, $main_table){
		return $this->buildSelectQuery($model, $main_table);
	}
	
	protected function buildSelectQuery($model, $main_table){
		$what=array();
		$left_join=array();
		foreach($model as $m){
			$qp=$this->buildModelElement($m, $main_table);
			if(!empty($qp['what'])){
				foreach($qp['what'] as $w){
					$what[]=$w;
				}
			}
			if(!empty($qp['left_join'])){
				foreach($qp['left_join'] as $w){
					$left_join[]=$w;
				}
			}
		}
		//print_r($what);
		//print_r($left_join);
		
		if(empty($what)){
			$what[]='`'.$main_table.'`.*';
		}
		if(empty($left_join)){
			$left_join_string='';
		}else{
			$left_join_string=implode(' ', $left_join);
		}
		return array('what'=>implode(', ', $what), 'left_join'=>$left_join_string);
		//$q='SELECT '.implode(', ', $what).' FROM '.$main_table.' '.implode(' ', $left_join);
		//echo $q;
	}
	
	protected function buildModelElement($element, $main_table){
		$what=array();
		$left_join=array();
		switch($element['type']){
			
			case 'primary_key' : {
				$what[]='`'.$main_table.'`.`'.$element['name'].'`';
				break;
			}
			case 'safe_string' : {
				if(isset($element['parameters']['is_numeric']) && $element['parameters']['is_numeric']==1){
					$what[]='`'.$main_table.'`.`'.$element['name'].'`*1 AS `'.$element['name'].'`';
				}else{
					$what[]='`'.$main_table.'`.`'.$element['name'].'`';
				}
				
				break;
			}
			case 'hidden' : {
				$what[]='`'.$main_table.'`.`'.$element['name'].'`';
				break;
			}
			case 'checkbox' : {
				$what[]='`'.$main_table.'`.`'.$element['name'].'`';
				break;
			}
			case 'select_box_structure' : {
				$left_join[]='LEFT JOIN `'.DB_PREFIX.'_topic` ON `'.$main_table.'`.`'.$element['name'].'`=`'.DB_PREFIX.'_topic`.`id`';
				$what[]='`'.DB_PREFIX.'_topic`.`name` AS `'.$element['name'].'`';
				$what[]='`'.$main_table.'`.`'.$element['name'].'` AS `'.$element['name'].'_orig`';
				break;
			}
			case 'select_by_query' : {
				$left_join[]='LEFT JOIN `'.DB_PREFIX.'_'.$element['primary_key_table'].'` ON `'.$main_table.'`.`'.$element['name'].'`=`'.DB_PREFIX.'_'.$element['primary_key_table'].'`.`'.$element['primary_key_name'].'`';
				$what[]='`'.DB_PREFIX.'_'.$element['primary_key_table'].'`.`'.$element['value_name'].'` AS `'.$element['name'].'`';
				break;
			}
			case 'select_box' : {
				$what[]='`'.$main_table.'`.`'.$element['name'].'`';
				break;
			}
			case 'auto_add_value' : {
				
				break;
			}
			case 'price' : {
				$what[]='`'.$main_table.'`.`'.$element['name'].'`';
				break;
			}
			case 'textarea' : {
				$what[]='`'.$main_table.'`.`'.$element['name'].'`';
				break;
			}
			case 'uploadify_image' : {
				
				break;
			}
			case 'mobilephone' : {
				$what[]='`'.$main_table.'`.`'.$element['name'].'`';
				break;
			}
			case 'password' : {
				$what[]='`'.$main_table.'`.`'.$element['name'].'`';
				break;
			}
			case 'photo' : {
				
				break;
			}
			case 'geodata' : {
				$what[]='`'.$main_table.'`.`geo_lat`';
				$what[]='`'.$main_table.'`.`geo_lng`';
				break;
			}
			case 'structure' : {
				
				break;
			}
			case 'textarea_editor' : {
				$what[]='`'.$main_table.'`.`'.$element['name'].'`';
				break;
			}
			case 'date' : {
				$what[]='`'.$main_table.'`.`'.$element['name'].'`';
				break;
			}
			case 'attachment' : {
				
				break;
			}
			case 'tlocation' : {
				
				break;
			}
		}
		return array('what'=>$what, 'left_join'=>$left_join);
	}
	
	protected function prepareData($adv, $fields_array, $model){
		foreach($adv as $k=>$ad){
			$p=array();
			foreach($fields_array as $f=>$ff){
				if(isset($ff['attached']) && !empty($ff['attached'])){
					$attached_parts=array();
					if($model[$f]['type']=='select_box'){
						$attached_parts[]=$model[$f]['select_data'][$ad[$f]];
					}else{
						$attached_parts[]=$ad[$f];
					}
						
					$separator=$ff['separator'];
					foreach($ff['attached'] as $attached){
		
						if($model[$attached['name']]['type']=='select_box'){
							//print_r($model[$f]);
							$attached_parts[]=$model[$f]['select_data'][$ad[$attached['name']]];
						}else{
							if($ad[$attached['name']]!=''){
								$attached_parts[]=$ad[$attached['name']];
							}
						}
					}
					$p[$f]=implode($separator, $attached_parts);
				}else{
					if($model[$f]['type']=='select_box'){
						//print_r($model[$f]);
						$p[$f]=$model[$f]['select_data'][$ad[$f]];
					}elseif($model[$f]['type']=='uploadify_image'){
						$p[$f]=$this->get_image_array('data', 'data', 'id', $p['id']);
					}else{
						$p[$f]=$ad[$f];
					}
					
				}
			}
			$p['href']=$ad['href'];
			$adv_[]=$p;
		}
		return $adv_;
	}
	
}