<?php
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
class Realtylogv2_Model extends Data_Model {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function get_model(){
		
		$form_data['realtylogv2']['id']['name'] = 'id';
		$form_data['realtylogv2']['id']['title'] = 'Идентификатор';
		$form_data['realtylogv2']['id']['value'] = 0;
		$form_data['realtylogv2']['id']['length'] = 40;
		$form_data['realtylogv2']['id']['type'] = 'safe_string';
		$form_data['realtylogv2']['id']['required'] = 'off';
		$form_data['realtylogv2']['id']['unique'] = 'off';
		
		$form_data['realtylogv2']['realtylog_id']['name'] = 'realtylog_id';
		$form_data['realtylogv2']['realtylog_id']['title'] = 'Идентификатор лога';
		$form_data['realtylogv2']['realtylog_id']['value'] = 0;
		$form_data['realtylogv2']['realtylog_id']['length'] = 40;
		$form_data['realtylogv2']['realtylog_id']['type'] = 'primary_key';
		$form_data['realtylogv2']['realtylog_id']['required'] = 'off';
		$form_data['realtylogv2']['realtylog_id']['unique'] = 'off';
		
		$form_data['realtylogv2']['editor_id']['name'] = 'editor_id';
		$form_data['realtylogv2']['editor_id']['primary_key_name'] = 'user_id';
		$form_data['realtylogv2']['editor_id']['primary_key_table'] = 'user';
		$form_data['realtylogv2']['editor_id']['title'] = 'Редактор';
		$form_data['realtylogv2']['editor_id']['value_string'] = '';
		$form_data['realtylogv2']['editor_id']['value'] = '';
		$form_data['realtylogv2']['editor_id']['length'] = 40;
		$form_data['realtylogv2']['editor_id']['type'] = 'select_by_query';
		$form_data['realtylogv2']['editor_id']['query'] = 'select * from '.DB_PREFIX.'_user order by login';
		$form_data['realtylogv2']['editor_id']['value_name'] = 'login';
		$form_data['realtylogv2']['editor_id']['title_default'] = 'выбрать пользователя';
		$form_data['realtylogv2']['editor_id']['value_default'] = 0;
		$form_data['realtylogv2']['editor_id']['required'] = 'on';
		$form_data['realtylogv2']['editor_id']['unique'] = 'off';
		
		$form_data['realtylogv2']['log_data']['name'] = 'log_data';
		$form_data['realtylogv2']['log_data']['title'] = 'Данные';
		$form_data['realtylogv2']['log_data']['value'] = '';
		$form_data['realtylogv2']['log_data']['length'] = 40;
		$form_data['realtylogv2']['log_data']['type'] = 'textarea';
		$form_data['realtylogv2']['log_data']['required'] = 'off';
		$form_data['realtylogv2']['log_data']['unique'] = 'off';
		$form_data['realtylogv2']['log_data']['rows'] = '10';
		$form_data['realtylogv2']['log_data']['cols'] = '40';
		
		$form_data['realtylogv2']['action']['name'] = 'action';
		$form_data['realtylogv2']['action']['title'] = 'Действие';
		$form_data['realtylogv2']['action']['value'] = '';
		$form_data['realtylogv2']['action']['length'] = 40;
		$form_data['realtylogv2']['action']['type'] = 'select_box';
		$form_data['realtylogv2']['action']['select_data'] = array('edit'=>'Правка','delete'=>'Удаление','new'=>'Создание');
		$form_data['realtylogv2']['action']['required'] = 'off';
		$form_data['realtylogv2']['action']['unique'] = 'off';
		
		$form_data['realtylogv2']['log_date']['name'] = 'log_date';
		$form_data['realtylogv2']['log_date']['title'] = 'Дата';
		$form_data['realtylogv2']['log_date']['value'] = '';
		$form_data['realtylogv2']['log_date']['length'] = 40;
		$form_data['realtylogv2']['log_date']['type'] = 'safe_string';
		$form_data['realtylogv2']['log_date']['required'] = 'off';
		$form_data['realtylogv2']['log_date']['unique'] = 'off';
		
		return $form_data;
	}
}