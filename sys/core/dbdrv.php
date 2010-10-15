<?php

abstract class DBDrv
{
	protected $_connection;
	protected $_table;
	protected $_fields;
	protected $_joins;
	protected $_conds;
	protected $_limit;
	protected $_offset;

	function get($fields = null)
	{
		$this->_fields = $fields;
		return $this;
	}

	function new_query($name)
	{
		$this->_table = $name;
		$this->_fields = $this->_joins = $this->_conds = $this->_limit = $this->_offset = null;
	}

	function join($table, $type = 'natural', $conditions = array())
	{
		$this->_joins[] = array('table' => $table,
								'type' => $type,
								'conds' => $conditions
						);
		return $this;
	}

	function where($conditions = array())
	{
		$this->_conds = $conditions;
		return $this;
	}

	function group($fields = array())
	{
		$this->_conds = $conditions;
		return $this;
	}

	function having($fields = array())
	{
		$this->_conds = $conditions;
		return $this;
	}

	function order($fields = array())
	{
		$this->_conds = $conditions;
		return $this;
	}

	function limit($limit, $offset = null)
	{
		$this->_limit = $limit;
		$this->_offset = $offset;
		return $this;
	}

	abstract function exec();
}

/* End of file sys/core/dbdrv.php */