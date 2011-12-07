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

class Drv_MongoDB extends Drv
{
	private $_db;
	private $_db_name;
	private $_collections;
	private $_current_collection;

	function __construct($db_config)
	{
		parent::__construct($db_config);

		try
		{
			$this->_connection = new Mongo('mongodb://'.$db_config->host);
			$this->_db_name = $db_config->db;
			$this->_db = $this->_connection->{$this->_db_name};
			$this->_collections = array($this->_db_name => array());

			if (property_exists($db_config, 'user'))
			{
				$this->_db->authenticate($db_config->user, $db_config->pass);
			}
		}
		catch (MongoException $e)
		{
			$this->_raise_error($e);
		}
	}

	function register_functions()
	{
		$funcs = array
			(
				'id' => array($this, 'create_id'),
				'code' => array($this, 'create_code'),
				'date' => array($this, 'create_date'),
				'regex' => array($this, 'create_regex'),
				'bin' => array($this, 'create_bin_data'),
				'int32' => array($this, 'create_int32'),
				'int64' => array($this, 'create_int64'),
				'dbref' => array($this, 'create_dbref'),
				'minkey' => array($this, 'create_min_key'),
				'maxkey' => array($this, 'create_max_key'),
				'timestamp' => array($this, 'create_timestamp'),

				'int' => array($this, 'create_int'),
				'string' => array($this, 'create_string'),
				'bool' => array($this, 'create_bool'),
				'float' => array($this, 'create_float'),
				'null' => array($this, 'create_null')
			);
		return $funcs;
	}

	function exec()
	{
		$db = & $this->_collections[$this->_db_name];
		if (array_key_exists($this->_collection_name, $db))
		{
			$this->_current_collection = & $db[$this->_collection_name];
		}
		else
		{
			$this->_current_collection = $this->_collections[$this->_db_name][$this->_collection_name] = $this->_db->{$this->_collection_name};
		}

		try
		{
			switch($this->_type)
			{
				case self::DRV_INSERT:
					return $this->_insert();
				case self::DRV_SELECT:
					return $this->_select();
				case self::DRV_UPDATE:
					return $this->_update();
				case self::DRV_DELETE:
					return $this->_delete();
				case self::DRV_MAPREDUCE:
					return $this->_mapreduce();
				default:
					$this->_raise_error('Unknown query type '.$this->_type);
			}
		}
		catch (MongoException $e)
		{
			$this->_raise_error($e);
		}
	}

	function exec_one()
	{
		$db = & $this->_collections[$this->_db_name];
		if (array_key_exists($this->_collection_name, $db))
		{
			$this->_current_collection = & $db[$this->_collection_name];
		}
		else
		{
			$this->_current_collection = $this->_collections[$this->_db_name][$this->_collection_name] = $this->_db->{$this->_collection_name};
		}

		try
		{
			switch($this->_type)
			{
				case self::DRV_INSERT:
					return $this->_insert();
				case self::DRV_SELECT:
					return $this->_select_one();
				case self::DRV_UPDATE:
					return $this->_update();
				case self::DRV_DELETE:
					return $this->_delete();
				case self::DRV_MAPREDUCE:
					return $this->_mapreduce();
				default:
					$this->_raise_error('Unknown query type '.$this->_type);
			}
		}
		catch (MongoException $e)
		{
			$this->_raise_error($e);
		}
	}

	private function _insert()
	{
		if ($this->_multi_insert)
		{
			$this->_current_collection->batchInsert($this->_documents);
		}
		else
		{
			$this->_current_collection->insert(reset($this->_documents));
		}
		return TRUE;
	}

	private function _to_object($array)
	{
		if ( (!is_array($array)) && (!is_object($array)) ) return $array;

		$ret = new stdClass();
		foreach($array as $key => $value)
		{
			$ret->{$key} = $this->_to_object($value);
		}
		return $ret;
	}

	private function _select()
	{
		$conds = $this->_conditions?$this->_conditions:array();
		$fields = $this->_fields?$this->_fields:array();

		$res = $this->_current_collection->find($conds, $fields);
		if ($this->_as_object)
		{
			$ret = array();
			foreach($res as $doc)
			{
				$ret[] = $this->_to_object($doc);
			}
		}
		else
		{
			$ret = iterator_to_array($res);
		}
		return $ret;
	}

	private function _select_one()
	{
		$conds = $this->_conditions?$this->_conditions:array();
		$fields = $this->_fields?$this->_fields:array();

		$res = $this->_current_collection->findOne($conds, $fields);
		if ($this->_as_object)
		{
			return $this->_to_object($res);
		}
		else
		{
			return $res;
		}
	}

	private function _update()
	{
		$this->_current_collection->update($this->_conditions, $this->_fields);
		return TRUE;
	}

	private function _delete()
	{
		$this->_current_collection->remove($this->_conditions);
		return TRUE;
	}

	private function _mapreduce()
	{
		$q = $this->_db->command(array
			(
				'mapreduce' => $this->_collection_name,
				'map' => new MongoCode($this->_map),
				'reduce' => new MongoCode($this->_reduce),
				'out' => array
					(
						'inline' => 1
					)
			));
		if (!$q['ok'])
			$this->_raise_error($q['errmsg']);
		return $q['results'];
	}

	function create_id($id = NULL)
	{
		return new MongoId($id);
	}

	function create_code($code, $scope = array())
	{
		return new MongoCode($code, $scope);
	}

	function create_date($sec = NULL, $usec = 0)
	{
		return new MongoDate(NULL===$sec?time():$sec, $usec);
	}

	function create_regex($regex)
	{
		return new MongoRegex($regex);
	}

	function create_bin_data($data, $type = 2)
	{
		return new MongoBinData($data, $type);
	}

	function create_int32($value)
	{
		return new MongoInt32($value);
	}

	function create_int64($value)
	{
		return new MongoInt64($value);
	}

	function create_dbref($collection, $id, $database = NULL)
	{
		return new MongoDBRef($collection, $id, $database);
	}

	function create_min_key()
	{
		return new MongoMinKey();
	}

	function create_max_key()
	{
		return new MongoMaxKey();
	}

	function create_timestamp($sec = NULL, $usec = 0)
	{
		return new MongoTimestamp(NULL===$sec?time():$sec, $usec);
	}

	function create_int($value)
	{
		return (int) $value;
	}

	function create_string($value)
	{
		return (string) $value;
	}

	function create_bool($value)
	{
		return (bool) $value;
	}

	function create_float($value)
	{
		return (float) $value;
	}

	function create_null()
	{
		return NULL;
	}
}

/* End of file sys/core/mongodb.php */
