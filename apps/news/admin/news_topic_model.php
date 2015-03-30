<?php
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
class News_Topic_Model extends Data_Model {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function get_model(){
		$form_data = array();
		
		$form_data['news_topic']['id']['name'] = 'id';
		$form_data['news_topic']['id']['title'] = Multilanguage::_('L_ID');
		$form_data['news_topic']['id']['value'] = 0;
		$form_data['news_topic']['id']['length'] = 40;
		$form_data['news_topic']['id']['type'] = 'primary_key';
		$form_data['news_topic']['id']['required'] = 'off';
		$form_data['news_topic']['id']['unique'] = 'off';
		
		$form_data['news_topic']['name']['name'] = 'name';
		$form_data['news_topic']['name']['title'] = 'Название';
		$form_data['news_topic']['name']['value'] = '';
		$form_data['news_topic']['name']['length'] = 40;
		$form_data['news_topic']['name']['type'] = 'safe_string';
		$form_data['news_topic']['name']['required'] = 'on';
		$form_data['news_topic']['name']['unique'] = 'off';
		
		$form_data['news_topic']['url']['name'] = 'url';
		$form_data['news_topic']['url']['title'] = 'url';
		$form_data['news_topic']['url']['value'] = '';
		$form_data['news_topic']['url']['length'] = 40;
		$form_data['news_topic']['url']['type'] = 'safe_string';
		$form_data['news_topic']['url']['required'] = 'off';
		$form_data['news_topic']['url']['unique'] = 'off';
		
		return $form_data;
	}
}