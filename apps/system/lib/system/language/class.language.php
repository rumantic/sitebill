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
        parent::__construct();
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
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
		if($stmt){
			$ar=$DBC->fetch($stmt);
			if ( $ar[$key] > 0 ) {
				return $ar;
			}
		}
        return false;
    }

	function get_version_list ( $table_name, $language_id=0, $params=array() ) {
		$ret=array();
		$where=array();
		$DBC=DBC::getInstance();
		if(count($params)==0){
			$query = "SELECT * FROM ".DB_PREFIX."_".$table_name." WHERE language_id=".$language_id;
			$stmt=$DBC->query($query);
		}else{
			foreach($params as $k=>$v){
				$where[]="`".$k."`=?";
				$where_d[]=$v;
			}
			$query = "SELECT * FROM ".DB_PREFIX."_".$table_name." WHERE language_id=".$language_id." AND ".implode(' AND ', $where);
			$stmt=$DBC->query($query, $where_d);
		}

        if($stmt){
			while($ar=$DBC->fetch($stmt)){
				$ret[]=$ar;
			}
		}
		return $ret;
    }
}
