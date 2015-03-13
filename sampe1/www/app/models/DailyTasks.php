<?php

class DailyTasks extends \Phalcon\Mvc\Model
{

    const ROYAL_FLUSH = 1;

    const STRAIGHT_FLUSH = 2;

    const FOUR_OF_A_KIND = 3;

    const FULL_HOUSE = 4;

    const FLUSH = 5;

    const STRAIGHT = 6;

    const WINNING = 7;

    const ONE_UP = 8;

    const SEVEN_UP = 9;

    const POWER_OF_UP = 10;

    const TIMELY_CHIPS = 11;

    const GAMEYEPER = 12;

    /**
     *
     * @param string $account_id            
     * @param string $task_id            
     * @param unknown $def            
     * @return \DailyTasks
     */
    public static function load($account_id, $task_id, $def)
    {
        $today = date('Ymd');
        $task = static::findFirst(array(
            'account_id = :aid: AND task_id = :tid:',
            'bind' => array(
                'aid' => $account_id,
                'tid' => $task_id
            )
        ));
        if (! $task) {
            $task = new static();
            $task->account_id = $account_id;
            $task->task_id = $task_id;
            $task->current = $def;
            $task->last_day = $today;
            $task->rewarded = 'false';
            $task->save();
        }
        if ($task->last_day != $today && $task_id != static::GAMEYEPER) {
            $task->current = $def;
            $task->last_day = $today;
            $task->rewarded = 'false';
            $task->save();
        }
        if ($task_id == static::GAMEYEPER) {
            $task->last_day = $today;
        }
        return $task;
    }
}