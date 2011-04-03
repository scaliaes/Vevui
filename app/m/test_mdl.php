<?php

class Test_Mdl extends Mdl
{
	function __construct()
	{
		parent::__construct('mysql');
	}

	function get_data()
	{
		echo '<pre>';
		$res = $this->posts->get()->join('post2tag')->join('tags');
		foreach($res->exec() as $row)
			print_r($row);
echo '---------------------<br/>';
		$conditions[] = array(
				'idpost',
				3
			);
		$table = $this->{'posts'};	// Just like $this->posts;
		$table->get('title, content')->where($conditions);
		$res = $table->exec();
		foreach($res as $row)
			print_r($row);
		return true;
	}
}

/* End of file app/m/test_mdl.php */