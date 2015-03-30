<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * API Controller
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_Controller extends SiteBill {
    /**
     * Constructor
     */
    function __construct() {
        $this->Sitebill();
    }
    
    /**
     * Main
     */
    function main () {
    	$action = $this->getRequestValue('action');
    	$action = str_replace('/', '', $action);
    	$action = str_replace('.', '', $action);
    	
    	//first we need check session key for action other then oauth
    	if ( $action != 'oauth' and $action != 'server') {
    		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/api/classes/class.oauth.php');
    		$oauth = new API_oauth();
    		$result = $oauth->_check_session_key();
    		if ( $oauth->GetErrorMessage() == 'check_session_key_failed' ) {
    			echo $result;
    			exit;
    		}
    	}
    	
    	if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/api/classes/class.'.$action.'.php') ) {
    		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/api/classes/class.'.$action.'.php');
    		$class_name = 'API_'.$action;
    		$run_class_action = new $class_name;
    		echo $run_class_action->main();
    		exit;
    	} else {
    		echo 'api error';
    	}
    }
}
