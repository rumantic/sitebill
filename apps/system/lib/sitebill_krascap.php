<?php
if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/local/apps/system/lib/sitebill_krascap.php')) {
    require_once SITEBILL_DOCUMENT_ROOT . '/template/frontend/local/apps/system/lib/sitebill_krascap.php';
    return;
}

require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/krascap/KrascapUrlTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/krascap/KrascapFrontActionTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/krascap/KrascapRequestParamsTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/krascap/KrascapGridTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/krascap/KrascapMapTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/krascap/KrascapTemplateHelpersTrait.php';

/**
 * SiteBill sitebill.ru interface class
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
class SiteBill_Krascap extends SiteBill
{
    use KrascapUrlTrait;
    use KrascapFrontActionTrait;
    use KrascapRequestParamsTrait;
    use KrascapGridTrait;
    use KrascapMapTrait;
    use KrascapTemplateHelpersTrait;

    var $image_number = 5;
    public $lock_title = false;
    protected $_grid_constructor;

    //protected $currentCommand='';

    function isKernelEnable()
    {
        return false;
    }

    function getBestAgent($best_checkbox_name)
    {
        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php';
        $user_object_manager = new User_Object_Manager();
        $user_id = $user_object_manager->get_id_by_filter($best_checkbox_name, 1);
        $best_agent = $user_object_manager->load_by_id($user_id);
        if ($best_agent) {
            $this->template->assert('best_agent', $best_agent);
        }
    }


    /**
     * Constructor
     * @param void
     * @return void
     */
    function SiteBill_Krascap()
    {
        if (version_compare(phpversion(), "5.3.0", "<=")) {
            echo 'Р”Р»СЏ СЂР°Р±РѕС‚С‹ CMS Sitebill РЅРµРѕР±С…РѕРґРёРј <b>PHP 5.3</b> Рё РІС‹С€Рµ. РЎРµР№С‡Р°СЃ Сѓ РІР°СЃ СЂР°Р±РѕС‚Р°РµС‚ PHP РІРµСЂСЃРёРё ' . phpversion() . '<br>  Р’РєР»СЋС‡РёС‚Рµ, РїРѕР¶Р°Р»СѓР№СЃС‚Р°, РЅРѕРІСѓСЋ РІРµСЂСЃРёСЋ PHP С‡РµСЂРµР· РїР°РЅРµР»СЊ СѓРїСЂР°РІР»РµРЅРёСЏ С…РѕСЃС‚РёРЅРіРѕРј РёР»Рё РѕР±СЂР°С‚РёС‚РµСЃСЊ РІ С‚РµС….РїРѕРґРґРµСЂР¶РєСѓ РІР°С€РµРіРѕ С…РѕСЃС‚РёРЅРіР°.<br>РўР°РєР¶Рµ РјРѕР¶РµС‚Рµ Р·Р°РґР°С‚СЊ РІРѕРїСЂРѕСЃ РЅР° <a href="http://goo.gl/f78nzw">РЅР°С€РµРј С„РѕСЂСѓРјРµ</a>';
            exit;
        }
        parent::__construct();
        //parent::__construct();
        $this->template->assert('google_api_key', $this->getConfigValue('google_api_key'));
    }


    /**
     * Method for final operations
     */
    final function finalizer()
    {
        $Sitebill_Includer = Sitebill_Includer::getInstance();
        $Sitebill_Includer->fetch();
    }

    function load_user_stat($user_id)
    {
        $user_stat['advs_counter'] = 777;
        return $user_stat;
    }

    /**
     * Get preview image
     * @param int $record_id record ID
     * @param int $index image index
     * @return string
     */
    function getPreviewImage($record_id, $index)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT img' . $index . '_preview FROM re_data WHERE id=?';
        $stmt = $DBC->query($query, array($record_id));        //echo $query;
        $ar = $DBC->fetch($stmt);
        if ($ar['img' . $index . '_preview'] != '') {
            return '<img src="' . SITEBILL_MAIN_URL . '/img/data/' . $ar['img' . $index . '_preview'] . '" border="0">';
        }
        return false;
    }

    /**
     * Process get form
     * @param
     * @return string
     */
    function processGetRentForm()
    {
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/components/com_data_get_rent/sitebill_data_get_rent.php');
        $sitebill_data_get_rent = new Sitebill_Data_Get_Rent();
        $rs = $sitebill_data_get_rent->main();
        return $rs;
    }

    function getExtendedSearchFormParams()
    {
        $DBC = DBC::getInstance();
        $ar = array();
        $query = 'SELECT MAX(floor_count) AS max_floor_count, MAX(price) AS max_price FROM ' . DB_PREFIX . '_data WHERE active=1';
        $stmt = $DBC->query($query);
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
        }
        return $ar;
    }

    /*
     * return array of available layout of template
     */

    public function getTemplateLayouts()
    {
        return array();
    }

    /**
     * Main
     * @param void
     * @return string
     */
    function main()
    {


        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
        $REQUESTURIPATH = Sitebill::getClearRequestURI();
        global $folder;
        $this->template->assert('REQUESTURIPATH', $REQUESTURIPATH);
        $this->template->assert('estate_folder', $folder);
        Multilanguage::appendTemplateDictionary($this->getConfigValue('theme'));

        if (1 == $this->getConfigValue('is_underconstruction') and $_SESSION['user_id'] == '') {
            $access_allowed = false;
            $ip = $_SERVER['REMOTE_ADDR'];

            if ($ip != '') {
                $allowed_ips = array();

                if ('' !== trim($this->getConfigValue('is_underconstruction_allowed_ip'))) {
                    $allowed_ips = explode(',', trim($this->getConfigValue('is_underconstruction_allowed_ip')));
                }

                if (count($allowed_ips) > 0) {
                    foreach ($allowed_ips as $allowed_ip) {
                        $testing_ip = str_replace(array('*', '.'), array('(\d+)', '\.'), $allowed_ip);
                        if (preg_match('/^' . $testing_ip . '$/', $ip)) {
                            $access_allowed = true;
                            break;
                        }
                    }
                }
            }


            if (!$access_allowed) {
                header('HTTP/1.0 503 Service Unavailable');
                header('Retry-After: 3600');
                $this->template->assert('is_underconstruction_mode', '1');
                return;
            }
        }

        if (!isset($_SESSION['favorites'])) {
            $_SESSION['favorites'] = array();
        }

        //$this->runRouter($REQUESTURIPATH);

        if(1 === (int)$this->getConfigValue('apps.yandexrealty.enable')){
            $yandex_alias = trim($this->getConfigValue('apps.yandexrealty.alias'));
            $stantdart_yandex_alias = trim($this->getConfigValue('apps.yandexrealty.standart_entry_alias'));
            if ($stantdart_yandex_alias === '') {
                $stantdart_yandex_alias = 'yandexrealty';
            }
            if ($yandex_alias !== '') {

            } elseif (0 === intval($this->getConfigValue('apps.yandexrealty.disable_standart_entrypoint')) && $REQUESTURIPATH == $stantdart_yandex_alias) {
                $this->FrontAction_yandexrealty_export();
            }
        }

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/apps/apps_processor.php');
        $apps_processor = new Apps_Processor();
        $apps_processor->run_preload();

        //echo '<br><br><br>******************************************************************<br><br><br>';

        if (isset($_SESSION['theme']) && $_SESSION['theme'] != '' and $this->getConfigValue('show_demo_banners')) {
            $theme = $_SESSION['theme'];
        } else {
            $theme = $this->getConfigValue('theme');
        }

        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/main/main.php')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/apps/apps_processor_local.php');
            require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/main/main.php');
            $frontend_main = new frontend_main();
            $this->setFrontend($frontend_main);
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/bridge/bridge.xml')) {
                $kernel = new \bridge\Http\Kernel();
                $run_kernel = false;
                if (
                    $frontend_main->isKernelEnable() ||
                    ($this->getConfigValue('apps.admin3.enable') && $this->getConfigValue('apps.admin3.alias') == $REQUESTURIPATH)
                ) {
                    $run_kernel = true;
                }
                $kernel->handle(!$run_kernel);
                if ($run_kernel) {
                    exit;
                }
            }
            return $frontend_main->main();

        } else {
            if (!is_dir(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme'))) {
                echo _e('РЁР°Р±Р»РѕРЅ РЅРµ РЅР°Р№РґРµРЅ. РљР°С‚Р°Р»РѕРі РЅРµ СЃСѓС‰РµСЃС‚РІСѓРµС‚: ' . SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme'));
                exit;

            }
            global $__site_title, $folder, $smarty;
            //echo '<br><br><br>******************************************************************<br><br><br>';
            $this->setFrontend($this);


            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $Structure_Manager = new Structure_Manager();


            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/search/kvartira_search.php');
            $kvartira_search_form = new Kvartira_Search_Form();
            $kvartira_search_form->main();
            $this->template->assert('search_form_template', 'search_form.tpl');

            if ($this->getConfigValue('menu_type') == 'purecss') {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/menu/purecssmenu.php');
                $purecssmenu = new PureCSS_Menu();
                $this->template->assert('slide_menu', $purecssmenu->get_menu());
            } elseif ($this->getConfigValue('menu_type') == 'onelevel') {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/onelevelmenu/lib/onelevelmenu.php');
                $onelevel = new Onelevel_Menu();
                $this->template->assert('slide_menu', $onelevel->get_menu());
            } elseif ($this->getConfigValue('menu_type') == 'megamenu') {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/menu/megamenu.php');
                $megamenu = new Mega_Menu();
                $this->template->assert('slide_menu', $megamenu->get_menu());
            } else {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/menu/slidemenu.php');
                $slidemenu = new Slide_Menu();
                $this->template->assert('slide_menu', $slidemenu->get_menu());
            }

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
            /* require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/plugin/plugin_processor.php');
              $Plugin_Processor = new Plugin_Processor();
              $Plugin_Processor->main(); */

            $extendedSearchFormParams = $this->getExtendedSearchFormParams();
            $this->template->assert('max_floor_count', $extendedSearchFormParams['max_floor_count']);
            $this->template->assert('max_price', $extendedSearchFormParams['max_price']);


            //set default value
            $this->template->assert('base', SITEBILL_MAIN_URL);
            $this->template->assert('show_demo_banners', $this->getConfigValue('show_demo_banners'));
            $this->template->assert('REQUEST_URI', $_SERVER['REQUEST_URI']);
            $this->template->assert('type_list2', '');
            $this->template->assert('type_list3', '');
            $this->template->assert('title', $this->getConfigValue('site_title'));

            $this->template->assert('right_column', 1);

            $this->template->assert('structure_box', $Structure_Manager->getCategorySelectBoxWithName('topic_id', $this->getRequestValue('topic_id')));
            //print_r($_SESSION);

            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/login.php');
            $Login = new Login();

            if (preg_match('/\/logout/', $_SERVER['REQUEST_URI'])) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/logout.php');
                $Logout = new Logout;
                $Logout->main();
            }

            $this->template->assert('user_id', $Login->getSessionUserId());

            $this->template->assert('auth_menu', $Login->getAuthMenu());

            $this->template->assert('current_theme_name', $this->getConfigValue('theme'));

            if ($_SERVER['REQUEST_URI'] == '/') {
                if ($this->getConfigValue('theme') != 'etown') {
                    $this->grid_special();
                }
            }
            if ($this->getConfigValue('theme') != 'etown') {
                $this->grid_special_right();
            }
            if ($this->getConfigValue('theme') == 'albostar') {
                $this->template->assert('rot_banners', $this->getLast(10));
            }

            $this->template->assert('meta_keywords', '');
            $this->template->assert('meta_description', '');


            if (preg_match('/\/robox/', $_SERVER['REQUEST_URI'])) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/robokassa/robokassa.php');
                $robokassa = new Robox();
                $rs = $robokassa->main();
                if (preg_match('/result/', $_SERVER['REQUEST_URI'])) {
                    echo $rs;
                    exit;
                }
                $this->template->assert('main', $rs);
                $this->template->render();
                $rs = $this->template->toHTML();
                return $rs;
            }


            if (preg_match('/\/register/', $_SERVER['REQUEST_URI'])) {
                if (!$this->getConfigValue('allow_register_account')) {
                    $this->template->assert('main', 'Р¤СѓРЅРєС†РёСЏ СЂРµРіРёСЃС‚СЂР°С†РёРё РѕС‚РєР»СЋС‡РµРЅР° Р°РґРјРёРЅРёСЃС‚СЂР°С‚РѕСЂРѕРј');
                } else {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/register.php');
                    $Register = new Register;
                    //$smarty->assign->assert('main', $Register->main());
                    $rs1 = $Register->main();
                    $this->template->assert('main', $rs1);
                }
                $this->template->assert('hide_advelements', '1');
                $this->template->render();
                $rs = $this->template->toHTML();
                return $rs;
            }

            if (preg_match('/\/remind/', $_SERVER['REQUEST_URI'])) {
                if (!$this->getConfigValue('allow_remind_password')) {
                    $this->template->assert('main', Multilanguage::_('REMIND_PASS_OFF', 'system'));
                } else {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/user.php');
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/remind.php');
                    $remind = new Remind;
                    //$smarty->assign('main', $remind->main());
                    $this->template->assert('main', $remind->main());
                }
                $this->template->render();
                $rs = $this->template->toHTML();
                return $rs;
            }

            if (preg_match('/\/login/', $_SERVER['REQUEST_URI'])) {
                $this->template->assert('main', $Login->main());
                if ($Login->getSessionUserId() > 0) {
                    $this->template->assert('auth_menu', $Login->getAuthMenu());
                }
                $this->template->render();
                $rs = $this->template->toHTML();
                return $rs;
                //$resultString .= $this->getHomePageString();
                //return $resultString;
            }


            if ($this->getConfigValue('theme') != 'kgs') {
                if (preg_match('/^\/add(\/)*/', $_SERVER['REQUEST_URI'])) {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/form/add.php');
                    $user_add = new User_Add();

                    $this->template->assert('main', $user_add->main());
                    $this->template->render();
                    $rs = $this->template->toHTML();
                    return $rs;
                }
            }

            if (preg_match('/\/ipotekaorder\//', $_SERVER['REQUEST_URI'])) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/form/ipoteka.php');
                $ipoteka_order = new Ipoteka_Order_Form();

                $this->template->assert('main', $ipoteka_order->main());
                $this->template->render();
                $rs = $this->template->toHTML();
                return $rs;
            }

            if (preg_match('/\/goroda\//', $_SERVER['REQUEST_URI'])) {
                $city = $this->getCityListTr();
                $topic = $this->getTopicListTr();
                if (count($city) > 0 && count($topic) > 0) {
                    foreach ($city as $c) {
                        foreach ($topic as $t) {
                            $rs .= '<a href="/' . $c['translit_name'] . '-' . $t['translit_name'] . '.html">' . $c['name'] . ' (' . $t['name'] . ')</a><br />';
                        }
                    }
                }
                $this->template->assert('main', $rs);
                /* $this->template->assert('search_form', $land_front->getSearchForm());
                 */
                $this->template->render();
                $rs = $this->template->toHTML();
                return $rs;
            }

            if (preg_match('/\/contactus\//', $_SERVER['REQUEST_URI'])) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/form/contactus.php');
                $contactus_form = new contactus_Form();

                $this->template->assert('main', $contactus_form->main());
                $this->template->render();
                $rs = $this->template->toHTML();
                return $rs;
            }


            if (preg_match('/\/land\//', $_SERVER['REQUEST_URI'])) {
                require_once('lib/admin/land/land_manager.php');
                require_once('lib/frontend/land/land_front.php');
                $land_front = new Land_Front();

                $this->template->assert('main', $land_front->main());
                $this->template->assert('search_form', $land_front->getSearchForm());

                $this->template->render();
                $rs = $this->template->toHTML();
                return $rs;
            }


            if (preg_match('/\/getrent\//', $_SERVER['REQUEST_URI'])) {
                $this->template->assert('main', $this->processGetRentForm('buy'));
                $this->template->render();
                $rs = $this->template->toHTML();
                return $rs;
            }
            //require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
            //$apps_processor = new Apps_Processor();
            $apps_processor->run_frontend();
            if (count($apps_processor->get_executed_apps()) > 0) {
                $this->template->render();
                $rs = $this->template->toHTML();
                return $rs;
            }

            if (preg_match('/\/account/', $_SERVER['REQUEST_URI'])) {
                $this->template->assert('right_column', '');
                $this->template->assert('search_form_template', '');
                $this->template->assert('is_account', '1');

                //return;
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/account.php');
                $Account = new Account;

                if ($Account->get_user_id() > 0) {
                    $company_profile = $Account->get_company_profile($Account->get_user_id());
                    $this->template->assert('company', $company_profile);
                }


                $this->template->assert('breadcrumbs', $this->get_breadcrumbs(
                    array(
                        '<a href="' . $folder . '/">' . Multilanguage::_('L_HOME') . '</a>',
                        '<a href="' . $folder . '/account/">' . Multilanguage::_('PRIVATE_ACCOUNT', 'system') . '</a>'
                    )));

                if (preg_match('/profile/', $_SERVER['REQUEST_URI'])) {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/profile.php');
                    $profile = new User_Profile();
                    $this->template->assert('breadcrumbs', $this->get_breadcrumbs(
                        array(
                            '<a href="' . $folder . '/">' . Multilanguage::_('L_HOME') . '</a>',
                            '<a href="' . $folder . '/account/">' . Multilanguage::_('PRIVATE_ACCOUNT', 'system') . '</a>',
                            '<a href="' . $folder . '/account/profile/">' . Multilanguage::_('PROFILE', 'system') . '</a>'
                        )));

                    $this->template->assert('main', $profile->main());
                } elseif (preg_match('/balance/', $_SERVER['REQUEST_URI'])) {

                    $this->template->assert('breadcrumbs', $this->get_breadcrumbs(
                        array(
                            '<a href="' . $folder . '/">' . Multilanguage::_('L_HOME') . '</a>',
                            '<a href="' . $folder . '/account/">' . Multilanguage::_('PRIVATE_ACCOUNT', 'system') . '</a>',
                            '<a href="' . $folder . '/account/balance/">' . Multilanguage::_('BALANCE', 'system') . '</a>'
                        )));

                    $this->template->assert('main', $Account->main());
                } elseif (preg_match('/\/user/', $_SERVER['REQUEST_URI'])) {
                    if ($this->getConfigValue('apps.company.enable')) {
                        $this->template->assert('breadcrumbs', $this->get_breadcrumbs(
                            array(
                                '<a href="' . $folder . '/">' . Multilanguage::_('L_HOME') . '</a>',
                                '<a href="' . $folder . '/account/">' . Multilanguage::_('PRIVATE_ACCOUNT', 'system') . '</a>',
                                '<a href="' . $folder . '/account/user/">' . Multilanguage::_('REALTERS', 'system') . '</a>'
                            )));

                        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');
                        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/user/user_company_manager.php');
                        $user_company_manager = new User_Company_Manager();
                        $this->template->assert('main', $user_company_manager->frontend_main());
                    }
                } elseif (preg_match('/data/', $_SERVER['REQUEST_URI'])) {

                    $this->template->assert('breadcrumbs', $this->get_breadcrumbs(
                        array(
                            '<a href="' . $folder . '/">' . Multilanguage::_('L_HOME') . '</a>',
                            '<a href="' . $folder . '/account/">' . Multilanguage::_('PRIVATE_ACCOUNT', 'system') . '</a>',
                            '<a href="' . $folder . '/account/data/">' . Multilanguage::_('MY_ADS', 'system') . '</a>'
                        )));

                    if (preg_match('/add/', $_SERVER['REQUEST_URI'])) {
                        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/sitebill_krascap_admin.php');
                        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/sitebill_krascap_editor.php');
                        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/data/user_data.php');
                        $user_data_manager = new User_Data_Manager();
                        $this->template->assert('main', $user_data_manager->add());
                    } else {
                        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/sitebill_krascap_admin.php');
                        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/sitebill_krascap_editor.php');
                        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/data/user_data.php');
                        $user_data_manager = new User_Data_Manager();
                        $this->template->assert('main', $user_data_manager->main());
                    }
                } else {
                    $this->template->assert('breadcrumbs', $this->get_breadcrumbs(
                        array(
                            '<a href="' . $folder . '/">' . Multilanguage::_('L_HOME') . '</a>',
                            '<a href="' . $folder . '/account/">' . Multilanguage::_('PRIVATE_ACCOUNT', 'system') . '</a>'
                        )));

                    $this->template->assert('main', $Account->getHome());
                }
                $this->template->render();
                $rs = $this->template->toHTML();
                return $rs;
            }
        }

        $this->map();
        // $this->template->assert('total_map', $this->map2());
        if (1 == $this->getConfigValue('apps.seo.data_alias_enable')) {
            $requesturi = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
            $requesturi = str_replace('\\', '/', $requesturi);
            if (SITEBILL_MAIN_URL != '') {
                preg_replace('/^' . trim(SITEBILL_MAIN_URL, '/') . '/', '', $requesturi);
            }

            $url_string_parts = explode('/', $requesturi);
            if (count($url_string_parts) > 0) {
                $possible_alias = $url_string_parts[count($url_string_parts) - 1];

                $possible_alias = preg_replace('/[^A-Za-z0-9_-]/', '', urldecode($possible_alias));
                if ($possible_alias != '') {
                    $DBC = DBC::getInstance();
                    $q = 'SELECT id FROM ' . DB_PREFIX . '_data WHERE translit_alias=? LIMIT 1';
                    $stmt = $DBC->query($q, array((string)$possible_alias));
                    if ($stmt) {
                        $ar = $DBC->fetch($stmt);
                        if ((int)$ar['id'] > 0) {
                            $realty_id = (int)$ar['id'];
                            $this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
                            //require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
                            //$kvartira_view = new Kvartira_View();
                            $kvartira_view = $this->_getRealtyViewer();
                            $this->template->assert('main', $kvartira_view->main($realty_id));
                            return;
                        }
                    }
                }
            }
        }

        if (preg_match('/realty/', $_SERVER['REQUEST_URI'])) {
            if (SITEBILL_MAIN_URL != '') {
                $realty_view_regexp = '/^' . '\\' . SITEBILL_MAIN_URL . '\/realty/';
            } else {
                $realty_view_regexp = '/^\/realty/';
            }
            if (1 == $this->getConfigValue('apps.seo.level_enable') && preg_match($realty_view_regexp, $_SERVER['REQUEST_URI'])) {
                $realty_id = $this->getIDfromURI($_SERVER['REQUEST_URI']);
                //echo 'realty_id = '.$realty_id;
                if ($realty_id) {
                    $DBC = DBC::getInstance();
                    $query = 'SELECT topic_id FROM ' . DB_PREFIX . '_data WHERE id=?';
                    $stmt = $DBC->query($query, array($realty_id));
                    if ($stmt) {
                        $ar = $DBC->fetch($stmt);
                        $topic_id = $ar['topic_id'];
                        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
                        $Structure_Manager = new Structure_Manager();
                        $category_structure = $Structure_Manager->loadCategoryStructure();

                        if ($category_structure['catalog'][$topic_id]['url'] != '') {
                            $parent_category_url = $category_structure['catalog'][$topic_id]['url'] . '/';
                        } else {
                            $parent_category_url = '';
                        }

                        if (1 == $this->getConfigValue('apps.seo.html_prefix_enable')) {
                            $new_location = SITEBILL_MAIN_URL . '/' . $parent_category_url . 'realty' . $realty_id . '.html';
                        } else {
                            $new_location = SITEBILL_MAIN_URL . '/' . $parent_category_url . 'realty' . $realty_id;
                        }
                        header('HTTP/1.1 301 Moved Permanently');
                        header('Location: ' . $new_location);
                        exit();
                    }
                } else {
                    header("Status: 404 Not Found");
                    $this->template->assert('title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
                    $this->template->assert('meta_title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
                    $this->template->assert('error_message', '<h1>' . Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND') . '</h1>');
                    $this->template->assert('main_file_tpl', 'error_message.tpl');
                }
            } elseif (1 == $this->getConfigValue('apps.seo.level_enable') && !preg_match($realty_view_regexp, $_SERVER['REQUEST_URI'])) {
                $realty_id = $this->getIDfromURI($_SERVER['REQUEST_URI']);
                $this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
                //require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
                //$kvartira_view = new Kvartira_View();
                $kvartira_view = $this->_getRealtyViewer();
                $this->template->assert('main', $kvartira_view->main($realty_id));
            } elseif (0 == $this->getConfigValue('apps.seo.level_enable') && preg_match($realty_view_regexp, $_SERVER['REQUEST_URI'])) {
                $realty_id = $this->getIDfromURI($_SERVER['REQUEST_URI']);
                $this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
                //require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
                //$kvartira_view = new Kvartira_View();
                $kvartira_view = $this->_getRealtyViewer();
                $this->template->assert('main', $kvartira_view->main($realty_id));
            } else {
                header("Status: 404 Not Found");
                $this->template->assert('title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
                $this->template->assert('meta_title', Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND'));
                $this->template->assert('error_message', '<h1>' . Multilanguage::_('L_MESSAGE_PAGE_NOT_FOUND') . '</h1>');
                $this->template->assert('main_file_tpl', 'error_message.tpl');
            }
            /* $realty_id = $this->getIDfromURI($_SERVER['REQUEST_URI']);
              $this->growCounter('data', 'id', $realty_id, $this->getSessionUserId());
              require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/frontend/view/kvartira_view.php');
              $kvartira_view = new Kvartira_View();

              $this->template->assert('main', $kvartira_view->main($realty_id)); */
        } elseif ($this->getRequestValue('do') == 'buy') {
            $this->template->assert('main', $this->processAdvancedForm('buy'));
        } elseif ($this->getRequestValue('do') == 'rent') {
            $this->template->assert('main', $this->processAdvancedForm('rent'));
        } elseif ($this->getRequestValue('view') != '') {
            $this->template->assert('main', $this->getPage($this->getRequestValue('view')));
        } else {
            if ($this->getConfigValue('apps.realtypro.enable') != 1) {
                $this->template->assert('main', '<p><br></p>' . $this->grid_adv());
            }
        }
        $this->template->render();
        $rs = $this->template->toHTML();
        return $rs;
    }

    /**
     * Process advanced form
     * @param string $key key
     * @return string
     */
    function processAdvancedForm()
    {
        if ($_REQUEST['do'] == 'add_done') {
            $data = $this->initDataFromRequest();
            if ($this->checkAdvData($data)) {
                $data['active'] = 0;
                $data['street'] = $this->getStreetNameById($data['street_id']);

                $this->newAdvRecord($data);
                $rs = Multilanguage::_('L_MESSAGE_ON_MODERATION');
                return $rs;
            }
        }
        $rs = $this->getAdvForm($data, 'add_done');
        return $rs;
    }

    /**
     * Check adv data
     * @param array $data data
     * @return boolean
     */
    function checkAdvData($data)
    {
        if ($this->getRequestValue('district_id') == '' and $this->getRequestValue('new_district') == '') {
            $this->riseError(Multilanguage::_('L_ERROR_DISTRICT_NOT_SPECIFIED'));
            return false;
        }
        if ($this->getRequestValue('price') == '') {
            $this->riseError(Multilanguage::_('L_ERROR_PRICE_NOT_SPECIFIED'));
            return false;
        }
        return true;
    }
}

