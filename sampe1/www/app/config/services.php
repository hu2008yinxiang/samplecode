<?php
isset($di) && isset($config) || exit('unauthorized operation!');
// eventsManager
$di->setShared('eventsManager', '\Events\LazyManager');
$di->setShared('db', $config->database);
$di->setShared('redis', $config->redis);
$di->setShared('config', $config);
$di->setShared('url', $config->url);
$di->setShared('cache', $config->cache);
$di->setShared('modelsCache', $config->cache);
$di->setShared('logger', $config->logger);
$di->setShared('view', function () use($config)
{
    $view = new \Phalcon\Mvc\View();
    $view->setViewsDir($config->app->viewsDir);
    return $view;
});
// 头像相关
$di->setShared('photoManager', 'PhotoManager');
// -- 第三方账号适配器
foreach ($config->bind_config as $type => $options) {
    // $adapter = new $options['Adapter']($options);
    $di->setShared($type . 'Adapter', array(
        'className' => $options['Adapter'],
        'arguments' => array(
            array(
                'type' => 'parameter',
                'value' => $options
            )
        )
    ));
}

$di->setShared('dailyGiftManager', 'DailyGiftManager');
$di->setShared('achievementManager', '\AchievementManager');
$di->setShared('dailyTaskManager', '\DailyTaskManager');
// -- 成就监听器
/*
 * $di->setShared('achievementManager', array(
 * 'className' => '\AchievementManager',
 * 'calls' => array(
 * array(
 * 'method' => 'setEventsManager',
 * 'arguments' => array(
 * array(
 * 'type' => 'service',
 * 'name' => 'eventsManager'
 * )
 * )
 * )
 * )
 * ));
 * $di->get('achievementManager'); // 主动注册
 */

// -- 用户信息容器
$di->setShared('fakeUserInfoContainer', '\FakeUserInfoContainer');

// -- 礼物信息
$di->setShared('giftsManager', '\GiftsManager');

// -- 抽奖
$di->setShared('lotteryManager', '\LotteryManager');

// -- 排行
$di->setShared('ranksManager', '\RanksManager');

// --商城
$di->setShared('shopManager', '\ShopManager');
// --特供
$di->setShared('specialOfferManager', '\SpecialOfferManager');

// --MINA切换
$di->setShared('minaSwitcher', '\MinaSwitcher');

// 注册监听器
$eventsManager = $di->get('eventsManager');
// $eventsManager->attach('user','');
$lr = function ($event, $name, $class) use(&$di, $eventsManager)
{
    $di->setShared($name, array(
        'className' => $class,
        'calls' => array(
            array(
                'method' => 'setEventsManager',
                'arguments' => array(
                    array(
                        'type' => 'service',
                        'name' => 'eventsManager'
                    )
                )
            )
        )
    ));
    $eventsManager->attach($event, array(
        'type' => 'service',
        'name' => $name
    ));
};
$lr('user', 'userListener', '\Listeners\UserListener');
$lr('table', 'tableListener', '\Listeners\TableListener');
$lr('achievement:maxBetChanged', 'maxBetListener', '\Listeners\MaxBetListener');
$lr('achievement:maxBuyInChanged', 'maxBuyInListener', '\Listeners\MaxBuyInListener');
$lr('achievement:maxWinChanged', 'maxWinListener', '\Listeners\MaxWinListener');
unset($lr);

// 活动
$di->setShared('newsManager', '\NewsManager');

// 新版shop
$di->setShared('showBoxManager', '\ShowBoxManager');

// 附近的人
$di->setShared('nearByManager', '\NearByManager');