<?php
if (defined('TEST_SCRIPT_LOADED')) {
    return;
}
define('TEST_SCRIPT_LOADED', true);
$raw_data['role'] = 'mina';
$raw_data['account_id'] = '100433518';
$raw_data['cmds'] = array();
foreach (glob(__DIR__ . '/*.php') as $filename) {
    include $filename;
}
//opcache_reset();
return;
$raw_data['role'] = 'mina';
$raw_data['account_id'] = '100433753';
$raw_data['cmds'] = array();
$raw_data['cmds'][] = array(
    'cmd' => \Cmds\GetUserInfoCmd::CMD_NAME,
    'account_id' => '1004330481'
);