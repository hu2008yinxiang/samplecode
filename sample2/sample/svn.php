<?php
use Phalcon\Acl;
$username = 'hujixu';
$password = 'huji2008';
$path = 'https://thinkgeek.warstudio.mobi:18443/svn/CrashReportingSo';
$cmd = "svn list --username $username --password $password $path";

$desc = array(array('pipe','r'),array('pipe','w'));
$pipes = array();
exec('rm -rf ~/.subversion');
$cmd = <<<EOL
$cmd <<EOC
p
no
EOC
EOL;
echo $cmd,PHP_EOL;
exec($cmd);

