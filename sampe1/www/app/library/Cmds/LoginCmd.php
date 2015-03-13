<?php
namespace Cmds;

class LoginCmd extends Cmd
{

    const FORCE_VALID_TOKEN = false;

    const CMD_NAME = "Login";

    public static function defaultData()
    {
        return array(
            'account_token' => '',
            'bind_token' => '',
            'bind_type' => '',
            'latitude' => - 999,
            'longitude' => - 999,
            'locale' => 'N/A'
        );
    }

    protected function do_execute()
    {
        // $this->di->get('logger')->notice('login ' . var_export($this->data, true));
        $app_version = intval($this->data['_app_version']);
        $minClientVersion = $this->config->app->minClientVersion;
        if ($app_version < $minClientVersion) {
            $this->ret['result']['errno'] = \Errors::CLIENT_NEED_UPDATE;
            $this->ret['stop'] = true;
            return;
        }
        $check = true;
        $token = $this->data['account_token'];
        $ret = array();
        $ua = \UserAccounts::findFirst(array(
            'account_id = :account_id: and account_token = :account_token:',
            'bind' => array(
                'account_id' => $this->account_id,
                'account_token' => $token
            )
        ));
        $bind_token = $this->data['bind_token'];
        $bind_type = $this->data['bind_type'];
        if ($ua) {
            $di = $this->di;
            if (! empty($bind_type) && ! empty($bind_token)) {
                $reti = $ua->bind($bind_type, $bind_token);
                if (is_a($reti, '\\UserAccounts')) {
                    // 之前已经绑定
                    $ua = $reti;
                    if ($ua == false) {
                        $reti = false;
                    }
                    $this->ret['stop'] = true;
                } elseif ($reti == false) {
                    $this->ret['result']['errno'] = \Errors::BIND_EXPIRED;
                    $this->ret['stop'] = true;
                    return;
                }
                $check = false;
            }
            // $result ['cmd'] = 'Login';
            if ($ua->type != 'local') {
                $adapterName = $ua->type . 'Adapter';
                $adapter = $di->get($adapterName);
                // $check = true;
                $bindAccount = $adapter->load($ua->bind_id, $ua->bind_token, $ua->bind_detail, $check);
                if ($check) { // 要求检查token
                              // 超时并且强制token有效 或者 token 过期
                    if (($bindAccount->bindStatus() == \Bind\BindAccount::BIND_TIMEOUT) && (self::FORCE_VALID_TOKEN) || $bindAccount->bindStatus() == \Bind\BindAccount::BIND_EXPIRE) {
                        $this->ret['result']['errno'] = \Errors::BIND_EXPIRED;
                        $this->ret['stop'] = true;
                        $this->_eventsManager->fire('user:loginFailed', $this, $this->data);
                        return;
                    }
                    $this->photoManager->download($bindAccount->getPhoto(), $ua->account_id);
                    if ($bindAccount->bindStatus() == \Bind\BindAccount::BIND_OK) {
                        $ua->bind_token = $bindAccount->getToken();
                        $ua->bind_detail = $bindAccount->getDetail();
                        $ua->syncBindFriends($bindAccount->getFriends(), $ua->type);
                        $ua->save();
                    }
                }
                // $this->photoManager->download($ua->bind_detail['photo'], $ua->account_id);
            }
            $this->_eventsManager->fire('user:loginSuccess', $ua);
            $result = $ua->getInfoArray();
            $ua->app_version = $app_version;
            $ua->device_id = $this->data['_device_id'];
            $ua->save();
            \SessionManager::getLoginExtras($ua, $result);
            $this->ret['result'] = \Utils::array_merge_r($this->ret['result'], $result);
            // $app->getDI()
            // ->get('specialOfferManager')
            // ->getSpecialOffer($raw_data['account_id'], $result['results'][0]); // 插入特供信息
            // $this->specialOfferManager->getSpecialOffer($this->account_id, $this->ret['result']);
            $this->ret['result']['featured'] = false;
            //\Positions::setPosition($ua->account_id, $this->data['latitude'], $this->data['longitude'], $this->data['locale']);
        } else {
            $this->ret['result']['errno'] = \Errors::LOGIN_FAILED;
            $this->ret['stop'] = true;
            $this->_eventsManager->fire('user:loginFailed', $this, $this->data);
            return;
        }
    }

    public function check()
    {
        return false;
    }
}