<?php 

require_once WEBROOT_PATH . '/libs/db/dbfactory.php';
require_once WEBROOT_PATH . '/libs/cache/cachefactory.php';

/**
 * App通用 Model 类
 */
class AppModel extends Model {
	public $db = null;
	public $cache = null;
	
	public function __construct() {
		parent::__construct();
		$this->init();
	}
	
	protected function init() {
		$this->db = DbFactory::instance()->createDBO('default');
	}
}


// end of script