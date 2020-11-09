<?php
defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');

require_once(SITEBILL_DOCUMENT_ROOT.'/third/smarty/Smarty.class.php');
$smarty = new Smarty;
$smarty->template_dir = SITEBILL_DOCUMENT_ROOT.'/apps/admin/admin/template1';
$smarty->cache_dir    = SITEBILL_DOCUMENT_ROOT.'/cache/smarty';
$smarty->compile_dir  = SITEBILL_DOCUMENT_ROOT.'/cache/compile';

$smarty->assign('SITEBILL_DOCUMENT_ROOT', SITEBILL_DOCUMENT_ROOT);
$smarty->assign('ADMIN_BASE', SITEBILL_ADMIN_BASE);
$smarty->assign('MAIN_URL', SITEBILL_MAIN_URL);
$smarty->assign('estate_folder', SITEBILL_MAIN_URL);
$smarty->assign('estate_folder_control', SITEBILL_MAIN_URL.'/admin/');
$smarty->assign('assets_folder', SITEBILL_MAIN_URL.'/apps/admin/admin/template1');
if(!defined('ADMIN_MODE')){
	define('ADMIN_MODE',1);
}
//require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/multilanguage/multilanguage.class.php';
//	    $ML=Multilanguage::getInstance('backend','en');
//$ML::text('L_LAST_FIRST_MIDDLE');
//	    $ML::assign($smarty);

require_once(SITEBILL_DOCUMENT_ROOT."/inc/db.inc.php");
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/db/MySQL.php');
require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/multilanguage/multilanguage.class.php';
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill.php');

Sitebill::setLangSession();
Multilanguage::start('backend', $_SESSION['_lang']);

if ( $_SESSION['need_reload_words'] ) {
    if ( method_exists('Multilanguage', 'reLoadWords') ) {
        Multilanguage::reLoadWords();
        $_SESSION['need_reload_words'] = false;
    }
}


if(file_exists(SITEBILL_DOCUMENT_ROOT.'/inc/db.inc.php') && file_exists(SITEBILL_DOCUMENT_ROOT.'/install')){
	$msgs=array();
	Sitebill::removeDirectory(SITEBILL_DOCUMENT_ROOT.'/install', $msgs);
	if(count($msg)>0){
		foreach($msgs as $msg){
			echo $msg.'<br/>';
		}
	}
}
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/login.inc.php');
if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/sitebill_registry.php') ) {
	require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/sitebill_registry.php');
}
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/object_manager.php');
$sitebill = new SiteBill();



function appendAppToRecently(){
	$action=$_REQUEST['action'];
	if(!isset($_SESSION['_apps_memory']['list'])){
		require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
		$apps_processor = new Apps_Processor();
		$_SESSION['_apps_memory']['list']=$apps_processor->load_apps_menu();
	}

	if($action != '' && $action != 'data'){
		if(isset($_SESSION['_apps_memory']['list'][$action])){
			$app_name='<a href="'.SITEBILL_MAIN_URL.'/admin/?action='.$action.'">'.$_SESSION['_apps_memory']['list'][$action]['title'].'</a>';
		}else{
			$app_name='<a href="'.SITEBILL_MAIN_URL.'/admin/?action='.$action.'">'.Multilanguage::_('L_APPLICATION').' "'.$action.'"</a>';
		}
	}else{
		//$app_name='Редактор объявлений';
		$app_name='';
	}
	if($app_name!='' && $_SESSION['recently_apps'][0]!=$app_name){
		if(!isset($_SESSION['recently_apps'])){
			$_SESSION['recently_apps'][]=$app_name;
		}else{
			array_unshift($_SESSION['recently_apps'],$app_name);
		}

	}
	if (isset($_SESSION['recently_apps']) && is_array($_SESSION['recently_apps'])) {
	    $_SESSION['recently_apps'] = array_unique($_SESSION['recently_apps']);
	}
}





$smarty->assign('show_admin_helper', $sitebill->getConfigValue('show_admin_helper'));
$smarty->assign('g_api_key', trim($sitebill->getConfigValue('google_api_key')));
appendAppToRecently();




if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme').'/admin/index.php') ) {
	include_once(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme').'/admin/index.php');
} else {

    $access_allow=true;

    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/sitebill_krascap.php');
    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_admin.php');
    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/sitebill_krascap_editor.php');

    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
    require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/permission/permission.php');
    $permission = new Permission();





    if ( $sitebill->getConfigValue('check_permissions') ) {
        $action = $sitebill->getRequestValue('action');
        if ( $action == '' ) {
            $action = 'data';
        }

        if ( !$permission->get_access($_SESSION['user_id_value'], $action, 'access') and $action != 'logout' ) {
            $access_allow=false;
            /*$smarty->assign('content', 'Доступ запрещен');
            $smarty->display("main.tpl");
            exit;*/
            //continue;
        }

    }

    if($access_allow){
        if ( $_REQUEST['action'] != ''  ) {
            if ( $_REQUEST['action'] == 'street' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/street/street_manager.php');
                $Street_Manager = new Street_Manager();
                $rs = $Street_Manager->main();
            } elseif( $_REQUEST['action'] == 'apps' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
                $Apps_Processor = new Apps_Processor();
                $rs = $Apps_Processor->getAppsList();
                //$rs = $Apps_Processor->load_apps_list_from_location();
            } elseif( $_REQUEST['action'] == 'menu' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/menu/menu_manager.php');
                $Menu_Manager = new Menu_Manager();
                $rs = $Menu_Manager->main();
            } elseif( $_REQUEST['action'] == 'country' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/country/country_manager.php');
                $Country_Manager = new Country_Manager();
                $rs = $Country_Manager->main();
            } elseif( $_REQUEST['action'] == 'data' ) {
                if ( $sitebill->getConfigValue('apps.realtypro.enable') ) {
                    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/apps/realtypro/admin/admin.php');
                    $realty_pro_admin = new realtypro_admin();
                    $rs = $realty_pro_admin->main();
                } else {
                    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/data/data_manager.php');
                    if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme').'/admin/data/data_manager.php') ) {
                        require_once (SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme').'/admin/data/data_manager.php');
                        $data_manager_local = new Data_Manager_Local();
                        $rs = $data_manager_local->main();
                    } else {
                        $Data_Manager = new Data_Manager();
                        $rs = $Data_Manager->main();
                    }
                }
            } elseif( $_REQUEST['action'] == 'group' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/group/group_manager.php');
                $Group_Manager = new Group_Manager();
                $rs = $Group_Manager->main();
            } elseif( $_REQUEST['action'] == 'function' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/function/function_manager.php');
                $Function_Manager = new Function_Manager();
                $rs = $Function_Manager->main();
            } elseif( $_REQUEST['action'] == 'component' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/component/component_manager.php');
                $Component_Manager = new Component_Manager();
                $rs = $Component_Manager->main();
            } elseif( $_REQUEST['action'] == 'region' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/region/region_manager.php');
                $Region_Manager = new Region_Manager();
                $rs = $Region_Manager->main();
            } elseif( $_REQUEST['action'] == 'city' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/city/city_manager.php');
                $City_Manager = new City_Manager();
                $rs = $City_Manager->main();
            } elseif( $_REQUEST['action'] == 'metro' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/metro/metro_manager.php');
                $Metro_Manager = new Metro_Manager();
                $rs = $Metro_Manager->main();
            } elseif( $_REQUEST['action'] == 'district' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/district/district_manager.php');
                $District_Manager = new District_Manager();
                $rs = $District_Manager->main();
            } elseif( $_REQUEST['action'] == 'structure' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_manager.php');
                $Structure_Manager = new Structure_Manager();
                $rs = $Structure_Manager->main();
            } elseif( $_REQUEST['action'] == 'structure_company' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_implements.php');
                $Structure_Manager = Structure_Implements::getManager('company');
                $rs = $Structure_Manager->main();
            } elseif( $_REQUEST['action'] == 'structure_booking_hotel' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/structure/structure_implements.php');
                $Structure_Manager = Structure_Implements::getManager('booking_hotel');
                $rs = $Structure_Manager->main();
            } elseif( $_REQUEST['action'] == 'rent_order' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/components/com_data_get_rent/sitebill_data_get_rent.php');
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/rent_order/rent_order.php');
                $rent_order = new Rent_Order();
                $rs = $rent_order->main();
            }elseif( $_REQUEST['action'] == 'user' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/users/user_object_manager.php');
                $Users_Manager = new User_Object_Manager();
                $rs = $Users_Manager->main();
            } elseif( $_REQUEST['action'] == 'category' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/shop_category/shop_category_manager.php');
                $Shop_Category_Manager = new Shop_Category_Manager();
                $rs = $Shop_Category_Manager->main();
            } elseif( $_REQUEST['action'] == 'product' and file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/shop/lib/shop_product/shop_product_manager.php') ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/shop/lib/shop_product/shop_product_manager.php');
                $Shop_Product_Manager = new Shop_Product_Manager();
                $rs = $Shop_Product_Manager->main();
            } elseif( $_REQUEST['action'] == 'shop_order' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/shop/lib/shop_order/shop_order_manager.php');
                $Shop_Order_Manager = new Shop_Order_Manager();
                $rs = $Shop_Order_Manager->main();
            }  elseif( $_REQUEST['action'] == 'rubricator_component' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/components/rubricator/rubricator_component.php');
                $Rubricator_Component = new Rubricator_Component();
                $rs = $Rubricator_Component->main();
            } elseif( $_REQUEST['action'] == 'logout' ) {
                //$Sitebill_User=Sitebill_User::getInstance();
                //$Sitebill_User->logoutUser();
                $sitebill->delete_session_key($_SESSION['session_key']);
                $sitebill->delete_session_key($_SESSION['key']);
                $DBC=DBC::getInstance();
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
                setcookie('logged_user_id', '', time()-60*60*24*5, '/', SiteBill::$_cookiedomain);
                setcookie('logged_user_token', '', time()-60*60*24*5, '/', SiteBill::$_cookiedomain);
                header('Location: '.SITEBILL_ADMIN_BASE);
                exit;
            } elseif( $_REQUEST['action'] == 'loginasuser' ) {
                $user_id = intval($_GET['user_id']);
                $sitebill->delete_session_key($_SESSION['session_key']);
                $DBC=DBC::getInstance();
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
                setcookie('logged_user_id', '', time()-60*60*24*5, '/', SiteBill::$_cookiedomain);
                setcookie('logged_user_token', '', time()-60*60*24*5, '/', SiteBill::$_cookiedomain);

                require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/user/login.php';
                $Login = new Login();
                $Login->makeUserLogged($user_id, 0, false, false);

                header('Location: '.SITEBILL_MAIN_URL);
                exit;
            } elseif( $_REQUEST['action'] == 'cowork' ) {
                require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/components/cowork/cowork.php');
                $Cowork = new Cowork();
                $rs = $Cowork->main();
            } else {
                //try run apps
                try {
                    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/apps/apps_processor.php');
                    $apps_processor = new Apps_Processor();
                    $rs = $apps_processor->run($_REQUEST['action'], 'admin');
                } catch ( Exception $e ) {
                    if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/customentity/admin/admin.php')){
                        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/customentity/admin/admin.php');
                        if(customentity_admin::checkEntity($_REQUEST['action'])){
                            $CE=new customentity_admin();
                            $rs = $CE->main();
                        }else{
                            require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/data/data_manager.php');
                            if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme').'/admin/data/data_manager.php') ) {
                                require_once (SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme').'/admin/data/data_manager.php');
                                $data_manager_local = new Data_Manager_Local();
                                $rs = $data_manager_local->main();
                            } else {
                                $Data_Manager = new Data_Manager();
                                $rs = $Data_Manager->main();
                            }
                        }
                    }else{
                        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/data/data_manager.php');
                        if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme').'/admin/data/data_manager.php') ) {
                            require_once (SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme').'/admin/data/data_manager.php');
                            $data_manager_local = new Data_Manager_Local();
                            $rs = $data_manager_local->main();
                        } else {
                            $Data_Manager = new Data_Manager();
                            $rs = $Data_Manager->main();
                        }
                    }



                }

            }
        } else {
            require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/admin/data/data_manager.php');
            if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme').'/admin/data/data_manager.php') ) {
                require_once (SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme').'/admin/data/data_manager.php');
                $data_manager_local = new Data_Manager_Local();
                $rs = $data_manager_local->main();
            } else {
                $Data_Manager = new Data_Manager();
                $rs = $Data_Manager->main();
            }
        }
    }



    $sitebill_rent_editor = new SiteBill_Rent_Editor();
    $admin_menu = $sitebill_rent_editor->getAdminMenu();
    $smarty->assign('admin_menu', $admin_menu);

    $user_object_manager = new User_Object_Manager();
    $current_user_info = $user_object_manager->load_by_id($_SESSION['user_id_value']);
    $smarty->assign('current_user_info', $current_user_info);

    $am_array=$sitebill_rent_editor->getAdminMenuArray();
    if ( $sitebill->getConfigValue('check_permissions') ) {
        $am_array = $permission->clear_menu_array($am_array, $_SESSION['user_id_value']);
    }


    $smarty->assign('admin_menua', $am_array);

    if(file_exists(SITEBILL_DOCUMENT_ROOT.'/apps/customentity/admin/admin.php')){
        require_once(SITEBILL_DOCUMENT_ROOT.'/apps/customentity/admin/admin.php');
        $smarty->assign('custom_admin_entity_menu', customentity_admin::getEntityList());
    }

    if(!$access_allow){
        $smarty->assign('content', 'Доступ запрещен');
        $smarty->display("main.tpl");
        exit;
    }
}
if ( defined('IFRAME_MODE') ) {
    $smarty->assign('iframe_mode', true);
}
$smarty->assign('content', $rs);

if ( $sitebill->getConfigValue('apps.messenger.backend_enable') == 1 ) {
    require_once(SITEBILL_DOCUMENT_ROOT.'/apps/messenger/admin/admin.php');
    $messenger_admin_generator = new messenger_admin();
    $messenger_admin_generator->backend_preload();
}

//print_r($admin_menu);
if ( file_exists(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme').'/admin/template/main.tpl') ) {
	$smarty->display(SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$sitebill->getConfigValue('theme').'/admin/template/main.tpl');
} else {
	$smarty->display("main.tpl");
}
exit;
