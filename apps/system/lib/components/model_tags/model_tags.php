<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/components/ajax_common.php');
/**
 * Process ajax request and return json arrays with tags values 
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class model_tags extends ajax_common {
	function get_array () {
		$form_data = $this->get_model();
		if ( $form_data[$this->getRequestValue('column_name')]['type'] == 'select_by_query' ) {
			$tags_array = $this->get_array_by_query($form_data[$this->getRequestValue('column_name')]); 
		//} elseif ( $form_data[$this->getRequestValue('column_name')]['type'] == 'checkbox' ) {
		//	$tags_array = array();
		} elseif ( $form_data[$this->getRequestValue('column_name')]['type'] == 'client_id'  ) {
			$tags_array = $this->get_array_by_client_id($form_data[$this->getRequestValue('column_name')]);
		} elseif ( $form_data[$this->getRequestValue('column_name')]['type'] == 'select_box_structure'  ) {
			$tags_array = $this->get_array_by_structure($form_data[$this->getRequestValue('column_name')]);
		} elseif ( $form_data[$this->getRequestValue('column_name')]['type'] == 'select_box'  ) {
			$tags_array = $this->get_array_by_select_box($form_data[$this->getRequestValue('column_name')], $form_data);
		} else {
			$tags_array = $this->get_distinct_values($form_data[$this->getRequestValue('column_name')]);
		}
		return json_encode($tags_array);
	}
	
	function ajax () {
		header('HTTP/1.1 200 OK');
		header('Content-Type: application/json');
		if ( $this->getRequestValue('do') == 'set' ) {
			$this->init_session_tags();
			echo json_encode(array('status' => 'ok'));
		} else {
			echo $this->get_array();
		}
		exit;
	}
	
	function init_session_tags () {
		$tags_array_string = $this->getRequestValue('tags_array');
        $model_name = $this->getRequestValue('model_name');
		$tags_array_string = html_entity_decode($tags_array_string);
		$decoded = json_decode($tags_array_string, true);
        if ( $model_name != '' ) {
            $_SESSION['model_tags'][$model_name]['tags_array'] = $decoded;
        } else {
            $_SESSION['tags_array'] = $decoded;
        }
		//$this->writeLog(__METHOD__.var_export($_SESSION['tags_array'], true));
		return true;
	}
	
	function get_distinct_values ( $item_array ) {
		$DBC=DBC::getInstance();
		$query='SELECT distinct(`'.$item_array['name'].'`) FROM '.DB_PREFIX.'_'.$this->get_table_name().'';
		//$this->writeLog(__METHOD__.', query = '.$query);
		$stmt=$DBC->query($query);
		
		$ra = array();
		
		if($stmt){
			while ($ar=$DBC->fetch($stmt)){
				$this->total_in_select[$item_array['name']]++;
				$value = $ar[$item_array['name']];
				$value = trim($value);
				//$value = htmlspecialchars_decode($value);
				$value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
				$ra[] =  $value;
			}
		}
		return $ra;
	}
	
	function get_array_by_structure ( $item_array, $model=null ) {
		$DBC=DBC::getInstance();
		
		$query='SELECT * FROM '.DB_PREFIX.'_topic';
		$stmt=$DBC->query($query);
		if($stmt){
			while ($ar=$DBC->fetch($stmt)){
				$params['topic_id'] = $ar['id'];
				$breadcrumbs=$this->getBreadcrumbs($params);
				
				$ra[] = $breadcrumbs;
			}
		}
		return $ra;
	}
	
	function get_array_by_client_id ( $item_array, $model=null ) {
		$DBC=DBC::getInstance();
		
		$query='SELECT fio, phone FROM '.DB_PREFIX.'_client';
		$stmt=$DBC->query($query);
		if($stmt){
			while ($ar=$DBC->fetch($stmt)){
				$ra[] = $ar['fio'].', '.$ar['phone'];
			}
		}
		return $ra;
	}
	
	
	function get_array_by_select_box ( $item_array, $model=null ) {
		//$this->writeLog(__METHOD__." <pre>".var_export($item_array, true)."</pre>");
		return array_values($item_array['select_data']);
	}
	
	
	protected function getBreadcrumbs($params){
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
		$Structure_Manager = new Structure_Manager();
		$category_structure = $Structure_Manager->loadCategoryStructure();
		return $this->get_category_breadcrumbs_string( $params, $category_structure, SITEBILL_MAIN_URL.'/' );
		
	}
	
	
	
	/**
	 * Get single select box by query
	 * @param array $item_array
	 * @return string
	 */
	function get_array_by_query ( $item_array, $model=null ) {
		/*if(isset($item_array['parameters'])){
			$parameters=$item_array['parameters'];
		}else{
			$parameters=array();
		}
		
		if(isset($parameters['linked']) && $parameters['linked']!=''){
			$linked_elts_str=explode(';', $parameters['linked']);
		}
		 
		$links=array();
		if(!empty($linked_elts_str)){
			foreach ($linked_elts_str as $str){
				$x=explode(',', $str);
				$links[]=array(
						'linked_element'=>trim($x[0]),
						'linked_field'=>trim($x[1])
				);
			}
		}
		$depended_element_name='';
		if(isset($parameters['depended']) && $parameters['depended']!=''){
			$depended_element_name=trim($parameters['depended']);
		}*/
		//$rs='';
		
		/*$selected='';
		$onchange=array();
		if(count($links)>0){
			foreach($links as $lnks){
				$onchange[]='LinkedElements.refresh(this, \''.$lnks['linked_element'].'\', \''.$lnks['linked_field'].'\');';
			}
		}*/
		$this->total_in_select[$item_array['name']] = 0;
		//$rs .= '<select name="'.$item_array['name'].'" id="'.$item_array['name'].'" onchange="'.implode(' ', $onchange).' '.(isset($item_array['onchange']) ? $item_array['onchange'] : '').'"'.(isset($item_array['onclick']) ? ' onClick="'.$item_array['onclick'].'"' : ' ').'>';
		/*if ( $_SESSION['_lang'] != 'ru' ) {
			$lang_key = 'title_default_'.$_SESSION['_lang'];
			if ( $item_array[$lang_key] != '' ) {
				$item_array['title_default'] = $item_array[$lang_key];
			}
		}*/
		//$rs .= '<option value="'.$item_array['value_default'].'" '.$selected.'>'.$item_array['title_default'].'</option>';
		//print_r($item_array);
		$DBC=DBC::getInstance();
		/*if($depended_element_name!=''){
			$depended_value=$model[$depended_element_name]['value'];
			if((int)$depended_value!=0){
				$query='SELECT `'.$item_array['primary_key_name'].'`, `'.$item_array['value_name'].'` FROM '.DB_PREFIX.'_'.$item_array['primary_key_table'].' WHERE `'.$depended_element_name.'`=?';
				$stmt=$DBC->query($query, array((int)$depended_value));
			}else{
				$query='SELECT `'.$item_array['primary_key_name'].'`, `'.$item_array['value_name'].'` FROM '.DB_PREFIX.'_'.$item_array['primary_key_table'].' WHERE 1=0';
				$stmt=$DBC->query($query);
			}
			//echo $query;
		}else{
			$query=$item_array['query'];
			$stmt=$DBC->query($query);
		}*/
		
		$query=$item_array['query'];
		$stmt=$DBC->query($query);
		
		$ra = array();
		
		if($stmt){
			while ($ar=$DBC->fetch($stmt)){
				$this->total_in_select[$item_array['name']]++;
				$value = $ar[$item_array['value_name']];
				$value = trim($value);
				//$value = htmlspecialchars_decode($value);
				$value = htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
				$ra[] =  $value;
			}
		}
		return $ra;
		
	}
	
}