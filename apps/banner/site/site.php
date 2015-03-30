<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Banner admin frontend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class banner_site extends banner_admin {
    /**
     * 
     */
    function frontend () {
    	global $smarty;
    	$banners=array();
    	$banners=$this->get_banners_list();
    	if(count($banners)>0){
    		foreach ($banners as $v){
    			$banner_str='';
    			if($v['url']!=''){
    				$banner_str='<a href="'.$v['url'].'">'.$v['body'].'</a>';
    			}else{
    				$banner_str=$v['body'];
    			}
    			$this->template->assert($v['title'], $banner_str);
    		}
    	}
    	
    }
}
