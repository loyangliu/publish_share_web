<<<<<<< HEAD
<?php 

/**
 * 路由转发：
 * 接收客户端请求，根据URL解析路由规则，并转发到相应模块。
 * 
 */

include_once 'loader.php';
include_once 'router.php';

class Dispatcher
{
    public static $_this = null;
    
    public $passargs = null;
    
	protected function __construct()
	{
		
	}

	public static function & instance()
	{
		if(!Dispatcher::$_this)
		{
			Dispatcher::$_this = new Dispatcher;
		}
		
		return Dispatcher::$_this;
	}
	


	/** 
	 * $__url ： 路由规则( 字符串，eg:/ajax/system/user/login )
	 * 根据路由规则做模块转发
	 */
	public function dispatch($__url = null)
	{
		$url = $__url;		
		if(!$url)
		{
			$url = $this->getDefaultUri();
		}
		
		list($serverPath, $url) = $this->parseURI($url);
		if($serverPath == null && $url == null ) {
			echo "no resource found for your path. </br>";
			exit();
		}
		
		$route = & Router::instance();
		$ruler = $route->parse($serverPath, $url);
		
		try {
			$this->executeModule($ruler);
		} catch (Exception $e) {
			echo $e->getMessage();
			exit();
		}
		
	}
	
	/**
	 * 获取默认的 URI 路由规则，例：
	 * env('REQUEST_URI') = /index.php/ajax/system/user/login （index.php作为转发模块入口， /ajax/system/user/login 作为转发路由规则。）
	 * 这里截取出 /ajax/system/user/login 作为URI返回路由规则
	 */
	function getDefaultUri()
	{
		$url = $_SERVER['REQUEST_URI'];
		
		if (strpos($url, 'index.php')) {
			$elements = explode('/index.php', $url);
			$url = $elements[1];
			if($url == '') {
				$url = '/';
			} else if($url[0] != '/') {
				$url .= '/' . $url;
			}
		}
		
		return $url;
	}
	
	/**
	 * 根据路由规则$url，找到模块所在服务器上的目录位置，以及加载相应controller的路由信息，例：
	 * 当 url=/ajax/system/user/login 时， 有
	 * $serverPath = /home/liuyang/workspaces/php/mlf-web/app/system  
	 * $url = /ajax/user/login 
	 * 代表从 $serverPath 中，去加载 $url 所指的controller
	 */
	protected function parseURI($url)
	{
		if($url && $url[0] != '/')
		{
			$url = '/' . $url;
		}
		
		$serverPath = APP_PATH;
		$ajax = 0;
		
		while($url)
		{
			$r = null;
			$regexp = '/([^\/\?]+)/';
			if(!preg_match($regexp, $url, $r))  // preg_match 返回0或1，出错时返回false（例如 $url 为null或者为'/'时）
			{
				break;
			}
			
			if(strtolower($r[1]) == "ajax") {
				$ajax = 1;
				$url = preg_replace('/\/' . strtolower($r[1]) . '/', '', $url);
				continue;
			}
	
			$dir = $serverPath . DS . strtolower($r[1]);
			$filename = $dir . DS . CLASS_DIR . DS . strtolower($r[1]) . CONTROLLER_EXT;
			
			if (!file_exists($dir)) { // 本地目录根本不存在，可能被攻击，直接拒绝
				return array(null,null);
			}
	
			if(file_exists($filename))
			{
				$serverPath = $dir;
				break;
			}
			else
			{
				$serverPath .= DS . strtolower($r[0]);
				$url = preg_replace('/\/' . strtolower($r[1]) . '/', '', $url);
			}
		}
		
		if($url) {
			if($ajax == 1) {
				$url = "/ajax" . $url;
			}
			
			return array($serverPath, $url);
		} else {
			return array(null,null);
		}
	}


	/** 
	 * 根据规则，到具体模块下执行
	 * $ruler -- 规则实体
	 */
	private function executeModule(& $ruler)
	{
		$serverPath = $ruler->serverPath;
		$ctrlName= strtolower($ruler->ctrlName);
		$actionName= $ruler->actionName;
		
		// 加载模块 controller 的相应php脚本
		$ctrlClass =ucwords($ctrlName) . 'Controller';
		if (!loadController($serverPath, $ctrlName))
		{
			throw new Exception("missing controller: " . $ctrlClass);
		}
		
		// 实例化 controller
		$controller = new $ctrlClass();
		$action = $actionName;
		
		$classMethods = get_class_methods($controller);
		if(in_array($action, $controller->builtins)) {
			throw new Exception("invalid action: " . $action);
		}
		
		if (!in_array($action, $classMethods) && !in_array(strtolower($action), $classMethods)) {
			throw new Exception("missing action: " . $controller->action);
		}
		
		// controller 执行入口
		$controller->initialize($ruler);
		return $this->invoke($controller, $action);
	}
	

	/** 
	 * 模块执行入口
	 */
	protected function invoke(& $controller, $action)
	{
		$ret = $controller->loginCheck();
		
		if($ret) {
			call_user_func_array(array(& $controller, $action), array());
			$controller->echoEnd();
		}
	}
	
}
=======
<?php 

/**
 * 路由转发：
 * 接收客户端请求，根据URL解析路由规则，并转发到相应模块。
 * 
 */

include_once 'loader.php';
include_once 'router.php';

class Dispatcher
{
    public static $_this = null;
    
    public $passargs = null;
    
	protected function __construct()
	{
		
	}

	public static function & instance()
	{
		if(!Dispatcher::$_this)
		{
			Dispatcher::$_this = new Dispatcher;
		}
		
		return Dispatcher::$_this;
	}
	


	/** 
	 * $__url ： 路由规则( 字符串，eg:/ajax/system/user/login )
	 * 根据路由规则做模块转发
	 */
	public function dispatch($__url = null)
	{
		$url = $__url;		
		if(!$url)
		{
			$url = $this->getDefaultUri();
		}
		
		list($serverPath, $url) = $this->parseURI($url);
		if($serverPath == null && $url == null ) {
			echo "no resource found for your path. </br>";
			exit();
		}
		
		$route = & Router::instance();
		$ruler = $route->parse($serverPath, $url);
		
		try {
			$this->executeModule($ruler);
		} catch (Exception $e) {
			echo $e->getMessage();
			exit();
		}
		
	}
	
	/**
	 * 获取默认的 URI 路由规则，例：
	 * env('REQUEST_URI') = /index.php/ajax/system/user/login （index.php作为转发模块入口， /ajax/system/user/login 作为转发路由规则。）
	 * 这里截取出 /ajax/system/user/login 作为URI返回路由规则
	 */
	function getDefaultUri()
	{
		$url = $_SERVER['REQUEST_URI'];
		
		if (strpos($url, 'index.php')) {
            $elements = explode('/index.php', $url);
            $url = $elements[1];
            if($url == '') {
                $url = '/';
            } else if($url[0] != '/') {
                $url .= '/' . $url;
            }
		}
		
		return $url;
	}
	
	/**
	 * 根据路由规则$url，找到模块所在服务器上的目录位置，以及加载相应controller的路由信息，例：
	 * 当 url=/ajax/system/user/login 时， 有
	 * $serverPath = /home/liuyang/workspaces/php/mlf-web/app/system  
	 * $url = /ajax/user/login 
	 * 代表从 $serverPath 中，去加载 $url 所指的controller
	 */
	protected function parseURI($url)
	{
		if($url && $url[0] != '/')
		{
			$url = '/' . $url;
		}
		
		$serverPath = APP_PATH;
		$ajax = 0;
		
		while($url)
		{
			$r = null;
			$regexp = '/([^\/\?]+)/';
			if(!preg_match($regexp, $url, $r))  // preg_match 返回0或1，出错时返回false（例如 $url 为null或者为'/'时）
			{
				break;
			}
			
			if(strtolower($r[1]) == "ajax") {
				$ajax = 1;
				$url = preg_replace('/\/' . strtolower($r[1]) . '/', '', $url);
				continue;
			}
	
			$dir = $serverPath . DS . strtolower($r[1]);
			$filename = $dir . DS . CLASS_DIR . DS . strtolower($r[1]) . CONTROLLER_EXT;

			if (!file_exists($dir)) { // 本地目录根本不存在，可能被攻击，直接拒绝
				return array(null,null);
			}
	
			if(file_exists($filename))
			{
				$serverPath = $dir;
				break;
			}
			else
			{
				$serverPath .= DS . strtolower($r[0]);
				$url = preg_replace('/\/' . strtolower($r[1]) . '/', '', $url);
			}
		}
		
		if($url) {
			if($ajax == 1) {
				$url = "/ajax" . $url;
			}
			
			return array($serverPath, $url);
		} else {
			return array(null,null);
		}
	}


	/** 
	 * 根据规则，到具体模块下执行
	 * $ruler -- 规则实体
	 */
	private function executeModule(& $ruler)
	{
		$serverPath = $ruler->serverPath;
		$ctrlName= strtolower($ruler->ctrlName);
		$actionName= $ruler->actionName;
		
		// 加载模块 controller 的相应php脚本
		$ctrlClass =ucwords($ctrlName) . 'Controller';
		if (!loadController($serverPath, $ctrlName))
		{
			throw new Exception("missing controller: " . $ctrlClass);
		}
		
		// 实例化 controller
		$controller = new $ctrlClass();
		$action = $actionName;
		
		$classMethods = get_class_methods($controller);
		if(in_array($action, $controller->builtins)) {
			throw new Exception("invalid action: " . $action);
		}
		
		if (!in_array($action, $classMethods) && !in_array(strtolower($action), $classMethods)) {
			throw new Exception("missing action: " . $controller->action);
		}
		
		// controller 执行入口
		$controller->initialize($ruler);
		return $this->invoke($controller, $action);
	}
	

	/** 
	 * 模块执行入口
	 */
	protected function invoke(& $controller, $action)
	{
		$ret = $controller->loginCheck();
		
		if($ret) {
			call_user_func_array(array(& $controller, $action), array());
			$controller->echoEnd();
		}
	}
	
}
>>>>>>> d57f0a336aa7c3910557b97a5a113fb4775f5f48
