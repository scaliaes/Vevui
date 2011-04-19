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

class Cache extends Lib
{
	function  __construct()
	{
		parent::__construct();
	}

	function get($name, $params = NULL)
	{
		$path = $name;
		if ($params)
		{
			$path .= '/'.implode('/', array_map('sha1', $params));
		}
		$path .= '.html';

		$base_path = $this->ctrl()->e->cache['path'];
		return file_get_contents($base_path.'/'.$path);
	}

	function set($content, $name, $params = NULL)
	{
		$path = $name;
		if ($params)
		{
			$path .= '/'.implode('/', array_map('sha1', $params));
		}
		$path .= '.html';

		$base_path = $this->ctrl()->e->cache['path'];
		return file_put_contents($base_path.'/'.$path, $content);
	}
}

/* End of file sys/libraries/cache.php */
