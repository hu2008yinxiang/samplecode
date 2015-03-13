<?php

class DumpProcessor {

	private static function mkdir($dir) {
		return (is_dir ( $dir ) || (is_file ( $dir ) && unlink ( $dir )) || mkdir ( $dir, 0777, true ));
	}

	public static function genStack($sym_dir, $dump_file, $stack_dir, $tmp_dir, $wait = true, &$info = array()) {
		if (! (self::mkdir ( $tmp_dir ) && self::mkdir ( $stack_dir ) && is_file ( $dump_file ) && self::mkdir ( dirname ( $dump_file ) ))) {
			$info ['error'] = 'FILE_OR_DIR_NOT_ACCESSIBLE';
			return 1;
		}
		$file = fopen ( $dump_file . '.lock', 'c' );
		if ($wait) {
			flock ( $file, LOCK_EX );
		} else {
			if (! flock ( $file, LOCK_EX | LOCK_NB ))
				return 0;
		}
		$retCode = 0;
		self::innerGenStack ( $sym_dir, $dump_file, $stack_dir, $tmp_dir, $retCode, $info );
		flock ( $file, LOCK_UN );
		fclose ( $file );
		@unlink ( $dump_file . '.lock' );
		return $retCode;
	}

	private static function innerGenStack($sym_dir, $dump_file, $stack_dir, $tmp_dir, &$retCode, &$info = array()) {
		$dump_mark = $dump_file . '.mark';
		$tmp_stack = base64_encode ( $dump_file );
		$cmd = 'minidump_stackwalk ' . $dump_file . ' ' . $sym_dir . ' | grep "!" > ' . $tmp_stack;
		$ret = - 1;
		$out = array ();
		exec ( $cmd, $out, $ret );
		echo '[', $cmd, ']执行结果[', $ret, ']';
		if ($ret) {
			$info ['error'] = 'ERROR_WHEN_WALKSTACK';
			$retCode = 2;
			@unlink ( $tmp_stack );
			return;
		}
		$md5 = md5_file ( $tmp_stack );
		$stack_file = $stack_dir . '/' . $md5 . '.stack';
		if (! rename ( $tmp_stack, $stack_file )) {
			$info ['error'] = 'ERROR_WHEN_RENAME_TMP_TO_STACK';
			$retCode = 3;
			@unlink ( $tmp_stack );
			@unlink ( $stack_file );
			return;
		}
		// 写mark
		if (! file_put_contents ( $dump_mark, $md5 )) {
			@unlink ( $stack_file );
			@unlink ( $dump_mark );
			$info ['error'] = 'ERROR_WHEN_WRITE_DUMP_MARK';
			$retCode = 4;
			return;
		}
		$info ['md5'] = $md5;
		$info ['stack_file'] = $stack_file;
		$retCode = 0;
		return;
	}

	public static function genSymbol($so_file, $symbol_dir, $tmp_dir, $wait = true, &$info = array()) {
		if (! (self::mkdir ( $symbol_dir ) && self::mkdir ( $tmp_dir ))) {
			$info ['error'] = 'FILE_OR_DIR_NOT_ACCESSIBLE';
			return 1;
		}
		
		$file = fopen ( $so_file . '.lock', 'c' );
		if (! $file) {
			$info ['error'] = 'FILE_OR_DIR_NOT_ACCESSIBLE';
			return 1;
		}
		if ($wait) {
			flock ( $file, LOCK_EX );
		} else {
			if (! flock ( $file, LOCK_EX | LOCK_NB ))
				return 0;
		}
		$retCode = 0;
		self::innerGenSymbol ( $so_file, $symbol_dir, $tmp_dir, $retCode, $info );
		flock ( $file, LOCK_UN );
		fclose ( $file );
		@unlink ( $so_file . '.lock' );
		return $retCode;
	}

	private static function innerGenSymbol($so_file, $symbol_dir, $tmp_dir, $wait = true, &$retCode, &$info = array()) {
		$tmp_file = $tmp_dir . '/' . base64_encode ( $so_file );
		// 产生符号文件
		$cmd = 'dump_syms ' . $so_file . ' > ' . $tmp_file;
		$ret = - 1;
		$out = array ();
		exec ( $cmd, $out, $ret );
		if ($ret && ! file_exists ( $tmp_file )) {
			$info ['error'] = 'ERROR_WHEN_DUMP_SYMBOL';
			$retCode = 2;
			@unlink ( $tmp_file );
			return;
		}
		// 截取第一行
		$cmd = 'head -1 ' . $tmp_file;
		$out = array ();
		exec ( $cmd, $out, $ret );
		// 分析第一行
		$out = trim ( $out [0] );
		$line = $out;
		$out = preg_split ( '[\s]', $out );
		// $info = array ();
		$info ['platform'] = $out [1];
		$info ['architecture'] = $out [2];
		$info ['hash'] = $out [3];
		$info ['module'] = $out [4];
		// 拷贝符号文件
		$sym_file = $symbol_dir . '/' . $info ['module'] . '/' . $info ['hash'] . '/' . $info ['module'] . '.sym';
		$sym_dir = dirname ( $sym_file );
		if (! self::mkdir ( $sym_dir )) {
			$retCode = 3;
			$info ['error'] = 'FILE_OR_DIR_NOT_ACCESSIBLE';
			@unlink ( $tmp_file );
			return;
		}
		if (! rename ( $tmp_file, $sym_file )) {
			$retCode = 4;
			$info ['error'] = 'ERROR_WHEN_RENAME_TMP_TO_SYMBOL';
			@unlink ( $tmp_file );
			@unlink ( $sym_file );
			return;
		}
		// 生成mark
		$so_mark = $so_file . '.mark';
		if (! file_put_contents ( $so_mark, $line )) {
			$retCode = 5;
			@unlink ( $sym_file );
			@unlink ( $so_mark );
			$info ['error'] = 'ERROR_WHEN_WRITE_SO_MARK';
			return;
		}
		// 删除临时文件
		@unlink ( $tmp_file );
		$info ['sym_file'] = $sym_file;
		$retCode = 0;
	}

	public static function downloadSo($project, $version) {
		$lock_file = \Storage::getDir ( '/tmp', true ) . '/' . $project . '_' . $version . '.lock';
		$file = fopen ( $lock_file, 'c+' );
		if (! flock ( $file, LOCK_EX )) {
			return;
		}
		$svn = self::getSVN ();
		$update_file = "/$project/update.txt";
		
		$local_update_file = \Storage::getDir ( '/tmp' ) . '/' . $project . '_' . $version . '_update.txt';
		$svn->export ( $update_file, $local_update_file );
		if (file_exists ( $local_update_file )) {
			$found = false;
			$path = '';
			$csv = fopen ( $local_update_file, 'r' );
			while ( ($data = fgetcsv ( $csv )) !== FALSE ) {
				if ($data [0] == $version) {
					$path = $data [3];
					$found = true;
					break;
				}
			}
			if ($found) {
				$so_file = '/' . $project . '/' . $path;
				$local_so = \Storage::getDir ( '/so' ) . '/' . $project . '/' . $version . '/libgame.so';
				// 导出
				echo $so_file, ' => ', $local_so, PHP_EOL;
				$svn->export ( $so_file, $local_so );
			}
			@fclose ( $csv );
			@unlink ( $local_update_file );
		}
		flock ( $file, LOCK_UN );
		fclose ( $file );
		@unlink ( $lock_file );
	}

	public static function getSVN() {
		static $svn = NULL;
		if (! $svn) {
			$config = new \Phalcon\Config\Adapter\Ini ( APPLICATION_PATH . '/config/config.ini' );
			$config = $config->so_svn;
			$svn = new SVN ( $config->username, $config->password, $config->path );
		}
		return $svn;
	}
}