<?php
namespace Cmds;

class NewGotoTableCmd extends NewEnterTableCmd
{

    const CMD_NAME = 'NewGotoTable';

    public static function defaultData()
    {
        return array(
            'fid' => ''
        ) + parent::defaultData();
    }

    protected function do_execute()
    {
        $fid = $this->data['fid'];
        if (empty($fid)) {
            $this->ret['result']['errno'] = \Errors::EMPTY_DATA;
            return;
        }
        if (strlen($fid) == 12) {
            $this->_joinNearBy();
            return;
        }
        $is_online = \SessionManager::isOnline($fid);
        if (! $is_online) { // 不在线
            parent::do_execute();
            return;
        }
        // 好友在线
        $ss = \SessionManager::getTableSession($fid); // 得到session
        if (empty($ss)) { // 没有session
            parent::do_execute();
            return;
        }
        // 有session
        $key = \SessionManager::getTableSessionKey($ss); // 得到key
        $info = $this->redis->hgetall($key); // 得到数据
        
        if (empty($info) || ! in_array($info['WLR'], array(
            'w',
            'l'
        ))) { // 数据为空 或者 不允许加入
            parent::do_execute();
            return;
        }
        $tableList = static::getTableList();
        if (empty($info['bigBlind']) || ! array_key_exists($info['bigBlind'], $tableList)) {
            // 没有大盲注 或者 不存在牌桌
            parent::do_execute();
            return;
        }
        // error_log('-----PASSED CHECK------');
        $ret = array();
        $me = $this->getMe();
        $data = \SessionManager::defaultTableData();
        // 填充数据
        $data['buyIn'] = 0; // 买入
        $data['accountId'] = $this->account_id;
        
        $info = array_merge($info, array(
            'speed' => 'n',
            'capacity' => 9
        ));
        $data['bigBlind'] = $info['bigBlind']; // 大盲注必需
        $data['speed'] = in_array($info['speed'], array(
            'n',
            's'
        )) ? $info['speed'] : 'n'; // 猜测速度
        $data['capacity'] = in_array($info['capacity'], array(
            5,
            9
        )) ? $info['capacity'] : 9; // 猜测人数
        $data['WLR'] = $info['WLR']; // 节奏
                                     // 服务器
        $data['WLR'] = \SessionManager::getWLR($this->account_id);
        //
        $ret['server'] = \SessionManager::getTableServer($this->account_id);
        $extras = \Extras::load($fid, \Extras::LAST_MINA, $ret['server']);
        if ($extras) {
            $old = $extras->value;
            if ($this->minaSwitcher->isActive($old['host'], $old['port'])) {
                // 如果仍在活跃
                $ret['server'] = $old;
            }
        }
        unset($extras);
        // $friendId
        $data['friendId'] = $fid;
        
        $session = \SessionManager::genTableSession($this->account_id);
        // 进入牌桌
        \SessionManager::fillData($data, $me);
        \SessionManager::enterTable($session, $data);
        $ret['session'] = $session;
        $ret['chip'] = $me->chip;
        $ret['diamond'] = $me->diamond;
        // $tableList = \SessionManager::getTableList(false);
        list ($min, $max) = $tableList[$data['bigBlind']];
        $ret['table'] = array(
            'buyIn' => $data['buyIn'],
            'max' => $max,
            'min' => $min,
            'bigBlind' => $data['bigBlind']
        );
        foreach ($ret as $k => $v) {
            $this->ret['result'][$k] = $v;
        }
        $extras = \Extras::load($this->account_id, \Extras::LAST_MINA, $ret['server']);
        $extras->value = $ret['server'];
        $extras->save();
        unset($extras);
    }

    protected function _joinNearBy()
    {
        $tableList = static::getTableList();
        $fid = $this->data['fid'];
        $rid = substr($fid, 0, 9);
        $bigBlind = 0;
        $me = $this->getMe();
        $ru = \UserAccounts::findFirstByAccountId($rid);
        while (true) {
            if ($ru) {
                // 获取真实玩家的大盲注
                $bigBlind = $this->detectBigBlind($ru);
            }
            if ($bigBlind) {
                break;
            }
            if ($me) {
                // 获取自己的大盲注
                $bigBlind = $this->detectBigBlind($me);
            }
            if ($bigBlind) {
                break;
            }
            $bigBlind = 50;
            break;
        }
        $data = \SessionManager::defaultTableData();
        $data['bigBlind'] = $bigBlind;
        $data['buyIn'] = 0;
        $data['capacity'] = 9;
        $data['speed'] = 'n';
        $data['WLR'] = \SessionManager::getWLR($this->account_id);
        $data['friendId'] = $fid;
        $session = \SessionManager::genTableSession($this->account_id);
        // 进入牌桌
        \SessionManager::fillData($data, $me);
        \SessionManager::enterTable($session, $data);
        $ret['session'] = $session;
        $ret['chip'] = $me->chip;
        $ret['diamond'] = $me->diamond;
        // $tableList = \SessionManager::getTableList(false);
        list ($min, $max) = $tableList[$data['bigBlind']];
        $ret['table'] = array(
            'buyIn' => $data['buyIn'],
            'max' => $max,
            'min' => $min,
            'bigBlind' => $data['bigBlind']
        );
        foreach ($ret as $k => $v) {
            $this->ret['result'][$k] = $v;
        }
        $extras = \Extras::load($this->account_id, \Extras::LAST_MINA, $ret['server']);
        $extras->value = $ret['server'];
        $extras->save();
        unset($extras);
    }

    protected function detectBigBlind($ua)
    {
        $tableList = static::genTableData();
        $value = intval($ua->chip / 3);
        $ac = $this->achievementManager->getAchievement($ua->account_id, 'AssetsPeak');
        $match = false;
        $bigBlind = 0;
        $buyIn = 0;
        foreach ($table['i'] as $t) {
            if ($ac->current < $t['level']) {
                // 未解锁
                break;
            }
            $sep = intval(($t['max'] - $t['min']) / 3);
            $min = $t['min'] + $sep;
            $max = $t['max'] - $sep;
            $sep = intval(round($value / $t['bigBlind'])) * $t['bigBlind']; // 最接近的大盲注整数倍
            if ($sep < $min) {
                continue; // 跳过
            }
            if ($sep < $buyIn) {
                continue; // 跳过
            }
            if ($sep > $t['max']) {
                $sep = $t['max'];
            }
            $bigBlind = $t['bigBlind'];
            $buyIn = $sep;
            if ($ua->chip > $t['max']) {
                $buyIn = $t['max'];
            }
            $match = true;
        }
        return $match ? $bigBlind : 0;
    }
}