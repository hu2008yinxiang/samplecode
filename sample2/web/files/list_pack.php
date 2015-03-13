<?php
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../up/util.php';

$param 		= parseParam($_POST,$_GET);
$project 	= $param['project'];
$appVersion = $param['versionCode'];
if (empty($project)){
	echo "error,project name null";
	return;
}
$dirPath = UPLOAD_ZFILE_SUB_DIR.$project."/";
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
$downDirPath = DOWNLOAD_ZFILE_SUB_DIR.$project."/";
foreach ($files as $file) {
	echo "filename: <a href='$downDirPath$file'>" . $file . "</a><br />";
}
echo "$i files";


?>
