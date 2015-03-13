<?php

class DailyTaskManager extends \Phalcon\Mvc\User\Component
{

    protected $data = null;

    protected $shadow = null;

    public function setDI($di)
    {
        parent::setDI($di);
        $this->data = include APP_PATH . '/app/config/dailytasksConfig.php';
        $this->shadow = array();
        foreach ($this->data as $d) {
            $this->shadow[$d['id']] = $d;
        }
    }

    /**
     *
     * @param string $account_id            
     * @return array
     */
    public function getValues($account_id)
    {
        $values = array();
        foreach ($this->data as $dt) {
            $mdt = $this->getDailyTask($account_id, $dt['id']);
            $extra = $this->newsManager->getExtraTaskBonus(null, $dt['id']);
            $values[] = array(
                'id' => $dt['id'],
                'current' => $mdt->current,
                'rewarded' => $mdt->rewarded == 'true',
                'extra' => $extra
            );
        }
        return $values;
    }

    /**
     *
     * @return \DailyTasks
     * @param string $account_id            
     * @param string $task_id            
     */
    public function getDailyTask($account_id, $task_id)
    {
        return \DailyTasks::load($account_id, $task_id, 0);
    }

    /**
     *
     * @param string $account_id            
     * @param string $task_id            
     * @return number
     */
    public function reward($account_id, $task_id)
    {
        if (! isset($this->shadow[$task_id])) {
            return 0;
        }
        $info = $this->shadow[$task_id];
        $dt = $this->getDailyTask($account_id, $task_id);
        // 已领取 或者未达到要求
        $self_count_ids = array(
            1,
            2,
            3,
            4,
            5,
            6
        );
        // 如果在客户端计数 则直接通过
        if ($dt->rewarded == 'true' || (! in_array($task_id, $self_count_ids) && $info['require'] > $dt->current)) {
            return 0;
        }
        $reward = 0;
        $dt->rewarded = 'true';
        $reward = $info['reward'] + $this->newsManager->getExtraTaskBonus(null, $task_id);
        if ($task_id == \DailyTasks::GAMEYEPER) {
            $dt->rewarded = 'false';
            $reward = $dt->current;
            $dt->current = 0;
        }
        $dt->save();
        return $reward;
    }
}