<?php

defined('SITEBILL_DOCUMENT_ROOT') or die('Restricted access');
/**
 * Apps processort. Scan apps folder and run each apps if needed
 * @author Kondin Dmitriy <kondin@etown.ru> http://www.sitebill.ru
 */
define('APPS_FILE_NOT_EXIST', 1);

class Apps_Processor extends SiteBill
{

    private $plugins_dir;
    private $apps_executed = array();
    private $frontend_app = '';
    private $instance;

    /**
     * Constructor
     */
    function __construct()
    {
        $this->apps_dir = SITEBILL_APPS_DIR;
        parent::__construct();
    }

    /**
     * Installing app from install.xml
     * @param string $app App code
     * @return array Installing info
     */
    function installApp($app)
    {

        $result = array();

        $appdir = $this->apps_dir . '/' . $app;
        $installfile = $this->apps_dir . '/' . $app . '/install.xml';
        if (file_exists($installfile)) {
            $xml = @simplexml_load_file($installfile);

            $configs = array();

            if (isset($xml->config) && count($xml->config->configitem) > 0) {
                foreach ($xml->config->configitem as $configitem) {
                    $configs[trim($configitem['key'])] = array(
                        'title' => trim($configitem->title),
                        'type' => trim($configitem->type),
                        'default' => trim($configitem->default)
                    );
                }
            }

            print_r($configs);

        }
        return $result;
    }

    function load_apps_list_from_location()
    {
        $apps = array();
        if (is_dir($this->apps_dir)) {
            if ($dh = opendir($this->apps_dir)) {
                while (($app_dir = readdir($dh)) !== false) {
                    if (is_dir($this->apps_dir . '/' . $app_dir) and !preg_match('/\./', $app_dir)) {
                        if (is_file($this->apps_dir . '/' . $app_dir . '/' . $app_dir . '.xml')) {

                            $xml = @simplexml_load_file($this->apps_dir . '/' . $app_dir . '/' . $app_dir . '.xml');
                            $action = trim($xml->name);
                            $apps[$action]['title'] = trim($xml->administration->menu);
                            $apps[$action]['version'] = trim($xml->version);
                            $apps[$action]['creationDate'] = trim($xml->creationDate);
                            $apps[$action]['updateDate'] = trim($xml->updateDate);
                            //$apps[$action]['href'] = 'index.php?action='.$action;
                            //$apps[$action]['backend_menu'] = $add_to_menu;
                            if (isset($xml->description)) {
                                $apps[$action]['description'] = trim($xml->description);
                            } else {
                                $apps[$action]['description'] = '';
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }

        if (!empty($apps)) {

            $global_localization_location = SITEBILL_DOCUMENT_ROOT . '/apps_local/';
            if (is_dir($global_localization_location)) {
                if ($dh = opendir($global_localization_location)) {
                    foreach ($apps as $app_code => $v) {
                        if (is_dir($global_localization_location . $app_code)) {
                            if (is_dir($global_localization_location . $app_code . '/admin/') && file_exists($global_localization_location . $app_code . '/admin/local_admin.php')) {
                                $apps[$app_code]['alocalized'] = 1;
                            }
                            if (is_dir($global_localization_location . $app_code . '/site/') && file_exists($global_localization_location . $app_code . '/site/local_site.php')) {
                                $apps[$app_code]['slocalized'] = 1;
                            }
                        }
                    }
                }
            }

            $tpl_location = SITEBILL_DOCUMENT_ROOT . '/template/frontend/';
            if (is_dir($tpl_location)) {
                if ($dh = opendir($tpl_location)) {
                    while (($one_tpl_dir = readdir($dh)) !== false) {
                        //var_dump($one_tpl_dir);
                        $tpl_apps_dir = $tpl_location . $one_tpl_dir . '/apps/';
                        foreach ($apps as $app_code => $v) {
                            if (is_dir($tpl_apps_dir . $app_code)) {
                                if (is_dir($tpl_apps_dir . $app_code . '/admin/') && file_exists($tpl_apps_dir . $app_code . '/admin/local_admin.php')) {
                                    $apps[$app_code]['localized']['admin'][] = $one_tpl_dir;
                                }
                                if (is_dir($tpl_apps_dir . $app_code . '/site/') && file_exists($tpl_apps_dir . $app_code . '/site/local_site.php')) {
                                    $apps[$app_code]['localized']['site'][] = $one_tpl_dir;
                                }
                            }
                        }
                    }
                }
            }
            /* foreach($apps as $app_code=>$v){

              } */
        }
        print_r($apps);
        exit();
    }

    /*
     * Possible variant
     */

    function getAppsList()
    {

        $currentTplFolder = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme');
        $customisationFolder = SITEBILL_DOCUMENT_ROOT . '/customisation';

        $apps = array();

        if (is_dir($this->apps_dir)) {
            if ($dh = opendir($this->apps_dir)) {
                while (($app_dir = readdir($dh)) !== false) {
                    if (is_dir($this->apps_dir . '/' . $app_dir) and !preg_match('/\./', $app_dir)) {
                        if (is_file($this->apps_dir . '/' . $app_dir . '/' . $app_dir . '.xml')) {

                            $xml = @simplexml_load_file($this->apps_dir . '/' . $app_dir . '/' . $app_dir . '.xml');
                            $action = trim($xml->name);
                            $apps[$action]['title'] = trim($xml->administration->menu);
                            $apps[$action]['version'] = trim($xml->version);
                            $apps[$action]['creationDate'] = trim($xml->creationDate);
                            $apps[$action]['updateDate'] = trim($xml->updateDate);
                            //$apps[$action]['href'] = 'index.php?action='.$action;
                            //$apps[$action]['backend_menu'] = $add_to_menu;
                            if (isset($xml->description)) {
                                $apps[$action]['description'] = trim($xml->description);
                            }

                            $apps[$action]['tpl_loc_admin'] = 0;
                            if (file_exists($currentTplFolder . '/apps/' . $app_dir . '/admin/local_admin.php')) {
                                $apps[$action]['tpl_loc_admin'] = 1;
                            }

                            $apps[$action]['tpl_loc_site'] = 0;
                            if (file_exists($currentTplFolder . '/apps/' . $app_dir . '/site/local_site.php')) {
                                $apps[$action]['tpl_loc_site'] = 1;
                            }

                            $apps[$action]['customisation_loc_admin'] = 0;
                            if (file_exists($customisationFolder . '/apps/' . $app_dir . '/admin/local_admin.php')) {
                                $apps[$action]['customisation_loc_admin'] = 1;
                            }

                            $apps[$action]['customisation_loc_site'] = 0;
                            //var_dump($customisationFolder . '/apps/' . $app_dir . '/site/local_site.php');
                            if (file_exists($customisationFolder . '/apps/' . $app_dir . '/site/local_site.php')) {
                                $apps[$action]['customisation_loc_site'] = 1;
                            }


                        }
                    }
                }
                closedir($dh);
            }
        }
        $s = '<table class="table">';
        foreach ($apps as $k => $v) {
            $s .= '<tr>';
            $s .= '<td>';
            $s .= '<b>' . $v['title'] . '</b> (' . $k . ')<br>' . $v['description'];
            $s .= '</td>';
            $s .= '<td>' . $v['version'] . '</td>';
            $s .= '<td>Статус</td>';
            $s .= '<td>' . $v['tpl_loc_admin'] . '</td>';
            $s .= '<td>' . $v['tpl_loc_site'] . '</td>';
            $s .= '<td>' . $v['customisation_loc_admin'] . '</td>';
            $s .= '<td>' . $v['customisation_loc_site'] . '</td>';
            $s .= '</tr>';
        }
        $s .= '</table>';
        return $s;
    }

    /**
     * Run apps by action and interface name
     * @param string $app_name
     * @param string $interface
     * @return string
     */
    function run($app_name, $interface)
    {
        $has_admin_local = false;
        $includes = array();

        $app_mod_name = '';

        if (false !== strpos($app_name, ':')) {
            $parts = explode(':', $app_name);
            //print_r($parts);
            $app_name = $parts[0];
            $app_mod_name = $parts[1];
        }
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps' . '/' . $app_name . '/' . $interface . '/local_' . $interface . '.php')) {
            $has_admin_local = true;
            $includes[] = $this->apps_dir . '/' . $app_name . '/' . $interface . '/' . $interface . '.php';
            $includes[] = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps' . '/' . $app_name . '/' . $interface . '/local_' . $interface . '.php';
            /*$app_filename = SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps'.'/'.$app_name.'/'.$interface.'/'.$interface.'.php';*/
        } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps' . '/' . $app_name . '/' . $interface . '/' . $interface . '.php')) {
            $includes[] = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps' . '/' . $app_name . '/' . $interface . '/' . $interface . '.php';
            //$app_filename = SITEBILL_DOCUMENT_ROOT.'/template/frontend/'.$this->getConfigValue('theme').'/apps'.'/'.$app_name.'/'.$interface.'/'.$interface.'.php';
        } elseif (file_exists($this->apps_dir . '/' . $app_name . '/' . $interface . '/' . $interface . '.php')) {
            $includes[] = $this->apps_dir . '/' . $app_name . '/' . $interface . '/' . $interface . '.php';
            //$app_filename = $this->apps_dir.'/'.$app_name.'/'.$interface.'/'.$interface.'.php';
        }

        if (!empty($includes)) {
            foreach ($includes as $inc) {
                require_once($inc);
            }
            if ($has_admin_local) {
                $app_class_name = 'local_' . $app_name . '_' . $interface;
            } else {
                $app_class_name = $app_name . '_' . $interface;
            }
            $app_class_inst = new $app_class_name($app_mod_name);
            $this->set_running_instance($app_class_inst);
            $rs = $app_class_inst->main();
            return $rs;
        } else {
            throw new Exception('Apps file ' . $app_name . ' not exist', APPS_FILE_NOT_EXIST);
        }
    }

    private function set_running_instance($app_instance)
    {
        $this->instance = $app_instance;
    }

    public function get_running_instance()
    {
        return $this->instance;
    }

    /**
     * Set executed apps
     * @param string $apps_name
     */
    private function set_executed_apps($apps_name)
    {
        $this->apps_executed[] = $apps_name;
    }

    /**
     * Get executed apps
     * @return Array
     */
    function get_executed_apps()
    {
        return $this->apps_executed;
    }

    function get_runned_app()
    {
        $app = end($this->get_executed_apps());
        $app = preg_replace('/^(local_)/', '', $app);
        $app = preg_replace('/(_site)$/', '', $app);
        return $app;
    }

    /**
     * Run _preload method for applications
     * @param void
     * @return boolean
     */
    function run_preload()
    {
        $time_start = microtime(true);

        $Sitebill_Registry = Sitebill_Registry::getInstance();
        $is_preload = $Sitebill_Registry->getFeedback('preload_done');
        if ($is_preload) {
            return;
        }
        $Sitebill_Registry->addFeedback('preload_done', true);

        $apps_list = $this->load_apps_from_db(array('preload' => 1));
        if ($apps_list) {
            foreach ($apps_list as $name => $apps_array) {
                $app_class_name = false;
                if ($apps_array['local_admin_path'] != '') {
                    require_once($apps_array['local_admin_path']);
                    $app_class_name = 'local_' . $name . '_admin';
                    if (!class_exists($app_class_name)) {
                        $app_class_name = $name . '_admin';
                        if (!class_exists($app_class_name)) {
                            $app_class_name = false;
                        }
                    }
                } elseif ($apps_array['admin_path'] != '') {
                    if (!file_exists($apps_array['admin_path'])) {
                        $this->clear_apps_cache();
                        echo _e('Устаревший кэш приложений, перезапустите страницу');
                        exit;
                    }
                    require_once($apps_array['admin_path']);
                    $app_class_name = $name . '_admin';
                }
                /*
                  if ( $apps_array['local_site_path'] != ''  ) {
                  require_once ($apps_array['site_path']);
                  require_once ($apps_array['local_site_path']);
                  $app_class_name = 'local_'.$name.'_site';
                  } elseif ( $apps_array['site_path'] != '' ) {
                  require_once ($apps_array['site_path']);
                  $app_class_name = $name.'_site';
                  }
                 */
                if ($app_class_name) {
                    //echo $app_class_name.'<br>';
                    $app_class_inst = new $app_class_name;
                    $app_class_inst->_preload();
                }
            }
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            //$this->writeLog(array('apps_name' => 'apps_processor', 'method' => __METHOD__, 'message' => 'Время выполнения = '.$time, 'type' => ''));

            return;
            //return $apps_array;
        }


        if (is_dir($this->apps_dir)) {
            if ($dh = opendir($this->apps_dir)) {
                while (($app_dir = readdir($dh)) !== false) {
                    //echo '$app_dir = '.$app_dir.'<br>';
                    if (is_dir($this->apps_dir . '/' . $app_dir) and !preg_match('/\./', $app_dir)) {
                        if (is_file($this->apps_dir . '/' . $app_dir . '/site/site.php')) {
                            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/admin/admin.php')) {
                                require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/admin/admin.php');
                            } else {
                                require_once($this->apps_dir . '/' . $app_dir . '/admin/admin.php');
                            }

                            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/site.php')) {
                                require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/site.php');
                            } else {
                                require_once($this->apps_dir . '/' . $app_dir . '/site/site.php');
                            }
                            $app_class_name = $app_dir . '_site';
                            //echo $app_class_name.'<br>';
                            $app_class_inst = new $app_class_name;
                            if (method_exists($app_class_inst, '_preload')) {
                                $app_class_inst->_preload();
                            }
                            //$app_class_inst->_preload();
                        }/* elseif( is_file($this->apps_dir.'/'.$app_dir.'/admin/admin.php') ){
                          require_once ($this->apps_dir.'/'.$app_dir.'/admin/admin.php');
                          $app_class_name = $app_dir.'_admin';
                          $app_class_inst = new $app_class_name;
                          $app_class_inst->_preload();
                          } */
                    }
                }
                closedir($dh);
            }
        }
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        //$this->writeLog(array('apps_name' => 'apps_processor', 'method' => __METHOD__, 'message' => 'Время выполнения = '.$time, 'type' => ''));
    }

    function run_symfony_apps()
    {
        global $smarty;
        if (file_exists(SITEBILL_DOCUMENT_ROOT . '/apps/symfony/kernel.php') and defined('SYMFONY_ENABLED')) {
            $symfony_response = include_once(SITEBILL_DOCUMENT_ROOT . '/apps/symfony/kernel.php');
            if ($symfony_response) {
                $app_class_name = 'syma';
                $this->set_executed_apps($app_class_name);
                return true;
            }
        }
        return false;
    }

    /**
     * Run frontend apps
     * @param void
     * @return boolean
     */
    function run_frontend()
    {
        $time_start = microtime(true);
        if ($this->run_symfony_apps()) {
            return true;
        }

        $apps_list = $this->load_apps_from_db(array('frontend' => 1));
        if ($apps_list) {
            //print_r($apps_list);
            foreach ($apps_list as $name => $apps_array) {
                $app_class_name = false;
                if ($apps_array['local_admin_path'] != '') {
                    require_once($apps_array['local_admin_path']);
                } elseif ($apps_array['admin_path'] != '') {
                    require_once($apps_array['admin_path']);
                }

                if ($apps_array['local_site_path'] != '') {
                    require_once($apps_array['site_path']);
                    require_once($apps_array['local_site_path']);
                    $app_class_name = 'local_' . $name . '_site';
                    if (!class_exists($app_class_name)) {
                        $app_class_name = $name . '_site';
                        if (!class_exists($app_class_name)) {
                            $app_class_name = false;
                        }
                    }
                } elseif ($apps_array['site_path'] != '') {
                    require_once($apps_array['site_path']);
                    $app_class_name = $name . '_site';
                }
                if ($app_class_name) {
                    //echo $app_class_name.'<br>';
                    $app_class_inst = new $app_class_name;
                    if ($app_class_inst->frontend()) {
                        $this->set_executed_apps($app_class_name);
                    }
                }
            }
            $time_end = microtime(true);
            $time = $time_end - $time_start;
            //$this->writeLog(array('apps_name' => 'apps_processor', 'method' => __METHOD__, 'message' => 'Время выполнения = '.$time, 'type' => ''));

            return;
            //return $apps_array;
        }


        if (is_dir($this->apps_dir)) {
            if ($dh = opendir($this->apps_dir)) {
                while (($app_dir = readdir($dh)) !== false) {
                    //echo '$app_dir = '.$app_dir.'<br>';
                    if (is_dir($this->apps_dir . '/' . $app_dir) and !preg_match('/\./', $app_dir)) {
                        if (is_file($this->apps_dir . '/' . $app_dir . '/site/site.php')) {

                            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/admin/admin.php')) {
                                require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/admin/admin.php');
                            } else {
                                require_once($this->apps_dir . '/' . $app_dir . '/admin/admin.php');
                            }

                            if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/local_site.php')) {
                                require_once($this->apps_dir . '/' . $app_dir . '/site/site.php');
                                require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/local_site.php');
                                $app_class_name = 'local_' . $app_dir . '_site';
                            } elseif (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/site.php')) {
                                require_once(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $app_dir . '/site/site.php');
                                $app_class_name = $app_dir . '_site';
                            } else {
                                require_once($this->apps_dir . '/' . $app_dir . '/site/site.php');
                                $app_class_name = $app_dir . '_site';
                            }

                            //echo $app_class_name.'<br>';
                            $app_class_inst = new $app_class_name;
                            if ($app_class_inst->frontend()) {
                                $this->set_executed_apps($app_class_name);
                                //closedir($dh);
                                //return true;
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        //$this->writeLog(array('apps_name' => 'apps_processor', 'method' => __METHOD__, 'message' => 'Время выполнения = '.$time, 'type' => ''));
    }

    /**
     * Check apps compatibility
     * @param void
     * @return boolean
     */
    function check_installed_apps()
    {
        $time_start = microtime(true);

        if (is_dir($this->apps_dir)) {
            if ($dh = opendir($this->apps_dir)) {

                while (($app_dir = readdir($dh)) !== false) {
                    if (is_dir($this->apps_dir . '/' . $app_dir) and !preg_match('/\.{1,}/', $app_dir)) {
                        if (is_file($this->apps_dir . '/' . $app_dir . '/' . $app_dir . '.xml')) {
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
            }
        }
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        //$this->writeLog(array('apps_name' => 'apps_processor', 'method' => __METHOD__, 'message' => 'Время выполнения = '.$time, 'type' => ''));
    }

    function check_apps_compatibility()
    {
        if ($this->check_installed_apps()) {
            if (!class_exists('DomDocument')) {
                return false;
            }
        }
        return true;
    }

    function load_apps_array()
    {
        $time_start = microtime(true);

        $apps_array = array();

        if (is_dir($this->apps_dir)) {
            if ($dh = opendir($this->apps_dir)) {
                while (($app_dir = readdir($dh)) !== false) {
                    if (is_dir($this->apps_dir . '/' . $app_dir) and !preg_match('/\./', $app_dir)) {
                        if (is_file($this->apps_dir . '/' . $app_dir . '/' . $app_dir . '.xml')) {

                            //Parsing by simple_xml_dom
                            $xml = @simplexml_load_file($this->apps_dir . '/' . $app_dir . '/' . $app_dir . '.xml');
                            if ($xml && $xml instanceof SimpleXMLElement) {
                                $title = strval($xml->administration->menu);
                                $action = strval($xml->name);
                                $apps_array[$action]['xml'] = true;
                                if (is_file($this->apps_dir . '/' . $app_dir . '/update.php')) {
                                    $apps_array[$action]['update'] = true;
                                }
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        //$this->writeLog(array('apps_name' => 'apps_processor', 'method' => __METHOD__, 'message' => 'Время выполнения = '.$time, 'type' => ''));
        return $apps_array;
    }

    /**
     * Load installed apps and generate menu items from XML-files
     * @param boolean $load_not_active load not active menu items
     * @return array
     */
    function load_apps_menu($load_not_active = false, $admin_path = 'admin', $ignore_backend_menu = true)
    {

        if ($ignore_backend_menu) {
            $filter_params = array('backend_menu' => 1);
        } else {
            $filter_params = array();
        }
        $apps_array = $this->load_apps_from_db($filter_params);
        if ($apps_array) {
            return $apps_array;
        }
        $time_start = microtime(true);

        //return;
        //echo 'load_apps_menu';

        if (!$this->check_installed_apps()) {
            return false;
        }

        $menu = array();
        $menu_all = array();

        if (is_dir($this->apps_dir)) {
            if ($dh = opendir($this->apps_dir)) {
                while (($app_dir = readdir($dh)) !== false) {
                    if (is_dir($this->apps_dir . '/' . $app_dir) and !preg_match('/\./', $app_dir)) {
                        if (is_file($this->apps_dir . '/' . $app_dir . '/' . $app_dir . '.xml')) {

                            //Parsing by simple_xml_dom
                            $xml = @simplexml_load_file($this->apps_dir . '/' . $app_dir . '/' . $app_dir . '.xml');
                            if ($xml && $xml instanceof SimpleXMLElement) {
                                $title = strval($xml->administration->menu);
                                $action = (string)$xml->name;
                                $backendMenu = 'false';
                                if ($xml->backendMenu) {
                                    $backendMenu = (string)$xml->backendMenu;
                                }

                                $add_to_menu = false;
                                if ($backendMenu != 'false') {
                                    $add_to_menu = true;
                                }
                                if ($load_not_active) {
                                    $add_to_menu = true;
                                }
                                if ($add_to_menu) {
                                    $menu[$action]['title'] = $title;
                                    $menu[$action]['href'] = SITEBILL_MAIN_URL . '/' . $admin_path . '?action=' . $action;
                                    if (isset($_REQUEST[$action]) and ($_REQUEST[$action] == $action)) {
                                        $menu[$action]['active'] = 1;
                                    }
                                }
                                $menu_all[$action]['title'] = $title;
                                $menu_all[$action]['href'] = SITEBILL_MAIN_URL . '/' . $admin_path . '?action=' . $action;
                                $menu_all[$action]['backend_menu'] = $add_to_menu;
                                if (file_exists($this->apps_dir . '/' . $app_dir . '/handler/HandlerController.php')) {
                                    $menu_all[$action]['params']['handlers'] = ['bitrix24\handler\HandlerController'];
                                }

                                if (isset($xml->administration->category) && '' != strval($xml->administration->category)) {
                                    $menu_all[$action]['params']['category'] = strval($xml->administration->category);
                                }
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }
        uasort($menu, array($this, 'appsSort'));
        uasort($menu_all, array($this, 'appsSort'));
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        //$this->writeLog(array('apps_name' => 'apps_processor', 'method' => __METHOD__, 'message' => 'Время выполнения = '.$time, 'type' => ''));
        if (!$this->getConfigValue('apps_cache_disable')) {
            $this->update_apps_cache($menu_all);
        }
        return $menu;
    }

    function appsSort($a, $b)
    {
        if ($a['title'] > $b['title']) {
            return 1;
        } elseif ($a['title'] < $b['title']) {
            return -1;
        } else {
            return 0;
        }
    }

    /**
     * Возвращает путь к папке глобальных модификаций
     * @return string
     */
    function getCustomisationFolder()
    {
        return SITEBILL_DOCUMENT_ROOT . '/customisation';
    }

    /**
     * Обновляем записи в таблице apps
     * @param type $apps_array
     */
    function update_apps_cache($apps_array)
    {

        $common_local_folder = $this->getCustomisationFolder();

        $DBC = DBC::getInstance();

        //Сначала создадим таблицу apps для хранения информации о приложениях
        $query_create_table = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "_apps` (
  `apps_id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `backend_menu` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `href_admin` varchar(255) NOT NULL,
  `admin_path` text,
  `local_admin_path` text,
  `site_path` text,
  `local_site_path` text,
  `preload` tinyint(4) NOT NULL DEFAULT '0',
  `frontend` tinyint(4) NOT NULL DEFAULT '0',
  `params` text,
  PRIMARY KEY (`apps_id`)
  
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
        $stmt = $DBC->query($query_create_table, array());

        require_once(SITEBILL_DOCUMENT_ROOT . '/apps/system/lib/admin/object_manager.php');

        foreach ($apps_array as $apps_name => $apps_items) {
            if (!$this->get_apps_by_name($apps_name) and $apps_name != 'shoplog') {
                $app_class_name_admin = '';
                $app_class_name = '';
                $site_path = '';
                $admin_path = '';
                $local_site_path = '';
                $local_admin_path = '';
                $preload = 0;
                $frontend = 0;

                $has_custom_site = false;

                /**
                 * TODO
                 * has_customization - есть локализация в папке customization
                 * has_tpl_localization - есть локализация в шаблоне
                 */

                if (file_exists($this->apps_dir . '/' . $apps_name . '/admin/admin.php')) {
                    $admin_path = $this->apps_dir . '/' . $apps_name . '/admin/admin.php';
                    //$app_class_name_admin = $apps_name.'_admin';
                }
                if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $apps_name . '/admin/admin.php')) {
                    $local_admin_path = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $apps_name . '/admin/admin.php';
                    //require_once ($local_admin_path);
                    //$app_class_name_admin = $apps_name.'_admin';
                }/*elseif(file_exists($common_local_folder . '/apps/' . $apps_name . '/admin/admin.php')){

                }*/


                if (file_exists($this->apps_dir . '/' . $apps_name . '/site/site.php')) {
                    require_once($admin_path);
                    $site_path = $this->apps_dir . '/' . $apps_name . '/site/site.php';
                    require_once($site_path);
                    $app_class_name = $apps_name . '_site';
                }
                if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $apps_name . '/site/site.php')) {
                    require_once($admin_path);
                    $local_site_path = $this->apps_dir . '/' . $apps_name . '/site/site.php';
                    require_once($local_site_path);
                    $app_class_name = $apps_name . '_site';
                }
                if (file_exists(SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $apps_name . '/site/local_site.php')) {
                    require_once($admin_path);
                    $local_site_path = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . '/apps/' . $apps_name . '/site/local_site.php';
                    $app_class_name = 'local_' . $apps_name . '_site';
                    require_once($local_site_path);
                }
                /*if (file_exists($common_local_folder . '/apps/' . $apps_name . '/site/local_site.php')) {
                    $has_custom_site = true;
                    require_once ($admin_path);
                    $local_site_path = $common_local_folder . '/apps/' . $apps_name . '/site/local_site.php';
                    $app_class_name = 'local_' . $apps_name . '_site';
                    require_once ($local_site_path);
                }*/

                if ($app_class_name != '') {
                    //echo $app_class_name.'<br>';

                    $app_class_inst = new $app_class_name;

                    if (method_exists($app_class_inst, 'frontend')) {
                        //echo $app_class_name.'<br>';
                        //if ( $app_class_inst->frontend() ) {
                        $frontend = 1;
                        //}
                    }

                    if (method_exists($app_class_inst, '_preload')) {
                        $preload = 1;
                    }
                    unset($app_class_inst);
                }

                $active = 0;
                if ($this->getConfigValue('apps.' . $apps_name . '.enable')) {
                    $active = 1;
                }
                $active = 1;
                //echo $apps_name.', active = '.$active.', config_enable = '.$this->getConfigValue('apps.'.$apps_name.'.enable').'<br>';
                $priviledged_apps = array('config', 'table', 'menu', 'excelfree', 'excel', 'geodata', 'seo', 'sitemap',
                    'tlocation', 'toolbox', 'socialauth', 'banner', 'yandexrealty', 'getrent', 'client',
                    'predefinedlinks', 'currency', 'news', 'sitebill', 'customentity', 'dashboard', 'language', 'tpleditor', 'page', 'language',
                    'service');
                if (in_array($apps_name, $priviledged_apps)) {
                    $active = 1;
                }
                /*$query = 'INSERT INTO ' . DB_PREFIX . '_apps (`name`, `title`, `active`, `href_admin`, `admin_path`, `local_admin_path`, `site_path`, `local_site_path`, `preload`, `frontend`, `backend_menu`, `has_custom_site`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
                $stmt = $DBC->query($query, array($apps_name, $apps_items['title'], $active, $apps_items['href'], $admin_path, $local_admin_path, $site_path, $local_site_path, $preload, $frontend, $apps_items['backend_menu'], intval($has_custom_site)));*/
                $query = 'INSERT INTO ' . DB_PREFIX . '_apps (`name`, `title`, `active`, `href_admin`, `admin_path`, `local_admin_path`, `site_path`, `local_site_path`, `preload`, `frontend`, `backend_menu`, `params`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)';
                $stmt = $DBC->query($query, array($apps_name, $apps_items['title'], $active, $apps_items['href'], $admin_path, $local_admin_path, $site_path, $local_site_path, $preload, $frontend, $apps_items['backend_menu'], serialize($apps_items['params'])));
                //echo $DBC->getLastError();
            }
        }
    }

    /**
     * Загрузка массива приложений из базы данных
     * @return array массив приложений
     */
    function load_apps_from_db($params = array())
    {
        $Sitebill_Registry = Sitebill_Registry::getInstance();

        if ($this->getConfigValue('apps_cache_disable')) {
            return false;
        }
        $DBC = DBC::getInstance();
        if (isset($params['frontend']) && $params['frontend'] == 1) {
            $query = 'SELECT * FROM `' . DB_PREFIX . '_apps` WHERE `active`=? and `frontend`=?';
            $stmt = $DBC->query($query, array(1, 1));
        } elseif (isset($params['preload']) && $params['preload'] == 1) {
            $query = 'SELECT * FROM `' . DB_PREFIX . '_apps` WHERE `active`=? and `preload`=?';
            $stmt = $DBC->query($query, array(1, 1));
        } elseif (isset($params['backend_menu']) && $params['backend_menu'] == 1) {
            $query = 'SELECT * FROM `' . DB_PREFIX . '_apps` WHERE `active`=? and `backend_menu`=?';
            $stmt = $DBC->query($query, array(1, 1));
        } else {
            $query = 'SELECT * FROM `' . DB_PREFIX . '_apps` WHERE `active`=?';
            $stmt = $DBC->query($query, array(1));
        }

        $ra = array();
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                if ($ar['apps_id'] > 0) {
                    $ra[$ar['name']]['title'] = $ar['title'];
                    $ra[$ar['name']]['href'] = $ar['href_admin'];
                    $ra[$ar['name']]['admin_path'] = $ar['admin_path'];
                    $ra[$ar['name']]['local_admin_path'] = $ar['local_admin_path'];
                    $ra[$ar['name']]['site_path'] = $ar['site_path'];
                    $ra[$ar['name']]['local_site_path'] = $ar['local_site_path'];
                    $ra[$ar['name']]['preload'] = $ar['preload'];
                    $ra[$ar['name']]['frontend'] = $ar['frontend'];
                    $ra[$ar['name']]['params'] = unserialize($ar['params']);
                    if (isset($ra[$ar['name']]['params']['handlers']) && is_array($ra[$ar['name']]['params']['handlers'])) {
                        foreach ($ra[$ar['name']]['params']['handlers'] as $handler) {
                            $Sitebill_Registry::add_handler($handler);
                        }
                    }
                }
            }
        }
        if (count($ra) > 0) {
            return $ra;
        }
        return false;
    }

    /**
     * Получаем массив данных о приложении из таблицы apps по имени
     * @param type $name
     * @return mixed массив с данными о приложении или false если приложение не найдено
     */
    function get_apps_by_name($name)
    {
        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM `' . DB_PREFIX . '_apps` WHERE `name`=?';
        $stmt = $DBC->query($query, array($name));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            if ($ar['apps_id'] > 0) {
                return $ar;
            }
        }
        return false;
    }

}
