<?php

function is_internal_ip($ip)
{
    $ip = ip2long($ip);
    $net_a = ip2long('10.255.255.255') >> 24; // A类网预留ip的网络地址
    $net_b = ip2long('172.31.255.255') >> 20; // B类网预留ip的网络地址
    $net_c = ip2long('192.168.255.255') >> 16; // C类网预留ip的网络地址
    return $ip >> 24 === $net_a || $ip >> 20 === $net_b || $ip >> 16 === $net_c;
}
if (is_internal_ip($_SERVER['REMOTE_ADDR'])) {
    define('MICRO_TEST', true);
    ini_set('display_errors', true);
    ini_set('error_reporting', E_ALL);
    $a_time = microtime(true);
}
// echo $_SERVER['REMOTE_ADDR'];
require 'index.php';
if (defined('MICRO_TEST')) {
    $b_time = microtime(true);
    echo PHP_EOL, PHP_EOL, $b_time - $a_time;
}
?>