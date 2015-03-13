<?php

class NewsManager extends Phalcon\Mvc\User\Component
{

    public function getActiveNews(\UserAccounts $ua = null)
    {
        static $data = array();
        if (empty($data)) {
            $news = News::find(array(
                'orderBy' => 'news_id ASC'
            ));
            $data = array();
            foreach ($news as $n) {
                if ($this->isActive($ua, $n)) {
                    $data[] = $n;
                }
            }
        }
        return $data;
    }

    public function getExtraLoginBonus(\UserAccounts $ua = null)
    {
        $bonus = 0;
        if ($this->hasNews($ua, News::TYPE_EXTRA_LOGIN_BONUS)) {
            $bonus = 10000;
            $weekday = date('w');
            if ($weekday == 0 || $weekday == 7) {
                $bonus = 30000;
            }
        }
        return $bonus;
    }

    public function getExtraTaskBonus(\UserAccounts $ua = null, $taskid)
    {
        $extra = 0;
        switch ($taskid) {
            case DailyTasks::FOUR_OF_A_KIND:
                if ($this->hasNews($ua, News::TYPE_EXTRA_TASK_BONUS_4_OF_A_KIND)) {
                    $extra = 45000;
                }
                break;
            case DailyTasks::ROYAL_FLUSH:
                if ($this->hasNews($ua, News::TYPE_EXTRA_TASK_BONUS_ROYAL_STAIGHT_FLUSH)) {
                    $extra = 4000000;
                }
                break;
            case DailyTasks::STRAIGHT_FLUSH:
                if ($this->hasNews($ua, News::TYPE_EXTRA_TASK_BONUS_STAIGHT_FLUSH)) {
                    $extra = 200000;
                }
                break;
        }
        return $extra;
    }

    public function hasNews(\UserAccounts $ua = null, $type)
    {
        $news = $this->getActiveNews($ua);
        foreach ($news as $n) {
            if ($n->type == $type) {
                return true;
            }
        }
        return false;
    }

    public function getSpecialOfferInfo(UserAccounts $ua = null)
    {
        $news = $this->getActiveNews($ua);
        foreach ($news as $n) {
            if ($n->type == News::TYPE_SPECIAL_OFFER) {
                return true;
            }
        }
        return false;
    }

    public function getNewsUrl(\UserAccounts $ua = null)
    {
        $url = $this->url->getStatic('news/news.php');
        $url = str_replace('/static/', '/', $url);
        $ids = array();
        $news = $this->getActiveNews($ua);
        foreach ($news as $n) {
            $ids[] = $n->news_id;
        }
        $tag = '20150000';
        $luckySender = $this->getLuckySender();
        if (! empty($luckySender)) {
            $tag = date('Ymd');
        }
        $tid = '';
        if ($ua != null) {
            $tid = $ua->account_id;
        }
        return $url . '?' . http_build_query(array(
            'ids' => $ids,
            'tag' => $tag,
            'tid' => $tid
        ));
    }

    public function isActive(UserAccounts $ua = null, News $news)
    {
        $type = $news->type;
        $active = true;
        switch ($type) {
            case News::TYPE_CHIP_SENDER:
                break;
            case News::TYPE_EXTRA_LOGIN_BONUS:
                break;
            case News::TYPE_SPECIAL_OFFER:
                break;
            case News::TYPE_EXTRA_TASK_BONUS:
                break;
            case News::TYPE_FESTIVAL:
                break;
        }
        return $news->isActive($ua) && $active;
    }

    public function getLuckySender()
    {
        $date = new DateTime('yesterday');
        $key = 'lucky_sender:' . $date->format('Ymd');
        return json_decode($this->redis->get($key), true);
    }

    public function setLuckySender($ids)
    {
        $key = 'lucky_sender:' . date('Ymd');
        $this->redis->set($key, json_encode($ids)); // 写入值
        $this->redis->expire($key, 60 * 60 * 25); // 写入过期
    }

    public function incrSender()
    {
        $key = 'sender_count:' . date('Ymd');
        $this->redis->incr($key);
        $this->redis->expire($key, 60 * 60 * 25);
    }

    public function countSender()
    {
        $date = new DateTime('yesterday');
        $key = 'sender_count:' . $date->format('Ymd');
        return $this->redis->get($key);
    }

    public function getShowBoxSelectors()
    {
        $news = $this->getActiveNews(null);
        foreach ($news as $newi) {
            if ($newi->type == \News::TYPE_FESTIVAL) {
                return explode(',', $newi->content);
            }
        }
        return null;
    }

    public function getFestivalImageUrl(\UserAccounts $ua)
    {
        $news = $this->getActiveNews($ua);
        foreach ($news as $n) {
            if ($n->type == News::TYPE_FESTIVAL) {
                $imgPath = sprintf('news/news.php/images/%s.png', $n->news_id);
                $imgUrl = str_replace('/static/', '/', $this->url->getStatic($imgPath));
                return $imgUrl;
            }
        }
        return null;
    }
}