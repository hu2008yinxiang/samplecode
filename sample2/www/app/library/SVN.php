<?php

class SVN {
	protected $username, $password, $path;
	protected $configDir;
	protected $global_option;

	public function __destruct() {
		// @exec ( 'rm -rf ' . $this->configDir );
	}

	public function export($src, $dst, $version = 'HEAD') {
		$src = '/' . trim ( $src, ' /' );
		$cmd = "svn export --force $this->global_option -r $version $this->path$src $dst";
		echo $cmd, PHP_EOL;
		$out = array ();
		exec ( $cmd, $out );
		print_r ( $out );
	}

	/**
	 * 接受服务器证书
	 */
	public function acceptCert() {
		$out = array ();
		//exec ( "rm -rf $this->configDir" );
		$cmd = 'echo "p\nno\n" | svn list --config-dir ' . $this->configDir . ' --username ' . $this->username . ' --password ' . $this->password . ' ' . $this->path . ' ';
		// $cmd = 'sh ' . __DIR__ . '/shell.sh';
		echo $cmd, PHP_EOL;
		exec ( $cmd, $out );
		print_r ( $out );
		return;
		$cmd = 'echo "p\nno\n" | svn list --force-interactive --config-dir ' . $this->configDir . ' --username ' . $this->username . ' --password ' . $this->password . ' ' . $this->path . ' ';
		echo $cmd, PHP_EOL;
		exec ( $cmd, $out );
		print_r ( $out );
	}

	public function __construct($username, $password, $path) {
		$this->username = $username;
		$this->password = $password;
		$this->path = $path;
		$this->path = trim ( $path, ' /' );
		$this->configDir = __DIR__ . '/subversion';
		$this->global_option = "--username $this->username --password $this->password --no-auth-cache --non-interactive --trust-server-cert --config-dir $this->configDir";
		$this->acceptCert ();
		register_shutdown_function ( array (
				$this,
				'__destruct'
		) );
	}
}
