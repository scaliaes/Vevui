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

class LibraryLoader
{
	private $_user_libraries;

	function __construct($user = FALSE)
	{
		$this->_user_libraries = $user;
	}

	function __get($library_name)
	{
		$data = Vevui::get_installation_data();
		if ($this->_user_libraries)
		{
			$folder = APP_LIBRARIES_PATH;
			$data = isset($data['ul'][$library_name]) ? $data['ul'][$library_name] : NULL;
		}
		else
		{
			$folder = SYS_PATH.'/libraries';
			$data = isset($data['l'][$library_name]) ? $data['l'][$library_name] : NULL;
		}

		require($folder.'/'.$library_name.'.php');
		return $this->{$library_name} = new $library_name($data);
	}
}

/* End of file sys/core/libraryloader.php */
