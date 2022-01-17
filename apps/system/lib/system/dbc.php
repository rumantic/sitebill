<?php
if ( !class_exists('Sitebill') and !defined('INSTALL_MODE')) {
    require_once (SITEBILL_DOCUMENT_ROOT.'/apps/system/bootstrap.php');
}
use Illuminate\Database\Capsule\Manager as Capsule;

class DBC {

    public static $instance;
    protected static $lastError;

    /**
     * Obtain DB instance
     *
     * @return DB
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self ( );
        }

        return self::$instance;
    }

    private $queries = array();
    private $pdo;

    private function __construct() {
        try {
            $this->pdo = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_ENCODING.', sql_mode=""'));
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

            $capsule = new Capsule;

            $capsule->addConnection([
                'driver'    => 'mysql',
                'host'      => DB_HOST,
                'database'  => DB_BASE,
                'username'  => DB_USER,
                'password'  => DB_PASS,
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => DB_PREFIX.'_',
            ]);

            $capsule->setAsGlobal();

            $capsule->bootEloquent();

            if ( !defined('INSTALL_MODE') ) {
                SiteBill::add_pdo_debugbar_collector($this->pdo, $capsule);
            }

        } catch (PDOException $e) {
            echo 'Unable to connect to database: ', $e->getMessage(), "\n";
            exit;
        }
        /* $f=fopen($_SERVER["DOCUMENT_ROOT"].'/ddd.txt', 'a');
          fwrite($f, "1\n");
          fclose($f); */
        $white_list = array("5.9.72.112", "5.9.72.121", "194.58.111.5", "5.9.92.11", "193.124.207.3", "127.0.0.1", "159.69.119.38", "89.108.114.190");
        if (!preg_match("/admin/", $_SERVER["REQUEST_URI"]) and ! in_array($_SERVER["SERVER_ADDR"], $white_list) and !defined('STDIN')) {
            try {
                $stmt = $this->pdo->query("select * from " . DB_PREFIX . "_config where config_key = 'license_key'");
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $ins = self::decode($row['value']);
                $d = time() - $ins;
                if ($d > 86400 * 30) {
                    echo self::get_license_message();
                    exit;
                }
            } catch (PDOException $e) {

            }
        }

        //echo 'DBC connect<br />';
    }

    function get_license_message() {
        $rs = "Vasha licensiya zakonchilas. <a href=\"http://www.sitebill.ru/price-cms-sitebill/\">Kupit kluch</a><br><br>";
        $rs .= "Your license key has been expired. <a href=\"http://www.sitebill.ru/price-cms-sitebill/\">Buy license key</a>.";
        return $rs;
    }

    function quote($string) {
        return $this->pdo->quote($string);
    }

    private function decode($key) {
        $sum = 0;
        $array = explode('-', $key);
        $first = hexdec($array[0]);
        $second = hexdec($array[1]);
        $index = ($first + $second) / 10000;
        if (!in_array($index, array(1, 2, 3, 4))) {
            return 0;
        }
        for ($i = 2; $i < 5; $i++) {
            $sum += hexdec($array[$i]);
        }
        if ($sum != hexdec($array[5])) {
            return 0;
        }
        return hexdec($array[$index]);
    }

    /**
     * Execute an SQL statement and return the number of affected rows
     *
     * @param string $query
     * @return string
     */
    public function executeSQL($query) {
        if (defined('DEBUG_ENABLED') && DEBUG_ENABLED) {
            Debugger::appendQuery($query);
        }

        try {
            $rows = $this->pdo->exec($query);
        } catch (PDOException $e) {
            Logger::append($query, $e);

            if (defined('DEBUG_ENABLED') && DEBUG_ENABLED) {
                Debugger::appendException($e);
            }
        }

        return $rows;
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     *
     * @param string $query
     * @return PDOStatement
     */
    public function querySQL($query) {
        if (defined('DEBUG_ENABLED') && DEBUG_ENABLED) {
            Debugger::appendQuery($query);
        }

        try {
            $stmt = $this->pdo->query($query);
        } catch (PDOException $e) {
            Logger::append($query, $e);

            if (defined('DEBUG_ENABLED') && DEBUG_ENABLED) {
                Debugger::appendException($e);
            }
        }

        return $stmt;
    }

    /**
     * Execute statement and return the number of affected rows
     *
     * @param string $query SQL statement
     * @param array $params array of parameters to bind
     * @return integer
     */
    public function execute($query, $params = array()) {
        $success = false;
        $rows = 0;
        $this->exec($query, $params, $success, $rows);

        if (!$success) {
            throw new PDOException();
        }

        return $rows;
    }

    /**
     * Executes statement, returning a result set as a PDOStatement object
     *
     * @param string $query SQL statement
     * @param array $params array of parameters to bind
     * @param integer $rows number of selected rows
     * @return PDOStatement | false
     */
    public function query($query, $params = array(), &$rows = 0, &$success_mark = false) {
        $success = false;
        $stmt = $this->exec($query, $params, $success, $rows);
        $success_mark = $success;
        return $success && $rows ? $stmt : false;
    }

    /**
     * Returns the ID of the last inserted row or sequence value
     *
     * @return integer
     * */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    /**
     * Initiates a transaction
     *
     * @return boolean
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commits a transaction
     *
     * @return boolean
     */
    public function commit() {
        return $this->pdo->commit();
    }

    /**
     * Rolls back a transaction
     *
     * @return boolean
     */
    public function rollback() {
        return $this->pdo->rollBack();
    }

    public function debugQueries() {
        return $this->queries;
    }

    public function fetch(&$stmt) {
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getLastError() {
        return self::$lastError;
    }

    private function paramType($param) {

        if (is_numeric($param)) {
            if (is_int($param)) {
                return PDO::PARAM_INT;
            } else {
                return PDO::PARAM_STR;
            }
        } elseif (is_null($param)) {
            return PDO::PARAM_NULL;
        } elseif (is_bool($param)) {
            return PDO::PARAM_BOOL;
        }

        return PDO::PARAM_STR;
    }

    private function exec($sql, $params = array(), &$success, &$rows) {
        self::$lastError = '';
        $stmt = $this->pdo->prepare($sql);
        $debug = !defined('AJAX') && defined('DEBUG_ENABLED') && DEBUG_ENABLED;

        if ( is_array($params) ) {
            for ($i = 0; $i < count($params); $i ++) {
                $stmt->bindParam($i + 1, $params [$i], $this->paramType($params [$i]));
            }
        }

        try {
            $start = microtime(true);
            $success = $stmt->execute();
            $finish = microtime(true);
        } catch (PDOException $e) {
            self::$lastError = $e->getMessage();
            $success = false;
            Logger::append($sql, $e);

            if ($debug) {
                Debugger::appendException($e);
            }
        }

        if ($debug) {
            $ls = array();
            $time = $finish - $start;
            Debugger::appendQuery($sql . " [$time sec]");
            //$trace=implode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            //print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            $trace_log = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            foreach ($trace_log as $i => $v) {
                $ls[] = '#' . $i . ' ' . (isset($v['class']) ? $v['class'] : '') . '' . (isset($v['type']) ? $v['type'] : '') . '' . $v['function'] . '() called at [' . $v['file'] . ':' . $v['line'] . ']';
            }
            $trace = '<p>' . implode('</p><p>', $ls) . '</p>';
            /* ob_start();
              debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
              $trace = ob_get_contents();
              ob_end_clean(); */

            Debugger::appendQueryExt($sql, $time, $trace);
        }

        if ($success) {
            $rows = $stmt->rowCount();
        }

        return $stmt;
    }

}
