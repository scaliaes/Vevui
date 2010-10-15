<?php

class MongoDB extends DBDrv
{
	function __construct($db_config)
	{
		$connection_string = 'mongodb://';
		if ($db_config['user'])
			$connection_string .= $db_config['user']
				.':'.$db_config['pass'].'@';
		$connection_string .= $db_config['host'];
		$this->_connection = new Mongo($connection_string,
				$db_config['opts']);
	}

	function query($query)
	{
		echo '<pre>';
		return print_r($this->_connection->listDBs());
	}
}

/* End of file sys/core/drvs/mongodb.php */