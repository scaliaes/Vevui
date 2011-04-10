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
