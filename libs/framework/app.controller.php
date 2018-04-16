<?php 


require_once 'controller.php';
require_once WEBROOT_PATH . '/libs/base/session.php';
require_once WEBROOT_PATH . '/libs/framework/dispatcher.php';


/**
 * App通用 Controller ， 功能：
 * 1. 登录态校验
 */
class AppController extends Controller {

	public function __construct() {
		parent::__construct();
	}
	
	public function initialize(&$ruler) {
		parent::initialize($ruler);
	}
	
	// 登录态校验
	public function loginCheck() {
		$session = Session::getInstance();
		
		if(!$session->get("userid")) {
			Dispatcher::instance()->dispatch("/system/user/login");
			return false;
		}
		
		return true;
	}
	
	// 用户请求响应完毕
	public function echoEnd(){}

}

