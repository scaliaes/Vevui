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
	private static $_drv_loaded = FALSE;
	private $_core;

	private $_drv;
	private $_config_key;

	function __construct($db_index = NULL, $installation_data = NULL)
	{
		$this->_core = & Vevui::get();

		if ($installation_data && array_key_exists('missing', $installation_data))
		{
			$this->_core->missing_component(get_class($this), $installation_data['missing']);
		}

		$config = $this->_core->e->db;
		if (NULL === $db_index)
		{
			$db_config_key = $config->default_schema;
			$db_config_value = $config->databases->{$db_config_key};
		}
		else
		{
			$db_config_key = $db_index;
			$db_config_value = $config->databases->{$db_index};
		}

		if (array_key_exists($db_config_key, self::$_drivers))
		{
			$this->_drv = & self::$_drivers[$db_config_key]['drv'];
		}
		else
		{
			$drv = $db_config_value->drv;
			if (!self::$_drv_loaded)
			{
				require(SYS_PATH.'/core/drv.php');
				self::$_drv_loaded = TRUE;
			}
			require(SYS_PATH.'/core/drvs/'.$drv.'.php');

			$data = Vevui::get_installation_data();
			$data = array_key_exists('drv', $data) && array_key_exists($drv, $data['drv']) ? $data['drv'][$drv] : NULL;

			$class = 'Drv_'.$drv;
			$this->_drv = new $class($db_config_value, $data);

			self::$_drivers[$db_config_key]['drv'] = & $this->_drv;
			self::$_drivers[$db_config_key]['functions'] = $this->_drv->register_functions();
		}
		$this->_config_key = $db_config_key;
	}

	function __get($name)
	{
		return $this->_drv->new_query($name);
	}

	function disable_errors()
	{
		$this->_core->disable_errors();
	}

	function enable_errors()
	{
		$this->_core->enable_errors();
	}

	function __call($name, $arguments)
	{
		return call_user_func_array(self::$_drivers[$this->_config_key]['functions'][$name], $arguments);
	}
}

/* End of file sys/core/mdl.php */
