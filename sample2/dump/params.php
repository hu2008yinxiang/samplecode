<?php
date_default_timezone_set("UTC");
$json = $argv[1];
$content = json_decode($json, true);
$brand = isset($content["brand"]) ? $content["brand"] : "";
$versionCode = isset($content["versionCode"]) ? $content["versionCode"] : 0;
$project = isset($content["project"]) ? $content["project"] : "";
$model = isset($content["model"]) ? $content["model"] : "";
$androidSdkInt = isset($content["androidSdkInt"]) ? $content["androidSdkInt"] : 0;
$versionName = isset($content["versionName"]) ? $content["versionName"] : "";
$manufacturer = isset($content["manufacturer"]) ? $content["manufacturer"] : "";
echo "$brand,$model,$androidSdkInt,$versionName,$manufacturer";
?>
