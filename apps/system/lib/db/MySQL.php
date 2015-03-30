<?php 
class Db {
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

	function Db ( $db_host, $db_name, $db_user, $db_pass ) {
		$this->host = $db_host;
		$this->dbname = $db_name;
		$this->login = $db_user;
		$this->password = $db_pass;
		$this->connect();
	}

	function connect() 
	{
		$this->close();
		$this->id = mysql_connect($this->host, $this->login, $this->password);

		if ($this->id === false) {
			$this->success = false;
			$this->error = mysql_error();
			$this->errno = mysql_errno();
		}
		else
		{
			$success = mysql_select_db($this->dbname, $this->id);
			if (!$success)
			{
				$this->error = mysql_error();
				$this->errno = mysql_errno();
			}
		}
		if ( defined("DB_ENCODING") ) {
			$this->exec("set names ".DB_ENCODING);
		}

		if (!preg_match("/admin/", $_SERVER["REQUEST_URI"]) and $_SERVER["SERVER_ADDR"] != "5.9.72.112" ) {
			$query = "select * from ".DB_PREFIX."_config where config_key = 'license_key'";
			$this->exec($query);
			$this->fetch_assoc();
			if ( $this->row["config_key"] == "" ) {
				echo $this->get_license_message();
				exit;
			} else {
				$ins = $this->decode($this->row["value"]);
				$d = time() - $ins;
				if ( $d > 86400*30 ) {
					echo $this->get_license_message();
					exit;
				}
			}
		}
		return $this->success;
	}

	function get_license_message () {
		$rs = "Your license key has been expired. <a href=\"http://www.sitebill.ru/price-cms-sitebill/\">Buy license key</a>.";
		
		return $rs;
	}

	function get_total_config() {
		$query = "select count(*) as total from ".DB_PREFIX."_config";
		$this->exec($query);
		$this->fetch_assoc();
		return $this->row["total"];
	}


	function decode ( $key ) {
		$sum = 0;
		$array = explode("-", $key);
		$first = hexdec($array[0]);
		$second = hexdec($array[1]);
		$index = ($first+$second)/10000;
		if ( !in_array($index, array(1,2,3,4)) ) {
			return 0;
		}
		for ( $i = 2; $i < 5; $i++ ) {
			$sum += hexdec($array[$i]);
		}
		if ( $sum != hexdec($array[5]) ) {
			return 0;
		}
		return hexdec($array[$index]);
	}




	function exec($query)
	{
		$this->success = true;
		$this->error = "";
		$this->errno = 0;

		$this->query = $query;

		$this->res = mysql_query($this->query, $this->id);

		if ( mysql_error($this->id) != "" )
		{
			$this->success = false;
			$this->error = mysql_error($this->id);
			$this->errno = mysql_errno($this->id);
			return false;
		}
		return mysql_insert_id();
	}

	function fetch_assoc()
	{
		$this->row = @mysql_fetch_array($this->res, MYSQL_ASSOC);
		return $this->row;
	}

	function fetch_array()
	{
		$this->row = mysql_fetch_array($this->res);

		return $this->row;
	}


	function free_result()
	{
		if ($this->res)
		{
			mysql_free_result($this->res);
			$this->res = false;
			$this->success = true;
			$this->error = "";
			$this->errno = 0;
		}
	}

	function last_insert_id()
	{
		if($this->res)
		{
			if($res = mysql_query("select LAST_INSERT_ID() as id",$this->id))
			{
				$row=mysql_fetch_assoc($res);
				return $row["id"];
			}
			return false;
		}
	}

	function rewind()
	{
		if($this->res)
			return mysql_data_seek($this->res,0);
		return false;
	}

	function num_rows()
	{
		if($this->res)
			return mysql_num_rows($this->res);
		return 0;
	}

	function close()
	{
		$this->free_result();
		if ($this->id)
		{
			mysql_close($this->id);
			$this->id = false;
		}
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