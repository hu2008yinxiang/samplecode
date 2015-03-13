<?php
namespace Cmds;

class RemoveGiftCmd extends Cmd
{

    const CMD_NAME = 'RemoveGift';

    public static function defaultData()
    {
        return array(
            'gift_id' => '',
            'remove_all' => false
        );
    }

    protected function do_execute()
    {
        $remove_all = $this->data['remove_all'];
        $gift_id = $this->data['gift_id'];
        $remove_all = \Utils::bool($remove_all);
        $me = $this->getMe();
        $cond = array(
            'type = :type: AND status = :status: AND mail_id = :gift_id:',
            'bind' => array(
                'type' => \Mails::TYPE_GIFT,
                'status' => \Mails::STATUS_UNREAD,
                'gift_id' => $gift_id
            )
        );
        if ($remove_all) {
            $cond = array(
                'type = :type: AND status = :status:',
                'bind' => array(
                    'type' => \Mails::TYPE_GIFT,
                    'status' => \Mails::STATUS_UNREAD
                )
            );
        }
        foreach ($me->getMails($cond) as $gift) {
            $gift->status = \Mails::STATUS_READ;
            $gift->save();
        }
    }
}