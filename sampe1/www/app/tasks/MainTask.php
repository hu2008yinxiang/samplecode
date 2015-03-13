<?php

class MainTask extends \Phalcon\CLI\Task
{

    public function mainAction(array $params = array())
    {
        $name = APP_NAME;
        echo "\e[32m\e[1m\e[5m\e[45m", "Welcome to $name!", "\e[0m", PHP_EOL;
        register_shutdown_function(function ()
        {
            echo "\e[32m\e[1m\e[5m\e[45m", "Bye!", "\e[0m", PHP_EOL;
        });
        //$fp = fopen('php://stdin', 'r');
        //$in = '';
        //while ($in != 'quit') {
        //    echo '> ';
        //    $in = trim(fgets($fp));
        //    echo ':', $in, PHP_EOL;
        //}
    }
}