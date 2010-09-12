<?php

require(SYS_PATH.'/core/ctrl.php');
require(SYS_PATH.'/core/urihandler.php');

$uri_handler = new Vevui_URIHandler();

$filepath = APP_PATH.'/c/'.strtolower($uri_handler->request_class).'.php';
if (file_exists($filepath))
{
	include_once($filepath);
	$request_class_obj = new $uri_handler->request_class();
	if (is_subclass_of($request_class_obj, 'Ctrl'))
	{
		if (method_exists($request_class_obj, $uri_handler->request_method))
			call_user_func_array(array($request_class_obj, $uri_handler->request_method), $uri_handler->request_params);
		else
			echo 'Invalid method &lt;'.$uri_handler->request_method.'&gt; in class &lt;'.$uri_handler->request_class.'&gt;!<br/>';
	}
	else
	{
		echo 'Invalid class &lt;'.$uri_handler->request_class.'&gt;!<br/>';
	}
}
else
{
	echo 'Requested file &lt;'.preg_replace('/\w+\/\.\.\//', '', $filepath).'&gt; does not exist!<br/>';
}

/* End of file sys/core/core.php */
