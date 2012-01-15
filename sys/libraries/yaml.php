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

class Yaml extends Lib
{
	private static $_parser;

	public static function _install(&$extensions, &$files, &$directories)
	{
		$files = array(SYS_PATH.'/libraries/spyc/spyc.php' => TRUE);
		$directories = array(SYS_PATH.'/libraries/spyc' => TRUE);
	}

	public function  __construct($installation_data)
	{
		parent::__construct($installation_data);
		require(SYS_PATH.'/libraries/spyc/spyc.php');
		self::$_parser = new Spyc();
	}

	private function _parse_vars($array, $asobject)
	{
		$result = $asobject?new stdClass():array();
		foreach ($array as $key => $value)
		{
			switch(TRUE)
			{
				case is_string($value):
					switch(TRUE)
					{
						case 0===strncmp($value, 'ROOT_PATH', strlen('ROOT_PATH')):
							$value = ROOT_PATH.substr($value, strlen('ROOT_PATH'));
							break;
						case 0===strncmp($value, 'APP_PATH', strlen('APP_PATH')):
							$value = APP_PATH.substr($value, strlen('APP_PATH'));
							break;
						case 0===strncmp($value, 'SYS_PATH', strlen('SYS_PATH')):
							$value = SYS_PATH.substr($value, strlen('SYS_PATH'));
							break;

						case 0===strncmp($value, 'APP_CONTROLLERS_PATH', strlen('APP_CONTROLLERS_PATH')):
							$value = APP_CONTROLLERS_PATH.substr($value, strlen('APP_CONTROLLERS_PATH'));
							break;
						case 0===strncmp($value, 'APP_CONFIG_PATH', strlen('APP_CONFIG_PATH')):
							$value = APP_CONFIG_PATH.substr($value, strlen('APP_CONFIG_PATH'));
							break;
						case 0===strncmp($value, 'APP_HELPERS_PATH', strlen('APP_HELPERS_PATH')):
							$value = APP_HELPERS_PATH.substr($value, strlen('APP_HELPERS_PATH'));
							break;
						case 0===strncmp($value, 'APP_LIBRARIES_PATH', strlen('APP_LIBRARIES_PATH')):
							$value = APP_LIBRARIES_PATH.substr($value, strlen('APP_LIBRARIES_PATH'));
							break;
						case 0===strncmp($value, 'APP_MODELS_PATH', strlen('APP_MODELS_PATH')):
							$value = APP_MODELS_PATH.substr($value, strlen('APP_MODELS_PATH'));
							break;
						case 0===strncmp($value, 'APP_ERROR_TEMPLATES_PATH', strlen('APP_ERROR_TEMPLATES_PATH')):
							$value = APP_ERROR_TEMPLATES_PATH.substr($value, strlen('APP_ERROR_TEMPLATES_PATH'));
							break;
						case 0===strncmp($value, 'APP_VIEWS_PATH', strlen('APP_VIEWS_PATH')):
							$value = APP_VIEWS_PATH.substr($value, strlen('APP_VIEWS_PATH'));
							break;
						case 0===strncmp($value, 'APP_EXTENSIONS_PATH', strlen('APP_EXTENSIONS_PATH')):
							$value = APP_EXTENSIONS_PATH.substr($value, strlen('APP_EXTENSIONS_PATH'));
							break;
					}
					break;
				case is_array($value):
				case is_object($value):
					$value = $this->_parse_vars($value, $asobject);
					break;
			}
			if ($asobject) $result->{$key} = $value;
			else $result[$key] = $value;
		}
		return $result;
	}

	public function load($filepath, $asobject = TRUE)
	{
		$content = @self::$_parser->loadFile($filepath);

		return $this->_parse_vars($content, $asobject);
	}
}

/* End of file sys/libraries/yaml.php */
