<?php
class Mysql_DB_Emulator {
	var $host;
	var $dbname;
	var $login;
	var $password;

	var $id = false;
	var $error = "";
	var $success = false;
	var $errno = 0;
	var $query = "";
	var $res = false;
	var $row;

	function Mysql_DB_Emulator ( ) {
		$this->DBC = DBC::getInstance();
		return true;
	}

	function connect() # connect to MySQL server and select given database
	{
		return true;
	}


	function get_total_config() {
		return 1;
	}


	function exec($query)
	{
		$this->success = true;
		$this->error = "";
		$this->errno = 0;
		$this->res = false;

		$this->query = $query;
		
		$stmt=$this->DBC->query($query, array(), $row, $success);
	    if ( !$success ) {
	    	$this->success = false;
	    	$this->error = $this->DBC->getLastError();
	    	$this->errno = $this->DBC->getLastError();
	    	return false;
	    }
				
		$this->res = $stmt;
		return true;
	}

	function fetch_assoc()
	{
		if ( $this->res ) {
			$ar=$this->DBC->fetch($this->res);
			$this->row = $ar;
			//echo 'test';
			//print_r($ar);
			//exit;
			return $ar;
		}
		return false;
	}

	function fetch_array()
	{
		return false;
	}


	function free_result()
	{
		if ($this->res)
		{
			$this->res = false;
			$this->success = true;
			$this->error = "";
			$this->errno = 0;
		}
	}

	function last_insert_id()
	{
		return false;
	}

	function rewind()
	{
		return false;
	}

	function num_rows()
	{
		return 0;
	}

	function close()
	{
		return true;
	}

	function begin() {
		return true;
	}

	function commit() {
		return true;
	}

	function rollback() {
		return true;
	}
}
