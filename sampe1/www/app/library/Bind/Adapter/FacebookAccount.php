<?php
namespace Bind\Adapter;

use Bind\BindAccount;

class FacebookAccount extends BindAccount
{

    protected $_appId = null;

    protected $_secret = null;

    protected $_session = null;

    public function __construct($di, $appId, $secret, $token, $check = true)
    {
        $this->setDI($di);
        $this->_token = $token;
        $this->_appId = $appId;
        $this->_secret = $secret;
        $this->_session = new \Facebook\FacebookSession($token);
        try {
            
            $this->_status = self::BIND_INITED;
            if (! $check) {
                return;
            }
            $valid = $this->_session->validate($this->_appId, $this->_secret);
            $this->_session = $this->_session->getLongLivedSession($this->_appId, $this->_secret);
            $this->_token = $this->_session->getToken();
            $obj = $this->get('/me');
            $this->_nickname = $obj->getProperty('name');
            $this->_account = $obj->getProperty('id');
            $this->_email = $obj->getProperty('email');
            $this->_gender = $obj->getProperty('gender');
            $this->_detail = $obj->asArray();
            $this->_detail['bind_time'] = time();
            // $obj = (new Facebook\FacebookRequest($this->_session, 'GET', $path));
            $obj = $this->get('/me/picture', array(
                'redirect' => false,
                
                // 'height' => self::USER_PHOTO_SIZE,
                // 'witdh' => self::USER_PHOTO_SIZE,
                'type' => 'large'
            ));
            $url = $obj->getProperty('url');
            $this->_photo = $url;
            $this->_detail['photo'] = $url;
            $this->_detail['email'] = $this->_email;
            $this->innerGetFriends();
            $this->_status = self::BIND_OK;
        } catch (\Facebook\FacebookSDKException $e) {
            \Phalcon\DI::getDefault()->get('logger')->notice('facebook [ code: ' . $e->getCode() . ' ] ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString());
            $errorCode = $e->getCode();
            if (($errorCode >= 1 && $errorCode <= 89) || ($errorCode == 660)) {
                $this->_status = self::BIND_TIMEOUT;
            } else 
                if ($errorCode == 601 || $errorCode == 100) {
                    $this->_status = self::BIND_EXPIRE;
                } else {
                    $this->_status = self::BIND_EXPIRE;
                    // TODO 是否进行错误处理？
                    //throw $e;
                }
        }
    }

    /**
     *
     * @param string $path            
     * @param mixed $args            
     * @return GraphObject
     */
    public function get($path, $args = array())
    {
        $req = new \Facebook\FacebookRequest($this->_session, 'GET', $path, $args);
        $response = $req->execute();
        return $response->getGraphObject();
    }

    public function post($path, $args)
    {
        $req = new \Facebook\FacebookRequest($this->_session, 'POST', $path, $args);
        $response = $req->execute();
        return $response->getGraphObject();
    }

    protected function innerGetFriends()
    {
        $this->_friends = array();
        $obj = $this->get('/me/friends');
        $friends = $obj->getPropertyAsArray('data');
        foreach ($friends as $fobj) {
            $fid = $fobj->getProperty('id');
            $user = \UserAccounts::findFirstByBindId($fid);
            // var_dump ( $user );
            if ($user) {
                $this->_friends[] = $user;
            }
        }
    }

    /**
     *
     * @param FacebookAccount $fb            
     * @param string $account            
     * @param array $detail            
     */
    public static function load(FacebookAccount $fb, $account, array $detail)
    {
        $fb->_account = $account;
        $fb->_detail = $detail;
        $fb->_email = $detail['email'];
        $fb->_gender = $detail['gender'];
        $fb->_bind_time = $detail['bind_time'];
        $fb->_photo = $detail['photo'];
    }
}