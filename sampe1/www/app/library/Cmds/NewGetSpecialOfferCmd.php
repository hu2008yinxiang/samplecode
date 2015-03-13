<?php
namespace Cmds;

class NewGetSpecialOfferCmd extends Cmd
{

    const CMD_NAME = 'NewGetSpecialOffer';

    protected function do_execute()
    {
        $data = $this->specialOfferManager->getSpecialOfferInfo($this->account_id);
        $this->ret['result'] = array_merge($this->ret['result'], $data);
        $this->ret['result']['has'] = ! empty($data);
    }
}