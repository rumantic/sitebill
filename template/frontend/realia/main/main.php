<?php
class frontend_main extends SiteBill_Krascap {
	/**
	 * Main
	 * @param void
	 * @return string
	 */
	function main () {
		$layouts=array(
			'_default'=>'layout_basic.tpl',
			'home'=>'layout_home.tpl',
			'apps'=>'layout_basic.tpl',
			'realtygrid'=>'layout_full.tpl',
			'realtyview'=>'layout_full.tpl',
			'account'=>'layout_account.tpl',
			'find'=>'layout_find.tpl',
			'realtymap'=>'layout_map.tpl'/*,
			'multitab'=>'layout_multitab.tpl',*/
		);
		
		$this->check_local_config();
				
		$work_subcontroller='';
		$has_result=false;
		$undetected_url=false;
		
			
			global $__site_title, $folder, $smarty;
			$REQUESTURIPATH=Sitebill::getClearRequestURI();
			$this->template->assert('REQUESTURIPATH', $REQUESTURIPATH);
				
			Multilanguage::appendTemplateDictionary($this->getConfigValue('theme'));
			
			if($this->getConfigValue('use_google_map')){
				$this->template->assign('map_type', 'google');
			}else{
				$this->template->assign('map_type', 'yandex');
			}
			
			$this->getNewest();
			$this->getAgents();
			
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
			$apps_processor = new Apps_Processor();
			$apps_processor->run_preload();
	
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
			$Structure_Manager = new Structure_Manager();
	
			if ( $_SESSION['theme'] != '' ) {
				$theme = $_SESSION['theme'];
			} else {
				$theme = $this->getConfigValue('theme');
			}
				
			$this->template->assert('template_vars_logo', $this->getConfigValue('template.'.$theme.'.logo'));
			
			$this->template->assert('current_theme_name', $theme);
			$this->template->assert('allow_register_account', $this->getConfigValue('allow_register_account'));
                        
			$this->template->assert('apps_contact_phone', $this->getConfigValue('apps.contact.phone'));
			$this->template->assert('apps_contact_email', $this->getConfigValue('apps.contact.email'));
			$this->template->assert('apps_contact_address', $this->getConfigValue('apps.contact.address'));
			$this->template->assert('apps_contact_skype', $this->getConfigValue('apps.contact.skype'));
                        
			
			$result=$this->_detectUrlParams($_SERVER['REQUEST_URI']);
			
			if($result['topic_id'] && !is_array($result['topic_id'])){
				$this->setRequestValue('topic_id', $result['topic_id']);
			}
			if($result['city_id']){
				$this->setRequestValue('city_id', $result['city_id']);
			}
			
			$this->template->assert('navmenu', $this->getTemplateMenu());
	
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/search/kvartira_search.php');
			$kvartira_search_form = new Kvartira_Search_Form();
			$kvartira_search_form->main();
			$this->template->assert('search_form_template', 'search_form.tpl');
			//$this->template->assert('available_langs', Multilanguage::availableLanguages());
			if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/upper/upper.xml') ) {
			    $this->template->assert('show_upper', 'true');
			}
	
			
	
			$extendedSearchFormParams=$this->getExtendedSearchFormParams();
			$this->template->assert('max_floor_count', $extendedSearchFormParams['max_floor_count']);
			$this->template->assert('max_price', $extendedSearchFormParams['max_price']);
			
			if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable')==1){
				$this->template->assert('apps_billing', 'on');
				$this->template->assert('per_day_price', $this->getConfigValue('vip_cost'));
				$this->template->assert('per_day_price_premium', $this->getConfigValue('premium_cost'));
				$this->template->assert('ups_price', $this->getConfigValue('ups_price'));
				$this->template->assert('per_day_price_bold', $this->getConfigValue('bold_cost'));
				$this->template->assert('now', time());
				$this->grid_vip_right();
			}else{
				$this->template->assert('apps_billing', 'off');
				$this->grid_special_right();
			}
			
			
			//$this->template->assert('type_list', $this->getTypeString());
			//set default value
			$this->template->assert('base', SITEBILL_MAIN_URL);
			$this->template->assert('show_demo_banners', $this->getConfigValue('show_demo_banners'));
			$this->template->assert('REQUEST_URI', $_SERVER['REQUEST_URI']);
			$this->template->assert('type_list2', '');
			$this->template->assert('type_list3', '');
			$this->template->assert('title', $this->getConfigValue('site_title'));
			$this->template->assert('city_by_default', $this->getConfigValue('city'));
			$this->template->assert('estate_folder', $folder);
			$this->template->assert('right_column', 1);
	
			$this->template->assert('structure_box', $Structure_Manager->getCategorySelectBoxWithName('topic_id', $this->getRequestValue('topic_id') ));
			//print_r($_SESSION);
	
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
			$Login = new Login();
	
			if ( preg_match('/\/logout/', $_SERVER['REQUEST_URI']) ) {
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/logout.php');
				$Logout = new Logout;
				$Logout->main();
			}
			
			
			if(1==$this->getConfigValue('apps.company.enable')){
				$this->template->assign('app_company_namespace',$this->getConfigValue('apps.company.namespace'));
			}
			
	
			$this->template->assert('user_id', $Login->getSessionUserId());
			//$this->template->assert('auth_menu', $Login->getAuthMenu());
			$this->template->assert('user_menu', $Login->getUserMenu());
			
			
				
			
			
			if ( $this->getConfigValue('allow_register_account') ) {
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/register_using_model.php');
				$Register = new Register_Using_Model();
				//$smarty->assign->assert('main', $Register->main());
				$rs1 = $Register->getRegisterFormElements();
				$this->template->assert('register_form_elements', $rs1);
				
				
			}
			
	
			if($this->getConfigValue('apps.freeorder.enable')==1){
				require_once SITEBILL_DOCUMENT_ROOT.'/apps/freeorder/admin/admin.php';
				require_once SITEBILL_DOCUMENT_ROOT.'/apps/freeorder/site/site.php';
				$FreeOrder=new freeorder_site();
				$this->template->assert('freeorder_form', $FreeOrder->getForm());
				$this->template->assert('freeorder_on', 'yes');
			}else{
				$this->template->assert('freeorder_on', 'no');
			}
	
			$this->template->assert('meta_keywords', '');
			$this->template->assert('meta_description', '');
			
			
			if ( preg_match('/^myfavorites/', $REQUESTURIPATH) ) {
	        	$this->template->assert('main', '<p><br></p>'.$this->grid_adv_favorites());
	        	
	        	$work_subcontroller='realtygrid';
	        	$has_result=true;
	       }
	
	
			if ( !$has_result && preg_match('/\/robox/', $_SERVER['REQUEST_URI']) ) {
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/robokassa/robokassa.php');
				$robokassa = new Robox();
				$rs = $robokassa->main();
				if ( preg_match('/result/', $_SERVER['REQUEST_URI']) ) {
					echo $rs;
					exit;
				}
				$this->template->assert('main', $rs);
				
				$work_subcontroller='robox';
				$has_result=true;
				
				/*$this->template->render();
				$rs = $this->template->toHTML();
				return $rs;*/
			}
			if ( !$has_result && preg_match('/^maping[\/]?$/', $REQUESTURIPATH) ) {
				$this->template->assert('main', 'realtymap');
				$work_subcontroller='realtymap';
				$has_result=true;
			}
			if ( !$has_result && preg_match('/^map(\/(\?.*)?)?$/', $REQUESTURIPATH) ) {
				$this->template->assert('main', '<p><br></p>'.$this->map());
				$work_subcontroller='realtygrid';
				$has_result=true;
				//$this->map();
				//return true;
			}
			if ( !$has_result && preg_match('/^map_full_screen(\/(\?.*)?)?$/', $REQUESTURIPATH) ) {
			    echo '&nbsp;';
			    $this->template->assert('data', $this->map(true));
			    $work_subcontroller='realtygrid';
			    $has_result=true;
				//return true;
			}
			//echo $REQUESTURIPATH;
				
			if ( $this->getConfigValue('apps.geodata.on_home') && $REQUESTURIPATH == '' ) {
			    $this->template->assert('geodata_on_home', 1);
			    $this->template->assert('_geo_data', $this->map(true));
			}
				
			if ( !$has_result && preg_match('/^goroda\//', $REQUESTURIPATH) ) {
				$city=$this->getCityListTr();
				$topic=$this->getTopicListTr();
				if(count($city)>0 && count($topic)>0){
					foreach($city as $c){
						foreach($topic as $t){
							$rs.='<a href="/'.$c['translit_name'].'-'.$t['translit_name'].'.html">'.$c['name'].' ('.$t['name'].')</a><br />';
						}
					}
				}
				$this->template->assert('main', $rs);
				/*$this->template->assert('search_form', $land_front->getSearchForm());
				*/
				
				$work_subcontroller='goroda';
				$has_result=true;
			}
	
	
			if ( !$has_result && preg_match('/^register/', $REQUESTURIPATH) ) {
				if ( !$this->getConfigValue('allow_register_account') ) {
					$this->template->assert('main', 'Функция регистрации отключена администратором');
				} else {
					require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
					require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/register_using_model.php');
					$Register = new Register_Using_Model();
					//$smarty->assign->assert('main', $Register->main());
					$rs1 = $Register->main();
					$this->template->assert('main', $rs1);
				}
				
				$work_subcontroller='register';
				$has_result=true;
			}
	
			if ( !$has_result && preg_match('/^remind/', $REQUESTURIPATH) ) {
				if ( !$this->getConfigValue('allow_remind_password') ) {
					$this->template->assert('main', 'Функция напоминания пароля отключена администратором');
				} else {
					require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/user.php');
					require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/remind.php');
					$remind = new Remind;
					//$smarty->assign('main', $remind->main());
					$this->template->assert('main', $remind->main());
				}
				$work_subcontroller='register';
				$has_result=true;
			}
	
			if ( !$has_result && preg_match('/^login/', $REQUESTURIPATH) ) {
				$this->template->assert('main', $Login->main());
				if ( $Login->getSessionUserId() > 0 ) {
					$this->template->assert('auth_menu', $Login->getAuthMenu());
				}
				$this->template->render();
				$rs = $this->template->toHTML();
				return $rs;
				//$resultString .= $this->getHomePageString();
				//return $resultString;
			}
	
			if ( !$has_result && preg_match('/^add(\/?)$/', $REQUESTURIPATH) ) {
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/form/add.php');
				$user_add = new User_Add();
			
				$this->template->assert('main', $user_add->main());
				
				$work_subcontroller='add';
				$has_result=true;
				
				/*
				$this->template->render();
				$rs = $this->template->toHTML();
				return $rs;*/
			}
	
			
	
			if ( !$has_result && preg_match('/^ipotekaorder/', $REQUESTURIPATH) ) {
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/form/ipoteka.php');
				$ipoteka_order = new Ipoteka_Order_Form();
	
				$this->template->assert('main', $ipoteka_order->main());
				
				$work_subcontroller='ipotekaorder';
				$has_result=true;
			}
	
			if ( !$has_result && preg_match('/^contactus/', $REQUESTURIPATH) ) {
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/form/contactus.php');
				$contactus_form = new contactus_Form();
	
				$this->template->assert('main', $contactus_form->main());
				
				$work_subcontroller='contactus';
				$has_result=true;
				
				/*$this->template->render();
				$rs = $this->template->toHTML();
				return $rs;*/
			}
	
	
			if ( !$has_result && preg_match('/^land\//', $REQUESTURIPATH) ) {
				require_once('lib/admin/land/land_manager.php');
				require_once('lib/frontend/land/land_front.php');
				$land_front = new Land_Front();
				 
				$this->template->assert('main', $land_front->main());
				$this->template->assert('search_form', $land_front->getSearchForm());
	
				$this->template->render();
				$rs = $this->template->toHTML();
				return $rs;
			}
		
			
			if(!$has_result){
				$apps_processor->run_frontend();
				
				if ( count($apps_processor->get_executed_apps()) > 0 ) {
					$work_subcontroller='apps';
					$has_result=true;
				
					/*$this->template->render();
					 $rs = $this->template->toHTML();
					return $rs;*/
				}
			}
			
			
			if ( !$has_result && preg_match('/^account/', $REQUESTURIPATH) ) {
				$this->template->assert('right_column', '');
				$this->template->assert('is_account', '1');
				$this->template->assert('search_form_template', '');
	
				//return;
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
				$Account = new Account;
	
				if ( $Account->get_user_id() > 0 ) {
					$company_profile = $Account->get_company_profile($Account->get_user_id());
					$this->template->assert('company', $company_profile);
				}
	
	
				$this->template->assert('breadcrumbs',
						$this->get_breadcrumbs(
								array(
										'<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>',
										'<a href="'.$folder.'/account/">Личный кабинет</a>'
								)));
	
				if ( preg_match('/^account\/profile/', $REQUESTURIPATH) ) {
					require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/profile.php');
					require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/profile_using_model.php');
					$profile = new User_Profile_Model();
					$this->template->assert('breadcrumbs',
							$this->get_breadcrumbs(
									array(
											'<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>',
											'<a href="'.$folder.'/account/">Личный кабинет</a>',
											'<a href="'.$folder.'/account/profile/">Профиль</a>'
									)));
					
					$this->template->assert('main', $profile->main());
				} elseif ( preg_match('/^account\/balance/', $REQUESTURIPATH) ) {
	
					$this->template->assert('breadcrumbs',
							$this->get_breadcrumbs(
									array(
											'<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>',
											'<a href="'.$folder.'/account/">Личный кабинет</a>',
											'<a href="'.$folder.'/account/balance/">Баланс</a>'
									)));
	
					$this->template->assert('main', $Account->main());
				} elseif ( preg_match('/^account\/user/', $REQUESTURIPATH) ) {
					if ( $this->getConfigValue('apps.company.enable') ) {
						$this->template->assert('breadcrumbs',
								$this->get_breadcrumbs(
										array(
												'<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>',
												'<a href="'.$folder.'/account/">Личный кабинет</a>',
												'<a href="'.$folder.'/account/user/">Риелторы</a>'
										)));
	
						require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
						require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/user/user_company_manager.php');
						$user_company_manager = new User_Company_Manager();
						$this->template->assert('main', $user_company_manager->frontend_main());
					}
	
				} else {
	
					$this->template->assert('breadcrumbs',
							$this->get_breadcrumbs(
									array(
											'<a href="'.$folder.'/">'.Multilanguage::_('L_HOME').'</a>',
											'<a href="'.$folder.'/account/">Личный кабинет</a>',
											'<a href="'.$folder.'/account/data/">Мои объявления</a>'
									)));
	
					if(preg_match('/add/', $REQUESTURIPATH)){
						require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_admin.php');
						require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_editor.php');
						require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/data/user_data.php');
						$user_data_manager = new User_Data_Manager();
						$this->template->assert('main', $user_data_manager->add());
					}else{
						require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_admin.php');
						require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_editor.php');
						require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/data/user_data.php');
						$user_data_manager = new User_Data_Manager();
						$this->template->assert('main', $user_data_manager->main());
					}
	
				} 
				
				$work_subcontroller='account';
				$has_result=true;
				/*
				$this->template->render();
				$rs = $this->template->toHTML();
				return $rs;*/
			}
			
			if(!$has_result && $this->isRealtyDetected($REQUESTURIPATH)){
				$work_subcontroller='realtyview';
				$has_result=true;
			}
			
		
			
		if(preg_match('/^user(\d+).html/', $REQUESTURIPATH, $matches)){
			$this->setRequestValue('user_id', (int)$matches[1]);
			$this->template->assert('main', '<p><br></p>'.$this->grid_adv());
			$work_subcontroller='realtygrid';
			$has_result=true;
		} elseif( $this->getRequestValue('do') == 'news' ) {
			$this->template->assert('main', $this->viewNews( $this->getRequestValue('news_id') ));
		} elseif( $this->getRequestValue('do') == 'buy' ) {
			$this->template->assert('main', $this->processAdvancedForm('buy'));
		} elseif( $this->getRequestValue('do') == 'rent' ) {
			$this->template->assert('main', $this->processAdvancedForm('rent'));
		} elseif ( $this->getRequestValue('view') != '' ) {
			$this->template->assert('main', $this->getPage($this->getRequestValue('view')));
		}  elseif(!$has_result && $REQUESTURIPATH=='find'){
			$work_subcontroller='find';
			$has_result=true;
		} else {
			if($this->getConfigValue('apps.realtypro.enable')!=1){
				//$this->template->assert('_layout', 'layout_full.tpl');
				if(!$has_result){
					if($REQUESTURIPATH=='' && empty($_GET)){
						$work_subcontroller='home';
						if('classic'==$this->getConfigValue('template.realia.homepagetype')){
							$work_subcontroller='realtygrid';
						}
					}else{
						$work_subcontroller='realtygrid';
					}
					
					$this->template->assert('main', '<p><br></p>'.$this->grid_adv());
				}
				
			}
	
		}
		
		if('slider'==$this->getConfigValue('template.realia.homepagetype')){
			$this->template->assert('homepage_type', 'slider');
		}elseif('carousel'==$this->getConfigValue('template.realia.homepagetype')){
			$this->template->assert('homepage_type', 'carousel');
		}elseif('search'==$this->getConfigValue('template.realia.homepagetype')){
			$this->template->assert('homepage_type', 'search');
		}else{
			$this->template->assert('homepage_type', 'slider');
		}
		
		
		//echo $work_subcontroller;
		//var_dump($work_subcontroller);
		if($work_subcontroller!=='' && isset($layouts[$work_subcontroller])){
			$this->template->assert('_layout', $layouts[$work_subcontroller]);
		}else{
			$this->template->assert('_layout', $layouts['_default']);
		}
		
		$this->template->render();
		$rs = $this->template->toHTML();
		return $rs;
	}
	
	function grid_vip_right () {
		$grid_constructor=$this->_getGridConstructor();
		$params['vip'] = '1';
		$url_params=$this->_detectUrlParams($server_request_uri);
		$params['topic_id'] = $url_params['topic_id'];
		
		$grid_constructor->vip_right($params);
		
	}
	
	private function getTemplateMenu(){
		$DBC=DBC::getInstance();
	
		$additional_menu=array();
		$stmt=$DBC->query('SELECT name, url FROM '.DB_PREFIX.'_menu_structure WHERE menu_id=(SELECT menu_id FROM '.DB_PREFIX.'_menu WHERE tag=? LIMIT 1) ORDER BY sort_order ASC', array('navigation_menu'));
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$additional_menu[]=$ar;
			}
		}
		require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/realia/main/realia_menu_decorator.php');
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_implements.php');
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
		$SM=new Structure_Manager();
		$structure=$SM->loadCategoryStructure($this->getConfigValue('use_topic_publish_status'));
		/*
		$structure['catalog'][]=array('url'=>'#', 'name'=>"О нас");
		$indexes=array_keys($structure['catalog']);
		$last_index=$indexes[count($indexes)-1];
		$structure['childs'][0][]=$last_index;
		*/
		return Realia_Menu_Decorator::getMenu($structure);
			
	}
	
	private function getNewest(){
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
		$grid_constructor = new Grid_Constructor();
		$params['order'] = 'date_added';
		$params['asc'] = 'desc';
		$params['page_limit']=3;
		$params['page']=1;
		$res = $grid_constructor->get_sitebill_adv_ext( $params, false, false );
		//echo count($res);
		//print_r($res);
		$this->template->assign('new_grid_items', $res);
	}
	
	private function getAgents(){
		$ret=array();
		$DBC=DBC::getInstance();
		$query='SELECT * FROM '.DB_PREFIX.'_user WHERE group_id<>4 AND login<>\'_unregistered\' ORDER BY RAND() LIMIT 3';
		$stmt=$DBC->query($query);
		
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ret[]=$ar;
			}
		}
		$this->template->assign('agentslist_items', $ret);
	}
	
	function isRealtyDetected($requesturi){
		$result=false;
		$unknown_address=false;
		
		if(!$result && 1==$this->getConfigValue('apps.seo.data_alias_enable')){
			$url_string_parts=explode('/',$requesturi);
			if(count($url_string_parts)>0){
				$possible_alias=$url_string_parts[count($url_string_parts)-1];
		
				$possible_alias=preg_replace('/[^A-Za-z0-9_-]/','',urldecode($possible_alias));
				if($possible_alias!=''){
					$q="SELECT id FROM ".DB_PREFIX."_data WHERE translit_alias='".$possible_alias."'";
					$this->db->exec($q);
					if($this->db->success){
						$this->db->fetch_assoc();
						if((int)$this->db->row['id']>0){
							$realty_id=(int)$this->db->row['id'];
							$this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
							/*require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
							require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/view/local_kvartira_view.php');
							$kvartira_view = new Local_Kvartira_View();
							*/
							require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
							$kvartira_view = new Kvartira_View();
							
							if($html=$kvartira_view->main($realty_id)){
								$this->template->assert('main', $kvartira_view->main($realty_id));
								$result=true;
							}
						}
					}
				}
			}
		}
		
		
		
		if ( !$result && preg_match('/realty/', $requesturi) ) {
			
			if(1==$this->getConfigValue('apps.seo.level_enable') && preg_match('/^realty/', $requesturi)){
				
				$realty_id = $this->getIDfromURI($requesturi);
				//echo 'realty_id = '.$realty_id;
				if($realty_id){
					$query='SELECT topic_id FROM '.DB_PREFIX.'_data WHERE id='.$realty_id;
					$this->db->exec($query);
					$this->db->fetch_assoc();
					$topic_id=$this->db->row['topic_id'];
					//echo $topic_id;
					require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
					$Structure_Manager = new Structure_Manager();
					$category_structure = $Structure_Manager->loadCategoryStructure();
						
					if($category_structure['catalog'][$topic_id]['url']!=''){
						$parent_category_url=$category_structure['catalog'][$topic_id]['url'].'/';
					}else{
						$parent_category_url='';
					}
						
					if(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
						$new_location=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$realty_id.'.html';
					}else{
						$new_location=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$realty_id;
					}
					header('HTTP/1.1 301 Moved Permanently');
					header('Location: '.$new_location);
					exit();
				}/*else{
					$unknown_address=true;
					header("Status: 404 Not Found");
					$this->template->assign('title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
					$this->template->assign('meta_title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
					$this->template->assign('error_message', '<h1>'.Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND').'</h1>');
					$this->template->assign('main_file_tpl', 'error_message.tpl');
				}*/
		
			}elseif(1==$this->getConfigValue('apps.seo.level_enable') && !preg_match('/^realty/', $requesturi)){
				
				$realty_id = $this->getIDfromURI($requesturi);
				
				/*require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/view/local_kvartira_view.php');
				$kvartira_view = new Local_Kvartira_View();
				*/
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
				$kvartira_view = new Kvartira_View();
				$html=$kvartira_view->main($realty_id);
				if($html){
					$this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
					$this->template->assert('main', $html);
					$result=true;
				}
			} elseif(0==$this->getConfigValue('apps.seo.level_enable') && preg_match('/^realty/', $requesturi)){
				$realty_id = $this->getIDfromURI($_SERVER['REQUEST_URI']);
				
				/*require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/view/local_kvartira_view.php');
				$kvartira_view = new Local_Kvartira_View();
				*/
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
				$kvartira_view = new Kvartira_View();
				
				$html=$kvartira_view->main($realty_id);
				if($html){
					$this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
					$this->template->assert('main', $html);
					$result=true;
				}
			}/*else {
				$unknown_address=true;
				header("Status: 404 Not Found");
				$this->template->assign('title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
				$this->template->assign('meta_title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
				$this->template->assign('error_message', '<h1>'.Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND').'</h1>');
				$this->template->assign('main_file_tpl', 'error_message.tpl');
			}*/
		}
		return $result;
	}
	
	protected function setGridViewType(){
		if(in_array($this->getRequestValue('grid_type'),array('thumbs', 'list', 'map'))){
			$_SESSION['grid_type']=$this->getRequestValue('grid_type');
		}else{
			if(!isset($_SESSION['grid_type'])){
				if ( $this->getConfigValue('grid_type') != '' ) {
					$_SESSION['grid_type']=$this->getConfigValue('grid_type');
				} else {
					$_SESSION['grid_type']='list';
				}
			}
		}
	}
	function check_local_config () {
		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
		$config_admin = new config_admin();
	
		if ( !$config_admin->check_config_item('grid_type') ) {
			$config_admin->addParamToConfig('grid_type','list','Тип списка объявлений (list - обычная таблица, thumbs - div-блоки');
		}
		
		if ( !$config_admin->check_config_item('template.realia.homepagetype') ) {
			$config_admin->addParamToConfig('template.realia.homepagetype','classic','Тип главной страницы (classic|slider|carousel|search)');
		}
		
		if ( !$config_admin->check_config_item('vip_cost') ) {
			$config_admin->addParamToConfig('vip_cost','100','Цена VIP-объявления за 1 день');
			$query = "alter table ".DB_PREFIX."_data add column vip_status_end int(11) not null default 0";
			$this->db->exec($query);
			if ( !$this->db->success ) {
				//echo $this->db->error.'<br>';
			}
		}
		
		if ( !$config_admin->check_config_item('premium_cost') ) {
			$config_admin->addParamToConfig('premium_cost','100','Цена Премиум-объявления за 1 день');
			$query = "alter table ".DB_PREFIX."_data add column premium_status_end int(11) not null default 0";
			$this->db->exec($query);
			if ( !$this->db->success ) {
				//echo $this->db->error.'<br>';
			}
		
		}
		
		if ( !$config_admin->check_config_item('bold_cost') ) {
			$config_admin->addParamToConfig('bold_cost','100','Цена выделения объявления за 1 день');
			$query = "alter table ".DB_PREFIX."_data add column bold_status_end int(11) not null default 0";
			$this->db->exec($query);
			if ( !$this->db->success ) {
				//echo $this->db->error.'<br>';
			}
		
		}
		
		if ( !$config_admin->check_config_item('vip_rotator_number') ) {
			$config_admin->addParamToConfig('vip_rotator_number','5','Количество VIP-объявлений в колонке');
		}
		
		if ( !$config_admin->check_config_item('ups_price') ) {
			$config_admin->addParamToConfig('ups_price','400','(Цена одного поднятия');
		}
	}
}
?>