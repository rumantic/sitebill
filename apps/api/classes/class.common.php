<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * API Common class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_Common extends SiteBill {
    /**
     * Constructor
     */
    function __construct() {
        $this->Sitebill();
    }
    
    function main () {
    	$do = $this->getRequestValue('do');
    	$action = '_'.$do;
    	if(!method_exists($this, $action)){
    		$action='_default';
    	}
    	
    	$rs .= $this->$action();
    	return $rs;
    }
    
    function _default () {
    	return $this->request_failed('method not defined');
    }
    
    function request_failed( $message ) {
    	$response = array('error' => $message);
    	return $this->json_string($response);
    }
    
    function json_string ( $in_array ) {
    	return json_encode($in_array);
    }
}