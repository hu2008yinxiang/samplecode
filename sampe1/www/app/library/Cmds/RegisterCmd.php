<?php
namespace Cmds;

class RegisterCmd extends Cmd
{

    const CMD_NAME = 'Register';

    public static function defaultData()
    {
        return array(
            'type' => 'local',
            'nickname' => '',
            'token' => '',
            'latitude' => - 999,
            'longitude' => - 999,
            'locale' => 'N/A'
        );
    }

    protected function do_execute()
    {
        $this->ret['stop'] = true;
        $app_version = intval($this->data['_app_version']);
        $device_id = $this->data['_device_id'];
        $minClientVersion = $this->config->app->minClientVersion;
        if ($app_version < $minClientVersion) {
            $this->ret['result']['errno'] = \Errors::CLIENT_NEED_UPDATE;
            return;
        }
        if (! \SessionManager::checkIP()) {
            $this->ret['result']['errno'] = \Errors::QUOTA_LIMITED;
            return;
        }
        
        $type = $this->data['type'];
        $nickname = $this->data['nickname'];
        $token = $this->data['token'];
        if (strlen($type) < 1) {
            $this->ret['result']['errno'] = \Errors::OP_DENIED;
            $this->_eventsManager->fire('user:registerFailed', $this, $this->data);
            return;
        }
        $ua = null;
        if ($type == 'local') {
            if (empty($nickname)) {
                $this->ret['result']['errno'] = \Errors::EMPTY_DATA;
                $this->_eventsManager->fire('user:registerFailed', $this, $this->data);
                return;
            }
            $ua = \UserAccounts::registerByLocal($nickname);
        } else {
            if (empty($token)) {
                $this->ret['result']['errno'] = \Errors::EMPTY_DATA;
                $this->_eventsManager->fire('user:registerFailed', $this, $this->data);
                return;
            }
            $ua = \UserAccounts::registByBind($type, $token);
        }
        if (! $ua) {
            $this->ret['result']['errno'] = \Errors::REGISTER_FAILED;
            $this->_eventsManager->fire('user:registerFailed', $this, $this->data);
            return;
        }
        // 保存app_version 和 device_id
        $ua->app_version = $app_version;
        $ua->device_id = $device_id;
        $ua->save();
        $this->_eventsManager->fire('user:registerSuccess', $ua);
        $this->_eventsManager->fire('user:loginSuccess', $ua);
        $info = $ua->getInfoArray();
        \SessionManager::getLoginExtras($ua, $info);
        if (\UserAccounts::count(array(
            'device_id = :did:',
            'bind' => array(
                'did' => $device_id
            )
        )) > 5) {
            $info['free_play'] = true;
        }
        $this->ret['result'] = array_merge($this->ret['result'], $info);
        // $this->specialOfferManager->getSpecialOffer($ua->account_id, $this->ret['result']);
        //\Positions::setPosition($ua->account_id, $this->data['latitude'], $this->data['longitude'], $this->data['locale']);
    }

    public function check()
    {
        return false;
    }
}