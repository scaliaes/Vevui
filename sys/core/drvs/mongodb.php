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

class Drv_MongoDB extends Drv implements Iterator
{
	const DRV_UNDEFINED = 0;
	const DRV_RAW = 1;
	const DRV_INSERT = 2;
	const DRV_SELECT = 3;
	const DRV_UPDATE = 4;
	const DRV_DELETE = 5;
	const DRV_MAPREDUCE = 6;

	private $_type;
	private $_connection;
	private $_collection_name;

	private $_documents;
	private $_conditions;
	private $_fields;
	private $_unselected_fields;

	private $_map;
	private $_reduce;

	private $_as_object;

	private $_db;
	private $_db_name;
	private $_collections;
	private $_current_collection;

	private $_modifiers;

	private $_multiple;
	private $_safe;
	private $_fsync;

	private $_tailable;
	private $_tailable_wait;
	private $_tailable_max_life;
	private $_tailable_start;

	private $_current_query;

	private $_in_debug;

	public static function _install(&$extensions, &$files, &$directories)
	{
		$extensions = array('mongo' => TRUE);
	}

	function __construct($db_config, $installation_data = NULL)
	{
		parent::__construct($db_config, $installation_data);

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
			$this->_raise_error($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
			return;
		}

		$core = & Vevui::get();
		$this->_in_debug = $core->e->app->debug;
	}

	function new_query($name)
	{
		$this->_type = self::DRV_UNDEFINED;
		$this->_collection_name = $name;
		$this->_documents = $this->_conditions = $this->_fields = $this->_unselected_fields = NULL;
		$this->_map = $this->_reduce = NULL;
		$this->_as_object = FALSE;

		$this->_modifiers = array();
		$this->_multiple = TRUE;
		$this->_safe = $this->_fsync = $this->_tailable = FALSE;
		$this->_tailable_wait = $this->_tailable_max_life = $this->_tailable_start = NULL;
		$this->_current_query = NULL;

		return $this;
	}

	function insert($fields)
	{
		$this->_type = self::DRV_INSERT;
		$this->_documents = $fields;
		return $this;
	}

	function select($fields = NULL, $unselected_fields = NULL)
	{
		$this->_type = self::DRV_SELECT;
		$this->_fields = $fields;
		$this->_unselected_fields = $unselected_fields;
		return $this;
	}

	function update($fields = array())
	{
		$this->_type = self::DRV_UPDATE;
		$this->_fields = $fields;
		return $this;
	}

	function delete()
	{
		$this->_type = self::DRV_DELETE;
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
				'null' => array($this, 'create_null'),

				'affected_documents' => array($this, 'affected_documents')
			);
		return $funcs;
	}

	function safe()
	{
		$this->_safe = TRUE;
		return $this;
	}

	function fsync()
	{
		$this->_fsync = TRUE;
		return $this;
	}

	private function _exec()
	{
		$db = & $this->_collections[$this->_db_name];
		if (array_key_exists($this->_collection_name, $db))
		{
			$this->_current_collection = & $db[$this->_collection_name];
		}
		else
		{
			$this->_current_collection = & $this->_db->{$this->_collection_name};
			$this->_collections[$this->_db_name][$this->_collection_name] = & $this->_current_collection;
		}

		if ($this->_in_debug)
		{
			$this->safe();
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
					$this->_raise_error(0, 'Unknown query type '.$this->_type, __FILE__, __LINE__);
			}
		}
		catch (MongoException $e)
		{
			$this->_raise_error($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
		}

		return NULL;
	}

	function exec()
	{
		$result = $this->_exec();
		if (TRUE === $result)
		{
			return TRUE;
		}
		else if ($result instanceof MongoCursor)
		{
			if ($this->_as_object)
			{
				$ret = array();
				foreach($result as $doc)
				{
					$ret[] = $this->_to_object($doc);
				}
			}
			else
			{
				$ret = iterator_to_array($result);
			}
			return $ret;
		}

		return $this->_as_object?$this->_to_object($result):$result;
	}

	function exec_one()
	{
		$this->_multiple = FALSE;
		return $this->exec();
	}

	private function _insert()
	{
		$options = array();
		if ($this->_safe) $options['safe'] = TRUE;
		if ($this->_fsync) $options['fsync'] = TRUE;

		if ($this->_multiple)
		{
			$this->_current_collection->batchInsert($this->_documents, $options);
		}
		else
		{
			$this->_current_collection->insert($this->_documents, $options);
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

	private function _parse_where($conditions)
	{
		if (NULL === $conditions) return array();
		$where = array();
		foreach($conditions as $cond)
		{
			$field = (string) $cond[0];
			$values = $cond[1];
			$operator = $cond[2];
			if (NULL === $operator)
			{
				$where[$field] = $values;
				continue;
			}
			switch($operator)
			{
				case '=':
					$where[$field] = $values;
					break;
				case '!=':
					$where[$field] = array('$ne'=>$values);
					break;
				case '<':
					$where[$field] = array('$lt'=>$values);
					break;
				case '<=':
					$where[$field] = array('$lte'=>$values);
					break;
				case '>':
					$where[$field] = array('$gt'=>$values);
					break;
				case '>=':
					$where[$field] = array('$gte'=>$values);
					break;
				case 'all':
					$where[$field] = array('$all'=>$values);
					break;
				case 'exists':
					$where[$field] = array('$exists'=>$values);
					break;
				case 'in':
					$where[$field] = array('$in'=>$values);
					break;
				case 'nin':
					$where[$field] = array('$nin'=>$values);
					break;
			}
		}
		return $where;
	}

	private function _select()
	{
		$conds = $this->_parse_where($this->_conditions);
		$fields = $this->_fields?$this->_fields:array();
		$fields = is_string($fields)?array_map('trim', explode(',', $fields)):$fields;
		$unfields = $this->_unselected_fields?$this->_unselected_fields:array();

		$selected_fields = array();
		foreach($unfields as $field)
		{
			$selected_fields[$field] = 0;
		}
		foreach($fields as $field)
		{
			$selected_fields[$field] = 1;
		}

		if ($this->_multiple)
		{
			$result = $this->_current_collection->find($conds, $selected_fields);
			if ($this->_tailable) $result->tailable();
		}
		else
		{
			$result = $this->_current_collection->findOne($conds, $selected_fields);
		}

		return $result;
	}

	private function _update()
	{
		$options = array();
		if ($this->_safe) $options['safe'] = TRUE;
		if ($this->_fsync) $options['fsync'] = TRUE;
		$options['multiple'] = (bool) $this->_multiple;

		$fields = $this->_fields?array('$set'=>$this->_fields):array();
		$fields += $this->_modifiers;
		$this->_current_collection->update($this->_parse_where($this->_conditions), $fields, $options);
		return TRUE;
	}

	private function _delete()
	{
		$options = array();
		if ($this->_safe) $options['safe'] = TRUE;
		if ($this->_fsync) $options['fsync'] = TRUE;
		$options['justOne'] = (bool) (!$this->_multiple);
		$this->_current_collection->remove($this->_parse_where($this->_conditions), $options);
		return TRUE;
	}

	private function _mapreduce()
	{
		try
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
		}
		catch (MongoException $e)
		{
			$this->_raise_error($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
			return NULL;
		}

		if (!$q['ok'])
		{
			return $q['results'];
		}

		$this->_raise_error(0, $q['errmsg'], __FILE__, __LINE__);
		return NULL;
	}

	function affected_documents()
	{
		try
		{
			$q = $this->_db->lastError();
		}
		catch (MongoException $e)
		{
			$this->_raise_error($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
			return NULL;
		}

		if ($q['ok'])
		{
			return $q['n'];
		}

		$this->_raise_error(0, $q['err'], __FILE__, __LINE__);
		return NULL;
	}

	function tail($max_life_millis = NULL, $milliwait = 1e3)
	{
		$this->_tailable = TRUE;
		$this->_tailable_max_life = $max_life_millis;
		$this->_tailable_wait = $milliwait;
		return $this;
	}

	// Modifiers
	function inc($key, $by = 1)
	{
		$this->_modifiers['$inc'][(string)$key] = ctype_digit((string)$by)?(int)$by:(float)$by;
		return $this;
	}

	function dec($key, $by = 1)
	{
		$this->_modifiers['$inc'][(string)$key] = - (ctype_digit((string)$by)?(int)$by:(float)$by);
		return $this;
	}

	function push($key, $value)
	{
		$this->_modifiers['$push'][(string)$key] = $value;
		return $this;
	}

	function __call($name, $arguments)
	{
		switch ($name)
		{
			case 'unset':
				foreach ($arguments as $arg)
				{
					$this->_modifiers['$unset'][(string)$arg] = 1;
				}
				break;
		}
		return $this;
	}

	// Type wrappers.
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

	// Iterator
	function rewind()
	{
		if (NULL === $this->_current_query)
		{
			$this->_current_query = $this->_exec();
			if ($this->_tailable)
			{
				$this->_tailable_start = microtime(TRUE);
			}
		}

		if ($this->_current_query instanceof MongoCursor)
		{
			$this->_current_query->rewind();
		}
	}

	function valid()
	{
		if ($this->_current_query instanceof MongoCursor)
		{
			if ($this->_current_query->valid()) return TRUE;
			if ($this->_tailable)
			{
				while(!$this->_current_query->hasNext())
				{
					if ($this->_current_query->dead())
					{
						$this->_current_query = NULL;
						return FALSE;
					}
					if (NULL !== $this->_tailable_max_life)
					{
						$elapsed = (microtime(TRUE)-$this->_tailable_start)*1000;
						if ($elapsed > $this->_tailable_max_life)
						{
							$this->_current_query = NULL;
							return FALSE;
						}
					}
					usleep($this->_tailable_wait*1000);
				}
				$this->next();
				return TRUE;
			}
		}
		return FALSE;
	}

	function current()
	{
		return $this->_current_query->current();
	}

	function key()
	{
		return $this->_current_query->key();
	}

	function next()
	{
		if (NULL !== $this->_current_query)
		{
			$this->_current_query->next();
		}
	}
}

/* End of file sys/core/drvs/mongodb.php */
