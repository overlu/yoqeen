<?php

namespace Yoqeen\App\Application\Models;

use Yoqeen\Libs\Mod;
use \YQ;

class ApplicationMod extends Mod
{
	
	protected $p; //page num
	protected $limit; //select num

	function __construct()
	{
		parent::__construct();
		$this->table = 'application';
		$this->p = isset($_REQUEST['p'])?$_REQUEST['p']:0;
		$this->limit = isset($_REQUEST['limit'])?$_REQUEST['limit']:false;
	}
}