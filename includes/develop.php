<?php
function pageError($data = array(), $tag = 'error', $html = null){
	//输出错误信息前清空缓存
	if(ob_get_level())
	{
		if (!count(array_diff(ob_list_handlers(),array('default output handler'))))
		{
			ob_clean();
		}
	//	if(ob_get_status()->name!='zlib output compression')
	//	ob_end_clean();
	}

	//get base Url
	$currentPath = $_SERVER['PHP_SELF'];
	$pathInfo = pathinfo($currentPath);
	$hostName = $_SERVER['HTTP_HOST'];
	$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://' ? 'https://' : 'http://';
	$baseUrl = $protocol.$hostName.($pathInfo['dirname'] != "\\"?$pathInfo['dirname']:"")."/";

	$helpfile = HELP.DS."file.help.php";
	include_once $helpfile;
	$help = "Yoqeen\\Help\\File";
	$file = new $help;
	$data['initcss'] = $baseUrl.'skin/frontend/'.YOQEEN_THEME.'/css/init.css';
	$template = ERRORS.DS.$tag.".ert";
	$template = $file->template($template,$data);
	if(is_null($html))
	{
		die($template);
	}
	$_template = ERRORS.DS.'html'.DS.$html.'.tpl';
	if(!is_file($_template))
	{
		$file->create($_template,$template);
	}
	include_once($_template);
	die;
	//header('location:'.self::baseUrl().$tag.'/');
}

/**
 | 记录日志
 | $content     日志内容
 | $type        日志类型 {NOTICE INFO ERROR WARING OTHER}
 */
function errorLog($content, $type='ERROR')
{
    $file = BP.DS.'var'.DS.'reports'.DS.'error-'.date('Y-m-d').'.log';
    $content = '===  '.strftime("%Y-%m-%d %H:%M:%S").'  ['.strtoupper($type).']  ===   '.$content."\n\r";
    \YQ::help('file')->create($file, $content, 'a');
    return True;
}

//dump variable
function dd()
{
	$vars = func_get_args();
//	$open = fopen(BP.DS.'var'.DS.'.dump','w+');
	echo '<pre><p style="max-width:100%;display:inline-block;">____DUMP_BEGIN____<br>';
	foreach ($vars as $var)
	{
	//	$_dump = var_dump($var);
		var_dump($var);
	//	fwrite($open, $_dump);
	}
	echo '____DUMP_END____</p></pre>';
	die;
//	fclose($open);
//	$dump = file_get_contents(BP.DS.'var'.DS.'.dump');
//	pageError(array('title'=>'Data Dump','content'=>'<span style="font-size:14px;font-weight:bold;">Data Dump: </span><br>'.$dump));
}

function isAjax()
{
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) )
    {
        if('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
            return true;
    }
    //如果参数传递的参数中有ajax
    /*if(!empty($_POST['ajax']) || !empty($_GET['ajax']))
        return true;*/
    return false;
}

function yoqeenDevelop()
{
	$_error = error_get_last();
//	dump($_error);
    if($_error && in_array($_error['type'], array(0, 1, 4, 16, 64, 256, 2048, 4096, E_ALL)))
    {

		if(DEVELOPMOD == 'PRODUCTION')
		{
            errorLog($_error['message'].' At: '.$_error['file'].': '.$_error['line']);
			pageError(array('title'=>'System Upgrade','content'=>'<span style="font-size:14px;font-weight:bold;">System Upgrade</span><br>Sorry, We are upgrading now, Pls wait a moment.'),'500','upgrading');
		}
		else
		{
			if(in_array($_error['type'], array(0, 1, 4, 16, 64, 256, 2048, 4096, E_ALL)))
			{
   			 //	pageError(array('title'=>'System Upgrade','content'=>'<span style="font-size:14px;font-weight:bold;">Message : </span><br>'.$_error['message']));
      			pageError(array('title'=>'Error','content'=>'<span style="font-weight:bold;">Error: '.$_error['message'].'<br><br>File : '.$_error['file'].'<br><br>Line : '.$_error['line'].'</span>'));
			}
			/*if(in_array($_error['type'], array(1)))
		    {
		    	pageError(array('title'=>'System Upgrade','content'=>'<span style="font-size:14px;font-weight:bold;">Error Message : </span><br>'.$_error['message']));
		    }*/

		}
    }

    if(EFFECTMOD && !isAjax())
    {
    	function convert($size)
    	{
			$unit=array('b','kb','mb','gb','tb','pb');
			return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
		}
		$memory = convert(memory_get_usage(true));
		$version = \YQ::Version()->current();
		$lastest_version = \YQ::Version()->lastest();
		$html = '<style>html,body{margin:0;padding:0;}body{padding-bottom:20px;}</style><div style="position:fixed;bottom:0px;font-size:12px;background:#fff;height:20px;line-height:20px;text-align:center;width:100%;border-top:1px solid #999;z-index:9999999;font-family:sans-serif;color:#666;">Memory: '.$memory.', Executed in '. round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 2).'s . <a style="margin:0 1em;" href="'.YQ::baseUrl('application').'">Application Manage</a>';
    	if($version != $lastest_version)
    	{
    		$html .= ' <span style="color:#f00;">Current Version: '.$version.', Lastest Version: '.$lastest_version.', <a style="color:#f00;font-weight:bold;" href="'.YQ::baseUrl('application/upgrade').'">Upgrade</a></span>';
    	}
    	else
    	{
    		$html .= ' <span style="color:#f00;">Current Version: '.$version.'</span>.';
    	}
    	$html .= '</div>';
    	echo $html;
    }


}
if(IN_YOQEEN == 'web')
{
    register_shutdown_function("yoqeenDevelop");
}