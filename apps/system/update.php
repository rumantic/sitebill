<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
class system_update extends SiteBill {
    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
        if(file_exists(SITEBILL_DOCUMENT_ROOT.'/inc/db.inc.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/install')){
        	$msgs=array();

        	self::removeDirectory(SITEBILL_DOCUMENT_ROOT.'/install', $msgs);

        	if(count($msg)>0){
        		foreach($msgs as $msg){
        			echo $msg.'<br/>';
        		}
        	}
        }
    }
    
    public static function removeDirectory($dir, &$msg=array()) {
    	$files = scandir($dir);
    	
    	if(count($files)>2){
    		foreach($files as $file){
    			if($file!='.' && $file!='..'){
    				if(is_dir($dir.'/'.$file)){
    					self::removeDirectory($dir.'/'.$file, $msg);
    				}elseif(is_writable($dir.'/'.$file)){
    					@unlink($dir.'/'.$file);
    				}else{
    					$msg[]='Файл/директория '.$file.' не удален. Удалите его самостоятельно.';
    				}
    			}
    		}
    	}
    	 
    	if(is_writable($dir)){
    		rmdir($dir);
    	}else{
    		$msg[]='Файл/директория '.$dir.' не удален. Удалите его самостоятельно.';
    	}
    }
    
    function main () {
        $query_data[] = "alter table ".DB_PREFIX."_topic add column name_en varchar(255)";
        $query_data[] = "alter table ".DB_PREFIX."_data add column meta_title text";
        $query_data[] = "alter table ".DB_PREFIX."_data add column meta_keywords text";
        $query_data[] = "alter table ".DB_PREFIX."_data add column meta_description text";
        $query_data[] = "alter table ".DB_PREFIX."_data add column geo_lat decimal(9,6) DEFAULT NULL";
        $query_data[] = "alter table ".DB_PREFIX."_data add column geo_lng decimal(9,6) DEFAULT NULL";
        
        $query_data[] = "alter table ".DB_PREFIX."_topic add column meta_title text";
        $query_data[] = "alter table ".DB_PREFIX."_topic add column meta_keywords text";
        $query_data[] = "alter table ".DB_PREFIX."_topic add column meta_description text";
        
        
        $query_data[] = "drop index dna_key_idx on ".DB_PREFIX."_dna";
        $query_data[] = "create unique index dna_key_idx on ".DB_PREFIX."_dna (group_id, component_id, function_id)";
        
        $query_data[] = "alter table ".DB_PREFIX."_data add column premium_status_end int(11) not null default 0";
        $query_data[] = "alter table ".DB_PREFIX."_data add column bold_status_end int(11) not null default 0";
        $query_data[] = "alter table ".DB_PREFIX."_data add column vip_status_end int(11) not null default 0";
        
        $query_data[] = "alter table ".DB_PREFIX."_country add column url varchar(255)";
        $query_data[] = "alter table ".DB_PREFIX."_country add column description text";
        $query_data[] = "alter table ".DB_PREFIX."_country add column meta_title text";
        $query_data[] = "alter table ".DB_PREFIX."_country add column meta_description text";
        $query_data[] = "alter table ".DB_PREFIX."_country add column meta_keywords text";
        
        $query_data[] = "alter table ".DB_PREFIX."_uploadify add column element varchar(255)";
        
        $query_data[] = "alter table ".DB_PREFIX."_bill add column payment_sum varchar(255) NOT NULL";
        $query_data[] = "alter table ".DB_PREFIX."_bill add column bdirect TINYINT NOT NULL";
		
		$query_data[] = "alter table ".DB_PREFIX."_bill add column payment_sum_robokassa decimal(10,2) NOT NULL";
        $query_data[] = "alter table ".DB_PREFIX."_bill add column payment_type varchar(100) NOT NULL";
		
		$query_data[] = "alter table ".DB_PREFIX."_bill add column payment_params TEXT NOT NULL";

        $rs = '<h3>'.Multilanguage::_('SQL_NOW','system').'</h3>';
        foreach ( $query_data as $query ) {
        	$this->db->exec($query);
        	if ( !$this->db->success ) {
        		$rs .= Multilanguage::_('ERROR_ON_SQL_RUN','system').': '.$query.', <b>'.$this->db->error.'</b><br>';
        	} else {
        		$rs .= Multilanguage::_('QUERY_SUCCESS','system').': '.$query.'<br>';
        	}
        }
        $rs .= 'start update dependency<br>';
        $system_version = $this->get_app_version('system');
        $rs .= $this->get_dependency($system_version);
        $rs .= $this->update_language_structure();
        return $rs;
    }
    
    function update_language_structure () {
    	$languages = Multilanguage::foreignLanguages();
    	foreach ( $languages as $language_id => $language_title ) {
    		$query = "alter table ".DB_PREFIX."_menu_structure add column name_".$language_id." varchar(255)";
    		$this->db->exec($query);
    		
    		$query = "alter table ".DB_PREFIX."_topic add column name_".$language_id." varchar(255)";
    		$this->db->exec($query);
    	}
    }
    
    function get_dependency ( $version ) {
    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/sitebill/admin/admin.php');
    	$sitebill_admin = new sitebill_admin();
    	if ( version_compare($version, '2.6.1', '>') and !$this->get_app_version('menu')) {
    		//get apps.menu
    		$rs .= $sitebill_admin->update_app('menu');
    	}
    	$rs .= $sitebill_admin->update_app('geodata');
    	if ( !$this->get_app_version('logger') ) {
    		$rs .= $sitebill_admin->update_app('logger');
    	}
    	if ( !$this->get_app_version('customentity') ) {
    		$rs .= $sitebill_admin->update_app('customentity');
    	}
    	if ( !$this->get_app_version('toolbox') ) {
    		$rs .= $sitebill_admin->update_app('toolbox');
    	}
    	//if ( !$this->get_app_version('api') ) {
    		$rs .= $sitebill_admin->update_app('api');
    	//}
    	if ( !$this->get_app_version('realtyview') ) {
    		$rs .= $sitebill_admin->update_app('realtyview');
    	}
    	 
    	 
    	 
    	$rs .= 'all dependency updated<br>';
    	return $rs;
    } 
    
    function get_app_version ( $app_name ) {
    	require_once SITEBILL_DOCUMENT_ROOT.'/third/simple_html_dom/simple_html_dom.php';
    	$apps_dir = SITEBILL_DOCUMENT_ROOT.'/apps';
    	
    	$version = false;
    	 
    	if ( is_file($apps_dir.'/'.$app_name.'/'.$app_name.'.xml') ) {
    		//Parsing by simple_xml_dom
    		$xml = @file_get_html($apps_dir.'/'.$app_name.'/'.$app_name.'.xml');
    		if($xml && is_object($xml)){
    			//$title=SiteBill::iconv('UTF-8', 'UTF-8', $xml->find('administration',0)->find('menu',0)->innertext());
    			$action=(string)$xml->find('name',0)->innertext();
    			$version=(string)$xml->find('version',0)->innertext();
    		}
    	}
    	return $version;
    }
}
