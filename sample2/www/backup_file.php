<?php
$project = $argv [1];
$action = $argv [2];
$dir = realpath ( __DIR__ );
$backup_dir = $dir . '/backup/' . $project;
@mkdir($backup_dir,0777,true);

function file_copy($src, $dst, $mod = 0777) {
	// clean
	if ((is_file ( $src ) && is_dir ( $dst )) || (is_dir ( $src ) && is_file ( $dst ))) {
		exec ( 'rm -rf ' . $dst );
	}
	if (is_dir ( $src )) {
		@mkdir ( $dst, $mod, true );
	} else {
		@mkdir ( dirname( $dst ), $mod, true );
	}
	// copy
	$copy_cmd = 'cp -rf ';
	$cmd = $copy_cmd . $src . ' ' . $dst;
	exec ( $cmd );
	// chmod
	$cmd = 'chmod -R ' . $mod . ' ' . $dst;
	exec ( $cmd );
	echo 'copy '.$src.'  =>  '.$dst.PHP_EOL;
}
if ($action == 'backup') {
	$lines = file ( $dir . '/backup_file_' . $project . '.txt' );
	foreach ( $lines as $line ) {
		$line=trim($line);
		$src = $dir . $line;
		$dst = $backup_dir . $line;
		file_copy($src, $dst);
	}
} else if ($action == 'restore') {
	$lines = file ( $dir . '/backup_file_' . $project . '.txt' );
	foreach ( $lines as $line ) {
		$line=trim($line);
		$src = $dir . $line;
		$dst = $backup_dir . $line;
		file_copy($dst, $src);
	}
}
