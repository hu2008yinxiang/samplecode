<?php
namespace Cmds;

class ListRanksCmd extends Cmd
{

    const CMD_NAME = 'ListRanks';

    public static function defaultData()
    {
        return array(
            'type' => 'friends'
        );
    }

    protected function do_execute()
    {
        $types = array(
            'friends',
            'global'
        );
        if (! in_array($this->data['type'], $types)) {
            $this->ret['result']['errno'] = \Errors::EMPTY_DATA;
            return;
        }
        $method = 'do_execute_' . $this->data['type'];
        if (method_exists($this, $method)) {
            $this->$method();
            return;
        }
        $this->ret['result']['errno'] = \Errors::OP_DENIED;
    }

    protected function do_execute_friends()
    {
        $data = \Ranks::getRankList($this->account_id);
        $this->ret['result']['data'] = $data;
    }

    protected function do_execute_global()
    {
        $data = \Ranks::getRankList($this->account_id, true);
        $this->ret['result']['data'] = $data;
    }
}