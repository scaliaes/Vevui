<?php

class ModelLoader
{
	private $_models = array();

	function __construct()
	{
		require(SYS_PATH.'/core/mdl.php');
		require(APP_PATH.'/e/db.php');
	}

	function __get($model_name)
	{
		if (!array_key_exists($model_name, $this->_models))
		{
			include(APP_PATH.'/m/'.strtolower($model_name).'.php');
			$this->_models[$model_name] = new $model_name();
			$this->_models[$model_name.'2'] = new $model_name();
		}
		return $this->_models[$model_name];
    }
}
	
/* End of file sys/core/modelloader.php */