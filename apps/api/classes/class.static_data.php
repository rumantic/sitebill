<?php 
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * DATA REST class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
*/
class Static_Data {
	public static $instance;
	private static $data;
	private static $params;
	
	/**
	 * Obtain Static_Data instance
	 *
	 * @return DB
	 */
	public static function getInstance() {
		if (! self::$instance) {
			self::$instance = new self ( );
		}
		
		return self::$instance;
	}
	
	public static function set_data ( $data ) {
		self::$data = $data;
	}
	
	public static function set_param ( $param_key, $param_value ) {
		self::$params[$param_key] = $param_value;
	}
	
	public static function get_params ( ) {
		return self::$params;
	}
	
	
	public static function get_data () {
		return self::$data;
	}
}
?>