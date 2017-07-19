<?php
define('YOQEEN_START', microtime(true));
/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so we do not have to manually load any of
| our application's PHP classes. It just feels great to relax.
|
*/

require_once HELP.DS."function.php";

require_once INCLUDES.DS.'bootstrap.php';

require_once __DIR__.'/../vendor/autoload.php';