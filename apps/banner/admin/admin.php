<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Banner admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class banner_admin extends Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        $this->table_name = 'banner';
        $this->action = 'banner';
        $this->primary_key = 'banner_id';
	    
        $form_data = array();
        
        if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php') ){
        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
        	$ATH=new Admin_Table_Helper();
        	$form_data=$ATH->load_model($this->table_name, false);
        	if(empty($form_data)){
        		$form_data = array();
        		$form_data = $this->get_banner_model();
        		//$form_data = $this->_get_big_city_kvartira_model2($ajax);
        		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
        		require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php';
        		$TA=new table_admin();
        		$TA->create_table_and_columns($form_data, $this->table_name);
        		$form_data = array();
        		$form_data=$ATH->load_model($this->table_name, false);
        	}
        	 
        }else{
        	$form_data = $this->get_banner_model();
        }
        
        $this->data_model=$form_data;
    }
    
    function _preload(){
    	$requesturi=ltrim(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH),'/');
    	$banners=array();
    	$banners=$this->get_banners_list();
    	if(count($banners)>0){
    		foreach ($banners as $v){
    			$banner_str='';
    			$active_url=false;
    			if(isset($v['active_url']) && $v['active_url']!=''){
    				$active_url=trim($v['active_url'],'/');
    			}
    			 
    			if($active_url){
    
    				if(preg_match('/^'.str_replace('/', '\/', $active_url).'[\/]?/', $requesturi)){
    					if($v['url']!=''){
    						$banner_str='<a href="'.$v['url'].'" class="thumbnail">'.$v['body'].'</a>';
    					}else{
    						$banner_str=$v['body'];
    					}
    					$this->template->assert($v['title'], $banner_str);
    				}
    			}else{
    				if($v['url']!=''){
    					$banner_str='<a href="'.$v['url'].'" class="thumbnail">'.$v['body'].'</a>';
    				}else{
    					$banner_str=$v['body'];
    				}
    				$this->template->assert($v['title'], $banner_str);
    			}
    		}
    	}
    }
    
    /**
     * Enter description here ...
     * @return unknown
     */
    function get_banners_list() {
    	$DBC=DBC::getInstance();
    	$query = "SELECT * FROM ".DB_PREFIX."_banner WHERE published=1 ORDER BY ".$this->primary_key." ASC";
    	$ra=array();
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while ($ar=$DBC->fetch($stmt)){
    			$ra[] = $ar;
    		}
    	}
    	return $ra;
    }
    
    function main () {
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$form_data = $this->data_model;
    	$rs = $this->getTopMenu();
    
    	switch( $this->getRequestValue('do') ){
    		case 'structure' : {
    			$rs = $this->structure_processor();
    			break;
    		}
    
    		case 'edit_done' : {
    			$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
    			 
    			if ( !$this->check_data( $form_data[$this->table_name] ) ) {
    				$rs = $this->get_form($form_data[$this->table_name], 'edit');
    			} else {
    				$this->edit_data($form_data[$this->table_name]);
    				if ( $this->getError() ) {
    					$rs = $this->get_form($form_data[$this->table_name], 'edit');
    				} else {
    					$rs .= $this->grid();
    				}
    			}
    			break;
    		}
    
    		case 'edit' : {
    			 
    			if ( $this->getRequestValue('language_id') > 0 and !$this->language->get_version($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $this->getRequestValue('language_id')) ) {
    				$rs = $this->get_form($form_data[$this->table_name], 'new', $this->getRequestValue('language_id'));
    			} else {
    				if ( $this->getRequestValue('language_id') > 0 ) {
    					$form_data[$this->table_name] = $data_model->init_model_data_from_db_language ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name], false, $this->getRequestValue('language_id') );
    				} else {
    					$form_data[$this->table_name] = $data_model->init_model_data_from_db ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name] );
    				}
    				$rs = $this->get_form($form_data[$this->table_name], 'edit');
    			}
    
    			break;
    		}
    		case 'delete' : {
    			 
    			$this->delete_data($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
    			if ( $this->getError() ) {
    				$rs .= '<div align="center">'.Multilanguage::_('L_ERROR_ON_DELETE').': '.$this->GetErrorMessage().'<br>';
    				$rs .= '<a href="?action='.$this->action.'">ОК</a>';
    				$rs .= '</div>';
    			} else {
    				$rs .= $this->grid();
    			}
    
    
    			break;
    		}
    		 
    		case 'new_done' : {
    			$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
    			$data_model->forse_auto_add_values($form_data[$this->table_name]);
    			if ( !$this->check_data( $form_data[$this->table_name] ) || (1==$this->getConfigValue('filter_double_data') && !$this->checkUniquety($form_data[$this->table_name]))  ) {
    				$rs = $this->get_form($form_data[$this->table_name], 'new');
    					
    			} else {
    				$new_record_id=$this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
    				if ( $this->getError() ) {
    					$rs = $this->get_form($form_data[$this->table_name], 'new');
    				} else {
    					$rs .= $this->grid();
    				}
    			}
    			break;
    		}
    		 
    		case 'new' : {
    			$rs = $this->get_form($form_data[$this->table_name]);
    			break;
    		}
    		case 'mass_delete' : {
    			$id_array=array();
    			$ids=trim($this->getRequestValue('ids'));
    			if($ids!=''){
    				$id_array=explode(',',$ids);
    			}
    			$rs.=$this->mass_delete_data($this->table_name, $this->primary_key, $id_array);
    			break;
    		}
    		case 'change_param' : {
    			$id_array=array();
    			$ids=trim($this->getRequestValue('ids'));
    			$param_name=trim($this->getRequestValue('param_name'));
    			$param_value=trim($this->getRequestValue('new_param_value'));
    
    			if(isset($form_data[$this->table_name][$param_name]) && $ids!=''){
    				//echo 1;
    				$id_array=explode(',',$ids);
    				$rs.=$this->mass_change_param($this->table_name, $this->primary_key, $id_array, $param_name, $param_value);
    			}else{
    				$rs .= $this->grid();
    			}
    			break;
    		}
    		default : {
    			$rs .= $this->grid($user_id);
    		}
    	}
    	$rs_new = $this->get_app_title_bar();
    	$rs_new .= $rs;
    	return $rs_new;
    }
    
	function add_data ( $form_data ) {
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
	    $query = $this->get_insert_query(DB_PREFIX.'_'.$this->table_name, $form_data);
	    $DBC=DBC::getInstance();
	    $stmt=$DBC->query($query);
	    if($stmt){
	    	return true;
	    }else{
	    	return false;
	    }
	    //$new_record_id=$DBC->lastInsertId();
	}
	
	function edit_data ( $form_data ) {
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    $query = $this->get_edit_query(DB_PREFIX.'_'.$this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data);
	    $DBC=DBC::getInstance();
	    $stmt=$DBC->query($query);
	}
    
    
    function install () {
    	$DBC=DBC::getInstance();
        //create tables
        $query = "
CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_banner` (
  `banner_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `body` text,
  `catalog_id` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `url` text NOT NULL,
  PRIMARY KEY (`banner_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=".DB_ENCODING." ;
        ";
        $stmt=$DBC->query($query);
    }
    
    /**
     * Grid
     * @param void
     * @return string
     */
function grid () {
    	 
    	$params=array();
    	$params['action']=$this->action;
    	 
    	 
    	 
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/grid.php');
    	$common_grid = new Common_Grid($this);
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/page.php');
    	$common_page = new Common_Page();
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/tab.php');
    	$common_tab = new Common_Tab();
    	$url='/admin/index.php?action='.$this->action;
    
    	$common_grid->add_grid_item('banner_id');
    	if(isset($this->data_model[$this->table_name]['human_title'])){
    		$common_grid->add_grid_item('human_title');
    	}
    	
    	$common_grid->add_grid_item('title');
    	$common_grid->add_grid_item('description');
    	$common_grid->add_grid_item('published');
    
    
    	$common_grid->add_grid_control('edit');
    	$common_grid->add_grid_control('delete');
    	
    	$common_grid->set_grid_query("SELECT * FROM ".DB_PREFIX."_".$this->table_name." ORDER BY banner_id DESC");
    	$params['page']=$this->getRequestValue('page');
    	$params['per_page']=$this->getConfigValue('common_per_page');
    
    	$common_grid->setPagerParams($params);
    
    	$common_page->setTab($common_tab);
    	$common_page->setGrid($common_grid);
    
    	$rs .= $common_page->toString();
    	return $rs;
    }
    
    /**
     * Get gallery model
     * @param
     * @return
     */
    function get_banner_model () {
		$form_banner = array();
		
		$form_banner['banner']['banner_id']['name'] = 'banner_id';
		$form_banner['banner']['banner_id']['title'] = Multilanguage::_('L_ID');
		$form_banner['banner']['banner_id']['value'] = 0;
		$form_banner['banner']['banner_id']['length'] = 40;
		$form_banner['banner']['banner_id']['type'] = 'primary_key';
		$form_banner['banner']['banner_id']['required'] = 'off';
		$form_banner['banner']['banner_id']['unique'] = 'off';
		
		/*$form_banner['banner']['human_title']['name'] = 'human_title';
		$form_banner['banner']['human_title']['title'] = 'human_title';
		$form_banner['banner']['human_title']['value'] = '';
		$form_banner['banner']['human_title']['length'] = 40;
		$form_banner['banner']['human_title']['type'] = 'safe_string';
		$form_banner['banner']['human_title']['required'] = 'off';
		$form_banner['banner']['human_title']['unique'] = 'off';*/
		
		$form_banner['banner']['title']['name'] = 'title';
		$form_banner['banner']['title']['title'] = Multilanguage::_('L_BANNER_MARK');
		$form_banner['banner']['title']['value'] = '';
		$form_banner['banner']['title']['length'] = 40;
		$form_banner['banner']['title']['type'] = 'safe_string';
		$form_banner['banner']['title']['required'] = 'on';
		$form_banner['banner']['title']['unique'] = 'off';
		
		$form_banner['banner']['body']['name'] = 'body';
		$form_banner['banner']['body']['title'] = Multilanguage::_('L_BANNER_BODY');
		$form_banner['banner']['body']['value'] = '';
		$form_banner['banner']['body']['type'] = 'textarea';
		$form_banner['banner']['body']['required'] = 'on';
		$form_banner['banner']['body']['unique'] = 'off';
		$form_banner['banner']['body']['rows'] = '10';
		$form_banner['banner']['body']['cols'] = '60';
		
		$form_banner['banner']['description']['name'] = 'description';
		$form_banner['banner']['description']['title'] = 'Описание';
		$form_banner['banner']['description']['value'] = '';
		$form_banner['banner']['description']['type'] = 'textarea';
		
		
		$form_banner['banner']['published']['name'] = 'published';
		$form_banner['banner']['published']['title'] = Multilanguage::_('L_PUBLISHED');
		$form_banner['banner']['published']['value'] = '0';
		$form_banner['banner']['published']['type'] = 'checkbox';
		$form_banner['banner']['published']['required'] = 'off';
		$form_banner['banner']['published']['unique'] = 'off';
		/*
		
		$form_banner['banner']['url']['name'] = 'url';
		$form_banner['banner']['url']['title'] = Multilanguage::_('L_LINK');
		$form_banner['banner']['url']['value'] = '';
		$form_banner['banner']['url']['length'] = 40;
		$form_banner['banner']['url']['type'] = 'safe_string';
		$form_banner['banner']['url']['required'] = 'off';
		$form_banner['banner']['url']['unique'] = 'off';
		
		$form_banner['banner']['image']['name'] = 'image';
		$form_banner['banner']['image']['table_name'] = 'banner';
		$form_banner['banner']['image']['primary_key'] = 'id';
		$form_banner['banner']['image']['primary_key_value'] = 0;
		$form_banner['banner']['image']['action'] = 'banner';
		$form_banner['banner']['image']['title'] = 'Картинка';
		$form_banner['banner']['image']['value'] = '';
		$form_banner['banner']['image']['length'] = 40;
		$form_banner['banner']['image']['type'] = 'uploadify_image';
		$form_banner['banner']['image']['required'] = 'off';
		$form_banner['banner']['image']['unique'] = 'off';
	*/		
		return $form_banner;
    }
    
    function get_edit_query ( $table_name, $primary_key_name, $primary_key_value, $model_array, $language_id = 0 ) {
    	unset($model_array['image']);
    
    	$set = array();
    	$values = array();
    	foreach ( $model_array as $key => $item_array ) {
    		if ( $item_array['type'] == 'primary_key' ) {
    			$primary_key = $item_array['name'];
    			continue;
    		}
    
    		if ( $item_array['type'] == 'separator' ) {
    			continue;
    		}
    
    		if ( $item_array['type'] == 'spacer_text' ) {
    			continue;
    		}
    
    		if ( $item_array['type'] == 'photo' ) {
    			continue;
    		}
    		if ( $item_array['dbtype'] == 'notable' ) {
    			if ( $item_array['type'] == 'tlocation' ) {
    
    				if(isset($item_array['parameters']['visibles'])){
    					$visibles=explode('|', $item_array['parameters']['visibles']);
    				}else{
    					$visibles=array();
    				}
    
    				if(!empty($item_array['value'])){
    					foreach($item_array['value'] as $k=>$v){
    						if(!empty($visibles)){
    							if(in_array($k, $visibles)){
    								$pairs[] = '`'.$k.'` = '.(int)$v;
    							}
    						}else{
    							$pairs[] = '`'.$k.'` = '.(int)$v;
    						}
    					}
    				}
    			}
    			continue;
    		}
    		if ( $item_array['type'] == 'geodata' ) {
    			if($item_array['value']['lat']==''){
    				$pairs[] = '`'.$key.'_lat` = NULL';
    			}else{
    				$pairs[] = '`'.$key.'_lat` = '."'".$this->escape($item_array['value']['lat'])."'";
    			}
    
    			if($item_array['value']['lng']==''){
    				$pairs[] = '`'.$key.'_lng` = NULL';
    			}else{
    				$pairs[] = '`'.$key.'_lng` = '."'".$this->escape($item_array['value']['lng'])."'";
    			}
    
    
    			continue;
    		}
    		$pairs[] = '`'.$key.'` = '."'".$this->escape(html_entity_decode($item_array['value']))."'";
    	}
    	if ( $language_id > 0 ) {
    		$set[] = '`language_id`';
    		$values[] = "'".$language_id."'";
    		$set[] = '`link_id`';
    		$values[] = "'".$this->getRequestValue($primary_key)."'";
    		$query = "update `$table_name` set ".implode(' , ', $pairs)." where link_id = $primary_key_value";
    	} else {
    		$query = "update `$table_name` set ".implode(' , ', $pairs)." where $primary_key_name = $primary_key_value";
    	}
    
    	return $query;
    }
    
    function get_insert_query ( $table_name, $model_array, $language_id = 0 ) {
    	$set = array();
    	$values = array();
    	unset($model_array['image']);
    
    	foreach ( $model_array as $key => $item_array ) {
    		if ( $item_array['type'] == 'primary_key' ) {
    			$primary_key = $item_array['name'];
    			continue;
    		}
    
    		if ( $item_array['type'] == 'separator' ) {
    			continue;
    		}
    
    		if ( $item_array['type'] == 'spacer_text' ) {
    			continue;
    		}
    
    		if ( $item_array['type'] == 'photo' ) {
    			continue;
    		}
    		if ( $item_array['dbtype'] == 'notable' ) {
    			if ( $item_array['type'] == 'tlocation' ) {
    
    				if(isset($item_array['parameters']['visibles'])){
    					$visibles=explode('|', $item_array['parameters']['visibles']);
    				}else{
    					$visibles=array();
    				}
    
    
    				if(!empty($item_array['value'])){
    					foreach($item_array['value'] as $k=>$v){
    						if(!empty($visibles)){
    							if(in_array($k, $visibles)){
    								$set[] = '`'.$k.'`';
    								$values[] = "'".(int)$v."'";
    							}
    						}else{
    							$set[] = '`'.$k.'`';
    							$values[] = "'".(int)$v."'";
    						}
    
    
    					}
    				}
    			}
    			continue;
    		}
    
    		if ( $item_array['type'] == 'geodata' ) {
    			$set[] = '`'.$key.'_lat`';
    			if($item_array['value']['lat']==''){
    				$values[] = "NULL";
    			}else{
    				$values[] = "'".$this->escape($item_array['value']['lat'])."'";
    			}
    
    			$set[] = '`'.$key.'_lng`';
    
    			if($item_array['value']['lng']==''){
    				$values[] = "NULL";
    			}else{
    				$values[] = "'".$this->escape($item_array['value']['lng'])."'";
    			}
    			continue;
    		}
    
    		$set[] = '`'.$key.'`';
    		$values[] = "'".$this->escape(html_entity_decode($item_array['value']))."'";
    	}
    	if ( $language_id > 0 ) {
    		$set[] = '`language_id`';
    		$values[] = "'".$language_id."'";
    		$set[] = '`link_id`';
    		$values[] = "'".$this->getRequestValue($primary_key)."'";
    	}
    	$query = "insert into $table_name (".implode(' , ', $set).") values (".implode(' , ', $values).")";
    	return $query;
    }
}