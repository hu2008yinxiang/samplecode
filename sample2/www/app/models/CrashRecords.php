<?php

class CrashRecords extends \Phalcon\Mvc\Model {
	// record_id
	// project, versionCode,model,manufacturer,brand,androidSdkInt,versionName
	// status
	// proc
	// file_name
	// time_stamp
	public function getDmpUrl() {
		$path = \Storage::getFile ( "/dump/$this->project/$this->versionCode/$this->file_name" );
		if (file_exists ( $path ))
			return $this->getDI ()
				->get ( 'fileUrl' )
				->get ( "dump/$this->project/$this->versionCode/$this->file_name" );
		else {
			$path = $this->getArchieveUrl ();
			return <<<EOF
javascript:(function(){
	if(confirm('文件不存在，可能已经打包存档，是否下载打包的文件？')){
		window.location = '$path';
	}
})();
EOF;
		}
	}

	public function getArchieveUrl() {
		$path = $this->archieve . '.' . $this->project . '.tar.gz';
		if (! $this->archieve) {
			$path = date ( 'Y-m-d', strtotime ( $this->time_stamp ) ) . '.' . $this->project . '.tar.gz';
		}
		return $this->getDI ()
			->get ( 'fileUrl' )
			->get ( "/archieve/" . $path );
	}

	public function getDumpPath() {
		return \Storage::getFile ( "/dump/$this->project/$this->versionCode/$this->file_name" );
	}
}