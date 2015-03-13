<?php
namespace Cmds
{

    class RewardDailyTaskCmd extends Cmd
    {

        const CMD_NAME = 'RewardDailyTask';

        public static function defaultData()
        {
            return array(
                'id' => ''
            );
        }

        protected function do_execute()
        {
            $tid = $this->data['id'];
            if (empty($tid)) {
                $this->ret['result']['errno'] = \Errors::EMPTY_DATA;
                return;
            }
            $reward = $this->dailyTaskManager->reward($this->account_id, $tid);
            $this->ret['result']['reward'] = $reward;
            $me = $this->getMe();
            $me->chip += $reward;
            $me->save();
            $this->ret['result']['chip'] = $me->chip;
            $this->ret['result']['diamond'] = $me->diamond;
            $this->ret['result']['data'] = $this->dailyTaskManager->getValues($this->account_id);
        }
    }
}