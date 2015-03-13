<?php
function parseParam($postData,$getData){
	
	if(isset($postData["project"])){
		$requestData = $postData;
	}elseif (isset($getData["project"])){
		$requestData = $getData;
	}else{
		return null;
	}
	return $requestData;
}

function _log($type, $param)
{
	if(IS_SYSLOG)
	{
		try
		{
			$version = "1.0.1";
			$message = json_encode(array(time(), $param));
			_rsyslog_('crashlog'.$type . $version, $message);
		}
		catch(Exception $e)
		{
		}
	}
}

function _rsyslog_($tag, $message)
{
	openlog($tag, LOG_ODELAY, LOG_LOCAL0);
	syslog(LOG_INFO, $message);
	closelog();
}

function _createRandomNonces ($length,$mode=0)
{
	switch ($mode) {
		case '1':
			$str = '1234567890';
			break;
		case '2':
			$str = 'abcdefghijklmnopqrstuvwxyz';
			break;
		case '3':
			$str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
		case '4':
			$str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
			break;
		case '5':
			$str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
			break;
		case '6':
			$str = 'abcdefghijklmnopqrstuvwxyz1234567890';
			break;
		default:
			$str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
			break;
	}

	$randString = '';
	$len = strlen($str)-1;

	for($i = 0;$i < $length;$i ++){
		$num = mt_rand(0, $len);
		$randString .= $str[$num];
	}
	return $randString ;
}
?>