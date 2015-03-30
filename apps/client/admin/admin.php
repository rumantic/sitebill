<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Client admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class client_admin extends Object_Manager {
	public $client_topic_id = 123123;
    /**
     * Constructor
     */
    function __construct( $realty_type = false ) {
        $this->SiteBill();
        Multilanguage::appendAppDictionary('client');
        
        $this->table_name = 'client';
        $this->action = 'client';
        $this->primary_key = 'client_id';
        $this->app_title = Multilanguage::_('APP_TITLE','client');
        
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
        $config_admin = new config_admin();

        if ( !$config_admin->check_config_item('apps.client.enable') ) {
        	$config_admin->addParamToConfig('apps.client.enable','0','Включить приложение');
        }
        
        if ( !$config_admin->check_config_item('apps.client.namespace') ) {
        	$config_admin->addParamToConfig('apps.client.namespace','client','Пространство имен приложения');
        }
        
        if ( !$config_admin->check_config_item('apps.client.folder_title') ) {
        	$config_admin->addParamToConfig('apps.client.folder_title','Заявки','Заголовок приложения в хлебных крошках');
        }
        
        if ( !$config_admin->check_config_item('apps.client.allow-redirect_url_for_orders') ) {
        	$config_admin->addParamToConfig('apps.client.allow-redirect_url_for_orders','','Разрешить редирект на другую страницу при удачном завершении подачи заявки');
        }
        
        //$this->install();
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/client/admin/client_model.php');
        $Object=new Client_Model();
        $this->model = $Object;
        
        $form_data = array();
        
        if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php') ){
        	require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
        	$ATH=new Admin_Table_Helper();
        	$form_data=$ATH->load_model($this->table_name, false);
        	if(empty($form_data)){
        		$form_data = array();
        		$form_data = $Object->get_model($ajax);
        		//$form_data = $this->_get_big_city_kvartira_model2($ajax);
        		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';
        		require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php';
        		$TA=new table_admin();
        		$TA->create_table_and_columns($form_data, $this->table_name);
        		$form_data = array();
        		$form_data=$ATH->load_model($this->table_name, false);
        	}
        	 
        }else{
        	$form_data = $Object->get_model($ajax);
        }
        
        
        $this->data_model=$form_data;
      }
      
	protected function _installAction(){
		$this->install();
	}
    
    function set_client_topic_id ( $topic_id ) {
    	$this->client_topic_id = $topic_id;
    	$this->template->assign('client_topic_id', $this->client_topic_id);
    }
      
    function install () {
    	$query = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_client` (
		  `client_id` int(11) NOT NULL AUTO_INCREMENT,
		  `fio` varchar(255) NOT NULL,
		  `phone` varchar(255) NOT NULL,
		  `email` varchar(150) NOT NULL,
		  `address` text NOT NULL,
    	  `date` INT(10) NOT NULL, 
		  PRIMARY KEY (`client_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=".DB_ENCODING." AUTO_INCREMENT=1 ;";
    	$this->db->exec($query);
    	if ( !$this->db->success ) {
    		echo $this->db->error.'<br>';
    	}
    	$rs = Multilanguage::_('L_APPLICATION_INSTALLED');
    	return $rs;
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
    				$rs = $this->get_form($form_data[$this->table_name], 'edit');
    			} else {
    				$this->edit_data($form_data[$this->table_name]);
    				if ( $this->getError() ) {
    					$form_data['data']=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
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
    			//echo '<pre>';
    			//print_r($form_data[$this->table_name]);
    			//exit();
    			$data_model->forse_auto_add_values($form_data[$this->table_name]);
    			if ( !$this->check_data( $form_data[$this->table_name] ) || (1==$this->getConfigValue('filter_double_data') && !$this->checkUniquety($form_data[$this->table_name]))  ) {
    				$form_data['data']=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
    				$rs = $this->get_form($form_data[$this->table_name], 'new');
    				 
    			} else {
    				$new_record_id=$this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
    				if ( $this->getError() ) {
    					$form_data['data']=$this->removeTemporaryFields($form_data['data'],$remove_this_names);
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
    		case 'view' : {
    			if('add_comment'==$this->getRequestValue('subaction')){
    				$id=$this->addComment();
    				if((int)$id!=0){
    					SiteBill::appendAttachments('comment', (int)$id, $this->getRequestValue('attachments'));
    				}
    			}
    			if ( $this->getRequestValue('language_id') > 0 ) {
    				$form_data[$this->table_name] = $data_model->init_model_data_from_db_language ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name], false, $this->getRequestValue('language_id') );
    			} else {
    				$form_data[$this->table_name] = $data_model->init_model_data_from_db ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name] );
    			}
    			$rs = $this->show($form_data[$this->table_name]);
    			break;
    		}
    		case 'install' : {
    			$this->_installAction();
    		}
    		default : {
    			$rs .= $this->grid($user_id);
    		}
    	}
    	$rs_new = $this->get_app_title_bar();
    	$rs_new .= $rs;
    	return $rs_new;
    }
    
    function add_data($form_data, $language_id = 0){
    	$id=parent::add_data($form_data, $language_id);
    	if( $id && strlen($form_data['imgfile']['value']) > 0 ){
    		$this->update_photo($id);
    	}
    	return $id;
    }
    
    function edit_data ( $form_data, $language_id = 0, $primary_key_value = false ) {
    	$answer=parent::edit_data($form_data, $language_id, $primary_key_value);
    	if($answer!==false && strlen($form_data['imgfile']['value']) > 0){
    		$this->update_photo($this->getRequestValue($this->primary_key));
    	}
    	return $answer;
    }
    
   
    
    private function addComment(){
    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/comment/admin/admin.php';
    	$CA=new comment_admin();
    	return $CA->saveCommentNotAjax(true);
    }
    
    function show($data){
    	
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/client/template/edit.tpl')){
    		$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/client/template/edit.tpl';
    	}else{
    		$tpl_name=SITEBILL_DOCUMENT_ROOT.'/apps/client/admin/template/edit.tpl.html';
    	}
    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/comment/admin/admin.php';
    	$comments=comment_admin::getCommentsWithUser('client', $data[$this->primary_key]['value']);
    	
    	$now=time();
    	//echo 
    	foreach($comments as &$c){
    		//$d=$now-strtotime($c['comment_date']);
    		$cd=strtotime($c['comment_date']);
    		$c['comment_date']=date('d-m-Y H:i', $cd);
    		$c['ago']=$this->convertDeltaTime($now-$cd);
    	}
    	$this->template->assert('attachments_block', SiteBill::getAttachmentsBlock());
    	$this->template->assert('attachments', SiteBill::getAttachments('client', $data[$this->primary_key]['value']));
    	$this->template->assert('current_user_id', $this->getAdminUserId());
    	$this->template->assert('client_comments', $comments);
    	$this->template->assert('client_data', $data);
    	return $this->template->fetch($tpl_name);
    }
    
    private function convertDeltaTime($seconds){
    	if($seconds<61){
    		return '1 minute ago';
    	}elseif($seconds<3540){
    		return (string)ceil($seconds/60).' minutes ago';
    	}elseif($seconds<86400){
    		return (string)ceil($seconds/3600).' hours ago';
    		//return date('h',$seconds)/*(string)ceil($seconds/86400)*/.' hours ago';
    	}else{
    		return (string)ceil($seconds/86400).' days ago';
    	}
    	//return (string)ceil($seconds/24*86400).' days ago';
    }
    
    function grid () {
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/grid.php');
    	$common_grid = new Common_Grid($this);
    
    
    	$common_grid->add_grid_item($this->primary_key);
    	$common_grid->add_grid_item('type_id');
    	$common_grid->add_grid_item('date');
    	$common_grid->add_grid_item('fio');
    	$common_grid->add_grid_item('phone');
    	$common_grid->add_grid_item('email');
    	$common_grid->add_grid_item('status_id');
    	$common_grid->add_grid_control('view');
    	$common_grid->add_grid_control('edit');
    	$common_grid->add_grid_control('delete');
    
    	$common_grid->setPagerParams(array('action'=>$this->action,'page'=>$this->getRequestValue('page'),'per_page'=>$this->getConfigValue('common_per_page')));
    
    	//$common_grid->set_grid_query("select * from ".DB_PREFIX."_".$this->table_name." order by date desc");
    	$rs = $common_grid->construct_grid();
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
    function get_form ( $form_data=array(), $do = 'new', $language_id = 0, $button_title = '' ) {
        /*if ( $do == 'edit' ) {
            return $this->edit_case();
        }*/
    
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
    	$rs .= '<form method="post" class="form-horizontal" action="index.php" enctype="multipart/form-data" id="client_form">';
    		
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
    	$el['private'][]=array('html'=>'<input type="hidden" name="topic_id" id="topic_id" value="'.$this->client_topic_id.'">');
    	 
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
    
    
    private function update_photo ( $client_id ) {
    	if ( SITEBILL_MAIN_URL != '' ) {
    		$add_folder = SITEBILL_MAIN_URL.'/';
    	}
    
    	$this->user_image_dir='/img/data/user/';
    	$imgfile_directory=$this->user_image_dir;
    	 
    	$document_root = $_SERVER['DOCUMENT_ROOT'].$add_folder;
    
    	$avial_ext=array('jpg', 'jpeg', 'gif', 'png');
    	if(isset($_FILES['imgfile'])){
    			
    		if(($_FILES['imgfile']['error']!=0)OR($_FILES['imgfile']['size']==0)){
    			//echo 'Не указан или указан не верно файл для загрузки<br>';
    		}else{
    			$fprts=explode('.',$_FILES['imgfile']['name']);
    			if(count($fprts)>1){
    				$ext=$fprts[count($fprts)-1];
    				if(in_array($ext,$avial_ext)){
    					$usrfilename=time().'.'.$ext;
    					//echo $imgfile_directory.$usrfilename;
    					$i = rand(0, 999);
    					$preview_name="img".uniqid().'_'.time()."_".$i.".".$ext;
    					$preview_name_tmp="_tmp".uniqid().'_'.time()."_".$i.".".$ext;
    
    					if(! move_uploaded_file($_FILES['imgfile']['tmp_name'], $document_root.'/'.$imgfile_directory.$preview_name_tmp) ){
    							
    					}else{
    						list($width,$height)=$this->makePreview($document_root.'/'.$imgfile_directory.$preview_name_tmp, $document_root.'/'.$imgfile_directory.$preview_name, 160,160, $ext,1);
    						unlink($document_root.'/'.$imgfile_directory.$preview_name_tmp);
    
    						$query='UPDATE '.DB_PREFIX.'_'.$this->table_name.' SET imgfile="'.$preview_name.'" WHERE client_id='.$client_id;
    						$this->db->exec($query);
    					}
    				}
    					
    			}
    		}
    	}
    }
    
    function ajax () {
    	if ( $this->getRequestValue('action') == 'get_order_form' ) {
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/client/site/site.php';
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/client/admin/client_order.php';
    		
    		$Client_Order=new Client_Order();
    		$model=$this->getRequestValue('model');
    		$options=$this->getRequestValue('options');
    		//print_r($options, true);
    		$this->writeLog(array('apps_name'=>'apps.client', 'method' => __METHOD__, 'message' => 'get_order_form', 'type' => NOTICE));
    		
    		return $Client_Order->get_order_form($model, $options);
    	}elseif($this->getRequestValue('action') == 'save_order_form'){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/client/site/site.php';
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/client/admin/client_order.php';
    		
    		$Client_Order=new Client_Order();
    		$model=$this->getRequestValue('model');
    		$this->writeLog(array('apps_name'=>'apps.client', 'method' => __METHOD__, 'message' => 'save_order_form', 'type' => NOTICE));
    		
    		return $Client_Order->save_order_form($model);
    	}else {
    		//return $this->xls_parser();
    	}
    	return false;
    	
    	
    }
    
}
?>