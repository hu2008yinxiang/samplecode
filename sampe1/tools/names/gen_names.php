<?php
include __DIR__ . '/../../www/app/library/Misc.php';

$file = __DIR__ . '/names3.csv';
$fp = fopen($file, 'r');
$names = array(
    'size' => 0,
    'data' => array()
);
while ($data = fgetcsv($fp)) {
    if (count($data) != 4) {
        continue;
    }
    $data[0] = $data[0] == 'male' ? 'male' : 'female';
    $names['data'][] = $data;
}
$names['size'] = count($names['data']);
fclose($fp);
Misc::cacheToFile($names, __DIR__ . '/names.php');
copy(__DIR__ . '/names.php', __DIR__ . '/../../www/app/config/names.php');