<?php

namespace Yoqeen\Libs;

/**
* Cli 基类
*/
class Cli
{

    /**
     * 参数 -
     * @var [type]
     */
    private $shortOpts;

    /**
     * 长参数 --
     * @var [type]
     */
    private $longOpts;

    /**
     * 参数结果集
     * 
     * @var [type]
     */
    private $opts;

    /**
     * 开始执行时间
     */
    private $start;

    /**
     * 结束时间
     */
    private $end;
    
    function __construct()
    {
        if (PHP_SAPI !== 'cli') {
            echo 'shell file must be run as a CLI application';
            exit(1);
        }
        echo "start...\n\n";
        $this->start = time();
        global $argv;
        $this->opts = array_unique($argv);
        /**
         * 初始化
         */
        $this->shortOpts = [];
        $this->longOpts = [];
        $this->init();
        // var_dump($this->opts);
        if($this->getOpt('-h'))
        {
            $this->help();
        }
    }

    /**
     * 获取参数结果集
     * @return [type] [description]
     */
    private function init()
    {
        foreach ($this->opts as $key => $value)
        {
            if(preg_match('/^-(\w+)/', $value))
            {
                $this->shortOpts[$value] = true;
            }elseif(preg_match('/^--(\w+)/', $value))
            {
                $this->longOpts[$value] = true;
            }else{
                if($key>0 && isset($this->opts[$key-1]))
                {
                    if(isset($this->shortOpts[$this->opts[$key-1]]))
                    {
                        $this->shortOpts[$this->opts[$key-1]] = $value;
                    }elseif(isset($this->longOpts[$this->opts[$key-1]])){
                        $this->longOpts[$this->opts[$key-1]] = $value;
                    }
                }
            }
        }
        $this->opts = array_merge($this->shortOpts, $this->longOpts);
    }

    /**
     * 获取参数
     * @param  [type] $name 变量名
     * @return [type]       [description]
     */
    public function getOpt($name)
    {
        return isset($this->opts[$name]) ? $this->opts[$name] : null;
    }

    /**
     * 获取参数
     * @return [type] [description]
     */
    public function getOpts()
    {
        return $this->opts;
    }

    /**
     * 获取短参数
     * @return [type] [description]
     */
    public function getShortOpts()
    {
        return $this->shortOpts;
    }

    /**
     * 获取长参数
     * @return [type] [description]
     */
    public function getLongOpts()
    {
        return $this->longOpts;
    }

    /**
     * 获取model
     * @return [type] [description]
     */
    // private function getModel()
    // {
    //     return $this->getOpt('-m');
    // }

    /**
     * 获取控制器
     * @return [type] [description]
     */
    // private function getCon()
    // {
    //     return $this->getOpt('-c');
    // }

    /**
     * 获取方法
     * @return [type] [description]
     */
    // private function getAct()
    // {
    //     return $this->getOpt('-a');
    // }

    /**
     * 输出help信息
     * 头部两个空格
     * @return [type] [description]
     */
    public function help()
    {
        echo <<<HELP
  -h      show help info
HELP;
        exit();
    }

    public function __destruct()
    {
        echo "\n\nexec time: ".(time()-$this->start). "s.\nend...\n";
    }

    /**
     * 运行入口
     * @return [type] [description]
     */
    public function run()
    {
        exit('>>404');
    }
}