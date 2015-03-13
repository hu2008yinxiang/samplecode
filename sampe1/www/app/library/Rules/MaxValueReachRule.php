<?php
namespace Rules;

class MaxValueReachRule extends Rule
{

    protected function onValueChanged(\Phalcon\Events\Event $event, \UserAccounts $ua, $data = null)
    {
        $adata = $this->data;
        $acm = \Achievements::load($account_id, $achievement_id);
        // 已完成或者历史大于当前
        if ($acm->completed || $acm->current > $data) {
            return;
        }
        // 计算星级
        $index = 0;
        $rewardable = false;
        for ($index = 0; $index < 3; ++ $index) {
            $target = $adata['target'][$index];
            if ($data < $target) {
                break;
            }
            $rewardable = $rewardable || ($acm->status[$index] == '0');
        }
        // 达到3星级
        if ($index == 3) {
            $data = $adata['target'][2];
            // 完成
            $acm->completed = 1;
        }
        $acm->rewardable = $rewardable ? 1 : 0;
        $acm->current = $data;
        $acm->save();
    }
}