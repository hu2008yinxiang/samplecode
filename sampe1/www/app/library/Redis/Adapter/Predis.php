<?php
namespace Redis\Adapter;

// include __DIR__ . '/predis_0.8.5.phar';
require 'phar://' . __DIR__ . '/predis_0.8.5.phar/Predis/Autoloader.php';

\Predis\Autoloader::register();

class Predis extends \Redis\Adapter
{

    private $redisClient;

    public function __construct($config = array('host'=>'localhost','port'=>6379,'database'=>0,'password'=>NULL), $options = array())
    {
        $this->redisClient = new \Predis\Client($config, $options);
    }

    public function __call($method, $args)
    {
        return call_user_func_array(array(
            $this->redisClient,
            $method
        ), $args);
    }
}