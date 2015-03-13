<?php

class SpecialOfferManager extends Phalcon\Mvc\User\Component
{

    protected $items;

    public function setDI($di)
    {
        parent::setDI($di);
        $data = include DATA_PATH . '/specialOfferItems.php';
        $this->items = array();
        if (is_array($data)) {
            $this->items = $data;
        }
    }

    public function getSpecialOffer($account_id, &$ret)
    {
        if ($ret['errno']) { // 执行出错
            return;
        }
        $data = $this->getSpecialOfferInfo($account_id);
        if (! empty($data)) {
            $ret['special_offer'] = $data;
        }
    }

    public function getSpecialOfferData()
    {
        $configFile = DATA_PATH . '/specialOfferConfig.php';
        $data = array();
        if (is_file($configFile)) {
            $data = include $configFile;
        }
        return array_merge(array(
            'life_time' => 1800
        ), $data);
    }

    public function setAssetsDrain($account_id)
    {
        $sk = SessionManager::getSessionKey($account_id);
        $this->redis->hset($sk, 'so', 'drain');
    }

    public function setRegisterSpecialOffer($account_id)
    {
        $sk = SessionManager::getSessionKey($account_id);
        $this->redis->hset($sk, 'so', 'register');
    }

    public function getSpecialOfferInfo($account_id)
    {
        $me = \UserAccounts::findFirstByAccountId($account_id);
        if (! $me) {
            return array();
        }
        $has = false;
        $sk = SessionManager::getSessionKey($account_id);
        $so = $this->redis->hget($sk, 'so');
        $has = ($so == 'drain' || $so == 'register');
        if ($me->level >= 3) {
            srand();
            $d = rand(0, 99);
            if ($d < 5) {
                $has = true;
            }
        }
        $combo = 7;
        // 连续登录7天后
        if ($me->login_combo >= $combo) {
            $date = new DateTime(($combo - 1) . ' days ago');
            $ret = GooglePlayOrders::findFirst(array(
                'purchase_date > :date: AND account_id = :aid:',
                'bind' => array(
                    'date' => $date->format('Y-m-d H:i:s'),
                    'aid' => $me->account_id
                )
            ));
            // 没有购买过
            if (! $ret) {
                $has = true;
            }
        }
        $has = $has || $this->newsManager->getSpecialOfferInfo($me); // 是否有SpecialOffer的活动
        if ($has) {
            if ($so == date('Ymd') || $so == date('Ymd') . ':HIT') {
                return array();
            }
            // 阻止再次命中
            $this->redis->hset($sk, 'so', date('Ymd') . ':HIT');
            $ret = array();
            $items = array();
            $showBoxConfig = $this->showBoxManager->getShowBox();
            
            // 选出合适的special offer
            foreach ($this->items as $item) {
                $baseItem = $showBoxConfig[$item['base_slot']];
                $deltaItem = $showBoxConfig[$item['delta_slot']];
                // 价格不比base + delta 高 而且东西比 base + delta 多
                if (($baseItem['price'] + $deltaItem['price']) >= $item['price'] && ($baseItem['amount'] + $deltaItem['amount']) < $item['amount']) {
                    $items[] = $item;
                }
            }
            // 随机选取一个specialOffer
            $size = count($items);
            if ($size > 0) {
                // srand(date('Ymd') + $account_id);
                mt_srand();
                $index = mt_rand(0, $size - 1);
                $item = $items[$index];
            } else {
                $item = array();
            }
            foreach ($item as $k => $v) {
                $ret[$k] = $v;
            }
            return $ret;
        }
        // 阻止本次登录再次命中
        $this->redis->hset($sk, 'so', date('Ymd'));
        return array();
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setItems(array $items)
    {
        $this->items = $items;
        \Misc::cacheToFile($items, DATA_PATH . '/specialOfferItems.php');
    }
}