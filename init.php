<?php
defined('IN_YOQEEN') or exit('No permission');

isset($_REQUEST['GLOBALS']) && exit('Access Error');

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

if (version_compare(phpversion(), '5.3.0', '<')===true)
{
    exit('The php version is low, please upgrade first.');
}
set_time_limit(0);

ini_set('date.timezone','Asia/Shanghai');

define('DS', DIRECTORY_SEPARATOR); /* /or\ */

/*
Compilation includes configuration file
*/

require_once __DIR__.DS.'includes'.DS.'config.php';

VERSION or die;

/*
bug
 */
DEBUG ? error_reporting(E_ALL | E_STRICT) : error_reporting(0);

require_once INCLUDES.DS.'develop.php';

/*
Compilation includes lib file
*/
require_once LIBS.DS.'yoqeen.php';

/*
Compilation includes autoload file
*/
require_once INCLUDES.DS.'autoload.php';

require_once INCLUDES.DS."code.php";

