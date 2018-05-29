<?php 

/**
 * 数据的基本信息配置
 *
 */
class DATABASE_CONFIG {
	public static $databases = array (
		// default(function test) database
		'default' => array (
				'type' => 'mysqli',
				'host' => '115.28.54.202',
				'user' => 'root',
				'psw' => 'liuyang',
				'database' => 'wiki'
		),
			
		'publish_share' => array (
				'type' => 'mysqli',
				'host' => '115.28.54.202',
				'user' => 'root',
				'psw' => 'liuyang',
				'database' => 'publish_share'
		),
	);
}