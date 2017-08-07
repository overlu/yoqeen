<?php
    
namespace Yoqeen\Libs;

use \YQ;

class Api extends Core
{

	protected $_page = 'page';
	protected $_m = array();  //实例化mod集合

	function __construct()
	{
		parent::__construct();

		//api result
		$this->result = array(
			'code'		=> 0,			//api code
			'message'	=> 'welcome to yoqeen api',		//api message
			'data'		=> 'jsonData',	//api data
			'other'		=> 'other',		//api other info
		);
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
		return $this->session->getFront('api_user_id');
	}

	protected function needLogin()
	{
		if($this->session->get('api_user_id'))
		{
			return true;
		}
		$this->login();
	}

	protected function login()
	{
		$this->result = array(
			'code'		=> -1,			//api code
			'message'	=> 'need login first',		//api message
			'data'		=> '',	//api data
			'other'		=> '',		//api other info
		);
	}

	protected function paramsError()
	{
		$this->result = array(
			'code'		=> -2,			//api code
			'message'	=> 'params error',		//api message
			'data'		=> '',	//api data
			'other'		=> '',		//api other info
		);
	}

	protected function result()
	{
		echo encode($this->result);
		exit;
	}
}