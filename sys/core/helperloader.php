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

class Helper
{
	public function __construct($folder, $helper_name)
	{
		require($folder.'/'.$helper_name.'.php');
	}

	function __call($name, $args)
	{
		return call_user_func_array($name, $args);
	}
}

class HelperLoader
{
	private $_user_helpers;

	function __construct($user = FALSE)
	{
		$this->_user_helpers = $user;
	}

	function __get($helper_name)
	{
		$folder = $this->_user_helpers?APP_HELPERS_PATH:SYS_PATH.'/helpers';
		return $this->{$helper_name} = new Helper($folder, $helper_name);
	}
}

/* End of file sys/core/helperloader.php */
