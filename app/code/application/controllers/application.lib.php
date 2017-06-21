<?php

namespace Yoqeen\App\Application\Controllers;

use Yoqeen\Libs\Lib;
use Yoqeen\Libs\Http;
use \YQ;

class ApplicationLib extends Lib
{

	public $app;
	public $controllers;
	public $models;
	public $design;
	public $name;
	public $model;
	function __construct()
	{
		if(DEVELOPMOD == 'PRODUCTION')
		{
			to404();
		}
		parent::__construct();

		$this->name = isset($_REQUEST['appname'])?strtolower($_REQUEST['appname']):false;
		$this->app = $this->name?APP.DS.'code'.DS.$this->name:false;
		$this->controllers = $this->app?$this->app.DS.'controllers':false;
		$this->apis = $this->app?$this->app.DS.'apis':false;
		$this->models = $this->app?$this->app.DS.'models':false;
		$this->design = $this->app?APP.DS.'design'.DS.'base'.DS.$this->name:false;
		$this->file = YQ::help('file');
	//	$this->model = YQ::MODEL('application');

	}

	public function indexAct()
	{
		$apps = $this->file->read(APP.DS.'code', false);
		$this->assign('apps', $apps);
		$this->template('application');
		$this->page();
	}

	public function addAct()
	{
		$this->template('add', 'application');
		$this->page();
	}

	public function removeAct()
	{
		$app = isset($_REQUEST['app']) ? $_REQUEST['app'] : '';
		$this->assign('app', $app);
		$this->template('remove','application');
		$this->page();
	}

	public function lockAct()
	{
		$app = isset($_REQUEST['app']) ? $_REQUEST['app'] : '';
		$this->assign('app', $app);
		$this->template('lock','application');
		$this->page();
	}

	/**
	 * check app
	 * @return error
	 */
	private function appCheck()
	{
		if($this->name && !preg_match('/^[a-zA-Z0-9_]{1,}$/', $this->name))
		{
			$this->ajax['message'] = 'APP Name should used in [a-zA-Z0-9_]';
			$this->ajax();
		}
		if($this->name == '404')
		{
			$this->ajax['message'] = '404 APP can\'t be defined.';
			$this->ajax();
		}
		if(!$this->app)
		{
			$this->ajax['message'] = 'APP is not defined.';
			$this->ajax();
		}
		if(is_dir($this->app))
		{
			$this->ajax['message'] = 'APP '.$this->name.' code has already exist;';
			$this->ajax();
		}
		if(is_dir($this->design))
		{
			$this->ajax['message'] = 'APP '.$this->name.' design has already exist;';
			$this->ajax();
		}
	}

	public function createApplicationAjax()
	{

		$this->appCheck();
		//controller
		$this->file->create($this->controllers.DS.$this->name.'.lib.php',$this->getTemplate('c'));
		//api
		$this->file->create($this->apis.DS.$this->name.'.api.php',$this->getTemplate('a'));
		//model
		$this->file->create($this->models.DS.$this->name.'.mod.php',$this->getTemplate('m'));
		//template
		$this->file->makeDir($this->design.DS.'block');
		$this->file->create($this->design.DS.$this->name.'.tpl',"<div style='text-align:center'><span style='color:#108ee9;font-weight:bold;'>".$this->name."</span> has done.</div>");
		//success
		// $modUrl = Http::appUrl([$this->name]);
		// YQ::pageError(
		// 	array(
		// 		'title'=>'Success',
		// 		'content'=>"APP <span style='color:red;font-weight:bold;' >".$this->name."</span> has be created;click <a href='{$modUrl}'>here</a> to view. "
		// 	)
		// );
		$this->ajax['code'] = 1;
		$this->ajax['message'] = 'APP create success';
		$this->ajax['other'] = $this->name;
		$this->ajax();
	}

	public function removeApplicationAjax()
	{
		if(!$this->app)
		{
			$this->ajax['message'] = 'APP is not defined.';
			$this->ajax();
		}
		if(!is_dir($this->app))
		{
			$this->ajax['message'] = 'APP '.$this->name.' code does not exist.';
			$this->ajax();
		}
		if(file_exists($this->app.DS.'.lock'))
		{
			$this->ajax['message'] = 'APP was locked, cannot  remove.';
			$this->ajax();
		}
		$this->file->truncate($this->app);
		$this->file->truncate($this->design);
		$this->ajax['code'] = 1;
		$this->ajax['message'] = 'APP remove success.';
		$this->ajax();
	}

	public function lockApplicationAjax()
	{
		if(!$this->app)
		{
			$this->ajax['message'] = 'APP is not defined.';
			$this->ajax();
		}
		if(!is_dir($this->app))
		{
			$this->ajax['message'] = 'APP '.$this->name.' code does not exist.';
			$this->ajax();
		}
		if(file_exists($this->app.DS.'.lock'))
		{
			$this->file->filesDelete($this->app.DS.'.lock');
			$this->ajax['code'] = 1;
			$this->ajax['message'] = 'APP unlock success';
			$this->ajax['other'] = 'unlock';
			$this->ajax();
		}
		$this->file->create($this->app.DS.'.lock',time());
		$this->ajax['code'] = 1;
		$this->ajax['message'] = 'APP lock success';
		$this->ajax['other'] = 'lock';
		$this->ajax();
	}

	private function getTemplate($type='c')
	{
		if($type == 'c')
		{
			$content = (string)$this->file->get(APP.DS.'code'.DS.'application'.DS.'template'.DS.'controllers.template','all');
			$content = str_replace('[CONTROLLERS]',ucfirst(strtolower($this->name))."Lib",$content);
			$content = str_replace('[APPNAME]',ucfirst(strtolower($this->name)),$content);
		} elseif($type == 'a') {
			$content = (string)$this->file->get(APP.DS.'code'.DS.'application'.DS.'template'.DS.'api.template','all');
			$content = str_replace('[APIS]',ucfirst(strtolower($this->name))."Api",$content);
			$content = str_replace('[APPNAME]',ucfirst(strtolower($this->name)),$content);
			$content = str_replace('[APPAPI]',strtolower($this->name).".".strtolower($this->name).".demo",$content);
		} elseif($type == 'm') {
			$content = (string)$this->file->get(APP.DS.'code'.DS.'application'.DS.'template'.DS.'models.template','all');
			$content = str_replace('[MODELS]',ucfirst(strtolower($this->name))."Mod",$content);
			$content = str_replace('[APPNAME]',ucfirst(strtolower($this->name)),$content);
		} else {

		}
		return $content;
	}
}