<?php 

require_once WEBROOT_PATH . '/libs/db/dbfactory.php';

/**
 * App通用 Model 类
 */
class AppModel extends Model {
	public $db = null;
	
	public function __construct() {
		parent::__construct();
		$this->init();
	}
	
	private function init() {
		$this->db = DbFactory::instance()->createDBO('test');
	}
}


// end of script