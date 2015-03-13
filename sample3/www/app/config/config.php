<?php
defined('APP_NAME') || exit();
defined('APP_PATH') || define('APP_PATH', dirname(dirname(realpath(__DIR__))));
defined('DATA_PATH') || define('DATA_PATH', APP_PATH . '/../../data');
return array(
    'app' => array(
        'loaderDirs' => array(
            APP_PATH . '/app/controllers',
            APP_PATH . '/app/models',
            APP_PATH . '/app/library',
            APP_PATH . '/app/plugins',
            APP_PATH . '/app/tasks'
        ),
        'viewsDir' => APP_PATH . '/app/views/',
        'minaKey' => 'mina-key-here',
        'cronKey' => 'cron-key-here',
        'showOnlineCount' => false
    ),
    'url' => array(
        'staticBaseUri' => '/statics/',
        'baseUri' => '/'
    ),
    'images' => array(
        'baseUri' => 'dimages/f/'
    ),
    'redis' => function ()
    {
        $config = array(
            'host' => 'localhost',
            'port' => 6379,
            'database' => 9,
            'password' => 'hello_world',
            /* 'path'=>'/var/run/redis/redis.sock' */
        );
        $adapter = new Redis\Adapter\Predis($config);
        return $adapter;
    },
    'database' => function ()
    {
        $config = array(
            'host' => '127.0.0.1',
            'dbname' => 'solidarity_db',
            'port' => 13306,
            'username' => 'solidarity_user',
            'password' => 'pwsolidarityd',
            'charset' => 'utf8'
        );
        $database = new Phalcon\Db\Adapter\Pdo\Mysql($config);
        return $database;
    },
    'auth' => array(
        'ian' => array(
            'pwd' => 'huji2008',
            'role' => 'Admins'
        ),
        'arm_core' => array(
            'pwd' => 'arm2014',
            'role' => 'Users'
        )
    )
);

