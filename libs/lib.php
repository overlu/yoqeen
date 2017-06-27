<?php

namespace Yoqeen\Libs;

use \Yoqeen\Libs\Http;
use \YQ;

class Lib extends Core
{
	public $_page = 'page';
	public $title = "";
	function __construct()
	{
		parent::__construct();
		YQ::$template = ''; // tempalte file reset
	//	YQ::$block = array(); //block file reset
		YQ::$tpl_vars = array(); //assign var reset

		$this->ajax = array(
			'code'		=> 0,		//return code
			'message'	=> '',		//ruturn message
			'data'		=> '',		//return data
			'other'		=> '',		//return other info
		);
		$this->hook();
		$this->checkAutoLogin();
	}

	protected function assign($name,$var)
	{
		$var = isset($var)?$var:false;
		YQ::$tpl_vars[$name] = $var;
	}

	protected function page($page=null,$fd=null/*$_pageTemplate='page'*/)
	{
		// $_pageTemplate = 'page'.DS.$_pageTemplate;
		// YQ::flushed($_pageTemplate);
		$page = $page==null?$this->_page.DS.$this->_page:($fd==null?$this->_page.DS.$page:$fd.DS.$page);
		$this->assign('title', $this->title ? $this->title."——".PAGE_TITLE : PAGE_TITLE );
		YQ::flushed($page);
	}

	protected function template($val,$fd=null)
	{
		$val = strtolower($val);
		YQ::$template = $fd==null?$val.DS.$val:$fd.DS.$val;

	}

	public function __get($name)
	{
		if($name == "model")
		{
			$mod = ltrim(strrchr(strtolower(get_class($this)),"\\"),"\\");
			if(substr($mod, -3) != 'lib')
			{
				throw new \Exception("Controllers Class Name Should End with [lib]", 1);
				
			}
			$mod = substr($mod, 0, -3);
			return YQ::MODEL($mod);
		}
		if(strrchr($name,"Model") == "Model")
		{
			$str = substr($name, 0, -5);
			$arr = preg_split("/(?=[A-Z])/", $str);
			if(count($arr) == 2)
			{
			//	dump($arr);
				return YQ::MODEL(strtolower($arr[0]),strtolower($arr[1]));
			}
			else
			{
				return YQ::MODEL($str);
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
		return $this->session->getFront('user_id');
	}

	protected function needLogin()
	{
		if($this->session->getFront('user_id'))
		{
			return true;
		}
		$this->session->setCommon(["referer"=>YQ::getFullUrl()]);
		$loginUrl = YQ::baseUrl('login');
		redirectUrl($loginUrl);
	}

	protected function ajax()
	{
		if(isAjax())
		{
			header('Content-type: application/json');
			echo json_encode($this->ajax);
			exit;
		}
	}

	protected function key($key='')
	{
		if(!$key)
		{
			$key = md5(KEY.microtime());
			$this->session->setCommon(['key'=>$key]);
			return $key;
		}
		if(isset($_REQUEST['key']))
		{
			if($this->session->getCommon('key') == $_REQUEST['key'])
			{
				return true;
			}
		}
		return false;
	}

	protected function checkAutoLogin()
	{
		if($this->isLogin())
		{
			return true;
		}
		if(isset($_COOKIE['_a']) && $_COOKIE['_a']=='1' && isset($_COOKIE['_i']) && isset($_COOKIE['_l']))
		{
			$_k = '_L81,.%H9%';
			$user_id = substr($_COOKIE['_i'],2);
			if($_COOKIE['_l']==md5($_k.$user_id.getIp()))
			{
				$user = $this->loginModel->loadOne($user_id);
				if($user)
				{
					$user = array(
						'user_id' => $user_id,
						'user'	=> $user
					);
					$this->loginModel->updateLogin($user_id);
					return $this->session->setFront($user);
				}
			}
		}
	}

	protected function userRefresh()
	{
		$user = $this->userModel->loadOne($this->user('user_id'));
		if($user)
		{
			$user = array(
				'user_id' => $this->user('user_id'),
				'user'	=> $user
			);
			return $this->session->setFront($user);
		}
	}
}