<?php

class Utils {

	/**
	 * 解压
	 *
	 * @param string $zipped
	 * @return string
	 */
	public static function gunzip($zipped) {
		$offset = 0;
		if (substr ( $zipped, 0, 2 ) == "\x1f\x8b")
			$offset = 2;
		if (substr ( $zipped, $offset, 1 ) == "\x08") {
			return gzinflate ( substr ( $zipped, $offset + 8 ) );
		} else {
			return gzuncompress ( $zipped );
		}
		return $zipped;
	}

	/**
	 * 获取原始POST内容
	 *
	 * @return string
	 */
	public static function get_post_content() {
		$content = file_get_contents ( 'php://input' );
		if (isset ( $_SERVER ['HTTP_CONTENT_ENCODING'] ) && $_SERVER ['HTTP_CONTENT_ENCODING'] == 'gzip') {
			$content = self::gunzip ( $content );
		}
		return $content;
	}

	/**
	 * 后台执行命令
	 *
	 * @param string $command
	 */
	public static function execbg($command) {
		if (stripos ( php_uname (), 'Windows' )) {
			pclose ( popen ( "start /B " . $command, "r" ) );
			return;
		}
		exec ( $command . '< /dev/null > /dev/null &' );
	}

	public static function sendToTarGz($out, array $files) {
		\Storage::getDir ( dirname ( $out ) );
		$cmd = "tar zcf " . $out . ' ' . implode ( ' ', $files );
		exec ( $cmd );
	}

	public static function backUp($file) {
		$index = - 1;
		$filename = $file;
		while ( file_exists ( $filename ) ) {
			$index ++;
			$filename = $file . '.' . $index;
		}
		if ($index > - 1) {
			rename ( $file, $filename );
		}
	}
	
}