<?php
// return;
$file = __DIR__ . '/names2.csv';
$names = array();
$fp = fopen($file, 'r');
while ($line = fgetcsv($fp)) {
    $names[] = $line;
}
fclose($fp);
$data = array(
    'count' => count($names),
    'data' => $names
);
file_put_contents(__DIR__ . '/names.php', '<?php ' . PHP_EOL . 'return ' . var_export($data, true) . ';' . PHP_EOL);