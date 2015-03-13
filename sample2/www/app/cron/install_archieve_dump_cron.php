<?php
$curPath = realpath ( __DIR__ );
$appPath = dirname ( $curPath );
$cronContent = '2 17 * * * php ' . $appPath . '/cli.php Crash_Record backupDump';
$return_var = - 1;
$output = array ();
exec ( 'crontab -l -u root', $output, $return_var );
$has = false;
foreach ( $output as $index => $cron ) {
	
	if (empty ( $cron )) {
		unset ( $output [$index] );
		continue;
	}
	$cron = trim ( $cron );
	if (! (strpos ( $cron, $cronContent ) === false)) {
		unset ( $output [$index] );
		$has = true;
	}
}
if ($has) {
	$content = implode ( '\\n', $output );
	exec ( 'crontab -u root -r' );
	$cmd = 'echo "' . $content . '" | crontab -u root -';
	exec ( $cmd );
	echo '成功卸载CRON任务';
} else {
	$output [] = $cronContent;
	$content = implode ( '\\n', $output );
	$cmd = 'echo "' . $content . '" | crontab -u root -';
	exec ( $cmd );
	echo '成功安装CRON任务';
}
echo PHP_EOL;
//$cronOut = 'echo -e "`crontab -l `\\n'.'2 17 * * * php ' . $appPath . '/cli.php Crash_Record backupDump" | crontab -';
//echo $cronOut;