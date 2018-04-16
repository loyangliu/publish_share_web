<?php 

class Model
{
	public $controller = null;
	public function __construct(& $controller) {
		$this->controller = & $controller;
	}
	
}
