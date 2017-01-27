<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Columns admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/admin.php');
class columns_admin extends table_admin {
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        $this->table_name='columns';
        $this->app_title='Редактор таблиц';
        $this->action = 'columns';
        $this->primary_key = 'columns_id';
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/columns_model.php');
        $Object=new columns_Model();
		$this->data_model=$Object->get_model();
		$this->data_model[$this->table_name]['primary_key_table']['select_data']=$this->getTablesNames();
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php');
		$this->helper = new Admin_Table_Helper();
		
	}
	
	public function copyColumn($column_id, $newname, $opts=array()){
		
		$model=$this->data_model[$this->table_name];
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$model = $data_model->init_model_data_from_db ( $this->table_name, $this->primary_key, $column_id, $this->data_model[$this->table_name] );
		$model['name']['value']=$newname;
		if(!empty($opts)){
			foreach($opts as $k=>$v){
				if(isset($model[$k])){
					$model[$k]['value']=$v;
				}
			}
		}
		unset($model[$this->primary_key]);
		$new_record_id=$this->add_data($model, 0);
		if(false===$new_record_id){
			return $this->getError();
		}
	}
	
	protected function _add_lang_fieldsAction(){
		if(1!==intval($this->getConfigValue('apps.language.use_langs'))){
			return $this->_defaultAction();
		}
		$table_id=intval($this->getRequestValue('table_id'));
		$columns_id=intval($this->getRequestValue('columns_id'));
	
		$langs=array_values(Multilanguage::availableLanguages());
	
	
		$exising_columns=array();
		$need_ml_columns=array();
		$columns_to_add=array();
	
		$DBC=DBC::getInstance();
		$query='SELECT columns_id, table_id, name FROM '.DB_PREFIX.'_columns WHERE table_id=?';
		$stmt=$DBC->query($query, array($table_id));
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				if($ar['is_ml']==1){
					$need_ml_columns[]=$ar;
				}
				$exising_columns[]=$ar['name'];
			}
		}
	
		if(!empty($need_ml_columns)){
			foreach($need_ml_columns as $nmc){
				foreach($langs as $lng){
					$new_name=$nmc['name'].'_'.$lng;
					if(!isset($exising_columns[$new_name])){
						$columns_to_add[$nmc['columns_id']][]=$new_name;
					}
				}
			}
		}
	
		if(!empty($columns_to_add)){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php';
			$CM=new columns_admin();
			$opts=array(
					'required'=>0,
					'is_ml'=>0
			);
			foreach ($columns_to_add as $columns_id=>$new_names){
				foreach ($new_names as $new_name){
					$CM->copyColumn($columns_id, $new_name, $opts);
				}
			}
				
			$query = 'SELECT name FROM '.DB_PREFIX.'_table WHERE table_id=? LIMIT 1';
			$stmt=$DBC->query($query, array($table_id));
			if($stmt){
				$ar=$DBC->fetch($stmt);
				$this->helper->update_table($ar['name']);
				$_POST['table_name']=$ar['name'];
			}
				
				
		}
	
		return $this->_defaultAction();
	}
	
	/**
	 * Main
	 * @param void
	 * @return string
	 */
	function main () {
		$DBC=DBC::getInstance();
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$form_data = $this->data_model;
		$rs = $this->getTopMenu();
		
		$table_id=intval($this->getRequestValue('table_id'));
		
		if(0!==$table_id){
			$rs .= '<h4>Таблица ID '.$table_id.'</h4>';
		}
		
		$optype_field=null;
		if(defined('DEVMODE')){
			$query='SELECT * FROM '.DB_PREFIX.'_columns WHERE `table_id`=? AND name=?';
			$stmt=$DBC->query($query, array($table_id, 'optype'));
			if($stmt){
				$optype_field=$DBC->fetch($stmt);
			}
		}
		
	
		switch( $this->getRequestValue('do') ){
			case 'structure' : {
				$rs .= $this->structure_processor();
				break;
			}
			
			case 'copy' : {
				$column_id=intval($this->getRequestValue($this->primary_key));
				$this->copyColumn($column_id, 'xxx2');
				$rs.=$this->grid();
				break;
			}
			
			case 'add_lang_fields' : {
				$columns_id=intval($this->getRequestValue($this->primary_key));
				
				$langs=array_values(Multilanguage::availableLanguages());
				
				$DBC=DBC::getInstance();
				$query='SELECT title, name FROM '.DB_PREFIX.'_columns WHERE `'.$this->primary_key.'`=?';
				$stmt=$DBC->query($query, array($columns_id));
				
				if($stmt){
					$cdata=$DBC->fetch($stmt);
				}
				
				//$res='';
				foreach ($langs as $lng){
					$new_name=$cdata['name'].'_'.$lng;
					$new_title=$cdata['title'].' '.$lng;
					$opts=array(
							'required'=>0,
							'title'=>$new_title
					);
					$rs.=$this->copyColumn($columns_id, $new_name, $opts);
				}
				
				
				$rs.=$this->grid();
				break;
			}
	
			case 'edit_done' : {
				$form_data[$this->table_name]['action']['name']='uaction';
				$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
				$form_data[$this->table_name]['action']['name']='action';
				
				
				//unset($form_data[$this->table_name]['action']);
				if($form_data[$this->table_name]['dbtype']['value']!=1){
					$form_data[$this->table_name]['dbtype']['value']=0;
				}else{
					$form_data[$this->table_name]['dbtype']['value']=1;
				}
				
				$data_model->forse_auto_add_values($form_data[$this->table_name]);
				//$data_model->clear_auto_add_values(&$form_data[$this->table_name]);
				if ( !$this->check_data( $form_data[$this->table_name] ) ) {
					$form_data[$this->table_name]['action']['name']='uaction';
					$rs .= $this->get_form($form_data[$this->table_name], 'edit');
				} else {
					$form_data[$this->table_name]['parameters']['value']=serialize($form_data[$this->table_name]['parameters']['value']);
					$this->edit_data($form_data[$this->table_name]);
					if ( $this->getError() ) {
						$rs .= $this->get_form($form_data[$this->table_name], 'edit');
					} else {
						$query='SELECT name FROM '.DB_PREFIX.'_table WHERE table_id='.$this->getRequestValue('table_id').' LIMIT 1';
						$stmt=$DBC->query($query);
						if($stmt){
							$ar=$DBC->fetch($stmt);
							$this->helper->update_table($ar['name']);
							$_POST['table_name']=$ar['name'];
						}
						$rs .= $this->grid();
					}
				}
				
				break;
			}
	
			case 'edit' : {
				if ( $this->getRequestValue('subdo') == 'delete_image' ) {
					$this->deleteImage($this->table_name, $this->getRequestValue('image_id'));
				}
				 
				if ( $this->getRequestValue('subdo') == 'up_image' ) {
					$this->reorderImage($this->table_name, $this->getRequestValue('image_id'), $this->primary_key, $this->getRequestValue($this->primary_key),'up');
				}
				 
				if ( $this->getRequestValue('subdo') == 'down_image' ) {
					$this->reorderImage($this->table_name, $this->getRequestValue('image_id'), $this->primary_key, $this->getRequestValue($this->primary_key), 'down');
				}
				
				
				//echo '<pre>';
				//print_r($form_data[$this->table_name]);
				if ( $this->getRequestValue('language_id') > 0 and !$this->language->get_version($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $this->getRequestValue('language_id')) ) {
					$rs .= $this->get_form($form_data[$this->table_name], 'new', $this->getRequestValue('language_id'));
				} else {
					if ( $this->getRequestValue('language_id') > 0 ) {
						$form_data[$this->table_name] = $data_model->init_model_data_from_db_language ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name], false, $this->getRequestValue('language_id') );
					} else {
						$form_data[$this->table_name] = $data_model->init_model_data_from_db ( $this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key), $form_data[$this->table_name] );
					}
					
					$form_data[$this->table_name]['action']['name']='uaction';
					
					if($form_data[$this->table_name]['primary_key_table']['value']!=''){
						$form_data[$this->table_name]['primary_key_name']['select_data']=$this->getTableFields($form_data[$this->table_name]['primary_key_table']['value']);
						$form_data[$this->table_name]['value_name']['select_data']=$this->getTableFields($form_data[$this->table_name]['primary_key_table']['value']);
					}
					if($form_data[$this->table_name]['dbtype']['value']=='notable' || $form_data[$this->table_name]['dbtype']['value']=='0'){
						$form_data[$this->table_name]['dbtype']['value']=0;
					}else{
						$form_data[$this->table_name]['dbtype']['value']=1;
					}
					$rs .= $this->get_form($form_data[$this->table_name], 'edit');
				}
	
				break;
			}
			case 'delete' : {
				$this->delete_data($this->table_name, $this->primary_key, $this->getRequestValue($this->primary_key));
				if ( $this->getError() ) {
					$rs .= '<div align="center">Ошибка при удалении записи: '.$this->GetErrorMessage().'<br>';
					$rs .= '<a href="?action='.$this->action.'">ОК</a>';
					$rs .= '</div>';
				} else {
					$rs .= $this->grid();
				}
	
	
				break;
			}
			case 'add_meta' : {
				//$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
				$form_data = $this->data_model;
				$form_data[$this->table_name]['table_id']['value']=$this->getRequestValue('table_id');
				$form_data[$this->table_name]['type']['value']='safe_string';
				$form_data[$this->table_name]['active']['value']=1;
				$form_data[$this->table_name]['name']['value']='meta_title';
				$form_data[$this->table_name]['title']['value']='META TITLE';
				$form_data[$this->table_name]['dbtype']['value']='';
				$new_record_id=$this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
				
				$form_data = $this->data_model;
				$form_data[$this->table_name]['table_id']['value']=$this->getRequestValue('table_id');
				$form_data[$this->table_name]['type']['value']='safe_string';
				$form_data[$this->table_name]['active']['value']=1;
				$form_data[$this->table_name]['name']['value']='meta_keywords';
				$form_data[$this->table_name]['title']['value']='META KEYWORDS';
				$new_record_id=$this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
				
				$form_data = $this->data_model;
				$form_data[$this->table_name]['table_id']['value']=$this->getRequestValue('table_id');
				$form_data[$this->table_name]['active']['value']=1;
				$form_data[$this->table_name]['type']['value']='textarea';
				$form_data[$this->table_name]['name']['value']='meta_description';
				$form_data[$this->table_name]['title']['value']='META DESCRIPTION';
				$new_record_id=$this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
				
				$query = 'SELECT name FROM '.DB_PREFIX.'_table WHERE table_id='.$this->getRequestValue('table_id').' LIMIT 1';
				$stmt=$DBC->query($query);
				if($stmt){
					$ar=$DBC->fetch($stmt);
					$this->helper->update_table($ar['name']);
					$_POST['table_name']=$ar['name'];
				}
				
				$rs .= $this->grid();
			
			
				break;
			}	
			case 'new_done' : {
				$form_data[$this->table_name]['action']['name']='uaction';
				$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
				$form_data[$this->table_name]['action']['name']='action';
				
				$data_model->forse_auto_add_values($form_data[$this->table_name]);
				
				if($form_data[$this->table_name]['dbtype']['value']!=1){
					$form_data[$this->table_name]['dbtype']['value']='notable';
				}else{
					$form_data[$this->table_name]['dbtype']['value']='';
				}
				if ( !$this->check_data( $form_data[$this->table_name] ) ) {
					$form_data[$this->table_name]['action']['name']='uaction';
					$rs .= $this->get_form($form_data[$this->table_name], 'new');
					 
				} else {
					$form_data[$this->table_name]['parameters']['value']=serialize($form_data[$this->table_name]['parameters']['value']);
					$new_record_id=$this->add_data($form_data[$this->table_name], $this->getRequestValue('language_id'));
					if ( $this->getError() ) {
						$rs .= $this->get_form($form_data[$this->table_name], 'new');
					} else {
					    $query = "update ".DB_PREFIX."_columns set sort_order={$new_record_id} where columns_id={$new_record_id}";
					    $stmt=$DBC->query($query);
					    $query = 'SELECT name FROM '.DB_PREFIX.'_table WHERE table_id='.$this->getRequestValue('table_id').' LIMIT 1';
					    $stmt=$DBC->query($query);
					    if($stmt){
					    	$ar=$DBC->fetch($stmt);
					    	$this->helper->update_table($ar['name']);
					    	$_POST['table_name']=$ar['name'];
					    }
					    $rs .= $this->grid();
					}
				}
				break;
			}
				
			case 'new' : {
				$form_data[$this->table_name]['action']['name']='uaction';
				$form_data[$this->table_name]['table_id']['value']=$this->getRequestValue('table_id');
				
				
				
				$rs .= $this->get_form($form_data[$this->table_name]);
				break;
			}
			case 'mass_delete' : {
				$id_array=array();
				$ids=trim($this->getRequestValue('ids'));
				if($ids!=''){
					$id_array=explode(',',$ids);
				}
				$rs .= $this->mass_delete_data($this->table_name, $this->primary_key, $id_array);
				break;
			}
			case 'mass_activity_set' : {
				/*$id_array=array();
				$ids=trim($this->getRequestValue('ids'));
				if($ids!=''){
					$id_array=explode(',',$ids);
				}*/
				$rs .= $this->_mass_activity_setAction();
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
	
	
	function get_form( $form_data=array(), $do = 'new', $language_id = 0, $button_title = '', $action='' ){
		global $smarty;
		if($button_title==''){
			$button_title = Multilanguage::_('L_TEXT_SAVE');
		}
		if($action==''){
			$form_action='index.php?';
		}else{
			$form_action=$action;
		}
	    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
		
		
		$langs=array_values(Multilanguage::availableLanguages());
		/*if(count($_langs)>0){
			foreach($_langs as $l){
				$langs[$l]=$l;
			}
		}*/
		$smarty->assign('langs', $langs);
		$smarty->assign('langsjs', json_encode($langs));
		
        $rs .= $this->get_ajax_functions();
        $rs .= '<script>var langs='.json_encode(array_values($langs)).'</script>';
        $rs .= '<script src="'.SITEBILL_MAIN_URL.'/apps/columns/js/interface.js"></script>';
        $rs .= '<div id="element_preview"><h3>Предпросмотр элемента формы</h3><div id="element_preview_c">element_preview</div></div>';
		$rs .= '<form method="post" id="column_form" class="form-horizontal" action="'.$form_action.'" enctype="multipart/form-data">';
		if ( $this->getError() ) {
			$smarty->assign('form_error', $form_generator->get_error_message_row($this->GetErrorMessage()));
		}
		
		
		//$smarty->assign('langs', json_encode($langs));
		
		$el = $form_generator->compile_form_elements($form_data, true);
		//array_unshift($el['public'][$this->getConfigValue('default_tab_name')], array('title'=>'Предпросмотр','name'=>'_element_preview','html'=>'<div id="_element_preview"></div>'));
		//$el['public'][$this->getConfigValue('default_tab_name')]['_element_preview']=array('title'=>'Предпросмотр','name'=>'_element_preview','html'=>'<div id="_element_preview"></div>');
		//echo '<pre>';
		//print_r($form_data);
		//echo '</pre>';
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
		$el['controls']['submit']=array('html'=>'<button id="formsubmit" onClick="return SitebillCore.formsubmit(this);" type="submit" name="submit" class="btn btn-primary">'.$button_title.'</button>');
			
		
		
		
		
		$smarty->assign('form_elements',$el);
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl')){
			$tpl_name=SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/admin/template/form_data.tpl';
		}else{
			$tpl_name=$this->getAdminTplFolder().'/data_form.tpl';
		}
		
		$tpl_name=SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/template/form.tpl';
		
		return $smarty->fetch($tpl_name);
	}
	
	function check_data ( $form_data ) {
		if(preg_match('/([^\da-zA-Z0-9_])/', $form_data['name']['value'])){
			$this->riseError('Недопустимые символы в системном имени');
			return false;
		}
		if(!preg_match('/([a-zA-Z])/', $form_data['name']['value'])){
			$this->riseError('В системном имени должна быть хоть одна буква');
			return false;
		}
		if(!preg_match('/^[a-zA-Z_]/', $form_data['name']['value'])){
			$this->riseError('Системное имя должно начинаться с буквы или подчеркивания');
			return false;
		}
		
		return parent::check_data($form_data);
	}
	
	protected function _mass_activity_setAction(){
		$rs='';
		$ids=(array)$this->getRequestValue('batch_ids');
		if(count($ids)==0){
			$rs .= $this->grid();
		}
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$form_data = $this->data_model;
		
		foreach($form_data[$this->table_name] as $key=>$value){
			if($value['name']!='active_in_topic'){
				unset($form_data[$this->table_name][$key]);
			}
		}
		
		if(isset($_POST['submit'])){
				
			$form_data[$this->table_name] = $data_model->init_model_data_from_request($form_data[$this->table_name]);
			$need_to_update[]='active_in_topic';
				
		
			if(count($ids)<1){
				return $this->grid();
			}
		
			if(count($need_to_update)<1){
				return $this->grid();
			}
				
			$sub_form=array();
				
				
			if(isset($form_data[$this->table_name]['active_in_topic'])){
				$sub_form[$this->table_name]['active_in_topic']=$form_data[$this->table_name]['active_in_topic'];
			}
		
			if(empty($sub_form)){
				return $this->grid();
			}
		
			$sub_form[$this->table_name] = $data_model->init_model_data_from_request($sub_form[$this->table_name]);
			foreach($ids as $id){
				$concrete_form=$sub_form;
				$concrete_form[$this->table_name][$this->primary_key]['value']=$id;
				$concrete_form[$this->table_name][$this->primary_key]['type'] = 'primary_key';
				//print_r($concrete_form);
				$this->edit_data($concrete_form[$this->table_name], 0, $id);
			}
			$rs .= $this->grid();
		}else{
			foreach($ids as $id){
				$str_a[]='batch_ids[]='.$id;
			}
			$rs .= $this->get_form($form_data[$this->table_name], 'edit', 0, '', SITEBILL_MAIN_URL.'/admin/index.php?action=columns&do=mass_activity_set&'.implode('&', $str_a));
		}
		return $rs;
	}
	
	
	
}