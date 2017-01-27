<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/*
 * Common class for ajax methods.
 * Doing routings with models
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class ajax_common extends SiteBill {
	function get_primary_key () {
		return $this->getRequestValue('primary_key');
	}
	
	function get_table_name () {
		return $this->getRequestValue('model_name');
	}
	
	function get_action () {
		return $this->getRequestValue('model_name');
	}
	function get_key_by_title ( $title, $header ) {
		foreach ( $this->get_model() as $key => $item ) {
			if ( $title == $item['name'] and $item['title'] == $header[$item['title']] ) {
				return $header[$item['title']];
			}
		}
		return false;
	}
	
	function get_model () {
		$model_name = $this->getRequestValue('model_name');
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
		$ATH=new Admin_Table_Helper();
		$form_data=$ATH->load_model($model_name);
	
		//$this->writeLog(__METHOD__.' model = '.var_export($form_data, true));
	
		if(!empty($form_data[$model_name])){
			return $form_data[$model_name];
		}
		return false;
	}
	
	function get_model_with_table_name () {
		$model_name = $this->getRequestValue('model_name');
		require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
		$ATH=new Admin_Table_Helper();
		$form_data=$ATH->load_model($model_name);
	
		//$this->writeLog(__METHOD__.' model = '.var_export($form_data, true));
	
		if(!empty($form_data[$model_name])){
			return $form_data;
		}
		return false;
	}
	
	
	function get_key_by_title_only_model ( $title ) {
		foreach ( $this->get_model() as $key => $item ) {
			if ( $title == $item['title'] ) {
				return $item['name'];
			}
		}
		return false;
	}
}