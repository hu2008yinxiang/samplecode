<?php
date_default_timezone_set("UTC");

require_once(dirname(__FILE__)."/lwdb.php");
require_once dirname(__FILE__) . '/../up/util.php';

$param 		= parseParam($_POST,$_GET);
$project 	= $param['project'];
$version = $param['version'];

sql_query("delete from log_crash where project=\"$project\" and versionCode=$version");

header("Location: ./list_stacks.php");

?>
