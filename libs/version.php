<?php

namespace Yoqeen\Libs;

class Version
{

	function __construct()
	{
	}

	/**
	 | 当前version
	 */
	public static function current()
	{
		$lock = json_decode(\YQ::help('file')->get(APP.DS.'sql'.DS.'lock'), true);
		return $lock['version'] ? $lock['version'] : 'v0.0.0';
	}

	/**
	 | 最新version
	 */
	public static function lastest()
	{
		$fileClass = \YQ::Help('file');
		$sql_files = $fileClass->read(APP.DS.'sql');
		$lastest_version = self::current();
		foreach ($sql_files as $sql_file)
		{
			if($fileClass->name($sql_file) != 'lock' && version_compare($fileClass->name($sql_file, '.php'), $lastest_version, '>'))
			{
				$lastest_version = $fileClass->name($sql_file, '.php');
			}
		}
		return $lastest_version;
	}
}