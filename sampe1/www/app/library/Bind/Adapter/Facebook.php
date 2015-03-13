<?php
namespace Bind\Adapter;

use Bind\Adapter;
use Bind\BindAccount;
use Facebook\FacebookSession;

class Facebook extends Adapter
{

    protected $_appId;

    protected $_secret;

    public function __construct($options)
    {
        $this->_appId = $options['AppId'];
        $this->_secret = $options['Secret'];
    }

    public function bind($token)
    {
        FacebookSession::enableAppSecretProof(false);
        return new FacebookAccount($this->_di, $this->_appId, $this->_secret, $token);
    }

    public function load($account, $token, $detail, $check = false)
    {
        FacebookSession::enableAppSecretProof(false);
        $fb = new FacebookAccount($this->_di, $this->_appId, $this->_secret, $token, $check);
        if (! $check) {
            if (empty($detail)) {
                $detail = array();
            }
            FacebookAccount::load($fb, $account, $detail);
        }
        return $fb;
    }
}