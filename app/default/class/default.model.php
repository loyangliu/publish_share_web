<?php

class DefaultModel extends AppModel {
	
	public function testRedisWrite() {
		$this->cache->set("testkey", "testval");
	}
	
	public function testRedisGet() {
		return $this->cache->get("testkey");
	}
	
}

?>