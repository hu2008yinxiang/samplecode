<?php
namespace Cmds;

class AcceptGiftCmd extends Cmd
{

    const CMD_NAME = 'AcceptGift';

    public static function defaultData()
    {
        return array(
            'gift_ids' => array()
        );
    }

    protected function do_execute()
    {
        $me = $this->getMe();
        $data = array();
        foreach ($this->data['gift_ids'] as $gid) {
            $gift = \Mails::findFirst($gid);
            $amount = 0;
            if (! $gift || $gift->type != \Mails::TYPE_GIFT || $gift->status != \Mails::STATUS_UNREAD || $gift->dst_id != $this->account_id) {
                $data[] = array(
                    'id' => $gid,
                    'amount' => 0
                );
                continue;
            }
            // 获取筹码数
            $amount = $this->getGiftMoney($me, $gift->src_id);
            // 触发事件
            $this->getEventsManager()->fire('user:acceptGift', $me, array(
                'gift' => $gift,
                'amount' => $amount
            ));
            $gift->status = \Mails::STATUS_READ;
            $gift->save();
            $data[] = array(
                'id' => $gid,
                'amount' => $amount
            );
            $giftScale = $me->getDailyGiftScale();
            $amount *= $giftScale;
            $me->chip += $amount;
            $me->save();
            // 记录日志
            \Records::record($this->account_id, $gift->src_id, \Records::TYPE_CHIP, $amount, \Records::CODE_GIFT, \Records::REASON_DAILYGIFT);
            //
        }
        $this->ret['result']['data'] = $data;
        $this->ret['result']['chip'] = $me->chip;
        $this->ret['result']['diamond'] = $me->diamond;
        $this->ret['result']['scale'] = $giftScale;
    }

    public function getGiftMoney($me, $friendid)
    {
        switch ($friendid) {
            case \Mails::SENDER_FBLOGIN:
                return \DailyGiftManager::FBLOGIN_REWARD;
        }
        $today = date('Ymd');
        $extra = \Extras::load($me->account_id, \Extras::GIFT_SUM, array(
            'date' => $today,
            'sum' => 0
        ));
        if ($extra->value['date'] != $today) {
            $extra->value['date'] = $today;
            $extra->value['sum'] = 0;
        }
        if ($extra->value['sum'] > 5000) {
            return 100;
        }
        $gifts = array(
            100,
            200,
            500,
            700,
            1000,
            2000,
            5000,
            7000,
            10000,
            20000
        );
        $weight = array(
            60,
            90,
            30,
            7,
            6,
            5,
            4,
            3,
            2,
            1
        );
        srand(microtime() + $friendid);
        $min = 1;
        $max = array_sum($weight);
        $d = rand($min, $max);
        $index = - 1;
        do {
            ++ $index;
            $d -= $weight[$index];
        } while ($d > 0);
        $index = min(array(
            $index,
            count($gifts) - 1
        ));
        $value = $gifts[$index];
        
        $extra->value['sum'] += $value;
        $extra->save();
        return $value;
    }
}