<?php
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
class Front_Grid_Model extends Data_Model {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function get_model(){
		$form_data = array();
		
		$form_data['table_frontgrid']['frontgrid_id']['name'] = 'searchform_id';
		$form_data['table_frontgrid']['frontgrid_id']['title'] = 'ID';
		$form_data['table_frontgrid']['frontgrid_id']['value'] = 0;
		$form_data['table_frontgrid']['frontgrid_id']['length'] = 40;
		$form_data['table_frontgrid']['frontgrid_id']['type'] = 'primary_key';
		$form_data['table_frontgrid']['frontgrid_id']['required'] = 'off';
		$form_data['table_frontgrid']['frontgrid_id']['unique'] = 'off';
		
		$form_data['table_frontgrid']['title']['name'] = 'title';
		$form_data['table_frontgrid']['title']['title'] = 'Название сетки';
		$form_data['table_frontgrid']['title']['value'] = '';
		$form_data['table_frontgrid']['title']['length'] = 40;
		$form_data['table_frontgrid']['title']['type'] = 'safe_string';
		$form_data['table_frontgrid']['title']['required'] = 'on';
		$form_data['table_frontgrid']['title']['unique'] = 'off';
		
		return $form_data;
	}
}