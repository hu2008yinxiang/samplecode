<?php
date_default_timezone_set("UTC");

require_once(dirname(__FILE__)."/lwdb.php");
require_once dirname(__FILE__) . '/../up/util.php';

$param 		= parseParam($_POST,$_GET);
$project 	= $param['project'];
$version = $param['version'];

$infos = sql_fetch_rows("select log_file,count(1) as ccount from log_crash where project=\"$project\" and versionCode=$version group by log_file order by ccount desc");
echo "<html><body><p>$project:$version<br><br></p><table border='1'>";
echo "<tr><td>file</td><td>count</td><td>device</td></tr>";

foreach ($infos as $info) {
	$log_file = $info["log_file"];
	$count = $info["ccount"];
	echo "<tr><td><a href=\"stack.php?project=$project&v=$version&s=$log_file\">$log_file</a></td><td>$count</td><td><a href=\"list_model.php?project=$project&v=$version&s=$log_file\">device list</a></tr>";
}

echo "</table></body></html>";

?>
