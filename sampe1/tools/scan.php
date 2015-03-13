<?php
$file = $argv[1];
function getFiles($file, array &$files)
{
    $extlist = array(
        'php',
        'phtml'
    ];
    if (is_file($file) && in_array(pathinfo($file, PATHINFO_EXTENSION), $extlist)) {
        $files[] = $file;
        return;
    }
    if (is_dir($file)) {
        $fd = opendir($file);
        while ($entry = readdir($fd)) {
            if ($entry == '.' || $entry == '..')
                continue;
            $entry = $file . '/' . $entry;
            getFiles($entry, $files);
        }
        closedir($fd);
        return;
    }
}
$arr = array();
getFiles($file, $arr);

foreach ($arr as $file) {
    $file = realpath($file);
    if (is_file($file)) {
        system('php ' . __DIR__ . '/converter.php ' . $file . ' ' . $file);
    }
}