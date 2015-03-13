<?php
namespace Cmds;

class GetMonthlyOfferCmd extends Cmd
{
    const CMD_NAME = 'GetMonthlyOffer';

    protected function do_execute()
    {
        $offer = $this->shopManager->loadMonthlyOffer($this->account_id);
        $chip = 0;
        $today = date_create('today');
        if ($offer->value['end'] >= $today && $offer->value['current'] < $today && $offer->value['perday'] > 0) {
            $offer->value['current'] = $today;
            $chip = $offer->save() ? $offer->value['perday'] : 0;
        }
        $me = $this->getMe();
        $me->chip += $chip;
        $me->save();
        $scale = $me->getShopTaskScale();
        $task_chip = intval($chip * $scale);
        // 充值任务
        $dt = $this->dailyTaskManager->getDailyTask($this->account_id, \DailyTasks::GAMEYEPER);
        $dt->current += $task_chip;
        $dt->save();
        //
        $this->ret['result']['perday'] = $chip;
        $this->ret['result']['chip'] = $me->chip;
        $this->ret['result']['end'] = date_format($offer->value['end'], 'Y-m-d');
    }
}