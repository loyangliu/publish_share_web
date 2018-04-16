<?php 


  /**
   * app view base class,
   * 
   * can override by /app/libs/app.view.php
   * 
   */
class AppView extends View
{
	public function __construct(& $ruler)
	{
		parent::__construct($ruler);
	}
}


// end of script