<?php
namespace Cmds
{

    class LoginRewardCmd extends Cmd
    {

        const CMD_NAME = 'LoginReward';

        protected function do_execute()
        {
            $extra = \Extras::load($this->account_id, \Extras::LAST_LOGIN_REWARD, '00000000');
            $value = date('Ymd');
            $cb = 0;
            $award = 0;
            $me = $this->getMe();
            $scale = $me->getLoginRewardScale();
            if ($extra->value != $value) {
                $extra->value = $value;
                $extra->save();
                $cb = $me->login_combo;
                $award = $this->getAward($cb) + $this->newsManager->getExtraLoginBonus($me);
                // VIP 倍乘
                $award *= $scale;
                $me->chip += $award;
                $me->save();
            }
            $this->ret['result']['login_combo'] = $cb;
            $this->ret['result']['award'] = $award;
            $this->ret['result']['chip'] = $me->chip;
            $this->ret['result']['scale'] = $scale;
            return;
        }

        protected function getAward($cb)
        {
            $data = array(
                1 => 2000,
                2 => 3000,
                3 => 4000,
                4 => 5000,
                5 => 6000,
                6 => 8000,
                7 => 10000
            );
            if($cb > 7){
                $cb = 7;
            }
            if (! isset($data[$cb])) {
                return 0;
            }
            return $data[$cb];
        }
    }
}