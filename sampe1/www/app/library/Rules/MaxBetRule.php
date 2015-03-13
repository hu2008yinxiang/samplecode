<?php
namespace Rules;

class MaxBetRule extends MaxValueReachRule
{

    public function getCatalog()
    {
        return 'user';
    }

    public function maxBetChanged(\Phalcon\Events\Event $event, \UserAccount $ua, $data = null)
    {
        $this->onValueChanged($event, $ua, $data);
    }
}