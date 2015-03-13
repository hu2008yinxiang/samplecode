<?php
return;

$raw_data['role'] = 'mina';
$raw_data['account_id'] = '100433753';
$raw_data['cmds'] = array();
$id = time();
$points = 10;
$raw_data['cmds'][] = array(
    'cmd' => \Cmds\PushTapjoyCmd::CMD_NAME,
    'trans' => md5(sprintf(\Cmds\PushTapjoyCmd::FORMAT, $id, $points)),
    'id' => $id,
    'points' => $points
);