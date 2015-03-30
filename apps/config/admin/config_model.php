<?php
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
class Config_Model extends Data_Model {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function get_model(){
		
		$form_data = array();
		
		$form_data['config']['id']['name'] = 'id';
		$form_data['config']['id']['title'] = Multilanguage::_('L_ID');
		$form_data['config']['id']['value'] = 0;
		$form_data['config']['id']['length'] = 40;
		$form_data['config']['id']['type'] = 'primary_key';
		$form_data['config']['id']['required'] = 'off';
		$form_data['config']['id']['unique'] = 'off';
		
		$form_data['config']['config_key']['name'] = 'config_key';
		$form_data['config']['config_key']['title'] = Multilanguage::_('L_PARAMETER');
		$form_data['config']['config_key']['value'] = '';
		$form_data['config']['config_key']['length'] = 40;
		$form_data['config']['config_key']['type'] = 'safe_string';
		$form_data['config']['config_key']['required'] = 'on';
		$form_data['config']['config_key']['unique'] = 'off';
		
		$form_data['config']['value']['name'] = 'value';
		$form_data['config']['value']['title'] = Multilanguage::_('L_VALUE');
		$form_data['config']['value']['value'] = '';
		$form_data['config']['value']['length'] = 40;
		$form_data['config']['value']['type'] = 'safe_string';
		$form_data['config']['value']['required'] = 'on';
		$form_data['config']['value']['unique'] = 'off';
		
		$form_data['config']['title']['name'] = 'title';
		$form_data['config']['title']['title'] = Multilanguage::_('L_DESCRIPTION');
		$form_data['config']['title']['value'] = '';
		$form_data['config']['title']['length'] = 40;
		$form_data['config']['title']['type'] = 'safe_string';
		$form_data['config']['title']['required'] = 'on';
		$form_data['config']['title']['unique'] = 'off';
		
		return $form_data;
	}
}