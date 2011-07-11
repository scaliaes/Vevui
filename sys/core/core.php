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

	private $_track_errors = TRUE;
	private $_debug = FALSE;

	static public function & get()
	{
		if (is_null(self::$_core))
		{
			self::$_core = new Vevui();
		}
		return self::$_core;
	}

	public function shutdown()
	{
		$error = error_get_last();
		if ($error)
		{
			switch($error['type'])
			{
				case E_ERROR:		
				case E_PARSE:
				case E_CORE_ERROR:
				case E_COMPILE_ERROR:
				case E_USER_ERROR:
				case E_PARSE:
				case E_RECOVERABLE_ERROR:
					$this->_shutdown_error_handler($error);
			}
		}
	}

	public function exception_handler($exception)
	{
		if(FALSE === $this->_debug)
		{
			die();
		}
		else
		{
			echo '<div style="position: fixed; bottom: 0; left: 0; width: 100%; border: 1px solid; z-index: 10; background-color: #feedb9; font-size: 10px; padding: 5px; white-space: pre-wrap">';
			$error = array
			(
				'type' => $exception->getCode(),
				'message' => $exception->getMessage(),
				'file' => $exception->getFile(),
				'line' => $exception->getLine()
			);
			echo '<pre>EH: '; print_r($exception); echo '</pre></div>';
		}
	}

	public function error_handler($errno, $errstr, $errfile, $errline)
	{
		switch($errno)
		{
			case E_ERROR:		
			case E_PARSE:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
			case E_PARSE:
			case E_RECOVERABLE_ERROR:
				die();
				break;
			case E_WARNING:			
			case E_NOTICE:			
			case E_CORE_WARNING:
			case E_COMPILE_WARNING:
			case E_USER_WARNING:		
			case E_USER_NOTICE:
			case E_STRICT:		
			case E_DEPRECATED:
			case E_USER_DEPRECATED:		
				if(FALSE === $this->_debug)
				{
					die();
				}
				else
				{
					echo '<div style="position: fixed; bottom: 0; left: 0; width: 100%; border: 1px solid; z-index: 10; background-color: #feedb9; font-size: 10px; padding: 5px; white-space: pre-wrap">';
					$error = array
					(
						'type' => $errno,
						'message' => $errstr,
						'file' => $errfile,
						'line' => $errline
					);
					echo '<pre>EH: '; print_r($error); echo '</pre></div>';
				}
				break;
		}
	}

	private function _shutdown_error_handler($error)
	{
		header('HTTP/1.0 500 Internal Server Error');

		echo 'Ha ocurrido un error interno, disculpen las molestias';

		if($this->_debug)
		{
			echo '<div style="position: fixed; bottom: 0; left: 0; width: 100%; border: 1px solid; z-index: 10; background-color: #feedb9; font-size: 10px; padding: 5px; white-space: pre-wrap">';
			echo '<pre> SE:'; print_r($error); echo '</pre>';

			$file_contents = file_get_contents($error['file']);
			$nlines = count(file($error['file']));
			$range = range(max(1, $error['line']-5), min($nlines, $error['line']+5));
			$lines_array = preg_split('/<[ ]*br[ ]*\/[ ]*>/', highlight_string($file_contents, TRUE));
			$highlighted = '';
			$line_nums = '';
			foreach($range as $i)
			{
				if ($i == ($error['line']))
				{
					$line_nums .= '<div style="background-color: #ff9999">';
		//				$highlighted .= '<div style="background-color: #ff9999">';
				}
				$line_nums .= $i.'<br/>';
				$highlighted .= $lines_array[$i-1].'<br/>';
				if ($i == ($error['line']))
				{
					$line_nums .= '</div>';
		//				$highlighted .= '</div>';
				}
			}
			echo '<style type="text/css">
							.num {
							float: left;
							color: gray;
		//						font-size: 13px;
		//						font-family: monospace;
							text-align: right;
							margin-right: 6pt;
							padding-right: 6pt;
							border-right: 1px solid gray;}

							body {margin: 0px; margin-left: 5px;}
							td {vertical-align: top;}
							code {white-space: nowrap;}
						</style>',
					"<table><tr><td class=\"num\">\n$line_nums\n</td><td>\n$highlighted\n</td></tr></table>";
		}
		else
		{
			// TODO: Log in sqlite
		}
	}

	protected function __construct()
	{
		register_shutdown_function(array($this, 'shutdown'));
		set_error_handler(array($this, 'error_handler'));
		set_exception_handler(array($this, 'exception_handler'));

		$this->e = new ConfigLoader();
		$this->_debug = $this->e->app['debug'];
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
					require(SYS_PATH.'/core/lib.php');
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
					require(SYS_PATH.'/core/lib.php');
					$this->_library_loader_loaded = TRUE;
				}
				return $this->ml = new LibraryLoader(TRUE);
			default:
				trigger_error('Undefined variable: '.$prop_name, E_USER_ERROR);
		}
	}

	public function crender($view_name, $vars = array())
	{
		$output = $this->render($view_name, $vars, FALSE);
		$this->l->cache->set($_SERVER['REQUEST_URI'], $output);
		echo $output;
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

	public function not_found()
	{
		if (!$this->_haanga_loaded)
		{
			require(SYS_PATH.'/haanga/lib/Haanga.php');
			require(SYS_PATH.'/plugins/haanga.php');
			$config = $this->e->ha;
			Haanga::configure($config['haanga']);
			$this->_haanga_loaded = TRUE;
		}

		header("HTTP/1.0 404 Not Found");
		Haanga::Load('../o/404.html', array('resource' => $_SERVER['REQUEST_URI']));
		exit;
	}

	public function disable_errors()
	{
		restore_error_handler();
		restore_exception_handler();
	}

	public function enable_errors()
	{
		set_error_handler(array($this, 'error_handler'));
		set_exception_handler(array($this, 'exception_handler'));
	}
}

// Error handlers
error_reporting(0);

$core = & Vevui::get();
$app = $core->e->app;
$uri = $_SERVER['REQUEST_URI'];


// Check if query string is activated
if($app['query_string'])
{
	if(FALSE !== ($query_pos = strpos($uri, '?')))
	{
		$query_string = substr($uri, $query_pos + 1);
		$uri = substr($uri, 0, $query_pos);
		
		// Check if query string character set is valid
		if(!preg_match('/^[=&'.$app['url_chars'].']+$/i', $query_string))
			$core->not_found();
	}
}


// Apply URI Routing rules
if (array_key_exists('routes', $app))
{
	foreach($app['routes'] as $pattern=>$redir)
	{
		$count = 0;
		$uri = preg_replace('/'.str_replace('/', '\\/', $pattern).'/', $redir, $uri, 1, $count);
		if ($count) break;
	}
}

// Check if URI character set is valid
if(!preg_match('/^[\/'.$app['url_chars'].']+$/i', $uri))
	$core->not_found();
			
$uri_segs = explode('/', urldecode($uri));
$uri_segs_count = count($uri_segs);

$start = ($uri_segs[1] == 'index.php')?2:1;
$request_class = $default_controller;
$request_method = 'index';
$request_params = array();

if ($uri_segs[$start])
	$request_class = strtolower($uri_segs[$start]);

++$start;
if ($start < $uri_segs_count)
{
	if ($uri_segs[$start])
		$request_method = $uri_segs[$start];

	$request_params = array_slice($uri_segs, $start+1);
}

// Call controller/method
$filepath = APP_PATH.'/c/'.$request_class.'.php';

require($filepath);
$request_class_obj = new $request_class();

if( !is_subclass_of($request_class_obj, 'Ctrl') || !strncmp($request_method, '__', 2) || !is_callable(array($request_class_obj, $request_method)) )
{
	$core->not_found();
}

call_user_func_array(array($request_class_obj, $request_method), $request_params);

/* End of file sys/core/core.php */
