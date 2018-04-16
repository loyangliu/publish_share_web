<?php

/**
 * session 模块
 */

class Session {
	
	private static $_instance = null;
	
	public static function getInstance() {
		if (Session::$_instance == null) {
			Session::$_instance = new Session();
		}
		
		return Session::$_instance;
	}
	
	protected function __construct() {
		$this->init();
	}
	
	/**
	 * 初始化创建一个session，并种下session_id的cookie。
	 * 客户端请求种带上<session_name,session_id>的cookie，服务器会根据session_id找到对应创建好的$_SESSION会话。
	 * PHP会默认在执行session_start()创建会话后，为客户端种下<session_name,session_id>的cookie，有效期为Session时长。通常这个时长需要重新设置一下。
	 */
	private function init() {
		
		$sessionName = 'my_session';
		session_name($sessionName); // 设置会话名称，返回之前的会话名称
		
		if (isset($_COOKIE) && isset($_COOKIE['$sessionName'])) {
			$clientSessionId = $_COOKIE['$sessionName'];
		}
		
		if(isset($clientSessionId) && $clientSessionId != "") {
			session_id($clientSessionId);  // 设置当前会话的session_id
			setcookie($sessionName, $clientSessionId, time() + 5, '/');
		} else {
			session_start();  // 创建一个session会话，随机生成一个session_id
			setcookie($sessionName, session_id(), time() + 5, '/');
		}
		
	}
	
	public function get($key) {
		if (isset($_SESSION) && isset($_SESSION[$key])) {
			return $_SESSION[$key];
		}
		
		return false;
	}
	
	public function set($key, $value) {
		if (isset($_SESSION)) {
			$_SESSION[$key] = $value;
 		}
	}
	
}

?>