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

// Basic configuration
$config['debug'] = TRUE;
$config['profiling'] = 0.15;
$config['log_errors'] = ROOT_PATH.'/data/errors.db';	// Leave blank or comment out to disable.

// Controller loaded by default
$config['default_controller'] = 'main'; 

// Allowed chars in URI (case insensitive, PCRE style)
// Warning: changing this value may be dangerous, the character '-' must be at the end or escaped
$config['url_chars'] = 'a-z0-9_-';

// Allow query string in URI
$config['query_string'] = FALSE;

// Cache system
$config['cache_path'] = ROOT_PATH.'/cache';

// Routing system (case insensitive, PCRE style)
$config['routes'] =	array
	(
		'^/sample/(.*)' => '/main/index/\\1'
	);
	
/* End of file app/e/app.php */
