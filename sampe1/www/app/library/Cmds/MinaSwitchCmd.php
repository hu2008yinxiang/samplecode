<?php
namespace Cmds
{

    class MinaSwitchCmd extends Cmd
    {

        const CMD_NAME = 'MinaSwitch';

        public static function defaultData()
        {
            return array(
                'host' => '',
                'port' => '',
                'key' => ''
            );
        }

        protected function do_execute()
        {
            $host = $this->data['host'];
            $port = $this->data['port'];
            $key = $this->data['key'];
            
            if (empty($key) || $key != $this->config->app->minaKey) {
                return;
            }
            $found = $this->minaSwitcher->switchMina($host, $port);
            $this->logger->notice('MINA Switch ' . $host . ':' . $port . ':' . $key . ':' . ($found ? 'found' : 'not found'));
            $this->ret['result']['newMina'] = $found;
        }
    }
}