<?php 


class Controller
{
	public $model = null;
	public $view = null;
	public $cache = null;
	public $session = null;
	

	//name of controller
	public $name = null;
	//active action
	public $action = null;

	public $baseUrl = null; // xxxx/controller
	public $indexUrl = null; // index url
	public $ajaxUrl = null;

	public $href = null;
	public $webroot = null;
	public $serverPath = null;
	public $virtualPath = null;
	public $hostUrl = null;
	
	public $stylesUrl = null;  // styles url
	public $imagesUrl = null;  // images url
	public $scriptsUrl = null; // scripts url
	

	public $siteStylesUrl = null;
	public $siteImagesUrl = null;
	public $siteScriptsUrl = null;
	public $siteSwfsUrl = null;

	public $uploadUrl = null;

	//$_GET && $_POST
	public $params = null;
	public $uploadFile = null;
	//arguments by /controller/action/*/*/*
	public $passargs = null;

	// builtin function name, cannot use as action
	public $builtins = null;

	public $userData = null;
	
	public function __construct()
	{
		$this->builtins = array('__construct',
								'initialize',
								'setAction',
								'redirect',
								'beforeFilter',
								'afterFilter');
	}

	
/** 
 * initialize controller
 * 
 * 
 * @return 
 */
	public function initialize()
	{
		$modelClass = camelize($this->name) . 'Model';
		$viewClass = camelize($this->name) . 'View';
		
		if(!loadModel($this->serverPath, $this->name))
		{
			ethrow("missing model: $modelClass");
		}

		if(!class_exists($modelClass))
		{
			ethrow("missing model: $modelClass");
		}
		
		$this->model = new $modelClass($this);
		
		
		// allow use default view
		if(!loadView($this->serverPath, $this->name))
		{
			if(!class_exists('AppView'))
			{
				ethrow('missing default view: AppView');
			}
			
			// allow use AppView defalut
			$viewClass = 'AppView';
		}
			
		$this->view = new $viewClass($this);
	}


	public function composeMenu(){
		$baseUrl = substr($this->baseUrl, strlen($this->webroot));
		$sysmenu = SYSMENU_CONFIG::$menu;
		$sysmenu[0]['isActive'] = true; // 默认第一个菜单活跃
		
		foreach ($sysmenu as $menuIndex => & $menu){
			// 在二级菜单(以及二级扩展菜单)里找
			foreach ($menu['subMenu'] as $subMenuIndex => & $subMenu) {
				list($linkBase,) = explode('?', $subMenu['link']);
				if ($linkBase == $baseUrl 
					|| $linkBase == $baseUrl.'/'.$this->action 
					|| ($subMenu['extMenu'] && false !== array_search($baseUrl, $subMenu['extMenu']))
					|| ($subMenu['extMenu'] && false !== array_search($baseUrl.'/'.$this->action, $subMenu['extMenu']))){
					
					// 恢复默认	
					$sysmenu[0]['isActive'] = false;
					$subMenu['isActive'] = true;
					$menu['isActive'] = true;
					break;
				}
			}
		}
		return $sysmenu;
	}


/** 
 * get GET/POST param
 * 
 * @param name 
 * 
 * @return 
 */
	public function getParam($name)
	{
		if(isset($this->params) && isset($this->params[$name]))
		{
			if(is_array($this->params[$name]))
			{
				$tmp = array();
				foreach($this->params[$name] as $param)
				{
					$tmp[] = trim($param);
				}
				return $tmp;
			}
			
			return trim($this->params[$name]);
		}		

		return false;
	}
	

	public function setParam($name, $value)
	{
		$this->params[$name] = trim($value);
	}
	

	public function existParam($name)
	{
		return isset($this->params[$name]);
	}

/** 
 * call action directly
 * 
 * @param action 
 * 
 * @return 
 */
	public function setAction($action)
	{
		$this->action = $action;
		$args = func_get_args();
		unset($args[0]);
		call_user_func_array(array(& $this, $action), $args);
	}


/** 
 * redirect to url
 * 
 * 
 * @return 
 */
	public function redirect($url)
	{
		if(!$url)
		{
			return;
		}
		
		if($url && $url[0] != '/')
		{
			$url = env('SCRIPT_NAME') . '/' . $url;
		}
		
		header('HTTP/1.1 200 OK');
		header('Location: ' . $url);
		exit;
	}
	

/** 
 * before-filter
 * should override by app
 * 
 * 
 * @return 
 */
	public function beforeFilter()
	{
	}
	

/** 
 * after-filter
 * should override by app
 * 
 * @return 
 */
	public function afterFilter()
	{
	}

	

	public function &loadModel($serverPath, $modelName)
	{
		$serverPath = APP_PATH . DS . $serverPath;
		$serverPath = preg_replace('/\//', DS, $serverPath);
		$modelClass = camelize($modelName) . 'Model';

		if(!loadModel($serverPath, $modelName))
		{
			ethrow("missing model: $modelClass");
		}

		if(!class_exists($modelClass))
		{
			ethrow("missing model: $modelClass");
		}
		
		$model = new $modelClass($this);
		return $model;
	}
}

	


// end of script
