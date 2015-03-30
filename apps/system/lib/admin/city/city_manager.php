<?php
/**
 * City manager
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class City_Manager extends Object_Manager {
    /**
     * Constructor
     */
    function City_Manager() {
        $this->SiteBill();
        $this->table_name = 'city';
        $this->action = 'city';
        $this->app_title = Multilanguage::_('CITY_APP_NAME','system');
        $this->primary_key = 'city_id';
	    
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
        $this->data_model = $data_model->get_city_model();
    }
    
	/**
	 * Delete data
	 * @param string $table_name
	 * @param string $primary_key
	 * @param int $primary_key_value
	 */
	function delete_data($table_name, $primary_key, $primary_key_value ) {
		$search_queries=array(
			Multilanguage::_('TABLE_ADS','system')=>'SELECT COUNT(*) AS rs FROM '.DB_PREFIX.'_data WHERE city_id=?',
			Multilanguage::_('TABLE_METRO','system')=>'SELECT COUNT(*) AS rs FROM '.DB_PREFIX.'_metro WHERE city_id=?'
		);
		if($this->getConfigValue('link_street_to_city')){
			$search_queries[Multilanguage::_('TABLE_STREET','system')]='SELECT COUNT(*) AS rs FROM '.DB_PREFIX.'_street WHERE city_id=?';
		}
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
			return $this->riseError(implode('<br />',$ans));
		}
	}
}
?>