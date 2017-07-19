<?php

namespace Yoqeen\App\Index\Controllers;

use Yoqeen\Libs\Lib;
use \YQ;

class IndexLib extends Lib
{
	function __construct()
	{
		parent::__construct();
	}

	public function indexAct()
	{
		$var = "<i class='fa fa-smile-o' style='font-size:16em;'></i>".EOL.EOL."<span style='font-size:4em;'>HELLO YQOEEN!</span>";
        $this->assign('var', $var);

		$this->template('index');
        $this->page();
	}
}