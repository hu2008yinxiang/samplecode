<?php
namespace Cmds;

class SearchNearByCmd extends Cmd
{

    const CMD_NAME = 'SearchNearBy';

    public static function defaultData()
    {
        return array(
            'latitude' => - 999,
            'longitude' => - 999,
            'locale'=>'N/A'
        );
    }

    protected function do_execute()
    {
        \Positions::setPosition($this->account_id, $this->data['latitude'], $this->data['longitude'], $this->data['locale']);
        if (abs($this->data['latitude']) > 90 || abs($this->data['longitude']) > 180) {
            $this->ret['result']['errno'] = \Errors::LOCATE_FAILED;
            return;
        }
        $this->ret['result']['data'] = $this->nearByManager->searchNearBy($this->account_id, $this->data['latitude'], $this->data['longitude']);
    }
}