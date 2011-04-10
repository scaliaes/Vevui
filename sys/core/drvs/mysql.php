<?php

class MySQL extends DBDrv
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
			case DBDrv::SQL_RAW:
				return $this->_raw();
			case DBDrv::SQL_INSERT:
				return $this->_insert();
			case DBDrv::SQL_SELECT:
				return $this->_select();
			case DBDrv::SQL_UPDATE:
				return $this->_update();
			case DBDrv::SQL_DELETE:
				return $this->_delete();
			default:
				$this->_raise_error("Unknown query type {$this->_type}.");
		}
	}

	function exec()
	{
		$query = $this->string();

		$q = @mysql_unbuffered_query($query, $this->_connection) or $this->_raise_error(mysql_error());
		$res = array();
		while($row = mysql_fetch_assoc($q)) $res[] = $row;

		mysql_free_result($q);
		return $res;
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
		$fields = is_array(reset($this->_fields))?$this->_fields:array($this->_fields);

		$query .= '(`' . implode('`, `', array_keys($fields[0])) . '`) VALUES (';
		$rows = array();
		foreach($fields as $values)
		{
			$rows[] = implode(',', array_map(array($this, 'escape'), $values));
		}

		$query .= implode('), (', $rows).');';
		return $query;
	}

	private function _select()
	{
		function _parse_conds($conds, $prev_table, $next_table)
		{
			if (is_string($conds))
			{
				return $conds;
			}
			if (!is_array(current($conds))) $conds = array($conds);

			$and = '';
			$query = '';
			foreach($conds as $cond)
			{
				$comp = array_key_exists(2, $cond)?$cond[2]:'=';
				$query .= $and.$prev_table.'.'.$cond[0].$comp.$next_table.'.'.$cond[1];
				$and = ' AND ';
			}
			return $query;
		}

		$query = 'SELECT ';
		$fields = $this->_fields;
		$query .= is_string($fields)?$fields: ($fields?implode($fields, ','):'*');

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
					case Mdl::JOIN:
					case Mdl::INNER_JOIN:
						$query .= ' JOIN '.$table.' ON '._parse_conds($conds, $prev_table, $table);
						break;
					case Mdl::NATURAL_JOIN:
						$query .= ' NATURAL JOIN '.$table;
						break;
					case Mdl::LEFT_JOIN:
						$query .= ' LEFT JOIN '.$table.' ON '._parse_conds($conds, $prev_table, $table);
						break;
					case Mdl::RIGHT_JOIN:
						$query .= ' RIGHT JOIN '.$table.' ON '._parse_conds($conds, $prev_table, $table);
						break;
					case Mdl::LEFT_OUTER_JOIN:
						$query .= ' LEFT OUTER JOIN '.$table.' ON '._parse_conds($conds, $prev_table, $table);
						break;
					case Mdl::RIGHT_OUTER_JOIN:
						$query .= ' RIGHT OUTER JOIN '.$table.' ON '._parse_conds($conds, $prev_table, $table);
						break;
					case Mdl::FULL_OUTER_JOIN:
						$query .= ' FULL OUTER JOIN '.$table.' ON '._parse_conds($conds, $prev_table, $table);
						// Error? Unsupported in MySQL
						break;
					case Mdl::CROSS_JOIN:
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
			$where = array();
			foreach($this->_conds as $cond)
			{
				$comp = array_key_exists(2, $cond)?$cond[2]:'=';
				$value = is_string($cond[1])?'"'.mysql_real_escape_string($cond[1], $this->_connection).'"':$cond[1];	// Prevent SQL injection.
				$where[] = $cond[0].' '.$comp.' '.$value;
			}
			if ($where) $query .= ' WHERE '.implode($where, ' AND ');
		}

		if (is_numeric($this->_limit))	// LIMIT statement.
		{
			$query .= ' LIMIT ';
			if (is_numeric($this->_offset))
				$query .= $this->_offset.', ';
			$query .= $this->_limit;
		}

		return $query;
	}

	private function _update()
	{
		$query = 'UPDATE '.$this->_table;
		$fields = is_array(reset($this->_fields))?$this->_fields:array($this->_fields);

		$query .= '(`' . implode('`, `', array_keys($fields[0])) . '`) VALUES (';
		$rows = array();
		foreach($fields as $values)
		{
			$row = array();
			foreach($values as $value)
			{
				$row[] = is_string($value)?'"'.mysql_real_escape_string($value, $this->_connection).'"':$value;	// Prevent SQL injection.
			}
			$rows[] = implode(',', $row);
		}

		$query .= implode('), (', $rows).');';
		return $query;
	}

	private function _delete()
	{
		$query = 'DELETE FROM '.$this->_table;

		if ($this->_conds)	// WHERE statement.
		{
			
		}

		if ($this->_order)	// ORDER BY statement.
		{
			$query .= ' ORDER BY ';
			$ords = array();
			foreach($this->_order as $order)
			{
				$elem = $order[0];
				switch($order[1])
				{
					case Mdl::ORDER_BY_ASC:
						// Do nothing, default ORDER BY.
						break;
					case Mdl::ORDER_BY_DESC:
						$elem .= ' DESC';
						break;
					default:
						// Error.
				}
				$ords[] = $elem;
			}
			$query .= implode(',', $ords);
		}

		if (is_numeric($this->_limit))	// LIMIT statement.
		{
			$query .= ' LIMIT ';
			if (is_numeric($this->_offset)) $query .= $this->_offset.', ';
			$query .= $this->_limit;
		}

		return $query;
	}
}

/* End of file sys/core/drvs/mysql.php */