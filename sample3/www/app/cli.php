<?php
defined('APP_LOADED') && exit();
defined('APP_NAME') || define('APP_NAME', 'Poker PHP CLI');
defined('APP_PATH') || define('APP_PATH', dirname(realpath(__DIR__)));
define('APP_LOADED', true);
$config = new Phalcon\Config\Adapter\Php(APP_PATH . '/app/config/config.php');
$loader = new Phalcon\Loader();
$loader->registerDirs($config->app->loaderDirs->toArray())
    ->register();
$di = new Phalcon\DI\FactoryDefault\CLI();

require APP_PATH . '/app/config/services.php';

$app = new Phalcon\CLI\Console($di);
$params = array();
$arguments = array();
// resolve all services
foreach ($di->getServices() as $s) {
    break;
    $name = $s->getName();
    echo 'Loading [', $name, ']', PHP_EOL;
    if ($s->isResolved())
        continue;
    if ($s->isShared()) {
        ${$name} = $di->getShared($name);
    } else {
        ${$name} = $di->get($name);
    }
}
foreach ($argv as $k => $arg) {
    switch ($k) {
        case 0:
            break;
        case 1:
            $arguments['task'] = $arg;
            break;
        case 2:
            $arguments['action'] = $arg;
            break;
        default:
            $params[] = $arg;
            break;
    }
}
$arguments['params'] = $params;
$time_begin = microtime(true);
try {
    $app->handle($arguments);
} catch (Phalcon\Exception $e) {
    echo $e->getMessage();
    // exit(255);
}
$time_end = microtime(true);
//echo PHP_EOL, 'time elapsed: ', $time_end - $time_begin, ' seconds', PHP_EOL;