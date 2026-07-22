<?php
/**
 * TemplateTrait — extracted from SiteBill class (sitebill.php)
 * Auto-generated, do not edit manually.
 */
trait TemplateTrait
{
    public static function smarty_fetch($file_name)
    {
        global $smarty;
        return $smarty->fetch($file_name);

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

    function get_template_full_path( $template_value )
    {
        $old_local_template = SITEBILL_DOCUMENT_ROOT . '/template/frontend/' . $this->getConfigValue('theme') . $template_value;
        if (file_exists($old_local_template)) {
            return $old_local_template;
        }
        $local_template = SITEBILL_DOCUMENT_ROOT . '/template/frontend/local'. $template_value;
        if (file_exists($local_template)) {
            return $local_template;
        }
        return SITEBILL_DOCUMENT_ROOT.$template_value;
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

    public function getAdminTplFolder()
    {
        $allowed = array('template1', 'tailwind');
        $template = '';

        // 1. Per-session override (set in apps/admin/admin/backend.php)
        if ( isset($_SESSION['admin_template']) && in_array($_SESSION['admin_template'], $allowed, true) ) {
            $template = $_SESSION['admin_template'];
        }
        // 2. Global config fallback
        if ( $template === '' && method_exists($this, 'getConfigValue') ) {
            $cfg = $this->getConfigValue('apps.admin.template');
            if ( in_array($cfg, $allowed, true) ) {
                $template = $cfg;
            }
        }
        if ( $template === '' ) {
            $template = 'template1';
        }

        $folder = SITEBILL_DOCUMENT_ROOT . '/apps/admin/admin/' . $template;
        if ( !is_dir($folder) ) {
            $folder = SITEBILL_DOCUMENT_ROOT . '/apps/admin/admin/template1';
        }
        return $folder;
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

    /**
     * Return current displayed template file
     * @return string
     */
    public static function getTplFile()
    {
        return self::$tpl_file;
    }

    /**
     * Set new displayed template file
     * @param string $tpl
     */
    public static function setTplFile($tpl)
    {
        self::$tpl_file = $tpl;
    }

    /**
     * Set key-valued array of variables to template (equals to Template::assert(array))
     * @param array $vars key-valued array of variables
     * @return void
     */
    public function setTplVars($vars){
        if(is_array($vars) && !empty($vars)){
            foreach ($vars as $var => $val){
                $this->template->assign($var, $val);
            }
        }
    }

    static public function get_template_store($key)
    {
        return self::$_template_store[$key];
    }

    static public function set_template_store($key, $value)
    {
        self::$_template_store[$key] = $value;
    }

}
