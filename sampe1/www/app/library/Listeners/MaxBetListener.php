<?php
namespace Listeners
{

    class MaxBetListener extends \Phalcon\Mvc\User\Component
    {

        public function maxBetChanged(\Phalcon\Events\Event $event, $ua, $maxBet = 0)
        {
            //$this->logger->notice('user [ id: ' . $ua->account_id . ' ] [ name: ' . $ua->nickname . ' ] max bet changed [ maxBet: ' . $maxBet . ']');
            
            $ac = $this->achievementManager->getAchievement($ua->account_id, 'maxBet');
            $ac->current = ($ac->current > $maxBet) ? $ac->current : $maxBet;
            $ac->save();
        }
    }
}