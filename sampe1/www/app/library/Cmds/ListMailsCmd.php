<?php
namespace Cmds;

class ListMailsCmd extends Cmd
{

    const CMD_NAME = 'ListMails';

    protected function do_execute()
    {
        $me = $this->getMe();
        $cond = array(
            'type = :type: AND when > :when:',
            'bind' => array(
                'type' => \Mails::TYPE_TEXT,
                'when' => time() - 7 * 24 * 60 * 60
            ),
            'order' => 'when DESC',
            'limit' => 30
        );
        $data = array();
        foreach ($me->getMails($cond) as $mail) {
            $info = $mail->toArray();
            $sender = $mail->getSender();
            if ($sender) {
                $info['sender'] = $sender->nickname;
                // $info ['sender_id'] = $sender->account_id;
            } else {
                $info['sender'] = \Mails::getSenderName($gift->src_id);
            }
            $data[] = $info;
        }
        $this->ret['result']['data'] = $data;
    }
}
