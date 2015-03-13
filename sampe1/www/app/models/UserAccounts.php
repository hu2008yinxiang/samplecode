<?php

/**
 *
 * @author Ian
 *         An user account contains the main properties of a player.
 *
 */
class UserAccounts extends Phalcon\Mvc\Model
{

    const IN_APP_PHOTO_COUNT = IN_APP_PHOTO_COUNT;

    const VIP_BROZON = 0;

    const VIP_SILVER = 1;

    const VIP_GOLD = 2;

    const VIP_PLATINUM = 3;

    const VIP_DIAMOND = 4;

    const VIP_ROYAL = 5;

    protected $_vip_level = - 1;

    public function initialize()
    {
        $this->setSource('accounts');
        
        $this->keepSnapshots(true);
        $this->useDynamicUpdate(true);
        $this->hasMany('account_id', 'Friends', 'src_id');
        $this->hasMany('account_id', 'Mails', 'dst_id');
        $this->hasMany('account_id', 'Achievements', 'account_id');
        $this->hasMany('account_id', 'Extras', 'account_id');
        $this->hasOne('account_id', 'Ranks', 'account_id');
        $this->hasOne('ref_id', 'UserAccounts', 'account_id', array(
            'alias' => 'Referer'
        ));
    }

    public function beforeValidationOnCreate()
    {
        $data = $this->toArray();
        $data = array_filter($data, function ($value)
        {
            return isset($value);
        });
        $data = array_merge(self::getDefaultData(), $data);
        $this->assign($data);
    }

    public function beforeSave()
    {
        if (isset($this->best_hand) && is_array($this->best_hand)) {
            $this->best_hand = json_encode($this->best_hand);
        }
        
        if (isset($this->bind_detail) && is_array($this->bind_detail)) {
            $this->bind_detail = json_encode($this->bind_detail);
        }
    }

    public function afterFetch()
    {
        if (isset($this->best_hand) && is_string($this->best_hand)) {
            $this->best_hand = json_decode($this->best_hand, true);
        }
        if (isset($this->bind_detail) && is_string($this->bind_detail)) {
            $this->bind_detail = json_decode($this->bind_detail, true);
        }
        if ($this->hasSnapshotData() && $this->hasChanged('chip')) {
            // $this->getEventsManager()->fire('user:chipChanged', $this);
            // $this->eventsManager->fire('user:chipChanged', $this);
            $this->getDI()
                ->get('eventsManager')
                ->fire('user:chipChanged', $this);
        }
    }

    public function afterSave()
    {
        $this->afterFetch();
    }

    /**
     * 生成一个可用ID
     *
     * @return number
     */
    static public function nextId()
    {
        // $ua = new UserAccounts ();
        $redis = Phalcon\DI::getDefault()->get('redis');
        $prefix = strtoupper('user:nextid');
        $key_seed1 = $prefix . '_SEED_1';
        $key_seed2 = $prefix . '_SEED_2';
        $key_seed3 = $prefix . '_SEED_3';
        $key_last = $prefix . '_LAST_ID';
        
        // 设置初始值
        $redis->setnx($key_seed1, 100);
        $redis->setnx($key_seed2, 100);
        do {
            
            $last_id = intval($redis->get($key_last));
            if ($last_id && self::count(array(
                'account_id = :account_id:',
                'bind' => array(
                    'account_id' => $last_id
                )
            )) === 0) {
                return $last_id;
            }
            
            // 增加变量3
            $part3 = $redis->incrby($key_seed3, 3);
            $part2 = 0;
            $part1 = 0;
            if ($part3 % 1000 >= 997) {
                // 增加变量2
                $part2 = $redis->incr($key_seed2);
                if ($part2 % 1000 >= 999) {
                    // 增加变量1
                    $part1 = $redis->incr($key_seed1);
                } else {
                    $part1 = $redis->get($key_seed1);
                }
            } else {
                $part1 = $redis->get($key_seed1);
                $part2 = $redis->get($key_seed2);
            }
            
            $part2 = $part2 % 1000;
            $part3 = $part3 % 1000;
            $part3 = $part3 % 250 + ($part3 % 4) * 250;
            $part2 = $part2 % 333 + ($part2 % 3) * 333;
            $id_new = $part1 * 1000 * 1000 + $part2 * 1000 + $part3;
            
            // 判断是否被使用
            $used = self::count(array(
                'account_id = :account_id:',
                'bind' => array(
                    'account_id' => $id_new
                )
            ));
            if ($used) {
                error_log(__CLASS__ . '::' . __METHOD__ . ': ' . "$id_new is used.");
                continue;
            }
            unset($ua);
            
            $redis->set($key_last, $id_new);
            return $id_new;
        } while (true);
    }

    /**
     * 生成随机密码
     *
     * @param int $len            
     * @return string
     */
    static public function getRandomPassword($len)
    {
        $ret = "";
        while ($len -- > 0) {
            $random = rand(0, 2);
            if ($random === 0) {
                $ret .= chr(rand(ord('a'), ord('z')));
            } else 
                if ($random === 1) {
                    $ret .= chr(rand(ord('A'), ord('Z')));
                } else {
                    $ret .= chr(rand(ord('0'), ord('9')));
                }
        }
        return $ret;
    }

    /**
     * 创建账号时提供的默认信息
     *
     * @return array
     */
    static public function getDefaultData()
    {
        $di = \Phalcon\DI::getDefault();
        $config = $di->get('config');
        $data = $config->user_account_default->toArray();
        return array_merge(array(
            'account_id' => self::nextId(),
            'account_token' => self::getRandomPassword(128),
            'bind_id' => '',
            'bind_email' => '',
            'bind_token' => '',
            'bind_detail' => '',
            'gender' => 'secret',
            'nickname' => null, // 需客户端提供
            'photo' => rand(1, self::IN_APP_PHOTO_COUNT),
            'type' => 'local',
            'chip' => 20000,
            'diamond' => 20,
            'exp' => 0,
            'level' => 1,
            'best_hand' => array(
                0,
                0,
                0,
                0,
                0,
                0
            ),
            'biggest_win' => 0,
            'biggest_bet' => 0,
            'vip_score' => 0,
            
            // 'vip_level' => 0,
            'login_last' => 0,
            'login_combo' => 0,
            'last_pay' => 0,
            'round' => 0,
            'win_round' => 0,
            'last_lottery' => '0000-00-00',
            'sot_stage' => 1,
            'reg_time' => date('Y-m-d H:i:s'),
            'chands' => 0,
            'threshold' => - 1,
            'thc' => 100,
            'app_version' => 0,
            'device_id' => 'N/A'
        ), $data);
    }

    /**
     * 使用昵称创建新用户
     *
     * @param string $nickname            
     * @return \UserAccounts 失败可能为id被占用。
     */
    static public function registerByLocal($nickname)
    {
        $ua = new UserAccounts();
        $ret = $ua->create(array(
            'nickname' => $nickname
        ));
        if ($ret) {
            return $ua;
        }
        return $ret;
    }

    static public function registByBind($type, $token)
    {
        $ua = new UserAccounts();
        $di = $ua->getDI();
        $bindService = $di->get($type . 'Adapter');
        $bindAccount = $bindService->bind($token);
        if ($bindAccount->bindStatus() == \Bind\BindAccount::BIND_OK) {
            $bind_id = $bindAccount->getAccount();
            $pre_bind_ua = UserAccounts::findFirst(array(
                'bind_id = :bind_id:',
                'bind' => array(
                    'bind_id' => $bind_id
                )
            ));
            // 之前已绑定过
            if ($pre_bind_ua) {
                // 更新信息
                $pre_bind_ua->assign(array(
                    'nickname' => $bindAccount->getNickname(),
                    'gender' => $bindAccount->getGender(),
                    'bind_email' => $bindAccount->getEmail(),
                    'bind_detail' => $bindAccount->getDetail(),
                    'bind_token' => $bindAccount->getToken(),
                    'photo' => - 1
                ));
                // 保存
                $ret = $pre_bind_ua->save();
                if ($ret) {
                    // 返回
                    $di->get('photoManager')->download($bindAccount->getPhoto(), $pre_bind_ua->account_id);
                    //
                    $pre_bind_ua->syncBindFriends($bindAccount->getFriends(), $type);
                    return $pre_bind_ua;
                }
                return false;
            }
            $ret = $ua->create(array(
                'nickname' => $bindAccount->getNickname(),
                'gender' => $bindAccount->getGender(),
                'bind_id' => $bindAccount->getAccount(),
                'bind_email' => $bindAccount->getEmail(),
                'bind_detail' => $bindAccount->getDetail(),
                'bind_token' => $bindAccount->getToken(),
                'photo' => - 1,
                'type' => $type
            ));
            if ($ret === false) {
                return false;
            }
            // 此处加入Facebook 登录奖励
            $di->get('dailyGiftManager')->sendFBLoginReward($ua->account_id);
            $url = $bindAccount->getPhoto();
            // echo $url;
            $di->get('photoManager')->download($bindAccount->getPhoto(), $ua->account_id);
            // 获取好友列表
            
            $ua->syncBindFriends($bindAccount->getFriends(), $type);
            
            return $ua;
        }
        return false;
    }

    /**
     *
     * @param array $friends            
     * @param string $type            
     */
    public function syncBindFriends($friends, $type)
    {
        // if (empty($friends)) {
        // return;
        // }
        // 将以前的好友标记为删除
        foreach (Friends::find(array(
            'src_id = :src_id: and src = :src:',
            'bind' => array(
                'src_id' => $this->account_id,
                'src' => $type
            )
        )) as $f) {
            $f->status = \Friends::STATUS_DELETED;
            $f->save();
        }
        //
        $isUserAccount = false;
        foreach ($friends as $f) {
            if ($isUserAccount || ! is_string($f)) {
                $isUserAccount = true;
                $f = $f->account_id;
            }
            \Friends::add($this->account_id, $f, $type);
        }
    }

    public static function login($account_id, $token)
    {
        $user = static::findFirst(array(
            'account_id = :account_id: and account_token = :token:',
            'bind' => array(
                'account_id' => $account_id,
                'token' => $token
            )
        ));
        if ($user) {
            $di = $user->getDI();
            if ($user->type != 'local') {
                $adapter = $di->get($user->type . 'Adapter');
                $adapter->bind($user->bind_token);
                return true;
            }
        } else {
            return false;
        }
    }

    public function getInfoArray()
    {
        return $this->toArray(array(
            'account_id',
            'bind_email',
            'nickname',
            'gender',
            'photo',
            'type',
            'chip',
            'diamond',
            'exp',
            'level',
            'best_hand',
            'biggest_win',
            'vip_score',
            'biggest_bet',
            'login_last',
            'login_combo',
            'last_pay',
            'round',
            'win_round',
            'sot_stage'
        ));
    }

    public function bind($type, $token)
    {
        if ($this->type != 'local' && $this->type != $type) {
            return false;
        }
        $di = $this->getDI();
        $bindService = $di->get($type . 'Adapter');
        $bindAccount = $bindService->bind($token);
        if ($bindAccount->bindStatus() == \Bind\BindAccount::BIND_OK) {
            $bind_id = $bindAccount->getAccount();
            $pre_bind_ua = UserAccounts::findFirst(array(
                'bind_id = :bind_id:',
                'bind' => array(
                    'bind_id' => $bind_id
                )
            ));
            // 之前已绑定过
            if ($pre_bind_ua && $pre_bind_ua->account_id != $this->account_id) {
                $pre_bind_ua->syncBindFriends($bindAccount->getFriends(), $type);
                return $pre_bind_ua;
            }
            $this->assign(array(
                'nickname' => $bindAccount->getNickname(),
                'gender' => $bindAccount->getGender(),
                'bind_id' => $bindAccount->getAccount(),
                'bind_email' => $bindAccount->getEmail(),
                'bind_detail' => $bindAccount->getDetail(),
                'bind_token' => $bindAccount->getToken(),
                'photo' => - 1,
                'type' => $type
            ));
            if (! $this->save()) {
                return false;
            }
            // $url = $bindAccount->getPhoto();
            // echo $url;
            // $di->get('photoManager')->download($bindAccount->getPhoto(), $this->account_id);
            // 获取好友列表
            // 此处加入Facebook 登录奖励
            $this->getDI()
                ->get('dailyGiftManager')
                ->sendFBLoginReward($this->account_id);
            $this->syncBindFriends($bindAccount->getFriends(), $type);
            return true;
        }
    }

    /**
     *
     * @param string $id            
     * @return \UserAccounts
     */
    public static function findFirstByAccountId($id)
    {
        static $cache = array();
        if (isset($cache[$id])) {
            return $cache[$id];
        }
        $cache[$id] = parent::findFirstByAccountId($id);
        return $cache[$id];
    }

    /**
     *
     * @return number
     */
    public function getVIPLevel()
    {
        // vip 积分没有改变 并且计算过等级
        if ($this->hasSnapshotData() && ! $this->hasChanged('vip_score') && $this->_vip_level != - 1) {
            return $this->_vip_level;
        }
        
        static $points = array(
            400,
            4000,
            28000,
            200000,
            1600000
        );
        $this->_vip_level = 0;
        $score = $this->vip_score;
        foreach ($points as $point) {
            if ($score > $point) {
                ++ $this->_vip_level;
            }
        }
        return $this->_vip_level = min($this->_vip_level, static::VIP_ROYAL);
    }

    /**
     *
     * @return number
     */
    public function getLoginRewardScale()
    {
        static $scales = array(
            1,
            2,
            3,
            5,
            7,
            10
        );
        return $scales[$this->getVIPLevel()];
    }

    /**
     *
     * @return number
     */
    public function getShopTaskScale()
    {
        static $scales = array(
            0.2,
            0.5,
            1,
            1.5,
            2,
            3
        );
        return $scales[$this->getVIPLevel()];
    }

    /**
     *
     * @return number
     */
    public function getVIPPointScale()
    {
        static $scales = array(
            1,
            2,
            3,
            4,
            5,
            6
        );
        return $scales[$this->getVIPLevel()];
    }

    /**
     *
     * @return number
     */
    public function getDailyFreeSpinScale()
    {
        static $scales = array(
            1,
            2,
            5,
            10,
            20,
            100
        );
        return $scales[$this->getVIPLevel()];
    }

    /**
     *
     * @return number
     */
    public function getDailyGiftScale()
    {
        static $scales = array(
            1,
            2,
            3,
            5,
            7,
            10
        );
        return $scales[$this->getVIPLevel()];
    }

    /**
     *
     * @return number
     */
    public function getRankRewardScale()
    {
        static $scales = array(
            1,
            2,
            3,
            5,
            7,
            10
        );
        return $scales[$this->getVIPLevel()];
    }

    /**
     *
     * @return number
     */
    public function getExpScale()
    {
        static $scales = array(
            1,
            2,
            3,
            4,
            5,
            6
        );
        return $scales[$this->getVIPLevel()];
    }
}