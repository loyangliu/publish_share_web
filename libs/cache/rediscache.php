<?php 


include_once 'cache.php';

class RedisCache extends Cache {
	private $defaultLifeTime = 86400;
	private $ip;
	private $port;
	private $pass;
	private $redis;
	
	public function __construct($ip, $port, $pass, $defaultLifeTime) {
		$this->ip = $ip;
		$this->port = $port;
		$this->pass = $pass;
		$this->defaultLifeTime = $defaultLifeTime;
	}
	
	public function __destruct() {
		if($this->redis) {
			$this->redis->close();
		}
	}
	
	public function init() {
		$this->redis = new Redis();
		$this->redis->connect($this->ip, $this->port);
		$this->redis->auth($this->pass);
	}
	
	public function check() {
		$connect_status=$this->redis->ping();
		if($connect_status != "+PONG") {
			$this->redis->connect($this->ip, $this->port);
			$this->redis->auth($this->pass);
		}
	}
	

	public function get($key, $id=null) {
		$this->check();
		
		return json_decode($this->redis->get($key), true);
	}
	

	public function set($key, $value, $lifeTime = 0) {
		$this->check();
		
		$lifeTime = $lifeTime ? $lifeTime : $this->defaultLifeTime;
		return $this->redis->set($key, json_encode($value), $lifeTime);
	}
	

	public function delete($key) {
		$this->check();
		
		return $this->redis->del($key);
	}
	
	public function gc(){
		return;
	}

	public function cacheObject(){
		$this->check();
		
		return $this->redis;
	}
}



// end of script