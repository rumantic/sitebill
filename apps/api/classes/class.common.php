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
    
    function get_my_user_id () {
    	$session_key = $this->getRequestValue('session_key');
    	$DBC=DBC::getInstance();
    	$query = 'SELECT user_id FROM '.DB_PREFIX.'_oauth WHERE session_key=?';
    	$stmt=$DBC->query($query, array($session_key));
    	if ( $stmt ) {
    		$ar = $DBC->fetch($stmt);
    		if ( $ar['user_id'] > 0 ) {
    			return $ar['user_id'];
    		}
    	}
    	return false;
    }
    
    function request_failed( $message ) {
    	$response = array('error' => $message);
    	return $this->json_string($response);
    }
    
    function json_string ( $in_array ) {
    	return json_encode($in_array);
    }
}