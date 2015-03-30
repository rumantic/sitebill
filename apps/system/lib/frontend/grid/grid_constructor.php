<?php
/**
 * Grid constructor
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Grid_Constructor extends SiteBill_Krascap {
	public $grid_total;
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
    }
    
    function vip_right ( $params ) {
    	$res = $this->get_sitebill_adv_ext( $params, true, false );
    	$this->template->assign('special_items2', $res);
    }
    
    function vip_array ( $params ) {
    	$params['per_page'] = 100;
    	$res = $this->get_sitebill_adv_ext( $params, true, false );
    	return $res;
    }
    
    function specialGen(){
    	$params=array(
    		'min_price'=>1000,
    		'srch_word'=>'asdd ds'		
    	);
    	//gt gte lt lte e ne in instart inend
    	$rules=array(
    		'id'=>array(
    			'value'=>'int',
    			'look_in'=>'id',
    			'eq'=>'e'
    		),
    		'min_price'=>array(
    			'value'=>'int',
    			'look_in'=>'price',
    			'eq'=>'gte'
    		),
    		'max_price'=>array(
    			'value'=>'int',
    			'look_in'=>'price',
    			'eq'=>'lte'
    		),
    		'srch_word'=>array(
    			'value'=>'literal',
    			'look_in'=>array('text', 'phone'),
    			'eq'=>'in'
    		)
    	);
    	
    	/*
    	 id
    	 min_price
    	 max_price
    	 min_floor
    	 max_floor
    	 min_square
    	 max_square
    	 */
    	
    	$fields=array(
    		array(
    			'grid_tpl_name'=>'name1',
    			'sortable'=>true,
    			'separator'=>',',
    			'collection'=>array(
    				array(
    					'name'=>'city_id',
    					'type'=>'normalized'
    				),
    				array(
    					'name'=>'street_id',
    					'type'=>'normalized'
    				),
    				array(
    					'name'=>'number',
    					'type'=>'db'
    				)
    			)
    		)		
    	);
    }
    
    private function _grid($params){
    	
    	$default_order_by=array('lt.id DESC');
    	$default_sort_direction='DESC';
    	
    	$order_by=array(
    		array('price', 'asc'),
    		array('id', 'desc'),
    		array('topic_id', 'desc'),
    		array('city_id', 'desc'),
    		array('zxc', 'desc')
    	);
    	
    	$select_what=array('id', 'price', 'topic_id');
    	
    	$conditions=array(
    		array('price', 'lt', 3000),
    		array('price', 'gt', 1000),
    		array('topic_id', 'eq', array(1,2,3,4)),
    		array('city_id', 'eq', array(1))
    	);
    	
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	//print_r($params);
    	$params = $_REQUEST;
    	
    	$_model=$data_model->get_kvartira_model(false, true);
    	$_model_elements=$_model['data'];
    	$table='data';
    	$ptable='re_data';
    	
    	$_order_by=array();
    	$_left_joins=array();
    	
    	//print_r($_model_elements);
    	
    	
    	if(!empty($order_by)){
    		foreach($order_by as $order_by_variant){
    			$field_name=mb_strtolower($order_by_variant[0], SITE_ENCODING);
    			$sort_direction=mb_strtolower($order_by_variant[1], SITE_ENCODING);
    			
    			if(isset($_model_elements[$field_name])){
    				if($_model_elements[$field_name]['type']=='select_by_query'){
    					//print_r($_model_elements[$field_name]);
    					$_left_joins[]='LEFT JOIN '.DB_PREFIX.'_'.$_model_elements[$field_name]['primary_key_table'].' ON '.DB_PREFIX.'_data.'.$field_name.'='.DB_PREFIX.'_'.$_model_elements[$field_name]['primary_key_name'];
    					$sort_field_name=DB_PREFIX.'_'.$_model_elements[$field_name]['primary_key_table'].'.'.$_model_elements[$field_name]['value_name'];
    				}elseif($_model_elements[$field_name]['type']=='select_box_structure'){
    					$_left_joins[]='LEFT JOIN '.DB_PREFIX.'_topic ON '.DB_PREFIX.'_data.'.$field_name.'='.DB_PREFIX.'_topic.id';
    					$sort_field_name=DB_PREFIX.'_topic.name';
    				}else{
    					$sort_field_name=DB_PREFIX.'_data.'.$field_name;
    				}
    				
    				
    				
    				if($sort_direction=='asc'){
    					$_order_by[]=$sort_field_name.' ASC';
    				}elseif($sort_direction=='desc'){
    					$_order_by[]=$sort_field_name.' DESC';
    				}else{
    					$_order_by[]=$sort_field_name.' '.$default_sort_direction;
    				}
    			}
    		}
    	}
    	
    	if(empty($_order_by)){
    		$_order_by=$default_order_by;
    	}
    	
    	print_r($_order_by);
    	print_r($_left_joins);
    	 /*
    	foreach($_model['data'] as $field_key=>$field_opts){
    		
    		if(!isset($params[$field_key])){
    			continue;
    		}
    		
    		$value=$params[$field_key];
    		
    		
    		if($field_opts['type']=='safe_string'){
    			
    			if(is_array($value)){
    				$where[]=$ptable.'.'.$field_key.' IN (\''.implode('\', \'', $value).'\')';
    			}else{
    				if($value!=''){
    					$where[]=$ptable.'.'.$field_key.'= \''.$value.'\'';
    				}
    				
    			}
    			
    		}
    
    		if($field_opts['type']=='textarea'){
    			$where[]=$ptable.'.'.$field_key.'LIKE \'%'.$value.'%\'';
    		}
    		
    		if($field_opts['type']=='password'){
    			 
    		}
    		
    		if($field_opts['type']=='primary_key'){
    			if(!is_array($value)){
    				$value=(array)$value;
    			}
    			$where[]=$ptable.'.'.$field_key.' IN ('.implode(', ', $value).')';
    		}
    		
    		if($field_opts['type']=='hidden'){
    
    		}
    		
    		if($field_opts['type']=='checkbox'){
    			echo $field_key;
    			if(1==(int)$value){
    				$where[]=$ptable.'.'.$field_key.' = 1';
    			}
    		}
    		
    		if($field_opts['type']=='select_by_query'){
    			if(!is_array($value)){
    				$value=(array)$value;
    			}
    			$where[]=$ptable.'.'.$field_key.' IN ('.implode(', ', $value).')';
    		}
    		
    		if($field_opts['type']=='select_box'){
    			if(!is_array($value)){
    				$value=(array)$value;
    			}
    			$where[]=$ptable.'.'.$field_key.' IN ('.implode(', ', $value).')';
    		}
    		
    		if($field_opts['type']=='structure'){
    			if(!is_array($value)){
    				$value=(array)$value;
    			}
    			$where[]=$ptable.'.'.$field_key.' IN ('.implode(', ', $value).')';
    		}
    		
    		if($field_opts['type']=='select_box_structure'){
    			if(!is_array($value)){
    				$value=(array)$value;
    			}
    			$where[]=$ptable.'.'.$field_key.' IN ('.implode(', ', $value).')';
    		}
    		
    		if($field_opts['type']=='price'){
    			if(!is_array($value)){
    				$where[]=$ptable.'.'.$field_key.' = '.$value;
    			}else{
    				$where[]=$ptable.'.'.$field_key.' IN ('.implode(', ', $value).')';
    			}
    		}
    		
    		if($field_opts['type']=='mobilephone'){
    			if(is_array($value)){
    				$where[]=$ptable.'.'.$field_key.' IN ('.implode(', ', $value).')';
    			}else{
    				$where[]=$ptable.'.'.$field_key.'= \''.$value.'\'';
    			}
    		}
    
    	}*/
    	echo '<pre>';
    	print_r($where);
    	echo '</pre>';
    }
    
    function tryGetSimilarTopicsByTranslitName($topic_id) {
    	$translit_name = false;
    	$result = array();
    	$DBC=DBC::getInstance();
    	$query = "select id, translit_name from ".DB_PREFIX."_topic where id=?";
    	$stmt=$DBC->query($query, array($topic_id));
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		if ( strlen($ar['translit_name']) > 0  ) {
    			$translit_name = $ar['translit_name'];
    		}
    	}
    	
    	//echo '$translit_name = '.$translit_name.'<br>';
    	if ( $translit_name ) {
    		$query = "select id, translit_name from ".DB_PREFIX."_topic where translit_name=?";
    		$stmt=$DBC->query($query, array($translit_name));
    		if($stmt){
    			while ($ar=$DBC->fetch($stmt) ) {
    				if ( strlen($ar['translit_name']) > 0 and $ar['id'] != $topic_id ) {
    					array_push($result, $ar['id']);
    				}
    			}
    		}
    	}
    	/*
    	echo '<pre>1';
    	print_r($result);
    	echo '</pre>';
    	*/
    	return $result;
    }
    
    /**
     * Main
     * @param array $param
     * @return array
     */
    function main ( $params ) {
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();
        
        
        $this->template->assign('category_tree', $this->get_category_tree( $params, $category_structure ) );
		$this->template->assign('breadcrumbs', $this->prepareBreadcrumbs($params) );
		$this->template->assign('search_params', json_encode($params) );
		$this->template->assign('search_url', $_SERVER['REQUEST_URI'] );
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable')==1){
			$_billing_on=true;
		}else{
			$_billing_on=false;
		}
		
		if($params['admin']!=1 && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/front_grid_constructor.php')){
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/front_grid_constructor.php');
			
			if(1!=$this->getConfigValue('block_user_front_grids')){
				
				if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/grid/front_grid_local.php')){
					require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/grid/front_grid_local.php');
					$FGG = new Front_Grid_Local();
				}else{
					$FGG = new Front_Grid_Constructor();
				}
					
					
				if ( !is_array($params['topic_id']) && $params['topic_id'] != '' &&  $params['topic_id'] != 0) {
					$topic=(array)$params['topic_id'];
					if ( $this->getConfigValue('theme') == 'etown' ) {
						if ( $params['city_id'] != 0 and $params['city_id'] != '' ) {
							$topic = array_merge($topic, $this->tryGetSimilarTopicsByTranslitName($params['topic_id']));
							$params['topic_id'] = $topic; 
						}
					}
					/*
					echo '<pre>';
					print_r($params);
					print_r($topic);
					echo '</pre>';
					exit;
					*/
				}elseif(is_array($params['topic_id'])){
					$topic=$params['topic_id'];
				}
				
				//echo $FGG->grid_exists($topic);
					
				if($columns_data=$FGG->grid_exists($topic)){
					
					$data_model = new Data_Model();
				
					$_model=$data_model->get_kvartira_model();
				
				
					//$fields=new stdClass();
					//$FGG->generate($_model, $columns_data, $params);
					$FGG->fullGenerate($_model, $columns_data, $params);
				}else{
					if($_billing_on){
						$res = $this->get_sitebill_adv_ext( $params, false, true );
					}else{
						$res = $this->get_sitebill_adv_ext( $params );
					}
					$this->get_sales_grid($res);
				}
			}else{
				
				//$FGG = new Front_Grid_Constructor();
				if($_billing_on){
					$res = $this->get_sitebill_adv_ext( $params, false, true );
				}else{
					$res = $this->get_sitebill_adv_ext( $params );
				}
				
				$this->get_sales_grid($res);
			}
		}else{
			$res = $this->get_sitebill_adv_ext( $params );
			
			$this->get_sales_grid($res);
		}
		
	}
    
    
    
    
    
    /**
     * Main
     * @param array $param
     * @return array
     */
    function main_contact ( $params ) {
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	$Structure_Manager = new Structure_Manager();
    	$category_structure = $Structure_Manager->loadCategoryStructure();
    
    	$res = $this->get_sitebill_adv_ext( $params );
    	$res = $this->add_user_account_info($res);
    	$this->template->assign('category_tree', $this->get_category_tree( $params, $category_structure ) );
    	$this->template->assign('breadcrumbs', $this->get_category_breadcrumbs( $params, $category_structure, SITEBILL_MAIN_URL ) );
    
    	$this->get_sales_grid($res);
    }
    
    function add_user_account_info ( $res ) {
    	if ( !is_array($res) ) {
    		return $res;
    	}
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
    	$Users_Manager = new User_Object_Manager();
    	 
    	foreach ( $res as $item_id => $item ) {
    		$res[$item_id]['user_array'] = $Users_Manager->load_by_id($item['user_id']); 
    	}
    	//echo '<pre>';
    	//print_r($res);
    	//echo '</pre>';
    	return $res;
    }
    
    
    /**
     * Special
     * @param array $params
     */
    function special ( $params ) {
        $res = $this->get_sitebill_adv_ext( $params, true );
        $this->template->assign('special_items', $res);
        
    }
    
    /**
     * Special right
     * @param unknown_type $params
     */
    function special_right ( $params ) {
    	if ( $this->getConfigValue('theme') == '3columns' ) {
    		$params['only_img'] = 1;
    	}
        $res = $this->get_sitebill_adv_ext( $params, true );
        $this->template->assign('special_items2', $res);
    }
    
    /**
     * Get category tree
     * @param array $params
     * @param array $category_structure
     * @return string
     */
    function get_category_tree( $params, $category_structure ) {
        if(is_array($params['topic_id'])){
        	return '';
        }
        if ( count($category_structure['childs'][$params['topic_id']]) > 0 ) {
            foreach ( $category_structure['childs'][$params['topic_id']] as $item_id => $child_id ) {
	            if($category_structure['catalog'][$child_id]['url']!=''){
		        	$rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/'.$category_structure['catalog'][$child_id]['url'].'">'.$category_structure['catalog'][$child_id]['name'].'</a></li>';
		        }else{
		        	$rs .= '<li><a href="'.SITEBILL_MAIN_URL.'/topic'.$child_id.'.html">'.$category_structure['catalog'][$child_id]['name'].'</a></li>';
		        }
                //$rs .= '<li><a href="?topic_id='.$child_id.'">'.$category_structure['catalog'][$child_id]['name'].'</a></li>';
            }
            return $rs;
        }
        return '';
    }
    
    function get_grid_total_records () {
    	return $this->grid_total;
    }
    
    function get_sitebill_adv_ext( $params, $random = false, $premium = false ) {
    	
    	/*if(defined('IS_DEVELOPER') && IS_DEVELOPER==1){
    		
    		return $this->get_sitebill_adv_ext_modern($params, $random);
    	}*/
    	$premium_ra=array();
    	if ( $premium ) {
    		$premium_ra = $this->get_sitebill_adv_ext_base($params, $random, true);
    	}
    	
    	$ra=$this->get_sitebill_adv_ext_base($params, $random);
    	
    	if ( count($premium_ra)>0 ) {
    		$ra = array_merge($premium_ra, $ra);
    	}
    	
    	
    	return $ra;
    }
    
    function getGridSelectQuery($params){
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/front_grid_constructor.php');
    	$data_model = new Data_Model();
    	$_model=$data_model->get_kvartira_model();
    	
    	$params=$_REQUEST;
    	
    	if ( !isset($params['page']) or $params['page'] == 0 ) {
    		$page = 1;
    	} else {
    		$page = $params['page'];
    	}
    	$limit = $this->getConfigValue('per_page');
    	
    	if ( $params['vip'] == 1 ) {
    		if ( $params['per_page'] > 0 ) {
    			$limit = $params['per_page'];
    		} else {
    			$limit = $this->getConfigValue('vip_rotator_number');
    		}
    	} else {
    		if(isset($params['page_limit']) && $params['page_limit']!=0){
    			$limit = $params['page_limit'];
    		}else{
    			$limit = $this->getConfigValue('per_page');
    		}
    			
    	}
    	if ( $premium ) {
    		$limit = 5;
    	}
    	
    	$params['__from']=($page-1)*$limit;
    	$params['__to']=$limit;
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	//Data_Model::getSelectQuery($_model, $params);
    	$qp=Data_Model::prepareQueryParts($_model, $params);
    	//print_r($qp);
    	$query=Data_Model::getPrimaryQuery($qp);
    	
    	$DBC=DBC::getInstance();
    	$stmt=$DBC->query($query);
    	$ids=array();
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$ids[]=$ar['__pk'];
    		}
    	}
    	
    	$total = 0;
    	$query='SELECT FOUND_ROWS() AS total';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		$total=$ar['total'];
    	}
    	print_r($ids);
    	echo $total;
    	
    	$_params=$qp;
    	$_params['wp']=array();
    	$_params['wp'][]='(`'.$qp['main_table'].'`.`'.$qp['pk'].'` IN ('.implode(',', $ids).'))';
    	//$where_parts[]='(`'.$main_table.'`.`'.$field_data['name'].'` IN ('.implode(',', $vals).'))';
    	
    	$query=Data_Model::getDataSelectQuery($_params);
    	echo $query;
    	//echo Data_Model::primaryQuery($qp);
    	//print_r(Data_Model::getSelectQuery($_model, $params));
    	
    	
    	
    	
    	
    	
    	
    	
    	
    	
    }
    
    /**
     * Get sitebill adv ext
     * @param array $params
     * @param boolean $random
     * @return array
     */
    function get_sitebill_adv_ext_base( $params, $random = false, $premium=false ) {
    	
    	$data=$this->get_sitebill_adv_core($params, $random, $premium, true, true);
    	
    	 
    	
    	$this->template->assert('pager_array', $data['paging']);
    	$this->template->assert('pager', $data['pager']);
    	$this->template->assert('pagerurl', $data['pagerurl']);
    	$this->template->assert('url', $data['url']);
    	$this->template->assert('grid_geodata', json_encode($data['grid_geodata']));
    	$this->template->assert('geoobjects_collection_clustered', json_encode($data['geoobjects_collection_clustered']));
    	$this->template->assert('_total_records', $data['_total_records']);
    	$this->template->assert('_max_page', $data['_max_page']);
    	$this->template->assert('_params', $data['_params']);
    	$this->template->assert('_mysearch_params', $data['_mysearch_params']);
    	
    	return $data['data'];
    	print_r($d);
    	
    	
    	$select_fields=array();
    	/*$select_fields=array(
    		DB_PREFIX.'_data.id',
    		DB_PREFIX.'_data.city_id',
    		DB_PREFIX.'_data.price'		
    	);*/
    	//$this->_grid($params);
    	
    	$is_country_view=$this->getRequestValue('country_view');
    	$is_city_view=$this->getRequestValue('city_view');
    	$is_complex_view=$this->getRequestValue('complex_view');
    	 
    	
    	$this_is_favorites=false;
    	
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable')==1){
    		$_billing_on=true;
    	}else{
    		$_billing_on=false;
    	}
    	
    	if(isset($params['favorites']) && !empty($params['favorites'])){
    		$this_is_favorites=true;
    	}
    	
        if ( $this->getConfigValue('currency_enable') ) {
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/currency/admin/admin.php';
			$CM=new currency_admin();
        }
        
        if(isset($params['_collect_user_info']) && $params['_collect_user_info']==1){
        	$_collect_user_info=true;
        	unset($params['_collect_user_info']);
        }else{
        	$_collect_user_info=false;
        }
        
    	$this->grid_total = 0;
    	
    	
    	//collect WHERE parts
    	$preparedParams=$this->prepareRequestParams($params, $premium);
    	
    	
    	$where_array=$preparedParams['where_array'];
    	$add_from_table=$preparedParams['add_from_table'];
    	$add_select_value=$preparedParams['add_select_value'];
    	$params=$preparedParams['params'];
    	
    	$where_array_prepared=$preparedParams['where_array_prepared'];
    	$where_value_prepared=$preparedParams['where_value_prepared'];
    	
    	$select_what=$preparedParams['select_what'];
    	$left_joins=$preparedParams['left_joins'];
    	
    	$left_joins[]='LEFT JOIN '.DB_PREFIX.'_topic ON '.DB_PREFIX.'_data.topic_id='.DB_PREFIX.'_topic.id';
    	
    	if ( $this->getConfigValue('currency_enable') ) {
    		$select_what[]=DB_PREFIX.'_currency.code AS currency_code';
    		$select_what[]=DB_PREFIX.'_currency.name AS currency_name';
    		$select_what[]='(('.DB_PREFIX.'_data.price*'.DB_PREFIX.'_currency.course)/'.$CM->getCourse(CURRENT_CURRENCY).') AS price_ue';
    		 
    		$left_joins[]='LEFT JOIN '.DB_PREFIX.'_currency ON '.DB_PREFIX.'_data.currency_id='.DB_PREFIX.'_currency.currency_id';
    	}else{
    		$select_what[]=DB_PREFIX.'_data.price AS price_ue';
    	}
    	
    	
    	//append user vars
        
        if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/template_search.php')){
        	require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/template_search.php');
        	$Template_Search=new Template_Search();
        	$results=$Template_Search->run();
        	if(isset($results['where'])){
        		$where_array=array_merge($where_array, $results['where']);
        		$where_array_prepared=array_merge($where_array_prepared, $results['where']);
        	}
        	if(isset($results['params'])){
        		$params=array_merge($params, $results['params']);
        	}
        }
        
        /*print_r($where_array_prepared);
        print_r($where_value_prepared);*/
        
        if ( count($where_array)>0 ) {
            $where_statement = " WHERE ".implode(' AND ', $where_array);
        }
        
        if ( count($where_array_prepared)>0 ) {
        	$where_statement_prepared = " WHERE ".implode(' AND ', $where_array_prepared);
        }
        
        $order=$this->prepareSortOrder($params, $random, $premium);
        
       /*
        require_once SITEBILL_DOCUMENT_ROOT.'/apps/activelinker/admin/admin.php';
        $AL=new activelinker_admin();
        $this->template->assert('activelinker_desc', $AL->generate($params));
        */
        if ( !isset($params['page']) || (int)$params['page'] == 0 ) {
            $page = 1;
        } else {
            $page = (int)$params['page'];
        }
		
        
		
        /*
		if ( $this->getConfigValue('currency_enable') ) {
			$query = "select count(*) as total from re_data LEFT JOIN re_currency ON re_data.currency_id=re_currency.currency_id, re_topic $add_from_table $where_statement ";
		}else{
			$query = "select count(*) as total from re_data, re_topic $add_from_table $where_statement ";
		}
		*/
        
        $query = 'SELECT COUNT('.DB_PREFIX.'_data.id) AS total FROM '.DB_PREFIX.'_data'.(count($left_joins)>0 ? ' '.implode(' ', $left_joins).' ' : '').' '.$where_statement_prepared;
        
		/*if ( $this->getConfigValue('currency_enable') ) {
			$query = 'SELECT COUNT(re_data.id) AS total FROM '.DB_PREFIX.'_data LEFT JOIN '.DB_PREFIX.'_currency ON '.DB_PREFIX.'_data.currency_id='.DB_PREFIX.'_currency.currency_id, '.DB_PREFIX.'_topic '.$add_from_table.' '.$where_statement_prepared.' ';
		}else{
			$query = 'SELECT COUNT(re_data.id) AS total FROM '.DB_PREFIX.'_data, re_topic '.$add_from_table.' '.$where_statement_prepared.' ';
		}*/
		//echo $query.'<br>';
		//print_r($where_value_prepared);
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query, $where_value_prepared);
		/*
		$query1='SELECT COUNT('.DB_PREFIX.'_data.*) as total FROM '.DB_PREFIX.'_data d 
				LEFT JOIN '.DB_PREFIX.'_topic t ON t.id=d.topic_id';
		*/
		
		/*
		$this->db->exec($query);
		if ( !$this->db->success ) {
			echo $this->db->error.'<br>';
		}*/
		$total = 0;
		$this->grid_total = $total;
		if(!$stmt){
			$total = 0;
			$this->grid_total = $total;
			//return array();
		}else{
			$ar=$DBC->fetch($stmt);
			$total = $ar['total'];
			$this->grid_total = $total;
		}
		//echo $this->grid_total;
		
		global $smarty;
		
		
		$pageLimitParams=$this->preparePageLimitParams($params, $page, $total, $premium);
		$start=$pageLimitParams['start'];
		$limit=$pageLimitParams['limit'];
		$max_page=$pageLimitParams['max_page'];
		$page = (int)$params['page'];
		
		
		$pager_params=$params;
        	
	    
        unset($params['order']);
        unset($params['asc']);
        unset($params['favorites']);
        
        if ( preg_match('/\/special\//', $_SERVER['REQUEST_URI']) ) {
        	unset($params['spec']);
        	unset($pager_params['spec']);
        }
        
        if(''!=$is_country_view){
        	unset($pager_params['country_id']);
        	$pageurl=$is_country_view;
        	
        }elseif($is_city_view){
        	unset($pager_params['city_id']);
        	$pageurl=$is_city_view;
        }elseif(''!=$is_complex_view){
        	unset($pager_params['complex_id']);
        	$pageurl=$is_complex_view;
        	
        }else{
        	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        	$Structure_Manager = new Structure_Manager();
        	$category_structure = $Structure_Manager->loadCategoryStructure();
        	if($this_is_favorites){
        		$pageurl='myfavorites';
        	}else{
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
        }
	    
        
        
        if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/page_navigator.php')){
        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/page_navigator.php';
        	$url='';
        	if(isset($params['pager_url'])){
        		$url=$params['pager_url'];
        		unset($params['pager_url']);
        	}
        	
        	if($params['admin']){
        		$nurl='account/data';
        	}else{
        		$nurl=$pageurl;
        	}
        	$paging=Page_Navigator::getPagingArray($total, $page, $limit, $pager_params, $nurl);
        	$this->template->assert('pager_array', $paging);
        }
        
        
        $pager_params['page_url']=$pageurl;
        $this->template->assert('pager', $this->get_page_links_list ($page, $total, $limit, $pager_params ));
        $pairs=array();
        foreach ( $pager_params as $key => $value ) {
        	if($key=='page_url' || $key=='page_limit'){
        		
        	}else{
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
        	
        }
       
        if ( is_array($pairs) ) {
        	$url = $pageurl.'?'.implode('&', $pairs);
        }else{
        	$url = $pageurl.'?key=value';
        }
        $this->template->assert('pagerurl', $url);
	    
        $pairs=array();
        if($is_country_view){
        	unset($params['country_id']);
        }
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
	                //echo "key = $key, value = $value<br>";
		            $pairs[] = "$key=$value";
	        	}elseif($params['admin']){
	        		$pairs[] = "$key=$value";
	        	}
	        	
	        }
	    }
      
	    if($is_country_view){
	    	if ( is_array($pairs) ) {
	    		$url = $is_country_view.'?'.implode('&', $pairs);
	    	}else{
	    		$url = $is_country_view.'?';
	    	}
	    }else{
	    	if ( is_array($pairs) ) {
	    		$url = $pageurl.'?'.implode('&', $pairs);
	    	}else{
	    		$url = $pageurl.'?key=value';
	    	}
	    }
	    
        $this->template->assert('url', $url);
        
        /*
        if ( $this->getConfigValue('apps.company.timelimit') ) {
        	if ( $this->getConfigValue('currency_enable') ) {
        		$query = "select re_currency.code AS currency_code, re_currency.name AS currency_name, ((re_data.price*re_currency.course)/".$CM->getCourse(CURRENT_CURRENCY).") AS price_ue, re_data.*, re_topic.name as type_sh $add_select_value from re_data LEFT JOIN re_currency ON re_data.currency_id=re_currency.currency_id, re_topic $add_from_table $where_statement ORDER BY ".$order.($params['no_portions']==1 ? '' : " LIMIT ".$start.", ".$limit);
        	} else {
        		$query = "select re_data.*, re_data.price AS price_ue, re_topic.name as type_sh $add_select_value from re_data, re_topic $add_from_table $where_statement order by ".$order.($params['no_portions']==1 ? '' : " LIMIT ".$start.", ".$limit);
        	}
        	
        } else {
        	if ( $this->getConfigValue('currency_enable') ) {
        		$query = "select re_currency.code AS currency_code, re_currency.name AS currency_name, ((re_data.price*re_currency.course)/".$CM->getCourse(CURRENT_CURRENCY).") AS price_ue, re_data.*, re_topic.name as type_sh $add_select_value from re_data LEFT JOIN re_currency ON re_data.currency_id=re_currency.currency_id, re_topic $add_from_table $where_statement ORDER BY ".$order.($params['no_portions']==1 ? '' : " LIMIT ".$start.", ".$limit);
        	} else {
        		$query = "select re_data.*, re_data.price AS price_ue, re_topic.name as type_sh $add_select_value from re_data, re_topic $add_from_table $where_statement ORDER BY ".$order.($params['no_portions']==1 ? '' : " LIMIT ".$start.", ".$limit);
        	}
        }
        */
       
        //$select_what=array();
        if(count($select_fields)==0){
        	$select_what[]=DB_PREFIX.'_data.*';
        }else{
        	$select_what=array_merge($select_what, $select_fields);
        }
        
        $select_what[]=DB_PREFIX.'_topic.name AS type_sh';
        
        
        
        
        if ( $this->getConfigValue('apps.company.timelimit') ) {
        	$query = 'SELECT '.implode(', ', $select_what).' '.$add_select_value.'
        		FROM '.DB_PREFIX.'_data'.(count($left_joins)>0 ? ' '.implode(' ', $left_joins).' ' : '').' '.$where_statement_prepared.'
        		ORDER BY '.$order.($params['no_portions']==1 ? '' : ' LIMIT '.$start.', '.$limit);
        	/*if ( $this->getConfigValue('currency_enable') ) {
        		$query = 'SELECT '.implode(', ', $select_what).' '.$add_select_value.' 
        				FROM '.DB_PREFIX.'_data '.(count($left_joins)>0 ? implode(' ', $left_joins).' ' : '').'
        				re_topic '.' '.$where_statement_prepared.' 
        				ORDER BY '.$order.($params['no_portions']==1 ? '' : ' LIMIT '.$start.', '.$limit);
        	} else {
        		$query = 'SELECT '.implode(', ', $select_what).' '.$add_select_value.' 
        				FROM '.DB_PREFIX.'_data, '.DB_PREFIX.'_topic '.$add_from_table.' '.$where_statement_prepared.' 
        				ORDER BY '.$order.($params['no_portions']==1 ? '' : ' LIMIT '.$start.', '.$limit);
        	}*/
        	 
        } else {
        	$query = 'SELECT '.implode(', ', $select_what).' '.$add_select_value.'
        		FROM '.DB_PREFIX.'_data'.(count($left_joins)>0 ? ' '.implode(' ', $left_joins).' ' : '').' '.$where_statement_prepared.'
        		ORDER BY '.$order.($params['no_portions']==1 ? '' : ' LIMIT '.$start.', '.$limit);
        	/*if ( $this->getConfigValue('currency_enable') ) {
        		$query = 'SELECT '.implode(', ', $select_what).' '.$add_select_value .'
        		FROM '.DB_PREFIX.'_data'.(count($left_joins)>0 ? ' '.implode(' ', $left_joins).' ' : '').'
        		'.$where_statement_prepared .'
        		ORDER BY '.$order.($params['no_portions']==1 ? '' : ' LIMIT '.$start.', '.$limit);
        	} else {
        		$query = 'SELECT '.implode(', ', $select_what).' '.$add_select_value.' 
        		FROM '.DB_PREFIX.'_data'.(count($left_joins)>0 ? ' '.implode(' ', $left_joins).' ' : '').' '.$where_statement_prepared.' 
        		ORDER BY '.$order.($params['no_portions']==1 ? '' : ' LIMIT '.$start.', '.$limit);
        	}*/
        }
       
       // echo $query;
       // echo $query.'<br>';
      // print_r($where_value_prepared);
        $stmt=$DBC->query($query, $where_value_prepared);
        
        $ra = array();
        if($stmt){
        	
        	$i = 0;
        	if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/company/company.xml') ) {
        		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
        		$Account = new Account;
        	
        	}
        	
        	while($ar=$DBC->fetch($stmt)){
        		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/company/company.xml') ) {
        			$company_profile = $Account->get_company_profile($ar['user_id']);
        			$ar['company'] = $company_profile['name']['value'];
        		}
        		$ra[$i] = $ar;
        		$i++;
        	}
        }
        
        $gdata=array();
        $geoobjects_collection=array();
        if(count($ra)>0){
        	$ra=$this->transformGridData($ra, $_collect_user_info);
        	foreach($ra as $k=>$d){
        		if( isset($d['geo_lat']) && isset($d['geo_lng']) && $d['geo_lat']!='' && $d['geo_lng']!='' ){
        			 
        			 
        			$gdata[$k]['currency_name']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['currency_name']);
        			$gdata[$k]['city']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['city']);
        			$gdata[$k]['street']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['street']);
        			$gdata[$k]['price']=number_format($d['price'],0,'.',' ');
        			$gdata[$k]['type_sh']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['type_sh']);
        			$gdata[$k]['title']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['city'].' '.$d['street'].(($d['number']!='' && $d['number']!=0) ? ', '.$d['number'] : '').' ('.$d['price'].')');
        			if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_on_map.tpl')){
        				$smarty->assign('realty',$d);
        				$html=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_on_map.tpl');
        				$html = str_replace("\r\n", ' ', $html);
        				$html = str_replace("\n", ' ', $html);
        				$html = str_replace("\t", ' ', $html);
        				//$html = htmlspecialchars($html);
        				$html = addslashes($html);
        			}else{
        				$html = '';
        			}
        			 
        			 
        			$gdata[$k]['html']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $html);
        			$gdata[$k]['geo_lat']=$d['geo_lat'];
        			$gdata[$k]['geo_lng']=$d['geo_lng'];
        			$gdata[$k]['href']=$d['href'];
        			$gdata[$k]['parent_category_url']=$d['parent_category_url'];
        		}
        	}
        	
        	$grid_geodata=array();
        	$this->template->assert('grid_geodata', json_encode($this->generateGridGeoDataOld($ra)));
        }else{
        	$this->template->assert('grid_geodata', json_encode(array()));
        }
        
        if(count($gdata)>0){
        	foreach ($gdata as $gd){
        		$gc=$gd['geo_lat'].'_'.$gd['geo_lng'];
        		if(isset($geoobjects_collection[$gc])){
        			$geoobjects_collection[$gc]['html'].=$gd['html'];
        			$geoobjects_collection[$gc]['count']++;
        		}else{
        			$geoobjects_collection[$gc]['lat']=$gd['geo_lat'];
        			$geoobjects_collection[$gc]['lng']=$gd['geo_lng'];
        			$geoobjects_collection[$gc]['html']=$gd['html'];
        			$geoobjects_collection[$gc]['count']=1;
        		}
        	}
        }
        
        
        $this->template->assert('geoobjects_collection_clustered', json_encode($geoobjects_collection));
        $this->template->assert('_total_records', $total);
        $this->template->assert('_max_page', $max_page);
        
        
        return $ra;
    }
    
    
    function get_sitebill_adv_core( $params, $random = false, $premium=false, $paging=true, $geodata=false ) {
    	
    	$select_fields=array();
    	$return=array();
    	
    	$is_country_view=$this->getRequestValue('country_view');
    	$is_region_view=$this->getRequestValue('region_view');
    	$is_city_view=$this->getRequestValue('city_view');
    	$is_complex_view=$this->getRequestValue('complex_view');
    	$predefined_info=$this->getRequestValue('predefined_info');
    	 
    	$this_is_favorites=false;
    	 
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable')==1){
    		$_billing_on=true;
    	}else{
    		$_billing_on=false;
    	}
    	 
    	if(isset($params['favorites']) && !empty($params['favorites'])){
    		$this_is_favorites=true;
    	}
    	 
    	if ( $this->getConfigValue('currency_enable') ) {
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/currency/admin/admin.php';
    		$CM=new currency_admin();
    	}
    
    	if(isset($params['_collect_user_info']) && $params['_collect_user_info']==1){
    		$_collect_user_info=true;
    		unset($params['_collect_user_info']);
    	}else{
    		$_collect_user_info=false;
    	}
    
    	$this->grid_total = 0;
    	 
    	$preparedParams=$this->prepareRequestParams($params, $premium);
    	
    	
    	$where_array=$preparedParams['where_array'];
    	$add_from_table=$preparedParams['add_from_table'];
    	$add_select_value=$preparedParams['add_select_value'];
    	$params=$preparedParams['params'];
    	
    	$where_array_prepared=$preparedParams['where_array_prepared'];
    	$where_value_prepared=$preparedParams['where_value_prepared'];
    	 
    	$select_what=$preparedParams['select_what'];
    	$left_joins=$preparedParams['left_joins'];
    	 
    	$left_joins[]='LEFT JOIN '.DB_PREFIX.'_topic ON '.DB_PREFIX.'_data.topic_id='.DB_PREFIX.'_topic.id';
    	 
    	if ( $this->getConfigValue('currency_enable') ) {
    		$select_what[]=DB_PREFIX.'_currency.code AS currency_code';
    		$select_what[]=DB_PREFIX.'_currency.name AS currency_name';
    		$select_what[]='(('.DB_PREFIX.'_data.price*'.DB_PREFIX.'_currency.course)/'.$CM->getCourse(CURRENT_CURRENCY).') AS price_ue';
    		 
    		$left_joins[]='LEFT JOIN '.DB_PREFIX.'_currency ON '.DB_PREFIX.'_data.currency_id='.DB_PREFIX.'_currency.currency_id';
    	}else{
    		$select_what[]=DB_PREFIX.'_data.price AS price_ue';
    	}
    	 
    	 
    	if(isset($params['_no_interactive_search']) && 1==(int)$params['_no_interactive_search']){
			
		}else{
			if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/template_search.php')){
				require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/template_search.php');
				$Template_Search=new Template_Search();
				$results=$Template_Search->run();
				if(isset($results['where'])){
					$where_array=array_merge($where_array, $results['where']);
					$where_array_prepared=array_merge($where_array_prepared, $results['where']);
				}
				if(isset($results['params'])){
					$params=array_merge($params, $results['params']);
				}
			}
		}
		unset($params['_no_interactive_search']);
    	
    	
    
    	if ( count($where_array)>0 ) {
    		$where_statement = " WHERE ".implode(' AND ', $where_array);
    	}
    
    	if ( count($where_array_prepared)>0 ) {
    		$where_statement_prepared = " WHERE ".implode(' AND ', $where_array_prepared);
    	}
    
    	$order=$this->prepareSortOrder($params, $random, $premium);
    
    	
    	if ( !isset($params['page']) || (int)$params['page'] == 0 ) {
    		$page = 1;
    	} else {
    		$page = (int)$params['page'];
    	}
    
      	$query = 'SELECT COUNT('.DB_PREFIX.'_data.id) AS total FROM '.DB_PREFIX.'_data'.(count($left_joins)>0 ? ' '.implode(' ', $left_joins).' ' : '').' '.$where_statement_prepared;
    
    	$DBC=DBC::getInstance();
    	$stmt=$DBC->query($query, $where_value_prepared);
    	
		$total = 0;
    	$this->grid_total = $total;
    	if(!$stmt){
    		$total = 0;
    		$this->grid_total = $total;
    		//return array();
    	}else{
    		$ar=$DBC->fetch($stmt);
    		$total = $ar['total'];
    		$this->grid_total = $total;
    	}
    	//echo $this->grid_total;
    
    	global $smarty;
    
    
    	$pageLimitParams=$this->preparePageLimitParams($params, $page, $total, $premium);
    	$start=$pageLimitParams['start'];
    	$limit=$pageLimitParams['limit'];
    	$max_page=$pageLimitParams['max_page'];
    	$page = (isset($params['page']) ? (int)$params['page'] : 0);

    	if ( $_REQUEST['REST_API'] == 1 ) {
    		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/api/classes/class.static_data.php') ) {
    			$static_data = Static_Data::getInstance();
    			$static_data::set_param('max_page', $max_page);
    		}
    	}
    
    	$pager_params=$params;
    	
    	$mysearch_params=$params;
    	//$_SESSION['mysearch_params']=array();
    	unset($mysearch_params['page']);
    	unset($mysearch_params['order']);
    	unset($mysearch_params['asc']);
    	unset($mysearch_params['favorites']);
    	unset($mysearch_params['search']);
    	unset($mysearch_params['extended_search']);
    	/*
    	if(!empty($mysearch_params)){
    		$_SESSION['mysearch_params']=$mysearch_params;
    	}*/
    	 
    	unset($params['order']);
    	unset($params['asc']);
    	unset($params['favorites']);
    
    	if ( preg_match('/\/special\//', $_SERVER['REQUEST_URI']) ) {
    		unset($params['spec']);
    		unset($pager_params['spec']);
    	}
    	
    	
    
    	if(isset($params['pager_url'])){
    		$pageurl=$params['pager_url'];
    		unset($params['pager_url']);
    		unset($pager_params['pager_url']);
    	}elseif(''!=$is_country_view){
    		unset($pager_params['country_id']);
    		$pageurl=$is_country_view;
    		 
    	}elseif($predefined_info!=''){
    		$pageurl=$predefined_info['alias'];
    		foreach($predefined_info['params'] as $k=>$v){
    			unset($pager_params[$k]);
    		}
    	}elseif($is_city_view){
    		unset($pager_params['city_id']);
    		$pageurl=$is_city_view;
    	}elseif($is_region_view){
    		unset($pager_params['region_id']);
    		$pageurl=$is_region_view;
    	}elseif(''!=$is_complex_view){
    		unset($pager_params['complex_id']);
    		$pageurl=$is_complex_view;
    		 
    	}else{
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    		$Structure_Manager = new Structure_Manager();
    		$category_structure = $Structure_Manager->loadCategoryStructure();
    		if($this_is_favorites){
    			$pageurl='myfavorites';
    			unset($params['favorites']);
    			unset($pager_params['favorites']);
    		}else{
    			if(isset($params['topic_id']) && !is_array($params['topic_id']) && $params['topic_id']!=''){
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
    	}
    	$pager_params['page_url']=$pageurl;
    	 
    	if($paging){
    		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/page_navigator.php')){
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/page_navigator.php';
    			$url='';
    			if(isset($params['pager_url'])){
    				$url=$params['pager_url'];
    				unset($params['pager_url']);
    			}
    	
    			if(isset($params['admin']) && $params['admin']){
    				$nurl='account/data';
    			}else{
    				$nurl=$pageurl;
    			}
    			//print_r($params);
    			$_params=$pager_params;
    			unset($_params['page_url']);
    			$paging=Page_Navigator::getPagingArray($total, $page, $limit, $_params, $nurl);
    			//$this->template->assert('pager_array', $paging);
    		}
    		$return['paging']=$paging;
    		
    		
    		$return['pager']=$this->get_page_links_list ($page, $total, $limit, $pager_params);
    	}
    	
    
    	
    
    
    	
    	
    	
    	
    	//$this->template->assert('pager', $this->get_page_links_list ($page, $total, $limit, $pager_params ));
    	$pairs=array();
    	foreach ( $pager_params as $key => $value ) {
    		if($key=='page_url' || $key=='page_limit'){
    
    		}else{
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
    		 
    	}
    	 
    	if ( is_array($pairs) ) {
    		$url = $pageurl.'?'.implode('&', $pairs);
    	}else{
    		$url = $pageurl.'?key=value';
    	}
    	$return['pagerurl']=$url;
    	//$this->template->assert('pagerurl', $url);
    	 
    	$pairs=array();
    	if($is_country_view){
    		unset($params['country_id']);
    	}
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
    
    	if($is_country_view){
    		if ( is_array($pairs) ) {
    			$url = $is_country_view.'?'.implode('&', $pairs);
    		}else{
    			$url = $is_country_view.'?';
    		}
    	}else{
    		if ( is_array($pairs) ) {
    			$url = $pageurl.'?'.implode('&', $pairs);
    		}else{
    			$url = $pageurl.'?key=value';
    		}
    	}
    	 
    	//$this->template->assert('url', $url);
    	$return['url']=$url;
    	
    	if(count($select_fields)==0){
    		$select_what[]=DB_PREFIX.'_data.*';
    	}else{
    		$select_what=array_merge($select_what, $select_fields);
    	}
    
    	$select_what[]=DB_PREFIX.'_topic.name AS type_sh';
    
    
   
    	
    	$query = 'SELECT '.implode(', ', $select_what).' '.$add_select_value.'
        		FROM '.DB_PREFIX.'_data'.(count($left_joins)>0 ? ' '.implode(' ', $left_joins).' ' : '').' '.$where_statement_prepared.'
        		ORDER BY '.$order.((isset($params['no_portions']) && $params['no_portions']==1) ? '' : ' LIMIT '.$start.', '.$limit);
    	
    	//echo $query.'<br>';
    	//print_r($where_value_prepared);
    	$stmt=$DBC->query($query, $where_value_prepared);
    	
    	    
    	$ra = array();
    	if($stmt){
    		 
    		$i = 0;
    		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/company/company.xml') ) {
    			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
    			$Account = new Account;
    			 
    		}
    		 
    		while($ar=$DBC->fetch($stmt)){
    			if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/company/company.xml') ) {
    				$company_profile = $Account->get_company_profile($ar['user_id']);
    				$ar['company'] = $company_profile['name']['value'];
    			}
    			$ra[$i] = $ar;
    			$i++;
    		}
    	}
    	
    	
    	
    	if(count($ra)>0){
    		$ra=$this->transformGridData($ra, $_collect_user_info);
    	}
    	
    	
    	if($geodata && count($ra)>0){
    		
    		$gdata=array();

    		
    		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_on_map.tpl')){
    			$geotpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_on_map.tpl';
    		}else{
    			$geotpl='';
    		}
    		
    		foreach($ra as $k=>$d){
    			
    			if( isset($d['geo_lat']) && isset($d['geo_lng']) && $d['geo_lat']!='' && $d['geo_lng']!='' ){
    				$gdata[$k]['currency_name']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['currency_name']);
    				if((int)$d['price']!=0){
						$gdata[$k]['price']=number_format($d['price'],0,'.',' ');
					}else{
						$gdata[$k]['price']=$d['price'];
					}
					if(isset($d['type_sh'])){
						$gdata[$k]['type_sh']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['type_sh']);
					}
					$address=array();
					if(isset($d['city'])){
						$address[]=$d['city'];
						$gdata[$k]['city']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['city']);
					}
					if(isset($d['street'])){
						$address[]=$d['street'];
						$gdata[$k]['street']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['street']);
					}
					if(isset($d['number']) && $d['number']!='' && $d['number']!=0){
						$address[]=$d['number'];
					}
					if(isset($d['price'])){
						$address[]=$d['price'];
					}
					
    				$gdata[$k]['title']=SiteBill::iconv(SITE_ENCODING, 'utf-8', implode(', ', $address));
    				if($geotpl!=''){
    					$smarty->assign('realty', $d);
    					$html=$smarty->fetch($geotpl);
    					$html = str_replace("\r\n", ' ', $html);
    					$html = str_replace("\n", ' ', $html);
    					$html = str_replace("\t", ' ', $html);
    					//$html = htmlspecialchars($html);
    					$html = addslashes($html);
    				}else{
    					$html = '';
    				}
    		
    		
    				$gdata[$k]['html']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $html);
    				$gdata[$k]['geo_lat']=$d['geo_lat'];
    				$gdata[$k]['geo_lng']=$d['geo_lng'];
    				$gdata[$k]['href']=$d['href'];
    				$gdata[$k]['parent_category_url']=$d['parent_category_url'];
    				
    				unset($html);
    			}
    		}
    		
    		
    		$geoobjects_collection=array();
    		if(count($gdata)>0){
    			foreach ($gdata as $gd){
    				$gc=$gd['geo_lat'].'_'.$gd['geo_lng'];
    				if(isset($geoobjects_collection[$gc])){
    					$geoobjects_collection[$gc]['html'].=$gd['html'];
    					$geoobjects_collection[$gc]['count']++;
    				}else{
    					$geoobjects_collection[$gc]['lat']=$gd['geo_lat'];
    					$geoobjects_collection[$gc]['lng']=$gd['geo_lng'];
    					$geoobjects_collection[$gc]['html']=$gd['html'];
    					$geoobjects_collection[$gc]['count']=1;
    				}
    			}
    		}
    		
    		$return['geoobjects_collection_clustered']=$geoobjects_collection;
    		$return['grid_geodata']=$this->generateGridGeoDataOld($ra);
    		$grid_geodata=array();
    	}
    	
    	$return['_total_records']=$total;
    	$return['_max_page']=$max_page;
    	$return['_params']=$params;
    	$return['_mysearch_params']=$mysearch_params;
    	 
    	$return['data']=$ra;
    	$return['order']=$order;
    	return $return;
    }
    
    function get_sitebill_adv_ext_base_ajax( $params, $random = false, $premium=false, $paging=true, $geodata=false ) {
    	
    	$data=$this->get_sitebill_adv_core($params, $random, $premium, true, true);
    	
    	 
    	return $data;
    	
    	$return=array();
    	//$this->_grid($params);
    	 
    	$is_country_view=$this->getRequestValue('country_view');
    	 
    	 
    	$this_is_favorites=false;
    	 
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable')==1){
    		$_billing_on=true;
    	}else{
    		$_billing_on=false;
    	}
    	 
    	if(isset($params['favorites']) && !empty($params['favorites'])){
    		$this_is_favorites=true;
    	}
    	 
    	if ( $this->getConfigValue('currency_enable') ) {
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/currency/admin/admin.php';
    		$CM=new currency_admin();
    	}
    
    	if(isset($params['_collect_user_info']) && $params['_collect_user_info']==1){
    		$_collect_user_info=true;
    		unset($params['_collect_user_info']);
    	}else{
    		$_collect_user_info=false;
    	}
    
    	$this->grid_total = 0;
    	 
    	 
    	//collect WHERE parts
    	$preparedParams=$this->prepareRequestParams($params, $premium);
    	
    	$where_array=$preparedParams['where_array'];
    	$add_from_table=$preparedParams['add_from_table'];
    	$add_select_value=$preparedParams['add_select_value'];
    	$params=$preparedParams['params'];
    	 
		$where_array_prepared=$preparedParams['where_array_prepared'];
    	$where_value_prepared=$preparedParams['where_value_prepared'];
		
    	$select_what=$preparedParams['select_what'];
    	$left_joins=$preparedParams['left_joins']; 
		
		$left_joins[]='LEFT JOIN '.DB_PREFIX.'_topic ON '.DB_PREFIX.'_data.topic_id='.DB_PREFIX.'_topic.id';
    	
    	if ( $this->getConfigValue('currency_enable') ) {
    		$select_what[]=DB_PREFIX.'_currency.code AS currency_code';
    		$select_what[]=DB_PREFIX.'_currency.name AS currency_name';
    		$select_what[]='(('.DB_PREFIX.'_data.price*'.DB_PREFIX.'_currency.course)/'.$CM->getCourse(CURRENT_CURRENCY).') AS price_ue';
    		 
    		$left_joins[]='LEFT JOIN '.DB_PREFIX.'_currency ON '.DB_PREFIX.'_data.currency_id='.DB_PREFIX.'_currency.currency_id';
    	}else{
    		$select_what[]=DB_PREFIX.'_data.price AS price_ue';
    	}
    	 
    	//append user vars
    
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/template_search.php')){
        	require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/template_search.php');
        	$Template_Search=new Template_Search();
        	$results=$Template_Search->run();
        	if(isset($results['where'])){
        		$where_array=array_merge($where_array, $results['where']);
        		$where_array_prepared=array_merge($where_array_prepared, $results['where']);
        	}
        	if(isset($results['params'])){
        		$params=array_merge($params, $results['params']);
        	}
        }
    
    
    
    	if ( count($where_array)>0 ) {
            $where_statement = " WHERE ".implode(' AND ', $where_array);
        }
        
        if ( count($where_array_prepared)>0 ) {
        	$where_statement_prepared = " WHERE ".implode(' AND ', $where_array_prepared);
        }
    
    	$order=$this->prepareSortOrder($params, $random, $premium);
    
    	/*
    	 require_once SITEBILL_DOCUMENT_ROOT.'/apps/activelinker/admin/admin.php';
    	$AL=new activelinker_admin();
    	$this->template->assert('activelinker_desc', $AL->generate($params));
    	*/
    	if ( !isset($params['page']) or $params['page'] == 0 ) {
    		$page = 1;
    	} else {
    		$page = $params['page'];
    	}
    
    
    
    
    	$query = 'SELECT COUNT('.DB_PREFIX.'_data.id) AS total FROM '.DB_PREFIX.'_data'.(count($left_joins)>0 ? ' '.implode(' ', $left_joins).' ' : '').' '.$where_statement_prepared;
        
    	$DBC=DBC::getInstance();
		$stmt=$DBC->query($query, $where_value_prepared);
		
		
    	$total = 0;
		$this->grid_total = $total;
		if(!$stmt){
			$total = 0;
			$this->grid_total = $total;
			//return array();
		}else{
			$ar=$DBC->fetch($stmt);
			$total = $ar['total'];
			$this->grid_total = $total;
		}
		
		global $smarty;
		
    	
    
    
    	$pageLimitParams=$this->preparePageLimitParams($params, $page, $total, $premium);
		$start=$pageLimitParams['start'];
		$limit=$pageLimitParams['limit'];
		$max_page=$pageLimitParams['max_page'];
		$page = (int)$params['page'];
		
		
		$pager_params=$params;
    
    	if ( preg_match('/\/special\//', $_SERVER['REQUEST_URI']) ) {
    		unset($params['spec']);
    		unset($pager_params['spec']);
    	}
    
    	if(''!=$is_country_view){
    		unset($pager_params['country_id']);
    		$pageurl=$is_country_view;
    		 
    	}else{
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    		$Structure_Manager = new Structure_Manager();
    		$category_structure = $Structure_Manager->loadCategoryStructure();
    		if($this_is_favorites){
    			$pageurl='myfavorites';
    		}else{
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
    	}
    	 
    
    	if($paging){
    		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/page_navigator.php')){
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/page_navigator.php';
    			$url='';
    			if(isset($params['pager_url'])){
    				$url=$params['pager_url'];
    				unset($params['pager_url']);
    			}
    			 
    			if($params['admin']){
    				$nurl='account/data';
    			}else{
    				$nurl=$pageurl;
    			}
    			//print_r($params);
    			 
    			$paging=Page_Navigator::getPagingArray($total, $page, $limit, $pager_params, $nurl);
    			//$this->template->assert('pager_array', $paging);
    		}
    		$return['paging']=$paging;
    	}
    	
    
    
    
    	$select_what[]=DB_PREFIX.'_data.*';
        $select_what[]=DB_PREFIX.'_topic.name AS type_sh';
        
        
        
        
        if ( $this->getConfigValue('apps.company.timelimit') ) {
        	$query = 'SELECT '.implode(', ', $select_what).' '.$add_select_value.'
        		FROM '.DB_PREFIX.'_data'.(count($left_joins)>0 ? ' '.implode(' ', $left_joins).' ' : '').' '.$where_statement_prepared.'
        		ORDER BY '.$order.($params['no_portions']==1 ? '' : ' LIMIT '.$start.', '.$limit);
        	/*if ( $this->getConfigValue('currency_enable') ) {
        		$query = 'SELECT '.implode(', ', $select_what).' '.$add_select_value.' 
        				FROM '.DB_PREFIX.'_data '.(count($left_joins)>0 ? implode(' ', $left_joins).' ' : '').'
        				re_topic '.' '.$where_statement_prepared.' 
        				ORDER BY '.$order.($params['no_portions']==1 ? '' : ' LIMIT '.$start.', '.$limit);
        	} else {
        		$query = 'SELECT '.implode(', ', $select_what).' '.$add_select_value.' 
        				FROM '.DB_PREFIX.'_data, '.DB_PREFIX.'_topic '.$add_from_table.' '.$where_statement_prepared.' 
        				ORDER BY '.$order.($params['no_portions']==1 ? '' : ' LIMIT '.$start.', '.$limit);
        	}*/
        	 
        } else {
        	$query = 'SELECT '.implode(', ', $select_what).' '.$add_select_value.'
        		FROM '.DB_PREFIX.'_data'.(count($left_joins)>0 ? ' '.implode(' ', $left_joins).' ' : '').' '.$where_statement_prepared.'
        		ORDER BY '.$order.($params['no_portions']==1 ? '' : ' LIMIT '.$start.', '.$limit);
        	/*if ( $this->getConfigValue('currency_enable') ) {
        		$query = 'SELECT '.implode(', ', $select_what).' '.$add_select_value .'
        		FROM '.DB_PREFIX.'_data'.(count($left_joins)>0 ? ' '.implode(' ', $left_joins).' ' : '').'
        		'.$where_statement_prepared .'
        		ORDER BY '.$order.($params['no_portions']==1 ? '' : ' LIMIT '.$start.', '.$limit);
        	} else {
        		$query = 'SELECT '.implode(', ', $select_what).' '.$add_select_value.' 
        		FROM '.DB_PREFIX.'_data'.(count($left_joins)>0 ? ' '.implode(' ', $left_joins).' ' : '').' '.$where_statement_prepared.' 
        		ORDER BY '.$order.($params['no_portions']==1 ? '' : ' LIMIT '.$start.', '.$limit);
        	}*/
        }
       // echo $query.'<br>';
       // print_r($where_value_prepared);
       
        $stmt=$DBC->query($query, $where_value_prepared);
    
    	$ra = array();
        if($stmt){
        	
        	$i = 0;
        	if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/company/company.xml') ) {
        		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
        		$Account = new Account;
        	
        	}
        	
        	while($ar=$DBC->fetch($stmt)){
        		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/company/company.xml') ) {
        			$company_profile = $Account->get_company_profile($ar['user_id']);
        			$ar['company'] = $company_profile['name']['value'];
        		}
        		$ra[$i] = $ar;
        		$i++;
        	}
        }
    	
    
    	if(count($ra)>0){
			$ra=$this->transformGridData($ra, $_collect_user_info);
		}
		
    	 
    	 
    	
    	if($geodata){
    		$gdata=array();
    		foreach($ra as $k=>$d){
    			if( isset($d['geo_lat']) && isset($d['geo_lng']) && $d['geo_lat']!='' && $d['geo_lng']!='' ){
    		
    		
    				$gdata[$k]['currency_name']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['currency_name']);
    				$gdata[$k]['city']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['city']);
    				$gdata[$k]['street']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['street']);
    				$gdata[$k]['price']=number_format($d['price'],0,'.',' ');
    				$gdata[$k]['type_sh']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['type_sh']);
    				$gdata[$k]['title']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['city'].' '.$d['street'].(($d['number']!='' && $d['number']!=0) ? ', '.$d['number'] : '').' ('.$data[$k]['price'].')');
    				if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_on_map.tpl')){
    					$smarty->assign('realty',$d);
    					$html=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_on_map.tpl');
    					$html = str_replace("\r\n", ' ', $html);
    					$html = str_replace("\n", ' ', $html);
    					$html = str_replace("\t", ' ', $html);
    					//$html = htmlspecialchars($html);
    					$html = addslashes($html);
    				}else{
    					$html = '';
    				}
    		
    		
    				$gdata[$k]['html']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $html);
    				$gdata[$k]['geo_lat']=$d['geo_lat'];
    				$gdata[$k]['geo_lng']=$d['geo_lng'];
    				$gdata[$k]['href']=$d['href'];
    				$gdata[$k]['parent_category_url']=$d['parent_category_url'];
    			}
    		}
    		$geoobjects_collection=array();
    		if(count($gdata)>0){
    			foreach ($gdata as $gd){
    				$gc=$gd['geo_lat'].'_'.$gd['geo_lng'];
    				if(isset($geoobjects_collection[$gc])){
    					$geoobjects_collection[$gc]['html'].=$gd['html'];
    					$geoobjects_collection[$gc]['count']++;
    				}else{
    					$geoobjects_collection[$gc]['lat']=$gd['geo_lat'];
    					$geoobjects_collection[$gc]['lng']=$gd['geo_lng'];
    					$geoobjects_collection[$gc]['html']=$gd['html'];
    					$geoobjects_collection[$gc]['count']=1;
    				}
    			}
    		}
    		//$this->template->assert('geoobjects_collection_clustered', json_encode($geoobjects_collection));
    		$return['geoobjects_collection_clustered']=json_encode($geoobjects_collection);
    		$return['grid_geodata']=json_encode($this->generateGridGeoDataOld($ra));
    		
    		//$this->template->assert('geoobjects_collection', json_encode($gdata));
    		//}
    		
    		//LEGACY BLOCK. For old templates only!
    		$grid_geodata=array();
    		//$this->template->assert('grid_geodata', json_encode($this->generateGridGeoDataOld($ra)));
    	}
    	
    	$return['_total_records']=$total;
    	$return['_max_page']=$max_page;
    	
    	$return['data']=$ra;
    	$return['order']=$order;
    	return $return;
    
    
    	$this->template->assert('_total_records', $total);
    	$this->template->assert('_max_page', $max_page);
    
    
    	return $ra;
    }
    
    function getTranslitAlias($city,$street,$number){
    	if($city!=''){
    		$p[]=$this->transliteMe($city);
    	}
    	if($street!=''){
    		$p[]=$this->transliteMe($street);
    	}
    	if((int)$number!=0){
    		$p[]=(int)$number;
    	}
    	return implode('-',$p);
    }
    /*
    function get_sitebill_adv_ext_modern( $params, $random = false ) {
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$base_model=$data_model->get_kvartira_model();
    	$data_table_name=array_shift(array_keys($base_model));
    	$model_fields=array_keys($base_model[$data_table_name]);
    	foreach($base_model[$data_table_name] as $model_field){
    		if($model_field['type']=='select_by_query'){
    			$_table=DB_PREFIX.'_'.$model_field['primary_key_table'];
    			$sw[]=$_table.'.'.$model_field['primary_key_table'].' AS '.str_replace('_id', '', $model_field['name']);
    			$ljs[]='LEFT JOIN '.$_table.' ON '.$_table.'.'.$model_field['primary_key_name'].'='.DB_PREFIX.'_'.$data_table_name.'.'.$model_field['name'];
    		}
    	}
    	$_table_topic=DB_PREFIX.'_topic';
    	$_table_data=DB_PREFIX.'_data';
    	$_table_city=DB_PREFIX.'_city';
    	$_table_district=DB_PREFIX.'_district';
    	$_table_street=DB_PREFIX.'_street';
    	$_table_metro=DB_PREFIX.'_metro';
    	$_table_country=DB_PREFIX.'_country';
    	
    	$query_start='SELECT';
    	$select_what=array();
    	$query_joins=array();
    	
    	$select_what[]=$_table_data.'.*';
    	
    	if(in_array('topic_id',$model_fields)){
    		$select_what[]=$_table_topic.'.name AS type_sh';
    		$query_joins[]='LEFT JOIN '.$_table_topic.' ON '.$_table_topic.'.id='.$_table_data.'.topic_id';
    	}
    	
    	if(in_array('district_id',$model_fields)){
    		$select_what[]=$_table_district.'.name AS district';
    		$query_joins[]='LEFT JOIN '.$_table_district.' ON '.$_table_district.'.id='.$_table_data.'.district_id';
    	}
    	
    	if(in_array('city_id',$model_fields)){
    		$select_what[]=$_table_city.'.name AS city';
    		$query_joins[]='LEFT JOIN '.$_table_city.' ON '.$_table_city.'.city_id='.$_table_data.'.city_id';
    	}
    	
    	if(in_array('metro_id',$model_fields)){
    		$select_what[]=$_table_metro.'.name AS metro';
    		$query_joins[]='LEFT JOIN '.$_table_metro.' ON '.$_table_metro.'.metro_id='.$_table_data.'.metro_id';
    	}
    	
    	if(in_array('street_id',$model_fields)){
    		$select_what[]=$_table_street.'.name AS street';
    		$query_joins[]='LEFT JOIN '.$_table_street.' ON '.$_table_street.'.street_id='.$_table_data.'.street_id';
    	}
    	
    	if(in_array('country_id',$model_fields)){
    		$select_what[]=$_table_country.'.name AS country';
    		$query_joins[]='LEFT JOIN '.$_table_country.' ON '.$_table_country.'.country_id='.$_table_data.'.country_id';
    	}
    	
    	$query_joins[]='LEFT JOIN '.DB_PREFIX.'_user ON '.$_table_data.'.user_id='.DB_PREFIX.'_user.user_id';
    	
    	
    	
    	$select_what[]=DB_PREFIX.'_user.fio AS user';
    	
    	if(count($query_joins)>0){
    		$query_join=implode(' ',$query_joins);
    	}
    	
    	
    	 
    	if ( $this->getConfigValue('currency_enable') ) {
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/currency/admin/admin.php';
    		$CM=new currency_admin();
    		
    		$select_what[]=DB_PREFIX.'_currency.code AS currency_code';
    		$select_what[]=DB_PREFIX.'_currency.name AS currency_name';
    		$select_what[]='(('.$_table_data.'.price*'.DB_PREFIX.'_currency.course)/'.$CM->getCourse(CURRENT_CURRENCY).') AS price_ue';
    		
    		$query_join.=' LEFT JOIN '.DB_PREFIX.'_currency ON '.$_table_data.'.currency_id='.DB_PREFIX.'_currency.currency_id';
    	}
    
    	$this->grid_total = 0;
    	$where_array = false;
 
    
    	if(isset($params['favorites']) && !empty($params['favorites'])){
    		$where_array[] = DB_PREFIX.'_data.id IN ('.implode(',',$params['favorites']).')';
    	}
    
    	 
    
    	if(isset($params['optype'])){
    		$where_array[] = DB_PREFIX.'_data.optype='.(int)$params['optype'];
    	}
    
    	
    
    	if ( $params['topic_id'] != '' &&  $params['topic_id'] != 0) {
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    		$Structure_Manager = new Structure_Manager();
    		$category_structure = $Structure_Manager->loadCategoryStructure();
    		global $smarty;
    		//echo $category_structure['catalog'][$params['topic_id']]['description'];
    		$smarty->assign('topic_description', $category_structure['catalog'][$params['topic_id']]['description']);
    
    		$childs = $Structure_Manager->get_all_childs($params['topic_id'], $category_structure);
    		if ( count($childs) > 0 ) {
    			array_push($childs, $params['topic_id']);
    			$where_array[] = 're_data.topic_id IN ('.implode(' , ',$childs).') ';
    		} else {
    			$where_array[] = 're_data.topic_id='.$params['topic_id'];
    		}
    		
    	}
    
    	if ( isset($params['country_id']) and $params['country_id'] != 0  ) {
    		$where_array[] = 're_data.country_id = '.$params['country_id'];
    	}else{
    		unset($params['country_id']);
    	}
    
    	if ( isset($params['id']) and $params['id'] != 0  ) {
    		$where_array[] = 're_data.id = '.$params['id'];
    	}
    
    	if ( isset($params['mvids']) && is_array($params['mvids']) && count($params['mvids']) != 0  ) {
    		$where_array[] = 're_data.id IN ('.implode(',',$params['mvids']).')';
    	}
    
    
    	if ( isset($params['user_id']) && $params['user_id'] > 0  ) {
    		$where_array[] = 're_data.user_id = '.$params['user_id'];
    	}
    
    	if ( isset($params['onlyspecial']) && $params['onlyspecial'] > 0  ) {
    		$where_array[] = 're_data.hot = 1';
    	}
    
    
    	if ( isset($params['price']) && $params['price'] != 0  ) {
    		$where_array[] = 're_data.price  <= '.$params['price'];
    	}
    
    	if ( isset($params['price_min']) && $params['price_min'] != 0  ) {
    		$where_array[] = 're_data.price  >= '.$params['price_min'];
    	}
    
    	if ( isset($params['house_number']) && $params['house_number'] != 0  ) {
    		$where_array[] = 're_data.number  = \''.$params['house_number'].'\'';
    	}else{
    		unset($params['house_number']);
    	}
    
    
    	if ( isset($params['region_id']) && $params['region_id'] != 0 ) {
    		$where_array[] = 're_data.region_id = '.$params['region_id'];
    	}else{
    		unset($params['region_id']);
    	}
    
    	if ( isset($params['spec']) ) {
    		$where_array[] = ' re_data.hot = 1 ';
    	}
    	if ( isset($params['hot']) ) {
    		$where_array[] = ' re_data.hot = 1 ';
    	}
    	if ( isset($params['city_id']) and $params['city_id'] != 0  ) {
    		$where_array[] = 're_data.city_id = '.$params['city_id'];
    	}
    	if ( isset($params['district_id']) and $params['district_id'] != 0  ) {
    		$where_array[] = 're_data.district_id = '.$params['district_id'];
    	}else{
    		unset($params['district_id']);
    	}
    	if ( isset($params['metro_id']) and $params['metro_id'] != 0  ) {
    		$where_array[] = 're_data.metro_id = '.$params['metro_id'];
    	}else{
    		unset($params['metro_id']);
    	}
    	if ( isset($params['street_id']) and $params['street_id'] != 0  ) {
    		$where_array[] = 're_data.street_id = '.$params['street_id'];
    	}else{
    		unset($params['street_id']);
    	}
    	
    	if(isset($params['srch_phone']) and $params['srch_phone'] !== NULL){
    		$phone = preg_replace('/[^\d]/', '', $params['srch_phone']);
    		$sub_where=array();
    		if($this->getConfigValue('allow_additional_mobile_number')){
    			$sub_where[] = '(re_data.ad_mobile_phone LIKE \'%'.$phone.'%\')';
    		}
    		if($this->getConfigValue('allow_additional_stationary_number')){
    			$sub_where[] = '(re_data.ad_stacionary_phone LIKE \'%'.$phone.'%\')';
    		}
    		$sub_where[] = '(re_data.phone LIKE \'%'.$phone.'%\')';
    		$where_array[]='('.implode(' OR ',$sub_where).')';
    	}
    	
    	if(isset($params['srch_word']) and $params['srch_word'] !== NULL){
    		$sub_where=array();
    		$word=htmlspecialchars($params['srch_word']);
    		$sub_where[] = '(re_data.text LIKE \'%'.$word.'%\')';
    		$sub_where[] = '(re_data.more1 LIKE \'%'.$word.'%\')';
    		$sub_where[] = '(re_data.more2 LIKE \'%'.$word.'%\')';
    		$sub_where[] = '(re_data.more3 LIKE \'%'.$word.'%\')';
    		$where_array[]='('.implode(' OR ',$sub_where).')';
    	}
    
    	if(isset($params['room_count'])){
    		if(is_array($params['room_count']) && count($params['room_count'])>0){
    			$sub_where=array();
    			foreach($params['room_count'] as $rq){
    				if($rq==4){
    					$sub_where[]='room_count>3';
    				}elseif(0!=(int)$rq){
    					$sub_where[]='room_count='.(int)$rq;
    				}
    			}
    			if(count($sub_where)>0){
    				$where_array[]='('.implode(' OR ',$sub_where).')';
    			}
    		}else{
    			unset($params['room_count']);
    		}
    	}
    
    	if($params['srch_date_from']!=0 && $params['srch_date_to']!=0){
    		$where_array[]="((re_data.date_added>='".$params['srch_date_from']."') AND (re_data.date_added<='".$params['srch_date_to']."'))";
    	}elseif($params['srch_date_from']!=0){
    		$where_array[]="(re_data.date_added>='".$params['srch_date_from']."')";
    	}elseif($params['srch_date_to']!=0){
    		$where_array[]="(re_data.date_added<='".$params['srch_date_to']."')";
    	}
    
    	if($params['floor_min']!=0 && $params['floor_max']!=0){
    		$where_array[]="(re_data.floor BETWEEN ".$params['floor_min']." AND ".$params['floor_max'].")";
    	}elseif($params['floor_min']!=0){
    		$where_array[]="(re_data.floor>=".$params['floor_min'].")";
    	}elseif($params['floor_max']!=0){
    		$where_array[]="(re_data.floor<=".$params['floor_max'].")";
    	}
    
    	if($params['floor_count_min']!=0 && $params['floor_count_max']!=0){
    		$where_array[]="(re_data.floor_count BETWEEN ".$params['floor_count_min']." AND ".$params['floor_count_max'].")";
    	}elseif($params['floor_count_min']!=0){
    		$where_array[]="(re_data.floor_count>=".$params['floor_count_min'].")";
    	}elseif($params['floor_count_max']!=0){
    		$where_array[]="(re_data.floor_count<=".$params['floor_count_max'].")";
    	}
    
    	if($params['square_min']!=0 && $params['square_max']!=0){
    		$where_array[]="(re_data.square_all BETWEEN ".$params['square_min']." AND ".$params['square_max'].")";
    	}elseif($params['square_min']!=0){
    		$where_array[]="(re_data.square_all>=".$params['square_min'].")";
    	}elseif($params['square_max']!=0){
    		$where_array[]="(re_data.square_all<=".$params['square_max'].")";
    	}
    
    	if($params['is_phone']==1){
    		$where_array[]="(re_data.is_telephone=1)";
    	}else{
    		unset($params['is_phone']);
    	}
    
    	if($params['is_internet']==1){
    		$where_array[]="(re_data.is_internet=1)";
    	}else{
    		unset($params['is_internet']);
    	}
    
    	if($params['is_furniture']==1){
    		$where_array[]="(re_data.furniture=1)";
    	}else{
    		unset($params['is_furniture']);
    	}
    
    	if($params['owner']==1){
    		$where_array[]="(re_data.whoyuaare=1)";
    	}else{
    		unset($params['is_furniture']);
    	}
    
    	if($params['has_photo']==1){
    		$where_array[]='((SELECT COUNT(*) FROM '.DB_PREFIX.'_data_image WHERE id='.DB_PREFIX.'_data.id)>0)';
    	}else{
    		unset($params['has_photo']);
    	}
    
    	if($params['infra_greenzone']==1){
    		$where_array[]="(re_data.infra_greenzone=1)";
    	}else{
    		unset($params['infra_greenzone']);
    	}
    
    	if($params['infra_sea']==1){
    		$where_array[]="(re_data.infra_sea=1)";
    	}else{
    		unset($params['infra_sea']);
    	}
    
    	if($params['infra_sport']==1){
    		$where_array[]="(re_data.infra_sport=1)";
    	}else{
    		unset($params['infra_sport']);
    	}
    
    	if($params['infra_clinic']==1){
    		$where_array[]="(re_data.infra_clinic=1)";
    	}else{
    		unset($params['infra_clinic']);
    	}
    
    	if($params['infra_terminal']==1){
    		$where_array[]="(re_data.infra_terminal=1)";
    	}else{
    		unset($params['infra_terminal']);
    	}
    
    	if($params['infra_airport']==1){
    		$where_array[]="(re_data.infra_airport=1)";
    	}else{
    		unset($params['infra_airport']);
    	}
    
    	if($params['infra_bank']==1){
    		$where_array[]="(re_data.infra_bank=1)";
    	}else{
    		unset($params['infra_bank']);
    	}
    
    	if($params['infra_restaurant']==1){
    		$where_array[]="(re_data.infra_restaurant=1)";
    	}else{
    		unset($params['infra_restaurant']);
    	}
    
    	if(isset($params['object_state']) && is_array($params['object_state']) && count($params['object_state'])>0){
    		$where_array[]="(re_data.object_state IN (".implode(',', $params['object_state'])."))";
    	}else{
    		unset($params['object_state']);
    	}
    
    	if(isset($params['object_type']) && is_array($params['object_type']) && count($params['object_type'])>0){
    		$where_array[]="(re_data.object_type IN (".implode(',', $params['object_type'])."))";
    	}else{
    		unset($params['object_type']);
    	}
    
    	if(isset($params['object_destination']) && is_array($params['object_destination']) && count($params['object_destination'])>0){
    		$where_array[]="(re_data.object_destination IN (".implode(',', $params['object_destination'])."))";
    	}else{
    		unset($params['object_destination']);
    	}
    
    	if(isset($params['aim']) && is_array($params['aim']) && count($params['aim'])>0){
    		$where_array[]="(re_data.aim IN (".implode(',', $params['aim'])."))";
    	}else{
    		unset($params['aim']);
    	}
    
    	if($params['has_geo']==1){
    		$where_array[]='('.DB_PREFIX.'_data.geo_lat IS NOT NULL AND '.DB_PREFIX.'_data.geo_lng IS NOT NULL)';
    	}
    
    
    	
    	if ( $params['admin'] != 1 ) {
    		$where_array[] = 're_data.active=1';
    	} elseif ( $params['active'] == 1 ) {
    		$where_array[] = 're_data.active=1';
    	} elseif ( $params['active'] == 'notactive' ) {
    		$where_array[] = 're_data.active=0';
    	}
    
    	if ( $this->getConfigValue('apps.company.timelimit') ) {
    		$current_time = time();
    		
    		$query_join.=' LEFT JOIN '.DB_PREFIX.'_company ON '.DB_PREFIX.'_user.company_id='.DB_PREFIX.'_company.company_id';
    		$where_array[] = DB_PREFIX."_company.start_date <= ".$current_time;
    		$where_array[] = DB_PREFIX."_company.end_date >= ".$current_time;
    		
    	}
    
    	if ( $params['only_img'] ) {
    		 
    		$where_array[] = 're_data.id=i.id';
    		$add_from_table .= ' , re_data_image i ';
    	}
    
    
    	if ( $where_array ) {
    		$where_statement = " WHERE ".implode(' AND ', $where_array);
    	}
    
    	if ( isset($params['order']) ) {
    
    		if ( !isset($params['asc']) ) {
    			$asc = 'desc';
    		}
    		if ($params['asc'] == 'asc')  {
    			$asc = 'asc';
    		}elseif ($params['asc'] == 'desc') {
    			$asc = 'desc';
    		}else{
    			$asc = 'desc';
    		}
    		//
    		if     ( $params['order'] == 'type' ) $order = 'type_sh ';
    		elseif ( $params['order'] == 'street' ) $order = $_table_street.'.name ';
    		elseif ( $params['order'] == 'district' ) $order = $_table_district.'.name ';
    		elseif ( $params['order'] == 'metro' ) $order = $_table_metro.'.name ';
    		elseif ( $params['order'] == 'city' ) $order = $_table_city.'.name ';
    		elseif ( $params['order'] == 'date_added' ) $order = 're_data.date_added ';
    		elseif ( $params['order'] == 'price' ){
    			if ( $this->getConfigValue('currency_enable') ) {
    				$order = 'price_ue ';
    			}else{
    				$order = 'price ';
    			}
    		}
    
    		$order .= $asc;
    	} else {
    		
    		$order = "re_data.date_added DESC, re_data.id DESC";
    	}
    
    	if ( !isset($params['page']) or $params['page'] == 0 ) {
    		$page = 1;
    	} else {
    		$page = $params['page'];
    	}
    
    
    	if ( $random ) {
    		$order = ' rand() ';
    	}
    	$query = "select count(re_data.id) as total from re_data ".$query_join." $add_from_table $where_statement ";
    	
    
    	$this->db->exec($query);
    	if ( !$this->db->success ) {
    		echo $this->db->error.'<br>';
    	}
    	$this->db->fetch_assoc();
    	$total = $this->db->row['total'];
    	$this->grid_total = $total;
    	global $smarty;
    	$smarty->assign('_total_records',$total);
    
    	
    	$limit = $this->getConfigValue('per_page');
    	$max_page=ceil($total/$limit);
    	
    	if($page>$max_page){
    		$page=1;
    		$params['page']=1;
    	}
    
    	$start = ($page-1)*$limit;
    
    	$pager_params=$params;
    	
    	 
    	unset($params['order']);
    	unset($params['asc']);
    	unset($params['favorites']);
    
    	 
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	$Structure_Manager = new Structure_Manager();
    	$category_structure = $Structure_Manager->loadCategoryStructure();
    
    	
    	if($params['topic_id']!=''){
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
    				unset($params['topic_id']);
    			}elseif($category_structure['catalog'][$params['topic_id']]['url']!=''){
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
    	$this->template->assert('pager', $this->get_page_links_list ($page, $total, $limit, $pager_params ));
    	 
    	 
    	 
    
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
    				//echo "key = $key, value = $value<br>";
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
    	
    
    
    	
    	
    	$query = "SELECT ".implode(',',$select_what)." FROM re_data ".$query_join." ".$add_from_table." ".$where_statement." ORDER BY ".$order.($params['no_portions']==1 ? '' : " LIMIT ".$start.", ".$limit);
    	
    	$this->db->exec($query);
    	if ( !$this->db->success ) {
    		echo $this->db->error.'<br>';
    	}
    
    	$ra = array();
    	$i = 0;
    	if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/company/company.xml') ) {
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
    		$Account = new Account;
    
    	}
    
    	while ( $this->db->fetch_assoc() ) {
    		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/company/company.xml') ) {
    			$company_profile = $Account->get_company_profile($this->db->row['user_id']);
    			
    			$this->db->row['company'] = $company_profile['name']['value'];
    		}
    		$ra[$i] = $this->db->row;
    		$i++;
    	}
    	 
    	
    
    
    	
    	$params = array();
    	
    	
    
    	foreach ( $ra as $item_id => $item_array ) {
    		$params['topic_id'] = $item_array['topic_id'];
    
    		$ra[$item_id]['path'] = $this->get_category_breadcrumbs_string( $params, $category_structure );
    		$ra[$item_id]['date'] = date('d.m',strtotime($ra[$item_id]['date_added']));
    
    
    		$image_array = $data_model->get_image_array ( 'data', 'data', 'id', $item_array['id'], 1 );
    		if ( count($image_array) > 0 ) {
    			$ra[$item_id]['img'] = $image_array;
    		}
    
    
    		if(1==$this->getConfigValue('apps.seo.level_enable')){
    			 
    			if($category_structure['catalog'][$ra[$item_id]['topic_id']]['url']!=''){
    				$ra[$item_id]['parent_category_url']=$category_structure['catalog'][$ra[$item_id]['topic_id']]['url'].'/';
    			}else{
    				$ra[$item_id]['parent_category_url']='';
    			}
    		}else{
    			$ra[$item_id]['parent_category_url']='';
    		}
    		if(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
    			$ra[$item_id]['href']=SITEBILL_MAIN_URL.'/'.$ra[$item_id]['parent_category_url'].'realty'.$ra[$item_id]['id'].'.html';
    		}elseif(1==$this->getConfigValue('apps.seo.data_alias_enable')){
    			
    		}else{
    			$ra[$item_id]['href']=SITEBILL_MAIN_URL.'/'.$ra[$item_id]['parent_category_url'].'realty'.$ra[$item_id]['id'];
    		}
   
    		
    
    	}
    	
    
    	
    	return $ra;
    }
    */
    function get_sitebill_adv_ext2( $params, $random = false ) {
    	$QB=new Query_Builder();
    	$QB->addSelectFrom(DB_PREFIX.'_data');
    	$QB->addSelectWhat(DB_PREFIX.'_data.*');
    	$QB->addSelectWhat(DB_PREFIX.'_topic.name AS topic');
    	$QB->addSelectWhat(DB_PREFIX.'_city.name AS city');
    	$QB->addSelectWhat(DB_PREFIX.'_street.name AS street');
    	$QB->addSelectWhat(DB_PREFIX.'_district.name AS district');
    	$QB->addLeftJoin(DB_PREFIX.'_topic', DB_PREFIX.'_topic.id='.DB_PREFIX.'_data.topic_id');
    	$QB->addLeftJoin(DB_PREFIX.'_city', DB_PREFIX.'_city.city_id='.DB_PREFIX.'_data.city_id');
    	$QB->addLeftJoin(DB_PREFIX.'_street', DB_PREFIX.'_street.street_id='.DB_PREFIX.'_data.street_id');
    	$QB->addLeftJoin(DB_PREFIX.'_district', DB_PREFIX.'_district.id='.DB_PREFIX.'_data.district_id');
    	 
    	echo $QB->build();
    	 
    	if ( $this->getConfigValue('currency_enable') ) {
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/currency/admin/admin.php';
    		$CM=new currency_admin();
    	}
    
    	$this->grid_total = 0;
    	$where_array = false;
    
    	if ( $params['order'] == 'city' ) {
    		 
    		$where_array[] = 're_city.city_id=re_data.city_id';
    		$add_from_table .= ' , re_city ';
    		$add_select_value .= ' , re_city.name as city ';
    	}
    
    	if ( $params['order'] == 'district' ) {
    		$where_array[] = 're_district.id=re_data.district_id';
    		$add_from_table .= ' , re_district ';
    		$add_select_value .= ' , re_district.name as district ';
    	}
    
    	if ( $params['order'] == 'metro' ) {
    		$where_array[] = 're_metro.metro_id=re_data.metro_id';
    		$add_from_table .= ' , re_metro ';
    		$add_select_value .= ' , re_metro.name as metro ';
    	}
    
    	if ( $params['order'] == 'street' ) {
    		$where_array[] = 're_street.street_id=re_data.street_id';
    		$add_from_table .= ' , re_street ';
    		$add_select_value .= ' , re_street.name as street ';
    	}
    
    
    	if(isset($params['favorites']) && !empty($params['favorites'])){
    		$QB->addCondition(DB_PREFIX.'_data.id', 'in', $params['favorites']);
    		$where_array[] = 're_data.id IN ('.implode(',',$params['favorites']).')';
    	}
    
    	 
    
    	if(isset($params['optype'])){
    		$QB->addCondition(DB_PREFIX.'_data.optype', 'eq', (int)$params['optype']);
    		$where_array[] = DB_PREFIX.'_data.optype='.(int)$params['optype'];
    	}
    
    	$where_array[] = 're_topic.id=re_data.topic_id';
    
    	//echo '$params[\'topic_id\'] = '.$params['topic_id'].'<br>';
    
    	if ( $params['topic_id'] != '' &&  $params['topic_id'] != 0) {
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    		$Structure_Manager = new Structure_Manager();
    		$category_structure = $Structure_Manager->loadCategoryStructure();
    		global $smarty;
    		//echo $category_structure['catalog'][$params['topic_id']]['description'];
    		$smarty->assign('topic_description', $category_structure['catalog'][$params['topic_id']]['description']);
    
    		$childs = $Structure_Manager->get_all_childs($params['topic_id'], $category_structure);
    		if ( count($childs) > 0 ) {
    			array_push($childs, $params['topic_id']);
    			$where_array[] = 're_data.topic_id in ('.implode(' , ',$childs).') ';
    			$QB->addCondition(DB_PREFIX.'_data.topic_id', 'in', $childs);
    		} else {
    			$where_array[] = 're_data.topic_id='.$params['topic_id'];
    			$QB->addCondition(DB_PREFIX.'_data.topic_id', 'eq', $params['topic_id']);
    		}
    		//print_r($params);
    	}
    
    	if ( isset($params['country_id']) and $params['country_id'] != 0  ) {
    		$where_array[] = 're_data.country_id = '.$params['country_id'];
    		$QB->addCondition(DB_PREFIX.'_data.country_id', 'eq', $params['country_id']);
    	}else{
    		unset($params['country_id']);
    	}
    
    	if ( isset($params['id']) and $params['id'] != 0  ) {
    		$where_array[] = 're_data.id = '.$params['id'];
    		$QB->addCondition(DB_PREFIX.'_data.id', 'eq', $params['id']);
    	}
    
    	if ( isset($params['mvids']) && is_array($params['mvids']) && count($params['mvids']) != 0  ) {
    		$where_array[] = 're_data.id IN ('.implode(',',$params['mvids']).')';
    	}
    
    
    	if ( isset($params['user_id']) && $params['user_id'] > 0  ) {
    		$where_array[] = 're_data.user_id = '.$params['user_id'];
    		$QB->addCondition(DB_PREFIX.'_data.user_id', 'eq', $params['user_id']);
    	}
    
    	if ( isset($params['onlyspecial']) && $params['onlyspecial'] > 0  ) {
    		$where_array[] = 're_data.hot = 1';
    		$QB->addCondition(DB_PREFIX.'_data.hot', 'eq', 1);
    	}
    
    
    	if ( isset($params['price']) && $params['price'] != 0  ) {
    		$where_array[] = 're_data.price  <= '.$params['price'];
    		$QB->addCondition(DB_PREFIX.'_data.price', '<=', $params['price']);
    	}
    
    	if ( isset($params['price_min']) && $params['price_min'] != 0  ) {
    		$where_array[] = 're_data.price  >= '.$params['price_min'];
    		$QB->addCondition(DB_PREFIX.'_data.price', '>=', $params['price_min']);
    	}
    
    	if ( isset($params['house_number']) && $params['house_number'] != 0  ) {
    		$where_array[] = 're_data.number  = \''.$params['house_number'].'\'';
    		$QB->addCondition(DB_PREFIX.'_data.number', '=', $params['house_number']);
    	}else{
    		unset($params['house_number']);
    	}
    
    
    	if ( isset($params['region_id']) && $params['region_id'] != 0 ) {
    		$where_array[] = 're_data.region_id = '.$params['region_id'];
    		$QB->addCondition(DB_PREFIX.'_data.region_id', '=', $params['region_id']);
    	}else{
    		unset($params['region_id']);
    	}
    
    	if ( isset($params['spec']) ) {
    		$where_array[] = ' re_data.hot = 1 ';
    		$QB->addCondition(DB_PREFIX.'_data.hot', '=', 1);
    	}
    	if ( isset($params['hot']) ) {
    		$where_array[] = ' re_data.hot = 1 ';
    		$QB->addCondition(DB_PREFIX.'_data.hot', '=', 1);
    	}
    	if ( isset($params['city_id']) and $params['city_id'] != 0  ) {
    		$where_array[] = 're_data.city_id = '.$params['city_id'];
    		$QB->addCondition(DB_PREFIX.'_data.city_id', '=', $params['city_id']);
    	}
    	if ( isset($params['district_id']) and $params['district_id'] != 0  ) {
    		$where_array[] = 're_data.district_id = '.$params['district_id'];
    		$QB->addCondition(DB_PREFIX.'_data.district_id', '=', $params['district_id']);
    	}else{
    		unset($params['district_id']);
    	}
    	if ( isset($params['metro_id']) and $params['metro_id'] != 0  ) {
    		$where_array[] = 're_data.metro_id = '.$params['metro_id'];
    		$QB->addCondition(DB_PREFIX.'_data.metro_id', '=', $params['metro_id']);
    	}else{
    		unset($params['metro_id']);
    	}
    	if ( isset($params['street_id']) and $params['street_id'] != 0  ) {
    		$where_array[] = 're_data.street_id = '.$params['street_id'];
    		$QB->addCondition(DB_PREFIX.'_data.street_id', '=', $params['street_id']);
    	}else{
    		unset($params['street_id']);
    	}
    	////////////реализовать обработку OR
    	if(isset($params['srch_phone']) and $params['srch_phone'] !== NULL){
    		$phone = preg_replace('/[^\d]/', '', $params['srch_phone']);
    		$sub_where=array();
    		if($this->getConfigValue('allow_additional_mobile_number')){
    			$sub_where[] = '(re_data.ad_mobile_phone LIKE \'%'.$phone.'%\')';
    			$QB->addCondition(DB_PREFIX.'_data.ad_mobile_phone', 'like', '%'.$phone.'%');
    		}
    		if($this->getConfigValue('allow_additional_stationary_number')){
    			$sub_where[] = '(re_data.ad_stacionary_phone LIKE \'%'.$phone.'%\')';
    			$QB->addCondition(DB_PREFIX.'_data.ad_stacionary_phone', 'like', '%'.$phone.'%');
    		}
    		$sub_where[] = '(re_data.phone LIKE \'%'.$phone.'%\')';
    		$where_array[]='('.implode(' OR ',$sub_where).')';
    	}
    	////////////реализовать обработку OR
    	if(isset($params['srch_word']) and $params['srch_word'] !== NULL){
    		$sub_where=array();
    		$word=htmlspecialchars($params['srch_word']);
    		$sub_where[] = '(re_data.text LIKE \'%'.$word.'%\')';
    		/*$sub_where[] = '(re_data.more1 LIKE \'%'.$word.'%\')';
    		$sub_where[] = '(re_data.more2 LIKE \'%'.$word.'%\')';
    		$sub_where[] = '(re_data.more3 LIKE \'%'.$word.'%\')';*/
    		$where_array[]='('.implode(' OR ',$sub_where).')';
    	}
    
    	if(isset($params['room_count'])){
    		if(is_array($params['room_count']) && count($params['room_count'])>0){
    			$sub_where=array();
    			foreach($params['room_count'] as $rq){
    				if($rq==4){
    					$sub_where[]='room_count>3';
    				}elseif(0!=(int)$rq){
    					$sub_where[]='room_count='.(int)$rq;
    				}
    			}
    			if(count($sub_where)>0){
    				$where_array[]='('.implode(' OR ',$sub_where).')';
    			}
    		}else{
    			unset($params['room_count']);
    		}
    	}
    
    	if($params['srch_date_from']!=0 && $params['srch_date_to']!=0){
    		$where_array[]="((re_data.date_added>='".$params['srch_date_from']."') AND (re_data.date_added<='".$params['srch_date_to']."'))";
    		$QB->addCondition(DB_PREFIX.'_data.date_added', '>=', $params['srch_date_from']);
    		$QB->addCondition(DB_PREFIX.'_data.date_added', '<=', $params['srch_date_to']);
    	}elseif($params['srch_date_from']!=0){
    		$where_array[]="(re_data.date_added>='".$params['srch_date_from']."')";
    		$QB->addCondition(DB_PREFIX.'_data.date_added', '>=', $params['srch_date_from']);
    	}elseif($params['srch_date_to']!=0){
    		$where_array[]="(re_data.date_added<='".$params['srch_date_to']."')";
    		$QB->addCondition(DB_PREFIX.'_data.date_added', '<=', $params['srch_date_to']);
    	}
    
    	if($params['floor_min']!=0 && $params['floor_max']!=0){
    		$where_array[]="(re_data.floor BETWEEN ".$params['floor_min']." AND ".$params['floor_max'].")";
    		$QB->addCondition(DB_PREFIX.'_data.floor', '>=', $params['floor_min']);
    		$QB->addCondition(DB_PREFIX.'_data.floor', '<=', $params['floor_max']);
    	}elseif($params['floor_min']!=0){
    		$where_array[]="(re_data.floor>=".$params['floor_min'].")";
    		$QB->addCondition(DB_PREFIX.'_data.floor', '>=', $params['floor_min']);
    	}elseif($params['floor_max']!=0){
    		$where_array[]="(re_data.floor<=".$params['floor_max'].")";
    		$QB->addCondition(DB_PREFIX.'_data.floor', '<=', $params['floor_max']);
    	}
    
    	if($params['floor_count_min']!=0 && $params['floor_count_max']!=0){
    		$where_array[]="(re_data.floor_count BETWEEN ".$params['floor_count_min']." AND ".$params['floor_count_max'].")";
    		$QB->addCondition(DB_PREFIX.'_data.floor_count', '>=', $params['floor_count_min']);
    		$QB->addCondition(DB_PREFIX.'_data.floor_count', '<=', $params['floor_count_max']);
    	}elseif($params['floor_count_min']!=0){
    		$where_array[]="(re_data.floor_count>=".$params['floor_count_min'].")";
    		$QB->addCondition(DB_PREFIX.'_data.floor_count', '>=', $params['floor_count_min']);
    	}elseif($params['floor_count_max']!=0){
    		$where_array[]="(re_data.floor_count<=".$params['floor_count_max'].")";
    		$QB->addCondition(DB_PREFIX.'_data.floor_count', '<=', $params['floor_count_max']);
    	}
    
    	if($params['square_min']!=0 && $params['square_max']!=0){
    		$where_array[]="(re_data.square_all BETWEEN ".$params['square_min']." AND ".$params['square_max'].")";
    	}elseif($params['square_min']!=0){
    		$where_array[]="(re_data.square_all>=".$params['square_min'].")";
    	}elseif($params['square_max']!=0){
    		$where_array[]="(re_data.square_all<=".$params['square_max'].")";
    	}
    
    	if($params['is_phone']==1){
    		$where_array[]="(re_data.is_telephone=1)";
    	}else{
    		unset($params['is_phone']);
    	}
    
    	if($params['is_internet']==1){
    		$where_array[]="(re_data.is_internet=1)";
    	}else{
    		unset($params['is_internet']);
    	}
    
    	if($params['is_furniture']==1){
    		$where_array[]="(re_data.furniture=1)";
    	}else{
    		unset($params['is_furniture']);
    	}
    
    	if($params['owner']==1){
    		$where_array[]="(re_data.whoyuaare=1)";
    	}else{
    		unset($params['is_furniture']);
    	}
    
    	if($params['has_photo']==1){
    		$where_array[]='((SELECT COUNT(*) FROM '.DB_PREFIX.'_data_image WHERE id='.DB_PREFIX.'_data.id)>0)';
    	}else{
    		unset($params['has_photo']);
    	}
    
    	if($params['infra_greenzone']==1){
    		$where_array[]="(re_data.infra_greenzone=1)";
    	}else{
    		unset($params['infra_greenzone']);
    	}
    
    	if($params['infra_sea']==1){
    		$where_array[]="(re_data.infra_sea=1)";
    	}else{
    		unset($params['infra_sea']);
    	}
    
    	if($params['infra_sport']==1){
    		$where_array[]="(re_data.infra_sport=1)";
    	}else{
    		unset($params['infra_sport']);
    	}
    
    	if($params['infra_clinic']==1){
    		$where_array[]="(re_data.infra_clinic=1)";
    	}else{
    		unset($params['infra_clinic']);
    	}
    
    	if($params['infra_terminal']==1){
    		$where_array[]="(re_data.infra_terminal=1)";
    	}else{
    		unset($params['infra_terminal']);
    	}
    
    	if($params['infra_airport']==1){
    		$where_array[]="(re_data.infra_airport=1)";
    	}else{
    		unset($params['infra_airport']);
    	}
    
    	if($params['infra_bank']==1){
    		$where_array[]="(re_data.infra_bank=1)";
    	}else{
    		unset($params['infra_bank']);
    	}
    
    	if($params['infra_restaurant']==1){
    		$where_array[]="(re_data.infra_restaurant=1)";
    	}else{
    		unset($params['infra_restaurant']);
    	}
    
    	if(isset($params['object_state']) && is_array($params['object_state']) && count($params['object_state'])>0){
    		$where_array[]="(re_data.object_state IN (".implode(',', $params['object_state'])."))";
    	}else{
    		unset($params['object_state']);
    	}
    
    	if(isset($params['object_type']) && is_array($params['object_type']) && count($params['object_type'])>0){
    		$where_array[]="(re_data.object_type IN (".implode(',', $params['object_type'])."))";
    	}else{
    		unset($params['object_type']);
    	}
    
    	if(isset($params['object_destination']) && is_array($params['object_destination']) && count($params['object_destination'])>0){
    		$where_array[]="(re_data.object_destination IN (".implode(',', $params['object_destination'])."))";
    	}else{
    		unset($params['object_destination']);
    	}
    
    	if(isset($params['aim']) && is_array($params['aim']) && count($params['aim'])>0){
    		$where_array[]="(re_data.aim IN (".implode(',', $params['aim'])."))";
    	}else{
    		unset($params['aim']);
    	}
    
    	if($params['has_geo']==1){
    		$where_array[]='('.DB_PREFIX.'_data.geo_lat IS NOT NULL AND '.DB_PREFIX.'_data.geo_lng IS NOT NULL)';
    	}
    
    
    	/*
    	 if ($_SERVER['REQUEST_URI'] == '/')
    		$order = "re_data.id desc";
    	else
    		$order = "re_data.date_added desc";
    	*/
    	if ( $params['admin'] != 1 ) {
    		$where_array[] = 're_data.active=1';
    	} elseif ( $params['active'] == 1 ) {
    		$where_array[] = 're_data.active=1';
    	} elseif ( $params['active'] == 'notactive' ) {
    		$where_array[] = 're_data.active=0';
    	}
    
    	if ( $this->getConfigValue('apps.company.timelimit') ) {
    		$current_time = time();
    
    		$where_array[] = 're_data.user_id=u.user_id';
    		$where_array[] = 'u.company_id=c.company_id';
    		$where_array[] = "c.start_date <= $current_time";
    		$where_array[] = "c.end_date >= $current_time";
    		$add_from_table .= ' , re_user u, re_company c ';
    	}
    
    	if ( $params['only_img'] ) {
    		 
    		$where_array[] = 're_data.id=i.id';
    		$add_from_table .= ' , re_data_image i ';
    	}
    
    
    	if ( $where_array ) {
    		$where_statement = " WHERE ".implode(' AND ', $where_array);
    	}
    
    	if ( isset($params['order']) ) {
    
    		if ( !isset($params['asc']) ) {
    			$asc = 'desc';
    		}
    		if ($params['asc'] == 'asc')  {
    			$asc = 'asc';
    		}elseif ($params['asc'] == 'desc') {
    			$asc = 'desc';
    		}else{
    			$asc = 'desc';
    		}
    		//
    		if     ( $params['order'] == 'type' ) $order = 'type_sh ';
    		elseif ( $params['order'] == 'street' ) $order = 're_street.name ';
    		elseif ( $params['order'] == 'district' ) $order = 're_district.name ';
    		elseif ( $params['order'] == 'metro' ) $order = 're_metro.name ';
    		elseif ( $params['order'] == 'city' ) $order = 're_city.name ';
    		elseif ( $params['order'] == 'date_added' ) $order = 're_data.date_added ';
    		elseif ( $params['order'] == 'price' ){
    			$order = 'price ';
    		}
    
    		$order .= $asc;
    	} else {
    		//$order = "re_data.id desc";
    		$order = "re_data.date_added DESC, re_data.id DESC";
    	}
    
    	if ( !isset($params['page']) or $params['page'] == 0 ) {
    		$page = 1;
    	} else {
    		$page = $params['page'];
    	}
    
    
    	if ( $random ) {
    		$order = ' rand() ';
    	}
    
    	if ( $this->getConfigValue('currency_enable') ) {
    		$query = "select count(re_data.id) as total from re_data LEFT JOIN re_currency ON re_data.currency_id=re_currency.currency_id, re_topic $add_from_table $where_statement ";
    	}else{
    		$query = "select count(re_data.id) as total from re_data, re_topic $add_from_table $where_statement ";
    	}
    
    	$query1='SELECT COUNT('.DB_PREFIX.'_data.id) as total FROM '.DB_PREFIX.'_data d
				LEFT JOIN '.DB_PREFIX.'_topic t ON t.id=d.topic_id';
    
    	//$query = "select count(re_data.id) as total from re_data LEFT JOIN re_currency ON re_data.currency_id=re_currency.currency_id, re_topic $add_from_table $where_statement ";
    	//echo $query.'<br>';
    
    	$this->db->exec($query);
    	if ( !$this->db->success ) {
    		echo $this->db->error.'<br>';
    	}
    	$this->db->fetch_assoc();
    	$total = $this->db->row['total'];
    	$this->grid_total = $total;
    	global $smarty;
    	$smarty->assign('_total_records',$total);
    
    	//echo "total = $total<br>";
    	$limit = $this->getConfigValue('per_page');
    	$max_page=ceil($total/$limit);
    	//echo "max_page = $max_page<br>";
    	if($page>$max_page){
    		$page=1;
    		$params['page']=1;
    	}
    
    	$start = ($page-1)*$limit;
    
    	$pager_params=$params;
    	//print_r($pager_params);
    	 
    	unset($params['order']);
    	unset($params['asc']);
    	unset($params['favorites']);
    
    	 
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	$Structure_Manager = new Structure_Manager();
    	$category_structure = $Structure_Manager->loadCategoryStructure();
    
    	//print_r($params);
    	if($params['topic_id']!=''){
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
    				/*$pageurl='topic'.$params['topic_id'].'.html';
    				 unset($pager_params['topic_id']);
    				unset($params['topic_id']);*/
    			}
    		}else{
    			$pageurl='';
    		}
    	}else{
    		$pageurl='';
    	}
    	$this->template->assert('pager', $this->get_page_links_list ($page, $total, $limit, $pager_params ));
    	 
    	 
    	 
    
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
    				//echo "key = $key, value = $value<br>";
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
    
    
    	if ( $this->getConfigValue('apps.company.timelimit') ) {
    		if ( $this->getConfigValue('currency_enable') ) {
    			$query = "select re_currency.code AS currency_code, re_currency.name AS currency_name, ((re_data.price*re_currency.course)/".$CM->getCourse(CURRENT_CURRENCY).") AS price_ue, re_data.*, re_topic.name as type_sh $add_select_value from re_data LEFT JOIN re_currency ON re_data.currency_id=re_currency.currency_id, re_topic $add_from_table $where_statement ORDER BY ".$order.($params['no_portions']==1 ? '' : " LIMIT ".$start.", ".$limit);
    		} else {
    			$query = "select re_data.*, re_topic.name as type_sh $add_select_value from re_data, re_topic $add_from_table $where_statement order by ".$order.($params['no_portions']==1 ? '' : " LIMIT ".$start.", ".$limit);
    		}
    		//echo $query.'<br>';
    	} else {
    		if ( $this->getConfigValue('currency_enable') ) {
    			$query = "select re_currency.code AS currency_code, re_currency.name AS currency_name, ((re_data.price*re_currency.course)/".$CM->getCourse(CURRENT_CURRENCY).") AS price_ue, re_data.*, re_topic.name as type_sh $add_select_value from re_data LEFT JOIN re_currency ON re_data.currency_id=re_currency.currency_id, re_topic $add_from_table $where_statement ORDER BY ".$order.($params['no_portions']==1 ? '' : " LIMIT ".$start.", ".$limit);
    		} else {
    			$query = "select re_data.*, re_topic.name as type_sh $add_select_value from re_data, re_topic $add_from_table $where_statement ORDER BY ".$order.($params['no_portions']==1 ? '' : " LIMIT ".$start.", ".$limit);
    		}
    	}
    	//echo $query."<br>";
    	$this->db->exec($query);
    	if ( !$this->db->success ) {
    		echo $this->db->error.'<br>';
    	}
    
    	$ra = array();
    	$i = 0;
    	if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/company/company.xml') ) {
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
    		$Account = new Account;
    
    	}
    
    	while ( $this->db->fetch_assoc() ) {
    		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/company/company.xml') ) {
    			$company_profile = $Account->get_company_profile($this->db->row['user_id']);
    			//echo '<pre>';
    			//print_r($company_profile);
    			//echo '</pre>';
    			$this->db->row['company'] = $company_profile['name']['value'];
    		}
    		$ra[$i] = $this->db->row;
    		$i++;
    	}
    	 
    	//require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	//$Structure_Manager = new Structure_Manager();
    	//$category_structure = $Structure_Manager->loadCategoryStructure();
    
    
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$params = array();
    	//echo '<pre>';
    	//print_r($ra);
    	/*
    	 if($this->getConfigValue('apps.agentphones.enable')==1){
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/agentphones/admin/admin.php');
    	$AP=new agentphones_admin();
    	$AP->init();
    	}else{
    	$AP=NULL;
    	}
    	*/
    	 
    	if($this->getConfigValue('apps.mapviewer.enable')){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/mapviewer/admin/admin.php';
    		$Map_Viewer=new mapviewer_admin();
    		$coords_info=$Map_Viewer->loadMVData();
    	}else{
    		$coords_info=array();
    	}
    
    
    	foreach ( $ra as $item_id => $item_array ) {
    		if ( $item_array['country_id'] > 0 ) {
    			$ra[$item_id]['country'] = $data_model->get_string_value_by_id('country', 'country_id', 'name', $item_array['country_id'], true);
    		}
    		if ( $item_array['region_id'] > 0 ) {
    			$ra[$item_id]['region'] = $data_model->get_string_value_by_id('region', 'region_id', 'name', $item_array['region_id'], true);
    		}
    		if ( $item_array['district_id'] > 0 ) {
    			$ra[$item_id]['district'] = $data_model->get_string_value_by_id('district', 'id', 'name', $item_array['district_id'], true);
    		}
    		if ( $item_array['street_id'] > 0 ) {
    			$ra[$item_id]['street'] = $data_model->get_string_value_by_id('street', 'street_id', 'name', $item_array['street_id'], true);
    		}
    		if ( $item_array['city_id'] > 0 ) {
    			$ra[$item_id]['city'] = $data_model->get_string_value_by_id('city', 'city_id', 'name', $item_array['city_id'], true);
    		}
    		if ( $item_array['metro_id'] > 0 ) {
    			$ra[$item_id]['metro'] = $data_model->get_string_value_by_id('metro', 'metro_id', 'name', $item_array['metro_id'], true);
    		}
    		if ( $item_array['user_id'] > 0 ) {
    			$ra[$item_id]['user'] = $data_model->get_string_value_by_id('user', 'user_id', 'fio', $item_array['user_id'], true);
    		}
    		if ( $item_array['currency_id'] > 0 ) {
    			$ra[$item_id]['currency'] = $data_model->get_string_value_by_id('currency', 'currency_id', 'code', $item_array['currency_id'], true);
    			//$ra[$item_id]['currency_name'] = $data_model->get_string_value_by_id('currency', 'currency_id', 'name', $item_array['currency_id'], true);
    		}
    
    		$params['topic_id'] = $item_array['topic_id'];
    
    		$ra[$item_id]['path'] = $this->get_category_breadcrumbs_string( $params, $category_structure );
    		$ra[$item_id]['date'] = date('d.m',strtotime($ra[$item_id]['date_added']));
    
    
    		$image_array = $data_model->get_image_array ( 'data', 'data', 'id', $item_array['id'], 1 );
    		if ( count($image_array) > 0 ) {
    			$ra[$item_id]['img'] = $image_array;
    		}
    
    
    		if(1==$this->getConfigValue('apps.seo.level_enable')){
    			 
    			if($category_structure['catalog'][$ra[$item_id]['topic_id']]['url']!=''){
    				$ra[$item_id]['parent_category_url']=$category_structure['catalog'][$ra[$item_id]['topic_id']]['url'].'/';
    			}else{
    				$ra[$item_id]['parent_category_url']='';
    			}
    		}else{
    			$ra[$item_id]['parent_category_url']='';
    		}
    		if(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
    			$ra[$item_id]['href']=SITEBILL_MAIN_URL.'/'.$ra[$item_id]['parent_category_url'].'realty'.$ra[$item_id]['id'].'.html';
    		}else{
    			$ra[$item_id]['href']=SITEBILL_MAIN_URL.'/'.$ra[$item_id]['parent_category_url'].'realty'.$ra[$item_id]['id'];
    		}
    
    		if(isset($coords_info[$item_array['id']])){
    			$ra[$item_id]['mvdata']=$coords_info[$item_array['id']];
    		}
    
    
    		/*
    		 if($AP!==NULL){
    		$phones=array();
    		$phones[]=$ra[$item_id]['phone'];
    		if($this->getConfigValue('allow_additional_mobile_number')==1 && $ra[$item_id]['ad_mobile_phone']!=''){
    		$phones[]=$ra[$item_id]['ad_mobile_phone'];
    		}
    		if($this->getConfigValue('allow_additional_stationary_number')==1 && $ra[$item_id]['ad_stacionary_phone']!=''){
    		$phones[]=$ra[$item_id]['ad_stacionary_phone'];
    		}
    		$phone_check=$AP->checkCoincidence($phones,$ra[$item_id]['id']);
    		//print_r($phone_check);
    		$ra[$item_id]['check_result']=$phone_check;
    		}
    		*/
    
    	}
    	//echo '<pre>';
    	//print_r($ra);
    	//echo '</pre>';
    
    	echo '<br /><br />'.$QB->build().'<br /><br />';
    	return $ra;
    }
    
    /**
     * Get sales grid
     * @param array $adv res
     * @return string
     */
    function get_sales_grid ( $adv ) {
        global $topic_id;
        
        if ( $this->getConfigValue('theme') != 'estate' and !file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_grid.tpl') ) {
            $this->template->assign('main_file_tpl', '../estate/realty_grid.tpl');
        } else {
		
            $this->template->assign('main_file_tpl', 'realty_grid.tpl');
        }
        
        if ( $_REQUEST['REST_API'] == 1 ) {
        	if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/api/classes/class.static_data.php') ) {
        		$static_data = Static_Data::getInstance();
        		$static_data::set_data($adv);
        		return;
        	}
        }
        
        $this->template->assign('grid_items', $adv);
        

        return true;
    }
    
    function createMapListing($ra){
    	global $smarty;
    	$clustered_objects=array();
    	foreach($ra as $k=>$d){
    		if( isset($d['geo_lat']) && isset($d['geo_lng']) && $d['geo_lat']!='' && $d['geo_lng']!='' ){
    			$coords_string=$d['geo_lat'].'_'.$d['geo_lng'];
    			$clustered_objects[$coords_string][]=$d;
    		}
    	}
    	
    	
    	if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$theme.'/mapobjectslisting.tpl') ) {
    		$template=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$theme.'/mapobjectslisting.tpl';
    	} else {
    		$template=SITEBILL_DOCUMENT_ROOT.'/apps/system/template/mapobjectslisting.tpl';
    	}
    	$smarty->assign('mapobjects_clusters',$clustered_objects);
    	return $html=$smarty->fetch($template);
    }
    
    protected function prepareSortOrder($params, $random = false, $premium=false){
    	$order='';
    	$asc='';
    	
    	$default_sorts=$this->getConfigValue('apps.realty.sorts');
    	$sorts=array();
    	if($default_sorts!=''){
    		$matches=array();
			preg_match_all('/([a-z0-9_]+)\|(asc|desc)[;]?/i', $default_sorts, $matches);
			
			if(count($matches[0])>0){
				foreach($matches[1] as $k=>$fkey){
					if($matches[2][$k]=='asc' || $matches[2][$k]=='desc'){
						switch($fkey){
							case 'id' : {
								$sorts[]=DB_PREFIX.'_data.id '.$matches[2][$k];
								break;
							}
							case 'type' : {
								$sorts[]='type_sh '.$matches[2][$k];
								break;
							}
							case 'street' : {
								$sorts[]='street '.$matches[2][$k];
								break;
							}
							case 'square_all' : {
								$sorts[]=DB_PREFIX.'_data.square_all*1 '.$matches[2][$k];
								break;
							}
							case 'floor' : {
								$sorts[]=DB_PREFIX.'_data.floor*1 '.$matches[2][$k];
								break;
							}
							case 'district' : {
								$sorts[]='district '.$matches[2][$k];
								break;
							} 
							case 'metro' : {
								$sorts[]='metro '.$matches[2][$k];
								break;
							}
							case 'city' : {
								$sorts[]='city '.$matches[2][$k];
								break;
							}
							case 'date_added' : {
								$sorts[]=DB_PREFIX.'_data.date_added '.$matches[2][$k];
								break;
							}
							case 'price' : {
								$sorts[]='price_ue '.$matches[2][$k];
								break;
							}
						}
					}
				}
			}
    	}
    	
    	if(!empty($sorts)){
    		$default_sorts=implode(', ', $sorts);
    	}else{
    		$default_sorts=DB_PREFIX.'_data.date_added DESC, '.DB_PREFIX.'_data.id DESC';
    	}
    	
    	if ( $random ) {
    		$order = ' rand() ';
    	}elseif( isset($params['order']) ) {

            if ( !isset($params['asc']) ) {
                $asc = 'desc';
            } 
            if ($params['asc'] == 'asc')  {
            	$asc = 'asc';
            }elseif ($params['asc'] == 'desc') {
            	$asc = 'desc';
            }else{
            	$asc = 'desc';
            }
            
            switch($params['order']){
            	case 'id' : {
            		$order=DB_PREFIX.'_data.id '.$asc;
            		break;
            	}
            	case 'type' : {
            		$order='type_sh '.$asc;
            		break;
            	}
            	case 'street' : {
            		$order='street '.$asc;
            		break;
            	}
            	case 'square_all' : {
            		$order=DB_PREFIX.'_data.square_all*1 '.$asc;
            		break;
            	}
            	case 'floor' : {
            		$order=DB_PREFIX.'_data.floor*1 '.$asc;
            		break;
            	}
            	case 'district' : {
            		$order='district '.$asc;
            		break;
            	}
            	case 'metro' : {
            		$order='metro '.$asc;
            		break;
            	}
            	case 'city' : {
            		$order='city '.$asc;
            		break;
            	}
            	case 'date_added' : {
            		$order=DB_PREFIX.'_data.date_added '.$asc;
            		break;
            	}
            	case 'price' : {
            		$order='price_ue '.$asc;
            		break;
            	}
            	case 'popular' : {
            		$order=DB_PREFIX.'_data.view_count '.$asc;
            		break;
            	}
            	default : {
            		$order=$default_sorts;
            	}
            }
            //
            /*if     ( $params['order'] == 'type' ) $order = 'type_sh ';
            elseif ( $params['order'] == 'street' ) $order = 'street ';
            elseif ( $params['order'] == 'square_all' ) $order = 're_data.square_all*1 ';
            elseif ( $params['order'] == 'floor' ) $order = 're_data.floor*1 ';
            elseif ( $params['order'] == 'district' ) $order = 'district ';
            elseif ( $params['order'] == 'metro' ) $order = 'metro ';
            elseif ( $params['order'] == 'city' ) $order = 'city ';
            elseif ( $params['order'] == 'date_added' ) $order = 're_data.date_added ';
            elseif ( $params['order'] == 'id' ) $order = 're_data.id ';
            elseif ( $params['order'] == 'price' ){
            		$order = 'price_ue ';
            }else{
            	$order = "re_data.date_added ";
            }

            $order .= $asc;*/
        } else {
        	if ( $premium ) {
        		if((int)$params['page']==1 || (int)$params['page']==0){
					$order = ' '.DB_PREFIX.'_data.premium_status_end DESC';
				}else{
					$order = ' '.DB_PREFIX.'_data.premium_status_end DESC';
					//$order = " rand() ";
				}
        	} else {
        		$order = $default_sorts;
        	}
        }
        
        return $order;
    }
    
    protected function generateGridGeoDataOld($ra){
    	$grid_geodata=array();
    	foreach ( $ra as $item_id => $item_array ) {
    		if( isset($item_array['geo_lat']) && isset($item_array['geo_lng']) && $item_array['geo_lat']!='' && $item_array['geo_lng']!='' ){
    			$grid_geodata[]=array(
    					'lat'=>$item_array['geo_lat'],
    					'lng'=>$item_array['geo_lng'],
    					'id'=>$item_array['id']
    			);
    		}
    	}
    	return $grid_geodata;
    }
    
    protected function transformGridData($ra, $_collect_user_info=false){
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	$Structure_Manager = new Structure_Manager();
    	$category_structure = $Structure_Manager->loadCategoryStructure();
    	
    	
    	$params = array();
    	 
    	$_model=$data_model->get_kvartira_model(false, true);
    	
    	
    	$grid_geodata=array();
    	
    	$billing=false;
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable')==1){
    		$billing=true;
    	}
    	
    	
    	foreach ( $ra as $item_id => $item_array ) {
    		if( isset($item_array['geo_lat']) && isset($item_array['geo_lng']) && $item_array['geo_lat']!='' && $item_array['geo_lng']!='' ){
    			$grid_geodata[]=array(
    					'lat'=>$item_array['geo_lat'],
    					'lng'=>$item_array['geo_lng'],
    					'id'=>$item_array['id']
    			);
    		}
    		if ( $item_array['country_id'] > 0 ) {
    			$ra[$item_id]['country'] = $data_model->get_string_value_by_id('country', 'country_id', 'name', $item_array['country_id'], true);
    		}
    		
    		if ( $item_array['region_id'] > 0 ) {
    			$ra[$item_id]['region'] = $data_model->get_string_value_by_id('region', 'region_id', 'name', $item_array['region_id'], true);
    		}
    		if ( $item_array['district_id'] > 0 ) {
    			$ra[$item_id]['district'] = $data_model->get_string_value_by_id('district', 'id', 'name', $item_array['district_id'], true);
    		}
    		if ( $item_array['street_id'] > 0 ) {
    			$ra[$item_id]['street'] = $data_model->get_string_value_by_id('street', 'street_id', 'name', $item_array['street_id'], true);
    		}
    		if ( $item_array['city_id'] > 0 ) {
    			$ra[$item_id]['city'] = $data_model->get_string_value_by_id('city', 'city_id', 'name', $item_array['city_id'], true);
    		}
    		if ( $item_array['metro_id'] > 0 ) {
    			$ra[$item_id]['metro'] = $data_model->get_string_value_by_id('metro', 'metro_id', 'name', $item_array['metro_id'], true);
    		}
    		if ( $item_array['user_id'] > 0 ) {
    			if($_collect_user_info){
    				$DBC=DBC::getInstance();
    				$stmt=$DBC->query('SELECT phone, login, fio FROM '.DB_PREFIX.'_user WHERE user_id=? LIMIT 1', array($item_array['user_id']));
    				if($stmt){
    					$ar=$DBC->fetch($stmt);
    					$ra[$item_id]['_user_info']=$ar;
    				}
    			}
    			 
    			 
    			 
    			$ra[$item_id]['user'] = $data_model->get_string_value_by_id('user', 'user_id', 'fio', $item_array['user_id'], true);
    			if ( $ra[$item_id]['user'] == '' ) {
    				$ra[$item_id]['user'] = $data_model->get_string_value_by_id('user', 'user_id', 'login', $item_array['user_id'], true);
    			}
    		}
    		if ( $item_array['currency_id'] > 0 ) {
    			$ra[$item_id]['currency'] = $data_model->get_string_value_by_id('currency', 'currency_id', 'code', $item_array['currency_id'], true);
    		}
    	
    		foreach($_model['data'] as $k=>$v){
    			if($v['type']=='select_box'){
    				if(isset($_model['data'][$k]['select_data'][$ra[$item_id][$k]])){
    					$ra[$item_id][$k]=$_model['data'][$k]['select_data'][$ra[$item_id][$k]];
    				}else{
    					$ra[$item_id][$k]='';
    				}
    			}
    		}
    	
    	
    		$params['topic_id'] = $item_array['topic_id'];
    	
    		$ra[$item_id]['path'] = $this->get_category_breadcrumbs_string( $params, $category_structure );
    		$ra[$item_id]['date'] = date('d.m',strtotime($ra[$item_id]['date_added']));
    		$ra[$item_id]['datetime'] = date('d.m H:i',strtotime($ra[$item_id]['date_added']));
    		$ra[$item_id]['text'] = strip_tags($ra[$item_id]['text']);
    	
    	/*
    		$image_array = $data_model->get_image_array ( 'data', 'data', 'id', $item_array['id'], 1 );
    		if ( count($image_array) > 0 ) {
    			$ra[$item_id]['img'] = $image_array;
    		}
    	
    	*/
    		$ra[$item_id]['topic_info'] = $category_structure['catalog'][$ra[$item_id]['topic_id']];
    		if(1==$this->getConfigValue('apps.seo.level_enable')){
    			 
    			if($category_structure['catalog'][$ra[$item_id]['topic_id']]['url']!=''){
    				$ra[$item_id]['parent_category_url']=$category_structure['catalog'][$ra[$item_id]['topic_id']]['url'].'/';
    			}else{
    				$ra[$item_id]['parent_category_url']='';
    			}
    		}else{
    			$ra[$item_id]['parent_category_url']='';
    		}
    		
    		if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $ra[$item_id]['translit_alias']!=''){
    			$ra[$item_id]['href']=SITEBILL_MAIN_URL.'/'.$ra[$item_id]['parent_category_url'].$ra[$item_id]['translit_alias'];
    		}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
    			$ra[$item_id]['href']=SITEBILL_MAIN_URL.'/'.$ra[$item_id]['parent_category_url'].'realty'.$ra[$item_id]['id'].'.html';
    		}else{
    			$ra[$item_id]['href']=SITEBILL_MAIN_URL.'/'.$ra[$item_id]['parent_category_url'].'realty'.$ra[$item_id]['id'];
    		}
    		
    	
    		if($billing){
    			if(isset($item_array['premium_status_end']) && isset($_model['data']['premium_status_end']) && $ra[$item_id]['premium_status_end']>time()){
    				$ra[$item_id]['premium_status']=1;
    			}
    			if(isset($item_array['vip_status_end']) && isset($_model['data']['vip_status_end']) && $ra[$item_id]['vip_status_end']>time()){
    				$ra[$item_id]['vip_status']=1;
    			} 
    			if(isset($item_array['bold_status_end']) && isset($_model['data']['bold_status_end']) && $ra[$item_id]['bold_status_end']>time()){
    				$ra[$item_id]['bold_status']=1;
    			}
    		}
    	}
    	
    	foreach($ra as $item){
    		$_ids[]=$item['id'];
    	}
    	
    	$hasUploadify=false;
    	$hasUploads=false;
    	$uploads_element='';
    	foreach($_model['data'] as $k=>$v){
    		if(isset($v['type']) && $v['type']=='uploadify_image'){
    			$hasUploadify=true;
    		}elseif(isset($v['type']) && $v['type']=='uploads'){
    			$hasUploads=true;
    			$uploads_element=$v['name'];
    			break;
    		}
    	}
    	
    	if($hasUploadify){
    		$key='id';
    		if(count($_ids)>0){
    			$query = 'SELECT li.'.$key.' , i.* FROM '.DB_PREFIX.'_data_image li LEFT JOIN '.IMAGE_TABLE.' i USING(image_id) WHERE li.'.$key.' IN ('.implode(', ', $_ids).') ORDER BY li.sort_order ASC';
    			$DBC=DBC::getInstance();
    			$stmt=$DBC->query($query);
    			$images=array();
    			if($stmt){
    				$iurl = $this->storage_dir;
    				while($ar=$DBC->fetch($stmt)){
    					$ar['img_preview']=$iurl.$ar['preview'];
    					$ar['img_normal']=$iurl.$ar['normal'];
    					$images[$ar[$key]][]=$ar;
    						
    						
    				}
    			}
    			foreach($ra as $k=>$item){
    				if(isset($images[$item['id']])){
    					$ra[$k]['img']=$images[$item['id']];
    				}
    			}
    		}
    	}elseif($hasUploads){
    		//try to get uploadify images first
    		$old_uploadify_images = $this->get_uploadify_images($_ids);
    		foreach($ra as $k=>$item){
    			
    			if ( $item[$uploads_element] == '' ) {
    				if(isset($old_uploadify_images[$ra[$k]['id']])){
    					$ra[$k]['img'] = $old_uploadify_images[$ra[$k]['id']];
    				}/*else{
    					$ra[$k]['img']='';
    				}*/
    				
    			}else{
    				$ims=unserialize($item[$uploads_element]);
    				
    				if(count($ims)==0){
    					unset($ra[$k]['img']);
    					//$ra[$k]['img']='';
    				}else{
    					$ra[$k]['img']=$ims;
    				}
    			}
    			
    		}
    	}
    	
    	
    	
    	if($hasUploads){
    		foreach($ra as $e=>$item){
    			foreach($_model['data'] as $k=>$v){
    				if(isset($v['type']) && $v['type']=='uploads'){
    					$ra[$e][$k]=unserialize($ra[$e][$k]);
    				}
    			}
    		}
    	}
    	
    	$destination_elements=array();
    	foreach($_model['data'] as $k=>$v){
    		if(isset($v['type']) && $v['type']=='destination'){
    			$destination_elements[]=$v['name'];
    		}
    	}
    	
    	
    	
    	if(!empty($destination_elements)){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/destination/admin/admin.php';
    		$DA=new destination_admin();
    		$this->template->assert('destination_info', $DA);
    		foreach ($ra as $k=>$item){
    			foreach ($destination_elements as $destination_element){
    				if(0!=(int)$item[$destination_element]){
    					//print_r($DA->getDestinationObject($item[$destination_element]));
    					$ra[$k]['_destination_info'][$destination_element]['value']=$DA->getDestinationById($item[$destination_element]);
    					$ra[$k]['_destination_info'][$destination_element]['parts']=$DA->getDestinationObject($item[$destination_element]);
    				}
    			}
    		}
    	}
    	//print_r($ra);
    	
    	return $ra;
    }
    
    function get_uploadify_images ( $_ids ) {
    	$key = 'id';
    	
    	if(count($_ids)>0){
    		$query = 'SELECT li.'.$key.' , i.* FROM '.DB_PREFIX.'_data_image li LEFT JOIN '.IMAGE_TABLE.' i USING(image_id) WHERE li.'.$key.' IN ('.implode(', ', $_ids).') ORDER BY li.sort_order ASC';
    		$DBC=DBC::getInstance();
    		$stmt=$DBC->query($query);
    		$images=array();
    		if($stmt){
    			$iurl = $this->storage_dir;
    			while($ar=$DBC->fetch($stmt)){
    				$ar['img_preview']=$iurl.$ar['preview'];
    				$ar['img_normal']=$iurl.$ar['normal'];
    				$images[$ar[$key]][]=$ar;
    	
    	
    			}
    		}
    		return $images;
    	}
    	return false;
    }
    
    protected function prepareBreadcrumbs($params, $url = ''){
    	if($url==''){
    		$url=SITEBILL_MAIN_URL;
    	}
    	$breadcrumbs='';
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	$Structure_Manager = new Structure_Manager();
    	$category_structure = $Structure_Manager->loadCategoryStructure();
    	
    	if(1==0){
    		$bc_array=array();
    		
    		 
    		$rs = '';
    		 
    		if ( !isset($params['topic_id']) || is_array($params['topic_id']) ) {
    			return $rs;
    		}
    		 
    		if($category_structure['catalog'][$params['topic_id']]['url']!=''){
    			$bc_array[]=array(
    				'href'=>rtrim($url,'/').'/'.$category_structure['catalog'][$params['topic_id']]['url'],
    				'name'=>$category_structure['catalog'][$params['topic_id']]['name']
    			);
    		}else{
    			$bc_array[]=array(
    				'href'=>rtrim($url,'/').'/topic'.$params['topic_id'].'.html',
    				'name'=>$category_structure['catalog'][$params['topic_id']]['name']
    			);
    		}
    		 
    		$parent_category_id = $category_structure['catalog'][$params['topic_id']]['parent_id'];
    		while ( $category_structure['catalog'][$parent_category_id]['parent_id'] != 0 ) {
    			if ( $j++ > 100 ) {
    				return;
    			}
    			if($category_structure['catalog'][$parent_category_id]['url']!=''){
    				$bc_array[]=array(
    					'href'=>rtrim($url,'/').'/'.$category_structure['catalog'][$parent_category_id]['url'],
    					'name'=>$category_structure['catalog'][$parent_category_id]['name']
    				);
    			}else{
    				$bc_array[]=array(
    					'href'=>rtrim($url,'/').'/topic'.$parent_category_id.'.html',
    					'name'=>$category_structure['catalog'][$parent_category_id]['name']
    				);
    			}
    			$parent_category_id = $category_structure['catalog'][$parent_category_id]['parent_id'];
    		}
    		if ( $category_structure['catalog'][$parent_category_id]['name'] != '' ) {
    			if($category_structure['catalog'][$parent_category_id]['url']!=''){
    				$bc_array[]=array(
    					'href'=>rtrim($url,'/').'/'.$category_structure['catalog'][$parent_category_id]['url'],
    					'name'=>$category_structure['catalog'][$parent_category_id]['name']
    				);
    			}else{
    				$bc_array[]=array(
    					'href'=>rtrim($url,'/').'/topic'.$parent_category_id.'.html',
    					'name'=>$category_structure['catalog'][$parent_category_id]['name']
    				);
    			}
    		}
    		 
    		$bc_array[]=array(
    				'href'=>SITEBILL_MAIN_URL.'/',
    				'name'=>Multilanguage::_('L_HOME')
    		);
    		$bc_array=array_reverse($bc_array);
    		print_r($bc_array);
    	}else{
    		$breadcrumbs=$this->get_category_breadcrumbs( $params, $category_structure, SITEBILL_MAIN_URL );
    		return $breadcrumbs;
    	}
    }
    
    protected function preparePageLimitParams(&$params, $page, $total, $premium){
    	if ( $premium ) {
    		$limit = (int)$this->getConfigValue('apps.billing.premium_count');
    		if($limit==0){
    			$limit = 5;
    		}
    	}else{
    		$limit = $this->getConfigValue('per_page');
    		 
    		if ( (int)$params['vip'] == 1 ) {
    			if ( isset($params['per_page']) && (int)$params['per_page'] > 0 ) {
    				$limit = (int)$params['per_page'];
    			} else {
    				$limit = $this->getConfigValue('vip_rotator_number');
    			}
    		} else {
    			if(isset($params['page_limit']) && (int)$params['page_limit']!=0){
    				$limit = (int)$params['page_limit'];
    			}else{
    				$limit = $this->getConfigValue('per_page');
    			}
    			 
    		}
    	}
    	
    	$max_page=ceil($total/$limit);
    	
    	if($page>$max_page){
    		$page=1;
    		$params['page']=1;
    	}
    	$start = ($page-1)*$limit;
    	
    	return array('start'=>$start, 'limit'=>$limit, 'max_page'=>$max_page);
    }
    
    protected function prepareRequestParams($params, $premium=false){
    	/*
    	$fcwhere=array();
    	$filterconditions=array(
    		'price_min'=>array(
    				'field'=>'price',
    				'cond'=>'<=', //le, leeq, gr, greq, eq, partialeq, eqstart, eqend
    				'value'=>'int', //text
    				'notnull'=>1
    				),
    				'uniq_id'=>array(
    				'field'=>'uniq_id',
    				'cond'=>'eq', //le, leeq, gr, greq, eq, partialeq, eqstart, eqend
    				'value'=>'int', //text
    				'notnull'=>1
    				)	
    	);
    	
    	foreach ($filterconditions as $fe=>&$fc){
    		if(!isset($fc['value'])){
    			$fc['value']='text';
    		}
    		if(!isset($fc['notnull'])){
    			$fc['notnull']=0;
    		}
    	}
    	
    	foreach ($filterconditions as $fe=>$fc){
    		$value=$this->getRequestValue($fe);
    		if($fc['value']=='int'){
    			$value=(int)$value;
    		}
    		
    		if($fc['notnull']==1 && $fc['value']=='int' && $value==0){
    			continue;
    		}else{
    			$fcwhere[]='`'.$fc['field'].'`'.$fc['cond'].$value;
    		}
    	}
    	print_r($fcwhere);*/
    	
    	
    	//print_r($params);
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	 
    	$_model=$data_model->get_kvartira_model(false, true);
    	
    	$where_array = array();
    	$add_from_table='';
    	$add_select_value='';
    	$select_what=array();
    	$left_joins=array();
    	
    	$where_array_prepared = array();
    	$where_value_prepared = array();
    	
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable')==1){
    		$_billing_on=true;
    	}else{
    		$_billing_on=false;
    	}
    	
    	
    	
    	if(isset($params['currency_id']) && 0!=(int)$params['currency_id'] && 1==$this->getConfigValue('currency_enable')){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/currency/admin/admin.php';
    		$CA=new currency_admin();
    		$use_currency=true;
    		$price_koefficient=$CA->getCourse((int)$params['currency_id']);
    	}else{
    		$use_currency=false;
    		$price_koefficient=1;
    	}
    	
    	if ( isset($params['order']) && $params['order'] == 'city' ) {
    		 
    		//$where_array[] = 're_city.city_id=re_data.city_id';
    		//$add_from_table .= ' , re_city ';
    		//$add_select_value .= ' , re_city.name as city ';
    		$select_what[]=DB_PREFIX.'_city.name as city';
    		$left_joins[]='LEFT JOIN '.DB_PREFIX.'_city ON '.DB_PREFIX.'_city.city_id='.DB_PREFIX.'_data.city_id';
    	}
    	
    	if ( isset($params['order']) && $params['order'] == 'district' ) {
    		//$where_array[] = 're_district.id=re_data.district_id';
    		//$add_from_table .= ' , re_district ';
    		//$add_select_value .= ' , re_district.name as district ';
    		$select_what[]=DB_PREFIX.'_district.name as district';
    		$left_joins[]='LEFT JOIN '.DB_PREFIX.'_district ON '.DB_PREFIX.'_district.id='.DB_PREFIX.'_data.district_id';
    	}
    	
    	if ( isset($params['order']) && $params['order'] == 'metro' ) {
    		//$where_array[] = 're_metro.metro_id=re_data.metro_id';
    		//$add_from_table .= ' , re_metro ';
    		//$add_select_value .= ' , re_metro.name as metro ';
    		$select_what[]=DB_PREFIX.'_metro.name as metro';
    		$left_joins[]='LEFT JOIN '.DB_PREFIX.'_metro ON '.DB_PREFIX.'_metro.metro_id='.DB_PREFIX.'_data.metro_id';
    	}
    	
    	if ( isset($params['order']) && $params['order'] == 'street' ) {
    		//$where_array[] = 're_street.street_id=re_data.street_id';
    		//$add_from_table .= ' , re_street ';
    		//$add_select_value .= ' , re_street.name as street ';
    		$select_what[]=DB_PREFIX.'_street.name as street';
    		$left_joins[]='LEFT JOIN '.DB_PREFIX.'_street ON '.DB_PREFIX.'_street.street_id='.DB_PREFIX.'_data.street_id';
    	}
    	
    	
    	//Подключать модель и проверять на наличие такого поля
    	if(isset($params['srch_export_cian']) && $params['srch_export_cian']==1 && isset($_model['data']['export_cian'])){
    		$where_array[] = DB_PREFIX.'_data.export_cian=1';
    		$where_array_prepared[]='('.DB_PREFIX.'_data.export_cian=1)';
    	}else{
    		unset($params['srch_export_cian']);
    	}
    	
    	
    	if(isset($params['favorites']) && !empty($params['favorites'])){
    		$favorites_array=$params['favorites'];
    		foreach ($favorites_array as $k=>$v){
    			if((int)$v!=0){
    				$favorites_array[$k]=(int)$v;
    			}else{
    				unset($favorites_array[$k]);
    			}
    		}
    		if(count($favorites_array)>0){
    			$where_array[] = DB_PREFIX.'_data.id IN ('.implode(',', $favorites_array).')';
    			
    			$str_a=array();
    			foreach($favorites_array as $a){
    				$str_a[]='?';
    			}
    			$where_array_prepared[]='('.DB_PREFIX.'_data.id IN ('.implode(',', $str_a).'))';
    			$where_value_prepared=array_merge($where_value_prepared, $favorites_array);
    		}
    		
    	}
    	
    	
    	
    	if(isset($params['uniq_id']) && (int)$params['uniq_id']!=0){
    		$where_array[] = 're_data.uniq_id='.(int)$params['uniq_id'];
    		$where_array_prepared[]='('.DB_PREFIX.'_data.uniq_id = ?)';
    		$where_value_prepared[]=(int)$params['uniq_id'];
    	}else{
    		unset($params['uniq_id']);
    	}
    	
    	if(isset($params['optype']) && is_array($params['optype'])){
    		$optypes_array=$params['optype'];
    		foreach ($optypes_array as $k=>$v){
    			if((int)$v!=0){
    				$optypes_array[$k]=(int)$v;
    			}else{
    				unset($optypes_array[$k]);
    			}
    		}
    		if(count($optypes_array)>0){
    			$where_array[] = 're_data.optype IN ('.implode(',', $optypes_array).')';
    		
    			$str_a=array();
    			foreach($optypes_array as $a){
    				$str_a[]='?';
    			}
    			$where_array_prepared[]='('.DB_PREFIX.'_data.optype IN ('.implode(',', $str_a).'))';
    			$where_value_prepared=array_merge($where_value_prepared, $optypes_array);
    		}
    	}elseif(isset($params['optype'])){
    		$where_array[] = DB_PREFIX.'_data.optype='.(int)$params['optype'];
    		$where_array_prepared[]='('.DB_PREFIX.'_data.optype = ?)';
    		$where_value_prepared[]=(int)$params['optype'];
    	}
    	
    	//$where_array[] = 're_topic.id=re_data.topic_id';
    	//$where_array_prepared[]='('.DB_PREFIX.'_topic.id='.DB_PREFIX.'_data.topic_id)';
    	
    	//echo '$params[\'topic_id\'] = '.$params['topic_id'].'<br>';
    	
    	if ( !is_array($params['topic_id']) && $params['topic_id'] != '' &&  (int)$params['topic_id'] != 0) {
    		$topic_id=(int)$params['topic_id'];
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    		$Structure_Manager = new Structure_Manager();
    		$category_structure = $Structure_Manager->loadCategoryStructure();
    		$childs = $Structure_Manager->get_all_childs($topic_id, $category_structure);
    		if ( count($childs) > 0 ) {
    			array_push($childs, $topic_id);
    			$where_array[] = DB_PREFIX.'_data.topic_id IN ('.implode(' , ', $childs).') ';
    			$str_a=array();
    			foreach($childs as $a){
    				$str_a[]='?';
    			}
    			$where_array_prepared[]='('.DB_PREFIX.'_data.topic_id IN ('.implode(',', $str_a).'))';
    			$where_value_prepared=array_merge($where_value_prepared, $childs);
    		} else {
    			$where_array[] = 're_data.topic_id='.$topic_id;
    			$where_array_prepared[]='('.DB_PREFIX.'_data.topic_id=?)';
    			$where_value_prepared[]=$topic_id;
    		}
    	}elseif(is_array($params['topic_id'])){
    		$topics_array=$params['topic_id'];
    		foreach ($topics_array as $k=>$v){
    			if((int)$v!=0){
    				$topics_array[$k]=(int)$v;
    			}else{
    				unset($topics_array[$k]);
    			}
    		}
    		if(count($topics_array)>0){
    			$where_array[] = DB_PREFIX.'_data.topic_id IN ('.implode(',', $topics_array).')';
    			$str_a=array();
    			foreach($topics_array as $a){
    				$str_a[]='?';
    			}
    			$where_array_prepared[]='('.DB_PREFIX.'_data.topic_id IN ('.implode(',', $str_a).'))';
    			$where_value_prepared=array_merge($where_value_prepared, $topics_array);
    		}
    		//$where_array[] = 're_data.topic_id IN ('.implode(',', $params['topic_id']).')';
    	}else{
    		unset($params['topic_id']);
    	}
    	
    	if ( isset($params['country_id']) && (int)$params['country_id'] != 0  ) {
    		$where_array[] = 're_data.country_id = '.(int)$params['country_id'];
    		$where_array_prepared[]='('.DB_PREFIX.'_data.country_id=?)';
    		$where_value_prepared[]=(int)$params['country_id'];
    	}else{
    		unset($params['country_id']);
    	}
    	
    	if ( isset($params['complex_id']) && (int)$params['complex_id'] != 0  ) {
    		$where_array[] = 're_data.complex_id = '.(int)$params['complex_id'];
    		$where_array_prepared[]='('.DB_PREFIX.'_data.complex_id=?)';
    		$where_value_prepared[]=(int)$params['complex_id'];
    	}else{
    		unset($params['complex_id']);
    	}
    	 
    	/*
    	if ( isset($params['id']) && (int)$params['id'] != 0  ) {
    		$where_array[] = 're_data.id = '.(int)$params['id'];
    		$where_array_prepared[]='('.DB_PREFIX.'_data.id=?)';
    		$where_value_prepared[]=(int)$params['id'];
    	}
    	*/
    	if ( isset($params['id']) && is_array($params['id'])  ) {
    		
    		if(!empty($params['id'])){
    			$str_a=array();
    			foreach($params['id'] as $k=>$_id){
    				if((int)$_id!=0){
    					$str_a[]='?';
    				}else{
    					unset($params['id'][$k]);
    				}
    			}
    			if(!empty($params['id'])){
    				$where_array_prepared[]='('.DB_PREFIX.'_data.id IN ('.implode(',', $str_a).'))';
    				$where_value_prepared=array_merge($where_value_prepared, $params['id']);
    			}
    		}else{
    			unset($params['id']);
    		}
    	}elseif(isset($params['id'])){
    		if((int)$params['id'] != 0){
    			$where_array[] = 're_data.id = '.(int)$params['id'];
    			$where_array_prepared[]='('.DB_PREFIX.'_data.id=?)';
    			$where_value_prepared[]=(int)$params['id'];
    		}else{
    			unset($params['id']);
    		}
    	}
    	//echo $_SESSION['user_domain_owner'];
    	if(isset($_SESSION['user_domain_owner']) && (int)$_SESSION['user_domain_owner']['user_id']!=0){
    		//$where_array[] = 're_data.user_id = '.(int)$params['user_id'];
    		$where_array_prepared[]='('.DB_PREFIX.'_data.user_id=?)';
    		$where_value_prepared[]=(int)$_SESSION['user_domain_owner']['user_id'];
    	}else{
    		if ( isset($params['user_id']) && (int)$params['user_id'] > 0  ) {
    			$where_array[] = 're_data.user_id = '.(int)$params['user_id'];
    			$where_array_prepared[]='('.DB_PREFIX.'_data.user_id=?)';
    			$where_value_prepared[]=(int)$params['user_id'];
    		}else{
    			unset($params['user_id']);
    		}
    	}
    	
    	
    	if ( isset($params['onlyspecial']) && (int)$params['onlyspecial'] > 0  ) {
    		$where_array[] = 're_data.hot = 1';
    		$where_array_prepared[]='('.DB_PREFIX.'_data.hot=1)';
    	}else{
    			unset($params['onlyspecial']);
    		}
    	
    	
    	if ( isset($params['price']) && $params['price'] != 0  ) {
    		$price_str=(int)str_replace(' ', '', $params['price']);
    		if($use_currency){
    			$where_array[] = DB_PREFIX.'_data.price  <= '.$price_str;
    			$where_array_prepared[]='((('.DB_PREFIX.'_data.price*'.DB_PREFIX.'_currency.course)/'.$price_koefficient.')<=?)';
    			$where_value_prepared[]=$price_str;
    		}else{
    			$where_array[] = DB_PREFIX.'_data.price  <= '.$price_str;
    			$where_array_prepared[]='('.DB_PREFIX.'_data.price<=?)';
    			$where_value_prepared[]=$price_str;
    		}
    		
    	}else{
    		unset($params['price']);
    	}
    	
    	if ( isset($params['price_min']) && $params['price_min'] != 0  ) {
    		$price_str=(int)str_replace(' ', '', $params['price_min']);
    		if($use_currency){
    			$where_array[] = DB_PREFIX.'_data.price  >= '.$price_str;
    			$where_array_prepared[]='((('.DB_PREFIX.'_data.price*'.DB_PREFIX.'_currency.course)/'.$price_koefficient.')>=?)';
    			$where_value_prepared[]=$price_str;
    		}else{
    			$where_array[] = DB_PREFIX.'_data.price  >= '.$price_str;
    			$where_array_prepared[]='('.DB_PREFIX.'_data.price>=?)';
    			$where_value_prepared[]=$price_str;
    		}
    	}else{
    		unset($params['price_min']);
    	}
    	////
    	if ( isset($params['price_pm']) && $params['price_pm'] != 0  ) {
    		$price_str=(int)str_replace(' ', '', $params['price_pm']);
    		$where_array[] = 're_data.price_pm  <= '.$price_str*$price_koefficient;
    		$where_array_prepared[]='('.DB_PREFIX.'_data.price<=?)';
    		$where_value_prepared[]=$price_str;
    	}else{
    		unset($params['price_pm']);
    	}
    	if ( isset($params['price_pm_min']) && $params['price_pm_min'] != 0  ) {
    		$price_str=(int)str_replace(' ', '', $params['price_pm_min']);
    		$where_array[] = 're_data.price_pm  >= '.$price_str*$price_koefficient;
    		$where_array_prepared[]='('.DB_PREFIX.'_data.price>=?)';
    		$where_value_prepared[]=$price_str;
    	}else{
    		unset($params['price_pm_min']);
    	}
    	//////
    	if ( isset($params['house_number']) && $params['house_number'] != ''  ) {
    		$number=trim($params['house_number']);
    		$number=preg_replace('/[^[:alnum:] ]/', '', $number);
    		$where_array[] = 're_data.number  = \''.$number.'\'';
    		$where_array_prepared[]='('.DB_PREFIX.'_data.number=?)';
    		$where_value_prepared[]=$number;
    	}else{
    		unset($params['house_number']);
    	}
    	
    	
    	
    	
    	if ( isset($params['region_id']) && (int)$params['region_id'] != 0  ) {
    		if(is_array($params['region_id']) && !empty($params['region_id'])){
    			$regions_array=$params['region_id'];
    			foreach ($regions_array as $k=>$v){
    				if((int)$v!=0){
    					$regions_array[$k]=(int)$v;
    				}else{
    					unset($regions_array[$k]);
    				}
    			}
    			if(count($regions_array)>0){
    				$where_array[] = 're_data.region_id IN ('.implode(',', $regions_array).')';
    				
    				$str_a=array();
    				foreach($regions_array as $a){
    					$str_a[]='?';
    				}
    				$where_array_prepared[]='('.DB_PREFIX.'_data.region_id IN ('.implode(',', $str_a).'))';
    				$where_value_prepared=array_merge($where_value_prepared, $regions_array);
    			}
    		}else{
    			$where_array[] = 're_data.region_id = '.(int)$params['region_id'];
    			$where_array_prepared[]='('.DB_PREFIX.'_data.region_id=?)';
    			$where_value_prepared[]=(int)$params['region_id'];
    		}
    	
    	}else{
    		unset($params['region_id']);
    	}
    	
    	if ( isset($params['spec']) &&  $params['spec']!='') {
    		$where_array[] = ' re_data.hot = 1 ';
    		$where_array_prepared[]='('.DB_PREFIX.'_data.hot=1)';
    	}else{
    		unset($params['spec']);
    	}
    	if ( isset($params['hot']) && $params['hot']!='' ) {
    		$where_array[] = ' re_data.hot = 1 ';
    		$where_array_prepared[]='('.DB_PREFIX.'_data.hot=1)';
    	}else{
    		unset($params['hot']);
    	}
    	
    	
    	
    	
    	
    	if ( isset($params['district_id']) && $params['district_id'] != 0  ) {
    		if(is_array($params['district_id']) && !empty($params['district_id'])){
    			$districts_array=$params['district_id'];
    			foreach ($districts_array as $k=>$v){
    				if((int)$v!=0){
    					$districts_array[$k]=(int)$v;
    				}else{
    					unset($districts_array[$k]);
    				}
    			}
    			if(count($districts_array)>0){
    				$where_array[] = 're_data.district_id IN ('.implode(',', $districts_array).')';
    				
    				$str_a=array();
    				foreach($districts_array as $a){
    					$str_a[]='?';
    				}
    				$where_array_prepared[]='('.DB_PREFIX.'_data.district_id IN ('.implode(',', $str_a).'))';
    				$where_value_prepared=array_merge($where_value_prepared, $districts_array);
    			}
    			unset($districts_array);
    		}else{
    			$where_array[] = 're_data.district_id = '.(int)$params['district_id'];
    			$where_array_prepared[]='('.DB_PREFIX.'_data.district_id=?)';
    			$where_value_prepared[]=(int)$params['district_id'];
    		}
    	
    	}else{
    		unset($params['district_id']);
    	}
    	
    	if ( isset($params['city_id']) && $params['city_id'] != 0  ) {
    		if(is_array($params['city_id']) && !empty($params['city_id'])){
    			$city_array=$params['city_id'];
    			foreach ($city_array as $k=>$v){
    				if((int)$v!=0){
    					$city_array[$k]=(int)$v;
    				}else{
    					unset($city_array[$k]);
    				}
    			}
    			if(count($city_array)>0){
    				$where_array[] = 're_data.city_id IN ('.implode(',', $city_array).')';
    				
    				$str_a=array();
    				foreach($city_array as $a){
    					$str_a[]='?';
    				}
    				$where_array_prepared[]='('.DB_PREFIX.'_data.city_id IN ('.implode(',', $str_a).'))';
    				$where_value_prepared=array_merge($where_value_prepared, $city_array);
    			}
    			unset($city_array);
    		}else{
    			$where_array[] = 're_data.city_id = '.(int)$params['city_id'];
    			$where_array_prepared[]='('.DB_PREFIX.'_data.city_id=?)';
    			$where_value_prepared[]=(int)$params['city_id'];
    		}
    	
    	}else{
    		unset($params['city_id']);
    	}
    	    	 
    	if ( isset($params['metro_id']) and $params['metro_id'] != 0  ) {
    		if(is_array($params['metro_id']) && !empty($params['metro_id'])){
    			$metro_array=$params['metro_id'];
    			foreach ($metro_array as $k=>$v){
    				if((int)$v!=0){
    					$metro_array[$k]=(int)$v;
    				}else{
    					unset($metro_array[$k]);
    				}
    			}
    			if(count($metro_array)>0){
    				$where_array[] = 're_data.metro_id IN ('.implode(',', $metro_array).')';
    				
    				$str_a=array();
    				foreach($metro_array as $a){
    					$str_a[]='?';
    				}
    				$where_array_prepared[]='('.DB_PREFIX.'_data.metro_id IN ('.implode(',', $str_a).'))';
    				$where_value_prepared=array_merge($where_value_prepared, $metro_array);
    			}
    			unset($metro_array);
    		}else{
    			$where_array[] = 're_data.metro_id = '.(int)$params['metro_id'];
    			$where_array_prepared[]='('.DB_PREFIX.'_data.metro_id=?)';
    			$where_value_prepared[]=(int)$params['metro_id'];
    		}
    	
    	}else{
    		unset($params['metro_id']);
    	}
    	
    	if ( isset($params['street_id']) and $params['street_id'] != 0  ) {
    		if(is_array($params['street_id']) && !empty($params['street_id'])){
    			$street_array=$params['street_id'];
    			foreach ($street_array as $k=>$v){
    				if((int)$v!=0){
    					$street_array[$k]=(int)$v;
    				}else{
    					unset($street_array[$k]);
    				}
    			}
    			if(count($street_array)>0){
    				$where_array[] = 're_data.street_id IN ('.implode(',', $street_array).')';
    				
    				$str_a=array();
    				foreach($street_array as $a){
    					$str_a[]='?';
    				}
    				$where_array_prepared[]='('.DB_PREFIX.'_data.street_id IN ('.implode(',', $str_a).'))';
    				$where_value_prepared=array_merge($where_value_prepared, $street_array);
    			}
    			unset($street_array);
    		}else{
    			$where_array[] = 're_data.street_id = '.(int)$params['street_id'];
    			$where_array_prepared[]='('.DB_PREFIX.'_data.street_id=?)';
    			$where_value_prepared[]=(int)$params['street_id'];
    		}
    	}else{
    		unset($params['street_id']);
    	}
    	
    	
    	
    	if(isset($params['srch_phone']) && $params['srch_phone'] !== NULL && trim($params['srch_phone']) !== ''){
    		$phone = preg_replace('/[^\d]/', '', $params['srch_phone']);
    		$sub_where=array();
    		$where_array_prepared_sub=array();
    		if($this->getConfigValue('allow_additional_mobile_number')){
    			$sub_where[] = '(re_data.ad_mobile_phone LIKE \'%'.$phone.'%\')';
    			
    			$where_array_prepared_sub[]='('.DB_PREFIX.'_data.ad_mobile_phone LIKE ?)';
    			$where_value_prepared[]='%'.$phone.'%';
    		}
    		if($this->getConfigValue('allow_additional_stationary_number')){
    			$sub_where[] = '(re_data.ad_stacionary_phone LIKE \'%'.$phone.'%\')';
    			
    			$where_array_prepared_sub[]='('.DB_PREFIX.'_data.ad_stacionary_phone LIKE ?)';
    			$where_value_prepared[]='%'.$phone.'%';
    		}
    		$sub_where[] = '(re_data.phone LIKE \'%'.$phone.'%\')';
    		
    		
    		$where_array_prepared_sub[]='('.DB_PREFIX.'_data.phone LIKE ?)';
    		$where_value_prepared[]='%'.$phone.'%';
    		$where_array_prepared[]='('.implode(' OR ', $where_array_prepared_sub).')';
    		
    		$where_array[]='('.implode(' OR ', $sub_where).')';
    	}else{
    		unset($params['srch_phone']);
    	}
    	
    	if(isset($params['srch_word']) and $params['srch_word'] !== NULL){
    		$sub_where=array();
    		$where_array_prepared_sub=array();
    		
    		$word=htmlspecialchars($params['srch_word']);
    		$sub_where[] = '(re_data.text LIKE \'%'.$word.'%\')';
    		
    		$where_array_prepared_sub[]='('.DB_PREFIX.'_data.text LIKE ?)';
    		$where_value_prepared[]='%'.$word.'%';
    		
    		/*
    		$sub_where[] = '(re_data.more1 LIKE \'%'.$word.'%\')';
    		
    		$where_array_prepared_sub[]='('.DB_PREFIX.'_data.more1 LIKE ?)';
    		$where_value_prepared[]='%'.$word.'%';
    		
    		
    		$sub_where[] = '(re_data.more2 LIKE \'%'.$word.'%\')';
    		
    		$where_array_prepared_sub[]='('.DB_PREFIX.'_data.more2 LIKE ?)';
    		$where_value_prepared[]='%'.$word.'%';
    		
    		$sub_where[] = '(re_data.more3 LIKE \'%'.$word.'%\')';
    		
    		$where_array_prepared_sub[]='('.DB_PREFIX.'_data.more3 LIKE ?)';
    		$where_value_prepared[]='%'.$word.'%';
    		
    		$where_array[]='('.implode(' OR ',$sub_where).')';
    		*/
    		$where_array_prepared[]='('.implode(' OR ', $where_array_prepared_sub).')';
    	}else{
    		unset($params['srch_word']);
    	}
    	
    	if(isset($params['room_count'])){
    		if(is_array($params['room_count']) && count($params['room_count'])>0){
    			$sub_where=array();
    			$where_array_prepared_sub=array();
    			foreach($params['room_count'] as $rq){
    				if($rq==4){
    					$sub_where[]='room_count>3';
    					$where_array_prepared_sub[]='('.DB_PREFIX.'_data.room_count>3)';
    				}elseif(0!=(int)$rq){
    					$sub_where[]='room_count='.(int)$rq;
    					$where_array_prepared_sub[]='('.DB_PREFIX.'_data.room_count=?)';
    					$where_value_prepared[]=(int)$rq;
    				}
    			}
    			if(count($sub_where)>0){
    				$where_array[]='('.implode(' OR ', $sub_where).')';
    				$where_array_prepared[]='('.implode(' OR ', $where_array_prepared_sub).')';
    			}
    		}elseif((int)$params['room_count']!=0){
    			$where_array[] = 're_data.room_count = '.(int)$params['room_count'];
    			$where_value_prepared[]=(int)$params['room_count'];
    			$where_array_prepared[]='('.DB_PREFIX.'_data.room_count=?)';
    		}else{
    			unset($params['room_count']);
    		}
    	}
    	
    	if(isset($params['added_in_days']) && 0!=(int)$params['added_in_days']){
    		$date_limit=time()-((int)$params['added_in_days'])*24*3600;
    		$where_value_prepared[]=date('Y-m-d H:i:s', $date_limit);
    		$where_array_prepared[]='('.DB_PREFIX.'_data.date_added>=?)';
    	}else{
    		unset($params['added_in_days']);
    	}
    	
    	
    	if(isset($params['srch_date_to'])){
    		$srch_date_to=preg_replace('/[^\d-]/', '', $params['srch_date_to']);
    		if($srch_date_to!='' && $srch_date_to!=0){
    			$where_array[]="(re_data.date_added<='".$srch_date_to."')";
    			
    			$where_value_prepared[]=$srch_date_to;
    			$where_array_prepared[]='('.DB_PREFIX.'_data.date_added<=?)';
    		}else{
	    		unset($params['srch_date_to']);
	    	}
	    }
	    
	    if(isset($params['srch_date_from'])){
	    	$srch_date_from=preg_replace('/[^\d-]/', '', $params['srch_date_from']);
	    	if($srch_date_from!='' && $srch_date_from!=0){
	    		$where_array[]="(re_data.date_added>='".$srch_date_from."')";
    		
    			$where_value_prepared[]=$srch_date_from;
    			$where_array_prepared[]='('.DB_PREFIX.'_data.date_added>=?)';
	    	}else{
	    		unset($params['srch_date_from']);
	    	}
	    }
    	
    	
    	if(isset($params['floor_min']) && (int)$params['floor_min']!=0){
    		$where_array[]="(re_data.floor>=".(int)$params['floor_min'].")";
    		$where_value_prepared[]=(int)$params['floor_min'];
    		$where_array_prepared[]='('.DB_PREFIX.'_data.floor*1 >= ?)';
    	}else{
    		unset($params['floor_min']);
    	}
    	
    	if(isset($params['floor_max']) && (int)$params['floor_max']!=0){
    		$where_array[]="(re_data.floor<=".(int)$params['floor_max'].")";
    		$where_value_prepared[]=(int)$params['floor_max'];
    		$where_array_prepared[]='('.DB_PREFIX.'_data.floor*1 <= ?)';
    	}else{
    		unset($params['floor_max']);
    	}
    	
    	if(isset($params['floor_count_min']) && (int)$params['floor_count_min']!=0){
    		$where_array[]="(re_data.floor_count>=".(int)$params['floor_count_min'].")";
    		$where_value_prepared[]=(int)$params['floor_count_min'];
    		$where_array_prepared[]='('.DB_PREFIX.'_data.floor_count*1 >= ?)';
    	}else{
    		unset($params['floor_count_min']);
    	}
    	 
    	if(isset($params['floor_count_max']) && (int)$params['floor_count_max']!=0){
    		$where_array[]="(re_data.floor_count<=".(int)$params['floor_count_max'].")";
    		$where_value_prepared[]=(int)$params['floor_count_max'];
    		$where_array_prepared[]='('.DB_PREFIX.'_data.floor_count*1 <= ?)';
    	}else{
    		unset($params['floor_count_max']);
    	}
    	
    	
    	if(isset($params['square_min']) && (int)$params['square_min']!=0){
    		$square_min=preg_replace('/[^\d.,]/', '', $params['square_min']);
    		$where_array[]="(re_data.square_all>=".$square_min.")";
    		$where_value_prepared[]=$square_min;
    		$where_array_prepared[]='('.DB_PREFIX.'_data.square_all*1 >= ?)';
    	}else{
    		unset($params['square_min']);
    	}
    	
    	if(isset($params['square_max']) && (int)$params['square_max']!=0){
    		$square_max=preg_replace('/[^\d.,]/', '', $params['square_max']);
    		$where_array[]='(re_data.square_all<='.$square_max.')';
    		$where_value_prepared[]=$square_max;
    		$where_array_prepared[]='('.DB_PREFIX.'_data.square_all*1 <= ?)';
    	}else{
    		unset($params['square_max']);
    	}
    	
    	
    	if(isset($params['not_first_floor']) && (int)$params['not_first_floor']==1){
    		$where_array[]="(re_data.floor <> 1)";
    		$where_array_prepared[]='('.DB_PREFIX.'_data.floor <> 1)';
    	}else{
    		unset($params['not_first_floor']);
    	}
    	
    	if(isset($params['not_last_floor']) && (int)$params['not_last_floor']==1){
    		$where_array[]="(re_data.floor <> re_data.floor_count)";
    		$where_array_prepared[]='('.DB_PREFIX.'_data.floor <> '.DB_PREFIX.'_data.floor_count)';
    	}else{
    		unset($params['not_last_floor']);
    	}
    	
    	if(isset($params['live_square_min']) && $params['live_square_min']!=0 && $params['live_square_min']!==''){
    		$square_min=preg_match('/[^\d.,]/', '', $params['live_square_min']);
    		$where_array[]="(re_data.square_live>=".$square_min.")";
    		$where_value_prepared[]=$square_min;
    		$where_array_prepared[]='('.DB_PREFIX.'_data.square_live>= ?)';
    	}else{
    		unset($params['live_square_min']);
    	}
    	 
    	if(isset($params['live_square_max']) && $params['live_square_max']!=0 && $params['live_square_max']!==''){
    		$square_max=preg_match('/[^\d.,]/', '', $params['live_square_max']);
    		$where_array[]="(re_data.square_live<=".$square_max.")";
    		$where_value_prepared[]=$square_max;
    		$where_array_prepared[]='('.DB_PREFIX.'_data.square_live<= ?)';
    	}else{
    		unset($params['live_square_max']);
    	}
    	
    	
    	if(isset($params['kitchen_square_min']) && $params['kitchen_square_min']!=0 && $params['kitchen_square_min']!==''){
    		$square_min=preg_match('/[^\d.,]/', '', $params['kitchen_square_min']);
    		$where_array[]="(re_data.square_kitchen>=".$square_min.")";
    		$where_value_prepared[]=$square_min;
    		$where_array_prepared[]='('.DB_PREFIX.'_data.square_kitchen>= ?)';
    	}else{
    		unset($params['kitchen_square_min']);
    	}
    	
    	if(isset($params['kitchen_square_max']) && $params['kitchen_square_max']!=0 && $params['kitchen_square_max']!==''){
    		$square_max=preg_match('/[^\d.,]/', '', $params['kitchen_square_max']);
    		$where_array[]="(re_data.square_kitchen<=".$square_max.")";
    		$where_value_prepared[]=$square_max;
    		$where_array_prepared[]='('.DB_PREFIX.'_data.square_kitchen<= ?)';
    	}else{
    		unset($params['kitchen_square_max']);
    	}
    	
    	
    	if(isset($params['is_phone']) && (int)$params['is_phone']==1){
    		$where_array[]='('.DB_PREFIX.'_data.is_telephone=1)';
    		$where_array_prepared[]='('.DB_PREFIX.'_data.is_telephone=1)';
    	}else{
    		unset($params['is_phone']);
    	}
    	
    	if(isset($params['is_internet']) && (int)$params['is_internet']==1){
    		$where_array[]='('.DB_PREFIX.'_data.is_internet=1)';
    		$where_array_prepared[]='('.DB_PREFIX.'_data.is_internet=1)';
    	}else{
    		unset($params['is_internet']);
    	}
    	
    	if(isset($params['is_furniture']) && (int)$params['is_furniture']==1){
    		$where_array[]='('.DB_PREFIX.'_data.furniture=1)';
    		$where_array_prepared[]='('.DB_PREFIX.'_data.furniture=1)';
    	}else{
    		unset($params['is_furniture']);
    	}
    	
    	if(isset($params['owner']) && (int)$params['owner']==1){
    		$where_array[]='('.DB_PREFIX.'_data.whoyuaare=1)';
    		$where_array_prepared[]='('.DB_PREFIX.'_data.whoyuaare=1)';
    	}else{
    		unset($params['owner']);
    	}
    	
    	if(isset($params['has_photo']) && (int)$params['has_photo']==1){
    		//print_r($_model);
    		$hasUploadify=false;
    		$hasUploads=false;
    		$uploadsFields=array();
    		foreach($_model['data'] as $item){
    			if($item['type']=='uploadify_image'){
    				$hasUploadify=true;
    				break;
    			}elseif($item['type']=='uploads'){
    				$hasUploads=true;
    				$uploadsFields[]=$item['name'];
    			}
    		}
    		
    		//print_r($uploadsFields);
    		
    		if($hasUploadify){
    			$where_array[]='((SELECT COUNT(*) FROM '.DB_PREFIX.'_data_image WHERE id='.DB_PREFIX.'_data.id)>0)';
    			$where_array_prepared[]='((SELECT COUNT(*) FROM '.DB_PREFIX.'_data_image WHERE id='.DB_PREFIX.'_data.id)>0)';
    		}elseif($hasUploads){
    			$sub_query=array();
    			foreach($uploadsFields as $uf){
    				$sub_query[]=DB_PREFIX.'_data.`'.$uf.'`<>\'\'';
    			}
    			$where_array_prepared[]='('.implode(' OR ', $sub_query).')';
    			$where_array[]='('.implode(' OR ', $sub_query).')';;
    		}
    		
    		
    	}else{
    		unset($params['has_photo']);
    	}
    	
    	if(isset($params['infra_greenzone']) && (int)$params['infra_greenzone']==1){
    		$where_array[]="(re_data.infra_greenzone=1)";
    		$where_array_prepared[]='('.DB_PREFIX.'_data.infra_greenzone=1)';
    	}else{
    		unset($params['infra_greenzone']);
    	}
    	
    	if(isset($params['infra_sea']) && (int)$params['infra_sea']==1){
    		$where_array[]="(re_data.infra_sea=1)";
    		$where_array_prepared[]='('.DB_PREFIX.'_data.infra_sea=1)';
    	}else{
    		unset($params['infra_sea']);
    	}
    	
    	if(isset($params['infra_sport']) && (int)$params['infra_sport']==1){
    		$where_array[]="(re_data.infra_sport=1)";
    		$where_array_prepared[]='('.DB_PREFIX.'_data.infra_sport=1)';
    	}else{
    		unset($params['infra_sport']);
    	}
    	
    	if(isset($params['infra_clinic']) && (int)$params['infra_clinic']==1){
    		$where_array[]="(re_data.infra_clinic=1)";
    		$where_array_prepared[]='('.DB_PREFIX.'_data.infra_clinic=1)';
    	}else{
    		unset($params['infra_clinic']);
    	}
    	
    	if(isset($params['infra_terminal']) && (int)$params['infra_terminal']==1){
    		$where_array[]="(re_data.infra_terminal=1)";
    		$where_array_prepared[]='('.DB_PREFIX.'_data.infra_terminal=1)';
    	}else{
    		unset($params['infra_terminal']);
    	}
    	
    	if(isset($params['infra_airport']) && (int)$params['infra_airport']==1){
    		$where_array[]="(re_data.infra_airport=1)";
    		$where_array_prepared[]='('.DB_PREFIX.'_data.infra_airport=1)';
    	}else{
    		unset($params['infra_airport']);
    	}
    	
    	if(isset($params['infra_bank']) && (int)$params['infra_bank']==1){
    		$where_array[]="(re_data.infra_bank=1)";
    		$where_array_prepared[]='('.DB_PREFIX.'_data.infra_bank=1)';
    	}else{
    		unset($params['infra_bank']);
    	}
    	
    	if(isset($params['infra_restaurant']) && (int)$params['infra_restaurant']==1){
    		$where_array[]="(re_data.infra_restaurant=1)";
    		$where_array_prepared[]='('.DB_PREFIX.'_data.infra_restaurant=1)';
    	}else{
    		unset($params['infra_restaurant']);
    	}
    	
    	if(isset($params['object_state']) && is_array($params['object_state']) && count($params['object_state'])>0){
    		$state_array=$params['object_state'];
    		foreach ($state_array as $k=>$v){
    			if((int)$v!=0){
    				$state_array[$k]=(int)$v;
    			}else{
    				unset($state_array[$k]);
    			}
    		}
    		if(count($state_array)>0){
    			$where_array[] = 're_data.object_state IN ('.implode(',', $state_array).')';
    			
    			$str_a=array();
    			foreach($state_array as $a){
    				$str_a[]='?';
    			}
    			$where_array_prepared[]='('.DB_PREFIX.'_data.object_state IN ('.implode(',', $str_a).'))';
    			$where_value_prepared=array_merge($where_value_prepared, $state_array);
    		}
    	}else{
    		unset($params['object_state']);
    	}
    	
    	if(isset($params['object_type']) && is_array($params['object_type']) && count($params['object_type'])>0){
    		$state_array=$params['object_type'];
    		foreach ($state_array as $k=>$v){
    			if((int)$v!=0){
    				$state_array[$k]=(int)$v;
    			}else{
    				unset($state_array[$k]);
    			}
    		}
    		if(count($state_array)>0){
    			$where_array[] = 're_data.object_destination IN ('.implode(',', $state_array).')';
    			
    			$str_a=array();
    			foreach($state_array as $a){
    				$str_a[]='?';
    			}
    			$where_array_prepared[]='('.DB_PREFIX.'_data.object_destination IN ('.implode(',', $str_a).'))';
    			$where_value_prepared=array_merge($where_value_prepared, $state_array);
    		}
    	}else{
    		unset($params['object_type']);
    	}
    	/*
    	if(isset($params['object_destination']) && is_array($params['object_destination']) && count($params['object_destination'])>0){
    		$where_array[]="(re_data.object_destination IN (".implode(',', $params['object_destination'])."))";
    	}else{
    		unset($params['object_destination']);
    	}
    	*/
    	if(isset($params['aim']) && is_array($params['aim']) && count($params['aim'])>0){
    		$state_array=$params['aim'];
    		foreach ($state_array as $k=>$v){
    			if((int)$v!=0){
    				$state_array[$k]=(int)$v;
    			}else{
    				unset($state_array[$k]);
    			}
    		}
    		if(count($state_array)>0){
    			$where_array[] = 're_data.aim IN ('.implode(',', $state_array).')';
    			
    			$str_a=array();
    			foreach($state_array as $a){
    				$str_a[]='?';
    			}
    			$where_array_prepared[]='('.DB_PREFIX.'_data.aim IN ('.implode(',', $str_a).'))';
    			$where_value_prepared=array_merge($where_value_prepared, $state_array);
    		}
    		
    		
    	}else{
    		unset($params['aim']);
    	}
    	
    	if(isset($params['export_afy']) && (int)$params['export_afy']==1 && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/afyexporter/admin/admin.php')){
    		$where_array_prepared[]='('.DB_PREFIX.'_data.export_afy=1)';
    	}else{
    		unset($params['export_afy']);
    	}
    	
    	if(isset($params['export_cian']) && (int)$params['export_cian']==1 && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/cianexporter/admin/admin.php')){
    		$where_array_prepared[]='('.DB_PREFIX.'_data.export_cian=1)';
    	}else{
    		unset($params['export_cian']);
    	}
    	
    	if(isset($params['geocoords'])){
    		if(preg_match('/([-]?[0-9]{2,3}\.[0-9]{6}),([-]?[0-9]{2,3}\.[0-9]{6}):([-]?[0-9]{2,3}\.[0-9]{6}),([-]?[0-9]{2,3}\.[0-9]{6})/', $params['geocoords'], $matches)){
    			//print_r();
    			$lat_min=$matches[1];
    			$lng_min=$matches[2];
    			$lat_max=$matches[3];
    			$lng_max=$matches[4];
    			$diapasones=array();
    			if($lng_min>0 && $lng_max<0){
    				$diapasones[]=array(
    					'lat_min'=>$lat_min,
    					'lat_max'=>$lat_max,
    					'lng_min'=>$lng_min,
    					'lng_max'=>180
    				);
    				$diapasones[]=array(
    						'lat_min'=>$lat_min,
    						'lat_max'=>$lat_max,
    						'lng_min'=>-180,
    						'lng_max'=>$lng_max
    				);
    			}else{
    				$diapasones[]=array(
    					'lat_min'=>$lat_min,
    					'lat_max'=>$lat_max,
    					'lng_min'=>$lng_min,
    					'lng_max'=>$lng_max
    				);
    			}
    			
    			$where_array_prepared[]='('.DB_PREFIX.'_data.geo_lat IS NOT NULL AND '.DB_PREFIX.'_data.geo_lng IS NOT NULL)';
    			
    			$subarray=array();
    			foreach($diapasones as $diapasone){
    				
    				$subarray[]='('.DB_PREFIX.'_data.geo_lat >=? AND '.DB_PREFIX.'_data.geo_lat <= ? AND '.DB_PREFIX.'_data.geo_lng >=? AND '.DB_PREFIX.'_data.geo_lng <= ?)';
    				$where_value_prepared[]=$diapasone['lat_min'];
    				$where_value_prepared[]=$diapasone['lat_max'];
    				$where_value_prepared[]=$diapasone['lng_min'];
    				$where_value_prepared[]=$diapasone['lng_max'];
    			}
    			
    			$where_array_prepared[]='('.implode(' OR ', $subarray).')';
    		}
    		
    	}elseif(isset($params['has_geo']) && (int)$params['has_geo']==1){
    		$where_array[]='('.DB_PREFIX.'_data.geo_lat IS NOT NULL AND '.DB_PREFIX.'_data.geo_lng IS NOT NULL)';
    		$where_array_prepared[]='('.DB_PREFIX.'_data.geo_lat IS NOT NULL AND '.DB_PREFIX.'_data.geo_lng IS NOT NULL)';
    	}else{
    		unset($params['has_geo']);
    	}
    	
    	if(isset($params['minbeds']) && (int)$params['minbeds']!=0){
    		$where_array[]="(re_data.bedrooms_count >= ".(int)$params['minbeds'].")";
    		$where_value_prepared[]=(int)$params['minbeds'];
    		$where_array_prepared[]='('.DB_PREFIX.'_data.bedrooms_count>=?)';
    	}else{
    		unset($params['minbeds']);
    	}
    	
    	if(isset($params['minbaths']) && (int)$params['minbaths']!=0){
    		$where_array[]="(re_data.bathrooms_count >=".(int)$params['minbaths'].")";
    		$where_value_prepared[]=(int)$params['minbaths'];
    		$where_array_prepared[]='('.DB_PREFIX.'_data.bathrooms_count>=?)';
    	}else{
    		unset($params['minbaths']);
    	}
    	
    	if(isset($params['vip_status']) && (int)$params['vip_status']!=0){
    		$where_array[]='('.DB_PREFIX.'_data.vip_status_end<>0 AND '.DB_PREFIX.'_data.vip_status_end >= '.time().')';
    		$where_array_prepared[]='('.DB_PREFIX.'_data.vip_status_end<>0 AND '.DB_PREFIX.'_data.vip_status_end >= '.time().')';
    	}else{
    		unset($params['vip_status']);
    	}
    	
    	if(isset($params['premium_status']) && (int)$params['premium_status']!=0){
    		$where_array[]='('.DB_PREFIX.'_data.premium_status_end<>0 AND '.DB_PREFIX.'_data.premium_status_end >= '.time().')';
    		$where_array_prepared[]='('.DB_PREFIX.'_data.premium_status_end<>0 AND '.DB_PREFIX.'_data.premium_status_end >= '.time().')';
    	}else{
    		unset($params['premium_status']);
    	}
    	
    	if(isset($params['bold_status']) && (int)$params['bold_status']!=0){
    		$where_array[]='('.DB_PREFIX.'_data.bold_status_end<>0 AND '.DB_PREFIX.'_data.bold_status_end >= '.time().')';
    		$where_array_prepared[]='('.DB_PREFIX.'_data.bold_status_end<>0 AND '.DB_PREFIX.'_data.bold_status_end >= '.time().')';
    	}else{
    		unset($params['bold_status']);
    	}
    	 
    	if ( $params['admin'] != 1 ) {
    		$where_array[] = 're_data.active=1';
    		$where_array_prepared[]='('.DB_PREFIX.'_data.active=1)';
    		if(isset($_model['data']['is_predeleted'])){
    			$where_array_prepared[]='('.DB_PREFIX.'_data.is_predeleted<>1)';
    		}
    		//echo $_SESSION['current_user_group_name'];
    	}else{
    		if($_SESSION['current_user_group_name']!='admin'){
    			if(isset($_model['data']['is_predeleted'])){
    				$where_array_prepared[]='('.DB_PREFIX.'_data.is_predeleted<>1)';
    			}
    		}
    	
  			if ( $params['active'] == 1 ) {
    	    	$where_array[] = ''.DB_PREFIX.'_data.active=1';
	    		$where_array_prepared[]='('.DB_PREFIX.'_data.active=1)';
	    	} elseif ( $params['active'] == 'notactive' ) {
	    		$where_array[] = 're_data.active=0';
	    		$where_array_prepared[]='('.DB_PREFIX.'_data.active=0)';
	    	}
    	}
    	
    	if(isset($params['is_predeleted']) && (int)$params['is_predeleted']!=0){
    		if(isset($_model['data']['is_predeleted'])){
    			$where_array_prepared[]='('.DB_PREFIX.'_data.is_predeleted=1)';
    		}
    	}
    	
    	if ( $this->getConfigValue('apps.company.timelimit') ) {
    		$current_time = time();
    	
    		$where_array[] = 're_data.user_id=u.user_id';
    		$where_array[] = 'u.company_id=c.company_id';
    		$where_array[] = "c.start_date <= $current_time";
    		$where_array[] = "c.end_date >= $current_time";
    		$add_from_table .= ' , re_user u, re_company c ';
    		
    		$left_joins[]='LEFT JOIN '.DB_PREFIX.'_user u USING(user_id)';
    		$left_joins[]='LEFT JOIN '.DB_PREFIX.'_company c ON u.company_id=c.company_id';
    		//$where_array_prepared[]='('.DB_PREFIX.'_data.user_id=u.user_id)';
    		//$where_array_prepared[]='(u.company_id=c.company_id)';
    		$where_array_prepared[]='(c.start_date<=?)';
    		$where_value_prepared[]=$current_time;
    		$where_array_prepared[]='(c.end_date >=?)';
    		$where_value_prepared[]=$current_time;
    	}
    	
    	
    	if ( $_billing_on && $premium  ) {
    		$where_array[] = 're_data.premium_status_end >= '.time();
    		$where_value_prepared[]=time();
    		$where_array_prepared[]='('.DB_PREFIX.'_data.premium_status_end >= ?)';
    	} elseif ($_billing_on && $params['vip']==1 ) {
    		$where_array[] = '(re_data.vip_status_end<>0 AND re_data.vip_status_end >= '.time().')';
    		$where_value_prepared[]=time();
    		$where_array_prepared[]='('.DB_PREFIX.'_data.vip_status_end<>0 AND '.DB_PREFIX.'_data.vip_status_end >= ?)';
    	} elseif ($_billing_on && $params['premium']==1 ) {
    		$where_array[] = '(re_data.premium_status_end<>0 AND re_data.premium_status_end >= '.time().')';
    		$where_value_prepared[]=time();
    		$where_array_prepared[]='('.DB_PREFIX.'_data.premium_status_end<>0 AND '.DB_PREFIX.'_data.premium_status_end >= ?)';
    	} elseif($_billing_on && $params['admin'] == 1) {
    		//$where_array[] = '(re_data.premium_status_end < '.time().')';
    		//$where_array[] = 're_data.premium_status_end = 0';
    	}elseif($_billing_on){
    		$where_array[] = '(re_data.premium_status_end < '.time().')';
    		$where_value_prepared[]=time();
    		$where_array_prepared[]='('.DB_PREFIX.'_data.premium_status_end < ?)';
    	}
    	/*
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/template_search.php')){
    		require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/template_search.php');
    		$Template_Search=new Template_Search();
    		$results=$Template_Search->run();
    		if(isset($results['where'])){
    			$where_array=array_merge($where_array, $results['where']);
    		}
    		if(isset($results['params'])){
    			$params=array_merge($params, $results['params']);
    		}
    	}
    	*/
    	if ( isset($params['only_img']) && $params['only_img'] ) {
    		$where_array[] = 're_data.id=i.id';
    		$where_array_prepared[]='('.DB_PREFIX.'_data.id=i.id)';
    		$add_from_table .= ' , re_data_image i ';
    	}
    	
    	/*print_r( array(
    		'where_array'=>$where_array,
    		'add_from_table'=>$add_from_table,
    		'add_select_value'=>$add_select_value,
    		'params'=>$params,
    		'where_array_prepared'=>$where_array_prepared,
    		'where_value_prepared'=>$where_value_prepared,
    		'left_joins'=>$left_joins,
    		'select_what'=>$select_what
    	));*/
    	return array(
    		'where_array'=>$where_array,
    		'add_from_table'=>$add_from_table,
    		'add_select_value'=>$add_select_value,
    		'params'=>$params,
    		'where_array_prepared'=>$where_array_prepared,
    		'where_value_prepared'=>$where_value_prepared,
    		'left_joins'=>$left_joins,
    		'select_what'=>$select_what
    	);
    }
    
    function makeGrid($params, $_settings=array()){
    	print_r($params);
    	$result=array();
    	
    	$defaults=array(
    		'pagination'=>false,
    		'map_data'=>false,
    		'url'=>false,
    		'format'=>'html'
    	);
    	
    	$settings=array_merge($defaults, $_settings);
    	//print_r($settings);
    	
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable')==1){
    		$_billing_on=true;
    	}else{
    		$_billing_on=false;
    	}
    	if($_billing_on){
    		$res = $this->getDataBase($params, false, true );
    	}else{
    		$res = $this->getDataBase($params);
    	}
    	
    	//$result=$res;
    	
    	
    	$pairs=array();
    	foreach ( $res['_params'] as $key => $value ) {
    		if($key=='order' || $key=='asc'){
    			continue;
    		}
    		if(is_array($value)){
    			if(count($value)>0){
    				foreach($value as $v){
    					if($v!=''){
    						$pairs[] = $key.'[]='.$v;
    					}
    				}
    			}
    		}elseif ( $value != '') {
    			$pairs[] = "$key=$value";
    		}
    	}
    	
    	if ( is_array($pairs) ) {
    		$url = $settings['url'].'?'.implode('&', $pairs);
    	}else{
    		$url = $settings['url'].'?key=value';
    	}
    	
    	$result['sort_url']=$url;
    	
    	if($settings['pagination']){
    		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/page_navigator.php')){
    			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/page_navigator.php';
    			$paging=Page_Navigator::getPagingArray($res['_total_records'], $res['_current_page'], $res['_limit'], $res['_params'], $settings['url']);
    			$result['pagination']=$paging;
    		}
    	}
    	
    	echo '<pre>';
    	print_r($res['_params']);
    	print_r($result);
    	echo '</pre>';
    }
    
    function getDataBase($params, $random = false, $premium=false) {
    	
    	$result_data=array();
    	 
    	
    	 
    	$this_is_favorites=false;
    	 
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable')==1){
    		$_billing_on=true;
    	}else{
    		$_billing_on=false;
    	}
    	 
    	if(isset($params['favorites']) && !empty($params['favorites'])){
    		$this_is_favorites=true;
    	}
    	 
    	if ( $this->getConfigValue('currency_enable') ) {
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/currency/admin/admin.php';
    		$CM=new currency_admin();
    	}
    
    	if(isset($params['_collect_user_info']) && $params['_collect_user_info']==1){
    		$_collect_user_info=true;
    		unset($params['_collect_user_info']);
    	}else{
    		$_collect_user_info=false;
    	}
    
    	$this->grid_total = 0;
    	 
    	$preparedParams=$this->prepareRequestParams($params, $premium);
    	 
    	$where_array=$preparedParams['where_array'];
    	$add_from_table=$preparedParams['add_from_table'];
    	$add_select_value=$preparedParams['add_select_value'];
    	$params=$preparedParams['params'];
    	 
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/template_search.php')){
    		require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/template_search.php');
    		$Template_Search=new Template_Search();
    		$results=$Template_Search->run();
    		if(isset($results['where'])){
    			$where_array=array_merge($where_array, $results['where']);
    		}
    		if(isset($results['params'])){
    			$params=array_merge($params, $results['params']);
    		}
    	}
    
    	if ( count($where_array)>0 ) {
    		$where_statement = " WHERE ".implode(' AND ', $where_array);
    	}
    
    	$order=$this->prepareSortOrder($params, $random, $premium);
    
    	
    	if ( !isset($params['page']) or $params['page'] == 0 ) {
    		$page = 1;
    	} else {
    		$page = $params['page'];
    	}
    
    
    
    
    	if ( $this->getConfigValue('currency_enable') ) {
    		$query = "select count(*) as total from re_data LEFT JOIN re_currency ON re_data.currency_id=re_currency.currency_id, re_topic $add_from_table $where_statement ";
    	}else{
    		$query = "select count(*) as total from re_data, re_topic $add_from_table $where_statement ";
    	}
    	
    
    	$this->db->exec($query);
    	if ( !$this->db->success ) {
    		echo $this->db->error.'<br>';
    	}
    	$this->db->fetch_assoc();
    	$total = $this->db->row['total'];
    	$this->grid_total = $total;
    	
    	
    	
    	
    	
    	
    	//global $smarty;
    	
    
    
    	$limit = $this->getConfigValue('per_page');
    
    	if ( $params['vip'] == 1 ) {
    		if ( $params['per_page'] > 0 ) {
    			$limit = $params['per_page'];
    		} else {
    			$limit = $this->getConfigValue('vip_rotator_number');
    		}
    	} else {
    		if(isset($params['page_limit']) && $params['page_limit']!=0){
    			$limit = $params['page_limit'];
    		}else{
    			$limit = $this->getConfigValue('per_page');
    		}
    			
    	}
    	if ( $premium ) {
    		$limit = 5;
    	}
    
    
    	$max_page=ceil($total/$limit);
    	
    
    	if($page>$max_page){
    		$page=1;
    		$params['page']=1;
    	}
    
    	$start = ($page-1)*$limit;
    
    	$pager_params=$params;
    	 
    	/* 
    	unset($params['order']);
    	unset($params['asc']);
    	unset($params['favorites']);
    
    	if ( preg_match('/\/special\//', $_SERVER['REQUEST_URI']) ) {
    		unset($params['spec']);
    		unset($pager_params['spec']);
    	}
    */
    
    	
    
    
    	
    
    
    	if ( $this->getConfigValue('apps.company.timelimit') ) {
    		if ( $this->getConfigValue('currency_enable') ) {
    			$query = "select re_currency.code AS currency_code, re_currency.name AS currency_name, ((re_data.price*re_currency.course)/".$CM->getCourse(CURRENT_CURRENCY).") AS price_ue, re_data.*, re_topic.name as type_sh $add_select_value from re_data LEFT JOIN re_currency ON re_data.currency_id=re_currency.currency_id, re_topic $add_from_table $where_statement ORDER BY ".$order.($params['no_portions']==1 ? '' : " LIMIT ".$start.", ".$limit);
    		} else {
    			$query = "select re_data.*, re_data.price AS price_ue, re_topic.name as type_sh $add_select_value from re_data, re_topic $add_from_table $where_statement order by ".$order.($params['no_portions']==1 ? '' : " LIMIT ".$start.", ".$limit);
    		}
    		 
    	} else {
    		if ( $this->getConfigValue('currency_enable') ) {
    			$query = "select re_currency.code AS currency_code, re_currency.name AS currency_name, ((re_data.price*re_currency.course)/".$CM->getCourse(CURRENT_CURRENCY).") AS price_ue, re_data.*, re_topic.name as type_sh $add_select_value from re_data LEFT JOIN re_currency ON re_data.currency_id=re_currency.currency_id, re_topic $add_from_table $where_statement ORDER BY ".$order.($params['no_portions']==1 ? '' : " LIMIT ".$start.", ".$limit);
    		} else {
    			$query = "select re_data.*, re_data.price AS price_ue, re_topic.name as type_sh $add_select_value from re_data, re_topic $add_from_table $where_statement ORDER BY ".$order.($params['no_portions']==1 ? '' : " LIMIT ".$start.", ".$limit);
    		}
    	}
    	
    	$this->db->exec($query);
    	if ( !$this->db->success ) {
    		echo $this->db->error.'<br>';
    	}
    
    	$ra = array();
    	$i = 0;
    	if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/company/company.xml') ) {
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
    		$Account = new Account;
    
    	}
    
    	while ( $this->db->fetch_assoc() ) {
    		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/company/company.xml') ) {
    			$company_profile = $Account->get_company_profile($this->db->row['user_id']);
    			$this->db->row['company'] = $company_profile['name']['value'];
    		}
    		$ra[$i] = $this->db->row;
    		$i++;
    	}
    	 
    
    	 
    	 
    	 
    	 
    	$ra=$this->transformGridData($ra);
    	
    	$result_data['_total_records']=$total;
    	$result_data['_max_page']=$max_page;
    	$result_data['_current_page']=$params['page'];
    	$result_data['_records']=$ra;
    	$result_data['_params']=$params;
    	$result_data['_limit']=$limit;
    	return $result_data;
    
    	return $ra;
    }
}
?>
