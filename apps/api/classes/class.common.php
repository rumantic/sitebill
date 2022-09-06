<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/api/classes/class.response.php');
require_once(SITEBILL_DOCUMENT_ROOT.'/apps/api/classes/class.request.php');
/**
 * API Common class
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
class API_Common extends SiteBill {
    protected $request; // API_Request
    /**
     * @var Permission
     */
    protected $permission;

    /**
     * @var bool
     */
    private $check_permission_mode = false;

    /**
     * @var \api\entities\messages
     */
    private $messages_object;

    /**
     * @var \api\entities\messages_client_report
     */
    private $messages_client_report;

    /**
     * @var \api\entities\messages_data_report
     */
    private $messages_data_report;

    /**
     * @var deal_admin
     */
    private $deal_object;

    /**
     * Constructor
     */
    function __construct() {
        parent::__construct();
        $this->request = new API_Request($this->request());
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/user/login.php');
        $Login = new Login();
        $Login->checkLogin('', '', true, $this->getRequestValue('session_key'));
        //$_POST = $this->request->dump();
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/permission/permission.php' );
        $this->permission = new Permission();

        //$this->writeLog(__METHOD__.', request = <pre>'. var_export($this->request->dump(), true).'</pre>');
    }

    function enable_permission_mode () {
        $this->check_permission_mode = true;
    }

    function disable_permission_mode () {
        $this->check_permission_mode = false;
    }

    function get_permission_mode () {
        return $this->check_permission_mode;
    }

    function getRequestValue($key, $type = '', $from = '') {
        if ( $this->request->get($key) != null ) {
            return $this->request->get($key);
        }
        return parent::getRequestValue($key, $type, $from);
    }

    function main() {
        if ( $this->get_permission_mode() ) {
            $user_id = $this->get_my_user_id();

            if (!$this->permission->get_access($user_id, $this->request->get('action'), 'access')) {
                return $this->request_failed('access denied to '.$component);
            }
        }

        $do = $this->getRequestValue('do');
        $action = '_' . $do;
        if (!method_exists($this, $action)) {
            $action = '_default';
        }

        $rs = $this->$action();
        return $rs;
    }

    function _default() {
        return $this->request_failed('method not defined');
    }

    function force_get_session_key() {
        //Сначала из REQUEST
        //И затем из php://input
        if ($this->get_session_key()) {
            return $this->get_session_key();
        } else {
            $data = json_decode(file_get_contents('php://input'), true);
            if ( $data['session_key'] != '' ) {
                return $data['session_key'];
            }
        }
        return false;
    }

    function get_my_user_id() {
        if ($this->getSessionUserId()) {
            return $this->getSessionUserId();
        }
        $session_key = $this->request->get('session_key');
        if ( $session_key == '' ) {
            $session_key = $this->force_get_session_key();
        } else if ( $session_key == 'nobody' ) {
            return 0;
        }
        $DBC = DBC::getInstance();
        $query = 'SELECT user_id FROM ' . DB_PREFIX . '_oauth WHERE session_key=?';
        $stmt = $DBC->query($query, array($session_key));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['user_id'] > 0) {
                return $ar['user_id'];
            }
        }
        return false;
    }

    function request_failed($message) {
        $response = array('state'=>'error','message' => $message,'error' => $message);
        return $this->json_string($response);
    }

    function request_success($message) {
        $response = array('state'=>'success','message' => $message);
        return $this->json_string($response);
    }


    function json_string($in_array) {
        return json_encode($in_array);
    }

    function init_custom_model_object($model_name) {
        require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
        switch ( $model_name ) {
            case 'city':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/city/city_manager.php');
                $city_manager = new City_Manager();
                return $city_manager;
            break;

            case 'component':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/component/component_manager.php');
                $component_manager = new Component_Manager;
                return $component_manager;
            break;

            case 'country':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/country/country_manager.php');
                $country_manager = new Country_Manager;
                return $country_manager;
            break;

            case 'data':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/data/data_manager.php');
                $data_manager = new Data_Manager;
                return $data_manager;
            break;

            case 'district':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/district/district_manager.php');
                $district_manager = new District_Manager;
                return $district_manager;
            break;

            case 'function':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/function/function_manager.php');
                $function_manager = new Function_Manager;
                return $function_manager;
            break;

            case 'group':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/group/group_manager.php');
                $group_manager = new Group_Manager;
                return $group_manager;
            break;

            case 'menu':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/menu/menu_manager.php');
                $menu_manager = new Menu_Manager;
                return $menu_manager;
            break;

            case 'page':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/page/admin/admin.php');
                $page_admin = new page_admin();
                return $page_admin;
            break;


            case 'metro':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/metro/metro_manager.php');
                $metro_manager = new Metro_Manager();
                return $metro_manager;
            break;

            case 'region':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/region/region_manager.php');
                $region_manager = new Region_Manager();
                return $region_manager;
            break;

            case 'street':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/street/street_manager.php');
                $street_manager = new Street_Manager();
                return $street_manager;
            break;

            case 'user':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/users/user_object_manager.php');
                $user_object_manager = new User_Object_Manager();
                return $user_object_manager;
            break;

            case 'memorylist':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/admin/admin.php');
                $memorylist_admin = new memorylist_admin();
                return $memorylist_admin;
                break;

            case 'memorylist_user':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/memorylist/admin/memorylist_user.php');
                $memorylist_user = new memorylist_user();
                return $memorylist_user;
                break;

            case 'client':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/client/admin/admin.php');
                $client_admin = new client_admin();
                return $client_admin;
                break;

            case 'gallery':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/gallery/admin/admin.php');
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/gallery/site/site.php');
                $gallery_site = new gallery_site();
                return $gallery_site;
                break;

            case 'banner':
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/banner/admin/admin.php');
                require_once (SITEBILL_DOCUMENT_ROOT . '/apps/banner/site/site.php');
                $gallery_site = new banner_site();
                return $gallery_site;
                break;

            case 'cowork':
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/components/cowork/cowork_object.php';
                $cowork_object = new Cowork_Object();
                return $cowork_object;
                break;
            case 'building_blocks':
                $building_blocks = new \complex\Objects\BuildingBlocksObject();
                return $building_blocks;
                break;

            case 'subscribers':
                $subscribers_object = new \subscribers\Objects\SubscribersObject();
                return $subscribers_object;
                break;

            case 'columns':
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/columns/admin/admin.php');
                return new columns_admin();
                break;

            case 'table':
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/table/admin/admin.php');
                return new table_admin();
                break;

            case 'files_queue':
                return new \api\entities\files_queue();
                break;

            case 'messages':
                if ( !$this->messages_object ) {
                    $this->messages_object = new \api\entities\messages();
                }
                return $this->messages_object;
                break;

            case 'messages_client_report':
                if ( !$this->messages_client_report ) {
                    $this->messages_client_report = new \api\entities\messages_client_report();
                }
                return $this->messages_client_report;
                break;

            case 'messages_data_report':
                if ( !$this->messages_data_report ) {
                    $this->messages_data_report = new \api\entities\messages_data_report();
                }
                return $this->messages_data_report;
                break;

            case 'messages_summary_report':
                if ( !$this->messages_summary_report ) {
                    $this->messages_summary_report = new \api\entities\messages_summary_report();
                }
                return $this->messages_summary_report;
                break;

            case 'agency':
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/agency/admin/admin.php');
                return new agency_admin();
                break;

            case 'fake_config':
                return new \api\entities\fake_config();
                break;

            case 'schedule_tour':
                return new \api\entities\schedule_tour();
                break;

            case 'view_order':
                return new \api\entities\view_order();
                break;

            case 'deal':
                if ( !$this->deal_object ) {
                    require_once(SITEBILL_DOCUMENT_ROOT . '/apps/deal/admin/admin.php');
                    $this->deal_object = new deal_admin();
                }

                return $this->deal_object;
                break;

        }

        $DBC = DBC::getInstance();
        $query = "SELECT name FROM " . DB_PREFIX . "_table WHERE name=?";
        $stmt = $DBC->query($query, array($model_name));
        if (!$stmt) {
            $this->riseError('model not defined');
            return false;
        }

        $ar = $DBC->fetch($stmt);
        $model_name = $ar['name'];
        if ($model_name != '') {
            require_once (SITEBILL_DOCUMENT_ROOT . '/apps/customentity/admin/admin.php');
            $customentity_admin = new customentity_admin();
            $customentity_admin->custom_construct($model_name);
            return $customentity_admin;
        }
        $this->riseError('model not defined');
        return false;
    }
}
