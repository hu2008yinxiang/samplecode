<?php
namespace Listeners
{

    class MaxBuyInListener extends \Phalcon\Mvc\User\Component
    {

        public function maxBuyInChanged(\Phalcon\Events\Event $event, \UserAccounts $ua, $buyIn)
        {
            //$this->logger->notice('user [ id: ' . $ua->account_id . ' ] [ name: ' . $ua->nickname . ' ] max buy in changed [ maxBuyIn: ' . $buyIn . ']');
            
            $ac = $this->achievementManager->getAchievement($ua->account_id, 'maxBuyIn');
            $ac->current = ($ac->current > $buyIn) ? $ac->current : $buyIn;
            $ac->save();
        }
    }
}