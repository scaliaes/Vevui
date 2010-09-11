<?php

/***********************************************************
  Vevui configuration options
 **********************************************************/

/* Path where the sys folder is located */
$sys_path = '../sys';

/* Path where the app folder is located */
$app_path = '../app'; 


/***********************************************************
  DON'T EDIT BELOW THIS LINE (UNLESS YOU ARE A DEVELOPER ;)
 **********************************************************/

define('VEVUI_VERSION', '0.1a');

define('SYS_PATH', dirname(__FILE__).'/'.$sys_path);
define('APP_PATH', dirname(__FILE__).'/'.$app_path);

require(SYS_PATH.'/'.'core.php');



