<?php

namespace Yoqeen\Bootstrap;
use \YQ;

class Load
{

	public static function loadLibsClass($class)
	{
		$class = strtolower($class);
		$classArray = explode('\\',$class);
	//	var_dump($classArray);
		if(count($classArray) < 3)
		{
			return;
		}
		if($classArray[0] == 'yoqeen' && in_array($classArray[1], ['help','libs','hook','shell']) && $classArray[2] != '')
		{
			$yoqeenClass = BP.DS.$classArray[1].DS.($classArray[1]!='libs'?$classArray[2].'.'.$classArray[1]:$classArray[2]).'.php';
			if(is_file($yoqeenClass))
			{
				return include_once $yoqeenClass;
			}
			else
			{
				exit('File Miss: '.$yoqeenClass.' or class ['.$class.'] is not exist.');
				exit;
			}
		}
		if($classArray[0] == 'yoqeen' && $classArray[1] == 'app' && in_array($classArray[3], ['controllers','models','apis']) && $classArray[2] != '' && $classArray[4] != '')
		{
			if(in_array(substr($classArray[4],-3),['mod','lib','api']))
			{
				$yoqeenClass = APP.DS.'code'.DS.$classArray[2].DS.$classArray[3].DS.substr($classArray[4],0,-3).'.'.substr($classArray[4],-3).'.php';
			}
			else
			{
				$yoqeenClass = APP.DS.'code'.DS.$classArray[2].DS.$classArray[3].DS.$classArray[4].'.php';
			}
		//	die($yoqeenClass);
			if(is_file($yoqeenClass))
			{
				return include_once $yoqeenClass;
			}
			else
			{
				exit('File Miss: '.$yoqeenClass.' or class ['.$class.'] is not exist.');
				//YQ::pageError(array('title'=>'404','content'=>'<span style="font-size:14px;font-weight:bold;">404</span><br>Sorry,We Can\'t Find.'),'404','404');
				exit;
			}
		}
	}
}

spl_autoload_register(array(__NAMESPACE__.'\Load','loadLibsClass'));