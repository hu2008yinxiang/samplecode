<?php
namespace Cmds;

class EnterShootoutCmd extends Cmd
{

    const CMD_NAME = 'EnterShootout';

    public function do_execute()
    {
        // $extra = \Extras::load($this->account_id, \Extras::LAST_SOT_ROUND, 1);
        $me = $this->getMe();
        $round = $me->sot_stage;
        if ($round != 1 && $round != 2 && $round != 3)
            $round = 1;
        $data = array();
        
        if ($me->chip < 2000) {
            $this->ret['result']['errno'] = \Errors::CHIP_NOT_ENOUGH;
            return;
        }
        //$me->chip -= 2000;
        $me->save();
        $data['buyIn'] = 2000;
        $data['WLR'] = 'R' . $round;
        $data['speed'] = 'n';
        $data['capacity'] = 9;
        $data['bigBlind'] = 0;
        $session = \SessionManager::genTableSession($this->account_id);
        \SessionManager::fillData($data, $me);
        \SessionManager::enterTable($session, $data);
        $this->ret['result']['session'] = $session;
        $this->ret['result']['server'] = \SessionManager::getTableServer($me->account_id);
        $this->ret['result']['chip'] = $me->chip - 2000;
        $this->ret['result']['diamond'] = $me->diamond;
        $this->ret['result']['table'] = array(
            'buyIn' => $data['buyIn']
        );
    }
}