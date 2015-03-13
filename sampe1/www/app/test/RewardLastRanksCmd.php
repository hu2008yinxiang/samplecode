<?php
return;
// $raw_data['cmds'] = array();
$rank = Ranks::load($raw_data['account_id']);
$raw_data['cmds'][] = array(
    'cmd' => \Cmds\RewardLastRanksCmd::CMD_NAME,
    'serial' => $raw_data['account_id'] + $rank->getLastWin() + 1
);