<?php
defined('APP_NAME') || exit();
defined('APP_PATH') || define('APP_PATH', dirname(dirname(realpath(__DIR__))));
define('DOMAIN', ((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') || (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') ? 'https' : 'http') . '://' . @$_SERVER['HTTP_HOST']);
// define ( 'NETWORK_PROXY', '192.168.110.106:8088' );
// stream_context_set_default(array('http'=>array('proxy'=>'192.168.110.4:3128')));
defined('IN_APP_PHOTO_COUNT') || define('IN_APP_PHOTO_COUNT', 12); // 系统内置头像个数
defined('JSON_UNESCAPED_UNICODE') || define('JSON_UNESCAPED_UNICODE', 256);
defined('JSON_PRETTY_PRINT') || define('JSON_PRETTY_PRINT', 128);
defined('JSON_OPTION') || define('JSON_OPTION', JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
defined('DATA_PATH') || define('DATA_PATH', APP_PATH . '/../../data');
defined('BUYIN_ADJUST_FACTOR') || define('BUYIN_ADJUST_FACTOR', 24);
defined('TABLE_LIMITATION_ADVANCED') || define('TABLE_LIMITATION_ADVANCED', 10000000);
defined('TABLE_LIMITATION_EXPERT') || define('TABLE_LIMITATION_EXPERT', 100000000);
// ini_set('error_log', DATA_PATH . '/poker_' . date('Y-m-d') . '.log');
// --
return array(
    'app' => array(
        'loaderDirs' => array(
            APP_PATH . '/app/controllers',
            APP_PATH . '/app/models',
            APP_PATH . '/app/library',
            APP_PATH . '/app/plugins',
            APP_PATH . '/app/tasks'
        ),
        'viewsDir' => APP_PATH . '/app/views',
        'minaKey' => 'mina-key-here',
        'cronKey' => 'cron-key-here',
        'showOnlineCount' => false,
        'timezone' => 'Asia/Shanghai',/*US/Central*/
        'sys_timezone' => date_default_timezone_get(),
        'cronTime' => 'sunday 21:00',
        'mail' => array(
            'host' => 'smtp.163.com',
            'port' => 25,
            'username' => 'hu2008yinxiang@163.com',
            'password' => 'huji200810261',
            'from' => 'hu2008yinxiang@163.com',
            'fromName' => 'Server',
            'title' => 'GameYep Poker Team',
            'to' => array(
                array(
                    '769305240@qq.com',
                    'Jixu Hu'
                ),
                array(
                    '909050979@qq.com',
                    'Jie'
                )
            )
        ),
        'minClientVersion' => 116
    ),
    
    'redis' => function ()
    {
        $config = array(
            /*'host' => 'localhost',
            'port' => 6379,*/
            'database' => 9,
            'password' => 'hello_world',
            'path' => '/var/run/redis/redis.sock'
        );
        $adapter = new Redis\Adapter\Predis($config);
        return $adapter;
    },
    'database' => function ()
    {
        $config = array(
            'host' => '127.0.0.1',
            'dbname' => 'poker_db',
            'port' => 3306,
            'username' => 'poker_user',
            'password' => 'poker_pass',
            'charset' => 'utf8'
        );
        $database = new Phalcon\Db\Adapter\Pdo\Mysql($config);
        return $database;
    },
    'url' => function ()
    {
        $url = new Phalcon\Mvc\Url();
        $url->setStaticBaseUri(DOMAIN . '/static/');
        $url->setBaseUri(DOMAIN . $_SERVER['SCRIPT_NAME'] . '/');
        return $url;
    },
    'logger' => function ()
    {
        $logger = new \Phalcon\Logger\Adapter\File(DATA_PATH . '/poker_' . $_SERVER['USER'] . '_' . date('Y-m-d') . '.log');
        $logger->setLogLevel(\Phalcon\Logger::INFO);
        if (defined('MICRO_TEST')) {
            $logger->setLogLevel(\Phalcon\Logger::INFO);
        }
        return $logger;
    },
    'bind_config' => array(
        'facebook' => array(
            'AppId' => '971967089498086',
            'Secret' => '3b4dc5e77e7f83a6dc0fe9f3e05e61bd',
            'Adapter' => 'Bind\Adapter\Facebook'
        )
    ),
    'photo' => array(
        'save_path' => DATA_PATH . '/photo',
        'expire' => new DateInterval('P2D'),
        'default' => APP_PATH . '/app/config/iphotos/default.jpg',
        'scan_dir' => APP_PATH . '/app/config/iphotos'
    ),
    'friends' => array(
        'request_timeout' => new DateInterval('P7D')
    ),
    'achievements' => array(
        'configFile' => APP_PATH . '/app/config/achievementsConfig.php'
    ),
    'gifts' => array(
        'fullPack' => DATA_PATH . '/gifts/full.zip',
        'dlcPath' => DATA_PATH . '/gifts/full%d.zip',
        'configFile' => DATA_PATH . '/gift_config.php',
        'url' => 'http://domain.pos/gift/%d.zip',
        'backUrl' => 'http://thinkgeek.vicp.net:48080/index.php/gift/%d'
    ),
    'suites' => array(
        'out_file' => DATA_PATH . '/suites_config.php',
        'csv_file' => APP_PATH . '/app/config/suites.csv'
    ),
    'lottery' => array(
        'rewards' => array(
            1000,
            2000,
            3000,
            4000,
            5000,
            30000,
            40000,
            50000
        ),
        'weights' => array(
            600,
            300,
            200,
            150,
            120,
            20,
            15,
            12
        ),
        'bets' => array(
            1,
            2,
            5,
            10,
            20,
            100
        )
    ),
    'ranksManager' => array(
        'globalKey' => 'ranks:global',
        'rankLimit' => '700000000'
    ),
    'user_account_default' => array(
        'chip' => 40000,
        'diamond' => 9
    ),
    'names' => array(
        'csv_file' => APP_PATH . '/app/config/names3.csv',
        'out_file' => DATA_PATH . '/names.php'
    ),
    'mina' => array(
        'configPath' => DATA_PATH . '/minaConfig.php'
    ),
    'news' => 'http://thinkgeek.vicp.net:48080/news/20141106.php',
    'news_type' => 'notify',
    'sng' => array(
        'buyIns' => array(
            200 => 10,
            1000 => 50,
            2500 => 100,
            5000 => 250,
            10000 => 500,
            100000 => 5000,
            200000 => 6000,
            1000000 => 10000,
            2000000 => 20000,
            10000000 => 100000
        )
    ),
    'shopConfig' => array(
        'dataFile' => DATA_PATH . '/shopConfig.php',
        'monthlyOffer' => array(
            'iapid' => 'monthly6d99',
            'price' => 6.99,
            'amount' => 4000000,
            'perday' => 600000
        )
    ),
    'auth' => array(
        'laohu' => array(
            'role' => 'Users',
            'pwd' => 'huji2008'
        ),
        'nanocore' => array(
            'role' => 'Users',
            'pwd' => 'nano2014'
        )
    ),
    'cache' => function ()
    {
        $cache = null;
        if (extension_loaded('apcu')) {
            $functions = get_extension_funcs('apcu');
            foreach ($functions as $func) {
                $apc_func = str_replace('apcu', 'apc', $func);
                if (! function_exists($apc_func)) {
                    $function = "function $apc_func(){return call_user_func_array('$func',func_get_args());}";
                    eval($function);
                }
            }
            $cache = new \Phalcon\Cache\Backend\Apc(new \Phalcon\Cache\Frontend\Data(array(
                'lifetime' => 60 * 60 * 24 * 2
            )), array(
                'prefix' => 'poker-'
            ));
        } else {
            $cache = new \Phalcon\Cache\Backend\Memory(new \Phalcon\Cache\Frontend\Data(), array(
                'prefix' => 'poker-'
            ));
        }
        // return $cache; // 如果是发布环境可以直接这么返回
        return new \Cache\FileAutoRefreshCache(array(
            $cache
        ));
    },
    'dailytasks' => array(
        '1' => 0,
        '2' => 0,
        '3' => 0,
        '4' => 0,
        '5' => 0,
        '6' => 0,
        '7' => 3,
        '8' => 1,
        '9' => 7,
        '10' => 20,
        '11' => 1,
        '12' => 1
    ),
    'google_play' => array(
        'com.gameyep.holdem' => 'MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAqXv+f9R0GA8ZI0c4b/oT8U6zNkjAvLSn4W480UsuHPcsbH0FbsZxp/xBfSHNI7E6qcgTKGrxTpGl6xHqYFZJF5j2bVotWmH8P4kuTbi4m8P1ID2Bb2VzemUTQV1vzGe4vgWEDvpBX7o/uBqgF1E20qMZXzsy3FZVEvksEwUX8nr4n1Dr2le1m9k6Ff+OsrEdNa82jyvoiFyIZW4/0WsugC5lWLQsWetN3k4NzGmL5owiS7VXPDDHD/SilItpc8RV+u9i3WOcK23xtKV1rnXRDqlpQxTlOrpet1xcLRyYFFnHnhb1yAHRKOgZF0BxOxxzCw1zU3Vz+R6ifgoQTD22saMW0KLiDWy/knAeh3t2iEko4N6jRJ4xPVPosz7nlK3/5GtSAxOLCKlrYF5LuQUE5jeXOK6BUk9nN4G7S4rBlYi5TYTJjoAylb5kBILizftgYCXMJqbdbcuPQ5RHr9hrSsecUvnt34CNo8qPn0ieMUS9BQCgVRC9VGJx9uteJQPg5PAYsnRgk1o2/nifJgPCObOSmC6uiK+ER1cSQU6C7RoUkime537TJAsoE+HJig8PDcI8Y4xp/KvdrWw5ZxJb2T+0zMKCivgoyGYLGNRyTf/66O3KG0YQ5J4KererrbMAy8mZ+LNWMsHvv/SZXBZmTsidIHX1apW069gy8J2vS8kCAwEAAQ=='
        /*Google Play 的 public key*/
    )
);

