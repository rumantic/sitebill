<?php

/**
 * SiteBill parent class
 * @author Kondin Dmitriy <kondin@etown.ru>
 */
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', false);
}
if (!defined('DB_HOST')) {
    define('DB_HOST', $__server);
}
if (!defined('DB_PORT') and isset($__db_port)) {
    define('DB_PORT', $__db_port);
}
if (!defined('DB_BASE')) {
    define('DB_BASE', $__db);
}
if (!defined('DB_USER')) {
    define('DB_USER', $__user);
}
if (!defined('DB_PREFIX')) {
    define('DB_PREFIX', $__db_prefix);
}
if (!defined('DB_PASS')) {
    define('DB_PASS', $__password);
}
if (!defined('DB_DSN')) {
    if (defined('DB_PORT') && DB_PORT != '') {
        define('DB_DSN', 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_BASE);
    } else {
        define('DB_DSN', 'mysql:host=' . DB_HOST . ';dbname=' . DB_BASE);
    }
}

if (!defined('DB_ENCODING')) {
    define('DB_ENCODING', 'cp1251');
}

if (!defined('SITE_ENCODING')) {
    define('SITE_ENCODING', 'windows-1251');
}

if (!defined('DEBUG_ENABLED')) {
    define('DEBUG_ENABLED', false);
}

if (!defined('LOG_ENABLED')) {
    define('LOG_ENABLED', false);
}

if (!defined('UPLOADIFY_TABLE')) {
    define('UPLOADIFY_TABLE', DB_PREFIX . '_uploadify');
}

if (!defined('IMAGE_TABLE')) {
    define('IMAGE_TABLE', DB_PREFIX . '_image');
}

if (!defined('MEDIA_FOLDER')) {
    define('MEDIA_FOLDER', SITEBILL_DOCUMENT_ROOT . '/img/data');
}

if (!defined('STR_MEDIA')) {
    define('STR_MEDIA', true);
}
if (!defined('STR_MEDIA_FOLDERFDAYS')) {
    define('STR_MEDIA_FOLDERFDAYS', 1);
}


if (!defined('ESTATE_FOLDER')) {
    define('ESTATE_FOLDER', $folder);
}
if (!defined('SITEBILL_DOCUMENT_ROOT')) {
    define('SITEBILL_DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'] . ESTATE_FOLDER);
}

if (!defined('SITEBILL_APPS_DIR')) {
    define('SITEBILL_APPS_DIR', SITEBILL_DOCUMENT_ROOT . '/apps');
}
if (!defined('SITEBILL_MAIN_URL')) {
    define('SITEBILL_MAIN_URL', ESTATE_FOLDER);
}

if (!defined('NOTICE')) {
    define('NOTICE', 'NOTICE');
}
if (!defined('ERROR')) {
    define('ERROR', 'ERROR');
}
if (!defined('WARNING')) {
    define('WARNING', 'WARNING');
}

/* if(!defined('SITEBILL_MAIN_FULLURL')){
  define('SITEBILL_MAIN_FULLURL','http://'.$_SERVER['HTTP_HOST'].ESTATE_FOLDER);
  } */
/*
  if(isset($_GET['run_debug'])){
  define('DEBUG_ENABLED',true);
  unset($_GET['run_debug']);
  }
 */
//require_once(SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/sitebill_application.php');

/* $_SESSION['csrftoken'] = md5(uniqid(mt_rand() . microtime()));
  if($_SESSION['csrfsecret']==''){
  $_SESSION['csrfsecret']=md5(uniqid(mt_rand() . microtime()));
  } */

/*
  $salt=substr(md5(time().rand(100,999)), 0, 6);
  $token = $salt.":".MD5($salt.":".$_SESSION['skey']);
  setcookie('CSRF-TOKEN', $token, time()+3600, '/', Sitebill::$_cookiedomain); */
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/sitebill_autoload.php';

require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/debugger.class.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/logger.class.php';
if (!defined('SITEBILL_LOADING')) {
    define('SITEBILL_LOADING', true);
}
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/dbc.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/sconfig.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/sitebill_datetime.php';
if ( file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/metrics/bootstrap.php') ) {
    require_once SITEBILL_DOCUMENT_ROOT . '/apps/metrics/bootstrap.php';
}


//require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/sitebill_router.php';
//require_once SITEBILL_DOCUMENT_ROOT.'/apps/system/lib/system/sitebill_user.php';

$SConfig = SConfig::getInstance();
if ('' != $SConfig->getConfigValue('default_timezone')) {
    ini_set('date.timezone', $SConfig->getConfigValue('default_timezone'));
    date_default_timezone_set($SConfig->getConfigValue('default_timezone'));
}


require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/sitebill_registry.php');
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/multilanguage/multilanguage.class.php';


if (isset($_REQUEST['search'])) {
    $_SESSION['rem_page'] = 1;
}
if (isset($_REQUEST['page'])) {
    $_SESSION['rem_page'] = $_REQUEST['page'];
} elseif (!isset($_SESSION['rem_page'])) {
    $_SESSION['rem_page'] = 1;
}
$_POST['page'] = $_SESSION['rem_page'];

//Sitebill::parseLocalSettings();
//Sitebill::initLocalComponents();
/*
  if(!isset($_SESSION['Sitebill_User']) || !is_array($_SESSION['Sitebill_User'])){
  $_SESSION['Sitebill_User']=array();
  $_SESSION['Sitebill_User']['name']='';
  $_SESSION['Sitebill_User']['group_id']=0;
  $_SESSION['Sitebill_User']['group_name']='Гость';
  $_SESSION['Sitebill_User']['login']='';
  $_SESSION['Sitebill_User']['user_id']=0;
  $_SESSION['Sitebill_User']['group_system_name']='guest';
  }
 */

use Illuminate\Http\Request;

require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/AuthAccessTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/BreadcrumbTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/ConfigTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/DebugLogTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/FileUploadTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/ImageTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/MailTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/PaginationTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/QueryCacheTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/RequestTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/TemplateTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/TextUtilTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/TranslationTrait.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/traits/UrlTrait.php';

class SiteBill
{
    use AuthAccessTrait;
    use BreadcrumbTrait;
    use ConfigTrait;
    use DebugLogTrait;
    use FileUploadTrait;
    use ImageTrait;
    use MailTrait;
    use PaginationTrait;
    use QueryCacheTrait;
    use RequestTrait;
    use TemplateTrait;
    use TextUtilTrait;
    use TranslationTrait;
    use UrlTrait;

    /**
     * Error message
     */
    var $error_message = false;
    var $uploadify_dir = '/cache/upl/';
    var $storage_dir = '/img/data/';
    protected static $config_loaded = false;
    protected static $config_array = array();
    /* protected static $local_config = false; */
    private $external_uploadify_image_array = false;
    protected static $storage = array();
    protected static $Heaps = array();

    /* Container for local site settings from settings.ini.php */
    protected static $localSettings = false;
    /**
     * @var Grid_Constructor
     */
    public static $_grid_constructor_local = null;
    public static $_realty_viewer_local = null;
    protected $_grid_constructor = null;
    public static $_cookiedomain = '';
    public static $_trslashes = null;
    private static $_template_store = null;

    /**
     * @var string Текущая локаль
     */
    //private static $_locale = 'ru';

    const MEDIA_SAVE_FOLDER = 1;

    public static $_csrf_token = '';

    /**
     * @var \sharder\lib\sharder
     */
    protected $sharder;

    /**
     * @var logger_admin
     */
    private $logger_admin;

    /**
     *  Request mini data
     * @var array
     */
    public static $_request = null;

    public static $illuminate_database_registred = false;
    public static $illuminate_request_registred = false;

    /**
     * @var Request
     */
    public static $iRequest = null;

    /**
     * @var \Illuminate\Events\Dispatcher
     */
    private static $iEventDispatcher;

    /**
     * @var \DebugBar\StandardDebugBar
     */
    private static $debugbar;

    /**
     * @var Cowork_Object
     */
    private $cowork_object;

    /**
     * @var bool Устанавливаем true, если нам нужно заменять старый грид на Angular-грид
     */
    public static $replace_grid_with_angular = false;

    /**
     * @var bool Эта переменная устанавливается в true, если была успешная замена на Angular-грид
     */
    public static $grid_replaced_with_angular = false;

    /**
     * @var agency_admin
     */
    protected $agency_admin;

    /**
     * @var API_Common
     */
    protected $api_common;

    /**
     * Displayed template file
     *
     * @var string
     */
    protected static $tpl_file = 'main.tpl';

    /**
     * Контейнеризированный экземпляр frontend-контроллера
     * @var null
     */
    protected static $frontend = null;

    public $template;

    /**
     * @param SiteBill_Krascap $frontend
     */
    public function setFrontend($frontend){
        self::$frontend = $frontend;
    }

    /**
     * @return null|SiteBill_Krascap
     */
    public function getFrontend(){
        return self::$frontend;
    }

    public static function admin3_compatible()
    {
        return self::$grid_replaced_with_angular;
    }

    /**
     * @return \api\aliases\API_common_alias|API_Common
     */
    protected function get_api_common()
    {
        if (!$this->api_common) {
            $this->api_common = new \api\aliases\API_common_alias();
        }
        return $this->api_common;
    }

    /**
     * Формирование набора js-переменных для использования в скриптах
     * Для интеграции в smarty-шаблоны использовать переменную {$SystemJSvars}
     * TODO Добавить сюда выдачу языковых переменных, используемых скриптами,
     * напр. фраза "происходит загрузка" или уведомление о минимальном числе фото
     * @return string
     */
    function getSystemJSvars()
    {
        $vars = new stdClass();

        // Путь к ajax-точке входа
        $vars->ajaxPath = SITEBILL_MAIN_URL . '/js/ajax.php';
        // Текущий язык
        $vars->currentLang = $this->getCurrentLang();

        return '<!--SystemJSvars--><script>var SystemJSvars=' . json_encode($vars) . ';</script><!--.SystemJSvars-->';
    }

    function SiteBill()
    {
        self::__construct();
    }

    /**
     * Constructor
     */
    function __construct()
    {
        $this->register_illuminate_request();


        $this->extendsSmarty();

        //$this->initRequest();

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/template/template.php';
        self::register_debugbar();
        $this->template = new Template(null, $this->getdebugbarRenderer());
        $this->template->assign('SITEBILL_DOCUMENT_ROOT', SITEBILL_DOCUMENT_ROOT);

        Multilanguage::appendAppDictionary('system');
        if (!self::$localSettings) {
            $this->parseLocalSettings();
            $this->initLocalComponents();
        }
        if ($this->_grid_constructor === null) {
            $this->_grid_constructor = self::$_grid_constructor_local;
        }


        /*if (!isset($smarty->registered_plugins['function']['_e'])) {
            $smarty->registerPlugin("function","_e", "_translate");
        }*/


        /*if(self::$_csrf_token == ''){
            $valid_thru = time()+1800;
            self::$_csrf_token = $valid_thru.':'.base64_encode(
                hash_hmac(
                    'sha256',
                    $valid_thru . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $_SESSION['key'],
                    $this->getConfigValue('csrf_salt'),
                    true
                )
            );
        }*/

        if ($this->isDemo()) {
            $this->template->assert('show_demo_banners', '1');
        }

        $this->template->assert('estate_folder', SITEBILL_MAIN_URL);
        $this->template->assert('theme_folder', SITEBILL_MAIN_URL . '/template/frontend/' . $this->getConfigValue('theme'));
        $this->template->assert('current_theme_name', $this->getConfigValue('theme'));
        $this->template->assert('bootstrap_version', trim($this->getConfigValue('bootstrap_version')));

        $this->template->assert('CurrentLang', $this->getCurrentLang());

        $this->template->assert('SystemJSvars', $this->getSystemJSvars());
        /* if(1===(int)$this->getConfigValue('use_heaps')){
          if(!isset(self::$Heaps['user'])){
          require_once SITEBILL_DOCUMENT_ROOT.'/user_heap.php';
          self::$Heaps['user']=$userHeap;
          }
          } */

        /*
        $lang_str = 'var jsWords={};';
        $lang_str .= 'jsWords.L_FORMDATASTORING = \'' . Multilanguage::_('L_FORMDATASTORING') . '\';';
        $lang_str .= 'jsWords.L_FORMIMAGEMORE = \'' . Multilanguage::_('L_FORMIMAGEMORE') . '\';';
        $this->template->assert('jsWords', $lang_str);
*/
        //$this->db = new Db( $__server, $__db, $__user, $__password );
        Sitebill_Datetime::setDateFormat($this->getConfigValue('date_format'));

        if (defined('ADMIN_NO_MAP')) {
            $this->template->assert('ADMIN_NO_MAP_PROVIDERS', '1');
        } else {
            $this->template->assert('ADMIN_NO_MAP_PROVIDERS', '0');
        }
        if (defined('ADMIN_NO_NANOAPI')) {
            $this->template->assert('ADMIN_NO_NANOAPI', '1');
        } else {
            $this->template->assert('ADMIN_NO_NANOAPI', '0');
        }
        if (1 == $this->getConfigValue('use_google_map')) {
            $this->template->assert('map_type', 'google');
        } elseif (2 == $this->getConfigValue('use_google_map')) {
            $this->template->assert('map_type', 'leaflet_osm');
        } else {
            $this->template->assert('map_type', 'yandex');
        }

        $this->template->assert('estate_folder', SITEBILL_MAIN_URL);
        //self::setLangSession();

        require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/db/mysql_db_emulator.php';
        $this->db = new Mysql_DB_Emulator();
        $this->load_hooks();
        // $this->writeLog('sitebill constructor');

        //global $smarty;
        //$smarty->assign_by_ref('Sitebill', $this);

    }

    /**
     * Получение исполняемого файла приложения
     * содержит проверку локализаций, подключения родительских файлов
     * на выходе объект приложения требуемого класса
     * @param $app имя приложения
     * @param string $interface интерфейс site|admin
     * @return object|null объект приложения
     */
    function get_app($app, $interface = 'site')
    {

        $instance = null;

        $apps_processor = new Apps_Processor();
        $appdata = $apps_processor->get_apps_by_name($app);
        if ($appdata) {
            if ($appdata['local_admin_path'] != '') {
                require_once($appdata['local_admin_path']);
            } elseif ($appdata['admin_path'] != '') {
                require_once($appdata['admin_path']);
            }

            if ($interface == 'site') {
                if ($appdata['local_site_path'] != '') {
                    require_once($appdata['site_path']);
                    require_once($appdata['local_site_path']);
                    $app_class_name = 'local_' . $app . '_site';
                    if (!class_exists($app_class_name)) {
                        $app_class_name = $app . '_site';
                        if (!class_exists($app_class_name)) {
                            $app_class_name = false;
                        }
                    }
                } elseif ($appdata['site_path'] != '') {
                    require_once($appdata['site_path']);
                    $app_class_name = $app . '_site';
                }
                if ($app_class_name) {
                    $instance = new $app_class_name;
                }
            } else {
                $app_class_name = $app . '_admin';
                if ($app_class_name) {
                    $instance = new $app_class_name;
                }
            }
        }
        return $instance;
    }

    /* TODO Реализовать доступ к текущей локали и ее установку в качестве своства базового класса */

    /*
    static public function setLocale($locale){
        self::$_locale = $locale;
    }

    static public function getLocale(){
        return self::$_locale;
    }
    */


    static public function register_illuminate_event_dispatcher(\Illuminate\Events\Dispatcher $dispatcher)
    {
        self::$iEventDispatcher = $dispatcher;
    }

    static public function event_dispatcher()
    {
        return self::$iEventDispatcher;
    }


    function register_illuminate_request()
    {
        if (!self::$illuminate_request_registred) {
            self::$iRequest = Request::capture();
            self::$illuminate_request_registred = true;
        }
    }

    function request()
    {
        return self::$iRequest;
    }






    function load_hooks()
    {
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/hooks' . '/hooks.php')) {
            include_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/hooks' . '/hooks.php');
        }
    }

    public static function genPassword($len = 8)
    {
        $array = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '@', '#', '%', '&', '?', '!');
        shuffle($array);
        $p = array_slice($array, 0, $len);
        return implode('', $p);
    }

    public function getCurrentLang()
    {
        return $_SESSION['_lang'];
    }


    /*
     * return nonslashed full net url
     */


    protected function initLocalComponents()
    {
        $SConf = SConfig::getInstance();
        //var_dump($SConf->getConfigValue('theme'));
        $grid_constructor_full_path = '';
        if (self::$_grid_constructor_local === null) {
            if (self::$localSettings && isset(self::$localSettings['GridConstructor']) && file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . self::$localSettings['GridConstructor']['path'])) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/grid_constructor.php';
                $grid_constructor_full_path = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $SConf->getConfigValue('theme') . self::$localSettings['GridConstructor']['path'];
                require_once $grid_constructor_full_path;
                $gcname = self::$localSettings['GridConstructor']['name'];
                self::$_grid_constructor_local = new $gcname();
            } elseif (1 == intval($SConf->getConfigValue('classic_local_grid')) && file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/main/grid/local_grid_constructor.php')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/grid_constructor.php';
                $grid_constructor_full_path = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $SConf->getConfigValue('theme') . '/main/grid/local_grid_constructor.php';
                require_once $grid_constructor_full_path;
                $gcname = 'Local_Grid_Constructor';
                self::$_grid_constructor_local = new $gcname();
            } elseif (1 == intval($SConf->getConfigValue('classic_local_grid')) && file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/local/main/grid/local_grid_constructor.php')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/grid_constructor.php';
                $grid_constructor_full_path = SITEBILL_DOCUMENT_ROOT . '/template/frontend/local/main/grid/local_grid_constructor.php';
                require_once $grid_constructor_full_path;
                $gcname = 'Local_Grid_Constructor';
                self::$_grid_constructor_local = new $gcname();
            } else {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php';
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/GridConstructorFactory.php';
                self::$_grid_constructor_local = GridConstructorFactory::create();
            }
            //$this->writeLog('$grid_constructor_full_path = ' . $grid_constructor_full_path);
        }
        if (self::$_realty_viewer_local === null) {
            if (self::$localSettings && isset(self::$localSettings['RealtyView']) && file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . self::$localSettings['RealtyView']['path'])) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php';
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/view/kvartira_view.php');
                require_once SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $SConf->getConfigValue('theme') . self::$localSettings['RealtyView']['path'];
                $gcname = self::$localSettings['RealtyView']['name'];
                self::$_realty_viewer_local = new $gcname();
            } elseif (1 == intval($SConf->getConfigValue('classic_local_view')) && file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $SConf->getConfigValue('theme') . '/main/view/local_kvartira_view.php')) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php';
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/view/kvartira_view.php');
                require_once SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $SConf->getConfigValue('theme') . '/main/view/local_kvartira_view.php';
                $gcname = 'Local_Kvartira_View';
                self::$_realty_viewer_local = new $gcname();
            } else {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php';
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/view/kvartira_view.php');
                self::$_realty_viewer_local = new Kvartira_View();
            }
        }
        if (1 === intval($SConf->getConfigValue('set_cookie_subdomenal'))) {
            $cd = trim($SConf->getConfigValue('core_domain'));
            if ($cd != '') {
                self::$_cookiedomain = '.' . $cd;
            }
            //self::$_cookiedomain='.'.$SConf->getConfigValue('core_domain');
        }/* else{
          self::$_cookiedomain='.'.$_SERVER['HTTP_HOST'];
          } */
        //self::$_cookiedomain='';

        if (is_null(self::$_trslashes)) {
            if (1 == intval($SConf->getConfigValue('apps.seo.no_trailing_slashes'))) {
                self::$_trslashes = '';
            } else {
                self::$_trslashes = '/';
            }
        }
    }


    /* function SiteBill() {
      //echo 'SiteBill<br>';
      } */

    protected function parseLocalSettings()
    {
        //var_dump(self::$localSettings);
        if (!self::$localSettings) {
            if ($settings = parse_ini_file(SITEBILL_DOCUMENT_ROOT . '/settings.ini.php', true)) {
                self::$localSettings = $settings;
            } else {
                self::$localSettings = array();
            }
        }
    }

    protected function _setGridConstructor($newGridConstructor)
    {
        $this->_grid_constructor = $newGridConstructor;
        self::$_grid_constructor_local = $newGridConstructor;
    }

    public function _getGridConstructor($label = '')
    {
        if (method_exists(self::$_grid_constructor_local, 'set_label')) {
            self::$_grid_constructor_local->set_label($label);
        }
        return self::$_grid_constructor_local;
    }

    /**
     * @return Kvartira_View
     */
    public function _getRealtyViewer()
    {
        return self::$_realty_viewer_local;
    }

    static function getAttachments($object_type, $object_id)
    {
        $attachments = array();
        if ((int)$object_id == 0 || $object_type == '') {
            return $attachments;
        }
        $DBC = DBC::getInstance();
        $stmt = $DBC->query('SELECT * FROM ' . DB_PREFIX . '_attachment WHERE object_type=? AND object_id=?', array($object_type, $object_id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $attachments[] = $ar;
            }
        }
        return $attachments;
    }

    static function appendAttachments($object_type, $object_id, $attachments)
    {
        if (count($attachments) > 0) {
            $DBC = DBC::getInstance();
            $q = 'INSERT INTO ' . DB_PREFIX . '_attachment (file_name, object_id, object_type) VALUES (?,?,?)';
            foreach ($attachments as $attachment) {
                if (file_exists(SITEBILL_DOCUMENT_ROOT . '/cache/upl/' . $attachment)) {
                    copy(SITEBILL_DOCUMENT_ROOT . '/cache/upl/' . $attachment, SITEBILL_DOCUMENT_ROOT . '/attachments/' . $attachment);
                    unlink(SITEBILL_DOCUMENT_ROOT . '/cache/upl/' . $attachment);
                    if (file_exists(SITEBILL_DOCUMENT_ROOT . '/cache/upl/thumbnail/' . $attachment)) {
                        unlink(SITEBILL_DOCUMENT_ROOT . '/cache/upl/thumbnail/' . $attachment);
                    }
                    $DBC->query($q, array($attachment, $object_id, $object_type));
                }
            }
        }
    }


    function get_ajax_functions()
    {
        $rs = '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/refresher.functions.js"></script>';
        return $rs;
    }




    /**
     * Get page by URI
     * @param string $uri uri
     * @return array
     */
    function getPageByURI($uri)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_page WHERE uri=? LIMIT 1';
        $uri = str_replace('/', '', $uri);
        $stmt = $DBC->query($query, array($uri));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['page_id'] > 0) {
                return $ar;
            }
        }
        return false;
    }




    /* function setConfigValue ( $key, $value ) {
      if ( !$this->config_loaded ) {
      $this->loadConfig();
      }
      $this->config_array[$key]=$value;
      } */


    /**
     * return id of Admininstrator
     * @param
     * @return int
     */
    function getAdminUserId()
    {
        if (isset(self::$storage['AdminUserId'])) {
            return self::$storage['AdminUserId'];
        }
        $admin_id = 0;
        $DBC = DBC::getInstance();
        $query = 'SELECT u.user_id FROM ' . DB_PREFIX . '_user u LEFT JOIN ' . DB_PREFIX . '_group g USING(group_id) WHERE g.system_name=? LIMIT 1';
        $stmt = $DBC->query($query, array('admin'));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $admin_id = $ar['user_id'];
            self::$storage['AdminUserId'] = $admin_id;
        }
        return $admin_id;
    }

    /**
     * return Vendor info
     * @param id integer
     * @return string
     */
    function getVendorInfoById($id)
    {
        $vendor_info = array();
        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_vendor WHERE vendor_id=? LIMIT 1';
        $stmt = $DBC->query($query, array($id));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $vendor_info = $ar['user_id'];
        }
        return $vendor_info;
    }

    function getUnregisteredUserId()
    {
        $user_id = 0;
        /* if(0!=(int)$this->getConfigValue('free_advs_user_id')){
          return (int)$this->getConfigValue('free_advs_user_id');
          } */
        $DBC = DBC::getInstance();
        $query = 'SELECT user_id FROM ' . DB_PREFIX . '_user WHERE login=? LIMIT 1';
        $stmt = $DBC->query($query, array('_unregistered'));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $user_id = $ar['user_id'];
        }
        return $user_id;
    }

    function growCounter($table_name, $primary_key_name, $primary_key_value, $user_id = 0)
    {
        if (1 == $this->getConfigValue('use_realty_view_counter')) {
            if (!isset($_SESSION['realty_views'][$primary_key_value])) {
                $DBC = DBC::getInstance();
                $query = 'UPDATE ' . DB_PREFIX . '_' . $table_name . ' SET `view_count` = `view_count` + 1 WHERE ' . $primary_key_name . ' = ?';
                $stmt = $DBC->query($query, array($primary_key_value));
            }
            $_SESSION['realty_views'][$primary_key_value] = time();
        }
    }

    function validateEmailFormat($email)
    {
        if (preg_match('/^[0-9a-z]+[-\._0-9a-z]*@[0-9a-z]+[-\._^0-9a-z]*[0-9a-z]+[\.]{1}[a-z]{2,6}$/', strtolower($email))) {
            return true;
        } else {
            return false;
        }
    }

    function validateMobilePhoneNumberFormat($phone_number, $mask = '')
    {
        if ($mask != '') {
            $clear_number = preg_replace('/[^\d]/', '', $phone_number);

            if (preg_match('/^' . $mask . '$/', $clear_number)) {
                return $clear_number;
            } else {
                return FALSE;
            }
        } else {
            if ($this->getConfigValue('apps.fasteditor.enable')) {
                $clear_number = preg_replace('/[^\d]/', '', $phone_number);
                if (preg_match('/^8(\d){10}$/', $clear_number)) {
                    return $clear_number;
                } else {
                    return FALSE;
                }
            } else {
                return TRUE;
            }
        }
    }

    public static function getAttachmentsBlock()
    {
        global $smarty;
        return $smarty->fetch(SITEBILL_DOCUMENT_ROOT . '/apps/admin/admin/template/attachments_block.tpl');
    }

    public static function modelSimplification($model)
    {
        if (!empty($model)) {
            foreach ($model as $mkey => $melement) {
                foreach ($melement as $k => $v) {
                    if ($k == 'type' && ($v != 'select_by_query_multi' && $v != 'select_by_query' && $v != 'select_box' && $v != 'select_box_structure' && $v != 'structure' && $v != 'date' && $v != 'tlocation' && $v != 'client_id')) {
                        $model[$mkey]['value_string'] = $model[$mkey]['value'];
                    }
                    if (!in_array($k, array('name', 'title', 'value', 'value_string', 'type', 'image_array'))) {
                        unset($model[$mkey][$k]);
                    }
                }
            }
        }

        return $model;
    }




    function load_topic_links()
    {
        if ($this->loaded_links) {
            return $this->ral;
        }
        //echo '<hr>load<br>';
        //echo 'Загрузка правил перелинковки из таблицы topic_links<br>';
        $DBC = DBC::getInstance();
        $this->ral = array();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_topic_links';
        $stmt = $DBC->query($query, array(), $success);
        if ($DBC->getLastError()) {
            //echo '<font color="red">' . $DBC->getLastError() . '</font><br>';
        }

        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $this->ral[$ar['topic_id']]['link_topic_id'] = $ar['link_topic_id'];
                //echo $ar['params'].'<br>';
                //print_r(json_decode($ar['params']));
                //echo '<br>';
                $json_params_decode = json_decode($ar['params']);
                if (is_object($json_params_decode)) {
                    $this->ral[$ar['topic_id']]['params'] = $json_params_decode;
                } elseif ($ar['params'] != '') {
                    //echo $ar['params'].'<br>';
                    $this->ral[$ar['topic_id']]['params'] = $ar['params'];
                }
            }
        }
        //echo 'Загрузка правил перелинковки завершена<br>';
        $this->loaded_links = true;
        return $this->ral;
    }

    /**
     * Saving information about the achievement of the event
     * @param array $events Array of events information
     */
    function reachEventStat($events)
    {
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/statoid/admin/admin.php') && 1 == $this->getConfigValue('apps.statoid.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/statoid/admin/admin.php';
            $S = new statoid_admin();
            foreach ($events as $event) {
                $S->collectEvent($event['event'], $event['id']);
            }
        }
    }

    /**
     * Saving information about reaching the goals
     * @param array $targets Array of targets information
     */
    function reachTargetStat($targets)
    {
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/statoid/admin/admin.php') && 1 == $this->getConfigValue('apps.statoid.enable')) {
            require_once SITEBILL_DOCUMENT_ROOT . '/apps/statoid/admin/admin.php';
            $S = new statoid_admin();
            foreach ($targets as $target) {
                $S->collectTarget($target['event'], $target['id']);
            }
        }
    }

    protected function executeHTTPRequest($queryUrl, array $params = array(), $disable_http_build = false)
    {
        $result = array();
        if (!$disable_http_build) {
            $queryData = http_build_query($params);
        } else {
            $queryData = $params;
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $queryUrl,
            CURLOPT_POSTFIELDS => $queryData,
        ));

        $curlResult = curl_exec($curl);

        curl_close($curl);

        if ($curlResult != '') {
            $result = json_decode($curlResult, true);
        } else {
            $result = array('state' => 'error', 'message' => 'query failed');
        }

        return $result;
    }


    /**
     * Generation social markups (twitter, opengraph, schema)
     * @param array $params Data for markup
     * @return string
     */
    function generateSocials($params)
    {

        $social = '';

        // twitter card
        $social .= '<meta name="twitter:card" content="' . (isset($params['tw:cardtype']) ? $params['tw:cardtype'] : 'summary') . '">';
        $social .= '<meta name="twitter:title" content="' . htmlspecialchars(strip_tags($params['title'])) . '">';
        $social .= '<meta name="twitter:description" content="' . htmlspecialchars(strip_tags($params['description'])) . '">';
        if ($params['image'] != '') {
            $social .= '<meta name="twitter:image" content="' . $this->getServerFullUrl(true) . '/img/data/' . $params['image'] . '">';
        }elseif($params['srcimage'] != ''){
            $social .= '<meta name="twitter:image" content="' . $params['srcimage'] . '">';
        }
        if($params['video'] != ''){
            $social .= '<meta name="twitter:player" content="'.$params['video'].'">';
        }

        // open graph
        $social .= '<meta property="og:title" content="' . htmlspecialchars(strip_tags($params['title'])) . '" />';
        $social .= '<meta property="og:type" content="' . (isset($params['og:type']) ? $params['og:type'] : 'website') . '" />';
        $social .= '<meta property="og:url" content="' . $params['url'] . '" />';
        if ($params['image'] != '') {
            $social .= '<meta property="og:image" content="' . $this->getServerFullUrl(true) . '/img/data/' . $params['image'] . '" />';
        }elseif($params['srcimage'] != ''){
            $social .= '<meta property="og:image" content="' . $params['srcimage'] . '" />';
        }
        if($params['video'] != ''){
            $social .= '<meta property="og:video" content="'.$params['video'].'">';
        }



        $social .= '<meta property="og:description" content="' . htmlspecialchars(strip_tags($params['description'])) . '" />';

        // schema
        $social .= '<meta itemprop="name" content="' . htmlspecialchars(strip_tags($params['title'])) . '">';
        $social .= '<meta itemprop="description" content="' . htmlspecialchars(strip_tags($params['description'])) . '">';
        if ($params['image'] != '') {
            $social .= '<meta itemprop="image" content="' . $this->getServerFullUrl(true) . '/img/data/' . $params['image'] . '">';
        }elseif($params['srcimage'] != ''){
            $social .= '<meta itemprop="image" content="' . $params['srcimage'] . '">';
        }

        return $social;

    }


    function checkReCaptcha($token)
    {

        $secret = trim($this->getConfigValue('google_recaptcha_secret'));

        if ($secret != '') {
            $postdata = [
                'secret' => $secret,
                'response' => $token
            ];
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($curl);
            curl_close($curl);
            if (false !== $result) {
                $resp = json_decode($result, true);
                if (!isset($resp['success']) || !$resp['success']) {
                    return false;
                } else {
                    return true;
                }
            }

        }
        return false;

    }

    function exec_sql_query_array($query_data)
    {
        $DBC = DBC::getInstance();
        $rs = '';

        foreach ($query_data as $query) {
            $success = false;
            $stmt = $DBC->query($query, array(), $rows, $success);
            if (!$success) {
                $rs .= $DBC->getLastError() . ': ' . $query . '<br>';
            } else {
                $rs .= Multilanguage::_('QUERY_SUCCESS', 'system') . ': ' . $query . '<br>';
            }
        }
        return $rs;
    }




    /*
    public function response301(){
        header($this->getSAPIHeaderTitle().' 301 Moved Permanently');
        echo $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme').'/301.tpl');
        exit();
    }

    public function response302(){
        header($this->getSAPIHeaderTitle().' 302 Moved Temporarily');
        echo $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme').'/302.tpl');
        exit();
    }

    public function response400(){
        header($this->getSAPIHeaderTitle().' 400 Bad Request');
        echo $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme').'/400.tpl');
        exit();
    }

    public function response401(){
        header($this->getSAPIHeaderTitle().' 401 Unauthorized');
        echo $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme').'/401.tpl');
        exit();
    }

    public function response403(){
        header($this->getSAPIHeaderTitle().' 403 Forbidden');
        echo $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme').'/403.tpl');
        exit();
    }

    public function response404(){
        header($this->getSAPIHeaderTitle().' 404 Not Found');
        echo $this->template->fetch(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme').'/404.tpl');
        exit();
    }

    private function getSAPIHeaderTitle() {
        $sapi_name = php_sapi_name();
        if ($sapi_name == 'cgi' || $sapi_name == 'cgi-fcgi') {
            return 'Status:';
        } else {
            return $_SERVER['SERVER_PROTOCOL'];
        }
    }
    */
    /**
     * Return current CSRF token
     * @param int $len
     * @return string
     */
    /*function getCSRFToken($len = null){
        if(!is_null($len)){
            $len = 32;
        }
        if(!isset($_SESSION['csrftoken'])){
            $_SESSION['csrftoken'] = $this->generateCSRFToken($len);
        }
        return $_SESSION['csrftoken'];
    }
    */
    /*
    public function guest(){
        return intval($_SESSION['user_id']) === 0;
    }

    public function auth(){
        return intval($_SESSION['user_id']) > 0;
    }

    public function is($groupname){
        return $_SESSION['current_user_group_name'] === $groupname;
    }

    public function test($p = null){
        if($_SESSION['user_id'] != 0){
            true;
        }
        if($p !== null){
            return 'vv-'.$p.'-vv';
        }
        return 'vv-NULL-vv';

    }
    */

}

//Helpers
function store($key)
{
    return Sitebill::get_template_store($key);
}

function config($key, $default = false)
{
    return Sitebill::getConfigValueStatic($key, $default);
}

function set_store($key, $value)
{
    return Sitebill::set_template_store($key, $value);
}

function extract_scripts_and_styles($content)
{
    preg_match_all('#<script(.*?)</script>#is', $content, $matches);
    foreach ($matches[0] as $value) {
        $js[] = $value;
    }
    $content = preg_replace('#<script(.*?)</script>#is', '', $content);
    return array(
        'content' => $content,
        'js' => $js
    );
}

/**
 * Проверяем не является ли main_file_tpl устаревшим (для blade-шаблонов)
 * @return bool
 */
function safe_check_main_file_tpl()
{
    $deprecated_tpl_files = SiteBill::old_template_files_array();
    if (in_array(Sitebill::get_template_store('main_file_tpl'), $deprecated_tpl_files)) {
        return false;
    }
    if (Sitebill::get_template_store('main_file_tpl') == '') {
        return false;
    }
    return true;
}

function get_blade_analog_for_tpl_file($template_name)
{
    if (in_array($template_name, SiteBill::old_template_files_array())) {
        return 'pages.' . str_replace('.tpl', '', $template_name);
    }
    return false;
}

function request() {
    return Sitebill::$iRequest;
}

Sitebill::setLangSession();

/*
class Env {

    private static $instance = null;

    private static function initInstance() {
        self::$instance = parse_ini_file(SITEBILL_DOCUMENT_ROOT.'/.env', true);
    }

    public static function get($property){
        if(is_null(self::$instance)){
            self::initInstance();
        }
        return self::$instance[$property];
    }
}
*/
