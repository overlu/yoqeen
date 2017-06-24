<?php
/**
 | @author 2016-09-23 By Peng.lu 
 */

/**
 | 修改存储过程表sync_procedure 新增来源表和目标表
 */
$sql['alter_sync_procedure_add_table'] = <<<SQL
ALTER TABLE `sync_procedure`
ADD COLUMN `source_table`  varchar(50) CHARACTER SET utf8 NOT NULL AFTER `name`,
ADD COLUMN `target_table`  varchar(50) CHARACTER SET utf8 NOT NULL AFTER `source_table`;
SQL;

