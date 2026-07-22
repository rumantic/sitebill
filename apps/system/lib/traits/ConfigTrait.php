<?php
/**
 * ConfigTrait — extracted from SiteBill class (sitebill.php)
 * Auto-generated, do not edit manually.
 */
trait ConfigTrait
{
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
     * @param array $mediadata
     * @param string $type
     * @param int $inctype (0 relative, 1 absolute, 2 root)
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

    public function getMediaDocsDir()
    {
        return '/img/mediadocs/';
    }

    public function getImgDataDir()
    {
        return '/img/data/';
    }

    /**
     * Is demo
     * @param void
     * @return boolean
     */
    function isDemo()
    {
        global $__user, $__db;
        if ($__db && preg_match('/rumantic_estate/', $__db)) {
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

    protected function gatherParamsFromSconfig ($params)
    {
        if ( SConfig::getConfigValueStatic('searchable_params') !== null and is_array(SConfig::getConfigValueStatic('searchable_params')) ) {
            foreach ( SConfig::getConfigValueStatic('searchable_params') as $param ) {
                if (NULL !== $this->getRequestValue($param)) {
                    $params[$param] = $this->getRequestValue($param);
                }
            }
        }
        $base_deifinition = ['hot'];
        foreach ( $base_deifinition as $item ) {
            if (NULL !== $this->getRequestValue($item)) {
                $params[$item] = $this->getRequestValue($item);
            }
        }

        return $params;
    }

}
