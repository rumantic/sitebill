<?php
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
class Searchform_Model extends Data_Model {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function get_model(){
		$form_data = array();
		
		$form_data['table_searchform']['searchform_id']['name'] = 'searchform_id';
		$form_data['table_searchform']['searchform_id']['title'] = 'ID';
		$form_data['table_searchform']['searchform_id']['value'] = 0;
		$form_data['table_searchform']['searchform_id']['length'] = 40;
		$form_data['table_searchform']['searchform_id']['type'] = 'primary_key';
		$form_data['table_searchform']['searchform_id']['required'] = 'off';
		$form_data['table_searchform']['searchform_id']['unique'] = 'off';
		
		$form_data['table_searchform']['title']['name'] = 'title';
		$form_data['table_searchform']['title']['title'] = 'Название формы';
		$form_data['table_searchform']['title']['value'] = '';
		$form_data['table_searchform']['title']['length'] = 40;
		$form_data['table_searchform']['title']['type'] = 'safe_string';
		$form_data['table_searchform']['title']['required'] = 'on';
		$form_data['table_searchform']['title']['unique'] = 'off';

		$form_data['table_searchform']['title_en']['name'] = 'title_en';
		$form_data['table_searchform']['title_en']['title'] = 'Название формы (en)';
		$form_data['table_searchform']['title_en']['value'] = '';
		$form_data['table_searchform']['title_en']['length'] = 40;
		$form_data['table_searchform']['title_en']['type'] = 'safe_string';
		$form_data['table_searchform']['title_en']['required'] = 'off';
		$form_data['table_searchform']['title_en']['unique'] = 'off';
		
		
		return $form_data;
	}
}