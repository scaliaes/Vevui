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
	private function _parse_accept($header)
	{
		$parts = array();
		foreach(explode(',', $header) as $part)
		{
			$pos = strpos($part, ';');
			if (FALSE !== $pos) $part = substr($part, 0, $pos);
			$parts[] = trim($part);
		}
		return array_unique($parts);
	}

	function __get($name)
	{
		switch($name)
		{
			case 'ip':
				return $this->$name = array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'];
			case 'ua':
				return $this->$name = array_key_exists('HTTP_USER_AGENT', $_SERVER)?$_SERVER['HTTP_USER_AGENT']:NULL;
			case 'referer':
				return $this->$name = array_key_exists('HTTP_REFERER', $_SERVER)?$_SERVER['HTTP_REFERER']:NULL;
			case 'langs':
				if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER))
				{
					$langs = array();
					foreach($this->_parse_accept($_SERVER['HTTP_ACCEPT_LANGUAGE']) as $lang)
					{
						$lang = str_replace('-', '_', $lang);

						$parts = explode('_', $lang, 3);
						if (array_key_exists(1, $parts))
						{
							if (array_key_exists(2, $parts))
							{
								$parts[1] = ucfirst($parts[1]);
								$parts[2] = strtoupper($parts[2]);
							}
							else
							{
								if (3 < strlen($parts[1]))
								{
									$parts[1] = ucfirst($parts[1]);
								}
								else
								{
									$parts[1] = strtoupper($parts[1]);
								}
							}
						}
						$lang = implode('_', $parts);

						$langs[] = $lang;
					}
					return $this->$name = array_unique($langs);
				}

				$ua = $this->ua;
				$pos1 = strpos($ua, '(');
				$pos2 = strpos($ua, ')');
				if ( (FALSE===$pos1) || (FALSE===$pos2) ) return $this->$name = array();

				$ua = substr($ua, $pos1+1, $pos2-$pos1-1);
				$parts = explode(';', $ua);

				return $this->$name = array_key_exists(3, $parts)?array(trim($parts[3])):array();
			case 'charsets':
				if (array_key_exists('HTTP_ACCEPT_CHARSET', $_SERVER))
				{
					return $this->$name = $this->_parse_accept($_SERVER['HTTP_ACCEPT_CHARSET']);
				}

				return $this->$name = array();
			case 'encodings':
				if (array_key_exists('HTTP_ACCEPT_ENCODING', $_SERVER))
				{
					return $this->$name = $this->_parse_accept($_SERVER['HTTP_ACCEPT_ENCODING']);
				}

				return $this->$name = array();
			case 'accept':
				if (array_key_exists('HTTP_ACCEPT', $_SERVER))
				{
					return $this->$name = $this->_parse_accept($_SERVER['HTTP_ACCEPT']);
				}

				return $this->$name = array();
		}
	}
}

/* End of file sys/libraries/client.php */
