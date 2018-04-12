<?php 

 /**
 * 路由模块：
 * 自定义添加路由规则，根据uri自动匹配路由规则，并解析模块转发数据。
 */

require_once 'ruler.php'; 

class Router
{
	protected $routes = array();
	
	// 规则的注册顺序不可以调换
	protected function __construct()
	{
		// 匹配ajax情况，uri = /ajax/base/login
		$this->registRule("ajax", "#^/ajax/([^\/\?]+)/([^\/\?]+)\??(.*)$#");
		// 匹配正常情况，uri = /base/login
		$this->registRule("normal", "#^/([^\/\?]+)/([^\/\?]+)\??(.*)$#");
		// 匹配默认情况，uri = /
		$this->registRule("default", "#^/()()\??(.*)$#", array('controller'=>'default', 'action'=>'index'));
	}
	
	public static function & instance()
	{
		static $_this = null;
		if(!$_this)
		{
			$_this = new Router;
		}
		
		return $_this;
	}
	

	/** 
	 * 注册路由规则，例如：
	 * $ruleName -- 规则名称，自定义
	 * $regex    -- 路由的匹配规则
	 * $default  -- 默认的转发规则
	 * 
	 */
	private function registRule($ruleName, $regex, $default = null)
	{
		$this->routes[$ruleName] = array($regex, $default);
		
		/*
		$regex = '#^/ajax/([^\/\?]+)/([^\/\?]+)\??(.*)$#';
		if (preg_match($regex, '/ajax/user/login', $r)) {
			echo "</br>----";
			print_r($r);
		}
		*/
		
	}

	
	/** 
	 * 解析路由规则
	 * 
	 */
	public function parse($serverPath, $url)
	{
		$ruler = new RulerEntiry();
		$ruler->setServerPath($serverPath);
		
		foreach ($this->routes as $ruleName=>$rulerCfg) {
			list($regexp, $defrule) = $rulerCfg;
			
			if (preg_match($regexp, $url, $r)){
				if ($ruleName == "ajax") {
					$ctlName = $r[1] . ".Ajax";
					$actName = $r[2];
				} else if($ruleName == "normal") {
					$ctlName = $r[1];
					$actName = $r[2];
				} else if($ruleName == "default") {
					$ctlName = $defrule['controller'];
					$actName = $defrule['action'];
					$ruler->setServerPath($serverPath . "/default");
				}
				
				$parmArr = array();
				if ($r[3] && count($r)>=3 && $r[3] != '') {
					$parms = explode('&', $r[3]);
					foreach($parms as $parm) {
						$kv = explode('=', $parm);
						$parmArr[$kv[0]] = $kv[1];
					}
				}
				
				$ruler->set($ctlName, $actName, $parmArr);
				
				break;
			}
			
		}
		
		return $ruler;
	}

}
