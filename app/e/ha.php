<?php

$haanga = array
(
	'template_dir' => APP_PATH.'/v/',
	/* donde los archivos "compilados" PHP serán almacenados */
	'cache_dir' => '/var/tmp/Haanga/cache',
	/* Por defecto es TRUE, pero debe ser falso si ya se cuenta con un autoloader */
	'autoload' => TRUE,
	'compiler' => array	/* opts for the tpl compiler */
		(
			/* Avoid use if empty($var) */
			'if_empty' => FALSE,
			/* we're smart enought to know when escape :-) */
			'autoescape' => FALSE,
			/* let's save bandwidth */
			'strip_whitespace' => TRUE,
			/* call php functions from the template */
			'allow_exec' => TRUE,
			/* global $global, $current_user for all templates */
			'global' => array('globals'),
		),
);

/* End of file app/e/ha.php */
