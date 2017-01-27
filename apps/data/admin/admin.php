<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

/**
 * data admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class data_admin extends Object_Manager {

    public $save_url = 'empty';

    /**
     * Constructor
     */
    function __construct($realty_type = false) {
	$this->SiteBill();
	Multilanguage::appendAppDictionary('data');
	$this->table_name = 'data';
	$this->action = 'data';
	$this->primary_key = 'id';

	require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
	$data_model = new Data_Model();
	$this->data_model_object = $data_model;
	$this->data_model = $data_model->get_kvartira_model($this->getConfigValue('ajax_form_in_admin'));
	$this->data_model[$this->table_name]['user_id']['type'] = 'hidden';
	$this->data_model[$this->table_name]['user_id']['value'] = $this->getSessionUserId();
    }

    public function _preload() {
	
    }

    function grid() {
	$REQUESTURIPATH = Sitebill::getClearRequestURI();
	if ( $this->getConfigValue('apps.pdfreport.enabled') ) {
	    $this->template->assign('pdf_enable', 1);
	}
	//Устанавливаем параметр USER_ID для функции импорта XLS файла. 
	//Чтобы при загрузке из XLS пользоатель не смог получить доступ к чужим записям
	$_SESSION['politics']['data']['check_access'] = true;
	$_SESSION['politics']['data']['user_id'] = $this->getSessionUserId();

	$default_params['grid_item'] = array('id', 'topic_id', 'city_id', 'district_id', 'street_id', 'price', 'image');
	$default_params['render_user_id'] = $this->getSessionUserId();
	if ( !preg_match('/all[\/]?$/', $REQUESTURIPATH) ) {
	    $params['grid_conditions']['user_id'] = $this->getSessionUserId();
	}
	
	$params['grid_controls'] = array('edit', 'delete', 'memorylist');
	$params['url'] = '/'.$REQUESTURIPATH;
	$rs = '

    <link rel="stylesheet" href="/apps/admin/admin/template1/assets/css/font-awesome.min.css" />
		<link rel="stylesheet" href="/apps/data/css/style.css" />
    <script src="/apps/system/js/bootstrap/js/bootstrap.min.js"></script>
    
	
	<script src="/apps/admin/admin/template1/assets/js/bootstrap-tag.min.js"></script>
	
	<!-- ace scripts -->
';
	$rs .= parent::grid($params, $default_params);
	return $rs;
    }

    /**
     * Delete data
     * @param string $table_name
     * @param string $primary_key
     * @param int $primary_key_value
     */
    function delete_data($table_name, $primary_key, $primary_key_value) {
	if ($this->need_check_access($table_name)) {
	    if (!$this->check_access($table_name, $this->get_check_access_user_id($table_name), 'delete', $primary_key, $primary_key_value)) {
		$this->riseError('ID = ' . $primary_key_value . ', ' . Multilanguage::_('L_ACCESS_DENIED'));
		return false;
	    }
	}
	return parent::delete_data($table_name, $primary_key, $primary_key_value);
    }

    function _exportAction() {
	$REQUESTURIPATH = Sitebill::getClearRequestURI();	
	if ( !preg_match('/all[\/]?$/', $REQUESTURIPATH) ) {
	    $params['grid_conditions']['user_id'] = $this->getSessionUserId();
	}
	parent::_exportAction($params);
    }

    function ajax() {

	return false;
    }

}
