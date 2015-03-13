<?php
namespace Cmds;

class DeleteFriendCmd extends Cmd
{

    const CMD_NAME = 'DeleteFriend';

    public static function defaultData()
    {
        return array(
            'who' => ''
        );
    }

    protected function do_execute()
    {
        $who = $this->data['who'];
        $friend = \Friends::findFirst(array(
            'src_id = :src_id: AND dst_id = :dst_id: AND src = :type: AND status = :status:',
            'bind' => array(
                'src_id' => $this->account_id,
                'dst_id' => $who,
                'type' => 'local',
                'status' => \Friends::STATUS_ADDED
            )
        ));
        if (! $friend) {
            $this->ret['result']['errno'] = \Errors::OP_DENIED;
            return;
        }
        $friend->status = \Friends::STATUS_DELETED;
        $friend->when = time();
        $friend->save();
    }
}