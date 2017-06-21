<?php

namespace Yoqeen\Libs;

use \YQ;

class Tpl extends Core
{

	function __construct()
	{
		parent::__construct();
	}

	/*
	if user is login
	$userType: backend | front
	*/
	public function isLogin($userType = 'front')
	{
		if($userType != 'front')
		{
			$userType = 'backend';
		}
		return $this->session->get('user_id',$userType);
	}

	/*
	get session
	*/
	public function session($key, $type = 'common')
	{
		return $this->session->get($key, $type);
	}

	public function isApp($mod)
	{
		return $mod == \Yoqeen\Libs\Http::$yoqeenMod;
	}

	public function isCon($con)
	{
		return $con == \Yoqeen\Libs\Http::$yoqeenCon;
	}

	public function isFun($fun)
	{
		return $fun == \Yoqeen\Libs\Http::$yoqeenFun;
	}

	public function __call($method,$args)
	{
		if(substr($method, 0, 2) == 'is' && substr($method, -3) == 'Con')
		{
			$type = strtolower(substr($method, 2,-3));
			return $this->isCon($type);
		}
		if(substr($method, 0, 2) == 'is' && substr($method, -3) == 'Fun')
		{
			$type = strtolower(substr($method, 2,-3));
			return $this->isFun($type);
		}
		if(substr($method, 0, 2) == 'is')
		{
			$type = strtolower(substr($method, 2));
			return $this->isApp($type);
		}
	}

	public function currentApp($mod, $class = 'active')
	{
		if($this->isApp($mod))
		{
			echo $class;
		}
	}

	public function currentAdminApp($con, $class = 'active')
	{
		if(\Yoqeen\Libs\Http::$yoqeenMod == 'admin' && $this->isCon($con))
		{
			echo $class;
		}
	}

	public function userNotifications()
	{
		return YQ::Model('notification')->num($this->user('user_id'));
	}
}