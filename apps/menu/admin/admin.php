<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
* Service menu backend
 * @author Abushyk Kostyantyn <abushyk@gmail.com> http://www.sitebill.ru
 */
class menu_admin extends Object_Manager {
	
	private $pages=array();
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
    }
    
    public function _preload(){
    	$this->loadMenus();
    }
    
    private function loadMenus(){
    	global $smarty;
    	$ra = array();
    	$DBC=DBC::getInstance();
    	
    	$query = "SELECT ms.*, m.tag, m.name as menu_title FROM ".DB_PREFIX."_menu m, ".DB_PREFIX."_menu_structure ms WHERE m.menu_id=ms.menu_id ORDER BY ms.sort_order";
    	$stmt=$DBC->query($query);
    	if($stmt){
    		$trailing_slashe='/';
    		if(1==(int)$this->getConfigValue('apps.seo.no_trailing_slashes')){
    			$trailing_slashe='';
    		}
    		while ( $ar=$DBC->fetch($stmt) ) {
    			if ( isset($ar['name_'.Multilanguage::get_current_language()]) && $ar['name_'.Multilanguage::get_current_language()] != '' ) {
    				 $ar['name'] = $ar['name_'.Multilanguage::get_current_language()];
    			}
    			$ar['url']=trim($ar['url']);
    			if($ar['url']!='' && 0!==strpos($ar['url'], 'http:')){
    				$ar['url'] = SITEBILL_MAIN_URL.'/'.trim($ar['url'], '/').((false===strpos($ar['url'], '.') && $ar['url']!='#') ? $trailing_slashe : '');
    			}
    			$ra[$ar['tag']][] = $ar;
    			
    		}
    	}
    	if(!empty($ra)){
    		foreach ( $ra as $tag => $menu_structure ) {
    			$smarty->assign($tag, $menu_structure);
    			$tag_title = $tag.'_title';
    			$smarty->assign($tag_title, $menu_structure[0]['menu_title']);
    		}
    	}
    }
    
    public function sitemap($sitemap){
    	$urls=array();
    	
    	$priority=$this->getConfigValue('apps.sitemap.priority.menu');
    	$changefreq=$this->getConfigValue('apps.sitemap.changefreq.menu');
    	
    	$DBC=DBC::getInstance();
    	$query='SELECT url FROM '.DB_PREFIX.'_menu_structure';
    	$stmt=$DBC->query($query);
    	$domain=$_SERVER['HTTP_HOST'];
    	if($stmt){
    		if(1==(int)$this->getConfigValue('apps.seo.no_trailing_slashes')){
    			$trailing_slashe='';
    		}else{
    			$trailing_slashe='/';
    		}
    		while($ar=$DBC->fetch($stmt)){
    			$url=trim($ar['url']);
    			//echo $url.'<br>';
    			
    			$url=trim(str_replace('\\', '/', $url),'/');
    			if($url=='' || $url=='#'){
    				$url='';
    			}elseif(preg_match('/^(http:|https:)/', $url) && preg_match('/'.$domain.'/', $url)){
    				//$url=trim(preg_replace('/^(((http:|https:)\/\/?)'.$domain.')/', '', $url), '/');
    				$url=trim($url);
    				/*if(parse_url($url, PHP_URL_HOST)==$domain){
    					$url=preg_replace('/^(((http:|https:)\/\/?)'.$domain.')/', '', $url);
    				}else{
    					$url='';
    				}*/
    			}elseif(preg_match('/^(http:|https:)/', $url)){
    				$url='';
    			}elseif(preg_match('/^'.$domain.'/', $url)){
    				$url=trim(preg_replace('/^('.$domain.')/', '', $url), '/');
    			}else{
    				//explode
    			}
    			if($url!=''){
    				if(preg_match('/^(http:|https:)/', $url)){
    					//$url=SITEBILL_MAIN_URL.'/'.$url;
    				}else{
    					if(strpos($url, '.')){
    						$url=SITEBILL_MAIN_URL.'/'.$url;
    					}else{
    						$url=SITEBILL_MAIN_URL.'/'.$url.$trailing_slashe;
    					}
    				}
    				$urls[]=array('url'=>$url, 'changefreq'=>$sitemap->validateFrequency($changefreq), 'priority'=>$sitemap->validatePriority($priority));
    			}
    			//echo $url.'<br>';
    			//echo '<hr>';
    			/*if(trim($ar['url'])!='' && trim($ar['url'])!='#'){
    				if(preg_match('/^(http:|https:)/', $ar['url']) && parse_url($u['url'], PHP_URL_HOST)==$domain){
    					
    				}else{
    					
    				}
    				$url=trim(str_replace('\\', '/', $ar['url']),'/');
    				
    			}*/
    			
    		}
    	}
    	
    	return $urls;
    }
}