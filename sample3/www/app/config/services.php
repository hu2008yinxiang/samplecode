<?php
isset($di) && isset($config) || exit('unauthorized operation!');

$di->setShared('db', $config->database);
$di->setShared('redis', $config->redis);
$di->setShared('config', $config);
$di->setShared('view', function () use($config)
{
    $view = new \Phalcon\Mvc\View();
    $view->setViewsDir($config->app->viewsDir);
    return $view;
});
$di->setShared('url', function () use($config)
{
    $url = new \Phalcon\Mvc\Url();
    $url->setBaseUri($config->url->baseUri);
    $url->setStaticBaseUri($config->url->staticBaseUri);
    return $url;
});
$di->setShared('router', function () use($config)
{
    $router = new \Phalcon\Mvc\Router\Annotations(true);
    // $router = new \Phalcon\Mvc\Router(true);
    // $router->removeExtraSlashes(true);
    // $router->addResource($handler);
    $router->removeExtraSlashes(true);
    $router->addResource('Images');
    $router->addResource('Api');
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
$di->setShared('flash', function ()
{
    $flash = new \Flash\Adapter\Memory();
    $flash->setCssClasses(array(
        'error' => 'bg-danger text-danger clear-shadow',
        'warning' => 'bg-warning text-warning clear-shadow',
        'notice' => 'bg-info text-info clear-shadow',
        'success' => 'bg-success text-success clear-shadow'
    ));
    return $flash;
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
        'Api' => array(
            'register',
            'show'
        ),
        'redirect' => array(
            'index'
        ),
        'Images' => array(
            'read'
        )
    );
    $privateResources = array(
        'ads' => array(
            'index',
            'add'
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
