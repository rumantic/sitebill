<?php
/**
 * Country manager
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class Country_Manager extends Object_Manager {
    /**
     * Constructor
     */
    function Country_Manager() {
        $this->SiteBill();
        $this->table_name = 'country';
        $this->action = 'country';
        $this->app_title = Multilanguage::_('COUNTRY_APP_NAME','system');
        $this->primary_key = 'country_id';
	    
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
        $this->data_model = $data_model->get_country_model();
    }

}
?>