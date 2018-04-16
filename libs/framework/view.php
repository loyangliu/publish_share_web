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
	
	
	public function __construct(& $ruler) {
		$this->template = Template::instance($this);
		$this->ruler= $ruler;
		$this->templatePath = $ruler->serverPath . "/template";
	}
	
	// 展示页
	public function display($template) {
		$filename = $this->templatePath . "/" . $template . ".html";
		if (file_exists($filename)) {
			include $filename;
		} else {
			throw new Exception("no template found. file=" . $filename);
		}
	}

}
