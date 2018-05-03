<?php

require_once WEBROOT_PATH . '/libs/db/dbfactory.php';

class UserModel extends AppModel {
	
	protected function init() {
		$this->db = DbFactory::instance()->createDBO('default');
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