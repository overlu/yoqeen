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

		$this->model = YQ::MODEL(ltrim(strrchr(rtrim(strtolower(get_class($this)),'api'),"\\"),"\\"));

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
		if(strrchr($name,"Model") == "Model")
		{
			if(!isset($this->_m[$name]))
			{
				$this->_m[$name] = YQ::MODEL(substr($name, 0, -5));
			}
			return $this->_m[$name];
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