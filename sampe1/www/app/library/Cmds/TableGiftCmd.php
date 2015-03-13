<?php
namespace Cmds;

class TableGiftCmd extends Cmd
{

    const CMD_NAME = 'TableGift';

    public static function defaultData()
    {
        return array(
            'code' => '',
            'session' => ''
        );
    }

    protected function do_execute()
    {
        $code = $this->data['code'];
        $session = $this->data['session'];
        $redis = $this->di->get('redis');
        $sk = \SessionManager::getTableSessionKey($session);
        if (strlen($code) != 4 || empty($session) || $redis->hget($sk, 'accountId') != $this->account_id) {
            $this->ret['result']['errno'] = \Errors::OP_DENIED;
            return;
        }
        $keys = array(
            'orgBuyIn',
            'orgFee'
        );
        $orgInfo = array_combine($keys, $redis->hmget($sk, $keys));
        $orgInfo = array_merge(array(
            'orgBuyIn' => 0,
            'orgFee' => 0
        ), $orgInfo);
        $me = $this->getMe();
        if ($code[1] == 9) {
            // 付小费
            $bigBlind = 1000;
            $wlr = $redis->hget($sk, 'WLR');
            if ($wlr == 'w' || $wlr == 'l') {
                $bigBlind = $redis->hget($sk, 'bigBlind');
            }
            if (is_null($bigBlind) || $bigBlind < 1) {
                $this->ret['result']['errno'] = \Errors::OP_DENIED;
                return;
            }
            if (($bigBlind + $orgInfo['orgBuyIn'] + $orgInfo['orgFee']) > $me->chip) {
                $this->ret['result']['errno'] = \Errors::CHIP_NOT_ENOUGH;
                return;
            }
            $me->chip -= $bigBlind;
            $me->save();
            $this->ret['result']['chip'] = $me->chip - ($orgInfo['orgBuyIn'] + $orgInfo['orgFee']);
            $this->ret['result']['diamond'] = $me->diamond;
            $this->_eventsManager->fire('table:tableTipsSent', $me, $bigBlind);
            $redis->hset($sk, 'gift', $code);
            // 处理结束
            return;
        }
        $towho = $code[0]; // 送给谁
        $index = '' . $code[1] . $code[2]; // 礼物index
        $count = $code[3]; // 礼物数量
        if ($towho < 0 || $towho > 9 || $count < 1) { // 给谁不对
            $this->ret['result']['errno'] = \Errors::OP_DENIED;
            return;
        }
        $data = $this->di->get('giftsManager')->getData();
        $data = $data['data'];
        if (! isset($data[$index])) { // 找不到礼物
            $this->ret['result']['errno'] = \Errors::OP_DENIED;
            return;
        }
        // 礼物数据
        $gift = $data[$index];
        // 折扣
        if ($gift['discount'] == 1) {
            $gift['discount'] = 10;
        }
        $cost = round($gift['price'] * $count * $gift['discount'] / 10);
        switch (intval($index[0]) % 3) {
            case 0:
            case 1:
                if (($cost + $orgInfo['orgBuyIn'] + $orgInfo['orgFee']) > $me->chip) {
                    $this->ret['result']['errno'] = \Errors::CHIP_NOT_ENOUGH;
                    return;
                }
                $me->chip -= $cost;
                break;
            case 2:
                if ($cost > $me->diamond) {
                    $this->ret['result']['errno'] = \Errors::DIAMOND_NOT_ENOUGH;
                    return;
                }
                $me->diamond -= $cost;
                break;
        }
        $me->save();
        $this->_eventsManager->fire('table:tableGiftSent', $me, array(
            'gift' => $gift,
            'code' => $code,
            'cost' => $cost
        ));
        $redis->hset($sk, 'gift', $code);
        $this->ret['result']['chip'] = $me->chip - ($orgInfo['orgBuyIn'] + $orgInfo['orgFee']);
        $this->ret['result']['diamond'] = $me->diamond;
    }
}