<?php

class DBC {
	
	public static $instance;
	
	/**
	 * Obtain DB instance
	 *
	 * @return DB
	 */
	public static function getInstance() {
		if (! self::$instance) {
			self::$instance = new self ( );
		}
		
		return self::$instance;
	
	}
	
	private $queries = array ();
	
	private $pdo;
	
	private function __construct() {
		$this->pdo = new PDO ( DB_DSN, DB_USER, DB_PASS, array( PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES ".DB_ENCODING) );
		$this->pdo->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$this->pdo->setAttribute ( PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true );
	}
	
	/**
	 * Execute an SQL statement and return the number of affected rows 
	 *
	 * @param string $query
	 * @return string
	 */
	public function executeSQL($query) {
		if (defined ( 'DEBUG_ENABLED' ) && DEBUG_ENABLED) {
			Debugger::appendQuery ( $query );
		}
		
		try {
			$rows = $this->pdo->exec ( $query );
		} catch ( PDOException $e ) {
			Logger::append ( $query, $e );
			
			if (defined ( 'DEBUG_ENABLED' ) && DEBUG_ENABLED) {
				Debugger::appendException ( $e );
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
		if (defined ( 'DEBUG_ENABLED' ) && DEBUG_ENABLED) {
			Debugger::appendQuery ( $query );
		}
		
		try {
			$stmt = $this->pdo->query ( $query );
		} catch ( PDOException $e ) {
			Logger::append ( $query, $e  );
			
			if (defined ( 'DEBUG_ENABLED' ) && DEBUG_ENABLED) {
				Debugger::appendException ( $e );
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
		$this->exec ( $query, $params, $success, $rows );
		
		if (! $success) {
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
	public function query($query, $params = array(), &$rows = 0) {
		$success = false;
		$stmt = $this->exec ( $query, $params, $success, $rows );
		
		return $success && $rows ? $stmt : false;
	}
	
	/**
	 * Returns the ID of the last inserted row or sequence value
	 * 
	 * @return integer
	 * */
	public function lastInsertId() {
		return $this->pdo->lastInsertId ();
	}
	
	/**
	 * Initiates a transaction
	 *
	 * @return boolean
	 */
	public function beginTransaction() {
		return $this->pdo->beginTransaction ();
	}
	
	/**
	 * Commits a transaction
	 *
	 * @return boolean
	 */
	public function commit() {
		return $this->pdo->commit ();
	}
	
	/**
	 * Rolls back a transaction
	 *
	 * @return boolean
	 */
	public function rollback() {
		return $this->pdo->rollBack ();
	}
	
	public function debugQueries() {
		return $this->queries;
	}
	
	public function fetch(&$stmt) {
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
		
	private function paramType($param) {
		
		if (is_numeric( $param )) {
			return PDO::PARAM_INT;
		} elseif (is_null ( $param ) || (is_string ( $param ) && ! $param)) {
			return PDO::PARAM_NULL;
		} elseif (is_bool ( $param )) {
			return PDO::PARAM_BOOL;
		}
		
		return PDO::PARAM_STR;
	}
	
	private function exec($sql, $params = array(), &$success, &$rows) {
		$stmt = $this->pdo->prepare ( $sql );
		$debug = ! defined( 'AJAX') && defined ( 'DEBUG_ENABLED' ) && DEBUG_ENABLED;
		
		for ($i = 0; $i < count ( $params ); $i ++) {
			$stmt->bindParam ( $i + 1, $params [$i], $this->paramType ( $params [$i] ) );
		}
		
		try {
			$start = microtime( true );
			$success = $stmt->execute ();
			$finish = microtime( true );
		} catch ( PDOException $e ) {
			$success = false;
			Logger::append ( $sql, $e  );
			
			if ($debug) {
				Debugger::appendException ( $e );
			}
		}
		
		if ($debug) {
			$time = $finish - $start;
			Debugger::appendQuery ( $sql . " [$time sec]" );
			
			ob_start();
			debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			$trace = ob_get_contents();
			ob_end_clean();
			Debugger::appendQueryExt ( $sql, $time, $trace );
		}
		
		if ($success) {
			$rows = $stmt->rowCount ();
		}
		
		return $stmt;
	}

}