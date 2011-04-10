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
	private $_helper_loader_loaded = FALSE;
	private $_library_loader_loaded = FALSE;
	private $_debug;
	private $_profiling;
	private $_profile_this;

	function  __construct()
	{
		require(APP_PATH.'/e/app.php');
		$this->_debug = $debug;
		$this->_profiling = $profiling;

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

	protected function render($view_name, $vars = array())
	{
		$output = Haanga::Load($view_name.'.html', $vars, TRUE);
	}
}

/* End of file sys/core/ctrl.php */
