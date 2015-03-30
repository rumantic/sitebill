<?php
/**
 * Data manager
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class Data_Manager extends Object_Manager {
	protected $billing_mode_on=false;
    /**
     * Constructor
     */
    function Data_Manager() {
        $this->SiteBill();
        $this->table_name = 'data';
        $this->action = 'data';
        $this->app_title = Multilanguage::_('DATA_APP_NAME','system');
        $this->primary_key = 'id';
        $this->update_table();
	    
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
        $this->data_model = $data_model->get_kvartira_model($this->getConfigValue('ajax_form_in_admin'));
        
       
        if ( $this->getConfigValue('theme') == 'albostar' ) {
        	$this->data_model['data']['date_added']['type'] = 'safe_string';
        }
        $this->data_model['data']['user_id']['name'] = 'user_id';
		$this->data_model['data']['user_id']['primary_key_name'] = 'user_id';
		$this->data_model['data']['user_id']['primary_key_table'] = 'user';
		$this->data_model['data']['user_id']['title'] = Multilanguage::_('USER');
		$this->data_model['data']['user_id']['value_string'] = '';
		$this->data_model['data']['user_id']['value'] = 0;
		$this->data_model['data']['user_id']['length'] = 40;
		$this->data_model['data']['user_id']['type'] = 'select_by_query';
		if ( $this->getConfigValue('theme') == 'ipn' ) {
			$this->data_model['data']['user_id']['query'] = 'select * from '.DB_PREFIX.'_user  where group_id <> 3 order by fio';
		} else {
			$this->data_model['data']['user_id']['query'] = 'select * from '.DB_PREFIX.'_user order by fio';
		}
		$this->data_model['data']['user_id']['value_name'] = 'fio';
		$this->data_model['data']['user_id']['title_default'] = Multilanguage::_('L_CHOOSE_USER');
		$this->data_model['data']['user_id']['value_default'] = 0;
		$this->data_model['data']['user_id']['required'] = 'on';
		$this->data_model['data']['user_id']['unique'] = 'off';

		$this->data_model['data']['user_id']['value'] = $this->getAdminUserId();
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable')==1){
			$this->billing_mode_on=true;
		}
    }
    
    function structure_processor () {
    	if ( $this->getRequestValue('subdo') == 'sms' ) {
    		$form_data = $this->load_by_id($this->getRequestValue('id'));
    		if ( $form_data['tmp_password']['value'] == '' ) {
    			$form_data['tmp_password']['value'] = substr(md5(time()),1,6);
				
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
				$data_model = new Data_Model();
				$DBC=DBC::getInstance();
				$query = $data_model->get_edit_query(DB_PREFIX.'_'.$this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data);
				$DBC->query($query);
			}

    		$body=$this->getConfigValue('apps.fasteditor.sms_send_password_text');
    		
    		$body=str_replace('{password}',$form_data['tmp_password']['value'],$body);
    		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/sms/admin/admin.php');
    		$SMSSender=new sms_admin();
    		if($SMSSender->send($form_data['phone']['value'], $body)){
    			$rs=Multilanguage::_('MESSAGE_SUCCESS_NOTIFICATION','system').' '.$body;
    		}else{
    			$rs=Multilanguage::_('MESSAGE_ERROR_NOTIFICATION','system');
    		}
    		
    		return $rs;
    	}
    }
    
    function update_table () {
        if ( !$this->getConfigValue('update1') ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
            $config_manager = new config_admin();
            $DBC=DBC::getInstance();
            $query = array();
            $query[] = "alter table ".DB_PREFIX."_data add column planning text not null default ''";
            $query[] = "alter table ".DB_PREFIX."_data add column dom text not null default ''";
            $query[] = "alter table ".DB_PREFIX."_data add column flat_number text not null default ''";
            $query[] = "alter table ".DB_PREFIX."_data add column owner text not null default ''";
            $query[] = "alter table ".DB_PREFIX."_data add column source text not null default ''";
            $query[] = "alter table ".DB_PREFIX."_data add column adv_date text not null default ''";
            $query[] = "alter table ".DB_PREFIX."_data add column more1 text not null default ''";
            $query[] = "alter table ".DB_PREFIX."_data add column more2 text not null default ''";
            $query[] = "alter table ".DB_PREFIX."_data add column more3 text not null default ''";
            $query[] = "alter table ".DB_PREFIX."_data add column youtube text not null default ''";
            foreach ( $query as $i => $q ) {
                $DBC->query($q);
            }
            $config_manager->addParamToConfig('update1', '1', 'update1');
        }
    }
    
    /**
     * Get count
     */
    function get_count ( $active ) {
    	$DBC=DBC::getInstance();
    	if($active=='vip'){
    		$query = 'SELECT COUNT(id) AS total FROM '.DB_PREFIX.'_data WHERE vip_status_end<>0 AND '.DB_PREFIX.'_data.vip_status_end >= \''.time().'\'';
    	}elseif($active=='premium'){
    		$query = 'SELECT COUNT(id) AS total FROM '.DB_PREFIX.'_data WHERE premium_status_end<>0 AND '.DB_PREFIX.'_data.premium_status_end >= \''.time().'\'';
    	}elseif($active=='bold'){
    		$query = 'SELECT COUNT(id) AS total FROM '.DB_PREFIX.'_data WHERE bold_status_end<>0 AND '.DB_PREFIX.'_data.bold_status_end >= \''.time().'\'';
    	}elseif ( $active == 'all' ) {
            $query = "select count(id) as total from ".DB_PREFIX."_data";
        } elseif ( $active == 'notactive' ){
            $query = "select count(id) as total from ".DB_PREFIX."_data where active=0";
        } elseif( $active == 'hot' ){
        	$query = "select count(id) as total from ".DB_PREFIX."_data where hot=1";
        }else {
            $query = "select count(id) as total from ".DB_PREFIX."_data where active=1";
        }
        $stmt=$DBC->query($query);
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	return $ar['total'];
        }
        return 0;
    }
    
	/**
	 * Get top menu
	 * @param void 
	 * @return string
	 */
	function getTopMenu () {
	    global $smarty;
	    if($this->billing_mode_on){
	    	$smarty->assign('billing_mode_on', 1);
	    }
	    if ( file_exists(SITEBILL_DOCUMENT_ROOT."/apps/admin/admin/template/data_top_menu.tpl.html") ) {
	        $smarty->assign('user_select_box', $this->getUserSelectBox());
	        $smarty->assign('active_items_count', $this->get_count(1));
	        $smarty->assign('notactive_items_count', $this->get_count('notactive'));
	        if($this->billing_mode_on){
	        	$billing_mode_on_counts=array();
	        	$billing_mode_on_counts['vip']=$this->get_count('vip');
	        	$billing_mode_on_counts['premium']=$this->get_count('premium');
	        	$billing_mode_on_counts['bold']=$this->get_count('bold');
	        	$smarty->assign('billing_mode_on_counts', $billing_mode_on_counts);
	        	$billing_mode_on_statuses['vip']=(int)$this->getRequestValue('vip_status');
	        	$billing_mode_on_statuses['premium']=(int)$this->getRequestValue('premium_status');
	        	$billing_mode_on_statuses['bold']=(int)$this->getRequestValue('bold_status');
	        	$smarty->assign('billing_mode_on_statuses', $billing_mode_on_statuses);
	        }else{
	        	$smarty->assign('hot_items_count', $this->get_count('hot'));
	        }
	        
	        $smarty->assign('all_items_count', $this->get_count('all'));
	        
	        $smarty->assign('active', $this->getRequestValue('active'));
	        $smarty->assign('hot', $this->getRequestValue('hot'));
	         
	         
	        $rs = $smarty->fetch( SITEBILL_DOCUMENT_ROOT."/apps/admin/admin/template/data_top_menu.tpl.html" );
	    } else {
	        $rs = '';
	        $rs .= '<table border="0">';
	        $rs .= '<tr>';
	        $rs .= '<td>';
	        $rs .= '<a href="?action='.$this->action.'&do=new" class="btn btn-primary">'.Multilanguage::_('L_ADD_RECORD_BUTTON').'</a>';
	        $rs .= '</td>';
	        $rs .= '<td>';
	        $rs .= '&nbsp;&nbsp;&nbsp;&nbsp;';
	        if ( $this->getRequestValue('active') == 1 ) {
	        	$rs .= '<b>'.Multilanguage::_('ACTIVE_ITEMS','system').' ('.$this->get_count(1).')</b> | ';
	        } else {
	        	$rs .= '<a href="?action='.$this->action.'&active=1">'.Multilanguage::_('ACTIVE_ITEMS','system').' ('.$this->get_count(1).')</a> | ';
	        }
	        if ( $this->getRequestValue('active') == 'notactive' ) {
	        	$rs .= '<b>'.Multilanguage::_('NOTACTIVE_ITEMS','system').' ('.$this->get_count('notactive').')</b> | ';
	        } else {
	        	$rs .= '<a href="?action='.$this->action.'&active=notactive">'.Multilanguage::_('NOTACTIVE_ITEMS','system').' ('.$this->get_count('notactive').')</a> | ';
	        }
	        if ( $this->getRequestValue('hot') == 1 ) {
	        	$rs .= '<b>'.($this->getConfigValue('theme')=='albostar' ? Multilanguage::_('EDITED_ITEMS','system') : Multilanguage::_('HOT_ITEMS','system')).' ('.$this->get_count('hot').')</b> | ';
	        } else {
	        	$rs .= '<a href="?action='.$this->action.'&hot=1">'.($this->getConfigValue('theme')=='albostar' ? Multilanguage::_('EDITED_ITEMS','system') : Multilanguage::_('HOT_ITEMS','system')).' ('.$this->get_count('hot').')</a> | ';
	        }
	         
	        if ( $this->getRequestValue('active') == '' AND $this->getRequestValue('hot') != 1) {
	        	$rs .= '<b>Все ('.$this->get_count('all').')</b>  ';
	        } else {
	        	$rs .= '<a href="?action='.$this->action.'">Все ('.$this->get_count('all').')</a>  ';
	        }
	         
	        $rs .= '</td>';
	        $rs .= '<td>';
	        $rs .= ''.$this->getAdditionalSearchForm();
	        $rs .= '</td>';
	        $rs .= '</tr>';
	        $rs .= '</table>';
	    }
	    return $rs;
	}
    
    /**
     * Get data grid
     * @param int $user_id
     * @param int $topic_id
     * @return string
     */
    function get_data_grid ($user_id, $topic_id) {
        global $smarty;
        
                
        if($this->getConfigValue('apps.geodata.enable')){
        	$smarty->assign('app_geodata_mode', 1);
        }else{
        	$smarty->assign('app_geodata_mode', 0);
        }
        
        require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
        $grid_constructor = new Grid_Constructor();
        
        $params['user_id'] = $this->getRequestValue('user_id');
        $params['topic_id'] = $this->getRequestValue('topic_id');
        $params['order'] = $this->getRequestValue('order');
        $params['region_id'] = $this->getRequestValue('region_id');
        $params['city_id'] = $this->getRequestValue('city_id');
        $params['district_id'] = $this->getRequestValue('district_id');
        $params['metro_id'] = $this->getRequestValue('metro_id');
        $params['street_id'] = $this->getRequestValue('street_id');
        $params['page'] = $this->getRequestValue('page');
        $params['asc'] = $this->getRequestValue('asc');
        $params['price'] = $this->getRequestValue('price');
        $params['active'] = $this->getRequestValue('active');
        $params['hot'] = $this->getRequestValue('hot');
		$params['id'] = (int)$this->getRequestValue('srch_id');
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable')==1){
			$params['vip_status'] = (int)$this->getRequestValue('vip_status');
			$params['premium_status'] = (int)$this->getRequestValue('premium_status');
			$params['bold_status'] = (int)$this->getRequestValue('bold_status');
		}
		
		
		
		if(isset($this->data_model[$this->table_name]['uniq_id'])){
			$params['uniq_id'] = (int)$this->getRequestValue('uniq_id');
			$smarty->assign('show_uniq_id', 'true');
		}
		if($this->getRequestValue('srch_export_cian')=='on' || $this->getRequestValue('srch_export_cian')=='1'){
			$params['srch_export_cian'] = 1;
		}
		$params['srch_word'] = $this->getRequestValue('srch_word');
		$params['srch_phone'] = $this->getRequestValue('srch_phone');
		$params['srch_date_from'] = $this->getRequestValue('srch_date_from') ? $this->getRequestValue('srch_date_from') : 0;
		$params['srch_date_to'] = $this->getRequestValue('srch_date_to') ? $this->getRequestValue('srch_date_to') : 0;
        $params['admin'] = true;
        /*
        /* @TODO	 Удалить этот блок после удовлетворительного тестирования и перенести все в грид конструктор
        */ 
        
        if(1==$this->getConfigValue('use_new_realty_grid')){
        	$this->create_admin_grid($params);
        }else{
        	$grid_constructor->main($params);
        }
        
        
        
        
        
        $smarty->assign('admin', 1);
        $smarty->assign('topic_id', $params['topic_id']);
        if ( $this->getConfigValue('apps.fasteditor.enable') ) {
        	$smarty->assign('sms_enable', 'true');
        }
        if($this->getConfigValue('apps.realtypro.show_contact.enable')){
        	$smarty->assign('show_contacts_enable', 'true');
        }
		if($this->getConfigValue('show_up_icon')== 1 ){
        	$smarty->assign('show_up_icon', 'true');
        }
        
        
        if(1==$this->getConfigValue('use_new_realty_grid')){
        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php';
        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/gridmanager_admin.php';
        	$GMA=new gridmanager_admin();
        	$smarty->assign('grid_data_columns', $GMA->getGridColumns());
        	if(file_exists(SITEBILL_DOCUMENT_ROOT."/template/frontend/".$this->getConfigValue('theme')."/apps/admin/template/realty_grid_wdg.tpl")){
        		$html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT."/template/frontend/".$this->getConfigValue('theme')."/apps/admin/template/realty_grid_wdg.tpl");
        	}else{
        		$html = $smarty->fetch( SITEBILL_DOCUMENT_ROOT."/apps/admin/admin/template/realty_grid_wdg.tpl" );
        	}
			
        }else{
        	if(file_exists(SITEBILL_DOCUMENT_ROOT."/template/frontend/".$this->getConfigValue('theme')."/apps/admin/template/realty_grid.tpl")){
        		$html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT."/template/frontend/".$this->getConfigValue('theme')."/apps/admin/template/realty_grid.tpl");
        	}else{
				$html = $smarty->fetch( SITEBILL_DOCUMENT_ROOT."/apps/admin/admin/template/realty_grid.tpl" );
        	}
        }
       
        return $html;
    }
    
    function create_admin_grid ( $params ) {
    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
    	$grid_constructor = new Grid_Constructor();
    	
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	$Structure_Manager = new Structure_Manager();
    	$category_structure = $Structure_Manager->loadCategoryStructure();
    
    	$res = $this->get_sitebill_adv_ext_by_model( $params );
    	$this->template->assign('category_tree', $grid_constructor->get_category_tree( $params, $category_structure ) );
    	$this->template->assign('breadcrumbs', $grid_constructor->get_category_breadcrumbs( $params, $category_structure, SITEBILL_MAIN_URL ) );
    	$this->template->assign('search_params', json_encode($params) );
    	$this->template->assign('search_url', $_SERVER['REQUEST_URI'] );
    	//print_r(json_encode($params));
    
    	$grid_constructor->get_sales_grid($res);
    }
    
    /**
     * MUST be moved to Grid_Constructor
     */
    function get_sitebill_adv_ext_by_model( $params, $random = false ) {
    	 
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
    
    	//Подключать модель и проверять на наличие такого поля
    	if(isset($params['srch_export_cian']) && $params['srch_export_cian']==1){
    		$where_array[] = 're_data.export_cian=1';
    	}
    
    	if(isset($params['favorites']) && !empty($params['favorites'])){
    		$where_array[] = 're_data.id IN ('.implode(',',$params['favorites']).')';
    	}
    
    	if(isset($params['uniq_id']) && $params['uniq_id']!=0){
    		$where_array[] = 're_data.uniq_id='.$params['uniq_id'];
    	}
    
    	if(isset($params['optype'])){
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
    	////
    	if ( isset($params['price_pm']) && $params['price_pm'] != 0  ) {
    		$where_array[] = 're_data.price_pm  <= '.$params['price_pm'];
    	}
    	if ( isset($params['price_pm_min']) && $params['price_pm_min'] != 0  ) {
    		$where_array[] = 're_data.price_pm  >= '.$params['price_pm_min'];
    	}
    	//////
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
    	/*
    	 if(isset($params['metro_id']) && is_array($params['metro_id'])){
    	if(!empty($params['metro_id'])){
    	$where_array[] = 're_data.metro_id IN ('.implode(',',$params['metro_id']).')';
    	}
    	}elseif(isset($params['metro_id']) && (int)$params['metro_id']!=0){
    	$where_array[] = 're_data.metro_id = '.(int)$params['metro_id'];
    	}
    	*/
    	 
    	if ( isset($params['metro_id']) and $params['metro_id'] != 0  ) {
    		if(is_array($params['metro_id']) && !empty($params['metro_id'])){
    			$where_array[] = 're_data.metro_id IN ('.implode(',',$params['metro_id']).')';
    		}else{
    			$where_array[] = 're_data.metro_id = '.$params['metro_id'];
    		}
    
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
    
    	if(isset($params['not_first_floor']) && $params['not_first_floor']==1){
    		$where_array[]="(re_data.floor <> 1)";
    	}
    
    	if(isset($params['not_last_floor']) && $params['not_last_floor']==1){
    		$where_array[]="(re_data.floor <> re_data.floor_count)";
    	}
    
    
    	////////////////
    	if(isset($params['by_ipoteka']) && $params['by_ipoteka']==1){
    			
    	}
    	if(isset($params['status']) && $params['status']==1){
    
    	}
    	if(isset($params['nout_from_sale']) && $params['nout_from_sale']==1){
    
    	}
    	if(isset($params['nwith_null_params']) && $params['nwith_null_params']==1){
    
    	}
    	if(isset($params['new_only']) && $params['new_only']==1){
    
    	}
    	if(isset($params['is_balkony']) && $params['is_balkony']==1){
    
    	}
    	if(isset($params['is_sanitary']) && $params['is_sanitary']==1){
    
    	}
    	if(isset($params['live_square_min']) && $params['live_square_min']!=0){
    		$where_array[]="(re_data.square_live >= ".$params['live_square_min'].")";
    	}
    	if(isset($params['live_square_max']) && $params['live_square_max']!=0){
    		$where_array[]="(re_data.square_live <= ".$params['live_square_max'].")";
    	}
    	if(isset($params['kitchen_square_min']) && $params['kitchen_square_min']!=0){
    		$where_array[]="(re_data.square_kitchen >= ".$params['kitchen_square_min'].")";
    	}
    	if(isset($params['kitchen_square_max']) && $params['kitchen_square_max']!=0){
    		$where_array[]="(re_data.square_kitchen <= ".$params['kitchen_square_max'].")";
    	}
    
    	//////////////
    
    	if($params['floor_min']!=0 && $params['floor_max']!=0){
    		$where_array[]="(re_data.floor BETWEEN ".$params['floor_min']." AND ".$params['floor_max'].")";
    	}elseif($params['floor_min']!=0){
    		$where_array[]="(re_data.floor>=".$params['floor_min'].")";
    	}elseif($params['floor_max']!=0){
    		$where_array[]="(re_data.floor<=".$params['floor_max'].")";
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
    
    	if(isset($params['minbeds']) && (int)$params['minbeds']!=0){
    		$where_array[]="(re_data.bedrooms_count >= ".(int)$params['minbeds'].")";
    	}
    	if(isset($params['minbaths']) && (int)$params['minbaths']!=0){
    		$where_array[]="(re_data.bathrooms_count >=".(int)$params['minbaths'].")";
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
    		$order = "re_data.date_added DESC, re_data.price ASC, re_data.id DESC";
    	}
    
    	//echo $order.'<br />';
    
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
    
    	$DBC=DBC::getInstance();
    	$stmt=$DBC->query($query);
    
    	$total=0;
    	$this->db->exec($query);
    	if ( !$stmt ) {
    		echo 'ERROR <br>';
    	}else{
    		$ar=$DBC->fetch($stmt);
    		$total = $ar['total'];
    	}
    	
    	$this->grid_total = $total;
    	global $smarty;
    	$smarty->assign('_total_records', $total);
    
    
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
    		 
    	} else {
    		if ( $this->getConfigValue('currency_enable') ) {
    			$query = "select re_currency.code AS currency_code, re_currency.name AS currency_name, ((re_data.price*re_currency.course)/".$CM->getCourse(CURRENT_CURRENCY).") AS price_ue, re_data.*, re_topic.name as type_sh $add_select_value from re_data LEFT JOIN re_currency ON re_data.currency_id=re_currency.currency_id, re_topic $add_from_table $where_statement ORDER BY ".$order.($params['no_portions']==1 ? '' : " LIMIT ".$start.", ".$limit);
    		} else {
    			$query = "select re_data.*, re_topic.name as type_sh $add_select_value from re_data, re_topic $add_from_table $where_statement ORDER BY ".$order.($params['no_portions']==1 ? '' : " LIMIT ".$start.", ".$limit);
    		}
    	}
    	
    	$ra = array();
    	$stmt=$DBC->query($query);
    	if ( !$stmt ) {
    		echo 'ERROR <br>';
    	}else{
    		while ( $ar=$DBC->fetch($stmt) ) {
    			$ra[] = $ar;
    		}
    	}
    
    	
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/company/company.xml') && !empty($ra)){
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/account.php');
    		$Account = new Account;
    		foreach ($ra as $rk=>$rv){
    			$company_profile = $Account->get_company_profile($rv['user_id']);
    			$ra[$rk]['company'] = $company_profile['name']['value'];
    		}
    	}
    
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$params = array();
    	 
    	$_model=$data_model->get_kvartira_model();
    	$grid_geodata=array();
    	
    	
    	if(1==1){
    		
    	$ret=array();
    	
	    	foreach($ra as $r){
	    		$ret[]=SiteBill::modelSimplification($data_model->init_model_data_from_db('data', 'id', $r['id'], $_model['data'],true));
	    		//$ret[]=$data_model->init_model_data_from_db('data', 'id', $r['id'], $_model['data'],true);
	    	}
	    	 
	    	 
	    	if(!empty($ret)){
	    		foreach ( $ret as $item_id => $item_array ) {
	    			if( isset($item_array['geo']) && $item_array['geo']['value']['lat']!='' && $item_array['geo']['value']['lng']!=''){
	    				$grid_geodata[]=array(
	    						'lat'=>$item_array['geo']['value']['lat'],
	    						'lng'=>$item_array['geo']['value']['lng'],
	    						'id'=>$item_array['id']['value']
	    				);
	    			}
	    			$ret[$item_id]['date'] = date('d.m',strtotime($ret[$item_id]['date_added']['value']));
	    		
	    		}
	    	} 
	    	
	    	 
	    	return $ret;
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
    			$ra[$item_id]['user'] = $data_model->get_string_value_by_id('user', 'user_id', 'fio', $item_array['user_id'], true);
    			if ( $ra[$item_id]['user'] == '' ) {
    				$ra[$item_id]['user'] = $data_model->get_string_value_by_id('user', 'user_id', 'login', $item_array['user_id'], true);
    			}
    		}
    		if ( $item_array['currency_id'] > 0 ) {
    			$ra[$item_id]['currency'] = $data_model->get_string_value_by_id('currency', 'currency_id', 'code', $item_array['currency_id'], true);
    			//$ra[$item_id]['currency_name'] = $data_model->get_string_value_by_id('currency', 'currency_id', 'name', $item_array['currency_id'], true);
    		}
    
    		if(isset($item_array['optype']) && isset($_model['data']['optype']) && $_model['data']['optype']['type']=='select_box'){
    			$ra[$item_id]['optype']=$_model['data']['optype']['select_data'][(int)$item_array['optype']];
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
    		//echo $this->getConfigValue('apps.seo.data_alias_enable');
    		if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $ra[$item_id]['translit_alias']!=''){
    			$ra[$item_id]['href']=SITEBILL_MAIN_URL.'/'.$ra[$item_id]['parent_category_url'].$ra[$item_id]['translit_alias'];
    			//$ra[$item_id]['href']=SITEBILL_MAIN_URL.'/'.$ra[$item_id]['parent_category_url'].$this->getTranslitAlias($ra[$item_id]['city'],$ra[$item_id]['street'],$ra[$item_id]['number']);
    		}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
    			$ra[$item_id]['href']=SITEBILL_MAIN_URL.'/'.$ra[$item_id]['parent_category_url'].'realty'.$ra[$item_id]['id'].'.html';
    		}else{
    			$ra[$item_id]['href']=SITEBILL_MAIN_URL.'/'.$ra[$item_id]['parent_category_url'].'realty'.$ra[$item_id]['id'];
    		}
    
    	}
    	$this->template->assert('grid_geodata', json_encode($grid_geodata));
    	return $ra;
    }
    
    
    
    /**
     * Get form for edit or new record
     * @param array $form_data
     * @param string $do
     * @param int $language_id
     * @param string $button_title
     * @return string
     */
    function get_form ( $form_data=array(), $do = 'new', $language_id = 0, $button_title = '' ) {
    	$_SESSION['allow_disable_root_structure_select']=true;
    	global $smarty;
    	if($button_title==''){
    		$button_title = Multilanguage::_('L_TEXT_SAVE');
    	}
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	 
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
    	$form_generator = new Form_Generator();
    	
    	
    	$rs .= $this->get_ajax_functions();
    	
    	
    	
    	$topic_id=(int)$form_data['topic_id']['value'];
    	$current_id=(int)$form_data[$this->primary_key]['value'];
    	
    	if($topic_id!=0 && $current_id!=0){
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    		$Structure_Manager = new Structure_Manager();
    		$category_structure = $Structure_Manager->loadCategoryStructure();
    		 
    		if(1==$this->getConfigValue('apps.seo.level_enable')){
    			 
    			if($category_structure['catalog'][$topic_id]['url']!=''){
    				$parent_category_url=$category_structure['catalog'][$topic_id]['url'].'/';
    			}else{
    				$parent_category_url='';
    			}
    		}else{
    			$parent_category_url='';
    		}
    		if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $form_data['translit_alias']['value']!=''){
    			$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.$form_data['translit_alias']['value'];
    		}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
    			$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$current_id.'.html';
    		}else{
    			$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$current_id;
    		}
    		 
    		$rs .= '<div class="row"><a class="btn btn-success pull-right" href="'.$href.'" target="_blank">'.Multilanguage::_('L_SEE_AT_SITE').'</a></div>';
    		
    	}
    	
    	
    	
    	
    	if(1==$this->getConfigValue('apps.geodata.enable')){
    		$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/geodata/js/geodata.js"></script>';
    	}
    	$rs .= '<form method="post" class="form-horizontal" action="index.php" enctype="multipart/form-data">';
    	
    	if ( $this->getError() ) {
    		$smarty->assign('form_error',$form_generator->get_error_message_row($this->GetErrorMessage()));
    	}
    	
    	$el = $form_generator->compile_form_elements($form_data);
    	
    	if ( $do == 'new' ) {
    		$el['private'][]=array('html'=>'<input type="hidden" name="do" value="new_done" />');
    		$el['private'][]=array('html'=>'<input type="hidden" name="'.$this->primary_key.'" value="'.$this->getRequestValue($this->primary_key).'" />');
    	} else {
    		$el['private'][]=array('html'=>'<input type="hidden" name="do" value="edit_done" />');
    		$el['private'][]=array('html'=>'<input type="hidden" name="'.$this->primary_key.'" value="'.$form_data[$this->primary_key]['value'].'" />');
    	}
    	$el['private'][]=array('html'=>'<input type="hidden" name="action" value="'.$this->action.'">');
    	$el['private'][]=array('html'=>'<input type="hidden" name="language_id" value="'.$language_id.'">');
    	    	
    	$el['form_header']=$rs;
    	$el['form_footer']='</form>';
    	
    	if ( $do != 'new' ) {
    		//$el['controls']['apply']=array('html'=>'<button id="apply_changes">'.Multilanguage::_('L_TEXT_APPLY').'</button>');
    		$el['controls']['apply']=array('html'=>'<button id="apply_changes" class="btn btn-info">'.Multilanguage::_('L_TEXT_APPLY').'</button>');
    	}
    	//$el['controls']['submit']=array('html'=>'<input type="submit" name="submit" id="formsubmit" value="'.$button_title.'" />');
    	$el['controls']['submit']=array('html'=>'<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">'.$button_title.'</button>');
    	
    	
    	
    	$smarty->assign('form_elements',$el);
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data_admin.tpl')){
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data_admin.tpl';
		}elseif(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl')){
    		$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl';
    	}else{
    		$tpl_name=SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/data_form.tpl';
    	}
    	return $smarty->fetch($tpl_name);
    }
    
    
    function checkUniquety($form_data){
    	
    	if(isset($form_data['city_id']) && isset($form_data['street_id']) && isset($form_data['number'])){
    		$DBC=DBC::getInstance();
    		$q='SELECT COUNT(id) AS cnt
    		FROM '.DB_PREFIX.'_'.$this->table_name.'
    		WHERE city_id='.(int)$form_data['city_id']['value'].'
    		AND street_id='.(int)$form_data['street_id']['value'].'
    		AND number='.(int)$form_data['number']['value'];
    		$stmt=$DBC->query($q);
    		
    		if($stmt){
    			$ar=$DBC->fetch($stmt);
    			if($ar['cnt']>0){
    				$this->riseError('Такое объявление уже существует');
    				return FALSE;
    			}
    		}
    	}
    	return TRUE;
    }
    
	/**
	 * Edit data
	 * @param array $form_data form data
	 * @return boolean
	 */
	function edit_data ( $form_data ) {
		
		$need_send_message=0;
		if(isset($form_data['tmp_password']) && $form_data['tmp_password']['value']==''){
			$form_data['tmp_password']['value']=substr(md5(time()),1,6);
		}
		
		if(isset($form_data['price'])){
			$form_data['price']['value']=str_replace(' ', '', $form_data['price']['value']);
		}
		
		$DBC=DBC::getInstance();
		
		if($this->getConfigValue('apps.billing.enable')==1){
			if(isset($form_data['vip_status_end']) && isset($form_data['premium_status_end']) && isset($form_data['bold_status_end'])){
				$current_vip_status_end=0;
				$current_premium_status_end=0;
				$current_bold_status_end=0;
				$q='SELECT vip_status_end, premium_status_end, bold_status_end FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE `'.$this->primary_key.'`=?';
				$stmt=$DBC->query($q, array((int)$this->getRequestValue('id')));
				if($stmt){
					$ar=$DBC->fetch($stmt);
					$current_vip_status_end=(int)$ar['vip_status_end'];
					$current_premium_status_end=(int)$ar['premium_status_end'];
					$current_bold_status_end=(int)$ar['bold_status_end'];
				}
					
				$new_vip_date=$this->prepareVipStatsDateValue($current_vip_status_end,$form_data['vip_status_end']['value']);
				if($new_vip_date===FALSE){
					unset($form_data['vip_status_end']);
				}else{
					$form_data['vip_status_end']['value']=$new_vip_date;
				}
				$new_premium_date=$this->prepareVipStatsDateValue($current_premium_status_end,$form_data['premium_status_end']['value']);
				if($new_premium_date===FALSE){
					unset($form_data['premium_status_end']);
				}else{
					$form_data['premium_status_end']['value']=$new_premium_date;
				}
				$new_bold_date=$this->prepareVipStatsDateValue($current_bold_status_end,$form_data['bold_status_end']['value']);
				if($new_bold_date===FALSE){
					unset($form_data['bold_status_end']);
				}else{
					$form_data['bold_status_end']['value']=$new_bold_date;
				}
			}elseif(isset($form_data['vip_status_end'])){
				$current_vip_status_end=0;
				$q='SELECT vip_status_end FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE `'.$this->primary_key.'`=?';
				$stmt=$DBC->query($q, array((int)$this->getRequestValue('id')));
				if($stmt){
					$ar=$DBC->fetch($stmt);
					$current_vip_status_end=(int)$ar['vip_status_end'];
				}
					
				$new_vip_date=$this->prepareVipStatsDateValue($current_vip_status_end, $form_data['vip_status_end']['value']);
				if($new_vip_date===FALSE){
					unset($form_data['vip_status_end']);
				}else{
					$form_data['vip_status_end']['value']=$new_vip_date;
				}
			}elseif(isset($form_data['bold_status_end'])){
				$current_bold_status_end=0;
				$q='SELECT bold_status_end FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE `'.$this->primary_key.'`=?';
				$stmt=$DBC->query($q, array((int)$this->getRequestValue('id')));
				if($stmt){
					$ar=$DBC->fetch($stmt);
					$current_bold_status_end=(int)$ar['bold_status_end'];
				}
					
				$new_bold_date=$this->prepareVipStatsDateValue($current_bold_status_end, $form_data['bold_status_end']['value']);
				if($new_bold_date===FALSE){
					unset($form_data['bold_status_end']);
				}else{
					$form_data['bold_status_end']['value']=$new_bold_date;
				}
			}elseif(isset($form_data['premium_status_end'])){
				$current_premium_status_end=0;
				$q='SELECT premium_status_end FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE `'.$this->primary_key.'`=?';
				$stmt=$DBC->query($q, array((int)$this->getRequestValue('id')));
				if($stmt){
					$ar=$DBC->fetch($stmt);
					$current_premium_status_end=(int)$ar['premium_status_end'];
				}
					
				$new_premium_date=$this->prepareVipStatsDateValue($current_premium_status_end, $form_data['premium_status_end']['value']);
				if($new_premium_date===FALSE){
					unset($form_data['premium_status_end']);
				}else{
					$form_data['premium_status_end']['value']=$new_premium_date;
				}
			}
			
		}else{
			unset($form_data['premium_status_end']);
			unset($form_data['bold_status_end']);
			unset($form_data['vip_status_end']);
		}
		
		
		if($this->getConfigValue('notify_about_publishing')){
			$n_pass=$form_data['tmp_password']['value'];
			$n_id=$this->getRequestValue('id');
			$n_email=$form_data['email']['value'];
			$n_phone=$form_data['phone']['value'];
			$query='SELECT active, hot FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE `'.$this->primary_key.'`=?';
			$stmt=$DBC->query($query, array((int)$this->getRequestValue('id')));
			
			if($stmt){
				$ar=$DBC->fetch($stmt);
				$current_active_status=$ar['active'];
				$current_hot_status=$ar['hot'];
			}
			if($current_active_status==0 AND $form_data['active']['value']==1){
				$need_send_message=1;
			}
			if($current_hot_status==1 AND $form_data['hot']['value']==0){
				$need_send_message=1;
			}
		}
		
		
		
		$y_id='';
		if(isset($form_data['youtube'])){
	        if(strpos($form_data['youtube']['value'],'youtube.com')!==FALSE){
				$d=parse_url($form_data['youtube']['value']);
				if(isset($d['query'])){
					parse_str($d['query'],$a);
					$y_id=$a['v'];
				}
			}else{
			
				if(preg_match('/.*([-_A-Za-z0-9]+).*/',$form_data['youtube']['value'],$matches)){
					$y_id=$matches[0];
				}
			}
			$form_data['youtube']['value']=$y_id;
		}
		
		if(1==$this->getConfigValue('apps.geodata.try_encode') && 1==$this->getConfigValue('apps.geodata.enable')){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/geodata/admin/admin.php';
			$GA=new geodata_admin();
			$form_data=$GA->try_geocode($form_data);
		}
					
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();

	    /*
	    echo '<pre>';
	    print_r($form_data);
	    echo '</pre>';
	    */
	    
	    //$query = $data_model->get_edit_query(DB_PREFIX.'_'.$this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data);
	    $query = $data_model->get_edit_query(DB_PREFIX.'_'.$this->table_name, $this->primary_key, $form_data[$this->primary_key]['value'], $form_data);
	    //echo $query;
	    $stmt=$DBC->query($query);
	    /*if ( !$this->db->success ) {
	    	echo $this->db->error.'<br>';
	    }*/
	    
	    $imgs=array();
	     
	    foreach ($form_data as $form_item){
	    	if($form_item['type']=='uploads'){
	    		$ims=$this->appendUploads($this->table_name, $form_item, $this->primary_key, (int)$this->getRequestValue($this->primary_key));
	    		if(is_array($ims) && count($ims)>0){
	    			$imgs=array_merge($imgs, $ims);
	    		}
	    	}
	    }
	   	    
	    $ims=$this->editImageMulti('data', 'data', $this->primary_key, $this->getRequestValue('id'));
	    if(is_array($ims) && count($ims)>0){
	    	$imgs=array_merge($imgs, $ims);
	    }
	     
	    
	    
	    if(1==$this->getConfigValue('apps.seo.data_alias_enable') && isset($form_data['translit_alias']) && $form_data['translit_alias']['value']==''){
	    	$this->saveTranslitAlias($this->getRequestValue('id'));
	    }
		
		/*Send notify messages*/
	    if($need_send_message){
			
			if($n_email!=''){
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
				$SM = new Structure_Manager();
				$category_structure = $SM->loadCategoryStructure();
				if(1==$this->getConfigValue('apps.seo.data_alias_enable')){
					$query='SELECT translit_alias, topic_id FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE '.$this->primary_key.'=? LIMIT 1';
				}else{
					$query='SELECT topic_id FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE '.$this->primary_key.'=? LIMIT 1';
				}
				
				$stmt=$DBC->query($query, array($n_id));
				if($stmt){
					$seo_data=$DBC->fetch($stmt);
				}else{
					$seo_data=array();
				}
				
				
				if(1==$this->getConfigValue('apps.seo.level_enable')){
					if($category_structure['catalog'][$seo_data['topic_id']]['url']!=''){
						$parent_category_url=$category_structure['catalog'][$seo_data['topic_id']]['url'].'/';
					}else{
						$parent_category_url='';
					}
				}else{
					$parent_category_url='';
				}
				
				if(1==$this->getConfigValue('apps.seo.data_alias_enable') && $seo_data['translit_alias']!=''){
					$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.$seo_data['translit_alias'];
				}elseif(1==$this->getConfigValue('apps.seo.html_prefix_enable')){
					$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$n_id.'.html';
				}else{
					$href=SITEBILL_MAIN_URL.'/'.$parent_category_url.'realty'.$n_id;
				}
				
				
				$body=Multilanguage::_('YOUR_AD_PUBLISHED','system').'<br />';
				$body.=Multilanguage::_('AD_LINK','system').' <a href="http://'.$_SERVER['HTTP_HOST'].$href.'">http://'.$_SERVER['HTTP_HOST'].$href.'</a><br />';
	    		//$body.=Multilanguage::_('EDIT_LINK','system').' <a href="http://'.$_SERVER['HTTP_HOST'].'/simpleeditor/'.$n_id.'/">http://'.$_SERVER['HTTP_HOST'].'/simpleeditor/'.$n_id.'/</a><br />';
				//$body.=Multilanguage::_('EDIT_PASS','system').' <b>'.$n_pass.'</b><br />';
				//$body.='<a href="http://'.$_SERVER['HTTP_HOST'].'/uslugi/">'.Multilanguage::_('MORE_INFO','system').'</a><br />';
	    		
		    	/*require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/mailer/mailer.php');
				$mailer = new Mailer();*/
				$subject = $_SERVER['SERVER_NAME'].': '.Multilanguage::_('YOUR_AD_PUBLISHED','system');
				$from = $this->getConfigValue('order_email_acceptor');
				/*if ( $this->getConfigValue('use_smtp') ) {
					$mailer->send_smtp($n_email, $from, $subject, $body, 1);
				} else {
					$mailer->send_simple($n_email, $from, $subject, $body, 1);
				}*/
				$this->sendFirmMail($n_email, $from, $subject, $body);
			}elseif($n_phone!='' and file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/sms/admin/admin.php')){
				$body=$this->getConfigValue('apps.fasteditor.sms_send_password_text_long');
				$body=str_replace('{password}', $n_pass, $body);
				require_once (SITEBILL_DOCUMENT_ROOT.'/apps/sms/admin/admin.php');
	    		$SMSSender=new sms_admin();
	    		if($SMSSender->send($n_phone, $body)){
	    		
	    		}else{
	    		
	    		}
			}
			
			/*Add twit*/
			if($this->getConfigValue('apps.twitter.enable')){
				require_once SITEBILL_DOCUMENT_ROOT.'/apps/twitter/admin/admin.php';
				$Twitter=new twitter_admin();
				$Twitter->sendTwit($this->getRequestValue('id'));
			}
			
		}
		
		if($this->getConfigValue('is_watermark')){
			$filespath = SITEBILL_DOCUMENT_ROOT.'/img/data/';
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/watermark/watermark.php';
			$Watermark=new Watermark();
			$Watermark->setPosition($this->getConfigValue('apps.watermark.position'));
			$Watermark->setOffsets(array(
				$this->getConfigValue('apps.watermark.offset_left'),
				$this->getConfigValue('apps.watermark.offset_top'),
				$this->getConfigValue('apps.watermark.offset_right'),
				$this->getConfigValue('apps.watermark.offset_bottom')
			));
			if(1==$this->getConfigValue('save_without_watermark') && !empty($imgs)){
				$copy_folder=SITEBILL_DOCUMENT_ROOT.'/img/data/nowatermark/';
				foreach($imgs as $v){
					copy($filespath.$v['normal'], $copy_folder.$v['normal']);
				}
			}
			if(!empty($imgs)){
				foreach($imgs as $v){
					$Watermark->printWatermark($filespath.$v['normal']);
				}
			}
		}
		$page=$this->getRequestValue('page');
		$_POST=array();
	    $_POST['page']=$page;
	}
	
	
	
	/**
	 * Add data
	 * @param array $form_data form data
	 * @return boolean
	 */
	function add_data ( $form_data ) {
		
		$y_id='';
        if(strpos($form_data['youtube']['value'],'youtube.com')!==FALSE){
			$d=parse_url($form_data['youtube']['value']);
			if(isset($d['query'])){
				parse_str($d['query'],$a);
				$y_id=$a['v'];
			}
		}else{
			if(preg_match('/.*([-_A-Za-z0-9]+).*/',$form_data['youtube']['value'],$matches)){
				$y_id=$matches[0];
			}
		}
		$form_data['youtube']['value']=$y_id;
		$form_data['price']['value']=str_replace(' ', '', $form_data['price']['value']);
		$form_data['date_added']['value'] = date('Y-m-d H:i:s',time());
		
		
		if(1==$this->getConfigValue('apps.geodata.try_encode') && 1==$this->getConfigValue('apps.geodata.enable')){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/geodata/admin/admin.php';
			$GA=new geodata_admin();
			$form_data=$GA->try_geocode($form_data);
		}
		
		
		
		
		
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
	    $query = $data_model->get_insert_query(DB_PREFIX.'_'.$this->table_name, $form_data);
	    //echo $query;
	    $this->db->exec($query);
	    if ( !$this->db->success ) {
	    	echo $this->db->error.'<br>';
	    }
	     
	    $new_record_id = $this->db->last_insert_id();
	    
	    $imgs=array();
	    
	    foreach ($form_data as $form_item){
	    	if($form_item['type']=='uploads'){
	    		$ims=$this->appendUploads($this->table_name, $form_item, $this->primary_key, $new_record_id);
	    		if(is_array($ims) && count($ims)>0){
	    			$imgs=array_merge($imgs, $ims);
	    		}
	    	}
	    }
	    
	    $ims=$this->editImageMulti('data', 'data', 'id', $new_record_id);
		if(is_array($ims) && count($ims)>0){
    		$imgs=array_merge($imgs, $ims);
    	}
	   	if(1==$this->getConfigValue('apps.seo.data_alias_enable') && isset($form_data['translit_alias']) && $form_data['translit_alias']['value']==''){
	    	$this->saveTranslitAlias($new_record_id);
	    }
	    
		if($this->getConfigValue('is_watermark')){
			$filespath = SITEBILL_DOCUMENT_ROOT.'/img/data/';
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/watermark/watermark.php';
			$Watermark=new Watermark();
			$Watermark->setPosition($this->getConfigValue('apps.watermark.position'));
			$Watermark->setOffsets(array(
				$this->getConfigValue('apps.watermark.offset_left'),
				$this->getConfigValue('apps.watermark.offset_top'),
				$this->getConfigValue('apps.watermark.offset_right'),
				$this->getConfigValue('apps.watermark.offset_bottom')
			));
			if(1==$this->getConfigValue('save_without_watermark') && !empty($imgs)){
				$copy_folder=SITEBILL_DOCUMENT_ROOT.'/img/data/nowatermark/';
				foreach($imgs as $v){
					copy($filespath.$v['normal'], $copy_folder.$v['normal']);
				}
			}
			if(!empty($imgs)){
				foreach($imgs as $v){
					$Watermark->printWatermark($filespath.$v['normal']);
				}
			}
		}
		
		if($this->getConfigValue('apps.twitter.enable')){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/twitter/admin/admin.php';
			$Twitter=new twitter_admin();
			$Twitter->sendTwit($new_record_id);
		}
		
	    
		$page=$this->getRequestValue('page');
		$_POST=array();
	    $_POST['page']=$page;
	    return $new_record_id;
	}
    
	/**
	 * Return grid
	 * @param int $user_id user id
	 */
	function grid ( $user_id = 0 ) {
	    global $smarty;
	    
	    
	    $g=0;
	    
	    if($g==1){
	    	$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/data/datagrid.tpl';
	    	
	    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    	$data_model = new Data_Model();
	    	
	    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
	    	$form_generator = new Form_Generator();
	    	
	    	
	    	$_model=$data_model->get_kvartira_model();
	    	$_model[$this->table_name]['topic_id']['type']='select_box_structure_multiple_checkbox';
	    	 
	    	$_model[$this->table_name]=$data_model->init_model_data_from_request($_model[$this->table_name]);
	    	 
	    	 
	    	$selected_topics=$_model[$this->table_name]['topic_id']['values_array'];
	    	 
	    	if(is_array($selected_topics) && !empty($selected_topics)){
	    		$cats=array();
	    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
	    		$Structure_Manager = new Structure_Manager();
	    		$category_structure = $Structure_Manager->loadCategoryStructure();
	    		foreach ($selected_topics as $k=>$v){
	    			$childs = $Structure_Manager->get_all_childs($v, $category_structure);
	    			 
	    			if ( count($childs) > 0 ) {
	    				array_push($childs, $v);
	    				$cats=array_merge($cats, $childs);
	    			}else{
	    				$cats[]=$v;
	    			}
	    		}
	    		$_model[$this->table_name]['topic_id']['values_array']=array_unique($cats);
	    	}
	    	 
	    	 
	    	 
	    	 
	    	
	    	$this->template->assert('ajax_functions', $this->get_ajax_functions());
	    	
	    	$this->template->assert('topic_list', $form_generator->compile_select_box_structure_multiple_checkbox($_model[$this->table_name]['topic_id']));
	    	$this->template->assert('city_list', $form_generator->get_single_select_box_by_query($_model[$this->table_name]['city_id']));
	    	$this->template->assert('country_list', $form_generator->get_single_select_box_by_query($_model[$this->table_name]['country_id']));
	    	$this->template->assert('district_list', $form_generator->get_select_box_by_query_as_checkboxes($_model[$this->table_name]['district_id']));
	    	$this->template->assert('street_list', $form_generator->get_single_select_box_by_query($_model[$this->table_name]['street_id']));
	    	$this->template->assert('region_list', $form_generator->get_single_select_box_by_query($_model[$this->table_name]['region_id']));
	    	
	    	$DBC=DBC::getInstance();
	    	$options;
	    	foreach($_model[$this->table_name] as $mod){
	    		if($mod['type']=='select_by_query' && !in_array($mod['name'], array('user_id', 'country_id', 'region_id', 'city_id', 'district_id', 'street_id', 'metro_id'))){
	    			$query='SELECT `'.$mod['primary_key_name'].'` AS id, `'.$mod['value_name'].'` AS name FROM '.DB_PREFIX.'_'.$mod['primary_key_table'];
	    			$stmt=$DBC->query($query);
	    			if($stmt){
	    				while($ar=$DBC->fetch($stmt)){
	    					$options[$mod['name']][]=$ar;
	    				}
	    			}
	    		}elseif($mod['type']=='select_box'){
	    			foreach($mod['select_data'] as $sk=>$sd){
	    				$options[$mod['name']][]=array('id'=>$sk, 'name'=>$sd);
	    			}
	    		}
	    	}
	    	
	    	
	    	$searchParamsOptions=$options;
	    	$this->template->assert('searchParamsOptions', $searchParamsOptions);
	    	
	    	$query='SELECT
				MAX(price) AS max_price,
				MAX(square_all*1) AS max_square,
				MAX(floor*1) AS max_floor
				FROM '.DB_PREFIX.'_data
				WHERE active=1';
	    	$this->db->exec($query);
	    	$this->db->fetch_assoc();
	    	
	    	if($this->db->row['max_price']!=''){
	    		$a['price']['over']=$this->db->row['max_price'];
	    	}else{
	    		$a['price']['over']=0;
	    	}
	    	
	    	if($this->getRequestValue('price')!=''){
	    		$a['price']['max']=$this->getRequestValue('price');
	    	}else{
	    		$a['price']['max']=$a['price']['over'];
	    	}
	    	
	    	if($this->getRequestValue('price_min')!=''){
	    		$a['price']['min']=$this->getRequestValue('price_min');
	    	}else{
	    		$a['price']['min']=0;
	    	}
	    	
	    	
	    	
	    	///////////
	    	if($this->db->row['max_square']!=''){
	    		$a['max_square']['over']=$this->db->row['max_square'];
	    	}else{
	    		$a['max_square']['over']=0;
	    	}
	    	
	    	if($this->getRequestValue('squaretotal_max')!=''){
	    		$a['max_square']['max']=$this->getRequestValue('squaretotal_max');
	    	}else{
	    		$a['max_square']['max']=$a['max_square']['over'];
	    	}
	    	
	    	if($this->getRequestValue('squaretotal_min')!=''){
	    		$a['max_square']['min']=$this->getRequestValue('squaretotal_min');
	    	}else{
	    		$a['max_square']['min']=0;
	    	}
	    	
	    	
	    	
	    	if($this->db->row['max_floor']!=''){
	    		$a['max_floor']['over']=$this->db->row['max_floor'];
	    	}else{
	    		$a['max_floor']['over']=0;
	    	}
	    	
	    	if($this->getRequestValue('floor_max')!=''){
	    		$a['max_floor']['max']=$this->getRequestValue('floor_max');
	    	}else{
	    		$a['max_floor']['max']=$a['max_floor']['over'];
	    	}
	    	
	    	if($this->getRequestValue('floor_min')!=''){
	    		$a['max_floor']['min']=$this->getRequestValue('floor_min');
	    	}else{
	    		$a['max_floor']['min']=0;
	    	}
	    	$extendedSearchFormParams=$a;
	    	 
	    	$this->template->assert('extendedSearchFormParams', $extendedSearchFormParams);
	    	$this->template->assert('extendedSearchFormParamsJS', json_encode($extendedSearchFormParams));
	    	/*$geographicalValues=$this->getGeographicalValues();
	    	 $this->template->assert('availableCountries', $geographicalValues['countries']);
	    	$this->template->assert('availableRegions', $geographicalValues['regions']);
	    	$this->template->assert('availableDistricts', $geographicalValues['districts']);
	    	$this->template->assert('availableCities', $geographicalValues['cities']);*/
	    	 
	    	 
	    	 
	    	 
	    	$max_price=$extendedSearchFormParams['price']['max'];
	    	 
	    	 
	    	$diapasones=20;
	    	$diapasone_range=ceil($max_price/$diapasones);
	    	$diapasone_range_count=(string)$diapasone_range;
	    	$dlenth=$diapasone_range_count[0]*pow(10, strlen($diapasone_range)-1).'<br>';
	    	$real_diapasones=floor($max_price/$dlenth)+1;
	    	$price_diapasones='';
	    	for($i=1; $i<=$real_diapasones; $i++){
	    		$price_diapasones.='<option value="'.$dlenth*$i.'">'.number_format($dlenth*$i, 0, '.', ' ').'</option>';
	    	}
	    	 
	    	 
	    	$this->template->assert('price_diapasones', $price_diapasones);
	    	 
	    	$max_square=$extendedSearchFormParams['max_square']['max'];
	    	
	    	$diapasones=20;
	    	$diapasone_range=ceil($max_square/$diapasones);
	    	$diapasone_range_count=(string)$diapasone_range;
	    	$dlenth=$diapasone_range_count[0]*pow(10, strlen($diapasone_range)-1).'<br>';
	    	$real_diapasones=floor($max_square/$dlenth)+1;
	    	$price_diapasones='';
	    	for($i=1; $i<=$real_diapasones; $i++){
	    		$price_diapasones.='<option value="'.$dlenth*$i.'">'.number_format($dlenth*$i, 0, '.', ' ').'</option>';
	    	}
	    	
	    	
	    	$this->template->assert('square_diapasones', $price_diapasones);
	    	
	    	$smarty->assign('user_data_search_form', SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/data/search_form.tpl');
	    	$html=$smarty->fetch($tpl);
	    	return $html;
	    }
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $params = array();
        $params[] = 'action=data';
        if($this->getRequestValue('active')){
        	$params[] = 'active='.$this->getRequestValue('active');
        }
	 	if($this->getRequestValue('hot')){
        	$params[] = 'hot='.$this->getRequestValue('hot');
        }
        
        $params['active'] = $this->getRequestValue('active');
        $params['hot'] = $this->getRequestValue('hot');
        
        $current_category_id = $this->getRequestValue('topic_id');
        
        
	    $smarty->assign('data_category_tree', $Structure_Manager->get_category_tree_control($current_category_id, 0, false, $params));
	    
	    $rs .= '<table border="0" width="100%">';
	    $rs .= '<tr>';
	    /*
	    $rs .= '<td style="vertical-align: top; width: 190px; padding-right: 5px;">';
	    $rs .= $Structure_Manager->get_category_tree_control($current_category_id, 0, false, $params);
	    $rs .= '</td>';
	    */
	    $rs .= '<td style="vertical-align: top;">';
	   
	    $rs .= $this->get_data_grid(0, $current_category_id);
	    $rs .= '</td>';
	    $rs .= '<tr>';
	    $rs .= '</table>';
	    return $rs;
	}
	
	

	/**
	 * Get offer list
	 * @param int $user_id
	 * @param int $topic_id 
	 * @return mixed
	 */
	function getOfferList( $user_id, $topic_id ){
		$ret=array();
		if ( $topic_id > 0 ) {
		    $query='SELECT * FROM '.DB_PREFIX.'_data where topic_id='.$topic_id.' order by date_added desc';
		} else {
		    $query='SELECT * FROM '.DB_PREFIX.'_data order by date_added desc';
		}
		$this->db->exec($query);
		if($this->db->success){
			while($this->db->fetch_assoc()){
				$ret[]=$this->db->row;
			}
		}
        return $ret;
	}
	
	function getAdditionalSearchForm(){
		$query='select * from re_user order by fio';
		$this->db->exec($query);
		$ret.='<form method="post">';
		$ret.='<select name="user_id" style="width: 200px;" onchange="this.form.submit()">';
		$ret.='<option value="">'.Multilanguage::_('L_CHOOSE_USER').'</option>';
		while($this->db->fetch_assoc()){
			if($this->getRequestValue('user_id')==$this->db->row['user_id']){
				$ret.='<option value="'.$this->db->row['user_id'].'" selected="selected">'.$this->db->row['login'].' ('.$this->db->row['fio'].')</option>';
			}else{
				$ret.='<option value="'.$this->db->row['user_id'].'">'.$this->db->row['login'].' ('.$this->db->row['fio'].')</option>';
			}
			
		}
		$ret.='</select>';
		
		$ret.='<input type="hidden" name="action" value="'.$this->action.'">';
		$ret .= '<input type="submit" name="submit" value="'.Multilanguage::_('L_TEXT_SELECT').'">';
		$ret.='</form>';
		return $ret;
	}
	
	function getUserSelectBox(){
		$query='select * from re_user order by fio';
		$this->db->exec($query);
		$ret.='<select name="user_id" onchange="this.form.submit()">';
		$ret.='<option value="">'.Multilanguage::_('L_CHOOSE_USER').'</option>';
		while($this->db->fetch_assoc()){
			if($this->getRequestValue('user_id')==$this->db->row['user_id']){
				$ret.='<option value="'.$this->db->row['user_id'].'" selected="selected">'.$this->db->row['login'].' ('.$this->db->row['fio'].')</option>';
			}else{
				$ret.='<option value="'.$this->db->row['user_id'].'">'.$this->db->row['login'].' ('.$this->db->row['fio'].')</option>';
			}
				
		}
		$ret.='</select>';
		return $ret;
	}
	
	
	function mass_delete_data($table_name, $primary_key, $ids){
		$errors='';
		
		if(count($ids)>0){
			foreach($ids as $id){
				$log_id=false;
				if($this->getConfigValue('apps.realtylog.enable')){
		        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylog/admin/admin.php';
	        		$Logger=new realtylog_admin();
	        		$log_id=$Logger->addLog($id, $_SESSION['user_id_value'], 'delete', $this->table_name);
		        }
				$this->delete_data($this->table_name, $this->primary_key, $id);
				if ( $this->getError() ) {
					if($log_id!==false){
						$Logger->deleteLog($log_id);
					}
			        $errors .= '<div align="center">'.Multilanguage::_('L_ERROR_ON_DELETE').' ID='.$id.': '.$this->GetErrorMessage().'<br>';
			        $errors .= '</div>';
			        $this->error_message=false;
			    }
			}
		}
		if($errors!=''){
			$rs.=$errors.'<div align="center"><a href="?action='.$this->action.'">ОК</a></div>';
		}else{
			$rs .= $this->grid($user_id);
		}
		return $rs;
	}
	
	/**
	 * Delete data
	 * @param string $table_name
	 * @param string $primary_key
	 * @param int $primary_key_value
	 */
	function delete_data($table_name, $primary_key, $primary_key_value ) {
		$DBC=DBC::getInstance();
		$imgs_ids=array();
		$query='SELECT image_id FROM '.DB_PREFIX.'_'.$table_name.'_image WHERE '.$primary_key.'=?';;
		$stmt=$DBC->query($query, array($primary_key_value));
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$imgs_ids[]=$ar['image_id'];
			}
		}
		
		$delete_result=parent::delete_data($table_name, $primary_key, $primary_key_value);
		if($delete_result){
			if(count($imgs_ids)>0){
				foreach($imgs_ids as $im){
					$this->deleteImage($table_name, $im);
				}
			}
		}
		
		return $delete_result;
	}
	
	public function get_element($element_name){
		//echo $element_name;
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    $form_data = $this->data_model;
	    //print_r($form_data);
	    if(isset($form_data[$this->table_name][$element_name])){
	    	$fd[$this->table_name][$element_name]=$form_data[$this->table_name][$element_name];
	    	//$fd[$this->table_name]['name']='param';
	    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
	    	$form_generator = new Form_Generator();
	    	$element_data=$form_generator->compile_form_elements($fd[$this->table_name], false);
	    	//print_r($element_data);
	    	return $element_data['hash'][$element_name]['html'];
	    }
	    return '';
	}
	
	private function prepareVipStatsDateValue($current_vip_timestamp, $new_vip_timestamp){
		$ret=0;
		if($current_vip_timestamp<time()){
			$current_vip_timestamp=0;
		}
		 
		if($current_vip_timestamp!=0){
			$olddate=date('d.m.Y',$current_vip_timestamp);
			$oldtime=date('H:i:s',$current_vip_timestamp);
			$newdate=date('d.m.Y',$new_vip_timestamp);
			if($newdate!=$olddate){
				$ret=strtotime($newdate.' '.$oldtime);
			}else{
				$ret=FALSE;
			}
		}else{
			if($new_vip_timestamp=='' || $new_vip_timestamp==0){
				$ret=0;
			}else{
				$newdate=date('d.m.Y',$new_vip_timestamp);
				$ret=strtotime($newdate.' '.date('H:i:s',time()));
			}
		}
		return $ret;
	}
	
	protected function _duplicateAction(){
		$rs='';
		$id_array=array();
		$ids=trim($this->getRequestValue('ids'));
		if($ids!=''){
			$id_array=explode(',',$ids);
		}
		$rs.=$this->duplicate($this->table_name, $this->primary_key, $id_array);
		return $rs;
	}
	
	protected function duplicate($table_name, $primary_key, $ids){
		if(count($ids)==0){
			return;
		}
		$with_images=false;
		if(1==(int)$this->getRequestValue('duplicate_images')){
			$with_images=true;
		}
		
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$form_data = $this->data_model;
		
		/*$UploadifyElement='';
		$UploadsElement=array();*/
		
		
		foreach($ids as $id){
			$form_data[$this->table_name] = $data_model->init_model_data_from_db ( $this->table_name, $this->primary_key, $id, $form_data[$this->table_name] );
			//echo '<pre>';
			//print_r($form_data[$this->table_name]);
			//exit();
			if($with_images){
				$hasUploadify=false;
				$uploads=array();
				foreach ($form_data[$this->table_name] as $key=>$item){
					if($item['type']=='uploadify_image'){
						$hasUploadify=true;
						$images=array();
						if(count($item['image_array'])>0){
							$i=1;
							foreach ($item['image_array'] as $img){
								$preview=$img['preview'];
								$normal=$img['normal'];
								
								$parts=explode('.', $normal);
								$normal_name="img".uniqid().'_'.time()."_".$i.".".end($parts);
								reset($parts);
								$parts=explode('.', $preview);
								$preview_name="prv".uniqid().'_'.time()."_".$i.".".end($parts);
								reset($parts);
								copy(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$normal, SITEBILL_DOCUMENT_ROOT.'/img/data/'.$normal_name);
								copy(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$preview, SITEBILL_DOCUMENT_ROOT.'/img/data/'.$preview_name);
								$images[]=array('normal'=>$normal_name, 'preview'=>$preview_name);
								$i++;
							}
						}
					}elseif($item['type']=='uploads'){
						if(is_array($item['value']) && count($item['value'])>0){
							$i=1;
							//print_r($item['value']);
							foreach ($item['value'] as $k=>$img){
								$preview=$img['preview'];
								$normal=$img['normal'];
							
								$parts=explode('.', $normal);
								$normal_name="img".uniqid().'_'.time()."_".$i.".".end($parts);
								reset($parts);
								$parts=explode('.', $preview);
								$preview_name="prv".uniqid().'_'.time()."_".$i.".".end($parts);
								reset($parts);
								copy(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$normal, SITEBILL_DOCUMENT_ROOT.'/img/data/'.$normal_name);
								copy(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$preview, SITEBILL_DOCUMENT_ROOT.'/img/data/'.$preview_name);
								$form_data[$this->table_name][$key]['value'][$k]['normal']=$normal_name;
								$form_data[$this->table_name][$key]['value'][$k]['preview']=$preview_name;
								
								$i++;
							}
							$uploads[$key]=serialize($form_data[$this->table_name][$key]['value']);
						}
						
					}
				}
			}else{
				foreach ($form_data[$this->table_name] as $k=>$item){
					if($item['type']=='uploads'){
						$form_data[$this->table_name][$k]['value']='';
					}
				}
			}
			
			//echo '<pre>';
			//print_r($form_data);
			$form_data[$this->table_name][$primary_key]['value']=='';
			$new_record_id=$this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
			if($with_images && $hasUploadify && count($images)>0){
				$this->add_image_records($images, $this->table_name, $this->primary_key, $new_record_id);
			}
			if($with_images && !empty($uploads)){
				$DBC=DBC::getInstance();
				$query='UPDATE '.DB_PREFIX.'_data SET';
				foreach($uploads as $ku=>$kv){
					$query.=' `'.$ku.'`=?';
				}
				$query.=' WHERE '.$this->primary_key.'='.$new_record_id;
				$stmt=$DBC->query($query, array_values($uploads));
			}
		}
		return $this->_defaultAction();
	}
	
	protected function batch_update($table_name, $primary_key){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$form_data = $this->data_model;
		$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
		foreach($form_data[$this->table_name] as $key=>$value){
			if($value['type']=='attachment' || $value['type']=='photo' || $value['type']=='uploadify_image'){
				unset($form_data[$this->table_name][$key]);
			}
		}
			
		
		if(isset($_POST['submit'])){
			
			$need_to_update=$this->getRequestValue('batch_update');
			$ids=$this->getRequestValue('batch_ids');
			
			if(count($ids)<1){
				return $this->grid();
			}
			
			if(count($need_to_update)<1){
				return $this->grid();
			}
			
			$sub_form=array();
			foreach($need_to_update as $key=>$value){
				if(isset($form_data[$this->table_name][$key])){
					$sub_form[$this->table_name][$key]=$form_data[$this->table_name][$key];
				}
			}
			
			if(empty($sub_form)){
				return $this->grid();
			}
			
			$sub_form[$this->table_name] = $data_model->init_model_data_from_request($sub_form[$this->table_name]);
			$new_values=$this->getRequestValue('_new_value');
			if(1==$this->getConfigValue('use_combobox') && count($new_values)>0){
				$remove_this_names=array();
				foreach($sub_form[$this->table_name] as $fd){
					if(isset($new_values[$fd['name']]) && $new_values[$fd['name']]!='' && $fd['combo']==1){
						$id=md5(time().'_'.rand(100,999));
						$remove_this_names[]=$id;
						$sub_form[$this->table_name][$id]['value'] = $new_values[$fd['name']];
						$sub_form[$this->table_name][$id]['type'] = 'auto_add_value';
						$sub_form[$this->table_name][$id]['dbtype'] = 'notable';
						$sub_form[$this->table_name][$id]['value_table'] = $form_data[$this->table_name][$fd['name']]['primary_key_table'];
						$sub_form[$this->table_name][$id]['value_primary_key'] = $sub_form[$this->table_name][$fd['name']]['primary_key_name'];
						$sub_form[$this->table_name][$id]['value_field'] = $sub_form[$this->table_name][$fd['name']]['value_name'];
						$sub_form[$this->table_name][$id]['assign_to'] = $fd['name'];
						$sub_form[$this->table_name][$id]['required'] = 'off';
						$sub_form[$this->table_name][$id]['unique'] = 'off';
					}
				}
			}
			$data_model->forse_auto_add_values($sub_form[$this->table_name]);
				
				
				
			if ( !$this->check_data( $sub_form[$this->table_name] ) ) {
				$sub_form['data']=$this->removeTemporaryFields($sub_form['data'],$remove_this_names);
				$rs = $this->get_batch_update_form($form_data[$this->table_name], $ids, $need_to_update);
			} else {
				foreach($ids as $id){
					$concrete_form=$sub_form;
					$concrete_form[$this->table_name][$this->primary_key]['value']=$id;
					$concrete_form[$this->table_name][$this->primary_key]['type'] = 'primary_key';
					$this->edit_data($concrete_form[$this->table_name]);
					if ( $this->getError() ) {
						//$form_data['data']=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
						//$rs = $this->get_form($form_data[$this->table_name], 'edit');
					} else {
						if($this->getConfigValue('apps.realtylog.enable')){
							require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylog/admin/admin.php';
							$Logger=new realtylog_admin();
							$Logger->addLog($concrete_form[$this->table_name][$this->primary_key]['value'], $_SESSION['user_id_value'], 'edit', $this->table_name);
						}
						
						if($this->getConfigValue('apps.realtylogv2.enable')){
							require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/admin.php';
							$Logger=new realtylogv2_admin();
							$Logger->addLog($concrete_form[$this->table_name][$this->primary_key]['value'], $_SESSION['user_id_value'], 'edit', $this->table_name, $this->primary_key);
						}
						
					}
					
					
					
				}
				$rs .= $this->grid();
			}
			
			
		}else{
			$ids=$this->getRequestValue('batch_ids');
			$rs .= $this->get_batch_update_form($form_data[$this->table_name], explode(',', $ids));
		}
		
		return $rs;
	}
	
	function get_batch_update_form ( $form_data=array(), $ids=array(), $selected_fields=array(), $action = 'index.php' ) {
	
		$_SESSION['allow_disable_root_structure_select']=true;
		global $smarty;
		if($button_title==''){
			$button_title = Multilanguage::_('L_TEXT_SAVE');
		}
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
	
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
			
			
		$rs .= $this->get_ajax_functions();
		if(1==$this->getConfigValue('apps.geodata.enable')){
			$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/geodata/js/geodata.js"></script>';
		}
		$rs .= '<form method="post" class="form-horizontal" action="'.$action.'" enctype="multipart/form-data">';
			
		if ( $this->getError() ) {
			$smarty->assign('form_error',$form_generator->get_error_message_row($this->GetErrorMessage()));
		}
			
		$el = $form_generator->compile_form_elements($form_data);
	
		$el['private'][]=array('html'=>'<input type="hidden" name="do" value="batch_update" />');
		
		$el['private'][]=array('html'=>'<input type="hidden" name="action" value="'.$this->action.'">');
		$el['private'][]=array('html'=>'<input type="hidden" name="language_id" value="'.$language_id.'">');
		
		foreach($ids as $id){
			$el['private'][]=array('html'=>'<input type="hidden" name="batch_ids[]" value="'.$id.'">');
		}
	
		$el['form_header']=$rs;
		$el['form_footer']='</form>';
			
		/*if ( $do != 'new' ) {
		 $el['controls']['apply']=array('html'=>'<button id="apply_changes" class="btn btn-info">'.Multilanguage::_('L_TEXT_APPLY').'</button>');
		}*/
		$el['controls']['submit']=array('html'=>'<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">'.$button_title.'</button>');
			
	
	
	
		$smarty->assign('selected_fields', $selected_fields);
		$smarty->assign('form_elements', $el);
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data_batch_update.tpl')){
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data_batch_update.tpl';
		}else{
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/data_form_batch_update.tpl';
		}
		return $smarty->fetch($tpl_name);
	
	
	
	
	}
}
?>