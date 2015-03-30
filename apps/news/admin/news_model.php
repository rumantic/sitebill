<?php
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
class News_Model extends Data_Model {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function get_model(){
		$form_data = array();
		
		$form_data['news']['news_id']['name'] = 'news_id';
		$form_data['news']['news_id']['title'] = Multilanguage::_('L_ID');
		$form_data['news']['news_id']['value'] = 0;
		$form_data['news']['news_id']['length'] = 40;
		$form_data['news']['news_id']['type'] = 'primary_key';
		$form_data['news']['news_id']['required'] = 'off';
		$form_data['news']['news_id']['unique'] = 'off';
		
		
		
		
		$form_data['news']['title']['name'] = 'title';
		$form_data['news']['title']['title'] = Multilanguage::_('TITLE','news');
		$form_data['news']['title']['value'] = '';
		$form_data['news']['title']['length'] = 40;
		$form_data['news']['title']['type'] = 'safe_string';
		$form_data['news']['title']['required'] = 'on';
		$form_data['news']['title']['unique'] = 'off';
		
		
	
		/*
		$form_data['news']['meta_h1']['name'] = 'meta_h1';
		$form_data['news']['meta_h1']['title'] = 'H1';
		$form_data['news']['meta_h1']['value'] = '';
		$form_data['news']['meta_h1']['length'] = 40;
		$form_data['news']['meta_h1']['type'] = 'safe_string';
		$form_data['news']['meta_h1']['required'] = 'off';
		$form_data['news']['meta_h1']['unique'] = 'off';
		$form_data['news']['meta_h1']['tab'] = 'Мета теги';
		*/
		$form_data['news']['meta_title']['name'] = 'meta_title';
		$form_data['news']['meta_title']['title'] = 'META TITLE';
		$form_data['news']['meta_title']['value'] = '';
		$form_data['news']['meta_title']['length'] = 40;
		$form_data['news']['meta_title']['type'] = 'safe_string';
		$form_data['news']['meta_title']['required'] = 'off';
		$form_data['news']['meta_title']['unique'] = 'off';
		$form_data['news']['meta_title']['tab'] = 'Мета теги';
		
		$form_data['news']['meta_description']['name'] = 'meta_description';
		$form_data['news']['meta_description']['title'] = 'META DESCRIPTION';
		$form_data['news']['meta_description']['value'] = '';
		$form_data['news']['meta_description']['length'] = 40;
		$form_data['news']['meta_description']['type'] = 'textarea';
		$form_data['news']['meta_description']['required'] = 'off';
		$form_data['news']['meta_description']['unique'] = 'off';
		$form_data['news']['meta_description']['rows'] = '10';
		$form_data['news']['meta_description']['cols'] = '40';
		$form_data['news']['meta_description']['tab'] = 'Мета теги';
		
		$form_data['news']['meta_keywords']['name'] = 'meta_keywords';
		$form_data['news']['meta_keywords']['title'] = 'META KEYWORDS';
		$form_data['news']['meta_keywords']['value'] = '';
		$form_data['news']['meta_keywords']['length'] = 40;
		$form_data['news']['meta_keywords']['type'] = 'textarea';
		$form_data['news']['meta_keywords']['required'] = 'off';
		$form_data['news']['meta_keywords']['unique'] = 'off';
		$form_data['news']['meta_keywords']['rows'] = '10';
		$form_data['news']['meta_keywords']['cols'] = '40';
		$form_data['news']['meta_keywords']['tab'] = 'Мета теги';
		
		$form_data['news']['newsalias']['name'] = 'newsalias';
		$form_data['news']['newsalias']['title'] = 'Алиас';
		$form_data['news']['newsalias']['value'] = '';
		$form_data['news']['newsalias']['length'] = 40;
		$form_data['news']['newsalias']['type'] = 'safe_string';
		$form_data['news']['newsalias']['required'] = 'off';
		$form_data['news']['newsalias']['unique'] = 'off';
		$form_data['news']['newsalias']['tab'] = 'Мета теги';
		
		if(1==$this->getConfigValue('apps.news.use_news_topics')){
			$form_data['news']['news_topic_id']['name'] = 'news_topic_id';
			$form_data['news']['news_topic_id']['title'] = 'Категория';
			$form_data['news']['news_topic_id']['value'] = 0;
			$form_data['news']['news_topic_id']['length'] = 40;
			$form_data['news']['news_topic_id']['type'] = 'select_by_query';
			$form_data['news']['news_topic_id']['query'] = 'SELECT * FROM '.DB_PREFIX.'_news_topic ORDER BY name';
			$form_data['news']['news_topic_id']['value_name'] = 'name';
			$form_data['news']['news_topic_id']['primary_key_name'] = 'id';
			$form_data['news']['news_topic_id']['primary_key_table'] = 'news_topic';
			$form_data['news']['news_topic_id']['title_default'] = 'выбрать категорию';
			$form_data['news']['news_topic_id']['value_default'] = 0;
			$form_data['news']['news_topic_id']['required'] = 'off';
			$form_data['news']['news_topic_id']['unique'] = 'off';
		}
		
		$form_data['news']['anons']['name'] = 'anons';
		$form_data['news']['anons']['title'] = Multilanguage::_('ANONS','news');
		$form_data['news']['anons']['value'] = '';
		$form_data['news']['anons']['length'] = 40;
		$form_data['news']['anons']['type'] = 'textarea_editor';
		$form_data['news']['anons']['required'] = 'off';
		$form_data['news']['anons']['unique'] = 'off';
		$form_data['news']['anons']['rows'] = '10';
		$form_data['news']['anons']['cols'] = '40';
		
		$form_data['news']['description']['name'] = 'description';
		$form_data['news']['description']['title'] = Multilanguage::_('DESCRIPTION','news');
		$form_data['news']['description']['value'] = '';
		$form_data['news']['description']['length'] = 40;
		$form_data['news']['description']['type'] = 'textarea_editor';
		$form_data['news']['description']['required'] = 'off';
		$form_data['news']['description']['unique'] = 'off';
		$form_data['news']['description']['rows'] = '10';
		$form_data['news']['description']['cols'] = '40';
		
		$form_data['news']['date']['name'] = 'date';
		$form_data['news']['date']['title'] = Multilanguage::_('DATE','news');
		$form_data['news']['date']['value'] = '';
		$form_data['news']['date']['length'] = 40;
		$form_data['news']['date']['date_format'] = 'd.m.Y';
		$form_data['news']['date']['type'] = 'date';
		$form_data['news']['date']['required'] = 'on';
		$form_data['news']['date']['unique'] = 'off';
		
		$form_data['news']['image']['name'] = 'image';
		$form_data['news']['image']['table_name'] = 'news';
		$form_data['news']['image']['primary_key'] = 'news_id';
		$form_data['news']['image']['primary_key_value'] = 0;
		$form_data['news']['image']['action'] = 'news';
		$form_data['news']['image']['title'] = 'Фото';
		$form_data['news']['image']['value'] = '';
		$form_data['news']['image']['length'] = 40;
		$form_data['news']['image']['type'] = 'uploadify_image';
		$form_data['news']['image']['required'] = 'off';
		$form_data['news']['image']['unique'] = 'off';
		
		return $form_data;
	}
}