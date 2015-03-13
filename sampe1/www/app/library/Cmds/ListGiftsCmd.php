<?php
namespace Cmds;

class ListGiftsCmd extends Cmd
{

    const CMD_NAME = 'ListGifts';

    protected function do_execute()
    {
        $gifts = \Mails::find(array(
            'dst_id = :me: AND status = :status: AND type = :type:',
            'bind' => array(
                'me' => $this->account_id,
                'status' => \Mails::STATUS_UNREAD,
                'type' => \Mails::TYPE_GIFT
            )
        ));
        $data = array();
        foreach ($gifts as $gift) {
            $gift_data = $gift->toArray();
            $sender = $gift->getSender();
            if ($sender) {
                $gift_data['sender'] = $sender->nickname;
            } else {
                $gift_data['sender'] = \Mails::getSenderName($gift->src_id);
            }
            $data[] = $gift_data;
        }
        $this->ret['result']['data'] = $data;
    }
}