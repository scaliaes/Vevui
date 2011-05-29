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
	private static $_drivers = array();
	private $_core;

	private $_drv;

	function __construct($db_index = NULL)
	{
		$this->_core = Vevui::get();

		$config = $this->_core->e->db;
		if (NULL === $db_index)
		{
			$db_config_key = $config['default_schema'];
			$db_config_value = $config['db'][$db_config_key];
		}
		else
		{
			$db_config_key = $db_index;
			$db_config_value = $config['db'][$db_index];
		}

		if (array_key_exists($db_config_key, self::$_drivers))
		{
			$this->_drv = self::$_drivers[$db_config_key];
		}
		else
		{
			$drv = $db_config_value['drv'];
			require_once(SYS_PATH.'/core/sqldrv.php');
			require(SYS_PATH.'/core/drvs/'.$drv.'.php');
			$this->_drv = self::$_drivers[$db_config_key] = new $drv($db_config_value);
		}
	}

	function __get($name)
	{
		$this->_drv->new_query($name);
		return $this->_drv;
	}
	
	protected function last_id()
	{
		return $this->_drv->last_id();
	}
	
	protected function affected_rows()
	{
		return $this->_drv->affected_rows();
	}
}

/* End of file sys/core/mdl.php */
