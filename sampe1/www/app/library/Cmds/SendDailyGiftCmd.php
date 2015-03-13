<?php
namespace Cmds;

class SendDailyGiftCmd extends Cmd
{

    const CMD_NAME = 'SendDailyGift';

    public static function defaultData()
    {
        return array(
            'friends' => array(),
            'send_all' => false
        );
    }

    protected function do_execute()
    {
        $send_all = $this->data['send_all'];
        if (! is_array($this->data['friends'])) {
            $this->data['friends'] = array(
                $this->data['friends']
            );
        }
        $date = date('Y-m-d');
        $send_all = \Utils::bool($send_all);
        $me = $this->getMe();
        $count = 0;
        foreach ($me->getFriends(array(
            'status = :status: AND ( last_gift_day < :last_gift_day: OR last_gift_day is NULL )',
            'bind' => array(
                'status' => \Friends::STATUS_ADDED,
                'last_gift_day' => $date
            )
        )) as $friend) {
            // send all or in friends
            if (! ($send_all || in_array($friend->dst_id, $this->data['friends']))) {
                continue;
            }
            $di = \Phalcon\DI::getDefault();
            $dailyGiftManager = $di->get('dailyGiftManager');
            $dailyGiftManager->sendGift($this->account_id, $friend->dst_id);
            ++ $count;
        }
        // $this->ret ['result'] ['data'] = $data;
        $this->ret['result']['friends'] = $this->data['friends'][0];
        if ($count) {
            $dt = $this->dailyTaskManager->getDailyTask($this->account_id, \DailyTasks::TIMELY_CHIPS);
            $dt->current += $count;
            $dt->save();
        }
        return;
    }
}