<?php
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
class Table_Model extends Data_Model {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function get_model(){
		$form_data = array();
		
		$form_data['table']['table_id']['name'] = 'table_id';
		$form_data['table']['table_id']['title'] = 'ID';
		$form_data['table']['table_id']['value'] = 0;
		$form_data['table']['table_id']['length'] = 40;
		$form_data['table']['table_id']['type'] = 'primary_key';
		$form_data['table']['table_id']['required'] = 'off';
		$form_data['table']['table_id']['unique'] = 'off';
		
		$form_data['table']['name']['name'] = 'name';
		$form_data['table']['name']['title'] = 'Название таблицы';
		$form_data['table']['name']['value'] = '';
		$form_data['table']['name']['length'] = 40;
		$form_data['table']['name']['type'] = 'safe_string';
		$form_data['table']['name']['required'] = 'on';
		$form_data['table']['name']['unique'] = 'off';
		
		$form_data['table']['description']['name'] = 'description';
		$form_data['table']['description']['title'] = 'Описание таблицы';
		$form_data['table']['description']['value'] = '';
		$form_data['table']['description']['length'] = 40;
		$form_data['table']['description']['type'] = 'textarea';
		$form_data['table']['description']['required'] = 'off';
		$form_data['table']['description']['unique'] = 'off';
		
		return $form_data;
	}
}