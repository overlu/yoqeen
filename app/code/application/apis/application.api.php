<?php

namespace Yoqeen\App\Application\Apis;

use Yoqeen\Libs\Api;
use Yoqeen\Libs\Http;
use YQ;

class ApplicationApi extends Api
{
	function __construct()
	{
		parent::__construct();
		
	}

	public function getApplicationsApi()
	{
		$data = objectToArray($this->model->getApplications()->fetch);
		$this->result['data'] = $data;
		$this->result();
	}
}