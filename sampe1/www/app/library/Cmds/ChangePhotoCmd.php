<?php
namespace Cmds;

class ChangePhotoCmd extends Cmd
{

    const CMD_NAME = 'ChangePhoto';

    public static function defaultData()
    {
        return array(
            'photo' => - 1
        );
    }

    protected function do_execute()
    {
        $photo = intval($this->data['photo']);
        $me = $this->getMe();
        if ($photo > \UserAccounts::IN_APP_PHOTO_COUNT || $photo < 0 || $me->type != 'local') {
            $this->ret['result']['errno'] = \Errors::OP_DENIED;
            return;
        }
        $me->photo = $photo;
        $me->save();
        $this->ret['result']['photo'] = $me->photo;
    }
}