<?php
namespace Cmds;

class CheckOrderCmd extends Cmd
{

    const CMD_NAME = 'CheckOrder';

    public static function defaultData()
    {
        return array(
            'service' => 'unknow',
            'data' => array()
        );
    }

    protected function do_execute()
    {
        $type = $this->data['service'];
        $method = 'do_execute_' . $type;
        if (method_exists($this, $method)) {
            $this->{$method}();
        } else {
            $this->ret['result']['errno'] = \Errors::OP_DENIED;
        }
    }

    protected function do_execute_google_play()
    {
        $data = $this->data['data'];
        $config = $this->config->google_play;
        $content = $data['data'];
        $orderData = json_decode($content, true);
        $confArr = $config->toArray();
        if (! isset($orderData['packageName']) || ! isset($confArr[$orderData['packageName']])) {
            $this->logger->warning($this->account_id . ' has a failed purchase order.' . PHP_EOL . var_export($data, true));
            $this->ret['result']['errno'] = \Errors::INVALID_ORDER;
            return;
        }
        $cert = $orderData['packageName'];
        $cert = str_split($config->{$cert}, 64);
        array_unshift($cert, '-----BEGIN PUBLIC KEY-----');
        array_push($cert, '-----END PUBLIC KEY-----');
        $cert = implode("\n", $cert);
        $sign = $data['sign'];
        //
        $pub_key_id = openssl_pkey_get_public($cert);
        // error_log(var_export(is_resource($pub_key_id), true));
        $signature = base64_decode($sign);
        $result = openssl_verify($content, $signature, $pub_key_id);
        // $this->logger->notice(sprintf('openssl_verify:%s', $result));
        $orderData = json_decode($content, true);
        if (! ($result === 1) || empty($orderData)) {
            $this->logger->warning($this->account_id . ' has a failed purchase order.' . PHP_EOL . var_export($data, true));
            $this->ret['result']['errno'] = \Errors::INVALID_ORDER;
            return;
        }
        $iapid = $orderData['productId'];
        $orderId = $orderData['orderId'];
        $order = \GooglePlayOrders::findFirstByOrderId($orderId);
        $chip = 0;
        $diamond = 0;
        $price = 0;
        $benefit = 0;
        $me = $this->getMe();
        if (! $order) { // 订单没被注册过
            $order = new \GooglePlayOrders();
            $order->order_id = $orderId;
            $order->account_id = $this->account_id;
            $order->product_id = $iapid;
            $order->purchase_time = $orderData['purchaseTime'];
            $order->purchase_date = date('Y-m-d H:i:s', intval($order->purchase_time / 1000));
            $order->package_name = $orderData['packageName'];
            $order->sign = $sign;
            $order->data = $content;
            $shopConfig = $this->shopManager->getShopConfig();
            $monthlyOffer = $this->shopManager->getMonthlyOffer();
            if ($iapid == $monthlyOffer['iapid']) {
                // 查找月卡
                $chip = $monthlyOffer['amount'];
                $price = $monthlyOffer['price'];
                //
                $this->doMonthlyOffer($monthlyOffer);
            } else {
                $shopItemFound = false;
                foreach ($shopConfig as $k => $item) {
                    $k %= 12;
                    // 查找商城物品
                    if ($item['iapid'] == $iapid) {
                        $price = $item['price'];
                        if ($k < 6) {
                            $chip = $item['amount'];
                        } else {
                            $diamond = $item['amount'];
                        }
                        $shopItemFound = true;
                        break;
                    }
                }
                if (! $shopItemFound) {
                    // 从新版商城中查找
                    $itemList = $this->showBoxManager->getItemList();
                    foreach ($itemList as $item) {
                        if ($item['iapid'] == $iapid) {
                            $price = $item['price'];
                            if ($item['type'] == 'diamond') {
                                $diamond = $item['amount'];
                            } else {
                                $chip = $item['amount'];
                            }
                            $shopItemFound = true;
                            break;
                        }
                    }
                }
                
                if(!$shopItemFound){
                    // 从special offer中查找
                    $itemList = $this->specialOfferManager->getItems();
                    foreach ($itemList as $item){
                        if($item['iapid'] == $iapid){
                            $chip = $item['amount'];
                            $price = $item['price'];
                            $shopItemFound = true;
                            break;
                        }
                    }
                }
                
                if (! $shopItemFound) {
                    $this->logger->warning($this->account_id . ' has a failed purchase order, shop item not found.' . PHP_EOL . var_export($data, true));
                    return $this->ret['result']['errno'] = \Errors::INVALID_ORDER;
                }
            }
            
            $vipPointScale = $me->getVIPPointScale();
            $me->vip_score += intval(ceil($price * $vipPointScale)) * 40;
            $me->chip += $chip;
            $me->diamond += $diamond;
            // 保存结果
            $ret = ($me->save() && $order->save());
            if (! $ret) {
                // 订单保存失败
                $this->logger->warning($this->account_id . ' may has a faild order to save. ' . PHP_EOL . var_export($data, true));
                $this->logger->warning(var_export($me->getMessages(), true));
                $this->logger->warning(var_export($order->getMessages(), true));
            } else {
                $this->payedSuccess($price, $chip, $diamond);
                $this->logger->warning(sprintf('purchase ok user:%s order:%s price:%s +chip:%d +diamond:%d +vip_score:%d', $this->account_id, $order->order_id, $price, $chip, $diamond, floor($price)));
            }
        } else {
            $this->logger->warning(sprintf('requery order order:%s user:%s data:%s', $order->order_id, $this->account_id, $content));
        }
        $this->ret['result']['item_chip'] = $chip;
        $this->ret['result']['item_diamond'] = $diamond;
        $this->ret['result']['chip'] = $me->chip;
        $this->ret['result']['diamond'] = $me->diamond;
        $this->ret['result']['vip_score'] = $me->vip_score;
    }

    protected function payedSuccess($price, $chip = 0, $diamond = 0)
    {
        $me = $this->getMe();
        \SessionManager::setLivingPay($this->account_id, $price);
        // 充值任务
        $dt = $this->dailyTaskManager->getDailyTask($this->account_id, \DailyTasks::GAMEYEPER);
        $shopScale = $me->getShopTaskScale();
        $dt->current += intval(round(($chip + $diamond * 2500) * $shopScale));
        $dt->save();
        // buyin counter
        $buyInCounter = \Extras::load($this->account_id, \Extras::BUYIN_COUNTER, 0);
        $buyInCounter->value = 0;
        $buyInCounter->save();
        
        // 记录充值 用于设置threshold
        $ratio = ($me->win_round / ($me->round + 1)) > 0.3 ? 1.5 : 2.0;
        //
        $total = ($diamond * 2500) * $ratio + $me->chip * $ratio;
        $total = intval(round($total));
        $extra = \Extras::load($this->account_id, \Extras::EXTRA_THRESHOLD, $total);
        $extra->value = $total;
        $extra->save();
        
        //
        $key = 'p:user:' . $me->account_id;
        $pipe = $this->redis->pipeline();
        $pipe->hset($key, 'threshold', $total);
        $pipe->expire($key, 86400);
        $pipe->execute();
    }

    protected function doMonthlyOffer(array $monthlyOffer)
    {
        $endDate = date_create('30 days 23:59:59');
        $this->ret['result']['end_date'] = $endDate->format('Y-m-d');
        $currentDate = date_create('today');
        $value = array(
            'end' => $endDate,
            'current' => $currentDate,
            'perday' => $monthlyOffer['perday']
        );
        $extra = \Extras::load($this->account_id, \Extras::MONTHLY_OFFER, $value);
        $extra->value = $value;
        $extra->save();
    }
}