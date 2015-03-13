<?php
namespace Cmds;

class EnterTableCmd extends Cmd
{

    const CMD_NAME = 'EnterTable';

    public static function defaultData()
    {
        return array(
            'buyIn' => 0,
            'bigBlind' => 0,
            'capacity' => 9,
            'speed' => 'normal'
        );
    }

    protected function do_execute()
    {
        $orgBuyIn = $this->data['buyIn'];
        $data = $this->data;
        $tableList = static::getTableList();
        $speeds = array(
            'n',
            's'
        );
        // TODO 解锁问题
        $me = $this->getMe();
        if ($data['buyIn'] == 0 && $data['bigBlind'] == 0) {
            $autoData = $this->autoSelectTable($me);
            // error_log(var_export($autoData, true));
            if ($autoData['bigBlind'] == 0 || $autoData['buyIn'] == 0) {
                $this->ret['result']['errno'] = \Errors::CHIP_NOT_ENOUGH;
                return;
            }
            $data['buyIn'] = $autoData['buyIn'];
            $data['bigBlind'] = $autoData['bigBlind'];
            $data['capacity'] = $autoData['capacity'];
            $data['speed'] = $autoData['speed'];
        }
        
        if (empty($data['bigBlind'])) {
            $this->ret['result']['errno'] = \Errors::EMPTY_DATA;
            return;
        }
        
        if (empty($data['buyIn'])) {
            list ($min, $max) = $tableList[$data['bigBlind']];
            if ($me->chip > $min) {
                $data['buyIn'] = min($max, intval($me->chip / $data['bigBlind']) * $data['bigBlind']);
            } else {
                $autoData = $this->autoSelectTable($me);
                // error_log(var_export($autoData, true));
                if ($autoData['bigBlind'] == 0 || $autoData['buyIn'] == 0) {
                    $this->ret['result']['errno'] = \Errors::CHIP_NOT_ENOUGH;
                    return;
                }
                $data['buyIn'] = $autoData['buyIn'];
                $data['bigBlind'] = $autoData['bigBlind'];
                $data['capacity'] = $autoData['capacity'];
            }
        }
        
        if (! in_array($data['speed'][0], $speeds) || ($data['capacity'] != 5 && $data['capacity'] != 9) || ! array_key_exists($data['bigBlind'], $tableList)) {
            $this->ret['result']['errno'] = \Errors::EMPTY_DATA;
            return;
        }
        
        list ($min, $max) = $tableList[$data['bigBlind']];
        $me = $this->getMe();
        if ($min > $data['buyIn'] || $max < $data['buyIn'] || ($data['buyIn'] % $data['bigBlind'] != 0)) {
            $this->ret['result']['errno'] = \Errors::CHIP_NOT_ENOUGH;
            return;
        }
        if ($me->chip < $data['buyIn']) {
            $this->ret['result']['errno'] = \Errors::CHIP_NOT_ENOUGH;
            return;
        }
        // $me->chip -= $data['buyIn'];
        $me->save();
        // $me->account_id = $me->account_id;
        $data['WLR'] = static::getWLR($me->account_id);
        $session = \SessionManager::genTableSession($me->account_id);
        unset($data['cmd']);
        \SessionManager::fillData($data, $me);
        \SessionManager::enterTable($session, $data);
        $this->ret['result']['session'] = $session;
        $this->ret['result']['server'] = \SessionManager::getTableServer($me->account_id);
        $this->ret['result']['chip'] = $me->chip - $data['buyIn'];
        $this->ret['result']['diamond'] = $me->diamond;
        $this->ret['result']['table'] = array(
            'buyIn' => $data['buyIn'],
            'max' => $max,
            'min' => $min,
            'bigBlind' => $data['bigBlind']
        );
        
        $value = array(
            $data['bigBlind'],
            $data['speed'],
            $data['capacity']
        );
        $extra = \Extras::load($this->account_id, \Extras::LAST_BIGBLIND, $value);
        $extra->value = $value;
        $extra->save();
        if ($data['buyIn'] == $orgBuyIn) {
            $buyInLast = \Extras::load($me->account_id, \Extras::BUYIN_COUNTER, 0);
            $buyInLast->value = 7;
            $buyInLast->save();
        }
    }

    public static function getTableList()
    {
        return \SessionManager::getTableList(false);
    }

    public static function getWLR($account_id)
    {
        return \SessionManager::getWLR($account_id);
    }

    public static function genTableData()
    {
        $data_org = \SessionManager::getTableList(false);
        $data = array(
            'i' => array(),
            'k' => array()
        );
        $index = 0;
        foreach ($data_org as $b => $i) {
            $data['k'][$b] = $data['i'][$index] = array(
                'bigBlind' => $b,
                'min' => $i[0],
                'max' => $i[1],
                'level' => ($index > 11 ? TABLE_LIMITATION_EXPERT : ($index > 5 ? TABLE_LIMITATION_ADVANCED : 0))
            );
            ++ $index;
        }
        return $data;
    }

    public function autoSelectTable($ua)
    {
        $me = $ua;
        $table = static::genTableData();
        $bigBlind = 0;
        $buyIn = 0;
        $capacity = 9;
        $speed = 'normal';
        $buyInCounter = \Extras::load($ua->account_id, \Extras::BUYIN_COUNTER, 0);
        $extra = \Extras::findFirst(array(
            'account_id = :account_id: AND name = :name:',
            'bind' => array(
                'account_id' => $me->account_id,
                'name' => \Extras::LAST_BIGBLIND
            )
        ));
        if (! $extra) {
            $extra = new \Extras();
            $extra->account_id = $this->account_id;
            $extra->value = array(
                50,
                'normal',
                9
            );
            $extra->name = \Extras::LAST_BIGBLIND;
            $extra->save();
            $buyInCounter->account_id = $me->account_id;
            $buyInCounter->value = 7;
        }
        // 默认大盲注
        if (! $extra || ! array_key_exists($extra->value[0], $table['k'])) {
            $bigBlind = $table['i'][0]['bigBlind'];
        } else {
            $bigBlind = $extra->value[0];
            $capacity = $extra->value[2] == 5 ? 5 : 9;
            // $speed = $extra->value[1] == 'slow' ? 'slow' : 'normal';
        }
        // 获取最大 最小买入
        $max = $table['k'][$bigBlind]['max'];
        // 最小买入
        $min = $table['k'][$bigBlind]['min'];
        $buyIn = intval(floor($me->chip / $bigBlind) * $bigBlind);
        // 大于大盲注 最大买入
        if ($me->chip >= $max)
            $buyIn = $max;
            // 是否成功买入
        $match = false && ($me->chip >= $buyIn && $me->chip >= $min);
        // error_log(sprintf('buyIn:%s bigBlind:%s min:%s max:%s', $buyIn, $bigBlind, $min, $max));
        // $buyInCounter = \Extras::load($ua->account_id, \Extras::BUYIN_COUNTER, 0);
        if ($match) {
            $buyInCounter->value -= 1;
        } else {
            $buyInCounter->value = 0;
            $buyIn = 0;
        }
        if ($buyInCounter->value >= 0) {
            $buyInCounter->save();
        }
        // 玩家筹码太多 并且 上次买入没有消耗完
        if (true || ($buyInCounter->value < 1)) {
            // 调整买入
            $value = intval(round($me->chip / 3));
            $ac = $this->achievementManager->getAchievement($ua->account_id, 'AssetsPeak');
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
                if ($me->chip > $t['max']) {
                    $buyIn = $t['max'];
                }
                $match = true;
            }
        }
        // error_log(sprintf('buyIn:%s bigBlind:%s min:%s max:%s', $buyIn, $bigBlind, $min, $max));
        // 玩家筹码太少
        if (! $match) {
            // 向下 匹配
            foreach ($table['i'] as $t) {
                $min = $t['min'];
                $max = $t['max'];
                $c = intval(floor($me->chip / $t['bigBlind'])) * $t['bigBlind'];
                if ($c < $t['min']) {
                    continue;
                }
                $match = true;
                $bigBlind = $t['bigBlind'];
                $buyIn = $c;
            }
        }
        // error_log(sprintf('buyIn:%s bigBlind:%s min:%s max:%s', $buyIn, $bigBlind, $min, $max));
        if (! $match) {
            return array(
                'bigBlind' => 0,
                'buyIn' => 0,
                'capacity' => $capacity,
                'speed' => $speed
            );
        }
        // error_log(sprintf('buyIn:%s bigBlind:%s min:%s max:%s', $buyIn, $bigBlind, $min, $max));
        return array(
            'bigBlind' => $bigBlind,
            'buyIn' => $buyIn,
            'capacity' => $capacity,
            'speed' => $speed
        );
    }
}