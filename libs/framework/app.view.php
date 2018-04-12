<?php 

if (defined('FRAMEWORK_APP_VIEW_PHP'))
{
	return;
}
else
{
	define('FRAMEWORK_APP_VIEW_PHP', 1);
}




  /**
   * app view base class,
   * 
   * can override by /app/libs/app.view.php
   * 
   */

class AppView extends View
{
	public function __construct(& $controller)
	{
		parent::__construct($controller);
	}
}


// end of script