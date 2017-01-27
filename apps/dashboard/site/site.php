<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Dashboard fronend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class dashboard_site extends dashboard_admin {
	function frontend () {
            if ( !$this->getConfigValue('apps.dashboard.enable') ) {
                return false;
            }
	}
}