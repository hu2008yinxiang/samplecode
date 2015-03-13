<?php
namespace Cmds;

class ExitTableCmd extends Cmd
{

    const CMD_NAME = 'ExitTable';

    protected function do_execute()
    {
        $session = $this->data['session'];
        $key = $this->data['key'];
        $ikey = $this->getDI()->get('config')->app->minaKey;
        if ($ikey != $key || empty($ikey) || empty($key) || empty($session)) {
            $this->ret['result']['errno'] = \Errors::OP_DENIED;
            return;
        }
        \SessionManager::exitTable($session);
    }
}