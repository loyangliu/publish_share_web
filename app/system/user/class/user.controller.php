<?php

/**
 * User 模块
 * 
 */

require_once WEBROOT_PATH . '/libs/base/session.php';

class UserController extends AppController {
	
	public function loginCheck() {
		return true;
	}
	
	public function login() {
		$this->view->display("login");
	}
	
	public function doLogin() {
		
		if (isset($_POST) && isset($_POST["username"])) {
			$username = $_POST["username"];
		}
		if (isset($_POST) && isset($_POST["password"])) {
			$password = $_POST["password"];
		}
		
		if (isset($username) && isset($password)) {
			$ret = $this->model->checkLogin($username, $password);
			if($ret) {
				$session = Session::getInstance();
				$session->set("userid", $ret["uid"]);
			}
		}
		
		$this->redirect("/");
	}
}

?>