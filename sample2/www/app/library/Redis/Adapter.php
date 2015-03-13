<?php

namespace Redis;

abstract class Adapter {

	protected function __construct() {
	}

	/**
	 * 读取缓存
	 * 当$name为string时获取单个值，为array时批量获取
	 *
	 * @param string|array $name
	 *        	要读取的key(s)
	 * @return string|array
	 */
	public abstract function get($name);

	/**
	 * 设置缓存
	 * 当$name为array时进行批量设置（批量设置时值不支持数组）
	 * 当$value为array时设置为HashMap
	 *
	 * @param string|array $name
	 *        	缓存键
	 * @param string|array $value
	 *        	缓存值
	 * @return
	 *
	 *
	 */
	public abstract function set($name, $value = NULL);
}