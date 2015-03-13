<?php
namespace Cmds;

class RenameCmd extends Cmd
{

    const CMD_NAME = 'Rename';

    public static function defaultData()
    {
        return array(
            'name' => ''
        );
    }

    protected function do_execute()
    {
        $this->logger->notice('[ id: ' . $this->account_id . ' ] ' . 'rename  [ newName: ' . $this->data['name'] . ' ]');
        $this->data['name'] = trim($this->data['name']);
        if (empty($this->data['name']) || mb_strlen($this->data['name'], 'UTF-8') > 20) {
            $this->ret['result']['errno'] = \Errors::BAD_NAME;
            return;
        }
        $ua = $this->getMe();
        if ($ua->type != 'local') {
            $this->ret['result']['errno'] = \Errors::OP_DENIED;
            return;
        }
        $ua->nickname = $this->data['name'];
        $ua->save();
        $this->ret['result']['name'] = $ua->nickname;
    }
}