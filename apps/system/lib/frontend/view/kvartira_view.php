<?php
/**
 * Kvartira view
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Kvartira_View extends SiteBill {
	private $city_id = 0;
	private $topic_id = 0;
	
	protected $realty=null;
	protected $realty_title='';
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
    }
    function getCityID () {
    	return $this->city_id;
    }
    
    function getTopicID () {
    	return $this->topic_id;
    }
    function geocodeField($item, &$form_data_shared){
    	if($item['value']['lat']=='' && $item['value']['lng']=='' && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/geodata/admin/admin.php') && 1==$this->getConfigValue('apps.geodata.enable')){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/geodata/admin/admin.php';
    		if(method_exists('geodata_admin', 'geocode_address')){
    			$address_array=array();
    			if(isset($form_data_shared['country_id']) && $form_data_shared['country_id']['value_string']!=''){
    				$address_array[]=$form_data_shared['country_id']['value_string'];
    			}
    			if(isset($form_data_shared['region_id']) && $form_data_shared['region_id']['value_string']!=''){
    				$address_array[]=$form_data_shared['region_id']['value_string'];
    			}
    			if(isset($form_data_shared['city_id']) && $form_data_shared['city_id']['value_string']!=''){
    				$address_array[]=$form_data_shared['city_id']['value_string'];
    				if(isset($form_data_shared['street_id']) && $form_data_shared['street_id']['value_string']!=''){
    					$address_array[]=$form_data_shared['street_id']['value_string'];
    					if(isset($form_data_shared['number']) && $form_data_shared['number']['value']!=''){
    						$address_array[]=$form_data_shared['number']['value'];
    					}
    				}
    			}
    			$data=geodata_admin::geocode_address(implode(', ', $address_array));
    			if($data && $data['lat']!='' && $data['lng']!=''){
    				$form_data_shared[$item['name']]['value']['lat']=$data['lat'];
    				$form_data_shared[$item['name']]['value']['lng']=$data['lng'];
    				$DBC=DBC::getInstance();
    				$query='UPDATE '.DB_PREFIX.'_data SET `'.$item['name'].'_lat`=?, `'.$item['name'].'_lng`=? WHERE id=?';
    				$stmt=$DBC->query($query, array($data['lat'], $data['lng'], $form_data_shared['id']['value']));
    			}
    	
    		}
    		 
    		 
    	}
    }
    
    
    
    /**
     * Main
     * @param int $realty_id realty id
     * @return mixed
     */
    function main ( $realty_id ) {
    	
    	$result=false;
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
        
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();
        $user_object_manager = new User_Object_Manager();

        $data_model = new Data_Model();
        
        
        //load Data model with rules
        $form_data = $data_model->get_kvartira_model(false, false);
        
        //load Data model full without rules
        $form_data_shared = $data_model->get_kvartira_model(false, true);
        
        //init lang variables
        $form_data_language = $data_model->get_kvartira_model(false, true);
           
        //load User model with rules
        $form_user = $user_object_manager->get_user_model(true);
        
        //init Data model with rules
        $form_data = $data_model->init_model_data_from_db ( 'data', 'id', $realty_id, $form_data['data'], true );
        
        //init lang variables
        $form_data_language = $data_model->init_model_data_from_db ( 'data', 'id', $realty_id, $form_data_language['data'], true );
        
        $topic_id=0;
        if(isset($form_data['topic_id'])){
        	$topic_id=(int)$form_data['topic_id']['value'];
        	$this->topic_id = (int)$form_data['topic_id']['value'];
        }
        
        if ( (int)$form_data['city_id']['value'] > 0 ) {
        	$this->city_id = $form_data['city_id']['value'];
        }
        
        
        /*
        $ids=range(1,1000);
       
        $start=microtime(true);
        $form_data_sh = $data_model->init_model_data_from_db_multi ( 'data', 'id', $ids, $form_data_shared['data'], true );
      
        $end=microtime(true);
        echo $end-$start;
        
        $this->template->assign('db_multi', $form_data_sh);
        
        echo '<br>';
        
        
        $start=microtime(true);
        foreach($ids as $id){
        	$mod=$form_data_shared;
        	$mod = $data_model->init_model_data_from_db ( 'data', 'id', $ids, $mod['data'], true );
        }
       
        $end=microtime(true);
        echo $end-$start;
         
        */
       
       
       
        
        
        //init Data model full without rules
        $form_data_shared = $data_model->init_model_data_from_db ( 'data', 'id', $realty_id, $form_data_shared['data'], true );
        
       $this->realty=$form_data_shared;
       
        if(!$form_data){
        	return $result;
        }
        
        $show_not_active=false;
        if(1==intval($this->getConfigValue('apps.realty.allow_notactive_direct'))){
        	$show_not_active=true;
        }
        
        
      	if(isset($form_data_shared['active']) && $form_data_shared['active']['value']==0 && !$show_not_active){
        	header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        	$this->template->assign('main_file_tpl', 'error_message.tpl');
        	return false;
        }
        
        if(isset($form_data_shared['active']) && $form_data_shared['active']['value']==0 && $show_not_active){
        	$this->template->assign('notactive_item_showed', 1);
        }
       
        if(1==(int)$this->getConfigValue('apps.realty.use_predeleting') && isset($form_data_shared['archived']) && $form_data_shared['archived']['value'] == 1 && 1==$this->getConfigValue('apps.realty.archived_notactive')){
        	header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        	$this->template->assign('main_file_tpl', 'error_message.tpl');
        	return false;
        }
        $result=true;
        
        $form_data = $data_model->init_language_values($form_data, $form_data_language);
        $form_data_shared = $data_model->init_language_values($form_data_shared, $form_data_language);
        
        $DBC=DBC::getInstance();
        
       	
       	foreach($form_data_shared as $k=>$item){
       		if($item['type']=='geodata' && 1==$this->getConfigValue('apps.geodata.enable') && 1==$this->getConfigValue('apps.geodata.allow_view_coding')){
       			$this->geocodeField($item, $form_data_shared);
       			$form_data[$k]=$form_data_shared[$k];
       			/*if($item['value']['lat']=='' && $item['value']['lng']=='' && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/geodata/admin/admin.php') && 1==$this->getConfigValue('apps.geodata.enable')){
       				require_once SITEBILL_DOCUMENT_ROOT.'/apps/geodata/admin/admin.php';
       				if(method_exists('geodata_admin', 'geocode_address')){
       					$address_array=array();
       					if(isset($form_data_shared['country_id']) && $form_data_shared['country_id']['value_string']!=''){
       						$address_array[]=$form_data_shared['country_id']['value_string'];
       					}
       					if(isset($form_data_shared['region_id']) && $form_data_shared['region_id']['value_string']!=''){
       						$address_array[]=$form_data_shared['region_id']['value_string'];
       					}
       					if(isset($form_data_shared['city_id']) && $form_data_shared['city_id']['value_string']!=''){
       						$address_array[]=$form_data_shared['city_id']['value_string'];
       						if(isset($form_data_shared['street_id']) && $form_data_shared['street_id']['value_string']!=''){
       							$address_array[]=$form_data_shared['street_id']['value_string'];
       							if(isset($form_data_shared['number']) && $form_data_shared['number']['value']!=''){
       								$address_array[]=$form_data_shared['number']['value'];
       							}
       						}
       					}
       					$data=geodata_admin::geocode_address(implode(', ', $address_array));
       					if($data && $data['lat']!='' && $data['lng']!=''){
	       					$form_data_shared[$k]['value']['lat']=$data['lat'];
	       					$form_data_shared[$k]['value']['lng']=$data['lng'];
	       					$form_data[$k]['value']['lat']=$data['lat'];
	       					$form_data[$k]['value']['lng']=$data['lng'];
	       					$DBC=DBC::getInstance();
	       					$query='UPDATE '.DB_PREFIX.'_data SET `'.$item['name'].'_lat`=?, `'.$item['name'].'_lng`=? WHERE id=?';
	       					$stmt=$DBC->query($query, array($data['lat'], $data['lng'], $form_data_shared['id']['value']));
       					}
       					
       				}
       				
       				
       			}*/
       			break;
       		}
       	}
        
       	
       	
        
        //clearing by in topic activity
        $topic_id=0;
        if(isset($form_data['topic_id'])){
        	$topic_id=(int)$form_data['topic_id']['value'];
        }
        
        if($topic_id!=0){
        	foreach ( $form_data as $key => $item_array ) {
	        	
	        	if($topic_id!=0 && isset($item_array['active_in_topic']) && $item_array['active_in_topic']!=0){
	        		$active_array_ids = explode(',',$item_array['active_in_topic']);
	        		$child_cats = array();
	        		foreach ($active_array_ids as $item_id => $check_active_id) {
	        			$child_cats_compare = $Structure_Manager->get_all_childs($check_active_id, $category_structure);
	        			if ( is_array($child_cats_compare) ) {
	        				$child_cats = array_merge($child_cats, $child_cats_compare);
	        			}
	        			$child_cats[]=$check_active_id;
	        
	        		}
	        		
	        			        
	        		if(!in_array($topic_id, $child_cats)){
	        			unset($form_data[$key]);
	        			continue;
	        		}
	        	}
	        }
        }
        
        $CatalogChains = $Structure_Manager->createCatalogChains();
        if(isset($CatalogChains['ar'][$topic_id])){
        	$this->template->assign('data_supertopic', $CatalogChains['ar'][$topic_id][0]);
        	$this->template->assign('data_topic_chain', $CatalogChains['ar'][$topic_id]);
        }
        
        //load user data. always available!
        if($this->getConfigValue('apps.realtypro.show_contact.enable')){
        	$form_user = $data_model->init_model_data_from_db ( 'user', 'user_id', $form_data_shared['user_id']['value'], $form_user['user'], true);
        }else{
        	$form_user = $data_model->init_model_data_from_db ( 'user', 'user_id', $form_data_shared['user_id']['value'], $form_user['user'], true);
        }
        /*
        if($this->getConfigValue('apps.realtypro.show_contact.enable')){
        	$form_user = $data_model->init_model_data_from_db ( 'user', 'user_id', $form_data['user_id']['value'], $form_user['user'], true);
        }else{
        	$form_user = $data_model->init_model_data_from_db ( 'user', 'user_id', $form_data['user_id']['value'], $form_user['user'], true);
        }
       */
        
        if(isset($form_data['date_added']) && $form_data['date_added']['value']!=''){
        	$form_data['date_added']['value_string']=date('d-m-Y', strtotime($form_data['date_added']['value']));
        }
		
		
       	
        if ( $this->getConfigValue('apps.company.timelimit') ) {
        	$current_time = time();
        	$query = "select re_data.* from re_data, re_user u, re_company c where re_data.id=$realty_id and re_data.user_id=u.user_id and u.company_id=c.company_id and c.start_date <= $current_time and c.end_date >= $current_time";
        	$stmt=$DBC->query($query);
        	if($stmt){
        		$ar=$DBC->fetch($stmt);
        		if ( $ar['id'] == '' ) {
        			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        			$this->template->assign('error_message', 'Страница не найдена. 404 not found');
        			$this->template->assign('main_file_tpl', 'error_message.tpl');
        			return false;
        		}
        	}
       }
        if ( $this->getConfigValue('apps.company.enable') ) {
        	require_once (SITEBILL_DOCUMENT_ROOT.'/apps/company/admin/admin.php');
        	$company_admin = new company_admin();
       		$company_profile = $company_admin->load_by_id($form_user['company_id']['value']);
       		if ( $company_profile ) {
        		$this->template->assign('company_profile', $company_profile);
			}
			$this->template->assign('user_company_data', $company_admin->getUserCompanyData($form_user['user_id']['value']));
			
        }
        
        if(isset($form_data['topic_id'])){
        	$form_data['topic_id']['value_string']=$category_structure['catalog'][$form_data['topic_id']['value']]['name'];
        }
        
        
        
    	
    	
		$this->template->assert('hvd_tabbed', $this->getAutoOutputData($form_data));
    	
        
        
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/currency/admin/admin.php';
		$CA=new currency_admin();
      
       
		//append similar advs
		
		if(method_exists($this, 'getCustomSimilarData')){
			$this->template->assign('similar_data', $this->getCustomSimilarData($category_structure, $form_data_shared));
		}else{
			$simparams=array(
					'id'=>(int)$form_data['id']['value'],
					'topic_id'=>(int)$form_data['topic_id']['value'],
					'city_id'=>(int)$form_data['city_id']['value'],
					'district_id'=>(int)$form_data['district_id']['value'],
					'street_id'=>(int)$form_data['street_id']['value'],
			);
			$this->template->assign('similar_data', $this->getSimilar($category_structure, $simparams));
		}
		
		
	    
	   
	    if($form_user['login']['value']=='_unregistered'){
	    	$form_user['fio']['value']=$form_data['fio']['value'];
	    	$form_user['phone']['value']=$form_data['phone']['value'];
	    }
	    
	    if($this->getConfigValue('use_google_map')){
			$this->template->assign('map_type', 'google');
		}

		$this->template->assign('admin_user_id', $this->getAdminUserId());
	    $this->template->assign('current_user_id', $this->getSessionUserId());
	    $this->template->assign('photo', $form_data['image']['image_array']);
	    $this->template->assign('user_data', $form_user);
        //$this->template->assign('yandex_map_key', $this->getConfigValue('yandex_map_key'));
        //$this->template->assign('pmap', $this->getConfigValue('pmap'));
	    
	    $this->makeUserOperatios($form_data_shared);
	    
	   
        
        $hasTlocation=false;
        $tlocationElement='';
        
        foreach($form_data as $key=>$val){
        	if($val['type']=='tlocation'){
        		//print_r($val);
        		$hasTlocation=true;
        		$tlocationElement=$key;
        		$form_data['country_id']['value_string']=$val['value_string']['country_id'];
        		$form_data['region_id']['value_string']=$val['value_string']['region_id'];
        		$form_data['city_id']['value_string']=$val['value_string']['city_id'];
        		$form_data['district_id']['value_string']=$val['value_string']['district_id'];
        		$form_data['street_id']['value_string']=$val['value_string']['street_id'];
        		
        		$form_data_shared['country_id']['value_string']=$val['value_string']['country_id'];
        		$form_data_shared['region_id']['value_string']=$val['value_string']['region_id'];
        		$form_data_shared['city_id']['value_string']=$val['value_string']['city_id'];
        		$form_data_shared['district_id']['value_string']=$val['value_string']['district_id'];
        		$form_data_shared['street_id']['value_string']=$val['value_string']['street_id'];
        	}
        }
        $this->template->assign('data', $form_data);
        $this->template->assign('data_shared', $form_data_shared);
       
        $meta_data=$this->getMetaData($form_data_shared, $hasTlocation, $tlocationElement);
        
        $params['topic_id'] = (int)$form_data['topic_id']['value'];
        
       
	if ( 1 == $this->getConfigValue('apps.seo.country_info_in_realty_view') ) {
	    $this->template->assign('country_info', $this->get_country_info($form_data['country_id']['value']));
	} 
	if ( 1 == $this->getConfigValue('apps.seo.region_info_in_realty_view') ) {
	    $this->template->assign('region_info', $this->get_region_info($form_data['region_id']['value']));
	} 
	if ( 1 == $this->getConfigValue('apps.seo.city_info_in_realty_view') ) {
	    $this->template->assign('city_info', $this->get_city_info($form_data['city_id']['value']));
	} 
        $breadcrumbs=$this->getBreadcrumbs($params);
        
        $this->template->assign('realty_breadcrumbs', explode(' / ', $breadcrumbs));
        $this->template->assign('breadcrumbs', $breadcrumbs );
        
        $this->template->assign('meta_title', $meta_data['meta_title']);
        $this->template->assign('title', $meta_data['title']);
        $this->template->assign('meta_description', $meta_data['meta_description']);
        $this->template->assign('meta_keywords',$meta_data['meta_keywords']);
         
        
       
       
        if(1==$this->getConfigValue('apps.comment.enable')){
        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/comment/admin/admin.php';
        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/comment/site/site.php';
        	$CoM=new comment_site();
        	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
			$Login = new Login();
		   	$user_id=(int)$Login->getSessionUserId();
        	$commentsPanel=$CoM->generateCommentPanel($user_id, 'data', $realty_id);
        }
       
        $this->template->assert('geoobjects_collection_clustered', json_encode($this->getRealtyOnMap($form_data)));
        
        if ( $this->getConfigValue('theme') != 'estate' and !file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_view.tpl') ) {
            $this->template->assign('main_file_tpl', '../estate/realty_view.tpl');
        } else {
            $this->template->assign('main_file_tpl', 'realty_view.tpl');
        }
        
        if(1==$this->getConfigValue('apps.billing.enable') && 1==$this->getConfigValue('apps.billing.noauth_status_set')){
        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/billing/admin/admin.php';
        	$B=new billing_admin();
        	$params=array();
        	if(isset($form_data_shared['vip_status_end']) && intval($form_data_shared['vip_status_end']['value'])!=0 && $form_data_shared['vip_status_end']['value']>time()){
        		$params['vip_status_end']=$form_data_shared['vip_status_end']['value'];
        	}else{
        		$params['vip_status_end']='';
        	}
        	if(isset($form_data_shared['premium_status_end']) && intval($form_data_shared['premium_status_end']['value'])!=0 && $form_data_shared['premium_status_end']['value']>time()){
        		$params['premium_status_end']=$form_data_shared['premium_status_end']['value'];
        	}else{
        		$params['premium_status_end']='';
        	}
        	if(isset($form_data_shared['bold_status_end']) && intval($form_data_shared['bold_status_end']['value'])!=0 && $form_data_shared['bold_status_end']['value']>time()){
        		$params['bold_status_end']=$form_data_shared['bold_status_end']['value'];
        	}else{
        		$params['bold_status_end']='';
        	}
        	$this->template->assign('fast_billing', $B->getFastBilling($realty_id, $params));
        }
        
        $this->makePDF($realty_id, $meta_data['title']);
        
        return $result;
    }
    
    private function get_city_info($city_id) {
	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/city/city_manager.php');
	$city_manager = new City_Manager();
	return $city_manager->load_by_id($city_id);
    }

    private function get_region_info($region_id) {
	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/region/region_manager.php');
	$region_manager = new Region_Manager();
	return $region_manager->load_by_id($region_id);
    }

    private function get_country_info($country_id) {
	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/country/country_manager.php');
	$country_manager = new Country_Manager();
	return $country_manager->load_by_id($country_id);
    }
    
    
    
    protected function makeUserOperatios($form_data_shared){
    	/*$user_identity=md5($_SERVER['HTTP_USER_AGENT'].'_'.$_SERVER['REMOTE_ADDR']);
    	$realty_id=(int)$form_data_shared['id']['value'];
    	
    	$DBC=DBC::getInstance();
    	$query='SELECT COUNT(*) AS _cnt FROM '.DB_PREFIX.'_likevoter WHERE user_identity=? AND realty_id=?';
    	$DBC=DBC::getInstance();
    	$stmt=$DBC->query($query, array($user_identity, $realty_id));
    	
    	$likevoter=array();
    	$likevoter['yes']=0;
    	$likevoter['no']=0;
    	$likevoter['allow']=0;
    	
    	
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		if($ar['_cnt']==0){
    			$likevoter['allow']=1;
    		}else{
    			$likevoter['allow']=0;
    		}
    	}
    	
    	$query='SELECT COUNT(*) AS _cnt, resultcode FROM '.DB_PREFIX.'_likevoter WHERE realty_id=? GROUP BY resultcode';
    	$stmt=$DBC->query($query, array($realty_id));
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			//print_r($ar);
    			if($ar['resultcode']==1){
    				$likevoter['yes']=(int)$ar['_cnt'];
    			}elseif($ar['resultcode']==0){
    				$likevoter['no']=(int)$ar['_cnt'];
    			}
    		}
    	}
    	$this->template->assign('likevoter', $likevoter);*/
    }
    
    protected function makePDF($realty_id, $title){
    	//print_r(get_included_files());
    	$hasAccessor=false;
    	if(isset($_SESSION['Accessor'])){
    		$this->template->assign('accessor_mode', 1);
    		$hasAccessor=true;
    	}
    	
    	if(1==(int)$this->getConfigValue('apps.pdfreport.enabled') && $_GET['format']=='pdf'){
    		/*try{
    			$x=new Accessor();
    		}catch(Exception $e){
    			echo 'no';
    		}*/
    		
    		/*if(class_exists('Accessor')){
    			if($_SESSION['Accessor']['viewOptions']['mode']!='opened'){
    				$this->template->assign('accessor_mode', 1);
    			}
    		}else{
    			//echo 'no';
    		}*/
    		
    		//var_dump();
    		/*$test_accessor_val='template.'.$this->getConfigValue('theme').'.free_mode';
    		$test_accessor_module=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/accessor.php';
    		$test_accessor_module=str_replace('/', DIRECTORY_SEPARATOR, $test_accessor_module);
    		if(in_array($test_accessor_module, get_included_files()) && 1!=$this->getConfigValue($test_accessor_val)){
    			//$this->template->assign('accessor_on', 1);
    			
    			if($_SESSION['Accessor']['viewOptions']['mode']!='opened'){
    				$this->template->assign('user_data', array());
    			}
    		
    		}*/
    		
    	
    		
    		$_tpl_code='';
    		if(isset($_GET['tpl']) && $_GET['tpl']!=''){
    			$_tpl=$_GET['tpl'];
    			if(preg_match('/[^0-9a-zA-Z_-]/', $_tpl)){
    				$_tpl='';
    				
    			}else{
    				$_tpl_code='_'.$_tpl;
    			}
    			$_tpl=$_tpl.'.tpl';
    		}else{
    			$_tpl='';
    		}
    		
    		
    		
    		$pdfpageurl=Sitebill::getClearRequestURI();
    		$pdfpageurl=$this->getServerFullUrl().'/'.$pdfpageurl;
    		$pdfpageurl.=' | '.date('d-m-Y H:i');
    		$this->template->assign('pdfpageurl', $pdfpageurl);
    		
    		
    		$this->template->assign('_core_folder', SITEBILL_DOCUMENT_ROOT);
    		$pdf_file_name='realty'.$realty_id.$_tpl_code.'.pdf';
    		$pdf_file_storage=SITEBILL_DOCUMENT_ROOT.'/cache/';
    		if(0==(int)$this->getConfigValue('apps.pdfreport.use_cache') || $hasAccessor){
    			require_once(SITEBILL_DOCUMENT_ROOT."/apps/pdfreport/lib/dompdf/dompdf_config.inc.php");
    			//$tmpfile = tempnam(SITEBILL_DOCUMENT_ROOT.'/cache/', 'dompdf_');
    			global $smarty;
    			if($_tpl!='' && file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/pdfreport/'.$_tpl)){
    				$html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/pdfreport/'.$_tpl);
    				
    			}elseif($_tpl!='' && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/pdfreport/admin/template/'.$_tpl)){
    				$html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/pdfreport/admin/template/'.$_tpl);
    			}elseif(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/pdfreport/realty_view.tpl')){
    				$html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/pdfreport/realty_view.tpl');
    			}else{
    				$html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/pdfreport/admin/template/realty_view.tpl');
    			}
    			$dompdf = new DOMPDF();
    			$dompdf->load_html($html);
    			$dompdf->render();
    	
    			$output = $dompdf->output();
    		}else{
    			if(file_exists($pdf_file_storage.$pdf_file_name)){
    				$output=file_get_contents(SITEBILL_DOCUMENT_ROOT.'/cache/'.$pdf_file_name);
    			}else{
    				require_once(SITEBILL_DOCUMENT_ROOT."/apps/pdfreport/lib/dompdf/dompdf_config.inc.php");
    				$tmpfile = tempnam(SITEBILL_DOCUMENT_ROOT.'/cache/', 'dompdf_');
    				global $smarty;
    				if($_tpl!='' && file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/pdfreport/'.$_tpl)){
    					$html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/pdfreport/'.$_tpl);
    				}elseif(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/pdfreport/realty_view.tpl')){
    					$html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/pdfreport/realty_view.tpl');
    				}else{
    					$html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/pdfreport/admin/template/realty_view.tpl');
    				}
    				$dompdf = new DOMPDF();
    				$dompdf->load_html($html);
    				$dompdf->render();
    	
    				$output = $dompdf->output();
    				file_put_contents(SITEBILL_DOCUMENT_ROOT.'/cache/'.$pdf_file_name, $output);
    			}
    		}
    		header("Content-type: application/pdf");
    		//echo $output;
    		header('Content-Disposition: attachment; filename="'.$this->transliteMe($title).'.pdf"');
    		
    		echo $output;
    		exit();
    	}
    }
    
    public function getPublicAutoOutputData($form_data){
    	return $this->getAutoOutputData($form_data);
    }
    
    protected function getAutoOutputData($form_data){
    	$hvd_tabbed=array();
    	foreach($form_data as $hvd){
    		if($hvd['tab']==''){
    			$hvd_tabbed[$this->getConfigValue('default_tab_name')][]=$hvd;
    		}else{
    			$hvd_tabbed[$hvd['tab']][]=$hvd;
    		}
    	}
    	return $hvd_tabbed;
    }
    
    protected function getRealtyOnMap($form_data){
    	
    	$gdata=array();
    	$geoobjects_collection=array();
    	$gd=array();
    	
    	foreach ($form_data as $key=>$value){
    		if($key=='city_id'){
    			$gd['city']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $value['value_string']);
    		}elseif($key=='street_id'){
    			$gd['street']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $value['value_string']);
    		}elseif($key=='price'){
    			$gd['price']=$value['value'];
    		}elseif($key=='topic_id'){
    			$gd['type_sh']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $value['value_string']);
    		}else{
    			$gd[$key]=$value['value'];
    		}
    	}
    	
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_on_map.tpl')){
    		global $smarty;
    		$smarty->assign('realty', $gd);
    		$html=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_on_map.tpl');
    		$html = str_replace("\r\n", ' ', $html);
    		$html = str_replace("\n", ' ', $html);
    		$html = str_replace("\t", ' ', $html);
    		$html = addslashes($html);
    	}else{
    		$html = '';
    	}
    	//echo $html;
    	$gd['html']=SiteBill::iconv(SITE_ENCODING, 'utf-8', $html);
    	$gd['href']='#';
    	
    	if(isset($form_data['geo']) && $form_data['geo']['value']['lat']!='' && $form_data['geo']['value']['lng']!=''){
    		$gd['geo_lat']=$form_data['geo']['value']['lat'];
    		$gd['geo_lng']=$form_data['geo']['value']['lng'];
    		 
    		$gc=$gd['geo_lat'].'_'.$gd['geo_lng'];
    		 
    		$geoobjects_collection[$gc]['html'].=$gd['html'];
    		$geoobjects_collection[$gc]['count']++;
    		$geoobjects_collection[$gc]['lat']=$gd['geo_lat'];
    		$geoobjects_collection[$gc]['lng']=$gd['geo_lng'];
    	}
    	return $geoobjects_collection;
    }
	
	protected function formatPhoneNumber($num){
		$parts=array();
		$matches=array();
		$num=preg_replace('/[^\d]/','',$num);
		//echo 
		if(substr($num,0,1)=='8' && strlen($num)==11){
			preg_match_all('/(\d*)(\d{3})(\d{3})(\d{2})(\d{2})$/',$num,$matches);
		}elseif(strlen($num)==11 || strlen($num)==10){
			preg_match_all('/(\d*)(\d{3})(\d{3})(\d{2})(\d{2})$/',$num,$matches);
		}else{
			preg_match_all('/(\d*)(\d{2})(\d{2})$/',$num,$matches);
		}
		for($i=1; $i<6; $i++){
			if(isset($matches[$i]) && $matches[$i]!==''){
				$parts[]=$matches[$i][0];
			}
		}
		if(count($parts)>0){
			return implode('-',$parts);
		}else{
			return $num;
		}
	}
    
    protected function getSimilar($categories, $params=array()){
    	$similar_items_count=(0==(int)$this->getConfigValue('similar_items_count') ? 5 : (int)$this->getConfigValue('similar_items_count'));
    	
    	//$str='{}'
    	/*$similar_str=trim($this->getConfigValue('apps.realty.similar_preg'));
    	 
    	if($similar_str!=''){
    		//$title_str='';
    		 
    	
    		preg_match_all('/{([^}]+)}/', $similar_str, $matches);
    		//print_r($matches);
    		
    	}*/
    	
    	
    	
    	$simvariants=array();
    	$conds=array();
    	
    	/*$similar_str='{price:+3000,city_id,topic_id}{price:+3000,city_id}';
    	$similar_str='{price:+3000,city_id,topic_id}{topic_id}{price:+3000,city_id,!parenttopic}{!supertopic}{!rand}';
    	$similar_str='{price:+3000,city_id,topic_id}{!rand}';*/
    	
    	$similar_str=trim($this->getConfigValue('apps.realty.similar_preg'));
    	
    	if(preg_match_all('/{([^}]+)}/', $similar_str, $matches)){
    		$simvariants=$matches[1];
    	}
    	
    	if(!empty($simvariants)){
    		
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    		$Structure_Manager = new Structure_Manager();
    		$category_structure = $Structure_Manager->loadCategoryStructure();
    		
    		$current_topic=intval($this->realty['topic_id']['value']);
    		
    		foreach($simvariants as $step=>$stepstr){
    			$parts=explode(',', $stepstr);
    			if(count($parts)>0){
    				foreach($parts as $part){
    					if($part=='!rand'){
    						$conds[$step][]='rand';
    					}elseif($part=='!supertopic'){
    						if($current_topic!=0){
    							$ch=$Structure_Manager->createCatalogChains();
    							if(isset($ch['ar'][$current_topic]) && count($ch['ar'][$current_topic])==1){
    								$conds[$step][]='`topic_id`=?';
    								$conds_val[$step][]=$current_topic;
    							}elseif(isset($ch['ar'][$current_topic])){
    								$pt=$ch['ar'][$current_topic][0];
    								$childs=$Structure_Manager->get_all_childs($pt, $category_structure);
    								$childs[]=$pt;
    								$conds[$step][]='`topic_id` IN ('.implode(',', $childs).')';
    								//$conds_val[$step][]=$ch['ar'][$rt][0];
    							}
    						}
    					}elseif($part=='!parenttopic'){
    						if($current_topic!=0){
    							$ch=$Structure_Manager->createCatalogChains();
    							if(isset($ch['ar'][$current_topic]) && count($ch['ar'][$current_topic])>1){
    								$current_parent_topic=$ch['ar'][$current_topic][count($ch['ar'][$current_topic])-2];
    								$childs=$Structure_Manager->get_all_childs($current_parent_topic, $category_structure);
    								$childs[]=$current_parent_topic;
    								$conds[$step][]='`topic_id` IN ('.implode(',', $childs).')';
    							}else{
    								$conds[$step][]='`topic_id`=?';
    								$conds_val[$step][]=$current_topic;
    							}
    						}
    					}elseif($part=='!innertopic'){
    						if($current_topic!=0){
    							$childs=$Structure_Manager->get_all_childs($current_topic, $category_structure);
    							$childs[]=$current_topic;
    							
    							$conds[$step][]='`topic_id` IN ('.implode(',', $childs).')';
    						}
    					}elseif(false!==strpos($part, ':')){
    						list($key, $val)=explode(':', $part);
    						if(preg_match('/([d\+-])(\d+)(%?)/', $val, $m)){
    							if($m[1]=='d' && $m[3]=='%'){
    								$min_val=intval($this->realty[$key]['value']-($m[2]*$this->realty[$key]['value'])/100);
    								$max_val=intval($this->realty[$key]['value']+($m[2]*$this->realty[$key]['value'])/100);
    							}elseif($m[1]=='d' && $m[3]!='%'){
    								$min_val=intval($this->realty[$key]['value']-$m[2]);
    								$max_val=intval($this->realty[$key]['value']+$m[2]);
    							}elseif($m[1]=='+' && $m[3]=='%'){
    								$min_val=$this->realty[$key]['value'];
    								$max_val=intval($this->realty[$key]['value']+($m[2]*$this->realty[$key]['value'])/100);
    							}elseif($m[1]=='-' && $m[3]=='%'){
    								$min_val=intval($this->realty[$key]['value']-($m[2]*$this->realty[$key]['value'])/100);
    								$max_val=$this->realty[$key]['value'];
    							}elseif($m[1]=='+' && $m[3]!='%'){
    								$min_val=$this->realty[$key]['value'];
    								$max_val=intval($this->realty[$key]['value']+$m[2]);
    							}elseif($m[1]=='-' && $m[3]!='%'){
    								$min_val=intval($this->realty[$key]['value']-$m[2]);
    								$max_val=$this->realty[$key]['value'];
    							}
    							
    							$conds[$step][]='`'.$key.'`>=?';
    							$conds_val[$step][]=$min_val;
    							$conds[$step][]='`'.$key.'`<=?';
    							$conds_val[$step][]=$max_val;
    						}else{
    							$conds[$step][]='`'.$part.'`=?';
    							$conds_val[$step][]=$this->realty[$part]['value'];
    						}
    						
    					}else{
    						$conds[$step][]='`'.$part.'`=?';
    						$conds_val[$step][]=$this->realty[$part]['value'];
    					}
    				}
    			}
    		}
    	}
    	
    	$DBC=DBC::getInstance();
    	$ret=array();
    	
    	if(!empty($conds)){
    		$ids[]=$this->realty['id']['value'];
    		$last_to_select=$similar_items_count;
    		foreach($conds as $k=>$v){
    			if($last_to_select>0){
    				if($v[0]=='rand'){
    					$v=array();
    					$v[]='active=1';
    					if(1==(int)$this->getConfigValue('apps.realty.use_predeleting')){
    						$v[]='`archived`<>1';
    					}
    					$v[]='id NOT IN ('.implode(',',$ids).')';
    					$q='SELECT id FROM '.DB_PREFIX.'_data'.(!empty($v) ? ' WHERE '.implode(' AND ', $v) : '').' ORDER BY RAND() LIMIT '.$last_to_select;
    				}else{
    					$v[]='active=1';
    					if(1==(int)$this->getConfigValue('apps.realty.use_predeleting')){
    						$v[]='`archived`<>1';
    					}
    					$v[]='id NOT IN ('.implode(',',$ids).')';
    					$q='SELECT id FROM '.DB_PREFIX.'_data'.(!empty($v) ? ' WHERE '.implode(' AND ', $v) : '').' LIMIT '.$last_to_select;
    				}
    				$stmt=$DBC->query($q, $conds_val[$k]);
    				
    				if($stmt){
    					while($ar=$DBC->fetch($stmt)){
    						$ret[]=$ar['id'];
    						$ids[]=$ar['id'];
    					}
    					$last_to_select=$similar_items_count-count($ret);
    				}
    			}else{
    				break;
    			}
    		}
    	}else{
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    		$Structure_Manager = new Structure_Manager();
    		$category_structure = $Structure_Manager->loadCategoryStructure();
    		 
    		
    		
    		$where=array();
    		if(!empty($params)){
    			$ids[]=$params['id'];
    			$where['active']='active=1';
    			if(1==(int)$this->getConfigValue('apps.realty.use_predeleting')){
    				$where['archived']='`archived`<>1';
    			}
    			if($params['street_id']!=0){
    				$where['street_id']='street_id='.$params['street_id'];
    			}
    			if($params['topic_id']!=0){
    				$where['topic_id']='topic_id='.$params['topic_id'];
    			}
    			if($params['city_id']!=0){
    				$where['city_id']='city_id='.$params['city_id'];
    			}
    			if($params['district_id']!=0){
    				$where['district_id']='district_id='.$params['district_id'];
    			}
    			if($params['id']!=0){
    				$where['id']='id NOT IN ('.implode(',',$ids).')';
    			}
    			$q='SELECT id FROM '.DB_PREFIX.'_data'.(!empty($where) ? ' WHERE '.implode(' AND ',$where) : '').' LIMIT '.$similar_items_count;
    		
    			$stmt=$DBC->query($q);
    			if($stmt){
    				while($ar=$DBC->fetch($stmt)){
    					$ret[]=$ar['id'];
    					$ids[]=$ar['id'];
    				}
    			}
    		
    		
    			if(count($ret)<$similar_items_count){
    				$last=$similar_items_count-count($ret);
    				unset($where['district_id']);
    				unset($where['street_id']);
    				$where['id']='id NOT IN ('.implode(',',$ids).')';
    				$q='SELECT id FROM '.DB_PREFIX.'_data'.(!empty($where) ? ' WHERE '.implode(' AND ',$where) : '').' LIMIT '.$last;
    		
    				$stmt=$DBC->query($q);
    				if($stmt){
    					while($ar=$DBC->fetch($stmt)){
    						$ret[]=$ar['id'];
    						$ids[]=$ar['id'];
    					}
    				}
    			}
    			if(count($ret)<$similar_items_count){
    				$last=$similar_items_count-count($ret);
    				unset($where['city_id']);
    				//unset($where['street_id']);
    				$where['id']='id NOT IN ('.implode(',',$ids).')';
    				$q='SELECT id FROM '.DB_PREFIX.'_data'.(!empty($where) ? ' WHERE '.implode(' AND ',$where) : '').' LIMIT '.$last;
    				$stmt=$DBC->query($q);
    				if($stmt){
    					while($ar=$DBC->fetch($stmt)){
    						$ret[]=$ar['id'];
    						$ids[]=$ar['id'];
    					}
    				}
    			}
    			if(count($ret)<$similar_items_count){
    				$last=$similar_items_count-count($ret);
    				unset($where['topic_id']);
    				$where['id']='id NOT IN ('.implode(',',$ids).')';
    				$q='SELECT id FROM '.DB_PREFIX.'_data'.(!empty($where) ? ' WHERE '.implode(' AND ',$where) : '').' LIMIT '.$last;
    				$stmt=$DBC->query($q);
    				if($stmt){
    					while($ar=$DBC->fetch($stmt)){
    						$ret[]=$ar['id'];
    						$ids[]=$ar['id'];
    					}
    				}
    			}
    		
    		
    		}
    	}
    	
    	$datas=array();
    	
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	
    	$form_data_src = $data_model->get_kvartira_model(false, true);
    	
    	$hasTlocation=false;
    	foreach($form_data_src['data'] as $key=>$val){
    		if($val['type']=='tlocation'){
    			$hasTlocation=true;
    			$tlocationElement=$key;
    			break;
    		}
    	}
    	
    	
    	if(count($ret)>0){
    		/*if(1==(int)$this->getConfigValue('apps.seo.no_trailing_slashes')){
    			$trailing_slashe='';
    		}else{
    			$trailing_slashe='/';
    		}*/
    		foreach($ret as $r){
    		
    			$form_data = $form_data_src;
    			$form_data = $data_model->init_model_data_from_db ( 'data', 'id', $r, $form_data['data'], true );
    			//print_r($form_data);
    			$form_data['topic_id']['value_string']=$categories['catalog'][$form_data['topic_id']['value']]['name'];
    			
    			$form_data['href']=$this->getRealtyHREF($r, false, array('alias'=>$form_data['translit_alias']['value'], 'topic_id'=>$form_data['topic_id']['value']));
    			
    			
    			/*if(1==$this->getConfigValue('apps.seo.level_enable')){
    				if($category_structure['catalog'][$form_data['topic_id']['value']]['url']!=''){
    					$form_data['parent_category_url']=$category_structure['catalog'][$form_data['topic_id']['value']]['url'].'/';
    				}else{
    					$form_data['parent_category_url']='';
    				}
    			}else{
    				$form_data['parent_category_url']='';
    			}*/
    		
    			/*@TODO:
    			 * вариант $form_data['translit_alias'] закріт правами видимости
    			 */
    			/*if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $form_data['translit_alias']['value']!=''){
    				$form_data['href']=SITEBILL_MAIN_URL.'/'.$form_data['parent_category_url'].$form_data['translit_alias']['value'].$trailing_slashe;
    				//$ra[$item_id]['href']=SITEBILL_MAIN_URL.'/'.$ra[$item_id]['parent_category_url'].$this->getTranslitAlias($ra[$item_id]['city'],$ra[$item_id]['street'],$ra[$item_id]['number']);
    			}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
    				$form_data['href']=SITEBILL_MAIN_URL.'/'.$form_data['parent_category_url'].'realty'.$form_data['id']['value'].'.html';
    			}else{
    				$form_data['href']=SITEBILL_MAIN_URL.'/'.$form_data['parent_category_url'].'realty'.$form_data['id']['value'].$trailing_slashe;
    			}*/
    		
    			if($hasTlocation){
    				$form_data['country_id']['value_string']=$form_data[$tlocationElement]['value_string']['country_id'];
    				$form_data['region_id']['value_string']=$form_data[$tlocationElement]['value_string']['region_id'];
    				$form_data['city_id']['value_string']=$form_data[$tlocationElement]['value_string']['city_id'];
    				$form_data['district_id']['value_string']=$form_data[$tlocationElement]['value_string']['district_id'];
    				$form_data['street_id']['value_string']=$form_data[$tlocationElement]['value_string']['street_id'];
    			}
    		
    			$datas[]=$form_data;
    		
    		}
    	}
    	
    	
    	return $datas;
    }
    
  
    
    protected function getBreadcrumbs($params){
    	/*if(!is_null($this->realty)){
    		$p['topic_id']=intval($this->realty['topic_id']['value']);
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    		$Structure_Manager = new Structure_Manager();
    		$category_structure = $Structure_Manager->loadCategoryStructure();
    		
    		$cch=$Structure_Manager->createCatalogChains();
    		//echo intval($this->realty['topic_id']['value']);
    		//print_r($cch);
    		
    		$bclevels=array(
    			array('topic_id', 'optype', 'country_id'),
    				array('topic_id', 'optype'),
    				array('topic_id'),
    		);
    		
    		$bc=array_reverse($bclevels);
    		$bread_crumbs=array();
    		$bclevels=array();
    		$bclevels_str=array();
    		
    		foreach($bc as $level){
    			$parts=array();
    			$params=array();
    			foreach($level as $point){
    				$parts[]=$this->realty[$point]['value_string']; 
    				$params[$point]=$this->realty[$point]['value']; 
    			}
    			$bclevels[]=array(
    				'title'=>implode(', ', $parts),
    				'params'=>$params,
    				'href'=>SITEBILL_MAIN_URL.'/?'.http_build_query($params)
    			);
    			$bclevels_str[]='<a href="'.SITEBILL_MAIN_URL.'/?'.http_build_query($params).'">'.implode(', ', $parts).'</a>';
    			//$bread_crumbs[]=
    		}
    		
    		$bclevels_str[]=$this->realty_title;
    		
    		return implode(' / ', $bclevels_str);
    		print_r($bclevels);
    		return $this->get_category_breadcrumbs( $params, $category_structure, SITEBILL_MAIN_URL.'/' );
    	}*/
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	$Structure_Manager = new Structure_Manager();
    	$category_structure = $Structure_Manager->loadCategoryStructure();
    	return $this->get_category_breadcrumbs( $params, $category_structure, SITEBILL_MAIN_URL.'/' );
    }
    
    public function getPublicMetaData($form_data, $hasTlocation=false, $tlocationElement=''){
    	return $this->getMetaData($form_data, $hasTlocation, $tlocationElement);
    }
    
    protected function getMetaData($form_data, $hasTlocation=false, $tlocationElement=''){
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	$Structure_Manager = new Structure_Manager();
    	$category_structure = $Structure_Manager->loadCategoryStructure();
    	
    	
    	$title='';
    	$meta_title='';
    	$meta_description='';
    	$meta_keywords='';
    	$params['topic_id']= $form_data['topic_id']['value'];
    	
    	$title_str=trim($this->getConfigValue('apps.realty.title_preg'));
    	
    	if($title_str!=''){
    		//$title_str='';
    		 
    		
    		preg_match_all('/{([^}]+)}/', $title_str, $matches);
    		 
    		$str_parts=array();
    		if(count($matches[1])>0){
    			foreach ($matches[1] as $key=>$keyval){
    				if($keyval=='!topic_path'){
    					$str_parts[$key]=$this->get_category_breadcrumbs_string( $params, $category_structure, SITEBILL_MAIN_URL.'/' );
    				}elseif(isset($form_data[$keyval])){
    					if(in_array($form_data[$keyval]['type'], array('select_box', 'select_by_query', 'select_box_structure'))){
    						$str_parts[$key]=$form_data[$keyval]['value_string'];
    					}elseif($form_data[$keyval]['type']=='price'){
    						$str_parts[$key]=number_format($form_data[$keyval]['value'],0,',',' ');
    					}else{
    						$str_parts[$key]=$form_data[$keyval]['value'];
    					}
    				}else{
    					$str_parts[$key]='';
    				}
    			}
    		
    			$keys=array();
    		
    			foreach ($matches[1] as $key=>$keyval){
    				$keys[$key]='{'.$keyval.'}';
    			}
    		
    			$title_str=str_replace($keys, $str_parts, $title_str);
    		
    			$title=$title_str;
    		}
    	}else{
    		$title_parts=array();
    		if($hasTlocation){
    			$title_parts[]=$this->get_category_breadcrumbs_string( $params, $category_structure, SITEBILL_MAIN_URL.'/' );
    			if($form_data[$tlocationElement]['tlocation_string']!=''){
    				$title_parts[]=$form_data[$tlocationElement]['tlocation_string'];
    			}
    			if(0!=(int)$form_data['price']['value']){
    				$title_parts[]=number_format($form_data['price']['value'],0,',',' ');
    			}
    			if(!empty($title_parts)){
    				$title=implode(', ', $title_parts);
    			}
    		}else{
    			$title_parts[]=$this->get_category_breadcrumbs_string( $params, $category_structure, SITEBILL_MAIN_URL.'/' );
    			if($form_data['city_id']['value_string']!=''){
    				$title_parts[]=$form_data['city_id']['value_string'];
    			}
    			if($form_data['street_id']['value_string']!=''){
    				$title_parts[]=$form_data['street_id']['value_string'];
    			}
    			if(0!=(int)$form_data['price']['value']){
    				if(1==$this->getConfigValue('currency_enable') && isset($form_data['currency_id']) && $form_data['currency_id']['value']>0){
						$title_parts[]=number_format($form_data['price']['value'],0,',',' ').' '.$form_data['currency_id']['value_string'];
					}else{
						$title_parts[]=number_format($form_data['price']['value'],0,',',' ');
					}
    			}
    			if(!empty($title_parts)){
    				$title=implode(', ', $title_parts);
    			}
    		}
    	}
    	
    	
    	
    	$this->realty_title=$title;
    	
    	
    	
    	
    	
    
    	if($form_data['meta_title']['value']==''){
    		$meta_title=$title;
    	}else{
    		$meta_title=$form_data['meta_title']['value'];
    	}
    	 
    	if($form_data['meta_description']['value']!=''){
    		$meta_description=$form_data['meta_description']['value'];
    	}
    
    	if($form_data['meta_keywords']['value']!=''){
    		$meta_keywords=$form_data['meta_keywords']['value'];
    	}
    	
    	
    	
    	/*
    	 if($form_data['meta_title']['value']==''){
    	$this->template->assign('title', $form_data['topic_id']['value_string'].", ".$form_data['city_id']['value_string'].", ".$form_data['street_id']['value_string'].', цена: '.$form_data['price']['value'].' '.($form_data['currency_id']['value_string']=='' ? Multilanguage::_('L_RUR_SHORT') : $form_data['currency_id']['value_string']).' | '.$this->getConfigValue('site_title') );
    	}else{
    	$this->template->assign('title', $form_data['meta_title']['value']);
    	}
    	 
    	if($form_data['meta_description']['value']!=''){
    	$this->template->assign('meta_description', $form_data['meta_description']['value']);
    	}else{
    	$this->template->assign('meta_description', $form_data['text']['value'].' '.$this->getConfigValue('site_title'));
    	}
    	 
    	if($form_data['meta_keywords']['value']!=''){
    	$this->template->assign('meta_keywords', $form_data['meta_keywords']['value']);
    	}else{
    	$kw=array();
    
    	 
    	$kw[]=$this->getConfigValue('meta_keywords_main');
    	$kw[]=$form_data['optype']['value_string'];
    	$kw[]=$form_data['topic_id']['value_string'];
    	$kw[]=$form_data['city_id']['value_string'];
    	$kw[]=$form_data['district_id']['value_string'];
    	if($form_data['room_count']['value']>0){
    	$kw[]='комнат '.$form_data['room_count']['value'];
    	}
    	$kw=array_filter($kw);
    	if(count($kw)>0){
    	$this->template->assign('meta_keywords', implode(', ', $kw));
    	}else{
    	$this->template->assign('meta_keywords', $this->getConfigValue('meta_keywords_main'));
    	}
    	}
    	*/
    	
    	
    	 
    	 
    	return array(
    			'title'=>$title,
    			'meta_title'=>$meta_title,
    			'meta_description'=>$meta_description,
    			'meta_keywords'=>$meta_keywords
    	);
    }
    

}