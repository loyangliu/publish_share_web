<?php

/**
 * User 模块
 * 
 */
class UserController extends AppController {
	
	public function loginCheck() {
		return true;
	}
	
	public function login() {
		$this->view->display("login");
	}
	
	public function show() {
		$username = $_POST["username"];
		$password = $_POST["password"];
		echo "username=" . $username . "; password=" . $password;
	}
}

?>