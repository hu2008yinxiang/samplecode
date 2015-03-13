<?php
include __DIR__ . '/../../www/app/library/Misc.php';

$file = __DIR__ . '/names3.csv';
$fp = fopen($file, 'r');
$names = array(
    'male_size' => 0,
    'female_size' => 0,
    'male' => array(),
    'female' => array()
);
while ($data = fgetcsv($fp)) {
    if (count($data) != 4) {
        continue;
    }
    $data[0] = $data[0] == 'male' ? 'male' : 'female';
    $names[$data[0]][] = array(
        $data[1],
        $data[2],
        $data[3]
    );
}
$names['male_size'] = count($names['male']);
$names['female_size'] = count($names['female']);
fclose($fp);
Misc::cacheToFile($names, __DIR__ . '/names_map.php');
copy(__DIR__ . '/names_map.php', __DIR__ . '/../../www/app/config/names_map.php');