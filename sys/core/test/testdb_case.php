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

class DB_operation_set_env implements PHPUnit_Extensions_Database_Operation_IDatabaseOperation
{
	private $_elem;
	private $_value;

	public function __construct($elem, $value)
	{
		$this->_elem  = $elem;
		$this->_value = $value;
	}

	public function execute(PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection, PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
	{
		$connection->getConnection()->query('SET '.$this->_elem.'='.$this->_value);
	}
}

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
		self::$_pdo = new PDO($dsn, $db_config_value->user, $db_config_value->pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''));
		$this->_conn = $this->createDefaultDBConnection(self::$_pdo, $db_config_value->db);
	}

	protected function getDatabaseTester()
	{
		$tester = new PHPUnit_Extensions_Database_DefaultTester($this->_conn);
		$tester->setSetUpOperation(new PHPUnit_Extensions_Database_Operation_Composite(array
		(
			new DB_operation_set_env('foreign_key_checks', 0),
			PHPUnit_Extensions_Database_Operation_Factory::DELETE_ALL(),
			new DB_operation_set_env('foreign_key_checks', 1),
			PHPUnit_Extensions_Database_Operation_Factory::INSERT()
		)));
		$tester->setTearDownOperation(PHPUnit_Extensions_Database_Operation_Factory::NONE());

		return $tester;
	}

	final public function getConnection()
	{
		return $this->_conn;
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

/* End of file sys/core/test/testdb_case.php */
