<?php
namespace admin3\Http\Controllers;


use api\aliases\API_common_alias;
use bridge\Http\Controllers\BaseController;

class Admin3Controller extends BaseController
{
    /**
     * @var Permission
     */
    private $permission;

    function __construct()
    {
        parent::__construct();

        //Устанавливаем этот флаг, чтобы заменять старые гриды на Angular-гриды
        \SiteBill::$replace_grid_with_angular = true;
        $this->add_apps_local_and_root_resource_paths('admin3');
        define('SITEBILL_ADMIN_BASE', SITEBILL_MAIN_URL.'/'.$this->sitebill->getConfigValue('apps.admin3.alias'));


        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill_krascap.php');
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_admin.php');
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_editor.php');

        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
        require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/permission/permission.php');
        $this->permission = new \Permission();

    }

    function init_params () {
        $params = array();
        $this->sitebill->template->assign('title', 'CMS Sitebill');

        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/login.inc.php');
        if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/sitebill_registry.php') ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/sitebill_registry.php');
        }

        $user_object_manager = new \User_Object_Manager();
        $current_user_info = $user_object_manager->load_by_id($this->sitebill->getSessionUserId());
        $this->sitebill->template->assign('current_user_info', $current_user_info);


        if ( $this->sitebill->request()->get('action') ) {
            $legacy_content = $this->legacy_admin($this->sitebill);
        }
        $all_menus = $this->get_menu();
        $apps_menu = $all_menus['apps'];
        unset($all_menus['apps']);
        $params['nested_apps'] = $all_menus['nested_apps'];
        unset($all_menus['nested_apps']);
        $params['aside_menu'] = $all_menus;
        $params['legacy_content'] = $legacy_content;

        if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/customentity/admin/admin.php')){
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/customentity/admin/admin.php');
            $params['custom_admin_entity_menu'] = \customentity_admin::getEntityList($this->sitebill->getConfigValue('apps.admin3.alias'));
        }

        $confkey = 'user.settings.'.$this->sitebill->getSessionUserId();
        $Config = \SConfig::getInstance();
        $configItem = $Config::getHiddenConfigValue($confkey);
        if(is_null($configItem)){
            $configItem = array();
        }else{
            $configItem = json_decode($configItem, true);
        }
        $params['user_config'] = $configItem;

        return $params;
    }

    function index()
    {
        $params = $this->init_params();


        if ( \SiteBill::admin3_compatible() ) {
            return $this->return_pageview('apps.admin3.resources.views.components.common.common', $params);
        } elseif ( $params['legacy_content'] ) {
            return $this->return_pageview('apps.admin3.resources.views.components.legacy.legacy', $params);
        } else {
            if (
                $this->sitebill->getConfigValue('apps.admin3.default_app') != '' and
                $this->sitebill->getConfigValue('apps.admin3.default_app') != 'data'
            ) {
                $api_common = new API_common_alias();
                $custom_object = $api_common->init_custom_model_object($this->sitebill->getConfigValue('apps.admin3.default_app'));
                $params['table_name'] = $custom_object->table_name;
                $params['primary_key'] = $custom_object->primary_key;

            }
            return $this->return_pageview('apps.admin3.resources.views.components.dashboard.dashboard', $params);
        }
    }

    function profile () {
        $params = $this->init_params();
        $params['component'] = 'profile';

        return $this->return_pageview('apps.admin3.resources.views.components.dashboard.dashboard', $params);
    }

    private function get_menu () {
        $sitebill_rent_editor = new \SiteBill_Rent_Editor();
        $am_array = $sitebill_rent_editor->getAdminMenuArray($this->sitebill->getConfigValue('apps.admin3.alias'));
        unset($am_array['datamain']);
        unset($am_array['site']);
        if ( $this->sitebill->getConfigValue('check_permissions') ) {
            $am_array = $this->permission->clear_menu_array($am_array, $this->sitebill->getSessionUserId());
        }

        return $am_array;
    }

    private function legacy_admin ($sitebill) {
        global $smarty;
        $smarty->template_dir = SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template1';
        $smarty->assign('assets_folder', SITEBILL_MAIN_URL.'/apps/admin/admin/template1');

        $action = $this->sitebill->getRequestValue('action');

        if ( !$this->permission->get_access($this->sitebill->getSessionUserId(), $action, 'access') and $action != 'logout' ) {
            return _e('Доступ запрещен');
        }


        $rs = false;
        if ( $_REQUEST['action'] == 'street' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/street/street_manager.php');
            $Street_Manager = new \Street_Manager();
            $rs = $Street_Manager->main();
        } elseif( $_REQUEST['action'] == 'apps' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
        } elseif( $_REQUEST['action'] == 'menu' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/menu/menu_manager.php');
            $Menu_Manager = new \Menu_Manager();
            $rs = $Menu_Manager->main();
        } elseif( $_REQUEST['action'] == 'country' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/country/country_manager.php');
            $Country_Manager = new \Country_Manager();
            $rs = $Country_Manager->main();
        } elseif( $_REQUEST['action'] == 'data' ) {
            if ( \SConfig::getConfigValueStatic('apps.realtypro.enable') ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/apps/realtypro/admin/admin.php');
                $realty_pro_admin = new \realtypro_admin();
                $rs = $realty_pro_admin->main();
            } else {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/data/data_manager.php');
                if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.\SConfig::getConfigValueStatic('theme').'/admin/data/data_manager.php') ) {
                    require_once (SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.\SConfig::getConfigValueStatic('theme').'/admin/data/data_manager.php');
                    $data_manager_local = new \Data_Manager_Local();
                    $rs = $data_manager_local->main();
                } else {
                    $Data_Manager = new \Data_Manager();
                    $rs = $Data_Manager->main();
                }
            }
        } elseif( $_REQUEST['action'] == 'group' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/group/group_manager.php');
            $Group_Manager = new \Group_Manager();
            $rs = $Group_Manager->main();
        } elseif( $_REQUEST['action'] == 'function' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/function/function_manager.php');
            $Function_Manager = new \Function_Manager();
            $rs = $Function_Manager->main();
        } elseif( $_REQUEST['action'] == 'component' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/component/component_manager.php');
            $Component_Manager = new \Component_Manager();
            $rs = $Component_Manager->main();
        } elseif( $_REQUEST['action'] == 'region' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/region/region_manager.php');
            $Region_Manager = new \Region_Manager();
            $rs = $Region_Manager->main();
        } elseif( $_REQUEST['action'] == 'city' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/city/city_manager.php');
            $City_Manager = new \City_Manager();
            $rs = $City_Manager->main();
        } elseif( $_REQUEST['action'] == 'metro' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/metro/metro_manager.php');
            $Metro_Manager = new \Metro_Manager();
            $rs = $Metro_Manager->main();
        } elseif( $_REQUEST['action'] == 'district' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/district/district_manager.php');
            $District_Manager = new \District_Manager();
            $rs = $District_Manager->main();
        } elseif( $_REQUEST['action'] == 'structure' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
            $Structure_Manager = new \Structure_Manager();
            $rs = $Structure_Manager->main();
        } elseif( $_REQUEST['action'] == 'structure_company' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_implements.php');
            $Structure_Manager = \Structure_Implements::getManager('company');
            $rs = $Structure_Manager->main();
        } elseif( $_REQUEST['action'] == 'structure_booking_hotel' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_implements.php');
            $Structure_Manager = \Structure_Implements::getManager('booking_hotel');
            $rs = $Structure_Manager->main();
        } elseif( $_REQUEST['action'] == 'rent_order' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/components/com_data_get_rent/sitebill_data_get_rent.php');
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/rent_order/rent_order.php');
            $rent_order = new \Rent_Order();
            $rs = $rent_order->main();
        }elseif( $_REQUEST['action'] == 'user' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
            $Users_Manager = new \User_Object_Manager();
            $rs = $Users_Manager->main();
        } elseif( $_REQUEST['action'] == 'category' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/shop_category/shop_category_manager.php');
            $Shop_Category_Manager = new \Shop_Category_Manager();
            $rs = $Shop_Category_Manager->main();
        } elseif( $_REQUEST['action'] == 'product' and file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/shop/lib/shop_product/shop_product_manager.php') ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/shop/lib/shop_product/shop_product_manager.php');
            $Shop_Product_Manager = new \Shop_Product_Manager();
            $rs = $Shop_Product_Manager->main();
        } elseif( $_REQUEST['action'] == 'shop_order' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/shop/lib/shop_order/shop_order_manager.php');
            $Shop_Order_Manager = new \Shop_Order_Manager();
            $rs = $Shop_Order_Manager->main();
        }  elseif( $_REQUEST['action'] == 'rubricator_component' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/components/rubricator/rubricator_component.php');
            $Rubricator_Component = new \Rubricator_Component();
            $rs = $Rubricator_Component->main();
        } elseif( $_REQUEST['action'] == 'logout' ) {
            //$Sitebill_User=Sitebill_User::getInstance();
            //$Sitebill_User->logoutUser();
            $sitebill->delete_session_key($_SESSION['session_key']);
            $sitebill->delete_session_key($_SESSION['key']);
            $DBC=\DBC::getInstance();
            $auth_hash=md5(rand(10000, 99999));
            $sql = 'UPDATE '.DB_PREFIX.'_user SET `auth_hash`=? WHERE `user_id`=? ';
            $stmt=$DBC->query($sql, array($auth_hash, $_SESSION['user_id_value']));

            $query = "DELETE FROM " . DB_PREFIX . "_oauth WHERE user_id=?";
            $stmt = $DBC->query($query, array((string) $_SESSION['user_id_value']));

            $query = "DELETE FROM " . DB_PREFIX . "_oauth WHERE user_id=?";
            $stmt = $DBC->query($query, array((string) $_SESSION['user_id']));

            unset($_SESSION['user_id']);
            unset($_SESSION['user_id_value']);
            unset($_SESSION['group']);
            unset($_SESSION['session_key']);
            unset($_SESSION['key']);
            unset($_SESSION['Sitebill_User']);
            unset($_SESSION['current_user_group_name']);
            unset($_SESSION['current_user_group_id']);
            unset($_SESSION['current_user_name']);
            //unset($_COOKIE['logged_user_id'])
            setcookie('logged_user_id', '', time()-$sitebill->get_cookie_duration_in_sec(), '/',\SiteBill::$_cookiedomain);
            setcookie('logged_user_token', '', time()-$sitebill->get_cookie_duration_in_sec(), '/', \SiteBill::$_cookiedomain);
            header('Location: '.SITEBILL_ADMIN_BASE);
            exit;
        } elseif( $_REQUEST['action'] == 'loginasuser' ) {
            $user_id = intval($_GET['user_id']);
            $sitebill->delete_session_key($_SESSION['session_key']);
            $DBC=\DBC::getInstance();
            $auth_hash=md5(rand(10000, 99999));
            $sql = 'UPDATE '.DB_PREFIX.'_user SET `auth_hash`=? WHERE `user_id`=? ';
            $stmt=$DBC->query($sql, array($auth_hash, $_SESSION['user_id_value']));
            unset($_SESSION['user_id']);
            unset($_SESSION['user_id_value']);
            unset($_SESSION['group']);
            unset($_SESSION['session_key']);
            unset($_SESSION['key']);
            unset($_SESSION['Sitebill_User']);
            unset($_SESSION['current_user_group_name']);
            unset($_SESSION['current_user_group_id']);
            unset($_SESSION['current_user_name']);
            //unset($_COOKIE['logged_user_id'])
            setcookie('logged_user_id', '', time()-$sitebill->get_cookie_duration_in_sec(), '/', \SiteBill::$_cookiedomain);
            setcookie('logged_user_token', '', time()-$sitebill->get_cookie_duration_in_sec(), '/', \SiteBill::$_cookiedomain);

            require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php';
            $Login = new \Login();
            $Login->makeUserLogged($user_id, 0, false, false);

            header('Location: '.SITEBILL_MAIN_URL);
            exit;
        } elseif( $_REQUEST['action'] == 'cowork' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/components/cowork/cowork.php');
            $Cowork = new \Cowork();
            $rs = $Cowork->main();
        } elseif ( $_REQUEST['action'] == 'data' ) {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/data/data_manager.php');
            if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.\SConfig::getConfigValueStatic('theme').'/admin/data/data_manager.php') ) {
                require_once (SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.\SConfig::getConfigValueStatic('theme').'/admin/data/data_manager.php');
                $data_manager_local = new \Data_Manager_Local();
                $rs = $data_manager_local->main();
            } else {
                $Data_Manager = new \Data_Manager();
                $rs = $Data_Manager->main();
            }
        } else {
            //try run apps
            try {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
                $apps_processor = new \Apps_Processor();
                $rs = $apps_processor->run($_REQUEST['action'], 'admin');
            } catch ( \Exception $e ) {
                if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/customentity/admin/admin.php')){
                    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/customentity/admin/admin.php');
                    if(\customentity_admin::checkEntity($_REQUEST['action'])){
                        $CE=new \customentity_admin();
                        $rs = $CE->main();
                    } else {
                        $rs = $e->getMessage();
                    }
                } else {
                    $rs = $e->getMessage();
                }
            }

        }

        return $rs;
    }
}
