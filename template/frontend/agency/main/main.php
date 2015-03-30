<?php
class frontend_main extends SiteBill_Krascap {
	
	function local_apps_processor () {
		$this->apps_dir = SITEBILL_DOCUMENT_ROOT.'/apps';
		
		if ( preg_match('/simpleeditor/', $_SERVER['REQUEST_URI']) ) {
			$this->run_apps('fasteditor');
		} elseif ( preg_match('/freeorder/', $_SERVER['REQUEST_URI']) ) {
			$this->run_apps('freeorder');
		} elseif ( $this->run_apps('page') ) {
			
		} else {
			$this->run_apps('realtypro');
		}
	}
	
	/**
	 * Set executed apps
	 * @param string $apps_name
	 */
	private function set_executed_apps ( $apps_name ) {
		$this->apps_executed[] = $apps_name;
	}
	
	/**
	 * Get executed apps
	 * @return Array
	 */
	function get_executed_apps () {
		return $this->apps_executed;
	}
	
	
	function run_apps ( $app_dir ) {
		if ( is_dir($this->apps_dir.'/'.$app_dir) and !preg_match('/\./', $app_dir) ) {
			if ( is_file($this->apps_dir.'/'.$app_dir.'/site/site.php') ) {
				require_once ($this->apps_dir.'/'.$app_dir.'/admin/admin.php');
				require_once ($this->apps_dir.'/'.$app_dir.'/site/site.php');
				$app_class_name = $app_dir.'_site';
				//echo $app_class_name.'<br>';
				$app_class_inst = new $app_class_name;
				if ( $app_class_inst->frontend() ) {
					$this->set_executed_apps($app_class_name);
					//closedir($dh);
					return true;
				}
			}
		}
		return false;
	}
	
	function check_local_config () {
		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
		$config_admin = new config_admin();
	
		if ( !$config_admin->check_config_item('grid_type') ) {
			$config_admin->addParamToConfig('grid_type','list','Тип списка объявлений (list - обычная таблица, thumbs - div-блоки');
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
	
	
	
	
	/**
	 * Main
	 * @param void
	 * @return string
	 */
	function main () {
		$this->check_local_config();
		
		$layouts=array(
			'_default'=>'layout_basic.tpl',
			'apps_userdata_mini'=>'layout_wide.tpl',
		);
		
		/*require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/sitebill_includer.php');
			$Sitebill_Includer=Sitebill_Includer::getInstance();
			$Sitebill_Includer->addCss('/template/frontend/agency/css/style.css');
			$Sitebill_Includer->addCss('/template/frontend/agency/css/style3.css');
			*/
		/*if(!preg_match('/\/login/', $_SERVER['REQUEST_URI']) && (int)$_SESSION['user_id']==0){
			header('location:'.SITEBILL_MAIN_URL.'/login/');
		}*/
		
		
		
		$work_subcontroller='';
		$has_result=false;
		$undetected_url=false;
		
			
		global $__site_title, $folder, $smarty;
		
		
		$REQUESTURIPATH=Sitebill::getClearRequestURI();
		if($this->getConfigValue('use_google_map')){
			$this->template->assign('map_type', 'google');
		}else{
			$this->template->assign('map_type', 'yandex');
		}
			
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
		$apps_processor = new Apps_Processor();
		$apps_processor->run_preload();
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
		$Structure_Manager = new Structure_Manager();
	
		$this->template->assert('template_vars_logo', $this->getConfigValue('template.'.$this->getConfigValue('theme').'.logo'));
		$this->template->assert('current_theme_name', $this->getConfigValue('theme'));
		$this->template->assert('allow_register_account', $this->getConfigValue('allow_register_account'));
		
		$result=$this->_detectUrlParams($_SERVER['REQUEST_URI']);
		
		if($result['topic_id']/* && !is_array($result['topic_id'])*/){
			$this->setRequestValue('topic_id', $result['topic_id']);
		}
		if($result['city_id']){
			$this->setRequestValue('city_id', $result['city_id']);
		}
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/search/kvartira_search.php');
		$kvartira_search_form = new Kvartira_Search_Form();
		$kvartira_search_form->main();
		$this->template->assert('search_form_template', 'search_form.tpl');
		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/upper/upper.xml') ) {
		    $this->template->assert('show_upper', 'true');
		}
	
		if ( $this->getConfigValue('menu_type') == 'purecss' ) {
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/menu/purecssmenu.php');
			$purecssmenu = new PureCSS_Menu();
			$this->template->assert('slide_menu', $purecssmenu->get_menu());
		} elseif ( $this->getConfigValue('menu_type') == 'onelevel' ) {
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/onelevelmenu/lib/onelevelmenu.php');
			$onelevel = new Onelevel_Menu();
			$this->template->assert('slide_menu', $onelevel->get_menu());
		} else {
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/menu/slidemenu.php');
			$slidemenu = new Slide_Menu();
			$this->template->assert('slide_menu', $slidemenu->get_menu());
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
			
	
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php');
		$Login = new Login();
	
		if ( preg_match('/^logout(\/?)$/', $REQUESTURIPATH) ) {
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/logout.php');
			$Logout = new Logout;
			$Logout->main();
		}
			
		$this->template->assert('user_id', $Login->getSessionUserId());
		$this->template->assert('user_menu', $Login->getUserMenu());
			
		if ( $this->getConfigValue('allow_register_account') ) {
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/register_using_model.php');
			$Register = new Register_Using_Model();
			$rs1 = $Register->getRegisterFormElements();
			$this->template->assert('register_form_elements', $rs1);
		}
			
	/*
			if ( $_SERVER['REQUEST_URI'] == '/' ) {
				$this->grid_special();
			}
	*/
			//$this->grid_special_right();
			/*
			 if ( $_SERVER['REQUEST_URI'] == '/' and ($page_array = $this->getPageByURI('/index.html')) ) {
	
			} else {
			$page_array = $this->getPageByURI($_SERVER['REQUEST_URI']);
			}
			if ( $page_array ) {
			$this->template->assert('main', $page_array['body']);
			$this->template->assert('title', $page_array['title']);
			$this->template->assert('meta_keywords', $page_array['meta_keywords']);
			$this->template->assert('meta_description', $page_array['meta_description']);
			$this->template->render();
			$rs = $this->template->toHTML();
			return $rs;
			}
			*/
			
	
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
		
		
		if ( !$has_result && preg_match('/^myfavorites/', $REQUESTURIPATH) ) {
        	$this->template->assert('main', '<p><br></p>'.$this->grid_adv_favorites());
			$work_subcontroller='realtygrid';
        	$has_result=true;
        }


        if ( !$has_result && preg_match('/^robox/', $REQUESTURIPATH) ) {
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/robokassa/robokassa.php');
			$robokassa = new Robox();
			$rs = $robokassa->main();
			if ( preg_match('/^robox\/result/', $REQUESTURIPATH) ) {
				echo $rs;
				exit;
			}
			$this->template->assert('main', $rs);
			$work_subcontroller='robox';
			$has_result=true;
		}
		
		if ( !$has_result && preg_match('/^map(\/(\?.*)?)?$/', $REQUESTURIPATH) ) {
			$this->template->assert('main', '<p><br></p>'.$this->map());
			$work_subcontroller='realtygrid';
			$has_result=true;
		}
		if ( !$has_result && preg_match('/^map_full_screen(\/(\?.*)?)?$/', $REQUESTURIPATH) ) {
		    $this->template->assert('data', $this->map(true));
		    $work_subcontroller='realtygrid';
		    $has_result=true;
		}
			
		if ( $this->getConfigValue('apps.geodata.on_home') and $_SERVER['REQUEST_URI'] == SITEBILL_MAIN_URL.'/' ) {
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
			$work_subcontroller='login';
			$has_result=true;
		}

		if ( !$has_result && preg_match('/^add(\/?)$/', $REQUESTURIPATH) ) {
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/form/add.php');
			$user_add = new User_Add();
			$this->template->assert('main', $user_add->main());
			$work_subcontroller='add';
			$has_result=true;
		}

		

		if ( !$has_result && preg_match('/^ipotekaorder/', $REQUESTURIPATH) ) {
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/form/ipoteka.php');
			//require_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/main/form/local_ipoteka.php');
			//$ipoteka_order = new Local_Ipoteka_Order_Form();
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
				/*if(end($apps_processor->get_executed_apps())=='userdata_mini_site'){
					$work_subcontroller='apps_userdata_mini';
				}*/
				$has_result=true;
			}
		}
		
		/*if ( preg_match('/\/getrent\//', $_SERVER['REQUEST_URI']) ) {
			require_once (SITEBILL_DOCUMENT_ROOT.'/apps/getrent/admin/admin.php');
			require_once (SITEBILL_DOCUMENT_ROOT.'/apps/getrent/site/site.php');
			$getrent_site = new getrent_site();
			$getrent_site->frontend();
			$this->template->render();
			$rs = $this->template->toHTML();
			return $rs;
		}*/


		if ( !$has_result && preg_match('/^account/', $REQUESTURIPATH) ) {
			$this->template->assert('right_column', '');
			$this->template->assert('is_account', '1');
			$this->template->assert('search_form_template', '');

			//return;
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
			$Account = new Account;
			
			if(1==$this->getConfigValue('apps.upper.enable')){
				$user_id = $Account->get_user_id();
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/upper/admin/admin.php');
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/upper/site/site.php');
					
				$upper_site = new upper_site();
				$upps_left = $upper_site->checkUserLimits($user_id);
				$packs_left = $upper_site->checkUserPacks($user_id);
				
				$this->template->assert('apps_upper_enable', 1);
				$this->template->assert('upps_left', $upps_left);
				$this->template->assert('packs_left', $packs_left);
			}
			

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
		}
		
		if(!$has_result && $this->isRealtyDetected($REQUESTURIPATH)){
			$work_subcontroller='realtyview';
			$has_result=true;
		}
			
		
        
		if(!$has_result && preg_match('/user(\d+).html/', $_SERVER['REQUEST_URI'], $matches)){
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
		} else {
			if($this->getConfigValue('apps.realtypro.enable')!=1){
				if(!$has_result){
					$work_subcontroller='realtygrid';
					/*require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtydata/site/site.php';
					$RD=new realtydata_site();
					$this->template->assert('main', '<p><br></p>'.$RD->grid('html'));*/
					$this->template->assert('main', '<p><br></p>'.$this->grid_adv());
				}
			}
		}
		
		if($work_subcontroller!=='' && isset($layouts[$work_subcontroller])){
			
			$this->template->assert('_layout', $layouts[$work_subcontroller]);
		}else{
			$this->template->assert('_layout', $layouts['_default']);
		}
		/*if(DEBUG_ENABLED){
			$this->template->assert('_profiler', Debugger::formatedMessagesExt());
		}*/		$this->template->render();
		$rs = $this->template->toHTML();
		return $rs;
	}
	
	function grid_vip_right () {
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
		$grid_constructor = new Grid_Constructor();
		$params['vip'] = '1';
		$url_params=$this->_detectUrlParams($server_request_uri);
		$params['topic_id'] = $url_params['topic_id'];
		
		$grid_constructor->vip_right($params);
		
	}
	
	function isRealtyDetected($requesturi){
		$result=false;
		$unknown_address=false;
		$realty_id=false;
	
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
							require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
							$kvartira_view = new Kvartira_View();
							if($html=$kvartira_view->main($realty_id)){
								$this->template->assert('main', $html);
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
				}
	
			}elseif(1==$this->getConfigValue('apps.seo.level_enable') && !preg_match('/^realty/', $requesturi)){
	
				$realty_id = $this->getIDfromURI($requesturi);
	
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
	
	
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
				$kvartira_view = new Kvartira_View();
				$html=$kvartira_view->main($realty_id);
				if($html){
					$this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
					$this->template->assert('main', $html);
					$result=true;
				}
			}
		}
		
		return $result;
	}
	
	
}
?>