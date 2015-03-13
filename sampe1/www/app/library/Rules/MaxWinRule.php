<?php
namespace Rules;

class MaxWinRule extends MaxValueReachRule
{

    public function getCatalog()
    {
        return 'user';
    }

    public function maxWinChanged(\Phalcon\Events\Event $event,\UserAccounts $ua, $data = null)
    {
        $this->onValueChanged($event, $ua, $data);
    }
}