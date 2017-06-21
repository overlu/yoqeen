<?php

namespace Yoqeen\App\Admin\Controllers;

use Yoqeen\Libs\Lib;
use \YQ;

class AdminLib extends Lib
{
	protected $con;

	function __construct()
	{
		parent::__construct();
		$this->con = ltrim(strrchr(rtrim(strtolower(get_class($this)),'lib'),"\\"),"\\");
		// if($this->con != 'login')
		// {
		// 	$this->needLogin();
		// }
		$this->session->cookie('con',$this->con);
	}
	
	public function indexAct()
	{
		redirectUrl(YQ::baseUrl('admin/index'));
	}

	public function __get($name)
	{
		if($name == "model")
		{
			return YQ::MODEL('admin', $this->con);
		}
		if(strrchr($name,"Model") == "Model")
		{
			$str = substr($name, 0, -5);
			$arr = preg_split("/(?=[A-Z])/", $str);
			if(count($arr) == 2)
			{
			//	dump($arr);
				return YQ::MODEL('admin',strtolower($arr[0]),strtolower($arr[1]));
			}
			else
			{
				return YQ::MODEL('admin',$str);
			}
		}
		if(substr($name, 0, 4) == 'help')
		{
			$type = strtolower(substr($name, 4));
			return YQ::help($type);
		}
	}

	public function isLogin()
	{
		return $this->session->getBackend('user_id');
	}

	protected function needLogin()
	{
		if($this->session->getBackend('user_id'))
		{
			return true;
		}
		$loginUrl = '/admin/login';
		redirectUrl($loginUrl);
	}

	protected function checkAutoLogin()
	{
		if($this->isLogin())
		{
			return true;
		}
		if(isset($_COOKIE['_ab']) && $_COOKIE['_ab']=='1' && isset($_COOKIE['_ib']) && isset($_COOKIE['_lb']))
		{
			$_k = '_L8a$1,.%Hz9%';
			$user_id = substr($_COOKIE['_ib'],2);
			if($_COOKIE['_lb']==md5($_k.$user_id.getIp()))
			{
				$user = $this->loginModel->loadOne(['user_id'=>$user_id]);
				if($user)
				{
					$user = array(
						'user_id' => $user_id,
						'user'	=> $user
					);
					return $this->session->setBackend($user);
				}
			}
		}
	}

	protected function searchReset()
	{
		$this->session->cookie('search',null);
		$this->session->cookie('order',null);
		$this->session->cookie('sort',null);
		$this->session->cookie('p',null);
		unset($_COOKIE['search'],$_COOKIE['order'],$_COOKIE['sort'],$_COOKIE['p']);
	}
}