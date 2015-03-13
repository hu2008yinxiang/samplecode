<?php
namespace Cmds;

class CmdStore
{

    protected static $cmdStore = array();

    /**
     *
     * @param array $cmd
     */
    public static function addCmd($cmd, $account_id)
    {
        static $empty = array(
            'cmd' => 'Empty'
        );
        $cmd = array_merge($empty, $cmd);
        $class_name = 'Cmds\\' . $cmd['cmd'] . 'Cmd';
        if (class_exists($class_name)) {
            $cmd = new $class_name($cmd, $account_id);
        } else {
            $cmd = new MissedCmd($cmd, $account_id);
        }
        $cmd->setDI(\Phalcon\DI::getDefault());
        $cmd->setEventsManager(\Phalcon\DI::getDefault()->get('eventsManager'));
        array_push(self::$cmdStore, $cmd);
    }

    public static function checkSession($account_id, $session)
    {
        $server_session = \SessionManager::getLoginSession($account_id);
        // echo $server_session, '|', $session, PHP_EOL;
        $ret = ($server_session == $session) && ! empty($session);
        if ($ret) {
            // echo 'SESSION_OK', PHP_EOL;
            return $ret;
        }
        $check = false;
        foreach (static::$cmdStore as $cmd) {
            $check = $cmd->check() || $check;
            if ($check)
                break;
        }
        if ($check) {
            // echo 'NEED CHECK', PHP_EOL;
            // echo ($ret ? 'true' : 'false'), PHP_EOL;
            return $ret;
        }
        return true;
    }

    /**
     *
     * @return Cmd
     */
    public static function getCmd()
    {
        return array_shift(self::$cmdStore);
    }
}