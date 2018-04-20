<?php 



include_once 'template.php';

/**
 * 
 *
 */
class View
{
	public $template;
	public $ruler;
	public $templatePath = null;
	
	public $vars = array();
	
	public function __construct(& $ruler) {
		$this->template = Template::instance($this);
		$this->ruler= $ruler;
		$this->templatePath = $ruler->serverPath . "/template";
	}
	
	// 展示页
	public function display($template) {
		$filename = $this->templatePath . "/" . $template . ".html";
		if (file_exists($filename)) {
			extract($this->vars);
			include $filename;
		} else {
			throw new Exception("no template found. file=" . $filename);
		}
	}
	
	// 向页面传入php变量
	public function assign($var, $value) {
		$this->vars[$var] = $value;
	}

}
