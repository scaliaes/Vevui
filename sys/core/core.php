<?php

require(SYS_PATH.'/core/ctrl.php');
require(SYS_PATH.'/core/mdl.php');

set_error_handler('vevui_error_handler');

$uri_segs = explode('/', $_SERVER['REQUEST_URI']);
$uri_segs_count = count($uri_segs);

$start= ($uri_segs[1] === 'index.php')?2:1;
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
if (is_subclass_of($request_class_obj, 'Ctrl'))
{
	if (method_exists($request_class_obj, $request_method))
		call_user_func_array(array($request_class_obj, $request_method), $request_params);
	else
		echo 'Invalid method &lt;'.$request_method.'&gt; in class &lt;'.$request_class.'&gt;!<br/>';
}
else
{
	echo 'Invalid class &lt;'.$request_class.'&gt;!<br/>';
}

function vevui_error_handler($errno, $errstr, $errfile, $errline)
{
	header('HTTP/1.0 500 Internal Server Error');
	//header('HTTP/1.0 404 Not Found');
	die("$errno ------ $errstr ------ $errfile ------ $errline");
	
	// echo 'Requested file &lt;'.preg_replace('/\w+\/\.\.\//', '', $filepath).'&gt; does not exist!<br/>';
}

/* End of file sys/core/core.php */