<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Object manager
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Object_Manager extends SiteBill {
    /**
     * Table name
     * @var string
     */
    var $table_name;
    
    /**
     * Primary key
     * @var string
     */
    var $primary_key;
    
    /**
     * Action name
     * @var string
     */
    var $action;
    
    /**
     * Data model
     * @var array
     */
    var $data_model;
    
    protected $imgs = false;
    
    public $app_title;
    
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
    }
    
    function check_table_exist ( $table_name ) {
    	$query = "select * from ".DB_PREFIX."_{$table_name} limit 1";
    	$this->db->exec($query);
    	return $this->db->success;
    }
    
    protected function _edit_doneAction(){
    	$rs='';
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	
    	$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
    	
    	
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
    		$form_data[$this->table_name]=$this->removeTemporaryFields($form_data[$this->table_name],$remove_this_names);
    		$rs = $this->get_form($form_data[$this->table_name], 'edit');
    	} else {
    		$this->edit_data($form_data[$this->table_name]);
    		if ( $this->getError() ) {
    			$form_data[$this->table_name]=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
    			$rs = $this->get_form($form_data[$this->table_name], 'edit');
    		} else {
    			if($this->getConfigValue('apps.realtylog.enable')){
    				require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylog/admin/admin.php';
    				$Logger=new realtylog_admin();
    				$Logger->addLog($form_data[$this->table_name]['id']['value'], $_SESSION['user_id_value'], 'edit', $this->table_name);
    			}
    			if($this->getConfigValue('apps.shoplog.enable')){
    				require_once SITEBILL_DOCUMENT_ROOT.'/apps/shoplog/admin/admin.php';
    				$Logger=new shoplog_admin();
    				$Logger->addLog($form_data[$this->table_name][$this->primary_key]['value'], $_SESSION['user_id_value'], 'edit', $this->table_name);
    			}
    			if($this->getConfigValue('apps.realtylogv2.enable')){
    				require_once SITEBILL_DOCUMENT_ROOT.'/apps/realtylogv2/admin/admin.php';
    				$Logger=new realtylogv2_admin();
    				$Logger->addLog($form_data[$this->table_name]['id']['value'], $_SESSION['user_id_value'], 'edit', $this->table_name, $this->primary_key);
    			}
    			$rs .= $this->grid();
    		}
    	}
    	return $rs;
    }
    
    protected function _editAction(){
    	$rs='';
    	
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	
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
				$model_itited=$data_model->init_model_data_from_db_language ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name], false, $this->getRequestValue('language_id') );
				if($model_itited){
					$rs = $this->get_form($model_itited, 'edit');
				}else{
					$rs = '';
				}
				//$form_data[$this->table_name] = $data_model->init_model_data_from_db_language ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name], false, $this->getRequestValue('language_id') );
			} else {
				$model_itited=$data_model->init_model_data_from_db ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name] );
				if($model_itited){
					$rs = $this->get_form($model_itited, 'edit');
				}else{
					$rs = '';
				}
			}
			//$rs = $this->get_form($form_data[$this->table_name], 'edit');
		}
    	return $rs;
    }
    
    protected function _deleteAction(){
    	$rs='';
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
    
    protected function _new_doneAction(){
    	$rs='';
    	 
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	
    	$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
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
    			$form_data[$this->table_name]=$this->removeTemporaryFields($form_data[$this->table_name],$remove_this_names);
    			$rs = $this->get_form($form_data[$this->table_name], 'new');
		        
		    } else {
		        $new_record_id=$this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
		        if ( $this->getError() ) {
		        	$form_data[$this->table_name]=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
		        	$rs = $this->get_form($form_data[$this->table_name], 'new');
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
    
    protected function _newAction(){
    	$rs='';
    
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	$rs = $this->get_form($form_data[$this->table_name]);
    	return $rs;
    }
    
    protected function _mass_deleteAction(){
    	$rs='';
    
    	$id_array=array();
		$ids=trim($this->getRequestValue('ids'));
		if($ids!=''){
			$id_array=explode(',',$ids);
		}
		$rs.=$this->mass_delete_data($this->table_name, $this->primary_key, $id_array);
    	return $rs;
    }
    
    protected function _gridAction(){
    	$rs='';
    	$rs .= $this->grid();
    	return $rs;
    }
    
    protected function _batch_updateAction(){
    	$rs='';
    	$rs.=$this->batch_update($this->table_name, $this->primary_key);
		return $rs;
    }
    
    protected function _change_paramAction(){
    	$rs='';
    	$id_array=array();
    	$ids=trim($this->getRequestValue('ids'));
    	$param_name=trim($this->getRequestValue('param_name'));
    	$param_value=trim($this->getRequestValue('new_param_value'));
    	if(isset($form_data[$this->table_name][$param_name]) && $ids!=''){
    		$id_array=explode(',',$ids);
    		$rs.=$this->mass_change_param($this->table_name, $this->primary_key, $id_array, $param_name, $param_value);
    	}else{
    		$rs .= $this->_gridAction();
    	}
    	return $rs;
    }
    
    protected function _defaultAction(){
    	$rs='';
    	$rs .= $this->grid();
    	return $rs;
    }
    
    protected function _structureAction(){
    	$rs='';
    	$rs .= $this->structure_processor();
    	return $rs;
    }
    
    /**
     * Main
     * @param void
     * @return string
     */
    function main () {
	    $rs = $this->getTopMenu();
	    $rs .= '<hr>';
		$do=$this->getRequestValue('do');
		$action='_'.$do.'Action';
		if(!method_exists($this, $action)){
			$action='_defaultAction';
		}
		
		$rs .= $this->$action();
/*
		switch( $this->getRequestValue('do') ){
			case 'structure' : {
			        $rs = $this->structure_processor();
				break;
			}
		    
			case 'edit_done' : {
				$rs .= $this->_editDoneAction();
				break;
			}
		    
			case 'edit' : {
            	$rs .= $this->_editAction();
				break;
			}
			case 'delete' : {
				$rs .= $this->_deleteAction();
		        break;
			}
			
			case 'new_done' : {
				$rs .= $this->_new_doneAction();
				break;
			}
			
			case 'new' : {
				$rs .= $this->_newAction();
			    break;
			}
			case 'mass_delete' : {
				$rs .= $this->_mass_deleteAction();
				break;
			}
			case 'batch_update' : {
				$rs.=$this->_batch_updateAction();
				break;
			}
			case 'change_param' : {
				$rs.=$this->_change_paramAction();
				break;
			}
			default : {
			    $rs .= $this->_gridAction();
			}
		}*/
		$rs_new = $this->get_app_title_bar();
		$rs_new .= $rs;
		return $rs_new;
	}
	
	function checkUniquety($form_data){
		return TRUE;
	}
	
	function get_app_title_bar () {
		$breadcrumbs = array();
		$breadcrumbs[] =  array('href' => '#','title' => Multilanguage::_('L_ADMIN_MENU_APPLICATIONS'));
		
		if ( !empty($this->app_title) ) {
			$breadcrumbs[] =  array('href' => '?action='.$this->action.'','title' => $this->app_title);
	    } else {
	    	$breadcrumbs[] =  array('href' => '?action='.$this->action.'','title' => $this->action);
	    }
	    $this->template->assign('breadcrumbs_array', $breadcrumbs);
	    return '';
	    
	    /*
		$rs = '<div class="breadcrumbs" id="breadcrumbs">';
	    $rs .= '<ul class="breadcrumb">';
	    $rs .= '<li>'.Multilanguage::_('L_ADMIN_MENU_APPLICATIONS').' <span class="divider">/</span> ';
	    if ( !empty($this->app_title) ) {
	    	$rs .= '<a href="?action='.$this->action.'">'.$this->app_title.'</a>';
	    } else {
	    	$rs .= '<a href="?action='.$this->action.'">'.$this->action.'</a>';
	    }
	    $rs .= '</li>';
	    $rs .= '</ul>';
	    $rs .= '<div class="clear"></div>';
	    $rs .= '</div>';
	    
	    return $rs;
	    */
	}
	
	function mass_delete_data($table_name, $primary_key, $ids){
		$errors='';
		if(count($ids)>0){
			foreach($ids as $id){
				$this->delete_data($this->table_name, $this->primary_key, $id);
				if ( $this->getError() ) {
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
	
	function mass_change_param($table_name, $primary_key, $ids, $param_name, $param_value){
		$errors='';
		if(count($ids)>0){
			$data_model = new Data_Model();
			$form_data = $this->data_model;
			$partial_form_data=array();
			$partial_form_data[$this->table_name][$this->primary_key]=$form_data[$this->table_name][$this->primary_key];
			$partial_form_data[$this->table_name][$param_name]=$form_data[$this->table_name][$param_name];
			
			/*foreach($form_data[$this->table_name] as $fk=>$fv){
				if($fk!==$this->primary_key || $fk!==$param_name){
					unset($form_data[$this->table_name][$fk]);
				}
			}*/
			//$FD=$form_data
			foreach($ids as $id){
				$partial_form_data[$this->table_name][$this->primary_key]['value']=$id;
				$partial_form_data[$this->table_name][$param_name]['value']=$param_value;
				//print_r($partial_form_data[$this->table_name]);
				if ( $this->check_data( $partial_form_data[$this->table_name] ) ) {
					$this->edit_data($partial_form_data[$this->table_name]);
				}
			}
		}
		$rs .= $this->grid();
		
		return $rs;
	}
	
    /**
     * Load record by id
     * @param int $record_id
     * @return array
     */
    function load_by_id ( $record_id ) {
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    if ( !isset($this->data_model_object) || !is_object($this->data_model_object) ) {
	        $this->data_model_object = new Data_Model();
	    }
	    $form_data = $this->data_model;
	    if ( $record_id > 0 ) {
	    	$form_data[$this->table_name] = $this->data_model_object->init_model_data_from_db ( $this->table_name, $this->primary_key, $record_id, $form_data[$this->table_name], TRUE );
	    }
        return $form_data[$this->table_name];
        //print_r($form_data[$this->table_name]);
    }
    
	/**
	 * Delete data
	 * @param string $table_name
	 * @param string $primary_key
	 * @param int $primary_key_value
	 */
	function delete_data($table_name, $primary_key, $primary_key_value ) {
		$model=$this->data_model;
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$model = $data_model->init_model_data_from_db ( $table_name, $primary_key, $primary_key_value, $model[$table_name] );
		$uploads=array();
		foreach($model as $model_field){
			if($model_field['type']=='uploads' && !empty($model_field['value'])){
				foreach($model_field['value'] as $upload){
					$uploads[]=$upload['preview'];
					$uploads[]=$upload['normal'];
				}
			}
		}
		
		$DBC=DBC::getInstance();
	    $query = 'DELETE FROM '.DB_PREFIX.'_'.$table_name.' WHERE `'.$primary_key.'` = ?';
	    $stmt=$DBC->query($query, array($primary_key_value));
	    if(!$stmt){
	    	return false;
	    }
	    if(!empty($uploads)){
	    	foreach($uploads as $upload){
	    		@unlink(SITEBILL_DOCUMENT_ROOT.'/img/data/'.$upload);
	    	}
	    }
	    return true;
	}
	
    /**
     * Grid
     * @param void
     * @return string
     */
    function grid ($params=array()) {
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/grid.php');
        $common_grid = new Common_Grid($this);
        $common_grid->set_grid_table($this->table_name);
        
        if(isset($params['grid_item']) && count($params['grid_item'])>0){
        	foreach($params['grid_item'] as $grid_item){
        		$common_grid->add_grid_item($grid_item);
        	}
        }else{
        	$common_grid->add_grid_item($this->primary_key);
        	$common_grid->add_grid_item('name');
        }
        
        if(isset($params['grid_controls']) && count($params['grid_controls'])>0){
        	foreach($params['grid_controls'] as $grid_item){
        		$common_grid->add_grid_control($grid_item);
        	}
        }else{
        	$common_grid->add_grid_control('edit');
        	$common_grid->add_grid_control('delete');
        }
        
        if(isset($params['grid_conditions']) && count($params['grid_conditions'])>0){
        	$common_grid->set_conditions($params['grid_conditions']);
        }
        
        //$common_grid->set_grid_query('SELECT * FROM '.DB_PREFIX.'_'.$this->table_name.' ORDER BY name ASC');
        
        
        $common_grid->setPagerParams(array('action'=>$this->action, 'page'=>$this->getRequestValue('page'), 'per_page'=>$this->getConfigValue('common_per_page')));
        
        $rs = $common_grid->construct_grid();
        return $rs;
    }
		
	/**
	 * Add data
	 * @param array $form_data form data
	 * @param int $language_id
	 * @return boolean
	 */
	function add_data ( $form_data, $language_id = 0 ) {
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    $query = $data_model->get_insert_query(DB_PREFIX.'_'.$this->table_name, $form_data, $language_id);
	    //echo $query.'<br>';
	    $DBC=DBC::getInstance();
	    $stmt=$DBC->query($query);
	    
	    if ( !$stmt ) {
	        return false;
	    }
	    $new_record_id = $DBC->lastInsertId();
	    if($new_record_id>0){
	    	foreach ($form_data as $form_item){
	    		if($form_item['type']=='uploads'){
	    			$imgs_uploads = $this->appendUploads($this->table_name, $form_item, $this->primary_key, $new_record_id);
	    			
	    			$this->set_imgs($imgs_uploads);
	    			
	    		}
	    	}
	    	$imgs=$this->editImageMulti($this->action, $this->table_name, $this->primary_key, $new_record_id);
	    	
	    	$this->set_imgs($imgs);
	    }
	    
	    return $new_record_id;
	}
	
	function set_imgs ( $imgs = false ) {
		if (!empty($imgs) and count($imgs) > 0 ) {
			$this->imgs = $imgs;
		}
	}
	
	function get_imgs ( ) {
		return $this->imgs;
	}
	
	
	/**
	 * Edit data
	 * @param array $form_data form data
	 * @param int $language_id language id
	 * @return boolean
	 */
	function edit_data ( $form_data, $language_id = 0, $primary_key_value = false ) {
		
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    if ( $primary_key_value ) {
	    	$query = $data_model->get_edit_query(DB_PREFIX.'_'.$this->table_name, $this->primary_key, $primary_key_value, $form_data, $language_id);
	    } else {
	    	$query = $data_model->get_edit_query(DB_PREFIX.'_'.$this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data, $language_id);
	    }
	   
	    $DBC=DBC::getInstance();
	    $stmt=$DBC->query($query);
	   
	    /*if(!$stmt){
	    	return false;
	    }*/
	    foreach ($form_data as $form_item){
	    	if($form_item['type']=='uploads'){
	    		$imgs_uploads = $this->appendUploads($this->table_name, $form_item, $this->primary_key, (int)$this->getRequestValue($this->primary_key));
	    		$this->set_imgs($imgs_uploads);
	    		 
	    	}
	    }
	    foreach ($form_data as $form_item){
	    	if($form_item['type']=='uploadify_image'){
	    		$imgs=$this->editImageMulti($this->action, $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
	    		$this->set_imgs($imgs);
	    	}
	    }
	    foreach ($form_data as $form_item){
	    	if($form_item['type']=='uploadify_file'){
	    		$imgs=$this->editFileMulti($this->action, $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
	    		$this->set_imgs($imgs);
	    	}
	    }
	    
	}
	
	/**
	 * Check data
	 * @param array $form_data
	 * @return boolean
	 */
	function check_data ( $form_data ) {
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    if ( !$data_model->check_data($form_data) ) {
	        $this->riseError($data_model->GetErrorMessage());
	        return false;
	    }
	    return true;
	}
	
	/**
	 * Get top menu
	 * @param void 
	 * @return string
	 */
	function getTopMenu () {
	    $rs = '';
	    $rs .= '<a href="?action='.$this->action.'&do=new" class="btn btn-primary">'.Multilanguage::_('L_ADD_RECORD_BUTTON').'</a> ';
		//$rs .= '<form method="post"><input type="hidden" name="action" value="add" /><input type="submit" name="submit" value="Добавить объявление" /></form>';
	    return $rs;
	}
   
   
	
	/**
	 * Get form for edit or new record
	 * @param array $form_data
	 * @param string $do
	 * @param int $language_id
	 * @param string $button_title
	 * @return string
	 */
	function get_form ( $form_data=array(), $do = 'new', $language_id = 0, $button_title = '', $action = 'index.php' ) {
		
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
		 
		/*if ( $do != 'new' ) {
			$el['controls']['apply']=array('html'=>'<button id="apply_changes" class="btn btn-info">'.Multilanguage::_('L_TEXT_APPLY').'</button>');
		}*/
		$el['controls']['submit']=array('html'=>'<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" name="submit" class="btn btn-primary">'.$button_title.'</button>');
		 
		
	
		
		
		$smarty->assign('form_elements',$el);
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl')){
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl';
		}else{
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template/data_form.tpl';
		}
		return $smarty->fetch($tpl_name);
	}
	
	/**
	 * Set apps template
	 * @param string $apps_name
	 * @param string $theme
	 * @param string $template_key
	 * @param string $template_value
	 * @return boolean
	 */
	function set_apps_template ( $apps_name, $theme, $template_key, $template_value ) {
		if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$theme.'/apps/'.$apps_name.'/site/template/'.$template_value) ) {
			$this->template->assert($template_key, SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$theme.'/apps/'.$apps_name.'/site/template/'.$template_value);
		} elseif ( !file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$theme.'/'.$apps_name.'/'.$template_value) ) {
			if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/'.$apps_name.'/site/template/'.$template_value) ) {
				$this->template->assert($template_key, SITEBILL_DOCUMENT_ROOT.'/apps/'.$apps_name.'/site/template/'.$template_value);
			} else {
				echo sprintf(Multilanguage::_('L_FILE_NOT_FOUND'),SITEBILL_DOCUMENT_ROOT.'/apps/'.$apps_name.'/site/template/'.$template_value);
				exit;
			}
		} else {
			$this->template->assert($template_key, SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$theme.'/'.$apps_name.'/'.$template_value);
		}
	}
	
	function getSteps($form_data,$step){
	
		$default_tab_name=$this->getConfigValue('default_tab_name');
		$tabs=array($default_tab_name);
			
		foreach ( $form_data as $item_id => $item_array ) {
			if(isset($item_array['tab']) && $item_array['tab']!=''){
				$tabs[$item_array['tab']]=$item_array['tab'];
			}
		}
		$tabs_array=array();
		$i=1;
		foreach($tabs as $t){
			if($i < $step){
				$tabs_array[$i]=array('name'=>$t, 'step'=>$i, 'status'=>'done');
			}elseif($i==$step){
				$tabs_array[$i]=array('name'=>$t, 'step'=>$i, 'status'=>'current');
			}else{
				$tabs_array[$i]=array('name'=>$t, 'step'=>$i, 'status'=>'further');
			}
			$i++;
		}
		return $tabs_array;
	}
	
	public function _preload(){
		//echo get_class($this).'<br />';
	}
	
	protected function createTranslitAliasByFields($id, $fields_for_alias){
		$alias='';
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$form_data_shared = $data_model->get_kvartira_model(false, true);
		
		$form_data_shared = $data_model->init_model_data_from_db ( 'data', 'id', $id, $form_data_shared['data'], true );
		$values=array();
		foreach($fields_for_alias as $v){
			$key=trim($v);
			if(isset($form_data_shared[$key])){
				if(($form_data_shared[$key]['type']=='select_box_structure' || $form_data_shared[$key]['type']=='select_by_query' || $form_data_shared[$key]['type']=='select_box') && $form_data_shared[trim($v)]['value_string']!='' ){
					$values[]=$form_data_shared[trim($v)]['value_string'];
				}elseif($form_data_shared[trim($v)]['value']!=''){
					$values[]=$form_data_shared[trim($v)]['value'];
				}
			}
			
		}
		if(!empty($values)){
			foreach ($values as $k=>$v){
				$values[$k]=$this->transliteMe($v);
			}
			$alias=implode('-', $values);
		}
		return $alias;
	}
	
	protected function makeUniqueAlias($alias, $id){
		$is_similar_alias_exists=false;
		$DBC=DBC::getInstance();
		$query="SELECT COUNT(*) AS cnt FROM ".DB_PREFIX."_data WHERE translit_alias=? AND id<>? ORDER BY translit_alias DESC LIMIT 1";
		$stmt=$DBC->query($query, array($alias, $id));
		if($stmt){
			$ar=$DBC->fetch($stmt);
			if((int)$ar['cnt']>0){
				$is_similar_alias_exists=true;
			}
		}
		
		if($is_similar_alias_exists){
			$query="SELECT translit_alias FROM ".DB_PREFIX."_data WHERE translit_alias LIKE '".$alias."%' AND id<>? ORDER BY translit_alias DESC LIMIT 1";
			$stmt=$DBC->query($query, array($id));
			if($stmt){
				$ar=$DBC->fetch($stmt);
				if(preg_match('/'.$alias.'-(\d+)/', $ar['translit_alias'], $matches)){
					$alias.='-'.((int)$matches[1]+1);
				}else{
					$alias.='-1';
				}
			}
			
		}
		return $alias;
		
	}
	
	protected function saveTranslitAlias($id){
		
		
		$new_alias='';
		if(1==$this->getConfigValue('apps.seo.allow_custom_realty_aliases')){
			
			$DBC=DBC::getInstance();
			$query='SELECT translit_alias FROM re_data WHERE re_data.id=? LIMIT 1';
			$stmt=$DBC->query($query, array($id));
			if($stmt){
				$ar=$DBC->fetch($stmt);
				$old_alias=$ar['translit_alias'];
			}
			
			if($old_alias==''){
				
				if(''!=$this->getConfigValue('apps.seo.allow_custom_realty_aliase_fields')){
					
					$new_alias=$this->createTranslitAliasByFields($id, explode(',',$this->getConfigValue('apps.seo.allow_custom_realty_aliase_fields')));
					
				}
				
				if(''!=$new_alias){
					$new_alias=$this->makeUniqueAlias($new_alias, $id);
				}
			}
		}
		
		/*
		if(1==$this->getConfigValue('apps.seo.allow_custom_realty_aliases')){
			$old_alias='';
			$fields_for_alias=explode(',', $this->getConfigValue('apps.seo.allow_custom_realty_aliase_fields'));
			$DBC=DBC::getInstance();
			$query='SELECT translit_alias FROM re_data WHERE re_data.id=? LIMIT 1';
			$stmt=$DBC->query($query, array($id));
			if($stmt){
				$ar=$DBC->fetch($stmt);
				$old_alias=$ar['translit_alias'];
			}
			if($old_alias==''){
				if(count($fields_for_alias)>0 && $fields_for_alias[0]!='');
			}
		}*/
		
		
		if($new_alias==''){
			$DBC=DBC::getInstance();
			$new_alias=$this->createTranslitAliasByFields($id, array('city_id', 'street_id', 'number'));
			if(''!=$new_alias){
				$new_alias=$this->makeUniqueAlias($new_alias, $id);
			}
			/*$query='SELECT re_city.name AS city, re_street.name AS street, re_data.number
				FROM re_data
				LEFT JOIN re_city ON re_city.city_id=re_data.city_id
				LEFT JOIN re_street ON re_street.street_id=re_data.street_id
				WHERE re_data.id='.$id;
			$stmt=$DBC->query($query);
			//this->db->exec($query);
			if($stmt){
				$p=array();
				$this->db->fetch_assoc();
				if($this->db->row['city']!=''){
					$p[]=$this->transliteMe($this->db->row['city']);
				}
				if($this->db->row['street']!=''){
					$p[]=$this->transliteMe($this->db->row['street']);
				}
				if((int)$this->db->row['number']!=0){
					$p[]=(int)$this->db->row['number'];
				}
				if(!empty($p)){
					$alias=implode('-',$p);
					$q="SELECT translit_alias FROM ".DB_PREFIX."_data WHERE translit_alias LIKE '".$alias."%' AND id<>".$id." ORDER BY translit_alias DESC LIMIT 1";
			
					$this->db->exec($q);
					$this->db->fetch_assoc();
			
					if($this->db->row['translit_alias']!=''){
						if(preg_match('/'.$alias.'-(\d+)/',$this->db->row['translit_alias'],$matches)){
							$alias.='-'.((int)$matches[1]+1);
						}else{
							$alias.='-1';
						}
					}
			
					$query='UPDATE re_data SET translit_alias=\''.$alias.'\' WHERE id='.$id;
					$this->db->exec($query);
				}
			
			}*/
		}
		
		$query='UPDATE re_data SET translit_alias=? WHERE id=?';
		$stmt=$DBC->query($query, array($new_alias, $id));
		
		
		
		
	
	}
	
	protected function removeTemporaryFields(&$model,$remove_this_names=array()){
		if(count($remove_this_names)>0){
			foreach($remove_this_names as $r){
				unset($model[$r]);
			}
		}
		return $model;
	}
	
	protected function batch_update($table_name, $primary_key){
		$rs .= $this->grid($user_id);
		return $rs;
	}

}
?>
