<?php
namespace Cmds;

class ListAchievementsCmd extends Cmd
{

    const CMD_NAME = 'ListAchievements';

    protected function do_execute()
    {
        $am = $this->achievementManager;
        $this->ret['result']['data'] = $am->getValues($this->account_id);
    }
}