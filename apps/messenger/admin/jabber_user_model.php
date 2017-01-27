<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php';

class Jabber_User_Model extends Data_Model {

    public function __construct() {
	parent::__construct();
    }

    public function get_model() {

	$form_data = array();

	$form_data['user']['user_id']['name'] = 'user_id';
	$form_data['user']['user_id']['title'] = 'ID';
	$form_data['user']['user_id']['value'] = 0;
	$form_data['user']['user_id']['length'] = 40;
	$form_data['user']['user_id']['type'] = 'primary_key';
	$form_data['user']['user_id']['required'] = 'off';
	$form_data['user']['user_id']['unique'] = 'off';

	$form_data['user']['jabber_id']['name'] = 'jabber_id';
	$form_data['user']['jabber_id']['title'] = 'Логин';
	$form_data['user']['jabber_id']['value'] = '';
	$form_data['user']['jabber_id']['length'] = 40;
	$form_data['user']['jabber_id']['type'] = 'safe_string';
	$form_data['user']['jabber_id']['required'] = 'on';
	$form_data['user']['jabber_id']['unique'] = 'off';

	$form_data['user']['jabber_password']['name'] = 'jabber_password';
	$form_data['user']['jabber_password']['title'] = 'Старый пароль';
	$form_data['user']['jabber_password']['value'] = '';
	$form_data['user']['jabber_password']['length'] = 40;
	$form_data['user']['jabber_password']['type'] = 'password';
	$form_data['user']['jabber_password']['required'] = 'on';
	$form_data['user']['jabber_password']['unique'] = 'off';

	$form_data['user']['jabber_new_password']['name'] = 'jabber_new_password';
	$form_data['user']['jabber_new_password']['title'] = 'Новый пароль';
	$form_data['user']['jabber_new_password']['value'] = '';
	$form_data['user']['jabber_new_password']['length'] = 40;
	$form_data['user']['jabber_new_password']['type'] = 'password';
	$form_data['user']['jabber_new_password']['required'] = 'on';
	$form_data['user']['jabber_new_password']['unique'] = 'off';

	$form_data['user']['jabber_new_password_a']['name'] = 'jabber_new_password_a';
	$form_data['user']['jabber_new_password_a']['title'] = 'Новый пароль еще раз';
	$form_data['user']['jabber_new_password_a']['value'] = '';
	$form_data['user']['jabber_new_password_a']['length'] = 40;
	$form_data['user']['jabber_new_password_a']['type'] = 'password';
	$form_data['user']['jabber_new_password_a']['required'] = 'on';
	$form_data['user']['jabber_new_password_a']['unique'] = 'off';
	
	return $form_data;
    }

}
