<?php

class RanksManager extends \Phalcon\Mvc\User\Component
{

    const RANK_COUNT = 50;

    protected $globalKey = 'ranks:global';

    protected $poolKey = '';

    protected $infoKey = '';

    protected $rankLimit = 10010;

    public function setDI($di)
    {
        parent::setDI($di);
        $config = $di->get('config')->ranksManager;
        $this->globalKey = $config->globalKey;
        $this->rankLimit = $config->rankLimit;
        $this->poolKey = $this->globalKey . '_pool';
        $this->infoKey = $this->globalKey . '_info';
    }

    public function seed()
    {
        mt_srand();
        $map = \Ranks::getColumnMap();
        $this->rankLimit = \Ranks::maximum(array(
            'column' => $map['win']
        ));
        $this->rankLimit = ($this->rankLimit < 9000 ? 10010 : $this->rankLimit);
        $p = round($this->rankLimit / 100);
        $count = \Ranks::GLOBAL_RANK;
        $data = array();
        $ids = array();
        $last = ($p * mt_rand(5, 31)) + $this->rankLimit;
        // 以当前玩家最大值为基数
        $last -= $last % 10; // 10 的倍数
        $this->seedForExpert($data);
        $this->seedForAdvanced($data);
        $this->seedForPrimary($data);
        foreach ($data as $k => &$v) {
            $v += $last; // 叠加基数值
        }
        // 清除旧数据
        // 填充新数据
        $pipe = $this->redis->pipeline();
        $pipe->del($this->globalKey);
        $pipe->del($this->poolKey); /* 清除旧数据 */
        $pipe->hmset($this->globalKey, $data);
        $pipe->hmset($this->poolKey, $data); /* 填入新数据 */
        $pipe->hmset($this->infoKey, array(
            'min' => min($data)
        )); /* 更新最小值 */
        $pipe->execute(); /* 提交 */
        // $this->redis->hset($this->infoKey, 'min', min($data));
    }

    protected function seedForExpert(array &$data)
    {
        $index = 0;
        $id_base = 14512000; // id 起始量
        $delta = 0; // id 增量
        while ($index ++ < 5) {
            $delta = mt_rand(1, 200);
            $id_base = $id = $id_base + $delta;
            $win_count = mt_rand(1600000, 20000000) * 10;
            $data[$id . ''] = $win_count;
        }
    }

    protected function seedForAdvanced(array &$data)
    {
        $index = 0;
        $id_base = 14513000;
        $delta = 0;
        while ($index ++ < 10) {
            $delta = mt_rand(1, 100);
            $id_base = $id = $id_base + $delta;
            $win_count = mt_rand(18000, 2400000) * 10;
            $data[$id . ''] = $win_count;
        }
    }

    protected function seedForPrimary(array &$data)
    {
        $index = 0;
        $id_base = 14614000;
        $delta = 0;
        while ($index ++ < 35) {
            $delta = mt_rand(1, 28);
            $id_base = $id = $id_base + $delta;
            $win_count = mt_rand(1000, 200000) * 10;
            $data[$id . ''] = $win_count;
        }
    }

    public function getRankData()
    {
        while (true) {
            $members = $this->redis->hgetall($this->globalKey);
            if (count($members) < Ranks::GLOBAL_RANK) {
                $this->seed();
                continue;
            }
            break;
        }
        $data = array();
        
        foreach ($members as $k => $v) {
            $data[] = array(
                'account_id' => $k,
                'win' => $v
            );
        }
        return $data;
    }

    public function updateNow($interval = 0)
    {
        // 根据 interval 计算 更新幅值
        // 确定是否需要加入其他人
        // 确保当前排行榜数据初始化完成
        $data = $this->getRankData();
        unset($data);
        // 载入pool
        $data = $this->redis->hgetall($this->poolKey);
        mt_srand();
        // 获取当前排行榜
        $ranks = $this->redis->hgetall($this->globalKey);
        // 更新pool
        foreach ($data as $key => &$value) {
            $key = $key . '';
            $delta = 0;
            $begin = 0;
            $end = 0;
            $ratio = 1;
            switch ($key[4]) {
                case '2':
                    $begin = 2000000 * 0.05;
                    $end = 20000000;
                    $ratio = mt_rand(1, 5) / 6.0;
                    break;
                case '3':
                    $begin = 20000 * 0.08;
                    $end = 2000000 * 6.8;
                    $ratio = mt_rand(1, 5) / 4.65;
                    break;
                case '4':
                    $begin = 9000 * 0.08;
                    $end = 1000000 * 7.8;
                    $ratio = mt_rand(1, 5) / 3.3;
                    break;
                default:
                    break;
            }
            $delta = intval(mt_rand($begin, $end) * $ratio) * 10;
            if (mt_rand(1, 6) > 2) {
                // 写入pool
                $value += $delta;
            }
            // 写入rank
            $ranks[$key] = $value;
            unset($value);
        }
        
        // 降序排序
        arsort($ranks);
        
        // 去除50名以后的
        while (count($ranks) > static::RANK_COUNT) {
            array_pop($ranks);
        }
        //
        $this->redis->pipeline()
            ->hmset($this->poolKey, $data) /* 填入新数据 */
                ->del($this->globalKey) /* 清除旧数据 */
                ->hmset($this->globalKey, $ranks)/* 更新排行榜 */
                ->hmset($this->infoKey, array(
            'min' => min($ranks)
        ))/* 更新最小值 */
                ->execute(); /* 提交 */
    }

    public function playerUpdate($account_id, $win_count)
    {
        $min = $this->redis->hget($this->infoKey, 'min');
        if ($win_count < $min) {
            return;
        }
        $data = $this->redis->hgetall($this->globalKey);
        $data[$account_id . ''] = $win_count;
        arsort($data);
        while (count($data) > static::RANK_COUNT) {
            array_pop($data);
        }
        $pipe = $this->redis->pipeline();
        $pipe->del($this->globalKey); /* 删除旧值 */
        $pipe->hmset($this->globalKey, $data); /* 填入新值 */
        $pipe->hmset($this->infoKey, array(
            'min' => min($data)
        ));
        $pipe->execute();
    }

    /**
     * 结算
     */
    public function playerSettle($userRanks = true)
    {
        if ($userRanks) {
            // 正常结算
            Ranks::settleRanks(); // 用户结算
            $data = $this->getRankData();
            unset($data); // 确保排行榜数据
        } else {
            // 不存在上周排行 建立上周世界排行 然后进行结算 并且不丢失用户数据
            // 重建排行榜
            $this->seed();
        }
        
        $ranks = $this->redis->hgetall($this->globalKey);
        $min = $this->redis->hget($this->infoKey, 'min');
        $this->redis->pipeline()
            ->hset($this->infoKey, 'last', json_encode(array(
            'ranks' => $ranks,
            'min' => $min
        )))/* 转存 */
            ->del($this->globalKey) /* 删除 */
            ->execute();
    }

    public function getLastWeekGlobal($account_id)
    {
        // $rank = Ranks::load($account_id);
        // if ($rank->reward_tag[0] != '1') { // 已领取过
        // return false;
        // }
        while (true) {
            $last = json_decode($this->redis->hget($this->infoKey, 'last'), true);
            if (! isset($last['ranks']) || count($last['ranks']) != Ranks::GLOBAL_RANK) {
                $this->playerSettle(false);
                continue;
            }
            $ranks = $last['ranks'];
            break;
        }
        $rankData = array();
        foreach ($ranks as $id => $win) {
            $ua = array();
            if (FakeUserInfoContainer::isFakeUser($id)) {
                $ua = $this->fakeUserInfoContainer->getUserInfo($id);
            } else {
                $ua = \UserAccounts::findFirstByAccountId($id)->getInfoArray();
            }
            $rankData[] = array(
                'account_id' => $id,
                'nickname' => $ua['nickname'],
                'photo' => $ua['photo'],
                'last_win' => $win
            );
        }
        return $rankData;
    }

    public function getLastWeekFriend($account_id)
    {
        // $rank = Ranks::load($account_id);
        // if ($rank->reward_tag[1] != '1') {
        // return false;
        // }
        return Ranks::getFriendsLastRankList($account_id);
    }

    public function rewardGlobalRank($account_id)
    {
        $rank = Ranks::load($account_id);
        if ($rank->reward_tag[0] != '1') { // 已领取过
            return 0;
        }
        $reward_tag = $rank->reward_tag;
        $reward_tag[0] = '0';
        $rank->reward_tag = $reward_tag;
        $rank->save();
        while (true) {
            $last = json_decode($this->redis->hget($this->infoKey, 'last'), true);
            if (! isset($last['ranks']) || count($last['ranks']) != Ranks::GLOBAL_RANK) {
                $this->playerSettle(false);
                continue;
            }
            $ranks = $last['ranks'];
            break;
        }
        if (! array_key_exists($account_id, $ranks)) {
            return 0;
        }
        // 计算排名
        $index = 0;
        foreach ($ranks as $k => &$v) {
            ++ $index;
            $v = $index;
            unset($v);
        }
        $index = $ranks[$account_id];
        if ($index < 6) {
            switch ($index) {
                default:
                case 0:
                    return 0;
                case 1:
                    return 50000000;
                case 2:
                    return 20000000;
                case 3:
                    return 10000000;
                case 4:
                    return 8000000;
                case 5:
                    return 7000000;
            }
        }
        return 1000000 + (Ranks::GLOBAL_RANK - $index) * 100000;
    }

    public function rewardFriendRank($account_id, $serial)
    {
        $rank = Ranks::load($account_id);
        if ($rank->reward_tag[1] != '1') {
            return 0;
        }
        $reward_tag = $rank->reward_tag;
        $reward_tag[1] = '0';
        $rank->reward_tag = $reward_tag;
        $rank->save();
        if ($rank->getLastWin() == 0) {
            return 0;
        }
        $ranks = Ranks::getFriendsLastRankList($account_id);
        $fc = 0;
        foreach ($ranks as $r) {
            if ($r['last_win'] > 0) {
                $fc ++;
            }
        }
        $index = $serial - $account_id - $rank->getLastWin();
        if ($index < 1 || $index > $fc) {
            return 0;
        }
        $k = 1000 * ($fc - $index + 1);
        if ($k > 50000) {
            $k = 50000;
        }
        return $k;
    }
}
