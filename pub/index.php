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

/***********************************************************
  Vevui configuration options
 **********************************************************/

/* Path where the sys folder is located */
$sys_path = '../sys';

/* Path where the app folder is located */
$app_path = '../app'; 

/* Controller loaded by default */
$default_controller = 'sample'; 

/***********************************************************
  DON'T EDIT BELOW THIS LINE (UNLESS YOU ARE A DEVELOPER ;)
 **********************************************************/

define('VEVUI_VERSION', '0.1a');

$dirname_path = dirname(__FILE__);

define('SYS_PATH', $dirname_path.'/'.$sys_path);
define('APP_PATH', $dirname_path.'/'.$app_path);

require(SYS_PATH.'/core/core.php');

/* End of file pub/index.php */

