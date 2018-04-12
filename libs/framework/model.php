<?php 

if (defined('FRAMEWORK_MODEL_PHP'))
{
	return;
}
else
{
	define('FRAMEWORK_MODEL_PHP', 1);
}

include_once LIBS_DB. DS . 'dbfactory.php';
include_once LIBS_CACHE. DS . 'cachefactory.php';

class Model
{
	public $controller = null;
	public $db = null;
	public $cache = null;
	public $session = null;

	public $params = null;
	public $uploadFile = null;
	
	public $log = null;

	public function __construct(& $controller)
	{
		$this->controller = & $controller;
		
		try 
		{
			$this->db = DbFactory::instance()->createDefaultDBO();
			$this->cache = CacheFactory::instance()->createRedisCache();
		}
		catch(Exception $e)
		{
			echo $e->getTraceAsString();
			exit;
		}
	}


/** 
 * get GET/POST param
 * 
 * @param name 
 * 
 * @return 
 */
	public function getParam($name)
	{
		return $this->controller->getParam($name);
	}

	public function setParam($name, $value)
	{
		return $this->controller->setParam($name, $value);
	}

	public function existParam($name)
	{
		return $this->controller->existParam($name);
	}

	public function escape($sql)
	{
		return $this->db->escape($sql);
	}
	
}



// end of script