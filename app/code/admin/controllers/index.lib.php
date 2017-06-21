<?php

namespace Yoqeen\App\Admin\Controllers;

use Yoqeen\App\Admin\Controllers\AdminLib;
use \YQ;

class IndexLib extends AdminLib
{

	function __construct()
	{
		parent::__construct();
	}

    public function indexAct()
    {
        $this->assign('var','This is yoqeen admin !');
        $this->template('index');
        $this->page();
    }
}