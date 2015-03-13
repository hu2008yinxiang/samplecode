<?php
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/util.php';

try {
	$param 		= parseParam($_POST,$_GET);
	$project 	= $param['project'];
	$appVersion = $param['versionCode'];
	if (empty($project)){
		echo "error,project name is null";
		return;
	}
	if (empty($appVersion)){
		echo "error,versionCode is null! ";
		return;
	}
	$defaultVersion = 0;
	$file = $_FILES['file'];
	$file_name = $_FILES['file']['name'];
	$file_tmp_name = $_FILES['file']['tmp_name'];

	$nonces   = _createRandomNonces(8);
	$defaultDir = UPLOAD_UFILE_SUB_DIR.$project."/".$defaultVersion."/";
	$uploadDir	= UPLOAD_UFILE_SUB_DIR.$project."/".$appVersion."/";
	if(!file_exists($defaultDir)){
		mkdir($defaultDir,0777,true);   
	}  
	if(!file_exists($uploadDir)){
		mkdir($uploadDir,0777,true);   
	}   
	$defaultDataPath= $defaultDir.$file_name;
	$upLoadDataPath = $uploadDir.$file_name;
	if(move_uploaded_file($file_tmp_name, $upLoadDataPath))
	{
		if (copy($upLoadDataPath, $defaultDataPath)){
			echo "upload ok";
		}else{
			_log(SYSLOG_TYPE_FAIL, $file_name);
			echo 'upload error';
		}
	} else {
		_log(SYSLOG_TYPE_FAIL, $file_name);
		echo "upload error";
	} 
	$prefix = substr($file_name,0,strrpos($file_name,'.'));
	file_put_contents($uploadDir.PRIFIX_UPLOAD_PARAMS."_".$prefix,json_encode($param)."\n",FILE_APPEND);
}catch(Exception $e) {
	_log(SYSLOG_TYPE_EXP, $file_name);
	echo "upload exception..";
}



?>

