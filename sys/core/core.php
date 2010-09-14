<?php
require(SYS_PATH.'/core/ctrl.php');
require(SYS_PATH.'/core/mdl.php');
require(SYS_PATH.'/haanga/lib/Haanga.php');

set_error_handler('vevui_error_handler');

$uri_segs = explode('/', $_SERVER['REQUEST_URI']);
$uri_segs_count = count($uri_segs);

$start = ($uri_segs[1] == 'index.php')?2:1;
$request_class = 'sample';
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

$config = array(
      'template_dir' => APP_PATH.'/v/',
      /* donde los archivos "compilados" PHP serán almacenados */
      'cache_dir' => '/var/tmp/Haanga/sitio-web',
      /* Por defecto es TRUE, pero debe ser falso si ya se cuenta con un autoloader */ 
      'autoload' => TRUE, 
       /* Opciones que son pasadas al compilador */
      'compiler' => array('allow_exec' => TRUE), 
);
Haanga::configure($config); 

call_user_func_array(array($request_class_obj, $request_method), $request_params);

function vevui_error_handler($errno, $errstr, $errfile, $errline)
{
	header('HTTP/1.0 500 Internal Server Error');
	//header('HTTP/1.0 404 Not Found');
	die("errno=$errno.<br>errstr=$errstr.<br>errfile=$errfile.<br>errline=$errline.");
	
	// echo 'Requested file &lt;'.preg_replace('/\w+\/\.\.\//', '', $filepath).'&gt; does not exist!<br/>';
}

/* End of file sys/core/core.php */