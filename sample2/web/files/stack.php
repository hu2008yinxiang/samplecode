<?php
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../up/util.php';

$param          = parseParam($_POST,$_GET);
$project        = $param['project'];
$appVersion = $param['v'];
$file = $param['s'];

$pos = strrpos($file, "..");
if ($pos !== false || empty($project)){
        echo "error param,project or versionCode null";
        return;
}
$dirPath = UPLOAD_STACK_SUB_DIR.$project."/$appVersion/$file";

$a = file($dirPath);
foreach($a as $line => $content){
        echo $content . "<br />";
}


?>
