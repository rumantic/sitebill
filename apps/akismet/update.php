<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once (SITEBILL_DOCUMENT_ROOT . '/apps/akismet/admin/admin.php');
class akismet_update extends akismet_admin {
    /**
     * Construct
     */
    function __construct() {
        $this->sitebill();
    }
    
    function main ($secret_key = '') {
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
        $config_admin = new config_admin();
        if ( !$config_admin->check_config_item('apps.akismet.enable') ) {
	    $config_admin->addParamToConfig('apps.akismet.enable','0','Включить антиспам Akismet', 1);
        }
        
	if ( !$config_admin->check_config_item('apps.akismet.key') ) {
	    $config_admin->addParamToConfig('apps.akismet.key','','Ключ akismet. <a href="https://akismet.com/" target="_blank">Получить ключ</a>');
        }
	$rs = 'Обновление конфигурации';
        return $rs;
    }
}