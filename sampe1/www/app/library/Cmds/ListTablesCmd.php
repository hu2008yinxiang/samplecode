<?php
namespace Cmds;

class ListTablesCmd extends Cmd
{

    const CMD_NAME = 'ListTables';

    protected function do_execute()
    {
        $this->ret['result']['data'] = static::getTableList();
        $this->ret['result']['advanced'] = array(
            'assets' => TABLE_LIMITATION_ADVANCED
        );
        $this->ret['result']['expert'] = array(
            'assets' => TABLE_LIMITATION_EXPERT
        );
        $ac = $this->achievementManager->getAchievement($this->account_id, 'AssetsPeak');
        $this->ret['result']['current'] = array(
            'assets' => $ac->current
        );
    }

    public static function getTableList()
    {
        return array(
            20 => array(
                200,
                4000
            ),
            50 => array(
                500,
                10000
            ),
            2000 => array(
                20000,
                400000
            ),
            80000 => array(
                800000,
                16000000
            ),
            400000 => array(
                4000000,
                80000000
            ),
            10000000 => array(
                100000000,
                2000000000
            )
        );
    }
}