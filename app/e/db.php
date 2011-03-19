<?php

global $config;

$config['db'] = array
	(
		'mongodb' => array
			(
				'engine' => 'mongodb',
				'host' => 'localhost',
				'user' => '',
				'pass' => '',
				'db' => 'vevui'
			),
		'sql' => array
			(
				'engine' => 'mysql',
				'host' => 'localhost',
				'user' => 'root',
				'pass' => '',
				'db' => 'vevui'
			)
	);

/* End of file app/e/db.php */