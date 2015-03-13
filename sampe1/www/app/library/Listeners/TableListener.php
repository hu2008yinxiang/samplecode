<?php
namespace Listeners
{

    class TableListener extends \Phalcon\Mvc\User\Component
    {

        public function exitTable(\Phalcon\Events\Event $event, \UserAccounts $ua, array $data = null)
        {
            // $this->logger->notice('user [ id: ' . $ua->account_id . ' ] [ name: ' . $ua->nickname . ' ] exit table. table data ' . var_export($data, true));
            $default = array(
                'buyIn' => 0,
                'bigBlind' => 0,
                'capacity' => 5,
                'speed' => 'n',
                'WLR' => 'l',
                'level' => 0,
                'c0' => 0,
                'c1' => 0,
                'c2' => 0,
                'c3' => 0,
                'c4' => 0,
                'c5' => 0,
                'hands' => 0,
                'accountId' => '',
                'maxWin' => 0,
                'maxBet' => 0,
                'totalWin' => 0,
                'winHands' => 0,
                'continueWinTimes' => 0,
                'fee' => 0,
                'rank' => 10,
                'orgBuyIn' => 0,
                'orgFee' => 0,
                'thc' => $ua->thc,
                'threshold' => $ua->threshold
            );
            /**
             * $data['buyIn'] 买入剩余
             * $data['bigBlind'] 大盲注
             * $data['capacity'] 人数
             * $data['speed] 速度
             * $data['WLR'] 输赢概率
             * $data['level'] 进入等级
             * $data['c0']~$data['c5'] 最佳手牌
             * $data['hands'] 局数
             * $data['accountId'] 账号
             * $data['maxWin'] 最大赢注
             * $data['maxBet'] 最大下注
             * $data['totalWin'] 总计赢注
             * $data['winHands'] 赢得局数
             * $data['continueWinTimes'] 连赢次数
             */
            $data = array_merge($default, $data);
            if (empty($data['accountId']))
                return;
                // error_log($data['buyIn'] + $data['fee'] - $data['orgFee'] - $data['orgBuyIn']);
            $chip_delta = ($data['buyIn'] + $data['fee'] - $data['orgFee'] - $data['orgBuyIn']);
            $ua->chip += $chip_delta;
            // $this->logger->notice(sprintf('[ event:%s:%s:%s ] chip_delta:%s orgFee:%s orgBuyIn:%s fee:%s buyIn:%s chip:%s', 'EXIT_TABLE', $ua->account_id, $ua->nickname, $chip_delta, $data['orgFee'], $data['orgBuyIn'], $data['fee'], $data['buyIn'], $ua->chip));
            // $this->getEventsManager()->fire('user:chipChanged', $ua, $data['buyIn']);
            // }
            // round
            if ($this->logger->getLogLevel() >= \Phalcon\Logger::NOTICE) {
                $log_data = $data;
                $unset_keys = array(
                    'c0',
                    'c1',
                    'c2',
                    'c3',
                    'c4',
                    'c5',
                    'capacity',
                    'speed',
                    'rank'
                );
                foreach ($unset_keys as $k) {
                    unset($log_data[$k]);
                }
                $data_append = array(
                    'chip' => $ua->chip - $chip_delta,
                    'diamond' => $ua->diamond,
                    'chip_delta' => $chip_delta,
                    'chip_final' => $ua->chip,
                    'KEYWORD' => 'EXIT_TABLE'
                );
                $log_data = array_merge($log_data, $data_append);
                ksort($log_data);
                $log_str = json_encode($log_data);
                $this->logger->notice($log_str);
            }
            
            if ($data['hands'] != 0) {
                
                // drop
                $hands_delta = $data['hands'] - $ua->round;
                if ($hands_delta > 0) {
                    $living_pay = \SessionManager::getLivingPay($ua->account_id);
                    $living_pay->value['count'] -= $hands_delta;
                    if ($living_pay->value['count'] < 0) {
                        $living_pay->value['count'] = 0;
                    }
                    $living_pay->save();
                    // 修改chands
                    if ($data['WLR'] == 'w' || $data['WLR'] == 'l') {
                        $ua->chands -= $hands_delta;
                    }
                }
                //
                
                if ($ua->round != $data['hands'] && $data['orgBuyIn'] != 0) {
                    // 处理maxBuyIn变化
                    $this->eventsManager->fire('achievement:maxBuyInChanged', $ua, $data['orgBuyIn']);
                }
                $ua->round = $data['hands'];
                $this->getEventsManager()->fire('user:roundChanged', $ua, $data['hands']);
            } // best hand
            $best_hand = array(
                $data['c0'],
                $data['c1'],
                $data['c2'],
                $data['c3'],
                $data['c4'],
                $data['c5']
            );
            if ($ua->best_hand != $best_hand) {
                $ua->best_hand = $best_hand;
                $this->getEventsManager()->fire('user:bestHandChanged', $ua, $best_hand);
            }
            // total win
            if ($data['totalWin'] != 0) {
                $this->getEventsManager()->fire('user:totalWinChanged', $ua, $data['totalWin']);
                $rank = \Ranks::loadNow($ua->account_id);
                // $rank->win += $data['totalWin'];
                $rank->addWin($data['totalWin']);
                $rank->save();
                $this->ranksManager->playerUpdate($ua->account_id, $rank->getWin());
            }
            // win round
            if ($data['winHands'] != 0) {
                $delta = $data['winHands'] - $ua->win_round;
                
                // $dt = $this->dailyTaskManager->getDailyTask($ua->account_id,)
                if ($delta > 0) {
                    $ua->win_round = $data['winHands'];
                    $this->getEventsManager()->fire('user:winRoundChanged', $ua, $data['winHands']);
                    $dts = array(
                        \DailyTasks::ONE_UP,
                        \DailyTasks::SEVEN_UP,
                        \DailyTasks::POWER_OF_UP
                    );
                    foreach ($dts as $dtid) {
                        $dt = $this->dailyTaskManager->getDailyTask($ua->account_id, $dtid);
                        $dt->current += $delta;
                        $dt->save();
                    }
                }
            }
            // biggest win
            if ($data['maxWin'] > $ua->biggest_win) {
                $ua->biggest_win = $data['maxWin'];
                $this->getEventsManager()->fire('achievement:maxWinChanged', $ua, $data['maxWin']);
            }
            // max bet
            if ($data['maxBet'] > $ua->biggest_bet) {
                $ua->biggest_bet = $data['maxBet'];
                $this->getEventsManager()->fire('achievement:maxBetChanged', $ua, $data['maxBet']);
            }
            // 判断类型
            switch ($data['WLR']) {
                case 'w':
                case 'l':
                    break;
                case 'N':
                    
                    // $ua->chip += $data['fee'];
                    $this->getEventsManager()->fire('table:exitSitAndGo', $ua, $data);
                    break;
                case 'R1':
                case 'R2':
                case 'R3':
                    $this->getEventsManager()->fire('table:exitShootout', $ua, $data);
                    break;
            }
            if ($data['level'] != $ua->level) {
                // 等级变化
                $old_level = $ua->level;
                $new_level = $data['level'];
                $ua->level = $new_level;
                //
                $ac = $this->achievementManager->getAchievement($ua->account_id, 'Level');
                $ac->current = $ua->level;
                $ac->save();
                // 升级增加的VIP积分
                for ($lv = ($old_level + 1); $lv <= $new_level; ++ $lv) {
                    $ua->vip_score += $lv; // 此处没有倍乘
                }
                
                if (! empty($ua->ref_id)) {
                    $counter = array(
                        3,
                        10,
                        20,
                        30
                    );
                    
                    foreach ($counter as $c) {
                        if ($c > $old_level && $c <= $new_level) {
                            $name = 'InviteCounter' . $c;
                            unset($ac);
                            $ac = $this->achievementManager->getAchievement($ua->ref_id, $name);
                            $ac->current += 1;
                            $ac->save();
                            break;
                        }
                    }
                }
            }
            if ($data['continueWinTimes'] > 0) {
                $dt = $this->dailyTaskManager->getDailyTask($ua->account_id, \DailyTasks::WINNING);
                if ($dt->current < $data['continueWinTimes']) {
                    $dt->current = $data['continueWinTimes'];
                    $dt->save();
                }
            }
            $ua->exp = $data['exp'];
            
            if ($data['WLR'] == 'w' || $data['WLR'] == 'l') {
                $ua->threshold = $data['threshold'];
                $ua->thc = $data['thc'];
            }
            
            $ua->save();
            // $this->logger->notice(sprintf('chip: %d diamond: %d', $ua->chip, $ua->diamond));
            return;
        }

        public function enterTable(\Phalcon\Events\Event $event, \UserAccounts $ua, array $data = null)
        {
            // if ($data['WLR'] == 'w' || $data['WLR'] == 'l')
            // $this->eventsManager->fire('achievement:maxBuyInChanged', $ua, $data['buyIn']);
            // $this->logger->notice('user [ id: ' . $ua->account_id . ' ] [ name: ' . $ua->nickname . ' ] enter table. table data ' . var_export($data, true));
            // $this->logger->notice(sprintf('chip: %d diamond: %d', $ua->chip, $ua->diamond));
            if ($this->logger->getLogLevel() >= \Phalcon\Logger::NOTICE) {
                $log_data = $data;
                $unset_keys = array(
                    'c0',
                    'c1',
                    'c2',
                    'c3',
                    'c4',
                    'c5',
                    'capacity',
                    'speed',
                    'rank'
                );
                foreach ($unset_keys as $k) {
                    unset($log_data[$k]);
                }
                $data_append = array(
                    'chip' => $ua->chip,
                    'diamond' => $ua->diamond,
                    'KEYWORD' => 'ENTER_TABLE'
                );
                $log_data = array_merge($log_data, $data_append);
                ksort($log_data);
                $log_str = json_encode($log_data);
                $this->logger->notice($log_str);
            }
            return;
        }

        public function exitSitAndGo(\Phalcon\Events\Event $event, \UserAccounts $ua, array $data = null)
        {
            if ($data['rank'] > 1 && $data['rank'] < 4) {
                // TODO 有排名
            }
        }

        public function exitShootout(\Phalcon\Events\Event $event, \UserAccounts $ua, array $data = null)
        {
            if ($data['rank'] > 0 && $data['rank'] < 4) {
                // TODO 有排名
            }
            if ($data['rank'] == 10) {
                return;
            }
            if ($data['rank'] == 1) {
                $ac = $this->achievementManager->getAchievement($ua->account_id, 'SOT1stCounter');
                $ac->current += 1;
                $ac->save();
                $ua->sot_stage += 1;
                if ($ua->sot_stage > 3) {
                    $ua->sot_stage = 1;
                }
            } else {
                $ua->sot_stage = 1;
            }
        }

        public function tableTipsSent(\Phalcon\Events\Event $event, \UserAccounts $ua, $tip = 0)
        {
            $this->logger->info(sprintf('user [ id: %s ] [ name: %s] send tips %s', $ua->account_id, $ua->nickname, $tip));
            if ($tip > 0) {
                $ac = $this->achievementManager->getAchievement($ua->account_id, 'TipsCounter');
                $ac->current += 1;
                $ac->save();
            }
        }

        public function tableGiftSent(\Phalcon\Events\Event $event, \UserAccounts $ua, array $gift)
        {
            $this->logger->info(sprintf('user [ id: %s ] [ name: %s] send gift [ gift: %s ] [ cost: %s ] [ type: %s ]', $ua->account_id, $ua->nickname, $gift['code'], $gift['cost'], (intval($gift['gift']['index'][0])) < 2 ? 'chip' : 'diamond'));
            $ac = $this->achievementManager->getAchievement($ua->account_id, 'GiftCounter');
            $ac->current += $gift['code'][3];
            $ac->save();
        }
    }
}