<?php
class Form_Decorator {
	
	private $form_generator=null;
	
	public function setFormGenerator($form_generator=null){
		$this->form_generator=$form_generator;
	}
	
	public function decorateCheckboxInput($elementName='', $elementValue='', $isChecked=false, $params=array()){
		
		$attrs=array();
		$attrs[]='type="checkbox"';
		if($elementName!=''){
			$attrs[]='name="'.$elementName.'"';
		}
		if($elementValue!=''){
			$attrs[]='value="'.$elementValue.'"';
		}else{
			$attrs[]='value=""';
		}
		if(isset($params['id']) && $params['id']!=''){
			$attrs[]='id="'.$params['id'].'"';
		}
		if($isChecked){
			$attrs[]='checked="checked"';
		}
		
		$html='<input '.implode(' ', $attrs).'/>';
		
		return $html;
	}
	
	public function decorateTextInput($elementName='', $elementValue='', $params=array()){
		
		$attrs=array();
		$attrs[]='type="text"';
		
		if($elementName!=''){
			$attrs[]='name="'.$elementName.'"';
		}
		if($elementValue!=''){
			$attrs[]='value="'.$elementValue.'"';
		}else{
			$attrs[]='value=""';
		}
		if(isset($params['id']) && $params['id']!=''){
			$attrs[]='id="'.$params['id'].'"';
		}
		if(isset($params['placeholder']) && $params['placeholder']!=''){
			$attrs[]='placeholder="'.$params['placeholder'].'"';
		}
		
		if(isset($params['class']) && $params['class']!=''){
			$attrs[]='class="'.$params['class'].'"';
		}
		
		if(isset($params['styles']) && $params['styles']!=''){
			$attrs[]='style="'.$params['styles'].'"';
		}
		if(isset($params['onclick']) && $params['onclick']!=''){
			$attrs[]='onclick="'.$params['onclick'].'"';
		}
		if(isset($params['onchange']) && $params['onchange']!=''){
			$attrs[]='onchange="'.$params['onchange'].'"';
		}
		
		$html='<input '.implode(' ', $attrs).'/>';
		
		return $html;
		
	}
	
	public function decorateAgreementFormBlockCheckbox($text, $id){
		return '<div class="agreement_form_block"><input type="hidden" name="agreement_el" value="1"><div class="agreement_form_block_input"><input id="agreement_form_block_input_'.$id.'" type="checkbox" name="agreement" value="1"></div><label for="agreement_form_block_input_'.$id.'">'.$text.'</label></div>';
	}
	
	public function decorateAgreementFormBlockNote($text){
		return '<div class="agreement_form_block"><div class="agreement_form_block_note">'.$text.'</div></div>';
	}
	
	public function decorateMultiselectItemCheckbox($item_name, $item_key, $item_value, $item_values_array, $otherParams=array()){
		$rs='';
		$rs.='<div class="select_box_multiselect_item1">';
		$rs.='<input type="checkbox" name="'.$item_name.'[]" value="'.$item_key.'"'.((isset($item_values_array) && in_array($item_key, $item_values_array)) ? ' checked="checked"' : '').'>'.$item_value;
		$rs.='</div>';
		return $rs;
	}
	
	public function decorateMultiselectItem($name, $items, $item_values_array, $otherParams=array()) {
		$rs='';
		foreach ( $items as $item_key => $item_value ) {
			$rs.=$this->decorateMultiselectItemCheckbox($name, $item_key, $item_value, $item_values_array, $otherParams);
		}
		return $rs;
	}
}