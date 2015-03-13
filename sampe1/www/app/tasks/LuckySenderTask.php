<?php

class LuckySenderTask extends Phalcon\CLI\Task
{

    public function mainAction()
    {
        $active = $this->newsManager->hasNews(null, News::TYPE_CHIP_SENDER);
        if (! $active) {
            // 没有活动的Chip Sender活动
            return;
        }
        $count = intval($this->newsManager->countSender());
        $count = intval($count / 10000);
        $count = min($count, 3);
        $builder = $this->modelsManager->createBuilder();
        $builder->from('Friends');
        $builder->columns('src_id');
        $builder->distinct(true);
        $builder->where('last_gift_day = :lgd:');
        $builder->orderBy('rand()');
        $builder->limit($count);
        $query = $builder->getQuery();
        $result = $query->execute(array(
            'lgd' => date('Y-m-d')
        ));
        $ids = array();
        foreach ($result as $r) {
            $ids[] = $r['src_id'];
            // TODO Send Reward
            $this->dailyGiftManager->sendChipSenderReward($r['src_id']);
        }
        srand();
        $id = rand(14510000, 14512999);
        while (count($ids) < 3) {
            $id += rand(1, 333);
            $ids[] = $id;
        }
        $this->newsManager->setLuckySender($ids);
    }
}