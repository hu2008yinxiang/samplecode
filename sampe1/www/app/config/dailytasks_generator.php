<?php
$csv_file = __DIR__ . '/dailytasks.csv';
$handler = fopen($csv_file, 'r');
$skipLineCount = 2;
$data = array();

const I_ID = 0;

const I_TITLE = 1;

const I_DESC = 2;

const I_REWARD = 3;

const I_REQUIRE = 4;

const I_INFO = 5;

while (true) {
    $line = fgetcsv($handler);
    if ($skipLineCount > 0) {
        -- $skipLineCount;
        continue;
    }
    if ($line == null || empty($line)) {
        break;
    }
    $info = array(
        'id' => $line[I_ID],
        'title' => $line[I_TITLE],
        'desc' => $line[I_DESC],
        'reward' => $line[I_REWARD],
        'require' => $line[I_REQUIRE],
        'info' => $line[I_INFO]
    );
    $data[] = $info;
}
// 输出
file_put_contents(__DIR__ . '/dailytasksConfig.php', array(
    '<?php',
    PHP_EOL,
    'return json_decode(',
    var_export(json_encode($data, JSON_PRETTY_PRINT), true),
    ', true);',
    PHP_EOL
));
file_put_contents(__DIR__ . '/dailytasksConfig.json', json_encode($data, JSON_PRETTY_PRINT));
var_export(include __DIR__ . '/dailytasksConfig.php');
