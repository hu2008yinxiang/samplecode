<?php

class NameTask extends \Phalcon\CLI\Task
{

    public function mainAction(array $param = array())
    {
        $this->helpAction($param);
    }

    public function genAction(array $params = array())
    {
        $csv_file = $params[0];
        $out_file = $params[1];
        if (! is_file($csv_file)) {
            die($csv_file . ' not found.');
            return;
        }
        $csv_file = fopen($csv_file, 'r');
        $names = array();
        while ($data = fgetcsv($csv_file)) {
            if (count($data) != 4)
                continue;
            $names[] = $data;
        }
        fclose($csv_file);
        $data = array(
            'count' => count($names),
            'data' => $names
        );
        
        //$data = '<?php' . PHP_EOL . 'return unserialize(' . var_export(serialize($data), true) . ');' . PHP_EOL;
        //file_put_contents($out_file, $data);
        \Misc::cacheToFile($data, $out_file);
    }

    public function helpAction(array $param = array())
    {
        echo '', PHP_EOL;
    }
}