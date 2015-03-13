<?php
date_default_timezone_set("UTC");

require_once(dirname(__FILE__)."/lwdb.php");
require_once dirname(__FILE__) . '/../up/util.php';

$param          = parseParam($_POST,$_GET);
$project        = $param['project'];
$version = $param['v'];
$file = $param['s'];
$model = $param['m'];

$infos = sql_fetch_rows("select * from log_crash where project=\"$project\" and versionCode=$version and log_file=\"$file\" and model=\"$model\"");
echo "<html><body><p>$project:$version:$file<br><br></p><table border='1'>";
echo "<tr><td>brand</td><td>model</td><td>androidSdkInt</td><td>versionName</td><td>manufacturer</td></tr>";

foreach ($infos as $info) {
	$brand = $info["brand"];
	$model = $info["model"];
	$androidSdkInt = $info["androidSdkInt"];
	$versionName = $info["versionName"];
	$manufacturer = $info["manufacturer"];
	echo "<tr><td>$brand</td><td>$model</td><td>$androidSdkInt</td><td>$versionName</td><td>$manufacturer</td></tr>";
}

echo "</table></body></html>";

?>
