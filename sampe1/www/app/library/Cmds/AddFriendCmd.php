<?php
namespace Cmds;

class AddFriendCmd extends Cmd
{

    const CMD_NAME = 'AddFriend';

    public static function defaultData()
    {
        return array(
            'who' => ''
        );
    }

    protected function do_execute()
    {
        $who = $this->data['who'];
        if (empty($who)) {
            $this->ret['result']['errno'] = \Errors::EMPTY_DATA;
            return;
        }
        
        if ($who == $this->account_id) {
            $this->ret['result']['errno'] = \Errors::OP_DENIED;
            return;
        }
        if (\FakeUserInfoContainer::isFakeUser($who)) {
            $key = \SessionManager::getSessionKey($this->account_id);
            $value = $this->redis->hget($key, 'fu:' . $who);
            if ($value) {
                $this->ret['result']['errno'] = $this->ret['result']['errno'] = \Errors::ALREADY_REQUESTED;
            }
            $this->redis->hset($key, 'fu:' . $who, 1);
            return;
        }
        // 刷新
        $request_timeout = \Phalcon\DI::getDefault()->get('config')->friends->request_timeout;
        $date = new \DateTime();
        $date->sub($request_timeout);
        $time = $date->getTimestamp();
        
        $query = new \Phalcon\Mvc\Model\Query('UPDATE Friends SET status = :status: WHERE when < :when: AND status = :pre_status:', \Phalcon\DI::getDefault());
        $query->execute(array(
            'status' => \Friends::STATUS_NONE,
            'pre_status' => \Friends::STATUS_REQUESTED,
            'when' => $time
        ));
        //
        $friend = \Friends::findFirst(array(
            'src_id = :src_id: and dst_id = :dst_id:',
            'bind' => array(
                'src_id' => $this->account_id,
                'dst_id' => $who
            )
        ));
        
        if ($friend) {
            // 判断状态
            switch ($friend->status) {
                case \Friends::STATUS_ADDED:
                    $this->ret['result']['errno'] = \Errors::ALREADY_ADDED;
                    return;
                    break;
                case \Friends::STATUS_REQUESTED:
                    $this->ret['result']['errno'] = \Errors::ALREADY_REQUESTED;
                    return;
                    break;
                case \Friends::STATUS_REJECTED:
                    break;
                case \Friends::STATUS_DELETED:
                    break;
                case \Friends::STATUS_NONE:
                    break;
                default:
                    break;
            }
        }
        // 查找对方
        if (\FakeUserInfoContainer::isFakeUser($who)) { // Silly Player
            return;
        }
        $ua = \UserAccounts::findFirstByAccountId($who);
        if (! $ua) {
            $this->ret['result']['errno'] = \Errors::USER_NOT_FOUND;
            return;
        }
        
        $ret = \Friends::updateStatus($this->account_id, $who, 'local', array(
            'status' => \Friends::STATUS_REQUESTED,
            'when' => time()
        ));
    }
}