<?php
namespace Cmds
{

    /**
     *
     * @author 继续
     *         小红点
     *         More : Loterry News
     *         Shop
     *         Task : Achievement Task
     *         Mail : Mail Gift Request
     */
    class ListHotSpotCmd extends Cmd
    {

        const CMD_NAME = 'ListHotSpot';

        public static function defaultData()
        {
            return array();
        }

        protected function do_execute()
        {
            $me = $this->getMe();
            $this->ret['result']['more'] = $me->last_lottery == date('Y-m-d') ? 0 : 1;
            $this->ret['result']['shop'] = $this->shopManager->hasSale();
            $this->ret['result']['task'] = 0;
            $this->ret['result']['mail'] = \Mails::count(array(
                'dst_id = :dst_id: and status = :status:',
                'bind' => array(
                    'dst_id' => $this->account_id,
                    'status' => \Mails::STATUS_UNREAD
                )
            )) || (\Friends::count(array(
                'dst_id = :dst_id: AND status = :status:',
                'bind' => array(
                    'dst_id' => $this->account_id,
                    'status' => \Friends::STATUS_REQUESTED
                )
            ))) ? 1 : 0;
        }
    }
}