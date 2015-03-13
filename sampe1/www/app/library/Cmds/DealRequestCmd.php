<?php
namespace Cmds;

class DealRequestCmd extends Cmd
{

    const CMD_NAME = 'DealRequest';

    public static function defaultData()
    {
        return array(
            'action' => '',
            'who' => ''
        );
    }

    protected function do_execute()
    {
        if (empty($this->data['action']) || empty($this->data['who']) || $this->account_id == $this->data['who']) {
            $this->ret['result']['errno'] = \Errors::OP_DENIED;
            return;
        }
        $new_status = \Friends::STATUS_REQUESTED;
        switch ($this->data['action']) {
            case 'accept':
                $new_status = \Friends::STATUS_ADDED;
                \Friends::add($this->account_id, $this->data['who'], 'local');
                \Friends::add($this->data['who'], $this->account_id, 'local');
                return;
                break;
            case 'reject':
            case 'ignore':
                $new_status = \Friends::STATUS_REJECTED;
                break;
            case 'delete':
                $new_status = \Friends::STATUS_NONE;
                break;
            case 'read':
                
                break;
            default:
                break;
        }
        $query = new \Phalcon\Mvc\Model\Query('UPDATE Friends SET status = :new_status:, when = :when: WHERE src_id = :src_id: AND dst_id = :dst_id: AND status = :status:', \Phalcon\DI::getDefault());
        $query->execute(array(
            'new_status' => $new_status,
            'when' => time(),
            'src_id' => $this->data['who'],
            'dst_id' => $this->account_id,
            'status' => \Friends::STATUS_REQUESTED
        ));
    }
}