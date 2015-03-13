<?php

date_default_timezone_set("UTC");

require_once(dirname(__FILE__)."/lwdb.php");
$fileName = $argv[1];

$file = fopen($fileName,'r');
while ($data = fgetcsv($file)) { 
	sql_insert("insert into log_crash(versionCode,project,brand,model,androidSdkInt,versionName,manufacturer,log_file,uuid) values($data[0],\"$data[1]\",\"$data[2]\", \"$data[3]\", $data[4], \"$data[5]\", \"$data[6]\", \"$data[7]\", \"$data[8]\")");
}
?>
