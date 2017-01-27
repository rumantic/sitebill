<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * realtyview fronend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class realtyview_site extends realtyview_admin {

	protected $realty_id=0;
	
	protected $form_data_shared=null;
	protected $form_data=null;
	
	protected function make404(){
		$sapi_name = php_sapi_name();
		if ($sapi_name == 'cgi' || $sapi_name == 'cgi-fcgi') {
			header('Status: 404 Not Found');
		} else {
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
		}
		$this->template->assign('title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
		$this->template->assign('meta_title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
		$this->template->assign('error_message', '<h1>'.Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND').'</h1>');
		$this->template->assign('main_file_tpl', 'error_message.tpl');
		exit();
	}
	
	protected function movedPermanently($new_location){
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: '.$new_location);
		exit();
	}
	
	public function getActualURL($data){
		
		
		if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $data['translit_alias']!=''){
			$url=$data['translit_alias'];
		}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
			$url='realty'.$data['id'].'.html';
		}else{
			$url='realty'.$data['id'];
		}
		
		if(1==$this->getConfigValue('apps.seo.level_enable')){
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
			$Structure_Manager = new Structure_Manager();
			$category_structure = $Structure_Manager->loadCategoryStructure();
			if($category_structure['catalog'][$data['topic_id']]['url']!=''){
				$url=$category_structure['catalog'][$data['topic_id']]['url'].'/'.$url;
			}
		}
		
		return $url;
		
	}
	
	//protected function
	
	function frontend () {
		if(1!=$this->getConfigValue('apps.realtyview.enable')){
			return false;
		}
		$REQUESTURIPATH=Sitebill::getClearRequestURI();
		if(preg_match('/realty(\d+)(.html)?$/', $REQUESTURIPATH, $matches)){
			$this->realty_id=(int)$matches[1];
			$DBC=DBC::getInstance();
			$query='SELECT id, topic_id, translit_alias, active FROM '.DB_PREFIX.'_data WHERE id=? LIMIT 1';
			$stmt=$DBC->query($query, array($this->realty_id));
			if($stmt){
				$ar=$DBC->fetch($stmt);
			}else{
				return false;
			}
			
			if($ar['active']!=1){
				return false;
			}
			//$ar=$DBC->fetch($stmt);
			$actual_url=$this->getActualURL($ar);
			if($actual_url!=$REQUESTURIPATH){
				
				$this->movedPermanently(SITEBILL_MAIN_URL.'/'.$actual_url.(count($_GET)>0 ? '?'.http_build_query($_GET) : ''));
			}
			//echo $actual_url;
		}else{
			$url_string_parts=explode('/', $REQUESTURIPATH);
			if(count($url_string_parts)>0){
				$possible_alias=$url_string_parts[count($url_string_parts)-1];
			
				$possible_alias=preg_replace('/[^A-Za-z0-9_-]/', '', urldecode($possible_alias));
				if($possible_alias!=''){
					$DBC=DBC::getInstance();
					$query='SELECT id, active FROM '.DB_PREFIX.'_data WHERE translit_alias=?';
					$stmt=$DBC->query($query, array($possible_alias));
					if($stmt){
						$ar=$DBC->fetch($stmt);
						if((int)$ar['id']>0){
							$this->realty_id=(int)$ar['id'];
							if($ar['active']==0){
								return false;
							}
			
							
							
						}
					}
				}
			}
		}
		
		if($this->realty_id!=0){
			$this->growCounter('data', 'id', $this->realty_id, $this->getSessionUserId());
			$this->template->assert('main', $this->showRealty());
			/*require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
			$kvartira_view = new Kvartira_View();
			if($html=$kvartira_view->main($realty_id)){
				$this->template->assert('main', $html);
				$result=true;
			}*/
			return true;
		}
		return false;
	}
	
	public function setRealtyID ( $realty_id ) {
		$this->realty_id = $realty_id;
	}
	
	protected function loadSharedModel(){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$this->form_data_shared = $data_model->get_kvartira_model(false, true);
		$this->form_data_shared = $data_model->init_model_data_from_db ( 'data', 'id', $this->realty_id, $this->form_data_shared['data'], true );
	}
	
	protected function setAdvAuthor(){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
		$data_model = new Data_Model();
		$user_object_manager = new User_Object_Manager();
		$form_user = $user_object_manager->get_user_model();
		
		if($this->getConfigValue('apps.realtypro.show_contact.enable')){
			$form_user = $data_model->init_model_data_from_db ( 'user', 'user_id', $this->form_data_shared['user_id']['value'], $form_user['user'], true);
		}else{
			$form_user = $data_model->init_model_data_from_db ( 'user', 'user_id', $this->form_data_shared['user_id']['value'], $form_user['user'], true);
		}
		
		if($form_user['login']['value']=='_unregistered'){
			$form_user['fio']['value']=$this->form_data_shared['fio']['value'];
			$form_user['phone']['value']=$this->form_data_shared['phone']['value'];
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
		
		$this->template->assign('user_data', $form_user);
	}
	
	protected function setAdvPhoto(){
		$this->template->assign('photo', $this->form_data_shared['image']['image_array']);
	}
	
	public function showRealty(){
		
		if($this->getConfigValue('use_google_map')){
			$this->template->assign('map_type', 'google');
		}
		
		$DBC=DBC::getInstance();
		
		
		$this->loadSharedModel();
		$this->setAdvAuthor();
		
		
		
		$realty_id=$this->realty_id;
		//$result=false;
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
		//require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
	
		$Structure_Manager = new Structure_Manager();
		$category_structure = $Structure_Manager->loadCategoryStructure();
		//$user_object_manager = new User_Object_Manager();
	
		$data_model = new Data_Model();
	
		
		$form_data = $data_model->get_kvartira_model(false, false);
	
		//load Data model full without rules
		//$this->form_data_shared = $data_model->get_kvartira_model(false, true);
	
		//init lang variables
		$form_data_language = $data_model->get_kvartira_model(false, true);
		 
		//load User model with rules
		//$form_user = $user_object_manager->get_user_model();
	
		//init Data model with rules
		$form_data = $data_model->init_model_data_from_db ( 'data', 'id', $realty_id, $form_data['data'], true );
	
		//init lang variables
		$form_data_language = $data_model->init_model_data_from_db ( 'data', 'id', $realty_id, $form_data_language['data'], true );
	
		//init Data model full without rules
		//$this->form_data_shared = $data_model->init_model_data_from_db ( 'data', 'id', $realty_id, $this->form_data_shared['data'], true );
		 
		if(!$form_data){
			return $result;
		}
	/*
		if(isset($form_data_shared['active']) && $form_data_shared['active']['value']==0){
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
			$this->template->assign('main_file_tpl', 'error_message.tpl');
			return false;
	
			//return $result;
		}*/
		
		
		
		if(1==$this->getConfigValue('apps.geodata.enable') && 1==$this->getConfigValue('apps.geodata.allow_view_coding')){
			$this->geocodeRealtyNow();
		}
		
		$form_data = $data_model->init_language_values($form_data, $form_data_language);
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
		
	
		if(isset($form_data['topic_id'])){
			$form_data['topic_id']['value_string']=$category_structure['catalog'][$form_data['topic_id']['value']]['name'];
		}
	
	
		$this->template->assert('hvd_tabbed', $this->getAutoOutputData($form_data));
		 
		$this->setSimilar();
		
		
		
	
		$this->template->assign('admin_user_id', $this->getAdminUserId());
		$this->template->assign('current_user_id', $this->getSessionUserId());
		
		
		$this->setAdvPhoto();
		$this->setAdvInfo();
		
		$this->makeUserOperatios();
		 
		
	
		
		$this->template->assign('data', $form_data);
		
		if ( $_REQUEST['REST_API'] == 1 ) {
			if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/api/classes/class.static_data.php') ) {
				$static_data = Static_Data::getInstance();
				$static_data::set_data($form_data);
				return;
			}
		}
		
		$this->setBreadcrumbs();
		$this->setMetaData();
		$this->setAdvOnMap();
		$this->setCommentsBock();
		$this->setTemplates();
		$this->makePDF($realty_id);
		 
		
	}
	
	protected function setCommentsBock(){
		if(1==$this->getConfigValue('apps.comment.enable')){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/comment/admin/admin.php';
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/comment/site/site.php';
			$CoM=new comment_site();
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
			$Login = new Login();
			$user_id=(int)$Login->getSessionUserId();
			$commentsPanel=$CoM->generateCommentPanel($user_id, 'data', $this->realty_id);
		}
	}
	
	protected function setTemplates(){
		$this->set_apps_template('realtyview', $this->getConfigValue('theme'), 'main_file_tpl', 'main.tpl');
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_view.tpl')){
			$this->template->assign('inc_file_tpl', SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/realty_view.tpl');
		}else{
			$this->set_apps_template('realtyview', $this->getConfigValue('theme'), 'inc_file_tpl', 'view.tpl');
		}
	}
	
	protected function setAdvInfo(){
		$this->template->assign('data_shared', $this->form_data_shared);
	}
	
	protected function setAdvOnMap(){
		$this->template->assert('geoobjects_collection_clustered', json_encode($this->getRealtyOnMap($this->form_data_shared)));
	}
	
	protected function geocodeRealtyNow(){
		foreach($this->form_data_shared as $k=>$item){
			if($item['type']=='geodata' && 1==$this->getConfigValue('apps.geodata.enable') && 1==$this->getConfigValue('apps.geodata.allow_view_coding')){
				if($item['value']['lat']=='' && $item['value']['lng']=='' && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/geodata/admin/admin.php') && 1==$this->getConfigValue('apps.geodata.enable')){
					require_once SITEBILL_DOCUMENT_ROOT.'/apps/geodata/admin/admin.php';
					if(method_exists('geodata_admin', 'geocode_address')){
						$address_array=array();
						if(isset($this->form_data_shared['country_id']) && $this->form_data_shared['country_id']['value_string']!=''){
							$address_array[]=$this->form_data_shared['country_id']['value_string'];
						}
						if(isset($this->form_data_shared['region_id']) && $this->form_data_shared['region_id']['value_string']!=''){
							$address_array[]=$this->form_data_shared['region_id']['value_string'];
						}
						if(isset($this->form_data_shared['city_id']) && $this->form_data_shared['city_id']['value_string']!=''){
							$address_array[]=$this->form_data_shared['city_id']['value_string'];
							if(isset($this->form_data_shared['street_id']) && $this->form_data_shared['street_id']['value_string']!=''){
								$address_array[]=$this->form_data_shared['street_id']['value_string'];
								if(isset($this->form_data_shared['number']) && $this->form_data_shared['number']['value']!=''){
									$address_array[]=$this->form_data_shared['number']['value'];
								}
							}
						}
						$data=geodata_admin::geocode_address(implode(', ', $address_array));
						if($data && $data['lat']!='' && $data['lng']!=''){
							$this->form_data_shared[$k]['value']['lat']=$data['lat'];
							$this->form_data_shared[$k]['value']['lng']=$data['lng'];
							$form_data[$k]['value']['lat']=$data['lat'];
							$form_data[$k]['value']['lng']=$data['lng'];
							$DBC=DBC::getInstance();
							$query='UPDATE '.DB_PREFIX.'_data SET `'.$item['name'].'_lat`=?, `'.$item['name'].'_lng`=? WHERE id=?';
							$stmt=$DBC->query($query, array($data['lat'], $data['lng'], $this->form_data_shared['id']['value']));
						}
		
					}
		
		
				}
				break;
			}
		}
	}
	
	protected function setBreadcrumbs(){
		$params['topic_id'] = (int)$this->form_data_shared['topic_id']['value'];
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
		$Structure_Manager = new Structure_Manager();
		$category_structure = $Structure_Manager->loadCategoryStructure();
		
		$breadcrumbs=$this->get_category_breadcrumbs( $params, $category_structure, SITEBILL_MAIN_URL.'/' );
		
		$this->template->assign('realty_breadcrumbs', explode(' / ', $breadcrumbs));
		$this->template->assign('breadcrumbs', $breadcrumbs );
	}
	
	protected function setMetaData(){
		
		$hasTlocation=false;
		$tlocationElement='';
		
		foreach($this->form_data_shared as $key=>$val){
			if($val['type']=='tlocation'){
				//print_r($val);
				$hasTlocation=true;
				$tlocationElement=$key;
				$this->form_data_shared['country_id']['value_string']=$val['value_string']['country_id'];
				$this->form_data_shared['region_id']['value_string']=$val['value_string']['region_id'];
				$this->form_data_shared['city_id']['value_string']=$val['value_string']['city_id'];
				$this->form_data_shared['district_id']['value_string']=$val['value_string']['district_id'];
				$this->form_data_shared['street_id']['value_string']=$val['value_string']['street_id'];
			}
		}
		
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
		$Structure_Manager = new Structure_Manager();
		$category_structure = $Structure_Manager->loadCategoryStructure();
		$title='';
		$meta_title='';
		$meta_description='';
		$meta_keywords='';
		$params['topic_id']= $this->form_data_shared['topic_id']['value'];
		
		$title_parts=array();
		if($hasTlocation){
			$title_parts[]=$this->get_category_breadcrumbs_string( $params, $category_structure, SITEBILL_MAIN_URL.'/' );
			if($this->form_data_shared[$tlocationElement]['tlocation_string']!=''){
				$title_parts[]=$this->form_data_shared[$tlocationElement]['tlocation_string'];
			}
			if(0!=(int)$this->form_data_shared['price']['value']){
				$title_parts[]=number_format($this->form_data_shared['price']['value'],0,',',' ');
			}
			if(!empty($title_parts)){
				$title=implode(', ', $title_parts);
			}
		}else{
			$title_parts[]=$this->get_category_breadcrumbs_string( $params, $category_structure, SITEBILL_MAIN_URL.'/' );
			if($this->form_data_shared['city_id']['value_string']!=''){
				$title_parts[]=$this->form_data_shared['city_id']['value_string'];
			}
			if($this->form_data_shared['street_id']['value_string']!=''){
				$title_parts[]=$this->form_data_shared['street_id']['value_string'];
			}
			if(0!=(int)$this->form_data_shared['price']['value']){
				$title_parts[]=number_format($this->form_data_shared['price']['value'],0,',',' ');
			}
			if(!empty($title_parts)){
				$title=implode(', ', $title_parts);
			}
		}
			
		
		
		
		if($this->form_data_shared['meta_title']['value']==''){
			$meta_title=$title;
		}else{
			$meta_title=$this->form_data_shared['meta_title']['value'];
		}
		
		if($this->form_data_shared['meta_description']['value']!=''){
			$meta_description=$this->form_data_shared['meta_description']['value'];
		}
		
		if($this->form_data_shared['meta_keywords']['value']!=''){
			$meta_keywords=$this->form_data_shared['meta_keywords']['value'];
		}
		
		$this->template->assign('meta_title', $meta_title);
		$this->template->assign('title', $title);
		$this->template->assign('meta_description', $meta_description);
		$this->template->assign('meta_keywords', $meta_keywords);
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
	
	protected function setSimilar(){
		$DBC=DBC::getInstance();
		
		$simparams=array(
				'id'=>(int)$this->form_data_shared['id']['value'],
				'topic_id'=>(int)$this->form_data_shared['topic_id']['value'],
				'city_id'=>(int)$this->form_data_shared['city_id']['value'],
				'district_id'=>(int)$this->form_data_shared['district_id']['value'],
				'street_id'=>(int)$this->form_data_shared['street_id']['value'],
		);
		
		
		$similar_items_count=(0==(int)$this->getConfigValue('similar_items_count') ? 5 : (int)$this->getConfigValue('similar_items_count'));
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
		$Structure_Manager = new Structure_Manager();
		$category_structure = $Structure_Manager->loadCategoryStructure();
		 
		$ret=array();
		$datas=array();
		$where=array();
		if(!empty($params)){
			$ids[]=$params['id'];
			$where['active']='active=1';
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
				unset($where['district_id']);
				unset($where['street_id']);
				$where['id']='id NOT IN ('.implode(',',$ids).')';
				$q='SELECT id FROM '.DB_PREFIX.'_data'.(!empty($where) ? ' WHERE '.implode(' AND ',$where) : '').' LIMIT '.$similar_items_count;
			   
				$stmt=$DBC->query($q);
				if($stmt){
					while($ar=$DBC->fetch($stmt)){
						$ret[]=$ar['id'];
						$ids[]=$ar['id'];
					}
				}
			}
			if(count($ret)<$similar_items_count){
				unset($where['city_id']);
				//unset($where['street_id']);
				$where['id']='id NOT IN ('.implode(',',$ids).')';
				$q='SELECT id FROM '.DB_PREFIX.'_data'.(!empty($where) ? ' WHERE '.implode(' AND ',$where) : '').' LIMIT '.$similar_items_count;
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
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		 
		$form_data = $data_model->get_kvartira_model(false, false);
		 
		$hasTlocation=false;
		foreach($form_data['data'] as $key=>$val){
			if($val['type']=='tlocation'){
				$hasTlocation=true;
				$tlocationElement=$key;
				break;
			}
		}
		 
		 
		$i=0;
		foreach($ret as $r){
	
			$form_data = $data_model->get_kvartira_model(false, false);
			$form_data = $data_model->init_model_data_from_db ( 'data', 'id', $r, $form_data['data'], true );
			//print_r($form_data);
			$form_data['topic_id']['value_string']=$categories['catalog'][$form_data['topic_id']['value']]['name'];
			if(1==$this->getConfigValue('apps.seo.level_enable')){
				if($category_structure['catalog'][$form_data['topic_id']['value']]['url']!=''){
					$form_data['parent_category_url']=$category_structure['catalog'][$form_data['topic_id']['value']]['url'].'/';
				}else{
					$form_data['parent_category_url']='';
				}
			}else{
				$form_data['parent_category_url']='';
			}
	
			if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $form_data['translit_alias']['value']!=''){
				$form_data['href']=SITEBILL_MAIN_URL.'/'.$form_data['parent_category_url'].$form_data['translit_alias']['value'];
				//$ra[$item_id]['href']=SITEBILL_MAIN_URL.'/'.$ra[$item_id]['parent_category_url'].$this->getTranslitAlias($ra[$item_id]['city'],$ra[$item_id]['street'],$ra[$item_id]['number']);
			}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
				$form_data['href']=SITEBILL_MAIN_URL.'/'.$form_data['parent_category_url'].'realty'.$form_data['id']['value'].'.html';
			}else{
				$form_data['href']=SITEBILL_MAIN_URL.'/'.$form_data['parent_category_url'].'realty'.$form_data['id']['value'];
			}
	
			if($hasTlocation){
				$form_data['country_id']['value_string']=$form_data[$tlocationElement]['value_string']['country_id'];
				$form_data['region_id']['value_string']=$form_data[$tlocationElement]['value_string']['region_id'];
				$form_data['city_id']['value_string']=$form_data[$tlocationElement]['value_string']['city_id'];
				$form_data['district_id']['value_string']=$form_data[$tlocationElement]['value_string']['district_id'];
				$form_data['street_id']['value_string']=$form_data[$tlocationElement]['value_string']['street_id'];
			}
	
			$datas[]=$form_data;
			$i++;
			if($i==5){
				break;
			}
		}
		
		$this->template->assign('similar_data', $datas);
		
	}
	
	
	
	protected function getBreadcrumbs($params){
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
		 
		if($hasTlocation){
			$title=$this->get_category_breadcrumbs_string( $params, $category_structure, SITEBILL_MAIN_URL.'/' ).", ".$form_data[$tlocationElement]['tlocation_string'].", ".number_format($form_data['price']['value'],0,',',' ');
		}else{
			//echo '<pre>';
			//print_r($form_data);
			$title=$this->get_category_breadcrumbs_string( $params, $category_structure, SITEBILL_MAIN_URL.'/' ).", ".$form_data['city_id']['value_string'].", ".$form_data['street_id']['value_string'].", ".number_format($form_data['price']['value'],0,',',' ');
		}
	
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
	
	protected function makeUserOperatios(){
		
	}
	
	protected function makePDF($realty_id){
		if(1==(int)$this->getConfigValue('apps.pdfreport.enabled') && $_GET['format']=='pdf'){
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
			$pdfpageurl='http://'.$_SERVER['HTTP_HOST'].'/'.$pdfpageurl;
			$pdfpageurl.=' | '.date('d-m-Y H:i');
			$this->template->assign('pdfpageurl', $pdfpageurl);
	
	
			$this->template->assign('_core_folder', SITEBILL_DOCUMENT_ROOT);
			$pdf_file_name='realty'.$realty_id.$_tpl_code.'.pdf';
			$pdf_file_storage=SITEBILL_DOCUMENT_ROOT.'/cache/';
			if(0==(int)$this->getConfigValue('apps.pdfreport.use_cache')){
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
				/*if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/pdfreport/realty_view.tpl')){
				 $html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/pdfreport/realty_view.tpl');
				}else{
				$html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/pdfreport/admin/template/realty_view.tpl');
				}*/
				 
				$dompdf = new DOMPDF();
				$dompdf->load_html($html);
				$dompdf->render();
				 
				$output = $dompdf->output();
				//header("Content-type: application/pdf");
				//echo $output;
				//file_put_contents(SITEBILL_DOCUMENT_ROOT.'/cache/'.$pdf_file_name, $output);
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
					/*if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/pdfreport/realty_view.tpl')){
					 $html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/pdfreport/realty_view.tpl');
					}else{
					$html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/pdfreport/admin/template/realty_view.tpl');
					}*/
					 
					$dompdf = new DOMPDF();
					$dompdf->load_html($html);
					$dompdf->render();
					 
					$output = $dompdf->output();
					file_put_contents(SITEBILL_DOCUMENT_ROOT.'/cache/'.$pdf_file_name, $output);
					//$output=file_get_contents(SITEBILL_DOCUMENT_ROOT.'/cache/'.$pdf_file_name);
				}
			}
			header("Content-type: application/pdf");
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
	/*
	function growCounter($table_name, $primary_key_name, $primary_key_value, $user_id=0){
		if(1==$this->getConfigValue('use_realty_view_counter')){
			if(!isset($_SESSION['realty_views'][$primary_key_value])){
				$DBC=DBC::getInstance();
				$query='UPDATE '.DB_PREFIX.'_'.$table_name.' SET view_count=view_count+1 WHERE '.$primary_key_name.'=?';
				$stmt=$DBC->query($query, array($primary_key_value));
				$_SESSION['realty_views'][$primary_key_value]=1;
			}
		}
	}
	*/
}