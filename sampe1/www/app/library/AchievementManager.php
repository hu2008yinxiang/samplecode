<?php

class AchievementManager extends \Phalcon\Mvc\User\Component
{

    protected $data = null;

    protected $configFile = null;

    protected $mapping = null;

    public function setDI($di)
    {
        parent::setDI($di);
        $config = $di->get('config')->achievements;
        $this->configFile = $config->configFile;
        $data = array();
        if (is_file($this->configFile)) {
            $data = include $this->configFile;
        }
        foreach ($data as $ele) {
            $this->data[$ele['id']] = $ele;
            $this->data[strtolower($ele['val'])] = $ele;
        }
    }

    public function getValues($account_id)
    {
        $ret = array();
        foreach ($this->data as $k => $acv) {
            if (! is_int($k)) {
                continue;
            }
            $ac = \Achievements::load($account_id, $acv['id']);
            $ret[] = array(
                'id' => $acv['id'],
                'val' => $ac->current,
                'status' => $ac->status
            );
        }
        return $ret;
    }

    public function reward($account_id, $achievement_id)
    {
        if (! isset($this->data[$achievement_id])) {
            return false;
        }
        $ac = \Achievements::load($account_id, $achievement_id);
        $reqs = $this->data[$achievement_id]['requires'];
        switch ($ac->status) {
            case '000':
                $index = 0;
                break;
            case '100':
                $index = 1;
                break;
            case '110':
                $index = 2;
                break;
            default:
                return false;
        }
        if ($reqs[$index] > $ac->current) {
            return false;
        }
        $reward = 0;
        $rewards = $this->data[$achievement_id]['awards'];
        $reward = $rewards[$index];
        if ($reward == 0) {
            return false;
        }
        switch ($index) {
            default:
            case 0:
                $ac->status = '100';
                break;
            case 1:
                $ac->status = '110';
                break;
            case 2:
                $ac->status = '111';
                break;
            case 3:
                $ac->status = '111';
                break;
        }
        $ac->save();
        return array(
            'status' => $ac->status,
            'reward' => $reward
        );
    }

    public function getAchievement($account_id, $val)
    {
        if (! isset($this->data)) {
            return false;
        }
        return \Achievements::load($account_id, $this->data[strtolower($val)]['id']);
    }
}