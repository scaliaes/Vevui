<?php

class Test_Mdl extends MongoMdl
{
	function __construct()
	{
		parent::__construct('basedatos');
	}

	function get_data()
	{
		$this->foo->insert(array('nombre' => 'pepe'));
		$this->select_db('otra');
		$this->foo->insert(array('nombre' => 'juan'));
		$cursor = $this->foo->find();
		echo '<pre>';
		foreach ($cursor as $id => $value)
		{
		    echo "$id: ";
		    var_dump( $value );			
		}
		echo '</pre>';
	}
}

/* End of file app/m/test_mdl.php */