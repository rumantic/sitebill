<?php
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
class Currency_Model extends Data_Model {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function get_model(){
		
		$form_data = array();
		
		$form_data['currency']['currency_id']['name'] = 'currency_id';
		$form_data['currency']['currency_id']['title'] = Multilanguage::_('L_ID');
		$form_data['currency']['currency_id']['value'] = 0;
		$form_data['currency']['currency_id']['length'] = 40;
		$form_data['currency']['currency_id']['type'] = 'primary_key';
		$form_data['currency']['currency_id']['required'] = 'off';
		$form_data['currency']['currency_id']['unique'] = 'off';
		
		$form_data['currency']['code']['name'] = 'code';
		$form_data['currency']['code']['title'] = Multilanguage::_('L_CODE');
		$form_data['currency']['code']['value'] = '';
		$form_data['currency']['code']['length'] = 40;
		$form_data['currency']['code']['type'] = 'safe_string';
		$form_data['currency']['code']['required'] = 'on';
		$form_data['currency']['code']['unique'] = 'off';
		
		$form_data['currency']['name']['name'] = 'name';
		$form_data['currency']['name']['title'] = Multilanguage::_('L_TITLE');
		$form_data['currency']['name']['value'] = '';
		$form_data['currency']['name']['length'] = 40;
		$form_data['currency']['name']['type'] = 'safe_string';
		$form_data['currency']['name']['required'] = 'on';
		$form_data['currency']['name']['unique'] = 'off';
		
		$form_data['currency']['sort_order']['name'] = 'sort_order';
		$form_data['currency']['sort_order']['title'] = Multilanguage::_('L_ORDER');
		$form_data['currency']['sort_order']['value'] = '';
		$form_data['currency']['sort_order']['length'] = 40;
		$form_data['currency']['sort_order']['type'] = 'safe_string';
		$form_data['currency']['sort_order']['required'] = 'on';
		$form_data['currency']['sort_order']['unique'] = 'off';
		
		$form_data['currency']['course']['name'] = 'course';
		$form_data['currency']['course']['title'] = Multilanguage::_('L_UE_COURSE');
		$form_data['currency']['course']['value'] = '';
		$form_data['currency']['course']['length'] = 40;
		$form_data['currency']['course']['type'] = 'safe_string';
		$form_data['currency']['course']['required'] = 'on';
		$form_data['currency']['course']['unique'] = 'off';
				
		$form_data['currency']['is_default']['name'] = 'is_default';
		$form_data['currency']['is_default']['title'] = Multilanguage::_('L_DEFAULT');
		$form_data['currency']['is_default']['value'] = 0;
		$form_data['currency']['is_default']['length'] = 40;
		$form_data['currency']['is_default']['type'] = 'checkbox';
		$form_data['currency']['is_default']['required'] = 'off';
		$form_data['currency']['is_default']['unique'] = 'off';
		
		$form_data['currency']['is_active']['name'] = 'is_active';
		$form_data['currency']['is_active']['title'] = Multilanguage::_('L_ACTIVE');
		$form_data['currency']['is_active']['value'] = 0;
		$form_data['currency']['is_active']['length'] = 40;
		$form_data['currency']['is_active']['type'] = 'checkbox';
		$form_data['currency']['is_active']['required'] = 'off';
		$form_data['currency']['is_active']['unique'] = 'off';
		
		return $form_data;
	}
}