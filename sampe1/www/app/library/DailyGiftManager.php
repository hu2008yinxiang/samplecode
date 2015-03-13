<?php

class DailyGiftManager extends \Phalcon\DI\Injectable
{

    const FBLOGIN_REWARD = 20000;

    public function sendGift($from, $to)
    {
        $date = date('Y-m-d');
        $friend = \Friends::findFirst(array(
            'src_id = :from: AND dst_id = :to: AND status = :status: AND ( last_gift_day is NULL OR last_gift_day < :last_day: )',
            'bind' => array(
                'from' => $from,
                'to' => $to,
                'status' => \Friends::STATUS_ADDED,
                'last_day' => $date
            )
        ));
        if ($friend) {
            $friend->last_gift_day = $date;
            $friend->save();
            Mails::newMail($from, $to, 'gift');
            $this->newsManager->incrSender();
            return true;
        }
        return false;
    }

    public function sendSystemGift($from, $to, $content)
    {
        Mails::newMail($from, $to, 'gift', $content);
    }

    public function sendFBLoginReward($to)
    {
        $this->sendSystemGift(Mails::SENDER_FBLOGIN, $to, sprintf('Enjoy %s FREE chips for connecting facebook!', number_format(static::FBLOGIN_REWARD, 0, '', ',')));
    }

    public function sendChipSenderReward($to)
    {
        $this->sendSystemGift(Mails::SENDER_FBLOGIN, $to, '10M chips for event winner! Check news for details.');
    }
}