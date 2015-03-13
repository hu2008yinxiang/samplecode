<?php

class LotteryManager extends \Phalcon\Mvc\User\Component
{

    protected $rewards = null;

    protected $weights = null;

    protected $bets = null;

    public function setDI($dependencyInjector)
    {
        parent::setDI($dependencyInjector);
        $config = $dependencyInjector->get('config')->lottery;
        $rewards = $config->rewards;
        if (! is_array($rewards)) {
            $rewards = $rewards->toArray();
        }
        $this->rewards = $rewards;
        $weights = $config->weights;
        if (! is_array($weights)) {
            $weights = $weights->toArray();
        }
        $this->weights = $weights;
        $bets = $config->bets;
        if (! is_array($bets)) {
            $bets = $bets->toArray();
        }
        $this->bets = $bets;
    }

    public function getRewards()
    {
        return $this->rewards;
    }

    public function getWeights()
    {
        return $this->weights;
    }

    public function getBets()
    {
        return $this->bets;
    }

    public function playFree($account_id)
    {
        $me = \UserAccounts::findFirstByAccountId($account_id);
        $weights = $this->weights;
        // 修正权重
        /*
        if ($me->chip > 10000) {
            $p_weights = array(
                500 => 10,
                1000 => 2,
                1500 => 0,
                2000 => 0,
                2500 => 0,
                3000 => 0,
                3500 => 0,
                4000 => 1
            );
            foreach ($weights as $k => $v) {
                $weights[$k] = $p_weights[$this->rewards[$k]];
            }
        } else {
            $p_weights = array(
                500 => 5,
                1000 => 4,
                1500 => 3,
                2000 => 2,
                2500 => 1,
                3000 => 1,
                3500 => 1,
                4000 => 1
            );
            foreach ($weights as $k => $v) {
                $weights[$k] = $p_weights[$this->rewards[$k]];
            }
        }
        
        */
        
        /*$p_weights = array(
                500 => 1,
                1000 => 3,
                1500 => 5,
                2000 => 7,
                2500 => 8,
                3000 => 6,
                3500 => 4,
                4000 => 2
            );
            
        foreach ($weights as $k => $v) {
            $weights[$k] = $p_weights[$this->rewards[$k]];
        }
        */
            
        $max = array_sum($weights);
        $min = 0;
        srand(); // 重置随机数种子
        $ret = rand($min, $max);
        $index = - 1;
        do {
            ++ $index;
            $ret -= $weights[$index]; // 权重
        } while ($ret > 0);
        $reward = $this->rewards[$index];
        return array(
            $reward,
            $index
        );
    }

    public function playPaid($account_id, $pay)
    {
        // 修正权重
        $weights = $this->weights;
        /*
        $p_weights = array(
            500 => 1,
            1000 => 3,
            1500 => 5,
            2000 => 7,
            2500 => 8,
            3000 => 6,
            3500 => 4,
            4000 => 2
        );
        foreach ($weights as $k => $v) {
            $weights[$k] = $p_weights[$this->rewards[$k]];
        }
        // 修正权重
        // $weights[3] += 2; // 2000
        // $weights[4] += 1; // 2500
        // $weights[5] += 1; // 3000
         */
        $max = array_sum($weights);
        $min = 0;
        srand(); // 重置随机数种子
        $ret = rand($min, $max);
        $index = - 1;
        do {
            ++ $index;
            $ret -= $weights[$index]; // 权重
        } while ($ret > 0);
        $reward = $this->rewards[$index];
        return array(
            $reward,
            $index
        );
    }
}