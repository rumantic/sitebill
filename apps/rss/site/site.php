<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * RSS v2.0 Exporter at Harvard Law (http://cyber.law.harvard.edu/rss/rss.html) fronend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class rss_site extends rss_admin {
	
	function frontend () {
		
			
		if ( !$this->getConfigValue('apps.rss.enable') ) {
			return false;
		}
		
		$REQUESTURIPATH=$this->getClearRequestURI();
		if(!preg_match('/^rss(\/(.*)?)?$/', $REQUESTURIPATH)){
			return false;
		}
		
		if ( preg_match('/^rss\/data[\/]?$/', $REQUESTURIPATH) && 1==$this->getConfigValue('apps.rss.enable_realty')) {
			header('Content-Type:text/xml');
			$this->exportRssData();
			exit();
			return true;
		}elseif ( preg_match('/^rss[\/]?$/', $REQUESTURIPATH) ) {
			header('Content-Type:text/xml');
			$this->exportRssNews();
			exit();
			return true;
		}elseif ( preg_match('/^rss\/articles[\/]?$/', $REQUESTURIPATH) ) {
			
			header('Content-Type:text/xml');
			$this->exportRssArticles();
			
			exit();
			return true;
		}/*elseif ( preg_match('/^rss\/dataf[\/]?$/', $REQUESTURIPATH) ) {
			header('Content-Type:text/xml');
			$this->generateRSSTextF();
			exit();
			return true;
		}*/
		return false;
	}
}