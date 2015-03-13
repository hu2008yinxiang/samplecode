<?php

class LogController extends \Phalcon\Mvc\Controller
{

    public function indexAction()
    {
        $default_line = 40;
        $logs = glob(DATA_PATH . '/*.log');
        array_unshift($logs, '/opt/log/poker_error.log', '/opt/log/poker_access.log');
        $data = array();
        foreach ($logs as $log) {
            $data[pathinfo($log, PATHINFO_BASENAME)] = $log;
        }
        $this->view->setVar('logs', $data);
        $tmp = array_keys($data);
        $log = $this->request->get('log', null, $tmp[0]);
        $line = $this->request->get('line', null, $default_line);
        $line = intval($line);
        if (! is_numeric($line) || $line <= 0)
            $line = $default_line;
        $line = $line - ($line % $default_line);
        if ($line == 0)
            $line = $default_line;
        $log = pathinfo($log, PATHINFO_BASENAME);
        if (! isset($data[$log])) {
            $log = array_keys($data);
            $log = $log[0];
        }
        $logfile = $data[$log];
        $this->view->setVar('log', $log);
        $this->view->setVar('line', $line);
        $this->view->setVar('default_line', $default_line);
        $this->view->setVar('content', array(
            'Log file not found.'
        ));
        if (! is_file($logfile)) {
            return;
        }
        $cmd = "tail -n $line $logfile";
        if ($line > $default_line) {
            $cmd = "tail -n $line $logfile | head -n $default_line";
        }
        $content = array();
        exec($cmd, $content);
        $this->view->setVar('content', $content);
    }
}