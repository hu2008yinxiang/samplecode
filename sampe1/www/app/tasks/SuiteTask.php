<?php

class SuiteTask extends \Phalcon\CLI\Task
{

    public function mainAction(array $params = array())
    {}

    public function genAction(array $params = array())
    {
        $csv_file = $params[0];
        $out_file = $params[1];
        if (! is_file($csv_file)) {
            die('can\'t open ' . $csv_file);
        }
        $fd = fopen($csv_file, 'r');
        $suites = array();
        while ($data = fgetcsv($fd)) {
            if (count($data) != 6)
                continue;
            $suites[] = $data;
        }
        fclose($fd);
        $count = count($suites);
        $data = array(
            'count' => $count,
            'data' => $suites
        );
        /*file_put_contents($out_file, array(
            '<?php ',
            PHP_EOL,
            'return unserialize(',
            var_export(serialize($data), true),
            ');',
            PHP_EOL
        ));*/
        \Misc::cacheToFile($data, $out_file);
    }
}