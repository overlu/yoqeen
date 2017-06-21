<?php

namespace Yoqeen\Libs;

class Effectmod
{

	public $startTime,$endTime,$startMemory,$endMemory,$hostIP;

	function __construct()
	{
		$this->startTime = time();
		$this->startMemory = '';
	}
	
	public function run()
	{
		
	}
	
	final private function effectmodTime()
	{
		
	}

	final private function effectmodMemory()
	{

	}

	public function getHostIP()
	{
		return isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost');
	}
}