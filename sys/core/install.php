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

function _process_file($file, $class)
{
	$code = file_get_contents($file);

	if ( (FALSE === @eval('if(FALSE){ ?>'.$code.'}')) && (FALSE === @eval('if(FALSE){?>'.$code.'<?php }')) )
	{
		return $file.' has errors.';
	}

	$defining = FALSE;
	$funccode = '';
	$braces = 0;
	$visibility = TRUE;
	$static = FALSE;
	$function = FALSE;
	$infunc = FALSE;
	foreach(token_get_all($code) as $tok)
	{
		if ($defining)
		{
			if (is_array($tok))
			{
				switch($tok[0])
				{
					case T_CURLY_OPEN:
					case T_DOLLAR_OPEN_CURLY_BRACES:
						$infunc = TRUE;
						++$braces;
						break;
				}
				if (T_WHITESPACE != $tok[0]) $funccode .= $tok[1];
			}
			else
			{
				switch($tok)
				{
					case '{':
						$infunc = TRUE;
						++$braces;
						break;
					case '}':
						--$braces;
						break;
				}
				$funccode .= $tok;				
			}
			if ($infunc && !$braces)
			{
				break;
			}
			continue;
		}
		if (is_array($tok))
		{
			if ($visibility && $static && $function)
			{
				switch($tok[0])
				{
					case T_WHITESPACE:
						break;
					case T_STRING:
						if ('_install' == $tok[1])
						{
							$defining = TRUE;
						}
						break;
					default:
						$visibility = TRUE;
						$defining = $static = $function = FALSE;
				}
			}
			switch($tok[0])
			{
				case T_PRIVATE:
				case T_PROTECTED:
					$visibility = FALSE;
					break;
				case T_STATIC:
					$static = TRUE;
					break;
				case T_FUNCTION:
					$function = TRUE;
					break;
			}
		}
		else
		{
			$visibility = TRUE;
			$defining = $static = $function = FALSE;
		}
	}
	if ('' == $funccode) return array();

	$name = '_install'.mt_rand();

	$custom = $extensions = $files = $directories = NULL;
	$custom = eval('function '.$name.$funccode.' return '.$name.'($extensions, $files, $directories);');

	$data = array();
	// Extensions.
	if (NULL !== $extensions)
	{
		foreach($extensions as $extension => $required)
		{
			if ($required)
			{
				if (!extension_loaded($extension))
				{
					$data['missing']['e'][] = $extension;
				}
			}
			else
			{
				$data['e']['opt'][$extension] = extension_loaded($extension);
			}
		}
	}

	// Files.
	if (NULL !== $files)
	{
		foreach($files as $file => $required)
		{
			if ($required)
			{
				if (!is_file($file))
				{
					$data['missing']['f'][] = $file;
				}
			}
			else
			{
				$data['f']['opt'][$file] = is_file($file);
			}
		}
	}

	// Directories.
	if (NULL !== $directories)
	{
		foreach($directories as $dir => $required)
		{
			if ($required)
			{
				if (!is_dir($dir))
				{
					$data['missing']['d'][] = $dir;
				}
			}
			else
			{
				$data['d']['opt'][$dir] = is_dir($dir);
			}
		}
	}

	// Custom data;
	if (NULL !== $custom)
	{
		$data['c'] = $custom;
	}

	return $data;
}

function _process_directory($directory)
{
	$ret = array();
	// Process directory.
	foreach(scandir($directory) as $lib)
	{
		if (!is_file($directory.'/'.$lib))
		{
			continue;
		}

		if (0!==strcasecmp(substr($lib, -4), '.php'))
		{
			// Unknown file...
			continue;
		}

		$libname = substr($lib, 0, -4);
		$data = _process_file($directory.'/'.$lib, $libname);
		if (is_string($data))
		{
			return $data;
		}

		if ($data)
		{
			$ret[$libname] = $data;
		}
	}
	return $ret;
}

function vevui_install(&$msg)
{
	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	// General requirements.
	if (!is_dir(CACHE_PATH))
	{
		$msg = CACHE_PATH.' is not a directory.';
		return NULL;
	}

	if (!is_writable(CACHE_PATH))
	{
		$msg = CACHE_PATH.' is not writable.';
		return NULL;
	}

	$installation_data = array();

	// Test and install system libraries.
	$data = _process_directory(SYS_PATH.'/libraries');
	if (is_string($data))
	{
		$msg = $data;
		return NULL;
	}
	if ($data) $installation_data['l'] = $data;

	// Test and install drivers.
	$data = _process_directory(SYS_PATH.'/core/drvs');
	if (is_string($data))
	{
		$msg = $data;
		return NULL;
	}
	if ($data) $installation_data['drv'] = $data;

	// Test and install app libraries.
	$data = _process_directory(APP_LIBRARIES_PATH);
	if (is_string($data))
	{
		$msg = $data;
		return NULL;
	}
	if ($data) $installation_data['ul'] = $data;

	// Test and install app models.
	$data = _process_directory(APP_MODELS_PATH);
	if (is_string($data))
	{
		$msg = $data;
		return NULL;
	}
	if ($data) $installation_data['m'] = $data;

	// Test and install app controllers.
	$data = _process_directory(APP_CONTROLLERS_PATH);
	if (is_string($data))
	{
		$msg = $data;
		return NULL;
	}
	if ($data) $installation_data['c'] = $data;

	error_reporting(0);
	ini_set('display_errors', 0);

	return $installation_data;
}

/* End of file sys/core/install.php */
