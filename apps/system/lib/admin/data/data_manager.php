<?php
/**
 * Data manager
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class Data_Manager extends Object_Manager {
	protected $billing_mode_on=false;
	protected $data_model_object;
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
	    $this->data_model_object = $data_model;
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
		/*$this->data_model['data']['user_id']['value_name'] = 'fio';
		$this->data_model['data']['user_id']['title_default'] = Multilanguage::_('L_CHOOSE_USER');*/
		$this->data_model['data']['user_id']['value_default'] = 0;
		$this->data_model['data']['user_id']['required'] = 'on';
		$this->data_model['data']['user_id']['unique'] = 'off';
		/*var_dump($_SESSION['user_id_value']);
		if((1===(int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name']!=='admin') && (1===(int)$this->getConfigValue('data_adv_share_access'))){
			$user_id=(int)$_SESSION['user_id_value'];
			
			//$this->setRequestValue('user_id', $user_id);
			$this->data_model['data']['user_id']['value'] = $user_id;
		}else{
			$this->data_model['data']['user_id']['value'] = $this->getAdminUserId();
		}*/
		$user_id=(int)$_SESSION['user_id_value'];
		$this->data_model['data']['user_id']['value']=$user_id;
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable')==1){
			$this->billing_mode_on=true;
		}
    }
    
    function get_model () {
    	return $this->data_model;
    }
    
    function structure_processor () {
    	if ( $this->getRequestValue('subdo') == 'sms' ) {
    		$form_data = $this->load_by_id($this->getRequestValue('id'));
    		if ( $form_data['tmp_password']['value'] == '' ) {
    			$form_data['tmp_password']['value'] = substr(md5(time()),1,6);
				
				require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
				$data_model = new Data_Model();
				$DBC=DBC::getInstance();
				$queryp = $data_model->get_prepared_edit_query(DB_PREFIX.'_'.$this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data);
				$DBC->query($queryp['q'], $queryp['p']);
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
    	return;
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
        } elseif( $active == 'free' ){
        	$query = "select count(id) as total from ".DB_PREFIX."_data where status_id='free'";
        } elseif( $active == 'no_answer' ){
        	$query = "select count(id) as total from ".DB_PREFIX."_data where status_id='no_answer'";
        } elseif( $active == 'call' ){
        	$query = "select count(id) as total from ".DB_PREFIX."_data where status_id='call'";
        } elseif( $active == 'actual' ){
        	$query = "select count(id) as total from ".DB_PREFIX."_data where status_id='actual'";
        } elseif( $active == 'archived' ){
        	$query = "select count(id) as total from ".DB_PREFIX."_data where archived=1";
        } else {
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
	    if ( isset($this->data_model['data']['status_id']) ) {
	    	$smarty->assign('free_count', $this->get_count('free'));
	    	$smarty->assign('no_answer_count', $this->get_count('no_answer'));
	    	$smarty->assign('call_count', $this->get_count('call'));
	    	$smarty->assign('actual_count', $this->get_count('actual'));
	    }
	    
	    if(1==(int)$this->getConfigValue('apps.realty.use_predeleting') && isset($this->data_model['data']['archived'])){
	    	$smarty->assign('archived_count', $this->get_count('archived'));
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
	         
	        $rs = $smarty->fetch( $this->getAdminTplFolder()."/data_top_menu.tpl.html" );
	        //$rs = $smarty->fetch( SITEBILL_DOCUMENT_ROOT."/apps/admin/admin/template/data_top_menu.tpl.html" );
	    } else {
	        $rs = '';
	        $rs .= '<table border="0">';
	        $rs .= '<tr>';
	        $rs .= '<td>';
	        $rs .= '<a href="?action='.$this->action.'&do=new" class="btn btn-success">'.Multilanguage::_('L_ADD_RECORD_BUTTON').'</a>';
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
	
	protected function checkOwning($id, $user_id){
		$DBC=DBC::getInstance();
		$query='SELECT COUNT(`'.$this->primary_key.'`) AS _cnt FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE `'.$this->primary_key.'`=? AND `user_id`=?';
		$stmt=$DBC->query($query, array($id, $user_id));
		$res=false;
		if($stmt){
			$ar=$DBC->fetch($stmt);
			if((int)$ar['_cnt']===1){
				$res=true;
			}
		}
		return $res;
	}
	
	protected function _editAction(){
		if((1===(int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name']!=='admin') && (1===(int)$this->getConfigValue('data_adv_share_access'))){
			$user_id=(int)$_SESSION['user_id_value'];
			if($this->checkOwning($this->getRequestValue($this->primary_key), $user_id)){
				return parent::_editAction();
			}
		} else {
			return parent::_editAction();
		}
		return '';
	}
	
	protected function _edit_doneAction(){
		if((1===(int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name']!=='admin') && (1===(int)$this->getConfigValue('data_adv_share_access'))){
			$user_id=(int)$_SESSION['user_id_value'];
			$this->setRequestValue('user_id', $user_id);
			$_POST['user_id']=$user_id;
			if($this->checkOwning($this->getRequestValue($this->primary_key), $user_id)){
				return parent::_edit_doneAction();
			}
		} else {
			return parent::_edit_doneAction();
		}
		return '';
	}
	
	protected function _new_doneAction(){
		if((1===(int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name']!=='admin') && (1===(int)$this->getConfigValue('data_adv_share_access'))){
			$user_id=(int)$_SESSION['user_id_value'];
			$this->setRequestValue('user_id', $user_id);
			$_POST['user_id']=$user_id;
		}
		return parent::_new_doneAction();
	}
	
	protected function _delete_finalAction(){
		if(intval($this->getConfigValue('apps.realty.use_predeleting'))!==1){
			return '';
		}
		
		if((1===(int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name']!=='admin') && (1===(int)$this->getConfigValue('data_adv_share_access'))){
			$user_id=(int)$_SESSION['user_id_value'];
			if($this->checkOwning($id, $user_id)){
				return parent::_deleteAction();
			}
		} else {
			return parent::_deleteAction();
		}
		return '';
	}
	
	protected function _restoreAction(){
		if(intval($this->getConfigValue('apps.realty.use_predeleting'))!==1){
			return '';
		}
		$id=intval($this->getRequestValue($this->primary_key));
		if((1===(int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name']!=='admin') && (1===(int)$this->getConfigValue('data_adv_share_access'))){
			$user_id=(int)$_SESSION['user_id_value'];
			if($this->checkOwning($id, $user_id)){
				$DBC=DBC::getInstance();
				$query='UPDATE '.DB_PREFIX.'_data SET `archived`=0 WHERE `id`=?';
				$stmt=$DBC->query($query, array($id));
			}
		} else {
			$DBC=DBC::getInstance();
			$query='UPDATE '.DB_PREFIX.'_data SET `archived`=0 WHERE `id`=?';
			$stmt=$DBC->query($query, array($id));
		}
		header('location: '.SITEBILL_MAIN_URL.'/admin/?archived=1');
		exit();
		return '';
	}
	
	protected function _deleteAction(){
		$id=intval($this->getRequestValue($this->primary_key));
		if((1===(int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name']!=='admin') && (1===(int)$this->getConfigValue('data_adv_share_access'))){
			$user_id=(int)$_SESSION['user_id_value'];
			if($this->checkOwning($id, $user_id)){
				if(1==(int)$this->getConfigValue('apps.realty.use_predeleting') && isset($this->data_model['data']['archived'])){
					$DBC=DBC::getInstance();
					$query='UPDATE '.DB_PREFIX.'_data SET `archived`=1 WHERE `id`=?';
					$stmt=$DBC->query($query, array($id));
					header('location: '.SITEBILL_MAIN_URL.'/admin/?action='.$this->action);
					exit();
				}else{
					return parent::_deleteAction();
				}
			}
		} else {
			if(1==(int)$this->getConfigValue('apps.realty.use_predeleting') && isset($this->data_model['data']['archived'])){
				$DBC=DBC::getInstance();
				$query='UPDATE '.DB_PREFIX.'_data SET `archived`=1 WHERE `id`=?';
				$stmt=$DBC->query($query, array($id));
				header('location: '.SITEBILL_MAIN_URL.'/admin/?action='.$this->action);
				exit();
				return $this->grid();
			}else{
				return parent::_deleteAction();
			}
		}
		return '';
	}
	
	function gatherRequestParams(){
		$params=array();
		$var=$this->getRequestValue('user_id');
		if(!is_array($var) && intval($var)>0){
			$params['user_id'] = intval($var);
		}elseif(is_array($var)){
			$var=array_map(function($a){return intval($a);}, $var);
			$var=array_filter($var, function($a){if($a!=0){return $a;}});
			if(count($var)>0){
				$params['user_id']=$var;
			}
		}
		
		$var=$this->getRequestValue('topic_id');
		if(!is_array($var) && intval($var)>0){
			$params['topic_id'] = intval($var);
		}elseif(is_array($var)){
			$var=array_map(function($a){return intval($a);}, $var);
			$var=array_filter($var, function($a){if($a!=0){return $a;}});
			if(count($var)>0){
				$params['topic_id']=$var;
			}
		}
		
		$var=$this->getRequestValue('country_id');
		if(!is_array($var) && intval($var)>0){
			$params['country_id'] = intval($var);
		}elseif(is_array($var)){
			$var=array_map(function($a){return intval($a);}, $var);
			$var=array_filter($var, function($a){if($a!=0){return $a;}});
			if(count($var)>0){
				$params['country_id']=$var;
			}
		}
		
		$var=$this->getRequestValue('region_id');
		if(!is_array($var) && intval($var)>0){
			$params['region_id'] = intval($var);
		}elseif(is_array($var)){
			$var=array_map(function($a){return intval($a);}, $var);
			$var=array_filter($var, function($a){if($a!=0){return $a;}});
			if(count($var)>0){
				$params['region_id']=$var;
			}
		}
		
		$var=$this->getRequestValue('city_id');
		if(!is_array($var) && intval($var)>0){
			$params['city_id'] = intval($var);
		}elseif(is_array($var)){
			$var=array_map(function($a){return intval($a);}, $var);
			$var=array_filter($var, function($a){if($a!=0){return $a;}});
			if(count($var)>0){
				$params['city_id']=$var;
			}
		}
		
		$var=$this->getRequestValue('district_id');
		if(!is_array($var) && intval($var)>0){
			$params['district_id'] = intval($var);
		}elseif(is_array($var)){
			$var=array_map(function($a){return intval($a);}, $var);
			$var=array_filter($var, function($a){if($a!=0){return $a;}});
			if(count($var)>0){
				$params['district_id']=$var;
			}
		}
		
		$var=$this->getRequestValue('metro_id');
		if(!is_array($var) && intval($var)>0){
			$params['metro_id'] = intval($var);
		}elseif(is_array($var)){
			$var=array_map(function($a){return intval($a);}, $var);
			$var=array_filter($var, function($a){if($a!=0){return $a;}});
			if(count($var)>0){
				$params['metro_id']=$var;
			}
		}
		
		$var=$this->getRequestValue('street_id');
		if(!is_array($var) && intval($var)>0){
			$params['street_id'] = intval($var);
		}elseif(is_array($var)){
			$var=array_map(function($a){return intval($a);}, $var);
			$var=array_filter($var, function($a){if($a!=0){return $a;}});
			if(count($var)>0){
				$params['street_id']=$var;
			}
		}
		
		$var=intval($this->getRequestValue('page'));
		if($var>0){
			$params['page'] = $var;
		}
		
		$var=trim($this->getRequestValue('order'));
		if($var!=''){
			$params['order'] = $var;
		}
		
		$var=trim($this->getRequestValue('asc'));
		if($var!=''){
			$params['asc'] = $var;
		}
		
		$var=trim($this->getRequestValue('active'));
		if($var!=''){
			$params['active'] = $var;
		}
		
		$var=intval($this->getRequestValue('hot'));
		if($var>0){
			$params['hot'] = $var;
		}
		
		$var=$this->getRequestValue('id');
		if(!is_array($var) && intval($var)>0){
			$params['id'] = intval($var);
		}elseif(is_array($var)){
			$var=array_map(function($a){return intval($a);}, $var);
			$var=array_filter($var, function($a){if($a!=0){return $a;}});
			if(count($var)>0){
				$params['id']=$var;
			}
		}
		
		$var=intval($this->getRequestValue('status_id'));
		if($var>0){
			$params['status_id'] = $var;
		}
		
		$var=intval($this->getRequestValue('client_id'));
		if($var>0){
			$params['client_id'] = $var;
		}
		
		$var=intval($this->getRequestValue('archived'));
		if($var>0){
			$params['archived'] = $var;
		}
		
		//$params['topic_id'] = $this->getRequestValue('topic_id');
		// $params['order'] = $this->getRequestValue('order');
		//$params['country_id'] = $this->getRequestValue('country_id');
		// $params['region_id'] = $this->getRequestValue('region_id');
		//$params['city_id'] = $this->getRequestValue('city_id');
		//$params['district_id'] = $this->getRequestValue('district_id');
		//$params['metro_id'] = $this->getRequestValue('metro_id');
		//$params['street_id'] = $this->getRequestValue('street_id');
		//$params['page'] = $this->getRequestValue('page');
		// $params['asc'] = $this->getRequestValue('asc');
		$params['price'] = $this->getRequestValue('price');
		//$params['active'] = $this->getRequestValue('active');
		//$params['hot'] = $this->getRequestValue('hot');
		//$params['id'] = (int)$this->getRequestValue('srch_id');
		//$params['status_id'] = intval($this->getRequestValue('status_id'));
		//$params['client_id'] = intval($this->getRequestValue('client_id'));
		//$params['archived'] = intval($this->getRequestValue('archived'));
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable')==1){
			$var=intval($this->getRequestValue('vip_status'));
			if($var>0){
				$params['vip_status'] = $var;
			}
			$var=intval($this->getRequestValue('premium_status'));
			if($var>0){
				$params['premium_status'] = $var;
			}
			$var=intval($this->getRequestValue('bold_status'));
			if($var>0){
				$params['bold_status'] = $var;
			}
			/*$params['vip_status'] = (int)$this->getRequestValue('vip_status');
			 $params['premium_status'] = (int)$this->getRequestValue('premium_status');
			$params['bold_status'] = (int)$this->getRequestValue('bold_status');*/
		
		}
		
		
		
		if(isset($this->data_model[$this->table_name]['uniq_id'])){
			$var=intval($this->getRequestValue('uniq_id'));
			if($var>0){
				$params['uniq_id'] = $var;
			}
			//$params['uniq_id'] = (int)$this->getRequestValue('uniq_id');
			//$smarty->assign('show_uniq_id', 'true');
		}
		if($this->getRequestValue('srch_export_cian')=='on' || $this->getRequestValue('srch_export_cian')=='1'){
			$var=intval($this->getRequestValue('srch_export_cian'));
			if($var>0){
				$params['srch_export_cian'] = 1;
			}
			//$params['srch_export_cian'] = 1;
		
		}
		
		$var=$this->getRequestValue('srch_id');
		if(!is_array($var) && intval($var)>0){
			$params['id'] = intval($var);
		}elseif(is_array($var)){
			$var=array_map(function($a){return intval($a);}, $var);
			$var=array_filter($var, function($a){if($a!=0){return $a;}});
			if(count($var)>0){
				$params['id']=$var;
			}
		}
		
		$var=trim($this->getRequestValue('srch_word'));
		if($var!=''){
			$params['srch_word'] = $var;
		}
		$var=trim($this->getRequestValue('srch_phone'));
		if($var!=''){
			$params['srch_phone'] = $var;
		}
		$var=trim($this->getRequestValue('srch_date_from'));
		if($var!=''){
			$params['srch_date_from'] = $var;
		}else{
			$params['srch_date_from'] = 0;
		}
		$var=trim($this->getRequestValue('srch_date_to'));
		if($var!=''){
			$params['srch_date_to'] = $var;
		}else{
			$params['srch_date_to'] = 0;
		}
		
		//$params['per_page'] = 10;
		//$params['srch_word'] = $this->getRequestValue('srch_word');
		//$params['srch_phone'] = $this->getRequestValue('srch_phone');
		//$params['srch_date_from'] = $this->getRequestValue('srch_date_from') ? $this->getRequestValue('srch_date_from') : 0;
		//$params['srch_date_to'] = $this->getRequestValue('srch_date_to') ? $this->getRequestValue('srch_date_to') : 0;
		/*$params['admin'] = true;
		$params['_collect_user_info']=1;
		
		if((1===(int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name']!=='admin') && (1===(int)$this->getConfigValue('data_adv_share_access'))){
			$params['user_id']=(int)$_SESSION['user_id_value'];
		}*/
		return $params;
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
        $grid_constructor=$this->_getGridConstructor();
       // require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php';
      //  $grid_constructor = new Grid_Constructor();
        //$grid_constructor=$this->
        $params=$this->gatherRequestParams();
        if(isset($this->data_model[$this->table_name]['uniq_id'])){
        	$smarty->assign('show_uniq_id', 'true');
        }
       /* $var=$this->getRequestValue('user_id');
        if(!is_array($var) && intval($var)>0){
        	$params['user_id'] = intval($var);
        }elseif(is_array($var)){
        	$var=array_map(function($a){return intval($a);}, $var);
        	$var=array_filter($var, function($a){if($a!=0){return $a;}});
        	if(count($var)>0){
        		$params['user_id']=$var;
        	}
        }
        
        $var=$this->getRequestValue('topic_id');
        if(!is_array($var) && intval($var)>0){
        	$params['topic_id'] = intval($var);
        }elseif(is_array($var)){
        	$var=array_map(function($a){return intval($a);}, $var);
        	$var=array_filter($var, function($a){if($a!=0){return $a;}});
        	if(count($var)>0){
        		$params['topic_id']=$var;
        	}
        }
        
        $var=$this->getRequestValue('country_id');
        if(!is_array($var) && intval($var)>0){
        	$params['country_id'] = intval($var);
        }elseif(is_array($var)){
        	$var=array_map(function($a){return intval($a);}, $var);
        	$var=array_filter($var, function($a){if($a!=0){return $a;}});
        	if(count($var)>0){
        		$params['country_id']=$var;
        	}
        }
        
        $var=$this->getRequestValue('region_id');
        if(!is_array($var) && intval($var)>0){
        	$params['region_id'] = intval($var);
        }elseif(is_array($var)){
        	$var=array_map(function($a){return intval($a);}, $var);
        	$var=array_filter($var, function($a){if($a!=0){return $a;}});
        	if(count($var)>0){
        		$params['region_id']=$var;
        	}
        }
        
        $var=$this->getRequestValue('city_id');
        if(!is_array($var) && intval($var)>0){
        	$params['city_id'] = intval($var);
        }elseif(is_array($var)){
        	$var=array_map(function($a){return intval($a);}, $var);
        	$var=array_filter($var, function($a){if($a!=0){return $a;}});
        	if(count($var)>0){
        		$params['city_id']=$var;
        	}
        }
        
        $var=$this->getRequestValue('district_id');
        if(!is_array($var) && intval($var)>0){
        	$params['district_id'] = intval($var);
        }elseif(is_array($var)){
        	$var=array_map(function($a){return intval($a);}, $var);
        	$var=array_filter($var, function($a){if($a!=0){return $a;}});
        	if(count($var)>0){
        		$params['district_id']=$var;
        	}
        }
        
        $var=$this->getRequestValue('metro_id');
        if(!is_array($var) && intval($var)>0){
        	$params['metro_id'] = intval($var);
        }elseif(is_array($var)){
        	$var=array_map(function($a){return intval($a);}, $var);
        	$var=array_filter($var, function($a){if($a!=0){return $a;}});
        	if(count($var)>0){
        		$params['metro_id']=$var;
        	}
        }
        
        $var=$this->getRequestValue('street_id');
        if(!is_array($var) && intval($var)>0){
        	$params['street_id'] = intval($var);
        }elseif(is_array($var)){
        	$var=array_map(function($a){return intval($a);}, $var);
        	$var=array_filter($var, function($a){if($a!=0){return $a;}});
        	if(count($var)>0){
        		$params['street_id']=$var;
        	}
        }
        
        $var=intval($this->getRequestValue('page'));
        if($var>0){
        	$params['page'] = $var;
        }
        
        $var=trim($this->getRequestValue('order'));
        if($var!=''){
        	$params['order'] = $var;
        }
        
        $var=trim($this->getRequestValue('asc'));
        if($var!=''){
        	$params['asc'] = $var;
        }
        
        $var=trim($this->getRequestValue('active'));
        if($var!=''){
        	$params['active'] = $var;
        }
        
        $var=intval($this->getRequestValue('hot'));
        if($var>0){
        	$params['hot'] = $var;
        }
        
        $var=$this->getRequestValue('id');
        if(!is_array($var) && intval($var)>0){
        	$params['id'] = intval($var);
        }elseif(is_array($var)){
        	$var=array_map(function($a){return intval($a);}, $var);
        	$var=array_filter($var, function($a){if($a!=0){return $a;}});
        	if(count($var)>0){
        		$params['id']=$var;
        	}
        }
        
        $var=intval($this->getRequestValue('status_id'));
        if($var>0){
        	$params['status_id'] = $var;
        }
        
        $var=intval($this->getRequestValue('client_id'));
        if($var>0){
        	$params['client_id'] = $var;
        }
        
        $var=intval($this->getRequestValue('archived'));
        if($var>0){
        	$params['archived'] = $var;
        }
        
        //$params['topic_id'] = $this->getRequestValue('topic_id');
       // $params['order'] = $this->getRequestValue('order');
		//$params['country_id'] = $this->getRequestValue('country_id');
       // $params['region_id'] = $this->getRequestValue('region_id');
        //$params['city_id'] = $this->getRequestValue('city_id');
        //$params['district_id'] = $this->getRequestValue('district_id');
        //$params['metro_id'] = $this->getRequestValue('metro_id');
        //$params['street_id'] = $this->getRequestValue('street_id');
        //$params['page'] = $this->getRequestValue('page');
       // $params['asc'] = $this->getRequestValue('asc');
        $params['price'] = $this->getRequestValue('price');
        //$params['active'] = $this->getRequestValue('active');
        //$params['hot'] = $this->getRequestValue('hot');
		//$params['id'] = (int)$this->getRequestValue('srch_id');
		//$params['status_id'] = intval($this->getRequestValue('status_id'));
		//$params['client_id'] = intval($this->getRequestValue('client_id'));
		//$params['archived'] = intval($this->getRequestValue('archived'));
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable')==1){
			$var=intval($this->getRequestValue('vip_status'));
			if($var>0){
				$params['vip_status'] = $var;
			}
			$var=intval($this->getRequestValue('premium_status'));
			if($var>0){
				$params['premium_status'] = $var;
			}
			$var=intval($this->getRequestValue('bold_status'));
			if($var>0){
				$params['bold_status'] = $var;
			}
			//$params['vip_status'] = (int)$this->getRequestValue('vip_status');
			//$params['premium_status'] = (int)$this->getRequestValue('premium_status');
			//$params['bold_status'] = (int)$this->getRequestValue('bold_status');

		}
		
		
		
		if(isset($this->data_model[$this->table_name]['uniq_id'])){
			$var=intval($this->getRequestValue('uniq_id'));
			if($var>0){
				$params['uniq_id'] = $var;
			}
			//$params['uniq_id'] = (int)$this->getRequestValue('uniq_id');
			$smarty->assign('show_uniq_id', 'true');
		}
		if($this->getRequestValue('srch_export_cian')=='on' || $this->getRequestValue('srch_export_cian')=='1'){
			$var=intval($this->getRequestValue('srch_export_cian'));
			if($var>0){
				$params['srch_export_cian'] = 1;
			}
			//$params['srch_export_cian'] = 1;

		}
		
		$var=$this->getRequestValue('srch_id');
		if(!is_array($var) && intval($var)>0){
			$params['id'] = intval($var);
		}elseif(is_array($var)){
			$var=array_map(function($a){return intval($a);}, $var);
			$var=array_filter($var, function($a){if($a!=0){return $a;}});
			if(count($var)>0){
				$params['id']=$var;
			}
		}
		
		$var=trim($this->getRequestValue('srch_word'));
		if($var!=''){
			$params['srch_word'] = $var;
		}
		$var=trim($this->getRequestValue('srch_phone'));
		if($var!=''){
			$params['srch_phone'] = $var;
		}
		$var=trim($this->getRequestValue('srch_date_from'));
		if($var!=''){
			$params['srch_date_from'] = $var;
		}else{
			$params['srch_date_from'] = 0;
		}
		$var=trim($this->getRequestValue('srch_date_to'));
		if($var!=''){
			$params['srch_date_to'] = $var;
		}else{
			$params['srch_date_to'] = 0;
		}
		*/
		//$params['per_page'] = 10;
		//$params['srch_word'] = $this->getRequestValue('srch_word');
		//$params['srch_phone'] = $this->getRequestValue('srch_phone');
		//$params['srch_date_from'] = $this->getRequestValue('srch_date_from') ? $this->getRequestValue('srch_date_from') : 0;
		//$params['srch_date_to'] = $this->getRequestValue('srch_date_to') ? $this->getRequestValue('srch_date_to') : 0;
        $params['admin'] = true;
        $params['_collect_user_info']=1;
        
        if((1===(int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name']!=='admin') && (1===(int)$this->getConfigValue('data_adv_share_access'))){
        	$params['user_id']=(int)$_SESSION['user_id_value'];
		}
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
        if(intval($this->getConfigValue('admin_grid_leftbuttons'))===1 ){
        	$smarty->assign('admin_grid_leftbuttons', 1);
        }else{
        	$smarty->assign('admin_grid_leftbuttons', 0);
        }
        
        if(1==$this->getConfigValue('use_new_realty_grid')){
        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php';
        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/gridmanager_admin.php';
        	$GMA=new gridmanager_admin();
        	$smarty->assign('grid_data_columns', $GMA->getGridColumns());
        	if(file_exists(SITEBILL_DOCUMENT_ROOT."/template/frontend/".$this->getConfigValue('theme')."/apps/admin/template/realty_grid_wdg.tpl")){
        		$html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT."/template/frontend/".$this->getConfigValue('theme')."/apps/admin/template/realty_grid_wdg.tpl");
        	}else{
        		$html = $smarty->fetch( $smarty->template_dir."/realty_grid_wdg.tpl" );
        		//$html = $smarty->fetch( SITEBILL_DOCUMENT_ROOT."/apps/admin/admin/template/realty_grid_wdg.tpl" );
        	}
			
        }else{
        	if(file_exists(SITEBILL_DOCUMENT_ROOT."/template/frontend/".$this->getConfigValue('theme')."/apps/admin/template/realty_grid.tpl")){
        		$html = $smarty->fetch(SITEBILL_DOCUMENT_ROOT."/template/frontend/".$this->getConfigValue('theme')."/apps/admin/template/realty_grid.tpl");
        	}else{
        		//$html = $smarty->fetch( SITEBILL_DOCUMENT_ROOT."/apps/admin/admin/template/realty_grid.tpl" );
				$html = $smarty->fetch( $smarty->template_dir."/realty_grid.tpl" );
        	}
        }
       	return $html;
    }
    
    function create_admin_grid ( $params ) {
    	$grid_constructor = $this->_getGridConstructor();
   	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    	$Structure_Manager = new Structure_Manager();
    	$category_structure = $Structure_Manager->loadCategoryStructure();
    
    	$res = $this->get_sitebill_adv_ext_by_model( $params );
    	$this->template->assign('category_tree', $grid_constructor->get_category_tree( $params, $category_structure ) );
    	$this->template->assign('breadcrumbs', $grid_constructor->get_category_breadcrumbs( $params, $category_structure, SITEBILL_MAIN_URL ) );
    	$this->template->assign('search_params', json_encode($params) );
    	$this->template->assign('search_url', $_SERVER['REQUEST_URI'] );
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');

    	$data_model = new Data_Model();
    	$_model=$data_model->get_kvartira_model(false, true);
    	$this->template->assign('core_model', $_model['data'] );
    
    	$grid_constructor->get_sales_grid($res);
    }
    
    function add_tags_params ( $params ) {
    	if ( is_array($_SESSION['tags_array']) ) {
    		foreach ( $_SESSION['tags_array'] as $column_name => $column_values ) {
    			$column_values = $this->parse_id_values_from_model($column_name, $column_values, $this->get_model()); 
    			if ( isset($params[$column_name]) and !is_array($params[$column_name]) ) {
    				if ( $params[$column_name] != 0 ) {
    					array_push($column_values, $params[$column_name]);
    				}
    				$params[$column_name] = $column_values;
    			} elseif (isset($params[$column_name]) and is_array($params[$column_name]) ) {
    				$params[$column_name] = array_merge($params[$column_name], $column_values);
    				
    			} elseif ( is_array($column_values) ) {
    				$params[$column_name] = $column_values;
    			}
    		}
    	}
    	return $params;
    }
    
    function parse_id_values_from_model ( $column_name, $column_values, $data_model ) {
    	if ( $data_model[$this->table_name][$column_name]['type'] == 'select_by_query' ) {
    		foreach ( $column_values as $idx => $value ) {
    			$val=$this->data_model_object->get_value_id_by_name($data_model[$this->table_name][$column_name]['primary_key_table'], $data_model[$this->table_name][$column_name]['value_name'], $data_model[$this->table_name][$column_name]['primary_key_name'], $value);
    			
    			if(0!=(int)$val){
    				$column_values[$idx] = $val;
    			}else{
    				unset($column_values[$idx]);
    			}
    			
    		}
    	} elseif ( $data_model[$this->table_name][$column_name]['type'] == 'select_box' and count($column_values) > 0 ) {
    		$select_data = array_flip($data_model[$this->table_name][$column_name]['select_data']);
    		$ra = array();
    		foreach ( $column_values as $idx => $value ) {
    			if ( $select_data[$value] ) {
    				$ra[] = $select_data[$value];
    			}
    		}
    		return $ra;
    	} elseif ( $data_model[$this->table_name][$column_name]['type'] == 'select_box_structure' and count($column_values) > 0 ) {
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
    		$Structure=new Structure_Manager();
    		$x=$Structure->createCatalogChains();
    		$categoryChain=$x['txt'];
    		$categoryChainRev=array_flip($categoryChain);
    		foreach ( $column_values as $idx => $value ) {
    			$value_array = explode(' / ', $value);
    			$var = implode("|", $value_array);
    			$var = mb_strtolower($var);
    			if(isset($categoryChainRev[$var])) {
    				$column_values[$idx] = $categoryChainRev[$var];  
    			} else {
    				unset($column_values[$idx]);
    			}    			
    		}
    	}
    	return $column_values;
    }
    
    /**
     * MUST be moved to Grid_Constructor
     */
    function get_sitebill_adv_ext_by_model( $params, $random = false ) {
    	$params['_sortmodel']=1;
    	$grid_constructor=$this->_getGridConstructor();
	  	$data=$grid_constructor->get_sitebill_adv_core($params);
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');

    	$data_model = new Data_Model();
    	$this->template->assert('pager_array', $data['paging']);
		$this->template->assert('pager', $data['pager']);
    	$this->template->assert('pagerurl', $data['pagerurl']);
    	$this->template->assert('url', $data['url']);
    	$this->template->assert('_total_records', $data['_total_records']);
    	$this->template->assert('_max_page', $data['_max_page']);
    	$this->template->assert('_params', $data['_params']);
    	$_model=$data_model->get_kvartira_model();
   	
	   	$ret=array();

    	$i=0;
    	foreach($data['data'] as $r){
			$ret[$i]=SiteBill::modelSimplification($data_model->init_model_data_from_db('data', 'id', $r['id'], $_model['data'],true));
    		$ret[$i]['_href']=$r['href'];
    		$i++;
    	}
   		return $ret;
    }
    
    function add_tagged_parms_to_where($where_array, $tagged_params) {
    	
    	foreach ( $tagged_params as $column_name => $column_values ) {
    		if ( is_array($column_values) && count($column_values)>0 ) {
    			//$column_values=array_filter($column_values, function($a){if($a!=''){return $a;}});
    			if(!empty($column_values)){
				$type = $this->data_model['data'][$column_name]['type'];
    				if(isset($column_values['min']) || isset($column_values['max'])){
    					if(isset($column_values['min'])){
    						$where_array[]="(re_data.".$column_name." >= '".$column_values['min']."')";
    					}
    					if(isset($column_values['max'])){
    						$where_array[]="(re_data.".$column_name." <= '".$column_values['max']."')";
    					}
				} elseif  ($type == 'client_id') {
				    $where_fio_phone_array = array();
				    foreach ( $column_values as $fio_phone ) {
					list($fio, $phone) = explode(',',$fio_phone);
					$fio = trim($fio);
					$phone = trim($phone);
					$where_fio_phone_array[] = " client_id in (select client_id from ".DB_PREFIX."_client where fio='$fio' and phone='$phone') ";
				    }
				    
    					$where_array[]= implode(' or ', $where_fio_phone_array);
    				}else{
    					$where_array[]="(re_data.".$column_name." IN ('".implode('\',\'', $column_values)."'))";
    				}
    				
    			}
    			
    		}
    	}
    	return $where_array;
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
    		
    		$href=$this->getRealtyHREF($current_id, false, array('topic_id'=>$topic_id, 'alias'=>$form_data['translit_alias']['value']));
    		$rs .= '<div class="row"><a class="btn btn-success pull-right" href="'.$href.'" target="_blank">'.Multilanguage::_('L_SEE_AT_SITE').'</a></div>';
    	}
    	
    	if(1==$this->getConfigValue('apps.geodata.enable')){
    		$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/geodata/js/geodata.js"></script>';
    	}
		$rs .= '<form method="post" class="form-horizontal" action="index.php" enctype="multipart/form-data">';    	
    	/*$id=md5('data_form_'.time());
    	$rs .= '<form method="post" id="'.$id.'" class="form-horizontal" action="index.php" enctype="multipart/form-data">';
    	$rs .= '<script>var control_visibility="'.$id.'";</script>';*/
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
    		$el['controls']['apply']=array('html'=>'<button id="apply_changes" class="btn btn-info">'.Multilanguage::_('L_TEXT_APPLY').'</button>');
    	}
    	$el['controls']['submit']=array('html'=>'<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">'.$button_title.'</button>');
    	
    	$smarty->assign('form_elements',$el);
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data_admin.tpl')){
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data_admin.tpl';
		}elseif(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl')){
    		$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl';
    	}else{
    		$tpl_name=$this->getAdminTplFolder().'/data_form.tpl';
    	}
    	return $smarty->fetch($tpl_name);
    }
    
    
    function checkUniquety($form_data){
    	
    	$unque_fields=trim($this->getConfigValue('apps.realty.uniq_params'));
    	$fields=array();
    	if(''!==$unque_fields){
    		$matches=array();
    		preg_match_all('/([^,\s]+)/i', $unque_fields, $matches);
    		if(!empty($matches[1])){
    			$fields=$matches[1];
    		}
    	}
    	
    	if(!empty($fields)){
    		$where=array();
    		foreach ($fields as $f){
    			if(isset($form_data[$f])){
    				if($form_data[$f]['dbtype']==1 || ($form_data[$f]['dbtype']!='notable' &&  $form_data[$f]['dbtype']!='0')){
    					$where[]='`'.$f.'`=?';
    					$where_val[]=$form_data[$f]['value'];
    				}
    			}
    		}
    	}elseif(isset($form_data['city_id']) && isset($form_data['street_id']) && isset($form_data['number'])){
    		$where[]='`city_id`=?';
    		$where_val[]=(int)$form_data['city_id']['value'];
    		$where[]='`street_id`=?';
    		$where_val[]=(int)$form_data['street_id']['value'];
    		$where[]='`number`=?';
    		$where_val[]=$form_data['number']['value'];
    	}else{
    		return TRUE;
    	}
    	
    	$DBC=DBC::getInstance();
    	
    	$uns=array();
    	$query='SELECT id FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE '.implode(' AND ', $where);
    	
    	$stmt=$DBC->query($query, $where_val);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$uns[]=$ar['id'];
    		}
    	}
    	if(count($uns)>0){
    		$this->riseError('Такое объявление уже существует ('.implode(',', $uns).')');
    		return FALSE;
    	}
    	return TRUE;
    	
    	$query='SELECT COUNT(id) AS cnt FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE '.implode(' AND ', $where);
    	$stmt=$DBC->query($query, $where_val);
    	
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		if($ar['cnt']>0){
    			$this->riseError('Такое объявление уже существует');
    			return FALSE;
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
		
		$id=intval($this->getRequestValue('id'));
		
		$need_send_message=0;
		$status_changed=false;
		
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
				$stmt=$DBC->query($q, array($id));
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
		
		if(1===(int)$this->getConfigValue('notify_about_publishing') || 1===(int)$this->getConfigValue('apps.twitter.enable')){
			$query='SELECT active, hot FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE `'.$this->primary_key.'`=?';

			$stmt=$DBC->query($query, array((int)$this->getRequestValue('id')));
			if($stmt){
				$ar=$DBC->fetch($stmt);
				$current_active_status=$ar['active'];
				$current_hot_status=$ar['hot'];
			}
		}
		
		if(isset($form_data['status_id'])){
			$current_status_id=0;
			$query='SELECT status_id FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE `'.$this->primary_key.'`=?';
			$stmt=$DBC->query($query, array($id));
			if($stmt){
				$ar=$DBC->fetch($stmt);
				$current_status_id=intval($ar['status_id']);
			}
			
			if($current_status_id!==intval($form_data['status_id']['value'])){
				$status_changed=true;
			}
		}
		
		if($this->getConfigValue('notify_about_publishing')){
			
			if($current_active_status==0 AND $form_data['active']['value']==1){
				$need_send_message=1;
			}
			if($current_hot_status==1 AND $form_data['hot']['value']==0){
				$need_send_message=1;
			}
			
			if($need_send_message==1){
				$n_id=$id;
				$n_pass=$form_data['tmp_password']['value'];
				$n_email=$form_data['email']['value'];
				$n_phone=$form_data['phone']['value'];
				$n_fio=$form_data['fio']['value'];
				
				$user_id=$form_data['user_id']['value'];
				if($user_id>0){
					$DBC=DBC::getInstance();
					$query='SELECT email, phone, user_id, fio, group_id, login FROM '.DB_PREFIX.'_user WHERE user_id=?';
					$stmt=$DBC->query($query, array($user_id));
					if($stmt){
						$ar=$DBC->fetch($stmt);
						if($ar['login']!='_unregistered'){
							$n_pass=$form_data['tmp_password']['value'];
							$n_email=$ar['email'];
							$n_phone=$ar['phone'];
							$n_fio=$ar['fio'];
						}
					}
				}
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
			}elseif(strpos($form_data['youtube']['value'], 'youtu.be') !== FALSE){
				$d = parse_url($form_data['youtube']['value']);
				if (isset($d['path']) && trim($d['path'], '/')!='' && strpos(trim($d['path'], '/'), '/')===false) {
					$y_id=trim($d['path'], '/');
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
	    
	    $query_params = $data_model->get_prepared_edit_query(DB_PREFIX.'_'.$this->table_name, $this->primary_key, $form_data[$this->primary_key]['value'], $form_data);
	    $query_params_vals=$query_params['p'];
	    $query=$query_params['q'];
	    
	    $stmt=$DBC->query($query, $query_params_vals, $rows, $success_mark);
	    if ( !$success_mark ) {
	    	$this->riseError($DBC->getLastError());
	    }
	   	    
	    $imgs=array();

	    foreach ($form_data as $form_item){
	    	if($form_item['type']=='uploads'){
	    		$ims=$this->appendUploads($this->table_name, $form_item, $this->primary_key, $id);
	    		if(is_array($ims) && count($ims)>0 && 0==intval($form_item['parameters']['no_watermark'])){
	    			$imgs=array_merge($imgs, $ims);
	    		}
	    	}elseif($form_item['type']=='docuploads'){
	    		$imgs_uploads = $this->appendDocUploads($this->table_name, $form_item, $this->primary_key, $id);
	    	}elseif($form_item['type']=='select_by_query_multi'){
	    		//echo 1;
	    		$vals=$form_item['value'];
	    		if(!is_array($vals)){
	    			$vals=(array)$vals;
	    		}
	    		$query='DELETE FROM '.DB_PREFIX.'_multiple_field WHERE `table_name`=? AND `field_name`=? AND `primary_id`=?';
	    		$stmt=$DBC->query($query, array($this->table_name, $form_item['name'], $id));
	    		//echo $DBC->getLastError();
	    		if(!empty($vals)){
	    			//refresh
	    			$query='INSERT INTO '.DB_PREFIX.'_multiple_field (`table_name`, `field_name`, `primary_id`, `field_value`) VALUES (?,?,?,?)';
	    			foreach($vals as $val){
	    				$stmt=$DBC->query($query, array($this->table_name, $form_item['name'], $id, $val));
	    			}
	    		}
	    	}
	    }
	   	    
	    $ims=$this->editImageMulti('data', 'data', $this->primary_key, $id);
	    if(is_array($ims) && count($ims)>0){
	    	$imgs=array_merge($imgs, $ims);
	    }
	    
	    if(1==$this->getConfigValue('apps.seo.data_alias_enable') && ((isset($form_data['translit_alias']) && $form_data['translit_alias']['value']=='') || !isset($form_data['translit_alias']))){
	    	$this->saveTranslitAlias($id);
	    }
	    
	 	if($status_changed){
	    	$this->setStatusDate($id);
	    }
		
		/*Send notify messages*/
	    if($need_send_message){
			if($n_email!=''){
				$this->notifyEmailAboutActivation($n_id, $n_email, array('fio'=>$n_fio));
			}elseif($n_phone!='' and file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/sms/admin/admin.php')){
				$body=$this->getConfigValue('apps.fasteditor.sms_send_password_text_long');
				$body=str_replace('{password}', $n_pass, $body);
				require_once (SITEBILL_DOCUMENT_ROOT.'/apps/sms/admin/admin.php');
	    		$SMSSender=new sms_admin();
	    		if($SMSSender->send($n_phone, $body)){
	    		
	    		}else{
	    		
	    		}
			}
		}
		
		/*Add twit*/

		if($this->getConfigValue('apps.twitter.enable')){
			if($current_active_status==0 AND $form_data['active']['value']==1){
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
			
			if(defined('STR_MEDIA') && STR_MEDIA==Sitebill::MEDIA_SAVE_FOLDER){

				$copy_folder=MEDIA_FOLDER.'/nowatermark/';
               	if(defined('STR_MEDIA_FOLDERFDAYS') && STR_MEDIA_FOLDERFDAYS===1){
    				$foldeformat='Ymd';
    			}else{
    				$foldeformat='Ym';
    			}
    			$folder_name=date($foldeformat, time());
               	$locs=$copy_folder.'/'.$folder_name;
               	if(!is_dir($locs)){
               		mkdir($locs);
               	}
               	if(1==$this->getConfigValue('save_without_watermark') && !empty($imgs)){
               		$copy_folder=SITEBILL_DOCUMENT_ROOT.'/img/data/nowatermark';
               		foreach($imgs as $v){
               			copy($filespath.$v['normal'], $copy_folder.'/'.$v['normal']);
               		}
               	}
               	if(!empty($imgs)){
               		foreach($imgs as $v){
               			$Watermark->printWatermark(MEDIA_FOLDER.'/'.$v['normal']);
               		}
               	}
			}else{
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
			/*
			if(defined('STR_MEDIA') && STR_MEDIA=='new'){

				if(!empty($imgs)){

					foreach($imgs as $v){

						$file_name_parts=explode('/', $v['normal']);

						$file_name=end($file_name_parts);

						$file_name=preg_replace('/\.src\./', '.wtr.', $file_name);

						array_pop($file_name_parts);

						$file_name_parts[]=$file_name;

						//copy(MEDIA_FOLDER.'/'.$v['normal'], MEDIA_FOLDER.'/'.implode('/', $file_name_parts));
						copy(MEDIA_FOLDER.'/'.$v['normal'], MEDIA_FOLDER.'/'.implode('/', $file_name_parts));

						$Watermark->printWatermark(MEDIA_FOLDER.'/'.implode('/', $file_name_parts));

					}

				}

			

			}elseif(defined('STR_MEDIA') && STR_MEDIA=='semi'){
				$copy_folder=MEDIA_FOLDER.'/nowatermark/';
               	$folder_name=date('Ym', time());
               	$locs=$copy_folder.'/'.$folder_name;
               	if(!is_dir($locs)){
               		mkdir($locs);
               	}
               	if(1==$this->getConfigValue('save_without_watermark') && !empty($imgs)){

               		$copy_folder=SITEBILL_DOCUMENT_ROOT.'/img/data/nowatermark';

               		foreach($imgs as $v){

               			copy($filespath.$v['normal'], $copy_folder.'/'.$v['normal']);

               		}

               	}
               	if(!empty($imgs)){

               		foreach($imgs as $v){

               			$Watermark->printWatermark(MEDIA_FOLDER.'/'.$v['normal']);

               		}

               	}
            }else{
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
			*/
		}
		$page=$this->getRequestValue('page');
		$_POST=array();
	    $_POST['page']=$page;
	    return $id;
	}
	
	public function notifyEmailAboutActivation($n_id, $n_email, $data=array()){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
		$DBC=DBC::getInstance();
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
		
		$href=$this->getRealtyHREF($n_id, true, array('topic_id'=>$seo_data['topic_id'], 'alias'=>$seo_data['translit_alias']));
		$tpl=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps/system/template/mails/reguser_pub_notify.tpl';
		global $smarty;
		if(isset($data['fio']) && $data['fio']!=''){
			$smarty->assign('mail_fio', $data['fio']);
			$smarty->assign('fio', $data['fio']);
		}else{
			$smarty->assign('mail_fio', '');
			$smarty->assign('fio', '');
		}
		$smarty->assign('href', $href);
		$smarty->assign('edit_url', $this->getServerFullUrl().'/account/data/?do=edit&id='.$n_id);
		$smarty->assign('mail_adv_id', $n_id);

		$smarty->assign('mail_signature', $this->getConfigValue('email_signature'));
		if(file_exists($tpl)){
			$body=$smarty->fetch($tpl);
		}else{
			$body=Multilanguage::_('YOUR_AD_PUBLISHED','system').'<br />';
			$body.=Multilanguage::_('AD_LINK','system').' <a href="'.$href.'">'.$href.'</a><br />';
		}
		$subject = $_SERVER['SERVER_NAME'].': '.Multilanguage::_('YOUR_AD_PUBLISHED_SUBJ','system');
		$from = $this->getConfigValue('system_email');
		
		$this->template->assign('HTTP_HOST', $_SERVER['HTTP_HOST']);
		$email_template_fetched = $this->fetch_email_template('data_moderate_success');

		if ( $email_template_fetched ) {
		    $subject = $email_template_fetched['subject'];
		    $body = $email_template_fetched['message'];

		    $message_array['apps_name'] = 'need_moderate';
		    $message_array['method'] = __METHOD__;
		    $message_array['message'] = "subject = $subject, message = $body";
		    $message_array['type'] = '';
		    //$this->writeLog($message_array);
		}
		
		$this->sendFirmMail($n_email, $from, $subject, $body);
	}
	
	protected function _set_statusAction() {
		$set_status_id = (int)$this->getRequestValue('set_status_id');
		$data_id = (int)$this->getRequestValue('id');
		$this->setStatusState($data_id, $set_status_id);
		if ( $this->getError() ) {
			 echo $this->GetErrorMessage();
		}
		//echo 'set status action';
		return $this->grid();
	}
	
	public function setStatusState ( $data_id, $status_id ) {
		$DBC=DBC::getInstance();
		$query='UPDATE '.DB_PREFIX.'_'.$this->table_name.' SET status_id=? WHERE `'.$this->primary_key.'`=?';
		
		$stmt=$DBC->query($query, array($status_id, $data_id), $row, $success);
		if ( !$success ) {
			$this->riseError($DBC->getLastError());
			return false;
		}
		
		$this->setStatusDate($data_id);
	}
	
	
	public function setStatusDate($id, $date=''){
		$DBC=DBC::getInstance();
		if($date==''){
			$date=date('Y-m-d H:i:s', time());
		}
		$query='UPDATE '.DB_PREFIX.'_'.$this->table_name.' SET status_change=? WHERE `'.$this->primary_key.'`=?';
		$stmt=$DBC->query($query, array($date, $id), $row, $success);
		if ( !$success ) {
			$this->riseError($DBC->getLastError());
			return false;
		}
	}
	
	public function getDataStatInfo($params=array()){
		$statuses=array();
		$activities=array();
		
		$DBC=DBC::getInstance();
		
		$query='SELECT active, COUNT(id) AS _cnt FROM '.DB_PREFIX.'_data GROUP BY active';
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$activities[$ar['active']]=$ar['_cnt'];
			}
		}
		
		if(!empty($params)){
			foreach($params as $f){
				$query='SELECT `'.$f.'`, COUNT(id) AS _cnt FROM '.DB_PREFIX.'_data GROUP BY `'.$f.'`';
				$stmt=$DBC->query($query);
				if($stmt){
					while($ar=$DBC->fetch($stmt)){
						$statuses[$f][$ar[$f]]=$ar['_cnt'];
					}
				}
			}
		}
		return array('status'=>$statuses, 'active'=>$activities, 'total'=>array_sum($activities));
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
		}elseif(strpos($form_data['youtube']['value'], 'youtu.be') !== FALSE){
			$d = parse_url($form_data['youtube']['value']);
			if (isset($d['path']) && trim($d['path'], '/')!='' && strpos(trim($d['path'], '/'), '/')===false) {
				$y_id=trim($d['path'], '/');
			}
		}else{
			if(preg_match('/.*([-_A-Za-z0-9]+).*/',$form_data['youtube']['value'],$matches)){
				$y_id=$matches[0];
			}
		}
		$form_data['youtube']['value']=$y_id;
		$form_data['price']['value']=str_replace(' ', '', $form_data['price']['value']);
		//$form_data['date_added']['value'] = date('Y-m-d H:i:s', time());
		
		if(1==$this->getConfigValue('apps.geodata.try_encode') && 1==$this->getConfigValue('apps.geodata.enable')){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/geodata/admin/admin.php';
			$GA=new geodata_admin();
			$form_data=$GA->try_geocode($form_data);
		}
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    $queryp = $data_model->get_prepared_insert_query(DB_PREFIX.'_'.$this->table_name, $form_data, $language_id);
	    
	    $DBC=DBC::getInstance();
	    
	    $stmt=$DBC->query($queryp['q'], $queryp['p'], $row, $success_mark);
	    if ( !$success_mark ) {
	    	$this->riseError($DBC->getLastError());
	    	return false;
	    }
	    
		$new_record_id = $DBC->lastInsertId();
	    
	    if($new_record_id>0 && isset($form_data['status_id'])){
	    	$this->setStatusDate($new_record_id);
	    }
	    
	    $imgs=array();
	    
	    foreach ($form_data as $form_item){
	    	if($form_item['type']=='uploads'){
	    		$ims=$this->appendUploads($this->table_name, $form_item, $this->primary_key, $new_record_id);
	    		if(is_array($ims) && count($ims)>0 && 0==intval($form_item['parameters']['no_watermark'])){
	    			$imgs=array_merge($imgs, $ims);
	    		}
	    	}elseif($form_item['type']=='docuploads'){
	    		$imgs_uploads = $this->appendDocUploads($this->table_name, $form_item, $this->primary_key, $new_record_id);
	    	}elseif($form_item['type']=='select_by_query_multi'){
	    		$vals=$form_item['value'];
	    		if(!is_array($vals)){
	    			$vals=(array)$vals;
	    		}
	    		$query='DELETE FROM '.DB_PREFIX.'_multiple_field WHERE `table_name`=? AND `field_name`=? AND `primary_id`=?';
	    		$stmt=$DBC->query($query, array($this->table_name, $form_item['name'], $new_record_id));
	    		//echo $DBC->getLastError();
	    		if(!empty($vals)){
	    			//refresh
	    			$query='INSERT INTO '.DB_PREFIX.'_multiple_field (`table_name`, `field_name`, `primary_id`, `field_value`) VALUES (?,?,?,?)';
	    			foreach($vals as $val){
	    				$stmt=$DBC->query($query, array($this->table_name, $form_item['name'], $new_record_id, $val));
	    			}
	    		}
	    		
	    	}
		}
	    
	    $ims=$this->editImageMulti('data', 'data', 'id', $new_record_id);
		if(is_array($ims) && count($ims)>0){
    		$imgs=array_merge($imgs, $ims);
    	}
		if(1==$this->getConfigValue('apps.seo.data_alias_enable') && ((isset($form_data['translit_alias']) && $form_data['translit_alias']['value']=='') || !isset($form_data['translit_alias']))){
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
			
			if(defined('STR_MEDIA') && STR_MEDIA==Sitebill::MEDIA_SAVE_FOLDER){

				$copy_folder=MEDIA_FOLDER.'/nowatermark/';
               	if(defined('STR_MEDIA_FOLDERFDAYS') && STR_MEDIA_FOLDERFDAYS===1){
    				$foldeformat='Ymd';
    			}else{
    				$foldeformat='Ym';
    			}
    			$folder_name=date($foldeformat, time());
               	$locs=$copy_folder.'/'.$folder_name;
               	if(!is_dir($locs)){
               		mkdir($locs);
               	}
               	if(1==$this->getConfigValue('save_without_watermark') && !empty($imgs)){
               		$copy_folder=SITEBILL_DOCUMENT_ROOT.'/img/data/nowatermark';
               		foreach($imgs as $v){
               			copy($filespath.$v['normal'], $copy_folder.'/'.$v['normal']);
               		}
               	}
               	if(!empty($imgs)){
               		foreach($imgs as $v){
               			$Watermark->printWatermark(MEDIA_FOLDER.'/'.$v['normal']);
               		}
               	}

			}else{

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
			/*
			if(defined('STR_MEDIA') && STR_MEDIA=='new'){
				if(!empty($imgs)){
					foreach($imgs as $v){
						$file_name_parts=explode('/', $v['normal']);
						$file_name=end($file_name_parts);
						$file_name=preg_replace('/\.src\./', '.wtr.', $file_name);
						array_pop($file_name_parts);
						$file_name_parts[]=$file_name;
						//copy(MEDIA_FOLDER.'/'.$v['normal'], MEDIA_FOLDER.'/'.implode('/', $file_name_parts));
						copy(MEDIA_FOLDER.'/'.$v['normal'], MEDIA_FOLDER.'/'.implode('/', $file_name_parts));
						$Watermark->printWatermark(MEDIA_FOLDER.'/'.implode('/', $file_name_parts));
					}
				}
			
			}elseif(defined('STR_MEDIA') && STR_MEDIA=='semi'){
				$copy_folder=MEDIA_FOLDER.'/nowatermark/';
               	$folder_name=date('Ym', time());
               	$locs=$copy_folder.'/'.$folder_name;
               	if(!is_dir($locs)){
               		mkdir($locs);
               	}
               	if(1==$this->getConfigValue('save_without_watermark') && !empty($imgs)){
               		$copy_folder=SITEBILL_DOCUMENT_ROOT.'/img/data/nowatermark';
               		foreach($imgs as $v){
               			copy($filespath.$v['normal'], $copy_folder.'/'.$v['normal']);
               		}
               	}
               	if(!empty($imgs)){
               		foreach($imgs as $v){
               			$Watermark->printWatermark(MEDIA_FOLDER.'/'.$v['normal']);
               		}
               	}
            }else{
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
			*/
			
			
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
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $params = array();
        $params[] = 'action=data';
      
        if(''!=trim($this->getRequestValue('active'))){
        	$params[] = 'active='.trim($this->getRequestValue('active'));
        }
        if(''!=trim($this->getRequestValue('hot'))){
        	$params[] = 'hot='.trim($this->getRequestValue('hot'));
        }
        if(0!=intval($this->getRequestValue('status_id'))){
        	$params[] = 'status_id='.intval($this->getRequestValue('status_id'));
        }
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
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ret[]=$ar;
			}
		}
        return $ret;
	}
	
	function getAdditionalSearchForm(){
		$query='select * from re_user order by fio';
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		$ret='';
		if($stmt){
			$ret.='<form method="post">';
			$ret.='<select name="user_id" style="width: 200px;" onchange="this.form.submit()">';
			$ret.='<option value="">'.Multilanguage::_('L_CHOOSE_USER').'</option>';
			while($ar=$DBC->fetch($stmt)){
				if($this->getRequestValue('user_id')==$ar['user_id']){
					$ret.='<option value="'.$ar['user_id'].'" selected="selected">'.$ar['login'].' ('.$ar['fio'].')</option>';
				}else{
					$ret.='<option value="'.$ar['user_id'].'">'.$ar['login'].' ('.$ar['fio'].')</option>';
				}
			}
			$ret.='</select>';
			$ret.='<input type="hidden" name="action" value="'.$this->action.'">';
			$ret .= '<input type="submit" name="submit" value="'.Multilanguage::_('L_TEXT_SELECT').'">';
			$ret.='</form>';
		}
		return $ret;
	}
	
	function getUserSelectBox(){
		$query='select * from re_user order by fio';
		$DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		$ret='';
		if($stmt){
			$ret.='<select name="user_id" onchange="this.form.submit()">';
			$ret.='<option value="">'.Multilanguage::_('L_CHOOSE_USER').'</option>';
			while($ar=$DBC->fetch($stmt)){
				if($this->getRequestValue('user_id')==$ar['user_id']){
					$ret.='<option value="'.$ar['user_id'].'" selected="selected">'.$ar['login'].' ('.$ar['fio'].')</option>';
				}else{
					$ret.='<option value="'.$ar['user_id'].'">'.$ar['login'].' ('.$ar['fio'].')</option>';
				}
			}
			$ret.='</select>';
		}
		return $ret;
	}
	
	function mass_delete_data($table_name, $primary_key, $ids){
		$errors='';
		if((1===(int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name']!=='admin') && (1===(int)$this->getConfigValue('data_adv_share_access'))){
			$cuser_id=(int)$_SESSION['user_id_value'];
			if(count($ids)>0){
				foreach($ids as $k=>$id){
					if(!$this->checkOwning($id, $cuser_id)){
						unset($ids[$k]);
					}
				}
			}
		}
		
		if(count($ids)>0){
			if(1==(int)$this->getConfigValue('apps.realty.use_predeleting')){
				$DBC=DBC::getInstance();
				$query='UPDATE '.DB_PREFIX.'_data SET archived=1 WHERE `id` IN ('.implode(',', $ids).')';
				$stmt=$DBC->query($query);
				header('location: ?action='.$this->action);
				exit();
			}else{
				foreach($ids as $id){
					$log_id=false;
					if($this->getConfigValue('apps.realtylog.enable')){
			        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylog/admin/admin.php';
		        		$Logger=new realtylog_admin();
		        		$log_id=$Logger->addLog($id, $_SESSION['user_id_value'], 'delete', $this->table_name);
			        }
			        if($this->getConfigValue('apps.realtylogv2.enable')){
	
			        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/admin.php';
	
			        	$Logger=new realtylogv2_admin();
	
			        	$log_id=$Logger->addLog($id, $_SESSION['user_id_value'], 'delete', $this->table_name, $this->primary_key);
	
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
				if($errors!=''){
					$rs.=$errors.'<div align="center"><a href="?action='.$this->action.'">ОК</a></div>';
				}else{
					header('location: ?action='.$this->action);
					exit();
					$rs .= $this->grid($user_id);
				}
				return $rs;
			}
			return $rs;
			
		}
		
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
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    $form_data = $this->data_model;
	    if(isset($form_data[$this->table_name][$element_name])){
	    	$fd[$this->table_name][$element_name]=$form_data[$this->table_name][$element_name];
	    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
	    	$form_generator = new Form_Generator();
	    	$element_data=$form_generator->compile_form_elements($fd[$this->table_name], false);
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
	
	protected function _mass_deletebypropAction(){
		$rs='';
		
		$prop=$this->getRequestValue('prop');
		$prop_value=$this->getRequestValue('prop_value');
		$DBC=DBC::getInstance();
		$query='SELECT id FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE `'.$prop.'`=?';
		$stmt=$DBC->query($query, array($prop_value));
		$id_array=array();
		if($stmt){
			while ($ar=$DBC->fetch($stmt)){
				$id_array[]=$ar['id'];
			}
		}
		
		if(!empty($id_array)){
			$this->setRequestValue('ids', implode(',', $id_array));
		}
		$rs=$this->_mass_deleteAction();
		return $rs;
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
	
	protected function _viewAction(){
		global $smarty;
		$id=intval($this->getRequestValue('id'));
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$form_data_shared = $data_model->get_kvartira_model(false, true);
		$form_data_shared = $data_model->init_model_data_from_db ( 'data', 'id', $id, $form_data_shared['data'], true );
		
		if(!$form_data_shared){
			return '';
		}
        		        		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/view.php');
		$table_view = new Table_View();
		$order_table = '';
		$order_table .= '<table class="table">';
		$order_table .= $table_view->compile_view($form_data_shared);
		$order_table .= '</table>';
		
		$notes=array();
		$DBC=DBC::getInstance();
		$query='SELECT dn.*, u.fio FROM '.DB_PREFIX.'_data_note dn LEFT JOIN '.DB_PREFIX.'_user u USING(user_id) WHERE dn.id=? ORDER BY dn.added_at ASC';
		$stmt=$DBC->query($query, array($id));
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$notes[]=$ar;
			}
		}
		$smarty->assign('view_data_notes', $notes);
		$smarty->assign('view_data', $order_table);
		$html = $smarty->fetch( $smarty->template_dir."/realty_view.tpl" );
		return $html;
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
		
		foreach($ids as $id){
			$form_data[$this->table_name] = $data_model->init_model_data_from_db ( $this->table_name, $this->primary_key, $id, $form_data[$this->table_name] );
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
			if($value['type']=='attachment' || $value['type']=='photo' || $value['type']=='uploadify_image' || $value['type']=='uploads' || $value['type']=='avatar'){
				unset($form_data[$this->table_name][$key]);
			}
		}
		if(isset($_POST['submit'])){
			$need_to_update=$this->getRequestValue('batch_update');
			$ids=$this->getRequestValue('batch_ids');
			if((1===(int)$this->getConfigValue('check_permissions')) && ($_SESSION['current_user_group_name']!=='admin') && (1===(int)$this->getConfigValue('data_adv_share_access'))){
				$cuser_id=(int)$_SESSION['user_id_value'];
				if(count($ids)>0){
					foreach($ids as $k=>$id){
						if(!$this->checkOwning($id, $cuser_id)){
							unset($ids[$k]);
						}
					}
				}
			}
			
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