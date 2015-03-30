<?php
/**
 * Form generator
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Search_Form_Generator extends Form_Generator {
    
    
    function __construct() {
        parent::__construct();
    }
    
    function compile_price_element($item_array){
    	if(isset($item_array['parameters'])){
    		$parameters=$item_array['parameters'];
    	}else{
    		$parameters=array();
    	}
    	
    	if(isset($parameters['is_ranged']) && $parameters['is_ranged']==1){
    		if(''==$this->getRequestValue('price_min')){
    			$value_min='';
    		}else{
    			$value_min=number_format((int)str_replace(' ', '', $this->getRequestValue('price_min')),0,',',' ');
    		}
    		
    		if(''==$this->getRequestValue('price')){
    			$value_max='';
    		}else{
    			$value_max=number_format((int)str_replace(' ', '', $this->getRequestValue('price')),0,',',' ');
    		}
    		
    		
    		
    		$string = '<script type="text/javascript" src="'.SITEBILL_MAIN_URL.'/js/autoNumeric-1.7.5.js"></script>';
    		$string='';
    		$html.= Multilanguage::_('L_FROM').' <input type="text" class="price_field" name="price_min" value="'.$value_min.'" />';
    		$html.= ' '.Multilanguage::_('L_TO').' <input type="text" class="price_field" name="price" value="'.$value_max.'" />';
    		
    	}else{
    		$value=number_format((int)str_replace(' ', '', $item_array['value']),0,',',' ');
    		$string='';
    		$html='<input type="text" class="price_field" name="'.$item_array['name'].'" value="'.$value.'" />';
    	}
    	
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$string.$html,
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
    
    function compile_safe_string_element($item_array){
    	
    	if(isset($item_array['parameters'])){
    		$parameters=$item_array['parameters'];
    	}else{
    		$parameters=array();
    	}
    	
    	if(isset($parameters['is_ranged']) && $parameters['is_ranged']==1){
    		$value_min=htmlspecialchars($this->getRequestValue($item_array['name'].'_min'), ENT_QUOTES, SITE_ENCODING);
    		$value_max=htmlspecialchars($this->getRequestValue($item_array['name'].'_max'), ENT_QUOTES, SITE_ENCODING);
    		
    		$html='';
    		$html.= Multilanguage::_('L_FROM').' <input type="text" id="'.$id_min.'" name="'.$item_array['name'].'_min" value="'.$value_min.'" />';
    		$html.= ' '.Multilanguage::_('L_TO').' <input type="text" id="'.$id_max.'" name="'.$item_array['name'].'_max" value="'.$value_max.'" />';
    	}else{
    		if(is_array($item_array['value'])){
    			$value=$item_array['value'][count($item_array['value'])-1];
    			$value=htmlspecialchars($value, ENT_QUOTES, SITE_ENCODING);
    		}else{
    			$value=htmlspecialchars($item_array['value'], ENT_QUOTES, SITE_ENCODING);
    		}
    		
    		$html='<input type="text" name="'.$item_array['name'].'" value="'.$value.'" />';
    	}
    	
    	
    	return array(
    			'title'=>$item_array['title'],
    			'required'=>($item_array['required'] == "on" ? 1 : 0),
    			'html'=>$html,
    			'tab'=>(isset($item_array['tab']) ? $item_array['tab'] : '')
    	);
    }
   
}
?>