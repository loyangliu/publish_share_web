<?php

require_once WEBROOT_PATH . '/libs/base/session.php';

class DefaultController extends AppController {
	
	public function index () {
		$session = Session::getInstance();
		$userid = $session->get("userid");
		
		$this->model->testRedisWrite();
		$redisVal = $this->model->testRedisGet();
		
		echo "default-index page</br>";
		echo "redisVal=" . $redisVal. "</br>";
	}
}

?>