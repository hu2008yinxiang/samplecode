<?php
namespace Redis;

abstract class Adapter
{

    protected function __construct()
    {}

    abstract public function __call($method, $args);
}