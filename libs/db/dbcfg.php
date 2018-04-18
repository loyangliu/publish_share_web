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
			'host' => '192.168.111.128',
			'user' => 'root',
			'psw' => 'liuyang',
			'database' => 'db_att_plat' 
		),
		// autotest database
		'autotest' => array (
			'type' => 'mysqli',
			'host' => '192.168.111.128',
			'user' => 'root',
			'psw' => 'liuyang',
			'database' => 'db_att_auto' 
		),
		// test database
		'test' => array (
				'type' => 'mysqli',
				'host' => '115.28.54.202',
				'user' => 'root',
				'psw' => 'liuyang',
				'database' => 'wiki'
		),
	);
}