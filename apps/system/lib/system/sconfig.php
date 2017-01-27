<?php
class SConfig {
	
	public static $instance;
	private static $config_array=array();
	
	public static function getInstance() {
		if (! self::$instance) {
			self::$instance = new self ( );
		}
		return self::$instance;
	}
	
	public function getConfig(){
		return self::$config_array;
	}
	
	public function getConfigValue($key){
		if ( isset(self::$config_array[$key]) ) {
    	    return self::$config_array[$key];
    	}
    	return false;
	}
	
	public function setConfigValue($key, $value){
		self::$config_array[$key]=$value;
	}
	
	private function __construct() {
		self::loadConfig();
	}
	
	private static function loadConfig () {
		
		self::$config_array['per_page'] = 25;
		self::$config_array['site_title'] = 'Агентство недвижимости';
	
		self::$config_array['auto_image_big_width'] = 800;
		self::$config_array['auto_image_big_height'] = 600;
	
		self::$config_array['auto_image_preview_width'] = 200;
		self::$config_array['auto_image_preview_height'] = 200;
	
		self::$config_array['data_image_big_width'] = 1000;
		self::$config_array['data_image_big_height'] = 800;
	
		self::$config_array['data_image_preview_width'] = 300;
		self::$config_array['data_image_preview_height'] = 300;
	
		self::$config_array['shop_product_image_big_width'] = 800;
		self::$config_array['shop_product_image_big_height'] = 600;
	
		self::$config_array['shop_product_image_preview_width'] = 200;
		self::$config_array['shop_product_image_preview_height'] = 200;
	
		self::$config_array['vendor_image_big_width'] = 800;
		self::$config_array['vendor_image_big_height'] = 600;
	
		self::$config_array['vendor_image_preview_width'] = 50;
		self::$config_array['vendor_image_preview_height'] = 50;
	
		 
		 
		self::$config_array['topic_image_big_width'] = 800;
		self::$config_array['topic_image_big_height'] = 600;
		 
		self::$config_array['topic_image_preview_width'] = 200;
		self::$config_array['topic_image_preview_height'] = 200;
	
		$DBC=DBC::getInstance();
		$query='SELECT `config_key`, `value` FROM '.DB_PREFIX.'_config';
		$stmt=$DBC->query($query);
		if ( $stmt ) {
			while($ar=$DBC->fetch($stmt)){
				self::$config_array[$ar['config_key']] = $ar['value'];
			}
		}
		if(isset(self::$config_array['apps.realty.data_image_preview_width'])){
			self::$config_array['data_image_preview_width'] = self::$config_array['apps.realty.data_image_preview_width'];
		}
		 
		if(isset(self::$config_array['apps.realty.data_image_preview_height'])){
			self::$config_array['data_image_preview_height'] = self::$config_array['apps.realty.data_image_preview_height'];
		}
		 
		if(isset(self::$config_array['apps.realty.data_image_big_width'])){
			self::$config_array['data_image_big_width'] = self::$config_array['apps.realty.data_image_big_width'];
		}
		 
		if(isset(self::$config_array['apps.realty.data_image_big_height'])){
			self::$config_array['data_image_big_height'] = self::$config_array['apps.realty.data_image_big_height'];
		}
		//$core_domain='estatecms.ru';
		$core_domain=trim(self::$config_array['core_domain']);
		if($core_domain!=''){
			self::loadSubdomenalConfig($core_domain);
		}
		//var_dump(self::$config_array['apps.language.default_lang_code']);

		/*if(isset($_SESSION['user_domain_owner']) && isset($_SESSION['user_domain_owner']['theme']) && $_SESSION['user_domain_owner']['theme']!=''){
			self::$config_array['theme'] = $_SESSION['user_domain_owner']['theme'];
		}*/
	}
	
	private static function loadSubdomenalConfig($core_domain=''){
		$uri=$_SERVER['HTTP_HOST'];
		$uri=preg_replace('/^www\./', '', $uri);
		if($uri!=$core_domain){
			$subdomain=preg_replace('/\.'.$core_domain.'$/', '', $uri);
		}else{
			$subdomain='_core';
		}
		$subdomenal_config=$_SERVER['DOCUMENT_ROOT'].'/'.$subdomain.'.config.php';
		
		if(file_exists($subdomenal_config)){
			$subdomenal_settings=parse_ini_file($subdomenal_config, true);
			if(is_array($subdomenal_settings)){
				self::$config_array=array_merge(self::$config_array, $subdomenal_settings);
			}
		}
	}
}