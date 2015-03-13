<?php
date_default_timezone_set("UTC");

require_once(dirname(__FILE__)."/lwdb.php");
require_once dirname(__FILE__) . '/../up/util.php';

$param          = parseParam($_POST,$_GET);
$project        = $param['project'];
$version = $param['v'];
$file = $param['s'];

$infos = sql_fetch_rows("select manufacturer,model,count(1) as dc from log_crash where project=\"$project\" and versionCode=$version and log_file=\"$file\" group by manufacturer,model order by dc desc");
echo "<html><body><p>$project:$version:$file<br><br></p><table border='1'>";
echo "<tr><td>manufacturer</td><td>model</td><td>count</td></tr>";

foreach ($infos as $info) {
	$manufacturer = $info["manufacturer"];
	$model = $info["model"];
	$count = $info["dc"];
	echo "<tr><td>$manufacturer</td><td>$model</td><td><a href=\"list_device.php?project=$project&v=$version&s=$file&m=$model\">$count</td></td></tr>";
}

echo "</table></body></html>";

?>
