<?php

namespace Yoqeen\Help;

/**
 | 文件操作类
 */
class File
{
	function __construct()
	{
	}

	/**
	 | 创建文件
	 | $file 文件
	 | $content 内容
	 | $mod文件的访问类型
	 | @return  文件名
	 */
	function create($file, $content = '', $mod = "w")
	{
		$_file = explode(DS, $file);
		$dir = substr($file, 0, 0-strlen($_file[count($_file)-1]));
		$this->makeDir($dir);
		switch($mod)
		{
			case "r":
				$mod = "r+";
				break;
			default:
				$mod = $mod;
		}
		$open = fopen($file, $mod);
		fwrite($open, $content);
		fclose($open);
		clearstatcache();
		return $file;
	}

	/**
	 | 获取文件名
	 | $file 文件
	 | $suffix 是否输出文件后缀名 null不输出
	 | @return  文件名 or false
	 */
	function name($file,$suffix=NULL)
	{
		if(is_file($file))
		{
			return $suffix == NULL ? basename($file) : basename($file, $suffix);
		}
		return false;
	}

	/**
	 | 获取目录名
	 | $file 文件
	 | @return  目录名 or false
	 */
	function dirName($file)
	{
		if(is_file($file))
		{
			return dirname($file).DS;
		}
		return false;
	}

	/**
	 | 删除文件（文件集合）
	 | $file 文件 [为字符串或者数组]
	 | @return  未删除的文件 或者 true/false
	 */
	function remove($file)
	{
		if(is_array($file))
		{
			foreach($file as $key => $val)
			{
				if(is_file($val))
				{
					if(unlink($val)) unset($file[$key]);
				}
			}
			return empty($file) ? true : $file;
		}
		if(is_file($file) && unlink($file)) return true;
		return false;
	}

	/**
	 | 删除空目录
	 | $dir 目录 [为字符串或者数组]
	 | @return  未删除的目录 或者 true/false
	 */
	function dirsDelete($dir)
	{
		if(is_array($dir))
		{
			$f = array();
			foreach($dir as $key => $val)
			{
				$val = substr($val,-1) != DS?$val.DS:$val;
				if(is_dir($val) && !rmdir($val)) { $f[$key] = $val; }
			}
			return empty($f)?true:$f;
		}
		if(is_dir($dir) && rmdir($dir)) return true;
		return $dir;
	}

	/**
	 | 关闭打开的文件
	 */
	function close($file)
	{
		return fclose($file);
	}

	/**
	 | 获取文件的内容
	 | $file 文件
	 | $type: c, 返回一个字符
	 |		  s, 返回一行字符串
	 |		 ss, 返回一行字符串并移除html和php标签
	 |		all, 返回文件内容,
	 |	  array, 返回数组
	 | $length 内容长度
	 | $tags 不会被过滤的标签，用','隔开
	 | @return  文件的内容 or false
	 */
	function get($file, $type="all", $length="1024", $tags=NULL)
	{
		if(file_exists($file))
		{
			switch($type)
			{
				case "c":
					$file = fopen($file, "r");
					$content = fgetc($file);
					fclose($file);
					break;
				case "s":
					$file = fopen($file, "r");
					$content = fgets($file, $length);
					fclose($file);
					break;
				case "ss":
					$file = fopen($file, "r");
					$content = fgetss($file, $length, $tags);
					fclose($file);
					break;
				case "all":
					$content = file_get_contents($file);
					break;
				case "array":
					$content = file($file);
					break;
				default:
					$content = file_get_contents($file);
			}
			clearstatcache();
			return $content;
		}
		return false;
	}

	/**
	 | 获取文件的相关时间
	 | $file 文件
	 | $type: a, 返回最近访问时间
	 |		  c, 返回创建时间
	 |	      m, 返回最近修改时间
	 |	    all, 返回所有时间
	 | @return  时间
	 */
	function time($file,$type="all")
	{
		if(file_exists($file))
		{
			switch($type)
			{
				case "a":
					$time = fileatime($file);
					break;
				case "c":
					$time = filectime($file);
					break;
				case "m":
					$time = filemtime($file);
					break;
				case "all":
					$time = array(
						"fileatime" => fileatime($file),
						"filectime" => filectime($file),
						"filemtime" => filemtime($file)
				    );
					break;
				default:
					$time = array(
						"fileatime" => fileatime($file),
						"filectime" => filectime($file),
						"filemtime" => filemtime($file)
				    );
			}
			clearstatcache();
			return $time;
		}
		return false;
	}

	/**
	 | 返回文件的信息
	 */
	function info($file)
	{
		$info = stat($file);
		clearstatcache();
		return $info;
	}

	/**
	 | 修改文件名
	 */
	function reName($oldname, $newname)
	{
		return rename($oldname,$newname);
	}

	/**
	 | 递归创建目录
	 */
	public function makeDir($dir='', $mod=0755)
	{
		if(!$dir) return false;
		/*$_dir = explode(DS,$dir);
		$count = count($_dir);
		for($i=0; $i<$count; $i++)
		{
			$_d = array();
			for($j=0; $j<=$i; $j++)
			{
				$_d[$j] = $_dir[$j];
			}
			$_ndir = implode(DS, $_d);
			if(!is_dir($_ndir)) mkdir($_ndir, $mod);
		}
		return true;*/
		if(!is_dir($dir))
		{
			return mkdir($dir, $mod, true);
		}
		else
		{
			return true;
		}
		
	}

	/**
	 | 查找目录
	 | $dir 目录
	 | $pattern 文件类型
	 */
	function select($dir, $pattern="*")
	{
		$dir = substr($dir,-1) != DS ? $dir.DS : $dir;
		if(is_dir($dir))
		{
			return glob($dir.$pattern);
		}
		return false;
	}

	/**
	 | 读取文件夹，整个返回数组
	 | $dir 读取的目录
	 | $bl 是否返回全路径 true是
	 */
	function read($dir, $bl=true)
	{
		$dir = substr($dir,-1) !=  DS ? $dir.DS : $dir;
		if(is_dir($dir))
		{
			$temp = array();
			$dirs = opendir($dir);
			while($d = readdir($dirs))
			{
				if($d != "." && $d != "..")
				array_push($temp, $bl ? $dir.$d : $d);
			}
			closedir($dirs);
			return $temp;
		}
		return [];
	}

	/**
	 | 清空目录，慎用
	 */
	function truncate($dir)
	{
		$temp = $this->read($dir);
		foreach($temp as $val)
		{
			is_file($val) ? unlink($val) : $this->truncate($val);
		}
		return rmdir($dir);
	}

	/**
	 | 根据模板创建文件
	 | $template 模板文件
	 | $templateData 数组 key模板文件需要替换字段 value模板文件替换字段的值
	 */
	function template($template,$templateData)
	{
		if(!is_file($template) || !is_array($templateData)){ return ''; }
		$template = $this->get($template);
		$templateData = array_change_key_case($templateData,CASE_UPPER);
		foreach($templateData as $key=>$val)
		{
			$template = str_replace("[".$key."]",$val,$template);
		}
		return $template;
	}

}