<?php 

/**
 * 数据的基本信息配置
 *
 */

// 数据库配置初始化
DATABASE_CONFIG::init();

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

    /**
     * 初始化数据库配置
     */
	static public function init()
    {
        self::$databases['default'] = [
            'type' => getenv('DB_CONNECTION'),
            'host' => getenv('DB_HOST'),
            'user' => getenv('DB_USERNAME'),
            'psw' => getenv('DB_PASSWORD'),
            'database' => getenv('DB_DATABASE')
        ];
    }
}