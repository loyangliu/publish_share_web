<?php 
/*
 * Created on 2016-12-3
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 define('WEBSITE_ROOT', dirname(__FILE__));
 
 /**
  * tips: include,include_once和require,require_once的区别
  * require ： 在执行PHP程序前，就会读入require所引用的文件，变成PHP程序的一部分。如果被引用文件有语法错误，程序中断执行。
  * include ： 在执行PHP程序中，根据流程控制加载include所引用的文件，变成PHP程序的一部分。如果被引用文件有语法错误，程序不会中断，而是继续执行。
  * eg : if(false) { require(x.php); }  if(false) { include(y.php); }  
  * require_once 和 include_once 是为防止重复加载被引用文件。
  */
 /*
 require_once WEBSITE_ROOT.'/router/Router.php';
 require_once WEBSITE_ROOT.'/router/Dispatcher.php'; 
 
 header('Content-type: text/html; charset=utf-8');

 $rout = new Router();
 echo "11111";
 exit;
 Dispatcher::dispatch($rout);
 */


 require __DIR__.'/vendor/autoload.php';
 
 include_once 'project.php';
 
 $dispatcher = Dispatcher::instance();
 $dispatcher->dispatch();
?>
