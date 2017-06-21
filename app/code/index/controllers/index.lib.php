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
		$var = 'hello yoqeen !';
        $this->assign('var', $var);

		$this->template('index');
        $this->page();
	}
}