<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * mailbox fronend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class language_site extends language_admin {
	function frontend () {
		return false;
	}
}