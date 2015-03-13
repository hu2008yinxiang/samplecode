<?php
namespace Cmds
{

    class ListShopCmd extends Cmd
    {

        const CMD_NAME = 'ListShop';

        public static function defaultData()
        {
            return array();
        }

        protected function do_execute()
        {
            $raw = $this->shopManager->getShopConfig();
            $this->ret['result']['data'] = array(); // $this->shopManager->getShopConfig();
            foreach ($raw as $item) {
                $sale = number_format($item['sale'], 1);
                if (! isset($this->ret['result']['data'][$sale])) {
                    $this->ret['result']['data'][$sale] = array();
                }
                $this->ret['result']['data'][$sale][] = $item;
            }
            $me = $this->getMe();
            $this->ret['result']['referered'] = empty($me->ref_id);
            $this->ret['result']['monthly_offer'] = $this->shopManager->getMonthlyOffer();
            $this->ret['result']['has_monthly_offer'] = $this->shopManager->hasMonthlyOffer($this->account_id);
            $this->ret['result']['monthly_offer_end'] = $this->shopManager->loadMonthlyOffer($this->account_id)->value['end']->format('Y-m-d');
            return;
        }
    }
}