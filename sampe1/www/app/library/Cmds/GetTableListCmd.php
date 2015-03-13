<?php
namespace Cmds;

class GetTableListCmd extends Cmd
{

    const CMD_NAME = 'GetTableList';

    protected function do_execute()
    {
        $id = $this->account_id;
        $this->ret['result']['data'] = \SessionManager::getTableList();
        $this->ret['result']['online_players'] = \SessionManager::getTotalOnline();
        $limit = static::getTableLimitation();
        foreach ($limit as $k => $v) {
            $this->ret['result'][$k] = $v;
        }
        $ac = $this->achievementManager->getAchievement($this->account_id, 'AssetsPeak');
        $this->ret['result']['current'] = array(
            'assets' => $ac->current
        );
    }

    public static function getTableLimitation()
    {
        return array(
            'advance' => array(
                'assets' => TABLE_LIMITATION_ADVANCED
            ),
            'expert' => array(
                'assets' => TABLE_LIMITATION_EXPERT
            )
        );
    }
}