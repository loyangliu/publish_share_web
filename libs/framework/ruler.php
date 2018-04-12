<?php

/**
 * 模块转发数据实体类：
 * 定义出需要转发controller名称，action名称，转发url名称，请求参数等数据实体
 * 
 */

class RulerEntiry {
	
	public $serverPath;
	public $ctrlName;
	public $actionName;
	public $parmsArr = array();
	
	public function __construct() {
		
	}
	
	public function setServerPath($serverPath) {
		$this->serverPath = $serverPath;
	}
	
	public function set($ctrlName, $actionName, &$parmsArr) {
		$this->ctrlName= $ctrlName;
		$this->actionName= $actionName;
		$this->parmsArr= $parmsArr;
	}
}

?>