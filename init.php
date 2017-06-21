<?php
defined('IN_YOQEEN') or exit('No permission');
isset($_REQUEST['GLOBALS']) && exit('Access Error');

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

$yoqeenconfig = __DIR__.DS.'includes'.DS.'config.php';

if(is_file($yoqeenconfig))
{
    include_once $yoqeenconfig;
}
else
{
    exit($yoqeenconfig.' is not exist');
}

if(DEBUG)
{
    error_reporting(E_ALL | E_STRICT);
}
else
{
    error_reporting(0);
}

if(!VERSION)
{
    die;
}

/*
bug
 */
$yoqeendevelop = INCLUDES.DS.'develop.php';
if(is_file($yoqeendevelop))
{
    include_once $yoqeendevelop;
}
else
{
    exit($yoqeendevelop.' is not exist');
}

/*
Compilation includes lib file
*/
$yoqeenlibs = LIBS.DS.'yoqeen.php';
if(is_file($yoqeenlibs))
{
    include_once $yoqeenlibs;
}
else
{
    exit('yoqeen.php is not exist');
}

/*
Compilation includes bootstrap file
*/
$yoqeenbootstrap = INCLUDES.DS.'bootstrap.php';
if(is_file($yoqeenbootstrap))
{
    include_once $yoqeenbootstrap;
}
else
{
    exit($yoqeenbootstrap.' is not exist');
}

/*
Compilation includes function file
*/
$yoqeenhelp = HELP.DS."function.php";
if(is_file($yoqeenhelp))
{
    include_once $yoqeenhelp;
}
else
{
    exit($yoqeenhelp.' is not exist.');
}

$yoqeencode = INCLUDES.DS."code.php";
if(is_file($yoqeencode))
{
    include_once $yoqeencode;
}
else
{
    exit($yoqeencode.' is not exists.');
}