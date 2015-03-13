<?php
define('XHPROF_ENABLED', FALSE);
if (defined('XHPROF_ENABLED') && isset($_GET['XHPROF_ENABLED'])) {
    xhprof_enable();
}
if (! isset($_GET['_url']) && isset($_SERVER['PATH_INFO']))
    $_GET['_url'] = $_SERVER['PATH_INFO'];
try {
    defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(__DIR__) . '/app');
    $config = new \Phalcon\Config\Adapter\Php(__DIR__ . '/app/config/config.php');
    
    $loader = new \Phalcon\Loader();
    
    $loader->registerDirs(array(
        __DIR__ . $config->application->modelsDir,
        __DIR__ . $config->application->controllersDir,
        __DIR__ . $config->application->libraryDir,
        __DIR__ . $config->application->pluginsDir
    ))->register();
    
    $di = new \Phalcon\DI\FactoryDefault();
    $di->set('config', $config);
    $di->set('view', function () use($config)
    {
        $view = new \Phalcon\Mvc\View();
        $view->setViewsDir(__DIR__ . $config->application->viewsDir);
        if(defined('NO_PAGE')){
            //$view->setRenderLevel(Phalcon\Mvc\View::LEVEL_MAIN_LAYOUT);
            //$view->disableLevel(Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
            //$view->setLayoutsDir(__DIR__ . $config->application->viewsDir . '/index/');
            $view->setMainView('index/route404');
            $view->setRenderLevel(Phalcon\Mvc\View::LEVEL_MAIN_LAYOUT);
        }
        return $view;
    });
    
    $di->set('url', function () use($config)
    {
        $url = new Phalcon\Mvc\Url();
        $domain = ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') ? 'https' : 'http') . '://' . @$_SERVER['HTTP_HOST'];
        $url->setStaticBaseUri($domain . dirname($_SERVER['SCRIPT_NAME']) . '');
        $url->setBaseUri($domain . $_SERVER['SCRIPT_NAME'] . '/');
        return $url;
    });
    
    $di->set('fileUrl', function () use($config)
    {
        $fileUrl = new \Phalcon\Mvc\Url();
        $fileUrl->setBaseUri($config->storage->baseUri);
        $fileUrl->setStaticBaseUri($config->storage->baseUri);
        return $fileUrl;
    });
    
    // $di->set ( 'dispatcher', function () use($di) {
    // $eventsManager = $di->getShared ( 'eventsManager' );
    // $dispatcher = new Phalcon\Mvc\Dispatcher ();
    // $dispatcher->setEventsManager ( $eventsManager );
    // return $dispatcher;
    // } );
    
    $di->set('session', function ()
    {
        $session = new \Phalcon\Session\Adapter\Files();
        $session->start();
        return $session;
    });
    
    $di->set('logger', function ()
    {
        return new \Phalcon\Logger\Adapter\File(__DIR__ . '/app/logs/debug.log');
    });
    
    $di->set('db', function () use($config, $di)
    {
        $db = new $config->database->databaseClass(array(
            'host' => $config->database->host,
            'port' => $config->database->port,
            'dbname' => $config->database->dbname,
            'username' => $config->database->username,
            'password' => $config->database->password
        ));
        // $eventsManager = $di->getShared ( 'eventsManager' );
        // $logger = $di->getShared ( 'logger' );
        // $eventsManager->attach ( 'db', function ($event, $db) use($logger) {
        // if ($event->getType () == 'beforeQuery') {
        // $logger->log ( $db->getSQLStatement (), \Phalcon\Logger::INFO );
        // }
        // } );
        // $db->setEventsManager ( $eventsManager );
        return $db;
    });
    
    $di->set('redis', function () use($config)
    {
        $redis = new \Redis\Adapter\Predis(array(
            'host' => $config->redis->host,
            'port' => $config->redis->port,
            'database' => $config->redis->database,
            'password' => $config->redis->password
        ));
        return $redis;
    });
    $di->set('router', function ()
    {
        $router = new \Phalcon\Mvc\Router();
        $router->add('/view/{status:(release|debug)}/{project}/{versionCode:\d+}/{md5}', 'ViewCrash::showByMD5')
            ->setName('ViewCrash::showByMD5');
        $router->add('/view/{status:(release|debug)}/{project}/{versionCode:\d+}', 'ViewCrash::showByVersionCode')
            ->setName('ViewCrash::showByVersionCode');
        $router->add('/view/{status:(release|debug)}/{project}', 'ViewCrash::showByProject')
            ->setName('ViewCrash::showByProject');
        $router->add('/view/{status:(release|debug)}', 'ViewCrash::showByStatus')
            ->setName('ViewCrash::showByStatus');
        $router->add('/view', 'ViewCrash::showAll')
            ->setName('ViewCrash::showAll');
        $router->add('/', 'ViewCrash::showAll')
            ->setName('ViewCrash::index');
        $router->add('/view/recent', 'ViewCrash::showRecent')
            ->setName('ViewCrash::showRecent');
        $router->add('/view/dump/{dump_file}', 'ViewCrash::showByDumpFile')
            ->setName('ViewCrash::showByDumpFile');
        $router->add('/view/record/{record_id}', 'ViewCrash::showByRecordID')
            ->setName('ViewCrash::showByRecordID');
        $router->add('/view/stack/{stack_md5}', 'ViewCrash::showByStackMD5')
            ->setName('ViewCrash::showByStackMD5');
        $router->add('/archieve', 'ViewCrash::showArchieve')
            ->setName('ViewCrash::showArchieve');
        return $router;
    });
    $di->set('flashSession', function ()
    {
        $flashSession = new \Phalcon\Flash\Session();
        $flashSession->setCssClasses(array(
            'error' => 'alert alert-danger',
            'success' => 'alert alert-success',
            'notice' => 'alert alert-info',
            'warning' => 'alert alert-warning'
        ));
        return $flashSession;
    });
    
    $di->setShared('authSecurity', '\Security');
    
    $di->setShared('dispatcher', function () use($di)
    {
        $em = $di->get('eventsManager');
        $em->attach('dispatch:beforeException', function (\Phalcon\Events\Event $event, \Phalcon\Mvc\Dispatcher $dispatcher, \Exception $exception)
        {
            switch ($exception->getCode()) {
                case \Phalcon\Mvc\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                case \Phalcon\Mvc\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                    $dispatcher->forward(array(
                        'controller' => 'index',
                        'action' => 'route404'
                    ));
                    return false;
            }
        });
        $em->attach('dispatch', $di->get('authSecurity'));
        $dispatcher = new \Phalcon\Mvc\Dispatcher();
        $dispatcher->setDI($di);
        $dispatcher->setEventsManager($em);
        return $dispatcher;
    });
    
    $di->setShared('acl', function ()
    {
        $acl = new \Phalcon\Acl\Adapter\Memory();
        $acl->setDefaultAction(\Phalcon\Acl::DENY);
        $acl->addRole('Admins');
        $acl->addRole('Users');
        $acl->addRole('Guests');
        $publicResources = array(
            'index' => array(
                'index',
                'auth',
                'route404'
            ),
            'upload' => array(
                'index',
                'so'
            )
        );
        $privateResources = array(
            'view_crash' => array(
                '*'
            ),
            'manage' => array(
                '*'
            )
        );
        foreach ($acl->getRoles() as $role) {
            foreach ($publicResources as $resource => $actions) {
                $acl->addResource($resource, $actions);
                $acl->allow($role->getName(), $resource, $actions);
            }
        }
        foreach ($privateResources as $resource => $actions) {
            $acl->addResource($resource, $actions);
            $acl->allow('Users', $resource, $actions);
            $acl->allow('Admins', $resource, $actions);
            $acl->deny('Guests', $resource, $actions);
        }
        return $acl;
    });
    
    $app = new \Phalcon\Mvc\Application($di);
    $app->session;
    $response = $app->handle();
    
    echo $response->getContent();
} catch (\Phalcon\Exception $e) {
    echo $e->__toString();
}
if (defined('XHPROF_ENABLED') && isset($_GET['XHPROF_ENABLED'])) {
    $xhprof_data = xhprof_disable();
    $XHPROF_ROOT = __DIR__;
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
    include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
    
    // save raw data for this profiler run using default
    // implementation of iXHProfRuns.
    $xhprof_runs = new XHProfRuns_Default();
    
    // save the run under a namespace "xhprof_foo"
    $run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");
}