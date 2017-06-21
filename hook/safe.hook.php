<?php

namespace Yoqeen\Hook;

/**
 * 安全验证类
 */
class Safe
{

	private $getfilter = "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
    private $postfilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
    private $cookiefilter = "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";

	function __construct()
	{
	}

	public function hook()
	{
		if($_GET)
		{
	        foreach($_GET as $key => $value)
	        {
	        	$this->stopattack($key, $value, $this->getfilter);
	        }
		}
		if($_POST)
		{
	        foreach($_POST as $key => $value)
	        {
	        	$this->stopattack($key, $value, $this->postfilter);
	        }
	    }
	    if($_COOKIE)
	    {
	    	foreach($_COOKIE as $key => $value)
	        {
	        	$this->stopattack($key, $value, $this->cookiefilter);
	        }
	    }
	}

    /**
     * 参数检查并写日志
     */
    public function stopattack($StrFiltKey, $StrFiltValue, $ArrFiltReq)
    {
        if(is_array($StrFiltValue))
        {
        	$StrFiltValue = implode($StrFiltValue);
        }
        if (preg_match("/".$ArrFiltReq."/is",$StrFiltValue) == 1)
        {
            $this->log($_SERVER["REMOTE_ADDR"]."   ".$_SERVER["PHP_SELF"]."   ".$_SERVER["REQUEST_METHOD"]."   ".$StrFiltKey." => ".$StrFiltValue);
        //    $this->showmsg('您提交的参数非法,系统已记录您的本次操作！','',0,1);
        }
    }
    /**
     * SQL注入日志
     */
    public function log($log){
        \YQ::log($log, 'sql_attact.log', 'WARING');
    }

	/**
	 *xss过滤
	 * @param $string
	 * @param $low 安全别级低
	 */
	public function xss(&$string, $low = False)
	{
		if (! is_array ( $string ))
		{
			$string = trim ( $string );
			$string = strip_tags ( $string );
			$string = htmlspecialchars ( $string );
			if ($low)
			{
				return True;
			}
			$string = str_replace ( array ('"', "\\", "'", "/", "..", "../", "./", "//" ), '', $string );
			$no = '/%0[0-8bcef]/';
			$string = preg_replace ( $no, '', $string );
			$no = '/%1[0-9a-f]/';
			$string = preg_replace ( $no, '', $string );
			$no = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';
			$string = preg_replace ( $no, '', $string );
			return True;
		}
		$keys = array_keys ( $string );
		foreach ( $keys as $key )
		{
			$this->xss ( $string [$key] );
		}
	}

}