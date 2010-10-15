<?php

global $config;

$config['db'] = array(
	'mongodb' => array(
			'drv' => 'mongodb',
			'host' => 'localhost',
			'user' => '',
			'pass' => '',
			'db' => 'vevui',
			'opts' => array()
		),
	'mysql' => array(
			'drv' => 'mysql',
			'host' => 'localhost',
			'user' => 'root',
			'pass' => '',
			'db' => 'vevui'
		)
);

/* End of file app/e/db.php */