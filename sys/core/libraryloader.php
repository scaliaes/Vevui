<?php

class LibraryLoader
{
	private $_controller;
	private $_user_libraries;

	function __construct($ctrl, $user = FALSE)
	{
		$this->_controller = &$ctrl;
		$this->_user_libraries = $user;
		require(SYS_PATH.'/core/lib.php');
	}

	function __get($library_name)
	{
		$folder = $this->_user_libraries?APP_PATH.'/l/':SYS_PATH.'/libraries/';
		include($folder.$library_name.'.php');
		Lib::$controller = &$this->_controller;
		return $this->{$library_name} = new $library_name();
	}
}

/* End of file sys/core/libraryloader.php */