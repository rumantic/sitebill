<?php

namespace system\lib\frontend\grid\traits;

trait GeoQuery
{
    function get_map_search_items_fields()
    {
        if ($this->request()->get('map_search_items_fields')) {
            return $this->request()->get('map_search_items_fields');
        }
        return array('href', 'type_sh', 'price', 'city', 'image', 'image_cache', 'currency_name');
    }

    function map_search_items_html($ids)
    {
        $params['id'] = $ids;
        $params['no_portions'] = 1;
        $res = $this->get_sitebill_adv_core($params, false, false, false, false);

        $fields = $this->get_map_search_items_fields();
        $items = array();

        foreach ($res['data'] as $k => $datum) {
            //dd($datum);
            $items[] = $this->compile_map_balloon_html($datum);
        }

        return json_encode($items);
    }

    function compile_map_balloon_html( $item )
    {
        // тут локализация
        $template = SITEBILL_DOCUMENT_ROOT . '/apps/system/template/map_balloon_item.tpl';
        $template_local_legacy = SITEBILL_DOCUMENT_ROOT . '/template/frontend/'.$this->getConfigValue('theme').'/map_balloon_item.tpl';
        $template_local_config = SITEBILL_DOCUMENT_ROOT.'/template/frontend/local/'.$this->getConfigValue('apps.dashboard.config').'/resources/tpl/map_balloon_item.tpl';
        if ( file_exists($template_local_legacy) ) {
            $template = $template_local_legacy;
        } elseif ( file_exists($template_local_config) ) {
            $template = $template_local_config;
        }
        $this->template->assign('balloon_item', $item);
        return $this->template->fetch($template);
    }


    function map_search_items($ids)
    {
        $params['id'] = $ids;
        $params['no_portions'] = 1;
        $res = $this->get_sitebill_adv_core($params, false, false, false, false);

        $fields = $this->get_map_search_items_fields();
        $data = array();
        foreach ($res['data'] as $k => $datum) {
            foreach ($fields as $field) {
                if (isset($datum[$field])) {
                    $data[$k][$field] = $datum[$field];
                }
            }
        }
        $res['data'] = $data;

        return json_encode($res['data']);
    }


    function map_search_listing()
    {
        $theme = $this->getConfigValue('theme');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php');
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/main/main.php')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/main/main.php');
            $frontend = new \frontend_main();
        } else {
            $frontend = new \SiteBill_Krascap();
        }

        $params = $frontend->gatherRequestParams();


        $result_set = array();

        global $smarty;
        $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/activemap_listing.tpl';

        $responce = array(
            'status' => 0,
            'data' => array(),
            'msg' => ''
        );

        //$overall_limit = intval($this->getConfigValue('apps.geodata.iframe_map_limit'));


        $params['has_geo'] = 1;
        /*if ($overall_limit > 0) {
            $params['page_limit'] = $overall_limit;
        } else {
            $params['no_portions'] = 1;
        }*/
        //$params['geo_only'] = 1;
        $params['no_premium_filtering'] = 1;

        $all = intval($this->getRequestValue('all'));
        $bounds = $this->getRequestValue('bounds');
        $coords = $this->getRequestValue('polylineString');

        if ($all) {
            $res = $this->get_sitebill_adv_core($params, false, false, true, false);
            $msg = '';

            if ($res['_showed'] < $res['_total_records']) {
                $msg = 'Показано ' . $res['_showed'] . ' из ' . $res['_total_records'];
            }

            $smarty->assign('activemap_listing', $res['data']);

            $responce = array(
                'status' => 1,
                /*'data' => $res['data'],*/
                'listing' => $smarty->fetch($tpl),
                'total' => $res['_total_records'],
                'msg' => $msg,
                'paging' => $res['paging']
            );
        } elseif (null === $coords && null !== $bounds) {
            $msg = '';
            $params['map_bounds'] = $bounds;
            $res = $this->get_sitebill_adv_core($params, false, false, true, false);
            $smarty->assign('activemap_listing', $res['data']);
            $responce = array(
                'status' => 1,
                /*'data' => $res['data'],*/
                'listing' => $smarty->fetch($tpl),
                'total' => $res['_total_records'],
                'msg' => $msg,
                'paging' => $res['paging']
            );
        } else {
            $lines = array();

            if (null !== $coords) {
                if (!is_array($coords)) {
                    $pairs = explode(';', $coords);
                    foreach ($pairs as $p) {
                        $points[] = explode(',', $p);
                    }
                    $endel = end($points);
                    reset($points);
                    if ($endel[0] != $points[0][0] && $endel[1] != $points[0][1]) {
                        $points[] = $points[0];
                    }
                } else {
                    $points = $coords;
                    $points[] = $coords[0];
                }


                $count = count($points);
                $i = 0;
                $max_lat = false;
                $min_lat = false;
                $max_lng = false;
                $min_lng = false;
                foreach ($points as $k => $point) {
                    $lines[$k]['s']['lat'] = $point[0];
                    $lines[$k]['s']['lng'] = $point[1];
                    $lines[$k]['e']['lat'] = $points[$k + 1][0];
                    $lines[$k]['e']['lng'] = $points[$k + 1][1];
                    $delta_lat = $lines[$k]['e']['lat'] - $lines[$k]['s']['lat'];
                    $delta_lng = $lines[$k]['e']['lng'] - $lines[$k]['s']['lng'];
                    if ($delta_lng == 0) {
                        $lines[$k]['type'] = 'v';
                        $koef = 0;
                    } elseif ($delta_lat == 0) {
                        $lines[$k]['type'] = 'h';
                        $koef = 0;
                    } else {
                        $lines[$k]['type'] = 'c';
                        $koef = ($delta_lat) / ($delta_lng);
                    }

                    $lines[$k]['koef'] = $koef;
                    if ($lines[$k]['type'] == 'c') {
                        $lines[$k]['ckoef'] = $lines[$k]['s']['lat'] - $koef * $lines[$k]['s']['lng'];
                    } else {
                        $lines[$k]['ckoef'] = 0;
                    }
                    //$lines[$k]['ckoef']=$lines[$k]['s']['lat']-$koef*$lines[$k]['s']['lng'];
                    //echo $point[0].'<br>';
                    //echo $point[1].'<br>';
                    if ($max_lat !== false && $point[0] > $max_lat) {
                        $max_lat = $point[0];
                    } elseif ($max_lat === false) {
                        $max_lat = $point[0];
                    }
                    if ($min_lat !== false && $point[0] < $min_lat) {
                        $min_lat = $point[0];
                    } elseif ($min_lat === false) {
                        $min_lat = $point[0];
                    }
                    if ($max_lng !== false && $point[1] > $max_lng) {
                        $max_lng = $point[1];
                    } elseif ($max_lng === false) {
                        $max_lng = $point[1];
                    }
                    if ($min_lng !== false && $point[1] < $min_lng) {
                        $min_lng = $point[1];
                    } elseif ($min_lng === false) {
                        $min_lng = $point[1];
                    }
                    $i++;
                    if ($i == $count - 1) {
                        break;
                    }
                }
            } else {
                $smarty->assign('activemap_listing', array());
                $responce = array(
                    'status' => 0,
                    'data' => array(),
                    'listing' => $smarty->fetch($tpl),
                    'total' => '',
                    'msg' => ''
                );
                return json_encode(array($responce));
            }

            $ids = array();

            $DBC = \DBC::getInstance();
            $query = 'SELECT id, topic_id, geo_lat AS lat, geo_lng AS lng FROM ' . DB_PREFIX . '_data WHERE geo_lat IS NOT NULL AND geo_lng IS NOT NULL AND geo_lat>=? AND geo_lat<=? AND geo_lng>=? AND geo_lng<=? AND active=1';
            //print_r(array($min_lat, $max_lat, $min_lng, $max_lng));
            $stmt = $DBC->query($query, array($min_lat, $max_lat, $min_lng, $max_lng));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $ret[] = $ar;
                }
            }

            $finded_count = count($ret);
            $max_count = (int)$this->getConfigValue('apps.mapviewer.max_objects_onmap');
            if ($max_count == 0) {
                $max_count = 1000000;
            }
            //echo $finded_count;

            if ($finded_count > $max_count) {
                return json_encode('В выбранной Вами области содержится ' . $finded_count . ' объектов. Пожалуйста выберите меньшую область.');
            }

            //echo count($ret);

            $points = array();

            if (count($ret) > 0) {
                if (!empty($lines)) {
                    foreach ($ret as $pk => $point) {
                        $res = $this->isInRegion($point, $lines);
                        if ($res) {
                            $ids[] = $point['id'];

                        } else {
                            unset($ret[$pk]);
                        }
                    }
                } else {
                    foreach ($ret as $pk => $point) {
                        $ids[] = $point['id'];
                    }
                }


                $params = $frontend->gatherRequestParams();
                //$params=$this->getRequestValue('params');
                $params['id'] = $ids;
                //$params['no_portions']=1;
                //$params['has_geo']=1;
                //$params['geo_only'] = 1;
                $res = $this->get_sitebill_adv_core($params, false, false, true, false);
                $smarty->assign('activemap_listing', $res['data']);


                $responce = array(
                    'status' => 1,
                    /*'data' => $res['data'],*/
                    'listing' => $smarty->fetch($tpl),
                    'total' => $res['_total_records'],
                    'paging' => $res['paging'],
                    'msg' => $msg
                );
            } else {
                //$res['data']=array();
            }
        }


        return json_encode($responce);
    }

    function map_search()
    {

        $responce = array(
            'status' => 0,
            'data' => array(),
            'msg' => ''
        );

        if ( $this->getConfigValue('apps.geodata.disable_every_data_in_ajax') ) {
            return json_encode($responce);
        }


        $overall_limit = intval($this->getConfigValue('apps.geodata.iframe_map_limit'));

        $theme = $this->getConfigValue('theme');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php');
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/main/main.php')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/main/main.php');
            $frontend = new \frontend_main();
        } else {
            $frontend = new \SiteBill_Krascap();
        }

        $params = $frontend->gatherRequestParams();


        //$params=$this->getRequestValue('params');
        $params['has_geo'] = 1;
        if ($overall_limit > 0) {
            $params['page_limit'] = $overall_limit;
        } else {
            $params['no_portions'] = 1;
        }
        $params['geo_only'] = 1;
        $params['no_premium_filtering'] = 1;

        $all = intval($this->getRequestValue('all'));
        if ( $this->getConfigValue('apps.geodata.disable_all_data_in_ajax') ) {
            $all = false;
        }
        $bounds = $this->getRequestValue('bounds');
        $coords = $this->getRequestValue('polylineString');

        if ($all) {
            $res = $this->get_sitebill_adv_core($params, false, false, true, false);
            $msg = '';

            if ($res['_showed'] < $res['_total_records']) {
                $msg = 'Показано ' . $res['_showed'] . ' из ' . $res['_total_records'];
            }
            $responce = array(
                'status' => 1,
                'data' => $res['data'],
                'msg' => $msg,
                'all' => true,
            );
        } elseif (null === $coords && null !== $bounds) {
            $msg = '';
            $params['map_bounds'] = $bounds;
            $res = $this->get_sitebill_adv_core($params, false, false, true, false);
            $responce = array(
                'status' => 1,
                'data' => $res['data'],
                'msg' => $msg,
                'coords_and_bounds' => true,
            );
        } else {
            $lines = array();
            //

            if (null !== $coords) {
                if (is_scalar($coords)) {
                    $coords = $this->fromStringToPolylineStringPairs($coords);
                }
                $min_max_coords = $this->coords_to_min_max($coords);
                $max_lat = $min_max_coords['max_lat'];
                $min_lat = $min_max_coords['min_lat'];
                $max_lng = $min_max_coords['max_lng'];
                $min_lng = $min_max_coords['min_lng'];
            } else {
                $responce = array(
                    'status' => 0,
                    'data' => array(),
                    'msg' => '',
                    'only_coords' => true,
                );
                return json_encode(array($responce));
            }

            $ids = array();

            $DBC = \DBC::getInstance();
            $limit_and_order_map_query = '';
            if ($this->getConfigValue('apps.geodata.iframe_map_limit') > 0) {
                $limit_and_order_map_query = ' ORDER by date_added LIMIT ' . (int)$this->getConfigValue('apps.geodata.iframe_map_limit');
            }
            $query = 'SELECT id, topic_id, geo_lat AS lat, geo_lng AS lng 
                            FROM ' . DB_PREFIX . '_data 
                            WHERE geo_lat IS NOT NULL AND geo_lng IS NOT NULL 
                            AND geo_lat>=? AND geo_lat<=? AND geo_lng>=? AND geo_lng<=? 
                            AND active=1' . $limit_and_order_map_query;
            //print_r(array($min_lat, $max_lat, $min_lng, $max_lng));
            $stmt = $DBC->query($query, array($min_lat, $max_lat, $min_lng, $max_lng));
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    $ret[] = $ar;
                }
            }

            $finded_count = count($ret);
            $max_count = (int)$this->getConfigValue('apps.mapviewer.max_objects_onmap');
            if ($max_count == 0) {
                $max_count = 1000000;
            }
            //echo $finded_count;

            if ($finded_count > $max_count) {
                return json_encode('В выбранной Вами области содержится ' . $finded_count . ' объектов. Пожалуйста выберите меньшую область.');
            }

            //echo count($ret);

            $points = array();

            if (count($ret) > 0) {
                if (!empty($lines)) {
                    foreach ($ret as $pk => $point) {
                        $res = $this->isInRegion($point, $lines);
                        if ($res) {
                            $ids[] = $point['id'];

                        } else {
                            unset($ret[$pk]);
                        }
                    }
                } else {
                    foreach ($ret as $pk => $point) {
                        $ids[] = $point['id'];
                    }
                }


                $params = $frontend->gatherRequestParams();
                //$params=$this->getRequestValue('params');
                $params['id'] = $ids;
                $params['no_portions'] = 1;
                $params['has_geo'] = 1;
                $params['geo_only'] = 1;
                $res = $this->get_sitebill_adv_core($params, false, false, true, false);
                $responce = array(
                    'status' => 1,
                    'data' => $res['data'],
                    'msg' => $msg,
                    'ids_search' => true
                );
            } else {
                //$res['data']=array();
            }
        }


        return json_encode($responce);
    }

    private function coords_to_min_max($coords)
    {
        if (!is_array($coords)) {
            $pairs = explode(';', $coords);
            foreach ($pairs as $p) {
                $points[] = explode(',', $p);
            }
            $endel = end($points);
            reset($points);
            if ($endel[0] != $points[0][0] && $endel[1] != $points[0][1]) {
                $points[] = $points[0];
            }
        } else {
            $points = $coords;
            $points[] = $coords[0];
        }


        $count = count($points);
        $i = 0;
        $max_lat = false;
        $min_lat = false;
        $max_lng = false;
        $min_lng = false;
        foreach ($points as $k => $point) {
            $lines[$k]['s']['lat'] = $point[0];
            $lines[$k]['s']['lng'] = $point[1];
            $lines[$k]['e']['lat'] = $points[$k + 1][0];
            $lines[$k]['e']['lng'] = $points[$k + 1][1];
            $delta_lat = $lines[$k]['e']['lat'] - $lines[$k]['s']['lat'];
            $delta_lng = $lines[$k]['e']['lng'] - $lines[$k]['s']['lng'];
            if ($delta_lng == 0) {
                $lines[$k]['type'] = 'v';
                $koef = 0;
            } elseif ($delta_lat == 0) {
                $lines[$k]['type'] = 'h';
                $koef = 0;
            } else {
                $lines[$k]['type'] = 'c';
                $koef = ($delta_lat) / ($delta_lng);
            }

            $lines[$k]['koef'] = $koef;
            if ($lines[$k]['type'] == 'c') {
                $lines[$k]['ckoef'] = $lines[$k]['s']['lat'] - $koef * $lines[$k]['s']['lng'];
            } else {
                $lines[$k]['ckoef'] = 0;
            }
            //$lines[$k]['ckoef']=$lines[$k]['s']['lat']-$koef*$lines[$k]['s']['lng'];
            //echo $point[0].'<br>';
            //echo $point[1].'<br>';
            if ($max_lat !== false && $point[0] > $max_lat) {
                $max_lat = $point[0];
            } elseif ($max_lat === false) {
                $max_lat = $point[0];
            }
            if ($min_lat !== false && $point[0] < $min_lat) {
                $min_lat = $point[0];
            } elseif ($min_lat === false) {
                $min_lat = $point[0];
            }
            if ($max_lng !== false && $point[1] > $max_lng) {
                $max_lng = $point[1];
            } elseif ($max_lng === false) {
                $max_lng = $point[1];
            }
            if ($min_lng !== false && $point[1] < $min_lng) {
                $min_lng = $point[1];
            } elseif ($min_lng === false) {
                $min_lng = $point[1];
            }
            $i++;
            if ($i == $count - 1) {
                break;
            }
        }
        $ra = [
            'max_lat' => $max_lat,
            'min_lat' => $min_lat,
            'max_lng' => $max_lng,
            'min_lng' => $min_lng
        ];

        return $ra;

    }

    private function isInRegion($point, $lines)
    {
        $point_lat = $point['lat'];
        $point_lng = $point['lng'];
        //echo 'POINT: '.$point_lat.' '.$point_lng."\n\r";

        foreach ($lines as $line) {
            if ($line['type'] == 'v' && $this->isBetween($point_lat, $line['s']['lat'], $line['e']['lat']) && $point_lng == $line['s']['lng']) {
                return true;
            } elseif ($line['type'] == 'h' && $this->isBetween($point_lng, $line['s']['lng'], $line['e']['lng']) && $point_lat == $line['s']['lat']) {
                return true;
            }
        }

        $intersectCount = 0;

        foreach ($lines as $line) {
            if ($line['type'] == 'v') {

            } elseif ($line['type'] == 'h' && $this->isBetween($point_lng, $line['s']['lng'], $line['e']['lng']) && $point_lat < $line['s']['lat']) {
                $intersectCount++;
            } else {
                //echo 'LINE: '.$line['s']['lng'].' '.$line['e']['lng']."\n\r";
                if ($this->isBetween($point_lng, $line['s']['lng'], $line['e']['lng'])) {
                    $intersect_lat = $line['koef'] * $point_lng + $line['ckoef'];
                    if ($intersect_lat >= $point_lat) {
                        $intersectCount++;
                    }
                }
            }
        }
        //echo $intersectCount;

        if ($intersectCount == 0) {
            return false;
        }
        if ($intersectCount == 1) {
            return true;
        }
        if ($intersectCount % 2 == 0) {
            return false;
        }
        return true;
    }

    private function isBetween($point, $fp1, $fp2)
    {
        $start = $fp1;
        if ($fp2 < $start) {
            $start = $fp2;
            $end = $fp1;
        } else {
            $end = $fp2;
        }
        if ($point >= $start && $point <= $end) {
            return true;
        }
        return false;
    }

    function get_sitebill_adv_geomarkers($params)
    {
        $select_fields = array();
        $return = array();

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';

        if ($this->getConfigValue('currency_enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/currency/admin/admin.php';
            $CM = new \currency_admin();
        }

        $preparedParams = $this->prepareRequestParams($params);

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


        if (isset($params['_no_interactive_search']) && 1 == (int)$params['_no_interactive_search']) {

        } else {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/template_search.php')) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/template_search.php');
                $Template_Search = new \Template_Search();
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

        $REQUESTURIPATH = \Sitebill::getClearRequestURI();
        if ($REQUESTURIPATH == 'admin' or $this->getConfigValue('allow_tags_search_frontend')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/data/data_manager.php');

            $DM = new \Data_Manager();
            $tagged_params = $DM->add_tags_params($params);
            $where_array_prepared = $DM->add_tagged_parms_to_where($where_array_prepared, $tagged_params);
        }

        if (count($where_array_prepared) > 0) {
            $where_statement_prepared = " WHERE " . implode(' AND ', $where_array_prepared);
        }


        $DBC = \DBC::getInstance();


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

    function prepareDataForGeo(&$ra, $geotpl)
    {
        global $smarty;
        $gdata = array();
        //return false;

        foreach ($ra as $k => $d) {

            if (isset($d['geo_lat']) && isset($d['geo_lng']) && $d['geo_lat'] != '' && $d['geo_lat'] != '0.000000' && $d['geo_lng'] != '' && $d['geo_lng'] != '0.000000') {
                $gdata[$k]['currency_name'] = '';
                if (isset($d['currency_name'])) {
                    $gdata[$k]['currency_name'] = \SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['currency_name']);
                }

                if (isset($d['currency_id'])) {
                    $gdata[$k]['currency_id'] = $d['currency_id'];
                }
                if ((int)$d['price'] != 0) {
                    $gdata[$k]['price'] = number_format($d['price'], 0, '.', ' ');
                } else {
                    $gdata[$k]['price'] = $d['price'];
                }
                if (isset($d['type_sh'])) {
                    $gdata[$k]['type_sh'] = \SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['type_sh']);
                }

                $address = array();
                if (isset($d['city'])) {
                    $address[] = $d['city'];
                    $gdata[$k]['city'] = \SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['city']);
                }
                if (isset($d['street'])) {
                    $address[] = $d['street'];
                    $gdata[$k]['street'] = \SiteBill::iconv(SITE_ENCODING, 'utf-8', $d['street']);
                }
                if (isset($d['number']) && $d['number'] != '' && $d['number'] != 0) {
                    $address[] = $d['number'];
                }
                if (isset($d['price'])) {
                    $address[] = $d['price'];
                }
                $gdata[$k]['topic_id'] = $d['topic_id'];

                $gdata[$k]['title'] = \SiteBill::iconv(SITE_ENCODING, 'utf-8', implode(', ', $address));

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


                $gdata[$k]['html'] = \SiteBill::iconv(SITE_ENCODING, 'utf-8', $html);
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


        if ($this->getConfigValue('apps.complex.push_map')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/complex/admin/admin.php');
            $complex_admin = new \complex_admin();
            $complex_geodata = $complex_admin->get_geodata();
            if ($complex_geodata) {
                $gdata = array_merge($gdata, $complex_geodata);
            }
        }

        if ($this->getConfigValue('apps.mapbanner.enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/mapbanner/admin/admin.php');
            $mapbanner_admin = new \mapbanner_admin();
            $mapbanner_geodata = $mapbanner_admin->get_geodata();
            if ($mapbanner_geodata) {
                $gdata = array_merge($gdata, $mapbanner_geodata);
            }
        }


        $geoobjects_collection = array();
        if (count($gdata) > 0) {
            foreach ($gdata as $gd) {
                $gc = $gd['geo_lat'] . '_' . $gd['geo_lng'];
                if (isset($geoobjects_collection[$gc])) {
                    $geoobjects_collection[$gc]['html'] .= $gd['html'];
                    if (isset($gd['banner'])) {
                        $geoobjects_collection[$gc]['banner'] = $gd['banner'];
                    } else {
                        $geoobjects_collection[$gc]['banner'] = false;
                    }
                    $geoobjects_collection[$gc]['count']++;
                    $geoobjects_collection[$gc]['ids'][] = $gd['id'];
                } else {
                    /* if($gd['topic_id']==44){
                      $geoobjects_collection[$gc]['icon']='map';
                      } */
                    $geoobjects_collection[$gc]['lat'] = $gd['geo_lat'];
                    $geoobjects_collection[$gc]['lng'] = $gd['geo_lng'];
                    if (isset($gd['banner'])) {
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

    function createMapListing($ra)
    {
        global $smarty;
        $clustered_objects = array();
        foreach ($ra as $k => $d) {
            if (isset($d['geo_lat']) && isset($d['geo_lng']) && $d['geo_lat'] != '' && $d['geo_lng'] != '') {
                $coords_string = $d['geo_lat'] . '_' . $d['geo_lng'];
                $clustered_objects[$coords_string][] = $d;
            }
        }

        $theme = $this->getConfigValue('theme');

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/mapobjectslisting.tpl')) {
            $template = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/mapobjectslisting.tpl';
        } else {
            $template = SITEBILL_DOCUMENT_ROOT . '/apps/system/template/mapobjectslisting.tpl';
        }
        $smarty->assign('mapobjects_clusters', $clustered_objects);
        return $html = $smarty->fetch($template);
    }

    protected function generateGridGeoDataOld($ra)
    {
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



}
