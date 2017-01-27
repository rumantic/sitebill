<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Dashboard admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */

class dashboard_admin extends Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        //Multilanguage::appendAppDictionary('dashboard');
        $this->action = 'dashboard';
        
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
        $config_admin = new config_admin();
         
        if ( !$config_admin->check_config_item('apps.dashboard.enable') ) {
        	$config_admin->addParamToConfig('apps.dashboard.enable','0','Включить приложение Помощник',1);
        }
    }
    
    public function _preload(){
    	if ( $this->getConfigValue('apps.dashboard.enable') ) {
            $this->template->assert('dashboard', $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/dashboard/admin/template/start_dashboard_js_code.tpl'));
	}
    }
    
    public function ajax () {
        if ( $this->getRequestValue('action') == 'iframe' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/config_mask.php');
            $CM=new Config_Mask();
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/form/form_generator.php');
            $form_generator = new Form_Generator();
            
            $theme_items['name'] = 'theme';
            $theme_items['select_data'] = $CM->get_themes_array();
            $theme_items['value'] = $this->getConfigValue('theme');
            
            $this->template->assign('theme_select', $form_generator->get_select_box($theme_items));
            
            
            echo $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/dashboard/admin/template/main_dashboard.tpl');
        } elseif ( $this->getRequestValue('action') == 'save' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
            $config_admin = new config_admin();
            if ( $this->getRequestValue('theme') != '' ) {
		$DBC=DBC::getInstance();
		$query="UPDATE `".DB_PREFIX."_config` SET `value`=? WHERE `config_key`=?";
		$stmt=$DBC->query($query, array($this->getRequestValue('theme'), 'theme'));
		if ( $this->getRequestValue('theme') == 'novosel' ) {
		    $stmt=$DBC->query($query, array('3', 'bootstrap_version'));
		} else {
		    $stmt=$DBC->query($query, array('', 'bootstrap_version'));
		}
            }
            $this->clear_apps_cache();
            $ra['result'] = 'success';
            echo json_encode($ra);
            exit;
        } else {
            echo $this->template->fetch(SITEBILL_DOCUMENT_ROOT.'/apps/dashboard/admin/template/dashboard_iframe_code.tpl');
        }
    }
}
?>