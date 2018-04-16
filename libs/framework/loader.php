<?php 

/**
 * 用途：
 * 加载模块XXController,XXModel,XXView
 *
 */


// 加载模块Controller
function loadController($serverPath, $ctrlname)
{
	$classname = ucwords($ctrlname) . 'Controller';
	if(class_exists($classname))
	{
		return true;
	}

	loadAppMVC('controller');

	$filename = $serverPath . DS . CLASS_DIR . DS . strtolower($ctrlname) . CONTROLLER_EXT;
	if(file_exists($filename))
	{
		include_once($filename);
		return true;
	}

	return false;
}


// 加载模块Model
function loadModel($serverPath, $ctrlname)
{
	$classname = ucwords($ctrlname) . 'Model';
	if(class_exists($classname))
	{
		return true;
	}

	loadAppMVC('model');

	$filename = $serverPath . DS . CLASS_DIR . DS . strtolower($ctrlname) . MODEL_EXT;

	if(file_exists($filename))
	{
		include_once($filename);
		return true;
	}

	return false;
}


// 加载模块View
function loadView($serverPath, $ctrlname)
{
	$classname = ucwords($ctrlname) . 'View';
	if(class_exists($classname))
	{
		return true;
	}

	loadAppMVC('view');

	$filename = $serverPath . DS . CLASS_DIR . DS . strtolower($ctrlname) . VIEWER_EXT;

	if(file_exists($filename))
	{
		include_once($filename);
		return true;
	}

	return false;
}


// 加载基类 controller/model/view 
function loadAppMVC($name)
{
	if(!class_exists(ucwords($name)))
	{
		include_once(LIBS_FRAMEWORK . DS . strtolower($name) . PHP_EXT);
	}

	if(!class_exists('App' . ucwords($name)))
	{
		$filename = 'app.' . strtolower($name) . PHP_EXT;
			
		if(file_exists(LIBS_FRAMEWORK . DS . $filename))
		{
			include_once(LIBS_FRAMEWORK . DS . $filename);
		}
	}
}


// 加载 app/config 下的脚本
function config()
{
	$args = func_get_args();
	foreach($args as $arg)
	{
		$filename = APP_CONFIG_PATH . DS . strtolower($arg) . PHP_EXT;
			
		if(file_exists($filename))
		{
			include_once($filename);
		}
	}
}


