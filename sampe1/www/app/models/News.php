<?php

class News extends Phalcon\Mvc\Model
{

    const TYPE_SPECIAL_OFFER = 'Special Offer';

    const TYPE_EXTRA_LOGIN_BONUS = 'Extra Login Bonus';

    const TYPE_EXTRA_TASK_BONUS = 'Extra Task Bonus';

    const TYPE_EXTRA_TASK_BONUS_STAIGHT_FLUSH = 'Extra Task Bonus for Straight Flush';

    const TYPE_EXTRA_TASK_BONUS_ROYAL_STAIGHT_FLUSH = 'Extra Task Bonus for Royal Straight Flush';
    
    const TYPE_EXTRA_TASK_BONUS_4_OF_A_KIND = 'Extra Task Bonus for 4 of A Kind';

    const TYPE_CHIP_SENDER = 'Chip Sender';

    const TYPE_FESTIVAL = 'Festival';

    public $start_date;

    public $end_date;

    public $content;

    public $title;

    public $type;

    public $news_id;

    public function isActive(UserAccounts $ua = null)
    {
        $today = new DateTime();
        $start = DateTime::createFromFormat('Y-m-d H:i:s', $this->start_date);
        $end = DateTime::createFromFormat('Y-m-d H:i:s', $this->end_date);
        return $today >= $start && $today <= $end;
    }
}