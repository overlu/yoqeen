<?php

/**
 | YQ框架调度类 
 */
final class YQ
{

	/**
	 | 模板文件
	 */
	public static $template;

	/**
	 | 块文件
	 */
//	public static $block = array();

	 /**
	  | 模板文件变量
	  */
	public static $tpl_vars = array();

	/**
	 | 模板css文件集合
	 */
	public static $css = array();

	/**
	 | 模板js文件集合
	 */
	public static $js = array();

	/**
	 | 是否开启缓存
	 */
	public static $cache = CACHE;

	/**
	 | 缓存时间
	 */
	public static $cachetime = CACHETIME;

	/**
	 | html文件是否压缩
	 */
	public static $htmlcompress = HTMLCOMPRESS;

	/**
	 | 实例化类缓存集合
	 */
	public static $new = array();

	/**
	 | 模板主题
	 */
	public static $theme = YOQEEN_THEME;

	function __construct()
	{
	}

	/**
	 | 框架执行入口
	 */
	public static function run()
	{
		if(IN_YOQEEN == 'web')
		{
			/**
			 | session start
			 */
			self::sessionStart();
			Yoqeen\Libs\Http::route();
		}
		
		self::_run();
	}

	/**
	 | 入口
	 */
	private static function _run()
	{

		if(IN_YOQEEN == 'web')
		{
			/**
			 | 判断是否是API路由
			 */
			if(API && Yoqeen\Libs\Http::$yoqeenApi)
			{
				$yoqeenApiMod = Yoqeen\Libs\Http::$yoqeenApiMod;
				$yoqeenApiCon = Yoqeen\Libs\Http::$yoqeenApiCon;
				$yoqeenApiFun = Yoqeen\Libs\Http::$yoqeenApiFun."Api";
				if(is_file(APP . DS . "code" . DS . $yoqeenApiMod . DS . "apis" . DS . $yoqeenApiCon . ".api.php"))
				{
					$yoqeenConClass = "Yoqeen\\App\\".ucfirst(strtolower($yoqeenApiMod))."\\Apis\\".ucfirst(strtolower($yoqeenApiCon))."Api";

					if($obj = new $yoqeenConClass)
					{
						if(method_exists($obj,$yoqeenApiFun))
						{
							$obj->$yoqeenApiFun();
							return;
						}
					}
				}
				self::ApiError();
				return;
			}

			self::$theme = (isset($_SESSION['isBackend']) && Yoqeen\Libs\Http::$yoqeenMod=='admin') ? 'admin' : YOQEEN_THEME;
			/* model code */
			$yoqeenMod = Yoqeen\Libs\Http::$yoqeenMod;
			/* controllers code */
			$yoqeenCon = Yoqeen\Libs\Http::$yoqeenCon ? Yoqeen\Libs\Http::$yoqeenCon : $yoqeenMod;
			/* function code */
			$yoqeenFun = isAjax() ? Yoqeen\Libs\Http::$yoqeenFun.'Ajax' : Yoqeen\Libs\Http::$yoqeenFun.'Act';

			// dd($yoqeenMod, $yoqeenCon, $yoqeenFun);

			if(is_file(APP . DS . "code" . DS . $yoqeenMod . DS . "controllers" . DS . $yoqeenCon . ".lib.php"))
			{
				$yoqeenConClass = "Yoqeen\\App\\".ucfirst(strtolower($yoqeenMod))."\\Controllers\\".ucfirst(strtolower($yoqeenCon))."Lib";
				if($obj = new $yoqeenConClass)
				{
					if(method_exists($obj,$yoqeenFun))
					{
						$obj->$yoqeenFun();
						return;
					}
				}
			}
			to404();
		} elseif (IN_YOQEEN == 'cli') {
			global $argv;
			if(count($argv) > 1)
			{
				if(preg_match('/^[a-z]+$/', $argv[1]))
				{
					$yoqeenCliClass = "Yoqeen\\Shell\\".$argv[1];
					if($obj = new $yoqeenCliClass)
					{
						if(method_exists($obj,'run'))
						{
							$obj->run();
							return;
						}
					}
				}
			}
			exit('  error parameter');
		}
		
	}

	/**
	 | 实例化help类
	 | $help help类名
	 | $args 类的参数 如果不为空则不会被缓存
	 */
	public static function help($help, $args='')
	{
		$helpfile = HELP . DS . strtolower($help) . ".help.php";
		if(is_file($helpfile))
		{
			$help = "Yoqeen\\Help\\".ucfirst($help);
			if(!isset(self::$new[$help]) || $args)
			{
				self::$new[$help] =  $args === true ? new $help : new $help($args);
			}
			return self::$new[$help];
		}
		throw new \Exception("Error!: No help file ".$helpfile, 1);
	}

	/**
	 | 引入模板文件
	 | 如果$bl为true或者array，则读取文件内容
	 */
	public static function tpl($filename, $bl=false)
	{
		if(!empty(self::$tpl_vars)) 
		{
			extract(self::$tpl_vars);
		}
		$file = APP.DS."design".DS.self::$theme.DS.$filename.".tpl";
		if(!is_file($file))
		{
			$file = APP.DS."design".DS.'base'.DS.$filename.".tpl";
			if(!is_file($file))
			{
				return false;
			}
		}
		if(!$bl)
		{
			$tpl = new Yoqeen\Libs\Tpl;
			include_once $file;
			return $file;
		}
		else
		{
			$helpFile = self::help('file');
			if(is_array($bl))
			{
				return $helpFile->template($file,$bl);
			}
			else
			{
				return $helpFile->get($file);
			}
		}
		return;
	}

	/**
	 | 引入模板布局文件
	 */
	public static function flushed($_pageTemplate)
	{
		if(!empty(self::$tpl_vars)) 
		{
			extract(self::$tpl_vars);
		}
		if(self::$cache)
		{
			self::getcachepagefile($_pageTemplate);
			return $_pageTemplate.'/'.self::$template;
		}
		$pagefile = APP . DS . "design" . DS . self::$theme . DS . $_pageTemplate . ".tpl";
		if(is_file($pagefile))
		{
			include_once $pagefile;
			return $_pageTemplate.'/'.self::$template;
		}
		$pagefile = APP . DS . "design" . DS . 'base' . DS . $_pageTemplate . ".tpl";
		include_once $pagefile;
		return $_pageTemplate.'/'.self::$template;
	}

	/**
	 * 获取缓存文件
	 */
	public static function getcachepagefile($page)
	{
		$file = self::help('file');
		$cachefile = BP . DS . 'var' . DS . 'pagecache' . DS . 'yoqeen_' . self::$theme . '_' . $page . '_' . self::$template . '_c_';
		if(is_file($cachefile))
		{
			$cachefilecontent = json_decode($file->get($cachefile));
			if(time()>$cachefilecontent[1])
			{
				$file->filesDelete($cachefile);
				return self::createcachepagefile($page);
			}
			else
			{
				echo $cachefilecontent[2];
			}
		}
		else
		{
			return self::createcachepagefile($page);
		}
	}

	/**
	 | 创建缓存文件
	 */
	public static function createcachepagefile($page)
	{
		$pagefile = APP . DS . "design" . DS . self::$theme . DS . $page . ".tpl";
		include_once $pagefile;
		$file = self::help('file');
		$cachefile = BP . DS . 'var' . DS . 'pagecache' . DS . 'yoqeen_' . self::$theme . '_' . $page . '_' . self::$template . '_c_';
		if(self::$cache)
		{
			$cacheid = md5($cachefile);
			$cachetime = self::$cachetime ? (time()+self::$cachetime*60) : 'nolimit';
			$cachecontent = ob_get_contents().'<!--'.$cacheid.'-->';
			if(self::$htmlcompress)
			{
				$cachecontent = compress_html($cachecontent).'<!--'.$cacheid.'-->';   //压缩html
			}
			$file->create($cachefile,json_encode(array($cacheid,$cachetime,$cachecontent)));
		}
		return $cachecontent;
	}

	/**
	 | 输出错误信息
	 | $data 错误信息数据
	 | $tag 文件标签
	 | $html 模板文件，为空则不创建
	 */
	public static function pageError($data = array(), $tag = 'error', $html = null){
		//输出错误信息前清空缓存
		if(ob_get_level())
		{
			if (!count(array_diff(ob_list_handlers(),array('default output handler'))))
			{
				ob_clean();
			}
		//	if(ob_get_status()->name!='zlib output compression')
		//	ob_end_clean();
		}
		$file = self::help('file');
		$data['initcss'] = self::skinUrl('init.css','css');
		$template = ERRORS . DS . $tag . ".ert";
		$template = $file->template($template,$data);
		if(is_null($html))
		{
			die($template);
		}
		$_template = ERRORS . DS . 'html' . DS . $html . '.tpl';
		if(!is_file($_template))
		{
			$file->create($_template,$template);
		}
		include_once($_template);
		die;
		//header('location:'.self::baseUrl().$tag.'/');
	}

	/**
	 | 获取完整URL
	 */
	public static function getFullUrl()
	{
		$requestUri = self::getRequestUrI();
		$scheme = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
		$protocol = strstr(strtolower($_SERVER["SERVER_PROTOCOL"]), "/",true) . $scheme;
		$port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
		$_fullUrl = $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $requestUri.'/';
		return $_fullUrl;
	}

	/**
	 | 获取请求URL（不包含基础URL，包含文件夹路径）
	 */
	public static function getRequestUrI()
	{
		$requestUri = '';
		if (isset($_SERVER['REQUEST_URI'])) {
			$requestUri = $_SERVER['REQUEST_URI']; //$_SERVER["REQUEST_URI"] apache
		} else {
			if (isset($_SERVER['argv'])) {
				$requestUri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];
			} elseif (isset($_SERVER['QUERY_STRING'])) {
				$requestUri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
			}
		}
		return $requestUri;
	}

	/**
	 | 获取真实请求URL（不包含文件夹路径）
	 */
	public static function getRealRequestUrI()
	{
		$realRequestUri = self::getRequestUrI();
		$currentPath = $_SERVER['PHP_SELF'];
		$pathInfo = pathinfo($currentPath);
		$realRequestUri = substr($realRequestUri, strlen($pathInfo['dirname']));
		//$realRequestUri = ltrim($realRequestUri, $pathInfo['dirname']);
		//$realRequestUri = substr(0,1,$realRequestUri)=='/'
		return $realRequestUri;
	}

	/**
	 | 获取基础URL
	 */
	public static function baseUrl($uri='')
	{
		if(BASE_URL)
		{
			return BASE_URL.$uri;
		}
		$currentPath = $_SERVER['PHP_SELF'];
		$pathInfo = pathinfo($currentPath);
		$hostName = $_SERVER['HTTP_HOST'];
		$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://' ? 'https://' : 'http://';
		$url = $protocol.$hostName.($pathInfo['dirname'] != "\\"?$pathInfo['dirname']:"");
		return $uri ? rtrim($url,'/').'/'.$uri : rtrim($url,'/').'/';
	}

	/**
	 | 创建应用URL地址
	 | $array 地址参数 array($modName, $funtionName/$controllerName, ''/$funtionName) or string
	 | return URL
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
	}

	/**
	 | 获取前端皮肤文件url
	 | $file 文件名
	 | $folder 目录名
	 | return SKIN URL
	 */
	public static function skinUrl($file="", $folder="")
	{
		if($file)
		{
			if($folder)
			{
				return is_file(SKIN.DS.'frontend'.DS.self::$theme.DS.$folder.DS.$file) ? self::baseUrl().'skin/frontend/'.self::$theme.'/'.$folder.'/'.$file : self::baseUrl().'skin/base/'.$folder.'/'.$file;
			}
			return 
			is_file(SKIN.DS.'frontend'.DS.self::$theme.DS.$file) ? self::baseUrl().'skin/frontend/'.self::$theme.'/'.$file : self::baseUrl().'skin/base/'.$file;
		}
		return '';
	}

	/**
	 | 获取后台皮肤文件url
	 | $file 文件名
	 | $folder 目录名
	 | return SKIN URL
	 */
	public static function adminSkinUrl($file="", $folder="")
	{
		if($file != "")
		{
			if($folder != "" && is_file(SKIN.DS.'backend'.DS.$folder.DS.$file))
			{
				return self::baseUrl().'skin/backend/'.$folder.'/'.$file;
			}
			if($folder == "" && is_file(SKIN.DS.'backend'.DS.$file))
			{
				return self::baseUrl().'skin/backend/'.$file;
			}
		}
		return false;
	}

	/**
	 | 获取JS文件url
	 | $file 文件名
	 | $folder 目录名
	 | return JS URL
	 */
	public static function jsUrl($file, $folder='')
	{
		if($file != "")
		{
			if($folder != "" && is_file(LIBJS.DS.$folder.DS.$file))
			{
				return self::baseUrl().'libjs/'.$folder.'/'.$file;
			}
			if($folder == "" && is_file(LIBJS.DS.$file))
			{
				return self::baseUrl().'libjs/'.$file;
			}
		}
		return false;
	}

	/**
	 | 加载CSS文件
	 */
	public static function css($type='frontend')
	{
		$css = md5($type.CSS_VERSION.YOQEEN_THEME).'.css';
		$cssFiles = $type=='frontend' ? CSSFRONTEND : CSSBACKEND;
		$cssFiles = array_map('trim', explode(',', $cssFiles));
		$link = '';
		if(DEVELOPMOD == 'PRODUCTION')
		{
			if(!is_file(BP.DS.'var'.DS.'css'.DS.$css))
			{
				self::epd('CSSmin.php');
				$cssmini = new CSSmin();
				$csss = '';
				$cssPath = $type=='frontend' ? SKIN.DS.'frontend'.DS.self::$theme.DS.'css'.DS : SKIN.DS.'backend'.DS.'css'.DS;
				foreach($cssFiles as $cssFile)
				{
					if($type=='frontend' && !is_file($cssPath.$cssFile))
					{
						$cssPath = SKIN.DS.'base'.DS.'css'.DS;
					}
					$csss .= file_get_contents($cssPath.$cssFile);
				}
				file_put_contents(BP.DS.'var'.DS.'css'.DS.$css, $cssmini->run($csss));
			}
			$link = "<link rel='stylesheet' type='text/css' href='".self::baseUrl()."var/css/".$css."' />";
		}
		else
		{
			$cssPath = $type=='frontend' ? self::baseUrl().'skin/frontend/'.self::$theme.'/css/' : self::baseUrl().'skin/backend/css/';
			// dd($cssFiles);
			foreach($cssFiles as $cssFile)
			{
				if($type=='frontend' && !is_file($type=='frontend' ? SKIN.DS.'frontend'.DS.self::$theme.DS.'css'.DS.$cssFile : SKIN.DS.'backend'.DS.'css'.DS.$cssFile))
				{
					$link .= "<link rel='stylesheet' type='text/css' href='".self::baseUrl()."skin/base/css/".$cssFile."' />";
				}
				else
				{
					$link .= "<link rel='stylesheet' type='text/css' href='".$cssPath.$cssFile."' />";
				}
			}
		}
		return $link;
	}

	/**
	 | 加载JS文件
	 */
	public static function js($type='frontend')
	{
		$js = md5($type.JS_VERSION.YOQEEN_THEME).'.js';
		$jsFiles = $type=='frontend' ? JSFRONTEND : JSBACKEND;
		$jsFiles = array_map('trim', explode(',', $jsFiles));
		$script = '';
		if(DEVELOPMOD == 'PRODUCTION')
		{
			if(!is_file(BP.DS.'var'.DS.'js'.DS.$js))
			{
				self::epd('JSMin.php');
				$jss = '';
				foreach($jsFiles as $jsFile)
				{
					$jss .= file_get_contents(LIBJS.DS.$jsFile);
				}
				file_put_contents(BP.DS.'var'.DS.'js'.DS.$js, JSMin::minify($jss));
			}
			$script = "<script type='text/javascript' src='".self::baseUrl()."var/js/".$js."'></script>";
		}
		else
		{
			foreach($jsFiles as $jsFile)
			{
				$script .= "<script type='text/javascript' src='".self::baseUrl()."libjs/".$jsFile."'></script>";
			}
		}
		return $script ;
	}

	/**
	 | 加载第三方文件
	 | $epd 为字符串或者数组
	 */
	public static function epd($epd=null)
	{
		if(!$epd){return false;}
		if(!is_array($epd))
		{
			if(is_file(EPD.DS.$epd)) include_once(EPD.DS.$epd);
			return true;
		}
		foreach($epd as $val)
		{
			if(is_file(EPD.DS.$val)) include_once(EPD.DS.$val);
		}
		return true;
	}

	/**
	 | 获取Media文件url
	 | $file 文件名
	 | $folder 目录名
	 | return Media URL
	 */
	public static function mediaUrl($file, $folder='')
	{
		if($file != "")
		{
			if($folder != "" && is_file(MEDIA.DS.$folder.DS.$file))
			{
				return self::baseUrl().'media/'.$folder.'/'.$file;
			}
			if($folder == "" && is_file(MEDIA.DS.$file))
			{
				return self::baseUrl().'media/'.$file;
			}
		}
		return false;
	}


	/**
	 | 实例化Mod中的MODEL类
	 | $mod 模块名
	 | $model model文件名，为空则为模块名
	 */
	public static function Model($mod,$model=null)
	{
		$model = $model==null ? $mod : $model;
		if($mod && is_file(APP.DS."code".DS.$mod.DS."models".DS.$model.".mod.php"))
		{
		//	include_once LIBS.DS."mod.php";
		//	include_once APP.DS."code".DS.$mod.DS."models".DS.$model.".mod.php";
			$model = "Yoqeen\\App\\".$mod."\\Models\\".$model."Mod";
			if(class_exists($model, true))
			{
				if(!isset(self::$new[$model]))
				{
					self::$new[$model] = new $model;
				}
				return self::$new[$model];
			}
		}
		throw new \Exception("Error!: No model file ".APP.DS."code".DS.$mod.DS."models".DS.$model.".mod.php", 1);
	}

	/**
	 | 引入块文件
	 | $block 块名 {template.block} index.content
	 | 如果$bl为true或者array，则读取文件内容
	 */
	public static function Block($block=null,$bl=false)
	{
		if($block)
		{
			$blockArray = explode('.', $block);
			$count = count($blockArray);
			if( $count > 1 )
			{
				$block = $blockArray[0].DS.'block'.DS.$blockArray[1];
				if( $count > 2 )
				{
					unset($blockArray[0], $blockArray[1]);
					$blockh = implode(DS, $blockArray);
					$block .= DS.$blockh;
				}
				return self::tpl($block,$bl);
			}
		}
		throw new \Exception("Error!: No block file ".$block, 1);
	}

	/**
	 | 判断用户是否登录
	 | $userType: admin | front
	 */
	public static function isLogin($userType = 'front')
	{
		if($userType != 'front')
		{
			$userType = 'backend';
		}
		return self::help('session')->get('user_id',$userType);
	}

	/**
	 | 获取session
	 | $key session键名
	 | $type session类别['common','front','backend','other']
	 */
	public static function session($key, $type = 'common')
	{
		return self::help('session')->get($key, $type);
	}

	/**
	 * 输出API错误
	 */
	public static function apiError()
	{
		$result = array(
			'code'		=> -2,					//api code
			'message'	=> 'params error',		//api message
			'data'		=> '',					//api data
			'other'		=> '',					//api other info
		);
		exit(json_encode($result));
	}

	private static function sessionStart()
	{
		/* session star */
		$session = self::help('session');
		$sessiondir = BP.DS.'var'.DS.'session'.DS;
		$session->init($sessiondir);
		$session->dir(SESSIONDIR);
		$session->start(SESSION_TIME);

		if(Yoqeen\Libs\Http::isBackend() && !$session->getBackend('backend') && !isset($_SESSION['isBackend']))
		{
			$_SESSION['isBackend'] = true;
		}
	}

	public static function hook($hook, $args = '')
	{
		$hookfile = HOOK . DS . $hook . ".hook.php";
		if(is_file($hookfile))
		{
			$hook = "Yoqeen\\Hook\\".ucfirst($hook);
			$hook =  new $hook;
			$hook->hook($args);
		}
		throw new \Exception("Error!: No hook file ".$hook, 1);
	}

	/**
	 | 记录日志
	 | $content 	日志内容
	 | $filename    日志名
	 | $type    	日志类型 {NOTICE INFO ERROR WARING OTHER}
	 */
	public static function log($content, $filename='', $type='INFO')
	{
		$file = self::help('file');
		$dir = BP.DS.'var'.DS.'logs'.DS.date('Y-m-d').DS;
		if(!is_dir($dir))
		{
			$file->makeDir($dir);
		}
		$filename = $filename ? $filename : 'main.log';
		$content = '===  '.strftime("%Y-%m-%d %H:%M:%S").'  ['.strtoupper($type).']  ===   '.$content."\n\r";
		$file->create($dir.$filename, $content, 'a');
		return True;
	}

	/**
	 * 写入数据缓存
	 * key: 文件名
	 * data： 数据 delete/d 删除数据换成
	 * expire: 有效期，null为永久有效
	 */
	public static function data($key, $data = '', $expire = '3600')
	{
		$helpfile = self::help('file');
		$dir = BP.DS.'var'.DS.'data'.DS;
		if(!is_dir($dir))
		{
			$helpfile->makeDir($dir);
		}
		$file = $dir.md5($key).'.data';
		// dd($file);
		if($data)
		{
			if($data == 'delete' || $data == 'd')
			{
				$helpfile->filesDelete($file);
				return false;
			}
			$content = [
				'expire' => $expire ? time()+$expire : 'null',
				'data'	 =>	$data,
			];
			$helpfile->create($file, encode($content), 'a');
			return $data;
		}
		else
		{
			$content = $helpfile->get($file);
			if($content)
			{
				$content = json_decode($content, true);
				if($content['expire'] == 'null')
				{
					return $content['data'];
				}
				else
				{
					return time()<$content['expire'] ? $content['data'] : false;
				}
			}
		}
		return false;
	}

	/**
	 | 实例化Libs中的类
	 | $mod 模块名
	 */
	public static function Lib($lib, $param='')
	{
		if($lib && is_file(LIBS . DS . strtolower($lib) .".php"))
		{
			$model = "YOQEEN\\Libs\\".$lib;
			if(class_exists($model))
			{
				if(!isset(self::$new[$model]) || $param)
				{

					self::$new[$model] = new $model($param);
				}
				return self::$new[$model];
			}
		}
		return false;
	}

	public static function __callStatic($method, $arguments) 
    {
		if(substr($method, 0, 4) == 'help')
		{
			$type = strtolower(substr($method, 4));
			return (isset($arguments) && $arguments) ? self::help($type, $arguments[0]) : self::help($type);
		}
		if(substr($method, 0, 5) == 'model')
		{
			$type = strtolower(substr($method, 5));
			return (isset($arguments) && $arguments) ? self::Model($type, $arguments[0]) : self::Model($type);
		}
		if(is_file(LIBS . DS . strtolower($method) .".php"))
		{
			return (isset($arguments) && $arguments) ? self::Lib($method, $arguments[0]) : self::Lib($method, $arguments);
		}
    }
}