<?php
namespace Cmds;

class MissedCmd extends Cmd
{

    protected function do_execute()
    {
        $this->ret['result']['cmd'] = $this->data['cmd'];
        $this->ret['result']['errno'] = \Errors::UNKNOWN_CMD;
    }
}