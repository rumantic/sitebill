<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * News admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class user_news_admin extends news_admin {
    
	function grid ($params = array(), $default_params = array()) {
    	
    	$params=array();
    	$params['action']=$this->action;
    	
    	
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/page.php');
        $common_page = new Common_Page();
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/tab.php');
    	$common_tab = new Common_Tab();
		$url='/admin/index.php?action='.$this->action;
		
		$common_grid->set_grid_table($this->table_name);
		$common_grid->set_grid_url(SITEBILL_MAIN_URL.'/account_news/');
		$common_grid->add_grid_item('news_id');
        $common_grid->add_grid_item('date');
    	$common_grid->add_grid_item('title');
    	$common_grid->add_grid_item('anons');
    	if($this->use_topics){
    		$common_grid->add_grid_item('news_topic_id');
    	}
		
    	$common_grid->set_conditions(array('user_id'=>$this->getSessionUserId()));
        
        $common_grid->add_grid_control('edit');
        $common_grid->add_grid_control('delete');
		//$common_grid->set_grid_query("SELECT * FROM ".DB_PREFIX."_".$this->table_name." ORDER BY date DESC, news_id DESC");
		$params['page']=$this->getRequestValue('page');
		$params['per_page']=$this->getConfigValue('common_per_page');
		//$params['user_id']=$this->getSessionUserId();
        
        $common_grid->setPagerParams($params);
        
        $common_page->setTab($common_tab);
        $common_page->setGrid($common_grid);
        
		$rs .= $common_page->toString();
		return $rs;
    }
    
    function getTopMenu(){
    	$rs.='<a href="?action='.$this->action.'&section=news" class="btn btn-primary">Все новости</a>';
    	$rs.=' <a href="?action='.$this->action.'&section=news&do=new" class="btn btn-primary">Добавить новость</a>';
    	return $rs;
    }
    
    protected function _editAction(){
    	$rs='';
    	if(!$this->checkOwner($this->getSessionUserId(), $this->getRequestValue($this->primary_key))){
    		return $rs;
    	}
    	 
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	unset($form_data[$this->table_name]['user_id']);
    	 
    	if ( $this->getRequestValue('subdo') == 'delete_image' ) {
    		$this->deleteImage($this->table_name, $this->getRequestValue('image_id'));
    	}
    	 
    	if ( $this->getRequestValue('subdo') == 'up_image' ) {
    		$this->reorderImage($this->table_name, $this->getRequestValue('image_id'), $this->primary_key, $this->getRequestValue($this->primary_key),'up');
    	}
    	 
    	if ( $this->getRequestValue('subdo') == 'down_image' ) {
    		$this->reorderImage($this->table_name, $this->getRequestValue('image_id'), $this->primary_key, $this->getRequestValue($this->primary_key), 'down');
    	}
    	 
    	if ( $this->getRequestValue('language_id') > 0 and !$this->language->get_version($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $this->getRequestValue('language_id')) ) {
    		$rs = $this->get_form($form_data[$this->table_name], 'new', $this->getRequestValue('language_id'));
    	} else {
    		if ( $this->getRequestValue('language_id') > 0 ) {
    			$form_data[$this->table_name] = $data_model->init_model_data_from_db_language ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name], false, $this->getRequestValue('language_id') );
    		} else {
    			$form_data[$this->table_name] = $data_model->init_model_data_from_db ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name] );
    		}
    		$rs = $this->get_form($form_data[$this->table_name], 'edit', 0, '', SITEBILL_MAIN_URL.'/account_news/');
    	}
    	return $rs;
    }
    
    protected function _deleteAction(){
    	$rs='';
    	if(!$this->checkOwner($this->getSessionUserId(), $this->getRequestValue($this->primary_key))){
    		return $rs;
    	}
    	if($this->getConfigValue('apps.realtylog.enable')){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylog/admin/admin.php';
    		$Logger=new realtylog_admin();
    		$Logger->addLog($this->getRequestValue($this->primary_key), $_SESSION['user_id_value'], 'delete', $this->table_name);
    	}
    	if($this->getConfigValue('apps.shoplog.enable')){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/shoplog/admin/admin.php';
    		$Logger=new shoplog_admin();
    		$Logger->addLog($this->getRequestValue($this->primary_key), $_SESSION['user_id_value'], 'delete', $this->table_name);
    	}
    	if($this->getConfigValue('apps.realtylogv2.enable')){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/admin.php';
    		$Logger=new realtylogv2_admin();
    		$Logger->addLog($this->getRequestValue($this->primary_key), $_SESSION['user_id_value'], 'delete', $this->table_name, $this->primary_key);
    	}
    	$this->delete_data($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
    	if ( $this->getError() ) {
    		$rs .= '<div align="center">'.Multilanguage::_('L_ERROR_ON_DELETE').': '.$this->GetErrorMessage().'<br>';
    		$rs .= '<a href="?action='.$this->action.'">ОК</a>';
    		$rs .= '</div>';
    	} else {
    		$rs .= $this->grid();
    	}
    	return $rs;
    }
    
    protected function _edit_doneAction(){
    	$rs='';
    	if(!$this->checkOwner($this->getSessionUserId(), $this->getRequestValue($this->primary_key))){
    		return $rs;
    	}
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	 
    	$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
    	$form_data[$this->table_name]['user_id']['value']=$this->getSessionUserId();
    	
    	$new_values=$this->getRequestValue('_new_value');
    	if(1==$this->getConfigValue('use_combobox') && count($new_values)>0){
    		$remove_this_names=array();
    		foreach($form_data[$this->table_name] as $fd){
    			if(isset($new_values[$fd['name']]) && $new_values[$fd['name']]!='' && $fd['combo']==1){
    				$id=md5(time().'_'.rand(100,999));
    				$remove_this_names[]=$id;
    				$form_data[$this->table_name][$id]['value'] = $new_values[$fd['name']];
    				$form_data[$this->table_name][$id]['type'] = 'auto_add_value';
    				$form_data[$this->table_name][$id]['dbtype'] = 'notable';
    				$form_data[$this->table_name][$id]['value_table'] = $form_data[$this->table_name][$fd['name']]['primary_key_table'];
    				$form_data[$this->table_name][$id]['value_primary_key'] = $form_data[$this->table_name][$fd['name']]['primary_key_name'];
    				$form_data[$this->table_name][$id]['value_field'] = $form_data[$this->table_name][$fd['name']]['value_name'];
    				$form_data[$this->table_name][$id]['assign_to'] = $fd['name'];
    				$form_data[$this->table_name][$id]['required'] = 'off';
    				$form_data[$this->table_name][$id]['unique'] = 'off';
    			}
    		}
    	}
    	$data_model->forse_auto_add_values($form_data[$this->table_name]);
    	//$data_model->clear_auto_add_values($form_data[$this->table_name]);
    	if ( !$this->check_data( $form_data[$this->table_name] ) ) {
    		$form_data['data']=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
    		$rs = $this->get_form($form_data[$this->table_name], 'edit', 0, '', SITEBILL_MAIN_URL.'/account_news/');
    	} else {
    		$this->edit_data($form_data[$this->table_name]);
    		if ( $this->getError() ) {
    			$form_data['data']=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
    			$rs = $this->get_form($form_data[$this->table_name], 'edit', 0, '', SITEBILL_MAIN_URL.'/account_news/');
    		} else {
    			if($this->getConfigValue('apps.realtylog.enable')){
    				require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylog/admin/admin.php';
    				$Logger=new realtylog_admin();
    				$Logger->addLog($form_data['data']['id']['value'], $_SESSION['user_id_value'], 'edit', $this->table_name);
    			}
    			if($this->getConfigValue('apps.shoplog.enable')){
    				require_once SITEBILL_DOCUMENT_ROOT.'/apps/shoplog/admin/admin.php';
    				$Logger=new shoplog_admin();
    				$Logger->addLog($form_data[$this->table_name][$this->primary_key]['value'], $_SESSION['user_id_value'], 'edit', $this->table_name);
    			}
    			if($this->getConfigValue('apps.realtylogv2.enable')){
    				require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/admin.php';
    				$Logger=new realtylogv2_admin();
    				$Logger->addLog($form_data['data']['id']['value'], $_SESSION['user_id_value'], 'edit', $this->table_name, $this->primary_key);
    			}
    			$rs .= $this->grid();
    		}
    	}
    	return $rs;
    }
    
    protected function _newAction(){
    	$rs='';
    
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	unset($form_data[$this->table_name]['user_id']);
    	$rs = $this->get_form($form_data[$this->table_name], 'new', 0, '', SITEBILL_MAIN_URL.'/account_news/');
    	return $rs;
    }
    
    protected function _new_doneAction(){
    	$rs='';
    
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    
    	$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
    	$form_data[$this->table_name]['user_id']['value']=$this->getSessionUserId();
    	
    	$new_values=$this->getRequestValue('_new_value');
    	if(1==$this->getConfigValue('use_combobox') && count($new_values)>0){
    		$remove_this_names=array();
    		foreach($form_data[$this->table_name] as $fd){
    			if(isset($new_values[$fd['name']]) && $new_values[$fd['name']]!='' && $fd['combo']==1){
    				$id=md5(time().'_'.rand(100,999));
    				$remove_this_names[]=$id;
    				$form_data[$this->table_name][$id]['value'] = $new_values[$fd['name']];
    				$form_data[$this->table_name][$id]['type'] = 'auto_add_value';
    				$form_data[$this->table_name][$id]['dbtype'] = 'notable';
    				$form_data[$this->table_name][$id]['value_table'] = $form_data[$this->table_name][$fd['name']]['primary_key_table'];
    				$form_data[$this->table_name][$id]['value_primary_key'] = $form_data[$this->table_name][$fd['name']]['primary_key_name'];
    				$form_data[$this->table_name][$id]['value_field'] = $form_data[$this->table_name][$fd['name']]['value_name'];
    				$form_data[$this->table_name][$id]['assign_to'] = $fd['name'];
    				$form_data[$this->table_name][$id]['required'] = 'off';
    				$form_data[$this->table_name][$id]['unique'] = 'off';
    			}
    		}
    	}
    	$data_model->forse_auto_add_values($form_data[$this->table_name]);
    	if ( !$this->check_data( $form_data[$this->table_name] ) || (1==$this->getConfigValue('filter_double_data') && !$this->checkUniquety($form_data[$this->table_name]))  ) {
    		$form_data['data']=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
    		$rs = $this->get_form($form_data[$this->table_name], 'new', 0, '', SITEBILL_MAIN_URL.'/account_news/');
    
    	} else {
    		$new_record_id=$this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
    		if ( $this->getError() ) {
    			$form_data['data']=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
    			$rs = $this->get_form($form_data[$this->table_name], 'new', 0, '', SITEBILL_MAIN_URL.'/account_news/');
    		} else {
    			if($this->getConfigValue('apps.realtylog.enable')){
    				require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylog/admin/admin.php';
    				$Logger=new realtylog_admin();
    				$Logger->addLog($new_record_id, $_SESSION['user_id_value'], 'new', $this->table_name);
    			}
    			if($this->getConfigValue('apps.shoplog.enable')){
    				require_once SITEBILL_DOCUMENT_ROOT.'/apps/shoplog/admin/admin.php';
    				$Logger=new shoplog_admin();
    				$Logger->addLog($new_record_id, $_SESSION['user_id_value'], 'new', $this->table_name);
    			}
    			if($this->getConfigValue('apps.realtylogv2.enable')){
    				require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/admin.php';
    				$Logger=new realtylogv2_admin();
    				$Logger->addLog($new_record_id, $_SESSION['user_id_value'], 'new', $this->table_name, $this->primary_key);
    			}
    			$rs .= $this->grid();
    		}
    	}
    	return $rs;
    }
    
    private function checkOwner($user_id, $id){
    	$DBC=DBC::getInstance();
    	$query='SELECT COUNT('.$this->primary_key.') as cnt FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE user_id=? AND '.$this->primary_key.'=?';
    	$stmt=$DBC->query($query, array($user_id, $id));
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		if($ar['cnt']==1){
    			return true;
    		}
    	}
    	return false;
    }
}