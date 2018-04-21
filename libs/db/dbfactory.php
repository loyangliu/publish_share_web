<?php 

/**
 * 对外使用的DB工厂类
 */

require_once 'dbo.php';
require_once 'dbcfg.php';

class DbFactory 
{
	private static $_this = null;
	
	public static function instance() {
		if(DbFactory::$_this == null) {
			DbFactory::$_this = new DbFactory();
		}
		
		return DbFactory::$_this;
	}
	
	private $dbset = Array();
	
	private function loadDBO($dbconfig) {
		if( isset($dbconfig) ) {
			if ($dbconfig["type"] == "mysqli") {
				include_once('mysqli.php');
				$classname = "DB_MySQLi";
			} else if ($dbconfig["type"] == "mysql") {
				include_once('mysql.php');
				$classname = "DB_MySQL";
			}
			
			if( class_exists($classname) ) {
				return new $classname($dbconfig);
			}
		}
		
		return null;
	}
	
	// 初始化 db 配置
	private function init() {
		foreach(DATABASE_CONFIG::$databases as $key => $dbconfig) {
			if(!isset($this->dbset[$key])) {
				if ($dbo = $this->loadDBO($dbconfig)) {
					$this->dbset[$key] = $dbo;
				}
			}
		}
	}
	
	private function __construct() {
		$this->init();
	}
	
	// 创建自己的db对象
	public function createDBO($dbname = null) {
		if ($dbname == null) {
			$dbname = "default";
		}
		
		if(!isset($this->dbset[$dbname])) {
			throw new Exception("cannot create '.$dbname.' database.");
		}
		
		return $this->dbset[$dbname];
	}
}