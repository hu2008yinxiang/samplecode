<?php
namespace Cmds;

class RewardAchievementCmd extends Cmd
{

    const CMD_NAME = 'RewardAchievement';

    public static function defaultData()
    {
        return array(
            'achievement_id' => ''
        );
    }

    protected function do_execute()
    {
        $achievement_id = $this->data['achievement_id'];
        if (empty($achievement_id) || ! intval($achievement_id)) {
            $this->ret['result']['errno'] = \Errors::EMPTY_DATA;
            return;
        }
        $me = $this->getMe();
        $reward = $this->achievementManager->reward($this->account_id, $achievement_id);
        $this->ret['result']['reward'] = 0;
        if (is_array($reward)) {
            $this->ret['result']['reward'] = $reward['reward'];
            $me->chip += $reward['reward'];
            $me->save();
        }
        $this->ret['result']['achievement_id'] = $achievement_id;
        $ac = $this->achievementManager->getAchievement($this->account_id, $achievement_id);
        $this->ret['result']['status'] = $ac->status;
        $this->ret['result']['current'] = $ac->current;
        
        // $this->ret['result']['reward'] = $reward;
        $this->ret['result']['chip'] = $me->chip;
        $this->ret['result']['diamond'] = $me->diamond;
        $this->ret['result']['data'] = $this->achievementManager->getValues($me->account_id);
    }
}