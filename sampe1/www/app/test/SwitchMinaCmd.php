<?php
return;
//$raw_data = array();
$raw_data['role'] = 'mina';
$raw_data['cmds'] = array();
$raw_data['cmds'][] = array(
    'cmd' => Cmds\MinaSwitchCmd::CMD_NAME,
    'host' => 'thinkgeek.vicp.net',
    'port' => 48085,
    'key' => 'mina-key-here'
);