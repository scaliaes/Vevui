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

class Drv_MySQL extends SQLDrv
{
	private $_connection;
	private $_current_query;
	private $_current_row;
	private $_current_query_count;

	public static function _install(&$extensions, &$files, &$directories)
	{
		$extensions = array('mysql' => TRUE);
	}

	function __construct($db_config, $installation_data = NULL)
	{
		parent::__construct($db_config, $installation_data);

		$this->_current_query = $this->_current_row = NULL;
		$this->_current_query_count = 0;
		$this->_connection = @mysql_connect($db_config->host, $db_config->user, $db_config->pass);

		if (!$this->_connection)
		{
			$this->_raise_error(0, 'Can\' connect to MySQL.', __FILE__, __LINE__);
			return;
		}

		if (!@mysql_set_charset($db_config->char, $this->_connection))
		{
			$this->_raise_error(@mysql_errno($this->_connection), @mysql_error($this->_connection), __FILE__, __LINE__);
			return;
		}

		if (!@mysql_select_db($db_config->db, $this->_connection));
		{
			$this->_raise_error(@mysql_errno($this->_connection), @mysql_error($this->_connection), __FILE__, __LINE__);
			return;
		}
	}

	function register_functions()
	{
		$funcs = array
			(
				'escape' => array($this, 'escape'),
				'string' => array($this, 'string'),
				'last_id' => array($this, 'last_id'),
				'affected_rows' => array($this, 'affected_rows')
			);
		return $funcs;
	}

	function new_query($name)
	{
		$ret = parent::new_query($name);
		if ($this->_current_row)
		{
			while($row = mysql_fetch_array($this->_current_query, MYSQL_NUM)) ;
		}
		$this->_current_query = $this->_current_row = NULL;
		$this->_current_query_count = 0;
		return $ret;
	}

	function escape($mixed)
	{
		switch(TRUE)
		{
			case NULL === $mixed:
				return 'NULL';
			case ctype_digit($mixed):
			case is_int($mixed):
				return (int)$mixed;
			case is_bool($mixed):
				return $mixed?'TRUE':'FALSE';
			case is_string($mixed):
				return '"' . mysql_real_escape_string($mixed, $this->_connection) . '"';
			case is_array($mixed):
				$ret = array();
				foreach($mixed as $key=>$value)
				{
					$ret[$key] = $this->escape($value);
				}
				return $ret;
			case is_object($mixed):
				$ret = new stdClass();
				foreach($mixed as $key=>$value)
				{
					$ret->$key = $this->escape($value);
				}
				return $ret;
			default:
				return $mixed;
		}
	}

	function string()
	{
		switch($this->_type)
		{
			case self::SQL_RAW:
				return $this->_raw();
			case self::SQL_INSERT:
				return $this->_insert();
			case self::SQL_SELECT:
				return $this->_select();
			case self::SQL_UPDATE:
				return $this->_update();
			case self::SQL_DELETE:
				return $this->_delete();
			default:
				$this->_raise_error(0, 'Unknown query type '.$this->_type, __FILE__, __LINE__);
		}
		return NULL;
	}

	private function _exec()
	{
		$query = $this->string();
		$q = @mysql_unbuffered_query($query, $this->_connection);
		if (!$q)
		{
			$this->_raise_error(@mysql_errno($this->_connection), @mysql_error($this->_connection), __FILE__, __LINE__);
			return NULL;
		}
		return $q;
	}

	function exec()
	{
		$q = $this->_exec();
		if (TRUE === $q) return TRUE;

		$res = array();
		if ($this->_as_object)
		{
			while($row = mysql_fetch_assoc($q)) $res[] = (object) $row;
		}
		else
		{
			while($row = mysql_fetch_assoc($q)) $res[] = $row;
		}

		return $res;
	}

	function exec_one()
	{
		$this->limit(1);

		$q = $this->_exec();
		if (TRUE === $q) return TRUE;

		$this->_current_query = $q;
		if ($this->_as_object)
		{
			$row = mysql_fetch_assoc($q);
			$this->_current_row = $res = $row?(object)$row:NULL;
		}
		else
		{
			$this->_current_row = $row = mysql_fetch_assoc($q);
			$res = $row?$row:NULL;
		}

		return $res;
	}

	function last_id()
	{
		return mysql_insert_id($this->_connection);
	}

	function affected_rows()
	{
		return mysql_affected_rows($this->_connection);
	}

	private function _do_raw($query, $protected)
	{
		reset($protected);
		$parts = explode(key($protected), $query);

		$remaining = array_slice($protected, 1);
		if (!$remaining) return implode(current($protected), $parts);

		$new = array();
		foreach($parts as $part)
		{
			$new[] = $this->_do_raw($part, $remaining);
		}
		return implode(current($protected), $new);
	}

	private function _raw()
	{
		return $this->_protect?$this->_do_raw($this->_raw_query, $this->escape($this->_protect)):$this->_raw_query;
	}

	private function _insert()
	{
		$query = 'INSERT INTO '.$this->_table;

		$query .= '(`' . implode('`, `', array_keys($this->_fields[0])) . '`) VALUES (';
		$rows = array();
		foreach($this->_fields as $values)
		{
			$rows[] = implode(',', array_map(array($this, 'escape'), $values));
		}

		$query .= implode('), (', $rows).');';
		return $query;
	}

	private function _parse_where($conditions)
	{
		$where = array();
		foreach($conditions as $cond)
		{
			$field = $cond[0];
			$values = $cond[1];
			$operator = $cond[2];
			if (NULL !== $values)
			{
				if (is_array($values))
				{
					$comp = $operator?$operator:' IN ';
					$where[] = $field.$comp.'('.implode(', ', $this->escape($values)).')';
				}
				else
				{
					$comp = $operator?$operator:'=';
					$where[] = $field.$comp.$this->escape($values);
				}
			}
			else	// No value, so $cond[0] is a plain string properly escaped.
			{
				$where[] = $cond[0];
			}
		}
		return $where?' WHERE '.implode(' AND ', $where):'';
	}

	private function _parse_having($conditions)
	{
		$where = array();
		foreach($conditions as $cond)
		{
			$field = $cond[0];
			$values = $cond[1];
			$operator = $cond[2];
			if (NULL !== $values)
			{
				if (is_array($values))
				{
					$comp = $operator?$operator:' IN ';
					$where[] = $field.$comp.'('.implode(', ', $this->escape($values)).')';
				}
				else
				{
					$comp = $operator?$operator:'=';
					$where[] = $field.$comp.$this->escape($values);
				}
			}
			else	// No value, so $cond[0] is a plain string properly escaped.
			{
				$where[] = $cond[0];
			}
		}
		return $where?' HAVING '.implode(' AND ', $where):'';
	}

	private function _parse_order_by($orders)
	{
		$fields = array();
		foreach($orders as $order)
		{
			$fields[] = $order[0] . ($order[1]?' ASC':' DESC');
		}
		return $fields?' ORDER BY '.implode(', ', $fields):'';
	}

	private function _parse_limit($limit, $offset)
	{
		return ' LIMIT ' . (is_numeric($offset)?$offset.', ':'') . $limit;
	}

	private function _select()
	{
		$query = 'SELECT ';
		$fields = $this->_fields;
		$query .= $fields? (is_string($fields)?$fields:implode(',', $fields)) : '*';

		$query .= ' FROM '.$this->_table;	// FROM statement.

		if($this->_joins)	// JOINs statements.
		{
			foreach($this->_joins as $join)
			{
				$table = $join[0];
				$cond = $join[2];
				switch (strtolower($join[1]))
				{
					case self::JOIN:
						$comp = $cond[2]?$cond[2]:'=';
						$query .= ' JOIN '.$table.' ON '.$cond[0].$comp.$cond[1];
						break;
					case self::NATURAL_JOIN:
						$query .= ' NATURAL JOIN '.$table;
						break;
					case self::LEFT_JOIN:
						$comp = $cond[2]?$cond[2]:'=';
						$query .= ' LEFT JOIN '.$table.' ON '.$cond[0].$comp.$cond[1];
						break;
					case self::RIGHT_JOIN:
						$comp = $cond[2]?$cond[2]:'=';
						$query .= ' RIGHT JOIN '.$table.' ON '.$cond[0].$comp.$cond[1];
						break;
					case self::FULL_OUTER_JOIN:
						$comp = $cond[2]?$cond[2]:'=';
						$query .= ' FULL OUTER JOIN '.$table.' ON '.$cond[0].$comp.$cond[1];
						break;
					case self::CROSS_JOIN:
						$query .= ', '.$table;
						break;
					default:
						// Error
				}
			}
		}

		if ($this->_conds)	// WHERE statement.
		{
			$query .= $this->_parse_where($this->_conds);
		}

		if ($this->_group)	// GROUP BY statement.
		{
			$query .= ' GROUP BY '.implode(',', $this->_group);
		}

		if ($this->_having)	// HAVING statement.
		{
			$query .= $this->_parse_having($this->_having);
		}

		if ($this->_order)	// ORDER BY statement.
		{
			$query .= $this->_parse_order_by($this->_order);
		}

		if (is_numeric($this->_limit))	// LIMIT statement.
		{
			$query .= $this->_parse_limit($this->_limit, $this->_offset);
		}

		return $query;
	}

	private function _update()
	{
		$query = 'UPDATE '.$this->_table;
		if (is_array($this->_fields))
		{
			$fields = array();
			foreach($this->_fields as $name=>$value)
			{
				$fields[] = $name.'='.$this->escape($value);
			}
			$query .= ' SET '.implode(', ', $fields);
		}
		else	// Is a string.
		{
			$query .= ' SET '.$this->_fields;
		}

		if ($this->_conds)	// WHERE statement.
		{
			$query .= $this->_parse_where($this->_conds);
		}

		if ($this->_order)	// ORDER BY statement.
		{
			$query .= $this->_parse_order_by($this->_order);
		}

		if (is_numeric($this->_limit))	// LIMIT statement.
		{
			$query .= $this->_parse_limit($this->_limit, $this->_offset);
		}

		return $query;
	}

	private function _delete()
	{
		$query = 'DELETE FROM '.$this->_table;

		if ($this->_conds)	// WHERE statement.
		{
			$query .= $this->_parse_where($this->_conds);
		}

		if ($this->_order)	// ORDER BY statement.
		{
			$query .= $this->_parse_order_by($this->_order);
		}

		if (is_numeric($this->_limit))	// LIMIT statement.
		{
			$query .= $this->_parse_limit($this->_limit, $this->_offset);
		}

		return $query;
	}

	function rewind()
	{
		if ($this->_current_query)
		{
			$this->_raise_error(0, 'Rewind not allowed here.', __FILE__, __LINE__);
			return;
		}

		$this->_current_query = $this->_exec();
		if (TRUE !== $this->_current_query)
		{
			if ($this->_as_object)
			{
				$row = mysql_fetch_assoc($this->_current_query);
				$this->_current_row = $row?(object)$row:$row;
			}
			else
			{
				$this->_current_row = mysql_fetch_assoc($this->_current_query);
			}
		}
		else
		{
			$this->_current_row = FALSE;
		}
	}

	function valid()
	{
		return (bool) $this->_current_row;
	}

	function current()
	{
		return $this->_current_row;
	}

	function key()
	{
		return $this->_current_query_count;
	}

	function next()
	{
		if ($this->_as_object)
		{
			$row = mysql_fetch_assoc($this->_current_query);
			$this->_current_row = $row?(object)$row:$row;
		}
		else
		{
			$this->_current_row = mysql_fetch_assoc($this->_current_query);
		}
		++$this->_current_query_count;
	}
}

/* End of file sys/core/drvs/mysql.php */
