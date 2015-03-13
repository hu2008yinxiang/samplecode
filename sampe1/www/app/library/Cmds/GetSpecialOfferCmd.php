<?php
namespace Cmds;

class GetSpecialOfferCmd extends Cmd
{

    const CMD_NAME = 'GetSpecialOffer';

    protected function do_execute()
    {
        //$data = $this->specialOfferManager->getSpecialOfferInfo($this->account_id);
        //$this->ret['result'] = array_merge($this->ret['result'], $data);
        $this->ret['result']['has'] = false;
    }
}