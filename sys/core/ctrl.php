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
	private $_core;
	private $_debug;
	private $_profiling;
	private $_profile_this;

	function __construct($installation_data = NULL)
	{
		$this->_core = & Vevui::get();

		if ($installation_data && array_key_exists('missing', $installation_data))
		{
			$this->_core->missing_component(get_class($this), $installation_data['missing']);
		}

		$config = $this->e->app;

		$this->_debug = $config->debug;
		$this->_profiling = $config->profiling;

		if (lcg_value() < $this->_profiling) // Hooray, saving data!
		{

		}
	}

	function __get($prop_name)
	{
		return $this->{$prop_name} = $this->_core->{$prop_name};
	}

	protected function render($view_name, $vars = array(), $print_output = TRUE)
	{
		$output = $this->_core->render($view_name, $vars, $print_output);
		if($print_output)
			echo $output;
		else
			return $output;
	}

	protected function crender($view_name, $vars = array())
	{
		$this->_core->crender($view_name, $vars);
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

	protected function not_found()
	{
		$this->_core->not_found();
	}

	protected function forbidden()
	{
		header('HTTP/1.1 403 Forbidden');
		die();
	}

	function disable_errors()
	{
		$this->_core->disable_errors();
	}

	function enable_errors()
	{
		$this->_core->enable_errors();
	}

	function error_handler($callback)
	{
		$this->_core->register_error_handler($callback);
	}

	function __destruct()
	{
//		if ($this->_cache_result)
//		{
//			$this->l->cache->set($this->_cache_content, $this->_cache_name, $this->_cache_params);
//		}
	}
}

/* End of file sys/core/ctrl.php */
