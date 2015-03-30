<?php
/**
 * Update 1
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Update1 extends SiteBill {
    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
    }
    
    function main () {
        //$this->update_data_img();
        //$this->update_street_id();
    }
    
    function update_street_id () {
        $query = "select * from ".DB_PREFIX."_data";
        $this->db->exec($query);
        while ( $this->db->fetch_assoc() ) {
            $ra[$this->db->row['id']] = $this->db->row;
        }
        foreach ( $ra as $id => $item_array ) {
            $street_id = $this->get_street_id($item_array['street']);
            if ( $street_id ) {
                $query = "update ".DB_PREFIX."_data set street_id=$street_id where id=".$id;
                echo $query.'<br>';
                $this->db->exec($query);
            }
        }
        
    }
    
    function get_street_id ( $street ) {
        $street = trim($street);
        $query = "select * from ".DB_PREFIX."_street where name='$street'";
        //echo $query.'<br>';
        
        $this->db->exec($query);
        $this->db->fetch_assoc();
        if ( $this->db->row['street_id'] > 0 ) {
            return $this->db->row['street_id'];
        }
        return false;
    }
    
    /**
     * Update image
     * move from data -> data_image
     */
    function update_data_img () {
        //load all records from _data
        $query = "select * from ".DB_PREFIX."_data where id > 3704";
        $this->db->exec($query);
        while ( $this->db->fetch_assoc() ) {
            $ra[$this->db->row['id']] = $this->db->row;
        }
        //echo '<pre>';
        //print_r($ra);
        //echo '</pre>';
        
        //insert records into _image
        foreach ( $ra as $id => $item_array ) {
            for ( $i = 1; $i <= 5; $i++ ) {
                if ( $item_array['img'.$i] != '' ) {
                    $query = "insert into ".DB_PREFIX."_image (normal, preview) values ('{$item_array['img'.$i]}', '{$item_array['img'.$i.'_preview']}')";
                    $image_id = $this->db->exec($query);
                    
                    //link in table _data_image
                    $query = "insert into ".DB_PREFIX."_data_image (id, image_id, sort_order) values ($id, $image_id, $image_id)";
                    $this->db->exec($query);
                }
            }
        }
        
        
        
    }
}
?>
