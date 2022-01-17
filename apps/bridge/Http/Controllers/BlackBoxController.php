<?php
namespace bridge\Http\Controllers;

require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');

class BlackBoxController extends BaseController
{

    function index() {
        $has_result = false;

        if (!$has_result) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/apps/apps_processor.php');
            $apps_processor = new \Apps_Processor();
            $apps_processor->run_preload();

            $apps_processor->run_frontend();

            if (count($apps_processor->get_executed_apps()) > 0) {
                $work_subcontroller = 'apps';
                $has_result = true;
            }
        }

        $sitebill_krascap = new \SiteBill_Krascap();
        $REQUESTURIPATH = $sitebill_krascap::getClearRequestURI();


        if(!$has_result && $sitebill_krascap->isRealtyDetected($REQUESTURIPATH)){
            $has_result=true;
            $sitebill_krascap->grid_special_right();
            return $this->return_pageview('pages.realty_view');
        }
        if ( $has_result ) {
            return $this->return_default();
        }

        if($REQUESTURIPATH == ''){

            $returndata = [];

            if(!is_null($this->frontend) && method_exists($this->frontend, '_getTopLocations')){
                $returndata['toplocations'] = $this->frontend->_getTopLocations();
            }else{
                $returndata['toplocations'] = $this->_getTopLocations();
            }

            //
            $returndata['agents'] = $this->_getAgents();

            if(!is_null($this->frontend) && method_exists($this->frontend, '_getLatestNews')){
                $returndata['latestnews'] = $this->frontend->_getLatestNews();
            }else{
                $returndata['latestnews'] = $this->_getLatestNews();
            }

            if(!is_null($this->frontend) && method_exists($this->frontend, '_getTopComplexes')){
                $returndata['topcomplexes'] = $this->frontend->_getTopComplexes();
            }else{
                $returndata['topcomplexes'] = $this->_getTopComplexes();
            }

            if(!is_null($this->frontend) && method_exists($this->frontend, '_onHomeInit')){
                $onhomedata = $this->frontend->_onHomeInit();
                if(is_array($onhomedata) && !empty($onhomedata)){
                    foreach ($onhomedata as $k =>$v){
                        $returndata[$k] = $v;
                    }
                }
            }




            $sitebill_krascap->grid_special_right();

            $metadata = $this->_getMetadataFromPageUri('_home');

            if(!empty($metadata)){
                foreach ($metadata as $k => $v) {
                    $this->sitebill->template->assert($k, $v);
                }
            }

            return $this->return_pageview('pages.index', $returndata);
        }



        if ( !$has_result ) {
            $sitebill_krascap = new \SiteBill_Krascap();
            $sitebill_krascap->grid_special_right();
            if ($sitebill_krascap->grid_adv() === false ) {
                return $this->return_pageview('pages.error_message');
            }

            $params = array();

            $listingtype = \SiteBill::$iRequest->get('listingtype');
            if($listingtype != ''){
                setcookie('listingtype', $listingtype, time() + 31536000);
                $params['listingtype'] = $listingtype;
            }else{
                $params['listingtype'] = \SiteBill::$iRequest->cookie('listingtype');
            }
            return $this->return_pageview('pages.listing', $params);
            return $this->return_pageview('pages.listing_map', $params);
        }
        return $this->return_index();
    }

    function _getMetadataFromPageUri($code){
        $pageid = 0;
        $metadata = array();

        $DBC = \DBC::getInstance();
        $query = 'SELECT page_id FROM '.DB_PREFIX.'_page WHERE uri = ?';
        $stmt = $DBC->query($query, array($code));

        if($stmt){
            $ar = $DBC->fetch($stmt);
            $pageid = $ar['page_id'];
        }

        if($pageid != 0){
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
            $ATH = new \Admin_Table_Helper();
            $form_data = $ATH->load_model('page', true);
            $form_data = $form_data['page'];

            $DataModel = new \Data_Model();

            $form_data = $DataModel->init_model_data_from_db('page', 'page_id', $pageid, $form_data, true);
            $form_data = $DataModel->init_language_values($form_data);

            if(isset($form_data['title'])){
                $metadata['title'] = $form_data['title']['value'];
            }
            if(isset($form_data['meta_title'])){
                $metadata['meta_title'] = $form_data['meta_title']['value'];
            }
            if(isset($form_data['meta_description'])){
                $metadata['meta_description'] = $form_data['meta_description']['value'];
            }
            if(isset($form_data['meta_keywords'])){
                $metadata['meta_keywords'] = $form_data['meta_keywords']['value'];
            }

        }

        return $metadata;
    }

    function vacancy(){

        $metadata = array();

        require_once SITEBILL_DOCUMENT_ROOT.'/apps/page/admin/admin.php';
        require_once SITEBILL_DOCUMENT_ROOT.'/apps/page/site/site.php';

        $pagesite = new \page_site();

        $form_data = $pagesite->getPageByURI('_vacancy');

        if($form_data != 0){
            if(isset($form_data['title'])){
                $metadata['title'] = $form_data['title'];
            }
            if(isset($form_data['meta_title'])){
                $metadata['meta_title'] = $form_data['meta_title'];
            }
            if(isset($form_data['meta_description'])){
                $metadata['meta_description'] = $form_data['meta_description'];
            }
            if(isset($form_data['meta_keywords'])){
                $metadata['meta_keywords'] = $form_data['meta_keywords'];
            }

            $this->sitebill->template->assert('body', $form_data['body']);
        }

        if(!empty($metadata)){
            foreach ($metadata as $k => $v) {
                $this->sitebill->template->assert($k, $v);
            }
        }

        return $this->return_pageview('pages.vacancy');
    }

    function team(){

        $page = (0 < intval($_GET['page']) ? intval($_GET['page']) : 1);
        $perpage = 10;
        /*$order = trim($_GET['order']);
        switch ($o)*/

        $data = $this->getTeamUsers($page, $perpage);



        $params['users'] = $data['users'];

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/page_navigator.php')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/view/page_navigator.php';
            $paging = \Page_Navigator::getPagingArray($data['total'], $page, $perpage, array(), 'team');

            $params['paging'] = $paging;
        }

        //print_r($params['paging']);


        $metadata = $this->_getMetadataFromPageUri('_team');

        if(!empty($metadata)){
            foreach ($metadata as $k => $v) {
                $this->sitebill->template->assert($k, $v);
            }
        }

        return $this->return_pageview('pages.teamlisting', $params);
    }

    function getTeamUsers($page, $perpage){
        $ret = array(
            'users' => array(),
            'total' => 0
        );
        $ids = array();

        $DBC = \DBC::getInstance();
        $local_team_conditions = \SConfig::getConfigValue('TeamsQueryConditions');
        $query = 'SELECT COUNT(user_id) AS cnt FROM '.DB_PREFIX.'_user WHERE `active` = ? '.$local_team_conditions;
        $stmt = $DBC->query($query, array(1));
        if($stmt){
            $ar = $DBC->fetch($stmt);
            $ret['total'] = $ar['cnt'];
        }

        $query = 'SELECT user_id 
                    FROM '.DB_PREFIX.'_user 
                    WHERE `active` = ? '.$local_team_conditions.' 
                    ORDER BY user_id ASC LIMIT '.($perpage*($page-1)).', '.$perpage;
        $stmt = $DBC->query($query, array(1));
        if($stmt){
            while ($ar = $DBC->fetch($stmt)){
                $ids[] = $ar['user_id'];
            }
        }



        if(!empty($ids)){
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
            $ATH = new \Admin_Table_Helper();
            $form_data = $ATH->load_model('user', false);
            $form_data = $form_data['user'];

            $DataModel = new \Data_Model();

            $ret['users'] = $DataModel->init_model_data_from_db_multi('user', 'user_id', $ids, $form_data, true);


            foreach($ret['users'] as $id => $art){
                $ret['users'][$id] = $DataModel->init_language_values($art);
                $ret['users'][$id]['href'] = $this->sitebill->getUserHREF($art['user_id']['value']);
            }
        }





        return $ret;
    }

    /** TODO отправить эту выборку в рамки приложения ЖК и вызывать оттуда */
    function _getTopComplexes(){

        $ret = array();
        $ids = array();

        $DBC = \DBC::getInstance();
        if (intval($this->sitebill->getConfigValue('apps.complex.activity_status_enable')) == 1) {
            $query = 'SELECT complex_id FROM '.DB_PREFIX.'_complex WHERE is_special = ? AND `active` = ? LIMIT 5';
            $stmt = $DBC->query($query, array(1, 1));
        }else{
            $query = 'SELECT complex_id FROM '.DB_PREFIX.'_complex WHERE is_special = ? LIMIT 5';
            $stmt = $DBC->query($query, array(1));
        }


        if($stmt){
            while ($ar = $DBC->fetch($stmt)){
                $ids[] = $ar['complex_id'];
            }
        }

        if(!empty($ids)){
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
            $ATH = new \Admin_Table_Helper();
            $form_data = $ATH->load_model('complex', false);
            $form_data = $form_data['complex'];

            $DataModel = new \Data_Model();

            $ret = $DataModel->init_model_data_from_db_multi('complex', 'complex_id', $ids, $form_data, true);


            foreach($ret as $id => $art){
                $ret[$id] = $DataModel->init_language_values($art);
                $ret[$id]['href'] = $this->sitebill->createUrlTpl($this->sitebill->getConfigValue('apps.complex.alias').'/'.$art['url']['value']);
            }
        }



        return $ret;

    }

    function _getAgents(){

        $sitebill_krascap = new \SiteBill_Krascap();

        $items = array();
        $ids = array();

        $DBC = \DBC::getInstance();
        if ( \SConfig::getConfigValue('_getAgentsQuery') ) {
            $query = \SConfig::getConfigValue('_getAgentsQuery');
        } else {
            $query = 'SELECT user_id FROM '.DB_PREFIX.'_user LIMIT 4';
        }
        $stmt = $DBC->query($query, array(1));
        if($stmt){
            while ($ar = $DBC->fetch($stmt)){
                $ids[] = $ar['user_id'];
            }
        }

        if(!empty($ids)){
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
            $ATH = new \Admin_Table_Helper();
            $form_data = $ATH->load_model('user', false);
            $form_data = $form_data['user'];

            $DataModel = new \Data_Model();

            $items = $DataModel->init_model_data_from_db_multi('user', 'user_id', $ids, $form_data, true);


            foreach($items as $id => $art){
                $items[$id] = $DataModel->init_language_values($art);
                $items[$id]['href'] = $sitebill_krascap->getUserHREF($art['user_id']['value']);
            }
        }

        return $items;

    }

    function _getLatestNews(){
        $news = new \news_admin();
        return $news->getNewsList();
    }


    // Используется признак toplocation
    function _getTopLocations(){

        $sitebill_krascap = new \SiteBill_Krascap();

        $toplocations = array();
        $ids = array();

        $DBC = \DBC::getInstance();
        $query = 'SELECT city_id FROM '.DB_PREFIX.'_city WHERE toplocation = ? LIMIT 5';
        $stmt = $DBC->query($query, array(1));
        if($stmt){
            while ($ar = $DBC->fetch($stmt)){
                $ids[] = $ar['city_id'];
            }
        }

        if(!empty($ids)){
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
            require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
            $ATH = new \Admin_Table_Helper();
            $form_data = $ATH->load_model('city', false);
            $form_data = $form_data['city'];

            $DataModel = new \Data_Model();

            $toplocations = $DataModel->init_model_data_from_db_multi('city', 'city_id', $ids, $form_data, true);


            foreach($toplocations as $id => $art){
                $toplocations[$id] = $DataModel->init_language_values($art);
                $toplocations[$id]['href'] = $sitebill_krascap->createUrlTpl($art['url']['value']);
            }
        }

        return $toplocations;

    }

    function return_index() {
        return $this->return_pageview('pages.index');
    }

    function return_default() {
        return $this->return_pageview('pages.internal');
    }


    function userlisting ($id) {
        $sitebill_krascap = new \SiteBill_Krascap();
        $this->sitebill->setRequestValue('user_id', $id);
        /**
         * getAgentInfo вынести в базовый код с проверкой возможности показа данных ???
         */
        $agent = $this->getAgentInfo($id);
        $this->sitebill->template->assert('agent_info', $agent);
        $this->sitebill->template->assert('main', '<p><br></p>' . $sitebill_krascap->grid_adv());

        $params = array();

        $listingtype = \SiteBill::$iRequest->get('listingtype');
        if($listingtype != ''){
            setcookie('listingtype', $listingtype, time() + 31536000);
            $params['listingtype'] = $listingtype;
        }else{
            $params['listingtype'] = \SiteBill::$iRequest->cookie('listingtype');
        }

        $params['listingtype'] = 'grid';


        if(!is_null($this->frontend) && method_exists($this->frontend, '_getUserPageData')){
            $params['userpagedata'] = $this->frontend->_getUserPageData($id);
        }

        $breadcrumbs = array();
        $breadcrumbs[] = array('title' => \Multilanguage::_('L_HOME'), 'href' => $sitebill_krascap->createUrlTpl(''));
        $breadcrumbs[] = array('title' => _e('Наша команда'), 'href' => $sitebill_krascap->createUrlTpl('team'));
        $breadcrumbs[] = array('title' => $agent['fio']['value'], 'href' => '');

        if (!empty($breadcrumbs)) {
            $bc_ar = array();
            foreach ($breadcrumbs as $bc) {
                if ($bc['href'] != '') {
                    $bc_ar[] = '<a href="' . $bc['href'] . '">' . $bc['title'] . '</a>';
                } else {
                    $bc_ar[] = $bc['title'];
                }
            }
            $sitebill_krascap->template->assign('breadcrumbs', implode(' / ', $bc_ar));
        }


        return $this->return_pageview('pages.userpage', $params);
    }

    /*
     * Эту функцию перенести в SiteBill_Krascap и вызывать опосредованно через $this->frontend
     */
    private function getAgentInfo($id) {
        $DBC = \DBC::getInstance();
        $query = 'SELECT user_id FROM '.DB_PREFIX.'_user WHERE `active` = ? AND user_id = ?';
        $stmt = $DBC->query($query, array(1, $id));
        if(!$stmt){
            return false;
        }

        require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php';
        require_once SITEBILL_DOCUMENT_ROOT.'/apps/table/admin/helper.php';
        $ATH = new \Admin_Table_Helper();
        $form_data = $ATH->load_model('user', true);
        $form_data = $form_data['user'];

        $DataModel = new \Data_Model();

        $ret = $DataModel->init_model_data_from_db('user', 'user_id', $id, $form_data, true);


        $ret = $DataModel->init_language_values($ret);

        return $ret;
    }

    function ipotekaorder () {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/form/ipoteka.php');
        $ipoteka_order = new \Ipoteka_Order_Form();

        $this->sitebill->template->assert('main', $ipoteka_order->main());
        return $this->return_default();
    }

    function add () {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/form/add.php');
        $user_add = new \User_Add();

        $this->sitebill->template->assert('main', $user_add->main());
        return $this->return_default();
    }

    function account_data () {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/data/admin/admin.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/data/site/site.php');
        $data_site = new \data_site();
        $this->sitebill->template->assert('main', $data_site->main());

        return $this->return_default();
    }

    function account_profile () {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/profile.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/profile_using_model.php');
        $profile = new \User_Profile_Model();
        $folder = '';
        $this->sitebill->template->assert('breadcrumbs', $this->sitebill->get_breadcrumbs(
            array(
                '<a href="' . $folder . '/">' . \Multilanguage::_('L_HOME') . '</a>',
                '<a href="' . $folder . '/account/">Личный кабинет</a>',
                '<a href="' . $folder . '/account/profile/">Профиль</a>'
            )));

        $this->sitebill->template->assert('main', $profile->main());

        return $this->return_default();
    }


    function login () {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/login.php');
        $Login = new \Login();

        $this->sitebill->template->assert('main', $Login->main());
        return $this->return_default();
    }

    function contactus() {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/form/contactus.php');
        $contactus_form = new \contactus_Form();

        $this->sitebill->template->assert('main', $contactus_form->main());
        return $this->return_pageview('pages.contactus');
    }

    function logout () {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/logout.php');
        $Logout = new \Logout;
        $Logout->main();
    }

    function myfavorites () {
        $sitebill_krascap = new \SiteBill_Krascap();
        $this->sitebill->template->assert('main', '<p><br></p>' . $sitebill_krascap->grid_adv_favorites());
        return $this->return_default();
    }

    function robox () {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/robokassa/robokassa.php');
        $robokassa = new \Robox();
        $rs = $robokassa->main();
        if (preg_match('/result/', $_SERVER['REQUEST_URI'])) {
            echo $rs;
            exit();
        }
        $this->sitebill->template->assert('main', $rs);
        return $this->return_default();
    }

    function register () {
        if (!$this->sitebill->getConfigValue('allow_register_account')) {
            $this->sitebill->template->assert('main', 'Функция регистрации отключена администратором');
        } else {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/register_using_model.php');
            $Register = new \Register_Using_Model();
            $this->sitebill->template->assert('main', $Register->main());
        }
        return $this->return_default();
    }

    function compare () {

        $compared = array();
        $data = array();

        if(isset($_SESSION['compared']) && is_array($_SESSION['compared']) && !empty($_SESSION['compared'])){
            $compared = $_SESSION['compared'];
        }

        if(!empty($compared)){
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/grid/grid_constructor.php');
            $Grid_Constructor = new \Grid_Constructor();
            $params = array(
                'id' => $compared,
                'ids_only' => 1,
                'no_portions' => 1
            );

            $objects = $Grid_Constructor->get_sitebill_adv_core($params, false, false, false, false);
            if(!isset($objects['data']) || empty($objects['data'])){
                $compared = array();
                $_SESSION['compared'] = $compared;
            }else{
                foreach ($compared as $k => $id){
                    if(!in_array($id, $objects['data'])){
                        unset($compared[$k]);
                    }
                }
                $_SESSION['compared'] = $compared;
            }
        }

        if(!empty($compared)){
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/model/model.php');
            $data_model = new \Data_Model();
            $form_data_shared = $data_model->get_kvartira_model(false, false);
            $form_data_shared = $form_data_shared['data'];


            $rules = array();


            foreach ($form_data_shared as $item => $itemdata){
                if ($itemdata['group_id'] != 0 && $itemdata['group_id'] != '') {
                    unset($form_data_shared[$item]);
                }
            }

            foreach ($form_data_shared as $item => $itemdata){
                if (isset($itemdata['active_in_topic']) && $itemdata['active_in_topic'] != 0) {
                    $active_array_ids = explode(',', $itemdata['active_in_topic']);
                    $rules[$item] = $active_array_ids;
                }
            }

            $data = $data_model->init_model_data_from_db_multi('data', 'id', $compared, $form_data_shared, true, true, true);

            if(!empty($data)){
                foreach ($data as $di => $oneobj) {
                    $topic_id = $oneobj['topic_id']['value'];
                    foreach($oneobj as $key => $item_array){
                        if(isset($rules[$key]) && !in_array($topic_id, $rules[$key])){
                            unset($data[$di][$key]);
                        }
                    }
                }
            }
        }

        $title = \Multilanguage::_('Сравнить');

        $this->sitebill->template->assert('title', $title);

        return $this->return_pageview('pages.compare', ['compared' => $data]);
    }

    function about () {
        $metadata = $this->_getMetadataFromPageUri('_about');

        if(!empty($metadata)){
            foreach ($metadata as $k => $v) {
                $this->sitebill->template->assert($k, $v);
            }
        }
        return $this->return_pageview('pages.about');
    }

    function partners () {
        $metadata = $this->_getMetadataFromPageUri('_partners');

        if(!empty($metadata)){
            foreach ($metadata as $k => $v) {
                $this->sitebill->template->assert($k, $v);
            }
        }
        return $this->return_pageview('pages.partners');
    }

    function citizenship () {

        $metadata = array();

        require_once SITEBILL_DOCUMENT_ROOT.'/apps/page/admin/admin.php';
        require_once SITEBILL_DOCUMENT_ROOT.'/apps/page/site/site.php';

        $pagesite = new \page_site();

        $form_data = $pagesite->getPageByURI('_citizenship');

        if($form_data != 0){
            if(isset($form_data['title'])){
                $metadata['title'] = $form_data['title'];
            }
            if(isset($form_data['meta_title'])){
                $metadata['meta_title'] = $form_data['meta_title'];
            }
            if(isset($form_data['meta_description'])){
                $metadata['meta_description'] = $form_data['meta_description'];
            }
            if(isset($form_data['meta_keywords'])){
                $metadata['meta_keywords'] = $form_data['meta_keywords'];
            }

            $this->sitebill->template->assert('body', $form_data['body']);
        }

        if(!empty($metadata)){
            foreach ($metadata as $k => $v) {
                $this->sitebill->template->assert($k, $v);
            }
        }

        return $this->return_pageview('pages.citizenship');
    }

    function remind () {
        if (!$this->sitebill->getConfigValue('allow_remind_password')) {
            $this->sitebill->template->assert('main', 'Функция напоминания пароля отключена администратором');
        } else {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/user.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/remind.php');
            $remind = new \Remind;
            $this->sitebill->template->assert('main', $remind->main());
        }
        return $this->return_default();
    }

    function map_full_screen () {
        $sitebill_krascap = new \SiteBill_Krascap();
        $this->sitebill->template->assert('data', $sitebill_krascap->map(true));
        return $this->return_default();
    }

    function map () {
        $sitebill_krascap = new \SiteBill_Krascap();
        $this->sitebill->template->assert('main', '<p><br></p>' . $sitebill_krascap->map());
        return $this->return_default();
    }

    function client_order_entity ($entity) {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/client/admin/admin.php');
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/client/site/site.php');
        $client_site = new \client_site();
        $client_site->frontend();

        if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/laraway/resources/views/pages/'.$entity.'.blade.php') )  {
            return $this->return_pageview('pages.'.$entity);
        } else {
            return $this->return_default();
        }

    }
}
