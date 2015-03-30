<?php
/**
 * Shop Grid constructor
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
//class Shop_Grid_Constructor extends Shop_Product_Manager {
class Shop_Grid_Constructor extends Grid_Constructor {
    /**
     * Constructor
     */
    function __construct() {
        //$this->Shop_Product_Manager();
        parent::__construct();
    }
    
    /**
     * Main
     * @param
     * @return
     */
    function main () {
    	
    	$viewtype=$this->getRequestValue('viewtype');
    	
    	if($viewtype==NULL){
			if(isset($_SESSION['viewtype'])){
				$params['viewtype']=$_SESSION['viewtype'];
				$viewtype=$_SESSION['viewtype'];
			}else{
				$params['viewtype']='';
			}
		}else{
			$_SESSION['viewtype'] = $viewtype;
		}
    	
    	
    	
    	switch($viewtype){
    		case 'thumbs' : {
    			$this->template->assert('grid_type', 'thumbs');
    			break;
    		}
    		default : {
    			$this->template->assert('grid_type', 'grid');
    		}
    	}
    	//echo $viewtype;
    	/*echo 1;
    	if(preg_match('/topic(\d*).html/',$_SERVER['REQUEST_URI'],$matches)){
    		echo 2;
    	}*/
    	if(preg_match('/topic(\d*).html/',$_SERVER['REQUEST_URI'],$matches) && $this->isTopicExists($matches[1])){
    		$this->setRequestValue('topic_id', $matches[1]);
    		
    	}else{
    		if($x=$this->cityTopicUrlFind($_SERVER['REQUEST_URI'])){
    			$this->setRequestValue('city_id', $x[0]);
    			$this->setRequestValue('topic_id', $x[1]);
    			$gorod_name = $x[2];
    		}elseif($x=$this->topicUrlFind($_SERVER['REQUEST_URI'])){
    			$this->setRequestValue('topic_id', $x);
    			
    		}else{
    			if($this->getConfigValue('apps.seo.level_enable')==1){
    				$ru=trim($_SERVER['REQUEST_URI'],'/');
    				if(SITEBILL_MAIN_URL!=''){
    					$ru=str_replace(trim(SITEBILL_MAIN_URL,'/').'/', '', $ru);
    				}
    				if($this->getConfigValue('apps.shop.namespace')!=''){
    					$x=explode('/',$ru);
    					if($x[0]==$this->getConfigValue('apps.shop.namespace')){
    						$ru=trim(str_replace($this->getConfigValue('apps.shop.namespace'), '', $ru),'/');
    					}
    				}
    				$Structure=new Structure_Manager();
    				$urls=$Structure->loadCategoriesUrls();
    				$urls_to_ids=array_flip($urls);
    					
    				$parts=explode('?',$ru);
    				if(isset($urls_to_ids[$parts[0]])){
    					$this->setRequestValue('topic_id', $urls_to_ids[$parts[0]]);
    				}
    			}
    		}
    	}
    	
    	//print_r($_REQUEST);
    	
    	/*
       	if(preg_match('/topic(\d*).html/',$_SERVER['REQUEST_URI'],$matches)){
			$this->setRequestValue('topic_id', $matches[1]);
		}else{
			if($x=$this->topicUrlFind($_SERVER['REQUEST_URI'])){
				$this->setRequestValue('topic_id', $x);
			}
		}*/
    	
    	if(preg_match('/vendor(\d*).html/',$_SERVER['REQUEST_URI'],$matches)){
			$this->setRequestValue('vendor_id', $matches[1]);
			
		}
		
    	if(preg_match('/tag(\d*).html/',$_SERVER['REQUEST_URI'],$matches)){
			$this->setRequestValue('tag_id', $matches[1]);
			
		}
		
		

		
		$params['keyword'] = $this->getRequestValue('keyword');
		$params['topic_id'] = $this->getRequestValue('topic_id');
		$params['vendor_id'] = $this->getRequestValue('vendor_id');
		$params['tag_id'] = $this->getRequestValue('tag_id');
		$params['order'] = $this->getRequestValue('order');
		$params['page'] = $this->getRequestValue('page');
		$params['asc'] = $this->getRequestValue('asc');
		$params['price'] = $this->getRequestValue('price');
		$params['newsort'] = $this->getRequestValue('newsort');
		$params['availablesort'] = $this->getRequestValue('availablesort');
		$params['city_id'] = $_SESSION['city_id'];
		$params['pub_type'] = $this->getRequestValue('pub_type');
		$params['admin'] = $this->getRequestValue('admin');
		
		//print_r($params);
		
		$this->template->assert('seo_description', '');
		if($params['topic_id']!=NULL){
			$this->template->assert('main_file_tpl', 'product_grid.tpl');
			$Structure_Manager = new Structure_Manager();
	        $category_structure = $Structure_Manager->loadCategoryStructure();
	        //echo '<pre>';
	        //echo $params['topic_id'];
	        //print_r($category_structure);
	        $this->template->assert('topic_description', $category_structure['catalog'][$params['topic_id']]['description']);
	        if($params['page']>1){
	        	$this->template->assert('title', htmlspecialchars($category_structure['catalog'][$params['topic_id']]['name']).' Страница '.$params['page']);
	        }else{
	        	$this->template->assert('title', htmlspecialchars($category_structure['catalog'][$params['topic_id']]['name']));
	        }
	        $this->template->assert('meta_title', htmlspecialchars($category_structure['catalog'][$params['topic_id']]['meta_title']));
	        $this->template->assert('meta_description', htmlspecialchars($category_structure['catalog'][$params['topic_id']]['meta_description']));
	        $this->template->assert('meta_keywords', htmlspecialchars($category_structure['catalog'][$params['topic_id']]['meta_keywords']));
	         
	        $this->template->assert('category_name', htmlspecialchars($category_structure['catalog'][$params['topic_id']]['name']));
	        $this->template->assert('parent_category_name', htmlspecialchars($category_structure['catalog'][$category_structure['catalog'][$params['topic_id']]['parent_id']]['name']));
            if((int)$params['page']<=1){
            	$this->template->assert('seo_description', $category_structure['catalog'][$params['topic_id']]['description']);
            }
            
	        $res = $this->get_sitebill_adv_ext( $params );
	        //print_r($res);
	        $this->template->assert('category_tree', $this->get_category_tree( $params, $category_structure ) );
	        
	        $this->template->assert('breadcrumbs', $this->get_category_breadcrumbs( $params, $category_structure, SITEBILL_MAIN_URL ) );
	        $products=$this->get_shop_grid($res);
	        $this->template->assert('products', $products);
		}elseif($params['vendor_id']!=NULL){
			$this->template->assert('main_file_tpl', 'product_grid.tpl');
			$vendor=$this->getVendorInfoById($params['vendor_id']);
			if((int)$params['page']<=1){
            	$this->template->assert('seo_description', $vendor['seo_text']);
            }
			$res = $this->get_sitebill_adv_ext( $params );
			$products=$this->get_shop_grid($res);
	        $this->template->assert('products', $products);
		}elseif($params['tag_id']!=NULL){
			$this->template->assert('main_file_tpl', 'product_grid.tpl');
			$res = $this->get_sitebill_adv_ext( $params );
			$products=$this->get_shop_grid($res);
	        $this->template->assert('products', $products);
		}else{
		
			if($params['keyword']!=NULL){
				$this->template->assert('keyword', $params['keyword']);
				$this->template->assert('breadcrumbs', 
                    $this->get_breadcrumbs(
                        array(
                    		'<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>', 
            				'Поиск'
            			)));
				$this->template->assert('title', 'Поиск');
				$this->template->assert('main_file_tpl', 'product_grid.tpl');
				$res = $this->get_sitebill_adv_ext( $params );
				
				$products=$this->get_shop_grid($res);
				$this->template->assert('products', $products);
			}else{
				$this->template->assert('main_file_tpl', 'start.tpl');
				if($this->getConfigValue('theme')=='buvitrina'){
					$params['product_state']='2';
				}
				$params['order'] = 'id';
				$params['asc'] = 'desc';
				
				$res = $this->get_sitebill_adv_ext( $params );
				//print_r($res);
				$this->setRequestValue('admin', 0);
				$products=$this->get_shop_grid_on_main($res);
				$this->template->assert('products', $products);
			}
		}
        
		
    }
    
	function get_shop_grid_on_main($adv){
    	global $topic_id;

        //$this->template->assign('main_file_tpl', 'start.tpl');
        
        $page = $this->getRequestValue('page');
        if ( $page == '' ) {
            $page = 1;
        }
        $ra = array();
        foreach ($adv as $k => $v) {
            $imgs=$this->get_image_array('shop_product', 'shop_product', 'product_id', $v['product_id']);
            $v['image']=$imgs[0]['img_preview'];
            $ra[] = $v;                
        }
        
        //$this->template->assign('products', $ra);
        return $ra;
        return true;
    }
    
    
    function get_shop_grid($adv){
    	global $topic_id;

        $clr = 0;
        $counter = 0;
        $rc = 'r2';
        $array_count = count($adv);
        //echo $array_count;
        
        $page = $_REQUEST['page'];
        if ( $page == '' ) {
            $page = 1;
        }
        $ra = array();
        foreach ($adv as $k => $v) {
            	$imgs=$this->get_image_array('shop_product', 'shop_product', 'product_id', $v['product_id']);
            	$v['image']=$imgs[0]['img_preview'];
                $ra[] = $v;                
        }
        
        $get = $_GET;
        if ( isset($get['order']) ) unset($get['order']);
        if ( isset($get['asc']) )   unset($get['asc']);

        return $ra;
    }
    
	function topicUrlFind($request_uri){
		  
		if(preg_match('/([-0-9A-Za-z_]+.html)/',$request_uri,$matches)){

			$query="SELECT id FROM ".DB_PREFIX."_topic WHERE url='".mysql_real_escape_string($matches[1])."'";
			$this->db->exec($query);
			if($this->db->success AND $this->db->num_rows()>0){
				$this->db->fetch_assoc();
				return $this->db->row['id'];
			}else{
				return FALSE;
			}

		}else{
			return FALSE;
		}
		//echo $request_uri;
		 
	}
	
	function get_sitebill_adv_ext_random( $params ) {
		$query='SELECT * FROM re_shop_product WHERE active=1 AND '.((!defined('SITE_LANG')) ? 'language_id=0' : 'language_id='.SITE_LANG).' ORDER BY RAND() LIMIT 0,15';
		//$query = "select re_shop_product.*, re_topic.name as type_sh $add_select_value from re_shop_product, re_topic $add_from_table $where_statement order by $order LIMIT ".$start.", ".$limit;
		$this->db->exec($query);
        $ra = array();
        $i = 0;
        while ( $this->db->fetch_assoc() ) {
            //$this->db->row['type_sh'] = $this->getOneItemName($this->db->row['type_sh']);
            if(SITE_LANG==0){
            	$this->db->row['product_link']=SITEBILL_MAIN_URL.'/product'.$this->db->row['product_id'].'.html';
            }else{
            	$this->db->row['product_link']=SITEBILL_MAIN_URL.'/product'.$this->db->row['link_id'].'.html';
            }
            
            $ra[$i] = $this->db->row;
            $i++;
        }
	}
	
	
	function get_price_array ( $params ) {
		if ( $params['topic_id'] != '' ) {
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
			$Structure_Manager = new Structure_Manager();
			$category_structure = $Structure_Manager->loadCategoryStructure();
			
			$childs = $Structure_Manager->get_all_childs($params['topic_id'], $category_structure);
			if ( count($childs) > 0 ) {
				array_push($childs, $params['topic_id']);
				$where_array[] = DB_PREFIX.'_price.category_id in ('.implode(' , ',$childs).') ';
			} else {
				$where_array[] = DB_PREFIX.'_price.category_id='.$params['topic_id'];
			}
		}
		$where_array[] = DB_PREFIX.'_price.price_id=re_price_image.price_id';
		$where_array[] = DB_PREFIX.'_price_image.image_id=re_image.image_id';
		
		if ( $where_array ) {
			$where_statement = " WHERE ".implode(' AND ', $where_array);
		}
		
		
		$query = "SELECT re_price.*, re_image.*
		FROM  `re_price`, re_image, re_price_image
		 $where_statement order by price_id";
		//echo $query.'<br>';
		$this->db->exec($query);
		$ra = array();
		$i = 0;
		while ( $this->db->fetch_assoc() ) {
			$this->db->row['add_date']=date('d-m-Y H:i',$this->db->row['product_add_date']);

			$ra[$i] = $this->db->row;
			$i++;
		}
		if ( $i > 0 ) {
			return $ra;
		}
		return false;
	}
    
    /**
     * Get sitebill adv ext
     * Запрос к базе, список машин
     * @param
     * @return
     * @author Kris
     */
    
    function get_sitebill_adv_ext( $params ) {
    	
    	if($params['save_topic_id']===TRUE){
    		$_save_topic_id=TRUE;
    	}else{
    		$_save_topic_id=FALSE;
    	}
    	unset($params['save_topic_id']);
    	
    	$where_array = false;
    	
    	$enable_publication_limit=$this->getConfigValue('apps.shop.user_limit_enable');
    	
    	if($params['keyword'] != ''){
    		$where_array[] = 're_shop_product.product_name LIKE \'%'.$params['keyword'].'%\'';
    	}
    	/*
    	if($params['admin'] != 1){
    		$where_array[] = 're_shop_product.active=1';
    	}*/
    	
    	if(isset($params['product_state'])){
    		$where_array[] = '(re_shop_product.product_state='.(int)$params['product_state'].')';
    	}
    	unset($params['product_state']);
    	
    	if($enable_publication_limit==1){
    		
    		$this->template->assert('enable_publication_limit', 1);
    		
    		if($params['pub_type'] == 'archived'){
    			$where_array[] = '((re_shop_product.product_add_date+re_user.publication_limit*24*3600)<'.time().')';
    		}elseif($params['pub_type'] == 'unmoderated'){
    			//$where_array[] = '((re_shop_product.product_add_date+re_user.publication_limit*24*3600)>'.time().' AND re_shop_product.active=0)';
    			$where_array[] = '(re_shop_product.active=0)';
    		}elseif($params['pub_type'] == 'active'){
    			//$where_array[] = '((re_shop_product.product_add_date+re_user.publication_limit*24*3600)>'.time().' AND re_shop_product.active=1)';
    			$where_array[] = '(re_shop_product.active=1)';
    		}elseif($params['pub_type'] == 'notarchived'){
    			//$where_array[] = '((re_shop_product.product_add_date+re_user.publication_limit*24*3600)>'.time().' AND re_shop_product.active=1)';
    			$where_array[] = '((re_shop_product.product_add_date+re_user.publication_limit*24*3600)>'.time().')';
    		}else{
    			if($params['admin'] != 1){
    				$where_array[] = '((re_shop_product.product_add_date+re_user.publication_limit*24*3600)>'.time().' AND re_shop_product.active=1)';
    			}else{
    				//$where_array[] = '((re_shop_product.product_add_date+re_user.publication_limit*24*3600)>'.time().')';
    			}
		    }
    	}else{
    		
    		$this->template->assert('enable_publication_limit', 0);
    		
    		if($params['pub_type'] == 'archived'){
    			//$where_array[] = '((re_shop_product.product_add_date+re_user.publication_limit*24*3600)<'.time().')';
    		}elseif($params['pub_type'] == 'unmoderated'){
    			$where_array[] = '(re_shop_product.active=0)';
    		}elseif($params['pub_type'] == 'active'){
    			$where_array[] = '(re_shop_product.active=1)';
    		}else{
    			if($params['admin'] != 1){
    				$where_array[] = '(re_shop_product.active=1)';
    			}else{
    				//$where_array[] = '(re_shop_product.active=1)';
    			}
		    }
    	}
    	/*
			if ( $params['admin'] != 1 ) {
	            $where_array[] = 're_shop_product.active=1';
	        } elseif ( $params['active'] == 1 ) {
	            $where_array[] = 're_shop_product.active=1';
	        } elseif ( $params['active'] == 'notactive' ) {
	            $where_array[] = 're_shop_product.active=0';
	        }
    	*/
    	if($params['city_id']!=''){
    		$where_array[] = '(re_shop_product.city_id='.(int)$params['city_id'].')';
    	}
    	
    	
       //$where_array[] = 're_topic.id=re_shop_product.category_id';
        
        if ( $params['topic_id'] != '' ) {
        	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
            $Structure_Manager = new Structure_Manager();
            $category_structure = $Structure_Manager->loadCategoryStructure();
            
            $childs = $Structure_Manager->get_all_childs($params['topic_id'], $category_structure);
            if ( count($childs) > 0 ) {
                array_push($childs, $params['topic_id']);
                $where_array[] = 're_shop_product.category_id in ('.implode(' , ',$childs).') ';
            } else {
                $where_array[] = 're_shop_product.category_id='.$params['topic_id'];
            }
        }
        
    	if ( $params['vendor_id'] != '' ) {
        	$where_array[] = 're_shop_product.vendor_id = '.$params['vendor_id'];
        }
        
    	if ( $params['tag_id'] != '' ) {
        	$where_array[] = 're_shop_product.product_id IN (SELECT DISTINCT shop_product_id FROM '.DB_PREFIX.'_shop_product_tag WHERE tag_id='.$params['tag_id'].')';
        }
        
        if(!defined('SITE_LANG')){
        	$where_array[] = 're_shop_product.language_id=0';
        }else{
        	$where_array[] = 're_shop_product.language_id='.SITE_LANG;
        }
        
        
        
        if ( isset($params['user_id']) and $params['user_id'] > 0  ) {
            $where_array[] = 're_shop_product.user_id = '.$params['user_id'];
        }
        
        /*
        if ( isset($params['price']) and $params['price'] != 0  ) {
            $where_array[] = 're_shop_product.price  <= '.$params['price'];
        }
        */
        
        //$where_array[] = 're_shop_product.active=1';
        
        /*
        if ( $params['admin'] != 1 ) {
            $where_array[] = 're_shop_product.active=1';
        } elseif ( $params['active'] == 1 ) {
            $where_array[] = 're_shop_product.active=1';
        } elseif ( $params['active'] == 'notactive' ) {
            $where_array[] = 're_shop_product.active=0';
        }
        */
     	if($params['newsort']=='new'){
            $where_array[] = 're_shop_product.product_state=1';
        }elseif($params['newsort']=='bu'){
            $where_array[] = 're_shop_product.product_state=2';
        }
            
        if($params['availablesort']=='available'){
            $where_array[] = 're_shop_product.product_availability=1';
        }elseif($params['availablesort']=='notavailable'){
            $where_array[] = 're_shop_product.product_availability=2';
        }
        
        if ( $where_array ) {
            $where_statement = " WHERE ".implode(' AND ', $where_array);
        }
        
        //echo $where_statement;
        
        if ( isset($params['order']) ) {

            if ( !isset($params['asc']) ) {
                $asc = 'asc';
            } 
            elseif ($params['asc'] == 'asc')  $asc = 'asc';
            elseif ($params['asc'] == 'desc') $asc = 'desc';
            //
            if( $params['order'] == 'product_name' ) {
				$order = 're_shop_product.product_name ';
			}elseif ( $params['order'] == 'product_price' ) {
				$order = 're_shop_product.product_price ';
			}elseif($params['order'] == 'date'){
				$order = 're_shop_product.product_add_date ';
			}elseif($params['order'] == 'type'){
				$order = 're_shop_product.category_id ';
			}elseif($params['order'] == 'id'){
				$order = 're_shop_product.product_id ';
			}else{
				$order = 're_shop_product.product_price ';
			}
           

            $order .= $asc;
        } else {
            $order = "re_shop_product.product_price asc";
        }
        
        if ( !isset($params['page']) or $params['page'] == 0 ) {
            $page = 1;
        } else {
            $page = $params['page'];
        }
		
        $limit = $this->getConfigValue('per_page');
		$start = ($page-1)*$limit;
		if ( $random ) {
		    $order = ' rand() ';
		}
		
		$query = "SELECT COUNT(re_shop_product.product_id) AS total 
			FROM  `re_shop_product`
			LEFT JOIN re_user ON re_shop_product.user_id = re_user.user_id 
			LEFT JOIN `re_topic` ON `re_topic`.id=re_shop_product.category_id $where_statement order by $order";
        //echo $query;
		//$query = "select count(product_id) as total from re_shop_product, re_topic $where_statement order by $order";
		$this->db->exec($query);
        $this->db->fetch_assoc();
        $total = $this->db->row['total'];
        
        $_topic_id=$params['topic_id'];
        if(!$_save_topic_id){
        	unset($params['topic_id']);
        }
        
        unset($params['vendor_id']);
        unset($params['tag_id']);
        
        if(IS_NUKUPI==1){
        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/page_navigator.php';
			$Pager=new Page_Navigator($total,$page,$limit,'',array('pre_pages'=>'3','post_pages'=>'3'));
			$this->template->assert('pager', $Pager->getPagerArray());	
        }else{
        	$this->template->assert('pager', $this->get_page_links_list ($page, $total, $limit, $params ));	
        }
		
        
        $params['topic_id']=$_topic_id;
        
        //unset($params['topic_id']);
	    
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();
        
        //print_r($params);
        $pageurl='';
        if(!$_save_topic_id){
	        if($category_structure['catalog'][$params['topic_id']]['url']!=''){
	        	$pageurl=$category_structure['catalog'][$params['topic_id']]['url'];
	        }else{
	        	$pageurl='topic'.$params['topic_id'].'.html';
	        }
        }
	    //unset($params['topic_id']);
	    
	    unset($params['viewtype']);
	    
	    foreach ( $params as $key => $value ) {
	        if ( $value != '') {
	        	if($key!='topic_id'){
	                //echo "key = $key, value = $value<br>";
		            $pairs[] = "$key=$value";
	        	}elseif($_save_topic_id){
	        		$pairs[] = "$key=$value";
	        	}
	        }
	    }
      //print_r($pairs);
	    if ( is_array($pairs) ) {
		    $curl = $pageurl.'?'.implode('&', $pairs);
	    }else{
	    	$curl = $pageurl.'?key=value';
	    }
	   
        $this->template->assert('current_url', $curl);
        
        unset($pairs);
        unset($params['order']);
        unset($params['asc']);
   	 	
        foreach ( $params as $key => $value ) {
	        if ( $value != '') {
	        	if($key!='topic_id'){
	                //echo "key = $key, value = $value<br>";
		            $pairs[] = "$key=$value";
	        	}elseif($_save_topic_id){
	        		$pairs[] = "$key=$value";
	        	}
	        }
	    }
      //print_r($pairs);
	    if ( is_array($pairs) ) {
		    $url = $pageurl.'?'.implode('&', $pairs);
	    }else{
	    	$url = $pageurl.'?key=value';
	    }
	   
        $this->template->assert('url', $url);		
		
        //require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/user.php');
        //$User_Object = new User_Object();
        //$User_Object->System_User_Object();
        //$publication_limit=$User_Object->getUserPublicationLimit($this->getSessionUserId());
        
        $query = "SELECT re_shop_product.*,  re_topic.name as type_sh
			FROM  `re_shop_product`
			LEFT JOIN re_user ON re_shop_product.user_id = re_user.user_id 
        	LEFT JOIN `re_topic` ON `re_topic`.id=re_shop_product.category_id $add_from_table $where_statement order by $order LIMIT ".$start.", ".$limit;
        //echo $query;
        
        
        
       // $query = "select re_shop_product.*, re_topic.name as type_sh $add_select_value from re_shop_product, re_topic $add_from_table $where_statement order by $order LIMIT ".$start.", ".$limit;
        //echo $query."<br>";
        $this->db->exec($query);
        $ra = array();
        $i = 0;
        while ( $this->db->fetch_assoc() ) {
            //$this->db->row['type_sh'] = $this->getOneItemName($this->db->row['type_sh']);
            $this->db->row['add_date']=date('d-m-Y H:i',$this->db->row['product_add_date']);
            if(($this->db->row['product_add_date']+$this->db->row['publication_limit']*24*3600)<time()){
            	$this->db->row['product_publication_type']='Архивное';
            	
            }else{
            	$this->db->row['product_publication_type']='Активное';
            }
            $this->db->row['product_publication_end']=date('d-m-Y H:i',$this->db->row['product_add_date']+$this->db->row['publication_limit']*24*3600);
            if($this->isInCart($this->db->row['product_id'])){
            	$this->db->row['is_in_cart']=1;
            }else{
            	$this->db->row['is_in_cart']=0;
            }
            
            
            if(SITE_LANG==0){
            	$this->db->row['product_link']=SITEBILL_MAIN_URL.'/product'.$this->db->row['product_id'].'.html';
            }else{
            	$this->db->row['product_link']=SITEBILL_MAIN_URL.'/product'.$this->db->row['link_id'].'.html';
            }
            
        	if($this->getConfigValue('editor1') == 'bbeditor'){
        		require_once SITEBILL_DOCUMENT_ROOT.'/apps/bbcode/admin/admin.php';
            	require_once SITEBILL_DOCUMENT_ROOT.'/apps/bbcode/site/site.php';
            	$bbcode=new bbcode_site();
            	$this->db->row['product_s_desc']=$bbcode->getHtmlFromBBCode($this->db->row['product_s_desc']);
            	$this->db->row['product_desc']=$bbcode->getHtmlFromBBCode($this->db->row['product_desc']);
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
	    
	   // echo '<pre>';
	   // print_r($ra);
        
        foreach ( $ra as $item_id => $item_array ) {
        	
            //$params['topic_id'] = $item_array['topic_id'];
            
            $ra[$item_id]['path'] = $this->get_category_breadcrumbs_string( $params, $category_structure );
            
            
            
            if(1==$this->getConfigValue('apps.seo.level_enable')){
            	 
            	if($category_structure['catalog'][$ra[$item_id]['category_id']]['url']!=''){
            		$ra[$item_id]['parent_category_url']=$category_structure['catalog'][$ra[$item_id]['category_id']]['url'].'/';
            	}else{
            		$ra[$item_id]['parent_category_url']='';
            	}
            }else{
            	$ra[$item_id]['parent_category_url']='';
            }
            if(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
            	$ra[$item_id]['href']=SITEBILL_MAIN_URL.'/'.$ra[$item_id]['parent_category_url'].'product'.$ra[$item_id]['product_id'].'.html';
            }else{
            	$ra[$item_id]['href']=SITEBILL_MAIN_URL.'/'.$ra[$item_id]['parent_category_url'].'product'.$ra[$item_id]['product_id'];
            }
            
            if($this->getConfigValue('apps.shop.namespace')!=''){
            	//$ra[$item_id]['parent_category_url']=$this->getConfigValue('apps.shop.namespace').'/'.$ra[$item_id]['parent_category_url'];
            	$ra[$item_id]['href']='/'.$this->getConfigValue('apps.shop.namespace').$ra[$item_id]['href'];
            }
            
            //$image_array = $data_model->get_image_array ( 'data', 'data', 'id', $item_array['id'] );
            //if ( count($image_array) > 0 ) {
            //    $ra[$item_id]['img'] = $image_array;
            //}
        } 
        //echo '<pre>';
        //print_r($ra);
        return $ra;
    }
    
     /**
     * Check is product in Cart now
     * @param int $product_id
     * @return bool
     */   
    function isInCart($product_id){
    	if(count($_SESSION['product_list'])>0){
    		if(in_array($product_id, array_keys($_SESSION['product_list']))){
	    		return TRUE;
	    	}else{
	    		return FALSE;
	    	}
    	}else{
    		return FALSE;
    	}
    	
    }
    
    /**
     * Get grid
     * @param array $adv res
     * @return string
     */
    function get_grid ($tid, $adv )
    {
    	if ($tid == 1 || $tid == "")				// объявления
    		return $this->get_sales_grid($adv);
    	if ($tid == 2)								// автоотзывы
    		return $this->get_autoreview_grid($adv);
    	return false;	
    }
        
}
?>
