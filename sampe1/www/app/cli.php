<?php
defined('APP_LOADED') && exit();
defined('APP_NAME') || define('APP_NAME', 'Poker PHP CLI');
defined('APP_PATH') || define('APP_PATH', dirname(realpath(__DIR__)));
define('APP_LOADED', true);
$config = new Phalcon\Config\Adapter\Php(APP_PATH . '/app/config/config.php');
date_default_timezone_set($config->app->timezone);
if ($_SERVER['SCRIPT_NAME'] != '-') {
    ini_set('error_log', DATA_PATH . '/cli_' . $_SERVER['USER'] . '_' . date('Y-m-d') . '.log');
    error_reporting(E_ALL | E_STRICT);
}
$loader = new Phalcon\Loader();
$loader->registerDirs($config->app->loaderDirs->toArray())
    ->register();
$di = new Phalcon\DI\FactoryDefault\CLI();

require APP_PATH . '/app/config/services.php';

$app = new Phalcon\CLI\Console($di);
$params = array();
$arguments = array();

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
try {
    $app->handle($arguments);
} catch (Phalcon\Exception $e) {
    echo $e->getMessage();
    // exit(255);
}
//echo PHP_EOL, 'time elapsed: ', $time_end - $time_begin, ' seconds', PHP_EOL;