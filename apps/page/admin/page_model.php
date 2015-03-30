<?php
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
class Page_Model extends Data_Model {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function get_model(){
		
		$form_data = array();
		
		$form_data['page']['page_id']['name'] = 'page_id';
		$form_data['page']['page_id']['title'] = Multilanguage::_('L_ID');
		$form_data['page']['page_id']['value'] = 0;
		$form_data['page']['page_id']['length'] = 40;
		$form_data['page']['page_id']['type'] = 'primary_key';
		$form_data['page']['page_id']['required'] = 'off';
		$form_data['page']['page_id']['unique'] = 'off';
		
		$form_data['page']['title']['name'] = 'title';
		$form_data['page']['title']['title'] = Multilanguage::_('TITLE','page');
		$form_data['page']['title']['value'] = '';
		$form_data['page']['title']['length'] = 40;
		$form_data['page']['title']['type'] = 'safe_string';
		$form_data['page']['title']['required'] = 'on';
		$form_data['page']['title']['unique'] = 'off';
		
		$form_data['page']['uri']['name'] = 'uri';
		$form_data['page']['uri']['title'] = 'URI';
		$form_data['page']['uri']['value'] = '';
		$form_data['page']['uri']['length'] = 40;
		$form_data['page']['uri']['type'] = 'safe_string';
		$form_data['page']['uri']['required'] = 'on';
		$form_data['page']['uri']['unique'] = 'off';

		$form_data['page']['meta_title']['name'] = 'meta_title';
		$form_data['page']['meta_title']['title'] = Multilanguage::_('META_TITLE','page');
		$form_data['page']['meta_title']['value'] = '';
		$form_data['page']['meta_title']['length'] = 40;
		$form_data['page']['meta_title']['type'] = 'safe_string';
		$form_data['page']['meta_title']['required'] = 'off';
		$form_data['page']['meta_title']['unique'] = 'off';
		
		$form_data['page']['meta_keywords']['name'] = 'meta_keywords';
		$form_data['page']['meta_keywords']['title'] = Multilanguage::_('META_KEYWORDS','page');
		$form_data['page']['meta_keywords']['value'] = '';
		$form_data['page']['meta_keywords']['length'] = 40;
		$form_data['page']['meta_keywords']['type'] = 'safe_string';
		$form_data['page']['meta_keywords']['required'] = 'off';
		$form_data['page']['meta_keywords']['unique'] = 'off';
		
		$form_data['page']['meta_description']['name'] = 'meta_description';
		$form_data['page']['meta_description']['title'] = Multilanguage::_('META_DESCRIPTION','page');
		$form_data['page']['meta_description']['value'] = '';
		$form_data['page']['meta_description']['length'] = 40;
		$form_data['page']['meta_description']['type'] = 'safe_string';
		$form_data['page']['meta_description']['required'] = 'off';
		$form_data['page']['meta_description']['unique'] = 'off';
		
		$form_data['page']['body']['name'] = 'body';
		$form_data['page']['body']['title'] = Multilanguage::_('PAGE_BODY','page');
		$form_data['page']['body']['value'] = '';
		$form_data['page']['body']['type'] = 'textarea_editor';
		$form_data['page']['body']['required'] = 'off';
		$form_data['page']['body']['unique'] = 'off';
		$form_data['page']['body']['rows'] = '10';
		$form_data['page']['body']['cols'] = '40';
		
		$form_data['page']['is_service']['name'] = 'is_service';
		$form_data['page']['is_service']['title'] = 'Служебная';
		$form_data['page']['is_service']['value'] = 0;
		$form_data['page']['is_service']['length'] = 40;
		$form_data['page']['is_service']['type'] = 'checkbox';
		$form_data['page']['is_service']['required'] = 'off';
		$form_data['page']['is_service']['unique'] = 'off';
		
		return $form_data;
	}
}