<?php

class Storage {

	public static function getConfig() {
		static $config = NULL;
		if (! $config) {
			$config = new \Phalcon\Config\Adapter\Ini ( __DIR__ . '/../config/config.ini' );
		}
		return $config;
	}

	public static function getBaseDir() {
		static $baseDir = NULL;
		if (! $baseDir){
			$baseDir = __DIR__ . "/../../" . self::getConfig ()->storage->baseDir;
			$baseDir = realpath($baseDir);
		}
		return $baseDir;
	}

	public static function getFile($name) {
		return self::getBaseDir () . $name;
	}
	
	public static function getDir($name, $required = FALSE){
		$name = self::getFile($name);
		if($required&&!is_dir($name)){
			if(file_exists($name))unlink($name);
			mkdir($name,0777,true);
		}
		return $name;
	}
}