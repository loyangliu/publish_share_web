<?php 


require_once 'controller.php';
require_once WEBROOT_PATH . '/libs/base/session.php';
require_once WEBROOT_PATH . '/libs/framework/dispatcher.php';


/**
 * App通用 Controller ， 功能：
 * 1. 登录态校验
 */
class AppController extends Controller {

	public function initialize(&$ruler) {
		parent::initialize($ruler);

		$this->render();
	}
	
	// 向View传入默认参数
	private function render() {
		$this->view->assign("test", "testval");
	}
	
	// 框架登录态校验
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

