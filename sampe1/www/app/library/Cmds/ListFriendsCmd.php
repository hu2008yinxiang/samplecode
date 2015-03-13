<?php
namespace Cmds;

class ListFriendsCmd extends Cmd
{

    const CMD_NAME = 'ListFriends';

    protected function do_execute()
    {
        $friends = \Friends::find(array(
            'src_id = :src_id: and status = :status:',
            'bind' => array(
                'src_id' => $this->account_id,
                'status' => \Friends::STATUS_ADDED
            )
        ));
        $tmp = array();
        foreach ($friends as $friend) {
            // 已有并且当前为本地用户 跳过
            if (isset($tmp[$friend->dst_id]) && $friend->src == 'local') {
                continue;
            }
            $tmp[$friend->dst_id] = array_merge($friend->toArray(), \UserAccounts::findFirstByAccountId($friend->dst_id)->getInfoArray());
        }
        $this->ret['result']['data'] = array_values($tmp);
    }
}