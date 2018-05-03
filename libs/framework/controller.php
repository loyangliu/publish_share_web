<?php 

require_once 'ruler.php';
require_once 'loader.php';

class Controller
{
	public $model = null;
	public $view = null;
	public $ruler = null;
	
	public function __construct()
	{
		$this->builtins = array('__construct',
								'initialize',
								'setAction',
								'redirect',
								'beforeFilter',
								'afterFilter');
	}

	
	// 初始化Controller
	public function initialize(& $ruler)
	{
		// 初始化model
		$modelClass = ucwords($ruler->ctrlName). 'Model';
		if(!loadModel($ruler->serverPath, ucwords($ruler->ctrlName))) {
			throw new Exception("missing model: " . $modelClass);
		}

		$this->model = new $modelClass();
		
		// 初始化view
		if(loadView($ruler->serverPath, ucwords($ruler->ctrlName))) {
			$viewClass  = ucwords($ruler->ctrlName). 'View';
		} else {
			if(class_exists("AppView")) {
				$viewClass = 'AppView';
			} else {
				throw new Exception('missing default view: AppView');
			}
		}
		
		$this->view = new $viewClass($ruler);
		
		$this->ruler = $ruler;
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
		
		if($url && $url[0]!='/') {
			$url = '/' . $url;
		}
		header('HTTP/1.1 200 OK');
		header('Location: ' . BASE_URL . $url);
	}

}

	


// end of script
