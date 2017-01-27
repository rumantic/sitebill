<?php
/**
 * Grid constructor
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Auto_Grid_Constructor extends Auto_Manager {
    /**
     * Constructor
     */
    function __construct() {
        $this->Auto_Manager();
    }
    
    /**
     * Main
     * @param
     * @return
     */
    function main () {
        if (!isset($_REQUEST['flt_obj'])) {
            $_REQUEST['flt_obj'] = 0;
        }
        if (!isset($_REQUEST['flt_dc'])) {
            $_REQUEST['flt_dc'] = 0;
        }
        if (!isset($_REQUEST['flt_prc'])) {
            $_REQUEST['flt_prc'] = 0;
        }
        if (!isset($_REQUEST['flt_str'])) {
            $_REQUEST['flt_str'] = '';
        }
        if (!isset($_REQUEST['flt_text'])) {
            $_REQUEST['flt_text'] = '';
        }
        if ( $_REQUEST['flt_text'] == 'Что ищем' ) {
            $_REQUEST['flt_text'] = '';
        }

        $p_type_id = (int)$_REQUEST['flt_obj'];
        $p_district_id = (int)$_REQUEST['flt_dc'];
        $p_street = addslashes($_REQUEST['flt_str']);
        $p_text = addslashes($_REQUEST['flt_text']);
        $p_price = (int)$_REQUEST['flt_prc'] * 1000; 
        //echo 'test';
        //$res = $this->get_adv_ext($topic_id, '', $p_type_id, $p_district_id, $p_street, " `text` like '%$p_text%' " , $p_price, 'id desc');
        //echo $_REQUEST['tid'];
        $res = $this->get_sitebill_adv_ext($_REQUEST['tid'], $_REQUEST['tid1'], $_REQUEST['tid2'], $_REQUEST['district_id'], $_REQUEST['street_id'], $_REQUEST['price'] );
        //print_r($res);
        
        $this->get_grid($_REQUEST['tid'], 	$res);
    }
    
    /**
     * Get sitebill adv ext
     * Запрос к базе, список машин
     * @param
     * @return
     * @author Kris
     */
    function get_sitebill_adv_ext($tid, $tid1, $tid2, $district_id, $street_id, $p_price ) {
    	
    	if ($tid == 1 || $tid == "")				// объявления
    		return $this->get_sales_query();
    	if ($tid == 2)								// автоотзывы
    		return $this->get_autoreview_query();
    	if ($tid == 7)								// авторемонт
    		return $this->get_autoservice_query();
    	    	
        
    }
    
    /**
     * Get grid
     * @param array $adv res
     * @return string
     */
    function get_grid ($tid, $adv )
    {
    	if ($tid == 1 || $tid == "")				// объявления
    		return $this->get_sales_grid($adv);
    	if ($tid == 2)								// автоотзывы
    		return $this->get_autoreview_grid($adv);
    	if ($tid == 7)								// автоотзывы
    		return $this->get_autoservice_grid($adv);
    	return false;	
    }
    
    /**
     * Get sales grid
     * @param array $adv res
     * @return string
     */
    function get_sales_grid ( $adv ) {
        global $topic_id;

        $this->template->assign('main_file_tpl', 'auto_grid.tpl');
        //echo '<pre>';
        //print_r($adv);
        
        $clr = 0;
        $counter = 0;
        $rc = 'r2';
        $array_count = count($adv);
        //echo $array_count;
        
        $page = $_REQUEST['page'];
        if ( $page == '' ) {
            $page = 1;
        }
        $ra = array();
        foreach ($adv as $k => $v) {
            $counter++;
            if ( $this->validPage( $array_count, $counter, $page ) ) {
                $v['img'] = $this->getPreviewImage($v['id'], 1);
                if ($v['kpp_id'] > 0) {$v['kpp'] = $this->kpp_array[$v['kpp_id']];}						
            	if ($v['new_status_id'] > 0) {$v['new_status'] = $this->new_status_array[$v['new_status_id']];}
            	if ($v['bHavePhoto'] > 0) {$v['havePhoto'] = '+';} else {$v['havePhoto'] = '-' ;}
                $ra[] = $v;                
            }
        }
    
        $get = $_GET;
        if ( isset($get['order']) ) unset($get['order']);
        if ( isset($get['asc']) )   unset($get['asc']);

        /*if ( isset( $_REQUEST['metro_text'] ) )
            $get['metro_text'] = $_REQUEST['metro_text'];
		*/
        $url = '';
        foreach($get as $key => $value)
        {
                $url .= $key.'='.urlencode($value).'&';                
        }
         $url = SITEBILL_MAIN_URL.'/?'.$url;
        
        $this->template->assign('url', $url);
        $this->template->assign('grid_items', $ra);
        $this->template->assign('pager', $this->getPager($adv));

        return true;
    }
    
    /**
     * Get autoreview grid
     * @param array $adv res
     * @return string
     */
    function get_autoreview_grid ( $adv ) {
        global $topic_id;

        $this->template->assign('main_file_tpl', 'autoreview_grid.tpl');
        //echo '<pre>';
        //print_r($adv);
        
        $clr = 0;
        $counter = 0;
        $rc = 'r2';
        $array_count = count($adv);
        //echo $array_count;
        
        $page = $_REQUEST['page'];
        if ( $page == '' ) {
            $page = 1;
        }
        $ra = array();
        foreach ($adv as $k => $v) {
            $counter++;
            if ( $this->validPage( $array_count, $counter, $page ) ) {
                $v['img'] = $this->getPreviewImage($v['id'], 1);
                if ($v['kpp_id'] > 0) {$v['kpp'] = $this->kpp_array[$v['kpp_id']];}						
            	if ($v['new_status_id'] > 0) {$v['new_status'] = $this->new_status_array[$v['new_status_id']];}
            	$ra[] = $v;   
                       
            }
        }
    
        $get = $_GET;
        if ( isset($get['order']) ) unset($get['order']);
        if ( isset($get['asc']) )   unset($get['asc']);

        /*if ( isset( $_REQUEST['metro_text'] ) )
            $get['metro_text'] = $_REQUEST['metro_text'];
		*/
        $url = '';
        foreach($get as $key => $value)
        {
                $url .= $key.'='.urlencode($value).'&';                
        }
         $url = SITEBILL_MAIN_URL.'/?'.$url;
        
        $this->template->assign('url', $url);
        $this->template->assign('grid_items', $ra);
        $this->template->assign('pager', $this->getPager($adv));

        return true;
    }
    
    /**
     * Get autoservice grid
     * @param array $adv res
     * @return string
     */
    function get_autoservice_grid ( $adv ) {
    	global $topic_id;
    
    	$this->template->assign('main_file_tpl', 'autoservice_grid.tpl');
    	//echo '<pre>';
    	//print_r($adv);
    
    	$clr = 0;
    	$counter = 0;
    	$rc = 'r2';
    	$array_count = count($adv);
    	//echo $array_count;
    
    	$page = $_REQUEST['page'];
    	if ( $page == '' ) {
    		$page = 1;
    	}
    	$ra = array();
    	foreach ($adv as $k => $v) {
    		$counter++;
    		if ( $this->validPage( $array_count, $counter, $page ) ) {
    			$v['img'] = $this->getPreviewImage($v['id'], 1);
    			if ($v['kpp_id'] > 0) {
    				$v['kpp'] = $this->kpp_array[$v['kpp_id']];
    			}
    			if ($v['new_status_id'] > 0) {
    				$v['new_status'] = $this->new_status_array[$v['new_status_id']];
    			}
    			$ra[] = $v;
    
    		}
    	}
    
    	$get = $_GET;
    	if ( isset($get['order']) ) unset($get['order']);
    	if ( isset($get['asc']) )   unset($get['asc']);

    	$url = '';
    	foreach($get as $key => $value)
    	{
    		$url .= $key.'='.urlencode($value).'&';
    	}
    	$url = SITEBILL_MAIN_URL.'/?'.$url;
    
    	$this->template->assign('url', $url);
    	$this->template->assign('grid_items', $ra);
    	$this->template->assign('pager', $this->getPager($adv));
    
    	return true;
    }
    
    
    /**
     * Print row
     * @param array $v items array
     * @param string $row_class row class
     * @return string
     */
    function get_sales_row ( $v, $row_class ) {
        //echo '<pre>';
        //print_r($v);
        //echo '</pre>';
        
        $price = number_format($v['price'], 0, ' ', ' ');
        
        $rs = '';
        $rs .= "<tr valign='top' class='$row_class'>";
        $rs .= "<td class='$row_class'>";
        $img = $this->getPreviewImage($v['id'], 1);
        if ( $img ) {
            $rs .= '<a href="realty'.$v['id'].'.html">'.$img.'</a>';            
        }
        $rs .= "</td>";
        $rs .= "<td class='$row_class'><b>{$v['type_sh']}</b></td>";
        $rs .= "<td class='$row_class'>{$v['dc_name']}</td>";
        $rs .= "<td class='$row_class'>{$v['street']}</td>";
        $rs .= "<td class='$row_class' align=right nowrap><b>{$price}</b></td>";
        //$rs .= "<td class='$row_class'><div onmouseout='this.className = \"desc\"'  onmouseover='this.className = \"desc_full\"' class='desc'><a href=?tid={$topic_id}&id={$v['id']}&act=info>{$v['text']}</a></div></td>";
        $rs .= "<td class='$row_class'>{$v['floor']}/{$v['floor_count']}</td>";
        $rs .= "<td class='$row_class'>{$v['square_all']}/{$v['square_live']}/{$v['square_kitchen']}</td>";
        
        $rs .= "<td class='$row_class'>";
        if ( $this->recordHasPhoto($v['id']) ) {
            $rs .= '<img src="'.SITEBILL_MAIN_URL.'/img/hasphoto.jpg">';
            
        }
        $rs .= "</td>";
        
        $rs .= "<td class='$row_class'><a href='realty".$v['id'].".html'>подробнее</a></td>";
        $rs .= "</tr>";
        
        $ra['img'] = $this->getPreviewImage($v['id'], 1);
        return $ra;
    }
    
    /**
     * 
     * Build query for output ads,execute it, and return array of items
     * @author Kris
     * @return query
     */
    function get_sales_query()
    {
    	$where_array = false;
        
    	// свзываем таблицы
    	$where_array[] = 'auto.city_id=city.city_id';
        $where_array[] = 'auto.coachwork_id=coachwork.coachwork_id';
        $where_array[] = 'auto.mark_id=mark.mark_id';
        $where_array[] = 'auto.model_id=model.model_id';
        $where_array[] = 'model.mark_id=mark.mark_id';
        
        // фильтры
        if ( $_REQUEST['status_id'] != '' ) {
            $where_array[] = DB_PREFIX.'_auto.status_id='.$_REQUEST['status_id'];
        }
    	if ( $_REQUEST['city_id'] >0) {
            $where_array[] = 'auto.city_id='.$_REQUEST['city_id'];
        }
    	if ( $_REQUEST['coachwork_id'] >0) {
            $where_array[] = 'coachwork.coachwork_id='.$_REQUEST['coachwork_id'];
        }
	    if ( $_REQUEST['model_id'] >0) {
            $where_array[] = 'model.model_id='.$_REQUEST['model_id'];
        }
    	if ( $_REQUEST['mark_id'] >0) {
            $where_array[] = 'mark.mark_id='.$_REQUEST['mark_id'];
        }
   		if ( $_REQUEST['priceFrom'] >0) {
            $where_array[] = 'auto.price >= '.$_REQUEST['priceFrom'];
        }
    	if ( $_REQUEST['priceTo'] >0) {
            $where_array[] = 'auto.price <= '.$_REQUEST['priceTo'];
        }
    	if ( $_REQUEST['yearFrom'] >0) {
            $where_array[] = 'auto.year >= '.$_REQUEST['year_from_value'];
        }
    	if ( $_REQUEST['yearTo'] >0) {
            $where_array[] = 'auto.year <= '.$_REQUEST['year_to_value'];
        }
    	if ( $_REQUEST['runFrom'] >0) {
            $where_array[] = 'auto.run >= '.$_REQUEST['runFrom'];
        }
    	if ( $_REQUEST['runTo'] >0) {
            $where_array[] = 'auto.run <= '.$_REQUEST['runTo'];
        }
    	if ( $_REQUEST['havePhoto'] >0) {
            $where_array[] = " exists(select * from ".DB_PREFIX."_auto_image as image where image.auto_id = auto.auto_id ) ";
    	}
        if ( $_REQUEST['new_status_id'] >0) {
            $where_array[] = 'auto.new_status_id = '.$_REQUEST['new_status_id'];
        }
    	if ( $_REQUEST['parent_id'] >0) {
            $where_array[] = 'auto.parent_id = '.$_REQUEST['parent_id'];
        }     

        //$rs .= $this->getSubTypeFlatList($_REQUEST['tid1'], $_REQUEST['tid']);
        //$first_tid1 = $this->getFirstTid1($_REQUEST['tid']);
        
        
        /*if ($_SERVER['REQUEST_URI'] == '/')*/
            $order = "auto.auto_id desc";
        /*else
            $order = "re_data.date_added desc";*/
        if ( isset($_REQUEST['order']) ) {

            if ( !isset($_REQUEST['asc']) ) {
                $asc = 'asc';
            } 
            elseif ($_REQUEST['asc'] == 'asc')  $asc = 'asc';
            elseif ($_REQUEST['asc'] == 'desc') $asc = 'desc';
            //
            /*if     ( $_REQUEST['order'] == 'type' ) $order = 'type_sh ';
            elseif ( $_REQUEST['order'] == 'street' ) $order = 're_data.street ';
            elseif ( $_REQUEST['order'] == 'district' ) $order = 'dc_name ';
            elseif ( $_REQUEST['order'] == 'metro' ) $order = 're_metro.name '; */
            if ( $_REQUEST['order'] == 'price' ) $order = 'auto.price ';
            elseif ( $_REQUEST['order'] == 'coachwork' ) $order = ' coachwork.name ';
            elseif ( $_REQUEST['order'] == 'city' ) $order = ' city.name ';
            elseif ( $_REQUEST['order'] == 'kpp' ) $order = 'auto.kpp_id ';
            elseif ( $_REQUEST['order'] == 'mark' ) $order = 'mark.name ';
            elseif ( $_REQUEST['order'] == 'model' ) $order = 'model.name ';
            elseif ( $_REQUEST['order'] == 'new_status' ) $order = 'auto.new_status_id ';
            
            $order .= $asc;

        }
        
        
        
        if ( $where_array ) {
            $query = 	"select auto.*,".
            			"city.name as city, ".
            			"coachwork.name as coachwork, ".
			            "mark.name as mark, ".
			            "model.name as model ".
            			"from ".
            			DB_PREFIX."_auto as auto, ".
            			DB_PREFIX."_city as city, ".
            			DB_PREFIX."_coachwork as coachwork, ".
            			DB_PREFIX."_mark as mark, ".
            			DB_PREFIX."_model as model ".
            			"  where ".
            			implode(' and ', $where_array).
            			" order by ".$order;
        } else {
            //$query = "select auto.*, city.name as city from ".DB_PREFIX."_auto as auto, ".DB_PREFIX."_city as city ";
        }
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
        $ra = array();
        if($stmt){
        	while ( $ar=$DBC->fetch($stmt) ) {
        		$ra[$ar['auto_id']] = $ar;
        	}
        }
        
        return $ra;
    }

    /**
     * 
     * Build query for autoreviews output, execute it, and return array of items
     * @author Kris
     */
	function get_autoreview_query()
	{
		$where_array = false;
        
    	// фильтры
        if ( $_REQUEST['status_id'] != '' ) {
            $where_array[] = 'ar.status_id='.$_REQUEST['status_id'];
        }
    	if ( $_REQUEST['coachwork_id'] >0) {
            $where_array[] = 'coachwork.coachwork_id='.$_REQUEST['coachwork_id'];
        }
	    if ( $_REQUEST['model_id'] >0) {
            $where_array[] = 'model.model_id='.$_REQUEST['model_id'];
        }
    	if ( $_REQUEST['mark_id'] >0) {
            $where_array[] = 'mark.mark_id='.$_REQUEST['mark_id'];
        }
   		if ( $_REQUEST['yearFrom'] >0) {
            $where_array[] = 'ar.year >= '.$_REQUEST['year_from_value'];
        }
    	if ( $_REQUEST['yearTo'] >0) {
            $where_array[] = 'ar.year <= '.$_REQUEST['year_to_value'];
        }
    	if ( $_REQUEST['runFrom'] >0) {
            $where_array[] = 'ar.run >= '.$_REQUEST['runFrom'];
        }
    	if ( $_REQUEST['runTo'] >0) {
            $where_array[] = 'ar.run <= '.$_REQUEST['runTo'];
        }
    	if ( $_REQUEST['havePhoto'] >0) {
            $where_array[] = " exists(select * from ".DB_PREFIX."_auto_image as image where image.auto_id = ar.auto_id ) ";
    	}
        if ( $_REQUEST['new_status_id'] >0) {
            $where_array[] = 'ar.new_status_id = '.$_REQUEST['new_status_id'];
        }
    	if ( $_REQUEST['parent_id'] >0) {
            $where_array[] = 'ar.parent_id = '.$_REQUEST['parent_id'];
        }     

               
        //if ($_SERVER['REQUEST_URI'] == '/')
            $order = "ar.autoreview_id desc";
        /*else
            $order = "re_data.date_added desc";*/
        if ( isset($_REQUEST['order']) ) {

            if ( !isset($_REQUEST['asc']) ) {
                $asc = 'asc';
            } 
            elseif ($_REQUEST['asc'] == 'asc')  $asc = 'asc';
            elseif ($_REQUEST['asc'] == 'desc') $asc = 'desc';
           
            if ( $_REQUEST['order'] == 'coachwork' ) $order = ' coachwork.name ';
            elseif ( $_REQUEST['order'] == 'kpp' ) $order = 'ar.kpp_id ';
            elseif ( $_REQUEST['order'] == 'mark' ) $order = 'mark.name ';
            elseif ( $_REQUEST['order'] == 'model' ) $order = 'model.name ';
            elseif ( $_REQUEST['order'] == 'user' ) $order = 'u.login ';
            elseif ( $_REQUEST['order'] == 'date_created' ) $order = 'ar.date_created ';

            $order .= $asc;

        }
        
        //echo "order = $order<br>";
        
            $query = 	"select ar.*,".
            			"coachwork.name as coachwork, ".
			            "mark.name as mark, ".
			            "model.name as model, ".
            			"u.login as user ".
            			"from ".
            			DB_PREFIX."_autoreview as ar left join ".
            			DB_PREFIX."_coachwork as coachwork on ar.coachwork_id=coachwork.coachwork_id left join ".
            			DB_PREFIX."_mark as mark on ar.mark_id=mark.mark_id left join ".
            			DB_PREFIX."_model as model on ar.model_id=model.model_id and model.mark_id=mark.mark_id left join ".
            			DB_PREFIX."_user as u on u.user_id = ar.user_id ";
        if ( $where_array ) {
						$query .= 
            			"  where ".
            			implode(' and ', $where_array);
            			
        } 
        $query .= " order by ".$order;
        //echo $query;
        $DBC=DBC::getInstance();
		$stmt=$DBC->query($query);
        $ra = array();
        if($stmt){
        	while ( $ar=$DBC->fetch($stmt) ) {
        		$ra[$ar['autoreview_id']] = $ar;
        	}
        }
        return $ra;
	}

	/**
	 *
	 * Build query for autoservice output, execute it, and return array of items
	 * @author Kris
	 */
	function get_autoservice_query()
	{
		$where_array = false;
	
		// фильтры
		if ( $_REQUEST['coachwork_id'] >0) {
			$where_array[] = 'coachwork.coachwork_id='.$_REQUEST['coachwork_id'];
		}
		if ( $_REQUEST['model_id'] >0) {
			$where_array[] = 'model.model_id='.$_REQUEST['model_id'];
		}
		if ( $_REQUEST['mark_id'] >0) {
			$where_array[] = 'mark.mark_id='.$_REQUEST['mark_id'];
		}
		if ( $_REQUEST['parent_id'] >0) {
			$where_array[] = 'ar.parent_id = '.$_REQUEST['parent_id'];
		}
	
	
		//if ($_SERVER['REQUEST_URI'] == '/')
		$order = "ar.autoservice_id desc";
			/*else
			 $order = "re_data.date_added desc";*/
		if ( isset($_REQUEST['order']) ) {
	
			if ( !isset($_REQUEST['asc']) ) {
				$asc = 'asc';
			}
			elseif ($_REQUEST['asc'] == 'asc')  $asc = 'asc';
			elseif ($_REQUEST['asc'] == 'desc') $asc = 'desc';
	
			if ( $_REQUEST['order'] == 'coachwork' ) $order = ' coachwork.name ';
			elseif ( $_REQUEST['order'] == 'kpp' ) $order = 'ar.kpp_id ';
			elseif ( $_REQUEST['order'] == 'mark' ) $order = 'mark.name ';
			elseif ( $_REQUEST['order'] == 'model' ) $order = 'model.name ';
			elseif ( $_REQUEST['order'] == 'user' ) $order = 'u.login ';
			elseif ( $_REQUEST['order'] == 'date_created' ) $order = 'ar.date_created ';
	
			$order .= $asc;
	
		}
	
		//echo "order = $order<br>";
	
		$query = 	"select ar.*,".
				"coachwork.name as coachwork, ".
				"mark.name as mark, ".
				"model.name as model ".
				"from ".
				DB_PREFIX."_autoservice as ar left join ".
				DB_PREFIX."_coachwork as coachwork on ar.coachwork_id=coachwork.coachwork_id left join ".
				DB_PREFIX."_mark as mark on ar.mark_id=mark.mark_id left join ".
				DB_PREFIX."_model as model on ar.model_id=model.model_id and model.mark_id=mark.mark_id  ";
		if ( $where_array ) {
			$query .=
			"  where ".
			implode(' and ', $where_array);
	
		}
		$query .= " order by ".$order;
		echo $query;
		$this->db->exec($query);
		$ra = array();
		while ( $this->db->fetch_assoc() ) {
			$ra[$this->db->row['autoservice_id']] = $this->db->row;
		}
		return $ra;
	}
	
}
?>
