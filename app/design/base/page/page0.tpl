<!Doctype html>
<html xmlns=http://www.w3.org/1999/xhtml>
	<title><?=isset($title)?$title:"YOQEEN"?></title>
	<meta charset="UTF-8">
	<meta content="IE=edge" http-equiv="X-UA-Compatible">
	<meta name="keywords" content="yoqeen,php" />
	<meta name="description" content="A simple php framework" />
	<meta content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no, width=device-width" name="viewport">
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="default">
	<meta http-equiv="Cache-Control" content="no-siteapp" />
	<link rel='stylesheet' type='text/css' href='<?=YQ::baseUrl()?>skin/base/css/font-awesome.css' />
	<link rel='stylesheet' type='text/css' href='<?=YQ::baseUrl()?>skin/base/css/base.css' />
	<link rel='stylesheet' type='text/css' href='<?=YQ::baseUrl()?>skin/base/css/main.css' />
	<link rel='stylesheet' type='text/css' href='<?=YQ::baseUrl()?>skin/base/css/responsive.css' />
	<!-- js -->
	<script type="text/javascript"> var baseUrl = '<?=YQ::baseUrl()?>'; </script>
	<script src="//cdn.bootcss.com/jquery/2.2.3/jquery.min.js"></script>
	<body style='text-align: center;'>
		<header style="color: #333;font-size: 1.6em;font-weight: bold;"><i class="fa fa-cog"></i> <span>YOQEEN APP MANAGE SYSTEM</span></header>
		<main id='snow' style='margin:3em 0;'>
		<?php
			YQ::tpl(YQ::$template);
			YQ::Block('page.message');
		?>
		</main>
		<script type='text/javascript' src='<?=YQ::baseUrl()?>libjs/jquery.cookie.js'></script>
		<script type='text/javascript' src='<?=YQ::baseUrl()?>libjs/main.frontend.js'></script>
	</body>
</html>