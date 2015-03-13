<?php
namespace Cmds;

class SitDownCmd extends Cmd
{

    const CMD_NAME = 'SitDown';

    public static function defaultData()
    {
        return array(
            'key' => '',
            'buyIn' => 0,
            'accountId' => '',
            'bigBlind' => 0,
            'session' => ''
        );
    }

    protected function do_execute()
    {
        $key = $this->data['key'];
        $session = $this->data['session'];
        $account_id = $this->data['accountId'];
        $buyIn = $this->data['buyIn'];
        $bigBlind = $this->data['bigBlind'];
        $sk = \SessionManager::getTableSessionKey($session);
        $this->logger->info(sprintf('session_key:%s', $sk));
        if (empty($key) || empty($account_id) || $key != $this->getDI()->get('config')->app->minaKey) {
            $this->ret['result']['errno'] = \Errors::OP_DENIED;
            return;
        }
        $redis = $this->redis;
        $keys = array(
            'orgBuyIn',
            'orgFee',
            'buyIn',
            'fee'
        );
        $orgInfo = array_combine($keys, $redis->hmget($sk, $keys));
        $orgInfo = array_merge(array(
            'orgBuyIn' => 0,
            'orgFee' => 0,
            'buyIn' => 0,
            'fee' => 0
        ), $orgInfo);
        // 结算
        $ua = \UserAccounts::findFirstByAccountId($account_id);
        $chip_delta = $orgInfo['buyIn'] - ($orgInfo['orgBuyIn'] + $orgInfo['orgFee']);
        $this->logger->info(sprintf('[ event:%s:%s:%s ] chip_delta:%s orgBuyIn:%s orgFee:%s buyIn:%s chip:%s', 'SIT_DOWN', $ua->account_id, $ua->nickname, $chip_delta, $orgInfo['orgBuyIn'], $orgInfo['orgFee'], $orgInfo['buyIn'], $ua->chip));
        $ua->chip += $chip_delta; // 扣除上次买入
        $ua->save();
        // 重置 buyIn 和 orgBuyIn orgFee
        $redis->hmset($sk, array(
            'buyIn' => 0,
            'orgBuyIn' => 0,
            'orgFee' => 0,
            'fee' => 0
        ));
        $tableList = \SessionManager::getTableList(false);
        if (! isset($tableList[$bigBlind]) || $buyIn % $bigBlind != 0) {
            $this->ret['result']['errno'] = \Errors::UNSUPPORTED_BUYIN;
            return;
        }
        list ($min, $max) = $tableList[$bigBlind];
        if ($buyIn > $max || $buyIn < $min) {
            $this->ret['result']['errno'] = \Errors::UNSUPPORTED_BUYIN;
            return;
        }
        if ($ua->chip < $buyIn) {
            $this->ret['result']['errno'] = \Errors::CHIP_NOT_ENOUGH;
            return;
        }
        $redis->hmset($sk, array(
            'buyIn' => $buyIn,
            'orgBuyIn' => $buyIn,
            'orgFee' => 0,
            'fee' => 0,
            'cog' => $ua->chip - $buyIn
        )); // 填入最新买入
                // $ua->chip -= $buyIn;
                // $ua->save();
    }
}