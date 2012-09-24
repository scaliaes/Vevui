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

abstract class TestDB_case extends PHPUnit_Extensions_Database_TestCase
{
	private $_core;
	private $_conn = NULL;
	static private $_pdo = NULL;

	function __construct($db_index = NULL)
	{
		parent::__construct();
		$this->_core = & Vevui::get();

		$config = $this->_core->e->db;
		if (NULL === $db_index)
		{
			$db_config_value = $config->databases->{$config->default_schema};
		}
		else
		{
			$db_config_value = $config->databases->{$db_index};
		}

		// Only MySQL at the moment. mysql:dbname=testdb;host=127.0.0.1
		$dsn = $db_config_value->drv.':dbname='.$db_config_value->db.';host='.$db_config_value->host;
		self::$_pdo = new PDO($dsn, $db_config_value->user, $db_config_value->pass);
		$this->_conn = $this->createDefaultDBConnection(self::$_pdo, $db_config_value->db);
	}

	protected function getDatabaseTester()
	{
		$tester = new PHPUnit_Extensions_Database_DefaultTester($this->_conn);
		$tester->setSetUpOperation(PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT());
		$tester->setTearDownOperation(PHPUnit_Extensions_Database_Operation_Factory::NONE());

		return $tester;
	}

	final public function getConnection()
	{
		return $this->_conn;
	}

	protected function setUp()
	{
		$this->_core->test_setup();
	}

	function getMock($originalClassName, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = TRUE, $callOriginalClone = TRUE, $callAutoload = TRUE, $mockedMethods = array(), $cloneArguments = TRUE)
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

/* End of file sys/core/test/testdb_case.php */
