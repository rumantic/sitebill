<?php

/**
 * Grid constructor
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
require_once (SITEBILL_DOCUMENT_ROOT .'/apps/system/lib/frontend/grid/grid_constructor_root.php');
class Grid_Constructor extends Grid_Constructor_Root {

    public $grid_total;
    protected $grid_item_data_model = null;
    protected $billing_mode = false;
    protected $currency_admin = null;

    /**
     * Constructor
     */
    function __construct() {
        $this->SiteBill();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $this->grid_item_data_model = $data_model->get_kvartira_model(false, true);
        $this->grid_item_data_model = $this->grid_item_data_model['data'];
    }

    function vip_right($params) {
        if (!isset($params['_no_interactive_search'])) {
            $params['_no_interactive_search'] = 1;
        }
        $res = $this->get_sitebill_adv_ext($params, true, false);
        $this->template->assign('special_items2', $res);
    }

    function vip_array($params) {
        $params['per_page'] = 100;
        if (!isset($params['_no_interactive_search'])) {
            $params['_no_interactive_search'] = 1;
        }
        $res = $this->get_sitebill_adv_ext($params, true, false);
        return $res;
    }

    function map_search_items($ids){
        $params['id']=$ids;
        $params['no_portions']=1;
        $res = $this->get_sitebill_adv_core( $params, false, false, false, false );
        return json_encode($res['data']);
    }
    
    
    function map_search_listing(){
        $theme = $this->getConfigValue('theme');
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php');
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/main/main.php')) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/main/main.php');
            $frontend = new frontend_main();
        }else{
            $frontend = new SiteBill_Krascap();
        }
        
        $params = $frontend->gatherRequestParams();
        
        
        $result_set = array();
        
        global $smarty;
        $tpl = SITEBILL_DOCUMENT_ROOT.'/template/frontend/'. $this->getConfigValue('theme').'/activemap_listing.tpl';
        
        $responce = array(
            'status' => 0,
            'data' => array(),
            'msg' => ''
        );
        
        $overall_limit = intval($this->getConfigValue('apps.geodata.iframe_map_limit'));
        //$overall_limit = 10;
        
        $params['has_geo']=1;
        if ($overall_limit > 0) {
            $params['page_limit'] = $overall_limit;
        } else {
            $params['no_portions'] = 1;
        }
        //$params['geo_only'] = 1;
        $params['no_premium_filtering'] = 1;
        
        $all = intval($this->getRequestValue('all'));
        $bounds = $this->getRequestValue('bounds');
        $coords = $this->getRequestValue('polylineString');
        
        if($all){
            $res = $this->get_sitebill_adv_core( $params, false, false, true, false );
            $msg = '';
        
            if($res['_showed'] < $res['_total_records']){
                $msg = 'Показано '.$res['_showed'].' из '.$res['_total_records'];
            }
            
            $smarty->assign('activemap_listing', $res['data']);
            
            $responce = array(
                'status' => 1,
                'data' => $res['data'],
                'listing' => $smarty->fetch($tpl),
                'total' => $res['_total_records'],
                'msg' => $msg,
                'paging' => $res['paging']
            );
        }elseif(null === $coords && null !== $bounds){
           
            $params['map_bounds'] = $bounds;
            $res = $this->get_sitebill_adv_core( $params, false, false, true, false );
            $smarty->assign('activemap_listing', $res['data']);
            $responce = array(
                'status' => 1,
                'data' => $res['data'],
                'listing' => $smarty->fetch($tpl),
                'total' => $res['_total_records'],
                'msg' => $msg,
                    'paging' => $res['paging']
            );
        }else{
            $lines=array();
        
            if(null !== $coords){
                if(!is_array($coords)){
                    $pairs=explode(';', $coords);
                    foreach ($pairs as $p){
                        $points[]=explode(',', $p);
                    }
                    $endel=end($points);
                    reset($points);
                    if($endel[0]!=$points[0][0] && $endel[1]!=$points[0][1]){
                        $points[]=$points[0];
                    }
                }else{
                    $points = $coords;
                    $points[]=$coords[0];
                }


                $count=count($points);
                $i=0;
                $max_lat=false;
                $min_lat=false;
                $max_lng=false;
                $min_lng=false;
                foreach ($points as $k=>$point){
                    $lines[$k]['s']['lat']=$point[0];
                    $lines[$k]['s']['lng']=$point[1];
                    $lines[$k]['e']['lat']=$points[$k+1][0];
                    $lines[$k]['e']['lng']=$points[$k+1][1];
                    $delta_lat=$lines[$k]['e']['lat']-$lines[$k]['s']['lat'];
                    $delta_lng=$lines[$k]['e']['lng']-$lines[$k]['s']['lng'];
                    if($delta_lng==0){
                        $lines[$k]['type']='v';
                        $koef=0;
                    }elseif($delta_lat==0){
                        $lines[$k]['type']='h';
                        $koef=0;
                    }else{
                        $lines[$k]['type']='c';
                        $koef=($delta_lat)/($delta_lng);
                    }

                    $lines[$k]['koef']=$koef;
                    if($lines[$k]['type']=='c'){
                        $lines[$k]['ckoef']=$lines[$k]['s']['lat']-$koef*$lines[$k]['s']['lng'];
                    }else{
                        $lines[$k]['ckoef']=0;
                    }
                    //$lines[$k]['ckoef']=$lines[$k]['s']['lat']-$koef*$lines[$k]['s']['lng'];
                    //echo $point[0].'<br>';
                    //echo $point[1].'<br>';
                    if($max_lat!==false && $point[0]>$max_lat){
                        $max_lat=$point[0];
                    }elseif($max_lat===false){
                        $max_lat=$point[0];
                    }
                    if($min_lat!==false && $point[0]<$min_lat){
                        $min_lat=$point[0];
                    }elseif($min_lat===false){
                        $min_lat=$point[0];
                    }
                    if($max_lng!==false && $point[1]>$max_lng){
                        $max_lng=$point[1];
                    }elseif($max_lng===false){
                        $max_lng=$point[1];
                    }
                    if($min_lng!==false && $point[1]<$min_lng){
                        $min_lng=$point[1];
                    }elseif($min_lng===false){
                        $min_lng=$point[1];
                    }
                    $i++;
                    if($i==$count-1){
                        break;
                    }
                }
            }else{
                $smarty->assign('activemap_listing', array());
                $responce = array(
                    'status' => 0,
                    'data' => array(),
                'listing' => $smarty->fetch($tpl),
                'total' => $res['_total_records'],
                    'msg' => ''
                );
                return json_encode(array($responce));
            }
            
            $ids=array();

            $DBC=DBC::getInstance();
            $query='SELECT id, topic_id, geo_lat AS lat, geo_lng AS lng FROM '.DB_PREFIX.'_data WHERE geo_lat IS NOT NULL AND geo_lng IS NOT NULL AND geo_lat>=? AND geo_lat<=? AND geo_lng>=? AND geo_lng<=? AND active=1';
            //print_r(array($min_lat, $max_lat, $min_lng, $max_lng));
            $stmt=$DBC->query($query, array($min_lat, $max_lat, $min_lng, $max_lng));
            if($stmt){
                while($ar=$DBC->fetch($stmt)){
                    $ret[]=$ar;
                }
            }
            
            $finded_count=count($ret);
            $max_count=(int)$this->getConfigValue('apps.mapviewer.max_objects_onmap');
            if($max_count==0){
                $max_count=1000000;
            }
    //echo $finded_count;

            if($finded_count>$max_count){
                return json_encode('В выбранной Вами области содержится '.$finded_count.' объектов. Пожалуйста выберите меньшую область.');
            }

            //echo count($ret);

            $points=array();
            
            if(count($ret)>0){
                if(!empty($lines)){
                    foreach($ret as $pk=>$point){
                        $res=$this->isInRegion($point, $lines);
                        if($res){
                            $ids[]=$point['id'];

                        }else{
                            unset($ret[$pk]);
                        }
                    }
                }else{
                    foreach($ret as $pk=>$point){
                        $ids[]=$point['id'];
                    }
                }



                $params = $frontend->gatherRequestParams();
                //$params=$this->getRequestValue('params');
                $params['id']=$ids;
                //$params['no_portions']=1;
                //$params['has_geo']=1;
                //$params['geo_only'] = 1;
                $res = $this->get_sitebill_adv_core( $params, false, false, true, false );
                $smarty->assign('activemap_listing', $res['data']);
                
                
                 
                
                
                $responce = array(
                    'status' => 1,
                    'data' => $res['data'],
                    'listing' => $smarty->fetch($tpl),
                'total' => $res['_total_records'],
                    'paging' => $res['paging'],
                    'msg' => $msg
                );
            }else{
                //$res['data']=array();
            }
        }
                

        
        
        
    			
        
        return json_encode($responce);
    }
    
    function map_search(){
        
        $responce = array(
            'status' => 0,
            'data' => array(),
            'msg' => ''
        );
        
        $overall_limit = intval($this->getConfigValue('apps.geodata.iframe_map_limit'));
        
        $theme = $this->getConfigValue('theme');
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php');
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/main/main.php')) {
            require_once (SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/main/main.php');
            $frontend = new frontend_main();
        }else{
            $frontend = new SiteBill_Krascap();
        }
        
        $params = $frontend->gatherRequestParams();
        
        
        //$params=$this->getRequestValue('params');
        $params['has_geo']=1;
        if ($overall_limit > 0) {
            $params['page_limit'] = $overall_limit;
        } else {
            $params['no_portions'] = 1;
        }
        $params['geo_only'] = 1;
        $params['no_premium_filtering'] = 1;
        
        $all = intval($this->getRequestValue('all'));
        $bounds = $this->getRequestValue('bounds');
        $coords = $this->getRequestValue('polylineString');
        
        if($all){
            $res = $this->get_sitebill_adv_core( $params, false, false, true, false );
            $msg = '';
        
            if($res['_showed'] < $res['_total_records']){
                $msg = 'Показано '.$res['_showed'].' из '.$res['_total_records'];
            }
            $responce = array(
                'status' => 1,
                'data' => $res['data'],
                'msg' => $msg
            );
        }elseif(null === $coords && null !== $bounds){
           
            $params['map_bounds'] = $bounds;
            $res = $this->get_sitebill_adv_core( $params, false, false, true, false );
            $responce = array(
                'status' => 1,
                'data' => $res['data'],
                'msg' => $msg
            );
        }else{
            $lines=array();
        
            if(null !== $coords){
                if(!is_array($coords)){
                    $pairs=explode(';', $coords);
                    foreach ($pairs as $p){
                        $points[]=explode(',', $p);
                    }
                    $endel=end($points);
                    reset($points);
                    if($endel[0]!=$points[0][0] && $endel[1]!=$points[0][1]){
                        $points[]=$points[0];
                    }
                }else{
                    $points = $coords;
                    $points[]=$coords[0];
                }


                $count=count($points);
                $i=0;
                $max_lat=false;
                $min_lat=false;
                $max_lng=false;
                $min_lng=false;
                foreach ($points as $k=>$point){
                    $lines[$k]['s']['lat']=$point[0];
                    $lines[$k]['s']['lng']=$point[1];
                    $lines[$k]['e']['lat']=$points[$k+1][0];
                    $lines[$k]['e']['lng']=$points[$k+1][1];
                    $delta_lat=$lines[$k]['e']['lat']-$lines[$k]['s']['lat'];
                    $delta_lng=$lines[$k]['e']['lng']-$lines[$k]['s']['lng'];
                    if($delta_lng==0){
                        $lines[$k]['type']='v';
                        $koef=0;
                    }elseif($delta_lat==0){
                        $lines[$k]['type']='h';
                        $koef=0;
                    }else{
                        $lines[$k]['type']='c';
                        $koef=($delta_lat)/($delta_lng);
                    }

                    $lines[$k]['koef']=$koef;
                    if($lines[$k]['type']=='c'){
                        $lines[$k]['ckoef']=$lines[$k]['s']['lat']-$koef*$lines[$k]['s']['lng'];
                    }else{
                        $lines[$k]['ckoef']=0;
                    }
                    //$lines[$k]['ckoef']=$lines[$k]['s']['lat']-$koef*$lines[$k]['s']['lng'];
                    //echo $point[0].'<br>';
                    //echo $point[1].'<br>';
                    if($max_lat!==false && $point[0]>$max_lat){
                        $max_lat=$point[0];
                    }elseif($max_lat===false){
                        $max_lat=$point[0];
                    }
                    if($min_lat!==false && $point[0]<$min_lat){
                        $min_lat=$point[0];
                    }elseif($min_lat===false){
                        $min_lat=$point[0];
                    }
                    if($max_lng!==false && $point[1]>$max_lng){
                        $max_lng=$point[1];
                    }elseif($max_lng===false){
                        $max_lng=$point[1];
                    }
                    if($min_lng!==false && $point[1]<$min_lng){
                        $min_lng=$point[1];
                    }elseif($min_lng===false){
                        $min_lng=$point[1];
                    }
                    $i++;
                    if($i==$count-1){
                        break;
                    }
                }
            }else{
                $responce = array(
                    'status' => 0,
                    'data' => array(),
                    'msg' => ''
                );
                return json_encode(array($responce));
            }
            
            $ids=array();

            $DBC=DBC::getInstance();
            $limit_and_order_map_query = '';
            if ( $this->getConfigValue('apps.geodata.iframe_map_limit') > 0 ) {
                $limit_and_order_map_query = ' ORDER by date_added LIMIT '.(int)$this->getConfigValue('apps.geodata.iframe_map_limit');
            }
            $query='SELECT id, topic_id, geo_lat AS lat, geo_lng AS lng FROM '.DB_PREFIX.'_data WHERE geo_lat IS NOT NULL AND geo_lng IS NOT NULL AND geo_lat>=? AND geo_lat<=? AND geo_lng>=? AND geo_lng<=? AND active=1'.$limit_and_order_map_query;
            //print_r(array($min_lat, $max_lat, $min_lng, $max_lng));
            $stmt=$DBC->query($query, array($min_lat, $max_lat, $min_lng, $max_lng));
            if($stmt){
                while($ar=$DBC->fetch($stmt)){
                    $ret[]=$ar;
                }
            }
            
            $finded_count=count($ret);
            $max_count=(int)$this->getConfigValue('apps.mapviewer.max_objects_onmap');
            if($max_count==0){
                $max_count=1000000;
            }
    //echo $finded_count;

            if($finded_count>$max_count){
                return json_encode('В выбранной Вами области содержится '.$finded_count.' объектов. Пожалуйста выберите меньшую область.');
            }

            //echo count($ret);

            $points=array();
            
            if(count($ret)>0){
                if(!empty($lines)){
                    foreach($ret as $pk=>$point){
                        $res=$this->isInRegion($point, $lines);
                        if($res){
                            $ids[]=$point['id'];

                        }else{
                            unset($ret[$pk]);
                        }
                    }
                }else{
                    foreach($ret as $pk=>$point){
                        $ids[]=$point['id'];
                    }
                }



                $params = $frontend->gatherRequestParams();
                //$params=$this->getRequestValue('params');
                $params['id']=$ids;
                $params['no_portions']=1;
                $params['has_geo']=1;
                $params['geo_only'] = 1;
                $res = $this->get_sitebill_adv_core( $params, false, false, true, false );
                $responce = array(
                    'status' => 1,
                    'data' => $res['data'],
                    'msg' => $msg
                );
            }else{
                //$res['data']=array();
            }
        }
                

    			
        
        return json_encode($responce);
    }
    
    private function isInRegion($point, $lines){
    	$point_lat=$point['lat'];
    	$point_lng=$point['lng'];
    	//echo 'POINT: '.$point_lat.' '.$point_lng."\n\r";
    	
    	foreach($lines as $line){
    		if($line['type']=='v' && $this->isBetween($point_lat, $line['s']['lat'], $line['e']['lat']) && $point_lng==$line['s']['lng']){
    			return true;
    		}elseif($line['type']=='h' && $this->isBetween($point_lng, $line['s']['lng'], $line['e']['lng']) && $point_lat==$line['s']['lat']){
    			return true;
    		}
    	}
    	
    	$intersectCount=0;
    	
    	foreach($lines as $line){
    		if($line['type']=='v'){
    			
    		}elseif($line['type']=='h' && $this->isBetween($point_lng, $line['s']['lng'], $line['e']['lng']) && $point_lat<$line['s']['lat']){
    			$intersectCount++;
    		}else{
    			//echo 'LINE: '.$line['s']['lng'].' '.$line['e']['lng']."\n\r";
    			if($this->isBetween($point_lng, $line['s']['lng'], $line['e']['lng'])){
    				$intersect_lat=$line['koef']*$point_lng+$line['ckoef'];
    				if($intersect_lat>=$point_lat){
    					$intersectCount++;
    				}
    			}
    		}
    	}
    	//echo $intersectCount;
    	
    	if($intersectCount==0){
    		return false;
    	}
    	if($intersectCount==1){
    		return true;
    	}
    	if($intersectCount%2==0){
    		return false;
    	}
    	return true;
    }
    
    private function isBetween($point, $fp1, $fp2){
    	$start=$fp1;
    	if($fp2<$start){
    		$start=$fp2;
    		$end=$fp1;
    	}else{
    		$end=$fp2;
    	}
    	if($point>=$start && $point<=$end){
    		return true;
    	}
    	return false;
    }
    
    function tryGetSimilarTopicsByTranslitName($topic_id) {
        $translit_name = false;
        $result = array();
        $DBC = DBC::getInstance();
        $query = "select id, translit_name from " . DB_PREFIX . "_topic where id=?";
        $stmt = $DBC->query($query, array($topic_id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if (strlen($ar['translit_name']) > 0) {
                $translit_name = $ar['translit_name'];
            }
        }

        //echo '$translit_name = '.$translit_name.'<br>';
        if ($translit_name) {
            $query = "select id, translit_name from " . DB_PREFIX . "_topic where translit_name=?";
            $stmt = $DBC->query($query, array($translit_name));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    if (strlen($ar['translit_name']) > 0 and $ar['id'] != $topic_id) {
                        array_push($result, $ar['id']);
                    }
                }
            }
        }
        /*
          echo '<pre>1';
          print_r($result);
          echo '</pre>';
         */
        return $result;
    }

    /**
     * Main
     * @param array $param
     * @return array
     */
    function main($params) {

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();


        $this->template->assign('category_tree', $this->get_category_tree($params, $category_structure));

        $this->template->assign('breadcrumbs', $this->prepareBreadcrumbs($params));
        $sp=$params;
        unset($sp['page']);
        unset($sp['order']);
        $this->template->assign('search_params', json_encode($sp));
        $this->template->assign('search_url', $_SERVER['REQUEST_URI']);

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable') == 1) {
            $_billing_on = true;
            if(1 == $this->getConfigValue('apps.billing.disable_premium_popup')){
                $_billing_on = false;
            }
        } else {
            $_billing_on = false;
        }

        if ((!isset($params['admin']) || (isset($params['admin']) && $params['admin'] != 1)) && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/front_grid_constructor.php')) {

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/front_grid_constructor.php');

            if (1 != $this->getConfigValue('block_user_front_grids')) {

                if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/grid/front_grid_local.php')) {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/grid/front_grid_local.php');
                    $FGG = new Front_Grid_Local();
                } else {
                    $FGG = new Front_Grid_Constructor();
                }


                if (!is_array($params['topic_id']) && $params['topic_id'] != '' && $params['topic_id'] != 0) {
                    $topic = (array) $params['topic_id'];
                    if ($this->getConfigValue('theme') == 'etown') {
                        if ($params['city_id'] != 0 and $params['city_id'] != '') {
                            $topic = array_merge($topic, $this->tryGetSimilarTopicsByTranslitName($params['topic_id']));
                            $params['topic_id'] = $topic;
                        }
                    }
                    /*
                      echo '<pre>';
                      print_r($params);
                      print_r($topic);
                      echo '</pre>';
                      exit;
                     */
                } elseif (is_array($params['topic_id'])) {
                    $topic = $params['topic_id'];
                }



                if ($columns_data = $FGG->grid_exists($topic)) {

                    $data_model = new Data_Model();

                    $_model = $data_model->get_kvartira_model();


                    //$fields=new stdClass();
                    //$FGG->generate($_model, $columns_data, $params);
                    $FGG->fullGenerate($_model, $columns_data, $params);
                } else {
                    if ($_billing_on) {
                        $res = $this->get_sitebill_adv_ext($params, false, true);
                    } else {
                        $res = $this->get_sitebill_adv_ext($params);
                    }
                    $this->get_sales_grid($res);
                }
            } else {

                //$FGG = new Front_Grid_Constructor();
                if ($_billing_on) {
                    $res = $this->get_sitebill_adv_ext($params, false, true);
                } else {
                    $res = $this->get_sitebill_adv_ext($params);
                }

                $this->get_sales_grid($res);
            }
        } else {
            $res = $this->get_sitebill_adv_ext($params);

            $this->get_sales_grid($res);
        }
    }

    /**
     * Main
     * @param array $param
     * @return array
     */
    function main_contact($params) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();

        $res = $this->get_sitebill_adv_ext($params);
        $res = $this->add_user_account_info($res);
        $this->template->assign('category_tree', $this->get_category_tree($params, $category_structure));
        $this->template->assign('breadcrumbs', $this->get_category_breadcrumbs($params, $category_structure, SITEBILL_MAIN_URL));

        $this->get_sales_grid($res);
    }

    function add_user_account_info($res) {
        if (!is_array($res)) {
            return $res;
        }
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');
        $Users_Manager = new User_Object_Manager();

        foreach ($res as $item_id => $item) {
            $res[$item_id]['user_array'] = $Users_Manager->load_by_id($item['user_id']);
        }
        //echo '<pre>';
        //print_r($res);
        //echo '</pre>';
        return $res;
    }

    /**
     * Special
     * @param array $params
     */
    function special($params) {
        if (!isset($params['_no_interactive_search'])) {
            $params['_no_interactive_search'] = 1;
        }
        $res = $this->get_sitebill_adv_ext($params, true);
        $this->template->assign('special_items', $res);
    }

    /**
     * Special right
     * @param unknown_type $params
     */
    function special_right($params) {
        if ($this->getConfigValue('theme') == '3columns') {
            $params['only_img'] = 1;
        }
        if (!isset($params['_no_interactive_search'])) {
            $params['_no_interactive_search'] = 1;
        }
        $res = $this->get_sitebill_adv_ext($params, true);
        $this->template->assign('special_items2', $res);
    }

    /**
     * Get category tree
     * @param array $params
     * @param array $category_structure
     * @return string
     */
    function get_category_tree($params, $category_structure) {
        if (isset($params['topic_id']) && is_array($params['topic_id'])) {
            return '';
        }
        if (isset($params['topic_id']) && isset($category_structure['childs'][$params['topic_id']]) && count($category_structure['childs'][$params['topic_id']]) > 0) {
            foreach ($category_structure['childs'][$params['topic_id']] as $item_id => $child_id) {
                if ($category_structure['catalog'][$child_id]['url'] != '') {
                    $rs .= '<li><a href="' . SITEBILL_MAIN_URL . '/' . $category_structure['catalog'][$child_id]['url'] . '">' . $category_structure['catalog'][$child_id]['name'] . '</a></li>';
                } else {
                    $rs .= '<li><a href="' . SITEBILL_MAIN_URL . '/topic' . $child_id . '.html">' . $category_structure['catalog'][$child_id]['name'] . '</a></li>';
                }
                //$rs .= '<li><a href="?topic_id='.$child_id.'">'.$category_structure['catalog'][$child_id]['name'].'</a></li>';
            }
            return $rs;
        }
        return '';
    }

    function get_grid_total_records() {
        return $this->grid_total;
    }

    function get_sitebill_adv_ext($params, $random = false, $premium = false) {
        /* if(defined('IS_DEVELOPER') && IS_DEVELOPER==1){

          return $this->get_sitebill_adv_ext_modern($params, $random);
          } */
        $premium_ra = array();
        if ($premium) {
            $premium_ra = $this->get_sitebill_adv_ext_base($params, $random, true);
        }

        /* if($premium){
          $params['sort_premium']=1;
          } */

        $ra = $this->get_sitebill_adv_ext_base($params, $random);

        if (count($premium_ra) > 0) {
            $ra = array_merge($premium_ra, $ra);
        }


        return $ra;
    }

    /* function getGridSelectQuery($params){
      require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
      require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/front_grid_constructor.php');
      $data_model = new Data_Model();
      $_model=$data_model->get_kvartira_model();

      $params=$_REQUEST;

      if ( !isset($params['page']) or $params['page'] == 0 ) {
      $page = 1;
      } else {
      $page = $params['page'];
      }
      $limit = $this->getConfigValue('per_page');

      if ( $params['vip'] == 1 ) {
      if ( $params['per_page'] > 0 ) {
      $limit = $params['per_page'];
      } else {
      $limit = $this->getConfigValue('vip_rotator_number');
      }
      } else {
      if(isset($params['page_limit']) && $params['page_limit']!=0){
      $limit = $params['page_limit'];
      }else{
      $limit = $this->getConfigValue('per_page');
      }

      }
      if ( $premium ) {
      $limit = 5;
      }

      $params['__from']=($page-1)*$limit;
      $params['__to']=$limit;

      //Data_Model::getSelectQuery($_model, $params);
      $qp=Data_Model::prepareQueryParts($_model, $params);
      //print_r($qp);
      $query=Data_Model::getPrimaryQuery($qp);

      $DBC=DBC::getInstance();
      $stmt=$DBC->query($query);
      $ids=array();
      if($stmt){
      while($ar=$DBC->fetch($stmt)){
      $ids[]=$ar['__pk'];
      }
      }

      $total = 0;
      $query='SELECT FOUND_ROWS() AS total';
      $stmt=$DBC->query($query);
      if($stmt){
      $ar=$DBC->fetch($stmt);
      $total=$ar['total'];
      }
      print_r($ids);
      echo $total;

      $_params=$qp;
      $_params['wp']=array();
      $_params['wp'][]='(`'.$qp['main_table'].'`.`'.$qp['pk'].'` IN ('.implode(',', $ids).'))';
      //$where_parts[]='(`'.$main_table.'`.`'.$field_data['name'].'` IN ('.implode(',', $vals).'))';

      $query=Data_Model::getDataSelectQuery($_params);
      echo $query;
      //echo Data_Model::primaryQuery($qp);
      //print_r(Data_Model::getSelectQuery($_model, $params));

      } */

    /**
     * Get sitebill adv ext
     * @param array $params
     * @param boolean $random
     * @return array
     */
    function get_sitebill_adv_ext_base($params, $random = false, $premium = false) {

        $data = $this->get_sitebill_adv_core($params, $random, $premium, true, true);
        $this->template->assert('pager_array', $data['paging']);
        $this->template->assert('pager', $data['pager']);
        $this->template->assert('pagerurl', $data['pagerurl']);
        $this->template->assert('url', $data['url']);
        $this->template->assert('grid_geodata', json_encode($data['grid_geodata']));
        $this->template->assert('geoobjects_collection_clustered', json_encode($data['geoobjects_collection_clustered']));
        $this->template->assert('_total_records', $data['_total_records']);
        $this->template->assert('_max_page', $data['_max_page']);
        $this->template->assert('_params', $data['_params']);
        $this->template->assert('_mysearch_params', $data['_mysearch_params']);
        $this->template->assert('_grid_show_start', $data['_grid_show_start']);
        $this->template->assert('_grid_show_end', $data['_grid_show_end']);

        return $data['data'];
    }

    protected function get_data($params, $needle_fields = array()) {
        $select_fields = array();
        $return = array();
    }

    function get_sitebill_adv_geomarkers($params) {
        //print_r($params);
        $select_fields = array();
        $return = array();


        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';

        if ($this->getConfigValue('currency_enable')) {

            require_once SITEBILL_DOCUMENT_ROOT . '/apps/currency/admin/admin.php';
            $CM = new currency_admin();
        }



        $preparedParams = $this->prepareRequestParams($params, $premium);


        $where_array = $preparedParams['where_array'];
        $add_from_table = $preparedParams['add_from_table'];
        $add_select_value = $preparedParams['add_select_value'];
        $params = $preparedParams['params'];

        $where_array_prepared = $preparedParams['where_array_prepared'];
        $where_value_prepared = $preparedParams['where_value_prepared'];

        $select_what = $preparedParams['select_what'];
        $left_joins = $preparedParams['left_joins'];

        $left_joins[] = 'LEFT JOIN ' . DB_PREFIX . '_topic ON ' . DB_PREFIX . '_data.topic_id=' . DB_PREFIX . '_topic.id';

        if ($this->getConfigValue('currency_enable')) {
            $select_what[] = DB_PREFIX . '_currency.code AS currency_code';
            $select_what[] = DB_PREFIX . '_currency.name AS currency_name';
            $select_what[] = '((' . DB_PREFIX . '_data.price*' . DB_PREFIX . '_currency.course)/' . $CM->getCourse(CURRENT_CURRENCY) . ') AS price_ue';

            $left_joins[] = 'LEFT JOIN ' . DB_PREFIX . '_currency ON ' . DB_PREFIX . '_data.currency_id=' . DB_PREFIX . '_currency.currency_id';
        } else {
            $select_what[] = DB_PREFIX . '_data.price AS price_ue';
        }


        if (isset($params['_no_interactive_search']) && 1 == (int) $params['_no_interactive_search']) {
            
        } else {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/template_search.php')) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/template_search.php');
                $Template_Search = new Template_Search();
                $results = $Template_Search->run();
                if (isset($results['where'])) {
                    $where_array = array_merge($where_array, $results['where']);
                    $where_array_prepared = array_merge($where_array_prepared, $results['where']);
                }
                if (isset($results['params'])) {
                    $params = array_merge($params, $results['params']);
                }
            }
        }
        unset($params['_no_interactive_search']);

        $REQUESTURIPATH = Sitebill::getClearRequestURI();
        if ($REQUESTURIPATH == 'admin' or $this->getConfigValue('allow_tags_search_frontend')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/data/data_manager.php');

            $DM = new Data_Manager();
            $tagged_params = $DM->add_tags_params($params);
            $where_array_prepared = $DM->add_tagged_parms_to_where($where_array_prepared, $tagged_params);
        }

        if (count($where_array) > 0) {
            $where_statement = " WHERE " . implode(' AND ', $where_array);
        }

        if (count($where_array_prepared) > 0) {
            $where_statement_prepared = " WHERE " . implode(' AND ', $where_array_prepared);
        }


        $DBC = DBC::getInstance();


        global $smarty;
        $select_what = array();

        $select_what[] = DB_PREFIX . '_data.id, ' . DB_PREFIX . '_data.geo_lat, ' . DB_PREFIX . '_data.geo_lng';


        $query = 'SELECT ' . implode(', ', $select_what) . ' ' . $add_select_value . ' FROM ' . DB_PREFIX . '_data' . (count($left_joins) > 0 ? ' ' . implode(' ', $left_joins) . ' ' : '') . ' ' . $where_statement_prepared;

        $stmt = $DBC->query($query, $where_value_prepared);


        $ra = array();
        if ($stmt) {

            $i = 0;
            while ($ar = $DBC->fetch($stmt)) {
                $ra[$i] = $ar;
                $i++;
            }
        }



        $return['_total_records'] = count($ra);
        $return['data'] = $ra;
        return $return;
    }

    /* function get_sitebill_adv_core_ids( $params, $random = false, $premium=false, $paging=true, $geodata=false ) {
      $select_fields=array();
      $return=array();

      $is_route_catch=$this->getRequestValue('router_info');
      $is_country_view=$this->getRequestValue('country_view');
      $is_region_view=$this->getRequestValue('region_view');
      $is_city_view=$this->getRequestValue('city_view');
      $is_complex_view=$this->getRequestValue('complex_view');
      $is_find_view=intval($this->getRequestValue('find_url_catched'));
      $predefined_info=$this->getRequestValue('predefined_info');

      $this_is_favorites=false;

      if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable')==1){
      $_billing_on=true;
      }else{
      $_billing_on=false;
      }

      if(isset($params['favorites']) && !empty($params['favorites'])){
      $this_is_favorites=true;
      }

      require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php';

      if ( $this->getConfigValue('currency_enable') ) {

      require_once SITEBILL_DOCUMENT_ROOT.'/apps/currency/admin/admin.php';
      $CM=new currency_admin();
      }



      $this->grid_total = 0;

      $preparedParams=$this->prepareRequestParams($params, $premium);


      $where_array=$preparedParams['where_array'];
      $add_from_table=$preparedParams['add_from_table'];
      $add_select_value=$preparedParams['add_select_value'];
      $params=$preparedParams['params'];

      $where_array_prepared=$preparedParams['where_array_prepared'];
      $where_value_prepared=$preparedParams['where_value_prepared'];

      $select_what=$preparedParams['select_what'];
      $left_joins=$preparedParams['left_joins'];

      //$left_joins[]='LEFT JOIN '.DB_PREFIX.'_topic ON '.DB_PREFIX.'_data.topic_id='.DB_PREFIX.'_topic.id';

      if ( $this->getConfigValue('currency_enable') ) {
      $select_what[]=DB_PREFIX.'_currency.code AS currency_code';
      $select_what[]=DB_PREFIX.'_currency.name AS currency_name';
      $select_what[]='(('.DB_PREFIX.'_data.price*'.DB_PREFIX.'_currency.course)/'.$CM->getCourse(CURRENT_CURRENCY).') AS price_ue';

      $left_joins[]='LEFT JOIN '.DB_PREFIX.'_currency ON '.DB_PREFIX.'_data.currency_id='.DB_PREFIX.'_currency.currency_id';
      }else{
      $select_what[]=DB_PREFIX.'_data.price AS price_ue';
      }


      $REQUESTURIPATH=Sitebill::getClearRequestURI();
      if($REQUESTURIPATH=='admin' or $this->getConfigValue('allow_tags_search_frontend')){
      require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/data/data_manager.php');

      $DM=new Data_Manager();
      $tagged_params = $DM->add_tags_params($params);
      $where_array_prepared = $DM->add_tagged_parms_to_where($where_array_prepared, $tagged_params);
      }

      if ( count($where_array)>0 ) {
      $where_statement = " WHERE ".implode(' AND ', $where_array);
      }

      if ( count($where_array_prepared)>0 ) {
      $where_statement_prepared = " WHERE ".implode(' AND ', $where_array_prepared);
      }

      $order=$this->prepareSortOrder($params, $random, $premium);


      if ( !isset($params['page']) || (int)$params['page'] == 0 ) {
      $page = 1;
      } else {
      $page = (int)$params['page'];
      }
      $DBC=DBC::getInstance();
      if($paging){


      $query = 'SELECT COUNT('.DB_PREFIX.'_data.id) AS total FROM '.DB_PREFIX.'_data'.(count($left_joins)>0 ? ' '.implode(' ', $left_joins).' ' : '').' '.$where_statement_prepared;
      $md5_query_sum = md5($query.implode('', $where_value_prepared));

      $get_cache_value = false;
      if ( $this->getConfigValue('query_cache_enable') ) {
      //Попробуем получить значение счетчика из кэша
      $cache_query = "select `value` from ".DB_PREFIX."_cache where parameter = ? and valid_for > ?";
      $stmt=$DBC->query($cache_query, array($md5_query_sum, time()));
      if($stmt){
      $ar=$DBC->fetch($stmt);
      $total = $ar['value'];
      $this->grid_total = $total;
      $get_cache_value = true;
      }
      }

      //Если нет кэшированного значения для данного запроса, то делаем запрос в базу
      if ( !$get_cache_value ) {
      $stmt=$DBC->query($query, $where_value_prepared);

      $total = 0;
      $this->grid_total = $total;
      if(!$stmt){
      $total = 0;
      $this->grid_total = $total;
      //return array();
      }else{
      $ar=$DBC->fetch($stmt);
      $total = $ar['total'];
      $this->grid_total = $total;
      }
      //Если кэш включен, то добавляем значение в кэш
      if ( $this->getConfigValue('query_cache_enable') ) {
      $query_insert_cache = "insert into ".DB_PREFIX."_cache (`parameter`, `value`, `created_at`, `valid_for`) values (?, ?, ?, ?)";
      $stmt=$DBC->query($query_insert_cache, array($md5_query_sum, $total, time(), time()+$this->getConfigValue('query_cache_time')));
      }
      }
      if ( $this->getConfigValue('query_cache_enable') ) {
      //Очищаем старые записи кэша
      $query_delete_cache = "delete from ".DB_PREFIX."_cache where `created_at`<?";
      $stmt=$DBC->query($query_delete_cache, array(time()-$this->getConfigValue('query_cache_time')));
      }
      }


      $pageLimitParams=$this->preparePageLimitParams($params, $page, $total, $premium);
      $start=$pageLimitParams['start'];
      $limit=$pageLimitParams['limit'];
      $max_page=$pageLimitParams['max_page'];
      $page = (isset($params['page']) ? (int)$params['page'] : 0);

      if ( $_REQUEST['REST_API'] == 1 ) {
      if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/api/classes/class.static_data.php') ) {
      $static_data = Static_Data::getInstance();
      $static_data::set_param('max_page', $max_page);
      }
      }



      if(count($select_fields)==0){
      $select_what[]=DB_PREFIX.'_data.id';
      }else{
      $select_what=array_merge($select_what, $select_fields);
      }

      $query = 'SELECT '.implode(', ', $select_what).' '.$add_select_value.'
      FROM '.DB_PREFIX.'_data'.(count($left_joins)>0 ? ' '.implode(' ', $left_joins).' ' : '').' '.$where_statement_prepared.'
      ORDER BY '.$order.((isset($params['no_portions']) && $params['no_portions']==1) ? '' : ' LIMIT '.$start.', '.$limit);
      //echo $query;
      //print_r($where_value_prepared);
      $stmt=$DBC->query($query, $where_value_prepared);

      $ra = array();
      if($stmt){
      while($ar=$DBC->fetch($stmt)){
      $ra[] = $ar['id'];
      }
      }



      $return['_total_records']=$total;
      $return['_max_page']=$max_page;
      //$return['_params']=$params;
      //$return['_mysearch_params']=$mysearch_params;

      $return['data']=$ra;
      //$return['order']=$order;
      return $return;
      } */

    function get_sitebill_adv_core($params, $random = false, $premium = false, $paging = true, $geodata = false) {
        
        $ids_only = false;
        $geo_only = false;
        
        if(isset($params['ids_only'])){
            $ids_only = true;
        }

        $select_fields = array();
        
        if(isset($params['geo_only'])){
            $select_fields = array(
                DB_PREFIX.'_data.id',
                DB_PREFIX.'_data.geo_lat',
                DB_PREFIX.'_data.geo_lng'
            );
            $geo_only = true;
            unset($params['geo_only']);
        }
		
		$routed_params = array();
		if(isset($params['routed_params'])){
            $routed_params = $params['routed_params'];
            unset($params['routed_params']);
        }
		
        //print_r($select_fields);
        
        $return = array();

        $is_route_catch = $this->getRequestValue('router_info');
        $is_country_view = $this->getRequestValue('country_view');
        $is_region_view = $this->getRequestValue('region_view');
        $is_city_view = $this->getRequestValue('city_view');
        $is_metro_view = $this->getRequestValue('metro_view');
        $is_district_view = $this->getRequestValue('district_view');
        $is_complex_view = $this->getRequestValue('complex_view');
        $is_find_view = intval($this->getRequestValue('find_url_catched'));
        $predefined_info = $this->getRequestValue('predefined_info');
        $is_user_view = $this->getRequestValue('user_view');

        $this_is_favorites = false;

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable') == 1) {
            //$_billing_on=true;
            $this->billing_mode = true;
        } else {
            //$_billing_on=false;
            $this->billing_mode = false;
        }

        if (isset($params['favorites']) && !empty($params['favorites'])) {
            $this_is_favorites = true;
        }

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';

        if ($this->getConfigValue('currency_enable')) {

            require_once SITEBILL_DOCUMENT_ROOT . '/apps/currency/admin/admin.php';
            $CM = new currency_admin();
            $this->currency_admin = $CM;
        }

        if (1 === intval($this->getConfigValue('core.listing.add_user_info'))) {
            $_collect_user_info = true;
            unset($params['_collect_user_info']);
        } else {
            if (isset($params['_collect_user_info']) && $params['_collect_user_info'] == 1) {
                $_collect_user_info = true;
                unset($params['_collect_user_info']);
            } else {
                $_collect_user_info = false;
            }
        }
        
        if($geo_only){
            $_collect_user_info = false;
        }



        $this->grid_total = 0;

        $preparedParams = $this->prepareRequestParams($params, $premium);

        $where_array = $preparedParams['where_array'];
        $add_from_table = $preparedParams['add_from_table'];
        $add_select_value = $preparedParams['add_select_value'];
        $params = $preparedParams['params'];

        $where_array_prepared = $preparedParams['where_array_prepared'];
        $where_value_prepared = $preparedParams['where_value_prepared'];

        $where_statement_prepared = '';

        $select_what = $preparedParams['select_what'];
        $left_joins = $preparedParams['left_joins'];

        //$left_joins[]='LEFT JOIN '.DB_PREFIX.'_topic ON '.DB_PREFIX.'_data.topic_id='.DB_PREFIX.'_topic.id';

        if ($this->getConfigValue('currency_enable')) {
            $select_what[] = DB_PREFIX . '_currency.code AS currency_code';
            $select_what[] = DB_PREFIX . '_currency.name AS currency_name';
            $select_what[] = '((' . DB_PREFIX . '_data.price*' . DB_PREFIX . '_currency.course)/' . $CM->getCourse(CURRENT_CURRENCY) . ') AS price_ue';

            $left_joins[] = 'LEFT JOIN ' . DB_PREFIX . '_currency ON ' . DB_PREFIX . '_data.currency_id=' . DB_PREFIX . '_currency.currency_id';
        } else {
            $select_what[] = DB_PREFIX . '_data.price AS price_ue';
        }


        if (isset($params['_no_interactive_search']) && 1 == (int) $params['_no_interactive_search']) {
            
        } else {

            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/template_search.php')) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/template_search.php');
                $Template_Search = new Template_Search();
                $results = $Template_Search->run();
                
                if (isset($results['where_prepared'])) {
                	$where_array_prepared = array_merge($where_array_prepared, $results['where_prepared']);
                }
                if (isset($results['where_value_prepared'])) {
                	$where_value_prepared = array_merge($where_value_prepared, $results['where_value_prepared']);
                }

                if (isset($results['where'])) {
                    $where_array = array_merge($where_array, $results['where']);
                    $where_array_prepared = array_merge($where_array_prepared, $results['where']);
                }
                if (isset($results['params'])) {
                    $params = array_merge($params, $results['params']);
                }
            }
        }
        unset($params['_no_interactive_search']);



        $REQUESTURIPATH = Sitebill::getClearRequestURI();
        if ($REQUESTURIPATH == 'admin' || $REQUESTURIPATH == 'admin/index.php' || $this->getConfigValue('allow_tags_search_frontend')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/data/data_manager.php');

            $DM = new Data_Manager();
            $tagged_params = $DM->add_tags_params($params);

            $where_array_prepared = $DM->add_tagged_parms_to_where($where_array_prepared, $tagged_params);
        }

        if (count($where_array) > 0) {
            $where_statement = " WHERE " . implode(' AND ', $where_array);
        }

        if (count($where_array_prepared) > 0) {
            $where_statement_prepared = " WHERE " . implode(' AND ', $where_array_prepared);
        }

        $order = $this->prepareSortOrder($params, $random, $premium);


        if (!isset($params['page']) || (int) $params['page'] == 0) {
            $page = 1;
        } else {
            $page = (int) $params['page'];
        }
        $DBC = DBC::getInstance();
        if ($paging) {


            $query = 'SELECT COUNT(' . DB_PREFIX . '_data.id) AS total FROM ' . DB_PREFIX . '_data' . (count($left_joins) > 0 ? ' ' . implode(' ', $left_joins) . ' ' : '') . ' ' . $where_statement_prepared;
            $md5_query_sum = md5($query . implode('', $where_value_prepared));

            $get_cache_value = false;
            if ($this->getConfigValue('query_cache_enable')) {
                //Попробуем получить значение счетчика из кэша
                $cache_query = "select `value` from " . DB_PREFIX . "_cache where parameter = ? and valid_for > ?";
                $stmt = $DBC->query($cache_query, array($md5_query_sum, time()));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $total = $ar['value'];
                    $this->grid_total = $total;
                    $get_cache_value = true;
                }
            }

            //Если нет кэшированного значения для данного запроса, то делаем запрос в базу
            if (!$get_cache_value) {
                $stmt = $DBC->query($query, $where_value_prepared);

                $total = 0;
                $this->grid_total = $total;
                if (!$stmt) {
                    $total = 0;
                    $this->grid_total = $total;
                    //return array();
                } else {
                    $ar = $DBC->fetch($stmt);
                    $total = $ar['total'];
                    $this->grid_total = $total;
                }
                //Если кэш включен, то добавляем значение в кэш
                if ($this->getConfigValue('query_cache_enable')) {
                    $query_insert_cache = "insert into " . DB_PREFIX . "_cache (`parameter`, `value`, `created_at`, `valid_for`) values (?, ?, ?, ?)";
                    $stmt = $DBC->query($query_insert_cache, array($md5_query_sum, $total, time(), time() + $this->getConfigValue('query_cache_time')));
                }
            }
            if ($this->getConfigValue('query_cache_enable')) {
                //Очищаем старые записи кэша
                $query_delete_cache = "delete from " . DB_PREFIX . "_cache where `created_at`<?";
                $stmt = $DBC->query($query_delete_cache, array(time() - $this->getConfigValue('query_cache_time')));
            }
        }
        //echo $this->grid_total;

        global $smarty;


        $pageLimitParams = $this->preparePageLimitParams($params, $page, $total, $premium);
        $start = $pageLimitParams['start'];
        $limit = $pageLimitParams['limit'];
        $max_page = $pageLimitParams['max_page'];
        $page = (isset($params['page']) ? (int) $params['page'] : 0);

        if (isset($_REQUEST['REST_API']) && $_REQUEST['REST_API'] == 1) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.static_data.php')) {
                $static_data = Static_Data::getInstance();
                $static_data::set_param('max_page', $max_page);
            }
        }
		
		if(!empty($routed_params)){
			foreach($routed_params as $rk => $rv){
				unset($params[$rk]);
			}
		}
		
		
        $this->template->assign('grid_params', $params);

        $pager_params = $params;

        $mysearch_params = $params;
        //print_r($mysearch_params);
        //$_SESSION['mysearch_params']=array();
        unset($mysearch_params['page']);
        unset($mysearch_params['order']);
        unset($mysearch_params['asc']);
        unset($mysearch_params['favorites']);
        unset($mysearch_params['search']);
        unset($mysearch_params['extended_search']);
        /*
          if(!empty($mysearch_params)){
          $_SESSION['mysearch_params']=$mysearch_params;
          } */

        unset($params['order']);
        unset($params['asc']);
        unset($params['favorites']);

        if (preg_match('/\/special\//', $_SERVER['REQUEST_URI'])) {
            unset($params['spec']);
            unset($pager_params['spec']);
        }


        //$catched_router=$this->getCatchedRoute();



        if (isset($params['pager_url'])) {
            $pageurl = $params['pager_url'];
            unset($params['pager_url']);
            unset($pager_params['pager_url']);
        } elseif ($is_find_view == 1) {
            $pageurl = 'find';
        } elseif ('' != $is_country_view) {
            unset($pager_params['country_id']);
            $pageurl = $is_country_view;
        } elseif ($is_route_catch != '') {
            $pageurl = $is_route_catch['alias'];
            foreach ($is_route_catch['params'] as $k => $v) {
                unset($pager_params[$k]);
            }
        } elseif ($predefined_info != '') {
            $pageurl = $predefined_info['alias'];
            foreach ($predefined_info['params'] as $k => $v) {
                unset($pager_params[$k]);
            }
        } elseif ($is_city_view) {
            unset($pager_params['city_id']);
            $pageurl = $is_city_view;
        } elseif ($is_region_view) {
            unset($pager_params['region_id']);
            $pageurl = $is_region_view;
        } elseif ($is_metro_view) {
            unset($pager_params['metro_id']);
            $pageurl = $is_metro_view;
        } elseif ($is_district_view) {
            unset($pager_params['district_id']);
            $pageurl = $is_district_view;
        } elseif ('' != $is_complex_view) {
            unset($pager_params['complex_id']);
            $pageurl = $is_complex_view;
        } elseif ($is_user_view) {
            unset($pager_params['user_id']);
            $pageurl = $is_user_view;
        } else {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $Structure_Manager = new Structure_Manager();
            $category_structure = $Structure_Manager->loadCategoryStructure();
            if ($this_is_favorites) {
                $pageurl = 'myfavorites';
                unset($params['favorites']);
                unset($pager_params['favorites']);
            } else {
                if (isset($params['topic_id']) && !is_array($params['topic_id']) && $params['topic_id'] != '') {
                    if (!isset($params['admin']) || !$params['admin']) {
                        if ($this->cityTopicUrlFind($_SERVER['REQUEST_URI'])) {
                            $p = parse_url($_SERVER['REQUEST_URI']);
                            unset($params['city_id']);
                            unset($params['topic_id']);
                            unset($pager_params['city_id']);
                            unset($pager_params['topic_id']);
                            $pageurl = trim($p['path'], '/');
                        } elseif ($category_structure['catalog'][$params['topic_id']]['url'] != '' && 1 == $this->getConfigValue('apps.seo.level_enable')) {
                            $pageurl = $category_structure['catalog'][$params['topic_id']]['url'];
                            //unset($pager_params['topic_id']);
                            unset($params['topic_id']);
                            unset($pager_params['topic_id']);
                        } elseif ($category_structure['catalog'][$params['topic_id']]['url'] != '') {
                            //echo 1;
                            $pageurl = $category_structure['catalog'][$params['topic_id']]['url'];
                            unset($pager_params['topic_id']);
                            unset($params['topic_id']);
                        } else {
                            if (preg_match('/topic(\d*).html/', $_SERVER['REQUEST_URI'])) {
                                unset($pager_params['topic_id']);
                            }
                            if ($params['topic_id'] != 0) {
                                $pageurl = 'topic' . $params['topic_id'] . '.html';
                                unset($params['topic_id']);
                            } else {
                                $pageurl = '';
                                unset($params['topic_id']);
                                unset($pager_params['topic_id']);
                            }
                        }
                    } else {
                        $pageurl = '';
                    }
                } else {
                    $pageurl = '';
                }
            }
        }
        $pager_params['page_url'] = $pageurl;

        if ($paging) {

            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/page_navigator.php')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/page_navigator.php';
                $url = '';
                if (isset($params['pager_url'])) {
                    $url = $params['pager_url'];
                    unset($params['pager_url']);
                }

                if (isset($params['admin']) && $params['admin']) {
                    $nurl = 'account/data';
                } else {
                    $nurl = $pageurl;
                }
                //print_r($params);
                $_params = $pager_params;
                unset($_params['page_url']);
                $paging = Page_Navigator::getPagingArray($total, $page, $limit, $_params, $nurl);
                //$this->template->assert('pager_array', $paging);
            }
            $return['paging'] = $paging;


            $return['pager'] = $this->get_page_links_list($page, $total, $limit, $pager_params);
        }

        $pairs = array();
        //var_dump(http_build_query($pager_params));

        unset($pager_params['page_url']);
        unset($pager_params['page_limit']);
        if (!isset($params['admin']) || $params['admin'] != 1) {
            unset($pager_params['topic_id']);
        }

        if (is_array($pager_params)) {
            $url = $pageurl . '?' . urldecode(http_build_query($pager_params));
        } else {
            $url = $pageurl . '?key=value';
        }

        /* foreach ( $pager_params as $key => $value ) {
          if($key=='page_url' || $key=='page_limit'){

          }else{

          if(is_array($value)){
          if(count($value)>0){
          foreach($value as $v){
          if(is_array($v)){
          foreach($v as $sk=>$sv){
          $pairs[] = $key.'['.$sk.']='.$sv;
          }
          }else{
          if($v!=''){
          $pairs[] = $key.'[]='.$v;
          }
          }


          }
          }
          }elseif ( $value != '') {
          if($key!='topic_id'){
          $pairs[] = "$key=$value";
          }elseif($params['admin']){
          $pairs[] = "$key=$value";
          }

          }
          }

          } */

        /* if ( is_array($pairs) ) {
          $url = $pageurl.'?'.implode('&', $pairs);
          }else{
          $url = $pageurl.'?key=value';
          } */
        $return['pagerurl'] = $url;
        //$this->template->assert('pagerurl', $url);

        $pairs = array();
        if ($is_country_view) {
            unset($params['country_id']);
        }

        foreach ($params as $key => $value) {
            if (is_array($value)) {
                if (count($value) > 0) {
                    foreach ($value as $v) {
                        if ($v != '') {
                            $pairs[] = $key . '[]=' . $v;
                        }
                    }
                }
            } elseif ($value != '') {
                /* if($key!='topic_id'){
                  $pairs[] = "$key=$value";
                  }elseif($params['admin']){
                  $pairs[] = "$key=$value";
                  } */
                $pairs[] = "$key=$value";
            }
        }



        if ($is_country_view) {

            if (!empty($params)) {
                $url = $is_country_view . '?' . urldecode(http_build_query($params));
            } else {
                $url = $is_country_view . '?';
            }

            /* if ( is_array($pairs) ) {
              $url = $is_country_view.'?'.implode('&', $pairs);
              }else{
              $url = $is_country_view.'?';
              } */
        } else {
            if (!empty($params)) {
                $url = $pageurl . '?' . urldecode(http_build_query($params));
            } else {
                $url = $pageurl . '?key=value';
            }
            /* if ( is_array($pairs) ) {
              $url = $pageurl.'?'.implode('&', $pairs);
              }else{
              $url = $pageurl.'?key=value';
              } */
        }

        //$this->template->assert('url', $url);
        $return['url'] = $url;

        if (count($select_fields) == 0) {
            $select_what[] = DB_PREFIX . '_data.*';
        } else {
            $select_what = array_merge($select_what, $select_fields);
        }

        $query = 'SELECT ' . implode(', ', $select_what) . ' ' . $add_select_value . ' FROM ' . DB_PREFIX . '_data' . (count($left_joins) > 0 ? ' ' . implode(' ', $left_joins) . ' ' : '') . ' ' . $where_statement_prepared . ($order!='' ? ' ORDER BY '.$order : '') . ((isset($params['no_portions']) && $params['no_portions'] == 1) ? '' : ' LIMIT ' . $start . ', ' . $limit);
        //$this->writeLog(__METHOD__.', q = '.$query);
        //$this->writeLog(__METHOD__.', where_value_prepared = '. var_export($where_value_prepared, true));
//echo $query;
        
        $stmt = $DBC->query($query, $where_value_prepared, $success);
        //echo $DBC->getLastError();
        //print_r($where_value_prepared);
        //echo $query;
        //var_dump($stmt);
        
        $ra = array();
        if ($stmt) {

            $i = 0;
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/company/company.xml')) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
                $Account = new Account;
            }

            while ($ar = $DBC->fetch($stmt)) {
                if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/company/company.xml')) {
                    $company_profile = $Account->get_company_profile($ar['user_id']);
                    $ar['company'] = $company_profile['name']['value'];
                }
                if($ids_only){
                    $ra[$i] = $ar['id'];
                }else{
                    $ra[$i] = $ar;
                }
                
                $i++;
            }
        }

        if (count($ra) > 0 && !$ids_only && !$geo_only) {
            $ra = $this->transformGridData($ra, $_collect_user_info);
        }

        if ($geodata && count($ra) > 0 && !$ids_only) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/realty_on_map.tpl')) {
                $geotpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/realty_on_map.tpl';
            } else {
                $geotpl = '';
            }

            $gdt = $this->prepareDataForGeo($ra, $geotpl);
            
            
            $return['geoobjects_collection_clustered'] = $gdt['geoobjects_collection_clustered'];
            $return['grid_geodata'] = $gdt['grid_geodata'];
        }



        $return['_total_records'] = $total;
        $return['_max_page'] = $max_page;
        $return['_per_page'] = $limit;
        $return['_showed'] = count($ra);
        $return['_params'] = $params;
        $return['_mysearch_params'] = $mysearch_params;
        $return['_grid_show_start'] = $start+1;
        $return['_grid_show_end'] = (($start+$limit)>$total ? $total : ($start+$limit));
        

        $return['data'] = $ra;
        $return['order'] = $order;
        return $return;
    }

    function prepareDataForGeo(&$ra, $geotpl) {
        global $smarty;
        $gdata = array();
        //return false;

        foreach ($ra as $k => $d) {

            if (isset($d['geo_lat']) && isset($d['geo_lng']) && $d['geo_lat'] != '' && $d['geo_lat'] != '0.000000' && $d['geo_lng'] != '' && $d['geo_lng'] != '0.000000') {
                $gdata[$k]['currency_name'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['currency_name']);
                if (isset($d['currency_id'])) {
                    $gdata[$k]['currency_id'] = $d['currency_id'];
                }
                if ((int) $d['price'] != 0) {
                    $gdata[$k]['price'] = number_format($d['price'], 0, '.', ' ');
                } else {
                    $gdata[$k]['price'] = $d['price'];
                }
                if (isset($d['type_sh'])) {
                    $gdata[$k]['type_sh'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['type_sh']);
                }

                $address = array();
                if (isset($d['city'])) {
                    $address[] = $d['city'];
                    $gdata[$k]['city'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['city']);
                }
                if (isset($d['street'])) {
                    $address[] = $d['street'];
                    $gdata[$k]['street'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['street']);
                }
                if (isset($d['number']) && $d['number'] != '' && $d['number'] != 0) {
                    $address[] = $d['number'];
                }
                if (isset($d['price'])) {
                    $address[] = $d['price'];
                }
                $gdata[$k]['topic_id'] = $d['topic_id'];

                $gdata[$k]['title'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', implode(', ', $address));

                if ($geotpl != '') {
                    $smarty->assign('realty', $d);
                    $html = $smarty->fetch($geotpl);
                    $html = str_replace("\r\n", ' ', $html);
                    $html = str_replace("\n", ' ', $html);
                    $html = str_replace("\t", ' ', $html);
                    //$html = htmlspecialchars($html);
                    $html = addslashes($html);
                } else {
                    $html = '';
                }


                $gdata[$k]['html'] = SiteBill::iconv(SITE_ENCODING, 'utf-8', $html);
                //$gdata[$k]['html']='';
                $gdata[$k]['geo_lat'] = $d['geo_lat'];
                $gdata[$k]['geo_lng'] = $d['geo_lng'];
                $gdata[$k]['href'] = $d['href'];
                $gdata[$k]['id'] = $d['id'];
                $gdata[$k]['parent_category_url'] = (isset($d['parent_category_url']) ? $d['parent_category_url'] : '');
                if (isset($d['bold_status_map_end'])) {
                    $gdata[$k]['bold_status_map_end'] = $d['bold_status_map_end'];
                }

                unset($html);
            }
        }
        
        
        
        if ( $this->getConfigValue('apps.complex.push_map') ) {
            require_once (SITEBILL_DOCUMENT_ROOT.'/apps/complex/admin/admin.php');
            $complex_admin = new complex_admin();
            $complex_geodata = $complex_admin->get_geodata();
            if ( $complex_geodata ) {
                $gdata = array_merge($gdata, $complex_geodata);
            }
        }
        
        if ( $this->getConfigValue('apps.mapbanner.enable') ) {
            require_once (SITEBILL_DOCUMENT_ROOT.'/apps/mapbanner/admin/admin.php');
            $mapbanner_admin = new mapbanner_admin();
            $mapbanner_geodata = $mapbanner_admin->get_geodata();
            if ( $mapbanner_geodata ) {
                $gdata = array_merge($gdata, $mapbanner_geodata);
            }
        }
        


        $geoobjects_collection = array();
        if (count($gdata) > 0) {
            foreach ($gdata as $gd) {
                $gc = $gd['geo_lat'] . '_' . $gd['geo_lng'];
                if (isset($geoobjects_collection[$gc])) {
                    $geoobjects_collection[$gc]['html'] .= $gd['html'];
                    if ( isset($gd['banner']) ) {
                        $geoobjects_collection[$gc]['banner'] = $gd['banner'];
                    } else {
                        $geoobjects_collection[$gc]['banner'] = false;
                    }
                    $geoobjects_collection[$gc]['count'] ++;
                    $geoobjects_collection[$gc]['ids'][] = $gd['id'];
                } else {
                    /* if($gd['topic_id']==44){
                      $geoobjects_collection[$gc]['icon']='map';
                      } */
                    $geoobjects_collection[$gc]['lat'] = $gd['geo_lat'];
                    $geoobjects_collection[$gc]['lng'] = $gd['geo_lng'];
                    if ( isset($gd['banner']) ) {
                        $geoobjects_collection[$gc]['banner'] = $gd['banner'];
                    } else {
                        $geoobjects_collection[$gc]['banner'] = false;
                    }
                    $geoobjects_collection[$gc]['html'] = $gd['html'];
                    $geoobjects_collection[$gc]['count'] = 1;
                    $geoobjects_collection[$gc]['ids'][] = $gd['id'];
                    if (isset($gd['bold_status_map_end'])) {
                        $geoobjects_collection[$gc]['bold_status_map_end'] = $gd['bold_status_map_end'];
                    }
                }
            }
        }
        $return['geoobjects_collection_clustered'] = $geoobjects_collection;
        $return['grid_geodata'] = $this->generateGridGeoDataOld($ra);
        return $return;
    }

    function get_sitebill_adv_ext_base_ajax($params, $random = false, $premium = false, $paging = true, $geodata = false) {
        $data = $this->get_sitebill_adv_core($params, $random, $premium, true, true);
        return $data;
    }

    function getTranslitAlias($city, $street, $number) {
        if ($city != '') {
            $p[] = $this->transliteMe($city);
        }
        if ($street != '') {
            $p[] = $this->transliteMe($street);
        }
        if ((int) $number != 0) {
            $p[] = (int) $number;
        }
        return implode('-', $p);
    }

    function get_sitebill_adv_ext2($params, $random = false) {
        $QB = new Query_Builder();
        $QB->addSelectFrom(DB_PREFIX . '_data');
        $QB->addSelectWhat(DB_PREFIX . '_data.*');
        $QB->addSelectWhat(DB_PREFIX . '_topic.name AS topic');
        $QB->addSelectWhat(DB_PREFIX . '_city.name AS city');
        $QB->addSelectWhat(DB_PREFIX . '_street.name AS street');
        $QB->addSelectWhat(DB_PREFIX . '_district.name AS district');
        $QB->addLeftJoin(DB_PREFIX . '_topic', DB_PREFIX . '_topic.id=' . DB_PREFIX . '_data.topic_id');
        $QB->addLeftJoin(DB_PREFIX . '_city', DB_PREFIX . '_city.city_id=' . DB_PREFIX . '_data.city_id');
        $QB->addLeftJoin(DB_PREFIX . '_street', DB_PREFIX . '_street.street_id=' . DB_PREFIX . '_data.street_id');
        $QB->addLeftJoin(DB_PREFIX . '_district', DB_PREFIX . '_district.id=' . DB_PREFIX . '_data.district_id');

        echo $QB->build();

        if ($this->getConfigValue('currency_enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/currency/admin/admin.php';
            $CM = new currency_admin();
        }

        $this->grid_total = 0;
        $where_array = false;

        if ($params['order'] == 'city') {

            $where_array[] = 're_city.city_id=re_data.city_id';
            $add_from_table .= ' , re_city ';
            $add_select_value .= ' , re_city.name as city ';
        }

        if ($params['order'] == 'district') {
            $where_array[] = 're_district.id=re_data.district_id';
            $add_from_table .= ' , re_district ';
            $add_select_value .= ' , re_district.name as district ';
        }

        if ($params['order'] == 'metro') {
            $where_array[] = 're_metro.metro_id=re_data.metro_id';
            $add_from_table .= ' , re_metro ';
            $add_select_value .= ' , re_metro.name as metro ';
        }

        if ($params['order'] == 'street') {
            $where_array[] = 're_street.street_id=re_data.street_id';
            $add_from_table .= ' , re_street ';
            $add_select_value .= ' , re_street.name as street ';
        }


        if (isset($params['favorites']) && !empty($params['favorites'])) {
            $QB->addCondition(DB_PREFIX . '_data.id', 'in', $params['favorites']);
            $where_array[] = 're_data.id IN (' . implode(',', $params['favorites']) . ')';
        }



        if (isset($params['optype'])) {
            $QB->addCondition(DB_PREFIX . '_data.optype', 'eq', (int) $params['optype']);
            $where_array[] = DB_PREFIX . '_data.optype=' . (int) $params['optype'];
        }

        $where_array[] = 're_topic.id=re_data.topic_id';

        //echo '$params[\'topic_id\'] = '.$params['topic_id'].'<br>';

        if ($params['topic_id'] != '' && $params['topic_id'] != 0) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $Structure_Manager = new Structure_Manager();
            $category_structure = $Structure_Manager->loadCategoryStructure();
            global $smarty;
            //echo $category_structure['catalog'][$params['topic_id']]['description'];
            $smarty->assign('topic_description', $category_structure['catalog'][$params['topic_id']]['description']);

            $childs = $Structure_Manager->get_all_childs($params['topic_id'], $category_structure);
            if (count($childs) > 0) {
                array_push($childs, $params['topic_id']);
                $where_array[] = 're_data.topic_id in (' . implode(' , ', $childs) . ') ';
                $QB->addCondition(DB_PREFIX . '_data.topic_id', 'in', $childs);
            } else {
                $where_array[] = 're_data.topic_id=' . $params['topic_id'];
                $QB->addCondition(DB_PREFIX . '_data.topic_id', 'eq', $params['topic_id']);
            }
            //print_r($params);
        }

        if (isset($params['country_id']) and $params['country_id'] != 0) {
            $where_array[] = 're_data.country_id = ' . $params['country_id'];
            $QB->addCondition(DB_PREFIX . '_data.country_id', 'eq', $params['country_id']);
        } else {
            unset($params['country_id']);
        }

        if (isset($params['id']) and $params['id'] != 0) {
            $where_array[] = 're_data.id = ' . $params['id'];
            $QB->addCondition(DB_PREFIX . '_data.id', 'eq', $params['id']);
        }

        if (isset($params['mvids']) && is_array($params['mvids']) && count($params['mvids']) != 0) {
            $where_array[] = 're_data.id IN (' . implode(',', $params['mvids']) . ')';
        }


        if (isset($params['user_id']) && $params['user_id'] > 0) {
            $where_array[] = 're_data.user_id = ' . $params['user_id'];
            $QB->addCondition(DB_PREFIX . '_data.user_id', 'eq', $params['user_id']);
        }

        if (isset($params['onlyspecial']) && $params['onlyspecial'] > 0) {
            $where_array[] = 're_data.hot = 1';
            $QB->addCondition(DB_PREFIX . '_data.hot', 'eq', 1);
        }


        if (isset($params['price']) && $params['price'] != 0) {
            $where_array[] = 're_data.price  <= ' . $params['price'];
            $QB->addCondition(DB_PREFIX . '_data.price', '<=', $params['price']);
        }

        if (isset($params['price_min']) && $params['price_min'] != 0) {
            $where_array[] = 're_data.price  >= ' . $params['price_min'];
            $QB->addCondition(DB_PREFIX . '_data.price', '>=', $params['price_min']);
        }

        if (isset($params['house_number']) && $params['house_number'] != 0) {
            $where_array[] = 're_data.number  = \'' . $params['house_number'] . '\'';
            $QB->addCondition(DB_PREFIX . '_data.number', '=', $params['house_number']);
        } else {
            unset($params['house_number']);
        }


        if (isset($params['region_id']) && $params['region_id'] != 0) {
            $where_array[] = 're_data.region_id = ' . $params['region_id'];
            $QB->addCondition(DB_PREFIX . '_data.region_id', '=', $params['region_id']);
        } else {
            unset($params['region_id']);
        }

        if (isset($params['spec'])) {
            $where_array[] = ' re_data.hot = 1 ';
            $QB->addCondition(DB_PREFIX . '_data.hot', '=', 1);
        }
        if (isset($params['hot'])) {
            $where_array[] = ' re_data.hot = 1 ';
            $QB->addCondition(DB_PREFIX . '_data.hot', '=', 1);
        }
        if (isset($params['city_id']) and $params['city_id'] != 0) {
            $where_array[] = 're_data.city_id = ' . $params['city_id'];
            $QB->addCondition(DB_PREFIX . '_data.city_id', '=', $params['city_id']);
        }
        if (isset($params['district_id']) and $params['district_id'] != 0) {
            $where_array[] = 're_data.district_id = ' . $params['district_id'];
            $QB->addCondition(DB_PREFIX . '_data.district_id', '=', $params['district_id']);
        } else {
            unset($params['district_id']);
        }
        if (isset($params['metro_id']) and $params['metro_id'] != 0) {
            $where_array[] = 're_data.metro_id = ' . $params['metro_id'];
            $QB->addCondition(DB_PREFIX . '_data.metro_id', '=', $params['metro_id']);
        } else {
            unset($params['metro_id']);
        }
        if (isset($params['street_id']) and $params['street_id'] != 0) {
            $where_array[] = 're_data.street_id = ' . $params['street_id'];
            $QB->addCondition(DB_PREFIX . '_data.street_id', '=', $params['street_id']);
        } else {
            unset($params['street_id']);
        }
        ////////////реализовать обработку OR
        if (isset($params['srch_phone']) and $params['srch_phone'] !== NULL) {
            $phone = preg_replace('/[^\d]/', '', $params['srch_phone']);
            $sub_where = array();
            if ($this->getConfigValue('allow_additional_mobile_number')) {
                $sub_where[] = '(re_data.ad_mobile_phone LIKE \'%' . $phone . '%\')';
                $QB->addCondition(DB_PREFIX . '_data.ad_mobile_phone', 'like', '%' . $phone . '%');
            }
            if ($this->getConfigValue('allow_additional_stationary_number')) {
                $sub_where[] = '(re_data.ad_stacionary_phone LIKE \'%' . $phone . '%\')';
                $QB->addCondition(DB_PREFIX . '_data.ad_stacionary_phone', 'like', '%' . $phone . '%');
            }
            $sub_where[] = '(re_data.phone LIKE \'%' . $phone . '%\')';
            $where_array[] = '(' . implode(' OR ', $sub_where) . ')';
        }
        ////////////реализовать обработку OR
        if (isset($params['srch_word']) and $params['srch_word'] !== NULL) {
            $sub_where = array();
            $word = htmlspecialchars($params['srch_word']);
            $sub_where[] = '(re_data.text LIKE \'%' . $word . '%\')';
            /* $sub_where[] = '(re_data.more1 LIKE \'%'.$word.'%\')';
              $sub_where[] = '(re_data.more2 LIKE \'%'.$word.'%\')';
              $sub_where[] = '(re_data.more3 LIKE \'%'.$word.'%\')'; */
            $where_array[] = '(' . implode(' OR ', $sub_where) . ')';
        }

        if (isset($params['room_count'])) {
            if (is_array($params['room_count']) && count($params['room_count']) > 0) {
                $sub_where = array();
                foreach ($params['room_count'] as $rq) {
                    if ($rq == 4) {
                        $sub_where[] = 'room_count>3';
                    } elseif (0 != (int) $rq) {
                        $sub_where[] = 'room_count=' . (int) $rq;
                    }
                }
                if (count($sub_where) > 0) {
                    $where_array[] = '(' . implode(' OR ', $sub_where) . ')';
                }
            } else {
                unset($params['room_count']);
            }
        }

        if ($params['srch_date_from'] != 0 && $params['srch_date_to'] != 0) {
            $where_array[] = "((re_data.date_added>='" . $params['srch_date_from'] . "') AND (re_data.date_added<='" . $params['srch_date_to'] . "'))";
            $QB->addCondition(DB_PREFIX . '_data.date_added', '>=', $params['srch_date_from']);
            $QB->addCondition(DB_PREFIX . '_data.date_added', '<=', $params['srch_date_to']);
        } elseif ($params['srch_date_from'] != 0) {
            $where_array[] = "(re_data.date_added>='" . $params['srch_date_from'] . "')";
            $QB->addCondition(DB_PREFIX . '_data.date_added', '>=', $params['srch_date_from']);
        } elseif ($params['srch_date_to'] != 0) {
            $where_array[] = "(re_data.date_added<='" . $params['srch_date_to'] . "')";
            $QB->addCondition(DB_PREFIX . '_data.date_added', '<=', $params['srch_date_to']);
        }

        if ($params['floor_min'] != 0 && $params['floor_max'] != 0) {
            $where_array[] = "(re_data.floor BETWEEN " . $params['floor_min'] . " AND " . $params['floor_max'] . ")";
            $QB->addCondition(DB_PREFIX . '_data.floor', '>=', $params['floor_min']);
            $QB->addCondition(DB_PREFIX . '_data.floor', '<=', $params['floor_max']);
        } elseif ($params['floor_min'] != 0) {
            $where_array[] = "(re_data.floor>=" . $params['floor_min'] . ")";
            $QB->addCondition(DB_PREFIX . '_data.floor', '>=', $params['floor_min']);
        } elseif ($params['floor_max'] != 0) {
            $where_array[] = "(re_data.floor<=" . $params['floor_max'] . ")";
            $QB->addCondition(DB_PREFIX . '_data.floor', '<=', $params['floor_max']);
        }

        if ($params['floor_count_min'] != 0 && $params['floor_count_max'] != 0) {
            $where_array[] = "(re_data.floor_count BETWEEN " . $params['floor_count_min'] . " AND " . $params['floor_count_max'] . ")";
            $QB->addCondition(DB_PREFIX . '_data.floor_count', '>=', $params['floor_count_min']);
            $QB->addCondition(DB_PREFIX . '_data.floor_count', '<=', $params['floor_count_max']);
        } elseif ($params['floor_count_min'] != 0) {
            $where_array[] = "(re_data.floor_count>=" . $params['floor_count_min'] . ")";
            $QB->addCondition(DB_PREFIX . '_data.floor_count', '>=', $params['floor_count_min']);
        } elseif ($params['floor_count_max'] != 0) {
            $where_array[] = "(re_data.floor_count<=" . $params['floor_count_max'] . ")";
            $QB->addCondition(DB_PREFIX . '_data.floor_count', '<=', $params['floor_count_max']);
        }

        if ($params['square_min'] != 0 && $params['square_max'] != 0) {
            $where_array[] = "(re_data.square_all BETWEEN " . $params['square_min'] . " AND " . $params['square_max'] . ")";
        } elseif ($params['square_min'] != 0) {
            $where_array[] = "(re_data.square_all>=" . $params['square_min'] . ")";
        } elseif ($params['square_max'] != 0) {
            $where_array[] = "(re_data.square_all<=" . $params['square_max'] . ")";
        }

        if ($params['is_phone'] == 1) {
            $where_array[] = "(re_data.is_telephone=1)";
        } else {
            unset($params['is_phone']);
        }

        if ($params['is_internet'] == 1) {
            $where_array[] = "(re_data.is_internet=1)";
        } else {
            unset($params['is_internet']);
        }

        if ($params['is_furniture'] == 1) {
            $where_array[] = "(re_data.furniture=1)";
        } else {
            unset($params['is_furniture']);
        }

        if ($params['owner'] == 1) {
            $where_array[] = "(re_data.whoyuaare=1)";
        } else {
            unset($params['is_furniture']);
        }

        if ($params['has_photo'] == 1) {
            $where_array[] = '((SELECT COUNT(*) FROM ' . DB_PREFIX . '_data_image WHERE id=' . DB_PREFIX . '_data.id)>0)';
        } else {
            unset($params['has_photo']);
        }

        if ($params['infra_greenzone'] == 1) {
            $where_array[] = "(re_data.infra_greenzone=1)";
        } else {
            unset($params['infra_greenzone']);
        }

        if ($params['infra_sea'] == 1) {
            $where_array[] = "(re_data.infra_sea=1)";
        } else {
            unset($params['infra_sea']);
        }

        if ($params['infra_sport'] == 1) {
            $where_array[] = "(re_data.infra_sport=1)";
        } else {
            unset($params['infra_sport']);
        }

        if ($params['infra_clinic'] == 1) {
            $where_array[] = "(re_data.infra_clinic=1)";
        } else {
            unset($params['infra_clinic']);
        }

        if ($params['infra_terminal'] == 1) {
            $where_array[] = "(re_data.infra_terminal=1)";
        } else {
            unset($params['infra_terminal']);
        }

        if ($params['infra_airport'] == 1) {
            $where_array[] = "(re_data.infra_airport=1)";
        } else {
            unset($params['infra_airport']);
        }

        if ($params['infra_bank'] == 1) {
            $where_array[] = "(re_data.infra_bank=1)";
        } else {
            unset($params['infra_bank']);
        }

        if ($params['infra_restaurant'] == 1) {
            $where_array[] = "(re_data.infra_restaurant=1)";
        } else {
            unset($params['infra_restaurant']);
        }

        if (isset($params['object_state']) && is_array($params['object_state']) && count($params['object_state']) > 0) {
            $where_array[] = "(re_data.object_state IN (" . implode(',', $params['object_state']) . "))";
        } else {
            unset($params['object_state']);
        }

        if (isset($params['object_type']) && is_array($params['object_type']) && count($params['object_type']) > 0) {
            $where_array[] = "(re_data.object_type IN (" . implode(',', $params['object_type']) . "))";
        } else {
            unset($params['object_type']);
        }

        if (isset($params['object_destination']) && is_array($params['object_destination']) && count($params['object_destination']) > 0) {
            $where_array[] = "(re_data.object_destination IN (" . implode(',', $params['object_destination']) . "))";
        } else {
            unset($params['object_destination']);
        }

        if (isset($params['aim']) && is_array($params['aim']) && count($params['aim']) > 0) {
            $where_array[] = "(re_data.aim IN (" . implode(',', $params['aim']) . "))";
        } else {
            unset($params['aim']);
        }

        if ($params['has_geo'] == 1) {
            $where_array[] = '(' . DB_PREFIX . '_data.geo_lat IS NOT NULL AND ' . DB_PREFIX . '_data.geo_lng IS NOT NULL)';
        }


        /*
          if ($_SERVER['REQUEST_URI'] == '/')
          $order = "re_data.id desc";
          else
          $order = "re_data.date_added desc";
         */
        if ($params['admin'] != 1) {
            $where_array[] = 're_data.active=1';
        } elseif ($params['active'] == 1) {
            $where_array[] = 're_data.active=1';
        } elseif ($params['active'] == 'notactive') {
            $where_array[] = 're_data.active=0';
        }

        if ($this->getConfigValue('apps.company.timelimit')) {
            $current_time = time();

            $where_array[] = 're_data.user_id=u.user_id';
            $where_array[] = 'u.company_id=c.company_id';
            $where_array[] = "c.start_date <= $current_time";
            $where_array[] = "c.end_date >= $current_time";
            $add_from_table .= ' , re_user u, re_company c ';
        }

        if ($params['only_img']) {

            $where_array[] = 're_data.id=i.id';
            $add_from_table .= ' , re_data_image i ';
        }


        if ($where_array) {
            $where_statement = " WHERE " . implode(' AND ', $where_array);
        }

        if (isset($params['order'])) {

            if (!isset($params['asc'])) {
                $asc = 'desc';
            }
            if ($params['asc'] == 'asc') {
                $asc = 'asc';
            } elseif ($params['asc'] == 'desc') {
                $asc = 'desc';
            } else {
                $asc = 'desc';
            }
            //
            if ($params['order'] == 'type')
                $order = 'type_sh ';
            elseif ($params['order'] == 'street')
                $order = 're_street.name ';
            elseif ($params['order'] == 'district')
                $order = 're_district.name ';
            elseif ($params['order'] == 'metro')
                $order = 're_metro.name ';
            elseif ($params['order'] == 'city')
                $order = 're_city.name ';
            elseif ($params['order'] == 'date_added')
                $order = 're_data.date_added ';
            elseif ($params['order'] == 'price') {
                $order = 'price ';
            }

            $order .= $asc;
        } else {
            //$order = "re_data.id desc";
            $order = "re_data.date_added DESC, re_data.id DESC";
        }

        if (!isset($params['page']) or $params['page'] == 0) {
            $page = 1;
        } else {
            $page = $params['page'];
        }


        if ($random) {
            $order = ' rand() ';
        }

        if ($this->getConfigValue('currency_enable')) {
            $query = "select count(re_data.id) as total from re_data LEFT JOIN re_currency ON re_data.currency_id=re_currency.currency_id, re_topic $add_from_table $where_statement ";
        } else {
            $query = "select count(re_data.id) as total from re_data, re_topic $add_from_table $where_statement ";
        }

        $query1 = 'SELECT COUNT(' . DB_PREFIX . '_data.id) as total FROM ' . DB_PREFIX . '_data d
				LEFT JOIN ' . DB_PREFIX . '_topic t ON t.id=d.topic_id';

        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if (!$stmt) {
            echo 'ERROR ON SELECT<br>';
        }
        $ar = $DBC->fetch($stmt);
        $total = $ar['total'];
        $this->grid_total = $total;
        global $smarty;
        $smarty->assign('_total_records', $total);

        //echo "total = $total<br>";
        $limit = $this->getConfigValue('per_page');
        $max_page = ceil($total / $limit);
        //echo "max_page = $max_page<br>";
        if ($page > $max_page) {
            $page = 1;
            $params['page'] = 1;
        }

        $start = ($page - 1) * $limit;

        $pager_params = $params;
        //print_r($pager_params);

        unset($params['order']);
        unset($params['asc']);
        unset($params['favorites']);


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();

        //print_r($params);
        if ($params['topic_id'] != '') {
            if (!$params['admin']) {
                if ($this->cityTopicUrlFind($_SERVER['REQUEST_URI'])) {
                    $p = parse_url($_SERVER['REQUEST_URI']);
                    unset($params['city_id']);
                    unset($params['topic_id']);
                    unset($pager_params['city_id']);
                    unset($pager_params['topic_id']);
                    $pageurl = trim($p['path'], '/');
                } elseif ($category_structure['catalog'][$params['topic_id']]['url'] != '' && 1 == $this->getConfigValue('apps.seo.level_enable')) {
                    $pageurl = $category_structure['catalog'][$params['topic_id']]['url'];
                    //unset($pager_params['topic_id']);
                    unset($params['topic_id']);
                } elseif ($category_structure['catalog'][$params['topic_id']]['url'] != '') {
                    //echo 1;
                    $pageurl = $category_structure['catalog'][$params['topic_id']]['url'];
                    unset($pager_params['topic_id']);
                    unset($params['topic_id']);
                } else {
                    if (preg_match('/topic(\d*).html/', $_SERVER['REQUEST_URI'])) {
                        unset($pager_params['topic_id']);
                    }
                    if ($params['topic_id'] != 0) {
                        $pageurl = 'topic' . $params['topic_id'] . '.html';
                        unset($params['topic_id']);
                    } else {
                        $pageurl = '';
                        unset($params['topic_id']);
                        unset($pager_params['topic_id']);
                    }
                    /* $pageurl='topic'.$params['topic_id'].'.html';
                      unset($pager_params['topic_id']);
                      unset($params['topic_id']); */
                }
            } else {
                $pageurl = '';
            }
        } else {
            $pageurl = '';
        }
        $this->template->assert('pager', $this->get_page_links_list($page, $total, $limit, $pager_params));




        foreach ($params as $key => $value) {
            if (is_array($value)) {
                if (count($value) > 0) {
                    foreach ($value as $v) {
                        if ($v != '') {
                            $pairs[] = $key . '[]=' . $v;
                        }
                    }
                }
            } elseif ($value != '') {
                if ($key != 'topic_id') {
                    //echo "key = $key, value = $value<br>";
                    $pairs[] = "$key=$value";
                } elseif ($params['admin']) {
                    $pairs[] = "$key=$value";
                }
            }
        }

        if (is_array($pairs)) {
            $url = $pageurl . '?' . implode('&', $pairs);
        } else {
            $url = $pageurl . '?key=value';
        }
        $this->template->assert('url', $url);


        if ($this->getConfigValue('apps.company.timelimit')) {
            if ($this->getConfigValue('currency_enable')) {
                $query = "select re_currency.code AS currency_code, re_currency.name AS currency_name, ((re_data.price*re_currency.course)/" . $CM->getCourse(CURRENT_CURRENCY) . ") AS price_ue, re_data.*, re_topic.name as type_sh $add_select_value from re_data LEFT JOIN re_currency ON re_data.currency_id=re_currency.currency_id, re_topic $add_from_table $where_statement ORDER BY " . $order . ($params['no_portions'] == 1 ? '' : " LIMIT " . $start . ", " . $limit);
            } else {
                $query = "select re_data.*, re_topic.name as type_sh $add_select_value from re_data, re_topic $add_from_table $where_statement order by " . $order . ($params['no_portions'] == 1 ? '' : " LIMIT " . $start . ", " . $limit);
            }
            //echo $query.'<br>';
        } else {
            if ($this->getConfigValue('currency_enable')) {
                $query = "select re_currency.code AS currency_code, re_currency.name AS currency_name, ((re_data.price*re_currency.course)/" . $CM->getCourse(CURRENT_CURRENCY) . ") AS price_ue, re_data.*, re_topic.name as type_sh $add_select_value from re_data LEFT JOIN re_currency ON re_data.currency_id=re_currency.currency_id, re_topic $add_from_table $where_statement ORDER BY " . $order . ($params['no_portions'] == 1 ? '' : " LIMIT " . $start . ", " . $limit);
            } else {
                $query = "select re_data.*, re_topic.name as type_sh $add_select_value from re_data, re_topic $add_from_table $where_statement ORDER BY " . $order . ($params['no_portions'] == 1 ? '' : " LIMIT " . $start . ", " . $limit);
            }
        }
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        if (!$stmt) {
            echo 'ERROR ON SELECT<br>';
        }

        $ra = array();
        $i = 0;
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/company/company.xml')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
            $Account = new Account;
        }

        while ($ar = $DBC->fetch($stmt)) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/company/company.xml')) {
                $company_profile = $Account->get_company_profile($ar['user_id']);
                $ar['company'] = $company_profile['name']['value'];
            }
            $ra[$i] = $ar;
            $i++;
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $params = array();
        if ($this->getConfigValue('apps.mapviewer.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/mapviewer/admin/admin.php';
            $Map_Viewer = new mapviewer_admin();
            $coords_info = $Map_Viewer->loadMVData();
        } else {
            $coords_info = array();
        }


        foreach ($ra as $item_id => $item_array) {
            if ($item_array['country_id'] > 0) {
                $ra[$item_id]['country'] = $data_model->get_string_value_by_id('country', 'country_id', 'name', $item_array['country_id'], true);
            }
            if ($item_array['region_id'] > 0) {
                $ra[$item_id]['region'] = $data_model->get_string_value_by_id('region', 'region_id', 'name', $item_array['region_id'], true);
            }
            if ($item_array['district_id'] > 0) {
                $ra[$item_id]['district'] = $data_model->get_string_value_by_id('district', 'id', 'name', $item_array['district_id'], true);
            }
            if ($item_array['street_id'] > 0) {
                $ra[$item_id]['street'] = $data_model->get_string_value_by_id('street', 'street_id', 'name', $item_array['street_id'], true);
            }
            if ($item_array['city_id'] > 0) {
                $ra[$item_id]['city'] = $data_model->get_string_value_by_id('city', 'city_id', 'name', $item_array['city_id'], true);
            }
            if ($item_array['metro_id'] > 0) {
                $ra[$item_id]['metro'] = $data_model->get_string_value_by_id('metro', 'metro_id', 'name', $item_array['metro_id'], true);
            }
            if ($item_array['user_id'] > 0) {
                $ra[$item_id]['user'] = $data_model->get_string_value_by_id('user', 'user_id', 'fio', $item_array['user_id'], true);
            }
            if ($item_array['currency_id'] > 0) {
                $ra[$item_id]['currency'] = $data_model->get_string_value_by_id('currency', 'currency_id', 'code', $item_array['currency_id'], true);
                //$ra[$item_id]['currency_name'] = $data_model->get_string_value_by_id('currency', 'currency_id', 'name', $item_array['currency_id'], true);
            }

            $params['topic_id'] = $item_array['topic_id'];

            $ra[$item_id]['path'] = $this->get_category_breadcrumbs_string($params, $category_structure);
            $ra[$item_id]['date'] = date('d.m', strtotime($ra[$item_id]['date_added']));


            $image_array = $data_model->get_image_array('data', 'data', 'id', $item_array['id'], 1);
            if (count($image_array) > 0) {
                $ra[$item_id]['img'] = $image_array;
            }


            if (1 == $this->getConfigValue('apps.seo.level_enable')) {

                if ($category_structure['catalog'][$ra[$item_id]['topic_id']]['url'] != '') {
                    $ra[$item_id]['parent_category_url'] = $category_structure['catalog'][$ra[$item_id]['topic_id']]['url'] . '/';
                } else {
                    $ra[$item_id]['parent_category_url'] = '';
                }
            } else {
                $ra[$item_id]['parent_category_url'] = '';
            }
            if (1 == $this->getConfigValue('apps.seo.html_prefix_enable')) {
                $ra[$item_id]['href'] = SITEBILL_MAIN_URL . '/' . $ra[$item_id]['parent_category_url'] . 'realty' . $ra[$item_id]['id'] . '.html';
            } else {
                $ra[$item_id]['href'] = SITEBILL_MAIN_URL . '/' . $ra[$item_id]['parent_category_url'] . 'realty' . $ra[$item_id]['id'];
            }

            if (isset($coords_info[$item_array['id']])) {
                $ra[$item_id]['mvdata'] = $coords_info[$item_array['id']];
            }


            /*
              if($AP!==NULL){
              $phones=array();
              $phones[]=$ra[$item_id]['phone'];
              if($this->getConfigValue('allow_additional_mobile_number')==1 && $ra[$item_id]['ad_mobile_phone']!=''){
              $phones[]=$ra[$item_id]['ad_mobile_phone'];
              }
              if($this->getConfigValue('allow_additional_stationary_number')==1 && $ra[$item_id]['ad_stacionary_phone']!=''){
              $phones[]=$ra[$item_id]['ad_stacionary_phone'];
              }
              $phone_check=$AP->checkCoincidence($phones,$ra[$item_id]['id']);
              //print_r($phone_check);
              $ra[$item_id]['check_result']=$phone_check;
              }
             */
        }
        //echo '<pre>';
        //print_r($ra);
        //echo '</pre>';

        echo '<br /><br />' . $QB->build() . '<br /><br />';
        return $ra;
    }

    /**
     * Get sales grid
     * @param array $adv res
     * @return string
     */
    function get_sales_grid($adv) {
        global $topic_id;

        if ($this->getConfigValue('theme') != 'estate' and ! file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/realty_grid.tpl')) {
            $this->template->assign('main_file_tpl', '../estate/realty_grid.tpl');
        } else {

            $this->template->assign('main_file_tpl', 'realty_grid.tpl');
        }

        if (isset($_REQUEST['REST_API']) && $_REQUEST['REST_API'] == 1) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.static_data.php')) {
                $static_data = Static_Data::getInstance();
                $static_data::set_data($adv);
                return;
            }
        }

        $this->template->assign('grid_items', $adv);


        return true;
    }

    function createMapListing($ra) {
        global $smarty;
        $clustered_objects = array();
        foreach ($ra as $k => $d) {
            if (isset($d['geo_lat']) && isset($d['geo_lng']) && $d['geo_lat'] != '' && $d['geo_lng'] != '') {
                $coords_string = $d['geo_lat'] . '_' . $d['geo_lng'];
                $clustered_objects[$coords_string][] = $d;
            }
        }


        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/mapobjectslisting.tpl')) {
            $template = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/mapobjectslisting.tpl';
        } else {
            $template = SITEBILL_DOCUMENT_ROOT . '/apps/system/template/mapobjectslisting.tpl';
        }
        $smarty->assign('mapobjects_clusters', $clustered_objects);
        return $html = $smarty->fetch($template);
    }

    protected function prepareSortOrder($params, $random = false, $premium = false) {

        $order = '';
        $asc = '';

        $default_sorts = $this->getConfigValue('apps.realty.sorts');
        $sorts = array();
        
        if ($default_sorts != '') {
            switch ($default_sorts) {
                case 'priceup' : {
                        $sorts[] = DB_PREFIX . '_data.price_ue ASC';
                        break;
                    }
                case 'pricedown' : {
                        $sorts[] = DB_PREFIX . '_data.price_ue DESC';
                        break;
                    }
                default : {
                        $matches = array();
                        preg_match_all('/([a-z0-9_]+)\|(asc|desc)[;]?/i', $default_sorts, $matches);

                        if (count($matches[0]) > 0) {
                            foreach ($matches[1] as $k => $fkey) {
                                if ($matches[2][$k] == 'asc' || $matches[2][$k] == 'desc') {
                                    switch ($fkey) {
                                        case 'id' : {
                                                $sorts[] = DB_PREFIX . '_data.id ' . $matches[2][$k];
                                                break;
                                            }
                                        case 'type' : {
                                                $sorts[] = 'type_sh ' . $matches[2][$k];
                                                break;
                                            }
                                        case 'street' : {
                                                $sorts[] = 'street ' . $matches[2][$k];
                                                break;
                                            }
                                        case 'square_all' : {
                                                $sorts[] = DB_PREFIX . '_data.square_all*1 ' . $matches[2][$k];
                                                break;
                                            }
                                        case 'floor' : {
                                                $sorts[] = DB_PREFIX . '_data.floor*1 ' . $matches[2][$k];
                                                break;
                                            }
                                        case 'district' : {
                                                $sorts[] = 'district ' . $matches[2][$k];
                                                break;
                                            }
                                        case 'metro' : {
                                                $sorts[] = 'metro ' . $matches[2][$k];
                                                break;
                                            }
                                        case 'city' : {
                                                $sorts[] = 'city ' . $matches[2][$k];
                                                break;
                                            }
                                        case 'date_added' : {
                                                $sorts[] = DB_PREFIX . '_data.date_added ' . $matches[2][$k];
                                                break;
                                            }
                                        case 'date' : {
                                                $field = trim($this->getConfigValue('apps.realty.updated_at_field'));
                                                if ($field == '') {
                                                    $field = 'date_added';
                                                }
                                                $sorts[] = DB_PREFIX . '_data.`' . $field . '` ' . $matches[2][$k];
                                                break;
                                            }
                                        case 'price' : {
                                                $sorts[] = 'price_ue ' . $matches[2][$k];
                                                break;
                                            }
                                        default : {
                                            $sorts[]=DB_PREFIX.'_data.`'.$fkey.'` '.$matches[2][$k];
                                        }
                                    }
                                }
                            }
                        }
                    }
            }
        }

        //print_r($sorts);

        if (!empty($sorts)) {
            //array_unshift($sorts, '_prem_sort DESC');
            $default_sorts = implode(', ', $sorts);
        } else {
            $field = trim($this->getConfigValue('apps.realty.updated_at_field'));
            if ($field == '') {
                $field = 'date_added';
            }
			$default_sorts = DB_PREFIX . '_data.`' . $field . '` DESC, ' . DB_PREFIX . '_data.id DESC';
            /*if($premium){
                $default_sorts = '_prem_sort DESC, '.DB_PREFIX . '_data.`' . $field . '` DESC, ' . DB_PREFIX . '_data.id DESC';
            }*/
            
        }

        if ($random) {
            $order = ' rand() ';
        } elseif (isset($params['order'])) {

            if (!isset($params['asc'])) {
                $asc = 'desc';
            }
            if ($params['asc'] == 'asc') {
                $asc = 'asc';
            } elseif ($params['asc'] == 'desc') {
                $asc = 'desc';
            } else {
                $asc = 'desc';
            }

            switch ($params['order']) {
                case 'id' : {
                        $order = 'id ' . $asc;
                        break;
                    }
                case 'type' : {
                        $order = 'type_sh ' . $asc;
                        //$order=DB_PREFIX.'_data.topic_id '.$asc;
                        break;
                    }
                case 'street' : {
                        if (isset($this->grid_item_data_model['street_id'])) {
                            $order = 'street ' . $asc;
                        } else {
                            $order = $default_sorts;
                        }

                        break;
                    }
                case 'square_all' : {
                        $order = DB_PREFIX . '_data.square_all*1 ' . $asc;
                        break;
                    }
                case 'floor' : {
                        $order = DB_PREFIX . '_data.floor*1 ' . $asc;
                        break;
                    }
                case 'district' : {
                        $order = 'district ' . $asc;
                        break;
                    }
                case 'metro' : {
                        $order = 'metro ' . $asc;
                        break;
                    }
                case 'city' : {
                        $order = 'city ' . $asc;
                        break;
                    }
                case 'date_added' : {
                        $order = DB_PREFIX . '_data.date_added ' . $asc;
                        break;
                    }
                case 'price' : {
                        $order = 'price_ue ' . $asc;
                        break;
                    }
                case 'popular' : {
                        $order = DB_PREFIX . '_data.view_count ' . $asc;
                        break;
                    }
                case 'priceup' : {
                        $order = 'price_ue ASC';
                        break;
                    }
                case 'pricedown' : {
                        $order = 'price_ue DESC';
                        break;
                    }
                case 'popularup' : {
                        $order = DB_PREFIX . '_data.view_count ASC';
                        break;
                    }
                case 'populardown' : {
                        $order = DB_PREFIX . '_data.view_count DESC';
                        break;
                    }
                case 'dateup' : {
                        $order = DB_PREFIX . '_data.date_added ASC';
                        break;
                    }
                case 'datedown' : {
                        $order = DB_PREFIX . '_data.date_added DESC';
                        break;
                    }
                default : {
                        if (isset($params['_sortmodel']) && $params['_sortmodel'] == 1) {
                            $order = DB_PREFIX . '_data.`' . $params['order'] . '` ' . $asc;
                        } else {
                            $order = $default_sorts;
                        }
                    }
            }
            //$order='_prem_sort DESC, '.$order;

            //
            /* if     ( $params['order'] == 'type' ) $order = 'type_sh ';
              elseif ( $params['order'] == 'street' ) $order = 'street ';
              elseif ( $params['order'] == 'square_all' ) $order = 're_data.square_all*1 ';
              elseif ( $params['order'] == 'floor' ) $order = 're_data.floor*1 ';
              elseif ( $params['order'] == 'district' ) $order = 'district ';
              elseif ( $params['order'] == 'metro' ) $order = 'metro ';
              elseif ( $params['order'] == 'city' ) $order = 'city ';
              elseif ( $params['order'] == 'date_added' ) $order = 're_data.date_added ';
              elseif ( $params['order'] == 'id' ) $order = 're_data.id ';
              elseif ( $params['order'] == 'price' ){
              $order = 'price_ue ';
              }else{
              $order = "re_data.date_added ";
              }

              $order .= $asc; */
        } else {
            if ($premium) {
                if ((int) $params['page'] == 1 || (int) $params['page'] == 0) {
                    $order = ' ' . DB_PREFIX . '_data.premium_status_end ASC';
                } else {
                    $order = ' ' . DB_PREFIX . '_data.premium_status_end ASC';
                    //$order = " rand() ";
                }
            } else {
                $order = $default_sorts;
            }
        }

        return $order;
    }

    protected function generateGridGeoDataOld($ra) {
        $grid_geodata = array();
        foreach ($ra as $item_id => $item_array) {
            if (isset($item_array['geo_lat']) && isset($item_array['geo_lng']) && $item_array['geo_lat'] != '' && $item_array['geo_lng'] != '') {
                $grid_geodata[] = array(
                    'lat' => $item_array['geo_lat'],
                    'lng' => $item_array['geo_lng'],
                    'id' => $item_array['id']
                );
            }
        }
        return $grid_geodata;
    }

    function transformGridData($ra, $_collect_user_info = false) {
        
        $uselangs = false;
        if (1 === intval($this->getConfigValue('apps.language.use_langs'))){
            $uselangs = true;
            $postfix = $this->getLangPostfix($this->getCurrentLang());
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();

        $chains = $Structure_Manager->createCatalogChains();


        $params = array();

        $_model = $data_model->get_kvartira_model(false, true);

        $sbf = array();

        $fields = trim($this->getConfigValue('core.listing.select_query_fields'));
        if (!empty($fields)) {
            $fields_parts = explode("\n", $fields);
            foreach ($fields_parts as $fp) {
                list($f, $n) = explode('=', trim($fp));
                if (trim($f) != '') {
                    $sbf[trim($f)] = trim($f);
                    if (trim($n) != '') {
                        $sbf[trim($f)] = trim($n);
                    }
                }
            }
        }


        $grid_geodata = array();

        $billing = false;
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable') == 1) {
            $billing = true;
        }


        foreach ($ra as $item_id => $item_array) {



            if (isset($item_array['geo_lat']) && isset($item_array['geo_lng']) && $item_array['geo_lat'] != '' && $item_array['geo_lng'] != '') {
                $grid_geodata[] = array(
                    'lat' => $item_array['geo_lat'],
                    'lng' => $item_array['geo_lng'],
                    'id' => $item_array['id']
                );
            }
            if ($item_array['country_id'] > 0) {
                $parameters = $_model['data']['country_id']['parameters'];
                $fname = 'name';
                if ($uselangs && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                    $fname .= $postfix;
                }
                $ra[$item_id]['country'] = $data_model->get_string_value_by_id('country', 'country_id', $fname, $item_array['country_id'], true);
            }

            if ($item_array['region_id'] > 0) {
                $parameters = $_model['data']['region_id']['parameters'];
                $fname = 'name';
                if ($uselangs && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                    $fname .= $postfix;
                }
                $ra[$item_id]['region'] = $data_model->get_string_value_by_id('region', 'region_id', $fname, $item_array['region_id'], true);
            }
            if ($item_array['district_id'] > 0) {
                $parameters = $_model['data']['district_id']['parameters'];
                $fname = 'name';
                if ($uselangs && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                    $fname .= $postfix;
                }
                $ra[$item_id]['district'] = $data_model->get_string_value_by_id('district', 'id', $fname, $item_array['district_id'], true);
            }
            if ($item_array['street_id'] > 0) {
                $parameters = $_model['data']['street_id']['parameters'];
                $fname = 'name';
                if ($uselangs && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                    $fname .= $postfix;
                }
                $ra[$item_id]['street'] = $data_model->get_string_value_by_id('street', 'street_id', $fname, $item_array['street_id'], true);
            }
            if ($item_array['city_id'] > 0) {

                $parameters = $_model['data']['city_id']['parameters'];

                $fname = 'name';
                if ($uselangs && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                    $fname .= $postfix;
                }
                $ra[$item_id]['city'] = $data_model->get_string_value_by_id('city', 'city_id', $fname, $item_array['city_id'], true);
            }
            if ($item_array['metro_id'] > 0) {
                $parameters = $_model['data']['metro_id']['parameters'];
                $fname = 'name';
                if ($uselangs && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                    $fname .= $postfix;
                }
                $ra[$item_id]['metro'] = $data_model->get_string_value_by_id('metro', 'metro_id', $fname, $item_array['metro_id'], true);
            }
            if ($item_array['user_id'] > 0) {
                if ($_collect_user_info) {
                    $fields_str = trim($this->getConfigValue('core.listing.add_user_info_fields'));
                    if ($fields_str == '') {
                        $fields = '`phone`, `login`, `fio`';
                        $fields = array();
                    } else {
                        $fields = explode(',', $fields_str);
                        $fields = array_map(function($it) {
                            if (trim($it) !== '') {
                                return trim($it);
                            }
                        }, $fields);
                    }
                    if (empty($fields)) {
                        $fields = array('phone', 'login', 'fio');
                    }
                    $DBC = DBC::getInstance();
                    if(!isset($collected[$item_array['user_id']])){
                        $stmt = $DBC->query('SELECT `' . implode('`,`', $fields) . '` FROM ' . DB_PREFIX . '_user WHERE user_id=? LIMIT 1', array($item_array['user_id']));
                        if ($stmt) {
                            $ar = $DBC->fetch($stmt);
                            $collected[$item_array['user_id']] = $ar;
                        }
                    }
                    $ra[$item_id]['_user_info']=$collected[$item_array['user_id']];
                }



                $ra[$item_id]['user'] = $data_model->get_string_value_by_id('user', 'user_id', 'fio', $item_array['user_id'], true);
                if ($ra[$item_id]['user'] == '') {
                    $ra[$item_id]['user'] = $data_model->get_string_value_by_id('user', 'user_id', 'login', $item_array['user_id'], true);
                }
            }
            if ($item_array['currency_id'] > 0) {
                $ra[$item_id]['currency'] = $data_model->get_string_value_by_id('currency', 'currency_id', 'code', $item_array['currency_id'], true);
            }

            foreach ($_model['data'] as $k => $v) {
                if ($v['type'] == 'select_box') {
                    $ra[$item_id]['_' . $k . '_'] = $ra[$item_id][$k];
                    if (isset($_model['data'][$k]['select_data'][$ra[$item_id][$k]])) {
                        $ra[$item_id][$k] = $_model['data'][$k]['select_data'][$ra[$item_id][$k]];
                    } else {
                        $ra[$item_id][$k] = '';
                    }
                }
            }



            if (!empty($sbf)) {
            	//print_r($sbf);
            	$tmp_cache=array();
                foreach ($sbf as $kn => $vn) {
                    if (isset($_model['data'][$kn]) && $_model['data'][$kn]['type'] == 'select_by_query') {
                        if ($item_array[$kn] > 0) {
                        	
                            if ($kn == $vn) {
                                $vn = '_' . $kn . '_';
                            }
                            $parameters = $_model['data'][$kn]['parameters'];
                            $fname = $_model['data'][$kn]['value_name'];
                            if(isset($tmp_cache[$kn][$item_array[$kn]])){
                            	$txt=$tmp_cache[$kn][$item_array[$kn]];
                            }else{
                            	if ($uselangs && (!isset($parameters['no_ml']) || 0 === intval($parameters['no_ml']))) {
                            		$fname .= $postfix;
                            	}
                            	$txt = $data_model->get_string_value_by_id($_model['data'][$kn]['primary_key_table'], $_model['data'][$kn]['primary_key_name'], $fname, $item_array[$kn], true);
                            }
                            
                            $ra[$item_id][$vn]=$txt;
                        } else {
                            $ra[$item_id][$vn] = '';
                        }
                    }
                }
            }




            if ($uselangs) {
                foreach ($_model['data'] as $k => $v) {
                    if (($v['type'] == 'safe_string' || $v['type'] == 'textarea' || $v['type'] == 'textarea_editor') && isset($item_array[$k . $postfix]) && $item_array[$k . $postfix] != '') {
                        $ra[$item_id][$k] = $item_array[$k . $postfix];
                    }
                }
            }


            //$select_what[]=DB_PREFIX.'_topic.name AS type_sh';

            $params['topic_id'] = $item_array['topic_id'];

            $ra[$item_id]['path'] = $this->get_category_breadcrumbs_string($params, $category_structure);
            $ra[$item_id]['_chain'] = null;
            if (isset($chains['ar'][$item_array['topic_id']])) {
                $ra[$item_id]['_chain'] = $chains['ar'][$item_array['topic_id']];
            }



            $ra[$item_id]['date'] = date('d.m', strtotime($ra[$item_id]['date_added']));
            $ra[$item_id]['_posted_days'] = ceil((time()-strtotime($ra[$item_id]['date_added']))/86400);
            $ra[$item_id]['datetime'] = date('d.m H:i', strtotime($ra[$item_id]['date_added']));
            $ra[$item_id]['text'] = strip_tags($ra[$item_id]['text']);

            /*
              $image_array = $data_model->get_image_array ( 'data', 'data', 'id', $item_array['id'], 1 );
              if ( count($image_array) > 0 ) {
              $ra[$item_id]['img'] = $image_array;
              }

             */
            $ra[$item_id]['topic_info'] = null;
            if (isset($category_structure['catalog'][$ra[$item_id]['topic_id']])) {
                $ra[$item_id]['topic_info'] = $category_structure['catalog'][$ra[$item_id]['topic_id']];
            }


            if ($uselangs) {
                $fname = 'name'.$postfix;
                $ra[$item_id]['topic_info'][$fname] = $ra[$item_id]['topic_info'][$fname];
            }
            $ra[$item_id]['type_sh'] = $ra[$item_id]['topic_info']['name'];

            $ra[$item_id]['href'] = $this->getRealtyHREF($ra[$item_id]['id'], false, array('topic_id' => $ra[$item_id]['topic_id'], 'alias' => $ra[$item_id]['translit_alias']));

            if ($billing) {
                if (isset($item_array['premium_status_end']) && isset($_model['data']['premium_status_end']) && $ra[$item_id]['premium_status_end'] > time()) {
                    $ra[$item_id]['premium_status'] = 1;
                }
                if (isset($item_array['vip_status_end']) && isset($_model['data']['vip_status_end']) && $ra[$item_id]['vip_status_end'] > time()) {
                    $ra[$item_id]['vip_status'] = 1;
                }
                if (isset($item_array['bold_status_end']) && isset($_model['data']['bold_status_end']) && $ra[$item_id]['bold_status_end'] > time()) {
                    $ra[$item_id]['bold_status'] = 1;
                }
            }
        }

        foreach ($ra as $item) {
            $_ids[] = $item['id'];
        }

        $hasMultipleFields = array();
        $hasUploadify = false;
        $hasUploads = false;
        $uploads_element = '';
        foreach ($_model['data'] as $k => $v) {
            if (isset($v['type']) && $v['type'] == 'uploadify_image') {
                $hasUploadify = true;
            } elseif (isset($v['type']) && $v['type'] == 'uploads') {
                $hasUploads = true;
                $uploads_element = $v['name'];
                break;
            }
        }

        foreach ($_model['data'] as $k => $v) {
            if (isset($v['type']) && $v['type'] == 'select_by_query_multi') {
                $hasMultipleFields[] = $k;
            }
        }



        if (!empty($hasMultipleFields)) {
            $elements_keys = array();
            $data = array();
            $DBC = DBC::getInstance();
            $query = 'SELECT `primary_id`, `field_name`, `field_value` FROM ' . DB_PREFIX . '_multiple_field WHERE `table_name`=? AND `field_name` IN (' . implode(',', array_fill(0, count($hasMultipleFields), '?')) . ') AND `primary_id` IN (' . implode(',', array_fill(0, count($_ids), '?')) . ')';
            $query_params[] = 'data';
            //$query_params=$_ids;
            $query_params = array_merge($query_params, $hasMultipleFields, $_ids);
            $stmt = $DBC->query($query, $query_params);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $elements_keys[$ar['field_name']][$ar['field_value']] = '';
                    $data[$ar['primary_id']][$ar['field_name']][] = $ar['field_value'];
                }
            }
            //print_r($elements_keys);
            if (!empty($elements_keys)) {
                foreach ($elements_keys as $key => $ikeys) {
                    $name = $_model['data'][$key]['value_name'];
                    $pk = $_model['data'][$key]['primary_key_name'];
                    $query = 'SELECT `' . $pk . '`, `' . $name . '` FROM ' . DB_PREFIX . '_' . $_model['data'][$key]['primary_key_table'] . ' WHERE `' . $_model['data'][$key]['primary_key_name'] . '` IN (' . implode(',', array_keys($ikeys)) . ')';
                    $stmt = $DBC->query($query);
                    echo $DBC->getLastError();
                    if ($stmt) {
                        while ($ar = $DBC->fetch($stmt)) {
                            $elements_keys[$key][$ar[$pk]] = $ar[$name];
                        }
                    }
                }

                foreach ($ra as $item_id => $item_array) {
                    if (isset($data[$item_array['id']])) {
                        foreach ($data[$item_array['id']] as $ek => $v) {
                            if(!is_array($ra[$item_id][$ek])){
                                $ra[$item_id][$ek]=array();
                            }
                            $ra[$item_id][$ek][0] = $v;
                            foreach ($v as $_v) {
                                $ra[$item_id][$ek][1][$_v] = $elements_keys[$ek][$_v];
                            }
                        }
                    }
                }
            }
        }

        if ($hasUploadify) {
            $key = 'id';
            if (count($_ids) > 0) {
                $query = 'SELECT li.' . $key . ' , i.* FROM ' . DB_PREFIX . '_data_image li LEFT JOIN ' . IMAGE_TABLE . ' i USING(image_id) WHERE li.' . $key . ' IN (' . implode(', ', $_ids) . ') ORDER BY li.sort_order ASC';
                $DBC = DBC::getInstance();
                $stmt = $DBC->query($query);
                $images = array();
                if ($stmt) {
                    $iurl = $this->storage_dir;
                    while ($ar = $DBC->fetch($stmt)) {
                        $ar['img_preview'] = $iurl . $ar['preview'];
                        $ar['img_normal'] = $iurl . $ar['normal'];
                        $images[$ar[$key]][] = $ar;
                    }
                }
                foreach ($ra as $k => $item) {
                    if (isset($images[$item['id']])) {
                        $ra[$k]['img'] = $images[$item['id']];
                    }
                }
            }
        } elseif ($hasUploads) {
            //try to get uploadify images first
            //$old_uploadify_images = $this->get_uploadify_images($_ids);
            foreach ($ra as $k => $item) {
                //echo 'uploads_element = '.$uploads_element.'<br>';
                //echo '<pre>';
                //print_r($item);
                //echo '</pre>';

                if ($item[$uploads_element] == '') {
                    if (isset($old_uploadify_images[$ra[$k]['id']])) {
                        $ra[$k]['img'] = $old_uploadify_images[$ra[$k]['id']];
                    }
                    
                    if ( isset($item['image_cache']) ) {
                        $ra[$k]['image_cache'] = unserialize($item['image_cache']);
                    }
                    
                    /* else{
                      $ra[$k]['img']='';
                      } */
                } else {
                    $ims = unserialize($item[$uploads_element]);

                    if (is_array($ims) && count($ims) == 0) {
                        unset($ra[$k]['img']);
                        //$ra[$k]['img']='';
                    } else {
                        $ra[$k]['img'] = $ims;
                    }
                }
            }
        }

        if ($hasUploads) {
            foreach ($ra as $e => $item) {
                foreach ($_model['data'] as $k => $v) {
                    if (isset($v['type']) && $v['type'] == 'uploads') {
                        $ra[$e][$k] = unserialize($ra[$e][$k]);
                    } elseif (isset($ra[$e]['image_cache']) && $v['type'] == 'uploads') {
                        $ra[$e]['image_cache'] = unserialize($ra[$e]['image_cache']);
                    }
                }
            }
        }
        

        $destination_elements = array();
        foreach ($_model['data'] as $k => $v) {
            if (isset($v['type']) && $v['type'] == 'destination') {
                $destination_elements[] = $v['name'];
            }
        }



        if (!empty($destination_elements)) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/destination/admin/admin.php';
            $DA = new destination_admin();
            $this->template->assert('destination_info', $DA);
            foreach ($ra as $k => $item) {
                foreach ($destination_elements as $destination_element) {
                    if (0 != (int) $item[$destination_element]) {
                        //print_r($DA->getDestinationObject($item[$destination_element]));
                        $ra[$k]['_destination_info'][$destination_element]['value'] = $DA->getDestinationById($item[$destination_element]);
                        $ra[$k]['_destination_info'][$destination_element]['parts'] = $DA->getDestinationObject($item[$destination_element]);
                    }
                }
            }
        }
        
        //print_r($ra);

        return $ra;
    }
    
    /*function fillFields($item){
        $item['_title'] = $this->fillTitleLine($item);
        $item['_location'] = $this->fillLocationLine($item);
        $item['_pricefull'] = $this->fillPriceFull($item);
        $item['_price'] = $this->fillPrice($item);
        $item['_pricecurrency'] = $this->fillPriceCurrency($item);
        return $item;
    }
    
    function fillTitleLine($item){
        return $item['type_sh'];
    }
    
    function fillLocationLine($item){
        $ar = array();
        return $item['type_sh'];
    }
    
    function fillPriceFull($item){
        return $item['type_sh'];
    }
    
    function fillPrice($item){
        return $item['type_sh'];
    }
    
    function fillPriceCurrency($item){
        return $item['type_sh'];
    }*/

    function get_uploadify_images($_ids) {
        $key = 'id';

        if (count($_ids) > 0) {
            $query = 'SELECT li.' . $key . ' , i.* FROM ' . DB_PREFIX . '_data_image li LEFT JOIN ' . IMAGE_TABLE . ' i USING(image_id) WHERE li.' . $key . ' IN (' . implode(', ', $_ids) . ') ORDER BY li.sort_order ASC';
            $DBC = DBC::getInstance();
            $stmt = $DBC->query($query);
            $images = array();
            if ($stmt) {
                $iurl = $this->storage_dir;
                while ($ar = $DBC->fetch($stmt)) {
                    $ar['img_preview'] = $iurl . $ar['preview'];
                    $ar['img_normal'] = $iurl . $ar['normal'];
                    $images[$ar[$key]][] = $ar;
                }
            }
            return $images;
        }
        return false;
    }

    protected function prepareBreadcrumbs($params, $url = '') {
        //print_r($params);
        //if($params)
        //var_dump(Multilanguage::is_set('LT_BC_HOME_GRID', '_template'));
        if (Multilanguage::is_set('LT_BC_HOME_GRID', '_template')) {
            $ra[] = '<a href="' . SITEBILL_MAIN_URL . '/">' . Multilanguage::_('LT_BC_HOME_GRID', '_template') . '</a>';
        } else {
            $ra[] = '<a href="' . SITEBILL_MAIN_URL . '/">' . Multilanguage::_('L_HOME') . '</a>';
        }

        //
        $rs = implode(' / ', array_reverse($ra));

        if ($url == '') {
            $url = SITEBILL_MAIN_URL;
        }
        $breadcrumbs = '';

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();

        if (1 == 0) {
            $bc_array = array();


            $rs = '';

            if (!isset($params['topic_id']) || is_array($params['topic_id'])) {
                return $rs;
            }

            if ($category_structure['catalog'][$params['topic_id']]['url'] != '') {
                $bc_array[] = array(
                    'href' => rtrim($url, '/') . '/' . $category_structure['catalog'][$params['topic_id']]['url'],
                    'name' => $category_structure['catalog'][$params['topic_id']]['name']
                );
            } else {
                $bc_array[] = array(
                    'href' => rtrim($url, '/') . '/topic' . $params['topic_id'] . '.html',
                    'name' => $category_structure['catalog'][$params['topic_id']]['name']
                );
            }

            $parent_category_id = $category_structure['catalog'][$params['topic_id']]['parent_id'];
            while ($category_structure['catalog'][$parent_category_id]['parent_id'] != 0) {
                if ($j++ > 100) {
                    return;
                }
                if ($category_structure['catalog'][$parent_category_id]['url'] != '') {
                    $bc_array[] = array(
                        'href' => rtrim($url, '/') . '/' . $category_structure['catalog'][$parent_category_id]['url'],
                        'name' => $category_structure['catalog'][$parent_category_id]['name']
                    );
                } else {
                    $bc_array[] = array(
                        'href' => rtrim($url, '/') . '/topic' . $parent_category_id . '.html',
                        'name' => $category_structure['catalog'][$parent_category_id]['name']
                    );
                }
                $parent_category_id = $category_structure['catalog'][$parent_category_id]['parent_id'];
            }
            if ($category_structure['catalog'][$parent_category_id]['name'] != '') {
                if ($category_structure['catalog'][$parent_category_id]['url'] != '') {
                    $bc_array[] = array(
                        'href' => rtrim($url, '/') . '/' . $category_structure['catalog'][$parent_category_id]['url'],
                        'name' => $category_structure['catalog'][$parent_category_id]['name']
                    );
                } else {
                    $bc_array[] = array(
                        'href' => rtrim($url, '/') . '/topic' . $parent_category_id . '.html',
                        'name' => $category_structure['catalog'][$parent_category_id]['name']
                    );
                }
            }

            $bc_array[] = array(
                'href' => SITEBILL_MAIN_URL . '/',
                'name' => Multilanguage::_('L_HOME')
            );
            $bc_array = array_reverse($bc_array);
            print_r($bc_array);
        } else {
            $breadcrumbs = $this->get_category_breadcrumbs($params, $category_structure, SITEBILL_MAIN_URL);
            return $breadcrumbs;
        }
    }

    protected function preparePageLimitParams(&$params, $page, $total, $premium) {

        if ($premium) {
            $limit = (int) $this->getConfigValue('apps.billing.premium_count');
            if ($limit == 0) {
                $limit = 5;
            }
        } else {
            $limit = $this->getConfigValue('per_page');
            if (intval($this->getConfigValue('per_page_admin')) > 0 && isset($params['admin']) && $params['admin'] == 1) {
                $limit = $this->getConfigValue('per_page_admin');
            }

            if (isset($params['vip']) && (int) $params['vip'] == 1) {
                if (isset($params['per_page']) && (int) $params['per_page'] > 0) {
                    $limit = (int) $params['per_page'];
                } else {
                    $limit = $this->getConfigValue('vip_rotator_number');
                }
            } else {
                if (isset($params['page_limit']) && (int) $params['page_limit'] != 0) {
                    $limit = (int) $params['page_limit'];
                }/* else{
                  if(isset($params['admin']) && $params['admin']==1){
                  $limit = 10;
                  }else{
                  $limit = $this->getConfigValue('per_page');
                  }

                  } */
            }
        }

        $max_page = ceil($total / $limit);

        if ($page > $max_page) {
            $page = 1;
            $params['page'] = 1;
        }
        $start = ($page - 1) * $limit;

        return array('start' => $start, 'limit' => $limit, 'max_page' => $max_page);
    }

    protected function prepareRequestParams($params, $premium = false) {



        /* if($this->grid_model===null){
          require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
          $data_model = new Data_Model();
          $this->grid_model=$data_model->get_kvartira_model(false, true);
          } */


        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable') == 1) {
            $_billing_on = true;
        } else {
            $_billing_on = false;
        }



        if (isset($params['currency_id']) && 0 != (int) $params['currency_id'] && 1 == $this->getConfigValue('currency_enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/currency/admin/admin.php';
            $CA = new currency_admin();
            $this->use_currency = true;
            $this->price_koefficient = $CA->getCourse((int) $params['currency_id']);
        } elseif (!isset($params['currency_id']) && 1 == $this->getConfigValue('currency_enable')) {
            $def_currency = intval($this->getConfigValue('apps.currency.default_grid_currency_id'));
            if ($def_currency != 0) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/currency/admin/admin.php';
                $CA = new currency_admin();
                $this->use_currency = true;
                $this->price_koefficient = $CA->getCourse($def_currency);
            } else {
                $this->use_currency = false;
                $this->price_koefficient = 1;
            }
        } else {
            $this->use_currency = false;
            $this->price_koefficient = 1;
        }



        $where_array = array();
        $add_from_table = '';
        $add_select_value = '';
        $select_what = array();
        $left_joins = array();

        $where_array_prepared = array();
        $where_value_prepared = array();

        /* foreach($params as $param=>$pval){
          $method='prepareRequestParam_'.$param;

          if(method_exists($this, $method)){

          $d=$this->$method($pval, $params);
          if(false!==$d){
          if(isset($d['where_array']) && is_array($d['where_array'])){
          $where_array=array_merge($where_array, $d['where_array']);
          }
          if(isset($d['add_from_table']) && is_array($d['add_from_table'])){
          $add_from_table=array_merge($add_from_table, $d['add_from_table']);
          }
          if(isset($d['add_select_value']) && is_array($d['add_select_value'])){
          $add_select_value=array_merge($add_select_value, $d['add_select_value']);
          }
          if(isset($d['params']) && is_array($d['params'])){
          $params=array_merge($params, $d['params']);
          }
          if(isset($d['wa']) && is_array($d['wa'])){
          $where_array_prepared=array_merge($where_array_prepared, $d['wa']);
          }
          if(isset($d['wv']) && is_array($d['wv'])){
          $where_value_prepared=array_merge($where_value_prepared, $d['wv']);
          }
          if(isset($d['select_what']) && is_array($d['select_what'])){
          $select_what=array_merge($select_what, $d['select_what']);
          }
          if(isset($d['left_joins']) && is_array($d['left_joins'])){
          $left_joins=array_merge($left_joins, $d['left_joins']);
          }
          }
          }
          } */

        /*if($premium){
            $select_what[] = 'IF('.DB_PREFIX.'_data.`premium_status_end`>'.time().', 1, 0) AS _prem_sort';
        }*/
        

        if (isset($params['order']) && $params['order'] == 'city') {
            if ($this->getConfigValue('apps.language.use_langs')) {
                $field='name';
                $no_ml = 0;
                if (isset($this->grid_item_data_model['city_id']['parameters']['no_ml'])) {
                    $no_ml = intval($this->grid_item_data_model['city_id']['parameters']['no_ml']);
                }
                if(0 === intval($parameters['no_ml'])){
                    $field .= $this->getLangPostfix($this->getCurrentLang());
                }
                
                $select_what[] = DB_PREFIX . '_city.' . $field . ' as city';
            } else {
                $select_what[] = DB_PREFIX . '_city.name as city';
            }

            $left_joins[] = 'LEFT JOIN ' . DB_PREFIX . '_city ON ' . DB_PREFIX . '_city.city_id=' . DB_PREFIX . '_data.city_id';
        }

        if (isset($params['order']) && $params['order'] == 'district') {
            $select_what[] = DB_PREFIX . '_district.name as district';
            $left_joins[] = 'LEFT JOIN ' . DB_PREFIX . '_district ON ' . DB_PREFIX . '_district.id=' . DB_PREFIX . '_data.district_id';
        }

        if (isset($params['order']) && $params['order'] == 'metro') {
            $select_what[] = DB_PREFIX . '_metro.name as metro';
            $left_joins[] = 'LEFT JOIN ' . DB_PREFIX . '_metro ON ' . DB_PREFIX . '_metro.metro_id=' . DB_PREFIX . '_data.metro_id';
        }

        if (isset($params['order']) && $params['order'] == 'street' && isset($this->grid_item_data_model['street_id'])) {
            $select_what[] = DB_PREFIX . '_street.name as street';
            $left_joins[] = 'LEFT JOIN ' . DB_PREFIX . '_street ON ' . DB_PREFIX . '_street.street_id=' . DB_PREFIX . '_data.street_id';
        }


        if (isset($params['order']) && $params['order'] == 'type' && isset($this->grid_item_data_model['topic_id'])) {
            $select_what[] = DB_PREFIX . '_topic.name AS type_sh';
            $left_joins[] = 'LEFT JOIN ' . DB_PREFIX . '_topic ON ' . DB_PREFIX . '_topic.id=' . DB_PREFIX . '_data.topic_id';
        }




        //Подключать модель и проверять на наличие такого поля
        if (isset($params['srch_export_cian']) && $params['srch_export_cian'] == 1 && isset($this->grid_item_data_model['export_cian'])) {
            $where_array[] = DB_PREFIX . '_data.export_cian=1';
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.export_cian=1)';
        } else {
            unset($params['srch_export_cian']);
        }


        if (isset($params['favorites']) && !empty($params['favorites'])) {
            $favorites_array = $params['favorites'];
            foreach ($favorites_array as $k => $v) {
                if ((int) $v != 0) {
                    $favorites_array[$k] = (int) $v;
                } else {
                    unset($favorites_array[$k]);
                }
            }
            if (count($favorites_array) > 0) {
                $where_array[] = DB_PREFIX . '_data.id IN (' . implode(',', $favorites_array) . ')';

                $str_a = array();
                foreach ($favorites_array as $a) {
                    $str_a[] = '?';
                }
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.id IN (' . implode(',', $str_a) . '))';
                $where_value_prepared = array_merge($where_value_prepared, $favorites_array);
            }
        }

        if (isset($params['client_id']) && (int) $params['client_id'] != 0) {

            $where_array[] = 're_data.client_id=' . (int) $params['client_id'];
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.client_id = ?)';
            $where_value_prepared[] = (int) $params['client_id'];
        } else {
            unset($params['client_id']);
        }

        if (isset($params['uniq_id']) && (int) $params['uniq_id'] != 0) {
            $where_array[] = 're_data.uniq_id=' . (int) $params['uniq_id'];
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.uniq_id = ?)';
            $where_value_prepared[] = (int) $params['uniq_id'];
        } else {
            unset($params['uniq_id']);
        }

        if (isset($params['optype']) && is_array($params['optype'])) {
            $optypes_array = $params['optype'];
            foreach ($optypes_array as $k => $v) {
                if ((int) $v != 0) {
                    $optypes_array[$k] = (int) $v;
                } else {
                    unset($optypes_array[$k]);
                }
            }
            if (count($optypes_array) > 0) {
                $where_array[] = 're_data.optype IN (' . implode(',', $optypes_array) . ')';

                $str_a = array();
                foreach ($optypes_array as $a) {
                    $str_a[] = '?';
                }
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.optype IN (' . implode(',', $str_a) . '))';
                $where_value_prepared = array_merge($where_value_prepared, $optypes_array);
            }
        } elseif (isset($params['optype']) && $params['optype'] > 0) {
            $where_array[] = DB_PREFIX . '_data.optype=' . (int) $params['optype'];
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.optype = ?)';
            $where_value_prepared[] = (int) $params['optype'];
        }

        //$where_array[] = 're_topic.id=re_data.topic_id';
        //$where_array_prepared[]='('.DB_PREFIX.'_topic.id='.DB_PREFIX.'_data.topic_id)';
        //echo '$params[\'topic_id\'] = '.$params['topic_id'].'<br>';

        if (isset($params['topic_id'])) {
            $topics = $params['topic_id'];

            if (!is_array($topics)) {
                $topics = (array) $topics;
            }
            if (!empty($topics)) {
                $list = array();
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                $Structure_Manager = new Structure_Manager();
                $category_structure = $Structure_Manager->loadCategoryStructure();
                foreach ($topics as $topic_id) {
                    if (intval($topic_id) > 0 && isset($category_structure['catalog'][$topic_id])) {
                        $childs = $Structure_Manager->get_all_childs($topic_id, $category_structure);
                        if (!empty($childs)) {
                            $list = array_merge($list, $childs);
                        }
                        $list[] = intval($topic_id);
                    }
                }
            }

            if (!empty($list)) {
                $list = array_unique($list, SORT_NUMERIC);
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.topic_id IN (' . implode(',', array_fill(0, count($list), '?')) . '))';
                $where_value_prepared = array_merge($where_value_prepared, $list);
            } else {
                unset($params['topic_id']);
            }
        }



        /* if ( !is_array($params['topic_id']) && $params['topic_id'] != '' &&  (int)$params['topic_id'] != 0) {
          $topic_id=(int)$params['topic_id'];
          require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
          $Structure_Manager = new Structure_Manager();
          $category_structure = $Structure_Manager->loadCategoryStructure();
          $childs = $Structure_Manager->get_all_childs($topic_id, $category_structure);
          if ( count($childs) > 0 ) {
          array_push($childs, $topic_id);
          $where_array[] = DB_PREFIX.'_data.topic_id IN ('.implode(' , ', $childs).') ';
          $str_a=array();
          foreach($childs as $a){
          $str_a[]='?';
          }
          $where_array_prepared[]='('.DB_PREFIX.'_data.topic_id IN ('.implode(',', $str_a).'))';
          $where_value_prepared=array_merge($where_value_prepared, $childs);
          } else {
          $where_array[] = 're_data.topic_id='.$topic_id;
          $where_array_prepared[]='('.DB_PREFIX.'_data.topic_id=?)';
          $where_value_prepared[]=$topic_id;
          }
          }elseif(is_array($params['topic_id'])){
          $topics_array=$params['topic_id'];
          foreach ($topics_array as $k=>$v){
          if((int)$v!=0){
          $topics_array[$k]=(int)$v;
          }else{
          unset($topics_array[$k]);
          }
          }
          if(count($topics_array)>0){
          $where_array[] = DB_PREFIX.'_data.topic_id IN ('.implode(',', $topics_array).')';
          $str_a=array();
          foreach($topics_array as $a){
          $str_a[]='?';
          }
          $where_array_prepared[]='('.DB_PREFIX.'_data.topic_id IN ('.implode(',', $str_a).'))';
          $where_value_prepared=array_merge($where_value_prepared, $topics_array);
          }
          //$where_array[] = 're_data.topic_id IN ('.implode(',', $params['topic_id']).')';
          }else{
          unset($params['topic_id']);
          } */
        
        
        if(isset($params['wlocation']) && is_array($params['wlocation']) && !empty($params['wlocation'])){
            $wsubquery=array();
            $wsubqueryparams=array();
            foreach($params['wlocation'] as $wlocation){
                $subquery=array();
                $subqueryparams=array();
                foreach($wlocation as $k=>$v){
                    switch($k){
                        case 'country_id' : {
                            $subquery[] = '' . DB_PREFIX . '_data.country_id=?';
                            $subqueryparams[] = $v;
                            break;
                        }
                        case 'region_id' : {
                            $subquery[] = '' . DB_PREFIX . '_data.region_id=?';
                            $subqueryparams[] = $v;
                            break;
                        }
                        case 'city_id' : {
                            $subquery[] = '' . DB_PREFIX . '_data.city_id=?';
                            $subqueryparams[] = $v;
                            break;
                        }
                        case 'district_id' : {
                            $subquery[] = '' . DB_PREFIX . '_data.district_id=?';
                            $subqueryparams[] = $v;
                            break;
                        }
                        case 'street_id' : {
                            $subquery[] = '' . DB_PREFIX . '_data.street_id=?';
                            $subqueryparams[] = $v;
                            break;
                        }
                        case 'number' : {
                            $subquery[] = '' . DB_PREFIX . '_data.number=?';
                            $subqueryparams[] = $v;
                            break;
                        }
                    }
                }
                
                if(!empty($subquery)){
                    $wsubquery[]=implode(' AND ', $subquery);
                    $wsubqueryparams = array_merge($wsubqueryparams, $subqueryparams);
                }
                
            }
            
            if(!empty($wsubquery)){
                $where_array_prepared[] = '(('.implode(') OR (', $wsubquery).'))';
                $where_value_prepared = array_merge($where_value_prepared, $wsubqueryparams);
            }
            
        }
        
        if(isset($params['loc'])){
			$pairs=array();
			foreach($params['loc'] as $k=>$loc){
                $loc=urldecode($loc);
				if(preg_match('/^(\d+)\|(.*)/', $loc, $matches)){
					$sid=$matches[1];
					$nid=preg_replace('/[^[a-zа-я0-9-] ]/i', '', trim($matches[2]));
					if($sid>0 && $nid!=''){
						$pairs[]=array('sid'=>$sid, 'nid'=>$nid);
					}
				}
			}
            
			if(!empty($pairs)){
				$q=array();
				$v=array();
				foreach($pairs as $pair){
					$q[]='('.DB_PREFIX.'_data.street_id=? AND '.DB_PREFIX.'_data.number=?)';
					$v[]=$pair['sid'];
					$v[]=$pair['nid'];
				}
				$where_array_prepared[]='('.implode(' OR ', $q).')';
				$where_value_prepared=array_merge($where_value_prepared, $v);
				
				unset($pairs);
				unset($q);
				unset($v);
			}
			
		}
        
        
        if (isset($params['country_id']) && (int) $params['country_id'] != 0) {
            $where_array[] = 're_data.country_id = ' . (int) $params['country_id'];
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.country_id=?)';
            $where_value_prepared[] = (int) $params['country_id'];
        } else {
            unset($params['country_id']);
        }
        
        if (isset($params['community_id'])) {
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/community/admin/admin.php';
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/community/site/site.php';
            $CS=new community_site();
            $ids=$CS->getCommunityUsersIds($params['community_id']);
            if(!empty($ids)){
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.user_id IN (' . implode(',', array_fill(0, count($ids), '?')) . '))';
                $where_value_prepared = array_merge($where_value_prepared, $ids);
            }else{
                $where_array_prepared[] = '1=0';
            }
        }
        
        if (isset($params['complex_id'])) {
            if (is_array($params['complex_id'])) {
                $complex_array = $params['complex_id'];
                foreach ($complex_array as $k => $v) {
                    if ((int) $v != 0) {
                        $complex_array[$k] = (int) $v;
                    } else {
                        unset($complex_array[$k]);
                    }
                }
                if (count($complex_array) > 0) {
                    $where_array[] = 're_data.complex_id IN (' . implode(',', $complex_array) . ')';

                    $str_a = array();
                    foreach ($complex_array as $a) {
                        $str_a[] = '?';
                    }
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.complex_id IN (' . implode(',', $str_a) . '))';
                    $where_value_prepared = array_merge($where_value_prepared, $complex_array);
                } else {
                    unset($params['complex_id']);
                }
            } else {
                if (intval($params['complex_id']) > 0) {
                    $where_array[] = DB_PREFIX . '_data.`complex_id` = ' . intval($params['complex_id']);
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.`complex_id`=?)';
                    $where_value_prepared[] = intval($params['complex_id']);
                } else {
                    unset($params['complex_id']);
                }
            }
        }

        if (isset($params['complex_building_id'])) {
            if (is_array($params['complex_building_id'])) {
                $complex_array = $params['complex_building_id'];
                foreach ($complex_array as $k => $v) {
                    if ((int) $v != 0) {
                        $complex_array[$k] = (int) $v;
                    } else {
                        unset($complex_array[$k]);
                    }
                }
                if (count($complex_array) > 0) {
                    $where_array[] = 're_data.complex_building_id IN (' . implode(',', $complex_array) . ')';

                    $str_a = array();
                    foreach ($complex_array as $a) {
                        $str_a[] = '?';
                    }
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.complex_building_id IN (' . implode(',', $str_a) . '))';
                    $where_value_prepared = array_merge($where_value_prepared, $complex_array);
                } else {
                    unset($params['complex_building_id']);
                }
            } else {
                if (intval($params['complex_building_id']) > 0) {
                    $where_array[] = DB_PREFIX . '_data.`complex_building_id` = ' . intval($params['complex_building_id']);
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.`complex_building_id`=?)';
                    $where_value_prepared[] = intval($params['complex_building_id']);
                } else {
                    unset($params['complex_building_id']);
                }
            }
        }

        /* if ( isset($params['complex_id']) && (int)$params['complex_id'] != 0  ) {
          $where_array[] = 're_data.complex_id = '.(int)$params['complex_id'];
          $where_array_prepared[]='('.DB_PREFIX.'_data.complex_id=?)';
          $where_value_prepared[]=(int)$params['complex_id'];
          }else{
          unset($params['complex_id']);
          } */

        /*
          if ( isset($params['id']) && (int)$params['id'] != 0  ) {
          $where_array[] = 're_data.id = '.(int)$params['id'];
          $where_array_prepared[]='('.DB_PREFIX.'_data.id=?)';
          $where_value_prepared[]=(int)$params['id'];
          }
         */
        if (isset($params['id']) && is_array($params['id'])) {

            if (!empty($params['id'])) {
                $str_a = array();
                foreach ($params['id'] as $k => $_id) {
                    if ((int) $_id != 0) {
                        $str_a[] = '?';
                    } else {
                        unset($params['id'][$k]);
                    }
                }
                if (!empty($params['id'])) {
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.id IN (' . implode(',', $str_a) . '))';
                    $where_value_prepared = array_merge($where_value_prepared, $params['id']);
                }
            } else {
                unset($params['id']);
            }
        } elseif (isset($params['id'])) {
            if ((int) $params['id'] != 0) {
                $where_array[] = 're_data.id = ' . (int) $params['id'];
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.id=?)';
                $where_value_prepared[] = (int) $params['id'];
            } else {
                unset($params['id']);
            }
        }
        
        //echo $_SESSION['user_domain_owner'];
        if (isset($_SESSION['user_domain_owner']) && (int) $_SESSION['user_domain_owner']['user_id'] != 0) {
            //$where_array[] = 're_data.user_id = '.(int)$params['user_id'];
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.user_id=?)';
            $where_value_prepared[] = (int) $_SESSION['user_domain_owner']['user_id'];
        } else {
            
            /*if (isset($params['user_id']) && (int) $params['user_id'] > 0) {
                if(isset($params['coworked_ids']) && !empty($params['coworked_ids'])){
                    $where_array[] = 're_data.user_id = ' . (int) $params['user_id'];
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.user_id=? OR ' . DB_PREFIX . '_data.id IN ('.implode(',', array_fill(0, count($params['coworked_ids']), '?')).'))';
                    $where_value_prepared[] = (int) $params['user_id'];
                    $where_value_prepared= array_merge($where_value_prepared, $params['coworked_ids']);
                }else{
                    $where_array[] = 're_data.user_id = ' . (int) $params['user_id'];
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.user_id=?)';
                    $where_value_prepared[] = (int) $params['user_id'];
                }
                
            } else {
                unset($params['user_id']);
            }*/
            
            if(isset($params['user_id'])){
                if(isset($params['coworked_ids']) && !empty($params['coworked_ids'])){
                    $where_array[] = 're_data.user_id = ' . (int) $params['user_id'];
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.user_id=? OR ' . DB_PREFIX . '_data.id IN ('.implode(',', array_fill(0, count($params['coworked_ids']), '?')).'))';
                    $where_value_prepared[] = (int) $params['user_id'];
                    $where_value_prepared = array_merge($where_value_prepared, $params['coworked_ids']);
                }elseif(isset($params['coworked_users']) && !empty($params['coworked_users'])){
                    $where_array[] = 're_data.user_id = ' . (int) $params['user_id'];
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.user_id=? OR ' . DB_PREFIX . '_data.user_id IN ('.implode(',', array_fill(0, count($params['coworked_users']), '?')).'))';
                    $where_value_prepared[] = (int) $params['user_id'];
                    $where_value_prepared = array_merge($where_value_prepared, $params['coworked_users']);
                }else{
                    if(is_array($params['user_id'])){
                        $where_array[] = 're_data.user_id IN ('.implode(',', $params['user_id']).')';
                        //$where_array_prepared[] = '(' . DB_PREFIX . '_data.user_id IN ())';
                        $where_array_prepared[] = '(' . DB_PREFIX . '_data.user_id IN ('.implode(',', array_fill(0, count($params['user_id']), '?')).'))';
                        $where_value_prepared = array_merge($where_value_prepared, $params['user_id']);
                    }else{
                        if((int) $params['user_id'] > 0){
                            $where_array[] = 're_data.user_id = ' . (int) $params['user_id'];
                            $where_array_prepared[] = '(' . DB_PREFIX . '_data.user_id=?)';
                            $where_value_prepared[] = (int) $params['user_id'];
                        }
                        
                    }
                    
                }
            }
            
            
        }
        
        

        if (isset($params['agg_user_id'])) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.user_id IN (' . implode(',', array_fill(0, count($params['agg_user_id']), '?')) . '))';
            $where_value_prepared = array_merge($where_value_prepared, array_values($params['agg_user_id']));
            unset($params['agg_user_id']);
        }


        if (isset($params['onlyspecial']) && (int) $params['onlyspecial'] > 0) {
            $where_array[] = 're_data.hot = 1';
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.hot=1)';
        } else {
            unset($params['onlyspecial']);
        }


        if (isset($params['price']) && $params['price'] != 0) {

            //$price_str=preg_replace('/[^\d.,]/', '', $params['price']);
            $price_str = (int) str_replace(' ', '', $params['price']);
            if ($this->use_currency) {
                //$where_array[] = DB_PREFIX . '_data.price  <= ' . $price_str;
                $where_array_prepared[] = '(((' . DB_PREFIX . '_data.price*' . DB_PREFIX . '_currency.course)/' . $this->price_koefficient . ')<=?)';
                $where_value_prepared[] = $price_str;
            } else {
                //$where_array[] = DB_PREFIX . '_data.price  <= ' . $price_str;
                //if(isset($this->grid_item_data_model['discount_perc']))
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.price<=?)';
                $where_value_prepared[] = $price_str;
            }
            
        } else {
            unset($params['price']);
        }



        if (isset($params['price_min']) && $params['price_min'] != 0) {
            $price_str = (int) str_replace(' ', '', $params['price_min']);
            if ($this->use_currency) {
                $where_array[] = DB_PREFIX . '_data.price  >= ' . $price_str;
                $where_array_prepared[] = '(((' . DB_PREFIX . '_data.price*' . DB_PREFIX . '_currency.course)/' . $this->price_koefficient . ')>=?)';
                $where_value_prepared[] = $price_str;
            } else {
                $where_array[] = DB_PREFIX . '_data.price  >= ' . $price_str;
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.price>=?)';
                $where_value_prepared[] = $price_str;
            }
        } else {
            unset($params['price_min']);
        }
        ////
        if (isset($params['price_pm']) && $params['price_pm'] != 0) {
            $price_str = (int) str_replace(' ', '', $params['price_pm']);

            if ($this->use_currency) {
                $where_array[] = DB_PREFIX . '_data.price_pm<=' . $price_str;
                $where_array_prepared[] = '(((' . DB_PREFIX . '_data.price_pm*' . DB_PREFIX . '_currency.course)/' . $this->price_koefficient . ')<=?)';
                $where_value_prepared[] = $price_str;
            } else {
                $where_array[] = DB_PREFIX . '_data.price_pm<= ' . $price_str;
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.price_pm<=?)';
                $where_value_prepared[] = $price_str;
            }
        } else {
            unset($params['price_pm']);
        }
        if (isset($params['price_pm_min']) && $params['price_pm_min'] != 0) {
            $price_str = (int) str_replace(' ', '', $params['price_pm_min']);

            if ($this->use_currency) {
                $where_array[] = DB_PREFIX . '_data.price_pm  >= ' . $price_str;
                $where_array_prepared[] = '(((' . DB_PREFIX . '_data.price_pm*' . DB_PREFIX . '_currency.course)/' . $this->price_koefficient . ')>=?)';
                $where_value_prepared[] = $price_str;
            } else {
                $where_array[] = DB_PREFIX . '_data.price_pm  >= ' . $price_str;
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.price_pm>=?)';
                $where_value_prepared[] = $price_str;
            }
        } else {
            unset($params['price_pm_min']);
        }
        //////
        if (isset($params['house_number']) && $params['house_number'] != '') {
            $number = trim($params['house_number']);
            $number = preg_replace('/[^[a-zа-я0-9-] ]/i', '', $number);
            $where_array[] = 're_data.number  = \'' . $number . '\'';
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.number=?)';
            $where_value_prepared[] = $number;
        } else {
            unset($params['house_number']);
        }
        
        if (isset($params['region_id']) && (int) $params['region_id'] != 0) {
            if (is_array($params['region_id']) && !empty($params['region_id'])) {
                $regions_array = $params['region_id'];
                foreach ($regions_array as $k => $v) {
                    if ((int) $v != 0) {
                        $regions_array[$k] = (int) $v;
                    } else {
                        unset($regions_array[$k]);
                    }
                }
                if (count($regions_array) > 0) {
                    $where_array[] = 're_data.region_id IN (' . implode(',', $regions_array) . ')';

                    $str_a = array();
                    foreach ($regions_array as $a) {
                        $str_a[] = '?';
                    }
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.region_id IN (' . implode(',', $str_a) . '))';
                    $where_value_prepared = array_merge($where_value_prepared, $regions_array);
                }
            } else {
                $where_array[] = 're_data.region_id = ' . (int) $params['region_id'];
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.region_id=?)';
                $where_value_prepared[] = (int) $params['region_id'];
            }
        } else {
            unset($params['region_id']);
        }

        if (isset($params['spec']) && $params['spec'] != '') {
            $where_array[] = ' re_data.hot = 1 ';
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.hot=1)';
        } else {
            unset($params['spec']);
        }
        if (isset($params['hot']) && $params['hot'] != '') {
            $where_array[] = ' re_data.hot = 1 ';
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.hot=1)';
        } else {
            unset($params['hot']);
        }
        
        if (isset($params['district_id']) && $params['district_id'] != 0) {
            if (is_array($params['district_id']) && !empty($params['district_id'])) {
                $districts_array = $params['district_id'];
                foreach ($districts_array as $k => $v) {
                    if ((int) $v != 0) {
                        $districts_array[$k] = (int) $v;
                    } else {
                        unset($districts_array[$k]);
                    }
                }
                if (count($districts_array) > 0) {
                    $where_array[] = 're_data.district_id IN (' . implode(',', $districts_array) . ')';

                    $str_a = array();
                    foreach ($districts_array as $a) {
                        $str_a[] = '?';
                    }
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.district_id IN (' . implode(',', $str_a) . '))';
                    $where_value_prepared = array_merge($where_value_prepared, $districts_array);
                }
                unset($districts_array);
            } else {
                $where_array[] = 're_data.district_id = ' . (int) $params['district_id'];
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.district_id=?)';
                $where_value_prepared[] = (int) $params['district_id'];
            }
        } else {
            unset($params['district_id']);
        }

        if (isset($params['city_id']) && $params['city_id'] != 0) {
            if (is_array($params['city_id']) && !empty($params['city_id'])) {
                $city_array = $params['city_id'];
                foreach ($city_array as $k => $v) {
                    if ((int) $v != 0) {
                        $city_array[$k] = (int) $v;
                    } else {
                        unset($city_array[$k]);
                    }
                }
                if (count($city_array) > 0) {
                    $where_array[] = 're_data.city_id IN (' . implode(',', $city_array) . ')';

                    $str_a = array();
                    foreach ($city_array as $a) {
                        $str_a[] = '?';
                    }
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.city_id IN (' . implode(',', $str_a) . '))';
                    $where_value_prepared = array_merge($where_value_prepared, $city_array);
                }
                unset($city_array);
            } else {
                $where_array[] = 're_data.city_id = ' . (int) $params['city_id'];
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.city_id=?)';
                $where_value_prepared[] = (int) $params['city_id'];
            }
        } else {
            unset($params['city_id']);
        }

        if (isset($params['metro_id']) and $params['metro_id'] != 0) {
            if (is_array($params['metro_id']) && !empty($params['metro_id'])) {
                $metro_array = $params['metro_id'];
                foreach ($metro_array as $k => $v) {
                    if ((int) $v != 0) {
                        $metro_array[$k] = (int) $v;
                    } else {
                        unset($metro_array[$k]);
                    }
                }
                if (count($metro_array) > 0) {
                    $where_array[] = 're_data.metro_id IN (' . implode(',', $metro_array) . ')';

                    $str_a = array();
                    foreach ($metro_array as $a) {
                        $str_a[] = '?';
                    }
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.metro_id IN (' . implode(',', $str_a) . '))';
                    $where_value_prepared = array_merge($where_value_prepared, $metro_array);
                }
                unset($metro_array);
            } else {
                $where_array[] = 're_data.metro_id = ' . (int) $params['metro_id'];
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.metro_id=?)';
                $where_value_prepared[] = (int) $params['metro_id'];
            }
        } else {
            unset($params['metro_id']);
        }

        if (isset($params['street_id']) and $params['street_id'] != 0) {
            if (is_array($params['street_id']) && !empty($params['street_id'])) {
                $street_array = $params['street_id'];
                foreach ($street_array as $k => $v) {
                    if ((int) $v != 0) {
                        $street_array[$k] = (int) $v;
                    } else {
                        unset($street_array[$k]);
                    }
                }
                if (count($street_array) > 0) {
                    $where_array[] = 're_data.street_id IN (' . implode(',', $street_array) . ')';

                    $str_a = array();
                    foreach ($street_array as $a) {
                        $str_a[] = '?';
                    }
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.street_id IN (' . implode(',', $str_a) . '))';
                    $where_value_prepared = array_merge($where_value_prepared, $street_array);
                }
                unset($street_array);
            } else {
                $where_array[] = 're_data.street_id = ' . (int) $params['street_id'];
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.street_id=?)';
                $where_value_prepared[] = (int) $params['street_id'];
            }
        } else {
            unset($params['street_id']);
        }



        if (isset($params['srch_phone']) && $params['srch_phone'] !== NULL && trim($params['srch_phone']) !== '') {
            $phone = preg_replace('/[^\d]/', '', $params['srch_phone']);
            $sub_where = array();
            $where_array_prepared_sub = array();
            if ($this->getConfigValue('allow_additional_mobile_number')) {
                $sub_where[] = '(re_data.ad_mobile_phone LIKE \'%' . $phone . '%\')';

                $where_array_prepared_sub[] = '(' . DB_PREFIX . '_data.ad_mobile_phone LIKE ?)';
                $where_value_prepared[] = '%' . $phone . '%';
            }
            if ($this->getConfigValue('allow_additional_stationary_number')) {
                $sub_where[] = '(re_data.ad_stacionary_phone LIKE \'%' . $phone . '%\')';

                $where_array_prepared_sub[] = '(' . DB_PREFIX . '_data.ad_stacionary_phone LIKE ?)';
                $where_value_prepared[] = '%' . $phone . '%';
            }
            $sub_where[] = '(re_data.phone LIKE \'%' . $phone . '%\')';


            $where_array_prepared_sub[] = '(' . DB_PREFIX . '_data.phone LIKE ?)';
            $where_value_prepared[] = '%' . $phone . '%';
            $where_array_prepared[] = '(' . implode(' OR ', $where_array_prepared_sub) . ')';

            $where_array[] = '(' . implode(' OR ', $sub_where) . ')';
        } else {
            unset($params['srch_phone']);
        }

        if (isset($params['srch_word']) and $params['srch_word'] !== NULL) {
            $sub_where = array();
            $where_array_prepared_sub = array();

            $word = htmlspecialchars($params['srch_word']);
            if ($word != '') {
                $sub_where[] = '(re_data.text LIKE \'%' . $word . '%\')';

                $where_array_prepared_sub[] = '(' . DB_PREFIX . '_data.text LIKE ?)';
                $where_value_prepared[] = '%' . $word . '%';


                $where_array_prepared[] = '(' . implode(' OR ', $where_array_prepared_sub) . ')';
            }
        } else {
            unset($params['srch_word']);
        }

        if (isset($params['room_count'])) {
            if (is_array($params['room_count']) && count($params['room_count']) > 0) {
                $sub_where = array();
                $where_array_prepared_sub = array();
                foreach ($params['room_count'] as $rq) {
                    if ($rq == 4) {
                        $sub_where[] = 'room_count>3';
                        $where_array_prepared_sub[] = '(' . DB_PREFIX . '_data.room_count>3)';
                    } elseif (0 != (int) $rq) {
                        $sub_where[] = 'room_count=' . (int) $rq;
                        $where_array_prepared_sub[] = '(' . DB_PREFIX . '_data.room_count=?)';
                        $where_value_prepared[] = (int) $rq;
                    }
                }
                if (count($sub_where) > 0) {
                    $where_array[] = '(' . implode(' OR ', $sub_where) . ')';
                    $where_array_prepared[] = '(' . implode(' OR ', $where_array_prepared_sub) . ')';
                }
            } elseif ((int) $params['room_count'] != 0) {
                $where_array[] = 're_data.room_count = ' . (int) $params['room_count'];
                $where_value_prepared[] = (int) $params['room_count'];
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.room_count=?)';
            } else {
                unset($params['room_count']);
            }
        }

        if (isset($params['added_in_days']) && 0 != (int) $params['added_in_days']) {
            $date_limit = time() - ((int) $params['added_in_days']) * 24 * 3600;
            $where_value_prepared[] = date('Y-m-d H:i:s', $date_limit);
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.date_added>=?)';
        } else {
            unset($params['added_in_days']);
        }


        if (isset($params['srch_date_to'])) {
            $srch_date_to = '';
            if (preg_match('/^(\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d)$/', $params['srch_date_to'])) {
                $srch_date_to = $params['srch_date_to'];
            } elseif (preg_match('/^(\d\d\d\d-\d\d-\d\d)$/', $params['srch_date_to'])) {
                $srch_date_to = $params['srch_date_to'];
            } else {
                
            }
            if ($srch_date_to != '') {
                $where_array[] = "(re_data.date_added<='" . $srch_date_to . "')";
                $where_value_prepared[] = $srch_date_to;
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.date_added<=?)';
            } else {
                unset($params['srch_date_to']);
            }
        }

        if (isset($params['srch_date_from'])) {
            $srch_date_from = '';
            if (preg_match('/^(\d\d\d\d-\d\d-\d\d \d\d:\d\d:\d\d)$/', $params['srch_date_from'])) {
                $srch_date_from = $params['srch_date_from'];
            } elseif (preg_match('/^(\d\d\d\d-\d\d-\d\d)$/', $params['srch_date_from'])) {
                $srch_date_from = $params['srch_date_from'];
            } else {
                
            }
            if ($srch_date_from != '') {
                $where_array[] = "(re_data.date_added>='" . $srch_date_from . "')";
                $where_value_prepared[] = $srch_date_from;
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.date_added>=?)';
            } else {
                unset($params['srch_date_from']);
            }
        }


        if (isset($params['floor_min']) && (int) $params['floor_min'] != 0) {
            $where_array[] = "(re_data.floor>=" . (int) $params['floor_min'] . ")";
            $where_value_prepared[] = (int) $params['floor_min'];
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.floor*1 >= ?)';
        } else {
            unset($params['floor_min']);
        }

        if (isset($params['floor_max']) && (int) $params['floor_max'] != 0) {
            $where_array[] = "(re_data.floor<=" . (int) $params['floor_max'] . ")";
            $where_value_prepared[] = (int) $params['floor_max'];
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.floor*1 <= ?)';
        } else {
            unset($params['floor_max']);
        }

        if (isset($params['floor_count_min']) && (int) $params['floor_count_min'] != 0) {
            $where_array[] = "(re_data.floor_count>=" . (int) $params['floor_count_min'] . ")";
            $where_value_prepared[] = (int) $params['floor_count_min'];
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.floor_count*1 >= ?)';
        } else {
            unset($params['floor_count_min']);
        }

        if (isset($params['floor_count_max']) && (int) $params['floor_count_max'] != 0) {
            $where_array[] = "(re_data.floor_count<=" . (int) $params['floor_count_max'] . ")";
            $where_value_prepared[] = (int) $params['floor_count_max'];
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.floor_count*1 <= ?)';
        } else {
            unset($params['floor_count_max']);
        }


        if (isset($params['square_min']) && (int) $params['square_min'] != 0) {
            $square_min = preg_replace('/[^\d.,]/', '', $params['square_min']);
            $where_array[] = "(re_data.square_all>=" . $square_min . ")";
            $where_value_prepared[] = $square_min;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.square_all*1 >= ?)';
        } else {
            unset($params['square_min']);
        }

        if (isset($params['square_max']) && (int) $params['square_max'] != 0) {
            $square_max = preg_replace('/[^\d.,]/', '', $params['square_max']);
            $where_array[] = '(re_data.square_all<=' . $square_max . ')';
            $where_value_prepared[] = $square_max;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.square_all*1 <= ?)';
        } else {
            unset($params['square_max']);
        }


        if (isset($params['not_first_floor']) && (int) $params['not_first_floor'] == 1) {
            $where_array[] = "(re_data.floor*1 > 1)";
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.floor*1 > 1)';
        } else {
            unset($params['not_first_floor']);
        }

        if (isset($params['not_last_floor']) && (int) $params['not_last_floor'] == 1) {
            $where_array[] = "(re_data.floor*1 > 0 AND re_data.floor*1 <> re_data.floor_count*1)";
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.floor*1 > 0 AND ' . DB_PREFIX . '_data.floor*1 <> ' . DB_PREFIX . '_data.floor_count*1)';
        } else {
            unset($params['not_last_floor']);
        }

        if (isset($params['live_square_min']) && $params['live_square_min'] != 0 && $params['live_square_min'] !== '') {
            $square_min = preg_replace('/[^\d.,]/', '', $params['live_square_min']);
            $where_array[] = "(re_data.square_live>=" . $square_min . ")";
            $where_value_prepared[] = $square_min;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.square_live*1>= ?)';
        } else {
            unset($params['live_square_min']);
        }

        if (isset($params['live_square_max']) && $params['live_square_max'] != 0 && $params['live_square_max'] !== '') {
            $square_max = preg_replace('/[^\d.,]/', '', $params['live_square_max']);
            $where_array[] = "(re_data.square_live<=" . $square_max . ")";
            $where_value_prepared[] = $square_max;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.square_live*1<= ?)';
        } else {
            unset($params['live_square_max']);
        }


        if (isset($params['kitchen_square_min']) && $params['kitchen_square_min'] != 0 && $params['kitchen_square_min'] !== '') {
            $square_min = preg_replace('/[^\d.,]/', '', $params['kitchen_square_min']);
            $where_array[] = "(re_data.square_kitchen>=" . $square_min . ")";
            $where_value_prepared[] = $square_min;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.square_kitchen*1>= ?)';
        } else {
            unset($params['kitchen_square_min']);
        }

        if (isset($params['kitchen_square_max']) && $params['kitchen_square_max'] != 0 && $params['kitchen_square_max'] !== '') {
            $square_max = preg_replace('/[^\d.,]/', '', $params['kitchen_square_max']);
            $where_array[] = "(re_data.square_kitchen<=" . $square_max . ")";
            $where_value_prepared[] = $square_max;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.square_kitchen*1<= ?)';
        } else {
            unset($params['kitchen_square_max']);
        }


        if (isset($params['is_phone']) && (int) $params['is_phone'] == 1) {
            $where_array[] = '(' . DB_PREFIX . '_data.is_telephone=1)';
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.is_telephone=1)';
        } else {
            unset($params['is_phone']);
        }

        if (isset($params['is_internet']) && (int) $params['is_internet'] == 1) {
            $where_array[] = '(' . DB_PREFIX . '_data.is_internet=1)';
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.is_internet=1)';
        } else {
            unset($params['is_internet']);
        }

        if (isset($params['is_furniture']) && (int) $params['is_furniture'] == 1) {
            $where_array[] = '(' . DB_PREFIX . '_data.furniture=1)';
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.furniture=1)';
        } else {
            unset($params['is_furniture']);
        }

        if (isset($params['owner']) && (int) $params['owner'] == 1) {
            $where_array[] = '(' . DB_PREFIX . '_data.whoyuaare=1)';
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.whoyuaare=1)';
        } else {
            unset($params['owner']);
        }

        if (isset($params['status_id']) && isset($this->grid_item_data_model['status_id']) && intval($params['status_id']) > 0) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.status_id=?)';
            $where_value_prepared[] = intval($params['status_id']);
        } else {
            unset($params['status_id']);
        }

        if (isset($params['has_photo']) && (int) $params['has_photo'] == 1) {
            //print_r($_model);
            $hasUploadify = false;
            $hasUploads = false;
            $uploadsFields = array();
            foreach ($this->grid_item_data_model as $item) {
                if ($item['type'] == 'uploadify_image') {
                    $hasUploadify = true;
                    break;
                } elseif ($item['type'] == 'uploads') {
                    $hasUploads = true;
                    $uploadsFields[] = $item['name'];
                }
            }

            //print_r($uploadsFields);

            if ($hasUploadify) {
                $where_array[] = '((SELECT COUNT(*) FROM ' . DB_PREFIX . '_data_image WHERE id=' . DB_PREFIX . '_data.id)>0)';
                $where_array_prepared[] = '((SELECT COUNT(*) FROM ' . DB_PREFIX . '_data_image WHERE id=' . DB_PREFIX . '_data.id)>0)';
            } elseif ($hasUploads) {
                $sub_query = array();
                foreach ($uploadsFields as $uf) {
                    $sub_query[] = DB_PREFIX . '_data.`' . $uf . '`<>\'\'';
                }
                $where_array_prepared[] = '(' . implode(' OR ', $sub_query) . ')';
                $where_array[] = '(' . implode(' OR ', $sub_query) . ')';
                ;
            }
        } else {
            unset($params['has_photo']);
        }

        if (isset($params['infra_greenzone']) && (int) $params['infra_greenzone'] == 1) {
            $where_array[] = "(re_data.infra_greenzone=1)";
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.infra_greenzone=1)';
        } else {
            unset($params['infra_greenzone']);
        }

        if (isset($params['infra_sea']) && (int) $params['infra_sea'] == 1) {
            $where_array[] = "(re_data.infra_sea=1)";
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.infra_sea=1)';
        } else {
            unset($params['infra_sea']);
        }

        if (isset($params['infra_sport']) && (int) $params['infra_sport'] == 1) {
            $where_array[] = "(re_data.infra_sport=1)";
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.infra_sport=1)';
        } else {
            unset($params['infra_sport']);
        }

        if (isset($params['infra_clinic']) && (int) $params['infra_clinic'] == 1) {
            $where_array[] = "(re_data.infra_clinic=1)";
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.infra_clinic=1)';
        } else {
            unset($params['infra_clinic']);
        }

        if (isset($params['infra_terminal']) && (int) $params['infra_terminal'] == 1) {
            $where_array[] = "(re_data.infra_terminal=1)";
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.infra_terminal=1)';
        } else {
            unset($params['infra_terminal']);
        }

        if (isset($params['infra_airport']) && (int) $params['infra_airport'] == 1) {
            $where_array[] = "(re_data.infra_airport=1)";
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.infra_airport=1)';
        } else {
            unset($params['infra_airport']);
        }

        if (isset($params['infra_bank']) && (int) $params['infra_bank'] == 1) {
            $where_array[] = "(re_data.infra_bank=1)";
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.infra_bank=1)';
        } else {
            unset($params['infra_bank']);
        }

        if (isset($params['infra_restaurant']) && (int) $params['infra_restaurant'] == 1) {
            $where_array[] = "(re_data.infra_restaurant=1)";
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.infra_restaurant=1)';
        } else {
            unset($params['infra_restaurant']);
        }

        if (isset($params['object_state']) && is_array($params['object_state']) && count($params['object_state']) > 0) {
            $state_array = $params['object_state'];
            foreach ($state_array as $k => $v) {
                if ((int) $v != 0) {
                    $state_array[$k] = (int) $v;
                } else {
                    unset($state_array[$k]);
                }
            }
            if (count($state_array) > 0) {
                $where_array[] = 're_data.object_state IN (' . implode(',', $state_array) . ')';

                $str_a = array();
                foreach ($state_array as $a) {
                    $str_a[] = '?';
                }
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.object_state IN (' . implode(',', $str_a) . '))';
                $where_value_prepared = array_merge($where_value_prepared, $state_array);
            }
        } else {
            unset($params['object_state']);
        }

        if (isset($params['object_type']) && is_array($params['object_type']) && count($params['object_type']) > 0) {
            $state_array = $params['object_type'];
            foreach ($state_array as $k => $v) {
                if ((int) $v != 0) {
                    $state_array[$k] = (int) $v;
                } else {
                    unset($state_array[$k]);
                }
            }
            if (count($state_array) > 0) {
                $where_array[] = 're_data.object_destination IN (' . implode(',', $state_array) . ')';

                $str_a = array();
                foreach ($state_array as $a) {
                    $str_a[] = '?';
                }
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.object_destination IN (' . implode(',', $str_a) . '))';
                $where_value_prepared = array_merge($where_value_prepared, $state_array);
            }
        } else {
            unset($params['object_type']);
        }
        
        if (isset($params['aim']) && is_array($params['aim']) && count($params['aim']) > 0) {
            $state_array = $params['aim'];
            foreach ($state_array as $k => $v) {
                if ((int) $v != 0) {
                    $state_array[$k] = (int) $v;
                } else {
                    unset($state_array[$k]);
                }
            }
            if (count($state_array) > 0) {
                $where_array[] = 're_data.aim IN (' . implode(',', $state_array) . ')';

                $str_a = array();
                foreach ($state_array as $a) {
                    $str_a[] = '?';
                }
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.aim IN (' . implode(',', $str_a) . '))';
                $where_value_prepared = array_merge($where_value_prepared, $state_array);
            }
        } else {
            unset($params['aim']);
        }

        if (isset($params['export_afy']) && (int) $params['export_afy'] == 1 && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/afyexporter/admin/admin.php')) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.export_afy=1)';
        } else {
            unset($params['export_afy']);
        }

        if (isset($params['export_cian']) && (int) $params['export_cian'] == 1 && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/cianexporter/admin/admin.php')) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.export_cian=1)';
        } else {
            unset($params['export_cian']);
        }
        
        if (isset($params['map_bounds'])) {
            $where_array_prepared[] = '((' . DB_PREFIX . '_data.geo_lat BETWEEN ? AND ?) AND (' . DB_PREFIX . '_data.geo_lng BETWEEN ? AND ?))';
            $where_value_prepared[] = $params['map_bounds'][0][0];
            $where_value_prepared[] = $params['map_bounds'][1][0];
            $where_value_prepared[] = $params['map_bounds'][0][1];
            $where_value_prepared[] = $params['map_bounds'][1][1];
        }
        //Сомнительно
        if (isset($params['geocoords'])) {
            if (preg_match('/([-]?[0-9]{2,3}\.[0-9]{6}),([-]?[0-9]{2,3}\.[0-9]{6}):([-]?[0-9]{2,3}\.[0-9]{6}),([-]?[0-9]{2,3}\.[0-9]{6})/', $params['geocoords'], $matches)) {
                //print_r();
                $lat_min = $matches[1];
                $lng_min = $matches[2];
                $lat_max = $matches[3];
                $lng_max = $matches[4];
                $diapasones = array();
                if ($lng_min > 0 && $lng_max < 0) {
                    $diapasones[] = array(
                        'lat_min' => $lat_min,
                        'lat_max' => $lat_max,
                        'lng_min' => $lng_min,
                        'lng_max' => 180
                    );
                    $diapasones[] = array(
                        'lat_min' => $lat_min,
                        'lat_max' => $lat_max,
                        'lng_min' => -180,
                        'lng_max' => $lng_max
                    );
                } else {
                    $diapasones[] = array(
                        'lat_min' => $lat_min,
                        'lat_max' => $lat_max,
                        'lng_min' => $lng_min,
                        'lng_max' => $lng_max
                    );
                }

                $where_array_prepared[] = '(' . DB_PREFIX . '_data.geo_lat IS NOT NULL AND ' . DB_PREFIX . '_data.geo_lng IS NOT NULL)';

                $subarray = array();
                foreach ($diapasones as $diapasone) {

                    $subarray[] = '(' . DB_PREFIX . '_data.geo_lat >=? AND ' . DB_PREFIX . '_data.geo_lat <= ? AND ' . DB_PREFIX . '_data.geo_lng >=? AND ' . DB_PREFIX . '_data.geo_lng <= ?)';
                    $where_value_prepared[] = $diapasone['lat_min'];
                    $where_value_prepared[] = $diapasone['lat_max'];
                    $where_value_prepared[] = $diapasone['lng_min'];
                    $where_value_prepared[] = $diapasone['lng_max'];
                }

                $where_array_prepared[] = '(' . implode(' OR ', $subarray) . ')';
            }
        } elseif (isset($params['has_geo']) && (int) $params['has_geo'] == 1) {
            $where_array[] = '(' . DB_PREFIX . '_data.geo_lat IS NOT NULL AND ' . DB_PREFIX . '_data.geo_lng IS NOT NULL)';
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.geo_lat IS NOT NULL AND ' . DB_PREFIX . '_data.geo_lng IS NOT NULL)';
        } else {
            unset($params['has_geo']);
        }

        if (isset($params['minbeds']) && (int) $params['minbeds'] != 0) {
            $where_array[] = "(re_data.bedrooms_count >= " . (int) $params['minbeds'] . ")";
            $where_value_prepared[] = (int) $params['minbeds'];
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.bedrooms_count>=?)';
        } else {
            unset($params['minbeds']);
        }

        if (isset($params['minbaths']) && (int) $params['minbaths'] != 0) {
            $where_array[] = "(re_data.bathrooms_count >=" . (int) $params['minbaths'] . ")";
            $where_value_prepared[] = (int) $params['minbaths'];
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.bathrooms_count>=?)';
        } else {
            unset($params['minbaths']);
        }

        if (isset($params['vip_status']) && (int) $params['vip_status'] != 0) {
            $_time = strtotime(date('Y-m-d H:00:00', time() + 3600));
            $where_array[] = '(' . DB_PREFIX . '_data.vip_status_end<>0 AND ' . DB_PREFIX . '_data.vip_status_end >= ' . $_time . ')';
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.vip_status_end<>0 AND ' . DB_PREFIX . '_data.vip_status_end >= ?)';
            $where_value_prepared[] = $_time;
        } else {
            unset($params['vip_status']);
        }

        if (isset($params['premium_status']) && (int) $params['premium_status'] != 0) {
            $_time = strtotime(date('Y-m-d H:00:00', time() + 3600));
            $where_array[] = '(' . DB_PREFIX . '_data.premium_status_end<>0 AND ' . DB_PREFIX . '_data.premium_status_end >= ' . $_time . ')';
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.premium_status_end<>0 AND ' . DB_PREFIX . '_data.premium_status_end >= ?)';
            $where_value_prepared[] = $_time;
        } else {
            unset($params['premium_status']);
        }

        if (isset($params['bold_status']) && (int) $params['bold_status'] != 0) {
            $_time = strtotime(date('Y-m-d H:00:00', time() + 3600));
            $where_array[] = '(' . DB_PREFIX . '_data.bold_status_end<>0 AND ' . DB_PREFIX . '_data.bold_status_end >= ' . $_time . ')';
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.bold_status_end<>0 AND ' . DB_PREFIX . '_data.bold_status_end >= ?)';
            $where_value_prepared[] = $_time;
        } else {
            unset($params['bold_status']);
        }

        if (!isset($params['admin']) || (isset($params['admin']) && $params['admin'] != 1)) {
            $where_array[] = 're_data.active=1';
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.`active`=1)';
            if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting') && isset($this->grid_item_data_model['archived'])) {
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.`archived`<>1)';
            }
            //echo $_SESSION['current_user_group_name'];
        } else {

            if (1 == (int) $this->getConfigValue('apps.realty.use_predeleting') && $params['archived'] == 1 && isset($this->grid_item_data_model['archived'])) {
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.`archived`=1)';
            } elseif (1 == (int) $this->getConfigValue('apps.realty.use_predeleting') && isset($this->grid_item_data_model['archived'])) {
                if(ADMIN_MODE==1){
                    if (isset($params['active']) && $params['active'] == 1) {
                        $where_array_prepared[] = '(' . DB_PREFIX . '_data.`archived`=0)';
                    } elseif (isset($params['active']) && $params['active'] == 'notactive') {
                        $where_array_prepared[] = '(' . DB_PREFIX . '_data.`archived`=0)';
                    }
                }else{
                    $where_array_prepared[] = '(' . DB_PREFIX . '_data.`archived`<>1)';
                }
                
            }

            if (isset($params['active']) && $params['active'] == 1) {
                $where_array[] = '' . DB_PREFIX . '_data.`active`=1';
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.`active`=1)';
            } elseif (isset($params['active']) && $params['active'] == 'notactive') {
                $where_array[] = '' . DB_PREFIX . '_data.`active`=0';
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.`active`=0)';
            }
        }



        if ($this->getConfigValue('apps.company.timelimit')) {
            $current_time = time();

            $where_array[] = 're_data.user_id=u.user_id';
            $where_array[] = 'u.company_id=c.company_id';
            $where_array[] = "c.start_date <= $current_time";
            $where_array[] = "c.end_date >= $current_time";
            $add_from_table .= ' , re_user u, re_company c ';

            $left_joins[] = 'LEFT JOIN ' . DB_PREFIX . '_user u USING(user_id)';
            $left_joins[] = 'LEFT JOIN ' . DB_PREFIX . '_company c ON u.company_id=c.company_id';
            //$where_array_prepared[]='('.DB_PREFIX.'_data.user_id=u.user_id)';
            //$where_array_prepared[]='(u.company_id=c.company_id)';
            $where_array_prepared[] = '(c.start_date<=?)';
            $where_value_prepared[] = $current_time;
            $where_array_prepared[] = '(c.end_date >=?)';
            $where_value_prepared[] = $current_time;
        }


        if ($this->billing_mode && $premium) {
            $_time = strtotime(date('Y-m-d H:00:00', time() + 3600));
            $where_array[] = 're_data.premium_status_end >= ' . $_time;
            $where_value_prepared[] = $_time;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.premium_status_end >= ?)';
        } elseif ($this->billing_mode && $params['vip'] == 1) {
            $_time = strtotime(date('Y-m-d H:00:00', time() + 3600));
            $where_array[] = '(re_data.vip_status_end<>0 AND re_data.vip_status_end >= ' . $_time . ')';
            $where_value_prepared[] = $_time;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.vip_status_end<>0 AND ' . DB_PREFIX . '_data.vip_status_end >= ?)';
        } elseif ($this->billing_mode && $params['premium'] == 1) {
            $_time = strtotime(date('Y-m-d H:00:00', time() + 3600));
            $where_array[] = '(re_data.premium_status_end<>0 AND re_data.premium_status_end >= ' . $_time . ')';
            $where_value_prepared[] = $_time;
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.premium_status_end<>0 AND ' . DB_PREFIX . '_data.premium_status_end >= ?)';
        } elseif ($_billing_on && $params['admin'] == 1) {
            //$where_array[] = '(re_data.premium_status_end < '.time().')';
            //$where_array[] = 're_data.premium_status_end = 0';
        } elseif ($this->billing_mode) {
            if (!isset($params['no_premium_filtering']) && 1 != $this->getConfigValue('apps.billing.disable_premium_popup')) {
                $_time = strtotime(date('Y-m-d H:00:00', time() + 3600));
                $where_array[] = '(re_data.premium_status_end < ' . $_time . ')';
                $where_value_prepared[] = $_time;
                $where_array_prepared[] = '(' . DB_PREFIX . '_data.premium_status_end < ?)';
            }
        }
        
        if (isset($params['only_img']) && $params['only_img']) {
            $where_array[] = 're_data.id=i.id';
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.id=i.id)';
            $add_from_table .= ' , re_data_image i ';
        }

        return array(
            'where_array' => $where_array,
            'add_from_table' => $add_from_table,
            'add_select_value' => $add_select_value,
            'params' => $params,
            'where_array_prepared' => $where_array_prepared,
            'where_value_prepared' => $where_value_prepared,
            'left_joins' => $left_joins,
            'select_what' => $select_what
        );
    }    

}
