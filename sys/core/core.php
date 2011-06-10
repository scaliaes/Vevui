<?php
/*************************************************************************
 Copyright 2011 Vevui Development Team

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

$config = array();
$globals = array();
$globals['start_time'] = microtime(TRUE);

require(SYS_PATH.'/core/ctrl.php');
require(SYS_PATH.'/core/configloader.php');

class Vevui
{
	static private $_core = NULL;

	private $_haanga_loaded = FALSE;
	private $_helper_loader_loaded = FALSE;
	private $_library_loader_loaded = FALSE;

	static public function & get()
	{
		if (is_null(self::$_core))
		{
			self::$_core = new Vevui();
		}
		return self::$_core;
	}

	protected function  __construct()
	{
		$this->e = new ConfigLoader();
	}

	public function __get($prop_name)
	{
		switch ($prop_name)
		{
			case 'm':
				require(SYS_PATH.'/core/modelloader.php');
				return $this->m = new ModelLoader();
			case 'h':
				if (!$this->_helper_loader_loaded)
				{
					require(SYS_PATH.'/core/helperloader.php');
					$this->_helper_loader_loaded = TRUE;
				}
				return $this->h = new HelperLoader();
			case 'l':
				if (!$this->_library_loader_loaded)
				{
					require(SYS_PATH.'/core/libraryloader.php');
					$this->_library_loader_loaded = TRUE;
				}
				return $this->l = new LibraryLoader();
			case 'mh':
				if (!$this->_helper_loader_loaded)
				{
					require(SYS_PATH.'/core/helperloader.php');
					$this->_helper_loader_loaded = TRUE;
				}
				return $this->mh = new HelperLoader(TRUE);
			case 'ml':
				if (!$this->_library_loader_loaded)
				{
					require(SYS_PATH.'/core/libraryloader.php');
					$this->_library_loader_loaded = TRUE;
				}
				return $this->ml = new LibraryLoader(TRUE);
			default:
				trigger_error('Undefined variable: '.$prop_name, E_USER_ERROR);
		}
	}

	public function render($view_name, $vars = array(), $print_output = TRUE)
	{
		if (!$this->_haanga_loaded)
		{
			require(SYS_PATH.'/haanga/lib/Haanga.php');
			require(SYS_PATH.'/plugins/haanga.php');
			$config = $this->e->ha;
			Haanga::configure($config['haanga']);
			$this->_haanga_loaded = TRUE;
		}
		return Haanga::Load($view_name.'.html', $vars, TRUE);
	}
}

error_reporting(0);
ini_set('display_errors', 0);

$uri = $_SERVER['REQUEST_URI'];

$core = Vevui::get();
$app = $core->e->app;
if ($app['routing'])
{
	foreach($core->e->app['routes'] as $pattern=>$redir)
	{
		$count = 0;
		$uri = preg_replace('/'.str_replace('/', '\\/', $pattern).'/', $redir, $uri, 1, $count);
		if ($count) break;
	}
}

function vevui_shutdown()
{
	$error = error_get_last();
	if ($error)
	{
		require(SYS_PATH.'/core/errorhandler.php');
		vevui_shutdown_error_handler($error);
	}
}

register_shutdown_function('vevui_shutdown');
set_error_handler('vevui_error_handler');

$uri_segs = explode('/', urldecode($uri));
$uri_segs_count = count($uri_segs);

$start = ($uri_segs[1] == 'index.php')?2:1;
$request_class = $default_controller;
$request_method = 'index';
$request_params = array();

if ($uri_segs[$start])
	$request_class = strtolower(preg_replace( array("/[^a-z0-9]/i","/[_]+/") , "_", $uri_segs[$start]));

++$start;
if ($start < $uri_segs_count)
{
	if ($uri_segs[$start])
		$request_method = $uri_segs[$start];

	$request_params = array_slice($uri_segs, $start+1);
}

$filepath = APP_PATH.'/c/'.$request_class.'.php';

include($filepath);
//if (!is_subclass_of($request_class, 'Ctrl'))
//	trigger_error('Invalid class', E_USER_ERROR);
$request_class_obj = new $request_class();

if( !is_subclass_of($request_class_obj, 'Ctrl') || !strncmp($request_method, '__', 2) || !is_callable(array($request_class_obj, $request_method)) )
{
	require(SYS_PATH.'/haanga/lib/Haanga.php');
	require(SYS_PATH.'/plugins/haanga.php');
	require(APP_PATH.'/e/ha.php');

	Haanga::configure($config['haanga']);
	Haanga::Load(APP_PATH.'/o/404.html', array('resource' => $_SERVER['REQUEST_URI']));
	exit;
}

call_user_func_array(array($request_class_obj, $request_method), $request_params);

function vevui_error_handler($errno, $errstr, $errfile, $errline)
{
	$error = array
			(
				'type' => $errno,
				'message' => $errstr,
				'file' => $errfile,
				'line' => $errline
			);

	require(SYS_PATH.'/core/errorhandler.php');
	vevui_shutdown_error_handler($error);
	die();
//	header('HTTP/1.0 500 Internal Server Error');
//	header('HTTP/1.0 404 Not Found');
//	die("errno=$errno.<br>errstr=$errstr.<br>errfile=$errfile.<br>errline=$errline.");

	// echo 'Requested file &lt;'.preg_replace('/\w+\/\.\.\//', '', $filepath).'&gt; does not exist!<br/>';
}

/* End of file sys/core/core.php */
