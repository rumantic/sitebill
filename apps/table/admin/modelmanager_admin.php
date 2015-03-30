<?php
class modelmanager_admin extends table_admin {
	private $ignoredTables=array('table', 'columns', 'entity');
	
	public function __construct(){
		parent::__construct();
	}
	
	
	
	function main () {
		$rs = $this->getTopMenu();
		
		$do=$this->getRequestValue('do');
		$t=$this->getRequestValue('t');
		
		if($do=='createmodel' && $t!=''){
			$this->createModel($t);
		}
		
		$rs .= $this->getList();
		
		
		$rs_new = $this->get_app_title_bar();
		$rs_new .= $rs;
		return $rs_new;
	}
	
	private function getList(){
		
		$tables=$this->loadAllTables();
		$models=$this->loadAllModels();
		
		$model_columns=array();
		foreach($models as $model_name=>$model_column){
			$model_columns[$model_name]['table']=$model_name;
			foreach($model_column as $r){
				//$x=array();
				if($r['type']=='primary_key'){
					$model_columns[$model_name]['id']=array('c'=>$r['name'], 't'=>$r['type']);
				}else{
					if($r['type']=='select_by_query'){
						$data_type='sourced';
						$possible_sourced_entity='primary_key_table';
					}elseif($r['type']=='select_box'){
						$data_type='sourced';
					}else{
						$data_type='';
					}
					$model_columns[$model_name]['fields'][]=array('c'=>$r['name'], 't'=>$r['type'], 'data_type'=>$data_type);
				}
				//$entity_columns[$entity_names[2]]=$x
			}
		}
		global $smarty;
		$smarty->assign('entities_list', $this->loadTables());
		$smarty->assign('models_list', $model_columns);
		$smarty->assign('model_tables_list', $tables);
		$rs=$smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/entity/admin/template/entities_list.tpl');
		 
		return $rs;
	}
	
	
	private function loadTables($table=''){
		
		$DBC=DBC::getInstance();
		$query='SHOW FULL TABLES';
		$stmt=$DBC->query($query);
		while($ar=$DBC->fetch($stmt)){
			$ret[]=$ar;
		}
		
		foreach($ret as $r){
			$z=array_values($r);
			$table_name=preg_replace('/^'.DB_PREFIX.'_/', '', $z[0]);
			
			if(!in_array($table_name, $this->ignoredTables)){
				if($table!=''){
					if($table==$table_name){
						$entity_names[]=$table_name;
					}
				}else{
					$entity_names[]=$table_name;
				}
				
			}
				
		}
		$columns=array();
		foreach($entity_names as $en){
			$query='SHOW COLUMNS FROM `'.DB_PREFIX.'_'.$en.'`';
				
				
			$stmt=$DBC->query($query);
			while($ar=$DBC->fetch($stmt)){
				$columns[$en][]=$ar;
			}
		}
		
		
		
		$entity_columns=array();
		foreach($columns as $entity_name=>$entity_column){
			$entity_columns[$entity_name]['table']=$entity_name;
			foreach($entity_column as $r){
				//$x=array();
				if($r['Key']=='PRI'){
					$entity_columns[$entity_name]['id']=array('c'=>$r['Field'], 't'=>$r['Type']);
				}else{
					$entity_columns[$entity_name]['fields'][]=array('c'=>$r['Field'], 't'=>$r['Type']);
				}
				//$entity_columns[$entity_names[2]]=$x
			}
		}
		return $entity_columns;
		//print_r($entity_columns);
	}
	
	private function createModel($table){
		$a=$this->loadTables($table);
		//print_r($a);
		
		$model[$table][$a[$table]['id']['c']]['name'] = $a[$table]['id']['c'];
		$model[$table][$a[$table]['id']['c']]['title'] = $a[$table]['id']['c'];
		$model[$table][$a[$table]['id']['c']]['active'] = 1;
		$model[$table][$a[$table]['id']['c']]['type'] = 'primary_key';
		
		foreach($a[$table]['fields'] as $field){
			$model[$table][$field['c']]['name'] = $field['c'];
			$model[$table][$field['c']]['title'] = $field['c'];
			$model[$table][$field['c']]['active'] = 1;
			$model[$table][$field['c']]['type'] = 'safe_string';
		}
		
		
		$this->create_table_and_columns($model, $table);
		//print_r($model);
		
	}
	
	private function loadAllModels(){
		$models=array();
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
		$Helper=new Admin_Table_Helper();
		$tables=$this->loadAllTables();
		foreach ($tables as $k=>$value) {
			$m=$Helper->load_model($k, true, true);
			$models[$k]=$m[$k];
		}
		return $models;
	}
	
	private function loadAllTables(){
		$tables=array();
		$DBC=DBC::getInstance();
		$query = "SELECT * FROM ".DB_PREFIX."_table ORDER BY table_id DESC";
		$stmt=$DBC->query($query);
		while($ar=$DBC->fetch($stmt)){
			$tables[$ar['name']]=$ar;
		}
		return $tables;
	}
}