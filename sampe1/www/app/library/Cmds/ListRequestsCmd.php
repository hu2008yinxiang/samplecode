<?php
namespace Cmds;

class ListRequestsCmd extends Cmd
{

    /**
     *
     * @see \Cmds\Cmd::CMD_NAME
     */
    const CMD_NAME = 'ListRequests';

    protected function do_execute()
    {
        $di = \Phalcon\DI::getDefault();
        $request_timeout = $di->get('config')->friends->request_timeout;
        $date = new \DateTime();
        $date->sub($request_timeout);
        $time = $date->getTimestamp();
        
        $query = new \Phalcon\Mvc\Model\Query('UPDATE Friends SET status = :status: WHERE when < :when: AND status = :pre_status:', $di);
        $query->execute(array(
            'status' => \Friends::STATUS_NONE,
            'pre_status' => \Friends::STATUS_REQUESTED,
            'when' => $time
        ));
        $requests = \Friends::find(array(
            'dst_id = :dst_id: and status = :status:',
            'bind' => array(
                'dst_id' => $this->account_id,
                'status' => \Friends::STATUS_REQUESTED
            )
        ));
        $this->ret['result']['data'] = array();
        foreach ($requests as $f) {
            $this->ret['result']['data'][] = $f->toArray();
        }
        // $this->ret['result']['data'] = $requests->toArray();
    }
}