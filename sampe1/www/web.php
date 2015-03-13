<?php
if (! defined('MICRO_TEST')) {
    ini_set('display_errors', false);
}

if (! isset($_GET['_url']) && isset($_SERVER['PATH_INFO']))
    $_GET['_url'] = $_SERVER['PATH_INFO'];
defined('APP_PATH') || define('APP_PATH', realpath(__DIR__));
define('APP_NAME', 'poker server');
$_SERVER['APP_PATH'] = APP_PATH;
$config = new Phalcon\Config\Adapter\Php(APP_PATH . '/app/config/config.php');
$loader = new Phalcon\Loader();
$loader->registerDirs($config->app->loaderDirs->toArray())
    ->register();

$di = new Phalcon\DI\FactoryDefault();
date_default_timezone_set($config->app->timezone);
require APP_PATH . '/app/config/web_services.php';
// $di->get('facebookAdapter')->bind('');
$app = new Phalcon\Mvc\Application($di);
// remove global vars
$di = null;
unset($di);
$config = null;
unset($config);
$loader = null;
unset($loader);

$app->handle()->send();

