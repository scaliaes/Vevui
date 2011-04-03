<?php

class LibraryLoader
{
	private $_user_libraries;

	function __construct($user = FALSE)
	{
		$this->_user_libraries = $user;
	}

	function __get($library_name)
	{
		$folder = $this->_user_libraries?APP_PATH.'/l/':SYS_PATH.'/libraries/';
		include($folder.strtolower($library_name).'.php');
		return $this->{$library_name} = new $library_name();
	}
}

/* End of file sys/core/libraryloader.php */