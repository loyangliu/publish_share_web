<?php

  /**
   * @file   log.php
 * @author terisli, isd, tencent <terisli@tencent.com>
 * @date   Wed Apr 08 13:28:51 2009
 * @version 1.0
 * 
 * @brief  log class
 * 
 * 
 */

if(!defined('LOG_FATAL'))
{
	define('LOG_FATAL', 1);
}

if(!defined('LOG_ERROR'))
{
	define('LOG_ERROR', 2);
}

if(!defined('LOG_WARNING'))
{
	define('LOG_WARNONG', 3);
}

if(!defined('LOG_INFO'))
{
	define('LOG_INFO', 4);
}

if(!defined('LOG_DEBUG'))
{
	define('LOG_DEBUG', 5);
}

if (! defined ( 'DEFAULT_LOG_LEVEL' )) {
	define ( 'DEFAULT_LOG_LEVEL', LOG_DEBUG );
}

if (! defined ( 'LOGPATH' )) {
	define ( 'LOGPATH', WEBROOT_PATH . DS . 'log' . DS);
}


/** 
 * log class
 * 
 * 
 * @return 
 */
class Log
{

	//threshold level that enable logoutput,
	protected $maxlevel;

	protected function __construct()
	{
		
	}
	

	/** 
	 * instance a log object
	 * 
	 * @param level 
	 * 
	 * @return 
	 */
	public static function & instance($level)
	{
		static $_this = null;
		
		if(!$_this)
		{
			$_this = new Log;
			$_this->maxlevel = $level;
		}
		
		return $_this;
	}
	


	/** 
	 * write a msg
	 * 
	 * @param msg 
	 * @param level 
	 * 
	 * @return 
	 */
	public function write($msg, $level)
	{
		if($level > $this->maxlevel)
		{
			return;
		}

		$filename = realpath(LOGPATH).DS . date('ymd') . '.log';
		$fp = fopen($filename, 'ab+');
		if(!$fp)
		{
			return;
		}
		
		flock($fp, LOCK_EX);
		fwrite($fp, $msg);
		flock($fp, LOCK_UN);
		fclose($fp);
	}
	
}


// end of script