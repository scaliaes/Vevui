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

class Ctrl
{
	private $_haanga_loaded = FALSE;
	private $_helper_loader_loaded = FALSE;
	private $_library_loader_loaded = FALSE;
	private $_debug;
	private $_profiling;
	private $_profile_this;

	private $_cache_result = FALSE;
	private $_cache_name = NULL;
	private $_cache_params = NULL;
	private $_cache_content = '';

	function  __construct()
	{
		require(SYS_PATH.'/core/configloader.php');
		$this->e = new ConfigLoader();

		$config = $this->e->app;

		$this->_debug = $config['debug'];
		$this->_profiling = $config['profiling'];

		if (lcg_value() < $this->_profiling) // Hooray, saving data!
		{

		}
	}

	function __get($prop_name)
	{
		switch ($prop_name)
		{
			case 'm':
				require(SYS_PATH.'/core/modelloader.php');
				return $this->m = new ModelLoader($this);
			case 'h':
				if (!$this->_helper_loader_loaded)
				{
					require(SYS_PATH.'/core/helperloader.php');
					$this->_helper_loader_loaded = TRUE;
				}
				return $this->h = new HelperLoader();
			case 'l':
				if (!$this->_library_loader_loaded)
				{
					require(SYS_PATH.'/core/libraryloader.php');
					$this->_library_loader_loaded = TRUE;
				}
				return $this->l = new LibraryLoader($this);
			case 'mh':
				if (!$this->_helper_loader_loaded)
				{
					require(SYS_PATH.'/core/helperloader.php');
					$this->_helper_loader_loaded = TRUE;
				}
				return $this->mh = new HelperLoader(TRUE);
			case 'ml':
				if (!$this->_library_loader_loaded)
				{
					require(SYS_PATH.'/core/libraryloader.php');
					$this->_library_loader_loaded = TRUE;
				}
				return $this->ml = new LibraryLoader($this, TRUE);
			default:
				trigger_error('Undefined variable: '.$prop_name, E_USER_ERROR);
		}
	}

	function render($view_name, $vars = array(), $print_output = TRUE)
	{
		if (!$this->_haanga_loaded)
		{
			require(SYS_PATH.'/haanga/lib/Haanga.php');
			require(SYS_PATH.'/plugins/haanga.php');
			$config = $this->e->ha;
			Haanga::configure($config['haanga']);
			$this->_haanga_loaded = TRUE;
		}
		$output = Haanga::Load($view_name.'.html', $vars, TRUE);
		
		if ($this->_cache_result)
		{
			$this->_cache_content .= $output;
		}
		
		if($print_output)
			echo $output;
		else
			return $output;
	}

	protected function redir($location, $code = 301)
	{
		switch($code)
		{
			case 301:
				header('HTTP/1.1 301 Moved Permanently');
				break;
			case 302:
				header("HTTP/1.1 302 Moved Temporarily"); 
				break;
			default:
				break;
		}
		
		header('Location: '.$location);
		exit;
	}
	
	protected function cache($name, $_ = NULL)
	{
		$params = array_slice(func_get_args(), 1);
		$cache = $this->l->cache->get($name, $params);
		if (is_string($cache))
		{
			die($cache);
		}
		$this->_cache_result = TRUE;
		$this->_cache_name = $name;
		$this->_cache_params = & $params;
	}

	function  __destruct()
	{
		if ($this->_cache_result)
		{
			$this->l->cache->set($this->_cache_content, $this->_cache_name, $this->_cache_params);
		}
	}
}

/* End of file sys/core/ctrl.php */
