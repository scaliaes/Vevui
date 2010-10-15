<?php

class MySQL extends DBDrv
{
	function __construct($db_config)
	{
		$this->_connection = mysql_connect($db_config['host'],
					$db_config['user'], $db_config['pass']);
		mysql_select_db($db_config['db']);
	}

	function exec()
	{
		$query = 'SELECT ';	// For now, only select is supported.
		$fields = $this->_fields;
		$query .= is_string($fields)?$fields: ($fields?implode($fields, ','):'*');

		$query .= ' FROM '.$this->_table;	// FROM statement.

		if($this->_joins)	// JOINs statement.
		{
			$prev_table = $this->_table;
			foreach($this->_joins as $join)	// JOINs statement.
			{
				switch (strtolower($join['type']))
				{
					case 'natural':
						$query .= ' NATURAL JOIN '.$join['table'];
						break;
					case 'left':
						$query .= ' LEFT JOIN '.$join['table'].' ON ';
						foreach($join['conds'] as $cond)
						{
							$comp = array_key_exists(2, $cond)?$cond[2]:'=';
							$query .= $prev_table.'.'.$cond[0].$comp.$join['table'].'.'.$cond[1];
						}
						break;
					case 'right':
						$query .= ' RIGHT JOIN '.$join['table'].' ON ';
						foreach($join['conds'] as $cond)
						{
							$comp = array_key_exists(2, $cond)?$cond[2]:'=';
							$query .= $prev_table.'.'.$cond[0].$comp.$join['table'].'.'.$cond[1];
						}
						break;
					default:
						$query .= ' JOIN '.$join['table'].' ON ';
						$conds = $join['conds'];
						if (is_array(current($conds)))
						{
							$and = '';
							foreach($conds as $cond)
							{
								$comp = array_key_exists(2, $cond)?$cond[2]:'=';
								$query .= $and.$prev_table.'.'.$cond[0].$comp.$join['table'].'.'.$cond[1];
								$and = ' AND ';
							}
						}
						else
						{
							$comp = array_key_exists(2, $conds)?$conds[2]:'=';
							$query .= $prev_table.'.'.$conds[0].$comp.$join['table'].'.'.$conds[1];
						}
				}
				$prev_table = $join['table'];
			}
		}

		if ($this->_conds)	// WHERE statement.
		{
			$where = array();
			foreach($this->_conds as $cond)
			{
				$comp = array_key_exists(2, $cond)?$cond[2]:'=';
				if (is_string($cond[1]))	// Prevent SQL injection.
					$value = '"'.mysql_real_escape_string($cond[1], $this->_connection).'"';
				else
					$value = $cond[1];
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
		print_r('mysql>> '.$query.'<br/><br/>');
		$q = mysql_unbuffered_query($query, $this->_connection);
		$res = array();
		while($row = mysql_fetch_assoc($q)) $res[] = $row;

		mysql_free_result($q);
		return $res;
	}
}

/* End of file sys/core/drvs/mysql.php */