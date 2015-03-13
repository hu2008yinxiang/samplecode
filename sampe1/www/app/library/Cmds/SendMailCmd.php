<?php
namespace Cmds;

class SendMailCmd extends Cmd
{

    const CMD_NAME = 'SendMail';

    public static function defaultData()
    {
        return array(
            'to' => '',
            'content' => ''
        );
    }

    protected function do_execute()
    {
        $to = $this->data['to'];
        $content = $this->data['content'];
        $content = trim($content);
        if (empty($content) || empty($to)) {
            $this->ret['result']['errno'] = \Errors::EMPTY_DATA;
            return;
        }
        if ($this->account_id == $to) {
            $this->ret['result']['errno'] = \Errors::OP_DENIED;
            return;
        }
        if (\UserAccounts::findFirstByAccountId($to) == false) {
            $this->ret['result']['errno'] = \Errors::USER_NOT_FOUND;
            return;
        }
        \Mails::newMail($this->account_id, $to, \Mails::TYPE_TEXT, $content);
    }
}