<?php
date_default_timezone_set("UTC");

require_once(dirname(__FILE__)."/lwdb.php");

$infos = sql_fetch_rows("select project,versionCode,count(1) as ccount from log_crash group by  project,versionCode order by project,versionCode");
echo "<html><body><table  border='1'>";
echo "<tr><td>project</td><td>versionCode</td><td>count</td><td>OP</td></tr>";

foreach ($infos as $info) {
	$project = $info["project"];
	$version = $info["versionCode"];
	$count = $info["ccount"];
	echo "<tr><td><a href=\"list_pv.php?project=$project&version=$version\">$project</a></td><td>$version</td><td>$count</td><td><a href=\"delete_project.php?project=$project&version=$version\" onclick='if(!confirm(\"sure to delete?\"))return false;'>DEL</a></td></tr>";
}

echo "</table></body></html>";

?>
