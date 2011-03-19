<?php

class MongoMdl
{
	private $_m;
	private $_db;
	
	function __construct($dbname)
	{
		$this->_m = new Mongo();
		$this->_db = $this->_m->$dbname;
		//$this->db->authenticate($username, $password);		
	}
	
	function select_db($dbname)
	{
		$this->_db = $this->_m->$dbname;	
	}
	
	function __get($col)
	{
		return $this->_db->$col;
	}
}

/* End of file sys/core/mongomdl.php */