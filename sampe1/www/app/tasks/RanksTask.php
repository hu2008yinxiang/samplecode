<?php

class RanksTask extends \Phalcon\CLI\Task
{

    public function mainAction(array $param = null)
    {}

    public function updateAction(array $params = null)
    {
        error_log('updating ranks ...');
        $this->ranksManager->updateNow();
        error_log('ranks updated.');
    }

    public function settleAction(array $aprams = null)
    {
        error_log('settling ranks ...');
        $this->ranksManager->playerSettle();
        error_log('ranks settled.');
    }
}