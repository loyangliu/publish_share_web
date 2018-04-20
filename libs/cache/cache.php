<?php 


/**
 * 缓存操作API接口类
 *
 */
abstract class Cache
{
	public abstract function init();
	public abstract function get($key, $id);
	public abstract function set($key, $data, $lifeTime = 0);
	public abstract function delete($key);
	public abstract function gc();
}



// end of script