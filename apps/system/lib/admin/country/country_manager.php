<?php
/**
 * Country manager
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class Country_Manager extends Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
        $this->table_name = 'country';
        $this->action = 'country';
        $this->app_title = Multilanguage::_('COUNTRY_APP_NAME','system');
        $this->primary_key = 'country_id';

        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
        $this->data_model = $data_model->get_country_model();
    }

    function delete_data($table_name, $primary_key, $primary_key_value ) {
    	$DBC=DBC::getInstance();

    	$search_queries=array(
    			Multilanguage::_('TABLE_REGION','system')=>'SELECT COUNT(*) AS rs FROM '.DB_PREFIX.'_region WHERE country_id=?',
    			Multilanguage::_('TABLE_ADS','system')=>'SELECT COUNT(*) AS rs FROM '.DB_PREFIX.'_data WHERE country_id=?'
    	);
    	$ans=array();
    	$DBC=DBC::getInstance();


    	foreach($search_queries as $k=>$v){
    		$query=str_replace('?', $primary_key_value, $v);
    		$stmt=$DBC->query($query);
    		if ($stmt) {
    			$ar=$DBC->fetch($stmt);
    			$rs=$ar['rs'];
    			if($rs!=0){
    				$ans[]=sprintf(Multilanguage::_('MESSAGE_CANT_DELETE','system'), $k);
    			}
    		}
    	}
    	if(empty($ans)){
    		return parent::delete_data($table_name, $primary_key, $primary_key_value);
    	}else{
    		$this->riseError(implode('<br />',$ans));
    	}

    }
}
