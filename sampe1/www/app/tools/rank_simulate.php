<?php

function &getData()
{
    static $data = array();
    return $data;
}

function seed()
{
    $min = 10010;
    $p = round($min / 100);
    $count = 50;
    $last = ($p * rand(5, 31) + $min);
    $last -= $last % 10;
    
    $data = &getData();
    $ids = array();
    
    seedForExpert($data);
    seedForAdvanced($data);
    seedForPrimary($data);
    foreach ($data as $key => &$value) {
        $value += $last;
        unset($value);
    }
}

function seedForExpert(array &$data)
{
    $index = 0;
    $id_base = 14512000; // id 起始量
    $delta = 0; // id 增量
    while ($index ++ < 5) {
        $delta = rand(1, 200);
        $id_base = $id = $id_base + $delta;
        $win_count = rand(1600000, 20000000) * 10;
        $data[$id . ''] = $win_count;
    }
}

function seedForAdvanced(array &$data)
{
    $index = 0;
    $id_base = 14513000;
    $delta = 0;
    while ($index ++ < 10) {
        $delta = rand(1, 100);
        $id_base = $id = $id_base + $delta;
        $win_count = rand(18000, 2400000) * 10;
        $data[$id . ''] = $win_count;
    }
}

function seedForPrimary(array &$data)
{
    $index = 0;
    $id_base = 14614000;
    $delta = 0;
    while ($index ++ < 35) {
        $delta = rand(1, 28);
        $id_base = $id = $id_base + $delta;
        $win_count = rand(1000, 200000) * 10;
        $data[$id . ''] = $win_count;
    }
}

function update($interval = 0)
{
    $data = &getData();
    foreach ($data as $key => &$value) {
        $key = $key . '';
        $delta = 0;
        $begin = 0;
        $end = 0;
        $ratio = 1;
        switch ($key[4]) {
            case '2':
                $begin = 2000000 * 0.05;
                $end = 20000000;
                $ratio = rand(1, 5) / 6.0;
                break;
            case '3':
                $begin = 20000 * 0.08;
                $end = 2000000 * 6.8;
                $ratio = rand(1, 5) / 4.65;
                break;
            case '4':
                $begin = 9000 * 0.08;
                $end = 1000000 * 7.8;
                $ratio = rand(1, 5) / 3.3;
                break;
            default:
                break;
        }
        $delta = intval(rand($begin, $end) * $ratio) * 10;
        if (rand(1, 6) > 2) {
            $value += $delta;
        }
        
        unset($value);
    }
}

function recordData($data)
{
    static $header = false;
    
    $file = 'result.csv';
    
    if (! $header) {
        $fp = fopen($file, 'w');
        fputcsv($fp, array_keys($data));
    } else {
        $fp = fopen($file, 'a');
    }
    $header = true;
    fputcsv($fp, array_values($data));
    fclose($fp);
}

$index = 0;
seed();
while (++ $index < 33 * 7) {
    recordData(getData());
    update();
}
system(__DIR__ . '/' . "rank_result.xlsx");
