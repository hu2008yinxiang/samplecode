<?php
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../up/util.php';


$param 		= parseParam($_POST,$_GET);
$project 	= $param['project'];
$appVersion = $param['versionCode'];
if (empty($project)){
	echo "error,project name is null";
	return;
}
if (empty($appVersion)){
	echo "tips:versionCode is null ! system auto show all logs:".'</a><br />';
	$appVersion = 0;
}
$dirPath = UPLOAD_UFILE_SUB_DIR.$project."/".$appVersion."/";
$dir = dir($dirPath);

$i = 0;
$files=array();
while (($file = $dir->read()) !== false)
{
	if ($file != '.' && $file != '..') {
		$time = filemtime("$dirPath$file");
		$files[$time."||".$file] = $file;
		$i ++;
	}
}
$dir->close();
krsort($files);
$downDirPath = DOWNLOAD_UFILE_SUB_DIR.$project."/".$appVersion."/";
foreach ($files as $file) {
	echo "filename: <a href='$downDirPath$file'>" . $file . "</a><br />";
}
echo "$i files";


?>
