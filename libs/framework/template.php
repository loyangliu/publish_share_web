<?php 


/**
 * 加载页面
 *
 */
class Template
{
	
	public $vars = array();
	public $view = null;
	
	protected function __construct() {
	}
	
	
	public static function & instance() {
		static $_this = null;
		if(!$_this) {
			$_this = new Template;
		}
		
		return $_this;
	}
	
	


	// autocase/viewer => autocase/template/viewer.html
	// default/header => default/template/header.html
	// footer => /template/footer.html
	public function getpath($path) {
		$path = explode("/", $path);
		$num = count($path);
		$filename = '';
		
		$templatePath = null;
		if($this->view)
		{
			$templatePath = $this->view->templatePath;
		}
		
		switch($num)
		{
			case 0:
				return false;
				break;
			case 1:
				$filename = $path[0];

				if($templatePath)
				{
					$filename = $templatePath . DS . $filename . HTML_EXT;
					if(file_exists($filename))
					{
						return $filename;
					}
				}
				
				$filename = $path[0];
				$filename = WEBROOT_PATH . DS . TEMPLATES_DIR . DS . $filename . HTML_EXT;
				if(file_exists($filename))
				{
					return $filename;
				}
					
				break;
			default:
				$filename = $path[0];
				array_pop($path);
				$templatePath = WEBROOT_PATH . DS . APP_DIR . DS . implode(DS, $path) . TEMPLATES_DIR;
				$filename = $templatePath . DS . $filename . HTML_EXT;
				if(file_exists($filename))
				{
					return $filename;
				}
						
				break;
		}
		

		return false;
	}
	


/** 
 * display templates
 * 
 * @param templates 
 * 
 * @return 
 */
	public function display($templates = array())
	{
		if(!is_array($templates))
		{
			$templates = func_get_args();
		}
		
		if(empty($templates))
		{
			return;
		}
		else
		{
			$this->parse($templates);
		}
	}
}



/** 
 * support one template include the other
 * 
 * @param templates 
 * 
 * @return 
 */
function tinclude($templates)
{
	$template = & Template::instance();
	$includes = array();
	
	if(is_array($templates))
	{
		$includes = $templates;
	}
	else
	{
		$includes = func_get_args();
	}
	
	extract($template->vars);
	

	foreach($includes as $inc)
	{
		$path = $template->getpath($inc);
		if(!$path)
		{
			ethrow("{$templ} not exist");
		}

		include $path;
	}
}




// end of script
