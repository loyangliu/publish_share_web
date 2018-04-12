<?php 

if (defined('SCAKE_TEMPLATE_PHP'))
{
	return;
}
else
{
	define('SCAKE_TEMPLATE_PHP', 1);
}

if(!defined('TEMPLATES_DIR'))
{
	define('TEMPLATES_DIR', 'templates');
}


  /** 
   * template class
   * 
   * 
   * @return 
   */
class Template
{
	
	public $vars = array();
	public $view = null;
	
	protected function __construct()
	{
	}
	
	
/** 
 * instance a object
 * 
 * 
 * @return 
 */
	public static function & instance()
	{
		static $_this = null;
		if(!$_this)
		{
			$_this = new Template;
		}
		
		return $_this;
	}
	
	
/** 
 * set view object
 * 
 * @param view 
 * 
 * @return 
 */
	public function setView(& $view)
	{
		Template::instance()->view = & $view;
	}
	

/** 
 * assign a variable
 * 
 * @param var 
 * @param value 
 * 
 * @return 
 */
	public function assign($var, $value)
	{
		if(is_array($var))
		{
			foreach($var as $key => $val)
			{
				$this->vars[$key] = $val;
			}
		}
		else
		{
			$this->vars[$var] = $value;
		}
	}
	

	public function assignRef($var, & $value)
	{
		$this->vars[$var] = & $value;
	}
	
	
/** 
 * parse template to php
 * 
 * @param templates 
 * 
 * @return 
 */
	public function parse($templates)
	{
		
		if(!is_array($templates))
		{
			$templates = func_get_args();
		}
		
		extract($this->vars);

		
		$contents = '';
		
		foreach($templates as $templ)
		{
			//ob_end_clean();
			//ob_start();
			$path = $this->getpath($templ);
			if(!$path)
			{
				ethrow("{$templ} not exist");
			}
			include $path;
			//$content = ob_get_contents();
			//ob_end_clean();
			//ob_start();
			//$contents .= $content;
		}
		
		return false;
	}
	


	public function fetch($templates)
	{
		if(!is_array($templates))
		{
			$templates = func_get_args();
		}
		
		extract($this->vars);

		@ob_start();
		$contents = '';
		
		foreach($templates as $templ)
		{
			$path = $this->getpath($templ);
			if(!$path)
			{
				ethrow("{$templ} not exist");
			}
			
			include $path;
		}
		
		$contents = @ob_get_contents();
		@ob_end_clean();
		return $contents;
	}



	// autocase/viewer => autocase/template/viewer.html
	// default/header => default/template/header.html
	// footer => /template/footer.html or curctl/template/footer.html
/** 
 * parse path
 * 
 * @param path 
 * 
 * @return 
 */
	public function getpath($path)
	{
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
