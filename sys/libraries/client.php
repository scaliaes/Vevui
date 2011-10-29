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

class Client extends Lib
{
	function __get($name)
	{
		switch($name)
		{
			case 'ip':
				return $this->ip = array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'];
			case 'ua':
				return $this->ua = array_key_exists('HTTP_USER_AGENT', $_SERVER)?$_SERVER['HTTP_USER_AGENT']:NULL;
			case 'referer':
				return $this->referer = array_key_exists('HTTP_REFERER', $_SERVER)?$_SERVER['HTTP_USER_AGENT']:NULL;
		}
	}
}

/* End of file sys/libraries/client.php */
