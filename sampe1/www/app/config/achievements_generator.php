<?php
$csv_file = __DIR__ . '/achievements.csv';
$handler = fopen($csv_file, 'r');
$skipLineCount = 2;
$data = array();

const I_ID = 0;

const I_TITLE = 1;

const I_DESC = 2;

const I_VAL = 3;

const I_REQ_0 = 4;

const I_REQ_1 = 5;

const I_REQ_2 = 6;

const I_AWD_0_CHIP = 7;

const I_AWD_1_CHIP = 8;

const I_AWD_2_CHIP = 9;

//
while (true) {
    $line = fgetcsv($handler);
    if ($skipLineCount > 0) {
        -- $skipLineCount;
        continue;
    }
    if ($line == null || empty($line[I_ID]))
        break;
    $info = array(
        'id' => $line[I_ID],
        'title' => $line[I_TITLE],
        'desc' => $line[I_DESC],
        'val' => $line[I_VAL],
        'requires' => array(
            $line[I_REQ_0],
            $line[I_REQ_1],
            $line[I_REQ_2]
        ),
        'awards' => array(
            $line[I_AWD_0_CHIP],
            $line[I_AWD_1_CHIP],
            $line[I_AWD_2_CHIP]
        )
    );
    $data[] = $info;
}

file_put_contents(__DIR__ . '/achievementsConfig.php', array(
    '<?php',
    PHP_EOL,
    'return json_decode(',
    var_export(json_encode($data, JSON_PRETTY_PRINT), true),
    ',true );',
    PHP_EOL
));
file_put_contents(__DIR__ . '/achievementsConfig.json', json_encode($data, JSON_PRETTY_PRINT));

var_export(include __DIR__ . '/achievementsConfig.php');
