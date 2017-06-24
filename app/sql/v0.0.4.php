<?php
/**
 | @author 2016-09-23 By Peng.lu 
 */

/**
 | 修改同步日志表sync_log 新增运行时间
 */
$sql['alter_sync_procedure_add_exec_time'] = <<<SQL
ALTER TABLE `sync_logs`
ADD COLUMN `exec_time`  int(11) NOT NULL AFTER `version`;
SQL;

