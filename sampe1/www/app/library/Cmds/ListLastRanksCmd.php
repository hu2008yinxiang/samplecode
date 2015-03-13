<?php
namespace Cmds
{

    class ListLastRanksCmd extends Cmd
    {

        const CMD_NAME = 'ListLastRanks';

        protected function do_execute()
        {
            $gr = $this->ranksManager->getLastWeekGlobal($this->account_id);
            $fr = $this->ranksManager->getLastWeekFriend($this->account_id);
            //$this->ret['result']['has_global'] = ($gr != false);
            //$this->ret['result']['has_friend'] = ($fr != false);
            $this->ret['result']['global'] = $gr;
            $this->ret['result']['friend'] = $fr;
        }
    }
}