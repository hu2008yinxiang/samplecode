<?php

class LogTask extends Phalcon\CLI\Task
{

    public function mainAction()
    {}

    public function packAction()
    {
        $files = glob(DATA_PATH . '/*.log');
        $deadline = date('Ymd',strtotime('1 weeks ago'));
        $fs = array();
        foreach ($files as &$file) {
            if (date('Ymd', filemtime($file)) > $deadline) {
                continue;
            }
            $fs[] = '"' . $file . '"';
        }
        if (empty($fs)) {
            return;
        }
        $file_list = implode(' ', $fs);
        $gz = DATA_PATH . '/log_' . $deadline . '.tar.gz';
        if (is_file($gz)) {
            echo 'already packed!';
            return;
        }
        $cmd = "tar -czf \"$gz\" $file_list --remove-files -C \"" . DATA_PATH . "\"";
        exec($cmd);
    }
}