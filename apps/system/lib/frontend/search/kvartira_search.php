<?php
/**
 * Kvartira search form
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Kvartira_Search_Form extends SiteBill {
    /**
     * Construct
     */
    function __construct() {
        $this->SiteBill();
    }
    
    /**
     * Main
     * @param
     * @return
     */
    function main () {
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
		$form_generator = new Form_Generator();
	    
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
	    
	    $kvartira_model = $data_model->get_kvartira_model(true);
	     
	    $kvartira_model['data'] = $data_model->init_model_data_from_request($kvartira_model['data'], true, true);
	    
        
        $this->template->assert('ajax_functions', $this->get_ajax_functions());
        
        if ( $this->getConfigValue('country_in_form') ) {
        	$this->template->assert('country_list', $form_generator->get_single_select_box_by_query($kvartira_model['data']['country_id'], $kvartira_model['data']));
        }
        if ( $this->getConfigValue('district_in_form') ) {
            $this->template->assert('district_list', $form_generator->get_single_select_box_by_query($kvartira_model['data']['district_id'], $kvartira_model['data']));
        }

        if ( $this->getConfigValue('street_in_form') ) {
            $this->template->assert('street_list', $form_generator->get_single_select_box_by_query($kvartira_model['data']['street_id'], $kvartira_model['data']));
        }
        
        if ( $this->getConfigValue('region_in_form') ) {
            $this->template->assert('region_list', $form_generator->get_single_select_box_by_query($kvartira_model['data']['region_id'], $kvartira_model['data']));
        }

        if ( $this->getConfigValue('metro_in_form') ) {
            $this->template->assert('metro_list', $form_generator->get_single_select_box_by_query($kvartira_model['data']['metro_id'], $kvartira_model['data']));
        }
        
        if ( $this->getConfigValue('city_in_form') ) {
        	$this->template->assert('city_list', $form_generator->get_single_select_box_by_query($kvartira_model['data']['city_id'], $kvartira_model['data']));
        }
        
        if(isset($kvartira_model['data']['currency_id']) && 1==$this->getConfigValue('currency_enable')){
        	$this->template->assert('currency_list', $form_generator->get_single_select_box_by_query($kvartira_model['data']['currency_id']));
        }
        
        if(isset($kvartira_model['data']['tlocation'])){
        	$this->template->assert('country_list', '');
        	$this->template->assert('region_list', '');
        	$this->template->assert('city_list', '');
        	$this->template->assert('district_list', '');
        	$this->template->assert('street_list', '');
        	
        	$tdata=$form_generator->compile_tlocation_element($kvartira_model['data']['tlocation']);
        	foreach($tdata->collection as $el){
        		if($el['name']=='country_id'){
        			$this->template->assert('country_list', $el['html']);
        		}
        		if($el['name']=='region_id'){
        			$this->template->assert('region_list', $el['html']);
        		}
        		if($el['name']=='city_id'){
        			$this->template->assert('city_list', $el['html']);
        		}
        		if($el['name']=='district_id'){
        			$this->template->assert('district_list', $el['html']);
        		}
        		if($el['name']=='street_id'){
        			$this->template->assert('street_list', $el['html']);
        		}
        	}
        	
        	$this->template->assert('scripts', $tdata->scripts);
        	//$this->template->assert('tlocation_form_element', $form_generator->compile_tlocation_element($kvartira_model['data']['tlocation']));
        	//$this->template->assert('tlocation_form_element_simple', $form_generator->compile_tlocation_element($kvartira_model['data']['tlocation']));
        	//$this->template->assert('tlocation_form_element_extended', $form_generator->compile_tlocation_element($kvartira_model['data']['tlocation']));
        
        }
        
        
        
       
        
        $this->template->assert('price', $this->getRequestValue('price'));
        $this->template->assert('id', $this->getRequestValue('id'));
        
    }
    
}
?>
