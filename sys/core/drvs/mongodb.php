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

class MongoDB extends Drv
{
	private $_db;

	function __construct($dbname)
	{
		try
		{
			$this->_connection = new Mongo('mongodb://'.$db_config['host']);
			$this->_db = $this->_m->{$db_config['db']};

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
		if (1 == count($this->_documents))
		{
			$this->_collection->insert(reset($this->_documents));
		}
		else
		{
			$this->_collection->batchInsert($this->_documents);
		}
		return TRUE;
	}

	function _select()
	{
		$conds = $this->_conditions?$this->_conditions:array();
		$fields = $this->_fields?$this->_fields:array();
		return iterator_to_array($this->_collection->find($conds, $fields));
	}

	function _update()
	{
		$this->_collection->update($this->_conditions, $this->_fields);
		return TRUE;
	}

	function _delete()
	{
		$this->_collection->remove($this->_conditions);
		return TRUE;
	}
}

/* End of file sys/core/mongodb.php */
