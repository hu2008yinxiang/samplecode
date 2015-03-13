<?php
namespace Cmds;

class ListShowBoxCmd extends Cmd
{

    const CMD_NAME = 'ListShowBox';

    protected function do_execute()
    {
        
        $result = $this->showBoxManager->getShowBox();
        $me = $this->getMe();
        $this->ret['result']['referered'] = empty($me->ref_id);
        $this->ret['result']['monthly_offer'] = $this->shopManager->getMonthlyOffer();
        $this->ret['result']['has_monthly_offer'] = $this->shopManager->hasMonthlyOffer($this->account_id);
        $this->ret['result']['monthly_offer_end'] = $this->shopManager->loadMonthlyOffer($this->account_id)->value['end']->format('Y-m-d');
        
        $this->ret['result']['items'] = $result;
    }
}