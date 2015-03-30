<?php
/**
 * Jserver class
 * @author Kondin Dmitriy <kondin@etown.ru>
 * @url http://www.sitebill.ru
 */
class JServer {
    /**
     * Construct
     */
    function __construct() {
    	global $__server, $__db, $__user, $__password, $sitebill_document_root;
    	$this->db = new Db( $__server, $__db, $__user, $__password );
        //$this->SiteBill();
    }
    
    /**
     * Load product data
     * @param int $product_id
     * @return array
     */
    function load_product_data ( $product_id ) {
        $query = "SELECT * FROM ".DB_PREFIX."_shop_product WHERE product_id=$product_id";
        //echo $query;
        $this->db->exec($query);
        $this->db->fetch_assoc();
        if ( $this->db->row['product_id'] > 0 ) {
            return $this->db->row;
        }
        return false;
    }
    
    /**
     * Main
     * @param void
     * @return string
     */
    function main () {
        switch ( $this->getRequestValue('action') ) {
           	case 'add_to_cart': {
                $product_data = $this->load_product_data($this->getRequestValue('product_id'));
                if ( $product_data ) {
                    $_SESSION['product_list'][$this->getRequestValue('product_id')]['product_name'] = $product_data['product_name'];
                    $_SESSION['product_list'][$this->getRequestValue('product_id')]['product_price'] = $product_data['product_price'];
                    $_SESSION['product_list'][$this->getRequestValue('product_id')]['product_id'] = $product_data['product_id'];
                    
                    $product_count = $_SESSION['product_list'][$this->getRequestValue('product_id')]['count'];
                    $product_count++; 
                    $_SESSION['product_list'][$this->getRequestValue('product_id')]['count'] = $product_count;
                    
                    $_SESSION['product_list'][$this->getRequestValue('product_id')]['sum'] = $product_data['product_price']*$product_count;
                    
                    $body = 'add '.$this->getRequestValue('product_id');
                } else {
                    $body = 'Товар не найден';
                }
            	break;
            }
            
            case 'get_cart_count' : {
            	$items_count=0;
				$positions_count=count($_SESSION['product_list']);
				if($positions_count!=0){
					foreach($_SESSION['product_list'] as $v){
						$items_count+=$v['count'];
					}
				}
				$body=$items_count;
				//echo $items_count;
				break;
			}
        }
        
        
        $rs = $body;

        
        if ( $_REQUEST['callback'] != '' ) {
            $rs = $_REQUEST['callback'].'('.$rs.')';
        }
        
        return $rs;
    }
    
    function getRequestValue( $key ) {
        $value = (isset($_GET[$key])) ? $_GET[$key] : $_POST[$key];
        return $value;
    }
}
?>
