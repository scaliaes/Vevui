<?php

class ModelLoader
{
	private $_controller;
 	private $_config;
	private $_default_schema;

	function __construct($ctrl)
	{
		$this->_controller = &$ctrl;
		require(SYS_PATH.'/core/mdl.php');
		require(APP_PATH.'/e/db.php');
		$this->_config = & $db;
		$this->_default_schema = & $default_schema;
	}

	function __get($model_name)
	{
		include(APP_PATH.'/m/'.$model_name.'.php');
		Mdl::$config = &$this->_config;
		Mdl::$default_schema = &$this->_default_schema;
		Mdl::$controller = &$this->_controller;
		return $this->{$model_name} = new $model_name();
	}
}

/* End of file sys/core/modelloader.php */