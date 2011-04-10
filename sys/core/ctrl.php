<?php

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