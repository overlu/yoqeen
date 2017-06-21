<!Doctype html>
<html xmlns=http://www.w3.org/1999/xhtml>
	<head><?php YQ::tpl('page'.DS.'head'); ?></head>
	<body style='text-align: center;'>
		<header><?php YQ::tpl('page'.DS.'header'); ?></header>
		<main id='snow' style='margin:3em 0;'>
		<?php
			YQ::tpl(YQ::$template);
			YQ::Block('page.message');
		?>
		</main>
		<footer style="padding: 20px 0;"><?php YQ::tpl('page'.DS.'footer'); ?></footer>
		<?=YQ::js('backend')?>
	</body>
</html>