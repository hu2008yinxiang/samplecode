<?php
namespace Bind;

abstract class Adapter implements \Phalcon\DI\InjectionAwareInterface
{

    protected $_di = null;

    public function __construct($options)
    {}

    public function setDI($di)
    {
        $this->_di = $di;
    }

    public function getDI()
    {
        return $this->_di;
    }

    /**
     * 进行绑定
     *
     * @param string $token            
     * @return 绑定结果
     */
    abstract function bind($token);

    /**
     * 以本地数据还原绑定资料
     *
     * @param string $account            
     * @param string $token            
     * @param string $detail            
     */
    abstract function load($account, $token, $detail, $check = false);
}