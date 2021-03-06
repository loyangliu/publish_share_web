<?php 


class CACHE_CONFIG
{
	public static $caches = array(
			'file' => array(
					'type' => 'file',
					'classname' => 'FileCache',
					'filename' => 'file.cache'
			),
			
			'db' => array(
					'type' => 'db',
					'classname' => 'DbCache',
					'table' => 'cache'
			),
			
			'redis' => array(
				'type' => 'redis',
				'classname' => 'RedisCache',
				'ip' => '115.28.54.202',
				'port' => 6379,
				'pass' => 'liuyang',
				'defaultLifeTime' => 86400
			)
	);
	
}

