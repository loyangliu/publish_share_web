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
		
		if (isset($_POST) && isset($_POST["username"])) {
			$username = $_POST["username"];
		}
		if (isset($_POST) && isset($_POST["password"])) {
			$password = $_POST["password"];
		}
		
		if (isset($username) && isset($password)) {
			echo "username=" . $username . "; password=" . $password;
		} else {
			echo "username, password";
		}
		
		//$this->redirect("http://www.loyangliu.com");
	}
}

?>