<?php
if (! isset($_GET['_url']) && isset($_SERVER['PATH_INFO']))
    $_GET['_url'] = $_SERVER['PATH_INFO'];
defined('APP_PATH') || define('APP_PATH', realpath(__DIR__));
define('APP_NAME', 'poker server');
$_SERVER['APP_PATH'] = APP_PATH;
$config = new Phalcon\Config\Adapter\Php(APP_PATH . '/app/config/config.php');
$loader = new Phalcon\Loader();
// echo get_class ( $config->app->loaderDirs->toArray () );
$loader->registerDirs($config->app->loaderDirs->toArray())
->register();
$di = new Phalcon\DI\FactoryDefault();

$view = new Phalcon\Mvc\View();
$view->setViewsDir($config->app->viewsDir);
$di->setShared('view', $view);
$di->setShared('url', $config->url);
$di->setShared('config', $config);

$app = new Phalcon\Mvc\Application($di);
$response = $app->handle()->getContent();
echo $response;