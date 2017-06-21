<?php

namespace Yoqeen\Help;

/**
 | Session Cookie操作类
 */
class Session
{
	const SESSION_TYPE = ['common','front','backend','other'];
	/*******************************SESSION*******************************/

	CONST DS = DIRECTORY_SEPARATOR;

	/**
	 | 初始化session目录
	 */
	public function init($sessiondir)
	{
		if(!is_dir($sessiondir)){ mkdir($sessiondir); }
		if(file_exists($sessiondir.'locked')){ return; }
		$string = '0123456789abcdefghijklmnopqrstuvwxyz';
		$length = strlen($string);
		function makeDir($param)
		{
		    if(!file_exists($param) && !is_dir($param))
		    {
			  mkdir($param);
		    }
		}
		for($i = 0; $i < $length; $i++)
		{
		    for($j = 0; $j < $length; $j++)
		    {
			    makeDir($sessiondir.$string[$i]);
			    makeDir($sessiondir.$string[$i].DS.$string[$j]);
		    }
		}
		$open = fopen($sessiondir.'locked','w');
		fwrite($open,time());
		fclose($open);
	}

	/**
	 | 设置session存放目录
	 | $dir session存放目录
	 */
	public function dir($dir)
	{
		return session_save_path($dir);
	}

	/**
	 | 启动session
	 | $expire 过期时间
	 */
	public function start($expire)
	{
		/*if ($expire == 0)
		{
	        $expire = ini_get('session.gc_maxlifetime');
	    }
	    else
	    {
	        ini_set('session.gc_maxlifetime', $expire);
	    }*/
	    session_name(SESSION_NAME);
	    session_start();
	}

	/**
	 | 获取session类型
	 */
	public function type()
	{
		return self::SESSION_TYPE;
	}

	/**
	 | 获取session
	 | $key session键名
	 | $type: session类型， common为通用类型，front为前端类型，backend为后端类型，other为其他类型
	 */
	public function get($key = null, $type='common')
	{
		if(!in_array($type, self::SESSION_TYPE))
		{
			throw new \Exception("session type is not exist", 1);
			exit;
		}
		return $key ? isset($_SESSION[$type][$key]) ? $_SESSION[$type][$key] : '' : $_SESSION;
	}

	/**
	 | 创建session
	 | $session 数组 key：session名称 val：session值
	 | $type: session类型， common为通用类型，front为前端类型，backend为后端类型，other为其他类型
	 */
	public function set($session=array(), $type='common')
	{
		if(!in_array($type, self::SESSION_TYPE))
		{
			throw new \Exception("session type is not exist", 1);
			exit;
		}
		if(!is_array($session))
		{
			return false;
		}
		foreach($session as $key => $val)
		{
			$_SESSION[$type][$key] = $val;
		}
		return true;
	}

	/**
	 | 删除session
	 | $key session键名 string 或者 array
	 | $type: session类型， common为通用类型，front为前端类型，backend为后端类型，other为其他类型
	 */
	public function delete($key, $type='common')
	{
		if(!$key)
		{
			return false;
		}
		if(!in_array($type, self::SESSION_TYPE))
		{
			throw new \Exception("session type is not exist", 1);
			exit;
		}
		if(is_array($key))
		{
			foreach($key as $val){ unset($_SESSION[$type][$val]); }
		} else {
			unset($_SESSION[$type][$key]);
		}
		return true;
	}

	/**
	 | 销毁session
	 */
	public function destroy()
	{
		return session_destroy();
	}

	/*******************************COOKIE*******************************/
	/**
	 | 1.设置cookie的值，把name变量的值设为value
	 | example cookie($name,$value);
	 | 2.新建一个cookie 包括有效期 路径 域名等
	 | example cookie($name,$value,array('expires'=>3600,'path'=>'/','domain'=>'jquery.com'，'secure'=>1, 'httponly'=>1));
	 | 3.新建cookie
	 | example cookie('name','value');
	 | 4.删除一个cookie
	 | example cookie('name',null);
**/
	public function cookie($name='', $value='', $options='')
	{
		if($name)
		{
			if(is_array($options))
			{
				return setcookie($name, $value, $options['expire'], '/', $options['domain'], $options['secure'], $options['httponly']);
			}
			else
			{
				if($value === null)
				{
					return setcookie($name, '', time()-3600, '/');
				}
				if($value)
				{
					return setcookie($name,$value,time()+'604800','/');
				}
				return isset($_COOKIE[$name]) ? $_COOKIE[$name] : '';
			}
		}
		return $_COOKIE;
	}

	public function __call($method, $args)
	{
		if(substr($method, 0, 3) == 'get')
		{
			$type = strtolower(substr($method, 3));
			return $this->get(isset($args[0])?$args[0]:NULL, $type);
		}
		if(substr($method, 0, 3) == 'set')
		{
			$type = strtolower(substr($method, 3));
			return $this->set($args[0], $type);
		}
		if(substr($method, 0, 6) == 'delete')
		{
			$type = strtolower(substr($method, 6));
			return $this->delete($args[0], $type);
		}
	}
}