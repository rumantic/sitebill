<?php
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
class Mailbox_Model extends Data_Model {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function get_model(){
		
		$form_data = array();
		
		$form_data['mailbox']['mailbox_id']['name'] = 'mailbox_id';
		$form_data['mailbox']['mailbox_id']['title'] = 'ID';
		$form_data['mailbox']['mailbox_id']['value'] = 0;
		$form_data['mailbox']['mailbox_id']['length'] = 40;
		$form_data['mailbox']['mailbox_id']['type'] = 'primary_key';
		$form_data['mailbox']['mailbox_id']['required'] = 'off';
		$form_data['mailbox']['mailbox_id']['unique'] = 'off';
		
		$form_data['mailbox']['sender_id']['name'] = 'sender_id';
	    $form_data['mailbox']['sender_id']['primary_key_name'] = 'user_id';
	    $form_data['mailbox']['sender_id']['primary_key_table'] = 'user';
	    $form_data['mailbox']['sender_id']['title'] = 'Отправитель';
	    $form_data['mailbox']['sender_id']['value_string'] = '';
	    $form_data['mailbox']['sender_id']['value'] = 0;
	    $form_data['mailbox']['sender_id']['length'] = 40;
	    $form_data['mailbox']['sender_id']['type'] = 'select_by_query';
	    $form_data['mailbox']['sender_id']['query'] = 'select * from '.DB_PREFIX.'_user order by login';
	    $form_data['mailbox']['sender_id']['title_default'] = 'выбрать пользователя';
	    $form_data['mailbox']['sender_id']['value_default'] = 0;
	    $form_data['mailbox']['sender_id']['value_name'] = 'login';
	    $form_data['mailbox']['sender_id']['required'] = 'off';
	    $form_data['mailbox']['sender_id']['unique'] = 'off';
	    
	    $form_data['mailbox']['reciever_id']['name'] = 'reciever_id';
	    $form_data['mailbox']['reciever_id']['primary_key_name'] = 'user_id';
	    $form_data['mailbox']['reciever_id']['primary_key_table'] = 'user';
	    $form_data['mailbox']['reciever_id']['title'] = 'Получатель';
	    $form_data['mailbox']['reciever_id']['value_string'] = '';
	    $form_data['mailbox']['reciever_id']['value'] = 0;
	    $form_data['mailbox']['reciever_id']['length'] = 40;
	    $form_data['mailbox']['reciever_id']['type'] = 'select_by_query';
	    $form_data['mailbox']['reciever_id']['query'] = 'select * from '.DB_PREFIX.'_user order by login';
	    $form_data['mailbox']['reciever_id']['title_default'] = 'выбрать пользователя';
	    $form_data['mailbox']['reciever_id']['value_default'] = 0;
	    $form_data['mailbox']['reciever_id']['value_name'] = 'login';
	    $form_data['mailbox']['reciever_id']['required'] = 'off';
	    $form_data['mailbox']['reciever_id']['unique'] = 'off';
		
		$form_data['mailbox']['theme']['name'] = 'theme';
		$form_data['mailbox']['theme']['title'] = 'Тема';
		$form_data['mailbox']['theme']['value'] = '';
		$form_data['mailbox']['theme']['length'] = 40;
		$form_data['mailbox']['theme']['type'] = 'safe_string';
		$form_data['mailbox']['theme']['required'] = 'off';
		$form_data['mailbox']['theme']['unique'] = 'off';
		
		$form_data['mailbox']['name']['name'] = 'name';
		$form_data['mailbox']['name']['title'] = 'Имя';
		$form_data['mailbox']['name']['value'] = '';
		$form_data['mailbox']['name']['length'] = 40;
		$form_data['mailbox']['name']['type'] = 'safe_string';
		$form_data['mailbox']['name']['required'] = 'off';
		$form_data['mailbox']['name']['unique'] = 'off';
		
		$form_data['mailbox']['phone']['name'] = 'phone';
		$form_data['mailbox']['phone']['title'] = 'телефон';
		$form_data['mailbox']['phone']['value'] = '';
		$form_data['mailbox']['phone']['length'] = 40;
		$form_data['mailbox']['phone']['type'] = 'safe_string';
		$form_data['mailbox']['phone']['required'] = 'off';
		$form_data['mailbox']['phone']['unique'] = 'off';
		
		$form_data['mailbox']['email']['name'] = 'email';
		$form_data['mailbox']['email']['title'] = 'E-mail';
		$form_data['mailbox']['email']['value'] = '';
		$form_data['mailbox']['email']['length'] = 40;
		$form_data['mailbox']['email']['type'] = 'safe_string';
		$form_data['mailbox']['email']['required'] = 'off';
		$form_data['mailbox']['email']['unique'] = 'off';
		
		$form_data['mailbox']['realty_id']['name'] = 'realty_id';
		$form_data['mailbox']['realty_id']['title'] = 'ID объявления';
		$form_data['mailbox']['realty_id']['value'] = '';
		$form_data['mailbox']['realty_id']['length'] = 40;
		$form_data['mailbox']['realty_id']['type'] = 'safe_string';
		$form_data['mailbox']['realty_id']['required'] = 'off';
		$form_data['mailbox']['realty_id']['unique'] = 'off';
		
		$form_data['mailbox']['message']['name'] = 'message';
		$form_data['mailbox']['message']['title'] = 'Сообщение';
		$form_data['mailbox']['message']['value'] = '';
		$form_data['mailbox']['message']['length'] = 40;
		$form_data['mailbox']['message']['type'] = 'textarea';
		$form_data['mailbox']['message']['required'] = 'off';
		$form_data['mailbox']['message']['unique'] = 'off';
		$form_data['mailbox']['message']['rows'] = '10';
		$form_data['mailbox']['message']['cols'] = '40';
		
		$form_data['mailbox']['creation_date']['name'] = 'creation_date';
		$form_data['mailbox']['creation_date']['title'] = 'Дата';
		$form_data['mailbox']['creation_date']['value'] = '';
		$form_data['mailbox']['creation_date']['length'] = 40;
		$form_data['mailbox']['creation_date']['type'] = 'safe_string';
		$form_data['mailbox']['creation_date']['required'] = 'off';
		$form_data['mailbox']['creation_date']['unique'] = 'off';
		
		return $form_data;
	}
}