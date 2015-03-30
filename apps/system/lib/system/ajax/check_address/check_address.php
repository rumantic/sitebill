<?php
/**
 * Check address string
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Check_Address_Ajax extends SiteBill {
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
    }
    
    /**
     * Get address string, split it into parts and check each chunk in database
     * @param string $address
     * @return string
     */
    function check ( $address ) {
        //$address = mysql_real_escape_string($address);
        //preg_match_all('/\,/', $address, $address_array);
        //$address_array = split(',', $address);
        //$address_array = split(",", mysql_real_escape_string($address));
        //foreach
        $address_array = explode(',', $address);
        foreach ( $address_array as $item_id => $item ) {
            if ( preg_match('/г\./', $item) ) {
                $item = str_replace('г.', '', $item);
                $item = trim($item);
                if ( $city_id = $this->get_id_by_value('city', 'name', $item, 'city_id') ) {
                    $city_ok = true;
                    //$rs .= "<br>city = $item, city_id = $city_id<br>"; 
                } else {
                    return false;
                }
            } elseif ( preg_match('/обл\./', $item) ) {
                //return 'обл';
                //$item = str_replace('обл.', '', $item);
                $item = trim($item);
                if ( $region_id = $this->get_id_by_value('region', 'name', $item, 'region_id') ) {
                    $region_ok = true;
                    //$rs .= "region = $item, region_id = $region_id<br>"; 
                } else {
                    return false;
                }
            } elseif ( preg_match('/ул\./', $item) ) {
                $item = str_replace('ул.', '', $item);
                $item = trim($item);
                if ( $street_id = $this->get_id_by_value('street', 'name', $item, 'street_id') ) {
                    $street_ok = true;
                    //$rs .= "street = $item, stteet_id = $street_id<br>"; 
                } else {
                    return false;
                }
                
            }
            //$rs .= "$item_id = $item<br>"; 
        }
        //return $rs;
        
        if ( $city_ok and $street_ok and $region_ok ) {
            return ' <img src="/img/ok_round.png" width="16" height="16" border="0" alt="Адрес верный" title="Адрес верный"> ';
        }
        
        //$address_array[] = $address;
        //return 'check '.implode('|',$address_array);
        return '';
        //return 'check '.var_export($address_array, true);
    }
    
	function get_id_by_value ( $table, $key, $value, $id_key ) {
	    $query = "select * from ".DB_PREFIX."_".$table." where $key='$value'";
	    $this->db->exec($query);
	    $this->db->fetch_assoc();
	    if ( $this->db->row[$id_key] > 0 ) {
	        return $this->db->row[$id_key];
	    }
	    return false;
	}
}
?>
