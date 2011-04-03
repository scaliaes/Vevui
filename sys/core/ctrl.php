<?php

class Ctrl
{
	private $_helper_loader_loaded = FALSE;
	private $_library_loader_loaded = FALSE;

	function __get($prop_name)
	{
		switch ($prop_name)
		{
			case 'm':
				require(SYS_PATH.'/core/modelloader.php');
				return $this->m = new ModelLoader();
			case 'h':
				if (!$this->_helper_loader_loaded)
				{
					require(SYS_PATH.'/core/helperloader.php');
					$this->_helper_loader_loaded = TRUE;
				}
				return $this->h = new HelperLoader();
			case 'mh':
				if (!$this->_helper_loader_loaded)
				{
					require(SYS_PATH.'/core/helperloader.php');
					$this->_helper_loader_loaded = TRUE;
				}
				return $this->mh = new HelperLoader(TRUE);
			case 'l':
				if (!$this->_library_loader_loaded)
				{
					require(SYS_PATH.'/core/libraryloader.php');
					$this->_library_loader_loaded = TRUE;
				}
				return $this->l = new LibraryLoader();
			case 'ml':
				if (!$this->_library_loader_loaded)
				{
					require(SYS_PATH.'/core/libraryloader.php');
					$this->_library_loader_loaded = TRUE;
				}
				return $this->ml = new LibraryLoader(TRUE);
			default:
				trigger_error('Undefined variable: '.$prop_name, E_USER_ERROR);
		}
	}

	function render($view_name, $vars = array())
	{
		Haanga::Load($view_name.'.html', $vars);
	}
}

/* End of file sys/core/ctrl.php */