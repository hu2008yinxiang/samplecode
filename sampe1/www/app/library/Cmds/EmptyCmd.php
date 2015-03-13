<?php
namespace Cmds;

class EmptyCmd extends Cmd
{

    public function execute()
    {
        return array(
            'stop' => false,
            'replace' => false,
            'result' => array(
                'cmd' => 'Empty',
                'errno' => \Errors::OK
            )
        );
    }
}