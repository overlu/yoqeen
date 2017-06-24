<?php
/**
 | @author 2016-09-22 By Peng.lu 
 */

/**
 | 修改同步日志表sync_logs 主键为自增 
 */
$sql['alter_sync_logs_id_autoincrement'] = <<<SQL
ALTER TABLE `sync_logs`
MODIFY COLUMN `id`  int(11) NOT NULL AUTO_INCREMENT FIRST ;
SQL;

/**
 | 修改同步日志表sync_procedure 主键为自增 
 */
$sql['alter_sync_procedure_id_autoincrement'] = <<<SQL
ALTER TABLE `sync_procedure`
MODIFY COLUMN `id`  int(11) NOT NULL AUTO_INCREMENT FIRST ;
SQL;

