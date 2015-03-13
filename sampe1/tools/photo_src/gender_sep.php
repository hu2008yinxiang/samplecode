<?php
include __DIR__ . '/../../www/app/library/Misc.php';
error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', true);
function loadData()
{
    $pattern = __DIR__ . '/json/*.json';
    $files = glob($pattern);
    $data = array();
    foreach ($files as $file) {
        $ele = json_decode(file_get_contents($file), true);
        if (! is_array($ele)) {
            continue;
        }
        while (($c = array_pop($ele))) {
            $gender = $c['port_subject_gender'];
            $id = $c['port_subject_id'];
            $gender = ($gender == 'male' ? 'male' : 'female');
            $data[$id] = $gender;
        }
    }
    return $data;
}

function syncData()
{
    $newData = array(
        'male_size' => 0,
        'female_size' => 0,
        'male' => array(),
        'female' => array()
    );
    $pattern = __DIR__ . '/../../www/app/config/iphotos/pics/*.jpg';
    $files = glob($pattern);
    $data = loadData();
    foreach ($files as $file) {
        $name = basename($file, '.jpg');
        $gender = isset($data[$name]) ? $data[$name] : 'female';
        $newData[$gender][] = $name . '.jpg';
    }
    $newData['male_size'] = count($newData['male']);
    $newData['female_size'] = count($newData['female']);
    Misc::cacheToFile($newData, __DIR__ . '/photo_map.php');
    copy(__DIR__ . '/photo_map.php', __DIR__ . '/../../www/app/config/photo_map.php');
}
syncData();