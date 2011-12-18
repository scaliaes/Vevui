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

abstract class Drv
{
	function __construct()
	{
	}

	protected function _raise_error($error_string)
	{
		$core = & Vevui::get();

		// TODO: store error_string in error database
		if($core->e->app->debug)
			echo '<p>'.$error_string.'</p>';

		$core->internal_error();
	}

	abstract function register_functions();
	abstract function exec();
	abstract function exec_one();
}

/* End of file sys/core/drv.php */
