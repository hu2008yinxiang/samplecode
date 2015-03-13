<?php
namespace Cmds;

class FailedToEnterTableCmd extends Cmd
{

    const CMD_NAME = 'FailedToEnterTable';

    public static function defaultData()
    {
        return array(
            'session' => ''
        );
    }

    protected function do_execute()
    {
        $session = $this->data['session'];
        if (empty($session)) {
            $this->ret['result']['errno'] = \Errors::EMPTY_DATA;
            return;
        }
        \SessionManager::exitTable($session, false);
        $me = $this->getMe();
        $this->ret['result']['chip'] = $me->chip;
        $this->ret['result']['diamond'] = $me->diamond;
    }
}