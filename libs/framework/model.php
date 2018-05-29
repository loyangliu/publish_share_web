<?php 

require_once 'log.php';

class Model
{
	protected $_log = null;
	
	public function __construct() {
		$this->_log = Log::instance ( DEFAULT_LOG_LEVEL );
	}
	
	public function tostring() {
		return get_class ( $this );
	}
	
	public function log($msg, $level = LOG_DEBUG) {
		$trace = debug_backtrace ();
		$content = '<' . $level . '> ';
		$content .= '[' . date ( 'Y-m-d H:i:s' ) . '] ';
		
		$count = 1;
		$filename = str_replace ( WEBROOT_PATH . '/', '', $trace [0] ['file'], $count );
		$content .= '{' . $filename . ',' . $trace [0] ['line'] . ',' . $this->tostring () . '} ';
		$content .= $msg;
		$content .= "\n";
		$this->_log->write ( $content, $level );
	}
	
}
