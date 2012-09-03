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

/***********************************************************
  Vevui configuration options
 **********************************************************/

/* Path where the sys folder is located. */
$sys_path = '..';

/* Path where the app folder is located. */
$app_path = '../../app';

/* Environment to use. */
$environment = 'test';

/***********************************************************
  DON'T EDIT BELOW THIS LINE (UNLESS YOU ARE A DEVELOPER ;)
 **********************************************************/

define('ROOT_PATH', __DIR__.'/../..');
define('SYS_PATH', __DIR__.'/'.$sys_path);
define('APP_PATH', __DIR__.'/'.$app_path);

define('CACHE_PATH', __DIR__.'/../../cache');

define('APP_CONTROLLERS_PATH', APP_PATH.'/c');
define('APP_CONFIG_PATH', APP_PATH.'/e');
define('APP_HELPERS_PATH', APP_PATH.'/h');
define('APP_LIBRARIES_PATH', APP_PATH.'/l');
define('APP_MODELS_PATH', APP_PATH.'/m');
define('APP_ERROR_TEMPLATES_PATH', APP_PATH.'/o');
define('APP_VIEWS_PATH', APP_PATH.'/v');
define('APP_EXTENSIONS_PATH', APP_PATH.'/x');

define('ENVIRONMENT', $environment);

require(SYS_PATH.'/core/coreloader.php');

abstract class Test_case extends PHPUnit_Framework_TestCase
{
	private $_core;

	function __construct()
	{
		parent::__construct();
		$this->_core = & Vevui::get();
	}

	protected function setUp()
	{
		$this->_core->test_setup();
	}

	function getMock($originalClassName, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = TRUE, $callOriginalClone = TRUE, $callAutoload = TRUE)
	{
		$obj = call_user_func_array('parent::getMock', func_get_args());
		$lowercase_class_name = strtolower($originalClassName);
		switch(get_parent_class($originalClassName))
		{
			case 'Mdl':
				$this->_core->m->$lowercase_class_name = $obj;
				break;
			case 'Lib':
				$this->_core->ul->$lowercase_class_name = $obj;
				break;
			default:
				$this->assertTrue(FALSE, 'You can only mock Mdl or Lib subclasses.');
		}
		return $obj;
	}

	function __get($p)
	{
		return $this->_core->{$p};
	}
}

/* End of file sys/core/test.php */
