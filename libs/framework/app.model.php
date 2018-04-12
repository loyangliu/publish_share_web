<?php 


if (defined('SCAKE_APP_MODEL_PHP'))
{
	return;
}
else
{
	define('SCAKE_APP_MODEL_PHP', 1);
}


  /**
   * app model base class
   *
   * can override by /app/libs/app.model.php
   * 
   */
class AppModel extends Model
{
	public function __construct($controller) {
		parent::__construct($controller);
	}
}


// end of script