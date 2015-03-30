<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * SEO admin backend
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class seo_admin extends Object_Manager {
    /**
     * Constructor
     */
    function __construct() {
        $this->action = 'seo';
        $this->app_title = 'SEO-Оптимизация';
        
        $this->SiteBill();
        
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/config/admin/admin.php');
        $config_admin = new config_admin();
        
        if ( !$config_admin->check_config_item('apps.seo.html_prefix_enable') ) {
        	$config_admin->addParamToConfig('apps.seo.html_prefix_enable','1','Включить .html постфиксы в конце URL объявлений');
        }
         
        if ( !$config_admin->check_config_item('apps.seo.level_enable') ) {
        	$config_admin->addParamToConfig('apps.seo.level_enable','0','Включить SEO-режим с многоуровневым URL для каталогов. Прежде чем включить этот режим, внимательно ознакомьтесь с <a href="http://www.sitebill.ru/seo-level.html" target="_blank">инструкцией</a>');
        }
        
        if ( !$config_admin->check_config_item('apps.seo.data_alias_enable') ) {
        	$config_admin->addParamToConfig('apps.seo.data_alias_enable','0','Включить SEO-режим с расширенными ссылками объявлений.');
        }
        
        if ( !$config_admin->check_config_item('apps.seo.allow_custom_realty_aliases') ) {
        	$config_admin->addParamToConfig('apps.seo.allow_custom_realty_aliases','0','Разрешить установку нестандартных алиасов');
        }
    }
    
	function main(){
	    if ( $this->getRequestValue('do') == 'update' ) {
            $rs = $this->update_structure();	        
	    } elseif($this->getRequestValue('do') == 'update_data') {
	    	$rs = $this->update_data();
	    } else {
	        $rs = '<a href="?action=seo&do=update" class="btn btn-primary">Обновить структуру каталогов</a> ';
	        $rs .= '<a href="?action=seo&do=update_data" class="btn btn-primary">Обновить алиасы объявлений</a>';
	    }
	    
	    $rs_new = $this->get_app_title_bar();
	    $rs_new .= $rs;
	     
    	return $rs_new;
    }
    
    function update_data () {
    	$query = "SELECT id FROM ".DB_PREFIX."_data";
    	$DBC=DBC::getInstance();
    	$stmt=$DBC->query($query);
    	if($stmt){
    		while($ar=$DBC->fetch($stmt)){
    			$data[]=$ar['id'];
    		}
    	}
    	
    	if(!empty($data)){
    		foreach($data as $d){
    			$this->saveTranslitAlias($d);
    		}
    	}
    	
    	$rs .= "Обновлено<br>";
    	return $rs;
    }
    
    function update_structure () {
        $query = "select * from ".DB_PREFIX."_topic";
        $this->db->exec($query);
        while ( $this->db->fetch_assoc() ) {
            $ra[] = $this->db->row;
        }
        if ( !is_array($ra) ) {
            return 'Категории не найдены';
        }
        foreach ( $ra as $item_id => $item ) {
            if ( empty($item['url']) ) {
                $url = $this->transliteMe($item['name']);
                $query = "update ".DB_PREFIX."_topic set url='$url' where id=".$item['id'];
                //echo $query.'<br>';
                $this->db->exec($query);
                if ( $this->db->success ) {
                    $rs .= 'Категория '.$item['name']." успешно обновлена, установлен SEO-тег = ".$url."<br>";
                } else {
                    $rs .= 'Ошибка при обновлении категории '.$item['name'].": ".$this->db->error."<br>";
                }
            }
        }
        if ( empty($rs) ) {
            $rs = 'Все URL уже установлены. Если вы хотите обновить структуру, то удалите URL для категории, либо очистите все URL';
        }
        return $rs;
    }
}