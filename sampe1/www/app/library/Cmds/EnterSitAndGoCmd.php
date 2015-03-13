<?php
namespace Cmds;

class EnterSitAndGoCmd extends Cmd
{

    const CMD_NAME = 'EnterSitAndGo';

    public static function defaultData()
    {
        return array(
            'buyIn' => 0
        );
    }

    protected function do_execute()
    {
        $buyIn = $this->data['buyIn'];
        $buyIns = $this->config->sng->buyIns->toArray();
        // var_export($buyIns->toArray());
        if (! array_key_exists($buyIn, $buyIns)) {
            $this->ret['result']['errno'] = \Errors::EMPTY_DATA;
            return;
        }
        $fee = $buyIns[$buyIn];
        $cost = $buyIn + $fee;
        $me = $this->getMe();
        if ($cost > $me->chip) {
            $this->ret['result']['errno'] = \Errors::CHIP_NOT_ENOUGH;
            return;
        }
        // $me->chip -= $cost;
        $me->save();
        $data['buyIn'] = $buyIn;
        $data['WLR'] = 'N';
        $data['speed'] = 'n';
        $data['capacity'] = 9;
        $data['accountId'] = $me->account_id;
        $session = \SessionManager::genTableSession($data['accountId']);
        $data['bigBlind'] = 0;
        $data['fee'] = $fee;
        
        \SessionManager::fillData($data, $me);
        // $data['tournamentType'] = 'N';
        \SessionManager::enterTable($session, $data);
        $this->ret['result']['session'] = $session;
        $this->ret['result']['server'] = \SessionManager::getTableServer($me->account_id);
        $this->ret['result']['chip'] = $me->chip - $data['buyIn'] - $data['fee'];
        $this->ret['result']['diamond'] = $me->diamond;
        $this->ret['result']['table'] = array(
            'buyIn' => $data['buyIn']
        );
    }
}