<?php
/**
 | @author 2016-09-21 By Peng.lu
 */

/**
 | 初始化
 | 创建JSON解析函数fn_json_get(key/value)
 | 返回keys或者values
 */
/*$sql[] = <<<SQL
DELIMITER $$ 
USE `{$source_databases}`$$ 
DROP FUNCTION IF EXISTS `fn_json_get`$$ 
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_json_get`(
json_obj VARCHAR(4096),
type VARCHAR(10)
) RETURNS VARCHAR(4096) CHARSET utf8
BEGIN
DECLARE vs_return VARCHAR(4096);
DECLARE vs_keys, vs_values VARCHAR(4096);
IF JSON_VALID(json_obj) <> 1 OR JSON_TYPE(json_obj) <> 'OBJECT' THEN
SET vs_return = NULL;
ELSE
IF type = 'key' THEN
SET vs_return = CONVERT(JSON_KEYS(json_obj), CHAR);
ELSE
SET vs_return = CONVERT(JSON_EXTRACT(json_obj, '$.*'), CHAR);
END IF;
SET vs_return = REPLACE(REPLACE(vs_return, '[', ''), ']', '');
SET vs_return = REPLACE(vs_return, '"', '');
END IF;
RETURN(vs_return);
END$$ 
DELIMITER ;
SQL;*/
// $sql['json_function_init'] = <<<SQL
// DROP FUNCTION IF EXISTS `fn_json_get`;

// CREATE DEFINER = `root`@`localhost` FUNCTION `fn_json_get`(json_obj VARCHAR(4096),
// type VARCHAR(10))
//  RETURNS varchar(4096)
// BEGIN
// DECLARE vs_return VARCHAR(4096);
// DECLARE vs_keys, vs_values VARCHAR(4096);
// IF JSON_VALID(json_obj) <> 1 OR JSON_TYPE(json_obj) <> 'OBJECT' THEN
// SET vs_return = NULL;
// ELSE
// IF type = 'key' THEN
// SET vs_return = CONVERT(JSON_KEYS(json_obj), CHAR);
// ELSE
// SET vs_return = CONVERT(JSON_EXTRACT(json_obj, '$.*'), CHAR);
// END IF;
// SET vs_return = REPLACE(REPLACE(vs_return, '[', ''), ']', '');
// SET vs_return = REPLACE(vs_return, '"', '');
// END IF;
// RETURN(vs_return);
// END;
// SQL;

/**
 | 新建存储过程记录表
 */
$sql['sync_procedure_table_init'] = <<<SQL
DROP TABLE IF EXISTS `sync_procedure`;
CREATE TABLE `sync_procedure` (
`id`  int(11) NOT NULL AUTO_INCREMENT,
`name`  varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '来源表名' ,
`version`  varchar(20) CHARACTER SET utf8 NOT NULL COMMENT '当前版本号' ,
`date`  datetime NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '最近一次同步时间' ,
PRIMARY KEY (`id`)
)
;
SQL;

/**
 | 新建同步数据信息日志表sync_logs
 */
$sql['sync_logs_table_init'] = <<<SQL
DROP TABLE IF EXISTS `sync_logs`;
CREATE TABLE `sync_logs` (
`id`  int(11) NOT NULL ,
`source_table`  varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '来源表名' ,
`target_table`  varchar(50) CHARACTER SET utf8 NOT NULL COMMENT '目标表名' ,
`table_fields_match`  TEXT NOT NULL COMMENT '字段配对信息' ,
`info`  TEXT NOT NULL COMMENT '同步信息' ,
`version`  varchar(20) CHARACTER SET utf8 NOT NULL COMMENT '当前版本号' ,
`sync_date`  datetime NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '最近一次同步时间' ,
PRIMARY KEY (`id`)
)
;
SQL;

