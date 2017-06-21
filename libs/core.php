<?php

namespace Yoqeen\Libs;

use \YQ;

class Core
{

	function __construct()
	{
		$this->sessionInit();
	}

	final private function sessionInit()
	{
        /**
         * 加载session类
         */
		$this->session = YQ::help('session');

        /**
         * 加载验证类
         */
        $this->validator = YQ::help('validator');
	}



    /**
     * hook
     */
    protected function hook($args = '')
    {
        global $yooqeenHookConfig;
        foreach ($yooqeenHookConfig as $hook)
        {
            if(is_file(HOOK.DS.$hook.'.hook.php'))
            {
                $hook = "Yoqeen\\Hook\\".ucfirst($hook);
                $hook = new $hook;
                try {
                    $hook->hook($args);
                } catch (Exception $e) {
                    throw new \Exception($e->getMessage(), 1);
                    
                }
            }
        }
    }

    /**
     * 获取当前用户信息
     * @return [type] [description]
     */
    public function user($key = '')
    {
        if((isset($_SESSION['isBackend']) && Http::$yoqeenMod=='admin'))
        {
            if($this->session->getBackend('user'))
            {
                return $key ? $this->session->getBackend('user')[$key] : $this->session->getBackend('user');
            }
        }
        else
        {
            if($this->session->getFront('user'))
            {
                return $key ? $this->session->getFront('user')[$key] : $this->session->getFront('user');
            }
            // dd($this->session->get(),$key);
        }
        return false;
    }
}