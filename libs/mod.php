<?php

namespace Yoqeen\Libs;

use \PDO;

class Mod extends Core
{
	public $sql;
	public $query;
	public $fetch = array();
	public $num = 0;
	public $table;
	private $dbh = null;	//数据库连接资源
	private $prepare = null;	//预处理
	private $data = array();
	private $select = false;	//是否是select查询
    public $pk = null;

	const PERSISTENT = false;	//true为长连接，false为短连接

	function __construct($table=''){
		parent::__construct();

		$this->connect();
		/* reset sql */
		$this->table = $table ? $table : ltrim(strrchr(rtrim(strtolower(get_class($this)),'mod'),"\\"),"\\");
        if(!$this->pk)
        {
            $this->pk = $this->table.'_id';
        }
	}

	/**
	 * 连接数据库
	 * @return 数据库连接资源
	 */
	private function connect()
	{
		if($this->dbh)
		{
			return $this->dbh;
		}
		/* db connect */
		if(HOST_SELECT)
		{
			$dsn = DB_TYPE.":host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME;

			try {
				if(!self::PERSISTENT)
				{
					$this->dbh = new PDO($dsn, DB_USER, DB_PASSWORD); //初始化一个PDO对象，就是创建了数据库连接对象$dbh
				}
				else
				{
					//默认这个不是长连接，如果需要数据库长连接，需要最后加一个参数：array(PDO::ATTR_PERSISTENT => true) 变成这样：
					$this->dbh = new PDO($dsn, DB_USER, DB_PASSWORD, array(PDO::ATTR_PERSISTENT => true));
				}
				if(DEVELOPMOD == 'PRODUCTION')
				{
					$this->dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_SILENT); //不显示错误
				}
				else
				{
					$this->dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);//产生致命错误，PDOException
				}
			//	$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);//显示警告错误，并继续执行
				
				$this->dbh->exec('set names '.DB_CODE);  

			} catch (PDOException $e) {
	    		throw new \Exception("Error!: " . $e->getMessage(), 1);
			}
		}
	}

	private function reset()
	{
		$this->sql = "";
		$this->query = "";
		$this->fetch = array();
		$this->num = 0;
		$this->last_id = '';
		$this->prepare = null;	//预处理
		$this->data = array();
		$this->select = false;
        $this->where = '';
	}

    /**
     * 设置表
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * 设置是查询
     */
    public function setSelect()
    {
        $this->select = true;
        return $this;
    }

	//select
	public function select($name='*',$table=null)
	{
		$table = $table ? $table : $this->table;
		$this->sql .= $name != '*' ? ' SELECT '.$name.' FROM '.$table.' ' : ' SELECT * FROM '.$table.' ';
		$this->select = true;
		return $this;
	}

	/**
	 * @param  [type] $where [条件查询语句，如果使用占位符则需要传入$data]
	 * @param  array or '' $data  [传入条件查询的值]
	 */
	public function where($where, $data='')
	{
		$this->sql .= ' WHERE '.$where;
		if(is_array($data) && !empty($data))
		{
			foreach ($data as $key => $value)
			{
				$this->data[':'.$key] = $value;
			}
		}
		return $this;
	}

	//order by
	public function order($order,$ace="ASC")
	{
		$this->sql .= ' ORDER BY '.$order.' '.$ace;
		return $this;
	}

	//group by
	public function group($group)
	{
		$this->sql .= ' GROUP BY '.$group;
		return $this;
	}

	//limit
	public function limit($start, $limit='')
	{
		$this->sql .= $limit ? ' LIMIT '.$start.', '.$limit : ' LIMIT '.$start;
		return $this;
	}

	//left join
	public function left($name, $where, $data='')
	{
		$this->sql .= ' LEFT JOIN '.$name.' ON '. $where;
		if(is_array($data) && !empty($data))
		{
			foreach ($data as $key => $value)
			{
				$this->data[':'.$key] = $value;
			}
		}
		return $this;
	}

	//right join
	public function right($name, $where, $data='')
	{
		$this->sql .= ' RIGHT JOIN '.$name.' ON '. $where;
		if(is_array($data) && !empty($data))
		{
			foreach ($data as $key => $value)
			{
				$this->data[':'.$key] = $value;
			}
		}
		return $this;
	}

	//inner join
	public function inner($name, $where, $data='')
	{
		$this->sql .= ' INNER JOIN '.$name.' ON '. $where;
		if(is_array($data) && !empty($data))
		{
			foreach ($data as $key => $value)
			{
				$this->data[':'.$key] = $value;
			}
		}
		return $this;
	}

	//insert
	public function insert($array, $table=null)
	{
		$table = $table ? $table : $this->table;
		$keys = implode(',',$this->array_point(array_keys($array)));
		$_keys = implode(',',$this->array_point(array_keys($array),':','left'));
		$this->sql .= ' INSERT INTO `'.$table.'` ('.$keys.') VALUES ('.$_keys.')';
		foreach ($array as $key => $value)
		{
			$this->data[':'.$key] = $value;
		}
		$result = $this->result();
		$result->last_id = $this->last_id();
		return $result;
	}

	//update
	public function update($array, $table=null)
	{
		$table = $table ? $table : $this->table;
		$update = array();
		foreach($array as $key => $value)
		{
			$update[$key] = "`".$key."`=:".$key;
			$this->data[':'.$key] = $value;
		}
		$update = implode(",",$update);
		$this->sql .= ' UPDATE '.'`'.$table.'` SET '.$update;
		return $this;
	}

	//delete
	public function delete($table=null)
	{
		$table = $table ? $table : $this->table;
		$this->sql .= ' DELETE FROM `'.$table.'` ';
		return $this;
	}

	//truncate table 慎用
	public function truncate($table=null)
	{
		$table = $table ? $table : $this->table;
		$this->sql .= ' TRUNCATE TABLE `'.$table.'` ';
		return $this;
	}

	// query sql
	private function query()
	{
		$this->query = $this->data ? $this->prepare->execute($this->data) : $this->prepare->execute();
		return $this;
	}

	public function last_id($id_key='', $table='')
	{
		$id_key = $id_key ? $id_key : $this->pk;
		$table = $table ? $table : $this->table;
		$id = $this->dbh->lastinsertid();
		if(!$id){
		//	$id = mysql_fetch_array(mysql_query("SELECT `{$id_key}` FROM {$table} ORDER BY `{$id_key}` DESC LIMIT 1"));
		}
		$this->last_id = $id;
		return $this->last_id;
	}

	//do query sql
	public function exec($sql)
	{
		$this->sql = $sql;
		return $this;
	}

	//fetch
	private function fetch($type="assoc")
	{
		if(!$this->select)
		{
			return $this;
		}
		switch($type)
		{
			case "row":
				$this->fetch = $this->prepare->fetchAll(PDO::FETCH_NUM);
				break;
			case "assoc":
				$this->fetch = $this->prepare->fetchAll(PDO::FETCH_ASSOC);
				break;
			case "array":
				$this->fetch = $this->prepare->fetchAll(PDO::FETCH_BOTH);
				break;
			case "object":
				$this->fetch = $this->prepare->fetchAll(PDO::FETCH_OBJ);
				break;
			default:
				$this->fetch = $this->prepare->fetchAll(PDO::FETCH_ASSOC);
				break;
		}
		return $this;
	}

	//result num
	public function numTotal()
	{
		if($this->fetch)
		{
			$this->num = count($this->fetch);
		}
		else
		{
			$this->num = $this->prepare->rowCount();
		}
		return $this;
	}

	//array to key and value with ''
	public function array_point($array, $type = "`", $location = 'both')
	{
		$arr = array();
		foreach($array as $key=>$val)
		{
			switch ($location)
			{
				case 'both':
					$arr[$key] = $type.$val.$type;
					break;
				case 'left':
					$arr[$key] = $type.$val;
					break;
				case 'right':
					$arr[$key] = $val.$type;
					break;
				default:
					$arr[$key] = $type.$val.$type;
					break;
			}
		}
		return $arr;
	}

	//return result
	public function result($type="all",$fetch = 'assoc')
	{
		$result = "";
		if(!$this->prepare)
		{
			$this->prepare = $this->dbh->prepare($this->sql);
			
			$this->query();
       		$this->fetch($fetch);
		}
		switch($type)
		{
			case "fetch":
				$result = $this->fetch;
				break;
			case "sql":
				$result = $this->sql;
				break;
			case "query":
				$result = $this->query;
				break;
			case "num":
       			$this->numTotal();
				$result = $this->num;
				break;
			case "all":
       			$this->numTotal();
				$result = (object)[
					'fetch'=>$this->fetch,
					'sql'  =>$this->sql,
					'query'=>$this->query,
					'num'  =>$this->num,
					];
				break;
			default:
				$this->numTotal();
				if($this->num == 0) {
					return null;
				} elseif ($this->num == 1) {
					return $this->fetch[0][$type];
				} else {
					return array_column($this->fetch, $type);
				}
				break;
		}
        $this->reset();
		return $result;
	}

	//debug
	public function debug()
    {
        $keys = array();
        $values = array();
        
        # build a regular expression for each parameter
        foreach ($this->data as $key=>$value)
        {
            if (is_string($key))
            {
                $keys[] = '/'.$key.'/';
            }
            else
            {
                $keys[] = '/[?]/';
            }
            
            if(is_numeric($value))
            {
                $values[] = intval($value);
            }
            else
            {
                $values[] = $value;
            }
        }
        $sql = preg_replace($keys, $values, $this->sql);
        return $sql;
    }

    /**
     ******************************************************** 常用curl集合*************************************************************
     */
    
    /**
     * 根据数据解析where条件
     */
    public function dataWhere($where)
    {
    	if(is_array($where))
    	{
    		$w = [];
    		foreach ($where as $key => $value)
    		{
    			$w[] .= '`'.$key."`=:".$key;
    			$this->data[':'.$key] = $value;
    		}
    		$this->where = join(' AND ', $w);
    	}
    	else
    	{
    		$id = $where;
    		$this->where = $this->pk.' = :id';
    		$this->data[':id'] = $id;
    	}
    	return $this;
    }
    
    /**
     * 保存数据
     * $data 需要保存的数据
     * $where 保存的条件
     * $table 需要保存的表名
     */
    public function save($data, $where='')
    {
        if(!$where && isset($data[$this->pk]) && isInt($data[$this->pk]))
        {
        	$where = '`'.$this->pk.'` = '.$data[$this->pk];
        	unset($data[$this->pk]);
        }

    	return $where ? $this->update($data)->where($where)->result() : $this->insert($data);
    }

    /**
     * 根据条件读取一条数据
     */
    public function loadOne($where)
    {
        $this->select = true;

    	$this->dataWhere($where);
    	$result = $this->select()->where($this->where)->limit(1)->result('fetch');
    	return $result ? $result[0] : '';
    }

    /**
     * 根据条件读取数据
     */
    public function load($where)
    {
        $this->select = true;

    	$this->dataWhere($where);
    	return $this->select()->where($this->where)->result('fetch');
    }

    /**
     * 根据条件删除数据
     */
    public function remove($where)
    {
    	$this->dataWhere($where);
    	return $this->delete()->where($this->where)->result('query');
    }

	/**
	 * 查询记录数
	 */
    public function count($table=null)
    {
    	$this->select = true;
    	$this->table = $table ? $table : $this->table;
    	$this->sql = 'SELECT count(*) as "num" FROM `'.$this->table.'` ';
    	return $this->result()->fetch[0]['num'];
    }

    /**
     * 判断是否是存在
     */
    public function exists($key, $value, $table=null)
	{
		return $this->select($key,$table)->where('`'.$key.'`=:'.$key,[$key=>$value])->limit(1)->result('num');
	}

    public function __call($method, $args)
	{
		if(substr($method, 0, 6) == 'loadBy')
		{
			$key = strtolower(substr($method, 6));
			$where = array($ey=>$args[0]);
			return $this->load($where);
		}
		if(substr($method, 0, 9) == 'loadOneBy')
		{
			$key = strtolower(substr($method, 6));
			$where = array($ey=>$args[0]);
			return $this->loadOne($where);
		}
		if(substr($method, 0, 8) == 'removeBy')
		{
			$key = strtolower(substr($method, 8));
			$where = array($ey=>$args[0]);
			return $this->remove($where);
		}
	}
}