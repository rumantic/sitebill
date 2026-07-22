<?php
/**
 * DebugLogTrait — extracted from SiteBill class (sitebill.php)
 * Auto-generated, do not edit manually.
 */
trait DebugLogTrait
{
    public static function register_debugbar()
    {
        if (defined('DEBUG_ENABLED') && DEBUG_ENABLED && !isset(self::$debugbar)) {
            self::$debugbar = new \DebugBar\StandardDebugBar();
        }
    }

    static function getdebugbarRenderer()
    {
        if (isset(self::$debugbar)) {
            $baseUrl = SITEBILL_MAIN_URL . '/apps/third/vendor/maximebf/debugbar/src/DebugBar/Resources';
            return self::$debugbar->getJavascriptRenderer($baseUrl);
        }
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
     * Rise error
     * @param string $error_message error message
     * @return void
     */
    function riseError($error_message)
    {
        if ( $error_message != 'not login' ) {
            $this->writeLog('<span class="error">error: ' . $error_message . '</span>', true);
        }
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
            if (!isset($this->logger_admin)) {
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

    public static function get_microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

}
