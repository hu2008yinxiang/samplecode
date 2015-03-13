<?php
namespace Listeners
{

    class UserListener extends \Phalcon\Mvc\User\Component
    {

        public function loginSuccess(\Phalcon\Events\Event $event, \UserAccounts $ua, array $data = null)
        {
            // $this->logger->info('user [ id: ' . $ua->account_id . ' ] [ name: ' . $ua->nickname . ' ] login successfully.');
            $today = date('Y-m-d');
            $login_last = date('Y-m-d', $ua->login_last);
            $yesterday = date('Y-m-d', strtotime('yesterday'));
            $login_combo = $ua->login_combo;
            if ($login_last == $today) {
                // skip
            } elseif ($login_last == $yesterday) {
                $ua->login_combo += 1;
            } else {
                $ua->login_combo = 1;
            }
            if ($login_combo != $ua->login_combo) {
                // 触发连续登录
                if ($ua->login_combo > 7) {
                    $ua->login_combo = 7;
                }
                $this->eventsManager->fire('user:loginComboChanged', $ua);
            }
            $ua->login_last = time();
            $ua->save();
            // 添加自己为好友
            \Friends::updateStatus($ua->account_id, $ua->account_id, 'local', array(
                'status' => \Friends::STATUS_DELETED,
                'when' => time(),
                'last_gift_day' => date('Y-m-d')
            ));
            // 更新自己排名状态【防止出现自己排名查询不到】
            \Ranks::loadNow($ua->account_id);
            $sk = \SessionManager::getSessionKey($ua->account_id);
            // 上次没命中
            if ($this->redis->hget($sk, 'so') == date('Ymd')) {
                $this->redis->hset($sk, 'so', 'login');
            }
        }

        public function registerSuccess(\Phalcon\Events\Event $event, \UserAccounts $ua, array $data = null)
        {
            // $this->logger->info('user [ id: ' . $ua->account_id . ' ] [ name: ' . $ua->nickname . ' ] registered successfully.');
            \Ranks::loadNow($ua->account_id);
            \Friends::updateStatus($ua->account_id, $ua->account_id, 'local', array(
                'status' => \Friends::STATUS_DELETED,
                'when' => time(),
                'last_gift_day' => date('Y-m-d')
            ));
            $this->specialOfferManager->setRegisterSpecialOffer($ua->account_id);
        }

        public function chipChanged(\Phalcon\Events\Event $event, \UserAccounts $ua, $data = null)
        {
            $snapShot = $ua->getSnapshotData();
            if ($ua->chip < 40000 && $snapShot['chip'] > 40000) {
                $this->specialOfferManager->setAssetsDrain($ua->account_id);
            }
            $ac = $this->achievementManager->getAchievement($ua->account_id, 'AssetsPeak');
            if ($ua->chip > $ac->current) {
                $ac->current = $ua->chip;
                $ac->save();
            }
        }
    }
}