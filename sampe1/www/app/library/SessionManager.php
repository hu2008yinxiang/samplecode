<?php

class SessionManager
{

    const SESSION_KEY = 'sn:';

    const ONLINE_KEY = 'ol';

    const TABLE_SESSION_PREFIX = 'p:user:session:';
    
    // 7*24*60*60
    /**
     * 过期时间
     *
     * @var int
     */
    const SESSION_EXPIRE = 604800;

    public static function addLoginSession($account_id)
    {
        $di = \Phalcon\DI::getDefault();
        $redis = $di->get('redis');
        $session = time();
        $key = static::getSessionKey($account_id);
        $redis->hset($key, 'sn', $session);
        $redis->expire($key, static::SESSION_EXPIRE);
        return $session;
    }

    public static function getLoginSession($account_id)
    {
        if (empty($account_id))
            return '';
        $di = \Phalcon\DI::getDefault();
        $redis = $di->get('redis');
        $key = static::getSessionKey($account_id);
        $session = $redis->hget($key, 'sn');
        return $session;
    }

    public static function enterTable($session, $data)
    {
        if (($data['WLR'] == 'w' || $data['WLR'] == 'l') && (! isset($data['bigBlind']) || empty($data['bigBlind']))) {
            trigger_error('BigBlind is empty!');
            trigger_error(var_export($data, true));
        }
        ksort($data);
        $di = \Phalcon\DI::getDefault();
        $key = static::getTableSessionKey($session);
        $redis = $di->get('redis');
        $redis->del($key);
        $redis->hincrby(static::ONLINE_KEY, $data['bigBlind'], 1);
        $data['speed'] = $data['speed'][0];
        // $logger = $di->get('logger');
        // $logger->notice('Enter ' . $session . ' table with data: ' . PHP_EOL . var_export($data, true));
        // 写入redis
        $redis->hmset($key, $data);
        $redis->expire($key, static::SESSION_EXPIRE);
        $sk = static::getSessionKey($data['accountId'], 'sn');
        $redis->hset($sk, 'ts', $session); // 记录在线session
        $di->get('eventsManager')->fire('table:enterTable', \UserAccounts::findFirstByAccountId($data['accountId']), $data);
    }

    public static function exitTable($session, $success = true)
    {
        // return;
        $di = \Phalcon\DI::getDefault();
        $key = static::getTableSessionKey($session);
        $redis = $di->get('redis');
        $data = $redis->hgetall($key);
        if (empty($data)) {
            return;
        }
        //
        if (isset($data['bigBlind']) && isset($data['WLR']) && ($data['WLR'] == 'w' || $data['WLR'] == 'l')) {
            $val = $redis->hincrby(static::ONLINE_KEY, $data['bigBlind'], - 1);
            if ($val < 0 && isset($data['bigBlind'])) {
                $redis->hset(static::ONLINE_KEY, $data['bigBlind'], 0);
            }
        }
        if (isset($data['accountId'])) {
            $sk = static::getSessionKey($data['accountId'], 'sn');
            $redis->hdel($sk, 'ts', $session); // 记录在线session
            $ua = \UserAccounts::findFirstByAccountId($data['accountId']);
            if (! $ua) {
                error_log('LAOHU: ' . 'error when exit table can\' determine the actual user!!!');
                return;
            }
            $data['session'] = $session;
            if ($success) {
                $di->get('eventsManager')->fire('table:exitTable', $ua, $data);
            }
        }
        $redis->del($key);
    }

    public static function isOnline($account_id)
    {
        $sk = static::getSessionKey($account_id);
        $redis = \Phalcon\DI::getDefault()->get('redis');
        $session = $redis->hget($sk, 'ts');
        if ($session != null) {
            $key = static::getTableSessionKey($session);
            $ret = ($redis->exists($key) ? 1 : 0);
            if ($ret) {
                $wlr = $redis->hget($key, 'WLR');
                if ($wlr == 'N') {
                    return 2;
                }
                if ($wlr[0] == 'R') {
                    return 3;
                }
                return 1;
            }
            $redis->hdel($sk, 'ts');
        }
        return 0;
    }

    public static function updateTable($session, array $data = null, \UserAccounts $ua = null)
    {
        if (is_null($data) || empty($data)) {
            $di = \Phalcon\DI::getDefault();
            $key = static::getTableSessionKey($session);
            $redis = $di->get('redis');
            $data = $redis->hgetall($key);
            $ua = \UserAccounts::findFirstByAccountId($data['accountId']);
        }
        
        $keys = array(
            'c0',
            'c1',
            'c2',
            'c3',
            'c4',
            'c5'
        );
        sort($keys);
        $intersect = array_intersect(array_keys($data), $keys);
        sort($intersect);
        if ($keys == $intersect) {
            $rdata = $ua->best_hand;
            $ndata = array(
                $data['c0'],
                $data['c1'],
                $data['c2'],
                $data['c3'],
                $data['c4'],
                $data['c5']
            );
            // $ndata = static::getBestHand($rdata, $ndata);
            // if ($rdata != $ndata)
            {
                $ua->best_hand = $ndata;
                // $ua->save();
            }
        }
        $ua->round += $data['hands'];
        $ua->save();
    }

    public static function getBestHand(array $data1, array $data2)
    {
        if ($data1[5] > $data2[5]) {
            return $data1;
        } elseif ($data1[5] < $data2[5]) {
            return $data[2];
        } else {
            $i = 0;
            while ($i < 5) {
                if ($data1[$i] > $data2[$i]) {
                    return $data1;
                } elseif ($data1[$i] < $data2[$i]) {
                    return $data2;
                }
                ++ $i;
            }
            return $data1;
        }
    }

    public static function getTableSession($account_id)
    {
        $key = static::getSessionKey($account_id);
        $di = \Phalcon\DI::getDefault();
        $redis = $di->get('redis');
        $ss = $redis->hget($key, 'ts');
        if (! empty($ss)) {
            return $ss;
        }
        $redis->hdel($key, 'ts');
        return false;
    }

    public static function getSessionKey($account_id)
    {
        return static::SESSION_KEY . $account_id;
    }

    public static function getTableSessionKey($session)
    {
        return static::TABLE_SESSION_PREFIX . $session;
    }

    public static function genTableSession($account_id)
    {
        $di = \Phalcon\DI::getDefault();
        $redis = $di->get('redis');
        $key = static::getSessionKey($account_id);
        $val = $redis->hincrby($key, 'sc', 1);
        if ($val != ($val % 8)) {
            $val = $val % 8;
            $redis->hset($key, 'sc', $val);
        }
        $redis->expire($key, static::SESSION_EXPIRE);
        return $account_id . ':' . $val;
    }

    public static function getTableServer($account_id)
    {
        $di = Phalcon\DI::getDefault();
        $servers = $di->get('minaSwitcher')->getMinaServers();
        $count = count($servers) - 1;
        $index = rand(0, $count);
        $server = $servers[$index];
        $extras = \Extras::load($account_id, \Extras::LAST_MINA, $server);
        $extras->value = $server;
        $extras->save();
        return $server;
    }

    public static function checkIP()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = 'ip:' . $ip;
        $limit = 10;
        $expire = 1 * 60;
        $di = \Phalcon\DI::getDefault();
        $redis = $di->get('redis');
        $count = $redis->llen($key);
        if ($count < $limit) {
            $redis->lpush($key, time());
        } else {
            $time = $redis->lindex($key, - 1);
            if ((time() - $time) < $expire) {
                //
                $redis->expire($key, $expire);
                return false;
            } else {
                $redis->lpush($key, time());
                $redis->ltrim($key, 0, 9);
            }
        }
        $redis->expire($key, $expire);
        return true;
    }

    protected static function innerTableList()
    {
        $list = array(
            20 => array(
                200,
                4000
            ),
            50 => array(
                500,
                10000
            ),
            200 => array(
                2000,
                40000
            ),
            400 => array(
                4000,
                80000
            ),
            2000 => array(
                20000,
                400000
            ),
            4000 => array(
                40000,
                800000
            ),
            20000 => array(
                200000,
                4000000
            ),
            40000 => array(
                4000000,
                8000000
            ),
            80000 => array(
                8000000,
                16000000
            ),
            200000 => array(
                2000000,
                40000000
            ),
            400000 => array(
                4000000,
                80000000
            ),
            2000000 => array(
                20000000,
                400000000
            ),
            4000000 => array(
                40000000,
                800000000
            ),
            10000000 => array(
                100000000,
                2000000000
            ),
            20000000 => array(
                200000000,
                4000000000
            ),
            40000000 => array(
                400000000,
                8000000000
            ),
            100000000 => array(
                1000000000,
                20000000000
            ),
            500000000 => array(
                5000000000,
                100000000000
            )
        );
        return $list;
    }

    public static function getTableList($withOnline = true)
    {
        $list = static::innerTableList();
        if ($withOnline) {
            foreach ($list as $key => $data) {
                $list[$key][] = static::getOnlineCount($key);
            }
        }
        return $list;
    }

    public static function getOnlineCount($bigBlind)
    {
        $di = \Phalcon\DI::getDefault();
        if (! ($di->get('config')->app->showOnlineCount))
            return 0;
        $redis = $di->get('redis');
        $value = $redis->hget(static::ONLINE_KEY, $bigBlind);
        if (is_null($value)) {
            $value = rand(50, 802);
            $redis->hset(static::ONLINE_KEY, $bigBlind, $value);
        }
        return $value;
    }

    public static function getTotalOnline()
    {
        $di = \Phalcon\DI::getDefault();
        if (! ($di->get('config')->app->showOnlineCount))
            return 0;
        $redis = $di->get('redis');
        $vals = $redis->hvals(static::ONLINE_KEY);
        $value = array_sum($vals);
        return $value;
    }

    public static function defaultTableData()
    {
        return array(
            'hands' => 0,
            'maxWin' => 0,
            'maxBet' => 0,
            'totalWin' => 0,
            'winHands' => 0,
            'continueWinTimes' => 0
        );
    }

    /**
     * 添加附加登录信息
     *
     * @param \UserAccounts $ua            
     * @param array $info            
     */
    public static function getLoginExtras($ua, &$info)
    {
        $di = \Phalcon\DI::getDefault();
        $info['account_id'] = $ua->account_id;
        $info['account_token'] = $ua->account_token;
        $info['bind_token'] = $ua->bind_token;
        $info['session_id'] = \SessionManager::addLoginSession($ua->account_id);
        $info['online_players'] = \SessionManager::getTotalOnline();
        $info['gift_version'] = $di->get('giftsManager')->getVersion();
        $info['gift_url'] = $di->get('giftsManager')->getUrl();
        $info['news'] = $di->get('newsManager')->getNewsUrl($ua);
        $info['news_type'] = $di->get('config')->news_type;
        $festival_img = $di->get('newsManager')->getFestivalImageUrl($ua);
        if ($festival_img) {
            $info['festival_img'] = $festival_img;
        }
        
        $info['login_reward'] = array(
            'rewarded' => true
        );
        $info['has_free_lottery'] = ! ($ua->last_lottery == date('Y-m-d'));
        $info['free_lottery_scale'] = $ua->getDailyFreeSpinScale();
        $info['day_tag'] = date('Ymd');
        $settleDate = new DateTime($di->get('config')->app->cronTime);
        $nowDate = new DateTime();
        if ($nowDate >= $settleDate) { // 如果结算时间已过 延期到下一周
            $settleDate->add(DateInterval::createFromDateString('1 week'));
            // $settleDate = new DateTime('next week ' . $di->get('config')->app->cronTime);
        }
        $info['settle_time'] = $settleDate->getTimestamp();
        $nowDate = new DateTime('now');
        $info['now_time'] = $nowDate->getTimestamp();
        $extra = \Extras::load($ua->account_id, \Extras::LAST_LOGIN_REWARD, '00000000');
        if ($extra->value != date('Ymd')) {
            $info['login_reward']['rewarded'] = false;
            $info['login_reward']['login_combo'] = $ua->login_combo;
            $info['login_reward']['extras'] = $di->get('newsManager')->getExtraLoginBonus($ua);
            $info['login_reward']['vip_scale'] = $ua->getLoginRewardScale();
            if ($ua->chip <= 200) {
                $ua->chip = 500;
                $ua->save();
            }
        }
        $ranks = Ranks::load($ua->account_id);
        $gr = $ranks->reward_tag[0] == '1';
        $fr = $ranks->reward_tag[1] == '1';
        if ($gr || $fr) {
            $info['last_rank'] = true;
        }
        // 月卡
        $monthlyOffer = $di->get('shopManager')->loadMonthlyOffer($ua->account_id);
        $today = date_create('today');
        if ($monthlyOffer->value['end'] >= $today && $monthlyOffer->value['current'] < $today && $monthlyOffer->value['perday'] > 0) {
            $info['monthly_offer'] = $monthlyOffer->value['perday'];
        }
    }

    public static function getWLR($account_id)
    {
        $ret = 'l';
        $me = \UserAccounts::findFirstByAccountId($account_id);
        if ($me->level < 10) {
            $ret = 'w';
        }
        if ($me->chip > 400000) {
            $ret = 'l';
        }
        $today = new DateTime();
        // 默认活到昨天
        $extra = static::getLivingPay($account_id);
        // 活到今天
        $dieDate = \DateTime::createFromFormat('Y-m-d H:i:s', $extra->value['time']);
        if ($today <= $dieDate && $extra->value['count'] > 0) {
            $ret = 'w';
        }
        return $ret;
    }

    public static function getLivingPay($account_id)
    {
        $tomorow = new DateTime('-1 seconds');
        $extra = \Extras::load($account_id, \Extras::LIVING_PAY, array(
            'time' => $tomorow->format('Y-m-d H:i:s'),
            'count' => 0
        ));
        return $extra;
    }

    public static function setLivingPay($account_id, $pay)
    {
        $pay_lives = array(
            '0.99' => array(
                'time' => '12 hours',
                'count' => 15
            ),
            '4.99' => array(
                'time' => '1 day',
                'count' => 75
            ),
            '9.99' => array(
                'time' => '2 days',
                'count' => 150
            ),
            '19.99' => array(
                'time' => '3 days',
                'count' => 300
            ),
            '49.99' => array(
                'time' => '4 days',
                'count' => 750
            ),
            '99.99' => array(
                'time' => '7 days',
                'count' => 1500
            )
        );
        $livingDate = new DateTime('-1 seconds');
        $extra = \Extras::load($account_id, \Extras::LIVING_PAY, array(
            'time' => $livingDate->format('Y-m-d H:i:s'),
            'count' => 0
        ));
        $livingDate = DateTime::createFromFormat('Y-m-d H:i:s', $extra->value['time']);
        $newDate = new DateTime('-1 seconds');
        $count = $extra->value['count'];
        if (! $livingDate) {
            $livingDate = new DateTime('-1 seconds');
            $count = 0;
        }
        $today = new DateTime();
        if ($today > $livingDate) { // 已过期
            $livingDate = $today;
            $count = 0;
        }
        $key = '' . $pay;
        if (isset($pay_lives[$key])) {
            $life = $pay_lives[$key];
            $newDate->add(DateInterval::createFromDateString($life['time']));
            $count = $life['count'] > $count ? $life['count'] : $count;
        }
        if ($livingDate < $newDate) {
            $livingDate = $newDate;
        }
        $extra->value = array(
            'time' => $livingDate->format('Y-m-d H:i:s'),
            'count' => $count
        );
        $extra->save();
        unset($extra);
    }

    public static function fillData(array &$data, \UserAccounts $ua)
    {
        // 填入场数
        $data['level'] = $ua->level;
        $data['exp'] = $ua->exp;
        $data['scale'] = $ua->getExpScale();
        $data['hands'] = $ua->round; // 覆盖
        $data['accountId'] = $ua->account_id;
        $data['maxWin'] = 0; // 比较覆盖
        $data['maxBet'] = 0; // 比较覆盖
        $data['totalWin'] = 0; // 累加
        $data['winHands'] = $ua->win_round; // 覆盖
        $data['continueWinTimes'] = 0; // 比较覆盖
        $best_hand = $ua->best_hand;
        foreach ($best_hand as $k => $v) {
            $data['c' . $k] = $v; // 覆盖
        }
        $data['orgBuyIn'] = $data['buyIn'];
        $data['orgFee'] = isset($data['fee']) ? $data['fee'] : 0;
        if ($data['WLR'] == 'w' || $data['WLR'] == 'l') {
            
            $just_reged = ($ua->threshold == - 1);
            if ($just_reged) {
                // $reg_date = date_create_from_format('Y-m-d H:i:s', $ua->reg_time);
                // date_add($reg_date, date_interval_create_from_date_string('7 days'));
                // $just_reged = $reg_date > date_create();
                $just_reged = $ua->round <= 60;
                
                if (! $just_reged) {
                    $ua->threshold = $ua->chip;
                    if ($ua->threshold > 50000) {
                        $ua->threshold = 50000;
                    }
                    $ua->thc = 100;
                }
            }
            
            $extras = \Extras::load($ua->account_id, \Extras::EXTRA_THRESHOLD, 0);
            $value = $extras->value;
            if ($value != 0) {
                $ua->threshold = $value;
                $ua->thc = 100;
                $ua->chands = 20;
                $extras->value = 0;
                $extras->save();
            }
            
            $data['chands'] = $ua->chands;
            $data['thc'] = $ua->thc;
            $data['threshold'] = $ua->threshold;
            $ua->save();
        }
        $data['cog'] = $ua->chip - $data['buyIn'];
    }
}