<?php

namespace Yoqeen\Shell;

use Yoqeen\Libs\Cli;
/**
* shell demo
*/
class Demo extends Cli
{
    public function help()
    {
        echo <<<HELP
  -h    this is help
HELP;
        exit;
    }
    /**
     * 运行入口
     * @return [type] [description]
     */
    public function run()
    {
        echo '>>this is a demo';
    }
}