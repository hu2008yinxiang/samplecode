<?php
namespace Cmds
{

    class RewardLastRanksCmd extends Cmd
    {

        const CMD_NAME = 'RewardLastRanks';

        public static function defaultData()
        {
            return array(
                'serial' => 0
            );
        }

        protected function do_execute()
        {
            $serial = $this->data['serial'];
            $reward = 0;
            $reward += $this->ranksManager->rewardFriendRank($this->account_id, $serial);
            
            $reward += $this->ranksManager->rewardGlobalRank($this->account_id);
            
            $me = $this->getMe();
            $rankRewardScale = $me->getRankRewardScale();
            $reward *= $rankRewardScale;
            if ($reward >= 1) {
                $me->chip += $reward;
                $me->save();
            }
            $this->ret['result']['reward'] = $reward;
            $this->ret['result']['chip'] = $me->chip;
            $this->ret['result']['diamond'] = $me->diamond;
            $this->ret['result']['scale'] = $rankRewardScale;
        }
    }
}