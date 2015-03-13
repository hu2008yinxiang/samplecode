<?php
define('APP_PATH', __DIR__ . '/../../www');
include APP_PATH . '/app/library/Misc.php';

$fp = fopen(__DIR__ . '/suites.csv', 'r');
$suites = array(
    'size' => 0,
    'data' => array()
);

while ($suite = fgetcsv($fp)) {
    if (count($suite) != 6) {
        continue;
    }
    $suites['data'][] = $suite;
}
fclose($fp);
$suites['size'] = count($suites['data']);
Misc::cacheToFile($suites, __DIR__ . '/suites.php');
copy(__DIR__ . '/suites.php', APP_PATH . '/app/config/suites.php');