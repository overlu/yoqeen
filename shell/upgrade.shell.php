<?php

namespace Yoqeen\Shell;

use Yoqeen\Libs\Cli;
use \YQ;
/**
* app uugrade
*/
class Upgrade extends Cli
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
        $sql = [];
        $file = YQ::help('file');
        $sql_files = $file->read(APP.DS.'sql');
        sort($sql_files);
        $lock = json_decode($file->get(APP.DS.'sql'.DS.'lock'), true);
        $lastest_version = $lock['version'] ? $lock['version'] : 'v0.0.0';
        foreach ($sql_files as $sql_file)
        {
            if($file->name($sql_file) != 'lock' && version_compare($file->name($sql_file, '.php'), $lastest_version, '>'))
            {
                $lastest_version = $file->name($sql_file, '.php');
                include_once $sql_file;
            }
        }
        $install_num = 0;
        $install_info = [];
        foreach ($sql as $_key => $_sql)
        {
            
            try {
                if(!YQ::Mod()->exec($_sql)->result()->query)
                {
                    echo  "Error: {$_key}=> {$_sql}\n";
                }
                else
                {
                    echo "Success: {$_key}\n";
                    $install_num++;
                }
            } catch (PDOException $e) {
                echo "Error: {$_key}=> ".$e->getMessage()."\n";
            }
        }

        $lock = json_encode([
            'version' => $lastest_version,
            'date'    => date('Y-m-d H:i:s'),
        ]);
        $file->create(APP.DS.'sql'.DS.'lock', $lock);
        echo $install_num == 0 ? "Already the latest version" : ($install_num == count($sql) ? "Successed!!!" : "Failed");
    }
}