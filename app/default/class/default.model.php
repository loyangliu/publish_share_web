<?php

class DefaultModel extends AppModel {
	
	protected function init() {
		$this->cache = CacheFactory::instance()->createCache('redis');
		$this->cache->init();
	}
	
	public function testRedisWrite() {
		$this->cache->set("testkey", "testval");
	}
	
	public function testRedisGet() {
		return $this->cache->get("testkey");
	}
	
}

?>