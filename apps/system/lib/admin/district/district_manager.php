<?php
/**
 * District manager
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class District_Manager extends Object_Manager {
    /**
     * Constructor
     */
    function District_Manager() {
        $this->SiteBill();
        $this->table_name = 'district';
        $this->action = 'district';
        $this->app_title = Multilanguage::_('DISTRICT_APP_NAME','system');
        $this->primary_key = 'id';
	    
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
	    $data_model = new Data_Model();
        $this->data_model = $data_model->get_district_model();
    }
    
    /**
     * Get data by district
     * @param int $district_id
     * @return boolean
     */
    function getDataByDistrict ( $district_id ) {
		global $__db_prefix;

		$query = "select count(*) as cid from ".$__db_prefix."_data where district_id=$district_id";
        $this->db->exec($query);
        $this->db->fetch_assoc();
        if ( $this->db->row['cid'] > 0 ) {
        	return true;
        }
        return false;
    	
    }
    
    /**
     * Add record
     * @param void
     * @return string
     */
    function add_record_and_get_id ( $name ) {
        
        $query = "insert into ".DB_PREFIX."_district (name) values ('$name')";
        $district_id = $this->db->exec($query);
        return $district_id;
    }
    
    /**
     * Load
     * @param int $record_id record ID
     * @return boolean
     */
    function load ( $record_id ) {
        
        $query = "select * from re_district where id=$record_id";
        //echo $query;
        $this->db->exec($query);
        $this->db->fetch_assoc();
        
        $this->setRequestValue('name', $this->db->row['name']);
    }
    
	/**
	 * Delete data
	 * @param string $table_name
	 * @param string $primary_key
	 * @param int $primary_key_value
	 */
	function delete_data($table_name, $primary_key, $primary_key_value ) {
		$search_queries=array(
			Multilanguage::_('TABLE_ADS','system')=>'SELECT COUNT(*) AS rs FROM '.DB_PREFIX.'_data WHERE district_id=?',
		);
		if(0==$this->getConfigValue('link_street_to_city')){
			$search_queries[Multilanguage::_('TABLE_STREET','system')]='SELECT COUNT(*) AS rs FROM '.DB_PREFIX.'_street WHERE district_id=?';
		}
		$ans=array();
		foreach($search_queries as $k=>$v){
			$query=str_replace('?', $primary_key_value, $v);
			$this->db->exec($query);
		    if ($this->db->success) {
		    	$this->db->fetch_assoc();
		    	$rs=$this->db->row['rs'];
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
?>