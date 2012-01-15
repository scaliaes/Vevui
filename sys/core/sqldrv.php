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

abstract class SQLDrv implements Iterator
{
	const SQL_UNDEFINED = 0;
	const SQL_RAW = 1;
	const SQL_INSERT = 2;
	const SQL_SELECT = 3;
	const SQL_UPDATE = 4;
	const SQL_DELETE = 5;

	const JOIN = 0;
	const NATURAL_JOIN = 1;
	const LEFT_JOIN = 2;
	const RIGHT_JOIN = 3;
	const FULL_OUTER_JOIN = 4;
	const CROSS_JOIN = 5;

	private $_core;

	protected $_type;
	protected $_table;

	protected $_raw_query;
	protected $_protect;

	protected $_fields;
	protected $_joins;
	protected $_conds;
	protected $_group;
	protected $_having;
	protected $_limit;
	protected $_offset;

	protected $_order;

	protected $_as_object;

	function __construct($dbconfig, $installation_data = NULL)
	{
		$this->_core = & Vevui::get();

		if ($installation_data && array_key_exists('missing', $installation_data))
		{
			$this->_core->missing_component(get_class($this), $installation_data['missing']);
		}
	}

	function new_query($name)
	{
		$this->_type = self::SQL_UNDEFINED;
		$this->_table = $name;
		$this->_raw_query = $this->_protect = NULL;
		$this->_fields = $this->_joins = $this->_conds = $this->_group = $this->_having = $this->_limit = $this->_offset = NULL;
		$this->_order = NULL;
		$this->_as_object = FALSE;
		return $this;
	}

	function raw($query, $protect = array())
	{
		$this->_type = self::SQL_RAW;
		$this->_raw_query = $query;
		$this->_protect = $protect;
		return $this;
	}

	function insert($fields)
	{
		$this->_type = self::SQL_INSERT;
		$this->_fields = is_array(reset($fields))?$fields:array($fields);
		return $this;
	}

	function select($fields = NULL)
	{
		$this->_type = self::SQL_SELECT;
		$this->_fields = $fields;
		return $this;
	}

	function update($fields)
	{
		$this->_type = self::SQL_UPDATE;
		$this->_fields = $fields;
		return $this;
	}

	function delete()
	{
		$this->_type = self::SQL_DELETE;
		return $this;
	}

	function join($table, $field, $value, $operator = NULL)
	{
		$this->_joins[] = array
			(
				$table,
				self::JOIN,
				array($field, $value, $operator)
			);
		return $this;
	}

	function natural_join($table)
	{
		$this->_joins[] = array
			(
				$table,
				self::NATURAL_JOIN
			);
		return $this;
	}

	function left_join($table, $field, $value, $operator = NULL)
	{
		$this->_joins[] = array
			(
				$table,
				self::LEFT_JOIN,
				array($field, $value, $operator)
			);
		return $this;
	}

	function right_join($table, $field, $value, $operator = NULL)
	{
		$this->_joins[] = array
			(
				$table,
				self::RIGHT_JOIN,
				array($field, $value, $operator)
			);
		return $this;
	}

	function full_join($table, $field, $value, $operator = NULL)
	{
		$this->_joins[] = array
			(
				$table,
				self::FULL_OUTER_JOIN,
				array($field, $value, $operator)
			);
		return $this;
	}

	function cross_join($table)
	{
		$this->_joins[] = array
			(
				$table,
				self::CROSS_JOIN
			);
		return $this;
	}

	function where($field, $value = NULL, $operator = NULL)
	{
		$this->_conds[] = array($field, $value, $operator);
		return $this;
	}

	function group($field)
	{
		$this->_group[] = $field;
		return $this;
	}

	function having($field, $value = NULL, $operator = NULL)
	{
		$this->_having[] = array($field, $value, $operator);
		return $this;
	}

	function order($field, $asc = TRUE)
	{
		$this->_order[] = array
			(
				$field,
				$asc
			);
		return $this;
	}

	function limit($limit, $offset = NULL)
	{
		$this->_limit = $limit;
		$this->_offset = $offset;
		return $this;
	}

	function as_obj()
	{
		$this->_as_object = TRUE;
		return $this;
	}

	protected function _raise_error($errno, $error_string, $file, $line)
	{
		$this->_core->error_handler($errno, $error_string, $file, $line);
	}

	abstract function register_functions();
	abstract function exec();
	abstract function exec_one();
}

/* End of file sys/core/sqldrv.php */
