<?php
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
class Comment_Model extends Data_Model {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function get_model(){
		
		$form_data = array();
		
		$form_data['comment']['comment_id']['name'] = 'comment_id';
		$form_data['comment']['comment_id']['title'] = 'ID';
		$form_data['comment']['comment_id']['value'] = 0;
		$form_data['comment']['comment_id']['length'] = 40;
		$form_data['comment']['comment_id']['type'] = 'primary_key';
		$form_data['comment']['comment_id']['required'] = 'off';
		$form_data['comment']['comment_id']['unique'] = 'off';
		
		$form_data['comment']['user_id']['name'] = 'user_id';
	    $form_data['comment']['user_id']['primary_key_name'] = 'user_id';
	    $form_data['comment']['user_id']['primary_key_table'] = 'user';
	    $form_data['comment']['user_id']['title'] = 'Отправитель';
	    $form_data['comment']['user_id']['value_string'] = '';
	    $form_data['comment']['user_id']['value'] = 0;
	    $form_data['comment']['user_id']['length'] = 40;
	    $form_data['comment']['user_id']['type'] = 'select_by_query';
	    $form_data['comment']['user_id']['query'] = 'select * from '.DB_PREFIX.'_user order by login';
	    $form_data['comment']['user_id']['title_default'] = 'выбрать пользователя';
	    $form_data['comment']['user_id']['value_default'] = 0;
	    $form_data['comment']['user_id']['value_name'] = 'login';
	    $form_data['comment']['user_id']['required'] = 'on';
	    $form_data['comment']['user_id']['unique'] = 'off';
	    
	    $form_data['comment']['comment_text']['name'] = 'comment_text';
	    $form_data['comment']['comment_text']['title'] = 'Сообщение';
	    $form_data['comment']['comment_text']['value'] = '';
	    $form_data['comment']['comment_text']['length'] = 40;
	    $form_data['comment']['comment_text']['type'] = 'textarea';
	    $form_data['comment']['comment_text']['required'] = 'on';
	    $form_data['comment']['comment_text']['unique'] = 'off';
	    $form_data['comment']['comment_text']['rows'] = '10';
	    $form_data['comment']['comment_text']['cols'] = '40';
	    
	    $form_data['comment']['comment_date']['name'] = 'comment_date';
	    $form_data['comment']['comment_date']['title'] = 'Дата';
	    $form_data['comment']['comment_date']['value'] = '';
	    $form_data['comment']['comment_date']['length'] = 40;
	    $form_data['comment']['comment_date']['type'] = 'safe_string';
	    $form_data['comment']['comment_date']['required'] = 'off';
	    $form_data['comment']['comment_date']['unique'] = 'off';
	    
	    $form_data['comment']['parent_comment_id']['name'] = 'parent_comment_id';
	    $form_data['comment']['parent_comment_id']['title'] = 'Родительский комментарий';
	    $form_data['comment']['parent_comment_id']['value'] = 0;
	    $form_data['comment']['parent_comment_id']['length'] = 40;
	    $form_data['comment']['parent_comment_id']['type'] = 'safe_string';
	    $form_data['comment']['parent_comment_id']['required'] = 'off';
	    $form_data['comment']['parent_comment_id']['unique'] = 'off';
	    
	    $form_data['comment']['object_type']['name'] = 'object_type';
	    $form_data['comment']['object_type']['title'] = 'object_type';
	    $form_data['comment']['object_type']['value'] = 0;
	    $form_data['comment']['object_type']['length'] = 40;
	    $form_data['comment']['object_type']['type'] = 'safe_string';
	    $form_data['comment']['object_type']['required'] = 'on';
	    $form_data['comment']['object_type']['unique'] = 'off';
	    
	    $form_data['comment']['object_id']['name'] = 'object_id';
	    $form_data['comment']['object_id']['title'] = 'object_id';
	    $form_data['comment']['object_id']['value'] = 0;
	    $form_data['comment']['object_id']['length'] = 40;
	    $form_data['comment']['object_id']['type'] = 'safe_string';
	    $form_data['comment']['object_id']['required'] = 'on';
	    $form_data['comment']['object_id']['unique'] = 'off';
	    
	    $form_data['comment']['is_published']['name'] = 'is_published';
	    $form_data['comment']['is_published']['title'] = 'Опубликовано?';
	    $form_data['comment']['is_published']['value'] = 1;
	    $form_data['comment']['is_published']['type'] = 'checkbox';
	    $form_data['comment']['is_published']['required'] = 'off';
	    $form_data['comment']['is_published']['unique'] = 'off';
	    
	   	return $form_data;
	}
}