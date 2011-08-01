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
		try
		{
			$this->_connection = new Mongo('mongodb://'.$db_config['host']);
			$this->_db_name = $db_config['db'];
			$this->_db = $this->_connection->{$this->_db_name};
			$this->_collections = array($this->_db_name => array());

			if (array_key_exists('user', $db_config))
			{
				$this->_db->authenticate($db_config['user'], $db_config['pass']);
			}
		}
		catch (MongoException $e)
		{
			$this->_raise_error($e);
		}
	}

	function exec()
	{
		$db = $this->_collections[$this->_db_name];
		if (array_key_exists($this->_collection_name, $db))
		{
			$this->_current_collection = $db[$this->_collection_name];
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

	function _insert()
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

	function _select()
	{
		$conds = $this->_conditions?$this->_conditions:array();
		$fields = $this->_fields?$this->_fields:array();
		return iterator_to_array($this->_current_collection->find($conds, $fields));
	}

	function _update()
	{
		$this->_current_collection->update($this->_conditions, $this->_fields);
		return TRUE;
	}

	function _delete()
	{
		$this->_current_collection->remove($this->_conditions);
		return TRUE;
	}

	function _mapreduce()
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
}

/* End of file sys/core/mongodb.php */
