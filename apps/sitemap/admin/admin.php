<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Sitemap admin backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class sitemap_admin extends Object_Manager {
	
	private $urls=array();
	private $site_link;
	private $priority;
	private $changefreq;
	private $output_file;
	//private $action='sitemap';
    /**
     * Constructor
     */
    function __construct( $realty_type = false ) {
        $this->SiteBill();
        Multilanguage::appendAppDictionary('sitemap');
        $this->checkConfiguration();
        $this->action='sitemap';
        $this->site_link='http://'.$_SERVER['SERVER_NAME'].(SITEBILL_MAIN_URL!='' ? SITEBILL_MAIN_URL.'/' : '/');
        $this->output_file=SITEBILL_DOCUMENT_ROOT.'/sitemap.xml';
    	
        $changefreq_values=array(
    		'1'=>array('always','всегда'),
	    	'2'=>array('hourly','ежечасно'),
	    	'3'=>array('daily','ежедневно'),
	    	'4'=>array('weekly','еженедельно'),
	    	'5'=>array('monthly','ежемесячно'),
	    	'6'=>array('yearly','ежегодно'),
	    	'0'=>array('never','никогда')
		);
        
        $this->priority['news']=str_replace(',', '.', $this->getConfigValue('apps.sitemap.priority.news'));
    	
    	$this->priority['topic']=str_replace(',', '.', $this->getConfigValue('apps.sitemap.priority.topic'));
    	
    	$this->priority['country']=str_replace(',', '.', $this->getConfigValue('apps.sitemap.priority.country'));
    	
   	 	$this->priority['page']=str_replace(',', '.', $this->getConfigValue('apps.sitemap.priority.page'));
    	
    	$this->priority['menu']=str_replace(',', '.', $this->getConfigValue('apps.sitemap.priority.menu'));
    	
    	$this->priority['data']=str_replace(',', '.', $this->getConfigValue('apps.sitemap.priority.data'));
    	
    	$this->priority['company']=str_replace(',', '.', $this->getConfigValue('apps.sitemap.priority.company'));
    	
    	$this->priority['company_topic']=str_replace(',', '.', $this->getConfigValue('apps.sitemap.priority.company_topic'));
    	
    	$this->changefreq['news']=$changefreq_values[((int)$this->getConfigValue('apps.sitemap.changefreq.news')<7 ? (int)$this->getConfigValue('apps.sitemap.changefreq.news') : '6')][0];
    	$this->changefreq['topic']=$changefreq_values[((int)$this->getConfigValue('apps.sitemap.changefreq.topic')<7 ? (int)$this->getConfigValue('apps.sitemap.changefreq.topic') : '6')][0];
    	$this->changefreq['country']=$changefreq_values[((int)$this->getConfigValue('apps.sitemap.changefreq.country')<7 ? (int)$this->getConfigValue('apps.sitemap.changefreq.country') : '6')][0];
    	$this->changefreq['page']=$changefreq_values[((int)$this->getConfigValue('apps.sitemap.changefreq.page')<7 ? (int)$this->getConfigValue('apps.sitemap.changefreq.page') : '6')][0];
    	$this->changefreq['menu']=$changefreq_values[((int)$this->getConfigValue('apps.sitemap.changefreq.menu')<7 ? (int)$this->getConfigValue('apps.sitemap.changefreq.menu') : '6')][0];
    	
    	$this->changefreq['data']=$changefreq_values[((int)$this->getConfigValue('apps.sitemap.changefreq.data')<7 ? (int)$this->getConfigValue('apps.sitemap.changefreq.data') : '6')][0];
    	
    	$this->changefreq['company']=$changefreq_values[((int)$this->getConfigValue('apps.sitemap.changefreq.company')<7 ? (int)$this->getConfigValue('apps.sitemap.changefreq.company') : '6')][0];
    	
    	$this->changefreq['company_topic']=$changefreq_values[((int)$this->getConfigValue('apps.sitemap.changefreq.company_topic')<7 ? (int)$this->getConfigValue('apps.sitemap.changefreq.company_topic') : '6')][0];
    	
    	
		//print_r($this->priority);
		//print_r($this->changefreq);
    }
    
	function main () {
	    $rs=$this->getTopMenu();
	    
	    switch($this->getRequestValue('do')){
	    	case 'generate' : {
	    		$this->generateSitemap();
	    		$rs.=sprintf(Multilanguage::_('GENERATED','sitemap'), 'sitemap.xml');
	    		$rs .= Multilanguage::_('FILE_IS_ON','sitemap').': <a href="'.$this->site_link.'sitemap.xml'.'" target="_blank">'.$this->site_link.'sitemap.xml'.'</a><br>';
	    		break;
	    	}
	    	default : {
	    	
	    	}
	    }
	    return $rs;
	}
    
	private function generateSitemap(){
		
		$region_id=0;
		$host=$_SERVER['HTTP_HOST'];
		if($host=='erver.ru' || preg_match('/([a-z]+).erver.ru/', $host)){
			if(preg_match('/([a-z]+).erver.ru/', $host)){
				$region_alias=$host;
			}else{
				$region_alias='';
			}
			
			if($host!='erver.ru'){
				$this->output_file=str_replace('erver.ru', $host, $this->output_file);
			}
			
			
			
			if($region_alias!=''){
				$DBC=DBC::getInstance();
				$query='SELECT region_id FROM '.DB_PREFIX.'_region WHERE domain=? LIMIT 1';
				$stmt=$DBC->query($query, array($host));
				if($stmt){
					$ar=$DBC->fetch($stmt);
					$region_id=(int)$ar['region_id'];
				}
			}
		}
		
		
		
		
		
		
		if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/news/admin/admin.php') && 1==$this->getConfigValue('apps.news.enable')){
			$this->urls[]=array('url'=>'news/','changefreq'=>$this->changefreq['news'],'priority'=>$this->priority['news']);
		}
		
	    
		
		$level_enable=$this->getConfigValue('apps.seo.level_enable');
		$html_prefix_enable=$this->getConfigValue('apps.seo.html_prefix_enable');
		$data_alias_enable=$this->getConfigValue('apps.seo.data_alias_enable');
		
		require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
		$Structure_Manager = new Structure_Manager();
		$category_structure = $Structure_Manager->loadCategoryStructure();
		
		if(1==$this->getConfigValue('apps.sitemap.topics_enable')){
			$query='SELECT id, url FROM '.DB_PREFIX.'_topic';
			$this->db->exec($query);
			if($this->db->success){
				while($this->db->fetch_assoc()){
					 
					if(1==$level_enable){
						if($category_structure['catalog'][$this->db->row['id']]['url']!=''){
							$url=$category_structure['catalog'][$this->db->row['id']]['url'].'/';
						}else{
							$url='topic'.$this->db->row['id'].'.html';
						}
					}else{
						if($category_structure['catalog'][$this->db->row['id']]['url']!=''){
							$url=$category_structure['catalog'][$this->db->row['id']]['url'].'/';
						}else{
							$url='topic'.$this->db->row['id'].'.html';
						}
					}
					 
					$this->urls[]=array('url'=>$url,'changefreq'=>$this->changefreq['topic'],'priority'=>$this->priority['topic']);
				}
			}
		}
		
		if(1==$this->getConfigValue('apps.sitemap.country_enable')){
			$query='SELECT country_id, url FROM '.DB_PREFIX.'_country';
			$this->db->exec($query);
			if($this->db->success){
				while($this->db->fetch_assoc()){
					$url=$this->db->row['url'];
					if($url!=''){
						$this->urls[]=array('url'=>$url,'changefreq'=>$this->changefreq['country'],'priority'=>$this->priority['country']);
					}
				}
			}
		}
		
		$DBC=DBC::getInstance();
		$query='SELECT is_service FROM '.DB_PREFIX.'_page LIMIT 1';
		$stmt=$DBC->query($query);
		if($stmt){
			$query='SELECT uri FROM '.DB_PREFIX.'_page WHERE is_service=0';
		}else{
			$query='SELECT uri FROM '.DB_PREFIX.'_page';
		}
		
		
	    $this->db->exec($query);
	    if($this->db->success){
	    	while($this->db->fetch_assoc()){
	    		if($this->db->row['uri']!=''){
	    			$url=trim(str_replace('\\', '/', $this->db->row['uri']),'/').'/';
	    		}
	    		$this->urls[]=array('url'=>$url,'changefreq'=>$this->changefreq['page'],'priority'=>$this->priority['page']);
	    	}
	    }
		$query='SELECT url FROM '.DB_PREFIX.'_menu_structure';
	    $this->db->exec($query);
	    if($this->db->success){
	    	while($this->db->fetch_assoc()){
	    		if($this->db->row['url']!=''){
	    			$url=trim(str_replace('\\', '/', $this->db->row['url']),'/').'/';
	    		}
	    		$this->urls[]=array('url'=>$url,'changefreq'=>$this->changefreq['menu'],'priority'=>$this->priority['menu']);
	    	}
	    }
	    
	    
	    //Генерация урлов объявлений
	    if(1==$this->getConfigValue('apps.sitemap.data_enable')){
	    	
	    	
	    	
	        
	        $data=array();
	        if($region_id!=0){
	        	$query='SELECT `id`, `topic_id`'.(1==$data_alias_enable ? ', `translit_alias`' : '').' FROM '.DB_PREFIX.'_data WHERE `active`=1 AND `region_id`='.$region_id.' ORDER BY `id`';
	        }else{
	        	$query='SELECT `id`, `topic_id`'.(1==$data_alias_enable ? ', `translit_alias`' : '').' FROM '.DB_PREFIX.'_data WHERE `active`=1 ORDER BY `id`';
	        }
	        
	        $this->db->exec($query);
	        while($this->db->fetch_assoc()){
	        	$data[]=$this->db->row;
	        }
	        
	       
	        
	        if(count($data)>0){
	        	foreach($data as $k=>$d){
	        		
	        		if(1==$level_enable){
	        			if($category_structure['catalog'][$d['topic_id']]['url']!=''){
	        				$data[$k]['parent_category_url']=$category_structure['catalog'][$d['topic_id']]['url'].'/';
	        			}else{
	        				$data[$k]['parent_category_url']='';
	        			}
	        		}else{
	        			$data[$k]['parent_category_url']='';
	        		}
	        		if(1==$data_alias_enable && $d['translit_alias']!=''){
	        			$data[$k]['href']=SITEBILL_MAIN_URL.'/'.$data[$k]['parent_category_url'].$d['translit_alias'];
	        		}elseif(1==$html_prefix_enable){
	        			$data[$k]['href']=SITEBILL_MAIN_URL.'/'.$data[$k]['parent_category_url'].'realty'.$data[$k]['id'].'.html';
	        		}else{
	        			$data[$k]['href']=SITEBILL_MAIN_URL.'/'.$data[$k]['parent_category_url'].'realty'.$data[$k]['id'];
	        		}
	        	}
	        	foreach($data as $k=>$d){
	        		$url=trim(str_replace('\\', '/', $d['href']),'/');
	        		$this->urls[]=array('url'=>$url,'changefreq'=>$this->changefreq['data'],'priority'=>$this->priority['data']);
	        	}
	        }
	    }
	    
	    if($this->getConfigValue('apps.company.enable') && $this->getConfigValue('apps.sitemap.company_enable')){
	    	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_implements.php');
	    	$Structure_Manager = Structure_Implements::getManager('company');
	    	$category_structure = $Structure_Manager->loadCategoryStructure();
	    	
    	 
			if(count($category_structure)>0){
				foreach($category_structure['catalog'] as $cs){
					if($cs['url']!=''){
						$url=SITEBILL_MAIN_URL.$this->getConfigValue('apps.company.namespace').'/'.$cs['url'];
					}else{
						$url=SITEBILL_MAIN_URL.$this->getConfigValue('apps.company.namespace').'/company'.$cs['id'];
					}
					$this->urls[]=array('url'=>$url,'changefreq'=>$this->changefreq['company_topic'],'priority'=>$this->priority['company_topic']);
				}
			}
	    	
	    	
	    	
	    	$ret=array();
	    	if($region_id!=0){
	    		$query='SELECT company_id, company_topic_id, alias FROM '.DB_PREFIX.'_company WHERE region_id='.$region_id.' ORDER BY company_id';
	    	}else{
	    		$query='SELECT company_id, company_topic_id, alias FROM '.DB_PREFIX.'_company ORDER BY company_id';
	    	}
	    	
	    	$this->db->exec($query);
	    	while($this->db->fetch_assoc()){
	    		$ret[]=$this->db->row;
	    	}
	    	
	    	if(count($ret)>0){
	    		foreach($ret as $k=>$v){
	    			 
	    			if(1==$level_enable){
						 
						if($category_structure['catalog'][$v['company_topic_id']]['url']!=''){
							$ret[$k]['parent_category_url']=$category_structure['catalog'][$v['company_topic_id']]['url'].'/';
						}else{
							$ret[$k]['parent_category_url']='';
						}
					}else{
						$ret[$k]['parent_category_url']='';
					}
					if($v['alias']==''){
						$ret[$k]['href']=SITEBILL_MAIN_URL.'/'.$this->getConfigValue('apps.company.namespace').'/'.$ret[$k]['parent_category_url'].'company'.$v['company_id'];
					}else{
						$ret[$k]['href']=SITEBILL_MAIN_URL.'/'.$this->getConfigValue('apps.company.namespace').'/'.$ret[$k]['parent_category_url'].$v['alias'];
					}
	    		}
	    		foreach($ret as $k=>$d){
	    			$url=trim(str_replace('\\', '/', $d['href']),'/');
	    			$this->urls[]=array('url'=>$url,'changefreq'=>$this->changefreq['company'],'priority'=>$this->priority['company']);
	    		}
	    	}
	    }
	    
	    
	    
	    
		$ret ='<?xml version="1.0" encoding="UTF-8"?>'."\n";
	    $ret.='<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
	    if(count($this->urls)>0){
	    	foreach($this->urls as $u){
	    		$ret.='<url>'."\n";
	    		if(preg_match('/^http:/', $u['url'])){
	    			$ret.='<loc>'.$u['url'].'</loc>'."\n";
	    		}else{
	    			$ret.='<loc>'.$this->site_link.$u['url'].'</loc>'."\n";
	    		}
				$ret.='<lastmod>'.date('Y-m-d',time()).'</lastmod>'."\n";
				$ret.='<changefreq>'.$u['changefreq'].'</changefreq>'."\n";
				$ret.='<priority>'.$u['priority'].'</priority>'."\n";
				$ret.='</url>'."\n";
	    	}
	    }
	    $ret.='</urlset>'."\n";
	    $f=fopen($this->output_file,'w');
    	fwrite($f,SiteBill::iconv(SITE_ENCODING, 'utf-8', $ret));
    	fclose($f);
    	chmod($this->output_file, 0755);
	}
	
    

	function getTopMenu () {
	    $rs = '';
	    $rs .= '<a href="?action='.$this->action.'&do=generate" class="btn btn-primary">'.Multilanguage::_('GENERATE','sitemap').' sitemap.xml</a>';
	    return $rs;
	}
	
	private function checkConfiguration(){
    	require_once SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php';
    	$CF=new config_admin();
    	if($CF){
    		if ( !$CF->check_config_item('apps.sitemap.priority.news') ) {
				$CF->addParamToConfig('apps.sitemap.priority.news','0.5','Приоритетность URL <b>раздела новостей</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
			}
    		
	    	if ( !$CF->check_config_item('apps.sitemap.priority.topic') ) {
				$CF->addParamToConfig('apps.sitemap.priority.topic','0.5','Приоритетность URL <b>категорий</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
			}
			
			if ( !$CF->check_config_item('apps.sitemap.priority.page') ) {
				$CF->addParamToConfig('apps.sitemap.priority.page','0.5','Приоритетность URL <b>статических страниц</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
			}
			
			if ( !$CF->check_config_item('apps.sitemap.priority.menu') ) {
				$CF->addParamToConfig('apps.sitemap.priority.menu','0.5','Приоритетность URL <b>дополнительных меню</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
			}
			
			if ( !$CF->check_config_item('apps.sitemap.priority.data') ) {
				$CF->addParamToConfig('apps.sitemap.priority.data','0.5','Приоритетность URL <b>объявлений</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
			}
			
			if ( !$CF->check_config_item('apps.sitemap.priority.country') ) {
				$CF->addParamToConfig('apps.sitemap.priority.country','0.5','Приоритетность URL <b>Стран</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
			}
			
			if ( !$CF->check_config_item('apps.sitemap.priority.company') ) {
				$CF->addParamToConfig('apps.sitemap.priority.company','0.5','Приоритетность URL <b>компании</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
			}
			
			if ( !$CF->check_config_item('apps.sitemap.priority.company_topic') ) {
				$CF->addParamToConfig('apps.sitemap.priority.company_topic','0.5','Приоритетность URL <b>разделов компаний</b> относительно других URL на Вашем сайте. Диапазон от 0.0 до 1.0');
			}
			
    		if ( !$CF->check_config_item('apps.sitemap.changefreq.news') ) {
				$CF->addParamToConfig('apps.sitemap.changefreq.news','3','Вероятная частота изменения <b>страницы раздела новостей</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
			}
			
			if ( !$CF->check_config_item('apps.sitemap.changefreq.country') ) {
				$CF->addParamToConfig('apps.sitemap.changefreq.country','3','Вероятная частота изменения <b>страницы Страны</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
			}
    		
	    	if ( !$CF->check_config_item('apps.sitemap.changefreq.topic') ) {
				$CF->addParamToConfig('apps.sitemap.changefreq.topic','3','Вероятная частота изменения <b>страницы категории</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
			}
			
			if ( !$CF->check_config_item('apps.sitemap.changefreq.page') ) {
				$CF->addParamToConfig('apps.sitemap.changefreq.page','5','Вероятная частота изменения <b>статической страницы</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
			}
			
			if ( !$CF->check_config_item('apps.sitemap.changefreq.menu') ) {
				$CF->addParamToConfig('apps.sitemap.changefreq.menu','5','Вероятная частота изменения <b>вспомогательных меню</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
			}
			
			if ( !$CF->check_config_item('apps.sitemap.changefreq.data') ) {
				$CF->addParamToConfig('apps.sitemap.changefreq.data','4','Вероятная частота изменения <b>объявления</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
			}
			
			if ( !$CF->check_config_item('apps.sitemap.changefreq.company') ) {
				$CF->addParamToConfig('apps.sitemap.changefreq.company','5','Вероятная частота изменения информации о <b>компании</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
			}
			
			if ( !$CF->check_config_item('apps.sitemap.changefreq.company_topic') ) {
				$CF->addParamToConfig('apps.sitemap.changefreq.company_topic','5','Вероятная частота изменения информации о <b>разделах компаний</b>. Одно из числовых значений (1-всегда, 2-ежечасно, 3-ежедневно, 4-еженедельно, 5-ежемесячно, 6-ежегодно, 7-никогда)');
			}
			
			if ( !$CF->check_config_item('apps.sitemap.data_enable') ) {
				$CF->addParamToConfig('apps.sitemap.data_enable','0','Выводить объявления в sitemap');
			}
			
			if ( !$CF->check_config_item('apps.sitemap.company_enable') ) {
				$CF->addParamToConfig('apps.sitemap.company_enable','0','Выводить компании в sitemap');
			}
			
			if ( !$CF->check_config_item('apps.sitemap.topics_enable') ) {
				$CF->addParamToConfig('apps.sitemap.topics_enable','1','Выводить категории в sitemap');
			}
			
			if ( !$CF->check_config_item('apps.sitemap.country_enable') ) {
				$CF->addParamToConfig('apps.sitemap.country_enable','0','Выводить страны в sitemap');
			}
    	}
    	unset($CF);
    }
	
	
	
}