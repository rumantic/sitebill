<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Static pages handler fronend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class page_site extends page_admin {
	
	function frontend () {
		if ( !$this->getConfigValue('apps.page.enable') ) {
			return false;
		}
		
		$REQUESTURIPATH=Sitebill::getClearRequestURI();
		
		if(preg_match('/^blog(\/?)$/', $REQUESTURIPATH, $matches)){
			$rs=$this->showBlog();
			return true;
		} elseif (preg_match('/^recommendations(\/?)$/', $REQUESTURIPATH, $matches)) {
			//@todo:Надо переопределить ключевое слово recommendations на определение URL топика (И мета инфу для топика получать из топиков новостей, это чтобы не плодить лишних сущностей)
			$rs=$this->showBlogCategory();
			return true;
		}else{
			if ( $_SERVER['REQUEST_URI'] == SITEBILL_MAIN_URL.'/' and ($page_array = $this->getPageByURI('index.html')) ) {
					
			} else {
				$page_array=$this->getPageByURI($REQUESTURIPATH);
			}
			if($page_array){
				
				if ( preg_match('/roadmap/', $_SERVER['REQUEST_URI']) ) {
					$map_array = $this->getPageByURI('map');
					$this->template->assert('main', '<div class="apppage_wrapper">'.$page_array['body'].$map_array['body'].'</div>');
				} else {
					$this->template->assert('main', '<div class="apppage_wrapper">'.$page_array['body'].'</div>');
				}
				
				if(isset($page_array['template']) && $page_array['template']!=''){
					$tplname=str_replace(array('./', '../'), '', $page_array['template']);
					if(file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/'.$tplname)){
						global $smarty;
						$this->template->assert('main', $smarty->fetch(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/'.$tplname));
					}
				}
			
				$this->template->assert('title', $page_array['title']);
				$this->template->assert('breadcrumbs', $this->get_breadcrumbs(array('<a href="'.SITEBILL_MAIN_URL.'/">'.Multilanguage::_('L_HOME').'</a>',$page_array['title'])));
			
				$this->template->assert('meta_title', $page_array['meta_title']);
				$this->template->assert('meta_keywords', $page_array['meta_keywords']);
				$this->template->assert('meta_description', $page_array['meta_description']);
				$this->template->assert('apps_page_view', 1);
				
				$this->template->render();
				$rs = $this->template->toHTML();
				return true;
			}
		}
		
		
		
		return false;
    }
    
    
    private function showBlogCategory(){
    	$blogRecords=array();
    	$DBC=DBC::getInstance();
    	$page=((int)$this->getRequestValue('page')>0 ? (int)$this->getRequestValue('page') : 1);
    	$per_page=$this->getConfigValue('apps.page.per_page');
    	 
    	$start=($page-1)*$per_page;
    	$query='SELECT SQL_CALC_FOUND_ROWS * FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE topic_id=1 ORDER BY '.$this->primary_key.' DESC LIMIT '.$start.', '.$per_page;
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$ar['href']=SITEBILL_MAIN_URL.'/'.trim($ar['uri'], '/');
    			$fp=strpos($ar['body'], '<p>');
    			$lp=strpos($ar['body'], '</p>');
    			if($fp!==false && $lp!==false){
    				$ar['body']=strip_tags(mb_substr($ar['body'], $fp, $lp));
    			}else{
    				$ar['body']=mb_substr(strip_tags($ar['body']), 0, 200);
    			}
    			$blogRecords[]=$ar;
    		}
    	}
    	 
    	$total=0;
    	$query='SELECT FOUND_ROWS() AS ttl';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		$total=$ar['ttl'];
    	}
    	 
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/page_navigator.php')){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/page_navigator.php';
    		$url='';
    		if(isset($params['pager_url'])){
    			$url=$params['pager_url'];
    			unset($params['pager_url']);
    		}
    		 
    		if($params['admin']){
    			$nurl='account/data';
    		}else{
    			$nurl=$pageurl;
    		}
    		//print_r($params);
    
    
    		$paging=Page_Navigator::getPagingArray($total, $page, $per_page, array(), 'blog');
    
    		$this->template->assert('blog_pager_array', $paging);
    	}else{
    		$pager_params['page_url']='blog';
    		$this->template->assert('blog_pager', $this->get_page_links_list ($page, $total, $per_page, $pager_params ));
    	}
    	 
    	 
    	 
    	 
    	 
    	$this->template->assert('title', 'Рекомендации');
    	$this->template->assert('meta_title', 'Рекомендации');
    	$this->template->assert('blogRecords', $blogRecords);
    	$this->set_apps_template('page', $this->getConfigValue('theme'), 'main_file_tpl', 'blog_grid.tpl');
    }
    
    
    
    private function showBlog(){
    	$blogRecords=array();
    	$DBC=DBC::getInstance();
    	$page=((int)$this->getRequestValue('page')>0 ? (int)$this->getRequestValue('page') : 1);
    	$per_page=$this->getConfigValue('apps.page.per_page');
    	
    	$start=($page-1)*$per_page;
    	$query='SELECT SQL_CALC_FOUND_ROWS * FROM '.DB_PREFIX.'_'.$this->table_name.' ORDER BY '.$this->primary_key.' DESC LIMIT '.$start.', '.$per_page;
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$ar['href']=SITEBILL_MAIN_URL.'/'.trim($ar['uri'], '/');
    			$fp=strpos($ar['body'], '<p>');
    			$lp=strpos($ar['body'], '</p>');
    			if($fp!==false && $lp!==false){
    				$ar['body']=strip_tags(mb_substr($ar['body'], $fp, $lp));
    			}else{
    				$ar['body']=mb_substr(strip_tags($ar['body']), 0, 200);
    			}
    			$blogRecords[]=$ar;
    		}
    	}
    	
    	$total=0;
    	$query='SELECT FOUND_ROWS() AS ttl';
    	$stmt=$DBC->query($query);
    	if($stmt){
    		$ar=$DBC->fetch($stmt);
    		$total=$ar['ttl'];
    	}
    	
    	if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/page_navigator.php')){
    		require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/view/page_navigator.php';
    		$url='';
    		if(isset($params['pager_url'])){
    			$url=$params['pager_url'];
    			unset($params['pager_url']);
    		}
    		 
    		if($params['admin']){
    			$nurl='account/data';
    		}else{
    			$nurl=$pageurl;
    		}
    		//print_r($params);
    		
    		
    		$paging=Page_Navigator::getPagingArray($total, $page, $per_page, array(), 'blog');
    		
    		$this->template->assert('blog_pager_array', $paging);
    	}else{
    		$pager_params['page_url']='blog';
    		$this->template->assert('blog_pager', $this->get_page_links_list ($page, $total, $per_page, $pager_params ));
    	}
    	
    	
    	
    	
    	
    	$this->template->assert('title', 'Блог');
    	$this->template->assert('meta_title', 'Блог');
    	$this->template->assert('blogRecords', $blogRecords);
    	$this->set_apps_template('page', $this->getConfigValue('theme'), 'main_file_tpl', 'blog_grid.tpl');
    }
    
	function getPageByURI($uri){
		
        $uri = mysql_real_escape_string($uri);
        /*if ( SITEBILL_MAIN_URL != '' ) {
        	$uri = str_replace(SITEBILL_MAIN_URL, '', $uri);
        }
        
        $uri = str_replace('/', '', $uri);*/
        
    	$query = "SELECT * FROM ".DB_PREFIX."_".$this->table_name." WHERE uri='".$uri."'";
    	//echo $query;
    	$this->db->exec($query);
    	if($this->db->success){
	    	$this->db->fetch_assoc();
	    	if($this->db->row['page_id']>0){
	    		return $this->db->row;
	    	}
    	}
    	return 0;
    }
}