<?php
$di->setShared('db', $config->database);
$di->setShared('redis', $config->redis);
$di->setShared('config', $config);
$di->setShared('url', $config->url);
$di->setShared('logger', $config->logger);
$di->setShared('session', function ()
{
    $session = new Phalcon\Session\Adapter\Files(array(
        'uniqueId' => $_SERVER['SCRIPT_NAME']
    ));
    $session->start();
    return $session;
});

$di->setShared('view', function () use($config)
{
    $view = new \Phalcon\Mvc\View();
    $view->setViewsDir($config->app->viewsDir);
    $view->registerEngines(array(
        /*'.tal.html'=>'View\Engine\PHPTAL',*/
        '.volt.html' => 'Phalcon\Mvc\View\Engine\Volt',
        '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
    ));
    return $view;
});

$di->setShared('router', function () use($config)
{
    $router = new \Phalcon\Mvc\Router\Annotations(true);
    // $router = new \Phalcon\Mvc\Router(true);
    // $router->removeExtraSlashes(true);
    // $router->addResource($handler);
    $router->removeExtraSlashes(true);
    // $router->addResource('Images');
    // $router->addResource('Shop');
    $router->add('/', array(
        'controller' => 'index',
        'action' => 'index'
    ));
    
    $router->notFound(array(
        'controller' => 'index',
        'action' => 'route404'
    ));
    return $router;
});

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

$di->setShared('flash', function ()
{
    $flash = new \Flash\Adapter\Memory();
    $flash->setCssClasses(array(
        'error' => 'text-danger',
        'warning' => 'text-warning',
        'notice' => 'text-info',
        'success' => 'text-success'
    ));
    return $flash;
});
$di->setShared('flashSession', function ()
{
    $flash = new Phalcon\Flash\Session();
    $flash->setCssClasses(array(
        'error' => 'text-danger',
        'warning' => 'text-warning',
        'notice' => 'text-info',
        'success' => 'text-success'
    ));
    return $flash;
});

$di->setShared('authSecurity', '\SecurityPlugin');
$di->setShared('security', function ()
{
    $security = new Phalcon\Security();
    $security->setWorkFactor(12);
    return $security;
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
            'route404',
            'logout'
        )
    );
    $privateResources = array(
        'shop' => array(
            'index',
            'add',
            'edit'
        ),
        'log' => array(
            'index'
        ),
        'mina' => array(
            'index'
        ),
        'special-offer' => array(
            'index',
            'rule',
            'help'
        ),
        'news' => array(
            '*'
        ),
        'charge' => array(
            '*'
        ),
        'bank' => array(
            '*'
        ),
        'maintaince' => array(
            '*'
        ),
        'monthly-offer' => array(
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

// --商城
$di->setShared('shopManager', '\ShopManager');
// --特供
$di->setShared('specialOfferManager', '\SpecialOfferManager');
// --MINA切换
$di->setShared('minaSwitcher', '\MinaSwitcher');

// 新版shop
$di->setShared('showBoxManager', '\ShowBoxManager');