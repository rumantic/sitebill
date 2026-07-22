<?php

/**
 * Grid constructor
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */


require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/grid_constructor_root.php');

class Grid_Constructor extends Grid_Constructor_Root
{
    use \system\lib\frontend\grid\traits\PrepareRequestParams;
    use \system\lib\frontend\grid\traits\TransformGridData;
    use \system\lib\frontend\grid\traits\GeoQuery;

    public $grid_total;
    protected $grid_item_data_model = null;
    protected $billing_mode = false;
    protected $currency_admin = null;
    private $grid_label;
    private $query_stack;

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        $data_model = new Data_Model();
        $this->grid_item_data_model = $data_model->get_kvartira_model(false, true);
        $this->grid_item_data_model = $this->grid_item_data_model['data'];
    }

    function vip_right($params)
    {
        if (!isset($params['_no_interactive_search'])) {
            $params['_no_interactive_search'] = 1;
        }
        $res = $this->get_sitebill_adv_ext($params, true, false);
        $this->template->assign('special_items2', $res);
    }

    function vip_array($params)
    {
        $params['per_page'] = 100;
        if (!isset($params['_no_interactive_search'])) {
            $params['_no_interactive_search'] = 1;
        }
        $res = $this->get_sitebill_adv_ext($params, true, false);
        return $res;
    }


    /**
     * Возвращает массив имен свойств, передаваемых запросом на получение данных для отрисовки информации об объекте поверх карты
     * @return array
     */

    function tryGetSimilarTopicsByTranslitName($topic_id)
    {
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
    function main($params)
    {

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();


        $this->template->assign('category_tree', $this->get_category_tree($params, $category_structure));

        $this->template->assign('breadcrumbs', $this->prepareBreadcrumbs($params));
        $sp = $params;
        unset($sp['page']);
        unset($sp['order']);
        $this->template->assign('search_params', json_encode($sp));
        $this->template->assign('search_url', $_SERVER['REQUEST_URI']);

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php') && $this->getConfigValue('apps.billing.enable') == 1) {
            $_billing_on = true;
            if (1 == $this->getConfigValue('apps.billing.disable_premium_popup')) {
                $_billing_on = false;
            }
        } else {
            $_billing_on = false;
        }

        if ((!isset($params['admin']) || (isset($params['admin']) && $params['admin'] != 1)) && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/front_grid_constructor.php')) {

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
            $front_grid_constructor_path = SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/front_grid_constructor.php';
            require_once($front_grid_constructor_path);

            if (1 != $this->getConfigValue('block_user_front_grids')) {

                $check_front_grid_constructor_path = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/grid/front_grid_local.php';
                if (file_exists($check_front_grid_constructor_path)) {
                    $front_grid_constructor_path = $check_front_grid_constructor_path;
                    require_once($front_grid_constructor_path);
                    $FGG = new Front_Grid_Local();
                } else {
                    $FGG = new Front_Grid_Constructor();
                }
                //$this->writeLog('$front_grid_constructor_path = ' . $front_grid_constructor_path);


                if (!is_array($params['topic_id']) && $params['topic_id'] != '' && $params['topic_id'] != 0) {
                    $topic = (array)$params['topic_id'];
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
    function main_contact($params)
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();

        $res = $this->get_sitebill_adv_ext($params);
        $res = $this->add_user_account_info($res);
        $this->template->assign('category_tree', $this->get_category_tree($params, $category_structure));
        $this->template->assign('breadcrumbs', $this->get_category_breadcrumbs($params, $category_structure, SITEBILL_MAIN_URL));

        $this->get_sales_grid($res);
    }

    function add_user_account_info($res)
    {
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
    function special($params)
    {
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
    function special_right($params)
    {
        if ($this->getConfigValue('theme') == '3columns') {
            $params['only_img'] = 1;
        }
        if (!isset($params['_no_interactive_search'])) {
            $params['_no_interactive_search'] = 1;
        }
        //@todo: надо менять алгоритм получения спец.предложений. Rand это абсолютно не приемлемый вариант, грузит сильно
        $res = $this->get_sitebill_adv_ext($params, false);
        $this->template->assign('special_items2', $res);
    }

    /**
     * Get category tree
     * @param array $params
     * @param array $category_structure
     * @return string
     */
    function get_category_tree($params, $category_structure)
    {
        if (isset($params['topic_id']) && is_array($params['topic_id'])) {
            return '';
        }
        $rs = '';
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

    function get_grid_total_records()
    {
        return $this->grid_total;
    }

    function get_sitebill_adv_ext($params, $random = false, $premium = false)
    {
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

    /**
     * Get sitebill adv ext
     * @param array $params
     * @param boolean $random
     * @return array
     */
    function get_sitebill_adv_ext_base($params, $random = false, $premium = false)
    {

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
        $this->template->assert('grid_data', $data['data']);
        $this->template->assert('grid_constructor_case', true);

        return $data['data'];
    }

    protected function get_data($params, $needle_fields = array())
    {
        $select_fields = array();
        $return = array();
    }


    function get_sitebill_adv_core($params, $random = false, $premium = false, $paging = true, $geodata = false)
    {
        //return [];
        //$this->writeLog('Call root get_sitebill_adv_core');
        // 

        $ids_only = false;
        $geo_only = false;

        if (isset($params['ids_only'])) {
            $ids_only = true;
        }

        $select_fields = array();

        if (isset($params['geo_only'])) {
            $select_fields = array(
                DB_PREFIX . '_data.id',
                DB_PREFIX . '_data.geo_lat',
                DB_PREFIX . '_data.geo_lng'
            );
            $geo_only = true;
            unset($params['geo_only']);
        }

        $routed_params = array();
        if (isset($params['routed_params'])) {
            $routed_params = $params['routed_params'];
            unset($params['routed_params']);

            foreach ($routed_params as $k => $v) {
                $params[$k] = $v;
            }
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

        if ($geo_only) {
            $_collect_user_info = false;
        }


        $this->grid_total = 0;

        $preparedParams = $this->prepareRequestParams($params, $premium);

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
            if (!defined('CURRENT_CURRENCY')) {
                define('CURRENT_CURRENCY', 'RUR');
            }
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
                $Template_Search = new Template_Search();
                $results = $Template_Search->run();

                if (isset($results['where_prepared'])) {
                    $where_array_prepared = array_merge($where_array_prepared, $results['where_prepared']);
                }
                if (isset($results['where_value_prepared'])) {
                    $where_value_prepared = array_merge($where_value_prepared, $results['where_value_prepared']);
                }

                if (isset($results['where'])) {
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

        if ( isset($_COOKIE['exclude_ids_array']) and count(json_decode($_COOKIE['exclude_ids_array'])) > 0) {
            $where_array_prepared[] = "(re_data.`id` not in (".implode(',',json_decode($_COOKIE['exclude_ids_array'], true)).") )";
        }

        // Скрываем объекты с hide_map=1 на карте для анонимных пользователей
        if ($geo_only && isset($this->grid_item_data_model['hide_map'])) {
            $current_user_id = intval($this->getSessionUserId());
            $admin_user_id = intval($this->getAdminUserId());
            if ($current_user_id != $admin_user_id) {
                if ($current_user_id > 0) {
                    $where_array_prepared[] = "(" . DB_PREFIX . "_data.hide_map IS NULL OR " . DB_PREFIX . "_data.hide_map = 0 OR " . DB_PREFIX . "_data.user_id = ?)";
                    $where_value_prepared[] = $current_user_id;
                } else {
                    $where_array_prepared[] = "(" . DB_PREFIX . "_data.hide_map IS NULL OR " . DB_PREFIX . "_data.hide_map = 0)";
                }
            }
        }

        if (count($where_array_prepared) > 0) {
            $where_statement_prepared = " WHERE " . implode(' AND ', $where_array_prepared);
        }

        $order = $this->prepareSortOrder($params, $random, $premium);


        if (!isset($params['page']) || (int)$params['page'] == 0) {
            $page = 1;
        } else {
            $page = (int)$params['page'];
        }
        $DBC = DBC::getInstance();
        if ($paging) {

            if (in_array('re_data.price as fake_reservation_amount', $select_what)) {
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/reservation/admin/admin.php');
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/reservation/site/site.php');
                $reservation_control = new \reservation_site();

                $where_statement_prepared_reservation = $where_statement_prepared." AND re_data.id in (select id from re_reservation_rate) ";

                $pre_query = $this->compile_query('re_data.id as id, '.implode(', ', $select_what), $left_joins, $where_statement_prepared_reservation, $order, '');
                $reservation_ids = [];
                $stmt = $DBC->query($pre_query, $where_value_prepared);
                $price_min = intval($this->request()->get('price_min'));
                $price_max = intval($this->request()->get('price'));
                if ($stmt) {
                    while ( $ar = $DBC->fetch($stmt) ) {
                        //if ( $ar['current_reservation_amount'] >= $price_min and  $ar['current_reservation_amount'] <= $price_max ) {
                            // выполняем запросы к API
                            $reservation_price = json_decode($reservation_control->getCost($ar['id'], $this->request()->get('start_date'), $this->request()->get('end_date')), true);
                            if ( isset($reservation_price['min_price']) and  $reservation_price['min_price'] <= $price_max) {
                                $reservation_ids[$ar['id']] = $ar['id'];
                            }
                        //}
                    }
                } else {
                    echo $DBC->getLastError().'<br>';
                }
                if ( is_array($reservation_ids) and count($reservation_ids) > 0 ) {
                    $where_statement_prepared .= ' AND re_data.id IN (' . implode(',', $reservation_ids) . ') ';
                }


            }

            $query = 'SELECT COUNT(' . DB_PREFIX . '_data.id) AS total 
            FROM ' . DB_PREFIX . '_data' . (count($left_joins) > 0 ? ' ' . implode(' ', $left_joins) . ' ' : '') . '
             ' . $where_statement_prepared;

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
        $page = (isset($params['page']) ? (int)$params['page'] : 0);

        if (isset($_REQUEST['REST_API']) && $_REQUEST['REST_API'] == 1) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.static_data.php')) {
                $static_data = Static_Data::getInstance();
                $static_data::set_param('max_page', $max_page);
            }
        }

        if (!empty($routed_params)) {
            foreach ($routed_params as $rk => $rv) {
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
                $pageurl = $this->get_myfavorites_uri();
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
        if ( $this->getConfigValue('eat_all_request_for_paging') ) {
            $pager_params = $this->eat_all_request_for_paging($pager_params);
        }

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


        if (strpos($url, 'account/data') === 0) {
            $url = preg_replace('/^(account\/data)/', '', $url);
        }

        $return['url'] = /*SITEBILL_MAIN_URL.'/'.*/
            $url;

        if (count($select_fields) == 0) {
            $select_what[] = DB_PREFIX . '_data.*';
        } else {
            $select_what = array_merge($select_what, $select_fields);
        }

        $_select_columns = implode(', ', $select_what) . ' ' . $add_select_value;
        $_limit = ((isset($params['no_portions']) && $params['no_portions'] == 1) ? '' : ' LIMIT ' . $start . ', ' . $limit);
        /*        echo '<pre>';
                print_r($where_statement_prepared);
                echo '</pre>';*/

        $query = $this->compile_query($_select_columns, $left_joins, $where_statement_prepared, $order, $_limit);
        // echo '<br>'.$query.'<br>';
        //exit;


        $query_all_ids = $this->compile_query(' id ', $left_joins, $where_statement_prepared, $order, '');

        $this->set_query_stack($query_all_ids, $where_value_prepared, 'all_ids_in_grid');

        $stmt = $DBC->query($query . '/* .. grid .. */', $where_value_prepared, $success);

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
                    $ar['company'] = @$company_profile['name']['value'];
                }
                $ar = $this->itemReducer($ar);
                if ($ids_only) {
                    $ra[$i] = $ar['id'];
                } else {
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

            // @todo: генерация геоданных выполняется ВСЕГДА! независимо от того, нужна она или нет

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
        $return['_grid_show_start'] = $start + 1;
        $return['_grid_show_end'] = (($start + $limit) > $total ? $total : ($start + $limit));


        $return['data'] = $ra;
        $return['order'] = $order;
        return $return;
    }
    protected function itemReducer ( $item )
    {
        return $item;
    }

    protected function get_myfavorites_uri()
    {
        return $this->myfavorites_uri ? $this->myfavorites_uri : 'myfavorites';
    }

    public function set_myfavorites_uri($uri)
    {
        $this->myfavorites_uri = $uri;
    }

    private function compile_query($_select_columns, $left_joins, $where_statement_prepared, $order, $limit)
    {
        $query = 'SELECT ' . $_select_columns . ' FROM ' . DB_PREFIX . '_data' . (count($left_joins) > 0 ? ' ' . implode(' ', $left_joins) . ' ' : '') . ' ' . $where_statement_prepared . ($order != '' ? ' ORDER BY ' . $order : '') . $limit;
        return $query;
    }

    function set_query_stack($query, $where_value_prepared, $query_label)
    {
        $this->query_stack[$this->get_label()] = [
            $query_label => [
                'query' => $query,
                'where_value_prepared' => $where_value_prepared
            ]
        ];
    }

    function get_query_stack($grid_label, $query_label)
    {
        if (isset($this->query_stack[$grid_label]) and isset($this->query_stack[$grid_label][$query_label])) {
            return $this->query_stack[$grid_label][$query_label];
        }
        return false;
    }


    function get_sitebill_adv_ext_base_ajax($params, $random = false, $premium = false, $paging = true, $geodata = false)
    {
        $data = $this->get_sitebill_adv_core($params, $random, $premium, true, true);
        return $data;
    }

    function getTranslitAlias($city, $street, $number)
    {
        if ($city != '') {
            $p[] = $this->transliteMe($city);
        }
        if ($street != '') {
            $p[] = $this->transliteMe($street);
        }
        if ((int)$number != 0) {
            $p[] = (int)$number;
        }
        return implode('-', $p);
    }

    /**
     * Get sales grid
     * @param array $adv res
     * @return string
     */
    function get_sales_grid($adv)
    {
        //$this->writeLog('apps/system/lib/frontend/grid/grid_constructor.php:get_sales_grid');
        global $topic_id;

        if (
            $this->getConfigValue('theme') != 'estate' and
            !file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/realty_grid.tpl') and
            !file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/resources/views/pages/realty_grid.blade.php')
        ) {
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


    protected function prepareSortOrder($params, $random = false, $premium = false)
    {

        $order = '';
        $asc = '';

        $default_sorts = $this->getConfigValue('apps.realty.sorts');
        $sorts = array();

        if ($default_sorts != '') {
            switch ($default_sorts) {
                case 'priceup' :
                {
                    $sorts[] = DB_PREFIX . '_data.price_ue ASC';
                    break;
                }
                case 'pricedown' :
                {
                    $sorts[] = DB_PREFIX . '_data.price_ue DESC';
                    break;
                }
                default :
                {
                    $matches = array();
                    preg_match_all('/([a-z0-9_]+)\|(asc|desc)[;]?/i', $default_sorts, $matches);

                    if (count($matches[0]) > 0) {
                        foreach ($matches[1] as $k => $fkey) {
                            if ($matches[2][$k] == 'asc' || $matches[2][$k] == 'desc') {
                                switch ($fkey) {
                                    case 'id' :
                                    {
                                        $sorts[] = DB_PREFIX . '_data.id ' . $matches[2][$k];
                                        break;
                                    }
                                    case 'type' :
                                    {
                                        $sorts[] = 'type_sh ' . $matches[2][$k];
                                        break;
                                    }
                                    case 'street' :
                                    {
                                        $sorts[] = 'street ' . $matches[2][$k];
                                        break;
                                    }
                                    case 'square_all' :
                                    {
                                        $sorts[] = DB_PREFIX . '_data.square_all*1 ' . $matches[2][$k];
                                        break;
                                    }
                                    case 'floor' :
                                    {
                                        $sorts[] = DB_PREFIX . '_data.floor*1 ' . $matches[2][$k];
                                        break;
                                    }
                                    case 'district' :
                                    {
                                        $sorts[] = 'district ' . $matches[2][$k];
                                        break;
                                    }
                                    case 'metro' :
                                    {
                                        $sorts[] = 'metro ' . $matches[2][$k];
                                        break;
                                    }
                                    case 'city' :
                                    {
                                        $sorts[] = 'city ' . $matches[2][$k];
                                        break;
                                    }
                                    case 'date_added' :
                                    {
                                        $sorts[] = DB_PREFIX . '_data.date_added ' . $matches[2][$k];
                                        break;
                                    }
                                    case 'date' :
                                    {
                                        $field = trim($this->getConfigValue('apps.realty.updated_at_field'));
                                        if ($field == '') {
                                            $field = 'date_added';
                                        }
                                        $sorts[] = DB_PREFIX . '_data.`' . $field . '` ' . $matches[2][$k];
                                        break;
                                    }
                                    case 'price' :
                                    {
                                        $sorts[] = 'price_ue ' . $matches[2][$k];
                                        break;
                                    }
                                    default :
                                    {
                                        $sorts[] = DB_PREFIX . '_data.`' . $fkey . '` ' . $matches[2][$k];
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
                case 'id' :
                {
                    $order = 'id ' . $asc;
                    break;
                }
                case 'type' :
                {
                    $order = 'type_sh ' . $asc;
                    //$order=DB_PREFIX.'_data.topic_id '.$asc;
                    break;
                }
                case 'street' :
                {
                    if (isset($this->grid_item_data_model['street_id'])) {
                        $order = 'street ' . $asc;
                    } else {
                        $order = $default_sorts;
                    }

                    break;
                }
                case 'square_all' :
                {
                    $order = DB_PREFIX . '_data.square_all*1 ' . $asc;
                    break;
                }
                case 'floor' :
                {
                    $order = DB_PREFIX . '_data.floor*1 ' . $asc;
                    break;
                }
                case 'district' :
                {
                    $order = 'district ' . $asc;
                    break;
                }
                case 'metro' :
                {
                    $order = 'metro ' . $asc;
                    break;
                }
                case 'city' :
                {
                    $order = 'city ' . $asc;
                    break;
                }
                case 'date_added' :
                {
                    $order = DB_PREFIX . '_data.date_added ' . $asc;
                    break;
                }
                case 'price' :
                {
                    $order = 'price_ue ' . $asc;
                    break;
                }
                case 'popular' :
                {
                    $order = DB_PREFIX . '_data.view_count ' . $asc;
                    break;
                }
                case 'priceup' :
                {
                    $order = 'price_ue ASC';
                    break;
                }
                case 'pricedown' :
                {
                    $order = 'price_ue DESC';
                    break;
                }
                case 'popularup' :
                {
                    $order = DB_PREFIX . '_data.view_count ASC';
                    break;
                }
                case 'populardown' :
                {
                    $order = DB_PREFIX . '_data.view_count DESC';
                    break;
                }
                case 'dateup' :
                {
                    $order = DB_PREFIX . '_data.date_added ASC';
                    break;
                }
                case 'datedown' :
                {
                    $order = DB_PREFIX . '_data.date_added DESC';
                    break;
                }
                default :
                {
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
                if ((int)$params['page'] == 1 || (int)$params['page'] == 0) {
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



    protected function modelReducer ( $model )
    {
        return $model;
    }

    function get_uploadify_images($_ids)
    {
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

    protected function prepareBreadcrumbs($params, $url = '')
    {
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
            $j = 0;
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

    protected function preparePageLimitParams(&$params, $page, $total, $premium)
    {

        if ($premium) {
            $limit = (int)$this->getConfigValue('apps.billing.premium_count');
            if ($limit == 0) {
                $limit = 5;
            }
        } else {
            $limit = $this->getConfigValue('per_page');
            if (intval($this->getConfigValue('per_page_admin')) > 0 && isset($params['admin']) && $params['admin'] == 1) {
                $limit = $this->getConfigValue('per_page_admin');
            }

            if (isset($params['vip']) && (int)$params['vip'] == 1) {
                if (isset($params['per_page']) && (int)$params['per_page'] > 0) {
                    $limit = (int)$params['per_page'];
                } else {
                    $limit = $this->getConfigValue('vip_rotator_number');
                }
            } else {
                if (isset($params['page_limit']) && (int)$params['page_limit'] != 0) {
                    $limit = (int)$params['page_limit'];
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



    private function checkbox_where_mutator ( $checkbox_name, $params,  $where_array_prepared) {
        if (isset($params[$checkbox_name]) && (int)$params[$checkbox_name] == 1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.'.$checkbox_name.'=1)';
        } elseif (isset($params[$checkbox_name]) && (int)$params[$checkbox_name] == -1) {
            $where_array_prepared[] = '(' . DB_PREFIX . '_data.'.$checkbox_name.' <> 1)';
        } else {
            unset($params[$checkbox_name]);
        }
        return [$params, $where_array_prepared];
    }

    public function set_label($label)
    {
        $this->grid_label = $label;
    }

    public function get_label()
    {
        return $this->grid_label;
    }

    private function fromStringToPolylineStringPairs($polylineString)
    {
        $polylineStringArray = explode(',', $polylineString);
        for ($i = 0; $i < count($polylineStringArray); $i++) {
            $polylineStringArrayPairs[] = $polylineStringArray[$i] . ',' . $polylineStringArray[++$i];
        }
        $polylineStringPairs = implode(';', $polylineStringArrayPairs);
        return $polylineStringPairs;
    }
}
