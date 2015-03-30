<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Table admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class table_admin extends Object_Manager {
    /**
     * Constructor
     */
    function __construct( $realty_type = false ) {
        $this->SiteBill();
        $this->table_name='table';
        $this->app_title='Редактор таблиц';
        $this->action = 'table';
        $this->primary_key = 'table_id';
        $this->section='';
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/table_model.php');
        $Object=new Table_Model();
		$this->data_model=$Object->get_model();
        
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php');
		$this->helper = new Admin_Table_Helper();
		if ( !$this->helper->check_table_exist('table') or !$this->helper->check_table_exist('columns')) {
		    $this->install();
		}
		
		
		
		
		$section=$this->getRequestValue('section');
		if($section===NULL){
			if(isset($_SESSION['_table_section']) && $_SESSION['_table_section']!=''){
				$this->section=$_SESSION['_table_section'];
			}else{
				$this->section='table';
				$_SESSION['_table_section']='table';
			}
		}else{
			$this->section=$this->getRequestValue('section');
			$_SESSION['_table_section']=$this->section;
		}
		
		
		//$this->install();
	}
	
	public function _preload(){
		if(1==$this->getConfigValue('block_user_search_forms')){
			
		}else{
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/search_forms_admin.php';
			$SFA=new search_forms_admin();
			$this->template->assert('local_search_forms', $SFA->get_local_search_forms());
		}
		
	}
    
	function main () {
		
		if($this->section=='search_forms'){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/search_forms_admin.php';
			$SFA=new search_forms_admin();
			$rs.=$SFA->main();
			return $rs;
		}elseif($this->section=='gridmanager'){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/gridmanager_admin.php';
			$SFA=new gridmanager_admin();
			$rs.=$SFA->main();
			return $rs;
		}elseif($this->section=='front_gridmanager'){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/front_gridmanager_admin.php';
			$SFA=new front_gridmanager_admin();
			$rs.=$SFA->main();
			return $rs;
		}elseif($this->section=='modelmanager'){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/modelmanager_admin.php';
			$SFA=new modelmanager_admin();
			$rs.=$SFA->main();
			return $rs;
		}/*elseif($this->section=='formcompounder'){
			require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/formcompounder_admin.php';
			$SFA=new formcompounder_admin();
			$rs.=$SFA->main();
			return $rs;
		}*/
		$rs.=parent::main();
    	
    	if ( !$this->helper->check_table_exist('table') or !$this->helper->check_table_exist('columns')) {
    		$rs .= '<h1>Приложение не установлено. <a href="?action=table&do=structure&subdo=install">Установить</a></h1>';
    	}
    	 
	    return $rs;
	}
	
	protected function _fieldrulesAction(){
		$table_id=(int)$this->getRequestValue('table_id');
		$ret=array();
		$groups=array();
		
		$DBC=DBC::getInstance();
		$query = 'SELECT columns_id, active, table_id, name, title, type FROM '.DB_PREFIX.'_columns WHERE table_id=? ORDER BY active DESC, sort_order ASC';
		$stmt=$DBC->query($query, array($table_id));
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ret[$ar['columns_id']]=$ar;
			}
		}
		
		$columns_ids=array_keys($ret);
		$query = 'SELECT * FROM '.DB_PREFIX.'_group';
		$stmt=$DBC->query($query, array($table_id));
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$groups[]=$ar;
			}
		}
		$groups[]=array('name'=>'Гость', 'group_id'=>0);
		global $smarty;
		$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/template/columns_rules_grid.tpl';
		$smarty->assign('columns', $ret);
		$smarty->assign('groups', $groups);
		$smarty->assign('rules', $this->loadAllRules($columns_ids));
		return $smarty->fetch($tpl);
		print_r($ret);
		
		return '_fieldrulesAction';
	}
	
	protected function _copytableAction(){
		$table_id=(int)$this->getRequestValue('table_id');
		if(isset($_POST['submit'])){
			$new_name=$this->getRequestValue('name');
			$DBC=DBC::getInstance();
			$query='SELECT COUNT(table_id) AS _cnt FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE name=?';
			$stmt=$DBC->query($query, array($new_name));
			$ar=$DBC->fetch($stmt);
			$id=0;
			if((int)$ar['_cnt']==0){
				$query='INSERT INTO '.DB_PREFIX.'_table (name) VALUES (?)';
				$stmt=$DBC->query($query, array($new_name));
				if($stmt){
					$id=$DBC->lastInsertId();
				}
			}
			
			if($id!=0){
				$columns=array();
				$query='SELECT * FROM '.DB_PREFIX.'_columns WHERE table_id=?';
				$stmt=$DBC->query($query, array($table_id));
				if($stmt){
					while($ar=$DBC->fetch($stmt)){
						$columns[]=$ar;
					}
				}
			}
			
			
			
		}else{
			$ret='<form action=""><input type="text" name="name"><input type="submit" name="submit"></form>';
		}
		return $ret;
		$ret=array();
		$groups=array();
	
		$DBC=DBC::getInstance();
		$query = 'SELECT columns_id, active, table_id, name, title, type FROM '.DB_PREFIX.'_columns WHERE table_id=? ORDER BY active DESC, sort_order ASC';
		$stmt=$DBC->query($query, array($table_id));
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ret[$ar['columns_id']]=$ar;
			}
		}
	
		$columns_ids=array_keys($ret);
		$query = 'SELECT * FROM '.DB_PREFIX.'_group';
		$stmt=$DBC->query($query, array($table_id));
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$groups[]=$ar;
			}
		}
		$groups[]=array('name'=>'Гость', 'group_id'=>0);
		global $smarty;
		$tpl=SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/template/columns_rules_grid.tpl';
		$smarty->assign('columns', $ret);
		$smarty->assign('groups', $groups);
		$smarty->assign('rules', $this->loadAllRules($columns_ids));
		return $smarty->fetch($tpl);
	}
	
	private function loadAllRules($columns_ids){
		$ret=array();
		$DBC=DBC::getInstance();
		$query='SELECT * FROM '.DB_PREFIX.'_group_rule WHERE columns_id IN ('.implode(',', $columns_ids).')';
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ret['edit'][$ar['columns_id']][$ar['group_id']]=$ar['edit_status'];
				$ret['view'][$ar['columns_id']][$ar['group_id']]=$ar['view_status'];
			}
		}
		
		return $ret;
	}
	
	protected function _handlerAction(){
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/customentity/admin/admin.php')){
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/customentity/admin/admin.php');
			$custom_admin_entity_menu=customentity_admin::getEntityList();
		}else{
			header('location:'.SITEBILL_MAIN_URL.'/admin/?action=table');
		}
		$subdo=$this->getRequestValue('subdo');
		$table=$this->getRequestValue('table');
		
		if($subdo=='add_handler' && ''!=$table && ''!=$this->getRequestValue('entity_title') && !isset($custom_admin_entity_menu[$table])){
			$DBC=DBC::getInstance();
			$query='INSERT INTO '.DB_PREFIX.'_customentity (entity_name, entity_title) VALUES (?,?)';
			$stmt=$DBC->query($query, array($table, $this->getRequestValue('entity_title')));
			header('location:'.SITEBILL_MAIN_URL.'/admin/?action=table');
			exit();
		}elseif($subdo=='delete_handler' && ''!=$table && isset($custom_admin_entity_menu[$table])){
			$DBC=DBC::getInstance();
			$query='DELETE FROM '.DB_PREFIX.'_customentity WHERE entity_name=?';
			$stmt=$DBC->query($query, array($table));
			header('location:'.SITEBILL_MAIN_URL.'/admin/?action=table');
			exit();
		}
		
		if(isset($custom_admin_entity_menu[$table])){
			$rs='<form action="'.SITEBILL_MAIN_URL.'/admin/" method="post">
			<fieldset>
		    <legend>Пользовательский обработчик для <b>'.$table.'</b> существует. Хотите удалить?</legend>
		    <label>Имя модели</label>
		    <input type="text" disabled="disabled" name="entity_name" value="'.$table.'">
		    		<label>Название</label>
		    <input type="text" name="entity_title" value="'.$custom_admin_entity_menu[$table]['entity_title'].'">
		    		<input type="hidden" name="action" value="table">
		    <input type="hidden" name="do" value="handler">
		    <input type="hidden" name="subdo" value="delete_handler">
		    <input type="hidden" name="table" value="'.$table.'">
		    <br />
		    <button type="submit" class="btn btn-danger">Удалить</button>
		    <a class="btn btn-warning" href="'.SITEBILL_MAIN_URL.'/admin/?action=table">Нет, не нужно</a> 
		    		
		  </fieldset>
    		</form>';
			return $rs;
			return 'Handler setted. <a href="">Delete</a>?';
		}else{
			$rs='<form action="'.SITEBILL_MAIN_URL.'/admin/" method="post">
			<fieldset>
		    <legend>Пользовательский обработчик для <b>'.$table.'</b> не создан. Хотите создать?</legend>
		    <label>Имя модели</label>
		    <input type="text" disabled="disabled" name="entity_name" value="'.$table.'">
		    		<label>Название</label>
		    <input type="text" name="entity_title" value="">
		    <input type="hidden" name="action" value="table">
		    <input type="hidden" name="do" value="handler">
		    <input type="hidden" name="subdo" value="add_handler">
		  	<input type="hidden" name="table" value="'.$table.'">
		    <br />
		    <button type="submit" class="btn btn-primary">Создать</button> 
		    <a class="btn btn-warning" href="'.SITEBILL_MAIN_URL.'/admin/?action=table">Нет, не нужно</a>
		  </fieldset>
    		</form>';
			return $rs;
		}
		return '_handlerAction';
	}
	
	function init_from_php ( $table_name ) {
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		$kvartira_model = $data_model->get_kvartira_model();
		$street_model = $data_model->get_street_model();
		$city_model = $data_model->get_city_model();
		//print_r($street_model);
		$this->create_table_and_columns($kvartira_model, 'data');
		$this->create_table_and_columns($street_model, 'street');
		$this->create_table_and_columns($city_model, 'city');
		//print_r($kvartira_model);
		
		
	}
	
	function create_table_and_columns ( $model, $table_name ) {
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/columns/admin/admin.php');
		$columns_admin = new columns_admin();
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		
		$table_id = $data_model->get_value_id_by_name('table', 'name', 'table_id', $table_name);
		
		if ( !$table_id ) {
			$table_id = $this->add_table_record($table_name);
		}
		
		$columns_admin->data_model['columns']['table_id']['value'] = $table_id;
		
		foreach ($model[$table_name] as $item_id => $item) {
						
			$columns_admin->data_model['columns']['name']['value'] = $item['name'];
			$columns_admin->data_model['columns']['active']['value'] = 1;
			$columns_admin->data_model['columns']['title']['value'] = $item['title'];
			$columns_admin->data_model['columns']['type']['value'] = $item['type'];
			$columns_admin->data_model['columns']['value']['value'] = $item['value'];
			$columns_admin->data_model['columns']['primary_key_name']['value'] = $item['primary_key_name'];
				
			$columns_admin->data_model['columns']['primary_key_table']['value'] = $item['primary_key_table'];
			$columns_admin->data_model['columns']['value_string']['value'] = $item['value_string'];
			$columns_admin->data_model['columns']['query']['value'] = $item['query'];
			$columns_admin->data_model['columns']['value_name']['value'] = $item['value_name'];
			$columns_admin->data_model['columns']['title_default']['value'] = $item['title_default'];
			$columns_admin->data_model['columns']['value_default']['value'] = $item['value_default'];
			
			$columns_admin->data_model['columns']['value_table']['value'] = $item['value_table'];
			$columns_admin->data_model['columns']['value_primary_key']['value'] = $item['value_primary_key'];
			$columns_admin->data_model['columns']['value_field']['value'] = $item['value_field'];
			$columns_admin->data_model['columns']['assign_to']['value'] = $item['assign_to'];
			$columns_admin->data_model['columns']['dbtype']['value'] = $item['dbtype'];
			if (isset($item['select_data']) && is_array($item['select_data']) && !empty($item['select_data'])){
				$str='';
				foreach($item['select_data'] as $k=>$v){
					$str.='{'.$k.'~~'.$v.'}';
				}
				$columns_admin->data_model['columns']['select_data']['value'] = serialize($item['select_data']);
				$columns_admin->data_model['columns']['select_data']['value'] = $str;
			}else{
				$columns_admin->data_model['columns']['select_data']['value'] = '';
			}
			//$columns_admin->data_model['columns']['select_values']['value'] = ((is_array($item['select_values']) && !empty($item['select_values'])) ? implode('|',$item['select_values']) : '');
			$columns_admin->data_model['columns']['table_name']['value'] = $item['table_name'];
			$columns_admin->data_model['columns']['primary_key']['value'] = $item['primary_key'];
			$columns_admin->data_model['columns']['primary_key_value']['value'] = $item['primary_key_value'];
			$columns_admin->data_model['columns']['action']['value'] = $item['action'];
			$columns_admin->data_model['columns']['hint']['value'] = $item['hint'];
			$columns_admin->data_model['columns']['tab']['value'] = $item['tab'];
			
			$columns_admin->data_model['columns']['sort_order']['value'] = 0;
			if ( $item['required'] == 'on' ) {
				$item['required'] = 1;
			} else {
				$item['required'] = 0;
			}
			$columns_admin->data_model['columns']['required']['value'] = $item['required'];
			
			if ( $item['unique'] == 'on' ) {
				$item['unique'] = 1;
			} else {
				$item['unique'] = 0;
			}
			$columns_admin->data_model['columns']['unique']['value'] = $item['unique'];
			
			if(isset($item['parameters']) && $item['parameters']!='' && is_array($item['parameters']) && !empty($item['parameters'])){
				$columns_admin->data_model['columns']['parameters']['value'] = serialize($item['parameters']);
			}
			
			//echo '<hr>devider'.$item['table_name'].'<br>';
			//echo '<pre>';
			//print_r($columns_admin->data_model['columns']);
			//echo '</pre>';
				
			
			if ( $columns_admin->check_data($columns_admin->data_model['columns']) ) {
				$column_id = $columns_admin->add_data($columns_admin->data_model['columns']);
				if ( $columns_admin->getError() ) {
				    //echo $columns_admin->GetErrorMessage().'<br>';
					$rs .= $columns_admin->GetErrorMessage().'<br>';
				} else {
					$columns_admin->data_model['columns']['columns_id']['value'] = $column_id;
					$columns_admin->data_model['columns']['sort_order']['value'] = $column_id;
					$columns_admin->edit_data($columns_admin->data_model['columns'],0, $column_id);
				}
			} else {
				//echo $columns_admin->GetErrorMessage().'<br>';
				$rs .= $columns_admin->GetErrorMessage().'<br>';
			}
		}
		return $rs;
	}
	
	function add_table_record ( $table_name ) {
		$this->data_model['table']['name']['value'] = $table_name;
		
		$new_record_id=$this->add_data($this->data_model['table']);
		if ( $this->getError() ) {
			return $this->GetErrorMessage();
		}
		return $new_record_id;
	}
	
	function structure_processor () {
		switch ( $this->getRequestValue('subdo') ) {
			case 'create_table':
				$rs = $this->helper->create_table($this->getRequestValue('table_name'));
			break;
			
			case 'update_table':
				$rs = $this->helper->update_table($this->getRequestValue('table_name'));
			break;
			
			case 'clear_table':
				$rs = $this->helper->clear_table($this->getRequestValue('table_name'));
			break;
			
			case 'install':
				$rs = $this->install();
				$rs .= $this->init_from_php('data');
			break;
			
			case 'init_from_php':
				$rs = $this->init_from_php('data');
			break;
					
		}
		return $rs;
	}
	
	
	
	function getTopMenu () {
		$rs = '<a href="?action=table&section=table" class="btn btn-primary">Список таблиц</a> ';
	    $rs .= '<a href="?action=table&section=table&do=new" class="btn btn-primary">Добавить таблицу</a> ';
	    $rs .= '<a href="?action=columns&do=new" class="btn btn-primary">Добавить колонку в таблицу</a>	';
	    $rs .= '<a href="?action=table&section=gridmanager" class="btn btn-primary">Grid Manager</a> ';
	    $rs .= '<a href="?action=table&section=search_forms" class="btn btn-primary">Формы поиска</a> ';
	    //$rs .= '<a href="?action=table&section=modelmanager" class="btn btn-primary">Model Manager</a> ';
	    $rs .= '<a href="?action=table&section=front_gridmanager" class="btn btn-primary">Сетки</a> ';
	    return $rs;
	}
	
	function grid () {
		$rs = $this->get_table_list();
		return $rs;
	}
	
	function get_table_list () {
		$query = "SELECT * FROM ".DB_PREFIX."_table ORDER BY name ASC, table_id DESC";
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/customentity/admin/admin.php')){
			require_once(SITEBILL_DOCUMENT_ROOT.'/apps/customentity/admin/admin.php');
			$custom_admin_entity_menu=customentity_admin::getEntityList();
		}
		
		
		$this->db->exec($query);
		if ( !$this->db->success ) {
			echo $this->db->error;
			return false;
		}
		while ( $this->db->fetch_assoc() ) {
			$ra[$this->db->row['table_id']] = $this->db->row;
		}
		$rs .= '<script src="'.SITEBILL_MAIN_URL.'/apps/table/js/interface.js"></script>';
		$current_table=$this->getRequestValue('table_name');
		
		if(count($ra)>0){
			
			$rs .= '<div class="accordion" id="accordion2">';
			foreach ( $ra as $primary_key_value => $item_array ) {
				$rs .= '<div class="accordion-group">';
				$rs .= '<div class="accordion-heading">';
				$rs .= '<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#'.$item_array['name'].'">';
				$rs .= $item_array['name'].($item_array['description']!='' ? ' ('.$item_array['description'].')' : '');
				$rs .= '</a>';
				$rs .= '<a class="btn btn-success" href="'.SITEBILL_MAIN_URL.'/admin/?action=table&do=fieldrules&table_id='.$primary_key_value.'" title="Редактировать"><i class="icon-white icon-user"></i></a> ';
				$rs .= '<a class="btn btn-info" href="'.SITEBILL_MAIN_URL.'/admin/?action=table&do=edit&table_id='.$primary_key_value.'" title="Редактировать"><i class="icon-white icon-pencil"></i></a> ';
				$rs .= '<a class="btn btn-info" href="'.SITEBILL_MAIN_URL.'/admin/?action=columns&do=new&table_id='.$primary_key_value.'" title="Добавить колонку"><i class="icon-white icon-plus"></i></a> ';
				$rs .= '<a class="btn btn-danger" href="'.SITEBILL_MAIN_URL.'/admin/?action=table&do=delete&table_id='.$primary_key_value.'" title="Удалить" onclick="if ( confirm(\'Действительно хотите удалить запись?\') ) {return true;} else {return false;}"><i class="icon-white icon-remove"></i></a> ';
				
				if(isset($custom_admin_entity_menu[$item_array['name']])){
					$rs .= '<a class="btn btn-success" href="'.SITEBILL_MAIN_URL.'/admin/?action=table&do=handler&table='.$item_array['name'].'" title="Зарегистрированный обработчик"><i class="icon-white icon-asterisk"></i></a> ';
				}else{
					$rs .= '<a class="btn" href="'.SITEBILL_MAIN_URL.'/admin/?action=table&do=handler&table='.$item_array['name'].'" title="Обработчик по-умолчанию"><i class="icon-white icon-asterisk"></i></a> ';
				}
				
				if ( $this->helper->check_table_exist($item_array['name']) ) {
					//$rs .= '<a class="btn btn-info update_table" href="#" title="Сохранить сортировку"><i class="icon-white icon-refresh"></i></a> ';
					//$rs .= '<a class="btn btn-info" href="?action=table&do=structure&subdo=update_table&table_name='.$item_array['name'].'" title="Обновить таблицу" ><i class="icon-white icon-list-alt"></i></a>';
				} else {
					$rs .= '<a href="'.SITEBILL_MAIN_URL.'/admin/?action=table&do=structure&subdo=create_table&table_name='.$item_array['name'].'" title="Создать таблицу"><img src="'.SITEBILL_MAIN_URL.'/apps/table/img/create_table.png" border="0" class="admin_control"></a>';
				}
				$rs .= '<a class="btn btn-warning" href="?action=table&&do=structure&subdo=clear_table&table_name='.$item_array['name'].'" title="Очистить" onclick="if ( confirm(\'Действительно хотите очистить таблицу? Будут удалены все поля таблицы не входящие в текущую модель и данные, содержащиеся в этих полях.\') ) {return true;} else {return false;}"><i class="icon-white icon-cog"></i><sup>beta</sup></a> ';
				//$rs .= '<a class="btn" href="'.SITEBILL_MAIN_URL.'/admin/?action=table&do=copytable&table_id='.$primary_key_value.'" title="Копировать модель"><i class="icon-white icon-book"></i></a> ';
				$rs .= '</div>';
				$rs .= '<div id="'.$item_array['name'].'" class="accordion-body'.($current_table==$item_array['name'] ? ' in' : '').' collapse">';
				$rs .= '<div class="accordion-inner">';
				$rs .= $this->get_columns_list($item_array['table_id']);
				$rs .= '</div>';
				$rs .= '</div>';
				$rs .= '</div>';
				
			}
			
			$rs .= '</div>';
		}else{
			$rs.='<br><br>Записей не найдено';
		}
		
		return $rs;
	}
	
	function get_columns_list ( $table_id ) {
		/*
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php';
		$SM=new Structure_Manager();
		$structure=$SM->loadCategoryStructure();
		$structure=$structure['catalog'];
		$structure[0]['name']='Без ограничений';
		*/
		$this->db->exec('SELECT name FROM '.DB_PREFIX.'_table WHERE table_id='.$table_id.' LIMIT 1');
		$this->db->fetch_assoc();
		$table_name=$this->db->row['name'];
		$query = "SELECT * FROM ".DB_PREFIX."_columns where table_id=$table_id ORDER BY sort_order asc";
		
		$this->db->exec($query);
		if ( !$this->db->success ) {
			echo $this->db->error;
			return false;
		}
		while ( $this->db->fetch_assoc() ) {
			$ra[$this->db->row['columns_id']] = $this->db->row;
		}
		if(count($ra)>0){
			$rs = '<table border="0" width="100%" class="columns_list">';
			$rs .= '<tbody class="applied table">';
			foreach ( $ra as $primary_key_value => $item_array ) {
				if ( $item_array['active'] == 0 ) {
					$class = "row3notactive column";
				} else {
					$class = "row3 column";
				}
				if(isset($this->grid_controls) && count($this->grid_controls) > 0){
					$rs .= '<tr alt="'.$primary_key_value.'" class="'.$class.'">';
				}else{
					$rs .= '<tr alt="'.$primary_key_value.'" class="'.$class.'">';
				}
				$rs .= '<td><input type="checkbox" class="checker" value="'.$item_array['columns_id'].'" /></td>';
				
				$icon_class='';
				switch($item_array['type']){
					case 'checkbox' : {
						$icon_class='icon-check';
						break;
					}
					case 'geodata' : 
					case 'gadres' : 
					{
						$icon_class='icon-map-marker';
						break;
					}
					case 'safe_string' : 
					case 'mobilephone' :
					{
						$icon_class='icon-font';
						break;
					}
					case 'uploads' : 
					case 'uploadify_image' : 
					{
						$icon_class='icon-picture';
						break;
					}
					case 'date' :
					case 'dtdate' :
					case 'dtdatetime' :
					case 'dttime' :
					{
						$icon_class='icon-calendar';
						break;
					}	
					case 'select_box' :
					case 'select_box_structure' :
					case 'select_by_query' :
					case 'structure' :
					{
						$icon_class='icon-tasks';
						break;
					}
					case 'primary_key' :
					{
						$icon_class='icon-filter';
						break;
					}
					case 'textarea' :
					case 'textarea_editor' :
					{
						$icon_class='icon-comment';
						break;
					}
				}
				
				
				$rs .= '<td>'.$item_array['name'].'</td>';
				$rs .= '<td><i class="icon '.$icon_class.'"></i> '.$item_array['type'].'</td>';
				//$rs .= '<td>'.$item_array['name'].'</td>';
				/*$rs .= '<td>';
				if($item_array['active_in_topic']!=''){
					$active_in_topic_array=array();
					$active_in_topic=explode(',', $item_array['active_in_topic']);
					foreach($active_in_topic as $ait){
						if(isset($structure[$ait])){
							$active_in_topic_array[]=$structure[$ait]['name'];
						}
					}
					$rs .= implode(', ', $active_in_topic_array);
				}else{
					$rs .= 'Без ограничений';
				}
				$rs .= '</td>';*/
				if($item_array['required']==1){
					$rs .= '<td><a class="state_change btn btn-warning" href="derequired" title="Обязательное" alt="'.$primary_key_value.'"><i class="icon-white icon-ok-circle"></i></a></td>';
				}else{
					$rs .= '<td><a class="state_change btn" href="required" title="Обязательное" alt="'.$primary_key_value.'"><i class="icon-white icon-ok-circle"></i></a></td>';
				}
				$rs .= '<td><a href="#" id="'.$item_array['name'].'" data-type="text" data-pk="'.$item_array['columns_id'].'" class="editable editable-click addeditable" style="display: inline;">'.$item_array['title'].'</a></td>';
				$rs .= '<td class="field_tab" alt="'.$primary_key_value.'">'.($item_array['tab']!='' ? '<span class="defined">'.$item_array['tab'].'</span>' : '<span class="undefined">Не указано</span>').'</td>';
				
				$rs .= '<td width="15%" nowrap>';
				$rs .= '<a href="?action=columns&do=edit&table_name='.$table_name.'&columns_id='.$primary_key_value.'" class="btn btn-info" title="Редактировать"><i class="icon-white icon-pencil"></i></a>';
				$rs .= ' <a href="?action=columns&do=delete&table_name='.$table_name.'&columns_id='.$primary_key_value.'" title="Удалить" class="btn btn-danger" onclick="if ( confirm(\'Действительно хотите удалить запись?\') ) {return true;} else {return false;}"><i class="icon-white icon-remove"></i></a>';
				if($item_array['active'] == 0){
					$rs .= ' <a class="state_change btn" href="activate" title="Активация" alt="'.$primary_key_value.'"><i class="icon-white icon-off"></i></a>';
				}else{
					$rs .= ' <a class="state_change btn btn-warning" href="deactivate" title="Активация" alt="'.$primary_key_value.'"><i class="icon-white icon-off"></i></a>';
				}
				$rs .= '</td>';
					
				$rs .= '</tr>';
			}
			$rs .= '<tfoot><tr><td colspan="8"><button class="delete_checked_columns btn btn-danger"><i class="icon-white icon-remove"></i>'.Multilanguage::_('L_DELETE_CHECKED').'</button> 
					<button class="activity_set_columns btn btn-inverse"><i class="icon-white icon-th"></i> Установить активность в категориях <sup>(beta)</sup></button> 
					</td></tr></tfoot>';
			$rs .= '</tbody>';
			$rs .= '</table>';
		
		}
		return $rs;
	}
	
	
	
	function test_case () {
		$model_data = $this->helper->load_model('data');
		$rs = $this->get_test_form($model_data['data']);
		return $rs;
	}
	
	
	function get_test_form ( $form_data=array(), $do = 'new', $language_id = 0, $button_title = '' ) {
	    if (!is_array($form_data)) {
	        return false;
	    }
		if($button_title==''){
			$button_title = Multilanguage::_('L_TEXT_SAVE');
		}
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
		$data_model = new Data_Model();
		 
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
	
		$rs .= $this->get_ajax_functions();
	
		$rs .= '<form method="post" action="index.php" enctype="multipart/form-data">';
		$rs .= '<table>';
		if ( $this->getError() ) {
			$rs .= $form_generator->get_error_message_row($this->GetErrorMessage());
		}
		$rs .= $form_generator->compile_form($form_data);
	
		if ( $do == 'new' ) {
			$rs .= '<input type="hidden" name="do" value="new_done">';
			$rs .= '<input type="hidden" name="'.$this->primary_key.'" value="'.$this->getRequestValue($this->primary_key).'">';
		} else {
			$rs .= '<input type="hidden" name="do" value="edit_done">';
			$rs .= '<input type="hidden" name="'.$this->primary_key.'" value="'.$form_data[$this->primary_key]['value'].'">';
		}
		//$rs .= '<input type="hidden" name="page" value="'.$_SESSION['rem_page'].'">';
		$rs .= '<input type="hidden" name="action" value="'.$this->action.'">';
		$rs .= '<input type="hidden" name="language_id" value="'.$language_id.'">';
	
		$rs .= '<tr>';
		$rs .= '<td></td>';
		$rs .= '<td><input type="submit" name="submit" id="formsubmit" onClick="return SitebillCore.formsubmit(this);" value="'.$button_title.'"></td>';
		$rs .= '</tr>';
		$rs .= '</table>';
		$rs .= '</form>';
	
		return $rs;
	
	}
    
	function install () {
		$query = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_table` (
		  `table_id` int(11) NOT NULL AUTO_INCREMENT,
		  `name` varchar(255) NOT NULL,
		  `description` text,
		  PRIMARY KEY (`table_id`),
		  UNIQUE KEY `table_name` (`name`)
		) ENGINE=MyISAM  DEFAULT CHARSET=".DB_ENCODING." AUTO_INCREMENT=1 ;";
		$this->db->exec($query);
		
		
		
		$query = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_columns` (
			  `columns_id` int(11) NOT NULL AUTO_INCREMENT,
			  `active` tinyint(1) NOT NULL DEFAULT '1',
			  `table_id` int(11) NOT NULL DEFAULT '0',
			  `group_id` varchar(255) NOT NULL,
			  `name` varchar(255) NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `primary_key_name` varchar(255) DEFAULT NULL,
			  `primary_key_table` varchar(255) DEFAULT NULL,
			  `value_string` varchar(255) DEFAULT NULL,
			  `query` text,
			  `value_name` varchar(255) DEFAULT NULL,
			  `title_default` varchar(255) DEFAULT NULL,
			  `value_default` varchar(255) DEFAULT NULL,
			  `value` varchar(255) NOT NULL,
			  `type` varchar(255) NOT NULL,
			  `required` tinyint(1) NOT NULL DEFAULT '0',
			  `unique` tinyint(1) NOT NULL DEFAULT '0',
			  `sort_order` int(11) NOT NULL DEFAULT '0',
			  `value_table` varchar(255) NOT NULL,
			  `value_primary_key` varchar(255) NOT NULL,
			  `value_field` varchar(255) NOT NULL,
			  `assign_to` varchar(255) NOT NULL,
			  `dbtype` varchar(255) NOT NULL,
			  `table_name` varchar(255) NOT NULL,
			  `primary_key` varchar(255) NOT NULL,
			  `primary_key_value` varchar(255) NOT NULL,
			  `action` varchar(255) NOT NULL,
			  `select_data` text NOT NULL,
              `active_in_topic` varchar(255) DEFAULT NULL,
              `tab` varchar(255) DEFAULT NULL,
              `hint` varchar(255) DEFAULT NULL,
				`entity` varchar(255) DEFAULT NULL,
				`combo` tinyint(1) NOT NULL DEFAULT '0',
				`parameters` text,
			  PRIMARY KEY (`columns_id`),
			  UNIQUE KEY `column_table` (`table_id`,`name`)
			) ENGINE=MyISAM  DEFAULT CHARSET=".DB_ENCODING." AUTO_INCREMENT=1 ;";
		$this->db->exec($query);
		
		//create unique index
		$query = "CREATE UNIQUE INDEX column_table ON ".DB_PREFIX."_columns ( table_id,  name) ";
		$this->db->exec($query);
		
		$rs = 'Приложение установлено';
		return $rs;
	}
	
	function ajax () {
    	if($this->getRequestValue('action') == 'reorder_columns'){
    		$this->reorder_columns($this->getRequestValue('ids'));
    	}
    	if($this->getRequestValue('action') == 'get_table_fields_select'){
    		return $this->getTableFieldsSelectbox($this->getRequestValue('table_name'));
    	}
    	if($this->getRequestValue('action') == 'get_preview'){
    		return $this->getFormElementPreview($this->getRequestValue('data'));
    	}
    	if($this->getRequestValue('action') == 'change_column_state'){
    		return $this->changeColumnState($this->getRequestValue('operation'),(int)$this->getRequestValue('id'));
    	}
    	if($this->getRequestValue('action') == 'change_field_tab'){
    		return $this->changeFieldTab($this->getRequestValue('tab_name'),(int)$this->getRequestValue('id'));
    	}
    	if($this->getRequestValue('action') == 'change_column_field'){
    		return $this->changeColumnField();
    	}
    	if($this->getRequestValue('action') == 'save_search_form'){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/search_forms_admin.php';
			$SFA=new search_forms_admin();
			$SFA->save_search_form();
    	}
    	if($this->getRequestValue('action') == 'save_front_grid'){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/front_gridmanager_admin.php';
    		$SFA=new front_gridmanager_admin();
    		$SFA->save_front_grid();
    	}
    	if($this->getRequestValue('action') == 'save_group_rules'){
    		$DBC=DBC::getInstance();
    		$rules=$this->getRequestValue('rule');
    		$query='INSERT INTO '.DB_PREFIX.'_group_rule (group_id, columns_id, view_status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE view_status=?';
    		foreach($rules['view'] as $column_id=>$rule){
    			foreach($rule as $group_id=>$status){
    				$DBC->query($query, array($group_id, $column_id, $status, $status));
    			}
    		}
    		$query='INSERT INTO '.DB_PREFIX.'_group_rule (group_id, columns_id, edit_status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE edit_status=?';
    		foreach($rules['edit'] as $column_id=>$rule){
    			foreach($rule as $group_id=>$status){
    				$DBC->query($query, array($group_id, $column_id, $status, $status));
    			}
    		}
    		print_r($rules);
    	}
    	return false;
    }
    
    function changeColumnField(){
    	$user_id=(int)$_SESSION['user_id_value'];
    	if($user_id!==(int)$this->getAdminUserId()){
    		return json_encode(array('success'=>false, 'msg'=>'error'));
    	}
    	$id=(int)$this->getRequestValue('pk');
    	$field_name=$this->getRequestValue('field_name');
    	$value=SiteBill::iconv('utf-8', SITE_ENCODING, $this->escape($this->getRequestValue('value')));
    	if($id!=0 && $field_name!=''){
    		$query="UPDATE ".DB_PREFIX."_columns SET `".mysql_real_escape_string($field_name)."`='".mysql_real_escape_string($value)."' WHERE columns_id=".$id;
    		//echo $query;
    		$this->db->exec($query);
    		return json_encode(array('success'=>true));
    	}
    	return json_encode(array('success'=>false, 'msg'=>'error'));
    }
	
	function reorder_columns($ids_string){
		//$a='';
		$ids=array();
		$ids_string=trim($ids_string);
		if($ids_string!=''){
			$ids=explode(',',$ids_string);
		}
		if(!empty($ids)){
			foreach($ids as $k=>$id){
				//$a.='UPDATE '.DB_PREFIX.'_columns SET sort_order='.($k+1).' WHERE columns_id='.$id;
				$this->db->exec('UPDATE '.DB_PREFIX.'_columns SET sort_order='.($k+1).' WHERE columns_id='.$id);
			}
		}
		//return $a;
	}
	
	function changeFieldTab($tab_name,$id){
		
		if(get_magic_quotes_gpc()){
			$tab_name=stripslashes($tab_name);
		}
		$tab_name=trim($tab_name);
		$tab_name=SiteBill::iconv('utf-8', SITE_ENCODING, $tab_name);
		if($id!=0){
			$q='UPDATE '.DB_PREFIX.'_columns SET tab=\''.mysql_real_escape_string($tab_name).'\' WHERE columns_id='.$id;
			$this->db->exec($q);
			if($tab_name!=''){
				return $tab_name;
			}else{
				return 'Не указано';
			}
		}
		return '';
	}
	
	function changeColumnState($operation,$id){
		if($id!=0){
			if($operation=='activate'){
				$q='UPDATE '.DB_PREFIX.'_columns SET active=1 WHERE columns_id='.$id;
				$ret='activated';
			}elseif($operation=='deactivate'){
				$q='UPDATE '.DB_PREFIX.'_columns SET active=0 WHERE columns_id='.$id;
				$ret='deactivated';
			}elseif($operation=='required'){
				$q='UPDATE '.DB_PREFIX.'_columns SET required=1 WHERE columns_id='.$id;
				$ret='required';
			}elseif($operation=='derequired'){
				$q='UPDATE '.DB_PREFIX.'_columns SET required=0 WHERE columns_id='.$id;
				$ret='derequired';
			}else{
				$ret='';
			}
			$this->db->exec($q);
		}else{
			$ret='';
		}
		return $ret;
	}
	
	function getTablesNames(){
		$tables=array(''=>'выбрать');
		$this->db->exec('SHOW FULL TABLES');
		while($this->db->fetch_assoc()){
			$x=array_values($this->db->row);
			if($x[1]=='BASE TABLE'){
				$t_name=str_replace(DB_PREFIX.'_', '', $x[0]);
				$tables[$t_name]=$t_name;
			}
			
		}
		return $tables;
	}
	
	function getTableFieldsSelectbox($table){
		$columns=$this->getTableFields($table);
		foreach($columns as $k=>$v){
			$ret.='<option value="'.$k.'">'.$v.'</option>';
		}
		return $ret;
	}
	
	function getTableFields($table){
		$columns=array(''=>'выбрать');
		if($table!=''){
			$this->db->exec('SHOW COLUMNS FROM `'.DB_PREFIX.'_'.$table.'`');
			if($this->db->success){
				while($this->db->fetch_assoc()){
					$columns[$this->db->row['Field']]=$this->db->row['Field'];
				}
			}
		}
		return $columns;
	}
	
	function getFormElementPreview($data){
		$element_data=array();
		$_d=explode('|',$data);
		if(count($_d)>0){
			foreach($_d as $d){
				list($k,$v)=explode(':',$d);
				
				$element_data[$k]=SiteBill::iconv('utf-8', SITE_ENCODING, $v);
				if($k=='select_data'){
					$element_data[$k]=$this->helper->unserializeSelectData($element_data[$k]);
				}
			}
		}
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill_krascap.php';
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php';
		$FG=new Form_Generator();
		echo $FG->compile_form(array('preview'=>$element_data), true);
		//print_r($element_data);
	}
	
	
	
}