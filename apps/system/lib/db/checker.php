<?php
/**
 * Check database 
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class Estate_Checker extends SiteBill {
	/**
	 * Construct
	 */
	function __construct() {
		$this->SiteBill();
	}
	
	/**
	 * Main
	 */
	function main () {
		global $__db_prefix;
		
		$query = "select count(*) as cid from ".$__db_prefix."_data";
		$this->db->exec($query);
		$this->db->fetch_assoc();
		$count = $this->db->row['cid'];
		echo 'count = '.$count;
		if ( $count < 10 ) {
			mail('kondin@etown.ru', 'estate db empty!', 'somebody delete all data!');
		}
		return;
	}
}
?>