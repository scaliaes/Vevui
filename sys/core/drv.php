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

abstract class Drv
{
	const DRV_UNDEFINED = 0;
	const DRV_RAW = 1;
	const DRV_INSERT = 2;
	const DRV_SELECT = 3;
	const DRV_UPDATE = 4;
	const DRV_DELETE = 5;
	const DRV_MAPREDUCE = 6;

	protected $_type;
	protected $_connection;
	protected $_collection_name;

	protected $_documents;
	protected $_conditions;
	protected $_fields;

	protected $_multi_insert;

	protected $_map;
	protected $_reduce;

	protected $_as_object;

	function __construct() {}

	function new_query($name)
	{
		$this->_type = self::DRV_UNDEFINED;
		$this->_collection_name = $name;
		$this->_multi_insert = FALSE;
		$this->_documents = $this->_conditions = $this->_fields = NULL;
		$this->_map = $this->_reduce = NULL;
		$this->_as_object = FALSE;
		return $this;
	}

	function insert($fields, $multi = FALSE)
	{
		$this->_type = self::DRV_INSERT;
		$this->_documents = $fields;
		$this->_multi_insert = $multi;
		return $this;
	}

	function select($fields = NULL)
	{
		$this->_type = self::DRV_SELECT;
		$this->_fields = $fields;
		$this->_conditions = $conditions;
		return $this;
	}

	function update($fields, $conditions)
	{
		$this->_type = self::DRV_UPDATE;
		$this->_fields = $fields;
		$this->_conditions = $conditions;
		return $this;
	}

	function delete($conditions = NULL)
	{
		$this->_type = self::DRV_DELETE;
		$this->_conditions = $conditions;
		return $this;
	}

	function where($field, $value = NULL, $operator = NULL)
	{
		$this->_conditions[] = array($field, $value, $operator);
		return $this;
	}

	function mapreduce($map, $reduce)
	{
		$this->_type = self::DRV_MAPREDUCE;
		$this->_map = $map;
		$this->_reduce = $reduce;
		return $this;
	}

	function as_obj()
	{
		$this->_as_object = TRUE;
		return $this;
	}

	protected function _raise_error($error_string)
	{
		$core = & Vevui::get();

		// TODO: store error_string in error database
		if($core->e->app->debug)
			echo '<p>'.$error_string.'</p>';

		$core->internal_error();
	}

	abstract function register_functions();
	abstract function exec();
	abstract function exec_one();
}

/* End of file sys/core/drv.php */
