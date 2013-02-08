<?php
/*************************************************************************
 Copyright 2012 Vevui Development Team

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
		ob_start();
		$this->_core->test_setup();
	}

	function get_mock($original_class_name)
	{
		$obj = call_user_func_array('parent::getMock', func_get_args());
		$lowercase_class_name = strtolower($original_class_name);
		switch(get_parent_class($original_class_name))
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

	protected function tearDown()
	{
		ob_end_clean();
	}
}

/* End of file sys/core/test/test_case.php */
