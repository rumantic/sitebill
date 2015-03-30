<?php
/**
 * Form generator create simple view HTML-table with data elements without controls
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class Form_View_Generator extends Form_Generator {
	/**
	 * Compile form inputs
	 * @param $form_data form data
	 * @return string
	 */
	function compile_form ( $form_data, $ignore_tabs=false ) {
		$Sitebill_Registry=Sitebill_Registry::getInstance();
		 
		 
		$elements[]=array();
		$default_tab_name=$this->getConfigValue('default_tab_name');
		$tabs=array($default_tab_name);
		 
		foreach ( $form_data as $item_id => $item_array ) {
			$rs='';
			//echo "type = {$item_array['type']}, name = {$item_array['name']}<br>";
			switch ( $item_array['type'] ) {
				case 'price':
					$rs = $this->get_price_input($item_array);
					break;
				case 'select_box':
					$rs = $this->get_select_box_row($item_array);
					break;
	
				case 'email':
					$rs = $this->get_email_input($item_array);
					break;
	
				case 'mobilephone':
					$rs = $this->get_mobilephone_input($item_array);
					break;
	
				case 'select_by_query':
					$rs = $this->get_select_box_by_query_row_view($item_array);
					break;
	
				case 'select_by_query_multiple':
					$rs = $this->get_select_box_by_query_multiple_row($item_array);
					break;
	
				case 'select_box_structure':
					$rs = $this->get_select_box_structure_row($item_array);
					break;
	
				case 'structure':
					$rs = $this->get_structure_row($item_array);
					break;
	
				case 'select_box_structure_simple_multiple':
					$rs = $this->get_select_box_structure_simple_multiple_row($item_array);
					break;
	
				case 'shop_select_box_structure':
					$rs = $this->get_shop_select_box_structure_row($item_array);
					break;
	
				case 'service_type_select_box_structure':
					{
						$rs = $this->get_service_type_select_box_structure_row($item_array);
					}
					break;
					/*
					 case 'uploader':
					$rs .= $this->get_uploader_row($item_array);
					break;
	
					case 'pluploader':
					$rs .= $this->get_pluploader_row($item_array);
					break;
					*/
				case 'uploadify_image':
					switch($this->getConfigValue('uploader_type')){
						case 'pluploader' : {
							$rs = $this->get_pluploader_row($item_array);
							break;
						}
						default : {
							$rs = $this->get_uploadify_row($item_array);
						}
					}
	
					break;
	
				case 'uploadify_file':
					$rs = $this->get_uploadify_file_row($item_array);
					break;
	
				case 'separator':
					$rs = $this->get_separator_row($item_array);
					break;
	
				case 'checkbox':
					$rs = $this->get_checkbox_box_row($item_array);
					break;
	
				case 'grade':
					$rs = $this->get_grade_row($item_array);
					break;
	
				case 'date':
					$rs = $this->get_date_input($item_array);
					break;
	
				case 'auto_add_value':
					$rs = $this->get_safe_text_input($item_array);
					break;
	
				case 'geodata':
					$rs = $this->get_geodata_input($item_array);
					break;
	
				case 'password':
					break;
	
				case 'photo':
					$rs = $this->get_photo_input_view($item_array);
					break;
	
				case 'captcha':
					$rs = $this->get_captcha_input($item_array);
					break;
	
				case 'spacer_text':
					$rs = $this->get_spacer_text($item_array);
					break;
	
				case 'hidden':
					$rs = $this->get_hidden_input($item_array);
					break;
	
				case 'values_list':
					$rs = $this->get_safe_text_input($item_array);
					break;
				default:
					$rs = $this->get_safe_text_view($item_array);
			}
	
	
			// echo $default_tab_name;
	
			if(isset($item_array['tab']) && $item_array['tab']!=''){
				$tabs[$item_array['tab']]=$item_array['tab'];
				if($rs!=''){
					$elements[$item_array['tab']][]=$rs;
				}
			}else{
				if($rs!=''){
					$elements[$default_tab_name][]=$rs;
				}
			}
	
		}
		$rt='';
	
		if($Sitebill_Registry->getFeedback('divide_step_form')){
			$tabs_count=count($tabs);
			$current_step=$Sitebill_Registry->getFeedback('step');
			$Sitebill_Registry->addFeedback('steps',$tabs_count);
			if($tabs_count>1){
				$tabs_names=array_keys($tabs);
			}
			$tabs_names=array_keys($tabs);
	
			$rt.='<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/form_tabs.js"></script>';
			if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/css/form_tabs.css') ) {
				$rt.='<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/template/frontend/'.$this->getConfigValue('theme').'/css/form_tabs.css" />';
			} else {
				$rt.='<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/apps/system/css/form_tabs.css" />';
			}
			 
			$rt.='<tbody id="form_tab_switcher" style="display:none;">';
			$rt.='<tr colspan="2"><td>';
			$ti=1;
			 
			foreach($tabs as $tab){
				if($ti>$current_step){
					$rt.='<span>'.$tab.'</span>';
				}elseif($ti==$current_step){
					$rt.='<a href="'.md5($tab).'" class="active_tab">'.$tab.'</a>';
				}else{
					$rt.='<a href="'.md5($tab).'">'.$tab.'</a>';
				}
	
				$ti++;
			}
			$rt.='</td></tr></tbody>';
			 
			$ti=1;
			foreach($tabs as $tab){
				if($ti>$tabs_count){
					break;
				}
				if($ti==$current_step){
					$rt.='<tbody class="form_tab" id="'.md5($tab).'">';
					$rt.='<tr colspan="2"><td>'.$tab.'</td></tr>';
					if(count($elements[$tab])>0){
						foreach($elements[$tab] as $el){
							$rt.=$el;
						}
					}
					$rt.='</tbody>';
				}else{
					$rt.='<tbody class="form_tab">';
					$rt.='<tr colspan="2"><td>'.$tab.'</td></tr>';
					if(count($elements[$tab])>0){
						foreach($elements[$tab] as $el){
							$rt.=$el;
						}
					}
					$rt.='</tbody>';
				}
	
	
				$ti++;
			}
		}elseif(count($tabs)>1 && !$ignore_tabs){
			 
			$rt.='<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/apps/system/js/form_tabs.js"></script>';
			if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/css/form_tabs.css') ) {
				$rt.='<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/template/frontend/'.$this->getConfigValue('theme').'/css/form_tabs.css" />';
			} else {
				$rt.='<link rel="stylesheet" type="text/css" href="'.SITEBILL_MAIN_URL.'/apps/system/css/form_tabs.css" />';
			}
			$rt.='<tbody id="form_tab_switcher">';
			$rt.='<tr colspan="2"><td>';
			foreach($tabs as $tab){
				$rt.='<a href="'.md5($tab).'">'.$tab.'</a>';
			}
			$rt.='</td></tr></tbody>';
			 
			foreach($tabs as $tab){
				$rt.='<tbody class="form_tab" id="'.md5($tab).'">';
				$rt.='<tr colspan="2"><td>'.$tab.'</td></tr>';
				if(count($elements[$tab])>0){
					foreach($elements[$tab] as $el){
						//echo $el;
						$rt.=$el;
					}
				}
				$rt.='</tbody>';
			}
		}elseif(count($tabs)>1){
			foreach($tabs as $tab){
				if(count($elements[$tab])>0){
					foreach($elements[$tab] as $el){
						$rt.=$el;
					}
				}
			}
		}else{
			 
			if(count($elements[$default_tab_name])>0){
				foreach($elements[$default_tab_name] as $el){
					$rt.=$el;
				}
			}
			 
		}
		return $rt;
		//return $rs;
	}
	
	/**
	 * Get photo input
	 * @param array $item_array
	 * @return string
	 */
	function get_photo_input_view ( $item_array ) {
	
		/*Un-quote slashes*/
		$value = stripslashes( $value );
		/*HTML code*/
		$string .= "<tr  class=\"row3\" alt=\"".$item_array['name']."\">\n";
	
		/*Mark required field with simbol '*' */
		$string .= "<td class=\"$bg_color\">".$item_array['title']."</td>\n";
	
		$string .= '<td>';
		if ( $item_array['value'] != '' ) {
			$string .= '<img src="'.SITEBILL_MAIN_URL.'/img/data/user/'.$item_array['value'].'" border="0"/><br>';
		}
		$string .= '</td>';
	
		$string .= "</tr>\n";
	
		/*Return html code*/
		return $string;
	}
	
	
	/**
	 * Get select box row
	 * @param array $item_array
	 * @return string
	 */
	function get_select_box_by_query_row_view ( $item_array ) {
		$rs = '<tr  class="row3"  alt="'.$item_array['name'].'">';
		$rs .= '<td>';
		$rs .= $item_array['title'];
		$rs .= '</td>';
		$rs .= '<td>';
		$rs .= $this->get_single_select_box_by_query_view($item_array);
		$rs .= '</td>';
		$rs .= '</tr>';
	
		return $rs;
	}
	
	/**
	 * Get single select box by query
	 * @param array $item_array
	 * @return string
	 */
	function get_single_select_box_by_query_view ( $item_array ) {
		//return '';
		$this->total_in_select[$item_array['name']] = 0;
		$rs .= '<div id="'.$item_array['name'].'_div">';
		//echo $item_array['query'];
		$this->db->exec($item_array['query']);
		while ( $this->db->fetch_assoc() ) {
			$this->total_in_select[$item_array['name']]++;
			$value = $this->db->row[$item_array['value_name']];
			$value = trim($value);
			$value = htmlspecialchars_decode($value);
			if ( $this->db->row[$item_array['primary_key_name']] ==  $item_array['value'] ) {
				$rs .= $value;
			}
		}
		/*if(1==$this->getConfigValue('use_combobox')){
		 $rs .= '<input type="text" name="nw" id="nw" />';
		}*/
		$rs .= '</div>';
	
		return $rs;
	}
	
	
	
	/**
	 * Get safe string input
	 * @param array  $item_array
	 * @return string
	 */
	function get_safe_text_view ( $item_array ) {
	
		/*Un-quote slashes*/
		$value = stripslashes( $value );
		/*HTML code*/
		$string .= "<tr class=\"row3\" alt=\"".$item_array['name']."\">\n";
	
		/*Mark required field with simbol '*' */
		$string .= "<td class=\"$bg_color\">".$item_array['title'].((isset($item_array['hint']) && $item_array['hint']!='') ? ' <span class="hint">('.$item_array['hint'].')</span>' : '')."</td>\n";
	
		$string .= "<td class=\"$bg_color\">{$item_array['value']}</td>\n";
		$string .= "</tr>\n";
	
		/*Return html code*/
		return $string;
	}
	
	function get_checkbox_box_row_view ( $item_array ) {
	
		$rs = '<tr  class="row3">';
		$rs .= '<td>';
		$rs .= $item_array['title'];
		 
		$rs .= '</td>';
	
		$rs .= '<td>';
		$rs .= '<input type="checkbox" name="'.$item_array['name'].'" disabled="disabled"';
		if ( $item_array['value'] == 1 ) {
			$rs .= ' checked ';
		}
		$rs .= '/>';
	
		$rs .= '</td>';
		$rs .= '</tr>';
		return $rs;
	}
	
}