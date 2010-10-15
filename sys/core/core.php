<?php

$config = array();
$globals = array();
$globals['start_time'] = microtime(TRUE);
class Haanga_Extension_Tag_ElapsedTime
{
    public $is_block = FALSE;

    static function generator($cmp, $args, $assign=NULL)
    {
        /* ast */
        $code = hcode();

        /* llamar a la funcion */
        $exec = hexec('sprintf', '%.4f', 
            hexpr( hexec('microtime', TRUE), '-', hvar('globals', 'start_time') )
        );

        /* imprimir la funcion */
        $cmp->do_print($code, $exec);

        return $code;
    }
}

require(SYS_PATH.'/core/ctrl.php');
require(SYS_PATH.'/haanga/lib/Haanga.php');

set_error_handler('vevui_error_handler');

$uri_segs = explode('/', $_SERVER['REQUEST_URI']);
$uri_segs_count = count($uri_segs);

$start = ($uri_segs[1] == 'index.php')?2:1;
$request_class = $default_controller;
$request_method = 'index';
$request_params = array();

if ($uri_segs[$start])
	$request_class = $uri_segs[$start];

++$start;
if ($start < $uri_segs_count)
{
	if ($uri_segs[$start])
		$request_method = $uri_segs[$start];

	$request_params = array_slice($uri_segs, $start+1);
}

$filepath = APP_PATH.'/c/'.strtolower($request_class).'.php';

include($filepath);
$request_class_obj = new $request_class();
if (!is_subclass_of($request_class_obj, 'Ctrl'))
	trigger_error('Invalid class', E_USER_ERROR);

$haanga_config = array(
	'template_dir' => APP_PATH.'/v/',
	/* donde los archivos "compilados" PHP serán almacenados */
	'cache_dir' => '/var/tmp/Haanga/cache',
	/* Por defecto es TRUE, pero debe ser falso si ya se cuenta con un autoloader */ 
	'autoload' => TRUE, 
	'compiler' => array( /* opts for the tpl compiler */
			/* Avoid use if empty($var) */
			'if_empty' => FALSE,
			/* we're smart enought to know when escape :-) */
			'autoescape' => FALSE, 
			/* let's save bandwidth */
			'strip_whitespace' => TRUE, 
			/* call php functions from the template */
			'allow_exec'  => TRUE, 
			/* global $global, $current_user for all templates */
			'global' => array('globals'), 
		),
);
Haanga::configure($haanga_config); 

call_user_func_array(array($request_class_obj, $request_method), $request_params);

function vevui_error_handler($errno, $errstr, $errfile, $errline)
{
	header('HTTP/1.0 500 Internal Server Error');
	//header('HTTP/1.0 404 Not Found');
	die("errno=$errno.<br>errstr=$errstr.<br>errfile=$errfile.<br>errline=$errline.");
	
	// echo 'Requested file &lt;'.preg_replace('/\w+\/\.\.\//', '', $filepath).'&gt; does not exist!<br/>';
}

/* End of file sys/core/core.php */