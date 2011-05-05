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

class MySQL extends SQLDrv
{
	function __construct($db_config)
	{
		$this->_connection = @mysql_connect($db_config['host'],
					$db_config['user'], $db_config['pass']) or $this->_raise_error(mysql_error());
		@mysql_set_charset($db_config['char'], $this->_connection) or $this->_raise_error(mysql_error());
		@mysql_select_db($db_config['db'], $this->_connection) or $this->_raise_error(mysql_error());
	}

	function escape($mixed)
	{
		switch(TRUE)
		{
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
				$this->_raise_error("Unknown query type {$this->_type}.");
		}
	}

	function exec()
	{
		$query = $this->string();
		$q = @mysql_unbuffered_query($query, $this->_connection) or $this->_raise_error(@mysql_errno($this->_connection).': '.@mysql_error($this->_connection));
		
		if(TRUE === $q)
			return TRUE;

		$res = array();
		while($row = mysql_fetch_assoc($q)) $res[] = $row;

		mysql_free_result($q);
		return $res;
	}
	
	function last_id()
	{
		return mysql_insert_id($this->_connection);
	}
	
	function affected_rows()
	{
		return mysql_affected_rows();
	}

	private function _raw()
	{
		$query = $this->_raw_query;
		foreach($this->_protect as $key=>$param)
		{
			$query = str_replace($key, $this->escape($param), $query);
		}
		return $query;
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
			if ($values)
			{
				if (is_array($values))
				{
					$comp = $operator?$comp:' IN ';
					$where[] = $field.$comp.'('.implode(', ', $this->escape($values)).')';
				}
				else
				{
					$comp = $operator?$comp:'=';
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
			if ($values)
			{
				if (is_array($values))
				{
					$comp = $operator?$comp:' IN ';
					$where[] = $field.$comp.'('.implode(', ', $this->escape($values)).')';
				}
				else
				{
					$comp = $operator?$comp:'=';
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

	private function _parse_conds($conds, $prev_table, $next_table)
	{
		$query = array();
		foreach($conds as $cond)
		{
			$comp = $cond[2]?$cond[2]:'=';
			$query[] = $and.$prev_table.'.'.$cond[0].$comp.$next_table.'.'.$cond[1];
		}
		return implode(' AND ', $query);
	}

	private function _select()
	{
		$query = 'SELECT ';
		$fields = $this->_fields;
		$query .= $fields? (is_string($fields)?$fields:implode(',', $fields)) : '*';

		$query .= ' FROM '.$this->_table;	// FROM statement.

		if($this->_joins)	// JOINs statements.
		{
			$prev_table = $this->_table;
			foreach($this->_joins as $join)
			{
				$table = $join[0];
				$conds = $join[2];
				switch (strtolower($join[1]))
				{
					case self::JOIN:
						$query .= ' JOIN '.$table.' ON '._parse_conds($conds, $prev_table, $table);
						break;
					case self::NATURAL_JOIN:
						$query .= ' NATURAL JOIN '.$table;
						break;
					case self::LEFT_JOIN:
						$query .= ' LEFT JOIN '.$table.' ON '._parse_conds($conds, $prev_table, $table);
						break;
					case self::RIGHT_JOIN:
						$query .= ' RIGHT JOIN '.$table.' ON '._parse_conds($conds, $prev_table, $table);
						break;
					case self::FULL_OUTER_JOIN:
						$query .= ' FULL OUTER JOIN '.$table.' ON '._parse_conds($conds, $prev_table, $table);
						break;
					case self::CROSS_JOIN:
						$query .= ', '.$table;
						break;
					default:
						// Error
				}
				$prev_table = $table;
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
}

/* End of file sys/core/drvs/mysql.php */
