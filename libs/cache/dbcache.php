<?php 


include_once 'cache.php';
require_once WEBROOT_PATH . '/libs/db/dbfactory.php';

class DBCache extends Cache {
	protected $db;
	protected $table;
	
	public function __construct($table) {
		$this->table = $table;
	}

	public function init() {
		$this->db = & DbFactory::instance()->createDBO('default');
	}
	

	public function get($key, $id) {
		$now = time();
		$data = $this->db->getRow("select timeSet, timeExpire, value from `{$this->table}` where `key` = '$key' and timeExpire > '{$now}'", MYSQL_ASSOC);
	    if(!isset($data['value'])) {
			return false;
		}
		return json_decode($data['value'], true);
	}
	

	public function set($key, $data, $lifeTime = 0) {
		if($lifeTime > 0) {
			$timeExpire = time() + $lifeTime;
		} else {
			$timeExpire = 0;
		}
		
		$dataDb = array('key'=>$key, 'value'=>json_encode($data), 'timeSet'=>time(), 'timeExpire'=>$timeExpire);
		if ($this->db->getRow("select * from `{$this->table}` where `key` = '$key'", MYSQL_ASSOC)) {
			// update
			$this->db->update($dataDb, $this->table, "`key`='{$key}'");
		}else {
			// insert
			$this->db->insert($dataDb, $this->table);
		}
	}
	

	public function delete($key) {
		$this->db->query("delete from {$this->table} where `key` = '{$key}'");
	}

	public function gc() {
		$this->db->query("delete from {$this->table} where `timeExpire` < '".time()."'");
	}
}



// end of script