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

$globals = array();
$globals['start_time'] = microtime(TRUE);

class Vevui
{
	static private $_core = NULL;

	private $_haanga_loaded = FALSE;
	private $_helper_loader_loaded = FALSE;
	private $_library_loader_loaded = FALSE;

	private $_request_class = NULL;
	private $_request_method = NULL;
	private $_request_ctrl = NULL;

	private $_error = FALSE;

	private $_error_handler = NULL;

	private $_uri = NULL;

	public static function & get()
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
			$type = $error['type'];
			switch($type)
			{
				case E_ERROR:
				case E_PARSE:
				case E_CORE_ERROR:
				case E_COMPILE_ERROR:
				case E_USER_ERROR:
				case E_PARSE:
				case E_RECOVERABLE_ERROR:
					$this->_shutdown_error_handler($type, $error['message'], $error['file'], $error['line']);
					die();
			}
		}
	}

	public function exception_handler($e)
	{
		$this->_call_error_handler($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());

		if(FALSE === $this->e->app->debug)
		{
			$this->_shutdown_error_handler($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
			die();
		}
		else
		{
			echo '<div style="position: fixed; bottom: 0; left: 0; width: 100%; border: 1px solid; z-index: 10; background-color: #feedb9; font-size: 10px; padding: 5px; white-space: pre-wrap">';
			echo '<pre>EH: '; print_r($e); echo '</pre></div>';
		}
	}

	public function error_handler($errno, $errstr, $errfile, $errline)
	{
		if (__FILE__ == $errfile) $this->not_found();

		if (TRUE === $this->_call_error_handler($errno, $errstr, $errfile, $errline)) return TRUE;

		switch($errno)
		{
			case E_ERROR:
			case E_PARSE:
			case E_CORE_ERROR:
			case E_COMPILE_ERROR:
			case E_USER_ERROR:
			case E_PARSE:
			case E_RECOVERABLE_ERROR:
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
				if(FALSE === $this->e->app->debug)
				{
					$this->_shutdown_error_handler($errno, $errstr, $errfile, $errline);
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
					$this->_call_error_handler($errno, $errstr, $errfile, $errline);
				}
				break;
		}

		return TRUE;
	}

	public function test_setup()
    {
		$this->ul;
		$classes = glob(APP_PATH.'/l/*.php', GLOB_BRACE);
		foreach($classes as $c)
		{
			$classname = substr(basename($c), 0, -4);
			if (!class_exists($classname)) require($c);
			unset($this->ul->$classname);
			$this->ul->$classname = new $classname;
		}

		$this->m;
		$classes = glob(APP_PATH.'/m/*.php', GLOB_BRACE);
		foreach($classes as $c)
		{
			$classname = substr(basename($c), 0, -4);
			if (!class_exists($classname)) require($c);
			unset($this->m->$classname);
			$this->m->$classname = new $classname;
		}
    }

	private function _shutdown_error_handler($type, $message, $file, $line)
	{
		if ($this->_error) return;
		$this->_error = TRUE;
		$this->disable_errors();

		if (__FILE__ == $file) $this->_not_found();
		else $this->_internal_error();

		$app = $this->e->app;
		if($app->debug)
		{
			echo '<div style="position: fixed; bottom: 0; left: 0; width: 100%; border: 1px solid; z-index: 10; background-color: #feedb9; font-size: 10px; padding: 5px; white-space: pre-wrap">';
			$error = array
			(
				'type' => $type,
				'message' => $message,
				'file' => $file,
				'line' => $line
			);
			echo '<pre> SE:'; print_r($error); echo '</pre>';

			$file_contents = file_get_contents($file);
			$nlines = count(file($file));
			$range = range(max(1, $line-5), min($nlines, $line+5));
			$lines_array = preg_split('/<[ ]*br[ ]*\/[ ]*>/', highlight_string($file_contents, TRUE));
			$highlighted = '';
			$line_nums = '';
			foreach($range as $i)
			{
				if ($i == ($line))
				{
					$line_nums .= '<div style="background-color: #ff9999">';
		//				$highlighted .= '<div style="background-color: #ff9999">';
				}
				$line_nums .= $i.'<br/>';
				$highlighted .= $lines_array[$i-1].'<br/>';
				if ($i == ($line))
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
			if (property_exists($app, 'log_errors') && $app->log_errors)
			{
				$db = new SQLite3($app->log_errors);
				$values = array();
				$values['file'] = "'".$db->escapeString($file)."'";
				$values['line'] = (int) $line;
				$values['type'] = (int) $type;
				$values['message'] = "'".$db->escapeString($message)."'";

				$time = time();
				$sqltime = date('Y-m-d H:i:s', $time);
				$values['timestamp'] = "'".$sqltime."'";
				$values['last_timestamp'] = "'".$sqltime."'";
				$values['slice'] = (int) ($time & 0xffffff00);
				$values['count'] = 0;

				$values['class'] = "'".$db->escapeString($this->_request_class)."'";
				$values['method'] = "'".$db->escapeString($this->_request_method)."'";

				$values['uri'] = NULL===$this->_uri?'NULL':"'".$db->escapeString($this->_uri)."'";

				$input = array();
				if ($_GET) $input['_GET'] = $_GET;
				if ($_POST) $input['_POST'] = $_POST;
				if ($_COOKIE) $input['_COOKIE'] = $_COOKIE;
				$values['input'] = "'".$db->escapeString(serialize($input))."'";

				$sql = 'INSERT OR IGNORE INTO errors ('.implode(',', array_keys($values)).') VALUES ('.implode(',', $values).');';
				if ($db->exec($sql))
				{
					$sql = 'UPDATE errors
							SET count=count+1, last_timestamp='.$values['last_timestamp'].'
							WHERE file='.$values['file'].'
								AND line='.$values['line'].'
								AND type='.$values['type'].'
								AND slice='.$values['slice'];
					$db->exec($sql);
				}
				else
				{
					$sql = 'CREATE TABLE errors
							(
								file VARCHAR(255) NOT NULL,
								line INT NOT NULL,
								type INT NOT NULL,
								message TEXT NOT NULL,
								timestamp TIMESTAMP NOT NULL,
								last_timestamp TIMESTAMP NOT NULL,
								slice INT NOT NULL,
								count INT NOT NULL,
								class VARCHAR(255) NOT NULL,
								method VARCHAR(255) NOT NULL,
								uri TEXT NULL,
								input TEXT NOT NULL,
							CONSTRAINT uniq UNIQUE (file ASC, line ASC, type ASC, slice ASC)
							)';
					$db->exec($sql);
					$sql = 'INSERT OR IGNORE INTO errors ('.implode(',', array_keys($values)).') VALUES ('.implode(',', $values).');';
					$db->exec($sql);
				}
				$db->close();
			}
		}
	}

	protected function __construct()
	{
		// Error handlers
		error_reporting(0);
		ini_set('display_errors', 0);
		register_shutdown_function(array($this, 'shutdown'));
		set_error_handler(array($this, 'error_handler'));
		set_exception_handler(array($this, 'exception_handler'));
	}

	public function register_error_handler($callback)
	{
		$this->_error_handler = $callback;
	}

	public function route()
	{
		$app = $this->e->app;

		// Developer mode. Should show a warning.
		if (property_exists($app, 'dev_mode') && $app->dev_mode)
		{
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
		}

		$this->_uri = urldecode($_SERVER['REQUEST_URI']);

		// Strip /index.php if exists.
		$pos = strpos($this->_uri, '/index.php');
		if (0 === $pos)
		{
			$this->_uri = substr($this->_uri, strlen('/index.php'));
		}

		// Check if query string is activated
		if($app->query_string)
		{
			if(FALSE !== ($query_pos = strpos($this->_uri, '?')))
			{
				$query_string = substr($this->_uri, $query_pos + 1);
				$this->_uri = substr($this->_uri, 0, $query_pos);

				// Check if query string character set is valid
				if(!preg_match('/^[=&'.$app->url_chars.']+$/i', $query_string))
					$this->not_found();
			}
		}

		// Apply URI Routing rules
		if (property_exists($app, 'routes'))
		{
			foreach($app->routes as $pattern=>$redir)
			{
				$count = 0;
				$this->_uri = preg_replace('/'.str_replace('/', '\\/', $pattern).'/', $redir, $this->_uri, 1, $count);
				if ($count) break;
			}
		}

		// Check if URI character set is valid
		if(!preg_match('/^[\/'.$app->url_chars.']+$/i', $this->_uri))
			$this->not_found();

		$uri_segs = explode('/', $this->_uri);
		$uri_segs_count = count($uri_segs);

		$this->_request_class = $app->default_controller;
		$this->_request_method = 'index';
		$request_params = array();

		if ('' !== $uri_segs[1])
			$this->_request_class = strtolower($uri_segs[1]);

		if (2 < $uri_segs_count)
		{
			if ('' !== $uri_segs[2])
				$this->_request_method = $uri_segs[2];

			$request_params = array_slice($uri_segs, 3);
		}

		// Call controller/method
		$filepath = APP_CONTROLLERS_PATH.'/'.$this->_request_class.'.php';

		require(SYS_PATH.'/core/ctrl.php');
		require($filepath);

		$data = Vevui::get_installation_data();
		$data = array_key_exists('c', $data) && array_key_exists($this->_request_class, $data['c']) ? $data['c'][$this->_request_class] : NULL;

		$this->_request_ctrl = new $this->_request_class($data);

		if( !is_subclass_of($this->_request_ctrl, 'Ctrl') || !strncmp($this->_request_method, '_', 1) )
		{
			$this->not_found();
		}

		call_user_func_array(array($this->_request_ctrl, $this->_request_method), $request_params);
	}

	public function __get($prop_name)
	{
		switch ($prop_name)
		{
			case 'e':
				require(SYS_PATH.'/core/configloader.php');
				return $this->e = new ConfigLoader();
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
			case 'uh':
				if (!$this->_helper_loader_loaded)
				{
					require(SYS_PATH.'/core/helperloader.php');
					$this->_helper_loader_loaded = TRUE;
				}
				return $this->uh = new HelperLoader(TRUE);
			case 'ul':
				if (!$this->_library_loader_loaded)
				{
					require(SYS_PATH.'/core/libraryloader.php');
					require(SYS_PATH.'/core/lib.php');
					$this->_library_loader_loaded = TRUE;
				}
				return $this->ul = new LibraryLoader(TRUE);
			default:
				$this->internal_error();
		}
	}

	public function crender($view_name, $vars = array())
	{
		$output = $this->render($view_name, $vars, FALSE);
		if ($this->e->app->cache)
		{
			$this->l->cache->set($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], $output);
		}
		echo $output;
	}

	private function _load_haanga()
	{
		require(SYS_PATH.'/haanga/lib/Haanga.php');
		require(SYS_PATH.'/plugins/haanga.php');
		$config = $this->e->haanga;
		Haanga::configure($config);

		if ($this->e->i18n->active)
		{
			$locales = array();
			foreach($this->l->client->langs as $lang)
			{
				foreach($this->l->client->charsets as $enc)
				{
					if ('*' != $enc) $locales[] = $lang.'.'.strtolower($enc);
				}
				$locales[] = $lang;
			}

			$domain = $this->e->i18n->domain;
			putenv('LC_ALL='.implode(',', $locales));
			setlocale(LC_ALL, $locales);
			bindtextdomain($domain, $this->e->i18n->path);
			bind_textdomain_codeset($domain, $this->e->i18n->charset);
			textdomain($domain);
		}
	}

	public function render($view_name, $vars = array(), $print_output = TRUE)
	{
		if (!$this->_haanga_loaded)
		{
			$this->_load_haanga();
			$this->_haanga_loaded = TRUE;
		}
		return Haanga::Load($view_name.'.html', $vars, TRUE);
	}

	private function _not_found()
	{
		if (!$this->_haanga_loaded)
		{
			$this->_load_haanga();
			$this->_haanga_loaded = TRUE;
		}

		header('HTTP/1.0 404 Not Found');

		$config = $this->e->haanga;
		$config['template_dir'] = APP_ERROR_TEMPLATES_PATH;
		Haanga::configure($config);

		Haanga::Load('404.html', array('resource' => $_SERVER['REQUEST_URI']));
	}

	public function not_found()
	{
		$this->_not_found();
		die();
	}

	private function _internal_error()
	{
		if (!$this->_haanga_loaded)
		{
			$this->_load_haanga();
			$this->_haanga_loaded = TRUE;
		}

		header('HTTP/1.0 500 Internal Server Error');

		$config = $this->e->haanga;
		$config['template_dir'] = APP_ERROR_TEMPLATES_PATH;
		Haanga::configure($config);

		Haanga::Load('500.html', array('resource' => $_SERVER['REQUEST_URI']));
	}

	public function internal_error()
	{
		$this->_internal_error();
		die();
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

	private function _call_error_handler()
	{
		if (NULL !== $this->_error_handler)
		{
			return call_user_func_array($this->_error_handler, func_get_args());
		}
		return NULL;
	}

	public static function get_installation_data()
	{
		global $_installation_data;
		return $_installation_data;
	}

	public function missing_component($class, $missing)
	{
		echo 'Missing needed components for class '.$class.'.<br/>';
		if (array_key_exists('e', $missing)) echo 'Missing extensions: '.implode(',', $missing['e']).'<br/>';
		if (array_key_exists('f', $missing)) echo 'Missing files: '.implode(',', $missing['f']).'<br/>';
		if (array_key_exists('d', $missing)) echo 'Missing directories: '.implode(',', $missing['d']).'<br/>';

		$this->internal_error();
	}
}

$_installation_data = array();

/* End of file sys/core/core.php */
