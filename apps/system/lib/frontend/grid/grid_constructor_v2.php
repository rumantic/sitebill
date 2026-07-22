<?php
/**
 * Grid Constructor V2 — основной оркестратор
 *
 * Drop-in замена для Grid_Constructor.
 * Использует чистые паттерны: Builder, Pipeline, Strategy, Transformer.
 *
 * Расширяет Grid_Constructor_Root для совместимости
 * с шаблонами (template->assign) и роутингом.
 *
 * Публичный API полностью совместим с Grid_Constructor:
 *  - get_sitebill_adv_core($params, $random, $premium, $paging, $geodata)
 *  - get_sitebill_adv_ext($params, $random, $premium)
 *  - main($params), special($params), vip_right($params), etc.
 */

require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/grid_constructor_root.php';
require_once __DIR__ . '/v2/GridQueryBuilder.php';
require_once __DIR__ . '/v2/GridFilterPipeline.php';
require_once __DIR__ . '/v2/GridSorter.php';
require_once __DIR__ . '/v2/GridDataTransformer.php';

use system\lib\frontend\grid\v2\GridQueryBuilder;
use system\lib\frontend\grid\v2\GridFilterPipeline;
use system\lib\frontend\grid\v2\GridSorter;
use system\lib\frontend\grid\v2\GridDataTransformer;

class Grid_Constructor_V2 extends Grid_Constructor_Root
{
    use \system\lib\frontend\grid\traits\GeoQuery;

    /** @var int */
    public $grid_total = 0;

    /** @var array|null */
    protected $grid_item_data_model = null;

    /** @var bool */
    protected $billing_mode = false;

    /** @var object|null */
    protected $currency_admin = null;

    /** @var string */
    private $grid_label = '';

    /** @var array */
    private $query_stack = [];

    /** @var string */
    protected $myfavorites_uri;

    // ─── CONSTRUCTOR ────────────────────────────────

    public function __construct()
    {
        parent::__construct();
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php';
        $data_model = new Data_Model();
        $this->grid_item_data_model = $data_model->get_kvartira_model(false, true);
        $this->grid_item_data_model = $this->grid_item_data_model['data'];
    }

    // ─── PUBLIC API: полная совместимость ────────────

    function main($params)
    {
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php';
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();

        $this->template->assign('category_tree', $this->get_category_tree($params, $category_structure));
        $this->template->assign('breadcrumbs', $this->prepareBreadcrumbs($params));

        $sp = $params;
        unset($sp['page'], $sp['order']);
        $this->template->assign('search_params', json_encode($sp));
        $this->template->assign('search_url', $_SERVER['REQUEST_URI']);

        $billingOn = file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php')
            && $this->getConfigValue('apps.billing.enable') == 1
            && 1 != $this->getConfigValue('apps.billing.disable_premium_popup');

        if ((!isset($params['admin']) || $params['admin'] != 1) && file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/front_grid_constructor.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php';
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/front_grid_constructor.php';

            if (1 != $this->getConfigValue('block_user_front_grids')) {
                $check = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/grid/front_grid_local.php';
                if (file_exists($check)) {
                    require_once $check;
                    $FGG = new Front_Grid_Local();
                } else {
                    $FGG = new Front_Grid_Constructor();
                }

                // Topic expansion
                if (!is_array($params['topic_id']) && $params['topic_id'] != '' && $params['topic_id'] != 0) {
                    $topic = (array)$params['topic_id'];
                    if ($this->getConfigValue('theme') == 'etown' && $params['city_id'] != 0 && $params['city_id'] != '') {
                        $topic = array_merge($topic, $this->tryGetSimilarTopicsByTranslitName($params['topic_id']));
                        $params['topic_id'] = $topic;
                    }
                } elseif (is_array($params['topic_id'])) {
                    $topic = $params['topic_id'];
                }

                if ($columns_data = $FGG->grid_exists($topic)) {
                    $data_model = new Data_Model();
                    $_model = $data_model->get_kvartira_model();
                    $FGG->fullGenerate($_model, $columns_data, $params);
                } else {
                    $res = $billingOn
                        ? $this->get_sitebill_adv_ext($params, false, true)
                        : $this->get_sitebill_adv_ext($params);
                    $this->get_sales_grid($res);
                }
            } else {
                $res = $billingOn
                    ? $this->get_sitebill_adv_ext($params, false, true)
                    : $this->get_sitebill_adv_ext($params);
                $this->get_sales_grid($res);
            }
        } else {
            $res = $this->get_sitebill_adv_ext($params);
            $this->get_sales_grid($res);
        }
    }

    function main_contact($params)
    {
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php';
        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();

        $res = $this->get_sitebill_adv_ext($params);
        $res = $this->add_user_account_info($res);
        $this->template->assign('category_tree', $this->get_category_tree($params, $category_structure));
        $this->template->assign('breadcrumbs', $this->get_category_breadcrumbs($params, $category_structure, SITEBILL_MAIN_URL));
        $this->get_sales_grid($res);
    }

    function special($params)
    {
        if (!isset($params['_no_interactive_search'])) {
            $params['_no_interactive_search'] = 1;
        }
        $res = $this->get_sitebill_adv_ext($params, true);
        $this->template->assign('special_items', $res);
    }

    function special_right($params)
    {
        if ($this->getConfigValue('theme') == '3columns') {
            $params['only_img'] = 1;
        }
        if (!isset($params['_no_interactive_search'])) {
            $params['_no_interactive_search'] = 1;
        }
        $res = $this->get_sitebill_adv_ext($params, false);
        $this->template->assign('special_items2', $res);
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
        return $this->get_sitebill_adv_ext($params, true, false);
    }

    function get_sitebill_adv_ext($params, $random = false, $premium = false)
    {
        $premium_ra = [];
        if ($premium) {
            $premium_ra = $this->get_sitebill_adv_ext_base($params, $random, true);
        }

        $ra = $this->get_sitebill_adv_ext_base($params, $random);

        if (count($premium_ra) > 0) {
            $ra = array_merge($premium_ra, $ra);
        }

        return $ra;
    }

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

    function get_sitebill_adv_ext_base_ajax($params, $random = false, $premium = false, $paging = true, $geodata = false)
    {
        return $this->get_sitebill_adv_core($params, $random, $premium, true, true);
    }

    function get_grid_total_records()
    {
        return $this->grid_total;
    }

    // ─── CORE: главный метод получения данных ───────

    function get_sitebill_adv_core($params, $random = false, $premium = false, $paging = true, $geodata = false)
    {
        // ── Flags ───────────────────────────────────
        $ids_only = isset($params['ids_only']);
        $geo_only = false;
        $select_fields = [];

        if (isset($params['geo_only'])) {
            $select_fields = [
                DB_PREFIX . '_data.id',
                DB_PREFIX . '_data.geo_lat',
                DB_PREFIX . '_data.geo_lng',
            ];
            $geo_only = true;
            unset($params['geo_only']);
        }

        // Routed params
        $routed_params = [];
        if (isset($params['routed_params'])) {
            $routed_params = $params['routed_params'];
            unset($params['routed_params']);
            $params = array_merge($params, $routed_params);
        }

        // View flags
        $is_route_catch     = $this->getRequestValue('router_info');
        $is_country_view    = $this->getRequestValue('country_view');
        $is_region_view     = $this->getRequestValue('region_view');
        $is_city_view       = $this->getRequestValue('city_view');
        $is_metro_view      = $this->getRequestValue('metro_view');
        $is_district_view   = $this->getRequestValue('district_view');
        $is_complex_view    = $this->getRequestValue('complex_view');
        $is_find_view       = intval($this->getRequestValue('find_url_catched'));
        $predefined_info    = $this->getRequestValue('predefined_info');
        $is_user_view       = $this->getRequestValue('user_view');

        $this_is_favorites = !empty($params['favorites']);

        // Billing
        $this->billing_mode = file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/billing/lib/billing.php')
            && $this->getConfigValue('apps.billing.enable') == 1;

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php';

        $useCurrency = (bool)$this->getConfigValue('currency_enable');
        $priceKoeff = 1;
        if ($useCurrency) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/currency/admin/admin.php';
            $CM = new currency_admin();
            $this->currency_admin = $CM;
            if (!defined('CURRENT_CURRENCY')) {
                define('CURRENT_CURRENCY', 'RUR');
            }
            $priceKoeff = $CM->getCourse(CURRENT_CURRENCY);
        }

        $_collect_user_info = false;
        if (1 === intval($this->getConfigValue('core.listing.add_user_info'))) {
            $_collect_user_info = true;
            unset($params['_collect_user_info']);
        } elseif (isset($params['_collect_user_info']) && $params['_collect_user_info'] == 1) {
            $_collect_user_info = true;
            unset($params['_collect_user_info']);
        }
        if ($geo_only) {
            $_collect_user_info = false;
        }

        $this->grid_total = 0;

        // ── 1. BUILD QUERY via components ───────────

        $builder = new GridQueryBuilder();

        // Inject geo params from HTTP request if not already set by caller (e.g. /find?polylineString=...)
        if (!isset($params['polylineString'])) {
            $polylineString = $this->getRequestValue('polylineString');
            if ($polylineString && is_scalar($polylineString)) {
                $params['polylineString'] = $polylineString;
            }
        }
        // Parse map_bounds from URL string format "lat,lng,lat,lng" if not already a nested array
        if (!isset($params['map_bounds'])) {
            $mapBoundsStr = $this->getRequestValue('map_bounds');
            if ($mapBoundsStr && is_scalar($mapBoundsStr)) {
                $parts = array_map('floatval', explode(',', $mapBoundsStr));
                if (count($parts) === 4) {
                    $params['map_bounds'] = [[$parts[0], $parts[1]], [$parts[2], $parts[3]]];
                }
            }
        }

        // -- Filters (Pipeline)
        $pipeline = new GridFilterPipeline($builder, $this->grid_item_data_model);
        $pipeline->setParams($params)
            ->setBillingMode($this->billing_mode)
            ->setPremiumFlag($premium)
            ->setCurrency($useCurrency, $priceKoeff);

        $params = $pipeline->apply();

        // -- Currency select
        if ($useCurrency) {
            $builder->select(
                DB_PREFIX . '_currency.code AS currency_code',
                DB_PREFIX . '_currency.name AS currency_name',
                '((' . DB_PREFIX . '_data.price*' . DB_PREFIX . '_currency.course)/' . $priceKoeff . ') AS price_ue'
            );
            $builder->rawJoin(
                'LEFT JOIN ' . DB_PREFIX . '_currency ON ' . DB_PREFIX . '_data.currency_id=' . DB_PREFIX . '_currency.currency_id',
                'currency'
            );
        } else {
            $builder->select(DB_PREFIX . '_data.price AS price_ue');
        }

        // -- Template search (interactive)
        if (!isset($params['_no_interactive_search']) || (int)$params['_no_interactive_search'] != 1) {
            $tplSearchPath = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/template_search.php';
            if (file_exists($tplSearchPath)) {
                require_once $tplSearchPath;
                $Template_Search = new Template_Search();
                $results = $Template_Search->run();

                if (isset($results['where_prepared'])) {
                    $builder->whereArray($results['where_prepared']);
                }
                if (isset($results['where_value_prepared'])) {
                    $builder->whereArray([], $results['where_value_prepared']);
                }
                if (isset($results['where'])) {
                    $builder->whereArray($results['where']);
                }
                if (isset($results['params'])) {
                    $params = array_merge($params, $results['params']);
                }
            }
        }
        unset($params['_no_interactive_search']);

        // -- Tags
        $REQUESTURIPATH = Sitebill::getClearRequestURI();
        if ($REQUESTURIPATH === 'admin' || $REQUESTURIPATH === 'admin/index.php' || $this->getConfigValue('allow_tags_search_frontend')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/data/data_manager.php';
            $DM = new Data_Manager();
            $tagged_params = $DM->add_tags_params($params);
            $taggedWhere = $DM->add_tagged_parms_to_where([], $tagged_params);
            if (!empty($taggedWhere)) {
                $builder->whereArray($taggedWhere);
            }
        }

        // -- Exclude cookie
        if (isset($_COOKIE['exclude_ids_array']) && count(json_decode($_COOKIE['exclude_ids_array'])) > 0) {
            $excludeIds = json_decode($_COOKIE['exclude_ids_array'], true);
            $builder->where("(re_data.`id` NOT IN (" . implode(',', array_map('intval', $excludeIds)) . "))");
        }

        // -- hide_map filter for geo_only
        if ($geo_only && isset($this->grid_item_data_model['hide_map'])) {
            $current_user_id = intval($this->getSessionUserId());
            $admin_user_id = intval($this->getAdminUserId());
            if ($current_user_id != $admin_user_id) {
                if ($current_user_id > 0) {
                    $builder->where(
                        "(" . DB_PREFIX . "_data.hide_map IS NULL OR " . DB_PREFIX . "_data.hide_map = 0 OR " . DB_PREFIX . "_data.user_id = ?)",
                        $current_user_id
                    );
                } else {
                    $builder->where("(" . DB_PREFIX . "_data.hide_map IS NULL OR " . DB_PREFIX . "_data.hide_map = 0)");
                }
            }
        }

        // -- Sort (Strategy)
        $sorter = new GridSorter();
        $order = $sorter->resolve($params, $random, $premium);

        // Sort-required JOINs
        foreach ($sorter->getRequiredJoins($params) as $jd) {
            $builder->rawJoin(
                'LEFT JOIN ' . DB_PREFIX . '_' . $jd['table'] . ' ON ' . $jd['on'],
                $jd['table']
            );
            $builder->select($jd['select']);
        }

        // ── 2. COUNT (paging) ───────────────────────

        $page = max(1, (int)($params['page'] ?? 1));
        $total = 0;
        $DBC = DBC::getInstance();
        $return = [];

        if ($paging) {
            // Reservation special case
            if (in_array('re_data.price as fake_reservation_amount', $builder->getSelectColumns())) {
                $this->handleReservationFilter($builder, $params, $order);
            }

            $countQuery = $builder->buildCount();
            $values = $builder->getValues();
            $md5 = md5($countQuery . implode('', $values));

            $get_cache_value = false;
            if ($this->getConfigValue('query_cache_enable')) {
                $stmt = $DBC->query("SELECT `value` FROM " . DB_PREFIX . "_cache WHERE parameter = ? AND valid_for > ?", [$md5, time()]);
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ($ar) {
                        $total = $ar['value'];
                        $get_cache_value = true;
                    }
                }
            }

            if (!$get_cache_value) {
                $stmt = $DBC->query($countQuery, $values);
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $total = $ar['total'] ?? 0;
                }

                if ($this->getConfigValue('query_cache_enable')) {
                    $DBC->query(
                        "INSERT INTO " . DB_PREFIX . "_cache (`parameter`, `value`, `created_at`, `valid_for`) VALUES (?, ?, ?, ?)",
                        [$md5, $total, time(), time() + $this->getConfigValue('query_cache_time')]
                    );
                }
            }

            if ($this->getConfigValue('query_cache_enable')) {
                $DBC->query("DELETE FROM " . DB_PREFIX . "_cache WHERE `created_at`<?", [time() - $this->getConfigValue('query_cache_time')]);
            }

            $this->grid_total = $total;
        }

        // ── 3. Pagination params ────────────────────

        $pageLimitParams = $this->preparePageLimitParams($params, $page, $total, $premium);
        $start = $pageLimitParams['start'];
        $limit = $pageLimitParams['limit'];
        $max_page = $pageLimitParams['max_page'];

        $page = isset($params['page']) ? (int)$params['page'] : 0;

        // REST API
        if (isset($_REQUEST['REST_API']) && $_REQUEST['REST_API'] == 1) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/api/classes/class.static_data.php')) {
                $static_data = Static_Data::getInstance();
                $static_data::set_param('max_page', $max_page);
            }
        }

        // Restore routed
        if (!empty($routed_params)) {
            foreach ($routed_params as $rk => $rv) {
                unset($params[$rk]);
            }
        }

        $this->template->assign('grid_params', $params);

        // ── 4. Pager URL building ───────────────────

        $pager_params = $params;
        $mysearch_params = $params;
        unset($mysearch_params['page'], $mysearch_params['order'], $mysearch_params['asc'], $mysearch_params['favorites'], $mysearch_params['search'], $mysearch_params['extended_search']);
        unset($params['order'], $params['asc'], $params['favorites']);

        if (preg_match('/\/special\//', $_SERVER['REQUEST_URI'])) {
            unset($params['spec'], $pager_params['spec']);
        }

        $pageurl = $this->resolvePagerUrl($params, $pager_params, $is_route_catch, $is_country_view, $is_region_view, $is_city_view, $is_metro_view, $is_district_view, $is_complex_view, $is_find_view, $predefined_info, $is_user_view, $this_is_favorites);

        $pager_params['page_url'] = $pageurl;
        if ($this->getConfigValue('eat_all_request_for_paging')) {
            $pager_params = $this->eat_all_request_for_paging($pager_params);
        }

        if ($paging) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/page_navigator.php')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/page_navigator.php';
                $nurl = (isset($params['admin']) && $params['admin']) ? 'account/data' : $pageurl;
                $_params = $pager_params;
                unset($_params['page_url']);
                $pagingArr = Page_Navigator::getPagingArray($total, $page, $limit, $_params, $nurl);
            }
            $return['paging'] = $pagingArr ?? [];
            $return['pager'] = $this->get_page_links_list($page, $total, $limit, $pager_params);
        }

        unset($pager_params['page_url'], $pager_params['page_limit']);
        if (!isset($params['admin']) || $params['admin'] != 1) {
            unset($pager_params['topic_id']);
        }

        $return['pagerurl'] = is_array($pager_params)
            ? $pageurl . '?' . urldecode(http_build_query($pager_params))
            : $pageurl . '?key=value';

        // URL for sort links
        if ($is_country_view) {
            unset($params['country_id']);
        }
        if ($is_country_view) {
            $return['url'] = !empty($params)
                ? $is_country_view . '?' . urldecode(http_build_query($params))
                : $is_country_view . '?';
        } else {
            $return['url'] = !empty($params)
                ? $pageurl . '?' . urldecode(http_build_query($params))
                : $pageurl . '?key=value';
        }

        if (strpos($return['url'], 'account/data') === 0) {
            $return['url'] = preg_replace('/^(account\/data)/', '', $return['url']);
        }

        // ── 5. DATA QUERY ──────────────────────────

        if (empty($select_fields)) {
            $builder->select(DB_PREFIX . '_data.*');
        } else {
            $builder->select(...$select_fields);
        }

        $builder->orderBy($order);

        if (!isset($params['no_portions']) || $params['no_portions'] != 1) {
            $builder->limit($start, $limit);
        }

        $query = $builder->build();
        $values = $builder->getValues();

        // Query stack (for other modules)
        $builder->noLimit();
        // Use compile_query approach for compatibility
        $this->set_query_stack(
            $this->compileQueryFromBuilder($builder, 'id', $order, ''),
            $values,
            'all_ids_in_grid'
        );

        $stmt = $DBC->query($query . '/* .. grid v2 .. */', $values);

        $ra = [];
        if ($stmt) {
            $hasCompany = file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/company/company.xml');
            if ($hasCompany) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php';
                $Account = new Account();
            }

            while ($ar = $DBC->fetch($stmt)) {
                if ($hasCompany) {
                    $company_profile = $Account->get_company_profile($ar['user_id']);
                    $ar['company'] = @$company_profile['name']['value'];
                }
                $ar = $this->itemReducer($ar);

                if ($ids_only) {
                    $ra[] = $ar['id'];
                } else {
                    $ra[] = $ar;
                }
            }
        }

        // ── 6. Transform data ──────────────────────

        if (!empty($ra) && !$ids_only && !$geo_only) {
            $transformer = new GridDataTransformer($this->grid_item_data_model);
            $transformer->setCollectUserInfo($_collect_user_info);
            $ra = $transformer->transform($ra);
        }

        // ── 7. Geodata ─────────────────────────────

        if ($geodata && !empty($ra) && !$ids_only) {
            $geotpl = '';
            $geotplPath = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/realty_on_map.tpl';
            if (file_exists($geotplPath)) {
                $geotpl = $geotplPath;
            }

            $gdt = $this->prepareDataForGeo($ra, $geotpl);
            $return['geoobjects_collection_clustered'] = $gdt['geoobjects_collection_clustered'];
            $return['grid_geodata'] = $gdt['grid_geodata'];
        }

        // ── 8. Return ──────────────────────────────

        $return['_total_records']   = $total;
        $return['_max_page']        = $max_page;
        $return['_per_page']        = $limit;
        $return['_showed']          = count($ra);
        $return['_params']          = $params;
        $return['_mysearch_params'] = $mysearch_params;
        $return['_grid_show_start'] = $start + 1;
        $return['_grid_show_end']   = (($start + $limit) > $total ? $total : ($start + $limit));
        $return['data']             = $ra;
        $return['order']            = $order;

        return $return;
    }

    // ─── HELPER: PAGER URL ──────────────────────────

    protected function eat_all_request_for_paging($params)
    {
        $all = $this->request()->all();
        return array_merge($params, $all);
    }

    private function resolvePagerUrl(
        &$params, &$pager_params,
        $is_route_catch, $is_country_view, $is_region_view,
        $is_city_view, $is_metro_view, $is_district_view,
        $is_complex_view, $is_find_view, $predefined_info,
        $is_user_view, $this_is_favorites
    ): string {
        if (isset($params['pager_url'])) {
            $pageurl = $params['pager_url'];
            unset($params['pager_url'], $pager_params['pager_url']);
            return $pageurl;
        }

        if ($is_find_view == 1) {
            return 'find';
        }

        if ($is_country_view != '') {
            unset($pager_params['country_id']);
            return $is_country_view;
        }

        if ($is_route_catch != '') {
            foreach ($is_route_catch['params'] as $k => $v) {
                unset($pager_params[$k]);
            }
            return $is_route_catch['alias'];
        }

        if ($predefined_info != '') {
            foreach ($predefined_info['params'] as $k => $v) {
                unset($pager_params[$k]);
            }
            return $predefined_info['alias'];
        }

        if ($is_city_view) {
            unset($pager_params['city_id']);
            return $is_city_view;
        }
        if ($is_region_view) {
            unset($pager_params['region_id']);
            return $is_region_view;
        }
        if ($is_metro_view) {
            unset($pager_params['metro_id']);
            return $is_metro_view;
        }
        if ($is_district_view) {
            unset($pager_params['district_id']);
            return $is_district_view;
        }
        if ($is_complex_view != '') {
            unset($pager_params['complex_id']);
            return $is_complex_view;
        }
        if ($is_user_view) {
            unset($pager_params['user_id']);
            return $is_user_view;
        }

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php';
        $SM = new Structure_Manager();
        $cs = $SM->loadCategoryStructure();

        if ($this_is_favorites) {
            $pageurl = $this->get_myfavorites_uri();
            unset($params['favorites'], $pager_params['favorites']);
            return $pageurl;
        }

        if (isset($params['topic_id']) && !is_array($params['topic_id']) && $params['topic_id'] != '') {
            if (!isset($params['admin']) || !$params['admin']) {
                if ($this->cityTopicUrlFind($_SERVER['REQUEST_URI'])) {
                    $p = parse_url($_SERVER['REQUEST_URI']);
                    unset($params['city_id'], $params['topic_id'], $pager_params['city_id'], $pager_params['topic_id']);
                    return trim($p['path'], '/');
                }

                if (($cs['catalog'][$params['topic_id']]['url'] ?? '') != '' && 1 == $this->getConfigValue('apps.seo.level_enable')) {
                    $pageurl = $cs['catalog'][$params['topic_id']]['url'];
                    unset($params['topic_id'], $pager_params['topic_id']);
                    return $pageurl;
                }

                if (($cs['catalog'][$params['topic_id']]['url'] ?? '') != '') {
                    $pageurl = $cs['catalog'][$params['topic_id']]['url'];
                    unset($pager_params['topic_id'], $params['topic_id']);
                    return $pageurl;
                }

                if (preg_match('/topic(\d*).html/', $_SERVER['REQUEST_URI'])) {
                    unset($pager_params['topic_id']);
                }
                if ($params['topic_id'] != 0) {
                    $pageurl = 'topic' . $params['topic_id'] . '.html';
                    unset($params['topic_id']);
                    return $pageurl;
                }

                unset($params['topic_id'], $pager_params['topic_id']);
                return '';
            }
            return '';
        }

        return '';
    }

    // ─── HELPER: reservation special case ───────────

    private function handleReservationFilter(GridQueryBuilder $builder, array &$params, string $order): void
    {
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/reservation/admin/admin.php';
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/reservation/site/site.php';
        $reservation_control = new \reservation_site();

        $DBC = DBC::getInstance();

        // Build sub-query to get IDs from reservation
        $subBuilder = clone $builder;
        $subBuilder->select('re_data.id as id');
        $subBuilder->where("re_data.id IN (SELECT id FROM re_reservation_rate)");
        $subQuery = $subBuilder->build();

        $stmt = $DBC->query($subQuery, $subBuilder->getValues());
        $reservation_ids = [];
        $price_max = intval($this->request()->get('price'));

        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $reservation_price = json_decode(
                    $reservation_control->getCost($ar['id'], $this->request()->get('start_date'), $this->request()->get('end_date')),
                    true
                );
                if (isset($reservation_price['min_price']) && $reservation_price['min_price'] <= $price_max) {
                    $reservation_ids[$ar['id']] = $ar['id'];
                }
            }
        }

        if (!empty($reservation_ids)) {
            $builder->where('re_data.id IN (' . implode(',', $reservation_ids) . ')');
        }
    }

    // ─── PASSTHROUGH METHODS ────────────────────────

    protected function itemReducer($item)
    {
        return $item;
    }

    protected function modelReducer($model)
    {
        return $model;
    }

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
            }
        }
        return $rs;
    }

    function tryGetSimilarTopicsByTranslitName($topic_id)
    {
        $result = [];
        $DBC = DBC::getInstance();
        $stmt = $DBC->query("SELECT id, translit_name FROM " . DB_PREFIX . "_topic WHERE id=?", [$topic_id]);
        $translit_name = false;
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if (strlen($ar['translit_name'] ?? '') > 0) {
                $translit_name = $ar['translit_name'];
            }
        }

        if ($translit_name) {
            $stmt = $DBC->query("SELECT id, translit_name FROM " . DB_PREFIX . "_topic WHERE translit_name=?", [$translit_name]);
            if ($stmt) {
                while ($ar = $DBC->fetch($stmt)) {
                    if (strlen($ar['translit_name']) > 0 && $ar['id'] != $topic_id) {
                        $result[] = $ar['id'];
                    }
                }
            }
        }
        return $result;
    }

    function get_sales_grid($adv)
    {
        if (
            $this->getConfigValue('theme') != 'estate'
            && !file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/realty_grid.tpl')
            && !file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/resources/views/pages/realty_grid.blade.php')
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

    function get_uploadify_images($_ids)
    {
        if (empty($_ids)) return false;

        $key = 'id';
        $query = 'SELECT li.' . $key . ', i.* FROM ' . DB_PREFIX . '_data_image li LEFT JOIN ' . IMAGE_TABLE . ' i USING(image_id) WHERE li.' . $key . ' IN (' . implode(', ', $_ids) . ') ORDER BY li.sort_order ASC';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query);
        $images = [];
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

    function add_user_account_info($res)
    {
        if (!is_array($res)) return $res;
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php';
        $UM = new User_Object_Manager();
        foreach ($res as $item_id => $item) {
            $res[$item_id]['user_array'] = $UM->load_by_id($item['user_id']);
        }
        return $res;
    }

    protected function preparePageLimitParams(&$params, $page, $total, $premium)
    {
        if ($premium) {
            $limit = (int)$this->getConfigValue('apps.billing.premium_count');
            if ($limit == 0) $limit = 5;
        } else {
            $limit = $this->getConfigValue('per_page');
            if (intval($this->getConfigValue('per_page_admin')) > 0 && isset($params['admin']) && $params['admin'] == 1) {
                $limit = $this->getConfigValue('per_page_admin');
            }

            if (isset($params['vip']) && (int)$params['vip'] == 1) {
                $limit = (isset($params['per_page']) && (int)$params['per_page'] > 0)
                    ? (int)$params['per_page']
                    : $this->getConfigValue('vip_rotator_number');
            } elseif (isset($params['page_limit']) && (int)$params['page_limit'] != 0) {
                $limit = (int)$params['page_limit'];
            }
        }

        $max_page = ceil($total / $limit);
        if ($page > $max_page) {
            $page = 1;
            $params['page'] = 1;
        }
        $start = ($page - 1) * $limit;

        return ['start' => $start, 'limit' => $limit, 'max_page' => $max_page];
    }

    protected function prepareBreadcrumbs($params, $url = '')
    {
        if (Multilanguage::is_set('LT_BC_HOME_GRID', '_template')) {
            $ra[] = '<a href="' . SITEBILL_MAIN_URL . '/">' . Multilanguage::_('LT_BC_HOME_GRID', '_template') . '</a>';
        } else {
            $ra[] = '<a href="' . SITEBILL_MAIN_URL . '/">' . Multilanguage::_('L_HOME') . '</a>';
        }

        if ($url == '') $url = SITEBILL_MAIN_URL;

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php';
        $SM = new Structure_Manager();
        $cs = $SM->loadCategoryStructure();
        return $this->get_category_breadcrumbs($params, $cs, SITEBILL_MAIN_URL);
    }

    function getTranslitAlias($city, $street, $number)
    {
        $p = [];
        if ($city != '') $p[] = $this->transliteMe($city);
        if ($street != '') $p[] = $this->transliteMe($street);
        if ((int)$number != 0) $p[] = (int)$number;
        return implode('-', $p);
    }

    // ─── Query stack ────────────────────────────────

    function set_query_stack($query, $where_value_prepared, $query_label)
    {
        $this->query_stack[$this->get_label()] = [
            $query_label => [
                'query' => $query,
                'where_value_prepared' => $where_value_prepared,
            ]
        ];
    }

    function get_query_stack($grid_label, $query_label)
    {
        if (isset($this->query_stack[$grid_label][$query_label])) {
            return $this->query_stack[$grid_label][$query_label];
        }
        return false;
    }

    public function set_label($label)
    {
        $this->grid_label = $label;
    }

    public function get_label()
    {
        return $this->grid_label;
    }

    protected function get_myfavorites_uri()
    {
        return $this->myfavorites_uri ?: 'myfavorites';
    }

    public function set_myfavorites_uri($uri)
    {
        $this->myfavorites_uri = $uri;
    }

    private function fromStringToPolylineStringPairs($polylineString)
    {
        $polylineStringArray = explode(',', $polylineString);
        $polylineStringArrayPairs = [];
        for ($i = 0; $i < count($polylineStringArray); $i++) {
            $polylineStringArrayPairs[] = $polylineStringArray[$i] . ',' . $polylineStringArray[++$i];
        }
        return implode(';', $polylineStringArrayPairs);
    }

    /**
     * Helper: build a simple compile_query for query_stack compatibility
     */
    private function compileQueryFromBuilder(GridQueryBuilder $builder, string $select, string $order, string $limit): string
    {
        $sql = 'SELECT ' . $select . ' FROM ' . DB_PREFIX . '_data';
        $joins = $builder->getJoinClause();
        if ($joins) $sql .= $joins;
        $where = $builder->getWhereClause();
        if ($where) $sql .= $where;
        if ($order !== '') $sql .= ' ORDER BY ' . $order;
        if ($limit !== '') $sql .= $limit;
        return $sql;
    }
}
