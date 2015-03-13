<?php
namespace Cmds
{

    class ConfirmRefererCmd extends Cmd
    {

        const CMD_NAME = 'ConfirmReferer';

        public static function defaultData()
        {
            return array(
                'who' => ''
            );
        }

        protected function do_execute()
        {
            $me = $this->getMe();
            if (! empty($me->ref_id) || $me->account_id == $this->data['who']) {
                $this->ret['result']['errno'] = \Errors::OP_DENIED;
                return;
            }
            if (empty($this->data['who'])) {
                $this->ret['result']['errno'] = \Errors::EMPTY_DATA;
                return;
            }
            $referer = \UserAccounts::findFirst(array(
                'account_id = :id:',
                'bind' => array(
                    'id' => $this->data['who']
                )
            ));
            if (! $referer) {
                $this->ret['result']['errno'] = \Errors::USER_NOT_FOUND;
                return;
            }
            if ($referer->ref_id == $this->account_id) {
                $this->ret['result']['errno'] = \Errors::OP_DENIED;
                return;
            }
            $me->ref_id = $referer->account_id;
            \Friends::add($me->ref_id, $me->account_id, 'local');
            \Friends::add($me->account_id, $me->ref_id, 'local');
            $me->chip += 50000;
            $me->save();
            $this->ret['result']['chip'] = $me->chip;
        }
    }
}