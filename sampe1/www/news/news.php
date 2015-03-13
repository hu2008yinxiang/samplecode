<?php
if (! isset($_GET['_url']) && isset($_SERVER['PATH_INFO']))
    $_GET['_url'] = $_SERVER['PATH_INFO'];
defined('APP_PATH') || define('APP_PATH', realpath(dirname(__DIR__)));
define('APP_NAME', 'news server');
$_SERVER['APP_PATH'] = APP_PATH;
$config = new Phalcon\Config\Adapter\Php(APP_PATH . '/app/config/config.php');
// 设置程序时区
date_default_timezone_set($config->app->timezone);
$loader = new Phalcon\Loader();
$loader->registerDirs($config->app->loaderDirs->toArray())
    ->register();

$di = new Phalcon\DI\FactoryDefault();
$di->setShared('loader', $loader);

require APP_PATH . '/app/config/services.php';
// $di->get('facebookAdapter')->bind('');
$app = new Phalcon\Mvc\Micro($di);
// remove global vars
$di = null;
unset($di);
$config = null;
unset($config);
$loader = null;
unset($loader);

$app->map('/photo/{account_id}', function ($account_id) use($app)
{
    
    if ($account_id) {
        $account_id = pathinfo($account_id, PATHINFO_FILENAME);
        $response = $app->response;
        $photoManager = $app->getDI()
            ->get('photoManager');
        $content = $photoManager->readPhoto($account_id);
        if ($content) {
            $response->setContent($content);
            $response->setContentType('image/jpeg');
            $response->setExpires(new DateTime('1 week'));
            return $response;
        } else {}
    }
    return $app->handle('/404');
});

$app->map('/images/{file}', function ($file) use($app)
{
    $file = basename($file);
    $imageFile = DATA_PATH . '/news/images/' . $file;
    if (! is_file($imageFile)) {
        return $app->handle('/404');
    }
    $app->response->setContentType('image/png');
    $app->response->setExpires(new DateTime('1 week'));
    $app->response->setContent(file_get_contents($imageFile));
    return $app->response;
});

$app->map('/', function () use($app)
{
    include APP_PATH . '/news/dyn.php';
    return $app->response;
});

$app->notFound(function () use($app)
{
    $app->response->setStatusCode(404, 'Not Found')
        ->sendHeaders();
    include APP_PATH . '/404.php';
    return $app->response;
});

$app->handle();

