<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
class Client_Model extends Data_Model {
	
	private $_model_table='client';
	
	public function __construct(){
		parent::__construct();
	}
	
	public function get_model(){
	    $form_data = array();
		
		$form_data[$this->_model_table]['client_id']['name'] = 'client_id';
		$form_data[$this->_model_table]['client_id']['title'] = 'ID';
		$form_data[$this->_model_table]['client_id']['value'] = 0;
		$form_data[$this->_model_table]['client_id']['length'] = 40;
		$form_data[$this->_model_table]['client_id']['type'] = 'primary_key';
		$form_data[$this->_model_table]['client_id']['required'] = 'off';
		$form_data[$this->_model_table]['client_id']['unique'] = 'off';
		
		$form_data[$this->_model_table]['date']['name'] = 'date';
		$form_data[$this->_model_table]['date']['title'] = 'Дата поступления заявки';
		$form_data[$this->_model_table]['date']['value'] = time();
		$form_data[$this->_model_table]['date']['length'] = 40;
		$form_data[$this->_model_table]['date']['date_format'] = 'd.m.Y';
		$form_data[$this->_model_table]['date']['type'] = 'date';
		$form_data[$this->_model_table]['date']['required'] = 'on';
		$form_data[$this->_model_table]['date']['unique'] = 'off';
		
		$form_data[$this->_model_table]['type_id']['name'] = 'type_id';
		$form_data[$this->_model_table]['type_id']['title'] = 'Тип заявки';
		$form_data[$this->_model_table]['type_id']['value'] = '';
		$form_data[$this->_model_table]['type_id']['length'] = 40;
		$form_data[$this->_model_table]['type_id']['type'] = 'select_box';
		$form_data[$this->_model_table]['type_id']['select_data'] = array('usual' => 'обычная', 'ipoteka' => 'ипотека', 'rent' => 'снять квартиру', 'sell' => 'продать квартиру', 'contact' => 'контакт');
		$form_data[$this->_model_table]['type_id']['required'] = 'off';
		$form_data[$this->_model_table]['type_id']['unique'] = 'off';
		
		$form_data[$this->_model_table]['status_id']['name'] = 'status_id';
		$form_data[$this->_model_table]['status_id']['title'] = 'Статус заявки';
		$form_data[$this->_model_table]['status_id']['value'] = '';
		$form_data[$this->_model_table]['status_id']['length'] = 40;
		$form_data[$this->_model_table]['status_id']['type'] = 'select_box';
		$form_data[$this->_model_table]['status_id']['select_data'] = array('new' => 'Новая', 'inprogress' => 'В обработке', 'complete' => 'Выполнена', 'cancel' => 'Отменена', 'black' => 'Черный список');
		$form_data[$this->_model_table]['status_id']['required'] = 'off';
		$form_data[$this->_model_table]['status_id']['unique'] = 'off';
		
		$form_data[$this->_model_table]['fio']['name'] = 'fio';
		$form_data[$this->_model_table]['fio']['title'] = 'ФИО';
		$form_data[$this->_model_table]['fio']['value'] = '';
		$form_data[$this->_model_table]['fio']['length'] = 40;
		$form_data[$this->_model_table]['fio']['type'] = 'safe_string';
		$form_data[$this->_model_table]['fio']['required'] = 'off';
		$form_data[$this->_model_table]['fio']['unique'] = 'off';
		
		$form_data[$this->_model_table]['phone']['name'] = 'phone';
		$form_data[$this->_model_table]['phone']['title'] = 'Телефон';
		$form_data[$this->_model_table]['phone']['value'] = '';
		$form_data[$this->_model_table]['phone']['length'] = 40;
		$form_data[$this->_model_table]['phone']['type'] = 'safe_string';
		$form_data[$this->_model_table]['phone']['required'] = 'off';
		$form_data[$this->_model_table]['phone']['unique'] = 'off';
		
		$form_data[$this->_model_table]['email']['name'] = 'email';
		$form_data[$this->_model_table]['email']['title'] = 'E-mail';
		$form_data[$this->_model_table]['email']['value'] = '';
		$form_data[$this->_model_table]['email']['length'] = 40;
		$form_data[$this->_model_table]['email']['type'] = 'safe_string';
		$form_data[$this->_model_table]['email']['required'] = 'off';
		$form_data[$this->_model_table]['email']['unique'] = 'off';
		
		$form_data[$this->_model_table]['address']['name'] = 'address';
		$form_data[$this->_model_table]['address']['title'] = 'Адрес';
		$form_data[$this->_model_table]['address']['value'] = '';
		$form_data[$this->_model_table]['address']['length'] = 40;
		$form_data[$this->_model_table]['address']['type'] = 'safe_string';
		$form_data[$this->_model_table]['address']['required'] = 'off';
		$form_data[$this->_model_table]['address']['unique'] = 'off';
		
		$form_data[$this->_model_table]['order_text']['name'] = 'order_text';
		$form_data[$this->_model_table]['order_text']['title'] = 'Текст заявки';
		$form_data[$this->_model_table]['order_text']['value'] = '';
		$form_data[$this->_model_table]['order_text']['length'] = 40;
		$form_data[$this->_model_table]['order_text']['type'] = 'textarea_editor';
		$form_data[$this->_model_table]['order_text']['required'] = 'off';
		$form_data[$this->_model_table]['order_text']['unique'] = 'off';
		$form_data[$this->_model_table]['order_text']['rows'] = '10';
		$form_data[$this->_model_table]['order_text']['cols'] = '40';
		
	    
	   	return $form_data;
	}
}