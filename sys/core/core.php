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

	private $_error_handler = NULL;

	private $_uri = NULL;

	protected function __construct()
	{
		// TODO: Build ErrorHandler correctly
		require(SYS_PATH.'/core/errorhandler.php');
		$this->_error_handler = new ErrorHandler($this);
	}

	public static function & get()
	{
		if (is_null(self::$_core))
		{
			self::$_core = new Vevui();
		}
		return self::$_core;
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

	public function route()
	{
		$app = $this->e->app;

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

		require(SYS_PATH.'/core/baselog.php');
		require(SYS_PATH.'/core/log.php');
		require(SYS_PATH.'/core/ctrl.php');

		if(!file_exists($filepath))
		{
			$this->not_found();
		}
		require($filepath);

		$data = Vevui::get_installation_data();
		$data = array_key_exists('c', $data) && array_key_exists($this->_request_class, $data['c']) ? $data['c'][$this->_request_class] : NULL;

		$this->_request_ctrl = new $this->_request_class($data);

		if( !is_subclass_of($this->_request_ctrl, 'Ctrl') || !strncmp($this->_request_method, '_', 1) )
		{
			$this->not_found();
		}

		ob_start();
		call_user_func_array(array($this->_request_ctrl, $this->_request_method), $request_params);
		ob_flush();
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

  public function _not_found()
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

	public function render($view_name, $vars = array(), $print_output = TRUE)
	{
		if (!$this->_haanga_loaded)
		{
			$this->_load_haanga();
			$this->_haanga_loaded = TRUE;
		}
		return Haanga::Load($view_name.'.html', $vars, TRUE);
	}

	public function not_found()
	{
		$this->_not_found();
		die();
	}

	public function internal_error()
	{
		$this->_error_handler->_internal_error();
		die();
	}

	public function disable_errors()
	{
		$this->_error_handler->_disable_errors();
	}

  public function register_user_error_handler($callback)
  {
    $this->_error_handler->_register_user_error_handler($callback);
  }

	public function raise_error($errno, $error_string, $file, $line)
	{
		$this->_error_handler->error_handler($errno, $error_string, $file, $line);
	}

	public function enable_errors()
	{
		$this->_error_handler->_enable_errors();
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
