<?php
namespace Cmds;

class DealMailCmd extends Cmd
{

    const CMD_NAME = 'DealMail';

    public static function defaultData()
    {
        return array(
            'mail_id' => '',
            'action' => ''
        );
    }

    protected function do_execute()
    {
        $mail_id = $this->data['mail_id'];
        $action = $this->data['action'];
        $mail = \Mails::findFirstByMailId($mail_id);
        // mail 不存在 或者不是发给自己的
        if (empty($action) || $mail == false || $mail->dst_id != $this->account_id) {
            $this->ret['result']['errno'] = \Errors::OP_DENIED;
            return;
        }
        switch ($action) {
            case 'delete':
                $mail->delete();
                break;
            case 'read':
                $mail->status = \Mails::STATUS_READ;
                $mail->save();
                break;
            default:
                $this->ret['result']['errno'] = \Errors::OP_DENIED;
                return;
                break;
        }
    }
}