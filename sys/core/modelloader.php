<?php

class ModelLoader
{
	function __construct()
	{
		require(SYS_PATH.'/core/mdl.php');
		require(APP_PATH.'/e/db.php');
	}

	function __get($model_name)
	{
		include(APP_PATH.'/m/'.strtolower($model_name).'.php');
		return $this->{$model_name} = new $model_name(); // TODO: Pasar array de configuración.
	}
}

/* End of file sys/core/modelloader.php */