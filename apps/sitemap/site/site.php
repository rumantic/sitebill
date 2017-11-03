<?php
/**
 * Sitemap frontend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class sitemap_site extends sitemap_admin {
		
	/**
	 * Frontend
	 */
	function frontend () {
		$REQUESTURIPATH=Sitebill::getClearRequestURI();
		
		if($REQUESTURIPATH=='sitemap.xml'){
			$sitemap_prefix=md5($_SERVER['HTTP_HOST']).'.';
			if(file_exists(SITEBILL_DOCUMENT_ROOT.'/cache/'.$sitemap_prefix.'sitemap.xml')){
				$sitemap_created_at=filemtime(SITEBILL_DOCUMENT_ROOT.'/cache/'.$sitemap_prefix.'sitemap.xml');
				$time_offset=(int)$this->getConfigValue('apps.sitemap.sitemaplivetime');
				if(($sitemap_created_at+$time_offset)<time()){
					$this->buildSitemap();
				}
			}else{
				$this->buildSitemap();
			}
			$page=(int)$_GET['page'];
			if($page>0 && file_exists(SITEBILL_DOCUMENT_ROOT.'/cache/'.$sitemap_prefix.'sitemap_page'.$page.'.xml')){
				header("Content-Type: text/xml");
				echo file_get_contents(SITEBILL_DOCUMENT_ROOT.'/cache/'.$sitemap_prefix.'sitemap_page'.$page.'.xml');
			}else{
				header("Content-Type: text/xml");
				echo file_get_contents(SITEBILL_DOCUMENT_ROOT.'/cache/'.$sitemap_prefix.'sitemap.xml');
			}
			
			
			exit();
		}
		
		if ($REQUESTURIPATH!='sitemap') {
			return false;
		}
		
		/*if ($REQUESTURIPATH=='sitemap') {
			$urls=$this->getSitemapItemsHTML();
			$this->template->assert('grid_items', $urls);
			$this->set_apps_template($this->action, $this->getConfigValue('theme'), 'main_file_tpl', 'grid.tpl.html');
			return true;
		}
		
		return false;*/
		$DBC=DBC::getInstance();
		
		$map_page=array();
		$query = "select * from ".DB_PREFIX."_page order by page_id";
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$map_page[] = $ar;
			}
		}
		
		$this->template->assert('map_page', $map_page);
		
		//get news list
		$map_news=array();
		$query = "select * from ".DB_PREFIX."_news order by news_id";
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$map_news[] = $ar;
			}
		}
		$this->template->assert('map_news', $map_news);
		
		//get gallery list
		$map_gallery=array();
		$query = "select * from ".DB_PREFIX."_gallery order by gallery_id";
		$stmt=$DBC->query($query);
		if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$map_gallery[] = $ar;
			}
		}
		$this->template->assert('map_gallery', $map_gallery);
		$this->template->assert('title', 'Карта сайта');
		$template_full_path = $this->get_apps_template_full_path('sitemap', $this->getConfigValue('theme'), 'grid.tpl.html');
		$this->template->assert('main_file_tpl', $template_full_path);
		return true;
	}
}