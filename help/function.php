<?php
define('IMAGE_DEFAULT', "/media/image/no_image.png");

//判断是否为数组，过滤为空
function isArray($arr)
{
	return is_array($arr)&&!empty($arr)?true:false;
}

//判断是否是正整数
function isInt($int)
{
	return (is_numeric($int) && floor($int) == $int && $int>0);
}

//json 支持中文
function encode($arr)
{
	if(is_array($arr))
	{
		array_walk_recursive($arr,'array_urlecode');
		$json = urldecode(json_encode($arr));
		return $json;
	}
	return false;
}

function array_urlecode(&$val,$key)
{
	$val = urlencode(addslashes($val));
}


// 获取客户端IP地址
function getIP()
{
	$ip = '';
	if (getenv("HTTP_CLIENT_IP")) {
		$ip = getenv("HTTP_CLIENT_IP");
	} elseif (getenv("HTTP_X_FORWARDED_FOR")) {
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	} elseif (getenv("REMOTE_ADDR")) {
		$ip = getenv("REMOTE_ADDR");
	} else {
		$ip = "Unknow";
	}
	return $ip;
}

// 密码加密
function password($password)
{
	return md5($password.substr(md5($password),-2)).':'.substr(md5($password),-2);
}


//函数名: compress_html
//参数: $string
//返回值: 压缩后的$string
function compress_html($string)
{
	$string = str_replace("\r\n", '', $string); //清除换行符
	$string = str_replace("\n", '', $string); //清除换行符
	$string = str_replace("\t", '', $string); //清除制表符
	$pattern = array (
					"/> *([^ ]*) *</", //去掉注释标记
					"/[\s]+/",
					"/<!--[^!]*-->/",
					"/\" /",
					"/ \"/",
					"'/\*[^*]*\*/'"
					);
	$replace = array (
					">\\1<",
					" ",
					"",
					"\"",
					"\"",
					""
					);
	return preg_replace($pattern, $replace, $string);
}

//php跳转
//type，默认为302
function redirectUrl($url, $type = '302')
{
	if($url == '404')
	{
		$url = YQ::baseUrl('404');
	}
	if($url == 'back')
	{
		$url = getenv("HTTP_REFERER");
		if(!$url)
		{
			die("<script type='text/javascript'>history.go(-1)</script>");
		}
	}
	if($type == '301')
	{
		header('HTTP/1.1 301 Moved Permanently');
	} elseif ($type == '303') {
		header('HTTP/1.1 303 See Other');
	} elseif ($type == '307') {
		header('HTTP/1.1 307 Temporary Redirect');
	}
	header("Location: {$url}");
	exit;
}

//抓去url内容
//$url: 要抓取的url
//$userAgent: 模拟ua头部如“baiduspider”或“googlebot” Eg: "Baiduspider/2.0+(+http://www.baidu.com/search/spider.htm)";
//$refererUrl: 模拟referer;
//返回抓取的内容或者null
function getUrlContents($url,$userAgent='',$refererUrl='',$timeout=10)
{
	if(!$url) return null;
	$headers = function_exists('get_headers')?get_headers($url,1):get_url_headers($url,3);
	if(!preg_match('/200/',$headers[0])) return null;
	if(extension_loaded('curl'))
	{
		//$Url 需要抓取的页面地址
		//$User_Agent 需要返回的user_agent信息 如“baiduspider”或“googlebot”
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		if($userAgent)
		{
			curl_setopt ($ch, CURLOPT_USERAGENT, $userAgent);
		}
		if($refererUrl)
		{
			curl_setopt ($ch, CURLOPT_REFERER, $refererUrl);
		}
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
		$sources = curl_exec ($ch);
		curl_close($ch);
		return $sources;
	}

	return file_get_contents($url);
}

//获取url头部信息，取代get_header()
function get_url_headers($url,$timeout=10)
{
    $ch=curl_init();

    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_HEADER,true);
    curl_setopt($ch,CURLOPT_NOBODY,true);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);

    $data=curl_exec($ch);
    $data=preg_split('/\n/',$data);
    var_dump($data);
    $data=array_filter(array_map(function($data){
        $data=trim($data);
        if($data)
        {
            $data=preg_split('/:\s/',trim($data),2);
            $length=count($data);
            switch($length)
            {
                case 2:
                    return array($data[0]=>$data[1]);
                    break;
                case 1:
                    return $data;
                    break;
                default:
                	return $data;
                    break;
            }
        }
    },$data));

    //sort($data);

    foreach($data as $key=>$value)
    {
        $itemKey=array_keys($value)[0];
        if(is_int($itemKey)) {
            $data[$key]=$value[$itemKey];
        }elseif(is_string($itemKey)) {
            $data[$itemKey]=$value[$itemKey];
            unset($data[$key]);
        }
    }

    return $data;
}

function objectToArray($obj)
{
    $arr = is_object($obj) ? get_object_vars($obj) : $obj;
    if(is_array($arr))
    {
        return array_map(__FUNCTION__, $arr);
    }
    else
    {
        return $arr;
    }
}

//数组转对象
function arrayToObject($arr)
{
    if(is_array($arr))
    {
        return (object) array_map(__FUNCTION__, $arr);
    }else
    {
        return $arr;
    }
}

/**
 * 获取图片http地址
 */
function url($img, $size = '', $type='1')
{
    $baseUrl = YQ::baseUrl();
    $matter = "|".$baseUrl."|";
    if(preg_match($matter, $img))
    {
        $url = $img;
        $img = str_replace($baseUrl, BP.DS, $url);
        $img = str_replace("/",DS,$img);
        if(!is_file($img))
        {
            return IMAGE_DEFAULT;
        }
    }
    else
    {
    	if(is_file(MEDIA.DS.$img) || is_file($img))
    	{
    	    $img = is_file($img) ? $img : MEDIA.DS.$img;
    	}
    	else
    	{
    		return IMAGE_DEFAULT;
    	}
        $file = basename($img);
        $path = str_replace(BP.DS, '', $img);
        $url = str_replace(DS, '/', $baseUrl.$path);
        
    }
    if($size)
    {
        $sizepath = $type=='1'?$size:($size.'-'.$type);
        $path = dirname($url);
        $file = basename($url);
    	if(!is_file(str_replace('/',DS,str_replace($baseUrl, BP.DS, $path).DS.$sizepath.DS.$file)))
    	{
            $img = YQ::help('image',$img);
            $_size = max($img->width(),$img->height());
            if($size<$_size)
            {
                $img->thumbType = $type;
                $img->thumb($size,'',$type)->save();
            }
            else
            {
                return $url;
            }
    	}
    	$url = $path.'/'.$sizepath.'/'.$file;
    }
    return $url;
}

/**
 * 输出内容截断信息
 */
function more($tring, $url='', $length=140)
{
    $more = $url ? "<a href='".$url."' style='text-decoration:underline;' >more</a>" : '';
    echo (mb_strlen(strip_tags($tring),'utf-8')>$length)?(mb_substr(strip_tags($tring),0,$length,'utf-8').'...&nbsp;&nbsp;'.$more):$tring;
}

/**
 * post / get 请求
 * $data不为空则是post请求，默认为get
 */
function request($url, $data = '', $headers=false, $timeout = 5)
{
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $url);
    if($data != '')
    {
        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
    }
    else
    {
        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    }
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    if($headers)
    {
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
    }
    curl_setopt ($ch, CURLOPT_HEADER, 0);
    if (1 == strpos("$".$url, "https://"))
    {
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

function uucode() {
    $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $rand = $code[rand(0,25)]
        .strtoupper(dechex(date('m')))
        .date('d').substr(time(),-5)
        .substr(microtime(),2,5)
        .sprintf('%02d',rand(0,99));
    for(
        $a = md5( $rand, true ),
        $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
        $d = '',
        $f = 0;
        $f < 8;
        $g = ord( $a[ $f ] ),
        $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],
        $f++
    );
    return $d;
}

function to404()
{
    pageError(array('title'=>'404','content'=>'<span style="font-size:14px;font-weight:bold;">404</span><br>Sorry,We Can\'t Find.'),'404','404');
}

function now($style='Y-m-d H:i:s')
{
    return date($style, time());
}

/**
 * 分页
 * @param  [type] $current_page [当前页]
 * @param  [type] $total_page   [页面总数]
 * @param  [type] $uri          [地址]
 * @return [type]               [description]
 */
function pagination($current_page, $total, $uri)
{
    $params = $_GET ? '?'.http_build_query($_GET) : '';
    $current_page_before = $current_page_after = '';
    if($current_page == 1){
        $current_page_before .= '';
    }elseif($current_page>1 && $current_page<5){
        $current_page_before .= "<a href='".$uri."/1".$params."'>1</a>";
        if($current_page > 2){
            $current_page_before .= "<a href='".$uri."/2".$params."'>2</a>";
        }
        if($current_page > 3){
            $current_page_before .= "<a href='".$uri."/3".$params."'>3</a>";
        }
    }else{
        $current_page_before .= "<a href='".$uri."/".($current_page-1).$params."' title='上一页'><i class='fa fa-angle-left'></i></a><a href='".$uri."/1".$params."'>1</a><a class='more'>...</a>";
        $current_page_before .= "<a href='".$uri."/".($current_page-2).$params."'>".($current_page-2)."</a><a href='".$uri."/".($current_page-1).$params."'>".($current_page-1)."</a>";
    }
    if($current_page == $total){
        $current_page_after .= '';
    }elseif($total - $current_page < 4){
        if($total - $current_page >3){
            $current_page_after .= "<a href='".$uri."/".($total-3).$params."'>".($total-3)."</a>";
        }
        if($total - $current_page >2){
            $current_page_after .= "<a href='".$uri."/".($total-2).$params."'>".($total-2)."</a>";
        }
        if($total - $current_page >1){
            $current_page_after .= "<a href='".$uri."/".($total-1).$params."'>".($total-1)."</a>";
        }
        $current_page_after .= "<a href='".$uri."/".($total).$params."'>".($total)."</a>";
    }else{
        $current_page_after .= "<a href='".$uri."/".($current_page+1).$params."'>".($current_page+1)."</a><a href='".$uri."/".($current_page+2).$params."'>".($current_page+2)."</a>";
        $current_page_after .= "<a class='more'>...</a><a href='".$uri."/".($total).$params."'>".$total."</a><a href='".$uri."/".($current_page+1).$params."' title='下一页'><i class='fa fa-angle-right'></i></a>";
    }

    echo "<div class='pagination text-right'>".$current_page_before."<a class='active'>".$current_page."</a>".$current_page_after."</div>";
}