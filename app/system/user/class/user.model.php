<?php

require_once WEBROOT_PATH . '/libs/db/dbfactory.php';

class UserModel extends AppModel {
	
	public function __construct() {
		$this->db = DbFactory::instance()->createDBO('test');
	}
	
	public function checkLogin($name, $password) {
		$sql = " select uid,username,password from wiki_user where username='{$name}' ";
		$row = $this->db->getRow($sql);
		
		if(!$row) {
			return false;
		}
		
		return $row;
	}
}

?>