<?php 

require_once 'cachecfg.php';
require_once 'cache.php';

/**
 * 对外使用的Cache工厂类
 */
class CacheFactory {
	private static $_this = null;
	
	public static function instance() {
		if(CacheFactory::$_this == null) {
			$_this = new CacheFactory();
		}
		return $_this;
	}
	
	private $cacheset = array();
	
	private function __construct() {
		$this->init();
	}
	
	private function loadCache($cachecfg) {
		if ($cachecfg['type'] == 'file') {
			$className = $cachecfg['classname'];
			$parms = $cachecfg['filename'];
			
			include_once('filecache.php');
			if(class_exists($className)) {
				return new $className($parms);
			}
		} else if ($cachecfg['type'] == 'db') {
			$className = $cachecfg['classname'];
			$parms = $cachecfg['table'];
			
			include_once('dbcache.php');
			if(class_exists($className)) {
				return new $className($parms);
			}
		} else if($cachecfg['type'] == 'redis') {
			$className = $cachecfg['classname'];
			$ip = $cachecfg['ip'];
			$port = $cachecfg['port'];
			$pass = $cachecfg['pass'];
			$defaultLifeTime = $cachecfg['defaultLifeTime'];
			
			include_once('rediscache.php');
			if(class_exists($className)) {
				return new $className($ip, $port, $pass, $defaultLifeTime);
			}
		}
		
		return null;
	}
	
	private function init() {
		foreach (CACHE_CONFIG::$caches as $key=>$cacheconfig) {
			if(!isset($this->cacheset[$key])) {
				if ($cache = $this->loadCache($cacheconfig)) {
					$this->cacheset[$key] = & $cache;
				}
			}
		}
	}
	
	public function createCache($cacheName) {
		if(!isset($this->cacheset[$cacheName])) {
			throw new Exception('cannot create '.$cacheName.' cache.');
		}
		
		return $this->cacheset[$cacheName];
	}
	
}