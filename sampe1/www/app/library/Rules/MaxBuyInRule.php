<?php
namespace Rules;

class MaxBuyInRule extends MaxValueReachRule
{

    public function getCatalog()
    {
        return 'user';
    }

    public function maxBuyInChanged(\Phalcon\Events\Event $event, \UserAccounts $ua, $data = null)
    {
        $this->onValueChanged($event, $ua, $data);
    }
}