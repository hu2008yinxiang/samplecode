<?php
namespace Listeners
{

    class MaxWinListener extends \Phalcon\Mvc\User\Component
    {

        public function maxWinChanged(\Phalcon\Events\Event $event, \UserAccounts $ua, $maxWin = 0)
        {
            //$this->logger->notice('user [ id: ' . $ua->account_id . ' ] [ name: ' . $ua->nickname . ' ] max win changed [ maxWin: ' . $maxWin . ']');
            $ac = $this->achievementManager->getAchievement($ua->account_id, 'maxWin');
            $ac->current = ($ac->current > $maxWin) ? $ac->current : $maxWin;
            $ac->save();
        }
    }
}