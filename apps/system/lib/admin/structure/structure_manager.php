<?php
/**
 * Structure manager
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/cache/cache.php';
class Structure_Manager extends SiteBill_Krascap {
	
	private static $_category_structure=NULL;

    var $operation_type_array = array();
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        $this->operation_type_array = $this->load_operation_type_list();
        require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/version/version.php';
        $version = new Version();
        if ( !$version->get_version_value('topic.url') ) {
            $this->add_topic_url();
            $version->set_version_value('topic.url', 1);
        }
        $this->action='structure';
        $this->table_name='topic';
        $this->primary_key='id';
        //echo '<pre>';
        //print_r($this->operation_type_array);
    }
    
    function add_topic_url () {
        
        $query = "alter table ".DB_PREFIX."_topic add column url text";
        $this->db->exec($query);
    }
    
    function get_content_drop_menu () {
    	$ra = array();
    	$DBC=DBC::getInstance();
    
    	$query = "SELECT ms.*, m.tag, m.name as menu_title FROM ".DB_PREFIX."_menu m, ".DB_PREFIX."_menu_structure ms WHERE m.menu_id=ms.menu_id and m.tag like '%drop_menu%' ORDER BY ms.sort_order";
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while ( $ar=$DBC->fetch($stmt) ) {
    			$ra[$ar['tag']][] = $ar;
    		}
    	}
    	return $ra;
    }
    
    
    
    function upgrade () {
    	$query = "alter table ".DB_PREFIX."_topic add column meta_title text";
    	$this->db->exec($query);
    	
    	$query = "alter table ".DB_PREFIX."_topic add column meta_keywords text";
    	$this->db->exec($query);
    	 
    	$query = "alter table ".DB_PREFIX."_topic add column meta_description text";
    	$this->db->exec($query);
    }
    
    
    /**
     * Main
     * @param void
     * @return string
     */
    function main () {
    	//return 'В разработке';
        $do = $this->getRequestValue('do');
        switch ( $do ) {
            case 'delete':
                if ( $this->isDemo() ) {
                    return $this->demo_function_disabled();
                }
                $category_structure = $this->loadCategoryStructure();
                if ( count($category_structure['childs'][$this->getRequestValue('id')]) > 0 ) {
                    $rs = Multilanguage::_('CATEGORY_HAS_CHILDS','system').'<br>';
                    $rs .= '<a href="?action=structure">'.Multilanguage::_('BACK_TO_LIST','system').'</a>';
                    return $rs;
                }
                
                $this->db->exec('SELECT COUNT(*) AS rs FROM '.DB_PREFIX.'_data WHERE topic_id='.$this->getRequestValue('id'));
                $this->db->fetch_assoc();
                $c=$this->db->row['rs'];
                if($c!=0){
                	$rs = Multilanguage::_('NOT_EMPTY_CATEGORY','system').'<br>';
                    $rs .= '<a href="?action=structure">'.Multilanguage::_('BACK_TO_LIST','system').'</a>';
                    return $rs;
                }
                $this->deleteRecord($this->getRequestValue('id'));
                $Cache=Cache::getInstance();
		       	$Cache->clearValue('catalog_structure');
                $rs = $this->getTopMenu();
                $rs .= $this->grid();
            break;
            
            case 'edit':
            	
            	$rs='';
    	
		    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		    	$data_model = new Data_Model();
		    	$form_data = $this->getStrModel();
		    	
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
		    	//return $rs;
            	
            	/*if ( $this->getRequestValue('subdo') == 'delete_image' ) {
            		$this->deleteImage('topic', $this->getRequestValue('image_id'));
            	}
            	 
            	if ( $this->getRequestValue('subdo') == 'up_image' ) {
            		$this->reorderImage('topic', $this->getRequestValue('image_id'), 'id', $this->getRequestValue('id'),'up');
            	}
            	 
            	if ( $this->getRequestValue('subdo') == 'down_image' ) {
            		$this->reorderImage('topic', $this->getRequestValue('image_id'), 'id', $this->getRequestValue('id'), 'down');
            	}
            	//echo 1;
                $hash = $this->load($this->getRequestValue('id'));
                $rs = $this->getForm('edit');*/
            break;
            
            case 'new':
            	$rs='';
            	
            	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
            	$data_model = new Data_Model();
            	$form_data = $this->getStrModel();
            	$rs = $this->get_form($form_data[$this->table_name]);
            	//return $rs;
               // $rs = $this->getForm();
            break;
			
			case 'associations':
				$rs = $this->getTopMenu();
				if(isset($_POST['submit'])){
					
					//echo '<pre>';
					//print_r($_POST);
					$this->saveAssociations($_POST['data']);
					$rs .= $this->getCategoryTreeAssoc(0);
					//print_r($_POST);
				}else{
					$rs .= $this->getCategoryTreeAssoc(0);
				}
				
				break;
				
			/*case 'chains':
				$this->loadCategoriesUrls();
			
			break;*/
            
            case 'new_done': {
                if ( $this->isDemo() ) {
                    return $this->demo_function_disabled();
                }
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
                $data_model = new Data_Model();
                $form_data = $this->getStrModel();
                
                $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
               
                require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
                $OM=new Object_Manager();
 
                if ( !$OM->check_data( $form_data[$this->table_name] ) || !$this->checkData()  ) {
                	$rs = $this->get_form($form_data[$this->table_name], 'new');
                
                } else {
                	$new_record_id=$this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
                	if ( $this->getError() ) {
                		$rs = $this->get_form($form_data[$this->table_name], 'new');
                	} else {
                		$rs .= $this->grid();
                	}
                }
               /* return $rs;
                if ( !$this->checkData() ) {
                    $rs = $this->getForm();
                } else {
                    $this->addRecord();
                    $Cache=Cache::getInstance();
		        	$Cache->clearValue('catalog_structure');
                    $rs = $this->getTopMenu();
                    $rs .= $this->grid();
                }*/
            	break;
            }
            case 'edit_done':
                if ( $this->isDemo() ) {
                    return $this->demo_function_disabled();
                }
                $rs='';
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
                $data_model = new Data_Model();
                $form_data = $this->getStrModel();
                 
                $form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
                 
                 
                
                
               
                
                require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
                $OM=new Object_Manager();
              
                if ( !$OM->check_data( $form_data[$this->table_name]) || !$this->checkData()  ) {
                	$rs = $this->get_form($form_data[$this->table_name], 'edit');
                } else {
                	$this->edit_data($form_data[$this->table_name]);
                	if ( $this->getError() ) {
                		$rs = $this->get_form($form_data[$this->table_name], 'edit');
                	} else {
                		$rs .= $this->grid();
                	}
                }
                //return $rs;
                
                /*if ( !$this->checkData() ) {
                    $rs = $this->getForm('edit');
                } else {
                    $this->editRecord($this->getRequestValue('id'));
                    $Cache=Cache::getInstance();
		        	$Cache->clearValue('catalog_structure');
                    $rs = $this->getTopMenu();
                    $rs .= $this->grid();
                }*/
            break;
            
            case 'reorder_topics':
                $orderArray=$this->getRequestValue('order');
                $this->reorderTopics($orderArray);
                
		        $Cache=Cache::getInstance();
		        $Cache->clearValue('catalog_structure');
		        //$Cache->update();
		        $rs = $this->getTopMenu();
                $rs .= $this->grid();
                
            break;
            
            default: {
            	$rs .= $this->grid();
            }  
            
            
        }
        $rs = $this->get_app_title_bar().$this->getTopMenu().$rs;
        return $rs;
    }
    
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
    
    	foreach ($form_data as $form_item){
    		if($form_item['type']=='uploads'){
    			$imgs_uploads = $this->appendUploads($this->table_name, $form_item, $this->primary_key, (int)$this->getRequestValue($this->primary_key));
    			//$this->set_imgs($imgs_uploads);
    
    		}
    	}
    	foreach ($form_data as $form_item){
    		if($form_item['type']=='uploadify_image'){
    			$imgs=$this->editImageMulti($this->action, $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
    			//$this->set_imgs($imgs);
    		}
    	}
    	foreach ($form_data as $form_item){
    		if($form_item['type']=='uploadify_file'){
    			$imgs=$this->editFileMulti($this->action, $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
    			//$this->set_imgs($imgs);
    		}
    	}
    	 
    }
    
    function add_data ( $form_data, $language_id = 0 ) {
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
    	$data_model = new Data_Model();
    	$query = $data_model->get_insert_query(DB_PREFIX.'_'.$this->table_name, $form_data, $language_id);
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
    
    				//$this->set_imgs($imgs_uploads);
    
    			}
    		}
    		$imgs=$this->editImageMulti($this->action, $this->table_name, $this->primary_key, $new_record_id);
    
    		//$this->set_imgs($imgs);
    	}
    	 
    	return $new_record_id;
    }
    
    function get_app_title_bar () {
    	$rs = '<ul class="breadcrumb">';
    	$rs .= '<li>'.Multilanguage::_('L_ADMIN_MENU_APPLICATIONS').' <span class="divider">/</span> ';
   		$rs .= '<a href="?action=structure">Структура</a>';
    	$rs .= '</li>';
    	$rs .= '</ul>';
    	 
    	return $rs;
    }
    
	
	function saveAssociations($rules){
		//print_r($rules);
		$category_structure = $this->loadCategoryStructure();
		foreach($category_structure['childs'] as $k=>$v){
			$ret[$k]=$this->get_all_childs($k, $category_structure);
		}
		foreach($ret[0] as $kk=>$vv){
			if(isset($rules[$vv])){
				
				if($rules[$vv]['legacy']=='on'){
					$this->updateAssociations(array($vv), $rules[$vv]);
					//echo '<br />START CHILDS UPDATING FOR ID '.$vv.':<br />';
					$this->updateAssociations($ret[$vv], $rules[$vv]);
					//echo ':END CHILDS UPDATING<br /><br />';
					foreach($ret[$vv] as $vvv){
						unset($rules[$vvv]);
					}
				}else{
					$this->updateAssociations(array($vv), $rules[$vv]);
				}
				//unset($rules[$vv]);
			}else{
				//echo 'NO RULES FOR ID:'.$vv.'<br />';
			}
			unset($rules[$vv]);
		}
		//print_r($ret);
	}
	
	private function updateAssociations($items=array(),$rules=array()){
		if(!empty($items)){
			foreach($items as $v){
				$query='UPDATE '.DB_PREFIX.'_topic SET obj_type_id='.(int)$rules['obj_type_id'].', operation_type_id='.(int)$rules['operation_type'].' WHERE id='.(int)$v;
				$this->db->exec($query);
				//echo 'SET rules FOR ID:'.$v.' optype='.$rules['operation_type'].' realtytype='.$rules['obj_type_id'].'<br />';
			}
		}
	}
    
    /**
     * Get operation type name by ID
     * @param int $operation_type_id operation type id
     * @return string
     */
    function get_operation_type_name_by_id ( $operation_type_id ) {
        return $this->operation_type_array[$operation_type_id]['name'];
    }
    
    /**
     * Get operation type select box
     * @param int $operation_type_id operation type id
     * @return string
     */
    function get_operation_type_select_box ( $operation_type_id ) {
        $query = "SELECT * FROM ".DB_PREFIX."_operation_type order by `operation_type_id` ";
        
        //echo $query;
        $this->db->exec($query);
        $rs = '<select name="operation_type_id">';
        //$rs .= '<option value="1">1</option>';
        while ( $this->db->fetch_assoc() ) {
            if ( $operation_type_id ==  $this->db->row['operation_type_id'] ) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            //echo $this->db->row['operation_type_id'].'<br>';
            $rs .= '<option value="'.$this->db->row['operation_type_id'].'" '.$selected.'>'.$this->db->row['name'].'</option>';
        }
        $rs .= '</select>';
        return $rs;
    }
    
    /**
     * Edit record
     * @param int $id topic ID
     * @return boolean
     */
    function editRecord ( $id ) {
        $languages = Multilanguage::foreignLanguages();
        foreach ( $languages as $language_id => $language_title ) {
            $lang_string .= "name_{$language_id}='".$this->escape($this->getRequestValue('name_'.$language_id))."',";
        }
        
    	$query = "update ".DB_PREFIX."_topic set
        	name='".$this->escape($this->getRequestValue('name'))."',
        	{$lang_string}
        	parent_id='".(int)$this->getRequestValue('parent_id')."',
       	    url='".$this->escape(trim($this->getRequestValue('url')))."',
        	meta_title='".$this->escape($this->getRequestValue('meta_title'))."',
        	meta_keywords='".$this->escape($this->getRequestValue('meta_keywords'))."',
        	meta_description='".$this->escape($this->getRequestValue('meta_description'))."',
        	description='".$this->getRequestValue('description')."'
        where id=".$id."";
       
        //echo $query;
        $this->db->exec($query);
        if ( !$this->db->success ) {
        	echo $this->db->error;
        }else{
        	$imgs=$this->editImageMulti('topic', 'topic', 'id', $id);
        }
        
        return true;
    }
    
    /**
     * Delete record
     * @param int $id topic ID
     * @return boolean
     */
    function deleteRecord ( $id ) {
    	$imgs_ids=array();
    	if(1==$this->getConfigValue('allow_topic_images')){
    		$query='SELECT image_id FROM '.DB_PREFIX.'_topic_image WHERE id='.$id;
    		 
    		$this->db->exec($query);
    		if ($this->db->success) {
    			while($this->db->fetch_assoc()){
    				//print_r($this->db->row);
    				$imgs_ids[]=$this->db->row['image_id'];
    			}
    		}
    	}
    	
    	$query = "delete from ".DB_PREFIX."_topic where id=".$id."";
        $this->db->exec($query);
        if ( !$this->db->success ) {
        	echo $this->db->error;
        }
    	if(count($imgs_ids)>0){
    		foreach($imgs_ids as $im){
    			$this->deleteImage('topic', $im);
    		}
    	}
    	return TRUE;
    }
    
    function deleteTopicItem($id, $delete_option, $childs_delete_option){
    	$DBC=DBC::getInstance();
    	$childs_delete_option='move_up';
    	if($delete_option=='delete_current'){
    		if($childs_delete_option=='move_up'){
    			$query='SELECT parent_id FROM '.DB_PREFIX.'_topic WHERE id=? LIMIT 1';
    			$stmt=$DBC->query($query, array($id));
    			$ar=$DBC->fetch($stmt);
    			$parent_id=(int)$ar['parent_id'];
    			
    			
    			$query='UPDATE '.DB_PREFIX.'_topic SET parent_id=? WHERE parent_id=?';
    			$stmt=$DBC->query($query, array($parent_id, $id));
    			
    			$query='UPDATE '.DB_PREFIX.'_data SET topic_id=? WHERE topic_id=?';
    			$stmt=$DBC->query($query, array($parent_id, $id));
    			
    			$this->deleteRecord($id);
    		}
    	}elseif($delete_option=='delete_incoming'){
    		if($childs_delete_option=='move_up'){
    			$parent_id=$id;
    			
    			
    			$category_structure=$this->loadCategoryStructure();
    			$childs_topics=array();
    			$childs_topics=$this->get_all_childs($id, $category_structure);
    			if(count($childs_topics)>0){
    				$str_a=array();
    				foreach ($childs_topics as $ct){
    					$str_a[]='?';
    				}
    				
    				$params=$childs_topics;
    				array_unshift($params, $id);
    				
    				$query='UPDATE '.DB_PREFIX.'_data SET topic_id=? WHERE topic_id IN ('.implode(',', $str_a).')';
    				$stmt=$DBC->query($query, $params);
    				
	    			foreach($childs_topics as $did){
	    				$this->deleteRecord($did);
	    			}
    			}
    		}
    	}elseif($delete_option=='delete_branch'){
    		if($childs_delete_option=='move_up'){
    			$query='SELECT parent_id FROM '.DB_PREFIX.'_topic WHERE id=? LIMIT 1';
    			$stmt=$DBC->query($query, array($id));
    			$ar=$DBC->fetch($stmt);
    			$parent_id=(int)$ar['parent_id'];
    			 
    			 
    			$category_structure=$this->loadCategoryStructure();
    			$childs_topics=array();
    			$childs_topics=$this->get_all_childs($id, $category_structure);
    			 
    			if(count($childs_topics)>0){
    				$str_a=array();
    				foreach ($childs_topics as $ct){
    					$str_a[]='?';
    				}
    				
    				$childs_topics[]=$id;
    				$str_a[]='?';
    		
    				$params=$childs_topics;
    				array_unshift($params, $parent_id);
    		
    				$query='UPDATE '.DB_PREFIX.'_data SET topic_id=? WHERE topic_id IN ('.implode(',', $str_a).')';
    				$stmt=$DBC->query($query, $params);
    		
    				foreach($childs_topics as $did){
    					$this->deleteRecord($did);
    				}
    			}
    		}
    	}
    }
    
    
    /**
     * Add record
     * @param void
     * @return boolean
     */
    function addRecord ( ) {
        $languages = Multilanguage::foreignLanguages();
        foreach ( $languages as $language_id => $language_title ) {
        	$lang_string .= "name_{$language_id}='".$this->escape($this->getRequestValue('name_'.$language_id))."',";
        }
        
        $query = "insert into ".DB_PREFIX."_topic set 
        	name='".$this->escape($this->getRequestValue('name'))."',
        	{$lang_string} 
        	parent_id='".(int)$this->getRequestValue('parent_id')."', url='".$this->getRequestValue('url')."',
        	meta_title='".$this->escape($this->getRequestValue('meta_title'))."', 
        	meta_keywords='".$this->escape($this->getRequestValue('meta_keywords'))."', 
        	meta_description='".$this->escape($this->getRequestValue('meta_description'))."', 
        	description='".$this->getRequestValue('description')."'";
        //echo $query;
        $this->db->exec($query);
        if ( !$this->db->success ) {
        	echo $this->db->error;
        }else{
        	$new_record_id = $this->db->last_insert_id();
        	$imgs=$this->editImageMulti('topic', 'topic', 'id', $new_record_id);
        }
        
        return true;
    }
    
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
    		$el['private'][]=array('html'=>'<input type="hidden" name="id" value="'.$this->getRequestValue('id').'" />');
    	} else {
    		$el['private'][]=array('html'=>'<input type="hidden" name="do" value="edit_done" />');
    		$el['private'][]=array('html'=>'<input type="hidden" name="id" value="'.$form_data['id']['value'].'" />');
    	}
    	$el['private'][]=array('html'=>'<input type="hidden" name="action" value="structure">');
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
     * Get form
     * @param string $action action
     * @return string
     */
    function getForm ( $action = 'new' ) {
    	$form_data=$this->getStrModel();
    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
    	$OM=new Object_Manager();
    	$rs = $OM->get_form($form_data['structure'], $action, 0, '', SITEBILL_MAIN_URL.'/admin/index.php?action=structure');
    	return $rs;
        global $debug_mode;
        $editor_code=$this->getConfigValue('editor');
        
        $languages = Multilanguage::foreignLanguages();
        
        
        $id = 'descr';
        
        if ( $editor_code == 'ckeditor' ) {
        	$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/ckeditor/ckeditor.js"></script>';
        	$rs .= '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/ckeditor/adapters/jquery.js"></script>';
        	$rs .= '<script type="text/javascript">
        	$(document).ready(function() {
        	$("textarea#'.$id.'").ckeditor({
        	filebrowserBrowseUrl : \'/ckfinder/ckfinder.html\',
        	filebrowserImageBrowseUrl : \'/ckfinder/ckfinder.html?Type=Images\',
        	filebrowserFlashBrowseUrl : \'/ckfinder/ckfinder.html?Type=Flash\',
        	filebrowserUploadUrl : \'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files\',
        	filebrowserImageUploadUrl : \'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images\',
        	filebrowserFlashUploadUrl : \'/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash\'
        });
        });
        </script>';
        }else {
        	$rs .= '<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/js/cleditor/jquery.cleditor.css" />
        	<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/js/cleditor/jquery.cleditor.min.js"></script>
        	<script type="text/javascript">
        	$(document).ready(function() {
        	$("textarea#'.$id.'").cleditor();
        });
        </script>
        ';
        }
        
        
        $rs .= '<form method="post" action="index.php" name="rentform" enctype="multipart/form-data">';
        $rs .= '<table border="0">';
        
        $rs .= '<tr>';
        $rs .= '<td colspan="2" style="text-align: center;"><b>'.sprintf(Multilanguage::_('L_NEED_REQUIERD_FIELDS'),'<span class="error">*</span>').'</b></td>';
        $rs .= '</tr>';
        
        if ( $this->GetError() ) {
            $rs .= '<tr>';
            $rs .= '<td></td>';
            $rs .= '<td><span class="error">'.$this->GetError().'</span></td>';
            $rs .= '</tr>';
        }
        
        /*
        $rs .= '<tr>';
        $rs .= '<td class="left_column">Тип операции <span class="error">*</span>:</td>';
        $rs .= '<td>'.$this->get_operation_type_select_box( $this->getRequestValue('operation_type_id') ).'</td>';
        $rs .= '</tr>';
        */
        
        $rs .= '<tr>';
        $rs .= '<td class="left_column">'.Multilanguage::_('PARENT_TOPIC','system').':</td>';
        $rs .= '<td>'.$this->getCategorySelectBox( $this->getRequestValue('parent_id') ).'</td>';
        $rs .= '</tr>';
        
        $rs .= '<tr>';
        $rs .= '<td class="left_column">'.Multilanguage::_('TOPIC_NAME','system').'<span class="error">*</span>:</td>';
        $rs .= '<td><input type="text" name="name" value="'.$this->getRequestValue('name').'"></td>';
        $rs .= '</tr>';
        
        foreach ( $languages as $language_id => $language_title ) {
            $rs .= '<tr>';
            $rs .= '<td class="left_column">'.Multilanguage::_('TOPIC_NAME','system').' <b>'.$language_id.'</b>:</td>';
            $rs .= '<td><input type="text" name="name_'.$language_id.'" value="'.$this->getRequestValue('name_'.$language_id).'"></td>';
            $rs .= '</tr>';
        }
        
        $rs .= '<tr>';
        $rs .= '<td class="left_column">'.Multilanguage::_('META_TITLE','system').':</td>';
        $rs .= '<td><input type="text" name="meta_title" value="'.$this->getRequestValue('meta_title').'"></td>';
        $rs .= '</tr>';
        
        $rs .= '<tr>';
        $rs .= '<td class="left_column">'.Multilanguage::_('META_KEYWORDS','system').':</td>';
        $rs .= '<td><textarea name="meta_keywords" cols="50" rows="5">'.$this->getRequestValue('meta_keywords').'</textarea></td>';
        $rs .= '</tr>';
        
        $rs .= '<tr>';
        $rs .= '<td class="left_column">'.Multilanguage::_('META_DESCRIPTION','system').':</td>';
        $rs .= '<td><textarea name="meta_description" cols="50" rows="5">'.$this->getRequestValue('meta_description').'</textarea></td>';
        $rs .= '</tr>';
        
        
        $rs .= '<tr>';
        $rs .= '<td class="left_column">'.Multilanguage::_('DESCRIPTION','system').':</td>';
        $rs .= '<td><textarea id="'.$id.'" name="description" rows="10" cols="30">'.$this->getRequestValue('description').'</textarea></td>';
        $rs .= '</tr>';
        
        $rs .= '<tr>';
        $rs .= '<td class="left_column">'.Multilanguage::_('FINE_URL_TEXT','system').' <span class="error">*</span>:</td>';
        $rs .= '<td><input type="text" name="url" value="'.$this->getRequestValue('url').'"></td>';
        $rs .= '</tr>';
        
        if(1==$this->getConfigValue('allow_topic_images')){
        
	        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
	    	$form_generator = new Form_Generator();
	    	
	    	$form_data['name'] = 'image';
	    	$form_data['table_name'] = 'topic';
	    	$form_data['primary_key'] = 'id';
	    	$form_data['primary_key_value'] = $this->getRequestValue('id');
	    	$form_data['action'] = 'structure';
	    	$form_data['title'] = Multilanguage::_('L_UPLOADER_TITLE');
	    	$form_data['value'] = '';
	    	$form_data['length'] = 40;
	    	$form_data['type'] = 'uploadify_image';
	    	$form_data['required'] = 'off';
	    	$form_data['unique'] = 'off';
	
	    	$rs .= $form_generator->get_uploadify_row($form_data);
        }
        
        
        $rs .= '<tr>';
        $rs .= '<td></td>';
        if ( $action == 'edit' ) {
            $rs .= '<input type="hidden" name="do" value="edit_done">';
            $rs .= '<input type="hidden" name="id" value="'.$this->getRequestValue('id').'">';
        } else {
            $rs .= '<input type="hidden" name="do" value="done">';
        }
        $rs .= '<input type="hidden" name="action" value="structure">';
        
        $rs .= '<td><input type="submit" value="'.Multilanguage::_('L_TEXT_SAVE').'"></td>';
        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '</form>';
        
        return $rs;
    }
    
    
    /**
     * Load
     * @param int $record_id record ID
     * @return boolean
     */
    function load ( $record_id ) {
        
        $query = "select * from ".DB_PREFIX."_topic where id=$record_id";
        //echo $query;
        $this->db->exec($query);
        $this->db->fetch_assoc();
        
        $this->setRequestValue('name', $this->db->row['name']);
        
        $languages = Multilanguage::foreignLanguages();
        foreach ( $languages as $language_id => $language_title ) {
            $this->setRequestValue('name_'.$language_id, $this->db->row['name_'.$language_id]);
        }
        
        
        $this->setRequestValue('id', $this->db->row['id']);
        $this->setRequestValue('url', $this->db->row['url']);
        $this->setRequestValue('description', $this->db->row['description']);
        $this->setRequestValue('parent_id', $this->db->row['parent_id']);
        $this->setRequestValue('meta_title', $this->db->row['meta_title']);
        $this->setRequestValue('meta_keywords', $this->db->row['meta_keywords']);
        $this->setRequestValue('meta_description', $this->db->row['meta_description']);
    }
    
    /**
     * Check data
     * @param void
     * @return boolean
     */
    function checkData () {
        if ( $this->getRequestValue('name') == '' ) {
            $this->riseError(Multilanguage::_('NOT_SET_TOPIC_NAME','system'));
            return false;
        }
    	if ( $this->getRequestValue('parent_id') == $this->getRequestValue('id') ) {
            $this->riseError(Multilanguage::_('CANT_BE_PARENT_YOURSELF','system'));
            return false;
        }
        
        if ( $this->getRequestValue('url') == '' ) {
        	$this->riseError('Не указан ЧПУ каталога');
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
    	$rs .= '<a href="?action=structure&do=new" class="btn btn-primary">'.Multilanguage::_('L_ADD_RECORD_BUTTON').'</a> ';
		//$rs = '<a href="?action=structure" class="btn btn-primary">'.Multilanguage::_('TOPIC_LIST','system').'</a>';
		//$rs .= '<a href="?action=structure&do=chains" class="btn btn-primary">Структурные цепочки</a>';
        /*$rs .= '
<div class="navbar">
  <div class="navbar-inner">
    <div class="container">
        
        <a href="?action=structure&do=new" class="btn btn-primary">'.Multilanguage::_('ADD_TOPIC','system').'</a>
</div>        
</div>        
</div> 
        		<p>Вид менеджера: '.((isset($_SESSION['structure_manager_grid_type']) && $_SESSION['structure_manager_grid_type']=='new') ? 'Новый <a href="'.SITEBILL_MAIN_URL.'/admin/index.php?action=structure&structure_manager_grid_type=old">Старый</a>' : '<a href="'.SITEBILL_MAIN_URL.'/admin/index.php?action=structure&structure_manager_grid_type=new">Новый</a> Старый').'</p>       
';*/
    	
		//$rs .= '<a href="?action=structure&do=associations" class="btn btn-primary">'.Multilanguage::_('COMPARISONS','system').'</a>';
        return $rs;
    }
    
    /**
     * Возвращает ассоциативный массив соответствий id категорий и составным иерархическим урлам
     * @return array
     * 
     */
    function loadCategoriesUrls(){
    	if($this->getConfigValue('apps.cache.enable')==1){
    		$Cache=Cache::getInstance();
    		if($Cache->isValid('categories_urls','expired')){
    			//no caching needed
    			$ret=$Cache->getValue('categories_urls');
    		}else{
    			//caching needed
    			$ret=$this->createCategoriesUrls();
    			$Cache->addValue('categories_urls', $ret, (time()+86400));
    		}
    	}else{
    		//working without cache
    		$ret=$this->createCategoriesUrls();
    	}
    	return $ret;
    }
    
    /**
     * Создает ассоциативный массив соответствий id категорий и составным иерархическим урлам
     * @return array
     */
    private function createCategoriesUrls(){
    	$ret=array();
    	$_ret=array();
    	$query='SELECT id, parent_id, url AS name FROM '.DB_PREFIX.'_topic';
    	$this->db->exec($query);
    	if ( !$this->db->success ) {
    	    echo $this->db->error.'<br>';
    	}
    	while($this->db->fetch_assoc()){
    		$categories[$this->db->row['id']]=$this->db->row['name'];
    		$items[$this->db->row['id']]=$this->db->row['parent_id'];
    		$points[]=$this->db->row['id'];
    	}
    	if(count($points)>0){
    		foreach($points as $p){
    			$chain=array();
    			$chain[]=$categories[$p];
    			$this->appendParent($p,$items,$chain,$categories);
    			$_ret[$p]['chain_parts']=$chain;
    		}
    		
    		foreach($_ret as $k=>$r){
    			$ret[$k]=implode('/',$r['chain_parts']);
    		}
    	}
    	return $ret;
    }
    
    /**
     * Ищет транслитерированный урл предка для конкретного элемента
     */
    private function appendParent($child_id,&$items,&$chain,$categories){
    	if((int)$items[$child_id]!==0){
    		array_unshift($chain,$categories[$items[$child_id]]);
    		$this->appendParent($items[$child_id],$items,$chain,$categories);
    	}
    }
    
	function createCatalogChains(){
		$ret=array();
		$query='SELECT id, parent_id, LOWER(name) AS name FROM '.DB_PREFIX.'_topic';
		$this->db->exec($query);
		while($this->db->fetch_assoc()){
			$categories[$this->db->row['id']]=$this->db->row['name'];
			$items[$this->db->row['id']]=$this->db->row['parent_id'];
			$points[]=$this->db->row['id'];
		}
		foreach($points as $p){
			$chain=$categories[$p];
			$chain_num=$p;
			$this->findParent($p,$items,$chain,$chain_num,$categories);
			$ret[$p]=$chain;
			$ret_num[$p]=$chain_num;
			//echo $chain.'<br>';
		}
		
		return $rs=array('txt'=>$ret,'num'=>$ret_num);
		//print_r($chain);
	}
	
	function findParent($child_id,&$items,&$chain,&$chain_num,$categories){
		if((int)$items[$child_id]!==0){
			//echo $child_id.' has parent '.$items[$child_id].'<br>';;
			$chain=$categories[$items[$child_id]].'|'.$chain;
			$chain_num=$items[$child_id].'|'.$chain_num;
			$this->findParent($items[$child_id],$items,$chain,$chain_num,$categories);
		}
	}
    
    /**
     * Load category structure
     * @param void
     * @return array
     */
    function loadCategoryStructure () {
    	
    	if(self::$_category_structure!==NULL){
    		return self::$_category_structure;
    	}
    	
    	$query = "SELECT t.* FROM ".DB_PREFIX."_topic t order by `order` ";
    	
    	$this->db->exec($query);
    	while ( $this->db->fetch_assoc() ) {
    		if ( Multilanguage::get_current_language() != 'ru' and $this->db->row['name_'.Multilanguage::get_current_language()] != '' ) {
    			$this->db->row['name'] = $this->db->row['name_'.Multilanguage::get_current_language()];
    		}
    	
    		$ret['catalog'][$this->db->row['id']] = $this->db->row;
    		$ret['childs'][$this->db->row['parent_id']][] = $this->db->row['id'];
    	}
        /*
    	if($this->getConfigValue('apps.cache.enable')==1){
    		$Cache=Cache::getInstance();
	    	if($Cache->isValid('catalog_structure','expired')){
	    		//no caching needed
	    		$ret=$Cache->getValue('catalog_structure');
		    }else{
	    		//caching needed
	    		$query = "SELECT t.* FROM ".DB_PREFIX."_topic t order by `order` ";
		        $this->db->exec($query);
		        while ( $this->db->fetch_assoc() ) {
		            if ( Multilanguage::get_current_language() != 'ru' and $this->db->row['name_'.Multilanguage::get_current_language()] != '' ) {
		                $this->db->row['name'] = $this->db->row['name_'.Multilanguage::get_current_language()];
		            }
		            
		            $rs['catalog'][$this->db->row['id']] = $this->db->row;
		            $rs['childs'][$this->db->row['parent_id']][] = $this->db->row['id'];
		        }
		        
		        $Cache->addValue('catalog_structure', $rs, (time()+86400));
		        $ret=$rs;
	    	}
	    }else{
		 	//working without cache
    		$query = "SELECT t.* FROM ".DB_PREFIX."_topic t order by `order` ";
    		
	        $this->db->exec($query);
	        while ( $this->db->fetch_assoc() ) {
	            if ( Multilanguage::get_current_language() != 'ru' and $this->db->row['name_'.Multilanguage::get_current_language()] != '' ) {
	            	$this->db->row['name'] = $this->db->row['name_'.Multilanguage::get_current_language()];
	            }
	             
	            $ret['catalog'][$this->db->row['id']] = $this->db->row;
	            $ret['childs'][$this->db->row['parent_id']][] = $this->db->row['id'];
	        }
    	}*/
    	
    	if(1==$this->getConfigValue('apps.seo.level_enable')){
    		$urls=$this->loadCategoriesUrls();
    		if(count($ret['catalog'])>0){
    			foreach($ret['catalog'] as $k=>$v){
    				$ret['catalog'][$k]['url'] = $urls[$v['id']];
    			}
    		}
    		
    	}
    	
    	if(1==$this->getConfigValue('allow_topic_images')){
    		if(is_array($ret['catalog']) && count($ret['catalog']>0)){
    			foreach($ret['catalog'] as $k=>$v){
    				$query = "select i.* from ".DB_PREFIX."_topic_image as li, ".DB_PREFIX."_image as i where li.id=$k and li.image_id=i.image_id order by li.sort_order";
    				$this->db->exec($query);
    				while($this->db->fetch_assoc()){
    					$ret['catalog'][$k]['images'][]=$this->db->row;
    				}
    			}
    		}
	    	
    	}
    	
    	
    	
    	
    	$current=$this->urlAnalizer();
		if($current!==FALSE){
			$this->findCurrent($ret,$current);
		}
		/*
		echo '<pre>';
		print_r($ret);
		echo '</pre>';
		*/
		self::$_category_structure=$ret;
		return $ret;
	    
    }
    
    
    /**
     * Get all childs
     * @param array $category_structure
     */
    function get_all_childs ( $category_id, $category_structure ) {
    	$ra=array();
    	//echo 'category_id = '.$category_id.'<br>';
        if ( isset($category_structure['childs'][$category_id]) && count($category_structure['childs'][$category_id]) > 0 ) {
            $ra = $category_structure['childs'][$category_id];

            foreach ( $category_structure['childs'][$category_id] as $item_id => $child_id ) {
                if ( isset($category_structure['childs'][$child_id]) && count( $category_structure['childs'][$child_id] ) > 0 ) {
                    $ra = array_merge($ra, $this->get_all_childs($child_id, $category_structure));
                 }
            }
        }
        return $ra;
    }
    
    /**
     * Load data structure
     * @param int $user_id
     * @return array
     */
    function load_data_structure ( $user_id, $params = array(), $search_params=array() ) {
        $where_array = array();
        //echo '<pre>';
        //print_r($params);
        //echo '</pre>';
        if ( $user_id == 0 ) {
            if ( $params['active'] == 1 ) {
                $where_array[] = 're_data.active=1';
                
            } elseif ( $params['active'] == 'notactive' ) {
                $where_array[] = 're_data.active=0';
            }
            
        	if ( $params['hot'] == 1 ) {
                $where_array[] = 're_data.hot=1';
            }
            
            if(count($search_params)>0){
            	foreach($search_params as $v){
            		$where_array[]='re_data.'.$v;
            	}
            }
            /*
            if(isset($params['realty_type_id'])){
            	$where_array[] = 're_data.realty_type_id='.(int)$params['realty_type_id'];
            }
            */
            if ( count($where_array) > 0 ) {
                $where = ' WHERE '.implode(' AND ', $where_array);
            }
            //$query = "SELECT id, topic_id FROM ".DB_PREFIX."_data ".$where;
            $query = "SELECT COUNT(id) as total, topic_id FROM ".DB_PREFIX."_data ".$where." GROUP BY topic_id";
        } else {
           // $query = "SELECT id, topic_id FROM ".DB_PREFIX."_data  where user_id = $user_id";
        	$query = "SELECT COUNT(id) as total, topic_id FROM ".DB_PREFIX."_data  where user_id = $user_id GROUP BY topic_id";
        }
        //echo $query.'<br>';
        $this->db->exec($query);
        while ( $this->db->fetch_assoc() ) {
            //$ret['data'][$user_id][$this->db->row['topic_id']]++;
            $ret['data'][$user_id][$this->db->row['topic_id']]=$this->db->row['total'];
        }
        return $ret;
    }
    
	/**
     * Load data structure for shop
     * @param int $user_id
     * @return array
     */
    function load_data_structure_shop ( $user_id, $params = array() ) {
        $where_array = array();
        //echo '<pre>';
        //print_r($params);
        //echo '</pre>';
        $language_id=((int)$this->getRequestValue('language_id')==0 ? 0 : (int)$this->getRequestValue('language_id'));
        
        
        if ( $user_id == 0 ) {
        	
        	//$enable_publication_limit=$this->getConfigValue('apps.shop.user_limit_enable');
        	
	        if($params['enable_publication_limit']==1){
		    	$where_array[] = '(('.DB_PREFIX.'_shop_product.product_add_date+'.DB_PREFIX.'_user.publication_limit*24*3600)>'.time().')';
		    }
            
            if ( $params['active'] == 1 ) {
                $where_array[] = DB_PREFIX.'_shop_product.active=1';
            } elseif ( $params['active'] == 'notactive' ) {
                $where_array[] = DB_PREFIX.'_shop_product.active=0';
            }
            
            if(isset($params['city_id'])){
            	$where_array[] = '('.DB_PREFIX.'_shop_product.city_id='.$params['city_id'].')';
            }
            
            $where_array[]=DB_PREFIX.'_shop_product.language_id='.$language_id;
            
            if ( count($where_array) > 0 ) {
                $where = ' WHERE '.implode(' AND ', $where_array);
            }
            //$query = "SELECT product_id, category_id FROM ".DB_PREFIX."_shop_product ".$where;
            $query = "SELECT COUNT(".DB_PREFIX."_shop_product.product_id) as total, ".DB_PREFIX."_shop_product.category_id FROM ".DB_PREFIX."_shop_product LEFT JOIN ".DB_PREFIX."_user ON ".DB_PREFIX."_shop_product.user_id=".DB_PREFIX."_user.user_id ".$where." GROUP BY ".DB_PREFIX."_shop_product.category_id";
        } else {
        	
        	if ( $params['active'] == 1 ) {
                $where_array[] = DB_PREFIX.'_shop_product.active=1';
            } elseif ( $params['active'] == 'notactive' ) {
                $where_array[] = DB_PREFIX.'_shop_product.active=0';
            } elseif($params['archived'] == 1){
            	$where_array[] = '(('.DB_PREFIX.'_shop_product.product_add_date+'.DB_PREFIX.'_user.publication_limit*24*3600)<'.time().')';
            } elseif($params['archived'] == 'notarchived'){
            	$where_array[] = '(('.DB_PREFIX.'_shop_product.product_add_date+'.DB_PREFIX.'_user.publication_limit*24*3600)>'.time().')';
            }
            
            $where_array[]=DB_PREFIX.'_shop_product.user_id = '.$user_id;
            
            $where = ' WHERE '.implode(' AND ', $where_array);
            //$query = "SELECT product_id, category_id FROM ".DB_PREFIX."_shop_product  where user_id = $user_id";
            $query = "SELECT COUNT(product_id) as total, category_id FROM ".DB_PREFIX."_shop_product LEFT JOIN ".DB_PREFIX."_user ON ".DB_PREFIX."_shop_product.user_id=".DB_PREFIX."_user.user_id   ".$where." GROUP BY category_id";
        }
        //echo $query.'<br>';
        $this->db->exec($query);
        while ( $this->db->fetch_assoc() ) {
            //$ret['data'][$user_id][$this->db->row['category_id']]++;
            $ret['data'][$user_id][$this->db->row['category_id']]=$this->db->row['total'];
        }
        return $ret;
    }
    
    /**
     * Load data structure for price
     * @param int $user_id
     * @return array
     */
    function load_data_structure_price ( $user_id, $params = array() ) {
    	$where_array = array();
    	//echo '<pre>';
    	//print_r($params);
    	//echo '</pre>';
    	$language_id=((int)$this->getRequestValue('language_id')==0 ? 0 : (int)$this->getRequestValue('language_id'));
    
    
    		//$enable_publication_limit=$this->getConfigValue('apps.shop.user_limit_enable');
    		 
    
    		if ( count($where_array) > 0 ) {
    			$where = ' WHERE '.implode(' AND ', $where_array);
    		}
    		//$query = "SELECT product_id, category_id FROM ".DB_PREFIX."_shop_product ".$where;
    		$query = "SELECT COUNT(".DB_PREFIX."_price.price_id) as total, ".DB_PREFIX."_price.category_id FROM ".DB_PREFIX."_price GROUP BY ".DB_PREFIX."_price.category_id";
    	//echo $query.'<br>';
    	$this->db->exec($query);
    	while ( $this->db->fetch_assoc() ) {
    		//$ret['data'][$user_id][$this->db->row['category_id']]++;
    		$ret['data'][$user_id][$this->db->row['category_id']]=$this->db->row['total'];
    	}
    	return $ret;
    }
    
    
    
    /**
     * Get category select box
     * @param int $current_category_id category ID
     * @param mixed $ajax_function
     * @return string
     */
    function getCategorySelectBox ( $current_category_id, $ajax_function = false ) {
    	//echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        //echo '<pre>';
        //print_r($category_structure);
        $level = 1;
        $rs = '';
        if ( $ajax_function ) {
            $rs .= '<select name="parent_id" id="parent_id" onchange="'.$ajax_function.'">';
        } else {
            $rs .= '<select name="parent_id">';
        }
        $rs .= '<option value="0">'.Multilanguage::_('L_CHOOSE_TOPIC').'</option>';
        if(isset($category_structure['childs'][0]) && count($category_structure['childs'][0])>0){
        	foreach ( $category_structure['childs'][0] as $item_id => $categoryID ) {
        		//echo $categoryID.'<br>';
        		//echo 'items = '.$items.'<br>';
        		if ( $current_category_id == $categoryID ) {
        			$selected = " selected ";
        		} else {
        			$selected = "";
        		}
        		 
        		/*if($this->getConfigValue('disable_root_structure_select')==1){
        		 $disabled=' disabled="disabled"';
        		}else{
        		$disabled='';
        		}*/
        	
        		$rs .= '<option value="'.$categoryID.'" '.$selected.$disabled.'>'.str_repeat(' . ', $level).$category_structure['catalog'][$categoryID]['name'].'</option>';
        		$rs .= $this->getChildNodes($categoryID, $category_structure, $level + 1, $current_category_id);
        	}
        }
        
        $rs .= '</select>';
        return $rs;
    }
    
    /**
     * Get category select box
     * @param string $name name
     * @param int $current_category_id category ID
     * @param mixed $ajax_function
     * @return string
     */
    function getCategorySelectBoxWithName ( $name, $current_category_id, $ajax_function = false ) {
    	//echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        //echo '<pre>';
        //print_r($category_structure);
        
        $level = 1;
        $rs = '';
        $multiple=false;
        if(is_array($current_category_id)){
        	$multiple=true;
        }
        if ( $ajax_function ) {
            $rs .= '<select name="'.$name.($multiple ? '[]' : '').'" id="'.$name.'" onchange="'.$ajax_function.'"'.($multiple ? ' multiple="multiple"' : '').'>';
        } else {
            $rs .= '<select name="'.$name.($multiple ? '[]' : '').'"'.($multiple ? ' multiple="multiple"' : '').' id="'.$name.'">';
        }
        if(!$multiple){
        	$rs .= '<option value="0">'.Multilanguage::_('L_CHOOSE_TOPIC').'</option>';
        }
        if(isset($category_structure['childs'][0]) && count($category_structure['childs'][0])>0){
        	foreach ( $category_structure['childs'][0] as $item_id => $categoryID ) {
        		if($multiple){
        			if ( in_array($categoryID, $current_category_id)) {
        				$selected = " selected ";
        			} else {
        				$selected = "";
        			}
        		}else{
        			if ( $current_category_id == $categoryID ) {
        				$selected = " selected ";
        			} else {
        				$selected = "";
        			}
        		}
        	
        		if($this->getConfigValue('disable_root_structure_select')==1 && $_SESSION['allow_disable_root_structure_select']===true){
        			$disabled=' disabled="disabled" style="background-color:#eee;"';
        			 
        		}else{
        			$disabled='';
        		}
        		 
        		 
        	
        		$rs .= '<option value="'.$categoryID.'" '.$selected.$disabled.'>'.str_repeat(' . ', $level).$category_structure['catalog'][$categoryID]['name'].'</option>';
        		$rs .= $this->getChildNodes($categoryID, $category_structure, $level + 1, $current_category_id);
        	}
        }
        
        $_SESSION['allow_disable_root_structure_select']=false;
        $rs .= '</select>';
        return $rs;
    }
    
    function getCategoryCheckboxes ( $name, $current_category_id, $ajax_function = false ) {
    	$category_structure = $this->loadCategoryStructure();
    	$rs = '';
    	if(isset($category_structure['childs'][0]) && count($category_structure['childs'][0])>0){
    		$rs .= '<style></style>';
    		$rs .= '<div class="checkbox_collection">';
    		$rs .= '<a href="#" class="checkbox_collection_decheck">Очистить все</a>';
    		foreach ( $category_structure['childs'][0] as $item_id => $categoryID ) {
    			
    			$rs .= '<div class="ait_bc">';
    			$rs .= '<div class="ait_bc_h"><input name="'.$name.'[]" value="'.$category_structure['catalog'][$categoryID]['id'].'" type="checkbox"'.( in_array($categoryID, $current_category_id) ? ' checked="checked"' : '').' /> '.$category_structure['catalog'][$categoryID]['name'].'</div>';
    			$rs .= $this->getChildNodesCheckboxes($name, $categoryID, $category_structure, $current_category_id);
    			$rs .= '</div>';
    			
    		}
    		$rs .= '</div>';
    	}
    	return $rs;
    }
    
    function getChildNodesCheckboxes($name, $categoryID, $category_structure, $current_category_id) {
    	$rs='';
    	if(isset($category_structure['childs'][$categoryID]) && count($category_structure['childs'][$categoryID])>0){
	        foreach ( $category_structure['childs'][$categoryID] as $child_id ) {
	        	$rs .= '<div class="ait_bc"">';
	        	$rs .= '<div class="ait_bc_h"><input name="'.$name.'[]" value="'.$category_structure['catalog'][$child_id]['id'].'" type="checkbox"'.( in_array($child_id, $current_category_id) ? ' checked="checked"' : '').' /> '.$category_structure['catalog'][$child_id]['name'].'</div>';
	        	
	            if ( isset($category_structure['childs'][$child_id]) ) {
	            	if ( count($category_structure['childs'][$child_id]) > 0 ) {
	            		$rs .= $this->getChildNodesCheckboxes($name, $child_id, $category_structure, $current_category_id);
	            	}
	            }
	            $rs .= '</div>';
	        }
    	}
        return $rs;
    }
    
	function getShopCategorySelectBoxWithName ( $name, $current_category_id, $ajax_function = false ) {
    	//echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadShopCategoryStructure();
        //echo '<pre>';
        //print_r($category_structure);
        $level = 1;
        $rs = '';
        if ( $ajax_function ) {
            $rs .= '<select name="'.$name.'" id="'.$name.'" onchange="'.$ajax_function.'">';
        } else {
            $rs .= '<select name="'.$name.'">';
        }
        $rs .= '<option value="0">'.Multilanguage::_('L_CHOOSE_TOPIC').'</option>';
        foreach ( $category_structure['childs'][0] as $item_id => $categoryID ) {
            //echo $categoryID.'<br>';
            //echo 'items = '.$items.'<br>';
            if ( $current_category_id == $categoryID ) {
        		$selected = " selected ";
        	} else {
        		$selected = "";
        	}
            
            $rs .= '<option value="'.$categoryID.'" '.$selected.'>'.str_repeat(' . ', $level).$category_structure['catalog'][$categoryID]['category_name'].'</option>';
            $rs .= $this->getShopChildNodes($categoryID, $category_structure, $level + 1, $current_category_id);
        }
        $rs .= '</select>';
        return $rs;
    }
    
    /**
     * Load mark structure
     * @param void
     * @return array
     */
    function load_mark_structure () {
        $query = "SELECT * FROM ".DB_PREFIX."_mark order by `name` ";
        //echo $query;
        $this->db->exec($query);
        while ( $this->db->fetch_assoc() ) {
            $ret['mark'][$this->db->row['mark_id']] = $this->db->row;
            $ret['childs'][$this->db->row['parent_id']][] = $this->db->row['mark_id'];
        }
        return $ret;
    }
    
    /**
     * Load coachwork structure
     * @param void
     * @return array
     */
    function load_coachwork_structure () {
        $query = "SELECT * FROM ".DB_PREFIX."_coachwork order by `name` ";
        //echo $query;
        $this->db->exec($query);
        while ( $this->db->fetch_assoc() ) {
            $ret['coachwork'][$this->db->row['coachwork_id']] = $this->db->row;
            $ret['childs'][$this->db->row['parent_id']][] = $this->db->row['coachwork_id'];
        }
        return $ret;
    }

    /**
     * Load model structure
     * @param void
     * @return array
     */
    function load_model_structure () {
        $query = "SELECT * FROM ".DB_PREFIX."_model order by `name` ";
        //echo $query;
        $this->db->exec($query);
        while ( $this->db->fetch_assoc() ) {
            $ret['model'][$this->db->row['model_id']] = $this->db->row;
            $ret['childs'][$this->db->row['mark_id']][] = $this->db->row['model_id'];
        }
        return $ret;
    }
    
    /**
     * Load modification structure
     * @param void
     * @return array
     */
    function load_modification_structure () {
        $query = "SELECT * FROM ".DB_PREFIX."_modification order by `name` ";
        //echo $query;
        $this->db->exec($query);
        while ( $this->db->fetch_assoc() ) {
            $ret['modification'][$this->db->row['modification_id']] = $this->db->row;
            $ret['childs'][$this->db->row['model_id']][] = $this->db->row['modification_id'];
        }
        return $ret;
    }
    
    
    /**
     * Get mark select box
     * @param int $current_mark_id mark ID
     * @param mixed $ajax_function ajax function
     * @return string
     */
    function getMarkSelectBox ( $current_mark_id, $ajax_function = false ) {
    	//echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        $mark_structure = $this->load_mark_structure();
        //echo '<pre>';
        //print_r($category_structure);
        $level = 1;
        $rs = '';
        $rs .= '<div id="mark_id_div">';
        if ( $ajax_function ) {
            $rs .= '<select name="mark_id" id="mark_id" onchange="'.$ajax_function.'">';
        } else {
            $rs .= '<select name="mark_id" id="mark_id">';
        }
        $rs .= '<option value="0">..</option>';
        foreach ( $category_structure['childs'][0] as $item_id => $categoryID ) {
            //echo $categoryID.'<br>';
            //echo 'items = '.$items.'<br>';
            if ( $current_category_id == $categoryID ) {
        		$selected = " selected ";
        	} else {
        		$selected = "";
        	}
            
            $rs .= '<option disabled>'.str_repeat(' . ', $level).$category_structure['catalog'][$categoryID]['name'].'</option>';
            $rs .= $this->get_mark_option_items($categoryID, $mark_structure, $level, $current_mark_id);
            $rs .= $this->getChildNodes($categoryID, $category_structure, $level + 1, $current_category_id);
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }
    
    /**
     * Get flat mark select box
     * @param int $categoryID category ID
     * @param int $current_mark_id selected mark_id
     * @return string
     */
    function get_flat_mark_select_box ( $categoryID, $current_mark_id ) {
        $mark_structure = $this->load_mark_structure();
        $rs = '';
        $rs .= '<div id="mark_id_div">';
        $rs .= '<select name="mark_id" id="mark_id" onchange="update_model_list()">';
        $rs .= '<option value="0">'.Multilanguage::_('L_CHOOSE_MARK').'</option>';
        if ( is_array($mark_structure['childs'][$categoryID]) ) {
            foreach ( $mark_structure['childs'][$categoryID] as $mark_id ) {
                if ( $current_mark_id == $mark_id ) {
            		$selected = " selected ";
            	} else {
        	    	$selected = "";
        	    }
                $rs .= '<option value="'.$mark_id.'" '.$selected.'>'.$mark_structure['mark'][$mark_id]['name'].'</option>';
            }
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }
    
    /**
     * Get flat coachwork select box
     * @param int $categoryID category ID
     * @param int $current_coachwork_id selected coachwork_id
     * @return string
     */
    function get_flat_coachwork_select_box ( $categoryID, $current_coachwork_id ) {
        $coachwork_structure = $this->load_coachwork_structure();
        $rs = '';
        $rs .= '<div id="coachwork_id_div">';
        $rs .= '<select name="coachwork_id" id="coachwork_id">';
        $rs .= '<option value="0">'.Multilanguage::_('L_CHOOSE_BODYTYPE').'</option>';
        if ( is_array($coachwork_structure['childs'][$categoryID]) ) {
            foreach ( $coachwork_structure['childs'][$categoryID] as $coachwork_id ) {
                if ( $current_coachwork_id == $coachwork_id ) {
            		$selected = " selected ";
            	} else {
        	    	$selected = "";
        	    }
                $rs .= '<option value="'.$coachwork_id.'" '.$selected.'>'.$coachwork_structure['coachwork'][$coachwork_id]['name'].'</option>';
            }
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }
    
    /**
     * Get flat model select box
     * @param int $mark_id mark ID
     * @param int $current_model_id selected model_id
     * @return string
     */
    function get_flat_model_select_box ( $mark_id, $current_model_id ) {
        $model_structure = $this->load_model_structure();
        $rs = '';
        $rs .= '<div id="model_id_div">';
        $rs .= '<select name="model_id" id="model_id" onchange="update_modification_list()">';
        $rs .= '<option value="0">'.Multilanguage::_('L_CHOOSE_MODEL').'</option>';
        if ( is_array($model_structure['childs'][$mark_id]) ) {
            foreach ( $model_structure['childs'][$mark_id] as $model_id ) {
                if ( $current_model_id == $model_id ) {
            		$selected = " selected ";
            	} else {
        	    	$selected = "";
        	    }
                $rs .= '<option value="'.$model_id.'" '.$selected.'>'.$model_structure['model'][$model_id]['name'].'</option>';
                //$rs .= '<option value="'.$model_id.'" '.$selected.'>'.str_repeat(' _ ', $level+1).$model_id.'</option>';
                //$rs .= $this->get_model_option_items( $model_structure, $current_model_id );
            }
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }
    
    /**
     * Get flat modification select box
     * @param int $model_id model ID
     * @param int $current_modification_id selected modification_id
     * @return string
     */
    function get_flat_modification_select_box ( $model_id, $current_modification_id ) {
        $modification_structure = $this->load_modification_structure();
        $rs = '';
        $rs .= '<div id="modification_id_div">';
        $rs .= '<select name="modification_id" id="modification_id">';
        $rs .= '<option value="0">'.Multilanguage::_('L_CHOOSE_MODIFICATION').'</option>';
        if ( is_array($modification_structure['childs'][$model_id]) ) {
            foreach ( $modification_structure['childs'][$model_id] as $modification_id ) {
                if ( $current_modification_id == $modification_id ) {
            		$selected = " selected ";
            	} else {
        	    	$selected = "";
        	    }
                $rs .= '<option value="'.$modification_id.'" '.$selected.'>'.$modification_structure['modification'][$modification_id]['name'].'</option>';
                //$rs .= '<option value="'.$model_id.'" '.$selected.'>'.str_repeat(' _ ', $level+1).$model_id.'</option>';
                //$rs .= $this->get_model_option_items( $model_structure, $current_model_id );
            }
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }
    
    
    /**
     * Get mark select box
     * @param int $current_mark_id mark ID
     * @return string
     */
    function getModelSelectBox ( $current_mark_id ) {
    	//echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        $mark_structure = $this->load_mark_structure();
        $model_structure = $this->load_model_structure();
        //echo '<pre>';
        //print_r($model_structure);
        $level = 1;
        $rs = '';
        $rs .= '<div id="model_id_div">';
        $rs .= '<select name="model_id">';
        $rs .= '<option value="0">..</option>';
        foreach ( $category_structure['childs'][0] as $item_id => $categoryID ) {
            //echo $categoryID.'<br>';
            //echo 'items = '.$items.'<br>';
            if ( $current_category_id == $categoryID ) {
        		$selected = " selected ";
        	} else {
        		$selected = "";
        	}
            
            $rs .= '<option disabled>'.str_repeat(' . ', $level).$category_structure['catalog'][$categoryID]['name'].'</option>';
            $rs .= $this->get_mark_and_model_option_items($categoryID, $mark_structure, $level, $current_mark_id, $model_structure);
            $rs .= $this->getChildNodes($categoryID, $category_structure, $level + 1, $current_category_id);
        }
        $rs .= '</select>';
        $rs .= '</div>';
        return $rs;
    }
    
    
    /**
     * Get mark option items
     * @param int $categoryID category ID
     * @param array $mark_structure mark structure
     * @param int $level
     * @param int $current_mark_id selected mark_id
     * @return string
     */
    function get_mark_option_items( $categoryID, $mark_structure, $level, $current_mark_id ) {
        if ( is_array($mark_structure['childs'][$categoryID]) ) {
            foreach ( $mark_structure['childs'][$categoryID] as $mark_id ) {
                if ( $current_mark_id == $mark_id ) {
            		$selected = " selected ";
            	} else {
        	    	$selected = "";
        	    }
                $rs .= '<option value="'.$mark_id.'" '.$selected.'>'.str_repeat(' _ ', $level+1).$mark_structure['mark'][$mark_id]['name'].'</option>';
            }
        }
        return $rs;
    }
    
    /**
     * Get mark and model option items
     * @param int $categoryID category ID
     * @param array $mark_structure mark structure
     * @param int $level
     * @param int $current_model_id selected model_id
     * @param array $model_structure
     * @return string
     */
    function get_mark_and_model_option_items( $categoryID, $mark_structure, $level, $current_model_id, $model_structure ) {
        if ( is_array($mark_structure['childs'][$categoryID]) ) {
            foreach ( $mark_structure['childs'][$categoryID] as $mark_id ) {
                if ( $current_mark_id == $mark_id ) {
            		$selected = " selected ";
            	} else {
        	    	$selected = "";
        	    }
                $rs .= '<option disabled>'.str_repeat(' _ ', $level+1).$mark_structure['mark'][$mark_id]['name'].'</option>';
                $rs .= $this->get_model_option_items( $model_structure, $current_model_id, $mark_id, $level );
            }
        }
        return $rs;
    }
    
    /**
     * Get model option items
     * @param array $model_structure model structure
     * @param int $current_model_id current model id
     * @param int $mark_id
     * @param int $level level
     * @return string
     */
    function get_model_option_items( $model_structure, $current_model_id, $mark_id, $level ) {
        if ( is_array($model_structure['childs'][$mark_id]) ) {
            foreach ( $model_structure['childs'][$mark_id] as $model_id ) {
                if ( $current_model_id == $model_id ) {
            		$selected = " selected ";
            	} else {
        	    	$selected = "";
        	    }
                $rs .= '<option value="'.$model_id.'" '.$selected.'>'.str_repeat(' * ', $level+2).$model_structure['model'][$model_id]['name'].'</option>';
                //$rs .= '<option value="'.$model_id.'" '.$selected.'>'.str_repeat(' _ ', $level+1).$model_id.'</option>';
                //$rs .= $this->get_model_option_items( $model_structure, $current_model_id );
            }
        }
        return $rs;
    }
    
    function getCategoryTreeModern ( $current_category_id ) {
    	global $smarty;
    	$ret=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/system/template/structure_grid.tpl');
    	return $ret;
    }
    
   
    
    /**
     * Get category tree
     * @param int $current_category_id category ID
     * @return string
     */
    function getCategoryTree ( $current_category_id ) {
    	//echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        $level = 0;
        $rs = '';
        $rs .= '<form method="post">';
        $rs .= '<table border="0"  class="table table-hover">';
        $rs .= '<tr>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '<td class="row_title"><input type="submit" value="'.Multilanguage::_('RESORT_ITEMS','system').'" name="submit" class="btn btn-info"/></td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td class="row_title">'.Multilanguage::_('L_TEXT_TITLE').'</td>';
        $rs .= '<td class="row_title">'.Multilanguage::_('URL_NAME','system').'</td>';
        $rs .= '<td class="row_title">'.Multilanguage::_('SORT_ORDER','system').'</td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '</tr>';
        if ( count($category_structure) > 0 ) {
            foreach ( $category_structure['childs'][0] as $item_id => $catalog_id ) {
                //echo $catalog_id.'<br>';
                $rs .= $this->get_row($catalog_id, $category_structure, $level, 'row1');
                $rs .= $this->getChildNodesRow($catalog_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        $rs .= '<tr>';
        $rs .= '<input type="hidden" name="action" value="structure" />';
        $rs .= '<input type="hidden" name="do" value="reorder_topics" />';
        $rs .= '<td class="row_title"></td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '<td class="row_title"><input type="submit" value="'.Multilanguage::_('RESORT_ITEMS','system').'" name="submit" class="btn btn-info"/></td>';
        $rs .= '<td class="row_title"></td>';
        
        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '</form>';
        return $rs;
    }
    
    /**
     * Get category tree control
     * @param int $current_category_id category ID
     * @param int $user_id
     * @param boolean $control
     * @param array $params
     * @return string
     */
    function get_category_tree_control ( $current_category_id, $user_id, $control = false, $params = array(), $search_params=array() ) {
    	$category_structure = $this->loadCategoryStructure();
        $data_structure = $this->load_data_structure( $user_id, $params, $search_params );
        if(is_array($category_structure['catalog']) && count($category_structure['catalog'])>0){
	    	foreach($category_structure['catalog'] as $cat_point){
	        	$ch=0;
	        	$this->getChildsItemsCount($cat_point['id'], $category_structure['childs'], $data_structure['data'][$user_id], $ch);
	        	
	        	$data_structure['data'][$user_id][$cat_point['id']]+=$ch;
	        }
        }
        unset($params['active']);
        unset($params['hot']);
        
        $level = 0;
        $rs = '';
        $rs .= '<table border="0" width="100%" class="table table-hover">';
        if(is_array($category_structure['childs'][0]) && count($category_structure['childs'][0])>0){
	        foreach ( $category_structure['childs'][0] as $item_id => $catalog_id ) {
	            //echo $catalog_id.'<br>';
	            $rs .= $this->get_row_control($catalog_id, $category_structure, $level, 'row1', $user_id, $control, $data_structure, $current_category_id, $params);
	            $rs .= $this->get_child_nodes_row_control($catalog_id, $category_structure, $level + 1, $current_category_id, $user_id, $control, $data_structure, $params);
	        }
        }
        $rs .= '</table>';
        return $rs;
    }
    
	/**
     * Get category tree control for shop
     * @param int $current_category_id category ID
     * @param int $user_id
     * @param boolean $control
     * @param array $params
     * @return string
     */
    function get_category_tree_control_shop ( $current_category_id, $user_id, $control = false, $params = array() ) {
    	//print_r($params);
    	//echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        $data_structure = $this->load_data_structure_shop( $user_id, $params );
        //echo '<pre>';
        //print_r($data_structure);
        //print_r($category_structure);
        
        foreach($category_structure['catalog'] as $cat_point){
        	$ch=0;
        	$this->getChildsItemsCount($cat_point['id'], $category_structure['childs'], $data_structure['data'][$user_id], $ch);
        	
        	$data_structure['data'][$user_id][$cat_point['id']]+=$ch;
        }
        
        
        
        $level = 0;
        $rs = '';
        $rs .= '<table border="0">';
        $rs .= '<tr>';
        $rs .= '<td class="row_title">'.Multilanguage::_('L_TEXT_TITLE').'</td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '</tr>';
        foreach ( $category_structure['childs'][0] as $item_id => $catalog_id ) {
            //echo $catalog_id.'<br>';
            $rs .= $this->get_row_control($catalog_id, $category_structure, $level, 'row1', $user_id, $control, $data_structure, $current_category_id, $params);
            $rs .= $this->get_child_nodes_row_control($catalog_id, $category_structure, $level + 1, $current_category_id, $user_id, $control, $data_structure, $params);
        }
        $rs .= '</table>';
        return $rs;
    }
    
    /**
     * Get category tree control for price
     * @param int $current_category_id category ID
     * @param int $user_id
     * @param boolean $control
     * @param array $params
     * @return string
     */
    function get_category_tree_control_price ( $current_category_id, $user_id, $control = false, $params = array() ) {
    	//print_r($params);
    	//echo '$current_category_id = '.$current_category_id;
    	$category_structure = $this->loadCategoryStructure();
    	$data_structure = $this->load_data_structure_price( $user_id, $params );
    	//echo '<pre>';
    	//print_r($data_structure);
    	//print_r($category_structure);
    
    	foreach($category_structure['catalog'] as $cat_point){
    		$ch=0;
    		$this->getChildsItemsCount($cat_point['id'], $category_structure['childs'], $data_structure['data'][$user_id], $ch);
    		 
    		$data_structure['data'][$user_id][$cat_point['id']]+=$ch;
    	}
    
    
    
    	$level = 0;
    	$rs = '';
    	$rs .= '<table border="0">';
    	$rs .= '<tr>';
    	$rs .= '<td class="row_title">'.Multilanguage::_('L_TEXT_TITLE').'</td>';
    	$rs .= '<td class="row_title"></td>';
    	$rs .= '</tr>';
    	foreach ( $category_structure['childs'][0] as $item_id => $catalog_id ) {
    		//echo $catalog_id.'<br>';
    		$rs .= $this->get_row_control($catalog_id, $category_structure, $level, 'row1', $user_id, $control, $data_structure, $current_category_id, $params);
    		$rs .= $this->get_child_nodes_row_control($catalog_id, $category_structure, $level + 1, $current_category_id, $user_id, $control, $data_structure, $params);
    	}
    	$rs .= '</table>';
    	return $rs;
    }
    
    
    //function getOwnItemsCount($id,$data_structure){
    //	return $data_structure['data'][$user_id][$id];
    //}
    
	function getChildsItemsCount($id, $category_structure_childs, $data_structure,&$ret){
		//echo '1Call with id='.$id.' <br />';
		if(isset($category_structure_childs[$id]) && count($category_structure_childs[$id])>0){
			foreach($category_structure_childs[$id] as $v){
				//echo '2$v='.$v.' <br />';
				//echo '2a$count='.$data_structure[$v].' <br />';
				if(isset($data_structure[$v])){
					$ret+=$data_structure[$v];
				}
				
				$this->getChildsItemsCount($v, $category_structure_childs, $data_structure, $ret);
			}
		}
		//echo '3$ret='.$ret.' <br />';
    	//return $data_structure['data'][$user_id][$id];
    }
    
    
    /**
     * Get row
     * @param int $categoryID
     * @param array $category_structure
     * @param int $level
     * @param string $row_class
     */
    function get_row ( $categoryID, $category_structure, $level, $row_class ) {
        $rs .= '<tr>';
        $rs .= '<td class="'.$row_class.'">'.str_repeat('&nbsp;.&nbsp;', $level).$category_structure['catalog'][$categoryID]['name'].'</td>';
        if ( $category_structure['catalog'][$categoryID]['url'] == '' ) {
            $rs .= '<td class="'.$row_class.'">'.'topic'.$categoryID.'.html</td>';
        } else {
            $rs .= '<td class="'.$row_class.'">'.$category_structure['catalog'][$categoryID]['url'].'</td>';
        }
        $edit_icon = ' <a href="?action=structure&do=edit&id='.$categoryID.'" class="btn btn-info"><i class="icon-white icon-pencil"></i></a> ';
        $delete_icon = ' <a href="?action=structure&do=delete&id='.$categoryID.'" onclick="if ( confirm(\''.Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE').'\') ) {return true;} else {return false;}" class="btn btn-danger"><i class="icon-white icon-remove"></i></a> ';
            
        //$rs .= '<td class="'.$row_class.'">'.$this->get_operation_type_name_by_id($category_structure['catalog'][$categoryID]['operation_type_id']).'</td>';
        $rs .= '<td class="'.$row_class.'"><input type="text" size="4" name="order['.$categoryID.']" value="'.$category_structure['catalog'][$categoryID]['order'].'"/></td>';
        $rs .= '<td class="'.$row_class.'">'.$edit_icon.$delete_icon.'</td>';
        $rs .= '</tr>';
        
        return $rs;
    }
    
    /**
     * Get row control
     * @param int $categoryID
     * @param array $category_structure
     * @param int $level
     * @param string $row_class
     * @param int $user_id
     * @param boolean $control
     * @param array $data_structure
     * @param int $current_category_id
     * @param array $params
     * @return string
     */
    function get_row_control ( $categoryID, $category_structure, $level, $row_class, $user_id, $control = false, $data_structure, $current_category_id, $params = array() ) {
        //echo '<pre>';
        //print_r($params);
        //echo '</pre>';
		$rs='';
        
    	if(((int)$this->getConfigValue('hide_empty_catalog')!=0) AND ((int)$data_structure['data'][$user_id][$categoryID]==0)) {
    		return '';
    	}
        
    	
        
    	if ( count($params) > 0 ) {
            $add_url = '&'.implode('&',$params);
        }
        
        //echo "add_url = ".$add_url;
        $rs .= '<tr>';
        if ( $categoryID == $current_category_id ) {
            $row_class = 'active';
        }
        $subclass='';
        if($category_structure['catalog'][$categoryID]['parent_id']==0){
        	$subclass='maincat';
        }
        $rs .= '<td class="'.$row_class.' '.$subclass.'"><a href="?topic_id='.$categoryID.''.$add_url.'">'.str_repeat('&nbsp;.&nbsp;', $level).$category_structure['catalog'][$categoryID]['name'].'</a> ('.(int)$data_structure['data'][$user_id][$categoryID].')</td>';

        if ( $control ) {
            $edit_icon = '<a href="?action=structure&do=edit&id='.$categoryID.'"><img src="'.SITEBILL_MAIN_URL.'/img/edit.gif" border="0" width="16" height="16" alt="редактировать" title="редактировать"></a>';
            $delete_icon = '<a href="?action=structure&do=delete&id='.$categoryID.'" onclick="if ( confirm(\''.Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE').'\') ) {return true;} else {return false;}"><img src="'.SITEBILL_MAIN_URL.'/img/delete.gif" border="0" width="16" height="16" alt="удалить" title="удалить"></a>';
        }
            
        //$rs .= '<td class="'.$row_class.'">'.$this->get_operation_type_name_by_id($category_structure['catalog'][$categoryID]['operation_type_id']).'</td>';
        if ( $control ) {
            $rs .= '<td class="'.$row_class.'">'.$edit_icon.$delete_icon.'</td>';
        }
        $rs .= '</tr>';
    	
        
        
        return $rs;
    }
    
    
    /**
     * Get child nodes
     * @param $categoryID
     * @param $category_structure
     * @param $level
     * @param $current_category_id
     */
    function getChildNodesRow($categoryID, $category_structure, $level, $current_category_id) {
    	if ( !is_array($category_structure['childs'][$categoryID]) ) {
    		return '';
    	}
        foreach ( $category_structure['childs'][$categoryID] as $child_id ) {
        	if ( $current_category_id == $child_id ) {
        		$selected = " selected ";
        	} else {
        		$selected = "";
        	}
            $this->j++;
        	if ( ceil($this->j/2) > floor($this->j/2)  ) {
                $row_class = "row1";
            } else {
                $this->j = 0;
                $row_class = "row2";
            }
        	
            //print_r($category_structure['catalog'][$child_id]);
            $rs .= '<tr>';
            $rs .= '<td class="'.$row_class.'">'.str_repeat('&nbsp;.&nbsp;', $level).$category_structure['catalog'][$child_id]['name'].'</td>';
            
            if ( $category_structure['catalog'][$child_id]['url'] == '' ) {
                $rs .= '<td class="'.$row_class.'">'.'topic'.$category_structure['catalog'][$child_id]['id'].'.html</td>';
            } else {
                $rs .= '<td class="'.$row_class.'">'.$category_structure['catalog'][$child_id]['url'].'</td>';
            }
            
            
            $edit_icon = ' <a href="?action=structure&do=edit&id='.$child_id.'" class="btn btn-info"><i class="icon-white icon-pencil"></i></a> ';
            $delete_icon = ' <a href="?action=structure&do=delete&id='.$child_id.'" onclick="if ( confirm(\''.Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE').'\') ) {return true;} else {return false;}"  class="btn btn-danger"><i class="icon-white icon-remove"></i></a> ';
            
            //$rs .= '<td class="'.$row_class.'">'.$this->get_operation_type_name_by_id($category_structure['catalog'][$child_id]['operation_type_id']).'</td>';
            $rs .= '<td class="'.$row_class.'"><input type="text" size="5" name="order['.$child_id.']" value="'.$category_structure['catalog'][$child_id]['order'].'"/></td>';
            $rs .= '<td class="'.$row_class.'">'.$edit_icon.$delete_icon.'</td>';
            $rs .= '</tr>';
            //$rs .= '<option value="'.$child_id.'" '.$selected.'>'.str_repeat(' . ', $level).$category_structure['catalog'][$child_id]['name'].'</option>';
            //print_r($category_structure['childs'][$child_id]);
            if ( count($category_structure['childs'][$child_id]) > 0 ) {
                $rs .= $this->getChildNodesRow($child_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        return $rs;
    }
    
    /**
     * Get child nodes control
     * @param $categoryID
     * @param $category_structure
     * @param $level
     * @param $current_category_id
     */
    function get_child_nodes_row_control($categoryID, $category_structure, $level, $current_category_id, $user_id, $control = false, $data_structure, $params = array()) {
		$rs='';
    	if ( !isset($category_structure['childs'][$categoryID]) || !is_array($category_structure['childs'][$categoryID]) ) {
    		return '';
    	}
    	
    	
        
    	if ( count($params) > 0 ) {
            $add_url = '&'.implode('&',$params);
        }
    	
        foreach ( $category_structure['childs'][$categoryID] as $child_id ) {
        	
        	
	        if((0!=$this->getConfigValue('hide_empty_catalog')) AND (0==$data_structure['data'][$user_id][$child_id])) {
	    		$rs.='';
	    	}else{
	    		
		    	if ( $current_category_id == $child_id ) {
	        		$selected = " selected ";
	        	} else {
	        		$selected = "";
	        	}
	            $this->j++;
	        	if ( ceil($this->j/2) > floor($this->j/2)  ) {
	                $row_class = "row1";
	            } else {
	                $this->j = 0;
	                $row_class = "row2";
	            }
	        	
	            //print_r($category_structure['catalog'][$child_id]);
	            //print_r($data_structure['data'][$user_id]);
	            //echo "category_id = $child_id, count = ".$data_structure['data'][$user_id][$child_id].'<br>';
	            $rs .= '<tr>';
	            
	            if ( $child_id == $current_category_id ) {
	                $row_class = 'active';
	            }
	            $rs .= '<td class="'.$row_class.'"><a href="?topic_id='.$child_id.''.$add_url.'">'.str_repeat('&nbsp;.&nbsp;', $level).$category_structure['catalog'][$child_id]['name'].'</a> ('.(int)$data_structure['data'][$user_id][$child_id].')'.'</td>';
	            if ( $control ) {
	                $edit_icon = '<a href="?action=structure&do=edit&id='.$child_id.'"><img src="'.SITEBILL_MAIN_URL.'/apps/admin/admin/template/img/edit.png" border="0"  alt="редактировать" title="редактировать"></a>';
	                $delete_icon = '<a href="?action=structure&do=delete&id='.$child_id.'" onclick="if ( confirm(\''.Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE').'\') ) {return true;} else {return false;}"><img src="'.SITEBILL_MAIN_URL.'/apps/admin/admin/template/img/delete.png" border="0" width="16" height="16" alt="удалить" title="удалить"></a>';
	            }
	            
	            //$rs .= '<td class="'.$row_class.'">'.$this->get_operation_type_name_by_id($category_structure['catalog'][$child_id]['operation_type_id']).'</td>';
	            if ( $control ) {
	                $rs .= '<td class="'.$row_class.'">'.$edit_icon.$delete_icon.'</td>';
	            }
	            
	            $rs .= '</tr>';
	            //$rs .= '<option value="'.$child_id.'" '.$selected.'>'.str_repeat(' . ', $level).$category_structure['catalog'][$child_id]['name'].'</option>';
	            //print_r($category_structure['childs'][$child_id]);
	            if ( isset($category_structure['childs'][$child_id]) && count($category_structure['childs'][$child_id]) > 0 ) {
	                $rs .= $this->get_child_nodes_row_control($child_id, $category_structure, $level + 1, $current_category_id, $user_id, $control, $data_structure, $params);
	            }
	    	}
	    	
	    	
        	
        }
        return $rs;
    }
    
    /**
     * Load operation type list
     * @param void
     * @return array
     */
    function load_operation_type_list () {
        $query = "SELECT * FROM ".DB_PREFIX."_operation_type";
        //echo $query;
        $ret=array();
        $this->db->exec($query);
        while ( $this->db->fetch_assoc() ) {
            $ret[$this->db->row['operation_type_id']]= $this->db->row;
        }
        return $ret;
    }

    /**
     * Get child nodes
     * @param $categoryID
     * @param $category_structure
     * @param $level
     * @param $current_category_id
     */
    function getChildNodes($categoryID, $category_structure, $level, $current_category_id) {
    	$rs='';
    	if ( !isset($category_structure['childs'][$categoryID]) || !is_array($category_structure['childs'][$categoryID]) ) {
    		return '';
    	}
    	$multiple=false;
    	if(is_array($current_category_id)){
    		$multiple=true;
    	}
        foreach ( $category_structure['childs'][$categoryID] as $child_id ) {
        	if($multiple){
	        	if ( in_array($child_id, $current_category_id) ) {
	        		$selected = " selected ";
	        	} else {
	        		$selected = "";
	        	}
        	}else{
	        	if ( $current_category_id == $child_id ) {
	        		$selected = " selected ";
	        	} else {
	        		$selected = "";
	        	}
        	}
        	
            $rs .= '<option value="'.$child_id.'" '.$selected.'>'.str_repeat(' . ', $level).$category_structure['catalog'][$child_id]['name'].'</option>';
            
            if ( isset($category_structure['childs'][$child_id]) ) {
            	if ( count($category_structure['childs'][$child_id]) > 0 ) {
            		$rs .= $this->getChildNodes($child_id, $category_structure, $level + 1, $current_category_id);
            	}
            }
        }
        return $rs;
        
        
        ////////////////////////////////////////////
    	if ( !is_array($category_structure['childs'][$categoryID]) ) {
    		return '';
    	}
        foreach ( $category_structure['childs'][$categoryID] as $child_id ) {
        	if ( $current_category_id == $child_id ) {
        		$selected = " selected ";
        	} else {
        		$selected = "";
        	}
            //print_r($category_structure['catalog'][$child_id]);
            $rs .= '<option value="'.$child_id.'" '.$selected.'>'.str_repeat(' . ', $level).$category_structure['catalog'][$child_id]['name'].'</option>';
            //print_r($category_structure['childs'][$child_id]);
            if ( count($category_structure['childs'][$child_id]) > 0 ) {
                $rs .= $this->getChildNodes($child_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        return $rs;
    }
    
	function getShopChildNodes($categoryID, $category_structure, $level, $current_category_id) {
    	if ( !is_array($category_structure['childs'][$categoryID]) ) {
    		return '';
    	}
        foreach ( $category_structure['childs'][$categoryID] as $child_id ) {
        	if ( $current_category_id == $child_id ) {
        		$selected = " selected ";
        	} else {
        		$selected = "";
        	}
            $rs .= '<option value="'.$child_id.'" '.$selected.'>'.str_repeat(' . ', $level).$category_structure['catalog'][$child_id]['category_name'].'</option>';
            
            if ( count($category_structure['childs'][$child_id]) > 0 ) {
                $rs .= $this->getShopChildNodes($child_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        return $rs;
        
        
        ////////////////////////////////////////////
    	if ( !is_array($category_structure['childs'][$categoryID]) ) {
    		return '';
    	}
        foreach ( $category_structure['childs'][$categoryID] as $child_id ) {
        	if ( $current_category_id == $child_id ) {
        		$selected = " selected ";
        	} else {
        		$selected = "";
        	}
            //print_r($category_structure['catalog'][$child_id]);
            $rs .= '<option value="'.$child_id.'" '.$selected.'>'.str_repeat(' . ', $level).$category_structure['catalog'][$child_id]['category_name'].'</option>';
            //print_r($category_structure['childs'][$child_id]);
            if ( count($category_structure['childs'][$child_id]) > 0 ) {
                $rs .= $this->getShopChildNodes($child_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        return $rs;
    }
    
    
     
    /**
     * Get service type select box
     * @param int $current_servicetype_id service type ID
     * @return string
     * @author Kris
     */
    function getServiceTypesTree_selectBox ($select_name, $current_servicetype_id ) {
    	//echo '$current_category_id = '.$current_category_id;
    	$service_type_array = $this->getServiceTypeTree_array(0, 0);
    	$level = 1;
    	$rs = '';
    	$rs .= '<div id="parent_id_div">';
    	$rs .= '<select name="'.$select_name.'">';
    	$rs .= '<option value="0">..</option>';
    	
    	$rs .= $this->getServiceTypesTree_optionItems($service_type_array, $current_servicetype_id);
    	$rs .= '</select>';
    	$rs .= '</div>';
    	return $rs;
    }
    
    /**
     * Get service type 'option' items for select box
     * @param int $current_servicetype_id - current service type ID
     * @param array $array - items
     * @return string
     * @author Kris
     */
    function getServiceTypesTree_optionItems ($array,  $current_servicetype_id )
    {
    	if (count($array) == 0)
    		return '';
    	foreach ($array as $value)
    	{
    		
	    	$level_lines = '';
	    	for ($i = 0; $i< $value['level']; $i++ )
	    		$level_lines.= ' - ';
	    	if ( $current_servicetype_id == $value['id'] ) {
	    		$selected = " selected ";
	    	} else {
	    		$selected = "";
	    	}
	    	$rs .= '<option value="'.$value['id'].'" '.$selected.'>'.str_repeat(' . ', $value['level']). $value['name'].'</option>';
    		$rs .= $this -> getServiceTypesTree_optionItems($value['child'], $current_servicetype_id);
    
    	}
    return $rs;
    }
    
    /**
     * Get service type tree in array with field 
     * 'level', which specify the nesting level, and 
     * 'child' containing child array with the same structure as parent
     * @param int $current_servicetype_id service type ID
     * @return array
     * @author Kris
     */
    function getServiceTypeTree_array($level, $parent_id)
    {
    	global $_SESSION;
    	global $__db_prefix;
    
    	$query = "select st1.*
    	from ".$__db_prefix."_service_type as st1
    	where st1.parent_id = ".$parent_id."";
    	$this->db->exec($query);
    	$arr = array();
    	$j = 0;
    	while ( $this->db->fetch_assoc() ) {
    		$arr[$j]['name'] = $this->db->row['name'];
    		$arr[$j]['id'] = $this->db->row['service_type_id'];
    		$arr[$j]['level'] =$level;
    		$j++;
    	}
    
    	foreach ($arr as $key => $value)
    	{
    		$arr[$key]['child'] = $this->getServiceTypeTree_array($arr[$key]['level'] + 1, $arr[$key]['id']);
    	}
    	return $arr;
    }
    
    /**
     * Grid
     * @param void
     * @return string
     */
    function grid () {
    	//$rs .= $this->getCategoryTree(0);
    	$rs .= $this->getCategoryTreeModern(0);
    	//$rs .= $this->getCategoryTreeModern(0);
    	return $rs;
    	if(NULL!=$this->getRequestValue('structure_manager_grid_type')){
    		$_SESSION['structure_manager_grid_type']=$this->getRequestValue('structure_manager_grid_type');
    	}else{
    		//$_SESSION['structure_manager_grid_type']='';
    	}
    	$this->upgrade();
    	if(isset($_SESSION['structure_manager_grid_type']) && $_SESSION['structure_manager_grid_type']=='new'){
    		
    		$rs .= $this->getCategoryTreeModern(0);
    	}else{
    		$rs .= $this->getCategoryTree(0);
    	}
    	return $rs;
    }
	
	function findCurrent(&$structure,$active){
		foreach($structure['childs'] as $k=>$v){
			foreach($v as $vv){
				if($vv==$active){
					$structure['catalog'][$vv]['current']=1;
					if($k!=0){
						$structure['catalog'][$k]['current']=1;
						$this->findCurrent($structure,$k);
						return;
					}
				}
			}
		}
	}
	
	private function getRealtyTypeSelectbox($realty_type,$topic_id){
		$re_types=array('0'=>'Игнорировать','1'=>'Жилая','4'=>'Коммерческая','6'=>'Нежилая');
		$ret='';
		$ret.='<select name="data['.$topic_id.'][obj_type_id]">';
		foreach($re_types as $k=>$v){
			if($realty_type==$k){
				$ret.='<option value="'.$k.'" selected="selected">'.$v.'</option>';
			}else{
				$ret.='<option value="'.$k.'">'.$v.'</option>';
			}
		}
		$ret.='</select>';
		return $ret;
	}
	
	private function getOperationTypeSelectbox($operation_type,$topic_id){
		$op_types=array('0'=>'Игнорировать','1'=>'Продажа','2'=>'Аренда');
		$ret='';
		$ret.='<select name="data['.$topic_id.'][operation_type]">';
		foreach($op_types as $k=>$v){
			if($operation_type==$k){
				$ret.='<option value="'.$k.'" selected="selected">'.$v.'</option>';
			}else{
				$ret.='<option value="'.$k.'">'.$v.'</option>';
			}
		}
		$ret.='</select>';
		return $ret;
	}
	
	function getCategoryTreeAssoc () {
    	//echo '$current_category_id = '.$current_category_id;
        $category_structure = $this->loadCategoryStructure();
        //echo '<pre>';
        //print_r($category_structure);
        $level = 0;
        $rs = '';
        $rs .= '<form method="post">';
        $rs .= '<table border="0">';
        $rs .= '<tr>';
        $rs .= '<td class="row_title" colspan="4"><input type="submit" value="'.Multilanguage::_('L_TEXT_SAVE').'" name="submit" /></td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td class="row_title">'.Multilanguage::_('L_TEXT_TITLE').'</td>';
        $rs .= '<td class="row_title">'.Multilanguage::_('OPERATION_TYPE','system').'</td>';
        $rs .= '<td class="row_title">'.Multilanguage::_('ESTATE_TYPE','system').'</td>';
        $rs .= '<td class="row_title"></td>';
        $rs .= '</tr>';
        if ( count($category_structure) > 0 ) {
            foreach ( $category_structure['childs'][0] as $item_id => $catalog_id ) {
                //echo $catalog_id.'<br>';
                $rs .= $this->getRowAssoc($catalog_id, $category_structure, $level, 'row1');
                $rs .= $this->getChildNodesRowAssoc($catalog_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        $rs .= '<tr>';
        $rs .= '<input type="hidden" name="action" value="structure" />';
        $rs .= '<input type="hidden" name="do" value="associations" />';
        $rs .= '<td class="row_title" colspan="4"><input type="submit" value="'.Multilanguage::_('L_TEXT_SAVE').'" name="submit" /></td>';
        
        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '</form>';
        return $rs;
    }
	
	function getRowAssoc ( $categoryID, $category_structure, $level, $row_class ) {
        $rs .= '<tr>';
        $rs .= '<td class="'.$row_class.'">'.str_repeat('&nbsp;.&nbsp;', $level).$category_structure['catalog'][$categoryID]['name'].'</td>';
        
        $rs.='<td class="'.$row_class.'">'.$this->getOperationTypeSelectbox($category_structure['catalog'][$categoryID]['operation_type_id'],$categoryID).'</td>';		
        $rs.='<td class="'.$row_class.'">'.$this->getRealtyTypeSelectbox($category_structure['catalog'][$categoryID]['obj_type_id'],$categoryID).'</td>';
		
        $rs .= '<td><input type="checkbox" name="data['.$categoryID.'][legacy]" /> '.Multilanguage::_('INHERIT','system').'</td>';
        $rs .= '</tr>';
        
        return $rs;
    }
	
	function getChildNodesRowAssoc($categoryID, $category_structure, $level, $current_category_id) {
    	if ( !is_array($category_structure['childs'][$categoryID]) ) {
    		return '';
    	}
        foreach ( $category_structure['childs'][$categoryID] as $child_id ) {
        	if ( $current_category_id == $child_id ) {
        		$selected = " selected ";
        	} else {
        		$selected = "";
        	}
            $this->j++;
        	if ( ceil($this->j/2) > floor($this->j/2)  ) {
                $row_class = "row1";
            } else {
                $this->j = 0;
                $row_class = "row2";
            }
        	
            $rs .= '<tr>';
            $rs .= '<td class="'.$row_class.'">'.str_repeat('&nbsp;.&nbsp;', $level).$category_structure['catalog'][$child_id]['name'].'</td>';
            
            $rs.='<td class="'.$row_class.'">'.$this->getOperationTypeSelectbox($category_structure['catalog'][$child_id]['operation_type_id'],$child_id).'</td>';		
			$rs.='<td class="'.$row_class.'">'.$this->getRealtyTypeSelectbox($category_structure['catalog'][$child_id]['obj_type_id'],$child_id).'</td>';
			$rs .= '<td><input type="checkbox" name="data['.$child_id.'][legacy]" /> '.Multilanguage::_('INHERIT','system').'</td>';
			
            $rs .= '</tr>';
            if ( count($category_structure['childs'][$child_id]) > 0 ) {
                $rs .= $this->getChildNodesRowAssoc($child_id, $category_structure, $level + 1, $current_category_id);
            }
        }
        return $rs;
    }
    
    public static function has_child($id){
    	$DBC=DBC::getInstance();
    	$query='SELECT COUNT(id) AS cnt FROM '.DB_PREFIX.'_topic WHERE parent_id=?';
    	$stmt=$DBC->query($query, array($id));
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		return $ar['cnt'] > 0 ? true : false;
    	}
    	return false;
    }
    
    protected function getStrModel(){
    	$form_data['topic']['id']['name'] = 'id';
    	$form_data['topic']['id']['title'] = Multilanguage::_('L_ID');
    	$form_data['topic']['id']['value'] = 0;
    	$form_data['topic']['id']['length'] = 40;
    	$form_data['topic']['id']['type'] = 'primary_key';
    	$form_data['topic']['id']['required'] = 'off';
    	$form_data['topic']['id']['unique'] = 'off';
    	
    	$form_data['topic']['parent_id']['name'] = 'parent_id';
    	$form_data['topic']['parent_id']['title'] = 'Категория';
    	$form_data['topic']['parent_id']['value_string'] = '';
    	$form_data['topic']['parent_id']['value'] = 0;
    	$form_data['topic']['parent_id']['length'] = 40;
    	$form_data['topic']['parent_id']['type'] = 'select_box_structure';
    	$form_data['topic']['parent_id']['required'] = 'off';
    	$form_data['topic']['parent_id']['unique'] = 'off';
    	
    	$form_data['topic']['order']['name'] = 'order';
    	$form_data['topic']['order']['title'] = 'Порядок';
    	$form_data['topic']['order']['value'] = '';
    	$form_data['topic']['order']['length'] = 40;
    	$form_data['topic']['order']['type'] = 'safe_string';
    	$form_data['topic']['order']['required'] = 'off';
    	$form_data['topic']['order']['unique'] = 'off';
    	
    	$form_data['topic']['name']['name'] = 'name';
    	$form_data['topic']['name']['title'] = 'Название';
    	$form_data['topic']['name']['value'] = '';
    	$form_data['topic']['name']['length'] = 40;
    	$form_data['topic']['name']['type'] = 'safe_string';
    	$form_data['topic']['name']['required'] = 'on';
    	$form_data['topic']['name']['unique'] = 'off';
    	
    	$languages = Multilanguage::foreignLanguages();
    	
    	foreach ( $languages as $language_id => $language_title ) {
    		
    		$form_data['topic']['name_'.$language_id]['name'] = 'name_'.$language_id;
    		$form_data['topic']['name_'.$language_id]['title'] = Multilanguage::_('TOPIC_NAME','system').' <b>'.$language_id.'</b>';
    		$form_data['topic']['name_'.$language_id]['value'] = '';
    		$form_data['topic']['name_'.$language_id]['length'] = 40;
    		$form_data['topic']['name_'.$language_id]['type'] = 'safe_string';
    		$form_data['topic']['name_'.$language_id]['required'] = 'off';
    		$form_data['topic']['name_'.$language_id]['unique'] = 'off';
    		
    	}
    	
    	$form_data['topic']['meta_title']['name'] = 'meta_title';
    	$form_data['topic']['meta_title']['title'] = 'meta_title';
    	$form_data['topic']['meta_title']['value'] = '';
    	$form_data['topic']['meta_title']['length'] = 40;
    	$form_data['topic']['meta_title']['type'] = 'safe_string';
    	$form_data['topic']['meta_title']['required'] = 'off';
    	$form_data['topic']['meta_title']['unique'] = 'off';
    	
    	$form_data['topic']['meta_keywords']['name'] = 'meta_keywords';
    	$form_data['topic']['meta_keywords']['title'] = 'meta_keywords';
    	$form_data['topic']['meta_keywords']['value'] = '';
    	$form_data['topic']['meta_keywords']['length'] = 40;
    	$form_data['topic']['meta_keywords']['type'] = 'safe_string';
    	$form_data['topic']['meta_keywords']['required'] = 'off';
    	$form_data['topic']['meta_keywords']['unique'] = 'off';
    	
    	$form_data['topic']['meta_description']['name'] = 'meta_description';
    	$form_data['topic']['meta_description']['title'] = 'meta_description';
    	$form_data['topic']['meta_description']['value'] = '';
    	$form_data['topic']['meta_description']['length'] = 40;
    	$form_data['topic']['meta_description']['type'] = 'safe_string';
    	$form_data['topic']['meta_description']['required'] = 'off';
    	$form_data['topic']['meta_description']['unique'] = 'off';
    	
    	$form_data['topic']['description']['name'] = 'description';
    	$form_data['topic']['description']['title'] = 'Описание';
    	$form_data['topic']['description']['value'] = '';
    	$form_data['topic']['description']['length'] = 40;
    	$form_data['topic']['description']['type'] = 'textarea_editor';
    	$form_data['topic']['description']['required'] = 'off';
    	$form_data['topic']['description']['unique'] = 'off';
    	
    	$form_data['topic']['url']['name'] = 'url';
    	$form_data['topic']['url']['title'] = 'ЧПУ, название раздела латинскими буквами, например, arenda. Без точек и без /';
    	$form_data['topic']['url']['value'] = '';
    	$form_data['topic']['url']['length'] = 40;
    	$form_data['topic']['url']['type'] = 'safe_string';
    	$form_data['topic']['url']['required'] = 'on';
    	$form_data['topic']['url']['unique'] = 'off';
    	return $form_data;
    }
    
}
?>
