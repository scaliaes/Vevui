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

class Mdl
{
	const INNER_JOIN = 0; const JOIN = 0;	// They're just aliases.
	const NATURAL_JOIN = 1;
	const LEFT_JOIN = 2;
	const RIGHT_JOIN = 3;
	const LEFT_OUTER_JOIN = 4;
	const RIGHT_OUTER_JOIN = 5;
	const FULL_OUTER_JOIN = 6;
	const CROSS_JOIN = 7;

	const ORDER_BY_ASC = 0;
	const ORDER_BY_DESC = 1;

	static $config;
	static $default_schema;
	static $controller;
	private static $_drivers = array();

	private $_drv;

	function __construct($db_index = null)
	{
		if (null === $db_index)
		{
			$db_config_key = self::$default_schema;
			$db_config_value = self::$config[self::$default_schema];
		}
		else
		{
			$db_config_key = $db_index;
			$db_config_value = self::$config[$db_index];
		}

		if (array_key_exists($db_config_key, self::$_drivers))
		{
			$this->_drv = self::$_drivers[$db_config_key];
		}
		else
		{
			$drv = $db_config_value['drv'];
			require_once(SYS_PATH.'/core/dbdrv.php');
			require(SYS_PATH.'/core/drvs/'.$drv.'.php');
			$this->_drv = self::$_drivers[$db_config_key] = new $drv($db_config_value);
		}
	}

	function __get($name)
	{
		$this->_drv->new_query($name);
		return $this->_drv;
	}

	protected function call()
	{
		return self::$controller;
	}
}

/* End of file sys/core/mdl.php */
