<?php
namespace Rules;

class LoginComboRule extends Rule
{

    public function getCatalog()
    {
        return 'user';
    }

    public function loginComboChanged(\Phalcon\Events\Event $event, \UserAccounts $ua, array $data = null)
    {
        $acm = \Achievements::load($ua->account_id, $this->data['id'], $this->data['defaultStatus']);
        if ($acm->status['completed']) {
            if (! $acm->status['rewarded']) {
                // TODO notify client
                $this->notify($acm);
            }
            return true;
        }
        
        // not completed
        $acm->status['count'] = min(max($acm->status['count'], $ua->login_combo), $this->data['require']['count']);
        // completed ?
        $acm->status['completed'] = ($acm->status['count'] == $this->data['require']['count']);
        $acm->save();
        //
        if ($acm->status['completed']) {
            if (! $acm->status['rewarded']) {
                // TODO notify client
                $this->notify($acm);
            }
        }
        return true;
    }
    
    protected function notify($acm){
    	
    }
}