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

$haanga = array
(
	'template_dir' => APP_PATH.'/v/',
	/* donde los archivos "compilados" PHP serán almacenados */
	'cache_dir' => '/var/tmp/Haanga/cache',
	/* Por defecto es TRUE, pero debe ser falso si ya se cuenta con un autoloader */
	'autoload' => TRUE,
	'compiler' => array	/* opts for the tpl compiler */
		(
			/* Avoid use if empty($var) */
			'if_empty' => FALSE,
			/* we're smart enought to know when escape :-) */
			'autoescape' => FALSE,
			/* let's save bandwidth */
			'strip_whitespace' => TRUE,
			/* call php functions from the template */
			'allow_exec' => TRUE,
			/* global $global, $current_user for all templates */
			'global' => array('globals'),
		),
);

/* End of file app/e/ha.php */
