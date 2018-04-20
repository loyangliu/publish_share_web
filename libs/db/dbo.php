<?php 


/**
 * 数据库操作API接口类
 *
 */
abstract class DBO
{
	const FETCH_ASSOC = 1; // MYSQL_ASSOC;
	const FETCH_NUM = 2; //MYSQL_NUM;
	const FETCH_BOTH = 3; //MYSQL_BOTH;
	const FETCH_DEFAULT = 1; //MYSQL_BOTH
	
	
	public abstract function connect();
	public abstract function close();
	public abstract function query($sql, $limit = null);
	public abstract function update($data, $table, $where);
	public abstract function insert($data, $table);
	public abstract function getOne($sql);
	public abstract function getCol($sql, $limit = null);
	public abstract function getRow($sql, $fetchModel = self::FETCH_DEFAULT);
	public abstract function getAll($sql, $limit = null, $fetchModel = self::FETCH_DEFAULT);
}
