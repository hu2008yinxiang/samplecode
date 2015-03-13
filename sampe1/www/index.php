<?php
if (! defined('MICRO_TEST')) {
    ini_set('display_errors', false);
}
// $mem = memory_get_usage();

if (! isset($_GET['_url']) && isset($_SERVER['PATH_INFO']))
    $_GET['_url'] = $_SERVER['PATH_INFO'];
defined('APP_PATH') || define('APP_PATH', realpath(__DIR__));
define('APP_NAME', 'poker server');
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

$app->before(function () use($app)
{
    //
});

$app->finish(function () use($app)
{
    // $resp = $app->response;
    // $length = strlen($resp->getContent());
    // $resp->setHeader('Content-Length', $length);
});
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

$app->map('/gift/{version:[1-9][0-9]?|0}', function ($version) use($app)
{
    $gm = $app->getDI()
        ->get('giftsManager');
    
    $content = $gm->getPack($version);
    if ($content == false) {
        return $app->handle('/404');
    }
    $app->response->setHeader('Content-Disposition', 'attachment;filename=gift' . $version . '.zip');
    $app->response->setContentType('application/oct-stream');
    $app->response->setContent($content);
    return $app->response;
});

$app->map('/news', function () use($app)
{
    include 'news/20141106.php';
    return $app->response;
});

$app->map('/', function () use($app)
{
    $default = array(
        'account_id' => '',
        'session_id' => '',
        'role' => 'client',
        'cmds' => array(),
        'app_version' => 1,
        'device_id' => 'N/A'
    );
    $raw = $app->request->getRawBody();
    $raw_data = json_decode($raw, true);
    
    if (! is_array($raw_data)) {
        $raw = \Misc::gunzip($raw);
        $raw_data = json_decode($raw, true);
        if (! is_array($raw_data)) {
            $raw_data = array();
        }
    }
    $raw_data = array_merge($default, $raw_data);
    
    if (defined('MICRO_TEST')) {
        include APP_PATH . '/app/test/test.php';
        echo $raw = json_encode($raw_data, JSON_OPTION);
        echo PHP_EOL;
    }
    $is_client = (! in_array($raw_data['role'], array(
        'mina',
        'cron'
    )));
    if (! $is_client) {
        // error_log($raw);
    }
    $redis = $app->getDI()
        ->get('redis');
    $content = null;
    if (! empty($raw_data['account_id']) && $is_client) {
        $hash = hash('sha256', $raw);
        $ht = $redis->hget(\SessionManager::SESSION_KEY . $raw_data['account_id'], 'hash');
        if ($ht != null && ($hash == $ht)) {
            $content = $redis->hget(\SessionManager::SESSION_KEY . $raw_data['account_id'], 'content');
        }
    }
    if (empty($content)) {
        foreach ($raw_data['cmds'] as $cmd) {
            // $cmd['app_version'] = $raw_data['app_version'];
            $cmd['_app_version'] = $raw_data['app_version'];
            $cmd['_device_id'] = $raw_data['device_id'];
            Cmds\CmdStore::addCmd($cmd, $raw_data['account_id']);
        }
        if ($is_client) {
            // TODO 追加其他?
        }
        $result = array();
        $result['errno'] = 0;
        if (is_file(DATA_PATH . '/maintaince.txt')) {
            $result['errno'] = Errors::UNDER_MAINTAINCE;
            $result['msg'] = $result['error'] = file_get_contents(DATA_PATH . '/maintaince.txt');
        } elseif (($is_client) && (Cmds\CmdStore::checkSession($raw_data['account_id'], $raw_data['session_id']) == false)) {
            $result['errno'] = Errors::INVALID_SESSION;
        } else {
            $result['results'] = array();
            while (true) {
                $cmd = Cmds\CmdStore::getCmd();
                if (! $cmd) {
                    break;
                }
                $ret = $cmd->execute();
                if ($ret['replace']) {
                    $result = $ret['result'];
                    break;
                }
                if ($ret['stop']) {
                    $result['results'][] = $ret['result'];
                    $result['errno'] = 0;
                    break;
                }
                $result['results'][] = $ret['result'];
                $result['errno'] = 0;
            }
        }
        
        $result['error'] = \Errors::translate($result['errno']);
        $content = Misc::json_encode($result, JSON_OPTION);
        if (! empty($raw_data['account_id']) && $is_client) {
            $pipe = $redis->pipeline();
            $pipe->hmset(\SessionManager::SESSION_KEY . $raw_data['account_id'], array(
                'hash' => $hash,
                'content' => $content
            ));
            $pipe->expire(\SessionManager::SESSION_KEY . $raw_data['account_id'], 604800);
            $pipe->execute();
        }
    }
    $response = $app->response;
    $response->setContentType('application/json', 'UTF-8');
    if (defined('MICRO_TEST')) {
        $response->setContentType('text/plain', 'UTF-8');
    } else {
        $response->setHeader('Content-Length', strlen($content));
    }
    // $response->setJsonContent ( $result );
    $response->setContent($content);
    return $response;
});

$app->notFound(function () use($app)
{
    $app->response->setStatusCode(404, 'Not Found')
        ->sendHeaders();
    include '404.php';
    return $app->response;
});

$app->handle();
// $app->response->send();
//if ($_SERVER['HTTP_HOST'] == 'thinkgeek.vicp.net:48080' || $_SERVER['HTTP_HOST'] == '192.168.110.4:48680') {
//    error_log('ELAPSED: ' . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) . ' MEM: ' . ((memory_get_usage() - $mem) / 1024) . PHP_EOL);
//}

