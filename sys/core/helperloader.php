<?php

class Helper
{
	function __call($name, $args)
	{
		call_user_func_array($name, $args);
	}
}

class HelperLoader
{
	private $_user_helpers;

	function __construct($user = FALSE)
	{
		$this->_user_helpers = $user;
	}

	function __get($helper_name)
	{
		$folder = $this->_user_helpers?APP_PATH.'/h/':SYS_PATH.'/helpers/';
		include($folder.strtolower($helper_name).'.php');
		return $this->{$helper_name} = new Helper();
	}
}

/* End of file sys/core/helperloader.php */