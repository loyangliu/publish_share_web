<?php 


require_once 'dbo.php';

/**
 * mysqli 数据库封装类
 *
 */
class DB_MySQLi extends DBO
{
	protected $con;
	protected $dbcfg;

	public function __construct($dbconfig) {
		$this->dbcfg = $dbconfig;
	}

	public function __destruct() {
		$this->close();
	}


	// 建立连接
	public function connect() {
		$host = $this->dbcfg['host'];
		$user = $this->dbcfg['user'];
		$psw = $this->dbcfg['psw'];
		$database = $this->dbcfg['database'];
		
		$this->con = @mysqli_connect($host, $user, $psw, $database);
		if (!$this->con) {
			//连接DB出错信息放入LOG，返回错误信息
			$msg = "connect to mysql server failed:  {$this->dbcfg['user']}@{$this->dbcfg['host']}: {$this->dbcfg['database']}";
			throw new Exception("数据库连接出错，请联系管理员" . $msg);
		}
		$this->setCharset('utf8');
	}
	
	public function setCharset($charset) {
		if($this->con) {
			@mysqli_set_charset($this->con, $charset);
		}
	}
	
	protected function check() {
		if (!@mysqli_ping($this->con)) {
			$this->close();
			$this->connect();
		}
	}



/** 
 * query sql
 * 
 * @param sql 
 * @param limit such as 10  or  '10,10'
 * 
 * @return 
 */
	public function query($sql, $limit = null)
	{
		$this->check();
		if($limit)
		{
			$sql .= ' LIMIT ' . $limit;
		}

		$rs = @mysqli_query($this->con, $sql);
		if($rs)
		{
			return $rs;
		}
		else
		{
			//将sql的出错信息记录LOG，屏幕上不显示具体的sql信息
			$msg = "Invalid SQL:\n{$sql} \n" . mysqli_error($this->con);
			throw new Exception("数据库访问出错，请联系管理员" . $msg);
		}
		return false;
	}


/** 
 * get last insert id
 * 
 * 
 * @return 
 */
	public function lastId()
	{
		return mysqli_insert_id($this->con);
	}


/** 
 * do fetch
 * 
 * @param rs 
 * @param fetchModel 
 * 
 * @return 
 */
	public function fetch($rs, $fetchModel = self::FETCH_DEFAULT)
	{
		return mysqli_fetch_array($rs, $fetchModel);
	}
	


	public function select($fields, $table, $where, $limit = '')
	{
		$sql = $this->buildSelectSql($fields, $table, $where, $limit);
		return $this->getAll($sql, $limit, self::FETCH_ASSOC);
	}
	
	public function selectOne($fields, $table, $where, $limit = '')
	{
		$sql = $this->buildSelectSql($fields, $table, $where, $limit);
		return $this->getOne($sql);
	}
	
	public function selectCol($fields, $table, $where, $limit = '')
	{
		$sql = $this->buildSelectSql($fields, $table, $where, $limit);
		return $this->getCol($sql, $limit);
	}
	
    public function selectRow($fields, $table, $where, $limit = '')
	{
		$sql = $this->buildSelectSql($fields, $table, $where, $limit);
		return $this->getRow($sql, self::FETCH_ASSOC);
	}

	public function selectAll($fields, $table, $where, $limit = '', $key)
	{
		$sql = $this->buildSelectSql($fields, $table, $where, $limit);
		return $this->getAll($sql, $limit, self::FETCH_ASSOC, $key);
	}
/** 
 * update data
 * 
 * @param data 
 * @param table 
 * @param where 
 * 
 * @return 
 */
	public function update($data, $table, $where)
	{
		$sql = " select * from {$table} where {$where}";
		if(! $this->getRow($sql)){
			return $this->insert($data, $table);
		}
			
		$sql = $this->buildUpdateSql($data, $table, $where);
		return $this->query($sql);
	}



	/**
	 * update multiple data
	 *
	 * @param multidimensional array
	 * @param table
	 *
	 * @return
	 */
	public function updateAll($data, $table, $primaryFieldName, $where = '')
	{
		//拼装批量更新sql语句
		$set	=	array();
		foreach( current($data) as $field=>$val ) {
			$set[$field]	=	" {$field} = case {$primaryFieldName} ";
		}
	
		$caseData	=	array();
		foreach( $data as $row ) {
			foreach( $row as $field=>$val ){
				$caseData[$field][$row[$primaryFieldName]] = " when {$row[$primaryFieldName]} then '{$val}' ";
			}
		}
	
		$sql	=	"update {$table} SET ";
		$setField	=	"";
		foreach( $caseData as $field	=>	$row )
		{
			$setField	=	$set[$field];
			$caseStr	=	"";
			foreach( $row as $primaryField	=>	$case )
			{
				$caseStr	.=	$case;
				$primaryFieldArr[]	=	$primaryField;
			}
			$sql .= $setField. $caseStr ." end, ";
		}
		$sql = trim( $sql, ', ' );
	
		$primaryFieldStr	=	implode(',', array_unique($primaryFieldArr));
	
		if(!$where){
			$where	=	" where {$primaryFieldName} in ({$primaryFieldStr})";
		}
		$sql	.=	$where;
	
		return $this->query($sql);
	}
	
	
/** 
 * insert a data
 * 
 * @param data 
 * @param table 
 * 
 * @return 
 */
	public function insert($data, $table)
	{
		$sql = $this->buildInsertSql($data, $table);
		//echo 'sql:'.$sql.'<br />';
		return $this->query($sql);
	}
	
	
	/**
	 * insert  multiple data
	 *
	 * @param multidimensional array
	 * @param table
	 *
	 * @return
	 */
	public function insertAll($data, $table)
	{
		$names = '';
		$values = '';
	
		foreach ( $data[0] as $key => $val)
		{
			$names .= "`" . $key . '`,';
		}
	
		foreach ($data as $v )
		{
			$values	.=	" ( ";
			foreach ( $v as $key => $val)
			{
				$values .= "'" . $this->escape($val) . "',";
			}
			$values	=	trim( $values, "," );
			$values	.=	" ), ";
		}
	
		$names = preg_replace('/,$/', '', $names);
		$values = preg_replace('/,$/', '', $values);
	
		$sql = ' INSERT INTO ' . $table;
		$sql .= ' (' . $names . ') ';
		$sql .= ' VALUES ' ;
		$sql .=  $values;
		$sql  =	trim( $sql, ", " );
	
		return $this->query($sql);
	}
	
	
/** 
 * get first column of first row
 * 
 * @param sql 
 * 
 * @return 
 */
	public function getOne($sql)
	{
		$rs = $this->query($sql, 1);
		if (mysqli_num_rows($rs) == 0)
		{
			return false;
		}
		
		$row = $this->fetch($rs, self::FETCH_NUM);
		$this->free($rs);
		return $row[0];
	}

	

/** 
 * get first column array
 * 
 * @param sql 
 * @param limit 
 * 
 * @return 
 */
	public function getCol($sql, $limit = null)
	{
		if(!$rs = $this->query($sql, $limit))
		{
			return false;
		}
		
		$result = array();
        while ($row = $this->fetch($rs, self::FETCH_NUM))
		{
            $result[] = $row[0];
        }
        $this->free($rs);
        return $result;
	}
	


/** 
 * get first row
 * 
 * @param sql 
 * @param fetchModel 
 * 
 * @return 
 */
	public function getRow($sql, $fetchModel = self::FETCH_DEFAULT)
	{
		if(!$rs = $this->query($sql, 1))
		{
			return false;
		}
		
		$row = $this->fetch($rs, $fetchModel);
        $this->free($rs);
        return $row;
	}
	


/** 
 * get all data
 * 
 * @param sql 
 * @param limit 
 * @param fetchModel 
 * 
 * @return 
 */
	public function getAll($sql, $limit = null, $fetchModel = self::FETCH_DEFAULT, $key = null)
	{
		
	    if($key && $fetchModel == self::FETCH_NUM)
	    {
	    	throw new Exception('使用某个字段作为key时，不能用FETCH_NUM模式'); 
	    }
		if(!$rs = $this->query($sql, $limit))
		{
			return false;
		}
		
		$all = array();
		while($row = $this->fetch($rs, $fetchModel))
		{
		    if(!$key)
                $all[] = $row;
            else 
                $all[$row[$key]] = $row;
		}
		$this->free($rs);
		return $all;
	}



/** 
 * free result
 * 
 * @param result 
 * 
 * @return 
 */
	public function free($rs = NULL)
	{
		if($rs)
		{
			@mysqli_free_result($rs);
		}
	}



/** 
 * set database autocommit
 * 
 * @param mode 
 * 
 * @return 
 */
	public function autoCommit($mode = false)
	{
        $this->check();
	    return mysqli_autocommit($this->con, $mode);
    }



/** 
 * commit a transaction
 * 
 * 
 * @return 
 */
	public function commit()
	{
		return mysqli_commit($this->con);
		$this->autoCommit(true);
		return $flag;
	}


/** 
 * rollback a transaction
 * 
 * 
 * @return 
 */
	public function rollback()
	{
		return mysqli_rollback($this->con);
		$this->autoCommit(true);
		return $flag;
	}



/** 
 * ping
 * 
 * 
 * @return 
 */
	public function ping()
	{
		return mysqli_ping($this->con);
	}



/** 
 * close connection
 * 
 * 
 * @return 
 */
    public function close()
	{
		if($this->con)
		{
			return @mysqli_close($this->con);
		}

		return false;
    }



/** 
 * build update sql
 * 
 * @param data 
 * @param table 
 * @param where 
 * 
 * @return 
 */
	protected function buildUpdateSql($data, $table, $where)
	{
		$sql = '';
		$sql = ' UPDATE ' . $table . ' SET ';
		foreach ($data as $key => $val)
		{
			$sql .= "`" . $key . "`='" . $this->escape($val) . "',";
		}
		$sql = preg_replace( '/,$/' , '' , $sql );
		$sql .= " where {$where} ";
		return $sql;
	}



/** 
 * build insert sql
 * 
 * @param data 
 * @param table 
 * 
 * @return 
 */
	protected function buildInsertSql($data, $table)
	{
		$names = '';
		$values = '';

		foreach ($data as $key => $val)
		{
			$names .= "`" . $key . '`,';

			$val = $val === null ? 'null' : '\'' . $this->escape($val) . '\'';

			$values .= $val . ',';
			//$values .= "'" .$this->escape($val);
		}
		$names = preg_replace('/,$/', '', $names);
		$values = preg_replace('/,$/', '', $values);

		$sql = ' INSERT INTO ' . $table;
		$sql .= ' (' . $names . ') ';
		$sql .= ' VALUES ' ;
		$sql .= ' (' . $values . ') ';
		
		return $sql;
	}

    protected function buildSelectSql($fields, $table, $where, $limit = '')
    {
        $__fields = "";
		if(is_array($fields))
		{
			$__fields = implode(',', $fields);
		}
		else
		{
			$__fields = $fields;
		}

		$sql = " select {$__fields} from {$table} ";
		if($where)
		{
			$sql .= " where {$where} ";
		}
		return $sql;
    }

/** 
 * escape sql
 * 
 * @param str 
 * 
 * @return 
 */
	public function escape($str)
	{
		if (get_magic_quotes_gpc())
		{
			$str = stripslashes_deep($str);
		}


		if (!is_numeric($str))
		{
		    if(!$this->con)
                $this->check();
		    $str = mysqli_real_escape_string($this->con, $str);
		}

		return $str;
	}
}



// end of script
