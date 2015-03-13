<?php
isset($di) && isset($config) || exit('unauthorized operation!');
$di->setShared('db', $config->database);
$di->setShared('redis', $config->redis);
$di->setShared('config', $config);
$di->setShared('url', $config->url);
$di->setShared('view', function () use($config)
{
    $view = new \Phalcon\Mvc\View();
    $view->setViewsDir($config->app->viewsDir);
    return $view;
}); // 头像相关
$di->setShared('photoManager', array(
    'className' => 'PhotoManager',
    'arguments' => array(
        array(
            'type' => 'parameter',
            'value' => $config->photo
        )
    )
));// -- 第三方账号适配器
foreach($config->bind_config as $type=>$options)
{ // $adapter = new $options['Adapter']($options);
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
$di->setShared('dailyGiftManager', 'DailyGiftManager'); // -- 低级监听器
$di->setShared('listenerManager', array(
    'className' => '\ListenerManager',
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
$di->get('listenerManager'); // 主动注册
                                                                                                                                                                                                                               // -- 成就监听器
$di->setShared('achievementManager', array(
    'className' => '\AchievementManager',
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
$di->get('achievementManager'); // 主动注册
                                                                                                                                                                                                                                        // -- 用户信息容器
$di->setShared('fakeUserInfoContainer', '\FakeUserInfoContainer'); // -- 礼物信息
$di->setShared('giftsManager', '\GiftsManager'); // -- 抽奖
$di->setShared('lotteryManager', '\LotteryManager'); // -- 排行
$di->setShared('ranksManager', '\RanksManager');