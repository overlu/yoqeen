<?php

namespace Yoqeen\Libs;

class Http
{
	public static $yoqeenMod = '';
	public static $yoqeenCon = '';
	public static $yoqeenFun = '';

	//api
	public static $yoqeenApi = false;
	public static $yoqeenApiMod = '';
	public static $yoqeenApiCon = '';
	public static $yoqeenApiFun = '';
	public static $yoqeenApiParams = null;
	public static $yoqeenApiToken = '';

	public static $yoqeenFullUrl = '';
	public static $urlinfo = '';

	//获取完整URL
	public static function getFullUrl()
	{
		if(self::$yoqeenFullUrl)
		{
			return self::$yoqeenFullUrl;
		}
		$requestUri = self::getRequestUrI();
		$scheme = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
		$protocol = strstr(strtolower($_SERVER["SERVER_PROTOCOL"]), "/",true) . $scheme;
		$port = in_array($_SERVER["SERVER_PORT"], ["80","443"]) ? "" : (":".$_SERVER["SERVER_PORT"]);
		$_fullUrl = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $requestUri.'/';
		return $_fullUrl;
	}

	//获取请求URL（不包含基础URL，包含文件夹路径）
	public static function getRequestUrI()
	{
		$requestUri = '';
		if (isset($_SERVER['REQUEST_URI']))
		{
			$requestUri = $_SERVER['REQUEST_URI']; //$_SERVER["REQUEST_URI"] apache
		}
		else
		{
			if (isset($_SERVER['argv']))
			{
				$requestUri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];
			} elseif (isset($_SERVER['QUERY_STRING'])) {
				$requestUri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
			}
		}
		return $requestUri;
	}

	//获取真实请求URL（不包含文件夹路径）
	public static function getRealRequestUrI()
	{
		$realRequestUri = self::getRequestUrI();
		$currentPath = $_SERVER['PHP_SELF'];
		$pathInfo = pathinfo($currentPath);
		$realRequestUri = substr($realRequestUri, strlen($pathInfo['dirname']));
		//$realRequestUri = substr(0,1,$realRequestUri)=='/'
		return $realRequestUri;
	}

	//获取基础URL
	public static function baseUrl()
	{
		if(BASE_URL)
		{
			return BASE_URL;
		}
		$currentPath = $_SERVER['PHP_SELF'];
		$pathInfo = pathinfo($currentPath);
		$hostName = $_SERVER['HTTP_HOST'];
		$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://' ? 'https://' : 'http://';
		return $protocol.$hostName.($pathInfo['dirname'] != "\\"?$pathInfo['dirname']:"")."/";
	}

	public static function praseUrl()
	{
		if(self::$urlinfo)
		{
			return self::$urlinfo;
		}
		$currentPath = $_SERVER['PHP_SELF'];
		$currentPath = pathinfo($currentPath);
		$urlinfo = parse_url(self::getFullUrl());
		$urlinfo['path'] = trim(substr($urlinfo['path'], strlen($currentPath['dirname'])), '/');
		return $urlinfo;
	}

	/**
	 * create application url
	 * @param array($modName, $funtionName/$controllerName, ''/$funtionName) or string
	 * @return string
	 */
	public static function appUrl($array = array())
	{
		$baseUrl = self::baseUrl();
		if(is_string($array))
		{
			if(URLREWRITE)
			{
				return $baseUrl.$array;
			}
			else
			{
				return $baseUrl."?mod={$array}";
			}
		}
		if(empty($array))
		{
			return $baseUrl;
		}
		if(URLREWRITE)
		{
			return $baseUrl.implode('/',$array);
		}
		else
		{
			if(count($array) ==1 )
			{
				return $baseUrl."?mod={$array[0]}";
			} elseif (count($array) == 2) {
				return $baseUrl."?mod={$array[0]}&act={$array[1]}";
			} else {
				$paramsUrl = "mod={$array[0]}&con={$array[1]}&act={$array[2]}";
				unset($array[0],$array[1],$array[2]);
				if(!empty($array))
				{
					foreach ($array as $key => $value)
					{
						$paramsUrl .= "&{$key}={$value}";
					}
				}
				return $baseUrl.'?'.$paramsUrl;
			}
		}
	}

	//判断是否是后台链接
	public static function isBackend()
	{
		return (isset($_REQUEST[YOQEEN_MOD_CODE]) && $_REQUEST[YOQEEN_MOD_CODE]==YOQEEN_BACKEND) || preg_match("|^".self::baseUrl().YOQEEN_BACKEND."[/?#]+|", self::getFullUrl());
	}

	public static function route()
	{
		if(API && self::api())
		{
			self::$yoqeenApi = true;
			return;
		}
		if(self::routeCustom())
		{
			return;
		}
		self::urlRewrite();
		if(!self::$yoqeenMod)
		{
			if(YOQEEN_BACKEND != 'admin' && isset($_REQUEST[YOQEEN_MOD_CODE]) && $_REQUEST[YOQEEN_MOD_CODE]=='admin' && !isset($_SESSION['isBackend']))
			{
				to404();
			}
			/* model code */
			self::$yoqeenMod = isset($_REQUEST[YOQEEN_MOD_CODE]) ? $_REQUEST[YOQEEN_MOD_CODE] : YOQEEN_MOD_DEFAULT;
			/* controllers code */
			self::$yoqeenCon = isset($_REQUEST[YOQEEN_MOD_CON]) ? $_REQUEST[YOQEEN_MOD_CON] : self::$yoqeenMod;
			/* function code */
			self::$yoqeenFun = isset($_REQUEST[YOQEEN_ACT_CODE]) ? $_REQUEST[YOQEEN_ACT_CODE] : YOQEEN_FUN_DEFAULT;
		}

	}

	/**
	 * [urlRewrite url重写规则]
	 * @return
	 */
	public static function urlRewrite()
	{
		$urlinfo = self::praseUrl();
		if(!$urlinfo['path'])
		{
			return;
		}
		if(!URLREWRITE)
		{
			if($urlinfo['path'])
			{
				self::$yoqeenMod = 404;
				return;
			}
		}
		$requestUri = explode('/',$urlinfo['path']);
	//	var_dump($requestUri);die;
		if(!self::argCheck($requestUri))
		{
			self::$yoqeenMod = 404;
			return;
		}
		if(URLREWRITE)
		{
			if(YOQEEN_BACKEND != 'admin' && $requestUri[0] == 'admin' && !isset($_SESSION['isBackend']))
			{
				to404();
			}
			if($requestUri[0] == YOQEEN_BACKEND)
			{
				$requestUri[0] = 'admin';
			}
			if (count($requestUri) ==1)
			{
				self::$yoqeenMod = $requestUri[0] ? $requestUri[0] : YOQEEN_MOD_DEFAULT;
				self::$yoqeenCon = isset($_REQUEST[YOQEEN_MOD_CON]) ? $_REQUEST[YOQEEN_MOD_CON] : self::$yoqeenMod;
				self::$yoqeenFun = isset($_REQUEST[YOQEEN_ACT_CODE]) ? $_REQUEST[YOQEEN_ACT_CODE] : YOQEEN_FUN_DEFAULT;
			} elseif (count($requestUri) ==2) {
				self::$yoqeenMod = $requestUri[0];
				self::$yoqeenCon = $requestUri[0] == 'admin' ? $requestUri[1] : $requestUri[0];
				self::$yoqeenFun = $requestUri[0] == 'admin' ? YOQEEN_FUN_DEFAULT : $requestUri[1];
			} else {
				self::$yoqeenMod = $requestUri[0];
				self::$yoqeenCon = $requestUri[1];
				self::$yoqeenFun = $requestUri[2];
			}
		}
		return;
	}

	/**
	 * [argCheck 检测参数是否正确]
	 * @param  [string or array] $argument
	 * @return [bool]
	 */
	public static function argCheck($argument)
	{
		$pattern = '/^[\.a-zA-Z0-9_]{1,}$/';
		if(is_array($argument))
		{
			foreach ($argument as $value)
			{
				if(!preg_match($pattern, $value))
				{
					return false;
				}
			}
			return true;
		}
		return preg_match($pattern, $argument);
	}

	public static function api()
	{
		/*
		* http://example.com/api/mod.con.fun/paramJsonData/token
		* or
		* http://example.com/api?method=mod.con.fun&params=jsonData&token=tokenCode
		*/
		$urlinfo = self::praseUrl();
		if(!$urlinfo['path'])
		{
			return false;
		}
		$requestUri = explode('/',$urlinfo['path']);
		$count = count($requestUri);
		if($count < 1 || $requestUri[0] != API)
		{
			return false;
		}
	//	var_dump($requestUri);die;
		if(!self::argCheck($requestUri))
		{
			self::$yoqeenApiMod = 404;
			return ;
		}
		if(URLREWRITE)
		{
			if ($count > 1)
			{
				$apiRoute = explode('.', $requestUri[1]);
				$rcount = count($apiRoute);
				if($rcount<2){
					self::$yoqeenApiMod = 404;
				}else{
					self::$yoqeenApiMod = $apiRoute[0];
					self::$yoqeenApiCon = $rcount==2 ? $apiRoute[0] : $apiRoute[1];
					self::$yoqeenApiFun = $rcount==2 ? $apiRoute[1] : $apiRoute[2]; //API输出方法名需要以Api结尾

					if($count == 3)
					{
						self::$yoqeenApiToken = $requestUri[2];
					}
					else
					{
						self::$yoqeenApiParams = $requestUri[2];
						self::$yoqeenApiToken = $requestUri[3];
					}
				}
			}
			else
			{
				self::$yoqeenApiMod = 404;
			}
		}
		else
		{
			if(!isset($_REQUEST['method']) || !isset($_REQUEST['token']))
			{
				self::$yoqeenApiMod = 404;
			}
			else
			{
				$apiRoute = explode('.', $_REQUEST['method']);
				$rcount = count($apiRoute);
				self::$yoqeenApiMod = $apiRoute[0];
				self::$yoqeenApiCon = $rcount==2 ? $apiRoute[0] : $apiRoute[1];
				self::$yoqeenApiFun = $rcount==2 ? $apiRoute[1] : $apiRoute[2]; //API输出方法名需要以Api结尾

				self::$yoqeenApiParams = isset($_REQUEST['params']) ? $_REQUEST['params'] : null;
				self::$yoqeenApiToken = $_REQUEST['token'];
			}
		}

		return true;

	}

	/*
	*	用户自定义路由规则
	*/
	public static function routeCustom()
	{
		// $app			= App::instance();
		$request  	= Request::instance();
		$route		= Route::instance($request);
		// $route = $app->route;
		require_once INCLUDES.DS.'routes.php';
		return $route->end();
	}
}