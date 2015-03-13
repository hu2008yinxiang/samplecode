<?php
namespace Bind;

class BindAccount implements \Phalcon\DI\InjectionAwareInterface
{

    const BIND_INITED = 0;

    const BIND_OK = 1;

    const BIND_TIMEOUT = 2;

    const BIND_EXPIRE = 3;

    protected $_di = null;

    protected $_account = false;

    protected $_nickname = false;

    protected $_email = false;

    protected $_token = false;

    protected $_expire = false;

    protected $_detail = false;

    protected $_photo = false;

    protected $_friends = array();

    protected $_gender = false;

    protected $_status = self::BIND_INITED;

    public function setDI($dependencyInjector)
    {
        $this->_di = $dependencyInjector;
    }

    public function getDI()
    {
        return $this->_di;
    }

    /**
     * 获取最新token
     *
     * @return string 最新token（如果支持，不支持则返回false）
     */
    function getToken()
    {
        return $this->_token;
    }

    /**
     * 获取账号详细信息
     *
     * @return array mixed bool
     */
    function getDetail()
    {
        return $this->_detail;
    }

    /**
     * 获取绑定的账号
     *
     * @return string bool
     */
    function getAccount()
    {
        return $this->_account;
    }

    /**
     * 获取头像
     *
     * @return string bytes
     */
    function getPhoto()
    {
        return $this->_photo;
    }

    function getNickname()
    {
        return $this->_nickname;
    }

    function getFriends()
    {
        return $this->_friends;
    }

    function getExpire()
    {
        return $this->_expire;
    }

    function bindStatus()
    {
        return $this->_status;
    }

    function getGender()
    {
        return $this->_gender;
    }

    function getEmail()
    {
        return $this->_email;
    }
}