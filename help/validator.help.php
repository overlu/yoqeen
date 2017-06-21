<?php

namespace Yoqeen\Help;

use \YQ;
/**
 * 验证类
 */
class Validator
{

    /**
     * 是否为空值
     */
    public function isEmpty($str)
    {
        $str = trim($str);
        return !empty($str) ? True : False;
    }

	/**
	 * 判断是否是数字，包括整型和浮点型
	 */
	public function isNumber($number)
	{
		return filter_var($number, FILTER_VALIDATE_INT) || filter_var($number, FILTER_VALIDATE_FLOAT);
	}

	/**
	 * 判断是否是整型，$min -> $max
	 */
	public function isInt($int, $min=null, $max=null)
	{
		$options = '';
		if($min && !$max)
		{
			$options = array(
				"options"=>array(
					"min_range"=>$min,
				)
			);
		}
		if($max && !$min)
		{
			$options = array(
				"options"=>array(
					"max_range"=>$max,
				)
			);
		}
		if($min && $max && $max > $min)
		{
			$options = array(
				"options"=>array(
					"min_range"=>$min,
					"max_range"=>$max,
				)
			);
		}
		if(!$options)
			return filter_var($int, FILTER_VALIDATE_INT);
		return filter_var($int, FILTER_VALIDATE_INT, $options);
	}

	/**
	 * 判断是否是浮点型
	 */
	public function isFloat($float)
	{
		return filter_var($number, FILTER_VALIDATE_FLOAT);
	}

	/**
	 * 过滤整型中的非法字符
	 */
	public function int($int)
	{
		return filter_var($int, FILTER_SANITIZE_NUMBER_INT);
	}

	/**
	 * 过滤数字中的异常字符，浮点型
	 */
	public function number($number)
	{
		filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
	}

	/**
	 * 判断是否是邮箱
	 */
	public function isEmail($email)
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * 过滤邮箱中异常字符
	 */
	public function email($email)
	{
		return filter_var($email, FILTER_SANITIZE_EMAIL);
	}

	/**
	 * 判断是否是URL
	 */
	public function isUrl($url)
	{
		return filter_var($url, FILTER_VALIDATE_URL);
	}

	/**
	 * 过滤URL中异常字符
	 */
	public function url($url)
	{
		return filter_var($url, FILTER_SANITIZE_URL);
	}

	/**
	 * 判断是否是boolean
	 */
	public function isBoolean($boolean)
	{
		return !is_null(filter_var($boolean, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));
	}

	/**
	 * 判断是否是IP地址
	 */
	public function isIP($ip)
	{
		return filter_var($ip, FILTER_VALIDATE_IP);
	}

	/**
	 * 过滤器去除或编码不需要的字符
	 */
	public function string($string)
	{
		return filter_var($string, FILTER_SANITIZE_STRING);
	}

	/**
	 * 过滤空格换行符等
	 */
	public function blank($string, $type=false)
	{
		$regx = $type ? [" ","　","	", "\t", "\n", "\r", "&nbsp;", "&#10;", "&#13;"] : ["　","	", "\t", "\n", "\r", "&#10;", "&#13;"];
		return str_replace($regx, '', $string);
	}

	/**
	 * 过滤器对特殊字符进行 HTML 转义
	 */
	public function html($html)
	{
		YQ::epd('emoji.php');
		return filter_var($html,FILTER_SANITIZE_SPECIAL_CHARS);
	}

	/**
	 * emoji处理
	 */
	public function emoji($string, $type=true)
	{
		YQ::epd('emoji.php');
		return $type ? \emoji_unified_to_html($string) : \emoji_html_to_unified($string);
	}

	/**
	 * 过滤器对字符串执行 addslashes() 函数
	 */
	public function slashes($string)
	{
		return filter_var($string, FILTER_SANITIZE_MAGIC_QUOTES);
	}

	/**
	 * 过滤器根据正则表达式来验证值
	 */
	public function regexp($string, $regexp = '')
	{
		return filter_var($string, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>$regexp)));
	}

	/**
     * 判断是否是日期
     */
    public function isDate($date)
    {
        $dateArr = explode("-", $date);
        if(is_numeric($dateArr[0]) && is_numeric($dateArr[1]) && is_numeric($dateArr[2]))
        {
        	if(($dateArr[0] >= 1000 && $timeArr[0] <= 10000) && ($dateArr[1] >= 0 && $dateArr[1] <= 12) && ($dateArr[2] >= 0 && $dateArr[2] <= 31))
        	{
            	return True;
        	}
        }
        else
        {
            return False;
        }
        return False;
    }
    /**
     * 判断是否是时间
     */
    public function isTime($time)
    {
        $timeArr = explode(":", $time);
        if(is_numeric($timeArr[0]) && is_numeric($timeArr[1]) && is_numeric($timeArr[2]))
        {
        	if (($timeArr[0] >= 0 && $timeArr[0] <= 23) && ($timeArr[1] >= 0 && $timeArr[1] <= 59) && ($timeArr[2] >= 0 && $timeArr[2] <= 59))
        	{
            	return True;
            }
        }
        else
        {
            return False;
        }
        return False;
    }

    /**
     * 判断是否是密码
     */
    public function isPassword($password,$minLen=6,$maxLen=30)
    {
        $regexp='/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{'.$minLen.','.$maxLen.'}$/';
        $password = trim($password);
        if(empty($password))
        {
            return False;
        }
        return $this->regexp($password, $regexp);
    }

    /**
     * 验证长度
     * @param: string $str
     * @param: int $type(方式，默认min <= $str <= max)
     * @param: int $min,最小值;$max,最大值;
     * @param: string $charset 字符
    */
    public function length($str,$min=0,$max=0,$type=3,$charset = 'utf-8')
    {
        if(!self::isEmpty($str))
        	return False;
        $len = mb_strlen($str,$charset);
        switch($type)
        {
            case 1: //只匹配最小值
                return ($len >= $min) ? True : False;
                break;
            case 2: //只匹配最大值
                return ($max >= $len) ? True : False;
                break;
            default: //min <= $str <= max
                return (($min <= $len) && ($len <= $max)) ? True : False;
        }
    }

    /**
     * 验证中文
     * @param:string $str 要匹配的字符串
     * @param:$charset 编码（默认utf-8,支持gb2312）
     */
    public function isChinese($str,$charset = 'utf-8')
    {
        if(!self::isEmpty($str)) return false;
        $regexp = (strtolower($charset) == 'gb2312') ? "/^[".chr(0xa1)."-".chr(0xff)."]+$/"
        : "/^[x{4e00}-x{9fa5}]+$/u";
        return $this->regexp($str, $regexp);
    }

    /**
     * 判断是否是手机号
     */
    public function isMobile($mobile)
    {
        $regexp = "/^13[0-9]{1}[0-9]{8}$|15[012356789]{1}[0-9]{8}$|18[012356789]{1}[0-9]{8}$|14[57]{1}[0-9]$/";
        return $this->regexp($mobile, $regexp);
    }

    /**
     * 验证英文和中文
     * @param string $string
     * @param int $length
     */
    public function isName($string, $minLen=1, $maxLen=16, $charset='ALL')
    {
        if(empty($string))
            return false;
        switch($charset)
        {
            case 'EN':
            	$regexp = '/^[_\w\d]{'.$minLen.','.$maxLen.'}$/iu';
                break;
            case 'CN':
            	$regexp = '/^[_\x{4e00}-\x{9fa5}\d]{'.$minLen.','.$maxLen.'}$/iu';
                break;
            default:
            	$regexp = '/^[_\w\d\x{4e00}-\x{9fa5}]{'.$minLen.','.$maxLen.'}$/iu';
        }
        return $this->regexp($string, $regexp);
    }

    /**
     * 判断是否是QQ号
     */
    public function isQQ($qq)
    {
        $regexp = "/^[1-9][0-9]{4,12}$/";
        return $this->regexp($qq, $regexp);
    }
    
}