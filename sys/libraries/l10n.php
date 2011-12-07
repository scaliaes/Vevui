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

class L10n extends Lib
{
	function  __construct()
	{
		parent::__construct();
	}

	private function _get_locales($locale)
	{
		if (NULL === $locale) $locale = $this->l->client->locale;
		if (NULL === $locale) $locale = $this->e->l10n->default;
		if (!is_array($locale)) $locale = array($locale);
		return $locale;
	}

	private function _get_locale_info($locale)
	{
		$path = $this->l->app->cache_path.'/'.$locale.'.php';
		
	}

	function countries($locale = NULL, $short = FALSE)
	{
		$locale = $this->_get_locales($locale);

		$xml = simplexml_load_file(ROOT_PATH.'/pub/es.xml');

		$countries = array();
		foreach($xml->localeDisplayNames->territories->children() as $territory)
		{
			$type = (string) $territory['type'];
			if (ctype_upper($type))
			{
				if ( (!array_key_exists($type, $countries)) || (NULL===$territory['alt']) || ($short && ('short'==(string)$territory['alt'])) )
				{
					$countries[$type] = (string) $territory;
				}
			}
		}

		return $countries;
	}

	function languages($locale = NULL, $short = FALSE)
	{
		$locale = $this->_get_locales($locale);

		$xml = simplexml_load_file(ROOT_PATH.'/pub/es.xml');

		$languages = array();
		foreach($xml->localeDisplayNames->languages->children() as $language)
		{
			$type = (string) $language['type'];
			$languages[$type] = (string) $language;
		}

		return $languages;
	}
}

/* End of file sys/libraries/l10n.php */
