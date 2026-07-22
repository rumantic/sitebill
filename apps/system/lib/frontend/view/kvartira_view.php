<?php

use Illuminate\Support\Facades\Cache;

require_once __DIR__ . '/traits/ViewMapTrait.php';
require_once __DIR__ . '/traits/ViewPdfTrait.php';
require_once __DIR__ . '/traits/ViewDataTrait.php';
require_once __DIR__ . '/traits/ViewSimilarTrait.php';
require_once __DIR__ . '/traits/ViewSeoTrait.php';

/**
 * Kvartira view
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class Kvartira_View extends SiteBill
{
    use ViewMapTrait;
    use ViewPdfTrait;
    use ViewDataTrait;
    use ViewSimilarTrait;
    use ViewSeoTrait;

    private $city_id = 0;
    private $topic_id = 0;
    protected $realty = null;
    protected $realty_title = '';
    /**
     * @var string
     */
    private $pdffilename;

    /**
     * Constructor
     */
    function __construct()
    {
        parent::__construct();
    }

    function getCityID()
    {
        return $this->city_id;
    }

    function getTopicID()
    {
        return $this->topic_id;
    }

    function setRealty($form_data_shared)
    {
        $this->realty = $form_data_shared;
    }

    function getRealty()
    {
        return $this->realty;
    }

    /**
     * Main
     * @param int $realty_id realty id
     * @return mixed
     */
    function main($realty_id)
    {

        $result = false;


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/model/model.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');

        $Structure_Manager = new Structure_Manager();
        $category_structure = $Structure_Manager->loadCategoryStructure();
        $user_object_manager = new User_Object_Manager();

        $data_model = new Data_Model();


        //load Data model with rules
        $form_data = $data_model->get_kvartira_model(false, false);

        //load Data model full without rules
        $form_data_shared = $data_model->get_kvartira_model(false, true);

        //load User model with rules
        $form_user = $user_object_manager->get_user_model(true);

        //init Data model with rules
        $form_data = $data_model->init_model_data_from_db('data', 'id', $realty_id, $form_data['data'], true);


        $topic_id = 0;
        if (isset($form_data['topic_id'])) {
            $topic_id = intval($form_data['topic_id']['value']);
            $this->topic_id = $topic_id;
        }

        if (isset($form_data['city_id']) && intval($form_data['city_id']['value']) > 0) {
            $this->city_id = $form_data['city_id']['value'];
        }

        //init Data model full without rules
        $form_data_shared = $data_model->init_model_data_from_db('data', 'id', $realty_id, $form_data_shared['data'], true);

        $this->realty = $form_data_shared;

        if (!$form_data) {
            return $result;
        }

        if (!$this->isAccessibleObject($form_data_shared)) {
            return false;
        }

        $show_not_active = false;
        if (1 == intval($this->getConfigValue('apps.realty.allow_notactive_direct'))) {
            $show_not_active = true;
        }

        $ownermode = false;
        if (1 == intval($this->getConfigValue('apps.realty.allow_notactive_owner')) && 0 < intval($_SESSION['user_id']) && intval($_SESSION['user_id']) == $form_data_shared['user_id']['value']) {
            $ownermode = true;
        }

        if (isset($form_data_shared['active']) && $form_data_shared['active']['value'] == 0 && !$show_not_active && !$ownermode) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            $this->template->assign('main_file_tpl', 'error_message.tpl');
            return false;
        }

        if (isset($form_data_shared['active']) && $form_data_shared['active']['value'] == 0 && ($show_not_active || $ownermode)) {
            $this->template->assign('notactive_item_showed', 1);
        }

        if (1 == (int)$this->getConfigValue('apps.realty.use_predeleting') && isset($form_data_shared['archived']) && $form_data_shared['archived']['value'] == 1 && 1 == $this->getConfigValue('apps.realty.archived_notactive')) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            $this->template->assign('main_file_tpl', 'error_message.tpl');
            return false;
        }
        $result = true;

        if (isset($form_data_shared['active']) && $form_data_shared['active']['value'] == 1) {
            $this->setLastViewed($form_data_shared['id']['value']);
            $this->collectViewStat();
        }


        $form_data = $data_model->init_language_values($form_data, $form_data);
        $form_data_shared = $data_model->init_language_values($form_data_shared, $form_data_shared);

        $DBC = DBC::getInstance();


        foreach ($form_data_shared as $k => $item) {
            if ($item['type'] == 'geodata' && 1 == $this->getConfigValue('apps.geodata.enable') && 1 == $this->getConfigValue('apps.geodata.allow_view_coding')) {
                $this->geocodeField($item, $form_data_shared);
                $form_data[$k] = $form_data_shared[$k];
                break;
            }
        }

        //clearing by in topic activity
        $topic_id = 0;
        if (isset($form_data['topic_id'])) {
            $topic_id = (int)$form_data['topic_id']['value'];
        }

        if ($topic_id != 0) {
            foreach ($form_data as $key => $item_array) {

                if ($topic_id != 0 && isset($item_array['active_in_topic']) && $item_array['active_in_topic'] != 0) {
                    $active_array_ids = explode(',', $item_array['active_in_topic']);
                    $child_cats = array();
                    foreach ($active_array_ids as $item_id => $check_active_id) {
                        $child_cats_compare = $Structure_Manager->get_all_childs($check_active_id, $category_structure);
                        if (is_array($child_cats_compare)) {
                            $child_cats = array_merge($child_cats, $child_cats_compare);
                        }
                        $child_cats[] = $check_active_id;
                    }


                    if (!in_array($topic_id, $child_cats)) {
                        unset($form_data[$key]);
                        continue;
                    }
                }
            }
        }

        $CatalogChains = $Structure_Manager->createCatalogChains();
        if (isset($CatalogChains['ar'][$topic_id])) {
            $this->template->assign('data_supertopic', $CatalogChains['ar'][$topic_id][0]);
            $this->template->assign('data_topic_chain', $CatalogChains['ar'][$topic_id]);
        }

        //load user data. always available!
        if ($this->getConfigValue('apps.realtypro.show_contact.enable')) {
            $form_user = $data_model->init_model_data_from_db('user', 'user_id', $form_data_shared['user_id']['value'], $form_user['user'], true);
        } else {
            $form_user = $data_model->init_model_data_from_db('user', 'user_id', $form_data_shared['user_id']['value'], $form_user['user'], true);
        }
        $form_user = $data_model->init_language_values($form_user, $form_user);
        /*
          if($this->getConfigValue('apps.realtypro.show_contact.enable')){
          $form_user = $data_model->init_model_data_from_db ( 'user', 'user_id', $form_data['user_id']['value'], $form_user['user'], true);
          }else{
          $form_user = $data_model->init_model_data_from_db ( 'user', 'user_id', $form_data['user_id']['value'], $form_user['user'], true);
          }
         */

        if (isset($form_data['date_added']) && $form_data['date_added']['value'] != '') {
            $form_data['date_added']['value_string'] = date('d-m-Y', strtotime($form_data['date_added']['value']));
        }


        if ($this->getConfigValue('apps.company.timelimit')) {
            $current_time = time();
            $query = "select re_data.* from re_data, re_user u, re_company c where re_data.id=$realty_id and re_data.user_id=u.user_id and u.company_id=c.company_id and c.start_date <= $current_time and c.end_date >= $current_time";
            $stmt = $DBC->query($query);
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                if ($ar['id'] == '') {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
                    $this->template->assign('error_message', 'Страница не найдена. 404 not found');
                    $this->template->assign('main_file_tpl', 'error_message.tpl');
                    return false;
                }
            }
        }
        if ($this->getConfigValue('apps.company.enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/company/admin/admin.php');
            $company_admin = new company_admin();
            $company_profile = $company_admin->load_by_id($form_user['company_id']['value']);
            if ($company_profile) {
                $this->template->assign('company_profile', $company_profile);
            }
            $this->template->assign('user_company_data', $company_admin->getUserCompanyData($form_user['user_id']['value']));
        }

        if (isset($form_data['topic_id'])) {
            $form_data['topic_id']['value_string'] = $category_structure['catalog'][$form_data['topic_id']['value']]['name'];
        }

        $this->template->assert('hvd_tabbed', $this->getAutoOutputData($form_data));

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/currency/admin/admin.php';
        $CA = new currency_admin();

        if (1 == (int)$this->getConfigValue('apps.pdfreport.enabled') && isset($_GET['format']) && $_GET['format'] == 'pdf') {

        } else {
            if (method_exists($this, 'getCustomSimilarData')) {
                $this->template->assign('similar_data', $this->getCustomSimilarData($category_structure, $form_data_shared));
            } else {
                $simparams = array(
                    'id' => (int)$form_data['id']['value'],
                    'topic_id' => (int)$form_data['topic_id']['value'],
                    'city_id' => (int)$form_data['city_id']['value'],
                    'district_id' => (int)$form_data['district_id']['value'],
                    'street_id' => (int)$form_data['street_id']['value'],
                );
                if (1 == $this->getConfigValue('apps.realty.similar_grid')) {
                    $this->template->assign('similar_data_list', $this->getSimilar($category_structure, $simparams));
                } else {
                    $this->template->assign('similar_data', $this->getSimilar($category_structure, $simparams));
                }
            }
        }

        //append similar advs


        if ($form_user['login']['value'] == '_unregistered') {
            $form_user['fio']['value'] = $form_data['fio']['value'];
            $form_user['phone']['value'] = $form_data['phone']['value'];
        }

        if ($this->getConfigValue('use_google_map')) {
            $this->template->assign('map_type', 'google');
        } elseif (2 == $this->getConfigValue('use_google_map')) {
            $this->template->assign('map_type', 'leaflet_osm');
        } else {
            $this->template->assign('map_type', 'yandex');
        }

        $form_user['_href'] = $this->getUserHREF($form_user['user_id']['value']);

        $this->template->assign('admin_user_id', $this->getAdminUserId());
        $this->template->assign('current_user_id', $this->getSessionUserId());
        $this->template->assign('photo', $form_data['image']['image_array']);
        $this->template->assign('user_data', $form_user);
        //$this->template->assign('yandex_map_key', $this->getConfigValue('yandex_map_key'));
        //$this->template->assign('pmap', $this->getConfigValue('pmap'));


        $this->makeUserOperatios($form_data_shared);
        $this->appsHooks($form_data_shared);


        $hasTlocation = false;
        $tlocationElement = '';

        foreach ($form_data as $key => $val) {
            if ($val['type'] == 'tlocation') {
                //print_r($val);
                $hasTlocation = true;
                $tlocationElement = $key;
                $form_data['country_id']['value_string'] = $val['value_string']['country_id'];
                $form_data['region_id']['value_string'] = $val['value_string']['region_id'];
                $form_data['city_id']['value_string'] = $val['value_string']['city_id'];
                $form_data['district_id']['value_string'] = $val['value_string']['district_id'];
                $form_data['street_id']['value_string'] = $val['value_string']['street_id'];

                $form_data_shared['country_id']['value_string'] = $val['value_string']['country_id'];
                $form_data_shared['region_id']['value_string'] = $val['value_string']['region_id'];
                $form_data_shared['city_id']['value_string'] = $val['value_string']['city_id'];
                $form_data_shared['district_id']['value_string'] = $val['value_string']['district_id'];
                $form_data_shared['street_id']['value_string'] = $val['value_string']['street_id'];
            }
        }
        $this->template->assign('data', $form_data);
        $this->template->assign('data_shared', $form_data_shared);

        $this_adv_info = array();
        $this_adv_info['is_owned'] = 0;
        if ($_SESSION['user_id'] > 0 && $form_data_shared['user_id']['value'] == $_SESSION['user_id']) {
            $this_adv_info['_is_owned_adv'] = 1;
        } elseif ( $this->getConfigValue('enable_curator_mode') and  $this->getConfigValue('curator_mode_fullaccess') ) {
            $child_user_ids = \data\helpers\CuratorHelper::getChildUserIds($this->getSessionUserId());
            if ( is_array($child_user_ids) and in_array($form_data_shared['user_id']['value'], $child_user_ids) ) {
                $this_adv_info['_is_owned_adv'] = 1;
                $this->template->assign('curator_billing_enable', 1);
            }
        }


        $meta_data = $this->getMetaData($form_data_shared, $hasTlocation, $tlocationElement);

        $params['topic_id'] = intval($form_data_shared['topic_id']['value']);


        if (1 == $this->getConfigValue('apps.seo.country_info_in_realty_view')) {
            $this->template->assign('country_info', $this->get_country_info($form_data_shared['country_id']['value']));
        }
        if (1 == $this->getConfigValue('apps.seo.region_info_in_realty_view')) {
            $this->template->assign('region_info', $this->get_region_info($form_data_shared['region_id']['value']));
        }
        if (1 == $this->getConfigValue('apps.seo.city_info_in_realty_view')) {
            $this->template->assign('city_info', $this->get_city_info($form_data_shared['city_id']['value']));
        }
        $breadcrumbs = $this->getBreadcrumbs($params);

        $this->template->assign('realty_breadcrumbs', explode(' / ', $breadcrumbs));
        $this->template->assign('breadcrumbs', $breadcrumbs);

        $this->template->assign('title', $meta_data['title']);
        $this->template->assign('meta_title', $meta_data['meta_title']);
        $this->template->assign('meta_description', htmlentities($meta_data['meta_description'], ENT_QUOTES, 'utf-8', false));
        $this->template->assign('meta_keywords', htmlentities($meta_data['meta_keywords'], ENT_QUOTES, 'utf-8', false));


        $this->template->assign('this_adv_info', $this_adv_info);


        if (1 == $this->getConfigValue('apps.comment.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/comment/admin/admin.php';
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/comment/site/site.php';
            $CoM = new comment_site();
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/login.php');
            $Login = new Login();
            $user_id = (int)$Login->getSessionUserId();
            $commentsPanel = $CoM->generateCommentPanel($user_id, 'data', $realty_id);
        }

        /* if (1 == $this->getConfigValue('apps.reservation.enable')) {
          require_once SITEBILL_DOCUMENT_ROOT . '/apps/reservation/admin/admin.php';
          require_once SITEBILL_DOCUMENT_ROOT . '/apps/reservation/site/site.php';
          $CoM = new reservation_site();
          $reservationPanel = $CoM->getReservationPanelView($id);
          $this->template->assert('reservationPanel', $reservationPanel);
          } */

        $this->template->assert('geoobjects_collection_clustered', json_encode($this->getRealtyOnMap($form_data)));

        /*if ($this->getConfigValue('theme') != 'estate' and ! file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/realty_view.tpl')) {
            $this->template->assign('main_file_tpl', '../estate/realty_view.tpl');
        } else {*/
        $this->template->assign('main_file_tpl', 'realty_view.tpl');
        /*}*/

        if (1 == $this->getConfigValue('apps.billing.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/billing/admin/admin.php';
            $B = new billing_admin();
            $params = array();
            if (isset($form_data_shared['vip_status_end']) && intval($form_data_shared['vip_status_end']['value']) != 0 && $form_data_shared['vip_status_end']['value'] > time()) {
                $params['vip_status_end'] = $form_data_shared['vip_status_end']['value'];
            } else {
                $params['vip_status_end'] = '';
            }
            if (isset($form_data_shared['premium_status_end']) && intval($form_data_shared['premium_status_end']['value']) != 0 && $form_data_shared['premium_status_end']['value'] > time()) {
                $params['premium_status_end'] = $form_data_shared['premium_status_end']['value'];
            } else {
                $params['premium_status_end'] = '';
            }
            if (isset($form_data_shared['bold_status_end']) && intval($form_data_shared['bold_status_end']['value']) != 0 && $form_data_shared['bold_status_end']['value'] > time()) {
                $params['bold_status_end'] = $form_data_shared['bold_status_end']['value'];
            } else {
                $params['bold_status_end'] = '';
            }

            /* if ($_SESSION['user_id'] > 0 && $form_data_shared['user_id']['value'] == $_SESSION['user_id']) {
              $this_adv_info['_is_owned_adv'] = 1;
              } */
            if ($this_adv_info['_is_owned_adv'] == 1) {
                $this->template->assign('fast_billing', $B->getFastBillingOwner($realty_id, $params));
            } elseif (1 == $this->getConfigValue('apps.billing.noauth_status_set')) {
                $this->template->assign('fast_billing', $B->getFastBilling($realty_id, $params));
            }
        }

        $pdffilename = $this->getPDFFileName($form_data_shared);
        if ($pdffilename == '') {
            $pdffilename = $this->transliteMe($meta_data['title']);
        }
        $this->set_pdffilename($pdffilename);


        $this->makePDF($realty_id, $pdffilename);


        return $result;
    }

    protected function appsHooks($form_data_shared)
    {
        if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/reservation/site/site.php') ) {
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/reservation/admin/admin.php';
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/reservation/site/site.php';
            $RA = new reservation_site();
            if ( method_exists($RA, 'realtyViewHook') ) {
                $RA->realtyViewHook($form_data_shared);
            }
        }

    }
}
