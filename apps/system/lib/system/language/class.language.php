<?php
/**
 * Language class
 * define methods for grid controls, switch languages, load language version of the record
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Language extends SiteBill {
    private $language_key = array(1 => 'en');
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
    }
    
    /**
     * Get control
     */
    function get_control ( $action, $do, $key, $value, $language_id, $exist = false ) {
        $rs = "<a href=\"?action=$action&do=$do&$key=$value&language_id=$language_id\">".$this->get_icon($language_id, $exist)."</a>";
        return $rs;
    }
    
    /**
     * Get icon for language ID
     * @param int $language_id
     * @param boolean $exist
     * @return string
     */
    private function get_icon ( $language_id, $exist = false ) {
        $icons['en']['title'] = "English";
        $icons['en']['exist'] = "edit_en.gif";
        $icons['en']['empty'] = "edit_en_empty.gif";

        $icons['de']['title'] = "German";
        $icons['de']['exist'] = "edit_de.gif";
        $icons['de']['empty'] = "edit_de_empty.gif";
        if ( $exist ) {
            return '<img src="/apps/admin/admin/template/img/'.$icons[$this->language_key[$language_id]]['exist'].'" border="0" width="16" height="16" alt="'.$icons[$this->language_key[$language_id]]['title'].'" title="'.$icons[$this->language_key[$language_id]]['title'].'">';
        }
        return '<img src="/apps/admin/admin/template/img/'.$icons[$this->language_key[$language_id]]['empty'].'" border="0" width="16" height="16" alt="'.$icons[$this->language_key[$language_id]]['title'].'" title="'.$icons[$this->language_key[$language_id]]['title'].'">';
    }
    
    /**
     * Get language version of the record for this Lanugage ID
     * @param string $table_name
     * @param string $key
     * @param int $value
     * @param int $language_id
     * @return mixed
     */
    function get_version ( $table_name, $key, $value, $language_id ) {
    	if($language_id!=0){
        	$query = "select * from ".DB_PREFIX."_$table_name where language_id=$language_id and link_id=$value";
    	}else{
    		$query = "select * from ".DB_PREFIX."_$table_name where language_id=$language_id and product_id=$value";
    	}
        //echo $query;
        $this->db->exec($query);
        $this->db->fetch_assoc();
        if ( $this->db->row[$key] > 0 ) {
            return $this->db->row;
        }
        return false;
    }
    
	function get_version_list ( $table_name, $language_id=0, $params=array() ) {
		$ret=array();
		$where=array();
		if(count($params)==0){
			$query = "SELECT * FROM ".DB_PREFIX."_".$table_name." WHERE language_id=".$language_id;
		}else{
			foreach($params as $k=>$v){
				$where[]="`".$k."`='".mysql_real_escape_string($v)."'";
			}
			$query = "SELECT * FROM ".DB_PREFIX."_".$table_name." WHERE language_id=".$language_id." AND ".implode(' AND ',$where);
		}
        
        $this->db->exec($query);
        if($this->db->success){
        	while($this->db->fetch_assoc()){
        		$ret[]=$this->db->row;
        	}
        }
        return $ret;
    }
}
?>
