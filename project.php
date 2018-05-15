<?php

date_default_timezone_set('PRC');

if(!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if(!defined('BASE_URL'))
{
	define('BASE_URL', "/index.php");
}

if(!defined('WEBROOT_PATH'))
{
	define('WEBROOT_PATH', dirname(__FILE__));
}

if(!defined('WEBROOT_DIR'))
{
	define('WEBROOT_DIR', basename(WEBROOT_PATH));
}

if(!defined('APP_PATH'))
{
	define('APP_PATH', WEBROOT_PATH . DS . 'app');
}

if(!defined('APP_DIR'))
{
	define('APP_DIR', basename(APP_PATH)); //app
}

if(!defined('LIBS'))
{
	define('LIBS', WEBROOT_PATH . DS . 'libs');
}

if(!defined('LIBS_FRAMEWORK'))
{
	define('LIBS_FRAMEWORK', LIBS . DS . 'framework');
}

if(!defined('LIBS_DB'))
{
	define('LIBS_DB', LIBS . DS . 'db');
}

if(!defined('LIBS_CACHE'))
{
	define('LIBS_CACHE', LIBS . DS . 'cache');
}

if (! defined ( 'APP_CONFIG_PATH' )) {
	define ( 'APP_CONFIG_PATH', WEBROOT_PATH . DS . 'config' );
}


if(!defined('PHP_EXT'))
{
	define('PHP_EXT', ".php"); //app
}

if(!defined('CONTROLLER_EXT'))
{
	define('CONTROLLER_EXT', '.controller.php');
}

if(!defined('MODEL_EXT'))
{
	define('MODEL_EXT', '.model.php');
}

if(!defined('VIEWER_EXT'))
{
	define('VIEWER_EXT', '.view.php');
}

if(!defined('HTML_EXT'))
{
	define('HTML_EXT', '.html');
}

if(!defined('CLASS_DIR'))
{
	define('CLASS_DIR', 'class');
}





if(!defined('RIGHT_CHECK'))
{
	define('RIGHT_CHECK', 1);
}

if(!defined('SDL_CHECK'))
{
	define('SDL_CHECK', 1);
}

require_once 'libs/framework/loader.php';
require_once'libs/framework/dispatcher.php';
require_once'libs/base/functions.php';

\Carbon\Carbon::setLocale('zh'); 

error_reporting(E_ALL & ~E_NOTICE);