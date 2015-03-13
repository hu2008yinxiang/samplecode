<?php
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../up/util.php';

$param 		= parseParam($_POST,$_GET);
$project 	= $param['project'];
$appVersion = $param['versionCode'];
if (empty($project) || $appVersion === null){
	echo "error param,project or versionCode null";
	return;
}
$dirPath = UPLOAD_STACK_SUB_DIR.$project."/$appVersion/";

$a = file($dirPath . 'count');
foreach($a as $line => $content){
	$c = split(" ", $content);
	$count = $c[0];
	$file = $c[1];
	echo "count: " . $count . "        <a href='stack.php?project=$project&v=$appVersion&s=$file'>" . $file . "</a><br />";
}


?>
