<?php 


if (defined('FRAMEWORK_VIEW_PHP'))
{
	return;
}
else
{
	define('FRAMEWORK_VIEW_PHP', 1);
}

include_once 'template.php';

  /** 
   * view class
   * 
   * 
   */
class View
{
	//template object
	public $template;
	public $controller;
	
	public $templatePath = null;   // templates path
	
	
	public function __construct(& $controller)
	{
		$this->template = & Template::instance($this);
		$this->controller = & $controller;
		
		$this->templatePath = $controller->serverPath . DS . TEMPLATES_DIR;
	}

	

/** 
 * assign a variable
 * 
 * @param var 
 * @param name 
 * 
 * @return 
 */
	public function assign($var, $value)
	{
		$this->template->assign($var, $value);
	}

	public function assignRef($var, & $value)
	{
		$this->template->assignRef($var, $value);
	}
	

/** 
 * render template
 * 
 * @param template 
 * 
 * @return 
 */
	public function render($templates)
	{
		if(!is_array($templates))
		{
			$templates = func_get_args();
		}
		
		$this->beforeRender();
		$this->template->setView($this);
		$this->template->display($templates);
		$this->afterRender();
	}


/** 
 * display
 * 
 * @param template 
 * 
 * @return 
 */
	public function display($templates)
	{
		if(!is_array($templates))
		{
			$templates = func_get_args();
		}
		
		return $this->render($templates);
	}
	

	public function fetch($templates)
	{
		if(!is_array($templates))
		{
			$templates = func_get_args();
		}

		$this->beforeRender();
		$this->template->setView($this);
		$contents = $this->template->fetch($templates);
		$this->afterRender();
		return $contents;
	}


/** 
 * before-render
 * should override by app
 * 
 * 
 * @return 
 */
	public function beforeRender()
	{
	}
	

/** 
 * after-render
 * should override by app
 * 
 * @return 
 */
	public function afterRender()
	{
	}
	
}



// end of script
