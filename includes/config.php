<?php

//path
define('PS', PATH_SEPARATOR);      /* : or ; */
define('BP', dirname(dirname(__FILE__))); /*base dir*/
define('BASE_URL', ''); /* base url */
define('LIBS', BP.DS.'libs');
define('APP', BP.DS.'app');
define('LIBJS', BP.DS.'libjs');
define('SKIN', BP.DS.'skin');
define('INCLUDES', BP.DS.'includes');
define('ERRORS', BP.DS.'errors');
define('REPORTS', BP.DS.'reports');
define('HELP', BP.DS.'help');
define('EPD', BP.DS.'Epd');
define('MEDIA', BP.DS.'media');
define('HOOK', BP.DS.'hook');

//image

define('NO_IMAGE','media/image/no_image.png');
define('IMAGE_LOADING','media/image/loading.gif');
//class
define('YOQEEN_MOD_CODE', 'mod');
define('YOQEEN_ACT_CODE', 'act');
define('YOQEEN_MOD_CON', 'con');
define('YOQEEN_MOD_DEFAULT', 'index');
define('YOQEEN_FUN_DEFAULT', 'index');

//theme
define('YOQEEN_THEME', 'green');

//backend
//define('YOQEEN_BACKEND','admin');
define('YOQEEN_BACKEND','backend');

//db
define('HOST_SELECT', false); // connect the default host
if(HOST_SELECT)
{
	include_once 'database.php';
}
define('DB_SELECT', true);   // connect the default db

//cache
define('CACHE', false);	//cache
define('CACHETIME', 1); //cache time
define('HTMLCOMPRESS', false);	// html compress
define('JS_VERSION', '1.0.0');
define('CSS_VERSION', '1.0.0');

//session
//define('SESSIONDIR','2;'.BP.DS.'var'.DS.'session');
define('SESSIONDIR', BP.DS.'var'.DS.'session');
define('SESSION_TIME', '1440');
define('SESSION_NAME','8A3C12A9E3C71726A6242D22404405D2');

//url
define('URLREWRITE', true);

//email
define('EMAIL_SELECT', true);	//email config
if(EMAIL_SELECT)
{
	include_once 'email.php';
}

//debug
define('DEBUG', true);  //debug mode
define('EFFECTMOD', true); // performance adjustment
define('DEVELOPMOD', 'DEVELOPMENT'); //develop model [ PRODUCTION or DEVELOPMENT ]

//api
define('API', 'api');	//api path
define('APIKEY', 'canoiyr3290jcmn2021rmmca9u02r2mcp0q');	//api key

//version
define('VERSION', '1.0.0');

//key
define('KEY','canoiywqfqwfw2wqfqmmca9u02r2mcp0q');

//page
define('PAGE_TITLE','YOQEEN FRAMEWORK');

//css
define('CSSFRONTEND', 'font-awesome.css, base.css, main.css, responsive.css');
define('CSSBACKEND', 'font-awesome.css, base.css, main.css, responsive.css');

//js
define('JSFRONTEND', 'jquery.cookie.js, main.frontend.js');
define('JSBACKEND', 'jquery.cookie.js, main.backend.js');

/**
 * hook
 */
include_once 'hook.php';