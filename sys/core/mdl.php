<?php

class Mdl
{
	private $_drv;

	function __construct($db_index = null)
	{
		global $config;

		if (null === $db_index)
		{
			reset($config['db']);
			list($db_config_key, $db_config_value) = each($config['db']);
		}
		else
		{
			$db_config_key = $db_index;
			$db_config_value = $config['db'][$db_index];
		}

		if (array_key_exists('_drv', $db_config_value))
		{
			$this->_drv = $db_config_value['_drv'];
		}
		else
		{
			$drv = $db_config_value['drv'];
			require_once(SYS_PATH.'/core/dbdrv.php');
			require(SYS_PATH.'/core/drvs/'.$drv.'.php');
			$this->_drv = $config['db'][$db_config_key]['_drv'] = new $drv($db_config_value);
		}
	}

	function __get($name)
	{
		$this->_drv->new_query($name);
		return $this->_drv;
	}
}

/* End of file sys/core/mdl.php */