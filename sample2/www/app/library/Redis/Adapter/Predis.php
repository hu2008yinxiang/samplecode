<?php

namespace Redis\Adapter;

// include __DIR__ . '/predis_0.8.5.phar';
require 'phar://' . __DIR__ . '/predis_0.8.5.phar/Predis/Autoloader.php';

\Predis\Autoloader::register ();

class Predis extends \Redis\Adapter {
	private $redisClient;

	public function __construct($config = array('host'=>'localhost','port'=>6379,'database'=>0,'password'=>NULL), $options = array()) {
		$this->redisClient = new \Predis\Client ( $config, $options );
	}

	public function get($name) {
		if (is_array ( $name ))
			return $this->redisClient->mget ( $name );
		return $this->redisClient->get ( $name );
	}

	public function set($name, $value = NULL) {
		if (is_array ( $name )) {
			$this->redisClient->mset ( $name );
			return;
		}
		if (is_array ( $value )) {
			$this->redisClient->hmset ( $name, $value );
			return;
		}
		$this->redisClient->set ( $name, $value );
	}
}