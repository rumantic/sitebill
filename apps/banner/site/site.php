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

    function banner_group ($params) {

        $items = array();
        $query_params = array();
        $query_values = array();

        $DBC = DBC::getInstance();

        if (!empty($params)) {
            foreach ($params as $k => $op) {
                $query_params[] = '`' . $k . '`=?';
                $query_values[] = $op;
            }
        }

        $query = 'SELECT '.$this->primary_key.' FROM '.DB_PREFIX.'_'.$this->table_name.' WHERE ' . implode(' AND ', $query_params);
        $stmt = $DBC->query($query, $query_values);
        if ($stmt) {
            while($ar = $DBC->fetch($stmt)){
                $items[] = $ar[$this->primary_key];
            }
        }

        if(!empty($items)){
            $Object = new Data_Model();
            $items = $Object->init_model_data_from_db_multi($this->table_name, $this->primary_key, $items, $this->data_model[$this->table_name], true);

            foreach ($items as $k => $v){
                $items[$k] = $Object->init_language_values($v);
            }
        }

        return $items;

    }
}
