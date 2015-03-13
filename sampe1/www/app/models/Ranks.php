<?php

class Ranks extends \Phalcon\Mvc\Model
{

    const GLOBAL_RANK = 50;

    public function initialize()
    {
        $this->keepSnapshots(true);
        $this->useDynamicUpdate(true);
    }

    public static function getColumnMap()
    {
        static $map = null;
        if ($map == null) {
            $config = include DATA_PATH . '/ext_config.php';
            // 获取当前周位置
            if (! isset($config['rank_index'])) {
                $config['rank_index'] = 0;
                Misc::cacheToFile($config, DATA_PATH . '/ext_config.php');
            }
            $map = array(
                'win' => sprintf('win_%s', $config['rank_index']),
                'last_win' => sprintf('win_%s', (3 + ($config['rank_index'] - 1) % 3) % 3),
                'last_last' => sprintf('win_%s', (3 + ($config['rank_index'] - 2) % 3) % 3)
            );
        }
        return $map;
    }

    /**
     *
     * @param string $account_id            
     * @return \Ranks
     */
    public static function load($account_id)
    {
        $map = static::getColumnMap();
        $rank = static::findFirstByAccountId($account_id);
        if (! $rank) {
            $rank = new static();
            $rank->account_id = $account_id;
            // 设置当前值
            $rank->{$map['win']} = 0;
            $rank->reward_tag = '000000';
            // 设置上上周
            $rank->{$map['last_last']} = 0;
            // 设置上周
            $rank->{$map['last_win']} = 0;
            $rank->save();
        }
        // 清空上上周
        $rank->{$map['last_last']} = 0;
        $rank->save();
        return $rank;
    }

    /**
     * 读取当前的Rank
     *
     * @param string $account_id            
     * @return Ranks
     */
    public static function loadNow($account_id)
    {
        $rank = static::load($account_id);
        return $rank;
    }

    public static function getRankList($account_id = null, $global = false)
    {
        $data = array();
        if ($global) {
            // 全球榜
            $data = static::getGlobalRankList($account_id);
        } else {
            // 好友榜
            $data = static::getFriendsRankList($account_id);
        }
        return $data;
    }

    protected static function getFriendsRankList($account_id)
    {
        $map = static::getColumnMap();
        $ranks = new static();
        $mm = $ranks->getModelsManager();
        $builder = $mm->createBuilder();
        $builder->from('Friends')
            ->join('UserAccounts', 'UserAccounts.account_id = Friends.dst_id')
            ->leftJoin('Ranks', 'Ranks.account_id = UserAccounts.account_id')
            ->where('( Friends.status = :status: OR Friends.src_id = Friends.dst_id ) AND Friends.src_id = :src_id:')
            ->columns(array(
            'UserAccounts.account_id',
            'UserAccounts.nickname',
            'UserAccounts.photo',
            'IF ( Friends.last_gift_day = :today:, TRUE, FALSE ) AS gifted',
            sprintf('Ranks.%s as win', $map['win']) /*读取当前值*/
        ));
        $query = $builder->getQuery();
        $result = $query->execute(array(
            'status' => \Friends::STATUS_ADDED,
            'src_id' => $account_id,
            'today' => date('Y-m-d')
        ));
        $data = $result->toArray();
        foreach ($data as &$ele) {
            $ele['online'] = \SessionManager::isOnline($ele['account_id']);
        }
        unset($ele);
        return $data;
    }

    public static function getFriendsLastRankList($account_id)
    {
        $ranks = new static();
        $map = static::getColumnMap();
        $mm = $ranks->getModelsManager();
        $builder = $mm->createBuilder();
        $builder->from('Friends')
            ->join('UserAccounts', 'UserAccounts.account_id = Friends.dst_id')
            ->leftJoin('Ranks', 'Ranks.account_id = UserAccounts.account_id')
            ->where('( Friends.status = :status: OR Friends.src_id = Friends.dst_id ) AND Friends.src_id = :src_id:')
            ->columns(array(
            'UserAccounts.account_id',
            'UserAccounts.nickname',
            'UserAccounts.photo',
            sprintf('Ranks.%s as last_win', $map['last_win'])
        ));
        $query = $builder->getQuery();
        $result = $query->execute(array(
            'status' => \Friends::STATUS_ADDED,
            'src_id' => $account_id
        ));
        $data = $result->toArray();
        return $data;
    }

    protected static function getGlobalRankList($account_id)
    {
        $map = static::getColumnMap();
        $di = \Phalcon\DI::getDefault();
        $ranksManager = $di->get('ranksManager');
        $fakeUserInfoContainer = $di->get('fakeUserInfoContainer');
        $data = $ranksManager->getRankData();
        $hasMe = false;
        foreach ($data as &$ele) {
            $ua = null;
            if (\FakeUserInfoContainer::isFakeUser($ele['account_id'])) {
                $ua = $fakeUserInfoContainer->getUserInfo($ele['account_id'], 1);
            } else {
                $ua = \UserAccounts::findFirst($ele['account_id'])->getInfoArray();
            }
            $ele['nickname'] = $ua['nickname'];
            $ele['photo'] = $ua['photo'];
            $hasMe = $hasMe || ($ele['account_id'] == $account_id);
        }
        unset($ele);
        if (! $hasMe) {
            $rank = static::loadNow($account_id);
            $me = \UserAccounts::findFirst($account_id);
            $data[] = array(
                'account_id' => $me->account_id,
                'win' => $rank->{$map['win']},
                'photo' => $me->photo,
                'nickname' => ''
            );
        }
        return $data;
    }

    /**
     * 结算上周排行
     */
    public static function settleRanks()
    {
        $columns = static::getColumnMap();
        $column_last_last = $columns['last_last'];
        
        $config = include DATA_PATH . '/ext_config.php';
        // 获取当前周位置
        if (! isset($config['rank_index'])) {
            $config['rank_index'] = 0;
        }
        // 修改rank_index
        $config['rank_index'] = ($config['rank_index'] + 1) % 3;
        // 保存
        Misc::cacheToFile($config, DATA_PATH . '/ext_config.php');
        $ranks = new static();
        $wc = $ranks->getWriteConnection();
        $dt = $wc->getDialectType();
        $table = $ranks->getSource();
        switch ($dt) {
            case 'mysql':
                
                // $wc->execute("UPDATE `$table` SET `last_win` = `win`, `win` = 0, reward_tag = '110000'");
                $wc->execute("UPDATE `$table` SET `reward_tag` = '110000', `$column_last_last` = 0");
                break;
            default:
                
                // $ranks->getModelsManager()->executeQuery("UPDATE Ranks SET Ranks.last_win = Ranks.win, Ranks.win = 0, Ranks.reward_tag = '110000'");
                $ranks->getModelsManager()->executeQuery("UPDATE Ranks SET Ranks.reward_tag = '110000', Ranks.$column_last_last = 0");
        }
    }

    public function __get($name)
    {
        $map = static::getColumnMap();
        switch ($name) {
            case 'win':
            case 'last_win':
                return $this->{$map[$name]};
        }
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        $map = static::getColumnMap();
        switch ($name) {
            case 'win':
            case 'last_win':
                return $this->{$map[$name]} = $value;
        }
        return parent::__set($name, $value);
    }

    public function addWin($win)
    {
        $map = static::getColumnMap();
        $this->{$map['win']} += $win;
    }

    public function getWin()
    {
        $map = static::getColumnMap();
        return $this->{$map['win']};
    }

    public function getLastWin()
    {
        $map = static::getColumnMap();
        return $this->{$map['last_win']};
    }

    public static function clearLastLast()
    {
        // 用于清除上上周的记录
        $map = static::getColumnMap();
        $column = $map['last_last'];
        $r = new static();
        $db = $r->getWriteConnection();
        $dt = $db->getDialectType();
        switch ($dt) {
            case 'mysql':
                $db->execute('UPDATE `' . $r->getSource() . '` SET `' . $column . '` = :value_zero', array(
                    'value_zero' => 0
                ));
                break;
            default:
                $r->getModelsManager()->executeQuery('UPDATE Ranks SET Ranks.' . $column . ' = 0');
        }
    }
}
