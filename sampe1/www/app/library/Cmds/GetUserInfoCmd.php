<?php
namespace Cmds;

class GetUserInfoCmd extends Cmd
{

    const CMD_NAME = 'GetUserInfo';

    public static function defaultData()
    {
        return array(
            'account_id' => '',
            'with_achievements' => false
        );
    }

    protected function do_execute()
    {
        if (empty($this->data['account_id'])) {
            $this->ret['result']['errno'] = \Errors::EMPTY_DATA;
            return;
        }
        if (\FakeUserInfoContainer::isFakeUser($this->data['account_id'])) {
            $user = $this->fakeUserInfoContainer->getUserInfo($this->data['account_id']);
            $this->ret['result'] = array_merge($this->ret['result'], $user);
            if ($this->data['with_achievements'] == true) {
                $this->ret['result']['achievements'] = array();
            }
            return;
        }
        $user = \UserAccounts::findFirstByAccountId($this->data['account_id']);
        if (! $user) {
            $this->ret['result']['errno'] = \Errors::USER_NOT_FOUND;
            return;
        }
        $this->ret['result'] = array_merge($this->ret['result'], $user->getInfoArray());
        $this->ret['result']['last_lottery'] = $user->last_lottery;
        $this->ret['result']['has_free_lottery'] = ! (date('Y-m-d') == $user->last_lottery);
        if ($this->data['with_achievements'] == true) {
            $this->ret['result']['achievements'] = $this->achievementManager->getValues($this->data['account_id']);
        }
        return;
    }
}