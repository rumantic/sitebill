<?php
/**
 * District manager
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class District_Manager extends Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
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
		$DBC=DBC::getInstance();

		$query = "select count(*) as cid from ".$__db_prefix."_data where district_id=$district_id";
        $stmt=$DBC->query($query);
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	if ( $ar['cid'] > 0 ) {
        		return true;
        	}
        }
        return false;
    }

    /**
     * Add record
     * @param void
     * @return string
     */
    function add_record_and_get_id ( $name ) {
    	$DBC=DBC::getInstance();
        $query = "insert into ".DB_PREFIX."_district (name) values ('$name')";
        $stmt=$DBC->query($query);
        $district_id=0;
        if($stmt){
        	$district_id=$DBC->lastInsertId();
        }
        return $district_id;
    }

    /**
     * Load
     * @param int $record_id record ID
     * @return boolean
     */
    function load ( $record_id ) {
    	$DBC=DBC::getInstance();
        $query = 'SELECT * FROM '.DB_PREFIX.'_district WHERE id=?';
        //echo $query;
        $stmt=$DBC->query($query, array($record_id));
        $ar=array();
        if($stmt){
        	$ar=$DBC->fetch($stmt);
        }
        $this->setRequestValue('name', $ar['name']);
    }

	/**
	 * Delete data
	 * @param string $table_name
	 * @param string $primary_key
	 * @param int $primary_key_value
	 */
	function delete_data($table_name, $primary_key, $primary_key_value ) {
		$DBC=DBC::getInstance();
		$search_queries=array(
			Multilanguage::_('TABLE_ADS','system')=>'SELECT COUNT(*) AS rs FROM '.DB_PREFIX.'_data WHERE district_id=?',
		);
		if(0==$this->getConfigValue('link_street_to_city')){
			$search_queries[Multilanguage::_('TABLE_STREET','system')]='SELECT COUNT(*) AS rs FROM '.DB_PREFIX.'_street WHERE district_id=?';
		}
		$ans=array();
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
