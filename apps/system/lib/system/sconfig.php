<?php

class SConfig {

    public static $instance;
    private static $config_array = array();
    private static $public_config_array = array();

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self ( );
        }
        return self::$instance;
    }

    public function getConfig() {
        return self::$config_array;
    }

    public function getPublicConfig() {
        return self::$public_config_array;
    }

    public function getConfigValue($key) {
        if (isset(self::$config_array[$key])) {
            return self::$config_array[$key];
        }
        return false;
    }

    public function setConfigValue($key, $value) {
        self::$config_array[$key] = $value;
    }
    
    /**
     * Обновляем значение параметра в hidden_config
     * Если параметра нет, то он создается автоматически
     * @param type $key
     * @param type $value
     */
    public static function updateHiddenConfigValue($key, $value) {
        $DBC = DBC::getInstance();
        $query='INSERT INTO '.DB_PREFIX.'_hidden_config (`config_key`, `config_value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `config_value`=?';
        $stmt = $DBC->query($query, array($key, $value, $value), $success);
        if ( !$success ) {
            return $DBC->getLastError();
        }
        return true;
    }
    
    /**
     * Если нет ключа, создаем его. Но при этом не перезаписываем существующий ключ и его значение
     * @param type $key
     * @param type $value
     */
    public static function initHiddenConfigValue($key, $value) {
        $DBC = DBC::getInstance();
        $query='INSERT INTO '.DB_PREFIX.'_hidden_config (`config_key`, `config_value`) VALUES (?,?) ON DUPLICATE KEY UPDATE `config_value`=`config_value`';
        $stmt = $DBC->query($query, array($key, $value));
    }
    
    public static function getHiddenConfigValue($key) {
        $DBC = DBC::getInstance();
        $query = 'SELECT `config_value` FROM ' . DB_PREFIX . '_hidden_config WHERE `config_key`=?';
        $stmt = $DBC->query($query, array($key));
        if ($stmt) {
            $ar = $DBC->fetch($stmt);
            return $ar['config_value'];
        }
    }
    
    

    private function __construct() {
        self::loadConfig();
    }

    private static function loadConfig() {

        self::$config_array['per_page'] = 25;
        self::$config_array['site_title'] = 'Агентство недвижимости';

        self::$config_array['auto_image_big_width'] = 800;
        self::$config_array['auto_image_big_height'] = 600;

        self::$config_array['auto_image_preview_width'] = 200;
        self::$config_array['auto_image_preview_height'] = 200;

        self::$config_array['data_image_big_width'] = 1000;
        self::$config_array['data_image_big_height'] = 800;

        self::$config_array['data_image_preview_width'] = 300;
        self::$config_array['data_image_preview_height'] = 300;

        self::$config_array['shop_product_image_big_width'] = 800;
        self::$config_array['shop_product_image_big_height'] = 600;

        self::$config_array['shop_product_image_preview_width'] = 200;
        self::$config_array['shop_product_image_preview_height'] = 200;

        self::$config_array['vendor_image_big_width'] = 800;
        self::$config_array['vendor_image_big_height'] = 600;

        self::$config_array['vendor_image_preview_width'] = 50;
        self::$config_array['vendor_image_preview_height'] = 50;



        self::$config_array['topic_image_big_width'] = 800;
        self::$config_array['topic_image_big_height'] = 600;

        self::$config_array['topic_image_preview_width'] = 200;
        self::$config_array['topic_image_preview_height'] = 200;

        $DBC = DBC::getInstance();
        $query = 'SELECT * FROM ' . DB_PREFIX . '_config';
        $stmt = $DBC->query($query);
        if ($stmt) {
            while ($ar = $DBC->fetch($stmt)) {
                self::$config_array[$ar['config_key']] = $ar['value'];
                if ( $ar['public'] == 1 ) {
                    self::$public_config_array[$ar['config_key']] = $ar['value'];
                }
            }
        }
        if (isset(self::$config_array['apps.realty.data_image_preview_width'])) {
            self::$config_array['data_image_preview_width'] = self::$config_array['apps.realty.data_image_preview_width'];
        }

        if (isset(self::$config_array['apps.realty.data_image_preview_height'])) {
            self::$config_array['data_image_preview_height'] = self::$config_array['apps.realty.data_image_preview_height'];
        }

        if (isset(self::$config_array['apps.realty.data_image_big_width'])) {
            self::$config_array['data_image_big_width'] = self::$config_array['apps.realty.data_image_big_width'];
        }

        if (isset(self::$config_array['apps.realty.data_image_big_height'])) {
            self::$config_array['data_image_big_height'] = self::$config_array['apps.realty.data_image_big_height'];
        }

        if (!self::loadDomainConfig()) {
            $core_domain = trim(self::$config_array['core_domain']);
            if ($core_domain != '') {
                self::loadSubdomenalConfig($core_domain);
            }
        }
        //$core_domain='estatecms.ru';
        //var_dump(self::$config_array['apps.language.default_lang_code']);

        /* if(isset($_SESSION['user_domain_owner']) && isset($_SESSION['user_domain_owner']['theme']) && $_SESSION['user_domain_owner']['theme']!=''){
          self::$config_array['theme'] = $_SESSION['user_domain_owner']['theme'];
          } */
    }

    private static function loadDomainConfig() {
        $domain = $_SERVER['HTTP_HOST'];
        $domain = preg_replace('/^www\./', '', $domain);
        $domain_config = $_SERVER['DOCUMENT_ROOT'] . '/' . $domain . '.config.php';
        if (file_exists($domain_config)) {
            include_once $domain_config;
            //print_r($Local_Config);
            //$domain_settings=parse_ini_file($domain_config, true);
            $domain_settings = $Local_Config;
            if (is_array($domain_settings)) {
                self::$config_array = array_merge(self::$config_array, $domain_settings);
            }
            return true;
        }
        return false;
    }

    private static function loadSubdomenalConfig($core_domain = '') {
        $uri = $_SERVER['HTTP_HOST'];
        $uri = preg_replace('/^www\./', '', $uri);
        if ($uri != $core_domain) {
            $subdomain = preg_replace('/\.' . $core_domain . '$/', '', $uri);
        } else {
            $subdomain = '_core';
        }
        $subdomenal_config = $_SERVER['DOCUMENT_ROOT'] . '/' . $subdomain . '.config.php';

        if (file_exists($subdomenal_config)) {
            include_once $subdomenal_config;
            $subdomenal_settings = $Local_Config;
            //$subdomenal_settings=parse_ini_file($subdomenal_config, true);
            if (is_array($subdomenal_settings)) {
                self::$config_array = array_merge(self::$config_array, $subdomenal_settings);
            }
        }
    }

}
