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
        parent::__construct();
    }

    function main () {
        //$this->update_data_img();
        //$this->update_street_id();
    }

    function update_street_id () {
    	$DBC=DBC::getInstance();
    	$ra=array();
        $query = 'SELECT * FROM '.DB_PREFIX.'_data';
        $stmt=$DBC->query($query);
        if($stmt){
        	while($ar=$DBC->fetch($stmt)){
        		$ra[$ar['id']] = $ar;
        	}
        }
        if(!empty($ra)){
        	foreach ( $ra as $id => $item_array ) {
        		$street_id = $this->get_street_id($item_array['street']);
        		if ( $street_id ) {
        			$query = 'UPDATE '.DB_PREFIX.'_data SET street_id='.$street_id.' WHERE id='.$id;
        			echo $query.'<br>';
        			$stmt=$DBC->query($query);
        		}
        	}
        }
    }

    function get_street_id ( $street ) {
    	$DBC=DBC::getInstance();
        $street = trim($street);
        $query = 'SELECT * FROM '.DB_PREFIX.'_street WHERE name=?';
        $stmt=$DBC->query($query, array($street));

        if($stmt){
        	$ar=$DBC->fetch($stmt);
        	if ( $ar['street_id'] > 0 ) {
        		return $ar['street_id'];
        	}
        }
        return false;
    }

    /**
     * Update image
     * move from data -> data_image
     */
    function update_data_img () {
        $DBC=DBC::getInstance();
        $query = "select * from ".DB_PREFIX."_data where id > 3704";
        $stmt=$DBC->query($query);
        if($stmt){
        	while($ar=$DBC->fetch($stmt)){
        		$ra[$ar['id']] = $ar;
        	}
        }


        //insert records into _image
        foreach ( $ra as $id => $item_array ) {
            for ( $i = 1; $i <= 5; $i++ ) {
                if ( $item_array['img'.$i] != '' ) {
                    $query = "insert into ".DB_PREFIX."_image (normal, preview) values ('{$item_array['img'.$i]}', '{$item_array['img'.$i.'_preview']}')";
                    $stmt=$DBC->query($query);
                    $image_id = $DBC->lastInsertId();

                    //link in table _data_image
                    $query = "insert into ".DB_PREFIX."_data_image (id, image_id, sort_order) values ($id, $image_id, $image_id)";
                    $stmt=$DBC->query($query);
                }
            }
        }



    }
}
