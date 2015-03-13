<?php
namespace Cmds;

class PlayLotteryCmd extends Cmd
{

    const CMD_NAME = 'PlayLottery';

    public static function defaultData()
    {
        return array(
            'diamond' => 0
        );
    }

    protected function do_execute()
    {
        $me = $this->getMe();
        $diamond = $this->data['diamond'];
        $date = date('Y-m-d');
        $is_free = $me->last_lottery == $date ? 0 : 1;
        // $is_free = $is_free && ($diamond == 0);
        $lm = $this->lotteryManager;
        $reward = 0;
        $index = - 1;
        if ($is_free && ($diamond == 0)) {
            list ($reward, $index) = $lm->playFree($this->account_id);
            $reward *= $me->getDailyFreeSpinScale();
            $me->last_lottery = $date;
        } else {
            if (! in_array($diamond, $lm->getBets())) {
                $this->ret['result']['errno'] = \Errors::OP_DENIED;
                return;
            }
            if ($diamond > $me->diamond) {
                $this->ret['result']['errno'] = \Errors::DIAMOND_NOT_ENOUGH;
                return;
            }
            $me->diamond -= $diamond;
            // $me->save();
            list ($reward, $index) = $lm->playPaid($this->account_id, $diamond);
            $reward *= $diamond;
        }
        $me->chip += $reward;
        $me->save();
        $this->ret['result']['reward'] = $reward;
        $this->ret['result']['index'] = $index;
        $this->ret['result']['is_free'] = $is_free;
        $this->ret['result']['pay'] = $diamond;
        $this->ret['result']['diamond'] = $me->diamond;
        $this->ret['result']['chip'] = $me->chip;
        $this->getEventsManager()->fire('lottery:afterPlayed', $me, array(
            'is_free' => $is_free,
            'reward' => $reward
        ));
    }
}