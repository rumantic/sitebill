<?php
/**
 * Sitemap frontend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class sitemap_site extends sitemap_admin {
	/**
	 * Constructor
	 */
	function __construct() {
		$this->Sitebill();
	}
	
	/**
	 * Frontend
	 */
	function frontend () {
		if ( !preg_match('/sitemap/', $_SERVER['REQUEST_URI']) ) {
			return false;
		}
		//get page list
		$query = "select * from ".DB_PREFIX."_page order by page_id";
		$this->db->exec($query);
		while ( $this->db->fetch_assoc() ) {
			$map_page[] = $this->db->row;
		}
		$this->template->assert('map_page', $map_page);
		
		//get news list
		$query = "select * from ".DB_PREFIX."_news order by news_id";
		$this->db->exec($query);
		while ( $this->db->fetch_assoc() ) {
			$map_news[] = $this->db->row;
		}
		$this->template->assert('map_news', $map_news);
		
		//get gallery list
		$query = "select * from ".DB_PREFIX."_gallery order by gallery_id";
		$this->db->exec($query);
		while ( $this->db->fetch_assoc() ) {
			$map_gallery[] = $this->db->row;
		}
		$this->template->assert('map_gallery', $map_gallery);
		$this->template->assert('title', 'Карта сайта');
		
		
		
		$template_full_path = $this->get_apps_template_full_path('sitemap', $this->getConfigValue('theme'), 'grid.tpl.html');
		$this->template->assert('main_file_tpl', $template_full_path);
		return true;
	}
}