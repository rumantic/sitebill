<?php
/**
 * Cache class
 * @author Abushyk Kostyantyn <abushyk@gmail.com>
 * @url
 */
class Cache extends Sitebill {
	
	private static $instance;
	private $cacheValues=array();
	
	/**
	 * Return Cache object
	 * @return instance of Cache
	 */
	public static function getInstance(){
		if (self::$instance == NULL){
	      self::$instance = new Cache();
	    }
	    return self::$instance;
	}
		
	/**
	 * Return cache value
	 * @param string $name Cache parameter name
	 * @return mixed|NULL
	 */
	public function getValue($name){
		if(isset($this->cacheValues[$name])){
			return $this->extractValue($name);
		}else{
			return NULL;
		}
	}
	
	/**
	 * Check is valid cache parameter
	 * @param string $name Cache parameter name
	 * @param string $type {expired|date} Type of checking
	 * @return boolean
	 */
	public function isValid($name, $type='expired'){
		if(isset($this->cacheValues[$name])){
			switch($type){
				case 'date' : {
					if(date('d-m-Y',$this->cacheValues[$name]['valid_for'])==date('d-m-Y',time())){
						return TRUE;
					}else{
						return FALSE;
					}
					break;
				}
				default : {
					if($this->cacheValues[$name]['valid_for']>time()){
						return TRUE;
					}else{
						return FALSE;
					}
				}
			}
		}else{
			return FALSE;
		}
	}
		
	/**
	 * Delete cache value
	 * @param string $name Cache parameter name
	 */
	public function clearValue($name){
		if(isset($this->cacheValues[$name])){
			unset($this->cacheValues[$name]);
			$this->deleteCacheValue($name);
		}
	}
	
	/**
	 * Add value to cache
	 * @param string $name Cache parameter name
	 * @param mixed $value Cache value
	 * @param timestamp $valid_for
	 */
	public function addValue($name, $value, $valid_for){
		if(isset($this->cacheValues[$name])){
			$this->cacheValues[$name]['value']=$value;
			$this->cacheValues[$name]['valid_for']=$valid_for;
			$this->updateCacheValue($name, $value, $valid_for);
		}else{
			$this->cacheValues[$name]['value']=$value;
			$this->cacheValues[$name]['valid_for']=$valid_for;
			$this->addCacheValue($name, $value, $valid_for);
		}
		
	}
	
	/**
	 * Clear cache
	 */
	public function clear(){
		$this->cacheValues=array();
		$this->clearCache();
	}
	
	/**
	 * COnstructor
	 */
	private function __construct(){
		$this->SiteBill();
		$query="CREATE TABLE IF NOT EXISTS `".DB_PREFIX."_cache` (
  `parameter` varchar(200) NOT NULL,
  `value` text NOT NULL,
  `created_at` int(15) NOT NULL,
  `valid_for` int(15) NOT NULL,
  PRIMARY KEY (`parameter`)
) ENGINE=MyISAM DEFAULT CHARSET=".DB_ENCODING.";";
		$this->db->exec($query);
		$this->loadCache();
	}
	
	/**
	 * Clone object
	 */
	private function __clone(){
		
	}
	
	/**
	 * Load cache values from DB
	 */
	private function loadCache(){
		$query='SELECT parameter, value, valid_for FROM '.DB_PREFIX.'_cache';
		//echo $query;
		$this->db->exec($query);
		while($this->db->fetch_assoc()){
			$this->cacheValues[$this->db->row['parameter']]['valid_for']=$this->db->row['valid_for'];
		}
	}
		
	/**
	 * Delete cache value from DB
	 * @param string $name Cache parameter name
	 */
	private function deleteCacheValue($name){
		$query="DELETE FROM ".DB_PREFIX."_cache WHERE parameter='".$name."'";
		$this->db->exec($query);
	}
	
	/**
	 * Add new cache value to DB
	 * @param string $name Cache parameter name
	 * @param mixed $value Cache value
	 * @param timestamp $valid_for
	 */
	private function addCacheValue($name, $value, $valid_for){
		$query="INSERT INTO ".DB_PREFIX."_cache (parameter,value,created_at,valid_for) VALUES ('".$name."','".mysql_real_escape_string(serialize($value))."', ".time().", ".$valid_for.")";
		$this->db->exec($query);
	}
	
	/**
	 * Update cache value in DB
	 * @param string $name Cache parameter name
	 * @param mixed $value Cache value
	 * @param timestamp $valid_for
	 */
	private function updateCacheValue($name, $value, $valid_for){
		$query="UPDATE ".DB_PREFIX."_cache SET value='".mysql_real_escape_string(serialize($value))."', valid_for='".$valid_for."' WHERE parameter='".$name."'";
		$this->db->exec($query);
	}
	
	/**
	 * Clear cache table
	 */
	private function clearCache(){
		$query='TRUNCATE TABLE '.DB_PREFIX.'_cache';
		$this->db->exec($query);
	}
	
	/**
	 * Extract cache value from DB
	 * @param string $name Cache parameter name
	 * @return mixed
	 */
	private function extractValue($name){
		$query="SELECT value FROM ".DB_PREFIX."_cache WHERE parameter='".$name."'";
		$this->db->exec($query);
		$this->db->fetch_assoc();
		return unserialize($this->db->row['value']);
	}
	
	/*
	public function printCache(){
		echo '<pre>';
		print_r($this->cacheValues);
	}
	*/
}