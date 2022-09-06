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
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/dbc.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/sconfig.php';
require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/sitebill_datetime.php';


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

Sitebill::setLangSession();
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

class SiteBill
{
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

    function SiteBill() {
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

    static public function set_template_store($key, $value)
    {
        self::$_template_store[$key] = $value;
    }

    static public function get_template_store($key)
    {
        return self::$_template_store[$key];
    }

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

    public static function register_debugbar()
    {
        if (defined('DEBUG_ENABLED') && DEBUG_ENABLED && !isset(self::$debugbar)) {
            self::$debugbar = new \DebugBar\StandardDebugBar();
        }
    }

    function getdebugbarRenderer()
    {
        if (isset(self::$debugbar)) {
            $baseUrl = SITEBILL_MAIN_URL . '/apps/third/vendor/maximebf/debugbar/src/DebugBar/Resources';
            return self::$debugbar->getJavascriptRenderer($baseUrl);
        }
    }

    public static function smarty_fetch($file_name)
    {
        global $smarty;
        return $smarty->fetch($file_name);

    }

    public static function add_pdo_debugbar_collector($PDO, $capsule)
    {
        if (!isset(self::$debugbar)) {
            self::register_debugbar();
        }

        if (isset(self::$debugbar)) {
            $pdoCollector = new DebugBar\DataCollector\PDO\PDOCollector();
            $pdo_debug = new \DebugBar\DataCollector\PDO\TraceablePDO($PDO);
            $pdoCollector->addConnection($pdo_debug, 'sitebill-pdo');

            $pdo_debug_el = new \DebugBar\DataCollector\PDO\TraceablePDO($capsule->getConnection()->getPdo());
            $pdoCollector->addConnection($pdo_debug_el, 'eloquent');

            self::$debugbar->addCollector($pdoCollector);
        }
    }

    public static function add_debug_message($message)
    {
        if (isset(self::$debugbar)) {
            self::$debugbar["messages"]->addMessage($message);
        }
    }


    /**
     * Register plugins for using in smarty templates
     * @global type $smarty
     */
    public function extendsSmarty()
    {
        global $smarty;
        if (!isset($smarty->registered_plugins['function']['_e'])) {
            $smarty->registerPlugin('function', "_e", "_translate");
        }
        if (!isset($smarty->registered_plugins['function']['formaturl'])) {
            $smarty->registerPlugin('function', 'formaturl', array(&$this, 'formaturl'));
        }
        if (!isset($smarty->registered_plugins['function']['absoluteurl'])) {
            $smarty->registerPlugin('function', 'absoluteurl', array(&$this, 'absoluteurl'));
        }
        if (!isset($smarty->registered_plugins['function']['relativeurl'])) {
            $smarty->registerPlugin('function', 'relativeurl', array(&$this, 'relativeurl'));
        }
        if (!isset($smarty->registered_plugins['function']['mediaincpath'])) {
            $smarty->registerPlugin('function', 'mediaincpath', array(&$this, 'mediaincpath'));
        }
        if (!isset($smarty->registered_plugins['function']['getConfig'])) {
            $smarty->registerPlugin('function', 'getConfig', array(&$this, 'getConfig'));
        }
        if (!isset($smarty->registered_plugins['function']['_word'])) {
            $smarty->registerPlugin('function', '_word', array(&$this, 'getWord'));
        }
    }

    public function getWord($params)
    {
        $parts = explode('.', $params['key']);
        if (count($parts) == 2) {
            return Multilanguage::_($parts[1], $parts[0]);
        } elseif (count($parts) == 1) {
            return Multilanguage::_($parts[0]);
        }
        return '##ERROR##';
        /*$key = (isset($params['key']) ? $params['key'] : '');
        $app = (isset($params['app']) ? $params['app'] : $this->getConfigValue('theme') . '_template');
        return Multilanguage::_($key, $app);*/
    }

    /**
     * Smarty - версия функции getConfigValue
     * @param $params
     * @return string
     */
    public function getConfig($params)
    {
        return $this->getConfigValue($params['key']);
    }

    public function mediaincpath($params)
    {
        $mediadata = $params['data'];
        $type = 'normal';
        $inctype = 0;
        if (isset($params['type']) && $params['type'] != '') {
            $type = $params['type'];
        }
        /*if(isset($params['abs']) && $params['abs'] == 1){
            $inctype = 1;
        }elseif(isset($params['root']) && $params['root'] == 1){
            $inctype = 2;
        }*/

        if (isset($params['src']) && ($params['src'] == 2 || $params['src'] == 'root')) {
            $inctype = 2;
        } elseif (isset($params['src']) && ($params['src'] == 1 || $params['src'] == 'abs')) {
            $inctype = 1;
        }

        return $this->createMediaIncPath($mediadata, $type, $inctype);
    }

    function createSimpleMediaIncPath($filename, $type = 'normal', $inctype = 0)
    {
        $mediadata = array(
            'preview' => $filename,
            'normal' => $filename,
            'remote' => 'false'
        );
        return $this->createMediaIncPath($mediadata, $type, $inctype);
    }

    /**
     *
     * @param type $mediadata
     * @param type $type
     * @param type $inctype (0 relative, 1 absolute, 2 root)
     * @return string
     */
    function createMediaIncPath($mediadata, $type = 'normal', $inctype = 0)
    {

        $folder = '';

        if ($inctype == 2) {
            $folder = SITEBILL_DOCUMENT_ROOT;
        } elseif ($inctype == 1) {
            $folder = $this->getServerFullUrl();
        } else {
            $folder = SITEBILL_MAIN_URL;
        }


        if (isset($mediadata['remote']) && $mediadata['remote'] === 'true') {
            $path = $mediadata[$type];
        } else {
            $path = $folder . '/img/data/' . $mediadata[$type];
        }
        return $path;
    }

    /**
     * Return lang postfix for column name in format _lang
     * @param string $curlang Current lang code
     * @return string
     */
    function getLangPostfix($curlang)
    {

        $postfix = '';

        $default_lng = '';
        if (1 == $this->getConfigValue('apps.language.use_default_as_ru')) {
            $default_lng = 'ru';
        } elseif ('' != trim($this->getConfigValue('apps.language.use_as_default'))) {
            $default_lng = trim($this->getConfigValue('apps.language.use_as_default'));
        }

        if ($default_lng != '' && $default_lng == $curlang) {

        } else {
            $postfix = '_' . $curlang;
        }

        return $postfix;
    }


    /**
     * Create url
     * @param string $path - Url request path include query string
     * @param boolean $absolute - Is url must be absolute or relative
     * @param boolean $monolang - Must url have locale prefix (ex. admin section need no locale prefixes)
     * @param string $locale - Url locale prefix (different from requested)
     * @return string
     */
    public function createUrlTpl($path, $absolute = false, $monolang = false, $locale = null)
    {
        $trslashes = self::$_trslashes;

        $alias = '';

        $hash = '';
        $query = '';

        if ($path == '#') {
            return $path;
        }

        $pathparts = explode('#', $path);
        if (isset($pathparts[1])) {
            $hash = $pathparts[1];
        }

        $path = $pathparts[0];


        $pathparts = explode('?', $path);
        if (isset($pathparts[0])) {
            $alias = $pathparts[0];
        }

        if (isset($pathparts[1])) {
            $query = $pathparts[1];
        }

        $alias = trim($alias, '/');
        if ($alias == '#') {
            return $alias . (isset($query) && $query != '' ? '?' . $query : '');
        }

        $parts = array();
        if (!$monolang) {
            if (!is_null($locale) && $locale != '') {
                $parts[] = $locale;
            } elseif (!is_null($locale) && $locale == '') {

            } elseif (isset(self::$_request['request_lang_prefix']) && self::$_request['request_lang_prefix'] != '') {
                $parts[] = self::$_request['request_lang_prefix'];
            }
        }

        if ($alias != '') {
            if (false !== strpos($alias, '.')) {
                $trslashes = '';
            }
            $parts[] = $alias;
        }

        $_alias = (!empty($parts) ? implode('/', $parts) . $trslashes : '');

        if ($absolute) {
            $alias = $this->getServerFullUrl() . ($_alias != '' || $query != '' || $hash != '' ? '/' : '');
        } else {
            $alias = SITEBILL_MAIN_URL . '/';
        }

        $alias = $alias . ($_alias != '' ? $_alias : '') . ($query != '' ? '?' . $query : '') . ($hash != '' ? '#' . $hash : '');

        //$alias = ($absolute ? $this->getServerFullUrl() : SITEBILL_MAIN_URL) . (!empty($parts) ? '/'.implode('/', $parts).$trslashes : (!$absolute ? '/' : ''));
        //$alias = SITEBILL_MAIN_URL . '/' . (self::$current_lang_prefix != '' ? self::$current_lang_prefix.'/' : '') . $alias . ((false === strpos($alias, '.') && $alias != '#') ? self::$_trslashes : '');
        return $alias/*.($query != '' ? '?'.$query : '').($hash != '' ? '#'.$hash : '')*/ ;
    }

    /**
     * Smarty function for absolute url creation
     * @param array $params
     * @return string
     */
    public function absoluteurl($params)
    {
        $path = $params['path'];
        $absolute = true;
        $monolang = false;
        $locale = null;
        if (isset($params['monolang']) && $params['monolang'] == 1) {
            $monolang = true;
        }
        if (isset($params['locale'])) {
            $locale = trim($params['locale']);
        }
        return $this->createUrlTpl($path, $absolute, $monolang, $locale);
    }

    /**
     * Smarty function for relative url creation
     * @param array $params
     * @return string
     */
    public function relativeurl($params)
    {
        $path = $params['path'];
        $absolute = false;
        $monolang = false;
        $locale = null;
        if (isset($params['monolang']) && $params['monolang'] == 1) {
            $monolang = true;
        }
        if (isset($params['locale'])) {
            $locale = trim($params['locale']);
        }
        return $this->createUrlTpl($path, $absolute, $monolang, $locale);
    }

    /**
     * Smarty function for url creation
     * @param array $params
     * @return string
     */
    public function formaturl($params)
    {
        $path = $params['path'];
        $absolute = false;
        $monolang = false;
        $locale = null;

        if (isset($params['abs']) && $params['abs'] == 1) {
            $absolute = true;
        }
        if (isset($params['monolang']) && $params['monolang'] == 1) {
            $monolang = true;
        }
        if (isset($params['locale'])) {
            $locale = trim($params['locale']);
        }
        return $this->createUrlTpl($path, $absolute, $monolang, $locale);
    }

    public function checkCSRFToken($csrf_token)
    {
        list($valid_thru, $token) = explode(':', $csrf_token);
        $n = $valid_thru . ':' . base64_encode(
                hash_hmac(
                    'sha256',
                    $valid_thru . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $_SESSION['key'],
                    $this->getConfigValue('csrf_salt'),
                    true
                )
            );
        if ($n === $csrf_token && $valid_thru >= time()) {
            return true;
        }
        return false;
    }

    public function generateCSRFToken($len = 40)
    {
        $array = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
        $p = array();
        for ($i = 1; $i <= $len; $i++) {
            shuffle($array);
            $p[] = $array[0];
        }
        return implode('', $p);
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

    public function getUserHREF($rid, $external = false, $params = array())
    {
        $parts = array();

        if (false === $this->getConfigValue('apps.seo.user_html_end')) {
            $use_html_end = true;
        } else {
            $use_html_end = (1 === intval($this->getConfigValue('apps.seo.user_html_end')) ? true : false);
        }

        if (false === $this->getConfigValue('apps.seo.user_slash_divider')) {
            $use_slash_divider = false;
        } else {
            $use_slash_divider = (1 === intval($this->getConfigValue('apps.seo.user_slash_divider')) ? true : false);
        }


        if (trim($this->getConfigValue('apps.seo.user_alias')) != '') {
            $user_alias = trim($this->getConfigValue('apps.seo.user_alias'));
        } else {
            $user_alias = 'user';
        }

        if ($use_slash_divider) {
            $user_alias = $user_alias . '/' . $rid;
        } else {
            $user_alias = $user_alias . $rid;
        }

        if ($use_html_end) {
            $user_alias = $user_alias . '.html';
        } else {
            $user_alias = $user_alias . self::$_trslashes;
        }
        if ($this->getConfigValue('apps.agents.enable')) {
            $user_alias = $this->getConfigValue('apps.agents.alias') . '/' . $rid . self::$_trslashes;
        }


        $href = '';
        if ($external) {
            $href = $this->createUrlTpl($user_alias, true);
        } else {
            $href = $this->createUrlTpl($user_alias);
        }
        return $href;
    }

    public function getRealtyHREF($rid, $external = false, $params = array())
    {
        $parts = array();

        if (isset($params['topic_id'])) {
            $topic_id = intval($params['topic_id']);
        } else {
            $topic_id = 0;
        }

        if (isset($params['alias'])) {
            $alias = $params['alias'];
        } else {
            $alias = '';
        }

        if (trim($this->getConfigValue('apps.seo.realty_alias')) != '') {
            $realty_alias = trim($this->getConfigValue('apps.seo.realty_alias'));
        } else {
            $realty_alias = 'realty';
        }


        /* $trailing_slashe = '/';
          if (1 == (int) $this->getConfigValue('apps.seo.no_trailing_slashes')) {
          $trailing_slashe = '';
          } */

        $trailing_slashe = self::$_trslashes;

        if (1 == $this->getConfigValue('apps.seo.level_enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/structure/structure_manager.php');
            $Structure_Manager = new Structure_Manager();
            $category_structure = $Structure_Manager->loadCategoryStructure();
            if (isset($category_structure['catalog'][$topic_id]) && $category_structure['catalog'][$topic_id]['url'] != '') {
                $parts[] = $category_structure['catalog'][$topic_id]['url'];
            }
        }

        if (1 == $this->getConfigValue('apps.seo.data_alias_enable') && $alias != '') {
            $parts[] = $alias;
        } elseif (1 == $this->getConfigValue('apps.seo.html_prefix_enable')) {
            $trailing_slashe = '';
            $parts[] = $realty_alias . $rid . '.html';
        } else {
            $parts[] = $realty_alias . $rid;
        }
        $href = '';

        /*if(Sitebill::$_request['request_lang_prefix'] != ''){
            array_unshift($parts, Sitebill::$_request['request_lang_prefix']);
        }*/

        if ($external) {
            /*$href = implode('/', $parts);
            if ($href != '') {
                $href .= $trailing_slashe;
            }
            $href = $this->getServerFullUrl() . '/' . $href;*/
            $href = $this->createUrlTpl(implode('/', $parts), true);
        } else {
            /*array_unshift($parts, SITEBILL_MAIN_URL);
            $href = implode('/', $parts);
            if ($href != '') {
                $href .= $trailing_slashe;
            }*/
            $href = $this->createUrlTpl(implode('/', $parts));
        }
        return $href;
    }

    /*
     * return nonslashed full net url
     */

    public function getServerFullUrl($domain_only = false)
    {
        return (1 === (int)$this->getConfigValue('work_on_https') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . (!$domain_only ? SITEBILL_MAIN_URL : '');
    }

    public function getMediaDocsDir()
    {
        return '/img/mediadocs/';
    }

    public function getImgDataDir()
    {
        return '/img/data/';
    }

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
            } else {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/sitebill_krascap.php';
                $grid_constructor_full_path = SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/frontend/grid/grid_constructor.php';
                require_once $grid_constructor_full_path;
                self::$_grid_constructor_local = new Grid_Constructor();
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

    public static function setRequest($lang)
    {

        $r['request_lang_prefix'] = '';
        $r['locale'] = $lang;

        $SConfig = SConfig::getInstance();
        if (1 == intval($SConfig->getConfigValue('apps.language.use_langs')) && 1 == intval($SConfig->getConfigValue('apps.language.prefixmode'))) {

            $prefix_list = array();
            $prefixlistconf = trim($SConfig->getConfigValue('apps.language.language_prefix_list'));
            if ($prefixlistconf !== '') {
                $prefix_pairs = explode('|', $prefixlistconf);
                if (count($prefix_pairs) > 0) {
                    foreach ($prefix_pairs as $lp) {
                        list($pr, $lo) = explode('=', $lp);
                        $prefix_list[$pr] = $lo;
                    }
                }
            }

            foreach ($prefix_list as $pr => $lo) {
                if ($lo == $lang) {
                    $r['request_lang_prefix'] = $pr;
                    $r['locale'] = $lo;
                    break;
                }
            }
        }


        self::$_request = $r;
    }

    public static function initRequest()
    {
        if (!is_null(self::$_request)) {
            return;
        }

        $r = array();
        $r['clearRequestUri'] = null;
        $r['request_lang_prefix'] = '';
        $r['locale'] = '';

        $REQUEST_URI = $_SERVER['REQUEST_URI'];
        $REQUEST_URI = str_replace('\\', '/', $REQUEST_URI);
        $REQUEST_URI = ltrim($REQUEST_URI, '/');
        $parts = explode('/', $REQUEST_URI);
        $SConfig = SConfig::getInstance();
        if (1 == intval($SConfig->getConfigValue('apps.language.use_langs')) && 1 == intval($SConfig->getConfigValue('apps.language.prefixmode'))) {

            $prefix_list = array();
            $prefixlistconf = trim($SConfig->getConfigValue('apps.language.language_prefix_list'));
            if ($prefixlistconf !== '') {
                $prefix_pairs = explode('|', $prefixlistconf);
                if (count($prefix_pairs) > 0) {
                    foreach ($prefix_pairs as $lp) {
                        list($pr, $lo) = explode('=', $lp);
                        $prefix_list[$pr] = $lo;
                    }
                }
            }

            if (!empty($prefix_list) && isset($prefix_list[$parts[0]])) {
                $_SESSION['_lang'] = $prefix_list[$parts[0]];
                $r['request_lang_prefix'] = $parts[0];
                $r['locale'] = $prefix_list[$parts[0]];
            } elseif (!empty($prefix_list) && isset($prefix_list[''])) {
                $_SESSION['_lang'] = $prefix_list[''];
                $r['request_lang_prefix'] = '';
                $r['locale'] = $prefix_list[''];
            }

            /*$langlist = trim($SConfig->getConfigValue('apps.language.languages'));

            if ($langlist !== '') {
                $lang_pairs = explode('|', $langlist);
                if (count($lang_pairs) > 0) {
                    foreach ($lang_pairs as $lp) {
                        $matches = array();
                        if (preg_match('/([a-z]+)=(.+)/', trim($lp), $matches)) {
                            $langs[$matches[1]] = $matches[2];
                        }
                    }
                }
                if(isset($langs[$parts[0]])){
                    $_SESSION['_lang'] = $parts[0];
                    $r['request_lang_prefix'] = $parts[0];
                    $r['locale'] = $parts[0];
                }
            }*/
        }


        self::$_request = $r;
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

    /**
     * @param timestamp $date
     * @return timestamp
     */
    static function addMonthToDate($date)
    {
        $now_day = date('j', $date);
        $now_month = date('n', $date);
        $now_year = date('Y', $date);
        $now_month_days = date('t', $date);
        $time = date('H:i:s', $date);

        $next_year = $now_year;
        $next_month = $now_month + 1;
        if ($next_month > 12) {
            $next_month = 1;
            $next_year += 1;
        }

        $next_month_days = date('t', strtotime($next_year . '-' . ($next_month < 10 ? '0' . $next_month : $next_month) . '-01'));

        if ($now_day <= $next_month_days) {
            $next_day = $now_day;
        } elseif ($now_day == $now_month_days) {
            $next_day = $next_month_days;
        } else {
            $next_day = $next_month_days;
        }
        return strtotime($next_year . '-' . ($next_month < 10 ? '0' . $next_month : $next_month) . '-' . ($next_day < 10 ? '0' . $next_day : $next_day) . ' ' . $time);
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

    function sanitize($value, $flags)
    {
        if (is_array($value)) {
            $value = $this->htmlspecialchars($value, $flags);
        } else {
            $value = htmlspecialchars($this->escape($value), $flags, SITE_ENCODING);
        }
        return $value;
    }

    function escape($text)
    {
        return $text;
    }

    public function getAdminTplFolder()
    {
        return SITEBILL_DOCUMENT_ROOT . '/apps/admin/admin/template1';
    }

    /**
     * Get breadcrumbs
     * @param array $items
     * @return string
     */
    function get_breadcrumbs($items)
    {
        if (count($items) > 0) {
            $this->template->assert('breadcrumbs_array', $items);
            return implode(' / ', $items);
        }
        return '';
    }

    function get_ajax_functions()
    {
        $rs = '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/refresher.functions.js"></script>';
        return $rs;
    }

    /**
     * Get apps template full path
     * @param string $apps_name
     * @param string $theme
     * @param string $template_value
     * @return boolean
     */
    function get_apps_template_full_path($apps_name, $theme, $template_value)
    {
        if (!file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/' . $apps_name . '/' . $template_value)) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/' . $apps_name . '/site/template/' . $template_value)) {
                return SITEBILL_DOCUMENT_ROOT . '/apps/' . $apps_name . '/site/template/' . $template_value;
            } else {
                echo Multilanguage::_('L_FILE') . " " . SITEBILL_DOCUMENT_ROOT . '/apps/' . $apps_name . '/site/template/' . $template_value . ' не найден';
                exit;
            }
        } else {
            return SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $theme . '/' . $apps_name . '/' . $template_value;
        }
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

    /**
     * Get session key
     * @param void
     * @return string
     */
    function get_session_key()
    {
        return @$_SESSION['key'];
    }

    /**
     * Delete session by key
     * @param string $session_key
     * @return void
     */
    function delete_session_key($session_key)
    {
        $DBC = DBC::getInstance();
        $query = "DELETE FROM " . DB_PREFIX . "_session WHERE session_key=?";
        $stmt = $DBC->query($query, array((string)$session_key));
        return $_SESSION['key'];
    }

    function setSessionUserId($user_id)
    {
        self::$Heaps['session']['user_id'] = $user_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_id_value'] = $user_id;
    }

    /**
     * Get session user ID
     * @param void
     * @return int
     */
    function getSessionUserId()
    {
        $key = (isset($_SESSION['key']) ? $_SESSION['key'] : '');
        if (isset(self::$Heaps['session']['user_id']) && self::$Heaps['session']['user_id'] != '') {
            return self::$Heaps['session']['user_id'];
        }
        if ($key != '') {
            $DBC = DBC::getInstance();
            $query = "SELECT user_id FROM " . DB_PREFIX . "_session WHERE session_key=? LIMIT 1";
            $stmt = $DBC->query($query, array((string)$key));
            if ($stmt) {
                $ar = $DBC->fetch($stmt);
                $user_id = $ar['user_id'];
                if ($user_id != '' and $user_id != 0) {
                    $this->user_id = $user_id;
                    self::$Heaps['session']['user_id'] = $user_id;
                    //$init->setUserId($user_id);
                    return $user_id;
                } else {
                    $this->user_id = 0;
                    return 0;
                }
            }
        }
        $this->user_id = 0;
        return 0;
    }

    /**
     * Load uploadify images
     * @param string $session_code session code
     * @return array
     */
    function load_uploadify_images($session_code = '', $element_name = '')
    {
        $ra = array();

        $DBC = DBC::getInstance();
        if ($element_name == '') {
            $query = 'SELECT * FROM ' . UPLOADIFY_TABLE . ' WHERE `session_code`=? AND (`element`=? or `element` is null) ORDER BY `uploadify_id`';
            $stmt = $DBC->query($query, array((string)$session_code, ''));
        } else {
            $query = 'SELECT * FROM ' . UPLOADIFY_TABLE . ' WHERE `session_code`=? AND `element`=? ORDER BY `uploadify_id`';
            $stmt = $DBC->query($query, array((string)$session_code, $element_name));
        }
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ra[] = $ar['file_name'];
            }
        }
        if (empty($ra)) {
            return false;
        } else {
            return $ra;
        }
    }

    /**
     * Edit image
     * @param string $action action
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record ID
     * @return boolean
     */
    function editImageMulti($action, $table_name, $key, $record_id, $name_template = '')
    {
        if (!isset($record_id) or $record_id == 0) {
            return false;
        }
        $path = SITEBILL_DOCUMENT_ROOT . '/img/data/';
        $uploadify_path = SITEBILL_DOCUMENT_ROOT . $this->uploadify_dir;
        $session_key = (string)$this->get_session_key();
        $ra = array();
        //update image
        $images = $this->load_uploadify_images($session_key);
        if (!$images) {
            //Попробуем получить фото из внешнего запроса
            $images = $this->getExternalUploadifyImageArray();
            if (!$images) {
                return false;
            }
        }

        if ($action == 'data') {
            $DBC = DBC::getInstance();

            $avial_count = (int)$this->getConfigValue('photo_per_data');
            if ($avial_count == 0) {
                $avial_count = 1000;
            } else {
                $loaded = 0;
                $query = 'SELECT COUNT(data_image_id) AS cnt FROM ' . DB_PREFIX . '_' . $table_name . '_image WHERE ' . $key . '=' . $record_id;
                $stmt = $DBC->query($query);
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $loaded = (int)$ar['cnt'];
                }
                $avial_count = $avial_count - $loaded;
                if ($avial_count < 1) {
                    $this->delete_uploadify_images($session_key);
                    return false;
                }
            }

            if (count($images) > $avial_count) {
                $images = array_slice($images, 0, $avial_count);
            }
        }


        foreach ($images as $image_name) {
            $i++;
            $need_prv = 0;
            $preview_name = '';
            if (!empty($image_name)) {
                $arr = explode('.', $image_name);
                $ext = strtolower($arr[count($arr) - 1]);

                if (function_exists('exif_read_data')) {
                    $exif = exif_read_data($uploadify_path . $image_name, 0, true);
                    if (false === empty($exif['IFD0']['Orientation'])) {
                        switch ($exif['IFD0']['Orientation']) {
                            case 8:
                                $this->rotateImageInDestination($uploadify_path . $image_name, $uploadify_path . $image_name, 90);
                                break;
                            case 3:
                                $this->rotateImageInDestination($uploadify_path . $image_name, $uploadify_path . $image_name, 180);
                                break;
                            case 6:
                                $this->rotateImageInDestination($uploadify_path . $image_name, $uploadify_path . $image_name, -90);
                                break;
                        }
                    }
                }

                if ((1 == $this->getConfigValue('seo_photo_name_enable')) and ($name_template != '')) {
                    $name_template = substr($name_template, 0, 150);
                    if ($i == 0) {
                        $preview_name_no_ext = $name_template;
                        $prv_no_ext = $name_template . "_prev";
                    } else {
                        $preview_name_no_ext = $name_template . "_" . $i;
                        $prv_no_ext = $name_template . "_prev" . $i;
                    }

                    if (file_exists($path . $preview_name_no_ext . "." . $ext)) {
                        $rand = rand(0, 1000);
                        while (file_exists($path . $preview_name_no_ext . "_" . $rand . "." . $ext)) {
                            $rand = rand(0, 1000);
                        }
                        $preview_name = $preview_name_no_ext . "_" . $rand . "." . $ext;
                        $prv = $prv_no_ext . "_" . $rand . "." . $ext;
                    } else {
                        $preview_name = $preview_name_no_ext . "." . $ext;
                        $prv = $prv_no_ext . "." . $ext;
                    }
                } else {
                    $preview_name = "img" . uniqid() . '_' . time() . "_" . $i . "." . $ext;
                    $prv = "prv" . uniqid() . '_' . time() . "_" . $i . "." . $ext;
                    $preview_name_tmp = "_tmp" . uniqid() . '_' . time() . "_" . $i . "." . $ext;
                }

                if (in_array($ext, array('jpg', 'jpeg', 'gif', 'png'))) {

                    //echo $action.'_image_big_width';

                    $big_width = $this->getConfigValue($action . '_image_big_width');
                    if ($big_width == '') {
                        $big_width = $this->getConfigValue('news_image_big_width');
                    }
                    $big_height = $this->getConfigValue($action . '_image_big_height');
                    if ($big_height == '') {
                        $big_height = $this->getConfigValue('news_image_big_height');
                    }

                    $preview_width = $this->getConfigValue($action . '_image_preview_width');
                    if ($preview_width == '') {
                        $preview_width = $this->getConfigValue('news_image_preview_width');
                    }
                    $preview_height = $this->getConfigValue($action . '_image_preview_height');
                    if ($preview_height == '') {
                        $preview_height = $this->getConfigValue('news_image_preview_height');
                    }

                    if (defined('STR_MEDIA') && STR_MEDIA == Sitebill::MEDIA_SAVE_FOLDER) {
                        if (defined('STR_MEDIA_FOLDERFDAYS') && STR_MEDIA_FOLDERFDAYS === 1) {
                            $foldeformat = 'Ymd';
                        } else {
                            $foldeformat = 'Ym';
                        }
                        $folder_name = date($foldeformat, time());
                        $locs = MEDIA_FOLDER . '/' . $folder_name;
                        if (!is_dir($locs)) {
                            mkdir($locs);
                        }
                        $preview_name = $folder_name . '/' . $preview_name;
                        $prv = $folder_name . '/' . $prv;
                    }

                    $rn = $this->makePreview($uploadify_path . $image_name, $path . $preview_name, $big_width, $big_height, $ext, 1);
                    if (1 == $this->getConfigValue('apps.realty.preview_smart_resizing') && $action == 'data') {
                        $rp = $this->makePreview($uploadify_path . $image_name, $path . $prv, $preview_width, $preview_height, $ext, 'smart');
                    } else {
                        $rp = $this->makePreview($uploadify_path . $image_name, $path . $prv, $preview_width, $preview_height, $ext, 'width');
                    }
                    if ($rp && $rn) {
                        if (1 == $this->getConfigValue('apps.watermark.printanywhere')) {
                            $this->doWatermark($path . $preview_name, $path . $prv);
                        }

                        /* На случай, если сервер выставляет на загруженные файлы права 0600 */
                        chmod($path . $preview_name, 0644);
                        chmod($path . $prv, 0644);
                        /**/

                        $ra[$i]['preview'] = $prv;
                        $ra[$i]['normal'] = $preview_name;
                    }
                }
            }
        }

        $this->add_image_records($ra, $table_name, $key, $record_id);
        $this->delete_uploadify_images($this->get_session_key());
        return $ra;
    }

    function doWatermark($normal_image, $preview_image)
    {
        if ($this->getConfigValue('is_watermark')) {
            if (!$this->watermark_inst) {
                require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/watermark/watermark.php';
                $this->watermark_inst = new Watermark();
                $this->watermark_inst->setPosition($this->getConfigValue('apps.watermark.position'));
                $this->watermark_inst->setOffsets(array(
                    $this->getConfigValue('apps.watermark.offset_left'),
                    $this->getConfigValue('apps.watermark.offset_top'),
                    $this->getConfigValue('apps.watermark.offset_right'),
                    $this->getConfigValue('apps.watermark.offset_bottom')
                ));
            }

            $this->watermark_inst->printWatermark($normal_image);
            if ($this->getConfigValue('apps.watermark.preview_enable')) {
                $this->watermark_inst->printWatermark($preview_image, true);
            }
            return true;
        }
        return false;
    }

    /**
     * Эта функция устанавливает массив с картинками для эмитации загрузки картинок в UPLOADIFY
     * Используется в APPS.API для загрузки картинок из мобильного приложения
     * @param $_image_array - массив с картинками
     * @return void
     */
    function setExternalUploadifyImageArray($_image_array)
    {
        $this->external_uploadify_image_array = $_image_array;
    }

    function getExternalUploadifyImageArray()
    {
        return $this->external_uploadify_image_array;
    }

    function get_docuploads_extensions()
    {
        return array('docx', 'doc', 'xls', 'pdf', 'xlsx', 'jpg', 'jpeg', 'png', 'webp', 'mp4');
    }

    function appendDocUploads($table, $field, $pk_field, $record_id)
    {
        $field_name = $field['name'];
        $parameters = $field['parameters'];
        $session_key = (string)$this->get_session_key();
        $action = $table;
        if (!isset($record_id) || $record_id == 0) {
            return false;
        }

        $DBC = DBC::getInstance();

        $path = SITEBILL_DOCUMENT_ROOT . '/img/mediadocs/';
        $uploadify_path = SITEBILL_DOCUMENT_ROOT . $this->uploadify_dir;

        $ra = array();
        $uploads = $this->load_uploadify_images($session_key, $field_name);
        if (!$uploads) {
            return false;
        }


        $query = 'SELECT `' . $field_name . '` FROM ' . DB_PREFIX . '_' . $table . ' WHERE `' . $pk_field . '`=? LIMIT 1';

        $stmt = $DBC->query($query, array($record_id));
        if (!$stmt) {
            return false;
        }
        $ar = $DBC->fetch($stmt);

        if ($ar[$field_name] === '') {
            $attached_yet = array();
        } else {
            $attached_yet = unserialize($ar[$field_name]);
        }
        //print_r($attached_yet);
        $i = 0;
        $max_filesize = (int)str_replace('M', '', ini_get('upload_max_filesize'));
        if (isset($parameters['max_file_size']) && (int)$parameters['max_file_size'] != 0) {
            $max_filesize = (int)$parameters['max_file_size'];
        }
        if ( isset($parameters['accepted']) ) {
            $av = explode(',', $parameters['accepted']);
        }
        $allowed_exts = $this->get_docuploads_extensions();
        if (!empty($av)) {
            foreach ($av as $k => $v) {
                $v = trim(ltrim($v, '.'));
                if ($v == '') {
                    unset($av[$k]);
                } else {
                    $av[$k] = $v;
                }
            }
        }
        if (!empty($av)) {
            $allowed_exts = $av;
        }

        foreach ($uploads as $image_name) {
            $i++;

            if (!empty($image_name)) {

                $arr = explode('.', $image_name);
                $ext = strtolower(end($arr));

                if (!in_array($ext, $allowed_exts)) {
                    continue;
                }
                $filesize = filesize($uploadify_path . $image_name) / (1024 * 1024);
                if ($filesize > $max_filesize) {
                    continue;
                }
                if ($this->getConfigValue('use_native_file_name_on_uploadify')) {
                    $path_parts = pathinfo($image_name);
                    $file_name = $path_parts['filename'] . '.' . $path_parts['extension'];
                } else {
                    $file_name = "doc" . uniqid() . '_' . time() . '_' . $i . '.' . $ext;
                }
                $file_index = 1;
                while (file_exists($path . $file_name)) {
                    $i++;
                    if ($this->getConfigValue('use_native_file_name_on_uploadify')) {
                        $file_name = $path_parts['filename'] . '(' . $file_index . ')' . '.' . $path_parts['extension'];
                    } else {
                        $file_name = "doc" . uniqid() . '_' . time() . '_' . $i . '.' . $ext;
                    }
                    $file_index++;
                }


                if (copy($uploadify_path . $image_name, $path . $file_name)) {
                    chmod($path . $file_name, 0644);
                    /**/
                    $ra[$i]['preview'] = '';
                    $ra[$i]['normal'] = $file_name;

                    $attached_yet[] = array('preview' => '', 'normal' => $file_name, 'type' => 'doc', 'mime' => $ext);
                }
            }
        }

        $query = 'UPDATE ' . DB_PREFIX . '_' . $table . ' SET `' . $field_name . '`=? WHERE `' . $pk_field . '`=?';
        if (count($attached_yet) > 0) {
            $stmt = $DBC->query($query, array(serialize($attached_yet), $record_id));
        } else {
            $stmt = $DBC->query($query, array('', $record_id));
        }
        //$this->add_image_records($ra, $table_name, $key, $record_id);
        $this->delete_uploadify_images($session_key, $field_name);
        return $ra;
    }

    function appendUploads($table, $field, $pk_field, $record_id, $name_template = '')
    {
        $field_name = $field['name'];
        $uploadify_field_name = $field_name;
        if (isset($field['uploadify_field_name'])) {
            $uploadify_field_name = $field['uploadify_field_name'];
        }
        $parameters = $field['parameters'];
        $session_key = (string)$this->get_session_key();


        $action = $table;
        if (!isset($record_id) || $record_id == 0) {
            //$this->riseError('record id is null');
            return false;
        }

        $DBC = DBC::getInstance();

        $path = SITEBILL_DOCUMENT_ROOT . '/img/data/';
        $uploadify_path = SITEBILL_DOCUMENT_ROOT . $this->uploadify_dir;

        $ra = array();
        $uploads = $this->load_uploadify_images($session_key, $uploadify_field_name);
        if (!$uploads) {
            $uploads = $this->getExternalUploadifyImageArray();
            if (!$uploads) {
                // $this->riseError('empty uploads');
                return false;
            }
        }

        if (isset($parameters['max_img_count']) && $parameters['max_img_count'] != '') {
            $max_img_count = intval($parameters['max_img_count']);
        } else {
            $max_img_count = -1;
        }

        if (isset($parameters['max_img_count_ext']) && '' != $parameters['max_img_count_ext']) {
            $maximgcountextendrules = $parameters['max_img_count_ext'];
        } else {
            $maximgcountextendrules = '';
        }
        $controlledfields = array();
        $maxsizerules = array();
        if ($maximgcountextendrules != '') {
            $rulesparts = explode(':', $maximgcountextendrules);
            $size = intval($rulesparts[0]);
            if ($size > 0 && count($rulesparts) > 1) {
                unset($rulesparts[0]);
                $conditions = array();
                foreach ($rulesparts as $rule) {
                    $oneruleparts = explode(',', $rule);
                    if (count($oneruleparts) == 3) {
                        $controlledfields[$oneruleparts[0]] = 0;
                        $conditions[] = $oneruleparts;
                    }
                }
                $maxsizerules[] = array(
                    'size' => $size,
                    'conditions' => $conditions
                );
            }
        }

        $selectedfields = array();

        $selectedfields[] = '`' . $field_name . '`';
        if (!empty($controlledfields)) {
            foreach ($controlledfields as $controlledfield => $name) {
                $selectedfields[] = '`' . $controlledfield . '`';
            }
        }

        //$query = 'SELECT `' . $field_name . '` FROM ' . DB_PREFIX . '_' . $table . ' WHERE `' . $pk_field . '`=? LIMIT 1';
        $query = 'SELECT ' . implode(', ', $selectedfields) . ' FROM ' . DB_PREFIX . '_' . $table . ' WHERE `' . $pk_field . '`=? LIMIT 1';

        $stmt = $DBC->query($query, array($record_id));
        if (!$stmt) {
            $this->riseError('query = ' . $query . ', db error: ' . $DBC->getLastError());
            return false;
        }
        $advertdata = $DBC->fetch($stmt);

        if ($advertdata[$field_name] === '') {
            $attached_yet = array();
        } else {
            $attached_yet = unserialize($advertdata[$field_name]);
        }


        $i = 0;
        $max_filesize = (int)str_replace('M', '', ini_get('upload_max_filesize'));
        if (isset($parameters['max_file_size']) && (int)$parameters['max_file_size'] != 0) {
            $max_filesize = (int)$parameters['max_file_size'];
        }

        if ($max_img_count > -1) {
            if (!empty($maxsizerules)) {
                foreach ($maxsizerules as $maxsizerule) {
                    $condsok = true;
                    foreach ($maxsizerule['conditions'] as $condition) {
                        $operand = $condition[1];
                        $field = $condition[0];
                        $value = $condition[2];
                        switch ($operand) {
                            case 'eq' :
                            {
                                if ($advertdata[$field] != $value) {
                                    $condsok = false;
                                }
                                break;
                            }
                            case 'neq' :
                            {
                                if ($advertdata[$field] == $value) {
                                    $condsok = false;
                                }
                                break;
                            }
                            case 'gt' :
                            {
                                if ($advertdata[$field] <= $value) {
                                    $condsok = false;
                                }
                                break;
                            }
                            case 'lt' :
                            {
                                if ($advertdata[$field] >= $value) {
                                    $condsok = false;
                                }
                                break;
                            }
                        }
                    }

                    if ($condsok) {
                        $max_img_count = $maxsizerule['size'];
                        break;
                    }

                }
            }
        }


        if ($max_img_count > -1) {
            $last_count = $max_img_count - count($attached_yet);
            if ($last_count > 0) {
                $uploads = array_slice($uploads, 0, $last_count);
            } else {
                $uploads = array();
            }
        }
        if (!empty($uploads)) {

            $folder_name = '';
            if (defined('STR_MEDIA') && STR_MEDIA == Sitebill::MEDIA_SAVE_FOLDER) {
                if (defined('STR_MEDIA_FOLDERFDAYS') && STR_MEDIA_FOLDERFDAYS === 1) {
                    $foldeformat = 'Ymd';
                } else {
                    $foldeformat = 'Ym';
                }
                $folder_name = date($foldeformat, time());
                $locs = MEDIA_FOLDER . '/' . $folder_name;
                if (!is_dir($locs)) {
                    mkdir($locs);
                }
                $preview_name = $folder_name . '/' . $preview_name;
                $prv = $folder_name . '/' . $prv;
            } elseif (defined('STR_MEDIA_DIVIDED') && STR_MEDIA_DIVIDED == 1) {
                $fold1 = rand(0, 99);
                $fold2 = rand(0, 99);
                if ($fold1 < 10) {
                    $fold1 = '0' . $fold1;
                }
                if ($fold2 < 10) {
                    $fold2 = '0' . $fold2;
                }
                $folder_name = $fold1 . '/' . $fold2;
                $locs = MEDIA_FOLDER . '/' . $fold1;
                if (!is_dir($locs)) {
                    mkdir($locs);
                }
                $locs = MEDIA_FOLDER . '/' . $fold1 . '/' . $fold2;
                if (!is_dir($locs)) {
                    mkdir($locs);
                }
                /*
                 * Вариант вложенных папок для стандартных настроек
                 * папки от /000/000/ до /1f4/1f4/
                 * 500 вариантов / 500 вариантов
                 * в итоге не более 500 вариантов папок на одном уровне
                $fold1 = dechex(rand(0, 500));
                $fold2 = dechex(rand(0, 500));
                if(strlen($fold1) == 1){
                    $fold1 = '00' . $fold1;
                }elseif(strlen($fold1) == 2){
                    $fold1 = '0' . $fold1;
                }
                if(strlen($fold2) < 2){
                    $fold2 = '0' . $fold2;
                }elseif(strlen($fold2) == 2){
                    $fold1 = '0' . $fold2;
                }
                $folder_name = $fold1 . '/' . $fold2;
                $locs = MEDIA_FOLDER . '/' . $fold1;
                if (!is_dir($locs)) {
                    mkdir($locs);
                }
                $locs = MEDIA_FOLDER . '/' . $fold1 . '/' . $fold2;
                if (!is_dir($locs)) {
                    mkdir($locs);
                }
                */

            } else {
                $folder_name = '';
            }

            $uniq_file_name = uniqid() . '_' . time();

            foreach ($uploads as $image_name) {
                $i++;
                $need_prv = 0;
                $preview_name = '';
                $filesize = filesize($uploadify_path . $image_name) / (1024 * 1024);
                if ($filesize > $max_filesize) {
                    continue;
                }
                if (!empty($image_name)) {
                    $arr = explode('.', $image_name);
                    $ext = strtolower(end($arr));


                    if (function_exists('exif_read_data')) {
                        $exif = @exif_read_data($uploadify_path . $image_name, 0, true);
                        if (isset($exif['IFD0']) && isset($exif['IFD0']['Orientation']) && false === empty($exif['IFD0']['Orientation'])) {
                            switch ($exif['IFD0']['Orientation']) {
                                case 8:
                                    $this->rotateImageInDestination($uploadify_path . $image_name, $uploadify_path . $image_name, 90);
                                    break;
                                case 3:
                                    $this->rotateImageInDestination($uploadify_path . $image_name, $uploadify_path . $image_name, 180);
                                    break;
                                case 6:
                                    $this->rotateImageInDestination($uploadify_path . $image_name, $uploadify_path . $image_name, -90);
                                    break;
                            }
                        }
                    }
                    //$ext=strtolower($arr[count($arr)-1]);
                    if ((1 == $this->getConfigValue('seo_photo_name_enable')) and ($name_template != '')) {
                        $name_template = substr($name_template, 0, 150);
                        if ($i == 0) {
                            $preview_name_no_ext = $name_template;
                            $prv_no_ext = $name_template . "_prev";
                        } else {
                            $preview_name_no_ext = $name_template . "_" . $i;
                            $prv_no_ext = $name_template . "_prev" . $i;
                        }

                        if (file_exists($path . $preview_name_no_ext . "." . $ext)) {
                            $rand = rand(0, 1000);
                            while (file_exists($path . $preview_name_no_ext . "_" . $rand . "." . $ext)) {
                                $rand = rand(0, 1000);
                            }
                            $preview_name = $preview_name_no_ext . "_" . $rand . "." . $ext;
                            $prv = $prv_no_ext . "_" . $rand . "." . $ext;
                        } else {
                            $preview_name = $preview_name_no_ext . "." . $ext;
                            $prv = $prv_no_ext . "." . $ext;
                        }
                    } else {
                        $nm = $uniq_file_name . '_' . $i;
                        $preview_name = 'img' . $nm . "." . $ext;
                        $prv = "prv" . $nm . "." . $ext;
                        $preview_name_tmp = "_tmp" . uniqid() . '_' . time() . "_" . $i . "." . $ext;
                    }

                    if (in_array($ext, array('jpg', 'jpeg', 'gif', 'png', 'webp'))) {
                        $big_width = $this->getConfigValue($action . '_image_big_width');
                        if ($big_width == '') {
                            $big_width = $this->getConfigValue('data_image_big_width');
                        }
                        $big_height = $this->getConfigValue($action . '_image_big_height');
                        if ($big_height == '') {
                            $big_height = $this->getConfigValue('data_image_big_height');
                        }

                        $preview_width = $this->getConfigValue($action . '_image_preview_width');
                        if ($preview_width == '') {
                            $preview_width = $this->getConfigValue('data_image_preview_width');
                        }
                        $preview_height = $this->getConfigValue($action . '_image_preview_height');
                        if ($preview_height == '') {
                            $preview_height = $this->getConfigValue('data_image_preview_height');
                        }

                        if (isset($parameters['norm_width']) && (int)$parameters['norm_width'] != 0) {
                            $big_width = (int)$parameters['norm_width'];
                        }

                        if (isset($parameters['norm_height']) && (int)$parameters['norm_height'] != 0) {
                            $big_height = (int)$parameters['norm_height'];
                        }

                        if (isset($parameters['prev_width']) && (int)$parameters['prev_width'] != 0) {
                            $preview_width = (int)$parameters['prev_width'];
                        }

                        if (isset($parameters['prev_height']) && (int)$parameters['prev_height'] != 0) {
                            $preview_height = (int)$parameters['prev_height'];
                        }

                        if ($folder_name != '') {
                            $preview_name = $folder_name . '/' . $preview_name;
                            $prv = $folder_name . '/' . $prv;
                        }

                        if (isset($parameters['normal_smart_resizing']) && intval($parameters['normal_smart_resizing']) == 1) {
                            $rn = $this->makePreview($uploadify_path . $image_name, $path . $preview_name, $big_width, $big_height, $ext, 'smart');
                        } else {
                            $rn = $this->makePreview($uploadify_path . $image_name, $path . $preview_name, $big_width, $big_height, $ext, 1);
                        }

                        $preview_smart_resizing = false;
                        if (isset($parameters['preview_smart_resizing'])) {
                            if (intval($parameters['preview_smart_resizing']) == 1) {
                                $preview_smart_resizing = true;
                            } else {
                                $preview_smart_resizing = false;
                            }
                        } elseif (1 == $this->getConfigValue('apps.realty.preview_smart_resizing') && $action == 'data') {
                            $preview_smart_resizing = true;
                        }

                        if ($preview_smart_resizing) {
                            $rp = $this->makePreview($uploadify_path . $image_name, $path . $prv, $preview_width, $preview_height, $ext, 'smart');
                        } else {
                            $rp = $this->makePreview($uploadify_path . $image_name, $path . $prv, $preview_width, $preview_height, $ext, 'width');
                        }


                        if ($rn && $rp) {
                            if (1 == $this->getConfigValue('apps.watermark.printanywhere')) {
                                $this->doWatermark($path . $preview_name, $path . $prv);
                            }

                            /* На случай, если сервер выставляет на загруженные файлы права 0600 */
                            chmod($path . $preview_name, 0644);
                            chmod($path . $prv, 0644);
                            /**/
                            $ra[$i]['preview'] = $prv;
                            $ra[$i]['normal'] = $preview_name;
                        }
                        $preview_params = $this->get_image_info($path . $prv);
                        $normal_params = $this->get_image_info($path . $preview_name);

                    } elseif (in_array($ext, array('svg'))) {
                        if ($folder_name != '') {
                            $preview_name = $folder_name . '/' . $image_name;
                        } else {
                            $preview_name = $image_name;
                        }
                        $prv = $preview_name;
                        $this->makeMove($uploadify_path . $image_name, $path . $preview_name);

                        $ra[$i]['preview'] = $preview_name;
                        $ra[$i]['normal'] = $preview_name;
                        $rn = true;
                        $rp = true;
                        $preview_params = $this->get_svg_info($path . $preview_name);
                        $normal_params = $this->get_svg_info($path . $preview_name);
                    }
                    if ($rn && $rp) {
                        if ($this->getConfigValue('apps.sharder.enable')) {
                            $shard_result = $this->sharding(array($preview_name, $prv));
                            if ($shard_result) {
                                list($preview_name, $prv) = $shard_result;
                                $remote = 'true';
                            }
                        } else {
                            $remote = 0;
                        }
                        $attached_yet[] = array(
                            'preview' => $prv,
                            'normal' => $preview_name,
                            'type' => 'graphic',
                            'mime' => $ext,
                            'remote' => $remote,
                            'preview_params' => $preview_params,
                            'normal_params' => $normal_params,
                        );
                    }
                }
            }

            $query = 'UPDATE ' . DB_PREFIX . '_' . $table . ' SET `' . $field_name . '`=? WHERE `' . $pk_field . '`=?';
            if (is_array($attached_yet) and count($attached_yet) > 0) {
                $stmt = $DBC->query($query, array(serialize($attached_yet), $record_id));
            } else {
                $stmt = $DBC->query($query, array('', $record_id));
            }
        } else {
            // $this->riseError('empty uploads');
        }

        $this->delete_uploadify_images($session_key, $uploadify_field_name);
        return $ra;
    }

    function get_svg_info($svg_file_name)
    {
        $xmlget = simplexml_load_string(file_get_contents($svg_file_name));
        $xmlattributes = $xmlget->attributes();
        $ra = array();
        $ra['width'] = (string)$xmlattributes->width;
        $ra['height'] = (string)$xmlattributes->height;
        return $ra;
    }

    function get_image_info($file_name)
    {
        list($width, $height, $type, $attr) = getimagesize($file_name);
        $ra = array();
        $ra['width'] = $width;
        $ra['height'] = $height;
        // Далее пока лишняя инфа
        // $ra['type'] = $type;
        // $ra['attr'] = $attr;
        return $ra;
    }

    function sharding($files)
    {
        if ($this->getConfigValue('apps.sharder.enable')) {
            if (!is_object($this->sharder)) {
                $this->sharder = new \sharder\lib\sharder();
            }

            $result = $this->sharder->shard($files, $this->getServerFullUrl(true));
            if ($this->sharder->getError()) {
                $this->riseError('Error on sharding: ' . $this->sharder->getError());
                return false;
            }
            return $result;
        }
        return $files;
    }

    function rotateImageInDestination($source_image, $destination, $degree)
    {

        $arr = explode('.', $source_image);
        $ext = end($arr);

        if ($source_image == '') {
            return '';
        }

        if ($ext == 'jpg' || $ext == 'jpeg') {
            $source_image_res = @imagecreatefromjpeg($source_image);
        } elseif ($ext == 'png') {
            $source_image_res = @imagecreatefrompng($source_image);
        } elseif ($ext == 'gif') {
            $source_image_res = @imagecreatefromgif($source_image);
        } elseif ($ext == 'webp') {
            $source_image_res = @imagecreatefromwebp($source_image);
        }

        if (false === $source_image_res) {
            return;
        }

        $im = imagerotate($source_image_res, $degree, 0);

        if ($ext == 'jpg' || $ext == 'jpeg') {
            $im = imagerotate($source_image_res, $degree, 0);
            imagejpeg($im, $destination, (int)$this->getConfigValue('jpeg_quality'));
        } elseif ($ext == 'png') {
            $im = imagerotate($source_image_res, $degree, 0);
            imagepng($im, $destination, (int)$this->getConfigValue('png_quality'));
        } elseif ($ext == 'gif') {
            $im = imagerotate($source_image_res, $degree, 0);
            imagegif($im, $destination);
        } elseif ($ext == 'webp') {
            $im = imagerotate($source_image_res, $degree, 0);
            imagewebp($im, $destination);
        }

        return;
    }

    /**
     * Edit file
     * @param string $action action
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record ID
     * @return boolean
     */
    function editFileMulti($action, $table_name, $key, $record_id)
    {
        $path = SITEBILL_DOCUMENT_ROOT . '/img/data/';
        $uploadify_path = SITEBILL_DOCUMENT_ROOT . $this->uploadify_dir;

        $ra = array();
        $images = $this->load_uploadify_images($this->get_session_key());
        if (!$images) {
            return;
        }

        foreach ($images as $image_name) {
            $i++;
            $need_prv = 0;
            $preview_name = '';
            if (!empty($image_name)) {
                $arr = explode('.', $image_name);
                $ext = strtolower(end($arr));
                $preview_name = "file" . uniqid() . '_' . time() . "_" . $i . "." . $ext;
                $prv = "ffile" . uniqid() . '_' . time() . "_" . $i . "." . $ext;
                $preview_name_tmp = "_tmp" . uniqid() . '_' . time() . "_" . $i . "." . $ext;


                list($width, $height) = $this->makeMove($uploadify_path . $image_name, $path . $preview_name);
                $ra[$i]['preview'] = $preview_name;
                $ra[$i]['normal'] = $preview_name;
            }
        }
        $this->add_image_records($ra, $table_name, $key, $record_id);
        $this->delete_uploadify_images($this->get_session_key());
        return $ra;
    }

    function clear_uploadify_table($session_code = '', $anyway = false)
    {
        if (1 == (int)$this->getConfigValue('dontclean_uploadify_table') && !$anyway) {
            return true;
        }

        $postloaded = array();
        if (isset($_POST['_formpostloaded']) && is_array($_POST['_formpostloaded']) && count($_POST['_formpostloaded']) > 0) {
            $_postloaded = $_POST['_formpostloaded'];
            foreach ($_postloaded as $list) {
                $postloaded = array_merge($postloaded, $list);
            }
        }

        $uploadify_path = SITEBILL_DOCUMENT_ROOT . $this->uploadify_dir;
        $DBC = DBC::getInstance();
        $ra = array();
        if ($session_code == '') {
            $query = "SELECT file_name FROM " . UPLOADIFY_TABLE;
            $stmt = $DBC->query($query);
        } else {
            $query = "SELECT file_name FROM " . UPLOADIFY_TABLE . ' WHERE session_code=?';
            $stmt = $DBC->query($query, array($session_code));
        }

        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                if (!in_array($ar['file_name'], $postloaded)) {
                    $ra[] = $ar['file_name'];
                }
            }
        }

        if (count($ra) > 0) {
            foreach ($ra as $image_name) {
                if (is_file($uploadify_path . $image_name)) {
                    unlink($uploadify_path . $image_name);
                }
            }
        }

        if ($session_code == '') {
            $query = "TRUNCATE TABLE " . UPLOADIFY_TABLE;
            $stmt = $DBC->query($query);
        } else {
            if (!empty($postloaded)) {
                $query = 'DELETE FROM ' . UPLOADIFY_TABLE . ' WHERE `session_code`=? AND `file_name` NOT IN (' . implode(',', array_fill(0, count($postloaded), '?')) . ')';
                array_unshift($postloaded, $session_code);
                $stmt = $DBC->query($query, $postloaded);
            } else {
                $query = 'DELETE FROM ' . UPLOADIFY_TABLE . ' WHERE `session_code`=?';
                $stmt = $DBC->query($query, array($session_code));
            }
        }

        return true;
    }

    function clear_captcha_session_table()
    {
        $limit_date = date('Y-m-d H:i:s', (time() - 24 * 3600));
        $DBC = DBC::getInstance();
        $q = 'DELETE FROM ' . DB_PREFIX . '_captcha_session WHERE start_date<?';
        $DBC->query($q, array($limit_date));
        return true;
    }

    function clear_session_table()
    {
        $limit_date_anonim = date('Y-m-d H:i:s', (time() - 24 * 3600));
        $limit_date_user = date('Y-m-d H:i:s', (time() - 90 * 24 * 3600));

        $DBC = DBC::getInstance();

        $q = 'DELETE FROM ' . DB_PREFIX . '_session WHERE start_date<? AND user_id=0';
        $DBC->query($q, array($limit_date_anonim));

        $q = 'DELETE FROM ' . DB_PREFIX . '_session WHERE start_date<? AND user_id <> 0';
        $DBC->query($q, array($limit_date_user));

        return true;
    }

    /**
     * Delete uploadify images
     * @param string $session_code session code
     * @return array
     */
    function delete_uploadify_images($session_code, $element = '')
    {
        $uploadify_path = SITEBILL_DOCUMENT_ROOT . $this->uploadify_dir;
        $DBC = DBC::getInstance();

        $ra = array();
        if ($element != '') {
            $query = 'SELECT file_name FROM ' . UPLOADIFY_TABLE . ' WHERE `session_code`=? AND `element`=?';
            $stmt = $DBC->query($query, array((string)$session_code, $element));
        } else {
            $query = 'SELECT file_name FROM ' . UPLOADIFY_TABLE . ' WHERE `session_code`=?';
            $stmt = $DBC->query($query, array((string)$session_code));
        }


        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $ra[] = $ar['file_name'];
            }
        }
        if (count($ra) > 0) {
            foreach ($ra as $image_name) {
                if (is_file($uploadify_path . $image_name)) {
                    unlink($uploadify_path . $image_name);
                }
            }
        }
        if ($element != '') {
            $query = 'DELETE FROM ' . UPLOADIFY_TABLE . ' WHERE `session_code`=? AND `element`=?';
            $stmt = $DBC->query($query, array((string)$session_code, $element));
        } else {
            $query = 'DELETE FROM ' . UPLOADIFY_TABLE . ' WHERE session_code=?';
            $stmt = $DBC->query($query, array((string)$session_code));
        }

        return true;
    }

    /**
     * Delete uploadify image
     * @param string $image_name image_name
     * @return array
     */
    function delete_uploadify_image($image_name)
    {
        $DBC = DBC::getInstance();
        $file_name = $image_name;
        $uploadify_path = SITEBILL_DOCUMENT_ROOT . $this->uploadify_dir;
        $query = 'DELETE FROM ' . UPLOADIFY_TABLE . ' WHERE file_name=?';
        $DBC->query($query, array($file_name));
        unlink($uploadify_path . $file_name);
        return true;
    }

    function get_ajax_auth_form()
    {
        if (SITEBILL_MAIN_URL != '') {
            $add_folder = SITEBILL_MAIN_URL . '/';
        }
        $rs .= '<form method="post" onsubmit="run_login(\'login\', \'cp1251\', \'' . $_SERVER['SERVER_NAME'] . $add_folder . '\'); return false;">';
        $rs .= '';
        $rs .= '<table border="0">';
        if ($this->getError() and $this->GetErrorMessage() != 'not login') {
            $rs .= '<tr>';
            $rs .= '<td colspan="2"><span class="error">' . $this->GetErrorMessage() . '</span></td>';
            $rs .= '</tr>';
        }
        $rs .= '<tr>';
        $rs .= '<td class="special" colspan="2"><div id="error_message"></div></td>';
        $rs .= '</tr>';

        $rs .= '<tr>';
        $rs .= '<td class="special">' . Multilanguage::_('L_LOGIN') . ' </td>';
        $rs .= '<td class="special"><input type="text" name="login" id="login"></td>';
        $rs .= '</tr>';

        $rs .= '<tr>';
        $rs .= '<td class="special">' . Multilanguage::_('L_PASSWORD') . ' </td>';
        $rs .= '<td class="special"><input type="password" name="password" id="password"></td>';
        $rs .= '</tr>';
        $rs .= '<tr>';
        $rs .= '<td class="special">';
        if ($this->getConfigValue('allow_register_admin')) {
            $rs .= '<a href="#" onclick="run_command(\'register\', \'cp1251\', \'' . $_SERVER['SERVER_NAME'] . $add_folder . '\'); return false;">' . Multilanguage::_('L_AUTH_REGISTRATION') . '</a>';
        }
        $rs .= '</td>';
        $rs .= '<td class="special"><input type="submit" value="' . Multilanguage::_('L_LOGIN_BUTTON') . '" onclick="run_login(\'login\', \'cp1251\', \'' . $_SERVER['SERVER_NAME'] . $add_folder . '\'); return false;"></td>';
        $rs .= '</tr>';
        $rs .= '</table>';
        $rs .= '';
        $rs .= '</form>';
        return $rs;
    }

    /**
     * Get simple auth form
     * @param string $action
     * @param boolean $register
     * @param boolean $remind
     * @return string
     */
    function get_simple_auth_form($action = '/login/', $register = true, $remind = true)
    {
        if (SITEBILL_MAIN_URL != '') {
            $add_folder = '/' . SITEBILL_MAIN_URL;
        }

        if ($this->getConfigValue('theme') == 'albostar') {
            $rs .= '<form method="post" action="' . SITEBILL_MAIN_URL . $action . '">';
            $rs .= '';

            if ($this->getError() and $this->GetErrorMessage() != 'not login') {
                $rs .= '<div>';
                $rs .= '<span class="error">' . $this->GetErrorMessage() . '</span>';
                $rs .= '</div>';
            }


            $rs .= '<label>' . Multilanguage::_('L_AUTH_LOGIN') . '</label>';
            $rs .= '<input type="text" name="login" id="login">';
            $rs .= '<br />';

            $rs .= '<label>' . Multilanguage::_('L_AUTH_PASSWORD') . '</label>';
            $rs .= '<input type="password" name="password" id="password">';
            $rs .= '<input type="submit" value="Вход">';
            if ($register) {
                $rs .= '<br />';
                $rs .= '<a href="' . SITEBILL_MAIN_URL . '/register/">' . Multilanguage::_('L_AUTH_REGISTRATION') . '</a>';
            }
            if ($remind) {
                $rs .= '<br />';
                $rs .= '<a href="' . SITEBILL_MAIN_URL . '/remind/">' . Multilanguage::_('L_AUTH_FORGOT_PASS') . '</a>';
            }

            $rs .= '<input type="hidden" name="do" value="login">';
            $rs .= '</form>';
        } else {

            if ($action == '/admin/' && 1 === intval($this->getConfigValue('use_captcha_admin_entry'))) {
                $c['captcha']['name'] = 'captcha';
                $c['captcha']['title'] = Multilanguage::_('CAPTCHA_TITLE', 'system');
                $c['captcha']['value'] = '';
                $c['captcha']['length'] = 40;
                $c['captcha']['type'] = 'captcha';
                $c['captcha']['required'] = 'on';
                $c['captcha']['unique'] = 'off';
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/form/form_generator.php');
                $form_generator = new Form_Generator();

                $el = $form_generator->compile_form_elements($c);
                $el = $el['hash']['captcha']['html'];
            } else {
                $el = '';
            }

            $rs .= '<form method="post" action="' . SITEBILL_MAIN_URL . $action . '">';
            if ($this->getError() and $this->GetErrorMessage() != 'not login') {
                $rs .= '<div class="alert alert-error" style="display:block;">';
                $rs .= '<a class="close" data-dismiss="alert" href="#">x</a>' . $this->GetErrorMessage() . '';
                $rs .= '</div>';
            }

            $rs .= '<input class="span12" placeholder="' . Multilanguage::_('L_AUTH_LOGIN') . '" type="text" name="login" id="login" />';
            $rs .= '<input class="span12" placeholder="' . Multilanguage::_('L_AUTH_PASSWORD') . '" type="password" name="password" id="password" />';
            $rs .= $el;
            $rs .= '<label class="checkbox">';
            $rs .= '<input type="checkbox" name="rememberme" value="1"> Запомнить меня';
            $rs .= '</label>';
            $rs .= '<button class="btn-info btn" type="submit">' . Multilanguage::_('L_AUTH_ENTER') . '</button>';
            $rs .= '<input type="hidden" name="do" value="login">';
            $rs .= '</form>';


            if ($register) {
                $rs .= '<a href="' . SITEBILL_MAIN_URL . '/register/">' . Multilanguage::_('L_AUTH_REGISTRATION') . '</a>';
            }
            if ($remind) {
                $rs .= '<br><a href="' . SITEBILL_MAIN_URL . '/remind/">' . Multilanguage::_('L_AUTH_FORGOT_PASS') . '</a>';
            }
        }
        return $rs;
    }

    /**
     * Add image data records
     * @param array $images images
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record id
     * @return boolean
     */
    function add_image_records($images, $table_name, $key, $record_id)
    {

        $DBC = DBC::getInstance();
        foreach ($images as $item_id => $item_array) {
            $query = 'INSERT INTO ' . IMAGE_TABLE . ' (normal, preview) VALUES (?, ?)';
            $stmt = $DBC->query($query, array($item_array['normal'], $item_array['preview']));
            if ($stmt) {
                $image_id = $DBC->lastInsertId();
                $this->add_table_image_record($table_name, $key, $record_id, $image_id);
            }
        }
    }

    /**
     * Add table_image record
     * @param int $record_id record id
     * @param int $image_id image id
     * @return boolean
     */
    function add_table_image_record($table_name, $key, $record_id, $image_id)
    {
        $DBC = DBC::getInstance();
        $query = 'INSERT INTO ' . DB_PREFIX . '_' . $table_name . '_image (' . $key . ', image_id, sort_order) values (?, ?, ?)';
        $DBC->query($query, array($record_id, $image_id, $image_id));
        return true;
    }

    /**
     * Get Plupload plugin (http://www.plupload.com/)
     * Only html4 version available (not attached files for others)
     * @param string $session_code session code
     * @return string
     */
    function getPluploaderPlugin($session_code)
    {
        $this->clear_uploadify_table($session_code);
        global $folder;
        $rs .= '
    		
    		<style type="text/css">@import url(' . $folder . '/apps/system/js/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css);</style>
			<script type="text/javascript" src="' . $folder . '/apps/system/js/plupload/plupload.full.js"></script>
			<script type="text/javascript" src="' . $folder . '/apps/system/js/plupload/jquery.plupload.queue/jquery.plupload.queue.js">
			<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>
			<script type="text/javascript" src="' . $folder . '/apps/system/js/plupload/i18n/ru.js"></script>
			<script>        
		       $(function() {
		       		function log(msg){
		       			 $("#log").append(msg + "\n");
		       		
		       		};
		       		
		       		var del=[];
		       
					$("#html4_uploader").pluploadQueue({
						runtimes : \'html4\',
						multiple_queues: true,
						url : "' . $folder . '/apps/system/js/uploadify/uploadify.php?session=' . $session_code . '",
						init : {
							FileUploaded: function(up, file, info) {
								if (info.response.indexOf("wrong_ext") != -1){
									file.status = plupload.FAILED;
									up.trigger("UploadProgress", file);
								}else if(info.response.indexOf("max_file_size") != -1){
									file.status = plupload.FAILED;
									up.trigger("UploadProgress", file);
								}
							},
							
						}
					});
				});  
		    </script>  
			<div id="log"></div>
			<div id="html4_uploader">You browser doesnt support simple upload forms. Are you using Lynx?</div>';
        return $rs;
    }

    /**
     * Get uploadify plugin
     * @param string $session_code session code
     * @return string
     */
    function getUploadifyPlugin($session_code, $params = array())
    {
        $this->clear_uploadify_table($session_code);
        $uploaded_images = $this->load_uploadify_images($session_code);
        global $folder;
        $rs = '';
        $rs .= '
<link href="' . $folder . '/apps/system/js/uploadify/uploadify.css" type="text/css" rel="stylesheet" />
<style>
		#filecollector { overflow: hidden; }
		#filecollector div { width: 100px; display: block; float: left; padding: 5px; margin: 3px; }
		#filecollector div img { width: 100px; border: 1px solid #CFCFCF; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15); border-radius: 5px; margin-bottom: 5px; }
</style>
		
<script type="text/javascript" src="' . $folder . '/apps/system/js/uploadify/jquery.uploadify.js"></script>
<script type="text/javascript">
var uploadedfiles = 0;
var maxQueueSize = 100;
var queueSize = 0;
$(document).ready(function() {
	var max_item_count=' . ((int)$this->getConfigValue('photo_per_data') > 0 ? (int)$this->getConfigValue('photo_per_data') : 1000) . ';
	
	
	
  $(\'#file_upload\').uploadify({
    \'swf\'  : \'' . $folder . '/apps/system/js/uploadify/uploadify.swf\',
    \'uploader\'    : \'' . $folder . '/apps/system/js/uploadify/uploadify.php?session=' . $session_code . '\',
    \'cancelImg\' : \'' . $folder . '/apps/system/js/uploadify/uploadify-cancel.png\',
    \'folder\'    : \'' . $folder . '/cache/upl\',
    \'auto\'      : true,
	\'fileTypeExts\': \'*.jpg;*.jpeg;*.png;*.gif\',
	\'multi\': true,	
	\'queueSizeLimit\': 100,
		\'buttonText\': \'' . ((isset($params['button_name']) && $params['button_name'] != '') ? $params['button_name'] : Multilanguage::_('L_PHOTO')) . '\',	
	\'buttonImg\': \'' . $folder . '/img/button_img_upl.png\',	
    \'onUploadSuccess\': function(fileObj, response, data) {
    					queueSize++;
    					if ( response == \'max_file_size\' ) {
    						alert(\'' . Multilanguage::_('L_MESSAGE_MAX_UPL_SIZE') . ' ' . ini_get('upload_max_filesize') . ' \');
    						return false;
    					}
    					if ( response == \'wrong_ext\' ) {
    						alert(\'' . Multilanguage::_('L_MESSAGE_AVIALABLE_EXTS') . ' *.jpg,*.jpeg,*.png,*.gif\');
    						return false;
    					}
    					if ( response == \'bad_file\' ) {
    						alert(\'bad_file\');
    						return false;
    					}
    					if ( queueSize > maxQueueSize ) {
    						alert(\'' . Multilanguage::_('L_MESSAGE_MAX_FILES_COUNT') . '\');
    						return false;
    					}
    					var imgs_count=$("div.preview_admin").length+$("#filecollector img").length;
    					imgs_count++;
    					if(imgs_count==max_item_count){
    						$(\'#file_uploadUploader\').hide();
						}
    								
    					addFileNotify(queueSize);
    					addFileInCollector(response);
    					
    				}
                    
    });
    
});
function addFileNotify ( queueSize ) {
	$(\'#filenotify\').html( \'Вы успешно загрузили: \' + queueSize + \' файл(ов)\' );
}
function addFileInCollector ( filePath ) {
	var temp=new Array();
	temp=filePath.split(\'/\');
    //								temp=filePath.split(\'||\');
	var f=temp[temp.length-1];
	var cont=$(\'#filecollector\').html();
	cont=cont+\'<div><img src="\'+filePath+\'" /><a class="kill_upl btn btn-mini btn-danger" href="javascript:void(0)" alt="\'+f+\'">X</a></div>\';
    //								cont=cont+\'<div><img src="\'+temp[0]+\'" /><a class="kill_upl btn btn-mini btn-danger" href="javascript:void(0)" alt="\'+f+\'">X</a></div>\';
	$(\'#filecollector\').html(cont);
	
}

$(document).ready(function() {
	$(document).on(\'click\', \'a.kill_upl\',function(){
	
		var imgs_count=$("div.preview_admin").length+$("#filecollector img").length;
		var max_item_count=' . ((int)$this->getConfigValue('photo_per_data') > 0 ? (int)$this->getConfigValue('photo_per_data') : 1000) . ';
		var url=\'/js/ajax.php?action=delete_uploadify_image&img_name=\'+$(this).attr(\'alt\');
		$.getJSON(url,{},function(data){});
		var parent=$(this).parent(\'div\');
		parent.html(\'\');
		parent.remove();
		imgs_count--;
		if(imgs_count<max_item_count){
    		$(\'#file_uploadUploader\').show();
		}
	});
	
		
});

</script>
<input id="file_upload" name="file_upload" type="file" />
<div id="filenotify"></div>
<div id="filecollector">';
        if (false !== $uploaded_images) {
            foreach ($uploaded_images as $uplim) {
                $p = array();
                $p = explode('.', $uplim);
                if (in_array(strtolower(end($p)), array('jpg', 'jpeg', 'png', 'gif'))) {
                    $rs .= '<div><img src="' . SITEBILL_MAIN_URL . '/cache/upl/' . $uplim . '"><a class="kill_upl btn btn-mini btn-danger" href="javascript:void(0)" alt="' . $uplim . '">X</a></div>';
                }
            }
        }

        $rs .= '</div>';

        return $rs;
    }

    function getDropzonePlugin($session_code, $params = array())
    {
        $element = $params['element']['name'];
        $type = $params['element']['type'];

        //Проверяем наличие расширяющих правил для max_img_count
        if (isset($params['element']['parameters']['max_img_count_ext']) && '' != $params['element']['parameters']['max_img_count_ext']) {
            $maximgcountextendrules = $params['element']['parameters']['max_img_count_ext'];
        } else {
            $maximgcountextendrules = '';
        }

        $controlledfields = array();
        if ($maximgcountextendrules != '') {
            $rulesparts = explode(':', $maximgcountextendrules);
            $size = intval($rulesparts[0]);
            if ($size > 0 && count($rulesparts) > 1) {
                unset($rulesparts[0]);
                foreach ($rulesparts as $rule) {
                    $oneruleparts = explode(',', $rule);
                    if (count($oneruleparts) == 3) {
                        $controlledfields[$oneruleparts[0]] = $oneruleparts[0];
                    }
                }
            }
        }

        $rs = '';

        $this->clear_uploadify_table($session_code);

        $uploaded_images = $this->load_uploadify_images($session_code, $element);
        $id = 'dz_' . md5(time() . rand(100, 999));
        $Dropzone_name = 'Dropzone_' . md5(time() . rand(100, 999));

        if ((int)$params['min_img_count'] != 0) {
            $src = 'var formsubmit=$("#' . $id . '").parents("form").eq(0).find("[name=submit]");
					var vm=formsubmit.data("valid_me");
					if(vm === undefined){
						vm=[];
					}
					vm.push({id:"' . $id . '", count:' . (int)$params['min_img_count'] . '});
					formsubmit.data("valid_me", vm);';
        } else {
            $src = '';
        }


        $rs .= '<script>
    			
    			$(document).ready(function(){
    			
    			//var prevbuttonstatus_' . $Dropzone_name . ';
    				var ' . $Dropzone_name . ' = new Dropzone("div#' . $id . '", 
    				{ 
    					maxFilesize: ' . $params['max_file_size'] . ',
						url: "' . SITEBILL_MAIN_URL . '/apps/system/js/uploadify/uploadify.php?uploader_type=dropzone&element=' . $element . '&model=' . $params['element']['table_name'] . '&primary_key_value=' . $params['element']['primary_key_value'] . '&primary_key=' . $params['element']['primary_key'] . '",
	    				' .
            ((
                isset($params['element']) &&
                isset($params['element']['parameters']) &&
                isset($params['element']['parameters']['accepted']) &&
                $params['element']['parameters']['accepted'] != ''
            ) ? 'acceptedFiles: \'' . $params['element']['parameters']['accepted'] . '\',' : '') . '
						addRemoveLinks: true,
						customparams: {
						    url: \'' . SITEBILL_MAIN_URL . '/apps/system/js/uploadify/uploadify.php?uploader_type=dropzone\',
						    element: \'' . $element . '\',
						    model: \'' . $params['element']['table_name'] . '\',
						    primary_key_value: \'' . $params['element']['primary_key_value'] . '\',
						    primary_key: \'' . $params['element']['primary_key'] . '\',
						    controls: [' . (!empty($controlledfields) ? '\'' . implode('\',\'', $controlledfields) . '\'' : '') . ']
						}
					});
					$("div#' . $id . ' .dz-remove").click(function(){
							var _this=$(this);
							var url="' . SITEBILL_MAIN_URL . '/js/ajax.php?action=delete_uploadify_image&img_name="+$(this).attr("alt");
								$("#' . $id . ' .postloaded[value=\'"+$(this).attr("alt")+"\']").remove();
								$.getJSON(url,{},function(data){_this.parents(".dz-preview").eq(0).remove()});
    						});
					' . $src . ' 
					' . $Dropzone_name . '.on("complete", function(){
    						if(this.getQueuedFiles().length==0 && this.getUploadingFiles().length==0){
    							var form=$(this.element).parents("form");
								form.find("[name=submit]").show();
							}
    
    				}).on("success", function(file, responce) {
							if(responce.status=="error"){
								$(file.previewElement).remove();
							    if(typeof ' . $Dropzone_name . '_quenue !=\'undefined\' ){
								    ' . $Dropzone_name . '_quenue--;
                                }
                                var form=$(this.element).parents("form");
                                var msg = $(\'<div class="alert">\'+responce.msg+\'</div>\');
								msg.insertBefore($("#' . $id . '"));
								setTimeout(function(){msg.fadeOut(function(){msg.remove();});}, 1500);
							}else{														
								var form=$(this.element).parents("form");														
								var rem=$(file.previewElement).find(".dz-remove");
								var temp=new Array();
								temp=responce.msg.split(\'/\');
								var file_name=temp[temp.length-1];
								$("#' . $id . '").append($("<input class=\'postloaded\' name=\'_formpostloaded[' . $element . '][]\' type=\'hidden\' value=\'"+file_name+"\'>"));
								rem.attr("alt", file_name);
								rem.on("click", function(){
    								var url="' . SITEBILL_MAIN_URL . '/js/ajax.php?action=delete_uploadify_image&img_name="+$(this).attr("alt");
									$.getJSON(url,{},function(data){});
    							});
							}
    				}).on("addedfile", function(file){
    					var form=$(this.element).parents("form");
    					form.find("[name=submit]").hide();
    					var form=$(this.element).parents("form");
    					this.options.url = this.options.customparams.url + \'&element=\' + this.options.customparams.element + \'&model=\' + this.options.customparams.model + \'&primary_key_value=\' + this.options.customparams.primary_key_value + \'&primary_key=\' + this.options.customparams.primary_key;
    					if(this.options.customparams.controls.length > 0){
    					    for(var i in this.options.customparams.controls){
    					        this.options.url += \'&\'+this.options.customparams.controls[i]+\'=\' + form.find(\'[name=\'+this.options.customparams.controls[i]+\']\').val();
    					    }
    					}
    				});
                                
                    Dropzone.prototype.defaultOptions.dictDefaultMessage = "'._e('Переместите сюда файлы для загрузки').'";
                    Dropzone.prototype.defaultOptions.dictFallbackMessage = "'._e('Ваш браузер не поддерживает опцию drag-n-drop').'";
                    Dropzone.prototype.defaultOptions.dictFallbackText = "'._e('Пожалуйста, используйте форму ниже для загрузки файлов').'";
                    Dropzone.prototype.defaultOptions.dictFileTooBig = "'._e('Файл слишком большой').' ({{filesize}}MiB). '._e('Максимальный размер файла').': {{maxFilesize}}MiB.";
                    Dropzone.prototype.defaultOptions.dictInvalidFileType = "'._e('Формат файла не подходит').'";
                    Dropzone.prototype.defaultOptions.dictResponseError = "'._e('Ответ сервера с ошибкой').' {{statusCode}} code.";
                    Dropzone.prototype.defaultOptions.dictCancelUpload = "'._e('Отменить загрузку').'";
                    Dropzone.prototype.defaultOptions.dictCancelUploadConfirmation = "'._e('Вы уверены, что хотите прервать загрузку?').'";
                    Dropzone.prototype.defaultOptions.dictRemoveFile = "'._e('Удалить файл').'";
                    Dropzone.prototype.defaultOptions.dictMaxFilesExceeded = "'._e('Исчерпан лимит загрузки файлов').'";
				});
				</script>';
        $rs .= '<div data-ii="" class="dropzone_outer' . ($type == 'docuploads' ? ' docuploads' : '') . '"><div id="' . $id . '" class="dropzone_inner"><div class="dz-default dz-message"><span><span class="bigger-50 bolder">' . ($type == 'docuploads' ? Multilanguage::_('L_DOCUPLOADS_FILE') : Multilanguage::_('L_UPLOADS_FILE')) . '</span> <br>	<i class="upload-icon icon-cloud-upload blue icon-3x"></i></span></div>';
        if ( $this->getConfigValue('apps.realty.additional_dropzone_button') ) {
            $rs .= '<a class="btn btn-primary" id="dropzone_add_more_files_' . $id . '"><i class="fa fa-plus"></i> '.($type == 'docuploads' ? _e('Добавить файлы') : _e('Добавить фото')).'</a>';
        }

        if (false !== $uploaded_images) {
            foreach ($uploaded_images as $uplim) {

                $p = array();
                $p = explode('.', $uplim);
                if (($type == 'uploads' && in_array(strtolower(end($p)), array('jpg', 'jpeg', 'png', 'gif'))) || $type == 'docuploads') {
                    $rs .= '<input class="postloaded" name="_formpostloaded[' . $element . '][]" type="hidden" value="' . $uplim . '">';
                }
            }
        }

        if (false !== $uploaded_images) {
            foreach ($uploaded_images as $uplim) {

                $p = array();
                $p = explode('.', $uplim);

                if (($type == 'uploads' && in_array(strtolower(end($p)), array('jpg', 'jpeg', 'png', 'gif'))) || $type == 'docuploads') {
                    $rs .= '<div class="dz-preview dz-processing dz-image-preview dz-success">';
                    $rs .= '<div class="dz-details">';
                    $rs .= '<div class="dz-filename">';
                    $rs .= '<span data-dz-name="">' . $uplim . '</span></div>';
                    $rs .= '<div class="dz-size" data-dz-size="">';
                    $rs .= '<strong>0.1</strong> MiB</div>';
                    if ($type == 'uploads') {
                        $rs .= '<img data-dz-thumbnail="" alt="' . $uplim . '" src="' . SITEBILL_MAIN_URL . '/cache/upl/' . $uplim . '">';
                    }

                    $rs .= '</div>  <div class="dz-progress">';
                    $rs .= '<span class="dz-upload" data-dz-uploadprogress="" style="width: 100%;">';
                    $rs .= '</span>';
                    $rs .= '</div>';
                    $rs .= '<div class="dz-success-mark"><span>✔</span></div>  <div class="dz-error-mark"><span>✘</span></div>  <div class="dz-error-message">';
                    $rs .= '<span data-dz-errormessage="">';
                    $rs .= '</span>';
                    $rs .= '</div>';
                    $rs .= '<a class="dz-remove" href="javascript:undefined;" data-dz-remove="" alt="' . $uplim . '">' . _e('Удалить') . '</a>';
                    $rs .= '</div>';
                }
            }
        }
        $rs .= '</div>';
        $rs .= '</div>';

        return $rs;
    }

    /**
     * Get uploadify plugin
     * @param string $session_code session code
     * @return string
     */
    function getUploadifyFilePlugin($session_code, $params = array())
    {
        $this->clear_uploadify_table($session_code);
        $id = md5(time() . rand(1000, 9999));
        global $folder;

        $rs = '';
        $rs .= '
<link href="' . $folder . '/apps/system/js/uploadify/uploadify.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="' . $folder . '/apps/system/js/uploadify/jquery.uploadify.js"></script>
<script type="text/javascript">
var uploadedfiles = 0;
var maxQueueSize = 100;
var queueSize = 0;
$(document).ready(function() {
  $(\'#' . $id . '\').uploadify({
    \'swf\'  : \'' . $folder . '/apps/system/js/uploadify/uploadify.swf\',
    \'uploader\'    : \'' . $folder . '/apps/system/js/uploadify/uploadify.php?file=1&session=' . $session_code . '\',
    \'cancelImg\' : \'' . $folder . '/apps/system/js/uploadify/uploadify-cancel.png\',
    \'folder\'    : \'' . $folder . '/cache/upl\',
    \'auto\'      : true,
	\'fileTypeExts\': \'*.doc;*.pdf;*.zip\',
	\'multi\': true,	
	\'queueSizeLimit\': 100,
	\'buttonText\': \'' . ((isset($params['button_name']) && $params['button_name'] != '') ? $params['button_name'] : Multilanguage::_('L_FILE')) . '\',
	\'buttonImg\': \'' . $folder . '/img/button_img_upl.png\',
    \'onUploadSuccess\': function(fileObj, response, data) {
    					queueSize++;
    					if ( response == \'max_file_size\' ) {
    						alert(\'' . Multilanguage::_('L_MESSAGE_MAX_UPL_SIZE') . ' ' . ini_get('upload_max_filesize') . ' \');
    						return false;
    					}
    					if ( response == \'wrong_ext\' ) {
    						alert(\'' . Multilanguage::_('L_MESSAGE_AVIALABLE_EXTS') . ' png, jpg, tif, jpeg, doc,docx, xls, xlsx, pdf, txt, zip, rar\');
    						return false;
    					}
    					if ( queueSize > maxQueueSize ) {
    						alert(\'' . Multilanguage::_('L_MESSAGE_MAX_FILES_COUNT') . '\');
    						return false;
    					}
    					addFileNotify(queueSize);
    				}

    });
});
function addFileNotify ( queueSize ) {
	$(\'#filenotify\').html( \'Вы успешно загрузили: \' + queueSize + \' файл(ов)\' );
}
</script>
<input id="' . $id . '" name="file_upload" type="file" />
<div id="filenotify"></div>
        ';
        return $rs;
    }

    /**
     * Is demo
     * @param void
     * @return boolean
     */
    function isDemo()
    {
        global $__user, $__db;
        if (preg_match('/rumantic_estate/', $__db)) {
            return true;
        }
        return false;
    }

    /**
     * Demo function disabled
     * @param void
     * @return string
     */
    function demo_function_disabled()
    {
        return Multilanguage::_('L_MESSAGE_THIS_IS_TRIAL_COMMON');
    }

    /**
     * Load config
     * @param
     * @return
     */
    function loadConfig()
    {
        if (!self::$config_loaded) {
            $SConfig = SConfig::getInstance();
            self::$config_array = $SConfig->getConfig();
            self::$config_loaded = true;
        }
    }

    static function loadConfigStatic()
    {
        if (!self::$config_loaded) {
            $SConfig = SConfig::getInstance();
            self::$config_array = $SConfig->getConfig();
            self::$config_loaded = true;
        }
    }


    /**
     * Delete image
     * @param string $table_name table name
     * @param int $image_id image id
     * @return boolean
     */
    function deleteImage($table_name, $image_id)
    {
        $DBC = DBC::getInstance();
        $query = 'DELETE FROM ' . DB_PREFIX . '_' . $table_name . '_image WHERE image_id=?';
        $DBC->query($query, array($image_id));

        $this->deleteImageFiles($image_id);

        $query = 'DELETE FROM ' . IMAGE_TABLE . ' WHERE image_id=?';
        $DBC->query($query, array($image_id));
        return true;
    }

    function makeImageMain($action, $image_id, $key, $key_value)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT image_id FROM ' . DB_PREFIX . '_' . $action . '_image WHERE `' . $key . '`=? ORDER BY sort_order';
        $stmt = $DBC->query($query, array($key_value));
        $imgs = array();
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $imgs[] = $ar['image_id'];
            }
        }

        if (!empty($imgs)) {
            $imgids = array_flip($imgs);
            if (isset($imgids[$image_id])) {
                unset($imgs[$imgids[$image_id]]);
                array_unshift($imgs, $image_id);
            }
            $query = 'UPDATE ' . DB_PREFIX . '_' . $action . '_image SET sort_order=? WHERE image_id=?';
            foreach ($imgs as $k => $v) {
                $DBC->query($query, array($k + 1, $v));
            }
        }
    }

    function rotateImage2($thisimage, $isWatermark, $degree, $parameters)
    {

        if ($thisimage['normal'] == '') {
            return '';
        }

        $arr = explode('.', $thisimage['normal']);
        $ext = end($arr);

        if ($isWatermark && file_exists(SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $thisimage['normal'])) {
            $source_image = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $thisimage['normal'];
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'])) {
            $source_image = SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'];
        } else {
            $source_image = '';
        }

        $target_image_name = $thisimage['normal'];
        $target_preview_name = $thisimage['preview'];

        if ($source_image == '') {
            return '';
        }

        $source_preview = SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['preview'];

        $big_sizes = getimagesize($source_image);
        $prev_sizes = getimagesize($source_preview);

        if ($ext == 'jpg' || $ext == 'jpeg') {
            $source_image_res = imagecreatefromjpeg($source_image);
        } elseif ($ext == 'png') {
            $source_image_res = imagecreatefrompng($source_image);
        } elseif ($ext == 'gif') {
            $source_image_res = imagecreatefromgif($source_image);
        }


        $preview_width = $parameters['prev_width'];
        $preview_height = $parameters['prev_height'];

        if (1 == $parameters['preview_smart_resizing']) {
            $preview_mode = 'smart';
        } else {
            $preview_mode = 'width';
        }


        if ($isWatermark) {
            if ($ext == 'jpg' || $ext == 'jpeg') {
                $im = imagerotate($source_image_res, $degree, 0);
                @imagejpeg($im, SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $target_image_name, (int)$this->getConfigValue('jpeg_quality'));
                imagejpeg($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name, (int)$this->getConfigValue('jpeg_quality'));
            } elseif ($ext == 'png') {
                $im = imagerotate($source_image_res, $degree, 0);
                @imagepng($im, SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $target_image_name, (int)$this->getConfigValue('png_quality'));
                imagepng($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name, (int)$this->getConfigValue('png_quality'));
            } elseif ($ext == 'gif') {
                $im = imagerotate($source_image_res, $degree, 0);
                @imagegif($im, SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $target_image_name);
                imagegif($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name);
            } elseif ($ext == 'webp') {
                $im = imagerotate($source_image_res, $degree, 0);
                @imagewebp($im, SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $target_image_name);
                imagewebp($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name);
            }

            $rp = $this->makePreview($source_image, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_preview_name, $preview_width, $preview_height, $ext, $preview_mode);

            require_once SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/watermark/watermark.php';
            $watermark_inst = new Watermark();
            $watermark_inst->setPosition($this->getConfigValue('apps.watermark.position'));
            $watermark_inst->setOffsets(array(
                $this->getConfigValue('apps.watermark.offset_left'),
                $this->getConfigValue('apps.watermark.offset_top'),
                $this->getConfigValue('apps.watermark.offset_right'),
                $this->getConfigValue('apps.watermark.offset_bottom')
            ));

            $watermark_inst->printWatermark(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name);

        } else {
            if ($ext == 'jpg' || $ext == 'jpeg') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagejpeg($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name, (int)$this->getConfigValue('jpeg_quality'));
            } elseif ($ext == 'png') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagepng($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name, (int)$this->getConfigValue('png_quality'));
            } elseif ($ext == 'gif') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagegif($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name);
            } elseif ($ext == 'webp') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagewebp($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_image_name);
            }
            $rp = $this->makePreview($source_image, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $target_preview_name, $preview_width, $preview_height, $ext, $preview_mode);
        }

        return true;
    }

    function rotateImage($action, $image_id, $key, $key_value, $rot_dir)
    {
        if ($rot_dir == 'ccw') {
            $degree = 90;
        } else {
            $degree = -90;
        }

        $DBC = DBC::getInstance();
        $query = 'SELECT normal, preview FROM ' . DB_PREFIX . '_image WHERE `image_id`=? LIMIT 1';
        $normal = '';
        $stmt = $DBC->query($query, array($image_id));
        $imgs = array();
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $thisimage = $ar;
        }

        if ($thisimage['normal'] == '') {
            return '';
        }

        $arr = explode('.', $thisimage['normal']);
        $ext = end($arr);

        $hasWatermark = false;
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $thisimage['normal'])) {
            $source_image = SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $thisimage['normal'];
            $hasWatermark = true;
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'])) {
            $source_image = SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'];
        } else {
            $source_image = '';
        }

        if ($source_image == '') {
            return '';
        }

        $source_preview = SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['preview'];

        $big_sizes = getimagesize($source_image);
        $prev_sizes = getimagesize($source_preview);

        if ($ext == 'jpg' || $ext == 'jpeg') {
            $source_image_res = imagecreatefromjpeg($source_image);
        } elseif ($ext == 'png') {
            $source_image_res = imagecreatefrompng($source_image);
        } elseif ($ext == 'gif') {
            $source_image_res = imagecreatefromgif($source_image);
        } elseif ($ext == 'webp') {
            $source_image_res = imagecreatefromwebp($source_image);
        }

        $preview_width = $this->getConfigValue($action . '_image_preview_width');
        if ($preview_width == '') {
            $preview_width = $this->getConfigValue('news_image_preview_width');
        }
        $preview_height = $this->getConfigValue($action . '_image_preview_height');
        if ($preview_height == '') {
            $preview_height = $this->getConfigValue('news_image_preview_height');
        }
        if (1 == $this->getConfigValue('apps.realty.preview_smart_resizing') && $action == 'data') {
            $preview_mode = 'smart';
        } else {
            $preview_mode = 'width';
        }

        if ($hasWatermark) {
            if ($ext == 'jpg' || $ext == 'jpeg') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagejpeg($im, SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $thisimage['normal'], (int)$this->getConfigValue('jpeg_quality'));
                imagejpeg($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'], 100);
            } elseif ($ext == 'png') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagepng($im, SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $thisimage['normal'], (int)$this->getConfigValue('png_quality'));
                imagepng($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'], (int)$this->getConfigValue('png_quality'));
            } elseif ($ext == 'gif') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagegif($im, SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $thisimage['normal']);
                imagegif($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal']);
            } elseif ($ext == 'webp') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagewebp($im, SITEBILL_DOCUMENT_ROOT . '/img/data/nowatermark/' . $thisimage['normal']);
                imagewebp($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal']);
            }

            $rp = $this->makePreview(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'], SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['preview'], $preview_width, $preview_height, $ext, 'smart');
        } else {
            if ($ext == 'jpg' || $ext == 'jpeg') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagejpeg($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'], (int)$this->getConfigValue('jpeg_quality'));
            } elseif ($ext == 'png') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagepng($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'], (int)$this->getConfigValue('png_quality'));
            } elseif ($ext == 'gif') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagegif($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal']);
            } elseif ($ext == 'webp') {
                $im = imagerotate($source_image_res, $degree, 0);
                imagewebp($im, SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal']);
            }
            $rp = $this->makePreview(SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['normal'], SITEBILL_DOCUMENT_ROOT . '/img/data/' . $thisimage['preview'], $preview_width, $preview_height, $ext, 'smart');
        }

        return;
    }

    /**
     * Reorder image
     * @param $action
     * @param $image_id
     * @param $key
     * @param $key_value
     * @param $direction
     * @return mixed
     */

    function reorderImage($action, $image_id, $key, $key_value, $direction)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT ' . $action . '_image_id, sort_order FROM ' . DB_PREFIX . '_' . $action . '_image WHERE image_id=?';
        $stmt = $DBC->query($query, array($image_id));
        $rr = array();
        if (!$stmt) {
            return;
        }
        $rr = $DBC->fetch($stmt);
        $record_image_id = $rr[$action . '_image_id'];
        $sort_order = $rr['sort_order'];

        if ($direction == 'down') {
            $query = 'SELECT ' . $action . '_image_id, sort_order FROM ' . DB_PREFIX . '_' . $action . '_image WHERE sort_order > ? AND `' . $key . '` = ? ORDER BY sort_order ASC';
            $stmt = $DBC->query($query, array($sort_order, $key_value));
            if (!$stmt) {
                return;
            }
            $rr = $DBC->fetch($stmt);
            $next_record_image_id = (int)$rr[$action . '_image_id'];
            if ($next_record_image_id == 0) {
                return;
            }
            $next_sort_order = $rr['sort_order'];

            $query = 'UPDATE ' . DB_PREFIX . '_' . $action . '_image SET sort_order=? WHERE ' . $action . '_image_id=?';
            $stmt = $DBC->query($query, array($next_sort_order, $record_image_id));

            $query = 'UPDATE ' . DB_PREFIX . '_' . $action . '_image SET sort_order=? WHERE ' . $action . '_image_id=?';
            $stmt = $DBC->query($query, array($sort_order, $next_record_image_id));
        }

        if ($direction == 'up') {
            $query = 'SELECT ' . $action . '_image_id, sort_order FROM ' . DB_PREFIX . '_' . $action . '_image WHERE sort_order < ? AND `' . $key . '` = ? ORDER BY sort_order DESC';
            $stmt = $DBC->query($query, array($sort_order, $key_value));
            if (!$stmt) {
                return;
            }
            $rr = $DBC->fetch($stmt);
            $next_record_image_id = (int)$rr[$action . '_image_id'];
            if ($next_record_image_id == 0) {
                return;
            }
            $next_sort_order = $rr['sort_order'];
            $query = 'UPDATE ' . DB_PREFIX . '_' . $action . '_image SET sort_order=? WHERE ' . $action . '_image_id=?';
            $stmt = $DBC->query($query, array($next_sort_order, $record_image_id));

            $query = 'UPDATE ' . DB_PREFIX . '_' . $action . '_image SET sort_order=? WHERE ' . $action . '_image_id=?';
            $stmt = $DBC->query($query, array($sort_order, $next_record_image_id));
        }
    }

    function reorderTopics($orderArray)
    {
        if (count($orderArray) > 0) {
            $DBC = DBC::getInstance();
            $query = 'UPDATE ' . DB_PREFIX . '_topic SET `order`=? WHERE id=?';
            foreach ($orderArray as $k => $v) {
                $DBC->query($query, array((int)$v, (int)$k));
            }
        }
    }

    /**
     * Delete image files
     * @param $image_id image id
     * @return boolean
     */
    function deleteImageFiles($image_id)
    {
        $path = SITEBILL_DOCUMENT_ROOT . $this->storage_dir;
        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . IMAGE_TABLE . ' WHERE image_id=?';
        $stmt = $DBC->query($query, array((int)$image_id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                if (defined('STR_MEDIA') && STR_MEDIA == Sitebill::MEDIA_SAVE_FOLDER) {
                    $preview = $ar['preview'];
                    $normal = $ar['normal'];
                    @unlink(MEDIA_FOLDER . '/' . $preview);
                    @unlink(MEDIA_FOLDER . '/' . $normal);
                    @unlink(MEDIA_FOLDER . '/nowatermark/' . $normal);
                } else {
                    $preview = $ar['preview'];
                    $normal = $ar['normal'];
                    @unlink($path . $preview);
                    @unlink($path . $normal);
                    @unlink($path . 'nowatermark/' . $normal);
                }
            }
        }
        return true;
    }

    static function getConfigValueStatic($key, $default = false)
    {
        if (!self::$config_loaded) {
            self::loadConfigStatic();
        }
        if (isset(self::$config_array[$key])) {
            return self::$config_array[$key];
        }
        return $default;
    }

    /**
     * Get config value
     * @param string $key key
     * @return string
     */
    function getConfigValue($key, $default = false)
    {
        if (!self::$config_loaded) {
            $this->loadConfig();
        }
        if (isset(self::$config_array[$key])) {
            return self::$config_array[$key];
        }
        return $default;
    }

    function setConfigValue($key, $value)
    {
        self::$config_array[$key] = $value;
    }

    function getAllConfigArray()
    {
        return self::$config_array;
    }

    /* function setConfigValue ( $key, $value ) {
      if ( !$this->config_loaded ) {
      $this->loadConfig();
      }
      $this->config_array[$key]=$value;
      } */

    /**
     * Get debug mode
     * @param void
     * @return boolean
     */
    function getDebugMode()
    {
        return DEBUG_MODE;
    }

    /**
     * Set debug mode
     * @param boolean
     * @return void
     */
    function setDebugMode($debug_mode)
    {
        return;
    }

    function htmlspecialchars($value, $flags = '')
    {
        if ($flags == '') {
            $flags = ENT_COMPAT | ENT_HTML401;
        }
        if (is_array($value)) {
            if (count($value) > 0) {
                foreach ($value as $ak => $av) {
                    if (is_array($av)) {
                        $value[$ak] = $this->htmlspecialchars($av);
                    } else {
                        $value[$ak] = $this->escape(htmlspecialchars($av, $flags, SITE_ENCODING));
                    }
                }
            }
        } else {
            $value = $this->escape(htmlspecialchars($value, $flags, SITE_ENCODING));
        }
        return $value;
    }

    protected function restoreFavorites($user_id)
    {

        if (isset($_COOKIE['user_favorites']) && $_COOKIE['user_favorites'] != '') {
            $cc = unserialize($_COOKIE['user_favorites']);
        } else {
            $cc = array();
        }
        $cc[$user_id] = array();
        $DBC = DBC::getInstance();
        $query = 'SELECT id FROM ' . DB_PREFIX . '_userlists WHERE user_id=? AND lcode=?';
        $stmt = $DBC->query($query, array($user_id, 'fav'));

        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                $cc[$user_id][$ar['id']] = $ar['id'];
            }
        }

        @setcookie('user_favorites', '', time() - 7 * 24 * 3600, '/', self::$_cookiedomain);
        @setcookie('user_favorites', serialize($cc), time() + 7 * 24 * 3600, '/', self::$_cookiedomain);
        $_SESSION['favorites'] = $cc[$user_id];
        unset($cc);
    }

    function htmlspecialchars_decode($value, $flags = '')
    {
        if ($flags == '') {
            if (defined('ENT_HTML401')) {
                $flags = ENT_COMPAT | ENT_HTML401;
            } else {
                $flags = ENT_COMPAT;
            }
        }
        if (is_array($value)) {
            if (count($value) > 0) {
                foreach ($value as $ak => $av) {
                    if (is_array($av)) {
                        $value[$ak] = $this->htmlspecialchars_decode($av);
                    } else {
                        $value[$ak] = htmlspecialchars_decode($av, $flags);
                    }
                }
            }
        } else {
            $value = htmlspecialchars_decode($value, $flags);
        }
        return $value;
    }

    function get_phpinput_value($key)
    {
        $flags = ENT_COMPAT | ENT_HTML401;
        if (empty($this->phpinput_data)) {
            $this->phpinput_data = json_decode(file_get_contents('php://input'), true);
        }
        if (!empty($this->phpinput_data[$key])) {
            return $this->sanitize($this->phpinput_data[$key], $flags);
        }
        return null;
    }

    /**
     * Get value
     * @param string $key key
     * @return string
     */
    function getRequestValue($key, $type = '', $from = '')
    {
        $flags = ENT_COMPAT | ENT_HTML401;
        $value = NULL;
        switch ($from) {
            case 'get' :
            {
                if (isset($_GET[$key])) {
                    $value = $this->escape($_GET[$key]);
                    $value = htmlspecialchars($_GET[$key], $flags, SITE_ENCODING);
                }
                break;
            }
            case 'post' :
            {
                if (isset($_POST[$key])) {
                    $value = $this->escape($_POST[$key]);
                }
                break;
            }
            default :
            {
                if (isset($_GET[$key])) {
                    $value = $_GET[$key];
                    $value = $this->sanitize($value, $flags);
                } elseif (isset($_POST[$key])) {
                    $value = $_POST[$key];
                    $value = $this->sanitize($value, $flags);
                } elseif (isset($_REQUEST[$key])) {
                    $value = $_REQUEST[$key];
                    $value = $this->sanitize($value, $flags);
                }
            }
        }

        //Попробуем получить из PHP://INPUT значение
        if ($value === NULL) {
            $value = $this->get_phpinput_value($key);
        }


        if ($value === NULL) {
            return $value;
        }

        if (!is_array($value)) {
            $value = trim($value);
            $value = $this->getSafeValue($value);
            if ($this->getConfigValue('sql_paranoid_mode')) {
                if (preg_match('/union/i', $value)) {
                    return NULL;
                }
                if (preg_match('/left\sjoin/i', $value)) {
                    return NULL;
                }

                if (preg_match('/sleep[\s]*\(/i', $value)) {
                    return NULL;
                }
                if (preg_match('/benchmark/i', $value)) {
                    return NULL;
                }

                if (preg_match_all('/select/i', $value, $matches)) {
                    if (count($matches[0]) > 1) {
                        return NULL;
                    }
                }
            }
            return $value;
        } elseif (is_array($value)) {
            $values = $value;
            foreach ($values as $k => $v) {
                if (!is_array($v)) {
                    $v = trim($v);
                    $v = $this->getSafeValue($v);
                    if (($v === '' || preg_match('/union/i', $v) || preg_match('/select/i', $v) || preg_match('/left\sjoin/i', $v) || preg_match('/sleep[\s]*\(/i', $v)) and $this->getConfigValue('sql_paranoid_mode')) {
                        unset($values[$k]);
                    } else {
                        $values[$k] = $v;
                    }
                }
            }
            if (count($values) == 0) {
                return array();
            } else {
                return $values;
            }
        }

        switch ($type) {
            case 'int' :
            {
                if (!is_array($value)) {
                    $value = (int)$value;
                } else {
                    $value = 0;
                }

                break;
            }
            case 'bool' :
            {
                $value = (bool)$value;
                break;
            }
            case 'float' :
            {
                $value = preg_replace('/[^\d\.,]/', '', $value);
                break;
            }
        }

        return $value;
    }

    private function xssProtect($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = htmlspecialchars($v);
            }
        } else {
            $value = htmlspecialchars($value);
        }
        return $value;
    }

    private function getSafeValue($value)
    {
        return preg_replace('/(\/\*[^\/]*\*\/)/', '', $value);
    }

    /**
     * Set request value
     * @param string $key key
     * @param string $value value
     * @return void
     */
    function setRequestValue($key, $value)
    {
        $_REQUEST[$key] = $value;
        $_POST[$key] = $value;
        return;
    }

    /**
     * Rise error
     * @param string $error_message error message
     * @return void
     */
    function riseError($error_message)
    {
        $this->writeLog('<span class="error">error: ' . $error_message . '</span>', true);
        $this->error_message = $error_message;
        $this->error_state = true;
    }

    function clearError()
    {
        $this->error_message = '';
        $this->error_state = false;
    }

    /**
     * Get error
     * @param void
     * @return boolean
     */
    function getError()
    {
        return $this->error_message;
    }

    /**
     * Get error message
     * @param void
     * @return string
     */
    function GetErrorMessage()
    {
        return $this->error_message;
    }

    /**
     * Write log message
     * @param string $message message
     * @return void
     */
    function writeLog($message, $enable_trace = false)
    {
        if ($enable_trace) {

            /*
            ob_start();
            debug_print_backtrace();
            $trace = ob_get_contents();
            ob_end_clean();*/

            //$message.= '<hr>Stack trace<br><pre>'.$trace.'</pre>';
        }

        self::add_debug_message($message);

        if ($this->getConfigValue('apps.logger.enable') and file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/logger/admin/admin.php')) {
            if ( !isset($this->logger_admin) ) {
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');
                require_once(SITEBILL_DOCUMENT_ROOT . '/apps/logger/admin/admin.php');
                $this->logger_admin = new logger_admin();
            }
            if (is_array($message)) {
                $this->logger_admin->write_log($message);
            } else {
                $message_array = array('apps_name' => '', 'method' => '', 'message' => $message, 'type' => '');
                $this->logger_admin->write_log($message_array);
            }
            return;
        }
        return;
    }

    function writeArrayLog($array, $enable_trace = false)
    {
        $message = '<pre>' . var_export($array, true) . '</pre>';
        if ($enable_trace) {
            ob_start();
            debug_print_backtrace();
            $trace = ob_get_contents();
            ob_end_clean();
            $message .= '<hr>Stack trace<br><pre>' . $trace . '</pre>';
        }

        $this->writeLog($message);
    }

    /**
     * Get image list admin
     * @param string $action action
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record id
     * @return string
     */
    function getImageListAdmin($action, $table_name, $key, $record_id, &$callback_count = NULL, $no_controls = false)
    {

        if (SITEBILL_MAIN_URL != '') {
            $url = SITEBILL_MAIN_URL . '/' . $this->storage_dir;
        } else {
            $url = $this->storage_dir;
        }

        $record_id = (int)$record_id;

        if ($record_id == 0) {
            return '';
        }


        //$query = "SELECT i.* FROM ".DB_PREFIX."_".$table_name."_image AS li, ".IMAGE_TABLE." AS i WHERE li.".$key."=$record_id AND li.image_id=i.image_id ORDER BY li.sort_order";
        $query = 'SELECT i.* FROM ' . DB_PREFIX . '_' . $table_name . '_image AS li, ' . IMAGE_TABLE . ' AS i WHERE li.' . $key . '=? AND li.image_id=i.image_id ORDER BY li.sort_order';
        $DBC = DBC::getInstance();
        $stmt = $DBC->query($query, array($record_id));
        if ($stmt) {
            $i = 0;
            $rs .= '<style>
    			.preview_admin { float: left; min-height: 250px; padding: 5px; margin: 5px; }
    			.preview_admin td > img { width: 100px; border: 1px solid #CFCFCF;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
	border-radius: 5px;
	margin-bottom: 5px;}
    
    			</style>';

            $rs .= '<script type="text/javascript" src="' . SITEBILL_MAIN_URL . '/apps/system/js/dataimagelist.js?v=1"></script>';
            $rs .= '<script type="text/javascript">DataImagelist.attachDblclick();</script>';


            while ($ar = $DBC->fetch($stmt)) {

                $rs .= '<div class="preview_admin">
    		<table border="0" id="data_gallery">';

                if (isset($ar['title'])) {
                    $rs .= '<tr><td class="field_tab" style="height:20px; border: 1px solid gray;" alt="' . $ar['image_id'] . '">' . $ar['title'] . '<td></tr>';
                }
                if (isset($ar['description'])) {
                    $rs .= '<tr><td class="field_tab_description" style="height:20px; border: 1px solid gray;" alt="' . $ar['image_id'] . '">' . $ar['description'] . '<td></tr>';
                }


                $rs .= '<tr>
    		<td>
    		<br />
    		<img src="' . $url . '' . $ar['preview'] . '" border="0" align="left"/><br>
    		</td>';
                $rs .= '</tr>';

                $rs .= '<tr>';
                $rs .= '<td>';
                $rs .= '<a href="javascript:void(0);" onClick="DataImagelist.deleteImage(this,' . $ar['image_id'] . ',' . $record_id . ',\'' . $table_name . '\',\'' . $key . '\')"><img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/delete.png" width="16" border="0" alt="удалить" title="удалить"></a>
    		<a href="javascript:void(0);" onClick="DataImagelist.upImage(this,' . $ar['image_id'] . ',' . $record_id . ',\'' . $table_name . '\',\'' . $key . '\')"><img src="' . SITEBILL_MAIN_URL . '/img/up.gif" border="0" alt="наверх" title="наверх"></a>
    		<a href="javascript:void(0);" onClick="DataImagelist.downImage(this,' . $ar['image_id'] . ',' . $record_id . ',\'' . $table_name . '\',\'' . $key . '\')"><img src="' . SITEBILL_MAIN_URL . '/img/down1.gif" border="0" alt="вниз" title="вниз"></a>
    		<a href="javascript:void(0);" onClick="DataImagelist.makeMain(this,' . $ar['image_id'] . ',' . $record_id . ',\'' . $table_name . '\',\'' . $key . '\')">Сделать главной</a>
    		<!--<a href="javascript:void(0);" onClick="DataImagelist.rotateImage(this,' . $ar['image_id'] . ',' . $record_id . ',\'' . $table_name . '\',\'' . $key . '\', \'ccw\')"><img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/rotccw.png" border="0" alt="наверх" title="Повернуть против часовой стрелки"></a>
    		<a href="javascript:void(0);" onClick="DataImagelist.rotateImage(this,' . $ar['image_id'] . ',' . $record_id . ',\'' . $table_name . '\',\'' . $key . '\', \'cw\')"><img src="' . SITEBILL_MAIN_URL . '/apps/admin/admin/template/img/rotcw.png" border="0" alt="наверх" title="Повернуть по часовой стрелке"></a>-->
    				</td>
    		</tr>';

                $rs .= '</table>
    		</div>';
                //$rs .= '<div style="clear: both;"></div>';
                $i++;
            }
            if ($callback_count !== NULL) {
                $callback_count = $i;
            }
        }
        return $rs;
    }

    /**
     * Get file list admin
     * @param string $action action
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record id
     * @return string
     */
    function getFileListAdmin($action, $table_name, $key, $record_id)
    {
        if (SITEBILL_MAIN_URL != '') {
            $url = SITEBILL_MAIN_URL . '/' . $this->storage_dir;
        } else {
            $url = $this->storage_dir;
        }
        $record_id = (int)$record_id;
        $DBC = DBC::getInstance();
        $query = 'SELECT i.* FROM ' . DB_PREFIX . '_' . $table_name . '_image AS li, ' . IMAGE_TABLE . ' AS i WHERE li.' . $key . '=? AND li.image_id=i.image_id ORDER BY li.sort_order';
        $stmt = $DBC->query($query, array($record_id));
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                /* $up_link = '?action='.$action.'&do=edit&'.$key.'='.$record_id.'&subdo=up_image&image_id='.$ar['image_id'];
                  $down_link = '?action='.$action.'&do=edit&'.$key.'='.$record_id.'&subdo=down_image&image_id='.$ar['image_id'];


                  $up_link_img = '<a href="'.$up_link.'"><img src="'.SITEBILL_MAIN_URL.'/img/up.gif" border="0" alt="наверх" title="наверх"></a>';
                  $down_link_img = '<a href="'.$down_link.'"><img src="'.SITEBILL_MAIN_URL.'/img/down1.gif" border="0" alt="вниз" title="вниз"></a>';
                 */
                $delete_link = '?action=' . $action . '&do=edit&' . $key . '=' . $record_id . '&subdo=delete_image&image_id=' . $ar['image_id'];
                $rs .= '<div class="preview_admin" style="padding: 2px; border: 1px solid gray;">
    		<table border="0">
    		<tr>
    		<td>
    		<a href="' . $url . $ar['preview'] . '" target="_blank"><img src="/img/file.png" border="0" align="left"/> ' . $ar['preview'] . '</a><br>
    		</td>
    		<td>
    		<a href="' . $delete_link . '" onclick="return confirm(\'' . Multilanguage::_('L_MESSAGE_REALLY_WANT_DELETE') . '\');">' . Multilanguage::_('L_DELETE_LC') . '</a>
    		
    		</td>
    		</tr>
    		</table>
    		</div>';
                $rs .= '<div style="clear: both;"></div>';
            }
        }
        return $rs;
    }

    function get_page_links_list_default($page, $total, $per_page, $params)
    {
        if ($total <= $per_page) {
            return '';
        }
        if (isset($params['page_url']) && $params['page_url'] != '') {
            $url = SITEBILL_MAIN_URL . '/' . $params['page_url'];
            unset($params['page_url']);
        } else {
            $url = '';
        }
        $pairs = array();
        unset($params['page']);
        if (count($params) > 0) {
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
                    $pairs[] = "$key=$value";
                }
            }
        }
        if (count($pairs) > 0) {
            $url = $url . '?' . implode('&', $pairs);
        } else {
            $url = $url;
        }

        $current_page = $page;
        if ($current_page == '') {
            $current_page = 1;
        } else {
            $current_page = (int)$current_page;
        }

        $limit = $per_page;

        $total_pages = ceil($total / $limit);
        $page_navigation = '';
        $first_page_navigation = '';
        $last_page_navigation = '';
        $start_page_navigation = '';
        $end_page_navigation = '';
        $p_prew = $current_page - 1;
        $p_next = $current_page + 1;

        $last_number_page = '<li><a rel="nofollow" href="' . $url . (false !== strpos($url, '?') ? '&page=' . $total_pages : '?page=' . $total_pages) . '" class="pagenav"><strong>' . $total_pages . '</strong></a></li>';

        if ($current_page == 1) {
            $first_page_navigation .= '<li><span class="pagenav">&laquo;&laquo; </span></li>';
        } else {
            $first_page_navigation .= '<li><a rel="nofollow" href="' . $url . (false !== strpos($url, '?') ? '&page=1' : '?page=1') . '" class="pagenav" title="в начало">&laquo;&laquo; </a></li>';
        }

        if ($current_page == $total_pages) {
            $last_page_navigation .= '<li><span class="pagenav"> &raquo;&raquo;</span></li>';
            $last_number_page = '';
        } else {
            $last_page_navigation .= '<li><a rel="nofollow" href="' . $url . (false !== strpos($url, '?') ? '&page=' . $total_pages : '?page=' . $total_pages) . '" class="pagenav" title="в конец"> &raquo;&raquo;</a></li>';
        }

        if ($p_prew < 1) {
            $start_page_navigation .= '<li><span class="pagenav">&laquo; </span></li>';
        } else {
            $start_page_navigation .= '<li><a rel="nofollow" href="' . $url . (false !== strpos($url, '?') ? '&page=' . $p_prew : '?page=' . $p_prew) . '" class="pagenav" title="предыдущая">&laquo; </a></li>';
        }

        if ($p_next > $total_pages) {
            $end_page_navigation .= '<li><span class="pagenav"> &raquo;</span></li>';
        } else {
            $end_page_navigation .= '<li><a rel="nofollow" href="' . $url . (false !== strpos($url, '?') ? '&page=' . $p_next : '?page=' . $p_next) . '" class="pagenav" title="следующая"> &raquo;</a></li>';
        }


        $linestart = $current_page - 7;
        $lineend = $current_page + 7;

        if ($linestart <= 1) {
            $linestart = 1;
            $lineprefix = '';
        } else {
            $lineprefix = '<li>...</li>';
        }

        if ($lineend >= $total_pages) {
            $lineend = $total_pages;
            $last_number_page = '';
            $linepostfix = '';
        } else {
            $linepostfix = '<li>...</li>';
        }

        for ($i = $linestart; $i <= $lineend; $i++) {
            if ($current_page == $i) {
                $page_navigation .= '<li><span class="pagenav"> ' . $i . ' </span></li>';
            } else {
                $page_navigation .= '<li><a rel="nofollow" href="' . $url . (false !== strpos($url, '?') ? '&page=' . $i : '?page=' . $i) . '" class="pagenav"><strong>' . $i . '</strong></a></li>';
            }
        }
        $page_navigation = '<ul class="pagination">' . $first_page_navigation . $start_page_navigation . $lineprefix . $page_navigation . $linepostfix . $end_page_navigation . $last_number_page . $last_page_navigation . '</ul>';
        return $page_navigation;
    }

    /**
     * Get page links list
     * @param int $cur_page current page number
     * @param int $total
     * @param int $per_page
     * @param array $params
     * @return array
     */
    function get_page_links_list($page, $total, $per_page, $params)
    {

        if (defined('ADMIN_MODE')) {
            return $this->get_page_links_list_default($page, $total, $per_page, $params);
        }

        $pager_settings = array();
        $pager_settings['draw_all_pages'] = intval($this->getConfigValue('core.listing.pager_draw_all'));
        $pager_settings['draw_all_pages_max'] = intval($this->getConfigValue('core.listing.pager_draw_all_max'));
        $pager_settings['active_page_offset'] = intval($this->getConfigValue('core.listing.pager_page_offset'));
        $pager_settings['show_end_links'] = intval($this->getConfigValue('core.listing.pager_end_buttons'));
        $pager_settings['show_prev_links'] = intval($this->getConfigValue('core.listing.pager_prev_buttons'));
        $pager_settings['show_prefixes'] = intval($this->getConfigValue('core.listing.pager_show_prefixes'));

        if ($total <= $per_page) {
            return '';
        }

        if (isset($params['page_url']) && $params['page_url'] != '') {
            //$url = SITEBILL_MAIN_URL . '/' . $params['page_url'] . '/?';
            $url = $params['page_url'] . (false === strpos($params['page_url'], '.') ? '/' : '') . '?';
        } else {
            //$url = SITEBILL_MAIN_URL . '/?';
            $url = '?';
        }

        unset($params['page_url']);
        unset($params['page']);

        if (count($params) > 0) {
            $pager_params_string = urldecode(http_build_query($params));
        } else {
            $pager_params_string = '';
        }


        $current_page = $page;
        if ($current_page == '') {
            $current_page = 1;
        } else {
            $current_page = (int)$current_page;
        }

        $limit = $per_page;

        $total_pages = ceil($total / $limit);
        if ($total_pages <= $pager_settings['draw_all_pages_max']) {
            $pager_settings['draw_all_pages'] = 1;
        }
        $pages_count = ceil($total / $limit);
        if ($total_pages < 2) {
            return '';
        }

        $ret = array();

        $p_prew = $current_page - 1;
        $p_next = $current_page + 1;

        if ($current_page == 1) {
            $fpn['text'] = '&laquo;&laquo;';
            $fpn['href'] = $this->createUrlTpl($url . 'page=1' . ($pager_params_string != '' ? '&' . $pager_params_string : ''));
        } else {
            $fpn['text'] = '&laquo;&laquo;';
            $fpn['href'] = $this->createUrlTpl($url . 'page=1' . ($pager_params_string != '' ? '&' . $pager_params_string : ''));
        }

        $ret['fpn'] = $fpn;

        if ($current_page == $total_pages) {
            $lpn['text'] = '&raquo;&raquo;';
            $lpn['href'] = '';
        } else {
            $lpn['text'] = '&raquo;&raquo;';
            $lpn['href'] = $this->createUrlTpl($url . 'page=' . $total_pages . ($pager_params_string != '' ? '&' . $pager_params_string : ''));
        }

        $ret['lpn'] = $lpn;

        if ($p_prew < 1) {
            $ppn['text'] = '&laquo;';
            $ppn['href'] = '';
        } else {
            $ppn['text'] = '&laquo;';
            $ppn['href'] = $this->createUrlTpl($url . 'page=' . $p_prew . ($pager_params_string != '' ? '&' . $pager_params_string : ''));
            $ppn['go_page'] = $p_prew;
        }

        $ret['ppn'] = $ppn;

        if ($p_next > $total_pages) {
            $npn['text'] = '&raquo;';
            $npn['href'] = '';
        } else {
            $npn['text'] = '&raquo;';
            $npn['href'] = $this->createUrlTpl($url . 'page=' . $p_next . ($pager_params_string != '' ? '&' . $pager_params_string : ''));
            $npn['go_page'] = $p_next;
        }

        $ret['npn'] = $npn;

        $start_page = $current_page - $pager_settings['active_page_offset'];
        $end_page = $current_page + $pager_settings['active_page_offset'];

        if ($start_page <= 1) {
            $pager_settings['left_prefix'] = 0;
            $pager_settings['start'] = 1;
        } else {
            $pager_settings['left_prefix'] = 0;
            if ($pager_settings['show_prefixes'] == 1) {
                $pager_settings['left_prefix'] = 1;
            }
            $pager_settings['start'] = $start_page;
        }

        if ($end_page >= $total_pages) {
            $pager_settings['right_prefix'] = 0;
            $pager_settings['end'] = $total_pages;
        } else {
            $pager_settings['right_prefix'] = 0;
            if ($pager_settings['show_prefixes'] == 1) {
                $pager_settings['right_prefix'] = 1;
            }
            $pager_settings['end'] = $end_page;
        }

        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i == $current_page) {
                $ret['pages'][$i] = array('text' => $i, 'href' => '', 'current' => '1');
            } else {
                $ret['pages'][$i] = array('text' => $i, 'href' => $this->createUrlTpl($url . 'page=' . $i . ($pager_params_string != '' ? '&' . $pager_params_string : '')), 'current' => '0');
            }
        }

        $ret['current_page'] = $current_page;
        $ret['total_pages'] = $total_pages;

        global $smarty;
        $smarty->assign('pager_settings', $pager_settings);
        $smarty->assign('paging', $ret);
        $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/common_pager.tpl';
        if (!file_exists($tpl)) {
            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/system/template/common_pager_' . $this->getConfigValue('theme') . '.tpl')) {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/system/template/common_pager_' . $this->getConfigValue('theme') . '.tpl';
            } else {
                $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/system/template/common_pager.tpl';
            }
        }
        return $smarty->fetch($tpl);
    }

    /**
     * Get image list admin
     * @param string $action action
     * @param string $table_name table name
     * @param string $key key
     * @param int $record_id record id
     * @param int $limit limit value
     * @return string
     */
    function get_image_array($action, $table_name, $key, $record_id, $limit = 0)
    {
        return array();
    }

    /**
     * Get category breadcrumbs
     * @param array $params
     * @param array $category_structure
     * @param string $url
     * @return string
     */
    function get_category_breadcrumbs($params, $category_structure, $url = '')
    {
        $rs = '';


        if (!isset($params['topic_id']) || is_array($params['topic_id'])) {
            return $rs;
        }

        if ((int)$params['topic_id'] == 0) {
            return $rs;
        }
        if (!isset($category_structure['catalog'][$params['topic_id']])) {
            return $rs;
        }


        //foreach ( $category_structure['childs'][0] as $item_id => $catalog_id ) {

        $path = '';
        if ($category_structure['catalog'][$params['topic_id']]['url'] != '') {
            $path = rtrim($url, '/') . '/' . $category_structure['catalog'][$params['topic_id']]['url'];
        } else {
            $path = rtrim($url, '/') . '/' . '/topic' . $params['topic_id'] . '.html';
        }

        $ra[] = '<a itemprop="item" title="' . $category_structure['catalog'][$params['topic_id']]['name'] . '" href="' . $this->createUrlTpl($path) . '"><span itemprop="name">' . $category_structure['catalog'][$params['topic_id']]['name'] . '</span></a>';


        $parent_category_id = $category_structure['catalog'][$params['topic_id']]['parent_id'];
        while ($category_structure['catalog'][$parent_category_id]['parent_id'] != 0) {
            if ($j++ > 100) {
                return;
            }

            $path = '';
            if (isset($category_structure['catalog'][$parent_category_id]) && $category_structure['catalog'][$parent_category_id]['url'] != '') {
                $path = rtrim($url, '/') . '/' . $category_structure['catalog'][$parent_category_id]['url'];
            } else {
                $path = rtrim($url, '/') . '/' . '/topic' . $parent_category_id . '.html';
            }

            $ra[] = '<a itemprop="item" title="' . $category_structure['catalog'][$parent_category_id]['name'] . '" href="' . $this->createUrlTpl($path) . '"><span itemprop="name">' . $category_structure['catalog'][$parent_category_id]['name'] . '</span></a>';

            $parent_category_id = $category_structure['catalog'][$parent_category_id]['parent_id'];
        }
        if (isset($category_structure['catalog'][$parent_category_id]) && $category_structure['catalog'][$parent_category_id]['name'] != '') {
            $path = '';
            if ($category_structure['catalog'][$parent_category_id]['url'] != '') {
                $path = rtrim($url, '/') . '/' . $category_structure['catalog'][$parent_category_id]['url'];
            } else {
                $path = rtrim($url, '/') . '/' . '/topic' . $parent_category_id . '.html';
            }

            $ra[] = '<a itemprop="item" title="' . $category_structure['catalog'][$parent_category_id]['name'] . '" href="' . $this->createUrlTpl($path) . '"><span itemprop="name">' . $category_structure['catalog'][$parent_category_id]['name'] . '</span></a>';

        }
        if (Multilanguage::is_set('LT_BC_HOME', '_template')) {
            $ra[] = '<a itemprop="item" title="' . Multilanguage::_('LT_BC_HOME', '_template') . '" href="' . $this->createUrlTpl('') . '"><span itemprop="name">' . Multilanguage::_('LT_BC_HOME', '_template') . '</span></a>';
        } else {
            $ra[] = '<a itemprop="item" title="' . Multilanguage::_('L_HOME') . '" href="' . $this->createUrlTpl('') . '"><span itemprop="name">' . Multilanguage::_('L_HOME') . '</span></a>';
        }
        //$ra[]='<a href="'.SITEBILL_MAIN_URL.'/">'.Multilanguage::_('L_HOME').'</a>';
        $breadcrumbs_array = array_reverse($ra);
        $position = 1;
        foreach ($breadcrumbs_array as $item) {
            $li_breadcrumbs[] = '<span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">' . $item . '<meta itemprop="position" content="' . $position . '" /></span>';
            $position++;
        }
        $rs = implode(' / ', $li_breadcrumbs);
        $rs_result = '<div itemscope itemtype="https://schema.org/BreadcrumbList">' . $rs . '</div>';

        $this->template->assert('breadcrumbs_array', $breadcrumbs_array);

        return $rs_result;
    }

    /*
     * тестовая функция для кастомизации крошек
     */
    function get_category_breadcrumbs_test($params, $category_structure, $url = '')
    {
        $rs = '';
        $bc_array = array();

        if (!isset($params['topic_id']) || is_array($params['topic_id'])) {
            return $rs;
        }

        if ((int)$params['topic_id'] == 0) {
            return $rs;
        }
        if (!isset($category_structure['catalog'][$params['topic_id']])) {
            return $rs;
        }


        //foreach ( $category_structure['childs'][0] as $item_id => $catalog_id ) {
        if ($category_structure['catalog'][$params['topic_id']]['url'] != '') {
            $ra[] = '<a href="' . rtrim($url, '/') . '/' . $category_structure['catalog'][$params['topic_id']]['url'] . (false === strpos($category_structure['catalog'][$params['topic_id']]['url'], '.') ? self::$_trslashes : '') . '">' . $category_structure['catalog'][$params['topic_id']]['name'] . '</a>';
            $bc_array[] = array(
                'href' => SITEBILL_MAIN_URL . '/' . $category_structure['catalog'][$params['topic_id']]['url'] . (false === strpos($category_structure['catalog'][$params['topic_id']]['url'], '.') ? self::$_trslashes : ''),
                'name' => $category_structure['catalog'][$params['topic_id']]['name']
            );

        } else {
            $ra[] = '<a href="' . rtrim($url, '/') . '/topic' . $params['topic_id'] . '.html">' . $category_structure['catalog'][$params['topic_id']]['name'] . '</a>';
            $bc_array[] = array(
                'href' => SITEBILL_MAIN_URL . '/topic' . $params['topic_id'] . '.html',
                'name' => $category_structure['catalog'][$params['topic_id']]['name']
            );
        }

        $parent_category_id = $category_structure['catalog'][$params['topic_id']]['parent_id'];
        while ($category_structure['catalog'][$parent_category_id]['parent_id'] != 0) {
            if ($j++ > 100) {
                return;
            }
            if (isset($category_structure['catalog'][$parent_category_id]) && $category_structure['catalog'][$parent_category_id]['url'] != '') {
                $ra[] = '<a href="' . rtrim($url, '/') . '/' . $category_structure['catalog'][$parent_category_id]['url'] . (false === strpos($category_structure['catalog'][$parent_category_id]['url'], '.') ? self::$_trslashes : '') . '">' . $category_structure['catalog'][$parent_category_id]['name'] . '</a>';
                $bc_array[] = array(
                    'href' => SITEBILL_MAIN_URL . '/' . $category_structure['catalog'][$parent_category_id]['url'] . (false === strpos($category_structure['catalog'][$parent_category_id]['url'], '.') ? self::$_trslashes : ''),
                    'name' => $category_structure['catalog'][$parent_category_id]['name']
                );
            } else {
                $ra[] = '<a href="' . rtrim($url, '/') . '/topic' . $parent_category_id . '.html">' . $category_structure['catalog'][$parent_category_id]['name'] . '</a>';
                $bc_array[] = array(
                    'href' => SITEBILL_MAIN_URL . '/topic' . $parent_category_id . '.html',
                    'name' => $category_structure['catalog'][$parent_category_id]['name']
                );
            }
            $parent_category_id = $category_structure['catalog'][$parent_category_id]['parent_id'];
        }
        if (isset($category_structure['catalog'][$parent_category_id]) && $category_structure['catalog'][$parent_category_id]['name'] != '') {
            if ($category_structure['catalog'][$parent_category_id]['url'] != '') {
                $ra[] = '<a href="' . rtrim($url, '/') . '/' . $category_structure['catalog'][$parent_category_id]['url'] . (false === strpos($category_structure['catalog'][$parent_category_id]['url'], '.') ? self::$_trslashes : '1') . '">' . $category_structure['catalog'][$parent_category_id]['name'] . '</a>';
                $bc_array[] = array(
                    'href' => SITEBILL_MAIN_URL . '/' . $category_structure['catalog'][$parent_category_id]['url'] . (false === strpos($category_structure['catalog'][$parent_category_id]['url'], '.') ? self::$_trslashes : ''),
                    'name' => $category_structure['catalog'][$parent_category_id]['name']
                );

            } else {
                $ra[] = '<a href="' . rtrim($url, '/') . '/topic' . $parent_category_id . '.html">' . $category_structure['catalog'][$parent_category_id]['name'] . '</a>';
                $bc_array[] = array(
                    'href' => SITEBILL_MAIN_URL . '/topic' . $parent_category_id . '.html',
                    'name' => $category_structure['catalog'][$parent_category_id]['name']
                );

            }
        }
        if (Multilanguage::is_set('LT_BC_HOME', '_template')) {
            $ra[] = '<a href="' . SITEBILL_MAIN_URL . '/">' . Multilanguage::_('LT_BC_HOME', '_template') . '</a>';
            $bc_array[] = array(
                'href' => SITEBILL_MAIN_URL . '/',
                'name' => Multilanguage::_('LT_BC_HOME', '_template')
            );
        } else {
            $ra[] = '<a href="' . SITEBILL_MAIN_URL . '/">' . Multilanguage::_('L_HOME') . '</a>';
            $bc_array[] = array(
                'href' => SITEBILL_MAIN_URL . '/',
                'name' => Multilanguage::_('L_HOME')
            );
        }
        $bc_array = array_reverse($bc_array);
        //print_r($bc_array);
        //$ra[]='<a href="'.SITEBILL_MAIN_URL.'/">'.Multilanguage::_('L_HOME').'</a>';
        $rs = implode(' / ', array_reverse($ra));
        return $rs;
    }

    /**
     * Get category breadcrumbs
     * @param array $params
     * @param array $category_structure
     * @param string $url
     * @return string
     */
    function get_category_breadcrumbs_string($params, $category_structure, $url = '')
    {
        $rs = '';
        $ra = array();
        $parent_category_id = 0;
        $j = 0;
        if (isset($category_structure['catalog'][$params['topic_id']])) {
            $ra[] = '' . $category_structure['catalog'][$params['topic_id']]['name'] . '';
            $parent_category_id = $category_structure['catalog'][$params['topic_id']]['parent_id'];
        }


        while (isset($category_structure['catalog'][$parent_category_id]['parent_id']) && $category_structure['catalog'][$parent_category_id]['parent_id'] != 0) {
            if ($j++ > 100) {
                return;
            }
            $ra[] = '' . $category_structure['catalog'][$parent_category_id]['name'] . '';
            $parent_category_id = $category_structure['catalog'][$parent_category_id]['parent_id'];
        }
        if (isset($category_structure['catalog'][$parent_category_id]['name']) && $category_structure['catalog'][$parent_category_id]['name'] != '') {
            $ra[] = '' . $category_structure['catalog'][$parent_category_id]['name'] . '';
        }
        $this->set_breadcrumbs_array(array_reverse($ra));
        $rs = implode(' / ', array_reverse($ra));
        return $rs;
    }

    function set_breadcrumbs_array($breadcrumbs_array = array())
    {
        $this->breadcrumbs_array = $breadcrumbs_array;
    }

    function get_breadcrumbs_array()
    {
        return $this->breadcrumbs_array;
    }

    public function go301($new_location)
    {
        $sapi_name = php_sapi_name();
        if ($sapi_name == 'cgi' || $sapi_name == 'cgi-fcgi') {
            header('Status: 301 Moved Permanently');
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 301 Moved Permanently');
        }
        header('Location: ' . $new_location);
        exit();
    }

    /**
     * Make preview
     * @param
     * @return
     */
    function makePreview($src, $dst, $width, $height, $ext = 'jpg', $md = 0, $final_ext = '')
    {
        $dst_info = pathinfo($dst);

        if (!is_file($src) or empty($dst_info['extension'])) {
            return false;
        }
        $source_img = false;
        if ($ext == 'jpg' || $ext == 'jpeg') {
            $source_img = @ImageCreateFromJPEG($src);
        } elseif ($ext == 'png') {
            $source_img = @ImageCreateFromPNG($src);
        } elseif ($ext == 'gif') {
            $source_img = @ImageCreateFromGIF($src);
        } elseif ($ext == 'webp') {
            $source_img = @ImageCreateFromWebp($src);
        }

        if ($source_img === false) {
            return false;
        }

        $w_src = imagesx($source_img);
        $h_src = imagesy($source_img);
        if ($w_src > $h_src) {
            $mode = 'width';
        } else {
            $mode = 'height';
        }
        if ($md == 'height') {
            $mode = 'height';
        }
        if ($md == 'width') {
            $mode = 'width';
        }
        if ($md == 'smart') {
            $mode = 'smart';
        }
        if ($md == 'c' || $md == 'f') {
            $mode = $md;
        }

        if ($mode == 'smart' || $mode == 'c') {
            $source_width = $w_src;
            $source_height = $h_src;

            $dest_width = $width;
            $dest_height = $height;

            $width_proportion = $source_width / $dest_width;
            $height_proportion = $source_height / $dest_height;

            if ($width_proportion < $height_proportion) {
                $common_proportion = $width_proportion;
            } else {
                $common_proportion = $height_proportion;
            }

            $equal_width = $dest_width * $common_proportion;
            $equal_height = $dest_height * $common_proportion;


            $width_offset = intval(($source_width - $equal_width) / 2);
            $height_offset = intval(($source_height - $equal_height) / 2);

            $tmp_img = imageCreateTrueColor($dest_width, $dest_height);
            imageAlphaBlending($tmp_img, false);
            imageSaveAlpha($tmp_img, true);
            imageCopyResampled($tmp_img, $source_img, 0, 0, $width_offset, $height_offset, $dest_width, $dest_height, ($equal_width), ($equal_height));
        } elseif ($mode == 'f') {
            $source_width = $w_src;
            $source_height = $h_src;

            $dest_width = $width;
            $dest_height = $height;


            $width_proportion = $source_width / $dest_width;
            $height_proportion = $source_height / $dest_height;

            if ($width_proportion > $height_proportion) {
                $common_proportion = $width_proportion;
            } else {
                $common_proportion = $height_proportion;
            }

            $equal_width = $source_width / $common_proportion;
            $equal_height = $source_height / $common_proportion;

            $width_offset = intval(($dest_width - $equal_width) / 2);
            $height_offset = intval(($dest_height - $equal_height) / 2);

            $tmp_img = imageCreateTrueColor($dest_width, $dest_height);
            imageAlphaBlending($tmp_img, false);

            //$white = imagecolorallocate($f, 255,255,255);
            //imagecolortransparent($f, $white);
            //$trans_colour = imagecolorallocate($tmp_img, 255, 255, 255);
            $trans_colour = imagecolorallocatealpha($tmp_img, 255, 255, 255, 127);
            imagefill($tmp_img, 0, 0, $trans_colour);
            imageCopyResampled($tmp_img, $source_img, $width_offset, $height_offset, 0, 0, $equal_width, $equal_height, $source_width, $source_height);
            imageSaveAlpha($tmp_img, true);
        } else {
            $ratio = 1;
            if ($mode == 'width') {
                if ($w_src > $width) {
                    $ratio = $w_src / $width;
                }
            } else {
                $tmp = $width;
                $width = $height;
                $height = $tmp;
                if ($h_src > $height) {
                    $ratio = $h_src / $height;
                }
            }
            $width_tmp = intval($w_src / $ratio);
            $height_tmp = intval($h_src / $ratio);
            $tmp_img = imageCreateTrueColor($width_tmp, $height_tmp);
            imageAlphaBlending($tmp_img, false);
            imageSaveAlpha($tmp_img, true);
            imageCopyResampled($tmp_img, $source_img, 0, 0, 0, 0, $width_tmp, $height_tmp, $w_src, $h_src);
        }

        if ($final_ext != '') {
            if ($final_ext == 'jpg' || $final_ext == 'jpeg') {
                imagejpeg($tmp_img, $dst, (int)$this->getConfigValue('jpeg_quality'));
            } elseif ($final_ext == 'png') {
                imagepng($tmp_img, $dst, (int)$this->getConfigValue('png_quality'));
            } elseif ($final_ext == 'gif') {
                imagegif($tmp_img, $dst);
            } elseif ($final_ext == 'webp') {
                imagewebp($tmp_img, $dst);
            }
        } else {
            if ($ext == 'jpg' || $ext == 'jpeg') {
                imagejpeg($tmp_img, $dst, (int)$this->getConfigValue('jpeg_quality'));
            } elseif ($ext == 'png') {
                imagepng($tmp_img, $dst, (int)$this->getConfigValue('png_quality'));
            } elseif ($ext == 'gif') {
                imagegif($tmp_img, $dst);
            } elseif ($ext == 'webp') {
                imagewebp($tmp_img, $dst);
            }
        }

        ImageDestroy($source_img);
        ImageDestroy($tmp_img);
        // ImageDestroy($preview_img);
        return array($width, $height);
    }

    /**
     * Make move
     * @param
     * @return
     */
    function makeMove($src, $dst)
    {
        @rename($src, $dst);
    }

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
                $ocount = 0;
                $query = 'SELECT `view_count` FROM ' . DB_PREFIX . '_' . $table_name . ' WHERE ' . $primary_key_name . '=? LIMIT 1';
                $stmt = $DBC->query($query, array($primary_key_value));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    $ocount = intval($ar['view_count']);
                }
                $ocount++;
                $query = 'UPDATE ' . DB_PREFIX . '_' . $table_name . ' SET view_count=? WHERE ' . $primary_key_name . '=?';
                $stmt = $DBC->query($query, array($ocount, $primary_key_value));
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

    public static function iconv($in_charset, $out_charset, $string)
    {
        if (strtolower($in_charset) == strtolower($out_charset)) {
            return $string;
        } else {
            return iconv($in_charset, $out_charset . '//IGNORE', $string);
        }
    }

    public static function removeDirectory($dir, &$msg = array())
    {
        $files = scandir($dir);

        if (count($files) > 2) {
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    if (is_dir($dir . '/' . $file)) {
                        self::removeDirectory($dir . '/' . $file, $msg);
                    } elseif (is_writable($dir . '/' . $file)) {
                        @unlink($dir . '/' . $file);
                    } else {
                        $msg[] = 'Файл/директория ' . $file . ' не удален. Удалите его самостоятельно.';
                    }
                }
            }
        }

        if (is_writable($dir)) {
            rmdir($dir);
        } else {
            $msg[] = 'Файл/директория ' . $dir . ' не удален. Удалите его самостоятельно.';
        }
    }

    function transliteMe($str)
    {
        $str = str_replace(array(',', '.', '/', '\\', '"', '\'', '~', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '_', '+', '|', ';', '?', '<', '>', '`', '[', ']', '{', '}', '№'), '', $str);
        $str = mb_strtolower($str, SITE_ENCODING);
        $tr = array(
            "а" => "a", "б" => "b",
            "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ё" => "e", "ж" => "j",
            "з" => "z", "и" => "i", "й" => "y", "і" => "i", "ї" => "yi", "к" => "k", "л" => "l",
            "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
            "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
            "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y",
            "ы" => "i", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya", "і" => "i",
            "А" => "a", "Б" => "b",
            "В" => "v", "Г" => "g", "Д" => "d", "Е" => "e", "Ё" => "e", "Є" => "ye", "Ж" => "j",
            "З" => "z", "И" => "i", "Й" => "y", "І" => "i", "Ї" => "yi", "К" => "k", "Л" => "l",
            "М" => "m", "Н" => "n", "О" => "o", "П" => "p", "Р" => "r",
            "С" => "s", "Т" => "t", "У" => "u", "Ф" => "f", "Х" => "h",
            "Ц" => "ts", "Ч" => "ch", "Ш" => "sh", "Щ" => "sch", "Ъ" => "y",
            "Ы" => "i", "Ь" => "", "Э" => "e", "Ю" => "yu", "Я" => "ya", "І" => "i",
            " " => "-", 'Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y'
        );

        $str = strtr(mb_strtolower($str, SITE_ENCODING), $tr);
        $str = preg_replace('/([^a-z0-9-_])/', '', $str);
        $str = preg_replace('/(-+)/', '-', $str);
        return $str;
    }

    public static function setLangSession()
    {

        $C = SConfig::getInstance();

        $langs = array();

        $langlist = trim($C::getConfigValueStatic('apps.language.languages'));

        if ($langlist !== '') {
            $lang_pairs = explode('|', $langlist);
            if (count($lang_pairs) > 0) {
                foreach ($lang_pairs as $lp) {
                    $matches = array();
                    if (preg_match('/([a-z]+)=(.+)/', trim($lp), $matches)) {
                        $langs[$matches[1]] = $matches[2];
                    }
                }
            }
        }

        if (isset($_GET['_lang'])) {
            $lang = trim(preg_replace('/[^a-z]/i', '', $_GET['_lang']));
            if ($lang != '' && isset($langs[$lang])) {
                $_SESSION['_lang'] = $lang;
            }
        }
        if (!isset($_SESSION['_lang']) || $_SESSION['_lang'] == '') {

            if ('' == trim($C->getConfigValue('apps.language.default_lang_code'))) {
                $_SESSION['_lang'] = 'ru';
            } else {
                $_SESSION['_lang'] = trim($C->getConfigValue('apps.language.default_lang_code'));
            }
        }
    }

    public static function getClearRequestURI($test_url = '')
    {

        if ($test_url == '') {
            $url = $_SERVER['REQUEST_URI'];
            if (!is_null(@self::$_request['clearRequestUri'])) {
                return self::$_request['clearRequestUri'];
            }
        } else {
            $url = $test_url;
        }
        $url = urldecode($url);
        $url = str_replace('\\', '/', $url);
        $url = preg_replace('/\/(\/+)/', '', $url);

        $query_str_pos = strpos($url, '?');
        if (false !== $query_str_pos) {
            //$fp=substr($url, 0, $query_str_pos);
            $REQUESTURIPATH = substr($url, 0, $query_str_pos);
        } else {
            $REQUESTURIPATH = $url;
        }

        $SConfig = SConfig::getInstance();

        if (1 == intval($SConfig::getConfigValueStatic('apps.language.use_langs'))) {
            if (@self::$_request['request_lang_prefix'] != '') {
                $REQUESTURIPATH = preg_replace('/^(\/' . self::$_request['request_lang_prefix'] . ')/', '', $REQUESTURIPATH);
                $_SERVER['REQUEST_URI'] = $REQUESTURIPATH;
            }
        }

        if (preg_match('/(\/(\/+))/', $REQUESTURIPATH)) {
            return $REQUESTURIPATH;
        }
        //$REQUESTURIPATH = parse_url($url, PHP_URL_PATH);

        /* if ($REQUESTURIPATH == false) {
          $REQUESTURIPATH = urldecode($test_url);
          } */
        if ('/' === $REQUESTURIPATH) {
            return '';
        }

        //$REQUESTURIPATH=str_replace('\\', '/', $REQUESTURIPATH);
        if (substr($REQUESTURIPATH, 0, 1) === '/') {
            $REQUESTURIPATH = substr($REQUESTURIPATH, 1);
        }
        if (substr($REQUESTURIPATH, -1, 1) === '/') {
            $REQUESTURIPATH = substr($REQUESTURIPATH, 0, strlen($REQUESTURIPATH) - 1);
        }
        //var_dump($REQUESTURIPATH);
        //$REQUESTURIPATH=trim(str_replace('\\', '/', parse_url(urldecode($_SERVER['REQUEST_URI']), PHP_URL_PATH)),'/');
        if (SITEBILL_MAIN_URL != '') {
            $REQUESTURIPATH = trim(preg_replace('/^' . trim(SITEBILL_MAIN_URL, '/') . '/', '', $REQUESTURIPATH), '/');
        }

        if ($test_url == '') {
            self::$_request['clearRequestUri'] = $REQUESTURIPATH;
        }


        return $REQUESTURIPATH;
    }

    public function sendFirmMail($to, $from, $subject, $body, $customtpl = '', $to_user_id = 0)
    {
        Logger::emaillog($to, $from, $subject, $body, $to_user_id);
        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/system/mailer/mailer.php');
        $mailer = new Mailer();
        //Если указано несколько почтовых ящиков для получения в $to через запятую, то делаем из него массив
        if (is_string($to)) {
            if (preg_match('/,/', $to)) {
                $to_array = explode(',', $to);
                $to = array();
                foreach ($to_array as $k => $to_email_string) {
                    array_push($to, $to_email_string);
                }
            }
        }
        $this->writeLog(__METHOD__ . ', ' . "to = " . var_export($to, true));


        global $smarty;
        $smarty->assign('letter_content', $body);
        $smarty->assign('estate_core_url', $this->getServerFullUrl());
        $tpl = SITEBILL_DOCUMENT_ROOT . '/apps/system/template/firm_mail_wrapper.tpl';
        if ($customtpl != '' && file_exists($customtpl)) {
            $tpl = $customtpl;
        }
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/firm_mail_wrapper.tpl')) {
            $tpl = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/firm_mail_wrapper.tpl';
        }
        $body = $smarty->fetch($tpl);

        if ($this->getConfigValue('use_smtp')) {
            $mailer->send_smtp($to, $from, $subject, $body, 1);
        } else {
            $mailer->send_simple($to, $from, $subject, $body, 1);
        }
        /* TODO
         * Этот блок предназначен на замену для возможности отправки приложений в письмах
         */
        /*
        if ($this->getConfigValue('use_smtp')) {
            $mailer->send_smtp($to, $from, $subject, $body);
        } else {
            $mailer->send_simple($to, $from, $subject, $body, $attachments);
        }
        */
    }

    function check_access_agency($table_name, $user_id, $control_name, $primary_key_name, $primary_key_value)
    {
        if (!$this->agency_admin) {
            $this->agency_admin = $this->get_api_common()->init_custom_model_object('agency');
        }
        return $this->agency_admin->check_access_agency($table_name, $user_id, $control_name, $primary_key_name, $primary_key_value);
    }

    /**
     * Проверка владельца записи в таблице по USER_ID, если владелец совпадает с $table_name.user_id тогда возвращаем TRUE иначе FALSE
     * @param type $table_name - название таблицы
     * @param type $user_id - идентификатор пользователя для проверки
     * @param type $control_name - тип действия (edit, delete...)
     * @param type $primary_key_name - название PRIMARY KEY в таблице
     * @param type $primary_key_value - значение PRIMARY KEY
     * @return boolean
     */
    function check_access($table_name, $user_id, $control_name, $primary_key_name, $primary_key_value)
    {
        if (!$user_id) {
            return true;
        }
        $has_access = 0;
        if ($this->getConfigValue('apps.agency.enable')) {
            $has_access = intval($this->check_access_agency($table_name, $user_id, $control_name, $primary_key_name, $primary_key_value));
        }

        $DBC = DBC::getInstance();
        $enable_curator_mode = false;
        if (
            1 == $this->getConfigValue('enable_curator_mode')
            or
            1 == $this->getConfigValue('enable_coworker_mode')
        ) {
            $enable_curator_mode = true;


            if (1 === intval($this->getConfigValue('curator_mode_fullaccess'))) {

                $query = 'SELECT COUNT(d.' . $primary_key_name . ') AS _cnt FROM ' . DB_PREFIX . '_' . $table_name . ' d 
                LEFT JOIN ' . DB_PREFIX . '_user u USING(user_id) WHERE d.' . $primary_key_name . '=? AND u.parent_user_id=?';
                $stmt = $DBC->query($query, array($primary_key_value, $user_id));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ($ar['_cnt'] > 0) {
                        $has_access = 1;
                    }
                }
            } elseif ($table_name == 'data' && $this->getConfigValue('apps.data.enable_city_coworker')) {
                $has_access = $this->check_coworker_access_by_foreign_key(
                    $table_name,
                    $user_id,
                    $control_name,
                    $primary_key_name,
                    $primary_key_value,
                    'city');
            } else {
                $query = 'SELECT COUNT(id) AS _cnt FROM ' . DB_PREFIX . '_cowork WHERE coworker_id=? AND object_type=? AND id=?';
                $stmt = $DBC->query($query, array($user_id, $table_name, $primary_key_value));
                if ($stmt) {
                    $ar = $DBC->fetch($stmt);
                    if ($ar['_cnt'] > 0) {
                        $has_access = 1;
                    }
                }
            }
        }


        $where = array();
        $where_val = array();

        $where[] = '`' . $primary_key_name . '`=?';
        $where_val[] = $primary_key_value;


        if ($enable_curator_mode) {
            $where[] = '(`user_id`=? OR (`user_id`!=? AND 1=' . $has_access . '))';
            $where_val[] = $user_id;
            $where_val[] = $user_id;
        } else {
            $where[] = '`user_id`=?';
            $where_val[] = $user_id;
        }


        $query = 'SELECT `' . $primary_key_name . '` FROM `' . DB_PREFIX . '_' . $table_name . '` WHERE ' . implode(' AND ', $where);
        $stmt = $DBC->query($query, $where_val);
        if (!$stmt) {
            return false;
        }
        $ar = $DBC->fetch($stmt);
        if ($ar[$primary_key_name] > 0) {
            return true;
        }
        return false;
    }

    function check_coworker_access_by_foreign_key($table_name, $user_id, $control_name, $primary_key_name, $primary_key_value, $foreign_table)
    {
        if (!$this->cowork_object) {
            $api_common = $this->get_api_common();
            $this->cowork_object = $api_common->init_custom_model_object('cowork');
        }
        if ($this->cowork_object) {
            try {
                // Пока хардкодом прописываем выборку для city_id
                $data_record = \system\lib\model\eloquent\Data::where($primary_key_name, '=', $primary_key_value)
                    ->first();
                if ($data_record->city_id) {
                    return $this->cowork_object->check_cowork_record($foreign_table, $data_record->city_id, $user_id);
                }
            } catch (Exception $e) {
                $this->writeLog($e->getMessage());
            }
        }
        return 0;
    }

    function need_check_access($table_name)
    {
        return @$_SESSION['politics'][$table_name]['check_access'];
    }

    function get_check_access_user_id($table_name)
    {
        return @$_SESSION['politics'][$table_name]['user_id'];
    }

    /**
     * Перенаправляем неавторизованного пользователя на форму авторизации
     */
    function go_to_login()
    {
        header('location: ' . SITEBILL_MAIN_URL . '/login/');
        exit();
    }

    /**
     * Ищем в таблице emailtemplates шаблон с именем $name
     * Если находим, то делаем smarty fetch для subject и message
     * Предварительно все переменные должны быть assign-нуты в smarty
     * @param type $name - системное название шаблона
     * @return mixed (массив с готовый с subject и message, если шаблон найдет. false - если шаблон не найден)
     */
    function fetch_email_template($name)
    {
        global $smarty;
        $ra = array();
        if ($this->getConfigValue('apps.emailtemplates.enable')) {
            require_once(SITEBILL_DOCUMENT_ROOT . '/apps/emailtemplates/admin/admin.php');
            $emailtemplates_admin = new emailtemplates_admin();
            return $emailtemplates_admin->compile_template($name);
        }
        return false;
    }

    function clear_apps_cache()
    {
        //Очищаем кэш apps
        $DBC = DBC::getInstance();
        $query = "TRUNCATE TABLE " . DB_PREFIX . "_apps";
        $stmt = $DBC->query($query, array(), $rows, $success);
    }

    public function yandex_translate($value, $language)
    {
        if ($language == 'ge') {
            $language = 'ka';
        }
        $api_key = $this->getConfigValue('apps.language.yandex_translate_api_key');
        if ($api_key == '') {
            return '';
        }
        if ($value == '') {
            return '';
        }

        $url = 'https://translate.yandex.net/api/v1.5/tr.json/translate?key=' . $api_key . '&format=html&lang=' . $language . '&text=' . urlencode($value);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $output = curl_exec($ch);
        curl_close($ch);

        if (false === $result) {
            return '';
        }
        $res = json_decode($result);
        if ($res->code == '200') {
            return $res->text[0];
        } elseif ($res->code == '403') {
            $err = 'Превышено суточное ограничение на количество запросов';
        } elseif ($res->code == '404') {
            //resetCurrentYandexKey();
            $err = 'Превышено суточное ограничение на объем переведенного текста';
        } elseif ($res->code == '413') {
            $err = 'Превышен максимально допустимый размер текста';
        } elseif ($res->code == '422') {
            $err = 'Текст не может быть переведен';
        } elseif ($res->code == '402') {
            //resetCurrentYandexKey();
            $err = 'Ключ API заблокирован';
        } else {
            $err = 'Другая ошибка';
        }
        $this->writeLog(__METHOD__ . ', value = ' . $value . ', target_language = ' . $language . ', error = ' . $err);
        return '';
    }

    public function google_translate_array($api_key, $array_values, $language)
    {
        //$url = 'https://translation.googleapis.com/language/translate/v2?q=Привет&q=Мир';
        $url = 'https://translation.googleapis.com/language/translate/v2';

        $params = array(
            'key' => $api_key,
            'format' => 'html',
            'target' => $language,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params) . "&q=" . implode('&q=', $array_values));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public function google_translate_string($api_key, $value, $language)
    {
        $url = 'https://translation.googleapis.com/language/translate/v2';

        $params = array(
            'key' => $api_key,
            'format' => 'html',
            'target' => $language,
            'q' => $value
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }


    public function google_translate($value, $language)
    {
        if ($language == 'ge') {
            $language = 'ka';
        }
        if ($language == 'tj') {
            $language = 'tg';
        }
        if ($language == 'ua') {
            $language = 'ukr';
        }

        $api_key = $this->getConfigValue('apps.language.google_translate_api_key');
        if ($api_key == '') {
            return $value;
        }
        if ($value == '') {
            return '';
        }
        if (is_array($value)) {
            $output = $this->google_translate_array($api_key, $value, $language);
            $langdata = json_decode($output, true);
        } else {
            $output = $this->google_translate_string($api_key, $value, $language);
            $langdata = json_decode($output, true);

        }
        if (isset($output['error'])) {
            $this->riseError('Google translation error: ' . $output['error']['message']);
        }
        $this->writeLog(__METHOD__ . ', value = ' . $value . ', target_language = ' . $language . ', langdata = ' . var_export($langdata, true));

        if (is_string($value) and $langdata['data']['translations'][0]['translatedText'] != '') {
            return $langdata['data']['translations'][0]['translatedText'];
        } elseif (is_array($value)) {
            return $this->parse_pure_array_from_google_tranlations($langdata['data']['translations']);
        }
        return '';
    }

    function api_translate($value, $language)
    {
        if (1 == intval($this->getRequestValue('apps.language.autotrans_api'))) {
            return $this->yandex_translate($value, $language);
        } else {
            return $this->google_translate($value, $language);
        }
    }

    private function parse_pure_array_from_google_tranlations($translations)
    {
        foreach ($translations as $key => $value) {
            $ra[] = $value['translatedText'];
        }
        return $ra;
    }

    public function mtphn($s)
    {
        if (!function_exists('transliterator_transliterate') or !function_exists('metaphone')) {
            echo 'Для работы функции метафона нужно установить (PHP 5 >= 5.4.0, PHP 7, PECL intl >= 2.0.0';
            exit;
        }
        $key = transliterator_transliterate("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", $s);
        $key = preg_replace('/[-\s]+/', '-', $key);
        $key = str_replace('ʼ', '', $key);
        //echo $key.'<br>';
        return metaphone($key);
    }

    public static function get_microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
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


    function generateSocials($params)
    {

        $social = '';

        // twitter card
        $social .= '<meta name="twitter:card" content="' . $params['tw:cardtype'] . '">';
        $social .= '<meta name="twitter:title" content="' . htmlspecialchars(strip_tags($params['title'])) . '">';
        $social .= '<meta name="twitter:description" content="' . htmlspecialchars(strip_tags($params['description'])) . '">';
        if ($params['image'] != '') {
            $social .= '<meta name="twitter:image" content="' . $this->getServerFullUrl(true) . '/img/data/' . $params['image'] . '">';
        }

        // open graph
        $social .= '<meta property="og:title" content="' . htmlspecialchars(strip_tags($params['title'])) . '" />';
        $social .= '<meta property="og:type" content="' . $params['og:type'] . '" />';
        $social .= '<meta property="og:url" content="' . $params['url'] . '" />';
        if ($params['image'] != '') {
            $social .= '<meta property="og:image" content="' . $this->getServerFullUrl(true) . '/img/data/' . $params['image'] . '" />';
        }
        $social .= '<meta property="og:description" content="' . htmlspecialchars(strip_tags($params['description'])) . '" />';

        // schema
        $social .= '<meta itemprop="name" content="' . htmlspecialchars(strip_tags($params['title'])) . '">';
        $social .= '<meta itemprop="description" content="' . htmlspecialchars(strip_tags($params['description'])) . '">';
        if ($params['image'] != '') {
            $social .= '<meta itemprop="image" content="' . $this->getServerFullUrl(true) . '/img/data/' . $params['image'] . '">';
        }

        return $social;

    }

    function get_cache_hash($query, $params)
    {
        return md5($query . implode('', $params));
    }

    function get_query_cache_value($query, $params)
    {
        $result['result'] = false;
        if (!$this->getConfigValue('query_cache_enable')) {
            return $result;
        }
        $this->delete_query_cache();

        $DBC = DBC::getInstance();
        $md5_query_sum = $this->get_cache_hash($query, $params);

        $cache_query = "select `value` from " . DB_PREFIX . "_cache where parameter = ? and valid_for > ?";
        $stmt = $DBC->query($cache_query, array($md5_query_sum, time()));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            $result['result'] = true;
            $result['value'] = $ar['value'];
        }
        return $result;
    }

    function insert_query_cache_value($query, $params, $value)
    {
        $DBC = DBC::getInstance();
        if ($this->getConfigValue('query_cache_enable')) {
            $md5_query_sum = $this->get_cache_hash($query, $params);
            $query_insert_cache = "insert into " . DB_PREFIX . "_cache (`parameter`, `value`, `created_at`, `valid_for`) values (?, ?, ?, ?)";
            $stmt = $DBC->query($query_insert_cache, array($md5_query_sum, $value, time(), time() + $this->getConfigValue('query_cache_time')));
        }

    }

    function delete_query_cache()
    {
        $DBC = DBC::getInstance();
        if ($this->getConfigValue('query_cache_enable')) {
            //Очищаем старые записи кэша
            $query_delete_cache = "delete from " . DB_PREFIX . "_cache where `created_at`<?";
            $stmt = $DBC->query($query_delete_cache, array(time() - $this->getConfigValue('query_cache_time')));
        }
    }

    function get_tooltip_script()
    {
        $rs = "
 <script>
        $(document).ready(function () {
            $('.tooltipe_block').popover({
                trigger: 'hover',
                placement: 'top',
            });
        });
    </script>        
        ";
        return $rs;
    }

    function reducer_text($text, $max_length = 500)
    {
        if (strlen($text) > $max_length) {
            $text = '<div 
                style="display: block; width: 100%; overflow: hidden;"
                rel="popover" class="tooltipe_block" data-content="' . strip_tags($text) . '"
                >' . substr(strip_tags($text), 0, $max_length) . '</div>';
        }
        return $text;
    }

    public static function old_template_files_array()
    {
        return array('realty_grid.tpl', 'error_message.tpl', 'map.tpl', 'realty_view.tpl');
    }

    function enable_vue()
    {
        $this->template->assert('enable_vue', true);
    }

    function disable_vue()
    {
        $this->template->assert('enable_vue', false);
    }

    function checkReCaptcha($token)
    {

        $secret = trim($this->getConfigValue('google_recaptcha_secret'));

        if ($secret != '') {
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, "secret='.$secret.'&response=" . $token);
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

    function getSessionLanguage () {
        return $_SESSION['_lang'];
    }

    function get_cookie_duration_in_sec () {
        return 60*60*24*100;
    }
}

//Helpers
function store($key)
{
    return Sitebill::get_template_store($key);
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
