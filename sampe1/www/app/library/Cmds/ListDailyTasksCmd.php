<?php
namespace Cmds
{

    class ListDailyTasksCmd extends Cmd
    {

        const CMD_NAME = 'ListDailyTasks';

        protected function do_execute()
        {
            $this->ret['result']['data'] = $this->dailyTaskManager->getValues($this->account_id);
        }
    }
}