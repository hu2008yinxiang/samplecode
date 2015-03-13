#!/bin/sh
curDir=$(dirname $(readlink -f $0))
appDir=$(dirname $curDir)
php_cli="$appDir/cli.php"
php $php_cli Crash_Record backupDump > /dev/null

