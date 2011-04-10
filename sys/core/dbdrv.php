<?php
/*************************************************************************
 Copyright 2011 Vevui Development Team

 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at

     http://www.apache.org/licenses/LICENSE-2.0

 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
*************************************************************************/

abstract class DBDrv
{
	const SQL_UNDEFINED = 0;
	const SQL_RAW = 1;
	const SQL_INSERT = 2;
	const SQL_SELECT = 3;
	const SQL_UPDATE = 4;
	const SQL_DELETE = 5;

	protected $_type;
	protected $_connection;
	protected $_table;

	protected $_raw_query;
	protected $_protect;

	protected $_fields;
	protected $_joins;
	protected $_conds;
	protected $_limit;
	protected $_offset;

	protected $_order;

	function new_query($name)
	{
		$this->_type = DBDrv::SQL_UNDEFINED;
		$this->_table = $name;
		$this->_raw_query = $this->_protect = NULL;
		$this->_fields = $this->_joins = $this->_conds = $this->_limit = $this->_offset = NULL;
		$this->_order = NULL;
	}

	function raw($query, $protect = array())
	{
		$this->_type = DBDrv::SQL_RAW;
		$this->_raw_query = $query;
		$this->_protect = $protect;
		return $this;
	}

	function insert($fields)
	{
		$this->_type = DBDrv::SQL_INSERT;
		$this->_fields = $fields;
		return $this;
	}

	function select($fields = NULL)
	{
		$this->_type = DBDrv::SQL_SELECT;
		$this->_fields = $fields;
		return $this;
	}

	function update($fields, $conditions = array())
	{
		$this->_type = DBDrv::SQL_UPDATE;
		$this->_fields = $fields;
		if ($conditions) $this->_conds[] = $conditions;
		return $this;
	}

	function delete($conditions = array())
	{
		$this->_type = DBDrv::SQL_DELETE;
		if ($conditions) $this->_conds[] = $conditions;
		return $this;
	}

	function join($table, $type = Mdl::INNER_JOIN, $conditions = array())
	{
		$this->_joins[] = array
			(
				$table,
				$type,
				$conditions
			);
		return $this;
	}

	function where($conditions)
	{
		$this->_conds[] = $conditions;
		return $this;
	}

	function group($fields)
	{
		return $this;
	}

	function having($fields)
	{
		return $this;
	}

	function order($field, $type = DBDrv::ORDER_BY_ASC)
	{
		$this->_order[] = array
			(
				$field,
				$type
			);
		return $this;
	}

	function limit($limit, $offset = null)
	{
		$this->_limit = $limit;
		$this->_offset = $offset;
		return $this;
	}

	protected function _raise_error($error_string)
	{
		include(APP_PATH.'/x/db.php');
	}

	abstract function escape($mixed);
	abstract function string();
	abstract function exec();
}

/* End of file sys/core/dbdrv.php */
