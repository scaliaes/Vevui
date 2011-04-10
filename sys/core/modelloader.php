<?php

class ModelLoader
{
	function __construct($ctrl)
	{
		require(SYS_PATH.'/core/mdl.php');
		require(APP_PATH.'/e/db.php');

        Mdl::$config = & $db;
		Mdl::$default_schema = & $default_schema;
		Mdl::$controller = & $ctrl;
    }

	function __get($model_name)
	{
		include(APP_PATH.'/m/'.$model_name.'.php');
		return $this->{$model_name} = new $model_name();
	}
}

/* End of file sys/core/modelloader.php */
