<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Service menu fronend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class menu_site extends menu_admin {
	
	function frontend () {
		return false;
    }
    
}