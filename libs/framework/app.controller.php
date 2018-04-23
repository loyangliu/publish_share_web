<?php 


require_once 'controller.php';
require_once WEBROOT_PATH . '/libs/base/session.php';
require_once WEBROOT_PATH . '/libs/framework/dispatcher.php';


/**
 * App通用 Controller ， 功能：
 * 1. 登录态校验
 */
class AppController extends Controller {

    public $apiAuth = [
        'check' => false,
        'checkIgnoreActions' => [],
        'user' => false
    ];

    public function apiAuth()
    {
        // api_token
        $api_token = addslashes($_REQUEST['api_token']);
        if($api_token){
            $this->apiAuth['user'] = $this->model->db->getRow("select * from users where api_token='{$api_token}'");
        }

        // 开启了api认证
        if($this->apiAuth['check'] && !in_array($this->ruler->actionName, $this->apiAuth['checkIgnoreActions'])){
            if($this->apiAuth['user'] == false){
                echo apiJson(1000, '未认证');
                exit;
            }
        }

        $this->model->apiAuth = $this->apiAuth;
	}
	
	public function initialize(&$ruler) {
		parent::initialize($ruler);
		$this->ruler = $ruler;
        $this->apiAuth();

		$this->render();
	}
	
	// 向View传入默认参数
	private function render() {
		$this->view->assign("test", "testval");
	}
	
	// 登录态校验
	public function loginCheck() {
		/*$session = Session::getInstance();
		
		if(!$session->get("userid")) {
			Dispatcher::instance()->dispatch("/system/user/login");
			return false;
		}*/
		
		return true;
	}
	
	// 用户请求响应完毕
	public function echoEnd(){}

}

