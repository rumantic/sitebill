<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * realtyview admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */

class realtyview_admin extends Object_Manager {
    
    /**
     * Constructor
     */
    function __construct( $realty_type = false ) {
        parent::__construct();
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
        $config_admin = new config_admin();
         
        if ( !$config_admin->check_config_item('apps.realtyview.enable') ) {
        	$config_admin->addParamToConfig('apps.realtyview.enable','0','Включить приложение RealtyView');
        }
    }
    
    protected function _defaultAction(){
    	return '';
    }
   
}