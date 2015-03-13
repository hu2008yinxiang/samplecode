<?php
return array(
    'application' => array(
        'modelsDir' => '/app/models/',
        'viewsDir' => '/app/views/',
        'controllersDir' => '/app/controllers/',
        'libraryDir' => '/app/library/',
        'pluginsDir' => '/app/plugins/',
        'baseUri' => '/index.php/',
        'staticBaseUri' => '/'
    ),
    
    'database' => array(
        'databaseClass' => '\Phalcon\Db\Adapter\Pdo\Mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'dbname' => 'crash_log',
        'username' => 'crash_log_user',
        'password' => 'crash_log_pass'
    ),
    'redis' => array(
        'host' => '127.0.0.1',
        'port' => '6379',
        'database' => '0',
        'password' => ''
    ),
    'storage' => array(
        'baseDir' => '/data/',
        'baseUri' => '/data/'
    ),
    'so_svn' => array(
        'username' => 'CrashReporting',
        'password' => 'qwerasdf',
        'path' => 'https://192.168.110.2:18443/svn/CrashReportingSo/'
    ),
    'black_list' => array(
        'pkg' => 'com.gamemania.zombiecfmod;',
        'version' => ''
    ),
    'auth' => array(
        'ian' => array(
            'pwd' => 'huji2008',
            'role' => 'Users'
        ),
        'arm_core' => array(
            'pwd' => 'arm2014',
            'role' => 'Users'
        ),
        'gy_pro' => array(
            'pwd' => 'crash@self',
            'role' => 'Users'
        )
    )
);